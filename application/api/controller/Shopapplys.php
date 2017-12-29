<?php
namespace application\api\controller;
use application\common\model\ShopApplys as M;
use application\common\model\LogSms;
/**
* 门店申请控制器
 */
class Shopapplys extends Base{	
	/**
	 * 判断手机或邮箱是否存在
	 */
	public function checkShopPhone(){
		$m = new M();
		$userId = (int)session('MBIS_USER.userId');
		$rs = $m->checkShopPhone($userId);
		if($rs["status"]==1){
			return array("ok"=>"");
		}else{
			return array("error"=>$rs["msg"]);
		}
	
	}
	
	/**
	 * 获取验证码
	 */
	public function getPhoneVerifyCode(){
		$userPhone = input("post.userPhone2");
		$rs = array();
		if(!MBISIsPhone($userPhone)){
			return MBISReturn("手机号格式不正确!");
			exit();
		}
		$m = new M();
		$rs = $m->checkShopPhone($userPhone,(int)session('MBIS_USER.userId'));
		if($rs["status"]!=1){
			return MBISReturn("对不起，该手机号已提交过开店申请，如有疑问请与商城管理员联系!");
			exit();
		}
		
		$phoneVerify = rand(100000,999999);
		$msg = "欢迎您申请成为".MBISConf("CONF.mallName")."商家，您的注册验证码为:".$phoneVerify."，请在10分钟内输入。【".MBISConf("CONF.mallName")."】";
		$m = new LogSms();
		$rv = $m->sendSMS(0,$userPhone,$msg,'getPhoneVerifyCode',$phoneVerify);
		
		if($rv['status']==1){
			session('VerifyCode_shopPhone',$phoneVerify);
			session('VerifyCode_shopPhone_Time',time());
		}
		return $rv;
	}
	
	
	/**
	 * 提交申请
	 */
	public function apply(){
	
		$m = new M();
		$rs = $m->addApply();
		return $rs;
	
	}
	
	/**
	 * 跳到用户注册协议
	 */
	public function protocol(){
		return $this->fetch("shop_protocol");
	}
}
