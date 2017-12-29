<?php
namespace application\api\controller;
use application\common\model\Orders as itsMOrder;
use application\common\model\Orders as M;
use application\common\model\PaymentFactory;
use think\Db;
use think\Request;
use think\Url;
/**
* 订单控制器
 */
class Orders extends Base{
    /**
     * @do 首页学院列表
     */
    public function create()
    {
        $order = new itsMOrder();
        $rs = $order->getApiCreateOrder(1,$this->getCurReqParams(),$this->getCurUserInfo());
		MBISApiReturn($rs);
    }
    /**
     * @do 创建部分订单。
     */
    public function createSupplementaryFee()
    {
        $order = new itsMOrder();
        $rs = $order->createSupplementaryFee($this->getCurReqParams(),$this->getCurUserInfo());
        MBISApiReturn($rs);
    }
    /**
     * @do 获取需要补费订单。
     */
    public function getSupplementaryFeeList()
    {
        $order = new itsMOrder();
        $rs = $order->getSupplementaryFeeList($this->getCurReqParams(),$this->getCurUserInfo());
        MBISApiReturn($rs);
    }
    /**
     * @do 检查补费订单。
     */
    public function checkoutSupplementaryFee(){
        $order = new itsMOrder();
        $rs = $order->checkoutSupplementaryFee(1,$this->getCurReqParams(),$this->getCurUserInfo());
        MBISApiReturn($rs);
    }
	public function getPayStatus(){
	    $payment_id = Request::instance()->param('payment_id');
	    $orderNo    = Request::instance()->param('orderNo');
	    $data = Db::name('payments')->where("payment_id='{$payment_id}' AND disabled='false' AND orderNo='{$orderNo}'")->find();
	    header("content-type:text/json");
	    //判断是否订单完成支付了，如果是，付款单支付将无效。
	    if(!$data){
	        $arr = ['status'=>-1,'msg'=>'订单不存在。','data'=>null];
	        print_r(json_encode($arr,JSON_UNESCAPED_UNICODE));
	        exit;
	    }
	    if($data['status'] == 'succ'){
	        $arr = ['status'=>1,'msg'=>'订单已经支付成功','data'=>['payed'=>1]];
	        print_r(json_encode($arr,JSON_UNESCAPED_UNICODE));
	        exit;
	    }
	    $arr = ['status'=>1,'msg'=>'还没有支付成功','data'=>['payed'=>0]];
        print_r(json_encode($arr,JSON_UNESCAPED_UNICODE));
        exit;
	}
	/**
	 * 在线支付。
	 */
	public function dopay(){
	    
	    $payment_id = Request::instance()->param('payment_id');
	    $orderNo    = Request::instance()->param('orderNo');
	     
	    $where = ["dataFlag"=>1,"payStatus"=>1,"payType"=>1,'orderNo'=>$orderNo];
	    $is_pay = Db::name('orders')->where($where)->field('orderId')->find();
	    if($is_pay){
	        $arr = ['status'=>-1,'msg'=>'订单已支付。','data'=>null];
	        print_r(json_encode($arr,JSON_UNESCAPED_UNICODE));
	        exit;
	    }
	    
	    $data = Db::name('payments')->where("payment_id='{$payment_id}' AND disabled='false' AND `status` in ('ready','progress') AND orderNo='{$orderNo}'")->find();
	    header("content-type:text/json");
	    //判断是否订单完成支付了，如果是，付款单支付将无效。
	    if(!$data){
	        $arr = ['status'=>-1,'msg'=>'订单不存在。','data'=>null];
	        print_r(json_encode($arr,JSON_UNESCAPED_UNICODE));
	        exit;
	    }
	    $data['orderNo'] = $payment_id;
		$rs = PaymentFactory::getInstance()->dopay($data['pay_app_id'],$data,$msg);
		if($rs){
            //exit; 
		    //更新付款单状态，进入支付中。
		    Db::startTrans();
		    try{
		      Db::name('payments')->where("payment_id='{$payment_id}'")->update(['status'=>'progress','t_begin'=>time()]);
		      //
		      $rss = [
		          
		          'qrcode_url'=>Url::build('/api/Qrcode/create',null,'html',true)."?text=".$rs['code_url'],
		          'pay_code'=>$data['pay_app_id'],
		      ];
		      $arr = ['status'=>1,'msg'=>'','data'=>$rss];
		      Db::commit();
		    }catch(\Exception $e){
                dump($e->getMessage());
		      $arr = ['status'=>-1,'msg'=>"发起支付失败",'data'=>null];
		      Db::rollback();
		    }
		    
		}else{
		    $arr = ['status'=>-1,'msg'=>$msg,'data'=>null];
		}
		print_r(json_encode($arr,JSON_UNESCAPED_UNICODE));
		exit;
	}
    /**
     @ 动态生成协议
    */
    public function make_statement_tmpl()
    {
        $data = input('get.');
        $data['year'] = date('Y');
        $data['month'] = date('n');
        $data['day'] = date('j');
        #$key = 'skill_notfull';
        #学历
        $data['jump_type']==1 && $data['order']['orderType']==1 && $key = 'edu_full';
        $data['jump_type']==1 && $data['order']['orderType']==2 && $key = 'edu_notfull';
        #技能
        $data['jump_type']==2 && $data['order']['orderType']==1 && $key = 'skill_full';
        $data['jump_type']==2 && $data['order']['orderType']==2 && $key = 'skill_notfull';
        #$key = 'skill_notfull';
        $tmpl = get_statement_tmpl($key);
        $data['order']['signComPicUrl'] = $tmpl['signcompany'];
        $this->assign('data',$data);
        return $this->fetch($tmpl['path']);
    }
}
