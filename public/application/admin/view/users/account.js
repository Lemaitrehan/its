var grid;
function initGrid(){
	grid = $("#maingrid").ligerGrid({
		url:MBIS.U('admin/users/pageQuery'),
		pageSize:MBIS.pageSize,
		pageSizeOptions:MBIS.pageSizeOptions,
		height:'99%',
        width:'100%',
        minColToggle:6,
        rownumbers:true,
        columns: [
	        { display: '用户名', name: 'loginName', isSort: false},
	        { display: '真实姓名', name: 'trueName', isSort: false},
	        { display: '手机号码', name: 'userPhone', isSort: false},
	        { display: '电子邮箱', name: 'userEmail', isSort: false},
	        { display: '最后登录时间', name: 'lastTime', isSort: false},
	        { display: '状态', name: 'userStatus', isSort: false, render:function(rowdata, rowindex, value){
	        	return (value==1)?'<span style="cursor:pointer;" onclick="changeUserStatus('+rowdata['userId']+',0)">启用</span>':'<span style="cursor:pointer;" onclick="changeUserStatus('+rowdata['userId']+',1)">停用</span>';
	        }},
	        { display: '操作', name: 'op',isSort: false,render: function (rowdata, rowindex, value){
	            var h = "";
	            if(MBIS.GRANT.ZHGL_02)h += "<a href='javascript:getForEdit(" + rowdata['userId'] + ")'>修改</a> ";
	            return h;
	        }}
        ]
    });
}

function getForEdit(id){
	 var loading = MBIS.msg('正在获取数据，请稍后...', {icon: 16,time:60000});
     $.post(MBIS.U('admin/users/get'),{id:id},function(data,textStatus){
           layer.close(loading);
           var json = MBIS.toAdminJson(data);
           //清空密码
           json.loginPwd = '';
           if(json.userId){
           		MBIS.setValues(json);
           		$('#userId').val(json.userId);
           		toEdit(json.userId);
           }else{
           		MBIS.msg(json.msg,{icon:2});
           }
    });
}

function toEdit(id){
	var box = MBIS.open({title:'编辑',type:1,content:$('#accountBox'),area: ['450px', '260px'],btn:['确定','取消'],yes:function(){
					$('#accountForm').isValid(function(v){
						if(v){
							//var params = MBIS.getParams('.ipt');
                            var params = $('#accountForm').serialize();
			                if(id>0)
			                	params.userId = id;
			                var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
			           		$.post(MBIS.U('admin/users/editAccount'),params,function(data,textStatus){
			           			  layer.close(loading);
			           			  var json = MBIS.toAdminJson(data);
			           			  if(json.status=='1'){
			           			    	MBIS.msg("操作成功",{icon:1});
			           			    	$('#accountForm')[0].reset();
			           			    	layer.close(box);
			           		            grid.reload();
			           			  }else{
			           			        MBIS.msg(json.msg,{icon:2});
			           			  }
			           		});
						}else{
							return false;
						}
					});
		        	
		

	},cancel:function(){$('#accountForm')[0].reset();},end:function(){$('#accountForm')[0].reset();}});

}

function changeUserStatus(id, status){
	if(!MBIS.GRANT.ZHGL_02)return;
	$.post(MBIS.U('admin/Users/changeUserStatus'), {'id':id, 'status':status}, function(data, textStatus){
		var json = MBIS.toAdminJson(data);
	           			  if(json.status=='1'){
	           			    	MBIS.msg("操作成功",{icon:1});
	           		            grid.reload();
	           			  }else{
	           			    	MBIS.msg(json.msg,{icon:2});
	           			  }
	})
}


function accountQuery(){
          var query = MBIS.getParams('.query');
			    grid.set('url',MBIS.U('admin/Users/pageQuery',query));
			}

		