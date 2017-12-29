<?php
namespace application\admin\controller;
use application\admin\model\Customuser as M;
use application\admin\model\Major as Major;
use application\admin\model\Grade as Grade;
use application\admin\model\School as School;
/**
 * 客户管理控制器
 */
class Customuser extends Base{

	public function indexCustom(){
        $m = new M();
        return $this->fetch("list");
    }
    public function pageQueryC(){
        $m = new M();
        return $m->pageQueryC();
    }
    public function CustomInfo(){    //跳去查看页
        $m = new M();
        $id = input('id');
        $key = 'look';  //查看数据
        $info = $m->getCustomInfo($id,$key);
        $this->assign('data',$info);
        return $this->fetch('info');
    }
    public function toEditCustom(){ //跳去编辑页
        $m = new M();
        $id = input('id');
        $info = $m->getCustomInfo($id);
        $this->assign('data',$info);
        return $this->fetch('edit');
    }

    public function editCustom(){   //编辑操作
        $m = new M();
        return $m->editCustom();
    }

    public function delCustom(){    //删除操作
        $m = new M();
        return $m->delCustom();
    } 
}
