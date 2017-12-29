<?php
namespace application\admin\behavior;
/**
 * 记录用户的访问日志
 */
class ListenOperate 
{
    public function run(&$params){
        $urls = MBISConf('listenUrl');
        $request = request();
        $visit = strtolower($request->module()."/".$request->controller()."/".$request->action());
        if(array_key_exists($visit,$urls) && $urls[$visit]['isParent']){
        	$data = [];
        	$data['menuId'] = $urls[$visit]['menuId'];
        	$data['operateUrl'] = $_SERVER['REQUEST_URI'];
        	$data['operateDesc'] = $urls[$visit]['name'];
        	$data['content'] = !empty($_REQUEST)?json_encode($_REQUEST):'';
        	$data['operateIP'] = $request->ip();
        	model('admin/LogOperates')->add($data);
        }
    }
}