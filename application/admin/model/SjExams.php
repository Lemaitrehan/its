<?php
namespace application\admin\model;
use think\Db;
class sjexams extends Base{
	
	public $arrExamsStatus = array(
		0=>'未报考',
	    1=>'已报考',
	    2=>'免考',
	    3=>'缺考',
	    4=>'补考',
	);
	
	//实践课考试状态
	public $arrExmsPassStatus = array(
	     -1=>'作品未上传',
	      0=>'成绩未出',
	      1=>'不及格',
	      2=>'及格',
	      3=>'中等',
	      4=>'良好',
	      5=>'优秀',
	);
	//考试结构
	public $arrPassStatus = array(
	    0=>'没通过',
	    1=>'通过',
	);
	
	//考试结构
	public $arrGraduationStatus = array(
	    0=>'未毕业',
	    1=>'毕业',
	);
	
	//审核
	public $arrAuditStatus  =  array(
	    0=>'待审核',
	    1=>'审核不通过',
		2=>'审核通过'	
	);
	
	public $error = '';
	
	
	//是否审核过
	public function  audit($id){
	    $arrUsers = db::name('sj_exams')->where('id='.$id)
                	     ->field('auditStatus')
                	     ->find();
	    if( $arrUsers['auditStatus'] == 1 ){
	        return true;
	    }else{
	        return false;
	    }
	}
	
	/**
	 * 分页
	 */
	public function pageQuery($export=""){
	    //查找审核权限的按钮
	    $person_id   = session('MBIS_STAFF')->staffId;
	    $auditStatus = model('Review')->reviewShow('CKCJ_00',$person_id);
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
	     
	     $data = input('get.');
	     //接收科目信息
	     if( isset($data['arrSubject']) ){
	         //先做数据过滤
	         $join  = array(
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
                            	         ->where(  "x.id in (  select req_id FROM  mbis_sj_exams_subject  as aa where aa.`req_id` = `x`.`id`  )" )
                            	          
                            	         ->field('x.id,GROUP_CONCAT( concat(subject.subject_id,\'&&\',subject.subject_score,\'^^\',subject.exam_status) ) as subject_ids
                                                        	         ')
                            	          ->group('x.id')
                            	          ->select();
	         if(empty($res)){
	             return '';
	         }
	         $arrSubject = $data['arrSubject'] ;
	         $arrS = array();
	         foreach ($arrSubject as $key => $v ){
	             $subject_id         = $v['subject_id'];//科目id
	             $exam_method        = $v['exam_method'];//科目考试类型
	             $ys                 = isset($v['ys'])?$v['ys']:'';//运算
	             $subject_score      = isset($v['subject_score'])?$v['subject_score']:'';//科目状态
	             $exam_status        = isset($v['exam_status'])?$v['exam_status']:'';//科目分数
	             $arrS[$subject_id]  = array(
	             		'exam_method'=>$exam_method,
	             		'ys'=>$ys,
	             		'subject_score'=>$subject_score,
	             		'exam_status'=>$exam_status
	             );
	         }
	         $arrID = array();
	         //查找所有的科目报考情况
	         foreach ($res as $key => $v){
	             $subject_ids    =  $v['subject_ids'];
	             $arrSubjectInfo =  explode(',', $subject_ids);
	             foreach ($arrSubjectInfo as $k => $c){
	                 $arr_subject    =  explode('&&', $c);
	                 $subject_id     = (int)$arr_subject[0];//科目id
	                 $su             = $arr_subject[1];
	                 $arrsu          =  explode('^^', $su);
	                 $subject_score  = $arrsu[0];//科目分数
	                 $exam_status    = (int)$arrsu[1];//科目实践考试状态
	                 //理论考试
	                 if( isset($arrS[$subject_id]) && $arrS[$subject_id]['exam_method'] == 1  ){
	                 	 $ys = $arrS[$subject_id]['ys'];
	                 	 switch ($ys){
	                 	 	case '=':
	                 	 		if( $subject_score != $arrS[$subject_id]['subject_score']){
	                 	 			$arrID[] = $res[$key]['id'];
	                 	 		}
	                 	 	  break;
	                 	 	case '>':
		                 	 	if( $subject_score - $arrS[$subject_id]['subject_score'] <0 ){
		                 	 	  	$arrID[] = $res[$key]['id'];
		                 	 	 }
	                 	 	  break;
	                 	    case '<':
	                 	 	  	if( $subject_score - $arrS[$subject_id]['subject_score'] >0 ){
	                 	 	  		$arrID[] = $res[$key]['id'];
	                 	 	  	}
	                 	 	  break;
	                 	 }
	                 	
	                 }elseif(isset($arrS[$subject_id]) && $arrS[$subject_id]['exam_method'] == 2 && $arrS[$subject_id]['exam_status'] != $exam_status ){
	                 	  $arrID[] = $res[$key]['id'];
	                 }
	             }
	         }
	         // $whereS = implode(' AND ', $arrS);
	     }
	  
	     $status_where = array();
	     //考试成绩分析排除的id
	     if( isset($arrID) && !empty($arrID) ){
	     	 $arrID = array_unique($arrID);
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
	     
	    $arrExmsPassStatus   =  $this->arrExmsPassStatus;//科目通过状态
	    $arrPassStatus       =  $this->arrPassStatus;//考试
	    $arrGraduationStatus =  $this->arrGraduationStatus;//毕业状态
	    $arrExamsStatus      =  $this->arrExamsStatus;//报考状态
	    $arrAuditStatus      =  $this->arrAuditStatus;//审核
	    $join  = array(
	        array('users u','u.userId=x.userId','left'),
	        array('school s','s.school_id=x.school_id','left'),
	        array('major_edu m','m.major_id=x.major_id','left'),
	        array('grade g','g.grade_id=x.grade_id','left'),
	    );
	   if($export){
		   	$result = db::name('sj_exams')->alias('x')
									   	->join($join)
									   	->where($status_where)
									   	->where($where)
									   	//->where($son_where)
									   	->field('x.*,u.trueName,u.student_no,u.idcard,
									   			 s.school_id,s.name as school_name,s.school_no,
											     m.major_id,m.name as major_name,m.major_number,
									   			 g.name as grade_name')
									   	->select();
		   	$page['Rows'] = $result;
	   }else{
		   	$page = db::name('sj_exams')->alias('x')
									   	->join($join)
									   	->where($status_where)
									   	->where($where)
									   	//->where($son_where)
									   	->field('x.*,u.trueName,u.student_no,u.idcard,s.school_id,s.name as school_name,
								            	              m.major_id,m.name as major_name,g.name as grade_name')
									   	            	              //->order('id desc')
									   	->paginate(1000)
									   	->toArray();
	   } 
     
	    
	    $page['auditStatus'] = $auditStatus;
	    
	    foreach ($page['Rows'] as $key => $v){
	        
	           //$page['Rows'][$key]['exam_time']  = date('Y-m-d H:i:s',$v['exam_time']);
	           $page['Rows'][$key]['passText']   = $arrPassStatus[$v['status']];
	           $page['Rows'][$key]['statusText'] = $arrGraduationStatus[$v['status']];
	           $page['Rows'][$key]['auditText']  = $arrAuditStatus[$v['auditStatus']];
	           $sj_id = $v['id'];
	           $auditStatus = $v['auditStatus'];
    	        $res  = db::name('sj_exams_subject')->alias('a')
    	                     ->field('a.exam_time,b.subject_no,b.subject_id,b.exam_method,b.name,a.status,a.id,a.exam_status,subject_score')
    	                     ->join('subject_edu b','a.subject_id = b.subject_id','left')
    	                     ->where('a.req_id='.$sj_id)
    	                     ->where('a.id = (select max(id) from mbis_sj_exams_subject as c where  c.req_id = a.req_id AND c.subject_id = a.subject_id )
    	                        ')
    	                     ->group('a.req_id,a.subject_id')
    	                     ->select();
    	        foreach ($res as $k => $t){
    	            $type  = $t['exam_method']==2?2:1;
    	            $value = $t['exam_method']==2?$t['exam_status']:$t['subject_score'];
    	            if($export){
    	            	if($t['status'] == 2){
    	            		$page['Rows'][$key]['sub'][$t['subject_id']] =  $arrExamsStatus[ $t['status'] ];
    	            	}else{
    	            		$page['Rows'][$key]['sub'][$t['subject_id']] =  $t['exam_method']==2?$arrExmsPassStatus[$t['exam_status']]:$t['subject_score'];
    	            	}
    	            	$page['Rows'][$key]['exam_time'][$t['subject_id']] =  $t['exam_time'];
    	            	
    	            	$page['Rows'][$key]['subject_no'][$t['subject_id']] =  $t['subject_no'];
    	            }else{
	    	            //免考
	    	            if( $t['status'] == 2 ){
	    	                $mk =  $arrExamsStatus[ $t['status'] ];
	    	            }else{
	    	            	$mk ='';
	    	            }
	    	            //实践科目 不及格 才显示 重新上传 作品 
	    	            $sczp = ' '; 
	    	            if($auditStatus !=2 &&  $t['exam_method'] == 2 &&  ( in_array($t['exam_status'], array(-1,0,1) ) ) ){
	    	                $sczp  = ' <a href="'.url('admin/userworks/toEdit').'?id='.$t['id'].'">上传作品</a>';
	    	            }
	    	            //查看详情
	    	            if( !$mk ){
	    	                $infoHistory = ' <a href="javascript:;" onClick="getExamsHistory('.$t['id'].')" >查看考试记录</a>';
	    	            }
	    	           
	    	            if( $mk ){
	    	                $page['Rows'][$key]['kk'.$t['subject_id']] = $mk;
	    	            }else{
	        	            $page['Rows'][$key]['kk'.$t['subject_id']] = '<span class="km" id="'.$t['id'].'" data-type="'.$type.'"  data-value="'.$value.'">'
	        	                                           .($t['exam_method']==2?$arrExmsPassStatus[$t['exam_status']]:$t['subject_score'])
	        	                                           .$sczp.$infoHistory.'</span>';
	    	            }
    	            }   
    	            $arrROW[] =  $t['subject_id'];
    	        }
    	        //比较2交集
    	        $arr = array_diff($arrAllNew, $arrROW);
    	        if($arr && !$export){
    	            foreach ($arr as $tt ){
    	            	if($export){
    	            		$page['Rows'][$key]['sub'][$tt] ='';
    	            	}else{
    	            		$page['Rows'][$key][$tt] ='';
    	            	}
    	            }
    	        }
	    }
	    //查找所有的科目
	  /*   if(!empty($res)){
    	    foreach ($res as $key => $v){
    	            $page['subjectList'][] =  array('name'=>$v['name'],'value'=>'kk'.$key);
    	    }
	    } */
	    //查找学员所有的科目
	    if(!empty($arrAll)){
	        foreach ($arrAll as $key => $v){
	            $page['subjectList'][] =  array('name'=>$v['name'].'('.$v['subject_no'].')','value'=>'kk'.$v['subject_id']);
	        }
	    }
	    return $page;
	}
	
	//添加数据
 /*    public function addData(){
        //查找学员数据
        if( request()->isAjax() ){
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
                );
                $field = 'u.trueName,u.student_no,u.idcard,xl.school_name,xl.school_id,
                          xl.major_id,xl.major_name,
                          if( xl.level_id =1,\'高升专\',\'专升本\' ) as level_name,
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
                       $where['me.level_id']    = $level_id;
                       
                       $join = array(
                            array('major_edu_extend me','me.major_id = m.major_id'),
                            array('school s','FIND_IN_SET(s.school_id,m.school_ids)','left'),
                            array('mbis_subject_edu km','FIND_IN_SET(km.subject_id,subject_ids)','left')
                       );
                       $field = 'km.subject_id,km.name,km.exam_method';
                       $res   = db::name('major_edu')->alias('m')
                                            ->field($field)
                                            ->join($join)
                                            ->where($where)
                                            ->select();
                       return $res;
                  }else{
                      return ['statu'=>0,'msg'=>'参数错误！！！'];
                  }     
                
            }
            
            //保存数据
            if( input('action')=='addSubject' ){
                 $userID        = input('userID');
                 $school_id     = input('school_id');
                 $major_id      = input('major_id');
                 $level_id      = input('level_id');
                 $subjectString = input('subjectString');
             
                 if($userID && $school_id && $major_id && $level_id && $subjectString){
                     //查找按个专业下的考试
                      $where['userId']    = $userID;
                      $where['school_id'] = $school_id;
                      $where['major_id']  = $major_id;
                      $where['level_id']  = $level_id;
                      $arr = db::name('sj_exams')->field('id')->where($where)->find();
                      if( $arr['auditStatus'] == '1' ){
                          return ['status'=> 0,'msg'=>'审核通过后数据不能再修改！！！'];
                      }
                      if( $arr['id'] ){
                         $time      = time();
                         $person_id = session('MBIS_STAFF')->staffId;
                         Db::startTrans();
                         $arrSubject = explode('--', $subjectString);
                         
                         try{ 
                           foreach ($arrSubject as $key => $v){
                               //查找科目考试 类型
                               $subject_Info =  explode('-', $v);
                               if(!$subject_Info){
                                  exception('科目信息错误1');
                               }
                               $subject_id     = $subject_Info[0];//科目id
                               $subject_value  = $subject_Info[1];//考试成绩
                               $exam_method = db::name('subject_edu')->where('subject_id ='.$subject_id)->value('exam_method');
                               if(!$exam_method){
                                  exception('科目信息错误2');
                               }
                               //理论
                               if($exam_method==1){
                                   $data = array(
                                       'subject_score'   =>$subject_value,
                                       'updata_time'     =>$time,
                                       'updata_person_id'=>$person_id,
                                   );
                               //实践    
                               }else{
                                   $data = array(
                                       'updata_time'     =>$time,
                                       'updata_person_id'=>$person_id,
                                       'exam_status'     =>$subject_value,
                                   );
                               }
                               $where = array();
                               $where['req_id']     = ['=',$arr['id'] ];
                               $where['subject_id'] = ['=',$subject_id ];
                               $aff_id = db::name('sj_exams_subject')->where($where)->update($data);
                               if($aff_id !== 1){
                                   exception('科目成绩更新失败！！！');
                               }
                           }
                           //更改毕业状态
                           $major_obj = new \application\admin\model\Major();
                           $is_pass   = $major_obj->graduation($userID, $school_id, $major_id, $level_id);
                           if(!$is_pass){
                               exception('学院毕业状态失败！！！');
                           }
                           Db::commit();
                           
                           return ['status'=>1,'msg'=>'科目成绩更新成功'];
                           
                         }catch (\Exception $e){
                             
                           Db::rollback();
                           return ['status'=>0,'msg'=>$e->getMessage()];
                           
                         } 
                      }else{
                          return ['status'=>0,'msg'=>'查不到学员信息！！！'];
                      }
                     
                 }else{
                     return ['status'=>0,'msg'=>'参数非法！！！'];
                 }
                
            }
        
        }
        
        
    } */	
	
	//编辑数据
	public function editData($id,$subject_score,$exam_status){
	    $major_obj = new \application\admin\model\Major();
	    $res  = db::name('sj_exams_subject')->alias('a')
            	    ->join('sj_exams b','b.id = a.req_id','left')
            	    ->where('a.id='.$id)
            	    ->field('b.id,b.userId,b.school_id,b.major_id,b.level_id')
            	    ->find();
	    
	    $is_pass  = $this->audit( $res['id'] );
	    
	    if($is_pass){
	        return '该数据已通过审核,不能修改';
	    }
	    
	    if($subject_score){ 
    	    $data  = array(
    	      'subject_score'   => $subject_score,
    	      'updata_time'   => time(),
    	      'updata_person_id' => session('MBIS_STAFF')->staffId, 
    	    );
	    }else{
	        $data  = array(
	            'exam_status'      => $exam_status,
	            'updata_time'      => time(),
	            'updata_person_id' => session('MBIS_STAFF')->staffId,
	        );
	    }
	    $aff_id = db::name('sj_exams_subject')->where('id','=',$id)->update($data);
	    
	    //更改毕业状态
 	
 	    $userID     = $res['userId'];
 	    $school_id  = $res['school_id'];
 	    $major_id   = $res['major_id'];
 	    $level_id   = $res['level_id'];
	    $is_pass    = $major_obj->graduation($userID, $school_id, $major_id, $level_id); 
	    
	    
	    return $aff_id;
	}
	
	//删除数据
	public function delData($id){
	    $is_pass  = $this->audit( $id );
	    if($is_pass){
	        return '该数据已通过审核,不能修改';
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
	
    //查看	
    function subjectEXamsHistory($exams_subject_id){
        
         //查找查找 报考 基本表信息
          $join  = array(
              array('sj_exams_subject b','b.req_id = a.id','left'),
              array('users u','u.userId = a.userId','left'),
          );
         $where         = array();
         $where['b.id'] = $exams_subject_id;
         $res   = db::name('sj_exams')->alias('a')
                      ->field('a.id,u.trueName,a.exam_no,u.idcard,b.subject_id')
                      ->join($join)
                      ->where($where)
                      ->find();
         //查找所有关联的科目
         
         $join  = array(
             array('subject_edu sub','sub.subject_id = a.subject_id','left'),
         );
         $res1  = db::name('sj_exams_subject')->alias('a')
                                            ->field('sub.exam_method,sub.name as subject_name,sub.subject_no,a.status,a.subject_score,a.exam_status,a.works_data')
                                            ->join($join)
                                            ->where('req_id='.$res['id'].' AND a.subject_id='.$res['subject_id'])
                                            ->select();
         $array = array();
         $array['userInfo'] = array(
             'trueName'=> $res['trueName'],
             'exam_no' => $res['exam_no'],
             'idcard'  => $res['idcard'],
         );
         $arrExamsStatus    = $this->arrExamsStatus;//报考状态
         $arrExmsPassStatus = $this->arrExmsPassStatus;//实践课 考试 状态
         foreach ($res1 as $k => $v ){
             $exam_method = $v['exam_method'];//科目类型
             $status      = $v['status'];//报考状态
             //未报考 
             if($status == 3 ){
                 $subject_score = $arrExamsStatus[ $status ];
             }else{
                 $array['subject'][$k]['exam_method'] = $exam_method;
                 $array['subject'][$k]['exam_method_text'] = $exam_method==1?'理论':'实践';
                 if( $exam_method == 2 ){
                     $works_data  =  $v['works_data'];//学生作品
                     if($works_data){
                         $arrUrl = explode(',', $works_data);
                         $array['subject'][$k]['zp'] = $arrUrl;
                     }
                     $subject_score = $arrExmsPassStatus[ $v['exam_status'] ];
                 }else{
                     $subject_score = $v['subject_score'];
                 }
             }    
             //科目成绩
             $array['subject'][$k]['subject_score'] = $subject_score;
         }
         return $array ;
         
    }
	
	//导入数据
	public function importUsers($file){
	    set_time_limit(0);
	    import('phpexcel.PHPExcel.IOFactory');
	    $reader = \PHPExcel_IOFactory::createReader('Excel2007');
	    $objReader = \PHPExcel_IOFactory::load($file);
	    $objReader->setActiveSheetIndex(0);
	    $sheet  = $objReader->getActiveSheet();
	    $rows   = $sheet->getHighestRow();//行
	    $line   = $sheet->getHighestColumn();//列A B C D
	    $allLine = \PHPExcel_Cell::columnIndexFromString($line);
	
	    $allcolumn = 0;
	    $arrContent = array();
	    for ($r = 1; $r <= $rows; $r++){
	        for($l = 0; $l < $allLine; $l++){
	            $arrContent[$r][]  = (string)$sheet->getCellByColumnAndRow($l,$r);
	        }
	    }
	    
	    return $this->template3($arrContent); 
	 
	}

    function template1($arrContent){
        //模板标题
        $arrTitle = array('年月','院校代码','专业代码','姓名','准考证号','课程代码','成绩');
        $majorObj          = new \application\admin\model\Major;
        $arrExamsStatus    = $this->arrExamsStatus;//报考状态
        $arrExamsStatus    = array_flip($arrExamsStatus);
        $arrExmsPassStatus = $this->arrExmsPassStatus;//成绩结果
        $arrExmsPassStatus = array_flip($arrExmsPassStatus);
        $uid   =  session('MBIS_STAFF')->staffId;
        $time  =  time();
        // 启动事务
        Db::startTrans();
        try {
            foreach($arrContent as $key => $v ){
                if($key==1){
                    foreach ($v as $k => $t){
                        if( $arrTitle[$k] != $t ){
                            exception('模板格式不对，请按约定的格式上传数据');
                        }
                    }
                }else{
                    //查找数据 
                    $arrData = array();
                     $arrData[] =   $v[0];//考试时间
                     $arrData[] =   $v[1];//院校代码
                     $arrData[] =   $v[2];//专业代码
                     $arrData[] =   $v[3];//姓名
                     $arrData[] =   $v[4];//准考证号
                     $arrData[] =   $v[5];//课程代码
                     $arrData[] =   $v[6];//成绩
                     //考试时间
                     $exam_time = strtotime( $arrData[0] );
                    //查找是否有报考信息
                    $join = array(
                        array('sj_exams_subject b','b.req_id= a.id','left'),
                        array('school s','s.school_id = a.school_id','left'),
                        array('major_edu major','major.major_id = a.major_id','left'),
                        array('subject_edu sub','sub.subject_id = b.subject_id','left'),
                    );
                    $where = array();
                    //学校id
                    $where['S.school_no']   = $arrData[1];
                    //专业id
                    $where['major.major_number']   = $arrData[2];
                    //准考证号
                    $where['a.exam_no']        = $arrData[4];
                    //科目id
                    $where['sub.subject_no']   = $arrData[5];
                    $field = 'b.id,sub.exam_method';
                    $res =  db::name('sj_exams')->alias('a')
                                 ->join($join)
                                 ->where($where)
                                 ->field($field)
                                 ->find();
                    if( empty($res) ){
                        exception('找不到【'.$key.'】的报考信息，导致此次导入数据失败！！！');
                    }else{
                        //科目考试结果
                        $cj = $arrData[6];
                        //笔试
                        if( $res['exam_method'] == 1  ){
                            if( is_numeric($cj) ){
                                $data = array();
                                $data['exam_time']        = $exam_time;
                                $data['updata_person_id'] = $uid;
                                $data['updata_time']      = $time;
                                $data['subject_score']    = $cj;
                                $where = array();
                                $where['id'] = $res['id'];
                                $affow_id = db::name('sj_exams_subject')->where($where)->update($data);
                                if(!$affow_id){
                                    exception('第【'.$key.'】的考试成绩导入数据错误，导致此次导入数据失败！！！');
                                }
                            }else{
                               exception('第【'.$key.'】的考试成绩非法参数，导致此次导入数据失败！！！');
                            }
                        //理论   
                        }else{
                            if( is_string($cj) ){
                                if( !isset($arrExmsPassStatus[$cj]) ){
                                    exception('第【'.$key.'】的实践课考试状态不合法，导致此次导入数据失败！！！');
                                }
                                $exam_status = $arrExmsPassStatus[$cj];
                                $data['exam_time']        = $exam_time;
                                $data['updata_person_id'] = $uid;
                                $data['updata_time']      = $time;
                                $data['exam_status']      = $exam_status;
                                $where = array();
                                $where['id'] = $res['id'];
                                $affow_id = db::name('sj_exams_subject')->where($where)->update($data);
                                if(!$affow_id){
                                    exception('第【'.$key.'】的考试成绩导入数据错误，导致此次导入数据失败！！！');
                                }
                            }else{
                                exception('第【'.$key.'】的考试成绩非法参数，导致此次导入数据失败！！！');
                            }
                        }
                    }
                }
            }
            Db::commit();
            return true;
        }catch (\Exception $e){
            Db::rollback();
            return $e->getMessage();
        }
   }

    function template2($arrContent){
        
        //列表的选项
        $usreKey   = 2;//用户身份证
        $zkKey     = 3;//准考证号
        $schoolKey = 4;//学校编号
        $majorKey  = 5;//专业
        $levelKey  = 6;//层级
        //dump($arrContent);
        $majorObj       = new \application\admin\model\Major;
        $arrExamsStatus = $this->arrExamsStatus;//报考状态
        $arrExamsStatus = array_flip($arrExamsStatus);
        $arrExmsPassStatus = $this->arrExmsPassStatus;//成绩结果
        $arrExmsPassStatus = array_flip($arrExmsPassStatus);
         
        $uid  =  session('MBIS_STAFF')->staffId;
        $time =  time();
        if($arrContent){
            foreach($arrContent as $key => $v ){
                if($key==1){
                     
                    //查找学校
                    $school_number = ( string )$v[$schoolKey];#####
                    $res           =  substrString($school_number);
                    $where['school_no'] = ['=',$res];
                    $arrShool  = db::name('school')->field('school_id')->where($where)->find();
                    $school_id = $arrShool['school_id'];
                    if(!$school_id){
                        return '学校编号未找到！！！';
                    }
                     
                    //查找专业
                    $major_number = ( string )$v[$majorKey];#####
                    $res_major    = substrString($major_number);
                    $whereM['major_number'] = ['=',$res_major];
                    $arrMajor  = db::name('major_edu')->field('major_id')->where($whereM)->find();
                    $major_id  = $arrMajor['major_id'];
                    if(!$major_id){
                        return '专业编号未找到';
                    }
                     
                    //查找专业层次
                    $level         = ( string )$v[$levelKey];#####
                    $level         = substrString($level);
                    if($level==false){
                        return '专业层次没有找到';
                    }
                    $arrMajorLevel = $majorObj->arrMajorLevel;
                    $arr_level     = array_flip($arrMajorLevel);
                    if(empty($arr_level[$level])){
                        return '专业层次有误222';
                    }
                    $level_id                = $arr_level[$level];
                    //查找所有的专业层次下的科目
                    $arrAllMajorLevelSubject  = $majorObj->getMajorSubject($school_id, $major_id, $level_id);
                    if(!$arrAllMajorLevelSubject){
                        return '找不到相关科目！！！';
                    }
                    //查找后面的所有科目
                    $arrSub = array();
                    foreach ($v as $kk => $s){
                        if($kk>$levelKey){
                            $r = substrString($s);
                            if($r==false){
                                return '科目编号未填！！！';
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
        
        
        
                    //准考证号
                    $arrZkNo  = array();
                    //科目对应的成绩
                    $i=0;
                    foreach ($v as $kk => $s){
                        if($kk>$levelKey){
                            //查找科目的成绩
                            $s = trim($s);
                            if( count( $arrAllSubject ) + ($levelKey+1) != count($v) ){
                                return '科目数目不对';
                            }
                            //报考的状态
                            $arrUser[ $v[$usreKey] ]['subject'][] = array(
                                'subject_id'       => $arrAllSubject[$i],
                                'subject_score'    => $s,
                                'updata_time'      => $time,
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
         
         
        if($arrUserId){
            $userIds = implode(',', $arrUserId);
            if( count($arrUser) != count($arrUserId) ){
                return '学员数据有重复！！！';
            }
            $where   = array();
            $where['u.idcard']    = ['in',$userIds];
            $where['s.school_id'] = ['=',$school_id];
            $where['s.major_id']  = ['=',$major_id];
            $where['s.level_id']  = ['=',$level_id];
            //查找报考表中的信息
            $join = array(
                array('users u','u.userId = s.userId'),
            );
            $arrUsers = db::name('sj_exams')->alias('s')
            ->join($join)
            ->where($where)
            ->field('s.id,s.auditStatus,u.idcard')
            ->select();
             
            if( !$arrUsers ){
                return '学员信息错误,请更正后提交！！！';
            }
            //$userSubject 用户对的 成绩
             
            // 启动事务
            Db::startTrans();
            try{
                $sj_exams = db::name('sj_exams');
                //所有的学员
                foreach ($arrUsers as $key => $v ){
                    if($v['auditStatus'] == 2){
                        break;
                    }
                    $userSubject = $arrUser[ $v['idcard'] ]['subject'];
                    //学员下面的科目信息
                    foreach ($userSubject as $su){
                        if( !$arrAllMajorLevelSubject[ $su['subject_id'] ] ){
                            exception('专业信息不合法');
                        }else{
                            $exam_method = $arrAllMajorLevelSubject[ $su['subject_id'] ]['exam_method'];//科目考试类型
                        }
        
                        //修改 报考 科目成绩
                        $data1 = array();
                        if( !empty($su['subject_score']) ){
                            //理论
                            if( $exam_method == 1 ){
                                $data1 = array(
                                    'subject_score'    => $su['subject_score'],
                                    'updata_time'      => $time,
                                    'updata_person_id' => $uid
                                );
                                //实践
                            }else{
                                 
                                if( empty($arrExmsPassStatus[$su['subject_score']]) ){
                                    exception('科目实践考试成绩有不合法字符');
                                }
                                $data1 = array(
                                    'exam_status'      => $arrExmsPassStatus[$su['subject_score']],//报考状态
                                    'updata_time'      => $time,
                                    'updata_person_id' => $uid
                                );
                            }
        
                        }
                        $where               = array();
                        $where['req_id']     = ['=',$v['id'] ];
                        $where['subject_id'] = ['=',$su['subject_id'] ];
                        $id1 = db::name('sj_exams_subject')->where($where)->order('id DESC')->update($data1);
                        if(!$id1){
                            exception('考试成绩添加失败');
                        }
        
                    }
        
                }
                 
                Db::commit();
                return true;
            }catch (\Exception $e) {
                Db::rollback();
                return $e->getMessage();
            }
             
        }else{
            return '学员数据不存在';
        }
    }

    function template3($arrContent){
        
        //模板标题
        $arrTitle = array('学员编号','学员姓名','身份证号','报读院校','层次','报读专业','课程编码',
            '课程名称','年级','准考证号','登录密码','科目编号','科目名称','成绩'
        );
        $majorObj          = new \application\admin\model\Major;
        $arrExamsStatus    = $this->arrExamsStatus;//报考状态
        $arrExamsStatus    = array_flip($arrExamsStatus);
        $arrExmsPassStatus = $this->arrExmsPassStatus;//成绩结果
        $arrExmsPassStatus = array_flip($arrExmsPassStatus);
        $uid   =  session('MBIS_STAFF')->staffId;
        $time  =  time();
        
        // 启动事务
        Db::startTrans();
        try {
            $arrUserMain = [];
            $title_count = count($arrTitle);
            foreach($arrContent as $key => $v ){
                if($key==1){
                    foreach ($v as $k => $t){
                        if($k < $title_count && $arrTitle[$k] != $t ){
                            exception('模板格式不对，请按约定的格式上传数据');
                        }
                    }
                }else{
                    //查找数据 
                    $arrData = array();
             /*         $arrData[] =   $v[0];//考试时间
                     $arrData[] =   $v[1];//院校代码
                     $arrData[] =   $v[2];//专业代码
                     $arrData[] =   $v[3];//姓名
                     $arrData[] =   $v[4];//准考证号
                     $arrData[] =   $v[5];//课程代码
                     $arrData[] =   $v[6];//成绩 */
                     //学员
                     $student_no = $v[0];
                     $whereUser = array('student_no'=>$student_no);
                     $userId = db::name('users')->where($whereUser)->value('userId');
                      if(!$userId){
                          exception('第【'.$key.'】列-【学员编号】'.$v[0].'-【学员编号】：'.$v[1].'-的数据错误,因为学员编号找不到学员id，导致此次导入数据失败！！！');
                      }
                     //查找学校
                     $whereSchool = array('name'=>$v[3]);
                     $school_id   = db::name('school')->where($whereSchool)->value('school_id');
                     if(!$school_id){
                         exception('第【'.$key.'】列-【学员编号】'.$v[0].'-【学员名称】：'.$v[1].'-的数据错误,因为找不到学校id，导致此次导入数据失败！！！');
                     }
                     //查找专业层次
                     $level         = ( string )$v[4];#####
                     $arrMajorLevel = $majorObj->arrMajorLevel;
                     $arr_level     = array_flip($arrMajorLevel);
                     if(empty($arr_level[$level])){
                         exception('第【'.$key.'】列-【学员编号】'.$v[0].'-【学员名称】：'.$v[1].'-的数据错误,因为专业层次有误2，导致此次导入数据失败！！！');
                     }
                     $level_id                = $arr_level[$level];
                     //查找专业
                     $whereMajor = array('name'=>$v[5] );
                     $major_id   = db::name('major_edu')->where($whereMajor)->value('major_id');
                     if( !$major_id ){
                         exception('第【'.$key.'】列-【学员编号】'.$v[0].'-【学员名称】：'.$v[1].'-的数据错误,因为专业有误,查找不到系统专业id，导致此次导入数据失败！！！');
                     } 
                     //课程编码
                     $whereCourse = array('course_bn'=>$v[6]);
                     $course_id   = db::name('course')->where($whereCourse)->value('course_id');
                     if( !$course_id ){
                         exception('第【'.$key.'】列-【学员编号】'.$v[0].'-【学员名称】：'.$v[1].'-的数据错误,因为课程编号有误,查找不到系统课程id，导致此次导入数据失败！！！');
                     }
                     //课程名称
                     /* $course_name = $v[6];
                     if(!$course_name){
                         exception('第【'.$key.'】的数据错误,因为没有课程数据，导致此次导入数据失败！！！');
                     } */
                     //年级
                     $whereGrade = array('name'=>$v[8]);
                     $grade_id   = db::name('grade')->where($whereGrade)->value('grade_id');
                     if( !$grade_id ){
                         exception('第【'.$key.'】列-【学员编号】'.$v[0].'-【学员名称】：'.$v[1].'-的数据错误,因为年级有误,查找不到系统年级id，导致此次导入数据失败！！！');
                     } 
                     /* $grade_name = $v[8];
                     if(!$grade_name){
                         exception('第【'.$key.'】的数据错误,因为没有年级数据，导致此次导入数据失败！！！');
                     } */
                     //准考证号
                     $card_number = $v[9];
                     if(!$card_number){
                         exception('第【'.$key.'】列-【学员编号】'.$v[0].'-【学员名称】：'.$v[1].'-的数据错误,因为没有准考证号码，导致此次导入数据失败！！！');
                     }
                     //准考证密码
                     $password = $v[10];
                     if(!$password){
                         exception('第【'.$key.'】列-【学员编号】'.$v[0].'-【学员名称】：'.$v[1].'-的数据错误,因为没有准考证密码，导致此次导入数据失败！！！');
                     }
                     //科目编号
                     $whereSubject = array('subject_no'=>$v[11]);
                     $arr_subject  = db::name('subject_edu')->where($whereSubject)->field('subject_id,name,exam_method')->find();
                     if( !$arr_subject['subject_id'] ){
                         exception('第【'.$key.'】列-【学员编号】'.$v[0].'-【学员名称】：'.$v[1].'-的数据错误,因为科目编号有误,查找不到系统科目id，导致此次导入数据失败！！！');
                     }
                     //科目成绩
                     $subject_score = $v[13];
                     //专业检测
                     $Major  = new \application\admin\model\Major();
                     $arrAll = $Major->getMajorSubject($school_id, $major_id, $level_id);
                     if( !isset($arrAll[ $arr_subject['subject_id'] ]) ){
                         exception('第【'.$key.'】列-【学员编号】'.$v[0].'-【学员名称】：'.$v[1].'-的数据错误,因为该科目与专业，层级，等信息不匹配，导致此次导入数据失败！！！');
                     }
                     $arrUserInfo[] = array(
                         'userId'   =>$userId,
                         'school_id'=>$school_id,
                         'major_id' =>$major_id,
                         'level_id' =>$level_id,
                     );
                    //查找是否有考试成绩
                    $join = array(
                        array('sj_exams_subject b','b.req_id= a.id','left'),
                        array('school s','s.school_id = a.school_id','left'),
                        array('major_edu major','major.major_id = a.major_id','left'),
                        array('subject_edu sub','sub.subject_id = b.subject_id','left'),
                    );
                    $where = array();
                    //学员id
                    $where['a.userId']     = $userId;
                    //学校id
                    $where['a.school_id']  = $school_id;
                    //专业id
                    $where['a.major_id']   = $major_id;
                    //层级
                    $where['a.level_id']   = $level_id;
                    //科目id
                    #$where['sub.subject_id']  = $arr_subject['subject_id'];
                  
                    $field = 'a.id as m_id,b.id,b.subject_id,sub.exam_method';
                    $res =  db::name('sj_exams')->alias('a')
                                 ->join($join)
                                 ->where($where)
                                 ->field($field)
                                 ->select();
                    if( !empty($res) ){
                        foreach ($res as $s => $c){
                             if( $c['subject_id'] == $arr_subject['subject_id'] ){
                                 exception('第【'.$key.'】列-【学员编号】'.$v[0].'-【学员名称】：'.$v[1].'-的科目【'.$arr_subject["name"].'】考试成绩在系统数据中[已经存在]，导致此次导入数据失败！！！');
                             }
                        }
                    }
                
                    if( empty($res) && !isset($arrUserMain[$userId][$school_id][$major_id][$level_id]) ){
                        $data = array(
                            'userId' => $userId,
                            'school_id' => $school_id,
                            'major_id'  => $major_id,
                            'level_id'  => $level_id,
                            'grade_id'  => $grade_id,
                            'course_id' => $course_id,
                            //'grade_name'  =>$grade_name,
                            // 'course_name' => $course_name,
                            'exam_no'          => $card_number,
                            'exam_password'    => $password,
                            'data_type'        => 1,
                            'update_time'      => time(),
                            'update_person_id' => $uid,
                        );
                         $obj  = db::name('sj_exams');
                         $obj->insert($data);
                         $id = $obj->getLastInsID();
                        if(!$id){
                            exception('第【'.$key.'】列-【学员编号】'.$v[0].'-【学员名称】：'.$v[1].'-的考试成绩添加错误，导致此次导入数据失败！！！');
                        }
                       $arrUserMain[$userId][$school_id][$major_id][$level_id] = true;
                    }
                    
                    if( !empty($res) ){
                        $id = $res[0]['m_id'];
                    }
                    
                    //2017-7--3 新增 只修改准考证号码 和密码 数据
                    if(!$subject_score){
                         $obj  = db::name('sj_exams');
                         $data = array(
                             'exam_no'          => $card_number,
                             'exam_password'    => $password,
                             'update_time'      => time(),
                             'update_person_id' => $uid,
                         );
                         $affow_id = db::name('sj_exams_subject')->where($where)->update($data);
                         if(!$affow_id){
                             exception('第【'.$key.'】列-【学员编号】'.$v[0].'-【学员名称】：'.$v[1].'-修改准考证号或者密码错误，导致此次导入数据失败！！！');
                         }
                    }else{
                        //笔试
                        if( $arr_subject['exam_method'] == 1  ){
             
                            if( is_numeric($subject_score) || $subject_score == '免考' || !$subject_score){

                                $data = array();
                                $data['exam_time']        = '';
                                $data['updata_person_id'] = $uid;
                                $data['updata_time']      = $time;
                                
                                if($subject_score == '免考'){
                                    $data['status']    = 3;
                                }else{
                                    $data['subject_score']    = $subject_score;
                                }
                                /* if( $res['id'] ){
                                    $where = array();
                                    $where['id'] = $res['id'];
                                    $affow_id = db::name('sj_exams_subject')->where($where)->update($data);
                                }else{ */
                                    $data['req_id']     = $id;
                                    $data['subject_id'] = $arr_subject['subject_id'];
                                    $obj =db::name('sj_exams_subject');
                                    $obj->insert($data);
                                    $affow_id = $obj->getLastInsID();
                                //}
                                if(!$affow_id){
                                    exception('第【'.$key.'】列-【学员编号】'.$v[0].'-【学员名称】：'.$v[1].'-的考试成绩导入数据错误，导致此次导入数据失败！！！');
                                }
                            }else{
                               exception('第【'.$key.'】列-【学员编号】'.$v[0].'-【学员名称】：'.$v[1].'-的理论考试成绩非法参数，导致此次导入数据失败！！！');
                            }
                        //实践 
                        }else{

                            if( is_string($subject_score) || $subject_score == '免考' || !$subject_score ){
                                if( !isset($arrExmsPassStatus[$subject_score]) && $subject_score != '免考' ){
                                    exception('第【'.$key.'】列-【学员编号】'.$v[0].'-【学员名称】：'.$v[1].'-的实践课考试状态不合法，导致此次导入数据失败！！！');
                                }
                                $data  = array();
                                $data['exam_time']        = '';
                                $data['updata_person_id'] = $uid;
                                $data['updata_time']      = $time;
                                if($subject_score == '免考'){
                                    $data['status']    = 3;
                                }else{
                                    $exam_status = $arrExmsPassStatus[$subject_score];
                                    $data['exam_status']      = $exam_status;
                                }
                                /* if( $res['id'] ){
                                    $where = array();
                                    $where['id'] = $res['id'];
                                    $affow_id = db::name('sj_exams_subject')->where($where)->update($data);
                                }else{ */
                                    $data['req_id']    = $id;
                                    $data['subject_id'] = $arr_subject['subject_id'];
                                    $obj =db::name('sj_exams_subject');
                                    $obj->insert($data);
                                    $affow_id = $obj->getLastInsID();
                                //}
                                if(!$affow_id){
                                    exception('第【'.$key.'】列-【学员编号】'.$v[0].'-【学员名称】：'.$v[1].'-的考试成绩导入数据错误，导致此次导入数据失败！！！');
                                }
                            }else{
                                exception('第【'.$key.'】列-【学员编号】'.$v[0].'-【学员名称】：'.$v[1].'-的考试成绩非法参数，导致此次导入数据失败！！！');
                            }
                        }
                    }  
                }
            }
            foreach ($arrUserInfo as $v){
                $userID     = $v['userId'];
                $school_id  = $v['school_id'];
                $major_id   = $v['major_id'];
                $level_id   = $v['level_id'];
                $major_obj  = new \application\admin\model\Major();
                $is_pass    = $major_obj->graduation($userID, $school_id, $major_id, $level_id);
                if(!$is_pass){
                    //getLastSql();
                    exception('学员id为'.$userID.'的考试成绩毕业情况修改失败！！！');
                }
            }
            Db::commit();
            return true;
        }catch (\Exception $e){
            header("Content-type: text/html; charset=utf-8");
            return $e->getMessage();
        }
    }

}
