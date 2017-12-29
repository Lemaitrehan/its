var grid;
function initGrid(){
	grid = $("#maingrid").ligerGrid({
		url:MBIS.U('admin/banks/pageQuery'),
		pageSize:MBIS.pageSize,
		pageSizeOptions:MBIS.pageSizeOptions,
		height:'99%',
        width:'100%',
        minColToggle:6,
        rownumbers:true,
        columns: [
	        { display: '银行名称', name: 'bankName', isSort: false},
	        { display: '操作', name: 'op',isSort: false,render: function (rowdata, rowindex, value){
	            var h = "";
	            if(MBIS.GRANT.YHGL_02)h += "<a href='javascript:getForEdit(" + rowdata['bankId'] + ")'>修改</a> ";
	            if(MBIS.GRANT.YHGL_03)h += "<a href='javascript:toDel(" + rowdata['bankId'] + ")'>删除</a> "; 
	            return h;
	        }}
        ]
    });
}
function toDel(id){
	var box = MBIS.confirm({content:"您确定要删除该记录吗?",yes:function(){
	           var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
	           	$.post(MBIS.U('admin/banks/del'),{id:id},function(data,textStatus){
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
     $.post(MBIS.U('admin/banks/get'),{id:id},function(data,textStatus){
           layer.close(loading);
           var json = MBIS.toAdminJson(data);
           if(json.bankId){
           		MBIS.setValues(json);
           		toEdit(json.bankId);
           }else{
           		MBIS.msg(json.msg,{icon:2});
           }
    });
}

function toEdit(id){
	var title =(id==0)?"新增":"编辑";
	var box = MBIS.open({title:title,type:1,content:$('#bankBox'),area: ['450px', '160px'],btn:['确定','取消'],yes:function(){
		$('#bankForm').submit();
	}});
	$('#bankForm').validator({
        fields: {
            bankName: {
            	rule:"required;",
            	msg:{required:"银行名称不能为空"},
            	tip:"请输入银行名称",
            	ok:"",
            },
           
        },
       valid: function(form){
		        var params = MBIS.getParams('.ipt');
	                params.bankId = id;
	                var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
	           		$.post(MBIS.U('admin/banks/'+((id==0)?"add":"edit")),params,function(data,textStatus){
	           			  layer.close(loading);
	           			  var json = MBIS.toAdminJson(data);
	           			  if(json.status=='1'){
	           			    	MBIS.msg("操作成功",{icon:1});
	           			    	$('#bankForm')[0].reset();
	           			    	layer.close(box);
	           		            grid.reload();
	           			  }else{
	           			        MBIS.msg(json.msg,{icon:2});
	           			  }
	           		});

    	}

  });

}