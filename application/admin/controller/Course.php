<?php
namespace application\admin\controller;
use application\admin\model\Course as M;
use application\admin\model\Subject as Subject;
use application\admin\model\School as School;
use application\admin\model\Major as Major;
use application\admin\model\CourseSubject as CourseSubject;
use application\admin\model\AdItem as AdItem;
use application\admin\model\CourseItem as CourseItem;
use application\admin\model\UserRanks as UserRanks;
/**
 * 课程控制器
 */
class Course extends Base{
    
    public function index(){
        $m = new M();
        $school = new School();
        $type_id = Input('type_id/d',0);
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
        //是否上架下拉数据
        $this->assign('sel_is_shelves',ITSGetSelData('course','is_shelves'));
        //上课方式下拉数据
        $this->assign('sel_teaching_type',ITSGetSelData('course','teaching_type'));
        $this->assign('type_id',Input('type_id/d',0));
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
        $this->assign('id',$id);
        $type_id = Input('type_id/d',0);
        $this->assign('type_id',Input('type_id/d',0));
        $rs = $m->getById($id);
        //是否上架下拉数据
        $this->assign('sel_is_shelves',ITSGetSelData('course','is_shelves'));
        //上课方式下拉数据
        $this->assign('sel_teaching_type',ITSGetSelData('course','teaching_type'));
        //专业层次下拉数据
        $this->assign('sel_level_type',ITSGetSelData('major','level_type'));
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
        $lists_major = $major->get_lists();
        $this->assign("lists_major",$lists_major);
        //科目列表
        $subject = new Subject();
        $lists_subject = $subject->get_lists(['subject_type_id'=>$type_id]);
        $this->assign("lists_subject",$lists_subject);
        $lists_subject_type =$subject->get_subject_type_lists();
        $this->assign("lists_subject_type",$lists_subject_type); 
        $arrUserDiv = array();
        $subject_js_ids = '';
        $arrKs = array();
        if($id>0){
           $arrUserDiv =  $m->getJnCourseSubject($id);
           if($arrUserDiv){
               foreach ($arrUserDiv as $v){
                   $arr_s[] = $v['subject_id'];
                   $arrKs[$v['subject_id']] = $v['course_hours'];
               }
               $subject_js_ids =  implode(',', $arr_s);
           }
            $is_sell = $rs['is_shelves'];
        }else{
            $is_sell = '';
        }
        $this->assign('is_sell',$is_sell);
        $this->assign('arrUserDiv',$arrUserDiv);
        $this->assign('subject_js_ids',$subject_js_ids);
        $this->assign('arrKs',json_encode($arrKs));
        //学杂费列表
        $adItem = new AdItem();
        $lists_ad_item = $adItem->get_lists();
        $this->assign("lists_ad_item",$lists_ad_item);
        //html转换
        if(isset($rs['details']))
        {
            $rs['details'] = htmlspecialchars_decode($rs['details']);
        }
        //已选中的科目项 线下
        $courseSubject = model('common/CourseSubject');
        $rs['subject_ids'] = [];
        $subject_ids = $courseSubject->get_subject_ids($id,1);
        //$subject_ids = $courseSubject->get_subject_names($id,1);
        if(!empty($subject_ids))
        {
            //$subject_ids = implode(';',$subject_ids);
            $rs['subject_ids'] = $subject_ids;
        }
        
        //已选中的科目项 线上
        $rs['online_subject_ids'] = [];
        $online_subject_ids = $courseSubject->get_subject_ids($id,2);
        if(!empty($online_subject_ids))
        {
            $rs['online_subject_ids'] = $online_subject_ids;
        }
        
        //已选中的学杂费
        $rs['it_ids'] = [];
        if($id > 0)
        {
            $courseItem = new CourseItem();
            $it_ids = $courseItem->get_it_ids($type_id,$id,0);
            if($it_ids)
            {
                $rs['it_ids'] = $it_ids;
            }
        }
        //dump($rs);die;
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
     * 弹出科目/学杂费列表页
     */
    public function subjects(){
        $m = new M();
        return $m->getSubjects();
    }
    public function getSubjectList(){
        $m = new M();
        return $m->getSubjectList();
    }
    public function getAdItemList(){
        $m = new M();
        return $m->getAdItemList();
    }
    public function subjectSelect(){
        $m = new M();
        return $m->subjectSelect();
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
        $type_id = Input("type_id/d",0);
        $school_id = Input("school_id/d",0);
        $filter = ['school_id'=>$school_id,'is_sell'=>'1','closed'=>'0'];
        if(isset($_POST['level_type']) && $type_id==1)
        {
           $level_type = Input("level_type/d",0);
           $filter['level_type'] = $level_type; 
        }
        $lists_major = $major->get_lists($filter);
        $this->assign("type_id",$type_id);
        $this->assign("major_id",Input("major_id/d",0));
        $this->assign("lists_major",$lists_major);
        return array('status'=>1,'msg'=>'加载完成','html'=>$this->fetch("course/major/lists"));
    }
    //班级列表
    public function get_grade_list()
    {
        $major = new Major();
        $major_id = Input("major_id/d",0);
        $rs_major = $major->getById($major_id);
        $m = new M();
        $lists_grade = $m->getGradeLists();
        $this->assign("grade_id",Input("grade_id/d",0));
        $this->assign("lists_grade",$lists_grade);
        return array('status'=>1,'msg'=>'加载完成','html'=>$this->fetch("course/grade/lists"),'data'=>['exam_type'=>$rs_major['exam_type']]);
    }

    //专业列表
    public function getMajors(){
        $m = new M();
        return $m->getMajors();
    }
    
    ########################################################################################################
    /*
    *学历类课程管理功能处理
    */
    public function indexEdu(){
        $m = new M();
        $exam_type = session('examType');

        $type_id = 1;
        $this->assign("type_id",$type_id);

        $school = $m->getSchool($type_id,$exam_type); //院校列表
        $this->assign('school',$school);

        $major = $m->getMajor($type_id,$exam_type); //专业列表
        $this->assign('major',$major);

        $grade = $m->getGrade($exam_type); //年级列表
        $this->assign('grade',$grade);

        return $this->fetch("listedu");
    }
    public function pageQueryEdu(){
        $m = new M();
        return $m->pageQueryEdu();
    }
    public function toEditEdu(){
        $m = new M();
        $exam_type = session('examType');
        $id = Input("id/d",0);
        $type_id = Input('type_id/d',0);
        if($id){
            $arrInfo   = $m->getCourseInfo($id);
            $school_id = !empty($arrInfo)?$arrInfo['school_id']:'';
            $is_sell   = $arrInfo['is_shelves'];
        }else{
            $school_id = '';
            $is_sell   = '';
        }
        
        $this->assign('is_sell',$is_sell);
        //学习成绩
        $studyMode = $m->studyMode;
        $this->assign('studyMode',$studyMode);
        
        $school = $m->getSchool($type_id,$exam_type); //院校列表
        $this->assign('school',$school);
        if($school_id){
            $major = $m->getMajorList($school_id); //专业列表
            $major = empty($major['data'])?'':$major['data'];
        }
        
        $this->assign('major',!empty($major)?$major:'');

        $grade = $m->getGrade($exam_type); //年级列表
        $this->assign('grade',$grade);
        $res = $m->getCourseOne($id);
        $this->assign('exam_type',$exam_type);
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

    public function getMajorList(){
        $m = new M();
        return $m -> getMajorList(); 
    }

    public function getLevel(){
        $m = new M();
        return $m-> getLevel();
    }

    public function setCourseName(){
        $m = new M();
        return $m -> setCourseName();
    }
    

    //数据导出
    function export(){
        set_time_limit(0);
        $sjexamsObj = new M();
        $filename ='课程导出';
        $data = array();
        $data[] = array('课程名称','课程编号','院校','专业','层次','学习形式','考试类型');
        $res  = $sjexamsObj->pageQueryEdu(1);
        foreach($res['Rows'] as $key => $v ){
            $data[] =  array(
                $v['name'],
                $v['course_bn'],
                $v['school_name'],
                $v['major_name'],
                $v['level_type'],
                $v['studyMode'],
                $v['exam_type'],
            );
        }
        $data = array_values($data);
        array_excel($filename, $data);
        exit;
    }

    public function upSell(){
        $m = new M();
        return $m->upSell();
    }
    
}

