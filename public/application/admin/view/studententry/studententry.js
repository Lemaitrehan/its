var grid;
var url = 'admin/Studententry/indexEducation';
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
	        { display: '年级', name: 'grade_name', isSort: false},
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
  var url = 'admin/Studentsign/indexEducation';
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

function exportEdu(){
	window.location.href = MBIS.U('admin/Studententry/expUsersEdu');
}


//发送通知
function sendSms(){
	  var sel_data = grid.getSelectedRows();
	 
	    if(sel_data.length==0)  {MBIS.msg('请选择数据',{icon:2});return false};
	    var ids = '';
	    var is_xl_jn = 1;
	    for(i in sel_data)
	    {   
	    	is_xl_jn =  sel_data[i].skill_id?2:1;
	    	sel_data[i].userId;//学员id 
	    	sel_data[i].trueName;//学员id 
	    	sel_data[i].student_no;//学员id 
	        ids +=  sel_data[i].userId+'--'+sel_data[i].trueName+'('+sel_data[i].student_no+'),';   
	    }
	    if (ids.length > 0) {
	    	ids = ids.substr(0, ids.length - 1);
	    }
		window.location.href = MBIS.U('admin/Studentnoticelog/addEducationList','ids='+ids);
	
}

//批量绑定年级操作
function batch_set_grade()
{
    var sel_data = grid.getSelectedRows();
    if(sel_data.length==0)  {MBIS.msg('请选择数据',{icon:2});return false};
    var ids = '';
    for(i in sel_data)
    {
        var id = sel_data[i].skill_id || 0;
        id = id || sel_data[i].edu_id;
        ids += id+',';   
    }
    if (ids.length > 0) {
    	ids = ids.substr(0, ids.length - 1);
    }
     var loading2 = MBIS.msg('正在请求数据，请稍后...', {icon: 16,time:60000});
    var rs_html = $.get(MBIS.U('admin/studententry/batch_set_grade','ids='+ids),function(rs){
        $('#grade_info').html(rs);
        layer.close(loading2);
         var title ="分配年级";
	var box = MBIS.open({title:title,type:1,content:$('#grade_info').html(),area: ['450px', '320px'],btn:['确定','取消'],yes:function(){
        //alert($('#gradeSetForm'));
        var params = MBIS.getParams('.ipt');
        params.ids = ids;
         var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
        $.post(MBIS.U('admin/studententry/batch_set_grade'),params,function(data,textStatus){
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
    }); 
}





