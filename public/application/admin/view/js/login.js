$(document).keydown(function(event){ 
	if(event.keyCode==13){ 
		login(); 
	} 
}); 
function login(){
	var loading = MBIS.msg('加载中', {icon: 16,time:60000});
	var params = MBIS.getParams('.ipt');
	$.post(MBIS.U('admin/index/checkLogin'),params,function(data,textStatus){
		layer.close(loading);
		var json = MBIS.toAdminJson(data);
		if(json.status=='1'){
			MBIS.msg("登录成功",{icon:1},function(){
				location.href=MBIS.U('admin/index/index');
			});
		}else{
			getVerify('#verifyImg');
			MBIS.msg(json.msg,{icon:2});			
		}
	});
}
getVerify = function(img){
	$(img).attr('src',MBIS.U('admin/index/getVerify','rnd='+Math.random()));
}