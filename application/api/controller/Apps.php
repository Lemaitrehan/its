<?php
namespace application\api\controller;
use application\common\model\Apps as AM;


/*
 * 获取学员考试成绩
 *  */
class Apps extends Base {
    //获取课程列表
    public function getCourse(){
        $app = new AM();
        $rs = $app->getexamInfo();
        MBISApiReturn($rs);
    }
    //获取科目列表
    public function getSubject(){
        $app = new AM();
        $rs = $app->getSubjectInfo();
        MBISApiReturn($rs);           
    }
}
