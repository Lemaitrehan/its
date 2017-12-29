<?php
namespace application\api\controller;
use think\Db;
use application\common\model\Sms;
/**
 * APP获取信息
 * @author LI
 *
 */
class Sendsmsaccept extends Base{
    
    public function accept(){
        $Sms = new Sms();
        $res = $Sms->sendSms(1);
        return json_encode($res);
    }
    
    public function reply(){
        $Sms = new Sms();
        $res = $Sms->mailAccept();
    }
    
    
}


?>
