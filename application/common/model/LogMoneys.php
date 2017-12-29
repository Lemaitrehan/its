<?php
namespace application\common\model;
/**
 * 资金流水业务处理器
 */
class LogMoneys extends Base{
     /**
      * 获取列表
      */
      public function pageQuery($targetType,$targetId){
      	  $type = (int)input('post.type',-1);
          $where['targetType'] = (int)$targetType;
          $where['targetId'] = (int)$targetId;
          if(in_array($type,[0,1]))$where['moneyType'] = $type;
          $page = $this->where($where)->order('id desc')->paginate()->toArray();
          foreach ($page['Rows'] as $key => $v){
          	  $page['Rows'][$key]['dataSrc'] = MBISLangMoneySrc($v['dataSrc']);
          }
          return $page;
      }
}
