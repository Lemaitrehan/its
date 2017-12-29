<?php
namespace application\admin\controller;
use application\admin\model\StudentAudition as M;
/**
 * 试听管理控制器
 */
class Studentaudition extends Base{
	
    public function index(){
        $m = new M();
        $courselist = $m->get_course_list();
        $this->assign("courselist",$courselist);
        $subjectlist = $m->get_subject_list();
        $this->assign("subjectlist",$subjectlist);
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
        $this->assign("object",$rs);
        $userlist = $m->get_user_list();
        $this->assign("userlist",$userlist);
        $majorlist = $m->get_major_list();
        $this->assign("majorlist",$majorlist);
        $courselist = $m->get_course_list();
        $this->assign("courselist",$courselist);
        $subjectlist = $m->get_subject_list();
        $this->assign("subjectlist",$subjectlist);
        $employeelist = $m->get_employee_list();
        $this->assign("employeelist",$employeelist);
        //dump($courselist);
        //dump($rs);
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

    public function getemployeeInfo(){
        $m = new M();
        return $m->getemployeeInfo();
    }

    public function getcoursesubjectInfo(){
        $m = new M();
        return $m->getcoursesubjectInfo();
    }

    public function getcourseInfo(){
        $m = new M();
        return $m->getcourseInfo();
    }

}
