<?php
namespace application\admin\model;
use think\Db;
/**
 * 科目属性业务
 */
class SubjectTypePropValue extends Base{
    /**
	 * 属性值列表
	 */
    public function get_subject_prop_value($subject_id=0)
    {
        if(!$subject_id) return [];
        $lists = $this->where(['subject_id'=>$subject_id])->select();
        $tmp_lists = array();
        foreach($lists as $k=>$v)
        {
            $tmp_lists[$v['prop_id']] = $v['prop_value'];   
        }
        return $tmp_lists;
    }
    //保存属性值
    public function set_prop_value($id,$data=[])
    {
        if($id) $this->where(['subject_id'=>$id])->delete();
        if(!$data) return false;
        $prop_id_list = $data['prop_id_list'];
        $prop_value_list = $data['prop_value_list'];
        if($prop_id_list)
        {
            $save_data = [];
            foreach($prop_id_list as $k=>$prop_id)
            {
                $save_data[] = [
                    'subject_id'  =>  $id,
                    'prop_id' =>  $prop_id,
                    'prop_value' =>  $prop_value_list[$k],
                ];
            }
            $this->saveAll($save_data);
        } 
    }
}
