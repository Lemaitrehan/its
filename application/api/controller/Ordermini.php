<?php
namespace application\api\controller;
use think\Db;
/**
* 
 */
class Ordermini{
	/**
	* 
	*/
    public function index(){
        $orderMini = model('admin/OrderMini');
        dump($orderMini->makeOrder());
        
    }
    
    /**
     * 根据课程类型，获取专业列表
     * @param type_id 1=学历 2=技能
    */
    public function getMajorList()
    {
        $params = input('post.');
        $type_id = $params['type_id'];
        $filter = ['is_show'=>1];
        $field = 'major_id,name';
        $type_id==2 && $filter['type_id']=$type_id;
        $type_id==2 && $rs = Db::name('major')->field($field)->where($filter)->select();
        $type_id==1 && $rs = Db::name('major_edu')->field($field)->where($filter)->select();
        MBISApiReturn(MBISReturn("",1,$rs));   
    }
    /**
     * 根据课程类型，获取专业列表
     * @param type_id 类型ID:1=学历 2=技能
     * @param major_id 专业ID
    */
    public function getCourseList()
    {
        $params = input('post.');
        $type_id = $params['type_id'];
        $major_id = $params['major_id'];
        $filter = ['type_id'=>$type_id,'is_shelves'=>1,'major_id'=>$major_id];
        $field = 'course_id,name,course_bn,offers_price as sale_price';
        $rs = Db::name('course')->field($field)->where($filter)->select();
        MBISApiReturn(MBISReturn("",1,$rs));   
    }
    /**
      * 写入mini订单数据
    */
    public function create()
    {
        $params = json_decode(input('post.postJson'),true);
        $orderMini = model('admin/OrderMini');
        $orderMini->makeOrder($params);
    }
    
}
