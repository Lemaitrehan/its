<?php
namespace application\common\model;
use think\Db;
/**
 * 订单明细业务处理
 */
class OrderDetail extends Base{
    /* 写入记录 */
    public function putData($data){
        $formalData = $this->preData($data);
        $result = $this->insert($formalData);
        $odd_id = $this->getLastInsId();
        $return = array(
           'status' => $result,
           'id' => $odd_id,
           'data' => $formalData,
        );
        return $return;
    }
    
    /* 格式化数据 */
    public function preData($data){
        $time = time();
        $retrun['userId'] = $data['userId'];
        $retrun['agent_uid'] = $data['agent_uid'];
        $retrun['type_id'] = $data['type_id'];
        $retrun['orderNo'] = $data['orderNo'];
        $retrun['orderId'] = $data['orderId'];
        $return['createtime'] = !empty($return['createtime'])?$return['createtime']:$time;
        $return['lastmodify'] = !empty($return['lastmodify'])?$return['lastmodify']:$time;
        $retrun['course_id'] = $data['course_id'];
        $retrun['course_name'] = $data['course_name'];
        $retrun['obj_id'] = $data['obj_id'];
        $retrun['obj_name'] = $data['obj_name'];
        $retrun['price'] = $data['price'];
        $retrun['fee_price'] = $data['fee_price'];
        $retrun['number'] = $data['number'];
        $retrun['obj_amount'] = $data['obj_amount'];
        $retrun['obj_weight'] = '0.00';
        $retrun['score'] = 0;
        $retrun['cover_img'] = $data['cover_img'];
        $retrun['is_full_pay'] = $data['is_full_pay'];
        $retrun['course_real_price'] = $data['course_real_price'];
        $retrun['subject_offer_price'] = $data['price'];
        $retrun['deal_pay_price'] = $data['deal_pay_price'];
        $retrun['real_pay_price'] = $data['real_pay_price'];
        $retrun['remain_pay_price'] = $data['remain_pay_price'];
        $retrun['discount_aver_price'] = $data['discount_aver_price'];
        $retrun['teacher_id'] = $data['teacher_id'];
        //$retrun['extend_data'] = serialize(obj2Array($data));
        $retrun['extend_data'] = $data['extend_data'];
        return $retrun;
    }
    
}
