<?php
namespace application\api\controller;
/**
* 促销控制器
 */
class Promotion extends Base{
    
    /**
     @do 获取规则列表地址
    */
    public function getRuleListsUrl()
    {
        return MBISAPIReturn( MBISReturn("获取数据成功", 1, ['url'=>url('api/promotion/showRuleLists','',false,true).'?'.http_build_query($this->getCurReqParams())]) );
    }
    
    /**
     @do 获取规则列表 
    */
    public function showRuleLists()
    {
        $data = model('common/SalesRuleOrder')->getRuleLists($this->getCurReqParams(),$this->getCurUserInfo());
        $this->assign('data',$data);
        return $this->fetch('promotion/pmt/lists');
    }
    
}
