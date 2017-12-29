<?php
// +----------------------------------------------------------------------
// | APP 学生处理类
// +----------------------------------------------------------------------
// | Author: lijianhua
// +----------------------------------------------------------------------
namespace application\common\model;
use think\Db;
use think\Validate;

class StudentMessage extends Base{
    protected $rule =   [
        'tittle'  => 'require|max:50',
        'memo'    => 'require|max:250',
    ];
    
    protected $message  =   [
        'tittle.max' => '标题必须在1-5个字符',
        'memo.max'   => '留言内容必须在20-250个字符',
    ];
    
    protected $scene = [
        'edit'  =>  ['tittle','memo'],
    ];
	 //生成学生留言
	 public function studentMessage(){
	     $data = [
	             'tittle' => input('post.tittle'),
	             'memo'   => input('post.memo'),
	             'userId' => input('post.userId'),
	         ];
	     $rule     = $this->rule;
	     $validate = new Validate($rule);
	     $validate->scene('edit', ['tittle', 'memo']);
	 
	     $result = $validate->scene('edit')->check($data);
	     if(!$result){
	         MBISApiReturn( MBISReturn( $validate->getError(),-1,array()) );
	     }
	     
	     $id       = Db::name('demand_message')->insertGetId($data);
	     if($id){
            $msg    = '留言提交成功！！！';
            $status = '1';
         }else{
            $msg    = '留言提交失败！！！';
            $status = '-1';
         }
         MBISApiReturn( MBISReturn($msg,$status,array()) );
	 }
    
}
