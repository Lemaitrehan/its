<?php 
namespace application\admin\validate;
use think\Validate;
/**
 * 权限验证器
 */
class Brands extends Validate{
	protected $rule = [
	    ['brandName'  ,'require|max:60','请输入品牌名称|品牌名称不能超过20个字符'],
		['brandImg'  ,'require','请上传品牌图标'],
		['brandDesc'  ,'require','请输入品牌介绍']
    ];

    protected $scene = [
        'add'   =>  ['brandName','brandImg','brandDesc'],
        'edit'  =>  ['brandName','brandImg','brandDesc']
    ]; 
}