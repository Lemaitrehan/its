<?php

namespace application\common\model;

use think\Db;
use think\config;
use think\Exception;

class ReservationClient extends Base
{
    //预约单状态
    protected $status = [
        '1' => '已约访',
        '2' => '逾期未到访',
        '11' => '已分配',
        '20' => '已到访',
    ];
    protected $type = [
        '1' => '学历类',
        '2' => '技能类',
        '3' => '管理类'
    ];
    //分页数
    protected $pageSize = 10;

    /**
     * 获取用户预约单信息
     * @param $school
     * @param $params
     * @return bool
     */
    public function getStudentList($school, $params)
    {
        $order = '';
        $where = "b.campus_id = '$school'";
        //$where .= ' and b.id in (select max(id) from ' . $this->getTableName('reservation_list') . ' as cc where cc.req_id = b.req_id )';
        //客户名称或手机号码
        if (isset($params['mobile'])) {
            $where .= " and a.client_name|a.client_cellphone = '" . $params['mobile'] . "'";
        }
        //身份证
        if (isset($params['idcards'])) {
            $where .= " and c.id_card = '" . $params['idcards'] . "'";
        }
        //电销咨询
        if (isset($params['course_counselor_id'])) {
            $course_counselor_id = $params['course_counselor_id'];
            $where .= " and b.course_counselor_id = '" . $course_counselor_id . "'";
        }
        //记录创建人
        if (isset($params['creat_person_id'])) {
            $where .= " and b.creat_person_id = '" . $params['creat_person_id'] . "'";
        }
        //课程类型
        if (isset($params['type_id'])) {
            $type_id = (int)$params['type_id'];
            $where .= " and a.type_id = '" . $type_id . "'";
        }
        //约访时间=>开始时间
        if (isset($params['starttime'])) {
            $where .= 'and b.appointment_time >= ' . $params['starttime'];
        }
        //约访时间=>结束时间
        if (isset($params['endtime'])) {
            $where .= ' and b.appointment_time <= ' . $params['endtime'];
        }
        //客户id
        if (isset($params['customer_id'])) {
            $customer_id = (int)$params['customer_id'];
            $where .= " and a.req_id = '" . $customer_id . "'";
        }
        //状态
        if (isset($params['status'])) {
            $status = $params['status'];
            $where .= ' and a.status in (' . $status . ")";
        }
        //降序排序
        if (isset($params['order_desc'])) {
            $order = $params['order'] . ' DESC';
        }
        //升序排序
        if (isset($params['order_asc'])) {
            $order = $params['order'] . " ASC";
        }
        //分页
        $page = [];
        $limit = '';
        if (isset($params['page'])) {
            $page_size = isset($params['page_size']) ? $params['page_size'] : $this->pageSize;
            $start = ($params['page'] - 1) * $page_size;
            $limit = "{$start},$page_size";
            $total = $this->alias('a')
                ->join('reservation_list b', 'b.req_id = a.id', 'left')
                ->join('jr_customer c', 'c.id = a.req_id', 'left')
                ->where($where)
                ->group('b.req_id')
                ->count();
            //分页信息处理
            $page = [
                'total' => $total,
                'total_page' => $total / $page_size,
                'page_url' => $params['page'],
                'page_size' => $page_size
            ];
        }
        $fields = [
            'a' => 'a.appointment_num,a.status,a.creat_time,a.creat_person_id,a.update_time as re_update_time,a.update_person_id',
            'b' => 'b.appointment_time,b.id as reservation_id, b.customer_service_id, b.course_counselor_id, b.memo',
            'p' => 'p.name as school',
            'ad' => 'ad.realname',
            'u' => 'u.userPhoto',
            'et' => 'et.name as employee_name',
            'm' => 'm.name as major_name',
            'ad' => 'ad.realname',
            't' => 't.name as type_name',
            'ce' => 'ce.name as course_name,ce.cost_price,ce.sale_price,ce.market_price,ce.offers_price',
            'jc' => 'jc.follow,jc.access_time',
            'c' => 'c.*'

        ];
        $fields = implode(',', $fields);
        $data = $this->alias('a')
            ->join('subject_type t', 't.type_id = a.type_id', 'left')
            ->join('major m', 'm.major_id = a.major_id', 'left')
            ->join('course ce', 'ce.course_id = a.course_id', 'left')
            ->join('reservation_list b', 'b.req_id = a.id and b.status = a.status', 'left')
            ->join('admin ad', 'ad.username = b.course_counselor_id', 'left')
            ->join('admin_department p', 'p.id = b.campus_id', 'left')
            ->join('employee e', 'e.employee_no = ad.username', 'left')
            ->join('users u', 'u.employee_id = e.employee_id', 'left')
            ->join('employee_type et', 'et.employee_type_id = e.employee_type_id', 'left')
            ->join('jr_contact jc', 'jc.id = a.contact_id', 'left')
            ->join('jr_customer c', 'c.id = a.req_id', 'left')
            ->where($where)->field($fields)->limit($limit)
            ->order($order)->select();
        if (empty($data)) {
            return false;
        }
        foreach ($data as $k => $v) {
            $data[$k]['status'] = $this->status[$v['status']];
        }
        if (count($data) == 1) {
            $data = $data[0];
        }
        $result = array('data' => $data, 'page' => $page);
        return $result;
    }

    /**
     * 用户信息录入
     * @param $uid      操作人id
     * @param $school   学校
     * @return array
     */
    public function inputUserDetail($uid, $school)
    {
        $params = input('post.');
        $time = time();
        if (empty($params['user']['mobile']) && empty($params['user']['idcard'])) {
            return MBISReturn('手机号码或身份证必填一项', -1);
        }
        //客户录入数据处理
        $user = $this->checkData($params['user']);
        $user['inputtime'] = date('Y-m-d H:i:s', $time);
        $user['inputuser'] = $uid;
        if (!empty($params['user']['mobile'])) {
            if (!MBISIsPhone($params['user']['mobile'])) {
                MBISReturn('请输入正确的手机号码', '-1');
            }
            $reservation['client_cellphone'] = $params['user']['mobile'];
            $user['mobile_tel'] = $params['user']['mobile'];
            $where['mobile_tel'] = $params['user']['mobile'];
        } elseif (!empty($params['user']['idcard'])) {
            $user['id_card'] = $params['user']['idcard'];
            $where['id_card'] = $params['idcard'];
        }

        //不存在该手机号码或身份证的用户
        if (!$this->table($this->getTableName('jr_customer'))->where($where)->count()) {
            //事务写入数据
            Db::startTrans();
            try {
                $userId = $this->table($this->getTableName('jr_customer'))->insert($user, false, true);
                if (false === $userId) {
                    throw new \Exception('jr_customer 录入失败');
                }
                $reservation = [
                    'req_id' => $userId,
                    'client_name' => $user['name'],
                    'client_cellphone' => $user['mobile_tel'],
                    'status' => '1',
                    'creat_time' => $time,
                    'num' => 1,
                    'creat_person_id' => $uid
                ];
                if (isset($params['reser'])) {
                    $reservation = array_merge($params['reser'], $reservation);
                }
                $req_id = $this->data($reservation)->save();
                if (false === $req_id) {
                    throw new \Exception('reservation_cilent 录入失败');
                }
                $reservation_list = [
                    'req_id' => $this->id,
                    'appointment_time' => $time,  //约访时间
                    'visit_time' => $time,   //到访时间
                    'campus_id' => $school,      //校区id
                    'course_counselor_id' => '',  //校区咨询师
                    'status' => '1',
                    'create_time' => $time,
                    'creat_person_id' => $uid,
                    'num' => 1
                ];
                $n_id = $this->table($this->getTableName('reservation_list'))->insert($reservation_list, false, true);
                if ($n_id) {
                    Db::commit();
                    $obj = new Admin();
                    $counloser = $obj->counloser($school);
                    $rs = [
                        'req_id' => $n_id,
                        'counloser' => $counloser
                    ];
                    return MBISReturn("录入成功", 1, $rs);
                };
            } catch (\Exception $e) {
                Db::rollback();
                return MBISReturn($e->getMessage(), -1);
            }
        }
        return MBISReturn('已存在该手机号码或身份证的用户', -1);
    }

    /**
     * 获取工作情况
     * @param $school
     * @param $uid
     * @param $params
     * @return array
     */
    public function nowCount($school, $uid, $params)
    {
        /*$starttime = $params['starttime'];
        $endtime = $params['endtime'];
        $where = "b.appointment_time >= $starttime AND b.appointment_time <= $endtime AND b.campus_id = $school";*/
        $where = "b.campus_id = $school";
        //当天预约人数
        $appointNum = $this->alias('a')
            ->join('reservation_list b', 'b.req_id = a.id and b.status = a.status')
            ->where($where . " AND a.status in (1,11)")->count();
        //当天到访人数
        $visitNum = $this->alias('a')
            ->join('reservation_list b', 'b.req_id = a.id and b.status = a.status')
            ->where($where . " AND a.status = 20 AND b.creat_person_id = $uid")->count();
        return ['appointNum' => $appointNum, 'visitNum' => $visitNum];
    }

    /**
     * 改变预约单状态
     * @param $uid
     * @param $params
     * @return array
     *
     */
    public function changeStatus($uid, $params)
    {
        $time = time();
        $where['id'] = $params['req_id'];
        $res1 = $this->table($this->getTableName('reservation_list'))->where($where)->find();
        if ($res1) {
            $res1 = $res1->data;
            unset($res1['id']);
            $res1['create_time'] = $time;
            $res1['creat_person_id'] = $uid;
            $res1['update_time'] = $time;
            $res1['update_person_id'] = $uid;
            $res1['status'] = $params['status'];
            $res1['course_counselor_id'] = $params['course_counselor_id'];
            Db::startTrans();
            try {
                $n_id = $this->table($this->getTableName('reservation_list'))->insert($res1);
                if ($n_id === false) {
                    throw new \Exception('reservation_list 录入失败');
                }
                if ($this->where(array('id' => $res1['req_id']))->update(array('status' => '11'))) {
                    Db::commit();
                    return MBISReturn("录入成功", 1);
                }
            } catch (\Exception $e) {
                Db::rollback();
                return MBISReturn($e->getMessage(), -1);
            }
        }
        return MBISReturn('未知错误！');
    }

    /**
     * 拼接数据表名，加上数据表前缀
     * @param $name
     * @return bool|string
     */
    protected function getTableName($name)
    {
        if (empty($name)) return false;
        $prefix = Config::get('database.prefix');
        return $prefix . $name;
    }

    /**
     * 客户信息处理
     * @param $data
     * @return array
     */
    protected function checkData($data)
    {
        $user = [];
        //学员姓名
        if (!empty($data['trueName'])) {
            $user['name'] = $data['trueName'];
        }
        //	性别
        if (!empty($data['userSex'])) {
            $user['sex'] = $data['userSex'];
        }
        //生日
        if (!empty($data['brithday'])) {
            $user['birthday'] = strtotime($data['birthday']);
        }
        //手机号码2
        if (!empty($data['mobile2'])) {
            $user['other_tel'] = $data['mobile2'];
        }
        //E-Mail
        if (!empty($data['userEmail'])) {
            $user['email'] = $data['userEmail'];
        }
        //QQ
        if (!empty($data['userQQ'])) {
            $user['qq'] = $data['userQQ'];
        }
        //城市
        if (!empty($data['province'])) {
            $user['region'] = $data['province'] . ',' . $data['city'] . ',' . $data['area'];
        }
        //地址
        if (!empty($data['address'])) {
            $user['address'] = $data['address'];
        }
        //文化程度
        if (!empty($data['education_level'])) {
            $user['education'] = $data['education_level'];
        }
        //工作单位
        if (!empty($data['commany'])) {
            $user['work'] = $data['commany'];
        }
        //职务
        if (!empty($data['job_content'])) {
            $user['job'] = $data['job_content'];
        }
        //微信，以下为用户表没有字段
        if (!empty($data['user_weixin'])) {
            $user['weixin'] = $data['user_weixin'];
        }
        //紧急联系人
        if (isset($data['urgency_contact'])) {
            $user['urgency_contact'] = $data['urgency_contact'];
        }
        //紧急联系人电话
        if (isset($data['urgency_contact_mobile'])) {
            $user['urgency_contact_mobile'] = $data['urgency_contact_mobile'];
        }
        //推荐人
        if (isset($data['referee'])) {
            $user['referee'] = $data['referee'];
        }
        //推荐人电话
        if (isset($data['referee_mobile'])) {
            $user['referee_mobile'] = $data['referee_mobile'];
        }
        //介绍学员
        if (isset($data['students'])) {
            $user['students'] = $data['students'];
        }
        //备注
        if (isset($data['remark'])) {
            $user['remark'] = $data['remark'];
        }

        return $user;
    }

    /**
     * 专业筛选相关信息
     * @return array
     */
    public function getMajor()
    {
        $school = $this->table($this->getTableName('school'))->field('school_id as id,name,jump_type as type_id,exam_type')->where('exam_type <> 0')->select();
        $major = $this->table($this->getTableName('major'))->field('major_id as id,name,type_id')->where('type_id <> 1')->select();
        $major_edu = $this->table($this->getTableName('major_edu'))->alias('a')
                        ->join('major_edu_extend b','b.major_id = a.major_id','left')
                        ->field('a.major_id as id,a.name,a.school_ids as school_id,a.exam_type,GROUP_CONCAT(b.level_id) as level_id')
                        ->group('b.major_id')
                        ->select();
        foreach($major_edu as $k =>$v){
            $major_edu[$k]['school_id'] = explode(',',$v['school_id']);
            $major_edu[$k]['level_id'] = explode(',',$v['level_id']);
        }
        array_unshift($school,['id'=>'0','name'=>'全部']);
        $type = [['id'=>1,'name'=>'学历'],['id'=>2,'name'=>'技能'],['id'=>3,'name'=>'管理']];
        $level = [['name' => '高升专', 'id' => 2], ['name' => '专升本', 'id' => 3]];
        $exam = [['name' => '自考', 'id' => 1], ['name' => '成考', 'id' => 2], ['name' => '网教', 'id' => 3]];
        return $result=['type'=>$type,'exam'=>$exam,'level'=>$level,'school'=>$school,'major'=>$major,'major_edu'=>$major_edu];
    }

    /**
     * 根据专业查找课程
     * @param $params
     * @return array
     */
    public function getCourse($params)
    {
        $where['major_id'] = $params['major_id'];
        $rs = $this->table($this->getTableName('course'))->field('course_id,name')->where($where)->select();
        return MBISReturn('',1,$rs);
    }

    /**
     * 专业课程数组处理函数
     * @param $data
     * @return array|bool
     */
    protected function eachData($data)
    {
        if (!isset($data)) return false;
        $result = [];
        foreach ($data as $k => $v) {
            $str = '';
            if (isset($v['type_id'])) {
                $str .= $v['type_id'] . "_";
            }
            if (!empty($v['exam_type'])) {
                $str .= $v['exam_type'] . "_";
            }
            if (!empty($v['school_id']) && $v['type_id'] == 1) {
                $str .= $v['school_id'] . "_";
            }
            if (!empty($v['level_type'])) {
                $str .= $v['level_type'] . "_";
            }
            $str = trim($str, "_");
            $result[$str][] = ['name' => $v['name'], 'id' => $v['id']];
        }
        return $result;
    }


}