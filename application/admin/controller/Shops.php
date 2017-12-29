<?php
namespace application\admin\controller;
use application\admin\model\Shops as M;
/**
 * 店铺控制器
 */
class Shops extends Base{
    public function index(){
    	return $this->fetch("list");
    }
    public function stopIndex(){
    	return $this->fetch("list_stop");
    }
    /**
     * 获取分页
     */
    public function pageQuery(){
    	$m = new M();
    	return $m->pageQuery(1);
    }
    /**
     * 停用店铺列表
     */
    public function pageStopQuery(){
    	$m = new M();
    	return $m->pageQuery(-1);
    }
    /**
     * 获取菜单
     */
    public function get(){
    	$m = new M();
    	return $m->get((int)Input("post.id"));
    }
    /**
     * 跳去编辑页面
     */
    public function toEdit(){
    	$m = new M();
    	$id = (int)Input("get.id");
    	if($id>0){
    	    $object = $m->getById((int)Input("get.id"));
    	    $object['applyId'] = 0;
    	    $data['object']=$object;
    	}else{
    		$object = $m->getEModel('shops');
    		$object['catshops'] = [];
    		$object['accreds'] = [];
    		$object['applyId'] = 0;
    		$object['loginName'] = '';
    		$data['object']=$object;
    	}
    	$data['goodsCatList'] = model('goodsCats')->listQuery(0);
    	$data['accredList'] = model('accreds')->listQuery(0);
    	$data['bankList'] = model('banks')->listQuery();
    	$data['areaList'] = model('areas')->listQuery(0);   	
    	return $this->fetch("edit",$data);
    }
    /**
     * 跳去新增页面
     */
    public function toAddByApply(){
    	$apply = model('ShopApplys')->checkOpenShop((int)Input("get.id"));
    	if($apply['shopId']!=''){
    		$this->assign("msg",'对不起，该开店申请已处理！');
    		return $this->fetch("./message");
    	}else{
    		$object = model('ShopApplys')->getEModel('shops');
    		$object['userId'] = (int)$apply['userId'];
    		$object['applyId'] = (int)Input("get.id");
    		$object['loginName'] = '';
    		$object['catshops'] = [];
    		$object['accreds'] = [];
    		$data = [
    		   'object'=>$object,
    		   'goodsCatList'=>model('goodsCats')->listQuery(0),
    		   'accredList'=>model('accreds')->listQuery(0),
    		   'bankList'=>model('banks')->listQuery(),
    		   'areaList'=>model('areas')->listQuery(0)
    		];
	    	return $this->fetch("edit",$data);
    	}
    }
    /**
     * 新增菜单
     */
    public function add(){
    	$m = new M();
    	return $m->add();
    }
    /**
     * 编辑菜单
     */
    public function edit(){
    	$m = new M();
    	return $m->edit();
    }
    /**
     * 删除菜单
     */
    public function del(){
    	$m = new M();
    	return $m->del();
    }
    
    /**
     * 检测店铺编号是否存在
     */
    public function checkShopSn(){
    	$m = new M();
    	$isChk = $m->checkShopSn(input('post.shopSn'),input('shopId/d'));
        if(!$isChk){
    		return ['ok'=>'该店铺编号可用'];
    	}else{
    		return ['error'=>'对不起，该店铺编号已存在'];
    	}
    }
    
    /**
     * 自营店铺后台
     */
    public function inself(){
    	$staffId=session("MBIS_STAFF");
    	if(!empty($staffId)){
    		$id=1;
    		$s = new M();
    		$r = $s->selfLogin($id);
    		if($r['status']==1){
    			header("Location: ".Url('home/shops/index'));
    			exit();
    		}
    	}
    	header("Location: ".Url('home/shops/selfShop'));
    	exit();
    }
}
