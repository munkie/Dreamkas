define(function(require, exports, module) {
    //requirements
    var Select = require('blocks/select/select');

    return Select.extend({
        template: require('ejs!./template.ejs'),
        all: false,
        add: false,
        globalEvents: {
            'submit:success': function(data, block) {

                var modal = block.$el.closest('.modal')[0];

                if (modal && modal.id === 'modal_store-' + this.cid) {

                    this.selected = data.id;
                    this.render();
                }
            }
        },
        collection: function(){
            return PAGE.collections.stores;
        },
        blocks: {
            modal_store: require('blocks/modal/store/store')
        }
    });
});