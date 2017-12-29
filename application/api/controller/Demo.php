<?php
namespace application\api\controller;

class Demo
{
    public function index()
    {
        return json_encode(array('code'=>0,'msg'=>'获取数据成功！','data'=>array()));
    }
    
    public function test()
    {
        return json_encode(array('code'=>0,'msg'=>'test is ok...','data'=>array()));
    }
    
}
