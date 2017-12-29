<?php
// +----------------------------------------------------------------------
// | 短信model
// +----------------------------------------------------------------------
// | Author: lijianhua
// +----------------------------------------------------------------------

namespace application\common\model;
use think\Db;

class SmsAll extends Base{
    
     protected  $zz_template_id = '';//组装后的模板信息
     protected  $template_id    = '';//模板id
     protected  $noticeContent  = '';//短信模板{}
     protected  $status         = 0;//短信发送默认状态   未审核
     protected  $template_type  = 0;//模板类型
     protected  $xlTeacher      = '学历老师 牛大师;18665054391';
     //特殊标签
     public  $specialTag = array(
     		'1'=>'{学员名称}',
     );
     //特殊标签
     public  $specialTagMemo = array(
     		'批量选择对象发送标签'=>'{学员名称}',
     );
     
     public   $sendType = array(
         '1'=>'短信',
         '2'=>'邮件',
         '3'=>'APP',
         '4'=>'微信'
     ); 
     
     //模板类型
     public $templetType = array(
         '1'=>'考试通知',
         '2'=>'缴费通知',
         '3'=>'毕业证领取通知',
         '4'=>'上课通知',
         '5'=>'成绩查询通知',
         '6'=>'学位申请通知',
         '7'=>'毕业申请通知',
         '8'=>'报考通知'
     );
     
     //发送状态
     public  $sendStatus = array(
            '-1'=> '发送失败',
            '0' => '未审核',
            '1' => '审核不通过',
            '2' => '审核通过',
            '3' => '已发送',
     );
     //模版审核
     public  $temStatus = array(
         '0' => '未审核',
         '1' => '审核不通过',
         '2' => '审核通过',
     );
     //是否收到回执
     public  $arrAccept = array(
         '0' => '没有回执',
         '1' => '收到回执',
     );
     
     /**
      * 模版重组
      * @param unknown $arrayTemplate 模版变量
      * @param unknown $arrayTemplate 模版标记
      * @return string 返回加工过后的模版
      */
     protected function templateAssembly($arrayTemplate,$arrTag){
         $template   = $this->noticeContent;
         if( count($arrTag) != count($arrayTemplate) ) {
             return '模板标记与模板变量不一致';
         }
     
         $content  = $this->content;//用户提交过来的内容
     
         foreach ($arrTag as $key => $v ){
             if( !strpos($content,$v) ){
                 return '对不起，您不能修改模板的标记位置！！！';
             }
         }
     
         foreach ($arrTag as $key => $v ){
             $content = str_replace($v,$arrayTemplate[$key],$content,$num);
             if(!$num){
                 return '模板标记和模板变量解析错误！！！';
             }
         }
         $this->zz_template_id = $content;
         return  true;
     }
     
     /**
      * 修改模板
      * @param unknown $arrayTemplate 模版变量
      * @return string 返回加工过后的模版
      */
     protected function editTemplateAssembly($arrayTemplate){
         $template   = $this->noticeContent;
         $arrTag     = $this->arrTag;
         $template_id = $this->template_id;
         $arrTag     = $arrTag[$template_id];
         if( count($arrTag) != count($arrayTemplate) ) {
             return '模板标记与模板变量不一致';
         }
          
         $content  = $this->content;//用户提交过来的内容
          
         foreach ($arrTag as $key => $v ){
             if( !strpos($content,$v) ){
                 return '对不起，您不能修改模板的标记位置！！！';
             }
         }
          
         foreach ($arrTag as $key => $v ){
             $content = str_replace($v,$arrayTemplate[$key],$content,$num);
             if(!$num){
                 return '模板标记和模板变量解析错误！！！';
             }
         }
         $this->zz_template_id = $content;
         return  true;
     }
     
     //邮件发送封装
     function smtp_mail( $sendto_email, $subject, $body){
	     	require_once EXTEND_PATH.'/PHPMailer/phpmailer.php';
	     	$mail = new \PHPMailer();
	     	$mail->IsSMTP();                  // send via SMTP
	     	$mail->Host = " smtp.163.com";   // SMTP servers
	     	$mail->SMTPAuth = true;           // turn on SMTP authentication
	     	$mail->Username = "lijianhua_nihao";     // SMTP username  注意：普通邮件认证不需要加 @域名
	     	$mail->Password = "lijianhua123456"; // SMTP password
	     	$mail->Port       = 25;
	     	$mail->From = "lijianhua_nihao@163.com";      // 发件人邮箱
	     	$mail->FromName =  "管理员";  // 发件人
	     	$mail->CharSet    ="UTF-8";                 //设定邮件编码，默认ISO-8859-1，如果发中文此项必须设置为 UTF-8
	     	$mail->Encoding = "base64";
	     	$mail->AddAddress($sendto_email,"xxxxxxxxxx");  // 收件人邮箱和姓名
	     	$mail->AddReplyTo("lijianhua_nihao@163.com","李建华");//回复店址
		     					//$mail->WordWrap = 50; // set word wrap 换行字数
	     	//$mail->AddAttachment("/var/tmp/file.tar.gz"); // attachment 附件
	     	// $mail->AddAttachment(EXTEND_PATH."/default.jpg", "new.jpg"); 附件
	     	$mail->IsHTML(true);  // send as HTML
	     	// 邮件主题
	     	$mail->Subject = $subject;
	     	// 邮件内容
	     	$mail->Body = $body;
	     	$mail->AltBody ="text/html";
	     	if(!$mail->Send())
	     	{   
	     	    return  "邮件错误信息: " . $mail->ErrorInfo;
		    }else{
		     	return true;
		    }
     }
     
     //查找员工基本信息
     
     function userInfo($userIds){
           $map['userId'] = ['in',$userIds];
           $arr = Db::name('users')->field('userId,trueName,userPhone,userEmail')
                                   ->where($map)
                                   ->select();
           return  $arr;
     }
     
     /**
      * 短信息存储
      */
     function addSms($data,$sms_id){
         $time    = time();
         $arrUser = $this->arrUser;
         $uid     = session('MBIS_STAFF')->staffId;
         $time    = time();
                 
         //新增发送     
             $arrUserId = [];
             foreach($data as $k => $t ){
                 $arrUserId[] = $t['userId'];
             }
             if(!$arrUserId){
                 return false;
             }
             $userIds = implode(',', $arrUserId);
             
             //编辑
             if($sms_id){
                 $affow_id = $this->where('req_id','=',$sms_id)->delete();
                 
                 //存储公共信息
                 $dataT = array(
                     'title'         => $data[0]['title'],
                     'jump_type'     => $this->jump_type,
                     //'content'       => '',
                     'template_id'   => $data[0]['template_id'],
                     'templateReplaceText' => $data[0]['templateReplaceText'],
                     'targetTime'    => $data[0]['targetTime'],
                     'userIds'       => $userIds,
                     'update_id'     => $uid,
                     'update_time'   => $time,
                 );
                  
                 $sms_template_obj = db::name('sms_template');
                 $affow_id   = $sms_template_obj->where('id','=',$sms_id)->update($dataT);
                 
             //新增    
             }else{
                 //存储公共信息
                 $dataT = array(
                     'title'         => $data[0]['title'],
                     'jump_type'     => $this->jump_type,
                     //'content'       => '',
                     'template_id'   => $data[0]['template_id'],
                     'templateReplaceText'   => $data[0]['templateReplaceText'],
                     'targetTime'    => $data[0]['targetTime'],
                     'userIds'       => $userIds, 
                     'create_id'     => $uid,
                     'create_time'   => $time,
                     'update_id'     => $uid,
                     'update_time'   => $time,
                 );
                 
                 $sms_template_obj = db::name('sms_template');
                 $arrT   = $sms_template_obj->insert($dataT);
                 $sms_id = $sms_template_obj->getLastInsID();
             }
             
             foreach($data as $key => &$v ){
                 $data[$key]['req_id'] = $sms_id;
                 $v['update_time'] = $time;
                 $v['jump_type']   = $this->jump_type;
             }
             $ids  = $this->insertAll($data);
             return  $ids;
         
     }
     
     /**
      * 查找短信发送模版
      */
     function getSms($smsId){
         $res = Db::name('sms_template a')
                                 ->join('notice_tmpl tmpl','tmpl.notice_id = a.template_id','LEFT')
                                 ->where('a.id ='.$smsId)
                                 ->field('a.*,tmpl.send_type,tmpl.content as contentMb,FROM_UNIXTIME(a.targetTime,\'%Y-%m-%d\') as targetTime')
                                 ->find();
         return $res;
     }
     
     /**
      * 查找短信基本信息
      */
     function getSmsBase($smsId){
         $res = Db::name('sms')->where('smsId ='.$smsId)
                                ->field('*')
                                ->find();
         return $res;
     }
     
     
}
