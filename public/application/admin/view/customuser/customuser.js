var grid;
$(function(){
  $("#graduate_date").ligerDateEditor();
})
function initGrid(){
	var query = MBIS.getParams('.query');
	grid = $("#maingrid").ligerGrid({
		url:MBIS.U('admin/Customer/index',query),
		pageSize:MBIS.pageSize,
		pageSizeOptions:MBIS.pageSizeOptions,
		height:'99%',
        width:'100%',
        minColToggle:6,
        rownumbers:true,
        columns: [
          { display: '推广ID', name: 'tg_id',width:160,isSort: false},
          { display: '客户类型', name: 'type_name', isSort: false},
          { display: '访客名称', name: 'visitors_name',width:160,isSort: false},
	        { display: '访客电话', name: 'visitors_phone', isSort: false},
          { display: '对话渠道', name: 'dialogue_channel', isSort: false},
          { display: '客服名称', name: 'customer_name', isSort: false},
          { display: '通话开始时间', name: 'start_time', isSort: false},
	        { display: '通话时长', name: 'call_wait_time_text', isSort: false},
          { display: '通话等待时间', name: 'call_wait_time', isSort: false},
	      { display: '通话时长', name: 'call_time', isSort: false},
	      { display: '通话内容', name: 'content', isSort: false},
	      { display: '首次相应时间', name: 'frist_response_time', isSort: false},
	      { display: '平均相应时长', name: 'average_response_time', isSort: false},
	      { display: '访客发送条数', name: 'visitor_send_num', isSort: false},
	      { display: '客服回复条数', name: 'reply_num', isSort: false},
	      { display: '答应时间', name: 'promised_time', isSort: false},
	      { display: '结束时间', name: 'stop_promised_time', isSort: false},
	      { display: '对话主题', name: 'dialogue_title', isSort: false},
	      { display: '对话备注', name: 'dialogue_remark', isSort: false},
	      { display: '对话级别', name: 'dialogue_level', isSort: false},
	      { display: '服务质量', name: 'service_quality', isSort: false},
	      { display: '服务评价', name: 'service_evaluation', isSort: false},
	      { display: '访客区域', name: 'visitors_region', isSort: false},
	      { display: '访客ip', name: 'visitors_ip', isSort: false},
	      { display: '搜索引擎', name: 'search_engine', isSort: false},
	      { display: '关键字', name: 'keyword', isSort: false},
	      { display: '来源', name: 'url', isSort: false},
	      { display: '落地页', name: 'page_url', isSort: false},
	      { display: '对话页', name: 'dialogue_url', isSort: false},
	      { display: '访客系统', name: 'system', isSort: false},
	      { display: '分辨率', name: 'resolution', isSort: false},
	      { display: '浏览器', name: 'browser', isSort: false},
	      { display: '分组id', name: 'groupingId', isSort: false},
	      /*{ display: '操作', name: 'op',width:100,isSort: false,render: function (rowdata, rowindex, value){
              var h = "";
              if(MBIS.GRANT.XYCK_04)h += "<a href='"+MBIS.U('admin/Customuser/CustomInfo','id='+rowdata['userId'])+"'>查看</a> ";
              if(MBIS.GRANT.XYCK_02)h += "<a href='"+MBIS.U('admin/Customuser/toEditCustom','id='+rowdata['userId'])+"'>修改</a> ";
              if(MBIS.GRANT.XYCK_03)h += "<a href='javascript:toDelCustom(" + rowdata['userId'] + ")'>删除</a> "; 
              return h;
            
	        }}*/
        ]
    });
}
function userQueryU(){
	var query = MBIS.getParams('.query');
  grid.set('url',MBIS.U('admin/Customuser/pageQueryC',query));
}
function refresh(){
  $('.query').each(function(){
    if($(this).val() !== ''){
      $(this).val('');
    }
  });
  grid.set('url',MBIS.U('admin/Customuser/pageQueryC'));
}
function toAdd(){
  location.href=MBIS.U('admin/Customuser/toAdd');
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
            var indexUrl = 'admin/Customuser/indexCustom';
            var userId = $('#userId').val();
            //var params = MBIS.getParams('.ipt');
            var params = $('#userForm').serialize();
            var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
            $.post(MBIS.U('admin/Customuser/editCustom'),params,function(data,textStatus){
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
}

function toDelCustom(id){
  var box = MBIS.confirm({content:"您确定要删除该数据吗?",yes:function(){
  var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
  $.post(MBIS.U('admin/Customuser/delCustom'),{id:id},function(data,textStatus){
        layer.close(loading);
        var json = MBIS.toAdminJson(data);
        if(json.status=='1'){
            MBIS.msg(json.msg,{icon:1});
            layer.close(box);
                grid.reload();
        }else{
            MBIS.msg(json.msg,{icon:2});
        }
    });
  }});
}