<?php
namespace application\common\model;
/**
 * 用户地址
 */
use think\Db;
class UserAddress extends Base{
     /**
      * 获取列表
      */
      public function listQuery($userId){
         $where = ['userId'=>(int)$userId,'dataFlag'=>1];
         $rs = $this->order('addressId desc')->where($where)->select();
         $areaIds = [];
         $areaMaps = [];
         foreach ($rs as $key => $v){
         	 $tmp = explode('_',$v['areaIdPath']);
         	 foreach ($tmp as $vv){
         		if($vv=='')continue;
         	    if(!in_array($vv,$areaIds))$areaIds[] = $vv;
         	 }
         	 $rs[$key]['areaId2'] = $tmp[1];
         }
         if(!empty($areaIds)){
	         $areas = Db::name('areas')->where(['dataFlag'=>1,'areaId'=>['in',$areaIds]])->field('areaId,areaName')->select();
	         foreach ($areas as $v){
	         	 $areaMaps[$v['areaId']] = $v['areaName'];
	         }
	         foreach ($rs as $key => $v){
	         	 $tmp = explode('_',$v['areaIdPath']);
	         	 $areaNames = [];
		         foreach ($tmp as $vv){
	         		if($vv=='')continue;
	         	    $areaNames[] = $areaMaps[$vv];
	         	 }
	         	 $rs[$key]['areaName'] = implode('',$areaNames);
	         	 $rs[$key]['areaName1'] = $areaMaps[$v['areaId2']];
	         }
         }
         return $rs;
      }
    /**
    *  获取用户信息
    */
    public function getById($id){
        return $this->get(['addressId'=>$id,'userId'=>(int)session('MBIS_USER.userId')]);
    }
    /**
     * 新增
     */
    public function add(){
        $data = input('post.');
        $data['userId'] = (int)session('MBIS_USER.userId');
        $data['createTime'] = date('Y-m-d H:i:s');
        $areaIds = model('Areas')->getParentIs((int)input('areaId'));
        if(!empty($areaIds))$data['areaIdPath'] = implode('_',$areaIds)."_";
        $result = $this->validate('UserAddress.add')->allowField(true)->save($data);
        if(false !== $result){
            //修改默认地址
            if((int)input('post.isDefault')==1)
              $this->where("addressId != $this->addressId")->setField('isDefault',0);
            return MBISReturn("新增成功", 1,['addressId'=>$this->addressId]);
        }else{
            return MBISReturn($this->getError(),-1);
        }
    }
    /**
     * 编辑资料
     */
    public function edit(){
        $id = (int)input('post.addressId');
        $data = input('post.');
        $areaIds = model('Areas')->getParentIs((int)input('areaId'));
        if(!empty($areaIds))$data['areaIdPath'] = implode('_',$areaIds)."_";
        $result = $this->validate('UserAddress.edit')->allowField(true)->save($data,['addressId'=>$id,'userId'=>(int)session('MBIS_USER.userId')]);
        //修改默认地址
        if((int)input('post.isDefault')==1)
          $this->where("addressId != $id")->setField('isDefault',0);
        if(false !== $result){
            return MBISReturn("编辑成功", 1);
        }else{
            return MBISReturn($this->getError(),-1);
        }
    }
    /**
     * 删除
     */
    public function del(){
        $id = input('post.id/d');
        $data = [];
        $data['dataFlag'] = -1;
        $result = $this->update($data,['addressId'=>$id,'userId'=>(int)session('MBIS_USER.userId')]);
        if(false !== $result){
            return MBISReturn("删除成功", 1);
        }else{
            return MBISReturn($this->getError(),-1);
        }
    }

    /**
    * 设置为默认地址
    */
    public function setDefault(){
        $id = (int)input('post.id');
        $this->where("addressId != $id")->setField('isDefault',0);
        $rs = $this->where("addressId = $id and userId=".(int)session('MBIS_USER.userId'))->setField('isDefault',1);
        if(false !== $rs){
            return MBISReturn("设置成功", 1);
        }else{
            return MBISReturn($this->getError(),-1);
        }
    }
    
    /**
     * 获取默认地址
     */
    public function getDefaultAddress(){
    	$userId = (int)session('MBIS_USER.userId');
    	$where = ['userId'=>$userId,'dataFlag'=>1];
        $rs = $this->where($where)->order('isDefault desc,addressId desc')->find();
        if(empty($rs))return [];
        $areaIds = [];
        $areaMaps = [];
        $tmp = explode('_',$rs['areaIdPath']);
        $rs['areaId2'] = $tmp[1];
        foreach ($tmp as $vv){
         	if($vv=='')continue;
         	if(!in_array($vv,$areaIds))$areaIds[] = $vv;
        }
        if(!empty($areaIds)){
	         $areas = Db::name('areas')->where(['dataFlag'=>1,'areaId'=>['in',$areaIds]])->field('areaId,areaName')->select();
	         foreach ($areas as $v){
	         	 $areaMaps[$v['areaId']] = $v['areaName'];
	         }
	         $tmp = explode('_',$rs['areaIdPath']);
	         $areaNames = [];
		     foreach ($tmp as $vv){
	         	 if($vv=='')continue;
	         	 $areaNames[] = $areaMaps[$vv];
	         	 $rs['areaName'] = implode('',$areaNames);
	         }
         }
         return $rs;
    }
}
