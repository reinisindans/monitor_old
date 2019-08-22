var map;
var zoom_out;
var zoom_in;
var control;
var info_l;
var click;
var layercontrol;

$(function() {
    $('#raster_grenzen').on('click', function () {
        window.open(getURL_RASTER()+"raster.html?"+getAllParameters(), "_self");
        return false;
    });

    $('#swipe_m_grenzen').on('click', function () {
        window.open(getURL_RASTER()+"slider.html?"+getAllParameters(), "_self");
        return false;
    });

    $('#grenzen').on('click', function () {
        var kat = getKategorie_val();
        var ind = getIndikatorSelectVal();
        var time = getTime();
        window.open(getURL_SVG()+"index.html?"+getAllParameters(), "_self");
        return false;
    });

    //The Map Reset
    $('#btn_reset').click(function () {
        var url = window.location.href.replace(window.location.search,'');
        window.open(url,"_self");
        return false;
    });

    var click_farb = 0;
    //the color schema
    $("#farbwahl_btn").click(function () {
        if(click_farb ==0) {
            $("#color_schema").show();
            click_farb++;
        }else{
            $('#color_schema').hide();
            click_farb = 0;
        }

    });

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
                map.setView(new L.LatLng(50.9307, 9.7558),6);
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

    /*control = L.control.zoomBox({
        modal: true,
        position: 'topright'
    }).addTo(map);*/

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
                if(localStorage.getItem("glaetten") == 0) {
                    localStorage.setItem("darstellungMap",1);
                    localStorage.setItem("glaetten",1);
                    if(map.hasLayer(raster_links)){
                        holeKarte_links();
                        holeKarte_rechts();
                        $('#rasterize').css('background-color','#8CB91B');
                    }else{
                        holeKarte_links();
                        $('#rasterize').css('background-color','#8CB91B');
                    }
                }else{
                    localStorage.setItem("darstellungMap",0);
                    localStorage.setItem("glaetten",0);
                    if(map.hasLayer(raster_links)){
                        holeKarte_links();
                        holeKarte_rechts();
                        $('#rasterize').css('background-color','#4E60AA');
                    }else{
                        holeKarte_links();
                        $('#rasterize').css('background-color','#4E60AA');
                    }
                }
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
    layercontrol = L.control.layers(baseMaps,overlayMaps);
    layercontrol.addTo(map);

    //disbale hover
    $('.leaflet-control-layers').unbind('mouseover').unbind('mouseleave');

});

//overlapping check
function doTheyOverlap(el0, el1)
{
    var elY0 = (el0.offsetTop < el1.offsetTop)? el0 : el1;
    var elY1 = (el0 != elY0)? el0 : el1;
    var yInstersection = (elY0.offsetTop + elY0.clientHeight) - elY1.offsetTop > 0;

    var elX0 = (el0.offsetLeft < el1.offsetLeft)? el0 : el1;
    var elX1 = (el0 != elX0)? el0 : el1;
    var xInstersection = (elX0.offsetLeft + elX0.clientWidth) - elX1.offsetLeft > 0;

    return (yInstersection && xInstersection);
}
function getTest(){
    return test;
}