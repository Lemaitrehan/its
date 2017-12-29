<?php
namespace application\admin\behavior;
/**
 * 检测有没有访问权限
 */
class ListenPrivilege 
{
    public function run(&$params){
		return true;
        $privileges = session('MBIS_STAFF.privileges');
        $urls = MBISConf('listenUrl');
        $request = request();
        $visit = strtolower($request->module()."/".$request->controller()."/".$request->action());
        if(array_key_exists($visit,$urls) && !in_array($urls[$visit]['code'],$privileges)){
        	if($request->isAjax()){
        		echo json_encode(['status'=>-998,'msg'=>'对不起，您没有操作权限，请与管理员联系']);
        	}else{
        		header("Content-type: text/html; charset=utf-8");
        	    echo "对不起，您没有操作权限，请与管理员联系";
        	}
        	exit();
        }
    }
}