<?php
namespace application\common\model;
/**
 * 银行业务处理
 */
class Banks extends Base{
	/**
	 * 列表
	 */
	public function listQuery(){
		$data = cache('MBIS_BANKS');
		if(!$data){
			$data = $this->where('dataFlag',1)->field('bankId,bankName')->select();
			cache('MBIS_BANKS',$data,31536000);
		}
		return $data;
	}
}
