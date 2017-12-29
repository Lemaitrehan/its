<?php
namespace application\api\controller;
use application\common\model\Users as MUsers;
use application\common\model\LogSms;
use application\common\model\Sms as itsSms;
/**
* 登录注册控制器
 */
class Passport extends Base{
	/**
     * 去登录
     */
	public function login(){
		$USER = session('MBIS_USER');
		//如果已经登录了则直接跳去用户中心
		if(!empty($USER) && $USER['userId']!=''){
			$this->redirect("users/index");
		}
		$loginName = cookie("loginName");
		if(!empty($loginName)){
			$this->assign('loginName',cookie("loginName"));
		}else{
			$this->assign('loginName','');
		}
		return $this->fetch('user_login');
	}
		    
    /**
	 * 用户退出
	 */
	public function logout(){
        checkLogin();
        $m = new MUsers();
		$rs = $m->logout();
		MBISApiReturn($rs);
	}
	
	/**
     * 用户注册
     * 
     */
	public function regist(){
		$loginName = cookie("loginName");
		if(!empty($loginName)){
			$this->assign('loginName',cookie("loginName"));
		}else{
			$this->assign('loginName','');
		}
		return $this->fetch('regist');
	}
	
	
	/**
	 * 新用户注册
	 */
	public function toRegist(){
		$m = new MUsers();
		$rs = $m->regist();
		MBISApiReturn($rs);
	}
	
	/**
	 * 验证登陆
	 *
	 */
	public function checkLogin(){
        try
        {
            $m = new MUsers();
            $rs = $m->checkLogin();
            MBISApiReturn($rs);
        }
        catch(\Exception $e)
        {
            //add_logs('passport_login','[Error] '.$e->getMessage());
            add_logs('exception/passport_login','',true,$e);
            return MBISReturn('登录失败');
        }
	}

	/**
	 * 获取验证码
	 */
	public function getPhoneVerifyCode(){
		$userPhone = input("post.userPhone");
		$rs = array();
		if(!MBISIsPhone($userPhone)){
			return MBISReturn("手机号格式不正确!");
			exit();
		}
		$m = new MUsers();
		$rs = $m->checkUserPhone($userPhone,(int)session('MBIS_USER.userId'));
		if($rs["status"]!=1){
			return MBISReturn("手机号已存在!");
			exit();
		}
		$phoneVerify = rand(100000,999999);
		$msg = "欢迎您注册成为".MBISConf("CONF.mallName")."会员，您的注册验证码为:".$phoneVerify."，请在10分钟内输入。【".MBISConf("mallName")."】";
		$m = new LogSms();
		$rv = $m->sendSMS(0,$userPhone,$msg,'getPhoneVerifyCode',$phoneVerify);

		if($rv['status']==1){
			session('VerifyCode_userPhone',$phoneVerify);
			session('VerifyCode_userPhone_Time',time());
		}
		return $rv;
	}
	
	
	/**
	 * 判断手机或邮箱是否存在
	 */
	public function checkLoginKey(){
		$m = new MUsers();
		if(input("post.loginName"))$val=input("post.loginName");
		if(input("post.userPhone"))$val=input("post.userPhone");
		if(input("post.userEmail"))$val=input("post.userEmail");
		$rs = MBISCheckLoginKey($val);
		if($rs["status"]==1){
			return array("ok"=>"");
		}else{
			return array("error"=>$rs["msg"]);
		}
	}
	
	/**
	 * 判断邮箱是否存在
	 */
	public function checkEmail(){
		$data = $this->checkLoginKey();
		if(isset($data['error']))$data['error'] = '对不起，该邮箱已存在';
		return $data;
	}
	
	/**
	 * 判断用户名是否存在/忘记密码
	 */
	public function checkFindKey(){
		$m = new MUsers();
		$userId = (int)session('MBIS_USER.userId');
		$rs = MBISCheckLoginKey(input("post.loginName"),$userId);
		if($rs["status"]==1){
			return array("error"=>"该用户不存在！");
		}else{
			return array("ok"=>"");
		}
	
	}
	
	/**
	 * 跳到用户注册协议
	 */
	public function protocol(){
		return $this->fetch("user_protocol");
	}
	
	/**
	 * 用户中心
	 */
	public function index(){
        $m = new MUsers();
        $res = $m->check_accesstoken('15bc33f6e098e9d454b3edc2bc118eee');
        if($res['status'] == -1)
        {
            MBISApiReturn($res['status'],$res['msg']);
        }
        MBISApiReturn();
	}
	

	/**
	* 跳去修改个人资料
	*/
	public function edit(){
		$m = new MUsers();
		//获取用户信息
		$userId = (int)session('MBIS_USER.userId');
        $data = $m->getById($userId);
        $this->assign('data',$data);
		return $this->fetch('users/user_edit');
	}
	/**
	* 跳去修改密码页
	*/
	public function editPass(){
		$m = new MUsers();
		//获取用户信息
		$userId = (int)session('MBIS_USER.userId');
		$data = $m->getById($userId);
		$this->assign('data',$data);
		return $this->fetch('users/security/user_pass');
	}
	/**
	* 修改密码
	*/
	public function passedit(){
		$userId = (int)session('MBIS_USER.userId');
		$m = new MUsers();
		$rs = $m->editPass($userId);
		return $rs;
	}
	/**
    * 修改
    */
    public function toEdit(){
        $m = new MUsers();
        $rs = $m->edit();
        return $rs;
    }
    /**
     * 安全设置页
     */
    public function security(){
    	//获取用户信息
    	$m = new MUsers();
    	$data = $m->getById((int)session('MBIS_USER.userId'));
    	if($data['userPhone']!='')$data['userPhone'] = MBISStrReplace($data['userPhone'],'*',3);
    	if($data['userEmail']!='')$data['userEmail'] = MBISStrReplace($data['userEmail'],'*',2,'@');
    	$this->assign('data',$data);
    	return $this->fetch('users/security/index');
    }
    /**
     * 修改邮箱页
     */
    public function editEmail(){
    	//获取用户信息
    	$userId = (int)session('MBIS_USER.userId');
    	$m = new MUsers();
    	$data = $m->getById($userId);
    	if($data['userEmail']!='')$data['userEmail'] = MBISStrReplace($data['userEmail'],'*',2,'@');
    	$this->assign('data',$data);
    	$process = 'One';
    	$this->assign('process',$process);
    	if($data['userEmail']){
    		return $this->fetch('users/security/user_edit_email');
    	}else{
    		return $this->fetch('users/security/user_email');
    	}
    }
    /**
     * 发送验证邮件/绑定邮箱
     */
    public function getEmailVerify(){
    	$userEmail = input('post.userEmail');
    	if(!$userEmail){
    		return MBISReturn('请输入邮箱!',-1);
    	}
    	$code = input("post.verifyCode");
    	$process = input("post.process");
    	if(!MBISVerifyCheck($code)){
    		return MBISReturn('验证码错误!',-1);
    	}
    	$rs = MBISCheckLoginKey($userEmail,(int)session('MBIS_USER.userId'));
    	if($rs["status"]!=1){
    		return MBISReturn("邮箱已存在!");
    		exit();
    	}
    	$key = base64_encode($userEmail."_".session('MBIS_USER.userId')."_".time()."_".$process.'_'.md5(session('MBIS_USER.loginSecret')));
    	$url = url('home/users/emailEdit',array('key'=>$key),true,true);
    	$html="您好，会员 ".session('MBIS_USER.loginName')."：<br>
		您在".date('Y-m-d H:i:s')."发出了绑定邮箱的请求,请点击以下链接进行绑定邮箱:<br>
		<a href='".$url."'>".$url."</a><br>
		<br>如果您的邮箱不支持链接点击，请将以上链接地址拷贝到你的浏览器地址栏中。<br>
		该验证邮件有效期为30分钟，超时请重新发送邮件。<br>
		<br><br>*此邮件为系统自动发出的，请勿直接回复。";
    	$sendRs = MBISSendMail($userEmail,'绑定邮箱',$html);
    	if($sendRs['status']==1){
    		return MBISReturn('发送成功',1);
    	}else{
    		return MBISReturn($sendRs['msg'],-1);
    	}
    }
    /**
     * 绑定邮箱
     */
    public function emailEdit(){
    	$USER = session('MBIS_USER');
		if(empty($USER) && $USER['userId']==''){
			$this->redirect("home/users/login");
		}
    	$key = input('param.');
    	if($key['key']=='')$this->error('连接已失效！');
    	$key = $key['key'];
    	$key = base64_decode($key);
    	$key = explode('_',$key);
        $loginKey = md5(session('MBIS_USER.loginSecret'));
        if($loginKey!==$key[4])$this->error('无效的请求！');
    	if(time()>floatval($key[2])+30*60)$this->error('连接已失效！');
    	if(intval($key[1])==0)$this->error('无效的用户！');
    	$rs = MBISCheckLoginKey($key[1],(int)session('MBIS_USER.userId'));
    	if($rs["status"]!=1){
    		$this->error('邮箱已存在!');
    		exit();
    	}
    	$m = new MUsers();
    	$rs = $m->editEmail($key[1],$key[0]);
    	if($rs['status'] == 1){
    		$process = 'Three';
    		$this->assign('process',$process);
    		if($key[3]=='Two'){
    			return $this->fetch('users/security/user_edit_email');
    		}else{
    			return $this->fetch('users/security/user_email');
    		}
    	}
    	$this->error('绑定邮箱失败');
    }
    /**
     * 发送验证邮件/修改邮箱
     */
    public function getEmailVerifyt(){
    	$m = new MUsers();
    	$data = $m->getById(session('MBIS_USER.userId'));
    	$userEmail = $data['userEmail'];
    	if(!$userEmail){
    		return MBISReturn('请输入邮箱!',-1);
    	}
    	$code = input("post.verifyCode");
    	if(!MBISVerifyCheck($code)){
    		return MBISReturn('验证码错误!',-1);
    	}
    	$key = base64_encode("0_".session('MBIS_USER.userId')."_".time()."_".md5(session('MBIS_USER.loginSecret')));
    	$url = url('home/users/emailEditt',array('key'=>$key),true,true);
    	$html="您好，会员 ".session('MBIS_USER.loginName')."：<br>
		您在".date('Y-m-d H:i:s')."发出了修改邮箱的请求,请点击以下链接进行修改邮箱:<br>
		<a href='".$url."'>".$url."</a><br>
		<br>如果您的邮箱不支持链接点击，请将以上链接地址拷贝到你的浏览器地址栏中。<br>
		该验证邮件有效期为30分钟，超时请重新发送邮件。<br>
		<br><br>*此邮件为系统自动发出的，请勿直接回复。";
    	$sendRs = MBISSendMail($userEmail,'修改邮箱',$html);
    	if($sendRs['status']==1){
    		return MBISReturn('发送成功',1);
    	}else{
    		return MBISReturn($sendRs['msg'],-1);
    	}
    }
    /**
     * 修改邮箱
     */
    public function emailEditt(){
    	$USER = session('MBIS_USER');
    	if(empty($USER) && $USER['userId']!=''){
    		$this->redirect("home/users/login");
    	}
    	$key = input('param.');
    	if($key['key']=='')$this->error('连接已失效！');
    	$key = $key['key'];
    	$key = base64_decode($key);
        $loginKey = md5(session('MBIS_USER.loginSecret'));
    	$key = explode('_',$key);
        if($loginKey!= $key[3])$this->error('无效的请求！');
    	if(time()>floatval($key[2])+30*60)$this->error('连接已失效！');
    	if(intval($key[1])==0)$this->error('无效的用户！');
    	$m = new MUsers();
    	$data = $m->getById($key[1]);
    	if($data['userId']==session('MBIS_USER.userId')){
    		$process = 'Two';
    		$this->assign('process',$process);
    		return $this->fetch('users/security/user_edit_email');
    	}
        $this->error('无效的用户！');
    }
    /**
     * 修改手机页
     */
    public function editPhone(){
    	//获取用户信息
    	$userId = (int)session('MBIS_USER.userId');
    	$m = new MUsers();
    	$data = $m->getById($userId);
    	if($data['userPhone']!='')$data['userPhone'] = MBISStrReplace($data['userPhone'],'*',3);
    	$this->assign('data',$data);
    	$process = 'One';
    	$this->assign('process',$process);
    	if($data['userPhone']){
    		return $this->fetch('users/security/user_edit_phone');
    	}else{
    		return $this->fetch('users/security/user_phone');
    	}
    }
    /**
     * 跳到发送手机验证
     */
    public function toApply(){
    	return $this->fetch("user_verify_phone");
    }
    /**
     * 绑定手机/获取验证码
     */
    public function getPhoneVerifyo(){
    	$userPhone = input("post.userPhone");
    	if(!MBISIsPhone($userPhone)){
    		return MBISReturn("手机号格式不正确!");
    		exit();
    	}
    	$rs = array();
    	$m = new MUsers();
    	$rs = MBISCheckLoginKey($userPhone,(int)session('MBIS_USER.userId'));
    	if($rs["status"]!=1){
    		return MBISReturn("手机号已存在!");
    		exit();
    	}
    	$phoneVerify = rand(100000,999999);
    	$msg = "欢迎您".MBISConf("CONF.mallName")."会员，正在操作绑定手机，您的校验码为:".$phoneVerify."，请在10分钟内输入。【".MBISConf("mallName")."】";
    	$m = new LogSms();
    	$rv = $m->sendSMS(0,$userPhone,$msg,'getPhoneVerify',$phoneVerify);
    	if($rv['status']==1){
    		$USER = '';
    		$USER['userPhone'] = $userPhone;
    		$USER['phoneVerify'] = $phoneVerify;
    		session('Verify_info',$USER);
    		session('Verify_userPhone_Time',time());
    		return MBISReturn('短信发送成功!',1);
    	}
    	return $rv;
    }
    /**
     * 绑定手机
     */
    public function phoneEdito(){
    	$phoneVerify = input("post.Checkcode");
    	$process = input("post.process");
    	$timeVerify = session('Verify_userPhone_Time');
    	if(!session('Verify_info.phoneVerify') || time()>floatval($timeVerify)+10*60){
    		return MBISReturn("校验码已失效，请重新发送！");
    		exit();
    	}
   		if($phoneVerify==session('Verify_info.phoneVerify')){
   			$m = new MUsers();
   			$rs = $m->editPhone((int)session('MBIS_USER.userId'),session('Verify_info.userPhone'));
   			if($process=='Two'){
   				$rs['process'] = $process;
   			}else{
   				$rs['process'] = '0';
   			}
   			return $rs;
   		}
   		return MBISReturn("校验码不一致，请重新输入！");
    }
    public function editPhoneSu(){
    	$pr = input("get.pr");
    	$process = 'Three';
    	$this->assign('process',$process);
	    if($pr == 'Two'){
	    	return $this->fetch('users/security/user_edit_phone');
	    }else{
	    	return $this->fetch('users/security/user_phone');
	    }
    }
    /**
     * 修改手机/获取验证码
     */
    public function getPhoneVerifyt(){
    	$m = new MUsers();
    	$data = $m->getById(session('MBIS_USER.userId'));
    	$userPhone = $data['userPhone'];
    	$phoneVerify = rand(100000,999999);
    	$msg = "欢迎您".MBISConf("CONF.mallName")."会员，正在操作修改手机，您的校验码为:".$phoneVerify."，请在10分钟内输入。【".MBISConf("mallName")."】";
    	$m = new LogSms();
    	$rv = $m->sendSMS(0,$userPhone,$msg,'getPhoneVerify',$phoneVerify);
     	if($rv['status']==1){
	    	$USER = '';
	    	$USER['userPhone'] = $userPhone;
	    	$USER['phoneVerify'] = $phoneVerify;
	    	session('Verify_info2',$USER);
	    	session('Verify_userPhone_Time2',time());
	    	return MBISReturn('短信发送成功!',1);
    	}
    	return $rv;
    }
    /**
     * 修改手机
     */
    public function phoneEditt(){
    	$phoneVerify = input("post.Checkcode");
    	$timeVerify = session('Verify_userPhone_Time2');
    	if(!session('Verify_info2.phoneVerify') || time()>floatval($timeVerify)+10*60){
    		return MBISReturn("校验码已失效，请重新发送！");
    		exit();
    	}
    	if($phoneVerify==session('Verify_info2.phoneVerify')){
    		return MBISReturn("验证成功",1);
    	}
    	return MBISReturn("校验码不一致，请重新输入！",-1);
    }
    public function editPhoneSut(){
    	$process = 'Two';
    	$this->assign('process',$process);
    	if(session('Verify_info2.phoneVerify')){
    		return $this->fetch('users/security/user_edit_phone');
    	}
        $this->error('地址已失效，请重新验证身份');
    }
    
    /**
    * 处理图像裁剪
    */
    public function editUserPhoto(){
        $imageSrc = trim(input('post.photoSrc'),'/');
        $image = \image\Image::open($imageSrc);
        $x = (int)input('post.x');
        $y = (int)input('post.y');
        $w = (int)input('post.w',150);
        $h = (int)input('post.h',150);
        $rs = $image->crop($w, $h, $x, $y, 150, 150)->save($imageSrc);
        if($rs){
            return MBISReturn('',1,$imageSrc);
            exit;
        }
        return MBISReturn('发生未知错误.',-1);

    }
    
    /**
     * 忘记密码
     */
    public function forgetPass(){
    	return $this->fetch('forget_pass');
    }
    public function forgetPasst(){
    	if(time()<floatval(session('findPass.findTime'))+30*60){
	    	$userId = session('findPass.userId');
	    	$m = new MUsers();
	    	$info = $m->getById($userId);
	    	if($info['userPhone']!='')$info['userPhone'] = MBISStrReplace($info['userPhone'],'*',3);
	    	if($info['userEmail']!='')$info['userEmail'] = MBISStrReplace($info['userEmail'],'*',2,'@');
	    	$this->assign('forgetInfo',$info);
	    	return $this->fetch('forget_pass2');
    	}else{
    		$this->error('页面已过期！');
    	}
    }
    public function forgetPasss(){
    	$USER = session('findPass');
    	if(empty($USER) && $USER['userId']!=''){
    		$this->error('请在同一浏览器操作！');
    	}
    	$key = input('param.');
    	if($key['key']=='')$this->error('连接已失效！');
    	$key = $key['key'];
    	$keyFactory = new \org\Base64();
    	$key = $keyFactory->decrypt($key,(int)session('findPass.loginSecret'));
    	$key = explode('_',$key);
    	if(time()>floatval($key[2])+30*60)$this->error('连接已失效！');
    	if(intval($key[1])==0)$this->error('无效的用户！');
    	session('REST_userId',$key[1]);
    	session('REST_Time',$key[2]);
    	session('REST_success','1');
    	return $this->fetch('forget_pass3');
    }
    public function forgetPassf(){
    	return $this->fetch('forget_pass4');
    }
    /**
     * 找回密码
     */
    public function findPass(){
    	//禁止缓存
    	header('Cache-Control:no-cache,must-revalidate');
    	header('Pragma:no-cache');
    	$code = input("post.verifyCode");
    	$step = input("post.step/d");
    	switch ($step) {
    		case 1:#第一步，验证身份
    			if(!MBISVerifyCheck($code)){
    				return MBISReturn('验证码错误!',-1);
    			}
    			$loginName = input("post.loginName");
    			$rs = MBISCheckLoginKey($loginName);
    			if($rs["status"]==1){
    				return MBISReturn("用户名不存在!");
    				exit();
    			}
    			$m = new MUsers();
    			$info = $m->checkAndGetLoginInfo($loginName);
    			if ($info != false) {
    				session('findPass',array('userId'=>$info['userId'],'loginName'=>$loginName,'userPhone'=>$info['userPhone'],'userEmail'=>$info['userEmail'],'loginSecret'=>$info['loginSecret'],'findTime'=>time()));
    				return MBISReturn("操作成功",1);
    			}else return MBISReturn("用户名不存在!");
    			break;
    		case 2:#第二步,验证方式
    			if (session('findPass.loginName') != null ){
    				if(input("post.modes")==1){
    					if ( session('findPass.userPhone') == null) {
    						return MBISReturn('你没有预留手机号码，请通过邮箱方式找回密码！',-1);
    					}
    					$phoneVerify = input("post.Checkcode");
    					if(!$phoneVerify){
    						return MBISReturn('校验码不能为空!',-1);
    					}
    					return $this->checkfindPhone($phoneVerify);
    				}else{
    					if (session('findPass.userEmail')==null) {
    						return MBISReturn('你没有预留邮箱，请通过手机号码找回密码！',-1);
    					}
    					if(!MBISVerifyCheck($code)){
    						return MBISReturn('验证码错误!',-1);
    					}
    					return $this->getfindEmail();
    				}
    			}else $this->error('页面已过期！');
    			break;
    		case 3:#第三步,设置新密码
    			$resetPass = session('REST_success');
    			if($resetPass != 1)$this->error("页面已失效!");
    			$loginPwd = input("post.loginPwd");
    			$repassword = input("post.repassword");
    			if ($loginPwd == $repassword) {
    				$m = new MUsers();
    				$rs = $m->resetPass();
    				if($rs['status']==1){
    					return $rs;
    				}else{
    					return $rs;
    				}
    			}else return MBISReturn('两次密码不同！',-1);
    			break;
    		default:
    			$this->error('页面已过期！');
    			break;
    	}
    }
    /**
     * 手机验证码获取
     */
    public function getfindPhone(){
    	$smsVerfy = input("post.smsVerfy");
    	session('MBIS_USER',session('findPass.userId'));
    	if(session('findPass.userPhone')==''){
    		return MBISReturn('你没有预留手机号码，请通过邮箱方式找回密码！',-1);
    	}
    	$phoneVerify = rand(100000,999999);
    	$msg = "您正在重置登录密码，验证码为:".$phoneVerify."，请在10分钟内输入。【".MBISConf("mallName")."】";
    	$m = new LogSms();
    	session('MBIS_USER',null);
    	$rv = $m->sendSMS(0,session('findPass.userPhone'),$msg,'getPhoneVerify',$phoneVerify);
      	if($rv['status']==1){
	    	$USER = '';
	    	$USER['phoneVerify'] = $phoneVerify;
	    	$USER['time'] = time();
	    	session('findPhone',$USER);
	    	return MBISReturn('短信发送成功!',1);
    	}
    	return $rv;
    }
    /**
     * 手机验证码检测
     * -1 错误，1正确
     */
    public function checkfindPhone($phoneVerify){
    	if(!session('findPhone.phoneVerify') || time()>floatval(session('findPhone.time'))+10*60){
    		return MBISReturn("校验码已失效，请重新发送！");
    		exit();
    	}
    	if (session('findPhone.phoneVerify') == $phoneVerify ) {
    		$fuserId = session('findPass.userId');
    		if(!empty($fuserId)){
    			$rs['status'] = 1;
    			$keyFactory = new \org\Base64();
    			$key = $keyFactory->encrypt("0_".session('findPass.userId')."_".time(),(int)session('findPass.loginSecret'),30*60);
    			$rs['url'] = url('Home/Users/forgetPasss',array('key'=>$key),true,true);
    			return $rs;
    		}
    		return MBISReturn('无效用户',-1);
    	}
    	return MBISReturn('校验码错误!',-1);
    }
    /**
     * 发送验证邮件/找回密码
     */
    public function getfindEmail(){
    	$base64 = new \org\Base64();
    	$key = $base64->encrypt("0_".session('findPass.userId')."_".time(),(int)session('findPass.loginSecret'),30*60);
    	$url = url('Home/Users/forgetPasss',array('key'=>$key),true,true);
    	$html="您好，会员 ".session('findPass.loginName')."：<br>
		您在".date('Y-m-d H:i:s')."发出了重置密码的请求,请点击以下链接进行密码重置:<br>
		<a href='".$url."'>".$url."</a><br>
		<br>如果您的邮箱不支持链接点击，请将以上链接地址拷贝到你的浏览器地址栏中。<br>
		该验证邮件有效期为30分钟，超时请重新发送邮件。<br>
		<br><br>*此邮件为系统自动发出的，请勿直接回复。";
    	$sendRs = MBISSendMail(session('findPass.userEmail'),'密码重置',$html);
    	if($sendRs['status']==1){
    		return MBISReturn("操作成功",1);
    	}else{
    		return MBISReturn($sendRs['msg'],-1);
    	}
    }
    
    /**
     * 加载登录小窗口
     */
    public function toLoginBox(){
    	return $this->fetch('box_login');
    }

    /**
    * 跳去修改支付密码页
    */
    public function editPayPass(){
        $m = new MUsers();
        //获取用户信息
        $userId = (int)session('MBIS_USER.userId');
        $data = $m->getById($userId);
        $this->assign('data',$data);
        return $this->fetch('users/security/user_pay_pass');
    }
    /**
    * 修改支付密码
    */
    public function payPassEdit(){
        $userId = (int)session('MBIS_USER.userId');
        $m = new MUsers();
        $rs = $m->editPayPass($userId);
        return $rs;
    }

    /**
     * 获取用户金额
     */
    public function getUserMoney(){
        $m = new MUsers();
        $rs = $m->getFieldsById((int)session('MBIS_USER.userId'),'userMoney,lockMoney,payPwd');
        $rs['isSetPayPwd'] = ($rs['payPwd']=='')?0:1;
        unset($rs['payPwd']);
        return MBISReturn('',1,$rs);
    }
    
    /**
     * 建谷发送短信
     */
    public function itsSendSms()
    {
        $m = new itsSms();
        $rs = $m->send();
        MBISApiReturn($rs);
    }
}

