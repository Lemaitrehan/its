<?php
namespace application\common\model;

class JrRegion extends Base
{
    public function add_str($region)
    {
        $regionArr = explode(',',$region);
        $regionStr = '';
        $i = 0;
        foreach ($regionArr as $k=>$v){
            $reginName = $this->where(array('region_id'=>$v))->find();
            if($k == $i){
                $regionStr .= $reginName['region_name'];
            }else{
                $regionStr .= '-'.$reginName['region_name'];
            }
        }
        return $regionStr;
    }
}