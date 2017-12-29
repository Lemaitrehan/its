<?php
namespace application\admin\validate;
use think\Validate;
/**
 * 支付验证器
 */
class Payments extends Validate{
	protected $rule = [
		['payName','require','支付名称不能为空'],
		['payDesc','require','支付描述不能为空'],
		['payOrder','require','排序号不能为空'],
	];
	protected $scene = [
		'edit'=>['payName','payDesc','payOrder'],
	];
}