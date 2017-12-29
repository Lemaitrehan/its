var grid;
$(function(){
  $("#brithday").ligerDateEditor();
  $("#basic_brithday").ligerDateEditor();
  $("#access_time").ligerDateEditor();
  $("#edu_entry_time").ligerDateEditor();
  $("#skill_entry_time").ligerDateEditor();
  $("#start").ligerDateEditor();
  $("#end").ligerDateEditor();
})
function initGrid(type_id){
	grid = $("#maingrid").ligerGrid({
		url:MBIS.U('admin/Users/pageQueryU','type_id='+type_id),
		pageSize:MBIS.pageSize,
		pageSizeOptions:MBIS.pageSizeOptions,
		height:'99%',
        width:'100%',
        minColToggle:6,
        rownumbers:true,
        columns: [
          { display: '身份证号码', name: 'idcard',width:160,isSort: false},
	        { display: '学员编号', name: 'student_no', isSort: false},
          { display: '姓名', name: 'trueName', isSort: false},
	        { display: '学习状态', name: 'study_status', isSort: false},
	        { display: '联系电话', name: 'userPhone', isSort: false},
          { display: 'QQ', name: 'userQQ', isSort: false},
          { display: '邮箱', name: 'userEmail', isSort: false},
	        { display: '紧急联系人', name: 'urgency_contact', isSort: false},
	        { display: '紧急联系电话', name: 'urgency_contact_mobile', isSort: false},
	        // { display: '账号状态', name: 'userStatus', isSort: false, render:function(rowdata, rowindex, value){
	        // 	return (value==1)?'启用':'停用';
	        // }},
	        { display: '操作', name: 'op',isSort: false,render: function (rowdata, rowindex, value){
            if(type_id == 2){
	            var h = "";
              if(MBIS.GRANT.XYRD_04)h += "<a href='"+MBIS.U('admin/Users/userInfo','userId='+rowdata['userId']+'&type_id='+type_id)+"'>查看</a> ";
	            if(MBIS.GRANT.XYRD_02)h += "<a href='"+MBIS.U('admin/Users/toEditu','id='+rowdata['userId']+'&type_id='+type_id)+"'>修改</a> ";
	            if(MBIS.GRANT.XYRD_03)h += "<a href='javascript:toDel(" + rowdata['userId'] + ")'>删除</a> "; 
	            return h;
            }else
            if(type_id ==1){
              var h = "";
              if(MBIS.GRANT.XYCK_04)h += "<a href='"+MBIS.U('admin/Users/userInfo','userId='+rowdata['userId']+'&type_id='+type_id)+"'>查看</a> ";
              if(MBIS.GRANT.XYCK_02)h += "<a href='"+MBIS.U('admin/Users/toEditu','id='+rowdata['userId']+'&type_id='+type_id)+"'>修改</a> ";
              if(MBIS.GRANT.XYCK_03)h += "<a href='javascript:toDel(" + rowdata['userId'] + ")'>删除</a> "; 
              return h;
            }
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
function refresh(type_id){
  $('.query').each(function(){
    if($(this).val() !== ''){
      $(this).val('');
    }
  });
  $('#type_id').val(type_id);
  grid.set('url',MBIS.U('admin/users/pageQueryU','type_id='+type_id));
}
function toAdd(type_id){
  location.href=MBIS.U('admin/users/toAdd','type_id='+type_id);
}
function editInit(type_id){
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
            if(type_id == '1'){
              var indexUrl = 'admin/Users/indexEdu';
            }else{
              var indexUrl = 'admin/Users/indexSkill';
            }
            var userId = $('#userId').val();
            //var params = MBIS.getParams('.ipt');
            var params = $('#userForm').serialize();
            var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
            $.post(MBIS.U('admin/Users/'+((userId>0)?"editu":"addu")),params,function(data,textStatus){
              layer.close(loading);
              var json = MBIS.toAdminJson(data);
              if(json.status=='1'){
                  MBIS.msg(json.msg,{icon:1});
                  location.href=MBIS.U(indexUrl);
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
        //将上传的图片路径赋给全局变量
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
    var school_id = $('#school_id').val();
    if(school_id == ''){
      $('#major_id').empty();
      $('#course_id').empty();
      $('#major_id').append("<option value=''>请选择</option>");
      $('#course_id').append("<option value=''>请选择</option>");return false;
    }
    $.post(MBIS.U('admin/users/checkSchool'),{school_id:school_id},function(data){
      var json = MBIS.toAdminJson(data);
      if(json.status == 1){
        $('#major_id').empty();
        $('#course_id').empty();
        $('#major_id').append("<option value=''>请选择</option>");
        $('#course_id').append("<option value=''>请选择</option>");
        $.each(json.data,function(key,value){
          $('#major_id').append("<option value="+value['major_id']+">"+value['name']+"</option>");
        });
      }else{
        MBIS.msg(json.msg,{icon:2});
        $('#major_id').empty(); 
        $('#course_id').empty();
        $('#major_id').append("<option value=''>请选择</option>");
        $('#course_id').append("<option value=''>请选择</option>"); 
      }
    });   
}
function checkMajor(){
    var major_id = $('#major_id').val();
    if(major_id == ''){
      $('#course_id').empty();
      $('#course_id').append("<option value=''>请选择</option>");return false;
    }
    $.post(MBIS.U('admin/users/checkMajor'),{major_id:major_id},function(data){
      var json = MBIS.toAdminJson(data);
      if(json.status == 1){
        $('#course_id').empty();
        $('#course_id').append("<option value=''>请选择</option>");
        $.each(json.data,function(key,value){
          $('#course_id').append("<option value="+value['course_id']+">"+value['name']+"</option>");
        });
      }else{
        MBIS.msg(json.msg,{icon:2});
        $('#course_id').empty();
        $('#course_id').append("<option value=''>请选择</option>"); 
      }
    });   
}