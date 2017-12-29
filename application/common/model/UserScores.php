<?php
namespace application\common\model;
/**
 * 积分业务处理器
 */
class UserScores extends Base{
     /**
      * 获取列表
      */
      public function pageQuery($userId){
      	  $type = (int)input('post.type');
          $where = ['userId'=>(int)$userId];
          if($type!=0)$where['scoreType'] = $type;
          $page = $this->where($where)->order('scoreId desc')->paginate()->toArray();
          foreach ($page['Rows'] as $key => $v){
          	  $page['Rows'][$key]['dataSrc'] = MBISLangScore($v['dataSrc']);
          }
          return $page;
      }
}
