var grid;
function initGrid(){
	grid = $("#maingrid").ligerGrid({
		url:MBIS.U('admin/settlements/pageShopQuery'),
		pageSize:MBIS.pageSize,
		pageSizeOptions:MBIS.pageSizeOptions,
		height:'99%',
        width:'100%',
        minColToggle:6,
        rownumbers:true,
        columns: [
            { display: '<input type="checkbox" onclick="MBIS.checkChks(this,\'.chk_1\')"/>', width:30,name: 'orderNo',isSort: false,render: function (rowdata, rowindex, value){
            	return '<input type="checkbox" id="s_'+rowdata['shopId']+'" class="chk_1" value="'+rowdata['shopId']+'" dataval="'+rowdata['shopName']+'"/>';
            }},
            { display: '店铺编号', name: 'shopSn',isSort: false},
	        { display: '店铺名称', name: 'shopName',isSort: false},
	        { display: '店主姓名', name: 'shopkeeper',isSort: false},
	        { display: '店主联系电话', name: 'telephone',isSort: false},
	        { display: '待结算订单数', name: 'noSettledOrderNum',isSort: false},
	        { display: '待结算佣金', name: 'noSettledOrderFee',isSort: false,render: function (rowdata, rowindex, value){
	        	return '¥'+value;
	        }},
	        { display: '操作', name: 'op',isSort: false,render: function (rowdata, rowindex, value){
	            var h = "<a href='javascript:toView(" + rowdata['shopId'] + ")'>订单列表</a>&nbsp;&nbsp;";
	            return h;
	        }}
        ]
    });
}
function toView(id){
   location.href=MBIS.U('admin/settlements/toOrders','id='+id);
}
function initOrderGrid(id){
	grid = $("#maingrid").ligerGrid({
		url:MBIS.U('admin/settlements/pageShopOrderQuery','id='+id),
		pageSize:MBIS.pageSize,
		pageSizeOptions:MBIS.pageSizeOptions,
		height:'99%',
        width:'100%',
        minColToggle:6,
        rownumbers:true,
        columns: [
            { display: '订单号', name: 'orderNo',isSort: false},
	        { display: '支付方式', name: 'payTypeName',isSort: false},
	        { display: '商品金额', name: 'goodsMoney',isSort: false},
	        { display: '运费', name: 'deliverMoney',isSort: false,render: function (rowdata, rowindex, value){
	        	return '¥'+value;
	        }},
	        { display: '订单总金额', name: 'totalMoney',isSort: false,render: function (rowdata, rowindex, value){
	        	return '¥'+value;
	        }},
	        { display: '实付金额', name: 'realTotalMoney',isSort: false,render: function (rowdata, rowindex, value){
	        	return '¥'+value;
	        }},
	        { display: '佣金', name: 'commissionFee',isSort: false,render: function (rowdata, rowindex, value){
	        	return '¥'+value;
	        }},
	        { display: '下单时间', name: 'createTime',isSort: false}
        ]
    });
}
function loadShopGrid(){
	var areaIdPath = MBIS.ITGetAllAreaVals('areaId1','j-areas').join('_');
	grid.set('url',MBIS.U('admin/settlements/pageShopQuery','shopName='+$('#shopName').val()+"&areaIdPath="+areaIdPath));
}
function loadOrderGrid(){
	var id = $('#id').val();
    grid.set('url',MBIS.U('admin/settlements/pageShopOrderQuery','orderNo='+$('#orderNo').val()+"&payType="+$('#payType').val()+'&id='+id));
}
var generateNo = 0;
var shops = [];
function generateSettle(){
	var shopId = shops[generateNo];
	var shopName = $('#s_'+shopId).attr('dataval');

	var load = MBIS.msg('正在生成【'+shopName+'】结算单，请稍后...', {icon: 16,time:60000});
    $.post(MBIS.U('admin/settlements/generateSettleByShop'),{id:shopId},function(data,textStatus){
		layer.close(load);
		var json = MBIS.toAdminJson(data);
			if(json.status==1){
				if(generateNo<(shops.length-1)){
					generateNo++;
		            generateSettle();
				}else{
                    MBIS.msg(json.msg);
                    loadShopGrid();
				}
		}else{
			MBIS.msg(json.msg);
			loadShopGrid();
		}
	});
}
function generateSettleByShop(){
	var ids = MBIS.getChks('.chk_1');
	if(ids.length==0){
		MBIS.msg('请选择要结算的商家!',{icon:2});
		return;
	}
	shops = ids;
	MBIS.confirm({content:'您确定生成选中商家的结算单吗？',yes:function(){
        generateNo = 0;
	    generateSettle();
	}});
}