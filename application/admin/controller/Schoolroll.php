<?php

namespace application\admin\controller;

use application\admin\model\CourseSubject;
use application\admin\model\CourseSubjectUser;
use application\admin\model\Student;
use function PHPSTORM_META\type;
use think\Db;
use think\Response;
use think\View;
use application\admin\model\Users;
use application\common\exception\LogicException;
use Exception;
use application\admin\model\StudentEdu;
use application\admin\model\Major;
use application\admin\model\Course;

/**
 * 学籍管理
 */
class Schoolroll extends Base
{
    #region 学籍功能

    /**
     * 基础身份
     * @return mixed
     */
    public function index()
    {
        return $this->commUserInfo(1);
    }

    /**
     * 基础身份------课程信息和费用信息
     */
    public function index_info()
    {
        try {
            $number = trim(input('post.number'));

            if (strlen($number) < 0 || $this->checkNumberExt($number) == false) {
                throw new LogicException('学号不存在！', 1);
            }

            $model_student_edu = new StudentEdu();
            $user              = new Users();

            $result['data_one'] = $model_student_edu->alias('mse')
                ->field("mse.type_id, 'start_study_date', 'end_study_date', mme.major_number, mse.major_name, mse.course_bn, mse.course_name, mc.course_hours ,mse.userId,mse.course_id")
                ->where('mse.type_id IN ( 2, 3 )')
                ->where('mu.student_no', $number)
                ->join('mbis_course mc', ' mc.course_id = mse.course_id', 'LEFT')
                ->join('mbis_major_edu mme', ' mme.major_id = mse.major_id', 'LEFT')
                ->join('mbis_users mu', ' mu.userId = mse.userId')
                ->select();

            foreach ($result['data_one'] as $k => $v) {
                $tmp                               = $v->toArray();
                $result['data_one'][$k]['details'] = $model_student_edu->alias('mse')
                    ->field("ms.subject_no, ms.`name`, ms.course_hours, ms.sale_price")
                    ->where('mse.type_id IN ( 2, 3 )')
                    ->where('mse.userId', $tmp['userId'])
                    ->where('mse.course_id', $tmp['course_id'])
                    ->join('mbis_course mc', ' mc.course_id = mse.course_id', 'LEFT')
                    ->join('mbis_course_subject mcs', ' mcs.course_id=mc.course_id', 'LEFT')
                    ->join('mbis_subject ms', ' ms.subject_id = mcs.subject_id')
                    ->select();


                $result['data_one'][$k]['type_id'] = $user->getSubjectType($tmp['type_id']);

                $result['data_one'][$k] = $result['data_one'][$k]->toArray();
            }

            $result['data_two'] = $model_student_edu->alias('mse')
                ->field("mp.receiptNo, mu.student_no, mu.trueName, mp.receiptCate,mp.receiptDate, mp.realPayFee")
                ->where('mse.type_id IN ( 2, 3 )')
//                ->where('mu.student_no',$number)
                ->where('mse.userId', $tmp['userId'])
                ->join('mbis_users mu', ' mu.userId = mse.userId')
                ->join('mbis_payments mp', ' mp.userId=mse.userId')
                ->select();

            $this->assign('result', $result);

            $html_content = $this->fetch('info');

            return api_response(0, '', ['html_content' => $html_content]);
        } catch (Exception $e) {

            if ($e instanceof LogicException) {

                return api_response($e->getCode(), $e->getMessage());
            }

            return api_response(-1);
        }
    }

    /**
     * 基础身份------上传照片
     */
    public function index_upload_avatar()
    {

        try {
            $number = trim(input('post.number'));

            $file = $this->request->file('file');

            if ($file->isValid() == false) {
                throw new LogicException('文件存在错误！', 1);
            }

            $save_file = custom_upload_image($file, mk_file_name($file->getPathname()));

            if ($save_file['bool'] == false) {
                throw new LogicException($save_file['message'], 1);
            }

            $file_name = str_replace(ROOT_PATH.'public/', '', $save_file['data']->getPathName());

            if (Users::where('student_no', $number)->update(['idcard_Photo' => $file_name]) == false) {
                throw new LogicException('上传图片保存失败！', 1);
            }

            return api_response(0);

        } catch (Exception $e) {

            if ($e instanceof LogicException) {

                return api_response($e->getCode(), $e->getMessage());
            }

            return api_response(-1);
        }

    }

    /**
     * 更新用户信息
     * @return \think\response\Json
     */
    public function update_info()
    {
        try {

            $number = trim(input('post.number'));

            $uid_type = trim(input('post.uid_type'));

            $study_status = trim(input('post.study_status'));

            if (strlen($number) > 0 && $this->checkNumberExt($number)) {
                $where['student_no'] = $number;
            } else {
                throw new LogicException('学号不存在！', 1);
            }

            if (strlen($uid_type) > 0) {
                $update['uidType'] = $uid_type;
            }

            if (strlen($study_status) > 0) {
                $update['study_status'] = $study_status;
            }

            $result = Users::where($where)->update($update);

            if ($result == 1) {
                return api_response(0);
            }
            return api_response(0, '无需更新');

        } catch (Exception $e) {

            if ($e instanceof LogicException) {

                return api_response($e->getCode(), $e->getMessage());
            }

            return api_response(-1);
        }
    }

    /**
     * 加报查询
     * @return mixed
     */
    public function append_entry()
    {
        return $this->commUserInfo(2);
    }

    /**
     * 加报查询------查看加报课程与费用
     */
    public function append_entry_info()
    {
        try {
            $number = trim(input('post.number'));

            if (strlen($number) < 0 || $this->checkNumberExt($number) == false) {
                throw new LogicException('学号不存在！', 1);
            }

            $model_student_edu = new StudentEdu();

            $user_id = $model_student_edu->alias('mse')
                ->where('mu.student_no', $number)
                ->join('mbis_users mu', 'mu.userId = mse.userId')->limit(1)
                ->column('mu.userId');

            if (isset($user_id[0]) == false) {
                throw new LogicException('用户不存在报名！', 1);
            }

            $user_id = $user_id[0];

            $course_id = $model_student_edu->where('userId', $user_id)
                ->order('entry_time asc')
                ->group('userId')
                ->limit(1)
                ->field('course_id')
                ->column('course_id');

            $result['data_one'] = $model_student_edu->alias('mse')
                ->field("mse.type_id,'start_study_date','end_study_date',mme.major_number, mse.major_name, mse.course_bn, mse.course_name, mc.course_hours ,mse.userId,mse.course_id")
                # @todo: 之后需要加上表示是技能和管理
                ->where('mse.type_id IN ( 2, 3 )')
                ->where('mu.student_no', $number);

            if (isset($course_id[0]) && strlen($course_id[0]) > 0) {
                $course_id          = $course_id[0];
                $result['data_one'] = $result['data_one']->where('mse.course_id', '<>', $course_id);
            } else {
                throw new LogicException('用户不存在加报数据', 1);
            }

            $result['data_one'] = $result['data_one']->join('mbis_course mc', 'mc.course_id = mse.course_id', 'LEFT')
                ->join('mbis_major_edu mme', 'mme.major_id = mse.major_id', 'LEFT')
                ->join('mbis_users mu', 'mu.userId = mse.userId')
                ->select();
            $user               = new Users();
            foreach ($result['data_one'] as $k => $v) {
                $tmp = $v->toArray();

                if (count($tmp) > 0) {
                    $result['data_one'][$k]['details'] = $model_student_edu->alias('mse')
                        ->field("ms.subject_no, ms.`name`, ms.course_hours, ms.sale_price")
                        ->where('mse.type_id IN ( 2, 3 )')
                        ->where('mse.userId', $tmp['userId'])
                        ->where('mse.course_id', $tmp['course_id'])
                        ->join('mbis_course mc', ' mc.course_id = mse.course_id', 'LEFT')
                        ->join('mbis_course_subject mcs', ' mcs.course_id=mc.course_id', 'LEFT')
                        ->join('mbis_subject ms', ' ms.subject_id = mcs.subject_id')
                        ->select();

                    $result['data_one'][$k]['type_id'] = $user->getSubjectType($tmp['type_id']);
                    $result['data_one'][$k]            = $result['data_one'][$k]->toArray();
                }
            }

            $result['data_two'] = $model_student_edu->alias('mse')
                # @todo: 学历的receiptDate是date形式的，技能和管理的是unix时间戳,真尴尬
                ->field("mp.receiptNo, mu.student_no, mu.trueName, mp.receiptCate,mp.receiptDate, mp.realPayFee")
                ->where('mse.type_id IN ( 2, 3 )')
                ->where('mu.student_no', $number)
                ->where('mse.course_id', '<>', $course_id)
                ->where('mse.userId', $user_id)
                ->join('mbis_users mu', ' mu.userId = mse.userId')
                ->join('mbis_payments mp', ' mp.userId=mse.userId')
                ->select();


            $this->assign('result', $result);

            $html_content = $this->fetch('append_entry_info');

            return api_response(0, '', ['html_content' => $html_content]);
        } catch (Exception $e) {
            if ($e instanceof LogicException) {

                return api_response($e->getCode(), $e->getMessage());
            }

            return api_response(-1);
        }
    }

    /**
     * 休学登记
     * @return mixed
     */
    public function out_school()
    {
        return $this->commUserInfo(3);
    }

    /**
     * 休学登记------查看休学课程与休学前费用信息
     */
    public function out_school_info()
    {
        try {
            $number = trim(input('post.number'));

            if (strlen($number) < 0 || $this->checkNumberExt($number) == false) {
                throw new LogicException('学号不存在！', 1);
            }

            $model_student_edu  = new StudentEdu();
            $user               = new Users();
            $result['data_one'] = $model_student_edu->alias('mse')
                ->field("mse.type_id, 'start_study_date', 'end_study_date', mme.major_number, mse.major_name, mse.course_bn, mse.course_name, mc.course_hours ,mse.userId,mse.course_id")
                ->where('mse.type_id IN ( 2, 3 )')
                ->where('mu.student_no', $number)
                ->join('mbis_course mc', ' mc.course_id = mse.course_id', 'LEFT')
                ->join('mbis_major_edu mme', ' mme.major_id = mse.major_id', 'LEFT')
                ->join('mbis_users mu', ' mu.userId = mse.userId')
                ->select();

            foreach ($result['data_one'] as $k => $v) {
                $tmp = $v->toArray();
                if (count($tmp) > 0) {
                    $result['data_one'][$k]['details'] = $model_student_edu->alias('mse')
                        ->field("ms.subject_no, ms.`name`, ms.course_hours, ms.sale_price,mcsu.subject_id,mcsu.id")
                        ->where('mse.type_id IN ( 2, 3 )')
                        ->where('mse.userId', $tmp['userId'])
                        ->where('mse.course_id', $tmp['course_id'])
                        //如果科目学习状态为毕业，退学 这两种状态是不能休学的
                        ->where('mcsu.study_status', 'not in', '(5,4)')
                        ->join('mbis_course mc', ' mc.course_id = mse.course_id', 'LEFT')
                        ->join('mbis_course_subject mcs', ' mcs.course_id=mc.course_id', 'LEFT')
                        ->join('mbis_subject ms', ' ms.subject_id = mcs.subject_id')
                        ->join('mbis_course_subject_user mcsu', ' ms.subject_id = mcsu.subject_id')
                        ->select();

                    $result['data_one'][$k]['type_id'] = $user->getSubjectType($tmp['type_id']);
                    $result['data_one'][$k]            = $result['data_one'][$k]->toArray();
                }
            }

            $result['data_two'] = $model_student_edu->alias('mse')
                ->field("mp.receiptNo, mu.student_no, mu.trueName, mp.receiptCate,mp.receiptDate, mp.realPayFee")
                ->where('mse.type_id IN ( 2, 3 )')
                ->where('mu.student_no', $number)
                ->join('mbis_users mu', ' mu.userId = mse.userId')
                ->join('mbis_payments mp', ' mp.userId=mse.userId')
                ->select();

            $this->assign('result', $result);

            $html_content = $this->fetch('out_school_info');

            return api_response(0, '', ['html_content' => $html_content]);
        } catch (Exception $e) {
            if ($e instanceof LogicException) {

                return api_response($e->getCode(), $e->getMessage());
            }

            return api_response(-1);
        }
    }

    /**
     *休学登记保存休学信息------休学操作
     */
    public function out_school_exec()
    {
        try {
            Db::startTrans();

            $data = input('post.data');

            $data = explode(',', $data);

            $start_date = input('post.start_date');

            $end_date = input('post.end_date');

            if (strtotime($start_date) >= strtotime($end_date)) {
                throw new LogicException('休学结束时间必须大于开始时间！', 1);
            }

            $remark = input('post.remark');

            $staff = $_SESSION['MBISMART_']['MBIS_STAFF'];

            foreach ($data as $k => $v) {

                $input_data = explode('^^^', $v);

                $user_id    = $input_data[0];
                $subject_id = $input_data[1];
                $course_id  = $input_data[2];
                //mbis_course_subject_user 的主键
                $course_subject_user_id = $input_data[3];

                $pre_study_status = Db::table('mbis_course_subject_user')->where('user_id',
                    $user_id)->where('subject_id',
                    $subject_id)->where('id', $course_subject_user_id)->where('study_status', 'not in',
                    '(5,4)')->column('study_status');

                if (isset($pre_study_status[0]) == false) {
                    throw new LogicException('休学前的学习状态未知！', 1);
                }

                $up_csu = Db::table('mbis_course_subject_user')->where('user_id', $user_id)->where('subject_id',
                    $subject_id)->where('id', $course_subject_user_id)->where('study_status', 'not in',
                    '(5,4)')->update(['study_status' => 2]);

                $up_cso = Db::table('mbis_course_subject_out_log')->insert([
                    'user_id'                => $user_id,
                    'subject_id'             => $subject_id,
                    'course_id'              => $course_id,
                    'start_date'             => $start_date,
                    'end_date'               => $end_date,
                    'remark'                 => $remark,
                    'course_subject_user_id' => $course_subject_user_id,
                    'created_at'             => date('Y-m-d H:i:s', time()),
                    'pre_study_status'       => $pre_study_status[0],
                    'work_number'            => $staff['work_number'],
                    'work_name'              => $staff['work_name']
                ]);

                if ($up_cso == false || $up_csu == false) {
                    throw new LogicException('休学失败！', 1);
                }
            }

            Db::commit();
            return api_response(0);
        } catch (Exception $e) {
            Db::rollback();
            if ($e instanceof LogicException) {
                return api_response($e->getCode(), $e->getMessage());
            }
            return api_response(-1);
        }
    }

    /**
     * 休学复学
     * @return mixed
     */
    public function re_school()
    {
        return $this->commUserInfo(4);
    }

    /**
     * 休学复学------查看复学课程与复学费用信息
     */
    public function re_school_info()
    {
        try {
            $number = trim(input('post.number'));

            if (strlen($number) < 0 || $this->checkNumberExt($number) == false) {
                throw new LogicException('学号不存在！', 1);
            }

            $model_student_edu  = new StudentEdu();
            $user               = new Users();
            $result['data_one'] = $model_student_edu->alias('mse')
                ->field("mse.type_id, 'start_study_date', 'end_study_date', mme.major_number, mse.major_name, mse.course_bn, mse.course_name, mc.course_hours ,mse.userId,mse.course_id")
                ->where('mse.type_id IN ( 2, 3 )')
                ->where('mu.student_no', $number)
                ->join('mbis_course mc', ' mc.course_id = mse.course_id', 'LEFT')
                ->join('mbis_major_edu mme', ' mme.major_id = mse.major_id', 'LEFT')
                ->join('mbis_users mu', ' mu.userId = mse.userId')
                ->select();

            foreach ($result['data_one'] as $k => $v) {
                $tmp = $v->toArray();
                if (count($tmp) > 0) {
                    $result['data_one'][$k]['details'] = $model_student_edu->alias('mse')
                        ->field("ms.subject_no, ms.`name`, ms.course_hours, ms.sale_price,mcsu.subject_id,mcsu.id")
                        ->where('mse.type_id IN ( 2, 3 )')
                        ->where('mse.userId', $tmp['userId'])
                        ->where('mse.course_id', $tmp['course_id'])
                        //休学状态 才可以复学
                        ->where('mcsu.study_status', 2)
                        ->join('mbis_course mc', ' mc.course_id = mse.course_id', 'LEFT')
                        ->join('mbis_course_subject mcs', ' mcs.course_id=mc.course_id', 'LEFT')
                        ->join('mbis_subject ms', ' ms.subject_id = mcs.subject_id')
                        ->join('mbis_course_subject_user mcsu', ' ms.subject_id = mcsu.subject_id')
                        ->select();

                    $result['data_one'][$k]['type_id'] = $user->getSubjectType($tmp['type_id']);
                    $result['data_one'][$k]            = $result['data_one'][$k]->toArray();
                }
            }

            $result['data_two'] = $model_student_edu->alias('mse')
                ->field("mp.receiptNo, mu.student_no, mu.trueName, mp.receiptCate,mp.receiptDate, mp.realPayFee")
                ->where('mse.type_id IN ( 2, 3 )')
                ->where('mu.student_no', $number)
                ->join('mbis_users mu', ' mu.userId = mse.userId')
                ->join('mbis_payments mp', ' mp.userId=mse.userId')
                ->select();

            $this->assign('result', $result);

            $html_content = $this->fetch('re_school_info');

            return api_response(0, '', ['html_content' => $html_content]);
        } catch (Exception $e) {

            if ($e instanceof LogicException) {

                return api_response($e->getCode(), $e->getMessage());
            }
            return api_response(-1);
        }
    }

    /**
     *休学复学保存复学信息------复学操作
     */
    public function re_school_exec()
    {


        try {

            Db::startTrans();

            $data = input('post.data');

            $data = explode(',', $data);

            $date = input('post.date');

            $remark = input('post.remark');

            $staff = $_SESSION['MBISMART_']['MBIS_STAFF'];

            foreach ($data as $k => $v) {
                $input_data = explode('^^^', $v);

                $user_id    = $input_data[0];
                $subject_id = $input_data[1];
                $course_id  = $input_data[2];
                //mbis_course_subject_user 的主键
                $course_subject_user_id = $input_data[3];

                $up_csu = Db::table('mbis_course_subject_user')->where('user_id', $user_id)->where('subject_id',
                    $subject_id)->where('id', $course_subject_user_id)->where('study_status',
                    2)->update(['study_status' => 3]);


                $up_cso = Db::table('mbis_course_subject_re_log')->insert([
                    'user_id'                => $user_id,
                    'subject_id'             => $subject_id,
                    'course_id'              => $course_id,
                    'date'                   => $date,
                    'remark'                 => $remark,
                    'course_subject_user_id' => $course_subject_user_id,
                    'created_at'             => date('Y-m-d H:i:s', time()),
                    'pre_study_status'       => 2,
                    'work_number'            => $staff['work_number'],
                    'work_name'              => $staff['work_name']
                ]);

                if ($up_cso == false || $up_csu == false) {
                    throw new LogicException('复学失败！', 1);
                }
            }

            Db::commit();
            return api_response(0);
        } catch (Exception $e) {
            Db::rollback();
            if ($e instanceof LogicException) {
                return api_response($e->getCode(), $e->getMessage());
            }
            return api_response(-1);
        }
    }

    /**
     * 毕业登记
     * @return mixed
     */
    public function end_school()
    {

        return $this->commUserInfo(5);
    }

    /**
     * 毕业登记-查看毕业课程和毕业证件
     * @return \think\response\Json
     */
    public function end_school_info()
    {

        try {
            $number = trim(input('post.number'));

            if (strlen($number) < 0 || $this->checkNumberExt($number) == false) {
                throw new LogicException('学号不存在！', 1);
            }

            $model_student_edu = new StudentEdu();

            $model_course_subject_user = new CourseSubjectUser();
            $user                      = new Users();

            $result['data_one'] = $model_student_edu->alias('mse')
                ->field("mse.type_id, 'start_study_date', 'end_study_date', mme.major_number, mse.major_name, mse.course_bn, mse.course_name, mc.course_hours ,mse.userId,mse.course_id,mse.edu_id")
                ->where('mse.type_id IN ( 2, 3 )')
                ->where('mu.student_no', $number)
                ->join('mbis_course mc', ' mc.course_id = mse.course_id', 'LEFT')
                ->join('mbis_major_edu mme', ' mme.major_id = mse.major_id', 'LEFT')
                ->join('mbis_users mu', ' mu.userId = mse.userId')
                ->select();

            foreach ($result['data_one'] as $k => $v) {
                foreach ($result['data_one'] as $k => $v) {
                    $tmp                               = $v->toArray();
                    $result['data_one'][$k]['details'] =

                        //@todo:之后其他的 也要更改成这种方式，通过edu_id
                        $model_course_subject_user->alias('mcsu')
                            ->field("ms.subject_no, ms.`name`, ms.course_hours, ms.sale_price,mcsu.subject_id,mcsu.id")
                            ->where('mcsu.edu_id', $tmp['edu_id'])
                            ->where('mcsu.user_id', $tmp['userId'])
                            ->where('mcsu.study_status', 1)
                            ->join('mbis_subject ms', ' ms.subject_id = mcsu.subject_id')
                            ->select();

                    $result['data_one'][$k]['type_id'] = $user->getSubjectType($tmp['type_id']);
                    $result['data_one'][$k]            = $result['data_one'][$k]->toArray();
                }

                $this->assign('result', $result);

                $cert_type = $this->getCertType();

                $this->assign('cert_type', $cert_type);

                $html_content = $this->fetch('end_school_info');

                return api_response(0, '', ['html_content' => $html_content]);
            }
        } catch (Exception $e) {

            if ($e instanceof LogicException) {

                return api_response($e->getCode(), $e->getMessage());
            }

            return api_response(-1);
        }
    }

    /**
     *毕业证件
     */
    public function end_school_exec()
    {
        try {
            Db::startTrans();

            $data = input('post.data');

            $data = explode(',', $data);

            $date = input('post.date');

            $student_no = input('post.student_no');

            if (strtotime($date) < 1) {
                throw new LogicException('毕业时间不正确！', 1);
            }

            $cert_path_url = input('post.cert_path_url');

            $model_course_subject_user = new CourseSubjectUser();

            $model_student_edu = new StudentEdu();

            $staff = $_SESSION['MBISMART_']['MBIS_STAFF'];

            $cert_name   = input('post.cert_name');
            $cert_type   = input('post.cert_type');
            $cert_number = input('post.cert_number');

            foreach ($data as $k => $v) {

                $csu_info     = $model_course_subject_user->where('id', $v)->limit(1)->select()[0];
                $student_info = $model_student_edu->where('edu_id', $csu_info['edu_id'])->limit(1)->select()[0];
                $up_csu       = $model_course_subject_user->where('id', $v)->update(['study_status' => 5]);

                $up_cso = Db::table('mbis_course_subject_cert')->insert([
                    'major_id'               => $student_info['major_id'],
                    'user_id'                => $csu_info['user_id'],
                    'student_no'             => $student_no,
                    'course_subject_user_id' => $v,
                    'subject_id'             => $csu_info['subject_id'],
                    'subject_no'             => $csu_info['subject_no'],
                    'course_bn'              => $student_info['course_bn'],
                    'course_id'              => $csu_info['course_id'],
                    'date'                   => $date,
                    'cert_path'              => $cert_path_url,
                    'cert_name'              => $cert_name,
                    'cert_type'              => $cert_type,
                    'cert_number'            => $cert_number,
                    'created_at'             => date('Y-m-d H:i:s', time())
                ]);

                if ($up_cso == false || $up_csu == false) {
                    throw new LogicException('毕业失败！', 1);
                }
            }

            Db::commit();
            return api_response(0);
        } catch (Exception $e) {
            Db::rollback();
            if ($e instanceof LogicException) {
                return api_response($e->getCode(), $e->getMessage());
            }
            return api_response(-1);
        }

    }

    /**
     * 毕业证书上传
     * @return \think\response\Json
     */
    public function upload_cert_img()
    {
        try {
            $number = trim(input('post.number'));

            $file = $this->request->file('file');

            if ($file->isValid() == false) {
                throw new LogicException('文件存在错误！', 1);
            }

            $save_file = custom_upload_image($file, mk_file_name($file->getPathname()), 1);

            if ($save_file['bool'] == false) {
                throw new LogicException($save_file['message'], 1);
            }

            $file_name = str_replace(ROOT_PATH.'public/', '', $save_file['data']->getPathName());


            return api_response(0, "", ['cert_url' => $file_name]);

        } catch (Exception $e) {

            if ($e instanceof LogicException) {

                return api_response($e->getCode(), $e->getMessage());
            }

            return api_response(-1);
        }
    }

    /**
     * 退课登记
     */
    public function cancel_course()
    {

        return $this->commUserInfo(6);
    }

    /**
     * 退课登记-查看退课详情
     * @return \think\response\Json
     */
    public function cancel_course_info()
    {

        try {
            $number = trim(input('post.number'));

            if (strlen($number) < 0 || $this->checkNumberExt($number) == false) {
                throw new LogicException('学号不存在！', 1);
            }

            $model_student_edu         = new StudentEdu();
            $model_course_subject_user = new CourseSubjectUser();
            $user                      = new Users();

            $result['data_one'] = $model_student_edu->alias('mse')
                ->field("mse.type_id, 'start_study_date', 'end_study_date', mme.major_number, mse.major_name, mse.course_bn, mse.course_name, mc.course_hours ,mse.userId,mse.course_id,mse.edu_id")
                ->where('mse.type_id IN ( 2, 3 )')
                ->where('mu.student_no', $number)
                ->join('mbis_course mc', ' mc.course_id = mse.course_id', 'LEFT')
                ->join('mbis_major_edu mme', ' mme.major_id = mse.major_id', 'LEFT')
                ->join('mbis_users mu', ' mu.userId = mse.userId')
                ->select();

            foreach ($result['data_one'] as $k => $v) {
                $tmp                               = $v->toArray();
                $result['data_one'][$k]['details'] =

                    //@todo:之后其他的 也要更改成这种方式，通过edu_id
                    $model_course_subject_user->alias('mcsu')
                        ->field("ms.subject_no, ms.`name`, ms.course_hours, ms.sale_price,mcsu.subject_id,mcsu.id")
                        ->where('mcsu.edu_id', $tmp['edu_id'])
                        ->where('mcsu.user_id', $tmp['userId'])
                        ->where('mcsu.study_status', 'in', '(0,1,3)')
                        ->join('mbis_subject ms', ' ms.subject_id = mcsu.subject_id')
                        ->select();

                $result['data_one'][$k]['type_id'] = $user->getSubjectType($tmp['type_id']);
                $result['data_one'][$k]            = $result['data_one'][$k]->toArray();
            }

            $result['data_two'] = $model_student_edu->alias('mse')
                ->field("mp.receiptNo, mu.student_no, mu.trueName, mp.receiptCate,mp.receiptDate, mp.realPayFee")
                ->where('mse.type_id IN ( 2, 3 )')
                ->where('mu.student_no', $number)
                ->join('mbis_users mu', ' mu.userId = mse.userId')
                ->join('mbis_payments mp', ' mp.userId=mse.userId')
                ->select();

            $result['data_three'] = $model_student_edu->alias('mse')
                ->field(" mcscl.student_no, mcscl.refund_number, mcscl.date, mcscl.price, mcscl.class_number, mcscl.class_name,mu.trueName")
                ->where('mse.type_id IN ( 2, 3 )')
                ->where('mu.student_no', $number)
                ->join('mbis_users mu', ' mu.userId = mse.userId')
                ->join('mbis_course_subject_cancel_log mcscl', ' mcscl.user_id=mse.userId')
                ->select();

            $this->assign('result', $result);

            $html_content = $this->fetch('cancel_course_info');

            return api_response(0, '', ['html_content' => $html_content]);
        } catch (Exception $e) {

            if ($e instanceof LogicException) {

                return api_response($e->getCode(), $e->getMessage());
            }

            return api_response(-1);
        }
    }

    /**
     * 退课保存-查看退课详情
     * @return \think\response\Json
     */
    public function cancel_course_exec()
    {
        try {

            Db::startTrans();

            $student_no = input('post.student_no');

            $data = input('post.data');

            $data = explode(',', $data);

            $staff = $_SESSION['MBISMART_']['MBIS_STAFF'];

            $refund_number = input('post.refund_number');

            $model_course_subject_user = new CourseSubjectUser();

            $model_student_edu = new StudentEdu();

            foreach ($data as $k => $v) {

                $csu_info     = $model_course_subject_user->where('id', $v)->limit(1)->select()[0];
                $student_info = $model_student_edu->where('edu_id', $csu_info['edu_id'])->limit(1)->select()[0];
                $up_csu       = $model_course_subject_user->where('id', $v)->update(['study_status' => 6]);

                $up_cso = Db::table('mbis_course_subject_cancel_log')->insert([
                    'refund_number'             => $refund_number,
                    'major_id'                  => $student_info['major_id'],
                    'user_id'                   => $csu_info['user_id'],
                    'subject_id'                => $csu_info['subject_id'],
                    'course_id'                 => $csu_info['course_id'],
                    'student_no'                => $student_no,
                    'course_subject_user_id'    => $v,
                    'subject_no'                => $csu_info['subject_no'],
                    'course_bn'                 => $student_info['course_bn'],
                    'date'                      => time(),
                    'pre_study_status'          => $csu_info['study_status'],
                    'subject_course_count_hour' => $csu_info['subject_course_hour'],
                    'work_number'               => $staff['work_number'],
                    'work_name'                 => $staff['work_name'],
                    //退费金额=科目总价（总价要根据当前用户实际缴费的钱来算）-（剩余课时*单价）
                    'price'                     => $csu_info['real_price'] - $csu_info['subject_surplus_hour'] * $csu_info['subject_course_sin_price'],
                    //退课的时候，科目已缴费金额
                    'completed_price'           => $csu_info['real_price'],
                    //科目已消耗金额=科目已消耗课时*科目的课时单价 )
                    'pay_out_price'             => $csu_info['subject_used_course_hour'] * $csu_info['subject_course_sin_price'],
                    'subject_price'             => $csu_info['price'],
                    'subject_course_hour'       => $csu_info['subject_used_course_hour'],
                    'class_id'                  => $csu_info['class_id'],
                    'school_number'             => $student_info['school_code'],
                    'school_name'               => $student_info['school_name'],
                    'created_at'                => date('Y-m-d H:i:s', time()),
                ]);

                if ($up_cso == false || $up_csu == false) {
                    throw new LogicException('退课失败！', 1);
                }
            }

            Db::commit();
            return api_response(0);
        } catch (Exception $e) {
            Db::rollback();
            if ($e instanceof LogicException) {
                return api_response($e->getCode(), $e->getMessage());
            }
            return api_response(-1);
        }
    }

    /**
     * 转课登记
     * @return mixed
     */
    public function transfer_course()
    {
        return $this->commUserInfo(7);
    }

    /**
     * 转课登记-查看转课详情
     * @return \think\response\Json
     */
    public function transfer_course_info()
    {

        try {
            $number = trim(input('post.number'));

            if (strlen($number) < 0 || $this->checkNumberExt($number) == false) {
                throw new LogicException('学号不存在！', 1);
            }

            $model_student_edu = new StudentEdu();

            $model_course_subject_user = new CourseSubjectUser();
            $user                      = new Users();

            $result['data_one'] = $model_student_edu->alias('mse')
                ->field("mse.type_id, 'start_study_date', 'end_study_date', mme.major_number, mse.major_name, mse.course_bn, mse.course_name, mc.course_hours ,mse.userId,mse.course_id,mse.edu_id")
                ->where('mse.type_id IN ( 2, 3 )')
                ->where('mu.student_no', $number)
                ->join('mbis_course mc', ' mc.course_id = mse.course_id', 'LEFT')
                ->join('mbis_major_edu mme', ' mme.major_id = mse.major_id', 'LEFT')
                ->join('mbis_users mu', ' mu.userId = mse.userId')
                ->select();

            foreach ($result['data_one'] as $k => $v) {
                foreach ($result['data_one'] as $k => $v) {
                    $tmp                               = $v->toArray();
                    $result['data_one'][$k]['details'] =

                        //@todo:之后其他的 也要更改成这种方式，通过edu_id
                        $model_course_subject_user->alias('mcsu')
                            ->field("ms.subject_no, ms.`name`, ms.course_hours, ms.sale_price,mcsu.subject_id,mcsu.id")
                            ->where('mcsu.edu_id', $tmp['edu_id'])
                            ->where('mcsu.user_id', $tmp['userId'])
                            ->where('mcsu.study_status', 'in', '(1,2,3,4)')
                            ->join('mbis_subject ms', ' ms.subject_id = mcsu.subject_id')
                            ->select();

                    $result['data_one'][$k]['type_id'] = $user->getSubjectType($tmp['type_id']);
                    $result['data_one'][$k]            = $result['data_one'][$k]->toArray();
                }

                $this->assign('result', $result);

//                $cert_type = $this->getCertType();
//
//                $this->assign('cert_type', $cert_type);

//                $transfer_course_school = $this->getTransferCourseSchool();
//
//                $this->assign('transfer_course_school', $transfer_course_school);
//
//                $transfer_course_major = $this->getTransferCourseMajor();
//
//                $this->assign('transfer_course_major', $transfer_course_major);

                $html_content = $this->fetch('transfer_course_info');

                return api_response(0, '', ['html_content' => $html_content]);
            }
        } catch (Exception $e) {

            if ($e instanceof LogicException) {

                return api_response($e->getCode(), $e->getMessage());
            }

            return api_response(-1);
        }
    }

    /**
     *转课登记-转课操作
     */
    public function transfer_course_exec()
    {
        try {

            Db::startTrans();

            $student_no = input('post.student_no');

            $data = input('post.data');

            $data = explode(',', $data);

            $staff = $_SESSION['MBISMART_']['MBIS_STAFF'];

            $transfer_number = input('post.transfer_number');

            $model_course_subject_user = new CourseSubjectUser();

            $model_student_edu = new StudentEdu();


            foreach ($data as $k => $v) {

                $csu_info     = $model_course_subject_user->where('id', $v)->limit(1)->select()[0];
                $student_info = $model_student_edu->where('edu_id', $csu_info['edu_id'])->limit(1)->select()[0];
                $up_csu       = $model_course_subject_user->where('id', $v)->update(['study_status' => 4]);

                $up_cso = Db::table('mbis_course_subject_transfer_log')->insert([
                    'transfer_number'           => $transfer_number,
                    'major_id'                  => $student_info['major_id'],
                    'user_id'                   => $csu_info['user_id'],
                    'student_no'                => $student_no,
                    'course_subject_user_id'    => $v,
                    'subject_id'                => $csu_info['subject_id'],
                    'subject_no'                => $csu_info['subject_no'],
                    'course_bn'                 => $student_info['course_bn'],
                    'course_id'                 => $csu_info['course_id'],
                    'subject_balance'           => $csu_info['real_price'] - $csu_info['subject_surplus_hour'] * $csu_info['subject_course_sin_price'],
                    'subject_course_count_hour' => $csu_info['subject_course_hour'],
                    'subject_course_hour'       => $csu_info['subject_used_course_hour'],
                    'pay_out_price'             => $csu_info['subject_used_course_hour'] * $csu_info['subject_course_sin_price'],
                    'apply_transfer_time'       => time(),
                    'work_number'               => $staff['work_number'],
                    'work_name'                 => $staff['work_name'],
                    'pre_study_status'          => $csu_info['study_status'],
                    'created_at'                => date('Y-m-d H:i:s', time())
                ]);

                if ($up_cso == false || $up_csu == false) {
                    throw new LogicException('转课失败！', 1);
                }
            }

            Db::commit();
            return api_response(0);
        } catch (Exception $e) {
            Db::rollback();
            if ($e instanceof LogicException) {
                return api_response($e->getCode(), $e->getMessage());
            }
            return api_response(-1);
        }
    }

    public function transfer_course_select_exec()
    {

        $major_id = input('post.major_id');

        $course_id = input('post.course_id');

        $result = new \stdClass();

        if (strlen($major_id) > 0) {
            $result = Course::field('course_id,name')->where('major_id', $major_id)->select();
        }

        if (strlen($course_id) > 0) {
            $result = CourseSubject::alias('cs')
                ->field('cs.subject_id,ms.name')
                ->where('course_id', $course_id)
                ->join('mbis_subject ms', ' ms.subject_id = cs.subject_id')
                ->select();
        }

        return api_response(0, '', $result);
    }

    /**
     * 学籍档案
     */
    public function student_record()
    {
        return $this->commUserInfo(8);
    }

    /**
     * 学籍档案-学籍详情
     */
    public function student_record_info()
    {
        try {
            $number = trim(input('post.number'));

            if (strlen($number) < 0 || $this->checkNumberExt($number) == false) {
                throw new LogicException('学号不存在！', 1);
            }

            $model_student_edu = new StudentEdu();

            $model_course_subject_user = new CourseSubjectUser();
            $user                      = new Users();


            $result['data_one'] = $model_student_edu->alias('mse')
                ->field("mse.type_id, 'start_study_date', 'end_study_date', mme.major_number, mse.major_name, mse.course_bn, mse.course_name, mc.course_hours ,mse.userId,mse.course_id,mse.edu_id")
                ->where('mse.type_id IN ( 2, 3 )')
                ->where('mu.student_no', $number)
                ->join('mbis_course mc', ' mc.course_id = mse.course_id', 'LEFT')
                ->join('mbis_major_edu mme', ' mme.major_id = mse.major_id', 'LEFT')
                ->join('mbis_users mu', ' mu.userId = mse.userId')
                ->select();

            foreach ($result['data_one'] as $k => $v) {
                $tmp                               = $v->toArray();
                $result['data_one'][$k]['details'] =
                    //@todo:之后其他的 也要更改成这种方式，通过edu_id
                    $model_course_subject_user->alias('mcsu')
                        ->field("ms.subject_no, ms.`name`, ms.course_hours, ms.sale_price,mcsu.subject_id,mcsu.id")
                        ->where('mcsu.edu_id', $tmp['edu_id'])
                        ->where('mcsu.user_id', $tmp['userId'])
                        ->where('mcsu.study_status', 'in', '(1,2,3,4)')
                        ->join('mbis_subject ms', ' ms.subject_id = mcsu.subject_id')
                        ->select();

                $result['data_one'][$k]['type_id'] = $user->getSubjectType($tmp['type_id']);
                $result['data_one'][$k]            = $result['data_one'][$k]->toArray();
            }

            $result['data_two'] = $model_student_edu->alias('mse')
                ->field("mp.receiptNo, mu.student_no, mu.trueName, mp.receiptCate,mp.receiptDate, mp.realPayFee")
                ->where('mse.type_id IN ( 2, 3 )')
                ->where('mse.userId', $tmp['userId'])
                ->join('mbis_users mu', ' mu.userId = mse.userId')
                ->join('mbis_payments mp', ' mp.userId=mse.userId')
                ->select();

            $result['data_three'] = $model_student_edu->alias('mse')
                ->field(" mcscl.student_no, mcscl.refund_number, mcscl.date, mcscl.price, mcscl.class_number, mcscl.class_name,mu.trueName")
                ->where('mse.type_id IN ( 2, 3 )')
                ->where('mu.student_no', $number)
                ->join('mbis_users mu', ' mu.userId = mse.userId')
                ->join('mbis_course_subject_cancel_log mcscl', ' mcscl.user_id=mse.userId')
                ->select();

//            $result['data_four'] = $model_student_edu->alias('mse') ->filed('')
//                ->where('mse.type_id IN ( 2, 3 )')
//                ->where('mse.userId', $tmp['userId'])
//                ->select();


            $this->assign('result', $result);

            $html_content = $this->fetch('student_record_info');

            return api_response(0, '', ['html_content' => $html_content]);
        } catch (Exception $e) {
            $this->this_debug($e);

            if ($e instanceof LogicException) {

                return api_response($e->getCode(), $e->getMessage());
            }

            return api_response(-1);
        }
    }

    #endregion

    #region 通用方法

    /**
     * 获取所有的校区
     * @return array
     */
    private function getTransferCourseSchool()
    {
        return \application\admin\model\Department::getSchools();
    }

    private function getTransferCourseMajor()
    {
        return Major::where('type_id IN ( 2, 3 )')->select();
    }

    private function this_debug($e)
    {
        var_dump($e->getMessage());
        var_dump($e->getCode());
        var_dump($e->getFile());
        dd($e->getLine());
    }

    /**
     * 毕业证书类型
     * @return array
     */
    private function getCertType()
    {
        return [
            1 => '学历证书',
            2 => '职业证书',
            3 => '培训证书',
            4 => '职称证书',
            5 => '资格证书',
        ];
    }

    /**
     * 学历 技能 管理
     */
    private function getTypeIds()
    {
        return \application\admin\model\SubjectType::getTypes();
    }

    /**
     * 通用用户信息
     *
     * @param $type 1是基础身份，2：是加报查询
     *
     * @return mixed
     */
    private function commUserInfo($type = 1)
    {
        $result = [];

        $number       = trim(input('get.number'));
        $card_number  = trim(input('get.card_number'));
        $phone_number = trim(input('get.phone_number'));

        if (strlen($number) > 0) {
            $result = Users::alias('users')
                ->where('users.student_no', $number)
//                ->where('mse.type_id IN ( 2, 3 )')
                ->join('mbis_student_edu mse',
                    'mse.userId = users.userId')->find();
            $this->assign('number', $number);
        }

        if (strlen($card_number) > 0) {
            $result = Users::alias('users')
                ->where('users.idcard', $card_number)
//                ->where('mse.type_id IN ( 2, 3 )')
                ->join('mbis_student_edu mse', 'mse.userId = users.userId')->find();
            $this->assign('card_number', $card_number);
        }

        if (strlen($phone_number) > 0) {
            $result = Users::alias('users')
                ->where('users.userPhone', $phone_number)
//                ->where('mse.type_id IN ( 2, 3 )')
                ->join('mbis_student_edu mse',
                    'mse.userId = users.userId')->find();


            $this->assign('phone_number', $phone_number);
        }

        if (count($result) > 0) {
            $users = new Users();
            $this->assign('all_study_status', $users->get_all_study_status());
            $this->assign('all_uid_type', $users->get_all_uidType());
        }

        switch ($type) {
            case 1:
                $list = [
                    'id'     => 'info_button',
                    'remark' => '查看课程与费用信息',
                    'title'  => '基础身份'
                ];
                break;
            case 2:
                $list = [
                    'id'     => 'append_info_button',
                    'remark' => '查看加报课程与加报费用',
                    'title'  => '加报查询'
                ];
                break;
            case 3:
                $list = [
                    'id'     => 'out_school_info_button',
                    'remark' => '查看休学课程与休学前费用信息',
                    'title'  => '休学登记'
                ];
                break;
            case 4:
                $list = [
                    'id'     => 're_school_info_button',
                    'remark' => '查看复学课程与复学费用信息',
                    'title'  => '休学复学'
                ];
                break;
            case 5:
                $list = [
                    'id'     => 'end_school_info_button',
                    'remark' => '查看课程和证件',
                    'title'  => '毕业登记'
                ];
                break;
            case 6:
                $list = [
                    'id'     => 'cancel_course_info_button',
                    'remark' => '查看退课详情',
                    'title'  => '退课登记'
                ];
                break;
            case 7:
                $list = [
                    'id'     => 'transfer_course_info_button',
                    'remark' => '查看转课详情',
                    'title'  => '转课登记'
                ];
                break;
            case 8:
                $list = [
                    'id'     => 'student_record_info_button',
                    'remark' => '查看档案详情',
                    'title'  => '学籍档案'
                ];
                break;
        }


        $this->assign('list', $list);

        $this->assign('result', $result);

        return $this->fetch('list');
    }

    /**
     * 检查学号是否存在
     *
     * @param $number 学号
     *
     * @return bool
     */
    private function checkNumberExt($number)
    {

        if (Users::where('student_no', $number)->count() < 1) {

            return false;
        }
        return true;
    }

    #endregion


    #region  统计

    /**
     * 学员学习类型统计
     * @return mixed
     */
    public function study_type_statistics()
    {

        $start_date = strlen(input('get.start_date')) > 0 ? strtotime(input('get.start_date')) : null;

        $end_date = strlen(input('get.end_date')) > 0 ? strtotime(input('get.end_date')) : null;

        $school_id         = input('get.school_id');
        $type_id           = input('get.type_id');
        $model_student_edu = new StudentEdu();
        $result            = $model_student_edu->alias('mse')
            ->field("mse.type_id,count(mse.edu_id) as count_edu")
            ->group('mse.type_id');

        if ($start_date != null) {
            $result = $result->where('mse.entry_time', '>', $start_date);
            $this->assign('start_date', date('Y-m-d', $start_date));
        }
        if ($end_date != null) {
            $result = $result->where('mse.entry_time', '<=', $end_date);
            $this->assign('end_date', date('Y-m-d', $end_date));
        }
        if (strlen($school_id) > 0) {
            $result = $result->where('mse.school_id', $school_id);
            $this->assign('school_id', $school_id);
        }
        if (strlen($type_id) > 0) {
            $result = $result->where('mse.type_id', $type_id);
            $this->assign('type_id', $type_id);
        }

        $result = $result->select();

        $user = new Users();
        foreach ($result as $k => $v) {
            $result[$k]['type_name'] = $user->getSubjectType($v['type_id']);
        }

        $course_school = $this->getTransferCourseSchool();
        $type_ids      = $this->getTypeIds();

        $this->assign('type_ids', $type_ids);
        $this->assign('course_school', $course_school);
        $this->assign('result', json_encode($result));

        return $this->fetch(__FUNCTION__);
    }

    /**
     * 学员具体课程统计
     * @return mixed
     */
    public function study_course_statistics()
    {
        $start_date = strlen(input('get.start_date')) > 0 ? strtotime(input('get.start_date')) : null;

        $end_date = strlen(input('get.end_date')) > 0 ? strtotime(input('get.end_date')) : null;

        $school_id         = input('get.school_id');
        $type_id           = input('get.type_id');
        $page              = input('get.page') ? input('get.page') : 1;
        $model_student_edu = new StudentEdu();

        $subQuery = $model_student_edu->field("course_id,count(userId) as count_userId")->group('course_id');

        if ($start_date != null) {
            $subQuery = $subQuery->where('entry_time', '>', $start_date);
            $this->assign('start_date', date('Y-m-d', $start_date));
        }

        if ($end_date != null) {
            $subQuery = $subQuery->where('entry_time', '<=', $end_date);
            $this->assign('end_date', date('Y-m-d', $end_date));
        }

        if (strlen($school_id) > 0) {
            $subQuery = $subQuery->where('school_id', $school_id);
            $this->assign('school_id', $school_id);
        }

        if (strlen($type_id) > 0) {
            $subQuery = $subQuery->where('type_id', $type_id);
            $this->assign('type_id', $type_id);
        }

        $subQuerySql = $subQuery->buildSql();

        $result = $subQuery->setTable($subQuerySql.'as t')->field('t.*,(SELECT name FROM mbis_course WHERE t.course_id = course_id) AS course_name,(SELECT name FROM mbis_course WHERE t.course_id = course_id) AS major_name');

        $result = $result->paginate(10, false, [
            'type'      => 'bootstrap',
            'var_page'  => 'page',
            'list_rows' => 10,
            'query'     => [
                'school_id'  => $school_id,
                'type_id'    => $type_id,
                'start_date' => $start_date,
                'end_date'   => $end_date,
            ]
        ]);
        $list   = $result->render();

        $course_school = $this->getTransferCourseSchool();
        $type_ids      = $this->getTypeIds();

        $this->assign('page', $list);
        $this->assign('type_ids', $type_ids);
        $this->assign('course_school', $course_school);
        $this->assign('result', $result);

        return $this->fetch(__FUNCTION__);
    }

    /**
     * 学员具体科目统计
     * @return mixed
     */
    public function study_course_subject_statistics()
    {
        $start_date = strlen(input('get.start_date')) > 0 ? strtotime(input('get.start_date')) : null;

        $end_date = strlen(input('get.end_date')) > 0 ? strtotime(input('get.end_date')) : null;

        $school_id                 = input('get.school_id');
        $major_id                  = input('get.major_id');
        $type_id                   = input('get.type_id');
        $course_id                 = input('get.course_id');
        $page                      = input('get.page') ? input('get.page') : 1;
        $model_course_subject_user = new CourseSubjectUser();

        $subQuery = $model_course_subject_user->alias('mcsu')->field("count(subject_id) AS count_user, (SELECT NAME FROM mbis_subject WHERE mcsu.subject_id = subject_id) AS 'subject_name', mc.name AS 'course_name', mm.name AS 'major_name', mse.type_id, md.name as 'department_name'")->group('mcsu.subject_id');

        if ($start_date != null) {
            $subQuery = $subQuery->where('mse.entry_time', '>', $start_date);
            $this->assign('start_date', date('Y-m-d', $start_date));
        }

        if ($end_date != null) {
            $subQuery = $subQuery->where('mse.entry_time', '<=', $end_date);
            $this->assign('end_date', date('Y-m-d', $end_date));
        }

        if (strlen($school_id) > 0) {
            $subQuery = $subQuery->where('mse.department_id', $school_id);
            $this->assign('school_id', $school_id);
        }

        if (strlen($type_id) > 0) {
            $subQuery = $subQuery->where('mse.type_id', $type_id);
            $this->assign('type_id', $type_id);
        }

        if (strlen($major_id) > 0) {
            $subQuery = $subQuery->where('mse.major_id', $major_id);
            $this->assign('major_id', $major_id);
        }

        if (strlen($course_id) > 0) {
            $subQuery = $subQuery->where('mse.course_id', $course_id);
            $this->assign('course_id', $course_id);
        }

        $result = $subQuery->join('mbis_student_edu mse', 'mse.edu_id = mcsu.edu_id')
            ->join('mbis_course mc', 'mc.course_id = mse.course_id')
            ->join('mbis_major mm', 'mm.major_id = mse.major_id')
            ->join('mbis_department md', 'md.department_id = mse.department_id');

        $result = $result->paginate(10, false, [
            'type'      => 'bootstrap',
            'var_page'  => 'page',
            'list_rows' => 10,
            'query'     => [
                'start_date' => $start_date,
                'end_date'   => $end_date,
                'school_id'  => $school_id,
                'type_id'    => $type_id,
                'major_id'   => $major_id,
                'course_id'  => $course_id,
            ]
        ]);

        $list          = $result->render();
        $course_school = $this->getTransferCourseSchool();
        $type_ids      = $this->getTypeIds();

        $majors = [];
        if (strlen($type_id) > 0) {
            $majors = Db::table('mbis_major')->field('name as major_name,major_id')->where('type_id',
                $type_id)->select();
        }

        $courses = [];
        if (strlen($major_id) > 0 && strlen($type_id) > 0) {
            $courses = Db::table('mbis_course')->field('course_id,name as course_name')->where('type_id',
                $type_id)->where('major_id', $major_id)->select();
        }
        $user = new Users();
        foreach ($result as $k => $v) {
            $result[$k]['type_name'] = $user->getSubjectType($v['type_id']);
        }

//        dd($courses);


        $this->assign('majors', $majors);
        $this->assign('courses', $courses);
        $this->assign('page', $list);
        $this->assign('type_ids', $type_ids);
        $this->assign('course_school', $course_school);
        $this->assign('result', $result);

        return $this->fetch(__FUNCTION__);
    }

    /**
     * 学员课时消耗统计
     * @return mixed
     */
    public function study_course_hour_statistics()
    {
        $start_date = strlen(input('get.start_date')) > 0 ? strtotime(input('get.start_date')) : null;

        $end_date = strlen(input('get.end_date')) > 0 ? strtotime(input('get.end_date')) : null;

        $edu_id                    = input('get.edu_id');
        $student_no                = input('get.student_no');
        $model_course_subject_user = new CourseSubjectUser();
        $courses                   = [];

        $result = $model_course_subject_user->alias('mcsu')
            ->field('sum(mcsu.subject_used_course_hour) as \'such\',(select name from mbis_subject where mcsu.subject_id=subject_id ) subject_name')
            ->group('mcsu.subject_id');

        if ($start_date != null) {
            $result = $result->where('(select entry_time from mbis_student_edu where mcsu.edu_id=mcsu.edu_id)', '>',
                $start_date);
            $this->assign('start_date', date('Y-m-d', $start_date));
        }

        if ($end_date != null) {
            $result = $result->where('(select entry_time from mbis_student_edu where mcsu.edu_id=mcsu.edu_id)', '<=',
                $end_date);
            $this->assign('end_date', date('Y-m-d', $end_date));
        }

        if (strlen($student_no) > 0) {

            $user_info = Users::where('student_no', $student_no)->field('userId,trueName')->select()[0]->toArray();

            $courses = StudentEdu::where('userId', $user_info['userId'])->field('edu_id,course_name')->select();

            $result = $result->where('mcsu.user_id', $user_info['userId']);

            $this->assign('student_no', $student_no);
            $this->assign('trueName', $user_info['trueName']);
        }

        if (strlen($edu_id) > 0) {
            $result = $result->where('mcsu.edu_id', $edu_id);
            $this->assign('edu_id', $edu_id);
        }

        $this->assign('courses', $courses);

        $result = $result->select();

        $this->assign('result', json_encode($result));

        return $this->fetch(__FUNCTION__);
    }

    /**
     * 学员加报统计
     * @return mixed
     */
    public function study_renew_statistics()
    {
        $start_date = strlen(input('get.start_date')) > 0 ? strtotime(input('get.start_date')) : null;

        $end_date = strlen(input('get.end_date')) > 0 ? strtotime(input('get.end_date')) : null;

        $student_no        = input('get.student_no');
        $model_student_edu = new StudentEdu();
        $courses           = [];

        $subQuery = $model_student_edu->alias('mse')
            ->field('sum(mse.total_price) AS total_price,mse.course_id')
            ->group('mse.course_id');

        if ($start_date != null) {
            $subQuery = $subQuery->where('mse.entry_time', '>', $start_date);
            $this->assign('start_date', date('Y-m-d', $start_date));
        }

        if ($end_date != null) {
            $subQuery = $subQuery->where('mse.entry_time', '<=', $end_date);
            $this->assign('end_date', date('Y-m-d', $end_date));
        }

        if (strlen($student_no) > 0) {

            $user_info = Users::where('student_no', $student_no)->field('userId,trueName')->select()[0]->toArray();
            $subQuery  = $subQuery->where('mse.userId', $user_info['userId']);

            $this->assign('student_no', $student_no);
            $this->assign('trueName', $user_info['trueName']);
        }
        $subQuery = $subQuery->buildSql();

        $result = Db::query("SELECT t.*, (SELECT name FROM mbis_course WHERE t.course_id = course_id) AS 'course_name' FROM ".$subQuery." AS t");


        $this->assign('result', json_encode($result));

        return $this->fetch(__FUNCTION__);
    }

    /**
     * @todo: 由于考勤还未介入，所以暂时没有学员考勤统计
     * 学员考勤统计
     * @return mixed
     */
    public function study_assess_statistics()
    {
        return $this->fetch(__FUNCTION__);
    }

    /**
     * 学员身份统计
     * @return mixed
     */
    public function study_identity_statistics()
    {
        $start_date = strlen(input('get.start_date')) > 0 ? strtotime(input('get.start_date')) : null;

        $end_date = strlen(input('get.end_date')) > 0 ? strtotime(input('get.end_date')) : null;

        $school_id = input('get.school_id');

        $type_id = input('get.type_id');

        $result = new StudentEdu();
        $result = $result->alias('mse')
            ->field('count(mcsu.edu_id) count_edu,mcsu.study_status')
            ->join('mbis_course_subject_user mcsu', ' mcsu.edu_id = mse.edu_id')
            ->group('mcsu.study_status');


        if ($start_date != null) {
            $result = $result->where('mse.entry_time', '>', $start_date);
            $this->assign('start_date', date('Y-m-d', $start_date));
        }

        if ($end_date != null) {
            $result = $result->where('mse.entry_time', '<=', $end_date);
            $this->assign('end_date', date('Y-m-d', $end_date));
        }

        if ($school_id != null) {
            $result = $result->where('mse.school_id', $school_id);
            $this->assign('school_id', $school_id);
        }
        if ($type_id != null) {
            $result = $result->where('mse.type_id', $type_id);
            $this->assign('type_id', $type_id);
        }

        $result = $result->select();

        if (count($result) > 0) {
            $result = $result[0]->toArray();
        }

        $course_school = $this->getTransferCourseSchool();
        $type_ids      = $this->getTypeIds();

        $this->assign('type_ids', $type_ids);
        $this->assign('course_school', $course_school);

        $this->assign('result', json_encode($result));

        return $this->fetch(__FUNCTION__);
    }


    /*
     * 读取学员信息和所报课程
     */
    public function student_info()
    {
        $student_no = input('post.student_no');

        $user_info = Users::where('student_no', $student_no)->field('userId,trueName')->select()[0]->toArray();


        $result['courses'] = StudentEdu::where('userId',
            $user_info['userId'])->field('edu_id,course_name')->select();

        $result['name'] = $user_info['trueName'];

        return api_response(0, '', $result);

        /*
                //类型，1是读取课程，2：是读取科目
                $type = input('post.type');

                $student_no = input('post.student_no');

                $user_info = Users::where('student_no', $student_no)->field('userId,trueName')->select();

                if ($type == 1) {
                    $result['courses'] = StudentEdu::where('userId', $user_info['userId'])->field('edu_id,course_name')->get();

                    $result['name'] = $user_info['userId'];

                    return api_response(0, '', $result);
                }

                if ($type == 2) {
                    $edu_id = input('post.edu_id');

                    $subjects = CourseSubjectUser::alias('mcsu')->where('edu_id',
                        $edu_id)->where('user_id',
                        $user_info['userId'])->field('subject_name,id,(SELECT name FROM mbis_subject WHERE mcsu.subject_id = course_id) AS course_name)')->get();
                    return api_response(0, '', $subjects);
                }
        */
    }

    /**
     * 课程 专业 科目 关联查询
     * couser_major_subject
     */

    public function couser_major_subject()
    {
        $type_id = input('post.type_id');

        $major_id = input('post.major_id');


        $result = [];
        if (strlen($type_id) > 0 && strlen($major_id) > 0) {

            //通过专业以及学习类型查询课程
            $result = Db::table('mbis_course')->field('course_id,name as course_name')->where('type_id',
                $type_id)->where('major_id', $major_id)->select();

        }

        if (strlen($type_id) > 0) {
            //通过学习类型查询专业
            $result = Db::table('mbis_major')->field('major_id,name major_name')->where('type_id', $type_id)->select();

        }

        return api_response(0, '', $result);

    }


    #endregion

}
