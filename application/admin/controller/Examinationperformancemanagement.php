<?php
namespace application\admin\controller;
use application\admin\model\School;
use application\admin\model\examination as Mexaminationmanagement;
use think\Controller;
use think\Db;
// +----------------------------------------------------------------------
// | 报名管理
// +----------------------------------------------------------------------
// | Author: lijianhua
// +----------------------------------------------------------------------
class Examinationperformancemanagement extends Base{
    
    //学历报靠信息
    public function indexEducation(){
        if( request()->isAjax() ){
          return $eduInfo = $this->treeData(1);
        }
        $this->assign('type',1);
        return $this->fetch('list');
    }
    
    //添加学历报考
    public function addEducation (){
        if( request()->isAjax() ){
             $Examinationmanagement = new Mexaminationmanagement();
             $id = $Examinationmanagement->add(input('post.'),1);
             if($id){
                 return MBISReturn("操作成功", 1);
             }else{
                 return MBISReturn("操作失败", 0);
             }
        }
        $this->assign('type',1);
        $this->assign('grade_id',input('get.grade_id'));
        return $this->fetch('edit');
    }
    
    //技能报名信息
    public function indexSkill(){
         $m = new M();   
         if( request()->isAjax() ){
             return $eduInfo = $m->getEduInfo();
         }
         $this->assign('type',2);
         return $this->fetch('skilList');
    }
    
    //加载数据
    public function treeData($exam_type){
        $SchoolObj = new School();
        //查找 学历类的学校 或者 是 技术类的学校
        if( input('post.find') ){
           return $SchoolObj->getSchoolType($exam_type);
        //查找学校下面的专业
        }elseif( input('post.school_id') ){
            return  $SchoolObj->getSchoolMajor( input('post.school_id') );
        //查找学校的年级   
        }elseif( input('post.major_id') ){
            return  $SchoolObj->getSchoolMajorGrade( input('post.major_id') );
        //查找班级的考试    
        }elseif( input('post.grade_id') ){
            $Mexaminationmanagement = new Mexaminationmanagement();
            return  $Mexaminationmanagement->getGradeExamination( input('post.grade_id') );
        //查找学员    
        }elseif( input('get.action') == 'student' ){
           $Mexaminationmanagement =  new \application\admin\model\SjExams();
           return $Mexaminationmanagement->studentExams();
        }
        return $SchoolObj->getSchoolType($exam_type);
        
        /* $rs = Db::name('sj_exams ks')->join('subject km','ks.subject_id = km.subject_id','LEFT')
        ->field('km.subject_id,km.name')
        ->select();
        return $rs; */
    }
   
}
