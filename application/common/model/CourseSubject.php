<?php
namespace application\common\model;
use think\Db;
/**
 * 课程科目
 */
class CourseSubject extends Base{
    /**
	 * subject_ids
     * @obj_type  类型：1=线下科目 2=线上科目 3=实物 4=赠品
	 */
    public function get_subject_ids($course_id=0,$obj_type=1)
    {
        if(!$course_id) return [];
        $tmp_lists = [];
        $lists = $this->where(['obj_type'=>$obj_type,'course_id'=>$course_id])->select();
        if($lists)
        {
            foreach($lists as $k=>$v)
            {
                $tmp_lists[] = $v['subject_id'];   
            }
        }
        return $tmp_lists;
    }
    public function get_subject_names($course_id=0,$obj_type=1,$is_implode=false)
    {
        $subject_ids = $this->get_subject_ids($course_id,$obj_type);
        $tmp_lists = [];
        if($subject_ids)
        {
            $lists = model('Subject')->where('subject_id','in',$subject_ids)->select();
            foreach($lists as $k=>$v)
            {
               $tmp_lists[$v['subject_id']] = $v['name'];    
            }
        }
        return $is_implode?implode(',',$tmp_lists):$tmp_lists;
    }
    //保存课程科目值
    public function set_course_subject_value($id,$data=[])
    {
        if($id) $this->where(['course_id'=>$id])->delete();
        if(!$data) return false;
        $save_data = [];
        foreach($data as $key=>$val)
        {
            $save_data[] = [
                'course_id'  =>  $id,
                'subject_id'  =>  $val
            ];
        }
        $this->saveAll($save_data);
    }
    //获取线上课程科目信息
    public function get_subject_online($course_id=0,$obj_type=2)
    {
        if(!$course_id) return [];
        $tmp_lists = [];
        $lists = $this->field('course_id,subject_id,price')->where(['obj_type'=>$obj_type,'course_id'=>$course_id])->select();
        /*if($lists)
        {
            foreach($lists as $k=>$v)
            {
                $tmp_lists[] = $v['subject_id'];   
            }
        }*/
        return $lists;
    }
}
