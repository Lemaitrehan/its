function edit(){
	var params = {};
	params.license = $('#license').val();
	$('#licenseTr').hide();
	$('#editFrom').isValid(function(v){
	if(v){
		var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
		$.post(MBIS.U('admin/index/verifyLicense'),params,function(data,textStatus){
			layer.close(loading);
			var json = MBIS.toAdminJson(data);
			if(json.status=='1'){
				$('#licenseTr').show();
				$('#licenseStatus').html(json.license.licenseStatus);
			}else{
				MBIS.msg("操作成功",{icon:1});
			}
		});
	}});
}  