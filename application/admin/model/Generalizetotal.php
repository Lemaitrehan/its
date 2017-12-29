<?php
namespace application\admin\model;
use think\Db;
class Generalizetotal extends Base{
	
    //列表页
    public function listPage(){
        
        $where = array();
        if( input('search_title') && input('search_word')  ){
            $search_title = input('search_title');
            $search_word  = input('search_word');
            switch ($search_title){
                case 1:
                    $where['account'] = $search_word;
                  break;
                case 2:
                  break;
            }
        }
        
        if( input('start') || input('end')){
            if(input('start')){
                $where['time']    = ['>=',strtotime(input('start'))];
            }
            if(input('end')){
                $where['time']    = ['<=',strtotime( input('end') )];
            }
        }
        
        $join = array(
            array('staffs zh','zh.staffId = a.update_person_id','left')
        );
        $where['a.type'] = 1;
        $res = db::name('generalize_data_analyze')->alias('a')
                     ->join($join)
                     ->field('a.*,FROM_UNIXTIME(a.time) as time,FROM_UNIXTIME(a.update_time) as update_time,zh.staffName')
                    ->where($where)
                    ->paginate(input('post.pagesize/d'))
                    ->toArray();
        return   $res;
      
    }
	//导入数据
	public function importUsers(){
	    
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
               '日期','账户','展现','点击',
	           '消费','点击率','平均点击价格','网页转化','商桥转化','电话转化'
	    );
	    $arrField = array(
	        'time',
	        'account',
	        'show_num',
	        'click_num',
	        'consume',
	        'click_rate',
	        'average_price',
	        'web_page_conversion',
	        'business_conversion',
	        'phone_conversion',
	    );
	    $timeKey   = 0;//时间
	    $time      = time();
	    $person_id = session('MBIS_STAFF')->staffId;
	    Db::startTrans();
	    try{
    	    foreach ($arrContent as $key => $v ){
    	        
    	        //第一行标题
    	        if( $key <= 7 ){
    	             $res = $v[0];
    	             switch ($key){
    	                 //数据生成时间 
    	                 case 1:
    	                     $sx = mb_stripos($res,':');
    	                     if(!$sx){
    	                         exception('第'.($key+1).'条数据 读取失败,请按照正确模板导入！！！');
    	                     }
    	                     $aa1 = mb_substr($res,0,$sx);
    	                     if(!$aa1){
    	                         exception('第'.($key+1).'条数据 读取失败,请按照正确模板导入！！！');
    	                     }
    	                     $aa2 = mb_substr($res,$sx+1);
    	                     if(!$aa2){
    	                         exception('第'.($key+1).'条数据 读取失败,请按照正确模板导入！！！');
    	                     }
    	                     $start_time = strtotime($aa2);
    	                     if(!$start_time){
    	                         exception('第'.($key+1).'条数据 读取失败,请按照正确模板导入！！！');
    	                     }
    	                     $data['generate_data'] = $start_time;
    	                    break;
    	                 //时间范围   
    	                 case 3:
    	                     $sx       = mb_stripos($res,'：');
    	                     $timeStr  = mb_substr($res,$sx+1);
    	                     $arrTime  = explode('至', $timeStr);
    	                     $data['start_time'] = strtotime($arrTime[0]);
    	                     $data['stop_time']  = strtotime($arrTime[1]);
    	                     if( !($data['start_time'] && $data['stop_time']) ){
    	                         exception('第'.($key+1).'条数据 读取失败,请按照正确模板导入！！！');
    	                     }
    	                    break;
    	                 //时间单位   
    	                 case 4:
    	                     $data['unit'] = mb_substr($res,mb_stripos($res,'：')+1);
    	                     if(!$data['unit']){
    	                         exception('第'.($key+1).'条数据 读取失败,请按照正确模板导入！！！');
    	                     }
    	                    break;
    	                 //推广设备   
	                     case 5:
	                         $data['generalize_device'] = mb_substr($res,mb_stripos($res,'：')+1);
	                         if(!$data['generalize_device']){
	                             exception('第'.($key+1).'条数据 读取失败,请按照正确模板导入！！！');
	                         }
	                        break;
	                     //投放网络   
                         case 6:
                             $data['put_network'] = mb_substr($res,mb_stripos($res,'：')+1);
                             if(!$data['put_network']){
                                exception('第'.($key+1).'条数据 读取失败,请按照正确模板导入！！！');
                             }
                            break;
                         //投放物料   
                         case 7:
                             $data['put_material'] = mb_substr($res,mb_stripos($res,'：')+1);
                             if(!$data['put_material']){
                                 exception('第'.($key+1).'条数据 读取失败,请按照正确模板导入！！！');
                             }
                            break;
    	             }
    	             //存储主表
    	             if($key == 7){
        	             $data['create_person_id'] = session('MBIS_STAFF')->staffId;
        	             $data['create_time']      = time();
        	             $data['file_path']        = $file;
        	             $data['type']             = 1;
        	             $generalize  = db::name('generalize');
        	             $generalize->insert($data);
        	             $id1 = $generalize->getLastInsID();
        	             if(!$id1){
        	                 exception('模板主数据导入失败！！！');
        	             }
    	             } 
    	        }else{
    	            
    	            if($key == 8){
    	                continue;
    	            }
    	             $arrData = array();
    	             //----------组装字段---------------
    	             foreach ($v as $key2 => $c){
    	                 if( $key == 9 ){
    	                     if( $arrTitle[$key2] != $c ){
    	                         exception('第'.($key+1).'模板数据错误，导致此次操作失败！！！');
    	                     }
    	                 }else{
        	                 $arrData[ $arrField[$key2] ] = $c;
        	                 switch ($key2){
        	                     //开始时间  
        	                     case $timeKey:
        	                           $start_time = excelTime($c);
        	                           $start_time = strtotime($start_time);
        	                           if( !( $start_time>= $data['start_time'] && $start_time <= $data['stop_time'] ) ) {
        	                               exception('第'.($key+1).'条数据【日期时间不得大于主题的日期时间】 插入失败，导致此次操作失败！！！');
        	                           }
        	                           if(!$start_time){
        	                               exception('第'.($key+1).'条数据【开始时间】 插入失败，导致此次操作失败！！！');
        	                           }
         	                           $arrData['time'] = $start_time;
        	                       break;
        	                 }
    	                 }
    	             }
    	             
    	             if( $key > 9 ){
    	                 $generalize_data_analyze = db::name('generalize_data_analyze');
        	             unset($arrData['id']);
        	             $arrData['req_id'] = $id1;
        	             $arrData['update_time']      =  $time;
        	             $arrData['update_person_id'] =  $person_id;
        	             $arrData['type']             =  1;
        	             //----------END 组装字段---------------
        	             //如果导入的数据不存在当天的
        	             if( date('Y-m-d',$time) == date('Y-m-d',$arrData['time']) ){
        	                 $where = array();
        	                 $where['time']    = strtotime(date('Y-m-d',$arrData['time']));
        	                 $where['account'] = $arrData['account'];
        	                 $where['type']    = 1;
        	                 $old_id = $generalize_data_analyze->where($where)->value('id');
        	                 if($old_id){
        	                     $affow_id = $generalize_data_analyze->where('id='.$old_id)->update($arrData);
        	                     if(!$affow_id){
        	                         exception('第'.($key+1).'条数更新数据错误，导致此次操作失败！！！');
        	                     }
        	                 }else{
        	                     //存储数据
        	                     $id = $generalize_data_analyze->insert($arrData);
        	                     if(!$id){
        	                         exception('第'.($key+1).'条数据插入失败，导致此次操作失败！！！');
        	                     }
        	                 }
        	             }else{
        	               
        	                 $where = array();
        	                 $where['time']    = strtotime(date('Y-m-d',$arrData['time']));
        	                 $where['account'] = $arrData['account'];
        	                 $where['type']    = 1;
        	                 $old_id = $generalize_data_analyze->where($where)->value('id');
        	                 if(!$old_id){
                	             //存储数据
                	             $id = $generalize_data_analyze->insert($arrData);
                	             if(!$id){
                	                 exception('第'.($key+1).'条数据插入失败，导致此次操作失败！！！');
                	             }
        	                 }
        	             }
    	             }
    	        }     
    	    }
    	    // 提交事务
    	    Db::commit();
    	    return true;	
	    }catch (\Exception $e){
	        // 回滚事务
	        Db::rollback();
	        echo  $e->getMessage();
	    }
	 
	}
	
	 
	
}
