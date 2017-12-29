function toSubmit(id,type,grade_id){
    var params = MBIS.getParams('.ipt');
    if(id){
      params.id = id;
    }
    if(grade_id){
    	params.grade_id = grade_id;
    }
    var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
    if(type=='1'){
    	var url      = 'admin/Examinationmanagement/'+((id>0)?"editEducation":"addEducation"),
    	    urlIndex = 'admin/Examinationmanagement/indexEducation';
    }else{
    	var url = 'admin/Examinationmanagement/'+((id>0)?"editSkill":"addSkill"),
    	    urlIndex = 'admin/Examinationmanagement/indexSkill';
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

function getDataFirst(show_div,bg_div){  //第一次获取数据同时打开隐藏层--加载第一分页
		var page = 1;
		$.post(MBIS.U('admin/Studentnoticelog/getUsersList'),{page:page-1},function(data){
	        var json = MBIS.toAdminJson(data);
	        if(json.status == 1){
	        	ShowDiv(show_div,bg_div);
	            $('#user_table tr:not(:first)').empty();
	            $.each(json.data,function(k,v){
	                $('#user_table').append("<tr id='ts_"+v.userId+"' class='str'><td><input id='ck_"+v.userId+"' type='checkbox' name='chk' value='"+v.userId+"'></td><td>"+v.student_no+"</td><td>"+v.trueName+"</td><td>"+v.nickName+"</td><td>"+v.userQQ+"</td><td>"+v.userPhone+"</td><td>"+v.userEmail+"</td><td>"+v.uidType+"</td><td>"+v.student_type+"</td></tr>");
	            });
	            var curPage = page;
	            var total = json.pageinfo.total;
	            var pageSize = json.pageinfo.pageSize;
	            var totalPage = json.pageinfo.totalPage;
	            getPageBar(curPage,total,totalPage);
	        }else{
	            MBIS.msg(json.msg,{icon:2});
	        }
	    });
}

function getChecked(MyDiv,bg_div){
	var userId = [],userIds='',user=[];
	$('#'+MyDiv).find('input[name="chk"]:checked').each(function(i,e){
		userId.push( $(this).val() );
		var userName = $(e).closest('tr').find('td:eq(2)').html();
		var userNo   = $(e).closest('tr').find('td:eq(1)').html();
		user.push( userName+'('+userNo+')' );
	})
	if(userId){
		userIds =  userId.join(',');
	}
	if(user){
		user =  user.join(',');
	}
	$('#userIds').val(userIds);
	$('.checkUserIds').html(user);
	document.getElementById(MyDiv).style.display='none';
	document.getElementById(bg_div).style.display='none';
}

//全选
$('#allCheck').click(function(){
	var is_all = $(this).is(':checked');
	$(this).closest('table').find('input[type="checkbox"]').each(function(e){
		if(is_all){
			$(this).prop('checked',true);
		}else{
			$(this).prop('checked',false);
		}
	})
})


function getPageBar(curPage,total,totalPage){  
	  
	if(curPage>totalPage) curPage=totalPage;  //页码大于最大页数

	if(curPage<1) curPage=1;  //页码小于1  
	pageStr = "<span class='page_span'>共"+total+"条</span><span class='page_span'>第"+curPage+"页/共"+totalPage+"页</span>";  

	if(curPage==1){  //如果是第一页
		pageStr += "<span class='page_span'>首页</span><span class='page_span'>上一页</span>";  
	}else{  
		pageStr += "<span class='page_span'><a href='javascript:void(0)' id='firstpage' onclick='firstpage()' rel='1'>首页</a></span><span class='page_span'><a href='javascript:void(0)' id='lastpage' onclick='lastpage()' rel='"+(curPage-1)+"'>上一页</a></span>";  
	}  

	if(curPage>=totalPage){  //如果是最后页 
		pageStr += "<span class='page_span'>下一页</span><span class='page_span'>尾页</span>";  
	}else{  
		pageStr += "<span class='page_span'><a href='javascript:void(0)' id='nextpage' onclick='nextpage()' rel='"+(parseInt(curPage)+1)+"'>下一页</a></span><span class='page_span'><a href='javascript:void(0)' id='endpage' onclick='endpage()' rel='"+totalPage+"'>尾页</a></span>";  
	}  

	$("#pagecount").html(pageStr);  
}

function firstpage(){ //首页
	var rel = $('#firstpage').attr("rel");
	if(rel){
		getData(rel);
	}
}
function lastpage(){ //上一页
	var rel = $('#lastpage').attr("rel");  
	if(rel){  
		getData(rel);  
	}
}
function nextpage(){ //下一页
	var rel = $('#nextpage').attr("rel");
	if(rel){
		getData(rel);
	}
}
function endpage(){ //尾页
	var rel = $('#endpage').attr("rel");
	if(rel){
		getData(rel);
	}
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
