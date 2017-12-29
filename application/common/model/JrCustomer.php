<?php
namespace application\common\model;

use think\Db;

class JrCustomer extends Base
{
    /**
     * 查询客户信息
     * @param $params
     * @return array|bool
     */
    public function getUserDetail($params)
    {
        if(empty($params['mobile']) && empty($params['idcard'])) {
            return MBISReturn('请输入需要查询的条件',-1);
        }
        $where = ['disabled' => 0];
        //手机号码
        if(!empty($params['mobile'])) {
            $where['mobile_tel'] = $params['mobile'];
        }
        //身份证号
        elseif(!empty($params['idcard'])) {
            $where['id_card'] = $params['idcard'];
        }
        //客户信息
        $fields = '*';
        $user = $this->field($fields)->where($where)->find();
        if(empty($user)) {
            return false;
        }
        return $user;
    }
}