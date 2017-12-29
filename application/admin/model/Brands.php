<?php
namespace application\admin\model;
use think\Db;
/**
 * 品牌业务处理
 */
class Brands extends Base{
	/**
	 * 分页
	 */
	public function pageQuery(){
		$key = input('get.key');
		$id = input('get.id/d');
		$where = [];
		$where['b.dataFlag'] = 1;
		if($key!='')$where['b.brandName'] = ['like','%'.$key.'%'];
		if($id>0)$where['gcb.catId'] = $id;
		$total = Db::name('brands')->alias('b');
		if($id>0){ 
		    $total->join('__CAT_BRANDS__ gcb','b.brandId = gcb.brandId','left');
		}
		$page = $total->where($where)
		->field('b.brandId,b.brandName,b.brandImg,b.brandDesc')
		->order('b.brandId', 'desc')
		->paginate(input('post.pagesize/d'))->toArray();
		if(count($page['Rows'])>0){
			foreach ($page['Rows'] as $key => $v){
				$page['Rows'][$key]['brandDesc'] = strip_tags(htmlspecialchars_decode($v['brandDesc']));
			}
		}
		return $page;
	}	
	
	/**
	 * 获取指定对象
	 */
	public function getById($id){
		$result = $this->where(['brandId'=>$id])->find();
		//获取关联的分类
		$result['catIds'] = Db::name('cat_brands')->where(['brandId'=>$id])->column('catId');
		return $result;
	}
	
	/**
	 * 新增
	 */
	public function add(){
		$data = input('post.');
		MBISUnset($data,'brandId,dataFlag');
		$data['createTime'] = date('Y-m-d H:i:s');
		$idsStr = explode(',',$data['catId']);
		if($idsStr!=''){
			foreach ($idsStr as $v){
				if((int)$v>0)$ids[] = (int)$v;
			}
		}
		Db::startTrans();
        try{
			$result = $this->validate('Brands.add')->allowField(true)->save($data);
			if(false !== $result){
				//启用上传图片
			    MBISUseImages(1, $this->brandId, $data['brandImg']);
				//商品描述图片
				MBISEditorImageRocord(1, $this->brandId, '',$data['brandDesc']);
				foreach ($ids as $key =>$v){
					$d = array();
					$d['catId'] = $v;
					$d['brandId'] = $this->brandId;
					Db::name('cat_brands')->insert($d);
				}
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
		$brandId = input('post.id/d');
		$data = input('post.');
		$idsStr = explode(',',$data['catId']);
		if($idsStr!=''){
			foreach ($idsStr as $v){
				if((int)$v>0)$ids[] = (int)$v;
			}
		}
		$filter = array();
		//获取品牌的关联分类
		$catBrands = Db::name('cat_brands')->where(['brandId'=>$brandId])->select();
		foreach ($catBrands as $key =>$v){
			if(!in_array($v['catId'],$ids))$filter[] = $v['catId'];
		}
		Db::startTrans();
        try{
			MBISUseImages(1, $brandId, $data['brandImg'], 'brands', 'brandImg');
			// 品牌描述图片
			$desc = $this->where('brandId',$brandId)->value('brandDesc');
			MBISEditorImageRocord(1, $brandId, $desc, $data['brandDesc']);
			$result = $this->validate('Brands.edit')->allowField(['brandName','brandImg','brandDesc'])->save(input('post.'),['brandId'=>$brandId]);
			if(false !== $result){
				foreach ($catBrands as $key =>$v){
					Db::name('cat_brands')->where('brandId',$brandId)->delete();
				}
				foreach ($ids as $key =>$v){
					$d = array();
					$d['catId'] = $v;
					$d['brandId'] = $brandId;
					Db::name('cat_brands')->insert($d);
				}
				Db::commit();
				return MBISReturn("修改成功", 1);
			}
        }catch (\Exception $e) {
            Db::rollback();
        }
        return MBISReturn('修改失败',-1);
	}
	
	/**
	 * 删除
	 */
	public function del(){
		$id = input('post.id/d');
		$data = [];
		$data['dataFlag'] = -1;
		Db::startTrans();
        try{
			$result = $this->where(['brandId'=>$id])->update($data);
		    MBISUnuseImage('brands','brandImg',$id);
			// 品牌描述图片
			$desc = $this->where('brandId',$id)->value('brandDesc');
			MBISEditorImageRocord(1, $id, $desc,'');
			if(false !== $result){
				Db::commit();
				return MBISReturn("删除成功", 1);
			}
        }catch (\Exception $e) {
            Db::rollback();
        }
        return MBISReturn('删除失败',-1);
	}
	
	/**
	 * 获取品牌
	 */
	public function searchBrands(){
		$goodsCatatId = (int)input('post.goodsCatId');
		if($goodsCatatId<=0)return [];
		$key = input('post.key');
		$where = [];
		$where['dataFlag'] = 1;
		$where['catId'] = $goodsCatatId;
		if($key!='')$where['brandsName'] = ['like','%'.$key.'%'];
		return $this->alias('s')->join('__CAT_BRANDS__ cb','s.brandId=cb.brandId','inner')
		            ->where($where)->field('brandName,s.brandId')->select();
	}
}