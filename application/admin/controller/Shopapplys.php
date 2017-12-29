<?php
namespace application\admin\controller;
use application\admin\model\ShopApplys as M;
/**
 * 店铺申请控制器
 */
class Shopapplys extends Base{
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
     * 获取菜单
     */
    public function get(){        
    	$m = new M();
    	return $m->get((int)Input("post.id"));
    }
    /**
     * 跳去处理页面
     */
    public function toHandle(){
    	$m = new M();
    	$rs = $m->getById((int)Input("get.id"));
    	$this->assign("object",$rs);
    	return $this->fetch("edit");
    }
    /**
     * 编辑菜单
     */
    public function handle(){
    	$m = new M();
    	return $m->handle();
    }
    /**
     * 删除菜单
     */
    public function del(){
    	$m = new M();
    	return $m->del();
    }
}
