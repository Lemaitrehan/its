
var selectHtml = '';
function Initialize (arrExamsStatus){
	var obj = eval('(' + arrExamsStatus + ')');
    selectHtml += '<select  name="status" class="changeStatus"  style="width:60px;">';
	$.each(obj,function(i,e){
		selectHtml += '<option value="'+i+'">'+e+'</option>';
	})
    selectHtml += '<select>';
	selectHtml +=  '<button class="qrxg changeStatus" style="color:red;">确认</button>';
}

//查找学校下面的专业
function checkSchool(){
    var school_id = $('#school_id').val();
    var html = "";
    if(school_id == ''){
      $('#major_id').html(html);
      return false;
    }
    $.post(MBIS.U('admin/Examinationmanagement/indexEducation'),{school_id:school_id},function(data){
       if(data){
	        $.each(data,function(key,value){
	             html +="<option value="+value['major_id']+">"+value['name']+"</option>";
	        });
       } 
       $('#major_id').html(html);
    });   
}
/*function checkMajor(){
	 var major_id = $('#major_id').val();
	    var html = "";
	    if(major_id == ''){
	      $('#level_id').html(html);
	      $('#subject_id').html(html);
	      return false;
	    }
	    $.post(MBIS.U('admin/Examinationmanagement/indexEducation'),{major_id:major_id},function(data){
	        var html1 = html,html2= html ;
	    	if(data.subject){
		        $.each(data.subject,function(key,value){
		             html1 +="<option value="+value['subject_id']+">"+value['name']+"</option>";
		        });
	       }
	       if(data.level){
		        $.each(data.level,function(key,value){
		             html2 +="<option value="+key+">"+value+"</option>";
		        });
	       }
	       
	      // $('#subject_id').html(html1);
	       $('#level_id').html(html2);
	    }); 
	
}*/

//分页 is_data 报考数据分析
function initGrid(is_data){
	    var query = MBIS.getParams('.query');
	        query.action = 'fy';
	    if(is_data){
	    	var arrData = $('#target1').serializeArray();
	    	$.each(query,function(i,e){
	    		var obj = {name:i,value:e};
	    		arrData.push(obj);
	    	})
	    	query = arrData;
	    }
	    var url = MBIS.U('admin/Examinationmanagement/indexEducation',query);
	    $.post(url,query,function(data){
	    	listFy(data);
	    })
		
}
//导出
$('.daochu').click(function(){
	var query = MBIS.getParams('.query');
        query.action = 'fy';
		var arrData = $('#target1').serializeArray();
		$.each(arrData,function(i,e){
			query[e.name] = e.value;
		})
     var url = MBIS.U('admin/Examinationmanagement/export',query);
	 window.location.href = url;
})


function listFy(jsonObj){
		$json= 
		 [
	      { display: '序号', name:'userId',width:100,isSort: false,},
	      { display: '姓名', name: 'trueName',width:100,isSort: false},
	      { display: '学员编号', name: 'student_no',width:100,isSort: false},
	      { display: '身份证号', name: 'idcard',width:150,isSort: false},
	      { display: '准考证号', name: 'exam_no',width:200,isSort: false},
	      { display: '准考密码', name: 'exam_password',width:200,isSort: false},
	      //{ display: '报考时间', name: 'baokao_time',width:200,isSort: false},
	      { display: '审核状态', name: 'statusText',width:200,isSort: false},
	      { display: '报考院校', name: 'school_name',width:200,isSort: false},
	      { display: '报考专业', name: 'major_name',width:200,isSort: false},
	      { display: '年级', name: 'grade_name',width:200,isSort: false},
	    ];
	if(jsonObj.subjectList){
	$.each(jsonObj.subjectList,function(i,e){
		$json.push( { display: e.name, name:e.value,width:200,isSort: false} );
	})
	}
	$json.push(  { display: '操作', name: '',width:260,isSort: false,
						render: function (rowdata){
							//$.inArray( rowdata.bkAuditStatus,[] ) >-1
					            var h  = "";
							        h += '<a href="javascript:;" class="history" data-value="'+rowdata.id+'" >查看历史记录</a> ';
							     
							     if( rowdata.bkAuditStatus!= 2 ){
							    	 h += '<a href="javascript:;" class="edit">修改</a>  ';
							    	 h += '<a href="javascript:toDel('+rowdata.id+')">删除</a> ';
							     }
							     if( rowdata.bkAuditStatus == 0 ){
							         h += '<a href="javascript:auditStatus('+rowdata.id+',1)">审核不通过</a> ';
							     }
							     if( rowdata.bkAuditStatus != 2 ){
							         h += '<a href="javascript:auditStatus('+rowdata.id+',2)">审核通过</a> ';
							     }
							    return h;
						 }
		
	    }
	
	);
	    
	grid =  $("#maingrid").ligerGrid({
		//pageSize:MBIS.pageSize,
		//pageSizeOptions:MBIS.pageSizeOptions,
		userPager :false,
		height:'100%',
        width:'100%',
        minColToggle:5,
       // checkbox:false,
        rownumbers:true,
        data: jsonObj,
        usePager:true,
        columns:$json
    });
    }

//高级筛选
function dataAnalysis(){
	 $.ligerDialog.open({ target: $("#target1") ,width:700, height:500,
         title:'报考数据分析',
	     buttons: [  { text: '查询', onclick: function (i, d) { initGrid(1); }}, 
	        { text: '关闭', onclick: function (i, d) { $("input").ligerHideTip(); d.hide(); }} 
	     ]   
       
    });
	
}


//审核
function auditStatus(id,status){
	var url = MBIS.U('admin/Examinationmanagement/indexEducation');
	$.post(url,{action:'auditStatus',id:id,status:status},function(data,textStatus){
		var json = MBIS.toAdminJson(data);
		  if(json.status=='1'){
			  initGrid();
		  }else{
			  MBIS.msg(json.msg,{icon:2}); 
		  }
    });
}

//查看详情
$(document).on('click','.history',function(){
	var nowthis = this;
	if( $(this).closest('tr').next().is('.history') ){
		if( $(this).closest('tr').next().is(':visible ') ){
			$(this).closest('tr').next().hide();
		}else{
			$(this).closest('tr').next().show();
		}
	}else{
		var id  = $(this).attr('data-value');
		var url = MBIS.U('admin/Examinationmanagement/getHistory');
		$.post(url,{id:id},function(data,textStatus){
			  var json = MBIS.toAdminJson(data);
			  if(json.status=='1'){
				  var html='';
				  if(json.data){
					  $(json.data).each(function(i,e){
						  html += '<tr class="history'+id+'" style="border:1px solid red;">'
						  $(e).each(function(i,v){
							  html += '<td style="border:1px solid gray;text-align:center;" >'+v+'</td>';
						  })
						  html += '</tr>';
					  })
					
				  }
			    $(nowthis).closest('tr').after(html);
			    
			    //
			    var arr = [];
			    $(nowthis).closest('tr').find('td').not(':first').not(':last').each(function(iii){
	    			  var obj = $(this).find('div');
				    	if( obj.find('.km').length>0 ){
				    		var text = $(this).find('.km').html();
				    	}else{
				    		var text = $(this).find('div').html();
				    	}
				    	arr[iii] = text;
				 })
			    
			    //区分颜色
			    $(".history"+id).each(function(i,e){
			    	
			    	$(this).find('td').not(':first').not(':last').each(function(ii,ee){
			    		 var tdNum   = ii+1;
			    		 var tdHtml  = $(ee).html();
			    		 if( arr[ii] != tdHtml ){
			    			 $(ee).attr('style','border:1px solid  red;');
			    		 }
			    	})
				  
			    })
			  }else{
			    	MBIS.msg(json.msg,{icon:2});
			  }
	    });
	}
})
//编辑按钮
$(document).on('click','.edit',function(){
	
	$(this).closest('tr').find('.km').each(function(i,e){
		var selectObj = $(e).closest('td').find('.changeStatus');
		 if(!selectObj.html()){
			 var value = $(e).attr('data-value');
			 $(e).after(selectHtml);
			 $(e).closest('td').find('select').val(value);
		 }
		 if( $(e).is(':hidden')  ){
			 $(e).show();
			 selectObj.hide();
		 }else{
			 $(e).hide();
			 selectObj.show();
		 }
	})
})

//确认编辑按钮
$(document).on('click','.qrxg',function(){
	var obj   = $(this).closest('td').find('.km');
	var id    = obj.attr('id');
	var status = obj.closest('td').find('select').val();
	var url = MBIS.U('admin/Examinationmanagement/edit');
	$.post(url,{id:id,status:status},function(data,textStatus){
			  var json = MBIS.toAdminJson(data);
			  if(json.status=='1'){
			    	MBIS.msg(json.msg,{icon:1});
			    	layer.close(box);
		            grid.reload();
		            obj.attr('data-value',status);
			  }else{
			    	MBIS.msg(json.msg,{icon:2});
			  }
	 });
})

function toDel(id){
	var url=MBIS.U('admin/Examinationmanagement/del','id='+id);
	var box = MBIS.confirm({content:"您确定要删除该数据吗?",yes:function(){
	           var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
	           	$.post(url,{id:id},function(data,textStatus){
	           			  layer.close(loading);
	           			  var json = MBIS.toAdminJson(data);
	           			  if(json.status=='1'){
	           			    	MBIS.msg(json.msg,{icon:1});
	           			    	layer.close(box);
	           			    	initGrid();
	           			  }else{
	           			    	MBIS.msg(json.msg,{icon:2});
	           			  }
	           		});
	            }});
}

//手动添加页面
$(document).on('click','.addSD',function(){
	var url = $(this).attr('href');
	window.location.href = url;
})


//发送通知
function sendSms(){
	  var sel_data = grid.getSelectedRows();
	 
	    if(sel_data.length==0)  {MBIS.msg('请选择数据',{icon:2});return false};
	    var ids = '';
	    var is_xl_jn = 1;
	    for(i in sel_data)
	    {   
	    	is_xl_jn =  sel_data[i].skill_id?2:1;
	    	sel_data[i].userId;//学员id 
	    	sel_data[i].trueName;//学员id 
	    	sel_data[i].student_no;//学员id 
	        ids +=  sel_data[i].userId+'--'+sel_data[i].trueName+'('+sel_data[i].student_no+'),';   
	    }
	    if (ids.length > 0) {
	    	ids = ids.substr(0, ids.length - 1);
	    }
	    if(is_xl_jn){
		    window.location.href = MBIS.U('admin/Studentnoticelog/addEducationList','ids='+ids);
	    }else{
		    window.location.href = MBIS.U('admin/Studentnoticelog/addEditSkillList','ids='+ids);
	    }
	
}
