<?php
namespace application\common\model;
use think\Db;
/**
 * 学员报名记录model操作表
 */
class StudentSkill extends Base{

	/* 写入记录 */
    public function putData($data){
        $formalData = $data;
        $result = true;
        $id = 0;
        if($this->checkData($data)):
            $entryData = $this->preData($data);
            $result = $this->insert($entryData);
            $id = $this->getLastInsID();
        endif;
        $return = array(
           'status' => $result,
           'id' => $id,
           'data' => $formalData,
        );
        return $return;
    }
    /* 检查数据 */
    private function checkData($data){
        //$filter_has = ['userId'=>$data['userId'],'course_bn'=>$data['course_bn']];
        //$result = $this->where($filter_has)->value('skill_id');
        //if(!empty($result)) return false;
        return true;   
    }
    
    /* 格式化数据 */
    public function preData($data){
        $return['orderId'] = $data['orderId'];
        $return['orderNo'] = $data['orderNo'];
        $return['odd_id'] = $data['odd_id'];
        $return['school_id'] = $data['school_id'];
        $return['major_id'] = $data['major_id'];
        $return['course_id'] = $data['course_id'];
        //用户ID
        $return['userId'] = $data['userId'];
        //收款类别
        $return['receiptCate'] = $data['receiptCate'];
        //学校名称
        $return['school_name'] = $data['school_name'];
        //层次
        $return['level_name'] = $data['level_name'];
        //报读专业
        $return['major_name'] = $data['major_name'];
        //学习形式
        $return['studyStatus'] = $data['studyStatus'];
        //课程编码
        $return['course_bn'] = $data['course_bn'];
        //课程名称
        $return['course_name'] = $data['course_name'];
        //标准学费
        $return['price'] = $data['price'];
        $return['receivable_fee'] = $data['receivable_fee'];
        $return['real_fee'] = $data['real_fee'];
        $return['discount_price'] = $data['discount_price'];
        //折前减免
        $return['discountBefore'] = $data['discountBefore'];
        $return['discountPayNameRate'] = $data['discountPayNameRate'];
        $return['discountSubjectSumRate'] = $data['discountSubjectSumRate'];
        $return['discountTeamRate'] = $data['discountTeamRate'];
        $return['discountHeadmasterRate'] = $data['discountHeadmasterRate'];
        $return['discountActivityRate'] = $data['discountActivityRate'];
        $return['discountSpecialRate'] = $data['discountSpecialRate'];
        $return['discountAfter'] = $data['discountAfter'];
        $return['confirmUserType'] = $data['confirmUserType'];
        //付款方式折扣优惠
        /**$discount2 = trim($sheet->getCell("N".$row)->getValue());
        //科目累计折扣优惠
        $discount3 = trim($sheet->getCell("O".$row)->getValue());
        //团报折扣优惠
        $discount4 = trim($sheet->getCell("P".$row)->getValue());
        //校长特权优惠
        $discount5 = trim($sheet->getCell("Q".$row)->getValue());
        //活动折扣优惠
        $discount6 = trim($sheet->getCell("R".$row)->getValue());
        //特殊折扣优惠额
        $discount7 = trim($sheet->getCell("S".$row)->getValue());
        //折后减免
        $discount8 = trim($sheet->getCell("T".$row)->getValue());**/
        //应收学费总额
        $return['deal_price'] = $data['deal_price'];
        //累计已收学费总额
        $return['total_price'] = $data['total_price'];
        //待收学费总额
        $return['wait_price'] = $data['wait_price'];
        //是否欠费
        $return['arre_type'] = $data['arre_type'];
        $return['entry_time'] = $data['entry_time'];
        $return['data_type'] = $data['data_type'];
        $return['batch_num'] = $data['batch_num'];
        $return['exam_type'] = $data['exam_type'];
        return $return;   
    }
    
}
