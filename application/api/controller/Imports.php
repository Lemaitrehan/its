<?php
namespace application\api\controller;
use application\api\model\Imports as M;
use application\common\model\Orders as Orders;
/**
* 默认控制器
 */
class Imports extends Base{
	/**
	 * 数据导入首页
	 */
	public function index(){
		return $this->fetch('shops/import');
	}
	
    /**
     * 上传商品数据
     */
    public function importGoods(){
    	$rs = MBISUploadFile();
		if(json_decode($rs)->status==1){
			$m = new M();
    	    $rss = $m->importGoods($rs);
    	    return $rss;
		}
    	return $rs;
    }
    
    public function importUsers(){
		$m = new M();
        $m->importUsers();
    }

    public function importEntrys(){
		$m = new M();
        $m->importEntrys();
    }
    
    public function make_entry_data()
   {
       $lists = require_once('./entry_data.php');
       $index = !empty($_GET['index'])?(int)$_GET['index']:0;
       //dump($lists[$index]);exit;
       if(empty($lists[$index])) exit('END...');
       $v = $lists[$index];
       if($v['totalMoney']>0)
       {
                $_POST = array (
  'accesstoken' => $v['userId'],
  'userId' => $v['userId'],
  'jump_type' => 1,
  'cartData' => 
  array (
    0 => array (
      'cartId' => 0,
      'type_id' => 1,
      'userId' => $v['userId'],
      'course_id' => $v['course_id'],
      'subject_id' => 0,
      'cartNum' => 1,
      'extend_data' => '',
      'adItemList' => 
      array (
      ),
      'is_full_pay' => '1',
      'add_deposit_price' => 0,
    ),
  ),
  'orderData' => 
  array (
    'orderInfo' => 
    array (
      'discountMoney' => $v['discountMoney'],
      'totalMoney' => $v['totalMoney'],
      'realTotalMoney' => $v['realTotalMoney'],
      'realPayMoney' => $v['realPayMoney'],
      'name' => $v['name'],
      'mobile' => $v['mobile'],
      'idcard' => $v['idcard'],
      'entry_time' => $v['entry_time'],
    ),
    'paymentInfo' => 
    array (
      2 => 4,
    ),
  ),
  'channelType' => 1,
  'nodelcart' => 1,
  'isImport' => 1,
);
$res = model('common/Orders')->getApiCreateOrder(2);
dump($res);
}
echo $index;
$index++;
echo '<script>setTimeout(function(){location="/index.php/api/imports/make_entry_data?index='.$index.'"},5000);</script>';   
   }

    public function importUserInfo(){
        $m = new M();
        $m->importUserInfo();
    }
    public function importFeeLog(){
        $m = new M();
        $m->importFeeLog();
    }
    public function importInfo(){
        $m = new M();
        $m->importInfo();
    }
    public function importExams(){
        $m = new M();
        $m->importExams();
    }
}
