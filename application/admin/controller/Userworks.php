<?php
namespace application\admin\controller;
use application\admin\model\StudentWorks as M;
/**
 * 会员等级控制器
 */
class Userworks extends Base{
	
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
     * 跳去编辑页面
     */
    public function toEdit(){
        $id = input('get.id');
        $rs = \think\Db::name('sj_exams_subject')->where('id',$id)->find();
        !empty($rs['works_data']) && $rs['works_data'] = explode(',',$rs['works_data']);
        $rs_exams = \think\Db::name('sj_exams')->where('id',$rs['req_id'])->find();
        $rs_users = \think\Db::name('users')->where('userId',$rs_exams['userId'])->find();
        $rs_subjects = \think\Db::name('subject_edu')->where('subject_id',$rs['subject_id'])->find();
        $assign = ['data'=>$rs,
                   'exams'=>$rs_exams,
                   'users'=>$rs_users,
                   'subjects'=>$rs_subjects,
                   ];
        return $this->fetch("edit",$assign);
    }
    /*
    * 获取数据
    */
    public function get(){
        $m = new M();
        return $m->getById((int)Input("work_id"));
    }
    /**
     * 新增
     */
    public function add(){
        $m = new M();
        return $m->add();
    }
    /**
    * 修改
    */
    public function edit(){
        $m = new M();
        return $m->edit();
    }
    /**
     * 删除
     */
    public function del(){
        $m = new M();
        return $m->del();
    }
    
    //选择下拉数据
    public function seldata()
    {
        //323
        $tmp_major_edu = [];
        $tmp_major_edu_extend = [];
        $lists = \think\Db::name('major')->where('type_id',1)->select();
        //学校处理
        $tmp_school_edu = [];
        $tmp_major_name_id = [];
        foreach($lists as $v)
        {
           $tmp_school_edu[$v['name']][] = $v['school_id'];
           if(!isset($tmp_major_name_id[$v['name']]))
                $tmp_major_name_id[$v['name']] = $v['major_id'];
        }//end
        //dump($tmp_major_name_id);exit;
        foreach($lists as $v)
        {
           //mbis_major_edu 学历专业基础表(new)
           if(!isset($tmp_major_edu[ $v['name'] ]))
           {
               $tmp_major_edu[ $v['name'] ] = [
                   'major_id'=>$tmp_major_name_id[$v['name']],
                   'name'=>$v['name'],
                   'major_number'=>$v['major_number'],
                   'cover_img'=>$v['cover_img'],
                   'des'=>$v['des'],
                   'detail'=>$v['details'],
                   'exam_type'=>$v['exam_type'],
                   'createTime'=>$v['createtime'],
                   'is_show'=>$v['is_show'],
                   'school_ids'=>implode(',',array_unique($tmp_school_edu[$v['name']])) 
               ];
           }
           //mbis_major_edu_extend < mbis_subject
           $rs_subject = \think\Db::name('subject')->where(['subject_type_id'=>1,'major_id'=>$v['major_id']] )->select();
           $tmp_subject = [];
           foreach($rs_subject as $subject)
           {
               //自考 有科目
               $v['exam_type']==1 && $tmp_subject[] = $subject['subject_id'];
           }
           !empty($tmp_subject) && in_array($v['level_type'],[2,3]) && $tmp_major_edu_extend[ $v['major_id'].'_'.$v['level_type'] ] = [
               'major_id'=>$tmp_major_name_id[$v['name']],
               'level_id'=>$v['level_type'],
               'graduate_time'=>$v['graduate_type'],
               'subject_ids'=>implode(',',array_unique($tmp_subject))
           ];
        }
        //sort($tmp_major_edu);
        //\think\Db::name('major_edu')->insertAll($tmp_major_edu);
        //\think\Db::name('major_edu_extend')->insertAll($tmp_major_edu_extend);
        dump($tmp_major_edu_extend);exit;
        
        $get = input('get.');
        $mdl = $get['mdl'];
        $id = $get['id'];
        $name = $get['name'];
        $field = "{$id} as id,{$name} as name";
        $where = !empty($get['filter'])?$get['filter']:[];
        //支持 & 连接参数
        if(!empty($where) && strpos($where,'&')!==FALSE)
        {
            $tmp_where = [];
            $arr_where = explode('&amp;',$where);
            foreach($arr_where as $v)
            {
                $arr_where2 = explode('=',$v);
                !empty($arr_where2[0]) && $tmp_where[$arr_where2[0]] =  $arr_where2[1];
            }
            !empty($tmp_where) && $where = $tmp_where;
        }
        $datas = \think\Db::name($mdl)->field($field)->where($where)->select();
        if($mdl=='course_subject'){
            foreach($datas as &$v)
            {
                $v['name'] = \think\Db::name('subject')->where('subject_id',$v['name'])->value('name');  
            }
        }
        $assign = ['datas'=>$datas,'getdata'=>$get];
        return $this->fetch("seldata",$assign);
    }
    
    public function upload()
    {
        $post = input('post.');
        $works_data = [
            'userId' => $post['userId'],
            'major_id' => $post['major_id'],
            'subject_id' => $post['subject_id'],
            'isFinish' => @$post['isFinish'],
            'payFee' => @$post['payFee'],
            'lastmodify' => time(),
        ];
        $rs_works = \think\Db::name('student_works')->field('id')->where(['userId'=>$post['userId'],'major_id' => $post['major_id'],'subject_id' => $post['subject_id']])->find();
        if( !empty($rs_works) ){
            \think\Db::name('student_works')->where('id',$rs_works['id'])->update($works_data);
            $last_insert_id = $rs_works['id'];
        }else{
            $works_data['createtime'] = time();
            $last_insert_id = \think\Db::name('student_works')->insert($works_data,false,true);
        }
        if( !empty($post['work_url']))
        {
        $works_upload_data = [
            'userId' => $post['userId'],
            'major_id' => $post['major_id'],
            'subject_id' => $post['subject_id'],
            'work_id' => $last_insert_id,
            'work_url' => $post['work_url'],
            'createtime' => time(),
            'lastmodify' => time(),
        ];
        \think\Db::name('student_works_upload')->insert($works_upload_data,false,true);
        }
        return MBISReturn('保存成功',1,$last_insert_id);
    }
    
    public function getupload()
    {
        $get = input('get.');
        $filter_work_upload['userId'] = $get['userId'];
        $filter_work_upload['major_id'] = $get['major_id'];
        //!empty($get['subject_id']) && $filter_work_upload['subject_id'] = $get['subject_id'];
        $filter_work_upload['subject_id'] = $get['subject_id'];
        //$filter_work_upload['work_id'] = $get['work_id'];
        $datas = \think\Db::name('student_works_upload')->where($filter_work_upload)->select();
        $assign = ['datas'=>$datas,'getdata'=>$get];
        return $this->fetch("getupload",$assign); 
    }
    
}
