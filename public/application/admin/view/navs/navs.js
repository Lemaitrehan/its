var grid;
function initGrid(){
	grid = $("#maingrid").ligerGrid({
		url:MBIS.U('admin/Navs/pageQuery'),
		pageSize:MBIS.pageSize,
		pageSizeOptions:MBIS.pageSizeOptions,
		height:'99%',
        width:'100%',
        minColToggle:6,
        rownumbers:true,
        columns: [
	        { display: '导航类型', name: 'navType', isSort: false,render :function(rowdata, rowindex, value){
	        	return (value==0)?'顶部':'底部';
	        }},
	        { display: '导航名称', name: 'navTitle', isSort: false},
	        { display: '导航链接', name: 'navUrl', isSort: false},
	        { display: '是否显示', name: 'isShow', isSort: false,render :function(rowdata, rowindex, value){
	        	return (value==1)?'<span style="cursor:pointer" onclick="isShowtoggle(\'isShow\','+rowdata['id']+', 0)">显示</span>':'<span style="cursor:pointer" onclick="isShowtoggle(\'isShow\','+rowdata['id']+', 1)">隐藏</span>';
	        }},
	        { display: '打开方式', name: 'isOpen', isSort: false,render :function(rowdata, rowindex, value){
	        	return (value==1)?'<span style="cursor:pointer" onclick="isShowtoggle(\'isOpen\','+rowdata['id']+', 0)">新窗口打开</span>':'<span style="cursor:pointer" onclick="isShowtoggle(\'isOpen\','+rowdata['id']+', 1)">页面跳转</span>';
	        }},
	        { display: '排序号', name: 'navSort', isSort: false},
	        { display: '操作', name: 'op',isSort: false,render: function (rowdata, rowindex, value){
	            var h = "";
	            if(MBIS.GRANT.DHGL_02)h += "<a href='"+MBIS.U('admin/Navs/toEdit','id='+rowdata['id'])+"'>修改</a> ";
	            if(MBIS.GRANT.DHGL_03)h += "<a href='javascript:toDel(" + rowdata['id'] + ")'>删除</a> "; 
	            return h;
	        }}
        ]
    });
}
function toDel(id){
	var box = MBIS.confirm({content:"您确定要删除该记录吗?",yes:function(){
	           var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
	           	$.post(MBIS.U('admin/Navs/del'),{id:id},function(data,textStatus){
	           			  layer.close(loading);
	           			  var json = MBIS.toAdminJson(data);
	           			  if(json.status=='1'){
	           			    	MBIS.msg("操作成功",{icon:1});
	           			    	layer.close(box);
	           		            grid.reload();
	           			  }else{
	           			    	MBIS.msg(json.msg,{icon:2});
	           			  }
	           		});
	            }});
}
function edit(id){
  //获取所有参数
  var params = MBIS.getParams('.ipt');
    params.id = id;
    var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
    $.post(MBIS.U('admin/Navs/'+((id==0)?"add":"edit")),params,function(data,textStatus){
      layer.close(loading);
      var json = MBIS.toAdminJson(data);
      if(json.status=='1'){
          MBIS.msg("操作成功",{icon:1});
          location.href=MBIS.U('Admin/Navs/index');
      }else{
            MBIS.msg(json.msg,{icon:2});
      }
    });
}
function isShowtoggle(field, id, val){
	if(!MBIS.GRANT.DHGL_02)return;
	$.post(MBIS.U('admin/Navs/editiIsShow'), {'field':field, 'id':id, 'val':val}, function(data, textStatus){
		var json = MBIS.toAdminJson(data);
	           			  if(json.status=='1'){
	           			    	MBIS.msg("操作成功",{icon:1});
	           		            grid.reload();
	           			  }else{
	           			    	MBIS.msg(json.msg,{icon:2});
	           			  }
	})
}
/*表单验证*/
$('#navForm').validator({
    fields:{
      navTitle:{rule:'required',msg:{required:"请输入导航名称"},tip:"请输入导航名称",ok:"",},
      navUrl: {rule:"required;",msg:{required:"请输入导航链接"},tip:"请输入导航链接",ok:"",},
    },
    valid:function(form){
      edit($('#id').val());
    }
  });

function changeFlink(obj){
     var flink = $(obj).val();
     if(flink==1)
       $("#articles").hide();
     else
       $("#articles").show();
     
}
function changeArticles(obj){
     var url = $(obj).val();
    
     $("#navUrl").val(url);
}
