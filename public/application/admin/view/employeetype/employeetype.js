var grid;
var combo;
function initGrid(){
	grid = $("#maingrid").ligerGrid({
		url:MBIS.U('admin/employeetype/pageQuery'),
		pageSize:MBIS.pageSize,
		pageSizeOptions:MBIS.pageSizeOptions,
		height:'99%',
        width:'100%',
        minColToggle:3,
        rownumbers:true,
        columns: [
            { display: '岗位名称', name: 'name',isSort: false,},
            { display: '所属部门', name: 'department_id',isSort: false},
	        { display: '操作', name: 'op',width: 100,isSort: false,
	        	render: function (rowdata){
		            var h = "";
		            if(MBIS.GRANT.GWGL_02)h += "<a href='javascript:toEdit("+rowdata["employee_type_id"]+")'>修改</a> ";
		            if(MBIS.GRANT.GWGL_03)h += "<a href='javascript:toDel("+rowdata["employee_type_id"]+")'>删除</a> ";
		            return h;
	        	}
	        }
        ]
    });

}

function initCombo(){
}

function loadGrid(){
	grid.set('url',MBIS.U('admin/employeetype/pageQuery','key='+$('#key').val()));
}
function emptypeQuery(){
	var query = MBIS.getParams('.query');
    grid.set('url',MBIS.U('admin/employeetype/pageQuery',query));
}
function refresh(){
	$('.query').each(function(){
	    if($(this).val() !== ''){
	      $(this).val('');
	    }
  	});
  	grid.set('url',MBIS.U('admin/employeetype/pageQuery'));
}
function toEdit(id){
	location.href=MBIS.U('admin/employeetype/toEdit','id='+id);
}

function toEdits(id){
    var params = MBIS.getParams('.ipt');
    params.id = id;
    var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
	$.post(MBIS.U('admin/employeetype/'+((id>0)?"edit":"add")),params,function(data,textStatus){
		  layer.close(loading);
		  var json = MBIS.toAdminJson(data);
		  if(json.status=='1'){
		    	MBIS.msg(json.msg,{icon:1});
		        setTimeout(function(){ 
			    	location.href=MBIS.U('admin/employeetype/index');
		        },1000);
		  }else{
		        MBIS.msg(json.msg,{icon:2});
		  }
	});
}

function toDel(id){
	var box = MBIS.confirm({content:"您确定要删除该数据吗?",yes:function(){
	           var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
	           	$.post(MBIS.U('admin/employeetype/del'),{id:id},function(data,textStatus){
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