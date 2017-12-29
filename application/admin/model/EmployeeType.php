<?php
namespace application\admin\model;
/**
 * 学校业务处理
 */
use think\Db;
class EmployeeType extends Base{
	/**
	 * 分页
	 */
	public function pageQuery(){
		$where = [];
        $name = input('get.name');
        $department_id = input('get.department_id');
		if($name!='')$where['name'] = ['like','%'.$name.'%'];
		if($department_id!='')$where['department_id'] = ['=',"$department_id"];
        $page = $this->where($where)->field('*')->order('lastmodify desc')
		->paginate(input('post.pagesize/d'))->toArray();
		if(count($page['Rows']) > 0 ){
			foreach($page['Rows'] as $key => $v){
				$page['Rows'][$key]['department_id'] = $this->get_department_name($v['department_id']);
			}
		}
        return $page;
	}
	public function getById($id){
		return $this->get(['employee_type_id'=>$id]);
	}
	/**
	 * 新增
	 */
	public function add(){
		$data = input('post.');
		$data['createtime'] = time();
        $data['lastmodify'] = time();
		MBISUnset($data,'employee_type_id');
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
		MBISUnset($data,'id');
		Db::startTrans();
		try{
		    $result = $this->save($data,['employee_type_id'=>$id]);
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
		    $result = $this->where(['employee_type_id'=>$id])->delete();
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
	 * 部门列表
	 */
    public function get_department_list(){
        $department = Db::name('department')->field('*')->select();
        foreach ($department as &$v){
        	if($v['parent_id'] != 0){
        		$v['department'] = ($this->getDepartmentName($v['parent_id'])).'--'.$v['name'];
        	}else{
        		$v['department'] = $v['name'];
        	}
        }
        return $department;
    }
    public function get_department_name($id=0){
    	$department = Db::name('department');
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
    	return $name;
    }
    public function getDepartmentName($id=0){
    	return Db::name('department')->where('department_id',$id)->value('name');
    }
	/**
	 * 岗位列表
	 */
    public function get_lists(){
        $info = Db::name('employee_type')->alias('e')->join('department d','e.department_id = d.department_id','LEFT')->field('e.*,d.name as department_name')->select();
        return $info;
	}
    public function get_name($id=0){
        return $this->where('school_id',$id)->column('name');
	}
}
