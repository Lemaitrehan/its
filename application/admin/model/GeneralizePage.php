<?php
namespace application\admin\model;
use think\Db;
class GeneralizePage extends Base{
    public $arrTitle = array(
        0=>'百度推广计划模板 ',
        1=>'百度账户模板 ',
        3=>'趋势分析',
        4=>'全部来源',
        5=>'受访域名',
        6=>'受访页面1',
        7=>'受访页面2',
        8=>'在线客服',
        9=>'客户留言',
       10=>'流量概况',
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
        
        if( input('create_start') || input('create_end') ){
            if(input('start')){
                $where['create_time']    = ['>=',strtotime(input('create_start'))];
            }
            if(input('end')){
                $where['create_time']    = ['<=',strtotime( input('create_end') )];
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
        //
        if( input('file_name') ){
            $file_name = input('file_name');
            $where['file_path']    = ['like','%'.$file_name.'%'];
        }
        
        $type = input('type');
        if(!isset($type)){
            $type = 0;
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
	public function importUsers($file,$t1,$t2,$type){
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
	    
	   switch ($type){
	      //趋势分析
	      case 3:
	          $arrTitle = array('日期','点击量','点击率','浏览量(PV)','访客数(UV)','跳出率','平均访问时长');
	          $arrField = array(1=>'time_slot',2=>'click_volume',3=>'click_rate',4=>'browse_volume',5=>'visitor_num',6=>'bounce_rate',7=>'average_visitor_time',);
	        break;
	      //推广来源  
	      case 4:
	          $arrTitle = array('来源类型','浏览量(PV)','访客数(UV)','IP数','跳出率','平均访问时长');
	          $arrField = array(1=>'source_type',2=>'pv',3=>'uv',4=>'ip_num',5=>'bounce_rate',6=>'average_access_time',);
	         break; 
	      case 5:
        	   $arrTitle = array('域名','浏览量(PV)','访客数(UV)','IP数','跳出率','平均停留时长');
        	   $arrField = array(1=>'domain_url',2=>'pv',3=>'uv',4=>'ip',5=>'back_pag_rate',6=>'average_access_time',);
	         break;
	      case 6:
               $arrTitle = array(
                     '页面URL','浏览量(PV)','访客数(UV)','贡献下游浏览量','退出页次数','平均停留时长'
               );
               $arrField = array(1=>'page_url',2=>'pv',3=>'uv',4=>'down_view_num',5=>'back_page_num',
                   6=>'average_access_time',);
             break;
          case 7:
               $arrTitle = array('页面URL','浏览量(PV)','访客数(UV)','退出页次数','退出率');
                  $arrField = array(
                      1=>'page_url',
                      2=>'pv',
                      3=>'uv',
                      4=>'back_page_num',
                      5=>'back_pag_rate',
                  );
             break;
          case 10:
                $arrTitle = array('时段','访问量','浏览量(PV)','平均页面数','平均停留时间','跳出率');
                $arrField = array('time_slot','visitor_num','pv','average_page_num','average_access_time','bounce_rate');
             break;
	    }
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
	            'type'=>$type 
	        );
	        $generalize->insert($data);
	        $id1 = $generalize->getLastInsID();
    	    foreach ($arrContent as $key => $v ){
    	        if($type == 10){
    	            $this->templateFlow($type, $key, $v, $arrTitle, $arrField, $id1);
    	        }else{
    	            $this->templateTotal($key, $v, $arrTitle, $arrField, $id1, $type);
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
	
	//百度推广
	function templateTotal($key,$v,$arrTitle,$arrField,$id1,$type){
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
	                //推广趋势
	                if($type == '3'){
	                    $affow_id = db::name('generalize_trend')->insert($arrData);
	                    //推广来源
	                }elseif ($type == '4'){
	                    $affow_id = db::name('generalize_source')->insert($arrData);
	                    //受访页面
	                }else{
	                    $affow_id = $this->insert($arrData);
	                }
	    
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
	        //推广趋势
	        if($type == '3'){
	            $affow_id = db::name('generalize_trend')->insert($arrData);
	            //推广来源
	        }elseif ($type == '4'){
	            $affow_id = db::name('generalize_source')->insert($arrData);
	            //受访页面
	        }else{
	            $affow_id = $this->insert($arrData);
	        }
	        if(!$affow_id){
	            exception('请按照约定的数据模板导入5555！！！');
	        }
	    }
	}
	
	//流量
	
	function templateFlow($type,$key,$v,$arrTitle,$arrField,$id1){
           if( $key == 1 ){
                foreach ($v as $k1 => $t){
                    if($k1==0){
                        continue;
                    }
                    if( $arrTitle[$k1] != $t){
                        exception('请按照约定的数据模板导入3！！！');
                    }
                }
           }else{
               $arrData = array();
               //----------组装字段---------------
               foreach ($v as $k2 => $c){
                   $arrData[$arrField[$k2]] = $c;
               }
               $arrData['req_id'] = $id1;
               //推广趋势
               $affow_id = db::name('generalize_flow')->insert($arrData);
               if(!$affow_id){
                   exception('请按照约定的数据模板导入5555！！！');
               }
           }
	}
	
	//导入
	function import($type){
	    
	    $fileName = $_FILES['exel']['name'];
	    $str = '-';
	    if($type == 6 ){
	       $start =  mb_stripos($fileName,'_' )+1;
	       $length = mb_stripos($fileName,'(' )-$start;
	       $time =  mb_substr($fileName,$start,$length) ;
	    }elseif( $type == 10 ){
	        $str = '_';
	        $start =  mb_stripos($fileName,'_' )+1;
	        $length = mb_stripos($fileName,'.' )-$start;
	        $time =  mb_substr($fileName,$start,$length) ;
	    }else{
	       $time =  mb_substr($fileName,mb_stripos($fileName,'_' )+1,-4 ) ;
	    }
	    if(!$time){
	        return ('请按照约定的文件模板上传');
	    }
	    $arr = explode($str, $time);
	    if(!$arr){
	        return ('请按照约定的文件模板上传');
	    }
	    $t1 = strtotime($arr[0]);
	    $t2 = strtotime($arr[0]);
	    if(!($t1 && $t2 ) ){
	        return ('请按照约定的文件模板上传');
	    }
	    
	    $file = request()->file('exel');
	    if($file == null ){
	        return ('请选择上传文件');
	    }
	    // 移动到框架应用根目录/public/uploads/目录下
	    $info = $file->rule('uniqid')->validate(['size'=>256780,'ext'=>'xlsx,xls,csv'])->move(ROOT_PATH . 'public' . DS . 'upload'.'/generalize/exel','',true);
	    
	    if($info){
	        $file = ROOT_PATH . 'public' . DS . 'upload'.'/generalize/exel/'.$info->getSaveName();
	        $res =  $this->importUsers($file,$t1,$t2,$type);
	        if((int)$res){
	           return true;
	        }else{
	            return $res;
	        }
	    }else{
	        // 上传失败获取错误信息
	        $msg =  $file->getError();
	        return $msg;
	    }
	}
	
	
}
