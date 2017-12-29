<?php
namespace application\admin\validate;
use think\Validate;
/**
 * 导航验证器
 */
class Navs extends Validate{
	protected $rule = [
		['navTitle|max:30', 'require', '请输入导航名称|导航名称不能超过10个字符'],
		['navUrl','require', '请输入导航链接'],
	];
	protected $scene = [
		'add'=>['navTitle','navUrl'],
		'edit'=>['navTitle','navUrl'],
	];
	
}