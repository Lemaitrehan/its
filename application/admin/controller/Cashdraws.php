<?php
namespace application\admin\controller;
use application\admin\model\CashDraws as M;
/**
 * 提现控制器
 */
class Cashdraws extends Base{

    public function index(){
    	return $this->fetch("list");
    }

    /**
     * 获取分页
     */
    public function pageQuery(){
        $m = new M();
        return $m->pageQuery();
    }

    /**
     * 跳去编辑页面
     */
    public function toHandle(){
        //获取该记录信息
        $m = new M();
        $this->assign('object', $m->getById());
        return $this->fetch("edit");
    }
    
    /**
    * 修改
    */
    public function handle(){
        $m = new M();
        return $m->handle();
    }
}
