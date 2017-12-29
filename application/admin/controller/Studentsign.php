<?php
namespace application\admin\controller;
use application\admin\model\Studentsign as M;
// +----------------------------------------------------------------------
// | 报名管理
// +----------------------------------------------------------------------
// | Author: lijianhua
// +----------------------------------------------------------------------
class Studentsign extends Base{
    //学历报名信息
    public function indexEducation (){
        $m = new M();
        if( request()->isAjax() ){
          return $eduInfo = $m->getEduInfo();
        }
        $this->assign('type',1);
        return $this->fetch('educationList');
    }
    //技能报名信息
    public function indexSkill(){
         $m = new M();
         if( request()->isAjax() ){
             return $eduInfo = $m->getSkillInfo();
         }
         $this->assign('type',2);
         return $this->fetch('skilList');
    }      
}
