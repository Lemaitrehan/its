var grid;
var combo;
var type,exam_type;
var subjectIds='';
$(function(){
  $("#start_registration").ligerDateEditor();
  $("#stop_registration").ligerDateEditor();
  $("#start_execution").ligerDateEditor();
  $("#stop_execution").ligerDateEditor();
})
function initGrid(type_id){
    var grid_config = {
		url:MBIS.U('admin/course/pageQueryEdu','type_id='+type_id),
		pageSize:MBIS.pageSize,
		pageSizeOptions:MBIS.pageSizeOptions,
		height:'99%',
        width:'100%',
        minColToggle:6,
        rownumbers:true,
        columns: [
            { display: '课程名称', name: 'name',isSort: false},
            { display: '课程编号', name: 'course_bn',width:100,isSort: false},
            { display: '院校', name: 'school_name',width:150,isSort: false},
            { display: '专业', name: 'major_name',width:220,isSort: false},
            { display: '层次', name: 'level_type',width:100,isSort: false},
            { display: '学习形式', name: 'studyMode',width:100,isSort: false},
            { display: '考试类型', name: 'exam_type',width:100,isSort: false},
            { display: '是否上架', name: 'is_shelves',width:100,isSort: false},
        ]
    };
        grid_config.columns.push({ display: '操作', name: 'op',width: 100,isSort: false,
            render: function (rowdata){
                var h = "";
                if(MBIS.GRANT.XLKC_02)h += "<a href='javascript:toEdit("+type_id+","+rowdata["course_id"]+")'>修改</a> ";
                if(MBIS.GRANT.XLKC_03)h += "<a href='javascript:toDel("+type_id+","+rowdata["course_id"]+")'>删除</a> "; 
                return h;
            }
        }); 
    
	grid = $("#maingrid").ligerGrid(grid_config);
}

function initCombo(type_id,exam_type1){
    type = type_id;
    exam_type = exam_type1;
}   

function loadGrid(type_id){
	grid.set('url',MBIS.U('admin/course/pageQueryEdu','key='+$('#key').val())+'&type_id='+type_id);
}

function courseQuery(type_id){
    var query = MBIS.getParams('.query');
    grid.set('url',MBIS.U('admin/course/pageQueryEdu',query));
}
function refresh(type_id){
    $('.query').each(function(){
        if($(this).val() !== ''){
          $(this).val('');
        }
    });
    grid.set('url',MBIS.U('admin/course/pageQueryEdu','type_id='+type_id));
}
function toEdit(type_id,id){
	location.href=MBIS.U('admin/course/toEditEdu','type_id='+type_id+'&id='+id);
}

function toEdits(id,type_id){
    //var params = MBIS.getParams('.ipt');
    var params = $('#infoForm').serialize();
    params.id = id;
    var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
	$.post(MBIS.U('admin/course/'+((id>0)?"editEdu":"addEdu")),params,function(data,textStatus){
		  layer.close(loading);
		  var json = MBIS.toAdminJson(data);
		  if(json.status=='1'){
		    	MBIS.msg(json.msg,{icon:1});
		        setTimeout(function(){ 
			    	location.href=MBIS.U('admin/course/indexEdu','type_id='+type_id);
		        },1000);
		  }else{
		        MBIS.msg(json.msg,{icon:2});
		  }
	});
}

function toDel(type_id,id){
	var box = MBIS.confirm({content:"您确定要删除该数据吗?",yes:function(){
	           var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
	           	$.post(MBIS.U('admin/course/delEdu'),{type_id:type_id,id:id},function(data,textStatus){
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
//价格计算
function sub_price()
{
    var course_cost = 0;
    var course_market_price = 0;
    var course_sale_price = 0;
    $('#subject_ids').find("option").each(function(i) {
        var self_o = $(this);
        if(self_o.is(":selected"))
        {
            course_cost += parseFloat(self_o.attr('data-cost'));
            course_market_price += parseFloat(self_o.attr('data-market-price'));
            course_sale_price += parseFloat(self_o.attr('data-sale-price'));
        }
    });
    $('#course_cost').html(course_cost.toFixed(2));
    $('#course_market_price').html(course_market_price.toFixed(2));
    $('#course_sale_price').html(course_sale_price.toFixed(2));
}

//获取专业列表
function getMajorList(){
    var type_id   = $('#type_id').val();
    var school_id = $('#school_id').val();
    if(school_id == ''){
        return false;
    }
    $('#major_id').html("<option value=''>请选择</option>");//专业
    $('#level_type').html("<option value=''>请选择</option>");//层次
    $('#studyMode').val('');//学习方式 还原
    $('#name').val('');
    $('#course_bn').html()
    $.post(MBIS.U('admin/course/getMajorList'),{type_id:type_id,school_id:school_id},function(data){
        var json = MBIS.toAdminJson(data);
        if(json.status == 1){

            $.each(json.data,function(k,v){
                $('#major_id').append("<option value="+v['major_id']+">"+v['name']+"</option>");
            }); 
        }else{
            MBIS.msg(json.msg);
            $('#major_id').empty();
            $('#major_id').append("<option value=''>请选择</option>");
            $('#level_type').empty();
            $('#level_type').append("<option value=''>请选择</option>");
        }
    });
}
function getLevel(){
    var major_id = $('#major_id').val();
    if(major_id == ''){
        return false;
    }
    $('#level_type').html("<option value=''>请选择</option>");//层次
    $('#studyMode').val('');//学习方式 还原
    $('#name').val('');
    $('#course_bn').html()
    
    $.post(MBIS.U('admin/course/getLevel'),{major_id:major_id},function(data){
        var json = MBIS.toAdminJson(data);
        if(json.status == 1){
            $('#level_type').empty();
            $('#level_type').append("<option value=''>请选择</option>");
            $.each(json.data,function(k,v){
                $('#level_type').append("<option value="+v['level_id']+">"+v['level_name']+"</option>");
            });
        }else{
            MBIS.msg(json.msg);
            $('#level_type').empty();
            $('#level_type').append("<option value=''>请选择</option>");
            $('#name').val();
        }
    });
}

function getLevel_1(){
    $('#studyMode').val('');//学习方式 还原
    $('#name').val('');
    $('#course_bn').html()
}

function get_exam_type(exam_type){
	var html='';
    if( exam_type == 1){
    	html ='自考';
	}else if(exam_type == 2){
		html ='成考';
	}else{
		html ='网教';
	}
   return html; 
}

//动态生成课程名称
function setCourseName(){
	var course_string    = '';
	var exam_type_string = get_exam_type(exam_type);
	    course_string    = exam_type_string+'--';
    var school_id = $('#school_id').val();
    if(school_id == ''){
        MBIS.msg('请选择学院',{icon:2}); return false;
    }
    course_string    += $('#school_id option:selected').text()+'--';
    
    var level_type = $('#level_type').val();
    if(level_type == ''){
        MBIS.msg('请选择层次',{icon:2}); return false;
    }
    course_string    += $('#level_type option:selected').text()+'--';
    
    var major_id = $('#major_id').val();
    if(major_id == ''){
        MBIS.msg('请选择专业',{icon:2}); return false;
    }
    course_string    += $('#major_id option:selected').text()+'--';
    
    var studyMode = $('#studyMode').val();
    if(studyMode == ''){
        MBIS.msg('请选择学习形式',{icon:2}); return false;
    }
    course_string    += $('#studyMode option:selected').text();
    $('#name').val(course_string);
 /*   $.post(MBIS.U('admin/course/setCourseName'),{school_id:school_id,level_type:level_type,major_id:major_id,studyMode:studyMode},function(data){
        var json = MBIS.toAdminJson(data);  
        if(json.status == 1){
            $('#name').val(json.data);
        }
    });*/
}

//导出
function export_data(){
    var query = MBIS.getParams('.query');
	window.location.href= '/index.php/admin/course/export.html?'+query;
}

function upSell(id,type_id){
    var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
    $.post(MBIS.U('admin/course/upSell'),{id:id,type_id:type_id},function(data,textStatus){
        layer.close(loading);
        var json = MBIS.toAdminJson(data);
        if(json.status== '1'){
            MBIS.msg(json.msg,{icon:1});
            grid.reload();
        }else{
            MBIS.msg(json.msg,{icon:2});
        }
    });

}


