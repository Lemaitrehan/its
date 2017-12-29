<?php
namespace application\admin\model;
use think\Db;
/**
 * 科目类型属性模型
 */
class SubjectTypeProp extends Base{
    public $sel_data = array(
        //属性类型(0为唯一属性，1为下单选属性，2为复选属性)
        array(
            'key' => 'prop_type',
            'name' => '属性类型',
            'style' => 'display:none',
            'lists' => array(
                array('id'=>0,'name'=>'唯一属性'),
            )
        ),
        //录入方式( 0为手工录入、1为从下面的列表中选择、2为多行文本框)
        array(
            'key' => 'prop_input_type',
            'name' => '录入方式',
            'style' => '',
            'lists' => array(
                array('id'=>0,'name'=>'手工录入'),
                array('id'=>1,'name'=>'下拉列表选择'),
                array('id'=>2,'name'=>'多行文本框'),
            )
        ),
    );
    /**
	 * 分页
	 */
	public function pageQuery(){
        $type_id = input('get.type_id');
        $key = input('get.key');
        $where = [];
		if($type_id!='')$where['type_id'] = $type_id;
        if($key!='')$where['name'] = ['like','%'.$key.'%'];
        $page = $this->where($where)->field('*')->order('lastmodify desc')
		->paginate(input('post.pagesize/d'))->toArray();
        if(count($page['Rows'])>0){
			foreach ($page['Rows'] as $key => $v){
                $page['Rows'][$key]['prop_input_type'] = $this->get_sel_item_name('prop_input_type',$v['prop_input_type']);
			}
		}
        return $page;
	}
	public function getById($id){
		return $this->get(['prop_id'=>$id]);
	}
	/**
	 * 新增
	 */
	public function add(){
		$data = input('post.');
		$data['createtime'] = time();
        $data['lastmodify'] = time();
		MBISUnset($data,'id');
        Db::startTrans();
		try{
			$result = $this->allowField(true)->save($data);
			//$id = $this->prop_id;
	        if(false !== $result){
			    Db::commit();
	        	return MBISReturn("新增成功", 1);
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
		$id = (int)input('post.id');
		$data = input('post.');
        $data['lastmodify'] = time();
		MBISUnset($data,'createtime,id');
		Db::startTrans();
		try{
		    $result = $this->allowField(true)->save($data,['prop_id'=>$id]);
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
	    $id = input('post.id/d');
	    Db::startTrans();
		try{
		    $result = $this->where(['prop_id'=>$id])->delete();
	        if(false !== $result){
	        	Db::commit();
	        	return MBISReturn("删除成功", 1);
	        }
		}catch (\Exception $e) {
            Db::rollback();
            return MBISReturn('删除失败',-1);
        }
	}
    /**
	 * 获取科目类型名称
	 */
    public function get_name($id=0){
        return $this->where('type_id',$id)->column('name');
	}
    /**
	 * 下拉数据
	 */
    public function get_sel_data($type='',$cur_id=0)
    {
        return $this->sel_data;   
    }
    public function get_sel_item_name($type='',$id=0)
    {
        $item_name = '';
        $sel_data = $this->get_sel_data();
        foreach($sel_data as $k=>$v)
        {
            if($v['key'] == $type)
            {
               foreach($v['lists'] as $vv)
               {
                   if($vv['id'] == $id)
                   {
                       $item_name = $vv['name'];    
                   }
               }
            }
        }
        return $item_name;
    }
}
