<?php
namespace application\admin\model;
use think\Db;
/**
 * 作品业务处理
 */
class StudentWorks extends Base{
	/**
	 * 分页
	 */
	public function pageQuery(){
        $results = $this->field('*')->order('userId desc')->group('major_id,subject_id')->paginate(input('pagesize/d'));
        foreach($results as &$v)
        {
            //用户信息
            $rs_users = Db::name('users')->field('*')->where(['dataFlag'=>1,'userId'=>$v['userId']])->find();
            $v['student_no'] = $rs_users['student_no']; 
            $v['idcard'] = $rs_users['idcard'];
            $v['trueName'] = $rs_users['trueName'];
            $v['userPhone'] = $rs_users['userPhone'];
            $v['userQQ'] = $rs_users['userQQ'];
            $v['userEmail'] = $rs_users['userEmail'];
            $v['study_status'] = ITSSelItemName('user','study_status',$rs_users['study_status']);
            //专业信息
            $rs_majors = Db::name('major')->field('*')->where(['major_id'=>$v['major_id']])->find();
            $v['majorName'] = $rs_majors['name'];
            //科目信息
            $rs_subjects = Db::name('subject')->field('*')->where(['subject_id'=>$v['subject_id']])->find();
            $v['subjectName'] = $rs_subjects['name'];
            $v['workNums'] = Db::name('student_works_upload')->where(['userId'=>$v['userId'],'major_id'=>$v['major_id'],'subject_id'=>$v['subject_id']])->count();;
        }
		return $results;
	}
	public function getById($id){
		return $this->get(['id'=>$id]);
	}
	/**
	 * 新增
	 */
	public function add(){
		$data = input('post.');
		$data['createTime'] = date('Y-m-d H:i:s');
		MBISUnset($data,'rankId');
		Db::startTrans();
		try{
			$result = $this->validate('UserRanks.add')->allowField(true)->save($data);
			$id = $this->rankId;
			//启用上传图片
			MBISUseImages(1, $id, $data['userrankImg']);
	        if(false !== $result){
	        	Db::commit();
	        	return MBISReturn("新增成功", 1);
	        }
		}catch (\Exception $e) {
            Db::rollback();
            return MBISReturn('删除失败',-1);
        }
	}
    /**
	 * 编辑
	 */
	public function edit(){
		$Id = (int)input('post.id');
		$data = input('post.');
		Db::startTrans();
		try{
			if(empty($data['works_data'])) return MBISReturn('请上传作品',-1);
            $data_up['works_data'] = implode(',',$data['works_data']);
            $data_up['exam_status'] = 0;
		    $result = Db::name('sj_exams_subject')->where(['id'=>$Id])->update($data_up);
	        if(false !== $result){
	        	Db::commit();
	        	return MBISReturn("编辑成功", 1);
	        }
		}catch (\Exception $e) {
            Db::rollback();
            return MBISReturn('编辑失败',-1);
        }	        
	}
	/**
	 * 删除
	 */
    public function del(){
	    $id = (int)input('post.id/d');
	    Db::startTrans();
		try{
			$data = [];
			$data['dataFlag'] = -1;
		    $result = $this->update($data,['rankId'=>$id]);
	        if(false !== $result){
	        	MBISUnuseImage('user_ranks','userrankImg',$id);
	        	Db::commit();
	        	return MBISReturn("删除成功", 1);
	        }
		}catch (\Exception $e) {
            Db::rollback();
            return MBISReturn('编辑失败',-1);
        }	
	}
    public function get_lists($subject_id=0)
    {
        $lists = $this->where('dataFlag',1)->select();
        $lists_lv_price = model('SubjectLvPrice')->get_lv_price($subject_id);
        foreach($lists as $k=>$v)
        {
            $lists[$k]['lv_price'] = isset($lists_lv_price[$v['rankId']])?$lists_lv_price[$v['rankId']]:'';
        }
        return $lists;   
    }
	
}
