<?php
namespace application\common\model;
/**
 * 学历/非学历 >> 课程科目业务处理
 */
use think\Db;
class Course extends Base{
	//通过grade_id获取课程ID
    public function get_course_id($where=[])
    {
        return (int)$this->where($where)->value('course_id');   
    }
    //合并年级信息
    public function merge_grades($rs,$params=[])
    {
        $type_id = $params['jump_type'];
        $cartData = $params['cartData'];
        $orderInfo = $params['orderData']['orderInfo'];
        if(empty($orderInfo['discountMoney']) || $params['channelType']==2) $orderInfo['discountMoney']=0;
        if(empty($orderInfo['pmt_order']) || $params['channelType']==1) $orderInfo['pmt_order']=0;
        $paymentInfo = $params['orderData']['paymentInfo'];
        $course_ids = [];
        foreach($rs as $k=>$v)
        {
            $course_ids[] = $v['course_id'];   
        }
        //$course_ids = $this->filter_course_ids($type_id,$course_ids);
        if(empty($course_ids))
        {
            return MBISReturn("课程信息不完整[course_ids]");   
        }
        //课程信息
        $tmp_rs_course = [];
        if(isset($params['isImport']))
        {
            $rs_course = $this->where(['course_id'=>['in',$course_ids]])->field('*')->select();
        }
        else
        {
            $rs_course = $this->get_lists(['course_id'=>['in',$course_ids]]);
        }
        //dump($rs_course);exit;
        //合并年级信息
        //$rs_course = model('common/grade')->merge_grades($rs_course);
        foreach($rs_course as $k=>$v)
        {
            $tmp_rs_course[$v['course_id']] = $v;
        }
        //dump($rs_course);exit;
        $tmp_rs = [];
        $i = 0;
        //课程总金额
        $courseMoney = 0;
        //订单总金额
        $totalMoney = 0;
        //订单实付总金额 - 订单优惠
        $realTotalMoney = 0;
        //订单最小定金
        $depositMoney = 0;
        //订单追加定金
        $depositAddMoney =0;
        //尾款
        $depositRemainMoney = 0;
        //订单学杂费总额
        $adItMoney = 0;
        //订单全款金额
        $full_pay_price = 0;
        //订单非全款金额
        $notfull_pay_price = 0;
        //订单非全款应付款金额
        $notfull_deal_price = 0;
        $online_real_price=0;
        $one_total_sale_price = 0;
        foreach($rs as $k=>$v)
        {
            if(!empty($tmp_rs_course[$v['course_id']]))
            {
                $one_total_sale_price += $tmp_rs_course[$v['course_id']]['offers_price'];
            }
        }
        $tmp_discount_adver['one_total_sale_price'] = $one_total_sale_price;//END
        foreach($rs as $k=>$v)
        {
            if(!empty($tmp_rs_course[$v['course_id']]))
            {
                $cartInfo = $cartData[$v['cartId']];
                $full_pay_val = $cartInfo['is_full_pay'];
                $major_id = $v['major_id'] = $tmp_rs_course[$v['course_id']]['major_id'];
                $rs_major = model('common/major')->get_info(['major_id'=>$major_id]);
                $v['school_id'] = $rs_major['school_id'];
                $v['school_name'] = model('common/major')->get_school_name($major_id);
                $v['level_type'] = $rs_major['level_type'];
                $v['exam_type'] = $rs_major['exam_type'];
                $v['graduate_type'] = $rs_major['graduate_type'];
                $v['major_name'] = $rs_major['name'];
                //$v['grade_id'] = $tmp_rs_course[$v['course_id']]['grade_id'];
                //$v['grade_name'] = $tmp_rs_course[$v['course_id']]['grade_name'];
                $v['grade_id'] = '';
                $v['grade_name'] = '';
                //$v['teacher_id'] = $tmp_rs_course[$v['course_id']]['teacher_id'];
                //$v['teacher_name'] = $tmp_rs_course[$v['course_id']]['teacher_name'];
                $v['teacher_id'] = '0';
                $v['teacher_name'] = '';
                //标准价
                $v['stu_fee'] = $tmp_rs_course[$v['course_id']]['offers_price'];
                //优惠价
                $v['offers'] = $tmp_rs_course[$v['course_id']]['offers_price'];
                $course_name = $v['course_name'] = $tmp_rs_course[$v['course_id']]['name'];
                $v['cover_img'] = $tmp_rs_course[$v['course_id']]['cover_img'];
                $v['price'] = $tmp_rs_course[$v['course_id']]['offers_price'];
                $v['market_price'] = $tmp_rs_course[$v['course_id']]['market_price'];
                //最少预付定金
                $v['deposit_price'] = model('common/course')->get_deposit_price();
                //是否全额支付
                $v['is_full_pay'] = $cartInfo['is_full_pay'];
                $v['total_sale_price'] = getNumFormat($v['stu_fee']);
                $v['discount_aver_price'] = '0.00';
                //优惠金额平摊
                ($orderInfo['discountMoney']>0&&$tmp_discount_adver['one_total_sale_price']>0) && $v['discount_aver_price'] = getNumFormat(($v['stu_fee']/$tmp_discount_adver['one_total_sale_price'])*$orderInfo['discountMoney']);
                //应付款金额
                $v['deal_pay_price'] = $v['price'];
                //已付款金额
                $v['real_pay_price'] = $v['deal_pay_price'];
                //未付款金额
                $v['remain_pay_price'] = '0.00';
                //定金金额平摊
                $v['deposit_aver_price'] = $v['deposit_price']+$cartInfo['add_deposit_price'];
                if($full_pay_val==0)//非全款 
                {
                   $min_deposit = 0;
                   $max_deposit = $v['price']-$v['deposit_price'];
                   $remain_deposit = $v['price']-$v['deposit_price']-$cartInfo['add_deposit_price'];
                   //判断预付定金输入
                   if(!empty($cartInfo['add_deposit_price']) && ($cartInfo['add_deposit_price'] < $min_deposit || $cartInfo['add_deposit_price']>$max_deposit) )
                   {
                        return MBISReturn("课程[{$course_name}]输入预付定金参数有误[范围在{$min_deposit}-{$max_deposit}]");
                        exit;   
                   }
                   //科目实付定金
                   $v['course_real_price'] = $v['deposit_price']+$cartInfo['add_deposit_price'];
                   //已付款金额
                   $v['real_pay_price'] = $v['course_real_price'];
                   //未付款金额
                   $v['remain_pay_price'] = $v['deal_pay_price']-$v['real_pay_price'];
                   $depositMoney += $v['deposit_price'];
                   $depositAddMoney += $cartInfo['add_deposit_price'];
                   $depositRemainMoney += $remain_deposit;
                   //订单非全款金额
                   $notfull_pay_price += $v['real_pay_price'];
                   //订单非全款应付金额
                   $notfull_deal_price += $v['deal_pay_price'];
                }
                else
                {
                   //科目实付定金
                   $v['course_real_price'] = $v['price'];
                   //订单全款金额
                   $full_pay_price += $v['deal_pay_price'];
                   //定金金额平摊
                   $v['deposit_aver_price'] = '0.00';
                }
                //订单课程总加价
                $courseMoney += $v['price'];
                
                $tmp_rs['courseInfo'][$i]['course'] = $v;
                //学杂费
                $courseAdItMoney = 0;
                if(!empty($v['course_id']))
                {
                    //$it_ids = explode(',',$tmp_rs_course[$v['course_id']]['it_id']);
                    $it_ids = model('common/CourseItem')->get_it_ids($v['type_id'],$v['course_id'],0);
                    if(!empty($it_ids))
                    {
                        $lists_aditem = model('common/AdItem')->get_lists(['it_id'=>['in',$it_ids],'field'=>'it_id,name,price']);
                        foreach($lists_aditem as $it_k=>$it_v)
                        {
                            $lists_aditem[$it_k]['price'] = $cartInfo['adItemList'][$it_v['it_id']];
                            $adItMoney += $cartInfo['adItemList'][$it_v['it_id']];
                            $courseAdItMoney += $cartInfo['adItemList'][$it_v['it_id']];
                        }
                        $tmp_rs['courseInfo'][$i]['adItemList'] = $lists_aditem;
                    }
                }
                //订单应付/实付金额
                //$totalMoney += $v['price']+$courseAdItMoney;
                //$realTotalMoney += $v['course_real_price']+$courseAdItMoney;
                $totalMoney += $v['price']+$courseAdItMoney;
                $realTotalMoney += $v['course_real_price']+$courseAdItMoney;
                $i++;
            }
        }
        $this->common_info($params,$tmp_rs,$orderInfo,$paymentInfo,
    $courseMoney,$totalMoney,$realTotalMoney,$depositMoney,
    $depositAddMoney,$depositRemainMoney,$adItMoney,$type_id,$full_pay_val,
    $full_pay_price,$notfull_pay_price,$notfull_deal_price,$online_real_price);
        if($params['channelType']==1) //订单优惠金额通道
        {
            if($tmp_rs['orderInfo']['realPayMoney']<0)
            {
                $tmp_rs['orderInfo']['realPayMoney'] = abs($tmp_rs['orderInfo']['realPayMoney']);
                return MBISReturn("订单优惠价输入有误[超出{$tmp_rs['orderInfo']['realPayMoney']}]");  
            }
        }
        return $tmp_rs;
    }
    //合并科目信息
    public function merge_subjects($rs,$params=[])
    {
        $type_id = $params['jump_type'];
        $cartData = $params['cartData'];
        $orderInfo = $params['orderData']['orderInfo'];
        if(empty($orderInfo['discountMoney']) || $params['channelType']==2) $orderInfo['discountMoney']=0;
        if(empty($orderInfo['pmt_order']) || $params['channelType']==1) $orderInfo['pmt_order']=0;
        $paymentInfo = $params['orderData']['paymentInfo'];
        //dump($params);exit;
        $i = 0;
        $tmp_rs = [];
        //课程总金额
        $courseMoney = 0;
        //订单总金额
        $totalMoney = 0;
        //订单实付总金额 - 订单优惠
        $realTotalMoney = 0;
        //订单最小定金
        $depositMoney = 0;
        //订单追加定金
        $depositAddMoney =0;
        //未付款金额
        $depositRemainMoney = 0;
        //订单学杂费总额
        $adItMoney = 0;
        //定金/订单优惠价、价格分摊处理(按原价比例)
        $one_total_sale_price = 0;
        $tmp_discount_adver = [];
        //订单全款金额
        $full_pay_price = 0;
        //订单非全款金额
        $notfull_pay_price = 0;
        //订单非全款应付款金额
        $notfull_deal_price = 0;
        //线上课程 >> 订单总金额
        //$online_order_price = 0;
        //线上课程 >> 真实付款总金额
        //$online_real_price = 0;
        //线上课程 >> 真实付款总金额
        $online_real_price = 0;
        //课程科目基础信息
        $rs_course_subject = $this->pre_cart_data($params,$rs);
        if(isset($rs_course_subject['status']) && $rs_course_subject['status']==-1)
        {
            return MBISReturn($rs_course_subject['msg']);
        }
        //dump($rs_course_subject);exit;
        //查找课程总科目数
        $count_subject = $this->count_subject($rs);
        #全款标准价总和计算
        $tmp_discount_adver = $this->total_sale_price($rs,$cartData,$orderInfo,$rs_course_subject,$params);
        //分摊优惠价处理(全款、非全款)
        $rs_full_discount_adver = $this->adver_discount_price($rs,$cartData,$orderInfo,$rs_course_subject,$params);
        if(isset($rs_full_discount_adver['status']) && $rs_full_discount_adver['status']==-1)
        {
            return MBISReturn($rs_full_discount_adver['msg']);
        }
        $tmp_full_discount_adver = $rs_full_discount_adver['full_discount_adver'];
        $subject_discount_aver_price = $rs_full_discount_adver['subject_discount_aver_price'];
        //课程/科目定金分摊处理
        $tmp_deposit = $this->adver_deposit_price($rs,$cartData,$orderInfo,$rs_course_subject,$params);
        if(isset($tmp_deposit['status']) && $tmp_deposit['status']==-1)
        {
            return MBISReturn($tmp_deposit['msg']);
        }
        #dump($tmp_full_discount_adver);exit;
        //订单金额计算、课程课目分摊处理
        foreach($rs as $k=>$v)
        {
            //购物车信息
            $cartInfo = $cartData[$v['cartId']];
            empty($cartInfo['add_deposit_price']) && $cartInfo['add_deposit_price']=0;
            $course_id = $v['course_id'];
            if($course_id > 0 && !empty($rs_course_subject[$v['cartId']]['rs_course'][$course_id]))//含有科目列表
            {
                //$course_info = $this->get_info(['course_id'=>$course_id,'field'=>'course_id,name,offers_price,market_price,cover_img,is_shelves,des,teaching_type,course_hours,course_bn']);
                $course_info = $rs_course_subject[$v['cartId']]['rs_course'][$course_id];
                $course_name = $course_info['name'];
                if($course_info['is_shelves'] == 1)//课程已上架判断
                {
                    $v = array_merge(obj2Array($v),obj2Array($course_info));
                    $v['pmt_order_aver_price'] = '0.00';
                    $extend_data = unserialize($v['extend_data']);
                    if(!empty($extend_data['subject_ids']))
                    {
                        $subject_ids = $extend_data['subject_ids']; 
                    }
                    else
                    {
                        $subject_ids = model('common/CourseSubject')->get_subject_ids($course_id);
                    }
                    $subject_ids = model('common/subject')->filter_subject_ids($params['type_id'],$subject_ids);
                    if(empty($subject_ids))
                    {
                        return MBISReturn("科目信息不完整[subject_ids]");   
                    }
                    //$rs_subject = model('subject')->get_lists(['subject_id'=>['in',$subject_ids],'field'=>'subject_id,name,cost,sale_price,offer_price,market_price,course_hours,learn_coins,course_info,is_shelves,cover_img,teacher_id,major_id,school_id']);
                    $rs_subject = [];
                    foreach($subject_ids as $subject_id)
                    {
                        $rs_subject[] = $rs_course_subject[$v['cartId']]['rs_subject'][$subject_id];   
                    }
                    $subject_num = $count_subject[$v['course_id']];
                    $isall_subject = count($subject_ids)==$subject_num?true:false;
                    $price = 0;
                    //判断课程是否有优惠价
                    if($isall_subject && $course_info['offers_price']>0)
                    {
                        $price = $course_info['offers_price'];
                    }
                    $market_price = 0;
                    $real_price = 0;//实付金额
                    if($price>0)
                    {
                        $real_price = $price;  
                    }
                    //是否全款
                    $full_pay_val = 1;
                    //定金累加
                    $subject_deposit_price = 0;
                    $subject_nodeposit_price = 0;
                    //应付款
                    $deal_pay_price = 0;
                    //实付款
                    $real_pay_price = 0;
                    //未付款
                    $remain_pay_price = 0;
                    //最低定金金额
                    $v['deposit_price'] = model('common/course')->get_deposit_price();
                    //需要分摊定金总金额(非全款)
                    $notfull_deposit_price = $v['deposit_price']+$cartInfo['add_deposit_price']+$tmp_full_discount_adver['two_full_discount_'.$v['cartId']];
                    //课程总定金
                    $v['deposit_aver_price'] = $notfull_deposit_price;
                    //原价累加
                    $total_sale_price = 0;
                    //全款价格总额
                    $full_total_sale_price = 0;
                    //非全款价格总额
                    $notfull_total_sale_price = 0;
                    foreach($rs_subject as $kk=>$vv)
                    {
                        $total_sale_price += $vv['sale_price'];
                        //非全款处理
                        $subject_full_val = $cartInfo['subjectList'][$vv['subject_id']];
                        if($subject_full_val==0)
                        {
                           $notfull_total_sale_price += $vv['sale_price'];    
                        }
                        if($subject_full_val==1)
                        {
                            $full_total_sale_price += $vv['sale_price'];
                        }
                    }
                    //课程分摊订单优惠金额
                    $one_discount_price = 0;
                    if($full_total_sale_price>0)
                    {
                        $one_discount_price = ($full_total_sale_price/$tmp_discount_adver['one_total_sale_price'])*$orderInfo['discountMoney'];
                    }
                    if(!empty($rs_subject))
                    {
                        foreach($rs_subject as $kk=>$vv)
                        {
                            if(empty($vv['subject_id'])) continue;
                            //购买全部科目，判断是否有优惠价
                            if($isall_subject)
                            {
                                if($course_info['offers_price']>0)//有优惠价
                                {
                                    //$subject_offer_price = sprintf('%0.2f',round(($vv['sale_price']/$total_sale_price)*$price,2));//科目优惠价
                                    $subject_offer_price = getNumFormat(($vv['sale_price']/$total_sale_price)*$price);//科目优惠价
                                }
                                else
                                {
                                    $price += $vv['sale_price'];
                                    $real_price += $vv['sale_price'];
                                    $subject_offer_price = $vv['sale_price'];  
                                }
                            }//非全部科目
                            else
                            {
                                $price += $vv['sale_price'];
                                $real_price += $vv['sale_price'];
                                $subject_offer_price = $vv['sale_price'];   
                            }
                            $rs_subject[$kk]['subject_offer_price'] = $subject_offer_price;
                            $market_price += $vv['market_price'];
                            
                            $vv['is_full_pay'] = $cartInfo['subjectList'][$vv['subject_id']];
                            $vv['pmt_order_aver_price'] = '0.00';
                            if($vv['is_full_pay']==1)//全款
                            {
                                //应付款金额
                                $vv['deal_pay_price'] = getNumFormat($subject_offer_price-$subject_discount_aver_price);
                                //已付款金额
                                $vv['real_pay_price'] = $vv['deal_pay_price'];
                                //未付款金额
                                $vv['remain_pay_price'] = '0.00';
                                $subject_deposit_price += $subject_offer_price;
                                //定金分摊
                                $vv['deposit_aver_price'] = '0.00';
                                //订单全款金额
                                //$full_pay_price += $vv['real_pay_price'];
                                $full_pay_price += $subject_offer_price;
                                //科目分摊订单优惠金额
                                #dump($one_discount_price);
                                $subject_discount_aver_price = ($vv['sale_price']/$tmp_discount_adver['two_total_sale_price_'.$v['cartId']])*$one_discount_price;
                                //$subject_discount_aver_price = $tmp_discount_adver['two_total_sale_price_'.$v['cartId']];
                                $vv['discount_aver_price'] = getNumFormat($subject_discount_aver_price);
                            }
                            else//非全款
                            {
                                //应付款金额
                                $vv['deal_pay_price'] = $subject_offer_price;
                                //已付款金额
                                //$vv['real_pay_price'] = $tmp_deposit[$v['cartId']][$vv['subject_id']];
                                #dump($notfull_deposit_price);
                                $params['channelType']==1 && $vv['real_pay_price'] = $tmp_deposit[$v['cartId']]['subjectLists'][$vv['subject_id']];
                                $params['channelType']==2 && $vv['real_pay_price'] = getNumFormat(($vv['sale_price']/$notfull_total_sale_price)*$notfull_deposit_price);
                                //未付款金额
                                $vv['remain_pay_price'] = $vv['deal_pay_price']-$vv['real_pay_price'];
                                $subject_nodeposit_price += $subject_offer_price;
                                $full_pay_val = 0;   
                                //定金分摊
                                $vv['deposit_aver_price'] = $tmp_deposit[$v['cartId']]['subjectLists'][$vv['subject_id']];
                                
                                //订单非全款金额
                                $notfull_pay_price += $vv['real_pay_price'];
                                //订单非全款应付金额
                                $notfull_deal_price += $vv['deal_pay_price'];
                                //科目分摊订单优惠金额
                                $vv['discount_aver_price'] = '0.00';
                            }
                            $deal_pay_price += $vv['deal_pay_price'];
                            $real_pay_price += $vv['real_pay_price'];
                            $remain_pay_price += $vv['remain_pay_price'];
                        }
                    }
                    //科目总数
                    $v['count_subject'] = count($rs_subject);
                    //应付金额
                    $v['price'] = getNumFormat($price);
                    //实付金额
                    $v['real_price'] = getNumFormat($real_price);
                    $v['market_price'] = getNumFormat($market_price);
                    //科目总单价
                    $v['total_sale_price'] = getNumFormat($total_sale_price);
                    //应付款
                    $v['deal_pay_price'] = getNumFormat($deal_pay_price);
                    //实付款
                    $v['real_pay_price'] = getNumFormat($real_pay_price);
                    //未付款
                    $v['remain_pay_price'] = getNumFormat($remain_pay_price);
                    //优惠金额平摊
                    $v['discount_aver_price'] = getNumFormat($one_discount_price);
                    //定金金额平摊
                    $v['deposit_aver_price'] = getNumFormat($notfull_deposit_price);
                    //是否全款
                    $v['is_full_pay'] = $full_pay_val;
                    //订单课程总金额
                    $courseMoney += $v['price'];
                    if($full_pay_val==0)//非全款 
                    {
                       $min_deposit = 0;
                       $max_deposit = $subject_nodeposit_price-$v['deposit_price'];
                       $remain_deposit = $v['price']-($subject_deposit_price+$v['deposit_price'])-$cartInfo['add_deposit_price'];
                       //判断预付定金输入
                       if(!empty($cartInfo['add_deposit_price']) && ($cartInfo['add_deposit_price'] < $min_deposit || $cartInfo['add_deposit_price']>$max_deposit) )
                       {
                            return MBISReturn("课程[{$course_name}]输入预付定金参数有误[范围在{$min_deposit}-{$max_deposit}]");
                            exit;
                       }
                       //课程实付定金
                       $v['course_real_price'] = $subject_deposit_price+$v['deposit_price']+$cartInfo['add_deposit_price'];
                       $depositMoney += $subject_deposit_price+$v['deposit_price'];
                       $depositAddMoney += $cartInfo['add_deposit_price'];
                       $depositRemainMoney += $remain_deposit;
                    }
                    else//全款
                    {
                       //科目实付定金
                       $v['course_real_price'] = $v['price'];
                       //定金金额平摊
                       $v['deposit_aver_price'] = '0.00';
                    }
                    $courseAdItMoney = 0;
                    //课程学杂费
                    $lists_aditem = [];
                    if(!empty($course_id))
                    {
                        $it_ids = model('common/CourseItem')->get_it_ids($params['type_id'],$course_id,0);
                        if(!empty($it_ids))
                        {
                            $lists_aditem = model('common/AdItem')->get_lists(['it_id'=>['in',$it_ids],'field'=>'it_id,name,price']);
                            foreach($lists_aditem as $it_k=>$it_v)
                            {
                                $lists_aditem[$it_k]['price'] = $cartInfo['adItemList'][$it_v['it_id']];
                                $adItMoney += (int)$cartInfo['adItemList'][$it_v['it_id']]; 
                                $courseAdItMoney += (int)$cartInfo['adItemList'][$it_v['it_id']];
                            }
                        }
                    }
                    /**
                     * @do 线上课程处理
                     * @desc 线上价格处理
                    */
                    $lists_subject_online = [];
                    $v['online_course_price'] = '0.00';
                    if($isall_subject && !empty($course_id))
                    {
                        $lists_subject_online = model('common/CourseSubject')->get_subject_online($course_id,2);
                        $rs_online_subject = [];
                        //线上课程价格
                        $online_course_price = 0;
                        foreach($lists_subject_online as $v_online)
                        {
                            $v_online_show = $rs_course_subject[$v['cartId']]['rs_subject'][$v_online['subject_id']];
                            $v_online_show['subject_offer_price'] = $v_online['price'];
                            $v_online_show['price'] = $v_online['price'];
                            $v_online_show['online_price'] = $v_online['price'];
                            $rs_online_subject[] = $v_online_show;
                            $online_course_price += $v_online_show['online_price']; 
                            $online_real_price += $v_online_show['online_price'];   
                        }
                        //$lists_subject_online = model('common/CourseSubject')->get_subject_online($course_id,2);
                        if(!empty($rs_online_subject))
                        {
                            $v['online_course_price'] = $online_course_price;
                        }
                    }
                    
                    //订单应付/实付金额
                    //$totalMoney += $v['price']+$courseAdItMoney;
                    //$realTotalMoney += $v['course_real_price']+$courseAdItMoney;
                    $totalMoney += $v['price'];
                    $realTotalMoney += $v['course_real_price'];
                    //课程
                    $tmp_rs['courseInfo'][$i]['course'] = $v;
                    //线下科目
                    $tmp_rs['courseInfo'][$i]['subjectList'] = $rs_subject;
                    //学杂费
                    !empty($lists_aditem) && $tmp_rs['courseInfo'][$i]['adItemList'] = $lists_aditem;
                    //线上课程
                    $isall_subject && !empty($rs_online_subject) && $tmp_rs['courseInfo'][$i]['onlineSubjectList'] = $rs_online_subject;
                    $i++;
                }
            }
            else//单个科目
            {
                //$subject_info = model('subject')->get_info(['subject_id'=>$v['subject_id'],'field'=>'subject_id,subject_type_id,name,cost,sale_price,offer_price,market_price,course_hours,learn_coins,cover_img,is_shelves,course_info,is_shelves,teaching_type,teacher_id,major_id,school_id']);
                $subject_info = [];
                !empty($rs_course_subject[$v['cartId']]['rs_subject'][$v['subject_id']]) && 
                $subject_info = $rs_course_subject[$v['cartId']]['rs_subject'][$v['subject_id']];
                !empty($subject_info) && $subject_info['price'] = $subject_info['offer_price']>0?$subject_info['offer_price']:$subject_info['sale_price'];
                if(!empty($subject_info) && $subject_info['is_shelves'] == 1)//科目已上架判断
                {
                    $course_name = $subject_info['name'];
                    $full_pay_val = $cartInfo['subjectList'][$v['subject_id']];
                    $subject_info['school_name'] = model('common/major')->get_school_name($subject_info['major_id']);
                    $subject_info['major_name'] = model('common/major')->get_name($subject_info['major_id']);
                    $v = array_merge(obj2Array($v),obj2Array($subject_info));
                    $v['deposit_price'] = model('common/subject')->get_deposit_price();
                    $v['is_full_pay'] = $full_pay_val;
                    $v['total_sale_price'] = getNumFormat($v['sale_price']);
                    //应付款金额
                    $v['deal_pay_price'] = $v['price'];
                    //已付款金额
                    $v['real_pay_price'] = $v['deal_pay_price'];
                    //未付款金额
                    $v['remain_pay_price'] = '0.00';
                    $v['pmt_order_aver_price'] = '0.00';
                    //优惠金额平摊
                    $v['discount_aver_price'] = 0;
                    !empty($tmp_discount_adver['one_total_sale_price']) && $v['discount_aver_price'] = getNumFormat(($v['sale_price']/$tmp_discount_adver['one_total_sale_price'])*$orderInfo['discountMoney']);
                    //定金金额平摊
                    $v['deposit_aver_price'] = $v['deposit_price']+$cartInfo['add_deposit_price'];
                    if($full_pay_val==0)//非全款 
                    {
                       $min_deposit = 0;
                       $max_deposit = $v['price']-$v['deposit_price'];
                       $remain_deposit = $v['price']-$v['deposit_price']-$cartInfo['add_deposit_price'];
                       //判断预付定金输入
                       if(!empty($cartInfo['add_deposit_price']) && ($cartInfo['add_deposit_price'] < $min_deposit || $cartInfo['add_deposit_price']>$max_deposit) )
                       {
                            return MBISReturn("科目[{$course_name}]输入预付定金参数有误[范围在{$min_deposit}-{$max_deposit}]");   
                       }
                       //科目实付定金
                       $v['course_real_price'] = $v['deposit_price']+$cartInfo['add_deposit_price'];
                       //已付款金额
                       $v['real_pay_price'] = $v['course_real_price'];
                       //未付款金额
                       $v['remain_pay_price'] = $v['deal_pay_price']-$v['real_pay_price'];
                       $depositMoney += $v['deposit_price'];
                       $depositAddMoney += $cartInfo['add_deposit_price'];
                       $depositRemainMoney += $remain_deposit;
                       //订单非全款金额
                       $notfull_pay_price += $v['real_pay_price'];
                       //订单非全款应付金额
                       $notfull_deal_price += $v['deal_pay_price'];
                    }
                    else
                    {
                       //科目实付定金
                       $v['course_real_price'] = $subject_info['price'];
                       //订单全款金额
                       $full_pay_price += $v['deal_pay_price'];
                       //定金金额平摊
                       $v['deposit_aver_price'] = '0.00';
                    }
                    //订单实付定金累加
                    $courseMoney += $v['price'];
                    $tmp_rs['courseInfo'][$i]['course'] = $v;
                    //课目学杂费
                    $courseAdItMoney = 0;
                    if(!empty($v['subject_id']))
                    {
                        $it_ids = model('common/CourseItem')->get_it_ids($params['type_id'],0,$v['subject_id']);
                        if(!empty($it_ids))
                        {
                            $lists_aditem = model('common/AdItem')->get_lists(['it_id'=>['in',$it_ids],'field'=>'it_id,name,price']);
                            foreach($lists_aditem as $it_k=>$it_v)
                            {
                                $lists_aditem[$it_k]['price'] = $cartInfo['adItemList'][$it_v['it_id']];
                                $adItMoney += $cartInfo['adItemList'][$it_v['it_id']]; 
                                $courseAdItMoney += $cartInfo['adItemList'][$it_v['it_id']]; 
                            }
                            $tmp_rs['courseInfo'][$i]['adItemList'] = $lists_aditem;
                        }
                    }
                    //订单应付/实付金额
                    //$totalMoney += $v['price']+$courseAdItMoney;
                    //$realTotalMoney += $v['course_real_price']+$courseAdItMoney;
                    $totalMoney += $v['price'];
                    $realTotalMoney += $v['course_real_price'];
                    $i++;
                }
                else
                {
                    if(!empty($subject_info))
                       return MBISReturn("科目[{$subject_info['name']}]处于未上架状态");  
                }
            }
        }
        #dump($tmp_rs);exit;
        $this->common_info($params,$tmp_rs,$orderInfo,$paymentInfo,
    $courseMoney,$totalMoney,$realTotalMoney,$depositMoney,
    $depositAddMoney,$depositRemainMoney,$adItMoney,$type_id,$full_pay_val,
    $full_pay_price,$notfull_pay_price,$notfull_deal_price,$online_real_price);
        if($params['channelType']==1) //订单优惠金额通道
        {
            //$min_discount = 0;
            //$max_discount = $tmp_rs['orderInfo']['realPayMoney'];
            /*if($tmp_rs['orderInfo']['discountMoney']<$min_discount || $tmp_rs['orderInfo']['discountMoney']>$max_discount)
            {
                return MBISReturn("订单优惠价输入有误[范围在{$min_discount}-{$max_discount}]");  
            }*/
            if($tmp_rs['orderInfo']['realPayMoney']<0)
            {
                $tmp_rs['orderInfo']['realPayMoney'] = abs($tmp_rs['orderInfo']['realPayMoney']);
                return MBISReturn("订单优惠价输入有误[超出{$tmp_rs['orderInfo']['realPayMoney']}]");  
            }
        }
        return $tmp_rs;
    }
    public function common_info(&$params,&$tmp_rs,&$orderInfo,&$paymentInfo,
    &$courseMoney,&$totalMoney,&$realTotalMoney,&$depositMoney,
    &$depositAddMoney,&$depositRemainMoney,&$adItMoney,&$type_id,&$full_pay_val,
    &$full_pay_price,&$notfull_pay_price,&$notfull_deal_price,&$online_real_price)
    {
        //订单信息
        $tmp_rs['orderInfo'] = [
            //课程总金额
            'courseMoney' => $courseMoney,
            //应付总金额
            #'realTotalMoney' => $totalMoney-$orderInfo['discountMoney'],
            'realTotalMoney' => getNumFormat($totalMoney),
            //真实应付金额
            //'realPayMoney' => $realTotalMoney-$orderInfo['discountMoney'],
            'realPayMoney' => getNumFormat($realTotalMoney),
            //订单总金额
            'totalMoney' => $courseMoney+$adItMoney+$online_real_price,
            //订单优惠金额
            'discountMoney' => $orderInfo['discountMoney'],
            //订单促销优惠金额
            'pmt_order' => $orderInfo['pmt_order'],
            //订单最小定金
            'depositMoney' => $depositMoney,
            //订单追加定金
            'depositAddMoney' => $depositAddMoney,
            //未付款金额
            'depositRemainMoney' => $depositRemainMoney,
            //订单学杂费
            'adItMoney' => $adItMoney,
            'name' => $orderInfo['name'],
            'mobile' => $orderInfo['mobile'],
            'idcard' => $orderInfo['idcard'],
            'addr' => $orderInfo['addr'],
            'taxType' => $orderInfo['taxType'],
            'taxCompany' => $orderInfo['taxCompany'],
            //课程类型
            'type_id' => $type_id,
            //订单类型：全款/定金
            'orderType' => $full_pay_val==1?1:2,
            //订单全款金额
            'full_pay_price' => $full_pay_price,
            //订单全款金额(临时存储)
            'full_pay_price_tmp' => $full_pay_price,
            //订单非全款金额
            'notfull_pay_price' => $notfull_pay_price,
            //订单非全款金额
            'notfull_deal_price' => $notfull_deal_price,
            //线上课程真实付款金额
            'online_real_price' => $online_real_price,
            //通道类型
            'channelType' => $params['channelType'],
        ];
        //团购促销信息
        if($params['channelType']==2 && !empty($orderInfo['teamLists']))
        {
             $tmp_rs['orderInfo']['teamLists']=$orderInfo['teamLists'];  
        }
        //支付方式
        $keys_payment = array_keys($paymentInfo);
        $payType = $keys_payment[0];
        $payFrom = $paymentInfo[$payType];
        $tmp_rs['paymentInfo'] = get_payment_lists($payType,$payFrom);
        //订单促销处理
        if($params['channelType']==1) //订单优惠金额通道
        {
            $isDiscount = $tmp_rs['orderInfo']['full_pay_price']-$tmp_rs['orderInfo']['discountMoney']>0?1:0;
            $isDiscount == 0 && $tmp_rs['orderInfo']['discountMoney']=0;
            $discount_full_pay_price = $tmp_rs['orderInfo']['full_pay_price'];
            if($isDiscount==1)
                $discount_full_pay_price = $tmp_rs['orderInfo']['full_pay_price']-$tmp_rs['orderInfo']['discountMoney'];
            $tmp_rs['orderInfo']['realTotalMoney'] = getNumFormat($discount_full_pay_price+$tmp_rs['orderInfo']['notfull_deal_price']-$tmp_rs['orderInfo']['notfull_pay_price']+$tmp_rs['orderInfo']['notfull_pay_price']+$tmp_rs['orderInfo']['adItMoney']+$online_real_price);
            $tmp_rs['orderInfo']['realPayMoney'] = getNumFormat($discount_full_pay_price+$tmp_rs['orderInfo']['notfull_pay_price']+$tmp_rs['orderInfo']['adItMoney']+$online_real_price);
            
        }
        elseif($params['channelType']==2 && $tmp_rs['orderInfo']['full_pay_price']>0) //订单促销优惠通道
        {
            $rs_course = $tmp_rs;
            $params['rule_type'] = $type_id;
            $params['rule_use'] = 1;
            model('common/SalesRuleOrder')->set_order_sale_rules($params,$rs_course);
            //分摊订单促销金额到相应科目
            $this->adver_pmt_order($params,$rs_course,$online_real_price);
            $tmp_rs = $rs_course;
        }
    }
    /**
	 * 课程列表
	 */
    public function get_lists($params=[])
    {
        $where = [];
        $where['is_shelves'] = '1';
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
        if(isset($params['school_id']))
        {
            $where['school_id'] = $params['school_id'];   
        }
        if(isset($params['major_id']))
        {
            $where['major_id'] = $params['major_id'];   
        }
        if(isset($params['course_id']))
        {
            $where['course_id'] = $params['course_id'];   
        }
        if(isset($params['grade_id']))
        {
            $where['grade_id'] = $params['grade_id'];   
        }
        if(isset($params['kw']))
        {
            $where['name'] = ['like',"%{$params['kw']}%"];   
        }
        //分页信息处理
        $limit = '';
        if(isset($params['get_pager']))
        {
            if(empty($params['page_no'])) $params['page_no']=1;
            if(empty($params['page_size'])) $params['page_size']=12;
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
            if(isset($v['cover_img']))
            {
                $rs[$k]['cover_img'] = ITSPicUrl($v['cover_img']);
            }
            if(isset($v['teaching_type']))
            {
                $rs[$k]['teaching_type'] = $rs[$k]['teaching_type_format'] = ITSSelItemName('course','teaching_type',$v['teaching_type']);
            }
            //添加通道
            $rs[$k]['channelLists'] = get_channel_lists($v);
        }
        //分页信息处理
        if(isset($params['get_pager']))
        {
            $rs_p['lists'] = $rs;
            $rs_p['data_total'] = $data_total;
            $rs_p['page_total'] = $page_total;
            $rs_p['page_cur'] = $params['page_no'];;
            $rs = $rs_p;   
        }
        return $rs;
    }
    //课程详情
    public function get_info($params=[]){
        $field = '';
        if(isset($params['field']))
        {
           $field = $params['field'];
        }
        $where = [];
        if(isset($params['course_id']))
        {
           $where['course_id'] = $params['course_id'];    
        }
        $rs = $this->where($where)->field($field)->find();
        if(isset($params['field'])&&strpos($params['field'],',')===FALSE) return $rs[$field];
        if(isset($rs['details']))
        {
            $rs['details'] = htmlspecialchars_decode($rs['details']);
        }
        if(isset($rs['cover_img']))
        {
            $rs['cover_img'] = ITSPicUrl($rs['cover_img']);
        }
        if(isset($rs['teaching_type']))
        {
            $rs['teaching_type'] = ITSSelItemName('course','teaching_type',$rs['teaching_type']);
        }
        /*if(isset($rs['course_id']))
        {
            $course_price = $this->get_course_price(0,$rs);
            $rs['price'] = $course_price['price'];
            $rs['market_price'] = $course_price['market_price'];
        }*/
        //添加通道
        $rs['channelLists'] = get_channel_lists($rs);
        return $rs;
	}
    //课程名称
    public function get_name($id=0){
        return $this->where('course_id',$id)->value('name');
	}
    //课程价格
    public function get_course_price($course_id=0,$tmp_data=[])
    {
        if(!empty($tmp_data))
        {
            $rs = $tmp_data;
        }
        else
        {
            $rs = $this->where('course_id',$course_id)->field('course_id,offers_price,market_price')->find();
        }
        #dump($rs);
        //课程优惠价 >> 优先
        $price = $rs['offers_price'];
        //科目组合价格计算
        $subject_ids = model('common/CourseSubject')->get_subject_ids($rs['course_id']);
        if((float)$price<=0)
        {
            $price = 0;
        }
        $market_price = $rs['market_price'];
        if((float)$market_price<=0)
        {
            $market_price = 0;
        }
        foreach($subject_ids as $subject_id)
        {
            $price_subject = model('common/subject')->get_subject_price($subject_id);
            if((float)$price<=0)
            {
                $price += (float)$price_subject['price'];
            }
            if((float)$rs['market_price']<=0)
            {
                $market_price += (float)$price_subject['market_price'];
            }
        }
        return ['price'=>sprintf("%.2f",$price),'market_price'=>sprintf("%.2f",$market_price)];
    }
    //没有促销优惠价
    public function get_course_price_origi($course_id=0,$tmp_data=[])
    {
        if(!empty($tmp_data))
        {
            $rs = $tmp_data;
        }
        else
        {
            $rs = $this->where('course_id',$course_id)->field('course_id,offers_price,market_price')->find();
        }
        //课程优惠价 >> 优先
        $price = $rs['offers_price'];
        //科目组合价格计算
        $subject_ids = model('common/CourseSubject')->get_subject_ids($rs['course_id']);
        if((float)$price<=0)
        {
            $price = 0;
        }
        $market_price = $rs['market_price'];
        if((float)$market_price<=0)
        {
            $market_price = 0;
        }
        foreach($subject_ids as $subject_id)
        {
            $price_subject = model('common/subject')->where('subject_id',$subject_id)->field('sale_price,market_price')->find();
            if((float)$price<=0)
            {
                $price += (float)$price_subject['sale_price'];
            }
            if((float)$rs['market_price']<=0)
            {
                $market_price += (float)$price_subject['market_price'];
            }
        }
        return ['price'=>sprintf("%.2f",$price),'market_price'=>sprintf("%.2f",$market_price)];
    }
    //传入subject_ids计算课程价格
    public function get_course_price_by_sids($course_id=0,$ipt_subject_ids=[],$tmp_data=[])
    {
        if(!empty($tmp_data))
        {
            $rs = $tmp_data;
        }
        else
        {
            $rs = $this->where('course_id',$course_id)->field('course_id,offers_price,market_price')->find();
        }
        $subject_ids = model('common/CourseSubject')->get_subject_ids($rs['course_id'],1);
        $is_not_all = array_diff($subject_ids,$ipt_subject_ids);
        //课程优惠价 >> 优先
        $price = 0;
        $market_price = 0;
        if(!empty($ipt_subject_ids[0]) && !$is_not_all)
        {
            $price = $rs['offers_price'];
            $market_price = $rs['market_price'];
            if($market_price<=0) $market_price=$price;
        }
        if(!empty($ipt_subject_ids[0]) && $is_not_all)
        {
            $subject_ids = $ipt_subject_ids;
            foreach($subject_ids as $subject_id)
            {
                $price_subject = model('common/subject')->where('subject_id',$subject_id)->field('sale_price,market_price')->find();
                $price += (float)$price_subject['sale_price'];
                if((float)$rs['market_price']<=0)
                {
                    $market_price += (float)$price_subject['market_price'];
                }
            }
            
            if($market_price<=0) $market_price=$price;
        }
        
        return ['price'=>sprintf("%.2f",$price),'market_price'=>sprintf("%.2f",$market_price)];
    }
    //过滤不存在的课程ID
    public function filter_course_ids($type_id,$course_ids)
    {
        $tmp_ids =[];
        $rs = $this->where(['type_id'=>$type_id,'course_id'=>['in',$course_ids]])->field('course_id')->select();
        foreach($rs as $k=>$v)
        {
           $tmp_ids[] = $v['course_id'];    
        }
        return $tmp_ids;
    }
    //最少预付定金
    public function get_deposit_price($deposit_price=0)
    {
        $arr_deposit_price = [
            1 => 100,
            2 => 500,
        ];
        $channelType = 2;
        (isset($_POST['channelType']) && !empty($_POST['channelType'])) 
        && in_array($_POST['channelType'],[1,2]) 
        && $channelType = $_POST['channelType'];
        $price = $arr_deposit_price[$channelType];
        //if($deposit_price>0) $price=$deposit_price; 
        return sprintf('%0.2f',$price);  
    }
    //格式化购物车课程科目数据，存到内存
    private function pre_cart_data($params=[],$rs=[])
    {
        $tmp_rs = $rs_course = $rs_subject = [];
        $online_subject_ids = $course_subject_ids = $subject_subject_ids = $course_ids = $subject_ids = [];
        foreach($rs as $v):
          $v['course_id']>0 && $course_ids[] = (int)$v['course_id'];
          $v['subject_id']>0 && $subject_ids[] =(int)$v['subject_id'];
          $v['subject_id']>0 && $subject_subject_ids[$v['subject_id']][] = (int)$v['subject_id'];
          if($v['course_id']>0 && !empty($v['extend_data'])):
            $extend_data = unserialize($v['extend_data']);
            foreach($extend_data['subject_ids'] as $subject_id):
               $subject_ids[] =(int)$subject_id;
               $v['course_id']>0 && $course_subject_ids[$v['course_id']][] = (int)$subject_id;
            endforeach; 
          endif;
          //线上课程
          $params['type_id']==2 && $v['course_id']>0 && $subject_ids = array_merge($subject_ids,model('common/CourseSubject')->get_subject_ids($v['course_id'],2));
        endforeach;
        //dump(array_values(array_unique($subject_ids)));exit;
        //验证请求参数
        foreach($params['cartData'] as $v)
        {
            if(!empty($v['course_id'])):
                if(!in_array($v['course_id'],$course_ids)):
                    return MBISReturn("请求数据有误[50011]");  
                endif;
            else:
                if(empty($v['subject_id'])):
                    return MBISReturn("请求数据有误[50012]");  
                endif; 
            endif;
            
            if(!empty($v['subject_id'])):
                if(!in_array($v['subject_id'],$subject_ids)):
                    return MBISReturn("请求数据有误[50021]");  
                endif;
            else:
                if(empty($v['course_id'])):
                    return MBISReturn("请求数据有误[50022]");  
                endif;  
            endif;
            
            /*if(!empty($v['subjectList'])):
                $subjectList = $v['subjectList'];
                foreach($subjectList as $subject_id=>$is_full_val):
                   if(!empty($v['course_id']) && !in_array($subject_id,$course_subject_ids[$v['course_id']])):
                        return MBISReturn("请求数据有误[5003]");  
                   endif; 
                   if(!empty($v['subject_id']) && !in_array($subject_id,$subject_subject_ids[$v['subject_id']])):
                        return MBISReturn("请求数据有误[5004]");  
                   endif; 
                endforeach; 
            else:
                return MBISReturn("请求数据有误[5005]"); 
            endif;*/
        }//END
        !empty($course_ids) && $rs_course = model('common/course')->get_lists(['course_id'=>['in',array_values(array_unique($course_ids))],'field'=>'course_id,name,offers_price,market_price,cover_img,is_shelves,des,teaching_type,course_hours,course_bn']);
        !empty($subject_ids) && $rs_subject = model('common/subject')->get_lists(['subject_id'=>['in',array_values(array_unique($subject_ids))],'field'=>'subject_id,subject_type_id,name,cost,sale_price,offer_price,market_price,course_hours,learn_coins,cover_img,is_shelves,course_info,is_shelves,teaching_type,teacher_id,major_id,school_id,subject_no']);
        //!empty($online_subject_ids) && $rs_online_subject = model('common/subject')->get_lists(['subject_id'=>['in',$subject_ids],'field'=>'subject_id,subject_type_id,name,cost,sale_price,offer_price,market_price,course_hours,learn_coins,cover_img,is_shelves,course_info,is_shelves,teaching_type,teacher_id,major_id,school_id,subject_no']);
        if(empty($rs_course) && empty($rs_subject))
        {
           return MBISReturn("请求数据有误[5006]");    
        }
        foreach($rs as $v):
           foreach($rs_course as $v_c):
               $tmp_rs[$v['cartId']]['rs_course'][$v_c['course_id']] = $v_c;
           endforeach;
           foreach($rs_subject as $v_s):
               $tmp_rs[$v['cartId']]['rs_subject'][$v_s['subject_id']] = $v_s;
           endforeach; 
        endforeach;
        return $tmp_rs;  
    }
    //分摊优惠价处理(全款、非全款)
    public function adver_discount_price($rs,$cartData,$orderInfo,$rs_course_subject,$params=[])
    {
        $tmp_discount_adver = $this->total_sale_price($rs,$cartData,$orderInfo,$rs_course_subject,$params);
        //dump($tmp_discount_adver);
        $subject_discount_aver_price = 0;
        $tmp_full_discount_adver = [];
        foreach($rs as $k=>$v)
        {
            //购物车信息
            $cartInfo = $cartData[$v['cartId']];
            empty($cartInfo['add_deposit_price']) && $cartInfo['add_deposit_price']=0;
            $course_id = $v['course_id'];
            if($course_id > 0 && !empty($rs_course_subject[$v['cartId']]['rs_course'][$course_id]))//含有科目列表
            {
                //$course_info = $this->get_info(['course_id'=>$course_id,'field'=>'course_id,name,offers_price,market_price,cover_img,is_shelves,des,teaching_type']); 
                $course_info = $rs_course_subject[$v['cartId']]['rs_course'][$course_id];
                $course_name = $course_info['name'];
                if($course_info['is_shelves'] == 1)//课程已上架判断
                {
                    $v = array_merge(obj2Array($v),obj2Array($course_info));
                    $extend_data = unserialize($v['extend_data']);
                    if(!empty($extend_data['subject_ids']))
                    {
                        $subject_ids = $extend_data['subject_ids'];   
                    }
                    else
                    {
                        $subject_ids = model('common/CourseSubject')->get_subject_ids($course_id);
                    }
                    $subject_ids = model('common/subject')->filter_subject_ids($params['type_id'],$subject_ids);
                    if(empty($subject_ids))
                    {
                        return MBISReturn("科目信息不完整[subject_ids]");   
                    }
                    //$rs_subject = model('subject')->get_lists(['subject_id'=>['in',$subject_ids],'field'=>'subject_id,sale_price,market_price,offer_price,is_shelves']);
                    $rs_subject = [];
                    foreach($subject_ids as $subject_id)
                    {
                        $rs_subject[] = $rs_course_subject[$v['cartId']]['rs_subject'][$subject_id];
                    }
                    //原价累加
                    $total_sale_price = 0;
                    foreach($rs_subject as $kk=>$vv)
                    {
                        $subject_full_val = $cartInfo['subjectList'][$vv['subject_id']];
                        if($subject_full_val==1)
                        {
                            $total_sale_price += $vv['sale_price'];
                        }
                    }
                    //课程分摊订单优惠金额
                    $one_discount_price = 0;
                    if($total_sale_price>0)
                    {
                        $one_discount_price = ($total_sale_price/$tmp_discount_adver['one_total_sale_price'])*$orderInfo['discountMoney'];
                    }
                    //全款分摊优惠总额
                    $total_two_full_discount = 0;
                    foreach($rs_subject as $kk=>$vv)
                    {
                        //全款处理
                        $subject_full_val = $cartInfo['subjectList'][$vv['subject_id']];
                        if($subject_full_val==1)
                        {
                           $subject_discount_aver_price = ($vv['sale_price']/$tmp_discount_adver['two_total_sale_price_'.$v['cartId']])*$one_discount_price;
                           $total_two_full_discount += $subject_discount_aver_price;    
                        }       
                    }
                    $tmp_full_discount_adver['two_full_discount_'.$v['cartId']] = $total_two_full_discount;
                }
            }
        }//END
        return ['full_discount_adver'=>$tmp_full_discount_adver,'subject_discount_aver_price'=>$subject_discount_aver_price];
    }
    
    //全款标准价总和计算
    public function total_sale_price($rs,$cartData,$orderInfo,$rs_course_subject,$params=[])
    {
        //订单优惠价分摊处理(按标准价比例)
        $one_total_sale_price = 0;
        $tmp_discount_adver = [];
        foreach($rs as $k=>$v)
        {
            //购物车信息
            $cartInfo = $cartData[$v['cartId']];
            empty($cartInfo['add_deposit_price']) && $cartInfo['add_deposit_price']=0;
            $course_id = $v['course_id'];
            if($course_id > 0 && !empty($rs_course_subject[$v['cartId']]['rs_course'][$course_id]))//含有科目列表
            {
                //$course_info = $this->get_info(['course_id'=>$course_id,'field'=>'course_id,name,offers_price,market_price,cover_img,is_shelves,des,teaching_type']);
                $course_info = $rs_course_subject[$v['cartId']]['rs_course'][$course_id];
                $course_name = $course_info['name'];
                if($course_info['is_shelves'] == 1)//课程已上架判断
                {
                    $v = array_merge(obj2Array($v),obj2Array($course_info));
                    $extend_data = unserialize($v['extend_data']);
                    if(!empty($extend_data['subject_ids']))
                    {
                        $subject_ids = $extend_data['subject_ids'];   
                    }
                    else
                    {
                        $subject_ids = model('common/CourseSubject')->get_subject_ids($course_id);
                    }
                    $subject_ids = model('common/subject')->filter_subject_ids($params['type_id'],$subject_ids);
                    if(empty($subject_ids))
                    {
                        return MBISReturn("科目信息不完整[subject_ids]");   
                    }
                    //$rs_subject = model('subject')->get_lists(['subject_id'=>['in',$subject_ids],'field'=>'subject_id,sale_price,market_price,offer_price,is_shelves']);
                    $rs_subject = [];
                    foreach($subject_ids as $subject_id)
                    {
                        $rs_subject[] = $rs_course_subject[$v['cartId']]['rs_subject'][$subject_id];
                    }
                    $two_total_sale_price = 0;
                    foreach($rs_subject as $kk=>$vv)
                    {
 
                        $subject_full_val = $cartInfo['subjectList'][$vv['subject_id']];
                        if($subject_full_val==1)
                        {
                            $one_total_sale_price += $vv['sale_price'];
                            $two_total_sale_price += $vv['sale_price'];
                        }
                    }
                    $tmp_discount_adver['two_total_sale_price_'.$v['cartId']] = $two_total_sale_price;
                }
            }
            else
            {
                //$subject_info = model('subject')->get_info(['subject_id'=>$v['subject_id'],'field'=>'subject_id,sale_price,market_price,offer_price,is_shelves']);
                $subject_info = [];
                !empty($rs_course_subject[$v['cartId']]['rs_subject'][$v['subject_id']]) && 
                $subject_info = $rs_course_subject[$v['cartId']]['rs_subject'][$v['subject_id']];  
                if(!empty($subject_info) && $subject_info['is_shelves'] == 1)//科目已上架判断
                {
                    $subject_full_val = $cartInfo['subjectList'][$v['subject_id']];
                    if($subject_full_val==1)
                    {
                        $one_total_sale_price += $subject_info['sale_price'];
                    }
                }
            }
        }
        $tmp_discount_adver['one_total_sale_price'] = $one_total_sale_price;//END
        return $tmp_discount_adver;   
    }
    //课程/科目定金分摊处理
    public function adver_deposit_price($rs,$cartData,$orderInfo,$rs_course_subject,$params=[])
    {
        $tmp_deposit = [];
        foreach($rs as $k=>$v)
        {
            //购物车信息
            $cartInfo = $cartData[$v['cartId']];
            empty($cartInfo['add_deposit_price']) && $cartInfo['add_deposit_price']=0;
            $course_id = $v['course_id'];
            if($course_id > 0 && !empty($rs_course_subject[$v['cartId']]['rs_course'][$course_id]))//含有科目列表
            {
                //课程总定金
                $course_deposit_price = model('common/course')->get_deposit_price()+$cartInfo['add_deposit_price'];
                $tmp_deposit[$v['cartId']]['course_deposit_price'] = $course_deposit_price;
                //$course_info = $this->get_info(['course_id'=>$course_id,'field'=>'course_id,name,offers_price,market_price,cover_img,is_shelves,des,teaching_type']);
                $course_info = $rs_course_subject[$v['cartId']]['rs_course'][$course_id];
                $course_name = $course_info['name'];
                if($course_info['is_shelves'] == 1)//课程已上架判断
                {
                    $v = array_merge(obj2Array($v),obj2Array($course_info));
                    $extend_data = unserialize($v['extend_data']);
                    if(!empty($extend_data['subject_ids']))
                    {
                        $subject_ids = $extend_data['subject_ids'];   
                    }
                    else
                    {
                        $subject_ids = model('common/CourseSubject')->get_subject_ids($course_id);
                    }
                    $subject_ids = model('common/subject')->filter_subject_ids($params['type_id'],$subject_ids);
                    if(empty($subject_ids))
                    {
                        return MBISReturn("科目信息不完整[subject_ids]");   
                    }
                    //$rs_subject = model('subject')->get_lists(['subject_id'=>['in',$subject_ids],'field'=>'subject_id,sale_price,market_price,offer_price,is_shelves']);
                    $rs_subject = [];
                    foreach($subject_ids as $subject_id)
                    {
                        $rs_subject[] = $rs_course_subject[$v['cartId']]['rs_subject'][$subject_id];   
                    }
                    //原价累加
                    $deposit_total_sale_price = 0;
                    foreach($rs_subject as $kk=>$vv)
                    {
                        //全款处理
                        $subject_full_val = $cartInfo['subjectList'][$vv['subject_id']];
                        if($subject_full_val==0)
                        {
                            $deposit_total_sale_price += $vv['sale_price'];
                        }
                    }
                    //非全款分摊定金总额
                    $total_two_full_discount = 0;
                    foreach($rs_subject as $kk=>$vv)
                    {
                        //非全款处理
                        $subject_full_val = $cartInfo['subjectList'][$vv['subject_id']];
                        if($subject_full_val==0)
                        {
                           $subject_deposit_aver_price = ($vv['sale_price']/$deposit_total_sale_price)*$course_deposit_price;
                           $tmp_deposit[$v['cartId']]['subjectLists'][$vv['subject_id']] = getNumFormat($subject_deposit_aver_price);
                        }       
                    }
                }
            }
        }
        return $tmp_deposit;
    }
    //查找课程总科目数
    public function count_subject($rs)
    {
        $count_subject = [];
        foreach($rs as $k=>$v)
        {
            if($v['course_id']>0)
            {
                $count_subject[$v['course_id']] = model('common/CourseSubject')->where(['course_id'=>$v['course_id'],'obj_type'=>1])->count($v['course_id']);
            }
        }
        return $count_subject;
    }
    //分摊订单促销金额到相应科目
    public function adver_pmt_order($params=[],&$rs_course=[],$online_real_price=0)
    {
        $type_id = min(max(@$params['jump_type'],1),2);
        if($rs_course['orderInfo']['pmt_order']>0)
        {
            $full_arr = [];
            $full_total_price = 0;
            if($type_id==1 || $type_id==2)
            {
                foreach($rs_course['courseInfo'] as $k=>$v)
                {
                    $course = $v['course'];
                    if($course['course_id']>0)
                    {
                        if(!empty($v['subjectList'])):
                            foreach($v['subjectList'] as $kk=>$vv)
                            {
                                //全款处理
                                $subject_full_val = $vv['is_full_pay'];
                                if($subject_full_val==1)
                                {
                                   $full_total_price += $vv['sale_price'];
                                   $full_arr[] = ['id'=>'two_'.$course['cartId'].'_'.$vv['subject_id'],'price'=>$vv['sale_price']];    
                                }
                            }
                        endif;
                        //学历类处理
                        $type_id==1 && $course['is_full_pay']=='1' && $full_total_price += $course['stu_fee'];
                        $type_id==1 && $course['is_full_pay']=='1' && $full_arr[] = ['id'=>'one_'.$course['cartId'].'_'.$course['course_id'],'price'=>$course['stu_fee']];
                    }
                    else
                    {
                        $subject_full_val = $course['is_full_pay'];
                        if($subject_full_val==1)
                        {
                           $full_total_price += $course['sale_price'];
                           $full_arr[] = ['id'=>'one_'.$course['cartId'].'_'.$course['subject_id'],'price'=>$course['sale_price']];
                        }  
                    }
                }
                //分摊金额处理
                //$full_arr = array('id'=>'','price'=>'')
                //$full_total_price = 按比例总金额
                //$rs_course['orderInfo']['pmt_order'] = 需要分摊金额
                $tmp_pmt_order = get_aver_num($full_arr,$full_total_price,$rs_course['orderInfo']['pmt_order']);
                //分摊订单促销金额
                foreach($rs_course['courseInfo'] as $k=>$v)
                {
                    $course = $v['course'];
                    if($course['course_id']>0)
                    {
                        $one_full_total_price = 0;
                        if(!empty($v['subjectList'])):
                            foreach($v['subjectList'] as $kk=>$vv)
                            {
                                //全款处理
                                $two_subject_full_val = $vv['is_full_pay'];
                                if($two_subject_full_val==1)
                                {
                                   $vv['pmt_order_aver_price'] = $tmp_pmt_order['two_'.$course['cartId'].'_'.$vv['subject_id']];
                                   $rs_course['courseInfo'][$k]['subjectList'][$kk]['pmt_order_aver_price'] = $vv['pmt_order_aver_price'];
                                   $one_full_total_price += $vv['pmt_order_aver_price'];
                                   $rs_course['courseInfo'][$k]['subjectList'][$kk]['deal_pay_price'] = getNumFormat($vv['deal_pay_price'] - $vv['pmt_order_aver_price']);
                                   $rs_course['courseInfo'][$k]['subjectList'][$kk]['real_pay_price'] = getNumFormat($vv['real_pay_price'] - $vv['pmt_order_aver_price']);
                                   $rs_course['courseInfo'][$k]['subjectList'][$kk]['remain_pay_price'] = $rs_course['courseInfo'][$k]['subjectList'][$kk]['deal_pay_price'] - $rs_course['courseInfo'][$k]['subjectList'][$kk]['real_pay_price'];
                                }
                            }
                        endif;
                        //学历类处理
                        $type_id==1 && $course['is_full_pay']=='1' && $one_full_total_price = $tmp_pmt_order['one_'.$course['cartId'].'_'.$course['course_id']];//END
                        $rs_course['courseInfo'][$k]['course']['pmt_order_aver_price'] = getNumFormat($one_full_total_price);
                        $rs_course['courseInfo'][$k]['course']['deal_pay_price'] = getNumFormat($rs_course['courseInfo'][$k]['course']['deal_pay_price'] - $rs_course['courseInfo'][$k]['course']['pmt_order_aver_price']);
                        $rs_course['courseInfo'][$k]['course']['real_pay_price'] = getNumFormat($rs_course['courseInfo'][$k]['course']['real_pay_price'] - $rs_course['courseInfo'][$k]['course']['pmt_order_aver_price']);
                        $rs_course['courseInfo'][$k]['course']['remain_pay_price'] = $rs_course['courseInfo'][$k]['course']['deal_pay_price'] - $rs_course['courseInfo'][$k]['course']['real_pay_price'];
                    }
                    else
                    {
                        $one_subject_full_val = $course['is_full_pay'];
                        if($one_subject_full_val==1)
                        {
                           #$rs_course['courseInfo'][$k]['course']['pmt_order_aver_price'] = $tmp_pmt_order['one_'.$course['cartId'].'_'.$course['subject_id']];
                           $rs_course['courseInfo'][$k]['course']['pmt_order_aver_price'] = getNumFormat($tmp_pmt_order['one_'.$course['cartId'].'_'.$course['subject_id']]);
                           $rs_course['courseInfo'][$k]['course']['deal_pay_price'] = getNumFormat($rs_course['courseInfo'][$k]['course']['deal_pay_price'] - $rs_course['courseInfo'][$k]['course']['pmt_order_aver_price']);
                           $rs_course['courseInfo'][$k]['course']['real_pay_price'] = getNumFormat($rs_course['courseInfo'][$k]['course']['real_pay_price'] - $rs_course['courseInfo'][$k]['course']['pmt_order_aver_price']);
                           $rs_course['courseInfo'][$k]['course']['remain_pay_price'] = $rs_course['courseInfo'][$k]['course']['deal_pay_price'] - $rs_course['courseInfo'][$k]['course']['real_pay_price'];
                        }  
                    }
                }//END
            }
            #echo '<pre>';var_export($rs_course);exit;
        }
        $rs_course['orderInfo']['realTotalMoney'] = getNumFormat($rs_course['orderInfo']['full_pay_price']+$rs_course['orderInfo']['notfull_deal_price']+$rs_course['orderInfo']['adItMoney']+$online_real_price);
        $rs_course['orderInfo']['realPayMoney'] = getNumFormat($rs_course['orderInfo']['full_pay_price']+$rs_course['orderInfo']['notfull_pay_price']+$rs_course['orderInfo']['adItMoney']+$online_real_price);
        $rs_course['orderInfo']['pmt_order'] = getNumFormat($rs_course['orderInfo']['pmt_order']);
    }
    
    /* 课程数据处理 */
    public function getInfoData($course_id=0){
        $return = $this->get(['course_id'=>$course_id]);
        return $return;   
    }
}
