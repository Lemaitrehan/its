<?php
namespace application\admin\controller;
use application\admin\model\Roles as M;
/**
 * 角色控制器
 */
class Roles extends Base{
	
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
     * 获取菜单
     */
    public function get(){
        
    	$m = new M();
    	return $m->get((int)Input("post.id"));
    }
    /**
     * 跳去编辑页面
     */
    public function toEdit(){
        if( request()->isAjax() ){
            $action = input('action');
            $school = new \application\admin\model\School;
            //学校
            $education_type = input('post.education_type');
            if($education_type){
                $getSchoolType = $school->getSchoolType($education_type);
                MBISApiReturn( MBISReturn('学校数据！！！',1,$getSchoolType ) );
            }
            //专业
            $school_id = input('post.school');
            if($school_id){    
                $getSchoolType = $school->getSchoolMajor($school_id);
                MBISApiReturn( MBISReturn('学校专业数据！！！',1,$getSchoolType ) );
            }
            //年级
            if( $action == 'grade_id' ){
	             $search_name = input('search_name');
	             $where['name'] =  ['like','%'.$search_name.'%'];
	            return  db('grade')->field('grade_id as id,name')->where($where)->LIMIT(5)->select();
	         }
        }
        
    	$m = new M();
    	$rs = $m->getById((int)Input("get.id"));
    	$this->assign("object",$rs);
    	$arrUserRange = null;
    	//查找 查看学历 权限 
    	if( $rs['is_teachers'] ){
    	    $userRange = $rs['userRange'];
    	    $arrUserRange = unserialize($userRange);
    	}
    	$this->assign('arrUserRange',$arrUserRange);
    	$arrSchool   = null;
    	$arrMajor    = null;
    	$arrNewGrade = null;//查找年纪
    	if($arrUserRange){
        	$arrGrade = array();
        	foreach ( $arrUserRange  as $key =>   $v ){
        	    $education_type = $v['education_type'];//学历类型
        	    //查找学校
        	    if($education_type){
        	       $where  = array();
        	       $where['exam_type'] = ['=',$education_type];
        	       $arrSchool[$key] = db('school')->field('school_id as id,name')->where($where)->select();
        	    }else{
        	        $arrSchool[$key] = null;
        	    }
        	    //查找专业
        	    if($v['major']){
        	       $where  = array();
        	       $major  = $v['major'];
        	       $where['major_id'] = ['=',$major];
        	       $arrMajor[$key] = db('major')->field('major_id as id,name')->where($where)->select();
        	    }else{
        	        $arrMajor[$key] = null;
        	    }
        	    //查找年纪
        	    if($v['grade']){
        	       $arrGrade[] = $v['grade'];
        	    }
        	}
        	//dd($arrUserRange);
        	//查找年纪
        	if($arrGrade){
        	    $grade_ids   = implode(',', $arrGrade);
        	    $where = array();
        	    $where['grade_id'] = ['in',$grade_ids];
	            $arrGetGrade = db('grade')->field('grade_id as id,name')->where($where)->select();
	            $arrNewGrade = array();
                foreach ($arrGetGrade as $k => $t){
                    $arrNewGrade[ $t['id'] ] = $t['name']; 
                }
        	}
    	}
    	//
    	$this->assign('arrUserRange',$arrUserRange);
    	$this->assign('arrSchool',$arrSchool);//学校
    	$this->assign('arrMajor',$arrMajor);//专业
    	$this->assign('arrNewGrade',$arrNewGrade);//年纪
    	if($rs['school_ids']){
    	    $arrS = explode(',', $rs['school_ids']);
    	}
    	$this->assign('arrS',isset($arrS)?$arrS:null);
    	
    	$this->assign('isEdit',Input("get.id")?1:0 );
    	//查找学校
    	$department_ids = '21,22,23,24';
    	$where = array();
    	$where['department_id'] = ['in',$department_ids];
    	$arrD = db('department')->where($where)->field('department_id,name')->select();
    	$this->assign('arrD',$arrD);
    	return $this->fetch("edit");
    }
    /**
     * 新增菜单
     */
    public function add(){
    	$m = new M();
    	return $m->add();
    }
    /**
     * 编辑菜单
     */
    public function edit(){
    	$m = new M();
    	return $m->edit();
    }
    /**
     * 删除菜单
     */
    public function del(){
    	$m = new M();
    	return $m->del();
    }
    //查找学校
    public function getSchoolType(){
        $school = new \application\admin\model\School;
        $education_type = input('post.education_type');
        $getSchoolType = $school->getSchoolType($education_type);
        MBISApiReturn( MBISReturn('学校数据！！！',1,$getSchoolType ) );
    }
    //查找专业
    public function getSchoolMajor(){
        $school   = new \application\admin\model\School;
        $school_id = input('post.education_type');
        $getSchoolType = $school->getSchoolMajor($school_id);
        MBISApiReturn( MBISReturn('学校专业数据！！！',1,$getSchoolType ) );
    }
  /*   //查找专业年纪
    public function getSchoolMajorGrade(){
        $school   = new \application\admin\model\School;
        $major_id = input('post.major_id');
        $getSchoolType = $school->getSchoolMajorGrade($major_id);
        MBISApiReturn( MBISReturn('学校专业年级数据！！！',1,$getSchoolType ) );
    } */
}
