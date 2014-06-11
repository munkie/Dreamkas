define(function(require) {
    //requirements
    var Page = require('kit/page'),
        GroupModel = require('models/group'),
        router = require('router'),
        cookies = require('cookies'),
        config = require('config');

    return Page.extend({
        params: {
            edit: '0'
        },
        events: {
            'click .catalog__addGroupLink': function(e) {
                e.preventDefault();

                var block = this;

                block.blocks.tooltip_groupForm.show({
                    trigger: e.target,
                    collection: block.collections.groups,
                    model: new GroupModel()
                });
            },
            'click .catalog__exportLink': function(e) {
                e.preventDefault();

                var page = this;

                if (e.target.classList.contains('preloader_stripes')){
                    return;
                }

                e.target.classList.add('preloader_stripes');

                Promise.resolve(page.exportCatalog()).then(function(){
                    alert('Выгрузка началась');
                    e.target.classList.remove('preloader_stripes');
                }, function(){
                    alert('Выгрузка невозможна, обратитесь к администратору');
                    e.target.classList.remove('preloader_stripes');
                });
            }
        },
        listeners: {
            'change:params.edit': function(){
                var page = this;

                page.render();
            }
        },
        partials: {
            content: require('tpl!./content.ejs'),
            localNavigation: require('tpl!blocks/localNavigation/localNavigation_catalog.ejs')
        },
        collections: {
            groups: require('collections/groups')
        },
        blocks: {
            tooltip_groupForm: require('blocks/tooltip/tooltip_groupForm/tooltip_groupForm')
        },
        exportCatalog: function(){
            return $.ajax({
                url: config.baseApiUrl + '/integration/export/products',
                dataType: 'json',
                type: 'GET',
                headers: {
                    Authorization: 'Bearer ' + cookies.get('token')
                }
            })
        }
    });
});