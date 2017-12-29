<?php
namespace application\admin\model;
use think\Db;
/**
 * 前台菜单业务处理
 */
class HomeMenus extends Base{	
	protected $insert = ['dataFlag'=>1]; 
	
	/**
	 * 获取菜单
	 */
	public function getById($id){
		return $this->get(['dataFlag'=>1,'menuId'=>$id]);
	}
	
	/**
	 * 新增菜单
	 */
	public function add(){
		$data = input('post.');
		$data['createTime'] = date('Y-m-d H:i:s');
		$data["dataFlag"] = 1;
		$result = $this->validate('HomeMenus.add')->allowField(true)->save($data);
        if(false !== $result){
        	return MBISReturn("新增成功", 1);
        }else{
        	return MBISReturn($this->getError(),-1);
        }
	}
    /**
	 * 编辑菜单
	 */
	public function edit(){
		$menuId = input('post.menuId/d',0);
	    $result = $this->validate('HomeMenus.edit')->allowField(['menuName','menuSort','menuType','isShow','menuUrl','menuOtherUrl'])->save(input('post.'),['menuId'=>$menuId]);
        if(false !== $result){
        	return MBISReturn("编辑成功", 1);
        }else{
        	return MBISReturn($this->getError(),-1);
        }
	}
	/**
	 * 删除菜单
	 */
	public function del(){
	    $menuId = input('post.menuId/d',0);
		$data = [];
		$data['dataFlag'] = -1;
	    $result = $this->update($data,['menuId'=>$menuId]);
	    $this->update($data,['parentId'=>$menuId]);
        if(false !== $result){
        	return MBISReturn("删除成功", 1);
        }else{
        	return MBISReturn($this->getError(),-1);
        }
	}
	
	/**
	 * 分页
	 */
	public function pageQuery(){
		$menuType = (int)input('menuType',-1);
		$where = [];
		$where['a.dataFlag'] = 1;
		if($menuType!=-1)$where['a.menuType'] = $menuType;
		$rs = $this->alias('a')->join('__HOME_MENUS__ b','a.parentId = b.menuId','left')
			->field("a.menuId, a.parentId, a.menuName, a.menuUrl, a.menuOtherUrl, a.menuType, a.isShow, a.menuSort, b.menuName parentName")
			->where($where)
			->order('a.menuType asc,a.menuSort asc')
			->paginate(input('pagesize/d',1));
		return $rs;
	}
	
	/**
	 * 显示隐藏
	 */
	public function setToggle(){
		$menuId = input('post.menuId',0);
		$isShow = input('post.isShow/d');
		$result = $this->where(['menuId'=>$menuId,"dataFlag"=>1])->setField("isShow", $isShow);
		if(false !== $result){
			return MBISReturn("设置成功", 1);
		}else{
			return MBISReturn($this->getError(),-1);
		}
	}
	
	/**
	 * 获取菜单列表
	 */
	public function getMenus($parentId = -1){
		$rs = $this->where(['parentId'=>$parentId,'dataFlag'=>1])->field('menuId, parentId, menuName, menuUrl,menuOtherUrl')->order('menuSort', 'asc')->select();
		if(count($rs)>0){
			foreach ($rs as $key =>$v){
				$children = self::getMenus($rs[$key]['menuId']);
				if(!empty($children)){
					$rs[$key]["children"] = $children;
				}
			}
		};
		return $rs;
	}
	
	/**
    * 修改排序
    */ 
    public function changeSort(){
    	$id = (int)input('id');
    	$menuSort = (int)input('menuSort');
        $rs = $this->where('menuId',$id)->setField('menuSort',$menuSort);
        if($rs!==false){
        	return MBISReturn('修改成功',1);
        }
        return MBISReturn('修改失败',-1);
    }
}
