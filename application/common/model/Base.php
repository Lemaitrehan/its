<?php
namespace application\common\model;
use think\Model;
use think\Db;
/**
 * 基础模型器
 */

class Base extends Model {
	/**
	 * 获取空模型
	 */
	public function getEModel($tables){
		$rs =  Db::query('show columns FROM `'.config('database.prefix').$tables."`");
		$obj = [];
		if($rs){
			foreach($rs as $key => $v) {
				$obj[$v['Field']] = $v['Default'];
				if($v['Key'] == 'PRI')$obj[$v['Field']] = 0;
			}
		}
		return $obj;
	}
    
    public function checkIsModel($tables){
        $cur_table = config('database.prefix').$tables;
		$rs =  Db::query('show tables;');
        $flag = false;
		if($rs){
			foreach($rs as $key => $v) {
				$v['Tables_in_'.config('database.database')] == $cur_table && $flag = true;
			}
		}
		return $flag;
	}
}