<?php
namespace application\admin\controller;
use application\admin\model\Faq as M;
/**
 * 帮助文档控制器
 */
class Faq extends Base{
	
    public function index(){
        if(isset($_GET['edu_up']))
        {
            $db = \think\Db::name('student_edu');
            $lists = $db->select();
            $up_data = [];
            foreach($lists as &$v):
                $v = array_merge($v,unserialize($v['extend_data']));
                $v['level_id'] = ITSSelItemId('major','level_type',@$v['level_type']);
                $v['exam_type'] = ITSSelItemId('major','exam_type',@$v['exam_type']);
                $up_data[] = [
                    'edu_id' => $v['edu_id'],
                    'level_id' => $v['level_id'],
                    'exam_type' => $v['exam_type'],
                ];
            endforeach;
            $result = model('admin/studentEdu')->saveAll($up_data);
            dump($result);exit;
        }
    	return $this->fetch("list");
    }
    
    /**
     * 获取分页
     */
    public function pageQuery(){
    	$m = new M();
    	$rs = $m->pageQuery();
    	return $rs;
    }
    
    /**
     * 获取文章
     */
    public function get(){
    	$m = new M();
    	$rs = $m->get(Input("post.id/d",0));
    	return $rs;
    }
    
    /**
     * 详情页面
     */
    public function toView(){
    	$id = Input("get.id/d",0);
    	$m = new M();
    	if($id>0){
    		$object = $m->getById($id);
            $object['articleContent'] = htmlspecialchars_decode($object['articleContent']);
    	}
    	$this->assign('object',$object);
    	$this->assign('articlecatList',model('Article_Cats')->listQuery(0));
    	return $this->fetch("view");
    }
    
    /**
     * 设置是否显示/隐藏
     */
    public function editiIsShow(){
    	$m = new M();
    	$rs = $m->editiIsShow();
    	return $rs;
    }
    
    /**
     * 跳去新增/编辑页面
     */
    public function toEdit(){
    	$id = Input("get.id/d",0);
    	$m = new M();
    	if($id>0){
    		$object = $m->getById($id);
            $object['articleContent'] = htmlspecialchars_decode($object['articleContent']);
    	}else{
    		$object = $m->getEModel('articles');
    		$object['catName'] = '';
    	}
    	$this->assign('object',$object);
    	$this->assign('articlecatList',model('Article_Cats')->listQuery(0));
    	return $this->fetch("edit");
    }
    
    /**
     * 新增
     */
    public function add(){
    	$m = new M();
    	$rs = $m->add();
    	return $rs;
    }
    
    
    /**
     * 编辑
     */
    public function edit(){
    	$m = new M();
    	$rs = $m->edit();
    	return $rs;
    }
    
    /**
     * 删除
     */
    public function del(){
    	$m = new M();
    	$rs = $m->del();
    	return $rs;
    }
}
