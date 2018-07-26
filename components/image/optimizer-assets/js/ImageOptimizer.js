var ImageOptimizer = new function(){
    
    this.init = function(){
        cutterEvents();
    }
    
    function cutterEvents(){

        var desiredAspect = eval($('.optimizer-container').attr('data-aspect')),
            minWidth = $('.optimizer-container').attr('data-min-width'),
            minHeight = $('.optimizer-container').attr('data-min-height');
        
        $('.optimizer-container').find('img').on('load',function(){
            initCutter(desiredAspect, minWidth, minHeight);
        });

        $('[data-optimize-send]').on('click', function(){
            save(this);
        });
        
        $('[data-popover]').popover();
        
    }
    
    function renderLayout(){
        
        var cutter = $('[data-cutter]');
        
        //Display width y height
        var dh = $('.optimizer-container img').height();
        var dw = $('.optimizer-container img').width();
        
        var cw = cutter.width();
        var ch = cutter.height();
        
        var t=0, 
            l=0, 
            b=dh, 
            r=dw, 
            it=cutter.position().top,
            il=cutter.position().left,
            ch=cutter.height(), 
            cw=cutter.width();
        
        $('#rect_opt_t').css({left: l, top: t});
        $('#rect_opt_t').width(r).height(it);
        
        $('#rect_opt_b').css({left: l, top: it+ch});
        $('#rect_opt_b').width(r).height(b-ch-it);
        
        $('#rect_opt_l').css({left: l, top: it});
        $('#rect_opt_l').width(il).height(ch);
        
        $('#rect_opt_r').css({left: il+cw, top: it});
        $('#rect_opt_r').width(r-cw-il).height(ch);
    }
    
    //Es necesario esperar a que el navegador renderice completamente 
    function initCutter(desiredAspect, minWidth, minHeight){

        if($('.optimizer-container').width() == 0){
            setTimeout(function(){ initCutter(desiredAspect, minWidth, minHeight); },1);
        }else{
            loadCutter(desiredAspect, minWidth, minHeight);
        }
        
    }
    
    function loadCutter(desiredAspect, minWidth, minHeight)
    {
        
        var $container = $('.optimizer-container');
        var $img = $('#img2optimize');
        var $cutter = $('[data-cutter]');
        
        //Natural width y height
        var nh = document.getElementById('img2optimize').naturalHeight;
        var nw = document.getElementById('img2optimize').naturalWidth;
        
        //Container
        var ch = $container.height();
        
        //No se puede calcular con width() porque extrañamente devuelve el valor en porcentaje por unos momentos
        var cw = $container.width();

        var containerAspect = cw/ch;
        var imageAspect = nw/nh;

        var cutterW, cutterH;

        var imageDisplayWidth, imageDisplayHeight;

        //Css para mostrar correctamente la imagen. Determinamos tamaño visual de la imagen
        if(imageAspect > containerAspect){
            $img.css({width: '100%', height: 'auto'});
            imageDisplayWidth = cw;
            imageDisplayHeight = nh * imageDisplayWidth / nw;
        }else{
            $img.css({width: 'auto', height: '100%'});
            imageDisplayHeight = nh >= 500 ? 500 : nh;
            imageDisplayWidth = nw * imageDisplayHeight / nh;
        }

        //Calculamos cto zoom se ha aplicado a la imagen, para poder limitar correctamente el ancho y alto de cutter
        var zoomFactor = imageDisplayWidth / nw;

        //Determinamos tamaño del cutter
        if(imageAspect <= desiredAspect){
            cutterW = imageDisplayWidth;
            cutterH = imageDisplayWidth / desiredAspect;
        }else{
            cutterW = imageDisplayHeight * desiredAspect;
            cutterH = imageDisplayHeight;
        }

        $cutter.width(cutterW).height(cutterH);

        $('.optimizer-container').find('[data-cutter]').resizable({ 
            handles: "n, e, s, w, ne, se, sw, nw", 
            containment: ".optimizer-container img", 
            aspectRatio: desiredAspect,
            minHeight: minHeight*zoomFactor,
            minWidth: minWidth*zoomFactor,
            resize: function( event, ui ){
                renderLayout();
            } 
        }).draggable({ containment: ".optimizer-container img", drag: function(){
                renderLayout();
        } });

        $container.append('<div id="rect_opt_t" class="optimizer-layout"></div>');
        $container.append('<div id="rect_opt_l" class="optimizer-layout"></div>');
        $container.append('<div id="rect_opt_b" class="optimizer-layout" style="width:'+imageDisplayWidth+'px;top:'+cutterH+'px;height: '+(imageDisplayHeight-cutterH)+'px"></div>');
        $container.append('<div id="rect_opt_r" class="optimizer-layout" style="top:0;height:'+cutterH+'px;left:'+cutterW+'px;width:'+(imageDisplayWidth-cutterW)+'px;"></div>');
        
    }
    
    function save(trigger){
        
        var $cutter = $('[data-cutter]');
        var $img = $('#img2optimize');
        var $container = $('.optimizer-container');
        
        var desiredAspect = eval($container.attr('data-aspect'));
        
        var x1 = (100 * $cutter.position().left) / $img.width(),
            x2 = (100 * ( $cutter.width() + $cutter.position().left ) ) / $img.width(),
            y1 = (100 * $cutter.position().top ) / $img.height(),
            y2 = (100 * ( $cutter.height() + $cutter.position().top ) ) / $img.height();
    
        var url = $(trigger).attr('data-url');
        
        $.ajax({
            url: url,
            data: {
                x1: x1,
                x2: x2,
                y1: y1,
                y2: y2,
                aspect: desiredAspect
            },
            type: 'post'
        }).done(function(response){
            if(response.status == 'success'){
                if(response.step == -1){
                    Media.close()
                    $('[data-optimize-media-id='+response.media_id+']').removeClass('btn-default').addClass('btn-success').html(response.label);
                }else{
                    $('.bootbox-body').html(response.form);
                }
                
            }else{
                $('.bootbox-body').html(response.errors);
            }
        });
        
    }
}
