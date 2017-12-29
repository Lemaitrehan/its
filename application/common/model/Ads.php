<?php
namespace application\common\model;
/**
 * 广告类
 */
class Ads extends Base{
	public function recordClick(){
		$id = (int)input('id');
		return $this->where("adId=$id")->setInc('adClickNum');
	}
}
