<?php
namespace application\api\controller;
/**
 * 默认控制器
 */
class Index extends Base{
	
    public function index(){
    	$categorys = model('GoodsCats')->getFloors();
    	//MBISApiReturn(102,['list'=>[['name'=>'名称']]],'信息有误');
    }
    /**
     * 保存目录ID
     */
    public function getMenuSession(){
    	$menuId = input("post.menuId");
    	$menuType = session('MBIS_USER.loginTarget');
    	session('MBIS_MENUID3'.$menuType,$menuId);
    } 
    /**
     * 获取用户信息
     */
    public function getSysMessages(){
    	$rs = model('Systems')->getSysMessages();
    	return $rs;
    }
    /**
     * 定位菜单以及跳转页面
     */
    public function position(){
    	$menuId = (int)input("post.menuId");
    	$menuType = ((int)input("post.menuType")==1)?1:0;
    	session('MBIS_MENUID3'.$menuType,$menuId);
    }
}
