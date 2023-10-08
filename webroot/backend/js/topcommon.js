/*
 * 公共的common模块
 * @file topcommon.js
 */
var common = (function (object) {
    object.success=function(msg){
        return layer.msg(msg, {icon: 1,shade:0.6,time:600});
    }
    object.error=function(msg){
        return layer.msg(msg, {icon: 2,shade:0.6,time:1500});
    }
    object.load=function(){
        return layer.load(0, {shade: [0.3, '#393D49']});
    }
    /*
     * 按照父级元素调整图片大小
     * @param item  图像对象
     */
    object.imgresize = function (item) {
        var parent = item.parentNode || item.parentElement;
        var maxwidth = parent.clientWidth;
        var maxheight = parent.clientHeight;
        if (item.clientWidth > maxwidth - 4) {
            item.style.width = (maxwidth - 4) + 'px';
            item.style.height = "auto";
        }
        if (item.clientWidth > maxheight - 4) {
            item.style.width = "auto";
            item.style.height = (maxheight - 4) + 'px';
        }
        item.style.marginLeft = (maxwidth - item.clientWidth) / 2 + 'px';
        item.style.marginTop = (maxwidth - item.clientHeight) / 2 + 'px';
        item.style.position = 'relative';
    };
    /*
     * 自带锁定及加载框的公共ajax方法
     * @param option 参数
     */
    object.ajax = function (option) {
        var index;
        var defaultOption = {
            dataType: 'json',
            type: 'post',
            error: function () {
                common.error('网络请求失败,请联系管理员');
            },
            beforeSend: function () {
                index = object.load();
            },
            complete: function () {
                layer.close(index);
            }
            ,success:function(data){
                if(data.status){
                    common.success(data.msg);
                }else{
                    common.error(data.msg);
                }
            }
        }
        option = $.extend(defaultOption, option);
        $.ajax(option);
    };
    object.commonTable=function(option){
        if(typeof(option.tablePrimaryKey))
        var pk=option.tablePrimaryKey;
        var returnData={};
        layui.use('table', function() {
            returnData['table'] = layui.table
            returnData['form'] = layui.form;
            returnData['table']['tableEdit']=function(field,value){
                var data={};
                data[field]=value[field];
                data[pk]=value[pk]
                console.log(data);
                common.ajax(
                    {
                        url:'edit'+field,
                        data:data,
                    }
                )
            }
            returnData['table']['tableSwitch']=function(item){
                var data={};
                var value;
                if($(item).prop('checked')){
                    value=1;
                }else{
                    value=0;
                }
                data[$(item).prop('name')]=value;
                data[pk]=item.value;
                common.ajax(
                    {
                        url:'edit'+item.name,
                        data:data,
                    }
                )
            };
            var defaultOption={
                id:'table',
                elem: '#table'
                ,url:'index'
                ,page:true
            }
            returnData['table'].render($.extend(defaultOption,option));
            returnData['table'].on('tool(table)',function(obj){
                option.event[obj.event](obj);
            })
            $("#search").submit(function(){
                table.reload('table',
                    {
                        page: {curr: 1}
                        ,where:getFormJson("#search")
                    }
                )
                return false;
            })
            $("#tableOperation .layui-btn").click(function(){
                var option=$(this).data();
                if(typeof(option.url) == 'undefined'){
                    return  ;
                }
                var checkStatus = returnData['table'].checkStatus('table')
                    ,data = checkStatus.data;
                if(data.length==0) {
                    common.error("没有选中项目,无法进行操作");
                    return;
                }
                layer.confirm("确定要"+$(this).text()+"?",function(){
                    var id=[];
                    for (var x in data){
                        id.push(data[x][pk]);
                    }
                    var sendData={};
                    sendData[pk]=id;
                    option.data=$.extend(option.data,sendData);
                    option.success=function(data){
                        if(data.status){
                            common.success(data.msg);
                            returnData['table'].reload('table');
                        }else{
                            common.error(data.msg);

                        }
                    }
                    common.ajax(option)
                })


            });
            returnData['table'].on('edit(table)',function(obj){
                returnData['table'].tableEdit(obj.field,obj.data);
            })
            returnData['form'].on('switch(status)', function(){
                returnData['table'].tableSwitch(this);
            });
        })
       return returnData;
    }

    return object;
})(window.common || {});