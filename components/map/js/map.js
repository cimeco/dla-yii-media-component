/**
 * Created by juan on 19/01/18.
 */


var MapForm= new function(){


    this.map;
    this.markers= [];
    this.selectedMarkeLat;
    this.selectedMarkeLng;

    this.init= function(){
        $(document).on('shown.bs.modal', function(){
            MapForm.initMap();
        });


        $('.map-form').on('click', '.delete-marker', function(e){
            e.preventDefault();
            MapForm.deleteMarker($(this).data('lat'), $(this).closest('a').data('lng'), $(this).data('i'));
        });

        //MapForm.initMap();

    }

    this.initMap= function (){
        MapForm.map = new google.maps.Map(document.getElementById('map-canvas'), {
            center: {lat: -32.8912173, lng: -68.8394868},
            zoom: 18
        });

        MapForm.map.addListener('click', function(event){
            MapForm.addMaker(event.latLng);
        });

        MapForm.map.addListener('center_changed', function(){
            $('[name= "Map[_center_lat]"]').val(MapForm.map.getCenter().lat());
            $('[name= "Map[_center_lng]"]').val(MapForm.map.getCenter().lng());
        })

        MapForm.map.addListener('zoom_changed', function(){
            $('[name= "Map[_zoom]"]').val(MapForm.map.getZoom());
        })

        var input = (document.getElementById('map_search'));
        var autocomplete = new google.maps.places.Autocomplete(input, {});
        autocomplete.bindTo('bounds', MapForm.map);

        autocomplete.addListener('place_changed', function() {

            var place = autocomplete.getPlace();
            if (place.geometry) {
                MapForm.map.setCenter(place.geometry.location);
                MapForm.map.setZoom(18);
            }
            MapForm.addMaker(place.geometry.location, place.name);
            return ;
        });

        //map_status= true;

    }


    /**
     * Crea un marcador y lo agrega al mapa
     * @param {type} latlng
     * @returns {undefined}
     */
    this.addMaker= function(latlng, title){
        var lat= latlng.lat();
        var lng= latlng.lng();
        var i= $('#marker-list').find('li').length;
        var marker = new google.maps.Marker({
            position: {lat:lat, lng: lng},
            map: MapForm.map,
            animation: google.maps.Animation.DROP,
            title: "<?php echo Yii::t('app', 'Marker')?> Nº" + (i+1),
            draggable: true

        });

        marker.addListener('click', function(e){
            MapForm.editMarker(e.latLng.lat(), e.latLng.lng())
        });

        marker.addListener('dragstart', function(e){
            MapForm.selectedMarkeLat= e.latLng.lat();
            MapForm.selectedMarkeLng= e.latLng.lng();
        });

        marker.addListener('dragend', function(e){
            MapForm.changeMarkerPosition(e.latLng.lat(), e.latLng.lng());
        });

        var m={lat:lat, lng: lng, description: '', obj: marker, i: MapForm.markers.length};
        $('#marker-list').append('<li id="m'+i+'" style="border-bottom:  #b9bdc1 solid 1px;"><br></li>');
        if (title !== undefined) {
            $('#m'+i).append('<label>' + "Marcador Nº" + (i+1) + ': '+'</label>  <a class="delete-marker pull-right" href="#" data-lat="'+ lat +'" data-lng="'+ lng + '" data-i="'+i+'"><span class="glyphicon glyphicon-trash"></span> '+"Eliminar"+'</a><br>')
            $('#m'+i).append('<h6>' + title +'</h6>  ')
        }else{
            $('#m'+i).append('<label>' + "Marcador Nº" + (i+1) +'</label> <a class="delete-marker pull-right" href="#" data-lat="'+ lat +'" data-lng="'+ lng + '" data-i="'+i+'"><span class="glyphicon glyphicon-trash"></span> '+"Eliminar"+'</a><br>')
        }
        $('#m'+i).append("<input type='hidden' name='Map[_markers]["+ i + "][lat]' value='"+ lat + "'>");
        $('#m'+i).append("<input type='hidden' name='Map[_markers]["+ i + "][lng]' value='"+ lng + "'>");
        $('#m'+i).append('<div class="input-group"><label>Descripción:</label><input type="text" name="Map[_markers]['
        + i + '][description]" class="form-control form-control-sm"></div><br>');

        $("#scroll-div").animate({
            scrollTop: $('#m'+i).offset().top
        }, 0500);

        MapForm.markers.push(m);
        //MapForm.editMarker(lat, lng);
    }

    /**
     * Edita un marcador seleccionado
     * @param {type} lat
     * @param {type} lng
     * @returns {undefined}
     */
    this.editMarker= function(lat, lng){
        var mark;
        for (var i = 0; i < MapForm.markers.length; i++) {
            if (MapForm.markers[i].lat === parseFloat(lat) && MapForm.markers[i].lng === parseFloat(lng)) {
                mark = MapForm.markers[i];
            }
        }

        if(mark !== undefined){
            $('#mark_description').val(mark.description);
        }

        $('#mark_lat').val(lat);
        $('#mark_lng').val(lng);

        $('.marker-opt').show(0500);
    }

    /**
     * Guarda la info de un marcador
     * @returns {undefined}
     */
    this.saveMarker = function(){
        var description = $('#mark_description').val();
        var lat= $('#mark_lat').val();
        var lng= $('#mark_lng').val();
        var mark;

        for (var i = 0; i < MapForm.markers.length; i++) {
            if (MapForm.markers[i].lat === parseFloat(lat) && MapForm.markers[i].lng === parseFloat(lng)) {
                mark = MapForm.markers[i];
            }
        }



        mark.description= description;
        mark.obj.setTitle(description);
        $('[name= "Map[_markers]['+ mark.i + '][description]"]').val(description);

        var mark_index = MapForm.markers.findIndex(function(mark){ return mark.lat=== lat && mark.lng=== lng});

        MapForm.markers[mark_index] = mark;

        $('.marker-opt').hide(0500);
    }

    this.deleteMarker = function (lat, lng, j){

        var mark;

        for (var i = 0; i < MapForm.markers.length; i++) {
            if (MapForm.markers[i].lat === parseFloat(lat) && MapForm.markers[i].lng === parseFloat(lng)) {
                mark = MapForm.markers[i];
                MapForm.markers[i].obj.setMap(null);
                MapForm.markers.splice(i, 1);
            }
        }


        $('#m'+j).remove();
    }

    this.changeMarkerPosition= function(lat, lng){

        for (var i = 0; i < MapForm.markers.length; i++) {

            if (MapForm.markers[i].lat === parseFloat(MapForm.selectedMarkeLat) && markers[i].lng === parseFloat(MapForm.selectedMarkeLng)) {
                $('[name="Map[_markers]['+ i + '][lat]"]').val(lat);
                $('[name="Map[_markers]['+ i + '][lng]"]').val(lng);
                MapForm.markers[i].lat = lat;
                MapForm.markers[i].lng = lng;
            }
        }


    }

}