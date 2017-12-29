<?php
namespace application\admin\model;
use think\Db;
/**
 * 操作日志业务处理
 */
class LogOperates extends Base{
    /**
	 * 分页
	 */
	public function pageQuery(){
		$startDate = input('get.startDate');
		$endDate = input('get.endDate');
		$where = [];
		if($startDate!='')$where['l.operateTime'] = ['>=',$startDate." 00:00:00"];
		if($endDate!='')$where[' l.operateTime'] = ['<=',$endDate." 23:59:59"];
		return $mrs = Db::name('log_operates')->alias('l')->join('__STAFFS__ s',' l.staffId=s.staffId','left')
		    ->join('__MENUS__ m',' l.menuId=m.menuId','left')
			->where($where)
			->field('l.*,s.staffName,m.menuName')
			->order('l.operateId', 'desc')->paginate(input('pagesize/d'));
			
	}
	
	/**
	 * 新增操作权限
	 */
	public function add($param){
		$data = [];
		$data['staffId'] = (int)session('MBIS_STAFF.staffId');
		$data['operateTime'] = date('Y-m-d H:i:s');
		$data['menuId'] = $param['menuId'];
		$data['operateDesc'] = $param['operateDesc'];
		$data['content'] = $param['content'];
		$data['operateUrl'] = $param['operateUrl'];
		$data['operateIP'] = $param['operateIP'];
		$this->create($data);
	}
	
	/**
	 *  获取指定的操作记录
	 */
	public function getById($id){
		$rs = $this->get($id);
		if(!empty($rs)){
			return MBISReturn('', 1,$rs);
		}
		return MBISReturn('对不起，没有找到该记录', -1);
	}
}
