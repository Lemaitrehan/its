<?php
namespace application\admin\controller;
use application\admin\model\StudentNoticeLog as M;
use application\admin\model\NoticeTmpl as N;
use application\common\model\Sms;
use application\admin\model\School;
use think\Model;
/**
 * 通知发送记录管理控制器
 */
class Studentnoticelog extends Base{
	
	//学历类
    public function indexEducation(){
        if( request()->isAjax() ){
            if( input('action')=='audit' ){
                $m = new M();
                return $m->auditData();
            }
            return  $this->pageQuery(1);
        }
        $m = new M();
        $this->assign('type',1);
        return $this->fetch("list");
    }
    
    
    //技能类
    public function indexSkill(){
        if( request()->isAjax() ){
            if( input('action')=='audit' ){
                $m = new M();
                return $m->auditData();
            }
            return $this->pageQuery(2);
        }
        $m = new M();
        $this->assign('type',2);
        return $this->fetch("list");
    }
    
    //学历类  信息发送记录
    public function indexEducationHistory(){
        if( request()->isAjax() ){
            $m = new M();
            return  $m->pageSmsHistory();
        }
        return $this->fetch("smshistory");
    }
    
    /**
     * 获取分页
     */
    public function pageQuery($type=''){
        $m   = new M();
        $res = $m->pageQuery($type);
        return $res;
    }
    
    /*
    * 获取数据
    */
    public function getlists(){
        $m = new M();
        return $m->getById(Input("id/d",0));
    }
    
    //------------------------- 新增短信-----------------------
    //学历类添加短信
    public function addEducationList(){
        //模板信息
        if( input('action')=='tmpl'   ){
            return $this->chooseTmpl();
        }
        
    	if(input('post.notice_id')){
    		$this->publicAdd(1);
    	}
        return $this->publicEdit(1);
    } 
    //技能类添加短信
    public function addEditSkillList(){
        //模板信息
        if( input('action')=='tmpl'   ){
            return $this->chooseTmpl();
        }
        if(input('post.notice_id')){
            $this->publicAdd(2);
        }
        return $this->publicEdit(2);
    }
    
    /**
     * 新增短信队列
     */
    public function publicAdd($type){
        $Sms = new \application\common\model\Sms;
        $res = $Sms->smsAdd( input('post.notice_id'),0,0,$type );
         
        if($res){
            $status = 1;
            $msg    = '短信已经进入队列准备发送';
        }else{
            $status = -1;
            $msg    = '短信发送失败';
        }
        MBISApiReturn( MBISReturn($msg,$status,array() ) );
    }
    
    /**
     * @param unknown $type 1=>学历 2=》技能
     * @param string $smsLog
     */
    public function publicEdit($type,$smsLog=""){
        $schoolObj = new School();
        if( request()->isAjax() ){
            //查找学校信息
            if(input('post.school_id')){
                if($type ==  1){
                    $eduMajorObj = new \application\admin\model\Major();
                    return $eduMajorObj->getSchoolEduMajor(input('post.school_id'));
                }else{
                    return $arrMajor= $schoolObj->getSchoolMajor(input('post.school_id'));
                }
            }
            //查找专业层次
            elseif(input('post.major_id')){
                $major_id = input('post.major_id');
                //年级
                if($type==1){
                  $eduMajorObj = new \application\admin\model\Major();
                  $getEduMajorLevel = $eduMajorObj->getEduMajorLevel($major_id);
                  return $getEduMajorLevel;
                //科目
                }else{
                  return  $getSchoolType = $schoolObj->getSchoolMajorSubject($major_id);
                }
            
            }else{
                $school  = input('get.school');//学校
                $major   = input('get.major');//专业
                $level_id = input('get.level_id');//层次
                $grade   = input('get.grade_id');//年级
                $subject_id   = input('get.subject_id');//科目
                $userIds = input('get.userIds');//用户选中的用户
                $where   = array();
                if($school){
                    $where['sk.school_id'] = $school;
                }
                if($major){
                    $where['sk.major_id']  = $major;
                }
                if($level_id){
                    $where['sk.level_id']  = $level_id;
                }
                if($grade){
                    $where['sk.grade_id']  = $grade;
                }
                if($subject_id){
                    $where['sk.subject_id']  = $subject_id;
                }
                $search_title = input('search_title');
                $search_word  = input('search_word');
                if( $search_title && $search_word ){
                    switch ($search_title){
                        case 1:
                            $where['u.trueName']  = ['like','%'.$search_word.'%'];
                           break;
                         case 2:
                            $where['u.student_no']  = ['like','%'.$search_word.'%'];
                           break;
                         case 3:
                             $where['u.idcard']  = ['like','%'.$search_word.'%'];
                            break;
                         case 4:
                             $where['u.userPhone']  = ['like','%'.$search_word.'%'];
                            break;
                    }
                }
                
                $arrUserIds = array();
                if($userIds){
                    $arrUserIds = explode(',', $userIds);
                }
                if($type==1){
                   $field = 'u.userId,u.trueName,u.student_no,s.name as school_name,m.name as major_name,g.name as grade_name';
                   $getSchoolType = $schoolObj->getSearchUser($field,$where);
                }else{
                    $field = 'u.userId,u.trueName,u.student_no,s.name as school_name,m.name as major_name,subject.name as grade_name';
                    $getSchoolType = $schoolObj->getSearchUserSkill($field,$where);
                }
                
                foreach($getSchoolType['Rows'] as $Key => $v){
                    $getSchoolType['Rows'][$Key]['trueName'] = $v['trueName'].'('.$v['student_no'].')';
                    if( in_array( $v['userId'], $arrUserIds)){
                        $getSchoolType['Rows'][$Key]['checkbox']  = '<input id="ck_"'.$v['userId'].' checked="checked" type="checkbox" name="chk" value="'.$v['userId'].'">';
                    }else{
                        $getSchoolType['Rows'][$Key]['checkbox']  = '<input id="ck_"'.$v['userId'].' type="checkbox" name="chk" value="'.$v['userId'].'">';
                        
                    }
                    
                }
               return $getSchoolType;
            }   
        }
        //--------第三方跳转-------------
        $is_three = false;
        $arrU     = [];
        if( input('ids') ){
            $is_three = true;
            $ids = input('ids');
            $arrGetUser = explode(',', $ids);
            foreach ($arrGetUser as $key => $v ){
                $arr = explode('--', $v);
                $arrU[ $arr[0] ] = $arr[1];
            }
            //dd($arrGetUser);
        }
        $this->assign('arrU',json_encode($arrU) );
        $this->assign('is_three',$is_three);
        $m   = new M();
       /* $rs  = $m->getById(Input("id/d",0));
        $Sms = new Sms();
        //发送状态 -1=> 发送失败 0=》未发送  1=> 暂不发送  2=》 已经发送
        $res = $Sms->getSmsBase(Input("id/d",0));
        if( in_array($res['status'], array(-1,2) )  ){
            return MBISReturn("短信状态，不能删除", 1);
        } */
        //学院数据
        $arrSchool = $schoolObj->getSchoolClass($type);
        $this->assign("arrSchool",$arrSchool);
        //年级
        $arrGrade  = db('grade')->field('grade_id,name')->select();
        $this->assign('arrGrade',$arrGrade);
        //短信模板
        $noticetmpl_list = $m->get_noticetmpl_list();
        $this->assign("noticetmpl_list",$noticetmpl_list);
       
        //发送方式
        $sms      = new Sms();//实例化短信记录
        $sendType = $sms->sendType;
        $sendTypeName = $smsLog?$sendType[$smsLog['send_type']]:'';
        $this->assign("sendType",$smsLog?$smsLog['send_type']:'');
        $this->assign("sendTypeName",$sendTypeName);
        //模板类型
        $this->assign("template_id",$smsLog?$smsLog['template_id']:'');
        $templetType = $sms->templetType;
        $this->assign("templetType",$templetType);
        
        //供筛选学员列表
        $userinfo = $m->get_userinfo();
        //dump($userinfo);die;
        $page = $userinfo->render();
        $this->assign('list',$userinfo);
        
        $this->assign('smsLog',$smsLog?$smsLog:1);
        $this->assign('page',$page);
        $this->assign('type',$type);
        $this->assign('enjn',$smsLog?2:1);
        $this->assign('specialTag',json_encode($sms->specialTag));//特殊标记
        $this->assign('specialTagMemo',$sms->specialTagMemo);//特殊标记
        return $this->fetch("edit");
    }
    //------------------------- END新增短信-----------------------
    
    
    //学历编辑页面 
    public function  toEditEducation(){
        //模板信息
        if( input('action')=='tmpl'   ){
            return $this->chooseTmpl();
        }
        if( input('action')=='findUserS'  ){
            return $this->publicEdit(1);
        }
       return $this->editPublic(1);
    }
    //技能编辑页面
    public function  toEditSkill(){
        //模板信息
        if( input('action')=='tmpl'   ){
            return $this->chooseTmpl();
        }
        if( input('action')=='findUserS'  ){
            return $this->publicEdit(2);
        }
       return $this->editPublic(2);
    }
    
    //编辑公共页面
    public function  editPublic($type){
        $m      = new M();//实例化模板
        $sms    = new Sms();//实例化短信记录
        //编辑
        $sms_id = input('get.id');
        //发送状态 -1=> 发送失败 0=》未发送  1=> 暂不发送  2=》 已经发送
        if($sms_id){
                //模版变量
                $smsLog = $sms->getSms( Input("id/d",0) );
                $templateReplaceText = unserialize( $smsLog['templateReplaceText'] );
                $arrSpecialTag    = $sms->specialTag;
                $this->assign('SpecialTag1',$arrSpecialTag[1]);
                $this->assign('templateReplaceText',$templateReplaceText);
                if(!$smsLog){
                    return '';
                }
                $this->assign("smsLog",$smsLog);
                $sendType = $sms->sendType;
                $this->assign("sendType",$sendType);
                //查找所有的用户
                $userIds    = $smsLog['userIds'];
                $arrUserDiv = db('users')->where('userId','in',$userIds)->field('userId,trueName')->select();
                $this->assign('arrUserDiv',$arrUserDiv);
                $noticetmpl_list = $m->get_noticetmpl_list();
                return $this->publicEdit($type,$smsLog);
        }
        if(input('post.sms_id')){
            $id  = input('post.notice_id');
            $res = $sms->smsAdd($id,'',input('post.sms_id'),$type);
            if($res){
                $status = 1;
                $msg    = '短信已经进入队列准备发送';
            }else{
                $status = -1;
                $msg    = '短信发送失败';
            }
            MBISApiReturn( MBISReturn($msg,$status,array() ) );
        }
    }
    
    
    /**
     * 学历删除
     */
    public function delEducation(){
        $m = new M();
        return $m->del();
    }
    
    /**
     * 技能删除
     */
    public function delSkill(){
        $m = new M();
        return $m->del();
    }
    
    
    
    /**
     * ajax
     */
    public function chooseTmpl(){ //获取模板信息
        $m = new M();
        return $m->chooseTmpl();
    }
    public function userSearch(){ //获取学员信息
        $m = new M();
        return $m->userSearch();
    }
    public function getUsersList(){
        $m = new M();
        return $m->getUsersList();
    }

    
}
