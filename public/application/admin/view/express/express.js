var grid;
function initGrid(){
	grid = $("#maingrid").ligerGrid({
		url:MBIS.U('admin/express/pageQuery'),
		pageSize:MBIS.pageSize,
		pageSizeOptions:MBIS.pageSizeOptions,
		height:'99%',
        width:'100%',
        minColToggle:6,
        rownumbers:true,
        columns: [
	        { display: '快递名称', name: 'expressName', isSort: false},
	        { display: '操作', name: 'op',isSort: false,render: function (rowdata, rowindex, value){
	            var h = "";
	            if(MBIS.GRANT.KDGL_02)h += "<a href='javascript:getForEdit(" + rowdata['expressId'] + ")'>修改</a> ";
	            if(MBIS.GRANT.KDGL_03)h += "<a href='javascript:toDel(" + rowdata['expressId'] + ")'>删除</a> "; 
	            return h;
	        }}
        ]
    });
}
function toDel(id){
	var box = MBIS.confirm({content:"您确定要删除该记录吗?",yes:function(){
	           var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
	           	$.post(MBIS.U('admin/express/del'),{id:id},function(data,textStatus){
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

function getForEdit(id){
	 var loading = MBIS.msg('正在获取数据，请稍后...', {icon: 16,time:60000});
     $.post(MBIS.U('admin/express/get'),{id:id},function(data,textStatus){
           layer.close(loading);
           var json = MBIS.toAdminJson(data);
           if(json.expressId){
           		MBIS.setValues(json);
           		toEdit(json.expressId);
           }else{
           		MBIS.msg(json.msg,{icon:2});
           }
    });
}

function toEdit(id){
	var title =(id==0)?"新增":"编辑";
	var box = MBIS.open({title:title,type:1,content:$('#expressBox'),area: ['450px', '160px'],btn:['确定','取消'],yes:function(){
		$('#expressForm').submit();
	}});
	$('#expressForm').validator({
        fields: {
            expressName: {
            	rule:"required;",
            	msg:{required:"快递名称不能为空"},
            	tip:"请输入快递名称",
            	ok:"",
            },
           
        },
       valid: function(form){
		        var params = MBIS.getParams('.ipt');
	                params.expressId = id;
	                var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
	           		$.post(MBIS.U('admin/express/'+((id==0)?"add":"edit")),params,function(data,textStatus){
	           			  layer.close(loading);
	           			  var json = MBIS.toAdminJson(data);
	           			  if(json.status=='1'){
	           			    	MBIS.msg("操作成功",{icon:1});
	           			    	$('#expressForm')[0].reset();
	           			    	layer.close(box);
	           		            grid.reload();
	           			  }else{
	           			        MBIS.msg(json.msg,{icon:2});
	           			  }
	           		});

    	}

  });

}