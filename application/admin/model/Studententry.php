<?php
namespace application\admin\model;
use think\Db;
class Studententry extends Base{

	public function getEduInfo(){ //学历报名信息
	    $where = '';
	    $search_word = input('search_word');
	    if($search_word){
	        $where = "u.trueName  like '%$search_word%' OR a.exam_no like '%$search_word%'";
	    }
	    $field   = 'a.*,FROM_UNIXTIME(a.entry_time) as entry_time,u.trueName,u.userId,u.student_no'; 
	    $eduInfo = Db::name('studentEdu a')->join('users u','u.userId = a.userId','LEFT')
	                                       ->where($where)
	                                       ->field($field)
	                                       ->paginate(input('pagesize/d'));
	    return $eduInfo;
	}
	
	public function getSkillInfo(){ //技能报名信息
	    $where = '';
	    $search_word = input('search_word');
	    if($search_word){
	        $where = "u.trueName  like '%$search_word%' OR a.exam_no like '%$search_word%'";
	    }
	    $field   = 'a.*,FROM_UNIXTIME(a.entry_time) as entry_time,u.trueName,u.userId,u.student_no';
	    $eduInfo = Db::name('studentSkill a')->join('users u','u.userId = a.userId','LEFT')
                                    	   	 ->where($where)
                                    	     ->field($field)
                                    	     ->paginate(input('pagesize/d'));
	    return $eduInfo;
	}
	
	public function expUsersEdu(){
	    
	    
	    $where = '';
	    $search_word = input('search_word');
	    if($search_word){
	        $where = "u.trueName  like '%$search_word%' OR a.exam_no like '%$search_word%'";
	    }
	    $field   = 'a.*,FROM_UNIXTIME(a.entry_time) as entry_time,u.trueName,u.userId,u.student_no';
	    $eduInfo = Db::name('studentEdu a')->join('users u','u.userId = a.userId','LEFT')
                                    	   ->where($where)
                                    	   ->field($field)
                                    	   ->select();
	    
	    $xlsName  = "baoming";
	    $xlsCell  = array(
	        array('trueName','名称'),
	        array('student_no','学员编号'),
	        array('school_name','院校'),
	        array('major_name','专业'),
	        array('course_name','课程'),
	        array('grade_name','年级'),
	        array('exam_no','准考证号'),
	        array('receivable_fee','应收学费'),
	        array('real_fee','实收学费'),
	        array('arrearage_fee','欠费'),
	        array('entry_time','报名时间'),
	        array('remark','备注'),
	    );
	    
	    $this->expExcel($xlsName,$xlsCell,$eduInfo);
	}
	
	//技能类
	public function expUsersSkil(){
	    
	    $where = '';
	    $search_word = input('search_word');
	    if($search_word){
	        $where = "u.trueName  like '%$search_word%' OR a.exam_no like '%$search_word%'";
	    }
	    $field   = 'a.*,FROM_UNIXTIME(a.entry_time) as entry_time,u.trueName,u.userId,u.student_no';
	    $eduInfo = Db::name('studentSkill a')->join('users u','u.userId = a.userId','LEFT')
                                    	    ->where($where)
                                    	    ->field($field)
                                    	    ->select();
	     
	    $xlsName  = "baoming";
	    $xlsCell  = array(
	        array('trueName','名称'),
	        array('student_no','学员编号'),
	        array('school_name','院校'),
	        array('major_name','专业'),
	        array('exam_no','准考证号'),
	        array('receivable_fee','应收学费'),
	        array('real_fee','实收学费'),
	        array('arrearage_fee','欠费'),
	        array('entry_time','报名时间'),
	        array('remark','备注'),
	    );
	     
	    $this->expExcel($xlsName,$xlsCell,$eduInfo);
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
	    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(16); //设置某一列宽度
	    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(12); //设置某一列宽度
	    $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(12); //设置某一列宽度
	    $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(14); //设置某一列宽度
	    $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(14); //设置某一列宽度
	    $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(14); //设置某一列宽度
	    $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(32); //设置某一列宽度
	    $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(12); //设置某一列宽度
	    $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(14); //设置某一列宽度
	    $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(16); //设置某一列宽度
	    //$objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(20);//设置表格默认行高(全部)
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
	    $objPHPExcel->getActiveSheet()->getStyle('K1')->getFont()->setBold(true);
	
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
	    $objPHPExcel->getActiveSheet()->freezePane('K1');
	
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
}
