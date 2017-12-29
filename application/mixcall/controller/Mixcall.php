<?php
namespace application\mixcall\controller;
use think\Controller;
use think\Db;
use think\db\Query;
/**
* 评价控制器
 */
class Mixcall extends Controller{
	/**
	* 同步小its数据
	*/
	public function index(){
	    
	    
	    //$db = db('',"database");撒大大
	  /*   $a  =  db()->query('select * from mixcall.mix_admin as a 
	                       left join zp.mbis_accreds as b on b.accredId = a.userid');
	    $sql= "INSERT INTO mixcall.mix_admin (username) value(222)";
	    $id = db()->execute($sql);
	    exit; */
	   $data = input('post.data');
	   //$data = "KDUKzsHPeOvpYFwcaidYC1odG6ItwGuxo55KL18QlRijOiJpeAkJ8lgvj7ftdj5112wilyvMXRo9wtMGqRuhwtAus3gTk6y9zWBH4XEoPuBMtJ+xU9Ig9Z32RAJaH2rQMQdH/o/WYqBjlY2Dug1Buv2PJUsMO29KvXAp3WowOMJILMaCEb+/WQjyOCd6mVXlUgGi61p3mQ3SzAuLT+KfaweZ3Aj/zhM3aeymYPqAPASa1orFIVCrLQU9S5TWC3g94SyLXGCgQxGtONeuD/RzbSN+bElNIISFDWrvh1shcoSLyyCbj96x5nWJSiFRCwe8ngbwUnafG92hTjzOPDqCaSsaiMvhwJcCso9WHKUrqwDGrq4Y+kA+CersC+w5yuVKOtS1REeq4rIBkRK/D2LDPo2803UifjO7nd++33+YsHL/5E0nkDrVMOI47eveuylF7ru+W+4GQcweze0GIXuu7Diqqv+A/VTM215fjsYI8U6iDcYqyxr+oQlaVTBpEHf3jM8u+rTL19/xWj2t/A8p6b31n0yPq1qmGn+fiEof33Ydt/B2+9qhdwbckaWdNz8wD/5jVXAiUrBLb+LIcn3gK0aMnObausrv5q6GEETcq8sztnoDEwQFLwXY2vdiUo2QY65rDpXvZTOhiupgVwAXfjQQcdBwywVd+pCf36EgprryXq0ksR4YI8qS7v+07eGdKrBZI023dqoSY95ho2uo7PsP4IID/KQBAtq1i4iV0R85SC4o7sgmdzT4MRsE/auA67hExCR9uI+/m1Js3BC0qoAwtgJagTNnScBXAV2hC7LZkjeRIqAw0t1RnsYQly6jJp/dfSvKCIwJa37+46P4pemSA6Imx9TmbUahb7viwnm4m+rj8bOP/0gXuodPkOX+Am6CA/ZcCyIj1kHtLBpJn7Zs+Ri3noJN5fd5MjFGhs1EnxJdidOZNZTQ/oJyPYdH7EUMaatg6QBVXR1/ZgXxBO+niY9eHlIdPYO2ivKykaoYdejOeSv+JeZnWzIKm5Ac6r9o65yOV5myziJgw98YHhTDpSkdFf4M";
	   if(empty($data)){
	       exit( json_encode(array('status'=>0,'msg'=>'数据不能为空')) );
	   }
	   $sqls = $this->get_sqls($data);
	   if(!is_array($sqls)){
	       exit( json_encode(array('status'=>0,'msg'=>'数据错误')) );
	   }
	   Db::startTrans();
	   $db = db();
	   $status = 1;
	   $msg    = '数据更新成功';
	   try{
    	   foreach ($sqls as $key => $v){
    	      $id =  $db->execute("$v");
    	/*    dump($v);
    	      dump($id); */
    	      if(!$id){
    	          exception('数据更新失败');
    	      }
    	   }
    	   Db::commit();
       } catch (\Exception $e) {
           Db::rollback();
           //echo $e->getMessage();
           $status = 0;
           $msg    = '数据更新失败';;
       }    
	   exit( json_encode(array('status'=>$status,'msg'=>$msg)) );
	}
	
	
	
	//同步its订单信息
    function order_synchronization(){
        
        $data = input('post.data');
        if(!$data){
            return FALSE;
        }
        $data = json_decode( $this->get_sqls($data) );
        Db::startTrans();
        
        try{
            
            foreach ($data as $key => $v){
                 $arrUser     = $v['userid'];
                 //------------------先查找是否有用户-------------------
                 $where  = 'userPhone like %'.$arrUser['userPhone'].'% OR idcard like %'.$arrUser['idcard'].'%'; 
                 $userId = db('users')->where($where)->value('userId');
                     if(!$userId){
                         $addData = array(
                             'loginName'   => $arrUser['loginName'],
                             'loginSecret' => $arrUser['loginSecret'],
                             'loginPwd'    => $arrUser['loginPwd'],
                             'userType'    => $arrUser['userType'],
                             'userSex'     => $arrUser['userSex'],
                             'trueName'    => $arrUser['trueName'],
                             'nickName'    => $arrUser['nickName'],
                             'userPhoto'   => $arrUser['userPhone'],
                             'brithday'    => $arrUser['birthday'],
                             'userQQ'      => $arrUser['userQQ'],
                             'userPhone'   => $arrUser['userPhone'],
                             'userEmail'   => $arrUser['userEmail'],
                             'lastIP'      => $arrUser['lastIP'],
                             'lastTime'    => $arrUser['lastTime'],
                             'employee_id' => $arrUser['employee_id'],
                             'employee_type_id' => $arrUser['employee_type_id'],
                             'department_id' => $arrUser['department_id'],
                             'userStatus' => $arrUser['userStatus'],
                             'rankId' => $arrUser['rankId'],
                             'dataFlag' => $arrUser['dataFlag'],
                             'payPwd' => $arrUser['payPwd'],
                             'accesstoken' => $arrUser['loginName'],
                             'uidType' => $arrUser['uidType'],
                             'student_no' => $arrUser['student_no'],
                             'pre_entry_no' => $arrUser['pre_entry_no'],
                             'student_type' => $arrUser['student_type'],
                             'study_status' => $arrUser['study_status'],
                             'userAddress'  => $arrUser['userAddress'],
                             'is_import'    => $arrUser['is_import'],
                             'import_time'  => $arrUser['import_time'],
                             'createtime'   => $arrUser['createtime'],
                             'lastmodify'   => $arrUser['lastmodify'],
                             'data_type'    => $arrUser['data_type'],
                             'batch_num'    => $arrUser['batch_num'],
                             'idcard'       => $arrUser['idcard'],
                             'nation'       => $arrUser['nation'],
                             'user_weixin'  => $arrUser['user_weixin'],
                             'culture_method'           => $arrUser['culture_method'],
                             'education_level'          => $arrUser['education_level'],
                             'graduate_colleges'        => $arrUser['graduate_colleges'],
                             'colleges_number'          => $arrUser['colleges_number'],
                             'graduate_date'            => $arrUser['graduate_date'],
                             'certificate_number'       => $arrUser['certificate_number'],
                             'idcard_Photo'             => $arrUser['idcard_Photo'],
                             'identification_photo'     => $arrUser['identification_photo'],
                             'brfore_certificate_photo' => $arrUser['brfore_certificate_photo'],
                             'after_certificate_photo'  => $arrUser['after_certificate_photo'],
                             'grade_id'                 =>$arrUser['grade_id'],
                         );
                         $userId = db('users')->save($addData);
                         if(!$userId){
                             exception('订单id'.$key.'学员数据生成失败！！！');
                         }
                 }
                 //----------------------------------end 学员信息---------------------------------
                 
                 //----------------------------------start 订单信息-------------------------------
                 $arrOrder    = $v['order'];
                 $orderData      = array(
                     'orderNo'     => $arrOrder['orderNo'],
                     'userId'      => $userId,
                     'agent_uid'   => $arrOrder['agent_uid'],
                     'buyType'     => $arrOrder['buyType'],
                     'type_id'     => $arrOrder['type_id'],
                     'orderStatus' => $arrOrder[''],///???????????????
                     'courseMoney' => $arrOrder['courseMoney'],//???
                     'totalMoney'  => $arrOrder['deal_price'],
                     'realTotalMoney' => $arrOrder['realTotalMoney'],
                     'realPayMoney'   => $arrOrder['realPayMoney'],
                     'adItMoney'      => $arrOrder['adItMoney'],
                     'pmt_order'      => $arrOrder['pmt_order'],
                     'payType'        => $arrOrder['payType'],
                     'payFrom'         => $arrOrder['payFrom'],
                     'payStatus'       => $arrOrder['payStatus'],
                     'discountMoney'   => $arrOrder['discountMoney'],
                     'depositMoney'    => $arrOrder['depositMoney'],
                     'depositAddMoney' => $arrOrder['depositAddMoney'],
                     'depositRemainMoney' => $arrOrder['depositRemainMoney'],
                     'full_pay_price'     => $arrOrder['full_pay_price'],
                     'notfull_pay_price'  => $arrOrder['notfull_pay_price'],
                     'notfull_deal_price' => $arrOrder['notfull_deal_price'],
                     'name'         => $arrOrder['name'],
                     'mobile'       => $arrOrder['mobile'],
                     'idcard'       => $arrOrder['idcard'],
                     'orderRemarks' => $arrOrder['orderRemarks'],
                     'isClosed'     => $arrOrder['isClosed'],
                     'cancelReason' => $arrOrder['cancelReason'],
                     'orderType'    => $arrOrder['orderType'],
                     'platform'     => $arrOrder['platform'],
                     'tradeNo'      => $arrOrder['tradeNo'],
                     'payTime'      => $arrOrder['payTime'],
                     'isAppraise'    => $arrOrder['isAppraise'],
                     'confirmStatus' => $arrOrder['confirmStatus'],
                     'channelType'   => $arrOrder['channelType'],
                     'dataType'      => $arrOrder['dataType'],
                     'dataFlag'      => $arrOrder['dataFlag'],
                     'createtime'    => $arrOrder['createtime'],
                     'lastmodify'    => $arrOrder['lastmodify'],
                     'data_type'     => $arrOrder['data_type'],
                     'batch_num'     => $arrOrder['batch_num'],
                     'taxType'       => $arrOrder['taxType'],
                     'taxCompany'    => $arrOrder['taxCompany'],
                     'addr'          => $arrOrder['addr'],
                     'supplementNum' => $arrOrder['supplementNum'],
                 );
                 
                 $orderId = db('orders')->save($orderData);
                 if(!$orderId){
                     exception('订单id'.$key.'学员订单数据生成失败！！！');
                 }
                 //---------------------------END -订单信息----------------------------
                 
                 //---------------------------订单缴费---------------------------------
                 $arrOrderPay = $v['orderPay'];
                 foreach ($arrOrderPay as $ko => $vo){
                     $addOrderpay = array(
                         'userId'     => $vo[''],
                         'agent_uid'  => $vo[''],
                         'type_id'    => $vo['type_id'],
                         'orderId'    => $orderId,
                         'orderNo'    => $vo['orderNo'],
                         'course_id'  => $vo['course_id'],
                         'course_name' => $vo['course_name'],
                         'obj_id'      => $vo['obj_id'],
                         'obj_name'    => $vo['obj_name'],
                         'price'       => $vo['price'],
                         'fee_price'   => $vo['fee_price'],
                         'number'      => $vo['number'],
                         'obj_amount'  => $vo['obj_amount'],
                         'obj_weight'  => $vo['obj_weight'],
                         'score'       => $vo['score'],
                         'cover_img'   => $vo['cover_img'],
                         'is_full_pay'     => $vo['is_full_pay'],
                         'deal_pay_price'  => $vo['deal_pay_price'],
                         'real_pay_price'  => $vo['remain_pay_price'],
                         'remain_pay_price'    => $vo['remain_pay_price'],
                         'discount_aver_price' => $vo['discount_aver_price'],
                         'deposit_aver_price'  => $vo['deposit_aver_price'],
                         'pmt_order_aver_price'=> $vo['pmt_order_aver_price'],
                         'teacher_id'          => $vo['teacher_id'],
                         'course_real_price'   => $vo['course_real_price'],
                         'subject_offer_price' => $vo['extend_data'],
                         'extend_data'         => $vo['extend_data'],
                         'createtime'          => $vo['createtime'],
                         'lastmodify'          => $vo['lastmodify'],
                         'obj_type'            => $vo['obj_type'],
                         'online_course_price' => $vo['online_course_price'],
                     );
                     $order_detail_Id = db('order_detail')->save($addOrderpay);
                     if(!$order_detail_Id){
                         exception('订单id'.$key.'学员订单详情失败数据生成失败！！！');
                     }
                 }
                 //-----------------------end 订单缴费--------------------------------
                 
                 //-----------------------start 优惠------------------------------
                 $arrPromotionalInformation = array();
                 $addPromotionalInformation = array(
                         'rule_id'         => $arrPromotionalInformation['rule_id'],
                         'rule_use'        => $arrPromotionalInformation['rule_use'],
                         'platform_use'    => $arrPromotionalInformation['platform_use'],
                         'userId'          => $arrPromotionalInformation['userId'],
                         'agent_uid'       => $arrPromotionalInformation['agent_uid'],
                         'type_id'         => $arrPromotionalInformation['type_id'],
                         'orderNo'         => $arrPromotionalInformation['orderNo'],
                         'orderId'         => $arrPromotionalInformation['orderId'],
                         'name'            => $arrPromotionalInformation['name'],
                         'description'     => $arrPromotionalInformation['description'],
                         'from_time'       => $arrPromotionalInformation['from_time'],
                         'to_time'         => $arrPromotionalInformation['to_time'],
                         'member_lv_ids'   => $arrPromotionalInformation['member_lv_ids'],
                         'member_type_ids' => $arrPromotionalInformation['member_type_ids'],
                         'conditions'            => $arrPromotionalInformation['conditions'],
                         'action_conditions'     => $arrPromotionalInformation['action_conditions'],
                         'stop_rules_processing' => $arrPromotionalInformation[''],
                         'sort_order'      => $arrPromotionalInformation['sort_order'],
                         'action_solution' => $arrPromotionalInformation['action_solution'],
                         'c_template'     => $arrPromotionalInformation['c_template'],
                         's_template'     => $arrPromotionalInformation['s_template'],
                         'discount_price' => $arrPromotionalInformation['discount_price'],
                         'createtime'     => $arrPromotionalInformation['createtime'],
                         'lastmodify'     => $arrPromotionalInformation['lastmodify'],
                         'supplementNum'  => $arrPromotionalInformation['supplementNum']
                     
                 );
                 $order_youhui_Id = db('order_rule_log')->save($orderData);
                 if(!$order_youhui_Id){
                     exception('订单id'.$key.'学员优惠信息生成失败！！！');
                 }
                 //-------------------------------end 优惠信息------------------------------------
                 
            }
            Db::commit();
             $return = true; 
         } catch (\Exception $e) {
             $return = $e->getMessage();
             Db::rollback();
         }
        return $return;
    }
	
	
    //解密
	function get_sqls($data){
	    $data = json_decode($this->encrypt($data,'D'),true);
	    return json_decode(base64_decode($data['codes']),true);
	}
	
	function encrypt($string,$operation,$key='its2018')
	{
	    $key=md5($key);
	    $key_length=strlen($key);
	    $string=$operation=='D'?base64_decode($string):substr(md5($string.$key),0,8).$string;
	    $string_length=strlen($string);
	    $rndkey=$box=array();
	    $result='';
	    for($i=0;$i<=255;$i++)
	    {
	        $rndkey[$i]=ord($key[$i%$key_length]);
	        $box[$i]=$i;
	    }
	    for($j=$i=0;$i<256;$i++)
	    {
	        $j=($j+$box[$i]+$rndkey[$i])%256;
	        $tmp=$box[$i];
	        $box[$i]=$box[$j];
	        $box[$j]=$tmp;
	    }
	    for($a=$j=$i=0;$i<$string_length;$i++)
	    {
	        $a=($a+1)%256;
	        $j=($j+$box[$a])%256;
	        $tmp=$box[$a];
	        $box[$a]=$box[$j];
	        $box[$j]=$tmp;
	        $result.=chr(ord($string[$i])^($box[($box[$a]+$box[$j])%256]));
	    }
	    if($operation=='D')
	    {
	        if(substr($result,0,8)==substr(md5(substr($result,8).$key),0,8))
	        {
	            return substr($result,8);
	        }
	        else
	        {
	            return'';
	        }
	    }
	    else
	    {
	        return str_replace('=','',base64_encode($result));
	    }
	}

}
