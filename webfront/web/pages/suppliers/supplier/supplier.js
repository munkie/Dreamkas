define(function(require, exports, module) {
    //requirements
    var Page = require('pages/supplier');

    return Page.extend({
        partials: {
            content: require('ejs!./content.ejs')
        },
        blocks: {
            form_supplier: function(){
                var page = this,
                    Form_supplier = require('blocks/form/form_supplier/form_supplier');

                return new Form_supplier({
                    model: page.models.supplier
                });
            }
        }
    });
});