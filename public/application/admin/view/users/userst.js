var grid;
function initGrid(){
	grid = $("#maingrid").ligerGrid({
		url:MBIS.U('admin/users/pageQueryT'),
		pageSize:MBIS.pageSize,
		pageSizeOptions:MBIS.pageSizeOptions,
		height:'99%',
        width:'100%',
        minColToggle:6,
        rownumbers:true,
        columns: [
	        { display: '用户名', name: 'loginName', isSort: false},
	        { display: '真实姓名', name: 'trueName', isSort: false},
	        { display: '手机号码', name: 'userPhone', isSort: false},
	        { display: '电子邮箱', name: 'userEmail', isSort: false},
	        //{ display: '等级', name: 'rebate', isSort: false},
	        { display: '创建时间', name: 'createtime', isSort: false},
	        { display: '状态', name: 'userStatus', isSort: false, render:function(rowdata, rowindex, value){
	        	return (value==1)?'启用':'停用';
	        }},
	        { display: '操作', name: 'op',isSort: false,render: function (rowdata, rowindex, value){
	            var h = "";
	            if(MBIS.GRANT.LSCK_02)h += "<a href='"+MBIS.U('admin/users/toEditt','id='+rowdata['userId'])+"'>修改</a> ";
              if(MBIS.GRANT.LSCK_03)h += "<a href='javascript:toDel(" + rowdata['userId'] + ")'>删除</a> ";
	            if(MBIS.GRANT.LSCK_04)h += "<a href='"+MBIS.U('admin/users/toTeacherSet','id='+rowdata['userId'])+"'>授课科目配置</a> ";
	            return h;
	        }}
        ]
    });
	
	
	
}
function toDel(id){
	var box = MBIS.confirm({content:"您确定要删除该记录吗?",yes:function(){
	           var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
	           	$.post(MBIS.U('admin/users/delt'),{id:id},function(data,textStatus){
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

function userQueryT(){
	var query = MBIS.getParams('.query');
    grid.set('url',MBIS.U('admin/users/pageQueryT',query));
}

function refresh(){
  $('.query').each(function(){
      if($(this).val() !== ''){
        $(this).val('');
      }
    });
  grid.set('url',MBIS.U('admin/users/pageQueryT'));
}

function editInit(){
	 /* 表单验证 */
    $('#userForm').validator({
            
            fields: {
                trueName: {
                  rule:"required;",
                  msg:{required:"请输入真实姓名"},
                  tip:"请输入真实姓名",
                  ok:"",
                },
            },

          valid: function(form){
            var userId = $('#userId').val();
            //var params = MBIS.getParams('.ipt');
            var params = $('#userForm').serialize();
            var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
            $.post(MBIS.U('admin/users/'+((userId==0)?"addt":"editt")),params,function(data,textStatus){
              layer.close(loading);
              var json = MBIS.toAdminJson(data);
              if(json.status=='1'){
                  MBIS.msg("操作成功",{icon:1});
                  location.href=MBIS.U('Admin/users/index_t');
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

function TeacherSet(id){
  var params = $('#teacherForm').serialize();
  var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
    $.post(MBIS.U('admin/users/teacherset'),params,function(data,textStatus){
      layer.close(loading);
      var json = MBIS.toAdminJson(data);
      if(json.status=='1'){
          MBIS.msg("操作成功",{icon:1});
          location.href=MBIS.U('Admin/users/index_t');
      }else{
            MBIS.msg(json.msg,{icon:2});
      }
    });
}
