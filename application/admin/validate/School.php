<?php 
namespace application\admin\validate;
use think\Validate;
/**
 * 学校验证器
 */
class School extends Validate{
	protected $rule = [
	    ['name'  ,'require|max:180','请输入学校名称|学校名称不能超过60个字符'],
        ['costst'  ,'require|between:0,1','请输入成本结算比例|成本结算比例范围在0-1']
    ];

    protected $scene = [
        'add'   =>  ['name','costst'],
        'edit'  =>  ['name','costst'],
    ]; 
}