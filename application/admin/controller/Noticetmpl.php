<?php
namespace application\admin\controller;
use application\admin\model\NoticeTmpl as M;
/**
 * 通知提醒模板管理控制器
 */
class Noticetmpl extends Base{
	
    //主页
    public function index(){
        $m = new M();
        //分页
        if( request()->isAjax() ){
            return $m->pageQuery();
        }
        return $this->fetch("list");
    }
    
    //新增模板
    public function addNoticeTmpl(){
        if( input('post.send_type') ){
            $m = new M();
            return $m->add();
        }
        return  $this->publicAddEdit();
    }
    
    //编辑模板
    public function editNoticeTmpl(){
        if( input('post.id') ){
            $m = new M();
            return $m->edit();
        }
        return  $this->publicAddEdit();
    }
    
    //删除模板
    public function del(){
        $m = new M();
        return $m->del();
    }
    
    /**
     * 跳去编辑页面
     */
    public function publicAddEdit(){
        //信息通知父类
        $SmsAll    = new \application\common\model\SmsAll();
        $send_type = input('send_type');
        if( isset( $send_type ) ){
            $send_type =  input('send_type');
        }
        
        if(Input("id/d",0) ){
           $m  = new M();
           $rs = $m->getById(Input("id/d",0));
           $send_type = $rs['send_type'];
        }
        //查找短信发送方式
        $arrSendType    = $SmsAll->sendType;
        $this->assign("arrSendType",$arrSendType);
        //查找模板类型
        $arrTempletType = $SmsAll->templetType;
        $this->assign("arrTempletType",$arrTempletType);
        $this->assign("object",!empty($rs)?$rs:null);
        
        if( !empty( $rs['content']  ) ){
            $content = array(
                'content'=>html_entity_decode( $rs['content'] )
            );
        }else{
            $content = array(
                'content'=>''
            );
        }
        $this->assign("content",json_encode($content));
        $this->assign("send_type",!empty($send_type)?$send_type:1);
        return $this->fetch("edit");
    }
    

    
}
