<?php
namespace application\admin\model;
use think\Db;
class Generalizestatisticalsource extends Base{
	
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
                    $where['plan']    = $search_word;
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
        $where['type'] = 0;
        $join = array(
            array('staffs zh','zh.staffId = a.update_person_id','left')
        );
        $res = db::name('generalize_data_analyze')->alias('a')
                     ->join($join)
                     ->field('a.*,FROM_UNIXTIME(a.time) as time,FROM_UNIXTIME(a.update_time) as update_time,zh.staffName')
                    ->where($where)
                    ->paginate(input('post.pagesize/d'))
                    ->toArray();
        
        return   $res;
      
    }
	//导入数据
	public function importUsers($file,$t1,$t2){
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
	        '来源类型',
	        '浏览量(PV)',
	        '访客数(UV)',
	        'IP数',
	        '跳出率',
	        '平均访问时长'
	    );
	    $arrField = array(
            1=>'source_type',
            2=>'pv',
	        3=>'uv',
            4=>'ip_num',
            5=>'bounce_rate',
            6=>'average_access_time',
	    );
	    $time      = time();
	    $person_id = session('MBIS_STAFF')->staffId;
	    $generalize = db::name('generalize');
	    Db::startTrans();
	    try{
	        
	        $data = array(
	            'start_time'=>$t1,
	            'stop_time'=>$t2,
	            'create_person_id'=>$person_id,
	            'create_time'=>$time,
	            'file_path'=>$file,
	            'type'=>4 
	        );
	        $generalize->insert($data);
	        $id1 = $generalize->getLastInsID();
	        
    	    foreach ($arrContent as $key => $v ){
    	        //第一行标题
    	        if( $key <= 2 ){
    	             $arrData = array();
    	             switch ($key){
    	                 case 1:
    	                     foreach ($v as $k1 => $t){
    	                         if($k1==0){
    	                             continue;
    	                         }
        	                     if( $arrTitle[$k1-1] != $t){
        	                         exception('请按照约定的数据模板导入3！！！');
        	                     }
    	                     }
    	                    break;
    	                 //总计   
    	                 case 2:
    	                     $arrData = array();
    	                     foreach ($v as $k1 => $t){
    	                         if($k1==0){
    	                             continue;
    	                         }elseif($k1==1){
    	                             continue;
    	                         }
    	                         $arrData['type'] = 1;
    	                         $arrData[$arrField[$k1]] = $t;
    	                     }
    	                     $arrData['req_id'] = $id1;
    	                     $generalize_source = db::name('generalize_source');
    	                     $affow_id = $generalize_source ->insert($arrData);
    	                     if(!$affow_id){
    	                         exception('请按照约定的数据模板导入4！！！');
    	                     }
    	                    break; 
    	             }
    	        }else{
    	            
    	             $arrData = array();
    	             //----------组装字段---------------
    	             foreach ($v as $k2 => $c){
    	                 if($k2==0){ continue; };
    	                 $arrData[$arrField[$k2]] = $c;
    	             }
    	             $arrData['req_id'] = $id1;
    	             $trend    = db::name('generalize_source');
    	             $affow_id = $trend->insert($arrData);
    	             if(!$affow_id){
    	                 exception('请按照约定的数据模板导入5555！！！');
    	             }
    	        }     
    	    }
    	    // 提交事务
    	    Db::commit();
    	    return true;	
	    }catch (\Exception $e){
	        // 回滚事务
	        Db::rollback();
	        return   $e->getMessage();
	    }
	 
	}
	
	
}
