$(function(){
	$('.btn').click(function(){
         changeStyle($(this),$(this).attr('dataid'));
	})
})
function changeStyle(obj,id){
	if(obj.hasClass('btn-disabled'))return;
	var box = MBIS.confirm({content:"您确定要使用这套风格吗?",yes:function(){
		var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
		$.post(MBIS.U('admin/styles/changeStyle'),{id:id},function(data,textStatus){
			layer.close(loading);
			var json = MBIS.toAdminJson(data);
			if(json.status=='1'){
				MBIS.msg(json.msg,{icon:1});
				layer.close(box);
				$('.btn-disabled').attr('disabled',false).val('启用').removeClass('btn-disabled').addClass('btn-blue');
				$('.style_'+id).removeClass('btn-blue').addClass('btn-disabled').attr('disabled',true).val('应用中');
			}else{
				MBIS.msg(json.msg,{icon:2});
			}
		});
	}});
}