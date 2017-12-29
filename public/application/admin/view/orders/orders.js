var grid;
var combo;
$(function(){
	$("#start").ligerDateEditor();
  	$("#end").ligerDateEditor();
})
function initGrid(type_id){
	grid = $("#maingrid").ligerGrid({
		url:MBIS.U('admin/orders/pageQuery','type_id='+type_id),
		pageSize:MBIS.pageSize,
		pageSizeOptions:MBIS.pageSizeOptions,
		height:'99%',
        width:'100%',
        minColToggle:6,
        rownumbers:true,
        columns: [
	        { display: '订单号', name: 'orderNo',isSort: false},
            { display: '订单总金额', name: 'totalMoney',isSort: false},
            { display: '应付金额', name: 'realTotalMoney',isSort: false},
            { display: '实付金额', name: 'realPayMoney',isSort: false},
            { display: '未付金额', name: 'depositRemainMoney',isSort: false},
            //{ display: '课程金额', name: 'courseMoney',isSort: false},
            //{ display: '学杂费总额', name: 'adItMoney',isSort: false},
            { display: '优惠金额', name: 'discountMoney',isSort: false},
            //{ display: '支付类型', name: 'payTypeName',isSort: false},
            { display: '支付方式', name: 'payFromName',isSort: false},
            { display: '报名人姓名', width:50, name: 'name',isSort: false,},
            { display: '报名人手机号', width:100,name: 'mobile',isSort: false},
	        //{ display: '报名人身份证', name: 'idcard',isSort: false},
            { display: '订单状态', width:150, name: 'orderStatusName',isSort: false},
            { display: '创建时间', width:130, name: 'createtime_format',isSort: false},
	        { display: '操作', name: 'op',width: 120,isSort: false,
	        	render: function (rowdata){
		            var h = "";
                    if(MBIS.GRANT.DDLB_001)h += "<a href='"+MBIS.U('admin/orders/get')+"?type_id="+rowdata["type_id"]+"&id="+rowdata["orderId"]+"'>订单明细</a> ";
                    if(rowdata['isAudit']==1)
                    {
                        if(MBIS.GRANT.DDLB_002)h += "<a href='"+MBIS.U('admin/orders/audit')+"?type_id="+rowdata["type_id"]+"&id="+rowdata["orderId"]+"'>财务审核</a> ";
                    }
		            //if(MBIS.GRANT.)h += "<a href='javascript:toDel("+type_id+","+rowdata["school_id"]+")'>删除</a> "; 
		            return h;
	        	}
	        }
        ]
    });
}

function initCombo(){
}

function loadGrid(type_id){
	grid.set('url',MBIS.U('admin/orders/pageQuery','query_type_id='+$('#type_id').val()+'&orderNo='+$('#orderNo').val()+'&type_id='+type_id+'&buyType='+$('#buyType').val()+'&order_status='+$('#order_status').val() ));
}
function orderQuery(type_id){
	var query = MBIS.getParams('.query');
    grid.set('url',MBIS.U('admin/orders/pageQuery',query));
}
function refresh(){
	$('.query').each(function(){
	    if($(this).val() !== ''){
	      $(this).val('');
	    }
  	});
	grid.set('url',MBIS.U('admin/orders/pageQuery'));
}
function expOrders(){
  var query = MBIS.getParams('.query');
  var link = MBIS.U('admin/orders/expOrders',query);
  $('#export').attr('href',link);
}
function toEdit(type_id,id){
	location.href=MBIS.U('admin/school/toEdit','type_id='+type_id+'&id='+id);
}

function toEdits(id){
    //var params = MBIS.getParams('.ipt');
    var params = $('#schoolForm').serialize();
    params.id = id;
    var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
	$.post(MBIS.U('admin/school/'+((id>0)?"edit":"add")),params,function(data,textStatus){
		  layer.close(loading);
		  var json = MBIS.toAdminJson(data);
		  if(json.status=='1'){
		    	MBIS.msg(json.msg,{icon:1});
		        setTimeout(function(){ 
			    	location.href=MBIS.U('admin/school/index','type_id='+$('#type_id').val());
		        },1000);
		  }else{
		        MBIS.msg(json.msg,{icon:2});
		  }
	});
}

function toDel(type_id,id){
	var box = MBIS.confirm({content:"您确定要删除该数据吗?",yes:function(){
	           var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
	           	$.post(MBIS.U('admin/school/del'),{id:id},function(data,textStatus){
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

function toAudit(id){
    var params = $('#orderAuditForm').serialize();
    var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
	$.post(MBIS.U('admin/orders/toAudit'),params,function(data,textStatus){
		  layer.close(loading);
		  var json = MBIS.toAdminJson(data);
		  if(json.status=='1'){
		    	MBIS.msg(json.msg,{icon:1});
		        setTimeout(function(){ 
			    	location.href=MBIS.U('admin/orders/index');
		        },1000);
		  }else{
		        MBIS.msg(json.msg,{icon:2});
		  }
	});
}

//添加订单
function toAdd(type_id){
	location.href=MBIS.U('admin/orders/toAdd','type_id='+type_id);
}
function toSubmit(type_id){
    var params = $('#orderAddForm').serialize();
    var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
	$.post(MBIS.U('admin/orders/submit'),params,function(data,textStatus){
		  layer.close(loading);
		  var json = MBIS.toAdminJson(data);
		  if(json.status=='1'){
		    	MBIS.msg(json.msg,{icon:1});
		        setTimeout(function(){ 
                    window.location.reload();
			    	//location.href=MBIS.U('admin/orders/index','type_id='+$('#type_id').val());
		        },1000);
		  }else{
		        MBIS.msg(json.msg,{icon:2});
		  }
	});
}
//课程名称模糊查找
function courseQuery(){
    var type_id = $('#course_type_id').val();
    var key = $('#courseName').val();
    var html = '';
    $.post(MBIS.U('admin/orders/get_course_lists'),{'type_id':type_id,'name':key},function(text,dataStatus){
      $(text).each(function(k,v){
          var full_name = v.school_name+' - '+v.major_name+' - '+v.name + '('+v.offers_price+')';
        html += '<option title="'+full_name+'" value="'+v.course_id+'">'+full_name+'</option>';
      });
      $('#ltarget2').html(html);
    });
}

//科目名称模糊查找
function subjectQuery(){
    var type_id = $('#subject_type_id').val();
    var key = $('#subjectName').val();
    var html = '';
    $.post(MBIS.U('admin/orders/get_subject_lists'),{'type_id':type_id,'name':key},function(text,dataStatus){
      $(text).each(function(k,v){
          var full_name = v.school_name+' - '+v.major_name+' - '+v.name + '('+v.sale_price+')';
        html += '<option title="'+full_name+'" value="'+v.subject_id+'">'+full_name+'</option>';
      });
      $('#ltarget3').html(html);
    });
}

//账号模糊查找
 function userQuery(){
  var key = $('#loginName').val();
  var html = '';
  $.post(MBIS.U('admin/orders/userQuery'),{'loginName':key},function(text,dataStatus){
      $(text).each(function(k,v){
        html += '<option value="'+v.userId+'">'+v.loginName+'</option>';
      });
      $('#ltarget').html(html);
  });
  
 }
 
 function toImport(type_id,key){
	var w = MBIS.open({type: 2,title:"导入页面",shade: [0.6, '#000'],border: [0],content:MBIS.U('admin/orders/toImport','type_id='+type_id+'&key='+key),area: ['600px', '350px']
	});
}
