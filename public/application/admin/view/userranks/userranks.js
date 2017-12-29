var grid;
function initGrid(){
	grid = $("#maingrid").ligerGrid({
		url:MBIS.U('admin/userranks/pageQuery'),
		pageSize:MBIS.pageSize,
		pageSizeOptions:MBIS.pageSizeOptions,
		height:'99%',
        width:'100%',
        minColToggle:6,
        rownumbers:true,
        columns: [
	        { display: '会员等级图标', name: 'userrankImg', isSort: false,render:function(rowdata, rowindex, value){
	        	return '<img src="'+MBIS.conf.ROOT+'/'+rowdata['userrankImg']+'" height="28px" />';
	        }},
	        { display: '会员等级名称', name: 'rankName', isSort: false},
	        { display: '积分上限', name: 'startScore', isSort: false},
	        { display: '积分下限', name: 'endScore', isSort: false},
	        { display: '折扣率(%)', name: 'rebate', isSort: false},
	        { display: '操作', name: 'op',isSort: false,render: function (rowdata, rowindex, value){
	            var h = "";
	            if(MBIS.GRANT.HYDJ_02)h += "<a href='"+MBIS.U('admin/userranks/toEdit','id='+rowdata['rankId'])+"'>修改</a> ";
	            if(MBIS.GRANT.HYDJ_03)h += "<a href='javascript:toDel(" + rowdata['rankId'] + ")'>删除</a> "; 
	            return h;
	        }}
        ]
    });
}
function toDel(id){
	var box = MBIS.confirm({content:"您确定要删除该记录吗?",yes:function(){
	           var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
	           	$.post(MBIS.U('admin/userranks/del'),{id:id},function(data,textStatus){
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

function editInit(){
 /* 表单验证 */
    $('#userRankForm').validator({
            fields: {
                rankName: {
                  rule:"required",
                  msg:{required:"请输入会员等级名称"},
                  tip:"请输入会员等级名称",
                  ok:"",
                },
                userrankImg: {
                  rule:"required",
                  msg:{required:"请输上传会员图标"},
                  tip:"请输上传会员图标",
                  ok:"",
                }
                
            },

          valid: function(form){
            var params = MBIS.getParams('.ipt');
            var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
            $.post(MBIS.U('admin/userranks/'+((params.rankId==0)?"add":"edit")),params,function(data,textStatus){
              layer.close(loading);
              var json = MBIS.toAdminJson(data);
              if(json.status=='1'){
                  MBIS.msg("操作成功",{icon:1});
                  location.href=MBIS.U('Admin/userranks/index');
              }else{
                    MBIS.msg(json.msg,{icon:2});
              }
            });

      }

    });

//文件上传
MBIS.upload({
    pick:'#userranksPicker',
    formData: {dir:'userranks'},
    accept: {extensions: 'gif,jpg,jpeg,bmp,png',mimeTypes: 'image/*'},
    callback:function(f){
      var json = MBIS.toAdminJson(f);
      if(json.status==1){
      $('#uploadMsg').empty().hide();
      //保存上传的图片路径
      $('#userrankImg').val(json.savePath+json.thumb);
      $('#preview').html('<img src="'+MBIS.conf.ROOT+'/'+json.savePath+json.thumb+'" height="25" />');
      }else{
        MBIS.msg(json.msg,{icon:2});
      }
  },
  progress:function(rate){
      $('#uploadMsg').show().html('已上传'+rate+"%");
  }
});


};
  




		