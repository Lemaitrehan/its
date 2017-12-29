<?php
namespace application\admin\model;
/**
 * 通知记录业务处理
 */
use think\Db;
use application\common\model\SmsAll;
use think\Model;
class StudentNoticeLog extends Base{
	
	/**
	 * 分页
	 */
	public function pageQuery($type){
	    //实例化对象
	    $SmsAll         = new SmsAll();
	    //发送方式
	    $arrSendType    = $SmsAll->sendType;
	    //模板类型
	    $arrSempletType = $SmsAll->templetType;
	    //短信发送状态
	    $arrSendStatus  = $SmsAll->sendStatus;
	    //模板审核状态
	    $temStatus      = $SmsAll->temStatus;
	    //收到回执
	    $arrAccept      = $SmsAll->arrAccept;
	    
	    //查找审核权限的按钮
	    $person_id   = session('MBIS_STAFF')->staffId;
	    if( $type == 1){
	       $privilegeCode =  'CKTZ_01';
	    }else{
	        $privilegeCode =  'CKTZJN_01';
	    }
	
	    $auditStatus = model('Review')->reviewShow($privilegeCode,$person_id);
	    $where = [];
	    $where['s.jump_type'] = $type;
        $key = input('get.key');
		if($key!='')$where['s.userId'] = ['like','%'.$key.'%'];
		
          $page = Db::name('sms_template')->alias('s')
                                 ->join('notice_tmpl tmpl','tmpl.notice_id = s.template_id','LEFT')
                                 ->where($where)
                                 ->field('s.id,s.status,s.title,tmpl.send_type,tmpl.tmpl_type,tmpl.content')
                                 ->order('s.update_time desc')
		                         ->paginate(input('post.pagesize/d'))
                                 ->toArray();
		if(count($page['Rows'])>0){
			foreach($page['Rows'] as $key => $v){
			    //审核
			    $page['Rows'][$key]['auditStatus']   = $auditStatus;
				//模板类型名称
				$page['Rows'][$key]['template_type'] = $arrSempletType[ $v['tmpl_type'] ];
				//发送类型
				$page['Rows'][$key]['type']          = $arrSendType[ $v['send_type'] ];
				//模板状态
				$page['Rows'][$key]['statusText']    = $temStatus[ $v['status'] ];
			}
		}
        return $page;
	}
	
	
	/**
	 * 分页
	 */
	public function pageSmsHistory(){
	    //实例化对象
	    $SmsAll         = new SmsAll();
	    //发送方式
	    $arrSendType    = $SmsAll->sendType;
	    //模板类型
	    $arrSempletType = $SmsAll->templetType;
	    //短信发送状态
	    $arrSendStatus  = $SmsAll->temStatus;
	    //收到回执
	    $arrAccept      = $SmsAll->arrAccept;
	     
	    $where = [];
	    $key = input('get.key');
	    if($key!='')$where['user.trueName|user.student_no'] = ['like','%'.$key.'%'];
	
	    $page = Db::name('sms')->alias('s')
	    ->join('users user','user.userId = s.userId','LEFT')
	    ->join('notice_tmpl tmpl','tmpl.notice_id = s.template_id','LEFT')
	    ->where($where)
	    ->field('s.*,user.userPhone,user.trueName,tmpl.tmpl_type,FROM_UNIXTIME(S.targetTime) AS targetTime')
	    ->order('s.update_time desc')
	    ->paginate(input('post.pagesize/d'))
	    ->toArray();
	    if(count($page['Rows'])>0){
	        foreach($page['Rows'] as $key => $v){
	            $page['Rows'][$key]['user']          = $v['trueName'].'('.$v['userPhone'].')';
	            //模板类型名称
	            $page['Rows'][$key]['template_type'] = $arrSempletType[ $v['tmpl_type'] ];
	            //发送类型
	            $page['Rows'][$key]['type']          = $arrSendType[ $v['type'] ];
	            //发送状态
	            $page['Rows'][$key]['status']        = $arrSendStatus[ $v['status'] ];
	            $page['Rows'][$key]['is_accept']     = $arrAccept[ $v['is_accept'] ];
	        }
	    }
	    return $page;
	}
	
	
	public function getById($id){
		return $this->get(['sms_id'=>$id]);
	}
	/**
	 * 新增
	 */
	public function add(){
        
        return MBISReturn('新增失败',-1);
	}
    /**
	 * 编辑
	 */
	public function edit(){
		$id = (int)input('post.id');
		$data = input('post.');
        $data['lastmodify'] = time();
        MBISUnset($data,'type_id');
        MBISUnset($data,'startDate');
        MBISUnset($data,'endDate');
		MBISUnset($data,'id');
		Db::startTrans();
		try{
		    $result = $this->save($data,['sms_id'=>$id]);
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
	    $id     = input('post.id/d');
	    $SmsAll = new SmsAll();
	    $res    = $SmsAll->getSmsBase($id);
	    //发送状态 -1=> 发送失败 0=》未发送  1=> 暂不发送  2=》 已经发送
	    if( in_array($res['status'], array(-1,2) )  ){
	        return MBISReturn("短信状态，不能删除", 1);
	    }
	    Db::startTrans();
		try{
		    $result = db::name('sms')->where('smsId','=',$id)->delete();
	        if(false !== $result){
	        	Db::commit();
	        	return MBISReturn("删除成功", 1);
	        }
		}catch (\Exception $e) {
            Db::rollback();
            return MBISReturn('删除失败',-1);
        }
	}
	
	//审核
	public function auditData(){
	    $person_id           = session('MBIS_STAFF')->staffId;
	    $time                = time();
	    $id                  = input('id');
	    $status              = input('status');
	    $data['update_id']   = $person_id;
	    $data['update_time'] = $time;
	    $data['status']      = $status;
	    Db::startTrans();
	    try{
	       $affow_id1 = db::name('sms_template')->where('id ='.$id)->update($data);
	       $affow_id2 = db::name('sms')->where('req_id ='.$id)->update($data);
	       if($affow_id1 && $affow_id2){
	           Db::commit();
	           return MBISReturn("审核成功", 1);
	       }else{
               exception('审核失败！！！', 100006);
	       }
	    }catch (\Exception $e) {
	        Db::rollback();
	        return MBISReturn('审核失败',-1);
	    }
	}
	
	
	/**
	 * 通知发送记录信息列表
	 */
	public function get_info_list(){
		$info = Db::name('student_notice_log')->field('*')->select();
		return $info;
	}
	/**
	 * 通知模板列表
	 */
	public function get_noticetmpl_list(){
        $noticetmpl = Db::name('notice_tmpl');
        return $noticetmpl->field('*')->select();
    }
    /**
     * 通知模板主题
     */
    public function get_noticetmpl_title($id=0){
    	$noticetmpl = Db::name('notice_tmpl');
    	return $noticetmpl->where('notice_id',$id)->value('title');
    }
    public function get_userinfo(){
    	/*
    	$users = Db::name('users')
						//->where($where)
						->field('student_no,trueName,nickName,userQQ,userPhone,userEmail,uidType,student_type')
						->paginate(input('post.pagesize/d'))->toArray();
		//dump($users);die;
		if(count($users['Rows'])>0){
			foreach($users['Rows'] as $key => $v){
				$users['Rows'][$key]['uidType'] = $this->get_uidType($v['uidType']);
				$users['Rows'][$key]['student_type'] = $this->get_student_type($v['student_type']);
			}
		}
		*/
		$users = Db::name('users')
						//->where($where)
						->field('userId,student_no,trueName,nickName,userQQ,userPhone,userEmail,uidType,student_type')
						->paginate();
		//dump($users);die;
		if($users){
			foreach($users as $v){

			}
		}
		return $users;
    }
    public function getUsersList(){
    	$page = (int)input('post.page');
    	$users = Db::name('users')
						//->where($where)
						->field('userId,student_no,trueName,nickName,userQQ,userPhone,userEmail,uidType,student_type')
						->select();
		if($users){
			$pageinfo = [];
			$pageinfo['total']     = count($users); //总条数
			$pageinfo['pageSize']  = 15;  //每页条数
			$pageinfo['startPage'] = $page*$pageinfo['pageSize'];
			$pageinfo['totalPage'] = ceil($pageinfo['total']/$pageinfo['pageSize']); //总页数
			$info = Db::name('users')
						->field('userId,student_no,trueName,nickName,userQQ,userPhone,userEmail,uidType,student_type')
						->limit($pageinfo['startPage'],$pageinfo['pageSize'])
						->select();
			return ['status'=>1,'data'=>$info,'pageinfo'=>$pageinfo];
		}else{
			return ['msg'=>'数据加载失败','status'=>-1];
		}
    }


    /**
     * 科目类型列表
     */
    public function get_subjecttype_list(){
    	$employeetype = Db::name('subject_type');
    	return $employeetype->field('*')->select();
    }
    /**
     *根据条件获取学员信息并返回    未完，待续。。。
     */
    public function userSearch(){
    	$where = [];
    	$start = strtotime(input('post.start'));
		$end = strtotime(input('post.end'));
		$student_type = (int)input('post.student_type');
		if(!empty($start) && !empty($end)){
			$where['createtime'] = ['between',["$start","$end"]];
		}
		if($student_type !== '')$where['student_type'] = ['=',"$student_type"];
		$users = Db::name('users')
						->where($where)
						->field('student_no,trueName,nickName,userQQ,userPhone,userEmail,uidType,student_type')
						->limit(8,10)
						->select();
		if($users){
			$page = [];
			$page['total'] = count($users);
			$page['pagesize'] = 8;
			$page['totalpage'] = ceil($page['total']/$page['pagesize']);
			return ['status'=>1,'user'=>$users,'page'=>$page];
		}else{
			return ['status'=>-1,'msg'=>'未找到符合条件的数据'];
		}
    }
    
    /**
     * 根据选择的模板ID获取模板信息并返回
     */
    public function chooseTmpl(){
       
    	$notice_id = input('post.notice_id');
    	$noticetmpl = Db::name('notice_tmpl');
    	if($notice_id ==''){
    		return ['msg'=>'请选择模板','status'=>0];
    	}
   
	    $result      = $noticetmpl->where('notice_id',$notice_id)->field('title,content,send_type')->find();
	 
	    $SmsAll      = new SmsAll();
	  
	    $arrSendType = $SmsAll->sendType;  
	   // $arrTag      = $SmsAll->arrTag;

	    /* if(!isset($arrTag[$notice_id])){
	        return ['msg'=>'没有后台数据支持，请联系研发部','status'=>-1];
	    } */
	    if($result){
	    	return ['title'=>$result['title'],
	    	        'content'=>(string)html_entity_decode($result['content']),
	    	        'status'=>1,
	    	        'send_type'=> $result['send_type'],
	    	        'type'=>$arrSendType[ $result['send_type'] ] ];
	    }else{
	    	return ['msg'=>'数据错误','sataus'=>-1];
	    }
    }
    
    /**
     * 校区列表
     */
    public function get_businesscenter_list(){
    	$businesscenter = Db::name('BusinessCenter');
    	return $businesscenter->field('*')->select();
    }

    /**
     * 部门名称
     */
    public function get_department_name($id=0){
    	$department = Db::name('department');
    	return $department->where('department_id',$id)->value('name');
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

	public function get_send_type($type){
		switch($type){
			case 1:
				return '短信';
				break;
			case 2:
				return '邮件';
				break;
			case 3:
				return 'APP';
				break;
			case 4:
				return '微信';
				break;
			default :
				return '未知';
		}
	}
	//发送状态 -1=> 发送失败 0=》未发送 1=》 已经发送 2=> 暂不发送
	public function is_send($status){
	    
		switch($status){
		    case -1:
		        return '发送失败';
		      break;
			case 0:
				return '未发送';
			  break;
			case 1:
				return '已发送';
			  break;
			case 2:
				 return '暂不发送';
			  break;
		}		
	}
	public function get_uidType($type){
		switch($type){
			case 1:return '新生';
			case 2:return '在学生';
			case 3:return '会员';
		}
	}
	public function get_student_type($type){
		switch($type){
			case 1:return '技能';
			case 2:return '学历';
			case 3:return '技能学历';
		}
	}

}
