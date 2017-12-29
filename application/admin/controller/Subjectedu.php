<?php
namespace application\admin\controller;
use application\admin\model\SubjectEdu as M;
use application\admin\model\School as School;
use application\admin\model\Major as Major;
use application\admin\model\AdItem as AdItem;
use application\admin\model\CourseItem as CourseItem;
use application\admin\model\UserRanks as UserRanks;
/**
 * 学历类自考科目
 */
class Subjectedu extends Base{
	
/************************************************************************/
/*
 *学历类自考类科目处理方法   
 */
    public function indexEdu(){
        $m = new M();
        $type_id = 1;
        $this->assign("type_id",$type_id);
        return $this->fetch("listedu");
    }
    public function pageQueryEdu(){
        $m = new M();
        return $m->pageQueryEdu();
    }
    public function toEditEdu(){
        $m = new M();
        $id = Input("id/d",0);
        $type_id = Input('type_id/d',0);
        $res = $m->getSubjectOne($id);
        $this->assign('type_id',$type_id);
        $this->assign('object',$res);
        return $this->fetch("editedu");
    }
    public function addEdu(){
        $m = new M();
        return $m -> addEdu();
    }
    public function editEdu(){
        $m = new M();
        return $m -> editEdu();
    }
    public function delEdu(){
        $m = new M();
        return $m -> delEdu();
    }
}
