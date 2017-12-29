<?php
namespace application\admin\controller;
use application\admin\model\Generalizetotal AS M ;

use think\Controller;
// +----------------------------------------------------------------------
// | 客服系统
// +----------------------------------------------------------------------
// | Author: lijianhua
// +----------------------------------------------------------------------
class Generalizetotal extends Base{
    
    //列表页
    public function index(){
        if(request()->isAjax()){
            $m = new M();
            return $m->listPage();
        }
        return $this->fetch('list');
    }
    
    //添加导入数据
    public function addData(){
        $file = request()->file('exel');
        if($file == null ){
            $this->error('请选择上传文件');
        }
        // 移动到框架应用根目录/public/uploads/目录下
        $info = $file->validate(['size'=>256780,'ext'=>'xlsx,xls,csv'])->move(ROOT_PATH . 'public' . DS . 'upload'.'\Generalizetotal\exel','',true);
        if($info){
            $file = ROOT_PATH . 'public' . DS . 'upload'.'/Generalizetotal/exel/'.$info->getSaveName();
            $m      = new M();
            $res =  $m->importUsers($file);
            if((int)$res){
                $this->success('导入数据成功！！！',url('Generalizetotal/index') );
            }else{
                $this->error($res,url('Generalizetotal/index') );
            }
        }else{
            // 上传失败获取错误信息
            $msg =  $file->getError();
            $this->error($msg,url('Generalizetotal/index') );
        }
    }
   
}
