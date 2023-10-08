var page = require('webpage').create();
var args = require('system').args;

var url = args[1];
page.open(url, function (status) {
    if(status!=='success') {
        console.log(status);
        phantom.exit();
    }else{
        //如果请求成功, 退出
        console.log('页面加载完毕');
        window.setTimeout(function () {
            phantom.exit();
        }, 10000);

    }
});