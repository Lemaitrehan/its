<?php
namespace application\admin\model;
use think\Db;
/**
 * 课程科目
 */
class CourseSubject extends Base{
    /**
	 * subject_ids
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
    public function get_subject_names($course_id=0,$is_implode=false)
    {
        $subject_ids = $this->get_subject_ids($course_id);
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
        if($id) $this->where(['course_id'=>$id,'obj_type'=>1])->delete();
        if(!$data) return false;
        $save_data = [];
        foreach($data as $key=>$val)
        {
            $save_data[] = [
                'course_id'  =>  $id,
                'subject_id'  =>  $val,
                'obj_type'  =>  1,
            ];
        }
        $this->saveAll($save_data);
    }
    //线上课程价格处理
    public function set_course_subject_value_price($id,$data=[],$tmp_subject=[])
    {
        if($id) $this->where(['course_id'=>$id,'obj_type'=>2])->delete();
        if(!$data) return false;
        $save_data = [];
        foreach($data as $key=>$val)
        {
            $save_data[] = [
                'course_id'  =>  $id,
                'subject_id'  =>  $key,
                'price'  =>  $val,
                //'sale_price'  =>  $val,
                'obj_type'  =>  2,
            ];
        }
        $this->saveAll($save_data);
    }
    //课程名称
    public function get_course_name($subject_id=0){
        $course_id = $this->where('subject_id',$subject_id)->value('course_id');
        if(empty($course_id)) return '';
        return model('course')->get_name($course_id);
	}
    //科目全款值
    public function get_subject_full_val($course_id=0)
    {
        if(!$course_id) return [];
        $tmp_lists = [];
        $lists = $this->where(['course_id'=>$course_id])->select();
        if($lists)
        {
            foreach($lists as $k=>$v)
            {
                $tmp_lists[$v['subject_id']] = 1;   
            }
        }
        return $tmp_lists;
    }
}
