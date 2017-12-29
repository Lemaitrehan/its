<?php
namespace application\api\controller;
use application\common\model\Suggest as MS;
/*
 *   学员建议
 * 
 * */
class Suggest extends Base{
    public function insertSuggest(){
        $m = new MS();
        $rs = $m->insertData();
        return MBISApiReturn($rs);
    }
}

