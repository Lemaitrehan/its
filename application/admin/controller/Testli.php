<?php
namespace application\admin\controller;
use think\Model;
class Testli extends Base
{
	
    public function index()
    {
     return   $this->fetch('index');
     
    }
    
   
    

    
    public static function ReplyEmail($email,$data){
    
    
        $email_server_arr = [
            'qq' => [
                'host' => 'smtp.exmail.qq.com',
                'port' => '465',
                'protocol' => 'smtp',
                'ssl' => true,
            ],
            'gmail' => [
                'host' => 'smtp.gmail.com',
                'port' => '465',
                'protocol' => 'smtp',
                'ssl' => true,
            ],
        ];
        $serive_info = $email_server_arr[$email->type];
    
        //echo realpath('../../common/lib/PHPMailer/class.phpmailer.php');
        //exit('===tttt');
    
        require_once dirname(__FILE__).'/../PHPMailer/class.phpmailer.php';
        require_once dirname(__FILE__).'/../PHPMailer/class.smtp.php';
        $mail  = new \PHPMailer();
        $mail->CharSet    ="UTF-8";                 //设定邮件编码，默认ISO-8859-1，如果发中文此项必须设置为 UTF-8
        $mail->IsSMTP();                            // 设定使用SMTP服务
        $mail->SMTPAuth   = true;                   // 启用 SMTP 验证功能
        $mail->SMTPSecure = "ssl";                  // SMTP 安全协议
        $mail->Host       = $serive_info['host'];       // SMTP 服务器
        $mail->Port       = $serive_info['port'];                    // SMTP服务器的端口号
        $mail->Username   = $email->email;  // SMTP服务器用户名
        $mail->Password   = $email->password;        // SMTP服务器密码
        $mail->SetFrom($email->email,$data['from_name']);    // 设置发件人地址和名称
    
        $mail->AddReplyTo($data['to'],$data['to_name']);
        $mail->addReferences($data['references']);
        $mail->addMessageId($data['message_id']); //in_reply_to
        $mail->addInReplyTo($data['in_reply_to']);
    
        // 设置邮件回复人地址和名称
        $mail->Subject    = $data['title'];                     // 设置邮件标题
        $mail->AltBody    = $data['body'];
    
        if(isset($data['cc'])){               //抄送
            foreach($data['cc'] as $cc){
                $mail->addCC($cc);
            }
        }
        // 可选项，向下兼容考虑
        $mail->MsgHTML($data['body']);                         // 设置邮件内容
        $mail->AddAddress($data['to'], $data['to_name']);
        //$mail->AddAttachment("images/phpmailer.gif"); // 附件
        if(!$mail->Send()){
            return false;
        } else {
            return true;
        }
    } 
}
