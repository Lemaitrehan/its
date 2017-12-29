var grid;
var combo;
var type,addOredit;//type 学历，技能 addOredit//编辑后者 
var userIds='';
var sendType='';//短信发送方式
var specialTag ='';//特殊过来标记
$(function(){
	$("#targetTime").ligerDateEditor();
})

/*//var sss ='【自考/网教/成考缴费通知】各位自考/网教/成考学员：自考/网教/成考{学员名称},第{xxxx年}缴费已开始，具体详情见邮箱通知，{学历李老师：xx650543x1}';
var s = "我的电话号码{0631-1234567}你的是{021-87654321}，我们常联系啊!";
reg = /(\{.*\})/;   // 注意这个正则可能不完整还可能有更简单的写法
var reString = s.match(reg);
console.log(reString);*/

function initGrid(type1){
	    type = type1;
		if( type == 1 ){
			url=MBIS.U('admin/Studentnoticelog/indexEducation');
		}else{
			url=MBIS.U('admin/Studentnoticelog/indexSkill');
		}
  		grid = $("#maingrid").ligerGrid({
  			url:url,
  			pageSize:MBIS.pageSize,
  			pageSizeOptions:MBIS.pageSizeOptions,
  			height:'99%',
  	        width:'100%',
  	        minColToggle:9,
  	        checkbox:false,
  	        rownumbers:true,
  	        columns: [
  	            { display: 'ID', name: 'id',width: 100,isSort: false,},
  	            { display: '通知模板', name: 'template_type',isSort: false},
  		        { display: '发送方式', name: 'type',isSort: false},
  		        { display: '通知主题', name: 'title',isSort: false},
  		        { display: '通知内容', name: 'content',isSort: false},
  		        { display: '审核状态', name: 'statusText',isSort: false},
  		        { display: '操作', name: 'op',width:100,isSort: false,
  		        	render: function (rowdata){
  			            var h = "";
  			          if(rowdata.status != '2'){
	  			            if( (MBIS.GRANT['CKTZ_03'] && type == 1) || (MBIS.GRANT['CKTZJN_03'] && type == 2)  ){
	  			            	h += "<a href='javascript:toEdit("+rowdata["id"]+")'>修改</a> ";
	  			            }	
	  			            if( (MBIS.GRANT['CKTZ_04'] && type == 1) || (MBIS.GRANT['CKTZJN_04'] && type == 2)  ){
	  			            	h += "<a href='javascript:toDel("+rowdata["id"]+")'>删除</a> ";
	  		        	    }
  			                if( rowdata.auditStatus  )
			            	h += "<a href='javascript:toAudit("+rowdata["id"]+")'>审核</a> ";
  			           }
			           return h;
  		        	}
  		        }
  	        ]
  	    });
  	}
//审核
function toAudit(id){
	var is_edu = type;
	var status = '';
	$.ligerDialog.confirm('是否审核通过', function (yes) {
		yes?status = 2:status = 1;
		if(is_edu== 1){
		   var url  =  MBIS.U('admin/Studentnoticelog/indexEducation');
		}else{
		   var url  =  MBIS.U('admin/Studentnoticelog/indexSkill');
		}
	    $.post(url,{action:'audit',id:id,status:status},function(data){
	  	 if(data.status == 1){
	  		  initGrid(is_edu)
	      }else{
	    	  MBIS.msg(json.msg,{icon:2});
	      }
	    });
	});
	
}

//获取选中的模板
function chooseTmpl(){ 
	$('.tem_bl').remove();
	$('#content').val('');
	var notice_id =  $('#notice_id option:selected').val();
	   if( type == 1 ){
		   var url  =  MBIS.U('admin/Studentnoticelog/addEducationList');
		}else{
		   var url  =  MBIS.U('admin/Studentnoticelog/addEditSkillList');
		}
          $.post(url,{action:'tmpl',notice_id:notice_id},function(data){
        	 if(data.status == 1){
            	if(data.send_type != '2'){
            		KindEditor.remove('textarea[name="content"]');
            	 	$('#content').val(data.content);
            	}else{
            		editText(data.content);
            	}
            	$('#title').val(data.title);
              	$('#send_type').text(data.type);
            	//输入模板变量 
            	tmpInput(data.content);
            }else{
	            if(data.status == -1 || data.status == 0){
	              	$('.ipt').val('');
	            }
            }
          });
         
          
}
function tmpInput(content){
	 var res = content.match(/\{[^\{\}]*\}/g);//查找标签
	 if(res.length>0){
		 var html ='';
		 $(res).each(function(i,e){
			 ee=e.substring(1,e.length-1);
			  //如果是学员 input 隐藏
			  var is_user ='',is_user_true='';
			  if( e == specialTag[1]){
				    is_user = ' hide';
				    is_user_true = true;
			  }
			  html += '<tr class="tem_bl'+is_user+'">'
				        +'<th width="150">'+ee+'<font color="red">*</font>:</th>'
				        +'<td><input type="text" class="ipt" name="smsText[]" value="'+is_user_true+'" ></td>'
				   +'</tr>';
		 })
		 $('.userTr').after(html);
	 }
}


function initCombo(type1,sendType1,specialTag1,userIds1){
	type       = type1;
	sendType   = sendType1;
	specialTag = specialTag1;
	userIds    = userIds1;
}


function editOrAdd(type1){
	addOredit = type1;
}

function loadGrid(type){
	if( type == 1 ){
		grid.set('url',MBIS.U('admin/Studentnoticelog/indexEducation','key='+$('#key').val()));
	}else{
		grid.set('url',MBIS.U('admin/Studentnoticelog/indexSkill','key='+$('#key').val()));
	}
	
}

function refresh(type){
	if($('#key').val() !== ''){
		$('#key').val('');
	}
  	if( type == 1 ){
		grid.set('url',MBIS.U('admin/Studentnoticelog/indexEducation'));
	}else{
		grid.set('url',MBIS.U('admin/Studentnoticelog/indexSkill'));
	}
}

//添加短信通知
function toAdd(type){
	if( type == 1 ){
		location.href=MBIS.U('admin/Studentnoticelog/addEducationList');
	}else{
		location.href=MBIS.U('admin/Studentnoticelog/addEditSkillList');
	}
}
var findUserUrl;

$(function(){
	if(addOredit == 1){
		if(type == 1){
		    findUserUrl = MBIS.U('admin/Studentnoticelog/addEducationList'); 
	    }else{
		    findUserUrl = MBIS.U('admin/Studentnoticelog/addEditSkillList'); 
	    }
	}else{
		if(type == 2){
		     findUserUrl = MBIS.U('admin/Studentnoticelog/toEditEducation'); 
	    }else{
	    	 findUserUrl = MBIS.U('admin/Studentnoticelog/toEditSkill'); 
	    }
		
	}
})
//查找学校
$('#school').change(function(){
	var school_id = $(this).val();
	var params    = {school_id:school_id};
	if(type==1){
		$('#major,#level_id,#grade_id').html('<option value="">请选择</option>');
	}else{
		$('#major,#subject_id').html('<option>请选择</option>');
	}
	
	$.post(findUserUrl,params,function(data,textStatus){
    	if(data){
    		var html = '<option value="">请选择</option>';
    		$.each(data,function(i,e){
    			html += '<option value="'+e.major_id+'">'+e.name+'</option>';
    		})
    		$('#major').html(html);
    	}else{
    		MBIS.msg('没有专业信息',{icon:2});
    	}
    });
})
//查找班级
$('#major').change(function(){
	var major_id = $(this).val();
	if(major_id<=0){return '';}
	var params = {major_id:major_id};
	$.post(findUserUrl,params,function(data,textStatus){
    	if(data){
    		//查找层级
    		if( type == 1){
    			$('#level_id,#grade_id').html('<option value="">请选择</option>');
	    		var html = '<option value="">请选择</option>';
	    		$.each(data,function(i,e){
	    			html += '<option value="'+i+'">'+e+'</option>';
	    		})
	    		$('#level_id').html(html);
	        //查找科目
    		}else{
    			$('#subject_id').html('<option value="">请选择</option>');
	    		var html = '<option value="">请选择</option>';
	    		console.log(data)
	    		$.each(data,function(i,e){
	    			html += '<option value="'+e.subject_id+'">'+e.name+'</option>';
	    		})
	    		$('#subject_id').html(html);
    		}
    	}else{
    		MBIS.msg('查不到数据',{icon:2});
    	}
    });
})
//搜索
/*function search(){
	if(type == 1){                               
		var url = MBIS.U('admin/Studentnoticelog/toEditEducation'); 
	}else{
        var url = MBIS.U('admin/Studentnoticelog/toEditSkill'); 
	}
	var query = MBIS.getParams('.query');
    grid.set('url',MBIS.U('admin/sjexams/pageQuery',query));
}*/

function toEdit(id){
	if( type == 1 ){
		var url=MBIS.U('admin/Studentnoticelog/toEditEducation','id='+id);
	}else{
		var url=MBIS.U('admin/Studentnoticelog/toEditSkill','id='+id);
	}
	 window.location.href = url;
}

function toDetail(id){
	location.href=MBIS.U('admin/Studentnoticelog/toDetail','id='+id);
}

//编辑
$(function(){
	if(sendType=='2'){
		editor1 = KindEditor.create('textarea[name="content"]', {
			height:'350px',
			allowFileManager : true,
			allowImageUpload : true,
			items:[
			'source', '|', 'undo', 'redo', '|', 'preview', 'print', 'template', 'code', 'cut', 'copy', 'paste',
			'plainpaste', 'wordpaste', '|', 'justifyleft', 'justifycenter', 'justifyright',
			'justifyfull', 'insertorderedlist', 'insertunorderedlist', 'indent', 'outdent', 'subscript',
			'superscript', 'clearhtml', 'quickformat', 'selectall', '|', 'fullscreen', '/',
			'formatblock', 'fontname', 'fontsize', '|', 'forecolor', 'hilitecolor', 'bold',
			'italic', 'underline', 'strikethrough', 'lineheight', 'removeformat', '|','image','table', 'hr', 'emoticons', 'baidumap', 'pagebreak',
			'anchor', 'link', 'unlink', '|', 'about'
					],
					afterBlur: function(){ this.sync(); }
		});
	}
})
//文本编辑器
function editText(content){
	
		editor1 = KindEditor.create('textarea[name="content"]', {
			height:'350px',
			allowFileManager : true,
			allowImageUpload : true,
			items:[
			'source', '|', 'undo', 'redo', '|', 'preview', 'print', 'template', 'code', 'cut', 'copy', 'paste',
			'plainpaste', 'wordpaste', '|', 'justifyleft', 'justifycenter', 'justifyright',
			'justifyfull', 'insertorderedlist', 'insertunorderedlist', 'indent', 'outdent', 'subscript',
			'superscript', 'clearhtml', 'quickformat', 'selectall', '|', 'fullscreen', '/',
			'formatblock', 'fontname', 'fontsize', '|', 'forecolor', 'hilitecolor', 'bold',
			'italic', 'underline', 'strikethrough', 'lineheight', 'removeformat', '|','image','table', 'hr', 'emoticons', 'baidumap', 'pagebreak',
			'anchor', 'link', 'unlink', '|', 'about'
					],
					afterBlur: function(){ this.sync(); }
		});
		editor1.insertHtml(content);
}

	
//form 提交
$("#departmentForm").submit(function(e){
	var sms_id = $('#sms_id').val();//模板id 编辑
    var params = $('#departmentForm').serializeArray();
    if( !( $('#notice_id').val()  &&  $('#content').val()  ) ){  
    	alert('模板id,模板内容,用户id不能为空！！！');
    	return false;
    };
    
    if(  !sms_id &&  !$('#userIds').val() ){
    	alert('模板id,模板内容,用户id不能为空！！！');
    	return false;
    }
    var is_bt = true;
    $('input[name="smsText[]"]').each(function(){
    	 if( !$(this).val() ){
    		 is_bt = false;
    	 }
    })
    if(!is_bt){
    	alert('模板替换内容不得为空！！！');
    	return false;
    }
    var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
    if(sms_id){
    	``
    	params.push( {'name':'sms_id',value:sms_id} );
    	 if(type == 1){
    		 var url = MBIS.U('admin/Studentnoticelog/toEditEducation');
    	 }else{
    		 var url = MBIS.U('admin/Studentnoticelog/toEditSkill');
    	 }
     }else{
    	 if(type == 1){
    		 var url = MBIS.U('admin/Studentnoticelog/addEducationList');
    	 }else{
    		 var url = MBIS.U('admin/Studentnoticelog/addEditSkillList');
    	 }
     }
   
    $.post(url,params,function(data,textStatus){
		  layer.close(loading);
		  var json = MBIS.toAdminJson(data);
		  if(json.status=='1'){
		    	MBIS.msg(json.msg,{icon:1});
		    	window.location.href = location.href;
		  }else{
		        MBIS.msg(json.msg,{icon:2});
		  }
	});
    return false;
  
})

function toDel(id){
	if( type == 1 ){
		var url=MBIS.U('admin/Studentnoticelog/delEducation','id='+id);
	}else{
		var url=MBIS.U('admin/Studentnoticelog/delSkill','id='+id);
	}
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

//enjn 1=>学历 2=》技能
function initGrid1(addOredit){
	    var query = MBIS.getParams('.query');
	    query['userIds'] = userIds;
	    if(addOredit == 1){
			if(type == 1){
				var url = MBIS.U('admin/Studentnoticelog/addEducationList',query)
			}else{
				var url = MBIS.U('admin/Studentnoticelog/addEditSkillList',query)
			}
	    }else{
	    	if(type == 1){
				var url = MBIS.U('admin/Studentnoticelog/toEditEducation',query)
			}else{
				var url = MBIS.U('admin/Studentnoticelog/toEditSkill',query)
			}
	    }
	    url = url+'&action=findUserS'
		grid = $("#search_div_z").ligerGrid({
			url:url,
			pageSize:MBIS.pageSize,
			pageSizeOptions:MBIS.pageSizeOptions,
			height:'100%',
	        width:'100%',
	        minColToggle:5,
	        checkbox:false,
	        rownumbers:true,
	        columns: [
	            { display: '全选<input type="checkbox" id="allCheck" class="isAllCheck">', name:'checkbox',width:100,isSort: false,},
	            { display: '学员姓名(编号)', name: 'trueName',width:100,isSort: false},
		        { display: '年级', name: 'grade_name',width:100,isSort: false},
		        { display: '专业', name: 'major_name',width:150,isSort: false},
		        { display: '院校', name: 'school_name',width:200,isSort: false},
	        ]
	    });
		 $.ligerDialog.open({ target: $("#target1") ,width:700, height:700,
			                  title:'学员信息',
			    /* buttons: [  { text: '保存', onclick: function (i, d) { getChecked(); }}, 
			                 { text: '关闭', onclick: function (i, d) { $("input").ligerHideTip(); d.hide(); }} 
			             ]   */
			                
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
		arrUserIdName[ userId ] = userName;
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
	var  arrUserIdName ={};
	     arrUserIdName[userId] = userName;
	if( $(nowthis).is(':checked') ){
		addDiv(arrUserIdName)
	}else{
		delDiv(arrUserIdName);
	}
	
	
}

function threeSms(arrU){
	var arrUserIdName = eval( '('+ arrU+ ')' );
	addDiv(arrUserIdName);
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
		$('#userIds').val(userIds);
    	$('.checkUserIds').append(div);
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
		$('#userIds').val(userIds);
	}else{
		userIds =  '';
		$('#userIds').val('');
	}
	sort();
}

//删除
$(document).on('click','.del_phone',function(){
	var id = $(this).attr('data-type');
	var arrUserIds = userIds.split(',');
	arrUserIds.splice( $.inArray(id,arrUserIds),1);
	if(arrUserIds.length>0){
		userIds =  arrUserIds.join(',');
		$('#userIds').val(userIds);
	}else{
		userIds = '';
		$('#userIds').val('');
	}
	$(this).closest('div').remove();
	sort();
})

//查找所有选中的ids
function allCheckedUserID(){
	var user = [];
        if(userIds){user = userIds.split(',');};
	return user;
}

//重新排列x
function sort(){
	$('.num').each(function(i){
		$(this).html(i+1);
	})
	
}