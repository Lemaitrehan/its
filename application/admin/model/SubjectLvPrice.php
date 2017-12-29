<?php
namespace application\admin\model;
use think\Db;
/**
 * 会员等级价格业务
 */
class SubjectLvPrice extends Base{
    /**
	 * 价格列表
	 */
    public function get_lv_price($subject_id=0)
    {
        if(!$subject_id) return [];
        $lists = $this->where(['subject_id'=>$subject_id])->select();
        $tmp_lists = array();
        foreach($lists as $k=>$v)
        {
            $tmp_lists[$v['rankId']] = $v['price'];   
        }
        return $tmp_lists;
    }
    //保存属性值
    public function set_lv_price_value($id,$data=[])
    {
        if($id) $this->where(['subject_id'=>$id])->delete();
        if(!$data) return false;
        $save_data = [];
        foreach($data as $rankId=>$price)
        {
            if($price > 0)
            {
                $save_data[] = [
                    'subject_id'  =>  $id,
                    'rankId' =>  $rankId,
                    'price' =>  $price,
                ];
            }
        }
        $this->saveAll($save_data);
    }
}
