var appPath =  getAppPath();

/**
 * 查询条件页面的展开收起效果
 */
function showORhide(){
		var aa=appPath.split("/");
		var bb=aa[3];
		$(".showOrhideClass").hide();
		$("#show").click(function(){
			 if($("#showOrhide").html()=='收起'){
				  $("#showOrhideImage").attr("src","/"+bb+"/images/common/show.jpg");
			 }else{
				 $("#showOrhideImage").attr("src","/"+bb+"/images/common/hide.jpg");
			 }
			 $("#showOrhide").html($("#showOrhide").html()=='收起'?'展开':'收起'); 
				$(".showOrhideClass").each(function (){
					$(this).toggle();
			 });
		});
}


/**
 * 判断日期格式yyyy-MM-dd
 * @param {} dateString
 * @return {Boolean}
 */
function isDate(dateString)   {
	if(typeof(dateString) == "undefined" || !$.trim(dateString)){
		return true;
	}
	var reg = /^(\d{4})-(\d{2})-(\d{2})$/;     
	var str = dateString;     
	var arr = reg.exec(str);     
	if (!reg.test(str)){
		return false;
	}
		return true;
} 

/**
 * 检验日期时间格式
 * @param {} dateTimeString
 * @return {Boolean}
 */
function isDateTime(dateTimeString)   {
	if(typeof(dateTimeString) == "undefined" || !$.trim(dateTimeString)){
		return true;
	}
	var reg = /^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/;     
	var str = dateTimeString;     
	var arr = reg.exec(str);     
	if (!reg.test(str)){
		return false;
	}
		return true;
}

$.fn.serializeObject = function(){    
        var o = {};    
        var a = this.serializeArray();  
        $.each(a, function() {    
            if (o[this.name]) {    
                if (!o[this.name].push) {    
                    o[this.name] = [ o[this.name] ];    
                }    
                o[this.name].push($.trim(this.value) || '');    
            } else {    
                o[this.name] = $.trim(this.value) || '';    
            }    
        });    
        return o;    
	};
	
/**
非输入状态下屏蔽退格键
*/
$(document).keydown(function(){
 	if(!key(arguments[0])){
 		return false;
 	}
});
function key(e){
	var srcType ;
	var isReadOnly = "";
	if(e.srcElement){//IE
		srcType = e.srcElement.type;
		if(e.srcElement.attributes.readOnly){
			var isReadOnly =e.srcElement.attributes.readOnly.value;
		}
	}else if(e.target){//Firefox
		srcType = e.target.type;
	}
	var isShielding = false;
	if(srcType != "text" && srcType != "textarea" && srcType != "password"){
		isShielding = true;		
	}
	if(isReadOnly == "true"){
		isShielding = true;
	}
	if(isShielding){
		var keynum;
        if(window.event){//IE
			keynum = e.keyCode;
		}else if(e.which){// Netscape/Firefox/Opera
			keynum = e.which;
		}
		if(keynum == 8){
			return false;
		}
	}
	return true;
}

function doAjax(ajaxArgs){
	if($.isFunction(ajaxArgs.error)){
		var oldErrFn = ajaxArgs.error; 
	}
	ajaxArgs.error = function (XMLHttpRequest, textStatus, errorThrown){
			if(XMLHttpRequest.status == 403){
				window.location = appPath+"/login.jsp";
			}else if(oldErrFn){
				oldErrFn(XMLHttpRequest, textStatus, errorThrown);
			}
	}
	$.ajax(ajaxArgs);
}

/**
 * 在父窗口创建隐藏标签
 * @param {} domId
 */
function createParentHiddenDom(domId){
	var parentWindow;
	if(window.opener){
		parentWindow = window.opener;
	}else{
		parentWindow = window.parent;
	}
	if($(parentWindow.document.body).find("#"+domId).length == 0){
		$(parentWindow.document.body).append("<input type='hidden' id='"+domId+"' />");
	}
}
/**
 * 替换页面特殊字符加上/
 * @param v
 */
function valueReplace(v){
	v=v.toString().replace(new RegExp('(["\"])', 'g'),"\\\"");
	return v; 
}

/**
 * 获取当前时间字符串"yyyy-MM-dd HH:mm:ss"格式
 * @return {}
 */
function getCurrentTimeString(){
	var date = new Date();
	return  date.getFullYear() + 
			"-" +
			((new String(date.getMonth()+1)).length==1?"0"+(date.getMonth()+1):(date.getMonth()+1)) +
			"-" +
			((new String(date.getDate())).length==1 ? ("0"+date.getDate()) : date.getDate()) +
			" " +
			((new String(date.getHours())).length==1 ? ("0"+date.getHours()) : date.getHours()) +
			":" +
			((new String(date.getMinutes())).length==1 ? ("0"+date.getMinutes()) : date.getMinutes()) +
			":" +
			((new String(date.getSeconds())).length==1 ? ("0"+date.getSeconds()) : date.getSeconds());
}