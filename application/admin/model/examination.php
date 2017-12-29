<?php
namespace application\admin\model;
use think\Db;
class examination extends Base{
	
	public $arrExamsStatus = array(
		0=>'未报考',
	    1=>'已报考',
	    2=>'免考',
	    3=>'缺考',
	    4=>'补考',
	);
	
	//审核
	public $arrAuditStatus  =  array(
			0=>'待审核',
			1=>'审核不通过',
			2=>'审核通过'
	);
	
	//是否审核过
	public function  audit($id){
	    $arrUsers = db::name('sj_exams')
                	    ->where('id='.$id)
                	    ->field('auditStatus')
                	    ->find();
	    if( $arrUsers['auditStatus'] == 2 ){
	        return true;
	    }else{
	        return false;
	    }
	}
	
	
	/**
	 * 分页
	 */
	public function pageQuery($export=""){
	 
	    $where = array();
	     $school_id  = input('school_id');
	     $major_id   = input('major_id');
	     $level_id   = input('level_id');
	     $search_title  = input('search_title');
	     $search_word   = input('search_word');
	      if($school_id){
	        $where['x.school_id'] = $school_id;
	     }
	     if($major_id){
	        $where['x.major_id'] = $major_id;
	     }
	     if($level_id){
	        $where['x.level_id'] = $level_id;
	     } 
	
	     if($search_title && $search_word){
	         switch ($search_title){
	             case 1:
	                 $where['u.student_no'] = ['like','%'.$search_word.'%'];
	               break;
                 case 2:
                     $where['u.trueName'] = ['like','%'.$search_word.'%'];
                   break;
	             case 3:
	                 $where['x.exam_no'] = ['like','%'.$search_word.'%'];
	               break;
	             case 4:
	                $where['u.idcard'] = ['like','%'.$search_word.'%'];
	               break;
	         }
	    }
	    $data = input('post.');
	    $whereS = '1=1';
	    //接收科目信息
	    if( isset($data['arrSubject']) ){
	        
	        //先做数据过滤
	        $join  = array(
	          /*   array('users u','u.userId=x.userId','left'),
	            array('school s','s.school_id=x.school_id','left'),
	            array('major_edu m','m.major_id=x.major_id','left'),
	            array('grade g','g.grade_id=x.grade_id','left'), */
	            array('sj_exams_subject subject','subject.req_id=x.id','left'),
	        );
	        //子查询 查找 最后一条 数据
	        $son_where = "subject.id in (select max(id) from mbis_sj_exams_subject as subjects
	                              where subjects.req_id = subject.req_id AND  subjects.subject_id = subject.subject_id
	                             group by req_id,subject_id desc)";
	        $res  = db::name('sj_exams')->alias('x')
                	        ->join($join)
                	        ->where($where)
                	        ->where($son_where)
                	        ->where(  "x.id in (  select req_id FROM  mbis_sj_exams_subject  as aa where aa.`req_id` = `x`.`id` AND  $whereS )" )
                	        
                	        ->field('x.id,GROUP_CONCAT( concat(subject.subject_id,\'-\',subject.status) ) as subject_ids
                            	         ')
                	        ->group('x.id')
                	        ->select();
	        if(empty($res)){
	            return '';
	        }
	        $arrSubject = $data['arrSubject'] ;
	        $arrS = array();
	        foreach ($arrSubject as $key => $v ){
	            $subject_id = $v['subject_id'];
	            $status = $v['status'];
	            $arrS[$subject_id]  = $status;
	            //$arrS[]  = '( subject.subject_id ='.$subject_id.' AND subject.status='.$status.' )';
	        }
	        $arrID = array();
	        //查找所有的科目报考情况
	        foreach ($res as $key => $v){
	            $subject_ids    =  $v['subject_ids'];
	            $arrSubjectInfo =  explode(',', $subject_ids);
	            foreach ($arrSubjectInfo as $k => $c){
	                $arr_subject    =  explode('-', $c);
	                $subject_id     = (int)$arr_subject[0];
	                $subject_status = (int)$arr_subject[1];
	                if( isset($arrS[$subject_id]) && $arrS[$subject_id] != $subject_status ){
	                    $arrID[] = $res[$key]['id'];
	                } 
	            }
	        }
	       // $whereS = implode(' AND ', $arrS);
	    }
	    $status_where = array();
	    //考试成绩分析排除的id
	    if( isset($arrID) && !empty($arrID) ){
	       $ids_no  =  implode(',', $arrID);
	       $status_where['x.id'] = ['not in',$ids_no];
	    }
	    
	    //所有的科目
	    $Major  = new \application\admin\model\Major();
	    $arrAll = $Major->getMajorSubject($school_id, $major_id, $level_id);
	    $arrAllNew = array();
	    foreach ($arrAll as $key => $v ){
	        $arrAllNew[] = $key;
	    }
	    $arrExamsStatus = $this->arrExamsStatus;
	    $arrAuditStatus = $this->arrAuditStatus;
	    $join  = array(
	        array('users u','u.userId=x.userId','left'),
	        array('school s','s.school_id=x.school_id','left'),
	        array('major_edu m','m.major_id=x.major_id','left'),
	        array('grade g','g.grade_id=x.grade_id','left'),
	        array('sj_exams_subject subject','subject.req_id=x.id','left'),
	    );
	    //子查询 查找 最后一条 数据 
	    $son_where = " subject.id in (select max(id) from mbis_sj_exams_subject as subjects
	                              where subjects.req_id = subject.req_id AND  subjects.subject_id = subject.subject_id
	                             group by req_id,subject_id desc)"; 
	    
	    if($export){
	        $resUser  = db::name('sj_exams')->alias('x')
                	        ->join($join)
                	        ->where($where)
                	        ->where($status_where)
                	        ->where($son_where)
                	        ->where(  "x.id in (  select req_id FROM  mbis_sj_exams_subject  as aa where aa.`req_id` = `x`.`id` AND  $whereS )" )
                	        
                	        ->field('x.*,u.trueName,u.student_no,u.idcard,s.school_id,s.name as school_name,
                                	              m.major_id,m.name as major_name,g.name as grade_name,
                                	              GROUP_CONCAT( subject.id ) as subject_ids
                                	         ')
                	                        	         //->order('id desc')
                	        ->group('x.id')
                	        ->select();
	        if(empty($resUser)){
	            return '';
	        }
	        $page['Rows'] = $resUser;
	    }else{
    	    $page  = db::name('sj_exams')->alias('x')
                	     ->join($join)
                	     ->where($where)
                	     ->where($status_where)
                	     ->where($son_where)
                	     ->where(  "x.id in (  select req_id FROM  mbis_sj_exams_subject  as aa where aa.`req_id` = `x`.`id` AND  $whereS )" )
                	     
                	     ->field('x.*,u.trueName,u.student_no,u.idcard,s.school_id,s.name as school_name,
                	              m.major_id,m.name as major_name,g.name as grade_name,
                	              GROUP_CONCAT( subject.id ) as subject_ids
                	         ')
                	     //->order('id desc')
                	     ->group('x.id')
                	     //->select();
                	     ->paginate(input('post.pagesize/d'))
                	     ->toArray();
	    
	    }
	    foreach ($page['Rows'] as $key => $v){
	          
	    	    $page['Rows'][$key]['statusText'] = $arrAuditStatus[ $v['bkAuditStatus'] ];
	            $subject_ids = $v['subject_ids'];
	            $where = array();
	            $where['a.id'] = ['in',$subject_ids];
	            $join = array(
	                array('subject_edu ex','ex.subject_id = a.subject_id','left')
	            );
    	           $res  = db::name('sj_exams_subject')
    	                     ->alias('a')
    	                     ->field('a.id,ex.subject_id,ex.name,a.baokao_time,a.status')
    	                     ->join($join)
    	                     ->where($where)
    	                     ->select();
    	        $arrROW = array();
    	        foreach ($res as $k => $t){
    	        	$page['Rows'][$key]['baokao_time'] = date('Ym');
    	            //导出数据
    	            if($export){
    	                if($t['subject_id']>0){
    	                    $page['Rows'][$key]['sub'][$t['subject_id']] = $arrExamsStatus[$t['status']];
    	                }
    	                $arrROW[] =  $t['subject_id'];
    	            //列表页
    	            }else{
        	            if($t['subject_id']>0){
            	            $page['Rows'][$key]['kk'.$t['subject_id'] ] = '<span class="km" id="'.$t['id'].'"  data-value="'.$t['status'].'">'
            	                                           .$arrExamsStatus[$t['status']]
            	                                           .'</span>';
        	            }
        	            $arrROW[] =  $t['subject_id'];
    	            }
    	        }
    	        // exit;
    	        //比较2交集
    	        $arr = array_diff($arrAllNew, $arrROW);
    	        if($arr){
    	            foreach ($arr as $tt ){
    	               $page['Rows'][$key]['sub'][$tt] = '';
    	            }
    	        }
	    }
	    //查找学员所有的科目
	    if($export){
	        $page['allSubject'] = $arrAll;
	        return  $page;
	    }else{
    	    if(!empty($arrAll)){
        	    foreach ($arrAll as $key => $v){
        	         $page['subjectList'][] =  array('name'=>$v['name'].'('.$v['subject_no'].')','value'=>'kk'.$v['subject_id']);
        	    }
    	    }
	    }
	    return $page;
	}
	
	//查找历史记录
	public function getHistory(){
	    
	    $id    = input('id');
	    $where = array();
	    $where['req_main_id'] = $id;
	    $arr   = db::name('sj_exams_history')->field('school_id,major_id,level_id')->where($where)->find();
	    $school_id = $arr['school_id'];
	    $major_id  = $arr['major_id'];
	    $level_id  = $arr['level_id'];
	    //所有的科目
	    $Major  = new \application\admin\model\Major();
	    $arrAll = $Major->getMajorSubject($school_id, $major_id, $level_id);
	    $arrAllNew = array();
	    foreach ($arrAll as $key => $v ){
	        $arrAllNew[] = $key;
	    }
	
	    $arrExamsStatus = $this->arrExamsStatus;
	    $join  = array(
	        array('users u','u.userId=x.userId','left'),
	        array('school s','s.school_id=x.school_id','left'),
	        array('major_edu m','m.major_id=x.major_id','left'),
	        array('grade g','g.grade_id=x.grade_id','left'),
	        array('staffs yg','yg.staffId=x.update_person_id','left'),
	        
	    );
	     
	    $page = db::name('sj_exams_history')->alias('x')
                        	    ->join($join)
                        	    ->where($where)
                        	    ->field('x.*,u.trueName,u.student_no,u.idcard,s.school_id,s.name as school_name,
                                    	 m.major_id,m.name as major_name,g.name as grade_name,
                        	             yg.staffName,yg.staffNo
                        	             '
                        	        )
                        	     ->order('update_time DESC')
                                 ->select();
	    $data = array();
	    foreach ($page as $key => $v){
	        
	        $data[$key][]  = $key+1;//历史记录序号
	        $data[$key][]  = $v['trueName'];//姓名
	        $data[$key][]  = $v['student_no'];//学员编号
	        $data[$key][]  = $v['idcard'];//身份证号
	        $data[$key][]  = $v['exam_no'];//准考证号
	        $data[$key][]  = $v['exam_password'];//准考证号密码
	        //$data[$key][]  = $v['baokao_time'];//报考时间
	        $data[$key][]  = $v['major_name'];//报考专业
	        $data[$key][]  = $v['school_name'];//报考院校
	        $data[$key][]  = $v['grade_name'];//年级
	        
	        //查找存在的科目
	        
	        //查找科目
	        $sj_id = $v['id'];
	        $res  = db::name('sj_exams_subject_history')->alias('a')
                            	        ->field('b.subject_id,b.name,a.status,a.id')
                            	        ->join('subject_edu b','a.subject_id = b.subject_id','left')
                            	        ->where('a.req_id='.$sj_id)
                            	        ->select();
	        $arrSub = array();
	        foreach ($res as $k => $t){
	            $arrSub[ $t['subject_id'] ] = $arrExamsStatus[$t['status']];
	        }
	        
	        //查找学员所有的科目  默认按顺序填充
	        if(!empty($arrAll)){
	            foreach ($arrAll as $key1 => $v1){
	                $data[$key][] =  $arrSub[ $v1['subject_id'] ];
	            }
	        }
	        $data[$key][] =  $v['staffName'].'('.$v['staffNo'].')<br>'.date('Y-m-d H:i:s',$v['update_time']);
	    }
	    return MBISApiReturn( MBISReturn('',1,$data ) ); 
	}
	
	
	
	//编辑数据
	public function editData($id,$status){
	    $sj_exams_subject = db::name('sj_exams_subject');
	    $req_id   = $sj_exams_subject->where('id','=',$id)->value('req_id');
	    $is_pass  = $this->audit($req_id);
	    if($is_pass){
	        return '该数据已审核通过，不得修改';
	    }
	    $data  = array(
	             'status'   => $status,
	        'updata_time'   => time(),
	     'updata_person_id' => session('MBIS_STAFF')->staffId, 
	    );
	    $sj_exams_subject = db::name('sj_exams_subject');
	    $aff_id = $sj_exams_subject->where('id','=',$id)->update($data);
	    return $aff_id;
	}
	
	//删除数据
	public function delData($id){
	    $is_pass  = $this->audit($id);
	    if($is_pass){
	        return '该数据已通过审核,不能再修改';
	    }
	    Db::startTrans();
	    try{
    	    $aff_id1 = db('sj_exams')->where('id','=',$id)->delete();
    	    $aff_id2 = db('sj_exams_subject')->where('req_id','=',$id)->delete();
    	    if($aff_id1 && $aff_id2){
    	        Db::commit();
    	    }else{
    	        Db::rollback();
    	    }
	    } catch (\Exception $e) {
	        Db::rollback();
	        return false;
	    }
	   return true;      
	}
	
	
	//导入数据
	public function importUsers($file){
	    //读取exel 数据
	    $arrContent = importExcel($file);
	    return $this->template2($arrContent);
	}
	
	//模板一
	public function template1($arrContent){
	    
	    
	    
	}
	
	//模板二
	public function template2($arrContent){
	    //列表的选项
	    $bkKey     = 2;//报考时间
	    $usreKey   = 3;//用户身份证
	    $zkKey     = 4;//准考证号
	    $zkpassKey = 5;//准考证号密码
	    $schoolKey = 6;//学校编号
	    $majorKey  = 7;//专业
	    $levelKey  = 8;//层级
	    $gradeKey  = 9;//年级
	   
	    //dump($arrContent);
	    $majorObj       = new \application\admin\model\Major;
	    $arrExamsStatus = $this->arrExamsStatus;//报考状态
	    $arrExamsStatus = array_flip($arrExamsStatus);
	    $uid  =  session('MBIS_STAFF')->staffId;
	    $time =  time();
	    if($arrContent){
	        foreach($arrContent as $key => $v ){
	             
	            if($key==1){
	                 
	                //查找学校
	                $school_number = ( string )$v[$schoolKey];#####
	                $res           =  substrstring($school_number);
	                $where['school_no'] = ['=',$res];
	                $arrShool  = db::name('school')->field('school_id')->where($where)->find();
	                $school_id = $arrShool['school_id'];
	                if(!$school_id){
	                    return '学校编号未找到！！！';
	                }
	                 
	                //查找专业
	                $major_number = ( string )$v[$majorKey];#####
	                $res_major    = substrstring($major_number);
	                $whereM['major_number'] = ['=',$res_major];
	                $arrMajor  = db::name('major_edu')->field('major_id')->where($whereM)->find();
	                $major_id  = $arrMajor['major_id'];
	                if(!$major_id){
	                    return '专业编号未找到';
	                }
	                 
	                //查找专业层次
	                $level         = ( string )$v[$levelKey];#####
	                $level         = substrstring($level);
	                if($level==false){
	                    return '专业层次没有找到';
	                }
	                $arrMajorLevel = $majorObj->arrMajorLevel;
	                $arr_level     = array_flip($arrMajorLevel);
	                if(empty($arr_level[$level])){
	                    return '专业层次有误';
	                }
	                $level_id                = $arr_level[$level];
	                 
	                //年级
	                $grade         = ( string )$v[$gradeKey];#####
	                $grade         = substrstring($grade);
	                //查找年级
	                if(!$grade){
	                    return '年级有误1';
	                }
	                $grade_id = db::name('grade')->where('name','=',$grade)->value('grade_id');
	                if(!$grade_id){
	                    return '年级有误2';
	                }
	                //查找报考时间
	                $baokaoTime =  $v[$bkKey];
	                if(!$baokaoTime){
	                    return '报考时间有误';
	                }
	                //查找后面的所有科目
	                $arrSub = array();
	                foreach ($v as $kk => $s){
	                    if($kk>$gradeKey){
	                        $r = substrstring($s);
	                        if($r==false){
	                            return '专业编号未填！！！';
	                        }
	                        $arrSub[] = $r;
	                    }
	                }
	                if(!$arrSub){
	                    return '没有科目信息！！';
	                }
	                //查找科目id
	                $whereSub['subject_no'] = ['in',implode(',', $arrSub)];
	                $arrSubject  = db::name('subject_edu')->field('subject_id')->where($whereSub)->select();
	                if( count($arrSub) !=  count($arrSubject) ){
	                    return '科目编号有错误';
	                }
	                //查找所有的科目id
	                foreach ($arrSubject as $kkk => $vvv ){
	                    $arrAllSubject[] = $vvv['subject_id'];
	                }
	                 
	                //----------------------------数据---------------
	            }else{
	                //学员
	                if(empty($v[$usreKey])){
	                    return '学员身份证不能为空！！！';
	                }
	                $arrUser[ $v[$usreKey] ]['userId'] = $v[$usreKey];
	                $arrUserId[]  = $v[$usreKey];
	    
	                //报考时间
	                $baokaoTime = $v[$bkKey];
	                $baokaoTime = strtotime($baokaoTime);
	                if(!$baokaoTime){
	                    return '时间格式错误正确格式 2017-05！！！';
	                }
	                //准考证号
	                $arrZkNo  = array();
	                //科目对应的成绩
	                $i=0;
	                foreach ($v as $kk => $s){
	                    //准考证号
	                    if($kk == $zkKey ){
	                        if(!$s){
	                            return '准考证号不全';
	                        }
	                        $arrUser[ $v[$usreKey] ]['exam_no'] = $s;
	                    }
	                    //准考证密码
	                    if($kk == $zkpassKey){
	                        if(!$s){
	                            return '准考证密码不存在';
	                        }
	                        $arrUser[ $v[$usreKey] ]['exam_password'] = $s;
	                    }
	                    if($kk>$gradeKey){
	                        $s = trim($s);
	                         
	                        if( !isset($arrExamsStatus[$s]) ){
	                            return '报考状态错误';
	                        }
	                         
	                        if( count( $arrAllSubject ) + ($gradeKey+1) != count($v) ){
	                            return '科目数目不对';
	                        }
	                        //报考的状态
	                        $arrUser[ $v[$usreKey] ]['subject'][] = array(
	                            'subject_id' => $arrAllSubject[$i],
	                            'status'     => $arrExamsStatus[$s],
	                            'baokao_time' => $baokaoTime,
	                            'updata_time'=> $time,
	                            'updata_person_id' =>$uid
	                        );
	                        $i++;
	                    }
	                }
	            }
	        }
	         
	    }else{
	        return '数据不存在';
	    }
	    if(!$arrUserId){
	        return '学员数据不存在';
	    }
	    Db::startTrans();
	    
	    try{
	        $userIds = implode(',', $arrUserId);
	        $where   = array();
	        $where['idcard'] = ['in',$userIds];
	        //查找用户
	        $arrUsers =db::name('users')->alias('s')
	        ->field('s.userId,s.trueName,s.idcard')
	        ->where($where)
	        ->select();
	        if(!$arrUsers){
	            exception('找不到用户信息');
	        }
	         
	        if( !($school_id && $major_id && $level_id) ){
	            exception('学校，专业，层级查找不到信息');
	        }
	    
	        $time = time();
	        $person_id = session('MBIS_STAFF')->staffId;
	        //$userSubject 用户对的 成绩
	        foreach ($arrUsers as $key => $v ){
	            $data = array(
	                'userId'           => $v['userId'],
	                'examination_type' => 1,
	                'school_id'        => $school_id,
	                'major_id'         => $major_id,
	                'level_id'         => $level_id,
	                'grade_id'         => $grade_id,
	                'exam_no'          => $arrUser[$v['idcard']]['exam_no'],//准考证号
	                'exam_password'    => $arrUser[$v['idcard']]['exam_password'],//准考证号
	                'data_type'        => '1',  //数据类型：0=正常录入 1=批量导入
	            );
	            $sj_exams         = db::name('sj_exams');
	             
	            //先查找 用户 是否 已经 有报考
	            $where   = array();
	            $where['userId']    = $v['userId'];
	            $where['school_id'] = $school_id;
	            $where['major_id']  = $major_id;
	            $where['level_id']  = $level_id;
	            $arrEX = $sj_exams->where($where)->find();
	    
	            //如果存在考试信息不需要再次 添加
	            if($arrEX){
	                //审核通过
	                if( $arrEX['auditStatus'] == 2){
	                    break;
	                }
	                $userSubject = $arrUser[ $v['idcard'] ]['subject'];
	                foreach ($userSubject as $su){
	                    //查找已存在的科目
	                    $where = array();
	                    $where['req_id']     = $arrEX['id'];
	                    $where['subject_id'] = $su['subject_id'];
	                    $sj_exams_subject = db::name('sj_exams_subject');
	                    $arrS  = $sj_exams_subject->where($where)->order('id DESC')->find();
	                     
	                    //如果科目存在
	                    if($arrS){
	                        //补考
	                        if($su['status']==5){
	                            $data = array(
	                                'req_id'           => $arrEX['id'],
	                                'subject_id'       => $su['subject_id'],
	                                'status'           => $su['status'],//报考状态
	                                'baokao_time'      => $su['baokao_time'],
	                                'updata_time'      => $time,
	                                'updata_person_id' => $person_id,
	                            );
	                            $sj_exams_subject = db::name('sj_exams_subject');
	                            $sj_exams_subject->insert($data);
	                            $id =  $sj_exams_subject->getLastInsID();
	                            if(!$id){
	                                exception($v['trueName'].'补考数据插入错误');
	                            }
	                            //更新成绩
	                        }else{
	                            $data = array(
	                                'req_id'           => $arrEX['id'],
	                                'subject_id'       => $su['subject_id'],
	                                'status'           => $su['status'],//报考状态
	                                'baokao_time'      => $su['baokao_time'],
	                                'updata_time'      => $time,
	                                'updata_person_id' => $person_id,
	                            );
	                            $where = array();
	                            $where['req_id']     =  $arrEX['id'];
	                            $where['subject_id'] =  $arrS['subject_id'];
	                            $sj_exams_subject = db::name('sj_exams_subject');
	                            $affow_id = $sj_exams_subject->where($where)->update($data);
	                            if(!$affow_id){
	                                exception($v['trueName'].'更新数据插入错误');
	                            }
	                        }
	                        //添加没有录入的科目成绩
	                    }else{
	                         
	                        if( $su['status'] == 4 ){
	                            exception($v['trueName'].'数据错误');
	                        }
	                        $data   = array(
	                            'req_id'           => $arrEX['id'],
	                            'subject_id'       => $su['subject_id'],
	                            'status'           => $su['status'],//报考状态
	                            'baokao_time'      => $su['baokao_time'],
	                            'updata_time'      => $time,
	                            'updata_person_id' => $person_id,
	                        );
	                        $sj_exams_subject = db::name('sj_exams_subject');
	                        $sj_exams_subject->insert($data);
	                        $id =  $sj_exams_subject->getLastInsID();
	                        if(!$id){
	                            exception($v['trueName'].'添加数据错误');
	                        }
	                         
	                    }
	                }
	                //否则添加报考信息
	            }else{
	                $sj_exams->insert($data);
	                $id =  $sj_exams->getLastInsID();
	            	    
	                if(!$id){
	                    exception($v['trueName'].'科目报考添加数据错误');
	                }
	            	    
	                $userSubject = $arrUser[ $v['idcard'] ]['subject'];
	                $data1 = array();
	                foreach ($userSubject as $su){
	                    if( $su['status'] == 4 ){
	                        exception($v['trueName'].'添加非法数据错误');
	                    }
	                    $data1[] = array(
	                        'req_id'           => $id,
	                        'subject_id'       => $su['subject_id'],
	                        'status'           => $su['status'],//报考状态
	                        'baokao_time'      => $su['baokao_time'],
	                        'updata_time'      => $time,
	                        'updata_person_id' => $person_id,
	                    );
	                }
	                $id1 = db::name('sj_exams_subject')->insertAll($data1);
	                if(!$id1){
	                    exception($v['trueName'].'科目报考添加数据错误');
	                }
	                //修改学员年级
	                //$school_id,   $major_id, $level_id,$grade_id,
	                $where = array();
	                $where['school_id'] = $school_id;
	                $where['major_id']  = $major_id;
	                $where['level_id']  = $level_id;
	                $where['userId']    = $v['userId'];
	                $data = array('grade_id'=>$grade_id);
	                $affow_grade = db::name('student_edu')->where($where)->update($data);
	                if(!$affow_grade){
	                    exception($v['trueName'].'学员年级修改错误');
	                }
	    
	    
	            }
	             
	        }
	        Db::commit();
	        return true;
	    }catch (\Exception $e) {
	        Db::rollback();
	        return $e->getMessage();
	    }
	    
	}
	
	
	
	
	
	
	
	
	//手动添加数据
    public function addData(){
	 //查找学员数据
	 if( request()->isAjax() ){
	        //查找年级
	         $action = input('action');
	         
	         if( $action == 'grade_id' ){
	             $search_name = input('search_name');
	             $where['name'] =  ['like','%'.$search_name.'%'];
	            return  db::name('grade')->field('grade_id as id,name')->where($where)->LIMIT(5)->select();
	         }
        	 //查找学员
        	 $search_name = input('search_name');
        	 if($search_name){
            	 $where = "trueName like '%$search_name%' OR  userPhone like '%$search_name%'
            	 OR idcard like '%$search_name%' ";
            	 $res = db('users')->field('userId as id,trueName as name')
                            	   ->where($where)
                            	   ->limit(10)
                            	   ->select();
            	 return $res;
        	 }
        	 //查找学员信息
        	 if( input('post.action') == 'userInfo' ){
            	 $userId = input('post.userId');
            	 $where['xl.userId'] = $userId;
            	 $join = array(
            	       array('users u','u.userId = xl.userId','left'),
            	      // array('major m','m.major_id = xl.major_id','left'),
            	 );
            	 $field = 'xl.edu_id,u.trueName,u.student_no,u.idcard,xl.school_name,xl.school_id,
                    	   xl.major_id,xl.major_name,
                    	   if( xl.level_id =2,\'高升专\',\'专升本\' ) as level_name,
                    	   level_id
            	           ';
            	 $res = db::name('student_edu')->alias('xl')
                                        	   ->join($join)
                                        	   ->field($field)
                                        	   ->where($where)
                                        	   ->select();
            	 return $res;
        	
        	 }
        	 //查找该用户报考 所有的科目
        	 if( input('post.action') == 'subject' ){
        	     
                	 $userId    = input('userId');
                	 $school_id = input('school_id');
                	 $major_id  = input('major_id');
                	 $level_id  = input('level_id');
                
            	 if( $userId && $school_id && $major_id && $level_id){
            	     
                	 $where['s.school_id'] = $school_id;
                	 $where['m.major_id']  = $major_id;
                	 $where['me.level_id'] = $level_id;
                	  
                	 $join = array(
                    	 array('major_edu_extend me','me.major_id = m.major_id'),
                    	 array('school s','FIND_IN_SET(s.school_id,m.school_ids)','left'),
                    	 array('mbis_subject_edu km','FIND_IN_SET(km.subject_id,subject_ids)','left')
                	 );
                	 $where['km.subject_id'] = ['>',0];
                	 $field = 'km.subject_id,km.name,km.exam_method';
                	 $res   = db::name('major_edu')->alias('m')
                                            	   ->field($field)
                                            	   ->join($join)
                                            	   ->where($where)
                                            	   ->select();
                	 //查找学员已经报考的科目
                	 $join = array(
                	     array('sj_exams_subject ex','ex.req_id = a.id')
                	 );
                	 $res1 = db::name('sj_exams')->alias('a')
                	                     ->join($join)
                	                     ->field('ex.subject_id,ex.status')
                	                     ->select();
                	 $arr = array();
                	 foreach ($res1 as $v ){
                	     $arr[ $v['subject_id'] ] = $v['status'];
                	 }
                	 return array( $res,$arr);
            	 }else{
            	     return ['statu'=>0,'msg'=>'参数错误！！！'];
            	 }
	
	 }
	
	 //保存数据
	 if( input('action')=='addSubject' ){
    	 $userID        = input('userID');
    	 $edu_id        = input('edu_id');
    	 $school_id     = input('school_id');
    	 $major_id      = input('major_id');
    	 $level_id      = input('level_id');
    	 
    	 $exam_no       = input('exam_no');
    	 $exam_password = input('exam_password');
    	 $baokao_time   = input('baokao_time');
    	 
    	 $grade_id      = input('grade_id');
    	 $subjectString = input('subjectString');
    	 
    	 if(!$edu_id){
    	     return ['status'=>0,'msg'=>'参数错误！！！'];
    	 }
    	 
    	 if(!$exam_no){
    	     return ['status'=>0,'msg'=>'准考证号码不能为空！！！'];
    	 }
    	 
    	 if( !$exam_password ){
    	     return ['status'=>0,'msg'=>'准考证密码不能为空！！！'];
    	 }
    	 $baokao_time = strtotime($baokao_time);
    	 if( !$baokao_time ){
    	     return ['status'=>0,'msg'=>'报考时间不能为空！！！'];
    	 }
    	 
    	 if(!$grade_id){
    	     return ['status'=>0,'msg'=>'年级信息不能为空！！！'];
    	 }
    	 $grade_name = db::name('grade')->where('grade_id='.$grade_id)->value('name');
    	 
    	 if($userID && $school_id && $major_id && $level_id && $subjectString  )
    	 {
        	 //查找按个专业下的考试
        	 $where['req_edu_id']    = $edu_id;
        	 $arr = db::name('sj_exams')->field('id,bkAuditStatus')->where($where)->find();
        	 //修改
        	 if( $arr ){
        	     if($arr['bkAuditStatus'] ==2){
        	     	return ['status'=>0,'msg'=>'改数据已审核通过，不得修改！！！'];
        	     }
        	     Db::startTrans();
        	     try{
        	         $time      = time();
        	         $person_id = session('MBIS_STAFF')->staffId;
        	     
        	         $data = array(
        	             'exam_no'          => $exam_no,//准考证号
        	             'exam_password'    => $exam_password,//准考证密码
        	             'grade_id'         => $grade_id,//
        	             'update_time'      => $time,
        	             'update_person_id' => $person_id,
        	         );
        	         $sj_exams         = db::name('sj_exams');
        	         $affow_id  = $sj_exams->where( 'id = '.$arr['id'].' AND bkAuditStatus !=2' )->update($data);
        	         if(!$affow_id){
        	             exception('添加报考信息失败！！！');
        	         }
        	        
        	         //保存历史记录
        	     /*     $is_history = $this->saveHistory($arr['id']);
        	         if(!$is_history){
        	             exception('保存历史记录失败');
        	         } */
        	          
        	         //修改学员年级
        	         //$school_id,   $major_id, $level_id,$grade_id,
        	         $where = array();
        	         $where['edu_id'] = $edu_id;
        	         $data = array('grade_id'    => $grade_id,
        	         		       'grade_name'  => $grade_name, 
        	         		       'update_time' => $time
        	         		
        	                  );
        	         $affow_grade = db::name('student_edu')->where($where)->update($data);
        	         if(!$affow_grade){
        	             exception('学员年级修改错误');
        	         }
        	         
        	         //学员下面的报考信息 修改
        	         $arrSubject = explode('--', $subjectString);
        	         foreach ($arrSubject as $key => $v){
        	             //查找科目考试 类型
        	             $subject_Info =  explode('-', $v);
        	             if(!$subject_Info){
        	                 exception('科目信息错误1');
        	             }
        	             $subject_id     = $subject_Info[0];//科目id
        	             $subject_value  = $subject_Info[1];//报考状态
        	             /* if(!$subject_value>0){
        	                 break;
        	             } */
        	            // dump($subject_id.'xxx'.$subject_value);
        	           
        	             $sj_exams_subject = db::name('sj_exams_subject');
        	             $arrSU = $sj_exams_subject->where('req_id = '.$arr['id'].' AND subject_id ='.$subject_id)->group('id desc')->find(); 
        	            
        	             //如果科目存在 则修改
        	             if($arrSU['id']){
        	                 $data = array(
        	                 	'id'             =>  $arrSU['id'],
        	                     'baokao_time'      => $baokao_time,
        	                     'status'           => $subject_value,//报考状态
        	                 	 'baokao_time'      => $baokao_time,
        	                     'updata_time'      => $time,
        	                     'updata_person_id' => $person_id,
        	                 );
        	                 $affow_id2 = $sj_exams_subject->where('id = '.$arrSU['id'])->group('id desc')->update($data);
        	             //科目不存在 则添加
        	             }else{
        	                 $data = array(
        	                     'req_id'           => $arr['id'],
        	                     'baokao_time'      => $baokao_time,
        	                     'subject_id'       => $subject_id,
        	                     'status'           => $subject_value,//报考状态
        	                     'updata_time'      => $time,
        	                     'updata_person_id' => $person_id,
        	                 );
        	                 $affow_id2 = $sj_exams_subject->insertGetId($data);
        	             }
        	             if(!$affow_id2){
        	                 exception('报考科目数据添加失败');
        	             }
        	         }
        	         Db::commit();
        	         return ['status'=>1,'msg'=>'学员报考成功'];
        	          
        	     }catch (\Exception $e){
        	         Db::rollback();
        	         return ['status'=>0,'msg'=>$e->getMessage()];
        	          
        	     }
        	     //return ['status'=> 0,'msg'=>'报考信息已存在！！！'];
        	 }else{
        	     
        	     Db::startTrans();
        	     try{
                	 $time      = time();
                	 $person_id = session('MBIS_STAFF')->staffId;
                
                	 $data = array(
                	     'userId'           => $userID,
                	     'examination_type' => 1,
                	     'req_edu_id'       => $edu_id,//报名表id 2017-5-10 新加
                	     'school_id'        => $school_id,
                	     'major_id'         => $major_id,
                	     'level_id'         => $level_id,
                	     'exam_no'          => $exam_no,//准考证号
                	     'exam_password'    => $exam_password,//准考证密码
                	     //'baokao_time'      => $baokao_time,//报考时间
                	     'grade_id'         => $grade_id,//
                	     'data_type'        => '0',  //数据类型：0=正常录入 1=批量导入
                	 );
                	 $sj_exams         = db::name('sj_exams');
                	 $sj_exams->insert($data);
                	 $id =  $sj_exams->getLastInsID();
                	 if(!$id){
                	     exception('添加报考信息失败！！！');
                	 }
               
                	 //修改学员年级
                	 //$school_id,   $major_id, $level_id,$grade_id,
                	 $where = array();
                	 $where['edu_id'] = $edu_id;
                	 $data = array('grade_id'=>$grade_id,'update_time'=>$time);
                	 $affow_grade = db::name('student_edu')->where($where)->update($data);
                	 if(!$affow_grade){
                	     exception('学员年级修改错误');
                	 } 
                	 
                	 $arrSubject = explode('--', $subjectString);
                        	 foreach ($arrSubject as $key => $v){
                        	    //查找科目考试 类型
                        	     $subject_Info =  explode('-', $v);
                            	 if(!$subject_Info){
                            	   exception('科目信息错误1');
                            	 }
                            	 $subject_id     = $subject_Info[0];//科目id
                            	 $subject_value  = $subject_Info[1];//报考状态
                                /*  if(!$subject_value){
                                     break;
                                 } */
                        	     $data = array(
                                    'req_id'           => $id,
                                    'subject_id'       => $subject_id,
                                    'status'           => $subject_value,//报考状态
                                    'updata_time'      => $time,
                                    'updata_person_id' => $person_id,
        	                     );
                                 $sj_exams_subject = db::name('sj_exams_subject');
        	                     $sj_exams_subject->insert($data);
        	                     $id2 =  $sj_exams_subject->getLastInsID();
        	                 
                                 if(!$id2){
                                   exception('报考科目数据添加失败');
                                 }
                               
                        	 }
                        	
                    	     Db::commit();
                    	     return ['status'=>1,'msg'=>'学员报考成功'];
                	  
                	 }catch (\Exception $e){
                    	 Db::rollback();
                    	 return ['status'=>0,'msg'=>$e->getMessage()];
                	  
                	 }
	 
            	}
	  
        	 }else{
        	       return ['status'=>0,'msg'=>'参数非法！！！'];
        	 }
	
	      }
	
	    }
	
	 }
	 
	 
	 /**
	  * 历史记录
	  * @param unknown $id 原表的id
	  */
	 function saveHistory($id){
	       //查找更新之前的记录
	       $data1 = db::name('sj_exams')->where('id ='.$id)->field('*')->find();
	       $data1['req_main_id'] = $id;
	       unset($data1['id']);
	       $sj_exams_history = db::name('sj_exams_history');
	       $sj_exams_history->insert($data1);
	       $id1 = $sj_exams_history->getLastInsID();
	       if($id1){
	          $data2 = db::name('sj_exams_subject')->where('req_id ='.$id)
	                                               ->field('
                                                    req_id,
                                                    subject_id,
                                                    subject_score,
                                                    status,
                                                    exam_time,
                                                    updata_time,
                                                    updata_person_id,
                                                    exam_status,
                                                    works_data')
	                                               ->select();
	          foreach ($data2 as $key => $v ){
	              $data2[$key]['req_id'] =  $id1;
	          }
	         $id2 =  db::name('sj_exams_subject_history')->insertAll($data2);
	         if(!$id2){
	             return fasle;
	         }
	       }else{
	           return false;
	       }
	       return true;
	 }
	 
	
}
