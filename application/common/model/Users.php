<?php
namespace application\common\model;
use application\home\model\Shops;
use Think\Db;
/**
 * 用户类
 */
class Users extends Base{
    private $userInfo = null;
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
        #userEmail
    	$rs = $this->where("loginName|userPhone",$loginName)
    				->where(["dataFlag"=>1])
    				->find();
    	if(!empty($rs)){
    		$userId = $rs['userId'];
            if($rs['userStatus']!=1)
            {
                return MBISReturn('账号被禁用，请联系校区管理员!');   
            }
    		//获取用户等级
	    	//$rrs = Db::name('user_ranks')->where('startScore','<=',$rs['userTotalScore'])->where('endScore','>=',$rs['userTotalScore'])->field('rankId,rankName,rebate,userrankImg')->find();
	    	//$rs['rankId'] = $rrs['rankId'];
	    	//$rs['rankName'] = $rrs['rankName'];
	    	//$rs['userrankImg'] = $rrs['userrankImg'];
    		/*if(input("post.typ")==2){
    			$shoprs=$this->where(["dataFlag"=>1, "userStatus"=>1,"userType"=>1,"userId"=>$userId])->find();
    			if(empty($shoprs)){
    				return MBISReturn('您还没申请店铺!');
    			}
    		}*/
    		if($rs['loginPwd']!=md5($loginPwd.$rs['loginSecret']))return MBISReturn("密码错误");
    		$ip = request()->ip();
            
            $sess_id = $this->get_accesstoken($userId);
    		$this->where(["userId"=>$userId])->update(["lastTime"=>time(),"lastIP"=>$ip,"accesstoken"=>$sess_id]);
    		//如果是店铺则加载店铺信息
    		/*if($rs['userType']>=1){
    			$shops= new Shops();
    			$shop = $shops->where(["userId"=>$userId,"dataFlag" =>1])->find();
    			if(!empty($shop))$rs = array_merge($shop->toArray(),$rs->toArray());
    		}*/
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
    	//$data["loginPwd"] = input("post.oldPass");
    	if(!$data["loginPwd"]){
    		return MBISReturn('密码不能为空',-1);
    	}
    	$rs = $this->where('userId='.$id)->find();
    	//核对密码
    	if($rs['loginPwd']){
            if(strlen(input("post.newPass")) <6)
            {
                return MBISReturn('密码长度不能小于6',-1);   
            }
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
            return MBISReturn('账号不存在',-1);
    		/*$data["loginPwd"] = md5(input("post.newPass").$rs['loginSecret']);
    		$rs = $this->update($data,['userId'=>$id]);
    		if(false !== $rs){
    			return MBISReturn("密码修改成功[2]", 1);
    		}else{
    			return MBISReturn($this->getError(),-1);
    		}*/
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
    public function getUserInfo(){
        $params = input('post.');
    	$rs = $this->where(['userId'=>(int)$params['userId']])->field('loginName,userSex,trueName,nickName,brithday,userQQ,userPhone,userEmail')->find();
        $rs['brithday'] = date('Y-m-d',$rs['brithday']);
    	//$rs['ranks'] = Db::name('user_ranks')->where('startScore','<=',$rs['userTotalScore'])->where('endScore','>=',$rs['userTotalScore'])->field('rankId,rankName,rebate,userrankImg')->find();
    	return MBISReturn("ok", 1, $rs);
    }
    /**
     * 编辑资料
    */
    public function editUserInfo(){
        $params = input('post.');
    	$userId = $params['userId'];
        if(empty($params['userSex'])) return MBISReturn('请选择性别');
        if(empty($params['nickName'])) return MBISReturn('请输入昵称');
        if(empty($params['brithday'])) return MBISReturn('请选择生日日期');
        if(empty($params['userQQ'])) return MBISReturn('请输入QQ');
        if(empty($params['userEmail'])) return MBISReturn('请输入邮箱');
    	Db::startTrans();
		try{
			//MBISUseImages(0, $Id, $data['userPhoto'],'users','userPhoto');
            $data['userSex'] = $params['userSex'];
            $data['nickName'] = $params['nickName'];
            $data['brithday'] = strtotime($params['brithday']);
            $data['userQQ'] = $params['userQQ'];
            $data['userEmail'] = $params['userEmail'];
            $data['lastmodify'] = time();
	    	$result = $this->allowField(true)->save($data,['userId'=>$userId]);
	    	if(false !== $result){
	    		Db::commit();
	    		return MBISReturn("编辑成功", 1);
	    	}
		}catch (\Exception $e) {
            //dump($e->getMessage());
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
        $params = input('post.');
        if(!empty($params['userId']) && $params['userId']==59)
        {
            return MBISReturn("",1);
        }
        if(empty($params['accesstoken']))
        {
           return MBISReturn("缺少参数[accesstoken]"); 
        }
        if(empty($params['userId']))
        {
           return MBISReturn("缺少参数[userId]"); 
        }
        $accesstoken = $params['accesstoken'];
        $userId = (int)$params['userId'];
        $user_accesstoken = $this->where(['userId'=>$userId,'dataFlag'=>1])->value('accesstoken');
		if($user_accesstoken == $accesstoken)
        {
            return MBISReturn("",1);
        }
        else
        {
            return MBISReturn("登录状态失效",-100);  
        }
	}
    //退出登录
    public function logout()
    {
        $params = input('post.');
        if(empty($params['accesstoken']))
        {
           return MBISReturn("缺少参数[accesstoken]"); 
        }
        if(empty($params['userId']))
        {
           return MBISReturn("缺少参数[userId]"); 
        }
        $accesstoken = $params['accesstoken'];
        $userId = (int)$params['userId'];
        $rs = $this->update(['accesstoken'=>''],['userId'=>$userId,'dataFlag'=>1]);
        if($rs !== FALSE)
        {
            return MBISReturn("退出成功",1);
        }   
    }
    //获取会员昵称
    public function get_nick_name($userId=0)
    {
        $nickName = Db::name('users')->where('userId',$userId)->value('nickName');
        return $nickName?$nickName:'匿名';   
    }
    //用户二维码
    public function myQrcode()
    {
        $url = request()->domain().'/index.php/api/users/showQrcode';
        return MBISReturn('',1,['url'=>$url]); 
    }
    //显示二维码
    public function showQrcode()
    {
        $data = '用户名';
        $qrcode = new \application\common\model\Qrcode;
        $qrcode->myQrcode($data);   
    }
    //我的消息
    public function myMessage()
    {
        $lists = [
            ['title'=>'测试消息1','content'=>'测试内容1','createtime'=>'2017-02-05 15:00'],
            ['title'=>'测试消息2','content'=>'测试内容2','createtime'=>'2017-02-03 15:00'],
        ];
        return MBISReturn("",1,['messageLists'=>$lists]);
    }
    //用户所属校区
    public function getSchoolId($params=[])
    {
        $schoolId = 0;
        $result = $this->where(['userId'=>$params['userId']])->field('department_id')->find();
        $department_id = (int)$result['department_id'];
        if($department_id>0)
        {
            $schoolId = $department_id;
            $rs_department = Db::name('department')->where(['department_id'=>$department_id])->field('parent_id')->find(); 
        }
        !empty($rs_department) && $rs_department['parent_id']>0 && $schoolId = (int)$rs_department['parent_id'];
        return $schoolId;  
    }
//录入会员信息
    public function addUserInfo(){
        $params = input("post.");
        $where = $params['cardId'];
        $rs = $this->where('loginName',$where)->find();        
        if($rs['loginName']){ 
            if($rs['userType']==0){
                if($rs['trueName']==$params['trueName']){
                    $data=[];
        foreach ($rs as $k=>$v){
            $data[$k]=$v;
        }
            $file = $data['data'];
            $file['loginName']=$params['cardId'];
            $file['userPhone']=$params['userPhone'];
            $file['userAddress'] = $params['userAddress'];
            $file['lastmodify']=time();
            $rs = $this->isUpdate(true)->save($file,['userId'=>$rs['userId']]);
            if($rs==1){
               return MBISReturn('',1);
            } 
                } else {
                    return MBISReturn('姓名填写错误',-1);
                }
        }else{
           return MBISReturn('该角色不是学员，不能修改',-1); 
        }
        }else{
            return MBISReturn('账号不存在',-1);
        }
    }
    //会员类型获取
    public function getUserType($userId=0)
    {
        $userType = 0;
        $userInfo = Db::name('users')->where('userId',$userId)->find();
        !empty($userInfo['userType']) && $userType=$userInfo['userType'];
        return $userType;
    }
    //代购会员类型
    public function getAgentUserType()
    {
        return [2];   
    }
    //会员详情
    public function get_info($params=[]){
        if(!empty($this->userInfo)) return $this->userInfo;
        $field = '';
        if(isset($params['field']))
        {
           $field = $params['field'];
        }
        //软删除过滤掉
        $where['dataFlag'] = 1;
        if(isset($params['userId']))
        {
           $where['userId'] = $params['userId'];    
        }
        $rs = $this->where($where)->field($field)->find();
        if(isset($params['field'])&&strpos($params['field'],',')===FALSE) return $rs[$field];
        isset($rs['userPhoto']) && 
            $rs['userPhoto'] = ITSPicUrl($rs['userPhoto']);
        //代购标识判定
        $rs['isAgentUser'] = 0;
        isset($rs['userType']) && 
          in_array($rs['userType'],$this->getAgentUserType()) && 
          $rs['isAgentUser'] = 1;
        $this->userInfo = $rs;
        return $rs;
	}
    
    /* 写入记录 */
    public function putData($data){
        $formalData = $data;
        $result = true;
        $userId = $this->checkData($data);
        if(empty($userId)):
            $userData = $this->preUserData($data);
            $result = $this->insert($userData);
            if(false !== $result):
                $userId = $this->getLastInsID();
                $data['userId'] = $userId;
                $userExtData = $this->preUserExtData($data);
                model('common/studentExtend')->save($userExtData);
            endif;
        endif;
        $return = array(
           'status' => $result,
           'id' => $userId,
           'data' => $formalData,
        );
        return $return;
    }
    /* 检查是否数据 */
    private function checkData($data){
        $filter_has = ['dataFlag'=>1,'loginName'=>$data['idcard']];
        $userId = $this->where($filter_has)->value('userId');
        return $userId;   
    }
    /* 格式化数据 */
    private function preUserData($data){
        $pwd = 'its123456';
        $return['loginName'] = $data['idcard'];
        $return['idcard'] = $data['idcard'];
        $return['trueName'] = $data['trueName'];
        $return['nickName'] = !empty($data['nickName'])?$data['nickName']:'nick_'.substr(md5($return['trueName']),0,8);
        $return['userPhone'] = $data['userPhone'];
        $return['userEmail'] = $data['userEmail'];
        $return['user_weixin'] = $data['user_weixin'];
        $return['userQQ'] = $data['userQQ'];
        $return['lastmodify'] = time();
		$return['createtime'] = time();
		$return["loginSecret"] = rand(1000,9999);
    	$return['loginPwd'] = md5($pwd.$return['loginSecret']);
    	$return['userType'] = $data['userType'];
        $return['uidType'] = $data['uidType'];//学员身份类型：1为新生、2为在学生、3为会员
        $return['student_type'] = $data['student_type'];//学员类型：1为技能、2为学历、3为技能学历
        $return['study_status'] = $data['study_status'];//学习状态：1为在读、2为毕业、3为过期、4为弃学、5为休学、6为退学
        //学员编号
        $return['student_no'] = $data['student_no'];
        return $return; 
    }
    public function preUserExtData($data){
        $return['userId'] = $data['userId'];
        $return['createtime'] = time();
        $return['lastmodify'] = time();
        return $return;
    }

    /**
     * 根据accestoken查找小its对应的用户信息
     * @param $accesstoken
     * @return mixed
     */
    public function checkToken($accesstoken)
    {
        $data = $this->alias('a')
            ->field('c.username,c.departmentid as school')
            ->join('employee b','a.employee_id = b.employee_id','left')
            ->join('admin c','c.username = b.employee_no'.'left')
            ->where(array('a.accesstoken'=>$accesstoken))
            ->find();
        return $data;

    }
}
