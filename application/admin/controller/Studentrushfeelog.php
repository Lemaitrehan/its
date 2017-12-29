<?php
namespace application\admin\controller;
use application\admin\model\StudentRushFeeLog as M;
/**
 * 付费记录管理控制器
 */
class Studentrushfeelog extends Base{
	
    public function index(){
        $m = new M();
        //$list = $m->get_info_list();
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
        $userlist = $m->get_user_lists();
        $this->assign("userlist",$userlist);
        $noticetmpl_list = $m->get_noticetmpl_list();
        $this->assign("noticetmpl_list",$noticetmpl_list);
        $this->assign("object",$rs);
        return $this->fetch("edit");
    }
    /**
     * 跳去某学员付费记录信息详情页
     */
    public function toDetail(){
        return $this->fetch("detail");
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
     * ajax获取用户基本信息
     */
    public function getInfo(){
        $m = new M();
        return $m->getInfo();
    }

    public function chooseTmpl(){
        $m = new M();
        return $m->chooseTmpl();
    }

}
