var map,raster_rechts,download_State=false;

$(function storage_position(){

    map.on('moveend',         function () {

        var centerPoint = map.getSize().divideBy(2),
            targetLatLng = map.containerPointToLatLng(centerPoint);

        updateURLParameter('lat',targetLatLng.lat);
        updateURLParameter('lng',targetLatLng.lng);

    });
    map.on('zoomend',function () {

        var zoom = map.getZoom();
        updateURLParameter('zoom',zoom);

    });
});
function setMapView(lat,lng,zoom){
    var zoom_set,lat_set,lng_set;

    //Zoom
    if(!zoom){
        var zoom_param = getUrlParameter('zoom');
        if(!zoom_param) {
            setUrlParameter('zoom', 8);
            zoom_set = 8;
        }else{
            zoom_set = zoom_param;
        }
    }else{
        updateURLParameter('zoom',zoom);
        zoom_set = zoom;
    }
    //lat
    if(!lat){
        var lat_param = getUrlParameter('lat');
        if(!lat_param) {
            setUrlParameter('lat', 50.9307);
            lat_set = 50.9307;
        }else{
            lat_set = lat_param;
        }
    }else{
        updateURLParameter('lat',lat);
        lat_set = lat;
    }
    //lng
    if(!lng){
        var lng_param = getUrlParameter('lng');
        if(!lng_param){
            setUrlParameter('lng',9.7558);
            lng_set = 9.7558;
        }else{
            lng_set = lng_param;
        }
    }else{
        updateURLParameter('lng',lng);
    }
    map.setView(new L.LatLng(lat_set, lng_set), zoom_set);
}
$(function controls(){

    //Import
    var loader =  L.Control.fileLayerLoad({
        // Allows you to use a customized version of L.geoJson.
        // For example if you are using the Proj4Leaflet leaflet plugin,
        // you can pass L.Proj.geoJson and load the files into the
        // L.Proj.GeoJson instead of the L.geoJson.
        layer: L.geoJson,
        // See http://leafletjs.com/reference.html#geojson-options
        layerOptions: {style: {color:'red'}},
        // Add to map after loading (default: true) ?
        addToMap: true,
        // File size limit in kb (default: 1024) ?
        fileSizeLimit: 1024,
        // Restrict accepted file formats (default: .geojson, .kml, and .gpx) ?
        formats: [
            '.geojson',
            '.kml',
            '.gpx'
        ]
    }).addTo(map);

    document.getElementById("import").appendChild(loader.getContainer());

    L.control.scale({
        metric:true,
        imperial: false,
        maxWidth: 100
    }).addTo(map);

    //style
    $('.leaflet-control-scale-line').css({"border-bottom-color":"black","border-right-color": "black","border-left-color":"black"});

    var mess = 0;
    var lupe = 0;
    var time= 0;
    var glaetten = 0;

    var measureControl = L.control.measure({
        primaryLengthUnit: 'kilometers',
        secondaryLengthUnit: 'meters',
        primaryAreaUnit: 'hectares',
        activeColor: '#8CB91B',
        completedColor: '#284496',
        position: 'topright',
        localization: 'de',
        collapsed: false
    });

    $('#measure').click(function(){
        console.log('click');
        if(mess == 0){
            $('.toolbar').toggleClass("toolbar_close",500);
            $('.slider_zeit_container').toggleClass("slider_zeit_container_toggle",500);
            $('#measure').css('background-color','#8CB91B');
            measureControl.addTo(map);
            magnifyingGlass.remove();
            $('#lupe').css('background-color','#4E60AA');
            lupe=0;
            $('.leaflet-control-measure').css("cursor","pointer");
            $('.leaflet-control-measure').animate({width: "500%"}, 'slow');

            setTimeout(function() {
                $('.leaflet-control-measure').removeAttr("style");
            }, 1000 );
            mess++;
        }else{
            measureControl.remove();
            $('#measure').css('background-color','#4E60AA;');
            mess=0;
        }
        return false;
    });

    var magnifyingGlass = L.magnifyingGlass({
        layers: [
            layer = L.tileLayer.grayscale('http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
            })
        ]
    });

    $('#lupe').click(function(){
        if(lupe == 0){
            $('.toolbar').toggleClass("toolbar_close",500);
            $('.slider_zeit_container').toggleClass("slider_zeit_container_toggle",500);
            $('#lupe').css('background-color','#8CB91B;');
            magnifyingGlass.addTo(map);
            measureControl.remove();
            $('#measure').css('background-color','#4E60AA;');
            mess=0;
            lupe++;
        }else{
            magnifyingGlass.remove();
            $('#lupe').css('background-color','#4E60AA;');
            lupe=0;
        }
        return false;
    });

    if (localStorage.getItem("glaetten") === null) {
        localStorage.setItem("glaetten",0);
    }

    $('#glaettung').click(function () {
        if(localStorage.getItem("glaetten") == 0) {
            $('#glaettung').css('background-color','#8CB91B;');
            localStorage.setItem("darstellungMap",1);
            localStorage.setItem("glaetten",1);
            if(map.hasLayer(raster_links)){
                holeKarteGeoJSON();
                holeKarte_rechts();
                $('#rasterize').css('background-color','#8CB91B');
            }else{
                holeKarteGeoJSON();
                $('#rasterize').css('background-color','#8CB91B');
            }
        }else{
            $('#glaettung').css('background-color','#4E60AA;');
            localStorage.setItem("darstellungMap",0);
            localStorage.setItem("glaetten",0);
            if(map.hasLayer(raster_links)){
                holeKarteGeoJSON();
                holeKarte_rechts();
                $('#rasterize').css('background-color','#4E60AA');
            }else{
                holeKarteGeoJSON();
                $('#rasterize').css('background-color','#4E60AA');
            }
        }
        return false;
    });


    // Disable dragging when user's cursor enters the element
    $('#slider_zeit_container').mouseenter(function () {
        map.dragging.disable();
        slider_hover = 0;
        return false;
    })
        .mouseleave(function() {
            map.dragging.enable();
            slider_hover = 1;
            return false;
        });
});
function DotToComma(int){
    var res = int.toString().replace(/\./g, ',');
    return res;
}
function checkIndAvaliablitlity_grenzen(ind,time){
    $.ajax({
        type: "GET",
        dataType: 'json',
        url: "php/checkInd_avaliability.php",
        data: {
            'indikator': ind,
            'jahr':time
        },
        success: function (data) {
            console.log("check avability:"+ind+"||"+time+" ergebnis="+JSON.stringify(data));
            if(JSON.stringify(data) === '[]'){
                swal({
                    title: "Der Indikator ist nicht als Gebietskarte verfügbar",
                    type: "warning",
                    showCancelButton: false
                });
                LoadMenuByIndicator("S12RG");
            }else{
                var resultJson = $.parseJSON(JSON.stringify(data));
                var test_ind = _.find(resultJson, function (obj) { return obj.Jahr === time; });
                var text_ind = $(ind+'_item').text();
                if(test_ind){
                    localStorage.setItem("jahr_set_ui",time);
                    LoadMenuByIndicator(ind);
                }else{
                    var obergrenze_max = Math.max.apply(Math, resultJson.map(function (o) {
                        return o.Jahr;
                    }));
                    console.log(obergrenze_max);
                    localStorage.setItem("jahr_set_ui",obergrenze_max);
                    LoadMenuByIndicator(ind);
                }
            }
        }
    });
}

function setProgressBar() {
    removeProgressBar();
    showProgressBar();
    $('.progress').append('<div id="progressbar_div"><div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div><div></div>');
    var raumgl_grob = getRaumgliederung_grenzen_val();
    var raumgl_fein = getRaumgliederungfein_val();

    var progressBar = $('.progress-bar');
    var percentVal = 0;
    var intervall = 0;

    if(map.hasLayer(geojson)) {
        if (raumgl_grob === "stt") {
            intervall = 3500;
        }
        else if (getExpandArray().length >= 2) {
            intervall = 3500;
        }
        else if (raumgl_fein === "stt") {
            intervall = 3500;
        }
        else if (raumgl_fein === "gem") {
            if (countTagsDDM('#dropdown_grenzen_container') === 0) {
                intervall = 9000;
            } else {
                intervall = 4000;
            }
        }else{
            intervall = 1500;
        }
    }
    else {
        intervall = 1500;
    }
    console.log("zeige Progressbar mit dem Intervall: "+intervall);

    window.setInterval(function () {
        percentVal += 10;
        progressBar.css("width", percentVal + '%').attr("aria-valuenow", percentVal + '%').text(percentVal + '%');

        if (percentVal === 100) {
            percentVal = 0;
        }
    }, intervall);
}
 function removeProgressBar(){
     console.log("remove Progressbar");
     $('#Modal').css(
         {
             "position":"",
             "width":"",
             "height":"",
             "background-color":"",
             "filter":"",
             "opacity":"",
             "-moz-opacity":"",
             "z-index":"",
             "text-align":"",
             "vertical-align":"",
             "pointer-events": ""
         }
     );
     $('#progress_div').remove();
 }
 function setProgressHeader(string){
     $('#progress_header')
         .empty()
         .text(string);
 }
 function showProgressBar(){
     $('body').append('<div id="progress_div"><h2 id="progress_header"></h2><div class="progress"></div></div>');
     $('#progress_div').show();

     $('#Modal').css(
         {
             "position":"absolute",
             "width":"100%",
             "height":"100%",
             "background-color":"#000000",
             "filter":"alpha(opacity=60)",
             "opacity":"0.1",
             "-moz-opacity":"0.6",
             "z-index":"100",
             "text-align":"center",
             "vertical-align":"middle",
             "user-select": "none"
         }
     );
 }
function setBaseLayer(hintergrund){

    //set the background Layer
    if(hintergrund == 'topplus'){
        topplus.addTo(map);
    }
    if(hintergrund == 'osm'){
        osm.addTo(map);
    }
    if(satellite == 'satellite'){
        satellite.addTo(map);
    }
}
function zusatzLayer(array){
    //set the additional Layer

    overlays.clearLayers();

    for(var i = 0; i<array.length; i++){
        if(array[0][i] == 'grenze_laender'){
            grenze_laender.addTo(map);
            i++;
        }
        if(array[0][i] == 'grenze_kreise'){
            grenze_kreise.addTo(map);
            i++;
        }
        if(array[0][i] == 'grenze_gemeinden'){
            grenze_gemeinden.addTo(map);
            i++;
        }
        if(array[0][i] == 'autobahn'){
            autobahn.addTo(map);
            i++;
        }
        if(array[0][i] == 'fernbahnnetz'){
            fernbahnnetz.addTo(map);
            i++;
        }
        if(array[0][i] == 'gew_haupt'){
            gew_haupt.addTo(map);
            i++;
        }
    }
}

$(window).on('resize', function() {
    if($('.jq_dialog').is(":visible")===false) {
        //auffangen, das bei einem download die Seite nicht neu geladen wird
            this.location.href = this.location.href;
    }
});

function getDownloadState(){
    return download_State;
}
function setDownloadState(state){
    download_State = state;
}

function checkOverlap(ele1,ele2){

    var coverElem = ele2;
    var elemArray = [];
    elemArray.push(ele1,ele2);

    var state;

    for(i=0; i< elemArray.length; i++)
    {
        var currElemOffset = elemArray[i].offset();
        var currElemWidth = elemArray[i].width();

        var currElemStPoint = currElemOffset.left ;
        var currElemEndPoint = currElemStPoint + currElemWidth;
        state = currElemStPoint <= coverElem.offset().left && coverElem.offset().left <= currElemEndPoint;
    }

    return state;
}
function removefromarray(array,value_remove){
    var array_cleaned = [];
    $.each(array,function(key,value){
        if(value.id != value_remove){
            //must seperate time values
            if(value.id === getIndikatorSelectVal()){
                if(value.time != value_remove){
                    array_cleaned.push(value);
                }
            }else{
                array_cleaned.push(value);
            }
        }
    });

    return array_cleaned;
}
function getMaxArray(array, propName){
    return Math.max.apply(Math, array.map(function(i) {
        return i[propName];
    }));
}
function getMinArray(array, propName){
    return Math.min.apply(Math, array.map(function(i) {
        return i[propName];
    }));
}


