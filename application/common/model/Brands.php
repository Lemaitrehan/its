<?php
namespace application\common\model;
use think\Db;
/**
 * 品牌业务处理类
 */
class Brands extends Base{
	/**
	 * 获取品牌列表
	 */
	public function pageQuery($pagesize){
		$id = (int)input('id');
		$where['b.dataFlag']=1;
		if($id>1){
			$where['gcb.catId']=$id;
		}
		$rs = $this->alias('b')
				   ->join('__CAT_BRANDS__ gcb','gcb.brandId=b.brandId','left')
				   ->where($where)
				   ->field('b.brandId,brandName,brandImg,gcb.catId')
				   ->paginate($pagesize)->toArray();
		return $rs;
	}
	/**
	 * 获取品牌列表
	 */
	public function listQuery($catId){
		$rs = Db::name('cat_brands')->alias('l')->join('__BRANDS__ b','b.brandId=l.brandId and b.dataFlag=1 and l.catId='.$catId)
		          ->field('b.brandId,b.brandName')->select();
		return $rs;
	}
}
