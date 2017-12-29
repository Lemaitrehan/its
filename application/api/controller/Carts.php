<?php
namespace application\api\controller;
use application\common\model\Carts as itsCarts;
/**
* 购物车控制器
 */
class Carts extends Base{
    /**
    * 添加购物车
    */
	public function setCart(){
		$carts = new itsCarts();
		$rs = $carts->getApiSetCart($this->getCurReqParams(),$this->getCurUserInfo());
		MBISApiReturn($rs);
	}
	/**
	 * 查看购物车列表
	 */
	public function getCartList(){
		$carts = new itsCarts();
		$rs = $carts->getApiCartList(1,$this->getCurReqParams(),$this->getCurUserInfo());
		MBISApiReturn($rs);
	}
    /**
	 * 结合协议 && 查看购物车列表
	 */
	public function getStatementData(){
		$carts = new itsCarts();
		$rs = $carts->getApiStatementData($this->getCurReqParams(),$this->getCurUserInfo());
		MBISApiReturn($rs);
	}
	/**
	 * 删除购物车里的商品
	 */
	public function delCart(){
		$carts = new itsCarts();
		$rs= $carts->getApiDelCart($this->getCurReqParams(),$this->getCurUserInfo());
		MBISApiReturn($rs);
	}
    /**
     *清空购物车
     */
    public function deleteAllcart(){
        $car = new itsCarts();
        $rs = $car->delAllInfo();
        MBISApiReturn($rs);
    }
    /**
	 * @do 统计当前用户的购物车数量
     * 根据jump_type判断
	 */
	public function getCartNums(){
		$carts = new itsCarts();
		$rs= $carts->getCartNums($this->getCurReqParams(),$this->getCurUserInfo());
		MBISApiReturn($rs);
	}
}
