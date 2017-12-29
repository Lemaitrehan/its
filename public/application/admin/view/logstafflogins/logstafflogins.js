var grid;
$(function(){
	$("#startDate").ligerDateEditor();
	$("#endDate").ligerDateEditor();
	grid = $("#maingrid").ligerGrid({
		url:MBIS.U('admin/logstafflogins/pageQuery'),
		pageSize:MBIS.pageSize,
		pageSizeOptions:MBIS.pageSizeOptions,
		height:'99%',
        width:'100%',
        minColToggle:6,
        rownumbers:true,
        columns: [
	        { display: '职员', name: 'staffName'},
	        { display: '登录时间', name: 'loginTime'},
	        { display: '登录IP', name: 'loginIp'}
        ]
    });
})
function loadGrid(){
	grid.set('url',MBIS.U('admin/logstafflogins/pageQuery','startDate='+$('#startDate').val()+"&endDate="+$('#endDate').val()))
}