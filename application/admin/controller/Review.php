<?php
namespace application\admin\controller;
use application\admin\model\Review as MY;
use think\Controller;
// +----------------------------------------------------------------------
// | 审核模块
// +----------------------------------------------------------------------
// | Author: lijianhua
// +----------------------------------------------------------------------
class Review extends Base{
    
    //审核模块列表
    public function index(){
        if( request()->isAjax() ){
            $reviewObj = new MY();
            return $reviewObj->pageQuery();
        }
        return $this->fetch('list');
    }
    //新增审核页面
    public function addReview(){
       return $this->publucData();
    }
    //编辑
    public function editReview(){
       return $this->publucData();
    }
    
    public function delReview(){
        $reviewObj = new MY();
        $res = $reviewObj->delData();
        $status = $res?1:-1;
        return ['status'=>$status]; 
    }
    
    public function publucData(){
        $reviewObj = new MY();
        if( request()->isAjax() ){
            $action = input('act');
            //提交数据
            if($action == 'addData'){
                $id = $reviewObj->addData();
                if((int)$id){
                    return ['status'=>1];
                }else{
                    return ['status'=>0,'msg'=>$id];
                }
            }elseif($action == 'editData'){
                $id = $reviewObj->editData();
                if((int)$id){
                    return ['status'=>1];
                }else{
                    return ['status'=>0,'msg'=>$id];
                }
            //编辑数据    
            }elseif(input('post.review_id')){
                $id  = input('post.review_id');
                $arr = db('review')->where('id='.$id)->field('*')->find();
            }
            //查找所有的页面
            $res   = $reviewObj->getMenu();
            //查找所有的老师
            $arrPerson = $reviewObj->getWorkingPerson();
            return  array('page'=>$res,'person'=>$arrPerson,
                'menus_id'=>isset($arr['menus_id'])?$arr['menus_id']:'',
                'review_person_id'=>isset($arr['review_person_id'])?$arr['review_person_id']:'');
        }
    }
    
   
}
