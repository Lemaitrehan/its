var grid;
var combo;
function initGrid(type_id){
	grid = $("#maingrid").ligerGrid({
		url:MBIS.U('admin/school/pageQueryEdu','type_id='+type_id),
		pageSize:MBIS.pageSize,
		pageSizeOptions:MBIS.pageSizeOptions,
		height:'99%',
        width:'100%',
        minColToggle:6,
        rownumbers:true,
        columns: [
	        { display: '院校编号', name: 'school_no',isSort: false},
            { display: '院校名称', name: 'name',isSort: false},
            { display: '考试类型', name: 'exam_type',isSort: false},
            { display: '是否上架', name: 'is_sell',isSort: false},
	        { display: '操作', name: 'op',width: 100,isSort: false,
	        	render: function (rowdata){
		            var h = "";
		            if(type_id == 1){
		            	if(MBIS.GRANT.XLXY_02)h += "<a href='javascript:toEdit("+type_id+","+rowdata["school_id"]+")'>修改</a> ";
		            	if(MBIS.GRANT.XLXY_03)h += "<a href='javascript:toDel("+type_id+","+rowdata["school_id"]+")'>删除</a> ";
		            }
		            return h;
	        	}
	        }
        ]
    });

}

function initCombo(){
}
function loadGrid(type_id){
	grid.set('url',MBIS.U('admin/school/pageQueryEdu','key='+$('#key').val()+'&type_id='+type_id));
}
function refresh(type_id){
	if($('#key').val() !== ''){
		$('#key').val('');
	}
    grid.set('url',MBIS.U('admin/school/pageQueryEdu','type_id='+type_id));
}
function toEdit(type_id,id){
	location.href=MBIS.U('admin/school/toEditEdu','type_id='+type_id+'&id='+id);
}
function toEdits(id){
    //var params = MBIS.getParams('.ipt');
    var params = $('#schoolForm').serialize();
    params.id = id;
    var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
	$.post(MBIS.U('admin/school/'+((id>0)?"editEdu":"addEdu")),params,function(data,textStatus){
		  layer.close(loading);
		  var json = MBIS.toAdminJson(data);
		  if(json.status=='1'){
		    	MBIS.msg(json.msg,{icon:1});
		        setTimeout(function(){ 
			    	location.href=MBIS.U('admin/school/indexEdu','type_id='+$('#type_id').val());
		        },1000);
		  }else{
		        MBIS.msg(json.msg,{icon:2});
		  }
	});
}
function toDel(type_id,id){
	var box = MBIS.confirm({content:"您确定要删除该数据吗?",yes:function(){
	           	var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
	           	$.post(MBIS.U('admin/school/delEdu'),{id:id},function(data,textStatus){
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


function upSell(id,type_id){
    var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
    $.post(MBIS.U('admin/school/upSell'),{id:id,type_id:type_id},function(data,textStatus){
        layer.close(loading);
        var json = MBIS.toAdminJson(data);
        if(json.status== '1'){
            MBIS.msg(json.msg,{icon:1});
            grid.reload();
        }else{
            MBIS.msg(json.msg,{icon:2});
        }
    });

}