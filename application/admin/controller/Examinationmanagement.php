<?php
namespace application\admin\controller;
use application\admin\model\examination AS M ;
use application\admin\model\School;
use application\admin\model\Major;
use application\admin\model\Grade;
use application\admin\model\Subject;

use think\Controller;
// +----------------------------------------------------------------------
// | 报名管理
// +----------------------------------------------------------------------
// | Author: lijianhua
// +----------------------------------------------------------------------
class Examinationmanagement extends Base{
    
    //学历报靠信息
    public function indexEducation(){
  
        $sjexamsObj = new M();
        //考试类型
        $examType = session('examType');
        if( request()->isAjax() ){
             //分页
             if( input('post.action') == 'fy' ){
                  return $sjexamsObj->pageQuery();
             //专业   
             }elseif (input('post.school_id')){
                   $school_id   = input('post.school_id');
                   $majorObj    = new Major();
                   $where = "FIND_IN_SET($school_id,school_ids)";
                   $arrMajor    = $majorObj->getMajor( $where );
                   return $arrMajor;
             //查找专业科目 和 层级
             }elseif( input('post.major_id') ){
                   $major_id    = input('post.major_id');
                   //查找专业下面的科目
                   $subiectObj  = new Subject();
                   $arrSubject  = $subiectObj->getSubject();
                   //查找专业的层级
                   $majorObj      = new Major();
                   $arrMajorLevel = $majorObj->getMajorLevel($major_id);
                   return array('subject'=>$arrSubject,'level'=>$arrMajorLevel);
             }elseif ( input('post.action')=='auditStatus' ){
                $id             = input('post.id');
                $status         = input('post.status');
                $data = array(
                    'bkAuditStatus'    => $status,
                    'update_time'      => time(),
                    'update_person_id' => session('MBIS_STAFF')->staffId
                );
                $affow_id  = db('sj_exams')->where( 'id ='.$id )->update($data);
                if($affow_id){
                   return ['msg'=>'审核成功','status'=>1]; 
                }else{
                   return ['msg'=>'审核失败','status'=>-1];
                }
            }
        }
        
        //查找学校
        $schoolObj = new School();
        $arrSchool = $schoolObj->getSchoolClass(1);
        $this->assign("arrSchool",$arrSchool);
        $oneschool_id =  $arrSchool[0]['school_id'];
        //查找专业
        $majorObj    = new Major();
        $whereM      = "FIND_IN_SET($oneschool_id,school_ids)" ;
        $arrMajor    = $majorObj->getMajorEdu( $whereM );
        $this->assign("arrMajor",$arrMajor);
        //查找层次
        $arrMajorLevel = $majorObj->arrMajorLevel;
        $this->assign("arrMajorLevel",$arrMajorLevel);
        //查找层次下面的科目
        $Major  = new Major();
        $arrSubject = $Major->getMajorSubject($oneschool_id, $arrMajor[0]['major_id'],2);
        $arrOneSubject = array();
        foreach ($arrSubject as $key => $v ){
            $arrOneSubject[$key] = $v['name'];
        }
        $this->assign("arrOneSubject",$arrOneSubject);
        //年级
        $gradeObj = new Grade();
        $arrGrade = $gradeObj->getGrade();
        $this->assign("arrGrade",$arrGrade);
        
        $arrExamsStatus = $sjexamsObj->arrExamsStatus;
        $this->assign("arrOneExamsStatus",$arrExamsStatus);
        $this->assign("arrExamsStatus",json_encode($arrExamsStatus));
        $this->assign('type',1);
        return $this->fetch('list');
    }
    
    //数据导出
    function export(){
        $sjexamsObj = new M();
        $res =  $sjexamsObj->pageQuery(1);
        $data[0] = array(
        	'年月','院校代码','专业代码','姓名','准考证号'
        );
        $all =  $res['allSubject'];
        foreach ($all as $key => $v ){
        	$data[0][] = $v['name'].'('.$v['subject_no'].')';
        }
        $filename = '报考信息';
        foreach ($res['Rows'] as $key => $v){
        	$data[($key+1)][] = $v['baokao_time'];//'年月'
         	$data[$key+1][] = $v['school_name'];//'院校代码'
        	$data[$key+1][] = $v['major_name'];//'专业代码'
        	$data[$key+1][] = $v['trueName'];//'姓名'
        	$data[$key+1][] = $v['exam_no'];//'准考证' 
        	foreach($v['sub'] as $t){
        		$data[$key+1][] = $t;//科目
        	}
        } 
        array_excel($filename, $data);
        EXIT;
    }
    
    //添加数据
    function add(){
        
        $file = request()->file('exel');
        if($file == null ){
            $this->error('请选择上传文件');
        }
        // 移动到框架应用根目录/public/uploads/目录下
        $info = $file->rule('uniqid')->validate(['size'=>15678,'ext'=>'xlsx'])->move(ROOT_PATH . 'public' . DS . 'upload'.'/exams/baokao','',true);
        if($info){
            $file = ROOT_PATH . 'public' . DS . 'upload'.'/exams/baokao/'.$info->getSaveName();
            $m      = new M();
            $res =  $m->importUsers($file);
            if((int)$res){
                $this->success('导入数据成功！！！',url('Examinationmanagement/indexEducation') );
            }else{
                $this->error($res,url('Examinationmanagement/indexEducation') );
            }
        }else{
            // 上传失败获取错误信息
            $msg =  $file->getError();
            $this->error($msg,url('Examinationmanagement/indexEducation') );
            
        
        }
        
    }
    
    //查看历史记录
    function getHistory(){
        $id = input('post.id');
        if(!$id){
            return ['status'=>0,'msg'=>'参数错误'];
        }
        $sjexamsObj = new M();
       return  $sjexamsObj->getHistory();
    }
    
    //手动添加数据
    function manuallyAdd(){
/*         $m = new M();
        if( request()->isAjax() ){
            return $m->addData();
        }
        $arrExmsPassStatus =  $m->arrExamsStatus;
        $this->assign('arrExmsPassStatus',json_encode($arrExmsPassStatus));
        $arrGrade  = db('grade')->field('grade_id,name')->select();
        $this->assign('arrGrade',$arrGrade);
        return $this->fetch('edit'); */
    }
    
    //编辑
    function edit(){
        $m      = new M();
        $id     = input('id');
        $status = input('status');
        $is_true = $m->editData($id,$status);
        if((int)$is_true){
            $status = 1;
            $msg = '编辑成功';
        }else{
            $msg = $is_true;
            $status = 0;
        }
        MBISApiReturn( MBISReturn($msg,$status,array() ) );
    }
    
    //删除
    function del(){
        $m = new M();
        $id = input('id');
        $is_true = $m->delData($id);
        if((int)$is_true){
            $status = 1;
            $msg = '删除成功';
        }else{
            $msg = $is_true;  
            $status = 0;
        }
        MBISApiReturn( MBISReturn($msg,$status,array() ) );
        
    }
    
   
}
