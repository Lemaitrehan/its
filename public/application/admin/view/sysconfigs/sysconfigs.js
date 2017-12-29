var tab,h;
$(function(){
	h = MBIS.pageHeight()-32;
	tab = $("#wst-tabs").ligerTab({
	      height: '99%',
	      changeHeightOnResize:true,
	      showSwitchInTab : false,
	      showSwitch: true
	});
	$('.l-tab-content').height(h);
	$('.l-tab-content-item').height(h);
	$('.l-tab-content-item').css('overflow-y','auto');
	var uploads = ['watermarkFile','mallLogo','shopLogo','userLogo','goodsLogo'],key;
	for(var i=0;i<uploads.length;i++){
		key = uploads[i];
		MBIS.upload({
			  k:key,
		  	  pick:'#'+key+"Picker",
		  	  formData: {dir:'sysconfigs'},
		  	  accept: {extensions: 'gif,jpg,jpeg,bmp,png',mimeTypes: 'image/*'},
		  	  callback:function(f){
		  		  var json = MBIS.toAdminJson(f);
		  		  if(json.status==1){
		  			 $('#'+this.k+'Msg').empty().hide();
		  			 $('#'+this.k+'Prevw').attr('src',MBIS.conf.ROOT+'/'+json.savePath+json.name);
		  			 $('#'+this.k).val(json.savePath+json.name);
		  		  }
			  },
			  progress:function(rate){
				  $('#'+this.k+'Msg').show().html('已上传'+rate+"%");
			  }
		    });
	}
})
function edit(){
	if(!MBIS.GRANT.SCPZ_02)return;
	var params = MBIS.getParams('.ipt');
	var loading = MBIS.msg('正在保存数据，请稍后...', {icon: 16,time:60000});
    $.post(MBIS.U('admin/sysconfigs/edit'),params,function(data,textStatus){
          layer.close(loading);
          var json = MBIS.toAdminJson(data);
          if(json.status==1){
        	  MBIS.msg(json.msg,{icon:1});
          }
   });
}


$(function(){
	$('#watermarkColor').colpick({
	layout:'hex',
	submit:1,
	colorScheme:'dark',
	onChange:function(hsb,hex,rgb,el,bySetColor) {
		$(el).css('border-color','#'+hex);
	},
	onSubmit:function(hsb,hex,rgb,el,bySetColor){
		if(!bySetColor) $(el).val('#'+hex);
		$(el).colpickHide();
	}
	}).keyup(function(){
		$(this).colpickSetColor(this.value);
		$(this).colpickHide();
	});

});