<?php
namespace application\admin\model;
/**
 * 导航管理业务处理
 */
class Navs extends Base{
	/**
	 * 分页
	 */
	public function pageQuery(){
		return $this->field(true)->order('id desc')->paginate(input('pagesize/d'));
	}
	public function getById($id){
		return $this->get($id);
	}
	/**
	 * 新增
	 */
	public function add(){
		$data = input('post.');
		$data['createTime'] = date('Y-m-d H:i:s');
		MBISUnset($data,'id');
		$result = $this->validate('navs.add')->allowField(true)->save($data);
        if(false !== $result){
        	return MBISReturn("新增成功", 1);
        }else{
        	return MBISReturn($this->getError(),-1);
        }
	}
    /**
	 * 编辑
	 */
	public function edit(){
		$Id = input('post.id/d',0);
		//获取数据
		$data = input('post.');
		MBISUnset($data,'createTime');
	    $result = $this->validate('navs.edit')->allowField(true)->save($data,['id'=>$Id]);
        if(false !== $result){
        	return MBISReturn("编辑成功", 1);
        }else{
        	return MBISReturn($this->getError(),-1);
        }
	}
	/**
	 * 删除
	 */
    public function del(){
	    $id = input('post.id/d');
	    $result = $this->destroy($id);
        if(false !== $result){
        	return MBISReturn("删除成功", 1);
        }else{
        	return MBISReturn($this->getError(),-1);
        }
	}
	/**
	 * 设置显示隐藏 
	 */
    public function editiIsShow(){
        $id = input('post.id/d',0);
        $field = input('post.field');
        $val = input('post.val/d',0);
        if(!in_array($field,['isShow','isOpen']))return MBISReturn("非法的操作内容",-1);
        $result = Db::name('navs')->where(['id'=>['eq',$id]])->setField($field, $val);
        if(false !== $result){
            return MBISReturn("设置成功", 1);
        }else{
            return MBISReturn($this->getError(),-1);
        }
    }
	
}
