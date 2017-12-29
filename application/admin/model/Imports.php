<?php 
namespace application\admin\model;
use think\Db;
use think\Loader;
use think\Request;
/**
* 导入类
 */
class Imports{
	/**
	 * 上传商品数据
	 */
	public function importGoods($data){
		Loader::import('phpexcel.PHPExcel.IOFactory');
		$objReader = \PHPExcel_IOFactory::load(MBISRootPath().json_decode($data)->route.json_decode($data)->name);
		$objReader->setActiveSheetIndex(0);
		$sheet = $objReader->getActiveSheet();
		$rows = $sheet->getHighestRow();
		$cells = $sheet->getHighestColumn();
		//数据集合
        $readData = [];
        $shopId = (int)session('MBIS_USER.shopId');
        $importNum = 0;
        $goodsCatMap = []; //记录最后一级商品分类
        $goodsCatPathMap = [];//记录商品分类路径
        $shopCatMap = [];//记录店铺分类
        $goodsCat1Map = [];//记录最后一级商品分类对应的一级分类
        $tmpGoodsCatId = 0;
        $goodsCatBrandMap = [];//商品分类和品牌的对应关系
        //生成订单
		Db::startTrans();
		try{
	        //循环读取每个单元格的数据
	        for ($row = 3; $row <= $rows; $row++){//行数是以第3行开始
	        	$tmpGoodsCatId = 0;
	        	$goods = [];
	            $goods['shopId'] = $shopId;
	            $goods['goodsName'] = trim($sheet->getCell("A".$row)->getValue());
	            if($goods['goodsName']=='')break;//如果某一行第一列为空则停止导入
	            $goods['goodsSn'] = trim($sheet->getCell("B".$row)->getValue());
	            $goods['productNo'] = trim($sheet->getCell("C".$row)->getValue());
	            $goods['marketPrice'] = trim($sheet->getCell("D".$row)->getValue());
	            $goods['shopPrice'] = trim($sheet->getCell("E".$row)->getValue());
	            $goods['goodsStock'] = trim($sheet->getCell("F".$row)->getValue());
	            $goods['warnStock'] = trim($sheet->getCell("G".$row)->getValue());
	            $goods['goodsUnit'] = trim($sheet->getCell("H".$row)->getValue());
	            $goods['goodsSeoKeywords'] = trim($sheet->getCell("I".$row)->getValue());
	            $goods['goodsTips'] = trim($sheet->getCell("J".$row)->getValue());
	            $goods['isRecom'] = (trim($sheet->getCell("K".$row)->getValue())!='')?1:0;
	            $goods['isBest'] = (trim($sheet->getCell("L".$row)->getValue())!='')?1:0;
	            $goods['isNew'] = (trim($sheet->getCell("M".$row)->getValue())!='')?1:0;
	            $goods['isHot'] = (trim($sheet->getCell("N".$row)->getValue())!='')?1:0;
	            //查询商城分类
	            $goodsCat = trim($sheet->getCell("O".$row)->getValue());
	            if(!empty($goodsCat)){
	            	//先判断集合是否存在，不存在的时候才查数据库
	            	if(isset($goodsCatMap[$goodsCat])){
	            		$goods['goodsCatId'] = $goodsCatMap[$goodsCat];
		            	$goods['goodsCatIdPath'] = $goodsCatPathMap[$goodsCat];
		            	$tmpGoodsCatId = $goodsCat1Map[$goodsCat];
	            	}else{
		            	$goodsCatId = Db::name('goods_cats')->where(['catName'=>$goodsCat,'dataFlag'=>1])->field('catId')->find();
		            	if(!empty($goodsCatId['catId'])){
		            		$goodsCats = model('GoodsCats')->getParentIs($goodsCatId['catId']);
		            		$goods['goodsCatId'] = $goodsCatId['catId'];
		            		$goods['goodsCatIdPath'] = implode('_',$goodsCats)."_";
		            		//放入集合
		            		$goodsCatMap[$goodsCat] = $goodsCatId['catId'];
		            		$goodsCatPathMap[$goodsCat] = implode('_',$goodsCats)."_";
		            		$goodsCat1Map[$goodsCat] = $goodsCats[0];
		            		$tmpGoodsCatId = $goodsCats[0];
		            	}
	            	}
	            }
	            //查询店铺分类
	            $shopGoodsCat = trim($sheet->getCell("P".$row)->getValue());
	            if(!empty($shopGoodsCat)){
	            	//先判断集合是否存在，不存在的时候才查数据库
	            	if(isset($shopCatMap[$shopGoodsCat])){
	            		$goods['shopCatId1'] = $shopCatMap[$shopGoodsCat]['s1'];
		            	$goods['shopCatId2'] = $shopCatMap[$shopGoodsCat]['s2'];
	            	}else{
		            	$shopCat= Db::name("shop_cats")->alias('sc1')
		            	->join('__SHOP_CATS__ sc2','sc2.parentId=sc1.catId','left')
		            	->field('sc1.catId catId1,sc2.catId catId2,sc2.catName')
		            	->where(['sc1.shopId'=> $shopId,'sc1.dataFlag'=>1,'sc2.catName'=>$shopGoodsCat])
		            	->find();
		            	if(!empty($shopCat)){
		            		$goods['shopCatId1'] = $shopCat['catId1'];
		            		$goods['shopCatId2'] = $shopCat['catId2'];
		            		//放入集合
		            		$shopCatMap[$shopGoodsCat] = [];
		            		$shopCatMap[$shopGoodsCat]['s1'] = $goods['shopCatId1'];
		            		$shopCatMap[$shopGoodsCat]['s2'] = $goods['shopCatId2'];
		            	}
	            	}
	            }
	            //查询品牌
	            $brand = trim($sheet->getCell("Q".$row)->getValue());
	            if(!empty($brand)){
	            	if(isset($goodsCatBrandMap[$brand])){
		            	$goods['brandId'] = $goodsCatBrandMap[$brand];
	            	}else{
	            	    $brands = Db::name('brands')->alias('a')->join('__CAT_BRANDS__ cb','a.brandId=cb.brandId','inner')
		            	            ->where(['catId'=>$tmpGoodsCatId,'brandName'=>$brand,'dataFlag'=>1])->field('a.brandId')->find();
		            	if(!empty($brands)){
		            		$goods['brandId'] = $brands['brandId'];
		            		$goodsCatBrandMap[$brand] = $brands['brandId'];
		            	}
	            	}
	            }
	            $goods['goodsDesc'] = trim($sheet->getCell("R".$row)->getValue());
	            $goods['isSale'] = 0;
	            $goods['goodsStatus'] = (MBISConf("CONF.isGoodsVerify")==1)?0:1;
	            $goods['dataFlag'] = 1;
	            $goods['saleTime'] = date('Y-m-d H:i:s');
	            $goods['createTime'] = date('Y-m-d H:i:s');
	            $readData[] = $goods;
	            $importNum++;
	        }
            if(count($readData)>0){
            	$list = model('Goods')->saveAll($readData);
            	//建立商品评分记录
            	$goodsScores = [];
            	foreach ($list as $key =>$v){
					$gs = [];
					$gs['goodsId'] = $v['goodsId'];
					$gs['shopId'] = $shopId;
					$goodsScores[] = $gs;
            	}
            	if(count($goodsScores)>0)Db::name('goods_scores')->insertAll($goodsScores);
            }
            Db::commit();
            return json_encode(['status'=>1,'importNum'=>$importNum]);
		}catch (\Exception $e) {
			print_r($e);
            Db::rollback();
            return json_encode(MBISReturn('导入商品失败',-1));
        }
	}
    
    /**
	 * 导入学员数据
	 */
	public function importUsers($filename = ''){
        set_time_limit(0);
        ini_set("memory_limit","512M");
		Loader::import('phpexcel.PHPExcel.IOFactory');
        //$filename = './data/u-basic2.xlsx';
        $num = isset($_GET['num'])&&!empty($_GET['num'])?$_GET['num']:1;
        //$filename = "/xlsx/{$num}.xlsx";
        //MBISRootPath().
		$objReader = \PHPExcel_IOFactory::load($filename);
		$objReader->setActiveSheetIndex(0);
		$sheet = $objReader->getActiveSheet();
		$rows = $sheet->getHighestRow();
		$cells = $sheet->getHighestColumn();
        //dump($rows);exit;
        $max_rows = 200;
        if($rows-1>$max_rows){exit("每次导入数据最多{$max_rows}条<a href=\"javascript:history.back()\">返回</a>");}
        $readData = [];
        $importNum = 0;
        //性别
        $sexArr = [
            '男' => 1,
            '女' => 2,
            '无' => 0,
            '' => 0,
        ];
        //学员类型：1为技能、2为学历、3为技能学历
        $studentTypeArr = [
            '技能' => 1,
            '学历' => 2,
            '技能学历' => 3,
            '' => 1,
        ];
        //学习状态：1为在读、2为毕业、3为过期、4为弃学、5为休学、6为退学
        $studyStatusArr = [
            '在读' => 1,
            '毕业' => 2,
            '过期' => 3,
            '弃学' => 4,
            '休学' => 5,
            '退学' => 6,
            //'未确认' => 7,
            //'申请毕业' => 8,
            //'' => 7,
        ];
        //付费类型：0为未付费、1为全额、2为分期、3为定金
        $studentPayTypeArr = [
            '未付费' => 0,
            '全款' => 1,
            '分期' => 2,
            '定金' => 3,
            '' => 9,
        ];
        //学习形式：1为成考、2为华师大、3为深大、4为网教
        $studyTypeArr = [
            '成考' => 1,
            '华师大' => 2,
            '深大' => 3,
            '网教' => 4,
            '' => 99,
        ];
        $loginPwd = 'its123456';
        //为空学员编号集合
        $has_null_student_no = array();
        $count_has_null_student_no = 0;
        //重复学员编号集合
        $has_student_no = array();
        $count_has_student_no = 0;
        $has_iptdata_1 = array();
        //重复身份证号码集合
        $has_id_card = array();
        $count_has_id_card = 0;
        $has_iptdata_2 = array();
        //重复手机号码集合
        $has_mobile = array();
        $count_has_mobile = 0;
        $has_iptdata_3 = array();
        //写入标识 false=不写入 true=写入
        $is_insert = false;
        //主表写入数据集合
        $insert_data = array();
        //扩展表写入数据集合
        $ext_insert_data = array();
        for ($row = 2; $row <= $rows; $row++){
            $data = [];
            $sheet->setCellValueExplicit("C".$row, $sheet->getCell("C".$row)->getValue(), \PHPExcel_Cell_DataType::TYPE_STRING2);
            //$sheet->getStyle("A".$row)->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_TEXT);
            $loginName = trim($sheet->getCell("C".$row)->getValue());
            $userPhone = trim($sheet->getCell("D".$row)->getValue());
            $userPhone = substr($userPhone,0,11);
            $name = trim($sheet->getCell("B".$row)->getValue());
            $student_no = trim($sheet->getCell("A".$row)->getValue());
            //指定key,关联扩展数据
            $key = $student_no;
            //$pre_entry_no = trim($sheet->getCell("C".$row)->getValue());
            //学号与预报名处理
            $pre_entry_no = '';
            $data['student_no'] = $student_no;
            $data['pre_entry_no'] = $pre_entry_no;
            
            //学员编号判断是否为空
            if(empty($student_no)):
                $has_null_student_no[] = '[身份证]'.$loginName;
                $count_has_null_student_no++;
                continue;
            endif;
            //学员编号判断是否重复
            $result_user1 = model('Users')->where(['student_no'=>$student_no,'dataFlag'=>1])->find();
            if( !empty($result_user1) ):
                $has_student_no[] = $student_no;
                $count_has_student_no++;
                continue; 
            endif;
            $has_iptdata_1[] = $student_no;
            //身份证号码判断是否重复
            $data['loginName'] = $loginName;
            $result_user2 = model('Users')->where(['loginName'=>$loginName,'dataFlag'=>1])->find();
            if( !empty($result_user2) ):
                $has_id_card[] = $loginName;
                $count_has_id_card++;
                continue;
            endif;
            $has_iptdata_2[] = $loginName;
            //手机号码判断是否重复
            $result_user3 = model('Users')->where(['userPhone'=>$userPhone,'dataFlag'=>1])->find();
            if( !empty($result_user3) ):
                $has_mobile[] = $userPhone;
                $count_has_mobile++;
                continue; 
            endif;
            $has_iptdata_3[] = $userPhone;
            //重置写入标识
            $is_insert = true;
            
            $data['trueName'] = $data['nickName'] = $name;
            $student_type = trim($sheet->getCell("F".$row)->getValue());
            $data['student_type'] = $studentTypeArr[$student_type];
            $study_status = trim($sheet->getCell("G".$row)->getValue());
            empty($study_status) && $study_status='在读';
            //if(!in_array($study_status,array_values($studyStatusArr))) $study_status='';
            //if(strpos($study_status,'申请')!==FALSE) $study_status='申请毕业';
            //if(strpos($study_status,'在读')!==FALSE) $study_status='在读';
            $data['study_status'] = $studyStatusArr[$study_status];
            $data['userPhone'] = $userPhone;
            $userEmail = trim($sheet->getCell("J".$row)->getValue());
            $data['userEmail'] = $userEmail&&strpos($userEmail,'@')===FALSE?$userEmail.'@qq.com':$userEmail;
            $userSex = trim($sheet->getCell("K".$row)->getValue());
            $data['userSex'] = $sexArr[$userSex];
            $data['loginSecret'] = rand(1000,9999);
            $data['loginPwd'] = md5($loginPwd.$data['loginSecret']);
            $data['userStatus'] = 1;
            //$createtime = trim($sheet->getCell("Q".$row)->getValue());
            //$createtime = intval(($createtime - 25569) * 3600 * 24);
			//$createtime = strtotime(gmdate('Y-m-d',$createtime));
            $createtime = 0;
            $data['createtime'] = time();
            $data['idcard'] = $loginName;
            $data['lastmodify'] = time();
            $data['is_import'] = 1;
            $data['data_type'] = 1;
            $data['batch_num'] = 3;
            $data['import_time'] = time();
            $data['userQQ'] = trim($sheet->getCell("H".$row)->getValue());
            $data['user_weixin'] = trim($sheet->getCell("I".$row)->getValue());
            $insert_data[$key] = $data;
            //$userId = Db::name('users')->insert($data,false,true);
            /* 扩展信息 */
            //QQ号	微信号	邮箱	性别	通讯地址	紧急联系人	紧急联系电话	工作单位	职务
            $urgency_contact = trim($sheet->getCell("M".$row)->getValue());
            $urgency_contact_mobile = trim($sheet->getCell("N".$row)->getValue());
            $company = trim($sheet->getCell("O".$row)->getValue());
            $job_content = trim($sheet->getCell("P".$row)->getValue());
            $mobile2 = trim($sheet->getCell("E".$row)->getValue());
            $extData = array('urgency_contact'=>$urgency_contact,'urgency_contact_mobile'=>$urgency_contact_mobile,'company'=>$company,'job_content'=>$job_content,'mobile2'=>$mobile2,'createtime'=>time(),'lastmodify'=>time());
            $ext_insert_data[$key] = $extData; 
            //Db::name('student_extend')->insert($extData);
        }
        //dump($readData);exit;
        //批量写入数据
        if($is_insert == true && count($insert_data)>0):
            Db::startTrans();
            $lists = model('Users')->saveAll($insert_data);
            $extData = [];
            foreach ($lists as $key =>$v):
                $extData[] = array_merge(['userId'=>$v['userId']],$ext_insert_data[$key]);
            endforeach;
            if(count($extData)>0)Db::name('student_extend')->insertAll($extData);
            Db::commit();
        endif;
        $repeat_data = [
            ['name'=>"学员编号(为空)[{$count_has_null_student_no}条]",'lists'=>$has_null_student_no],
            ['name'=>"学员编号重复[{$count_has_student_no}条]",'lists'=>$has_student_no],
            ['name'=>"身份证号码重复[{$count_has_id_card}条]",'lists'=>$has_id_card],
            ['name'=>"手机号码重复[{$count_has_mobile}条]",'lists'=>$has_mobile],
        ];
        $return = array(
            'finish_import_num'=>count($insert_data),
            'nofinish_import_num'=>($count_has_null_student_no+$count_has_student_no+$count_has_id_card+$count_has_mobile),
            'repeat_data'=>$repeat_data
        );
        //dump($return);exit;
        return $return;
   }
   
   /* 导入学历报名数据 */
   public function importEntrys($filename = 'users-bm.xlsx'){
        set_time_limit(0);
		Loader::import('phpexcel.PHPExcel.IOFactory');
        $num = isset($_GET['num'])&&!empty($_GET['num'])?$_GET['num']:1;
        //$filename = "/xlsx/{$num}.xlsx";
		$objReader = \PHPExcel_IOFactory::load($filename);
		$objReader->setActiveSheetIndex(0);
		$sheet = $objReader->getActiveSheet();
		$rows = $sheet->getHighestRow();
		$cells = $sheet->getHighestColumn();
        $max_rows = 200;
        if($rows-2>$max_rows){exit("每次导入数据最多{$max_rows}条<a href=\"javascript:history.back()\">返回</a>");}
        $readData = [];
        $importNum = 0;
        //性别
        $sexArr = [
            '男' => 1,
            '女' => 2,
            '无' => 0,
            '' => 0,
        ];
        //学员类型：1为技能、2为学历、3为技能学历
        $studentTypeArr = [
            '技能' => 1,
            '学历' => 2,
            '技能学历' => 3,
            '' => 1,
        ];
        //学习状态：1为在读、2为毕业、3为过期、4为弃学、5为休学、6为退学
        $studyStatusArr = [
            '在读' => 1,
            '毕业' => 2,
            '过期' => 3,
            '弃学' => 4,
            '休学' => 5,
            '退学' => 6,
        ];
        //付费类型：0为未付费、1为全额、2为分期、3为定金
        $studentPayTypeArr = [
            '未付费' => 0,
            '全款' => 1,
            '分期' => 2,
            '定金' => 3,
            '' => 9,
        ];
        //学习形式：1为成考、2为华师大、3为深大、4为网教
        $studyTypeArr = [
            '成考' => 1,
            '华师大' => 2,
            '深大' => 3,
            '网教' => 4,
            '' => 99,
        ];
        #dump(strpos('a|b|c','|')!==FALSE );exit;
        #dump($cells);exit;
        $loginPwd = 'its123456';
        $has_data = array();
        //会员数据条件
        $where_users = [];
        //学校数据条件
        $where_schools = [];
        //专业数据条件
        $where_majors = [];
        //考试类型
        $where_exam_types = [];
        //层次
        $where_level_types = [];
        //年级
        $where_grades = [];
        //课程数据
        $course_datas = [];
        //批次
        $batch_num = 1;
        //没有匹配到
        $data_no_match = [];
        for ($row = 3; $row <= $rows; $row++){
            #Db::startTrans();
            $data = [];												
            #会员id
            $idcard = trim($sheet->getCell("A".$row)->getValue());
            #学员编号
            $student_no = trim($sheet->getCell("B".$row)->getValue());
            #预报名编号
            $pre_entry_no = trim($sheet->getCell("C".$row)->getValue());
            #姓名
            $name = trim($sheet->getCell("D".$row)->getValue());
            #合作方
            $parter_name = trim($sheet->getCell("E".$row)->getValue());
            #报读院校
            $school_name = trim($sheet->getCell("F".$row)->getValue());
            #报名专业名称
            $major_name = trim($sheet->getCell("G".$row)->getValue());
            $major_name = $this->trans_major_names($major_name);
            #考试类型
            $exam_type = trim($sheet->getCell("H".$row)->getValue());  
            in_array($exam_type,['自考全日制','深大','自考'])
            && $exam_type='自考';
            in_array($exam_type,['自考（深大全日制学员）'])
            && $exam_type='自考（深大全日制学员）';
            #层次
            $level_type = trim($sheet->getCell("I".$row)->getValue());
            in_array($level_type,['','网教','套读','高升本','高升专'])
            && $level_type='专科(高升专)'; 
            in_array($level_type,['自考','专本套读','本升本'])
            && $level_type='本科(专升本)';
            #学习形式
            $study_type = trim($sheet->getCell("J".$row)->getValue());
            #报名时间
            $entry_time = trim($sheet->getCell("K".$row)->getValue());
            $entry_time = intval(($entry_time - 25569) * 3600 * 24);
			$entry_time = strtotime(gmdate('Y-m-d',$entry_time));
            #年级
            $grade_name = trim($sheet->getCell("L".$row)->getValue());
            #考籍号
            $exam_no = trim($sheet->getCell("M".$row)->getValue());
            #信息来源
            $info_source = trim($sheet->getCell("N".$row)->getValue());
            #报名来源校区代码
            $school_code = trim($sheet->getCell("R".$row)->getValue());
            #标准价
            $totalMoney = trim($sheet->getCell("S".$row)->getValue());
            #优惠金额
            $discount_fee = trim($sheet->getCell("T".$row)->getValue());
            #应收学费
            $realPayMoney = trim($sheet->getCell("U".$row)->getValue());
            #实收费用
            
            #总支出
            $arrearage_fee = trim($sheet->getCell("V".$row)->getValue());
            #已收费用
            #欠费
            #备注(注明优惠明细)
            #发票号码
            #学费收缴情况备注说明
            #学员证制作
            #结业证制作
            #就业情况
            #结课情况
            #交资料情况(序列化）
            #通知上课情况
            #反馈情况
            #
            #
            #
            $key = $idcard;
            (empty($key) || strlen($key)<=11) && $key = $name;
            #empty($key) && $key = $pre_entry_no;
            if(empty($key) || (int)$totalMoney<=0) 
            {
                $data_no_match[] = $key;
                continue;
            }
            /*$where_users[] = $key;
            $where_schools[] = $school_name;
            $where_majors[] = $major_name;
            $where_exam_types[] = $exam_type;
            $where_level_types[] = $level_type;
            $where_grades[] = $grade_name;*/
            !empty($key) && $tmp_users = $this->getUserLists(['key'=>['in',[$key]]],$msg);
            if(!empty($tmp_users[0]['userId']))//查会员
            {
                $userId = $tmp_users[0]['userId'];
                $trueName = $tmp_users[0]['trueName'];
                $userPhone = $tmp_users[0]['userPhone'];
                $tmp_schools = $this->getSchoolLists(['name'=>[$school_name],'all_names'=>[]]);
                $schoolId = !empty($tmp_schools[0]['school_id'])?$tmp_schools[0]['school_id']:0;
                empty($schoolId) && !in_array($school_name,['','未定','暂未定院校'])
                   && $schoolId = Db::name('school')->insert(['jump_type' => 1,'name' => $school_name, 'data_type' => 1, 'batch_num' => $batch_num],false,true);  
                if(!empty($schoolId))//查学校
                {
                   $level_type_id = ITSSelItemId('major','level_type',$level_type);
                   $exam_type_id = ITSSelItemId('major','exam_type',$exam_type);
                   //$course_datas[$key.'_'.$schoolId] = 1;
                   $tmp_majors = $this->getMajorLists(['school_ids'=>[$schoolId],'exam_type_ids'=>[$exam_type_id],'level_type_ids'=>[$level_type_id],'name'=>[$major_name],'all_names'=>[] ]);
                   $majorId = !empty($tmp_majors[0]['major_id'])?$tmp_majors[0]['major_id']:0;
                   
                   empty($majorId) && !in_array($major_name,['','未确定','待定'])
                   && $majorId = Db::name('major')->insert(['type_id' => 1,'school_id' => $schoolId,'name' => $major_name,'level_type' => $level_type_id,'exam_type' =>$exam_type_id, 'data_type' => 1, 'batch_num' => $batch_num ],false,true);
                    if(!empty($majorId))//查专业
                    {
                        
                        //$course_datas[$key.'_'.$majorId] = 1;
                        $tmp_grades = $this->getGradeLists(['major_ids'=>[$majorId],'name'=>[$grade_name],'all_names'=>[] ]);
                        $gradeId = !empty($tmp_grades[0]['grade_id'])?$tmp_grades[0]['grade_id']:0;
                        empty($gradeId) && !empty($grade_name)
                   && $gradeId = Db::name('grade')->insert(['major_id' => $majorId,'name' => $grade_name,'stu_fee' => $totalMoney, 'data_type' => 1, 'batch_num' => $batch_num, 'data_type' => 1, 'batch_num' => $batch_num],false,true);
                        if(!empty($gradeId))//查年级
                        {
                            
                            //$course_datas[$key.'_'.$gradeId] = 1;
                            //$c_key = $key.'_'.$schoolId.'_'.$majorId.'_'.$gradeId;
                            //$course_ids[$c_key] = $gradeId;
                            $tmp_courses = $this->getCourseLists(['major_ids'=>[$majorId],'grade_ids'=>[$gradeId]]);
                            $courseId = !empty($tmp_courses[0]['course_id'])?$tmp_courses[0]['course_id']:0;
                            empty($courseId)
                   && $courseId = Db::name('course')->insert(['type_id' => 1,'school_id' => $schoolId,'major_id' => $majorId,'grade_id' => $gradeId,'name' => $major_name.'-'.$level_type.'-'.$exam_type.'-'.$grade_name,'offers_price' => $totalMoney,'market_price' => $totalMoney, 'data_type' => 1, 'batch_num' => $batch_num],false,true);
                            if(!empty($courseId))//查课程
                            {
                                //$course = $tmp_courses[0];
                                $course_datas[] = [
                                    'course_id' => $courseId,
                                    'userId' => $userId,
                                    'discountMoney' => (int)($totalMoney-$realPayMoney),
                                    'totalMoney' => (int)$totalMoney,
                                    'realTotalMoney' => (int)$realPayMoney,
                                    'realPayMoney' => (int)$realPayMoney,
                                    'name' => $trueName,
                                    'mobile' => $userPhone,
                                    'idcard' => $idcard,
                                    'entry_time' => !empty($entry_time)?strtotime($entry_time):0,
                                ];
                                #Db::commit();
                                #dump($tmp_courses);
                            }
                            else
                            {
                                $data_no_match[] = $key;
                                #Db::rollback();   
                            }
                        }
                        else
                        {
                            $data_no_match[] = $key;
                            #Db::rollback();   
                        }
                    }
                    else
                    {
                        $data_no_match[] = $key;
                        #Db::rollback();   
                    }   
                }
                else
                {
                    $data_no_match[] = $key;
                    #Db::rollback();   
                }
            }
            $data = [
                'idcard'=>$idcard,
                'student_no'=>$student_no,
                'pre_entry_no'=>$pre_entry_no,
                'name'=>$name,
                'parter_name'=>$parter_name,
            ];
            $data['key'] = $key;
            $readData[] = $data;
            $importNum++;
        }
            /** 写入订单 **/
            $import_data = $repeat_data = array();
            foreach($course_datas as $v):
            $rs_users = Db::name('users')->where(['userId'=>$v['userId']])->find();
            //判断是否有重复课程
            $rs_order_detail = Db::name('order_detail')->where(['userId'=>$v['userId'],'course_id'=>$v['course_id']])->find();
            if(!empty($rs_order_detail)){
                $repeat_data[] = array('name'=>$rs_users['trueName'],'idcard'=>$rs_users['idcard'],'course_name'=>$rs_order_detail['course_name'],'orderNo'=>$rs_order_detail['orderNo']);
                continue;    
            }
            $params = array (
  'accesstoken' => $v['userId'],
  'userId' => $v['userId'],
  'jump_type' => 1,
  'cartData' => 
  array (
    0 => array (
      'cartId' => 0,
      'type_id' => 1,
      'userId' => $v['userId'],
      'course_id' => $v['course_id'],
      'subject_id' => 0,
      'cartNum' => 1,
      'extend_data' => '',
      'adItemList' => 
      array (
      ),
      'is_full_pay' => '1',
      'add_deposit_price' => 0,
    ),
  ),
  'orderData' => 
  array (
    'orderInfo' => 
    array (
      'discountMoney' => $v['discountMoney'],
      'totalMoney' => $v['totalMoney'],
      'realTotalMoney' => $v['realTotalMoney'],
      'realPayMoney' => $v['realPayMoney'],
      'name' => $v['name'],
      'mobile' => $v['mobile'],
      'idcard' => $v['idcard'],
      'entry_time' => $v['entry_time'],
    ),
    'paymentInfo' => 
    array (
      2 => 4,
    ),
  ),
  'channelType' => 1,
  'nodelcart' => 1,
  'isImport' => 1,
);
$res = model('common/orders')->getApiCreateOrder(99,$params,$rs_users);
$import_data[] = $v;
            endforeach;
            return array(
                'finish_import_num'=>count($import_data),
                'nofinish_import_num'=>count($repeat_data),
                'repeat_data'=>$repeat_data
            );
            //遍历
            #echo '<pre>';var_export($params);
            exit;
        
        
        #$curl = new \org\Curl();
        #$res = $curl->action('post','/index.php/api/orders/create',null,null,$params);
        
        # 匹配用户信息 #
        if(false)
        {
        !empty($where_users) && $tmp_users = $this->getUserLists(['key'=>['in',$where_users]],$msg);
        # 匹配学校 #
        !empty($where_schools) && $where_schools = array_unique($where_schools);
        !empty($where_schools) && $tmp_schools = $this->getSchoolLists(['key'=>['in',$where_users],'name'=>$where_schools,'all_names'=>$where_schools]);
        # 匹配考试类型 #
        !empty($where_exam_types) && $where_exam_types = array_unique($where_exam_types);
        !empty($where_exam_types) && $tmp_exam_types = $this->getExamTypeLists(['key'=>['in',$where_users],'name'=>$where_exam_types,'all_names'=>$where_exam_types]);
        # 匹配层次 #
        !empty($where_level_types) && $where_level_types = array_unique($where_level_types);
        !empty($where_level_types) && $tmp_level_types = $this->getLevelTypeLists(['key'=>['in',$where_users],'name'=>$where_level_types,'all_names'=>$where_level_types]);
        # 匹配专业 #
        !empty($where_majors) && $where_majors = array_unique($where_majors);
        !empty($where_majors) && $tmp_majors = $this->getMajorLists(['key'=>['in',$where_users],'school_ids'=>$tmp_schools['school_ids'],'exam_type_ids'=>$tmp_exam_types['exam_type_ids'],'level_type_ids'=>$tmp_level_types['level_type_ids'],'name'=>$where_majors,'all_names'=>$where_majors]);
        # 匹配年级信息 #
        !empty($where_grades) && $where_grades = array_unique($where_grades);
        !empty($where_grades) && $tmp_grades = $this->getGradeLists(['key'=>['in',$where_users],'major_ids'=>$tmp_majors['major_ids'],'name'=>$where_grades,'all_names'=>$where_grades]);
        # 匹配课程信息 #
        if(!empty($tmp_grades['grade_ids']))
        {
            $tmp_courses = $this->getCourseLists(['key'=>['in',$where_users],'grade_ids'=>$tmp_grades['grade_ids']]);
        }
        }
    }
   
   
   
    /**
        @do      获取会员列表
        @desc    获取所有会员数据
        @params  接受参数处理 array
        @msg     消息提示
    */
    public function getUserLists($params=[],&$msg='')
    {
       try{
           if(empty($params['key']))
           {
               $msg = MBISReturn('参数错误[key]');
               return false;   
           }
           $rs = [];
           $where = [];
           strlen($params['key'][1][0])>12 && $where['loginName'] = $params['key'];#|student_no|pre_entry_no
           strlen($params['key'][1][0])<=12 && $where['trueName'] = $params['key'];
           $rs = Db::name('users')->where($where)->field('userId,userType,rankId,uidType,trueName,userPhone')->select();
           #return format_arr_by_key('loginName|student_no|pre_entry_no',$rs,$msg);
           return $rs;
       }catch(\Exception $e){
           echo $e->getFile().$e->getLine().$e->getMessage();
       }
    }
    //学校处理
    public function getSchoolLists($params,&$msg='')
    {
       #['name'=>['in',$where_schools] ]
       $all_names = $params['all_names'];
       $params['name'] = $this->trans_school_names($params['name']);
       $where['jump_type'] = 1;
       //$where['name'] = ['in',$params['name']];
       $where['name'] = ['like',"%{$params['name'][0]}%"];
       $rs = model('school')->where($where)->select();
       return $rs;
       $new_all_names = [];
       $school_ids = [];
       foreach($rs as $k=>$v)
       {
            $new_all_names[] = $v['name'];
            $school_ids[] = $v['school_id'];
       }
       $not_find_names = array_diff($all_names,$new_all_names);
       //未匹配到数据自动创建
       $new_data = [];
       foreach($not_find_names as $k=>$v)
       {
           !in_array($v,['','未定','暂未定院校'])
           && $new_data[] = [
                'jump_type' => 1,
                'name' => $v,
           ];   
       }
       if(!empty($new_data))
       {
            /*$lists = model('school')->saveAll($new_data);
            foreach($lists as $school_id)
            {
                $school_ids[] = $school_id;   
            }*/
       }
       //dump($school_ids);exit;
       //model('school')
       #echo '<pre>';var_export($not_find_names);exit;
       return ['lists'=>$rs,'school_ids'=>$school_ids,'not_find_names'=>$not_find_names]; 
    }
    //学校数据转换
    public function trans_school_names($data)
    {
       /*$not_found_data = array (
          '中国石油大学（华东校区）'=>'中国石油大学（华东）'
        );
       in_array($data[0],array_flip($not_found_data)) 
       && $data[0]=$not_found_data[$data[0]];*/
       return $data;   
    }
    //考试类型处理
    public function getExamTypeLists($params,&$msg='')
    {
       $all_names = $params['all_names'];
       $names = $params['name'];
       $data_exam_type = ITSGetSelData('major','exam_type');
       $rs = $data_exam_type['lists'];
       $new_all_names = [];
       $exam_type_ids = [];
       foreach($rs as $k=>$v)
       {
           if(in_array($v['name'],$names))
           {
               $new_all_names[] = $v['name'];
               $exam_type_ids[] = $v['id'];
           }
       }
       $not_find_names = array_diff($all_names,$new_all_names);
       #echo '<pre>';var_export($not_find_names);exit;
       return ['lists'=>$rs,'exam_type_ids'=>$exam_type_ids,'not_find_names'=>$not_find_names]; 
    }
    //层次类型处理
    public function getLevelTypeLists($params,&$msg='')
    {
       $all_names = $params['all_names'];
       $names = $params['name'];
       $data_level_type = ITSGetSelData('major','level_type');
       $rs = $data_level_type['lists'];
       array_push($rs,array('id'=>2,'name'=>'高升专'));
       array_push($rs,array('id'=>3,'name'=>'专升本'));
       #array_push($rs,array('id'=>4,'name'=>'专本套读'));
       #array_push($rs,array('id'=>1,'name'=>'高升本'));
       $new_all_names = [];
       $level_type_ids = [];
       foreach($rs as $k=>$v)
       {
           if(in_array($v['name'],$names))
           {
               $new_all_names[] = $v['name'];
               $level_type_ids[] = $v['id'];
           }
       }
       $not_find_names = array_diff($all_names,$new_all_names);
       #echo '<pre>';var_export($not_find_names);exit;
       return ['lists'=>$rs,'level_type_ids'=>$level_type_ids,'not_find_names'=>$not_find_names]; 
    }
    //专业处理
    public function getMajorLists($params,&$msg='')
    {
       #['name'=>['in',$where_schools] ]
       $all_names = $params['all_names'];
       //$params['name'] = $this->trans_major_names($params['name']);
       #类型判断
       $where['type_id'] = 1;
       #学校条件
       $where['school_id'] = ['in',$params['school_ids']];
       #专业名称条件
       //$where['name'] = ['in',$params['name']];
       $where['name'] = ['like',"%{$params['name'][0]}%"];
       #考试类型
       $where['exam_type'] = ['in',$params['exam_type_ids']];
       #层次
       $where['level_type'] = ['in',$params['level_type_ids']];
       $rs = model('major')->where($where)->select();
       return $rs;
       $new_all_names = [];
       $major_ids = [];
       foreach($rs as $k=>$v)
       {
            $new_all_names[] = $v['name'];
            $major_ids[] = $v['major_id'];   
       }
       $not_find_names = array_diff($all_names,$new_all_names);
       //未匹配到数据自动创建
       $new_data = [];
       foreach($not_find_names as $k=>$v)
       {
           !in_array($v,['','未定','暂未定院校'])
           && $new_data[] = [
                'jump_type' => 1,
                'name' => $v,
           ];   
       }
       if(!empty($new_data))
       {
            /*$lists = model('major')->saveAll($new_data);
            foreach($lists as $major_id)
            {
                $major_ids[] = $major_id;   
            }*/
       }
       #echo '<pre>';var_export($not_find_names);exit;
       return ['lists'=>$rs,'major_ids'=>$major_ids,'not_find_names'=>$not_find_names]; 
    }
    //专业数据转换
    public function trans_major_names($data)
    {
       $not_found_data = array (
        '艺术设计（平面）'=>'艺术设计（平面设计方向）',
        '艺术设计（环艺）'=>'艺术设计（环境艺术设计方向）',

        '艺术设计专业'=>'艺术设计（平面设计方向）',

        '艺术设计（环艺方向）'=>'艺术设计（环境艺术设计方向）',

        '艺术设计（平面方向）'=>'艺术设计（平面设计方向）',

        '艺术设计（平面方向）'=>'艺术设计（平面设计方向）',

        '艺术设计专业（环境艺术设计方向）'=>'艺术设计（环境艺术设计方向）',

        '人力资源专业'=>'人力资源管理',

        '平面设计'=>'艺术设计（平面设计方向）',

        '动漫设计与制作'=>'动漫设计与技术',

        '人力资源管理师'=>'人力资源管理',

        '人力资源'=>'人力资源管理',

        '中药专业'=>'中药',

        '护理专业'=>'护理学',

        '装饰艺术设计'=>'装饰艺术设计（窒内装饰设计）',

        '金融管理'=>'金融管理与实务',

        '机械制造及其自动化'=>'机械制造与自动化',

        '金融实务与管理'=>'金融管理与实务',

        '机械设计及其自动化'=>'机械设计制造及其自动化',

        '计算机科学与技术专业'=>'计算机科学与技术',

        '机械设计制造与自动化'=>'机械设计制造及其自动化',

         '计算机信息管理专'=>'计算机信息管理',

        '教育学（教育管理方向）'=>'教育学（小学教育方向；教育管理方向）',

        '计算机专业'=>'计算机',

         '法律实务'=>'法律事务',

        '英语专业'=>'英语',

         '视觉传达艺术'=>'视觉传达艺术设计',

         '经济学（项目管理高起专）'=>'经济学（项目管理高升专）',

         '机械制造及自动化'=>'机械制造与自动化',

        '计算机'=>'计算机科学与技术',

         '金融学（经济类）'=>'金融学',

        '互联网营销'=>'计算机科学与技术（互联网营销）',

         '机械工程及自动化（机电一体化）'=>'机械工程及其自动化（机电一体化）',

        '经济学（项目管理）'=>'经济学（项目管理高升专）',

        '计算机技术'=>'计算机网络技术',

        '资源勘察工程'=>'资源勘查工程'
            );
       in_array($data,array_flip($not_found_data)) 
       && $data=$not_found_data[$data];
       return $data;      
    }
    //年级处理
    public function getGradeLists($params,&$msg='')
    {
       #['name'=>['in',$where_schools] ]
       $all_names = $params['all_names'];
       $params['name'] = $this->trans_grade_names($params['name']);
       $where['major_id'] = ['in',$params['major_ids']];
       //$where['name'] = ['in',$params['name']];
       $where['name'] = ['like',"%{$params['name'][0]}%"];
       $rs = model('grade')->where($where)->select();
       return $rs;
       $new_all_names = [];
       $grade_ids = [];
       #dump($rs);exit;
       foreach($rs as $k=>$v)
       {
            $new_all_names[] = $v['name'];
            $grade_ids[] = $v['grade_id'];
       }
       #dump($grade_ids);exit;
       $not_find_names = array_diff($all_names,$new_all_names);
       #echo '<pre>';var_export($not_find_names);exit;
       return ['lists'=>$rs,'grade_ids'=>$grade_ids,'not_find_names'=>$not_find_names]; 
    }
    //年级转换数据
    public function trans_grade_names($data)
    {
       /*$grades = array (
          '2017级'=>'2017级',
          '201610'=>'2016级10月',
          '201603'=>'2016级春季',
          '201609'=>'2016级秋季',
          '201703'=>'2017级春季',
          '201903'=>'2019级春季',
          '2016级'=>'2016级',
          '2018级'=>'2018级',
          '201503'=>'2017级春季',
          '201509'=>'2015级秋季',
          '201803'=>'2018级春季',
          '201709'=>'2017级秋季',
        );*/
        return $data;
    }
    //课程处理
    public function getCourseLists($params,&$msg='')
    {
       #['name'=>['in',$where_schools] ]
       #$all_names = $params['all_names'];
       #$params['name'] = $this->trans_school_names($params['name']);
       #$where['major_id'] = ['in',$params['major_ids']];
       $where['major_id'] = ['in',$params['major_ids']];
       $where['grade_id'] = ['in',$params['grade_ids']];
       $rs = model('course')->where($where)->select();
       return $rs;
       #dump($where);
       $new_all_names = [];
       $course_ids = [];
       foreach($rs as $k=>$v)
       {
            $new_all_names[] = $v['name'];
            $course_ids[] = $v['course_id'];
       }
       //$not_find_names = array_diff($all_names,$new_all_names);
       #echo '<pre>';var_export($rs);exit;
       return ['lists'=>$rs,'course_ids'=>$course_ids]; 
    }

    public function importUserInfo(){     //导入学员数据
        set_time_limit(0);
        Loader::import('phpexcel.PHPExcel.IOFactory');
        $filename = '/user.xlsx';
        $num = isset($_GET['num'])&&!empty($_GET['num'])?$_GET['num']:1;
        //$filename = "/xlsx/{$num}.xlsx";
        $objReader = \PHPExcel_IOFactory::load(MBISRootPath().$filename);
        //dump($objReader);die;
        $objReader->setActiveSheetIndex(0);
        $sheet = $objReader->getActiveSheet();
        $rows = $sheet->getHighestRow();
        $cells = $sheet->getHighestColumn();
        $readData = [];
        $importNum = 0;
        /*
        //性别
        $sexArr = [
            '男' => 1,
            '女' => 2,
            '无' => 0,
            '' => 0,
        ];
        //学员类型：1为技能、2为学历、3为技能学历
        $studentTypeArr = [
            '技能' => 1,
            '学历' => 2,
            '技能学历' => 3,
            '' => 1,
        ];
        //学习状态：1为在读、2为毕业、3为过期、4为弃学、5为休学、6为退学
        $studyStatusArr = [
            '在读' => 1,
            '毕业' => 2,
            '过期' => 3,
            '弃学' => 4,
            '休学' => 5,
            '退学' => 6,
            '未确认' => 7,
            '申请毕业' => 8,
            '' => 7,
        ];
        //付费类型：0为未付费、1为全额、2为分期、3为定金
        $studentPayTypeArr = [
            '未付费' => 0,
            '全款' => 1,
            '分期' => 2,
            '定金' => 3,
            '' => 9,
        ];
        //学习形式：1为成考、2为华师大、3为深大、4为网教
        $studyTypeArr = [
            '成考' => 1,
            '华师大' => 2,
            '深大' => 3,
            '网教' => 4,
            '' => 99,
        ];
        */
        $loginPwd = 'its123456';
        //$has_data = array();
        for ($row = 1; $row <= $rows; $row++){
            $data = [];
            //$sheet->setCellValueExplicit("D".$row, $sheet->getCell("D".$row)->getValue(), \PHPExcel_Cell_DataType::TYPE_STRING2);
            //$sheet->getStyle('A')->getNumberFormat()
        //->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2);
            //$sheet->getStyle("A".$row)->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_TEXT);
            $student_no = trim($sheet->getCell("A".$row)->getValue());
            $trueName = trim($sheet->getCell("B".$row)->getValue());
            $createtime = trim($sheet->getCell("D".$row)->getValue());
            $createtime = intval(($createtime - 25569) * 3600 * 24);
            $createtime = strtotime(gmdate('Y-m-d',$createtime));
            //$createtime = gmdate('Y-m-d',$createtime);
            //dump($createtime);die;
            $loginName = $trueName;
            //$loginName = trim($sheet->getCell("A".$row)->getValue());
            //$userPhone = trim($sheet->getCell("F".$row)->getValue());
            //$userPhone = substr($userPhone,0,11);
            //$name = trim($sheet->getCell("D".$row)->getValue());
            //P967925（5）、R090669（6） 替换成手机号
            //if(strpos($loginName,'（') !== FALSE)
            //{
            //    $loginName=$userPhone;   
            //}
            //if(empty($loginName)) $loginName=$userPhone;
            //if(empty($loginName)) $loginName=$name;
            //if(empty($loginName)) continue;
            //$loginName = $loginName;
            //$has_data[$loginName][] = 1;
            //$key = $loginName;
            //$student_no = trim($sheet->getCell("B".$row)->getValue());
            //$pre_entry_no = trim($sheet->getCell("C".$row)->getValue());
            //学号与预报名处理
            //if(empty($key))
            //{
                //$key = $pre_entry_no;   
            //}
            
            $result_user = model('Users')->where(['trueName'=>$trueName,'dataFlag'=>1])->find();
            if( !empty($result_user) )
            {
                //@file_put_contents('./log_users.log',date('Y-m-d H:i:s').'userId:'.$result_user['userId'].' - name:'.$name.' - loginName:'.$loginName.chr(10),FILE_APPEND);
                add_logs('import_users','userId:'.$result_user['userId'].' - student_no_in:'.$result_user['student_no'].' - student_no_out:'.$student_no.' - trueName_in:'.$result_user['trueName'].' - trueName_out:'.$trueName.' - loginName:'.$result_user['loginName']);
                    continue;
            }


            $data['student_no'] = $student_no;
            $data['trueName'] = $data['nickName'] = $trueName;
            $data['createtime'] = $createtime;
            //$data['pre_entry_no'] = $pre_entry_no;
            $data['loginName'] = $loginName;
            //if(isset($has_data[$loginName]) && count($has_data[$loginName])>1)
            //{
            //   continue;   
            //}
            //$data['trueName'] = $data['nickName'] = $name;
            //$student_type = trim($sheet->getCell("E".$row)->getValue());
            //$data['student_type'] = $studentTypeArr[$student_type];
            //$study_status = trim($sheet->getCell("J".$row)->getValue());
            //if(!in_array($study_status,array_values($studyStatusArr))) $study_status='';
            //if(strpos($study_status,'申请')!==FALSE) $study_status='申请毕业';
            //if(strpos($study_status,'在读')!==FALSE) $study_status='在读';
            //$data['study_status'] = $studyStatusArr[$study_status];
            //$data['userPhone'] = $userPhone;
            //$userEmail = trim($sheet->getCell("G".$row)->getValue());
            //$data['userEmail'] = $userEmail&&strpos($userEmail,'@')===FALSE?$userEmail.'@qq.com':$userEmail;
            //$userSex = trim($sheet->getCell("H".$row)->getValue());
            //$data['userSex'] = $sexArr[$userSex];
            $data['loginSecret'] = rand(1000,9999);
            $data['loginPwd'] = md5($loginPwd.$data['loginSecret']);
            $data['userStatus'] = 1;
            //$createtime = trim($sheet->getCell("Q".$row)->getValue());
            //$createtime = intval(($createtime - 25569) * 3600 * 24);
            //$createtime = strtotime(gmdate('Y-m-d',$createtime));
            //$data['createtime'] = $createtime?$createtime:time();
            $data['lastmodify'] = time();
            $data['is_import'] = 1;
            $data['import_time'] = time();
            $data['data_type'] = 1;
            $data['batch_num'] = 2;
            //$data['key'] = $key;
            $readData[] = $data;
            $importNum++;
        }
        //unset($readData);
        //dump($readData);
        $readData = $this->second_array_unique_bykey($readData,$key='trueName');
        //dump($readData);die;
        //$objReader->setActiveSheetIndex(1);
        //$sheet = $objReader->getActiveSheet();
        //$rows = $sheet->getHighestRow();
        //$cells = $sheet->getHighestColumn();
        //$readData1 = [];
        //$importNum = 0;
        //for ($row = 1; $row <= $rows; $row++){
            //$data = [];
            //$key = trim($sheet->getCell("A".$row)->getValue());
            //$data['pre_entry_no'] = trim($sheet->getCell("B".$row)->getValue());
            //学号与预报名处理
            //if(empty($key))
            //{
            //    continue;
                //$key = $data['pre_entry_no'];   
            //}
            //$student_type = trim($sheet->getCell("C".$row)->getValue());
            //$data['student_type'] = !empty($studentTypeArr[$student_type])?$studentTypeArr[$student_type]:'';
            
            //$study_status = trim($sheet->getCell("D".$row)->getValue());
            //$data['study_status'] = !empty($studyStatusArr[$study_status])?$studyStatusArr[$study_status]:'';
            
            //$student_pay_type = trim($sheet->getCell("G".$row)->getValue());
            //$data['student_pay_type'] = !empty($studentPayTypeArr[$student_pay_type])?$studentPayTypeArr[$student_pay_type]:'';
            
            //$study_type = trim($sheet->getCell("H".$row)->getValue());
            //$data['study_type'] = !empty($studyTypeArr[$study_type])?$studyTypeArr[$study_type]:'';
            //$data['idcard_no'] = trim($sheet->getCell("E".$row)->getValue());
            //$data['customer_source'] = trim($sheet->getCell("N".$row)->getValue());
            //$data['createtime'] = $data['lastmodify'] = time();
            //$readData1[$key] = $data;
            //$readData1[] = $data;
            //$importNum++;
        //}
        /*
        dump($readData1);exit;
        
        //Db::startTrans();
        $i = 1;
        $userData = [];
        $login_key = array();
        foreach($readData as $k=>$v)
        {
             $key = $v['key'];
             unset($v['key']);
             $userData[] = $v;
             $login_key[$v['loginName']] = $key;
             $i++;
        }
        */
        //dump($readData);exit;
        $extData = [];
        if(count($readData)>0){
            $list = model('Users')->saveAll($readData);
            //dump($list);die;
            
            //dump($merge_data);die;
            foreach ($list as $key =>$v){
                //$oldkey = $login_key[$v['loginName']];
                //$merge_data = !empty($readData1[$oldkey])?$readData1[$oldkey]:['idcard_no'=>'','customer_source'=>'','createtime'=>time(),'lastmodify'=>time()];
                //$extData[] = array_merge(['userId'=>$v['userId']],$merge_data);
                $extData[]['userId'] = $v['userId']; 
                //$extData[]['idcard_no'] = ''; 
                //$extData[]['customer_source'] = ''; 
                //$extData[]['createtime'] = time(); 
                //$extData[]['lastmodify'] = time(); 
                //$extData[] = array_merge($extData,$merge_data);
            }
            //dump($extData);die;
            foreach($extData as &$v){
                $v['idcard_no'] = '';
                $v['customer_source'] = '';
                $v['createtime'] = time();
                $v['lastmodify'] = time();
            }
            //dump($extData);die;
            if(count($extData)>0)Db::name('student_extend')->insertAll($extData);
            //Db::commit();
        }
        if(count($readData)>0){
            echo 'Run : users('.count($readData).'), extend('.count($readData).') datas';
        }else{
            echo 'Run : users('.'0'.'), extend('.'0'.') datas';
        }
    }
    public function importFeeLog(){    //导入缴费记录
        set_time_limit(0);
        Loader::import('phpexcel.PHPExcel.IOFactory');
        $filename = '/user1.xlsx';
        $num = isset($_GET['num'])&&!empty($_GET['num'])?$_GET['num']:1;
        //$filename = "/xlsx/{$num}.xlsx";
        $objReader = \PHPExcel_IOFactory::load(MBISRootPath().$filename);
        //dump($objReader);die;
        $objReader->setActiveSheetIndex(0);
        $sheet = $objReader->getActiveSheet();
        $rows = $sheet->getHighestRow();
        $cells = $sheet->getHighestColumn();
        $readData = [];
        $importNum = 0;
        
        for ($row = 1; $row <= $rows; $row++){
            $data = [];
            //$sheet->setCellValueExplicit("D".$row, $sheet->getCell("D".$row)->getValue(), \PHPExcel_Cell_DataType::TYPE_STRING2);
            //$sheet->getStyle('A')->getNumberFormat()
            //->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2);
            //$sheet->getStyle("A".$row)->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_TEXT);
            $student_no = trim($sheet->getCell("A".$row)->getValue());
            $student_name = trim($sheet->getCell("B".$row)->getValue());
            $name1 = trim($sheet->getCell("C".$row)->getValue());
            $entry_time = trim($sheet->getCell("D".$row)->getValue());
            $partners = trim($sheet->getCell("E".$row)->getValue());
            $receive_amount = trim($sheet->getCell("F".$row)->getValue());
            $entry_time = intval(($entry_time - 25569) * 3600 * 24);
            $entry_time = strtotime(gmdate('Y-m-d',$entry_time));
            //$loginName = trim($sheet->getCell("A".$row)->getValue());
            //$userPhone = trim($sheet->getCell("F".$row)->getValue());
            //$userPhone = substr($userPhone,0,11);
            //$name = trim($sheet->getCell("D".$row)->getValue());
            //P967925（5）、R090669（6） 替换成手机号
            //if(strpos($loginName,'（') !== FALSE)
            //{
            //    $loginName=$userPhone;   
            //}
            //if(empty($loginName)) $loginName=$userPhone;
            //if(empty($loginName)) $loginName=$name;
            //if(empty($loginName)) continue;
            //$loginName = $loginName;
            //$has_data[$loginName][] = 1;
            //$key = $loginName;
            //$student_no = trim($sheet->getCell("B".$row)->getValue());
            //$pre_entry_no = trim($sheet->getCell("C".$row)->getValue());
            //学号与预报名处理
            //if(empty($key))
            //{
                //$key = $pre_entry_no;   
            //}
            //$data['student_no'] = $student_no;
            //$data['trueName'] = $data['nickName'] = $trueName;
            //$data['createtime'] = $createtime;
            //$data['pre_entry_no'] = $pre_entry_no;
            
            
            $result_user = model('Users')->where(['trueName'=>$student_name,'dataFlag'=>1])->value('userId');
            //dump($result_user);die;
            if( empty($result_user) )
            {
                //@file_put_contents('./log_users.log',date('Y-m-d H:i:s').'userId:'.$result_user['userId'].' - name:'.$name.' - loginName:'.$loginName.chr(10),FILE_APPEND);
                
                add_logs('import_users','userId:'.'还没有'.' - student_no:'.$student_no.' - trueName:'.$trueName);
                    continue;
                
                
            }
            if(!empty($result_user)){
                $data['userId'] = $result_user;
                $data['student_no'] = $student_no;
                $data['receive_amount'] = $receive_amount;
                $data['student_name'] = $student_name;
                $data['name1'] = $name1;
                $data['entry_time'] = $entry_time;
                $data['partners'] = $partners;
                $data['pay_type'] = 5;   //导入数据默认 线下收款-现金
                $data['createtime'] = time();
                $data['lastmodify'] = time();
                $data['data_type'] = 1;
                $data['batch_num'] = 1;  //第一批导入数据
            }
            //$data['trueName'] = $data['nickName'] = $name;
            //$student_type = trim($sheet->getCell("E".$row)->getValue());
            //$data['student_type'] = $studentTypeArr[$student_type];
            //$study_status = trim($sheet->getCell("J".$row)->getValue());
            //if(!in_array($study_status,array_values($studyStatusArr))) $study_status='';
            //if(strpos($study_status,'申请')!==FALSE) $study_status='申请毕业';
            //if(strpos($study_status,'在读')!==FALSE) $study_status='在读';
            //$data['study_status'] = $studyStatusArr[$study_status];
            //$data['userPhone'] = $userPhone;
            //$userEmail = trim($sheet->getCell("G".$row)->getValue());
            //$data['userEmail'] = $userEmail&&strpos($userEmail,'@')===FALSE?$userEmail.'@qq.com':$userEmail;
            //$userSex = trim($sheet->getCell("H".$row)->getValue());
            //$data['userSex'] = $sexArr[$userSex];
            //$data['loginSecret'] = rand(1000,9999);
            //$data['loginPwd'] = md5($loginPwd.$data['loginSecret']);
            //$data['userStatus'] = 1;
            //$createtime = trim($sheet->getCell("Q".$row)->getValue());
            //$createtime = intval(($createtime - 25569) * 3600 * 24);
            //$createtime = strtotime(gmdate('Y-m-d',$createtime));
            //$data['createtime'] = $createtime?$createtime:time();
            
            //$data['is_import'] = 1;
            //$data['import_time'] = time();
            //$data['key'] = $key;
            $readData[] = $data;
            $importNum++;
        }
        //dump($readData);
        die;
        //unset($readData);
        //dump($readData);exit;
        //$objReader->setActiveSheetIndex(1);
        //$sheet = $objReader->getActiveSheet();
        //$rows = $sheet->getHighestRow();
        //$cells = $sheet->getHighestColumn();
        //$readData1 = [];
        //$importNum = 0;
        //for ($row = 1; $row <= $rows; $row++){
            //$data = [];
            //$key = trim($sheet->getCell("A".$row)->getValue());
            //$data['pre_entry_no'] = trim($sheet->getCell("B".$row)->getValue());
            //学号与预报名处理
            //if(empty($key))
            //{
            //    continue;
                //$key = $data['pre_entry_no'];   
            //}
            //$student_type = trim($sheet->getCell("C".$row)->getValue());
            //$data['student_type'] = !empty($studentTypeArr[$student_type])?$studentTypeArr[$student_type]:'';
            
            //$study_status = trim($sheet->getCell("D".$row)->getValue());
            //$data['study_status'] = !empty($studyStatusArr[$study_status])?$studyStatusArr[$study_status]:'';
            
            //$student_pay_type = trim($sheet->getCell("G".$row)->getValue());
            //$data['student_pay_type'] = !empty($studentPayTypeArr[$student_pay_type])?$studentPayTypeArr[$student_pay_type]:'';
            
            //$study_type = trim($sheet->getCell("H".$row)->getValue());
            //$data['study_type'] = !empty($studyTypeArr[$study_type])?$studyTypeArr[$study_type]:'';
            //$data['idcard_no'] = trim($sheet->getCell("E".$row)->getValue());
            //$data['customer_source'] = trim($sheet->getCell("N".$row)->getValue());
            //$data['createtime'] = $data['lastmodify'] = time();
            //$readData1[$key] = $data;
            //$readData1[] = $data;
            //$importNum++;
        //}
        /*
        dump($readData1);exit;
        
        //Db::startTrans();
        $i = 1;
        $userData = [];
        $login_key = array();
        foreach($readData as $k=>$v)
        {
             $key = $v['key'];
             unset($v['key']);
             $userData[] = $v;
             $login_key[$v['loginName']] = $key;
             $i++;
        }
        */
        #dump($userData);exit;
        if(count($readData)>0){
            $list = model('student_fee_log')->saveAll($readData);
            //dump($list);die;
            //$extData = [];
            //dump($merge_data);die;
            //foreach ($list as $key =>$v){
                //$oldkey = $login_key[$v['loginName']];
                //$merge_data = !empty($readData1[$oldkey])?$readData1[$oldkey]:['idcard_no'=>'','customer_source'=>'','createtime'=>time(),'lastmodify'=>time()];
                //$extData[] = array_merge(['userId'=>$v['userId']],$merge_data);
                //$extData[]['userId'] = $v['userId']; 
                //$extData[]['idcard_no'] = ''; 
                //$extData[]['customer_source'] = ''; 
                //$extData[]['createtime'] = time(); 
                //$extData[]['lastmodify'] = time(); 
                //$extData[] = array_merge($extData,$merge_data);
            //}
            //dump($extData);die;
            //foreach($extData as &$v){
            //    $v['idcard_no'] = '';
            //    $v['customer_source'] = '';
            //    $v['createtime'] = time();
            //    $v['lastmodify'] = time();
            //}
            //dump($extData);die;
            //if(count($extData)>0)Db::name('student_extend')->insertAll($extData);
            //Db::commit();
        }
        echo 'Run: fee_log('.count($list).') datas';exit;
    }
    public function second_array_unique_bykey($arr,$key){  //二维数组处理
        $tmp_arr = [];  
        foreach($arr as $k => $v){ 
            if(in_array($v[$key], $tmp_arr)){   //搜索$v[$key]是否在$tmp_arr数组中存在，若存在返回true   
                unset($arr[$k]); //销毁一个变量  如果$tmp_arr中已存在相同的值就删除该值  
            }else{  
                $tmp_arr[$k] = $v[$key];  //将不同的值放在该数组中保存  
            }  
        }  
        //ksort($arr); //ksort函数对数组进行排序(保留原键值key)  sort为不保留key值  
        return $arr;  
    }
    public function importInfo(){     //导入学员数据
        set_time_limit(0);
        Loader::import('phpexcel.PHPExcel.IOFactory');
        $filename = '/info.xlsx';
        $num = isset($_GET['num'])&&!empty($_GET['num'])?$_GET['num']:1;
        //$filename = "/xlsx/{$num}.xlsx";
        $objReader = \PHPExcel_IOFactory::load(MBISRootPath().$filename);
        //dump($objReader);die;
        $objReader->setActiveSheetIndex(0);
        $sheet = $objReader->getActiveSheet();
        $rows = $sheet->getHighestRow();
        $cells = $sheet->getHighestColumn();
        $readData = [];
        $importNum = 0;
        
        $loginPwd = 'its123456';
        //$has_data = array();
        for ($row = 2; $row <= $rows; $row++){
            $data = [];
            //$sheet->setCellValueExplicit("D".$row, $sheet->getCell("D".$row)->getValue(), \PHPExcel_Cell_DataType::TYPE_STRING2);
            //$sheet->getStyle('A')->getNumberFormat()
        //->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2);
            //$sheet->getStyle("A".$row)->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_TEXT);
            $student_no = trim($sheet->getCell("C".$row)->getValue());
            $trueName = trim($sheet->getCell("D".$row)->getValue());
            $userPhone = trim($sheet->getCell("G".$row)->getValue());
            $userQQ = trim($sheet->getCell("J".$row)->getValue());
            $idcard = trim($sheet->getCell("K".$row)->getValue());
            //$createtime = intval(($createtime - 25569) * 3600 * 24);
            //$createtime = strtotime(gmdate('Y-m-d',$createtime));
            //$createtime = gmdate('Y-m-d',$createtime);
            //dump($createtime);die;
            !empty($idcard) ? $loginName = $idcard : $loginName = $userPhone; 
            //$loginName = trim($sheet->getCell("A".$row)->getValue());
            //$userPhone = trim($sheet->getCell("F".$row)->getValue());
            //$userPhone = substr($userPhone,0,11);
            //$name = trim($sheet->getCell("D".$row)->getValue());
            //P967925（5）、R090669（6） 替换成手机号
            //if(strpos($loginName,'（') !== FALSE)
            //{
            //    $loginName=$userPhone;   
            //}
            //if(empty($loginName)) $loginName=$userPhone;
            //if(empty($loginName)) $loginName=$name;
            //if(empty($loginName)) continue;
            //$loginName = $loginName;
            //$has_data[$loginName][] = 1;
            //$key = $loginName;
            //$student_no = trim($sheet->getCell("B".$row)->getValue());
            //$pre_entry_no = trim($sheet->getCell("C".$row)->getValue());
            //学号与预报名处理
            //if(empty($key))
            //{
                //$key = $pre_entry_no;   
            //}
            
            $result_user = model('Users')->where(['loginName'=>$loginName,'dataFlag'=>1])->find();
            if( !empty($result_user) )
            {
                //@file_put_contents('./log_users.log',date('Y-m-d H:i:s').'userId:'.$result_user['userId'].' - name:'.$name.' - loginName:'.$loginName.chr(10),FILE_APPEND);
                add_logs('import_users','userId:'.$result_user['userId'].' - loginName:'.$result_user['loginName'].' - trueName:'.$result_user['trueName'].' - trueName_out:'.$trueName.' - loginName_out:'.$loginName);
                    continue;
            }


            $data['student_no'] = $student_no;
            $data['trueName'] = $data['nickName'] = $trueName;
            $data['createtime'] = time();
            //$data['pre_entry_no'] = $pre_entry_no;
            $data['loginName'] = $loginName;
            $data['userPhone'] = $userPhone;
            $data['userQQ'] = $userQQ;
            //if(isset($has_data[$loginName]) && count($has_data[$loginName])>1)
            //{
            //   continue;   
            //}
            //$data['trueName'] = $data['nickName'] = $name;
            //$student_type = trim($sheet->getCell("E".$row)->getValue());
            //$data['student_type'] = $studentTypeArr[$student_type];
            //$study_status = trim($sheet->getCell("J".$row)->getValue());
            //if(!in_array($study_status,array_values($studyStatusArr))) $study_status='';
            //if(strpos($study_status,'申请')!==FALSE) $study_status='申请毕业';
            //if(strpos($study_status,'在读')!==FALSE) $study_status='在读';
            //$data['study_status'] = $studyStatusArr[$study_status];
            //$data['userPhone'] = $userPhone;
            //$userEmail = trim($sheet->getCell("G".$row)->getValue());
            //$data['userEmail'] = $userEmail&&strpos($userEmail,'@')===FALSE?$userEmail.'@qq.com':$userEmail;
            //$userSex = trim($sheet->getCell("H".$row)->getValue());
            //$data['userSex'] = $sexArr[$userSex];
            $data['loginSecret'] = rand(1000,9999);
            $data['loginPwd'] = md5($loginPwd.$data['loginSecret']);
            //$data['userStatus'] = 1;
            //$createtime = trim($sheet->getCell("Q".$row)->getValue());
            //$createtime = intval(($createtime - 25569) * 3600 * 24);
            //$createtime = strtotime(gmdate('Y-m-d',$createtime));
            //$data['createtime'] = $createtime?$createtime:time();
            $data['lastmodify'] = time();
            $data['is_import'] = 1;
            $data['import_time'] = time();
            $data['data_type'] = 1;
            $data['batch_num'] = 2;
            //$data['key'] = $key;
            $readData[] = $data;
            $importNum++;
        }
        //unset($readData);
        //dump($readData);exit;
        $extData = [];
        if(count($readData)>0){
            $list = model('Users')->saveAll($readData);
            //dump($list);die;
            
            //dump($merge_data);die;
            foreach ($list as $key =>$v){
                //$oldkey = $login_key[$v['loginName']];
                //$merge_data = !empty($readData1[$oldkey])?$readData1[$oldkey]:['idcard_no'=>'','customer_source'=>'','createtime'=>time(),'lastmodify'=>time()];
                //$extData[] = array_merge(['userId'=>$v['userId']],$merge_data);
                $extData[]['userId'] = $v['userId']; 
                //$extData[]['idcard_no'] = ''; 
                //$extData[]['customer_source'] = ''; 
                //$extData[]['createtime'] = time(); 
                //$extData[]['lastmodify'] = time(); 
                //$extData[] = array_merge($extData,$merge_data);
            }
            //dump($extData);die;
            foreach($extData as &$v){
                $v['idcard_no'] = '';
                $v['customer_source'] = '';
                $v['createtime'] = time();
                $v['lastmodify'] = time();
            }
            //dump($extData);die;
            if(count($extData)>0)Db::name('student_extend')->insertAll($extData);
            //Db::commit();
        }
        echo 'Run : users('.count($readData).'), extend('.count($extData).') datas';
    }
    public function importExams(){     //导入学员成绩
        set_time_limit(0);
        Loader::import('phpexcel.PHPExcel.IOFactory');
        $filename = '/info24.xlsx'; 
        $num = isset($_GET['num'])&&!empty($_GET['num'])?$_GET['num']:1;
        //$filename = "/xlsx/{$num}.xlsx";
        $objReader = \PHPExcel_IOFactory::load(MBISRootPath().$filename);
        //dump($objReader);die;
        $objReader->setActiveSheetIndex(0);
        $sheet = $objReader->getActiveSheet();
        $rows = $sheet->getHighestRow();
        $cells = $sheet->getHighestColumn();
        $readData = [];
        $importNum = 0;

        $subordinateArr = [
            '龙岗' => 1,
            '宝安' => 2,
            '福田' => 3,
            '南山' => 4,
            '深大' => 5,
        ];

        $study_sessionArr = [
            '201101' =>'2011级1月',
            '201104' =>'2011级4月',
            '201107' =>'2011级7月',
            '201110' =>'2011级10月',

            '201201' =>'2012级1月',
            '201204' =>'2012级4月',
            '201207' =>'2012级7月',
            '201210' =>'2012级10月',

            '201301' =>'2013级1月',
            '201304' =>'2013级4月',
            '201307' =>'2013级7月',
            '201310' =>'2013级10月',

            '201401' =>'2014级1月',
            '201404' =>'2014级4月',
            '201407' =>'2014级7月',
            '201410' =>'2014级10月',

            '201501' =>'2015级1月',
            '201504' =>'2015级4月',
            '201507' =>'2015级7月',
            '201510' =>'2015级10月',

            '201601' =>'2016级1月',
            '201604' =>'2016级4月',
            '201607' =>'2016级7月',
            '201610' =>'2016级10月',

            '201701' =>'2017级1月',
            '201704' =>'2017级4月',
            '201707' =>'2017级7月',
            '201710' =>'2017级10月',
        ];

        for ($row = 2; $row <= $rows; $row++){
            $data = [];
            $study_session = trim($sheet->getCell("B".$row)->getValue());    //学习届数(年级)
            $student_no = trim($sheet->getCell("E".$row)->getValue());  //学员报名编号\预报名编号
            $name = trim($sheet->getCell("F".$row)->getValue());  //姓名
            $exam_no = trim($sheet->getCell("G".$row)->getValue());   //准考证号
            $login_pass = trim($sheet->getCell("H".$row)->getValue());  //查询成绩登录密码
            //$userPhone = trim($sheet->getCell("G".$row)->getValue());   //手机号码
            //$mobile2 = trim($sheet->getCell("H".$row)->getValue());   //第二手机号码
            //$urgency_contact = trim($sheet->getCell("I".$row)->getValue());  //紧急联系人
            //$urgency_contact_mobile = '';                                   //紧急联系人电话
            //$userQQ = trim($sheet->getCell("J".$row)->getValue());   //学员QQ
            $idcard = trim($sheet->getCell("K".$row)->getValue());   //身份证号
            $subordinate = trim($sheet->getCell("L".$row)->getValue());  //所属
            $subordinate = $subordinateArr[$subordinate];
            $study_session = $study_sessionArr[$study_session];

            $subject1 = trim($sheet->getCell("M".$row)->getValue());
            $subject2 = trim($sheet->getCell("N".$row)->getValue());
            $subject3 = trim($sheet->getCell("O".$row)->getValue());
            $subject4 = trim($sheet->getCell("P".$row)->getValue());
            $subject5 = trim($sheet->getCell("Q".$row)->getValue());
            $subject6 = trim($sheet->getCell("R".$row)->getValue());
            $subject7 = trim($sheet->getCell("S".$row)->getValue());
            $subject8 = trim($sheet->getCell("T".$row)->getValue());
            $subject9 = trim($sheet->getCell("U".$row)->getValue());
            $subject10 = trim($sheet->getCell("V".$row)->getValue());
            $subject11 = trim($sheet->getCell("W".$row)->getValue());
            $subject12 = trim($sheet->getCell("X".$row)->getValue());
            $subject13 = trim($sheet->getCell("Y".$row)->getValue());
            $subject14 = trim($sheet->getCell("Z".$row)->getValue());
            $subject15 = trim($sheet->getCell("AA".$row)->getValue());
            $subject16 = trim($sheet->getCell("AB".$row)->getValue());
            $subject17 = trim($sheet->getCell("AC".$row)->getValue());
            $subject18 = trim($sheet->getCell("AD".$row)->getValue());
            $subject19 = trim($sheet->getCell("AE".$row)->getValue());
            $subject20 = trim($sheet->getCell("AF".$row)->getValue());
            $subject21 = trim($sheet->getCell("AG".$row)->getValue());
            $subject22 = trim($sheet->getCell("AH".$row)->getValue());
            $subject23 = trim($sheet->getCell("AI".$row)->getValue());
            $subject24 = trim($sheet->getCell("AJ".$row)->getValue());
            if(!empty($name)){  //查userId
                $uid = model('Users')->where('trueName',$name)->value('userId');
            }
            if(false == $uid){
                add_logs('import_sj_exams_info','不存在:name:'.$name.' - idcard:'.$idcard);
                continue;
            }

            if($study_session){  //查course_id
                $where['name'] = ['=',"$study_session"];
                $where['major_id'] = ['=',"38"];
                $grade_id = Db::name('grade')->where($where)->value('grade_id');
                //dump($grade_id);die;
                $course_id = Db::name('course')->where('grade_id',$grade_id)->value('course_id');
            }
            $data['examination_type'] = 1;  //学历
            $data['school_id'] = 12;  //华师大
            $data['major_id'] = 38;  //艺术设计(平面设计方向)
            $data['userId'] = $uid;
            $data['course_id'] = $course_id;
            $data['study_session'] = $study_session;
            $data['student_no'] = $student_no;
            $data['name'] = $name;
            //$data['createtime'] = time();
            //$data['userPhone'] = $userPhone;
            $data['login_pass'] = $login_pass;
            //$data['userQQ'] = $userQQ;
            $data['idcard_no'] = $idcard;
            $data['exam_no'] = $exam_no;
            $data['subordinate'] = $subordinate;
            $data['remarks'] = $subject22.','.$subject23.','.$subject24;
            $data['data_type'] = 1;  //导入数据
            $data['batch_num'] = 2;  //第一批

            $data['subject']['221'] = $subject1;   //科目成绩
            $data['subject']['222'] = $subject2; 
            $data['subject']['227'] = $subject3; 
            $data['subject']['241'] = $subject4; 
            $data['subject']['235'] = $subject5; 
            $data['subject']['236'] = $subject6; 
            $data['subject']['223'] = $subject7; 
            $data['subject']['224'] = $subject8; 
            $data['subject']['225'] = $subject9; 
            $data['subject']['226'] = $subject10; 
            $data['subject']['228'] = $subject11; 
            $data['subject']['229'] = $subject12; 
            $data['subject']['230'] = $subject13; 
            $data['subject']['231'] = $subject14; 
            $data['subject']['232'] = $subject15; 
            $data['subject']['233'] = $subject16; 
            $data['subject']['234'] = $subject17; 
            $data['subject']['237'] = $subject18; 
            $data['subject']['238'] = $subject19; 
            $data['subject']['239'] = $subject20; 
            $data['subject']['240'] = $subject21; 
            $readData[] = $data;
            $importNum++;
        }
        if(count($readData)>0){
            $subject = [];
            foreach($readData as $k=>$v){
                foreach($v['subject'] as $key=>$val){
                    if($val == ''){
                        #unset($v['subject'][$key]);
                    }else{
                        $subject[$k][$key] =  array( $key => $val);
                        foreach ($v as $k1 => $v1) {
                            if($k1 != 'subject' ){
                              $subject[$k][$key][$k1] = $v1;
                            }
                        }
                    }
                }
            }
            $info = [];
            foreach($subject as $k=>$v){
                foreach($v as $k2=>$v2){
                    $info[] = $v2;
                }
            }
            foreach($info as $k=>&$v){
                $v['subject_id'] = key($v);
                $v['subject_score'] = reset($v);
                array_shift($v);
            }
            die;
            //dd($info);
            $list = model('sj_exams')->insertAll($info);
            //dump($list);die;
            //$extData = [];
            //dump($merge_data);die;
            //foreach ($list as $key =>$v){
                //$oldkey = $login_key[$v['loginName']];
                //$merge_data = !empty($readData1[$oldkey])?$readData1[$oldkey]:['idcard_no'=>'','customer_source'=>'','createtime'=>time(),'lastmodify'=>time()];
                //$extData[] = array_merge(['userId'=>$v['userId']],$merge_data);
                //$extData[]['userId'] = $v['userId']; 
                //$extData[]['idcard_no'] = ''; 
                //$extData[]['customer_source'] = ''; 
                //$extData[]['createtime'] = time(); 
                //$extData[]['lastmodify'] = time(); 
                //$extData[] = array_merge($extData,$merge_data);
            //}
            //dump($extData);die;
            // foreach($extData as &$v){
            //     $v['idcard_no'] = '';
            //     $v['customer_source'] = '';
            //     $v['createtime'] = time();
            //     $v['lastmodify'] = time();
            // }
            //dump($extData);die;
            //if(count($list)>0)Db::name('student_extend')->insertAll($extData);
            //Db::commit();
        }
        echo 'Run : sj_exams('.count($info).') datas';
    }
    
    /**
	 * 导入缴费数据
	 */
	public function importFees($filename = ''){
        set_time_limit(0);
        ini_set("memory_limit","512M");
		Loader::import('phpexcel.PHPExcel.IOFactory');
        $filename = './data/bm-total.xlsx';
        $num = isset($_GET['num'])&&!empty($_GET['num'])?$_GET['num']:1;
        //$filename = "/xlsx/{$num}.xlsx";
        //MBISRootPath().
		$objReader = \PHPExcel_IOFactory::load($filename);
		$objReader->setActiveSheetIndex(0);
		$sheet = $objReader->getActiveSheet();
		$rows = $sheet->getHighestRow();
		$cells = $sheet->getHighestColumn();
        //dump($rows);exit;
        $max_rows = 200;
        if($rows-1>$max_rows){exit("每次导入数据最多{$max_rows}条<a href=\"javascript:history.back()\">返回</a>");}
        $readData = [];
        $importNum = 0;
        //性别
        $sexArr = [
            '男' => 1,
            '女' => 2,
            '无' => 0,
            '' => 0,
        ];
        //学员类型：1为技能、2为学历、3为技能学历
        $studentTypeArr = [
            '技能' => 1,
            '学历' => 2,
            '技能学历' => 3,
            '' => 1,
        ];
        //学习状态：1为在读、2为毕业、3为过期、4为弃学、5为休学、6为退学
        $studyStatusArr = [
            '在读' => 1,
            '毕业' => 2,
            '过期' => 3,
            '弃学' => 4,
            '休学' => 5,
            '退学' => 6,
            //'未确认' => 7,
            //'申请毕业' => 8,
            //'' => 7,
        ];
        //付费类型：0为未付费、1为全额、2为分期、3为定金
        $studentPayTypeArr = [
            '未付费' => 0,
            '全款' => 1,
            '分期' => 2,
            '定金' => 3,
            '' => 9,
        ];
        //学习形式：1为成考、2为华师大、3为深大、4为网教
        $studyTypeArr = [
            '成考' => 1,
            '华师大' => 2,
            '深大' => 3,
            '网教' => 4,
            '' => 99,
        ];
        $loginPwd = 'its123456';
        $has_data = array();
        $has_userdata = array();
        
        //payments
        //
        
        for ($row = 2; $row <= $rows; $row++){
            $data = [];
            $sheet->setCellValueExplicit("C".$row, $sheet->getCell("C".$row)->getValue(), \PHPExcel_Cell_DataType::TYPE_STRING2);
            //$sheet->getStyle("A".$row)->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_TEXT);
            $loginName = trim($sheet->getCell("C".$row)->getValue());
            $userPhone = trim($sheet->getCell("D".$row)->getValue());
            $userPhone = substr($userPhone,0,11);
            $name = trim($sheet->getCell("B".$row)->getValue());
            //P967925（5）、R090669（6） 替换成手机号
            if(strpos($loginName,'（') !== FALSE)
            {
                //$loginName=$userPhone;   
            }
            //if(empty($loginName)) $loginName=$userPhone;
            //if(empty($loginName)) $loginName=$name;
            if(empty($loginName)) {
                //$has_userdata[] = array($name,$loginName);
                continue;
            }
            $has_data[$loginName][] = 1;
            $key = $loginName;
            $student_no = trim($sheet->getCell("A".$row)->getValue());
            $feeCate = trim($sheet->getCell("D".$row)->getValue());
            $feeType = trim($sheet->getCell("E".$row)->getValue());
            $schoolName = trim($sheet->getCell("F".$row)->getValue());
            $levelName = trim($sheet->getCell("G".$row)->getValue());
            //报读专业
            $majorName = trim($sheet->getCell("H".$row)->getValue());
            //学习形式
            $studyType = trim($sheet->getCell("I".$row)->getValue());
            //课程编码
            $courseBn = trim($sheet->getCell("J".$row)->getValue());
            //课程名称
            $courseName = trim($sheet->getCell("K".$row)->getValue());
            //标准学费
            $courseFee = trim($sheet->getCell("L".$row)->getValue());
            //折前减免
            $discount1 = trim($sheet->getCell("M".$row)->getValue());
            //付款方式折扣优惠
            $discount2 = trim($sheet->getCell("N".$row)->getValue());
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
            $discount8 = trim($sheet->getCell("T".$row)->getValue());
            //应收学费总额
            $receFee = trim($sheet->getCell("U".$row)->getValue());
            //累计已收学费总额
            $totalFee = trim($sheet->getCell("V".$row)->getValue());
            //待收学费总额
            $waitFee = trim($sheet->getCell("W".$row)->getValue());
            //是否欠费
            $isArre = trim($sheet->getCell("X".$row)->getValue());
            $result_user = model('Users')->where(['loginName'=>$loginName,'dataFlag'=>1])->find();
            if(!empty($result_user)) $userId = $result_user['userId'];
            if(empty($userId)) continue;
            //student_edu
        $student_edu_data = array(
'userId' => $userId,
'agent_uid' => 0,
'odd_id' => 0,
'orderId' => 0,
'orderNo' => 0,
'school_id' => 0,
'school_name' => $schoolName,
'major_id' => 0,
'major_name' => $majorName,
'course_id' => 0,
'level_id' => 0,
'course_name' => $courseName,
'grade_id' => 0,
'grade_name' => '',
'course_bn' => $courseBn,
'exam_no' => '',
'login_pass' => '',
'login_url' => '',
'info_source' => '',
'school_code' => '',
'receivable_fee' => 0,
'real_fee' => 0,
'arrearage_fee' => 0,
'remark' => '',
'invoice_no' => '',
'fee_content' => '',
'student_cert' => '',
'complete_cert' => '',
'job_content' => '',
'class_ending' => '',
'delivery_info' => '',
'notify_school_info' => '',
'feedback_content' => '',
'is_school_sms' => 0,
'extend_data' => '',
'entry_time' => time(),
'data_type' => 1,
'batch_num' => 3,
'exam_type' => 0,
        );
        Db::name('student_edu')->insert($student_edu_data);
            //学员编号	学员姓名	身份证号	收款类别	报考类型	报读院校	层次	报读专业	学习形式	课程编码	课程名称	标准学费	折前减免	付款方式折扣优惠	科目累计折扣优惠	团报折扣优惠	校长特权优惠	活动折扣优惠	特殊折扣优惠额	折后减免	应收学费总额	累计已收学费总额	待收学费总额	是否欠费		
            
        //收款校区	学员编号	学员姓名	身份证号	收款类别	报考类型	报读院校	层次	报读专业	学习形式	课程编码	课程名称	收款金额	收款日期	收据号码	缴费类型	缴费方式	手续费率	手续费	实际到账金额	银行账户
        //收款校区
        $receiptSchool = trim($sheet->getCell("A".$row)->getValue());
        //收款类别
        $receiptCate = trim($sheet->getCell("E".$row)->getValue());
        //报考类型
        $examType = trim($sheet->getCell("F".$row)->getValue());
        //报读院校
        $examSchool = trim($sheet->getCell("G".$row)->getValue());
        //收款金额
        $receiptPrice = trim($sheet->getCell("M".$row)->getValue());
        //收款日期
        $receiptDate = trim($sheet->getCell("N".$row)->getValue());
        //收据号码
        $receiptNo = trim($sheet->getCell("O".$row)->getValue());
        //缴费类型
        $payType = trim($sheet->getCell("P".$row)->getValue());
        //缴费方式
        $payWay = trim($sheet->getCell("Q".$row)->getValue());
        //手续费率
        $procRate = trim($sheet->getCell("R".$row)->getValue());
        //手续费
        $procFee = trim($sheet->getCell("S".$row)->getValue());
        //实际到账金额
        $realPayFee = trim($sheet->getCell("T".$row)->getValue());
        //银行账户
        $bankAccount = trim($sheet->getCell("U".$row)->getValue());
        
            //$pre_entry_no = trim($sheet->getCell("C".$row)->getValue());
            //学号与预报名处理
            if(empty($key))
            {
                //$key = $pre_entry_no;   
            }
            $pre_entry_no = '';
            $data['student_no'] = $student_no;
            $data['pre_entry_no'] = $pre_entry_no;
            
            $data['loginName'] = $loginName;
            $result_user = model('Users')->where(['loginName'=>$loginName,'dataFlag'=>1])->find();
            if( !empty($result_user) )
            {
                //@file_put_contents('./log_users.log',date('Y-m-d H:i:s').'userId:'.$result_user['userId'].' - name:'.$name.' - loginName:'.$loginName.chr(10),FILE_APPEND);
                
                add_logs('import_users','userId:'.$result_user['userId'].' - name:'.$name.' - loginName:'.$loginName);
                $has_userdata[] = array('name'=>$name,'idcard'=>$loginName);
                continue;
            }
            if(isset($has_data[$loginName]) && count($has_data[$loginName])>1)
            {
                continue;   
            }
            $data['trueName'] = $data['nickName'] = $name;
            $student_type = trim($sheet->getCell("F".$row)->getValue());
            $data['student_type'] = $studentTypeArr[$student_type];
            $study_status = trim($sheet->getCell("G".$row)->getValue());
            empty($study_status) && $study_status='在读';
            //if(!in_array($study_status,array_values($studyStatusArr))) $study_status='';
            //if(strpos($study_status,'申请')!==FALSE) $study_status='申请毕业';
            //if(strpos($study_status,'在读')!==FALSE) $study_status='在读';
            $data['study_status'] = $studyStatusArr[$study_status];
            $data['userPhone'] = $userPhone;
            $userEmail = trim($sheet->getCell("J".$row)->getValue());
            $data['userEmail'] = $userEmail&&strpos($userEmail,'@')===FALSE?$userEmail.'@qq.com':$userEmail;
            $userSex = trim($sheet->getCell("K".$row)->getValue());
            $data['userSex'] = $sexArr[$userSex];
            $data['loginSecret'] = rand(1000,9999);
            $data['loginPwd'] = md5($loginPwd.$data['loginSecret']);
            $data['userStatus'] = 1;
            //$createtime = trim($sheet->getCell("Q".$row)->getValue());
            //$createtime = intval(($createtime - 25569) * 3600 * 24);
			//$createtime = strtotime(gmdate('Y-m-d',$createtime));
            $createtime = 0;
            $data['createtime'] = $createtime?$createtime:time();
            $data['idcard'] = $loginName;
            $data['lastmodify'] = time();
            $data['is_import'] = 1;
            $data['data_type'] = 1;
            $data['batch_num'] = 3;
            $data['import_time'] = time();
            //$data['key'] = $key;
            $readData[] = $data;
            $importNum++;
        }
        dump($readData);exit;
        //Db::startTrans();
        $i = 1;
        if(count($readData)>0){
            $list = model('Users')->saveAll($readData);
            $extData = [];
            foreach ($list as $key =>$v){
                $extData[] = ['userId'=>$v['userId'],'idcard_no'=>'','customer_source'=>'','createtime'=>time(),'lastmodify'=>time()];
            }
            if(count($extData)>0)Db::name('student_extend')->insertAll($extData);
            //Db::commit();
        }
        return array(
            'finish_import_num'=>count($readData),
            'nofinish_import_num'=>count($has_userdata),
            'repeat_data'=>$has_userdata
        );
   }
   
   public function importFees2016($filename = ''){
        set_time_limit(0);
        ini_set("memory_limit","512M");
		Loader::import('phpexcel.PHPExcel.IOFactory');
        //$filename = './data/bm-total.xlsx';
        $num = isset($_GET['num'])&&!empty($_GET['num'])?$_GET['num']:1;
        //$filename = "/xlsx/{$num}.xlsx";
        //MBISRootPath().
		$objReader = \PHPExcel_IOFactory::load($filename);
		$objReader->setActiveSheetIndex(0);
		$sheet = $objReader->getActiveSheet();
		$rows = $sheet->getHighestRow();
		$cells = $sheet->getHighestColumn();
        //$max_rows = 200;
        //if($rows-1>$max_rows){exit("每次导入数据最多{$max_rows}条<a href=\"javascript:history.back()\">返回</a>");}
        $readData = [];
        $has_data = array();
        $has_userdata = array();
        $has_iptdata = array();
        $user_tmpdata = array();
        //没有会员数据
        $no_userdata = array();
        
        for ($row = 2; $row <= $rows; $row++){
            $data = [];
            $sheet->setCellValueExplicit("C".$row, $sheet->getCell("C".$row)->getValue(), \PHPExcel_Cell_DataType::TYPE_STRING2);
            $loginName = trim($sheet->getCell("C".$row)->getValue());
            $userPhone = trim($sheet->getCell("D".$row)->getValue());
            $userPhone = substr($userPhone,0,11);
            $name = trim($sheet->getCell("B".$row)->getValue());
            if(empty($loginName)) {
                //$has_userdata[] = array($name,$loginName);
                continue;
            }
            $key = $loginName;
            $student_no = trim($sheet->getCell("A".$row)->getValue());
            //收款类别
            $receiptCate = trim($sheet->getCell("D".$row)->getValue());
            //报考类型
            $feeType = trim($sheet->getCell("E".$row)->getValue());
            $exam_type = ITSSelItemId('major','exam_type',$feeType);
            $schoolName = trim($sheet->getCell("F".$row)->getValue());
            //层次
            $levelName = trim($sheet->getCell("G".$row)->getValue());
            //报读专业
            $majorName = trim($sheet->getCell("H".$row)->getValue());
            //学习形式
            $studyType = trim($sheet->getCell("I".$row)->getValue());
            //课程编码
            $courseBn = trim($sheet->getCell("J".$row)->getValue());
            //课程名称
            $courseName = trim($sheet->getCell("K".$row)->getValue());
            //标准学费
            $courseFee = trim($sheet->getCell("L".$row)->getValue());
            //折前减免
            $discount1 = trim($sheet->getCell("M".$row)->getValue());
            
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
            $receFee = $courseFee-$discount1;
            //累计已收学费总额
            $totalFee = trim($sheet->getCell("O".$row)->getValue());
            //待收学费总额
            $waitFee = trim($sheet->getCell("P".$row)->getValue());
            //是否欠费
            $isArre = trim($sheet->getCell("Q".$row)->getValue());
            
            $result_user = model('Users')->where(['loginName'=>$loginName,'dataFlag'=>1])->find();
            //var_dump($result_user);
            if(!empty($result_user)) {
                $userId = $result_user['userId'];
            }
            else{
                /*$no_userdata[] = array(
                    'student_no'=>$student_no,
                    'name'=>$name,
                    'idcard'=>$loginName,
                );*/   
            }
            //if(empty($userId)) continue;
            $user_tmpdata[$userId] = $result_user;
            //var_dump($result_user);
            //student_edu
        $student_edu_data = array(
'userId' => $userId,
'agent_uid' => 0,
'odd_id' => 0,
'orderId' => 0,
'orderNo' => 0,
'school_id' => 0,
'school_name' => $schoolName,
'major_id' => 0,
'major_name' => $majorName,
'course_id' => 0,
'level_id' => 0,
'course_name' => $courseName,
'grade_id' => 0,
'grade_name' => '',
'course_bn' => $courseBn,
'exam_no' => '',
'login_pass' => '',
'login_url' => '',
'info_source' => '',
'school_code' => '',
'receivable_fee' => $receFee,
'real_fee' => $receFee,
'arrearage_fee' => 0,
'remark' => '',
'invoice_no' => '',
'fee_content' => '',
'student_cert' => '',
'complete_cert' => '',
'job_content' => '',
'class_ending' => '',
'delivery_info' => '',
'notify_school_info' => '',
'feedback_content' => '',
'is_school_sms' => 0,
'extend_data' => '',
'entry_time' => time(),
'data_type' => 1,
'batch_num' => 3,
'exam_type' => $exam_type,
'studyStatus' => $studyType,
'price' => $courseFee,
'discount_price' => $discount1,
'deal_price' => $receFee,
'total_price' => $totalFee,
'wait_price' => $waitFee,
'arre_type' => $isArre,
'receiptCate' => $receiptCate,
'level_name' => $levelName,
        );
        
            //学员编号	学员姓名	身份证号	收款类别	报考类型	报读院校	层次	报读专业	学习形式	课程编码	课程名称	标准学费	折前减免	付款方式折扣优惠	科目累计折扣优惠	团报折扣优惠	校长特权优惠	活动折扣优惠	特殊折扣优惠额	折后减免	应收学费总额	累计已收学费总额	待收学费总额	是否欠费		
            
        //收款校区	学员编号	学员姓名	身份证号	收款类别	报考类型	报读院校	层次	报读专业	学习形式	课程编码	课程名称	收款金额	收款日期	收据号码	缴费类型	缴费方式	手续费率	手续费	实际到账金额	银行账户
        //收款校区
        /**$receiptSchool = trim($sheet->getCell("A".$row)->getValue());
        //收款类别
        $receiptCate = trim($sheet->getCell("E".$row)->getValue());
        //报考类型
        $examType = trim($sheet->getCell("F".$row)->getValue());
        //报读院校
        $examSchool = trim($sheet->getCell("G".$row)->getValue());
        //收款金额
        $receiptPrice = trim($sheet->getCell("M".$row)->getValue());
        //收款日期
        $receiptDate = trim($sheet->getCell("N".$row)->getValue());
        //收据号码
        $receiptNo = trim($sheet->getCell("O".$row)->getValue());
        //缴费类型
        $payType = trim($sheet->getCell("P".$row)->getValue());
        //缴费方式
        $payWay = trim($sheet->getCell("Q".$row)->getValue());
        //手续费率
        $procRate = trim($sheet->getCell("R".$row)->getValue());
        //手续费
        $procFee = trim($sheet->getCell("S".$row)->getValue());
        //实际到账金额
        $realPayFee = trim($sheet->getCell("T".$row)->getValue());
        //银行账户
        $bankAccount = trim($sheet->getCell("U".$row)->getValue());**/
            
            $result_edu = model('student_edu')->where(['userId'=>$userId,'course_bn'=>$courseBn])->find();
            if( !empty($result_edu) )
            {
                $has_userdata[] = array('course_bn'=>$courseBn,'idcard'=>$loginName,'name'=>$result_user['trueName'],'type'=>1);
                continue;
            }
            $has_iptdata[] = $userId.'__'.$courseBn.'__1';
            //Db::name('student_edu')->insert($student_edu_data);
            $result_edu2 = model('student_edu')->where(['userId'=>$userId,'level_name'=>$levelName])->find();
            if( !empty($result_edu2) )
            {
                $has_userdata[] = array('course_bn'=>$levelName,'idcard'=>$loginName,'name'=>$result_user['trueName'],'type'=>2);
                continue;
            }
            $has_iptdata[] = $userId.'__'.$levelName.'__2';
            
            $readData[] = $student_edu_data;
        }
        //dump($has_iptdata);exit;
        if(!empty($has_iptdata)):
            $unique_arr = array_unique($has_iptdata);
            $diff_arr = array_diff_assoc($has_iptdata,$unique_arr);
            if(!empty($diff_arr)):
                foreach($diff_arr as $v):
                    list($userId,$courseBn,$type) = explode('__',$v);
                    $has_userdata[] = array('course_bn'=>$courseBn,'idcard'=>$user_tmpdata[$userId]['idcard'],'name'=>$user_tmpdata[$userId]['trueName'],'type'=>$type);
                endforeach;
            endif;
        endif;
        if(!empty($has_userdata)):
            return array(
                'finish_import_num'=>0,
                'nofinish_import_num'=>count($has_userdata),
                'repeat_data'=>$has_userdata
            );
        else:
            Db::name('student_edu')->insertAll($readData);
            return array(
                'finish_import_num'=>count($readData),
                'nofinish_import_num'=>count($has_userdata),
                'repeat_data'=>$has_userdata
            );
        endif;
        
        
   }
   
    public function importPayments2016($filename = ''){
        set_time_limit(0);
        ini_set("memory_limit","512M");
		Loader::import('phpexcel.PHPExcel.IOFactory');
        //$filename = './data/bm-detail.xlsx';
		$objReader = \PHPExcel_IOFactory::load($filename);
		$objReader->setActiveSheetIndex(0);
		$sheet = $objReader->getActiveSheet();
		$rows = $sheet->getHighestRow();
		$cells = $sheet->getHighestColumn();
        //dump($rows);exit;
        //$max_rows = 200;
        //if($rows-1>$max_rows){exit("每次导入数据最多{$max_rows}条<a href=\"javascript:history.back()\">返回</a>");}
        $readData = [];
        $has_data = array();
        $has_userdata = array();
        $has_iptdata = array();
        $user_tmpdata = array();
        
        for ($row = 2; $row <= $rows; $row++){
            $data = [];
            $sheet->setCellValueExplicit("D".$row, $sheet->getCell("D".$row)->getValue(), \PHPExcel_Cell_DataType::TYPE_STRING2);
            $loginName = trim($sheet->getCell("D".$row)->getValue());
            $name = trim($sheet->getCell("C".$row)->getValue());
            if(empty($loginName)) {
                //$has_userdata[] = array($name,$loginName);
                continue;
            }
            $key = $loginName;
            $student_no = trim($sheet->getCell("B".$row)->getValue());
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
            $result_user = model('Users')->where(['loginName'=>$loginName,'dataFlag'=>1])->find();
            if(!empty($result_user)) $userId = $result_user['userId'];
            if(empty($userId)) continue;
            $user_tmpdata[$userId] = $result_user;
        //收款校区	学员编号	学员姓名	身份证号	收款类别	报考类型	报读院校	层次	报读专业	学习形式	课程编码	课程名称	收款金额	收款日期	收据号码	缴费类型	缴费方式	手续费率	手续费	实际到账金额	银行账户
        //收款校区
        $receiptSchool = trim($sheet->getCell("A".$row)->getValue());
        //收款类别
        $receiptCate = trim($sheet->getCell("E".$row)->getValue());
        //报考类型
        $examType = trim($sheet->getCell("F".$row)->getValue());
        //层次
        $levelName = trim($sheet->getCell("H".$row)->getValue());
        //报读院校
        $examSchool = trim($sheet->getCell("G".$row)->getValue());
        //课程编码
        $courseBn = trim($sheet->getCell("K".$row)->getValue());
        //收款金额
        $receiptPrice = trim($sheet->getCell("M".$row)->getValue());
        //收款日期
        $receiptDate = trim($sheet->getCell("N".$row)->getValue());
        $receiptDate = intval(($receiptDate - 25569) * 3600 * 24);
		$receiptDate = gmdate('Y-m-d',$receiptDate);
        //收据号码
        $receiptNo = trim($sheet->getCell("O".$row)->getValue());
        //缴费类型
        $payType = trim($sheet->getCell("P".$row)->getValue());
        //缴费方式
        $payWay = trim($sheet->getCell("Q".$row)->getValue());
        //手续费率
        $procRate = trim($sheet->getCell("R".$row)->getValue());
        //手续费
        $procFee = trim($sheet->getCell("S".$row)->getValue());
        //实际到账金额
        $realPayFee = trim($sheet->getCell("T".$row)->getValue());
        //银行账户
        $bankAccount = trim($sheet->getCell("U".$row)->getValue());
        
        $data = array(
            'payment_id' => getPaymentId(),
            'userId' => $userId,
            'type_id' => 1,
            'status' => 'succ',
            't_payed' => time(),
            'pay_name' => $payWay,
            'pay_type' => $payType,
            'money' => $receiptPrice,
            'cur_money' => $receiptPrice,
            'receiptSchool' => $receiptSchool,
            'receiptPrice' => $receiptPrice,
            'receiptDate' => $receiptDate,
            'receiptNo' => $receiptNo,
            'procRate' => $procRate,
            'procFee' => $procFee,
            'realPayFee' => $realPayFee,
            'course_bn' => $courseBn,
            'receiptCate' => $receiptCate,
            'data_type' => 1,
            'batch_num' => 3,
        );
            
            $result_p = Db::name('payments')->where(['receiptNo'=>$receiptNo])->find();
            if( !empty($result_p) )
            {
                $has_userdata[] = array('receiptNo'=>$receiptNo,'idcard'=>$loginName,'name'=>$result_user['trueName']);
                
                continue;
            }
            $has_iptdata[] = $receiptNo;
            //Db::name('payments')->insert($data);
            $readData[] = $data;
        }
        if(!empty($has_iptdata)):
            $unique_arr = array_unique($has_iptdata);
            $diff_arr = array_diff_assoc($has_iptdata,$unique_arr);
            if(!empty($diff_arr)):
                foreach($diff_arr as $v):
                    $receiptNo = $v;
                    $has_userdata[] = array('receiptNo'=>$receiptNo,'idcard'=>$user_tmpdata[$userId]['idcard'],'name'=>$user_tmpdata[$userId]['trueName']);
                endforeach;
            endif;
        endif;
        if(!empty($has_userdata)):
            return array(
                'finish_import_num'=>0,
                'nofinish_import_num'=>count($has_userdata),
                'repeat_data'=>$has_userdata
            );
            //dump($has_userdata);exit;
        else:
            //判断payment_id是否重复
            $payment_ids = array();
            foreach($readData as $v):
               $payment_ids[]= $v['payment_id']; 
            endforeach;
            foreach($readData as $data):
                $result_p2 = Db::name('payments')->where(['payment_id'=>$data['payment_id']])->find();
                if( !empty($result_p2) )
                {
                    $data['payment_id'] = getPaymentId();
                }
                Db::name('payments')->insert($data);
            endforeach;
            //Db::name('payments')->insertAll($readData);
            return array(
                'finish_import_num'=>count($readData),
                'nofinish_import_num'=>count($has_userdata),
                'repeat_data'=>$has_userdata
            );
        endif;
   }
   
   static private $import_data = array();
   public function check_import_data($filename,$ck_table_name='',$ck_fields=array(),$replace_keys=array()){
       if(empty(self::$import_data)):
           self::$import_data = $this->make_import_data($filename,$replace_keys);
       endif;
       $data = self::$import_data;
       //dump($data);exit;
       $no_data = array();
       foreach($data as $v):
           $filter = array();
           $item = array();
           foreach($ck_fields as $field):
               if($field=='exam_type') $v[$field]=ITSSelItemId('major','exam_type',$v[$field]);
               $item[$field] = $v[$field];
               if($field=='bn') continue;
	           $filter[$field] = $v[$field];
           endforeach;
           //dump($filter);exit;
           $result = Db::name($ck_table_name)->where($filter)->select();
           if(empty($result)):
               $no_data[] = $item;
           endif;
       endforeach;
       //dump($no_data);
       return $no_data;
   }
    //导入数据格式化
    public function make_import_data($filename,$replace_keys=array()){
        /*$replace_keys = array(
            'A' => 'student_no',
            'B' => 'name',
            'C' => 'idcard',
            'D' => 'receiptCate',
            'E' => 'exam_type',
            'F' => 'school_name',
            'G' => 'level_name',
            'H' => 'major_name',
            'I' => 'studyStatus',
            'J' => 'course_bn',
            'K' => 'course_name',
            'L' => 'price',
            'M' => 'discount_price',
            'N' => 'deal_price',
            'O' => 'total_price',
            'P' => 'wait_price',
            'Q' => 'arre_type',
        );*/
        set_time_limit(0);
        ini_set("memory_limit","512M");
        Loader::import('phpexcel.PHPExcel.IOFactory');
        //$filename = './data/bm-detail.xlsx';
        $objReader = \PHPExcel_IOFactory::load($filename);
        //$objReader->setActiveSheetIndex(0);
        $readData = $objReader->getActiveSheet()->toArray(null,true,true,true);
        /*$sheet = $objReader->getActiveSheet();
        $rows = $sheet->getHighestRow();
        $cells = $sheet->getHighestColumn();
        $readData = [];*/
        array_shift($readData);
        $return = array();
        $replace_keys = array_flip($replace_keys);
        if(empty($replace_keys)) return $readData;
        foreach($readData as $k=>$v):
           $return[$k]['bn'] = $k+2;
           if(!empty($replace_keys)):
               foreach($replace_keys as $kk=>$vv):
                   $return[$k][$kk] = $v[$vv];
               endforeach;
           endif;
        endforeach;
        //dump($return);
        return $return;
    }
   
   public function show_data($data,$ck_fields=array(),$title='缴费汇总(检测客户)'){
        $html = '<h2>'.$title.'</h2>';
        $html .= '<ol>';
        foreach($data as $v):
           $names = '';
           foreach($ck_fields as $name=>$field):
               if($field=='exam_type') $v[$field]=ITSSelItemName('major','exam_type',$v[$field]);
               $names .= $name.':'.$v[$field].'&nbsp;&nbsp;&nbsp;&nbsp;';
           endforeach;
           $html .= "<li>{$names}</li>"; 
        endforeach;
         $html .= '</ol>';
         $html .= '<p style="text-align:center"><a href="javascript:history.back()">返回</a></p>';
         echo $html;
   }
   //学员编号	学员名称	身份证号
   
   //报考类型	报读院校	层次	报读专业	学习形式	课程编码	课程名称

   
}