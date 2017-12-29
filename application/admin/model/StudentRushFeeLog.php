<?php
namespace application\admin\model;
/**
 * 学员付费记录业务处理
 */
use think\Db;
class StudentRushFeeLog extends Base{
	/**
	 * 分页
	 */
	public function pageQuery(){
		$where = [];
		
		$start = strtotime(input('get.start'));
		$end = strtotime(input('get.end'));
		$fush_fee_no = input('get.fush_fee_no');
		$student_no = input('get.student_no');
		$unpaid_fee = input('get.unpaid_fee');
		if(!empty($start) && !empty($end)){
			$where['endtime'] = ['between',["$start","$end"]];
		}
		if(!empty($fush_fee_no)){
			$where['fush_fee_no'] = ['=',"$fush_fee_no"];
		}
		if(!empty($student_no)){
			$where['student_no'] = ['=',"$student_no"];
		}
		if(!empty($unpaid_fee)){
			$where['unpaid_fee'] = ['=',"$unpaid_fee"];
		}
		
        $page = $this->where($where)->field('*')->order('rush_fee_id desc')
		->paginate(input('post.pagesize/d'))->toArray();
		
		if(count($page['Rows'])>0){
			foreach($page['Rows'] as $key => $v){
				$page['Rows'][$key]['course_id'] = $this->get_course_name($v['course_id']);
				$page['Rows'][$key]['status'] = $this->get_status($v['status']);
				$page['Rows'][$key]['notice_tmpl_id'] = $this->get_tmpl_title($v['notice_tmpl_id']);
				$page['Rows'][$key]['endtime'] = ITSTime2Date($v['endtime']);
				$page['Rows'][$key]['userId'] = $this->get_users_name($v['userId']);
			}
		}
        return $page;
	}
	public function getById($id){
		if($id == ''){
			$info = $this->get(['rush_fee_id'=>$id]);
			//$info['course_name'] = '';
		}else{
			$info = $this->get(['rush_fee_id'=>$id]);
			$info['course_name'] = '';
			$info['endtime'] = ITSTime2Date($info['endtime']);
			if(isset($info['course_id'])) $info['course_name'] = $this->get_course_name($info['course_id']);
		}
		return $info;
	}
	/**
	 * 新增
	 */
	public function add(){
		$data = input('post.');
		$data['createtime'] = time();
        $data['lastmodify'] = time();
        $data['endtime'] = strtotime($data['endtime']);
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
        $data['lastmodify'] = time();
        $data['endtime'] = strtotime($data['endtime']);
		MBISUnset($data,'id');
		Db::startTrans();
		try{
		    $result = $this->save($data,['rush_fee_id'=>$id]);
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
		    $result = $this->where(['rush_fee_id'=>$id])->delete();
	        if(false !== $result){
	        	Db::commit();
	        	return MBISReturn("删除成功", 1);
	        }
		}catch (\Exception $e) {
            Db::rollback();
            return MBISReturn('删除失败',-1);
        }
	}

	public function get_noticetmpl_list(){
        $noticetmpl = Db::name('notice_tmpl');
        return $noticetmpl->field('*')->select();
    }

    public function chooseTmpl(){
    	$notice_id = input('post.notice_id');
    	$noticetmpl = Db::name('notice_tmpl');
    	if($notice_id ==''){
    		return ['msg'=>'请选择模板','status'=>0];
    	}
	    $result = $noticetmpl->where('notice_id',$notice_id)->field('content')->find();
	    if($result){
	    	return ['content'=>$result['content'],'status'=>1];
	    }else{
	    	return ['msg'=>'数据错误','sataus'=>-1];
	    }
    }

	/**
	 * 学员付费明细记录列表
	 */
	public function get_info_list(){
		$info = Db::name('student_fee_log')->field('*')->select();
		return $info;
	}
	/**
	 * 会员列表
	 */
	public function get_users_list(){
		$where = [];
		$where['userType'] = 0;
		$where['dataFlag'] = 1;
        $users = Db::name('users');
        return $users->field('*')->where($where)->order('convert(trueName using gb2312) asc')->select();
    }

    /**
	 * 获取所有学员信息 (待完善) 目前只查询有报名信息的学员
	 */
	public function get_user_lists(){
		$where = [];
		$where['userType'] = 0;
		$where['dataFlag'] = 1;
		//$where['u.userType'] = 0;
		//$where['orderStatus'] = 0;
		return $userlist = Db::name('users')
							//->alias('u')
							//->join('student_extend e')
							//->join('orders o','u.userId = o.userId')
							->where($where)
							->field('userId,trueName')
							//->field('u.userId,u.trueName,e.student_no')
							//->field('u.userId,u.trueName,o.totalMoney,o.orderId,o.orderNo')
							->order('convert(trueName using gb2312) asc')
							->select();
	}
    /**
     * 会员姓名
     */
    public function get_users_name($id=0){
    	return Db::name('users')->where('userId',$id)->value('trueName');
    }
	public function get_status($status){
		switch($status){
			case 1:return '定金学员';
			case 2:return '补缴学费';
		}
	}
	public function get_course_name($id=0){
		return Db::name('course')->where('course_id',$id)->value('name');
	}
	public function get_tmpl_title($id=0){
		return Db::name('notice_tmpl')->where('notice_id',$id)->value('title');
	}

	public function getInfo(){
		$userId = (int)input('post.userId');
		$where = [];
		$where['u.userId'] = $userId;
		$info = Db::name('users')
				->alias('u')
				->join('order_detail o','u.userId=o.userId','left')
				->where($where)
				->field('u.student_no,u.pre_entry_no,o.orderNo,o.course_id,o.course_name')
				->select();
		//$extend = Db::name('student_extend')->where($where)->value('student_no');
		//$order = Db::name('orders')->where($where)->field('orderNo,totalMoney')->find();
		//dump($info);die;
		foreach($info as &$v){
			$v['student_no'] = (!empty($v['student_no'])) ? $v['student_no'] : $v['pre_entry_no'];
			if($v['orderNo'] != ''){
				return ['data' => $info,'status' => 1];
			}else{
				return ['data' => $info,'msg' => '找不到订单信息','status' => -1];
			}
		}
	}

}
