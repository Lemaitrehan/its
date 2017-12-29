var grid;
var combo;
$(function(){
	$("#induction_time").ligerDateEditor();
	$("#dimission_time").ligerDateEditor();
	$("#start").ligerDateEditor();
  	$("#end").ligerDateEditor();
})
function initGrid(){
	grid = $("#maingrid").ligerGrid({
		url:MBIS.U('admin/employee/pageQuery'),
		pageSize:MBIS.pageSize,
		pageSizeOptions:MBIS.pageSizeOptions,
		height:'99%',
        width:'100%',
        minColToggle:9,
        rownumbers:true,
        columns: [
            { display: '姓名', name: 'name',isSort: false},
            { display: '性别', name: 'sex',width:50,isSort: false},
            { display: '编号', name: 'employee_no',width:80,isSort: false},
            { display: '部门', name: 'department_id',width:200,isSort: false},
	        { display: '岗位', name: 'employee_type_id',isSort: false},
	        { display: '电话', name: 'mobile',isSort: false},
	        { display: '入职时间', name: 'induction_time',isSort: false},
	        { display: '工作状态', name: 'status',width:50,isSort: false},
	        { display: '工作方式', name: 'cooperation_type',width:50,isSort: false},
	        { display: '操作', name: 'op',width:80,isSort: false,
	        	render: function (rowdata){
		            var h = "";
		            if(MBIS.GRANT.YGCX_02)h += "<a href='javascript:toEdit("+rowdata["employee_id"]+")'>修改</a> ";
		            if(MBIS.GRANT.YGCX_03)h += "<a href='javascript:toDel("+rowdata["employee_id"]+")'>删除</a> ";
		            //if(MBIS.GRANT.YGCX_04)h += "<a href='javascript:toDetail("+rowdata["employee_id"]+")'>完善资料</a> ";
		            return h;
	        	}
	        }
        ]
    });

}

function initCombo(){
}

function loadGrid(){
	grid.set('url',MBIS.U('admin/employee/pageQuery','key='+$('#key').val()));
}
function employeeQuery(){
	var query = MBIS.getParams('.query');
    grid.set('url',MBIS.U('admin/employee/pageQuery',query));
}
function refresh(){
	$('.query').each(function(){
	    if($(this).val() !== ''){
	      $(this).val('');
	    }
  	});
  	grid.set('url',MBIS.U('admin/employee/pageQuery'));
}
function toEdit(id){
	location.href=MBIS.U('admin/employee/toEdit','id='+id);
}

function toDetail(id){
	location.href=MBIS.U('admin/employee/toDetail','id='+id);
}

function toEdits(id){
    var params = MBIS.getParams('.ipt');
    params.id = id;
    var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
	$.post(MBIS.U('admin/employee/'+((id>0)?"edit":"add")),params,function(data,textStatus){
		  layer.close(loading);
		  var json = MBIS.toAdminJson(data);
		  if(json.status=='1'){
		    	MBIS.msg(json.msg,{icon:1});
		        setTimeout(function(){ 
			    	location.href=MBIS.U('admin/employee/index');
		        },1000);
		  }else{
		        MBIS.msg(json.msg,{icon:2});
		  }
	});
}

function toDel(id){
	var box = MBIS.confirm({content:"您确定要删除该数据吗?",yes:function(){
	           var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
	           	$.post(MBIS.U('admin/employee/del'),{id:id},function(data,textStatus){
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
function checkType(){
	var departmentId = $('#department_id').val();
	if(departmentId == ''){
		MBIS.msg('请选择有效选项',{icon:2});return false;
	}
	$.post(MBIS.U('admin/employee/checkType'),{departmentId:departmentId},function(data){
		var json = MBIS.toAdminJson(data);
		if(json.status == 1){
			$('#employee_type_id').empty();
			$('#employee_type_id').append("<option value=''>请选择</option>");
			$.each(json.data,function(key,value){
				$('#employee_type_id').append("<option value="+value['employee_type_id']+">"+value['name']+"</option>");
			});
		}else{
			MBIS.msg(json.msg,{icon:2});
			$('#employee_type_id').empty();
			$('#employee_type_id').append("<option value=''>请选择</option>");
		}
	});
}
function checkdep(){
  var department_id = $('#department_id').val();
  if(department_id == ''){
    $('#employee_type_id').empty();
    $('#employee_type_id').append("<option value=''>请选择</option>");return false;
  }
  $.post(MBIS.U('admin/employee/checkdep'),{department_id:department_id},function(data){
    var json = MBIS.toAdminJson(data);
    if(json.status == 1){
      $('#employee_type_id').empty();
      $('#employee_type_id').append("<option value=''>请选择</option>");
      $.each(json.data,function(key,value){
        $('#employee_type_id').append("<option value="+value['employee_type_id']+">"+value['name']+"</option>");
      });
    }else{
      MBIS.msg(json.msg,{icon:2});
      $('#employee_type_id').empty();
      $('#employee_type_id').append("<option value=''>请选择</option>");
    }
  });
}