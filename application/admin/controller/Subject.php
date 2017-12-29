<?php
namespace application\admin\controller;
use application\admin\model\Subject as M;
use application\admin\model\School as School;
use application\admin\model\Major as Major;
use application\admin\model\AdItem as AdItem;
use application\admin\model\CourseItem as CourseItem;
use application\admin\model\UserRanks as UserRanks;
/**
 * 学校控制器
 */
class Subject extends Base{
	
    public function index(){
        $m = new M();
        $school = new School();
        $type_id = Input('type_id/d',0);
        $major_id = Input('major_id/d',0);
        $school_id = $m->getMajorSchoolId($major_id);
        if($school_id){
            $this->assign("school_id",$school_id);
        }
        $where_school['jump_type'] = $type_id;
        if($type_id==1)
        {
           $where_school['is_nav'] = '0';    
        }
        $lists_school = $school->get_lists($where_school);
        $this->assign("lists_school",$lists_school);
        $major = new Major();
        $where_major['type_id'] = $type_id;
        /*
        if($type_id==1)
        {
            $where_major['is_show'] = '0';
        }
        */
        $lists_major = $major->get_list($where_major);
        $this->assign('lists_major',$lists_major);
        //老师列表
        $teacher_lists = $m->get_teacher_lists();
        $this->assign('teacher_lists',$teacher_lists);
        //是否上架下拉数据
        $this->assign('sel_is_shelves',ITSGetSelData('course','is_shelves'));
        //上课方式下拉数据
        $this->assign('sel_teaching_type',ITSGetSelData('course','teaching_type'));
        $this->assign("type_id",Input("type_id/d",0));
        $this->assign("major_id",Input("major_id/d",0));
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
        $id = Input("id/d",0);
        $type_id = Input('type_id/d',0);
        $school_id = Input('school_id/d',0);
        $major_id = Input('major_id/d',0);
        $this->assign("type_id",Input("type_id/d",0));
        $arrPublicSubject = $m->arrPublicSubject;
        $this->assign("arrPublicSubject",$arrPublicSubject);
        //自考类添加科目业务需要
        $this->assign("school_id",$school_id);
        $school = $m->get_school_name($school_id);
        $this->assign('school',$school);
        $this->assign("major_id",Input("major_id/d",0));
        $major = $m->get_major_name($major_id);
        $this->assign('major',$major);
        //是否上架下拉数据
        $this->assign('sel_is_shelves',ITSGetSelData('subject','is_shelves'));
        //上课方式
        $this->assign('sel_teaching_type',ITSGetSelData('subject','teaching_type'));
        $rs = $m->getById($id);
        //学校列表
        $school = new School();
        $where_school['jump_type'] = $type_id;
        if($type_id==1)
        {
           $where_school['is_nav'] = '0';    
        }
        $lists_school = $school->get_lists($where_school);
        $this->assign("lists_school",$lists_school);
        //专业列表
        $major = new Major();
        $where = [];
        $where['type_id'] = $type_id;
        $lists_major = $major->get_lists($where);
        $this->assign("lists_major",$lists_major);
      
        //科目列表 2017-4-8 查看 无使用
        /* $lists_subject = $m->get_lists(['subject_type_id'=>2]);
        $this->assign("lists_subject",$lists_subject); */
        //dd(66666);
        //学杂费列表
        $adItem = new AdItem();
        $lists_ad_item = $adItem->get_lists();
        $this->assign("lists_ad_item",$lists_ad_item);
   
        //会员等级列表
        $userRank = new UserRanks();
        $lists_lv = $userRank->get_lists($id);
        $this->assign("lists_lv",$lists_lv);
        //优惠方式
        $lists_discount = $m->get_discount_setting($id);
        if($lists_discount)
        {
            $rs['offer_type_ids'] = $lists_discount['type'];   
        }else{
            $rs['offer_type_ids'] = '';
        }
        //html转换
        if(isset($rs['details']))
        {
            $rs['details'] = htmlspecialchars_decode($rs['details']);
        }
        //前置条件
        if(isset($rs['front_ids']))
        {
            $rs['front_ids'] = explode(',',$rs['front_ids']);
        }
        //已选中的学杂费
        if($id > 0)
        {
            $courseItem = new CourseItem();
            $it_ids = $courseItem->get_it_ids($type_id,0,$id);
            if($it_ids){
                $rs['it_ids'] = $it_ids;
            }else{
                $rs['it_ids'] = '';
                $rs['it_ids'] = explode(',',$rs['it_ids']);
            }
        }else{
            $rs['it_ids'] = '';
            $rs['it_ids'] = explode(',',$rs['it_ids']);
        }
        //老师列表
        $this->assign("lists_teacher",$m->get_teacher_lists());
        $lists_subject_type =$m->get_subject_type_lists();
        $this->assign("lists_subject_type",$lists_subject_type);
        if($id > 0){
            $rs['school'] = '';
            $rs['school'] = $m->get_school_name($rs['school_id']);
            $rs['major'] = '';
            $rs['major'] = $m->get_major_name($rs['major_id']);
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
    /**
     * 科目属性列表
     */
    public function get_subject_prop_data(){
        $m = new M();
        $lists_subject_prop = $m->get_subject_prop_data(Input("type_id/d",0),Input("subject_id/d",0));
        $this->assign("lists_subject_prop",$lists_subject_prop);
        return array('status'=>1,'msg'=>'加载完成','html'=>$this->fetch("prop"));
    }
    /**
     * 优惠条件
     */
    public function get_discount_data(){
        $m = new M();
        $type = Input("type/d",0);
        $subject_id = Input("subject_id/d",0);
        $lists_discount = $m->get_discount_setting($subject_id);
        if(!isset($lists_discount['price']))
        {
           $lists_discount['price'] = ''; 
        }
        if(!isset($lists_discount['discount']))
        {
           $lists_discount['discount'] = ''; 
        }
        $this->assign("lists_discount",$lists_discount);
        return array('status'=>1,'msg'=>'加载完成','html'=>$this->fetch("subject/discount/".$type));
    }
    
    /**
     * 专业列表
    */
    public function get_major_list()
    {
        $major = new Major();
        $school_id = Input("school_id/d",0);
        $lists_major = $major->get_lists(['school_id'=>$school_id]);
        $this->assign("type_id",Input("type_id/d",0));
        $this->assign("major_id",Input("major_id/d",0));
        $this->assign("lists_major",$lists_major);
        return array('status'=>1,'msg'=>'加载完成','html'=>$this->fetch("subject/major/lists"));
    }
    public function getAdItemList(){
        $m = new M();
        return $m->getAdItemList();
    }
}
