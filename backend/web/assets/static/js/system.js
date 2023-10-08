/**
显示货品查询弹出框
*domId:想要赋值的控件id，fnName：想要回调的方法名（传入逗号分隔的id）
*allDataFnName:想要回调的方法名（传入所有数据）
*argObj:想要额外传入的参数
**/
function showGoodsQueryDialog(domId,fnName,allDataFnName,argObj){
	var isOpen = false;
	//访问一次服务器，检测是否登录失效，如果失效，就不打开子窗口，防止在子窗口中打开登录页面
	doAjax({
		async:false,
		url:appPath+'/page/system/checkOpenCommonPage',
		success:function(data,textStatus,jqXHR){
			if(data && data == "ok"){
				isOpen = true;
			}
		},
		error:function(XMLHttpRequest, textStatus, errorThrown){
		}
	});
	if(!isOpen){
		return ;
	}
	var args = "dialogHeight:510px;"+
			  "dialogWidth:850px;"+
			  "center:true;"+
			  "help:no;"+
			  "status:no;"+
			  "dialogLeft:200px;"+
			  "dialogTop:170px;"+
			  "resizable:yes;";
	var obj = new Object();
	if(typeof(domId) == "string"){
		obj.domId = domId;			
	}
	if(typeof(fnName) == "string"){
		obj.fnName = fnName;
	}
	if(typeof(allDataFnName) == "string"){
		obj.allDataFnName = allDataFnName;
	}
	if(typeof(argObj) != "undefined"){
		obj.argObj = argObj;
	}
	obj.win = window;
	obj.doc = document;
	window.showModalDialog(appPath+"/jsp/common_query/common_goods_query.jsp",obj,args);
}

/**
显示赠品查询弹出框
*domId:想要赋值的控件id，fnName：想要回调的方法名（传入逗号分隔的id）
*allDataFnName:想要回调的方法名（传入所有数据）
*argObj:想要额外传入的参数
**/
function showGiftQueryDialog(domId,fnName,allDataFnName,argObj){
	var isOpen = false;
	//访问一次服务器，检测是否登录失效，如果失效，就不打开子窗口，防止在子窗口中打开登录页面
	doAjax({
		async:false,
		url:appPath+'/page/system/checkOpenCommonPage',
		success:function(data,textStatus,jqXHR){
			if(data && data == "ok"){
				isOpen = true;
			}
		},
		error:function(XMLHttpRequest, textStatus, errorThrown){
		}
	});
	if(!isOpen){
		return ;
	}
	var args = "dialogHeight:510px;"+
			  "dialogWidth:850px;"+
			  "center:true;"+
			  "help:no;"+
			  "status:no;"+
			  "dialogLeft:200px;"+
			  "dialogTop:170px;"+
			  "resizable:yes;";
	var obj = new Object();
	if(typeof(domId) == "string"){
		obj.domId = domId;			
	}
	if(typeof(fnName) == "string"){
		obj.fnName = fnName;
	}
	if(typeof(allDataFnName) == "string"){
		obj.allDataFnName = allDataFnName;
	}
	if(typeof(argObj) != "undefined"){
		obj.argObj = argObj;
	}
	obj.win = window;
	obj.doc = document;
	window.showModalDialog(appPath+"/jsp/common_query/common_gift_query.jsp",obj,args);
}

function showOrderQueryDialog(domId,fnName,allDataFnName,argObj){
	var isOpen = false;
	//访问一次服务器，检测是否登录失效，如果失效，就不打开子窗口，防止在子窗口中打开登录页面
	doAjax({
		async:false,
		url:appPath+'/page/system/checkOpenCommonPage',
		success:function(data,textStatus,jqXHR){
			if(data && data == "ok"){
				isOpen = true;
			}
		},
		error:function(XMLHttpRequest, textStatus, errorThrown){
		}
	});
	if(!isOpen){
		return ;
	}
	var args = "dialogHeight:550px;"+
			  "dialogWidth:850px;"+
			  "center:true;"+
			  "help:no;"+
			  "status:no;"+
			  "dialogLeft:200px;"+
			  "dialogTop:170px;"+
			  "resizable:yes;";
	var obj = new Object();
	if(typeof(domId) == "string"){
		obj.domId = domId;			
	}
	if(typeof(fnName) == "string"){
		obj.fnName = fnName;
	}
	if(typeof(allDataFnName) == "string"){
		obj.allDataFnName = allDataFnName;
	}
	if(typeof(argObj) != "undefined"){
		obj.argObj = argObj;
	}
	obj.win = window;
	obj.doc = document;
	window.showModalDialog(appPath+"/page/queryOrder/queryPage?type=commonQuery",obj,args);
}

function checkUserButtonRight(buttonNo){
	var rslt = false;
	doAjax({
		url:appPath + "/page/sysButton/"+buttonNo+"/checkRight",
		async : false,
		success:function(data){
			if(typeof(data)=='boolean' && data){
				rslt = true;
			}
		}
	});
	if(!rslt){
		$.messager.alert('提示信息','您没有该权限,请联系管理员！','info');
	}
	return rslt;
}