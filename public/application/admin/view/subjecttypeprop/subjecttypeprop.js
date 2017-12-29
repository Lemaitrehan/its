var grid;
var combo;
function initGrid(type_id){
	grid = $("#maingrid").ligerGrid({
		url:MBIS.U('admin/subjecttypeprop/pageQuery','type_id='+type_id),
		pageSize:MBIS.pageSize,
		pageSizeOptions:MBIS.pageSizeOptions,
		height:'99%',
        width:'100%',
        minColToggle:6,
        rownumbers:true,
        columns: [
            { display: '属性名称', name: 'name',isSort: false},
            { display: '录入方式', name: 'prop_input_type',isSort: false},
	        { display: '操作', name: 'op',width: 100,isSort: false,
	        	render: function (rowdata){
		            var h = "";
		            if(MBIS.GRANT.KMLXSX_002)h += "<a href='javascript:toEdit("+rowdata["prop_id"]+")'>修改</a> ";
		            if(MBIS.GRANT.KMLXSX_003)h += "<a href='javascript:toDel("+rowdata["prop_id"]+")'>删除</a> "; 
		            return h;
	        	}
	        }
        ]
    });

}

function initCombo(){
}

function loadGrid(type_id){
	grid.set('url',MBIS.U('admin/subjecttypeprop/pageQuery','key='+$('#key').val())+'&type_id='+type_id);
}

function toEdit(id,type_id){
	location.href=MBIS.U('admin/subjecttypeprop/toEdit','id='+id+'&type_id='+type_id);
}

function toEdits(id){
    var params = MBIS.getParams('.ipt');
    //var params = $('#infoForm').serialize();
    params.id = id;
    var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
	$.post(MBIS.U('admin/subjecttypeprop/'+((id>0)?"edit":"add")),params,function(data,textStatus){
		  layer.close(loading);
		  var json = MBIS.toAdminJson(data);
		  if(json.status=='1'){
		    	MBIS.msg(json.msg,{icon:1});
		        setTimeout(function(){ 
			    	location.href=MBIS.U('admin/subjecttypeprop/index','type_id='+$('#type_id').val());
		        },1000);
		  }else{
		        MBIS.msg(json.msg,{icon:2});
		  }
	});
}

function toDel(id){
	var box = MBIS.confirm({content:"您确定要删除该数据吗?",yes:function(){
	           var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
	           	$.post(MBIS.U('admin/subjecttypeprop/del'),{id:id},function(data,textStatus){
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

//改变可选值
function changePropvalue(val)
{
    if(val == 1)
    {
        $('#prop_value').removeAttr('disabled');
    }
    else
    {
        $('#prop_value').attr('disabled',true);   
    }
}