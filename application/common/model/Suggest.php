<?php



namespace application\common\model;
use think\db;

class Suggest extends Base {  
    /*
     * 插入建议数据
     * 
     * */
    public function insertData(){
        $params = input('post.');
        $params['createTime'] = time();
        $this->data=$params;
        $rs = $this->save();
        if($rs){
            return MBISReturn('',1);
        }else{
            return MBISReturn('',-1);
        }
    }

}
