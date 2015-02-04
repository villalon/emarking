YUI().use('io-base','io-form','node','panel','tabview','event-mouseenter', 'dd-plugin', function (Y) {
    var tooltips =  Y.all('.has-tooltip');
    tooltips.on('mouseenter',function(e){
        var elid= e.target.getAttribute('data-tooltip');

       Y.one(elid).show();
    });
    tooltips.on('mouseleave',function(e){
        var elid=e.target.getAttribute('data-tooltip');
        Y.one(elid).hide();
    });
    var notgradedRows = Y.all(".notgraded");
    var gradedRows = Y.all(".graded");
    notgradedRows.hide();
    var markerfilter = Y.one("#markerfilter");
    markerfilter.on("keyup",function(e){
        var text = e.target.get('value');
        var icons = Y.all(".graded .icon-male");
        icons.each(function(item,i){
            var name = item.getAttribute('data-name');
            if(name.indexOf(text) != -1&&text.length>0){
                item.addClass("me");
            }else{
                item.removeClass("me");
            }
        });
    });

});