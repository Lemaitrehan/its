<?php 
namespace application\admin\validate;
use think\Validate;
/**
 * 权限验证器
 */
class Articles extends Validate{
	protected $rule = [
	    ['articleTitle'  ,'require|max:48','请输入文章标题|文章标题不能超过16个字符'],
		['articleKey'  ,'require|max:300','请输入关键字|关键字不能超过100个字符'],
	    ['articleContent'  ,'require','请输入文章内容']
    ];

    protected $scene = [
        'add'   =>  ['articleTitle','articleKey','articleContent'],
        'edit'  =>  ['articleTitle','articleKey','articleContent']
    ]; 
}