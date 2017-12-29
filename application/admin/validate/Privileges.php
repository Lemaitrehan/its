<?php 
namespace application\admin\validate;
use think\Validate;
/**
 * 权限验证器
 */
class Privileges extends Validate{
	protected $rule = [
	    ['privilegeName'  ,'require|max:60','请输入权限名称|权限名称不能超过20个字符'],
        ['privilegeCode'  ,'require|max:30','请输入权限代码|权限代码不能超过10个字符'],
        ['menuId'  ,'number','无效的权限菜单']
    ];

    protected $scene = [
        'add'   =>  ['privilegeName','privilegeCode','menuId'],
        'edit'  =>  ['privilegeName','privilegeCode'],
    ]; 
}