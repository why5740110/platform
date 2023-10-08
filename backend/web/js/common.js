
$(function(){
    $(".del_single_upload").click(function(){
        var that = this;
        layer.confirm('确定删除图片?', {icon: 3, title:'提示'}, function(index){
            $(that).parent().parent().find('.upload-single').html('');
            $(that).parent().parent().find('input').val('');
            layer.close(index);
        });
    })
    $("#oldsystemframe").height($(".layui-body").height());
    $(".picture_center").load(function(){
        imgresize($(this));
    })
    $(document).on('click','.upload-image-prev',function () {
        var item=$(this).parent().parent();
        console.log(item.after(item.prev()));

    })
    $(document).on('click','.upload-image-next',function () {
        var item=$(this).parent().parent();
        console.log(item.before(item.next()));
    })
    $(document).on('click','.upload-image-del',function () {
        //由于layui的上传组件有问题,绑定多个相同class的元素，remove最后一个元素，其他元素事件失效
        // ，所以只能使用隐藏的方式来处理
        var that = this;
        layer.confirm('确定删除图片?', {icon: 3, title:'提示'}, function(index){
            var parent=$(that).parent().parent();
            parent.hide();
            parent.find('input[type="hidden"]').remove();
            layer.close(index);
        });
    });
    $(document).on('click','.upload-image-refresh',function(){

    })
})
layui.use(['form','element'], function(){
    var form = layui.form;
    $('#commonForm').ajaxForm({
        dataType:'json',
        beforeSend:function(){
            index=layer.load(0, {shade: [0.1, '#393D49']}); //0代表加载的风格，支持0-2
        },
        complete:function(){
            layer.close(index);
        },
        success:function(data){
            if(data.status){
                layer.msg(data.msg,{icon:1});
                setTimeout(function(){
                    window.location.href=data.url;
                },1000)
            }else{
                var item=$("[name='"+data.field+"']");
                item.addClass('layui-form-danger');
                item.focus();
                console.log(data.msg);
                layer.msg(data.msg, {icon: 2});
            }
        },
        error:function(){
            layer.msg('网络异常，请联系系统维护人员', {icon: 2});
        }
    });
});

// layui.use(['upload','layer'], function(){
//     var $ = layui.jquery,upload = layui.upload,layer=layui.layer;
//     var config={
//         elem: '.product_url'
//         ,url: '/upload/index'
//         ,accept:'file'
//         ,multiple: true
//         ,exts: 'jpg|png|gif'
//         ,before:function(){
//             index=layer.msg('文件上传中，请稍等……', {
//                 icon: 16
//                 ,shade: 0.01
//             });
//         }
//         ,done: function(res){
//             layer.close(index);
//             if(res.code==0){
//                 //请求失败
//                 layer.msg(res.msg, {icon: 2});
//             }else{
//                 var item=$(this)[0].item;
//                 var itemIndex=$(this)[0].elem.index(item);
//                 if(itemIndex==0){
//                     var html='<div class="multiupload-item"> ' +
//                         '<input type="hidden" name="icon_url" value="'+res.data.savePath+'"> ' +
//                         '<div class="upload-multiupload " id="icon_urlpreview">' +
//                         "<img onload='common.imgresize(this)' src= '"+res.data.fullPath+"'>" +
//                         '</div> ' +
//                         '<div class="layui-btn-group"> ' +
//                         '<button type="button" class="layui-btn layui-btn-sm layui-btn-disabled" title="新上传的图片支持更新"><i class="layui-icon">&#x1002;</i></button> ' +
//                         '<button type="button" class="layui-btn layui-btn-sm upload-image-prev"><i class="layui-icon">&#xe603;</i></button> ' +
//                         '<button type="button" class="layui-btn layui-btn-sm upload-image-next"><i class="layui-icon">&#xe602;</i></button> ' +
//                         '<button type="button" class="layui-btn layui-btn-sm upload-image-del"><i class="layui-icon">&#xe640;</i></button> ' +
//                         '</div> ' +
//                         '</div>';
//                     item.parent().find('.multi-upload').append(html);
//
//                 }else{
//                     //请求成功
//                     item.parent().siblings("input").val(res.data.savePath);
//                     item.parent().siblings(".upload-multiupload").html("<img onload='common.imgresize(this)' src= '"+res.data.fullPath+"'>");
//
//                 }
//
//             }
//
//         }
//     }
//     upload.render(config);
// });

//获取form表单的所有值
function getFormJson(form) {
    var values = {};
    var a = $(form).serializeArray();
    $.each(a, function () {
    if (values[this.name] !== undefined) {
        if (!values[this.name].push) {
            values[this.name] = [values[this.name]];
        }
        values[this.name].push(this.value || '');
    } else {
        values[this.name] = this.value || '';
    }
    });
    return values;
}
 //展开高级搜索
$('#morelink_open').on('click', function(){
    $('#morelink_close').show();
    $('#morelink_open').hide();
    $('.div_more').show();
})
//收起高级搜索
$('#morelink_close').on('click', function(){
    $('#morelink_close').hide();
    $('#morelink_open').show();
    $('.div_more').hide();
})

