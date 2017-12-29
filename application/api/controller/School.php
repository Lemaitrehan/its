<?php
namespace application\api\controller;
use application\common\model\Users as MUsers;
use application\common\model\School as itsMSchool;
use application\common\model\LogSms;
use think\Request;
use think\Db;
/**
* 学校控制器
 */
class School extends Base{
    /**
     * @do 首页学院列表
     */
    public function getIndexList()
    {
        $school = new itsMSchool();
        $rs = $school->getApiIndexList();
		MBISApiReturn($rs);
    }
    //学院列表筛选项
    public function getSelItems(){
        $school = new itsMSchool();
        $rs = $school->getApiSelItems();
		MBISApiReturn($rs);
    }
	/**
     * @do 学院列表
     */
    public function getList()
    {
        $school = new itsMSchool();
        $rs = $school->getApiList();
		MBISApiReturn($rs);
    }
    //学院专业
    public function getSchoolMajor()
    {
        $school = new itsMSchool();
        $rs = $school->getApiSchoolMajor();
        MBISApiReturn($rs);
    }
    //专业详情
    public function getMajorDetail()
    {
        $school = new itsMSchool();
        $rs = $school->getApiMajorDetail();
        MBISApiReturn($rs);
    }
    //年级列表
    public function getGradeList()
    {
        $school = new itsMSchool();
        $rs = $school->getApiGradeList();
        MBISApiReturn($rs);
    }
    //年级详情
    public function getGradeDetail()
    {
        $school = new itsMSchool();
        $rs = $school->getApiGradeDetail();
        MBISApiReturn($rs);
    }
    //科目详情
    public function getSubjectDetail()
    {
        $school = new itsMSchool();
        $rs = $school->getApiSubjectDetail();
        MBISApiReturn($rs);
    }
    //课程科目详情
    public function getCourseDetail()
    {
        $school = new itsMSchool();
        $rs = $school->getApiCourseDetail();
        MBISApiReturn($rs);
    }
    public function getCoursePrice()
    {
        $school = new itsMSchool();
        $rs = $school->getApiCoursePrice();
        MBISApiReturn($rs);
    }
    public function search(){
        //课程&科目搜索
        //word
        $word = Request::instance()->post('word');
        $word = trim($word);
        if(empty($word)){
            MBISApiReturn(MBISReturn("请输入搜索关键词",-1,null));
        }
        
        //搜索课程启动。
        $data = Db::name('course')->where(['name'=>['like',"%{$word}%"],'is_shelves'=>1])->field('name,course_id as id,cover_img,type_id')->select();
        foreach($data as &$val){
            $val['cover_img'] = ITSPicUrl($val['cover_img']);
        }
        unset($val);
        $searchResult['course'] = $data;
        
        $data = Db::name('subject')->where(['name'=>['like',"%{$word}%"],'is_shelves'=>1])->field('name,subject_id as id,cover_img,subject_type_id as type_id')->select();
        
        foreach($data as &$val){
            $val['cover_img'] = ITSPicUrl($val['cover_img']);
        }
        unset($val);
        $searchResult['subject'] = $data;
        MBISApiReturn(MBISReturn("",1,$searchResult));
    }
    /**
     * @do 获取通道列表
     */
    public function getChannelLists()
    {
        MBISApiReturn(MBISReturn("",1,get_channel_lists()));   
    }
}

