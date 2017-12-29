var grid;
var combo;
var type1;
$(function(){
  $("#createtime").ligerDateEditor();
  $("#start").ligerDateEditor();
  $("#end").ligerDateEditor();
})
function initGrid(type_id){
	type1 = type_id;
	grid = $("#maingrid").ligerGrid({
		url:MBIS.U('admin/currentckwork/pageQuery', 'type_id=' + type_id),
		pageSize:MBIS.pageSize,
		pageSizeOptions:MBIS.pageSizeOptions,
		height:'99%',
        width:'100%',
        minColToggle:6,
        rownumbers:true,
        columns: [
          	{ display: '学员编号', name: 'user_no',isSort: false},            
	      	{ display: '学员姓名', name: 'trueName',isSort: false},
          	{ display: '考勤类型', name: 'ckwork_type',isSort: false},
	      	{ display: '考勤课程', name: 'object_id',isSort: false},
	        { display: '消耗学币数', name: 'xb_count',isSort: false},
	        { display: '考勤日期', name: 'createtime',isSort: false},
	        { display: '备注', name: 'remark',isSort: false},
	        { display: '操作', name: 'op',isSort: false,
	        	render: function (rowdata){
		            var h = "";
		            if( ( type_id ==1 && MBIS.GRANT.CKKQ_02 ) ||  ( type_id ==2 && MBIS.GRANT.XYKQ_02 ) )
		            	h += "<a href='javascript:toEdit("+rowdata["cc_id"]+','+type_id+")'>修改</a> ";
		            if( ( type_id ==1 && MBIS.GRANT.CKKQ_03 ) ||  ( type_id ==2 && MBIS.GRANT.XYKQ_03 ) )
		            	h += "<a href='javascript:toDel("+rowdata["cc_id"]+")'>删除</a> ";
		            return h;
	        	}
	        }
        ]
    });

}

function initCombo(){
}

function loadGrid(){
	grid.set('url',MBIS.U('admin/currentckwork/pageQuery','key='+$('#key').val()));
}

function ckworkQuery(){
	var query = MBIS.getParams('.query');
    grid.set('url',MBIS.U('admin/currentckwork/pageQuery',query));
}
function refresh(type_id){
	$('.query').each(function(){
	    if($(this).val() !== ''){
	      $(this).val('');
	    }
  	});
  	$('#type_id').val(type_id);
	grid.set('url',MBIS.U('admin/currentckwork/pageQuery','type_id='+type_id));
}

function toEdit(id,type_id){
	location.href=MBIS.U('admin/currentckwork/toEdit','id='+id+'&type_id='+type_id);
}

function toEdits(id,type_id){
	if(type_id == 1){
		var indexurl = 'admin/currentckwork/indexEdu';
	}else{
		var indexurl = 'admin/currentckwork/indexSkill';
	}
    var params = MBIS.getParams('.ipt');
    params.id = id;
    var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
	$.post(MBIS.U('admin/currentckwork/'+((id>0)?"edit":"add")),params,function(data,textStatus){
		  layer.close(loading);
		  var json = MBIS.toAdminJson(data);
		  if(json.status=='1'){
		    	MBIS.msg(json.msg,{icon:1});
		        setTimeout(function(){ 
			    	location.href=MBIS.U(indexurl);
		        },1000);
		  }else{
		        MBIS.msg(json.msg,{icon:2});
		  }
	});
}

function toDel(id){
	var box = MBIS.confirm({content:"您确定要删除该数据吗?",yes:function(){
	           var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
	           	$.post(MBIS.U('admin/currentckwork/del'),{id:id},function(data,textStatus){
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

function search(){
	var student_no = $('#student_no').val();
	if(student_no == ''){  //没有输入编号
		MBIS.msg('请填写正确的学员编号',{icon:2});
		$('#student_no').focus();
	}
	/*
	if(student_no !== ''){ //有输入编号

	}
	*/

	$.post(MBIS.U('admin/currentckwork/search'),{student_no:student_no},function(data){
        if(data.status == 1){
        	$('#trueName').val(data.trueName);
        	if(data.e_course_id == null){
        		$('#object_name').val(data.k_course_name);
        		$('#object_id').val(data.k_course_id);
        	}else
        	if(data.k_course_id == null){
        		$('#object_name').val(data.e_course_name);
        		$('#object_id').val(data.e_course_id);
        	}
        }else
        if(data.status == -1 || data.status == 0){
          	MBIS.msg(data.msg,{icon:2});
        }
    });
}