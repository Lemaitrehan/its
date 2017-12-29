<?php
namespace application\common\model;
use think\Db;
/**
 * 订单促销处理类
 */
class SalesRuleOrder extends Base{
    //规则业务处理
    public function set_order_sale_rules(&$params,&$orderInfo)
    {
        $lists = $this->get_lists($params);
        //按照c_template、rule_id数据分组
        !empty($lists) && $tmp_wait_datas = format_arr_by_key('c_template','rule_id',$lists,$msg);
        $tmp_ok_rules = [];//符合条件
        $tmp_no_rules = [];//不符合条件
        $tmp_wait_rules = [];//即将符合条件
        $is_condition = false;
        foreach($lists as $k=>$v)
        {
            $right_condition = $v;
            $func = 'compare_'.$v['c_template'];
            if(method_exists($this,$func)):
                $is_condition = $this->{$func}($params,$orderInfo,$right_condition,$tmp_wait_rules);
            endif;
            if($is_condition):
                //优惠金额
                $v['discount_price'] = getNumFormat($orderInfo['orderInfo']['okRule'][$v['c_template'].'_'.$v['rule_id']]);
                $tmp_ok_rules[] = $v;
                //即将符合下一个规则
                $tmp_wait_data = !empty($tmp_wait_datas[$v['c_template']])?$tmp_wait_datas[$v['c_template']]:[];
                $data_wait_rules = [];
                !empty($tmp_wait_data) && $data_wait_rules = $this->get_next_soon_rule($lists,$tmp_wait_data,$v['c_template'],$v['rule_id']);
                !empty($data_wait_rules) && $tmp_wait_rules[] = [
                    'rule_id'=>$data_wait_rules['rule_id'],
                    'name'=>$data_wait_rules['name'],
                    'rule_desc_cur'=>$v['rule_desc_cur'],
                    'rule_desc_format'=>$data_wait_rules['rule_desc_format']
                ];
            else:
                $tmp_no_rules[] = $v;
            endif;
        }
        $orderInfo['orderInfo']['okRuleLists'] = $tmp_ok_rules;
        $orderInfo['orderInfo']['waitRuleLists'] = $tmp_wait_rules;
        $orderInfo['orderInfo']['noRuleLists'] = $tmp_no_rules;
        //dump($tmp_wait_rules);
        //dump($tmp_ok_rules);
        //dump($tmp_no_rules);exit;
    }
    
    //当订单科目的数量满X，给予优惠
    public function compare_promotion_conditions_order_itemsquanityallsubjects(&$params,&$orderInfo,&$right_condition,&$tmp_wait_rules=[])
    {
        //判断金额是否小于0
        if($orderInfo['orderInfo']['full_pay_price']<=0) return false;
        $type_id = $params['type_id'];
        $userId = $params['userId'];
        //获取用户已购买订单
        $rs_orders = [];
        !empty($params['idcard']) && $userId = Db::name('users')->where(['idcard'=>$params['idcard']])->value('userId');
        !empty($userId) && $rs_orders = Db::name('orders')->where(['userId'=>$userId,'confirmStatus'=>1,'payStatus'=>1])->select();
        //$rs_orders = Db::name('orders')->where(['userId|agent_uid'=>$userId,'confirmStatus'=>1,'payStatus'=>1])->select();
        $subject_count = 0;
        if(!empty($rs_orders))
        {
            $in_oids = [];
            foreach($rs_orders as $k=>$v):
                $in_oids[] = $v['orderId'];
            endforeach;
            $count_o_detail = Db::name('order_detail')->where([
                'orderId'=>['in',$in_oids],
                'is_full_pay'=>1,
                'obj_id'=>['gt',0],
            ])->count();
            $subject_count += $count_o_detail;  
        }
        //新的全款科目个数
        $courseInfo = $orderInfo['courseInfo'];
        #dump($courseInfo);exit;
        if(!empty($courseInfo))
        {
            foreach($courseInfo as $k=>$v):
                if($params['type_id']==1)//学历
                {
                     if($v['course']['is_full_pay']==1):
                        $subject_count += 1;
                     endif;  
                }
                elseif($params['type_id']==2)//技能
                {
                    if(!empty($v['subjectList']))//课程
                    {
                        foreach($v['subjectList'] as $kk=>$vv):
                            if($vv['is_full_pay']==1):
                                $subject_count += 1;
                            endif;
                        endforeach;
                    }
                    else //单个科目
                    {
                         if(!empty($v['course']) && $v['course']['is_full_pay']==1):
                            $subject_count += 1;
                         endif;  
                    }
                }
            endforeach;  
        }
        //比较是否符合促销规则
        $conditions = $right_condition['conditions'];
        $is_condition = false;
        if(!empty($conditions)):
            foreach($conditions as $k=>$v):
                if($k == 'condition_1')://条件1处理
                    $orderInfo['orderInfo']['userCondition'][$right_condition['c_template']]['user'] = array('subject_count'=>$subject_count);
                    $right_condition['s_template']=='promotion_solutions_topercent' && $orderInfo['orderInfo']['userCondition'][$right_condition['c_template']][$right_condition['s_template']] = getNumFormat($right_condition['action_solution']['solution_1'][0]['value']/100);
                    $orderInfo['orderInfo']['userCondition'][$right_condition['c_template']]['system'] = $v;
                    $is_condition = $this->run_if_code($subject_count,$v);
                endif;//END
            endforeach;
        endif;
        if($is_condition):
            $is_condition = $this->get_solution_price($params,$orderInfo,$right_condition);
        endif;
        if($is_condition) //即将符合订单促销
        {
            
            //dump($right_condition);exit; 
        }
        $right_condition['rule_desc_cur'] = "当前全款科目数量：{$subject_count}";
        return $is_condition;
    }
    
    //对报名人数满足X的团体报名，订单给予优惠
    public function compare_promotion_conditions_order_usersquanityall(&$params,&$orderInfo,&$right_condition,&$tmp_wait_rules=[])
    {
        //判断金额是否小于0
        if($orderInfo['orderInfo']['full_pay_price']<=0) return false;
        $type_id = $params['type_id'];
        $userId = $params['userId'];
        //团购人数
        $team_count = 0;
        if(!empty($orderInfo['orderInfo']['teamLists']))
        {
            foreach($orderInfo['orderInfo']['teamLists'] as $v)
            {
                if(!empty($v['name']) && !empty($v['mobile']) && !empty($v['idcard'])) 
                {
                    $team_count +=1;   
                }
            }
            //$team_count = count($orderInfo['orderInfo']['teamLists']);   
        }
        if($team_count==0) return;
        //比较是否符合促销规则
        $conditions = $right_condition['conditions'];
        $is_condition = false;
        if(!empty($conditions)):
            foreach($conditions as $k=>$v):
                if($k == 'condition_1')://条件1处理
                    $orderInfo['orderInfo']['userCondition'][$right_condition['c_template']]['user'] = array('team_count'=>$team_count);
                    $right_condition['s_template']=='promotion_solutions_topercent' && $orderInfo['orderInfo']['userCondition'][$right_condition['c_template']][$right_condition['s_template']] = getNumFormat($right_condition['action_solution']['solution_1'][0]['value']/100);
                    $orderInfo['orderInfo']['userCondition'][$right_condition['c_template']]['system'] = $v;
                    $is_condition = $this->run_if_code($team_count,$v);
                endif;//END
            endforeach;
        endif;
        if($is_condition):
            $is_condition = $this->get_solution_price($params,$orderInfo,$right_condition);
        endif;
        $right_condition['rule_desc_cur'] ="当前团购数量：{$team_count}";
        return $is_condition;
    }
    
    //用户自定义订单促销模板
    public function compare_promotion_conditions_order_userdefined(&$params,&$orderInfo,&$right_condition,&$tmp_wait_rules=[])
    {
        //判断金额是否小于0
        if($orderInfo['orderInfo']['full_pay_price']<=0) return false;
        $type_id = $params['type_id'];
        $userId = $params['userId'];
        $orderType = $orderInfo['orderInfo']['orderType'];
        //$userInfo = Db::name('users')->where('userId',$userId)->find();
        $userInfo = [];
        $uidType = !empty($userInfo['uidType'])?$userInfo['uidType']:1;
        //比较是否符合促销规则
        $conditions = $right_condition['conditions'];
        $is_condition = false;
        $conOrderType = $conUidType = 0;
        if(!empty($conditions)):
            foreach($conditions as $k=>$v):
                if($k == 'condition_1')://条件1处理
                    $orderInfo['orderInfo']['userCondition'][$right_condition['c_template']]['user']['orderType'] = $orderType;
                    $orderInfo['orderInfo']['userCondition'][$right_condition['c_template']]['system']['orderType'] = $v[0]['value'];
                    $conOrderType = $v[0]['value'];
                    //$is_condition = $v[0]['value']==$orderType?true:false;
                elseif($k == 'condition_2')://条件2处理
                    $orderInfo['orderInfo']['userCondition'][$right_condition['c_template']]['user']['uidType'] = $uidType;
                    $orderInfo['orderInfo']['userCondition'][$right_condition['c_template']]['system']['uidType'] = $v[0]['value'];
                    $conUidType = $v[0]['value'];
                    //$is_condition = $v[0]['value']==$uidType?true:false;
                endif;//END
                $right_condition['s_template']=='promotion_solutions_topercent' && $orderInfo['orderInfo']['userCondition'][$right_condition['c_template']][$right_condition['s_template']] = getNumFormat($right_condition['action_solution']['solution_1'][0]['value']/100);
            endforeach;
        endif;
        $is_condition = ($conOrderType==$orderType&&$conUidType==$uidType)?true:false;
        if($is_condition):
            $is_condition = $this->get_solution_price($params,$orderInfo,$right_condition);
        endif;
        $orderTypeName = ITSSelItemName('user','orderType',$orderType);
        $uidTypeName = ITSSelItemName('user','uidType',$uidType);
        $right_condition['rule_desc_cur'] ="当前交款方式：{$orderTypeName} 当前学员身份：{$uidTypeName}";
        return $is_condition;
    }
    
    //校长特别折扣优惠
    public function compare_promotion_conditions_school_special_discount(&$params,&$orderInfo,&$right_condition,&$tmp_wait_rules=[])
    {
        //判断金额是否小于0
        if($orderInfo['orderInfo']['full_pay_price']<=0) return false;
        $type_id = $params['type_id'];
        $userId = $params['userId'];
        $orderType = $orderInfo['orderInfo']['orderType'];
        $userInfo = Db::name('users')->where('userId',$userId)->find();
        $uidType = $userInfo['uidType'];
        $schoolId = model('users')->getSchoolId(['userId'=>$params['userId']]);
        $school_setting = getSchoolDiscountSetting(['schoolId'=>$schoolId]);
        //比较是否符合促销规则
        $conditions = $right_condition['conditions'];
        $is_condition = false;
        if(!empty($conditions)):
            foreach($conditions as $k=>$v):
                $is_condition = false;
                if($k == 'condition_1')://条件1处理
                    $orderInfo['orderInfo']['userCondition'][$right_condition['c_template']]['user']['schoolId'] = $schoolId;
                    $orderInfo['orderInfo']['userCondition'][$right_condition['c_template']]['system']['schoolId'] = $schoolId;
                    $is_condition = $v[0]['value']==$schoolId?true:false;
                endif;//END
                
            endforeach;
        endif;
        if($is_condition):
            $is_condition = $this->get_solution_price($params,$orderInfo,$right_condition);
        endif;
        return $is_condition;
    }
    
    //获取促销规则列表
    public function get_lists($params=[])
    {
        $rs = array(
    # rule_1 当订单科目的数量满X，给予优惠 #
        0 => array(
            'name' => '当订单科目的数量满X，给予优惠',
            'description' => '规则描述',
            'status' => '1',
            'sort_order' => '0',
            'stop_rules_processing' => '0',
            'from_time' => '1254326400',
            'to_time' => '1604073600',
            'member_lv_ids' => '1,2,3',
            'member_type_ids' => '0,1,2',
            
            /* 优惠条件 */
            'c_template' => 'promotion_conditions_order_itemsquanityallsubjects',
            //'conditions' => '规则条件:序列化',
            /**
                type:大于=gt 小于=lt 等于=eq 小于等于=lte 大于等于=gte
                value: 正整数
            **/
            'conditions' => array(
                'condition_1' => array(
                    array('type'=>'gte','value'=>5),//区间1
                    array('type'=>'lte','value'=>500),//区间2
                ),
                //'condition_2' => array('type'=>'lte','value'=>500),
            ),
            /**
                暂时没用到
            */
            'action_conditions' => '动作执行条件:序列化',
            
            /* 优惠方案 */
            /**
               s_template可选 
               1、订单以固定折扣出售(promotion_solutions_topercent) 
               2、订单固定价格购买(promotion_solutions_tofixed)
               3、订单减固定价格出售(promotion_solutions_byfixed)
            */
            's_template' => 'promotion_solutions_topercent',
            //'action_solution' => '动作方案:序列化',
            'action_solution' => array(
                'solution_1' => array('value'=>50),
            ),
        ),
    
    # rule_2 对报名人数满足X的团体报名，订单给予优惠 #
        1 => array(
            'name' => '对报名人数满足X的团体报名，订单给予优惠',
            'description' => '规则描述',
            'status' => '1',
            'sort_order' => '0',
            'stop_rules_processing' => '0',
            'from_time' => '1254326400',
            'to_time' => '1604073600',
            'member_lv_ids' => '1,2,3',
            'member_type_ids' => '0,1,2',
            'c_template' => 'promotion_conditions_order_usersquanityall',
            //'conditions' => '规则条件:序列化',
            /**
                type:大于=gt 小于=lt 等于=eq 小于等于=lte 大于等于=gte
                value: 正整数
            **/
            'conditions' => array(
                'condition_1' => array(
                    array('type'=>'gte','value'=>5),//区间1
                    array('type'=>'lte','value'=>500),//区间2
                ),
                //'condition_2' => array('type'=>'lte','value'=>500),
            ),
            /**
                暂时没用到
            */
            'action_conditions' => '动作执行条件:序列化',

            /**
               s_template可选 
               1、订单以固定折扣出售(promotion_solutions_topercent) 
               2、订单固定价格购买(promotion_solutions_tofixed)
               3、订单减固定价格出售(promotion_solutions_byfixed)
            */
            's_template' => 'promotion_solutions_topercent',
            //'action_solution' => '动作方案:序列化',
            'action_solution' => array(
                'solution_1' => array('value'=>50),
            ),
        ),
    
    # rule_3 用户自定义订单促销模板 #
    2 => array(
            'name' => '用户自定义订单促销模板',
            'description' => '规则描述',
            'status' => '0',
            'sort_order' => '0',
            'stop_rules_processing' => '0',
            'from_time' => '1254326400',
            'to_time' => '1604073600',
            'member_lv_ids' => '1,2,3',
            'member_type_ids' => '0,1,2',
            'c_template' => 'promotion_conditions_order_userdefined',
            //'conditions' => '规则条件:序列化',
            /**
                type:大于=gt 小于=lt 等于=eq 小于等于=lte 大于等于=gte
                value: 正整数
            **/
            'conditions' => array(
                //交款方式可单选： 1= 一次性交全款 2= 预报+补费
                'condition_1' => array('value'=>1),
                //补费时学员身份可单选: 1=新生 2=在校生 3=会员
                'condition_2' => array('value'=>1),
            ),
            /**
                暂时没用到
            */
            'action_conditions' => '动作执行条件:序列化',

            /**
               s_template可选 
               1、订单以固定折扣出售(promotion_solutions_topercent) 
               2、订单固定价格购买(promotion_solutions_tofixed)
               3、订单减固定价格出售(promotion_solutions_byfixed)
            */
            's_template' => 'promotion_solutions_byfixed',
            //'action_solution' => '动作方案:序列化',
            'action_solution' => array(
                'solution_1' => array('value'=>5000),
            ),
        ),
        
        4 => array(
            'name' => '校长特别折扣优惠',
            'description' => '长期有效',
            'status' => '1',
            'sort_order' => '0',
            'stop_rules_processing' => '0',
            'from_time' => '0',
            'to_time' => '0',
            'member_lv_ids' => '',
            'member_type_ids' => '',
            
            /* 优惠条件 */
            'c_template' => 'promotion_conditions_school_special_discount',
            //'conditions' => '规则条件:序列化',
            /**
                校区ID
            **/
            'conditions' => array(
                'condition_1' => array(
                    array('type'=>'eq','value'=>10),
                ),
            ),
            /**
                暂时没用到
            */
            'action_conditions' => '',
            
            /* 优惠方案 */
            /**
               s_template可选 
               1、订单以固定折扣出售(promotion_solutions_topercent) 
               2、订单固定价格购买(promotion_solutions_tofixed)
               3、订单减固定价格出售(promotion_solutions_byfixed)
            */
            's_template' => 'promotion_solutions_topercent',
            //'action_solution' => '动作方案:序列化',
            'action_solution' => array(
                'solution_1' => array(array('value'=>95)),
            ),
        ),

);
        //假设
        $schoolId = model('users')->getSchoolId(['userId'=>$params['userId']]);
        $school_setting = getSchoolDiscountSetting(['schoolId'=>$schoolId]);
        $school_special = array(
            'rule_id' => 1000001,
            'name' => '校长特别折扣优惠',
            'description' => '长期有效',
            'status' => '1',
            'sort_order' => '0',
            'stop_rules_processing' => '0',
            'from_time' => 0,
            'to_time' => 0,
            'member_lv_ids' => '',
            'member_type_ids' => '',
            
            /* 优惠条件 */
            'c_template' => 'promotion_conditions_school_special_discount',
            //'conditions' => '规则条件:序列化',
            /**
                校区ID
            **/
            'conditions' => array(
                'condition_1' => array(
                    array('type'=>'eq','value'=>$schoolId),
                ),
            ),
            'conditions_old' => serialize(array(
                'condition_1' => array(
                    array('type'=>'eq','value'=>$schoolId),
                ),
            )),
            /**
                暂时没用到
            */
            'action_conditions' => '',
            'action_solution_old' => serialize([]),
            /* 优惠方案 */
            /**
               s_template可选 
               1、订单以固定折扣出售(promotion_solutions_topercent) 
               2、订单固定价格购买(promotion_solutions_tofixed)
               3、订单减固定价格出售(promotion_solutions_byfixed)
            */
            's_template' => 'promotion_solutions_byfixed',
            //'action_solution' => '动作方案:序列化',
            'action_solution' => array(
                'solution_1' => array(array('value'=>$school_setting['discount'])),
            ),
        );

        $time = time();
        $where = [];
        $where['status'] = 1;
        $where['from_time'] = ['<=',$time];
        $where['to_time'] = ['>',$time];
        if(isset($params['type_id']))
        {
            $where['rule_type'] = $params['type_id']; 
        }
        if(isset($params['type_id'])){
			 $params['type_id'] = intval($params['type_id']);
			 $where['rule_type'] = ['like',"%{$params['type_id']}%"];
        }
		if(isset($params['rule_use'])){
             $params['rule_use'] = intval($params['rule_use']);
             $where['rule_use'] = ['like',"%{$params['rule_use']}%"];
        }
        //$userInfo = Db::name('users')->where('userId',$params['userId'])->find();
        $userInfo = [];
        $uidType = !empty($userInfo['uidType'])?$userInfo['uidType']:1;
        $userRankId = !empty($userInfo['userRankId'])?$userInfo['userRankId']:1;
        if(!empty($uidType)){
             $where['member_type_ids'] = ['like',"%{$uidType}%"];
        }
        if(!empty($userRankId)){
             $where['member_lv_ids'] = ['like',"%{$userRankId}%"];
        }
        $has_one = [];
        $rs = $this->where($where)->select();
        foreach($rs as $k=>&$v)
        {
             $v['conditions_old'] = $v['conditions'];
             $v['conditions'] = !empty($v['conditions'])?unserialize($v['conditions']):'';
             //过滤相同条件的规则，只保留了一条
             $v['c_template']=='promotion_conditions_order_itemsquanityallsubjects' && $key = $v['c_template'].'_'.$v['conditions']['condition_1'][0]['value'].'_'.$v['conditions']['condition_1'][1]['value'];
             $v['c_template']=='promotion_conditions_order_usersquanityall' && $key = $v['c_template'].'_'.$v['conditions']['condition_1'][0]['value'].'_'.$v['conditions']['condition_1'][1]['value'];
             $v['c_template']=='promotion_conditions_order_userdefined' && $key = $v['c_template'].'_'.$v['conditions']['condition_1'][0]['value'].'_'.$v['conditions']['condition_2'][0]['value'];
             //dump($has_one);
             if(in_array($key,$has_one)) 
             {
                 //echo $key,'<hr>';exit;
                 //unset($rs[$k]);
                 continue;
             }
             //echo $key,'<hr>';
             $has_one[] = $key;
             $v['action_solution_old'] = $v['action_solution'];
             $v['action_solution'] = !empty($v['action_solution'])?unserialize($v['action_solution']):'';
             $v['from_time_format'] = date('Y-m-d',$v['from_time']);
             $v['to_time_format'] = date('Y-m-d',$v['to_time']);
             $v['time_desc'] = '有效期：'.$rs[$k]['from_time_format'].'到'.$rs[$k]['to_time_format'];
             $v['rule_desc_format'] = $this->format_condition($v);
             //会员等级
             if(!empty($v['member_lv_ids']))
             {
                if(!in_array($userRankId,explode(',',$v['member_lv_ids'])))
                {
                   unset($rs[$k]);
                }
             }
             //报名时身份
             if(!empty($v['member_type_ids']))
             {
                if(!in_array($uidType,explode(',',$v['member_type_ids'])))
                {
                   unset($rs[$k]);
                }
             }
        }
        //if(isset($params['smsVcode']) && $params['smsVcode']==model('Sms')->get_sms_vcode($params))
        if(!empty($params['schoolDiscountPrice']))
        {
            //$school_special['schoolDiscountPrice'] = $params['schoolDiscountPrice'];
            $school_special['action_solution']['solution_1'][0]['value'] = $params['schoolDiscountPrice'];
            array_push($rs,$school_special);
        }
        #dump($rs);exit;
        return $rs;
    }
    
    //转换符号
    public function transform_scope($scope='',$type=1)
    {
        $arr = array(
            'lt' => '<',
            'gt' => '>',
            'eq' => '==',
            'lte' => '<=',
            'gte' => '>=',
        );
        $arr2 = array(
            'lt' => '小于',
            'gt' => '大于',
            'eq' => '等于',
            'lte' => '小于等于',
            'gte' => '大于等于',
        );
        $type==2 && $arr = $arr2;
        return $arr[$scope];   
    }
    //优惠方案价格处理
    public function get_solution_price(&$params,&$orderInfo,&$right_condition)
    {
         $c_key = $right_condition['c_template'];
         $key = $right_condition['s_template'];
         $arr_rate = array(
            'promotion_solutions_topercent' => 100,
            'promotion_solutions_tofixed' => 1,
            'promotion_solutions_byfixed' => 1,
         );
         $solution_price = 0;
         $action_solution = $right_condition['action_solution'];
         foreach($action_solution as $k=>$v):
            if($k == 'solution_1'):
                $solution_price += (float)($v[0]['value']/$arr_rate[$key]);
            endif;
         endforeach;
         #echo $orderInfo['orderInfo']['full_pay_price'],'<hr>';
         $realPayMoney = $orderInfo['orderInfo']['full_pay_price'];
         #$realTotalMoney = $orderInfo['orderInfo']['realTotalMoney'];
         if($key == 'promotion_solutions_topercent')
         {
             $o_solution_price = $realPayMoney*$solution_price;
             $pmt_order_price = $realPayMoney-$realPayMoney*$solution_price;
         }
         elseif($key == 'promotion_solutions_tofixed')
         {
             $o_solution_price = $solution_price;
             $pmt_order_price = $realPayMoney-$solution_price;
         }
         elseif($key == 'promotion_solutions_byfixed')
         {
             $o_solution_price = $realPayMoney-$solution_price;
             $pmt_order_price = $solution_price;
         }
         if($realPayMoney<$pmt_order_price) return false;
         //校长特殊优惠,判断是否超过设置的折扣
         if($c_key=='promotion_conditions_school_special_discount')
         {
            $schoolId = model('users')->getSchoolId(['userId'=>$params['userId']]);
            $school_setting = getSchoolDiscountSetting(['schoolId'=>$schoolId]);
            $pmt_school_solution_price = $realPayMoney*(1-$school_setting['discount']/100);
            if($pmt_order_price>$pmt_school_solution_price) return false; 
         }//end
         
         $orderInfo['orderInfo']['pmt_order'] += $pmt_order_price;
         $orderInfo['orderInfo']['okRule'][$c_key.'_'.$right_condition['rule_id']] = getNumFormat($pmt_order_price);
         //优惠券重新计算
         /*if($o_solution_price>=$orderInfo['orderInfo']['discountMoney'])
         {
             $o_solution_price = $o_solution_price-$orderInfo['orderInfo']['discountMoney'];
             $realTotalMoney = $realTotalMoney-$orderInfo['orderInfo']['discountMoney'];
         }
         else
         {
             $orderInfo['orderInfo']['discountMoney'] = 0;   
         }*/
         //$orderInfo['orderInfo']['realTotalMoney'] = $realTotalMoney+$orderInfo['orderInfo']['notfull_pay_price']+$orderInfo['orderInfo']['adItMoney'];
         //$orderInfo['orderInfo']['realPayMoney'] = $o_solution_price;
         $orderInfo['orderInfo']['full_pay_price'] = $o_solution_price;
         return true;
    }
    
    //条件动态执行代码
    public function run_if_code($curr_count=0,$v)
    {
        $is_condition=false;
        $eval_str = [];
        foreach($v as $kk=>$vv):
            if($vv['type']!='' && $vv['value']!='')
            {
                $condition_type = $this->transform_scope($vv['type']);
                $condition_value = $vv['value'];
                $eval_str[] = "$curr_count $condition_type $condition_value";
            }
        endforeach;
        if(!empty($eval_str)):
            $eval_code = implode(' AND ',$eval_str);
            $eval_code = 'if('.$eval_code.') {$is_condition=true;}';
            eval($eval_code);
        endif;
        return $is_condition;
    }
    /**
    * @do 根据当前符合促销的规则，获取即将符合的下一个促销规则
    */
    public function get_next_soon_rule($lists,$rules,$key='',$rule_id=0)
    {
        if($rule_id=='1000001' || $key=='promotion_conditions_order_userdefined') return;
        $tmp_rules = [];
        foreach($lists as $v):
            $tmp_rules[$v['c_template']][] = $v['rule_id'];
        endforeach;
        $cur_key = array_search($rule_id,$tmp_rules[$key]);
        $next_key = $cur_key+1;
        $next_rule_id = 0;
        !empty($tmp_rules[$key][$next_key]) && $next_rule_id=$tmp_rules[$key][$next_key];
        $next_soon_rule = [];
        $next_rule_id>0 && $next_soon_rule = $rules[$next_rule_id];
        return $next_soon_rule;
    }
    /**
     * @do 格式化条件信息
    */
    public function format_condition(&$v)
    {
        $c_key = $v['c_template'];
        $s_key = $v['s_template'];
        $condition_arr = !empty($v['conditions_old'])?unserialize($v['conditions_old']):'';
        $eval_str = [];
        $rule_desc_format = '';
        #科目数量
        if($c_key=='promotion_conditions_order_itemsquanityallsubjects'):
            foreach($condition_arr['condition_1'] as $kk=>$vv):
                if($vv['type']!='' && $vv['value']!='')
                {
                    $condition_type = $this->transform_scope($vv['type'],2);
                    $condition_value = $vv['value'];
                    $eval_str[] = "$condition_type$condition_value";
                }
            endforeach;
            $rule_desc_format .= '科目数量'.implode('',$eval_str);
        endif;
        #团购信息
        if($c_key=='promotion_conditions_order_usersquanityall'):
            foreach($condition_arr['condition_1'] as $kk=>$vv):
                if($vv['type']!='' && $vv['value']!='')
                {
                    $condition_type = $this->transform_scope($vv['type'],2);
                    $condition_value = $vv['value'];
                    $eval_str[] = "$condition_type$condition_value";
                }
            endforeach;
            $rule_desc_format .= '团购数量'.implode('',$eval_str);
        endif;
        #自定义信息
        if($c_key=='promotion_conditions_order_userdefined'):
            #交款方式
            $eval_str[] = '交款方式为'.ITSSelItemName('user','orderType',$condition_arr['condition_1'][0]['value']);
            #学员身份
            $eval_str[] = '学员身份为'.ITSSelItemName('user','uidType',$condition_arr['condition_2'][0]['value']);
            $rule_desc_format .= implode('，',$eval_str);
        endif;
        #优惠方案
        $solution_arr = !empty($v['action_solution_old'])?unserialize($v['action_solution_old']):'';
        #订单以固定折扣出售
        if($s_key=='promotion_solutions_topercent'):
           $rule_desc_format .= '，订单以'.round($solution_arr['solution_1'][0]['value']/10,2).'折出售';
        endif;
        #订单以固定价格出售
        if($s_key=='promotion_solutions_tofixed'):
           $rule_desc_format .= '，订单以'.$solution_arr['solution_1'][0]['value'].'元出售'; 
        endif;
        #订单减固定价格出售
        if($s_key=='promotion_solutions_byfixed'):
           $rule_desc_format .= '，订单减去'.$solution_arr['solution_1'][0]['value'].'元出售';  
        endif;
        return $rule_desc_format;  
    }
    /**
     * @do 获取规则列表
    */
    public function getRuleLists($params=[],$userInfo=[])
    {
        return $this->get_lists($params);   
    }
}
