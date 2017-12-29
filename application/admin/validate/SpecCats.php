<?php
namespace application\admin\validate;
use think\Validate;
/**
 * 规格类型验证器
 */
class SpecCats extends Validate{
	protected $rule = [
		['catName|max:30', 'require', '请输入规格名称|规格名称不能超过10个字符'],
		['goodsCatId','require|gt:0', '请选择所属商品分类'],
		['isAllowImg','require|in:0,1', '请选择是否显示允许上传图片'],
		['isShow','require|in:0,1', '请选择是否显示']
	];
	protected $scene = [
		'add'=>['catName','goodsCatId','isAllowImg','isShow'],
		'edit'=>['catName','goodsCatId','isAllowImg','isShow']
	];
	
}