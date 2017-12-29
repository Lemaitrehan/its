<?php
namespace application\admin\model;
use think\Db;
/**
 * 会员业务处理
 */
class Users extends Base{
	/**
	 * 分页
	 */
	public function pageQueryU(){
		/******************** 查询 ************************/
		//dd($_GET);
		$where = [];
		$type_id = input('get.type_id');
		//dump($type_id);die;
		if($type_id == '1'){  //学历
			$where['u.student_type'] = ['in',[2,3]];
		}elseif($type_id == '2'){ //技能
			$where['u.student_type'] = ['in',[1,3]];
		}
		$study_status = input('get.study_status');
		if(!empty($study_status))
			$where['u.study_status'] = ['=',"$study_status"];
		
		$key = input('get.key');
		$key_value = trim(input('get.key_value'));
		if(($key !== '') && ($key_value !== '')){
			if($key =='trueName'){
				$where['u.trueName'] = ['like',"%$key_value%"];
			}elseif($key == 'student_no'){
				$where['u.student_no'] = ['like',"%$key_value%"];
			}elseif($key == 'userPhone'){
				$where['u.userPhone'] = ['like',"%$key_value%"];
			}elseif($key == 'idcard'){
				$where['u.idcard'] = ['like',"%$key_value%"];
			}
		}
		//$start = strtotime(input('get.start'));
		//$end = strtotime(input('get.end'));
		$where['u.dataFlag'] = 1;   
		$where['u.userType'] = 0;  //学员类型
		//$lName = input('get.loginName1');
		//$phone = input('get.loginPhone');
		//$trueName = input('get.trueName');
		$schoolId = input('get.school_id');
		$majorId = input('get.major_id');
		$courseId = input('get.course_id');
		/*
		if(!empty($start) && !empty($end)){
			$where['u.createtime'] = ['between',["$start","$end"]];
		}
		if(!empty($lName))
			$where['u.loginName'] = ['like',"%$lName%"];
		if(!empty($phone))
			$where['u.userPhone'] = ['like',"%$phone%"];
		if(!empty($trueName))
			$where['u.trueName'] = ['like',"%$trueName%"];
		*/
		

		if($type_id == '1'){
			$join = array( array('student_edu e','u.userId=e.userId','left') );
			if(!empty($schoolId))$where['e.school_id'] = ['=',"$schoolId"];
			if(!empty($majorId))$where['e.major_id'] = ['=',"$majorId"];
			if(!empty($courseId))$where['e.course_id'] = ['=',"$courseId"];
		}else{
			$join = array( array('student_skill k','u.userId=k.userId','left') );
			if(!empty($schoolId))$where['k.school_id'] = ['=',"$schoolId"];
			if(!empty($majorId))$where['k.major_id'] = ['=',"$majorId"];
			if(!empty($courseId))$where['k.course_id'] = ['=',"$courseId"];
		}
		/*
		$rss = $this->alias('u')
				->join('student_extend x','u.userId=x.userId','left')  //学员扩展信息
				->join($join)
				->where($where)
				//->Distinct(true) 
				->field('u.userId,u.idcard,u.student_no,u.trueName,u.study_status,u.userPhone,u.userQQ,u.userEmail,x.urgency_contact,x.urgency_contact_mobile')
				->order('u.lastmodify desc')
				->select();
		$count = count($rss);
		*/
		$rs = $this->alias('u')
				->join('student_extend x','u.userId=x.userId','left')  //学员扩展信息
				->join($join)
				->where($where)
				//->Distinct(true) 
				->field('u.userId,u.idcard,u.student_no,u.trueName,u.study_status,u.userPhone,u.userQQ,u.userEmail,x.urgency_contact,x.urgency_contact_mobile')
				->order('u.lastmodify desc')
				->paginate(input('pagesize/d'))->toArray();
		//getLastSql();
		//dump($rs);die;
        if(count($rs['Rows'])>0){
			foreach ($rs['Rows'] as $key => $v){
                $rs['Rows'][$key]['study_status'] = $this->get_study_status($v['study_status']);
			}
		}
		return $rs;
	}
	public function pageQueryT(){
		/******************** 查询 ************************/
		$where = [];
		$where['dataFlag'] = 1;
		$where['userType'] = 1;  //老师类型
		$lName = input('get.loginName1');
		$phone = input('get.loginPhone');
		$email = input('get.loginEmail');
		$uType = input('get.userType');
		$uStatus = input('get.userStatus1');
		if(!empty($lName))
			$where['loginName'] = ['like',"%$lName%"];
		if(!empty($phone))
			$where['userPhone'] = ['like',"%$phone%"];
		if(!empty($email))
			$where['userEmail'] = ['like',"%$email%"];
		/*
		if(is_numeric($uType))
			$where['userType'] = ['=',"$uType"];
		*/
		if(is_numeric($uStatus))
			$where['userStatus'] = ['=',"$uStatus"];

		/********************* 取数据 *************************/
		$rs = $this->where($where)
					->order('userId desc')
					->paginate(input('pagesize/d'))->toArray();
        if(count($rs['Rows'])>0){
			foreach ($rs['Rows'] as $key => $v){
                $rs['Rows'][$key]['createtime'] = $v['createtime']?date('Y-m-d H:i',$v['createtime']):'无';
			}
		}
		return $rs;
	}

	public function pageQueryZ(){
		/******************** 查询 ************************/
		$where = [];
		$where['dataFlag'] = 1;
		$where['userType'] = 2;  //咨询师类型
		$start = strtotime(input('get.start'));
		$end = strtotime(input('get.end'));
		$department_id = input('get.department_id');
		$employee_type_id = input('get.employee_type_id');
		$lName = input('get.loginName1');
		$phone = input('get.loginPhone');
		$email = input('get.loginEmail');
		$uType = input('get.userType');
		$uStatus = input('get.userStatus1');
		if(!empty($start) && !empty($end)){
			$where['u.createtime'] = ['between',["$start","$end"]];
		}
		if(!empty($lName))
			$where['u.loginName'] = ['like',"%$lName%"];
		if(!empty($phone))
			$where['u.userPhone'] = ['like',"%$phone%"];
		if(!empty($email))
			$where['u.userEmail'] = ['like',"%$email%"];
		if(!empty($department_id))
			$where['u.department_id'] = ['=',"$department_id"];
		if(!empty($employee_type_id))
			$where['e.employee_type_id'] = ['=',"$employee_type_id"];
		/*
		if(is_numeric($uType))
			$where['userType'] = ['=',"$uType"];
		*/
		if(is_numeric($uStatus))
			$where['u.userStatus'] = ['=',"$uStatus"];

		/********************* 取数据 *************************/
		$join = [];
		$join = [
			//['department d','u.department_id=d.department_id','left'],
			['employee_type e','u.employee_type_id=e.employee_type_id','left']
		];
		$field = 'u.userId,u.loginName,u.department_id,u.employee_type_id,u.trueName,u.userPhone,u.userEmail,u.createtime,u.userStatus,e.name as employee_type_name';
		$rs = Db::name('users')
					->alias('u')
					->join($join)
		            ->where($where)
		            ->field($field)
					->order('userId desc')
					->paginate(input('pagesize/d'))
					->toArray();
        if(count($rs['Rows'])>0){
			foreach ($rs['Rows'] as $key => $v){
                $rs['Rows'][$key]['createtime'] = $v['createtime']?date('Y-m-d H:i',$v['createtime']):'无';
                $rs['Rows'][$key]['department_name'] = $this->get_department_name($v['department_id']);
			}
		}
		return $rs;
	}

	/**
	 * 处理学员学历报名信息分页
	 */
	public function getedu($id){
		$res = Db::name('student_edu')->where('edu_id',$id)->find();
		if($id > 0){
			$res = Db::name('student_edu')->where('edu_id',$id)->find();
			$res['entry_time'] = $this->time_date($res['entry_time']);
		}
		return $res;
	}
	public function pageQueryE(){
		$userId = (int)input('userId');
		$rs = Db::name('student_edu')->alias('d')->join('users u','u.userId=d.userId')->where('d.userId',$userId)
				->order('entry_time desc')
				->paginate(input('pagesize/d'))->toArray();
        if(count($rs['Rows'])>0){
			foreach ($rs['Rows'] as $key => $v){
                $rs['Rows'][$key]['school_id'] = $this->get_school_name($v['school_id']);
                $rs['Rows'][$key]['major_id'] = $this->get_major_name($v['major_id']);
                $rs['Rows'][$key]['course_id'] = $this->get_course_name($v['course_id']);
                $rs['Rows'][$key]['grade_id'] = $this->get_grade_name($v['grade_id']);
			}
		}
		return $rs;
	}
	public function addedu(){  //新增学历报名信息
		$data = input('post.');
		$data['entry_time'] = strtotime($data['entry_time']);
    	MBISUnset($data,'edu_id');   	
    	Db::startTrans();
		try{
			$result = model('studentEdu')->save($data);
	        if(false !== $result){
	        	Db::commit();
	        	return MBISReturn("新增成功", 1);
	        }
		}catch (\Exception $e) {
            Db::rollback();
            return MBISReturn('新增失败',-1);
        }	
	}
	public function editedu(){  //编辑学历报名信息
		$id = (int)input('post.edu_id');
		$data = input('post.');
        $data['entry_time'] = strtotime($data['entry_time']);
		MBISUnset($data,'edu_id');
		MBISUnset($data,'userId');
		Db::startTrans();
		try{
		    $result = model('studentEdu')->save($data,['edu_id'=>$id]);
	        if(false !== $result){
	        	Db::commit();
	        	return MBISReturn("编辑成功", 1);
	        }
	    }catch (\Exception $e) {
            Db::rollback();
        }
        return MBISReturn('编辑失败',-1);  
	}
	public function deledu(){
	    $id = input('post.id/d');
	    Db::startTrans();
		try{
		    $result = model('studentEdu')->where(['edu_id'=>$id])->delete();
	        if(false !== $result){
	        	Db::commit();
	        	return MBISReturn("删除成功", 1);
	        }
		}catch (\Exception $e) {
            Db::rollback();
            return MBISReturn('删除失败',-1);
        }
	}
	/**
	 * 处理学员技能报名信息分页
	 */
	public function getskill($id){
		$res = Db::name('student_skill')->where('skill_id',$id)->find();
		if($id > 0){
			$res = Db::name('student_skill')->where('skill_id',$id)->find();
			$res['entry_time'] = $this->time_date($res['entry_time']);
			$res['access_time'] = $this->time_date($res['access_time']);
		}
		return $res;
	}
	public function pageQueryS(){
		$userId = (int)input('userId');
		$rs = Db::name('student_skill')->alias('s')->join('users u','u.userId=s.userId')->where('s.userId',$userId)
			->order('entry_time desc')
			->paginate(input('pagesize/d'))->toArray();
        if(count($rs['Rows'])>0){
			foreach ($rs['Rows'] as $key => $v){
                $rs['Rows'][$key]['school_id'] = $this->get_school_name($v['school_id']);
                $rs['Rows'][$key]['major_id'] = $this->get_major_name($v['major_id']);
                $rs['Rows'][$key]['course_id'] = $this->get_course_name($v['course_id']);
			}
		}
		return $rs;
	}
	public function addskill(){  //新增学历报名信息
		$data = input('post.');
		$data['entry_time'] = strtotime($data['entry_time']);
		$data['access_time'] = strtotime($data['access_time']);
    	MBISUnset($data,'skill_id');   	
    	Db::startTrans();
		try{
			$result = model('studentSkill')->save($data);
	        if(false !== $result){
	        	Db::commit();
	        	return MBISReturn("新增成功", 1);
	        }
		}catch (\Exception $e) {
            Db::rollback();
            return MBISReturn('新增失败',-1);
        }	
	}
	public function editskill(){  //编辑学历报名信息
		$id = (int)input('post.skill_id');
		$data = input('post.');
        $data['entry_time'] = strtotime($data['entry_time']);
        $data['access_time'] = strtotime($data['access_time']);
		MBISUnset($data,'skill_id');
		MBISUnset($data,'userId');
		Db::startTrans();
		try{
		    $result = model('studentSkill')->save($data,['skill_id'=>$id]);
	        if(false !== $result){
	        	Db::commit();
	        	return MBISReturn("编辑成功", 1);
	        }
	    }catch (\Exception $e) {
            Db::rollback();
        }
        return MBISReturn('编辑失败',-1);  
	}
	public function delskill(){
	    $id = input('post.id/d');
	    Db::startTrans();
		try{
		    $result = model('studentSkill')->where(['skill_id'=>$id])->delete();
	        if(false !== $result){
	        	Db::commit();
	        	return MBISReturn("删除成功", 1);
	        }
		}catch (\Exception $e) {
            Db::rollback();
            return MBISReturn('删除失败',-1);
        }
	}
	public function getById($id){   //获取学员数据
        $rs = $this->get(['userId'=>$id]);
        if($id > 0)
        {
            $basic = $rs->toArray();
            $basic['brithday'] = $this->time_date($basic['brithday']);
            $studentExtend = model('studentExtend')->getById($id);
            if(empty($studentExtend)){
            	$extend = [];
            }else{
            	$extend = model('studentExtend')->getById($id)->toArray();
            }
            /*
            $studentEdu = model('studentEdu')->getById($id);
            if(empty($studentEdu)){
            	$edu = [];
            }else{
            	$edu = $this->change_key(model('studentEdu')->getById($id)->toArray(),'edu_');
            }
            $studentSkill = model('studentSkill')->getById($id);
            if(empty($studentSkill)){
            	$skill = [];
            }else{
            	$skill = $this->change_key(model('studentSkill')->getById($id)->toArray(),'skill_');
            }
            $rs = array_merge($basic,$extend,$edu,$skill);
			*/
			$rs['basic'] = [];
			$rs['extend'] = [];
			if($basic && $extend){
				$rs['basic'] = $basic;
				$rs['extend'] = $extend;
			}
        }
		return $rs;
	}
	public function getInfo($id){   //获取老师数据
		$rs = $this->get(['userId'=>$id]);
		if($id > 0){
			$basic = $rs->toArray();
			$tcExtend = model('tcExtend')->getById($id);
			if(empty($tcExtend)){
				$tc_extend = [];
			}else{
				$tc_extend = model('tcExtend')->getById($id)->toArray();
			}
			$rs = array_merge($basic,$tc_extend);	
		}
		return $rs;
	}
	public function get_zxs($id){//获取咨询师数据
		if($id > 0){
			$rs = $this->get(['userId'=>$id]);
			return $rs;
		}
	}
    public function change_key($data=array(),$prefix=''){
        $tmp_data = [];
        foreach($data as $k=>$v)
        {
            $tmp_data[$prefix.$k] = $v;   
        }
        return $tmp_data;
    }
   	/**
   	 * 获取学员相关数据信息
   	 */
   	public function getBasicExtendInfo($userId){  //学员基础&扩展信息
   		$info = Db::name('users')
   					->alias('u')
   					->join('student_extend x','u.userId=x.userId','LEFT')
   					->where('u.userId',$userId)
   					->field('u.*,x.study_type,x.student_pay_type,x.province,x.city,x.address,x.company,x.job_content,x.mobile2,x.social_no,x.urgency_contact,x.urgency_contact_mobile,x.customer_source,x.industry,x.salary,x.remark')
   					->find();
   		if($info){
   			$info['userSex'] = $this->getSex($info['userSex']);
   			$info['uidType'] = $this->get_uidType($info['uidType']);
   			$info['student_type'] = $this->get_student_type($info['student_type']);
   			$info['study_status'] = $this->get_study_status($info['study_status']);
   			$info['student_pay_type'] = $this->get_student_pay_type($info['student_pay_type']);
   			$info['study_type'] = $this->get_study_type($info['study_type']);
   			$info['rankId'] = $this->getUserRank($info['rankId']);
   			$info['userStatus'] = $this->getUserStatus($info['userStatus']);
   			$info['lastTime'] = ($info['lastTime'] !== 0) ? $this->time_date($info['lastTime']) : '尚未登录';
   		}
   		return $info;
   	}
   	public function getBasic($userId){ //学员基础信息
   		$basicInfo = Db::name('users')->where('userId',$userId)->select();
   		foreach($basicInfo as &$v){
   			$v['userSex'] = $this->getSex($v['userSex']);
   			$v['brithday'] = $this->time_date($v['brithday']);
   			$v['userStatus'] = $this->getUserStatus($v['userStatus']);
   			$v['uidType'] = $this->get_uidType($v['uidType']);
   			$v['student_type'] = $this->get_student_type($v['student_type']);
   			$v['study_status'] = $this->get_study_status($v['study_status']);
   			$v['lastTime'] = $this->time_date($v['lastTime']);
   			$v['createtime'] = $this->time_date($v['createtime']);
   			$v['lastmodify'] = $this->time_date($v['lastmodify']);
   		}
   		//dump($basicInfo);die;
   		return $basicInfo;
   	}
   	public function getExtend($userId){ //学员扩展信息
   		$extendInfo = Db::name('studentExtend')->where('userId',$userId)->select();
   		foreach($extendInfo as &$v){
   			$v['student_type'] = $this->get_student_type($v['student_type']);
   			$v['study_status'] = $this->get_study_status($v['study_status']);
   			$v['study_type'] = $this->get_study_type($v['study_type']);
   			$v['student_pay_type'] = $this->get_student_pay_type($v['student_pay_type']);
   			$v['createtime'] = $this->time_date($v['createtime']);
   			$v['lastmodify'] = $this->time_date($v['lastmodify']);
   		}
   		//dump($extendInfo);die;
   		return $extendInfo;
   	}
   	public function getEduInfo($userId){ //学历报名信息
   		$eduInfo = Db::name('studentEdu')
   		                    ->where('userId',$userId)
   		                    ->select();
   		foreach($eduInfo as &$v){
   			$v['type'] = '学历类';
   			$v['school_id'] = $this->get_school_name($v['school_id']);
   			$v['major_id'] = $this->get_major_name($v['major_id']);
   			$v['course_id'] = $this->get_course_name($v['course_id']);
   			$v['grade_id'] = $this->get_grade_name($v['grade_id']);
   			$v['entry_time'] = $this->time_date($v['entry_time']);
   		}
   		//dump($eduInfo);die;
   		return $eduInfo;
   	}
   	public function getSkillInfo($userId){ //技能报名信息
   		$skillInfo = Db::name('studentSkill')
   		                        ->where('userId',$userId)
   		                        ->select();
   		//dump($skillInfo);die;
   		foreach($skillInfo as &$v){
   			$v['type'] = '技能类';
   			$v['school_id'] = $this->get_school_name($v['school_id']);
   			$v['major_id'] = $this->get_major_name($v['major_id']);
   			$v['course_id'] = $this->get_course_name($v['course_id']);
   			$v['entry_time'] = $this->time_date($v['entry_time']);
   		}
   		return $skillInfo;
   	}
   	public function getCkworkInfo($userId){ //学员考勤记录信息
   		$ckworkInfo = Db::name('current_ckwork')->alias('ck')->join('users u','ck.userId=u.userId','left')->where('ck.userId',$userId)->field('ck.*,u.trueName')->select();
   		foreach($ckworkInfo as &$v){
   			$v['object_id'] = $this->get_course_name($v['object_id']);
   			$v['ckwork_type'] = $this->check_type($v['ckwork_type']);
   			$v['createtime'] = $this->time_date($v['createtime']);
   		}
   		//dump($ckworkInfo);die;
   		return $ckworkInfo;
   	}
   	public function getFeeInfo($userId){ //学员缴费记录信息
   		$feeInfo = Db::name('student_fee_log')
   		                ->alias('fee')
   		                ->join('users u','fee.userId=u.userId','left')
				   		->where('fee.userId',$userId)
				   		->field('fee.*,u.trueName')
				   		->select();
   		foreach($feeInfo as &$v){
   			$v['fee_class'] = $this->get_fee_class($v['fee_class']);
   			$v['fee_type'] = $this->get_fee_type($v['fee_type']);
   			$v['pay_type'] = $this->get_pay_type($v['pay_type']);
   			$v['plan_paytime'] = $this->time_date($v['plan_paytime']);
   			$v['pay_time'] = $this->time_date($v['pay_time']);
   			$v['createtime'] = $this->time_date($v['createtime']);
   			$v['lastmodify'] = $this->time_date($v['lastmodify']);
   		}
   		//dump($feeInfo);die;
   		return $feeInfo;
   	}

	/**
	 * 新增
	 */
	public function addu(){    //新增学员信息
		$data = input('post.');
		//dd($data);
		$basic = $data['basic']; //基础信息数据集
        $extend = $data['extend']; //扩展信息数据集
        //$edu = $data['edu']; //学历报名信息数据集
        //$skill = $data['skill']; //技能报名信息数据集
		$basic['lastmodify'] = time();
		$basic['createtime'] = time();
		$basic["loginSecret"] = rand(1000,9999);
    	$basic['loginPwd'] = md5($basic['loginPwd'].$basic['loginSecret']);
    	$basic['brithday'] = $basic['brithday'] ? strtotime($basic['brithday']) : time();
    	$extend['lastmodify'] = time();
		$extend['createtime'] = time();
    	//$edu['entry_time'] = strtotime($edu['entry_time']);
    	//$skill['access_time'] = strtotime($skill['access_time']);
    	//$skill['entry_time'] = strtotime($skill['entry_time']);
    	$basic['userType'] = 0;
    	MBISUnset($data,'userId');
    	Db::startTrans();
		try{
			$result = $this->validate('Users.add')->allowField(true)->insert($basic);
			$userId = $this->getLastInsID();
	        if(false !== $result){
	        	MBISUseImages(1, $userId, $basic['userPhoto']);
                $extend['userId'] = $userId;
                model('studentExtend')->save($extend);
                /*
                $edu['userId'] = $userId;
                $skill['userId'] = $userId;
                if(empty($skill['exam_no']) && empty($edu['exam_no'])){
                	model('studentExtend')->save($extend);
                }
                elseif(!empty($edu['exam_no']) && empty($skill['exam_no'])){
                	model('studentExtend')->save($extend);
                	model('studentEdu')->save($edu);
                }
                elseif(!empty($skill['exam_no']) && empty($edu['exam_no'])){
                	model('studentExtend')->save($extend);
                	model('studentSkill')->save($skill);
                }else{
                	model('studentExtend')->save($extend);
                	model('studentEdu')->save($edu);
                	model('studentSkill')->save($skill);
                }
                */
	        	Db::commit();
	        	return MBISReturn("新增成功", 1);
	        }
		}catch (\Exception $e) {
            Db::rollback();
            return MBISReturn('新增失败',-1);
        }	
	}

	public function adde(){    //新增学员报名信息
		$data = input('post.');
		$userId = $data['userId'];
        $edu = $data['edu'];  //学历报名信息数据集
        $skill = $data['skill'];  //技能报名信息数据集
    	Db::startTrans();
		try{
			if(!empty($edu['school_id']) && empty($skill['school_id'])){
        		$edu['userId'] = $userId;
        		$edu['entry_time'] = strtotime($edu['entry_time']);
        		$result_e = model('studentEdu')->save($edu);
        	}
			if(!empty($skill['school_id']) && empty($edu['school_id'])){
        		$skill['userId'] = $userId;
        		$skill['entry_time'] = strtotime($skill['entry_time']);
        		$result_s = model('studentSkill')->save($skill);
        	}
        	if(!empty($edu['school_id']) && !empty($skill['school_id'])){
        		$edu['userId'] = $userId;
        		$edu['entry_time'] = strtotime($edu['entry_time']);
        		$result_e = model('studentEdu')->save($edu);
        		$skill['userId'] = $userId;
        		$skill['entry_time'] = strtotime($skill['entry_time']);
        		$result_s = model('studentSkill')->save($skill);
        	}
	        if(false !== $result_s){
	        	Db::commit();
	        	return MBISReturn("新增成功", 1);
	        }elseif(false !== $result_e){
	        	Db::commit();
	        	return MBISReturn("新增成功", 1);
	        }
		}catch (\Exception $e) {
            Db::rollback();
            return MBISReturn('新增失败',-1);
        }	
	}

	public function addt(){   //新增老师信息
		$data = input('post.');
		$basic = $data['basic']; //基础信息数据集
        $extend = $data['extend']; //扩展信息数据集
        $basic['lastmodify'] = time();
		$basic['createtime'] = time();
		$basic["loginSecret"] = rand(1000,9999);
    	$basic['loginPwd'] = md5($basic['loginPwd'].$basic['loginSecret']);
    	$basic['userType'] = 1;
    	MBISUnset($basic,'userId');
    	MBISUnset($data,'userId');
    	Db::startTrans();
		try{
			$result = $this->validate('Users.add')->allowField(true)->insert($basic);
			$userId = $this->getLastInsID();
	        if(false !== $result){
	        	MBISUseImages(1, $userId, $basic['userPhoto'], 'users', 'userPhoto');
                $extend['userId'] = $userId;
                $extend['createtime'] = time();
                $extend['lastmodify'] = time();
                model('tcExtend')->save($extend);
	        	Db::commit();
	        	return MBISReturn("新增成功", 1);
	        }
		}catch (\Exception $e) {
            Db::rollback();
            return MBISReturn('新增失败',-1);
        }	
	}
	/*
	*	老师科目配置处理start
	*/
	public function getTeacherInfo($id=0){
		$where = [];
		$where['userType'] = 1;
		$where['userId'] = $id;
		$teacher = Db::name('users')->where($where)->find();	
		if($teacher){
			$set = Db::name('tc_subject_setting')->where('userId',$id)->select();
			if($set){
				$teacher['subject_set'] = $set;
			}else{
				$teacher['subject_set'] = [];
			}
			foreach($teacher['subject_set'] as &$v){
				$v['userId'] = $this->getTeacherName($v['userId']);
				$v['type_id'] = $this->getSubjectType($v['type_id']);
				$v['subject_id'] = $this->getSubjectName($v['subject_id']);
			}
		}
		return $teacher;
	}
	
	public function getSetSubject(){
		$type_id = (int)input('post.type_id');
		if($type_id != ''){
			$subject = Db::name('subject')->where('subject_type_id',$type_id)->field('subject_id,name,subject_no')->select();
			if($subject){
				foreach($subject as &$v){
					if($v['subject_no'] == ''){
						$v['name'] = $v['name'].'';
					}else{
						$v['name'] = $v['name'].'('.$v['subject_no'].')';
					}
				}
				return ['data'=>$subject,'status'=>1];
			}else{
				return ['msg'=>'系统错误','status'=> -2];
			}
		}else{
			return ['status'=> -1,'msg'=>'请选择有效选项'];
		}
	}
	public function addTeacherSet(){
		$data = input('post.');
		if(($data['type_id'] == '') || ($data['subject_id'] == '')){
			return ['msg'=>'请选择有效选项','status'=>-2];
		}
		Db::startTrans();
		try{
			$res = Db::name('tc_subject_setting')->insert($data);
			$ss_id = Db::name('tc_subject_setting')->getLastInsID();
			if(false !== $res){
				$data['ss_id'] = '';
				$data['ss_id'] = $ss_id;
				$data['userId'] = $this->getTeacherName($data['userId']);
				$data['type_id'] = $this->getSubjectType($data['type_id']);
				$data['subject_id'] = $this->getSubjectName($data['subject_id']);
				Db::commit();
	        	return ['msg'=>'新增成功','status'=>1,'data'=>$data];
			}
		}catch (\Exception $e){
			Db::rollback();
            return ['msg'=>'新增失败','status'=>-1];
		}
	}
	public function delTeacherSet(){
		$id = (int)input('post.ssId');
		Db::startTrans();
	    try{
		    $result = Db::name('tc_subject_setting')->where('ss_id='."$id")->delete();
	        if(false !== $result){
	        	Db::commit();
	        	return MBISReturn("删除成功", 1);
	        }
	    }catch (\Exception $e) {
            Db::rollback();
            return MBISReturn('删除失败',-1);
        }
	}
	public function getSubjects(){
		return Db::name('subject')->field('subject_id,name')->select();
	}
	public function getTeacherName($id=0){
		return Db::name('users')->where('userId',$id)->value('trueName');
	}
	public function getSubjectType($type=0){
		switch($type){
			case 1: return '学历';
			case 2: return '技能';
		}
	}
	//  老师科目配置处理end
	##
	public function addz(){  //新增咨询师信息
		$data = input('post.');
		$basic = $data['basic']; //基础信息数据集
        $basic['lastmodify'] = time();
		$basic['createtime'] = time();
		$basic["loginSecret"] = rand(1000,9999);
		$basic["staffs_id"]   = $basic['staffs_id'];
		
    	$basic['loginPwd'] = md5($basic['loginPwd'].$basic['loginSecret']);
    	$basic['userType'] = 2;
    	MBISUnset($basic,'userId');
    	MBISUnset($data,'userId');
    	Db::startTrans();
		try{
			$result = $this->validate('Users.add')->allowField(true)->insert($basic);
			$userId = $this->getLastInsID();
	        if(false !== $result){
	        	MBISUseImages(1, $userId, $basic['userPhoto']);
	        	Db::commit();
	        	return MBISReturn("新增成功", 1);
	        }
		}catch (\Exception $e) {
            Db::rollback();
            return MBISReturn('新增失败',-1);
        }	
	}

    /**
	 * 编辑
	 */
	public function editu(){
        $postdata = $data = input('post.');
        //dd($postdata);
        if(isset($data['extend']))
        {
            $extend = $data['extend'];
        }
        //基本信息
        $data = $data['basic'];
		$data['lastmodify'] = time();
		$data['brithday'] = $data['brithday'] ? strtotime($data['brithday']) : time();
        if(isset($data['userId']))
        {
		    $Id = (int)$data['userId'];
        }
        else
        {
            $Id = Input('post.userId/d',0);   
        }
		$u = $this->where('userId',$Id)->field('loginSecret')->find();
		if(empty($u))return MBISReturn('无效的用户');
		//判断是否需要修改密码
		if(empty($data['loginPwd'])){
			unset($data['loginPwd']);
		}else{
    		$data['loginPwd'] = md5($data['loginPwd'].$u['loginSecret']);
		}
		//dump($Id);
		//dump($extend);
		//dd($data);
		Db::startTrans();
		try{
			if(isset($data['userPhoto'])){
			    MBISUseImages(1, $Id, $data['userPhoto'], 'users', 'userPhoto');
			}

			MBISUnset($data,'userId');
		    $result = $this->allowField(true)->save($data,['userId'=>$Id]);
	        if(false !== $result){
                if(isset($postdata['extend']))
                {
                    model('studentExtend')->save($extend,['userId'=>$Id]);
                }
	        	Db::commit();
	        	return MBISReturn("编辑成功", 1);
	        }
		}catch (\Exception $e) {
            Db::rollback();
            return MBISReturn('编辑失败',-1);
        }
	}
	
	public function editt(){
        $postdata = $data = input('post.');
        if(isset($data['extend'])){
            $extend = $data['extend'];
        }
        //基本信息
        $data = $data['basic'];
		$data['lastmodify'] = time();
        if(isset($data['userId'])){
		    $Id = (int)$data['userId'];
        }else{
            $Id = Input('post.userId/d',0);   
        }
		$u = $this->where('userId',$Id)->field('loginSecret')->find();
		if(empty($u))return MBISReturn('无效的用户');
		//判断是否需要修改密码
		if(empty($data['loginPwd'])){
			unset($data['loginPwd']);
		}else{
    		$data['loginPwd'] = md5($data['loginPwd'].$u['loginSecret']);
		}
		Db::startTrans();
		try{
			if(isset($data['userPhoto'])){
			    MBISUseImages(1, $Id, $data['userPhoto'], 'users', 'userPhoto');
			}
			
			MBISUnset($data,'createTime,userId');
		    $result = $this->allowField(true)->save($data,['userId'=>$Id]);
	        if(false !== $result){
                if(isset($postdata['extend'])){
                    model('tcExtend')->save($extend,['userId'=>$Id]);
                }
	        	Db::commit();
	        	return MBISReturn("编辑成功", 1);
	        }
		}catch (\Exception $e) {
            Db::rollback();
            return MBISReturn('编辑失败',-1);
        }
	}

	public function editz(){
        $postdata = $data = input('post.');
        /*
        if(isset($data['extend'])){
            $extend = $data['extend'];
        }
        */
        //基本信息
        //dd($data);
        $data = $data['basic'];
		$data['lastmodify'] = time();
        if(isset($data['userId'])){
		    $Id = (int)$data['userId'];
        }else{
            $Id = Input('post.userId/d',0);   
        }
		$u = $this->where('userId',$Id)->field('loginSecret')->find();
		if(empty($u))return MBISReturn('无效的用户');
		//判断是否需要修改密码
		if(empty($data['loginPwd'])){
			unset($data['loginPwd']);
		}else{
    		$data['loginPwd'] = md5($data['loginPwd'].$u['loginSecret']);
		}
		Db::startTrans();
		try{
			if(isset($data['userPhoto'])){
			    MBISUseImages(1, $Id, $data['userPhoto'], 'users', 'userPhoto');
			}
			
			MBISUnset($data,'createTime,userId');
		    $result = $this->allowField(true)->save($data,['userId'=>$Id]);
	        if(false !== $result){
	        	/*
                if(isset($postdata['extend'])){
                    model('tcExtend')->save($extend,['userId'=>$Id]);
                }
				*/
	        	Db::commit();
	        	return MBISReturn("编辑成功", 1);
	        }
		}catch (\Exception $e) {
            Db::rollback();
            return MBISReturn('编辑失败',-1);
        }
	}

	/**
	 * 删除
	 */
    public function delu(){
	    $id = (int)input('post.id');
	    Db::startTrans();
	    try{
		    $data = [];
			$data['dataFlag'] = -1;
		    $result = $this->update($data,['userId'=>$id]);
	        if(false !== $result){
	        	MBISUnuseImage('users','userPhoto',$id);
                model('studentExtend')->where(['userId'=>$id])->delete();  //学员扩展信息
                model('studentEdu')->where(['userId'=>$id])->delete();  //学员学历报名信息
                model('studentSkill')->where(['userId'=>$id])->delete();  //学员技能报名信息
	        	Db::commit();
	        	return MBISReturn("删除成功", 1);
	        }
	    }catch (\Exception $e) {
            Db::rollback();
            return MBISReturn('编辑失败',-1);
        }
	}

	public function delt(){
	    $id = (int)input('post.id');
	    Db::startTrans();
	    try{
		    $data = [];
			$data['dataFlag'] = -1;
		    $result = $this->update($data,['userId'=>$id]);
	        if(false !== $result){
	        	MBISUnuseImage('users','userPhoto',$id);
                model('TcExtend')->where(['userId'=>$id])->delete();
	        	Db::commit();
	        	return MBISReturn("删除成功", 1);
	        }
	    }catch (\Exception $e) {
            Db::rollback();
            return MBISReturn('编辑失败',-1);
        }
	}

	public function delz(){
	    $id = (int)input('post.id');
	    Db::startTrans();
	    try{
		    $data = [];
			$data['dataFlag'] = -1;
		    $result = $this->update($data,['userId'=>$id]);
	        if(false !== $result){
	        	MBISUnuseImage('users','userPhoto',$id);
	        	Db::commit();
	        	return MBISReturn("删除成功", 1);
	        }
	    }catch (\Exception $e) {
            Db::rollback();
            return MBISReturn('编辑失败',-1);
        }
	}

	/**
	* 是否启用
	*/
	public function changeUserStatus($id, $status){
		$result = $this->update(['userStatus'=>(int)$status],['userId'=>(int)$id]);
		if(false !== $result){
        	return MBISReturn("删除成功", 1);
        }else{
        	return MBISReturn($this->getError(),-1);
        }
	}
	/**
	* 根据用户名查找用户
	*/
	public function getByName($name){
		return $this->field(['userId','loginName'])->where(['loginName'=>['like',"%$name%"]])->select();
	}
	/**
	* 获取所有用户id
	*/
	public function getAllUserId()
	{
		return $this->where('dataFlag',1)->column('userId');
	}
	public function get_school_name($id){
		return Db::name('school')->where('school_id',$id)->value('name');
	}
	public function get_major_name($id){
		return Db::name('major')->where('major_id',$id)->value('name');
	}
	public function get_course_name($id){
		return Db::name('course')->where('course_id',$id)->value('name');
	}
	public function get_grade_name($id){
		return Db::name('grade')->where('grade_id',$id)->value('name');
	}
	public function getSubjectName($id=0){
		return Db::name('subject')->where('subject_id',$id)->value('name');
	}
	/**
	 * 获取学院列表/专业列表/课程列表
	 */
	public function get_schools($type_id){
		return Db::name('school')->where('jump_type',$type_id)->field('school_id,name')->select();
	}
	//获取学院类型  1=学历类  2=技能类
	public function getSchoolType($id=0){
		return Db::name('school')->where('school_id',$id)->value('jump_type');
	}
	//会员等级列表
	public function get_rank_lists(){
		return Db::name('user_ranks')->field('*')->where('dataFlag',1)->select();
	}
	public function get_department_list(){
		$department_id_array = Db::name('employee_type')->column('department_id');
		$department = Db::name('department')->where('department_id','in',$department_id_array)->field('*')->select();
        foreach ($department as &$v){
        	if($v['parent_id'] != 0){
        		$v['department'] = ($this->getDepartmentName($v['parent_id'])).'--'.$v['name'];
        	}else{
        		$v['department'] = $v['name'];
        	}
        }
        //dump($department);die;
        return $department;
	}
	public function get_employeetype_list(){
		$employee_type = Db::name('employee_type')->field('*')->select();
		foreach($employee_type as &$v){
			if($v['department_id'] != 0){
				$v['department'] = $this->getDepartmentName($v['department_id']);
			}else{
				$v['department'] = '';
			}
		}
		return $employee_type;
	}
	public function get_employee_list(){
		return Db::name('employee')->field('*')->select();
	}
	public function getDepartmentName($id=0){
    	return Db::name('department')->where('department_id',$id)->value('name');
    }
    public function get_department_name($id=0){
    	$department = Db::name('department');
    	$name = '';
    	if($id){
    		$departmentName = $department->where('department_id',$id)->value('name');
    		$parent_id = $department->where('department_id',$id)->value('parent_id');
    		if($parent_id != 0){
    			$parentName = $department->where('department_id',$parent_id)->value('name');
    		}else{
    			$parentName = '';
    		}

    		$name = $parentName.'&nbsp;&nbsp;'.$departmentName;
    	}
    	//dump($name);die;
    	return $name;
    }
    public function get_employeetype_name($id=0){
    	return Db::name('employee_type')->where('employee_type_id',$id)->value('name');
    }
	/**
	 * 获取课程列表
	 */
	public function get_course_lists(){
		return Db::name('course')->field('*')->where('type_id',1)->select();
	}

	/**
	 * 获取年级列表
	 */
	public function get_grade_lists(){
		return Db::name('grade')->field('*')->select();
	}

	public function time_date($time){
		return date('Y-m-d',$time);
	}

	public function getSex($sex){  //性别
		switch($sex){
			case 0:return '保密';
			case 1:return '男';
			case 2:return '女';
		}
	}
	public function getUserStatus($status){  //账号状态
		switch($status){
			case 1:return '启用';
			case 0:return '停用';
		}
	}
	public function get_uidType($type){
		switch($type){
			case 1:return '新生';
			case 2:return '在学生';
			case 3:return '会员';
		}
	}
	public function get_student_type($type){ //学员类型
		switch($type){
			case 1:return '技能';
			case 2:return '学历';
			case 3:return '技能学历';
		}
	}
	public function get_study_status($status){ //学习状态
		switch($status){
			case 1:return '在读';
			case 2:return '毕业';
			case 3:return '过期';
			case 4:return '弃学';
			case 5:return '休学';
			case 6:return '退学';
			case 7:return '其他';
			default : return '未知';
		}
	}
	public function get_study_type($type){ //学习形式
		switch($type){
			case 1:return '成考';
			case 2:return '华师大';
			case 3:return '深大';
			case 4:return '网教';
			default : return '未知';
		}
	}
	public function get_student_pay_type($type){ //付费类型
		switch($type){
			case 0:return '未付费'; 
			case 1:return '全额'; 
			case 2:return '分期'; 
			case 3:return '定金'; 
		}
	}
	public function getUserRank($id){  //会员等级
		return Db::name('user_ranks')->where('rankId',$id)->value('rankName');
	}
	public function check_type($type){
		switch($type){
			case 1:
				return '标准考勤';
				break;
			case 2:
				return '自定义考勤';
				break;
		}
	}
	public function get_fee_type($type){
		switch($type){
			case 1:
				return '一次性收费';
				break;
			case 2:
				return '定金';
				break;
			case 3:
				return '补费';
				break;
			default :
				return '其他';
		}
	}

	public function get_fee_class($type){
		switch($type){
			case 1:
				return '培训费';
				break;
			case 2:
				return '证书费';
				break;
			case 3:
				return '报考费';
				break;
			case 4:
				return '学位费';
				break;
			default :
				return '其他费用';
		}
	}

	public function get_pay_type($type){
		switch($type){
			case 1:
				return '线上收款-支付宝';
				break;
			case 2:
				return '线上收款-微信';
				break;
			case 3:
				return '线上收款-银联';
				break;
			case 4:
				return '线下收款-POS机';
				break;
			case 5:
				return '线下收款-现金';
				break;
			case 6:
				return '线下收款-对公转账';
				break;
			case 7:
				return '线下收款-支票支付';
				break;
			case 8:
				return '其他支付';
				break;
		}
	}

	/**
	 * ajax 操作
	 */
	public function checkSchool(){
		$school_id = (int)input('post.school_id');
		//获取选中学校对应的所有可选专业
		$majors = Db::name('major')->where('school_id',$school_id)->field('major_id,name')->select();
		if(!empty($majors)){
			return ['data'=>$majors,'status'=>1];
		}else{
			return ['msg'=>'暂无专业信息','staatus'=>-2];
		}
	}
	public function checkMajor(){
		$major_id = (int)input('post.major_id');
		//获取选中专业对应的所有可选课程
		$courses = Db::name('course')->where('major_id',$major_id)->field('course_id,name')->select();
		if(!empty($courses)){
			return ['data'=>$courses,'status'=>1];
		}else{
			return ['msg'=>'暂无课程信息','staatus'=>-2];
		}
	}
	public function dateSelect(){
		$userId = (int)input('post.userId');
        $start = strtotime(input('post.start'));
        $end = strtotime(input('post.end'));
        //判断是否选择了查询时间
        if(($start == '') || ($end == '')){
        	return ['msg'=>'请选择考勤起止日期','status'=>-3];
        }
        //判断开始时间是否小于结束时间
        if($start > $end){
        	return ['msg'=>'开始时间不能大于结束时间','status'=>-1];
        }
        if($start < $end){
        	$ckworklog = Db::name('current_ckwork')->alias('ck')->join('users u','ck.userId=u.userId','left')->where('ck.userId',$userId)->where('ck.createtime',['>',$start],['<',$end],'and')->field('ck.*,u.trueName')->select();
        	
        	if(!empty($ckworklog)){
        		foreach($ckworklog as &$v){
        			$v['object_id'] = $this->get_course_name($v['object_id']);
   					$v['ckwork_type'] = $this->check_type($v['ckwork_type']);
   					$v['createtime'] = $this->time_date($v['createtime']);
        		}
        		//dump($ckworklog);die;
        		return ['data'=>$ckworklog,'status'=>1];
        	}else{
        		return ['msg'=>'抱歉,没有找到您要查找的信息','status'=> -2];
        	}
        }
	}
	public function checkdep(){
		$department_id = (int)input('post.department_id'); //选中的部门
		if(!empty($department_id)){
			//$sons = Db::name('department')->where('parent_id',$department_id)->select();
			//if($sons){
			//	return ['data'=>$sons,'status'=>1];
			//}else{
				$employeetypes = Db::name('employee_type')->where('department_id',$department_id)->select();
				if(!empty($employeetypes)){
					return ['data'=>$employeetypes,'status'=>1];
				}else{
					return ['msg'=>'该部门暂未设置岗位','status'=> -2];
				}
			//}
		}
	}
	public function checkType(){
		$departmentId = (int)input('post.departmentId'); //选中的部门
		if(!empty($departmentId)){
			//$sons = Db::name('department')->where('parent_id',$departmentId)->select();
			//if($sons){
			//	return ['data'=>$sons,'status'=>1];
			//}else{
				$employeetypes = Db::name('employee_type')->where('department_id',$departmentId)->select();
				if(!empty($employeetypes)){
					return ['data'=>$employeetypes,'status'=>1];
				}else{
					return ['msg'=>'该部门暂未设置岗位','status'=> -2];
				}
			//}
		}
	}
	public function checkemp(){
		$employeetypeId = (int)input('post.employeetypeId'); //选中的部门
		if(!empty($employeetypeId)){
			$employee = Db::name('employee')->where('employee_type_id',$employeetypeId)->select();
			if($employee){
				return ['data'=>$employee,'status'=>1];
			}else{
				return ['msg'=>'该岗位暂未安排员工','status'=> -2];
			}
		}
		
	}
	public function checkname(){
		$employee_id = (int)input('post.employee_id');
		if($employee_id !== ''){
			$name = Db::name('employee')->where('employee_id',$employee_id)->value('name');
			if($name){
				return ['name'=>$name,'status'=>1];
			}else{
				return ['msg'=>'未找到名字','status'=>-2];
			}
		}
	}

	/**
     *
     * 导出Excel
     */
    public function expUsersU(){//导出学员Excel
    	//dd($_GET);
    	//$data= input('get.');
    	//dump($data);die;
    	$where = [];
		//$start = strtotime(input('get.start'));
		//$end = strtotime(input('get.end'));
		$where['dataFlag'] = 1;   
		$where['userType'] = 0;  //学员类型
		// $lName = input('get.loginName1');
		// $phone = input('get.loginPhone');
		// $trueName = input('get.trueName');
		$schoolId = input('get.school_id');
		$majorId = input('get.major_id');
		$courseId = input('get.course_id');
		//$uType = input('get.userType');   
		//$uStatus = input('get.userStatus1');
		/*
		if(is_numeric($uType))
			$where['userType'] = ['=',"$uType"];
		if(is_numeric($uStatus))
			$where['userStatus'] = ['=',"$uStatus"];
		*/
		/*
		if(!empty($start) && !empty($end)){
			$where['u.createtime'] = ['between',["$start","$end"]];
		}
		if(!empty($lName))
			$where['u.loginName'] = ['like',"%$lName%"];
		if(!empty($phone))
			$where['u.userPhone'] = ['like',"%$phone%"];
		if(!empty($trueName))
			$where['u.trueName'] = ['like',"%$trueName%"];
		*/
		if(empty($schoolId)){

			$rs = $this
					->alias('u')
					->join('student_extend x','u.userId=x.userId','left')  //学员扩展信息
					->where($where) 
					->field('u.*,x.urgency_contact,x.urgency_contact_mobile')
					->order('u.userId desc')
					//->order('userId desc')
					->select();
		}else{
			
			$where['school_id'] = ['=',"$schoolId"];
			$schoolType = $this->getSchoolType($schoolId); //获取学院类型 1=学历 2=技能
		
			if($schoolType == 1){ //查询条件选择的学院是学历类 --> 关联学历报名表查询

				if(!empty($majorId))
					$where['d.major_id'] = ['=',"$majorId"];
				if(!empty($courseId))
					$where['d.course_id'] = ['=',"$courseId"];
				$rs = $this->alias('u')
					->join('student_extend x','u.userId=x.userId','left')  //学员扩展信息
					->join('student_edu d','u.userId=d.userId','left')  //学员学历报名信息
					->where($where) 
					->field('u.*,d.school_id,d.major_id,d.course_id,x.urgency_contact,x.urgency_contact_mobile')
					->order('u.userId desc')
					->select();

			}elseif($schoolType == 2){ //查询条件选择的学院是技能类 --> 关联技能报名表查询

				if(!empty($majorId))
					$where['k.major_id'] = ['=',"$majorId"];
				if(!empty($courseId))
					$where['k.course_id'] = ['=',"$courseId"];
				$rs = $this->alias('u')
					->join('student_extend x','u.userId=x.userId','left')  //学员扩展信息
					->join('student_skill k','u.userId=k.userId','left')  //学员技能报名信息
					->where($where) 
					->field('u.*,k.school_id,k.major_id,k.course_id,x.urgency_contact,x.urgency_contact_mobile')
					->order('u.userId desc')
					->select();
			}	
		}
		//dump($rs);die;
    	if($rs){
    		$xlsData = $rs;
    	}else{
    		die("<span>导出Excel出错</span><button type='button' class='btn' onclick='javascript:history.go(-1)'>返&nbsp;回</button>");
    		//$xlsData = [];
    	}
        $xlsName  = "Users";
        $xlsCell  = array(
        array('idcard','身份证号码'),
        array('student_no','学员编号'),
        array('trueName','姓名'),
        array('study_status','学习状态'),
        array('userPhone','联系电话'),
        array('userQQ','QQ'),
        array('userEmail','邮箱'),
        array('urgency_contact','紧急联系人'),
        array('urgency_contact_mobile','紧急联系电话'),
        );
        foreach ($xlsData as $k => $v)
        {
            $xlsData[$k]['idcard']=" ".$v['idcard'];
            $xlsData[$k]['userPhone']=" ".$v['userPhone'];       
            $xlsData[$k]['study_status']=$this->get_study_status($v['study_status']);
        }
        //dump($xlsData);die;
        $this->exportExcel($xlsName,$xlsCell,$xlsData);
         
    }

    public function exportExcel($expTitle,$expCellName,$expTableData){
        import('phpexcel.PHPExcel');
        import('phpexcel.PHPExcel.IOFactory');
        import('phpexcel.PHPExcel.Style.Alignment');
        import('phpexcel.PHPExcel_Cell_DataType');
        $objPHPExcel = new \PHPExcel();
        $xlsTitle = iconv('utf-8', 'gb2312', $expTitle);//文件名称
        $fileName = $xlsTitle.date('_YmdHis').'.xlsx';//or $xlsTitle 文件名称可根据自己情况设定
        $cellNum = count($expCellName);
        $dataNum = count($expTableData);
        $cellName = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ');
        //Excel表格样式设置区  ****** start  **********************************************************
        /*
        $styleArray1 = array(
		  'font' => array(
		    'bold' => true,
		    'size'=>12,
		    'color'=>array(
		      'argb' => '00000000',
		    ),
		    'alignment' => array(
    		'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
    		'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER,
  			),
		  ),
		);
		
		$objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArray1); */
        //$objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(12); //设置表格默认列宽(全部)
        //$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true); //设置某一列自适应宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(22); //设置某一列宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(16); //设置某一列宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(12); //设置某一列宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(12); //设置某一列宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(14); //设置某一列宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(14); //设置某一列宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(32); //设置某一列宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(14); //设置某一列宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(16); //设置某一列宽度
        //$objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(20);//设置表格默认行高(全部)
        $objPHPExcel->getActiveSheet()->getDefaultStyle()->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER); //设置水平居中
        $objPHPExcel->getActiveSheet()->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER); //设置垂直居中
        $objPHPExcel->getActiveSheet()->getStyle('A')->getNumberFormat()
    	->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_TEXT);

        $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true); //设置字体加粗
        $objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('C1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('D1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('E1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('F1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('G1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('H1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('I1')->getFont()->setBold(true);

        //$objPHPExcel->getActiveSheet()->freezePaneByColumnAndRow(9,2); //冻结单元格
    	$objPHPExcel->getActiveSheet()->freezePane('A1');
    	$objPHPExcel->getActiveSheet()->freezePane('B1');
    	$objPHPExcel->getActiveSheet()->freezePane('C1');
    	$objPHPExcel->getActiveSheet()->freezePane('D1');
    	$objPHPExcel->getActiveSheet()->freezePane('E1');
    	$objPHPExcel->getActiveSheet()->freezePane('F1');
    	$objPHPExcel->getActiveSheet()->freezePane('G1');
    	$objPHPExcel->getActiveSheet()->freezePane('H1');
    	$objPHPExcel->getActiveSheet()->freezePane('I1');

        //Excel表格样式设置区  ****** end  ************************************************************
        for($i=0;$i<$cellNum;$i++){
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellName[$i].'1', $expCellName[$i][1]); 
        }  
        for($i=0;$i<$dataNum;$i++){
          for($j=0;$j<$cellNum;$j++){
            $objPHPExcel->getActiveSheet(0)->setCellValue($cellName[$j].($i+2), $expTableData[$i][$expCellName[$j][0]]);
          }             
        }
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        /*
        header('Content-type:application/vnd.ms-excel;charset=utf-8;name="'.$xlsTitle.'.xlsx"');
        header("Content-Disposition:attachment;filename=$fileName");//attachment新窗口打印inline本窗口打印
        header('Cache-Control: max-age=0');

        header("Content-Type: application/force-download"); 
   		header("Content-Type: application/octet-stream"); 
   		header("Content-Type: application/download");  
   		header("Content-Transfer-Encoding: binary"); 
   		header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
   		header("Pragma: no-cache"); 
        
        $objWriter->save('php://output');exit;  //输出到浏览器下载
        */
       	$path = TEMP_PATH;
        $path_file = TEMP_PATH."$fileName";
        $objWriter->save($path_file); //保存到临时文件目录
        $url = 'InfoDownload?path='.$path.'&file='.$fileName;
        $html = 
        "<span>Excel文件导出成功</span><a href=".$url.">立即下载</a><button type='button' class='btn' onclick='javascript:history.go(-1)'>返&nbsp;回</button>";
        die("$html");
        
    }

    /*************************************************************************************************************/
    /*************************************************************************************************************/
    /*
    *学历类学籍管理重写
     */
    public function timeToDate($time){  //时间戳转时间
    	if($time){
    		$time = date('Y-m',$time);
    	}else{
    		$time = date('Y-m',time());
    	}
    	return $time; 
    }

    public function get_culture_method($value){  //培养方式
    	switch($value){
    		case 1:return '普通';
    	}
    }

    public function get_education_level($value){  //文化程度
    	switch($value){
    		case 1:return '初中毕业';
    		case 2:return '高中毕业';
    	}
    }

    public function get_level_id($id){  //报考层次
    	switch($id){
    		case 1:return '高升专';
    		case 2:return '专升本';
    	}
    }

    public function pageQueryUser(){
    	$exam_type = session('examType');
    	$where = [];
        $where['u.dataFlag'] = 1;
        $where['u.userType'] = 0;
    	$school_id = input('get.school_id');
    	$major_id = input('get.major_id');
    	$level_id = input('get.level_id');
    	$study_status = input('get.study_status');

    	if($school_id !=''){
    		$where['d.school_id'] = ['=',"$school_id"];
    	}
    	if($major_id !=''){
    		$where['d.major_id'] = ['=',"$major_id"];
    	}
    	if($level_id !=''){
    		$where['d.level_id'] = ['=',"$level_id"];
    	}
    	if($study_status !=''){
    		$where['u.study_status'] = ['=',"$study_status"];
    	}
    	
    	$key = input('get.key');
		$key_value = trim(input('get.key_value'));
		if(($key !== '') && ($key_value !== '')){
			if($key =='trueName'){
				$where['u.trueName'] = ['like',"%$key_value%"];
			}elseif($key == 'student_no'){
				$where['u.student_no'] = ['like',"%$key_value%"];
			}elseif($key == 'userPhone'){
				$where['u.userPhone'] = ['like',"%$key_value%"];
			}elseif($key == 'idcard'){
				$where['u.idcard'] = ['like',"%$key_value%"];
			}
		}

    	//$where['d.exam_type'] = ['=',"$exam_type"];
    	$join = [];
    	$join = [
    		['student_extend x','u.userId=x.userId','left'],
    		['student_edu d','u.userId=d.userId','left'],
    	    ['school ss','ss.school_id = d.school_id','left'],
  			//['major_edu m','m.major_id=d.major_id','left']
    	];
    	$field = 'u.userId,u.trueName,u.userQQ,u.idcard,u.student_no,u.userPhone,u.userEmail,u.study_status,u.user_weixin,x.urgency_contact,x.urgency_contact_mobile,x.company,x.job_content,x.mobile2';

    	$page = $this
    			->alias('u')
    			->join($join)
    			->where($where)
    			->field($field)
                ->group('u.userId')
    			->order('u.lastmodify desc')
                //->select();
				->paginate(input('pagesize/d'))
				->toArray();
                //dump($page);
		//getLastSql();
        $total_num = $this
    			->alias('u')
    			->join($join)
    			->where($where)
    			->field($field)
                ->group('u.userId')
                ->select();
        $page['Total'] = count($total_num);
		if(count($page['Rows'])>0){
			foreach ($page['Rows'] as $key => $v){
                $page['Rows'][$key]['study_status'] = $this->get_study_status($v['study_status']);
			}
		}
		return $page;
    }

    public function getInfoOne($id,$Tokey=''){
    	$exam_type = session('examType');
    	$where = [];
    	//$where['d.exam_type'] = ['=',"$exam_type"];
    	$where['u.userId']    = ['=',"$id"];

    	$join = [];
    	$join = [
    		['student_extend x','u.userId=x.userId','left'],
    		['student_edu d','u.userId=d.userId','left'],
  			['school s','s.school_id=d.school_id','left'],
  			['major_edu m','m.major_id=d.major_id','left'],
  			['grade g','u.grade_id=g.grade_id','left']
    	];

    	$field = 'u.userId,u.trueName,u.userQQ,u.userPhone,u.userEmail,u.student_no,u.pre_entry_no,u.study_status,u.idcard,u.userSex,u.user_weixin,u.nation,u.culture_method,u.education_level,u.graduate_colleges,u.colleges_number,u.graduate_date,u.certificate_number,u.idcard_Photo,u.identification_photo,u.brfore_certificate_photo,u.after_certificate_photo,u.grade_id,x.mobile2,x.exam_no,x.login_pass,x.exam_no2,x.login_pass2,x.address,x.company,x.urgency_contact,x.urgency_contact_mobile,d.school_id,d.major_id,d.level_id,s.name as school_name,m.name as major_name,g.name as grade_name';
    	$res = $this
    			->alias('u')
    			->join($join)
    			->where($where)
    			->field($field)
    			->find();
    	if($Tokey == 'look' && $res ){
    	    $res['graduate_date'] = $this->timeToDate($res['graduate_date']);
    		$res['study_status'] = $this->get_study_status($res['study_status']);
    		$res['userSex'] = $this->getSex($res['userSex']);
    		$res['culture_method'] = $this->get_culture_method($res['culture_method']);
    		$res['education_level'] = $this->get_education_level($res['education_level']);
    		$res['level_id'] = $this->get_level_id($res['level_id']);
    	}
		return $res;
    }

    public function editUser(){
    	$data = input('post.');
    	$userId = $data['userId'];
    	$user = isset($data['user'])?$data['user']:[];
    	$extend = isset($data['extend'])?$data['extend']:[];
    	$edu = isset($data['edu'])?$data['edu']:[];
    	$user['graduate_date'] = $user['graduate_date'] ? strtotime($user['graduate_date']) : 0;
    	Db::startTrans();
		try{
            if(empty($user['trueName'])):
                return MBISReturn('请输入真实姓名',-1);
            endif;
            //判断真实姓名是否重复
            if(Db::name('users')->where(['userId'=>['neq',$userId],'trueName'=>$user['trueName']])->find()):
                return MBISReturn('真实姓名重复了，请修改',-1);
            endif;
            if(empty($user['idcard'])):
                return MBISReturn('请输入身份证号',-1);
            endif;
            //判断这是身份证号是否重复
            if(Db::name('users')->where(['userId'=>['neq',$userId],'idcard'=>$user['idcard']])->find()):
                return MBISReturn('身份证号重复了，请修改',-1);
            endif;
            if(empty($user['student_no'])):
                return MBISReturn('请输入学员编号',-1);
            endif;
            //判断这是学员编号是否重复
            if(Db::name('users')->where(['userId'=>['neq',$userId],'student_no'=>$user['student_no']])->find()):
                return MBISReturn('学员编号重复了，请修改',-1);
            endif;
            if(empty($user['userPhone'])):
                return MBISReturn('请输入联系电话',-1);
            endif;
            //判断这是联系电话是否重复
            if(Db::name('users')->where(['userId'=>['neq',$userId],'userPhone'=>$user['userPhone']])->find()):
                return MBISReturn('联系电话重复了，请修改',-1);
            endif;
            
			if(isset($user['idcard_Photo'])){
			    MBISUseImages(1, $userId, $user['idcard_Photo'], 'users', 'idcard_Photo');
			}
			if(isset($user['identification_photo'])){
			    MBISUseImages(1, $userId, $user['identification_photo'], 'users', 'identification_photo');
			}
			if(isset($user['brfore_certificate_photo'])){
			    MBISUseImages(1, $userId, $user['brfore_certificate_photo'], 'users', 'brfore_certificate_photo');
			}
			if(isset($user['after_certificate_photo'])){
			    MBISUseImages(1, $userId, $user['after_certificate_photo'], 'users', 'after_certificate_photo');
			}
		    $result = $this->allowField(true)->save($user,['userId'=>$userId]);
	        if(false !== $result){
                if(isset($data['extend']))
                {
                    model('studentExtend')->save($extend,['userId'=>$userId]);
                }
                if(isset($data['edu']))
                {
                	model('studentEdu')->save($edu,['userId'=>$userId]);
                }
	        	Db::commit();
	        	return MBISReturn("编辑成功", 1);
	        }
		}catch (\Exception $e) {
            Db::rollback();
            return MBISReturn('编辑失败',-1);
        }
    }

    public function getGrade($type){
    	$grade = Db::name('grade')->where('exam_type',$type)->field('grade_id,name')->select();
    	return $grade;
    }

    public function getSchool($type){
    	$where = [];
    	$where['m.exam_type'] = ['=',"$type"];

    	$field = 's.school_id,s.name';

    	$join = [];
    	$join = [
    		['major_edu m','FIND_IN_SET(s.school_id,m.school_ids)','left']
    	];
    	$school = Db::name('school')
    					->alias('s')
    					->join($join)
    					->where($where)
    					->field($field)
    					->group('s.school_id')
    					->select();
    	//dd($school);
    	return $school;
    }

    public function getMajor($type){
    	$major = Db::name('major_edu')->where('exam_type',$type)->field('major_id,name')->select();
    	return $major;
    }

    public function getCourse($type){
    	$where = [];
    	$where['m.exam_type'] = ['=',"$type"];

    	$field = 'c.course_id,c.name';

    	$join = [];
    	$join = [
    		['major_edu m','c.major_id=m.major_id','left']
    	];

    	$course = Db::name('course')
    					->alias('c')
    					->join($join)
    					->where($where)
    					->field($field)
    					->select();
    	//dd($course);
    	return $course;
    }

    public function majorGet(){
    	$school_id = input('post.school_id');
    	$where = 'FIND_IN_SET('."$school_id".',school_ids)';
    	$field = 'major_id,name';
    	$majors = Db::name('major_edu')
    					->where($where)
    					->field($field)
    					->select();
    	if(!empty($majors)){
    		return ['data'=>$majors,'status'=>1];
    	}else{
    		return ['msg'=>'抱歉,出错了','status'=>-1];
    	}
    }

    public function levelGet(){
    	$major_id = input('post.major_id');
    	$where = [];
    	$where['major_id'] = ['=',"$major_id"];
    	$levels = Db::name('major_edu_extend')
    					->where($where)
    					->field('level_id')
    					->select();
    	if(!empty($levels)){
    		foreach($levels as &$v){
    			$v['level_name'] = $this->get_level_id($v['level_id']);
    		}
    		return ['data'=>$levels,'status'=>1];
    	}else{
    		return ['msg'=>'抱歉,出错了','status'=>-1];
    	}
    }


    /**
     *
     * 导出Excel
     */
    public function expUsersEdu(){//导出学员Excel
    	//dd($_GET);
    	//$data= input('get.');
    	//dump($data);die;
    	$where = [];
		//$start = strtotime(input('get.start'));
		//$end = strtotime(input('get.end'));
		$where['u.dataFlag'] = 1;   
		$where['u.userType'] = 0;  //学员类型
		
		$exam_type = session('examType');
    	$school_id = input('get.school_id');
    	$major_id = input('get.major_id');
    	$level_id = input('get.level_id');
    	$study_status = input('get.study_status');

    	if($school_id !=''){
    		$where['d.school_id'] = ['=',"$school_id"];
    	}
    	if($major_id !=''){
    		$where['d.major_id'] = ['=',"$major_id"];
    	}
    	if($level_id !=''){
    		$where['d.level_id'] = ['=',"$level_id"];
    	}
    	if($study_status !=''){
    		$where['u.study_status'] = ['=',"$study_status"];
    	}
    	
    	$key = input('get.key');
		$key_value = trim(input('get.key_value'));
		if(($key !== '') && ($key_value !== '')){
			if($key =='trueName'){
				$where['u.trueName'] = ['like',"%$key_value%"];
			}elseif($key == 'student_no'){
				$where['u.student_no'] = ['like',"%$key_value%"];
			}elseif($key == 'userPhone'){
				$where['u.userPhone'] = ['like',"%$key_value%"];
			}elseif($key == 'idcard'){
				$where['u.idcard'] = ['like',"%$key_value%"];
			}
		}

    	//$where['ss.exam_type'] = ['=',"$exam_type"];
        //$where['d.edu_id'] = ['>',0];
    	$join = [
    		['student_extend x','u.userId=x.userId','left'],
    		['student_edu d','u.userId=d.userId','left'],
    	    ['school ss','ss.school_id=d.school_id','left'],
  			//['major_edu m','m.major_id=d.major_id','left']
    	];
    	
    	$field = 'u.userId,u.trueName,u.userQQ,u.idcard,u.student_no,u.userPhone,u.userEmail,u.study_status,u.user_weixin,
    	          d.school_name,d.major_name,d.grade_name,d.course_name,d.exam_no,
    	          x.urgency_contact,x.urgency_contact_mobile,x.company';

    	$rs = $this
    			->alias('u')
    			->join($join)
                ->field($field)
    			->where($where)
                ->group('u.userId')
    			->order('u.lastmodify desc')
				->select();
    	
    	if($rs){
    		$xlsData = $rs;
    	}else{
    		die("<span>缺少导出数据,导出Excel失败</span><button type='button' class='btn' onclick='javascript:history.go(-1)'>返&nbsp;回</button>");
    		//$xlsData = [];
    	}
        $xlsName  = "Users";
   /*      $xlsCell  = array(
            array('idcard','身份证号码'),
            array('student_no','学员编号'),
            array('trueName','姓名'),
            array('study_status','学习状态'),
            array('userPhone','联系电话'),
            array('userQQ','QQ'),
            array('user_weixin','微信号'),
            array('userEmail','邮箱'),
            array('urgency_contact','紧急联系人'),
            array('urgency_contact_mobile','紧急联系电话'),
            array('company','工作单位'),
        ); */
        
        $xlsCell  = array(
            array('school_name','学校名称'),
            array('major_name','专业名称'),
            array('grade_name','年级'),
            array('course_name','课程名称'),
            array('student_no','学员编号'),
            array('trueName','姓名'),
            array('idcard','身份证号码'),
            array('exam_no','准考证号码'),
            array('study_status','学习状态'),
            array('userPhone','联系电话'),
            array('userQQ','QQ'),
            array('user_weixin','微信号'),
            array('userEmail','邮箱'),
            array('urgency_contact','紧急联系人'),
            array('urgency_contact_mobile','紧急联系电话'),
            array('company','工作单位'),
        );
        
        foreach ($xlsData as $k => $v)
        {
            $xlsData[$k]['idcard']=" ".$v['idcard'];
            $xlsData[$k]['userPhone']=" ".$v['userPhone'];       
            $xlsData[$k]['urgency_contact_mobile']=" ".$v['urgency_contact_mobile'];       
            $xlsData[$k]['study_status']=$this->get_study_status($v['study_status']);
        }
        //dump($xlsData);die;
        $this->expExcel($xlsName,$xlsCell,$xlsData);
         
    }

    public function expExcel($expTitle,$expCellName,$expTableData){
        import('phpexcel.PHPExcel');
        import('phpexcel.PHPExcel.IOFactory');
        import('phpexcel.PHPExcel.Style.Alignment');
        import('phpexcel.PHPExcel_Cell_DataType');
        $objPHPExcel = new \PHPExcel();
        $xlsTitle = iconv('utf-8', 'gb2312', $expTitle);//文件名称
        $fileName = $xlsTitle.date('_YmdHis').'.xlsx';//or $xlsTitle 文件名称可根据自己情况设定
        $cellNum = count($expCellName);
        $dataNum = count($expTableData);
        $cellName = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ');
        //Excel表格样式设置区  ****** start  **********************************************************
        /*
        $styleArray1 = array(
		  'font' => array(
		    'bold' => true,
		    'size'=>12,
		    'color'=>array(
		      'argb' => '00000000',
		    ),
		    'alignment' => array(
    		'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
    		'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER,
  			),
		  ),
		);
		
		$objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArray1); */
        //$objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(12); //设置表格默认列宽(全部)
        //$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true); //设置某一列自适应宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(22); //设置某一列宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(16); //设置某一列宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(12); //设置某一列宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(12); //设置某一列宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(14); //设置某一列宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(14); //设置某一列宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(14); //设置某一列宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(32); //设置某一列宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(12); //设置某一列宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(14); //设置某一列宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(16); //设置某一列宽度
        //$objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(20);//设置表格默认行高(全部)
        $objPHPExcel->getActiveSheet()->getDefaultStyle()->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER); //设置水平居中
        $objPHPExcel->getActiveSheet()->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER); //设置垂直居中
        $objPHPExcel->getActiveSheet()->getStyle('A')->getNumberFormat()
    	->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_TEXT);

        $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true); //设置字体加粗
        $objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('C1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('D1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('E1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('F1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('G1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('H1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('I1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('J1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('K1')->getFont()->setBold(true);

        //$objPHPExcel->getActiveSheet()->freezePaneByColumnAndRow(9,2); //冻结单元格
    	$objPHPExcel->getActiveSheet()->freezePane('A1');
    	$objPHPExcel->getActiveSheet()->freezePane('B1');
    	$objPHPExcel->getActiveSheet()->freezePane('C1');
    	$objPHPExcel->getActiveSheet()->freezePane('D1');
    	$objPHPExcel->getActiveSheet()->freezePane('E1');
    	$objPHPExcel->getActiveSheet()->freezePane('F1');
    	$objPHPExcel->getActiveSheet()->freezePane('G1');
    	$objPHPExcel->getActiveSheet()->freezePane('H1');
    	$objPHPExcel->getActiveSheet()->freezePane('I1');
    	$objPHPExcel->getActiveSheet()->freezePane('J1');
    	$objPHPExcel->getActiveSheet()->freezePane('K1');

        //Excel表格样式设置区  ****** end  ************************************************************
        for($i=0;$i<$cellNum;$i++){
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellName[$i].'1', $expCellName[$i][1]); 
        }  
        for($i=0;$i<$dataNum;$i++){
          for($j=0;$j<$cellNum;$j++){
            $objPHPExcel->getActiveSheet(0)->setCellValue($cellName[$j].($i+2), $expTableData[$i][$expCellName[$j][0]]);
          }             
        }
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        /*
        header('Content-type:application/vnd.ms-excel;charset=utf-8;name="'.$xlsTitle.'.xlsx"');
        header("Content-Disposition:attachment;filename=$fileName");//attachment新窗口打印inline本窗口打印
        header('Cache-Control: max-age=0');

        header("Content-Type: application/force-download"); 
   		header("Content-Type: application/octet-stream"); 
   		header("Content-Type: application/download");  
   		header("Content-Transfer-Encoding: binary"); 
   		header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
   		header("Pragma: no-cache"); 
        
        $objWriter->save('php://output');exit;  //输出到浏览器下载
        */
       	$path = TEMP_PATH;
        $path_file = TEMP_PATH."$fileName";
        $objWriter->save($path_file); //保存到临时文件目录
        $url = 'InfoDownload?path='.$path.'&file='.$fileName;
        $html = 
        "<span>Excel文件导出成功</span><a href=".$url.">立即下载</a><button type='button' class='btn' onclick='javascript:history.go(-1)'>返&nbsp;回</button>";
        die("$html");
        
    }
}
