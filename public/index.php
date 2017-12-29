<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// [ 应用入口文件 ]

// 定义应用目录
error_reporting(E_ALL & ~E_NOTICE) ;
if(strpos($_SERVER['PATH_INFO'],'/api') !== FALSE)
{
    if(isset($_GET))
    {
        $_get = $_GET;
        $_POST = array_merge($_get,$_POST);
    }
}
if( $_SERVER['SERVER_PORT'] == 80 ){
    $serverhost = 'http://'.$_SERVER['HTTP_HOST'];
}else{
    $serverhost = 'https://'.$_SERVER['HTTP_HOST'];
}
define("APP_DEBUG", true);
define('ENTRY_PATH', 'public');
define("SERVERHOST", $serverhost);//定义主机头
define("SMSTIME", 60);//定义定时任务一次发送通知多少个用户
define('APP_PATH', __DIR__ . '/../application/');
//define('ROOT_PATH', str_replace('\\','/',realpath(__DIR__)) );
define('CONF_PATH', __DIR__.'/../application/common/conf/');
define('MBIS_COMM', __DIR__.'/../application/common/common/');
define('MBIS_HOME_COMM', __DIR__.'/../application/index/common/');
define('MBIS_ADMIN_COMM', __DIR__.'/../application/admin/common/');
define('MBIS_API_COMM', __DIR__.'/../application/api/common/');
// 加载框架引导文件
require __DIR__ . '/../thinkphp/start.php';
