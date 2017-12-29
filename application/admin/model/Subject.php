<?php
namespace application\admin\model;
/**
 * 科目业务处理
 */
use think\Db;
use think\Model;
class Subject extends Base{
    
    //上课方式
    public  $arrTeachingType = array(
                1=>'线下面授',
                2=>'线上直学',
                3=>'混合【线下+线上】',
               );
    
    //是否属于公共科目
    public  $arrPublicSubject = array(
        1=>'否',
        2=>'是'
    );
    
    public $deposit_price = 500;//课程定金
	/**
	 * 分页
	 */
	public function pageQuery(){
        $where = [];
        $type_id = input('get.type_id');
        $school_id = input('get.school_id');
        $major_id = input('get.major_id'); 
        $is_shelves = input('get.is_shelves');
        $name = input('get.name');
        $teaching_type = input('get.teaching_type');
        $teacher_id = input('get.teacher_id');

        if($school_id != ''){
            $where['s.school_id'] = ['=',"$school_id"];
        }
        if($major_id != ''){
            $where['s.major_id'] = ['=',"$major_id"];
        }
        if($is_shelves != ''){
            $where['s.is_shelves'] = ['=',"$is_shelves"];
        }
        if($name != ''){
            $where['s.name'] = ['like',"%$name%"];
        }
        if($teaching_type != ''){
            $where['s.teaching_type'] = ['=',"$teaching_type"];
        }
        if($teacher_id != ''){
            $where['s.teacher_id'] = ['=',"$teacher_id"];
        }
        if($type_id!=''){
            //$where['s.subject_type_id'] = ['=',"$type_id"];
        }
        $where['m.type_id'] = ['=',"$type_id"];
        
        $join = array(
                   array('major m','m.major_id = s.major_id','left'),
                   array('school sc','sc.school_id = s.school_id','left'),
                   array('subject_type type','type.type_id = s.school_id','left'),
            
            
        );
        $page = $this->alias('s')
                     ->where($where)
                     //->whereOr('s.is_public','=','2')
                     //->where('s.is_public',['in',[1,2]],['>=',1],'or')    //id在1~3之间，或者id>=1
                     ->join($join)
                     //->field('s.*')
                     ->field('s.*,m.name as major_name,sc.name as school_name,type.name as type_name')
                     ->order('lastmodify desc')
		             ->paginate(input('post.pagesize/d'))
                     ->toArray();
        if(count($page['Rows'])>0){
            $subjectType = model('SubjectType');
			foreach ($page['Rows'] as $key => $v){
                //$page['Rows'][$key]['course_id'] = model('CourseSubject')->get_course_name($v['subject_id']);
                //$page['Rows'][$key]['major_id'] = model('major')->get_name($v['major_id']);
                //$page['Rows'][$key]['school_id'] = model('school')->get_name($v['school_id']);
                //$page['Rows'][$key]['subject_type_id'] = $subjectType->get_name($v['subject_type_id']);
                $page['Rows'][$key]['teaching_type'] = ITSSelItemName('subject','teaching_type',$v['teaching_type']);
                $page['Rows'][$key]['is_shelves'] = ITSSelItemName('subject','is_shelves',$v['is_shelves']);
                $page['Rows'][$key]['teacher_id'] = $v['teacher_id'] == 0 ? '尚未安排' : $this->get_teacher_name($v['teacher_id']);
			}
		}
        return $page;
	}
	
	
	public function getById($id){
        $rs = [];
        $rs['subject_id'] = 0;
        $rs['teacher_id'] = 0;
        $rs['cover_img'] = '';
        $rs['album_imgs'] = '';
        if($id>0){
            $rs = $this->get(['subject_id'=>$id]);
        }
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
		if($data['is_public'] == 2){
		    unset($data['school_id']);
		    unset($data['major_id']);
		}
        $where = [];
        if($data['subject_no'] !== ''){
            $where['subject_no'] = $data['subject_no'];
        }
        $res = $this->where($where)->find();
        if($res){
            return MBISReturn('科目代码已存在',-2);
        }
        /* if($data['subject_type_id'] == 1){
            $data['it_ids'] = [];
        }else{
            $data['it_ids'] = explode(',',$data['it_ids'][0]);
        } */
        $data['subject_type_id'] = 2;
        $prop_id_list = isset($data['prop_id_list'])?$data['prop_id_list']:[];
        $prop_value_list = isset($data['prop_value_list'])?$data['prop_value_list']:[];
        $member_price = isset($data['member_price'])?$data['member_price']:[];
        $it_ids = isset($data['it_ids'])?$data['it_ids']:[];
		$data['createtime'] = time();
        $data['lastmodify'] = time();
		MBISUnset($data,'subject_id,id,prop_id_list,prop_value_list,member_price,it_ids');
        if(isset($data['front_ids']))
        {
            $data['front_ids'] = implode(',',$data['front_ids']);   
        }
        //优惠方式
        if(isset($data['discount']))
        {
            $data['offer_type_ids'] = serialize($data['discount']);
            MBISUnset($data,'discount');
        }
        Db::startTrans();
		try{
			$result = $this->allowField(true)->save($data);
			$id = $this->subject_id;
	        if(false !== $result){
                model('SubjectTypePropValue')->set_prop_value($id,['prop_id_list'=>$prop_id_list,'prop_value_list'=>$prop_value_list]);
                model('SubjectLvPrice')->set_lv_price_value($id,$member_price);
                //model('CourseItem')->set_course_item_value($data['subject_type_id'],0,$id,$it_ids);
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
		$id = (int)input('post.id');
		$data = input('post.');
        /* if($data['subject_type_id'] == 1){
            $data['it_ids'] = [];
        }else{
            $data['it_ids'] = explode(',',$data['it_ids'][0]);
        } */
        $prop_id_list = isset($data['prop_id_list'])?$data['prop_id_list']:[];
        $prop_value_list = isset($data['prop_value_list'])?$data['prop_value_list']:[];
        $member_price = isset($data['member_price'])?$data['member_price']:[];
        $it_ids = isset($data['it_ids'])?$data['it_ids']:[];
        $data['lastmodify'] = time();
		MBISUnset($data,'createtime,id,prop_id_list,prop_value_list,member_price,it_ids');
        if(isset($data['front_ids']))
        {
            $data['front_ids'] = implode(',',$data['front_ids']);   
        }
        //优惠方式
        if(isset($data['discount']))
        {
            $data['offer_type_ids'] = serialize($data['discount']);
            MBISUnset($data,'discount');
        }
		Db::startTrans();
		try{
		    if($data['is_public']==2){
		        $data['school_id'] = '';
		        $data['major_id']  = '';
		    }
		    $result = $this->allowField(true)->save($data,['subject_id'=>$data['subject_id']]);
	        if(false !== $result){
                model('SubjectTypePropValue')->set_prop_value($data['subject_id'],['prop_id_list'=>$prop_id_list,'prop_value_list'=>$prop_value_list]);
                model('SubjectLvPrice')->set_lv_price_value($data['subject_id'],$member_price);
                //model('CourseItem')->set_course_item_value($data['subject_type_id'],0,$data['subject_id'],$it_ids);
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
        $type_id = input('post.type_id/d');
	    $id = input('post.id/d');
	    Db::startTrans();
		try{
		    $result = $this->where(['subject_id'=>$id])->delete();
	        if(false !== $result){
                if($type_id == 2)
                {
                    Db::name('subject_lv_price')->where(['subject_id'=>$id])->delete();
                    Db::name('subject_type_prop_value')->where(['subject_id'=>$id])->delete();
                }
	        	Db::commit();
	        	return MBISReturn("删除成功", 1);
	        }
		}catch (\Exception $e) {
            Db::rollback();
            return MBISReturn('删除失败',-1);
        }
	}
	
	
    //科目类型列表
    public function get_subject_type_lists()
    {
        return Db::name('subject_type')->select();  
    }
    
    
    //科目属性列表
    public function get_subject_prop_data($type_id=0,$subject_id=0)
    {
        $prop_value =model('subject_type_prop_value')->get_subject_prop_value($subject_id);
        
        $lists = Db::name('subject_type_prop')->where(['type_id'=>$type_id])->select();
        foreach($lists as $k=>$v)
        {
            if(in_array($v['prop_input_type'],array(1)))
            {
                if($v['prop_value'])
                {
                    $lists[$k]['prop_value'] = explode(chr(10),$v['prop_value']);
                }
            }
            $lists[$k]['prop_default_value'] = isset($prop_value[$v['prop_id']])?$prop_value[$v['prop_id']]:'';
        }
        return $lists;  
    }
    
    
    //数据列表
    public function get_lists($where=[])
    {   $join = array(
                   array('school sc','sc.school_id = s.school_id','left'),
                   array('major m','m.major_id = s.major_id','left'),
                );
        $rs = $this->alias('s')
                   ->join($join)
                   ->where($where)
                   ->select();
        foreach($rs as &$v){
            //$v['school_id'] = $this->get_school_name($v['school_id']);
            //$v['major_id'] = $this->get_major_name($v['major_id']);
            $v['is_shelves'] = ITSSelItemName('subject','is_shelves',$v['is_shelves']);
            $v['teacher_id'] = $this->get_teacher_name($v['teacher_id']);
        }
        return $rs;
    }
    
    
    //获取字段值
    public function get_discount_setting($subject_id=0)
    {
        $offer_type_ids = $this->where('subject_id',$subject_id)->value('offer_type_ids');
        if(!$offer_type_ids) return '';
        return unserialize($offer_type_ids);   
    }
    
    
    //获取老师列表
    public function get_teacher_list()
    {
        $rs = Db::name('Users')->where(['userType'=>1,'dataFlag'=>1])->field('userId,nickName')->select();
        foreach($rs as &$v)
        {
            $v['rename_nickName'] = $v['nickName'].'('.$v['userId'].')';  
        }
        return $rs;   
    }
    
    
    public function get_teacher_lists(){
        $where = [];
        $where['u.userType'] = 1;
        $where['u.dataFlag'] = 1;
        $res = Db::name('users')->alias('u')->join('tc_extend t','u.userId=t.userId','left')->where($where)->field('u.userId,u.trueName,t.tc_no')->select();
        return $res;
    }
    
    
    public function get_teacher_name($id=0){
        $where = [];
        $where['userId'] = $id;
        $where['userType'] = 1;
        $where['dataFlag'] = 1;
        //$where['status'] = 1;
        return $res = Db::name('users')->where($where)->value('trueName'); 
    }
    
    
    public function get_school_name($id=0){
        return Db::name('school')->where('school_id',$id)->value('name');
    }
    
    
    public function get_major_name($id=0){
        return Db::name('major')->where('major_id',$id)->value('name');
    }
    
    
    public function getAdItemList(){
        $id_list = input('post.');
        if($id_list){
            $ids = [];
            $ids = $id_list['id'];
            return ['status'=>1,'data'=>$ids];
        }else{
            return ['msg'=>'系统错误','status'=>-1];
        }
    }
    
    
    public function getMajorSchoolId($id=0){
        return Db::name('major')->where('major_id',$id)->value('school_id');
    }
    
    /**
     * 查找科目基本信息
     * @param unknown $subject_id 科目id
     */
    public function getSubjectInfo($subject_id){
        //科目名称 ,上课方式,市场价格，优惠价格
        $field = 'name,cover_img,teaching_type,market_price,sale_price';
        $where['subject_id'] =  $subject_id;
        return  $this->field($field)->where($where)->select();
    }
}
