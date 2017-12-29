<?php
namespace application\admin\model;
/**
 * 学员/老师考勤业务处理
 */
use think\Db;
class CurrentCkwork extends Base{
	/**
	 * 分页
	 */
	public function pageQuery(){

		$where = [];
		$type_id = input('get.type_id');
		if ($type_id == 1) {
			$where['u.student_type'] = ['in',[2,3]];
		}else{	
			$where['u.student_type'] = ['in',[1,3]]; 
		}
		$start = strtotime(input('get.start'));
		$end = strtotime(input('get.end'));
		$userNo = input('get.userNo');
		$trueName = input('get.trueName');
		$objectId = input('get.object_id');
		$classId = input('get.class_id');
		if(!empty($start) && !empty($end)){
			$where['c.createtime'] = ['between',["$start","$end"]];
		}
		if(!empty($userNo)){
			$where['c.user_no'] = ['like',"%$userNo%"];
		}
		if(!empty($trueName)){
			$where['u.trueName'] = ['like',"%$trueName%"];
		}
		if(!empty($objectId)){
			$where['c.object_id'] = ['=',"$objectId"];
		}
		if(!empty($classId)){
			$where['c.class_id'] = ['=',"$classId"];
		}
        $page = $this->alias('c')
        			 ->join('users u','c.userId=u.userId')
        			 ->where($where)
        			 ->field('c.*,u.trueName')
        			 ->order('c.lastmodify desc')
					 ->paginate(input('post.pagesize/d'))
					 ->toArray();
		
		if(count($page['Rows'])>0){
			foreach($page['Rows'] as $key => $v){				
				if(isset($page['Rows'][$key]['object_id'])){
					$page['Rows'][$key]['object_id'] = $this->get_course_name($v['object_id']);
				}
				$page['Rows'][$key]['user_no'] = $v['user_no']?$v['user_no']:'暂无编号';
				$page['Rows'][$key]['createtime'] = $this->time_date($v['createtime']);
				$page['Rows'][$key]['ckwork_type'] = $this->check_type($v['ckwork_type']);
			}
		}
        return $page;
	}

	public function pageQueryT(){
        $key = input('get.key');
        $where = [];
        $where['userType'] = 1;
        $where['dataFlag'] = 1;
		if($key!='')$where['user_no'] = ['like','%'.$key.'%'];
        $page = $this->alias('c')->join('users u','c.userId=u.userId')->where($where)->field('c.*,u.trueName')->order('lastmodify desc')
		->paginate(input('post.pagesize/d'))->toArray();
		
		if(count($page['Rows'])>0){
			foreach($page['Rows'] as $key => $v){
				
				if(isset($page['Rows'][$key]['object_id'])){
					$page['Rows'][$key]['object_id'] = $this->get_subject_name($v['object_id']);
				}
				$page['Rows'][$key]['createtime'] = $this->time_date($v['createtime']);
				$page['Rows'][$key]['ckwork_type'] = $this->check_type($v['ckwork_type']);
			}
		}
		
        return $page;
	}

	public function getById($id){
		$rs = $this->get(['cc_id'=>$id]);
		if($id>0){
			$rs['createtime'] = $this->time_date($rs['createtime']);
		}
		return $rs;
	}
	/**
	 * 新增
	 */
	public function add(){
		$data = input('post.');
		//dd($data);
		$data['createtime'] = strtotime($data['createtime']);
        $data['lastmodify'] = time();
        $data['user_no'] = $this->get_student_no($data['userId']);
        MBISUnset($data,'id');
		Db::startTrans();
		try{
			$result = $this->save($data);
	        if(false !== $result){
			    Db::commit();
	        	return MBISReturn("新增成功", 1);
	        }
		}catch (\Exception $e) {
            Db::rollback();
        }  
        return MBISReturn('新增失败',-1);
	}

	public function addt(){
		$data = input('post.');
		$data['createtime'] = strtotime($data['createtime']);
        $data['lastmodify'] = time();
        $data['userId'] = $this->get_tid($data['user_no']);
        //$data['settlement_time'] = strtotime(input('post.startDate'));
		//MBISUnset($data,'startDate');
        MBISUnset($data,'id');
		Db::startTrans();
		try{
			$result = $this->save($data);
	        if(false !== $result){
			    Db::commit();
	        	return MBISReturn("新增成功", 1);
	        }
		}catch (\Exception $e) {
            Db::rollback();
        }  
        return MBISReturn('新增失败',-1);
	}

    /**
	 * 编辑
	 */
	public function edit(){
		$id = (int)input('post.id');
		$data = input('post.');
		$data['createtime'] = strtotime($data['createtime']);
        $data['lastmodify'] = time();
        //$data['settlement_time'] = strtotime(input('post.startDate'));
        //MBISUnset($data,'startDate');
		MBISUnset($data,'id');
		Db::startTrans();
		try{
		    $result = $this->save($data,['cc_id'=>$id]);
	        if(false !== $result){
	        	Db::commit();
	        	return MBISReturn("编辑成功", 1);
	        }
	    }catch (\Exception $e) {
            Db::rollback();
        }
        return MBISReturn('编辑失败',-1);  
	}

	public function editt(){
		$id = (int)input('post.id');
		$data = input('post.');
		$data['createtime'] = strtotime($data['createtime']);
        $data['lastmodify'] = time();
        //$data['settlement_time'] = strtotime(input('post.startDate'));
        //MBISUnset($data,'startDate');
		MBISUnset($data,'id');
		Db::startTrans();
		try{
		    $result = $this->save($data,['cc_id'=>$id]);
	        if(false !== $result){
	        	Db::commit();
	        	return MBISReturn("编辑成功", 1);
	        }
	    }catch (\Exception $e) {
            Db::rollback();
        }
        return MBISReturn('编辑失败',-1);  
	}

	/**
	 * 删除
	 */
    public function del(){
	    $id = input('post.id/d');
	    Db::startTrans();
		try{
		    $result = $this->where(['cc_id'=>$id])->delete();
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
	 * 考勤记录列表
	 */
	public function get_info_list(){
		$info = Db::name('current_ckwork')->alias('c')->join('users u','c.userId=u.userId')->field('c.*,u.trueName')->where('userType',0)->select();
		return $info;
	}

	public function get_info_listt(){
		$info = Db::name('current_ckwork')->alias('c')->join('users u','c.userId=u.userId')->field('c.*,u.trueName')->where('userType',1)->select();
		return $info;
	}

	public function get_teacher_list(){
		$wehre = [];
		$where['userType'] = 1;
		$where['dataFlag'] = 1;
		$teacher = Db::name('users')->alias('u')->join('tc_extend t','u.userId = t.userId')->field('u.trueName,t.tc_no')->where($where)->order('convert(trueName using gb2312) asc')->select();
		return $teacher;
	}
	/**
	 * 学员列表
	 */
	public function get_student_list($type_id){
		$wehre = [];
		if($type_id == 1){
			$where['student_type'] = ['in',[2,3]];
		}else{
			$where['student_type'] = ['in',[1,3]];
		}
		$where['userType'] = 0;
		$where['dataFlag'] = 1;
        $student = Db::name('users')->field('trueName,student_no,userId')->where($where)->order('convert(trueName using gb2312) asc')->select();
        return $student;
    }
    /**
     * 岗位列表
     */
    public function get_employeetype_list(){
    	$employeetype = Db::name('EmployeeType');
    	return $employeetype->field('*')->select();
    }
    /**
     * 校区列表
     */
    public function get_businesscenter_list(){
    	$businesscenter = Db::name('BusinessCenter');
    	return $businesscenter->field('*')->select();
    }

    /**
     * 科目列表
     */
    public function get_subject_list(){
    	$subject = Db::name('subject');
    	return $subject->field('*')->select();
    }
    /**
     * 科目名称
     */
    public function get_subject_name($id=0){
    	$subject = Db::name('subject');
    	return $subject->where('subject_id',$id)->value('name');
    }

    /**
     * 合作方名称
     */
    public function get_partners_name($id=0){
    	$department = Db::name('partners');
    	return $department->where('p_id',$id)->value('name');
    }
    /**
     * 课程名称
     */
    public function get_course_name($id=0){
    	$course = Db::name('course');
    	return $course->where('course_id',$id)->value('name');
    }
    public function get_course_id($name){
    	$course = Db::name('course');
    	return $course->wehere('name',$name)->value('course_id');
    }
    /**
     * 校区名称
     */
    public function get_businesscenter_name($id=0){
    	$businesscenter = Db::name('business_center');
    	return $businesscenter->where('business_center_id',$id)->value('name');
    }
    /**
	 * 课程列表
	 */
    public function get_course_list($type_id){
    	$course = Db::name('course')->where('type_id',$type_id)->field('course_id,name')->select();
        return $course;
	}

	public function get_settlement_type($type=0){
		switch($type){
			case 1:
				return '管理费';
				break;
			case 2:
				return '统考费';
				break;
			case 3:
				return '实践报考费';
				break;
			default :
				return '未知';
		}
	}
	public function get_pay_type($type=0){
		switch($type){
			case 1:
				return '现金支付';
				break;
			case 2:
				return '银行转账';
				break;
			case 3:
				return '微信支付';
				break;
			case 4:
				return '支付宝支付';
				break;
			case 5:
				return '支票/汇票';
				break;
			default :
				return '未知';
		}
	}
	public function time_date($time){
		return date('Y-m-d',$time);
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


	/**
	 * 获取学员信息
	 */
	public function search(){
		$student_no = input('post.student_no');
		$userId = $this->get_userId($student_no); //获取学员ID
		if(!empty($userId)){
			$info = [];
			$user = Db::name('users')->where('userId',$userId)->field('trueName')->find();
			//$extend = Db::name('student_extend')->where('userId',$userId)->field('*')->find();
			$edu = Db::name('student_edu')->where('userId',$userId)->field('grade,course_id,major_id')->find();  //学历报名信息
			$skill = Db::name('student_skill')->where('userId',$userId)->field('course_id,major_id')->find();  //技能报名信息
			if(false !== $edu){
				$info['e_course_name'] = $this->get_course_name($edu['course_id']);
				$info['e_course_id'] = $edu['course_id'];
			}
			if(false !== $skill){
				$info['k_course_name'] = $this->get_course_name($skill['course_id']);
				$info['k_course_id'] = $skill['course_id'];
			}
			//$order = Db::name('orders')->where('userId',$userId)->field('*')->find();
			if(empty($edu) && empty($skill)){
				return ['msg'=>'该学员没有报名任何课程','status'=>0];
			}
			$info['trueName'] = $user['trueName'];
			return ['trueName'=>$info['trueName'],'e_course_name'=>$info['e_course_name'],'e_course_id'=>$info['e_course_id'],'k_course_name'=>$info['k_course_name'],'k_course_id'=>$info['k_course_id'],'status'=>1];
		}else{
			return ['msg'=>'编号错误','status'=>-1];
		} 
	}

	public function get_userId($no){
		return Db::name('users')->where('student_no',$no)->value('userId');
	}

	public function get_tid($no){
		return Db::name('tc_extend')->where('tc_no',$no)->value('userId');
	}
	public function get_student_no($userId){
		return Db::name('users')->where('userId',$userId)->value('student_no');
	}

}
