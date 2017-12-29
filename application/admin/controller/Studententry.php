<?php
namespace application\admin\controller;
use application\admin\model\Studententry as M;
// +----------------------------------------------------------------------
// | 报名管理
// +----------------------------------------------------------------------
// | Author: lijianhua
// +----------------------------------------------------------------------
class Studententry extends Base{
    //学历报名信息
    public function indexEducation (){
        $m = new M();
        if( request()->isAjax() ){
          return $eduInfo = $m->getEduInfo();
        }
        $this->assign('type',1);
        return $this->fetch('educationlist');
    }
    
    //技能报名信息
    public function indexSkill(){
         $m = new M();
         if( request()->isAjax() ){
             return $eduInfo = $m->getSkillInfo();
         }
         $this->assign('type',2);
         return $this->fetch('skillist');
    }
    
    //学历到处报名数据
    public function expUsersEdu(){
        $m = new M();
        $m->expUsersEdu();
    }
    //技能类 报名数据
    public function expUsersSkil(){
        $m = new M();
        $m->expUsersSkil();
    }
    
    public function InfoDownload(){  //数据导出Excel下载文件的函数
        header("Content-type:text/html;charset=utf-8");
        $path = input('get.path');  //存储路径
        $file=input('get.file');  //文件名称
        $file_path = $path.$file;
        if(!file_exists($file_path))
        {
            echo "文件不存在或已丢失";
            return ;
        }
        $fp=fopen($file_path,"r");
        $file_size=filesize($file_path);
        //下载文件需要用到的头
        Header("Content-type: application/octet-stream");
        Header("Accept-Ranges: bytes");
        Header("Accept-Length:".$file_size);
        Header("Content-Disposition: attachment; filename=".$file);
        $buffer=1024;
        $file_count=0;
        while(!feof($fp) && $file_count<$file_size)
        {
            $file_con=fread($fp,$buffer);
            $file_count+=$buffer;
            echo $file_con;
        }
        fclose($fp);
    }
    
    //分配年级处理
    public function batch_set_grade()
    {
        if(request()->isPost())
        {
           $post = input('post.');
           $ids = explode(',',$post['ids']);
           $grade_info = $post['sel_grade_id'];
           list($grade_id,$grade_name) = explode('###',$grade_info);
           $flag = model('admin/StudentEdu')->save(['grade_id'=>$grade_id,'grade_name'=>$grade_name],['edu_id'=>['in',$ids]]);
           if($flag!==false) return MBISReturn('提交成功',1);
           if($flag===false) return MBISReturn('提交失败');
        }
        else
        {
            $ids = input('get.ids');
            $this->assign('ids',$ids);
            $ids = explode(',',$ids);
            $datas = model('admin/StudentEdu')->where(['edu_id'=>['in',$ids]])->select();
            foreach($datas as $v)
            {
                $v['userId'] = \Think\Db::name('users')->where('userId',$v['userId'])->value('trueName');   
            }
            $this->assign('datas',$datas);
            $grades = model('admin/grade')->select();
            $this->assign('grades',$grades);
            return $this->fetch('grade-lists'); 
        }
    }
}
