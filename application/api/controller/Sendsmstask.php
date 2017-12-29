<?php
namespace application\api\controller;
use think\Db;
use application\common\model\AppIndex as AppClass;
use application\common\model\Sms;
/**
 * 发送短信定时任务
 * @author LI
 *
 */
class Sendsmstask extends Base{
    
    public function smsQueue(){
        $Sms = new Sms();
        $Sms->sendSms();
    }
}



?>
