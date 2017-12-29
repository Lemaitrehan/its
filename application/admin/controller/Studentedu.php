<?php
namespace application\admin\controller;
use application\admin\model\StudentEdu as M;
// +----------------------------------------------------------------------
// | 报名管理
// +----------------------------------------------------------------------
// | Author: liuyaping
// +----------------------------------------------------------------------
class Studentedu extends Base{
    //学历报名信息
    public function indexEdu(){
        $exam_type = session('examType');
        $m = new M();
        if( request()->isAjax() ){
          return $eduInfo = $m->getEduInfo();
        }
        $grade = $m->getGrade($exam_type); //年级列表
        $this->assign('grade',$grade);

        $school = $m->getSchool($exam_type); //院校列表
        $this->assign('school',$school);

        $major = $m->getMajor($exam_type); //专业列表
        $this->assign('major',$major);
        $this->assign('exam_type',$exam_type);
        $this->assign('type_id',1);
        return $this->fetch('educationlist');
    }

    public function pageQuery(){
        $m = new M();
        return $m->pageQuery();
    }

    public function toEdit(){
        $m = new M();
        $this->assign('type_id',1);
        $edu_id = input('get.id');
        $res = $m->getInfoOne($edu_id);
        $this->assign('data',$res);
        return $this->fetch('edit');
    }

    public function edit(){
        $m = new M();
        return $m->edit();
    }  

    public function majorGet(){
        $m = new M();
        return $m->majorGet();
    }

    public function levelGet(){
        $m = new M();
        return $m->levelGet();
    }    
}
