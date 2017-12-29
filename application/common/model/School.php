<?php
namespace application\common\model;
/**
 * 学校业务处理
 */
use think\Db;
class School extends Base{
    //首页学院列表
    public function getApiIndexList(){
        $params['is_nav'] = 1;
        $params['field'] = 'school_id,jump_type,name,cover_img';
        $rs = $this->get_lists($params);
        return MBISReturn("",1,$rs);
	}
    //学院列表筛选项
    public function getApiSelItems(){
        $params = input('post.');
        if(!isset($params['jump_type'])) $params['jump_type']=2;
        if($params['jump_type'] == 1)//学历
        {
            $rs = ITSGetSelData('major');
            unset($rs[2],$rs[3]);
        }
        else if($params['jump_type'] == 2)//非学历
        {
             if(!isset($params['school_id'])) return MBISReturn("缺少参数[school_id]",-1);
             $rs['school_id']=$params['school_id'];
             $params['is_show'] = 1;
             $params['field'] = 'major_id,name';
             $rs['selItem'][0]['key'] = 'major_id';
             $rs['selItem'][0]['name'] = '专业列表';
             $rs_major = model('major')->get_lists($params);
             array_push($rs_major,['major_id'=>-1,'name'=>'全部']);
             $rs['selItem'][0]['majorList'] = $rs_major;
             $rs['selItem'][1]['key'] = 'show_type_id';
             $rs['selItem'][1]['name'] = '展示类型';
             $rs['selItem'][1]['showTypeList'] = [
                ['show_type_id'=>1,'name'=>'课程'],
                ['show_type_id'=>2,'name'=>'科目'],
                ['show_type_id'=>3,'name'=>'线上'],
             ];
             if(isset($params['jump_type'])) $rs['jump_type']=$params['jump_type'];
        }
        
        return MBISReturn("",1,$rs);
    }
    //学院列表
    public function getApiList(){
        $params = input('post.');
        if(!isset($params['jump_type'])) $params['jump_type']=2;
        if($params['jump_type'] == 1)//学历
        {
            $params['is_nav'] = 0;
            $params['field'] = 'school_id,jump_type,name,cover_img';
            if(isset($params['level_type']) || isset($params['exam_type']))
            {
                $school_ids = $this->get_school_id($params);
                if($school_ids)
                {
                    $params['school_id'] = ['in',$school_ids];
                }
                else
                {
                   $params['school_id'] = -1;
                }
            }
            $rs = $this->get_lists($params);
        }
        else if($params['jump_type'] == 2)//非学历
        {
            if(!isset($params['school_id'])) return MBISReturn("缺少参数[school_id]",-1);
            if(!isset($params['major_id'])) $params['major_id']=-1;
            if(!isset($params['show_type_id'])) $params['show_type_id']=1;
            $rs['show_type_id'] = $params['show_type_id'];
            //全部
            if($params['major_id']==-1) unset($params['major_id']);
            if($params['show_type_id'] == 1)//课程
            {
                $params['field'] = 'course_id,major_id,name,cover_img,offers_price,market_price,course_bn';
                $rs_course = model('course')->get_lists($params);
                foreach($rs_course['lists'] as $k=>$v)
                {
                    $course_price = model('course')->get_course_price_origi(0,$v);
                    $rs_course['lists'][$k]['sale_price'] =(int)$course_price['price'];
                    $rs_course['lists'][$k]['market_price'] =$v['market_price']>0?(int)$v['market_price']:(int)$course_price['market_price'];
                }
                #dump($rs_course);
                $rs['courseList'] = $rs_course;
            }
            elseif($params['show_type_id'] == 2 || $params['show_type_id'] == 3)//科目
            {
                $params['is_shelves'] = 1;
                $params['show_type_id']==2 && $params['teaching_type']=1;
                $params['show_type_id']==3 && $params['teaching_type']=2;
                $params['field'] = 'subject_id,school_id,major_id,name,cover_img,sale_price,market_price,subject_no';
                $rs_subject = model('subject')->get_lists($params);
                foreach($rs_subject['lists'] as $k=>$v)
                {
                    $rs_subject['lists'][$k]['course_id'] =$v['subject_id'];
                    $rs_subject['lists'][$k]['sale_price'] =(int)$v['sale_price'];
                    $rs_subject['lists'][$k]['market_price'] =(int)$v['market_price'];
                    $rs_subject['lists'][$k]['channelLists'] = get_channel_lists($v);
                    unset($rs_subject['lists'][$k]['subject_id']); 
                }
                $rs['courseList'] = $rs_subject;
            }
            
        }
        return MBISReturn("",1,$rs);
	}
    //学院详情 && 专业列表
    public function getApiSchoolMajor()
    {
        $params = input('post.');
        if(!isset($params['school_id'])) return MBISReturn("缺少参数[school_id]",-1);
        $rs['schoolDetail'] = $this->get_info(['school_id'=>$params['school_id'],'field'=>'name,details']);
        $rs['majorList'] = $this->getMajorList(['school_id'=>$params['school_id']]);
        return MBISReturn("",1,$rs);  
    }
    //年级列表
    public function getApiGradeList()
    {
        $params = input('post.');
        if(!isset($params['major_id'])) return MBISReturn("缺少参数[major_id]",-1);
        $params['field'] = 'grade_id,major_id,name';
        $rs = model('Grade')->get_lists($params);
        if(empty($rs)) return MBISReturn("",1,$rs);
        $grade_ids = [];
        foreach($rs as $v):
            $grade_ids[] = $v['grade_id'];
        endforeach;
        $params_course['grade_id'] = ['in',$grade_ids];
        $params_course['field'] = 'grade_id,course_id';
        $rs_course = model('course')->get_lists($params_course);
        empty($rs_course) && $rs=[];
        if(!empty($rs_course)):
            $has_grade_ids = [];
            foreach($rs_course as $v):
                $has_grade_ids[] = $v['grade_id'];
            endforeach;
            $new_rs = [];
            foreach($rs as $k=>$v):
               if(in_array($v['grade_id'],$has_grade_ids))
                 $new_rs[] = $v;
            endforeach;
        endif;
        return MBISReturn("",1,$rs);
    }
    //年级详情
    public function getApiGradeDetail()
    {
        /*$params = input('post.');
        if(!isset($params['major_id'])) return MBISReturn("缺少参数[major_id]",-1);
        if(!isset($params['grade_id'])) return MBISReturn("缺少参数[grade_id]",-1);
        $rs = model('major')->get_info(['major_id'=>$params['major_id'],'field'=>'school_id,exam_type,level_type']);
        $rs['school_name'] = $this->get_info(['school_id'=>$rs['school_id'],'field'=>'name']);
        $grade = model('Grade')->get_info(['major_id'=>$params['major_id'],'grade_id'=>$params['grade_id']]);
        $grade['stu_fee'] = (int)$grade['stu_fee'];
        $grade['offers'] = (int)$grade['offers'];
        $grade['market_price'] = (int)$grade['market_price'];
        $grade['channelLists'] = get_channel_lists($grade);
        $rs['gradeDetail'] = $grade;
        return MBISReturn("",1,$rs);*/
        $params = input('post.');
        if(!isset($params['course_id'])) return MBISReturn("缺少参数[course_id]",-1);
        $rs = model('course')->get_info(['course_id'=>$params['course_id']]);
        $rs['school_name'] = $this->get_info(['school_id'=>$rs['school_id'],'field'=>'name']);
        $rs['major_name'] = model('major')->get_info(['major_id'=>$rs['major_id'],'field'=>'name']);
        $rs['level_type_format'] = ITSSelItemName('major','level_type',$rs['level_type']);
        //$grade = model('Grade')->get_info(['major_id'=>$params['major_id'],'grade_id'=>$params['grade_id']]);
        //$grade['stu_fee'] = (int)$grade['stu_fee'];
        //$grade['offers'] = (int)$grade['offers'];
        //$grade['market_price'] = (int)$grade['market_price'];
        //$grade['channelLists'] = get_channel_lists($grade);
        //$rs['gradeDetail'] = $grade;
        return MBISReturn("",1,$rs);
    }
    //科目详情
    public function getApiSubjectDetail()
    {
        $params = input('post.');
        if(!isset($params['subject_id'])) return MBISReturn("缺少参数[subject_id]",-1);
        $tmp_rs = [];
        $params['is_shelves'] = 1;
        $rs = model('subject')->get_info($params);
        if($rs)
        {
            $school_info = $this->get_info(['school_id'=>$rs['school_id']]);
            $major_info = model('major')->get_info(['major_id'=>$rs['major_id']]);
            $tmp_rs[] = ['item_name'=>'招生院校','item_value'=>$school_info['name']];
            $tmp_rs[] = ['item_name'=>'专业','item_value'=>$major_info['name']];
            $tmp_rs[] = ['item_name'=>'层次','item_value'=>$major_info['level_type']];
            $tmp_rs[] = ['item_name'=>'考试类型','item_value'=>$major_info['exam_type']];
            $tmp_rs[] = ['item_name'=>'科目名称','item_value'=>$rs['name']];
            $subject_prop = model('SubjectTypePropValue')->get_subject_prop($rs['subject_type_id'],$rs['subject_id'],'name');
            if($subject_prop)
            {
                foreach($subject_prop as $k=>$v)
                {
                    $tmp_rs[] = ['item_name'=>$k,'item_value'=>$v];
                }
            }
        }
        return MBISReturn("",1,$tmp_rs);
    }
    //技能 >> 课程科目详情
    public function getApiCourseDetail()
    {
        $params = input('post.');
        if(!isset($params['jump_type'])) return MBISReturn("缺少参数[jump_type]",-1);
        if(!isset($params['show_type_id'])) return MBISReturn("缺少参数[show_type_id]",-1);
        if(!isset($params['course_id'])) return MBISReturn("缺少参数[course_id]",-1);
        
        if($params['show_type_id']==1)//课程详情
        {
            $params['field'] = 'type_id,course_id,school_id,major_id,name,cover_img,details,teaching_type,offers_price,market_price,course_bn,des';
            $rs = model('course')->get_info($params);
            //课程价格处理
            $price_course = model('course')->get_course_price(0,$rs);
        }
        elseif($params['show_type_id']==2 || $params['show_type_id']==3)//课目详情
        {
            $params['subject_id'] = $params['course_id'];
            $params['field'] = 'subject_id,school_id,major_id,name,cover_img,details,sale_price,offer_price,cost,market_price,teaching_type,teacher_id,subject_no';
            $rs = model('subject')->get_info($params);
            $rs['sale_price'] = (int)$rs['sale_price'];
            $rs['offer_price'] = (int)$rs['offer_price'];
            $rs['cost'] = (int)$rs['cost'];
            $rs['market_price'] = (int)$rs['market_price'];
            //科目价格处理
            $price_course = model('subject')->get_subject_price(0,$rs);
        }
        $tmp_rs['jump_type'] = $params['jump_type'];
        if(!empty($rs))
        {
            $rs['price'] = (int)$price_course['price'];
            $rs['market_price'] = (int)$price_course['market_price'];
            if(isset($rs['offers_price']))
            {
                $rs['offers_price'] = (int)$rs['offers_price'];  
            }
            $tmp_rs['courseInfo'] = $rs;
        }
        if($tmp_rs && $params['show_type_id']==1)//课程详情 >> 科目列表
        {
            $subjectList = model('subject')->get_subject_props(['course_id'=>$rs['course_id']]);
            foreach($subjectList as $k=>$v)
            {
                $subjectList[$k]['sale_price'] = (int)$v['sale_price'];
            }
            #dump($subjectList);exit;
            $tmp_rs['subjectList'] = $subjectList;
        }
        return MBISReturn("",1,$tmp_rs);
    }
    //技能 >> 课程价格计算
    public function getApiCoursePrice()
    {
        $params = input('post.');
        if(!isset($params['jump_type'])) return MBISReturn("缺少参数[jump_type]",-1);
        #course_id
        if(!isset($params['course_id'])) return MBISReturn("缺少参数[course_id]",-1);
        #subject_ids = 1,2,3
        if(!isset($params['subject_ids'])) return MBISReturn("缺少参数[subject_ids]",-1);
        $rs = model('course')->get_course_price_by_sids($params['course_id'],explode(',',$params['subject_ids']));
        return MBISReturn("",1,$rs);
    }
    /** 类自身方法 **/
    public function get_lists($params=[])
    {
        $where = [];
        $field = '';
        if(isset($params['field']))
        {
            $field = $params['field'];
        }
        $limit = '';
        if(isset($params['limit']))
        {
            $limit = $params['limit'];
        }
        if(isset($params['is_nav']))
        {
            $where['is_nav'] = $params['is_nav'];   
        }
        if(isset($params['school_id']))
        {
            $where['school_id'] = $params['school_id'];   
        }
        $rs = $this->where($where)->field($field)->limit($limit)->select();
        //dump($rs);exit;
        /**foreach($rs as $k=>$v)
        {
            //dump($v['cover_img']);
            //$v = $v->data;
            if(isset($v['cover_img']))
            {
                $rs[$k]['cover_img'] = ITSPicUrl($v['cover_img']);
            }
            ///if(isset($v['cover_img']))
            {
                $rs[$k]['cover_img'] = !empty($rs[$k]['cover_img'])?ITSPicUrl($v['cover_img']):'';
            }
        }*/
        return $rs;
    }
    public function get_name($id=0){
        return $this->where('school_id',$id)->value('name');
	}
    //学院详情
    public function get_info($params)
    {
        $rs = [];
        $school_id = $params['school_id'];
        $where['school_id'] = $school_id;
        $field = '';
        if(isset($params['field']))
        {
            $field = $params['field'];
        }
        $rs = $this->where($where)->field($field)->find();
        $rs = $rs->data;
        if(isset($params['field'])&&strpos($params['field'],',')===FALSE) return $rs[$field];
        if(isset($rs['cover_img']))
        {
            $rs['cover_img'] = ITSPicUrl($rs['cover_img']);
        }
        if(isset($rs['details']))
        {
            $rs['details'] = htmlspecialchars_decode($rs['details']);
        }
        return $rs;  
    }
    //专业学校id
    public function get_school_id($params=[])
    {
        //层次：0为未知、1为高升本、2为高升专、3为专升本、4为专本套读
        if(isset($params['level_type']))
        {
            $where['level_type'] = $params['level_type'];
        }
        //考试类型：0为未知、1为自考、2为成考、3为网教
        if(isset($params['exam_type']))
        {
            $where['exam_type'] = $params['exam_type'];
        }
        $rs = Db::name('major')->where($where)->column('school_id');
        return array_unique($rs);
    }
    //专业列表
    public function getMajorList($params)
    {
        /*$params['field'] = 'major_id,name,cover_img,exam_type,level_type';
        //$params['limit'] = '0,3';
        $rs = model('major')->get_lists($params);
        $tmp_rs = [];
        foreach($rs as $k=>$v)
        {
            $key = $v['exam_type'].'_'.$v['level_type'];
            $tmp_rs[$key]['exam_type'] = ITSSelItemName('major','exam_type',$v['exam_type']);
            $tmp_rs[$key]['level_type'] = ITSSelItemName('major','level_type',$v['level_type']);
            $tmp_rs[$key]['lists'][] = $v;
        }
        rsort($tmp_rs);*/
        $rs = Db::name('major_edu')->where(["school_ids"=>['like',"%{$params['school_id']},%"]])->field("*,CONCAT(school_ids,',') AS school_ids")->select();
        
        foreach($rs as $o_k=>$o_v)
        {
            $o_v['school_id'] = $params['school_id'];
            $o_v['cover_img'] = ITSPicUrl($o_v['cover_img']);
            $rs_major = Db::name('major_edu_extend')->where(['major_id'=>$o_v['major_id']])->select();
            //dump(['school_id'=>$params['school_id'],'major_id'=>$o_v['major_id'],'level_type'=>$v['level_id']]);
            foreach($rs_major as $v):
            
                $course_info = Db::name('course')->where(['school_id'=>$params['school_id'],'major_id'=>$o_v['major_id'],'level_type'=>$v['level_id']])->find();
                if( !empty($course_info['course_id']) )
                {
                    $o_v['course_id'] = $course_info['course_id'];
                    $key = $o_v['exam_type'].'_'.$v['level_id'];
                    $tmp_rs[$key]['exam_type'] = ITSSelItemName('major','exam_type',$o_v['exam_type']);
                    $tmp_rs[$key]['level_type'] = ITSSelItemName('major','level_type',$v['level_id']);
                    $tmp_rs[$key]['lists'][] = array_merge($o_v,$v);
                }
            endforeach;
        }
        if(empty($tmp_rs)) return [];
        rsort($tmp_rs);
        return $tmp_rs;
    }
    /* 获取学校数据 */
    public function getInfoData($school_id=0){
        $return = $this->get(['school_id'=>$school_id]);
        return $return;    
    }
}
