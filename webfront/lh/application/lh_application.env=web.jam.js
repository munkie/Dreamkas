this.$lh_application= $jin_class( function( $lh_application, application ){
    
    $lh_widget( $lh_application )
    
    $lh_application.id= 'lh_application'
    
    application.templates= null
    
    application.render= function( application, data ){
        application.$.removeAttribute( 'lh_loading' )
        application.templates.render( data , application.$ )
        $jin_component.checkTree( application.$ )
        return application
    }
    
    application.api= function( application, suffix ){
        return $lh_resource( application.$.getAttribute( 'lh_application_api' ) + suffix )
    }
    
    application.resource_product_list= function( application, params ){
        return application.api( 'products' )
    }
    
    application.resource_product=
    application.resource_product_edit=
    function( application, params ){
        return application.api( 'products/' + params.product )
    }
    
    application.resource_invoice= function( application, params ){
        return application.api( 'invoices/' + params.invoice )
    }
    
    application.resource_invoice_list= function( application, params ){
        return application.api( 'invoices' )
    }
    
    var init= application.init
    application.init= function( application, node ){
        init.apply( this, arguments )
        
        document.title= document.location.search
        
        if( !document.location.search ) document.location= '?dashboard'
        
        $lh_resource( 'lh/-mix/index.stage=release.xsl' )
        .get( function initView( resource ){
            application.templates= resource.xml()
            
            var params= {}
            document.location.search
            .replace( /^\?/, '' )
            .split( /[\/&;]/ )
            .forEach( function( chunk ){
                var pair= chunk.split( '=' )
                params[ pair[ 0 ] ]= pair[ 1 ] || ''
            })
            
            var data= $jin_domx.parse( '<data/>' )
            .attr( 'lh_application_view', 'lh_' + Object.keys( params ).join( '_' ) )
            
            application.$.setAttribute( 'lh_loading', 'true' )
            
            var loaderName= 'resource_' + Object.keys( params ).join( '_' )
            var loader= application[ loaderName ]
            
            if( loader ){
                loader.call( application, params )
                .get( function( resource ){
                    resource.xml().parent( data )
                    application.render( data )
                } )
            } else {
                application.render( data )
            }
            
        } )
        
        $lh_product_onSave.listen( document.body, function( event ){
            if( event.catched() ) return
            
            var editor= $lh_product_edit( event.target() )
            var data= editor.data()
            var id= data.select( 'id' )[ 0 ]
            if( id ) id= id.parent( null ).text()
            
            var url=  'products'
            if( id ) url+= '/' + id
            
            application.api( url )
            .request( id ? 'put' : 'post', data, function( resource ){
                switch( true ){
                    case resource.isCreated():
                        id= resource.xml().select('id')[0].text()
                        document.location= '?product/list#product=' + id
                        break
                    case resource.isSaved():
                        document.location= '?product=' + id
                        break
                    case resource.isWrongData():
                        data= resource.xml()
                        editor.errors( data )
                        break;
                    default:
                        $lh_notify( 'Ошибка при сохранении данных товара. ' + resource.message() )
                }
            } )
            
            event.catched( true )
        })
        
        $lh_invoice_onSave.listen( document.body, function( event ){
            if( event.catched() ) return
            
            var editor= $lh_invoice_edit( event.target() )
            var data= editor.data()
            var id= data.select( 'id' )[ 0 ]
            if( id ) id= id.parent( null ).text()
            
            var url=  'invoices'
            if( id ) url+= '/' + id
            
            application.api( url )
            .request( id ? 'put' : 'post', data, function( resource ){
                switch( true ){
                    case resource.isCreated():
                        id= resource.xml().select('id')[0].text()
                        document.location= '?invoice/list#invoice=' + id
                        break
                    case resource.isSaved():
                        document.location= '?invoice=' + id
                        break
                    case resource.isWrongData():
                        data= resource.xml()
                        editor.errors( data )
                        break;
                    default:
                        $lh_notify( 'Ошибка при сохранении данных накладной. ' + resource.message() )
                }
            } )
            
            event.catched( true )
        })
        
    }
    
})