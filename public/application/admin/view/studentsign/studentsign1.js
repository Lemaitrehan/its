var grid;
var url = 'admin/Studentsign/indexskill';
$(function(){
})
function initGrid(type){
	grid = $("#maingrid").ligerGrid({
		url:MBIS.U(url),
		pageSize:MBIS.pageSize,
		pageSizeOptions:MBIS.pageSizeOptions,
		height:'99%',
        width:'100%',
        minColToggle:6,
        rownumbers:true,
        columns: [
            { display: '名称', name: 'trueName', isSort: false},
            { display: '院校', name: 'school_name', isSort: false},
	        { display: '专业', name: 'major_name', isSort: false},
	        { display: '课程', name: 'course_name', isSort: false},
	        { display: '准考证号', name: 'exam_no', isSort: false},
	        { display: '应收学费', name: 'receivable_fee', isSort: false},
	        { display: '实收学费', name: 'real_fee', isSort: false},
	        { display: '欠费', name: 'arrearage_fee', isSort: false},
	        { display: '报名时间', name: 'entry_time', isSort: false},
	        { display: '备注', name: 'remark', isSort: false}
        ]
    });
	
}
//刷新
function refresh(){
  $('.query').each(function(){
    if($(this).val() !== ''){
      $(this).val('');
    }
  });
  grid.set('url',MBIS.U(url));
}
//查询
function　loadGrid(){
	var query = MBIS.getParams('.query');
	  grid.set('url',MBIS.U(url,query));
}
function expUsersU(){
  var query = MBIS.getParams('.query');
  var link = MBIS.U('admin/users/expUsersU',query);
  $('#export').attr('href',link);
}





