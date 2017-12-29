<?php
// +----------------------------------------------------------------------
// | 时间处理类
// +----------------------------------------------------------------------
// | Author: lijianhua
// +----------------------------------------------------------------------
namespace application\common\model;
/**
 * 订单业务处理类
 */
class Timecalculate extends Base{
    
    //获取时间函数
     function timeCalculate($type){
         //获取当前30天前到今天的数据
         switch ($type){
             case 1:
                 $time  = time();
                 $start = strtotime( date('Y-m-d 00:00:00',strtotime("-30 day",$time)) );
                 return array('start'=>$start,'end'=> $time );
              break; 
             
         }
     }
     
     // 两个日期之间的所有日期
     function prDates($dt_start,$dt_end){ 
         while ($dt_start<=$dt_end){
             $arr[]    = date('Y-m-d',$dt_start);
             $dt_start = strtotime('+1 day',$dt_start);
         }
         return $arr;
     }
     
   
    
	     
}
