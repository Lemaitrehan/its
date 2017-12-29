<?php
namespace application\admin\model;
/**
 * 员工考勤记录业务处理
 */
use think\Db;
class StaffCkwork extends Base{
	/**
	 * 分页
	 */
	public function pageQuery(){
        $key = input('get.key');
        $where = [];
		if($key!='')$where['user_no'] = ['like','%'.$key.'%'];
        $page = Db::name('staff_ckwork')->alias('s')->join('employee e','s.user_no=e.employee_no','left')->where($where)->field('s.*,e.name as employee_name')->order('lastmodify desc')
		->paginate(input('post.pagesize/d'))->toArray();
		
		if(count($page['Rows'])>0){
			foreach($page['Rows'] as $key => $v){
				if(isset($page['Rows'][$key]['employee_type_id'])){
					$page['Rows'][$key]['employee_type_id'] = $this->get_employeetype_name($v['employee_type_id']);
				}
				$page['Rows'][$key]['ckwork_type'] = $this->get_check_type($v['ckwork_type']);
			}
		}
        return $page;
	}

	public function getById($id){
		return $this->get(['sc_id'=>$id]);
	}

	/**
	 * 新增
	 */
	
	public function add(){
		$data = input('post.');
		$data['createtime'] = time();
        $data['lastmodify'] = time();
        $data['object_id'] = 1;
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
        $data['lastmodify'] = time();
        //$data['settlement_time'] = strtotime(input('post.startDate'));
        //MBISUnset($data,'startDate');
		MBISUnset($data,'id');
		Db::startTrans();
		try{
		    $result = $this->save($data,['sc_id'=>$id]);
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
		    $result = $this->where(['sc_id'=>$id])->delete();
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
	 * 员工考勤记录列表
	 */
	public function get_info_list(){
		$info = Db::name('staff_ckwork')->field('*')->select();
		return $info;
	}
	/**
	 * 合作方列表
	 */
	public function get_partners_list(){
        $department = Db::name('partners');
        return $department->field('*')->select();
    }
    /**
     * 岗位列表
     */
    public function get_employeetype_list(){
    	$employee_type = Db::name('employee_type')->field('*')->select();
		foreach($employee_type as &$v){
			if($v['department_id'] != 0){
				$parent_id = Db::name('department')->where('department_id',$v['department_id'])->value('parent_id');
				if($parent_id != 0){
					$v['department'] = ($this->getDepartmentName($parent_id)).'--'.($this->getDepartmentName($v['department_id'])).'--';
				}else{
					$v['department'] = ($this->getDepartmentName($v['department_id'])).'--';
				}
			}else{
				$v['department'] = '';
			}
		}
		return $employee_type;
    }
    public function getDepartmentName($id=0){
    	return Db::name('department')->where('department_id',$id)->value('name');
    }
    /**
     * 员工列表
     */
    public function get_employee_list(){
    	$employee = Db::name('employee');
    	return $employee->field('*')->where(array('status' => array('neq',-1)))->select();
    }

    /**
     * 获取选定岗位的员工编号列表
     */
    public function get_employee_no_list($employee_type_id=0){
    	$employeeno_list = Db::name('employee')->where('employee_type_id',$employee_type_id)->select();
    	return $employeeno_list;
    }
    /**
     * 校区列表
     */
    public function get_businesscenter_list(){
    	$businesscenter = Db::name('BusinessCenter');
    	return $businesscenter->field('*')->select();
    }

    /**
     * 合作方名称
     */
    public function get_partners_name($id=0){
    	$department = Db::name('partners');
    	return $department->where('p_id',$id)->value('name');
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
    /**
	 * 合作方列表
	 */
    public function get_partners_lists(){
        return $this->field('*')->select();
	}

	public function get_check_type($type){
		switch($type){
			case 1:
				return '教务处理';
				break;
			case 2:
				return '自定义考勤';
				break;
		}
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

	public function checkemployee(){
		$employee_type_id = (int)input('post.employee_type_id');
		if($employee_type_id !==''){
			$employee = Db::name('employee')->where('employee_type_id',$employee_type_id)->select();
			if($employee){
				return ['data'=>$employee,'status'=>1];
			}else{
				return ['status'=>-1,'msg'=>'此岗位尚无员工'];
			}
		}
	}

}
