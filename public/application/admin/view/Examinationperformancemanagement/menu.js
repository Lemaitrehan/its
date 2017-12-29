var treeObj,grid,url,data_type,grade_id;

$(function(){
    data_type = $('#layout').attr('data-type');
	if(data_type == 1){
		url = 'admin/Examinationperformancemanagement/indexEducation';
	}else{
		url = 'admin/Examinationperformancemanagement/indexSkill';
	}
	$("#layout").ligerLayout({leftWidth:'230',space: 8,allowLeftCollapse:false,allowCenterBottomResize:false});
	$('#menuTree').height(MBIS.pageHeight()-36);
	//tree
	var setting = {
	      view: {
	           selectedMulti: false,
	           dblClickExpand:false
	      },
	      async: {
	           enable: true,
	           url:MBIS.U(url),
	           autoParam:["id", "name=n", "level=lv"]
	      },
	      callback:{
	           onClick: onClick
	      }
	};
	$.fn.zTree.init($("#menuTree"), setting);
	treeObj = $.fn.zTree.getZTreeObj("menuTree");	
	
	
})
//节点点击加载事件
function onClick(e,treeId, treeNode){
	var nodes = treeObj.getSelectedNodes();
	var node = nodes[0];
	if(!node.isParent && data_type == 1 && ( treeNode.school_id || 
		treeNode.major_id || treeNode.grade_id || treeNode.exam_id || treeNode.subject_id ) ){
 		 //学校专业
		 if(treeNode.school_id){
			 var parameter = {school_id:treeNode.school_id};
		 //专业下的年级	 
		 }else if(treeNode.major_id){
			 var parameter = {major_id:treeNode.major_id};
		 //查找年级下的考试	 
		 }else if(treeNode.grade_id){
			var parameter = {grade_id:treeNode.grade_id};
		 //查找考试下面所有科目成绩	
		 }else if(treeNode.exam_id){
			 initGrid(data_type,treeNode.exam_id);
			 return false;
		 }else{
			 return false;
		 }
		 $.post(MBIS.U(url),parameter,function(data,textStatus){
			 if( data.length  > 0 ){
		       treeObj.addNodes(treeNode, data);
			 }
	    });
	}
}

//新增学历管理
$('.add').click(function(){
	if(data_type == 1){
        window.location.href = MBIS.U('admin/Examinationmanagement/addEducation')+'?grade_id='+grade_id;
	}else{
		window.location.href = MBIS.U('admin/Examinationmanagement/addSkill')+'?grade_id='+grade_id;
	}
})


function initGrid(type,exam_id){
	data_type = $('#layout').attr('data-type');
	if(data_type == 1){
		url = 'admin/Examinationperformancemanagement/indexEducation';
	}else{
		url = 'admin/Examinationperformancemanagement/indexSkill';
	}
	grid = $("#maingrid").ligerGrid({
		url:MBIS.U(url)+'?action=student',
		pageSize:MBIS.pageSize,
		pageSizeOptions:MBIS.pageSizeOptions,
		height:'99%',
        width:'100%',
        minColToggle:7,
        rownumbers:true,
        columns: [
            { display: '学员ID', name: 'userId',width:50,isSort: false,},
            { display: '学员编号', name: 'student_no',isSort: false},
            { display: '学员姓名', name: 'name',isSort: false},
            { display: '考试科目', name: 'subject_name',isSort: false},
            { display: '考试日期', name: 'exam_time',isSort: false},
            { display: '考试分数', name: 'subject_score',isSort: false},
            { display: '学历老师', name: 'teacher',isSort: false},
            { display: '学历老师联系电话',name: 'teacher_phone',isSort: false},
	        { display: '操作', name: 'op',width:60,isSort: false,
	        	render: function (rowdata){
		            //var h = "";
		            //if(MBIS.GRANT.SFJL_02)h += "<a href='javascript:toEdit("+rowdata["fee_id"]+",1)'>修改</a> ";
		            //if(MBIS.GRANT.SFJL_03)h += "<a href='javascript:toDel("+rowdata["fee_id"]+")'>删除</a> ";
		            //if(MBIS.GRANT.SFJL_04)h += "<a href='javascript:toDetail("+rowdata["fee_id"]+")'>查看详情</a> ";
		           // return h;
	        	}
	        }
        ]
    });

}


       		
