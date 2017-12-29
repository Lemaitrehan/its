<?php 
namespace application\admin\validate;
use think\Validate;
/**
 * 广告位置验证器
 */
class AdPositions extends Validate{
	protected $rule = [
	    ['positionName|max:30'  ,'require','请输入位置名称|位置名称不能超过10个字符'],
	    ['positionCode|max:60'  ,'require','请输入位置代码|位置代码不能超过20个字符'],
		['positionType'  ,'require','请选择位置类型'],
	    ['positionWidth'  ,'require','请输入建议宽度'],
	    ['positionHeight'  ,'require','请输入建议高度'],
    ];

    protected $scene = [
        'add'   =>  ['positionName','positionCode','positionType','positionWidth','positionHeight'],
        'edit'  =>  ['positionName','positionCode','positionType','positionWidth','positionHeight'],
    ]; 
}