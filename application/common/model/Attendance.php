<?php
// +----------------------------------------------------------------------
// | APP考勤
// +----------------------------------------------------------------------
// | 由于 业务和数据 不完善 。只是做 数据而已
// +----------------------------------------------------------------------
// | Author: lijianhua
// +----------------------------------------------------------------------

namespace application\common\model;
use think\Db;

class Attendance extends Base{
    
      function grade(){
            $arr = array(
                1=>'暑假设计班',
                2=>'UI设计高级班'
            );
            return $arr;
      }
    
      //已经签名的考勤
      function alreadySigned(){
          
      
          
           
          
           $join   = [
               ['current_ckwork kq','kq.userId = user.userId','LEFT'],
           ];
           $arr   = db::name('users user')->join($join)
                                          ->where('kq.createtime',['>=',$arrTime['start']],['<=',$arrTime['end']],'or')
                                          ->field("kq.createtime,FROM_UNIXTIME(kq.createtime,'%Y-%m-%d') as dayTime")
                                          ->select();
         if(!$arr){
               
         }
         
         $arrDay = array_flip($arrDay);
         $arrNew = array();
         foreach ($arr as $k => $t ){
                 if(  isset( $arrDay[ $t['dayTime'] ]) ){
                      unset( $arrDay[ $t['dayTime'] ]);
                 }
                 $arrNew[$t['dayTime']]= array('day'=>$t['dayTime']);
                 $arrNew[$t['dayTime']]['list'][] = array('time'=>$t['createtime'],'timeString'=>date('Y-m-d H:i:s',$t['createtime']) );
         } 
         dd($arrNew);
         return $arrNew; 
      }
      
      
      function Attendancelist(){
          
          $year      =   input('post.year');//考勤年
          $month     =   input('post.month');//考勤月
          $acceptStart    =   strtotime('2017-5-1 00:00:00');
          $dayNum    =   date('t',$acceptStart);
          $acceptEnd    =   strtotime('2017-5-'.$dayNum.' 23:59:59');
          
         /*  dd(date('Y-m-d H:i:s',$acceptEnd) );
          $time      =   time();
          $nowYear   =   date('y');
          $nowYear   =   date('n'); */
          
          $timeClass = new \application\common\model\Timecalculate;
          $arrDay    = $timeClass->prDates($acceptStart,$acceptEnd);
          
          $arrTotal  =array(
              'total' => array(
                  'total'  => 20,
                  'lack'   => 1,
                  'deduct' => 20
              ),
          );
          foreach ($arrDay as $v){
           $arrTotal['day'][] =    array(
                           'timeString' => $v,
                           'timeUnix'   => strtotime($v),
                           'number'     => 2,
                           'list'       => array(
                               0=>array(
                                   'timeString' => date('Y-m-d H:i:s',time()),
                                   'timeUnix'   => time(),
                                   'lack'       => 5,
                               ),
                               1=>array(
                                   'timeString' => date('Y-m-d H:i:s',time()),
                                   'timeUnix'   => time(),
                                   'lack'       => 5,
                               )
                           )
                     );
          }
          return $arrTotal;
      }

    
	     
}
