var grid;
function initGrid(){
	grid = $("#maingrid").ligerGrid({
		url:MBIS.U('admin/roles/pageQuery'),
		pageSize:MBIS.pageSize,
		pageSizeOptions:MBIS.pageSizeOptions,
		height:'99%',
        width:'100%',
        minColToggle:6,
        rownumbers:true,
        columns: [
	        { display: '权限名称', name: 'roleName'},
	        { display: '权限说明', name: 'roleDesc'},
	        { display: '操作', name: 'op',isSort: false,render: function (rowdata, rowindex, value){
	            var h = "";
	            if(MBIS.GRANT.JSGL_02)h += "<a href='javascript:toEdit(" + rowdata['roleId'] + ")'>修改</a> ";
	            if(MBIS.GRANT.JSGL_03)h += "<a href='javascript:toDel(" + rowdata['roleId'] + ")'>删除</a> "; 
	            return h;
	        }}
        ]
    });
}
function toEdit(id){
	location.href=MBIS.U('admin/roles/toEdit','id='+id);
}
function toDel(id){
	var box = MBIS.confirm({content:"您确定要删除该角色吗?",yes:function(){
	           var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
	           $.post(MBIS.U('admin/roles/del'),{id:id},function(data,textStatus){
	           			  layer.close(loading);
	           			  var json = MBIS.toAdminJson(data);
	           			  if(json.status=='1'){
	           			    	MBIS.msg("操作成功",{icon:1});
	           			    	layer.close(box);
	           		            grid.reload();
	           			  }else{
	           			    	MBIS.msg(json.msg,{icon:2});
	           			  }
	           		});
	            }});
}
function getNodes(event, treeId, treeNode){
	zTree.expandNode(treeNode,true, true, true);
	if($.inArray(treeNode.privilegeCode,rolePrivileges)>-1){
		zTree.checkNode(treeNode,true,true);
	}
}



function save(){
	if(!$('#roleName').isValid())return;
	var nodes = zTree.getChangeCheckedNodes();
	var privileges = [];
	for(var i=0;i<nodes.length;i++){
		if(nodes[i].isParent==0)privileges.push(nodes[i].privilegeCode);
	}
	var params = MBIS.getParams('.ipt');
	params.privileges = privileges.join(',');
	var arrStr = [];
	//查找员工所能查看的学历
	$('.addDivHtml').each(function(){
		 var education_type = $(this).find('.education_type').val();//学历类型
		 if(education_type > 0 ){
			 var school         = $(this).find('.school').val();//学校
			 var major          = $(this).find('.major').val();//专业
			 var grade          = $(this).find('.grade').val();//年级
			 arrStr.push( education_type+'--'+school+'--'+major+'--'+grade );
		 }
	})
	if(arrStr){
		var tt = arrStr.join('**');
		params.edu = tt;
	}
	//全选
	var is_all =  $('.allCheck').is(':checked');
		//添加用户所在的校区
    if(!is_all){
		var check = [];
		$('.oneCheck:checked').each(function(){
			var value = $(this).val();
			check.push(value);
		})
		check = check.join(','); 
		params.sch  = check;
	}else{
		params.sch  = 0;
	}

	var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
    $.post(MBIS.U('admin/roles/'+((params.roleId==0)?"add":"edit")),params,function(data,textStatus){
    	layer.close(loading);
    	var json = MBIS.toAdminJson(data);
    	if(json.status=='1'){
    		MBIS.msg("操作成功",{icon:1});
    		location.href=MBIS.U('admin/roles/index');
    	}else{
    		MBIS.msg(json.msg,{icon:2});
    	}
    });
}

//添加
$('.addUserSchool').click(function(){
	//查找最后一个 schoolTd
	 var num = parseInt($('.schoolTd').find('.grade').last().attr('num')) + 1;
	 var cloneDiv = $('.cloneDiv').clone(true).removeClass('hide').removeClass('cloneDiv');
	 cloneDiv.find('.grade').addClass('grade'+num);
	 cloneDiv.find('.grade').attr('num',num);
	 $('.schoolTd').append(cloneDiv);
	 select2('.grade'+num);
})

//删除
$(document).on('click','.delUserSchool',function(){
	$(this).closest('.addDivHtml').remove();
})

//
$('.allCheck').click(function(){
	if( $(this).is(':checked') ){
		$('.oneCheck').prop('checked',true);
	}else{
		$('.oneCheck').prop('checked',false);
	}
})

$('.oneCheck').click(function(){
	var is_all = true;
	$('.oneCheck').each(function(i,e){
		 if( $(e).is(':checked')  == false ){
			 is_all = false;
		 }
	})
	if( is_all ){
		$('.allCheck').prop('checked',true);
	}else{
		$('.allCheck').prop('checked',false);
	}
})

$('.is_teachers').click(function(){
	if( $(this).val() == 1 ){
		$('.is_hide').closest('tr').show();
	}else{
		$('.is_hide').closest('tr').hide();
	}
})
//查找学校
$(document).on('change','.education_type',function(){
	var params = {},
	education_type = $(this).val();
	if(education_type<=0){
       		return '';
	}
	var nowthis = $(this);
	params = {action:'getSchoolType',education_type:education_type};
	$.post(MBIS.U('admin/roles/toEdit'),params,function(data,textStatus){
    	var json = MBIS.toAdminJson(data);
    	if(json.status=='1'){
    		MBIS.msg("加载数据中",{icon:1});
    		var html = '<option value=" ">请选择</option>';
    		$.each(json.data,function(i,e){
    			html += '<option value="'+e.school_id+'">'+e.name+'</option>';
    		})
    		nowthis.closest('div').find('.school').html(html);
    		nowthis.closest('div').find('.grade,.major,.grade').html('<option value=" ">请选择</option>');
    	}else{
    		MBIS.msg(json.msg,{icon:2});
    	}
    });
})

//查找学校
$(document).on('change','.school',function(){
	var params = {},
	school = $(this).val();
	if(school<=0){
       		return '';
	}
	var nowthis = $(this);
	//查找
	params = {action:'getSchoolMajor',school:school};
	$.post(MBIS.U('admin/roles/toEdit'),params,function(data,textStatus){
    	var json = MBIS.toAdminJson(data);
    	if(json.status=='1'){
    		MBIS.msg("加载数据中",{icon:1});
    		var html = '<option value=" ">请选择</option>';
    		$.each(json.data,function(i,e){
    			html += '<option value="'+e.major_id+'">'+e.name+'</option>';
    		})
    		nowthis.closest('div').find('.major').html(html);
    		nowthis.closest('div').find('.grade').html('<option value=" ">请选择</option>');
    	}else{
    		MBIS.msg(json.msg,{icon:2});
    	}
    });
})

//查找班级
/*$(document).on('change','.major',function(){
	var params = {},
	education_type = $(this).val();
	if(education_type<=0){
       		return '';
	}
	var nowthis = $(this);
	params = {action:'major',education_type:education_type};
	$.post(MBIS.U('admin/roles/toEdit'),params,function(data,textStatus){
    	var json = MBIS.toAdminJson(data);
    	if(json.status=='1'){
    		MBIS.msg("加载数据中",{icon:1});
    		var html = '<option value="">请选择</option>';
    		$.each(json.data,function(i,e){
    			html += '<option value="'+e.major_id+'">'+e.name+'</option>';
    		})
    		nowthis.closest('div').find('.grade').html(html);
    	}else{
    		MBIS.msg(json.msg,{icon:2});
    	}
    });
})*/
select2('.grade_id1');


function select2(nowthis){
   //年级搜索
    function formatRepo1(repo) {
        return repo.name;
    }
    function formatRepoProvince1(repo) {
        if (repo.name) {
            return repo.name;
        } else {
            return repo.text;
        }
    }
    $(nowthis).select2({
        placeholder: '请输入学年级信息....',
        allowClear: true,
        minimumInputLength: 1,
        width: 160,
        ajax: {
            url: "/index.php/admin/roles/toEdit.html",
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                	action:'grade_id',
                    search_name: params.term, // search term
                };
            },
            processResults: function (data, params) {
                return {
                    results: data
                };
            },
        },
        templateResult: formatRepo1, // omitted for brevity, see the source of this page
        templateSelection: formatRepoProvince1 // omitted for brevity, see the source of this page
    });
}

