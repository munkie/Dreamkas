define(function(require, exports, module) {
    //requirements
    var Block = require('kit/block/block'),
        formatMoney = require('kit/formatMoney/formatMoney'),
        normalizeNumber = require('kit/normalizeNumber/normalizeNumber');

    return Block.extend({
        template: require('ejs!./template.ejs'),
        events: {
            'click .receipt__productLink': function(e) {
                var block = this;

                document.getElementById('modal_receiptProduct').block.show({
                    models: {
                        receiptProduct: block.models.receipt.collections.products.get(e.currentTarget.dataset.receiptProductCid)
                    }
                });
            },
            'click .receipt__clearLink .confirmLink__confirmation': function(e) {
                var block = this;

                block.models.receipt.collections.products.reset([]);
            }
        },
        models: {
            receipt: function(){
                return PAGE.models.receipt;
            }
        },
        blocks: {
            modal_receipt: require('blocks/modal/receipt/receipt')
        },
        initialize: function() {
            var block = this;

            var initialize = Block.prototype.initialize.apply(block, arguments);

            block.listenTo(block.models.receipt.collections.products, {
                'add remove change reset': function() {
                    block.render();
                    block.$('.receipt__scrollContainer').scrollTop(block.$('.receipt__scrollContainer table').height());
                }
            });

            return initialize;
        },
        calculateItemPrice: function(receiptProductModel) {
            return formatMoney(normalizeNumber(receiptProductModel.get('quantity')) * normalizeNumber(receiptProductModel.get('price')));
        },
        calculateTotalPrice: function() {
            var block = this,
                totalPrice = 0;

            block.models.receipt.collections.products.forEach(function(receiptProductModel) {
                totalPrice += normalizeNumber(block.calculateItemPrice(receiptProductModel));
            });

            return formatMoney(totalPrice);
        }
    });
});