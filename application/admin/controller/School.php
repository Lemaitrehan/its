<?php
namespace application\admin\controller;
use application\admin\model\School as M;
/**
 * 学校控制器
 */
class School extends Base{
	
    public function index(){
        $this->assign("type_id",Input("type_id/d",0));
    	return $this->fetch("list");
    }
    /**
     * 获取分页
     */
    public function pageQuery(){
        $m = new M();
        return $m->pageQuery();
    }
    /**
     * 跳去编辑页面
     */
    public function toEdit(){
        $m = new M();
        $this->assign("type_id",Input("type_id/d",0));
        $rs = $m->getById(Input("id/d",0));
        if(isset($rs['details']))
        {
            $rs['details'] = htmlspecialchars_decode($rs['details']);
        }
        $is_sell = !empty($rs['is_sell'])?$rs['is_sell']:'';
        $this->assign('is_sell',$is_sell);
        $this->assign("object",$rs);
        return $this->fetch("edit");
    }
    /*
    * 获取数据
    */
    public function get(){
        $m = new M();
        return $m->getById(Input("id/d",0));
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

    ###################################################################################################
    /*
    *   学历类学院管理重写
    */
    public function indexEdu(){
        $exam_type = session('examType');
        $type_id = 1;
        $this->assign("type_id",$type_id);
        return $this->fetch("listedu");
    }
    /**
     * 获取分页
     */
    public function pageQueryEdu(){
        $m = new M();
        return $m->pageQueryEdu();
    }
    /**
     * 跳去编辑页面
     */
    public function toEditEdu(){
        $m = new M();
        $this->assign("type_id",Input("type_id/d",0));
        $rs = $m->getSchoolOne(Input("id/d",0));
        $this->assign("object",$rs);
        $is_sell = !empty($rs['is_sell'])?$rs['is_sell']:'';
        $this->assign('is_sell',$is_sell);
        return $this->fetch("editedu");
    }
    /**
     * 新增
     */
    public function addEdu(){
        $m = new M();
        return $m->addEdu();
    }
    /**
    * 修改
    */
    public function editEdu(){
        $m = new M();
        return $m->editEdu();
    }
    /**
     * 删除
     */
    public function delEdu(){
        $m = new M();
        return $m->delEdu();
    }

    public function upSell(){
        $m = new M();
        return $m->upSell();
    }
}
