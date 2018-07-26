var Media = new function(){
    
    var editorSelector;
    var dialog;
    var dialogContent;
    
    var currentWidget;
    
    var events;
    
    this.previewOptions;
    
    this.init = function(options)
    {
        events = {};
        editorSelector = options.editorSelector;
        Media.previewOptions = options.previewOptions;
        
        console.log('Preview options');
        console.log(Media.previewOptions);
        
        //Evento para insertar media en ckeditor o mostrar código de inserción
        $('[data-preview-list]').on('click', '[data-media-insert]', function(event){
            event.preventDefault();
            event.stopPropagation();
            Media.insert(event.target);
        });
        
        //Para eliminar media (se elimina la relación, no el recurso)
        $('[data-preview-list]').on('click', '[data-media-delete]', function(event){ deleteMedia(event, this); });
        //Para ordenar
        $('[data-preview-list]').sortable();
        
        //Evento para mostrar formulario de alta
        $('body').on('click', '[data-media-action]', function(e){
            Media.loadForm(this); 
        });
        
        //Evento para mostrar formulario de alta
        $('body').on('click', '[data-media-add]', function(e){
            Media.addMedia(this); 
        });
        
        initSearch();
        
        ImageOptimizer.init();
    };
    
    function initSearch()
    {
        $('body').on('click', '[data-search-btn]', function(e){
            
            currentWidget = $(this).closest('.media-box').attr('id');
            dialogContent = $('<div></div>');
            
            dialog = bootbox.dialog({
                size: 'large',
                title: 'Search...',
                message: dialogContent,
                buttons: false,
            });
            
            Media.search('Image');
        });
        
        $('body').on('click', '[data-search-box] .tab', function(e){
            e.preventDefault();
            e.stopPropagation();
            var type = $(this).attr('data-type');
            Media.search(type);
            $('[data-search-box] .nav li').removeClass('active');
            $('[data-search-box] .nav [data-type='+type+']').parent().addClass('active');
        });

        $('body').on('click', '#btn-search', function(e){
            e.preventDefault();
            e.stopPropagation();
            Media.search('Image', $('#search_media_input').val());
        });

        $('body').on('keypress', '[data-search-box]', function(e){
            if(e.keyCode === 13){
                e.preventDefault();
                e.stopPropagation();
                Media.search($('.active').attr('data-type'), $('#search_media_input').val());
            }
        });
    }
    
    /**
     * Registra un tipo de media para subida de archivos
     * @param {...} options
     * @returns {undefined}
     */
    this.registerUploader = function(options)
    {
        // Change this to the location of your server-side upload handler:
        var url = options.url;
        $('#'+options.inputId).fileupload({
            url: url,
            dataType: 'json',
            done: function (e, data) {
                if(data.result.status == 'success'){
                    $(this).closest('.media-box').find('[data-preview-list]').append(data.result.preview);
                }else{
                    for(e in data.result.errors){
                        $(this).closest('.media-box').find('[data-messages]').append('<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert" aria-label="Close"> <span aria-hidden="true">&times;</span></button>'+data.result.errors[e]+'</div>');
                    }
                }
               $(this).closest('.media-box').find('.progress-bar').css(
                    'width',
                    '0%'
                );
            },
            progressall: function (e, data) {
                var progress = parseInt(data.loaded / data.total * 100, 10);
                $(this).closest('.media-box').find('.progress-bar').css(
                    'width',
                    progress + '%'
                );
            }
        }).prop('disabled', !$.support.fileInput)
            .parent().addClass($.support.fileInput ? undefined : 'disabled');
    
        $('#'+options.inputId).bind('fileuploadsubmit', function (e, data) {
            data.formData = {previewOptions: JSON.stringify(Media.previewOptions)};
        });
    
    }
    
    /**
     * Inserta media en ckeditor. Si no existe instancia de ckeditor, 
     * muestra un modal con el código de inserción.
     * @param {...} element
     * @returns {undefined}
     */
    this.insert = function(element)
    {
        
        $.ajax({
            url: $(element).data('media-url'),
            data: jQuery.param({id: $(element).data('media-id')}),
            dataType: 'json',
            success: function (data){
                
                //I18n
                var lang = $(element).attr('data-language');
                if(lang && $(editorSelector).filter('.'+lang).length){
                    $editor = $(editorSelector).filter('.'+lang);
                }else{
                    $editor = $(editorSelector);
                }
                
                //Epigrafe
                var media = data.media;
                if (data.epigraph_param && !data.include_caption){
                    var title= $('[name="MediaData['+ $(element).data('media-id') +'][title]"]').val()
                    var description= $('[name="MediaData['+ $(element).data('media-id') +'][description]"]').val();
                    var caption= (title !== undefined ? title : '') + ' / ' + (description !== undefined ? description : '');
                    if(caption !== ''){
                        media= media + '<div class="image-caption">'+ caption + '</div>'
                    }
                }
                
                //Insertamos en ckeditor o mostramos código
                if($editor.length > 0){
                    var editorID = $editor.attr('id');
                    var editor = CKEDITOR.instances[editorID];
                    editor.insertHtml(media);
                }else{
                    var textArea = $('<div class="form-group"><textarea class="form-control" style="width:100%; text-align:center;" /></div>');
                    textArea.find('textarea').text(media);
                    Media.loadContent('Html', textArea);
                }
            }
        });
        
    };
    
    function getUrl(trigger)
    {
        var url = $(trigger).attr('data-media-url');
        return url;
    }
    
    /**
     * Inicializa el formulario para la carga de media sin subida de archivos
     * (youtube, iframe, etc)
     * @param jQuery trigger
     * @returns {undefined}
     */
    this.loadForm = function(target)
    {
        bootbox.hideAll();
        
        var url = getUrl(target); 
        
        currentWidget = $(target).closest('.media-box').attr('id');
        
        dialogContent = $('<div id="form-container"></div>');

        $.ajax({
            url: url,
            type: 'get',
            dataType: 'json',
        }).done(function(json){
            
            if(json.status == 'success'){
                dialogContent.html(json.form);
                dialogContent.off('click', '#submit-modal-form').on('click', '#submit-modal-form',function(e){
                    e.preventDefault();
                    e.stopPropagation();
                    Media.sendForm(url, target);
                });
                trigger('modalLoad', dialogContent);
            }else{
                dialogContent.html(json.errors);
            }
            
            dialog = bootbox.dialog({
                size: 'large',
                title: json.title,
                message: dialogContent,
                buttons: false,

            });
            
        });
        
    }
    
    /**
     * Envía el formulario al servidor e inserta el media en la lista de media
     * @param string url
     * @param jQuery trigger
     * @returns {undefined}
     */
    this.sendForm = function(url, trigger)
    {

        var data = dialogContent.find('form').serializeArray();
        data.push({name: 'save', value: true});
        data.push({name: 'previewOptions', value: JSON.stringify(Media.previewOptions)});

        $.ajax({
            url: url,
            type: 'post',
            data: data,
            dataType: 'json'
        }).done(function(json){
            
            if(json.status == 'success'){

                bootbox.hideAll();

                $previewList = $(trigger).closest('.media-box').find('[data-preview-list]');
                
                $previewList.append(json.preview);
                $previewList.on('click', '[data-media-delete]', function(event){ deleteMedia(event, this); });
                $previewList.sortable();

            }else{

                alert('Error');

            }
        });
    };
    
    this.close = function()
    {
        bootbox.hideAll();
    }
    
    function deleteMedia(event, element)
    {
        $(element).closest('[data-media]').hide(200, function(){ $(this).remove(); });
    }
    
    this.loadContent = function(title, content)
    {
        
        dialogContent = $(content);
        dialog = bootbox.dialog({
            size: 'large',
            title: title,
            message: dialogContent,
            buttons: false,
        });
    }
    
    this.addMedia = function(element){
        $.ajax({
            url: $(element).attr('data-media-url'),
            data: $.param({media_id: $(element).data('media-id')}),
            dataType: 'json',
        }).done(function(data){
            //El modal no se debe ocultar; debe permitir continuar agregando media
            $(element).closest('[data-media]').hide(200);
            $('#'+currentWidget).find('[data-preview-list]').append(data.preview);
            $('#'+currentWidget).find('[data-preview-list]').sortable();
        });
    }
    
    this.search = function(type, search, page){

        if(page === undefined){
            page = 1;
        }
        
        if(search === undefined){
            search = '';
        }

        var data = $.param({'MediaSearch[type]': type, 'MediaSearch[_search]': search, page: page});
        $.ajax({
            url: $('#'+currentWidget).find('[data-search-btn]').attr('data-media-url'),
            data: data,
            dataType: 'json',
        }).done(function(data){
            
            dialogContent.html(data.form);
            
            //Loading
            $('.bootbox .modal-body').css('opacity', 1);

            if(data.pages.pageCount > 0){
                
                $('#m-pagination').twbsPagination({
                    totalPages: data.pages.pageCount,
                    visiblePages: 10,
                    initiateStartPageClick: false, 
                    startPage: data.pages.currentPage,
                    first: '<<',
                    prev: '<',
                    next: '>',
                    last: '>>',
                    onPageClick: function (event, page) {
                        //Loading
                        $('.bootbox .modal-body').css('opacity', 0.5);
                        
                        Media.search(type, search, page);
                        
                        $('.bootbox').animate({
                            scrollTop: 0
                        }, 200);
                    }
                });
            }

        });

    }
    
    this.suscribe = function(event, listener)
    {
        if(!(event in events)){
            events[event] = [];
        }
        
        events[event].push(listener);
    }
    
    function trigger(event, target)
    {console.log(events)
        if(typeof events === 'object'){
            for (var e in events[event]){
                var listener = events[event][e];
                console.log(target);
                listener.call(target);
            }
        }
    }
}
