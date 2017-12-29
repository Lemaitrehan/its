<?php
namespace application\common\model;
use think\Db;

/**
 * 订单业务处理类
 */
class AppIndex extends Base{
    
    public $hotSearchTerm = array(1=>'艺术设计',2=>'在线教育',3=>'自考'); 
     
    /**
     * 热门课程
     * 2017-3-25
     */
     function hotCourse(){
         $arr = db::name('course')->field('name,cover_img,course_hours')
                                          ->where('is_hot = 1')
                                          ->select();
         foreach ($arr as $k => $v){
             foreach($v as $key => $t){
                 if( $key == 'cover_img' && $t ){
                     $arr[$k][$key] = SERVERHOST.'/'.$t;
                 }
             }
         }
         return $arr; 
    
    }
    
    /**
     * 热门科目
     * 2017-3-25
     */
    function hotSubject(){
        $arr = db::name('subject')->field('name,cover_img,course_hours')
                                          ->where('is_hot = 1')
                                          ->select();
        foreach ($arr as $k => $v){
            foreach($v as $key => $t){
                if( $key == 'cover_img' && $t ){
                    $arr[$k][$key] = SERVERHOST.'/'.$t;
                }
            }
        }
        return $arr; 
    }
    
    /**
     * 热门专业
     * 2017-3-25
     */
    function hotProfessional(){
        $arr = db::name('major')->field('name,cover_img')
                                  ->where('is_hot = 1')
                                  ->select();
        
        foreach ($arr as $k => $v){
            foreach($v as $key => $t){
                if( $key == 'cover_img' && $t ){
                    $arr[$k][$key] = SERVERHOST.'/'.$t;
                }
            }
        }
        return $arr; 
    }
    
    
    
    /**
     * 课程关键字搜索
     * 2017-3-25
     */
    function searchCourse(){
        
        $courseName  = input('post.courseName');
        $map['name'] = ['like','%'.$courseName.'%']; 
        $arr         = db::name('course')->field('name,cover_img,course_hours')
                                         ->where($map)
                                         ->limit(6)
                                         ->select();
        foreach ($arr as $k => $v){
            foreach($v as $key => $t){
                if( $key == 'cover_img' && $t ){
                    $arr[$k][$key] = SERVERHOST.'/'.$t;
                }
            }
        }
        
        return $arr;
    }
    
    
    
	     
}
