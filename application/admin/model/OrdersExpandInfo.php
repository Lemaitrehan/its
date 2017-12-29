<?php
namespace application\admin\model;
/**
 * 查找订单的基本 拓展数据
 */
use think\Db;
class OrdersExpandInfo extends Base{
    
    /**
     * 查找订单下面的科目的个数 或者 是 课程的个数
     * @param unknown $type
     * @param unknown $CourseOrSubject_id
     */
    public  function OrdersConfInfo($type,$CourseOrSubject_id){
        if($type == 1){ 
            $where['course_id'] = $CourseOrSubject_id;
        }elseif($type ==2){
            $where['obj_id']     = $CourseOrSubject_id;
        }else{
            return '';
        }
        $rs = Db::name('order_detail')->where($where)
                                      ->field('count(odd_id) as buyNum')
                                      ->find();
        return $rs['buyNum'];
    }
}
