<?php 
namespace application\admin\validate;
use think\Validate;
/**
 * 权限验证器
 */
class Areas extends Validate{
	protected $rule = [
	    ['areaName'  ,'require|max:30','请输入地区名称|地区名称不能超过10个字符'],
		['areaKey'  ,'require|max:2','请输入排序字母|排序字母不能超过1个字符'],
	    ['areaSort'  ,'require|max:16','请输入排序号|排序号不能超过8个字符'],
    ];

    protected $scene = [
        'add'   =>  ['areaName','areaKey','areaSort'],
        'edit'  =>  ['areaName','areaKey','areaSort'],
    ]; 
}