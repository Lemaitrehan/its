<?php
namespace application\common\model;
use think\Db;
/**
 * 学员报名记录model操作表
 */
class StudentEdu extends Base{
    private $lastUpdateId = null;
	/* 写入记录 */
    public function putData($data){
        $formalData = $data;
        $result = true;
        $id = true;
        $entryData = $this->preData($data);
        if($this->checkData($data)):
            //用户ID+课程，存在则更新
            $entry_id = $this->isDataUpdate($data);
            if( !empty($entry_id) ):
                $this->where(['edu_id'=>$entry_id])->update($entryData);
                $result = $id = $entry_id;
            else:
                $result = $this->insert($entryData);
                $id = $this->getLastInsID();
            endif;
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
        //$filter_has = ['userId'=>$data['userId'],'loginName'=>$data['idcard']];
        //$result = $this->where($filter_has)->value('edu_id');
        //if(!empty($result)) return false;
        return true;   
    }
    /**
        @do 是否更新判定
        @desc 是否更新判定
        @param $type_id 课程类型：1=学历 2=技能 3=管理
        @param $userId 用户ID:对应users表的userId
        @param $course_bn 课程编码：对应course表的course_bn
        @return 布尔值：true/false
     */
    public function isDataUpdate($params=array(),&$msg='') {
        if(!$this->checkIsUpdateData($params,$msg)):
            return false;
        endif;
        $type_id = (int)$params['type_id'];
        $filter = ['userId'=>$params['userId'],'course_bn'=>$params['course_bn']];
        //学历类处理
        $type_id==1 && $filter['level_id']=$params['level_id'];
        $result = $this->where($filter)->find();
        if(!$result) return false;
        return $result['edu_id'];   
    }
    /* 验证传入参数是否合法 */
    private function checkIsUpdateData($params=array(),&$msg='') {
        $flag = true;
        if(empty($params['type_id'])):
           $msg = array('errcode'=>'40001','errmsg'=>'参数有误[1]');
           return false; 
        endif;
        $type_id = (int)$params['type_id'];
        if(empty($params['userId']) || empty($params['course_bn'])):
            $msg = array('errcode'=>'40001','errmsg'=>'参数有误[2]');
            $flag = false;
        endif;
        //学历单独判断
        if($type_id==1):
            if(empty($params['level_id'])):
               $msg = array('errcode'=>'40001','errmsg'=>'参数有误[3]');
               $flag = false; 
            endif;
        endif;
        return $flag;   
    }
    
    /* 格式化数据 */
    public function preData($data){
        $return['type_id'] = $data['type_id'];
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
        //应收学费总额
        $return['deal_price'] = $data['deal_price'];
        //累计已收学费总额
        $return['total_price'] = $data['total_price'];
        //待收学费总额
        $return['wait_price'] = $data['wait_price'];
        //是否欠费
        $return['arre_type'] = $data['receivable_fee']==0?'否':'是';
        $return['entry_time'] = $data['entry_time'];
        $return['data_type'] = $data['data_type'];
        $return['batch_num'] = $data['batch_num'];
        $return['exam_type'] = $data['exam_type'];
        $return['level_id'] = $data['level_id'];
        $return['level_name'] = $data['level_name'];
        return $return;   
    }
    
}
