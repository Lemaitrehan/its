<?php
namespace application\admin\model;
/**
 * 学历类缴费处理
 */
use think\Db;
class StudentBillFeeLog extends Base{
    
    //过滤重复数据处理
    public function filter_repeat_data(&$data)
    {
        $repeat_data = [];
        foreach($data['data'] as $k=>&$v)
        {
           if(empty($v['receipt_no'])) continue;
           $id = $this->where(['receipt_no'=>$v['receipt_no']])->value('id');
           !empty($id) && $repeat_data[] = $v;
           if(!empty($id)) unset($data['data'][$k]);
        }
        $data['repeat_data'] = $repeat_data;
    }
    
    //字段导入再处理
    public function format_import_student_no(&$data)
    {
        foreach($data['data'] as &$v)
        {
           $v['userId'] = Db::name('users')->where(['userType'=>0,'student_no'=>$v['student_no'] ])->value('userId');   
        }
    }
    public function format_import_school_name(&$data)
    {
        foreach($data['data'] as &$v)
        {
           $v['school_id'] = Db::name('school')->where('name',$v['school_name'])->value('school_id');   
        }
    }
    public function format_import_level_name(&$data)
    {
        foreach($data['data'] as &$v)
        {
           $v['level_id'] = ITSSelItemId('major','level_type',$v['level_name']);   
        }
    }
    public function format_import_major_name(&$data)
    {
        foreach($data['data'] as &$v)
        {
           $v['major_id'] = Db::name('major')->where('name',$v['major_name'])->value('major_id');     
        }
    }
    public function format_import_receipt_time(&$data)
    {
        foreach($data['data'] as &$v)
        {
           $v['receipt_time'] = strtotime(gmdate('Y-m-d',intval(($v['receipt_time'] - 25569) * 3600 * 24)));     
        }
    }
    public function format_import_bill_type(&$data)
    {
        foreach($data['data'] as &$v)
        {
           $v['bill_type'] = ITSSelItemId('fee','bill_type',$v['bill_type']);   
        }
    }
    public function format_import_bill_way(&$data)
    {
        foreach($data['data'] as &$v)
        {
           $v['bill_way'] = ITSSelItemId('fee','bill_way',$v['bill_way']);   
        }
    }
    public function format_import_sign_name(&$data)
    {
        foreach($data['data'] as &$v)
        {
           $v['signUserId'] = Db::name('users')->where(['userType'=>2,'trueName'=>$v['sign_name']])->value('userId');   
        }
    }
}
