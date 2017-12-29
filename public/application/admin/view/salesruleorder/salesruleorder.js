var grid;
var combo;
$(function(){
	$("#from_time").ligerDateEditor({showTime: true,});
	$("#to_time").ligerDateEditor({showTime: true,});
	$("#start").ligerDateEditor({showTime: true,});
  	$("#end").ligerDateEditor({showTime: true,});
})
function initGrid(){
	grid = $("#maingrid").ligerGrid({
		url:MBIS.U('admin/salesruleorder/pageQuery'),
		pageSize:MBIS.pageSize,
		pageSizeOptions:MBIS.pageSizeOptions,
		height:'99%',
        width:'100%',
        minColToggle:9,
        rownumbers:true,
        columns: [
            { display: '规则名称', name: 'name',isSort: false},
            //{ display: '规则描述', name: 'description',isSort: false,},
            { display: '规则类型', name: 'rule_type',width:100,isSort: false},
            { display: '适用范围', name: 'rule_use',width:80,isSort: false},
	        { display: '起始时间', name: 'from_time',width:100,isSort: false},
	        { display: '截止时间', name: 'to_time',width:100,isSort: false},
	        //{ display: '过滤条件模板', name: 'c_template',isSort: false},
	        //{ display: '规则条件', name: 'conditions',isSort: false},
	        //{ display: '动作执行条件', name: 'action_conditions',isSort: false},
	        //{ display: '优惠方案模板', name: 's_template',isSort: false},
	        //{ display: '动作方案', name: 'action_solution',isSort: false},
	        { display: '会员等级', name: 'member_lv_ids',isSort: false},
	        { display: '报名时身份', name: 'member_type_ids',isSort: false},
	        { display: '是否排他', name: 'stop_rules_processing',width:50,isSort: false},
	        { display: '启用状态', name: 'status',width:50,isSort: false},
	        { display: '优先级', name: 'sort_order',width:50,isSort: false},
	        { display: '操作', name: 'op',width:70,isSort: false,
	        	render: function (rowdata){
		            var h = "";
		            if(MBIS.GRANT.YHLB_02)h += "<a href='javascript:toEdit("+rowdata["rule_id"]+")'>修改</a> ";
		            if(MBIS.GRANT.YHLB_03)h += "<a href='javascript:toDel("+rowdata["rule_id"]+")'>删除</a> ";
		            return h;
	        	}
	        }
        ]
    });

}

function initCombo(){
}

function loadGrid(){
	grid.set('url',MBIS.U('admin/salesruleorder/pageQuery','key='+$('#key').val()));
}

function ruleQuery(){
	var query = MBIS.getParams('.query');
    grid.set('url',MBIS.U('admin/salesruleorder/pageQuery',query));
}
function refresh(){
	$('.query').each(function(){
	    if($(this).val() !== ''){
	      $(this).val('');
	    }
  	});
  grid.set('url',MBIS.U('admin/salesruleorder/pageQuery'));
}

function toEdit(id){
	location.href=MBIS.U('admin/salesruleorder/toEdit','id='+id);
}

function toEdits(id){
	//var params = MBIS.getParams('.ipt');
	var params = $('#ruleForm').serialize();
	params.id = id;
    var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
	$.post(MBIS.U('admin/salesruleorder/'+((id>0)?"edit":"add")),params,function(data,textStatus){
		  layer.close(loading);
		  var json = MBIS.toAdminJson(data);
		  if(json.status=='1'){
		    	MBIS.msg(json.msg,{icon:1});
		        setTimeout(function(){ 
			    	location.href=MBIS.U('admin/salesruleorder/index');
		        },1000);
		  }else{
		        MBIS.msg(json.msg,{icon:2});
		  }
	});
}

function toDel(id){
	var box = MBIS.confirm({content:"您确定要删除该数据吗?",yes:function(){
	var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
	           	$.post(MBIS.U('admin/salesruleorder/del'),{id:id},function(data,textStatus){
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
	var type = $('#type').val();
	if(type == ''){
		MBIS.msg('请选择有效选项',{icon:2});
	}
}

function getInfo(){
	var type = $('#type').val();
	var userId = $('#userId').val();
	if(userId == '' || type == ''){
		MBIS.msg('请选择有效选项',{icon:2});	
	}
	$.post(MBIS.U('admin/sjexams/getUserInfo'),{userId:userId,type:type},function(data){
		var json = MBIS.toAdminJson(data);
		if(json.status == 1){
			$('#course_id').empty();
			$('#subject_id').empty();
			$('#course_id').append("<option value=''>请选择</option>");
			$('#subject_id').append("<option value=''>请选择</option>");
			$.each(json.data,function(key,value){
				$('#trueName').val(value.trueName);
				$('#student_no').val(value.student_no);
				$('#idcard_no').val(value.idcard_no);
				$('#exam_no').val(value.exam_no);
				$('#school_id').val(value.school);
				$('#major_id').val(value.major);
				$('#level_type').val(value.level_type);
				$('#exam_type').val(value.exam_type);
				$('#course_id').append("<option value="+value['course_id']+" selected>"+value['course']+"</option>");
				$.each(value.subjectList,function(k,val){
					$('#subject_id').append("<option value="+val['subject_id']+">"+val['name']+"</option>");
				});
			});
		}else
		if(json.status == -2){
			MBIS.msg(json.msg);
			$('#trueName').val(json.data.trueName);
			$('#student_no').val(json.data.student_no);
			$('#idcard_no').val(json.data.idcard_no);
			$('#exam_no').val(json.data.exam_no);
			$('#school_id').val(json.data.school);
			$('#major_id').val(json.data.major);
			$('#level_type').val(json.data.level_type);
			$('#exam_type').val(json.data.exam_type);
			$('#course_id').empty();
			$('#subject_id').empty();
			$('#course_id').append("<option value=''>请选择</option>");
			$('#subject_id').append("<option value=''>请选择</option>");
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