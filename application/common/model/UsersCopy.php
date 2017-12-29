<?php
namespace application\common\model;
use application\home\model\Shops;
use Think\Db;
/**
 * 用户类
 */
class UsersCopy extends Base{
    /**
     * 用户登录验证
     */
    public function checkLogin(){
    	$loginName = input("post.loginName");
    	$loginPwd = input("post.loginPwd");
    	//$code = input("post.verifyCode");
    	$rememberPwd = input("post.rememberPwd",1);
    	/*if(!MBISVerifyCheck($code) && strpos(MBISConf("CONF.captcha_model"),"4")>=0){
    		return MBISReturn('验证码错误!');
    	}*/
    	$rs = $this->where("loginName|userEmail|userPhone",$loginName)
    				->where(["dataFlag"=>1, "userStatus"=>1])
    				->find();
    	if(!empty($rs)){
    		$userId = $rs['userId'];
    		//获取用户等级
	    	//$rrs = Db::name('user_ranks')->where('startScore','<=',$rs['userTotalScore'])->where('endScore','>=',$rs['userTotalScore'])->field('rankId,rankName,rebate,userrankImg')->find();
	    	//$rs['rankId'] = $rrs['rankId'];
	    	//$rs['rankName'] = $rrs['rankName'];
	    	//$rs['userrankImg'] = $rrs['userrankImg'];
    		if(input("post.typ")==2){
    			$shoprs=$this->where(["dataFlag"=>1, "userStatus"=>1,"userType"=>1,"userId"=>$userId])->find();
    			if(empty($shoprs)){
    				return MBISReturn('您还没申请店铺!');
    			}
    		}
    		if($rs['loginPwd']!=md5($loginPwd.$rs['loginSecret']))return MBISReturn("密码错误");
    		$ip = request()->ip();
            
            $sess_id = $this->get_accesstoken($userId);
    		$this->where(["userId"=>$userId])->update(["lastTime"=>time(),"lastIP"=>$ip,"accesstoken"=>$sess_id]);
    		//如果是店铺则加载店铺信息
    		if($rs['userType']>=1){
    			$shops= new Shops();
    			$shop = $shops->where(["userId"=>$userId,"dataFlag" =>1])->find();
    			if(!empty($shop))$rs = array_merge($shop->toArray(),$rs->toArray());
    		}
    		//记录登录日志
    		$data = array();
    		$data["userId"] = $userId;
    		$data["loginTime"] = date('Y-m-d H:i:s');
    		$data["loginIp"] = $ip;
    		Db::name('log_user_logins')->insert($data);
    
    		$rd = $rs;
    		//记住密码
    		cookie("loginName", $loginName, time()+3600*24*90);
    		if($rememberPwd == "on"){
    			$datakey = md5($rs['loginName'])."_".md5($rs['loginPwd']);
    			$key = $rs['loginSecret'];
    			//加密
    			$base64 = new \org\Base64();
    			$loginKey = $base64->encrypt($datakey, $key);
    			cookie("loginPwd", $loginKey, time()+3600*24*90);
    		}else{
    			cookie("loginPwd", null);
    		}
            session('var_session_id',$sess_id);
            $rs['accesstoken'] = $sess_id;
            $rs['userPhoto'] = request()->domain().'/'.$rs['userPhoto'];
            unset($rs['loginSecret'],$rs['loginPwd']);
    		session('MBIS_USER',$rs);
    		return MBISReturn("","1",$rs);
    	}
    	return MBISReturn("用户不存在");
    }
    
    /**
     * 会员注册
     */
    public function regist(){
    	
    	$data = array();
    	$data['loginName'] = input("post.loginName");
    	$data['loginPwd'] = input("post.loginPwd");
    	$data['reUserPwd'] = input("post.reUserPwd");
    	$loginName = $data['loginName'];
    	//检测账号是否存在
    	$crs = MBISCheckLoginKey($loginName);
    	if($crs['status']!=1)return $crs;
    	if($data['loginPwd']!=$data['reUserPwd']){
    		return MBISReturn("两次输入密码不一致!");
    	}
    	foreach ($data as $v){
    		if($v ==''){
    			return MBISReturn("注册信息不完整!");
    		}
    	}
    	$nameType = (int)input("post.nameType");
    	$mobileCode = input("post.mobileCode");
    	$code = input("post.verifyCode");
    	/*if($nameType!=3 && !MBISVerifyCheck($code)){
    		return MBISReturn("验证码错误!");
    	}*/
    	if($nameType==3 && MBISConf("CONF.phoneVerfy")==1){//手机号码
    		$data['userPhone'] = $loginName;
    		$verify = session('VerifyCode_userPhone');
    		$startTime = (int)session('VerifyCode_userPhone_Time');
    		if((time()-$startTime)>120){
    			return MBISReturn("验证码已超过有效期!");
    		}
    		if($mobileCode=="" || $verify != $mobileCode){
    			return MBISReturn("验证码错误!");
    		}
    		$loginName = MBISRandomLoginName($loginName);
    	}else if($nameType==1){//邮箱注册
    		$data['userEmail'] = $loginName;
    		$unames = explode("@",$loginName);
    		$loginName = MBISRandomLoginName($unames[0]);
    		
    	}
    	if($loginName=='')return MBISReturn("注册失败!");//分派不了登录名
    	$data['loginName'] = $loginName;
    	unset($data['reUserPwd']);
    	unset($data['protocol']);
    	//检测账号，邮箱，手机是否存在
    	$data["loginSecret"] = rand(1000,9999);
    	$data['loginPwd'] = md5($data['loginPwd'].$data['loginSecret']);
    	$data['userType'] = 0;
    	$data['nickName'] = 'nick_'.substr(md5($loginName),12,8);
    	$data['userQQ'] = "";
    	//$data['userScore'] = 0;
    	$data['createtime'] = time();
    	$data['dataFlag'] = 1;
    	Db::startTrans();
        try{
	    	$userId = $this->data($data)->save();
	    	if(false !== $userId){
	    		$data = array();
	    		$ip = request()->ip();
	    		//$data['lastTime'] = date('Y-m-d H:i:s');
                $data['lastTime'] = time();
	    		$data['lastIP'] = $ip;
	    		$userId = $this->userId;
                $sess_id = $this->get_accesstoken($userId);
                $data['accesstoken'] = $sess_id;
                $data['lastmodify'] = time();
	    		$this->where(["userId"=>$userId])->update($data);
	    		//记录登录日志
	    		$data = array();
	    		$data["userId"] = $userId;
	    		$data["loginTime"] = date('Y-m-d H:i:s');
	    		$data["loginIp"] = $ip;
	    		Db::name('log_user_logins')->insert($data);
	    		$user = $this->get($userId);
                $user['accesstoken'] = $sess_id;
                $user['userPhoto'] = request()->domain().'/'.$user['userPhoto'];
                unset($user['loginSecret'],$user['loginPwd']);
	    		session('MBIS_USER',$user);
	    		Db::commit();
	    		return MBISReturn("",1,$user);
	    	}
        }catch (\Exception $e) {
        	Db::rollback();
        }
    	return MBISReturn("注册失败!");
    }
    
    /**
     * 查询用户手机是否存在
     * 
     */
    public function checkUserPhone($userPhone,$userId = 0){
    	$dbo = $this->where(["dataFlag"=>1, "userPhone"=>$userPhone]);
    	if($userId>0){
    		$dbo->where("userId","<>",$userId);
    	}
    	$rs = $dbo->count();
    	if($rs>0){
    		return MBISReturn("手机号已存在!");
    	}else{
    		return MBISReturn("",1);
    	}
    }

    /**
     * 修改用户密码
     */
    public function editPass($id){
    	$data = array();
    	$data["loginPwd"] = input("post.newPass");
    	if(!$data["loginPwd"]){
    		return MBISReturn('密码不能为空',-1);
    	}
    	$rs = $this->where('userId='.$id)->find();
    	//核对密码
    	if($rs['loginPwd']){
    		if($rs['loginPwd']==md5(input("post.oldPass").$rs['loginSecret'])){
    			$data["loginPwd"] = md5(input("post.newPass").$rs['loginSecret']);
    			$rs = $this->update($data,['userId'=>$id]);
    			if(false !== $rs){
    				return MBISReturn("密码修改成功", 1);
    			}else{
    				return MBISReturn($this->getError(),-1);
    			}
    		}else{
    			return MBISReturn('原始密码错误',-1);
    		}
    	}else{
    		$data["loginPwd"] = md5(input("post.newPass").$rs['loginSecret']);
    		$rs = $this->update($data,['userId'=>$id]);
    		if(false !== $rs){
    			return MBISReturn("密码修改成功", 1);
    		}else{
    			return MBISReturn($this->getError(),-1);
    		}
    	}
    }
    /**
     * 修改用户支付密码
     */
    public function editPayPass($id){
        $data = array();
        $data["payPwd"] = input("post.newPass");
        if(!$data["payPwd"]){
            return MBISReturn('支付密码不能为空',-1);
        }
        $rs = $this->where('userId='.$id)->find();
        //核对密码
        if($rs['payPwd']){
            if($rs['payPwd']==md5(input("post.oldPass").$rs['loginSecret'])){
                $data["payPwd"] = md5($data["payPwd"].$rs['loginSecret']);
                $rs = $this->update($data,['userId'=>$id]);
                if(false !== $rs){
                    return MBISReturn("支付密码修改成功", 1);
                }else{
                    return MBISReturn("支付密码修改失败",-1);
                }
            }else{
                return MBISReturn('原始支付密码错误',-1);
            }
        }else{
            $data["payPwd"] = md5($data["payPwd"].$rs['loginSecret']);
            $rs = $this->update($data,['userId'=>$id]);
            if(false !== $rs){
                return MBISReturn("支付密码修改成功", 1);
            }else{
                return MBISReturn("支付密码修改失败",-1);
            }
        }
    }
   /**
    *  获取用户信息
    */
    public function getById($id){
    	$rs = $this->get(['userId'=>(int)$id]);
    	$rs['ranks'] = Db::name('user_ranks')->where('startScore','<=',$rs['userTotalScore'])->where('endScore','>=',$rs['userTotalScore'])->field('rankId,rankName,rebate,userrankImg')->find();
    	return $rs;
    }
    /**
     * 编辑资料
    */
    public function edit(){
    	$Id = (int)input('post.userId/d');
    	$data = input('post.');
    	MBISAllow($data,'brithday,trueName,userName,userId,userPhoto,userQQ,userSex');
    	Db::startTrans();
		try{
			MBISUseImages(0, $Id, $data['userPhoto'],'users','userPhoto');
	    	$result = $this->allowField(true)->save($data,['userId'=>$Id]);
	    	if(false !== $result){
	    		Db::commit();
	    		return MBISReturn("编辑成功", 1);
	    	}
		}catch (\Exception $e) {
            Db::rollback();
            return MBISReturn('编辑失败',-1);
        }	
    }
    /**
    * 绑定邮箱
     */
    public function editEmail($userId,$userEmail){
    	$data = array();
    	$data["userEmail"] = $userEmail;
		$rs = $this->update($data,['userId'=>$userId]);
		if(false !== $rs){
			return MBISReturn("",1);
		}else{
			return MBISReturn("",-1);
		}
    }
    /**
     * 绑定手机
     */
    public function editPhone($userId,$userPhone){
    	$data = array();
    	$data["userPhone"] = $userPhone;
    	$rs = $this->update($data,['userId'=>$userId]);
    	if(false !== $rs){
    		return MBISReturn("绑定成功", 1);
    	}else{
    		return MBISReturn($this->getError(),-1);
    	}
    }
    /**
     * 查询并加载用户资料
     */
    public function checkAndGetLoginInfo($key){
    	if($key=='')return array();
    	$rs = $this->where(["loginName|userEmail|userPhone"=>['=',$key],'dataFlag'=>1])->find();
    	return $rs;
    }
    /**
     * 重置用户密码
     */
    public function resetPass(){
    	if(time()>floatval(session('REST_Time'))+30*60){
    		return MBISReturn("连接已失效！", -1);
    	}
    	$reset_userId = (int)session('REST_userId');
    	if($reset_userId==0){
    		return MBISReturn("无效的用户！", -1);
    	}
    	$user = $this->where(["dataFlag"=>1,"userStatus"=>1,"userId"=>$reset_userId])->find();
    	if(empty($user)){
    		return MBISReturn("无效的用户！", -1);
    	}
    	$loginPwd = input("post.loginPwd");
    	if(trim($loginPwd)==''){
    		return MBISReturn("无效的密码！", -1);
    	}
    	$data['loginPwd'] = md5($loginPwd.$user["loginSecret"]);
    	$rc = $this->update($data,['userId'=>$reset_userId]);
    	if(false !== $rc){
    		return MBISReturn("修改成功", 1);
    	}
    	session('REST_userId',null);
    	session('REST_Time',null);
    	session('REST_success',null);
    	session('findPass',null);
    	return $rs;
    }
    
    /**
     * 获取用户可用积分
     */
    public function getFieldsById($userId,$fields){
    	return $this->where(['userId'=>$userId,'dataFlag'=>1])->field($fields)->find();
    }
    private function get_accesstoken($userId=0)
    {
        $member_ident = uniqid();
        $sess_id = md5 ( $member_ident . 'api' . $userId );
        return $sess_id; 
    }
    public function check_accesstoken($accesstoken='',$userId=''){
        if(!isset($_POST['accesstoken']) || !isset($_POST['userId']))
        {
           return MBISReturn("参数有误！"); 
        }
        $accesstoken = $_POST['accesstoken'];
        $userId = (int)$_POST['userId'];
        $user_accesstoken = $this->where(['userId'=>$userId,'dataFlag'=>1])->value('accesstoken');
		if($user_accesstoken == $accesstoken)
        {
            return MBISReturn("",1);
        }
        else
        {
            return MBISReturn("登录状态失效！");  
        }
        
	}
    
}
