var grid;
var combo;
var id = 0;
var send_type ;
var content;
function initCombo(send_type1,content1){
	send_type = send_type1;
	content    = content1;
}

$(function(){
	$('select[name="send_type"]').change(function(){
		 var send_type = $(this).val(),
		     id        = $('#notice_id').val();
		 if(id>0){
			 var url = MBIS.U('admin/noticetmpl/addNoticeTmpl');
		 }else{
			 var url = MBIS.U('admin/noticetmpl/editNoticeTmpl');
		 }
		 window.location.href = url+'?send_type='+send_type+'&id='+id; 
	})
	  //编辑器
	if( send_type == 2){	
	   KindEditor.ready(function(K) {
	    editor1 = K.create('textarea[name="content"]', {
	      height:'350px',
	      allowFileManager : false,
	      allowImageUpload : true,
	      items:[
	              'source', '|', 'undo', 'redo', '|', 'preview', 'print', 'template', 'code', 'cut', 'copy', 'paste',
	              'plainpaste', 'wordpaste', '|', 'justifyleft', 'justifycenter', 'justifyright',
	              'justifyfull', 'insertorderedlist', 'insertunorderedlist', 'indent', 'outdent', 'subscript',
	              'superscript', 'clearhtml', 'quickformat', 'selectall', '|', 'fullscreen', '/',
	              'formatblock', 'fontname', 'fontsize', '|', 'forecolor', 'hilitecolor', 'bold',
	              'italic', 'underline', 'strikethrough', 'lineheight', 'removeformat', '|','image','table', 'hr', 'emoticons', 'baidumap', 'pagebreak',
	              'anchor', 'link', 'unlink', '|', 'about'
	      ],
	      afterBlur: function(){ this.sync(); }
	    });
	    editor1.insertHtml(content.content);
	  });
	}	
})

function initGrid(){
	grid = $("#maingrid").ligerGrid({
		url:MBIS.U('admin/noticetmpl/index'),
		pageSize:MBIS.pageSize,
		pageSizeOptions:MBIS.pageSizeOptions,
		height:'99%',
        width:'100%',
        minColToggle:6,
        rownumbers:true,
        columns: [
            { display: 'ID', name: 'notice_id',width:100,isSort: false,},
            { display: '模板类型', name: 'tmpl_type',width:200,isSort: false},
	        { display: '发送方式', name: 'send_type',width:100,isSort: false},
	        { display: '模板主题', name: 'title',width:200,isSort: false},
	        { display: '模板内容', name: 'content',isSort: false},
	        { display: '操作', name: 'op',width:100,isSort: false,
	        	render: function (rowdata){
		            var h = "";
		            if(MBIS.GRANT.TZMB_02)h += "<a href='javascript:toEdit("+rowdata["notice_id"]+")'>修改</a> ";
		            if(MBIS.GRANT.TZMB_03)h += "<a href='javascript:toDel("+rowdata["notice_id"]+")'>删除</a> ";
		            return h;
	        	}
	        }
        ]
    });

}

function loadGrid(){
	grid.set('url',MBIS.U('admin/noticetmpl/index','key='+$('#key').val()));
}
function refresh(){
	if($('#key').val() !== ''){
		$('#key').val('');
	}
  	grid.set('url',MBIS.U('admin/noticetmpl/index'));
}
function toEdit(id){
	location.href=MBIS.U('admin/noticetmpl/editNoticeTmpl','id='+id);
}

function toDetail(id){
	location.href=MBIS.U('admin/noticetmpl/toDetail','id='+id);
}

function toEdits(id){
    var params = MBIS.getParams('.ipt');
    params.id = id;
    if(id >0){
    	var url = MBIS.U('admin/noticetmpl/editNoticeTmpl');
    }else{
    	var url = MBIS.U('admin/noticetmpl/addNoticeTmpl');
    }
    var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
	$.post(url,params,function(data,textStatus){
		  layer.close(loading);
		  var json = MBIS.toAdminJson(data);
		  if(json.status=='1'){
		    	MBIS.msg(json.msg,{icon:1});
		        setTimeout(function(){ 
			    	location.href=MBIS.U('admin/noticetmpl/index');
		        },1000);
		  }else{
		        MBIS.msg(json.msg,{icon:2});
		  }
	});
}

function toDel(id){
	var box = MBIS.confirm({content:"您确定要删除该数据吗?",yes:function(){
	           var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
	           	$.post(MBIS.U('admin/noticetmpl/del'),{id:id},function(data,textStatus){
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
