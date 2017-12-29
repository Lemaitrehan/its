<?php
namespace application\common\model;
/**
 * 年级业务处理
 */
use think\Db;
class Grade extends Base{
    
    //合并年级信息
    public function merge_grades($rs)
    {
        $grade_ids = [];
        foreach($rs as $k=>$v)
        {
            $grade_ids[] = $v['grade_id'];   
        }
        $tmp_rs_grade = [];
        $rs_grade = $this->get_lists(['grade_id'=>['in',$grade_ids],'field'=>'major_id,grade_id,name,stu_fee,offers,deposit_price,market_price,teacher_id']);
        foreach($rs_grade as $k=>$v)
        {
            $tmp_rs_grade[$v['grade_id']] = $v;
        }
        foreach($rs as $k=>$v)
        {
            if(isset($tmp_rs_grade[$v['grade_id']]))
            {
                $v['grade_name'] = $tmp_rs_grade[$v['grade_id']]['name'];
                $v['price'] = $this->get_grade_price(0,$tmp_rs_grade[$v['grade_id']]);
                $v['deposit_price'] = $tmp_rs_grade[$v['grade_id']]['deposit_price'];
                $v['market_price'] = $tmp_rs_grade[$v['grade_id']]['market_price'];
                $v['stu_fee'] = $tmp_rs_grade[$v['grade_id']]['stu_fee'];
                $v['offers'] = $tmp_rs_grade[$v['grade_id']]['offers'];
                $v['major_id'] = $tmp_rs_grade[$v['grade_id']]['major_id'];
                $v['teacher_id'] = $tmp_rs_grade[$v['grade_id']]['teacher_id'];
                $v['teacher_name'] = $tmp_rs_grade[$v['grade_id']]['teacher_name'];
                $v['channelLists'] = $tmp_rs_grade[$v['grade_id']]['channelLists'];
            }
            $rs[$k] = $v;
        }
        return $rs;
    }
    //年级价格处理
    public function get_grade_price($grade_id=0,$tmp_data=[])
    {
        if(!empty($tmp_data))
        {
            $rs = $tmp_data;
        }
        else
        {
            $rs = $this->where('grade_id',$grade_id)->field('stu_fee,offers')->find();
        }
        $price = $rs['stu_fee'];
        if($rs['offers'] > 0 && $rs['offers'] <= $price)
        {
            $price = $rs['offers'];  
        }
        return $price;
    }
    /**
	 * 年级列表
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
        if(isset($params['major_id']))
        {
            $where['major_id'] = $params['major_id'];   
        }
        if(isset($params['grade_id']))
        {
            $where['grade_id'] = $params['grade_id'];   
        }
        $rs = $this->where($where)->field($field)->limit($limit)->select();
        foreach($rs as $k=>$v)
        {
            if(isset($v['cover_img']))
            {
                //$rs[$k]['cover_img'] = ITSPicUrl($v['cover_img']);
            }
            if(isset($v['teacher_id']))
            {
                $rs[$k]['teacher_name'] = model('common/users')->get_nick_name($v['teacher_id']);
            }
            //添加通道
            $rs[$k]['channelLists'] = get_channel_lists($v);
        }
        return $rs;
    }
    //年级名称
    public function get_name($id=0){
        return $this->where('major_id',$id)->value('name');
	}
    //年级详情
    public function get_info($params=[]){
        $where = [];
        $field = '';
        if(isset($params['field']))
        {
            $field = $params['field'];
        }
        /**
          专业详情处理(考虑到每年年级内容可能有变化)
          1、年级详情未填写，专业详情为准
          2、年级、专业详情同时填写，则年级优先级最高
        */
        if(isset($params['grade_id']))
        {
           $where['grade_id'] = $params['grade_id'];    
        }
        $rs = $this->where($where)->field($field)->find();
        if(isset($params['field'])&&strpos($params['field'],',')===FALSE) return $rs[$field];
        if(isset($params['major_id']))
        {
           $rs_major = model('major')->get_info(['major_id'=>$params['major_id']]);    
        }
        if(empty($rs['rp_des'])){
           $rs['rp_des'] = $rs_major['details'];    
        }
        else
        {
           $rs['rp_des'] = htmlspecialchars_decode($rs['rp_des']);   
        }
        if(isset($rs['teacher_id']))
        {
            $rs['teacher_name'] = model('users')->get_nick_name($rs['teacher_id']);
        }
        //虚拟课程信息
        if(isset($rs['grade_id']))
        {
            $rs['course_id'] = model('course')->get_course_id(['major_id'=>$params['major_id'],'grade_id'=>$params['grade_id']]);     
        }
        //自考，科目信息特殊处理
        if($rs_major['exam_type'] == '自考')
        {
           $rs_subject = model('subject')->get_subject_props(['course_id'=>$rs['course_id']]);
           $subject_data = '';
           if($rs_subject)
           {
               $subject_data = '<table width="100%" style="text-align:center;">';
               $subject_data .= '<tr>';
               $subject_data .= '<th>课目名称</th>';
               $subject_data .= '<th>类型序号</th>';
               $subject_data .= '<th>科目代码</th>';
               $subject_data .= '<th>学分</th>';
               $subject_data .= '<th>类型</th>';
               $subject_data .= '<th>考试方式</th>';
               $subject_data .= '<th>考试时间</th>';
               $subject_data .= '</tr>';
               foreach($rs_subject as $k=>$v)
               {
                   $subject_data .= '<tr>';
                   $subject_data .= '<td>'.$v['name'].'</td>';
                   $subject_data .= '<td>'.$v['subject_no'].'</td>';
                   $subject_data .= '<td>'.$v['type_bn'].'</td>';
                   $subject_data .= '<td>'.$v['subject_edu_score'].'</td>';
                   $subject_data .= '<td>'.$v['subject_type'].'</td>';
                   $subject_data .= '<td>'.$v['exam_type'].'</td>';
                   $subject_data .= '<td>'.$v['exam_time'].'</td>';
                   $subject_data .= '</tr>';
               }
               $subject_data .= '</table>';
           }
           $rs['rp_des'] .= $subject_data;    
        }
        $rs['channelLists'] = get_channel_lists($rs);
        return $rs;
	}
    //最少预付定金
    public function get_deposit_price($deposit_price=0)
    {
        $arr_deposit_price = [
            1 => 100,
            2 => 500,
        ];
        $channelType = 2;
        (isset($_POST['channelType']) && !empty($_POST['channelType'])) 
        && in_array($_POST['channelType'],[1,2]) 
        && $channelType = $_POST['channelType'];
        $price = $arr_deposit_price[$channelType];
        //dump($price);
        //if($deposit_price>0) $price=$deposit_price; 
        return sprintf('%0.2f',$price);  
    }
}
