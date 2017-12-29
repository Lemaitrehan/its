            var tab = null;
            var accordion = null;
            var tree = null;
            var tabItems = [];
            var menuId = 75;//主页默认显示的模块
            
			function getLeft(indexUrl){
					//var indexUrl = MBIS.U('admin/users/index_u') ;
					var html = '<div id="layout1" style="width:99.2%; margin:0 auto; margin-top:4px; ">' 
						+'<div position="left"  title="主要菜单" id="accordion1">'
						+'</div>'
						+'<div position="center" id="framecenter">' 
						+'<div tabid="home" title="<span class=\'xxx\'>我的主页</span>" style="height:300px" >';
					  if(indexUrl != null && indexUrl !=""){
						  html += '<iframe frameborder="0" name="home" id="home" src="'+indexUrl+'"></iframe>';
					  }else{
						  var indexUrl1 = MBIS.U('admin/Noindex/index') ;
						  html += '<iframe frameborder="0" name="home" id="home" src="'+indexUrl1+'"></iframe>';
					  }	
					  html +='</div>'
						 +'</div>' 
						+'</div>';
				   return html;		
			}
			//首页显示
			function indexShowHtml(){
				 var html  ='<div class="kjtype" style="width:99.2%; margin:0 auto; margin-top:4px; ">' 
						        +'<ul style="margin:100px auto;width:80%;">'
						            +'<li  data-value="1" class="type" style="float:left;width:30%;background-color:#ccc;height:100px;line-height:100px;text-align:center;font-size:28px;font-weight:bold;">自考</li>'
						            +'<li  data-value="2" class="type" style="float:left;width:30%;background-color:#ccc;height:100px;line-height:100px;text-align:center;font-size:28px;font-weight:bold;margin-left:10px;">成考</li>'
						            +'<li  data-value="3" class="type" style="float:left;width:30%;background-color:#ccc;height:100px;line-height:100px;text-align:center;font-size:28px;font-weight:bold;margin-left:10px;">网教</li>'
						        +'</ul>' 
						   +'</div>';
				 return html;
			}
                
            $(function ()
            {
            		
            /*	setInterval(showalert, 4000); 2017-5-11 需求变更 暂时不用
            	var  timeObj ; 
            	function showalert() 
            	{  clearInterval(timeObj );
	            	$.post(MBIS.U('admin/Taskmessage/index'),{action:'sms'},function(data,textStatus){
	            	     timeObj  =   setInterval(function(){
	            	    		if(data.num >0 ){
	            	    			$("#taskSms").fadeOut(200).fadeIn(200);
	            	    		}else{
	            	    			clearInterval(timeObj );
	            	    		}
	            	      },400);
	            	
	            	});
            	}*/
            	
            	
            	
            	$(document).on('click','.type',function(){
            		var type = $(this).attr('data-value');
            		menu(menuId,type);
            	})
            	
            	//总标题
            	$('.tab_total').click(function(){
            		$('.l-selected').removeClass('l-selected');
            		$(this).addClass('l-selected');
            		menuId = $(this).attr('data-id');
            		menu(menuId);
            	})
            	$('#taskSms').click(function(){
            		var nowthis =$('.tab_total[data-id="72"]');
            		$('.l-selected').removeClass('l-selected');
            		$(nowthis).addClass('l-selected');
            		menu(72,0,1);
            	})
            	
            	menu(menuId);
            	//第一步加载  left 大分类
            	function menu(menuId,type,is_sms){
            		if(type   == undefined ){type   = 0};
            		if(is_sms == undefined ){is_sms = 0};
            	    if( menuId == 75 && type==0 ){
            			var html = indexShowHtml();
            			 $('.kjtype').remove();
            			 $('#layout1').remove();
            		     $('#tabs').after(html); 	
            			return false;
            		}
	            	$.post(MBIS.U('admin/index/getSubMenus'),{id:menuId,type:type},function(data,textStatus){
						var indexUrl = null;										
		           		var json = MBIS.toAdminJson(data);
		           		//return false;
		           		var html =''; 
		                    if(json && json.length>0){
		           			    $.each(json,function(i,e){
			           			   	html += '<div type="xx'+menuId+'" title="'+e.menuName+'">';
			           			        html +='<div title="'+e.menuName+'" style=" height:7px;"></div>';
			           			           $(e.list).each(function(j,v){
			           			        	   var tabId        = v.menuId;
			           			    		   //var privilegeUrl = MBIS.U(v.privilegeUrl);
                                               var thisurl      = MBIS.blank(v.privilegeUrl).split('?');
                                               var privilegeUrl = MBIS.U(MBIS.blank(v.privilegeUrl,''),thisurl[1]?thisurl[1]:null);
											       if(i==0 && j==0){
											    	   indexUrl = privilegeUrl;
											    	};
											       
                                               //MBIS.U(MBIS.blank(json[i]['list'][j]['privilegeUrl'],''),thisurl[1]?thisurl[1]:null)
			           			    		   var menuName     = v.menuName; 
				           			           html +='<a class="l-link" href="javascript:f_addTab(\''+tabId+'\',\''+menuName+'\',\''+privilegeUrl+'\')">'+menuName+'</a>'
			           			    	   })
			           			        html +='</div>' ;
		           			    })
		           		    }
						 indexUrl = getLeft(indexUrl);
						 $('#layout1').remove();
            		     $('#tabs').after(indexUrl); 	
		                 $('#accordion1').html(html);
		                 xx();
		                 if(is_sms){
		                	 $('.l-link').each(function(){
		                		 if( $.trim($(this).html())== '通知模板列表' ){
		                		   $('.l-accordion-content[type="xx'+menuId+'"]:eq(0)').attr('style','display: none;');
		                		   $('.l-accordion-content[type="xx'+menuId+'"]:last()').attr('style','display: block;');
		                		   f_addTab('192','任务列表','/index.php/admin/Taskmessage/index.html');
		                		 }
		                	 })
		                 }
		               })
            	}
            	
            	function xx(){
	                //布局
	                $("#layout1").ligerLayout({
	                    leftWidth: 190,
	                    height: '100%',
	                    heightDiff: -34,
	                    space: 4,
	                    onHeightChanged: f_heightChanged,
	                    onLeftToggle: function ()
	                    {
	                        tab && tab.trigger('sysWidthChange');
	                    },
	                    onRightToggle: function ()
	                    {
	                        tab && tab.trigger('sysWidthChange');
	                    }
	                });
	
	                var height = $(".l-layout-center").height();
	
	                //Tab
	                tab = $("#framecenter").ligerTab({
	                    height: height,
	                    showSwitchInTab : true,
	                    showSwitch: true,
	                    onAfterAddTabItem: function (tabdata)
	                    {
	                        tabItems.push(tabdata);
	                        saveTabStatus();
	                    },
	                    onAfterRemoveTabItem: function (tabid)
	                    {  
	                        for (var i = 0; i < tabItems.length; i++)
	                        {
	                            var o = tabItems[i];
	                            if (o.tabid == tabid)
	                            {
	                                tabItems.splice(i, 1);
	                                saveTabStatus();
	                                break;
	                            }
	                        }
	                    },
	                    onReload: function (tabdata)
	                    { 
	                        var tabid = tabdata.tabid;
	                        addFrameSkinLink(tabid);
	                    }
	                });
	                //面板
	                $("#accordion1").ligerAccordion({
	                    height: height - 24, speed: null
	                });
	
	                $(".l-link").hover(function ()
	                {
	                    $(this).addClass("l-link-over");
	                }, function ()
	                {
	                    $(this).removeClass("l-link-over");
	                });
            	}
               
            	/*//主页刷新
                $(document).on('click','#framecenter li[tabid="home"]',function(){
                	$('#home').attr('src',$('#home').attr('src') );
                })
                
                $(document).on('click','#framecenter li[tabid="home"] a',function(){
                	
                })*/
                
              /*  function openNew(url)
                {  
                    var jform = $('#opennew_form');
                    if (jform.length == 0)
                    {
                        jform = $('<form method="post" />').attr('id', 'opennew_form').hide().appendTo('body');
                    } else
                    {
                        jform.empty();
                    } 
                    jform.attr('action', url);
                    jform.attr('target', '_blank'); 
                    jform.trigger('submit');
                };


                tab = liger.get("framecenter");
                accordion = liger.get("accordion1");
                tree = liger.get("tree1");
                $("#pageloading").hide();*/
               // css_init();
                //pages_init();
            });
            
            function f_heightChanged(options)
            {  
                if (tab)
                    tab.addHeight(options.diff);
                if (accordion && options.middleHeight - 24 > 0)
                    accordion.setHeight(options.middleHeight - 24);
            }
            
            function f_addTab(tabid, text, url)
            {  
            	if(url==''){
					 url = MBIS.U('admin/Noindex/index') ;
            	}
            	if(tab == undefined ){
            		return false;
            	}
                tab.addTabItem({
                    tabid: tabid,
                    text: text,
                    url: url,
                    callback: function ()
                    {   
                        ///addShowCodeBtn(tabid); 
                        //addFrameSkinLink(tabid); 
                    }
                });
            }
           
          
            function addFrameSkinLink(tabid)
            {
                var prevHref = getLinkPrevHref(tabid) || "";
                var skin = getQueryString("skin");
                if (!skin) return;
                skin = skin.toLowerCase();
                attachLinkToFrame(tabid, prevHref + skin_links[skin]);
            }
            var skin_links = {
                "aqua": "lib/ligerUI/skins/Aqua/css/ligerui-all.css",
                "gray": "lib/ligerUI/skins/Gray/css/all.css",
                "silvery": "lib/ligerUI/skins/Silvery/css/style.css",
                "gray2014": "lib/ligerUI/skins/gray2014/css/all.css"
            };
            function pages_init()
            {
                var tabJson = $.cookie('liger-home-tab'); 
                if (tabJson)
                { 
                    var tabitems = JSON2.parse(tabJson);
                    for (var i = 0; tabitems && tabitems[i];i++)
                    {  
                        f_addTab(tabitems[i].tabid, tabitems[i].text, tabitems[i].url);
                    } 
                }
            }
            function saveTabStatus()
            { 
                $.cookie('liger-home-tab', JSON2.stringify(tabItems));
            }
            function css_init()
            {
                var css = $("#mylink").get(0), skin = getQueryString("skin");
                $("#skinSelect").val(skin);
                $("#skinSelect").change(function ()
                { 
                    if (this.value)
                    {
                        location.href = "index.htm?skin=" + this.value;
                    } else
                    {
                        location.href = "index.htm";
                    }
                });

               
                if (!css || !skin) return;
                skin = skin.toLowerCase();
                $('body').addClass("body-" + skin); 
                $(css).attr("href", skin_links[skin]); 
            }
            function getQueryString(name)
            {
                var now_url = document.location.search.slice(1), q_array = now_url.split('&');
                for (var i = 0; i < q_array.length; i++)
                {
                    var v_array = q_array[i].split('=');
                    if (v_array[0] == name)
                    {
                        return v_array[1];
                    }
                }
                return false;
            }
            function attachLinkToFrame(iframeId, filename)
            { 
                if(!window.frames[iframeId]) return;
                var head = window.frames[iframeId].document.getElementsByTagName('head').item(0);
                var fileref = window.frames[iframeId].document.createElement("link");
                if (!fileref) return;
                fileref.setAttribute("rel", "stylesheet");
                fileref.setAttribute("type", "text/css");
                fileref.setAttribute("href", filename);
                head.appendChild(fileref);
            }
            function getLinkPrevHref(iframeId)
            {
                if (!window.frames[iframeId]) return;
                var head = window.frames[iframeId].document.getElementsByTagName('head').item(0);
                var links = $("link:first", head);
                for (var i = 0; links[i]; i++)
                {
                    var href = $(links[i]).attr("href");
                    if (href && href.toLowerCase().indexOf("ligerui") > 0)
                    {
                        return href.substring(0, href.toLowerCase().indexOf("lib") );
                    }
                }
            }
   

/*$(window).resize(function(){
	var h = MBIS.pageHeight()-100;
    $('.l-tab-content').height(h);
    $('.l-tab-content-item').height(h);
    $('.wst-iframe').each(function(){
    	$(this).height(h-26);
    });
    $('.wst-accordion').each(function(){
    	liger.get($(this).attr('id')).setHeight(h-26);
    });
});
function changeTab(obj,n){
    var ltab = liger.get("wst-ltabs-"+n);
    ltab.setHeader("wst-ltab-"+n, $(obj).text());
    $('#wst-lframe-'+n).attr('src',$(obj).attr('url'));
}
function initTabMenus(menuId){
	$.post(MBIS.U('admin/index/getSubMenus'),{id:menuId},function(data,textStatus){
		 var json = MBIS.toAdminJson(data);
		 var html = [];
		 html.push('<div id="wst-layout-'+menuId+'" style="width:99.2%; margin:0 auto; margin-top:4px; ">'); 
		 html.push('<div position="left" id="wst-accordion-'+menuId+'" title="管理菜单" class="wst-accordion">');
         if(json && json.length>0){
			 for(var i=0;i<json.length;i++){
       		 html.push('<div title="'+json[i]['menuName']+'">'); 
       		 html.push('     <div style=" height:7px;"></div>');
       		 if(json[i]['list']){
	        		 for(var j=0;j<json[i]['list'].length;j++){
                         var thisurl = MBIS.blank(json[i]['list'][j]['privilegeUrl']).split('?');
		        		 html.push('<a class="wst-link" href="javascript:void(0)" url="'+MBIS.U(MBIS.blank(json[i]['list'][j]['privilegeUrl'],''),thisurl[1]?thisurl[1]:null)+'" onclick="javascript:changeTab(this,'+menuId+')">'+json[i]['list'][j]['menuName']+'</a>');  
	        		 }
       		 }
       		 html.push('     </div> ');
			 }
		 }
		 html.push('</div>');
		 html.push('<div id="wst-ltabs-'+menuId+'" position="center" class="wst-lnavtabs">'); 
		 html.push('  <div tabid="wst-ltab-'+menuId+'" title="我的主页" style="height:300px" >');
		 html.push('      <iframe frameborder="0" class="wst-iframe" id="wst-lframe-'+menuId+'" src="'+(initFrame?"":MBIS.U('admin/users/index_u'))+'"></iframe>');
		 html.push('  </div>');
		 html.push('</div>'); 
		 html.push('</div>');
		 initFrame = true;
		 $('#wst-tab-'+menuId).html(html.join(''));
		 $("#wst-layout-"+menuId).ligerLayout({
	         leftWidth: 190,
	         height: '100%',
	         space: 0
	     });
		 var height = $(".l-layout-center").height();
		 $("#wst-accordion-"+menuId).ligerAccordion({
		      height: height - 24, speed: null
		 });
		 $("#wst-ltabs-"+menuId).ligerTab({
		      height: height,
		      changeHeightOnResize:true,
		      showSwitchInTab : false,
		      showSwitch: false
	     });
		 if(initFrame)$('.l-tab-loading').remove();
         if(menuId!=1 && $('#wst-accordion-'+menuId+' .l-accordion-content').length>0)
            $('#wst-accordion-'+menuId+' .l-accordion-content').find('a').eq(0).click();
	 });
}
var mMgrs = {},tab,initFrame = false;
$(function (){   
    tab = $("#wst-tabs").ligerTab({
         height: '100%',
         changeHeightOnResize:true,
         showSwitchInTab : true,
         showSwitch: true,
         onAfterSelectTabItem:function(n){
        	 var menuId = n.replace('wst-tab-','');
        	 if(!mMgrs['m'+menuId]){
	        	 var ltab = $("#wst-tab-"+menuId);
	        	 mMgrs['m'+menuId] = true;
	        	 if(menuId=='market'){
	        		 $('#wst-market').attr('src','http://market.shangtaosoft.com');
	        	 }else{
	        	     initTabMenus(menuId);
        	     }
        	 }
         }
    });
    var tabId = tab.getSelectedTabItemID();
    mMgrs['m'+tabId.replace('wst-tab-','')] = true;
    initTabMenus(tabId.replace('wst-tab-',''));
    $('.l-tab-content').height(MBIS.pageHeight()-70);
    $('.l-tab-content-item').height(MBIS.pageHeight()-70);
    $('.wst-iframe').each(function(){
    	$(this).height(h-10);
    });
});
*/
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
function logout(){
	MBIS.confirm({content:"您确定要退出该系统吗?",yes:function(){
		var loading = MBIS.msg('正在退出，请稍后...', {icon: 16,time:60000});
		$.post(MBIS.U('admin/index/logout'),MBIS.getParams('.ipt'),function(data,textStatus){
			layer.close(loading);
			var json = MBIS.toAdminJson(data);
			if(json.status=='1'){
				location.reload();
			}
		});
	}});
}
function clearCache(){
	var loading = MBIS.msg('正在清理缓存，请稍后...', {icon: 16,time:60000});
	$.post(MBIS.U('admin/index/clearcache'),{},function(data,textStatus){
		layer.close(loading);
		var json = MBIS.toAdminJson(data);
		if(json.status && json.status=='1'){
			MBIS.msg(json.msg,{icon:1});
		}else{
			MBIS.msg(json.msg,{icon:2});
		}
	});
}
function editPassBox(){
	var w = MBIS.open({type: 1,title:"修改密码",shade: [0.6, '#000'],border: [0],content:$('#editPassBox'),area: ['450px', '250px'],
	    btn: ['确定', '取消'],yes: function(index, layero){
	    	$('#editPassFrom').isValid(function(v){
	    		if(v){
		        	var params = MBIS.getParams('.ipt');
		        	var ll = MBIS.msg('数据处理中，请稍候...');
				    $.post(MBIS.U('admin/Staffs/editMyPass'),params,function(data){
				    	layer.close(ll);
				    	var json = MBIS.toAdminJson(data);
						if(json.status==1){
							MBIS.msg(json.msg, {icon: 1});
							layer.close(w);
						}else{
							MBIS.msg(json.msg, {icon: 2});
						}
				   });
	    		}})
        }
	});
}

function showFaq(){
	var w = MBIS.open({type: 2,title:"常见问题",shade: [0.6, '#000'],border: [0],content:MBIS.U('admin/faq/index'),area: ['1000px', '500px'],
	    btn: [],yes: function(index, layero){
	    	
        }
	});
}
            
            
            