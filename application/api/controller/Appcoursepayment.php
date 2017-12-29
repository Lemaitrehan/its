<?php
// +----------------------------------------------------------------------
// | app单个项目购买
// +----------------------------------------------------------------------
// | Author: lijianhua
// +----------------------------------------------------------------------
namespace application\api\controller;
use think\Db;
use application\admin\model\Course  AS Course;//课程类
use application\admin\model\Subject AS Subject;//课程类
class Appcoursepayment extends Base{
    
    //课程全款配置
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
    
    //科目全款配置
    function subjectPayment(){
        $subject_id   =   37;//input('post.subject_id');
        $Subject_obj  =   new Subject;
        //查找课程及下面的科目信息
        $arrCourse        = $Subject_obj->getSubjectInfo( $subject_id );
        $arrTeachingType  = $Subject_obj->arrTeachingType;
       
        $deposit_price    = $Subject_obj->deposit_price;//课程定金
        $CourseSubject    = array();//课程下面的科目信息
        $course_num       = 0;//课程下面的科目数量
        //查找科目购买数量
        $OrdersExpandInfo = new \application\admin\model\OrdersExpandInfo();
        $buyNum           = $OrdersExpandInfo->OrdersConfInfo(2, $subject_id);
        
        foreach($arrCourse as $v){
            $CourseSubject = array('subject_name'        =>$v['name'],//名称
                                   'subject_cover_img'   =>SERVERHOST.'/'.$v['cover_img'],//图片
                                   'teaching_type'       =>$arrTeachingType[$v['teaching_type']],//标准价
                                   'sale_price'          =>$v['sale_price'],//标准价
                                   'subject_market_price' =>$v['market_price'],//原价
                                   'deposit_price'       =>$deposit_price,//最少定金
                                   'buyNum'              =>$buyNum //科目购买数量
            );
        }

        MBISApiReturn( MBISReturn('科目立即购买配置数据！！！',1,$CourseSubject ) );
    }
    
//单个购买（科目 ）或者  课程
    public function buyCourseOrSubject(){
        $Course_id          = input('post.CourseOrSubject_id');//1=>科目 2=>课程
        $Subject_ids        = input('post.Subject_ids');//课程下面的科目ids
        $arr_Subject_ids    = array();
        if($Course_id){
            $arr_Subject_ids = json_decode($Subject_ids,true);
        }
        
        $Subject_id         = input('post.Subject_id');//课程下面的科目ids
        $payMoney           = input('post.payMoney');//用户提交定金
        $channelType        = input('post.channelType');//优惠类型
        $jump_type          = input('post.jump_type');//学历类或者技术类
        $userId             = input('post.userId');//学历类或者技术类
        
      /*   $Subject_id         = 37;//课程下面的科目ids
        $payMoney           = 500;//用户提交定金
        $channelType        = 2;//优惠类型
        $jump_type          = 1;//学历类或者技术类
        $userId             = 59;//学历类或者技术类  */
        
        $_POST              = array (
                                      'accesstoken' => input('accesstoken'),
                                      'no_cart'     => 1,
                                      'cartData'    =>  array (
                                                           'no_cart' => 
                                                            array (
                                                              'add_deposit_price' => input('post.add_deposit_price'),//定金
                                                              'course_id'   => $Course_id,
                                                              'subject_id'  => $Subject_id,
                                                              'subjectList' => $arr_Subject_ids,
                                                            ),
                                                           ),
                                      'channelType' => $channelType,//优惠2
                                      'jump_type'   => $jump_type,
                                      'userId'      => $userId,
                                     
                              );
        //查找科目或者 课程的 购买数量
        $OrdersExpandInfo =  new \application\admin\model\OrdersExpandInfo();
        if($Course_id){
            $type = 1;
            $CourseOrSubject_id = $Course_id;
        }else{
            $type = 2;
            $CourseOrSubject_id = $Subject_id;
        }
        $buyNum           =  $OrdersExpandInfo->OrdersConfInfo($type, $CourseOrSubject_id);
        $cartObj          =  new \application\api\controller\Carts();
        //获取优惠信息
        $res              =  $cartObj->getCartList();
        $res              =  json_decode($res,true); 
        if( $res['status'] >=1){
            $array            =  array('url'    => SERVERHOST.'/'.url(''),//优惠生成网址
                                       'buyNum' => $buyNum//已购买数量   
                                     
            );
            MBISApiReturn( MBISReturn('科目后者课程优惠信息！！！',1,$array ) );
        }
    }
    
    //优惠信息
    public function preferentialInformation(){
        return $this->fetch('index');
    }

    
    
    
   
    
}



?>
