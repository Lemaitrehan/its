var grid;
var combo;
var userIds='';
var arrKS  = {};
function initGrid(type_id){
    var grid_config = {
        url:MBIS.U('admin/course/pageQuery','type_id='+type_id),
        pageSize:MBIS.pageSize,
        pageSizeOptions:MBIS.pageSizeOptions,
        height:'99%',
        width:'100%',
        minColToggle:6,
        rownumbers:true,
        columns: [
            { display: '课程名称', name: 'name',isSort: false},
            { display: '课程编号', name: 'course_bn',isSort: false},
            { display: '课程总课时', name: 'course_hours',isSort: false},
            { display: '专业名称', name: 'major_id',isSort: false},
            { display: '学院名称', name: 'school_id',isSort: false},
            { display: '原价', name: 'market_price',isSort: false},
            { display: '标准价', name: 'sale_price',isSort: false},
            { display: '是否上架', name: 'is_shelves',isSort: false},
        ]
    };
    //技能类判断
    if(type_id == 2)
    {
        grid_config.columns.push({ display: '上课方式', name: 'teaching_type',isSort: false});
        grid_config.columns.push({ display: '科目项', name: 'subject_ids',width: 200,isSort: false});
    }
    if(type_id == 1){
        grid_config.columns.push({display: '年级', name:'grade_id',isSort: false});
    }
    if(type_id == 2){
        grid_config.columns.push({ display: '操作', name: 'op',width: 100,isSort: false,
            render: function (rowdata){
                var h = "";
                if(MBIS.GRANT.KCCX_02)h += "<a href='javascript:toEdit("+type_id+","+rowdata["course_id"]+")'>修改</a> ";
                if(MBIS.GRANT.KCCX_03)h += "<a href='javascript:toDel("+type_id+","+rowdata["course_id"]+")'>删除</a> "; 
                return h;
            }
        });
    }else
    if(type_id == 1){
       grid_config.columns.push({ display: '操作', name: 'op',width: 100,isSort: false,
            render: function (rowdata){
                var h = "";
                if(MBIS.GRANT.XLKC_02)h += "<a href='javascript:toEdit("+type_id+","+rowdata["course_id"]+")'>修改</a> ";
                if(MBIS.GRANT.XLKC_03)h += "<a href='javascript:toDel("+type_id+","+rowdata["course_id"]+")'>删除</a> "; 
                return h;
            }
        }); 
    }
    grid = $("#maingrid").ligerGrid(grid_config);

}
var id;
function initCombo(getid,subject_js_ids,ks){
	id = getid;
	userIds = subject_js_ids;
	arrKS   = ks;
}

function loadGrid(type_id){
    grid.set('url',MBIS.U('admin/course/pageQuery','key='+$('#key').val())+'&type_id='+type_id);
}

function courseQuery(type_id){
    var query = MBIS.getParams('.query');
    grid.set('url',MBIS.U('admin/course/pageQuery',query));
}
function refresh(type_id){
    $('.query').each(function(){
        if($(this).val() !== ''){
          $(this).val('');
        }
    });
    grid.set('url',MBIS.U('admin/course/pageQuery','type_id='+type_id));
}
function toEdit(type_id,id){
    location.href=MBIS.U('admin/course/toEdit','type_id='+type_id+'&id='+id);
}

function toEdits(id,type_id){
    //var params = MBIS.getParams('.ipt');
    var params = $('#infoForm').serialize();
    params.id = id;
    var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
    $.post(MBIS.U('admin/course/'+((id>0)?"edit":"add")),params,function(data,textStatus){
          layer.close(loading);
          var json = MBIS.toAdminJson(data);
          if(json.status=='1'){
                MBIS.msg(json.msg,{icon:1});
                setTimeout(function(){ 
                    location.href=MBIS.U('admin/course/index','type_id='+type_id);
                },1000);
          }else{
                MBIS.msg(json.msg,{icon:2});
          }
    });
}

function toDel(type_id,id){
    var box = MBIS.confirm({content:"您确定要删除该数据吗?",yes:function(){
               var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
                $.post(MBIS.U('admin/course/del'),{type_id:type_id,id:id},function(data,textStatus){
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
var is_cs = 0;
//获取专业列表
function getMajorLists(type_id,school_id,level_type,major_id)
{
    var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
    $.post(MBIS.U('admin/course/get_major_list'),{type_id:type_id,school_id:school_id,level_type:level_type,major_id:major_id},function(data,textStatus){
          layer.close(loading);
          var json = MBIS.toAdminJson(data);
          if(json.status=='1'){
                $('#major_grade').html('');
                $('#school_major').html(json.html);
                if(is_cs>0){
	                $('#subject_ids').val('');
	                $('.checkUserIds').html('');
	                $('#course_hours').val('');
	                userIds ='';
                }
                ++is_cs;
          }else{
                MBIS.msg(json.msg,{icon:2});
          }
    });
}
//专业
$(document).on('change','#major_id',function(){
	 $('#subject_ids').val('');
     $('.checkUserIds').html('');
     $('#course_hours').val('');
     userIds ='';
})


//获取班级列表列表
function getGradeLists(major_id,grade_id)
{
    //var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
    $.post(MBIS.U('admin/course/get_grade_list'),{major_id:major_id,grade_id:grade_id},function(data,textStatus){
          //layer.close(loading);
          var json = MBIS.toAdminJson(data);
          if(json.status=='1'){
                if(json.data.exam_type==1)
                {
                    $('#subject_ids').attr('disabled',false);
                }
                else
                {
                    $('#subject_ids').attr('disabled',true);   
                }
                $('#major_grade').html(json.html);
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

//选择科目点击事件
function subjectSelect(){
    var course_id = $('#pkey').val();
    var type_id = $('#type_id').val();
    var major_id = $('#major_id').val();
    $.post(MBIS.U('admin/course/subjectSelect'),{course_id:course_id,type_id:type_id,major_id:major_id},function(data){
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

function getChecked(show_div,bg_div){
    var id_array = Array();
    $("input[name='chk']:checked").each(function(){
        id_array.push($(this).val());
    });
    $.post(MBIS.U('admin/course/getSubjectList'),{id:id_array},function(data){
        var json = MBIS.toAdminJson(data);
        if(json.status == 1){
            $('#subject_ids').val(json.data);
            $('#course_hours').val(json.courseHours);
            $('#cost_price').val(json.cost);
            $('#market_price').val(json.marketPrice);
            document.getElementById(show_div).style.display='none';
            document.getElementById(bg_div).style.display='none';
        }else{
            MBIS.msg(json.msg,{icon:2});  
        }
    });
}

function initGrid1(){

    var school_id = $('#school_id').val(),major_id = $('#major_id').val();
	$("#search_div_zgrid").ligerGrid({
        url:MBIS.U('admin/course/subjectSelect',{school_id:school_id,major_id:major_id,type_id:2,subject_ids:userIds}),
        pageSize:MBIS.pageSize,
        pageSizeOptions:MBIS.pageSizeOptions,
        height:'99%',
        width:'100%',
        checkbox:false,
        minColToggle:6,
        rownumbers:true,
        columns: [
  	        { display: '全选<input type="checkbox" id="allCheck" class="isAllCheck">', name:'checkbox',width:100,isSort: false,},
            { display: '科目名称', name: 'name',isSort: false},
            { display: '科目代码', name: 'subject_no',isSort: false},
            { display: '所属学院', name: 'school_id',isSort: false},
            { display: '所属专业', name: 'major_id',isSort: false},
            { display: '原价', name: 'market_price',isSort: false},
            { display: '标准价', name: 'sale_price',isSort: false},
            { display: '课时', name: 'course_hours',isSort: false},
            { display: '是否上架', name: 'is_shelves',isSort: false},
            { display: '授课老师', name: 'teacher_id',isSort: false},
        ]
    });
    
    $.ligerDialog.open({ target: $("#search_div_zgrid") ,width:700, height:700,
        title:'学员信息',
   /* buttons: [  { text: '保存', onclick: function (i, d) { getChecked(); }}, 
       { text: '关闭', onclick: function (i, d) { $("input").ligerHideTip(); d.hide(); }} 
   ]   */
    });
}

$(document).on('click','input[name="chk"]',function(){
	getChecked(this);
})

//全选
$(document).on('click','#allCheck',function(){
	var is_all = $(this).is(':checked');
	var arrUserIdName   = {};
	$(this).closest('#search_div_zgrid').find('input[name="chk"]').each(function(i,e){
		if(is_all){
			$(this).prop('checked',true);
		}else{
			$(this).prop('checked',false);
		}
		var userId   = $(this).val();
		var userName = $(this).closest('tr').find('td:eq(1)').find('div').html();
		var ks       = $(this).closest('tr').find('td:eq(7)').find('div').html();
		arrUserIdName[ userId ] = userName;
		arrKS[userId] = ks;
	})
	if(is_all){
	    addDiv(arrUserIdName);
	}else{
		delDiv(arrUserIdName);
	}
})

//单选操作
function getChecked(nowthis){ 
	var  userId   = $(nowthis).val();
	var  userName = $(nowthis).closest('tr').find('td:eq(1)').find('div').html();
	var  ks       = $(nowthis).closest('tr').find('td:eq(7)').find('div').html();
	     arrKS[userId] = ks;
	var  arrUserIdName ={};
	     arrUserIdName[userId] = userName;
	if( $(nowthis).is(':checked') ){
		addDiv(arrUserIdName)
	}else{
		delDiv(arrUserIdName);
	}
}

//添加学员
function addDiv(arrUserIdName){
	   var arrCheckedUserID  = allCheckedUserID();
	    var length           = arrCheckedUserID.length;
	    var div ='';
	    var j   = 0;
	    $.each(arrUserIdName,function(i,v){
		    if( $.inArray(i,arrCheckedUserID) == -1 || arrCheckedUserID.length == 0 ){
		    	j++;
			    div += '<div class="ddd" style="float:left;border:1px solid gray;width:auto;text-align:left;margin-right:2px;"><span class="num">'+(length+j)+'</span>、'+v+'<a  data-type="'+i+'" class="del_phone xxx" style="color:red;">X</a></div>';
		    	arrCheckedUserID.push(i);
		    }
	    })
	    userIds = arrCheckedUserID.join(',');
		$('#subject_ids').val(userIds);
    	$('.checkUserIds').append(div);
    	kcTime();
}

//删除
function delDiv(arrUserIdName){
	var arrCheckedUserID =  allCheckedUserID();
	$('.del_phone').each(function(i,e){
		  var id = $(this).attr('data-type');
		  if( arrUserIdName[id] ){
			  arrCheckedUserID.splice( $.inArray(id,arrCheckedUserID),1);
			  console.log(arrCheckedUserID);
			  $(e).closest('div').remove();
		  }
	})
	if(arrCheckedUserID.length>0){
		userIds =  arrCheckedUserID.join(',');
		$('#subject_ids').val(userIds);
	}else{
		userIds =  '';
		$('#subject_ids').val('');
	}
	sort();
	kcTime();
}

//删除
$(document).on('click','.del_phone',function(){
	var id = $(this).attr('data-type');
	var arrUserIds = userIds.split(',');
	arrUserIds.splice( $.inArray(id,arrUserIds),1);
	if(arrUserIds.length>0){
		userIds =  arrUserIds.join(',');
		$('#subject_ids').val(userIds);
	}else{
		userIds = '';
		$('#subject_ids').val('');
	}
	$(this).closest('div').remove();
	sort();
	kcTime();
})

//查找所有选中的ids
function allCheckedUserID(){
	var user = [];
        if(userIds){user = userIds.split(',');};
	return user;
}

//课程记时
function kcTime(){
	var user = allCheckedUserID();
	var ks   = 0;
	if(user){
		$(user).each(function(i,e){
			ks = parseInt(arrKS[e]) + ks;
		})
	}
	$('#course_hours').val(ks)
}


//重新排列x
function sort(){
	$('.num').each(function(i){
		$(this).html(i+1);
	})
	
}

//----------------------------------------------------------------------------

function getCheckedi(show_div,bg_div){
    var id_array = Array();
    $("input[name='chki']:checked").each(function(){
        id_array.push($(this).val());
    });
    $.post(MBIS.U('admin/course/getAdItemList'),{id:id_array},function(data){
        var json = MBIS.toAdminJson(data);
        if(json.status == 1){
            $('#it_ids').val(json.data);
            document.getElementById(show_div).style.display='none';
            document.getElementById(bg_div).style.display='none';
        }else{
            MBIS.msg(json.msg,{icon:2});  
        }
    });
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
