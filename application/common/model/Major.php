<?php
namespace application\common\model;
/**
 * 专业业务处理
 */
use think\Db;
class Major extends Base{
    /**
	 * 专业列表
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
        if(isset($params['school_id']))
        {
            $where['school_id'] = $params['school_id'];   
        }
        if(isset($params['major_id']))
        {
            $where['major_id'] = $params['major_id'];   
        }
        $rs = $this->where($where)->field($field)->limit($limit)->select();
        foreach($rs as $k=>$v)
        {
            if(isset($v['cover_img']))
            {
                $rs[$k]['cover_img'] = ITSPicUrl($v['cover_img']);
            }
        }
        return $rs;
    }
    //专业名称
    public function get_name($id=0){
        return $this->where('major_id',$id)->value('name');
	}
    //专业详情
    public function get_info($params=[]){
        $field = '';
        if(isset($params['field']))
        {
           $field = $params['field'];
        }
        $where = [];
        if(isset($params['major_id']))
        {
           $where['major_id'] = $params['major_id'];    
        }
        $rs = $this->where($where)->field($field)->find();
        if(isset($params['field'])&&strpos($params['field'],',')===FALSE) return $rs[$field];
        if(isset($rs['details']))
        {
            $rs['details'] = htmlspecialchars_decode($rs['details']);
        }
        if(isset($rs['exam_type']))
        {
            $rs['exam_type'] = ITSSelItemName('major','exam_type',$rs['exam_type']);
        }
        if(isset($rs['level_type']))
        {
            $rs['level_type'] = ITSSelItemName('major','level_type',$rs['level_type']);
        }
        if(isset($rs['graduate_type']))
        {
            $rs['graduate_type'] = ITSSelItemName('major','graduate_type',$rs['graduate_type']);
        }
        if(isset($rs['cover_img']))
        {
            $rs['cover_img'] = ITSPicUrl($rs['cover_img']);
        }
        return $rs;
	}
    //学院名称
    public function get_school_name($id=0){
        $school_id = $this->where('major_id',$id)->value('school_id');
        return model('school')->get_name($school_id);
	}
    /* 获取专业数据 */
    public function getInfoData($major_id=0){
        $return = $this->get(['major_id'=>$major_id]);
        return $return;   
    }
	
}
