<?php
namespace application\admin\model;
use think\Db;
/**
 * 菜单业务处理
 */
class Menus extends Base{
	protected $insert = ['dataFlag'=>1]; 
    public $adminRoleId = 1;
	/**
	 * 获取菜单列表
	 */
	public function listQuery($parentId = -1){
		if($parentId==-1)return ['id'=>0,'name'=>MBISConf('CONF.mallName'),'isParent'=>true,'open'=>true];
		$rs = $this->where(['parentId'=>$parentId,'dataFlag'=>1])->field('menuId id,menuName name')->order('menuSort', 'asc')->select();
		if(count($rs)>0){
			foreach ($rs as $key =>$v){
				$rs[$key]['isParent'] = true;
			}
		};
		return $rs;
	}
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
		$result = $this->validate('Menus.add')->save(input('post.'));
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
		$menuId = input('post.menuId/d');
	    $result = $this->validate('Menus.edit')->allowField(['menuName','menuSort'])->save(input('post.'),['menuId'=>$menuId]);
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
	    $menuId = input('post.id/d');
		$data = [];
		$data['dataFlag'] = -1;
	    $result = $this->update($data,['menuId'=>$menuId]);
        if(false !== $result){
        	return MBISReturn("删除成功", 1);
        }else{
        	return MBISReturn($this->getError(),-1);
        }
	}
	
	/**
	 * 获取用户菜单
	 */
	public function getMenus(){
		$STAFF = session('MBIS_STAFF');
        $filter_menu['parentId'] = 0;
        $filter_menu['dataFlag'] = 1;
        if($STAFF['staffRoleId'] != 1)
        {
           $filter_menu['menuId'] = ['in',$STAFF['menuIds']];    
        }
		return $this->where($filter_menu)->field('menuId,menuName')->order('menuSort', 'asc')->select();
	}
	
	/**
	 * 获取子菜单
	 */
	public function getSubMenus($parentId){
		//用户权限判断
	    $STAFF    = session('MBIS_STAFF');
	    $examType = session('examType');
        $filter_menu['parentId'] = $parentId;
        $filter_menu['dataFlag'] = 1;
        if($STAFF['staffRoleId'] != 1)
        {
           $filter_menu['menuId'] = ['in',$STAFF['menuIds']];    
        }
        //学历 考试类型
		$allowMenus = [];
		$rs2 = $this->where($filter_menu)->field('menuId,menuName')->order('menuSort', 'asc')->select();
		if( $examType != 1 ){
    		foreach ($rs2 as $key =>  $v ){
    		    if(  $v['menuId']  == 119 ){
    		        unset($rs2[$key]);
    		    }
    		}
		}
		//查找学历下面的权限
		$where = array();
		//CKBM_001 报名
		//CKBK_001 报考
		//CKCJ_00   成绩
		//XLKMGL_001 科目管理

		if( $examType == 1){
		    $where['privilegeCode'] = ['in',('LQGL_00')];
		    $res = db::name('privileges')->field('menuId')->where($where)->select();
		}else{
		    $where['privilegeCode'] = ['in',('CKBK_001,CKCJ_00')];
		    $res = db::name('privileges')->field('menuId')->where($where)->select();
		}
		
		if(!empty($res)){
		    $gl_id = array();//菜单权限分类
		    foreach ($res as $key => $v ){
		        $gl_id[] = $v['menuId'];
		    }
		    $gl_ids = implode(',', $gl_id);
		}
		foreach ($rs2 as $key2 =>$v2){
			if($STAFF['staffRoleId']!=1 && !in_array($v2['menuId'],$STAFF['menuIds']))continue;
            $filter_menu2['parentId'] = $v2['menuId'];
            $filter_menu2['m.dataFlag'] = 1;
            if($STAFF['staffRoleId'] != 1)
            {
               $filter_menu2['m.menuId'] = ['in',$STAFF['menuIds']];    
            }
            if( isset($gl_ids) ){
               $filter_menu2['m.menuId'] = ['not in',$gl_ids];
            }
			$rs3 = Db::name('menus')->alias('m')
			->join('__PRIVILEGES__ p','m.menuId= p.menuId and isMenuPrivilege=1 and p.dataFlag=1','inner')
			->where($filter_menu2)
			->field('m.menuId,m.menuName,privilegeUrl')
			->order('menuSort', 'asc')
			->select();
			if(!empty($rs3))$rs2[$key2]['list'] = $rs3;
		}
		return $rs2;
	}
}
