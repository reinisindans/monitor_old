var zoom_out;
var zoom_in;
var control;
var info_l;
var click;
var layercontrol;
var sideByside;
var vergleichcontrol;

$(function() {

    //map buttons---------------------------------
    //Center Map
    var center = L.control({position: 'topright'});
    center.onAdd = function (map) {
        var div = L.DomUtil.create('div');
        div.title="Die Ausdehnung der Karte auf Deutschland setzen";
        div.innerHTML = '<div class="germany btn_map"></div>';

        L.DomEvent
            .on(div, 'dblclick', L.DomEvent.stop)
            .on(div, 'click', L.DomEvent.stop)
            .on(div, 'mousedown', L.DomEvent.stopPropagation)
            .on(div, 'click', function(){
                indicatorJSONGroup.fitBounds();
            });

        return div;
    };
    center.addTo(map);

    zoom_out = L.control({position: 'topright'});
    zoom_out.onAdd = function (map) {
        var div = L.DomUtil.create('div');
        div.title="Aus der Karte herauszoomen";
        div.innerHTML = '<div class="zoomOut btn_map"></div>';

        L.DomEvent
            .on(div, 'dblclick', L.DomEvent.stop)
            .on(div, 'click', L.DomEvent.stop)
            .on(div, 'mousedown', L.DomEvent.stopPropagation)
            .on(div, 'click', function(){
                map.setZoom(map.getZoom()-1);
            });
        return div;
    };
    zoom_out.addTo(map);

    zoom_in = L.control({position: 'topright'});
    zoom_in.onAdd = function (map) {
        var div = L.DomUtil.create('div');
        div.title="In die Karte hineinzoomen";
        div.innerHTML = '<div class="zoomIn btn_map"></div>';

        L.DomEvent
            .on(div, 'dblclick', L.DomEvent.stop)
            .on(div, 'click', L.DomEvent.stop)
            .on(div, 'mousedown', L.DomEvent.stopPropagation)
            .on(div, 'click', function(){
                map.setZoom(map.getZoom()+1);
            });

        return div;
    };
    zoom_in.addTo(map);

    //the raster View Control
    rasterize = L.control({position: 'topright'});
    rasterize.onAdd = function (map) {
        var div = L.DomUtil.create('div');
        div.title='Die Karte glätten';
        div.innerHTML = '<div id="rasterize" class="rasterize btn_map"></div>';

        L.DomEvent
            .on(div, 'dblclick', L.DomEvent.stop)
            .on(div, 'click', L.DomEvent.stop)
            .on(div, 'mousedown', L.DomEvent.stopPropagation)
            .on(div, 'click', function(){
                if(getGlaettungModus()==0) {
                    setGlaettungModus(1);
                    initRaster();
                    $('#rasterize').css('background-color','#8CB91B');
                }else{
                    setGlaettungModus(0);
                    initRaster();
                    $('#rasterize').css('background-color',farbschema.getColorMain());
                }
            });

        return div;
    };

    //the comperative Map
    vergleichcontrol = L.control({position:'topright'});
    vergleichcontrol.onAdd = function(map){
        var div = L.DomUtil.create('div');
        div.title="Zwei Indikatorkarten miteinander Vergleichen";
        div.innerHTML = '<div class="vergleich btn_map" id="vergleich_btn"></div>';
        let timer;
        L.DomEvent
            .on(div, 'dblclick', L.DomEvent.stop)
            .on(div, 'click', L.DomEvent.stop)
            .on(div, 'mousedown', L.DomEvent.stopPropagation)
            .on(div, 'click', function(){
                openVergleichskarteDialog();
                if(!getSideBySideState()){
                    sideByside = L.control.sideBySide(getRasterLayer().addTo(map), null);
                    sideByside.addTo(map);
                }
            })
            .on(div,'mouseover',function () {
                if(getSideBySideState()){
                    timer = setTimeout(function(){
                        openVergleichskarteDialog();
                    },1000);
                }
            })
            .on(div,'mouseleave',function() {
                clearTimeout(timer);
            });

        return div;
    };

    info_l = L.control({position: 'topleft'});
    info_l.onAdd = function (map) {
        var div_l = L.DomUtil.create('div');
        div_l.title="Weiterführende Informationen zum Indikator";
        div_l.innerHTML = '<div class="info_lBtn btn_map"></div>';

        L.DomEvent
            .on(div_l, 'dblclick', L.DomEvent.stop)
            .on(div_l, 'click', L.DomEvent.stop)
            .on(div_l, 'mousedown', L.DomEvent.stopPropagation)
            .on(div_l, 'click', function(){
                $("#info_l").dialog({
                    hide: 'blind',
                    show: 'blind',
                    maxHeight: window.innerHeight - 15,
                    overflow:'scroll',
                    position: {
                        my: "right top",
                        at: "right top",
                        of: ".info_lBtn"
                    }
                });
            });

        return div_l;
    };
    //the layercontrol
    layercontrol = L.control.layers(baseMaps,overlayMaps);
    layercontrol.addTo(map);
    //expand the layer control with close icon
    $('.leaflet-control-layers-list').prepend('<div style="float: right;" id="close_layerlist">X</div>');
    //add the close option
    $('body').on("click","#close_layerlist",function(){$('.leaflet-control-layers').removeClass('leaflet-control-layers-expanded')});

    //Tools----------------------------------------
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

    //scalebar
    L.control.scale({
        metric:true,
        imperial: false,
        maxWidth: 200
    }).addTo(map);

    //style
    $('.leaflet-control-scale-line').css({"border-bottom-color":"black","border-right-color": "black","border-left-color":"black"});

    var mess = 0;
    var lupe = 0;
    var vergleich = 0;

    var measureControl = L.control.measure({
        primaryLengthUnit: 'kilometers',
        secondaryLengthUnit: 'meters',
        primaryAreaUnit: 'hectares',
        activeColor: farbschema.getColorActive(),
        completedColor: farbschema.getColorMain(),
        position: 'topright',
        localization: 'de',
        collapsed: false
    });

    $('#measure').click(function(){
        if (mess === 0) {
            lupe = 0;
            $.when(legende.close())
                .then($('#measure').css('background-color', farbschema.getColorActive()))
                .then(measureControl.addTo(map))
                .then(magnifyingGlass.remove())
                .then($('#lupe').css('background-color', farbschema.getColorMain()))
                .then(function(){
                        if(raeumlicheauswahl.getRaeumlicheGliederung()==='gebiete') {
                            $('.leaflet-control-measure').attr("style","margin-top : 90px !important;");
                        }
                    })
                .then($('.leaflet-control-measure-toggle ')
                    .animate({"width":"80px","height":"80px"},1000,
                        function(){
                            $(this).css({"width":"40px","height":"40px"})
                        }))
                .then($('.leaflet-control-measure-toggle').css("background-color",farbschema.getColorActive()));
            mess++;
        } else {
            mess = 0;
            $.when(measureControl.remove())
                .then($('#measure').css('background-color', '#4E60AA;'))
                .then(legende.Resize());
        }
        return false;
    });

    let magnifyingGlass = L.magnifyingGlass({
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
            $('#lupe').css('background-color',farbschema.getColorActive());
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
});

function getSideBySideControl(){
    return sideByside;
}
function removeSideBySideControl(){
    $.when($('.leaflet-sbs').remove())
        .then(cleanRasters())
        .then(initRaster())
        .then($('#indicator_ddm_vergleich').dropdown('clear'))
        .then($('.ind_content').hide());
}
function getSideBySideState(){
    if($('.leaflet-sbs').length >=1){
        return true;
    }else{
        return false;
    }
}
