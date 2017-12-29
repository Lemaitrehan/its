<?php
namespace application\api\controller;
/**
 * 文件上传控制器
 */
class Upload extends Base{
	
    public function base64Upload(){
        $data = html_entity_decode(input('post.data'));
        $flag = preg_match('/data:image\/png;base64*/i',$data) ? true : false;
        !$flag && MBISApiReturn(MBISReturn("数据格式有误"));
        $dirpath = 'upload/base64/signimg/'.date('Y/m/d').'/';
        if(!is_dir($dirpath))
		{
			@mkdir($dirpath,0777,true);	
		}
        $filename = uniqid().'.png';
        $url = $dirpath.$filename;
        $full_url = request()->domain().'/'.$url;
        @file_put_contents($dirpath.$filename,file_get_contents($data));
    	MBISApiReturn(MBISReturn("",1,['full_url'=>$full_url,'url'=>$url]));
    }
    
}
