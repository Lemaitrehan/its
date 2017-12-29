<?php

namespace application\admin\controller;
use application\admin\model\Contract as M;

class Contract extends Base {
    public function index(){
        return $this->fetch('list');
    }
    public function pageQuery(){
        $m = new M();
        return $m->pageQuery();
    }
    public function edit(){
        $m = new M();
        $data = $m->editInfo();
        $this->assign('data',$data);
        return $this->fetch('edit');
    }
    /*添加工公章*/
//    public function add(){
//        $m = new M();
//        $data = $m->addInfo();
//        return $this->fetch('add');
//    }
    public function addContract(){
        $m = new M();
        return $m->addContractInfo();
    }
    public function save(){
        $m = new M();
         return $m->saveInfo();
    }
}
