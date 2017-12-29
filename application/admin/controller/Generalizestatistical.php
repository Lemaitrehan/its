<?php
namespace application\admin\controller;
use application\admin\model\GeneralizePage AS M ;

use think\Controller;
// +----------------------------------------------------------------------
// | 客服系统
// +----------------------------------------------------------------------
// | Author: lijianhua
// +----------------------------------------------------------------------
class Generalizestatistical extends Base{
    
    //列表页
    public function index(){
        $m = new M();
        if(request()->isAjax()){
            return $res = $m->listPage();
        }
        $arrTitle = $m->arrTitle;
        $this->assign('arrTitle',$arrTitle);
        return $this->fetch('list');
    }
    
    public function InfoDownload(){  //数据导出Excel下载文件的函数
        header("Content-type:text/html;charset=utf-8");
        $path = input('get.path');  //存储路径
        $file=input('get.file');  //文件名称
        $file_path = $path.$file;
        if(!file_exists($file_path))
        {
            echo "文件不存在或已丢失";
            return ;
        }
        $fp=fopen($file_path,"r");
        $file_size=filesize($file_path);
        //下载文件需要用到的头
        Header("Content-type: application/octet-stream");
        Header("Accept-Ranges: bytes");
        Header("Accept-Length:".$file_size);
        Header("Content-Disposition: attachment; filename=".$file);
        $buffer=1024;
        $file_count=0;
        while(!feof($fp) && $file_count<$file_size)
        {
            $file_con=fread($fp,$buffer);
            $file_count+=$buffer;
            echo $file_con;
        }
        fclose($fp);
    }
    
    //添加导入数据(趋势分析)
    public function addData0(){
        $m      = new \application\admin\model\Generalize;
        $res    = $m->importUsers();
        if((int)$res){
            $this->success('导入数据成功！！！',url('Generalizestatistical/index') );
        }else{
            $this->error($res,url('Generalizestatistical/index') );
        }
    
    }
    
    //百度账户模板(趋势分析)
    public function addData1(){
        $m      = new \application\admin\model\Generalizetotal;
        $res    = $m->importUsers();
        if((int)$res){
            $this->success('导入数据成功！！！',url('Generalizestatistical/index') );
        }else{
            $this->error($res,url('Generalizestatistical/index') );
        }
    
    }
    
    //添加导入数据(趋势分析)
    public function addData3(){
        $m      = new \application\admin\model\GeneralizePage;
        $res    = $m->import(3);
        if((int)$res){
            $this->success('导入数据成功！！！',url('Generalizestatistical/index') );
        }else{
            $this->error($res,url('Generalizestatistical/index') );
        }
        
    }
    //添加导入数据(全部来源)
    public function addData4(){
        $m      = new \application\admin\model\GeneralizePage;
        $res    = $m->import(4);
        if((int)$res){
            $this->success('导入数据成功！！！',url('Generalizestatistical/index') );
        }else{
            $this->error($res,url('Generalizestatistical/index') );
        }
    }
    
    //添加导入数据(受访域名)
    public function addData5(){
        $m      = new \application\admin\model\GeneralizePage;
        $res    = $m->import(5);
        if((int)$res){
            $this->success('导入数据成功！！！',url('Generalizestatistical/index') );
        }else{
            $this->error($res,url('Generalizestatistical/index') );
        }
    }
    
    //添加导入数据(受访页面模板1)
    public function addData6(){
        $m      = new \application\admin\model\GeneralizePage;
        $res    = $m->import(6);
        if((int)$res){
            $this->success('导入数据成功！！！',url('Generalizestatistical/index') );
        }else{
            $this->error($res,url('Generalizestatistical/index') );
        }
    }
    
    //添加导入数据(受访页面模板2)
    public function addData7(){
        $m      = new \application\admin\model\GeneralizePage;
        $res    = $m->import(7);
        if((int)$res){
            $this->success('导入数据成功！！！',url('Generalizestatistical/index') );
        }else{
            $this->error($res,url('Generalizestatistical/index') );
        }
    }
    
    //添加导入数据(客服对话记录)
    public function addData8(){
        $m      = new \application\admin\model\Customer;
        $res    = $m->importUsers(8);
        if((int)$res){
            $this->success('导入数据成功！！！',url('Generalizestatistical/index') );
        }else{
            $this->error($res,url('Generalizestatistical/index') );
        }
    }
    
    //添加导入数据(客服对话记录)
    public function addData9(){
        $m      = new \application\admin\model\CustomerMessage;
        $res    = $m->importUsers(9);
        if((int)$res){
            $this->success('导入数据成功！！！',url('Generalizestatistical/index') );
        }else{
            $this->error($res,url('Generalizestatistical/index') );
        }
    }
    
    //流量概况
    public function addData10(){
        $m      = new \application\admin\model\GeneralizePage();
        $res    = $m->import(10);
        if((int)$res){
            $this->success('导入数据成功！！！',url('Generalizestatistical/index') );
        }else{
            $this->error($res,url('Generalizestatistical/index') );
        }
    }

}
