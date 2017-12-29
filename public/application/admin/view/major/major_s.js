var grid;
var combo;
var type;
var subjectIds1='';
var subjectIds2='';
var subjectIds3='';
var tag = 1;
var type_id = 1;

function int(school_ids1,subject_ids1_1,subject_ids2_2){
	subjectIds1 = school_ids1;
	subjectIds2 = subject_ids1_1;
	subjectIds3 = subject_ids2_2;
}

function initGrid(type_id1){
	type_id = type_id1;
    var grid_config = {
		url:MBIS.U('admin/course/pageQuery','type_id='+type_id),
		pageSize:MBIS.pageSize,
		pageSizeOptions:MBIS.pageSizeOptions,
		height:'99%',
        width:'100%',
        minColToggle:6,
        checkbox:false,
        rownumbers:true,
        columns: [
            { display: '课程名称', name: 'name',isSort: false},
            { display: '课程编号', name: 'course_bn',isSort: false},
            { display: '课程总课时', name: 'course_hours',isSort: false},
            { display: '专业名称', name: 'major_id',isSort: false},
            { display: '学院名称', name: 'school_id',isSort: false},
            { display: '原价', name: 'market_price',isSort: false},
            { display: '标准价', name: 'offers_price',isSort: false},
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

function initCombo(type_id){
    type = type_id;
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
    var params = $('#addForm').serialize();
    params.id = id;
    var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
	$.post(MBIS.U('admin/major/getFormInfo'),params,function(data,textStatus){
		  layer.close(loading);
		  var json = MBIS.toAdminJson(data);
		  if(json.status=='1'){
		    	MBIS.msg(json.msg,{icon:1});
		    	location.href=MBIS.U('admin/course/index','type_id='+type_id);
		  }else{
		        MBIS.msg(json.msg,{icon:2});
		  }
	});
}

function toDel(type_id,id){
	var box = MBIS.confirm({content:"您确定要删除该数据吗?",yes:function(){
	           var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
	           	$.post(MBIS.U('admin/Major/delEducation'),{type_id:type_id,id:id},function(data,textStatus){
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
function getMajorLists(type_id,school_id,level_type,major_id)
{
    var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
    $.post(MBIS.U('admin/course/get_major_list'),{type_id:type_id,school_id:school_id,level_type:level_type,major_id:major_id},function(data,textStatus){
          layer.close(loading);
          var json = MBIS.toAdminJson(data);
          if(json.status=='1'){
                $('#major_grade').html('');
                $('#school_major').html(json.html);
          }else{
                MBIS.msg(json.msg,{icon:2});
          }
    });
}

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
                $('#tab_subject').append("<tr id='ts_"+v.subject_id+"' class='str'><td><input id='ck_"+v.subject_id+"' type='checkbox' name='chk' value='"+v.subject_id+"'></td><td>"+v.subject_id+"</td><td>"+v.name+"</td><td>"+v.subject_no+"</td><td>"+v.school_id+tag+"</td><td>"+v.major_id+"</td><td>"+v.market_price+"</td><td>"+v.sale_price+"</td><td>"+v.course_hours+"</td><td>"+v.is_shelves+"</td><td>"+v.teacher_id+"</td></tr>");
                if($.inArray(v.subject_id,v.subject_ids) !== -1){
                    $('#ck_'+v.subject_id).attr('checked',true);
                }
            });
        }else{
            MBIS.msg(json.msg,{icon:2});
        }
    });
}

function initGrid1(type){
	  if(type == 1){
			   var url = MBIS.U('admin/major/toAdd')+'?action=getSchool&school_ids='+subjectIds1;
			       tag = 1;
		       grid = $("#search_div_z1").ligerGrid({
		           url:url,
		           pageSize:MBIS.pageSize,
		           pageSizeOptions:MBIS.pageSizeOptions,
		           height:'99%',
		           width:'99%',
		           minColToggle:5,
		           checkbox:false,
		           rownumbers:true,
		           columns: [
		               { display: '全选<input type="checkbox" id="allCheck" class="isAllCheck">', name:'checkbox',width:60,isSort: false,},
		               { display: '学院名称', name: 'school_name',width:140,isSort: false},
		           ]
		       });
		
		       $.ligerDialog.open({ target: $("#target1") ,title:'学院列表',width:'100%', height:'100%',cls:"closeCls"
		               , /*buttons: [  { text: '保存', onclick: function (i, d) { getChecked(); }}, 
		                            { text: '关闭', onclick: function (i, d) { $("input").ligerHideTip(); d.hide(); }} 
		                        ]  */           
		       });
	  }
      //查找科目
	  else if( type == 2 || type == 3 ){
		  console.log(type)
		  if(type ==  2){
			  tag = 2;
		      var url = MBIS.U('admin/major/toAdd')+'?action=getSubjectList&subjectIds='+subjectIds2;
		  }
		  if( type == 3){
			  tag = 3;
			  var url = MBIS.U('admin/major/toAdd')+'?action=getSubjectList&subjectIds='+subjectIds3;
		  }  
        grid = $("#search_div_z2").ligerGrid({
            url:url,
            pageSize:MBIS.pageSize,
            pageSizeOptions:MBIS.pageSizeOptions,
            height:'99%',
            width:'99%',
            minColToggle:5,
            checkbox:false,
            rownumbers:true,
            columns: [
                { display: '全选<input type="checkbox" id="allCheck" class="isAllCheck">', name:'checkbox',width:60,isSort: false,},
                { display: '科目名称', name: 'name',width:140,isSort: false},
            ]
        });

        $.ligerDialog.open({ target: $("#target2") ,title:'科目列表',width:'100%', height:'100%',cls:"closeCls"
                , buttons: [  { text: '保存', onclick: function (i, d) { getChecked(); }}, 
                             { text: '关闭', onclick: function (i, d) { $("input").ligerHideTip(); d.hide(); }} 
                         ]             
        });
    }
    //全选 的判断     var is_all_checked = true;
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
    var tt = tag;
    if(tt == 3){
    	 tt = 2;
    }
    $(this).closest('#search_div_z'+tt).find('input[name="chk"]').each(function(i,e){
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


//单选操作
function getCheckedOne(nowthis){
    var  subject_id   = $(nowthis).val();
    var  subject_name = $(nowthis).closest('tr').find('td:eq(1)').find('div').html();
    var  subjectList ={};
         subjectList[subject_id] = subject_name;
    if( $(nowthis).is(':checked') ){
        addDiv(subjectList)
    }else{
        delDiv(subjectList);
    }
}

//添加选中数据到下面列表
function addDiv(arrUserIdName){
        var arrCheckedUserID  = allCheckedUserID();
        var length           = arrCheckedUserID.length;
        var div ='';
        var j   = 0;
        $.each(arrUserIdName,function(i,v){
            if( $.inArray(i,arrCheckedUserID) == -1 || arrCheckedUserID.length == 0 ){
                j++;
                div += '<div class="ddd" style="float:left;border:1px solid gray;width:auto;text-align:left;margin-right:2px;"><span class="num_'+tag+'">'+(length+j)+'</span>、'+v+'<a  data-type="'+i+'" class="del_phone del_phone'+tag+' xxx" style="color:red;">✘</a></div>';
                arrCheckedUserID.push(i);
            }
        })
        if(tag == 1){
        	subjectIds1 = subjectIds1 = arrCheckedUserID.join(',');
            $('#subject_ids'+tag).val(subjectIds1);
        }else if(tag == 2){
        	subjectIds2 = arrCheckedUserID.join(',');
            $('#subject_ids'+tag).val(subjectIds2);
        }else{
        	subjectIds3 = arrCheckedUserID.join(',');
            $('#subject_ids'+tag).val(subjectIds3);
        }
        $('.checkSubjectIds'+tag).append(div);
}

//删除
function delDiv(arrUserIdName){
    var arrCheckedUserID =  allCheckedUserID();
    $('.del_phone'+tag).each(function(i,e){
          var subject_id = $(this).attr('data-type');
          if( arrUserIdName[subject_id] ){
              arrCheckedUserID.splice( $.inArray(subject_id,arrCheckedUserID),1);
              $(e).closest('div').remove();
          }
    })
    if(arrCheckedUserID.length>0){
    	if(tag ==  1){
    		 subjectIds1 =  arrCheckedUserID.join(',');
    		 $('#subject_ids'+tag).val(subjectIds1);
    	}else if( tag ==  2){
    		 subjectIds2 =  arrCheckedUserID.join(',');
    		 $('#subject_ids'+tag).val(subjectIds2);
    	}else{
    		subjectIds3 =  arrCheckedUserID.join(',');
   		    $('#subject_ids'+tag).val(subjectIds3);
    	}
      
    }else{
    	if(tag ==  1){
    	   subjectIds1 =  '';
    	   $('#subject_ids'+tag).val(subjectIds1);
    	}else if( tag ==  2 ){
    	   subjectIds2 =  '';
    	   $('#subject_ids'+tag).val(subjectIds2);
    	}else{
    		subjectIds3 =  '';
     	   $('#subject_ids'+tag).val(subjectIds3);
    	}
      
    }
    sort(tag);
}

//删除
$(document).on('click','.del_phone',function(){
	tag  =  $(this).closest('.divText').attr('tag');
    var subject_id = $(this).attr('data-type');
    if(tag == 1){
       var arrSubjectIds = subjectIds1.split(',');
    }else if( tag == 2 ){
       var arrSubjectIds = subjectIds2.split(',');
    }else{
       var arrSubjectIds = subjectIds3.split(',');
    }
    arrSubjectIds.splice( $.inArray(subject_id,arrSubjectIds),1);
    if(arrSubjectIds.length>0){
    	if(tag == 1){
    	    subjectIds1 =  arrSubjectIds.join(',');
    	    $('#subject_ids1').val(subjectIds1);
    	}else if(tag == 2){
    	    subjectIds2 =  arrSubjectIds.join(',');
    	    $('#subject_ids2').val(subjectIds2);
    	}else{
    		subjectIds3 =  arrSubjectIds.join(',');
    	    $('#subject_ids3').val(subjectIds3);
    	}
      
    }else{
    	if(tag == 1){
    	   subjectIds1 = '';
    	   $('#subject_ids1').val('');
    	}else if(tag == 2){
    	   subjectIds2 = '';
    	   $('#subject_ids2').val('');
    	}else{
    	   subjectIds3 = '';
    	   $('#subject_ids3').val('');
    	}
    }
    $(this).closest('div').remove();
    sort(tag);
})

//查找所有选中的ids
function allCheckedUserID(){
        if(tag == 1){
         var subject = [];
          if(subjectIds1){subject = subjectIds1.split(',');};
          return subject;
        }else if(tag == 2){
          var subject = [];
          if(subjectIds2){subject = subjectIds2.split(',');};
          return subject;
        }else{
          var subject = [];
          if(subjectIds3){subject = subjectIds3.split(',');};
          return subject;
        }
}

//重新排列x
function sort(tag){
    $('.num_'+tag).each(function(i){
        $(this).html(i+1);
    })
    
}

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

//清除 主页数据
/*$('#level').click(function(){
	if( !$(this).is(':checked') ){
		$(this).closest('td').find('input').val('');
	}
})*/

function toSave(){
     var params = $('#addForm').serialize();
     var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
     
     if( $('#major_id').val() >0 ){
    	 var url     = MBIS.U('admin/major/toEditEducation');
     }else{
    	 var url     = MBIS.U('admin/major/toAdd');
     }
	$.post(url,params,function(data,textStatus){
		  layer.close(loading);
		  var json = MBIS.toAdminJson(data);
		  if(json.status=='1'){
		    	MBIS.msg(json.msg,{icon:1});
		    	location.href=MBIS.U('admin/major/index','type_id='+type_id);
		  }else{
		        MBIS.msg(json.msg,{icon:2});
		  }
	});
 }

