<?php
namespace application\admin\controller;
use application\admin\model\Users as M;
use application\admin\model\Major as Major;
use application\admin\model\Grade as Grade;
use application\admin\model\School as School;
/**
 * 会员控制器
 */
class Users extends Base{

	public function indexEdu(){
        $m = new M();
        $type_id = 1;
        $schools = $m->get_schools($type_id);
        $this->assign('type_id',$type_id);
        $this->assign('schools',$schools);
        return $this->fetch("listu");
    }
    public function indexSkill(){
        $m = new M();
        $type_id = 2;
        $schools = $m->get_schools($type_id);
        $this->assign('type_id',$type_id);
        $this->assign('schools',$schools);
        return $this->fetch("listu");
    }
    public function index_t(){
    	return $this->fetch("listt");
    }
    public function index_z(){
        $m = new M();
        $department = $m->get_department_list();
        $this->assign('department',$department);
        return $this->fetch("listz");
    }
    /**
     * 查看学员信息
     */
    public function userInfo(){
        $userId = (int)input('userId');
        $type_id = (int)input('type_id');
        $m = new M();
        $this->assign('userId',$userId);
        $this->assign('type_id',$type_id);
        //基础信息&扩展信息
        $userInfo = $m->getBasicExtendInfo($userId);
        //dd($userInfo);
        $this->assign('userInfo',$userInfo);
        //基础信息
        $basicInfo = $m->getBasic($userId);
        $this->assign('basicInfo',$basicInfo);
        //扩展信息
        $extendInfo = $m->getExtend($userId);
        $this->assign('extendInfo',$extendInfo);
        //学历报名信息
        $eduInfo = $m->getEduInfo($userId);
        $this->assign('eduInfo',$eduInfo);
        //技能报名信息
        $skillInfo = $m->getSkillInfo($userId);
        $this->assign('skillInfo',$skillInfo);
        //考勤记录
        $ckworkInfo = $m->getCkworkInfo($userId);
        $this->assign('ckworkInfo',$ckworkInfo);
        //缴费记录
        $feeInfo = $m->getFeeInfo($userId);
        $this->assign('feeInfo',$feeInfo);
        return $this->fetch('userinfo');
    }
    /**
     * 处理学员报名信息
     */
    public function listEdu(){  //学员学历报名信息列表
        $userId = (int)input('userId');
        $this->assign('userId',$userId);
        return $this->fetch("listedu");
    }
    public function listSkill(){  //学员技能报名信息列表
        $userId = (int)input('userId');
        $this->assign('userId',$userId);
        return $this->fetch("listskill");
    }
    public function pageQueryE(){ //学历分页
        $m = new M();
        $userId = (int)input('userId');
        return $m->pageQueryE($userId);
    }
    public function pageQueryS(){ //技能分页
        $m = new M();
        $userId = (int)input('userId');
        return $m->pageQueryS($userId);
    }
    public function toEdu(){ //跳去新增/编辑页面
        $m = new M();
        $userId = (int)input('userId');
        $this->assign('userId',$userId);
        //学校列表(学历类)
        $school = new School();
        $lists_school_edu = $school->get_lists_edu();
        $this->assign("lists_school_edu",$lists_school_edu);
        //专业列表
        $major = new Major();
        $lists_major = $major->get_lists();
        $this->assign("lists_major",$lists_major);
        //课程列表
        $lists_course = $m->get_course_lists();
        $this->assign("lists_course",$lists_course);
        //年级列表
        $lists_grade = $m->get_grade_lists();
        $this->assign("lists_grade",$lists_grade);
        $data = $this->getedu();
        $assign = ['data'=>$data];
        return $this->fetch("editedu",$assign);
    }
    public function toSkill(){ //跳去新增/编辑页面
        $m = new M();
        $userId = (int)input('userId');
        $this->assign('userId',$userId);
        //学校列表(技能类)
        $school = new School();
        $lists_school_skill = $school->get_lists_skill();
        $this->assign("lists_school_skill",$lists_school_skill);
        //专业列表
        $major = new Major();
        $lists_major = $major->get_lists();
        $this->assign("lists_major",$lists_major);
        //课程列表
        $lists_course = $m->get_course_lists();
        $this->assign("lists_course",$lists_course);
        $data = $this->getskill();
        $assign = ['data'=>$data];
        return $this->fetch("editskill",$assign);
    }
    public function getedu(){
        $m = new M();
        return $m->getedu((int)input('id'));
    }
    public function getskill(){
        $m = new M();
        return $m->getskill((int)input('id'));
    }
    public function addedu(){ //增加
        $m = new M();
        return $m->addedu();
    }
    public function addskill(){ //增加
        $m = new M();
        return $m->addskill();
    }
    public function editedu(){  //修改
        $m = new M();
        return $m->editedu();
    }
    public function editskill(){  //修改
        $m = new M();
        return $m->editskill();
    }
    public function deledu(){  //删除
        $m = new M();
        return $m->deledu();
    }
    public function delskill(){  //删除
        $m = new M();
        return $m->delskill();
    }

    /**
     * 获取分页
     */
    public function pageQueryU(){
        $m = new M();
        return $m->pageQueryU();
    }
    public function pageQueryT(){
        $m = new M();
        return $m->pageQueryT();
    }
    public function pageQueryZ(){
        $m = new M();
        return $m->pageQueryZ();
    }
    /**
     * 跳去新增页面
     */
    public function toAdd(){
        $m = new M();
        //学校列表(学历类/技能类)
        $type_id = input('get.type_id');
        $this->assign('type_id',$type_id);
        $school = new School();
        $lists_school_edu = $school->get_lists_edu();
        $lists_school_skill = $school->get_lists_skill();
        $this->assign("lists_school_edu",$lists_school_edu);
        $this->assign("lists_school_skill",$lists_school_skill);
        //专业列表
        $major = new Major();
        $lists_major = $major->get_lists();
        $this->assign("lists_major",$lists_major);
        //课程列表
        $lists_course = $m->get_course_lists();
        $this->assign("lists_course",$lists_course);
        //年级列表
        $lists_grade = $m->get_grade_lists();
        $this->assign("lists_grade",$lists_grade);
        //会员等级
        $ranklist = $m->get_rank_lists();
        $this->assign('ranklist',$ranklist);
        $data = $this->getu();
        $assign = ['data'=>$data];
        return $this->fetch("addu",$assign);
    }
    /**
     * 跳去编辑页面
     */
    public function toEditu(){
        $userId = (int)input('id');
        $type_id = (int)input('type_id');
        $m = new M();
        
        $this->assign('userId',$userId);
        $this->assign('type_id',$type_id);
        //学校列表(学历类/技能类)
        $school = new School();
        $lists_school_edu = $school->get_lists_edu();
        $lists_school_skill = $school->get_lists_skill();
        $this->assign("lists_school_edu",$lists_school_edu);
        $this->assign("lists_school_skill",$lists_school_skill);
        //专业列表
        $major = new Major();
        $lists_major = $major->get_lists();
        $this->assign("lists_major",$lists_major);
        //课程列表
        $lists_course = $m->get_course_lists();
        $this->assign("lists_course",$lists_course);
        //年级列表
        $lists_grade = $m->get_grade_lists();
        $this->assign("lists_grade",$lists_grade);
        //学员学历报名信息
        $edulist = $m->getEduInfo($userId);
        $this->assign('edulist',$edulist);
        //学员技能报名信息
        $skilllist = $m->getSkillInfo($userId);
        $this->assign('skilllist',$skilllist);
        //学历报名信息
        $eduInfo = $m->getEduInfo($userId);
        $this->assign('eduInfo',$eduInfo);
        //技能报名信息
        $skillInfo = $m->getSkillInfo($userId);
        $this->assign('skillInfo',$skillInfo);
        //会员等级
        $ranklist = $m->get_rank_lists();
        $this->assign('ranklist',$ranklist);
        $data = $this->getu();
        //dump($data);die;
        $assign = ['data'=>$data];
        return $this->fetch("editu",$assign);
    }
    /**
     *老师授课科目配置     
     */
    public function toTeacherSet(){
        $m = new M();
        $userId = (int)input('id');
        $this->assign('userId',$userId);
        $teacher = $m->getTeacherInfo($userId);
        $this->assign('teacher',$teacher);
        $subject = $m->getSubjects();
        $this->assign('subject',$subject);
        return $this->fetch('teacherset');
    }
    public function getSetSubject(){
        $m = new M();
        return $m->getSetSubject();
    }
    public function addTeacherSet(){
        $m = new M();
        return $m->addTeacherSet();
    }
    public function delTeacherSet(){
        $m = new M();
        return $m->delTeacherSet();
    }

    public function toEditt(){
        $m = new M();
        $data = $this->gett();
        $assign = ['data'=>$data];
        return $this->fetch("editt",$assign);
    }
    public function toEditz(){
        $m = new M();
        $departmentlist = $m->get_department_list();
        $this->assign('departmentlist',$departmentlist);
        $employeetypelist = $m->get_employeetype_list();
        $this->assign('employeetypelist',$employeetypelist);
        $employeelist = $m->get_employee_list();
        $this->assign('employeelist',$employeelist);
        $data = $this->getz();
        $assign = ['data'=>$data];
        return $this->fetch("editz",$assign);
    }
    /*
    * 获取数据
    */
    public function getu(){
        $m = new M();
        return $m->getById((int)Input("id"));
    }
    public function gett(){
        $m = new M();
        return $m->getInfo((int)Input("id"));
    }
    public function getz(){
        $m = new M();
        return $m->get_zxs((int)Input("id"));
    }
    /**
     * 新增
     */
    public function addu(){
        $m = new M();
        return $m->addu();
    }
    public function addt(){
        $m = new M();
        return $m->addt();
    }
    public function addz(){
        $m = new M();
        return $m->addz();
    }
    /**
    * 修改
    */
    public function editu(){
        $m = new M();
        return $m->editu();
    }
    public function editt(){
        $m = new M();
        return $m->editt();
    }
    public function editz(){
        $m = new M();
        return $m->editz();
    }
    /**
     * 删除
     */
    public function delu(){
        $m = new M();
        return $m->delu();
    }
    public function delt(){
        $m = new M();
        return $m->delt();
    }
    public function delz(){
        $m = new M();
        return $m->delz();
    }
    /**********************************************************************************************
      *                                             账号管理                                                                                                                              *
      **********************************************************************************************/
    /**
    * 账号管理页面
    */
    public function accountIndex(){
        return $this->fetch("account_list");
    }
    /**
     * 判断账号是否存在
     */
    public function checkLoginKey(){
        $basic = Input('post.');
    	$rs = MBISCheckLoginKey($basic['basic']['loginName'],Input('post.userId/d',0));
    	if($rs['status']==1){
    		return ['ok'=>$rs['msg']];
    	}else{
    		return ['error'=>$rs['msg']];
    	}
    }
    /**
    * 是否启用
    */
    public function changeUserStatus($id, $status){
        $m = new M();
        return $m->changeUserStatus($id, $status);
    }
    public function editAccount(){
        $m = new M();
        return $m->edit();
    }
    /**
    * 获取所有用户id
    */
    public function getAllUserId()
    {
        $m = new M();
        return $m->getAllUserId();
    }

    /**
     * ajax获取级联数据
     */
    public function checkSchool(){
        $m = new M();
        return $m->checkSchool();
    }
    public function checkMajor(){
        $m = new M();
        return $m->checkMajor();
    }
    public function checkCourse(){
        $m = new M();
        return $m->checkCourse();
    }
    /**
     * ajax查询学员考勤信息
     */
    public function dateSelect(){
        $m = new M();
        return $m->dateSelect();
    }

    public function expUsersU(){ //导出1
        $m = new M();
        return $m->expUsersU();
    }
    public function expUsers(){ //导出2
        $m = new M();
        return $m->expUsers();
    }
    public function InfoDownload(){  //数据导出Excel下载文件的函数
        header("Content-type:text/html;charset=utf-8");
        $path = input('get.path');  //存储路径
        $file=input('get.file');  //文件名称
        $file_path = $path.$file;
        if(!file_exists($file_path))
        {
            echo "文件不存在或已丢失";
            return ;
        } 
        $fp=fopen($file_path,"r");
        $file_size=filesize($file_path);
        //下载文件需要用到的头
        Header("Content-type: application/octet-stream");
        Header("Accept-Ranges: bytes");
        Header("Accept-Length:".$file_size);
        Header("Content-Disposition: attachment; filename=".$file);
        $buffer=1024;
        $file_count=0;
        while(!feof($fp) && $file_count<$file_size)
        {
            $file_con=fread($fp,$buffer);
            $file_count+=$buffer;
            echo $file_con;
        }
        fclose($fp); 
    }
    public function checkdep(){
        $m = new M();
        return $m->checkdep();
    }
    public function checkType(){
        $m = new M();
        return $m->checkType();
    }
    public function checkemp(){
        $m = new M();
        return $m->checkemp();
    }
    public function checkname(){
        $m = new M();
        return $m->checkname();
    }

    /*********************************************************************************************************/
    /*********************************************************************************************************/
    /*
    *学历类学籍管理重写
    */
    public function indexUser(){
        $exam_type = session('examType');
        $m = new M();
        $type_id = 1;
        $this->assign('type_id',$type_id);

        $grade = $m->getGrade($exam_type); //年级列表
        $this->assign('grade',$grade);

        $school = $m->getSchool($exam_type); //院校列表
        $this->assign('school',$school);

        $major = $m->getMajor($exam_type); //专业列表
        $this->assign('major',$major);

        $course = $m->getCourse($exam_type); //课程列表
        $this->assign('course',$course);
        $exam_type = session('examType');
        $this->assign('exam_type',$exam_type);
        return $this->fetch('listuser');
    }
    public function pageQueryUser(){
        $m = new M();
        return $m->pageQueryUser();
    }
    public function getUserInfo(){ //查看信息
        $m = new M();
        $Tokey = 'look';  //查看操作
        $id = (int)input('id');
        $res = $m->getInfoOne($id,$Tokey);
        $this->assign('data',$res);

        return $this->fetch('info');
    }
    public function toEditUser(){ //去修改页面
        $exam_type = session('examType');
        $m = new M();

        $grade = $m->getGrade($exam_type); //年级列表
        $this->assign('grade',$grade);

        $school = $m->getSchool($exam_type); //院校列表
        $this->assign('school',$school);

        $major = $m->getMajor($exam_type); //专业列表
        $this->assign('major',$major);
        
        $type_id = input('type_id');
        $this->assign('type_id',$type_id);
        $id = (int)input('id');
        $res = $m->getInfoOne($id);
        $res['graduate_date'] = !empty($res['graduate_date'])?str_replace('1970-01-01','',date('Y-m-d',$res['graduate_date'])):'';
        $this->assign('data',$res);
        return $this->fetch('edituser');
    }

    public function addUser(){ //新增

    }

    public function editUser(){ //修改
        $m = new M();
        return $m->editUser();
    }

    public function majorGet(){ //异步获取专业列表
        $m = new M();
        return $m->majorGet();
    }

    public function levelGet(){  //异步获取专业层次
        $m = new M();
        return $m->levelGet();
    }

    public function expUsersEdu(){  //学历数据导出
        $m = new M();
        return $m->expUsersEdu();
    } 
    
    /**
     * 导入数据
    */
    public function toImport(){
        $this->assign('assign_get',input('get.'));
        return $this->fetch("import");
    }
    public function import()
    {
        if(empty($_FILES['importFile']['tmp_name'])) exit('请选择文件<a href="javascript:history.back()">返回</a>');
        $support_extension = ['xlsx','xls'];
        $pathinfo = pathinfo($_FILES['importFile']['name']);
        if(!in_array(strtolower($pathinfo['extension']),$support_extension)) exit('只支持'.implode('、',$support_extension).'文件<a href="javascript:history.back()">返回</a>');
        $path = $_FILES['importFile']['tmp_name'];
        $post = input('post.');
        $post['key']=='xj' && $data = model('admin/imports')->importUsers($path);
        $post['key']=='bm' && $data = model('api/imports')->importEntrys($path);
        $repeat_data = '';
        if(!empty($data['repeat_data']))
        {
            $repeat_data .= "<h3>有问题数据{$data['nofinish_import_num']}条(不做导入)</h3>";
            foreach($data['repeat_data'] as $v):
                $repeat_data .= "<h5>{$v['name']}</h5><ol>";
                foreach($v['lists'] as $vv):
                    $repeat_data .= "<li style=\"padding-bottom:10px;\">{$vv}</li>"; 
                endforeach;
                $repeat_data .= '</ol>';
                /*$order_data = "";
                $post['key']=='bm' && $order_data .= "<br>订单号：{$v['orderNo']}&nbsp;&nbsp;课程名称：{$v['course_name']}";
                $repeat_data .= "<li style=\"padding-bottom:10px;\">姓名：{$v['name']}&nbsp;&nbsp;身份证：{$v['idcard']}{$order_data}</li>"; */  
            endforeach;
            
        }
        if($data['finish_import_num']>0)
            exit($data['finish_import_num'].'条数据导入成功'.'<p style="text-align:center"><a href="javascript:history.back()">返回</a></p>');
        if($data['finish_import_num']==0)
            exit($repeat_data.'<p style="text-align:center"><a href="javascript:history.back()">返回</a></p>');    
    }

}
