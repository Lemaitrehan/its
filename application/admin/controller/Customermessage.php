<?php
namespace application\admin\controller;
use application\admin\model\CustomerMessage AS M ;

use think\Controller;
// +----------------------------------------------------------------------
// | 客服系统
// +----------------------------------------------------------------------
// | Author: lijianhua
// +----------------------------------------------------------------------
class Customermessage extends Base{
    
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
        $info = $file->validate(['size'=>256780,'ext'=>'xlsx,xls'])->move(ROOT_PATH . 'public' . DS . 'upload'.'\exams\baokao','',true);
        if($info){
            $file = ROOT_PATH . 'public' . DS . 'upload'.'/exams/baokao/'.$info->getSaveName();
            $m      = new M();
            $res =  $m->importUsers($file);
            if((int)$res){
                $this->success('导入数据成功！！！',url('Customermessage/index') );
            }else{
                $this->error($res,url('Customermessage/index') );
            }
        }else{
            // 上传失败获取错误信息
            $msg =  $file->getError();
            $this->error($msg,url('Customermessage/index') );
        }
    }
   
}
