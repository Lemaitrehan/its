<?php
namespace application\admin\model;
use think\Db;
class Accreds extends Base{
	/**
	 * 分页
	 */
	public function pageQuery(){
		return $this->where('dataFlag',1)->field(true)->order('accredId desc')->paginate(input('pagesize/d'));
	}
	/**
	 * 列表
	 */
    public function listQuery(){
		return $this->where('dataFlag',1)->field(true)->select();
	}
	public function getById($id){
		return $this->get(['accredId'=>$id,'dataFlag'=>1]);
	}
	/**
	 * 新增
	 */
	public function add(){
		$data = input('post.');
		$data['createTime'] = date('Y-m-d H:i:s');
		MBISUnset($data,'accredId');
		Db::startTrans();
		try{
			$result = $this->validate('Accreds.add')->allowField(true)->save($data);
			if(false !==$result){
				$id = $this->accredId;
				//启用上传图片
				MBISUseImages(1, $id, $data['accredImg']);
		        if(false !== $result){
		        	Db::commit();
		        	return MBISReturn("新增成功", 1);
		        }
			}
		}catch (\Exception $e) {
            Db::rollback();
        }
        return MBISReturn('新增失败',-1);	
	}
    /**
	 * 编辑
	 */
	public function edit(){
		$data = input('post.');
		MBISUnset($data,'createTime');
		Db::startTrans();
		try{
			MBISUseImages(1, (int)$data['accredId'], $data['accredImg'], 'accreds', 'accredImg');
		    $result = $this->validate('Accreds.edit')->allowField(true)->save($data,['accredId'=>(int)$data['accredId']]);
	        if(false !== $result){
	        	Db::commit();
	        	return MBISReturn("编辑成功", 1);
	        }
	    }catch (\Exception $e) {
            Db::rollback();
        }
        return MBISReturn('编辑失败',-1);  
	}
	/**
	 * 删除
	 */
    public function del(){
	    $id = (int)input('post.id/d');
	    Db::startTrans();
		try{
		    $result = $this->setField(['dataFlag'=>-1,'accredId'=>$id]);
		    MBISUnuseImage('accreds','accredImg',$id);	
	        if(false !== $result){
	        	Db::commit();
	        	return MBISReturn("删除成功", 1);
	        }
		}catch (\Exception $e) {
            Db::rollback();
        }
        return MBISReturn('删除失败',-1); 
	}
	
}
