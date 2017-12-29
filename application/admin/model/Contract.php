<?php
namespace application\admin\model;
use think\Db;
class Contract extends Base {
    public function pageQuery(){
        $page = Db::name('statement_tmpl')->paginate(input('post.pagesize/d'))->toArray(); 
        return $page;
    }
    public function editInfo(){
        $where = input('get.id');
        $page = Db::name('statement_tmpl')->where('id',$where)->paginate(input('post.pagesize/d'))->toArray();
        $data['id'] = $page['Rows'][0]['id'];
        $data['name'] = $page['Rows'][0]['name'];
        $data['path'] = $page['Rows'][0]['path'];
        $data['action'] = $page['Rows'][0]['action'];
        $data['images'] = $page['Rows'][0]['signcompany'];
        return $data;        
    }
    public function addInfo(){
        return MBISReturn('',1);
    }
    public function saveInfo(){
        $data = input('post.');
        $file =$data['basic'];
        $rs = substr($file['signcompany'], 0,1);
        if($rs != '/'){
            $file['signcompany'] = '/'.$file['signcompany'];
        }            
        $info['signcompany'] = $file['signcompany'];
        $info['name'] = $file['name'];
        $info['id'] = $file['userId'];
        $inffo['path'] = 'html/skill/tmpl-full';
        $info['action'] = 'request()->domain()';
        $rs = Db::name('statement_tmpl')->update($info);
        if($rs == 1){
          return MBISReturn('更改成功',1);  
        }else{
            return MBISReturn('更改失败,请修改需要更变的内容',-1);
        }
    }
    public function addContractInfo(){
        $data = input('post.');
        $file['name'] = $data['basic']['name'];
        $file['signcompany'] = '/'.$data['basic']['signcompany'];
        $file['path'] = 'html/skill/tmpl-full';
        $file['action']='request()->domain()';
        $file['key']='skill_notfull';
        $rs = Db::name('statement_tmpl')->insert($file);
        if($rs == 1){
          return MBISReturn('添加成功',1);  
        }else{
            return MBISReturn('添加失败',-1);
        }
    }
}
