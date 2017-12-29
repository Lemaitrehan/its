<?php
namespace application\admin\controller;
use application\admin\model\Studentmatriculate as M;
// +----------------------------------------------------------------------
// | 报名管理
// +----------------------------------------------------------------------
// | Author: liuyaping
// +----------------------------------------------------------------------
class Studentmatriculate extends Base{
    //学历报名信息
    public function indexEdu(){
        $exam_type = session('examType');
        $m = new M();
        if( request()->isAjax() ){
          return $eduInfo = $m->getEduInfo();
        }
        $grade = $m->getGrade($exam_type); //年级列表
        $this->assign('grade',$grade);

        $school = $m->getSchool($exam_type); //院校列表
        $this->assign('school',$school);

        $major = $m->getMajor($exam_type); //专业列表
        $this->assign('major',$major);
        $this->assign('exam_type',$exam_type);
        $this->assign('type_id',1);
        return $this->fetch('educationlist');
    }

    public function pageQuery(){
        $m = new M();
        return $m->pageQuery();
    }

    public function toEdit(){
        $m = new M();
        $this->assign('type_id',1);
        $edu_id = input('get.id');
        $res = $m->getInfoOne($edu_id);
        $this->assign('data',$res);
        return $this->fetch('edit');
    }

    public function edit(){
        $m = new M();
        return $m->edit();
    }  

    public function majorGet(){
        $m = new M();
        return $m->majorGet();
    }

    public function levelGet(){
        $m = new M();
        return $m->levelGet();
    }

    public function import(){  //上传处理表格文件
        $m      = new M();
        $file = request()->file('exel');
        //dd($file);
        // 移动到框架应用根目录/public/uploads/目录下
        $info = $file->validate(['size'=>15678,'ext'=>'xlsx'])->rule('md5')->move(ROOT_PATH . 'public' . DS . 'upload'.'\studentedu','',false);
        if($info){
            $file = ROOT_PATH . 'public' . DS . 'upload'.'\studentedu\\'.$info->getSaveName();
            //dd($file);
            $res =  $m->importStudentEdu($file);
            if((int)$res){
                $this->success('导入数据成功！！！',url('studentedu/indexEdu') );
            }else{
                $this->success($res,url('studentedu/indexEdu') );
            }
        }else{
            // 上传失败获取错误信息
            $msg =  $file->getError();
            $this->success($msg,url('studentedu/indexEdu') );
            
        
        }
    }

    public function matriculate(){  //批量录取
        $m = new M();
        return $m->matriculate();
    }
    public function getEduList(){
        $m = new M();
        return $m->getEduList();
    }

    public function expStudentEdu(){  //导出
        $m = new M();
        return $m->expStudentEdu();
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
}
