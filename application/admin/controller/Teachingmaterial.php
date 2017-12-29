<?php
namespace application\admin\controller;
use application\admin\model\TeachingMaterial as M;
/**
 * 教材管理控制器
 */
class Teachingmaterial extends Base{
	
    public function indexEdu(){
        $m = new M();
        $type = 1;
        $this->assign('type',$type);
        $list = $m->get_info_list();
        //dump($list);die;
        return $this->fetch("list");
    }

    public function indexSkill(){
        $m = new M();
        $type = 2;
        $this->assign('type',$type);
        $list = $m->get_info_list();
        //dump($list);die;
        return $this->fetch("list");
    }

    /**
     * 获取分页
     */
    public function pageQuery(){
        $m = new M();
        return $m->pageQuery();
    }
    /*
    * 获取数据
    */
    public function getlists(){
        $m = new M();
        return $m->getById(Input("id/d",0));
    }
    /**
     * 跳去编辑页面
     */
    public function toEdit(){
        $m = new M();
        $type = input('get.type');
        $this->assign('type',$type);
        $rs = $m->getById(Input("id/d",0));
        if(isset($rs['details']))
        {
            $rs['details'] = htmlspecialchars_decode($rs['details']);
        }
        $this->assign("object",$rs);
        return $this->fetch("edit");
    }
    /**
     * 新增
     */
    public function add(){
        $m = new M();
        return $m->add();
    }
    /**
    * 修改
    */
    public function edit(){
        $m = new M();
        return $m->edit();
    }
    /**
     * 删除
     */
    public function del(){
        $m = new M();
        return $m->del();
    }

    
}
