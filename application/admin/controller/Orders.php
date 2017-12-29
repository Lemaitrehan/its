<?php
namespace application\admin\controller;
use think\Db;
use think\Model;
use application\admin\model\Orders as M;
/**
 * 订单控制器
 */
class Orders extends Base{
	
    public function index(){
        //订单状态(多条件组合)
        $this->assign('sel_order_status',ITSGetSelData('order','order_status'));
        $this->assign("type_id",Input("type_id/d",0));
    	return $this->fetch("list");
    }
    /**
     * 获取分页
     */
    public function pageQuery(){
        $m = new M();
        return $m->pageQuery();
    }
    /**
     * 跳去编辑页面
     */
    public function toEdit(){
        $m = new M();
        $this->assign("type_id",Input("type_id/d",0));
        $rs = $m->getById(Input("id/d",0));
        if(isset($rs['details']))
        {
            $rs['details'] = htmlspecialchars_decode($rs['details']);
        }
        $this->assign("object",$rs);
        return $this->fetch("edit");
    }
    /*
    * 获取数据
    */
    public function get(){
        $m = new M();
        $rs = $m->getById(Input("id/d",0));
        $this->assign("object",$rs);
        return $this->fetch("view");
    }
    /**
     * 新增
     */
    public function add(){
        $m = new M();
        return $m->add();
    }
    /**
    * 修改
    */
    public function edit(){
        $m = new M();
        return $m->edit();
    }
    /**
     * 删除
     */
    public function del(){
        $m = new M();
        return $m->del();
    }
    /**
    * 财务审核
    */
    public function audit(){
        $m = new M();
        $rs = $m->getById(Input("id/d",0));
        $this->assign("object",$rs);
        return $this->fetch("audit");
    }
    /**
    * 财务审核提交
    */
    public function toAudit(){
        $m = new M();
        return $m->toAudit();
    }

    public function expOrders(){
        $m = new M();
        return $m->expOrders();
    }

    public function InfoDownload(){  //下载已保存的xlsx文件
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
    /**
    * 添加订单
    */
    public function toAdd(){
        $type_id = Input("type_id/d",1);
        $m = new M();
        $rs_course = model('course')->where(['is_shelves'=>1])->select();
        $this->assign('course_list',$rs_course);
        $this->assign('type_id',$type_id);
        $this->assign('payment_lists',get_payment_lists());
        //$rs = $m->getById(Input("id/d",0));
        //$this->assign("object",$rs);
        return $this->fetch("add");
    }
    
    /**
     * checkout && 创建订单
     */
    public function checkoutOrder(){
        $params = input('post.');
        $type_id = $params['type_id'];
        //$user_ids = explode(',',$params['user_ids']);
        $user_ids = [];
        //$course_ids = explode(',',$params['course_ids']);
        $course_ids = $params['course_ids'];
        $type_id==2 && $subject_ids = explode(',',$params['subject_ids']);
        $payType = $params['payType']; //支付类型
        $payFrom = $params['payFrom']; //支付方式
        if(empty($user_ids)) return MBISReturn("请选择账号");
        if(count($user_ids)>10) return MBISReturn("最多选择10个账号");
        if($type_id==1 && empty($course_ids)) return MBISReturn("请选择课程");
        if($type_id==2 && empty($course_ids) && empty($subject_ids)) return MBISReturn("请选择课程/科目");
        if(empty($payFrom)) return MBISReturn("请选择支付方式");
        $rs_users = Db::name('users')->where(['userId'=>['in',$user_ids]])->select();
        $tmp_users = [];
        foreach($rs_users as $v):
            $tmp_users[$v['userId']] = $v;
        endforeach;
        
        //dump($rs_users);exit;
        foreach($user_ids as $userId):
        $params = [];
        $params['accesstoken'] = $userId;
        $params['userId'] = $userId;
        $params['jump_type'] = $type_id;
        $params['channelType'] = 1;
        $params['nodelcart'] = 1;
        $params['platform'] = 1;
        $course_data = [];
        //课程处理
        if(!empty($course_ids)):
            foreach($course_ids as $k=>$course_id):
                $cartId = $type_id.$course_id.$k.'0';
                //学历 >> 课程
                $type_id==1 && $course_data[$cartId] = array (
                  'cartId' => $cartId,
                  'type_id' => $type_id,
                  'userId' => $userId,
                  'course_id' => $course_id,
                  'subject_id' => 0,
                  'cartNum' => 1,
                  'extend_data' => '',
                  'is_full_pay' => '1',
                  'add_deposit_price' => 0,
              );
              //技能 >> 课程
              $type_id==2 && $course_data[$cartId] = array (
                  'cartId' => $cartId,
                  'type_id' => $type_id,
                  'userId' => $userId,
                  'course_id' => $course_id,
                  'subject_id' => 0,
                  'cartNum' => 1,
                  'extend_data' => '',
                  'subjectList' => model('CourseSubject')->get_subject_full_val($course_id),
                  'extend_data' => serialize(['subject_ids'=>model('CourseSubject')->get_subject_ids($course_id)]),
                  'add_deposit_price' => 0,
              );
            endforeach;
        endif;
        //课目处理
        if(!empty($subject_ids)):
            foreach($subject_ids as $k_s=>$subject_id):
              if(empty($subject_id)) continue;
              //技能 >> 课目
              $cartId = $type_id.$subject_id.$k_s.'1';
              $type_id==2 && $course_data[$cartId] = array (
                  'cartId' => $cartId,
                  'type_id' => $type_id,
                  'userId' => $userId,
                  'course_id' => 0,
                  'subject_id' => $subject_id,
                  'cartNum' => 1,
                  'extend_data' => '',
                  'subjectList' => 
                  array (
                    $subject_id => 1
                  ),
                  'add_deposit_price' => 0,
              );
            endforeach;
        endif;
        $params['cartData'] = $course_data;
        $params['orderData'] = array (
            'orderInfo' =>
            array (
              'discountMoney' => 0,
              'totalMoney' => 0,
              'realTotalMoney' => 0,
              'realPayMoney' => 0,
              'name' => $tmp_users[$userId]['trueName'],
              'mobile' => $tmp_users[$userId]['userPhone'],
              'idcard' => $tmp_users[$userId]['idcard'],
              'entry_time' => time(),
            ),
            'paymentInfo' =>
            array (
              $payType => $payFrom,
            ),
          );
  $params['nodelcart'] = 1;
  //dump($_POST);exit;
$res = model('common/orders')->getApiCreateOrder(99,$params,$tmp_users[$userId]);
//dump($res);exit;
endforeach;
    if($res['status']==1){ return MBISReturn("提交成功",1);}
    else{return MBISReturn($res['msg'],-1);}
    }
    
    /**
     * 创建订单
     */
    public function submit2(){
        $params = input('post.');
        $type_id = $params['type_id'];
        $user_ids = explode(',',$params['user_ids']);
        $course_ids = explode(',',$params['course_ids']);
        $type_id==2 && $subject_ids = explode(',',$params['subject_ids']);
        $payType = $params['payType']; //支付类型
        $payFrom = $params['payFrom']; //支付方式
        if(empty($user_ids)) return MBISReturn("请选择账号");
        if(count($user_ids)>10) return MBISReturn("最多选择10个账号");
        if($type_id==1 && empty($course_ids)) return MBISReturn("请选择课程");
        if($type_id==2 && empty($course_ids) && empty($subject_ids)) return MBISReturn("请选择课程/科目");
        if(empty($payFrom)) return MBISReturn("请选择支付方式");
        $rs_users = Db::name('users')->where(['userId'=>['in',$user_ids]])->select();
        $tmp_users = [];
        foreach($rs_users as $v):
            $tmp_users[$v['userId']] = $v;
        endforeach;
        
        //dump($rs_users);exit;
        foreach($user_ids as $userId):
        $params = [];
        $params['accesstoken'] = $userId;
        $params['userId'] = $userId;
        $params['jump_type'] = $type_id;
        $params['channelType'] = 1;
        $params['nodelcart'] = 1;
        $params['platform'] = 1;
        $course_data = [];
        //课程处理
        if(!empty($course_ids)):
            foreach($course_ids as $k=>$course_id):
                $cartId = $type_id.$course_id.$k.'0';
                //学历 >> 课程
                $type_id==1 && $course_data[$cartId] = array (
                  'cartId' => $cartId,
                  'type_id' => $type_id,
                  'userId' => $userId,
                  'course_id' => $course_id,
                  'subject_id' => 0,
                  'cartNum' => 1,
                  'extend_data' => '',
                  'is_full_pay' => '1',
                  'add_deposit_price' => 0,
              );
              //技能 >> 课程
              $type_id==2 && $course_data[$cartId] = array (
                  'cartId' => $cartId,
                  'type_id' => $type_id,
                  'userId' => $userId,
                  'course_id' => $course_id,
                  'subject_id' => 0,
                  'cartNum' => 1,
                  'extend_data' => '',
                  'subjectList' => model('CourseSubject')->get_subject_full_val($course_id),
                  'extend_data' => serialize(['subject_ids'=>model('CourseSubject')->get_subject_ids($course_id)]),
                  'add_deposit_price' => 0,
              );
            endforeach;
        endif;
        //课目处理
        if(!empty($subject_ids)):
            foreach($subject_ids as $k_s=>$subject_id):
              if(empty($subject_id)) continue;
              //技能 >> 课目
              $cartId = $type_id.$subject_id.$k_s.'1';
              $type_id==2 && $course_data[$cartId] = array (
                  'cartId' => $cartId,
                  'type_id' => $type_id,
                  'userId' => $userId,
                  'course_id' => 0,
                  'subject_id' => $subject_id,
                  'cartNum' => 1,
                  'extend_data' => '',
                  'subjectList' => 
                  array (
                    $subject_id => 1
                  ),
                  'add_deposit_price' => 0,
              );
            endforeach;
        endif;
        $params['cartData'] = $course_data;
        $params['orderData'] = array (
    'orderInfo' => 
    array (
      'discountMoney' => 0,
      'totalMoney' => 0,
      'realTotalMoney' => 0,
      'realPayMoney' => 0,
      'name' => $tmp_users[$userId]['trueName'],
      'mobile' => $tmp_users[$userId]['userPhone'],
      'idcard' => $tmp_users[$userId]['idcard'],
      'entry_time' => time(),
    ),
    'paymentInfo' => 
    array (
      $payType => $payFrom,
    ),
  );
  $params['nodelcart'] = 1;
  //dump($_POST);exit;
$res = model('common/orders')->getApiCreateOrder(99,$params,$tmp_users[$userId]);
//dump($res);exit;
endforeach;
    if($res['status']==1){ return MBISReturn("提交成功",1);}
    else{return MBISReturn($res['msg'],-1);}
    }
    
    //获取课程列表
    public function get_course_lists()
    {
        $name = Input("name",'');
        $type_id = Input("type_id/d",1);
        $where['is_shelves'] = 1;
        $type_id>0 && $where['type_id'] = $type_id;
        !empty($name) && $where['name'] = ['like',"%{$name}%"];
        $rs_course = model('course')->where($where)->field('school_id,major_id,course_id,name,offers_price')->select();
        foreach($rs_course as &$v)
        {
            $v['school_name'] = model('school')->get_name($v['school_id']);
            $v['major_name'] = model('major')->get_name($v['major_id']);   
        }
        return $rs_course;
    }
    
    //获取科目列表
    public function get_subject_lists()
    {
        $name = Input("name",'');
        $type_id = Input("type_id/d",1);
        $where['is_shelves'] = 1;
        $type_id>0 && $where['subject_type_id'] = $type_id;
        !empty($name) && $where['name'] = ['like',"%{$name}%"];
        $rs_subject = model('subject')->where($where)->field('school_id,major_id,subject_id,name,sale_price')->select();
        foreach($rs_subject as &$v)
        {
            $v['school_name'] = model('school')->get_name($v['school_id']);
            $v['major_name'] = model('major')->get_name($v['major_id']);   
        }
        return $rs_subject;
    }
    
    /**
    * 查找用户
    */
    public function userQuery(){
        $name = input('post.loginName');
        return model('admin/users')->field(['userId','loginName'])->where(['userType'=>0,'loginName'=>['like',"%$name%"]])->select();
    }
    
    public function getUserData()
    {
        $data = input('get.');
        $field_type = $data['field_type'];
        $val = $data['val'];
        $where = [];
        switch($field_type){
            case 'name':
                $where = ['trueName'=>$val];
                break;  
            case 'mobile':
                $where = ['userPhone'=>$val];
                break; 
            case 'idcard':
                $where = ['idcard'=>$val];
                break;   
        }
        $rs = model('admin/users')->where($where)->find();
        return MBISAPIReturn(MBISReturn('获取数据成功',1,$rs));
    }
    
    /**
     * 导入数据
    */
    public function toImport(){
        $this->assign('assign_get',input('get.'));
        return $this->fetch("import");
    }
    public function import()
    {
        if(empty($_FILES['importFile']['tmp_name'])) exit('请选择文件<a href="javascript:history.back()">返回</a>');
        $support_extension = ['xlsx','xls'];
        $pathinfo = pathinfo($_FILES['importFile']['name']);
        if(!in_array(strtolower($pathinfo['extension']),$support_extension)) exit('只支持'.implode('、',$support_extension).'文件<a href="javascript:history.back()">返回</a>');
        $path = $_FILES['importFile']['tmp_name'];
        $post = input('post.');
        $post['key']=='xj' && $data = model('api/imports')->importUsers($path);
        $post['key']=='bm' && $data = model('api/imports')->importEntrys($path);
        $repeat_data = '';
        if(!empty($data['repeat_data']))
        {
            $repeat_data .= "<h3>{$data['nofinish_import_num']}条重复数据列表(不做导入)</h3><ol>";
            foreach($data['repeat_data'] as $v):
                $order_data = "";
                $post['key']=='bm' && $order_data .= "<br>订单号：{$v['orderNo']}&nbsp;&nbsp;课程名称：{$v['course_name']}";
                $repeat_data .= "<li style=\"padding-bottom:10px;\">姓名：{$v['name']}&nbsp;&nbsp;身份证：{$v['idcard']}{$order_data}</li>";   
            endforeach;
            $repeat_data .= '</ol>';
        }
        exit($data['finish_import_num'].'条数据导入成功'.$repeat_data.'<p style="text-align:center"><a href="javascript:history.back()">返回</a></p>');
    }
}
