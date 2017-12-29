<?php
namespace application\admin\model;
/**
 * 合作方业务处理
 */
use think\Db;
class Partners extends Base{
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
					$page['Rows'][$key]['business_type'] = $this->choice($v['business_type']);
				}
			}
        return $page;
	}
	public function getById($id){
		return $this->get(['p_id'=>$id]);
	}
	/**
	 * 新增
	 */
	public function add(){
		$data = input('post.');
		$data['createtime'] = time();
        $data['lastmodify'] = time();
		MBISUnset($data,'p_id');
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
		    $result = $this->save($data,['p_id'=>$id]);
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
		    $result = $this->where(['p_id'=>$id])->delete();
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
	 * 合作方列表
	 */
    public function get_lists(){
        return $this->field('*')->select();
	}

	public function choice($id=0){
		switch($id){
			case 1:
				return '供应商';
				break;
			case 2:
				return '分销商';
				break;
			case 3:
				return '合作老师';
				break;
		}
	}
}
