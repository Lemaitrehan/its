<?php
namespace application\common\model;
use think\Db;
/**
 * 学员扩展信息业务处理
 */
class StudentExtend extends Base{

	/**
	 * 新增
	 */
	public function add($data=[]){
        return $this->save($data);
	}
    
    public function getById($id){
		return $this->get(['userId'=>$id]);
	}
    
}
