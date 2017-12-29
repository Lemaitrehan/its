<?php
namespace application\admin\model;
use think\Db;
class CustomerMessage extends Base{
	
    //列表页
    public function listPage(){
        
        $where = array();
        if( input('search_title') && input('search_word')  ){
            $search_title = input('search_title');
            $search_word  = input('search_word');
            switch ($search_title){
                case 1:
                   $where['name'] = $search_word;
                  break;
                case 2:
                    $where['mobile_phone'] = $search_word;
                  break;
            }
        }
        
        $res = $this->field('*,FROM_UNIXTIME(time) as time')
                    ->where($where)
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
	    $info = $file->validate(['size'=>256780,'ext'=>'xlsx,xls,csv'])->move(ROOT_PATH . 'public' . DS . 'upload'.'\generalize\exel',true,true);
	    if($info){
	        $file = ROOT_PATH . 'public' . DS . 'upload'.'/generalize/exel/'.$info->getSaveName();
	    }else{
	        return $msg =  $file->getError();
	    }
	
	    set_time_limit(0);
	    import('phpexcel.PHPExcel.IOFactory');
	    $reader = \PHPExcel_IOFactory::createReader('Excel2007');
	    $objReader = \PHPExcel_IOFactory::load($file);
	
	    $objReader->setActiveSheetIndex(1);
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
	        'ID',	
	        '时间',	
	        '主题',
	        '留言内容',
	        '姓名',
	        '邮箱',
	        'QQ',
	        '电话',
	        '手机',
	        '自定义1',
	        '自定义2',
	        'ip',	
	        '地理位置',
	        '来源',	
	        '搜索引擎',	
	        '关键词',	
	        '留言页面',
	        '留言给客服',
	        '留言给分组',
	        '问题类别',
	        '留言原因',
	    );
	    $arrField = array(
            'id',
            'time',
            'title',
            'content',
            'name',
            'mail',
            'qq',
            'phone',
            'mobile_phone',
            'custom1',
            'custom2',
            'ip',
            'site',
            'url',
	        'search_engine',
            'keyword',
            'message_page',
            'message_to_customer',
            'message_to_grouping',
            'problem_type',
            'message_reason',
	    );
	    $timeKey = 1;//时间
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
    	                     //开始时间  
    	                     case $timeKey:
    	                           $start_time = strtotime($c);
    	                           if(!$start_time){
    	                               exception('第'.($key+1).'条数据【开始时间】 插入失败，导致此次操作失败！！！');
    	                           }
     	                           $arrData['time'] = $start_time;
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
	
	 
	
}
