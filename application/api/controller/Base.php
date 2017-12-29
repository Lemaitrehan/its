<?php
namespace application\api\controller;
/**
* 基础控制器
 */
use think\Controller;
class Base extends Controller {
    private $curReqParams = null;
    private $curUserInfo = null;
	public function __construct(){
		parent::__construct();
        @file_put_contents('./api_params.log',var_export($_POST,true).chr(10),FILE_APPEND);
        $this->_check_common();
        $this->assign('domainUrl',request()->domain());
        //echo $this->gen_sign($_POST);
        //$this->check_sign($_POST);
        //if(!$this->check_sign($_POST)) MBISApiReturn(MBISReturn("Token有误"));
		$this->assign("v",MBISConf('CONF.wstVersion')."_".MBISConf('CONF.wstPcStyleId'));
		define('UID', input('userId') );
        $this->curReqParams = input('post.');
        $this->curUserInfo = model('common/users')->get_info(['userId'=>input('post.userId')]);
        $this->set_api_log();
	}
    /**
     * @do 公共处理
     * @param userId post
     * @param accesstoken post
     */
    private function _check_common()
    {
        $check_login_controllers = ['carts','orders','users'];
        //$module = strtolower(request()->module());
        $controller = strtolower(request()->controller());
        in_array($controller,$check_login_controllers) && 
        checkLogin();
    }
    //请求参数
    public function getCurReqParams()
    {
        //json_decode
        !empty($this->curReqParams['postJson']) && $postJson = json_decode(html_entity_decode($this->curReqParams['postJson']),true);
        !empty($postJson) && $this->curReqParams = array_merge($this->curReqParams,$postJson);
        //$request = request();
        //$visit = strtolower($request->module()."/".$request->controller()."/".$request->action());
         !empty($this->curReqParams['jump_type']) && $this->curReqParams['type_id'] = $this->curReqParams['jump_type'];
        return $this->curReqParams;   
    }
    //用户信息
    public function getCurUserInfo()
    {
        return $this->curUserInfo;   
    }
    private function check_sign($params)
    {
        $flag = false;
        !empty($params['sign'])
         && $params['sign']==$this->gen_sign($params)
         && $flag=true;
        $flag==false && MBISApiReturn(MBISReturn("Token有误"));
        return $flag;
    }
    public function un_set(&$params,$key='')
    {
        unset($params[$key]);
    }
    /**
     * echo $this->gen_sign($_POST);exit;
     *   #dump($this->assemble($_POST));exit;
    */
    public function gen_sign($params){
        !empty($params['sign']) && $this->un_set($params,'sign');
        return strtoupper(md5(strtoupper(md5($this->assemble($params))).ITS_TOKEN));
    }
	public function assemble($params) 
	{ 
		if(!is_array($params))  return null; 
		ksort($params,SORT_STRING); 
		$sign = ''; 
		foreach($params AS $key=>$val){ 
			if(is_null($val))   continue;
            if(is_bool($val))   $val = ($val) ? 1 : 0;
			$sign .= $key . (is_array($val) ? $this->assemble($val) : $val); 
		} 
		return $sign; 
	}
    private function set_api_log()
    {
        $is_api_log = model('common/base')->checkIsModel('api_log');
        $log_type_arr = ['school'=>1,'members'=>2,'carts'=>3,'orders'=>3,'passport'=>4,'promotion'=>3];
        $is_api_log == true && $log_data = [
            'agent_uid' => !empty($this->curUserInfo['isAgentUser'])?(int)@$this->curReqParams['userId']:0,
            'userId' => empty($this->curUserInfo['isAgentUser'])?(int)@$this->curReqParams['userId']:0,
            'type_id' => !empty($this->curReqParams['jump_type'])?$this->curReqParams['jump_type']:0,
            'api_type' => 2,
            //'log_type' => @$log_type_arr[strtolower(request()->controller())],
            'api_url' => strtolower(request()->module()."/".request()->controller()."/".request()->action()),
            'params' => htmlentities(json_encode($this->getCurReqParams())),
            'params_raw' => htmlentities(json_encode($_REQUEST)),
            'system' => (int)@ITSSelItemId('common','platform',$this->curReqParams['system'],'key'),
            'version' =>(int)@$this->curReqParams['version'],
            'status' => 1,
            'createtime' => time(),
            'lastmodify' => time(),
        ];
        $is_api_log == true && \think\Db::name('api_log')->insert($log_data);   
    }
}