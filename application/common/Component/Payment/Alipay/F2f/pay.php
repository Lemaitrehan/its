<?php

namespace  application\common\Component\Payment\Alipay\F2f;
use application\common\interfaces\IPayment;
use application\common\Component\Common\orderPay;
use think\Log;

/**
 * 支付宝面对面支付
 * @author jodendy
 * @see application\common\Component\Payment\Alipay\F2f\pay
 * @package payment
 * @access public
 * 
 */
class pay implements IPayment{
    
    /* (non-PHPdoc)
     * @see \application\common\interfaces\IPayment::callback()
     */
    public function callback($pay_id, $callback_data, &$msg)
    {
        // TODO Auto-generated method stub
        //付款单号。
        $recv = $_POST;
        $recv = $recv?$recv:file_get_contents("php://input");
        
        $recv = '{
            "notify_id":"4c2c04c3cc50e978d44212febe7c3f0lse",
            "seller_email":"pay***@fangbei.org",
            "notify_type":"trade_status_sync",
            "sign":"R0iRdYmSQ0+zuSUGLzkutHcR40hoOp+CcKojVBCMa1uji3rqQFe5XeHoJB1nMBCApE3zXPKhXMdLis109ngPbGy+NUEBR7YZjYuR/hXq3WXeYfZ8aiWLvloZHrF7dQWxDho/VHYexaLeqvRi/03m0HxrwhZKUOu1eS9wMgZOlqQ=",
            "trade_no":"2016082621001004750241229810",
            "buyer_id":"2088002364008751",
            "app_id":"2016061501500000",
            "gmt_create":"2016-08-26 18:20:37",
            "out_trade_no":"1001011",
            "seller_id":"2088421202724253",
            "notify_time":"2016-08-26 18:20:37",
            "subject":"方倍工作室-支付宝-当面付-扫码支付",
            "trade_status":"TRADE_SUCCESS",
            "open_id":"20880044751374809757987911112575",
            "total_amount":"0.01",
            "sign_type":"RSA",
            "buyer_logon_id":"118***@qq.com"
        }';
        $recv = json_decode($recv,1);
        //写日志
        $log = function($recv){
            //TODO 自行配置notify 通知信息。和post数据记录
            Log::record(date('时间：Y-m-d H:i:s ,').'支付宝当面支付，notify参数:【'.json_encode($recv,JSON_UNESCAPED_UNICODE).'】');
        };
        $log($recv);
        
        //1.生成付款订单的状态更新和查询。
        //2.针对付款订单的科目（订单详细）、订单主表更新数据。
        if($recv['trade_status'] !== 'TRADE_SUCCESS'){
            exit('FAIL');
        }
        
        //$f2fpay = new F2fpay();
        //$f2fpay->query($recv['out_trade_no'],$recv['trade_no']);
        //更新数据表。
        $func = function($recv,&$msg){
            //1、更新付款单
            //2、更新详细订单
            //3、更新主表。
            //TODO object update data add by zgh.
            //TODO add object eg: $order->pay($recv,'wxpay',$msg);
            //TODO 查询订单数据需要调整，注意。
            return orderPay::getSelf()->updateOrderByPay($recv,['key'=>'alipay','alias'=>'f2f'], $msg);
        };
        $rs = $func($recv,$msg);
        
        $log = function($rs,$msg){
            //TODO 自行配置写日志。
            Log::record(date('时间：Y-m-d H:i:s ,支付宝当面支付，支付提示:【').$msg.'】');
            Log::record(date('时间：Y-m-d H:i:s ,支付宝当面支付，支付结果:').(!$rs?'【FAIL】':'【SUCCESS】'));
        };
        $log($rs,$msg);
        if(!$rs){
            exit('FAIL');
        }
        exit('SUCCESS');
    }

    /* (non-PHPdoc)
     * @see \application\common\interfaces\IPayment::dopay()
     */
    public function dopay($pay_id, $order_data, &$msg)
    {
        // TODO Auto-generated method stub
        //支付宝相关资料。
        //$pay = config('alipay_f2f');
        
        $f2fpay = new F2fpay();
        $response = 	$f2fpay->qrpay($order_data['orderNo'],  $order_data['realPayMoney'], "测试");
        //print_r($response);
        return $response;
        
    }

    /* (non-PHPdoc)
     * @see \application\common\interfaces\IPayment::return_back()
     */
    public function return_back($pay_id, $callback_data, &$msg){
        // TODO Auto-generated method stub
    }
    
}