<?php
namespace application\admin\controller;
/**
 * 基础控制器
 */
use think\Controller;
class Base extends Controller {
	public function __construct(){
		parent::__construct();
        //dump(MBISReturn("操作成功", 0, [0,1,2]));exit;
		//$this->assign("v",MBISConf('CONF.wstVersion'));
        //是否前台显示
        $this->assign("list_common_is_show",ITSGetSelData('common','is_show'));
        $this->assign("v",'its-v1.0');
        $this->assign("defaultimg",\think\Request::instance()->domain().'/static/images/default.jpg');
        //是否允许编辑用户信息
        $this->assign('privAllowEditUserInfo',session("MBIS_STAFF.staffRoleId")==1?1:0);
        
	}
    protected function fetch($template = '', $vars = [], $replace = [], $config = [])
    {
    	$replace['__ADMIN__'] = str_replace('/index.php','',\think\Request::instance()->root()).'/application/admin/view';
        return $this->view->fetch($template, $vars, $replace, $config);
    }

	public function getVerify(){
		MBISVerify();
	}
	
	public function uploadPic(){
		return MBISUploadPic(1);
	}

	/**
    * 编辑器上传文件
    */
    public function editorUpload(){
        return MBISEditUpload(1);
    }
    
    //选择下拉数据
    public function seldata()
    {
        $get = input('get.');
        $mdl = $get['mdl'];
        $id = $get['id'];
        $name = $get['name'];
        empty($get['selval']) && $get['selval']='';
        $field = "*,{$id} as id,{$name} as name";
        $where = !empty($get['filter'])?$get['filter']:[];
        //支持 & 连接参数
        if(!empty($where) && strpos($where,'&')!==FALSE)
        {
            $tmp_where = [];
            $arr_where = explode('&amp;',$where);
            foreach($arr_where as $v)
            {
                $arr_where2 = explode('=',$v);
                !empty($arr_where2[0]) && $tmp_where[$arr_where2[0]] =  $arr_where2[1];
            }
            !empty($tmp_where) && $where = $tmp_where;
        }
        if($mdl=='major_edu'){
            $school_id = $where['school_id'];
            unset($where);
            $where['school_ids'] = ['like',"%{$school_id}%"];
        }
        $datas = \think\Db::name($mdl)->field($field)->where($where)->select();
        if($mdl=='course_subject'){
            foreach($datas as &$v)
            {
                $v['name'] = \think\Db::name('subject')->where('subject_id',$v['name'])->value('name');  
            }
        }
        if($mdl=='major_edu_extend'){
            foreach($datas as &$v)
            {
                $v['name'] = ITSSelItemName('major','level_type',$v['name']);  
            }
        }
        if($mdl=='course'){
            foreach($datas as &$v)
            {
                $v['name'] .= '('.$v['offers_price'].')';  
            }
        }
        $assign = ['datas'=>$datas,'getdata'=>$get];
        return $this->fetch("./seldata",$assign);
    }
    
}