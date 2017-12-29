<?php
namespace application\admin\model;
/**
 * 课程业务处理
 */
use think\Db;
use think\Model;
//use application\common\model\CourseSubject as CourseSubject;
use application\admin\model\CourseSubject as CourseSubject;
class Course extends Base{
    public $deposit_price = 500;//课程定金
    //学习形式
    public $studyMode = array(
       1=>'函授',
       2=>'脱产',
       3=>'远程教育',
       4=>'全日制',
       5=>'业余制'
    );
    
    /**
     * 分页
     */
    public function pageQuery(){
        $where = ['closed'=>['<>','1']];
        $type_id = input('get.type_id');
        $school_id = input('get.school_id');
        $major_id = input('get.major_id'); 
        $is_shelves = input('get.is_shelves');
        $name = input('get.name');
        $teaching_type = input('get.teaching_type');

        if($school_id != ''){
            $where['school_id'] = ['=',"$school_id"];
        }
        if($major_id != ''){
            $where['major_id'] = ['=',"$major_id"];
        }
        if($is_shelves != ''){
            $where['is_shelves'] = ['=',"$is_shelves"];
        }
        if($name != ''){
            $where['name'] = ['like',"%$name%"];
        }
        if($teaching_type != ''){
            $where['teaching_type'] = ['=',"$teaching_type"];
        }
        //学历课程类型
        if($type_id=='1')$where['type_id'] = $type_id;
        //非学历课程类型
        if($type_id!='1')$where['type_id'] = ['neq',1];

        $page = $this->where($where)->field('*')->order('lastmodify desc')
        ->paginate(input('post.pagesize/d'))->toArray();
        if(count($page['Rows'])>0){
            $courseSubject = model('CourseSubject');
            foreach ($page['Rows'] as $key => $v){
                $page['Rows'][$key]['major_id'] = model('major')->get_name($v['major_id']);
                $page['Rows'][$key]['school_id'] = model('school')->get_name($v['school_id']);
                $page['Rows'][$key]['is_shelves'] = $v['is_shelves']==1?"<a href='#' onclick='upSell(".$v['course_id'].",2)'>上架</a>":"<a href='#' onclick='upSell(".$v['course_id'].",1)'>下架</a>";
                $page['Rows'][$key]['subject_ids'] = '';
                if(in_array($type_id,[2]))
                {
                    $page['Rows'][$key]['subject_ids'] = $courseSubject->get_subject_names($v['course_id'],true);
                    $page['Rows'][$key]['teaching_type'] = ITSSelItemName('course','teaching_type',$v['teaching_type']);
                }
                if(in_array($type_id,[1])){
                    $page['Rows'][$key]['grade_id'] = $this->getGradeName($v['grade_id']);
                }
            }
        }
        //dump($page);die;
        return $page;
    }
    public function getById($id){
        $rs = [];
        $rs['course_id'] = 0;
        $rs['cover_img'] = '';
        if($id>0)
        {
            $rs = $this->get(['course_id'=>$id]);
        }
        $rs['level_type'] = 0;
        if($id>0 && $rs['type_id'] == 1)
        {
            $rs['level_type'] = model('major')->get_level_type($rs['major_id']);
        }
        return $rs;
    }
    /**
     * 新增
     */
    public function add(){
        $data = input('post.');
        $where = [];
        $course_bn = $data['course_bn'];
        if($course_bn !== ''){
            $where['course_bn'] = $course_bn;
        }
        $res = $this->where($where)->find();
        if($res){
            return MBISReturn('课程编号已存在',-2);
        }
        if($data['type_id'] == 1){
            $data['subject_ids'] = [];
            //$data['it_ids'] = [];
        }else{
            $data['subject_ids'] = explode(',',$data['subject_ids'][0]);
            //$data['it_ids'] = explode(',',$data['it_ids'][0]);
        }
        $subject_ids = isset($data['subject_ids'])?$data['subject_ids']:[];
        //$it_ids = isset($data['it_ids'])?$data['it_ids']:[];
        $data['createtime'] = time();
        $data['lastmodify'] = time();
        //学历类价格处理
        if($data['type_id'] == 1)
        {
            if(isset($data['grade_id']))
            {
                //$rs_grade = model('grade')->get_grade_data(['grade_id'=>$data['grade_id']]);
                //$data['sale_price'] = $rs_grade['stu_fee'];
                //$data['offers_price'] = $rs_grade['offers'];
            }
        }
        //添加线上课程处理
        $online_subject = [];
        $this->pre_online_subject($data,$online_subject);
        //END
        Db::startTrans();
        try{
            //type_id
            if( !$data['major_id'] ){
                exception('专业不能为空');
            }
            $type_id = db::name('major')->where('major_id='.$data['major_id'] )->value('type_id');
            $data['type_id'] = $type_id; 
            MBISUnset($data,'course_id,id,subject_ids');
            
            $result = $this->allowField(true)->save($data);
            $id = $this->course_id;
            if(false !== $result){
                model('CourseSubject')->set_course_subject_value($id,$subject_ids);
                //线上课程价格处理
                !empty($online_subject) && model('CourseSubject')->set_course_subject_value_price($id,$online_subject);
                //model('CourseItem')->set_course_item_value($data['type_id'],$id,0,$it_ids);
                Db::commit();
                return MBISReturn("新增成功", 1);
            }
        }catch (\Exception $e) {
            Db::rollback();
        }
        
        return MBISReturn('新增失败',-1);
    }
    /**
     * 编辑
     */
    public function edit(){
        $id = (int)input('post.course_id');
        $data = input('post.');
        if($data['type_id'] == 1){
            $data['subject_ids'] = [];
            //$data['it_ids'] = [];
        }else{
            $data['subject_ids'] = explode(',',$data['subject_ids'][0]);
            $subject_ids  = $data['subject_ids'];
            //$data['it_ids'] = explode(',',$data['it_ids'][0]);
        }
        $subject_ids = isset($data['subject_ids'])?$data['subject_ids']:[];
        //$it_ids = isset($data['it_ids'])?$data['it_ids']:[];
        $data['lastmodify'] = time();
        MBISUnset($data,'id,subject_ids');
        //添加线上课程处理
        $online_subject = [];
        $this->pre_online_subject($data,$online_subject);
        Db::startTrans();
        try{
            $data['subject_ids'] = $subject_ids;
            
            if( !$data['major_id'] ){
                exception('专业不能为空');
            }
            
            if($data['type_id']==1):
                $type_id = db::name('major')->where('major_id='.$data['major_id'] )->value('type_id');
                $data['type_id'] = $type_id;
            endif;
            
            $result = $this->allowField(true)->save($data,['course_id'=>$id]);
            if(false !== $result){
                model('CourseSubject')->set_course_subject_value($id,$subject_ids);
                //线上课程价格处理
                !empty($online_subject) && model('CourseSubject')->set_course_subject_value_price($id,$online_subject);
                //model('CourseItem')->set_course_item_value($data['type_id'],$id,0,$it_ids);  //此处数据处理还有疑问，待解决。。。
                Db::commit();
                return MBISReturn("编辑成功", 1);
            }
        }catch (\Exception $e) {
            dump($e->getMessage());
            Db::rollback();
        }
        return MBISReturn('编辑失败',-1);  
    }
    /**
     * 删除
     */
    public function del(){
        $type_id = input('post.type_id/d');
        $id = input('post.id/d');
        Db::startTrans();
        try{
            $result = $this->where(['course_id'=>$id])->delete();
            if(false !== $result){
                if($type_id == 2)
                {
                    Db::name('course_subject')->where(['course_id'=>$id])->update(['closed'=>1,'lastmodify'=>time()]);
                }
                Db::commit();
                return MBISReturn("删除成功", 1);
            }
        }catch (\Exception $e) {
            Db::rollback();
            return MBISReturn('删除失败',-1);
        }
    }
    //数据列表
    public function get_lists()
    {
        return $this->select();   
    }
    //班级列表
    public function getGradeLists()
    {
        return model('grade')->get_lists();   
    }
    public function get_name($id=0){
        return $this->where('course_id',$id)->value('name');
    }
    //专业列表
    public function getMajors(){
        $type_id = (int)input('post.type_id');
        $school_id = (int)input('post.school_id');
        $where = [];
        $majors = [];
        if(($type_id != '') && ($school_id != '')){
            //$where['type_id'] = $type_id;
            $where['closed'] = ['eq',0];
            $where['school_id'] = $school_id;
            $majors = Db::name('major')->where($where)->field('major_id,name')->select();
        }
        if($majors){
            return ['data'=>$majors,'status'=>1];
        }else{
            return ['msg'=>'暂无数据','status'=>-1];
        }
    }
    public function getSubjects(){
        $type_id = input('post.type_id');
        $subjects = Db::name('subject')->where('subject_type_id',$type_id)->select();
        if($subjects){
            return ['status'=>1,'data'=>$subjects];
        }  
    }

    public function subjectSelect(){
        $course_id = input('course_id');
        $courseSubject = new CourseSubject();
        $type_id = input('type_id');
        $major_id = input('major_id');
        $post_subject_ids = input('subject_ids');
        //$where = [];
       // $where['subject_type_id'] = $type_id;
       // $where['major_id'] = $major_id;
        $where = " subject_type_id = $type_id  and ( major_id = $major_id OR is_public = 2)";
        $subject = Db::name('subject')->where($where)
                                      ->paginate(input('post.pagesize/d'))
                                      ->toArray();
        $arrCheckSubject = array();
        if($post_subject_ids){
            $arrCheckSubject = explode(',', $post_subject_ids);
        }
        //dump($subject);die;
        foreach($subject['Rows'] as &$v){
            if($course_id !== 0){
                $subject_ids = $courseSubject->get_subject_ids($course_id);
                $v['subject_ids'] = $subject_ids;
            }else{
                $v['subject_ids'] = [];
            }
           /*  if( $arrCheckSubject && isset(in_array($v['subject_id'], $arrCheckSubject)) ){
                $is_check = ' checked';
            }else{
                $is_check = ' checked';
            } */
            if($arrCheckSubject && array_search($v['subject_id'],$arrCheckSubject) !== false ){
                $is_check = ' checked';
            }else{
                $is_check = ' ';
            }
            $v['checkbox'] = '<input '.$is_check.' id="ck_'.$v['subject_id'].'" type="checkbox" name="chk" value="'.$v['subject_id'].'">';
            $v['school_id'] = $this->get_school_name($v['school_id']);
            $v['major_id'] = $this->get_major_name($v['major_id']);
            $v['is_shelves'] = ITSSelItemName('subject','is_shelves',$v['is_shelves']);
            $v['teacher_id'] = $v['teacher_id'] == 0 ? '尚未安排' : $this->get_teacher_name($v['teacher_id']);
        }
        if($subject){
            //dump($subject);die;
            return $subject;
        }else{
            return ['status'=>-1,'msg'=>'科目列表加载失败!'];
        }
    }
    public function getSubjectList(){
        $idList = input('post.');
        $ids = [];
        $cost = '';
        $marketPrice = '';
        $courseHours = '';
        if($idList){
            $ids = $idList['id'];
            $cost = $this->getPrice('cost',$ids);                        //成本价
            $marketPrice = $this->getPrice('market_price',$ids);              //原价
            $courseHours = $this->getHours($ids); //总课时 
        }
        return ['status'=>1,'data'=>$ids,'cost'=>$cost,'marketPrice'=>$marketPrice,'courseHours'=>$courseHours];
    }

    public function getAdItemList(){
        $id_list = input('post.');
        $ids = [];
        if($id_list){   
            $ids = $id_list['id'];
        }
        return ['status'=>1,'data'=>$ids];
    }

    public function getPrice($field='',$ids=[]){  //计算课程成本价\市场价
        $where = [];
        $where['subject_id'] = ['in',$ids];
        $price = Db::name('subject')->where($where)->sum("$field");
        //dump($price);die;
        return $price;
    }
    public function getHours($ids=[]){
        $where = [];
        $where['subject_id'] = ['in',$ids];
        $hours = Db::name('subject')->where($where)->sum('course_hours');
        return $hours;
    }
    public function getGradeName($id=0){
        return Db::name('grade')->where('grade_id',$id)->value('name');
    }
    public function get_teacher_name($id=0){
        $where = [];
        $where['userId'] = $id;
        $where['userType'] = 1;
        $where['dataFlag'] = 1;
        //$where['status'] = 1;
        return $res = Db::name('users')->where($where)->value('trueName'); 
    }
    public function get_school_name($id=0){
        return Db::name('school')->where('school_id',$id)->value('name');
    }
    public function get_major_name($id=0){
        return Db::name('major')->where('major_id',$id)->value('name');
    }
    
    //添加线上课程处理
    public function pre_online_subject(&$data,&$online_subject=[])
    {
        //$online_subject = [];
        if( !empty($data['online_subject_ids']))
        {
            $rs_subject = Db::name('subject')->where(['subject_id'=>['in',$data['online_subject_ids']]])->field('subject_id,sale_price')->select();
            $adver_subject = [];
            $tmp_online_subject = [];
            $subject_sale_price = 0;
            //金额计算
            $online_course_price = $data['online_course_price'];
            foreach($rs_subject as $v)
            {
                $one_subject_sale_price = round($v['sale_price'],2);
                $adver_subject[] = [
                    'id' => $v['subject_id'],
                    'price' => $one_subject_sale_price,
                ];
                $tmp_online_subject[$v['subject_id']] = $one_subject_sale_price;
                //总金额计算
                $subject_sale_price += $one_subject_sale_price;   
            }
            //折扣计算
            $data['online_course_price_type']==2 && $online_course_price = $subject_sale_price*($data['online_course_price']/100);
            $online_subject = get_aver_num($adver_subject,$subject_sale_price,$online_course_price);
        }
        MBISUnset($data,'online_subject_ids');
    }
    
    /**
     * 查找课程基本信息
     * @param unknown $course_id 课程id
     */
    public function getCourseInfo($course_id){
        //科目名称 ，市场价格，优惠价格deposit_price
        $field = 'school_id,major_id,name,market_price,is_shelves';
        $where['course_id'] =  $course_id;
        $res= $this->field($field)->where($where)->find();
        return  $res;
    }
    
    /**
     * 查找课程下面的科目详情
     * @param unknown $course_id  课程id 
     * @return multitype:number string multitype:
     */
    public function getCourseDetails($course_id){
        $where['c.course_id'] =  $course_id;
        //科目名称 ，市场价格，优惠价格，课时，最少定金
        $field ='c.course_id,c.name as course_name,c.cover_img as course_cover_img,
                 c.market_price as course_market_price,c.sale_price,
                 s.name,s.market_price,s.sale_price,s.course_hours';
        $join = array(
                   array('course_subject cs','cs.course_id = c.course_id','LEFT'),
                   array('subject s','s.subject_id = cs.subject_id','LEFT'),
        );
       return  $this->alias('c')->join($join)->field($field)->where($where)->select();
    }
    
    #######################################################################################################
    /*
    *学历类课程重写
    */
    public function pageQueryEdu($is_export=""){
        $exam_type = session('examType');
        $type_id = input('get.type_id');
        $school_id = input('get.school_id');
        $level_type = input('get.level_type');
        $major_id = input('get.major_id');
        //$grade_id = input('get.grade_id');
        $name = input('get.name');
        $course_bn = input('get.course_bn');
        
        $where = ['c.closed'=>['<>',"1"]];
        if($exam_type != '')
        {
            $where['c.exam_type'] = ['=',"$exam_type"];
        }
        if($type_id != '')
        {
            $where['c.type_id'] = ['=',"$type_id"];
        }
        
        if($school_id != '')
        {
            $where['c.school_id'] = ['=',"$school_id"];
        }
        if($level_type != '')
        {
            $where['c.level_type'] = ['=',"$level_type"];
        }
        if($major_id != '')
        {
            $where['c.major_id'] = ['=',"$major_id"];
        }

        // if($grade_id != '')
        // {
        //     $where['g.grade_id'] = ['=',"$grade_id"];
        // }

        if($name != '')
        {
            $where['c.name'] = ['like',"%$name%"];
        }
        if($course_bn != '')
        {
            $where['c.course_bn'] = ['like',"%$course_bn%"];
        }
        
        $field = 'c.is_shelves,c.studyMode,c.course_id,
            c.name,c.course_bn,c.school_id,c.major_id,c.level_type,c.grade_id,
            s.name as school_name,m.name as major_name,c.exam_type';

        $join = [];
        $join = [
            ['school s','c.school_id=s.school_id','left'],
            ['major_edu m','c.major_id=m.major_id','left'],
            ['grade g','c.grade_id=g.grade_id','left']
        ];
        
        $page = $this
                ->alias('c')
                ->join($join)
                ->where($where)
                ->field($field)
                ->order('c.lastmodify desc')
                ->paginate(input('post.pagesize/d'))
                ->toArray();
        if($is_export){
            $page = array();
            $res = $this
                ->alias('c')
                ->join($join)
                ->where($where)
                ->field($field)
                ->order('c.lastmodify desc')
                ->select();
            $page['Rows'] = $res;
        }else{
            $page = $this
                    ->alias('c')
                    ->join($join)
                    ->where($where)
                    ->field($field)
                    ->order('c.lastmodify desc')
                    ->paginate(input('post.pagesize/d'))
                    ->toArray();
            
        }
        $arrStudyMode = $this->studyMode;
        
        if(count($page['Rows'])>0)
        {
            foreach ($page['Rows'] as $key => $v)
            {

                $page['Rows'][$key]['is_shelves']  = $v['is_shelves']==1?"<a href='#' onclick='upSell(".$v['course_id'].",2)'>上架</a>":"<a href='#' onclick='upSell(".$v['course_id'].",1)'>下架</a>";
                $page['Rows'][$key]['studyMode']  = $arrStudyMode[$v['studyMode']];
                $page['Rows'][$key]['level_type'] = $this->getLevelType($v['level_type']);
                $page['Rows'][$key]['exam_type']  = $this->getExamType($v['exam_type']);
            }
        }
        
        return $page;
    }
  
    

    public function getCourseOne($id){ //获取一条数据
        $res = $this->get(['course_id'=>$id]);
        //dd($res);
        if($res)
        {
            $res['start_registration'] = $this->timeDate($res['start_registration']);
            $res['stop_registration'] = $this->timeDate($res['stop_registration']);
            $res['start_execution'] = $this->timeDate($res['start_execution']);
            $res['stop_execution'] = $this->timeDate($res['stop_execution']);
        }
        return $res;
    }

    public function addEdu(){ //新增数据
        $data = input('post.');
        $course_bn = $data['course_bn'];
        if($course_bn !== ''){
            $res = $this->where('course_bn',$course_bn)->find();
            if($res){
                return MBISReturn('课程编号已存在',-2);exit;
            }
        }
        $data['createtime'] = time();
        $data['lastmodify'] = time();
        //$data['start_registration'] = $data['start_registration'] ? strtotime($data['start_registration']) : time();
        //$data['stop_registration'] = $data['stop_registration'] ? strtotime($data['stop_registration']) : time();
        $data['start_execution'] = $data['start_execution'] ? strtotime($data['start_execution']) : time();
        $data['stop_execution'] = $data['stop_execution'] ? strtotime($data['stop_execution']) : time();
        if( $data['studyMode'] <=0 ){
            return MBISReturn('学习方式不能为空',-1);
        }
        //$data['exam_type'] = session('examType');
        MBISUnset($data,'course_id');
        Db::startTrans();
        try{
            $result = $this->allowField(true)->save($data);
            if(false !== $result)
            {
                Db::commit();
                return MBISReturn("新增成功", 1);
            }
        }catch (\Exception $e) {
            Db::rollback();
        }
        return MBISReturn('新增失败',-1);
    }
    
    public function editEdu(){ //编辑数据
        $data = input('post.');
        $id = (int)input('post.course_id');
        $course_bn = trim($data['course_bn']);
        if($course_bn !== ''){
            $res = $this->where('course_bn',$course_bn)->find();
            if($res && ($res['course_id'] !== $id)){
                return MBISReturn('课程编号已存在',-2);exit;
            }
        }
        $data['lastmodify'] = time();
        //$data['start_registration'] = $data['start_registration'] ? strtotime($data['start_registration']) : time();
        //$data['stop_registration'] = $data['stop_registration'] ? strtotime($data['stop_registration']) : time();
        $data['start_execution'] = $data['start_execution'] ? strtotime($data['start_execution']) : time();
        $data['stop_execution'] = $data['stop_execution'] ? strtotime($data['stop_execution']) : time();
        MBISUnset($data,'course_id');
        
        if( $data['studyMode'] <=0 ){
            return MBISReturn('学习方式不能为空',-1);
        }
        //$data['exam_type'] = session('examType');
        Db::startTrans();
        try{
            $result = $this->allowField(true)->save($data,['course_id'=>$id]);
            if(false !== $result)
            {
                Db::commit();
                return MBISReturn("编辑成功", 1);
            }
        }catch (\Exception $e) {
            Db::rollback();
        }
        return MBISReturn('编辑失败',-1);
    }

    public function delEdu(){ //删除数据
        $id = input('post.id/d');
        Db::startTrans();
        try{
            $result = $this->where(['course_id'=>$id])->update(['closed'=>1,'lastmodify'=>time()]);
            if(false !== $result)
            {
                Db::commit();
                return MBISReturn("删除成功", 1);
            }
        }catch (\Exception $e) {
            Db::rollback();
            return MBISReturn('删除失败',-1);
        }
    }

    public function getSchool($type_id,$exam_type){ //院校列表
        $where = [];
        $where['s.jump_type'] = ['=',"$type_id"];
        $where['s.exam_type'] = ['=',"$exam_type"];

        $field = 's.school_id,s.name';
        $join = [];
        $join = [
            ['major_edu m','FIND_IN_SET(s.school_id,m.school_ids)','left']
        ];
        $res = Db::name('school')
                    ->alias('s')
                    ->join($join)
                    ->where($where)
                    ->field($field)
                    ->group('s.school_id')
                    ->select();
        //getLastSql();
        //dd($res);
        return $res;
    }

    public function getMajor($type_id,$exam_type){ //专业列表
        $where = [];
        $where['m.exam_type'] = ['=',"$exam_type"];

        $field = 'm.major_id,m.name';

        $res = Db::name('major_edu')
                    ->alias('m')
                    ->where($where)
                    ->field($field)
                    ->select();
        return $res;
    }

    public function getGrade($exam_type){ //年级列表
        $where = [];
        $where['g.exam_type'] = ['=',"$exam_type"];
        $field = 'g.grade_id,g.name';
        $res = Db::name('grade')
                    ->alias('g')
                    ->where($where)
                    ->field($field)
                    ->select();
        return $res;
    }

    public function getMajorList($school_id=""){ //ajax动态加载专业列表
        if( !$school_id ){
            $school_id = input('post.school_id');
        }
        
        $where = 'FIND_IN_SET('."$school_id".',school_ids)';
        $field = 'm.major_id,m.name';

        $res = Db::name('major_edu')
                    ->alias('m')
                    ->where($where)
                    ->field($field)
                    ->select();
        if($res)
        {
            return ['data'=>$res,'status'=> 1];
        }
        else
        {
            return ['msg'=>'数据加载失败','status'=> -1];
        }
    }

    public function getLevel(){  //获取专业的层次信息
        $major_id = input('post.major_id');
        $where = [];
        $where['major_id'] = ['=',"$major_id"];
        $levels = Db::name('major_edu_extend')
                        ->where($where)
                        ->field('level_id')
                        ->select();
        if(!empty($levels)){
            foreach($levels as &$v){
                $v['level_name'] = $this->getLevelType($v['level_id']);
            }
            return ['data'=>$levels,'status'=>1];
        }else{
            return ['msg'=>'数据加载失败','status'=>-1];
        }
    }

    public function setCourseName(){ //ajax生成课程名称
        $exam_type = session('examType');
        $school_id = input('post.school_id');
        $level_type = input('post.level_type');
        $major_id = input('post.major_id');
        $major_id = input('post.studyMode');
        
        $course_name = $this->getExamType($exam_type).'--';//1
        if($school_id){
            $course_name .= $this->getSchoolName($school_id).'--';//2
        }
        if($level_type){
            $course_name .= $this->getLevelType($level_type).'--';
        }
        if($major_id){
            $course_name .= $this->getMajorName($major_id);
        }
        return ['data'=>$course_name,'status'=>1];
    }

    public function getExamType($type){ //考试类型
        switch($type)
        {
            case 1:return '自考';
            case 2:return '成考';
            case 3:return '网教';
        }
    }
    public function getLevelType($type){ //层次
        switch($type)
        {
            case 2:return '高升专';
            case 3:return '专升本';
        }
    }

    public function getSchoolName($id){
        return Db::name('school')->where('school_id',$id)->value('name');
    }
    public function getMajorName($id){
        return Db::name('major_edu')->where('major_id',$id)->value('name');
    }
    public function timeDate($time){
        return date('Y-m-d',(int)$time);
    }
    
    //
    public function  getJnCourseSubject($id){
        $join = array(
            array('course_subject b','b.course_id=a.course_id','left'),
            array('subject c','c.subject_id=b.subject_id','left'),
        );
        $res =  $this->alias('a')
             ->field('c.subject_id,c.name,c.course_hours')
             ->join($join)
             ->where('a.course_id='.$id)
             ->select();
         return $res;
    }

    public function upSell()
    {
        $id = input('post.id/d');
        $type_id = input("post.type_id/d");
        Db::startTrans();
        try{
            $result = $this->where(['course_id'=>$id])->update(['is_shelves'=>$type_id,'lastmodify'=>time()]);
            if(false !== $result){
                Db::commit();
                return MBISReturn("变更成功", 1);
            }
        }catch (\Exception $e) {
            Db::rollback();
            return MBISReturn('变更失败',-1);
        }
    }

}
