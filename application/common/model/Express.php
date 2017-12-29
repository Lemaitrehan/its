<?php
namespace application\common\model;
/**
 * 快递业务处理类
 */
use think\Db;
class Express extends Base{
    /**
	 * 获取快递列表
	 */
    public function listQuery(){
         return $this->where('dataFlag',1)->select();
    }
}
