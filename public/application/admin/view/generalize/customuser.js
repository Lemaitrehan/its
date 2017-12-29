var grid;
function initGrid(){
	var query = MBIS.getParams('.query');
	grid = $("#maingrid").ligerGrid({
		url:MBIS.U('admin/Generalize/index',query),
		pageSize:MBIS.pageSize,
		pageSizeOptions:MBIS.pageSizeOptions,
		height:'99%',
        width:'100%',
        minColToggle:6,
        rownumbers:true,
        columns: [
          { display: '日期', name: 'time',width:160,isSort: false},
          { display: '推广计划', name: 'plan', isSort: false},
          { display: '账户', name: 'account',width:160,isSort: false},
	      { display: '展现', name: 'show_num', isSort: false},
	      { display: '点击', name: 'click_num', isSort: false},
          { display: '消费', name: 'consume', isSort: false},
          { display: '点击率', name: 'click_rate', isSort: false},
          { display: '平均点击价格', name: 'average_price', isSort: false},
	      { display: '网页转化', name: 'web_page_conversion', isSort: false},
          { display: '商桥转化', name: 'business_conversion', isSort: false},
	      { display: '电话转化', name: 'phone_conversion', isSort: false},
	      { display: '最后操作人', name: 'staffName', isSort: false},
	      { display: '最后修改时间', name: 'update_time', isSort: false},
	      /*{ display: '操作', name: 'op',width:100,isSort: false,render: function (rowdata, rowindex, value){
              var h = "";
              if(MBIS.GRANT.XYCK_04)h += "<a href='"+MBIS.U('admin/Customuser/CustomInfo','id='+rowdata['userId'])+"'>查看</a> ";
              if(MBIS.GRANT.XYCK_02)h += "<a href='"+MBIS.U('admin/Customuser/toEditCustom','id='+rowdata['userId'])+"'>修改</a> ";
              if(MBIS.GRANT.XYCK_03)h += "<a href='javascript:toDelCustom(" + rowdata['userId'] + ")'>删除</a> "; 
              return h;
            
	        }}*/
        ]
    });
}

function refresh(){
  $('.query').each(function(){
    if($(this).val() !== ''){
      $(this).val('');
    }
  });
  grid.set('url',MBIS.U('admin/Generalize/index'));
}
