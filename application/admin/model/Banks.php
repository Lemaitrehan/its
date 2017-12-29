<?php
namespace application\admin\model;
/**
 * 银行业务处理
 */
class Banks extends Base{
	/**
	 * 分页
	 */
	public function pageQuery(){
		return $this->where('dataFlag',1)->field('bankId,bankName')->order('bankId desc')->paginate(input('pagesize/d'));
	}
	public function getById($id){
		return $this->get(['bankId'=>$id,'dataFlag'=>1]);
	}
	/**
	 * 列表
	 */
	public function listQuery(){
		return $this->where('dataFlag',1)->field('bankId,bankName')->select();
	}
	/**
	 * 新增
	 */
	public function add(){
		$data = ['bankName'=>input('post.bankName'),
				 'createTime'=>date('Y-m-d H:i:s'),];
		$result = $this->validate('Banks.add')->allowField(['bankName','createTime'])->save($data);
        if(false !== $result){
        	cache('MBIS_BANKS',null);
        	return MBISReturn("新增成功", 1);
        }else{
        	return MBISReturn($this->getError(),-1);
        }
	}
    /**
	 * 编辑
	 */
	public function edit(){
		$bankId = input('post.bankId/d',0);
	    $result = $this->validate('Banks.edit')->allowField(['bankName'])->save(['bankName'=>input('post.bankName')],['bankId'=>$bankId]);

        if(false !== $result){
        	cache('MBIS_BANKS',null);
        	return MBISReturn("编辑成功", 1);
        }else{
        	return MBISReturn($this->getError(),-1);
        }
	}
	/**
	 * 删除
	 */
    public function del(){
	    $id = input('post.id/d',0);
		$data = [];
		$data['dataFlag'] = -1;
	    $result = $this->update($data,['bankId'=>$id]);
        if(false !== $result){
        	cache('MBIS_BANKS',null);
        	return MBISReturn("删除成功", 1);
        }else{
        	return MBISReturn($this->getError(),-1);
        }
	}
	
}
