<?php
namespace application\admin\model;
/**
 * 专业业务处理
 */
use think\Db;
use think\exception\ThrowableError;
class Major extends Base{
    
    //专业
    public $arrMajorType = array(
             1=>'自考', 
             2=>'成考', 
             3=>'网教'
    );
    
    //层次
    public $arrMajorLevel = array(
        2=>'高升专',
        3=>'专升本',
    );
    
    //是否展示app
    public $arrShow = array(
        0=>'否',
        1=>'是'        
    );
    
    //是否出售
    public $arrSell = array(
        2=>'否',
        1=>'是'
    );
    
    //是否HOT
    public $arrHot = array(
        0=>'否',
        1=>'是'
    );
    
    public $arrSkillMajorType = array(
        2=>'技能',
        3=>'管理'
    );
    
	/**
	 * 分页
	 */
	public function pageQuery(){
	    $arrShow = $this->arrShow;
	    $arrSell = $this->arrSell;
	    $arrHot  = $this->arrHot;
	    //$arrSkillMajorType = $this->arrSkillMajorType;
        //动态获取课程类型
        $arrSkillMajorType = type_get_data();
	    
		$where = ['closed'=>['<>','1']];
		//$where['type_id'] = ['in','2,3'];
        $where['type_id'] = ['<>','1'];
		if(input('name')){
		    $name = input('name');
		    $where['name'] = ['like',$name];
		}
        $page = $this->where($where)
                     ->field('major_id,major_number,name,is_show,is_sell,is_hot,type_id as type_name')
                     ->order('lastmodify desc')
		             ->paginate(input('post.pagesize/d'))
                     ->toArray();
        foreach($page['Rows'] as $key => $v){
            $page['Rows'][$key]['is_show']    = $arrShow[$v['is_show']];
            $page['Rows'][$key]['is_sell']    = $v['is_sell']==1?"<a href='#' onclick='upSell(".$v['major_id'].",2)'>上架</a>":"<a href='#' onclick='upSell(".$v['major_id'].",1)'>下架</a>";
            $page['Rows'][$key]['is_hot']     = $arrHot[$v['is_hot']];
            $page['Rows'][$key]['type_name']  = $arrSkillMajorType[$v['type_name']];
        }
        return $page;
	}
        public function pageQueryNew(){
            $where = array();
            #$exam_type = session('examType');
            #$where['exam_type'] = $exam_type;
            if(input('marjor_id')){
                $marjor_id = input('marjor_id');
                $where['a.major_id'] = $marjor_id;
            }
            $where['a.closed'] = ['<>','1'];
            $where['b.closed'] = ['<>','1'];
            $page      = Db::name('major_edu')->alias('a')
                                              ->field('a.major_number,a.name,a.is_show,a.major_id,a.is_sell,GROUP_CONCAT( DISTINCT  b.level_id,b.graduate_time ) as level_id,GROUP_CONCAT( DISTINCT b.level_id,\'-\', b.graduate_time) AS level_graduatr,GROUP_CONCAT( DISTINCT c.name) as school_name ')
                                              ->join(
                                                  array(
                                                     array('major_edu_extend b','b.major_id =a.major_id','left'), 
                                                     array('school c','FIND_IN_SET(c.school_id , a.school_ids)','left'),
                                                  )   
                                               )
                                              ->where($where)
                                              ->group('a.major_id')
                                              ->paginate(1000)//input('post.pagesize/d')
                                              ->toArray(); 
            $data = [];
            $arrMajorLevel = $this->arrMajorLevel;
            foreach ($page['Rows'] as $k=>$v){
                if($v['is_show']==1){
                    $data[$k]['is_show'] = '是';
                }else{
                    $data[$k]['is_show'] = '否';
                }
                if($v['is_sell']==1){
                    $data[$k]['is_sell'] = "<a href='#' onclick='upSellEdu(".$v['major_id'].",2)'>上架</a>";
                }else{
                    $data[$k]['is_sell'] = "<a href='#' onclick='upSellEdu(".$v['major_id'].",1)'>下架</a>";
                }
                $data[$k]['name'] = $v['name'];
                $data[$k]['major_number'] = $v['major_number'];
                $data[$k]['major_id'] = $v['major_id'];
                
                $arr_level_graduatr = explode(',',$v['level_graduatr']);
                $level_graduatr = '';
                foreach ($arr_level_graduatr as $kk => $vv){
                    $arr_l = explode('-',$vv);
                    $level_graduatr .= '层次：'.$arrMajorLevel[ $arr_l[0] ];
                    $level_graduatr .= ' ';
                    $level_graduatr .= '学年：'.$arr_l[1];
                    $level_graduatr .= '  <br>';
    
                }
                $data[$k]['school_name']   = $v['school_name'];
                $data[$k]['graduate_time'] = $level_graduatr;
                
            }
            $page['Rows'] = $data;
            return $page;
	}
	public function getById($id){
        $rs = $this->get(['major_id'=>$id]);
        if(isset($rs['details']))
        {
            $rs['details'] = htmlspecialchars_decode($rs['details']);
        }
		return $rs;
	}
	/**
	 * 新增
	 */
	public function add(){
		$data = input('post.');
		$data['createtime'] = time();
        $data['lastmodify'] = time();
		MBISUnset($data,'major_id,id');
		Db::startTrans();
		try{
			$result = $this->allowField(true)->save($data);
	        if(false !== $result){
			    Db::commit();
	        	return MBISReturn("新增成功", 1);
	        }
		}catch (\Exception $e) {
            Db::rollback();
            return MBISReturn('新增失败',-1);
        }
        
      
	}
    /**
	 * 编辑
	 */
	public function edit(){
		$id = (int)input('post.major_id');
		$data = input('post.');
        $data['lastmodify'] = time();
		MBISUnset($data,'createtime');
		Db::startTrans();
		try{
		    $result = $this->allowField(true)->save($data,['major_id'=>$id]);
		    //查找下面的课程
		    $arr_major_ids = db::name('course')->where('major_id='.$id)->column('course_id');
		    if($arr_major_ids){
		        $major_ids = implode(',', $arr_major_ids);
		        $affow_id  = db::name('course')->where(" course_id in ( $major_ids )")->update( array('type_id'=>$data['type_id']) );
		    }
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
		    $result = $this->where(['major_id'=>$id])->update(['closed'=>1,'lastmodify'=>time()]);
	        if(false !== $result){
	        	Db::commit();
	        	return MBISReturn("删除成功", 1);
	        }
		}catch (\Exception $e) {
            Db::rollback();
            return MBISReturn('删除失败',-1);
        }
	}
        
        
        /*
         * 删除major_edu数据
         */
        public function todel(){
	    $id = input('post.id');
	    Db::startTrans();
		try{
		    $result = Db::name('major_edu')->where(['major_id'=>$id])->update(['close'=>1]);
                    $result = Db::name('major_edu_extend')->where(['major_id'=>$id])->update(['close'=>1]);
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
	 * 下拉数据
	 */
    public function get_sel_data($type='',$cur_id=0)
    {
        return ITSGetSelData('major');   
    }
    /**
	 * 专业列表
	 */
    public function get_lists($where=[])
    {
        $rs = $this->where($where)->select();
        foreach($rs as $k=>$v)
        {
            if(isset($v['exam_type']))
            {
                $rs[$k]['exam_type'] = ITSSelItemName('major','exam_type',$v['exam_type']);
            }
            if(isset($v['level_type']))
            {
                $rs[$k]['level_type'] = ITSSelItemName('major','level_type',$v['level_type']);
            }
        }
        return $rs;
    }
    public function get_name($id=0){
        return $this->where('major_id',$id)->value('name');
	}
    public function get_level_type($id=0){
        return $this->where('major_id',$id)->value('level_type');
	}
    public function get_list($where=[]){
        $field = "major_id,type_id,school_id,name,cover_img,des,details,level_type,graduate_type,exam_type,edu_type,is_show,createtime,lastmodify,data_type,batch_num,is_hot";
	return $this->where($where)->field($field)->select();
	}
    public function get_major_list(){
        $exam_type = session('examType');
        return Db::name('major_edu')->where('exam_type = '.$exam_type)->field('name,major_id')->select();
    }
    public function toSave(){
        $exam_type = session('examType');
        $data = input('post.');
        $info['name'] = $data['name'];
        $info['major_number'] = $data['major_number'];
        $info['type_id']=1;
        $schools = input();
        Db::startTrans();
		try{
		    $result = $this->save($info);
	        if(false !== $result){
	        	Db::commit();
	        	return MBISReturn("添加成功", 1);
	        }
		}catch (\Exception $e) {
            Db::rollback();
            return MBISReturn('添加失败',-1);
        }
        
    }
    /*
     * 获取所有学校列表（自考，成考，网教）
     */
    public function getShoolList(){
        $where['exam_type'] = ['=',session('examType') ];
        $rs = Db::name('school')
                ->where($where)
                ->field('school_no,name as school_name,school_id')
                ->paginate(input('post.pagesize/d'))
                ->toArray();
        $school_ids = input('school_ids');
        if($school_ids){
           $arr =  explode(',', $school_ids);
        }
        foreach ($rs['Rows'] as $k=>$v){
            if( isset($arr) && in_array( $v['school_id'], $arr) ){
                $is_checked = ' checked=checked ';
            }else{
                $is_checked = ' ';
            }
            $rs['Rows'][$k] = array( 'school_name' => $v['school_name'].'('.$v['school_no'].')', 
                                  'checkbox'    =>'<input id="ck_'.$v['school_id'].'" type="checkbox"  '.$is_checked.' name="chk" value="'.$v['school_id'].'">'
                
            );
        }
        return  $rs;
    }
    
    
    
    
    public function selectSchool(){
        $school_id = input('post.ids');
        $school_name = Db::name('school')->field('name')->where('school_id',$school_id)->select();
        $totle['name'] = $school_name[0]['name'];
        $totle['school_id'] = $school_id;
        return ['data'=>$totle,'status'=>1];
    }
    
    //(学历)添加数据
    public function saveInfo(){
        //学校
        $school_ids = input('school_id1');
        $school_arr = explode(',', $school_ids);
        if(!$school_ids){
            return ['msg'=>'没有学校信息','status'=>0];
        }
        
        $person_id    = session('MBIS_STAFF')->staffId;
        $time         = time();
        
        $is_show      = input('is_show');
        $name         = input('name');
        $major_number = input('major_number');
        $cover_img    = input('cover_img');
        $des          = input('des');
        $details      = input('detail');
        $exam_type    = session('examType');
        $createTime   = $time;
        if(!$name){
            exception('专业名称必填！！！');
        }
        if(!$major_number){
            exception('专业编号必填！！！');
        }
        $data['is_show']       = $is_show;
        $data['name']          = $name;
        $data['major_number']  = $major_number;
        $data['cover_img']     = $cover_img;
        $data['des']           = $des;
        $data['detail']        = $details;
        $data['exam_type']     = $exam_type;
        $data['school_ids']    = $school_ids;
        $data['createTime']    = $time;
        
        Db::startTrans();
		try{
		    $major_edu = Db::name('major_edu');
		    
		    $major_number = $major_edu->where('major_number','=',$major_number)->value('major_number');
		    
		    if($major_number){
		        exception('专业编号不能重复');
		    }
		    
            $rs = $major_edu->insert($data);
            $majorId = $major_edu->getLastInsID();
            if(!$majorId){
                exception('生成专业失败'); 
            }
		    //层级     
            $arr_level_id      = $_POST['level_id'];
            $arr_graduate_time = $_POST['graduate_time'];
            $arr_subject_ids   = $_POST['subject_ids'];
            
            foreach ($arr_level_id as $key => $v ){
                $dataSubject[] = array(
                    'major_id'     => $majorId,
                    'level_id'     => $v,
                    'subject_ids'  => $arr_subject_ids[$key],
                    'graduate_time'=> $arr_graduate_time[$key],
                );
                if(empty($v)){
                    exception('没有专业层级数据');
                }
                if(!$arr_subject_ids[$key] && session('examType') == 1 ){
                    exception('专业层级科目不能为空',-1);
                }
                
                if(!$arr_graduate_time[$key]){
                    exception('专业层级毕业时间不能为空',-1);
                }
            }
            $result = Db::name('major_edu_extend')->insertAll($dataSubject);
            
            if(!$result){
                exception('专业层级数据生成失败',-1);
            }
            //学校
            foreach ($school_arr as $key1 => $v1){
                //层级
                foreach ($arr_level_id as $key2 => $v2 ){
                    //兼容历史记录
                    $dataHistory[] = array(
                        'req_id'     => $majorId,//专业公共表id
                        'type_id'    => '1',//类型ID 1=> 学历 2=>技能
                        'school_id'  => $v1,
                        'name'          => $name,
                        'major_number'  => $major_number,
                        'cover_img'     => $cover_img,
                        'des'           => $des,//专业简介
                        'details'       => $details,//专业详情
                        'level_type'    => $v2,//层次：0
                        'exam_type'     => $exam_type,//考试类型：
                        'graduate_type' => $arr_graduate_time[$key2],//毕业时间
                        'edu_type'      => $v2,//学历类型：1=专科、2=本科
                        'is_show'       => $is_show,//是否展示
                        'createtime'    => $time,
                        'lastmodify'    => $person_id,
                    );
                }
            }
            $result1 = Db::name('major')->insertAll($dataHistory);
            if(!$result1){
                exception('专业兼容老版数据生成失败',-1);
            }
            if($result1){
                Db::commit();  
                return MBISReturn('生成专业数据成功',1);
            }else{
                exception('专业层级数据生成失败',-1);
            }
                
		}catch (\Exception $e) {
            Db::rollback();
            return MBISReturn($e->getMessage(),-1);
        }
    }
    
    //（学历）修改专业数据(NEW 学历)
    public function editData(){
        //专业id
        $major_id = input('major_id');
        if(!$major_id){
            return ['status'=>0,'msg'=>'参数错误'];
        }
        //学校
        $school_ids = input('school_id1');
        $school_arr = explode(',', $school_ids);
        //如果没有学校信息 删除 所有的数据
        if(!$school_ids){
            return ['status'=>0,'msg'=>'没有选择学校'];
        }
        
        $person_id    = session('MBIS_STAFF')->staffId;
        $time         = time();
        $is_hot       = input('is_hot');
        $type_id      = input('type_id');
        $is_sell      = input('is_sell');
        $is_show      = input('is_show');
        $name         = input('name');
        $major_number = input('major_number');
        $cover_img    = input('cover_img');
        $des          = input('des');
        $details      = input('detail');
        $exam_type    = session('examType');
        $createTime   = $time;
        
        
        $data['is_show']       = $is_show;
        $data['name']          = $name;
        $data['major_number']  = $major_number;
        $data['cover_img']     = $cover_img;
        $data['des']           = $des;
        $data['detail']        = $details;
        $data['exam_type']     = $exam_type;
        $data['school_ids']    = $school_ids;
        $data['createTime']    = $time;
        $data['is_sell']       = $is_sell;
        $type_id!=1 && $data['type_id']       = $type_id;
        $data['is_hot']        = $is_hot;
        if(!$name){
            return ['status'=>0,'msg'=>'专业名称必填'];
        }
        if(!$major_number){
            return ['status'=>0,'msg'=>'专业编号必填'];
        }
        Db::startTrans();
        try{
            
            $major_edu = Db::name('major_edu');
            $where['major_number'] = $major_number;
            $where['major_id']     = ['NEQ',$major_id];
            $major_number1 = $major_edu->where($where)->value('major_number');
            if($major_number1){
                exception('专业编号不能重复');
            }
          
            $rs = $major_edu->where('major_id','=',$major_id)->update($data);
            if(!$rs){
                exception('修改专业失败');
            }
            //层级
            $arr_level_id      = $_POST['level_id'];
            $arr_graduate_time = $_POST['graduate_time'];
            $arr_subject_ids   = $_POST['subject_ids'];
            //清除专业 拓展表 
            $res = Db::name('major_edu_extend')->where('major_id','=',$major_id)->delete();
            if(!$res){
                exception('专业数据修改失败1',-1);
            }
            foreach ($arr_level_id as $key => $v ){
                $dataSubject[] = array(
                    'major_id'     => $major_id,
                    'level_id'     => $v,
                    'subject_ids'  => $arr_subject_ids[$key],
                    'graduate_time'=> $arr_graduate_time[$key],
                );
                if(empty($v)){
                    exception('没有专业层级数据');
                }
                if(!$arr_subject_ids[$key]){
                   // exception('专业层级科目不能为空',-1);
                }
                if(!$arr_graduate_time[$key]){
                    exception('专业层级毕业时间不能为空',-1);
                }
            }
            $result = Db::name('major_edu_extend')->insertAll($dataSubject);
        
            if(!$result){
                exception('专业层级数据生成失败',-1);
            }
            //学校
            foreach ($school_arr as $key1 => $v1){
                //层级
                foreach ($arr_level_id as $key2 => $v2 ){
                    //兼容历史记录
                    $dataHistory[] = array(
                        'req_id'        => $major_id,//专业公共表id
                        //'type_id'       => '1',//类型ID 1=> 学历 2=>技能
                        'school_id'     => $v1,
                        'name'          => $name,
                        'major_number'  => $major_number,
                        'cover_img'     => $cover_img,
                        'des'           => $des,//专业简介
                        'details'       => $details,//专业详情
                        'level_type'    => $v2,//层次：0
                        'exam_type'     => $exam_type,//考试类型：
                        'graduate_type' => $arr_graduate_time[$key2],//毕业时间
                        'edu_type'      => $v2,//学历类型：1=专科、2=本科
                        'is_show'       => $is_show,//是否展示
                        'createtime'    => $time,
                        'lastmodify'    => $time,
                        'data_type'     => '',
                        'batch_num'     => '',
                        'is_hot'        => '',
                    );
                }
            }
            //
            $is_true = true;
            foreach ($dataHistory as $key => $v ){
                $req_id     = $v['req_id'];
                $school_id  = $v['school_id'];
                $level_type = $v['level_type'];
                //查找是否已存在
                $where  = array();
                $where['req_id']     = $req_id;
                $where['school_id']  = $school_id;
                $where['level_type'] = $level_type;
                $arr = Db::name('major')->field('major_id')->where($where)->find();
                if($arr['major_id']){
                    $result1 = Db::name('major')->where('major_id ='.$arr['major_id'])->update($v);
                }else{
                    $result1 = Db::name('major')->insert($v);
                }
                if(!$result1){
                    $is_true = false;
                }
            }
        
            if($is_true){
                Db::commit();
                return MBISReturn('编辑专业数据成功',1);
            }else{
                exception('编辑层级数据生成失败',-1);
            }
        
        }catch (\Exception $e) {
            Db::rollback();
            return MBISReturn($e->getMessage(),-1);
        }
        
    }
    
    //查找公共科目 带分页(学历)
    public function selectSubjectList(){
        $rs = Db::name('subject_edu')
                ->alias('se')
                ->field('subject_id,subject_no,name')
                ->paginate(input('post.pagesize/d'))->toArray();
        $subjectIds = input('subjectIds');
        if($subjectIds){
            $arr = explode(',', $subjectIds);
        }
        $data = [];
        foreach($rs['Rows'] as $k=>$v){
            if( isset($arr) && in_array($v['subject_id'], $arr)){
                $is_checked = ' checked=checked ';
            }else{
                $is_checked = ' ';
            }
            $data[$k]['name'] = $v['name'].'('.$v['subject_no'].')';
            $data[$k]['checkbox'] = '<input id="ck_'.$k.'" type="checkbox" name="chk" '.$is_checked.' value="'.$v['subject_id'].'">';
        }
        $rs['Rows'] = $data;
        return $rs;
        
    }
    //删除公共科目（学历）
    public function delEducation($major_id){
        Db::startTrans();
        try{
           $res1    = $this->where(['req_id'=>$major_id])->delete();
           $res2    = Db::name('major_edu')->where('major_id','=',$major_id)->delete();
           $res3    = Db::name('major_edu_extend')->where('major_id','=',$major_id)->delete();
           if($res1 && $res2 && $res3){
               return ['msg'=>'删除成功','status'=>1];
           }else{
               exception('删除数据失败',-1);
           }
        }catch (\Exception $e) {
            return ['msg'=>'删除失败','status'=>0];
        }
    }
    
    //查找学校下面的科目（学历）
    public function getSchoolEduMajor($school_id){
        $where = "FIND_IN_SET($school_id,school_ids)";
        $res   =  db::name('major_edu')->field('major_id,name')->where($where)->select();
       return $res;
    }
    //查找专业的层级
    public  function getEduMajorLevel($major_id){
    
       $arr =  db::name('major_edu_extend')->where('major_id ='.$major_id)->field('level_id')->select();
       
       if($arr){
           $arrMajorLevel = $this->arrMajorLevel;
           $arrNew = array();
           foreach ($arr as $v){
               $arrNew[ $v['level_id'] ] = $arrMajorLevel[ $v['level_id'] ];
           }
          return $arrNew;
       }else{
          return [];
       }
       
    }
    
    //-----------------------------END 学历类--------------------------------------------------
    
    
    public function checkMajorList(){
        $major_id = input('get.major_id');
        $rs = Db::name('major_edu')
                ->alias('me')
                ->join('major_edu_extend mee','mee.major_id=me.major_id')
                ->field('me.major_num,me.name,me.exam_type,mee.level,mee.graduate_time,school_id,me.major_id')
                ->where('me.major_id',$major_id)
                ->paginate(input('post.pagesize/d'))->toArray();
        $data = [];
        foreach($rs['Rows'] as $k=>$v){
            if($v['exam_type'] == 1){
                $data[$k]['exam_type'] = '自考';
            }
            if($v['exam_type'] ==2){
                $data[$k]['exam_type'] = '成考';
            }
            if($v['exam_type'] ==3){
                $data[$k]['exam_type'] = '网教';
            }
            if($v['level'] == 1){
                $data[$k]['level'] = '高升专';
                $data[$k]['type'] = '专科';
            }
            if($v['level'] == 2){
                $data[$k]['level'] = '专升本';
                $data[$k]['type'] = '本科';
            }
            $data[$k]['level_id'] = $v['level'];
            $data[$k]['name'] = $v['name'];
            $data[$k]['major_num'] = $v['major_num'];
            $data[$k]['graduate_time'] = $v['graduate_time'];
            $data[$k]['major_id'] = $v['major_id'];
            $rs = Db::name('school')->field('name')->where('school_id',$v['school_id'])->select();
            $data[$k]['school_name'] = $rs[0]['name'];
            $major_num = $v['major_num'];
        }
        $rs['Rows'] = $data;
        return $rs;
    }
    public function pageQuerySubjectList(){
        $major_id = input('get.major_id');
        $level = input('get.level');
        $rs = Db::name('major_edu')
                ->alias('me')
                ->join('major_edu_extend mee','mee.major_id=me.major_id')
                ->field('mee.subject_ids')
                ->where(['me.major_id'=>$major_id,'mee.level'=>$level])
                ->paginate(input('post.pagesize/d'))->toArray();

        $subject_ids = $rs['Rows'][0]['subject_ids'];
        $subject_arr = explode(',', $subject_ids);
        $data = [];
        foreach ($subject_arr as $k=>$v){
            $rs = Db::name('subject_edu')
                    ->field('name,subject_type_id,subject_no,credit,genre,exam_method,exam_time')
                    ->where('subject_id',$v)
                    ->paginate(input('post.pagesize/d'))->toArray();
            $data[$k] = $rs['Rows'][0];
        }
        foreach($data as $k=>$v){
            switch ($v['genre']) {
                case 1:
                    $data[$k]['genre'] = '必考';
                    break;
                case 2:
                    $data[$k]['genre'] = '免考';
                    break;
                case 3:
                    $data[$k]['genre'] = '加考';
                    break;

                default:
                    break;
            }
            switch ($v['exam_method']) {
                case 1:
                    $data[$k]['exam_method'] = '笔试';
                    break;
                case 2:
                    $data[$k]['exam_method'] = '实践考核';
                    break;

                default:
                    break;
            }
            switch ($v['subject_type_id']) {
                case 1:
                    $data[$k]['subject_type_id'] = '学历';
                    break;
                case 1:
                    $data[$k]['subject_type_id'] = '技能';
                    break;
                default:
                    break;
            }
        }
        $rs['Rows'] = $data;
        return $rs;
    }
    
    //查找学校专业
    public function  getMajorEdu($where){
        $res = db::name('major_edu')->where( $where )
        ->field('major_id,name')
        ->select();
        return $res;
    }
    
    //查找学校专业
    public function  getMajor($where){
        $res = db::name('major_edu')->where($where)
        ->field('major_id,name')
        ->select();
        return $res;
    }
    //查找专业的层级
    public function  getMajorLevel($major_id){
        $res = $this->where('major_id='.$major_id)
        ->field('level_type')
        ->select();
        $arrLevel = array();
        if($res){
            $arrMajorLevel = $this->arrMajorLevel;
            foreach ($res as $v){
        	       $arrLevel[ $v['level_type'] ] =  $arrMajorLevel[ $v['level_type'] ] ;
            }
        }
        return $arrLevel;
    }
    
    //查找专业 下面的科目
    public function getMajorSubject($school_id,$major_id,$level_id){
        
        $where['s.school_id'] = $school_id;
        $where['m.major_id']  = $major_id;
        $where['me.level_id'] = $level_id;
         
        $join = array(
            array('major_edu_extend me','me.major_id = m.major_id'),
            array('school s','FIND_IN_SET(s.school_id,m.school_ids)','left'),
            array('mbis_subject_edu km','FIND_IN_SET(km.subject_id,subject_ids)','left')
        );
        $field = 'km.subject_id,km.name,km.subject_no,km.exam_method';
        $res   = db::name('major_edu')->alias('m')
                                      ->field($field)
                                      ->join($join)
                                      ->where($where)
                                      ->select();
        $arrNEW = array();
        foreach ($res as $v ){
            $arrNEW[$v['subject_id']] = $v; 
        }
        return $arrNEW;
    }
    
    //查找学员的专业 下 的 科目  ，是否 通过 （如果全部通过就毕业）
    public function graduation($userID,$school_id,$major_id,$level_id){
        if( !($userID && $school_id && $major_id && $level_id) ){
            return false;
        }
        //查找所有的科目
        $arrSubject = $this->getMajorSubject($school_id, $major_id, $level_id);
        //查找学员所有的科目信息
        $join = array(
            array('sj_exams_subject ex','ex.req_id = e.id','left'),
        );
        $field  = 'e.userId,e.school_id,e.major_id,e.level_id,
                   ex.subject_id,ex.subject_score,ex.exam_status,ex.status';
        $where                = array();
        $where['e.userId']    = ['in',$userID];
        $where['e.school_id'] = $school_id;
        $where['e.major_id']  = $major_id;
        $where['e.level_id']  = $level_id;
        
        $res  = db::name('sj_exams')->alias('e')
                                    ->field($field)
                                    ->join($join)
                                    ->where($where)
                                    ->select();
        $is_update = true;
        //查找毕业的人
        if($res){
            $arrUser = array();
            $arrUserKm = array();
            foreach ( $res as $key => $v ){
                $arrUser[ $v['userId'] ][] = array(
                     'subject_id'    => $v['subject_id'],
                     'subject_score' => $v['subject_score'],
                     'exam_status'   => $v['exam_status'],
                     'status'        => $v['status'],//报考状态
                );
                $arrUserKm[$v['userId'] ][] = $v['subject_id'];
            }
            foreach ($arrSubject as $v){
                $arrSubject1[] = $v['subject_id'];
            }
         
            foreach ($arrUserKm as $ukey => $u){
                $is_all = array_diff($arrSubject1,$u);
                if($is_all){
                    unset($arrUser[$ukey]);
                }
            }
            if(!$arrUser){
                return true;
            }
            $arrPass = array();
            foreach ($arrUser as $key  => $v ){
                $is_pass = true;
                foreach ($v as $k => $t){
                     //免考的
                     if($t['status'] == 3){
                         break;
                     }
                     //没有成绩
                     if( !$t['subject_score'] && !$t['exam_status'] ){
                         $is_pass = false;
                         break;
                     //理论小于60分 不合格
                     }elseif( $t['subject_score'] >0 && $t['subject_score']< 60 ){
                         $is_pass = false;
                         break;
                     //理论课不及格的    
                     }elseif( $t['exam_status'] == 1 ){
                         $is_pass = false;
                         break;
                     }
                }
                //考试通过的学员
                if($is_pass){
                    $arrPass[] = array(
                        'userId'    => $key
                    );
                }
            }
            $updata_person_id = session('MBIS_STAFF')->staffId;
            $time             = time();
            //更新
            if($arrPass){
                foreach ($arrPass as $v ){
                    $data = array(
                        'status'           => 1,
                        'update_time'      => $time,
                        'update_person_id' => $updata_person_id
                    );
                    $where = array();
                    $where['userId']    = ['in',$userID];
                    $where['school_id'] = $school_id;
                    $where['major_id']  = $major_id;
                    $where['level_id']  = $level_id;
                    $affow_id  = db::name('sj_exams')->where($where)->update($data);
                    if(!$affow_id){
                        $is_update = false;
                    }
                }
            }
        }
        return $is_update;
    }

    public function upSellEdu()
    {
        $id = input('post.id/d');
        $type_id = input("post.type_id/d");
        Db::startTrans();
        try{
            $result = db::name('major_edu')->where(['major_id'=>$id])->update(['is_sell'=>$type_id]);
            if(false !== $result){
                Db::commit();
                return MBISReturn("变更成功", 1);
            }
        }catch (\Exception $e) {
            Db::rollback();
            return MBISReturn('变更失败',-1);
        }
    }

    public function upSell()
    {
        $id = input('post.id/d');
        $type_id = input("post.type_id/d");
        Db::startTrans();
        try{
            $result = db::name('major')->where(['major_id'=>$id])->update(['is_sell'=>$type_id,'lastmodify'=>time()]);
            if(false !== $result){
                Db::commit();
                return MBISReturn("变更成功", 1);
            }
        }catch (\Exception $e) {
            Db::rollback();
            return MBISReturn('变更失败',-1);
        }
    }
    
    //查找学校专业[非学历]
    public function  getMajorSkill($where){
        $res = db::name('major')->where($where)
        ->field('major_id,name')
        ->select();
        return $res;
    }
   
}
