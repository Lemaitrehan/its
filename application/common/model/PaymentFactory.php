<?php

namespace application\common\model;
use application\common\interfaces\IPayment;
use application\common\model\PaymentConfig;

/**
 * 支付接口工厂
 * @author jodendy
 *
 */
class PaymentFactory implements IPayment{
	
	private static $payObj = null;
	
	/* (non-PHPdoc)
     * @see \application\common\interfaces\IPayment::callback()
     */
    public function callback($pay_id, $callback_data, &$msg)
    {
        $doPay = $this->_init($pay_id, $msg);
        if(!$doPay){
            return false;
        }
        return $doPay->callback($pay_id, $callback_data, $msg);
    }

 /* (non-PHPdoc)
     * @see \application\common\interfaces\IPayment::dopay()
     */
    public function dopay($pay_id, $order_data, &$msg)
    {
        $doPay = $this->_init($pay_id, $msg);
        if(!$doPay){
            return false;
        }
        // TODO Auto-generated method stub
        return $doPay->dopay($pay_id, $order_data, $msg);
    }

    /* (non-PHPdoc)
     * @see \application\common\interfaces\IPayment::return_back()
     */
    public function return_back($pay_id, $callback_data, &$msg)
    {
        // TODO Auto-generated method stub
        $doPay = $this->_init($pay_id, $msg);
        if(!$doPay){
            return false;
        }
        return $doPay->return_back($pay_id, $callback_data, $msg);
        
    }
    public static function getInstance(){
        if(!self::$payObj)self::$payObj = new self();
        return self::$payObj;
    }
	private function _init($pay_id,&$msg){
	    //支付代码
	    $doPay = PaymentConfig::getPayMenthod($pay_id);
	    if($doPay){
	        return $doPay;
	    }else{
	        $msg = '支付方式不存在';
	        return false;
	    }
	}
 
	
}