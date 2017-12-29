<?php
namespace application\admin\model;
/**
 * 角色志业务处理
 */
class Roles extends Base{
	/**
	 * 分页
	 */
	public function pageQuery(){
		return $this->where('dataFlag',1)->field('roleId,roleName')->paginate(input('pagesize/d'));
	}
	/**
	 * 列表
	 */
	public function listQuery(){
		return $this->where('dataFlag',1)->field('roleId,roleName')->select();
	}
	/**
	 * 删除
	 */
    public function del(){
	    $id = input('post.id/d');
		$data = [];
		$data['dataFlag'] = -1;
	    $result = $this->update($data,['roleId'=>$id]);
        if(false !== $result){
        	return MBISReturn("删除成功", 1);
        }else{
        	return MBISReturn($this->getError(),-1);
        }
	}
	
	/**
	 * 获取角色权限
	 */
	public function getById($id){
		return $this->get(['dataFlag'=>1,'roleId'=>$id]);
	}
	
	/**
	 * 新增
	 */
	public function add(){
	    
        $data = input('post.');
        $file['roleId']     = $data['roleId'];
        $file['roleName']   = $data['roleName'];
        $file['roleDesc']   = $data['roleDesc'];
        $file['privileges'] = $data['privileges'];
        $file['is_teachers']   = $data['is_teachers'];
        $sch = $data['sch'];
        $edu = $data['edu'];
        
        //学校
        if($sch){
            $file['school_ids'] = $sch;
        }
        //查看用户的全选范围
        $arrUser = [];
        if($edu){
            $arrEdu = explode('**',$edu);
            foreach ($arrEdu as $key => $v ){
                $row = explode('--', $v);
                $education_type =  $row[0];
                $school  =  $row[1];
                $major   =  $row[2];
                $grade   =  $row[3];
                $arrUser[$key] = array(
                    'education_type' => $education_type>0?$education_type:0,
                    'school'         => $school>0?$school:0,
                    'major'          => $major>0?$major:0,
                    'grade'          => $grade>0?$grade:0,
                ); 
            }
        }
        $userRange = serialize($arrUser);
        $file['userRange'] = $userRange;
        
        $result = $this->validate('Roles.add')->allowField(true)->save($file);
        
        if(false !== $result){
        	return MBISReturn("新增成功", 1);
        }else{
        	return MBISReturn($this->getError(),-1);
        }
	}
    /**
	 * 编辑
	 */
	public function edit(){
		$id   = input('post.roleId/d');
		$data = input('post.');
		$sch  = $data['sch'];
		$edu  = $data['edu'];
		unset( $data['sch'] );
		unset( $data['edu'] );
		
		//学校
		if($sch){
		    $data['school_ids'] = $sch;
		}
		//查看用户的全选范围
		$arrUser = [];
		if($edu){
		    $arrEdu = explode('**',$edu);
		    foreach ($arrEdu as $key => $v ){
		        $row = explode('--', $v);
		        $education_type =  $row[0];
		        $school  =  $row[1];
		        $major   =  $row[2];
		        $grade   =  $row[3];
		        $arrUser[$key] = array(
		            'education_type' => $education_type>0?$education_type:0,
		            'school'         => $school>0?$school:0,
		            'major'          => $major>0?$major:0,
		            'grade'          => $grade>0?$grade:0,
		        );
		    }
		}
		$userRange = serialize($arrUser);
		$data['userRange'] = $userRange;
	    $result = $this->validate('Roles.edit')->allowField(true)->save($data,['roleId'=>$id]);
        if(false !== $result){
            $staffRoleId = (int)session('MBIS_STAFF.staffRoleId');
        	if($id==$staffRoleId){
        		$STAFF = session('MBIS_STAFF');
        		$STAFF['privileges'] = explode(',',input('post.privileges'));
        		$STAFF['roleName'] = Input('post.roleName');
        		session('MBIS_STAFF',$STAFF);
        	}
        	return MBISReturn("编辑成功", 1);
        }else{
        	return MBISReturn($this->getError(),-1);
        }
	}
	
}
