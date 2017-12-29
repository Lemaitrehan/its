<?php
namespace application\common\model;
use think\Db;
/**
 *mail邮件 
 */
class Mail extends Base{
    // $sendto_email, $subject, $body, $extra_hdrs, $user_name
    function smtp_mail(){
        vendor('class.phpmailer.php#PHPMailer');
        
        $mail = new \PHPMailer();
        $mail->IsSMTP();                  // send via SMTP
        $mail->Host = "smtp.163.com";   // SMTP servers
        $mail->SMTPAuth = true;           // turn on SMTP authentication
        $mail->Username = "lijianhua_nihao@163.com";     // SMTP username  注意：普通邮件认证不需要加 @域名
        $mail->Password = "lijianhua1234"; // SMTP password
        $mail->From = "lijianhua_nihao@163.com";      // 发件人邮箱
        $mail->FromName =  "xxxxxxxxxxx";  // 发件人
        
        $mail->CharSet = "GB2312";   // 这里指定字符集！
        $mail->Encoding = "base64";
        $mail->AddAddress('553110736@qq.com',"username");  // 收件人邮箱和姓名
        $mail->AddReplyTo("lijianhua_nihao@163.com","lijianhua_nihao@163.com");
        //$mail->WordWrap = 50; // set word wrap 换行字数
        //$mail->AddAttachment("/var/tmp/file.tar.gz"); // attachment 附件
        //$mail->AddAttachment("/tmp/image.jpg", "new.jpg");
        $mail->IsHTML(true);  // send as HTML
        // 邮件主题
        $mail->Subject = '测试主题';
        // 邮件内容
        $mail->Body = '我日';
    }
    function xx(){
        $this->smtp_mail("yourmail@yourdomain.com", "欢迎使用phpmailer！", "NULL", "yourdomain.com", "username");
        
    }
   
}
