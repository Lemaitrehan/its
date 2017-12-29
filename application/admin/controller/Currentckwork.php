<?php
namespace application\admin\controller;
use application\admin\model\CurrentCkwork as M;
/**
 * 学员/老师考勤记录管理控制器
 */
class Currentckwork extends Base{
	
    public function indexEdu(){
        $m = new M();
        $type_id = 1;
        $objects = $m->get_course_list($type_id);
        $this->assign('objects',$objects);
        $this->assign('type_id',$type_id);
        return $this->fetch("list");
    }

    public function indexSkill(){
        $m = new M();
        $type_id = 2;
        $objects = $m->get_course_list($type_id);
        $this->assign('objects',$objects);
        $this->assign('type_id',$type_id);
        return $this->fetch("list");
    }
    public function index_t(){
        $m = new M();
        //$list = $m->get_info_listt();
        //dump($list);die;
        return $this->fetch("listt");
    }
    /**
     * 获取分页
     */
    public function pageQuery(){
        $m = new M();
        return $m->pageQuery();
    }
    public function pageQueryT(){
        $m = new M();
        return $m->pageQueryT();
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
        $type_id = input('get.type_id');
        $this->assign('type_id',$type_id);
        $rs = $m->getById(Input("id/d",0));
        $course_list = $m->get_course_list($type_id);
        $student_list = $m->get_student_list($type_id);
        $this->assign("course_list",$course_list);
        $this->assign("student_list",$student_list);
        $this->assign("object",$rs);
        return $this->fetch("edit");
    }
    public function toEditt(){
        $m = new M();
        $rs = $m->getById(Input("id/d",0));
        $subject_list = $m->get_subject_list();
        $teacher_list = $m->get_teacher_list();
        $this->assign("subject_list",$subject_list);
        $this->assign("teacher_list",$teacher_list);
        $this->assign("object",$rs);
        return $this->fetch("editt");
    }
    /**
     * 新增
     */
    public function add(){
        $m = new M();
        return $m->add();
    }
    public function addt(){
        $m = new M();
        return $m->addt();
    }
    /**
    * 修改
    */
    public function edit(){
        $m = new M();
        return $m->edit();
    }
    public function editt(){
        $m = new M();
        return $m->editt();
    }
    /**
     * 删除
     */
    public function del(){
        $m = new M();
        return $m->del();
    }

    /**
     * 获取输入的学员信息
     */
    public function search(){
        $m = new M();
        return $m->search();
    }
}
