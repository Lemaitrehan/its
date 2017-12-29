<?php
namespace application\admin\model;
/**
 * 通知模板业务处理
 */
use think\Db;
class NoticeTmpl extends Base{
	/**
	 * 分页
	 */
	public function pageQuery(){
	    $Sms         = new \application\common\model\Sms();
	    $sendType    = $Sms->sendType;
	    $templetType = $Sms->templetType;
	    
        $key = input('get.key');
        $where = [];
		if($key!='')$where['title'] = ['like','%'.$key.'%'];
        $page = $this->where($where)
                     ->field('*')
                     ->order('lastmodify desc')
		             ->paginate(input('post.pagesize/d'))
                     ->toArray();
		if(count($page['Rows'])>0){
			foreach($page['Rows'] as $key => $v){
				$page['Rows'][$key]['tmpl_type'] = $templetType[ $v['tmpl_type'] ];
				$page['Rows'][$key]['send_type'] = $sendType[ $v['send_type'] ];
				$page['Rows'][$key]['content']   = html_entity_decode( $v['content'] );
			}
		}
		
        return $page;
	}
	public function getById($id){
		return $this->get(['notice_id'=>$id]);
	}
	/**
	 * 新增
	 */
	public function add(){
		$data = input('post.');
		$data['createtime'] = time();
        $data['lastmodify'] = time();
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
		    $result = $this->save($data,['notice_id'=>$id]);
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
		    $result = $this->where(['notice_id'=>$id])->delete();
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
	 * 通知模板信息列表
	 */
	public function get_info_list(){
		$info = Db::name('notice_tmpl')->field('*')->select();
		return $info;
	}
	/**
	 * 部门列表
	 */
	public function get_department_list(){
        $department = Db::name('department');
        return $department->field('*')->select();
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

	public function get_tmpl_type($type){
		switch($type){
			case 1:return '考试通知';
			case 2:return '缴费通知';				
			case 3:return '毕业证领取通知';			
			case 4:return '上课通知';				
			case 5:return '成绩查询通知';				
			case 6:return '学位申请通知';				
			case 7:return '毕业申请通知';				
			case 8:return '报考通知';				
		}
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
		}
	}

}
