<?php
namespace application\admin\model;
use think\Db;
class Customer extends Base{
	
	
    //列表页
    public function listPage(){
        $where = array();
        if( input('search_title') && input('search_word')  ){
            $search_title = input('search_title');
            $search_word  = input('search_word');
            switch ($search_title){
                case 1:
                   $where['visitors_name'] = $search_word;
                  break;
                case 2:
                    $where['visitors_phone'] = $search_word;
                  break;
                case 3:
                    $where['customer_name'] = $search_word;
                  break;
                  
            }
        }
        $res = $this->where($where)
                    ->paginate(input('post.pagesize/d'))
                    ->toArray();
        return   $res;
      
    }
	
	//导入数据
	public function importUsers($type){
	    $file = request()->file('exel');
	    if($file == null ){
	        $this->error('请选择上传文件');
	    }
	    // 移动到框架应用根目录/public/uploads/目录下
	    $info = $file->rule('uniqid')->validate(['size'=>256780,'ext'=>'xlsx,xls,csv'])->move(ROOT_PATH . 'public' . DS . 'upload'.'/generalize/exel','',true);
	    if($info){
	        $file = ROOT_PATH . 'public' . DS . 'upload'.'/generalize/exel/'.$info->getSaveName();
	    }else{
	        return $msg =  $file->getError();
	    }
	
	    set_time_limit(0);
	    import('phpexcel.PHPExcel.IOFactory');
	    $reader = \PHPExcel_IOFactory::createReader('Excel2007');
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

	    //标题数据
	    $arrTitle = array(
	        'ID','访客姓名','对话渠道','客服姓名','开始时间','通话时长','等待时长（秒）','通话时长（秒）',
	        '对话记录','首次响应时长','平均响应时长','访客发送条数','客服回复条数','应答时间','结束时间',
	        '对话主题','对话级别','对话备注','服务质量','服务评价','访客地区','访客IP','搜索引擎','关键词',
	        '来源','落地页','对话页','访客电脑系统','分辨率','浏览器','分组ID'
	    );
	    $arrField = array(
            'id',
            'type_name',
            'dialogue_channel',
            'customer_name',
            'start_time',
            'call_wait_time_text',
            'call_wait_time',
            'call_time',
            'content',
            'frist_response_time',
            'average_response_time',
            'visitor_send_num',
            'reply_num',
            'promised_time',
            'stop_promised_time',
            'dialogue_title',
            'dialogue_level',
            'dialogue_remark',
            'service_quality',
            'service_evaluation',
            'visitors_region',
            'visitors_ip',
            'search_engine',
            'keyword',
            'url',
            'page_url',
            'dialogue_url',
            'system',
            'resolution',
            'browser',
            'groupingId',
	    );
	  /*   'tg_id',
	    'visitors_name',
	    'visitors_phone', 
	                'customer_id',
	    
	    */
	    //单元格
	    $visitors = 1;//访客信息
	    $service = 3;//客服人员
	    $start_time_key = 4;//开始时间
	    
	    $frist_response_time_key = 9;//首次响应时长
	    $average_response_time_key = 10;//平均响应时长
	    $visitor_send_num_key = 11;//访客发送条数
	    $reply_num_key = 12;//客服回复条数
	    
	    $promised_time_key = 13;//应答时间
	    $stop_promised_time_key = 14;//结束时间
	    Db::startTrans();
	    
    	    $data['create_person_id'] = session('MBIS_STAFF')->staffId;
    	    $data['create_time']      = time();
    	    $data['file_path']        = $file;
    	    $data['type']             = $type;
    	    $generalize  = db::name('generalize');
    	    $generalize->insert($data);
    	    $id1 = $generalize->getLastInsID();
    	    if(!$id1){
    	        exception('模板主数据导入失败！！！');
    	    }
    	    
	    try{
    	    foreach ($arrContent as $key => $v ){
    	        
    	        //第一行标题
    	        if ($key == 1){
    	             foreach ($v as $key1 => $t ){
    	                 if( $arrTitle[$key1] != $t ){
    	                     exception('导入模板不符合要求');
    	                 }
    	             }
    	        }else{
    	            
    	             $arrData = array();
    	             //----------组装字段---------------
    	             foreach ($v as $key2 => $c){
    	                 $arrData[ $arrField[$key2] ] = $c;
    	                 switch ($key2){
    	                     //访客信息
    	                     case  $visitors:
        	                     $pattern     = '/(.*)【(.*)】|\s+(.*)\/(.*)/';
        	                     $is_visitors = preg_match_all($pattern, $c,$res);
        	                  
        	                     if(!$visitors){
        	                         exception('第'.($key+1).'条数据访客信息错误');
        	                     }
        	                  
        	                     $arrData['type_name']      = $res[1][0];//访客类型
        	                     $arrData['tg_id']          = $res[2][0];//推广id
        	                     $arrData['visitors_name']  = isset($res[3][1])?$res[3][1]:$res[3][0];//访客名称
        	                     $arrData['visitors_phone'] = isset($res[4][1])?$res[4][1]:$res[4][0];//访客电话
        	                    break;
        	                 //客服  
    	                     case $service:
    	                         $where['name'] = ['=',$c];
    	                         $yg_id = db::name('employee')->where($where)->value('employee_id');
    	                         $arrData['customer_id'] = $yg_id;
    	                       break;
    	                     //开始时间  
    	                     case $start_time_key:
    	                           $start_time = strtotime($c);
    	                           if(!$start_time){
    	                               exception('第'.($key+1).'条数据【开始时间】 插入失败，导致此次操作失败！！！');
    	                           }
     	                           $arrData['start_time'] = $start_time;
    	                       break;
    	                     //首次响应时长
    	                     case $frist_response_time_key:
    	                          $arrData['frist_response_time'] = str_replace("秒","",$c);
    	                        break;
	                         //首次响应时长
	                         case $average_response_time_key:
	                              $arrData['average_response_time'] = str_replace("秒","",$c);
	                            break;
                            //访客发送条数
                            case $visitor_send_num_key:
                                  $arrData['visitor_send_num'] = str_replace("条","",$c);
                                break;
                            //客服回复条数
                            case $reply_num_key:
                                  $arrData['reply_num'] = str_replace("条","",$c);
                                break;
    	                     //答应时间
	                         case $promised_time_key:
	                                $promised_time = strtotime($c);
	                                if(!$promised_time){
	                                    exception('第'.($key+1).'条数据【答应时间】 插入失败，导致此次操作失败！！！');
	                                }
     	                            $arrData['promised_time'] = $promised_time;
	                             break;
	                           //结束时间
                             case $stop_promised_time_key:
                                     $stop_promised_time = strtotime($c);
                                     if(!$stop_promised_time){
                                         exception('第'.($key+1).'条数据【结束时间】 插入失败，导致此次操作失败！！！');
                                     }
                                     $arrData['stop_promised_time'] = $stop_promised_time;
                                  break;
    	                 }
    	             }
    	             unset($arrData['id']);
    	             //----------END 组装字段---------------
    	             //存储数据
    	             $id = $this->insert($arrData);
    	             if(!$id){
    	                 exception('第'.($key+1).'条数据插入失败，导致此次操作失败！！！');
    	             }
    	        }     
    	    }
    	    // 提交事务
    	    Db::commit();
    	    return true;	
	    }catch (\Exception $e){
	        // 回滚事务
	        Db::rollback();
	        return $e->getMessage();
	    }
	    
	}
	
	//
	public function customerApi(){
	    
  /*   	    'type_name' =>'',
    	    'tg_id' =>'',
    	    'visitors_name' =>'',
    	    'visitors_phone' =>'',
    	    'dialogue_channel' =>'',
    	    'customer_id' =>'',
    	    'customer_name' =>'',
    	    'start_time' =>'',
    	    'call_wait_time_text' =>'',
    	    'call_wait_time' =>'',
    	    'call_time' =>'',
    	    'content' =>'',
    	    'frist_response_time' =>'',
    	    'average_response_time' =>'',
    	    'visitor_send_num' =>'',
    	    'reply_num' =>'',
    	    'promised_time' =>'',
    	    'stop_promised_time' =>'',
    	    'dialogue_title' =>'',
    	    'dialogue_level' =>'',
    	    'dialogue_remark' =>'',
    	    'service_quality' =>'',
    	    'service_evaluation' =>'',
    	    'visitors_region
    	    'visitors_ip
    	    'search_engine
    	    'keyword
    	    'url
    	    'page_url
    	    'dialogue_url
    	    'system
    	    'resolution
    	    'browser
    	    'groupingId */
	    
	} 
	 
	
}
