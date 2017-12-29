var grid;var grid;
$(function(){
  $("#brithday").ligerDateEditor();
  $("#basic_brithday").ligerDateEditor();
  $("#access_time").ligerDateEditor();
  $("#edu_entry_time").ligerDateEditor();
  $("#skill_entry_time").ligerDateEditor();
  $("#start").ligerDateEditor();
  $("#end").ligerDateEditor();
})
function initGrid(){
	grid = $("#maingrid").ligerGrid({
		url:MBIS.U('admin/production/pageQuery'),
		pageSize:MBIS.pageSize,
		pageSizeOptions:MBIS.pageSizeOptions,
		height:'99%',
                width:'100%',
                minColToggle:6,
                rownumbers:true,
                columns: [
	        { display: '学员编号', name: 'student_no', isSort: false},
                { display: '身份证号', name: 'idcard_no', isSort: false},
                { display: '姓名', name: 'trueName', isSort: false},
                { display: '学习状态', name: 'study_status', isSort: false},
                { display: '联系电话', name: 'userPhone', isSort: false},
                { display: 'QQ', name: 'userQQ', isSort: false},
                { display: '邮箱', name: 'userEmail', isSort: false},
                { display: '提交作品数', name: 'work_num', isSort: false},
                { display: '报考费', name: 'student_no', isSort: false},
	        { display: '操作', name: 'op',isSort: false,render: function (rowdata, rowindex, value){
	            var h = "";
		            if(MBIS.GRANT.ZFGL_02)h += "<a href='"+MBIS.U('admin/production/toEdit','id='+rowdata['id'])+"'>编辑</a> ";
		            if(MBIS.GRANT.ZFGL_03)h += "<a href='"+MBIS.U('admin/production/tocheck','id='+rowdata['id'])+ ")'>查看作品</a> "; 
	            return h;
	        }}
        ]
    });
}
function toDel(id){
	var box = MBIS.confirm({content:"您确定要删除该记录吗?",yes:function(){
	           var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
	           	$.post(MBIS.U('admin/Users/delu'),{id:id},function(data,textStatus){
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

function userQueryU(){
	var query = MBIS.getParams('.query');
  grid.set('url',MBIS.U('admin/Users/pageQueryU',query));
}
function refresh(){
  $('.query').each(function(){
    if($(this).val() !== ''){
      $(this).val('');
    }
  });
  grid.set('url',MBIS.U('admin/users/pageQueryU'));
}

function expUsersU(){
  var query = MBIS.getParams('.query');
  var link = MBIS.U('admin/users/expUsersU',query);
  $('#export').attr('href',link);
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
                  msg:{required:"请输入名称"},
                  tip:"请输入名称",
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
            var userId = $('#id').val();
            var params = $('#userForm').serialize();
            var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
            $.post(MBIS.U('admin/Production/add'),params,function(data,textStatus){
              layer.close(loading);
              var json = MBIS.toAdminJson(data);
              if(json.status=='1'){
                  MBIS.msg(json.msg,{icon:1});
                  location.href=MBIS.U('Admin/Contract/index');
              }else{
                    MBIS.msg(json.msg,{icon:2});
              }
            });

      }

    });



//上传头像
  MBIS.upload({
      pick:'#adFilePicker',
      formData: {dir:'users'},
      accept: {extensions: 'gif,jpg,jpeg,bmp,png',mimeTypes: 'image/*'},
      callback:function(f){
        var json = MBIS.toAdminJson(f);
        if(json.status==1){
        $('#uploadMsg').empty().hide();
        $('#userPhoto').val(json.savePath+json.thumb);
        $('#preview').html('<img src="'+MBIS.conf.ROOT+'/'+json.savePath+json.thumb+'"  height="152" />');
        }else{
          MBIS.msg(json.msg,{icon:2});
        }
    },
    progress:function(rate){
        $('#uploadMsg').show().html('已上传'+rate+"%");
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
    var school_id = $('#search_school_id').val();
    $.post(MBIS.U('admin/users/checkSchool'),{school_id:school_id},function(data){
      var json = MBIS.toAdminJson(data);
      if(json.status == 1){
        $('#search_major_id').empty();
        $('#search_course_id').empty();
        $('#search_major_id').append("<option value=''>请选择</option>");
        $('#search_course_id').append("<option value=''>请选择</option>");
        $.each(json.data,function(key,value){
          $('#search_major_id').append("<option value="+value['major_id']+">"+value['name']+"</option>");
        });
      }else{
        MBIS.msg(json.msg,{icon:2});
        $('#search_major_id').empty(); 
        $('#search_course_id').empty();
        $('#search_major_id').append("<option value=''>请选择</option>");
        $('#search_course_id').append("<option value=''>请选择</option>"); 
      }
    });   
}
function checkMajor(){
    var major_id = $('#search_major_id').val();
    $.post(MBIS.U('admin/users/checkMajor'),{major_id:major_id},function(data){
      var json = MBIS.toAdminJson(data);
      if(json.status == 1){
        $('#search_course_id').empty();
        $('#search_course_id').append("<option value=''>请选择</option>");
        $.each(json.data,function(key,value){
          $('#search_course_id').append("<option value="+value['course_id']+">"+value['name']+"</option>");
        });
      }else{
        MBIS.msg(json.msg,{icon:2});
        $('#search_course_id').empty();
        $('#search_course_id').append("<option value=''>请选择</option>")(); 
      }
    });   
}
function toAdd(){
    location.href=MBIS.U('admin/production/toAdd');
}
