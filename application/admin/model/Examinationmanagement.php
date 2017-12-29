<?php
namespace application\admin\model;
use think\Db;
class Examinationmanagement extends Base{
    public $arrExamsStatus = array(
        1=>'已报考',
        2=>'未报考',
        3=>'免考',
    );
    
    //导入数据
    public function importUsers(){
        set_time_limit(0);
    
        import('phpexcel.PHPExcel.IOFactory');
        $reader = \PHPExcel_IOFactory::createReader('Excel2007');
        //$filename = "/xlsx/{$num}.xlsx";
        $file = 'D:/zp/v1.0/public/upload/exams/baokao/achievement-2017-4-22.xlsx';
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
        if($arrContent){
            foreach($arrContent as $key => $v ){
                if($key==1){
                    //查找后面的所有科目
                    $arrSub = array();
                    foreach ($v as $kk => $s){
                        if($kk>6){
                            $r = substrString($s);
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
                }else{
                    //学员
                    if(empty($v[$usreKey])){
                        return '学员身份证不能为空！！！';
                    }
                    $arrUser[ $v[$usreKey] ]['userId'] = $v[$usreKey];
                    $arrUserId[]  = $v[$usreKey];
                    //查找学校
                    $school_number = ( string )$v[$schoolKey];#####
                    $res           =  substrString($school_number);
                    $where['school_no'] = ['=',$res];
                    $arrShool  = db::name('school')->field('school_id')->where($where)->find();
                    $school_id = $arrShool['school_id'];
                    if(!$school_id){
                        return '学校编号未找到！！！';
                    }
    
                    $arrUser[$v[$usreKey]]['school_id'] = $school_id;
    
                    //查找专业
                    $major_number = ( string )$v[$majorKey];#####
                    $res_major    = substrString($major_number);
                    $whereM['major_number'] = ['=',$res_major];
                    $arrMajor  = db::name('major')->field('major_id')->where($whereM)->find();
                    $major_id  = $arrMajor['major_id'];
                    if(!$major_id){
                        return '专业编号未找到';
                    }
                    $arrUser[ $v[$usreKey] ]['major_id'] = $major_id;
    
                    //查找专业层次
                    $level         = ( string )$v[$levelKey];#####
                    if($level==false){
                        return '专业层次没有找到';
                    }
                    $arrMajorLevel = $majorObj->arrMajorLevel;
                    $arr_level     = array_flip($arrMajorLevel);
                    if(empty($arr_level[$level])){
                        return '专业层次没有找到';
                    }
                    $level_id                = $arr_level[$level];
                    $arrUser[ $v[$usreKey] ]['level_id'] = $level_id;
    
    
                    //准考证号
                    $arrZkNo  = array();
                    //科目对应的成绩
                    foreach ($v as $kk => $s){
                        //准考证号
                        if($kk == $zkKey ){
                            if(!$s){
                                return '准考证号不全';
                            }
                            $arrUser[ $v[$usreKey] ]['exam_no'] = $s;
                        }
                        $i = 0;
                        if($kk>6){
                            $s = trim($s);
                            if( empty($arrExamsStatus[$s]) ){
                                return '报考状态错误';
                            }
                            //
                            if( count( $arrAllSubject ) + 7 != count($v) ){
                                return '科目数目不对';
                            }
                            //报考的状态
                            $arrUser[ $v[$usreKey] ]['subject'][] = array(
                                'subject_id' => $arrAllSubject[$i],
                                'status'     => $arrExamsStatus[$s]
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
            $where   = array();
            $where['idcard'] = ['in',$userIds];
            //查找用户
            $arrUsers =db::name('users')->alias('s')
            ->field('s.userId,s.idcard')
            ->where($where)
            ->select();
    
            //$userSubject 用户对的 成绩
            Db::startTrans();
            $is_true = true;
            foreach ($arrUsers as $key => $v ){
                $userSubject = $arrUser[ $v['idcard'] ]['subject'];
                $data = array(
                    'userId'           => $v['userId'],
                    'examination_type' => 1,
                    'school_id'        => $arrUser[$v['idcard']]['school_id'],
                    'major_id'         => $arrUser[$v['idcard']]['major_id'],
                    'level_id'         => $arrUser[$v['idcard']]['level_id'],
                    'exam_no'          => $arrUser[$v['idcard']]['exam_no'],//准考证号
                    'data_type'        => '1',  //数据类型：0=正常录入 1=批量导入
                );
    
                $id  = $this->save($data);
    
                if($id){
                    foreach ($userSubject as $su){
                        $data[] = array(
                            'subject_id'       => $su['subject_id'],
                            'status'           => $su['status'],//报考状态
                        );
                    };
                    $id1 = db::name('sj_exams_subject')->insertAll($data);
                    if(!$id1){
                        $is_true = false;
                    }
                }else{
                    $is_true = false;
                }
    
            }
    
            if($is_true){
                Db::commit();
                return '上次数据成功';
            }else{
                Db::rollback();
                return '上次数据失败';
    
            }
        }else{
            return '学员数据不存在';
        }
         
    
    }
    /**
     * 分页
     */
    public function pageQuery(){
        $arrExamsStatus = $this->arrExamsStatus;
        $where = array();
        $school_id = input('school_id');
        $major_id  = input('major_id');
        $level_id  = input('level_id');
         
         
        /*  if($school_id){
         $where['x.school_id'] = $school_id;
         }
         if($major_id){
         $where['x.major_id'] = $major_id;
         }
         if($level_id){
         $where['x.level_id'] = $level_id;
        } */
         
        if( !($school_id && $major_id  && $level_id) ){
            return ['status=>0','msg'=>'学校，专业，层级为必填！！！'];
        }
         
        $join  = array(
            array('users u','u.userId=x.userId','left'),
            array('school s','s.school_id=x.school_id','left'),
            array('major m','m.major_id=x.major_id','left'),
            //array('subject km','km.subject_id=x.subject_id','left'),
        );
        $page = $this->alias('x')
        ->join($join)
        ->where($where)
        ->field('x.*,GROUP_CONCAT(x.status) as statusS,GROUP_CONCAT(x.subject_id) as subject_ids,trueName,u.student_no,u.idcard,m.major_id,m.name as major_id,
					          s.school_id,s.name as school_name')
    		             ->group('x.school_id,x.major_id,x.level_id')
    		             ->paginate(input('post.pagesize/d'))
    		             ->toArray();
        //查找所有的科目
    
    
        //查找专业
        foreach ($page['Rows'] as $key => $v){
            $statusS     = $v['statusS'];
            $page['Rows'][$key]['k1'] = $arrExamsStatus[];
            $page['Rows'][$key]['k2'] ='不通过';
        }
        $page['subjectList'] = array( array('name'=>'计算机','value'=>'k1'),
            array('name'=>'UI','value'=>'k2'),
        );
        return $page;
    }
    
	
}
