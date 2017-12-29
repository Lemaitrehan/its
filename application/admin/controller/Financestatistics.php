<?php

namespace application\admin\controller;

use think\Db;

class Financestatistics extends Base
{
    #region 校区收费统计

    /**
     * 各个校区收费统计
     */
    public function school_in_money()
    {
        $start_date = strlen(input('get.start_date')) > 0 ? strtotime(input('get.start_date')) : null;

        $end_date = strlen(input('get.end_date')) > 0 ? strtotime(input('get.end_date')) : null;

        $result = Db::name('order_detail')->alias('mr')
            ->field('IF ( ISNULL ( sum(mr.real_pay_price ) ), \'0\', sum(mr.real_pay_price ) ) AS realPayMoney,md.name as school_name')
            ->join('mbis_admin_department md', ' md.id = mr.department_id');

        if ($start_date != null) {
            $result = $result->where('mr.createtime', '>', $start_date);
            $this->assign('start_date', date('Y-m-d', $start_date));
        }

        if ($end_date != null) {
            $result = $result->where('mr.createtime', '<=', $end_date);
            $this->assign('end_date', date('Y-m-d', $end_date));
        }
        $result = $result->group('md.id')->select();

        $this->assign('chart_result', json_encode($result));

        return $this->fetch(__FUNCTION__);

    }

    /**
     * 各个校区收费增长图-周
     */
    public function school_in_money_trend_week()
    {
        $start_date = strlen(input('get.start_date')) > 0 ? strtotime(input('get.start_date')) : null;

        $end_date = strlen(input('get.end_date')) > 0 ? strtotime(input('get.end_date')) : null;


        $format_start_date = strtotime(date('Y-m-d', $start_date));
        $format_end_date   = strtotime(date('Y-m-d', $end_date));


        $schools = \application\admin\model\Department::getSchools();

        foreach ($schools as $k => $v) {

            for ($i = $format_start_date ; $i <= $format_end_date ; $i += 86400) {

                $result = Db::name('order_detail')->alias('mr')
                    ->field('IF ( ISNULL ( sum(mr.real_pay_price ) ), \'0\', sum(mr.real_pay_price ) ) AS realPayMoney,md.name as school_name,FROM_UNIXTIME(mr.createtime,\'%Y-%m-%d\') as createtime')
                    ->join('mbis_admin_department md', ' md.id = mr.department_id')
                    ->where('md.id', $k)
                    ->where("FROM_UNIXTIME(mr.createtime,'%Y-%m-%d') = :createtime")->bind([
                        'createtime' => [
                            date('Y-m-d', $i),
                            \PDO::PARAM_INT
                        ]
                    ])
                    ->select();

                if (count($result) == 1) {
                    $data[$k][$i] = $result[0];
                    continue;
                }

                $data[$k][$i] = ['realPayMoney' => 0, 'school_name' => $v];

            }
            unset($result);
        }


        $this->assign('chart_result', json_encode($data));

        return $this->fetch(__FUNCTION__);

    }

    /**
     * 各个校区收费增长图-日
     */
    public function school_in_money_trend_day()
    {
        $start_date = strlen(input('get.start_date')) > 0 ? strtotime(input('get.start_date')) : null;

        $end_date = strlen(input('get.end_date')) > 0 ? strtotime(input('get.end_date')) : null;


        $format_start_date = strtotime(date('Y-m-d', $start_date));
        $format_end_date   = strtotime(date('Y-m-d', $end_date));

        $schools = \application\admin\model\Department::getSchools();

        foreach ($schools as $k => $v) {

            for ($i = $format_start_date ; $i <= $format_end_date ; $i += 86400) {

                $result = Db::name('order_detail')->alias('mr')
                    ->field('IF ( ISNULL ( sum(mr.real_pay_price ) ), \'0\', sum(mr.real_pay_price ) ) AS realPayMoney,md.name as school_name, 
                    if( ISNULL(FROM_UNIXTIME(mr.createtime,\'%Y-%m-%d\')),\''.date('Y-m-d',
                            $i).'\',FROM_UNIXTIME(mr.createtime,\'%Y-%m-%d\') ) as createtime')
                    ->join('mbis_admin_department md', ' md.id = mr.department_id')
                    ->where('md.id', $k)
                    ->where("FROM_UNIXTIME(mr.createtime,'%Y-%m-%d') = :createtime")->bind([
                        'createtime' => [
                            date('Y-m-d', $i),
                            \PDO::PARAM_INT
                        ]
                    ])
                    ->select();

                if (count($result) == 1) {
                    $data[$k][$i] = $result[0];
                    continue;
                }

                $data[$k][$i] = ['realPayMoney' => 0, 'school_name' => $v, 'createtime' => date('Y-m-d', $i)];

            }
            unset($result);
        }


        $this->assign('chart_result', json_encode($data));

        return $this->fetch(__FUNCTION__);
    }

    /**
     * 各个校区收费对比图
     */
    public function school_in_money_diff()
    {
        $diff_start_date = strlen(input('get.start_date')) > 0 ? strtotime(input('get.start_date')) : null;
        $diff_end_date   = strlen(input('get.end_date')) > 0 ? strtotime(input('get.end_date')) : null;


        $format_start_date = strtotime(date('Y-m', $diff_start_date));
        $format_end_date   = strtotime(date('Y-m', $diff_end_date));

        $schools = \application\admin\model\Department::getSchools();

        foreach ($schools as $k => $v) {

            $one = Db::name('order_detail')->alias('mr')
                ->field('IF ( ISNULL ( sum(mr.real_pay_price) ) , \'0\', sum(mr.real_pay_price ) ) AS realPayMoney')
                ->join('mbis_admin_department md', ' md.id = mr.department_id')
                ->where('md.id', $k)
                ->where("FROM_UNIXTIME(mr.createtime,'%Y-%m') = :createtime")
                ->bind(['createtime' => [date('Y-m', $format_start_date), \PDO::PARAM_INT]])
                ->select();

            if (count($one) == 1) {
                $data_one[] = $one[0]['realPayMoney'];
            } else {
                $data_one[] = 0;
            }

            $two = Db::name('order_detail')->alias('mr')
                ->field('IF ( ISNULL ( sum(mr.real_pay_price ) ), \'0\', sum(mr.real_pay_price ) ) AS realPayMoney')
                ->join('mbis_admin_department md', ' md.id = mr.department_id')
                ->where('md.id', $k)
                ->where("FROM_UNIXTIME(mr.createtime,'%Y-%m') = :createtime")
                ->bind(['createtime' => [date('Y-m', $format_end_date), \PDO::PARAM_INT]])
                ->select();

            if (count($two) == 1) {
                $data_two[] = $two[0]['realPayMoney'];
            } else {
                $data_two[] = 0;
            }
        }


        $this->assign('format_start_date', date('Y-m', $format_start_date));

        $this->assign('format_end_date', date('Y-m', $format_end_date));

        $this->assign('chart_result_one', json_encode($data_one));

        $this->assign('chart_result_two', json_encode($data_two));

        return $this->fetch(__FUNCTION__);
    }

    #endregion


    #region 校区退费统计

    /**
     * 校区退费统计
     */
    public function school_out_money()
    {
        $start_date = strlen(input('get.start_date')) > 0 ? strtotime(input('get.start_date')) : null;

        $end_date = strlen(input('get.end_date')) > 0 ? strtotime(input('get.end_date')) : null;

        $result = Db::name('order_detail')->alias('mr')
            ->field('IF ( ISNULL ( sum(mr.real_pay_price ) ), \'0\', sum(mr.real_pay_price ) ) AS realPayMoney,md.name as school_name')
            ->join('mbis_admin_department md', ' md.id = mr.department_id');

        if ($start_date != null) {
            $result = $result->where('mr.createtime', '>', $start_date);
            $this->assign('start_date', date('Y-m-d', $start_date));
        }

        if ($end_date != null) {
            $result = $result->where('mr.createtime', '<=', $end_date);
            $this->assign('end_date', date('Y-m-d', $end_date));
        }
        $result = $result->group('md.id')->select();

        $this->assign('chart_result', json_encode($result));

        return $this->fetch(__FUNCTION__);

    }

    /**
     * 校区退费增长图-周
     */
    public function school_out_money_trend_week()
    {
        $start_date = strlen(input('get.start_date')) > 0 ? strtotime(input('get.start_date')) : null;

        $end_date = strlen(input('get.end_date')) > 0 ? strtotime(input('get.end_date')) : null;


        $format_start_date = strtotime(date('Y-m-d', $start_date));
        $format_end_date   = strtotime(date('Y-m-d', $end_date));


        $schools = \application\admin\model\Department::getSchools();

        foreach ($schools as $k => $v) {

            for ($i = $format_start_date ; $i <= $format_end_date ; $i += 86400) {

                $result = Db::name('order_detail')->alias('mr')
                    ->field('IF ( ISNULL ( sum(mr.real_pay_price ) ), \'0\', sum(mr.real_pay_price ) ) AS realPayMoney,md.name as school_name,FROM_UNIXTIME(mr.createtime,\'%Y-%m-%d\') as createtime')
                    ->join('mbis_admin_department md', ' md.id = mr.department_id')
                    ->where('md.id', $k)
                    ->where("FROM_UNIXTIME(mr.createtime,'%Y-%m-%d') = :createtime")->bind([
                        'createtime' => [
                            date('Y-m-d', $i),
                            \PDO::PARAM_INT
                        ]
                    ])
                    ->select();

                if (count($result) == 1) {
                    $data[$k][$i] = $result[0];
                    continue;
                }

                $data[$k][$i] = ['realPayMoney' => 0, 'school_name' => $v];

            }
            unset($result);
        }


        $this->assign('chart_result', json_encode($data));

        return $this->fetch(__FUNCTION__);

    }

    /**
     * 校区退费增长图-日
     */
    public function school_out_money_trend_day()
    {
        $start_date = strlen(input('get.start_date')) > 0 ? strtotime(input('get.start_date')) : null;

        $end_date = strlen(input('get.end_date')) > 0 ? strtotime(input('get.end_date')) : null;


        $format_start_date = strtotime(date('Y-m-d', $start_date));
        $format_end_date   = strtotime(date('Y-m-d', $end_date));

        $schools = \application\admin\model\Department::getSchools();

        foreach ($schools as $k => $v) {

            for ($i = $format_start_date ; $i <= $format_end_date ; $i += 86400) {

                $result = Db::name('order_detail')->alias('mr')
                    ->field('IF ( ISNULL ( sum(mr.real_pay_price ) ), \'0\', sum(mr.real_pay_price ) ) AS realPayMoney,md.name as school_name, 
                    if( ISNULL(FROM_UNIXTIME(mr.createtime,\'%Y-%m-%d\')),\''.date('Y-m-d',
                            $i).'\',FROM_UNIXTIME(mr.createtime,\'%Y-%m-%d\') ) as createtime')
                    ->join('mbis_admin_department md', ' md.id = mr.department_id')
                    ->where('md.id', $k)
                    ->where("FROM_UNIXTIME(mr.createtime,'%Y-%m-%d') = :createtime")->bind([
                        'createtime' => [
                            date('Y-m-d', $i),
                            \PDO::PARAM_INT
                        ]
                    ])
                    ->select();

                if (count($result) == 1) {
                    $data[$k][$i] = $result[0];
                    continue;
                }

                $data[$k][$i] = ['realPayMoney' => 0, 'school_name' => $v, 'createtime' => date('Y-m-d', $i)];

            }
            unset($result);
        }


        $this->assign('chart_result', json_encode($data));

        return $this->fetch(__FUNCTION__);
    }


    /**
     * 校区退费对比图
     */
    public function school_out_money_diff()
    {
        $diff_start_date = strlen(input('get.start_date')) > 0 ? strtotime(input('get.start_date')) : null;
        $diff_end_date   = strlen(input('get.end_date')) > 0 ? strtotime(input('get.end_date')) : null;


        $format_start_date = strtotime(date('Y-m', $diff_start_date));
        $format_end_date   = strtotime(date('Y-m', $diff_end_date));

        $schools = \application\admin\model\Department::getSchools();

        foreach ($schools as $k => $v) {

            $one = Db::name('order_detail')->alias('mr')
                ->field('IF ( ISNULL ( sum(mr.real_pay_price) ) , \'0\', sum(mr.real_pay_price ) ) AS realPayMoney')
                ->join('mbis_admin_department md', ' md.id = mr.department_id')
                ->where('md.id', $k)
                ->where("FROM_UNIXTIME(mr.createtime,'%Y-%m') = :createtime")
                ->bind(['createtime' => [date('Y-m', $format_start_date), \PDO::PARAM_INT]])
                ->select();

            if (count($one) == 1) {
                $data_one[] = $one[0]['realPayMoney'];
            } else {
                $data_one[] = 0;
            }

            $two = Db::name('order_detail')->alias('mr')
                ->field('IF ( ISNULL ( sum(mr.real_pay_price ) ), \'0\', sum(mr.real_pay_price ) ) AS realPayMoney')
                ->join('mbis_admin_department md', ' md.id = mr.department_id')
                ->where('md.id', $k)
                ->where("FROM_UNIXTIME(mr.createtime,'%Y-%m') = :createtime")
                ->bind(['createtime' => [date('Y-m', $format_end_date), \PDO::PARAM_INT]])
                ->select();

            if (count($two) == 1) {
                $data_two[] = $two[0]['realPayMoney'];
            } else {
                $data_two[] = 0;
            }
        }


        $this->assign('format_start_date', date('Y-m', $format_start_date));

        $this->assign('format_end_date', date('Y-m', $format_end_date));

        $this->assign('chart_result_one', json_encode($data_one));

        $this->assign('chart_result_two', json_encode($data_two));

        return $this->fetch(__FUNCTION__);
    }

    #endregion


    #region 课程收费统计

    /**
     * 课程收费统计
     */
    public function course_in_money()
    {
        $start_date = strlen(input('get.start_date')) > 0 ? strtotime(input('get.start_date')) : null;

        $end_date = strlen(input('get.end_date')) > 0 ? strtotime(input('get.end_date')) : null;

        $major_id = strlen(input('get.major_id')) > 0 ? strtotime(input('get.major_id')) : null;

        $course_id  = strlen(input('get.course_id')) > 0 ? input('get.course_id') : null;

        $result = Db::name('order_detail')->alias('mr')
            ->field('sum(mr.real_pay_price) as \'real_pay_price\',mm.name as \'major_name\',md.name as \'department_name\'')
            ->join('mbis_course mc', 'mc.course_id = mr.course_id')
            ->join('mbis_major mm', 'mm.major_id = mc.major_id')
            ->join('mbis_admin_department md', 'md.id=mr.department_id')
            ->group('mm.major_id,mr.department_id');

        $schools = \application\admin\model\Department::getSchools();

        foreach ($schools as $k => $v) {

            $data[$k] = clone $result;

            $data[$k] = $data[$k]->where('mr.department_id', $k);

            if ($start_date != null) {
                $data[$k] = $data[$k]->where('mr.createtime', '>', $start_date);
                $this->assign('start_date_'.$k, date('Y-m-d', $start_date));
            }

            if ($end_date != null) {
                $data[$k] = $data[$k]->where('mr.createtime', '<=', $end_date);
                $this->assign('end_date_'.$k, date('Y-m-d', $end_date));
            }

            if ($major_id != null) {
                $data[$k] = $data[$k]->where('mm.major_id', $major_id);
            }

            $data[$k] = $data[$k]->select();

        }

        $this->assign('chart_result', json_encode($data));
        $this->assign('schools', $schools);
        return $this->fetch(__FUNCTION__);

    }

    /**
     * 课程收费增长图-周
     */
    public function course_in_money_trend_week()
    {
        $start_date = strlen(input('get.start_date')) > 0 ? strtotime(input('get.start_date')) : null;
        $end_date   = strlen(input('get.end_date')) > 0 ? strtotime(input('get.end_date')) : null;
        $course_id  = strlen(input('get.course_id')) > 0 ? input('get.course_id') : null;

        $format_start_date = strtotime(date('Y-m-d', $start_date));
        $format_end_date   = strtotime(date('Y-m-d', $end_date));

        $schools = \application\admin\model\Department::getSchools();

        foreach ($schools as $k => $v) {

            for ($i = $format_start_date ; $i <= $format_end_date ; $i += 86400) {

                $result = Db::name('order_detail')->alias('mr')
                    ->field('IF ( ISNULL ( sum(mr.real_pay_price ) ), \'0\', sum(mr.real_pay_price ) ) AS realPayMoney,md.name as \'school_name\'')
                    ->join('mbis_course mc', 'mc.course_id = mr.course_id')
                    ->join('mbis_admin_department md', 'md.id=mr.department_id')
                    ->where('md.id', $k)
                    ->where('mc.course_id', $course_id)
                    ->where("FROM_UNIXTIME(mr.createtime,'%Y-%m-%d') = :createtime")->bind([
                        'createtime' => [
                            date('Y-m-d', $i),
                            \PDO::PARAM_INT
                        ]
                    ])
                    ->select();

                if (count($result) == 1) {
                    $data[$k][$i] = $result[0];
                    continue;
                }

                $data[$k][$i] = ['realPayMoney' => 0, 'school_name' => $v];

            }
            unset($result);
        }

        $this->assign('chart_result', json_encode($data));

        return $this->fetch(__FUNCTION__);
    }


    /**
     * 课程收费增长图-日
     */
    public function course_in_money_trend_day()
    {
        $start_date = strlen(input('get.start_date')) > 0 ? strtotime(input('get.start_date')) : null;

        $end_date = strlen(input('get.end_date')) > 0 ? strtotime(input('get.end_date')) : null;

        //切记不能让course_id为空
        $course_id = strlen(input('get.course_id')) > 0 ? input('get.course_id') : null;

        $format_start_date = strtotime(date('Y-m-d', $start_date));
        $format_end_date   = strtotime(date('Y-m-d', $end_date));

        $schools = \application\admin\model\Department::getSchools();

        foreach ($schools as $k => $v) {

            for ($i = $format_start_date ; $i <= $format_end_date ; $i += 86400) {

                $result = Db::name('order_detail')->alias('mr')
                    ->field('IF ( ISNULL ( sum(mr.real_pay_price ) ), \'0\', sum(mr.real_pay_price ) ) AS realPayMoney,md.name as \'school_name\',
                    if( ISNULL(FROM_UNIXTIME(mr.createtime,\'%Y-%m-%d\')),\''.date('Y-m-d',
                            $i).'\',FROM_UNIXTIME(mr.createtime,\'%Y-%m-%d\') ) as createtime')
                    ->join('mbis_course mc', 'mc.course_id = mr.course_id')
                    ->join('mbis_admin_department md', 'md.id=mr.department_id')
                    ->where('md.id', $k)
                    ->where('mc.course_id', $course_id)
                    ->where("FROM_UNIXTIME(mr.createtime,'%Y-%m-%d') = :createtime")->bind([
                        'createtime' => [
                            date('Y-m-d', $i),
                            \PDO::PARAM_INT
                        ]
                    ])
                    ->select();


                if (count($result) == 1) {
                    $data[$k][$i] = $result[0];
                    continue;
                }

                $data[$k][$i] = ['realPayMoney' => 0, 'school_name' => $v, 'createtime' => date('Y-m-d', $i)];

            }
            unset($result);
        }

        $this->assign('chart_result', json_encode($data));

        return $this->fetch(__FUNCTION__);
    }


    /**
     * 课程收费对比图
     */
    public function course_in_money_diff()
    {
        $diff_start_date = strlen(input('get.start_date')) > 0 ? strtotime(input('get.start_date')) : null;
        $diff_end_date   = strlen(input('get.end_date')) > 0 ? strtotime(input('get.end_date')) : null;
        //切记不能让course_id为空
        $course_id = strlen(input('get.course_id')) > 0 ? input('get.course_id') : null;


        $format_start_date = strtotime(date('Y-m', $diff_start_date));
        $format_end_date   = strtotime(date('Y-m', $diff_end_date));

        $schools = \application\admin\model\Department::getSchools();

        foreach ($schools as $k => $v) {

            $one = Db::name('order_detail')->alias('mr')
                ->field('IF ( ISNULL ( sum(mr.real_pay_price ) ), \'0\', sum(mr.real_pay_price ) ) AS realPayMoney')
                ->join('mbis_course mc', 'mc.course_id = mr.course_id')
                ->join('mbis_admin_department md', 'md.id=mr.department_id')
                ->where('md.id', $k)
                ->where('mc.course_id', $course_id)
                ->where("FROM_UNIXTIME(mr.createtime,'%Y-%m') = :createtime")->bind([
                    'createtime' => [
                        date('Y-m', $format_start_date),
                        \PDO::PARAM_INT
                    ]
                ])
                ->select();

            if (count($one) == 1) {
                $data_one[] = $one[0]['realPayMoney'];
            } else {
                $data_one[] = 0;
            }

            $two = Db::name('order_detail')->alias('mr')
                ->field('IF ( ISNULL ( sum(mr.real_pay_price ) ), \'0\', sum(mr.real_pay_price ) ) AS realPayMoney')
                ->join('mbis_course mc', 'mc.course_id = mr.course_id')
                ->join('mbis_admin_department md', 'md.id=mr.department_id')
                ->where('md.id', $k)
                ->where('mc.course_id', $course_id)
                ->where("FROM_UNIXTIME(mr.createtime,'%Y-%m') = :createtime")->bind([
                    'createtime' => [
                        date('Y-m', $format_end_date),
                        \PDO::PARAM_INT
                    ]
                ])
                ->select();

            if (count($two) == 1) {
                $data_two[] = $two[0]['realPayMoney'];
            } else {
                $data_two[] = 0;
            }
        }


        $this->assign('format_start_date', date('Y-m', $format_start_date));

        $this->assign('format_end_date', date('Y-m', $format_end_date));

        $this->assign('chart_result_one', json_encode($data_one));

        $this->assign('chart_result_two', json_encode($data_two));

        return $this->fetch(__FUNCTION__);
    }

    #endregion


    #region 科目收费统计

    /**
     * 科目收费统计
     */
    public function subject_in_money()
    {
        $start_date = strlen(input('get.start_date')) > 0 ? strtotime(input('get.start_date')) : null;

        $end_date = strlen(input('get.end_date')) > 0 ? strtotime(input('get.end_date')) : null;

        $major_id = strlen(input('get.major_id')) > 0 ? strtotime(input('get.major_id')) : null;

        $course_id=strlen(input('get.course_id')) > 0 ? strtotime(input('get.course_id')) : null;




        $result = Db::name('order_detail')->alias('mr')
            ->field('sum(mr.real_pay_price) as \'real_pay_price\',mm.name as \'major_name\',md.name as \'department_name\'')
            ->join('mbis_course mc', 'mc.course_id = mr.course_id')
            ->join('mbis_major mm', 'mm.major_id = mc.major_id')
            ->join('mbis_admin_department md', 'md.id=mr.department_id')
            ->group('mm.major_id,mr.department_id');

        dd($result->buildSql());

        $schools = \application\admin\model\Department::getSchools();

        foreach ($schools as $k => $v) {

            $data[$k] = clone $result;

            $data[$k] = $data[$k]->where('mr.department_id', $k);

            if ($start_date != null) {
                $data[$k] = $data[$k]->where('mr.createtime', '>', $start_date);
                $this->assign('start_date_'.$k, date('Y-m-d', $start_date));
            }

            if ($end_date != null) {
                $data[$k] = $data[$k]->where('mr.createtime', '<=', $end_date);
                $this->assign('end_date_'.$k, date('Y-m-d', $end_date));
            }

            if ($major_id != null) {
                $data[$k] = $data[$k]->where('mm.major_id', $major_id);
            }

            $data[$k] = $data[$k]->select();

        }

        $this->assign('chart_result', json_encode($data));
        $this->assign('schools', $schools);
        return $this->fetch(__FUNCTION__);

    }

    /**
     * 科目收费增长图-周
     */
    public function subject_in_money_trend_week()
    {
        $start_date = strlen(input('get.start_date')) > 0 ? strtotime(input('get.start_date')) : null;

        $end_date = strlen(input('get.end_date')) > 0 ? strtotime(input('get.end_date')) : null;

        $result = Db::name('orders')->alias('mr')->field('sum(mr.realPayMoney) as realPayMoney,mr.department_id,mr.createtime');

        if ($start_date != null) {
            $result = $result->where('mr.createtime', '>', $start_date);
            $this->assign('start_date', date('Y-m-d', $start_date));
        }

        if ($end_date != null) {
            $result = $result->where('mr.createtime', '<=', $end_date);
            $this->assign('end_date', date('Y-m-d', $end_date));
        }

        $result = $result->select();

        return $this->fetch(__FUNCTION__);

    }

    /**
     * 科目收费增长图-日
     */
    public function subject_in_money_trend_day()
    {
        $start_date = strlen(input('get.start_date')) > 0 ? strtotime(input('get.start_date')) : null;

        $end_date = strlen(input('get.end_date')) > 0 ? strtotime(input('get.end_date')) : null;

        $result = Db::name('orders')->alias('mr')->field('sum(mr.realPayMoney) as realPayMoney,mr.department_id,mr.createtime');

        if ($start_date != null) {
            $result = $result->where('mr.createtime', '>', $start_date);
            $this->assign('start_date', date('Y-m-d', $start_date));
        }

        if ($end_date != null) {
            $result = $result->where('mr.createtime', '<=', $end_date);
            $this->assign('end_date', date('Y-m-d', $end_date));
        }

        $result = $result->select();

        return $this->fetch(__FUNCTION__);

    }

    /**
     * 科目收费对比图
     */
    public function subject_in_money_diff()
    {
        $start_date = strlen(input('get.start_date')) > 0 ? strtotime(input('get.start_date')) : null;

        $end_date = strlen(input('get.end_date')) > 0 ? strtotime(input('get.end_date')) : null;

        $result = Db::name('orders')->alias('mr')->field('sum(mr.realPayMoney) as realPayMoney,mr.department_id,mr.createtime');

        if ($start_date != null) {
            $result = $result->where('mr.createtime', '>', $start_date);
            $this->assign('start_date', date('Y-m-d', $start_date));
        }

        if ($end_date != null) {
            $result = $result->where('mr.createtime', '<=', $end_date);
            $this->assign('end_date', date('Y-m-d', $end_date));
        }

        $result = $result->select();

        return $this->fetch(__FUNCTION__);

    }

    #endregion


    #region 收费账户统计

    /**
     * 收费账户统计
     */
    public function account_in_money()
    {
        $start_date = strlen(input('get.start_date')) > 0 ? strtotime(input('get.start_date')) : null;

        $end_date = strlen(input('get.end_date')) > 0 ? strtotime(input('get.end_date')) : null;

        $result = Db::name('orders')->alias('mr')->field('sum(mr.realPayMoney) as realPayMoney,department_id');

        if ($start_date != null) {
            $result = $result->where('mr.createtime', '>', $start_date);
            $this->assign('start_date', date('Y-m-d', $start_date));
        }

        if ($end_date != null) {
            $result = $result->where('mr.createtime', '<=', $end_date);
            $this->assign('end_date', date('Y-m-d', $end_date));
        }

        $result = $result->select();

        return $this->fetch(__FUNCTION__);

    }

    /**
     * 收费账户增长图-周
     */
    public function account_in_money_trend_week()
    {
        $start_date = strlen(input('get.start_date')) > 0 ? strtotime(input('get.start_date')) : null;

        $end_date = strlen(input('get.end_date')) > 0 ? strtotime(input('get.end_date')) : null;

        $result = Db::name('orders')->alias('mr')->field('sum(mr.realPayMoney) as realPayMoney,mr.department_id,mr.createtime');

        if ($start_date != null) {
            $result = $result->where('mr.createtime', '>', $start_date);
            $this->assign('start_date', date('Y-m-d', $start_date));
        }

        if ($end_date != null) {
            $result = $result->where('mr.createtime', '<=', $end_date);
            $this->assign('end_date', date('Y-m-d', $end_date));
        }

        $result = $result->select();

        return $this->fetch(__FUNCTION__);

    }

    /**
     * 收费账户增长图-日
     */
    public function account_in_money_trend_day()
    {
        $start_date = strlen(input('get.start_date')) > 0 ? strtotime(input('get.start_date')) : null;

        $end_date = strlen(input('get.end_date')) > 0 ? strtotime(input('get.end_date')) : null;

        $result = Db::name('orders')->alias('mr')->field('sum(mr.realPayMoney) as realPayMoney,mr.department_id,mr.createtime');

        if ($start_date != null) {
            $result = $result->where('mr.createtime', '>', $start_date);
            $this->assign('start_date', date('Y-m-d', $start_date));
        }

        if ($end_date != null) {
            $result = $result->where('mr.createtime', '<=', $end_date);
            $this->assign('end_date', date('Y-m-d', $end_date));
        }

        $result = $result->select();

        return $this->fetch(__FUNCTION__);

    }

    /**
     * 收费账户对比图
     */
    public function account_in_money_diff()
    {
        $start_date = strlen(input('get.start_date')) > 0 ? strtotime(input('get.start_date')) : null;

        $end_date = strlen(input('get.end_date')) > 0 ? strtotime(input('get.end_date')) : null;

        $result = Db::name('orders')->alias('mr')->field('sum(mr.realPayMoney) as realPayMoney,mr.department_id,mr.createtime');

        if ($start_date != null) {
            $result = $result->where('mr.createtime', '>', $start_date);
            $this->assign('start_date', date('Y-m-d', $start_date));
        }

        if ($end_date != null) {
            $result = $result->where('mr.createtime', '<=', $end_date);
            $this->assign('end_date', date('Y-m-d', $end_date));
        }

        $result = $result->select();

        return $this->fetch(__FUNCTION__);

    }

    #endregion


    #region 校区欠费统计

    /**
     * 校区欠费统计
     */
    public function school_arrears_in_money()
    {
        $start_date = strlen(input('get.start_date')) > 0 ? strtotime(input('get.start_date')) : null;

        $end_date = strlen(input('get.end_date')) > 0 ? strtotime(input('get.end_date')) : null;

        $result = Db::name('orders')->alias('mr')->field('sum(mr.realPayMoney) as realPayMoney,department_id');

        if ($start_date != null) {
            $result = $result->where('mr.createtime', '>', $start_date);
            $this->assign('start_date', date('Y-m-d', $start_date));
        }

        if ($end_date != null) {
            $result = $result->where('mr.createtime', '<=', $end_date);
            $this->assign('end_date', date('Y-m-d', $end_date));
        }

        $result = $result->select();

        return $this->fetch(__FUNCTION__);

    }

    /**
     * 校区欠费增长图-周
     */
    public function school_arrears_in_money_trend_week()
    {
        $start_date = strlen(input('get.start_date')) > 0 ? strtotime(input('get.start_date')) : null;

        $end_date = strlen(input('get.end_date')) > 0 ? strtotime(input('get.end_date')) : null;

        $result = Db::name('orders')->alias('mr')->field('sum(mr.realPayMoney) as realPayMoney,mr.department_id,mr.createtime');

        if ($start_date != null) {
            $result = $result->where('mr.createtime', '>', $start_date);
            $this->assign('start_date', date('Y-m-d', $start_date));
        }

        if ($end_date != null) {
            $result = $result->where('mr.createtime', '<=', $end_date);
            $this->assign('end_date', date('Y-m-d', $end_date));
        }

        $result = $result->select();

        return $this->fetch(__FUNCTION__);

    }

    /**
     * 校区欠费增长图-日
     */
    public function school_arrears_in_money_trend_day()
    {
        $start_date = strlen(input('get.start_date')) > 0 ? strtotime(input('get.start_date')) : null;

        $end_date = strlen(input('get.end_date')) > 0 ? strtotime(input('get.end_date')) : null;

        $result = Db::name('orders')->alias('mr')->field('sum(mr.realPayMoney) as realPayMoney,mr.department_id,mr.createtime');

        if ($start_date != null) {
            $result = $result->where('mr.createtime', '>', $start_date);
            $this->assign('start_date', date('Y-m-d', $start_date));
        }

        if ($end_date != null) {
            $result = $result->where('mr.createtime', '<=', $end_date);
            $this->assign('end_date', date('Y-m-d', $end_date));
        }

        $result = $result->select();

        return $this->fetch(__FUNCTION__);

    }

    /**
     * 校区欠费对比图
     */
    public function school_arrears_in_money_diff()
    {
        $start_date = strlen(input('get.start_date')) > 0 ? strtotime(input('get.start_date')) : null;

        $end_date = strlen(input('get.end_date')) > 0 ? strtotime(input('get.end_date')) : null;

        $result = Db::name('orders')->alias('mr')->field('sum(mr.realPayMoney) as realPayMoney,mr.department_id,mr.createtime');

        if ($start_date != null) {
            $result = $result->where('mr.createtime', '>', $start_date);
            $this->assign('start_date', date('Y-m-d', $start_date));
        }

        if ($end_date != null) {
            $result = $result->where('mr.createtime', '<=', $end_date);
            $this->assign('end_date', date('Y-m-d', $end_date));
        }

        $result = $result->select();

        return $this->fetch(__FUNCTION__);

    }

    #endregion


    #region 其他收费统计

    /**
     * 其他收费统计
     */
    public function other_in_money()
    {
        $start_date = strlen(input('get.start_date')) > 0 ? strtotime(input('get.start_date')) : null;

        $end_date = strlen(input('get.end_date')) > 0 ? strtotime(input('get.end_date')) : null;

        $result = Db::name('orders')->alias('mr')->field('sum(mr.realPayMoney) as realPayMoney,department_id');

        if ($start_date != null) {
            $result = $result->where('mr.createtime', '>', $start_date);
            $this->assign('start_date', date('Y-m-d', $start_date));
        }

        if ($end_date != null) {
            $result = $result->where('mr.createtime', '<=', $end_date);
            $this->assign('end_date', date('Y-m-d', $end_date));
        }

        $result = $result->select();

        return $this->fetch(__FUNCTION__);

    }

    /**
     * 其他收费增长图-周
     */
    public function other_in_money_trend_week()
    {
        $start_date = strlen(input('get.start_date')) > 0 ? strtotime(input('get.start_date')) : null;

        $end_date = strlen(input('get.end_date')) > 0 ? strtotime(input('get.end_date')) : null;

        $result = Db::name('orders')->alias('mr')->field('sum(mr.realPayMoney) as realPayMoney,mr.department_id,mr.createtime');

        if ($start_date != null) {
            $result = $result->where('mr.createtime', '>', $start_date);
            $this->assign('start_date', date('Y-m-d', $start_date));
        }

        if ($end_date != null) {
            $result = $result->where('mr.createtime', '<=', $end_date);
            $this->assign('end_date', date('Y-m-d', $end_date));
        }

        $result = $result->select();

        return $this->fetch(__FUNCTION__);

    }

    /**
     * 其他收费增长图-日
     */
    public function other_in_money_trend_day()
    {
        $start_date = strlen(input('get.start_date')) > 0 ? strtotime(input('get.start_date')) : null;

        $end_date = strlen(input('get.end_date')) > 0 ? strtotime(input('get.end_date')) : null;

        $result = Db::name('orders')->alias('mr')->field('sum(mr.realPayMoney) as realPayMoney,mr.department_id,mr.createtime');

        if ($start_date != null) {
            $result = $result->where('mr.createtime', '>', $start_date);
            $this->assign('start_date', date('Y-m-d', $start_date));
        }

        if ($end_date != null) {
            $result = $result->where('mr.createtime', '<=', $end_date);
            $this->assign('end_date', date('Y-m-d', $end_date));
        }

        $result = $result->select();

        return $this->fetch(__FUNCTION__);

    }

    /**
     * 其他收费对比图
     */
    public function other_in_money_diff()
    {
        $start_date = strlen(input('get.start_date')) > 0 ? strtotime(input('get.start_date')) : null;

        $end_date = strlen(input('get.end_date')) > 0 ? strtotime(input('get.end_date')) : null;

        $result = Db::name('orders')->alias('mr')->field('sum(mr.realPayMoney) as realPayMoney,mr.department_id,mr.createtime');

        if ($start_date != null) {
            $result = $result->where('mr.createtime', '>', $start_date);
            $this->assign('start_date', date('Y-m-d', $start_date));
        }

        if ($end_date != null) {
            $result = $result->where('mr.createtime', '<=', $end_date);
            $this->assign('end_date', date('Y-m-d', $end_date));
        }

        $result = $result->select();

        return $this->fetch(__FUNCTION__);

    }

    #endregion


    #region 成本支出统计

    /**
     * 成本支出统计
     */
    public function cost_out_money()
    {
        $start_date = strlen(input('get.start_date')) > 0 ? strtotime(input('get.start_date')) : null;

        $end_date = strlen(input('get.end_date')) > 0 ? strtotime(input('get.end_date')) : null;

        $result = Db::name('orders')->alias('mr')->field('sum(mr.realPayMoney) as realPayMoney,department_id');

        if ($start_date != null) {
            $result = $result->where('mr.createtime', '>', $start_date);
            $this->assign('start_date', date('Y-m-d', $start_date));
        }

        if ($end_date != null) {
            $result = $result->where('mr.createtime', '<=', $end_date);
            $this->assign('end_date', date('Y-m-d', $end_date));
        }

        $result = $result->select();

        return $this->fetch(__FUNCTION__);

    }

    /**
     * 成本支出增长图-周
     */
    public function cost_out_money_trend_week()
    {
        $start_date = strlen(input('get.start_date')) > 0 ? strtotime(input('get.start_date')) : null;

        $end_date = strlen(input('get.end_date')) > 0 ? strtotime(input('get.end_date')) : null;

        $result = Db::name('orders')->alias('mr')->field('sum(mr.realPayMoney) as realPayMoney,mr.department_id,mr.createtime');

        if ($start_date != null) {
            $result = $result->where('mr.createtime', '>', $start_date);
            $this->assign('start_date', date('Y-m-d', $start_date));
        }

        if ($end_date != null) {
            $result = $result->where('mr.createtime', '<=', $end_date);
            $this->assign('end_date', date('Y-m-d', $end_date));
        }

        $result = $result->select();

        return $this->fetch(__FUNCTION__);

    }

    /**
     * 成本支出增长图-日
     */
    public function cost_out_money_trend_day()
    {
        $start_date = strlen(input('get.start_date')) > 0 ? strtotime(input('get.start_date')) : null;

        $end_date = strlen(input('get.end_date')) > 0 ? strtotime(input('get.end_date')) : null;

        $result = Db::name('orders')->alias('mr')->field('sum(mr.realPayMoney) as realPayMoney,mr.department_id,mr.createtime');

        if ($start_date != null) {
            $result = $result->where('mr.createtime', '>', $start_date);
            $this->assign('start_date', date('Y-m-d', $start_date));
        }

        if ($end_date != null) {
            $result = $result->where('mr.createtime', '<=', $end_date);
            $this->assign('end_date', date('Y-m-d', $end_date));
        }

        $result = $result->select();

        return $this->fetch(__FUNCTION__);

    }

    /**
     * 成本支出对比图
     */
    public function cost_out_money_diff()
    {
        $start_date = strlen(input('get.start_date')) > 0 ? strtotime(input('get.start_date')) : null;

        $end_date = strlen(input('get.end_date')) > 0 ? strtotime(input('get.end_date')) : null;

        $result = Db::name('orders')->alias('mr')->field('sum(mr.realPayMoney) as realPayMoney,mr.department_id,mr.createtime');

        if ($start_date != null) {
            $result = $result->where('mr.createtime', '>', $start_date);
            $this->assign('start_date', date('Y-m-d', $start_date));
        }

        if ($end_date != null) {
            $result = $result->where('mr.createtime', '<=', $end_date);
            $this->assign('end_date', date('Y-m-d', $end_date));
        }

        $result = $result->select();

        return $this->fetch(__FUNCTION__);

    }

    #endregion


}