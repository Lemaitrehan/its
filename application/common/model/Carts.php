<?php
namespace application\common\model;
use think\Db;
use application\common\model\SalesRuleOrder as SalesRuleOrder;
/**
 * 购物车业务处理类
 */
class Carts extends Base{
    //添加购物车
    public function getApiSetCart($params=[],$userInfo=[])
    {
        return $this->set_data($params,$userInfo);
    }
    //删除购物车
    public function getApiDelCart($params=[],$userInfo=[])
    {
        return $this->del_data($params,$userInfo);
    }
    /**
     * @do 购物车列表
     * @desc 1
     * @type=1 1=购物车列表 2=下单前处理
     * @params 模拟参数 适用于后台或内部调用
     * @custom_data 模拟一条购物车数据
     *  EXAMPLE:['cartId'=>xxx,'userId'=>xxx]
     * @
     */
    public function getApiCartList($type=1,$params=[],$userInfo=[],$custom_data=[])
    {
        try{
//            !empty($custom_data) && $params = $_POST;
        //isset($_GET['demo']) && $params = $_POST;
        if(empty($params['jump_type']))
        {
            return MBISReturn("缺少参数[jump_type]");
        }
        $params['type_id'] = $params['jump_type'];
        $userId = $params['userId'];
        $agent_uid = 0;
        $userType = $userInfo['userType'];
        //学员自己购买 userType==0
        if($userType==0 && empty($params['orderData']['orderInfo']['name'])){
            $params['orderData']['orderInfo']['idcard'] = $userInfo['idcard'];
            $params['orderData']['orderInfo']['mobile'] = $userInfo['userPhone'];
            $params['orderData']['orderInfo']['name'] = $userInfo['trueName'];
        }
        //代购处理
        if( !empty($userInfo['isAgentUser']) )
        {
            $userId = 0;
            $agent_uid = $params['userId'];
            $params['userId|agent_uid'] = $agent_uid;
        }
        else
        {
            $params['userId'] = $userId;   
        }
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
        //普通处理
        empty($params['isFastBuy']) && $rs = $this->get_lists($params);
        //后台添加处理
        !empty($custom_data) && $rs = $custom_data;
        //快速购买处理
        if(!empty($params['isFastBuy']) ):
            $custom_cartId = 'no_cart';
            //课程快速购买
            $params['show_type_id']==1 && $rs = [
                [
                    'cartId' => $custom_cartId,
                    'type_id' => $params['jump_type'],
                    'userId' => $params['userId'],
                    'course_id' => $params['cartData'][$custom_cartId]['course_id'],
                    'subject_id' => '0',
                    'agent_uid' => '0',
                    'cartNum' => '1',
                    'extend_data' => serialize(['subject_ids'=>array_keys($params['cartData'][$custom_cartId]['subjectList'])]),
                ]
            ];
            //课目快速购买
            in_array($params['show_type_id'],[2,3]) && $rs = [
                [
                    'cartId' => $custom_cartId,
                    'type_id' => $params['jump_type'],
                    'userId' => $params['userId'],
                    'course_id' => 0,
                    'subject_id' => $params['cartData'][$custom_cartId]['subject_id'],
                    'agent_uid' => '0',
                    'cartNum' => '1',
                    'extend_data' => serialize(['subject_ids'=>[] ]),
                ]
            ];
        endif;//快速购买END
        if(empty($rs)) return MBISReturn("购物车数据为空"); 
        if($params['type_id']==1 || $params['type_id']==2)
        {
            if(empty($params['cartData']))
            {
                if(!empty($rs))
                {
                   foreach($rs as $k=>$v)
                   {
                       $tmp_aditem = [];
                       //技能科目 >>单个科目
                       if(!empty($v['subject_id']) && $v['subject_id']>0)
                       {
                           $it_ids = model('CourseItem')->get_it_ids($v['type_id'],0,$v['subject_id']);
                       }
                       else
                       {
                            $it_ids = model('CourseItem')->get_it_ids($v['type_id'],$v['course_id'],0);
                       }
                        if(!empty($it_ids))
                        {
                            $lists_aditem = model('AdItem')->get_lists(['it_id'=>['in',$it_ids],'field'=>'it_id,name,price']);
                            foreach($lists_aditem as $it_k=>$it_v)
                            {
                                $tmp_aditem[$it_v['it_id']] = 0;
                            }
                        }
                        $tmp_subject = [];
                        if(!empty($v['subject_id']) && $v['subject_id']>0)
                        {
                            $tmp_subject[$v['subject_id']]=1;  
                        }
                        else
                        {
                            //技能课程 >>带科目列表
                            if($params['type_id']==2)
                            {
                                $extend_data = unserialize($v['extend_data']);
                                if(!empty($extend_data['subject_ids']))
                                {
                                    $subject_ids = $extend_data['subject_ids'];
                                    foreach($subject_ids as $subject_id)
                                    {
                                        $tmp_subject[$subject_id]=1;
                                    }
                                }
                            }
                        }
                       //学历
                       $params['type_id']==1 && $params['cartData'][$v['cartId']] = [
                            'course_id' => $v['course_id'],
                            'subject_id' => 0,
                            'adItemList' => $tmp_aditem,
                            'is_full_pay' => '1',
                            'add_deposit_price'=>0
                       ];  
                       //技能
                       $params['type_id']==2 && $params['cartData'][$v['cartId']] = [
                            'course_id' => $v['course_id']>0?$v['course_id']:0,
                            'subject_id' => $v['subject_id']>0?$v['subject_id']:0,
                            'subjectList' => $tmp_subject,
                            'adItemList' => $tmp_aditem,
                            'is_full_pay' => '1',
                            'add_deposit_price'=>0
                       ];  
                   }
                }
            }
            if(empty($params['orderData']))
            {
                $params['orderData'] = array(
                    //订单信息参数
                    'orderInfo' => array(
                        'discountMoney' => 0,
                        'name' => '',
                        'mobile' => '',
                        'idcard' => '',
                        //团购用户信息
                        'teamLists' => array(
                            array('name' => '','mobile' => '','idcard' => ''),
                        ),
                        'pmt_order' => 0,//订单促销金额
                    ),
                    //支付方式参数
                    'paymentInfo' => array(
                        1 => 2
                    )
                 );
            }
            if(empty($params['orderData']['orderInfo']['name'])) $params['orderData']['orderInfo']['name']='';
            if(empty($params['orderData']['orderInfo']['mobile'])) $params['orderData']['orderInfo']['mobile']='';
            if(empty($params['orderData']['orderInfo']['idcard'])) $params['orderData']['orderInfo']['idcard']='';
            if(empty($params['orderData']['orderInfo']['addr'])) $params['orderData']['orderInfo']['addr']='';
            if(empty($params['orderData']['orderInfo']['taxType'])) $params['orderData']['orderInfo']['taxType']=1;
            if(empty($params['orderData']['orderInfo']['taxCompany'])) $params['orderData']['orderInfo']['taxCompany']='';
            if(empty($params['orderData']['orderInfo']['teamLists'])) $params['orderData']['orderInfo']['teamLists']= array('name' => '','mobile' => '','idcard' => '');
            if(empty($params['orderData']['orderInfo']['pmt_order'])) $params['orderData']['orderInfo']['pmt_order']= 0;
            if(empty($params['orderData']['paymentInfo'])) $params['orderData']['paymentInfo']= array(1 => 2);
            if(empty($params['channelType'])) $params['channelType']= 2;
            if(empty($params['schoolDiscountPrice'])) $params['schoolDiscountPrice']= 0;
            if(empty($params['signPicUrl'])) $params['signPicUrl']= '';
            $params['type_id']==1 && $rs_course = model('common/course')->merge_grades($rs,$params);
            $params['type_id']==2 && $rs_course = model('common/course')->merge_subjects($rs,$params);
        }
        #echo '<pre>';var_export($params);exit;
        $rs_course = array_merge(['jump_type'=>$params['type_id']],$rs_course);
        if(isset($rs_course['status']) && $rs_course['status']==-1)
        {
            //return MBISReturn($rs_course['msg']);
        }
        //if(empty($rs_course['courseInfo'])) return MBISReturn('数据结构有误[courseInfo]',-1);
        #@file_put_contents('./api_returns.log',var_export($rs_course,true).chr(10),FILE_APPEND);
            if($type==2) return $rs_course;
            return MBISReturn("",1,$rs_course);
        }
        catch(\Exception $e) 
        {
            add_logs('exception/cart_lists','',true,$e);
            return MBISReturn('获取数据失败',4001);
        }
    }
    
    /**
     * @do配合协议 && 获取购物车数据
     * @params 请求参数
     * @userInfo 用户信息
     */
    public function getApiStatementData($params=[],$userInfo=[])
    {
        $rs_course = $this->getApiCartList(2,$params,$userInfo);
        $tmpl = $this->checkoutStatementData($params,$userInfo,$rs_course);
        return MBISReturn("获取数据成功", 1, ['name'=>$tmpl['name'],'url'=>$tmpl['statement_url'] ]);
    }
    //下单 && 补缴 >> 合同处理
    public function checkoutStatementData($params,$userInfo,&$rs_course)
    {
        $tmp_data = [];
        foreach($rs_course['courseInfo'] as $k=>$v)
        {
           $course = $v['course'];
           $course['type_id']==1 && !empty($course['course_id']) && $tmp_data['subjects'][] = [
                    'school_name' => (string)$course['school_name'],//院 校
                    'major_name' => $course['major_name'],//专 业
                    'level_type' => $course['level_type'],//学历层次
                    'exam_type' => $course['exam_type'],//报考类型
                    'study_type' => '',//学习形式
                    'graduate_type' => $course['graduate_type'],//学制
                    #'grade_name'=>$course['grade_name'],//学制
                    'item_price'=>(int)$course['stu_fee'],//标准学费
                    'entry_price'=>(int)$course['stu_fee'],//报名费
                    'textbook_price'=>!empty($v['adItemList'])?$this->get_aditem_price($v['adItemList']):'',//教材费
                    'paper_price'=>'0',//论文指导费
                ];
           $course['type_id']==2 && !empty($course['course_id']) && $tmp_data['subjects'][] = [
                    'major_name' => (string)$v['subjectList'][0]['major_name'],
                    'item_name'=>$course['name'],
                    'item_no'=>(string)$course['course_bn'],
                    'item_price'=>$course['offers_price'],
                ];
           $course['type_id']==2 && empty($course['course_id']) && $tmp_data['subjects'][] = [
                    'major_name' => $course['major_name'],
                    'item_name'=>$course['name'],
                    'item_no'=>$course['subject_no'],
                    'item_price'=>$course['sale_price'],
                ];    
               
        }
        #类型
        $tmp_data['jump_type'] = $params['jump_type'];
        #学员签名地址
        //$tmp_data['order']['signComPicUrl'] = 'https://ss1.bdstatic.com/70cFuXSh_Q1YnxGkpoWK1HF6hhy/it/u=792260938,3344439313&fm=23&gp=0.jpg';
        #学员签名地址
        $tmp_data['order']['signPicUrl'] = !empty($params['signPicUrl'])?ITSPicUrl($params['signPicUrl']):'';
        #应交学费总额
        $tmp_data['order']['orderType'] = $rs_course['orderInfo']['orderType'];
        #应交学费总额
        $tmp_data['order']['realTotalMoney'] = $rs_course['orderInfo']['realTotalMoney'];
        #交纳学费定金
        $tmp_data['order']['notfull_pay_price'] = $rs_course['orderInfo']['notfull_pay_price'];
        #乙方
        $tmp_data['order']['name'] = $rs_course['orderInfo']['name'];
        #证件号码
        $tmp_data['order']['idcard'] = $rs_course['orderInfo']['idcard'];
        #联系电话
        $tmp_data['order']['mobile'] = $rs_course['orderInfo']['mobile'];
        #客户地址
        #dump($rs_course['orderInfo']);exit;
        $tmp_data['order']['addr'] = $rs_course['orderInfo']['addr'];
        #累计科目
        $tmp_data['order']['pmt_info']['promotion_conditions_order_itemsquanityallsubjects'] = !empty($rs_course['orderInfo']['userCondition']['promotion_conditions_order_itemsquanityallsubjects']['user']['subject_count'])?$rs_course['orderInfo']['userCondition']['promotion_conditions_order_itemsquanityallsubjects']['user']['subject_count']:0;
        #交款方式
        $tmp_data['order']['pmt_info']['promotion_conditions_order_userdefined']['orderType'] = !empty($rs_course['orderInfo']['userCondition']['promotion_conditions_order_userdefined']['user']['orderType'])?$rs_course['orderInfo']['userCondition']['promotion_conditions_order_userdefined']['user']['orderType']:1;
        #客户身份
        $tmp_data['order']['pmt_info']['promotion_conditions_order_userdefined']['uidType'] = !empty($rs_course['orderInfo']['userCondition']['promotion_conditions_order_userdefined']['user']['uidType'])?$rs_course['orderInfo']['userCondition']['promotion_conditions_order_userdefined']['user']['uidType']:1;
        #团购优惠
        $tmp_data['order']['pmt_info']['promotion_conditions_order_usersquanityall'] = !empty($rs_course['orderInfo']['userCondition']['promotion_conditions_order_usersquanityall']['promotion_solutions_topercent'])?$rs_course['orderInfo']['userCondition']['promotion_conditions_order_usersquanityall']['promotion_solutions_topercent']:1;
        #其    他
        $tmp_data['order']['pmt_info']['promotion_conditions_school_special_discount'] = !empty($rs_course['orderInfo']['userCondition']['promotion_conditions_school_special_discount']['user']['schoolId'])?$rs_course['orderInfo']['userCondition']['promotion_conditions_school_special_discount']['user']['schoolId']:0;
        $statement_url = url('api/orders/make_statement_tmpl','',false,true).'?'.http_build_query($tmp_data);
        //$statement_url =  "<a href='{$statement_url}' target='_blank'>点击</a>";
        //dump($rs_course);exit;
        $userId = $params['userId'];
        $agent_uid = 0;
        $userType = 0;//0=普通学员 1=老师 2=咨询师
        if(!empty($userInfo['userType'])) $userType=$userInfo['userType'];
        if($userType==2)
        {
            $userId = 0;
            $agent_uid = $params['userId'];
        }
        //写人合同信息
        $contract_data = [
            'userId' => $userId,
            'agent_uid' => $agent_uid,
            'type_id' => $rs_course['jump_type'],
            //'orderNo' => $params['userId'],
            //'orderId' => $params['userId'],
            'name' => $rs_course['orderInfo']['name'],
            'mobile' => $rs_course['orderInfo']['mobile'],
            'idcard' => $rs_course['orderInfo']['idcard'],
            'url' => $statement_url,
            'userSignImg' => @$params['signPicUrl'],
            //'isUse' => $params['userId'],
            'createtime' => time(),
            'lastmodify' => time(),
        ];
        !empty($params['orderId']) && $contract_data['supplementNum'] = model('common/orders')->getSupplementNum($params['orderId']);
        !empty($contract_data['userSignImg']) && Db::name('order_contract_log')->insert($contract_data,false,true);
        #学历
        $tmp_data['jump_type']==1 && $tmp_data['order']['orderType']==1 && $key = 'edu_full';
        $tmp_data['jump_type']==1 && $tmp_data['order']['orderType']==2 && $key = 'edu_notfull';
        #技能
        $tmp_data['jump_type']==2 && $tmp_data['order']['orderType']==1 && $key = 'skill_full';
        $tmp_data['jump_type']==2 && $tmp_data['order']['orderType']==2 && $key = 'skill_notfull';
        #$key = 'edu_notfull';
        $tmpl = get_statement_tmpl($key);
        $tmpl['statement_url'] = $statement_url;
        return $tmpl;
    }
    //计算学杂费金额
    public function get_aditem_price($lists=[])
    {
        if(empty($lists)) return '';
        $aditem_price = 0;
        foreach($lists as $v)
        {
            $aditem_price += $v['price'];   
        }
        return $aditem_price;
    }
    
    //获取购物车列表
    public function get_lists($params=[])
    {
        $where = [];
        if(isset($params['type_id']))
        {
            $where['type_id'] = $params['type_id']; 
        }
        if(isset($params['cartId']))
        {
            $where['cartId'] = $params['cartId']; 
        }
        if(isset($params['userId|agent_uid']))
        {
            $where['userId|agent_uid'] = $params['userId|agent_uid']; 
        }
        else
        {
            if(isset($params['userId']))
            {
                $where['userId'] = $params['userId'];
            }
        }
        $rs = $this->where($where)->select();
        return $rs;
    }
    //写入数据
    public function set_data($params=[],$userInfo=[])
    {
        if(empty($params['type_id']))
        {
            return MBISReturn("缺少参数[jump_type]");
        }
        if(empty($params['userId']))
        {
            return MBISReturn("缺少参数[userId]"); 
        }
        $userId = $params['userId'];
        $agent_uid = 0;
        $userType = $userInfo['userType'];
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
        $data['type_id'] = $params['type_id'];
        $data['userId'] = $userId;
        $data['agent_uid'] = $agent_uid;
        $data['cartNum'] = 1;
        $filter_cart['type_id'] = $params['type_id'];
        //$filter_cart['userId|agent_uid'] = $userId;
        if(!empty($params['course_id']))
        {
            if((int)$params['course_id']<=0) return MBISReturn("参数[course_id]有误");
            $data['course_id'] = $params['course_id'];
            $filter_cart['course_id'] = $params['course_id'];
            $data['subject_id'] = 0;
        }
        if(!empty($params['subject_id']))
        {
            if((int)$params['subject_id']<=0) return MBISReturn("参数[subject_id]有误");
            $data['subject_id'] = $params['subject_id'];
            $filter_cart['subject_id'] = $params['subject_id'];
            $data['course_id'] = 0;
        }
        $data['extend_data'] = '';
        if($params['type_id'] == 2)
        {
            if(!empty($params['subject_ids']))
            {
                $extend_data['subject_ids'] = explode(',',$params['subject_ids']);
                $data['extend_data'] = serialize($extend_data);
            }
            else
            {
                if(!empty($params['course_id']))
                {
                    if((int)$params['course_id']<=0) return MBISReturn("参数[course_id]有误");
                    $extend_data['subject_ids'] = model('CourseSubject')->get_subject_ids($params['course_id']);
                    $data['extend_data'] = serialize($extend_data);
                }
            }
        }
        $rs = false;
        if(!empty($params['course_id']) || !empty($params['subject_id']))
        {
            $rs_cart = $this->where($filter_cart)->find();
            if( !empty($rs_cart['cartId']) )
            {
                $rs = $this->update($data,['cartId'=>$rs_cart['cartId']]);   
            }
            else
            {
                $rs = $this->data($data)->save();
            }
            if(false !== $rs){
                return MBISReturn("加入购物车成功", 1);
            }else{
                return MBISReturn("加入购物车失败");
            }
        }
        else
        {
            return MBISReturn("参数有误[course_id/subject_id]");   
        }
    }
    //删除数据
    public function del_data($params=[],$userInfo=[])
    {
        if(empty($params['type_id']))
        {
            return MBISReturn("缺少参数[jump_type]");
        }
        if(empty($params['userId']))
        {
            return MBISReturn("缺少参数[userId]"); 
        }
        if(empty($params['cartId']))
        {
            return MBISReturn("缺少参数[cartId]"); 
        }
        $filter_cart['type_id'] = $params['type_id'];
        $userId = $params['userId'];
        $agent_uid = 0;
        $userType = $userInfo['userType'];
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
        //$filter_cart['userId|agent_uid'] = $params['userId'];
        if(!empty($params['cartId']))
        {
            $cartIds = explode(',',$params['cartId']);
            $filter_cart['cartId'] = ['in',$cartIds];
        }
        $rs = $this->where($filter_cart)->delete();
        if(false !== $rs){
            return MBISReturn("删除购物车成功", 1);
        }else{
            return MBISReturn("删除购物车失败");
        }
    }
    public function delAllInfo(){
        $data = input('post.');
        $course_id = $data['cartId'];
        $info_array = explode(',', $course_id);
        foreach($info_array as $k=>$v){
           $rs = $this->where('cartId',$v)->delete(); 
        }      
        if($rs == 1){
            return MBISReturn('清空购物车成功',1);
        }else{
            return MBISReturn('清空购物车失败');
        }
    }
    //统计当前用户的购物车数量
    public function getCartNums($params=[],$userInfo=[])
    {
        if(empty($params['type_id']))
        {
            return MBISReturn("缺少参数[jump_type]");
        }
        $filter_cart['type_id'] = $params['type_id'];
        $userId = $params['userId'];
        $agent_uid = 0;
        $userType = $userInfo['userType'];
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
        $count = $this->where($filter_cart)->count();
        return MBISReturn("获取数据成功", 1, ['cartNums'=>$count]);
    }
    
	
}
