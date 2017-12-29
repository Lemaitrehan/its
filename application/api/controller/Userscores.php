<?php
namespace application\api\controller;
/**
* 积分控制器
 */
class Userscores extends Base{
    /**
    * 查看商城消息
    */
	public function index(){
		$rs = model('Users')->getFieldsById((int)session('MBIS_USER.userId'),['userScore','userTotalScore']);
		$this->assign('object',$rs);
		return $this->fetch('users/userscores/list');
	}
    /**
    * 获取数据
    */
    public function pageQuery(){
        $userId = (int)session('MBIS_USER.userId');
        $data = model('UserScores')->pageQuery($userId);
        return MBISReturn("", 1,$data);
    }
}
