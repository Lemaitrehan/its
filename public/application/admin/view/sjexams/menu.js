
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

//分页
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
	    var url = MBIS.U('admin/Sjexams/indexEducation',query);
	    $.post(url,query,function(data){
	    	listFy(data);
	    })
		
}
//导出
$('.daochu1').click(function(){
	
	var query = MBIS.getParams('#exportForm .query');
        query.action = 'fy';
    var url = MBIS.U('admin/Sjexams/export',query);
    window.location.href = 	url;
})

//导出
$('.daochu').click(function(){
	var query = MBIS.getParams('.query');
        query.action = 'fy';
    var arrData = $('#target1').serializeArray();
	$.each(arrData,function(i,e){
		query[e.name] = e.value;
	})
    var url = MBIS.U('admin/Sjexams/export',query);
    window.location.href = 	url;
})

function listFy(jsonObj){
		$json= 
		 [
	      { display: '序号', name:'userId',width:100,isSort: false,},
	      { display: '姓名', name: 'trueName',width:100,isSort: false},
	      { display: '学员编号', name: 'student_no',width:100,isSort: false},
	      { display: '身份证号', name: 'idcard',width:150,isSort: false},
	      { display: '准考证号', name: 'exam_no',width:200,isSort: false},
	      { display: '报考专业', name: 'major_name',width:200,isSort: false},
	      { display: '是否欠费', name: '',width:200,isSort: false},
	      { display: '考试通过', name: 'passText',width:200,isSort: false},
	      { display: '毕业状态', name: 'statusText',width:200,isSort: false},
	      { display: '审核状态', name: 'auditText',width:200,isSort: false},
	      { display: '报考院校', name: 'school_name',width:200,isSort: false},
	      { display: '年级', name: 'grade_name',width:200,isSort: false},
	    ];
	if(jsonObj.subjectList){
	$.each(jsonObj.subjectList,function(i,e){
		$json.push( { display: e.name, name:e.value,width:300,isSort: false} );
	})
	}
	$json.push(  { display: '操作', name: '',width:200,isSort: false,
						render: function (rowdata){
					            var h = "";
							         //   if( (MBIS.GRANT['CKTZ_03'] && type == 1) || (MBIS.GRANT['CKTZJN_03'] && type == 2)  ){
							   
							         
							     if( rowdata.auditStatus != 2 ){
							    	 h += '<a href="javascript:;" class="edit">修改</a>  ';
							    	 h += '<a href="javascript:auditStatus('+rowdata.id+',1)">审核不通过</a> ';
							         h += '<a href="javascript:auditStatus('+rowdata.id+',2)">审核通过</a> ';
							     }
/*							     h += '<a href="javascript:toDel('+rowdata.id+')">删除</a>';
*/							    return h;
						 }
		
	    }
	
	);
	$('#maingrid').remove();
	$('.nextTable').after('<div id="maingrid"></div>');
	grid =  $("#maingrid").ligerGrid({
		pageSize:MBIS.pageSize,
		pageSizeOptions:MBIS.pageSizeOptions,
		height:'100%',
        width:'100%',
        minColToggle:5,
        rownumbers:true,
        data: jsonObj,
        columns:$json
    });
    }

//审核
function auditStatus(id,status){
	var url = MBIS.U('admin/Sjexams/indexEducation');
	$.post(url,{action:'audit',id:id,status:status},function(data){
		 if(data.status > 0 ){
			 var msg = data.msg,status= 1;
		 }else{
			 var msg = data.msg,status= 0;
		 }
		 MBIS.msg(msg,{icon:status});
		 initGrid(0);
	})
}

//高级筛选
function dataAnalysis(){
	 $.ligerDialog.open({ target: $("#target1") ,width:700, height:500,
         title:'成绩数据分析',
	     buttons: [  { text: '查询', onclick: function (i, d) { initGrid(1); }}, 
	        { text: '关闭', onclick: function (i, d) { $("input").ligerHideTip(); d.hide(); }} 
	     ]   
       
    });
}

function getExamsHistory(id){
	var url = MBIS.U('admin/Sjexams/indexEducation');
	$.post(url,{exams_subject_id:id},function(data){
			  if(data){
				 var arrUser     = data.userInfo;//学员信息
				 var arrSubject  = data.subject;//科目信息
				 console.log(arrSubject)
				 var userInfo = '<table>'
				    	             +'<tr><th>学员信息:</th></tr>'
				    	             +'<tr>'
					    	             +'<th>姓名:</th>'
					    	             +'<td>'+arrUser.trueName+'</td>'
				    	             +'</tr>'
				    	             +'<tr>'
					    	             +'<th>身份证号:</th>'
					    	             +'<td>'+arrUser.idcard+'</td>'
				    	             +'</tr>'
				    	             +'<tr>'
					    	             +'<th>准考证号:</th>'
					    	             +'<td>'+arrUser.exam_no+'</td>'
				    	             +'</tr>'
				    	       + '</table>';
				     $.each(arrSubject,function(i,e){
					    	  userInfo += '<table>'
							    	             +'<tr><th>第'+(i+1)+'次考试</th></tr>'
							    	             +'<tr>'
								    	             +'<th>科目考试类型:</th>'
								    	             +'<td>'+e.exam_method_text+'</td>'
							    	             +'</tr>'
							    	             +'<tr>'
								    	             +'<th>成绩:</th>'
								    	             +'<td>'+e.subject_score+'</td>'
							    	             +'</tr>';
					    	  if(e.zp){
					    		  $(e.zp).each(function(ii,vv){
					    	           userInfo  +='<tr>'
								    	             +'<th>作品:</th>'
								    	             +'<td><img src="/'+vv+'"/></td>'
						                            '</tr>'; 
					    		  })
					    	  }			    	 
					    	  userInfo +=   '</table>';
				     })
				   
				     $("#target1").html(userInfo);
			       	 $.ligerDialog.open({ target: $("#target1") ,width:700, height:800,
		                  title:'考试记录',
						     buttons: [  { text: '保存', onclick: function (i, d) { getChecked(); }}, 
						                 { text: '关闭', onclick: function (i, d) { $("input").ligerHideTip(); d.hide(); }} 
						             ]   
						                
					});
			  }else{
			    	MBIS.msg('无数据',{icon:2});
			  }
	 });
}

//手动添加页面
$(document).on('click','.addSD',function(){
	var url = $(this).attr('href');
	window.location.href = url;
})

//编辑按钮
$(document).on('click','.edit',function(){
	$(this).closest('tr').find('.km').each(function(i,e){
		//
		var type  = $(e).attr('data-type');
		var value = $(e).attr('data-value');
		var is_html_obj;//加载哪个对象
		//理论考试成绩
		if(type == 1){
			var inputObj = $(e).closest('td').find('.input');
			var inputText  = '<input type="text" class="input" name="subject_score" value="'+value+'">';
			if(!inputObj.is('.input')){
				 $(e).after(inputText);
			 }
			is_html_obj = inputObj;
		//实践考试成绩
		}else if(type = 2){
			//select 对象
			var selectObj = $(e).closest('td').find('.changeStatus');
			 if(!selectObj.html()){
				 $(e).after(selectHtml);
				 $(e).closest('td').find('select').val(value);
			 }
			 is_html_obj = selectObj;
		}
		
		if( $(e).is(':hidden')  ){
			 $(e).show();
			 is_html_obj.hide();
		 }else{
			 $(e).hide();
			 is_html_obj.show();
		 }
		 
	})
})

$(document).on('keydown','input[name="subject_score"]',function(event){
	if(event.keyCode == 13){
		var obj   = $(this).closest('td').find('.km');
		var id    = obj.attr('id');
		var subject_score = obj.closest('td').find('input').val();
		var url = MBIS.U('admin/Sjexams/edit');
		$.post(url,{id:id,subject_score:subject_score},function(data,textStatus){
				  var json = MBIS.toAdminJson(data);
				  if(json.status=='1'){
			            obj.attr('data-value',subject_score);
			            MBIS.msg(json.msg,{icon:1});
				  }else{
				    	MBIS.msg(json.msg,{icon:2});
				  }
		 });
	}
	
})
//确认编辑按钮
$(document).on('click','.qrxg',function(){
	var obj   = $(this).closest('td').find('.km');
	var id    = obj.attr('id');
	var exam_status = obj.closest('td').find('select').val();
	var url = MBIS.U('admin/Sjexams/edit');
	$.post(url,{id:id,exam_status:exam_status},function(data,textStatus){
			  var json = MBIS.toAdminJson(data);
			  if(json.status=='1'){
		            obj.attr('data-value',exam_status);
		            MBIS.msg('修改成功',{icon:1});
			  }else{
			    	MBIS.msg(json.msg,{icon:2});
			  }
	 });
})

function toDel(id){
	var url=MBIS.U('admin/Sjexams/del','id='+id);
	var box = MBIS.confirm({content:"您确定要删除该数据吗?",yes:function(){
	           var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
	           	$.post(url,{id:id},function(data,textStatus){
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



