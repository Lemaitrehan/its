<?php
namespace application\admin\controller;
use application\admin\model\StudentFeeLog as M;
/**
 * 付费记录管理控制器
 */
class Studentfeelog extends Base{
	
    //学历
    public function indexEducation(){
        if( request()->isAjax() ){
            //查找专业下面的科目
             $school_id   = input('post.school_id');
             $majorObj    = new \application\admin\model\Major();
             $where = "FIND_IN_SET($school_id,school_ids)";
             $arrMajor    = $majorObj->getMajor( $where );
             return $arrMajor;
        }
        //学校列表(学历类)
        $school = new \application\admin\model\School();
        $lists_school_edu = $school->get_lists_edu();
        $this->assign("school",$lists_school_edu);
        $m = new M();
        //$list = $m->get_info_list();
        $this->assign('type',1);
        return $this->fetch("list1");
    }
    //技能
    public function indexSkill(){
        if( request()->isAjax() ){
            //查找专业下面的科目
             $school_id   = input('post.school_id');
             $majorObj    = new \application\admin\model\Major();
             $where = "FIND_IN_SET($school_id,school_id)";
             $arrMajor    = $majorObj->getMajorSkill( $where );
             return $arrMajor;
        }
        //学校列表(学历类)
        $school = new \application\admin\model\School();
        $lists_school_edu = $school->get_lists_skill();
        $this->assign("school",$lists_school_edu);
        $m = new M();
        //$list = $m->get_info_list();
        $this->assign('type',2);
        return $this->fetch("list1");
    }
    
    //查看历史记录
    public function paymentRecords(){
        $m = new M();
        return $m->paymentRecords(input('post.userId'));
    }
    
    //学历查看历史记录
    public function paymentRecords2(){
        $m = new M();
        $rs = $m->paymentRecords2(input('get.userId'),input('get.courseBn'));
        $this->assign('feeList',$rs);
        return $this->fetch("feeList");
        //return $m->paymentRecords2(input('get.userId'),input('get.courseBn'));
    }
    
    //学历
    public function pageQuery1(){
        $m = new M();
        return $m->pageQuery( 1, 1 );
    }
    //技能
    public function pageQuery2(){
        $m = new M();
        return $m->pageQuery( 2, 1 );
    }
    /*
    * 获取数据
    */
    public function getlists(){
        $m = new M();
        return $m->getById(Input("id/d",0));
    }
    /**
     * 跳去编辑页面
     */
    public function toEditEducation(){
        $m = new M();
        $rs = $m->getById(Input("id/d",0));
        $userlist = $m->get_user_lists(1); //学历类学员列表
        $this->assign("object",$rs);
        $this->assign("userlist",$userlist);
        $this->assign('type',1);
        return $this->fetch("edit-edu");
    }
    /**
     * 跳去编辑页面
     */
    public function toEditSkill(){
        $m = new M();
        $rs = $m->getById(Input("id/d",0));
        $userlist = $m->get_user_lists(2);  //技能类学员列表
        $this->assign("object",$rs);
        $this->assign("userlist",$userlist);
        $this->assign('type',2);
        return $this->fetch("edit");
    }
    public function toEditPublic(){
        $m = new M();
        $rs = $m->getById(Input("id/d",0));
        $userlist = $m->get_user_lists();
        $this->assign("object",$rs);
        $this->assign("userlist",$userlist);
        return $this->fetch("edit");
    }
    
    /**
     * 跳去某学员付费记录信息详情页
     */
    public function toDetail(){
        return $this->fetch("detail");
    }
    /**
     * 新增
     */
    public function addEducation(){
        $m = new M();
        return $m->add();
    }
    public function addSkill(){
        $m = new M();
        return $m->add();
    }
    /**
    * 修改
    */
    public function editEducation(){
        $m = new M();
        return $m->edit();
    }
    public function editSkill(){
        $m = new M();
        return $m->edit();
    }
    /**
     * 删除
     */
    public function delEducation(){
        $m = new M();
        return $m->del();
    }
    public function delSkill(){
        $m = new M();
        return $m->del();
    }
    /**
     * ajax获取用户基本信息
     */
    public function getInfo(){
        $m = new M();
        return $m->getInfo();
    }
    //缴费明细管理
    public function feeDetail1(){
        if( request()->isAjax() ){
            //查找专业下面的科目
            $school_id   = input('post.school_id');
            $majorObj    = new \application\admin\model\Major();
            $where = "FIND_IN_SET($school_id,school_ids)";
            $arrMajor    = $majorObj->getMajor( $where );
            return $arrMajor;
        }
        $m = new M();
        //$list = $m->get_info_list();
        $this->assign('userId',input('get.userId'));
        $this->assign('courseBn',input('get.courseBn'));
        $this->assign('type',1);
        //学校列表(学历类)
        $school = new \application\admin\model\School();
        $lists_school_edu = $school->get_lists_edu();
        $this->assign("school",$lists_school_edu);
        return $this->fetch("feeDetailList");
    }
    public function feeDetail2(){
        if( request()->isAjax() ){
            //查找专业下面的科目
            $school_id   = input('post.school_id');
            $majorObj    = new \application\admin\model\Major();
            $where = "FIND_IN_SET($school_id,school_id)";
            $arrMajor    = $majorObj->getMajorSkill( $where );
            return $arrMajor;
        }
        $m = new M();
        //$list = $m->get_info_list();
        $this->assign('userId',input('get.userId'));
        $this->assign('courseBn',input('get.courseBn'));
        $this->assign('type',2);
        //学校列表(学历类)
        $school = new \application\admin\model\School();
        $lists_school_edu = $school->get_lists_skill();
        $this->assign("school",$lists_school_edu);
        return $this->fetch("feeDetailList");
    }
    public function feeDetailQuery1(){
        $m = new M();
        $this->assign('type',1);
        return $m->feeDetailQuery( 1, 1  );
    }
    public function feeDetailQuery2(){
        $m = new M();
        $this->assign('type',2);
        return $m->feeDetailQuery( 2, 1  );
    }
    
    /**
     * 检查导入数据
    */
    public function checkImport(){
        $get = input('get.');
        empty($get['type_id']) && $get['type_id']='1';
        empty($get['key']) && $get['key']='jfhz';
        $this->assign('assign_get',$get);
        $post = $get;
        //if(!empty($_FILES)):
            /*if(empty($_FILES['importFile']['tmp_name'])) exit('请选择文件<a href="javascript:history.back()">返回</a>');
        $support_extension = ['xlsx','xls'];
        $pathinfo = pathinfo($_FILES['importFile']['name']);
        if(!in_array(strtolower($pathinfo['extension']),$support_extension)) exit('只支持'.implode('、',$support_extension).'文件<a href="javascript:history.back()">返回</a>');
        $path = $_FILES['importFile']['tmp_name'];
        $post = input('post.');*/
        $replace_keys = array();
        if($post['key']=='jfhz'):
        $path = 'student_edu';
        $ck_table_name='users';
        $ck_fields = array(
            //'序号'=>'bn',
            '学员编号'=>'student_no',
            '学员姓名'=>'trueName',
            '身份证号'=>'idcard',
        );
        /*$replace_keys = array(
            'A' => 'student_no',
            'B' => 'trueName',
            'C' => 'idcard',
            'D' => 'receiptCate',
            'E' => 'exam_type',
            'F' => 'school_name',
            'G' => 'level_name',
            'H' => 'major_name',
            'I' => 'studyStatus',
            'J' => 'course_bn',
            'K' => 'course_name',
            'L' => 'price',
            'M' => 'discount_price',
            'N' => 'deal_price',
            'O' => 'total_price',
            'P' => 'wait_price',
            'Q' => 'arre_type',
        );*/
        $title = "缴费汇总(检测客户)";
        elseif($post['key']=='jfmx'):
        $ck_table_name='users';
        $ck_fields = array(
            //'序号'=>'bn',
            '学员编号'=>'student_no',
            '学员姓名'=>'trueName',
            '身份证号'=>'idcard',
        );
        /*$replace_keys = array(
            'B' => 'student_no',
            'C' => 'trueName',
            'D' => 'idcard',
        );*/
        $title = "缴费明细(检测客户)";
        elseif($post['key']=='jfmx2'):
        $ck_table_name='student_edu';
        						
        $ck_fields = array(
            //'序号'=>'bn',
            '报考类型'=>'exam_type',
            '报读院校'=>'school_name',
            '层次'=>'level_name',
            '报读专业'=>'major_name',
            '学习形式'=>'studyStatus',
            '课程编码'=>'course_bn',
            '课程名称'=>'course_name',
        );
        /*$replace_keys = array(
            'F'=>'exam_type',
            'G'=>'school_name',
            'H'=>'level_name',
            'I'=>'major_name',
            'J'=>'studyStatus',
            'K'=>'course_bn',
            'L'=>'course_name',
        ); */
        $title = "缴费明细(检测课程)";
        endif;
        $ck_data = model('admin/imports')->check_import_data($path,$ck_table_name,$ck_fields,$replace_keys);
        //dump($ck_data);exit;
        $html = model('admin/imports')->show_data($ck_data,$ck_fields,$title);
        $this->assign('html',$html);
        //else:
            return $this->fetch("ckimport");
        //endif;
    }
    
    /**
     * 导入数据
    */
    public function toImport(){
        $this->assign('assign_get',input('get.'));
        return $this->fetch("import");
    }
    /*public function import()
    {
        if(empty($_FILES['importFile']['tmp_name'])) exit('请选择文件<a href="javascript:history.back()">返回</a>');
        $support_extension = ['xlsx','xls'];
        $pathinfo = pathinfo($_FILES['importFile']['name']);
        if(!in_array(strtolower($pathinfo['extension']),$support_extension)) exit('只支持'.implode('、',$support_extension).'文件<a href="javascript:history.back()">返回</a>');
        $path = $_FILES['importFile']['tmp_name'];
        $mdl = 'student_bill_fee_log';
        $params['path'] = $path;
        $params['fields'] = ['student_no','name','idcard','school_name','level_name','major_name'
        ,'stu_price','receipt_time','income','receipt_no','account_name','bill_type','bill_way','bill_name','sign_name','remark'];
        $params['mdl'] = $mdl;
        $result = true;
        $data = model('common/Import')->get_xls_data($params);
        $repeat_data = '';
        if(!empty($data['repeat_data']))
        {
            $repeat_data .= '<h3>收据号码重复数据列表(不做导入)</h3><ul>';
            foreach($data['repeat_data'] as $v):
                $repeat_data .= '<li>收据号码：'.$v['receipt_no'].'</li>';   
            endforeach;
            $repeat_data .= '</ul>';
        }
        $result = model('common/Import')->put_2_tb('student_bill_fee_log',$data);
        if(!empty($result)) exit(count($data['data']).'条数据导入成功'.$repeat_data);
        if(empty($result)) exit('导入数据失败');
    }*/
    public function import()
    {
        if(empty($_FILES['importFile']['tmp_name'])) exit('请选择文件<a href="javascript:history.back()">返回</a>');
        $support_extension = ['xlsx','xls'];
        $pathinfo = pathinfo($_FILES['importFile']['name']);
        if(!in_array(strtolower($pathinfo['extension']),$support_extension)) exit('只支持'.implode('、',$support_extension).'文件<a href="javascript:history.back()">返回</a>');
        $path = $_FILES['importFile']['tmp_name'];
        $post = input('post.');
        $post['key']=='jfhz' && $data = model('admin/imports')->importFees2016($path);
        $post['key']=='jfmx' && $data = model('admin/imports')->importPayments2016($path);
        $repeat_data = '';
        if(!empty($data['repeat_data']))
        {
            $post['key']=='jfhz' && $item_name='学员+(课程编码/层次)';
            $post['key']=='jfmx' && $item_name='单据号';
            $repeat_data .= "<h3>{$item_name}重复数据({$data['nofinish_import_num']}条)，不做导入</h3><ol>";
            foreach($data['repeat_data'] as $v):
                $order_data = "";
                //$post['key']=='bm' && $order_data .= "<br>订单号：{$v['orderNo']}&nbsp;&nbsp;课程名称：{$v['course_name']}";
                $show_name = '课程编码';
                ($post['key']=='jfhz' && $v['type']==2) && $show_name='层次';
                $post['key']=='jfhz' && $repeat_data .= "<li style=\"padding-bottom:10px;\">{$show_name}：{$v['course_bn']}&nbsp;&nbsp;身份证：{$v['idcard']}&nbsp;&nbsp;学员名称：{$v['name']}{$order_data}</li>"; 
                 $post['key']=='jfmx' && $repeat_data .= "<li style=\"padding-bottom:10px;\">单据号：{$v['receiptNo']}&nbsp;&nbsp;身份证：{$v['idcard']}&nbsp;&nbsp;学员名称：{$v['name']}{$order_data}</li>";  
            endforeach;
            $repeat_data .= '</ol>';
        }
        if($data['finish_import_num']==0) exit($repeat_data.'<p style="text-align:center"><a href="javascript:history.back()">返回</a></p>');
        if($data['finish_import_num']>0) exit($data['finish_import_num'].'条数据导入成功'.$repeat_data.'<p style="text-align:center"><a href="javascript:history.back()">返回</a></p>');
    }
    
    //数据导出
    function exportxl(){
        set_time_limit(0);
        $m = new M();
        $filename = '学员缴费信息';
        $data = array();
        $data[] = array(
            '学员ID','学员编号','学员姓名','身份证号','收款类别','报考类型','报读院校',
            '层次','报读专业','学习形式','课程编码','课程名称','标准学费','优惠金额','应收学费总额',
            '累计已收学费总额','待收学费总额','是否欠费'
        );
        $res  = $m->pageQuery(1,1);//
        foreach($res['Rows'] as $key => $v ){
            $data[] = array(
                 $v['userId'],
                 $v['student_no'],
                 $v['trueName'],
                 $v['idcard'],
                 $v['receiptCate'],
                 $v['exam_type'],
                 $v['school_name'],
                 $v['level_name'],
                 $v['major_name'],
                 $v['studyStatus'],
                 $v['course_bn'],
                 $v['course_name'],
                 $v['price'],
                 $v['discount_price'],
                 $v['deal_price'],
                 $v['total_price'],
                 $v['wait_price'],
                 $v['arre_type'],
            );    
        }
        $data = array_values($data);
        array_excel($filename, $data);
        exit;
    }
    
    //数据导出
    function exportxlmx(){
        set_time_limit(0);
        $m = new M();
        $filename = '学员缴费明细管理';
        $data = array();
        $data[] = array(
            '收款校区','学员ID','学员编号','学员姓名','身份证号','收款类别','报考类型',
            '报读院校','层次','报读专业','学习形式','课程编码','课程名称','收入','单据日期',
            '收据号码','缴费类型','缴费方式',
        );
        $res  = $m->feeDetailQuery(1);
        foreach($res['Rows'] as $key => $v ){
            $data[] = array(
                $v['receiptSchool'],
                $v['userId'],
                $v['student_no'],
                $v['trueName'],
                $v['idcard'],
                $v['receiptCate'],
                $v['exam_type'],
                $v['school_name'],
                $v['level_name'],
                $v['major_name'],
                $v['studyStatus'],
                $v['course_bn'],
                $v['course_name'],
                $v['money'],
                $v['receiptDate'],
                $v['receiptNo'],
                $v['pay_type'],
                $v['pay_name'],
            );
        }
        $data = array_values($data);
        array_excel($filename, $data);
        exit;
    }
    
    //数据检查
    public function check_history_data(){
        $filepath = 'check_data.xlsx';
        $exception_data = array();
        $error_data = array();
        $replace_keys = array(
            'B' => 'student_no',
            'K' => 'course_bn',
            'M' => 'money',
        );
        $data = model('admin/imports')->make_import_data($filepath,$replace_keys);
        $user_data = model('admin/imports')->getCustomLists($data);
        //dump($user_data);exit;
        foreach($data as $v):
            if(empty($user_data[$v['student_no']])) {
                $exception_data[] = $v;
                //echo '<p>'.$v['student_no'].'异常</p>';
                continue;
            }
            $filter = array(
                //'userId' => $user_data[trim($v['student_no'])],
                'userId' => $user_data[$v['student_no']],
                'course_bn' => $v['course_bn'],
                //'money' => str_replace( array('(',')'),'',$v['money'] ),
                'money' => $v['money'],
            );
            $result = model('common/payments')->where($filter)->find();
            //echo '<p>'.implode(',',$filter).'</p>';
            if(empty($result)):
                $filter2 = array(
                    'userId' => $user_data[$v['student_no']],
                    'course_bn' => $v['course_bn'],
                );
                $money2 = model('common/payments')->where($filter2)->value('money');
                $filter['money2'] = $money2;
                $filter['bn'] = $v['bn'];
                $error_data[] = $filter;
            else:
                //echo '<p><b>金额正确</b></p>';
            endif;
        endforeach;
        echo '<p><b>缺少学籍数据</b></p>';
        $arr = array();
        foreach($exception_data as $v):
           echo $v['bn'].'<br>'; 
        endforeach;
        //dump($exception_data);
        echo '<p><b>金额对不上</b></p>';
        foreach($error_data as $v):
           echo $v['bn'].'<br>'; 
        endforeach;
        //dump($error_data);
        //dump($user_data);   
    }
    
    
}
