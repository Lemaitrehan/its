<?php
namespace application\admin\model;
/**
 * 员工业务处理
 */
use think\Db;
class AdItem extends Base{
	/**
	 * 分页
	 */
	public function pageQuery(){
        $key = input('get.key');
        $where = [];
		if($key!='')$where['name'] = ['like','%'.$key.'%'];
        $page = $this->where($where)->field('*')->order('lastmodify desc')
		->paginate(input('post.pagesize/d'))->toArray();
		
		if(count($page['Rows'])>0){
			foreach($page['Rows'] as $key => $v){
				/*
				if(isset($page['Rows'][$key]['department_id'])){
					$page['Rows'][$key]['department_id'] = $this->get_department_name($v['department_id']);
				}
				if(isset($page['Rows'][$key]['employee_type_id'])){
					$page['Rows'][$key]['employee_type_id'] = $this->get_employeetype_name($v['employee_type_id']);
				} 
				if(isset($page['Rows'][$key]['business_center_id'])){
					$page['Rows'][$key]['business_center_id'] = $this->get_businesscenter_name($v['business_center_id']);
				}
				*/
				$page['Rows'][$key]['teaching_type'] = $this->get_teaching_type($v['teaching_type']);
				$page['Rows'][$key]['is_shelves'] = $this->get_is_shelves($v['is_shelves']);
			}
		}
		
        return $page;
	}
	public function getById($id){
		return $this->get(['it_id'=>$id]);
	}
	/**
	 * 新增
	 */
	public function add(){
		$data = input('post.');
		$data['createtime'] = time();
        $data['lastmodify'] = time();
        MBISUnset($data,'id');
		Db::startTrans();
		try{
			$result = $this->save($data);
	        if(false !== $result){
			    Db::commit();
	        	return MBISReturn("新增成功", 1);
	        }
		}catch (\Exception $e) {
            Db::rollback();
        }  
        return MBISReturn('新增失败',-1);
	}
    /**
	 * 编辑
	 */
	public function edit(){
		$id = (int)input('post.id');
		$data = input('post.');
        $data['lastmodify'] = time();
		MBISUnset($data,'id');
		Db::startTrans();
		try{
		    $result = $this->save($data,['it_id'=>$id]);
	        if(false !== $result){
	        	Db::commit();
	        	return MBISReturn("编辑成功", 1);
	        }
	    }catch (\Exception $e) {
            Db::rollback();
        }
        return MBISReturn('编辑失败',-1);  
	}
	/**
	 * 删除
	 */
    public function del(){
	    $id = input('post.id/d');
	    Db::startTrans();
		try{
		    $result = $this->where(['it_id'=>$id])->delete();
	        if(false !== $result){
	        	Db::commit();
	        	return MBISReturn("删除成功", 1);
	        }
		}catch (\Exception $e) {
            Db::rollback();
            return MBISReturn('删除失败',-1);
        }
	}
	/**
	 * 员工信息列表
	 */
	public function get_info_list(){
		$info = Db::name('ad_item')->field('*')->select();
		return $info;
	}
	/**
	 * 部门列表
	 */
	public function get_department_list(){
        $department = Db::name('department');
        return $department->field('*')->select();
    }
    /**
     * 岗位列表
     */
    public function get_employeetype_list(){
    	$employeetype = Db::name('EmployeeType');
    	return $employeetype->field('*')->select();
    }
    /**
     * 校区列表
     */
    public function get_businesscenter_list(){
    	$businesscenter = Db::name('BusinessCenter');
    	return $businesscenter->field('*')->select();
    }

    /**
     * 部门名称
     */
    public function get_department_name($id=0){
    	$department = Db::name('department');
    	return $department->where('department_id',$id)->value('name');
    }
    /**
     * 岗位名称
     */
    public function get_employeetype_name($id=0){
    	$employeetype = Db::name('EmployeeType');
    	return $employeetype->where('employee_type_id',$id)->value('name');
    }
    /**
     * 校区名称
     */
    public function get_businesscenter_name($id=0){
    	$businesscenter = Db::name('business_center');
    	return $businesscenter->where('business_center_id',$id)->value('name');
    }

    public function time_date($time){
		return date('Y-m-d',$time);
	}

	public function get_teaching_type($status){
		switch($status){
			case 1:return '学杂';
			case 2:return '服务';
			case 3:return '教材';
			default:return '未知';
		}
	}

	public function get_is_shelves($status){
		switch($status){
			case 0:return '已下架';				
			case 1:return '已上架';				
			case 2:return '待上架';
			default:return '未知';
		}
	}
	public function get_is_sole($type){
		switch($type){
			case 0:return '否';
			case 1:return '是';
			default:return '未知';
		}
	}
    //数据列表
    public function get_lists($where=[])
    {
        $rs = $this->where($where)->select();
        return $rs;
    }

	/**
     *
     * 导出Excel
     */
    public function expAditem(){//导出Excel
        $where = [];
        $key = input('get.key');
        if($key!='')$where['name'] = ['like','%'.$key.'%'];
        $rs = $this->where($where)->field('*')->order('lastmodify desc')->select();
        if($rs){
            $xlsData = $rs;
        }else{
            $xlsData = [];
        }
        $xlsName  = "Aditem";
        $xlsCell  = array(
        array('name','杂费名称'),
        array('price','标准价'),
        array('offers_price','优惠价'),
        array('is_sole','是否唯一'),
        array('teaching_type','杂费类型'),
        array('is_shelves','是否上架'),
        array('des','简介'),
        array('details','详情'),
        array('createtime','添加时间'),
        array('lastmodify','更新时间')
        );
        foreach ($xlsData as $k => $v)
        {
            $xlsData[$k]['is_sole']=$this->get_is_sole($v['is_sole']);
            $xlsData[$k]['teaching_type']=$this->get_teaching_type($v['teaching_type']);
            $xlsData[$k]['is_shelves']=$this->get_is_shelves($v['is_shelves']);
            $xlsData[$k]['createtime']=$this->time_date($v['createtime']);
            $xlsData[$k]['lastmodify']=$this->time_date($v['lastmodify']);
        }
        //dump($xlsData);die;
        $this->exportExcel($xlsName,$xlsCell,$xlsData);
         
    }

    public function exportExcel($expTitle,$expCellName,$expTableData){
        import('phpexcel.PHPExcel');
        import('phpexcel.PHPExcel.IOFactory');
        import('phpexcel.PHPExcel.Style.Alignment');
        $objPHPExcel = new \PHPExcel();
        $xlsTitle = iconv('utf-8', 'gb2312', $expTitle);//文件名称
        $fileName = $expTitle.date('_YmdHis');//or $xlsTitle 文件名称可根据自己情况设定
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
        $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(12); //设置表格默认列宽
        $objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(20); //设置表格默认行高
        $objPHPExcel->getActiveSheet()->getDefaultStyle()->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER); //设置水平居中
        $objPHPExcel->getActiveSheet()->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER); //设置垂直居中
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

        //Excel表格样式设置区  ****** end  ************************************************************
        for($i=0;$i<$cellNum;$i++){
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellName[$i].'1', $expCellName[$i][1]); 
        }  
        for($i=0;$i<$dataNum;$i++){
          for($j=0;$j<$cellNum;$j++){
            $objPHPExcel->getActiveSheet(0)->setCellValue($cellName[$j].($i+2), $expTableData[$i][$expCellName[$j][0]]);
          }             
        }
        header('pragma:public');
        header('Content-type:application/vnd.ms-excel;charset=utf-8;name="'.$xlsTitle.'.xlsx"');
        header("Content-Disposition:attachment;filename=$fileName.xlsx");//attachment新窗口打印inline本窗口打印
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');   
    }
}
