var grid;
var combo;
var userIds='';
var sendType='';//短信发送方式
var specialTag ='';//特殊过来标记
$(function(){
	$("#targetTime1").ligerDateEditor();
	$("#targetTime2").ligerDateEditor();
})

//垃圾桶
var is_trash   = 0;//0=》正常 1=》垃圾
var is_send_re = 1;//1=》收件 2=》发送 
function trash(){
	is_send_re = 0;
	if(is_trash==0){
		is_trash = 1;
		$('.trash').removeClass('btn-red').html('换回任务列表');
	}else{
		is_trash = 0;
		$('.trash').addClass('btn-red').html('垃圾桶');
	}
	initGrid();
}

function sendReSms(is_send_re1,nowthis){
	is_trash   = 0;
	is_send_re = is_send_re1;
	$('.mail').removeClass('btn-blue');
	$(nowthis).addClass('btn-blue');
	initGrid();
}

function initGrid(){
	    var url  = trashX();
	    var name = is_send_re==1?'发送人':'接收人';
  		grid = $("#maingrid").ligerGrid({
  			url:url,
  			pageSize:MBIS.pageSize,
  			pageSizeOptions:MBIS.pageSizeOptions,
  			height:'99%',
  	        width:'100%',
			checkbox:false,
  	        minColToggle:9,
  	        checkbox:false,
  	        rownumbers:true,
  	        columns: [
  	            { display: 'ID', name: 'id',width: 100,isSort: false,},
  	            { display: '任务名称', name: 'name',isSort: false},
  	            { display: '任务审核人', name: 'auditorName',isSort: false},
  		        { display: '状态', name: 'statusText',isSort: false},
  		        { display: '开始时间', name: 'start_time',isSort: false},
  		        { display: '结束时间', name: 'stop_time',isSort: false},
  		        { display: '创建日期', name: 'create_person_time',isSort: false},
  		        { display: name, name: 'trueName',isSort: false},
  		        { display: '操作', name: 'op',width:100,isSort: false,
  		        	render: function (rowdata){
  			            var h = "";
  			               /* if(rowdata["trash"]==0){
  			            	h += "<a href='javascript:toDel("+rowdata["id"]+")'>删除</a> ";
  			                }*/
  			                if(is_send_re==1 && ( rowdata['status'] == 0 ) ){
  			            	   h += "<a href='javascript:complete("+rowdata["id"]+",1)'>接收</a> ";
		                    }
  			            	
  			            	if(is_send_re==3 && rowdata['status'] !=2){
  	  			            	h += "<a href='javascript:complete("+rowdata["id"]+",2)'>完成</a> ";
  			                }
  			            return h;
  		        	}
  		        }
  	        ]
  	    });
  	}

//查询
function loadGrid(){
   var url=trashX()+'&status='+$('#status').val();
   grid.set('url',url);
}
//刷新
function refresh(type){
	if($('#status').val() !== ''){
		$('#status').val('');
	}
	 var url = trashX();
	grid.set('url',url);

}

//垃圾 与  收件箱 发件箱 的变量切换
function trashX(){
	if(is_trash == 1){
		is_send_re = 0;
		return  url=MBIS.U('admin/Taskmessage/index')+'?is_trash='+is_trash;

	}else if(is_send_re){
		is_trash == 0;
		return  url=MBIS.U('admin/Taskmessage/index')+'?is_send_re='+is_send_re;

	}else{
		return  url=MBIS.U('admin/Taskmessage/index')+'?is_trash= 0';
	}
}

//添加短信通知
function toAdd(type){
		location.href=MBIS.U('admin/Taskmessage/addTask');
}

//完成
function complete(id,status){
  	var url = MBIS.U('admin/Taskmessage/complete');
    $.post(url,{id:id,status:status},function(data,textStatus){
		  var json = MBIS.toAdminJson(data);
		  if(json.status=='1'){
		    	MBIS.msg(json.msg,{icon:1});
		    	window.location.href = MBIS.U('admin/Taskmessage/index');
		  }else{
		        MBIS.msg(json.msg,{icon:2});
		  }
	});
}
//查找学校
$('#department').change(function(){
	var department_id = $(this).val();
	var params        = {action:'employee_type_id',department_id:department_id};
	var findUserUrl = MBIS.U('admin/Taskmessage/addTask');
	$.post(findUserUrl,params,function(data){
		var html = '<option value="">请选择</option>';
    	if(data){
    		$.each(data,function(i,e){
    			html += '<option value="'+e.employee_type_id+'">'+e.name+'</option>';
    		})
    		$('#employee_type').html(html);
    	}else{
    		$('#employee_type').html(html);
    	}
    });
})



function toDetail(id){
	location.href=MBIS.U('admin/Studentnoticelog/toDetail','id='+id);
}

	
//form 提交
$("#departmentForm").submit(function(e){
    var params = $('#departmentForm').serializeArray();
    if( !( $('input[name="name"]').val()   && $('textarea[name="content"]').val() ) ){  
    	alert('任务名称，截止时间，任务内容必填！！！');
    	return false;
    };
    
    if( !$('#userIds').val() ){
    	alert('学员id不能为空！！！');
    	return false;
    }
   
    var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
    	params.push( {'name':'action',value:'addData'} );
    	var url = MBIS.U('admin/Taskmessage/addTask');
	    $.post(url,params,function(data,textStatus){
			  layer.close(loading);
			  var json = MBIS.toAdminJson(data);
			  if(json.status=='1'){
			    	MBIS.msg(json.msg,{icon:1});
			    	window.location.href = MBIS.U('admin/Taskmessage/index');
			  }else{
			        MBIS.msg(json.msg,{icon:2});
			  }
		});
    return false;
  
})

function toDel(id){
	
	var url=MBIS.U('admin/Taskmessage/del')+'?id='+is_trash;
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
function initGrid1(){
	    var query = MBIS.getParams('.query');
	    query['userIds'] = userIds;
	    query['action']  = 'user';
	var url = MBIS.U('admin/Taskmessage/addTask',query)
		
		grid = $("#search_div_z").ligerGrid({
			url:url,
			pageSize:MBIS.pageSize,
			pageSizeOptions:MBIS.pageSizeOptions,
			height:'100%',
	        width:'100%',
	        minColToggle:5,
	        rownumbers:true,
	        columns: [
	            { display: '全选<input type="checkbox" id="allCheck" class="isAllCheck">', name:'checkbox',width:100,isSort: false,},
		        { display: '姓名', name: 'name',width:100,isSort: false},
	            { display: '员工编号', name: 'employee_no',width:100,isSort: false},
		        { display: '手机号码', name: 'mobile',width:200,isSort: false},
		        { display: '部门', name: 'bm_name',width:200,isSort: false},
		        { display: '岗位', name: 'gw_name',width:200,isSort: false},
	        ]
	    });
		 $.ligerDialog.open({ target: $("#target1") ,width:700, height:800,
			                  title:'选择学员对象',
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

$(function(){

	function select2(nowthis){
	   //年级搜索
	    function formatRepo1(repo) {
	        return repo.name;
	    }
	    function formatRepoProvince1(repo) {
	        if (repo.name) {
	            return repo.name;
	        } else {
	            return repo.text;
	        }
	    }
	    $(nowthis).select2({
	        placeholder: '请输入学年级信息....',
	        allowClear: true,
	        minimumInputLength: 1,
	        width: 160,
	        ajax: {
	            url: "/index.php/admin/Taskmessage/addTask.html",
	            dataType: 'json',
	            delay: 250,
	            data: function (params) {
	                return {
	                	action:'userOne',
	                    search_name: params.term, // search term
	                };
	            },
	            processResults: function (data, params) {
	                return {
	                    results: data
	                };
	            },
	        },
	        templateResult: formatRepo1, // omitted for brevity, see the source of this page
	        templateSelection: formatRepoProvince1 // omitted for brevity, see the source of this page
	    });
	}
	
    select2('.auditor');
})