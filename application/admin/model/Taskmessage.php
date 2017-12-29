<?php
namespace application\admin\model;
/**
 *任务管理
 */
use think\Model;
use think\Db;
class Taskmessage extends Model {
    //分页
    public function index(){
        
        $join = array(
            array('task_obj b','b.req_id = a.id','left'),
            array('employee u','u.staff_id=b.from_person_id','left'),//接收人
            array('employee u1','u1.staff_id=a.create_person_id','left'),
            array('employee u2','u2.staff_id=a.auditor','left'),
        );
        $person_id = session('MBIS_STAFF')->staffId;
        
        //如果是垃圾
        if( input('is_trash') ){
            $is_trash = input('is_trash');
            $where['b.trash'] = $is_trash;
        }else{
            $where['b.trash'] = 0;
        }
        
        //收件与发件is_send_re=1 收件
        if( input('is_send_re') ){
            $is_send_re = input('is_send_re');
            //接收
            if($is_send_re==1){
                $where['b.from_person_id']   = $person_id;
            //发送    
            }elseif($is_send_re==2){
                $where['a.create_person_id'] = $person_id;
             //审核   
            }else{
                $where['a.auditor'] = $person_id;
            }
            
        }
        if( input('status') ){
            $where['b.status'] = input('status');
        }
        
        $field = 'b.id,a.name,b.trash,b.status,
                  if(b.status =\'2\',\'完成\',if(b.status=0,\'未接收\',\'未完成\')) as statusText,
                  FROM_UNIXTIME(a.start_time) as start_time,
                  FROM_UNIXTIME(a.stop_time) as stop_time,
                  FROM_UNIXTIME(a.create_person_time) as create_person_time,
                  u2.name as auditorName
                 ';
        if($is_send_re == 1){
            $field.=',u1.name as trueName';
        }else{
            $field.=',u.name as trueName';
        }
        $res = db::name('task')->alias('a')
                                ->join($join)
                                ->where($where)
                                ->field($field)
                                ->order('b.update_time DESC')
                                ->paginate(input('post.pagesize/d'))
                                ->toArray();
        foreach ($res['Rows'] as $key => $v){
            $res['Rows'][$key]['statusText'] = $v['status']=='2'? "<span style='color:red;'>".$v['statusText']."</span>" : $v['statusText'];
        }
        return $res;
    }
    
    //查找未读消息
    
    public function smsTotal(){
        
        $join = array(
            array('task_obj b','b.req_id = a.id','left')
        );
        $person_id = session('MBIS_STAFF')->staffId;
        $where     = array();
        $where['b.from_person_id'] = $person_id;
        $where['b.status']          = 0;
        
        $field = 'b.id';
        $res = db::name('task')->alias('a')
                                ->join($join)
                                ->where($where)
                                ->field($field)
                                ->select();
        return count($res);
    }
    
    //添加任务
    public function addData(){
        
        $name        = input('name');
        $auditor     = input('auditor');
        $userIds     = input('userIds');
        $start_time  = input('start_time');
        $stop_time   = input('stop_time');
        $content     = input('content');
        if( !($name && $userIds && $stop_time && $auditor  && $content && $userIds ) ){
            return '表单输入框必填！！！';
        }
        $person_id = session('MBIS_STAFF')->staffId;
        $time      = time();
        $data = array(
            'name'                =>$name,
            'content'             =>$content,
            'create_person_id'    =>$person_id,
            'create_person_time'  =>$time,
            'update_person_id'    =>$person_id,
            'update_person_time'  =>$time,
            'start_time'          =>strtotime( $start_time ),
            'stop_time'           =>strtotime( $stop_time ),
            'auditor'             =>$auditor
         );
        Db::startTrans();
        try{
            //短信基础表
            $taskObj = db::name('task');
            $taskObj->insert($data);
            $id = $taskObj->getLastInsID();
            //短信对象表
            $arrUserId = explode(',', $userIds);
          
            $data1 = array(); 
            foreach($arrUserId as $key => $v ){
                $data1[] = array(
                    'req_id' => $id,
                    'from_person_id'=> $v,
                    'update_time'=> $time,
                );
            }
            $ids= db::name('task_obj')->insertAll($data1);
            // 提交事务
            Db::commit();
            return ['status'=>'1','msg'=>'任务创建成功！！！'];
        } catch (\Exception $e) {
                   // 回滚事务
            Db::rollback();
            return ['status'=>0,'msg'=>'任务创建失败！！！'];
        }
        
        
    }
    
    
    
    
    //完成
    function complete(){
        $id     = input('post.id');
        $status = input('post.status');
        return $aff_id = db('task_obj')->where('id='.$id)->update(array('status'=>$status,'update_time'=>time()));
    }
    
    
    
    //删除垃圾
    function delData(){
        $id = input('post.id');
        return $aff_id = db('task_obj')->where('id='.$id)->update(array('trash'=>1));
    }
    
    //查找没有完成的任务
    function unfinished(){
            $join = array(
                array('task_obj b','b.req_id = a.id','left')
            );
            $person_id = session('MBIS_STAFF')->staffId;
            $time      = time();
            $where     = array();
            $where['b.from_person_id'] = $person_id;
            $where['b.status']         = ['neq',2];
            
            
            $where1['a.stop_time']      = ['elt',$time];
            $where = ' ( b.from_person_id ='.$person_id.' and b.status !=2   )
                        AND ( (a.start_time <='.$time.' AND a.stop_time>='.$time.')
                                OR  a.stop_time <= '.$time.'  )'  
                            ;
            $field = 'a.content,
                     FROM_UNIXTIME(a.start_time) as start_time,
                     FROM_UNIXTIME(a.stop_time) as stop_time
                     ';
            $res = db::name('task')->alias('a')
                                   ->join($join)
                                   ->where($where)
                                   ->field($field)
                                   ->select();
            return $res;
    }
    
    
    //查找所有的员工
    public function getUser($no_page='0'){
        $where = array();
        //部门
        if( input('department') ){
            $department_id = input('department');
            $where['u.department_id'] = ['=',$department_id];
        }
        //岗位
        if( input('employee_type') ){
            $employee_type_id = input('employee_type');
            $where['u.employee_type_id'] = ['=',$employee_type_id];
        }
        //
        if( input('search_title') ){
        
            $search_title = input('search_title');
            $search_word  = input('search_word');
            switch ($search_title && $search_word){
                //员工名称
                case '1':
                    $where['u.trueName'] = ['like','%'.$search_word.'%'];
                  break;  
                //员工编号
                case '2':
                    $where['u.student_no'] = ['like','%'.$search_word.'%'];
                  break;
                //身份证号
                case '3':
                    $where['u.idcard'] = ['like','%'.$search_word.'%'];
                  break;
                //手机号   
                case '4':
                    $where['u.userPhone'] = ['like','%'.$search_word.'%'];
                  break;
            }
        }
        $where['u.staff_id'] = ['>',0];
        
        $join = array(
                  array('department d','d.department_id=u.department_id','left'),
                  array('employee_type gw','gw.employee_type_id=u.employee_type_id','left'),
        );
        $field = 'u.employee_id,u.staff_id,u.name,u.employee_no,u.mobile,d.name as bm_name,gw.name as gw_name';
   
        if($no_page){
            $search_name = input('search_name');
            $where['u.name'] = ['like','%'.$search_name.'%'] ;
            $field = 'u.staff_id as id,u.name';
            $res = db::name('employee')->alias('u')
                                    ->join($join)
                                    ->where($where)
                                    ->field($field)
                                    ->select();
            return $res;
        }else{
            $res = db::name('employee')->alias('u')
                                    ->join($join)
                                    ->where($where)
                                    ->field($field)
                                    ->paginate(input('post.pagesize/d'))
                                    ->toArray();
        }
        $userIds = input('get.userIds');//用户选中的用户
        $arrUserIds = array();
        if($userIds){
            $arrUserIds = explode(',', $userIds);
        }
        
        foreach($res['Rows'] as $Key => $v){
            $getSchoolType['Rows'][$Key]['name'] = $v['name'].'('.$v['employee_no'].')';
            if( in_array( $v['staff_id'], $arrUserIds)){
                $res['Rows'][$Key]['checkbox']  = '<input id="ck_"'.$v['staff_id'].' checked="checked" type="checkbox" name="chk" value="'.$v['staffs_id'].'">';
            }else{
                $res['Rows'][$Key]['checkbox']  = '<input id="ck_"'.$v['staff_id'].' type="checkbox" name="chk" value="'.$v['staff_id'].'">';
        
            }
        
        }
        return $res;
    }
    
    //查找岗位
    public function employee_type($department_id){
         $arr = db::name('employee_type')->where('department_id ='.$department_id)->field('employee_type_id,name')->select();
         return $arr;   
    }
    
    //查找部门
    function getDepartment(){
        return db::name('department')->field('department_id,name')->select();
    }
    
}