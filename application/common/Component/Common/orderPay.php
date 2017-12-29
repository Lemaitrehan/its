<?php 
namespace application\common\Component\Common;
use application\common\interfaces\IOrderPay;
use think\Db;
use think\console\command\make\Model;
class orderPay implements IOrderPay{
 
    private static $Instance = null;
    /** (non-PHPdoc)
     * @see \application\common\interfaces\IOrderPay::updateOrderByPay()
     * @return boolean
     */
    public function updateOrderByPay($callback_data, $type, &$msg)
    {
        //TODO Auto-generated method stub
        if($type['key'] == 'alipay'){
            $data = $this->alipay($callback_data,$type['alias']);
        } 
        if($type['key'] == 'wxpay'){
            $data = $this->wxpay($callback_data,$type['alias']);
        }
       
        
        $payments = Db::name('payments')->where(['payment_id'=>$data['payment_id']])->find();
        if(!$data||!$payments){
            $msg = '非法订单数据';
            return false;
        }
        if($payments['disabled'] != 'false'){
            $msg = '订单已失效';
            return false;
        }
        if($payments['status'] == 'ready'){
            $msg = '支付还未启动';
            return false;
        }
        if($payments['status'] == 'succ'){
            $msg = '订单已支付过了，不需要重复支付';
            return false;
        }
        
        //更新启动
        $filter = array('orderId'=>$payments['orderId']);
        $payments['obj_ids'] && $filter['odd_id']=['in',json_decode($payments['obj_ids'],1)];
       
        //if($payments['obj_ids']){
            //更新订单详细科目数据
            $udata = [
                'lastmodify'=>time(),
                'is_full_pay'=>1,
            ];
            Db::startTrans();
            try{
                
                //检查是否是最后的科目更新，是的话，更新最后的主订单。
                $count = Db::name("order_detail")->where(['is_full_pay'=>0,'orderId'=>$payments['orderId']])->field('count(odd_id) c')->find();
                
                $orders = Db::name("orders")->where(['orderId'=>$payments['orderId']])->find();
                //if($count['c'] >= count(json_decode($payments['obj_ids'],1))){
                    //更新数量大于等于需要更新的数据，此时可以更新主订单表。
                    $r = Db::name("orders")->where(['orderId'=>$payments['orderId']])->update([
                       'confirmStatus'=>0,
                       'payStatus'=>1,
                       'orderType'=>1,
                       'payType'=>2,
                       'lastmodify'=>time(),
                    ]);
                    
                    $order_detail = Db::name("order_detail")->where($filter)->select();
                    //学生报名记录写入 
                    //1、学历 mbis_student_edu 
                    //2、技能 mbis_student_skill
                    $student_edu = $student_skill = [];
                    
                    foreach($order_detail as $good){
                        if($good['type_id'] == '1'){
                            //1、学历
                            $student_edu[] = [
                                'userId'=>$orders['userId'],//'会员ID' ,
                                'agent_uid'=>$orders['agent_uid'],//'代购会员ID' ,
                                'odd_id'=>$good['odd_id'],//'订单明细表对象ID' ,
                                'orderId'=>$good['orderId'],//'订单主表ID' ,
                                'orderNo'=>$good['orderNo'],//'订单号' ,
                                //'school_id'=>$good['school_id'],//'合作院校【上游单】' ,
                                //'school_name'=>$good['school_name'],//'合作院校【上游单】' ,
                                //'major_id'=>$good['major_id'],//'报名专业ID' ,
                                //'major_name'=>$good['major_name'],//'报名专业名称' ,
                                'course_id'=>$good['course_id'],//'报读课程ID' ,
                                'course_name'=>$good['course_name'],//'报读课程名称' ,
                                'grade_id'=>$good['obj_id'],//'年级ID' ,
                                'grade_name'=>$good['obj_name'],//'报名年级名称' ,
                                //'course_bn'=>$good['course_bn'],//'课程编码' ,
                                //'exam_no'=>$good['exam_no'],//'考籍号/用户名/准考证号' ,
                                //'login_pass'=>'',//'登陆密码' ,
                                //'login_url'=>'',//'登陆网址' ,
                                //'info_source'=>'',//'信息来源' ,
                                //'school_code'=>$order_detail['school_code'],//'报名来源【下游单】校区代码' ,
                                'receivable_fee'=>$good['deal_pay_price'],//'应收学费' ,
                                'real_fee'=>$good['real_pay_price'],//'实收费用' ,
                                'arrearage_fee'=>$good['remain_pay_price'],//'欠费' ,
                                'remark'=>'',//'备注(注明优惠明细)' ,
                                //'invoice_no'=>'',//'发票号码' ,
                                //'fee_content'=>'',//'学费收缴情况(序列化)' ,
                                //'student_cert'=>'',//'学员证制作：0为否，1为是' ,
                                //'complete_cert'=>'',//'结业证制作：0为否，1为是' ,
                                //'job_content'=>'',//'就业情况' ,
                                //'class_ending'=>'',//'结课情况' ,
                                //'delivery_info'=>'',//'交资料情况(序列化)' ,
                                //'notify_school_info'=>'',//'通知上课情况' ,
                                //'feedback_content'=>'',//'反馈情况' ,
                                //'is_school_sms'=>'',//'是否发送上课通知短信：0为否，1为是' ,
                                'extend_data'=>trim($good['extend_data']),//'扩展数据' ,
                                'entry_time'=>time(),//'报名时间' ,
                            ];
                            
                        }elseif($good['type_id']=='2'){
                            //2、技能
                           $student_skill[] = [
                               'userId'=>$orders['userId'],//'会员ID' ,
                               'agent_uid'=>$orders['agent_uid'],//'代购会员ID' ,
                               'odd_id'=>$good['odd_id'],//'订单明细表对象ID' ,
                               'orderId'=>$good['orderId'],//'订单主表ID' ,
                               'orderNo'=>$good['orderNo'],//'订单号' ,
                               //'school_id'=>'',//'合作院校【上游单】' ,
                               //'school_name'=>'',//'合作院校【上游单】' ,
                               //'major_id'=>'',//'报名专业ID' ,
                              // 'major_name'=>'',//'报名专业名称' ,
                               'subject_id'=>$good['obj_id'],//'报名科目ID' ,
                               'subject_name'=>$good['obj_name'],//'报名科目名称' ,
                               'course_id'=>$good['course_id'],//'报读课程ID' ,
                               'course_name'=>$good['course_name'],//'报读课程名称' ,
                               //'info_source'=>'',//'信息来源' ,
                               //'course_bn'=>'',//'课程编码' ,
                               //'exam_no'=>'',//'考籍号/用户名/准考证号' ,
                               //'login_pass'=>'',//'登陆密码' ,
                               //'login_url'=>'',//'登陆网址' ,
                               //'school_code'=>'',//'报名来源【下游单】校区代码' ,
                               'receivable_fee'=>$good['deal_pay_price'],//'应收学费' ,
                               'real_fee'=>$good['real_pay_price'],//'实收费用' ,
                               'arrearage_fee'=>$good['remain_pay_price'],//'欠费' ,
                               'remark'=>'',//'备注(注明优惠明细)' ,
                               
                               //'invoice_no'=>'',//'发票号码' ,
                               //'fee_content'=>'',//'学费收缴情况(序列化)' ,
                               //'student_cert'=>'',//'学员证是否制作：0为否，1为是' ,
                               //'complete_cert'=>'',//'结业证是否制作：0为否，1为是' ,
                               //'job_content'=>'',//'就业情况' ,
                               //'class_ending'=>'',//'结课情况' ,
                               //'delivery_info'=>'',//'交资料情况(序列化)' ,
                               //'notify_school_info'=>'',//'通知上课情况' ,
                               //'feedback_content'=>'',//'反馈情况' ,
                               //'is_school_sms'=>'',//'是否发送上课通知短信：0为否，1为是' ,
                               'extend_data'=>trim($good['extend_data']),//'扩展数据' ,
                               //'follow_type'=>'',//'跟进方式：1为电话跟进、2为网络跟进、3为短信跟进' ,
                               'counselor'=>$good['teacher_id'],//'负责咨询师' ,
                               //'call_service'=>'',//'电询客服' ,
                               // 'order_status'=>'',//'咨询状态:1为首咨、2为跟进、3为已约访、4为到访、5为已报名、6为意向较好、7为陪同来访' ,
                              // 'agree_no'=>'',//'协议编号' ,
                               //'access_time'=>'',//'下次回访时间' ,
                               'entry_time'=>time(),//'报名时间' ,
                           ];
                        }
                        
                    }
                   
                    //入库处理
                    //1、学历报名批量入库
                    if($student_edu){
                        Db::name('student_edu')->insertAll($student_edu);
                    }
                    
                    //2、技能报名批量入库
                    if($student_skill){
                        Db::name('student_skill')->insertAll($student_skill);
                    }
                    
                //}

                
                //更新订单详细表。
                //$count['c'] && 
                Db::name('order_detail')->where($filter)->update($udata);
              
                //更新付款订单信息。
                Db::name('payments')->where(['payment_id'=>$payments['payment_id'],'disabled'=>'false'])->update([
                    'trade_no'=>$data['trade_no'],
                    'thirdparty_account'=>$data['thirdparty_account'],
                    't_confirm'=>time(),
                    'status'=>'succ',
                    't_payed'=>time(),
                ]);
               
                
                //付款记录
                $fl = Db::name('payments')->where(['orderId'=>$payments['orderId']])->field("count(payment_id) c")->find();
                //mbis_order_payment
                //记录写入
                Db::name('order_payment')->insert([
                    'userId'=>$orders['userId'],//'学员ID' ,
                    'type_id'=>$orders['type_id'],//'购买对象类型：0=其他，1=学历，2=技能' ,
                    'orderId'=>$orders['orderId'],//'订单主表标识' ,
                    'payType'=>'1',//'支付类型：1=线上支付，2=线下支付' ,
                    'payFrom'=>$orders['payFrom'],//'支付方式：1=支付宝，2为微信，3=网银支付，4=现金支付，5=POS机支付，6=支票，7=对公账号转账，99=其他方式' ,
                    'orderNo'=>$payments['orderNo'],//'订单编号' ,
                    'payMoney'=>$payments['money'],//'支付金额' ,
                    'tradeNo'=>$data['trade_no'],//'交易号' ,
                    'isFirst'=>$fl['c']>1?0:1,//'是否首次付款：1=是，0=否' ,
                    'createtime'=>time(),//'支付时间' ,
                    'lastmodify'=>time(),//'最后更新时间' ,
                ]);
                //事务提交
                Db::commit();
                $msg = '更新成功';
                return true;
            }catch (\Exception $e) {
                //print_r($e->getMessage());die;
                //事务回滚
                Db::rollback();
                $msg = '更新失败';
                return false;
            }
        //}
       // $msg = '更新失败';
       // return false;
    }
    
    /**
     * @return \application\common\Component\Common\OrderPay
     */
    public static function getSelf(){
        self::$Instance = self::$Instance!=null?self::$Instance:new self();
        return self::$Instance;
    }
    public function __construct(){
        //TODO 初始化
    }
    
    /**
     * 支付宝支付
     * @param array $callback
     * @param array $method
     * @return multitype:['payment_id'=>'','amount'=>'','method_type'=>'alipay','trade_no'=>''];
     */
    public function alipay($callback,$method){
        
        $data = [
            'payment_id'=>$callback['out_trade_no'],
            'amount'=>$callback['total_amount'],
            'method_type'=>'alipay',
            'trade_no'=>$callback['trade_no'],
            'thirdparty_account'=>$callback['buyer_logon_id'],
        ];
        return $data;
    }
    
    /**
     * 微信支付
     * @param array $callback
     * @param array $method
     * @return multitype:['payment_id'=>'','amount'=>'','method_type'=>'wxpay','trade_no'=>''];
     */
    public function wxpay($callback,$method=''){
        
        //$data = [];
        
        $data = [
            'payment_id'=>$callback['out_trade_no'],
            'amount'=>$callback['total_fee']/100,
            'method_type'=>'wxpay',
            'trade_no'=>$callback['transaction_id'],
            'thirdparty_account'=>$callback['openid'],
        ];
        return $data;
    }
 

 
   
    
    

    
}