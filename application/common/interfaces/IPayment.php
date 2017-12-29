<?php
namespace application\common\interfaces;

interface IPayment{
	
	/**
	 * @param integer $pay_id
	 * @param \ArrayObject $order_data
	 * @param string unknown $msg
	 */
	public function dopay($pay_id,$order_data,&$msg);
	public function callback($pay_id,$callback_data,&$msg);
	public function return_back($pay_id,$callback_data,&$msg);
}