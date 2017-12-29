<?php 
namespace application\admin\validate;
use think\Validate;
/**
 * 角色验证器
 */
class Roles extends Validate{
	protected $rule = [
        ['roleName'  ,'require|max:30','请输入角色名称|角色名称不能超过10个字符']
    ];

    protected $scene = [
        'add'   =>  ['menuName'],
        'edit'  =>  ['menuName']
    ]; 
}