<?php 
namespace application\admin\validate;
use think\Validate;
/**
 * 权限验证器
 */
class GoodsAppraises extends Validate{
	protected $rule = [
		['isShow','require','状态不能为空'],
		['goodsScore','number|gt:0','评分只能是数字|评分必须大于0'],
		['timeScore','number|gt:0','评分只能是数字|评分必须大于0'],
		['serviceScore','number|gt:0','评分只能是数字|评分必须大于0'],
		['content','length:3,50','评价内容3-50个字'],
    ];

    protected $scene = [
        'edit'=>['isShow','goodsScore','timeScore','serviceScore','content'],
    ]; 
}