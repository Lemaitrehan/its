<?php
namespace application\admin\model;
use think\Db;
/**
 * 广告位置业务处理
 */
class AdPositions extends Base{
	/**
	 * 分页
	 */
	public function pageQuery(){
		return $this->where('dataFlag',1)->field(true)->order('apSort asc,positionId asc')->paginate(input('pagesize/d'));
	}
	public function getById($id){
		return $this->get(['positionId'=>$id,'dataFlag'=>1]);
	}
	/**
	 * 新增
	 */
	public function add(){
		$data = input('post.');
		MBISUnset($data,'positionId');
		$result = $this->validate('AdPositions.add')->allowField(true)->save($data);
        if(false !== $result){
        	return MBISReturn("新增成功", 1);
        }else{
        	return MBISReturn($this->getError(),-1);
        }
	}
    /**
	 * 编辑
	 */
	public function edit(){
		$Id = (int)input('post.positionId');
	    $result = $this->validate('AdPositions.edit')->allowField(true)->save(input('post.'),['positionId'=>$Id]);
        if(false !== $result){
        	return MBISReturn("编辑成功", 1);
        }else{
        	return MBISReturn($this->getError(),-1);
        }
	}
	/**
	 * 删除
	 */
    public function del(){
	    $id = (int)input('post.id/d');
	    $result = $this->setField(['positionId'=>$id,'dataFlag'=>-1]);
        if(false !== $result){
        	return MBISReturn("删除成功", 1);
        }else{
        	return MBISReturn($this->getError(),-1);
        }
	}
	/**
	* 获取广告位置
	*/
	public function getPositon($typeId){
		return $this->where(['positionType'=>$typeId,'dataFlag'=>1])->order('apSort asc,positionId asc')->select();
	}
	
}
