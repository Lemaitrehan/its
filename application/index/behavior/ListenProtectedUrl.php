<?php
namespace application\index\behavior;
/**
 * 检测用户有没有登录和访问权限
 */
class ListenProtectedUrl 
{
    public function run(&$params){
	    $request = request();
	    $urls = MBISConf('protectedUrl');
        $visit = strtolower($request->module()."/".$request->controller()."/".$request->action());
        //受保护资源进来检测身份
        if(isset($urls[$visit])){
        	$menuType = (int)$urls[$visit];
        	$userType = -1;
        	if((int)session('MBIS_USER.userId')>0)$userType = 0;
        	if((int)session('MBIS_USER.shopId')>0)$userType = 1;
        	//未登录不允许访问受保护的资源
        	if($userType==-1){
		        if($request->isAjax()){
		        	echo json_encode(['status'=>-999,'msg'=>'对不起，您还没有登录，请先登录']);
		        }else{
		        	header("Location:".url('home/users/login'));
		        }
		        exit();
        	}
        	//已登录但不是商家 则不允许访问受保护的商家资源
        	if($userType==0 && $menuType==1){
        	    if($request->isAjax()){
		        	echo json_encode(['status'=>-999,'msg'=>'对不起，您不是商家，请先申请为商家再访问']);
		        }else{
		        	header("Location:".url('home/shops/login'));
		        }
		        exit();
        	}
	        
        }
    }
}