<?php 
namespace application\common\validate;
use think\Validate;
/**
 * 评价验证器
 */
class GoodsAppraises extends Validate{
	protected $rule = [
        ['goodsScore'  ,'between:1,5','评分必须在1-5之间'],
        ['serviceScore'  ,'between:1,5','评分必须在1-5之间'],
        ['timeScore'  ,'between:1,5','评分必须在1-5之间'],
        ['content'  ,'require|length:3,600','点评内容不能为空|点评内容应为3-200个字'],
    ];

    protected $scene = [
        'add'   =>  ['goodsScore','serviceScore','timeScore','content'],
    ]; 
}