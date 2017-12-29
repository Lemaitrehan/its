<?php
namespace application\admin\model;
/**
 * 学校业务处理
 */
use think\Db;
class School extends Base{
    
    public $arr_exam_type = array(
        0=>'未知',
        1=>'自考',
        2=>'成考',
        3=>'网教',
    );
    
	/**
	 * 分页
	 */
	public function pageQuery(){
        $type_id = Input("type_id/d",0);
        $key = input('get.key');
        $where = ['closed'=>['<>','1']];
        if($type_id!='')$where['jump_type'] = $type_id;
		if($key!='')$where['name'] = ['like','%'.$key.'%'];
        $field = "school_id,school_no,name,jump_type,is_nav,cover_img,costst,principal_name,principal_mobile,addr,info,details,createtime,lastmodify,data_type,batch_num,if(is_sell=1,'上架','下架') as is_sell";
        $page = $this->where($where)->field($field)->order('lastmodify desc')
		->paginate(input('post.pagesize/d'))->toArray();
        foreach($page['Rows'] as $k => $v){
            if($v['is_sell'] == '上架'){
                $is_sell = 2;
            }else{
                $is_sell = 1;
            }
            $page['Rows'][$k]['is_sell'] = "<a href='#' onclick='upSell(".$v['school_id'].",$is_sell)'>".$v['is_sell']."</a>";
        }
        return $page;
	}
	public function getById($id){
		return $this->get(['school_id'=>$id]);
	}
	/**
	 * 新增
	 */
	public function add(){
		$data = input('post.');
        $data['costst'] = (float)$data['costst'];
		$data['createtime'] = time();
        $data['lastmodify'] = time();
		MBISUnset($data,'school_id');
        MBISUnset($data,'id');
		Db::startTrans();
		try{
			$result = $this->validate('school.add')->allowField(true)->save($data);
			//$id = $this->school_id;
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
		$id = (int)input('post.school_id');
		$data = input('post.');
        $data['lastmodify'] = time();
		MBISUnset($data,'createtime');
		Db::startTrans();
		try{
		    $result = $this->validate('school.edit')->allowField(true)->save($data,['school_id'=>$id]);
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
		    $result = $this->where(['school_id'=>$id])->update(['closed'=>1,'lastmodify'=>time()]);
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
	 * 学校列表
	 */
    public function get_lists($where=[]){
        $field = "school_id,school_no,name,jump_type,is_nav,cover_img,costst,principal_name,principal_mobile,addr,info,details,createtime,lastmodify,data_type,batch_num";
        return $this->where($where)->field($field)->select();
	}
	public function get_lists_edu(){
		return $this->where('jump_type',1)->field('school_id,name')->select();
	}
	public function get_lists_skill(){
		return $this->where('jump_type',2)->field('school_id,name')->select();
	}
    public function get_name($id=0){
        return $this->where('school_id',$id)->value('name');
	}
	
	
	/**
	 * 查找学校类型(按学历和技能分)
	 * @param unknown $exam_type 1=》学历 2=》技能
	 */
	public function  getSchoolClass($exam_type){
	    return   db::name('school s')->field('s.school_id,s.name')
                            	     ->where('s.jump_type='.$exam_type)
                            	     ->group('s.school_id')
                            	     ->select();
	}
	
	//查找学校类型 (考试类分)
	public function  getSchoolType($exam_type){
	   return   db::name('school s')
	                         //->join('major m','m.school_id = s.school_id','LEFT')
	                         //->field('s.school_id,s.name')
	                         ->where('s.exam_type='.$exam_type)
	                         //->group('s.school_id')
	                         ->select();
	}
	
	//查找学校专业
	public function  getSchoolMajor($school_id){  
	    /*   $res = db::name('school s')->join('major m','m.school_id = s.school_id','LEFT')
                            	     ->field('m.major_id,m.name')
                            	     ->where('s.school_id='.$school_id)
                            	     #->group('s.school_id')
                            	     ->select(); */
	      $where = "FIND_IN_SET($school_id,school_ids)";
	      $res   = db::name('major_edu m')->field('m.major_id,m.name')
                                	      ->where($where)
                                	      ->select(); 
	      return $res;
	}
	//查找学校专业年纪
	public function  getSchoolMajorGrade($major_id){
	       $major = db::name('major m')->join('grade g','g.major_id = m.major_id','LEFT')
                        	           ->field('g.grade_id,g.name')
                        	           ->where('m.major_id='.$major_id)
                        	           ->select();
	       return $major;
	}
	
	//查找学校专业科目
	public function  getSchoolMajorSubject($major_id){
	    $subject = db::name('major m')->join('subject s','s.major_id = m.major_id','LEFT')
	    ->field('s.subject_id,s.name')
	    ->where('m.major_id='.$major_id.' and s.subject_id >0')
	    ->select();
	    return $subject;
	}

	/**
	 * 查找 各种 分类下 的学生
	 * @param string $field
	 * @param string $where
	 */
	public function getSearchUser($field="",$where=""){
	    $join = array(
	            array('school s','s.school_id=sk.school_id','left'),
        	    array('major m','m.major_id=sk.major_id','left'),
        	    array('grade g','g.grade_id=sk.grade_id','left'),
        	    array('users u','u.userId=sk.userId','left')
	    );
	    $res = db::name('student_edu')->alias('sk')
                    	         ->field($field)
                    	         ->join($join)
                    	         ->where($where)
                    	         ->paginate(input('post.pagesize/d'))
	                             ->toArray();
	    return $res;
	}
	
	/**
	 * 查找 各种 分类下 的学生
	 * @param string $field
	 * @param string $where
	 */
	public function getSearchUserSkill($field="",$where=""){
	    $join = array(
	        array('school s','s.school_id=sk.school_id','left'),
	        array('major m', 'm.major_id =sk.major_id','left'),
	        array('subject subject','subject.subject_id=sk.subject_id','left'),
	        array('users u','u.userId=sk.userId','left')
	    );
	    $res = db::name('student_skill')->alias('sk')
	    ->field($field)
	    ->join($join)
	    ->where($where)
	    ->paginate(input('post.pagesize/d'))
	    ->toArray();
	    return $res;
	}

	##################################################################################################
	##################################################################################################
	/*
	*学历类院校管理重写
	*/
	public function pageQueryEdu(){
		$exam_type = session('examType');
        $type_id = Input("type_id/d",0);
        $key = input('get.key');
        $where = ['closed'=>['<>',1]];
        if($type_id!='')$where['jump_type'] = ['=',"$type_id"];
		if($key!='')$where['name'] = ['like','%'.$key.'%'];
		//if($exam_type!='')$where['m.exam_type'] = ['=',"$exam_type"];
		$arr_exam_type  =  $this->arr_exam_type;
        $field = "s.school_id,s.school_no,s.name,s.exam_type,if(s.is_sell=1,'上架','下架') as is_sell";
        $where['s.exam_type'] = ['=',session('examType')];
        $page = $this
        	->alias('s')
        	//->join('major m','s.school_id=m.school_id')
        	->where($where)
        	->field($field)
        	//->order('s.lastmodify desc')
			->paginate(input('post.pagesize/d'))
			->toArray();
        foreach($page['Rows'] as $key => $v ){
           $page['Rows'][ $key ]['exam_type'] =  $arr_exam_type[ $v['exam_type'] ];
            if($v['is_sell'] == '上架'){
                $is_sell = 2;
            }else{
                $is_sell = 1;
            }
            $page['Rows'][$key]['is_sell'] = "<a href='#' onclick='upSell(".$v['school_id'].",$is_sell)'>".$v['is_sell']."</a>";
        }
        
        return $page;
	}
	public function getSchoolOne($id){
		return $this->get(['school_id'=>$id]);
	}
	/**
	 * 新增
	 */
	public function addEdu(){
	    $time = time();
		$data = input('post.');
        $data['costst'] = (float)$data['costst'];
		$data['createtime'] = $time;
        $data['lastmodify'] = $time;
        $data['exam_type']  = session('examType');//学历考试类型
		MBISUnset($data,'school_id');
        MBISUnset($data,'id');
		Db::startTrans();
		try{
			$result = $this->validate('school.add')->allowField(true)->save($data);
			//$id = $this->school_id;
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
	public function editEdu(){
		$id = (int)input('post.school_id');
		$data = input('post.');
        $data['lastmodify'] = time();
		MBISUnset($data,'createtime');
		Db::startTrans();
		try{
		    $result = $this->validate('school.edit')->allowField(true)->save($data,['school_id'=>$id]);
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
    public function delEdu(){
	    $id = input('post.id/d');
	    Db::startTrans();
		try{
		    $result = $this->where(['school_id'=>$id])->update(['closed'=>1,'lastmodify'=>time()]);
	        if(false !== $result){
	        	Db::commit();
	        	return MBISReturn("删除成功", 1);
	        }
		}catch (\Exception $e) {
            Db::rollback();
            return MBISReturn('删除失败',-1);
        }
	}
	public function second_array_unique_bykey($arr,$key){  //二维数组处理
        $tmp_arr = [];  
        foreach($arr as $k => $v){ 
            if(in_array($v[$key], $tmp_arr)){   //搜索$v[$key]是否在$tmp_arr数组中存在，若存在返回true   
                unset($arr[$k]); //销毁一个变量  如果$tmp_arr中已存在相同的值就删除该值  
            }else{  
                $tmp_arr[$k] = $v[$key];  //将不同的值放在该数组中保存  
            }  
        }  
        //ksort($arr); //ksort函数对数组进行排序(保留原键值key)  sort为不保留key值  
        return $arr;  
    }

    public function upSell()
    {
        $id = input('post.id/d');
        $type_id = input("post.type_id/d");
        Db::startTrans();
        try{
            $result = $this->where(['school_id'=>$id])->update(['is_sell'=>$type_id,'lastmodify'=>time()]);
            if(false !== $result){
                Db::commit();
                return MBISReturn("变更成功", 1);
            }
        }catch (\Exception $e) {
            Db::rollback();
            return MBISReturn('变更失败',-1);
        }
    }
}
