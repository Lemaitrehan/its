<?php
namespace application\common\interfaces;

interface IOrderPay{
   
   /**
     * 统一支付异步消息处理响应系统更新支付状态。 
     * @param array $callback_data 异步响应数据，数组
     * @param array $type 响应类型及数据别名。[key=alipay,alias=f2f];
     * @param string $msg 响应更新系统数据出现错误的提示内容提示。
     */
   function updateOrderByPay($callback_data,$type,&$msg);
}