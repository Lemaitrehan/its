<?php
namespace application\api\controller;
use application\home\model\Settlements as M;
/**
* 结算控制器
 */
class Settlements extends Base{
	
    public function index(){
    	  return $this->fetch('shops/settlements/list');
    }

    /**
     * 获取结算单
     */
    public function pageQuery(){
        $m = new M();
        $rs = $m->pageQuery();
        return MBISReturn('',1,$rs);
    }
    /**
     * 获取待结算订单
     */
    public function pageUnSettledQuery(){
        $m = new M();
        $rs = $m->pageUnSettledQuery();
        return MBISReturn('',1,$rs);
    }
    /**
     * 结算订单
     */
    public function settlement(){
        $m = new M();
        return $m->settlement();
   } 
   /**
    * 获取已结算订单
    */
   public function pageSettledQuery(){
       $m = new M();
       $rs = $m->pageSettledQuery();
       return MBISReturn('',1,$rs);
   }
   /**
    * 查看结算详情
    */
   public function view(){
       $m = new M();
       $rs = $m->getById();
       $this->assign('object',$rs);
       return $this->fetch('shops/settlements/view');
   }
}
