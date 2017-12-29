var grid;
$(function(){
})
function initGrid(type){
	grid = $("#maingrid").ligerGrid({
    url:MBIS.U('admin/Studentedu/pageQuery','type_id='+type_id),
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
          { display: '报读院校', name: 'school_name', isSort: false},
          { display: '专业', name: 'major_name', isSort: false},
          { display: '层次', name: 'level_id', isSort: false},
          { display: '年级', name: 'grade_name', isSort: false},
          { display: '录取状态', name: 'entry_status', isSort: false},
          { display: '处理状态', name: 'dispose_status', isSort: false},
          { display: '处理结果', name: 'dispose_result', isSort: false},
          { display: '操作', name: 'op',isSort: false,render: function (rowdata, rowindex, value){
              var h = "";
              //if(MBIS.GRANT.XYCK_04)h += "<a href='"+MBIS.U('admin/Studentedu/getUserInfo','id='+rowdata['edu_id']+'&type_id='+type_id)+"'>查看</a> ";
              if(MBIS.GRANT.XYCK_02)h += "<a href='"+MBIS.U('admin/Studentedu/toEdit','id='+rowdata['edu_id']+'&type_id='+type_id)+"'>编辑</a> ";
              return h;
            
          }}
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
  var url = 'admin/Studentedu/pageQuery';
  grid.set('url',MBIS.U(url));
}
//查询
function eduQuery(){
	var query = MBIS.getParams('.query');
  grid.set('url',MBIS.U('admin/Studentedu/pageQuery',query));
}

function editInit(type_id){
   /* 表单验证 */
    $('#eduForm').validator({
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
              var indexUrl = 'admin/Studentedu/indexEdu';
              //var params = MBIS.getParams('.ipt');
              var params = $('#eduForm').serialize();
              var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
              $.post(MBIS.U('admin/Studentedu/edit'),params,function(data,textStatus){
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
}

function majorGet(){
  var school_id = $('#school_id').val();
  if(school_id == ''){
    return false;
  }
  $.post(MBIS.U('admin/Studentedu/majorGet'),{school_id:school_id},function(data){
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
  $.post(MBIS.U('admin/Studentedu/levelGet'),{major_id:major_id},function(data){
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

function matriculate(exam_type){
        var url = MBIS.U('admin/Studentedu/getEduList?exam_tyype='+exam_type);
        grid = $("#search_div_z").ligerGrid({
            url:url,
            pageSize:MBIS.pageSize,
            pageSizeOptions:MBIS.pageSizeOptions,
            height:'99%',
            width:'99%',
            minColToggle:5,
            rownumbers:true,
            columns: [
                { display: '全选<input type="checkbox" id="allCheck" class="isAllCheck">', name:'checkbox',width:60,isSort: false,},
                { display: '学院编号', name: 'school_no',width:150,isSort: false},
                { display: '学院名称', name: 'name',width:140,isSort: false},
            ]
        });

        $.ligerDialog.open({ target: $("#target1") ,title:'学员列表',width:'100%', height:'100%',cls:"closeCls"
                , buttons: [  { text: '保存', onclick: function (i, d) { getChecked(); }}, 
                             { text: '关闭', onclick: function (i, d) { $("input").ligerHideTip(); d.hide(); }} 
                         ]             
        });
        //全选 的判断
        var is_all_checked = true;
        if( $('input[name="chk"]').val()  ){
            $('input[name="chk"]').each(function(i,e){
                if( !$(e).is(':checked') ){
                    is_all_checked = false;; 
                }
            })
            if(is_all_checked){
                $('.isAllCheck').prop('checked',true);
            }
        }
}
//单选点击事件
$(document).on('click','input[name="chk"]',function(){
    getCheckedOne(this);
})

//全选
$(document).on('click','#allCheck',function(){
    var is_all = $(this).is(':checked');
    var subjectList   = {};
    $(this).closest('#search_div_zgrid').find('input[name="chk"]').each(function(i,e){
        if(is_all){
            $(this).prop('checked',true);
        }else{
            $(this).prop('checked',false);
        }
        var subject_id   = $(this).val();
        var subject_name = $(this).closest('tr').find('td:eq(1)').find('div').html();
        subjectList[ subject_id ] = subject_name;
    })
    if(is_all){
        addDiv(subjectList);
    }else{
        delDiv(subjectList);
    }
})

//关闭弹窗
$(document).on("click",".closeCls",function(){
    $(this).closest('#search_')
    var checkedIds = subjectIds;
    if(checkedIds){
        makeResult(checkedIds);
    }else{
        makeResult(checkedIds);
    }
})




