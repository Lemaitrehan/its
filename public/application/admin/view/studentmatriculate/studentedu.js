var grid;
var checkedids = [];
var ids = '';
$(function(){
})
function initGrid(type){
	grid = $("#maingrid").ligerGrid({
    url:MBIS.U('admin/Studentmatriculate/pageQuery','type_id='+type_id),
    pageSize:MBIS.pageSize,
    pageSizeOptions:MBIS.pageSizeOptions,
    height:'99%',
        width:'100%',
        minColToggle:6,
        rownumbers:true,
        checkbox:false,
        columns: [
          { display: '全选<input type="checkbox" id="allCheck" onclick="javascript:allck(this);" class="isAllCheck">', name:'checkbox',width:50,isSort: false,},
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
              if(MBIS.GRANT.XYCK_02)h += "<a href='"+MBIS.U('admin/Studentmatriculate/toEdit','id='+rowdata['edu_id'])+"'>编辑</a> ";
              return h;
            
          }}
      
        ]
  });
}
function allck(obj){ //全选与取消全选
  if(obj.checked){
    $("input[name='chk']").prop('checked',true);
  }else{
    $("input[name='chk']").prop('checked',false);
  }
}

function matriculate(){  //批量处理
  /*
  $.ligerDialog.open({  url: '',
                        target: $("#search_div_z1"),
                        title:'批量处理', 
                        height: 300,
                        width: 200

                        ,buttons: [
                          { text: '确定', onclick: function (item, dialog) { alert(item.text); } }, 
                          { text: '取消', onclick: function (item, dialog) { dialog.close(); } }
                        ]
                    });
  */
  checkedids = [];
  ids = '';
  $("input[name='chk']").each(function(){
    if($(this).prop('checked')){
      checkedids.push($(this).val());
    }
  });
  ids = checkedids.join(",");
  if(ids == ''){
    MBIS.msg('请选择需要批量录取的学员',{icon:2});return false;
  }
  var indexUrl = 'admin/Studentmatriculate/indexEdu';
  var loading = MBIS.msg('处理中，请稍后...', {icon: 16,time:60000});
  $.post(MBIS.U('admin/Studentmatriculate/matriculate'),{ids:ids},function(data){
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

//刷新
function refresh(){
  $('.query').each(function(){
    if($(this).val() !== ''){
      $(this).val('');
    }
  });
  var url = 'admin/Studentmatriculate/pageQuery';
  grid.set('url',MBIS.U(url));
}
//查询
function eduQuery(){
	var query = MBIS.getParams('.query');
  grid.set('url',MBIS.U('admin/Studentmatriculate/pageQuery',query));
}

function editInit(){
   /* 表单验证 */
    $('#eduForm').validator({
            valid: function(form){
              var indexUrl = 'admin/Studentmatriculate/indexEdu';
              //var params = MBIS.getParams('.ipt');
              var params = $('#eduForm').serialize();
              var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
              $.post(MBIS.U('admin/Studentmatriculate/edit'),params,function(data,textStatus){
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
  $.post(MBIS.U('admin/Studentmatriculate/majorGet'),{school_id:school_id},function(data){
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
  $.post(MBIS.U('admin/Studentmatriculate/levelGet'),{major_id:major_id},function(data){
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



/*
function matriculateaa(exam_type){
        var url = MBIS.U('admin/Studentmatriculate/getEduList');
        grid = $("#search_div_z1").ligerGrid({
            url:url,
            pageSize:MBIS.pageSize,
            pageSizeOptions:MBIS.pageSizeOptions,
            height:'99%',
            width:'99%',
            minColToggle:5,
            rownumbers:true,
            columns: [
                { display: '全选<input type="checkbox" id="allCheck" class="isAllCheck">', name:'checkbox',width:50,isSort: false,},
                { display: '身份证号码', name: 'idcard',width:160,isSort: false},
                { display: '学员编号', name: 'student_no',width:60, isSort: false},
                { display: '姓名', name: 'trueName', width:60,isSort: false},
                { display: '报读院校', name: 'school_name', width:100,isSort: false},
                { display: '专业', name: 'major_name', width:130,isSort: false},
                { display: '层次', name: 'level_id', width:60,isSort: false},
                { display: '年级', name: 'grade_name', width:60,isSort: false},
            ]
        });

        $.ligerDialog.open({ target: $("#target1") ,title:'学员列表',width:'750', height:'100%',cls:"closeCls"
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
*/




