function getLastVersion(){
	$.post(MBIS.U('admin/index/getVersion'),{},function(data,textStatus){
		var json = {};
		try{
	      if(typeof(data )=="object"){
			  json = data;
	      }else{
			  json = eval("("+data+")");
	      }
		}catch(e){}
	    if(json){
		   if(json.version && json.version!='same'){
			   $('#application-version-tips').show();
			   $('#application_version').html(json.version);
			   $('#application_down').attr('href',json.downloadUrl);
		   }
		   if(json.accredit=='no'){
			   $('#application-accredit-tips').show();
		   }
		   if(json.licenseStatus)$('#licenseStatus').html(json.licenseStatus);
	   }
	});
}
$(function(){
    getLastVersion();
})