<?php
namespace application\api\controller;
use think\Db;
#use think\Request;
#use think\Url;
/**
* 学员
 */
class Student extends Base{
    
    public function  test(){
        $aa = new \application\admin\model\StudentFeeLog();
        $arrOrder =  array(
                 'full_pay_price' => 10000,
                 'orderType'   =>1,
                 'userId'      => 1,
                 'orderId'     => 111111111111,
                 'orderNo'     => 222222222222,
                 'fee_class'   => '1',
                 'fee_type'    => '1',//收费类型：1=一次性收款，2=定金，3补费
                 'name'        => '培训费',//项目名称
                 'real_amount' => 10000,//全款金额
                 'notfull_deal_price'=>10000,
                 'depositRemainMoney' => 500,
             );
        $aa->installment($arrOrder);
    }
    public function  test1(){
        $aa = new \application\common\model\Sms;
        # $_POST['userIds'] = '78,65';
        # $_POST['content'] = 'sd【考试提醒通知】学员{学员名称}：请准备{考试时间}，参加{考试名称}考试，提前安排好时间、 查好考场及乘车路线及准备齐证件、考试工具（身份证、准考证、2B铅笔、黑色水笔、橡皮等），考试，不能迟到。{科目老师：手机号码}';
         $a = $aa->smsAdd(2,1);
        exit;
    }
    //学员缴费列表
    public function studentFees(){
        $name    = input('name');
        $phone   = input('phone');
        $idcard  = input('idcard');
        $orderNo = input('orderNo');
        //单独查询订单id
        if($orderNo){
            $map['orderNo'] = $orderNo;
        //查询学员姓名,手机号码,身份证号码
        }else{
        
            if( ( ( $name && $idcard  ) || ( $phone && $idcard) ) ){
               
            }else{
                MBISApiReturn( MBISReturn('学员姓名,手机号码,身份证号码为必填！！！',-1,array()) );
            }
            $map['name']   = $name;
            $map['mobile'] = $phone;
            $map['idcard'] = $idcard;
        }
        $arrOder   = db::name('orders')->field('orderId,userId')->where($map)->find();
        $orderId   = $arrOder['orderId'];
        if(!$orderId){
            MBISApiReturn( MBISReturn('查询不到数据！！！',1,array()) );
        }
        $params = input('post.');
        $params['userId'] = $arrOder['userId'];
        if(empty($params['userId']))
        {
            return MBISReturn("请先登录");
        }
        $rs['selItem'] = ITSGetSelData('order','order_status');
        //$params['type_id'] = $params['jump_type'];
        $params['type_id'] = -1;
        $params['field'] = 'type_id,orderId,orderNo,realTotalMoney as deal_pay_price,realPayMoney  as real_pay_price,depositRemainMoney as remain_pay_price,discountMoney,adItMoney,payType,payFrom,createtime,confirmStatus,orderType,payStatus,agent_uid,channelType';
        $order =  new \application\common\model\Orders();
        $orderLists = $order->get_lists($params);
        $rs['orderLists'] = $order->merge_order_detail($params['type_id'],$orderLists);
        return MBISApiReturn( MBISReturn('学院缴费记录！！！',1,$rs['orderLists']['lists']) );
    }
    
    //学杂费类型
    function studenArchivesType(){
          $studen = new \application\admin\model\Student;
          $studenArchivesType = $studen->studenArchivesType;
          $newArray = array();
          foreach($studenArchivesType as $key => $v){
              $newArray[$key]['archivesId']   =  $key;
              $newArray[$key]['archivesName'] =  $v;
          }
          $newArray = array_values($newArray);
          MBISApiReturn( MBISReturn('学杂费类型',1,$newArray) );
    }
    
    //学杂费保存数据
    function studenArchivesSave(){
        
        $type     = input('type');//学杂费
        $name     = input('name');//名称
        $phone    = input('phone');//电话
        $idcard   = input('idcard');//身份证卡号
        $money    = input('money');//提交金额
        $myUserId = input('myUserId');//提交人id
        $memo     = input('memo');//提交人备注
       
        if(!$type || !$money || !$idcard){
            MBISApiReturn( MBISReturn('学杂费类型,客户身份证号码,学杂费为必填！！！',-1,array()) );
        }
        
        if($name){
            $map['user.trueName']  = $name;
        }
        if($phone){
            $map['user.userPhone'] = $phone;
        }
        
        $map1 = '( st.idcard_no ='."'{$idcard}'".'OR et.tc_no ='."'{$idcard}' )";
            
        $join   = [
                   ['student_extend st','st.userId = user.userId','LEFT'],
                   ['mbis_tc_extend et','et.userid = user.userid','LEFT'],
                  ];
        
        $arrInfo       = Db::name('users')->alias('user')
                                          ->join($join)
                                          ->field('user.userId,st.userId')
                                          ->where( $map )
                                          ->where( $map1 )
                                          ->find();
        #getLastSql();
        if(!$arrInfo){
            MBISApiReturn( MBISReturn('查询不到客户信息！！！',-1,array()) );
        }
        $data = array(
            'typeId'     => 1,
            'userId'     => $arrInfo['userId'],
            'createtime' => time(),
            'createId'   => UID,
            'memo'       => $memo,
            'money'      => $money
        );
        
        $id  = Db::name('incidentals')->insertGetId($data);
        if($id){
            $msg    = '学杂费提交成功！！！';
            $status = '1';
        }else{
            $msg    = '学杂费提交失败！！！';
            $status = '-1';
        }
        MBISApiReturn( MBISReturn($msg,$status,array()) );
        
    }
    
    //学员留言
    function StudentMessage(){
        $studentMessageClass = new \application\common\model\StudentMessage;
        $return_id = $studentMessageClass->studentMessage();
    }
    
 
    
    
   
}

