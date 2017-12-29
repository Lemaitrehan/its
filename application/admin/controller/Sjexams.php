<?php
namespace application\admin\controller;
use application\admin\model\SjExams as M;
use application\admin\model\School;
use application\admin\model\Major;
use application\admin\model\Grade;
use application\admin\model\Subject;
/**
 * 学员考试管理控制器
 */
class Sjexams extends Base{
    
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
            //查看科目历史记录    
            }elseif( input('post.exams_subject_id') ){
                $exams_subject_id = input('post.exams_subject_id');
                return  $sjexamsObj->subjectEXamsHistory($exams_subject_id);
            //审核    
            }elseif ( input('post.action')=='audit' ){
                $id = input('post.id');
                $status = input('post.status');
                $data = array(
                    'auditStatus'      => $status,
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
        $this->assign("arrSubject",$arrSubject);
        //年级
        $gradeObj = new Grade();
        $arrGrade = $gradeObj->getGrade();
        $this->assign("arrGrade",$arrGrade);
        //成绩状态
        $arrExmsPassStatus = $sjexamsObj->arrExmsPassStatus;
        $this->assign("arrOneExamsStatus",$arrExmsPassStatus);
        $this->assign("arrExamsStatus",json_encode($arrExmsPassStatus));
        $this->assign('type',1);
        return $this->fetch('list');
    }
    
    //数据导出
    function export(){
        set_time_limit(0);
        $sjexamsObj = new M();
        $filename ='考试成绩导出';
        $data = array();
        $data[] = array('年月','院校代码','专业代码','姓名','准考证号','课程代码','成绩'); 
        $res  = $sjexamsObj->pageQuery(1);
        foreach($res['Rows'] as $key => $v ){
        	foreach ($v['sub'] as $k => $t){
        		$arr_exam_time  = $v['exam_time'];
        		$arr_subject_no = $v['subject_no'];
        		//dd($arr_exam_time);
        		//if( $arr_exam_time[$k]  ){
		        	$data[$k+1] = array(
		        			$arr_exam_time[$k]?date('Ym',$arr_exam_time[$k]):'',//报考时间
		        			$v['school_no']."\t",//院校代码
		        			$v['major_number']."\t",//专业代码
		        			$v['trueName'],//姓名
		        			$v['exam_no']."\t",//准考证
		        			$arr_subject_no[$k]."\t",//课程代码
		        			$t,//成绩
		        	);
        		//}
        	}
        }
        $data = array_values($data);
        array_excel($filename, $data);
        exit;
    }
    //添加数据 导入
    function add(){
        $file = request()->file('exel');
         if($file == null ){
            $this->error('请选择上传文件');
        }
        // 移动到框架应用根目录/public/uploads/目录下
        $info = $file->rule('uniqid')->validate(['size'=>156780,'ext'=>'xlsx'])->move(ROOT_PATH . 'public' . DS . 'upload'.'/exams/baokao','',true);
        if($info){
            $file = ROOT_PATH . 'public' . DS . 'upload'.'/exams/baokao/'.$info->getSaveName();
            $m      = new M();
            $res =  $m->importUsers($file);
            if((int)$res){
               $this->success('导入数据成功！！！',url('Sjexams/indexEducation') );
            }else{
               $this->error($res,url('Sjexams/indexEducation') );
            }
        }else{
            // 上传失败获取错误信息
            $msg =  $file->getError();
            $this->error($msg,url('Sjexams/indexEducation') );
    
        }
    
    }
    
/*     //手动添加数据
    function manuallyAdd(){
        $m = new M();
        if( request()->isAjax() ){
            return $m->addData();
        }
        $arrExmsPassStatus = $m->arrExmsPassStatus;
        $this->assign('arrExmsPassStatus',json_encode($arrExmsPassStatus));
        return $this->fetch('edit');
    } */
    
    //编辑
    function edit(){
        $m      = new M();
        $id     = input('id');
        $subject_score = input('subject_score');
        $exam_status   = input('exam_status');
        $is_true = $m->editData($id,$subject_score,$exam_status);
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
            $status = 0;
            $msg = '删除成功';
        }else{
            $msg = $is_true;
            $status = 0;
        }
        MBISApiReturn( MBISReturn($msg,$status,array() ) );
    
    }
    
    
    
  
}
