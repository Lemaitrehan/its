<?php
namespace application\common\model;
use think\Db;
/**
 * 退款业务处理类
 */
class OrderRefunds extends Base{
	/**
	 * 用户申请退款
	 */
	public function refund(){
		$orderId = (int)input('post.id');
		$reason = (int)input('post.reason');
		$content = input('post.content');
		$money = (float)input('post.money');
		$userId = (int)session('MBIS_USER.userId');
		if($money==0)return MBISReturn("退款金额不能为0");
		$order = Db::name('orders')->alias('o')->join('__SHOPS__ s','o.shopId=s.shopId','left')
		           ->where(['o.userId'=>$userId,'o.payType'=>1,'o.orderId'=>$orderId,'o.orderStatus'=>['in',[-3,-1]]])
		           ->field('o.orderId,s.userId,o.orderStatus,o.orderNo,o.realTotalMoney')->find();
		$reasonData = MBISDatas(4,$reason);
		if(empty($reasonData))return MBISReturn("无效的退款原因");
		if($reason==10000 && $content=='')return MBISReturn("请输入退款原因");
		if(empty($order))return MBISReturn('操作失败，请检查订单状态是否已改变');
		if($money>$order['realTotalMoney'])return MBISReturn("申请退款金额不能大于实支付金额");
		//查看退款申请是否已存在
		$orfId = $this->where('orderId',$orderId)->value('id');
		Db::startTrans();
		try{
			$result = false;
			//如果退款单存在就进行编辑
			if($orfId>0){
				$object = $this->get($orfId);
				$object->refundReson = $reason;
				if($reason==10000)$object->refundOtherReson = $content;
				$object->backMoney = $money;
				$object->refundStatus = 0;
				$result = $object->save();
			}else{
				$data = [];
				$data['orderId'] = $orderId;	
	            $data['refundTo'] = 0;
	            $data['refundReson'] = $reason;
	            if($reason==10000)$data['refundOtherReson'] = $content;
	            $data['backMoney'] = $money;
	            $data['createTime'] = date('Y-m-d H:i:s');
	            $data['refundStatus'] = ($order['orderStatus']==-1)?1:0;
	            $result = $this->save($data);
			}			
            if(false !== $result){
            	//拒收申请退款的话要给商家发送信息
            	if($order['orderStatus']!=-1){
				     MBISSendMsg($order['userId'],"订单【".$order['orderNo']."】用户申请退款，请及时处理。",['from'=>1,'dataId'=>$orderId]);
			    }
            	Db::commit();
                return MBISReturn('您的退款申请已提交，请留意退款信息',1);
            }
		}catch (\Exception $e) {
		    Db::rollback();
	    }
	    return MBISReturn('操作失败',-1);
	}

	/**
	 * 获取订单价格以及申请退款价格
	 */
	public function getRefundMoneyByOrder($orderId = 0){
		return Db::name('orders')->alias('o')->join('__ORDER_REFUNDS__ orf','orf.orderId=o.orderId')->where('orf.id',$orderId)->field('o.orderId,orderNo,goodsMoney,deliverMoney,totalMoney,realTotalMoney,orf.backMoney')->find();
	}

	/**
	 * 商家处理是否同意退款
	 */
	public function shoprefund(){
        $id = (int)input('id');
        $refundStatus = (int)input('refundStatus');
        $content = input('content');
        if($id==0)return MBISReturn('无效的操作');
        if(!in_array($refundStatus,[1,-1]))return MBISReturn('无效的操作');
        if($refundStatus==-1 && $content=='')return MBISReturn('请输入拒绝原因');
        Db::startTrans();
        try{
        	$object = $this->get($id);
            $object->refundStatus = $refundStatus;
            if($object->refundStatus==-1)$object->shopRejectReason = $content;
            $result = $object->save();
            if(false !== $result){
            	//如果是拒收话要给用户发信息
            	if($refundStatus==-1){
            		$order = Db::name('orders')->where('orderId',$object->orderId)->field('userId,orderNo,orderId')->find();
					MBISSendMsg($order['userId'],"订单【".$order['orderNo']."】商家不同意退款，原因：".$content."。",['from'=>1,'dataId'=>$order['orderId']]);
            	}
            	Db::commit();
            	return MBISReturn('操作成功',1);
            }
        }catch (\Exception $e) {
        	print_r($e);
		    Db::rollback();
	    }
	    return MBISReturn('操作失败',-1);
        

	}
}
