var grid;
var combo;
function initGrid(){
	grid = $("#maingrid").ligerGrid({
		url:MBIS.U('admin/grade/pageQueryEdu'),
		pageSize:MBIS.pageSize,
		pageSizeOptions:MBIS.pageSizeOptions,
		height:'99%',
        width:'100%',
        minColToggle:6,
        rownumbers:true,
        columns: [
            { display: '年级名称', name: 'name',isSort: false},
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

function loadGrid(){
	grid.set('url',MBIS.U('admin/grade/pageQueryEdu','key='+$('#key').val()));
}
function refresh(){
  $('.query').each(function(){
    if($(this).val() !== ''){
      $(this).val('');
    }
  });
  grid.set('url',MBIS.U('admin/grade/pageQueryEdu'));
}
function toEdit(id){
	location.href=MBIS.U('admin/grade/toEditEdu','id='+id);
}
function toEdits(id){
    //var params = MBIS.getParams('.ipt');
    var params = $('#infoForm').serialize();
    params.id = id;
    var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
	$.post(MBIS.U('admin/grade/'+((id>0)?"editEdu":"addEdu")),params,function(data,textStatus){
		  layer.close(loading);
		  var json = MBIS.toAdminJson(data);
		  if(json.status=='1'){
		    	MBIS.msg(json.msg,{icon:1});
		        setTimeout(function(){
			    	location.href=MBIS.U('admin/grade/indexEdu');
		        },1000);
		  }else{
		        MBIS.msg(json.msg,{icon:2});
		  }
	});
}
function toDel(id){
	var box = MBIS.confirm({content:"您确定要删除该数据吗?",yes:function(){
	           var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
	           	$.post(MBIS.U('admin/grade/delEdu'),{id:id},function(data,textStatus){
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
function toBack(){
    location.href=MBIS.U('admin/major/index','type_id=1');
}