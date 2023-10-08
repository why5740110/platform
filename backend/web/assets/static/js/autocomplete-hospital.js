var isChinese = function (str) {
    var pattern = /^[\u0391-\uFFE5]+$/g;
    return pattern.test(str);
}

// 缓存搜索结果，比对搜索关键字，有则不再从远程获取
var SearchResultCache = [];

var findMatches = function (q, process) {
    // 全部是中文则调用搜索补全
    if (!isChinese(q)) {
        return [];
    }

    return $.getJSON('/search/hospital', {q: q}, function(data) {
        var matches = [];
        for (var i in data) {
            matches.push(data[i].name);
        }
        return process(matches);
    });
};

$(function(){
    try {
        $("#DoctorInfo_hospital").typeahead({
            source: findMatches,
            updater: function(currentItem) {
                $("#DoctorInfo_hospital").focus();
                return currentItem;
            },
        })
    } catch(e) {
        console.log(e);
    };
});
