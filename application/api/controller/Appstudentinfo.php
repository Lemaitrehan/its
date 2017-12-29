<?php
// +----------------------------------------------------------------------
// | APP学员数据补交
// +----------------------------------------------------------------------
// | Author: lijianhua
// +----------------------------------------------------------------------
namespace application\api\controller;
use think\Db;
/**
* 学员
 */
class Appstudentinfo extends Base{
    
    function studentInfoSubmit(){
        $userId      =  input('post.userId');//用户id
        $trueName    =  input('post.trueName');//用户名称
        $userPhone   =  input('post.userPhone');//用户电话
        $idcard      =  input('post.idcard');//用户身份证
        
        $province    =  input('post.province');//省
        $city        =  input('post.city');//市
        $address     =  input('post.address');//详细地址
        $userAddress =  $province.$city.$address;//用户地址
        
        Db::startTrans();//开启事物
            $data['trueName']    =  $trueName;
            $data['userPhone']   =  $userPhone;
            $data['idcard']      =  $idcard;
            $data['userAddress'] =  $userAddress;
            $update_id1          =  model('users')->save($data,['userId' => $userId]);//
            if(!$update_id1){
                Db::rollback();
                MBISApiReturn( MBISReturn('数据更新失败！！！',-1,array() ) );
            } 
            $userExtend = model('student_extend')->where('userId = '.$userId)->find();
            $data1['userId']      =  $userId;
            $data1['idcard_no']   =  $idcard;
            $data1['province']    =  $province;
            $data1['city']        =  $city;
            $data1['address']     =  $address;
            if($userExtend['userId']){
               $update_id2        =  model('student_extend')->save($data1,['userId' => $userId]);
            }else{
               $update_id2        =  model('student_extend')->save($data1);
            }
            if($update_id2){
                Db::commit();
                MBISApiReturn( MBISReturn('数据提交成功！！！',1,array() ) );
            }
    }
      
    
  
    
}



?>
