<?php
namespace application\mixcall\controller;
use think\Controller;
use think\Db;
use think\db\Query;
/**
* 同步MINI ITS数据
 */
class Syncdata extends Controller{
	
    /* 同步客户信息 */
    
    /* 订单单号检测 */
    
	/* 同步订单数据 */
    public function to_order(){
        try{
            $data = input('post.data');
            /* 解密数据 */
            $data = $this->de_postdata($data);
            //检查数据
            if(!$this->checkReqData($data)) return false;
            Db::startTrans();
            //学员数据处理
            $userData = $this->preUserData($data);
            $result_user = model('common/users')->putData($userData);
            $userId = $data['user']['userId'] = $result_user['id'];
            $orderId = $data['order']['orderId'] = 0;
            $orderNo = $data['order']['orderNo'] = '';
            //dump($userData);exit;
            //$data['user']['entry']['userId'] = $result_user['id'];
            //订单主数据处理
            /*
            $orderData = $this->preOrderData($data);
            $result_order = model('common/orders')->putData($orderData);
            $orderId = $data['order']['orderId'] = $result_order['id'];
            $orderNo = $data['order']['orderNo'] = $result_order['data']['orderNo'];
            */
            //订单明细数据处理
            /*
            $result_entrys = $data['entry'];
            foreach($result_entrys as $k=>$entry):
                $orderDetailData = $this->preOrderDetailData($data,$entry);
                $result_order_detail = model('common/orderDetail')->putData($orderDetailData);
                $data['entry'][$k]['userId'] = $data['user']['userId'];
                $data['entry'][$k]['odd_id'] = $result_order_detail['id'];
                //$data['user']['entry']['courseData'] = $orderDetailData['courseData'];
            endforeach;*/
            //优惠信息
            
            //报名信息
            $result_entrys = $data['entry'];
            foreach($result_entrys as $k=>$entry):
                $entry['userId'] = $userId;
                $entry['odd_id'] = 0;
                $entry['other_price'] = 0;
                $entryData = $this->preEntryData($data,$entry);
                //$results[] = $entryData;
                $result_entry = model('common/student_edu')->putData($entryData);
                //$data['order']['type_id']==1 && $result_entry = model('common/student_edu')->putData($entryData);
                //in_array($data['order']['type_id'],array(2,3)) && $result_entry = model('common/student_skill')->putData($entryData);
                
            endforeach;
            //缴费信息
            $paymentData = $this->prePaymentData($data);
            $result_payment = model('common/payments')->putData($paymentData);
                        //var_dump($result_user['status'],$result_entry['status'],$result_payment['status']);exit;
            if( !empty($result_user['status']) && 
                //!empty($result_order['status']) && 
                //!empty($result_order_detail['status']) && 
                !empty($result_entry['status']) && 
                !empty($result_payment['status'])
             ):
                Db::commit();
                $return = array(
                   'userId'=>$userId,
                   'orderId'=>$orderId,
                   'orderNo'=>$orderNo,
                );
                MBISApiReturn(MBISReturn("同步数据成功",1,$return));
            else:
                Db::rollback();
                MBISApiReturn(MBISReturn("同步数据失败[1]"));
            endif;
        }catch(Exception $e){
            MBISApiReturn( MBISReturn("同步数据失败[2]：".$e->getFile().$e->getLine().$e->getMessage()) );
        }
    }
    private function checkReqData($data){
        $user = $data['user'];
        $order = $data['order'];
        foreach($data as $k=>$v):
            if(empty($v)):
                //MBISApiReturn(MBISReturn("[orderId:{$order['orderId']}]同步数据失败[{$k} is empty]"));
            endif;
        endforeach;
        //未报名，不做处理
        //if($entry['receiptType']!='全款' ) return false;
        return true;
    }
    private function preUserData($data){
        $data = $data['user'];
        $return['loginName'] = $data['idcard'];
        $return['idcard'] = $data['idcard'];
        $return['trueName'] = $data['trueName'];
        $return['nickName'] = $data['nickName'];
        $return['userPhone'] = $data['userPhone'];
        $return['userEmail'] = $data['userEmail'];
        $return['user_weixin'] = $data['user_weixin'];
        $return['userQQ'] = $data['userQQ'];
        $return['student_no'] = $data['student_no'];
    	$return['userType'] = 0;
        $return['uidType'] = 1;
        $return['student_type'] = 1;
        $return['study_status'] = 1;
        return $return;
    }
    private function preOrderData($data){
        $user = $data['user'];
        $order = $data['order'];
        $entry = $data['entry'];
        ///echo '<pre>';var_dump($entry);exit;
        $payment = $data['payment'];
        $type_id = $order['type_id'];
        //$course_id = $entry['course_id'];
        $return['type_id'] = $type_id;
        //客户信息
        $return['userId'] = $user['userId'];
        $return['agent_uid'] = 0;
        $return['buyType'] = 0; //购买方式：0=直销订单，1=代销订单
        $return['name'] = $user['trueName'];
        $return['mobile'] = $user['userPhone'];
        $return['idcard'] = $user['idcard'];//
        $return['confirmStatus'] = 1; //确认状态：0=未确认  1=已确认  2=已取消
        $return['payStatus'] = 1; //付款状态：0=未付款  1=已付款  2=已退款
        $return['isAppraise'] = 0;
        $return['createtime'] = $order['createtime'];
        //$return['lastmodify'] = time();
        $return['platform'] = '1'; //平台标识：1=pc，2=wap，3=android，4=ios
        $return['dataType'] = 1; //数据类型：1=正常下单  2=导入数据 99=后台录入
        $return['data_type'] = 1;
        $return['batch_num'] = 1;
        $return['courseMoney'] = $order['courseMoney'];
        $return['totalMoney'] = $order['totalMoney'];
        $return['realTotalMoney'] = $order['realTotalMoney'];
        $return['realPayMoney'] = $order['realPayMoney'];
        $return['adItMoney'] = $order['adItMoney'];
        $return['discountMoney'] = $order['discountMoney'];
        $return['depositRemainMoney'] = $order['depositRemainMoney'];
        $return['full_pay_price'] = $order['full_pay_price'];
        $return['notfull_pay_price'] = $order['notfull_pay_price'];
        $return['notfull_deal_price'] = $order['notfull_deal_price'];
        $return['orderRemarks'] = $order['orderRemarks'];
        $return['channelType'] = '-1';
        //支付方式
        $return['payTime'] = $order['payTime'];
        $return['payType'] = 2;
        $return['payFrom'] = ITSGetPayFromId($order['pay_name']);
        return $return;
    }
    private function preOrderDetailData($data,$entry=array()){
        $user = $data['user'];
        $order = $data['order'];
        //$entry = $data['entry'];
        //$order_detail = $user['order_detail'];
        //$payment = $entry['payment'][0];
        $type_id = $order['type_id'];
        $school_id = $entry['school_id'];
        $major_id = $entry['major_id'];
        $course_id = $entry['course_id'];
        $schoolData = model('common/school')->getInfoData($school_id);
        $majorData = model('common/major')->getInfoData($major_id);
        $courseData = model('common/course')->getInfoData($course_id);
        //dump($courseData);exit;
        $courseData['schoolName'] = (string)$schoolData['name'];
        $courseData['majorName'] = (string)$majorData['name'];
        $return['userId'] = $user['userId'];
        $return['agent_uid'] = 0;
        $return['type_id'] = $type_id;
        $return['orderNo'] = $order['orderNo'];
        $return['orderId'] = $order['orderId'];
        $return['createtime'] = $entry['entry_time'];
        $return['lastmodify'] = $entry['entry_time'];
        $return['course_id'] = $course_id;
        $return['course_name'] = (string)$courseData['name'];
        $return['course_bn'] = (string)$courseData['course_bn'];
        $return['grade_id'] = 0;
        $return['grade_name'] = '';
        $return['obj_id'] = 0;
        $return['obj_name'] = '';
        $return['price'] = $entry['price'];
        $return['fee_price'] = (string)$entry['price'];
        $return['number'] = 1;
        $return['obj_amount'] = $entry['price'];
        $return['obj_weight'] = '0.00';
        $return['score'] = 0;
        $return['cover_img'] = (string)$courseData['cover_img'];
        $return['is_full_pay'] = $entry['remain_price']==0?1:0;
        $return['course_real_price'] = $entry['receivable_fee'];
        $return['subject_offer_price'] = 0;
        $return['deal_pay_price'] = $entry['receivable_fee'];
        $return['real_pay_price'] = $entry['real_fee'];
        $return['remain_pay_price'] = $entry['remain_price'];
        $return['discount_aver_price'] = '0.00';
        $return['teacher_id'] = 0;
        $return['extend_data'] = serialize(obj2Array($entry));
        $return['courseData'] = $courseData;
        //dump($return);exit;
        return $return;
    }
    private function preEntryData($data,$entry=array()){
        $user = $data['user'];
        $order = $data['order'];
        $return['type_id'] = $entry['type_id'];
        $return['orderId'] = $order['orderId'];
        $return['orderNo'] = $order['orderNo'];
        $return['odd_id'] = $entry['odd_id'];
        $return['school_id'] = $entry['school_id'];
        $return['major_id'] = $entry['major_id'];
        $return['course_id'] = $entry['course_id'];
        $return['userId'] = $entry['userId'];
        //收款类别
        $return['receiptCate'] = (string)$entry['receiptCate'];
        //学校名称
        $return['school_name'] = (string)$entry['school_name'];
        //层次
        $return['level_id'] = $entry['level_id'];
        $return['level_name'] = $entry['level_name'];
        //报读专业
        $return['major_name'] = (string)$entry['major_name'];
        //学习形式
        $return['studyStatus'] = $entry['studyStatus'];
        //课程编码
        $return['course_bn'] = $entry['course_bn'];
        //课程名称
        $return['course_name'] = $entry['course_name'];
        //标准学费
        $return['price'] = $entry['price'];
        $return['receivable_fee'] = $entry['final_price'];
        $return['real_fee'] = $entry['deal_price'];
        $return['arrearage_fee'] = $entry['remain_price'];
        //优惠金额
        $return['discount_price'] = $entry['discount_price'];
        //其他优惠
        $return['discountBefore'] = $entry['discountBefore'];
        $return['discountPayNameRate'] = $entry['discountPayNameRate'];
        $return['discountSubjectSumRate'] = $entry['discountSubjectSumRate'];
        $return['discountTeamRate'] = $entry['discountTeamRate'];
        $return['discountHeadmasterRate'] = $entry['discountHeadmasterRate'];
        $return['discountActivityRate'] = $entry['discountActivityRate'];
        $return['discountSpecialRate'] = $entry['discountSpecialRate'];
        $return['discountAfter'] = $entry['discountAfter'];
        $return['confirmUserType'] = $entry['confirmUserType'];
        //应收学费总额
        $return['deal_price'] = $entry['final_price'];
        //累计已收学费总额
        $return['total_price'] = $entry['total_price'];
        //待收学费总额
        $return['wait_price'] = $entry['wait_price'];
        //是否欠费
        $return['arre_type'] = $entry['arre_type'];
        $return['entry_time'] = $entry['entry_time'];
        $return['data_type'] = $entry['data_type'];
        $return['batch_num'] = $entry['batch_num'];
        $return['exam_type'] = $entry['exam_type'];
        return $return;
    }
    private function prePaymentData($data){
        $user = $data['user'];
        $order = $data['order'];
        $entry = $data['entry'];
        $payment = $data['payment'];
        $type_id = $order['type_id'];
        $returns = array();
        $data = array();
        foreach($payment as $data):
            $return['orderId'] = $order['orderId'];
            $return['orderNo'] = $order['orderNo'];
            $return['receiptCate'] = $data['receiptCate'];
            $return['receiptSchool'] = $data['receiptSchool'];
            $return['receiptPrice'] = $data['receiptPrice'];
            $return['receiptDate'] = $data['receiptDate'];
            $return['receiptNo'] = $data['receiptNo'];
            $return['course_bn'] = $data['course_bn'];
            //$return['payType'] = $data['payType'];
            //$return['payWay'] = $data['payWay'];
            $return['procRate'] = $data['procRate'];
            $return['procFee'] = $data['procFee'];
            $return['realPayFee'] = $data['realPayFee'];
            $return['status'] = $data['status'];
            $return['pay_name'] = $data['pay_name'];
            $return['pay_type'] = $data['pay_type'];
            $return['userId'] = $user['userId'];
            $return['payment_id'] = $data['payment_id'];
            $return['type_id'] = $type_id;
            $return['data_type'] = $data['data_type'];
            $return['batch_num'] = $data['batch_num'];
            $return['status'] = $data['status'];
            $return['money'] = $data['money'];
            $return['cur_money'] = $data['cur_money'];
            $returns[] = $return;
        endforeach;
        return $returns;
    }
	
    //解密
	function de_postdata($data){
	    return json_decode($this->encrypt($data,'D'),true);
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
