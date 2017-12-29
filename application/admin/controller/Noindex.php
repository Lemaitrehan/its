<?php
namespace application\admin\controller;
/**
 * 空白页提示
 */
class Noindex extends Base{
	
    public function index(){
        $url = SERVERHOST.'/static/images/noindex.jpg';
        $this->assign('url',$url);
    	return $this->fetch("noindex");
    }
   
    
}
