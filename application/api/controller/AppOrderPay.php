<?php
// +----------------------------------------------------------------------
// | app购物车
// +----------------------------------------------------------------------
// | Author: lijianhua
// +----------------------------------------------------------------------
namespace application\api\controller;
use think\Db;
use application\admin\model\Course AS Course;//课程类
class Appcoursepayment extends Base{
    
    //科目全款配置
    function coursePayment(){
           $Course_obj  =   new Course;
           //查找课程及下面的科目信息
           $arrCourse   =   $Course_obj->getCourseDetails(input('post.course_id'));//
           $deposit_price = $Course_obj->deposit_price;//课程定金
           $CourseInfo  =   array();//课程基本信息
           $CourseSubject = array();//课程下面的科目信息
           $course_num  = 0;//课程下面的科目数量
           foreach($arrCourse as $v){
               
               $CourseInfo = array('course_name'         =>$v['course_name'],//名称
                                   'course_cover_img'    =>SERVERHOST.'/'.$v['course_cover_img'],//图片
                                   'sale_price'          =>$v['sale_price'],//标准价
                                   'course_market_price' =>$v['course_market_price'],//原价
                                   'course_num'          =>++$course_num,//科目数
                                   'deposit_price'       =>$deposit_price,//最少定金
                             );
               $CourseSubject[] = array(
                                    'subject_name'         => $v['name'],
                                    'subject_market_price' => $v['market_price'] ,
                                    'subject_sale_price'   => $v['sale_price'] ,
                                    'subject_course_hours' => $v['course_hours'],
               );
               
           }
           
           $array =  array('CourseInfo'    => $CourseInfo,
                           'CourseSubject' => $CourseSubject,
                           'deposit_price' => $deposit_price
           );
           MBISApiReturn( MBISReturn('科目全款配置数据！！！',1,$array ) );
    }
    
    //单个购买（科目 ）
    public function buyCourseOrSubject(){
        
        
        $type               = input('post.type');//1=>科目 2=>课程
        $CourseOrSubject_id = input('post.CourseOrSubject_id');//1=>科目 2=>课程
        $payMoney           = input('post.payMoney');//用户提交金额
        
        //查找科目或者 课程的 购买数量
        $OrdersExpandInfo =  new \application\admin\model\OrdersExpandInfo();
        $buyNum           =  $OrdersExpandInfo->OrdersConfInfo();
        dd($buyNum);
        //查找优惠信息
        $array = array(
                    'discount_text'  =>'1.满500元减50元\n 2.全款付款减免 100元\n',
                    'Discount_money' => 30
        );
        
        
        MBISApiReturn( MBISReturn('科目后者课程优惠信息！！！',1,$array ) );
    }
    
    
   
    
}



?>
