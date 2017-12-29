<?php
namespace application\admin\controller;
use application\admin\model\Messages as M;
use application\admin\model\ServicesJson as Services_JSON;
/**
 * 商城消息控制器
 */
class Messages extends Base{
	
    public function index(){
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
    * 查找用户
    */
    public function userQuery(){
        $m = model('users');
        return $m->getByName(input('post.loginName'));
    }
    /**
    * 发送消息
    */
    public function add(){
        $m = new M();
        return $m->add();
    }
    /**
     * 删除
     */
    public function del(){
        $m = new M();
        return $m->del();
    }
    /**
    * 查看完整消息
    */
    public function showFullMsg(){
        $m = new M();
        $rs = $m->getById(Input("id/d",0));
        return $this->fetch('msg', ['data'=>$rs]);

    }




























}
