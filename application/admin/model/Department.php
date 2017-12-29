<?php
namespace application\admin\model;
/**
 * 部门业务处理
 */
use think\Db;
class Department extends Base{
	/**
	 * 分页
	 */
	public function pageQuery(){
        $key = input('get.key');
        $where = [];
		if($key!='')$where['name'] = ['like','%'.$key.'%'];
        $page = $this->where($where)->field('*')->order('lastmodify desc')
		->paginate(input('post.pagesize/d'))->toArray();
		if(count($page['Rows'])>0){
			foreach($page['Rows'] as $key => $v){
				if($page['Rows'][$key]['parent_id'] !=0){
					$page['Rows'][$key]['parent_id'] = $this->getDepartmentName($v['parent_id']);
				}else{
					$page['Rows'][$key]['parent_id'] = '暂无';
				}
			}
		}
        return $page;
	}
	public function getById($id){
		return $this->get(['department_id'=>$id]);
	}
	/**
	 * 新增
	 */
	public function add(){
		$data = input('post.');
		$data['createtime'] = time();
        $data['lastmodify'] = time();
		MBISUnset($data,'department_id');
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
		    $result = $this->save($data,['department_id'=>$id]);
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
		    $result = $this->where(['department_id'=>$id])->delete();
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
	 * 学校列表
	 */
    public function get_lists(){
    	$department = Db::name('department');
        return $this->field('*')->select();
	}
    public function get_name($id=0){
        return $this->where('school_id',$id)->column('name');
	}
	public function getDepartmentList(){
		return $this->where('parent_id',0)->field('department_id,name')->select();
	}
	public function getDepartmentName($id=0){
		return $this->where('department_id',$id)->value('name');
	}
}
