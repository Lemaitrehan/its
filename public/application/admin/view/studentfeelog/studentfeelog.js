var grid;
var combo;
var type1;
$(function(){
	$("#pay_time").ligerDateEditor();
	$("#plan_paytime").ligerDateEditor();
	$("#start").ligerDateEditor();
  	$("#end").ligerDateEditor();
  	
  	$("#first_phase").ligerDateEditor();
  	$("#second_phase").ligerDateEditor();
  	$("#third_phase").ligerDateEditor();
})
function initGrid(type){
	type1 = type;
    var grid_config_1 = {
		url:MBIS.U('admin/studentfeelog/pageQuery'+type),
		pageSize:MBIS.pageSize,
		pageSizeOptions:MBIS.pageSizeOptions,
		height:'99%',
        width:'100%',
        minColToggle:7,
        rownumbers:true,
        columnWidth:150,
        //checkbox: false,  															
        columns: [
        //学员编号	学员姓名	身份证号	收款类别	报考类型	报读院校	层次	报读专业	学习形式	课程编码	课程名称	标准学费	折前减免	付款方式折扣优惠	科目累计折扣优惠	团报折扣优惠	校长特权优惠	活动折扣优惠	特殊折扣优惠额	折后减免	应收学费总额	累计已收学费总额	待收学费总额	是否欠费
        
        //学员编号	学员姓名	身份证号	收款类别	报考类型	报读院校	层次	报读专业	学习形式	课程编码	课程名称	标准学费	优惠金额	应收学费总额	累计已收学费总额	待收学费总额	是否欠费

            { display: '收款校区', name: 'receiptSchool',isSort: false,},
            { display: '学员ID', name: 'userId',isSort: false,},
            { display: '学员编号', name: 'student_no',isSort: false},
            { display: '学员姓名', name: 'trueName',isSort: false},
            { display: '身份证号', name: 'idcard',isSort: false},
            { display: '收款类别', name: 'receiptCate',isSort: false},
            { display: '报考类型', name: 'exam_type',isSort: false},
            { display: '报读院校', name: 'school_name',isSort: false},
            { display: '层次', name: 'level_name',isSort: false},
            { display: '报读专业', name: 'major_name',isSort: false},
            { display: '学习形式', name: 'studyStatus',isSort: false},
            { display: '课程编码', name: 'course_bn',isSort: false},
            { display: '课程名称', name: 'course_name',width:350,isSort: false},
            { display: '标准学费', name: 'price',isSort: false},
            { display: '优惠金额', name: 'discount_price',isSort: false},
            { display: '应收学费总额', name: 'deal_price',isSort: false},
            { display: '累计已收学费总额', name: 'total_price',isSort: false},
            { display: '待收学费总额', name: 'wait_price',isSort: false},
            { display: '是否欠费', name: 'arre_type',isSort: false},
       /*     { display: '报考院校', name: 'school_name',isSort: false},
            { display: '层次', name: 'level_id_format',isSort: false},
            { display: '报考专业', name: 'major_name',isSort: false},
            { display: '标准学费', name: 'stu_price',isSort: false},
            { display: '单据日期', name: 'receipt_time_format',width:100,isSort: false},
            { display: '收入', name: 'income',isSort: false},
            { display: '收据号码', name: 'receipt_no',isSort: false},
            { display: '帐户名称', name: 'account_name',isSort: false},
	        { display: '缴费类型', name: 'bill_type_format',isSort: false},
	        { display: '缴费方式', name: 'bill_way_format',isSort: false},
	        { display: '缴费名称', name: 'bill_name',isSort: false},
	        { display: '签单咨询师', name: 'sign_name',isSort: false},
            { display: '备注', name: 'remark',isSort: false},*/
	        { display: '操作', name: 'op',width:100,isSort: false,
	        	render: function (rowdata){
		            var h = "";
		            h += "<a href='javascript:toSee2("+rowdata["userId"]+",\""+rowdata['course_bn']+"\")'>查看缴费记录</a> ";
		           /* if( ( type1 ==1 && MBIS.GRANT.JFXI_003 ) ||  ( type1 ==2 && MBIS.GRANT.SFJL_02 ) )
		            	h += "<a href='javascript:toEdit("+rowdata["fee_id"]+",1)'>修改</a> ";
		            if(  (type1 ==1 && MBIS.GRANT.JFXI_004 ) ||  ( type1 ==2 && MBIS.GRANT.SFJL_03 ) )
		            	h += "<a href='javascript:toDel("+rowdata["fee_id"]+")'>删除</a> ";*/
		            //if(MBIS.GRANT.SFJL_04)h += "<a href='javascript:toDetail("+rowdata["fee_id"]+")'>查看详情</a> ";
		            return h;
	        	}
	        }
        ]
    };
    var grid_config_2 = {
		url:MBIS.U('admin/studentfeelog/pageQuery'+type),
		pageSize:MBIS.pageSize,
		pageSizeOptions:MBIS.pageSizeOptions,
		height:'99%',
        width:'100%',
        minColToggle:7,
        rownumbers:true,
        columnWidth:150,
        //checkbox: false,  															
        columns: [
        //学员编号	学员姓名	身份证号	收款类别	报考类型	报读院校	层次	报读专业	学习形式	课程编码	课程名称	标准学费	折前减免	付款方式折扣优惠	科目累计折扣优惠	团报折扣优惠	校长特权优惠	活动折扣优惠	特殊折扣优惠额	折后减免	应收学费总额	累计已收学费总额	待收学费总额	是否欠费
        
        //学员编号	学员姓名	身份证号	收款类别	报考类型	报读院校	层次	报读专业	学习形式	课程编码	课程名称	标准学费	优惠金额	应收学费总额	累计已收学费总额	待收学费总额	是否欠费

            { display: '收款校区', name: 'receiptSchool',isSort: false,},
            { display: '学员ID', name: 'userId',isSort: false,},
            { display: '学员编号', name: 'student_no',isSort: false},
            { display: '学员姓名', name: 'trueName',isSort: false},
            { display: '身份证号', name: 'idcard',isSort: false},
            { display: '收款类别', name: 'receiptCate',isSort: false},
            { display: '报读专业', name: 'major_name',isSort: false},
            { display: '学习形式', name: 'studyStatus',isSort: false},
            { display: '课程编码', name: 'course_bn',isSort: false},
            { display: '课程名称', name: 'course_name',width:350,isSort: false},
            { display: '标准学费', name: 'price',isSort: false},
            { display: '优惠金额', name: 'discount_price',isSort: false},
            { display: '应收学费总额', name: 'deal_price',isSort: false},
            { display: '累计已收学费总额', name: 'total_price',isSort: false},
            { display: '待收学费总额', name: 'wait_price',isSort: false},
            { display: '是否欠费', name: 'arre_type',isSort: false},
       /*     { display: '报考院校', name: 'school_name',isSort: false},
            { display: '层次', name: 'level_id_format',isSort: false},
            { display: '报考专业', name: 'major_name',isSort: false},
            { display: '标准学费', name: 'stu_price',isSort: false},
            { display: '单据日期', name: 'receipt_time_format',width:100,isSort: false},
            { display: '收入', name: 'income',isSort: false},
            { display: '收据号码', name: 'receipt_no',isSort: false},
            { display: '帐户名称', name: 'account_name',isSort: false},
	        { display: '缴费类型', name: 'bill_type_format',isSort: false},
	        { display: '缴费方式', name: 'bill_way_format',isSort: false},
	        { display: '缴费名称', name: 'bill_name',isSort: false},
	        { display: '签单咨询师', name: 'sign_name',isSort: false},
            { display: '备注', name: 'remark',isSort: false},*/
	        { display: '操作', name: 'op',width:100,isSort: false,
	        	render: function (rowdata){
		            var h = "";
		            h += "<a href='javascript:toSee2("+rowdata["userId"]+",\""+rowdata['course_bn']+"\")'>查看缴费记录</a> ";
		           /* if( ( type1 ==1 && MBIS.GRANT.JFXI_003 ) ||  ( type1 ==2 && MBIS.GRANT.SFJL_02 ) )
		            	h += "<a href='javascript:toEdit("+rowdata["fee_id"]+",1)'>修改</a> ";
		            if(  (type1 ==1 && MBIS.GRANT.JFXI_004 ) ||  ( type1 ==2 && MBIS.GRANT.SFJL_03 ) )
		            	h += "<a href='javascript:toDel("+rowdata["fee_id"]+")'>删除</a> ";*/
		            //if(MBIS.GRANT.SFJL_04)h += "<a href='javascript:toDetail("+rowdata["fee_id"]+")'>查看详情</a> ";
		            return h;
	        	}
	        }
        ]
    };
    //学历缴费
	grid = $("#maingrid").ligerGrid(eval('grid_config_'+type1));
    //技能缴费
    //if(type1==2) grid = $("#maingrid").ligerGrid(grid_config_2);
}

function initCombo(){
}
function loadGrid(){
	grid.set('url',MBIS.U('admin/studentfeelog/pageQuery','key='+$('#key').val()));
}
function feeQuery(){
	if(type1==1){
		//var url = 'admin/studentfeelog/pageQuery1';
	}
    var url = 'admin/studentfeelog/pageQuery'+type1;
	var query = MBIS.getParams('.query');
    grid.set('url',MBIS.U(url,query));
}
function feeLogQuery(){
	if(type1==1){
		//var url = 'admin/studentfeelog/feeDetailQuery1';
	}else{
		//var url = 'admin/studentfeelog/feeDetailQuery2';
	}
    var url = 'admin/studentfeelog/feeDetailQuery'+type1;
	var query = MBIS.getParams('.query');
    grid.set('url',MBIS.U(url,query));
}
function refresh(){
	$('.query').each(function(){
	    if($(this).val() !== ''){
	      $(this).val('');
	    }
  	});
	if(type1==1){
		var url = 'admin/studentfeelog/pageQuery1';
	}else{
		var url = 'admin/studentfeelog/pageQuery2';
	}
  grid.set('url',MBIS.U(url));
}

function toEdit(id){
	if(type1==1){
		var url = 'admin/studentfeelog/toEditEducation';
	}else{
		var url = 'admin/studentfeelog/toEditSkill';
	}
	location.href=MBIS.U(url,'id='+id);
}

function toDetail(id){
	location.href=MBIS.U('admin/studentfeelog/toDetail','id='+id);
}

//查看缴费记录
function toSee(userId){
	    var url      = 'admin/studentfeelog/paymentRecords';
		$.post(MBIS.U(url),{userId:userId},function(data){
			  if(data.length>0){
				  var html ='';
				 $.each(data,function(i,e){
						  html+=  '<tr>'
							        +'<td>'+e.userId+'</td>'
							        +'<td>'+e.student_no+'</td>'
							        +'<td>'+e.name+'</td>'
							        +'<td>'+e.idcard+'</td>'
							        +'<td>'+e.school_name+'</td>'
							        +'<td>'+e.level_id_format+'</td>'
							        +'<td>'+e.major_name+'</td>'
							        +'<td>'+e.stu_price+'</td>'
							        +'<td>'+e.receipt_time_format+'</td>'
							        +'<td>'+e.income+'</td>'
							        +'<td>'+e.receipt_no+'</td>'
							        +'<td>'+e.account_name+'</td>'
							        +'<td>'+e.bill_type_format+'</td>'
							        +'<td>'+e.bill_way_format+'</td>'
							        +'<td>'+e.bill_name+'</td>'
							        +'<td>'+e.sign_name+'</td>'
							        +'<td>'+e.remark+'</td>'
							     +'</tr>';
				 }) 
				 $('#target1 table tr').not(':eq(0)').remove();
		         $('#target1 table').append(html);
		         
		         $.ligerDialog.open({ target: $("#target1") ,width:1200, height:800,
		             title:'缴费记录表',
		    	     buttons: [  { text: '查询', onclick: function (i, d) { initGrid(1); }}, 
		    	        { text: '关闭', onclick: function (i, d) { $("input").ligerHideTip(); d.hide(); }} 
		    	     ]   
		           
		        });
					         
			  }else{
			        MBIS.msg('查不到缴费记录',{icon:2});
			  }
		});
}

function toEdits(id,type){
    var params = MBIS.getParams('.ipt');
    params.id = id;
    var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
    if(type=='1'){
    	var url      = 'admin/studentfeelog/'+((id>0)?"editEducation":"addEducation"),
    	    urlIndex = 'admin/studentfeelog/indexEducation';
    }else{
    	var url = 'admin/studentfeelog/'+((id>0)?"editSkill":"addSkill"),
    	    urlIndex = 'admin/studentfeelog/indexSkill';
    }
	$.post(MBIS.U(url),params,function(data,textStatus){
		  layer.close(loading);
		  var json = MBIS.toAdminJson(data);
		  if(json.status=='1'){
		    	MBIS.msg(json.msg,{icon:1});
		        setTimeout(function(){ 
			    	location.href=MBIS.U(urlIndex);
		        },1000);
		  }else{
		        MBIS.msg(json.msg,{icon:2});
		  }
	});
}

function toDel(id,type){
	if(type==1){
		var url = 'admin/studentfeelog/delEducation';
	}else{
		var url = 'admin/studentfeelog/delSkill';
	}
	var box = MBIS.confirm({content:"您确定要删除该数据吗?",yes:function(){
	           var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
	           	$.post(MBIS.U(url),{id:id},function(data,textStatus){
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
function getInfo(){
	var userId = $('#userId').val();
	if(userId !== ''){
		$.post(MBIS.U('admin/studentfeelog/getInfo'),{userId:userId},function(data){
			var json = MBIS.toAdminJson(data);
			if(json.status == 1){
				$('#student_no').val(json.data.student_no);
				$('#student_name').val(json.data.trueName);
				$('#entry_time').val(json.data.createtime);
				$('#orderNo').val(json.data.orderNo);
				//$('#price').val(json.data.totalMoney);
			}
			else
			if(json.status == -1){
				MBIS.msg(json.msg,{icon:1});
				$('#student_no').val('');
				$('#student_name').val('');
				$('#entry_time').val('');
				$('#orderNo').val('');
				//$('#price').val('');
			}
		});
	}else
	if(userId == ''){
		$('#student_no').val('');
		$('#student_name').val('');
		$('#entry_time').val('');
		$('#orderNo').val('');
		//$('#price').val('');
	}
}
function toImport2(type){
	var w = MBIS.open({type: 2,title:"导入页面",shade: [0.6, '#000'],border: [0],content:MBIS.U('admin/studentfeelog/toImport'),area: ['500px', '250px']
    /*,btn: ['确定', '取消'],yes: function(index, layero){
            var ll = MBIS.msg('数据处理中，请稍候...');
            $.post(MBIS.U('admin/studentfeelog/import'),$('#importForm').serialize(),function(data){
                layer.close(ll);
                var json = MBIS.toAdminJson(data);
                if(json.status==1){
                    MBIS.msg(json.msg, {icon: 1});
                    layer.close(w);
                }else{
                    MBIS.msg(json.msg, {icon: 2});
                }
           });
        }*/
	});
}
 function toImport(type_id,key){
	var w = MBIS.open({type: 2,title:"导入页面",shade: [0.6, '#000'],border: [0],content:MBIS.U('admin/studentfeelog/toImport','type_id='+type_id+'&key='+key),area: ['600px', '350px']
	});
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
	    	sel_data[i].name;//学员id 
	    	sel_data[i].student_no;//学员id 
	        ids +=  sel_data[i].userId+'--'+sel_data[i].name+'('+sel_data[i].student_no+'),';   
	    }
	    if (ids.length > 0) {
	    	ids = ids.substr(0, ids.length - 1);
	    }
		window.location.href = MBIS.U('admin/Studentnoticelog/addEducationList','ids='+ids);
	
}

//查看缴费记录
function toSee2(userId,courseBn){
	    //var url      = 'admin/studentfeelog/paymentRecords2';
        
        var url      = 'admin/Studentfeelog/feeDetail'+type1;
        var w = MBIS.open({offset:["0","0"],type: 2,title:"查看缴费记录",shade: [0.6, '#000'],border: [0],content:MBIS.U(url,'userId='+userId+'&courseBn='+courseBn),area: ['100%', '100%']
        //,btn: ['确定', '取消'],yes: function(index, layero){}
	});
}

function initGrid2(type,userId,courseBn){
	type1 = type;
    var userId = userId || 0;
    var courseBn = courseBn || '';
    var grid_config_1 = {
		url:MBIS.U('admin/studentfeelog/feeDetailQuery'+type1,'userId='+userId+'&courseBn='+courseBn),
		pageSize:MBIS.pageSize,
		pageSizeOptions:MBIS.pageSizeOptions,
		height:'99%',
        width:'100%',
        minColToggle:7,
        rownumbers:true,
        columnWidth:150,
        //checkbox: false,  															
        columns: [
        //学员编号	学员姓名	身份证号	收款类别	报考类型	报读院校	层次	报读专业	学习形式	课程编码	课程名称	标准学费	折前减免	付款方式折扣优惠	科目累计折扣优惠	团报折扣优惠	校长特权优惠	活动折扣优惠	特殊折扣优惠额	折后减免	应收学费总额	累计已收学费总额	待收学费总额	是否欠费
        
//收款校区	学员编号	学员名称	身份证号	收款类别	报考类型	报读院校	层次	报读专业	学习形式	课程编码	课程名称	收入	单据日期	收据号码	缴费类型	缴费方式
            { display: '收款校区', name: 'receiptSchool',isSort: false,},
            { display: '学员ID', name: 'userId',isSort: false,},
            { display: '学员编号', name: 'student_no',isSort: false},
            { display: '学员姓名', name: 'trueName',isSort: false},
            { display: '身份证号', name: 'idcard',isSort: false},
            { display: '收款类别', name: 'receiptCate',isSort: false},
            { display: '报考类型', name: 'exam_type',isSort: false},
            { display: '报读院校', name: 'school_name',isSort: false},
            { display: '层次', name: 'level_name',isSort: false},
            { display: '报读专业', name: 'major_name',isSort: false},
            { display: '学习形式', name: 'studyStatus',isSort: false},
            { display: '课程编码', name: 'course_bn',isSort: false},
            { display: '课程名称', name: 'course_name',width:350,isSort: false},
            { display: '收入', name: 'money',isSort: false},
            { display: '单据日期', name: 'receiptDate',isSort: false},
            { display: '收据号码', name: 'receiptNo',isSort: false},
            { display: '缴费类型', name: 'pay_type',isSort: false},
            { display: '缴费方式', name: 'pay_name',isSort: false},
            
            //{ display: '报读院校', name: 'school_name',isSort: false},
            //{ display: '层次名称', name: 'level_name',width:60,isSort: false},
            //{ display: '报读专业', name: 'major_name',isSort: false},
            //{ display: '标准学费', name: 'price',isSort: false},
            //{ display: '优惠金额', name: 'discount_price',isSort: false},
            //{ display: '应收学费总额', name: 'deal_price',isSort: false},
       /*     { display: '报考院校', name: 'school_name',isSort: false},
            { display: '层次', name: 'level_id_format',isSort: false},
            { display: '报考专业', name: 'major_name',isSort: false},
            { display: '标准学费', name: 'stu_price',isSort: false},
            { display: '单据日期', name: 'receipt_time_format',width:100,isSort: false},
            { display: '收入', name: 'income',isSort: false},
            { display: '收据号码', name: 'receipt_no',isSort: false},
            { display: '帐户名称', name: 'account_name',isSort: false},
	        { display: '缴费类型', name: 'bill_type_format',isSort: false},
	        { display: '缴费方式', name: 'bill_way_format',isSort: false},
	        { display: '缴费名称', name: 'bill_name',isSort: false},
	        { display: '签单咨询师', name: 'sign_name',isSort: false},
            { display: '备注', name: 'remark',isSort: false},*/
	        /*{ display: '操作', name: 'op',width:100,isSort: false,
	        	render: function (rowdata){
		            var h = "";
		            //h += "<a href='javascript:toSee2("+rowdata["userId"]+",\""+rowdata['course_bn']+"\")'>查看缴费记录</a> ";
		           if( ( type1 ==1 && MBIS.GRANT.JFXI_003 ) ||  ( type1 ==2 && MBIS.GRANT.SFJL_02 ) )
		            	h += "<a href='javascript:toEdit("+rowdata["fee_id"]+",1)'>修改</a> ";
		            if(  (type1 ==1 && MBIS.GRANT.JFXI_004 ) ||  ( type1 ==2 && MBIS.GRANT.SFJL_03 ) )
		            	h += "<a href='javascript:toDel("+rowdata["fee_id"]+")'>删除</a> ";
		            //if(MBIS.GRANT.SFJL_04)h += "<a href='javascript:toDetail("+rowdata["fee_id"]+")'>查看详情</a> ";
		            return h;
	        	}
	        }*/
        ]
    };
    var grid_config_2 = {
		url:MBIS.U('admin/studentfeelog/feeDetailQuery'+type1,'userId='+userId+'&courseBn='+courseBn),
		pageSize:MBIS.pageSize,
		pageSizeOptions:MBIS.pageSizeOptions,
		height:'99%',
        width:'100%',
        minColToggle:7,
        rownumbers:true,
        columnWidth:150,
        //checkbox: false,  															
        columns: [
        //学员编号	学员姓名	身份证号	收款类别	报考类型	报读院校	层次	报读专业	学习形式	课程编码	课程名称	标准学费	折前减免	付款方式折扣优惠	科目累计折扣优惠	团报折扣优惠	校长特权优惠	活动折扣优惠	特殊折扣优惠额	折后减免	应收学费总额	累计已收学费总额	待收学费总额	是否欠费
        
//收款校区	学员编号	学员名称	身份证号	收款类别	报考类型	报读院校	层次	报读专业	学习形式	课程编码	课程名称	收入	单据日期	收据号码	缴费类型	缴费方式
            { display: '收款校区', name: 'receiptSchool',isSort: false,},
            { display: '学员ID', name: 'userId',isSort: false,},
            { display: '学员编号', name: 'student_no',isSort: false},
            { display: '学员姓名', name: 'trueName',isSort: false},
            { display: '身份证号', name: 'idcard',isSort: false},
            { display: '收款类别', name: 'receiptCate',isSort: false},
            { display: '报读专业', name: 'major_name',isSort: false},
            { display: '学习形式', name: 'studyStatus',isSort: false},
            { display: '课程编码', name: 'course_bn',isSort: false},
            { display: '课程名称', name: 'course_name',width:350,isSort: false},
            { display: '收入', name: 'money',isSort: false},
            { display: '单据日期', name: 'receiptDate',isSort: false},
            { display: '收据号码', name: 'receiptNo',isSort: false},
            { display: '缴费类型', name: 'pay_type',isSort: false},
            { display: '缴费方式', name: 'pay_name',isSort: false},
            
            //{ display: '报读院校', name: 'school_name',isSort: false},
            //{ display: '层次名称', name: 'level_name',width:60,isSort: false},
            //{ display: '报读专业', name: 'major_name',isSort: false},
            //{ display: '标准学费', name: 'price',isSort: false},
            //{ display: '优惠金额', name: 'discount_price',isSort: false},
            //{ display: '应收学费总额', name: 'deal_price',isSort: false},
       /*     { display: '报考院校', name: 'school_name',isSort: false},
            { display: '层次', name: 'level_id_format',isSort: false},
            { display: '报考专业', name: 'major_name',isSort: false},
            { display: '标准学费', name: 'stu_price',isSort: false},
            { display: '单据日期', name: 'receipt_time_format',width:100,isSort: false},
            { display: '收入', name: 'income',isSort: false},
            { display: '收据号码', name: 'receipt_no',isSort: false},
            { display: '帐户名称', name: 'account_name',isSort: false},
	        { display: '缴费类型', name: 'bill_type_format',isSort: false},
	        { display: '缴费方式', name: 'bill_way_format',isSort: false},
	        { display: '缴费名称', name: 'bill_name',isSort: false},
	        { display: '签单咨询师', name: 'sign_name',isSort: false},
            { display: '备注', name: 'remark',isSort: false},*/
	        /*{ display: '操作', name: 'op',width:100,isSort: false,
	        	render: function (rowdata){
		            var h = "";
		            //h += "<a href='javascript:toSee2("+rowdata["userId"]+",\""+rowdata['course_bn']+"\")'>查看缴费记录</a> ";
		           if( ( type1 ==1 && MBIS.GRANT.JFXI_003 ) ||  ( type1 ==2 && MBIS.GRANT.SFJL_02 ) )
		            	h += "<a href='javascript:toEdit("+rowdata["fee_id"]+",1)'>修改</a> ";
		            if(  (type1 ==1 && MBIS.GRANT.JFXI_004 ) ||  ( type1 ==2 && MBIS.GRANT.SFJL_03 ) )
		            	h += "<a href='javascript:toDel("+rowdata["fee_id"]+")'>删除</a> ";
		            //if(MBIS.GRANT.SFJL_04)h += "<a href='javascript:toDetail("+rowdata["fee_id"]+")'>查看详情</a> ";
		            return h;
	        	}
	        }*/
        ]
    };
    //学历缴费
	grid = $("#maingrid").ligerGrid(eval('grid_config_'+type1));
    //技能缴费
    //if(type1==2) grid = $("#maingrid").ligerGrid(grid_config_2);
}

function majorGet(nowthis){
    $.post('?',{school_id:$(nowthis).val() },function(data){
        var json = MBIS.toAdminJson(data);
        var html = '<option value=" ">全部</option>';
        $(json).each(function(i,e){
            html += '<option value="'+e.name+'">'+e.name+'</option>';
        })
        $('#major_id').html(html);
   });
}
//导出
$('.daochu').click(function(){
	var query = MBIS.getParams('.query');
        query.action = 'fy';
    var url = MBIS.U('admin/Studentfeelog/exportxl',query);
    window.location.href = 	url;
})
//导出
$('.daochu1').click(function(){
	var query = MBIS.getParams('.query');
        query.action = 'fy';
    var url = MBIS.U('admin/Studentfeelog/exportxlmx',query);
    window.location.href = 	url;
})
