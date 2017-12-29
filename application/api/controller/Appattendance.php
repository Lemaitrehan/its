<?php
// +----------------------------------------------------------------------
// | APP考勤
// +----------------------------------------------------------------------
// | Author: lijianhua
// +----------------------------------------------------------------------
namespace application\api\controller;
use think\Db;
use application\common\model\Attendance as AttendanceClass;

/**
* 学员
 */
class Appattendance extends Base{
    
    
    /**
     * 考勤科目
     * 2017-3-25
     */
    function AppattendanceSubject(){
        $appClass = new AttendanceClass;
        $arrType  = $appClass->grade();
        $newArray = array();
        foreach ($arrType as $key =>$v){
            $newArray[$key]['Subject_id']   = $key;
            $newArray[$key]['Subject_name'] = $v;
        }
        MBISApiReturn( MBISReturn('科目类型！！！',1,array_values($newArray) ) );
    }
      
    
    /**
     * 考勤记录
     * 2017-3-25
     */
    function attendanceRecord(){
           $appClass  =   new AttendanceClass;
           $arrCourse =   $appClass->Attendancelist();
           MBISApiReturn( MBISReturn('考勤记录！！！',1,$arrCourse ) );
    }
    
  
    
}



?>
