<?php
namespace application\api\controller;
use application\common\model\CashDraws as M;
/**
* 提现记录控制器
 */
class Cashdraws extends Base{
    /**
     * 查看用户资金流水
     */
	public function index(){
		return $this->fetch('users/cashdraws/list');
	}
    /**
     * 获取用户数据
     */
    public function pageQuery(){
        $userId = (int)session('MBIS_USER.userId');
        $data = model('CashDraws')->pageQuery(0,$userId);
        return MBISReturn("", 1,$data);
    }

    /**
     * 跳转提现页面
     */
    public function toEdit(){
        $this->assign('accs',model('CashConfigs')->listQuery(0,(int)session('MBIS_USER.userId')));
        return $this->fetch('users/cashdraws/box_draw');
    }

    /**
     * 提现
     */ 
    public function drawMoney(){
        $m = new M();
        return $m->drawMoney();
    }
}
