<?php
namespace application\admin\controller;
use application\admin\model\Taskmessage as MyMode;
use application\admin\model\School as School;


/**
 * 任务消息
 */
class Taskmessage extends Base{
	//列表
    public function index(){
        $m = new MyMode();
        if( request()->isAjax() ){
           //查找站内新消息
           if( input('action') == 'sms' ){
               $num = $m->smsTotal();
               return ['num'=>$num];
           } 
           return   $m->index();
        }
    	return $this->fetch("list");
    }
    
    //添加任务
    public function addTask(){
        $m         = new MyMode();
        $schoolObj = new School();
        //弹窗信息
        if( request()->isAjax() ){
           
            if( input('action')=='employee_type_id' ){
                $department_id = input('department_id');
                if($department_id){
                   return $m->employee_type($department_id);
                }else{
                   return [];
                }
            }elseif(input('action')=='userOne'){
                return  $m->getUser(1);
            }elseif(input('action')=='user'){
                return  $m->getUser();
            }elseif(input('action')=='addData'){
                return   $id = $m->addData();
            }
            
            
        }
        //查找学校
        $arrSchool = $schoolObj->getSchoolClass(1);
        $this->assign('arrSchool',$arrSchool);
        //查找部门
        $arrDepartment = $m->getDepartment();
        $this->assign('arrDepartment',$arrDepartment);
        return $this->fetch("edit");
    }
    
    
    //删除
    function del(){
        $m      =   new MyMode();
        $aff_id =  $m->delData();
        $status =  $aff_id?1:0;
        if($aff_id){
            return ['status'=>$status];
        }else{
            return ['status'=>$status];
        }
    }
    //任务专题
    function complete(){
        $m        = new MyMode();
        $affow_id = $m->complete();
        if($affow_id){
            return ['status'=>1,'msg'=>'状态修改成功'];
        }else{
            return ['status'=>0,'msg'=>'状态修改失败'];
        }
    }
   
}

