<?php
namespace application\admin\model;
use think\Db;
use application\admin\model\StudentEdu as SEdu; //引用报名模型
/**
 * 录取管理
 */
class Studentmatriculate extends Base{

	public function getLevel($type){
		switch($type){
			case 1:return '高升专';
			case 2:return '专升本';
		}
	}

	public function getEntryStatus($status){
		switch($status){
			case 1:return '未报考';
			case 2:return '未录取';
			case 3:return '已录取';
		}
	}

	public function getDisposeStatus($status){
		switch($status){
			case 1:return '未处理';
			case 2:return '已处理';
			default :return '';
		}
	}

	public function getDisposeResult($status){
		switch($status){
			case 1:return '转其他课程';
			case 2:return '转下次';
			case 3:return '退费';
			case 4:return '其他';
			default :return '';
		}
	}

	public function pageQuery(){
		$exam_type = session('examType');
    	$where = [];
    	$school_id = input('get.school_id');
    	$major_id = input('get.major_id');
    	$level_id = input('get.level_id');
    	$grade_id = input('get.grade_id');
    	if($school_id !=''){
    		$where['e.school_id'] = ['=',"$school_id"];
    	}
    	if($major_id !=''){
    		$where['e.major_id'] = ['=',"$major_id"];
    	}
    	if($level_id !=''){
    		$where['e.level_id'] = ['=',"$level_id"];
    	}
    	if($grade_id !=''){
    		$where['e.grade_id'] = ['=',"$grade_id"];
    	}
    	$key = input('get.key');
		$key_value = trim(input('get.key_value'));
		if(($key !== '') && ($key_value !== '')){
			if($key =='trueName'){
				$where['u.trueName'] = ['like',"%$key_value%"];
			}elseif($key == 'student_no'){
				$where['u.student_no'] = ['like',"%$key_value%"];
			}elseif($key == 'userPhone'){
				$where['u.userPhone'] = ['like',"%$key_value%"];
			}elseif($key == 'idcard'){
				$where['u.idcard'] = ['like',"%$key_value%"];
			}
		}

    	$where['m.exam_type'] = ['=',"$exam_type"];

    	$join = [];
    	$join = [
    		['users u','e.userId=u.userId','left'],
  			['major_edu m','e.major_id=m.major_id','left']
    	];
    	$field = 'e.edu_id,e.school_id,e.school_name,e.major_id,e.major_name,e.level_id,e.grade_id,e.grade_name,e.entry_status,e.dispose_status,e.dispose_result,u.trueName,u.idcard,u.student_no,u.userPhone';

    	$page = Db::name('student_edu')
    			->alias('e')
    			->join($join)
    			->where($where)
    			->field($field)
    			//->order('e.lastmodify desc')
				->paginate(input('pagesize/d'))
				->toArray();
		//getLastSql();
		
		if(count($page['Rows'])>0){
			foreach ($page['Rows'] as $key => $v){
                $page['Rows'][$key]['checkbox'] = '<input type="checkbox" id="ck_'.$v['edu_id'].'" name="chk"  value="'.$v['edu_id'].'">';
                $page['Rows'][$key]['level_id'] = $this->getLevel($v['level_id']);
                $page['Rows'][$key]['entry_status'] = $this->getEntryStatus($v['entry_status']);
                $page['Rows'][$key]['dispose_status'] = $this->getDisposeStatus($v['dispose_status']);
                $page['Rows'][$key]['dispose_result'] = $this->getDisposeResult($v['dispose_result']);
			}
		}
		return $page;
	}

	public function getGrade($type){
    	$grade = Db::name('grade')->where('exam_type',$type)->field('grade_id,name')->select();
    	return $grade;
    }

    public function getSchool($type){
    	$where = [];
    	$where['m.exam_type'] = ['=',"$type"];

    	$field = 's.school_id,s.name';

    	$join = [];
    	$join = [
    		['major_edu m','FIND_IN_SET(s.school_id,m.school_ids)','left']
    	];
    	$school = Db::name('school')
    					->alias('s')
    					->join($join)
    					->where($where)
    					->field($field)
    					->group('s.school_id')
    					->select();
    	//dd($school);
    	return $school;
    }

    public function getMajor($type){
    	$major = Db::name('major_edu')->where('exam_type',$type)->field('major_id,name')->select();
    	return $major;
    }

	public function getInfoOne($id){
		$where = [];
		$where['edu_id'] = ['=',"$id"];

		$join = [];
		$join = [
			['users u','e.userId=u.userId','left']
		];

		$field = 'u.trueName,u.idcard,u.student_no,e.edu_id,e.school_name,e.major_name,e.grade_name,e.level_id,e.entry_status,e.dispose_status,e.dispose_result';
		$res = Db::name('student_edu')
				->alias('e')
				->join($join)
				->where($where)
				->field($field)
				->find();
		$res['level_id'] = $this->getLevel($res['level_id']);
		return $res;
	}

	public function edit(){
        $studentEdu = new SEdu();
		$data = input('post.');
        //dd($data);exit;
		$edu_id = (int)input('post.edu_id');
		MBISUnset($data,'edu_id');
        if(empty($data)){
            return MBISReturn("已录取无法修改,请联系管理员", -2);
        }
        if($data['entry_status'] == '3'){
            $data['dispose_status'] = 0;
            $data['dispose_result'] = 0;
        }
		Db::startTrans();
		try{
		    $result = $studentEdu->allowField(true)->save($data,['edu_id'=>$edu_id]);
	        if(false !== $result){
	        	Db::commit();
	        	return MBISReturn("编辑成功", 1);
	        }
	    }catch (\Exception $e) {
            Db::rollback();
        }
        return MBISReturn('编辑失败',-1);
	}

	public function majorGet(){
    	$school_id = input('post.school_id');
    	$where = 'FIND_IN_SET('."$school_id".',school_ids)';
    	$field = 'major_id,name';
    	$majors = Db::name('major_edu')
    					->where($where)
    					->field($field)
    					->select();
    	if(!empty($majors)){
    		return ['data'=>$majors,'status'=>1];
    	}else{
    		return ['msg'=>'抱歉,出错了','status'=>-1];
    	}
    }

    public function levelGet(){
    	$major_id = input('post.major_id');
    	$where = [];
    	$where['major_id'] = ['=',"$major_id"];
    	$levels = Db::name('major_edu_extend')
    					->where($where)
    					->field('level_id')
    					->select();
    	if(!empty($levels)){
    		foreach($levels as &$v){
    			$v['level_name'] = $this->getLevel($v['level_id']);
    		}
    		return ['data'=>$levels,'status'=>1];
    	}else{
    		return ['msg'=>'抱歉,出错了','status'=>-1];
    	}
    }

    public function matriculate(){  //批量处理
        $studentEdu = new SEdu();
        $ids = input('post.ids');
        //dd($ids);
        $data = [];
        $idarr = [];
        $idarr = explode(',',$ids);
        for($i=0;$i<count($idarr);$i++){
            $data[$i]['edu_id'] = $idarr[$i];
        }
        foreach($data as &$v){
            $v['entry_status'] = 3;
            $v['dispose_status'] = 0;
            $v['dispose_result'] = 0;
        }
        //dd($data);
        Db::startTrans();
        try{
            $result = $studentEdu->saveAll($data);
            if(false !== $result){
                Db::commit();
                return MBISReturn("处理成功", 1);
            }
        }catch (\Exception $e) {
            Db::rollback();
        }
        return MBISReturn('处理失败',-1);
    }

    public function getEduList(){
        $exam_type = session('examType');
        $where                      = [];
        $where['m.exam_type']       = ['=',"$exam_type"];
        $where['e.entry_status']    = ['in',[1,2]];
        $where['e.dispose_status']  = ['=',1];
        $join = [];
        $join = [
            ['users u','e.userId=u.userId','left'],
            ['major_edu m','e.major_id=m.major_id','left']
        ];
        $field = 'e.edu_id,e.school_id,e.school_name,e.major_id,e.major_name,e.level_id,e.grade_id,e.grade_name,e.entry_status,e.dispose_status,e.dispose_result,u.trueName,u.idcard,u.student_no,u.userPhone';

        $page = Db::name('student_edu')
                    ->alias('e')
                    ->join($join)
                    ->where($where)
                    ->field($field)
                    ->paginate(input('pagesize/d'))
                    ->toArray();
        //getLastSql();
        if(count($page['Rows'])>0){
            foreach($page['Rows'] as $key=>$v){
                $page['Rows'][$key]['checkbox'] = '<input type="checkbox" id="ck_'.$v['edu_id'].'" name="chk"  value="'.$v['edu_id'].'">';
                $page['Rows'][$key]['level_id'] = $this->getLevel($v['level_id']);
            }
        }
        return $page;
    }

    /**
     *
     * 导出Excel
     */
    public function expStudentEdu(){//导出录取信息Excel
        //dd($_GET);
        //$data= input('get.');
        //dump($data);die;
        $exam_type = session('examType');
        $where = [];
        $school_id = input('get.school_id');
        $major_id = input('get.major_id');
        $level_id = input('get.level_id');
        $grade_id = input('get.grade_id');
        if($school_id !=''){
            $where['e.school_id'] = ['=',"$school_id"];
        }
        if($major_id !=''){
            $where['e.major_id'] = ['=',"$major_id"];
        }
        if($level_id !=''){
            $where['e.level_id'] = ['=',"$level_id"];
        }
        if($grade_id !=''){
            $where['e.grade_id'] = ['=',"$grade_id"];
        }
        $key = input('get.key');
        $key_value = trim(input('get.key_value'));
        if(($key !== '') && ($key_value !== '')){
            if($key =='trueName'){
                $where['u.trueName'] = ['like',"%$key_value%"];
            }elseif($key == 'student_no'){
                $where['u.student_no'] = ['like',"%$key_value%"];
            }elseif($key == 'userPhone'){
                $where['u.userPhone'] = ['like',"%$key_value%"];
            }elseif($key == 'idcard'){
                $where['u.idcard'] = ['like',"%$key_value%"];
            }
        }

        $where['m.exam_type'] = ['=',"$exam_type"];

        $join = [];
        $join = [
            ['users u','e.userId=u.userId','left'],
            ['major_edu m','e.major_id=m.major_id','left']
        ];
        $field = 'e.edu_id,e.school_id,e.school_name,e.major_id,e.major_name,e.level_id,e.grade_id,e.grade_name,e.entry_status,e.dispose_status,e.dispose_result,u.trueName,u.idcard,u.student_no,u.userPhone';

        $rs = Db::name('student_edu')
                ->alias('e')
                ->join($join)
                ->where($where)
                ->field($field)
                ->select();
        if($rs){
            $xlsData = $rs;
        }else{
            die("<span>缺少导出数据,导出Excel失败</span><button type='button' class='btn' onclick='javascript:history.go(-1)'>返&nbsp;回</button>");
            //$xlsData = [];
        }
        $xlsName  = "StudentEduList";
        $xlsCell  = array(
        array('idcard','身份证号码'),
        array('student_no','学员编号'),
        array('trueName','姓名'),
        array('school_name','报读院校'),
        array('major_name','专业'),
        array('level_id','层次'),
        array('grade_name','年级'),
        array('entry_status','录取状态'),
        array('dispose_status','处理状态'),
        array('dispose_result','处理结果')
        );
        foreach ($xlsData as $k => $v)
        {
            $xlsData[$k]['idcard']=" ".$v['idcard'];  
            $xlsData[$k]['level_id']=$this->getLevel($v['level_id']);
            $xlsData[$k]['entry_status']=$this->getEntryStatus($v['entry_status']);
            $xlsData[$k]['dispose_status']=$this->getDisposeStatus($v['dispose_status']);
            $xlsData[$k]['dispose_result']=$this->getDisposeResult($v['dispose_result']);
        }
        //dump($xlsData);die;
        $this->expExcel($xlsName,$xlsCell,$xlsData);
         
    }

    public function expExcel($expTitle,$expCellName,$expTableData){
        import('phpexcel.PHPExcel');
        import('phpexcel.PHPExcel.IOFactory');
        import('phpexcel.PHPExcel.Style.Alignment');
        import('phpexcel.PHPExcel_Cell_DataType');
        $objPHPExcel = new \PHPExcel();
        $xlsTitle = iconv('utf-8', 'gb2312', $expTitle);//文件名称
        $fileName = $xlsTitle.date('_YmdHis').'.xlsx';//or $xlsTitle 文件名称可根据自己情况设定
        $cellNum = count($expCellName);
        $dataNum = count($expTableData);
        $cellName = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ');
        //Excel表格样式设置区  ****** start  **********************************************************
        /*
        $styleArray1 = array(
          'font' => array(
            'bold' => true,
            'size'=>12,
            'color'=>array(
              'argb' => '00000000',
            ),
            'alignment' => array(
            'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER,
            ),
          ),
        );
        
        $objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArray1); */
        //$objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(12); //设置表格默认列宽(全部)
        //$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true); //设置某一列自适应宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(22); //设置某一列宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(12); //设置某一列宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(12); //设置某一列宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(22); //设置某一列宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(22); //设置某一列宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(14); //设置某一列宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(14); //设置某一列宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(12); //设置某一列宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(12); //设置某一列宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(14); //设置某一列宽度
        //$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(16); //设置某一列宽度
        $objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(20);//设置表格默认行高(全部)
        $objPHPExcel->getActiveSheet()->getDefaultStyle()->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER); //设置水平居中
        $objPHPExcel->getActiveSheet()->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER); //设置垂直居中
        $objPHPExcel->getActiveSheet()->getStyle('A')->getNumberFormat()
        ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_TEXT);

        $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true); //设置字体加粗
        $objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('C1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('D1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('E1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('F1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('G1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('H1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('I1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('J1')->getFont()->setBold(true);
        //$objPHPExcel->getActiveSheet()->getStyle('K1')->getFont()->setBold(true);

        //$objPHPExcel->getActiveSheet()->freezePaneByColumnAndRow(9,2); //冻结单元格
        $objPHPExcel->getActiveSheet()->freezePane('A1');
        $objPHPExcel->getActiveSheet()->freezePane('B1');
        $objPHPExcel->getActiveSheet()->freezePane('C1');
        $objPHPExcel->getActiveSheet()->freezePane('D1');
        $objPHPExcel->getActiveSheet()->freezePane('E1');
        $objPHPExcel->getActiveSheet()->freezePane('F1');
        $objPHPExcel->getActiveSheet()->freezePane('G1');
        $objPHPExcel->getActiveSheet()->freezePane('H1');
        $objPHPExcel->getActiveSheet()->freezePane('I1');
        $objPHPExcel->getActiveSheet()->freezePane('J1');
        //$objPHPExcel->getActiveSheet()->freezePane('K1');

        //Excel表格样式设置区  ****** end  ************************************************************
        for($i=0;$i<$cellNum;$i++){
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellName[$i].'1', $expCellName[$i][1]); 
        }  
        for($i=0;$i<$dataNum;$i++){
          for($j=0;$j<$cellNum;$j++){
            $objPHPExcel->getActiveSheet(0)->setCellValue($cellName[$j].($i+2), $expTableData[$i][$expCellName[$j][0]]);
          }             
        }
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        /*
        header('Content-type:application/vnd.ms-excel;charset=utf-8;name="'.$xlsTitle.'.xlsx"');
        header("Content-Disposition:attachment;filename=$fileName");//attachment新窗口打印inline本窗口打印
        header('Cache-Control: max-age=0');

        header("Content-Type: application/force-download"); 
        header("Content-Type: application/octet-stream"); 
        header("Content-Type: application/download");  
        header("Content-Transfer-Encoding: binary"); 
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
        header("Pragma: no-cache"); 
        
        $objWriter->save('php://output');exit;  //输出到浏览器下载
        */
        $path = TEMP_PATH;
        $path_file = TEMP_PATH."$fileName";
        $objWriter->save($path_file); //保存到临时文件目录
        $url = 'InfoDownload?path='.$path.'&file='.$fileName;
        $html = 
        "<span>Excel文件导出成功</span><a href=".$url.">立即下载</a><button type='button' class='btn' onclick='javascript:history.go(-1)'>返&nbsp;回</button>";
        die("$html");
    }

    //导入数据
    public function importStudentEdu($file){
        set_time_limit(0);
    
        import('phpexcel.PHPExcel.IOFactory');
        $reader = \PHPExcel_IOFactory::createReader('Excel2007');
        //$filename = "/xlsx/{$num}.xlsx";
        //$file = 'D:/zp/v1.0/public/upload/exams/baokao/achievement-2017-4-22.xlsx';
        $objReader = \PHPExcel_IOFactory::load($file);
    
        $objReader->setActiveSheetIndex(0);
        $sheet  = $objReader->getActiveSheet();
        $rows   = $sheet->getHighestRow();//行
        $line   = $sheet->getHighestColumn();//列A B C D
        $allLine = \PHPExcel_Cell::columnIndexFromString($line);
    
        $allcolumn = 0;
        $arrContent = array();
        for ($r = 1; $r <= $rows; $r++){
            for($l = 0; $l < $allLine; $l++){
                $arrContent[$r][]  = (string)$sheet->getCellByColumnAndRow($l,$r);
            }
        }
        dump($arrContent);die;
        //列表的选项
        $usreKey   = 2;//用户身份证
        $zkKey     = 3;//准考证号
        $schoolKey = 4;//学校编号
        $majorKey  = 5;//专业
        $levelKey  = 6;//层级
        dump($arrContent);
        $majorObj       = new \application\admin\model\Major;
        $arrExamsStatus = $this->arrExamsStatus;//报考状态
        $arrExamsStatus = array_flip($arrExamsStatus);
        if($arrContent){
            foreach($arrContent as $key => $v ){
                if($key==1){
                    //查找后面的所有科目
                    $arrSub = array();
                    foreach ($v as $kk => $s){
                        if($kk>6){
                            $r = substrString($s);
                            if($r==false){
                                return '专业编号未填！！！';
                            }
                            $arrSub[] = $r;
                        }
    
                    }
                    if(!$arrSub){
                        return '没有科目信息！！';
                    }
                    //查找科目id
                    $whereSub['subject_no'] = ['in',implode(',', $arrSub)];
                    $arrSubject  = db::name('subject_edu')->field('subject_id')->where($whereSub)->select();
                    if( count($arrSub) !=  count($arrSubject) ){
                        return '科目编号有错误';
                    }
                    //查找所有的科目id
                    foreach ($arrSubject as $kkk => $vvv ){
                        $arrAllSubject[] = $vvv['subject_id'];
                    }
                }else{
                    //学员
                    if(empty($v[$usreKey])){
                        return '学员身份证不能为空！！！';
                    }
                    $arrUser[ $v[$usreKey] ]['userId'] = $v[$usreKey];
                    $arrUserId[]  = $v[$usreKey];
                    //查找学校
                    $school_number = ( string )$v[$schoolKey];#####
                    $res           =  substrString($school_number);
                    $where['school_no'] = ['=',$res];
                    $arrShool  = db::name('school')->field('school_id')->where($where)->find();
                    $school_id = $arrShool['school_id'];
                    if(!$school_id){
                        return '学校编号未找到！！！';
                    }
    
                    $arrUser[$v[$usreKey]]['school_id'] = $school_id;
    
                    //查找专业
                    $major_number = ( string )$v[$majorKey];#####
                    $res_major    = substrString($major_number);
                    $whereM['major_number'] = ['=',$res_major];
                    $arrMajor  = db::name('major')->field('major_id')->where($whereM)->find();
                    $major_id  = $arrMajor['major_id'];
                    if(!$major_id){
                        return '专业编号未找到';
                    }
                    $arrUser[ $v[$usreKey] ]['major_id'] = $major_id;
    
                    //查找专业层次
                    $level         = ( string )$v[$levelKey];#####
                    if($level==false){
                        return '专业层次没有找到';
                    }
                    $arrMajorLevel = $majorObj->arrMajorLevel;
                    $arr_level     = array_flip($arrMajorLevel);
                    if(empty($arr_level[$level])){
                        return '专业层次没有找到';
                    }
                    $level_id                = $arr_level[$level];
                    $arrUser[ $v[$usreKey] ]['level_id'] = $level_id;
    
    
                    //准考证号
                    $arrZkNo  = array();
                    //科目对应的成绩
                    foreach ($v as $kk => $s){
                        //准考证号
                        if($kk == $zkKey ){
                            if(!$s){
                                return '准考证号不全';
                            }
                            $arrUser[ $v[$usreKey] ]['exam_no'] = $s;
                        }
                        $i = 0;
                        if($kk>6){
                            $s = trim($s);
                            if( empty($arrExamsStatus[$s]) ){
                                return '报考状态错误';
                            }
                            //
                            if( count( $arrAllSubject ) + 7 != count($v) ){
                                return '科目数目不对';
                            }
                            //报考的状态
                            $arrUser[ $v[$usreKey] ]['subject'][] = array(
                                'subject_id' => $arrAllSubject[$i],
                                'status'     => $arrExamsStatus[$s]
                            );
                            $i++;
                        }
                    }
                }
            }
        }else{
            return '数据不存在';
        }
         
        if($arrUserId){
            $userIds = implode(',', $arrUserId);
            $where   = array();
            $where['idcard'] = ['in',$userIds];
            //查找用户
            $arrUsers =db::name('users')->alias('s')
            ->field('s.userId,s.idcard')
            ->where($where)
            ->select();
    
            //$userSubject 用户对的 成绩
            Db::startTrans();
            $is_true = true;
            foreach ($arrUsers as $key => $v ){
                $userSubject = $arrUser[ $v['idcard'] ]['subject'];
                $data = array(
                    'userId'           => $v['userId'],
                    'examination_type' => 1,
                    'school_id'        => $arrUser[$v['idcard']]['school_id'],
                    'major_id'         => $arrUser[$v['idcard']]['major_id'],
                    'level_id'         => $arrUser[$v['idcard']]['level_id'],
                    'exam_no'          => $arrUser[$v['idcard']]['exam_no'],//准考证号
                    'data_type'        => '1',  //数据类型：0=正常录入 1=批量导入
                );
    
                $id  = $this->save($data);
    
                if($id){
                    foreach ($userSubject as $su){
                        $data[] = array(
                            'subject_id'       => $su['subject_id'],
                            'status'           => $su['status'],//报考状态
                        );
                    };
                    $id1 = db::name('sj_exams_subject')->insertAll($data);
                    if(!$id1){
                        $is_true = false;
                    }
                }else{
                    $is_true = false;
                }
    
            }
    
            if($is_true){
                Db::commit();
                return '上次数据成功';
            }else{
                Db::rollback();
                return '上次数据失败';
    
            }
        }else{
            return '学员数据不存在';
        }
    }
    
}
