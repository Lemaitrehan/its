<?php
namespace application\admin\model;
use think\Db;
class Generalizestatistical extends Base{
    public $arrTitle = array(
        0=>'百度推广计划模板 ',
        3=>'趋势分析',
        4=>'全部来源',
        5=>'受访页面1',
        6=>'受访页面2',
        7=>'受访域名'
    );
    //列表页
    public function listPage(){
        
        $where = array();
      /*   if( input('search_title') && input('search_word')  ){
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
        } */
        if( input('yg') ){
            $yg = input('yg');
            $where['zh.staffName']    = ['like','%'.$yg.'%'];
        }
        if( input('start') || input('end')){
            if(input('start')){
                $where['time']    = ['>=',strtotime(input('start'))];
            }
            if(input('end')){
                $where['time']    = ['<=',strtotime( input('end') )];
            }
        }
        $type = input('type');
        if(!$type){
            $type = 3;
        }
        $where['type'] = $type;
        
        $join = array(
            array('staffs zh','zh.staffId = a.create_person_id','left')
        );
        $res = db::name('generalize')->alias('a')
                     ->join($join)
                     ->field('a.*,FROM_UNIXTIME(a.create_time) as create_time,zh.staffName')
                    ->where($where)
                    ->paginate(input('post.pagesize/d'))
                    ->toArray();
        $arrTitle  = $this->arrTitle;
        foreach ($res['Rows'] as $key => $v){
            $res['Rows'][$key]['start_time']  = date('Y-m-d',$v['start_time']);
            $res['Rows'][$key]['stop_time']   = date('Y-m-d',$v['stop_time']);
            $res['Rows'][$key]['title']       = $arrTitle[$v['type']];
            $a =  basename($v['file_path']);
            $b  = dirname($v['file_path']);
            $res['Rows'][$key]['path']       =  $b;
            $res['Rows'][$key]['file']       =  $a;
        }
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
	       '日期','点击量','点击率','浏览量(PV)','访客数(UV)','跳出率','平均访问时长'
	    );
	    $arrField = array(
            1=>'time_slot',
            2=>'click_volume',
            3=>'click_rate',
            4=>'browse_volume',
            5=>'visitor_num',
            6=>'bounce_rate',
            7=>'average_visitor_time',
	    );
	    $timeKey   = 0;//时间
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
	            'type'=>3 
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
        	                         exception('请按照约定的数据模板导入！！！');
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
    	                     $trend = db::name('generalize_trend');
    	                     $affow_id = $trend->insert($arrData);
    	                     if(!$affow_id){
    	                         exception('请按照约定的数据模板导入！！！');
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
    	             $trend    = db::name('generalize_trend');
    	             $affow_id = $trend->insert($arrData);
    	             if(!$affow_id){
    	                 exception('请按照约定的数据模板导入！！！');
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
