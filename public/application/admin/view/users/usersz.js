var grid;
$(function(){
  $("#brithday").ligerDateEditor();
  $("#start").ligerDateEditor();
  $("#end").ligerDateEditor();
})
function initGrid(){
	grid = $("#maingrid").ligerGrid({
		url:MBIS.U('admin/Users/pageQueryZ'),
		pageSize:MBIS.pageSize,
		pageSizeOptions:MBIS.pageSizeOptions,
		height:'99%',
        width:'100%',
        minColToggle:6,
        rownumbers:true,
        columns: [
	        { display: '用户账号', name: 'loginName', isSort: false},
	        { display: '真实姓名', name: 'trueName', isSort: false},
          { display: '手机号码', name: 'userPhone', isSort: false},
          { display: '部门', name: 'department_id',width:160, isSort: false},
	        { display: '岗位', name: 'employee_type_id', isSort: false},
	        { display: '电子邮箱', name: 'userEmail', isSort: false},
	        //{ display: '等级', name: 'rebate', isSort: false},
	        { display: '创建时间', name: 'createtime', isSort: false},
	        { display: '状态', name: 'userStatus', isSort: false, render:function(rowdata, rowindex, value){
	        	return (value==1)?'启用':'停用';
	        }},
	        { display: '操作', name: 'op',isSort: false,render: function (rowdata, rowindex, value){
	            var h = "";
	            if(MBIS.GRANT.ZXSCK_02)h += "<a href='"+MBIS.U('admin/Users/toEditz','id='+rowdata['userId'])+"'>修改</a> ";
	            if(MBIS.GRANT.ZXSCK_03)h += "<a href='javascript:toDel(" + rowdata['userId'] + ")'>删除</a> "; 
	            return h;
	        }}
        ]
    });
	
	
	
}
function toDel(id){
	var box = MBIS.confirm({content:"您确定要删除该记录吗?",yes:function(){
	           var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
	           	$.post(MBIS.U('admin/Users/delz'),{id:id},function(data,textStatus){
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

function userQueryZ(){
				var query = MBIS.getParams('.query');
			    grid.set('url',MBIS.U('admin/Users/pageQueryZ',query));
			}
function refresh(){
  $('.query').each(function(){
      if($(this).val() !== ''){
        $(this).val('');
      }
    });
  grid.set('url',MBIS.U('admin/Users/pageQueryZ'));
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
            //var params = MBIS.getParams('.ipt');
            var params = $('#userForm').serialize();
            var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
            $.post(MBIS.U('admin/Users/'+((userId==0)?"addz":"editz")),params,function(data,textStatus){
              layer.close(loading);
              var json = MBIS.toAdminJson(data);
              if(json.status=='1'){
                  MBIS.msg("操作成功",{icon:1});
                  location.href=MBIS.U('Admin/Users/index_z');
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
function checkdep(){
  var department_id = $('#department_id').val();
  if(department_id == ''){
    $('#employee_type_id').empty();
    $('#employee_type_id').append("<option value=''>请选择</option>");return false;
  }
  $.post(MBIS.U('admin/users/checkdep'),{department_id:department_id},function(data){
    var json = MBIS.toAdminJson(data);
    if(json.status == 1){
      $('#employee_type_id').empty();
      $('#employee_type_id').append("<option value=''>请选择</option>");
      $.each(json.data,function(key,value){
        $('#employee_type_id').append("<option value="+value['employee_type_id']+">"+value['name']+"</option>");
      });
    }else{
      MBIS.msg(json.msg,{icon:2});
      $('#employee_type_id').empty();
      $('#employee_type_id').append("<option value=''>请选择</option>");
    }
  });
}

function checkType(){
  var departmentId = $('#department_id').val();
  if(departmentId == ''){
    MBIS.msg('请选择有效选项',{icon:2});return false;
  }
  $.post(MBIS.U('admin/users/checkType'),{departmentId:departmentId},function(data){
    var json = MBIS.toAdminJson(data);
    if(json.status == 1){
      $('#employee_type_id').empty();
      $('#employee_type_id').append("<option value=''>请选择</option>");
      $('#employee_id').empty();
      $('#employee_id').append("<option value=''>请选择</option>");
      $('#trueName').val('');
      $.each(json.data,function(key,value){
        $('#employee_type_id').append("<option value="+value['employee_type_id']+">"+value['name']+"</option>");
      });
    }else{
      MBIS.msg(json.msg,{icon:2});
      $('#employee_type_id').empty();
      $('#employee_type_id').append("<option value=''>请选择</option>");
      $('#employee_id').empty();
      $('#employee_id').append("<option value=''>请选择</option>");
      $('#trueName').val('');
    }
  });
}

function checkemp(){
  var departmentId = $('#department_id').val();
  if(departmentId == ''){
    $('#employee_type_id').empty();
    $('#employee_type_id').append("<option value=''>请选择</option>");
    $('#employee_id').empty();
    $('#employee_id').append("<option value=''>请选择</option>");
    MBIS.msg('请先选择部门');return false;
  }
  var employeetypeId = $('#employee_type_id').val();
  if(employeetypeId == ''){
    MBIS.msg('请选择有效选项',{icon:2});return false;
  }
  $.post(MBIS.U('admin/users/checkemp'),{employeetypeId:employeetypeId},function(data){
    var json = MBIS.toAdminJson(data);
    if(json.status == 1){
      $('#employee_id').empty();
      $('#employee_id').append("<option value=''>请选择</option>");
      $('#trueName').val('');
      $.each(json.data,function(key,value){
        $('#employee_id').append("<option value="+value['employee_id']+">"+value['name']+"</option>");
      });
    }else{
      MBIS.msg(json.msg,{icon:2});
      $('#employee_id').empty();
      $('#employee_id').append("<option value=''>请选择</option>");
      $('#trueName').val('');
    }
  });
}
function checkname(){
  var departmentId = $('#department_id').val();
  if(departmentId == ''){
    $('#employee_type_id').empty();
    $('#employee_type_id').append("<option value=''>请选择</option>");
    $('#employee_id').empty();
    $('#employee_id').append("<option value=''>请选择</option>");
    MBIS.msg('请先选择部门');return false;
  }
  var employeetypeId = $('#employee_type_id').val();
  if(employeetypeId == ''){
    $('#employee_id').empty();
    $('#employee_id').append("<option value=''>请选择</option>");
    MBIS.msg('请先选择岗位');return false;
  }
  var employee_id = $('#employee_id').val();
  if(employee_id == ''){
    MBIS.msg('请选择有效选项',{icon:2});return false;
  }
  $.post(MBIS.U('admin/users/checkname'),{employee_id:employee_id},function(data){
    var json = MBIS.toAdminJson(data);
    if(json.status == 1){
      $('#trueName').val(json.name);
    }else{
      $('#trueName').val('');
    }
  });
}