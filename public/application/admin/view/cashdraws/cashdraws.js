var grid;
function initGrid(){
	grid = $("#maingrid").ligerGrid({
		url:MBIS.U('admin/cashdraws/pageQuery'),
		pageSize:MBIS.pageSize,
		pageSizeOptions:MBIS.pageSizeOptions,
		height:'99%',
        width:'100%',
        minColToggle:6,
        rownumbers:true,
        columns: [
            { display: '提现单号', name: 'cashNo',Sort: false},
	        { display: '提现银行', name: 'accTargetName',isSort: false},
	        { display: '开卡地区', name: 'accAreaName',Sort: false},
	        { display: '银行卡号', name: 'accNo',Sort: false},
	        { display: '持卡人', name: 'accUser',Sort: false},
	        { display: '提现金额', name: 'money',Sort: false,render: function (rowdata, rowindex, value){
	            return '¥'+value;
	        }},
	        { display: '提现时间', name: 'createTime',Sort: false},
	        { display: '状态', name: 'cashSatus',Sort: false,render: function (rowdata, rowindex, value){
	            return (rowdata['cashSatus']==1)?"已通过":"待处理";
	        }},
	        { display: '操作', name: 'op',isSort: false,render: function (rowdata, rowindex, value){
	            var h = "";
	            if(rowdata['cashSatus']==0 && MBIS.GRANT.TXSQ_04)h += "<a href='javascript:toEdit(" + rowdata['cashId'] + ")'>处理</a> ";
	            return h;
	        }}
        ]
    });
}
function toEdit(id){
	location.href=MBIS.U('admin/cashdraws/toHandle','id='+id);
}
function loadGrid(){
	grid.set('url',MBIS.U('admin/cashdraws/pageQuery','cashNo='+$('#cashNo').val()+"&cashSatus="+$('#cashSatus').val()));
}

function save(){
	if(MBIS.confirm({content:'您确定通过该提现申请吗？',yes:function(){
        var params = MBIS.getParams('.ipt');
		var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
	    $.post(MBIS.U('admin/cashdraws/handle'),params,function(data,textStatus){
	    	layer.close(loading);
	    	var json = MBIS.toAdminJson(data);
	    	if(json.status=='1'){
	    		MBIS.msg("操作成功",{icon:1});
	    		location.href=MBIS.U('admin/cashdraws/index');
	    	}else{
	    		MBIS.msg(json.msg,{icon:2});
	    	}
	    });
	}}));
}