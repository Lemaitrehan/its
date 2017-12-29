<?php
namespace application\api\controller;
use application\api\model\Qrcode as M;
use think\Loader;
use think\Request;
/**
* 二维处理类
 */
class Qrcode extends Base{
    public function demo(){
		$m = new M();
        $m->demo();
	}
	public function create(){
	    Loader::import('phpqrcode.phpqrcode');
	    $data = Request::instance()->get('text');
	    // 纠错级别：L、M、Q、H
	    $level = 'Q';
	    // 点的大小：1到10,用于手机端4就可以了
	    $size = 8;
	    // 下面注释了把二维码图片保存到本地的代码,如果要保存图片,用$fileName替换第二个参数false
	    //$path = "images/";
	    // 生成的文件名
	    //$fileName = $path.$size.'.png';
	    \QRcode::png($data, false, $level, $size,2);exit;
	}
}
