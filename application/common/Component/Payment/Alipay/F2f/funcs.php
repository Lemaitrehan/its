<?php
namespace  application\common\Component\Payment\Alipay\F2f;
use think\Url;
/**
 * 
 * @author jodendy
 */
class funcs{
    
    public static  function writeLog($text) {
        // $text=iconv("GBK", "UTF-8//IGNORE", $text);
        $text = funcs::characet ( $text );
        file_put_contents ( dirname ( __FILE__ ) . "/log/log.txt", date ( "Y-m-d H:i:s" ) . "  " . $text . "\r\n", FILE_APPEND );
    }
    
    //转换编码
    public static  function characet($data) {
        if (! empty ( $data )) {
            $fileType = mb_detect_encoding ( $data, array (
                'UTF-8',
                'GBK',
                'GB2312',
                'LATIN1',
                'BIG5'
            ) );
            if ($fileType != 'UTF-8') {
                $data = mb_convert_encoding ( $data, 'UTF-8', $fileType );
            }
        }
        return $data;
    }
    
    /**
     * 使用SDK执行接口请求
     * @param unknown $request
     * @param string $token
     * @return Ambigous <boolean, mixed>
     */
    public static function aopclient_request_execute($request,$out_trade_no='', $token = NULL) {
        $aop = new AopClient ();
        $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';//$config ['gatewayUrl'];
        $aop->appId = '2088711458142920';//$config ['app_id'];
        $aop->rsaPrivateKeyFilePath = __DIR__.'/key/rsa_private_key.pem';//$config ['merchant_private_key_file'];
        $aop->alipayPublicKey = __DIR__.'/key/rsa_alipay_public_key.pem';
        $aop->apiVersion = "1.0";
        $out_trade_no && $aop->notify_url = Url::build('/api/pay/callback/type/1',"id={$out_trade_no}",'html',true);
        $result = $aop->execute ( $request, $token );
        funcs::writeLog("response: ".var_export($result,true));
        return $result;
    }
}