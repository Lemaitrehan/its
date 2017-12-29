<?php
namespace application\admin\model;
/**
 * 订单业务处理
 */
use think\Db;
use application\common\model\Orders as ITSComOrders;
class Orders extends Base{
	/**
	 * 分页
	 */
	public function pageQuery($orderId=""){
		$start = strtotime(input('get.start'));
		$end = strtotime(input('get.end'));
        $type_id = Input("type_id/d",0);
        $query_type_id = Input("query_type_id/d",0);
        $orderNo = input('get.orderNo');
        $buyType = input('get.buyType');
        $order_status = input('get.order_status');
        $where = [];
        if(!empty($start) && !empty($end)){
			$where['createtime'] = ['between',["$start","$end"]];
		}
        if($type_id!='')$where['type_id'] = $type_id;
        if($query_type_id!='')$where['type_id'] = $query_type_id;
		if($orderNo!='')$where['orderNo'] = $orderNo;
        if($buyType!='')$where['buyType'] = $buyType;
        if($order_status!='' && $order_status!='0_0_0')
        {
            list($confirmStatus,$payStatus,$orderType) = explode('_',$order_status);
            $where['confirmStatus'] = $confirmStatus;
            $where['payStatus'] = $payStatus;
            $where['orderType'] = $orderType;
        }
        
        if($orderId){
            $where['orderId'] = $orderId;
        }
        
        $page = $this->where($where)->field('*')->order('createtime desc')
		->paginate(input('post.pagesize/d'))->toArray();
        if(count($page['Rows'])>0){
			foreach ($page['Rows'] as $key => $v){
                $page['Rows'][$key]['loginName'] = Db::name('users')->where('userId',$v['userId'])->value('loginName');
                $page['Rows'][$key]['typeName']  = Db::name('subject_type')->where('type_id',$v['type_id'])->value('name');
                $page['Rows'][$key]['orderStatusName'] = ITSSelItemName('order','order_status',$v['confirmStatus'].'_'.$v['payStatus'].'_'.$v['orderType']);
                $page['Rows'][$key]['payTypeName'] = ITSGetPayTypeName($v['payType']);
                $page['Rows'][$key]['payFromName'] = ITSGetPayFromName($v['payFrom']);
                $page['Rows'][$key]['createtime_format'] = date('Y-m-d H:i',$v['createtime']);
                $page['Rows'][$key]['isAudit'] = 0;
                //线下支付&&未确认 进行财务审核
                //if($v['isClosed']==0 && $v['orderType']==1 && $v['payType']==2 && $v['orderStatus']!=2)
                if($v['payType']==2 && $v['confirmStatus']==0)
                {
                    $page['Rows'][$key]['isAudit'] = 1;   
                }
			}
		}
        return $page;
	}
	public function getById($id){
		$rs = $this->get(['orderId'=>$id]);
        $rs = obj2Array($rs);
        $rs['createtime'] = date('Y-m-d H:i',$rs['createtime']);
        $rs['typeName'] = db::name('subject_type')->where('type_id',$rs['type_id'])->value('name');
        $rs['payTypeName'] = ITSGetPayTypeName($rs['payType']);
        $rs['payFromName'] = ITSGetPayFromName($rs['payFrom']);
        //$rs['orderStatusName'] = MBISLangOrderStatus($rs['orderStatus']);
        $rs['orderStatusName'] = ITSSelItemName('order','order_status',$rs['confirmStatus'].'_'.$rs['payStatus'].'_'.$rs['orderType']);
        $rs['agent_truename'] = Db::name('users')->where('userId',$rs['agent_uid'])->value('trueName');
        //应付金额
        $rs['dealPayMoney'] = sprintf('%0.2f',MBISBCMoney($rs['realPayMoney'],$rs['depositRemainMoney']));
        //应付定金金额
        $rs['dealDepositMoney'] = sprintf('%0.2f',MBISBCMoney($rs['depositMoney'],$rs['depositAddMoney']));
        //课程明细
        $comOrders = new \application\common\model\Orders;
        $courseDetail = $comOrders->get_course_detail(['orderId'=>$id,'fromType'=>'admin']);
        $rs['courseDetail'] = $courseDetail[$id];
        $tmp_order_team_detail = $comOrders->get_order_team_detail(['orderId'=>[$id],'fromType'=>'admin']);
        $tmp_order_rule_detail = $comOrders->get_order_rule_detail(['orderId'=>[$id],'fromType'=>'admin']);
        $rs['orderTeamLists'] = isset($tmp_order_team_detail[$id])?$tmp_order_team_detail[$id]:[];
        $rs['orderRuleLists'] = isset($tmp_order_rule_detail[$id])?$tmp_order_rule_detail[$id]:[];
        $rs['buyType'] = $this->get_buy_type($rs['buyType']);
        return $rs;
	}
	/**
	 * 新增
	 */
	public function add(){
		$data = input('post.');
        $data['costst'] = (float)$data['costst'];
		$data['createtime'] = time();
        $data['lastmodify'] = time();
		MBISUnset($data,'school_id');
        MBISUnset($data,'id');
		Db::startTrans();
		try{
			$result = $this->validate('school.add')->allowField(true)->save($data);
			//$id = $this->school_id;
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
		$id = (int)input('post.school_id');
		$data = input('post.');
        $data['lastmodify'] = time();
		MBISUnset($data,'createtime');
		Db::startTrans();
		try{
		    $result = $this->validate('school.edit')->allowField(true)->save($data,['school_id'=>$id]);
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
		    $result = $this->where(['school_id'=>$id])->delete();
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
	 * 学校列表
	 */
    public function get_lists($where=[]){
        return $this->where($where)->field('*')->select();
	}
	public function get_lists_edu(){
		return $this->where('jump_type',1)->field('school_id,name')->select();
	}
	public function get_lists_skill(){
		return $this->where('jump_type',2)->field('school_id,name')->select();
	}
    public function get_name($id=0){
        return $this->where('school_id',$id)->value('name');
	}
    public function get_buy_type($type){
        switch($type){
            case 0:return '直销订单';
            case 1:return '代销订单';
        }
    }
    /**
	 * 订单审核
     * @desc
     未确认，未付款（定金）；----线上付款
未确认，未付款（全款）；----线上付款

未确认，已付款（定金）；----线下付款
未确认，已付款（全款）；----线下付款

已确认，已付款（定金）；----线下线上付款（后台财务审核/支付网关回调触发）
已确认，已付款（全款）；----线下线上付款（后台财务审核/支付网关回调触发）

已取消，未付款（定金）；----线下线上付款（线上付款可由用户操作触发，线下付款需要后台人员操作触发）
已取消，未付款（全款）；----线下线上付款（线上付款可由用户操作触发，线下付款需要后台人员操作触发）

已取消，已退款（定金）；----线下线上付款
已取消，已退款（全款）；----线下线上付款
	 */
	public function toAudit(){
		$data = input('post.');
        $id = $data['orderId'];
        MBISUnset($data,'orderId');
        //$data['payStatus'] = 1;
        //$data['orderStatus'] = 2;
        $data['payTime'] = time();
        $data['lastmodify'] = time();
		Db::startTrans();
		//try{
            //付款 && 已确认
            if($data['payStatus']==1 && $data['confirmStatus']==1)
            {
                $comOrders = new ITSComOrders();
                $comOrders->set_entry_data(['type_id'=>$data['type_id'],'orderId'=>$id]);
            }
            MBISUnset($data,'type_id');
		    $result = $this->allowField(true)->save($data,['orderId'=>$id]);
	        if(false !== $result){
	        	Db::commit();
	        	return MBISReturn("提交成功", 1);
	        }
	    /*}catch (\Exception $e) {
            Db::rollback();
        }*/
        return MBISReturn('提交失败',-1);  
	}

	/**
     *
     * 导出Excel
     */
    public function expOrders(){//导出Excel
        $where = [];
        $start = strtotime(input('get.start'));
        $end = strtotime(input('get.end'));
        $type_id = Input("type_id/d",0);
        $query_type_id = Input("query_type_id/d",0);
        $orderNo = input('get.orderNo');
        $buyType = input('get.buyType');
        $order_status = input('get.order_status');
        
        if(!empty($start) && !empty($end)){
            $where['createtime'] = ['between',["$start","$end"]];
        }
        if($type_id!='')$where['type_id'] = $type_id;
        if($query_type_id!='')$where['type_id'] = $query_type_id;
        if($orderNo!='')$where['orderNo'] = $orderNo;
        if($buyType!='')$where['buyType'] = $buyType;
        if($order_status!='' && $order_status!='0_0_0')
        {
            list($confirmStatus,$payStatus,$orderType) = explode('_',$order_status);
            $where['confirmStatus'] = $confirmStatus;
            $where['payStatus'] = $payStatus;
            $where['orderType'] = $orderType;
        }
        
        $rs = $this->where($where)->field('*')->order('createtime desc')->select();
        
        if($rs){
            $xlsData = $rs;
        }else{
            die("<span>导出Excel出错</span><button type='button' class='btn' onclick='javascript:history.go(-1)'>返&nbsp;回</button>");
            //$xlsData = [];
        }
        $xlsName  = "Orders";
        $xlsCell  = array(
        array('orderNo','订单号'),
        array('totalMoney','订单总金额'),
        array('realTotalMoney','应付金额'),
        array('realPayMoney','实付金额'),
        array('depositRemainMoney','未付金额'),
        array('discountMoney','优惠金额'),
        array('payFrom','支付方式'),
        array('name','报名人姓名'),
        array('mobile','报名人手机号'),
        array('confirmStatus','订单状态'),
        array('createtime','创建时间')
        );
        foreach ($xlsData as $k => $v)
        {
            $xlsData[$k]['payFrom']=ITSGetPayFromName($v['payFrom']);
            $xlsData[$k]['confirmStatus']=ITSSelItemName('order','order_status',$v['confirmStatus'].'_'.$v['payStatus'].'_'.$v['orderType']);
            $xlsData[$k]['createtime']=date('Y-m-d H:i',$v['createtime']);
        }
        //dump($xlsData);die;
        $this->exportExcel($xlsName,$xlsCell,$xlsData);
         
    }

    public function exportExcel($expTitle,$expCellName,$expTableData){
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
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(16); //设置某一列宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(16); //设置某一列宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(12); //设置某一列宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(12); //设置某一列宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(12); //设置某一列宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(12); //设置某一列宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(16); //设置某一列宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15); //设置某一列宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(16); //设置某一列宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(28); //设置某一列宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(18); //设置某一列宽度
        $objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(20);//设置表格默认行高(全部)
        $objPHPExcel->getActiveSheet()->getDefaultStyle()->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER); //设置水平居中
        $objPHPExcel->getActiveSheet()->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER); //设置垂直居中
        $objPHPExcel->getActiveSheet()->getStyle('B')->getNumberFormat()
        ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1); //设置价格类数据显示两位小数,有千位分号     FORMAT_NUMBER_00(两位小数,无千位分号)
        $objPHPExcel->getActiveSheet()->getStyle('C')->getNumberFormat()
        ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $objPHPExcel->getActiveSheet()->getStyle('D')->getNumberFormat()
        ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $objPHPExcel->getActiveSheet()->getStyle('E')->getNumberFormat()
        ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $objPHPExcel->getActiveSheet()->getStyle('F')->getNumberFormat()
        ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
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
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        /*
        header('pragma:public');
        header('Content-type:application/vnd.ms-excel;charset=utf-8;name="'.$xlsTitle.'.xlsx"');
        header("Content-Disposition:attachment;filename=$fileName.xlsx");//attachment新窗口打印inline本窗口打印
        
        $objWriter->save('php://output');exit;
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
