<?php
namespace application\index\behavior;
/**
 * 初始化基础数据
 */
class InitConfig 
{
    public function run(&$params){
        MBISConf('protectedUrl',model('HomeMenus')->getMenusUrl());
        MBISConf('CONF',MBISConfig());
    }
}