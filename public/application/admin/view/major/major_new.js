var grid;
var combo;
//学历类 专业erwerw
function initGrid(exam_type){
    var grid_config = {
		url:MBIS.U('admin/major/index','exam_type='+exam_type),
		pageSize:MBIS.pageSize,
		pageSizeOptions:MBIS.pageSizeOptions,
		height:'99%',
		rowHeight:'40px',
                width:'100%',
                minColToggle:6,
                rownumbers:true,
                columns: [
            { display: '专业编号', name: 'major_number',isSort: false},
            { display: '专业名称', name: 'name',isSort: false},
            { display: '所属学校', name: 'school_name',isSort: false},
            { display: '学年', name: 'graduate_time',width:'20%',isSort: false},
            { display: '是否前台显示', name: 'is_show',isSort: false},
            { display: '是否上架', name: 'is_sell',isSort: false},
        ]
    };

        grid_config.columns.push({ display: '操作', name: 'op',width: 200,isSort: false,
	        	render: function (rowdata){
		            var h = "";
		            	if(MBIS.GRANT.XLZY_02)h += "<a href='javascript:toEdit("+rowdata["major_id"]+")'>编辑</a> ";
		            	if(MBIS.GRANT.XLZY_03)h += "<a href='javascript:toDel("+rowdata["major_id"]+")'>删除</a> ";
		            return h;
	        	}
	        });

    
      
	grid = $("#maingrid").ligerGrid(grid_config);

}


/*function getChecked(){
    var data = $("#target1").val();
}
function initCombo(){
}

function loadGrid(type_id){
	grid.set('url',MBIS.U('admin/major/pageQuery','key='+$('#key').val())+'&type_id='+type_id);
}
function refresh(type_id){
	$('.query').each(function(){
	    if($(this).val() !== ''){
	      $(this).val('');
	    }
  	});
    grid.set('url',MBIS.U('admin/major/pageQueryNew','type_id='+type_id));
}
function majorQuery(){
	var major_id = $('#major_id').val();
    grid.set('url',MBIS.U('admin/major/pageQuery?','major_id='+major_id));
}*/
function majorQuery(){
	var major_id = $('#major_id').val();
    grid.set('url',MBIS.U('admin/major/index','major_id='+major_id));
}
function toEdit(id){
	location.href=MBIS.U('admin/major/toEditEducation','id='+id);
}

function toEdits(id){
    //var params = MBIS.getParams('.ipt');
    var params = $('#majorForm').serialize();
    params.id = id;
    var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
	$.post(MBIS.U('admin/major/'+((id>0)?"edit":"add")),params,function(data,textStatus){
		  layer.close(loading);
		  var json = MBIS.toAdminJson(data);
		  if(json.status=='1'){
		    	MBIS.msg(json.msg,{icon:1});
		        setTimeout(function(){ 
			    	location.href=MBIS.U('admin/major/index','type_id='+$('#type_id').val());
		        },1000);
		  }else{
		        MBIS.msg(json.msg,{icon:2});
		  }
	});
}

function toDel(id){
	var box = MBIS.confirm({content:"您确定要删除该数据吗?",yes:function(){
	           var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
	           	$.post(MBIS.U('admin/major/toDel'),{id:id},function(data,textStatus){
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
function add(){
    location.href=MBIS.U('admin/major/toadd');
}
function toSave(){
    //var params = MBIS.getParams('.ipt');
    var params = $('#majorForm').serialize();
    var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
	$.post(MBIS.U('admin/major/toSave'),params,function(data,textStatus){
		  layer.close(loading);
		  var json = MBIS.toAdminJson(data);
		  if(json.status=='1'){
		    	MBIS.msg(json.msg,{icon:1});
		        setTimeout(function(){ 
			    	location.href=MBIS.U('admin/major/index','exam_type='+$('#exam_type').val());
		        },1000);
		  }else{
		        MBIS.msg(json.msg,{icon:2});
		  }
	});
}

function initGrid1(exam_type){
        var url = MBIS.U('admin/major/getSchoolList?exam_tyype='+exam_type);
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

        $.ligerDialog.open({ target: $("#target1") ,title:'学院列表',width:'100%', height:'100%',cls:"closeCls"
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
function getCheckedSubject(type_id){
    var school_id = $('#school_id').val();
    var major_id = $('#major_id').val();
    var course_id = $('#pkey').val();
    var subject_ids = $('#subject_ids').val();
    subjectIds = subject_ids;
    alert(subjectIds);
}
//选择科目点击事件
function subjectSelect(){
    var course_id = $('#pkey').val();
    var type_id = $('#type_id').val();
    var major_id = $('#major_id').val();
    $.post(MBIS.U('admin/major/subjectSelect'),{course_id:course_id,type_id:type_id,major_id:major_id},function(data){
        var json = MBIS.toAdminJson(data);
        if(json.status == 1){
            $('#tab_subject tr:not(:first)').empty();
            $.each(json.data,function(k,v){
                $('#tab_subject').append("<tr id='ts_"+v.subject_id+"' class='str'><td><input id='ck_"+v.subject_id+"' type='checkbox' name='chk' value='"+v.subject_id+"'></td><td>"+v.subject_id+"</td><td>"+v.name+"</td><td>"+v.subject_no+"</td><td>"+v.school_id+"</td><td>"+v.major_id+"</td><td>"+v.market_price+"</td><td>"+v.sale_price+"</td><td>"+v.course_hours+"</td><td>"+v.is_shelves+"</td><td>"+v.teacher_id+"</td></tr>");
                if($.inArray(v.subject_id,v.subject_ids) !== -1){
                    $('#ck_'+v.subject_id).attr('checked',true);
                }
            });
        }else{
            MBIS.msg(json.msg,{icon:2});
        }
    });
}
function toCheck(major_id){
    location.href=MBIS.U('admin/major/checkMajor','major_id='+major_id);
}

function upSellEdu(id,type_id){
    var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
    $.post(MBIS.U('admin/major/upSellEdu'),{id:id,type_id:type_id},function(data,textStatus){
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