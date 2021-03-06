<?php 
namespace application\common\validate;
use think\Validate;
/**
 * 门店分类验证器
 */
class ShopCats extends Validate{
	protected $rule = [
        ['catName'  ,'require|max:60','请输入分类名称|分类名称不能超过20个字符'],
        ['parentId'  ,'number','无效的父级分类']
    ];

    protected $scene = [
        'add'   =>  ['catName','parentId'],
        'edit'  =>  ['catName'],
    ]; 
}