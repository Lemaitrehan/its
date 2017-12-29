var grid;
var combo;
function initGrid(){
	grid = $("#maingrid").ligerGrid({
		url:MBIS.U('admin/subjecttype/pageQuery'),
		pageSize:MBIS.pageSize,
		pageSizeOptions:MBIS.pageSizeOptions,
		height:'99%',
        width:'100%',
        minColToggle:6,
        rownumbers:true,
        columns: [
            //{ display: 'ID', name: 'type_id',isSort: false},
            { display: '类型名称', name: 'name',isSort: false},
	        { display: '操作', name: 'op',width: 100,isSort: false,
	        	render: function (rowdata){
		            var h = "";
                    if(MBIS.GRANT.KMLXSX_000)h += "<a href='"+MBIS.U('admin/subjecttypeprop/index')+"?type_id="+rowdata["type_id"]+"'>属性列表</a> ";
		            if(MBIS.GRANT.KMLX_02)h += "<a href='javascript:toEdit("+rowdata["type_id"]+")'>修改</a> ";
		            //if(MBIS.GRANT.)h += "<a href='javascript:toDel("+rowdata["subject_id"]+")'>删除</a> "; 
		            return h;
	        	}
	        }
        ]
    });

}

function initCombo(){
}

function loadGrid(){
	grid.set('url',MBIS.U('admin/subjecttype/pageQuery','key='+$('#key').val()));
}

function toEdit(id){
	location.href=MBIS.U('admin/subjecttype/toEdit','id='+id);
}

function toEdits(id){
    var params = MBIS.getParams('.ipt');
    //var params = $('#infoForm').serialize();
    params.id = id;
    var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
	$.post(MBIS.U('admin/subjecttype/'+((id>0)?"edit":"add")),params,function(data,textStatus){
		  layer.close(loading);
		  var json = MBIS.toAdminJson(data);
		  if(json.status=='1'){
		    	MBIS.msg(json.msg,{icon:1});
		        setTimeout(function(){ 
			    	location.href=MBIS.U('admin/subjecttype/index');
		        },1000);
		  }else{
		        MBIS.msg(json.msg,{icon:2});
		  }
	});
}

function toDel(id){
	var box = MBIS.confirm({content:"您确定要删除该数据吗?",yes:function(){
	           var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
	           	$.post(MBIS.U('admin/subjecttype/del'),{id:id},function(data,textStatus){
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

//获取科目类型对应的属性列表
function get_subject_prop_data(type_id,subject_id)
{
    var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
    $.post(MBIS.U('admin/subject/get_subject_prop_data'),{type_id:type_id,subject_id:subject_id},function(data,textStatus){
          layer.close(loading);
          var json = MBIS.toAdminJson(data);
          if(json.status=='1'){
                $('#subject_type_prop').html(json.html);
                layer.close(box);
          }else{
                MBIS.msg(json.msg,{icon:2});
          }
    });
}