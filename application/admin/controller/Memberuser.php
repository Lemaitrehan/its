<?php
namespace application\admin\controller;
use application\admin\model\Memberuser as M;
use application\admin\model\Major as Major;
use application\admin\model\Grade as Grade;
use application\admin\model\School as School;
/**
 * 会员管理控制器
 */
class Memberuser extends Base{

	public function indexMember(){
        $m = new M();
        return $this->fetch("list");
    }
    public function pageQueryM(){
        $m = new M();
        return $m->pageQueryM();
    }
    public function MemberInfo(){   //跳去查看页面
        $m = new M();
        $id = input('id');
        $key = 'look';  //查看信息
        $info = $m->getMemberInfo($id,$key);
        $this->assign('data',$info);
        return $this->fetch("info");
    }
    public function toEditMember(){ //跳去编辑页面
        $m = new M();
        $id = input('id');
        $info = $m->getMemberInfo($id);
        $this->assign('data',$info);
        return $this->fetch("edit");
    }
    public function editMember(){  //修改
        $m = new M();
        return $m->editMember();
    }
    public function delMember(){  //删除
        $m = new M();
        return $m->delMember();
    }
}
