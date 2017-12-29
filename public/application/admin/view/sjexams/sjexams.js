var grid;
var combo;
$(function(){
	$("#exam_time").ligerDateEditor();
	$("#startDate").ligerDateEditor();
	$("#start").ligerDateEditor();
  	$("#end").ligerDateEditor();
})
function initGrid(type_id){
	$("#maingrid").remove();
	grid = $("#maingrid").ligerGrid({
		url:MBIS.U('admin/sjexams/pageQuery','type_id='+type_id),
		pageSize:MBIS.pageSize,
		pageSizeOptions:MBIS.pageSizeOptions,
		height:'99%',
        width:'100%',
        minColToggle:9,
        rownumbers:true,
        columns: [
            { display: '姓名', name: 'name',width:60,isSort: false},
            { display: '学员编号', name: 'student_no',width:80,isSort: false,},
	        { display: '身份证号', name: 'idcard_no',isSort: false},
	        { display: '准考证号', name: 'exam_no',width:80,isSort: false},
	        { display: '报考专业', name: 'major_id',isSort: false},
	        { display: '考试课程', name: 'course_id',isSort: false},
	        { display: '考试科目', name: 'subject_id',isSort: false},
	        { display: '考试时间', name: 'exam_time',isSort: false},
	        { display: '考试成绩', name: 'subject_score',width:60,isSort: false},
	        { display: '操作', name: 'op',width:80,isSort: false,
	        	render: function (rowdata){
	        		if(type_id == 2){
			        /*    var h = "";
			            if(MBIS.GRANT.KSCJ_02)h += "<a href='javascript:toEdit("+rowdata["id"]+","+type_id+")'>修改</a> ";
			            if(MBIS.GRANT.KSCJ_03)h += "<a href='javascript:toDel("+rowdata["id"]+")'>删除</a> ";
			            //if(MBIS.GRANT.)h += "<a href='javascript:toDetail("+rowdata["id"]+")'>完善资料</a> ";
			            return h;*/
		           	}else
		         /*  	if(type_id ==1){
		           		var h = "";
			            if(MBIS.GRANT.CKCJ_02)h += "<a href='javascript:toEdit("+rowdata["id"]+","+type_id+")'>修改</a> ";
			            if(MBIS.GRANT.CKCJ_03)h += "<a href='javascript:toDel("+rowdata["id"]+")'>删除</a> ";
			            //if(MBIS.GRANT.)h += "<a href='javascript:toDetail("+rowdata["id"]+")'>完善资料</a> ";
			            return h;
		           	}*/
	        	}
	        }
        ]
    });
}

function initCombo(){
}

function loadGrid(){
	grid.set('url',MBIS.U('admin/sjexams/pageQuery','key='+$('#key').val()));
}

function examQuery(){
	var query = MBIS.getParams('.query');
    grid.set('url',MBIS.U('admin/sjexams/pageQuery',query));
}
function refresh(type_id){
	$('.query').each(function(){
	    if($(this).val() !== ''){
	      $(this).val('');
	    }
  	});
  	$('#type_id').val(type_id);
  	grid.set('url',MBIS.U('admin/sjexams/pageQuery','type_id='+type_id));
}

function toEdit(id,type_id){
	location.href=MBIS.U('admin/sjexams/toEdit','id='+id+'&type_id='+type_id);
}

function toDetail(id){
	location.href=MBIS.U('admin/sjexams/toDetail','id='+id);
}
function toEdits(id,type_id){
	if(type_id=='1'){
    	var urlIndex = 'admin/sjexams/indexEdu';
    }else{
    	var urlIndex = 'admin/sjexams/indexSkill';
    }
    var params = MBIS.getParams('.ipt');
    params.id = id;
    var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
	$.post(MBIS.U('admin/sjexams/'+((id>0)?"edit":"add")),params,function(data,textStatus){
		  layer.close(loading);
		  var json = MBIS.toAdminJson(data);
		  if(json.status=='1'){
		    	MBIS.msg(json.msg,{icon:1});
		        setTimeout(function(){ 
			    	location.href=MBIS.U(urlIndex);
		        },1000);
		  }else{
		        MBIS.msg(json.msg,{icon:2});
		  }
	});
}

function toDel(id){
	var box = MBIS.confirm({content:"您确定要删除该数据吗?",yes:function(){
	var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
	           	$.post(MBIS.U('admin/sjexams/del'),{id:id},function(data,textStatus){
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

function check(){
	var type = $('#examination_type').val();
	if(type == ''){
		$('#name').val('');
		$('#student_no').val('');
		$('#idcard_no').val('');
		$('#exam_no').val('');
		$('#school_id').val('');
		$('#school_name').val('');
		$('#major_id').val('');
		$('#major_name').val('');
		$('#course_id').val('');
		$('#course_name').val('');
		$('#userId').empty();
		$('#subject_id').empty();
		$('#userId').append("<option value=''>请选择</option>");
		$('#subject_id').append("<option value=''>请选择</option>");return false;
	}
	$.post(MBIS.U('admin/sjexams/getUsersList'),{type:type},function(data,textStatus){
		var json = MBIS.toAdminJson(data);
		if(json.status == 1){
			$('#name').val('');
			$('#student_no').val('');
			$('#idcard_no').val('');
			$('#exam_no').val('');
			$('#school_id').val('');
			$('#school_name').val('');
			$('#major_id').val('');
			$('#major_name').val('');
			$('#course_id').val('');
			$('#course_name').val('');
			$('#userId').empty();
			$('#subject_id').empty();
			$('#userId').append("<option value=''>请选择</option>");
			$('#subject_id').append("<option value=''>请选择</option>");
			$.each(json.data,function(k,v){
				$('#userId').append("<option courseKey="+v['course_id']+" entryKey="+v['entry_id']+" schoolKey="+v['school_id']+" majorKey="+v['major_id']+" value="+v['userId']+">"+v['trueName']+"("+v['userId']+" -- "+v['course_name']+")</option>");
			});
		}else{
			MBIS.msg(json.msg,{icon:2});
			$('#name').val('');
			$('#student_no').val('');
			$('#idcard_no').val('');
			$('#exam_no').val('');
			$('#school_id').val('');
			$('#school_name').val('');
			$('#major_id').val('');
			$('#major_name').val('');
			$('#course_id').val('');
			$('#course_name').val('');
			$('#userId').empty();
			$('#subject_id').empty();
			$('#userId').append("<option value=''>请选择</option>");
			$('#subject_id').append("<option value=''>请选择</option>");
		}
	});
}
function getInfo(){
	var type = $('#examination_type').val();
	if(type == ''){
		MBIS.msg('请先选择报考类型');return false;
	}
	var userId = $('#userId').val();
	var course_id = $('#userId option:selected').attr("courseKey");
	var entry_id = $('#userId option:selected').attr("entryKey");
	var school_id = $('#userId option:selected').attr("schoolKey");
	var major_id = $('#userId option:selected').attr("majorKey");
	if(userId == ''){
		$('#name').val('');
		$('#student_no').val('');
		$('#idcard_no').val('');
		$('#exam_no').val('');
		$('#school_id').val('');
		$('#school_name').val('');
		$('#major_id').val('');
		$('#major_name').val('');
		$('#course_id').val('');
		$('#course_name').val('');
		$('#subject_id').empty();
		$('#subject_id').append("<option value=''>请选择</option>");return false;
	}
	
	$.post(MBIS.U('admin/sjexams/getUserInfo'),{userId:userId,type:type,course_id:course_id,entry_id:entry_id,school_id:school_id,major_id:major_id},function(data){
		var json = MBIS.toAdminJson(data);
		if(json.status == 1){
			$('#name').val('');
			$('#student_no').val('');
			$('#idcard_no').val('');
			$('#exam_no').val('');
			$('#school_id').val('');
			$('#school_name').val('');
			$('#major_id').val('');
			$('#major_name').val('');
			$('#course_id').val('');
			$('#course_name').val('');
			$('#subject_id').empty();
			$('#subject_id').append("<option value=''>请选择</option>");
			$.each(json.data,function(key,value){
				$('#name').val(value.trueName);
				$('#student_no').val(value.student_no);
				$('#idcard_no').val(value.idcard);
				$('#exam_no').val(value.exam_no);
				$('#school_id').val(value.school_id);
				$('#school_name').val(value.school_name);
				$('#major_id').val(value.major_id);
				$('#major_name').val(value.major_name);
				$('#course_id').val(value.course_id);
				$('#course_name').val(value.course_name);
				$.each(value.subjectList,function(k,val){
					$('#subject_id').append("<option value="+val['subject_id']+">"+val['name']+"</option>");
				});
			});
		}else
		if(json.status == -1){
			
			$('#name').val('');
			$('#student_no').val('');
			$('#idcard_no').val('');
			$('#exam_no').val('');
			$('#school_id').val('');
			$('#school_name').val('');
			$('#major_id').val('');
			$('#major_name').val('');
			$('#course_id').val('');
			$('#course_name').val('');
			$('#subject_id').empty();
			$('#subject_id').append("<option value=''>请选择</option>");
			MBIS.msg(json.msg);
		}
	});
}

function getCourses(){
	var type_id = $('#type_id').val();
	$.post(MBIS.U('admin/sjexams/getCourses'),{type_id:type_id},function(data){
		var json = MBIS.toAdminJson(data);
		if(json.status == 1){
			$('#course_id').empty();
			$('#course_id').append("<option value=''>请选择</option>");
			$.each(json.data,function(k,v){
				$('#course_id').append("<option value="+v['course_id']+">"+v['name']+"</option>");
			});
		}else
		if(json.status == 2){
			$('#course_id').empty();
			$('#course_id').append("<option value=''>请选择</option>");
			$.each(json.data,function(k,v){
				$('#course_id').append("<option value="+v['course_id']+">"+v['name']+"</option>");
			});
		}
	});
}