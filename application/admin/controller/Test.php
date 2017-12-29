<?php

namespace application\admin\controller;
use think\Loader;
use application\admin\model\Course as M;

class Test extends Base
{
    public function index()
    {
        return 'ADMIN API ...';
        return '<style type="text/css">*{ padding: 0; margin: 0; } .think_default_text{ padding: 4px 48px;} a{color:#2E5CD5;cursor: pointer;text-decoration: none} a:hover{text-decoration:underline; } body{ background: #fff; font-family: "Century Gothic","Microsoft yahei"; color: #333;font-size:18px} h1{ font-size: 100px; font-weight: normal; margin-bottom: 12px; } p{ line-height: 1.6em; font-size: 42px }</style><div style="padding: 24px 48px;"> <h1>:)</h1><p> ThinkPHP V5<br/><span style="font-size:30px">十年磨一剑 - 为API开发设计的高性能框架</span></p><span style="font-size:22px;">[ V5.0 版本由 <a href="http://www.qiniu.com" target="qiniu">七牛云</a> 独家赞助发布 ]</span></div><script type="text/javascript" src="http://tajs.qq.com/stats?sId=9347272" charset="UTF-8"></script><script type="text/javascript" src="http://ad.topthink.com/Public/static/client.js"></script><thinkad id="ad_bd568ce7058a1091"></thinkad>';
    }

    public function dowload()
    {
        $head = ['课程名称', '课程编号', '专业名称', '科目项'];
        $m = new M();
        $courseSubject = model('CourseSubject');
        $major = model('major');
        $where = ['closed' => ['<>', '1'], 'type_id' => '2'];
        $field = 'course_id,name,course_bn,major_id';
        $data = $m->where($where)->field($field)->order('lastmodify desc')->select();
        $data = json_decode(json_encode($data));
        $result = [];
        foreach ($data as $key => $v) {
            $v = (array)$v;
            $result[$key]['name'] =  $v['name'];
            $result[$key]['course_bn'] =  $v['course_bn'];
            $result[$key]['major_id'] =  $major->get_name($v['major_id']);
            $result[$key]['suject'] =  $courseSubject->get_subject_names($v['course_id'], true);
            /*echo iconv('utf-8', 'gb2312', $v['name']) . "\t";
            echo iconv('utf-8', 'gb2312', $v['course_bn']) . "\t";
            echo iconv('utf-8', 'gb2312', $major->get_name($v['major_id'])) . "\t";
            echo iconv('utf-8', 'gb2312', $courseSubject->get_subject_names($v['course_id'], true)) . "\t";
            echo "\t\n";*/
        }



        $fileName = "_" . date("Y_m_d") . ".xls";
        Loader::import('phpexcel.PHPExcel');//手动引入PHPExcel.php
        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->getProperties();
        $key = ord("A"); // 设置表头
        foreach ($head as $v) {
            $colum = chr($key);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($colum . '1', $v);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($colum . '1', $v);
            $key += 1;
        }
        $column = 2;
        $objActSheet = $objPHPExcel->getActiveSheet();
        foreach ($result as $key => $rows) { // 行写入
            $span = ord("A");
            foreach ($rows as $keyName => $value) { // 列写入
                $objActSheet->setCellValue(chr($span) . $column, $value);
                $span++;
            }
            $column++;
        }
        $fileName = iconv("utf-8", "gb2312", $fileName); // 重命名表
        $objPHPExcel->setActiveSheetIndex(0); // 设置活动单指数到第一个表,所以Excel打开这是第一个表
        header('Content-Type: application/vnd.ms-excel');
        header("Content-Disposition: attachment;filename='$fileName'");
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output'); // 文件通过浏览器下载
        exit();


        /*echo "<pre>";
        var_dump($result);*/
    }
}
