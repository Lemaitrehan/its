<?php
namespace application\admin\model;
/**
 * 学员付费记录业务处理
 */
use think\Db;
class StudentFeeLog extends Base{
    
    protected  $arr_fee_class = array(
        '1'=>'培训费',
        '2'=>'证书费',
        '3'=>'报考费',
        '4'=>'学位费',
    );
    protected  $arr_fee_type = array(
        '1'=>'一次性收费',
        '2'=>'定金',
        '3'=>'补费',
    );
    //订单分期
    /**
     * @param unknown $order_id  订单id
     * @param unknown orderType  订单类型：1=全额订单，2=定金订单
     * 
     * depositMoney //定金
     * depositAddMoney//追加定金
     * full_pay_price//全款金额
     * notfull_deal_price、、订单非全款应付金额(打折)
     * pay_type 支付类型
     * depositRemainMoney//剩余付款金额
     */
    public function  installment($arrOrder){
        //订单类型：1=全额订单，2=定金订单
        $time = time();
        if( $arrOrder['orderType'] == '1' ){
             $data[] =  array(
                 'userId'      => $arrOrder['userId'],
                 'orderId'     => $arrOrder['orderId'],
                 'orderNo'     => $arrOrder['orderNo'],
                 'entry_id'    => '',//报名表
                 'fee_class'   => '1',
                 'fee_type'    => '1',//收费类型：1=一次性收款，2=定金，3补费
                 'name'        => '培训费',//项目名称
                 'pay_type'    => $arrOrder['pay_type'],//支付类型
                 'real_amount' => $arrOrder['full_pay_price'],//全款金额
                 'pay_time'    => $time,
                 'createtime'  => $time,
                 'lastmodify'  => $time,
             );
            
        }else{
            //非全款  应付多少钱( 全部算定金 )
            $dj =   $arrOrder['notfull_deal_price'] - $arrOrder['depositRemainMoney'] ;
            //存定金    
            $data[] =  array(
                'userId'      => $arrOrder['userId'],
                'orderId'     => $arrOrder['orderId'],
                'orderNo'     => $arrOrder['orderNo'],
                'entry_id'    => '',//报名表
                'fee_class'   => '1',
                'fee_type'    => '2',//收费类型：1=一次性收款，2=定金，3补费
                'name'        => '定金',//项目名称
                'pay_type'    => $arrOrder['pay_type'],//支付类型
                'real_amount' => $dj,//付款金额
                'createtime'  => $time,
                'lastmodify'  => $time,
            );
            //分期
            $one  = round($arrOrder['notfull_deal_price']/3 ,2);
            //current_income
            //Instalment_amount
            for($i=1;$i<=3;$i++){
                
                if( $i == 1){
                    $current_income = $dj;
                }else{
                    $current_income = 0;
                }
                $data[] =  array(
                    'userId'      => $arrOrder['userId'],
                    'orderNo'     => $arrOrder['orderNo'],
                    'entry_id'    => '',//报名表
                    'fee_class'   => '1',
                    'fee_type'    => '2',//收费类型：1=一次性收款，2=定金，3补费
                    'name'        => '第'.$i.'期付款',//项目名称
                    'real_amount'      => '',//定金金额
                    'current_income'   =>$current_income, //本期收入
                    'Instalment_amount'=>$one,//分期付款金额
                    'createtime'  => $time,
                    'lastmodify'  => $time,
                );
            }
        }
        
        $this->saveAll($data);
        
    } 
    
    
	/**
	 * 分页
	 * @$type 学历与技能
	 */
	public function pageQuery($type,$id=""){
	    
	    //订单收费统计
	    if(!$id){
	        $where = array();
	        $student_name = input('student_name');
	        if($student_name){
	            $where['user.trueName']= ['like','%'.$student_name.'%']; 
	        }
    	    $join = array(
    	        array('users user','a.userId = user.userId','left'),
    	    );
    	    $res = db::name('orders')->alias('a')
    	                      ->field('user.userId,`user`.trueName,user.idcard,`user`.student_no,SUM(totalMoney) as total_totalMoney,SUM(realPayMoney) as total_realPayMoney,SUM(totalMoney)-SUM(realPayMoney) as total_noPayMoney')
    	                      ->join($join)
    	                      ->where($where)
    	                      ->group('a.userId')
    	                      //->order('a.orderId DESC')
    	                      ->paginate(input('post.pagesize/d'))
                              ->toArray();
    	    return $res;
	    }
		$where   = [];
		$start   = strtotime(input('get.start'));
		$end     = strtotime(input('get.end'));
		$orderNo = input('get.orderNo');
		$feeNo   = input('get.feeNo');
		$realAmount = input('get.realAmount');
		$student_name = input('get.student_name');
        $arre_type = input('get.arre_type');
        
        //课程类型处理
        $type==1 && $where['type_id'] = 1;
        $type!=1 && $where['type_id'] = ['neq',1];
        
		if(!empty($start) && !empty($end)){
            $type==1 && $where['receipt_time'] = ['between',["$start","$end"]];
			//$type==2 && $where['a.pay_time'] = ['between',["$start","$end"]];
		}
		if($type==2 && !empty($orderNo)){
			$where['a.orderNo'] = ['like',"%$orderNo%"];
		}
		if(!empty($feeNo)){
            $type==1 && $where['receipt_no'] = ['like',"%$feeNo%"];
			//$type==2 && $where['a.fee_no'] = ['like',"%$feeNo%"];
		}
		if($type==2 && !empty($realAmount)){
			//$where['a.real_amount'] = ['=',"$realAmount"];
		}
		if(!empty($student_name)){
            $userId = Db::name('users')->where(['trueName'=>['like',"%$student_name%"]])->value('userId');
            !empty($userId) && $where['userId'] = $userId;
            empty($userId) && $where['userId'] = '-1';
		}
        if($type=='1'){
           //false && $where['s.type_id'] = ['in',"1,3"];
        }else{
           //$type==2 && $where['s.type_id'] = ['in',"2,3"];
        }
        /*
        $type==2 && $page = $this->alias('a')
                     ->join('orders s','a.orderId = s.orderId','LEFT')
                    // ->join('users user','user.userId = a.userId','LEFT')
                     ->where($where)
                     ->field('a.*')
                     ->order('lastmodify desc')
		             ->paginate(input('post.pagesize/d'))
                     ->toArray();
		if($type==2 && count($page['Rows'])>0){
			foreach($page['Rows'] as $key => $v){
				$page['Rows'][$key]['fee_type'] = $this->get_fee_type($v['fee_type']);
				$page['Rows'][$key]['pay_type'] = $this->get_pay_type($v['pay_type']);
				$page['Rows'][$key]['fee_class'] = $this->get_fee_class($v['fee_class']);
				$page['Rows'][$key]['pay_time'] = ($v['pay_time'] !== 0) ? $this->time_date($v['pay_time']) : '未知';
				$page['Rows'][$key]['plan_paytime'] = $this->time_date($v['plan_paytime']);
				$page['Rows'][$key]['entry_time'] = ($v['entry_time'] !==0) ? $this->time_date($v['entry_time']) : '未知';
				//$page['Rows'][$key]['trueName'] = $this->get_users_name($v['userId']);
			}
		}*/
        
        //学历处理
        $exam_type = session('examType');
        $school_id = input('school_id');
        $major_id  = input('major_id');
        $level_id  = input('level_id');
        if($type==1 && $school_id){
           $school_name = db::name('school')->where('school_id='.$school_id)->value('name');
            $where['school_name']= ['=',$school_name];
        }
        if($major_id){
            $where['major_name']= ['=',$major_id];
        }
        if($level_id){
            $where['level_name']= ['=',$level_id];
        }
        if($arre_type){
            $where['arre_type']= ['=',$arre_type];
        }
        
        
        $type==1 && $where['exam_type'] = $exam_type;
        if( isset($_GET['action']) &&  $_GET['action'] == 'fy' ){
            //$type==1 && 
            $page1 = Db::name('student_edu')->where($where)->select();
            $page['Rows'] = $page1;
        }else{
            //$type==1 && 
            $page = Db::name('student_edu')->where($where)->paginate(input('post.pagesize/d'))
            ->toArray();
        }
        //$type==1 && 
        if(count($page['Rows'])>0){
			foreach($page['Rows'] as $key => $v){
                $result_payments = Db::name('payments')->where(['userId'=>$v['userId'],'course_bn'=>$v['course_bn']])->order('receiptDate ASC')->select();
                $page['Rows'][$key]['receiptSchool'] = !empty($result_payments[0]['receiptSchool'])?$result_payments[0]['receiptSchool']:'';
                $page['Rows'][$key]['exam_type'] = ITSSelItemName('major','exam_type',$v['exam_type']);
                $userInfo = Db::name('users')->where(['userId'=>$v['userId']])->find();
                $page['Rows'][$key] = array_merge($userInfo,$page['Rows'][$key]);
                $page['Rows'][$key]['total_price'] = Db::name('payments')->where(['userId'=>$v['userId'],'course_bn'=>$v['course_bn']])->sum('money');
                $page['Rows'][$key]['wait_price'] = $v['deal_price']-$page['Rows'][$key]['total_price'];
                $page['Rows'][$key]['arre_type'] = $page['Rows'][$key]['wait_price']>0?'是':'否';
				//$page['Rows'][$key]['level_id_format'] = ITSSelItemName('major','level_type',$v['level_id']);
                //$page['Rows'][$key]['bill_way_format'] = ITSSelItemName('fee','bill_way',$v['bill_way']);
                //$page['Rows'][$key]['bill_type_format'] = ITSSelItemName('fee','bill_type',$v['bill_type']);
				//$page['Rows'][$key]['receipt_time_format'] = ($v['receipt_time'] !==0) ? $this->time_date($v['receipt_time']) : '未知';
				//$page['Rows'][$key]['trueName'] = $this->get_users_name($v['userId']);
			}
		}
        /**$type==1 && $page = Db::name('student_bill_fee_log')->where($where)->paginate(input('post.pagesize/d'))
                     ->toArray();
        if($type==1 && count($page['Rows'])>0){
			foreach($page['Rows'] as $key => $v){
				$page['Rows'][$key]['level_id_format'] = ITSSelItemName('major','level_type',$v['level_id']);
                $page['Rows'][$key]['bill_way_format'] = ITSSelItemName('fee','bill_way',$v['bill_way']);
                $page['Rows'][$key]['bill_type_format'] = ITSSelItemName('fee','bill_type',$v['bill_type']);
				$page['Rows'][$key]['receipt_time_format'] = ($v['receipt_time'] !==0) ? $this->time_date($v['receipt_time']) : '未知';
				//$page['Rows'][$key]['trueName'] = $this->get_users_name($v['userId']);
			}
		}*/
        return $page;
	}
    

	public function paymentRecords($userId){
	    //学历处理
	    $where['userId'] = $userId;
	    $res = Db::name('student_bill_fee_log')->where($where)
	                                           ->select();
	    if(count($res)>0){
	        foreach($res as $key => $v){
	            $res[$key]['level_id_format'] = ITSSelItemName('major','level_type',$v['level_id']);
	            $res[$key]['bill_way_format'] = ITSSelItemName('fee','bill_way',$v['bill_way']);
	            $res[$key]['bill_type_format'] = ITSSelItemName('fee','bill_type',$v['bill_type']);
	            $res[$key]['receipt_time_format'] = ($v['receipt_time'] !==0) ? $this->time_date($v['receipt_time']) : '未知';
	            //$page['Rows'][$key]['trueName'] = $this->get_users_name($v['userId']);
	        }
	    }
	    return $res;
	    
	}
    
    //学历查看缴费明细
	public function paymentRecords2($userId,$courseBn){
	    //学历处理
	    $where['userId'] = $userId;
        $where['course_bn'] = $courseBn;
	    $res = Db::name('payments')->where($where)
	                                           ->select();
	    if(count($res)>0){
	        foreach($res as $key => $v){
	            /*$res[$key]['level_id_format'] = ITSSelItemName('major','level_type',$v['level_id']);
	            $res[$key]['bill_way_format'] = ITSSelItemName('fee','bill_way',$v['bill_way']);
	            $res[$key]['bill_type_format'] = ITSSelItemName('fee','bill_type',$v['bill_type']);
	            $res[$key]['receipt_time_format'] = ($v['receipt_time'] !==0) ? $this->time_date($v['receipt_time']) : '未知';*/
	            //$page['Rows'][$key]['trueName'] = $this->get_users_name($v['userId']);
	        }
	    }
	    return $res;
	    
	}
	
	public function getById($id){
		if($id == ''){
			$info = $this->get(['fee_id'=>$id]);
		}else{
			$info = $this->get(['fee_id'=>$id]);
			$info['pay_time'] = ($info['pay_time'] !==0) ? $this->time_date($info['pay_time']) : '未知';
			$info['plan_paytime'] = ($info['plan_paytime'] !==0) ? $this->time_date($info['plan_paytime']) : '未知';
			$info['entry_time'] = ($info['entry_time'] !==0) ? $this->time_date($info['entry_time']) : '未知';
		}
		return $info;
	}
	/**
	 * 新增
	 */
	public function add(){
		$data = input('post.');
		//dd($data);
		$orderNo = trim(input('post.orderNo'));
		if($orderNo == ''){
			return MBISReturn("订单信息不能为空",-3);
		}
		$data['orderId'] = Db::name('orders')->where('orderNo',$orderNo)->value('orderId');
		if( $data['orderId'] =='' ){
		    return MBISReturn("订单信息不能为空",-3);
		}

		$data['createtime'] = time();
        $data['lastmodify'] = time();
        $data['entry_time'] = strtotime($data['entry_time']);
        $data['pay_time'] = strtotime($data['pay_time']);
        $data['plan_paytime'] = strtotime($data['plan_paytime']);
        if(empty($data['userId'])){
        	return MBISReturn("会员信息不能为空",-2);
        }
        MBISUnset($data,'id,student_no,student_name,entry_time,partners');
		$result = $this->save($data);
        if(false !== $result){
        	return MBISReturn("新增成功", 1);
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
        $data['pay_time'] = strtotime($data['pay_time']);
        $data['plan_paytime'] = strtotime($data['plan_paytime']);
		MBISUnset($data,'id');
		//dd($data);
		Db::startTrans();
		try{
		    $result = $this->save($data,['fee_id'=>$id]);
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
		    $result = $this->where(['fee_id'=>$id])->delete();
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
		$where['dataFlag'] = 1;
		$where['userType'] = 0;
        $users = Db::name('users');
        return $users->field('*')->where($where)->order('convert(trueName using gb2312) asc')->select();
    }

    /**
	 * 获取所有学员信息 (待完善) 目前只查询有报名信息的学员
	 */
	public function get_user_lists($type){
		$where = [];
		$where['u.userType'] = 0;
		$where['u.dataFlag'] = 1;
		if($type == 1){
			$where['u.student_type'] = ['in',[2,3]];
		}else{
			$where['u.student_type'] = ['in',[1,3]];
		}
		//$where['orderStatus'] = 0;
		return $userlist = Db::name('users')
							->alias('u')
							//->join('orders o','u.userId = o.userId')
							->where($where)
							//->field('u.userId,u.trueName,o.totalMoney,o.orderId,o.orderNo')
							->order('convert(trueName using gb2312) asc')
							->select();
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
     * 会员姓名
     */
    public function get_users_name($id=0){
    	$users = Db::name('users');
    	return $users->where('userId',$id)->value('trueName');
    }
    /**
     * 岗位名称
     */
    public function get_employeetype_name($id=0){
    	$employeetype = Db::name('EmployeeType');
    	return $employeetype->where('employee_type_id',$id)->value('name');
    }
    /**
     * 校区名称
     */
    public function get_businesscenter_name($id=0){
    	$businesscenter = Db::name('business_center');
    	return $businesscenter->where('business_center_id',$id)->value('name');
    }

    public function time_date($time){
		return date('Y-m-d',$time);
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

	public function getInfo(){
		$userId = (int)input('post.userId');
		$where = [];
		$where['u.userId'] = $userId;
		//$where['orderStatus'] = 0;
		$info = Db::name('users')
				->alias('u')
				->join('orders o','u.userId=o.userId','left')
				->where($where)
				->field('u.student_no,u.pre_entry_no,u.trueName,u.createtime,o.orderNo,o.totalMoney')->find();
		//dump($info);die;
		$info['createtime'] = ($info['createtime'] !==0) ? $this->time_date($info['createtime']) : '';
		if(!empty($info['student_no'])){
			$info['student_no'] = $info['student_no'];
		}else{
			$info['student_no'] = (!empty($info['pre_entry_no'])) ? $info['pre_entry_no'] : '';
		}
		if(!empty($info)){
			return ['data' => $info,'status' => 1];
		}else{
			return ['msg' => '数据加载失败','status' => -1];
		}
	}
    
    /**
	 * 分页
	 * @$type 缴费明细管理
	 */
	public function feeDetailQuery($type,$id=""){
	
		$where   = [];
		$start   = strtotime(input('get.start'));
		$end     = strtotime(input('get.end'));
		$orderNo = input('get.orderNo');
		$feeNo   = input('get.feeNo');
		$realAmount = input('get.realAmount');
		$student_name = input('get.student_name');
        
        $reqUserId = input('get.userId');
        $courseBn = input('get.courseBn');
        
        //课程类型处理
        $type==1 && $where['a.type_id'] = 1;
        $type!=1 && $where['a.type_id'] = ['neq',1];
        
		if(!empty($start) && !empty($end)){
            $type==1 && $where['receipt_time'] = ['between',["$start","$end"]];
			//$type==2 && $where['a.pay_time'] = ['between',["$start","$end"]];
		}
		if($type==2 && !empty($orderNo)){
			//$where['a.orderNo'] = ['like',"%$orderNo%"];
		}
		if(!empty($feeNo)){
            $type==1 && $where['receipt_no'] = ['like',"%$feeNo%"];
			//$type==2 && $where['a.fee_no'] = ['like',"%$feeNo%"];
		}
		if($type==2 && !empty($realAmount)){
			//$where['a.real_amount'] = ['=',"$realAmount"];
		}
		if(!empty($student_name)){
            //$type==1 && $where['name'] = ['like',"%$student_name%"];
            $userId = Db::name('users')->where(['trueName'=>['like','%'.$student_name.'%']])->value('userId');
            !empty($userId) && $where['a.userId'] = $userId;
            empty($userId) && $where['a.userId'] = '-1';
			//$type==2 && $where['a.student_name'] = ['like',"%$student_name%"];
		}
        if($type=='1'){
           //false && $where['s.type_id'] = ['in',"1,3"];
        }else{
           //$type==2 && $where['s.type_id'] = ['in',"2,3"];
        }
        /*
        $type==2 && $page = $this->alias('a')
                     ->join('orders s','a.orderId = s.orderId','LEFT')
                    // ->join('users user','user.userId = a.userId','LEFT')
                     ->where($where)
                     ->field('a.*')
                     ->order('lastmodify desc')
		             ->paginate(input('post.pagesize/d'))
                     ->toArray();
		if($type==2 && count($page['Rows'])>0){
			foreach($page['Rows'] as $key => $v){
				$page['Rows'][$key]['fee_type'] = $this->get_fee_type($v['fee_type']);
				$page['Rows'][$key]['pay_type'] = $this->get_pay_type($v['pay_type']);
				$page['Rows'][$key]['fee_class'] = $this->get_fee_class($v['fee_class']);
				$page['Rows'][$key]['pay_time'] = ($v['pay_time'] !== 0) ? $this->time_date($v['pay_time']) : '未知';
				$page['Rows'][$key]['plan_paytime'] = $this->time_date($v['plan_paytime']);
				$page['Rows'][$key]['entry_time'] = ($v['entry_time'] !==0) ? $this->time_date($v['entry_time']) : '未知';
				//$page['Rows'][$key]['trueName'] = $this->get_users_name($v['userId']);
			}
		}*/
        
        //学历处理
        $exam_type = session('examType');
        //$type==1 && $where['exam_type'] = $exam_type;
        $school_id = input('school_id');
        $major_id  = input('major_id');
        $level_id  = input('level_id');
        if($type==1 && $school_id){
            $school_name = db::name('school')->where('school_id='.$school_id)->value('name');
            $where['edu.school_name']= ['=',$school_name];
        }
        if($major_id){
            $where['edu.major_name']= ['=',$major_id];
        }
        if($level_id){
            $where['edu.level_name']= ['=',$level_id];
        }
        $join = array(
            array('student_edu edu','edu.userId = a.userId AND edu.course_bn=a.course_bn','left'),
            array('users users','users.userId = a.userId','left'),
        );
        !empty($reqUserId) && $where['a.userId']=$reqUserId;
        !empty($courseBn) && $where['a.course_bn']=$courseBn;
        !empty($_GET['school_name']) && $where['a.receiptSchool']=input('get.school_name');
        !empty($_GET['receiptNo']) && $where['a.receiptNo']=input('get.receiptNo');
        if( isset( $_GET['action'])  &&  $_GET['action'] == 'fy' ){
            $page1 = Db::name('payments')->alias('a')
                                        ->where($where)
                                        ->join($join)
                                        ->select();
            $page['Rows'] = $page1;
        }else{
            $page = Db::name('payments')->alias('a')
                                        ->where($where)
                                        ->join($join)
                                        ->paginate(input('post.pagesize/d'))
                                        ->toArray();
        }
        //dump($page);exit;
        //getLastSql();
        if(count($page['Rows'])>0){
            $Major = new \application\admin\model\Major;
            $arrMajorType = $Major->arrMajorType;
			foreach($page['Rows'] as $key => $v){
                $eduInfo = Db::name('student_edu')->where(['userId'=>$v['userId'],'course_bn'=>$v['course_bn']])->find();
                if(empty($eduInfo)) continue;
                if(!empty($eduInfo)):
                $page['Rows'][$key]['exam_type'] = !empty($arrMajorType[$eduInfo['exam_type']])?$arrMajorType[$eduInfo['exam_type']]:''; 
                endif;
               $page['Rows'][$key]['receiptDate'] = strpos($v['receiptDate'],'-')===FALSE?date('Y-m-d',$v['receiptDate']):$v['receiptDate'];
                $page['Rows'][$key] = array_merge($eduInfo,$page['Rows'][$key]);
                $userInfo = Db::name('users')->where(['userId'=>$v['userId']])->find();
                $page['Rows'][$key] = array_merge($userInfo,$page['Rows'][$key]);
                
			}
		}
        
        return $page;
	}
    
}
