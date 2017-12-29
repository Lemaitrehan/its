var grid;
var combo;
function initGrid(){
	grid = $("#maingrid").ligerGrid({
		url:MBIS.U('admin/partners/pageQuery'),
		pageSize:MBIS.pageSize,
		pageSizeOptions:MBIS.pageSizeOptions,
		height:'99%',
        width:'100%',
        minColToggle:5,
        rownumbers:true,
        columns: [
            { display: '合作方名称', name: 'name',isSort: false,},
            { display: '负责人',name: 'principal',isSort: false},
            { display: '负责人电话',name: 'principal_mobile',isSort: false},
            { display: '合作类型',name: 'business_type',isSort: false},
	        { display: '主营业务', name: 'business_info',isSort: false},
	        { display: '操作', name: 'op',isSort: false,
	        	render: function (rowdata){
		            var h = "";
		            if(MBIS.GRANT.HZFGL_02)h += "<a href='javascript:toEdit("+rowdata["p_id"]+")'>修改</a> ";
		            if(MBIS.GRANT.HZFGL_03)h += "<a href='javascript:toDel("+rowdata["p_id"]+")'>删除</a> ";
					//if(MBIS.GRANT.)h += "<a href='javascript:toEditJx("+rowdata["p_id"]+")'>绩效管理</a> ";
		            return h;
	        	}
	        }
        ]
    });

}

function initCombo(){
}

function loadGrid(){
	grid.set('url',MBIS.U('admin/partners/pageQuery','key='+$('#key').val()));
}
function refresh(){
	if($('#key').val() !== ''){
		$('#key').val('');
	}
  	grid.set('url',MBIS.U('admin/partners/pageQuery'));
}
function toEdit(id){
	location.href=MBIS.U('admin/partners/toEdit','id='+id);
}

function toEdits(id){
    var params = MBIS.getParams('.ipt');
    params.id = id;
    var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
	$.post(MBIS.U('admin/partners/'+((id>0)?"edit":"add")),params,function(data,textStatus){
		  layer.close(loading);
		  var json = MBIS.toAdminJson(data);
		  if(json.status=='1'){
		    	MBIS.msg(json.msg,{icon:1});
		        setTimeout(function(){ 
			    	location.href=MBIS.U('admin/partners/index');
		        },1000);
		  }else{
		        MBIS.msg(json.msg,{icon:2});
		  }
	});
}

function toDel(id){
	var box = MBIS.confirm({content:"您确定要删除该数据吗?",yes:function(){
	           var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
	           	$.post(MBIS.U('admin/partners/del'),{id:id},function(data,textStatus){
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