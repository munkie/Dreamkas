define(function(require, exports, module) {
    //requirements
    var Form_stockMovement = require('blocks/form/stockMovement/stockMovement');

    return Form_stockMovement.extend({
        template: require('ejs!./template.ejs'),
        id: 'form_invoice',
        model: require('resources/invoice/model')
    });
});