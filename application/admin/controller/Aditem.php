<?php
namespace application\admin\controller;
use application\admin\model\AdItem as M;
/**
 * 学杂费/服务信息管理控制器
 */
class Aditem extends Base{
	
    public function index(){
        $m = new M();
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
        /*
        $department_list = $m->get_department_list();
        $employeetype_list = $m->get_employeetype_list();
        $businesscenter_list = $m->get_businesscenter_list();
        $this->assign("department_list",$department_list);
        $this->assign("employeetype_list",$employeetype_list);
        $this->assign("businesscenter_list",$businesscenter_list);
        */
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

    public function expAditem(){
        $m = new M();
        return $m->expAditem();
    }

    
}
