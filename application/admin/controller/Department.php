<?php
namespace application\admin\controller;
use application\admin\model\Department as M;
/**
 * 部门管理控制器
 */
class Department extends Base{
	
    public function index(){
        $m = new M();
        $list = $m->get_lists();
        return $this->fetch("list");
    }
    /**
     * 获取分页
     */
    public function pageQuery(){
        $m = new M();
        return $m->pageQuery();
    }
    /*
    * 获取数据
    */
    public function getlists(){
        $m = new M();
        return $m->getById(Input("id/d",0));
    }
    /**
     * 跳去编辑页面
     */
    public function toEdit(){
        $m = new M();
        $rs = $m->getById(Input("id/d",0));
        $department_list = $m->getDepartmentList();
        $this->assign('department_list',$department_list);
        $this->assign("object",$rs);
        return $this->fetch("edit");
    }
    /**
     * 新增
     */
    public function add(){
        $m = new M();
        return $m->add();
    }
    /**
    * 修改
    */
    public function edit(){
        $m = new M();
        return $m->edit();
    }
    /**
     * 删除
     */
    public function del(){
        $m = new M();
        return $m->del();
    }

    
}
