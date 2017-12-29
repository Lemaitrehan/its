<?php 
namespace application\admin\validate;
use think\Validate;
use think\Db;
/**
 * 职员验证器
 */
class Staffs extends Validate{
	protected $rule = [
	    ['loginName'  ,'require|max:20|checkLoginName:1','请输入登录账号|登录账号不能超过20个字符'],
	    ['loginPwd'  ,'require|min:6','请输入登录密码|登录密码不能少于6个字符'],
        ['staffName'  ,'require|max:60','请输入职员名称|职员名称不能超过20个字符'],
        ['workStatus','require|in:0,1','请选择工作状态|无效的工作状态值'],
        ['staffStatus','require|in:0,1','请选择账号状态|无效的账号状态值']
    ];

    protected $scene = [
        'add'   =>  ['loginName','loginPwd','staffName','workStatus','staffStatus'],
        'edit'  =>  ['staffName','workStatus','staffStatus']
    ]; 
    
    protected function checkLoginName($value){
    	$where = [];
    	$where['dataFlag'] = 1;
    	$where['loginName'] = $value;
    	$rs = Db::name('staffs')->where($where)->count();
    	return ($rs==0)?true:'该登录账号已存在';
    }
}