<?php
namespace application\admin\model;
/**
 * 试听业务处理
 */
use think\Db;
class StudentAudition extends Base{
	/**
	 * 分页
	 */
	public function pageQuery(){
		$where = [];
		
		//$start = strtotime(input('get.start'));
		//$end = strtotime(input('get.end'));
		$course_id = input('get.course_id');
		$subject_id = input('get.subject_id');
		$status = input('get.status');
		//if(!empty($start) && !empty($end)){
		//	$where['endtime'] = ['between',["$start","$end"]];
		//}
		if(!empty($course_id)){
			$where['course_id'] = ['=',"$course_id"];
		}
		if(!empty($subject_id)){
			$where['subject_id'] = ['=',"$subject_id"];
		}
		if(!empty($status)){
			$where['status'] = ['=',"$status"];
		}
		
        $page = $this->where($where)->field('*')->order('sa_id desc')
		->paginate(input('post.pagesize/d'))->toArray();
		
		if(count($page['Rows'])>0){
			foreach($page['Rows'] as $key => $v){
				
				$page['Rows'][$key]['name'] = $v['name'] == '' ? $v['name'] = '未选择': $v['name'];
				$page['Rows'][$key]['course_bn'] = $v['course_bn'] == '' ? $v['course_bn'] = '无': $v['course_bn'];
				$page['Rows'][$key]['status'] = $this->get_status($v['status']);
				$page['Rows'][$key]['subject_id'] = $v['subject_id'] == 0 ? $v['subject_id'] = '未选择' : $this->get_subject_name($v['subject_id']);
				$page['Rows'][$key]['userId'] = $v['userId'] == 0 ? $v['userId'] = '非会员' : $this->get_users_name($v['userId']);
				$page['Rows'][$key]['ey_userId'] = $v['ey_userId'] == 0 ? $v['ey_userId'] = '未选择' : $this->get_employee_name($v['ey_userId']);
				$page['Rows'][$key]['username'] = $v['username'] == '' ? $v['username'] = '会员' : $v['username'];
				$page['Rows'][$key]['campus_id'] = $this->get_campus($v['campus_id']);
			}
		}
        return $page;
	}
	public function getById($id){
		if($id == ''){
			$info = $this->get(['sa_id'=>$id]);
			//$info['course_name'] = '';
		}else{
			$info = $this->get(['sa_id'=>$id]);
			//$info['course_name'] = '';
			//$info['endtime'] = ITSTime2Date($info['endtime']);
			//if(isset($info['course_id'])) $info['course_name'] = $this->get_course_name($info['course_id']);
		}
		//dump($info);die;
		return $info;
	}
	/**
	 * 新增
	 */
	public function add(){
		$data = input('post.');
		//dump($data);die;
		$data['createtime'] = time();
        $data['lastmodify'] = time();
        MBISUnset($data,'sa_id,choice,member_choice');
        //dump($data);die;
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
		//$id = (int)input('post.id');
		$data = input('post.');
		if(isset($data['sa_id']))$id = $data['sa_id'];
        $data['lastmodify'] = time();
		MBISUnset($data,'sa_id,choice,member_choice');
		Db::startTrans();
		try{
		    $result = $this->save($data,['sa_id'=>$id]);
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
		    $result = $this->where(['sa_id'=>$id])->delete();
	        if(false !== $result){
	        	Db::commit();
	        	return MBISReturn("删除成功", 1);
	        }
		}catch (\Exception $e) {
            Db::rollback();
            return MBISReturn('删除失败',-1);
        }
	}
	public function get_major_list(){
		return Db::name('major')->where('type_id',2)->field('major_id,name')->select();
	}
	public function get_course_list(){
        return Db::name('course')->where('type_id',2)->field('course_id,name')->select();
    }
    public function get_subject_list(){
    	return Db::name('subject')->where('subject_type_id',2)->field('subject_id,name')->select();
    }
    //public function get_employee_list(){
    //	return Db::name('users')->where('userType',2)->field('trueName')->select();
    //}
    public function get_employee_list(){
    	return Db::name('employee')->field('employee_id,employee_no,name')->select();
    } 
	public function get_user_list(){
		$where = [];
		$where['userType'] = 0;
		$where['dataFlag'] = 1;
		//$where['u.userType'] = 0;
		//$where['orderStatus'] = 0;
		return  Db::name('users')
						//->alias('u')
						//->join('student_extend e')
						//->join('orders o','u.userId = o.userId')
						->where($where)
						->field('userId,trueName')
						//->field('u.userId,u.trueName,e.student_no')
						//->field('u.userId,u.trueName,o.totalMoney,o.orderId,o.orderNo')
						->order('convert(trueName using gb2312) asc') //姓名首字母排序
						->select();
	}
    /**
     * 会员姓名
     */
    public function get_users_name($id=0){
    	return Db::name('users')->where('userId',$id)->value('trueName');
    }
    public function get_employee_name($id=0){
    	return Db::name('employee')->where('employee_id',$id)->value('name');
    }
	public function get_status($status){
		switch($status){
			case 1:return '待审核';
			case 2:return '审核通过';
			case 3:return '审核不通过';
		}
	}
	public function get_campus($id=0){
		switch($id){
			case 1:return '龙岗校区';
			case 2:return '福田校区';
			case 3:return '宝安校区';
			case 4:return '南山校区';
		}
	}
	public function get_course_name($id=0){
		return Db::name('course')->where('course_id',$id)->value('name');
	}
	public function get_subject_name($id=0){
		return Db::name('subject')->where('subject_id',$id)->value('name');
	}

	public function getemployeeInfo(){
		$employee_id = (int)input('post.employee_id');
		$info = Db::name('employee')->where('employee_id',$employee_id)->field('employee_no,name')->find();
		if($info){
			return ['data'=>$info,'status'=>1];
		}else{
			return ['msg'=>'未找到 ^_^ ','status'=>-1];
		}
	}
	public function getcoursesubjectInfo(){
		$major_id = (int)input('post.major_id');
		$info = [];
		$info['course'] = Db::name('course')->where('major_id',$major_id)->field('course_id,name,course_bn')->select();
		$info['subject'] = Db::name('subject')->where('major_id',$major_id)->field('subject_id,name')->select();
		//dump($info);die;
		if($info){
			return ['data'=>$info,'status'=>1];
		}else{
			return ['msg'=>'未找到 ^_^ ','status'=>-1];
		}
	}

	public function getcourseInfo(){
		$course_id = (int)input('post.course_id');
		$info = Db::name('course')->where('course_id',$course_id)->field('name,course_bn')->find();
		//dump($info);die;
		if($info){
			return ['data'=>$info,'status'=>1];
		}else{
			return ['msg'=>'未找到 ^_^ ','status'=>-1];
		}
	}

}
