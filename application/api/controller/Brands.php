<?php
namespace application\api\controller;
use application\common\model\Brands as M;
/**
* 品牌控制器
 */
class Brands extends Base{
	/**
	 * 品牌街
	 */
	public function index(){
		$m = new M();
		$pagesize = 25;
		$brandsList = $m->pageQuery($pagesize);
		$this->assign('list',$brandsList);

		$g = model('goodsCats');
		$goodsCats = $g->listQuery(0);
    	$this->assign('goodscats',$goodsCats);

    	$selectedId = (int)input("id");
    	$this->assign('selectedId',$selectedId);
		return $this->fetch('brands_list');
	}
	/**
	 * 获取品牌列表
	 */
    public function listQuery(){
        $m = new M();
        return ['status'=>1,'list'=>$m->listQuery(input('post.catId/d'))];
    }
}
