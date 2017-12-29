<?php 
namespace application\admin\validate;
use think\Validate;
/**
 * 广告验证器
 */
class Ads extends Validate{
	protected $rule = [
        ['adName'  ,'require|max:30','请输入广告标题|广告标题不能超过10个字符'],
        ['adFile'   ,'require','请上传广告图片'],
        ['adStartDate' , 'require|date', '请输入广告开始时间|广告时间格式错误' ],
        ['adEndDate'   , 'require|lt:adStartDate|date|checkDate:1', '请输入广告结束时间|广告结束时间必须大于开始时间|广告时间格式错误'],
    ];

    protected $scene = [
        'add'   =>  ['adName','adURL','adStartDate','adEndDate'],
        'edit'  =>  ['adName','adURL','adStartDate','adEndDate'],
    ]; 

    // 自定义验证规则
    /*
    * $value:该字段的值
    * $rule:自定义规则:后面的值
    * $data:所有数据
    */
    protected function checkDate($value,$rule,$data)
    {
        return (strtotime($value)>strtotime($data['adStartDate']))? true : '广告开始时间不能大于结束时间';
    }
}