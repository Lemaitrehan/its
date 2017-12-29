<?php
namespace application\admin\controller;
use application\admin\model\Goods as M;
/**
 * 商品控制器
 */
class Goods extends Base{
   /**
	* 查看上架商品列表
	*/
	public function index(){
    	$this->assign("areaList",model('areas')->listQuery(0));
		return $this->fetch('list_sale');
	}
   /**
    * 批量删除商品
    */
    public function batchDel(){
        $m = new M();
        return $m->batchDel();
    }

    /**
    * 设置违规商品
    */
    public function illegal(){
        $m = new M();
        return $m->illegal();
    }
    /**
     * 通过商品审核
     */
    public function allow(){
        $m = new M();
        return $m->allow();
    }
	/**
	 * 获取上架商品列表
	 */
	public function saleByPage(){
		$m = new M();
		$rs = $m->saleByPage();
		$rs['status'] = 1;
		return $rs;
	}
	
    /**
	 * 审核中的商品
	 */
    public function auditIndex(){
    	$this->assign("areaList",model('areas')->listQuery(0));
		return $this->fetch('goods/list_audit');
	}
	/**
	 * 获取审核中的商品
	 */
    public function auditByPage(){
		$m = new M();
		$rs = $m->auditByPage();
		$rs['status'] = 1;
		return $rs;
	}
   /**
	 * 审核中的商品
	 */
    public function illegalIndex(){
    	$this->assign("areaList",model('areas')->listQuery(0));
		return $this->fetch('list_illegal');
	}
    /**
	 * 获取违规商品列表
	 */
	public function illegalByPage(){
		$m = new M();
		$rs = $m->illegalByPage();
		$rs['status'] = 1;
		return $rs;
	}
    
    /**
     * 跳去编辑页面
     */
    public function toView(){
    	$m = new M();
    	$object = $m->getById(input('get.id'));
    	if($object['goodsImg']=='')$object['goodsImg'] = MBISConf('CONF.goodsLogo');
    	$data = ['object'=>$object];
    	return $this->fetch('default/shops/goods/edit',$data);
    }
    
    /**
     * 编辑商品
     */
    public function toEdit(){
    	$m = new M();
    	return $m->edit();
    }
    /**
     * 删除商品
     */
    public function del(){
    	$m = new M();
    	return $m->del();
    }
}
