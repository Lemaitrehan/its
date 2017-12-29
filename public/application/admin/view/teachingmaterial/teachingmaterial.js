var grid;
var combo;
var type1;
function initGrid(type){
	type1 = type;
	grid = $("#maingrid").ligerGrid({
		url:MBIS.U('admin/teachingmaterial/pageQuery'),
		pageSize:MBIS.pageSize,
		pageSizeOptions:MBIS.pageSizeOptions,
		height:'99%',
        width:'100%',
        minColToggle:9,
        rownumbers:true,
        columns: [
            { display: '教材名称', name: 'name',isSort: false,},
            { display: '教材编号', name: 'material_no',isSort: false,},
            { display: '教材数量', name: 'quantity',isSort: false,},
            { display: '单位', name: 'units',width:50,isSort: false,},
            { display: '标准价格', name: 'price',isSort: false,},
            { display: '可优惠价格', name: 'offers_price',isSort: false},
	        { display: '教材类型', name: 'material_type',isSort: false},
	        { display: '教材状态', name: 'status',isSort: false},
	        { display: '是否上架', name: 'is_shelves',isSort: false},
	        { display: '教材简介', name: 'intro',isSort: false},
	        { display: '教材详情', name: 'details',isSort: false},
	        { display: '操作', name: 'op',isSort: false,
	        	render: function (rowdata){
		            var h = "";
		            if( ( type1 ==1 && MBIS.GRANT.CKJC_02 ) ||  ( type1 ==2 && MBIS.GRANT.JCCK_02 ) )
		            	h += "<a href='javascript:toEdit("+rowdata["tm_id"]+")'>修改</a> ";
		            if( ( type1 ==1 && MBIS.GRANT.CKJC_03 ) ||  ( type1 ==2 && MBIS.GRANT.JCCK_03 ) )
		            	h += "<a href='javascript:toDel("+rowdata["tm_id"]+")'>删除</a> ";
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
	grid.set('url',MBIS.U('admin/teachingmaterial/pageQuery','key='+$('#key').val()));
}

function tmQuery(){
	var query = MBIS.getParams('.query');
    grid.set('url',MBIS.U('admin/teachingmaterial/pageQuery',query));
}
function refresh(){
	$('.query').each(function(){
	    if($(this).val() !== ''){
	      $(this).val('');
	    }
  	});
  	grid.set('url',MBIS.U('admin/teachingmaterial/pageQuery'));
}
function toEdit(id,type){
	location.href=MBIS.U('admin/teachingmaterial/toEdit','id='+id+'&type='+type);
}

function toDetail(id){
	location.href=MBIS.U('admin/teachingmaterial/toDetail','id='+id);
}

function toEdits(id,type){
	if(type == '1'){
		var indexurl = 'admin/teachingmaterial/indexEdu';
	}else{
		var indexurl = 'admin/teachingmaterial/indexSkill';
	}
    var params = MBIS.getParams('.ipt');
    //var params = $('#teachingmaterialForm').serialize();
    params.id = id;
    var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
	$.post(MBIS.U('admin/teachingmaterial/'+((id>0)?"edit":"add")),params,function(data,textStatus){
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
   	$.post(MBIS.U('admin/teachingmaterial/del'),{id:id},function(data,textStatus){
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