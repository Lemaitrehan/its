<?php
namespace application\common\model;
use think\Db;
/**
 * 提现流水业务处理器
 */
class CashDraws extends Base{
     /**
      * 获取列表
      */
      public function pageQuery($targetType,$targetId){
      	  $type = (int)input('post.type',-1);
          $where = [];
          $where['targetType'] = (int)$targetType;
          $where['targetId'] = (int)$targetId;
          if(in_array($type,[0,1]))$where['moneyType'] = $type;
          return $this->where($where)->order('cashId desc')->paginate()->toArray();
      }

      /**
       * 申请提现
       */
      public function drawMoney(){
          $userId = (int)session('MBIS_USER.userId');
          $money = (float)input('money');
          $accId = (float)input('accId');
          $payPwd = input('payPwd');
          if($money<=0)return MBISReturn('提取金额必须大于0');
          if($payPwd=='')return MBISReturn('支付密码不能为空');
          //加载提现账号信息
          $acc = Db::name('cash_configs')->alias('cc')
                   ->join('__BANKS__ b','cc.accTargetId=b.bankId')->where(['cc.dataFlag'=>1,'id'=>$accId])
                   ->field('b.bankName,cc.*')->find();
          if(empty($acc))return MBISReturn('提现账号不存在');
          $areas = model('areas')->getParentNames($acc['accAreaId']);
          //加载用户
          $user = model('users')->get($userId);
          $payPwd = md5($payPwd.$user->loginSecret);
          if($payPwd!=$user->payPwd)return MBISReturn('支付密码错误');
          if($money>$user->userMoney)return MBISReturn('提取金额不能大于用户余额');
          //减去要提取的金额
          $user->userMoney = $user->userMoney-$money;
          $user->lockMoney = $user->lockMoney+$money;
          Db::startTrans();
          try{
             $result = $user->save();
             if(false !==$result){
                //创建提现记录
                $data = [];
                $data['targetType'] = 0;
                $data['targetId'] = $userId;
                $data['money'] = $money;
                $data['accType'] = 3;
                $data['accTargetName'] = $acc['bankName'];
                $data['accAreaName'] = implode('',$areas);
                $data['accNo'] = $acc['accNo'];
                $data['accUser'] = $acc['accUser'];
                $data['cashSatus'] = 0;
                $data['cashConfigId'] = $accId;
                $data['createTime'] = date('Y-m-d H:i:s');
                $this->save($data);
                $this->cashNo = $this->cashId.(fmod($this->cashId,7));
                $this->save();
                Db::commit();
                return MBISReturn('提现申请成功，请留意系统信息',1);
             }
          }catch (\Exception $e) {
            Db::rollback();
            return MBISReturn('提现申请失败',-1);
          }
          
          

      }

     
}
