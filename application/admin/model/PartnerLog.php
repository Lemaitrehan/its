<?php
namespace application\admin\model;
/**
 * 合作方结算明细业务处理
 */
use think\Db;
class PartnerLog extends Base{
	/**
	 * 分页
	 */
	public function pageQuery(){
        $key = input('get.key');
        $where = [];
        if($key!=''){
        	$p_id = $this->get_partner_id($key);
        	$where['p_id'] = ['=',"$p_id"];
        }
        $page = $this->where($where)->order('lastmodify desc')
		->paginate(input('post.pagesize/d'))->toArray();
		
		if(count($page['Rows'])>0){
			foreach($page['Rows'] as $key => $v){
				if(isset($page['Rows'][$key]['p_id'])){
					$page['Rows'][$key]['p_id'] = $this->get_partners_name($v['p_id']);
				}
				$page['Rows'][$key]['settlement_type'] = $this->get_settlement_type($v['settlement_type']);
				$page['Rows'][$key]['pay_type'] = $this->get_pay_type($v['pay_type']);
				$page['Rows'][$key]['settlement_time'] = $this->time_date($v['settlement_time']);
			}
		}
        return $page;
	}
	public function getById($id){
		if($id > 0){
			$rs = $this->get(['id' => $id]);
			$rs['settlement_time'] = $this->time_date($rs['settlement_time']);
		}else{
			$rs = $this->get(['id' => $id]);
		}
		return $rs;
	}
	/**
	 * 新增
	 */
	public function add(){
		$data = input('post.');
		$data['createtime'] = time();
        $data['lastmodify'] = time();
        $data['settlement_time'] = strtotime(input('post.settlement_time'));
        MBISUnset($data,'id');
		Db::startTrans();
		try{
			$result = $this->save($data);
	        if(false !== $result){
			    Db::commit();
	        	return MBISReturn("新增成功", 1);
	        }
		}catch (\Exception $e) {
            Db::rollback();
        }  
        return MBISReturn('新增失败',-1);
	}
    /**
	 * 编辑
	 */
	public function edit(){
		$id = (int)input('post.id');
		$data = input('post.');
        $data['lastmodify'] = time();
        $data['settlement_time'] = strtotime(input('post.settlement_time'));
		MBISUnset($data,'id');
		Db::startTrans();
		try{
		    $result = $this->save($data,['id'=>$id]);
	        if(false !== $result){
	        	Db::commit();
	        	return MBISReturn("编辑成功", 1);
	        }
	    }catch (\Exception $e) {
            Db::rollback();
        }
        return MBISReturn('编辑失败',-1);  
	}
	/**
	 * 删除
	 */
    public function del(){
	    $id = input('post.id/d');
	    Db::startTrans();
		try{
		    $result = $this->where(['id'=>$id])->delete();
	        if(false !== $result){
	        	Db::commit();
	        	return MBISReturn("删除成功", 1);
	        }
		}catch (\Exception $e) {
            Db::rollback();
            return MBISReturn('删除失败',-1);
        }
	}
	/**
	 * 合作方结算明细记录列表
	 */
	public function get_info_list(){
		$info = Db::name('partner_log')->field('*')->select();
		return $info;
	}
	/**
	 * 合作方列表
	 */
	public function get_partners_list(){
        $department = Db::name('partners');
        return $department->field('*')->select();
    }
    /**
     * 岗位列表
     */
    public function get_employeetype_list(){
    	$employeetype = Db::name('EmployeeType');
    	return $employeetype->field('*')->select();
    }
    /**
     * 校区列表
     */
    public function get_businesscenter_list(){
    	$businesscenter = Db::name('BusinessCenter');
    	return $businesscenter->field('*')->select();
    }
    public function get_partner_id($key){
    	$where = [];
    	$where['name'] = ['=',"$key"];
    	$id = Db::name('partners')->where($where)->value('p_id');
    	return $id;
    }
    /**
     * 合作方名称
     */
    public function get_partners_name($id=0){
    	$department = Db::name('partners');
    	return $department->where('p_id',$id)->value('name');
    }
    /**
     * 岗位名称
     */
    public function get_employeetype_name($id=0){
    	$employeetype = Db::name('EmployeeType');
    	return $employeetype->where('employee_type_id',$id)->value('name');
    }
    /**
     * 校区名称
     */
    public function get_businesscenter_name($id=0){
    	$businesscenter = Db::name('business_center');
    	return $businesscenter->where('business_center_id',$id)->value('name');
    }
    /**
	 * 合作方列表
	 */
    public function get_partners_lists(){
        return $this->field('*')->select();
	}

	public function get_settlement_type($type=0){
		switch($type){
			case 1:
				return '管理费';
				break;
			case 2:
				return '统考费';
				break;
			case 3:
				return '实践报考费';
				break;
			default :
				return '未知';
		}
	}
	public function get_pay_type($type=0){
		switch($type){
			case 1:
				return '现金支付';
				break;
			case 2:
				return '银行转账';
				break;
			case 3:
				return '微信支付';
				break;
			case 4:
				return '支付宝支付';
				break;
			case 5:
				return '支票/汇票';
				break;
			default :
				return '未知';
		}
	}
	public function time_date($time){
		return date('Y-m-d',$time);
	}

}
