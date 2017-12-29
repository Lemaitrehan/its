<?php
namespace application\api\controller;
use application\common\model\Users as MUsers;
use application\common\model\Orders as itsMOrders;
use application\common\model\LogSms;
/**
* 用户控制器
 */
class Users extends Base{
	
	/**
	 * 用户中心
	 */
	public function index(){
        //MBISApiReturn($rs);
	}
    
    /**
	 * 订单列表
	 */
	public function orderLists(){
        $order = new itsMOrders();
        $rs = $order->getApiOrderLists();
        MBISApiReturn($rs);
	}
	
    /**
	 * 订单详情
	 */
	public function orderDetail(){
        $order = new itsMOrders();
        $rs = $order->getApiOrderDetail();
        MBISApiReturn($rs);
	}
    
    /**
     * 修改密码
    */
    public function editPass(){
        $m = new MUsers();
        $rs = $m->editPass(Input('post.userId'));
        MBISApiReturn($rs);
	}
    
    /**
     * 我的消息
    */
    public function myMessage(){
        $m = new MUsers();
        $rs = $m->myMessage();
        MBISApiReturn($rs);
	}
    
    /**
     * 我的二维码
    */
    public function myQrcode(){
        $m = new MUsers();
        $rs = $m->myQrcode();
        MBISApiReturn($rs);
	}
    public function showQrcode(){
        $m = new MUsers();
        $m->showQrcode();
	}
    //读取个人资料
    public function getUserInfo(){
        $m = new MUsers();
        $rs = $m->getUserInfo();
        MBISApiReturn($rs);
	}
    //修改个人资料
    public function editUserInfo(){
        $m = new MUsers();
        $rs = $m->editUserInfo();
        MBISApiReturn($rs);
	}
	//更新学员信息
	public function updateStudentsInfo(){
	    $m = new Musers();
	    $rs = $m->addUserInfo();
	    MBISApiReturn($rs);
	}
}

