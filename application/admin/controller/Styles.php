<?php
namespace application\admin\controller;
use application\admin\model\Styles as M;
/**
 * 风格配置控制器
 */
class Styles extends Base{
	
    public function index(){
    	$m = new M();
    	$list = $m->listQuery();
        $this->assign("list",$list);
    	return $this->fetch();
    }
    
    /**
     * 保存
     */
    public function changeStyle(){
    	$m = new M();
    	return $m->changeStyle();
    }
}
