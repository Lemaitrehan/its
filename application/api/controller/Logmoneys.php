<?php
namespace application\api\controller;
use application\common\model\LogMoneys as M;
/**
* 资金流水控制器
 */
class Logmoneys extends Base{
    /**
     * 查看用户资金流水
     */
	public function usermoneys(){
		$rs = model('Users')->getFieldsById((int)session('MBIS_USER.userId'),['lockMoney','userMoney']);
		$this->assign('object',$rs);
		return $this->fetch('users/logmoneys/list');
	}
    /**
     * 查看用户资金流水
     */
    public function shopmoneys(){
        $rs = model('Shops')->getFieldsById((int)session('MBIS_USER.shopId'),['lockMoney','shopMoney','noSettledOrderFee','paymentMoney']);
        $this->assign('object',$rs);
        return $this->fetch('shops/logmoneys/list');
    }
    /**
     * 获取用户数据
     */
    public function pageUserQuery(){
        $userId = (int)session('MBIS_USER.userId');
        $data = model('logMoneys')->pageQuery(0,$userId);
        return MBISReturn("", 1,$data);
    }
    /**
     * 获取商家数据
     */
    public function pageShopQuery(){
        $shopId = (int)session('MBIS_USER.shopId');
        $data = model('logMoneys')->pageQuery(1,$shopId);
        return MBISReturn("", 1,$data);
    }
}
