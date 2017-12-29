<?php
namespace application\admin\model;
/**
 * 经营范围业务处理
 */
use think\Db;
class Datas extends Base{
	/**
	 * 获取指定分类的列表
	 */
	public function listQuery($catId){
		return Db::name('datas')->where('catId',$catId)->field('dataName,dataVal')->select();
	}
}
