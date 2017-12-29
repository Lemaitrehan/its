<?php
namespace application\admin\model;
/**
 * 优惠促销规则业务处理
 */
use think\Db;
class SalesRuleOrder extends Base{
	/**
	 * 分页
	 */
	public function pageQuery(){

		$where = [];
		$start = strtotime(input('get.start'));
		$end = strtotime(input('get.end'));
		$rule_type = input('get.rule_type');
		$rule_use = input('get.rule_use');
		$status = input('get.status');
		$name = input('get.name');
		if(!empty($start) && !empty($end)){
			$where['from_time'] = ['between',["$start","$end"]];
		}
		if(!empty($rule_type)){
			$where['rule_type'] = ['=',"$rule_type"];
		}
		if(!empty($rule_use)){
			$where['rule_use'] = ['=',"$rule_use"];
		}
		if(!empty($name)){
			$where['name'] = ['like',"%$name%"];
		}
		if($status != ''){
			$where['status'] = ['=',"$status"];
		}	
		$page = $this->where($where)->field('*')->paginate(input('post.pagesize/d'))->toArray();
		
		if(count($page['Rows'])>0){
			foreach($page['Rows'] as $key => $v){
				$page['Rows'][$key]['from_time'] = $v['from_time'] == 0 ? '' : $this->time_date($v['from_time']);
				$page['Rows'][$key]['to_time'] = $v['to_time'] == 0 ? '' : $this->time_date($v['to_time']);
				$page['Rows'][$key]['rule_type'] = $this->get_rule_type($v['rule_type']);
				$page['Rows'][$key]['rule_use'] = $this->get_rule_use($v['rule_use']);
				$page['Rows'][$key]['platform_use'] = $this->get_platform_use($v['platform_use']);
				$page['Rows'][$key]['status'] = $this->get_status($v['status']);
				$page['Rows'][$key]['stop_rules_processing'] = $this->get_processing($v['stop_rules_processing']);
				$page['Rows'][$key]['c_template'] = $this->get_c_template($v['c_template']);
				$page['Rows'][$key]['s_template'] = $this->get_s_template($v['s_template']);
				$page['Rows'][$key]['member_type_ids'] = $this->get_member_type($v['member_type_ids']);
				$page['Rows'][$key]['member_lv_ids'] = $v['member_lv_ids'] != ''? $this->get_member_lv($v['member_lv_ids']) : '未知';
			}
		}
		//dump($page);die;
        return $page;
	}
	public function getById($id){
		if($id == ''){
			$info = $this->get(['rule_id'=>$id]);
		}else{
			$info = $this->get(['rule_id'=>$id]);
			if($info['rule_type']){
                $info['rule_type'] = explode(',',$info['rule_type']);
            }else{
                $info['rule_type'] = [];
            }
            if($info['rule_use']){
                $info['rule_use'] = explode(',',$info['rule_use']);
            }else{
                $info['rule_use'] = [];
            }
            if($info['platform_use']){
                $info['platform_use'] = explode(',',$info['platform_use']);
            }else{
                $info['platform_use'] = [];
            }
			if($info['member_lv_ids']){
                $info['member_lv_ids'] = explode(',',$info['member_lv_ids']);
            }else{
                $info['member_lv_ids'] = [];
            }
            if($info['member_type_ids']){
                $info['member_type_ids'] = explode(',',$info['member_type_ids']);
            }else{
                $info['member_type_ids'] = [];
            }
            if($info['conditions']){
                $info['conditions'] = unserialize($info['conditions']);
            }else{
                $info['conditions'] = [];
            }
            if($info['action_solution']){
                $info['action_solution'] = unserialize($info['action_solution']);
            }else{
                $re['action_solution'] = [];
            }
            if($info['from_time'] == 0){
                $info['from_time'] = '';
            }else{
                $info['from_time'] = date('Y-m-d H:i',$info['from_time']);
            }
            if($info['to_time'] == 0){
                $info['to_time'] = '';
            }else{
                $info['to_time'] = date('Y-m-d H:i',$info['to_time']);
            }
		}
		//dump($info);die;
		return $info;
	}
	/**
	 * 新增
	 */
	public function add(){
		$data = input('post.');
		//dump($data);die;
		$data = $data['rule'];
		if(isset($data['rule_type'])){
			$data['rule_type'] = implode(',',$data['rule_type']);
		}else{
			$data['rule_type'] = '';
		}
		if(isset($data['rule_use'])){
			$data['rule_use'] = implode(',',$data['rule_use']);
		}else{
			$data['rule_use'] = '';
		}
		if(isset($data['platform_use'])){
			$data['platform_use'] = implode(',',$data['platform_use']);
		}else{
			$data['platform_use'] = '';
		}
		if(isset($data['member_lv_ids'])){
			$data['member_lv_ids'] = implode(',',$data['member_lv_ids']);
		}else{
			$data['member_lv_ids'] = '';
		}
		if(isset($data['member_type_ids'])){
			$data['member_type_ids'] = implode(',',$data['member_type_ids']);
		}else{
			$data['member_type_ids'] = '';
		}
		if(isset($data['conditions'],$data['c_template']))$data['conditions'] = $this->filterData($data['c_template'],$data['conditions']);
		if(isset($data['action_solution'],$data['s_template']))$data['action_solution'] = $this->filterData($data['s_template'],$data['action_solution']);
        $data['from_time'] = strtotime($data['from_time']);
        $data['to_time'] = strtotime($data['to_time']);
        MBISUnset($data,'id');
        //dump($data);die;
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
		//dump($_POST);die;
		$data = input('post.');
		$data = $data['rule'];
		if(isset($data['id']))$id = $data['id'];
		//dump($data);die;
		if(isset($data['rule_type'])){
			$data['rule_type'] = implode(',',$data['rule_type']);
		}else{
			$data['rule_type'] = '';
		}
		if(isset($data['rule_use'])){
			$data['rule_use'] = implode(',',$data['rule_use']);
		}else{
			$data['rule_use'] = '';
		}
		if(isset($data['platform_use'])){
			$data['platform_use'] = implode(',',$data['platform_use']);
		}else{
			$data['platform_use'] = '';
		}
		if(isset($data['member_lv_ids'])){
			$data['member_lv_ids'] = implode(',',$data['member_lv_ids']);
		}else{
			$data['member_lv_ids'] = '';
		}
		if(isset($data['member_type_ids'])){
			$data['member_type_ids'] = implode(',',$data['member_type_ids']);
		}else{
			$data['member_type_ids'] = '';
		}
		if(isset($data['conditions'],$data['c_template']))$data['conditions'] = $this->filterData($data['c_template'],$data['conditions']);
		if(isset($data['action_solution'],$data['s_template']))$data['action_solution'] = $this->filterData($data['s_template'],$data['action_solution']);
        $data['from_time'] = strtotime($data['from_time']);
        $data['to_time'] = strtotime($data['to_time']);
        //dump($data);die;
		MBISUnset($data,'id');
		Db::startTrans();
		try{
		    $result = $this->save($data,['rule_id'=>$id]);
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
		    $result = $this->where(['rule_id'=>$id])->delete();
	        if(false !== $result){
	        	Db::commit();
	        	return MBISReturn("删除成功", 1);
	        }
		}catch (\Exception $e) {
            Db::rollback();
            return MBISReturn('删除失败',-1);
        }
	}
    public function get_rule_type($type){
    	$type = explode(',',$type);
		foreach($type as &$v){
			if($v == 1){
				$v = '学历类';
			}else
			if($v == 2){
				$v = '非学历类';
			}else{
				$v = '未知';
			}
		}
		$type = implode(',',$type);
		return $type;
    }
    public function get_rule_use($type){
    	$type = explode(',',$type);
		foreach($type as &$v){
			if($v == 1){
				$v = '下单';
			}else
			if($v == 2){
				$v = '补费';
			}else{
				$v = '未知';
			}
		}
		$type = implode(',',$type);
		return $type;
    }
    public function get_platform_use($type){
    	$type = explode(',',$type);
		foreach($type as &$v){
			if($v == 1){
				$v = 'PC';
			}else
			if($v == 2){
				$v = 'iPad';
			}else
			if($v == 3){
				$v = 'iPhone';
			}else
			if($v == 4){
				$v = 'Android';
			}else
			if($v == 5){
				$v = 'WeChat';
			}else
			if($v == 6){
				$v = 'Wap';
			}else{
				$v = '未知';
			}
		}
		$type = implode(',',$type);
		return $type;
    }
    public function time_date($time){
		return date('Y-m-d H:i',$time);
	}
	public function get_status($status){
		switch($status){
			case 0:return '否';
			case 1:return '是';
		}
	}
	public function get_processing($type){
		switch($type){
			case 0:return '否';
			case 1:return '是';
		}
	}
	public function get_c_template($type){
		switch($type){
			case 1:return '满足X报名科目数';
			case 2:return '满足X报名人数';
			case 3:return '自定义优惠条件';
		}
	}
	public function get_s_template($type){
		switch($type){
			case 1:return '以固定折扣';
			case 2:return '以固定价格';
			case 3:return '减去固定价格';
		}
	}
	public function get_member_type($ids){
		$ids = explode(',',$ids);
		foreach($ids as &$v){
			if($v == 1){
				$v = '新生报名';
			}else
			if($v == 2){
				$v = '在校生加报';
			}else
			if($v == 3){
				$v = '学员会员加报';
			}else{
				$v = '未知';
			}
		}
		$ids = implode(',',$ids);
		return $ids;
	}
	public function get_member_lv($ids){
		$ids = explode(',',$ids);
		foreach($ids as &$v){
			$v = $this->get_rank_name($v);
		}
		$ids = implode(',',$ids);
		return $ids;
	}
	public function get_rank_name($id=0){
		return Db::name('user_ranks')->where('rankId',$id)->value('rankName');
	}

	public function getUserRanks(){
		return Db::name('user_ranks')->field('rankId,rankName')->select();
	}

	//过滤数据  $templet 选中的模板值 string
	//          $data    要过滤的数据 array
	//          $arr    return
	public function filterData($templet,$data){
		$arr = [];
		$arr = $data[$templet];
		//dump($arr);
		foreach($arr as &$v){
			foreach($v as &$val){
				if(isset($val['type']))$val['type'] = trim($val['type']);
				if(isset($val['value']))$val['value'] = trim($val['value']);
				//dump($val);die;
			}
		}
		//dump($arr);die;
		//return $arr;
		return serialize($arr);
	}
}
