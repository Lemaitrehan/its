<?php
namespace application\common\model;
use think\Db;
use think\Route;
use think\Request;
/**
 * 订单业务处理类
 */
class Orders extends Base{
    
    /**
	 * 创建订单
     * @type 1=前台下单  2=导入下单 99=后后下单
	 */
	public function getApiCreateOrder($type=1,$params=[],$userInfo=[]){
       try{
        if(empty($params['cartData'])) return MBISReturn("购物车数据为空");
        $isImportFlag = $isAdminFlag = false;
        //导入标识
        $type==2 && $isImportFlag = true;
        //后台标识
        $type==99 && $isAdminFlag = true;
        $params['orderData']['orderInfo']['pmt_order'] = 0;
        $params['type_id'] = $params['jump_type'];
        //$orderunique = MBISOrderQnique();
        $orderNo = MBISOrderNo();
        $userId = $params['userId'];
        $agent_uid = 0;
        $userType = $userInfo['userType'];//0=普通学员 1=老师 2=咨询师
        empty($params['system']) && $params['system'] = 'pc';
        $params['platform'] = ITSSelItemId('common','platform',$params['system'],'key');
        //代购处理
        if( !empty($userInfo['isAgentUser']) )
        {
            $userId = 0;
            $agent_uid = $params['userId'];
            $filter_cart['userId|agent_uid'] = $agent_uid; 
        }
        else
        {
            $filter_cart['userId'] = $userId;   
        }
        //dump($userInfo);exit;
        //购物车数据
        $type_id = $params['jump_type'];
        $cartData = $params['cartData'];
        if(!empty($userInfo['isAgentUser']) && empty($params['orderData']['orderInfo']['name'])) return MBISReturn('请输入客户姓名');  
        if(!empty($userInfo['isAgentUser']) && empty($params['orderData']['orderInfo']['mobile'])) return MBISReturn('请输入客户手机号');  
        if(!empty($userInfo['isAgentUser']) && !MBISIsPhone($params['orderData']['orderInfo']['mobile'])) return MBISReturn('客户手机号格式有误');  
        $cartOrderData = model('common/carts')->getApiCartList(2,$params,$userInfo,($isImportFlag||$isAdminFlag)?$params['cartData']:[]);
        
        if(isset($cartOrderData['status']) && $cartOrderData['status']==-1)
            return MBISReturn($cartOrderData['msg']);
        if(empty($cartOrderData['courseInfo']))
            return MBISReturn('下单数据有误[courseInfo]');   
      
        Db::startTrans();
        //系统处理返回数据
        $orderInfo = $cartOrderData['orderInfo'];
        if( !empty($userInfo['isAgentUser']) )
        {
            #咨询师代购
            !empty($orderInfo['idcard']) && $userId = $this->set_user_account($orderInfo);
            #咨询师自己购买
            empty($orderInfo['idcard']) && $userId = $params['userId'];
            $agent_uid = $params['userId'];
        }
        /** 订单信息主表 **/
        $order['orderNo'] = $orderNo;
        $order['userId'] = $userId;
        $order['agent_uid'] = $agent_uid;
        $order['buyType'] = ($isImportFlag||$isAdminFlag)?0:1; //购买方式：0=直销订单，1=代销订单
        //$order['orderStatus'] = 0;
        $order['confirmStatus'] = ($isImportFlag||$isAdminFlag)?1:0; //确认状态：0=未确认  1=已确认  2=已取消
        $order['payStatus'] = ($isImportFlag||$isAdminFlag)?1:0; //付款状态：0=未付款  1=已付款  2=已退款
        $order['isAppraise'] = 0;
        $order['createtime'] = $order['lastmodify'] = time();
        $order['platform'] = $params['platform']; //平台标识：1=pc，2=wap，3=android，4=ios
        $order['dataType'] = $type; //数据类型：1=正常下单  2=导入数据 99=后台录入
        $isImportFlag && $order['data_type'] = 1;
        $isImportFlag && $order['batch_num'] = 1;
        //支付方式
        $paymentInfo = $params['orderData']['paymentInfo'];
        $keys_payment = array_keys($paymentInfo);
        $order['payType'] = $keys_payment[0];
        $order['payFrom'] = $paymentInfo[$order['payType']];
        $order['payType']==2 && $order['payStatus'] = 1; //线下支付，则已付款
        //合并数据
        $order = array_merge($order,$orderInfo);
        //teamLists
        if(isset($order['teamLists']))
        {
            $teamLists = $order['teamLists'];
            unset($order['teamLists']);
        }
        //userCondition
        if(isset($order['userCondition']))
        {
            $userCondition = $order['userCondition'];
            unset($order['userCondition']);
        }
        //okRule
        if(isset($order['okRule']))
        {
            $okRule = $order['okRule'];
            unset($order['okRule']);
        }
        //okRuleLists
        if(isset($order['okRuleLists']))
        {
            $okRuleLists = $order['okRuleLists'];
            unset($order['okRuleLists']);
        }
        //noRuleLists
        if(isset($order['noRuleLists']))
        {
            $noRuleLists = $order['noRuleLists'];
            unset($order['noRuleLists']);
        }
        if(isset($order['waitRuleLists']))
        {
            //$noRuleLists = $order['noRuleLists'];
            unset($order['waitRuleLists']);
        }
        //noRuleLists
        if(isset($order['full_pay_price']) && isset($order['full_pay_price_tmp']))
        {
            unset($order['full_pay_price']);
            $order['full_pay_price'] = $order['full_pay_price_tmp'];
            #unset($order['full_pay_price']);
        }
        if(isset($order['notfull_pay_price']))
        {
            
        }
        if(isset($order['notfull_deal_price']))
        {
            #unset($order['notfull_deal_price']);
        }
        $entry_time = 0;
        if(isset($order['entry_time']))
        {
            $entry_time = $order['entry_time'];
            unset($order['entry_time']);
        }
        #dump($order);exit;
        $result = $this->data($order,true)->isUpdate(false)->allowField(true)->save($order);
        $orderId = $this->orderId;
        /** 订单明细数据 **/
        $course_detail = [];
        $aditem_detail = [];
        $courseInfo = $cartOrderData['courseInfo'];
        $obj_ids = [];
        #echo '<pre>';var_export($courseInfo);exit;
        foreach($courseInfo as $k=>$v)
        {
            //课程/科目明细
            $course_data = [];
            $course_one = [];
            $course_one['userId'] = $course_data['userId'] = $userId;
            $course_one['agent_uid'] = $course_data['agent_uid'] = $agent_uid;
            $course_one['type_id'] = $course_data['type_id'] = $orderInfo['type_id'];
            $course_one['orderNo'] = $course_data['orderNo'] = $orderNo;
            $course_one['orderId'] = $course_data['orderId'] = $orderId;
            $course_one['createtime'] = $course_one['lastmodify'] = $course_data['createtime'] = $course_data['lastmodify'] = time();
            //课程或科目信息
            $course = $v['course'];
            if($course['course_id'] > 0)//课程
            {
                if($type_id==1)
                {
                    //$type==2 && $course['deal_pay_price'] = getNumFormat($course['deal_pay_price']-$course['discount_aver_price']);
                    //$type==2 && $course['real_pay_price'] = getNumFormat($course['real_pay_price']-$course['discount_aver_price']);
                    $course_data['course_id'] = $course['course_id'];
                    $course_data['course_name'] = $course['course_name'];
                    $course_data['obj_id'] = $course['grade_id'];
                    $course_data['obj_name'] = $course['grade_name'];
                    $course_data['price'] = $course['stu_fee'];
                    $course_data['fee_price'] = $course['offers'];
                    $course_data['number'] = $course['cartNum'];
                    $course_data['obj_amount'] = $course['stu_fee'];
                    $course_data['obj_weight'] = '0.00';
                    $course_data['score'] = 0;
                    $course_data['cover_img'] = $course['cover_img'];
                    $course_data['is_full_pay'] = $course['is_full_pay'];
                    $course_data['course_real_price'] = $course['course_real_price'];
                    $course_data['subject_offer_price'] = $course['price'];
                    $course_data['deal_pay_price'] = $course['deal_pay_price'];
                    $course_data['real_pay_price'] = $course['real_pay_price'];
                    $course_data['remain_pay_price'] = $course['remain_pay_price']-$course['discount_aver_price'];
                    $course_data['discount_aver_price'] = $course['discount_aver_price'];
                    $course_data['teacher_id'] = $course['teacher_id'];
                    $course_detail[] = $course_data;
                    $course_data['extend_data'] = serialize(obj2Array($course));
                    $last_insert_id = Db::name('order_detail')->insert($course_data,false,true);
                    if($course_data['is_full_pay']==0)
                    {
                        $obj_ids[] = $last_insert_id;
                    }  
                    //$odd_id = $course['course_id'];
                    $odd_id = $last_insert_id;
                }
                else
                {
                    //课程
                    $course_one['course_id'] = $course['course_id'];
                    $course_one['course_name'] = $course['name'];
                    $course_one['obj_id'] = 0;
                    $course_one['obj_name'] = '';
                    $course_one['price'] = $course['total_sale_price'];
                    $course_one['fee_price'] = $course['offers_price'];
                    $course_one['number'] = $course['cartNum'];
                    $course_one['obj_amount'] = $course['total_sale_price'];
                    $course_one['obj_weight'] = '0.00';
                    $course_one['score'] = 0;
                    $course_one['cover_img'] = $course['cover_img'];
                    $course_one['is_full_pay'] = $course['is_full_pay'];
                    $course_one['course_real_price'] = $course['course_real_price'];
                    $course_one['subject_offer_price'] = $course['price'];
                    $course_one['deal_pay_price'] = $course['deal_pay_price'];
                    $course_one['real_pay_price'] = $course['real_pay_price'];
                    $course_one['remain_pay_price'] = $course['remain_pay_price'];
                    $course_one['discount_aver_price'] = $course['discount_aver_price'];
                    $course_one['pmt_order_aver_price'] = $course['pmt_order_aver_price'];
                    $course_one['online_course_price'] = $course['online_course_price'];
                    $course_one['extend_data'] = serialize(obj2Array($course));
                    $course_detail[] = $course_one;
                    Db::name('order_detail')->insert($course_one,false,true);
                    $odd_id = $course['course_id'];
                    //科目列表
                    $subjectList = $v['subjectList'];
                    foreach($subjectList as $kk_subject=>$vv_subject)
                    {
                        $course_data['course_id'] = $course['course_id'];
                        $course_data['course_name'] = $course['name'];
                        $course_data['obj_id'] = $vv_subject['subject_id'];
                        $course_data['obj_name'] = $vv_subject['name'];
                        $course_data['price'] = $vv_subject['sale_price'];
                        $course_data['fee_price'] = $vv_subject['offer_price'];
                        $course_data['number'] = $course['cartNum'];
                        $course_data['obj_amount'] = $vv_subject['sale_price'];
                        $course_data['obj_weight'] = '0.00';
                        $course_data['score'] = 0;
                        $course_data['cover_img'] = $vv_subject['cover_img'];
                        $course_data['is_full_pay'] = $vv_subject['is_full_pay'];
                        //$course_data['course_real_price'] = $vv_subject['course_real_price'];
                        $course_data['subject_offer_price'] = $vv_subject['subject_offer_price'];
                        $course_data['deal_pay_price'] = $vv_subject['deal_pay_price'];
                        $course_data['real_pay_price'] = $vv_subject['real_pay_price'];
                        $course_data['remain_pay_price'] = $vv_subject['remain_pay_price'];
                        $course_data['discount_aver_price'] = $vv_subject['discount_aver_price'];
                        $course_data['deposit_aver_price'] = $vv_subject['deposit_aver_price'];
                        $course_data['pmt_order_aver_price'] = $vv_subject['pmt_order_aver_price'];
                        $course_data['teacher_id'] = $vv_subject['teacher_id'];
                        $course_data['extend_data'] = serialize(obj2Array($vv_subject));
                        $course_detail[] = $course_data;
                        $last_insert_id = Db::name('order_detail')->insert($course_data,false,true);
                        if($course_data['is_full_pay']==0)
                        {
                            $obj_ids[] = $last_insert_id;
                        }
                    }
                    //线上课程处理，导入或后台添加不做处理
                    if( !$isImportFlag && !$isAdminFlag && !empty($v['onlineSubjectList']))
                    {
                        $onlineSubjectList = $v['onlineSubjectList'];
                        foreach($onlineSubjectList as $kk_ol_subject=>$vv_subject)
                        {
                            $course_data['course_id'] = $course['course_id'];
                            $course_data['course_name'] = $course['name'];
                            $course_data['obj_id'] = $vv_subject['subject_id'];
                            $course_data['obj_name'] = $vv_subject['name'];
                            $course_data['price'] = $vv_subject['sale_price'];
                            $course_data['fee_price'] = $vv_subject['offer_price'];
                            $course_data['number'] = 1;
                            $course_data['obj_amount'] = $vv_subject['sale_price'];
                            $course_data['obj_weight'] = '0.00';
                            $course_data['score'] = 0;
                            $course_data['cover_img'] = $vv_subject['cover_img'];
                            $course_data['is_full_pay'] = 1;
                            //$course_data['course_real_price'] = $vv_subject['course_real_price'];
                            $course_data['subject_offer_price'] = $vv_subject['online_price'];
                            $course_data['deal_pay_price'] = $vv_subject['online_price'];
                            $course_data['real_pay_price'] = $vv_subject['online_price'];
                            $course_data['remain_pay_price'] = '0.00';
                            $course_data['discount_aver_price'] = '0.00';
                            $course_data['deposit_aver_price'] = '0.00';
                            $course_data['pmt_order_aver_price'] = '0.00';
                            $course_data['teacher_id'] = $vv_subject['teacher_id'];
                            $course_data['obj_type'] = $vv_subject['obj_type'];
                            $course_data['extend_data'] = serialize(obj2Array($vv_subject));
                            $course_detail[] = $course_data;
                            $last_insert_id = Db::name('order_detail')->insert($course_data,false,true);
                        }
                    }
                }
            }
            else //科目
            {
                $course_data['course_id'] = 0;
                $course_data['course_name'] = '';
                $course_data['obj_id'] = $course['subject_id'];
                $course_data['obj_name'] = $course['name'];
                $course_data['price'] = $course['sale_price'];
                $course_data['fee_price'] = $course['offer_price'];
                $course_data['number'] = $course['cartNum'];
                $course_data['obj_amount'] = $course['sale_price']*$course_data['number'];
                $course_data['obj_weight'] = '0.00';
                $course_data['score'] = 0;
                $course_data['cover_img'] = $course['cover_img'];
                $course_data['is_full_pay'] = $course['is_full_pay'];
                $course_data['course_real_price'] = $course['course_real_price'];
                $course_data['subject_offer_price'] = $course['price'];
                $course_data['deal_pay_price'] = $course['deal_pay_price'];
                $course_data['real_pay_price'] = $course['real_pay_price'];
                $course_data['remain_pay_price'] = $course['remain_pay_price'];
                $course_data['discount_aver_price'] = $course['discount_aver_price'];
                $course_data['deposit_aver_price'] = $course['deposit_aver_price'];
                $course_data['pmt_order_aver_price'] = $course['pmt_order_aver_price'];
                $course_data['teacher_id'] = $course['teacher_id'];
                $course_data['obj_type'] = $course['obj_type'];
                $course_data['extend_data'] = serialize($course);
                $last_insert_id = Db::name('order_detail')->insert($course_data,false,true);
                $odd_id = $last_insert_id;
                $course_detail[] = $course_data;
                if($course_data['is_full_pay']==0)
                {
                    $obj_ids[] = $last_insert_id;
                }  
            }
            //学杂费信息判断
            if(!empty($v['adItemList']))
            {
                //学杂费明细
                $aditem_data = [];
                $aditem_data['orderNo'] = $orderNo;
                $aditem_data['type_id'] = $orderInfo['type_id'];
                $aditem_data['orderId'] = $orderId;
                $aditem_data['createtime'] = $aditem_data['lastmodify'] = time();
                $adItemList = $v['adItemList'];
                foreach($adItemList as $kk_aditem=>$vv_aditem)
                {
                    $aditem_data['obj_id'] = $odd_id;
                    $aditem_data['it_id'] = $vv_aditem['it_id'];
                    $aditem_data['it_name'] = $vv_aditem['name'];
                    $aditem_data['it_price'] = $vv_aditem['price'];
                    $aditem_data['it_feel'] = '0.00';
                    $aditem_detail[] = $aditem_data;
                }
            }
        }
        //批量写入学杂费
        if(!empty($aditem_detail))
        {
            Db::name('order_aditem')->insertAll($aditem_detail);   
        }
        //团购信息处理
        if(!empty($teamLists))
        {
            $team_detail = [];
            foreach($teamLists as $k=>$v)
            {
                 if(!empty($v['name']) && !empty($v['mobile']) && !empty($v['idcard']))
                 {
                     $team_detail[] = [
                        'userId' => $userId,
                        'agent_uid' => $agent_uid,
                        'type_id' => $type_id,
                        'orderId' => $orderId,
                        'orderNo' => $orderNo,
                        'name' => $v['name'],
                        'mobile' => $v['mobile'],
                        'idcard' => $v['idcard'],
                        'isMain' => $k==0?1:0,
                        'createtime' => time(),
                        'lastmodify' => time(),
                     ];  
                 }
            }
            Db::name('order_team_log')->insertAll($team_detail);
        }
        //促销规则处理
        if(!empty($okRuleLists))
        {
            $rule_detail = [];
            foreach($okRuleLists as $k=>$v)
            {
                 $rule_detail[] = [
                    'rule_id' => $v['rule_id'],
                    'platform_use' => ITSSelItemId('common','platform',$params['system'],'key'),
                    'name' => $v['name'],
                    'description' => $v['description'],
                    'from_time' => $v['from_time'],
                    'to_time' => $v['to_time'],
                    'member_lv_ids' => $v['member_lv_ids'],
                    'member_type_ids' => $v['member_type_ids'],
                    'conditions' => $v['conditions_old'],
                    'action_conditions' => $v['action_conditions'],
                    'stop_rules_processing' => $v['stop_rules_processing'],
                    'sort_order' => $v['sort_order'],
                    'action_solution' => $v['action_solution_old'],
                    'c_template' => $v['c_template'],
                    's_template' => $v['s_template'],
                    'discount_price' => $v['discount_price'],
                    'userId' => $userId,
                    'agent_uid' => $agent_uid,
                    'type_id' => $type_id,
                    'orderId' => $orderId,
                    'orderNo' => $orderNo,
                    'createtime' => time(),
                    'lastmodify' => time(),
                 ];  
            }
            Db::name('order_rule_log')->insertAll($rule_detail);
        }
        //线上付款 >> 收款单生成
        if($order['payType']==1 || $order['payType']==2)
        {
            $payment_id = getPaymentId();
            $payment_data = [
                'payment_id' => $payment_id,
                'type_id' => $type_id,
                'orderId' => $orderId,
                'orderNo' => $orderNo,
                'obj_ids' => !empty($obj_ids)?json_encode($obj_ids):'',
                'money' => $order['realPayMoney'],
                'cur_money' => $order['realPayMoney'],
                'userId' => $userId,
                'agent_uid' => $agent_uid,
                'status' => ($isImportFlag||$isAdminFlag)?'succ':'ready', //导入或后台添加设置为succ
                'pay_name' => '',
                'pay_type' => $order['payType'],
                'pay_app_id' => $order['payFrom'],
                't_payed' => ($isImportFlag||$isAdminFlag)?$order['realPayMoney']:'0.00',
                'op_id' => '0',
                't_begin' => ($isImportFlag||$isAdminFlag)?$entry_time:time(),
                'ip' => request()->ip(),
                'trade_no' => '',
                'thirdparty_account' => '',
            ];
            Db::name('payments')->insert($payment_data);
        }
        #更新合同信息
        if(!empty($params['signPicUrl']))
        {
            //代购处理
            $filter_contract['useType'] = 1;
            $filter_contract['isUse'] = 0;
            $filter_contract['userSignImg'] = $params['signPicUrl'];
            if( !empty($userInfo['isAgentUser']) )
            {
                $userId = 0;
                $agent_uid = $params['userId'];
                $filter_contract['userId|agent_uid'] = $agent_uid; 
            }
            else
            {
                $filter_contract['userId'] = $userId;   
            }
            $rs_contract = Db::name('order_contract_log')->where($filter_contract)->order('lastmodify desc')->select();
            if(!empty($rs_contract[0]['log_id']))
            {
                $contract_data = [
                    'orderId' => $orderId,
                    'orderNo' => $orderNo,
                    'isUse' => 1,
                    'lastmodify' => time(),
                ];
                Db::name('order_contract_log')->update($contract_data,['log_id'=>$rs_contract[0]['log_id']]);
            }
        }
        //建立订单记录
        $logOrder = [];
        $logOrder['orderId'] = $orderId;
        $logOrder['orderStatus'] = 0;
        $logOrder['logContent'] = "下单成功，等待用户支付";
        $logOrder['logUserId'] = $userId;
        $logOrder['logType'] = 0;
        $logOrder['logTime'] = date('Y-m-d H:i:s');
        Db::name('log_orders')->insert($logOrder);
        //删除已选的购物车商品
        !empty($userInfo['isAgentUser']) && $userId = $agent_uid;
        if(!isset($params['nodelcart'])):
            if(!empty($params['cartData']))
            {
                $cartData = $params['cartData'];
                $tmp_cartId = [];
                //获取选中的购物车项
                foreach($cartData as $cartId=>$v)
                {
                    $tmp_cartId[] = $cartId;   
                }
                $params['cartId'] = ['in',$tmp_cartId];
            }
            $filter_cart['cartId'] = $params['cartId'];
            Db::name('carts')->where($filter_cart)->delete();
        endif;
	    Db::commit();
        $order['createtime_format'] = date('Y-m-d H:i',$order['createtime']);
        $order['payTypeName'] = ITSGetPayTypeName($order['payType']);
        $order['payFromName'] = ITSGetPayFromName($order['payFrom']);
        $order['payType']==1 && $order['payment_id'] = $payment_id;
        $order = array_merge($order,$this->pre_order_detail_data($orderId,$order));
        ($isImportFlag||$isAdminFlag) && $this->set_entry_data(['type'=>$type,'type_id'=>$type_id,'orderId'=>$orderId,'entry_time'=>$entry_time]);
	    return MBISReturn("提交订单成功", 1,$order);
      }
      catch(\Exception $e) 
      {
        Db::rollback();
        add_logs('exception/order_create','',true,$e);
        return MBISReturn('提交订单失败');
      }
    }
    
    /* 插入数据 */
    public function putData($data){
        $formalData = $this->preData($data);
        $result = $this->save($formalData);
        $orderId = $this->orderId;
        $return = array(
           'status' => $result,
           'id' => $orderId,
           'data' => $formalData,
        );
        return $return;
    }
    /* 格式化数据 */
    private function preData($data){
        $time = time();
        $return['orderNo'] = MBISOrderNo();
        $return['type_id'] = $data['type_id'];
        $return['userId'] = $data['userId'];
        $return['agent_uid'] = $data['agent_uid'];
        $return['buyType'] = $data['buyType']; //购买方式：0=直销订单，1=代销订单
        $return['name'] = $data['name'];
        $return['mobile'] = $data['mobile'];
        $return['idcard'] = $data['idcard'];
        $return['confirmStatus'] = $data['confirmStatus']; //确认状态：0=未确认  1=已确认  2=已取消
        $return['payStatus'] = $data['payStatus']; //付款状态：0=未付款  1=已付款  2=已退款
        $return['isAppraise'] = 0;
        $return['createtime'] = !empty($return['createtime'])?$return['createtime']:$time;
        $return['lastmodify'] = !empty($return['lastmodify'])?$return['lastmodify']:$time;
        $return['platform'] = $data['platform']; //平台标识：1=pc，2=wap，3=android，4=ios
        $return['dataType'] = $data['dataType']; //数据类型：1=正常下单  2=导入数据 99=后台录入
        $return['data_type'] = $data['data_type'];
        $return['batch_num'] = $data['batch_num'];
        $return['courseMoney'] = $data['courseMoney'];
        $return['totalMoney'] = $data['totalMoney'];
        $return['realTotalMoney'] = $data['realTotalMoney'];
        $return['realPayMoney'] = $data['realPayMoney'];
        $return['discountMoney'] = $data['discountMoney'];
        $return['depositRemainMoney'] = $data['depositRemainMoney'];
        $return['full_pay_price'] = $data['full_pay_price'];
        $return['notfull_pay_price'] = $data['notfull_pay_price'];
        $return['notfull_deal_price'] = $data['notfull_deal_price'];
        $return['orderRemarks'] = $data['orderRemarks'];
        $return['platform'] = '1';
        $return['channelType'] = '-1';
        //支付方式
        $return['payType'] = $data['payType'];
        $return['payFrom'] = $data['payFrom'];
        $return['payTime'] = $data['payTime'];
        $return['payType']==2 && $return['payStatus'] = 1; //线下支付，则已付款
        
        return $return;  
    }
    
    //用户订单列表
    public function getApiOrderLists(){
        checkLogin();
        $params = input('post.');
        /*if(empty($params['jump_type']))
        {
            return MBISReturn("缺少参数[jump_type]");
        }*/
        if(empty($params['userId']))
        {
            return MBISReturn("请先登录"); 
        }
        $rs['selItem'] = ITSGetSelData('order','order_status');
        //$params['type_id'] = $params['jump_type'];
        $params['type_id'] = -1;
        $params['field'] = 'type_id,orderId,orderNo,realTotalMoney as deal_pay_price,realPayMoney  as real_pay_price,depositRemainMoney as remain_pay_price,discountMoney,adItMoney,payType,payFrom,createtime,confirmStatus,orderType,payStatus,agent_uid,channelType';
        $orderLists = $this->get_lists($params);
        $rs['orderLists'] = $this->merge_order_detail($params['type_id'],$orderLists);
        return MBISReturn("",1,$rs);
	}
    
    //用户订单详情
    public function getApiOrderDetail(){
        checkLogin();
        $params = input('post.');
        /*if(empty($params['jump_type']))
        {
            return MBISReturn("缺少参数[jump_type]");
        }*/
        if(empty($params['userId']))
        {
            return MBISReturn("请先登录"); 
        }
        if(empty($params['orderId']))
        {
            return MBISReturn("参数有误[orderId]"); 
        }
        $params['field'] = 'type_id,orderId,orderNo,realTotalMoney as deal_pay_price,realPayMoney  as real_pay_price,depositRemainMoney as remain_pay_price,discountMoney,adItMoney,payType,payFrom,createtime,confirmStatus,orderType,payStatus';
        $rs = $this->get_info($params);
        $tmp_order_detail = $this->get_course_detail(['orderId'=>$rs['orderId']]);
        $rs['courseList'] = $tmp_order_detail[$rs['orderId']];
        return MBISReturn("",1,$rs);
	}
    
    /**
     * 合并订单明细
    */
    public function merge_order_detail($type_id,$orderLists)
    {
        //dump($orderLists);exit;
        $oids = [];
        foreach($orderLists['lists'] as $k=>$v)
        {
            $oids[] = $v['orderId']; 
        }
        if(empty($oids)) return [];
        $orderLists = $this->implode_course_info($orderLists,$oids);
        #$tmp_order_detail = $this->get_course_detail(['orderId'=>$oids]);
        #$tmp_order_rule_detail = $this->get_order_rule_detail(['orderId'=>$oids]);
        #$tmp_order_team_detail = $this->get_order_team_detail(['orderId'=>$oids]);
        foreach($orderLists['lists'] as &$v)
        {
            # 课程科目列表 #
            #$v['courseLists'] = isset($tmp_order_detail[$v['orderId']])?$tmp_order_detail[$v['orderId']]:[];
            # 团购列表 #
            #$v['orderTeamLists'] = isset($tmp_order_team_detail[$v['orderId']])?$tmp_order_team_detail[$v['orderId']]:[];  
            # 订单促销列表 #
            #$v['orderRuleLists'] = isset($tmp_order_rule_detail[$v['orderId']])?$tmp_order_rule_detail[$v['orderId']]:[];  
        }
        return $orderLists;
        //dump($tmp_rs);exit;
    }
    
    /**
     * 合并课程名称
    */
    public function implode_course_info($orderLists,$oids=[])
    {
        $tmp_order_detail = $this->get_course_detail(['orderId'=>$oids]);
        foreach($orderLists['lists'] as &$v)
        {
            # 课程科目列表 #
            $order_detail_lists = isset($tmp_order_detail[$v['orderId']])?$tmp_order_detail[$v['orderId']]:[];
            $v['courseNums'] = count($order_detail_lists);
            $v['courseNames'] = '';
            $arr_course = [];
            foreach($order_detail_lists as $kk=>$vv)
            {
               $arr_course[] = $vv['course_name'];    
            }
            if(!empty($arr_course))
            {
               $v['courseNames'] = implode(',',$arr_course);    
            }
        }
        return $orderLists;   
    }
    
    /**
     * 订单课程/科目/年级明细
    */
    public function get_course_detail($params=[])
    {
        $fromType = isset($params['fromType'])?$params['fromType']:'';
        $filter_order['obj_type']=!empty($params['obj_type'])?(int)$params['obj_type']:1;
        !empty($params['type_id']) && $filter_order['type_id']=$params['type_id'];
        if(is_array($params['orderId']))
        {
            $filter_order['orderId'] = ['in',$params['orderId']];
        }
        else
        {
            $filter_order['orderId'] = $params['orderId'];   
        }
        if(isset($params['rule_use']) && $params['rule_use']==2)
        {
            $filter_order['odd_id'] = ['in',$params['odd_id']]; 
        }
        //是否全款
        isset($params['is_full_pay']) && $filter_order['is_full_pay'] = $params['is_full_pay'];
        $rs = Db::name('order_detail')->where($filter_order)->select();
        $tmp_order_detail = [];
        $adItemLists = [];
        foreach($rs as $k=>$v)
        {
           $course_info = [];
           if($v['type_id']==1)//学历
           {
               if($fromType=='admin' && !empty($v['extend_data']))
               {
                   $course_info = array_merge(unserialize($v['extend_data']),$course_info);
                   $course_info['odd_id'] = $v['odd_id'];  
               }
               $obj_id = $v['course_id'];
               $course_info['course_name'] = $v['course_name'];
               $course_info['price'] = $v['obj_amount'];
               $course_info['fee_price'] = $v['fee_price'];
               $course_info['course_real_price'] = $v['deal_pay_price'];
               $course_info['deal_pay_price'] = $v['deal_pay_price'];
               $course_info['real_pay_price'] = $v['real_pay_price'];
               $course_info['remain_pay_price'] = $v['remain_pay_price'];
               $course_info['discount_aver_price'] = $v['discount_aver_price'];
               $course_info['deposit_aver_price'] = $v['deposit_aver_price'];
               $course_info['pmt_order_aver_price'] = $v['pmt_order_aver_price'];
               $course_info['cover_img'] = $v['cover_img'];
               $course_info['is_full_pay'] = $v['is_full_pay'];
               $course_info['is_full_pay_name'] = ITSSelItemName('common','is_full_pay',$v['is_full_pay']);
           }
           else
           {
               $subjectLists = [];
               if(!isset($params['rule_use']) && $v['course_id'] > 0 && $v['obj_id']>0) 
               {
                   continue;   
               }
               if($v['course_id'] > 0 && $v['obj_id']==0)//课程
               {
                   $course_info['course_name'] = $v['course_name'];
                   $course_info['deal_pay_price'] = $v['deal_pay_price'];
                   $course_info['real_pay_price'] = $v['real_pay_price'];
                   $course_info['remain_pay_price'] = $v['remain_pay_price'];
                   $course_info['is_full_pay'] = $v['is_full_pay'];
                   $course_info['is_full_pay_name'] = ITSSelItemName('common','is_full_pay',$v['is_full_pay']);
                   $obj_id = $v['course_id'];
                   //科目列表
                   $subjectLists = $this->get_subject_lists(['orderId'=>$v['orderId'],'course_id'=>$obj_id,'fromType'=>$fromType]);
               }
               elseif($v['course_id'] == 0 && $v['obj_id']>0)//科目
               {
                   if($fromType=='admin' && !empty($v['extend_data']))
                   {
                       $course_info = array_merge(unserialize($v['extend_data']),$course_info);
                       $course_info['odd_id'] = $v['odd_id'];     
                   }
                   
                   $course_info['course_name'] = $v['obj_name'];
                   $obj_id = $v['obj_id'];
                   $course_info['is_full_pay'] = $v['is_full_pay'];
                   $course_info['is_full_pay_name'] = ITSSelItemName('common','is_full_pay',$v['is_full_pay']);
               }
               $course_info['price'] = $v['obj_amount'];
               $course_info['fee_price'] = $v['fee_price'];
               $course_info['course_real_price'] = $v['deal_pay_price'];
               $course_info['discount_aver_price'] = $v['discount_aver_price'];
               $course_info['deposit_aver_price'] = $v['deposit_aver_price'];
               $course_info['pmt_order_aver_price'] = $v['pmt_order_aver_price'];
               $course_info['cover_img'] = $v['cover_img'];
           }
           
           if(!empty($subjectLists))
           {
               $course_info['subjectLists'] = $subjectLists;    
           }
           //补缴过滤掉
           if(!isset($params['rule_use']))
           {
               //学杂费列表
               $adItemLists = $this->get_aditem_detail(['orderId'=>$v['orderId'],'obj_id'=>$obj_id]);
               if(!empty($adItemLists))
               {
                   $course_info['adItemLists'] = $adItemLists;    
               }
           }
           if(!empty($course_info))
           {
               $tmp_order_detail[$v['orderId']][] = $course_info;   
           }
        }
        #dump($tmp_order_detail);exit;
        return $tmp_order_detail;
    }
    /**
     * 订单技能课程 >> 科目列表
    */
    public function get_subject_lists($params=[])
    {
        $fromType = isset($params['fromType'])?$params['fromType']:'';
        $filter_subject['orderId'] = $params['orderId'];
        $filter_subject['course_id'] = $params['course_id'];
        $filter_subject['obj_id'] = ['gt',0];
        $rs = Db::name('order_detail')->where($filter_subject)->field('*')->select();
        foreach($rs as &$v)
        {
            if($fromType=='admin' && !empty($v['extend_data']))
           {
               $v = array_merge(unserialize($v['extend_data']),$v);     
           }
            $v['is_full_pay_name'] = ITSSelItemName('common','is_full_pay',$v['is_full_pay']);
            //$v['teacher_name'] = 'Lis';
        }
        return $rs;
    }
    /**
     * 订单课程/科目/年级 >> 学杂费明细
    */
    public function get_aditem_detail($params=[])
    {
        $filter_aditem['orderId'] = $params['orderId'];
        $filter_aditem['obj_id'] = $params['obj_id'];
        $rs = Db::name('order_aditem')->where($filter_aditem)->field('it_name,it_price')->select();
        return $rs;
    }
    /**
     * 订单 >> 优惠促销明细
    */
    public function get_order_rule_detail($params=[],$rule_detail=[])
    {
        $rs = [];
        $filter['orderId'] = ['in',$params['orderId']];
        !empty($params['rule_use']) && $filter['rule_use'] = $params['rule_use'];
        empty($params['rule_tmp']) && empty($rule_detail) && $rs = Db::name('order_rule_log')->where($filter)->field('orderId,name,description,discount_price')->select();
        !empty($params['rule_tmp']) && !empty($rule_detail) && $rs = $rule_detail;
        $tmp_rs = [];
        foreach($rs as $k=>$v)
        {
            $tmp_rs[$v['orderId']][] = $v;
        }
        return $tmp_rs;
    }
    /**
     * 订单 >> 团购信息明细
    */
    public function get_order_team_detail($params=[])
    {
        $filter['orderId'] = ['in',$params['orderId']];
        $rs = Db::name('order_team_log')->where($filter)->field('orderId,name,mobile,idcard,isMain')->select();
        $tmp_rs = [];
        foreach($rs as $k=>$v)
        {
            $tmp_rs[$v['orderId']][] = $v;
        }
        return $tmp_rs;
    }
    /**
     * 格式化订单明细数据
     * @orderId 订单ID
     * @order 订单主信息
     * @rule_use 1=下单 2=补缴
     * @params 请求参数
     * @rs_course 课程、科目、订单、支付方式等等数据
    */
    private function pre_order_detail_data($orderId=0,$order=[],$rule_use=1,$params=[],$rs_course=[],$rule_detail=[])
    {
        #下单
        if($rule_use==1)
        {
            $order_detail = $this->get_course_detail(['orderId'=>$orderId]);
        }
        #补缴
        elseif($rule_use==2)
        {
            $order_detail = [];
            if(!empty($params['orders']))
            {
                $odd_ids = [];
                foreach($params['orders'] as $v)
                {
                   $odd_ids[] = $v['odd_id'];    
                }
                //$order_detail = $this->get_course_detail(['orderId'=>$orderId,'rule_use'=>$rule_use,'odd_id'=>$odd_ids]);
                if(!empty($rs_course['courseInfo'])):
                    foreach($rs_course['courseInfo'] as $k=>$v):
                        if(!empty($v['subjectList'])):
                        foreach($v['subjectList'] as $kk=>$vv):
                            $vv['obj_name'] = $vv['name'];
                            $order_detail[$orderId][$k]['subjectLists'][$kk] = $vv;
                        endforeach;
                        endif;
                    endforeach;
                endif;
            }
        }
        #echo '<pre>';var_export($order_detail);exit;
        $order_detail = $order_detail[$orderId];
        $tmp_full_order_detail = [];
        $tmp_notfull_order_detail = [];
        foreach($order_detail as $k=>$v)
        {
            #notfull_deal_price
            if(empty($v['subjectLists'])) continue;
            foreach($v['subjectLists'] as $kk=>$vv):
                $tmp_data = [
                    'name' => $vv['obj_name'],
                    'deal_pay_price' => getNumFormat($vv['deal_pay_price']),
                    'real_pay_price' => getNumFormat($vv['real_pay_price']),
                    'pmt_order_aver_price' => getNumFormat($vv['pmt_order_aver_price']),
                    'is_full_pay' => $vv['is_full_pay'],
                ];
                #$vv['is_full_pay']==1 && $tmp_full_order_detail['lists'][]= $tmp_data;
                $vv['is_full_pay']==1 && $tmp_full_order_detail['subjectList'][]= $tmp_data;
                $vv['is_full_pay']==0 && $tmp_notfull_order_detail['subjectList'][]= $tmp_data;
            endforeach;
        }
        $return = ['full_order_detail'=>$tmp_full_order_detail,'notfull_order_detail'=>$tmp_notfull_order_detail];
        #全款#
        /*$return['full_order_detail']['full_pay_price'] = $order['full_pay_price'];
        $return['full_order_detail']['pmt_full_pay_price'] = getNumFormat($order['full_pay_price']-$order['pmt_order']);*/
        $return['full_order_detail']['full_pay_price'] = getNumFormat($order['full_pay_price_tmp']);
        $return['full_order_detail']['pmt_full_pay_price'] = getNumFormat($order['full_pay_price']);
        #优惠促销#
        $tmp_order_rule_detail = $this->get_order_rule_detail(['orderId'=>[$orderId],'rule_use'=>$rule_use,'rule_tmp'=>@$params['rule_tmp']],$rule_detail);
        $return['full_order_detail']['orderRuleList'] = isset($tmp_order_rule_detail[$orderId])?$tmp_order_rule_detail[$orderId]:[];
        #非全款#
        $return['notfull_order_detail']['notfull_deal_price'] = getNumFormat($order['notfull_deal_price']);
        $return['notfull_order_detail']['notfull_pay_price'] = getNumFormat($order['notfull_pay_price']);
        #dump($return);exit;
        return $return;
    }
    /**
     * 订单支付 >> 报名信息生成
    */
    public function set_entry_data($params=[])
    {
        $type = !empty($params['type'])?$params['type']:1;
        $type_id = $params['type_id'];
        $orderId = $params['orderId'];
        $entry_time = !empty($params['entry_time'])?$params['entry_time']:time();
        $userId = !empty($params['userId'])?$params['userId']:0;
        $orderInfo = $this->where('orderId',$orderId)->find();
        if(empty($orderInfo)) return;
        $courseInfo = $this->get_course_detail(['type_id'=>$type_id,'orderId'=>$orderId,'is_full_pay'=>1,'fromType'=>'admin']);
        $courseLists = $courseInfo[$orderId];
        //获取全款odd_id
        $filter_odd_id = [];
        foreach($courseLists as $k=>$v):
            /* 全款处理 */
            #学历 -> 单个课程
            $type_id==1 && $filter_odd_id[] = $v['odd_id'];
            #技能 -> 单个科目
            $type_id==2 && empty($v['subjectLists']) && $filter_odd_id[] = $v['odd_id'];
            #技能 -> 多个科目
            if($type_id==2 && !empty($v['subjectLists'])): //技能
                foreach($v['subjectLists'] as $kk_subject=>$vv_subject):
                    $filter_odd_id[] = $vv_subject['odd_id'];
                endforeach;
            endif;
        endforeach;
        //判断是否订单是否生成报名记录,有则跳过
        $filter_entry_has = [];
        $has_odd_ids = [];
        $has_lists = [];
        !empty($filter_odd_id) && $filter_entry_has = ['orderId'=>$orderId];
        !empty($filter_odd_id) && $filter_entry_has['odd_id'] = ['in',$filter_odd_id];   
        !empty($filter_entry_has) && $type_id==1 && $has_lists = Db::name('student_edu')->where($filter_entry_has)->field('odd_id')->select();
        !empty($filter_entry_has) && $type_id==2 && $has_lists = Db::name('student_skill')->where($filter_entry_has)->field('odd_id')->select();
        foreach($has_lists as $k=>$v):
            $has_odd_ids[] = $v['odd_id'];
        endforeach;//END
        //dump($courseLists);exit;
        #自动创建用户账号
        empty($userId) && $userId = $this->set_user_account($orderInfo);   
        $entry_data = [];
        $comData = [];
        //$comData['type_id'] = $type_id;
        $comData['orderId'] = $orderId;
        $comData['orderNo'] = $orderInfo['orderNo'];
        $comData['userId'] = $userId;
        $comData['agent_uid'] = $orderInfo['agent_uid'];
        $comData['entry_time'] = $entry_time;
        //dump($courseLists);exit;
        foreach($courseLists as $k=>$v):
            #已报名的过滤掉
            if(!empty($v['odd_id']) && in_array($v['odd_id'],$has_odd_ids)) continue;
            //非全款课程/科目，不写入报名记录
            #学历
            if($type_id==1):
                $data = [];
                $data['odd_id'] = $v['odd_id'];
                $data['school_id'] = $v['school_id'];
                $data['school_name'] = $v['school_name'];
                $data['level_id'] = ITSSelItemId('major','level_type',@$v['level_type']);
                $data['exam_type'] = ITSSelItemId('major','exam_type',@$v['exam_type']);
                $data['major_id'] = $v['major_id'];
                $data['major_name'] = $v['major_name'];
                $data['course_id'] = $v['course_id'];
                $data['course_name'] = $v['course_name'];
                $data['extend_data'] = serialize($v);
                //应收学费
                $data['receivable_fee'] = $v['deal_pay_price'];
                //实收费用
                $data['real_fee'] = $v['real_pay_price'];
                //欠费
                $data['arrearage_fee'] = $v['remain_pay_price'];
                $data['grade_id'] =0;
                $data['grade_name'] = !empty($v['grade_name'])?$v['grade_name']:@$v['name'];
                $type==2 && $data['data_type'] = 1;
                $type==2 && $data['batch_num'] = 1;
                $data = array_merge($comData,$data);
                $entry_data[] = $data;
                //model('student_edu')->save($data);
            endif;
            #技能
            if($type_id==2):
                if(!empty($v['subjectLists']))://课程
                    foreach($v['subjectLists'] as $kk_subject=>$vv_subject):
                        //非全款跳过
                        if($vv_subject['is_full_pay']==0) continue;
                        $data = [];
                        $data['odd_id'] = $vv_subject['odd_id'];
                        $data['school_id'] = $vv_subject['school_id'];
                        $data['school_name'] = $vv_subject['school_name'];
                        $data['major_id'] = $vv_subject['major_id'];
                        $data['major_name'] = $vv_subject['major_name'];
                        $data['course_id'] = $vv_subject['course_id'];
                        $data['course_name'] = $vv_subject['course_name'];
                        $data['extend_data'] = serialize($vv_subject);
                        $data['subject_id'] = $vv_subject['subject_id'];
                        $data['subject_name'] = $vv_subject['name'];
                        //应收学费
                        $data['receivable_fee'] = $vv_subject['deal_pay_price'];
                        //实收费用
                        $data['real_fee'] = $vv_subject['real_pay_price'];
                        //欠费
                        $data['arrearage_fee'] = $vv_subject['remain_pay_price'];
                        $data = array_merge($comData,$data);
                        $entry_data[] = $data;
                    endforeach;
                else://科目
                    $data = [];
                    $data['odd_id'] = $v['odd_id'];
                    $data['school_id'] = $v['school_id'];
                    $data['school_name'] = $v['school_name'];
                    $data['major_id'] = $v['major_id'];
                    $data['major_name'] = $v['major_name'];
                    $data['course_id'] = $v['course_id'];
                    $data['course_name'] = $v['course_name'];
                    $data['extend_data'] = serialize($v);
                    $data['subject_id'] = $v['subject_id'];
                    $data['subject_name'] = $v['name'];
                    //应收学费
                    $data['receivable_fee'] = $v['deal_pay_price'];
                    //实收费用
                    $data['real_fee'] = $v['real_pay_price'];
                    //欠费
                    $data['arrearage_fee'] = $v['remain_pay_price'];
                    $data = array_merge($comData,$data);
                    $entry_data[] = $data;
                endif; 
            endif;
        endforeach;
        #学历 >> 写入报名数据
        $type_id==1 && !empty($entry_data) && Db::name('student_edu')->insertAll($entry_data);
        #技能 >> 写入报名数据
        $type_id==2 && !empty($entry_data) && Db::name('student_skill')->insertAll($entry_data);   
    }
    
    /**
     * 代购订单创建会员账号
     */
    public function set_user_account($orderData=[])
    {
        $filter_has = ['dataFlag'=>1,'loginName'=>$orderData['idcard']];
        $userId = Db::name('users')->where($filter_has)->value('userId');
        if(!empty($userId)) return $userId;
        $pwd = 'its123456';
		$basic = []; //基础信息数据集
        $extend = []; //扩展信息数据集
        $basic['loginName'] = $orderData['idcard'];
        $basic['idcard'] = $orderData['idcard'];
        $basic['trueName'] = $orderData['name'];
        $basic['nickName'] = 'nick_'.substr(md5($orderData['name']),0,8);
        $basic['userPhone'] = $orderData['mobile'];
        $basic['lastmodify'] = time();
		$basic['createtime'] = time();
		$basic["loginSecret"] = rand(1000,9999);
    	$basic['loginPwd'] = md5($pwd.$basic['loginSecret']);
    	$basic['userType'] = 0;
        $basic['uidType'] = 1;//学员身份类型：1为新生、2为在学生、3为会员
        $basic['student_type'] = 1;//学员类型：1为技能、2为学历、3为技能学历
        $basic['study_status'] = 1;//学习状态：1为在读、2为毕业、3为过期、4为弃学、5为休学、6为退学
        $result = model('common/users')->allowField(true)->insert($basic);
        $userId = model('common/users')->getLastInsID();
        if(false !== $result){
            $extend['userId'] = $userId;
            $extend['createtime'] = time();
            $extend['lastmodify'] = time();
            //$extend['idcard_no'] = $orderData['idcard'];
            model('common/studentExtend')->save($extend);
        }
        return $userId;   
    }
    
    /**
     * 订单列表
    */
    public function get_lists($params=[])
    {
        $where = [];
        //$where['is_shelves'] = '1';
        $field = '';
        if(isset($params['field']))
        {
            $field = $params['field'];
        }
        $limit = '';
        if(isset($params['limit']))
        {
            $limit = $params['limit'];
        }
        if(isset($params['userId']))
        {
            $where['userId|agent_uid'] = $params['userId'];   
        }
        if(isset($params['orderNo']))
        {
            $where['orderNo'] = $params['orderNo'];   
        }
        if(isset($params['order_status']) && $params['order_status']!='0_0_0')
        {
            list($confirmStatus,$payStatus,$orderType) = explode('_',$params['order_status']);
            $where['confirmStatus'] = $confirmStatus;
            $where['payStatus'] = $payStatus;
            $where['orderType'] = $orderType;
        }
        //分页信息处理
        $limit = '';
        if(isset($params['get_pager']))
        {
            if(empty($params['page_no'])) $params['page_no']=1;
            if(empty($params['page_size'])) $params['page_size']=10;
            $data_total = $this->where($where)->count();
            $page_total = ceil($data_total/$params['page_size']);
            if(isset($params['page_no']) && isset($params['page_size']))
            {
                $start = ($params['page_no']-1)*$params['page_size'];
                $limit = "{$start},{$params['page_size']}";
            }
        }
        //排序处理
        $order = 'lastmodify DESC';
        $rs = $this->where($where)->field($field)->limit($limit)->order($order)->select();
        foreach($rs as $k=>$v)
        {
            $v = array_merge(obj2Array($v),obj2Array($this->get_info([],$v)) );
        }
        #dump($rs);exit;
        //分页信息处理
        if(isset($params['get_pager']))
        {
            $rs_p['lists'] = $rs;
            $rs_p['data_total'] = $data_total;
            $rs_p['page_total'] = $page_total;
            $rs_p['page_cur'] = $params['page_no'];
            $rs = $rs_p;   
        }
        else
        {
            $rs_p['lists'] = $rs;
            $rs = $rs_p;   
        }
        return $rs;
    }
    
    //订单详情
    public function get_info($params=[],$rs=array()){
        $field = '';
        if(isset($params['field']))
        {
           $field = $params['field'];
        }
        $where = [];
        if(isset($params['userId']))
        {
            $where['userId|agent_uid'] = $params['userId'];   
        }
        if(isset($params['orderId']))
        {
           $where['orderId'] = $params['orderId'];    
        }
        if(empty($rs))
        {
            $rs = $this->where($where)->field($field)->find();
        }
        //是否显示补款按钮
        $rs['is_show_pay_btn'] = 0;
        //订单状态判断
        if(isset($rs['confirmStatus']))
        {
            $orderStatus = $rs['confirmStatus'].'_'.$rs['payStatus'].'_'.$rs['orderType'];
            $rs['order_status_format'] = ITSSelItemName('order','order_status',$orderStatus);
            if($rs['orderType']==2) //非全款
            {
                $rs['is_show_pay_btn'] = 1;   
            }
        }
        //支付方式
        if(isset($rs['payType']))
        {
            $rs['pay_type_format'] = ITSGetPayTypeName($rs['payType']);
            $rs['pay_from_format'] = ITSGetPayFromName($rs['payFrom']);
        }
        if(isset($rs['createtime']))
        {
            $rs['createtime_format'] = date('Y-m-d H:i',$rs['createtime']);
        }
        if(isset($params['field'])&&strpos($params['field'],',')===FALSE) return $rs[$field];
        return $rs;
	}
	
	/**
	 * 获取订单需要再次缴费列表。
	 */
	public function getSupplementaryFeeList($params=[],$userInfo=[]){
	    $orderId = @$params['orderId'];
	    $jump_type = min(max($params['jump_type'],1),2);
        //构建条件
        $filter['orderId'] = $orderId;
        $filter['type_id'] = $jump_type;
        //普通模式
        $filter['userId'] = @$params['userId'];
        $filter['agent_uid'] = 0;
        //代购模式
        if(!empty($userInfo['isAgentUser']))
        {
            //$filter['userId'] = @$params['buyUserId'];
            $filter['userId|agent_uid'] = @$params['userId'];
            unset($filter['userId'],$filter['agent_uid']);
        }
        //通道类型
        empty($params['channelType']) && $channelType = $this->getChannelType($orderId);
        !empty($params['channelType']) && $channelType = $params['channelType'];
	    $datas = Db::name('order_detail')->where($filter)->select();
        if(empty($datas)) return MBISReturn("获取数据失败");
	    $subjectList = $courseInfo = $course = $users = [];
	    $subjectList1 = $courseInfo1 = $course1 = [];
	    foreach ($datas as $obj){
	        if($obj['obj_id'] == 0){
	             $users[$obj['userId']] = Db::name('users')->where(['userId'=>$obj['userId']])->find();
	             $course_item = [//课程
	                'userId' => $obj['userId'],
	                'course_id' => $obj['course_id'],
	                'subject_id' => 0,
	                'type_id' => $obj['type_id'],
	                'stu_fee' => $obj['price'],
	                'offers' => $obj['fee_price'],
	                'course_name' => $obj['course_name'],
	                'cover_img' => $obj['cover_img'],
	                'price' => $obj['price'],//课程总价。
	                //'market_price' => $obj['market_price'],
	                //'deposit_price' => $obj['deposit_price'],
	                
	                'is_full_pay' => $obj['is_full_pay'],
                    //'is_full_pay' => 1,
	                //'total_sale_price' => $obj['total_sale_price'],
	                'deal_pay_price' => $obj['deal_pay_price'],
	                'real_pay_price' => $obj['real_pay_price'],//已缴费总额
	                'remain_pay_price' => $obj['remain_pay_price'],
	                'discount_aver_price' => $obj['discount_aver_price'],
	                'deposit_aver_price' => $obj['deposit_aver_price'],
	                'course_real_price' => $obj['course_real_price'],
	                 
	                 //补充内容
	                 'orderNo'=>$obj['orderNo'],//订单号。
	                 'userName'=>$users[$obj['userId']]['trueName'],//学员名称
	                 //'subjectNum'=>Db::name('order_detail')->where(['orderId']->),//科目总数。
	                 
	            ];
	            $subjectNum = $subjectNum_is_payed = $subjectNum_is_paying = 0;
	            foreach ($datas as $v){
	                //if($v['course_id'] == $obj['course_id']){
                    if($v['course_id'] > 0 && $v['obj_id'] > 0){    
	                    $subjectNum++;
	                    $v['is_full_pay'] == 1 && $subjectNum_is_payed++;
	                    $v['is_full_pay'] == 0 && $subjectNum_is_paying++;
	                }
	                
	            }
	            $course_item['subjectNum'] = $subjectNum;//科目总数。
	            $course_item['subjectNum_is_payed'] = $subjectNum_is_payed;//已经缴清科目总数。
	            $course_item['subjectNum_is_paying'] = $subjectNum_is_paying;//需要缴费科目总数。
	            
	            $obj['is_full_pay'] == 0 && $course[$obj['course_id']] = $course_item;
	            $course1[] = $course_item;
	        }
	       }
	    
	    $remain_pay_price = 0;
	    foreach($course1 as $vl){
	        $subjectList = $subjectList1 = array();
    	    foreach ($datas as $obj){
    	        if($obj['obj_id'] > 0 && $obj['course_id'] == $vl['course_id']){
    	            
    	            $item = [
    	            'subject_id' => $obj['obj_id'],
    	            'odd_id' => $obj['odd_id'],
    	            'name' => $obj['obj_name'],
    	            'sale_price' => $obj['price'],
    	            'offer_price' => $obj['fee_price'],
    	            //'market_price' => $obj['market_price'],
    	            //'course_hours' => $obj[''],
    	            'learn_coins' => unserialize($obj['extend_data'])['learn_coins'],
    	            'course_info' => unserialize($obj['extend_data'])['course_info'],
    	            'course_hours' => unserialize($obj['extend_data'])['course_hours'],
    	            //'extend_data' => unserialize($obj['extend_data']),
    	            //'is_shelves' => 1,
    	            'cover_img' => $obj['cover_img'],
    	            'teacher_id' =>$obj['teacher_id'],//讲师ID
    	            'teacher_name' =>unserialize($obj['extend_data'])['teacher_name'],//讲师
    	            //'major_id' => 82,
    	            //'school_id' => 8,
    	            //'teacher_name' => '匿名',
    	            //'school_name' => '艺术设计学院',
    	            //'major_name' => '数码影视-影视后期合成特效',
    	            'subject_offer_price' => $obj['fee_price'],
    	            'discount_aver_price' => $obj['discount_aver_price'],
    	            'is_full_pay' => $obj['is_full_pay'],
                    //'is_full_pay' => 1,
    	            'pmt_order_aver_price' => '0.00',
    	            
    	            'deal_pay_price' => $obj['deal_pay_price'],
    	            'real_pay_price' => $obj['real_pay_price'],
    	            'remain_pay_price' => $obj['remain_pay_price'],
    	            'deposit_aver_price' => $obj['deposit_aver_price'],
    	           ];
    	           $obj['is_full_pay'] == 0 && $subjectList[] = $item;
    	            //计算公式
    	           $obj['is_full_pay'] == 0 && $remain_pay_price +=$obj['remain_pay_price'];
    	           $subjectList1[] = $item; 
    	        }
    	    }
    	    
    	    $vl['is_full_pay'] == 0 && 
    	    $courseInfo[] = [
    	        'course'=>$vl,
    	        'adItemList' =>[
    	            0 =>  [
    	                'it_id' => 4,
    	                'name' => '教材费',
    	                'price' => 0,
    	            ],
	            ],
	            'subjectList'=>$subjectList,
    	    ];
    	    $courseInfo1[] = [
    	        'course'=>$vl,
    	        'adItemList' =>[
    	            0 =>  [
    	                'it_id' => 4,
    	                'name' => '教材费',
    	                'price' => 0,
    	            ],
	            ],
	            'subjectList'=>$subjectList1,
    	    ];
	    }
	    
	    
	    $rs_course = array (
	        'jump_type' => $jump_type,//1、学历，2、课程。
	        'courseInfo' =>$courseInfo,
	        'courseInfo1' =>$courseInfo1,
    	    'orderInfo' => [
    	        'courseMoney' => 0,//0
    	        'realTotalMoney' => $remain_pay_price,//本次应付金额。
    	        'realPayMoney' => $remain_pay_price,//本次应付金额。
    	        'totalMoney' => 0,
    	        'discountMoney' => 0,
    	        'pmt_order' => 0,
    	        'depositMoney' => 0,
    	        'depositAddMoney' => 0,
    	        'depositRemainMoney' => 0,
    	        'adItMoney' => 0,
    	        'orderNo'=>count($datas)>0?$datas[0]['orderNo']:0,
    	        'name' => '',
    	        'mobile' => '',
    	        'idcard' => '',
    	        'type_id' => $jump_type,
    	        'orderType' => 1,
    	        'full_pay_price' => $remain_pay_price,//全款金额
    	        'notfull_pay_price' => 0,
    	        'notfull_deal_price' => 0,
    	        'teamLists' =>[
            	        0 =>[
            	            'name' => '',
            	            'mobile' => '',
            	            'idcard' => '',
            	        ],
    	         ],
    	        'okRuleLists' =>[],
    	        'noRuleLists' =>[],
    	    ],
	        'pay_record'=> [],
            'paymentInfo' => get_payment_lists($payType=1,$payFrom=2),
	    );
	   
	    $pay_record = Db::name('split')->where(['orderId'=>$params['orderId'],'payed_time'=>['gt',0],'payed_amount'=>['gt',0]])->select();
	   //'缴费日期, 缴费金额,受理人,缴费方式';
	    foreach ($pay_record as $val){
	        $item = [
	            'payed_time'=>date('Y-m-d',$val['payed_time']),//付费时间。
	            'amount'    =>sprintf('%0.2f',$val['payed_amount']),
	            'agents'    =>$val['agents'],
	            'payment'   =>$val['payment'],
	        ];
	        $rs_course['pay_record'][] = $item;
	    }
	    //print_r($rs_course);die;
        //补缴促销计算 && 通道类型==2 && 全款金额>0
        if($channelType==2 &&  $rs_course['orderInfo']['full_pay_price']>0)
        {
            $params['rule_type'] = $params['jump_type'];
            $params['rule_use'] = 2;
            model('common/SalesRuleOrder')->set_order_sale_rules($params, $rs_course);
            #本次应付款
            $rs_course['orderInfo']['realTotalMoney'] = $rs_course['orderInfo']['full_pay_price'];
            #本次真实付款
            $rs_course['orderInfo']['realPayMoney'] = $rs_course['orderInfo']['full_pay_price'];
        }
	    return MBISReturn("", 1,$rs_course);
	    
	}
    
	public function checkoutSupplementaryFee($type=1,$params=[],$userInfo=[]){
	    #echo '<pre>';print_r($params);exit;
        /**$params = array(
	    'orderId'=>'2137',//订单ID
	    'jump_type'=>'2',//学历，科目
	    'type_id'=>2,//学历，科目
	    'userId' =>'59',//当前登录用户ID
        'agent_uid' =>'2770',//被代购人用户ID
	    'orders' =>array(//详细情况
	        [
	            'odd_id'=>'2235',//订单详细ID，科目编号。
	            'is_full_pay'=>0,//是否全款
	            //'append_price'=>100,//追缴费用。
	        ],
            [
	            'odd_id'=>'1627',//订单详细ID，科目编号。
	            'is_full_pay'=>1,//是否全款
	            //'append_price'=>800,//追缴费用。
	        ]
	    ),
        'append_price'=>'800',//补缴费用，默认是大于500的
        'nojson' => 1,
        'payType'=>1,//支付方式ID。
        'payFrom'=>2,//支付方式ID。
	    );*/
        //echo json_encode($params);die;
        
	    //参数定义
	    //目标补缴费用，其实就是针对现有的科目进行优惠处理，生成付款。
	    //1、订单ID，用户ID,订单详情id=>是否全款，补交费用。//'amount'=>'',//科目原价。
       //通道类型
	   try{
           if(empty($params['orders'])){
	            return MBISReturn("科目未选择", -1,null);
	        }
           $jump_type = @$params['jump_type'];
           $channelType = $this->getChannelType($params['orderId']);
           !empty($params['channelType']) && $channelType = $params['channelType'];
	   }catch (\Exception $e){
	       return MBISReturn("JSON参数错误".$e->getMessage(), -1,null);
	   }
	    $pars = array();
        //非全款标识处理
        $notfull_val = [];
	    foreach ($params['orders'] as $val){
	        $odd_ids[] = $val['odd_id'];
            $notfull_val[$val['odd_id']] = $val['is_full_pay'];
	    }
	    $odd_ids && $datas = Db::name('order_detail')->where(['obj_id'=>['gt',0],'orderId'=>$params['orderId'],'is_full_pay'=>0,'odd_id'=>['in',$odd_ids]])->select();
         
        //代购模式
        if(!empty($userInfo['isAgentUser']))
        {
            $params['buyUserId'] = @$datas[0]['userId'];
            $params['agent_uid'] = @$datas[0]['agent_uid'];
            $buyUserInfo = Db::name('users')->where('userId',$params['buyUserId'])->find();
        }
        else
        {
            $params['buyUserId'] = 0;
            $params['agent_uid'] = 0; 
            $buyUserInfo = Db::name('users')->where('userId',$params['userId'])->find();
        }
	    $course_id = [];
        //非全款标准价总金额
        $notfull_total_price = 0;
        //非全款科目分摊价格数组
        $notfull_arr_price = [];
        //还需付款总金额
        $notfull_remain_price = 0;
	    foreach ($datas as $val){
	        //组装数据，准备进入促销。
	        $course_id[] = $val['course_id'];
            (isset($notfull_val[$val['odd_id']]) && $notfull_val[$val['odd_id']]==0) && $notfull_remain_price += $val['remain_pay_price'];
            (isset($notfull_val[$val['odd_id']]) && $notfull_val[$val['odd_id']]==0) && $notfull_arr_price[] = [
            'id'=>$val['odd_id'],'price'=>$val['price']];
            (isset($notfull_val[$val['odd_id']]) && $notfull_val[$val['odd_id']]==0) && $notfull_total_price += $val['price'];
	    }
        //还需付款总金额处理 待续。。。
        $min_pay_price = 500;
        $notfull_remain_price-$min_pay_price<0 && $min_pay_price = $notfull_remain_price;    
        //非全款科目分摊处理
        !empty($notfull_arr_price) && 
            $notfull_arr_price = get_aver_num($notfull_arr_price,$notfull_total_price,$params['append_price']);
        empty($notfull_arr_price) && $params['append_price'] = '0.00';
        empty($notfull_arr_price) && $min_pay_price = 0; 
	    $courses = [];
	    $course_id  && $courses = Db::name('order_detail')->where(['obj_id'=>0,'orderId'=>$params['orderId'],'course_id'=>['in',$course_id]])->select();
	    $courseInfo = [];$total_remain_pay_price=0;$remain_pay_price=0;$notfull_pay_price=0;$notfull_deal_price=0;
        //全款金额
        $full_pay_price=0;
	    foreach ($courses as $val){
	        $subjectList = array();
            //课程实付款
            $course_real_price = 0;
            //课程未付款
            $course_remain_price = 0;
            //课程实收定金
            $course_deposit_aver_price = 0;
	        foreach ($datas as $obj){
	            if($obj['obj_id'] > 0 && $obj['course_id'] == $val['course_id']){
                    
                    $obj['deal_pay_price'] = $obj['remain_pay_price'];
                    $real_pay_price=$obj['deal_pay_price'];
                    $remain_pay_price=$obj['remain_pay_price'];
                    //接受参数 >> 是否全款标识
                    $req_is_full_val = (isset($notfull_val[$obj['odd_id']]) && $notfull_val[$obj['odd_id']]==1)?1:0;
                    /**
                     @do 全款处理
                     @desc 1、本次全款总金额计算
                           2、全款应付款
                           3、全款真实付款
                    */
                    //本次订单实收款累计(全款)
                    $req_is_full_val==1 && $total_remain_pay_price +=$obj['remain_pay_price'];
                    //本次订单应收款\实收款累计(全款)
                    $req_is_full_val==1 && $full_pay_price +=$obj['remain_pay_price'];
                    
                    /**
                     @do 非全款处理
                     @desc 1、本次非全款总金额计算
                           2、非全款应付款
                           3、非全款真实付款
                    */
                    //本次订单实收款(非全款)
                    $req_is_full_val==0 && $notfull_pay_price +=$notfull_arr_price[$obj['odd_id']];
                    //本次科目应收款(非全款)
                    $req_is_full_val==0 && $notfull_deal_price +=$obj['remain_pay_price'];
                    //本次订单实收款(非全款)
                    $req_is_full_val==0 && $total_remain_pay_price +=$notfull_arr_price[$obj['odd_id']];
                    //本次科目实收款累计(非全款)
                    $req_is_full_val==0 && 
                    //$real_pay_price=$obj['real_pay_price']+$notfull_arr_price[$obj['odd_id']];
                    $real_pay_price=$notfull_arr_price[$obj['odd_id']];
                    //本次科目未收款
                    $remain_pay_price=$obj['deal_pay_price']-$real_pay_price;
                    //本次课程补缴定金累计(非全款)
                    $req_is_full_val==0 && 
                    $course_deposit_aver_price +=($obj['real_pay_price']+$notfull_arr_price[$obj['odd_id']]);
                    /**
                     * @do 科目列表
                    */
	                $subjectList[] = [
    	            'subject_id' => $obj['obj_id'],
    	            'name' => $obj['obj_name'],
    	            'sale_price' => $obj['price'],
    	            'offer_price' => $obj['fee_price'],
    	            //'market_price' => $obj['market_price'],
    	            //'course_hours' => $obj[''],
    	            'learn_coins' => unserialize($obj['extend_data'])['learn_coins'],
    	            'course_info' => unserialize($obj['extend_data'])['course_info'],
    	            //'is_shelves' => 1,
    	            'cover_img' => $obj['cover_img'],
    	            'teacher_id' =>$obj['teacher_id'],
    	            //'major_id' => 82,
    	            //'school_id' => 8,
    	            //'teacher_name' => '匿名',
    	            //'school_name' => '艺术设计学院',
    	            //'major_name' => '数码影视-影视后期合成特效',
    	            'subject_offer_price' => $obj['fee_price'],
    	            'discount_aver_price' => $obj['discount_aver_price'],
    	            //'is_full_pay' => $obj['is_full_pay'],
                    'is_full_pay' => $remain_pay_price==0?1:0,
    	            'pmt_order_aver_price' => '0.00',
    	            
    	            'deal_pay_price' => $obj['deal_pay_price'],
    	            //'real_pay_price' => $obj['real_pay_price'],
    	            //'remain_pay_price' => $obj['remain_pay_price'],
                    'real_pay_price' => $real_pay_price,
                    'remain_pay_price' => $remain_pay_price,
    	            'deposit_aver_price' => $remain_pay_price==0?'0.00':$obj['deposit_aver_price']+(isset($notfull_val[$obj['odd_id']])?$notfull_arr_price[$obj['odd_id']]:0),
    	           ];
                   //全款总金额计算
	               //(isset($notfull_val[$obj['odd_id']]) && $notfull_val[$obj['odd_id']]==1) && $remain_pay_price +=$obj['remain_pay_price'];
                   $course_real_price += $real_pay_price;
                   $course_remain_price += $remain_pay_price;
	            }
	        }
            /**
             * @do 课程列表
            */
	        $courseItem = [
                //创建一个cartId
                'cartId' => $val['type_id'].$val['userId'].$val['course_id'].rand(0,99),
	            'userId' => $val['userId'],
	            'course_id' => $val['course_id'],
	            'subject_id' => 0,
	            'type_id' => $val['type_id'],
	            'stu_fee' => $val['price'],
	            'offers' => $val['fee_price'],
	            'course_name' => $val['course_name'],
	            'cover_img' => $val['cover_img'],
	            'price' => $val['price'],
	            //'market_price' => $obj['market_price'],
	            //'deposit_price' => $obj['deposit_price'],
	            'is_full_pay' => $val['is_full_pay'],
	            //'total_sale_price' => $obj['total_sale_price'],
	            'deal_pay_price' => $val['deal_pay_price'],
	            //'real_pay_price' => $val['real_pay_price'],
	            //'remain_pay_price' => $val['remain_pay_price'],
                'real_pay_price' => $course_real_price,
	            'remain_pay_price' => $course_remain_price,
	            'discount_aver_price' => $val['discount_aver_price'],
	            //'deposit_aver_price' => $val['deposit_aver_price'],
                'deposit_aver_price' => $course_deposit_aver_price,
	            'course_real_price' => $val['course_real_price'],
	        ];
            /**
             * @do 课程信息
            */
	        $courseInfo[] = [
	            'course'=>$courseItem,
	            /*'adItemList' =>[
	                0 =>  [
	                    'it_id' => 4,
	                    'name' => '教材费',
	                    'price' => 0,
	                ],
	            ],*/
	            'subjectList'=>$subjectList,
	        ];
	        
	    }
	    $rs_course = array (
	        'jump_type' => $jump_type,//1、学历，2、课程。
	        'courseInfo' =>$courseInfo,
	        'orderInfo' => [
	            'courseMoney' => 0,//0
	            'realTotalMoney' => $total_remain_pay_price,//本次应付金额(已参与订单促销)
	            'realPayMoney' => $total_remain_pay_price,//本次应付金额(已参与订单促销)
	            'totalMoney' => 0,
	            'discountMoney' => 0,
	            'pmt_order' => 0,
	            'depositMoney' => 0,
	            'depositAddMoney' => 0,
	            'depositRemainMoney' => 0,
	            'orderNo'=>count($datas)>0?$datas[0]['orderNo']:0,
	            'adItMoney' => 0,
	            'name' => $buyUserInfo['trueName'],
	            'mobile' => $buyUserInfo['userPhone'],
	            'idcard' => $buyUserInfo['idcard'],
	            'type_id' => $jump_type,
	            'orderType' => 1,
                'full_pay_price_tmp' => $full_pay_price,//全款金额(未参与订单促销)
	            'full_pay_price' => $full_pay_price,//全款金额(已参与订单促销)
	            'notfull_pay_price' => $notfull_pay_price,//本次非全款金额
	            'notfull_deal_price' => $notfull_deal_price,//本次应付款金额
	            'teamLists' =>[
	                0 =>[
	                    'name' => '',
	                    'mobile' => '',
	                    'idcard' => '',
	                ],
	            ],
	            'okRuleLists' =>[],
	            'noRuleLists' =>[],
	        ],
	            //'pay_record'=> [],
	            'paymentInfo' => get_payment_lists($payType=1,$payFrom=2),
	        );
        //补缴促销计算 && 通道类型==2 && 全款金额>0
        if($channelType==2 && $full_pay_price>0)
        {
            $params['rule_type'] = $params['jump_type'];
            $params['rule_use'] = 2;
            model('common/SalesRuleOrder')->set_order_sale_rules($params, $rs_course);
            model('common/course')->adver_pmt_order($params, $rs_course);
        }
        if(!empty($params['isSupplement'])){
            //$params['addr'] = $buyUserInfo['addr'];
            $tmpl = model('common/carts')->checkoutStatementData($params,$userInfo,$rs_course);
            return MBISReturn("获取数据成功", 1, ['name'=>$tmpl['name'],'url'=>$tmpl['statement_url'] ]);
        }
	    if($type==2){
            return $rs_course;
	    }
        return MBISReturn("", 1,$rs_course['orderInfo']);
	}
	public function createSupplementaryFee($params=[],$userInfo=[]){
        $rs_course = $this->checkoutSupplementaryFee(2,$params,$userInfo);
        //代购模式
        if(!empty($userInfo['isAgentUser']))
        {
            $userId = @$params['buyUserId'];
            $agent_uid = @$params['agent_uid'];
        }
        else
        {
            $userId = @$params['userId'];
            $agent_uid = 0;  
        }
        $solutions = $rs_course['orderInfo'];
        //参数
        $orderId = @$params['orderId'];
        $supplementNum = model('common/orders')->getSupplementNum($orderId);
        $type_id = @$params['jump_type'];
        $odd_ids = [];
        foreach ($params['orders'] as $val){
           $odd_ids[] = $val['odd_id'];
        }
        //生成补充费用订单。
        Db::startTrans();
        try{
            //1、生成付款单
            //2、生成补缴非要用订单。
            $pid = getPaymentId();
            Db::name('payments')->insert([
                'payment_id'=>$pid,//'支付单号' ,
                'type_id'=>$type_id,//'购买对象类型：1=学历，2=技能' ,
                'orderId'=>$orderId,//'订单主表标识' ,
                'orderNo'=>$solutions['orderNo'],//'订单编号' ,
                'obj_ids'=>json_encode($odd_ids),//'类型=1：为年级ids、类型=2：为科目ids ,存json字符串' ,
                'money'=>$solutions['full_pay_price']+$params['append_price'],//'支付金额' ,
                'cur_money'=>$solutions['full_pay_price']+$params['append_price'],//'支付货币金额' ,
                'userId'=>$userId,//'会员ID' ,
                'agent_uid'=>$agent_uid,//'代购会员ID' ,
                'status'=>'ready',//'支付状态' ,
                //'pay_name'=>@PaymentConfig::getPayDesc($params['payFrom'])['name'],//支付名称
                'pay_name'=>ITSGetPayFromName($params['payFrom']),//支付名称
                'pay_type'=>$params['payType'],//'支付类型' ,
                'payType'=>$params['payType'],//'支付类型' ,
                'payTypeName'=>ITSGetPayTypeName($params['payType']),//'支付类型' ,
                't_payed'=>0,//'支付完成时间' ,
                'op_id'=>0,//'操作员' ,
                'payment_bn'=>'',//'支付单唯一编号' ,
                'currency'=>'CNY',//'货币' ,
                'paycost'=>'',//'支付网关费用' ,
                'pay_app_id'=>$params['payFrom'],//'支付方式名称' ,
                'pay_ver'=>'1.0',//'支付版本号' ,
                'ip'=> Request::instance()->ip(),//'支付IP' ,
                't_begin'=>0,//'支付开始时间' ,
                't_confirm'=>0,//'支付确认时间' ,
                'memo'=>'',//'支付注释' ,
                'return_url'=>'',//'支付返回地址' ,
                'disabled'=>'false',//'支付单状态' ,
                'trade_no'=>'',//支付单交易编号' ,
                //'thirdparty_account'=>'',//'第三方支付账户' ,
                'rule_use' => 2,
                'supplementNum' => $supplementNum,
            ]);
            
            //生成补费订单。
            Db::name('split')->insert([
                'userId'=>$userId,//'会员ID' ,
                'agent_uid'=>$agent_uid,//'代购会员ID' ,
                'orderId'=>$orderId,//'订单ID' ,
                'orderNo'=>$solutions['orderNo'],//'订单号' ,
                'payment_id'=>$pid,//'付款单号' ,
                'pay_id'=>$params['payFrom'],//'支付方式ID' ,
                'obj_id'=>implode(",", $odd_ids),//'明细ID' ,
                'discount'=>$solutions['pmt_order'],//'优惠金额' ,
                'amount'=>$solutions['full_pay_price']+$params['append_price'],//'应付金额',
                'agents'=>'平台',//'受理人',
                //'payment'=>@PaymentConfig::getPayDesc($params['payFrom'])['name'],//支付方式 ,
                'payment'=>ITSGetPayFromName($params['payFrom']),//支付方式 ,
                'payed_amount'=> 0,////'已付金额'
                'create_time'=>time(),//'创建时间'
                'payed_time'=>0,//'付款时间'
                'payType'=>$params['payType'],//'支付类型' ,
                'payTypeName'=>ITSGetPayTypeName($params['payType']),//'支付类型' ,
            ]);
            
            //如果追加费用为0的时候，全款全科目缴费，并且是缴费完毕。
            /*if($params['append_price'] == 0){
                //订单缴费完成全部。
            }*/
            
            //okRuleLists
            if(isset($solutions['okRuleLists']))
            {
                $okRuleLists = $solutions['okRuleLists'];
                unset($solutions['okRuleLists']);
            }
            //waitRuleLists
            if(isset($solutions['waitRuleLists']))
            {
                $waitRuleLists = $solutions['waitRuleLists'];
                unset($solutions['waitRuleLists']);
            }
            if(isset($solutions['noRuleLists']))
            {
                $noRuleLists = $solutions['noRuleLists'];
                unset($solutions['noRuleLists']);
            }
            if(isset($solutions['teamLists']))
            {
                $teamLists = $solutions['teamLists'];
                unset($solutions['teamLists']);
            }
            if(isset($solutions['userCondition']))
            {
                $userCondition = $solutions['userCondition'];
                unset($solutions['userCondition']);
            }
            if(isset($solutions['okRule']))
            {
                $okRule = $solutions['okRule'];
                unset($solutions['okRule']);
            }
            //$order = model('common/orders')->where(['orderId'=>$orderId])->field('*')->find();
            $order = $solutions;
            //促销规则处理
            $rule_detail = [];
            if(!empty($okRuleLists))
            {
                foreach($okRuleLists as $k=>$v)
                {
                     $rule_detail[] = [
                        'rule_id' => $v['rule_id'],
                        'name' => $v['name'],
                        'description' => $v['description'],
                        'from_time' => $v['from_time'],
                        'to_time' => $v['to_time'],
                        'member_lv_ids' => $v['member_lv_ids'],
                        'member_type_ids' => $v['member_type_ids'],
                        'conditions' => $v['conditions_old'],
                        'action_conditions' => $v['action_conditions'],
                        'stop_rules_processing' => $v['stop_rules_processing'],
                        'sort_order' => $v['sort_order'],
                        'action_solution' => $v['action_solution_old'],
                        'c_template' => $v['c_template'],
                        's_template' => $v['s_template'],
                        'discount_price' => $v['discount_price'],
                        'userId' => $userId,//会员ID
                        'agent_uid' => $agent_uid,//代购会员ID
                        'type_id' => $order['type_id'],
                        'orderId' => $orderId,
                        'orderNo' => $order['orderNo'],
                        'rule_use' => 2,
                        'createtime' => time(),
                        'lastmodify' => time(),
                        'supplementNum' => $supplementNum,
                     ];  
                }
                //Db::name('order_rule_log')->insertAll($rule_detail);
            }
            //存储本次补缴信息，用于付款完后更新
            //代购模式
            if(!empty($rs_course['courseInfo']))
            {
                if(!empty($userInfo['isAgentUser'])){
                    $params['userId'] = @$params['buyUserId'];
                }
                else{
                    $params['agent_uid'] = 0;   
                }
                model('common/OrderSupplementData')->saveData($params,array_merge($rs_course,['order_rule_log'=>$rule_detail]));
            }
            Db::commit();
            
            $order['createtime_format'] = date('Y-m-d H:i');
            $order['payTypeName'] = ITSGetPayTypeName($params['payType']);
            $order['payFromName'] = ITSGetPayFromName($params['payFrom']);
            $order['payment_id'] = $pid;
            $order['realTotalMoney'] = $solutions['realTotalMoney'];
            $order['realPayMoney'] = $solutions['realPayMoney'];
            $params['rule_tmp'] = 1;
            $order = array_merge(obj2Array($order),$this->pre_order_detail_data($orderId,$order,2,$params,$rs_course,$rule_detail));
            return MBISReturn("补缴数据提交成功", 1,$order);
            
        }catch(\Exception $e){
            Db::rollback();
            return MBISReturn($e->getMessage().$e->getFile().$e->getLine(), -1,[]);
        }
       
	}
    /**
     * @获取通道类型
     * @param orderId 订单ID
    */
    public function getChannelType($orderId=0)
    {
        $channelType = 1;
        !empty($orderId) && $rs = 
                model('common/orders')->where(['orderId'=>$orderId])->field('channelType')->find();
        !empty($rs) && $channelType = (int)$rs['channelType'];
        return $channelType;
    }
    
    /**
     * @do 获取当前补缴次数
     * @desc SupplementNum+1
     * @param orderId 订单ID
    */
    public function getSupplementNum($orderId=0,$tmp_orders=[])
    {
        $rs_order = model('common/orders')->where(['orderId'=>$orderId])->find();
        if(empty($rs_order)) return 0;
        !empty($rs_order) && $supplementNum = (int)$rs_order['supplementNum'];
        return $supplementNum+1;
    }
    
}
