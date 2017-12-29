<?php
namespace application\admin\model;
/**
 * 年级业务处理
 */
use think\Db;
use think\Model;
class Grade extends Base{
	/**
	 * 分页
	 */
	public function pageQuery(){
        $key = input('get.key');
        $major_id = input('get.major_id');
        $where = [];
        if($major_id!='')$where['major_id'] = $major_id;
		if($key!='')$where['name'] = ['like','%'.$key.'%'];
        $page = $this->where($where)->field('*')->order('lastmodify desc')
		->paginate(input('post.pagesize/d'))->toArray();
        
        if(count($page['Rows'])>0){
            $major = model('Major');
			foreach ($page['Rows'] as $key => $v){
                $page['Rows'][$key]['major_id'] = $major->get_name($v['major_id']);
                $page['Rows'][$key]['rp_start_time'] = ITSTime2Date($v['rp_start_time']);
                $page['Rows'][$key]['rp_end_time'] = ITSTime2Date($v['rp_end_time']);
			}
		}
        
        return $page;
	}
	public function getById($id){
        $rs = $this->get(['grade_id'=>$id]);
        
        if(isset($rs['rp_start_time']))
        {
            $rs['rp_start_time'] = ITSTime2Date($rs['rp_start_time']);
        }
        if(isset($rs['rp_end_time']))
        {
            $rs['rp_end_time'] = ITSTime2Date($rs['rp_end_time']);
        }
        
		return $rs;
	}
	/**
	 * 新增
	 */
	public function add(){
		$data = input('post.');
        //dd($data);
		$data['createtime'] = time();
        $data['lastmodify'] = time();
		MBISUnset($data,'grade_id,id');
        $data['rp_start_time'] = ITSDate2Time($data['rp_start_time'],'begin');
        $data['rp_end_time'] = ITSDate2Time($data['rp_end_time'],'end');
        Db::startTrans();
		try{
			$result = $this->allowField(true)->save($data);
			$id = $this->grade_id;
	        if(false !== $result){
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
		$id = (int)input('post.grade_id');
		$data = input('post.');
        $data['lastmodify'] = time();
		MBISUnset($data,'id');
        $data['rp_start_time'] = ITSDate2Time($data['rp_start_time'],'begin');
        $data['rp_end_time'] = ITSDate2Time($data['rp_end_time'],'end');
		Db::startTrans();
		try{
		    $result = $this->allowField(true)->save($data,['grade_id'=>$id]);
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
		    $result = $this->where(['grade_id'=>$id])->delete();
	        if(false !== $result){
	        	Db::commit();
	        	return MBISReturn("删除成功", 1);
	        }
		}catch (\Exception $e) {
            Db::rollback();
            return MBISReturn('删除失败',-1);
        }
	}
    //数据列表
    public function get_lists($params=[])
    {
        $params = input('post.');
        $where = [];
        if(isset($params['major_id']))
        {
            $where['major_id'] = $params['major_id'];   
        }
        return $this->where($where)->select();   
    }
    /**
      * @do 获取专业年级价格
     */
    public function get_grade_price($params=[])
    {
        $where = [];
        if(isset($params['grade_id']))
        {
            $where['grade_id'] = $params['grade_id'];
        }
        $rs = $this->where($where)->field('stu_fee,offers')->find();
        $sale_price = $rs['stu_fee'];
        //优惠价 < 销售价,设置优惠价=销售价
        if($rs['offers'] < $sale_price)
        {
           $sale_price = $rs['offers'];
        }
        return $sale_price;
    }
    public function get_grade_data($params=[])
    {
        $where = [];
        if(isset($params['grade_id']))
        {
            $where['grade_id'] = $params['grade_id'];
        }
        $rs = $this->where($where)->field('*')->find();
        return $rs;
    }
    
    //获取年级信息
    public function getGrade(){
        
       return  $this->field('grade_id,name')->select();
    }

#################################################################################################
    /*
    *学历类年级管理
    */
    public function pageQueryEdu(){
        $exam_type = session('examType');
        $key = input('get.key');
        $where = [];
        if($key!='')$where['name'] = ['like','%'.$key.'%'];
        if($exam_type)$where['exam_type'] = ['=',"$exam_type"];
        $page = $this
                ->where($where)
                ->field('grade_id,name')
                ->order('lastmodify desc')
                ->paginate(input('post.pagesize/d'))
                ->toArray();
        return $page;
    }
    //获取一条数据
    public function getGradeOne($id){
        $rs = $this->get(['grade_id'=>$id]);
        return $rs;
    }
    /**
     * 新增
     */
    public function addEdu(){
        $exam_type = session('examType');
        $data = input('post.');
        //dd($data);
        $data['exam_type'] = $exam_type;
        $data['createtime'] = time();
        $data['lastmodify'] = time();
        MBISUnset($data,'grade_id');
        //dd($data);
        Db::startTrans();
        try{
            $result = $this->allowField(true)->save($data);
            if(false !== $result){
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
    public function editEdu(){
        $exam_type = session('examType');
        $id = (int)input('post.grade_id');
        $data = input('post.');
        $data['lastmodify'] = time();
        $data['exam_type'] = $exam_type;
        MBISUnset($data,'id');
        Db::startTrans();
        try{
            $result = $this->allowField(true)->save($data,['grade_id'=>$id]);
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
    public function delEdu(){
        $id = input('post.id/d');
        Db::startTrans();
        try{
            $result = $this->where(['grade_id'=>$id])->delete();
            if(false !== $result){
                Db::commit();
                return MBISReturn("删除成功", 1);
            }
        }catch (\Exception $e) {
            Db::rollback();
            return MBISReturn('删除失败',-1);
        }
    }
   
}
