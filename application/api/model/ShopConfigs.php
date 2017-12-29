<?php
namespace application\home\model;
/**
* 门店配置类
 */
use think\Db;
class ShopConfigs extends Base{
    /**
    * 店铺设置
    */
     public function getShopCfg($id){
        $rs = $this->where("shopId=".$id)->find();
        if($rs != ''){
            //图片
            $rs['shopAds'] = ($rs['shopAds']!='')?explode(',',$rs['shopAds']):null;
            //图片的广告地址
            $rs['shopAdsUrl'] = ($rs['shopAdsUrl']!='')?explode(',',$rs['shopAdsUrl']):null;
            return $rs;
        }
     }

     /**
      * 修改店铺设置
      */
     public function editShopCfg($shopId){
        $data = input('post.');
        //加载商店信息
        Db::startTrans();
		try{
	        $shopcg = $this->where('shopId='.$shopId)->find(); 
	        $scdata = array();
	        $scdata["shopId"] =  $shopId;
	        $scdata["shopKeywords"] =  Input("shopKeywords");
	        $scdata["shopBanner"] =  Input("shopBanner");
	        $scdata["shopDesc"] =  Input("shopDesc");
	        $scdata["shopAds"] =  Input("shopAds");
	        $scdata["shopAdsUrl"] =  Input("shopAdsUrl");
	        $scdata["shopHotWords"] =  Input("shopHotWords");
	        MBISUseImages(0, $shopcg['configId'], $scdata['shopBanner'],'shop_configs','shopBanner');
	        MBISUseImages(0, $shopcg['configId'], $scdata['shopAds'],'shop_configs','shopAds');
	        $rs = $this->where("shopId=".$shopId)->update($scdata);	
	        if($rs!==false){
	        	Db::commit();
	            return MBISReturn('操作成功',1);
	        }
		}catch (\Exception $e) {
			print_r($e);
            Db::rollback();
        }
        return MBISReturn('操作失败',-1);
     }
     /**
      * 获取商城搜索关键字
      */
     public function searchShopkey($shopId){
     	$rs = $this->where('shopId='.$shopId)->field('configId,shopHotWords')->find();
     	$keys = [];
     	if($rs['shopHotWords']!='')$keys = explode(',',$rs['shopHotWords']);
     	return $keys;
     }
}
