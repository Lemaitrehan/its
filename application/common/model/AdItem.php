<?php
namespace application\common\model;
use think\Db;
/**
 * 课程学杂费
 */
class AdItem extends Base{
    
    /**
	 * 学杂费列表
	 */
    public function get_lists($params=[])
    {
        $where = [];
        $field = '';
        if(isset($params['field']))
        {
            $field = $params['field'];
        }
        $limit = '';
        if(isset($params['limit']))
        {
            $limit = $params['limit'];
        }
        if(isset($params['it_id']))
        {
            $where['it_id'] = $params['it_id'];   
        }
        $rs = $this->where($where)->field($field)->limit($limit)->select();
        foreach($rs as $k=>$v)
        {
            if(isset($v['cover_img']))
            {
                $rs[$k]['cover_img'] = ITSPicUrl($v['cover_img']);
            }
            if(isset($v['price']))
            {
                $rs[$k]['price'] = '';
            }
        }
        return $rs;
    }
    
    /**
	 * subject_ids
	 */
    public function get_subject_ids($course_id=0)
    {
        if(!$course_id) return [];
        $tmp_lists = [];
        $lists = $this->where(['course_id'=>$course_id])->select();
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
}
