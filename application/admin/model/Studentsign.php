<?php
namespace application\admin\model;
use think\Db;
class Studentsign extends Base{

	public function getEduInfo(){ //学历报名信息
	    $where = '';
	    $search_word = input('search_word');
	    if($search_word){
	        $where = "u.trueName  like '%$search_word%' OR a.exam_no like '%$search_word%'";
	    }
	    $field   = 'a.*,FROM_UNIXTIME(a.entry_time) as entry_time,u.trueName'; 
	    $eduInfo = Db::name('studentEdu a')->join('users u','u.userId = a.userId','LEFT')
	                                       ->where($where)
	                                       ->field($field)
	                                       ->paginate(input('pagesize/d'));
	    return $eduInfo;
	}
	public function getSkillInfo(){ //技能报名信息
	    $where = '';
	    $search_word = input('search_word');
	    if($search_word){
	        $where = "u.trueName  like '%$search_word%' OR a.exam_no like '%$search_word%'";
	    }
	    $field   = 'a.*,FROM_UNIXTIME(a.entry_time) as entry_time,u.trueName';
	    $eduInfo = Db::name('studentSkill a')->join('users u','u.userId = a.userId','LEFT')
                                    	   	 ->where($where)
                                    	     ->field($field)
                                    	     ->paginate(input('pagesize/d'));
	    return $eduInfo;
	}
	
	
}
