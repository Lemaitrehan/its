<?php
namespace  application\common\model;

use application\common\Component\Payment\Alipay\F2f\pay;
class PaymentConfig{
	
	/**
	 * 此参数和系统支付ID一致，如果不一致可能导致错误。
	 * @var array
	 */
	 static $payments = array(
	 	'1'=>array('name'=>'支付宝当面付','namespace'=>'Alipay\F2f',),
	 	'2'=>array('name'=>'微信支付扫码PC支付','namespace'=>'Wxpay\Scan',),
        '12'=>array('name'=>'微信APP支付','namespace'=>'Wxpay\App',),
	 );
	
	/**
	 * 微信支付扫码PC支付。
	 * 此参数和系统支付ID一致，如果不一致可能导致错误。
	 * @var pay
	 */
	 public static function getPayMenthod($pay_id){
	     if(isset(self::$payments[$pay_id])){
	         $pay = 'application\common\Component\Payment\\'.self::$payments[$pay_id]['namespace'].'\pay';
	         $dopay = new $pay();
	         return $dopay;
	     }
	     return false;
	 }
	 /**
	  * 获取支付接口描述。
	  * @param int $pay_id
	  * @return multitype:|NULL|array
	  */
	 public static function getPayDesc($pay_id){
	     if(isset(self::$payments[$pay_id])){
	         return self::$payments[$pay_id];
	     } 
	     return null;
	 }
}