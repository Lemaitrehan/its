var grid;
var combo;
function initGrid(){
	grid = $("#maingrid").ligerGrid({
		url:MBIS.U('admin/staffckwork/pageQuery'),
		pageSize:MBIS.pageSize,
		pageSizeOptions:MBIS.pageSizeOptions,
		height:'99%',
        width:'100%',
        minColToggle:6,
        rownumbers:true,
        columns: [
            { display: '编号', name: 'user_no',isSort: false},            
	        { display: '岗位', name: 'employee_type_id',isSort: false},
	        { display: '姓名', name: 'employee_name',isSort: false},
	        { display: '考勤类型', name: 'ckwork_type',isSort: false},
	        { display: '考勤分', name: 'xb_count',isSort: false},
	        { display: '备注', name: 'remark',isSort: false},
	        { display: '操作', name: 'op',isSort: false,
	        	render: function (rowdata){
		            var h = "";
		            if(MBIS.GRANT.YGKQ_002)h += "<a href='javascript:toEdit("+rowdata["sc_id"]+")'>修改</a> ";
		            if(MBIS.GRANT.YGKQ_003)h += "<a href='javascript:toDel("+rowdata["sc_id"]+")'>删除</a> ";
		            return h;
	        	}
	        }
        ]
    });

}

function initCombo(){
}

function loadGrid(){
	grid.set('url',MBIS.U('admin/staffckwork/pageQuery','key='+$('#key').val()));
}
function refresh(){
	if($('#key').val() !== ''){
		$('#key').val('');
	}
  	grid.set('url',MBIS.U('admin/staffckwork/pageQuery'));
}
function toEdit(id){
	location.href=MBIS.U('admin/staffckwork/toEdit','id='+id);
}

function toEdits(id){
    var params = MBIS.getParams('.ipt');
    params.id = id;
    var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
	$.post(MBIS.U('admin/staffckwork/'+((id>0)?"edit":"add")),params,function(data,textStatus){
		  layer.close(loading);
		  var json = MBIS.toAdminJson(data);
		  if(json.status=='1'){
		    	MBIS.msg(json.msg,{icon:1});
		        setTimeout(function(){ 
			    	location.href=MBIS.U('admin/staffckwork/index');
		        },1000);
		  }else{
		        MBIS.msg(json.msg,{icon:2});
		  }
	});
}

function toDel(id){
	var box = MBIS.confirm({content:"您确定要删除该数据吗?",yes:function(){
	           var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
	           	$.post(MBIS.U('admin/staffckwork/del'),{id:id},function(data,textStatus){
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
function checkemployee(){
	var employee_type_id = $('#employee_type_id').val();
	if(employee_type_id == ''){
		MBIS.msg('请选择有效选项',{icon:2});return false;
	}
	$.post(MBIS.U('admin/staffckwork/checkemployee'),{employee_type_id:employee_type_id},function(data){
		var json = MBIS.toAdminJson(data);
		if(json.status == 1){
			$('#user_no').empty();
			$('#user_no').append("<option value=''>请选择</option>");
			$.each(json.data,function(key,value){
				$('#user_no').append("<option value="+value['employee_no']+">"+value['name']+" "+value['employee_no']+"</option>");
			});
		}else{
			MBIS.msg(json.msg,{icon:2});
			$('#user_no').empty();
			$('#user_no').append("<option value=''>请选择</option>");
		}
	});
}