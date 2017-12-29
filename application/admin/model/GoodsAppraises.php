<?php
namespace application\admin\model;
/**
 * 商品评价业务处理
 */
class GoodsAppraises extends Base{
	/**
	 * 分页
	 */
	public function pageQuery(){
		$where = 'p.shopId=g.shopId and gp.goodsId=g.goodsId and o.orderId=gp.orderId';
		$shopName = input('shopName');
     	$goodsName = input('goodsName');

	 	$areaId1 = (int)input('areaId1');
		if($areaId1>0){
			$where.=" and p.areaIdPath like '".$areaId1."%'";

			$areaId2 = (int)input("areaId1_".$areaId1);
			if($areaId2>0)
				$where.=" and p.areaIdPath like '".$areaId1."_".$areaId2."%'";

			$areaId3 = (int)input("areaId1_".$areaId1."_".$areaId2);
			if($areaId3>0)
				$where.=" and p.areaId = $areaId3";
		}


	 	if($shopName!='')
	 		$where.=" and (p.shopName like '%".$shopName."%' or p.shopSn like '%'".$shopName."%')";
	 	if($goodsName!='')
	 		$where.=" and (g.goodsName like '%".$goodsName."%' or g.goodsSn like '%".$goodsName."%')";

		$rs = $this->alias('gp')->field('gp.*,g.goodsName,g.goodsImg,o.orderNo,u.loginName')
					->join('__GOODS__ g ','gp.goodsId=g.goodsId','left') 
		         	->join('__ORDERS__ o','gp.orderId=o.orderId','left')
		         	->join('__USERS__ u','u.userId=gp.userId','left')
		         	->join('__SHOPS__ p','p.shopId=gp.shopId','left')
		         	->where($where)
		         	->order('id desc')
		         	->paginate(input('pagesize/d'))->toArray();
		return $rs;
	}
	public function getById($id){
		return $this->alias('gp')->field('gp.*,o.orderNo,u.loginName,g.goodsName,g.goodsImg')
					->join('__GOODS__ g ','gp.goodsId=g.goodsId','left') 
		         	->join('__ORDERS__ o','gp.orderId=o.orderId','left')
		         	->join('__USERS__ u','u.userId=gp.userId','left')
		         	->where('gp.id',$id)->find();
	}
    /**
	 * 编辑
	 */
	public function edit(){
		$Id = input('post.id/d',0);
		$data = input('post.');
		MBISUnset($data,'createTime');
	    $result = $this->validate('GoodsAppraises.edit')->allowField(true)->save($data,['id'=>$Id]);
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
	    $id = input('post.id/d',0);
		$data = [];
		$data['dataFlag'] = -1;
	    $result = $this->update($data,['id'=>$id]);
        if(false !== $result){
        	return MBISReturn("删除成功", 1);
        }else{
        	return MBISReturn($this->getError(),-1);
        }
	}
	
}
