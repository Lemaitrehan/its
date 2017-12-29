<?php

namespace application\common\Component\Payment\Wxpay\Scan;
use application\common\interfaces\IPayment;
use think\Url;
use application\common\Component\Common\orderPay;
use think\Log;

class pay implements IPayment{

    private $notify_url = '';

    public function __construct(){
        //TODO 初始化 参数，默认不处理。
    }
    /* (non-PHPdoc)
     * @see \application\common\interfaces\IPayment::return_back()
     */
    public function return_back($pay_id, $callback_data, &$msg)
    {
        // TODO Auto-generated method stub
        //忽略处理。
    }

    /**
     * 支付后返回后处理的事件的动作
     * @params array - 所有返回的参数，包括POST和GET
     * @return null
     */
    public function callback($pay_id, $callback_data, &$msg)
    {
		$recv = $_POST;
		$recv = $recv?$recv:file_get_contents("php://input");
		$recv = $recv?$recv:@$GLOBALS['HTTP_RAW_POST_DATA'];
		$recv = $this->xmlToArray($recv);
		/*$recv = array (
		    'appid' => 'wx8b91af098c81c3af',
		    'bank_type' => 'CFT',
		    'cash_fee' => '4',
		    'fee_type' => 'CNY',
		    'is_subscribe' => 'N',
		    'mch_id' => '1328325201',
		    'nonce_str' => '152a11c0ac4d756',
		    'openid' => 'o7YZht1OemPyVavQmQNC0z2RAEfw',
		    'out_trade_no' => '1001011',
		    'result_code' => 'SUCCESS',
		    'return_code' => 'SUCCESS',
		    'sign' => '6DB4475C5C4803FBA68BB8D394EE5C3E',
		    'time_end' => '20151127115816',
		    'total_fee' => '4',
		    'trade_type' => 'APP',
		    'transaction_id' => '1002580900201511271787929751',
		);
		*/
		//{"appid":"wx8b91af098c81c3af","bank_type":"CFT","cash_fee":"1","fee_type":"CNY","is_subscribe":"N","mch_id":"1328325201","nonce_str":"234016","openid":"ofbjMwhS7ZdZlrwYRs0pfgE0XAQ4","out_trade_no":"201703151556339388","result_code":"SUCCESS","return_code":"SUCCESS","sign":"AC4B9700D2363FB855F6AB01E13338DD","time_end":"20170315161117","total_fee":"1","trade_type":"NATIVE","transaction_id":"4009372001201703153453649389"}
		//写日志
		$log = function($recv){
		    //TODO 自行配置notify 通知信息。和post数据记录
		    Log::record(date('时间：Y-m-d H:i:s ,').'微信支付notify参数:【'.json_encode($recv,JSON_UNESCAPED_UNICODE).'】');
		};
		$log($recv);
		
        //1.生成付款订单的状态更新和查询。
        //2.针对付款订单的科目（订单详细）、订单主表更新数据。
        if($recv['result_code'] !== 'SUCCESS'||$recv['result_code'] !== 'SUCCESS'){
            exit($this->ToXml(array('return_code'=>'FAIL', 'return_msg'=>'订单支付失败' . " " . $msg ."")));
        }
        
        //核对商户id是否一致及appid是否一致。
        $fc = function(){
            //TODO 自行配置
            //TODO 要求配置参数返回是数组，必须包含app_id,mch_id;
            //
            return config('wxpay_scan');
        };
        $config = $fc();
        if(@$config['app_id'] !=$recv['appid']||@$config['mch_id']!=$recv['mch_id']){
            exit($this->ToXml(array('return_code'=>'FAIL', 'return_msg'=>'x订单支付失败' . " " . $msg ."")));
        }
        
        
        //更新数据表。
        $func = function($recv,&$msg){
            //1、更新付款单
            //2、更新详细订单
            //3、更新主表。
            //TODO object update data add by zgh.
            //TODO add object eg: $order->pay($recv,'wxpay',$msg);
            //TODO 查询订单数据需要调整，注意。
           return orderPay::getSelf()->updateOrderByPay($recv,['key'=>'wxpay','alias'=>'scan'], $msg);
        };
        
        $rs = $func($recv,$msg);
        $log = function($rs,$msg){
            
            //TODO 自行配置写日志。
            Log::record(date('时间：Y-m-d H:i:s ,微信支付提示:【').$msg.'】');
            Log::record(date('时间：Y-m-d H:i:s ,微信支付结果:').(!$rs?'【FAIL】':'【SUCCESS】'));
        };
        $log($rs,$msg);
        
        if(!$rs){
            exit($this->ToXml(['return_code'=>'FAIL', 'return_msg'=>$msg]));
        }
        exit($this->ToXml(['return_code'=>'SUCCESS', 'return_msg'=>'OK']));
    }

    /* (non-PHPdoc)
     * @see \application\common\interfaces\IPayment::dopay()
     */
    public function dopay($pay_id, $order_data, &$msg)
    {
        //TODO Auto-generated method stub
        $this->makeNotify($order_data['orderNo']);

        $return = array();
        $wxpay_config = config('wxpay_scan');
        $app_id  = $wxpay_config['app_id'];
        $mer_id  = $wxpay_config['mch_id'];
        $mer_key = $wxpay_config['mch_key'];

        //基本参数
        $return['appid']            = $app_id;
        $return['mch_id']           = $mer_id;
        $return['nonce_str']        = rand(100000,999999);
        $return['body']             = '微信支付';
        $return['out_trade_no']     = $order_data['orderNo'];
        $return['total_fee']        = $order_data['money']*100;
        $return['spbill_create_ip'] = $this->getClientIP();
        $return['notify_url']       = $this->notify_url;
        $return['trade_type']       = 'NATIVE';
        //除去待签名参数数组中的空值和签名参数
         
        $para_filter = $this->paraFilter($return);
        //对待签名参数数组排序
        $para_sort = $this->argSort($para_filter);

        //生成签名结果
        $mysign = $this->buildMysign($para_sort, $mer_key);
        //签名结果与签名方式加入请求提交参数组中
        $return['sign'] = $mysign;

        $xmlData = $this->converXML($return);

        $result  = $this->curlSubmit($xmlData);

        //进行与支付订单处理
        $resultArray = $this->converArray($result);
        //处理正确
        if(isset($resultArray['return_code']) && $resultArray['return_code'] == 'SUCCESS')
        {
            if(isset($resultArray['result_code']) && $resultArray['result_code'] == 'SUCCESS')
            {
                $resultArray['key'] = 'OK';//$order_data['key'];
                return $resultArray;
            }
        }

        //处理错误
        if(isset($resultArray['err_code']) && isset($resultArray['err_code_des']))
        {
            $msg = ($resultArray['err_code'].":".$resultArray['err_code_des']);
            return false;
        }
        return null;
        //生成二维码模式。
        //echo 'Component';
    }
    private function makeNotify($order_id){
        $this->notify_url =Url::build('/api/pay/callback/type/2',"id={$order_id}",'html',true);
    }
    /**
     * @see paymentplugin::getSubmitUrl()
     */
    public function getSubmitUrl()
    {
        return 'https://api.mch.weixin.qq.com/pay/unifiedorder';
    }
    /**
     * @brief 提交数据
     * @param xml $xmlData 要发送的xml数据
     * @return xml 返回数据
     */
    private function curlSubmit($xmlData)
    {
        //接收xml数据的文件
        $url = $this->getSubmitUrl();

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/xml', 'Content-Type: application/xml'));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlData);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }
    /**
     * @brief 从array到xml转换数据格式
     * @param array $arrayData
     * @return xml
     */
    private function converXML($arrayData)
    {
        $xml = '<xml>';
        foreach($arrayData as $key => $val)
        {
            $xml .= '<'.$key.'><![CDATA['.$val.']]></'.$key.'>';
        }
        $xml .= '</xml>';
        return $xml;
    }

    /**
     * @brief 从xml到array转换数据格式
     * @param xml $xmlData
     * @return array
     */
    private function converArray($xmlData)
    {
        $result = array();
        $xmlHandle = xml_parser_create();
        xml_parse_into_struct($xmlHandle, $xmlData, $resultArray);

        foreach($resultArray as $key => $val)
        {
            if($val['tag'] != 'XML')
            {
                $result[$val['tag']] = $val['value'];
            }
        }
        return array_change_key_case($result);
    }
    /**
     * 生成签名结果
     * @param $sort_para 要签名的数组
     * @param $key 支付宝交易安全校验码
     * @param $sign_type 签名类型 默认值：MD5
     * return 签名结果字符串
     */
    private function buildMysign($sort_para,$key,$sign_type = "MD5")
    {
        //把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
        $prestr = $this->createLinkstring($sort_para);
        //把拼接后的字符串再与安全校验码直接连接起来
        $prestr = $prestr.'&key='.$key;
        //把最终的字符串签名，获得签名结果
        $mysgin = md5($prestr);
        return strtoupper($mysgin);
    }

    /**
     * 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
     * @param $para 需要拼接的数组
     * return 拼接完成以后的字符串
     */
    private function createLinkstring($para)
    {
        $arg  = "";
        foreach($para as $key => $val)
        {
            $arg.=$key."=".$val."&";
        }

        //去掉最后一个&字符
        $arg = trim($arg,'&');

        //如果存在转义字符，那么去掉转义
        if(get_magic_quotes_gpc())
        {
            $arg = stripslashes($arg);
        }

        return $arg;
    }

    /**
     * 对数组排序
     * @param $para 排序前的数组
     * return 排序后的数组
     */
    private function argSort($para)
    {
        ksort($para);
        reset($para);
        return $para;
    }
    /**
     * 除去数组中的空值和签名参数
     * @param $para 签名参数组
     * return 去掉空值与签名参数后的新签名参数组
     */
    private function paraFilter($para)
    {
        $para_filter = array();
        foreach($para as $key => $val)
        {
            if($key == "sign" || $key == "sign_type" || $val == "")
            {
                continue;
            }
            else
            {
                $para_filter[$key] = $para[$key];
            }
        }
        return $para_filter;
    }
    private function getClientIP()
    {
        global $ip;
        if (getenv("HTTP_CLIENT_IP"))
            $ip = getenv("HTTP_CLIENT_IP");
        else if(getenv("HTTP_X_FORWARDED_FOR"))
            $ip = getenv("HTTP_X_FORWARDED_FOR");
        else if(getenv("REMOTE_ADDR"))
            $ip = getenv("REMOTE_ADDR");
        else $ip = "Unknow";
        return $ip;
    }
    /**
     * 输出xml字符
     **/
    public function ToXml($values = array())
    {
        if(!is_array($values)
            || count($values) <= 0)
        {
            die("数组数据异常！");
        }
         
        $xml = "<xml>";
        foreach ($values as $key=>$val)
        {
            if (is_numeric($val)){
                $xml.="<".$key.">".$val."</".$key.">";
            }else{
                $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
            }
        }
        $xml.="</xml>";
        return $xml;
    }
    /**
     * xml转换数组
     */
    private function xmlToArray($xml)
    {
    
        if(!$xml){
            exit($this->ToXml(array('return_code'=>'FAIL', 'return_msg'=>"数据传递失败")));
            //throw new Exception("xml数据异常！");
        }
        //将XML转为array
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $values = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $values;
    }

}