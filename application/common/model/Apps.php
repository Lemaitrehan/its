<?php
namespace application\common\model;
use think\Db;

class Apps extends Base {
    //查询学员考试课程
    public function getexamInfo(){
        $params = input('post.');
        $rs = Db::name('sj_exams')
                ->alias('sj')
                ->join('course co','sj.course_id=co.course_id')
                ->where('userId',$params['userId'])
                ->field('co.name as course_name,sj.course_id')
                ->order('sj.id desc')
		->select();
        $pt = $this->joint($rs);
        $pt = array_values($pt);
        return MBISReturn('',1,$pt);
        
    }
    //查询学院考试科目
    public function getSubjectInfo(){
        $parms = input('post.');
        $rs = Db::name('sj_exams')
                ->alias('sj')
                ->join('subject su','sj.subject_id=su.subject_id' )
                ->where(['sj.userId'=>$parms['userId'],'sj.course_id'=>$parms['coursId']])
                ->field('sj.subject_score,sj.exam_time,sj.idcard_no,sj.status,su.name as subject_name,sj.name')
		->select();
        foreach ($rs as $k=>$v){
            $rs[$k]['exam_addrs'] = '南山';
            $rs[$k]['status']=1;
        }
        $rs = array_values($rs);
        return MBISReturn('',1,$rs);
    }
    public function joint($rs){
        foreach ($rs as $k=>$v){
            $data[$k] = $v;
        }
        foreach ($data as $k=>$v){
            $course_name['course'][$k] = $v['course_name'];
            $course_name['id'][$k] = $v['course_id'];
        }
        $id = $course_name['id'];
        $id = array_unique($id);
        $name = $course_name['course'];
        $name = array_unique($name);
        foreach ($name as $k=>$v){
            $file[$k]['name'] = $v;
        }      
        foreach($id as $k=>$v){
            $file[$k]['id'] = $v;
        }
        return $file;
    }
}
