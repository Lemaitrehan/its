var grid;
var combo;
function initGrid(type_id,major_id){
    if(type_id == 1){
        var grid_config = {
            url:MBIS.U('admin/subject/pageQuery','type_id='+type_id+'&major_id='+major_id),
            pageSize:MBIS.pageSize,
            pageSizeOptions:MBIS.pageSizeOptions,
            height:'99%',
            width:'100%',
            minColToggle:6,
            rownumbers:true,
            columns: [
                { display: '科目名称', name: 'name',isSort: false},
                { display: '科目代码', name: 'subject_no',isSort: false},
            ]
        };
    }else{
        var grid_config = {
            url:MBIS.U('admin/subject/pageQuery','type_id='+type_id),
            pageSize:MBIS.pageSize,
            pageSizeOptions:MBIS.pageSizeOptions,
            height:'99%',
            width:'100%',
            minColToggle:6,
            rownumbers:true,
            columns: [
                { display: '科目名称', name: 'name',isSort: false},
                { display: '科目代码', name: 'subject_no',isSort: false},
            ]
        };
    }
    
    if(type_id == 2)
    {
        //grid_config.columns.push({ display: '课程名称', name: 'course_id',isSort: false});
    }
    grid_config.columns.push({ display: '专业名称', name: 'major_name',isSort: false});
    grid_config.columns.push({ display: '学院名称', name: 'school_name',isSort: false});
    //技能类判断
    if(type_id == 2)
    {
        grid_config.columns.push({ display: '原价', name: 'market_price',width: 100,isSort: false});
        grid_config.columns.push({ display: '标准价',name: 'sale_price',width: 100, isSort: false});
        grid_config.columns.push({ display: '总课时', name: 'course_hours',width: 100,isSort: false});
        grid_config.columns.push({ display: '上课方式', name: 'teaching_type',width: 100,isSort: false});
        grid_config.columns.push({ display: '上课老师', name: 'teacher_id',width: 100,isSort: false});
        grid_config.columns.push({ display: '是否上架', name: 'is_shelves',width: 100,isSort: false});
    }
    grid_config.columns.push({ display: '操作', name: 'op',width: 100,isSort: false,
	        	render: function (rowdata){
		            var h = "";
                    if(type_id == 2){
                        //if(MBIS.GRANT.)h += "<a href='javascript:toEdit("+type_id+","+rowdata["subject_id"]+")'>修改</a> ";
                        if(MBIS.GRANT.KMCX_02)h += "<a href='javascript:toEdit("+type_id+","+rowdata["subject_id"]+")'>修改</a> ";
                        if(MBIS.GRANT.KMCX_03)h += "<a href='javascript:toDel("+type_id+","+rowdata["subject_id"]+")'>删除</a> "; 
                    }else
                    if(type_id == 1){
                        //if(MBIS.GRANT.)h += "<a href='javascript:toEdit("+type_id+","+rowdata["subject_id"]+")'>修改</a> ";
                        if(MBIS.GRANT.XLZY_052)h += "<a href='javascript:toEdit("+type_id+","+rowdata["subject_id"]+")'>修改</a> ";
                        if(MBIS.GRANT.XLZY_052)h += "<a href='javascript:toDel("+type_id+","+rowdata["subject_id"]+")'>删除</a> ";
                    } 
		            return h;
	        	}
	        });
	grid = $("#maingrid").ligerGrid(grid_config);

}

function initCombo(){
}

function loadGrid(type_id){
	grid.set('url',MBIS.U('admin/subject/pageQuery','key='+$('#key').val())+'&type_id='+type_id);
}

function subjectQuery(type_id){
    var query = MBIS.getParams('.query');
    grid.set('url',MBIS.U('admin/subject/pageQuery',query));
}
function refresh(type_id){
    $('.query').each(function(){
        if($(this).val() !== ''){
          $(this).val('');
        }
    });
    grid.set('url',MBIS.U('admin/subject/pageQuery','type_id='+type_id));
}
function toEdit(type_id,id){
	location.href=MBIS.U('admin/subject/toEdit','type_id='+type_id+'&id='+id);
}

function toEditEduSubject(type_id,school_id,major_id){
    window.location.href=MBIS.U('admin/subject/toEdit','type_id='+type_id+'&school_id='+school_id+'&major_id='+major_id);
}

function toEdits(id,type){
    //var params = MBIS.getParams('.ipt');
    var type_id  = type;
    var major_id = $('#major_id').val();
    var params   = $('#infoForm').serialize();
    params.id = id;
    var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
	$.post(MBIS.U('admin/subject/'+((id>0)?"edit":"add")),params,function(data,textStatus){
		  layer.close(loading);
		  var json = MBIS.toAdminJson(data);
		  if(json.status=='1'){
		    	MBIS.msg(json.msg,{icon:1});
		        setTimeout(function(){ 
			    	location.href=MBIS.U('admin/subject/index','type_id='+type_id+'&major_id='+major_id);
		        },1000);
		  }else{
		        MBIS.msg(json.msg,{icon:2});
		  }
	});
}

function toDel(type_id,id){
	var box = MBIS.confirm({content:"您确定要删除该数据吗?",yes:function(){
	           var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
	           	$.post(MBIS.U('admin/subject/del'),{type_id:type_id,id:id},function(data,textStatus){
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
    $.post(MBIS.U('admin/subject/get_subject_prop_data'),{type_id:type_id,subject_id:subject_id},function(data,textStatus){
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
    $.post(MBIS.U('admin/subject/get_discount_data'),{type:type,subject_id:subject_id},function(data,textStatus){
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
        $.post(MBIS.U('admin/subject/getAdItemList'),{id:id_array},function(data){
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