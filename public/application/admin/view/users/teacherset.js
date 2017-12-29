$(function(){
	$('#type_id').change(function(){
	  var type_id = $('#type_id').val();
	  var subject_id = $('#subject_id').val();
	  $.post(MBIS.U('admin/users/getSetSubject'),{type_id:type_id},function(data){
	    var json = MBIS.toAdminJson(data);
	    if(json.status == 1){
	      $('#subject_id').empty();
	      $('#subject_id').append("<option value=''>请选择</option>");
	      $.each(json.data,function(key,value){
	        $('#subject_id').append("<option value="+value['subject_id']+">"+value['name']+"</option>");
	      });
	    }else{
	      MBIS.msg(json.msg,{icon:2});
	      $('#subject_id').empty();
	      $('#subject_id').append("<option value=''>请选择</option>");
	    }
	  });
	});

	$('#btnsub').click(function(){
	    var type_id = $('#type_id').val();
	  	var subject_id = $('#subject_id').val();
	  	var userId = $('#userId').val();
	  	var pay = $('#pay').val();
	    var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
	  	$.post(MBIS.U('admin/users/addTeacherSet'),{userId:userId,type_id:type_id,subject_id:subject_id,pay:pay},function(data,textStatus){
	        layer.close(loading);
	        var json = MBIS.toAdminJson(data);
	        if(json.status=='1'){
	            MBIS.msg("操作成功",{icon:1});
	            $('#no_info').remove();
	            var trHtml = "<tr id='t_"+json.data.ss_id+"'><td>"+json.data.ss_id+"</td><td>"+json.data.userId+"</td><td>"+json.data.type_id+"</td><td>"+json.data.subject_id+"</td><td><a href='javascript:void(0);' onclick='toDel("+json.data.ss_id+")'>删除</a></td></tr>";
	            $('#t_set').append(trHtml);
	        }else{
	            MBIS.msg(json.msg,{icon:2});
	        }
	  	});   
	});

})

