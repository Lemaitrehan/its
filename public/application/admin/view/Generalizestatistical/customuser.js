var grid;
function initGrid(type){
	var query = MBIS.getParams('.query');
	console.log(query)
	query.type = type;
	grid = $("#maingrid").ligerGrid({
		url:MBIS.U('admin/Generalizestatistical/index',query),
		pageSize:MBIS.pageSize,
		pageSizeOptions:MBIS.pageSizeOptions,
		height:'99%',
        width:'100%',
        minColToggle:6,
        rownumbers:true,
        columns: [
          { display: '提交时间', name: 'create_time', isSort: false},
          { display: '文件名称', name: 'file',width:160,isSort: false},
	      { display: '文件数据开始时间', name: 'start_time', isSort: false},
	      { display: '文件数据结束时间', name: 'stop_time', isSort: false},
	      { display: '操作人', name: 'staffName', isSort: false},
      /*    { display: '模板类型', name: 'title', isSort: false},
          { display: '开始时间', name: 'start_time',width:160,isSort: false},
	      { display: '结束时间', name: 'stop_time', isSort: false},
	      { display: '最后操作人', name: 'staffName', isSort: false},
	      { display: '最后修改时间', name: 'create_time', isSort: false},*/
	      { display: '操作', name: 'op',width:100,isSort: false,render: function (rowdata, rowindex, value){
              var h = "";
              //if(MBIS.GRANT.XYCK_04)h += '<a href="InfoDownload?path=D:/zp/v1.0/public/upload/generalize/exel/&amp;file=111.csv">立即下载</a>';
              if(MBIS.GRANT.XYCK_04)h += '<a href="InfoDownload?path='+rowdata.path+'/&amp;file='+rowdata.file+'">立即下载</a>';

              return h;
            
	        }}
        ]
    });
}
$('.titleText').click(function(){
   $('.titleText').removeClass('btn-blue');
   $(this).addClass('btn-blue');
   var type = $(this).val();
   var url = MBIS.U('admin/Generalizestatistical/addData'+type);
   $('#type').val(type);
   $('form').attr('action',url);
   initGrid($(this).val())
})

function refresh(){
  $('.query').each(function(){
    if($(this).val() !== ''){
      $(this).val('');
    }
  });
  grid.set('url',MBIS.U('admin/Generalize/index'));
}
