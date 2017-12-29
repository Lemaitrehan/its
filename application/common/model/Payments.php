<?php
namespace application\common\model;
/**
 * 缴费明细model业务处理
 */
class Payments extends Base{
	public function putData($data){
        $formalData = $data;
        $result = true;
        $id = 0;
        if($this->checkData($data)):
            $formalData = $this->preData($data);
            !empty($formalData) && $result = $this->insertAll($formalData);
            //!empty($formalData) && $result = $this->insertAll($formalData);
            $id = '-1';
        endif;
        $return = array(
           'status' => $result,
           'id' => $id,
           'data' => $formalData,
        );
        return $return; 
    }
    /* 检查数据 */
    private function checkData(&$datas){
        /*foreach($datas as $k=>$data):
            $filter_has = ['payment_id'=>$data['payment_id']];
            $result = $this->where($filter_has)->value('payment_id');
            if(!empty($result)):
                //$this->where($filter_has)->delete();
                unset($datas[$k]);
            endif;
        endforeach;*/
        return true;   
    }
    /* 格式化数据 */
    public function preData($datas){
        
        $returns = array();
        foreach($datas as $data):
            $return['payment_id'] = getPaymentId();
            $return['orderId'] = $data['orderId'];
            $return['orderNo'] = $data['orderNo'];
            $return['receiptCate'] = $data['receiptCate'];
            $return['receiptSchool'] = $data['receiptSchool'];
            $return['receiptPrice'] = $data['money'];
            $return['receiptDate'] = $data['receiptDate'];
            $return['receiptNo'] = $data['receiptNo'];
            $return['course_bn'] = $data['course_bn'];
            //$return['payType'] = $data['payType'];
            //$return['payWay'] = $data['payWay'];
            $return['procRate'] = $data['procRate'];
            $return['procFee'] = $data['procFee'];
            $return['realPayFee'] = $data['realPayFee'];
            $return['status'] = $data['status'];
            $return['pay_name'] = $data['pay_name'];
            $return['pay_type'] = $data['pay_type'];
            $return['userId'] = $data['userId'];
            //$return['payment_id'] = $data['payment_id'];
            $return['type_id'] = $data['type_id'];
            $return['data_type'] = $data['data_type'];
            $return['batch_num'] = $data['batch_num'];
            $return['status'] = $data['status'];
            $return['money'] = $data['money'];
            $return['cur_money'] = $data['cur_money'];
            $returns[] = $return;
        endforeach;
        return $returns;
    }
}
