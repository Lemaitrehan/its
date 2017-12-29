<?php
namespace application\admin\model;
/**
 * 科目业务处理
 */
use think\Db;
use think\Model;
class SubjectEdu extends Base{
    /*###################################################################################*/
    /*
    *学历类自考类科目业务处理
    *
    */
    //加载数据列表--搜索--查询处理
    public function pageQueryEdu(){
        $type_id = input('get.type_id');
        $exam_method = input('get.exam_method');
        $exam_time = input('get.exam_time');
        $genre = input('get.genre');
        $name = input('get.name');
        $subject_no = input('get.subject_no');
        $type_number = input('get.type_number');
        $where = [];
        if($type_id != '')
        {
            $where['subject_type_id'] = ['=',"$type_id"];
        }
        if($exam_method != '')
        {
            $where['exam_method'] = ['=',"$exam_method"];
        }
        if($exam_time != '')
        {
            $where['exam_time'] = ['=',"$exam_time"];
        }
        if($genre != '')
        {
            $where['genre'] = ['=',"$genre"];
        }
        if($name != '')
        {
            $where['name'] = ['like',"%$name%"];
        }
        if($subject_no != '')
        {
            $where['subject_no'] = ['like',"%$subject_no%"];
        }
        if($type_number != '')
        {
            $where['type_number'] = ['like',"%$type_number%"];
        }

        $field = 'subject_id,name,subject_no,type_number,credit,genre,exam_method,exam_time';

        $page = $this
                ->where($where)
                ->field($field)
                ->order('lastmodify desc')
                ->paginate(input('post.pagesize/d'))
                ->toArray();
        if(count($page['Rows'])>0)
        {
            foreach ($page['Rows'] as $key => $v)
            {
                $page['Rows'][$key]['credit'] = $this->getCredit($v['credit']);
                $page['Rows'][$key]['genre'] = $this->getGenre($v['genre']);
                $page['Rows'][$key]['exam_method'] = $this->getExamMethod($v['exam_method']);
                $page['Rows'][$key]['exam_time'] = $this->getExamTime($v['exam_time']);
            }
        }
        return $page;
    }
    //新增处理
    public function addEdu(){
        $data = input('post.');
        if($data['subject_no'] !== '')
        {
            $where['subject_no'] = $data['subject_no'];
        }
        $res = $this->where($where)->find();
        if($res)
        {
            //return MBISReturn('科目代码已存在',-2);
        }
        if($data['type_id'])
        {
            $data['subject_type_id'] = $data['type_id'];
        }else
        {
            $data['subject_type_id'] = 1;
        }
        $data['lastmodify'] = time();
        MBISUnset($data,'subject_id,type_id');
        Db::startTrans();
        try{
            $result = $this->allowField(true)->save($data);
            if(false !== $result)
            {
                Db::commit();
                return MBISReturn("新增成功", 1);
            }
        }catch (\Exception $e) {
            Db::rollback();
        }
        return MBISReturn("新增失败",-1);
    }
    //编辑处理
    public function editEdu(){
        $id = (int)input('post.subject_id');
        $data = input('post.');
        $where = [];
        if($data['subject_no'] !== '')
        {
            $where['subject_no'] = $data['subject_no'];
        }
        $res = $this->where($where)->find();
        if($res && ($res['subject_id'] !== $id))
        {
            //return MBISReturn('科目代码已存在',-2);exit;
        }
        if($data['type_id'])
        {
            $data['subject_type_id'] = $data['type_id'];
        }else
        {
            $data['subject_type_id'] = 1;
        }
        $data['lastmodify'] = time();
        MBISUnset($data,'subject_id,type_id');
        $result = $this->allowField(true)->save($data,['subject_id'=>$id]);
        if(false !== $result)
        {
            return MBISReturn("编辑成功",1);
        }else
        {
            return MBISReturn("编辑失败",-1);
        }
    }
    //删除处理
    public function delEdu(){
        $type_id = input('post.type_id/d');
        $id = input('post.id/d');
        $result = $this->where(['subject_id'=>$id])->delete();
        if(false !== $result)
        {
            return MBISReturn("删除成功", 1);
        }else
        {
            return MBISReturn("删除失败", -1);
        }
    }
    //获取一条数据
    public function getSubjectOne($id){
        $res = $this->get(['subject_id'=>$id]);
        return $res;
    }
    //获取学分数据
    public function getCredit($id){
        switch($id)
        {
            case 1:return '1';
            case 2:return '2';
            case 3:return '3';
            case 4:return '4';
            case 5:return '5';
            case 6:return '6';
            case 7:return '7';
            case 8:return '8';
            case 9:return '9';
            case 10:return '10';
            case 11:return '11';
            case 12:return '12';
            case 13:return '13';
            case 14:return '14';
            case 99:return '不计学分';
        }
    }
    //获取类型数据
    public function getGenre($id){
        switch($id)
        {
            case 1:return '必考';
            case 2:return '选考';
            case 3:return '加考';
        }
    }
    //获取考试方式数据
    public function getExamMethod($id){
        switch($id)
        {
            case 1:return '笔试';
            case 2:return '实践考试';
        }
    }
    //获取考试时间数据
    public function getExamTime($id){
        switch($id)
        {
            case 1:return '1、7';
            case 2:return '4、10';
        }
    }

    
    //查找专业下面的科目
    public function getSubject(){
        $join = array(
            array('major m','m.major_id = s.major_id','left'),
        );
        $res = $this->alias('s')
                    ->join($join)
                    ->field('s.subject_id,s.name')
                    ->select();
        return $res;
    }
}
