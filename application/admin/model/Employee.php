<?php
namespace application\admin\model;
/**
 * 员工业务处理
 */
use think\Db;
class Employee extends Base{
	/**
	 * 分页
	 */
	public function pageQuery(){
        $where = [];
        $start = strtotime(input('get.start'));
		$end = strtotime(input('get.end'));
		$name= input('get.name');
		$employee_no = input('get.employee_no');
		$mobile = input('get.mobile');
		$department_id = input('get.department_id');
		$employee_type_id = input('get.employee_type_id');
		if(!empty($start) && !empty($end)){
			$where['induction_time'] = ['between',["$start","$end"]];
		}
		if(!empty($name))
			$where['name'] = ['like',"%$name%"];
		if(!empty($employee_no))
			$where['employee_no'] = ['like',"%$employee_no%"];
		if(!empty($mobile))
			$where['mobile'] = ['like',"%$mobile%"];
		if(!empty($department_id))
			$where['department_id'] = ['=',"$department_id"];
		if(!empty($employee_type_id))
			$where['employee_type_id'] = ['=',"$employee_type_id"];

        $page = $this->where($where)->field('*')->order('lastmodify desc')
		->paginate(input('post.pagesize/d'))->toArray();
		if(count($page['Rows'])>0){
			foreach($page['Rows'] as $key => $v){
				if(isset($page['Rows'][$key]['department_id'])){
					$page['Rows'][$key]['department_id'] = $this->get_department_name($v['department_id']);
				}
				if(isset($page['Rows'][$key]['employee_type_id'])){
					$page['Rows'][$key]['employee_type_id'] = $this->get_employeetype_name($v['employee_type_id']);
				} 
				$page['Rows'][$key]['sex'] = $this->getSex($v['sex']);
				$page['Rows'][$key]['cooperation_type'] = $this->getCooperationType($v['cooperation_type']);
				$page['Rows'][$key]['induction_time'] = $this->time_date($v['induction_time']);
				$page['Rows'][$key]['status'] = $this->get_status($v['status']);
			}
		}
		//dump($page);die;
        return $page;
	}
	public function getById($id){
		if($id == ''){
			$info = $this->get(['employee_id'=>$id]);
		}else{
			$info = $this->get(['employee_id'=>$id]);
			$info['induction_time'] = $this->time_date($info['induction_time']);
			if($info['dimission_time'] == 0){
				$info['dimission_time'] = '';
			}else{
				$info['dimission_time'] = $this->time_date($info['dimission_time']);
			}	
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
        $data['induction_time'] = strtotime(input('post.induction_time'));
        $data['dimission_time'] = strtotime(input('post.dimission_time'));
		MBISUnset($data,'employee_id');
        MBISUnset($data,'id');
        MBISUnset($data,'startDate');
        MBISUnset($data,'endDate');
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
        $data['induction_time'] = strtotime(input('post.induction_time'));
        $data['dimission_time'] = strtotime(input('post.dimission_time'));
        MBISUnset($data,'startDate');
        MBISUnset($data,'endDate');
		MBISUnset($data,'id');
		Db::startTrans();
		try{
		    $result = $this->save($data,['employee_id'=>$id]);
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
		    $result = $this->where(['employee_id'=>$id])->delete();
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
	 * 员工信息列表
	 */
	public function get_info_list(){
		$info = Db::name('employee')->field('*')->select();
		return $info;
	}
	/**
	 * 部门列表
	 */
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
    /**
     * 岗位列表
     */
    public function get_employeetype_list(){
    	$employee_type = Db::name('employee_type')->field('*')->select();
		foreach($employee_type as &$v){
			if($v['department_id'] != 0){
				$v['employeetype'] = $this->getDepartmentName($v['department_id']).'--'.$v['name'];
			}else{
				$v['employeetype'] = $v['name'];
			}
		}
		return $employee_type;
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
    public function getDepartmentName($id=0){
    	return Db::name('department')->where('department_id',$id)->value('name');
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

	public function get_status($status){
		switch($status){
			case 0:
				return '在职';
				break;
			case 1:
				return '临时';
				break;
			case -1:
				return '离职';
				break;
			default :
				return '未知';
		}
	}
	public function getSex($sex){
		switch($sex){
			case 0:return '未知';
			case 1:return '男';
			case 2:return '女';
		}
	}
	public function getCooperationType($type){
		switch($type){
			case 1:return '全职';
			case 2:return '兼职';
		}
	}

	/**
	 *ajax操作
	 */
	public function checkType(){
		$departmentId = (int)input('post.departmentId'); //选中的部门
		if(!empty($departmentId)){
			$employeetypes = Db::name('employee_type')->where('department_id',$departmentId)->select();
			if(!empty($employeetypes)){
				return ['data'=>$employeetypes,'status'=>1];
			}else{
				return ['msg'=>'该部门暂未设置岗位','status'=> -2];
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

}
