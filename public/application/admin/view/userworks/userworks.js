var grid;
function initGrid(){
	grid = $("#maingrid").ligerGrid({
		url:MBIS.U('admin/userworks/pageQuery'),
		pageSize:MBIS.pageSize,
		pageSizeOptions:MBIS.pageSizeOptions,
		height:'99%',
        width:'100%',
        minColToggle:6,
        rownumbers:true,
        columns: [
	        { display: '学员编号', name: 'student_no', isSort: false},
	        { display: '身份证号', name: 'idcard', isSort: false},
	        { display: '姓名', name: 'trueName', isSort: false},
	        { display: '学习状态', name: 'study_status', isSort: false},
	        { display: '联系电话', name: 'userPhone', isSort: false},
            { display: 'QQ', name: 'userQQ', isSort: false},
            { display: '邮箱', name: 'userEmail', isSort: false},
            { display: '专业', name: 'majorName', isSort: false},
            { display: '科目', name: 'subjectName', isSort: false},
            { display: '提交作品数', name: 'workNums', isSort: false},
	        { display: '操作', name: 'op',isSort: false,render: function (rowdata, rowindex, value){
	            var h = "";
	            if(MBIS.GRANT.ZPGL_00)h += "<a href='"+MBIS.U('admin/userworks/toEdit','userId='+rowdata['userId']+'&major_id='+rowdata['major_id']+'&subject_id='+rowdata['subject_id']+'&work_id='+rowdata['id'])+"'>查看作品</a> ";
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
    $('#userWorkForm').validator({
          valid: function(form){
            var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
            $.post(MBIS.U('admin/userworks/edit'),$('#userWorkForm').serialize(),function(data,textStatus){
              layer.close(loading);
              var json = MBIS.toAdminJson(data);
              if(json.status=='1'){
                  MBIS.msg("操作成功",{icon:1});
                  location.href=MBIS.U('Admin/sjexams/indexEducation');
              }else{
                    MBIS.msg(json.msg,{icon:2});
              }
            });

      }

    });

//文件上传

MBIS.upload({
    pick:'#userranksPicker',
    formData: {dir:'userworks'},
    accept: {extensions: 'gif,jpg,jpeg,bmp,png',mimeTypes: 'image/*'},
    callback:function(f){
      var json = MBIS.toAdminJson(f);
      if(json.status==1){
      $('#uploadMsg').empty().hide();
      //$('#userrankImg').val(json.savePath+json.thumb);
      addUploadPic(json.savePath+json.thumb);
      }else{
        MBIS.msg(json.msg,{icon:2});
      }
  },
  progress:function(rate){
      $('#uploadMsg').show().html('已上传'+rate+"%");
  }
});
};
//提交表单
function doSubmit()
{
    $.post(MBIS.U('admin/userworks/upload'),$('#userWorkForm').serialize(),function(data,textStatus){
      var json = MBIS.toAdminJson(data);
      if(json.status=='1'){
          MBIS.msg(json.msg,{icon:1});
          getUpload($('#userSelData').val(),$('#majorSelData').val(),$('#subjectSelData').val());
          //$('#preview').html(json.data);
      }else{
            MBIS.msg(json.msg,{icon:2});
      }
    });   
}
//获取上传列表
function getUpload(userId,major_id,subject_id)
{
    $.get(MBIS.U('admin/userworks/getupload','userId='+userId+'&major_id='+major_id+'&subject_id='+subject_id),{},function(rs){
         
         $('#preview').html(rs);
    });
}

//追加图片
function addUploadPic(picUrl)
{
    $('#preview').append('<div class="upload-item"><span title="删除作品，需要提交" onclick="$(this).parent().remove()" class="del-btn">X</span><img title="点击预览" onclick="$.ligerDialog.open({ url: \'/'+picUrl+'\', width:800, height: 450, isResize: true });" src="/'+picUrl+'" width="100"><input type="hidden" name="works_data[]" value="'+picUrl+'"></div>');   
}



		