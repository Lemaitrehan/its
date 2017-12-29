<?php
namespace application\admin\controller;
use application\admin\model\OrderRefunds as M;
/**
 * 退款订单控制器
 */
class Orderrefunds extends Base{
    /**
     * 退款列表
     */
    public function refund(){
    	$areaList = model('areas')->listQuery(0); 
    	$this->assign("areaList",$areaList);
    	return $this->fetch("list");
    }
    public function refundPageQuery(){
        $m = new M();
        return $m->refundPageQuery();
    }
    /**
     * 跳去退款界面
     */
    public function toRefund(){
    	$m = new M();
    	$object = $m->getInfoByRefund();
    	$this->assign("object",$object);
    	return $this->fetch("box_refund");
    }
    /**
     * 退款
     */
    public function orderRefund(){
    	$m = new M();
        return $m->orderRefund();
    }
}
