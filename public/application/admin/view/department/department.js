var grid;
var combo;
function initGrid(){
	grid = $("#maingrid").ligerGrid({
		url:MBIS.U('admin/department/pageQuery'),
		pageSize:MBIS.pageSize,
		pageSizeOptions:MBIS.pageSizeOptions,
		height:'99%',
        width:'100%',
        minColToggle:4,
        rownumbers:true,
        columns: [
            { display: '部门名称', name: 'name',isSort: false,},
            { display: '上级部门', name: 'parent_id',isSort: false,},
            { display: '负责人',name: 'uname',isSort: false},
	        { display: '主要业务', name: 'business_info',isSort: false},
	        { display: '操作', name: 'op',isSort: false,
	        	render: function (rowdata){
		            var h = "";
		            if(MBIS.GRANT.BMGL_02)h += "<a href='javascript:toEdit("+rowdata["department_id"]+")'>修改</a> ";
		            if(MBIS.GRANT.BMGL_03)h += "<a href='javascript:toDel("+rowdata["department_id"]+")'>删除</a> ";
					//if(MBIS.GRANT.BMGL_04)h += "<a href='javascript:toEditJx("+rowdata["department_id"]+")'>绩效管理</a> ";
		            return h;
	        	}
	        }
        ]
    });

}

function initCombo(){
}

function loadGrid(){
	grid.set('url',MBIS.U('admin/department/pageQuery','key='+$('#key').val()));
}
function refresh(){
	if($('#key').val() !== ''){
		$('#key').val('');
	}
  	grid.set('url',MBIS.U('admin/department/pageQuery'));
}
function toEdit(id){
	location.href=MBIS.U('admin/department/toEdit','id='+id);
}

function toEdits(id){
    var params = MBIS.getParams('.ipt');
    params.id = id;
    var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
	$.post(MBIS.U('admin/department/'+((id>0)?"edit":"add")),params,function(data,textStatus){
		  layer.close(loading);
		  var json = MBIS.toAdminJson(data);
		  if(json.status=='1'){
		    	MBIS.msg(json.msg,{icon:1});
		        setTimeout(function(){ 
			    	location.href=MBIS.U('admin/department/index');
		        },1000);
		  }else{
		        MBIS.msg(json.msg,{icon:2});
		  }
	});
}

function toDel(id){
	var box = MBIS.confirm({content:"您确定要删除该数据吗?",yes:function(){
	           var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
	           	$.post(MBIS.U('admin/department/del'),{id:id},function(data,textStatus){
	           			  layer.close(loading);
	           			  var json = MBIS.toAdminJson(data);
	           			  if(json.status=='1'){
	           			    	MBIS.msg(json.msg,{icon:1});
	           			    	layer.close(box);
	           		            grid.reload();
	           			  }else{
	           			    	MBIS.msg(json.msg,{icon:2});
	           			  }
	           		});
	            }});
}