<?php

namespace application\common\model;

use think\Db;
use think\config;
use think\Exception;

class Admin extends Base
{
    //各校区咨询师角色id
    protected $roleids = [
        '2' => '32', //宝安
        '3' => '27', //福田
        '4' => '25', //龙岗
        '5' => '35', //南山
    ];
    public function userDetail($uid)
    {
        $where['username'] = $uid;
        $user = $this->where($where)->field('username,realname')->select();
        return $user;
    }

    /**
     * 对应校区咨询师
     * @param $school
     * @return bool
     */
    public function counloser($school)
    {
        $where['a.roleid'] = $this->roleids[$school];
        $field = 'a.username,a.realname,u.userPhoto,t.name';
        $data = $this->alias('a')
                    ->join('employee e','e.employee_no = a.username','left')
                    ->join('users u','u.employee_id = e.employee_id','left')
                    ->join('employee_type t','t.employee_type_id = e.employee_type_id','left')
                    ->field($field)
                    ->where($where)->select();
        if($data) {
            return $data;
        }
        return false;
    }

}