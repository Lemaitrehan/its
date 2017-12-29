<?php 
namespace application\common\model;
use think\Db;
use think\Loader;
/**
* 导入类
 */
class Import{
    //格式化数据
	public function get_xls_data($params=[],&$msg='')
    {
        if(empty($params['letter'])) for($letter=[],$i=65;$i<91;$i++){ $letter[] = strtoupper(chr($i));}
        $letter = !empty($params['letter'])?$params['letter']:$letter;
        $path = $params['path'];
        $mdl = !empty($params['mdl'])?$params['mdl']:'';
        $start = !empty($params['start'])?$params['start']:1;
        $fields = !empty($params['fields'])?$params['fields']:$letter;
        Loader::import('phpexcel.PHPExcel.IOFactory');
		$objReader = \PHPExcel_IOFactory::load($path);
		$objReader->setActiveSheetIndex(0);
		$sheet = $objReader->getActiveSheet();
		$rows = $sheet->getHighestRow();
		$cells = $sheet->getHighestColumn();
        $readData = [];
        for ($row = $start; $row <= $rows; $row++){
            $data = [];
            for($j=0;$j<count($fields);$j++)
            {
                if(ord($letter[$j]) > ord($cells)) continue;
                $data[$fields[$j]] = trim($sheet->getCell($letter[$j].$row)->getValue());
            }
            !empty($data[$fields[0]]) && $readData[] = $data;
        }
        $header_row = array_shift($readData);
        $returnData = ['header'=>$header_row,'data'=>$readData];
        //结合原有数据做过滤重复处理
        $method = 'filter_repeat_data';
        !empty($mdl) && method_exists(model($mdl),$method) 
        && model($mdl)->$method($returnData);
        !empty($returnData['data']) && sort($returnData['data']);
        if(empty($returnData['data'])) return $returnData;
        /**
            @do 数据再次格式化
            @参考mdl > format_import_xx方法
            @传入xls数据列表 array
        */
        foreach($fields as $field)
        {
            $method = 'format_import_'.$field;
            !empty($mdl) && method_exists(model($mdl),$method) 
            && model($mdl)->$method($returnData);
        }
        return $returnData;
    }
    //写入数据表
    public function put_2_tb($mdl,$data=[])
    {
        if(empty($data['data'][0])) return true;
        Db::startTrans();
        try{
            $data = $this->format_data_by_tb($mdl,$data);
            $result = Db::name($mdl)->insertAll($data);
            Db::commit();
            return $result;
        }catch (\Exception $e) {
			dump($e->getLine().' >> '.$e->getFile().' >> '.$e->getMessage());
            Db::rollback();
            return false;
        }
    }
    //格式化数据
    private function format_data_by_tb($mdl,$data=[])
    {
       $new_data = [];
       $field_default_vals = model('common/base')->getEModel($mdl);
       //踢掉主键
       if(isset($field_default_vals['id'])) unset($field_default_vals['id']);
       //对比出数据表存在的字段
       !empty($data['data'][0]) && $fields = array_intersect(array_keys($field_default_vals),array_keys($data['data'][0]));
       foreach($data['data'] as $k=>$v):
         $new_v = [];
         foreach($v as $kk=>$vv):
            in_array($kk,$fields) && $new_v[$kk] = $vv;
         endforeach;
         isset($field_default_vals['createtime']) && $new_v['createtime'] = time();
         isset($field_default_vals['lastmodify']) && $new_v['lastmodify'] = time();
         $new_data[] = $new_v;
       endforeach;
       return $new_data;
    }
}