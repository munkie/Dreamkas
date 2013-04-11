var productRouterE = new ProductsRouter;
var invoicesRouteE = new InvoicesRouter;
var amountsRouteE = new AmountsRouter;
var lighthouseRouterE = new LighthouseRouter;

Backbone.history.start({
	pushState: true
});

window.app = new Backbone.Router;

$("body").on('click', 'a', function(event){
    if($(this).attr('href')) {
        app.navigate($(this).attr('href'), {trigger: true});
        event.preventDefault();
    }
});
