var grid;
var combo;
$(function(){
	$("#start").ligerDateEditor();
	$("#end").ligerDateEditor();
  	$("#endtime").ligerDateEditor();
})
function initGrid(){
	grid = $("#maingrid").ligerGrid({
		url:MBIS.U('admin/studentaudition/pageQuery'),
		pageSize:MBIS.pageSize,
		pageSizeOptions:MBIS.pageSizeOptions,
		height:'99%',
        width:'100%',
        minColToggle:7,
        rownumbers:true,
        columns: [
            { display: '会员姓名', name: 'userId',isSort: false},
            { display: '选择校区', name: 'campus_id',isSort: false},
            { display: '试听课程', name: 'name',isSort: false},
            { display: '课程编号', name: 'course_bn',isSort: false},
            { display: '试听科目', name: 'subject_id',isSort: false},
            { display: '业务员', name: 'ey_userId',isSort: false},
            //{ display: '员工编号', name: 'employee_no',isSort: false},
            { display: '申请人信息', name: 'username',isSort: false},
            { display: '审核状态', name: 'status',isSort: false},
	        { display: '操作', name: 'op',width:70,isSort: false,
	        	render: function (rowdata){
		            var h = "";
		            if(MBIS.GRANT.STLB_02)h += "<a href='javascript:toEdit("+rowdata["sa_id"]+")'>修改</a> ";
		            if(MBIS.GRANT.STLB_03)h += "<a href='javascript:toDel("+rowdata["sa_id"]+")'>删除</a> ";
		            return h;
	        	}
	        }
        ]
    });

}

function initCombo(){
}

function loadGrid(){
	grid.set('url',MBIS.U('admin/studentaudition/pageQuery','key='+$('#key').val()));
}

function auditionQuery(){
	var query = MBIS.getParams('.query');
    grid.set('url',MBIS.U('admin/studentaudition/pageQuery',query));
}
function refresh(){
	$('.query').each(function(){
	    if($(this).val() !== ''){
	      $(this).val('');
	    }
  	});
  	grid.set('url',MBIS.U('admin/studentaudition/pageQuery'));
}
function toEdit(id){
	location.href=MBIS.U('admin/studentaudition/toEdit','id='+id);
}

function toDetail(id){
	location.href=MBIS.U('admin/studentaudition/toDetail','id='+id);
}

function toEdits(id){
	var userId = $('#userId').val();
	var username = $('#username').val();
	if((userId == '') && (username == '')){
		MBIS.msg('请选择会员信息或输入申请人信息',{icon:2});
		return false;
	}
    //var params = MBIS.getParams('.ipt');
    var params = $('#auditionForm').serialize();
    params.id = id;
    var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
	$.post(MBIS.U('admin/studentaudition/'+((id>0)?"edit":"add")),params,function(data,textStatus){
		  layer.close(loading);
		  var json = MBIS.toAdminJson(data);
		  if(json.status=='1'){
		    	MBIS.msg(json.msg,{icon:1});
		        setTimeout(function(){ 
			    	location.href=MBIS.U('admin/studentaudition/index');
		        },1000);
		  }else{
		        MBIS.msg(json.msg,{icon:2});
		  }
	});
}

function toDel(id){
	var box = MBIS.confirm({content:"您确定要删除该数据吗?",yes:function(){
	           var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
	           	$.post(MBIS.U('admin/studentaudition/del'),{id:id},function(data,textStatus){
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
function majorSelect(){
	var major_id = $('#major_id').val();
	if(major_id != ''){
		$.post(MBIS.U('admin/studentaudition/getcoursesubjectInfo'),{major_id:major_id},function(data){
			var json = MBIS.toAdminJson(data);
			if(json.status == 1){
				$('#course_id').empty();
				$('#name').val('');
				$('#course_bn').val('');
				$('#course_id').append("<option value=''>请选择</option>");
				$.each(json.data.course,function(key,value){
					$('#course_id').append("<option value="+value['course_id']+">"+value['name']+"</option>");
				});
				$('#subject_id').empty();
				$('#subject_id').append("<option value=''>请选择</option>");
				$.each(json.data.subject,function(k,v){
					$('#subject_id').append("<option value="+v['subject_id']+">"+v['name']+"</option>");
				});
			}else{
				MBIS.msg(json.msg,{icon:2});
			}
		});
	}else{
		$('#course_id').empty();
		$('#course_id').append("<option value=''>请选择</option>");
		$('#name').val('');
		$('#course_bn').val('');
		$('#subject_id').empty();
		$('#subject_id').append("<option value=''>请选择</option>");
	}
}
function courseSelect(){
	var course_id = $('#course_id').val();
	if(course_id != ''){
		$.post(MBIS.U('admin/studentaudition/getcourseInfo'),{course_id:course_id},function(data){
			var json = MBIS.toAdminJson(data);
			if(json.status == 1){
				$('#name').val(json.data.name);
				$('#course_bn').val(json.data.course_bn);
			}else{
				MBIS.msg(json.msg,{icon:2});
			}
		});
	}else{
		$('#name').val('');
		$('#course_bn').val('');
	}
}
function employeeSelect(){
	var ey_userId = $('#ey_userId').val();
	if(ey_userId != ''){
		$.post(MBIS.U('admin/studentaudition/getemployeeInfo'),{employee_id:ey_userId},function(data){
			var json = MBIS.toAdminJson(data);
			if(json.status == 1){
			  $('#employee_no').val(json.data.employee_no);
			  $('#username').val(json.data.name);
			}else{
			  MBIS.msg(json.msg,{icon:2});
			}
		});
	}else{
		$('#employee_no').val('');
		$('#username').val('');
	}
}