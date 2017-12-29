var grid;
var combo;
function initGrid(type_id){
    grid = $("#maingrid").ligerGrid({
        url:MBIS.U('admin/subjectedu/pageQueryEdu','type_id='+type_id),
        pageSize:MBIS.pageSize,
        pageSizeOptions:MBIS.pageSizeOptions,
        height:'99%',
        width:'100%',
        minColToggle:9,
        rownumbers:true,
        columns: [
            { display: '科目名称', name: 'name',isSort: false},
            { display: '科目代码', name: 'subject_no',isSort: false,},
            { display: '类型序号', name: 'type_number',isSort: false},
            { display: '学分', name: 'credit',isSort: false},
            { display: '类型', name: 'genre',isSort: false},
            { display: '考试方式', name: 'exam_method',isSort: false},
            { display: '考试时间(月)', name: 'exam_time',isSort: false},
            { display: '操作', name: 'op',width:80,isSort: false,
                render: function (rowdata){
                    if(type_id ==1){
                        var h = "";
                        if(MBIS.GRANT.CKCJ_02)h += "<a href='javascript:toEdit("+rowdata["subject_id"]+","+type_id+")'>修改</a> ";
                        if(MBIS.GRANT.CKCJ_03)h += "<a href='javascript:toDel("+rowdata["subject_id"]+","+type_id+")'>删除</a> ";
                        return h;
                    }
                }
            }
        ]
    });

}

function initCombo(){
}

function loadGrid(type_id){
	grid.set('url',MBIS.U('admin/subjectedu/pageQueryEdu','key='+$('#key').val())+'&type_id='+type_id);
}

function subjectQuery(type_id){
    var query = MBIS.getParams('.query');
    grid.set('url',MBIS.U('admin/subjectedu/pageQueryEdu',query));
}
function refresh(type_id){
    $('.query').each(function(){
        if($(this).val() !== ''){
          $(this).val('');
        }
    });
    grid.set('url',MBIS.U('admin/subjectedu/pageQueryEdu','type_id='+type_id));
}
function toEdit(id,type_id){
	location.href=MBIS.U('admin/subjectedu/toEditEdu','type_id='+type_id+'&id='+id);
}

function toEdits(id,type_id){
    //var params = MBIS.getParams('.ipt');
    var params   = $('#infoForm').serialize();
    //params.id = id;
    //params.type_id = type_id;
    var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
	$.post(MBIS.U('admin/subjectedu/'+((id>0)?"editEdu":"addEdu")),params,function(data,textStatus){
		  layer.close(loading);
		  var json = MBIS.toAdminJson(data);
		  if(json.status=='1'){
		    	MBIS.msg(json.msg,{icon:1});
		        setTimeout(function(){ 
			    	location.href=MBIS.U('admin/subjectedu/indexEdu','type_id='+type_id);
		        },1000);
		  }else{
		        MBIS.msg(json.msg,{icon:2});
		  }
	});
}
function toDel(id,type_id){
	var box = MBIS.confirm({content:"您确定要删除该数据吗?",yes:function(){
        var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
       	$.post(MBIS.U('admin/subjectedu/delEdu'),{type_id:type_id,id:id},function(data,textStatus){
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
//获取科目类型对应的属性列表
function get_subject_prop_data(type_id,subject_id)
{
    var loading = MBIS.msg('正在请求数据，请稍后...', {icon: 16,time:60000});
    $.post(MBIS.U('admin/subjectedu/get_subject_prop_data'),{type_id:type_id,subject_id:subject_id},function(data,textStatus){
          layer.close(loading);
          var json = MBIS.toAdminJson(data);
          if(json.status=='1'){
                $('#subject_type_prop').html(json.html);
                $('#subject_type_id').val(type_id);
          }else{
                MBIS.msg(json.msg,{icon:2});
          }
    });
}
//获取优惠条件
function get_discount_data(type,subject_id)
{
    var loading = MBIS.msg('正在请求数据，请稍后...', {icon: 16,time:60000});
    $.post(MBIS.U('admin/subjectedu/get_discount_data'),{type:type,subject_id:subject_id},function(data,textStatus){
          layer.close(loading);
          var json = MBIS.toAdminJson(data);
          if(json.status=='1'){
                $('#discount_data').html(json.html);
          }else{
                MBIS.msg(json.msg,{icon:2});
          }
    });
}

//获取专业列表
function getMajorLists(type_id,school_id,major_id)
{
    var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
    $.post(MBIS.U('admin/course/get_major_list'),{type_id:type_id,school_id:school_id,major_id:major_id},function(data,textStatus){
          layer.close(loading);
          var json = MBIS.toAdminJson(data);
          if(json.status=='1'){
                $('#school_major').html(json.html);
          }else{
                MBIS.msg(json.msg,{icon:2});
          }
    });
}
//获取专业列表
function getMajors(){
    var type_id = $('#type_id').val();
    var school_id = $('#school_id').val();
    $.post(MBIS.U('admin/course/getMajors'),{type_id:type_id,school_id:school_id},function(data){
        var json = MBIS.toAdminJson(data);
        if(json.status == 1){
            $('#major_id').empty();
            $('#major_id').append("<option value=''>请选择</option>");
            $.each(json.data,function(k,v){
                $('#major_id').append("<option value="+v['major_id']+">"+v['name']+"</option>");
            }); 
        }else{
            MBIS.msg(json.msg);
            $('#major_id').empty();
            $('#major_id').append("<option value=''>请选择</option>");
        }
    });
}
//弹出隐藏层
function ShowDiv(show_div,bg_div){
document.getElementById(show_div).style.display='block';
document.getElementById(bg_div).style.display='block' ;
var bgdiv = document.getElementById(bg_div);
bgdiv.style.width = document.body.scrollWidth;
// bgdiv.style.height = $(document).height();
$("#"+bg_div).height($(document).height());
};
//关闭弹出层
function CloseDiv(show_div,bg_div)
{
document.getElementById(show_div).style.display='none';
document.getElementById(bg_div).style.display='none';
};

function getCheckedi(show_div,bg_div){
    var id_array = Array();
    $("input[name='chki']:checked").each(function(){
        id_array.push($(this).val());
    });
    if(id_array != ''){
        $.post(MBIS.U('admin/subjectedu/getAdItemList'),{id:id_array},function(data){
            var json = MBIS.toAdminJson(data);
            if(json.status == 1){
                $('#it_ids').val(json.data);
                document.getElementById(show_div).style.display='none';
                document.getElementById(bg_div).style.display='none';
            }else{
                MBIS.msg(json.msg,{icon:2});  
            }
        });
    }else{
       MBIS.msg("没有选中任何选项",{icon:2}); 
    }
}