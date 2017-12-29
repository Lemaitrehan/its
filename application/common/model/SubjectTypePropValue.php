<?php
namespace application\common\model;
use think\Db;
/**
 * 科目属性业务
 */
class SubjectTypePropValue extends Base{
    /**
	 * 属性值列表
	 */
    private function get_subject_prop_value_data($subject_id=0)
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
    private function get_subject_prop_data($type_id=0)
    {
        $rs =Db::name('subject_type_prop')->where('type_id',$type_id)->select();
        return $rs;
    }
    public function get_subject_prop($type_id=0,$subject_id=0,$file_name='field_name')
    {
        $rs_prop =$this->get_subject_prop_data($type_id);
        $rs_prop_value =$this->get_subject_prop_value_data($subject_id);
        $rs_tmp = [];
        foreach($rs_prop as $k=>$v)
        {
           if($v['field_name'])
           {
                $rs_tmp[$v[$file_name]] = $rs_prop_value[$v['prop_id']];
           }
        }
        return $rs_tmp;
    }
}
