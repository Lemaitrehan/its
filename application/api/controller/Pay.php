<?php
namespace application\api\controller;
use think\Request;
use application\common\model\PaymentConfig;
use think\Log;
/**
 * 品牌控制器
 */
class Pay extends Base{
    
    function callback(){
       //参数获取
       $type = Request::instance()->param('type');
       $id   = Request::instance()->param('id');
       
       //echo $id;die;
       $dopay = PaymentConfig::getPayMenthod($type);
      
       if($dopay){
           $dopay->callback($id,null,$msg);
       }else{
           Log::write("没有对应的支付方式。相关参数：POST=".var_export($_POST,1).",INPUT:".var_export(file_get_contents("php://input")));
           exit('xxx');
       }
       
    }
}