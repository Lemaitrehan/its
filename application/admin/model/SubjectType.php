<?php
namespace application\admin\model;
use think\Db;
/**
 * 科目类型模型
 */
class SubjectType extends Base{
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
            $subjectType = model('SubjectType');
			foreach ($page['Rows'] as $key => $v){
                //$page['Rows'][$key]['subject_type_id'] = $subjectType->get_name($v['subject_type_id']);
			}
		}
        return $page;
	}
	public function getById($id){
		return $this->get(['type_id'=>$id]);
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
			$result = $this->allowField(true)->save($data);
			$id = $this->type_id;
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
		MBISUnset($data,'createtime,id');
		Db::startTrans();
		try{
		    $result = $this->allowField(true)->save($data,['type_id'=>$id]);
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
		    $result = $this->where(['subject_id'=>$id])->delete();
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
	 * 获取科目类型名称
	 */
    public function get_name($id=0){
        return $this->where('type_id',$id)->column('name');
	}
}
