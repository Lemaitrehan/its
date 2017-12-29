<?php
namespace application\admin\controller;
use application\admin\model\Major as M;
use application\admin\model\School as School;
/**
 * 学校控制器df
 */

class Major extends Base{
	
    //学历类x
    public function index(){
        $major = new M();
        if( request()->isAjax() ){
            $m = new M();
            return $m->pageQueryNew();
        }
        $list_major = $major->get_major_list();
        $this->assign("lists_school",$list_major);
        $this->assign("type_id",Input("type_id/d",0));
        $this->assign('exam_type', session('examType'));
    	return $this->fetch("listNew");
    }
    
    //学历类 编辑
    public function toEditEducation(){
        
        //提交编辑数据
        if( request()->isAjax() ){
             $major = new M();
             return $major->editData();
        }
        
        $id      = input('id');
        $arrInfo = db('major_edu')->where('major_id = '.$id)->find();
        $this->assign('arrInfo', $arrInfo );
        $this->assign('exam_type', $arrInfo['exam_type'] );
    
        //查找学校
        $arrSchool = db('school')->where('school_id','in',$arrInfo['school_ids'] )->select();
        $this->assign('arrSchool', $arrSchool );
     
        //层次
        $join     = array(
             array('subject_edu b','FIND_IN_SET(b.subject_id,a.subject_ids)','left'),
        );
        $field    = 'a.id,a.level_id,a.subject_ids,a.graduate_time,
                     b.subject_id,b.name as subject_name';
        $arrLevel = db('major_edu_extend')->alias('a')
                                    ->join($join)
                                    ->field($field)
                                    ->where('a.major_id','=',$id )
                                    ->select();
        $arrNew = array();
        $arrNew[ 2 ]['level_id']      =  '';
        $arrNew[ 2 ]['graduate_time'] =  '';
        $arrNew[ 2 ]['subject_ids']   =  '';
        $arrNew[ 2 ]['subject']       =  array();
        $arrNew[ 3 ]['level_id']      =  '';
        $arrNew[ 3 ]['graduate_time'] =  '';
        $arrNew[ 3 ]['subject_ids']   =  '';
        $arrNew[ 3 ]['subject']       =  array();
    
        foreach ( $arrLevel as $key => $v ){
            $arrNew[ $v['level_id'] ]['level_id']      =  $v['level_id'];
            $arrNew[ $v['level_id'] ]['graduate_time'] =  $v['graduate_time'];
            $arrNew[ $v['level_id'] ]['subject_ids']   =  $v['subject_ids'];
            $arrNew[ $v['level_id'] ]['subject'][]     =  array(
                                                       'subject_id'   => $v['subject_id'],
                                                       'subject_name' => $v['subject_name'],
                                                    );
        }
        $this->assign('arrLevel', $arrNew );
        
        $this->assign('school_ids',$arrInfo['school_ids']);
        $this->assign('subject_ids1',isset($arrNew[2]['subject_ids'])?$arrNew[2]['subject_ids']:'');
        $this->assign('subject_ids2',isset($arrNew[3]['subject_ids'])?$arrNew[3]['subject_ids']:'');
        
        return $this->fetch("add");
    }
    
    
    /*
     * 学历类 专业 添加
     */
    public function toAdd(){
        $m = new M();
    
        if( request()->isAjax() ){
            $action = input('action');
            //查找学校
            if( $action == 'getSchool' ){
                return  $school_list = $m->getShoolList();
                //查找科目
            }elseif( $action == 'getSubjectList' ){
                return  $SubjectList = $m->selectSubjectList();
                //提交数据
            }else{
                $rs = $m->saveInfo();
                return $rs;
            }
        }
        $this->assign('exam_type', session('examType'));
        $this->assign('arrInfo',1);
        $this->assign('is_sell',1);
        $this->assign('arrSchool', array() );
        $this->assign('arrLevel', 1 );
        $this->assign('school_ids','');
        $this->assign('subject_ids1','');
        $this->assign('subject_ids2','');
        return $this->fetch('add');
    }
    
    
    /**
     * 跳去编辑页面
     */
    public function toEdit(){
        $m = new M();
        $school = new School();
        $type_id = Input('type_id/d',0);
        $this->assign("type_id",Input("type_id/d",0));
        $rs = $m->getById(Input("id/d",0));
        $where_school['jump_type'] = $type_id;
        if($type_id==1)
        {
           $where_school['is_nav'] = '0';    
        }
        $lists_school = $school->get_lists($where_school);
        $sel_data = $m->get_sel_data();
        $this->assign("lists_school",$lists_school);
        $this->assign("sel_data",$sel_data);
        $this->assign("object",$rs);
        return $this->fetch("edit");
    }
    //学历专业删除
    public  function delEducation(){
         $major_id = input('id');
         $m = new M();
         return $m->delEducation($major_id);
    }
    
    /*
     * 删除 学历类数据
     */
    public function toDel(){
        $m = new M();
        return $m->toDel();
    }
    ###############################################################################
    
    //------------技能类专业----------------
    public function skilllist(){
        
        $m = new M();
        if( request()->isAjax() ){
            $m = new M();
            return $m->pageQuery();
        }
        $school = new School();
        $type_id = Input('type_id/d',0);
        $where_school['jump_type'] = $type_id;
        if($type_id==1)
        {
            $where_school['is_nav'] = '0';
        }
        //$lists_school = $school->get_lists($where_school);
       // $this->assign("lists_school",$lists_school);
        $this->assign("type_id",Input("type_id/d",0));
        
        return $this->fetch("list");
    }
    
    //添加专业
    public function toAddSkill(){
        $m = new M();
        if($_POST){
            return $m->add();
        }
        $lists_subject_type =model('admin/subject')->get_subject_type_lists();
        $this->assign("lists_subject_type",$lists_subject_type); 
        //查找技能类的学校
        $schoolObj = new School();
        $lists_school = $schoolObj->get_lists_skill();
        $this->assign('lists_school',$lists_school);
        $this->assign('object',null);
       return $this->fetch('edit');
    }
    
    //编辑专业
    public function toEditSkill(){
        
        $m = new M();
        if($_POST){
            return $m->edit();
        }
        $id = input('get.id');
        if(!$id){
           return MBISReturn('参数错误',-1);      
        }
        $lists_subject_type =model('admin/subject')->get_subject_type_lists();
        $this->assign("lists_subject_type",$lists_subject_type); 
        //查找基本数据
        $arrInfo = db('major')->where('major_id='.$id)->find();
        //查找技能类的学校
        $schoolObj = new School();
        $lists_school = $schoolObj->get_lists_skill();
        $this->assign('lists_school',$lists_school);
        $this->assign('object',$arrInfo);
        //$this->assign('a',2);
        //echo $arrInfo['is_show'];exit;
        //echo $arrInfo['is_show']=='0';exit;
        #echo $arrInfo['is_show'];exit;
       // dd(!isset($arrInfo['type_id']) );exit;
        return $this->fetch('edit');
    }
    
    //删除专业
    public function toDelSkill(){
        $m = new M();
        return $m->del();
    }
    
    
    /*
     * 保存数据
     */
    public function toSave(){
        $m = new M();
        $rs = $m->toSave();
    }
    /*
     * 学院列表
     */
    public function getSchoolList(){
        $m = new M();
        $rs = $m->queryInfo();
        return $rs;
    }
    public function toChoseSchool(){
        return $this->fetch('chose');
    }
    /*
     * 获取数据
     */
    public function getInfo(){
        $m = new M();
        $rs = $m->selectSchool();
        return $rs;
    }
    public function getFormInfo(){
        $m = new M();
        $rs = $m->saveInfo();
        return $rs;
    }
    public function getSubjectList(){
       $m = new M();
       $rs = $m->selectSubjectList();
       return $rs;
    }
    public function checkMajor(){
        $m = new M();
        $this->assign('major_id', input('get.major_id'));
        $this->assign('type_id',2);
        return $this->fetch('searchM');
    }
    public function checkList(){
        $m = new M();     
        return $m->checkMajorList();
    }
    public function selectSubjectlist(){
        $data = input('get.');
        $this->assign('level',$data['level']);
        $this->assign('major_id',$data['major_id']);        
        return $this->fetch('subject_list');
    }
    public function selectHistoryPrice(){
        return $this->fetch('subject_list');
    }
    public function pageQuerySubjectList(){
        $m = new M();
        return $m->pageQuerySubjectList();
    }

    //上下架
    public function upSell(){
        $m = new M();
        return $m->upSell();
    }

    //上下架(学历类)
    public function upSellEdu(){
        $m = new M();
        return $m->upSellEdu();
    }
}
