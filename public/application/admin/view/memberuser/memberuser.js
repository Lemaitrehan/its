var grid;
$(function(){
  $("#graduate_date").ligerDateEditor();
})
function initGrid(){
	grid = $("#maingrid").ligerGrid({
		url:MBIS.U('admin/Memberuser/pageQueryM'),
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
          { display: '微信号', name: 'user_weixin', isSort: false},
          { display: '邮箱', name: 'userEmail', isSort: false},
	        { display: '紧急联系人', name: 'urgency_contact', isSort: false},
          { display: '紧急联系电话', name: 'urgency_contact_mobile', isSort: false},
	        { display: '工作单位', name: 'company', isSort: false},
	        { display: '操作', name: 'op',isSort: false,render: function (rowdata, rowindex, value){
              var h = "";
              if(MBIS.GRANT.XYCK_04)h += "<a href='"+MBIS.U('admin/Memberuser/MemberInfo','id='+rowdata['userId'])+"'>查看</a> ";
              if(MBIS.GRANT.XYCK_02)h += "<a href='"+MBIS.U('admin/Memberuser/toEditMember','id='+rowdata['userId'])+"'>修改</a> ";
              //if(MBIS.GRANT.XYCK_03)h += "<a href='javascript:toDelMember(" + rowdata['userId'] + ")'>删除</a> "; 
              return h;
            
	        }}
        ]
    });
}
function userQueryU(){
	var query = MBIS.getParams('.query');
  grid.set('url',MBIS.U('admin/Memberuser/pageQueryM',query));
}
function refresh(){
  $('.query').each(function(){
    if($(this).val() !== ''){
      $(this).val('');
    }
  });
  grid.set('url',MBIS.U('admin/Memberuser/pageQueryM'));
}
function toAdd(){
  location.href=MBIS.U('admin/Memberuser/toAdd','type_id='+type_id);
}
function editInit(){
	 /* 表单验证 */
    $('#userForm').validator({
            fields: {
                idcard: {
                  rule:"required;idcard;myRemote",
                  msg:{required:"请输入身份证号"},
                  tip:"请输入身份证号",
                  ok:"",
                },
                trueName: {
                  rule:"required;",
                  msg:{required:"请输入真实姓名"},
                  tip:"请输入真实姓名",
                  ok:"",
                },
                student_no: {
                  rule:"required;",
                  msg:{required:"请输入学员编号"},
                  tip:"请输入学员编号",
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
            var indexUrl = 'admin/Memberuser/indexMember';
            var userId = $('#userId').val();
            //var params = MBIS.getParams('.ipt');
            var params = $('#userForm').serialize();
            var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
            $.post(MBIS.U('admin/Memberuser/editMember'),params,function(data,textStatus){
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
    
  //上传身份证
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

  //上传证件照
  MBIS.upload({
      pick:'#adFile_identification',
      formData: {dir:'users'},
      accept: {extensions: 'gif,jpg,jpeg,bmp,png',mimeTypes: 'image/*'},
      callback:function(f){
        var json = MBIS.toAdminJson(f);
        if(json.status==1){
        $('#uploadMsg_identification').empty().hide();
        //将上传的图片路径赋给全局变量
        $('#identification_photo').val(json.savePath+json.thumb);
        $('#identification_html').html('<img src="'+MBIS.conf.ROOT+'/'+json.savePath+json.thumb+'"  height="152" />');
        }else{
          MBIS.msg(json.msg,{icon:2});
        }
      },
      progress:function(rate){
          $('#uploadMsg_identification').show().html('已上传'+rate+"%");
      }
  });

  //上传入学前毕业证
  MBIS.upload({
      pick:'#adFile_brfore_certificate',
      formData: {dir:'users'},
      accept: {extensions: 'gif,jpg,jpeg,bmp,png',mimeTypes: 'image/*'},
      callback:function(f){
        var json = MBIS.toAdminJson(f);
        if(json.status==1){
        $('#uploadMsg_brfore_certificate').empty().hide();
        //将上传的图片路径赋给全局变量
        $('#brfore_certificate_photo').val(json.savePath+json.thumb);
        $('#brfore_certificate_html').html('<img src="'+MBIS.conf.ROOT+'/'+json.savePath+json.thumb+'"  height="152" />');
        }else{
          MBIS.msg(json.msg,{icon:2});
        }
      },
      progress:function(rate){
          $('#uploadMsg_brfore_certificate').show().html('已上传'+rate+"%");
      }
  });

  //上传入学后毕业证
  MBIS.upload({
      pick:'#adFile_after_certificate',
      formData: {dir:'users'},
      accept: {extensions: 'gif,jpg,jpeg,bmp,png',mimeTypes: 'image/*'},
      callback:function(f){
        var json = MBIS.toAdminJson(f);
        if(json.status==1){
        $('#uploadMsg_after_certificate').empty().hide();
        //将上传的图片路径赋给全局变量
        $('#after_certificate_photo').val(json.savePath+json.thumb);
        $('#after_certificate_html').html('<img src="'+MBIS.conf.ROOT+'/'+json.savePath+json.thumb+'"  height="152" />');
        }else{
          MBIS.msg(json.msg,{icon:2});
        }
      },
      progress:function(rate){
          $('#uploadMsg_after_certificate').show().html('已上传'+rate+"%");
      }
  });
}
function majorGet(){
  var school_id = $('#school_id').val();
  if(school_id == ''){
    return false;
  }
  $.post(MBIS.U('admin/users/majorGet'),{school_id:school_id},function(data){
    var json = MBIS.toAdminJson(data);
    if(json.status == 1){
      $('#major_id').empty();
      $('#major_id').append("<option value=''>请选择</option>");
      $('#level_id').empty();
      $('#level_id').append("<option value=''>请选择</option>");
      $.each(json.data,function(key,value){
        $('#major_id').append("<option value="+value['major_id']+">"+value['name']+"</option>");
      });
    }else{
      MBIS.msg(json.msg,{icon:2});
      $('#major_id').empty();
      $('#major_id').append("<option value=''>请选择</option>");
      $('#level_id').empty();
      $('#level_id').append("<option value=''>请选择</option>");
    }
  });
}
function levelGet(){
  var major_id = $('#major_id').val();
  if(major_id == ''){
    return false;
  }
  $.post(MBIS.U('admin/users/levelGet'),{major_id:major_id},function(data){
    var json = MBIS.toAdminJson(data);
    if(json.status == 1){
      $('#level_id').empty();
      $('#level_id').append("<option value=''>请选择</option>");
      $.each(json.data,function(key,value){
        $('#level_id').append("<option value="+value['level_id']+">"+value['level_name']+"</option>");
      });
    }else{
      MBIS.msg(json.msg,{icon:2});
      $('#level_id').empty();
      $('#level_id').append("<option value=''>请选择</option>");
    }
  });
}