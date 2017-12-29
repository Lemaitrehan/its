<?php
namespace application\common\model;
/**
 * 科目业务处理
 */
use think\Db;
class Subject extends Base{
    /**
	 * 科目列表
	 */
    public function get_lists($params=[])
    {
        $where = [];
        $field = '';
        if(isset($params['field']))
        {
            $field = $params['field'];
        }
        if(isset($params['school_id']))
        {
            $where['school_id'] = $params['school_id'];   
        }
        if(isset($params['major_id']))
        {
            $where['major_id'] = $params['major_id'];   
        }
        if(isset($params['subject_id']))
        {
            $where['subject_id'] = $params['subject_id'];
        }
        if(isset($params['is_shelves']))
        {
            $where['is_shelves'] = $params['is_shelves'];   
        }
        if(isset($params['teaching_type']))
        {
            $where['teaching_type'] = $params['teaching_type'];   
        }
        //分页信息处理
        $limit = '';
        if(isset($params['get_pager']))
        {
            if(empty($params['page_no'])) $params['page_no']=1;
            if(empty($params['page_size'])) $params['page_size']=12;
            $data_total = $this->where($where)->count();
            $page_total = ceil($data_total/$params['page_size']);
            if(isset($params['page_no']) && isset($params['page_size']))
            {
                $start = ($params['page_no']-1)*$params['page_size'];
                $limit = "{$start},{$params['page_size']}";
            }
        }
        //排序处理
        $order = 'lastmodify DESC';
        $pagesize='';
        
        if(isset($params['to_array']))
        {
            $rs = $this->where($where)->field($field)->limit($limit)->order($order)->paginate($pagesize)->toArray();   
        }
        else
        {
            $rs = $this->where($where)->field($field)->limit($limit)->order($order)->select();
        }
        
        foreach($rs as $k=>$v)
        {
            if(isset($v['cover_img']))
            {
                $rs[$k]['cover_img'] = ITSPicUrl($v['cover_img']);
            }
            if(isset($v['sale_price']))
            {
                //$subject_price = $this->get_subject_price($v['subject_id'],$v);
                //$rs[$k]['price'] = $subject_price['price'];
                //$rs[$k]['market_price'] = $subject_price['market_price'];
            }
            if(isset($v['teacher_id']))
            {
                $rs[$k]['teacher_name'] = model('common/users')->get_nick_name($v['teacher_id']);
            }
            if(isset($v['major_id']))
            {
                $rs[$k]['school_name'] = model('common/major')->get_school_name($v['major_id']);
                $rs[$k]['major_name'] = model('common/major')->get_name($v['major_id']);
            }
            if(isset($v['teaching_type']))
            {
                $rs[$k]['obj_type'] = $v['teaching_type'];
                $rs[$k]['teaching_type'] = $rs[$k]['teaching_type_format'] = ITSSelItemName('subject','teaching_type',$v['teaching_type']);
            }
            if(isset($v['subject_no']))
            {
                $rs[$k]['course_bn'] = $v['subject_no'];
            }
        }
        //分页信息处理
        if(isset($params['get_pager']))
        {
            $rs_p['lists'] = $rs;
            $rs_p['data_total'] = $data_total;
            $rs_p['page_total'] = $page_total;
            $rs_p['page_cur'] = $params['page_no'];
            $rs = $rs_p;
        }
        return $rs;
    }
    //专业名称
    public function get_name($id=0){
        return $this->where('subject_id',$id)->value('name');
	}
    //专业详情
    public function get_info($params=[]){
        $field = '';
        if(isset($params['field']))
        {
           $field = $params['field'];
        }
        $where = [];
        if(isset($params['subject_id']))
        {
           $where['subject_id'] = $params['subject_id'];    
        }
        if(isset($params['is_shelves']))
        {
            $where['is_shelves'] = $params['is_shelves'];   
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
        if(isset($rs['teaching_type']))
        {
            $rs['obj_type'] = $rs['teaching_type'];
            $rs['teaching_type'] = ITSSelItemName('subject','teaching_type',$rs['teaching_type']);
        }
        if(isset($rs['cover_img']))
        {
            $rs['cover_img'] = ITSPicUrl($rs['cover_img']);
        }
        if(isset($rs['subject_id']))
        {
            $subject_price = $this->get_subject_price(0,$rs);
            $rs['price'] = $subject_price['price'];
            $rs['market_price'] = $subject_price['market_price'];
        }
        if(isset($rs['teacher_id']))
        {
            $rs['teacher_name'] = model('common/users')->get_nick_name($rs['teacher_id']);
        }
        return $rs;
	}
    //科目属性列表
    public function get_subject_props($params=[])
    {
        $rs = [];
        $subject_ids = model('common/CourseSubject')->get_subject_ids($params['course_id']);
        if($subject_ids)
        {
            $params['field'] = 'subject_id,subject_type_id,name,subject_no,course_hours,learn_coins,sale_price,market_price,teacher_id';
            $params['subject_id'] = ['in',$subject_ids];
            $params['to_array'] = 1;
            $rs = $this->get_lists($params);
            if($rs['Rows'])
            {
                foreach($rs['Rows'] as $k=>&$v)
                {
                   $subject_prop = model('common/SubjectTypePropValue')->get_subject_prop($v['subject_type_id'],$v['subject_id']);
                   $v = array_merge($v,$subject_prop);
                   if(isset($v['teacher_id']))
                   {
                       $v['teacher_name'] = model('common/users')->get_nick_name($v['teacher_id']);
                   }
                }
                $rs = $rs['Rows'];
            }
        }
        return $rs;
    }
    //科目价格处理
    public function get_subject_price($subject_id=0,$tmp_data=[])
    {
        if(!empty($tmp_data))
        {
            $rs = $tmp_data;
        }
        else
        {
            $rs = $this->where('subject_id',$subject_id)->field('sale_price,offer_price,market_price,is_shelves')->find();
        }
        $price = 0;
        $market_price = 0;
        $price = $rs['sale_price'];
        $market_price = (float)$rs['market_price']<=0?$price:$rs['market_price'];
        /*if((float)$rs['offer_price'] > 0 && $rs['offer_price'] <= $price)
        {
            $price = $rs['offer_price'];
        }*/
        //会员价格待处理。。。
        //优惠价格待处理。。。
        return ['price'=>$price,'market_price'=>$market_price];
    }
    //过滤不存在的科目ID
    public function filter_subject_ids($type_id,$subject_ids)
    {
        $tmp_subject_ids =[];
        $rs = $this->where(['subject_type_id'=>$type_id,'subject_id'=>['in',$subject_ids]])->field('subject_id')->select();
        foreach($rs as $k=>$v)
        {
           $tmp_subject_ids[] = $v['subject_id'];    
        }
        return $tmp_subject_ids;
    }
    //最少预付定金
    public function get_deposit_price($deposit_price=0)
    {
        $price = 500;
        //if($deposit_price>0) $price=$deposit_price; 
        return sprintf('%0.2f',$price);  
    }
	
}
