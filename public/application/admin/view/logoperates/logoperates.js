var grid;
$(function(){
	$("#startDate").ligerDateEditor();
	$("#endDate").ligerDateEditor();
	grid = $("#maingrid").ligerGrid({
		url:MBIS.U('admin/logoperates/pageQuery'),
		pageSize:MBIS.pageSize,
		pageSizeOptions:MBIS.pageSizeOptions,
		height:'99%',
        width:'100%',
        minColToggle:6,
        rownumbers:true,
        columns: [
	        { display: '职员', name: 'staffName',isSort: false},
	        { display: '操作功能', name: 'operateDesc',isSort: false,render: function (rowdata, rowindex, value){
	        	return rowdata['menuName']+"-"+rowdata['operateDesc'];
	        }},
	        { display: '访问路径', name: 'operateUrl',isSort: false},
	        { display: '操作IP', name: 'operateIP',isSort: false},
	        { display: '操作时间', name: 'operateTime',isSort: false},
	        { display: '传递参数', name: 'op',isSort: false,render: function (rowdata, rowindex, value){
	        	return '<a href="javascript:toView('+rowdata['operateId']+')">查看</a>';
	        }}
        ]
    });
})
function loadGrid(){
	grid.set('url',MBIS.U('admin/logoperates/pageQuery','startDate='+$('#startDate').val()+"&endDate="+$('#endDate').val()))
}
function toView(id){
	 var loading = MBIS.msg('正在获取数据，请稍后...', {icon: 16,time:60000});
	 $.post(MBIS.U('admin/logoperates/get'),{id:id},function(data,textStatus){
	       layer.close(loading);
	       var json = MBIS.toAdminJson(data);
	       if(json.status==1){
	    	   $('#content').html(json.data.content);
	    	   var box = MBIS.open({ title:"传递参数",type: 1,area: ['500px', '350px'],
		                content:$('#viewBox'),
		                btn:['关闭'],
		                yes: function(index, layero){
		                	layer.close(box);
		                }
	    	   });
	       }else{
	           MBIS.msg(json.msg,{icon:2});
	       }
	 });
}