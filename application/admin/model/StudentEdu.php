<?php
namespace application\admin\model;
use think\Db;
/**
 * 报名管理
 */
class StudentEdu extends Base{

	public function getLevel($type){
		switch($type){
			case 1:return '高升专';
			case 2:return '专升本';
		}
	}

	public function getEntryStatus($status){
		switch($status){
			case 1:return '未报考';
			case 2:return '未录取';
			case 3:return '已录取';
		}
	}

	public function getDisposeStatus($status){
		switch($status){
			case 1:return '未处理';
			case 2:return '已处理';
			default :return '';
		}
	}

	public function getDisposeResult($status){
		switch($status){
			case 1:return '转其他课程';
			case 2:return '转下次';
			case 3:return '退费';
			case 4:return '其他';
			default :return '';
		}
	}

	public function pageQuery(){
		$exam_type = session('examType');
    	$where = [];
    	$school_id = input('get.school_id');
    	$major_id = input('get.major_id');
    	$level_id = input('get.level_id');
    	$grade_id = input('get.grade_id');
    	if($school_id !=''){
    		$where['e.school_id'] = ['=',"$school_id"];
    	}
    	if($major_id !=''){
    		$where['e.major_id'] = ['=',"$major_id"];
    	}
    	if($level_id !=''){
    		$where['e.level_id'] = ['=',"$level_id"];
    	}
    	if($grade_id !=''){
    		$where['e.grade_id'] = ['=',"$grade_id"];
    	}
    	$key = input('get.key');
		$key_value = trim(input('get.key_value'));
		if(($key !== '') && ($key_value !== '')){
			if($key =='trueName'){
				$where['u.trueName'] = ['like',"%$key_value%"];
			}elseif($key == 'student_no'){
				$where['u.student_no'] = ['like',"%$key_value%"];
			}elseif($key == 'userPhone'){
				$where['u.userPhone'] = ['like',"%$key_value%"];
			}elseif($key == 'idcard'){
				$where['u.idcard'] = ['like',"%$key_value%"];
			}
		}

    	$where['m.exam_type'] = ['=',"$exam_type"];

    	$join = [];
    	$join = [
    		['users u','e.userId=u.userId','left'],
  			['major_edu m','e.major_id=m.major_id','left']
    	];
    	$field = 'e.edu_id,e.school_id,e.school_name,e.major_id,e.major_name,e.level_id,e.grade_id,e.grade_name,e.entry_status,e.dispose_status,e.dispose_result,u.trueName,u.idcard,u.student_no,u.userPhone';

    	$page = $this
    			->alias('e')
    			->join($join)
    			->where($where)
    			->field($field)
    			//->order('e.lastmodify desc')
				->paginate(input('pagesize/d'))
				->toArray();
		//getLastSql();
		
		if(count($page['Rows'])>0){
			foreach ($page['Rows'] as $key => $v){
                $page['Rows'][$key]['level_id'] = $this->getLevel($v['level_id']);
                $page['Rows'][$key]['entry_status'] = $this->getEntryStatus($v['entry_status']);
                $page['Rows'][$key]['dispose_status'] = $this->getDisposeStatus($v['dispose_status']);
                $page['Rows'][$key]['dispose_result'] = $this->getDisposeResult($v['dispose_result']);
			}
		}
		return $page;
	}

	public function getGrade($type){
    	$grade = Db::name('grade')->where('exam_type',$type)->field('grade_id,name')->select();
    	return $grade;
    }

    public function getSchool($type){
    	$where = [];
    	$where['m.exam_type'] = ['=',"$type"];

    	$field = 's.school_id,s.name';

    	$join = [];
    	$join = [
    		['major_edu m','FIND_IN_SET(s.school_id,m.school_ids)','left']
    	];
    	$school = Db::name('school')
    					->alias('s')
    					->join($join)
    					->where($where)
    					->field($field)
    					->group('s.school_id')
    					->select();
    	//dd($school);
    	return $school;
    }

    public function getMajor($type){
    	$major = Db::name('major_edu')->where('exam_type',$type)->field('major_id,name')->select();
    	return $major;
    }

	public function getInfoOne($id){
		$where = [];
		$where['edu_id'] = ['=',"$id"];

		$join = [];
		$join = [
			['users u','e.userId=u.userId','left']
		];

		$field = 'u.trueName,u.idcard,u.student_no,e.edu_id,e.school_name,e.major_name,e.grade_name,e.level_id,e.entry_status,e.dispose_status,e.dispose_result';
		$res = $this
				->alias('e')
				->join($join)
				->where($where)
				->field($field)
				->find();
		$res['level_id'] = $this->getLevel($res['level_id']);
		return $res;
	}

	public function edit(){
		$data = input('post.');
		$edu_id = input('post.edu_id');
		MBISUnset($data,'edu_id');
		Db::startTrans();
		try{
		    $result = $this->allowField(true)->save($data,['edu_id'=>$edu_id]);
	        if(false !== $result){
	        	Db::commit();
	        	return MBISReturn("编辑成功", 1);
	        }
	    }catch (\Exception $e) {
            Db::rollback();
        }
        return MBISReturn('编辑失败',-1);
	}

	public function majorGet(){
    	$school_id = input('post.school_id');
    	$where = 'FIND_IN_SET('."$school_id".',school_ids)';
    	$field = 'major_id,name';
    	$majors = Db::name('major_edu')
    					->where($where)
    					->field($field)
    					->select();
    	if(!empty($majors)){
    		return ['data'=>$majors,'status'=>1];
    	}else{
    		return ['msg'=>'抱歉,出错了','status'=>-1];
    	}
    }

    public function levelGet(){
    	$major_id = input('post.major_id');
    	$where = [];
    	$where['major_id'] = ['=',"$major_id"];
    	$levels = Db::name('major_edu_extend')
    					->where($where)
    					->field('level_id')
    					->select();
    	if(!empty($levels)){
    		foreach($levels as &$v){
    			$v['level_name'] = $this->getLevel($v['level_id']);
    		}
    		return ['data'=>$levels,'status'=>1];
    	}else{
    		return ['msg'=>'抱歉,出错了','status'=>-1];
    	}
    }
    
}
