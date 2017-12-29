<?php
namespace application\admin\behavior;
/**
 * 初始化基础数据
 */
class InitConfig 
{
    public function run(&$params){
        MBISConf('listenUrl',MBISVisitPrivilege());
        MBISConf('CONF',MBISConfig());
    }
}