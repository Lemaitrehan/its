<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

return [
    //分页配置
    'paginate'               => [
        'type'      => 'bootstrap',
        'var_page'  => 'p',
        'list_rows' => 15,
    ],
    'extra_config_list'     => ['database'],
    'database'              => [
        // 数据库类型
        'type'        => 'mysql',
        'hostname'    => '127.0.0.1',
        // 数据库名
        'database'    => 'zhongpeng_mbis_accreds',
        // 数据库用户名
        'username'    => 'root',
        // 数据库密码
        'password'    => 'sasa',
        // 数据库连接端口
        'hostport'    => '3306',
        // 数据库连接参数
        'params'      => [],
        // 数据库编码默认采用utf8
        'charset'     => 'utf8',
        // 数据库表前缀
        'prefix'      => 'mix_',
        // 数据库调试模式
        'debug'       => false,
    ]
];
