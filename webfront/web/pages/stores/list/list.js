define(function(require, exports, module) {
    //requirements
    var Page = require('kit/page');

    return Page.extend({
        partials: {
            globalNavigation: require('rv!pages/globalNavigation_main.html'),
            content: require('rv!./content.html')
        }
    });
});