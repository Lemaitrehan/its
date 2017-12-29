var grid;
var combo;
function initGrid(){
	grid = $("#maingrid").ligerGrid({
		url:MBIS.U('admin/Review/index'),
		pageSize:MBIS.pageSize,
		pageSizeOptions:MBIS.pageSizeOptions,
		height:'99%',
        width:'100%',
        minColToggle:6,
        rownumbers:true,
        columns: [
            { display: 'ID', name: 'id',isSort: false},
	        { display: '审核页面', name: 'menuName',isSort: false},
            { display: '审核人', name: 'trueName',isSort: false,},
            { display: '部门',name: 'bm_name',width: 100, isSort: false},
	        { display: '手机', name: 'mobile',width: 200,isSort: false},
	        { display: '操作', name: 'op',width: 100,isSort: false,
	        	render: function (rowdata){
		            var h = "";
		                h += '<a href="javascript:toEdit('+rowdata['id']+')">编辑</a> ';
		                h += '<a href="javascript:toDel('+rowdata['id']+')">删除</a>';
		            	//if(MBIS.GRANT.XYZL_02)h += "<a href='javascript:toEdit("+type_id+","+rowdata["school_id"]+")'>修改</a> ";
		            	//if(MBIS.GRANT.XYZL_03)h += "<a href='javascript:toDel("+type_id+","+rowdata["school_id"]+")'>删除</a> ";
		            return h;
	        	}
	        }
        ]
    });

}


function loadGrid(){
	grid.set('url',MBIS.U('admin/Review/index','search_word='+$('#search_word').val() ));
}
function refresh(){
	if($('#search_word').val() !== ''){
		$('#search_word').val('');
	}
    grid.set('url',MBIS.U('admin/Review/index'));
}
var review_id;
function toEdit(id){
	 if(id){
		  review_id = id;
		 var parm = {act:'xx',review_id:review_id};
		 var url  = MBIS.U('admin/Review/editReview');
	 }else{
		 var parm = {act:'xx'};
		 var url  = MBIS.U('admin/Review/addReview') ;
		 
	 }
   	 $.post(url,parm,function(data){
   		 var html = '<option value="">请选择</option>',html1=html,html2=html;
         if(data){
        	 $(data.page).each(function(i,e){
        		 var is_selected = data.menus_id == e.menuId?' selected':'';
        		 html1+= '<option value="'+e.menuId+'"'+is_selected+'>'+e.menuName+'</option>';
        	 })
        	 $(data.person).each(function(i,e){
        		 var is_selected = data.review_person_id == e.staff_id?' selected':'';
        		 html2+= '<option value="'+e.staff_id+'"'+is_selected+'>'+e.name+'</option>';
        	 })
         }
         $('#list').html(html1);
         $('#review_name').html(html2);
   	 })
   	 
	 $.ligerDialog.open({ target: $("#target1") ,width:600, height:500,
		                   title:'设置审核页面',
		     buttons: [  { text: '保存', onclick: function (i, d) {submitData(); $("input").ligerHideTip(); d.hide(); }}, 
		                 { text: '关闭', onclick: function (i, d) { $("input").ligerHideTip(); d.hide(); }} 
		             ]   
	 });
	
}

//审核提交
function submitData(){
	var list_id        = $('#list').val();
	var review_name_id = $('#review_name').val();
	if( !list_id || !review_name_id ){
		alert('必填！！！');
		return false;
	}
	if(review_id){
		var url = MBIS.U('admin/Review/editReview');
		var parm = {act:'editData',list_id:list_id,review_name_id:review_name_id,id:review_id};
	}else{
		var url  = MBIS.U('admin/Review/addReview');
		var parm = {act:'addData',list_id:list_id,review_name_id:review_name_id};
	}
	$.post(url,parm,function(data){
  	      if(data.status<=0){
  	    	    MBIS.msg(data.msg,{icon:2});
  	      }else{
  	    	 initGrid();
  	      }
	})
	
	
}


function toDel(id){
	var box = MBIS.confirm({content:"您确定要删除该数据吗?",yes:function(){
	           var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
	           	$.post(MBIS.U('admin/Review/delReview'),{id:id},function(data,textStatus){
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


function formatRepo(repo) {
    return repo.name;
}
function formatRepoProvince(repo) {
    if (repo.name) {
        return repo.name;
    } else {
        return repo.text;
    }
}
  function select2(name){
      $('#'+name).select2({
          placeholder: '请选择',
          allowClear: true,
          minimumInputLength: 1,
          width: 160,
          ajax: {
              url: "/index.php/admin/testli/index.html",
              dataType: 'json',
              delay: 250,
              data: function (params) {
                  return {
                      search_name: params.term, // search term
                  };
              },
              processResults: function (data, params) {
                  return {
                      results: data
                  };
              },
          },
          templateResult: formatRepo, // omitted for brevity, see the source of this page
          templateSelection: formatRepoProvince // omitted for brevity, see the source of this page
      });
  }