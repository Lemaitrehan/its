var grid;
function initGrid(){
	var query = MBIS.getParams('.query');
	grid = $("#maingrid").ligerGrid({
		url:MBIS.U('admin/Customermessage/index',query),
		pageSize:MBIS.pageSize,
		pageSizeOptions:MBIS.pageSizeOptions,
		height:'99%',
        width:'100%',
        minColToggle:6,
        rownumbers:true,
        columns: [
          { display: '日期', name: 'time',width:160,isSort: false},
          { display: '主题', name: 'title', isSort: false},
          { display: '留言内容', name: 'content',width:160,isSort: false},
	      { display: '名称', name: 'name', isSort: false},
          { display: '邮箱', name: 'mail', isSort: false},
          { display: '客服名称', name: 'qq', isSort: false},
          { display: 'qq', name: 'start_time', isSort: false},
	      { display: '电话', name: 'phone', isSort: false},
          { display: '手机', name: 'mobile_phone', isSort: false},
	      { display: '自定义1', name: 'custom1', isSort: false},
	      { display: '自定义2', name: 'custom2', isSort: false},
	      { display: 'ip', name: 'ip', isSort: false},
	      { display: '位置', name: 'site', isSort: false},
	      { display: '来源', name: 'url', isSort: false},
	      { display: '搜索引擎', name: 'search_engine', isSort: false},
	      { display: '关键字', name: 'keyword', isSort: false},
	      { display: '留言页面', name: 'message_page', isSort: false},
	      { display: '留言给客服', name: 'message_to_customer', isSort: false},
	      { display: '留言给分组', name: 'message_to_grouping', isSort: false},
	      { display: '问题类别', name: 'problem_type', isSort: false},
	      { display: '留言原因', name: 'message_reason', isSort: false},
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
  grid.set('url',MBIS.U('admin/Customuser/pageQueryC'));
}
