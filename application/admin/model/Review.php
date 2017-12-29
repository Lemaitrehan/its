<?php
namespace application\admin\model;
use think\Db;
class Review extends Base{
    
    //获取
    function getMenu(){
            $join = array(
                array('__MENUS__ m2','m2.parentId = m1.menuId','left'),
                array('__MENUS__ m3','m3.parentId = m2.menuId','left'),
            );
            $where = "m1.parentId = 0 AND m3.menuId >0 
                AND m1.dataFlag = 1 and m2.dataFlag = 1 and m3.dataFlag = 1";
            $field = 'm3.menuName,m3.menuId';
            
            $res   =  db::name('menus')->alias('m1')
                                 ->join($join)
                                 ->field($field)
                                 ->where($where)
                                 ->order('m1.menuSort')
                                 ->select();
            return $res;
    }
    
    //查找所有的工作人民
    function getWorkingPerson(){
        
       return  db::name('employee')->field('staff_id,name')->where('staff_id>0')->select();
    }
    //新增
    function addData(){
        //查找是否存在
        $menus_id = $this->where('menus_id ='.input('list_id') )->value('menus_id');
        if($menus_id){
            return '该菜单已有审核设置';
        }
        $user_id = session('MBIS_STAFF')->staffId;
        $time    = time();
        $data = array(
            'menus_id'          => input('list_id'),
            'review_person_id'  => input('review_name_id'),
            'create_person_id'  => $user_id,
            'create_person_time'=> $time,
            'update_person_id'  => $user_id,
            'update_time'       => $time
        );
        
        $id = $this->save($data);
        return $id;
    }
    
    //编辑
    function editData(){
        //查找是否存在
        $menus_id = $this->where('menus_id ='.input('list_id').' AND id != '.input('id') )->value('menus_id');
        if($menus_id){
            return '该菜单已有审核设置';
        }
        $user_id = session('MBIS_STAFF')->staffId;
        $time    = time();
        $data = array(
            'menus_id'          => input('list_id'),
            'review_person_id'  => input('review_name_id'),
            'create_person_time'=> $time,
            'update_time'       => $time
        );
        $id = $this->where('id', input('id') )->update($data);
        return $id;
    }
    
    //编辑
    function delData(){
    /*     $user_id = session('MBIS_STAFF')->staffId;
        $time    = time();
        $data = array(
            'update_person_id'  => $user_id,
            'update_time'       => $time,
            'status'            => -1
        ); */
        $id = $this->where('id', input('id') )->delete();
        return $id;
    }
    
	/**
	 * 分页
	 */
	public function pageQuery(){
	     $join  = array(
	               array('employee u','u.staff_id = a.review_person_id','left'),
	               array('department b','b.department_id = u.department_id','left'),
	               array('menus m','m.menuId = a.menus_id','left'),
	     );
	     $field = 'a.id,u.name as trueName,u.mobile,b.name as bm_name,m.menuName,
	               FROM_UNIXTIME(create_person_time) as create_person_time,
	               FROM_UNIXTIME(update_time) as update_time
	             ';
	     $where  = array();
	     if(input('search_word')){
	         $search_word = input('search_word');
	         $where['u.name|m.menuName'] = ['like','%'.$search_word.'%']; 
	     }
		 $res = $this->alias('a')
		             ->join($join)
		             ->where($where)
		             ->field($field)
		             ->order('a.update_time desc')
		             ->paginate(input('pagesize/d'));
		 return $res;
	}
	
	/**
	 * 查找审核按钮权限
	 * @param unknown $privilegeCode //权限代码
	 * @param unknown $usesId //用户id
	 */
	function reviewShow($privilegeCode,$usesId){
	    $join = array( array('privileges p','p.menuId = a.menus_id','left'), );
	    $where['a.review_person_id'] = $usesId;
	    $where['privilegeCode'] = $privilegeCode;
	    $res  = $this->alias('a')
	                 ->field('a.*')
	                 ->join($join)->where($where)->find();
	    if($res){
	        return true;
	    }else{
	        return false;
	    }
	}
	
	
}
