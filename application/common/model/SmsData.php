<?php
// +----------------------------------------------------------------------
// | 短信数据类
// +----------------------------------------------------------------------
// | Author: lijianhua
// +----------------------------------------------------------------------

namespace application\common\model;
use think\Db;

class SmsData{
    protected $arrExamineType = array(
                                    0 => '未知',
                                    1 => '自考',
                                    2 => '成考',
                                    3 => '网教'
                           );
    
    //考试数据
 /*    function examineData(){
        $examineType    = input('post.examineType'); //考试类型
        $examineType    = 1;
        $arrExamineType = $this->arrExamineType;//考试类型数组
        $map['exam_type'] = $examineType;
        $res = $this->examineType($map);//获取考试人的个人信息
    } */
    
    
    /**
     * 查找用户需要的考试信息
     * @param unknown $userIds 用户ids
     * @param string $examineType 考试类型
     * @param string $is_auto 自动 还是 手动发送
     * @param string $date  手动选择日期
     * @param string $limit 限制自动发送时 保存的短信条目
     */
    public function usersExamination($is_auto="",$limit="",$userIds="",$examineType="",$date=""){
   
        if($examineType){
            $map['zy.exam_type'] = 1;
        }
        //用户id
        if($userIds){
           $map['e.userId']  = ['in',$userIds];
        }
        //手动发送日期
        if($date){
            $map['e.userId']  = ['in',$userIds];
        }
        $field = 'user.userId,user.trueName,user.userPhone,
                  FROM_UNIXTIME(e.exam_time) as exam_time ,
                  e.id,s.name
	             ';
        $join   = array( array( 'users user','user.userId = e.userId','LEFT' ),
                         array( 'subject s','s.subject_id = e.subject_id','LEFT' ),
                         array( 'major zy','zy.major_id   = s.major_id','LEFT' ),
        );
        if($is_auto){
            //未通知的学院SMSTIME
            $map['e.is_sms'] = 0;
            //考试时间已过期的
            $map['e.exam_time'] = ['>=',time() ]; ;
            $res  = db::name('sj_exams')->alias('e')
                                        ->join($join)
                                        ->where($map)
                                        ->field($field)
                                        ->LIMIT($limit)
                                        ->select();
    
        }else{
            $res  = db::name('sj_exams')->alias('e')
                                        ->join($join)
                                        ->where($map)
                                        ->field($field)
                                        ->select();
        }
        return $res;
    }
   /*  //考试类型
    function examineType($where){
        //exam_type  考试类型：0为未知、1为自考、2为成考、3为网教
        $join = [ ['subject km','km.subject_id = ks.subject_id','LEFT'],
            ['major zy','zy.major_id = km.major_id','LEFT'],
            ['users u','u.userId = ks.userId','LEFT'],
        ];
    
        $res =  db::name('sj_exams ks')->join($join)
        ->field('u.trueName,u.userPhone,zy.exam_type')
        ->where($where)
        ->select();
        return $res;
    } */
  
}
