<?php
namespace application\admin\controller;
use application\admin\model\Grade as M;
use application\admin\model\Major as Major;
/**
 * 年级控制器
 */
class Grade extends Base{
	
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
    public function toEdit(){
        $m = new M();
        $rs = $m->getById(Input("id/d",0));
        //专业列表
        $major = new Major();
        $lists_major = $major->get_lists();
        $this->assign("lists_major",$lists_major);
        if(isset($rs['rp_des']))
        {
            $rs['rp_des'] = htmlspecialchars_decode($rs['rp_des']);
        }
        $this->assign("object",$rs);
        return $this->fetch("edit");
    }
    /*
    * 获取数据
    */
    public function get(){
        $m = new M();
        return $m->getById(Input("id/d",0));
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

#########################################################################################################
    /*
    *学历类年级管理
    */
    public function indexEdu(){
        return $this->fetch("listedu");
    }
    /**
     * 获取分页
     */
    public function pageQueryEdu(){
        $m = new M();
        return $m->pageQueryEdu();
    }
    
    /**
     * 跳去编辑页面
     */
    public function toEditEdu(){
        $m = new M();
        $rs = $m->getGradeOne(Input("id/d",0));
        $this->assign("object",$rs);
        return $this->fetch("editedu");
    }

    /*
    * 获取数据
    */
    public function getOne(){
        $m = new M();
        return $m->getGradeOne(Input("id/d",0));
    }

    /**
     * 新增
     */
    public function addEdu(){
        $m = new M();
        return $m->addEdu();
    }

    /**
    * 修改
    */
    public function editEdu(){
        $m = new M();
        return $m->editEdu();
    }

    /**
     * 删除
     */
    public function delEdu(){
        $m = new M();
        return $m->delEdu();
    }


    
}
