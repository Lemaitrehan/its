<?php
namespace application\common\model;
use think\Db;
/**
 * 课程学杂费
 */
class CourseItem extends Base{
    /**
	 * subject_ids
	 */
    public function get_it_ids($type_id,$course_id=0,$subject_id=0)
    {
        $tmp_lists = [];
        if($course_id > 0)
        {
            $lists = $this->where(['type_id'=>$type_id,'course_id'=>$course_id])->select();
        }
        if($subject_id > 0)
        {
            $lists = $this->where(['type_id'=>$type_id,'subject_id'=>$subject_id])->select();
        }
        if(!empty($lists))
        {
            foreach($lists as $k=>$v)
            {
                $tmp_lists[] = $v['it_id'];   
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
    //保存课程学杂费值
    public function set_course_item_value($type_id,$id=0,$subject_id=0,$data=[])
    {
        if($id>0)
        {
            $this->where(['type_id'=>$type_id,'course_id'=>$id])->delete();   
        }
        if($subject_id>0)
        {
            $this->where(['type_id'=>$type_id,'subject_id'=>$subject_id])->delete();   
        }
        if(!$data) return false;
        $save_data = [];
        foreach($data as $key=>$val)
        {
            $save_data[] = [
                'type_id'  =>  $type_id,
                'course_id'  =>  $id,
                'subject_id'  =>  $subject_id,
                'it_id' => $val,
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
}
