<?php
// +----------------------------------------------------------------------
// | 短信model
// +----------------------------------------------------------------------
// | Author: lijianhua
// +----------------------------------------------------------------------

namespace application\common\model;
use think\Db;
use application\common\model\SmsData;

class Sms extends SmsAll{
    
     protected  $smsUserName   = 'ZPPX_lg';//短信帐号
     protected  $smspassword   = '18128859099';//短信密码
     protected  $time          = '';//当前时间戳
     protected  $day           =  1;//离目标时间 {} 天通知
     protected  $limit         = 60;//一次发送多少条 短信
     protected  $autoLimit     = 60;//一次自动从考试表复制到 sms 表 里面 做队列
     protected  $is_auto       = 0;//自动或者是手动
     protected  $arrayTemplate = '';//模板变量
     protected  $content       = '';//用户提交过来的模板
     protected  $type          = '1';//发送方式 短信
     protected  $arrUser       =  array();//用户修改提交的短信 用户信息
     protected  $jump_type     =  '';//学历类型 1=>学历  2=》技能
     protected  $userEmail     =  '';//用户邮箱  
     protected  $title         =  '';//主题
     //查找模板
     function findTemplate($template_id){
         $arr_template = db::name('notice_tmpl')->field('content,send_type,title')->where('notice_id ='.$template_id)->find();
         $template      = $arr_template['content'];
         return array('template'  => $arr_template['content'],
         		      'send_type' => $arr_template['send_type'],
         		      'title'     => $arr_template['title']
         );
     }
     
     //保存到短信队列信息表里面
     /**
      * @param unknown $id 模板id
      * @param string $is_auto 是否是系统自动队列
      * @param string $sms_id 编辑短信
      * @param string $jump_type 1=>学历类 2=>技能类
      */
     function smsAdd($id,$is_auto='',$sms_id="",$jump_type=""){
         import('sms.YouYiSMS', EXTEND_PATH);
         $smsUserName     = $this->smsUserName;
         $smspassword     = $this->smspassword;
         $this->jump_type = $jump_type;
         $sms         = new \YouYiSMS($smsUserName, $smspassword);
         $arrTemplateInfo     = $this->findTemplate($id);//模板信息
         $this->noticeContent = $arrTemplateInfo['template'];//模板
         $this->type          = $arrTemplateInfo['send_type'];//发送方式
         $this->title          = $arrTemplateInfo['title'];//发送主题
         $this->is_auto       = $is_auto;//自动或手动
         $this->content       = input('post.content');//用户提交的模板
         $this->template_id   = $id;//模板id
         $this->status        = input('post.status')?input('post.status'):0;//选项发送状态
         
         $userIds  = input('post.userIds');
         $arrUser  = $this->userInfo($userIds);
         return  $this->templateReplace($arrUser);
       
     }

//模板提交 
     public function templateReplace($arrUser){
         $a = array(1,2);
         $a[2] =3;
     	 $arrSpecialTag    = $this->specialTag;
     	 $arrayTemplate    = $_POST['smsText'];//短信变量替换
     	 $contentTemplate  = $this->content;//用户提交过来的模板
         foreach($arrUser as $v ){
             preg_match_all('/\{[^\{\}]*\}/', $contentTemplate,$arrayTemplateTag);
             //模板标记 和 对应 的值
             $arrayTemplateRe = array();
             if($arrayTemplateTag){
	             foreach ($arrayTemplateTag[0] as $key => $v1){
	             	if($v1==$arrSpecialTag[1]){
	             	   $arrayTemplate[$key] = $v['trueName'].'('.$v['userPhone'].')';
	             	}
	             	$arrayTemplateRe[ $v1 ] = $arrayTemplate[$key];
	             }
             }
          
             $res = $this->templateAssembly($arrayTemplate,$arrayTemplateTag[0]);
             if( $res !== true ){
                 MBISApiReturn( MBISReturn($res,-1,'' ) );
             }else{
                 $template  = $this->zz_template_id;
             }
             $targetTime = input('post.targetTime')?strtotime( input('post.targetTime') ):'';
             $data[]   = array(
                 'userId'      => $v['userId'],
                 'userPhone'   => $v['userPhone'],
                 'content'     => $template,
                 'title'       => $this->title,
             	 'userEmail'   => $v['userEmail'],
                 'smsTime'     => time(),
                 'type'        => $this->type,
                 'status'      => $this->status,
                 'createId'    => session('MBIS_STAFF')->staffId?session('MBIS_STAFF')->staffId:0,
                 'template_id' => $this->template_id,
             	 'templateReplaceText' => serialize($arrayTemplateRe),
                 'targetTime'  => $targetTime
             );
         }
         //存;储短信（后发）
         $ids = $this->addSms( $data,input('post.sms_id') );
         if($ids){
             return  true;
         }else{
             return  false;
         }
     }

    
     /**
      * 发送成功后 修改 考试表的 发送状态
      * @param unknown $arr_sj_exams_id 考试表的id
      */
     function editSendSmsStatus($arr_sj_exams_id){
         if(!$arr_sj_exams_id){
             return '';
         }
         $list  = [];
         foreach ($arr_sj_exams_id as $key => $v ){
             $list[] = ['id'=>$v,'is_sms'=>1 ];
         }
         $obj_SjExams =  new \application\admin\model\SjExams;
         $obj_SjExams->saveAll($list);
          
     }
     
     /**
      * 短信队列发送
      * @$type//接收的类型 1=》 短信 2=》 邮件 3=.app
      */
     function sendSms($type=""){
    
         //读取短信的基本信息
            //发送状态 -1=> 发送失败 0=》未发送  1=> 暂不发送  2=》 已经发送
         #1.查找还没有发送的短信 手动的
         #2 查找自动保存  考试预提前 几天 发送
         $time            = time();
         $day             = $this->day;
         $day             = $day*24*60*60;
         //app
         if($type){
             $map = "( ( status = -1 ||  status = 2 )  AND type = 3 ) OR  ( targetTime >0 AND targetTime - $time <= $day ) ";
         
         //短信邮箱
         }else{
             $smsUserName = $this->smsUserName;
             $smspassword = $this->smspassword;
             import('sms.YouYiSMS', EXTEND_PATH);
             $sms  = new \YouYiSMS($smsUserName, $smspassword);
             $map = "( ( status = -1 ||  status = 2 )  AND type IN (1,2) ) OR ( targetTime >0 AND  targetTime - $time <= $day ) ";
         }

         $res = $this->field('type,smsId,userPhone,content,type,userEmail,title')
                     ->where($map)
                     ->LIMIT( $this->limit )
                     ->select();
         if($type){
              $arrSmsInfo = array();
              $url  = url('Api/Sendsmsaccept/accept');
              if($res){
                
                  $arrSms = array();
                  foreach ($res as $v){
                      $url1 = $url.'?sms_id='.$v['smsId'];
                      $arrSmsInfo[] = array(
                            'title'  => $v['title'],
                            'content'=> $v['content'],
                            'url'    => SERVERHOST.$url1
                      );
                      $arrSms[] = $v['smsId'];
                  }
               
                  $SmsIdS = implode(',', $arrSms);
                  $where['smsId'] = ['in',$SmsIdS];
                  $data = [
                      'status'   => 2,
                      'sendTime' => $time
                  ];
                  $this->where($where)->update($data);
             }
             return $arrSmsInfo;
         }else{
             foreach ($res as $v ){
             	 //短信发送
             	 if( $v['type'] == '1' ){
    	             $sms_id = $sms->sendSMS($v['userPhone'],$v['content']);
    	             //发送失败 修改状态
    	             if($sms_id['smsid']){
    	                 $status =  3;
    	             }else{
    	                 $status = -1;
    	             }
    	         //邮件发送    
             	 }elseif($v['type'] == '2'){
             	 	  $res1 = $this->sendMail($v['userEmail'],$v['title'],$v['content'],$v['smsId']);
             	 	  dump($res1) ;
             	 	  if($res1){
             	 	  	$status =  3;
             	 	  }else{
             	 	  	$status = -1;
             	 	  }
             	 }
             	 
             	 $this->save([
             	 		'status'   => $status,
             	 		'sendTime' => $time
             	 		],['smsId' => $v['smsId'] ]
             	 );
             }
         }
         
     
     }
     //邮箱
     public function sendMail($from,$title,$body,$sms_id)
     {
       $url = SERVERHOST.'/'.url('Api/Sendsmsaccept/reply').'?sms_id='.$sms_id.'&action=main';
       $is_accept_div = '<div><a  target="_self" href="'.$url.'">收到邮件后请回复</a></div>';
       $body = htmlspecialchars_decode($body);
	   $html =     '<!DOCTYPE html>
			     	<html>
			     	<head>
			     	<meta charset="UTF-8">
			     	<title>Insert title here</title>
			     	</head>
			     	<body>'.
			     	   $body.$is_accept_div
			     	.'</body>
			     	</html>';
     	// 参数说明(发送到,邮件主题, 邮件内容)
     	  return $this->smtp_mail($from,$title,$html);
     	 
     }
     
     //修改邮件回执
     public function mailAccept(){
         $smsId   = input('sms_id');
         $action  = input('action');
         $time    = time();
         $data    = array(
                 'is_accept_time' => $time,
                 'is_accept'      => 1,
                 'update_time'    => $time
         );
         $aff_id  = $this->where('smsId',$smsId)->update($data);
         if($action){
                $html =     '<!DOCTYPE html>
			     	<html>
			     	<head>
			     	<meta charset="UTF-8">
			     	<title>Insert title here</title>
			     	</head>
			     	<body>
			     	   <h1>谢谢您的回执，祝您生活愉快</h1>
			     	</body>
			     	</html>';
                 echo $html;
             exit; 
         }
         if($aff_id){
             MBISApiReturn( MBISReturn('回复成功！！！',1 ) );
         }else{
             MBISApiReturn( MBISReturn('回复成功！！！',0 ) );
         }
         
     }
    
     
     
	     
}
