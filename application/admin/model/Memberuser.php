<?php
namespace application\admin\model;
use think\Db;
use application\admin\model\Users as Member;
/**
 * 会员业务处理
 */
class Memberuser extends Base{
	/**
	 * 分页
	 */
	public function pageQueryM(){
		$User = new Member();
		$where = [];
		$where['u.dataFlag'] = 1;   
		$where['u.userType'] = 0; 	//学员类型
		$where['u.uidType'] = 3;	//会员
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
		/*
		$start = strtotime(input('get.start'));
		$end = strtotime(input('get.end'));
		if(!empty($start) && !empty($end)){
			$where['u.createtime'] = ['between',["$start","$end"]];
		}
		*/
		
		/*
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
		*/
		$join = [];
		$join = [
			['student_extend x','u.userId=x.userId','left'],
			['student_edu e','u.userId=e.userId','left'],
			['student_skill s','u.userId=s.userId','left']
		];

		$field = 'u.userId,u.loginName,u.trueName,u.userSex,u.idcard,u.student_no,u.study_status,u.userPhoto,u.userPhone,u.userQQ,u.userEmail,u.user_weixin,u.nation,x.company,x.urgency_contact,x.urgency_contact_mobile';


		$rs = $User->alias('u')
				->join($join)
				->where($where)
				->field($field)
				->order('u.lastmodify desc')
				->paginate(input('pagesize/d'))
				->toArray();
		//getLastSql();
		
        if(count($rs['Rows'])>0){
			foreach ($rs['Rows'] as $key => $v){
                $rs['Rows'][$key]['study_status'] = $this->get_study_status($v['study_status']);
			}
		}
		
		return $rs;
	}

	public function getMemberInfo($id,$key=''){
		$User = new Member();
		$where = [];
		$where['u.userId'] = ['=',"$id"];

		$field = 'u.userId,u.trueName,u.loginName,u.userSex,u.idcard,u.student_no,u.study_status,u.userPhoto,u.userPhone,u.userQQ,u.userEmail,u.user_weixin,u.nation,x.company,x.address,x.urgency_contact,x.urgency_contact_mobile';

		$join = [];
		$join = [
			['student_extend x','u.userId=x.userId','left'],
			//['student_edu e','u.userId=e.userId','left'],
			//['student_skill s','u.userId=s.userId','left']
		];

		$res = $User
				->alias('u')
				->join($join)
				->where($where)
				->field($field)
				->find();
		if($key == 'look'){
			$res['userSex'] = $this->getSex($res['userSex']);
			$res['study_status'] =$this->get_study_status($res['study_status']);
		}
		return $res;
	}
	

	public function editMember(){
		$Users = new Member();
    	$data = input('post.');
    	$userId = $data['userId'];
    	$user = $data['user'];
    	$extend = $data['extend'];
    	Db::startTrans();
		try{
			if(isset($user['userPhoto'])){
			    MBISUseImages(1, $userId, $user['userPhoto'], 'users', 'userPhoto');
			}
		    $result = $Users->allowField(true)->save($user,['userId'=>$userId]);
	        if(false !== $result){
                if(isset($data['extend']))
                {
                    model('studentExtend')->save($extend,['userId'=>$userId]);
                }
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
    public function delMember(){
    	$Users = new Member();
	    $id = (int)input('post.id');
	    Db::startTrans();
	    try{
		    $data = [];
			$data['dataFlag'] = -1;
		    $result = $Users->update($data,['userId'=>$id]);
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
            return MBISReturn('删除失败',-1);
        }
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

    	$where['m.exam_type'] = ['=',"$exam_type"];

    	$join = [];
    	$join = [
    		['student_extend x','u.userId=x.userId','left'],
    		['student_edu d','u.userId=d.userId','left'],
  			['major_edu m','m.major_id=d.major_id','left']
    	];
    	$field = 'u.userId,u.trueName,u.userQQ,u.idcard,u.student_no,u.userPhone,u.userEmail,u.study_status,u.user_weixin,x.urgency_contact,x.urgency_contact_mobile,x.company';

    	$page = $this
    			->alias('u')
    			->join($join)
    			->where($where)
    			->field($field)
    			->order('u.lastmodify desc')
				->paginate(input('pagesize/d'))
				->toArray();
		//getLastSql();
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
    	$where['m.exam_type'] = ['=',"$exam_type"];
    	$where['u.userId']    = ['=',"$id"];

    	$join = [];
    	$join = [
    		['student_extend x','u.userId=x.userId','left'],
    		['student_edu d','u.userId=d.userId','left'],
  			['school s','s.school_id=d.school_id','left'],
  			['major_edu m','m.major_id=d.major_id','left'],
  			['grade g','u.grade_id=g.grade_id','left']
    	];

    	$field = 'u.userId,u.trueName,u.userQQ,u.userPhone,u.userEmail,u.student_no,u.pre_entry_no,u.study_status,u.idcard,u.userSex,u.user_weixin,u.nation,u.culture_method,u.education_level,u.graduate_colleges,u.colleges_number,u.graduate_date,u.certificate_number,u.idcard_Photo,u.identification_photo,u.brfore_certificate_photo,u.after_certificate_photo,u.grade_id,x.address,x.company,x.urgency_contact,x.urgency_contact_mobile,d.school_id,d.major_id,d.level_id,s.name as school_name,m.name as major_name,g.name as grade_name';
    	$res = $this
    			->alias('u')
    			->join($join)
    			->where($where)
    			->field($field)
    			->find();

    	$res['graduate_date'] = $this->timeToDate($res['graduate_date']);

    	if($Tokey == 'look'){
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
    	$user = $data['user'];
    	$extend = $data['extend'];
    	$edu = $data['edu'];
    	$user['graduate_date'] = $user['graduate_date'] ? strtotime($user['graduate_date']) : time();
    	Db::startTrans();
		try{
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
    	$where = [];
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

    	$where['m.exam_type'] = ['=',"$exam_type"];

    	$join = [];
    	$join = [
    		['student_extend x','u.userId=x.userId','left'],
    		['student_edu d','u.userId=d.userId','left'],
  			['major_edu m','m.major_id=d.major_id','left']
    	];
    	$field = 'u.userId,u.trueName,u.userQQ,u.idcard,u.student_no,u.userPhone,u.userEmail,u.study_status,u.user_weixin,x.urgency_contact,x.urgency_contact_mobile,x.company';

    	$rs = $this
    			->alias('u')
    			->join($join)
    			->where($where)
    			->field($field)
    			->order('u.lastmodify desc')
				->select();
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
        $objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(20);//设置表格默认行高(全部)
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
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
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
