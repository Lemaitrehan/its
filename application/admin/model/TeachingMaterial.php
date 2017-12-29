<?php
namespace application\admin\model;
/**
 * 教材管理业务处理
 */
use think\Db;
class TeachingMaterial extends Base{
	/**
	 * 分页
	 */
	public function pageQuery(){
        
        $where = [];
		$material_type = input('get.material_type');
		$status = input('get.status');
		$is_shelves = input('get.is_shelves');
		$name = input('get.name');
		if($material_type != '')$where['material_type'] = ['=',"$material_type"];
		if($status != '')$where['status'] = ['=',"$status"];
		if($is_shelves != '')$where['is_shelves'] = ['=',"$is_shelves"];
		if($name != '')$where['name'] = ['like',"%$status%"];
        $page = $this->where($where)
        	->field('*')->order('lastmodify desc')
			->paginate(input('post.pagesize/d'))
			->toArray();
		
		if(count($page['Rows'])>0){
			foreach($page['Rows'] as $key => $v){
				$page['Rows'][$key]['material_type'] = $this->get_material_type($v['material_type']);
				$page['Rows'][$key]['is_shelves'] = $this->get_is_shelves($v['is_shelves']);
				$page['Rows'][$key]['units'] = $this->get_units($v['units']);
				$page['Rows'][$key]['status'] = $this->get_status($v['status']);
			}
		}
		
        return $page;
	}
	public function getById($id){
		return $this->get(['tm_id'=>$id]);
	}
	/**
	 * 新增
	 */
	public function add(){
		$data = input('post.');
		//dump($data);die;
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
		    $result = $this->save($data,['tm_id'=>$id]);
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
		    $result = $this->where(['tm_id'=>$id])->delete();
	        if(false !== $result){
	        	Db::commit();
	        	return MBISReturn("删除成功", 1);
	        }
		}catch (\Exception $e) {
            Db::rollback();
            return MBISReturn('删除失败',-1);
        }
	}
	
	public function get_info_list(){
		$info = Db::name('teaching_material')->field('*')->select();
		return $info;
	}

	public function get_units($type){
		switch($type){
			case 1:return '套';
			case 2:return '本';
		}
	}

	public function get_status($status){
		switch($status){
			case 0:return '在库';
			case 1:return '借出';
			case 2:return '丢失';
			case 3:return '损坏';
		}
	}
	
	public function get_material_type($type){
		switch($type){
			case 1: return '书本';
			case 2: return '画板';
			case 3: return '其他';
		}
	}

	public function get_is_shelves($status){
		switch($status){
			case 0:return '已下架';
			case 1:return '已上架';
			case 2:return '待上架';
		}
	}

    public function get_lists($where=[])
    {
        $rs = $this->where($where)->select();
        return $rs;
    }

}
