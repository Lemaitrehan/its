var grid;
var combo;
$(function(){
	$("#start").ligerDateEditor();
	$("#end").ligerDateEditor();
  	$("#endtime").ligerDateEditor();
})
function initGrid(){
	grid = $("#maingrid").ligerGrid({
		url:MBIS.U('admin/studentrushfeelog/pageQuery'),
		pageSize:MBIS.pageSize,
		pageSizeOptions:MBIS.pageSizeOptions,
		height:'99%',
        width:'100%',
        minColToggle:7,
        rownumbers:true,
        columns: [
            { display: '会员姓名', name: 'userId',width:50,isSort: false},
            { display: '学员编号', name: 'student_no',isSort: false},
            { display: '账单编号', name: 'fush_fee_no',isSort: false},
            { display: '购买课程', name: 'course_id',isSort: false},
            { display: '收入金额', name: 'income_fee',isSort: false},
            { display: '收入点数', name: 'income_point',isSort: false},
            { display: '未缴金额', name: 'unpaid_fee',isSort: false},
            { display: '状态', name: 'status',isSort: false},
            { display: '通知信息', name: 'notice_tmpl_id',isSort: false},
	        { display: '截止时间', name: 'endtime',isSort: false},
	        { display: '操作', name: 'op',width:60,isSort: false,
	        	render: function (rowdata){
		            var h = "";
		            if(MBIS.GRANT.CFCK_02)h += "<a href='javascript:toEdit("+rowdata["rush_fee_id"]+")'>修改</a> ";
		            if(MBIS.GRANT.CFCK_03)h += "<a href='javascript:toDel("+rowdata["rush_fee_id"]+")'>删除</a> ";
		            return h;
	        	}
	        }
        ]
    });

}

function initCombo(){
}

function loadGrid(){
	grid.set('url',MBIS.U('admin/studentrushfeelog/pageQuery','key='+$('#key').val()));
}

function feeLogQuery(){
	var query = MBIS.getParams('.query');
    grid.set('url',MBIS.U('admin/studentrushfeelog/pageQuery',query));
}
function refresh(){
	$('.query').each(function(){
	    if($(this).val() !== ''){
	      $(this).val('');
	    }
  	});
	grid.set('url',MBIS.U('admin/studentrushfeelog/pageQuery'));
}
function toEdit(id){
	location.href=MBIS.U('admin/studentrushfeelog/toEdit','id='+id);
}

function toDetail(id){
	location.href=MBIS.U('admin/studentrushfeelog/toDetail','id='+id);
}

function toEdits(id){
    var params = MBIS.getParams('.ipt');
    params.id = id;
    var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
	$.post(MBIS.U('admin/studentrushfeelog/'+((id>0)?"edit":"add")),params,function(data,textStatus){
		  layer.close(loading);
		  var json = MBIS.toAdminJson(data);
		  if(json.status=='1'){
		    	MBIS.msg(json.msg,{icon:1});
		        setTimeout(function(){ 
			    	location.href=MBIS.U('admin/studentrushfeelog/index');
		        },1000);
		  }else{
		        MBIS.msg(json.msg,{icon:2});
		  }
	});
}

function toDel(id){
	var box = MBIS.confirm({content:"您确定要删除该数据吗?",yes:function(){
	           var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
	           	$.post(MBIS.U('admin/studentrushfeelog/del'),{id:id},function(data,textStatus){
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

function getInfo(){
	var userId = $('#userId').val();
	if(userId !== ''){
		$.post(MBIS.U('admin/studentrushfeelog/getInfo'),{userId:userId},function(data){
			var json = MBIS.toAdminJson(data);
			if(json.status == 1){
				$.each(json.data,function(k,v){
					$('#student_no').val(v.student_no);
					$('#fush_fee_no').val(v.orderNo);
					$('#course_id').val(v.course_id);
					$('#course_name').val(v.course_name);
				});
			}
			else
			if(json.status == -1){
				$.each(json.data,function(k,v){
					$('#student_no').val(v.student_no);
					MBIS.msg(json.msg,{icon:2});
					$('#fush_fee_no').val('');
					$('#fush_fee_no').val('');
					$('#course_id').val('');
					$('#course_name').val('');
				});
				
			}
		});
	}else
	if(userId == ''){
		$('#student_no').val('');
		$('#fush_fee_no').val('');
		$('#fush_fee_no').val('');
		$('#course_id').val('');
		$('#course_name').val('');
	}
}

function chooseTmpl(){   //获取选中的模板
	var notice_id = $('#notice_tmpl_id option:selected').val();
      $.post(MBIS.U('admin/studentrushfeelog/chooseTmpl'),{notice_id:notice_id},function(data){
        if(data.status == 1){
        	$('#content-str').hide();
        	$('#content-td').html(data.content);
          	$('#content-str').show();
        }else
        if(data.status == -1 || data.status == 0){
          	$('#content-str').hide();
        }

      });
}

