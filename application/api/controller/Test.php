<?php
namespace application\api\controller;
use think\Db;
use think\Validate;
use think\Cache;
use application\common\model\Jr_contact as Contact;

class Test extends Base
{
    /*
     * test
     */
    public function test()
    {
        $table = input('param.table');
        $lastTime = input('param.lasttime');
        $where = [];
        if(!empty($lastTime)){
           $where =  [
               'lastmodify'=>[
                   '>=',$lastTime,
               ],
           ];
        }
        $result = Db::name($table)->where($where)->select();
        echo json_encode($result);
        exit;
    }

}