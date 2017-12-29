<?php
namespace application\common\model;
/**
 * 客户端处理器
 */
class Client extends Base{
    //protected static $isAndroid=false;
    //protected static $isIpad=false;
    //protected static $isIphone=false;
    private $client_key='';
    private $system='';
    private $version='';
    /**
      * 客户端标识判断
      */
    public function set_system_version($params=[])
    {
        $system = 'PC';
        $version = '1.0';
        !empty($params['system']) && $system = $params['system'];
        !empty($params['version']) && $version = strtolower($params['version']);
        $this->client_key = strtolower($system.$version);
        //return $this->client_key->strtolower($system.$version);
    }
    
    /**
      * 获取客户端key标识
      */
    public function get_client_key()
    {
        return $this->client_key;
    }
    
    

    
}
