/**
 * @author merauy@gmail.com
 * @resource http://www.goodxyx.com/jquery/plugins/uploadreplace.html
 * @Copyright(c)2010~2014 
 * @version 1.0.0
 * @direction javascript异步上传文件插件
 * @comment 所谓的AJAX异步上传附件，以目前（2010-10-01）的知识来说不准确。
 *   JS没有提供ajax直接上传的接口，目前大部分的异步上传控件是FLASH来实现。
 *   再ajax出现之前，JS就可以实现异步上传文件功能了
 *   本代码清晰展示了JS异步上传的实现原理 
 *   该上传插件可以不依赖于jQuery，有了jQuery使得代码更少而已
 *   该插件仅用于学习与分享，希望能帮助大家。
 *  
 */
(function(window, undefined){
	var czyx = {
		_tid:0,
		getNextTempId:function(){
			var id="czyx"+(++czyx._tid);
			return $('#'+id).length ? czyx.getNextTempId() : id
		},
		toFormString:	function(obj,baseName){
			if(baseName === undefined){
				baseName = '' ;	
			}
			var r = [] ;
			var getInputName = function(n, bn){
				return bn ? bn+'['+ n +']' : n ;
			};
			var getInputValue = function(v){
				switch(typeof v){
					case 'boolean':
						return v ? 'true' : 'false' ;
					case 'number':
						return v ;
					case 'function' :
						return getInputValue(v());
					case 'string':
						return czyx.quote(v);
					default:
						return 'null' ;
				}
			};
			for(var i in obj){
				if(typeof obj[i] == 'object' && obj[i]){
					r[r.length] = czyx.toFormString(obj[i], getInputName(i, baseName)) ;
				}else{
					r[r.length] = '<input type="hidden" name="' 
						+ getInputName(i, baseName) 
						+ '" value="' + getInputValue(obj[i]) 
						+ '" />' ;
				}
			}
			return r.join('');
		},
		goToUrl:function(url, data, type, target){
			data = data || {} ;
			if(typeof type != 'undefined' && type.toUpperCase() == 'POST'){
				if(target){
					$('<form method="post" target="'+target+'">'
						+ czyx.toFormString(data)
						+ '</form>'
					).attr('action', url)
					.appendTo('body').submit().remove();
				}else{
					$('<form method="post">'
						+ czyx.toFormString(data)
						+ '</form>'
					).attr('action', url)
					.appendTo('body').submit();		
				}
			}else{
				if(target){
					$('<form method="post" target="'+target+'">'
						+ czyx.toFormString(data) 
						+ '</form>'
					).attr('action', url)
					.appendTo('body')
					.submit()
					.remove();
				}else{
					if(url.indexOf('?') == -1){
						url += '?' ;
					}else{
						var lastChar = url.charAt(url.length-1) ;
						if(lastChar != '?' && lastChar != '&'){
							url += '&' ;
						}
					}
					url += $.param(data);
					window.location.href = url;
				}
			}
		},
		getWebSocket:function(url){
			var socket = window.WebSocket || window.MozWebSocket || this.WebSocket ;
			if(!socket){
				this.loadPlugin('WebSocket');
				return ;
			}
			return new socket(url);
		},
		loadPlugin:function(name, callback){
			$.getScript(this.rootPath + 'czyx/' + name + '.js', callback);
			return this ;
		},
		quote:function(html, type){
			switch(type){
				case 'url':
					return ;
				default:
					return html.split('&').join('&amp;')
						.split("<").join("&lt;")
						.split(">").join("&gt;")
						.split(" ").join("&nbsp;")
						.split("'").join("&#039;")
						.split('"').join("&quot;");
			}
			
		},
		error:function(msg, e){
			if(this.debug){
				$('<div></div>').appendTo('body').html(msg);
			}
		},
		isIe6:function(){
			return !-[1,] && /MSIE 6\.0/.test(navigator.userAgent);
		},
		reload:function(){
			window.location.href = window.location.href.replace(/&\_=[0-9]+/,'') + '&_=' + (+new Date);
		},
		rootPath:'./'
	};
	window.czyx = czyx ;
})(window);
$.extend(czyx,{
	uploadReplace:function(selectedQuery, param){
		param = param||{};
		param = $.extend(true,{},czyx.uploadSettings,param);
		param.url = param.url||window.location.href||'';
		param.MAX_FILE_SIZE = !-[1,] && param.MAX_FILE_SIZE ;
		return $(selectedQuery).each(function(){
				//绑定click只是让效果看上去更好，与上传功能无关
				var jThis = $(this).click(function(){
					$(this).parent().css({
								opacity:1,
								filter:'alpha(opacity=100)'	
							});	
				}) ;
				if(jThis.is('input[type="file"]')){
					var div = $('<div />')
						.insertBefore(jThis.css(param.uploadInputCss))
						.css(param.uploadReplaceCss)
						.attr('id', czyx.getNextTempId())
						.hover(function(){
							$(this).css({
								opacity:0.7,
								filter:'alpha(opacity=70)'	
							});	
						},function(){
							$(this).css({
								opacity:1,
								filter:'alpha(opacity=100)'	
							});	
						});
					
					//有一篇文章说，IE浏览器可以设置最大字节数，那么我就加上这句的
					//<input type="hidden" name="MAX_FILE_SIZE" value="30000" />
					//MAX_FILE_SIZE 隐藏字段（单位为字节）必须先于文件输入字段，其值为接收文件的最大尺寸。
					//这是对浏览器的一个建议，PHP 也会检查此项。
					//在浏览器端可以简单绕过此设置，因此不要指望用此特性来阻挡大文件。
					//实际上，PHP 设置中的上传文件最大值是不会失效的。
					//但是最好还是在表单中加上此项目，
					//因为它可以避免用户在花时间等待上传大文件之后才发现文件过大上传失败的麻烦。
					//详见：http://www.ugia.cn/?p=73
					if(param.MAX_FILE_SIZE){
						this.MAX_FILE_SIZE = param.MAX_FILE_SIZE ;
					}
					
					jThis.appendTo(div).change(function(){
						var tidDiv = $(this).parent().attr('id'),jForm;
						tidIframe  = czyx.getNextTempId(),
						tidForm    = czyx.getNextTempId();
						
						if(param.uploadBefore.call(this) === false){
							return false ;
						}
						if(!this.name){
							$(this).attr('name', 'czyxupload').data('addTempName', 1);
						}
						
						jForm = $('<form id="' + tidForm + '" action="' + param.url + '" '
							+ 'method="post" style="display:none;" '
							+ 'enctype="multipart/form-data"></form>'
						).appendTo('body');
						
						if(param.data){
							jForm.html(czyx.toFormString(param.data));
						}
						jForm.append(this).data('uploadEnd',param.uploadEnd);
						
						$('<iframe src="'
							+ (/^https/i.test(window.location.href || '') ? 'javascript:false' : 'about:blank')
							+ '" '
							+ 'id="' + tidIframe+'" '
							+ 'style="display:none;" '
							+ 'name="' + tidIframe+'" '
							+ 'onload="czyx._uploadEnd(this,\'' + tidDiv + '\',\'' + tidForm + '\');">'
							+ '</iframe>'
						).appendTo('body');
					});
				}	
		});
	},
	uploadSettings:{
		uploadReplaceCss:{//上传控件默认样式
			width:80,
			height:20,
			overflow:'hidden',
			position:'relative',
			background:'url(upload.png) center no-repeat'
		},
		uploadInputCss:{
			fontSize:72,
			cursor:'pointer',
			position:'absolute',
			right:0,
			//bottom:0,
			filter:'alpha(opacity=0)',
			opacity:0,
			outline:'none',
			hideFocus:'expression(this.hideFocus=true)'	
		},
		uploadEnd:$.noop,    //上传前的回调函数，如果返回 false将不执行上传
		uploadBefore:$.noop, //上传完毕后的回调函数，参数服务器端返回的字符串
		MAX_FILE_SIZE:null   //上传的文件最大字节数，暂不支持
	},
	_uploadEnd:function(iframeNode, divId, formId){
		var jForm = $('#'+formId);
		if(jForm.attr('target') != iframeNode.name){
			jForm.attr('target', iframeNode.name).submit();
		}else{
			var jFile = jForm.find('input:last');
			if(jFile.data('addTempName')){
				jFile.removeData('addTempName').removeAttr('name');
			}
			jForm.data('uploadEnd').call(
				jFile.appendTo('#'+divId).get(0),
				(iframeNode.contentWindow ? 
					iframeNode.contentWindow.document : 
						iframeNode.contentDocument ? 
						iframeNode.contentDocument : 
						iframeNode.document
				).body.innerHTML
				.split('&lt;').join('<')
				.split('&gt;').join('>')
				.split('&amp;').join('&')
			);
			jForm.removeData('uploadEnd').remove();
			$(iframeNode).remove();
		}
	}
});