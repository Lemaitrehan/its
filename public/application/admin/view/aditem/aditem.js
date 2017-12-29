var grid;
var combo;
function initGrid(){
	grid = $("#maingrid").ligerGrid({
		url:MBIS.U('admin/aditem/pageQuery'),
		pageSize:MBIS.pageSize,
		pageSizeOptions:MBIS.pageSizeOptions,
		height:'99%',
        width:'100%',
        minColToggle:9,
        rownumbers:true,
        columns: [
            { display: '杂费名称', name: 'name',isSort: false,},
            { display: '标准价格', name: 'price',isSort: false,},
            { display: '可优惠价格', name: 'offers_price',isSort: false},
	        { display: '杂项类型', name: 'teaching_type',isSort: false},
	        { display: '是否上架', name: 'is_shelves',isSort: false},
	        { display: '杂费简介', name: 'des',isSort: false},
	        { display: '杂费详情', name: 'details',isSort: false},
	        { display: '操作', name: 'op',isSort: false,
	        	render: function (rowdata){
		            var h = "";
		            if(MBIS.GRANT.XZF_02)h += "<a href='javascript:toEdit("+rowdata["it_id"]+")'>修改</a> ";
		            if(MBIS.GRANT.XZF_03)h += "<a href='javascript:toDel("+rowdata["it_id"]+")'>删除</a> ";
		            //if(MBIS.GRANT.XZF_03)h += "<a href='javascript:toDetail("+rowdata["it_id"]+")'>完善资料</a> ";
		            return h;
	        	}
	        }
        ]
    });

}

function initCombo(){
}

function loadGrid(){
	grid.set('url',MBIS.U('admin/aditem/pageQuery','key='+$('#key').val()));
}
function refresh(){
	if($('#key').val() !== ''){
		$('#key').val('');
	}
  	grid.set('url',MBIS.U('admin/aditem/pageQuery'));
}
function expAditem(){
	var key = $('#key').val();
	var link = MBIS.U('admin/aditem/expAditem','key='+key);
  	$('#export').attr('href',link);
}
function toEdit(id){
	location.href=MBIS.U('admin/aditem/toEdit','id='+id);
}

function toDetail(id){
	location.href=MBIS.U('admin/aditem/toDetail','id='+id);
}

function toEdits(id){
    var params = MBIS.getParams('.ipt');
    params.id = id;
    var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
	$.post(MBIS.U('admin/aditem/'+((id>0)?"edit":"add")),params,function(data,textStatus){
		  layer.close(loading);
		  var json = MBIS.toAdminJson(data);
		  if(json.status=='1'){
		    	MBIS.msg(json.msg,{icon:1});
		        setTimeout(function(){ 
			    	location.href=MBIS.U('admin/aditem/index');
		        },1000);
		  }else{
		        MBIS.msg(json.msg,{icon:2});
		  }
	});
}

function toDel(id){
	var box = MBIS.confirm({content:"您确定要删除该数据吗?",yes:function(){
   	var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
   	$.post(MBIS.U('admin/aditem/del'),{id:id},function(data,textStatus){
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