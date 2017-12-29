var grid;
var combo;
function initGrid(){
        var shcool_id = $('#school_id').val();
        console.log(school_id);
	grid = $("#maingrid").ligerGrid({
		url:MBIS.U('admin/grade/pageQuery_g','school_id='+school_id),
		pageSize:MBIS.pageSize,
		pageSizeOptions:MBIS.pageSizeOptions,
		height:'99%',
                width:'100%',
                minColToggle:6,
                rownumbers:true,
                columns: [
                    { display: '年级名称', name: 'name',isSort: false},
                    { display: '专业', name: 'major_id',isSort: false},
                    { display: '学习时间/年', name: 'stu_time',isSort: false},
                    { display: '标准学费', name: 'stu_fee',isSort: false},
                    { display: '市场价', name: 'market_price',isSort: false},
                    { display: '学习阶段时间', name: 'stu_stage',isSort: false},
                    { display: '开始报名时间', name: 'rp_start_time',isSort: false},
                    { display: '最后报名时间', name: 'rp_end_time',isSort: false},
                    { display: '操作', name: 'op',width: 100,isSort: false,
                        render: function (rowdata){
                            var h = "";
                            if(MBIS.GRANT.XLZY_042)h += "<a href='javascript:toEdit("+rowdata["grade_id"]+")'>修改</a> ";
                            if(MBIS.GRANT.XLZY_043)h += "<a href='javascript:toDel("+rowdata["grade_id"]+")'>删除</a> "; 
                            return h;
                        }
                    }
                ]
        });

}

function initCombo(){
}

function loadGrid(major_id){
	grid.set('url',MBIS.U('admin/grade/pageQuery','key='+$('#key').val()+'&major_id='+major_id));
}

function toEdit(id,major_id){
	location.href=MBIS.U('admin/grade/toEdit','id='+id+'&major_id='+major_id);
}

function toEdits(id){
    //var params = MBIS.getParams('.ipt');
    var params = $('#infoForm').serialize();
    params.id = id;
    var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
	$.post(MBIS.U('admin/grade/'+((id>0)?"edit":"add")),params,function(data,textStatus){
		  layer.close(loading);
		  var json = MBIS.toAdminJson(data);
		  if(json.status=='1'){
		    	MBIS.msg(json.msg,{icon:1});
		        setTimeout(function(){ 
			    	location.href=MBIS.U('admin/grade/index','major_id='+$('#major_id').val());
		        },1000);
		  }else{
		        MBIS.msg(json.msg,{icon:2});
		  }
	});
}

function toDel(id){
	var box = MBIS.confirm({content:"您确定要删除该数据吗?",yes:function(){
	           var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
	           	$.post(MBIS.U('admin/grade/del'),{id:id},function(data,textStatus){
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
function majorQuery_g(){
    var school_id = $('#school_id').val();
    if(school_id==''){
        MBIS.msg('请选择需要查询的院校',{icon:2});
    }
    grid.set('url',MBIS.U('admin/grade/pageQuery_s','school_id='+school_id));
}