<?php
namespace application\admin\controller;
use application\admin\model\Employee as M;
/**
 * 员工管理控制器
 */
class Employee extends Base{
	
    public function index(){
        $m = new M();
        $department = $m->get_department_list();
        $this->assign('department',$department);
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
        //dump($rs);die;
        $department_list = $m->get_department_list();
        //dump($department_list);die;
        $employeetype_list = $m->get_employeetype_list();
        //$businesscenter_list = $m->get_businesscenter_list();
        $this->assign("department_list",$department_list);
        $this->assign("employeetype_list",$employeetype_list);
        //$this->assign("businesscenter_list",$businesscenter_list);
        $this->assign("object",$rs);
        //查找后台数据
        $arrStaffs  = db('staffs')->field('staffId,staffName,staffNo')->select();
        $this->assign('arrStaffs',$arrStaffs);
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
    /**
     * ajax操作
     */
    public function checkType(){
        $m = new M();
        return $m->checkType();
    }
    public function checkdep(){
        $m = new M();
        return $m->checkdep();
    }

    
}
