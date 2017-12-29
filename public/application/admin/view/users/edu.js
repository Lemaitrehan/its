var grid;
$(function(){
  $("#entry_time").ligerDateEditor();
  $("#start").ligerDateEditor();
  $("#end").ligerDateEditor();
})
function initGrid(){
	grid = $("#maingrid").ligerGrid({
		url:MBIS.U('admin/Users/pageQueryE','userId='+userId),
		pageSize:MBIS.pageSize,
		pageSizeOptions:MBIS.pageSizeOptions,
		height:'99%',
        width:'100%',
        minColToggle:6,
        rownumbers:true,
        columns: [
	        { display: '会员ID', name: 'userId', isSort: false},
	        { display: '姓名', name: 'trueName', isSort: false},
          { display: '院校', name: 'school_id', isSort: false},
          //{ display: '考试类型', name: 'userPhone', isSort: false},
          //{ display: '层次', name: 'userPhone', isSort: false},
          { display: '专业', name: 'major_id', isSort: false},
          { display: '课程', name: 'course_id', isSort: false},
          { display: '年级', name: 'grade_id', isSort: false},
          { display: '准考证号', name: 'exam_no', isSort: false},
          { display: '应收学费', name: 'receivable_fee', isSort: false},
          { display: '实收费用', name: 'real_fee', isSort: false},
          { display: '备注', name: 'remark', isSort: false},
	        { display: '操作', name: 'op',isSort: false,render: function (rowdata, rowindex, value){
	            var h = "";
	            if(MBIS.GRANT.HYGL_02)h += "<a href='"+MBIS.U('admin/Users/toEdu','id='+rowdata['edu_id'])+"'>修改</a> ";
	            if(MBIS.GRANT.HYGL_03)h += "<a href='javascript:toDel(" + rowdata['edu_id'] + ")'>删除</a> "; 
	            return h;
	        }}
        ]
    });
	
	
	
}
function toDel(id){
	var box = MBIS.confirm({content:"您确定要删除该记录吗?",yes:function(){
	           var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
	           	$.post(MBIS.U('admin/Users/deledu'),{id:id},function(data,textStatus){
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

function userQuery(){
				var query = MBIS.getParams('.query');
			    grid.set('url',MBIS.U('admin/Users/pageQueryE',query));
			}



function editInit(){
	 /* 表单验证 */
    $('#userForm').validator({
            dataFilter: function(data) {
                if (data.ok === '该登录账号可用' ) return "";
                else return "已被注册";
            },
            rules: {
                loginName: function(element) {
                    return /\w{5,}/.test(element.value) || '账号应为5-16字母、数字或下划线';
                },
                myRemote: function(element){
                    return $.post(MBIS.U('admin/users/checkLoginKey'),{'loginName':element.value,'userId':$('#userId').val()},function(data,textStatus){});
                }
            },
            fields: {
                loginName: {
                  rule:"required;loginName;myRemote",
                  msg:{required:"请输入用户名"},
                  tip:"请输入用户名",
                  ok:"",
                },
                trueName: {
                  rule:"required;",
                  msg:{required:"请输入真实姓名"},
                  tip:"请输入真实姓名",
                  ok:"",
                },
                nickName: {
                  rule:"required;",
                  msg:{required:"请输入昵称"},
                  tip:"请输入昵称",
                  ok:"",
                },
                userPhone: {
                  rule:"required;mobile;myRemote",
                  msg:{required:"请输入手机号"},
                  tip:"请输入手机号",
                  ok:"",
                },
                userEmail: {
                  rule:"required;email;myRemote",
                  msg:{required:"请输入邮箱"},
                  tip:"请输入邮箱",
                  ok:"",
                },
                userQQ: {
                  rule:"integer[+]",
                  msg:{integer:"QQ只能是数字"},
                  tip:"QQ只能是数字",
                  ok:"",
                },
                
            },

          valid: function(form){
            var userId = $('#userId').val();
            var params = $('#userForm').serialize();
            var id = $('#edu_id').val();
            var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
            $.post(MBIS.U('admin/Users/'+((id>0)?"editedu":"addedu")),params,function(data,textStatus){
              layer.close(loading);
              var json = MBIS.toAdminJson(data);
              if(json.status=='1'){
                  MBIS.msg(json.msg,{icon:1});
                  location.href=MBIS.U('Admin/Users/toEditu','id='+userId);
              }else{
                    MBIS.msg(json.msg,{icon:2});
              }
            }); 
      }

    });
}

//切换选项卡
function changeTab(id1,id2)
{
    $('#'+id1).find('a').click(function(){
        var thiso = $(this);
        var index = $('#'+id1).find('a').index(thiso);
        thiso.addClass('hover').siblings().removeClass('hover');
        $('#'+id2).find('table').eq(index).show().siblings().hide();  
    });
}
function checkSchool(){
    var school_id = $('#edu_school_id').val();
    $.post(MBIS.U('admin/users/checkSchool'),{school_id:school_id},function(data){
      var json = MBIS.toAdminJson(data);
      if(json.status == 1){
        $('#edu_major_id').empty();
        $('#edu_course_id').empty();
        $('#edu_major_id').append("<option value=''>请选择</option>");
        $('#edu_course_id').append("<option value=''>请选择</option>");
        $.each(json.data,function(key,value){
          $('#edu_major_id').append("<option value="+value['major_id']+">"+value['name']+"</option>");
        });
      }else{
        MBIS.msg(json.msg,{icon:2});
        $('#edu_major_id').empty(); 
        $('#edu_course_id').empty();
        $('#edu_major_id').append("<option value=''>请选择</option>");
        $('#edu_course_id').append("<option value=''>请选择</option>"); 
      }
    });   
}
function checkMajor(){
    var major_id = $('#edu_major_id').val();
    $.post(MBIS.U('admin/users/checkMajor'),{major_id:major_id},function(data){
      var json = MBIS.toAdminJson(data);
      if(json.status == 1){
        $('#edu_course_id').empty();
        $('#edu_course_id').append("<option value=''>请选择</option>");
        $.each(json.data,function(key,value){
          $('#edu_course_id').append("<option value="+value['course_id']+">"+value['name']+"</option>");
        });
      }else{
        MBIS.msg(json.msg,{icon:2});
        $('#edu_course_id').empty();
        $('#edu_course_id').append("<option value=''>请选择</option>")(); 
      }
    });   
}

function dateSelect(){
    var start = $('#start').val();
    var end = $('#end').val();
    var userId = $('#hiddenId').val();
    $.post(MBIS.U('admin/users/dateSelect'),{start:start,end:end,userId:userId},function(data){
      var json = MBIS.toAdminJson(data);
      if(json.status == 1){
        $('#cktable tr:gt(1)').remove();
        $.each(json.data,function(k,v){
          var tr = $('<tr>');
          var td = $("<td>"+v['userId']+"</td><td>"+v['user_no']+"</td><td>"+v['trueName']+"</td><td>"+v['object_id']+"</td><td>"+v['ckwork_type']+"</td><td>"+v['xb_count']+"</td><td>"+v['remark']+"</td><td>"+v['createtime']+"</td>");
          tr.append(td);
          $('#cktable').append(tr);
        });
          
      }else{
        MBIS.msg(json.msg);
      }
    });

}