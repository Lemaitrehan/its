function initSummary(){
	 var loading = MBIS.msg('正在获取数据，请稍后...', {icon: 16,time:60000});
	 $.post(MBIS.U('admin/images/summary'),{rnd:Math.random()},function(data,textStatus){
	       layer.close(loading);
	       var json = MBIS.toAdminJson(data);
	       if(json.status==1){
	    	   json = json.data;
	    	   var html = [],tmp,i=1,divLen = 0;
	    	   for(var key in json){
	    		   if(key=='_MBISSummary_')continue;
	    		   tmp = json[key];
	    		   html.push('<tr class="l-grid-row wst-grid-tree-row '+(((i%2==0))?"l-grid-row-alt":"")+'" height="28">'
	    				     ,'<td class="l-grid-row-cell l-grid-row-cell-rownumbers" style="width:26px;">'+(i++)+'</td>'
	    				     ,'<td class="l-grid-row-cell">'+MBIS.blank(tmp.directory,'未知目录')+'('+key+')'+'</td>'
	    				     ,'<td class="l-grid-row-cell">'+getCharts(json['_MBISSummary_'],tmp.data['1'],tmp.data['0'])+'</td>'
	    				     ,'<td class="l-grid-row-cell" nowrap>'+tmp.data['1']+'/'+tmp.data['0']+'</td>'
	    				     ,'<td class="l-grid-row-cell"><a href="'+MBIS.U('admin/images/lists','keyword='+key)+'">查看详情</a></td>');
	    	   }
	    	   $('#list').html(html.join(''));
	       }else{
	           MBIS.msg(json.msg,{icon:2});
	       }
	 });
}
function getCharts(maxSize,size1,size2){
	var w = MBIS.pageWidth()-400;
	var tlen = (parseFloat(size1,10)+parseFloat(size2,10))*w/maxSize+1;
	var s1len = parseFloat(size1,10)*w/maxSize;
	var s2len = parseFloat(size2,10)*w/maxSize;
	return ['<div style="width:'+tlen+'px"><div style="height:20px;float:left;width:'+s1len+'px;background:green;"></div><div style="height:20px;float:left;width:'+s2len+'px;background:#ddd;"></div></div>'];
}
var grid;
function initGrid(){
	
	grid = $("#maingrid").ligerGrid({
		url:MBIS.U('admin/images/pageQuery','keyword='+$('#key').val()+"&isUse="+$('#isUse').val()),
		pageSize:MBIS.pageSize,
		pageSizeOptions:MBIS.pageSizeOptions,
		height:'99%',
        width:'100%',
        rowHeight:50,
        minColToggle:6,
        rownumbers:true,
        columns: [
	        { display: '图片', name: 'imgPath',isSort: false,render: function (rowdata, rowindex, value){
	        	return '<div style="margin:5px;"><img height="40" width="40" src="'+MBIS.conf.ROOT+'/'+value+'"/></div>';
	        }},
	        { display: '上传者', name: 'userName',isSort: false,render: function (rowdata, rowindex, value){
	        	if(rowdata['fromType']==1){
	        		return "【职员】"+rowdata['loginName'];
	        	}else{
	        		if(MBIS.blank(rowdata['userType'])==''){
	        			return '游客';
	        		}else{
	        			if(rowdata['userType']==1){
	        				return "【商家:"+rowdata['shopName']+"】"+rowdata['loginName'];
	        			}else{
	        				return rowdata['loginName'];
	        			}
	        		}
	        	}
	        }},
	        { display: '文件大小(M)', name: 'imgSize',isSort: false},
	        { display: '状态', name: 'isUse',isSort: false,render: function (rowdata, rowindex, value){
	        	return (value==1)?'有效':'无效';
	        }},
	        { display: '上传时间', name: 'createTime',isSort: false},
	        { display: '操作', name: 'op',isSort: false,render: function (rowdata, rowindex, value){
	        	var h = '<a href="javascript:toView('+rowdata['imgId']+',\''+rowdata['imgPath']+'\')">查看</a>';
	        	if(MBIS.GRANT.TPKJ_04)h += '&nbsp;&nbsp;<a href="javascript:toDel('+rowdata['imgId']+')">删除</a>';
	        	return h;
	        }}
        ]
    });
	loadGrid();
}
function loadGrid(){
	grid.set('url',MBIS.U('admin/images/pageQuery','keyword='+$('#key').val()+"&isUse="+$('#isUse').val()))
}
function toView(id,img){
    parent.showBox({title:'图片详情',type:2,content:MBIS.U('admin/images/checkImages','imgPath='+img),area: ['700px', '510px'],btn:['关闭']});
}
function toDel(id){
	var box = MBIS.confirm({content:"您确定要删除该图片吗?<br/>注意：删除该图片后将不可找回!",yes:function(){
		var loading = MBIS.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
		$.post(MBIS.U('admin/images/del'),{id:id},function(data,textStatus){
			layer.close(loading);
			var json = MBIS.toAdminJson(data);
			if(json.status=='1'){
				MBIS.msg("操作成功",{icon:1});
				layer.close(box);
				grid.reload();
			}else{
				MBIS.msg(json.msg,{icon:2});
			}
		});
	}});
}