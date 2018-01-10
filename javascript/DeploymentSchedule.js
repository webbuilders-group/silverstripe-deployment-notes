(function($) {
    $.entwine('lenovo', function($) {
        $('.deployment-schedule pre').entwine({
            onadd: function() {
                var code=$(this).find('code[class^=language]');
                
                if(code.length>0) {
                    var brush=code.attr('class').replace('language-', '');
                    $(this).attr('class', 'prettyprint lang-'+brush);
                    
                    if(PR && PR.prettyPrint) {
                        PR.prettyPrint();
                    }
                }
            }
        });
    });
})(jQuery);