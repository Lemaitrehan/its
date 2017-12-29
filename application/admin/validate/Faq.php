<?php 
namespace application\admin\validate;
use think\Validate;
/**
 * 权限验证器
 */
class Faq extends Validate{
	protected $rule = [
	    ['articleTitle'  ,'require|max:180','请输入帮助标题|帮助标题不能超过60个字符'],
	    ['articleContent'  ,'require','请输入帮助内容']
    ];

    protected $scene = [
        'add'   =>  ['articleTitle','articleContent'],
        'edit'  =>  ['articleTitle','articleContent']
    ]; 
}