<?php
namespace application\api\controller;
use think\Db;
use application\common\model\AppIndex as AppClass;
#use think\Request;
#use think\Url;
/**
* 学员
 */
class Appindex extends Base{
    
    /**
     * 热门课程
     * 2017-3-25
     */
    function hotCourse(){
           $appClass  =   new AppClass;
           $arrCourse =   $appClass->hotCourse();
           if($arrCourse){
               $status = 1;
           }else{
               $status = -1;
           }
           MBISApiReturn( MBISReturn('热门课程数据！！！',$status,$arrCourse ) );
    }
    
    /**
     * 热门科目
     * 2017-3-25
     */
    function hotSubject(){
        $appClass   =   new AppClass;
        $arrSubject =   $appClass->hotSubject();
        if($arrSubject){
            $status = 1;
        }else{
            $status = -1;
        }
        MBISApiReturn( MBISReturn('热门课程数据！！！',$status,$arrSubject ) );
    }
    
    /**
     * 分类（专业）
     * 2017-3-25
     */
    function hotProfessional(){
        $appClass   =   new AppClass;
        $arrProfessional =   $appClass->hotProfessional();
        if($arrProfessional){
            $status = 1;
        }else{
            $status = -1;
        }
        MBISApiReturn( MBISReturn('分类！！！',$arrProfessional,$arrProfessional ) );
    }
    /**
     * 热门关键字获取
     * 2017-3-25
     */
    function searchCourseTerm(){
        $appClass        =   new AppClass;
        $arrSearchTerm   =   $appClass->hotSearchTerm;
        $newArray = array();
        foreach ($arrSearchTerm as $key => $v ){
            $newArray[$key]['termId']   = $key;
            $newArray[$key]['termName'] = $v;
        }
        $newArray = array_values($newArray);
        MBISApiReturn( MBISReturn('分类！！！',1,$newArray ) );
    }
    
    /**
     * 课程关键字搜索
     * 2017-3-25
     */
    function searchCourse(){
        $appClass        =   new AppClass;
        $arrSearchCourse =   $appClass->searchCourse();
        if($arrSearchCourse){
            $status = 1;
        }else{
            $status = -1;
        }
        MBISApiReturn( MBISReturn('分类！！！',$status,$arrSearchCourse ) );
    }
    
    
}



?>
