<?php
namespace application\common\exception;
/**
 */
use think\exception\Handle;

class WstHttpException extends Handle
{

    public function render(\Exception $e)
    {
    	if(config('app_debug')){
    		return parent::render($e);
    	}else{
    	    header("Location:".url('home/error/index'));
    	}
    }

}