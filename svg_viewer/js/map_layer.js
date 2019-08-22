var map;
var webatlas,webatlas_farbe;
var topplus,topplus_farbe;
var osm,osm_farbe;
var satellite,satellite_farbe;
var grenze_laender;
var grenze_kreise;
var grenze_gemeinden;
var layer_grund;
var noBackground;
var autobahn;
var fernbahnnetz;
var gew_haupt;
var baseMaps, baseMaps_farbe;
var overlayMaps;
var overlays = new L.FeatureGroup();
var backLayer;

$(function createLayer(){

    //Date for attribution Webatlas
    var currentdate = new Date();
    var datetime = currentdate.getFullYear();

    //Todo: Wenn Topplus nach auÃŸen freigegeben ist, kann dieser den topplus ersetzten

    //URL BKG
    var url_topplus = 'https://sgx.geodatenzentrum.de/wms_topplus_web_open';
    var url_webatlas = 'https://sg.geodatenzentrum.de/wms_webatlasde.light_grau?';
    var url_webatlas_farbe = 'https://sg.geodatenzentrum.de/wms_webatlasde.light?';

    //Layer BKG
    var topplus_layer = 'web_grau';
    var topplus_layer_farbe = 'webatlasde.light';
    var webatlas_layer = 'webatlasde.light_grau';

    // WMS Webatlas
    webatlas = new L.tileLayer.wms(url_webatlas, {
        layers: webatlas_layer,
        version: '1.3.0',
        format: 'image/png',
        srs:"EPSG:3035",
        transparent: true,
        name: "webatlas",
        attribution: '<a href="https://www.bkg.bund.de">TopPlus © GeoBasis- DE / BKG ('+datetime+')</a>'
    }).addTo(map);

    webatlas_farbe = new L.tileLayer.wms(url_webatlas_farbe, {
        layers: topplus_layer_farbe,
        version: '1.3.0',
        format: 'image/png',
        srs:"EPSG:3035",
        transparent: true,
        name: "webatlas_farbe",
        attribution: '<a href="https://www.bkg.bund.de">TopPlus © GeoBasis- DE / BKG ('+datetime+')</a>'
    });

    topplus = new L.tileLayer.wms(url_topplus, {
        layers: topplus_layer,
        version: '1.3.0',
        format: 'image/png',
        srs:"EPSG:3035",
        transparent: true,
        name: "topplus",
        attribution: '<a href="http://www.bkg.bund.de">TopPlus © GeoBasis- DE / BKG ('+datetime+')</a>'
    });

    topplus_farbe = new L.tileLayer.wms(url_topplus, {
        layers: 'web',
        version: '1.3.0',
        format: 'image/png',
        srs:"EPSG:3035",
        transparent: true,
        name: "webatlas_farbe",
        attribution: '<a href="http://www.bkg.bund.de">TopPlus © GeoBasis- DE / BKG ('+datetime+')</a>'
    });

    noBackground = L.tileLayer('',{name:"noBackground"});

    //Satellite
    satellite = L.tileLayer.grayscale('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
        attribution: '&copy; Esri',name: "satellite"});

    satellite_farbe = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
        attribution: '&copy; Esri',name: "satellite_farbe"});

    //OSM
    osm = L.tileLayer.grayscale('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',name: "osm"});

    osm_farbe = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',name: "osm_farbe"});

    layer_grund = new L.tileLayer.wms('http://maps.ioer.de/cgi-bin/mapserv_dv?Map=' + localStorage.getItem("mapfile_datenalter"),
        {
            layers: localStorage.getItem("layer_datenalter"),
            version: '1.3.0',
            format: 'image/png',
            transparent: true,
            opacity: 0.5
        });

    grenze_laender =new L.GeoJSON('',{
        onEachFeature: onEachFeature
    });

    grenze_kreise = new L.GeoJSON('',{
        onEachFeature: onEachFeature
    });

    grenze_gemeinden = new L.GeoJSON('',{
        onEachFeature: onEachFeature
    });

    autobahn = new L.GeoJSON('',{
        onEachFeature: onEachFeature
    });
    fernbahnnetz = new L.GeoJSON('',{
        onEachFeature: onEachFeature
    });
    gew_haupt = new L.GeoJSON('',{
        onEachFeature: onEachFeature
    });


    baseMaps = {
        "WebAtlas_DE": webatlas,
        "TopPlus-Web-Open": topplus,
        "Satellit":satellite,
        "OSM": osm,
        "kein Hintergrund": noBackground
    };

    baseMaps_farbe = {
        "WebAtlas_DE": webatlas_farbe,
        "TopPlus-Web-Open": topplus_farbe,
        "Satellit":satellite_farbe,
        "OSM": osm_farbe,
        "kein Hintergrund": noBackground
    };

    overlayMaps = {
        "Ländergrenzen":grenze_laender,
        "Kreisgrenzen":grenze_kreise,
        "Gemeindegrenzen":grenze_gemeinden,
        "Autobahnnetz (Stand 2015)":autobahn,
        "Fernbahnnetz (Stand 2016)":fernbahnnetz,
        "Hauptfließgewässer": gew_haupt
    };

    map.on('overlayadd', function(e) {

        var time = getTime();

        if(time == 2016){
            time = 2015;
        }

        var url = "php/holeZusatzLayer.php";

        console.log("Hole Overlay, mit den Parametern:"+time);

        if(e.name === "Autobahnnetz (Stand 2015)") {
            $.ajax({
                url: url,
                type: "GET",
                dataType: 'json',
                data: {
                    'LAYER': 'bab_grossmasstaeblich'
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    console.log(xhr.status);
                    console.log(thrownError);
                    alertError();
                    removeProgressBar();
                },
                success: function (data) {
                    var data = JSON.parse((data));
                    autobahn.addData(data);
                    autobahn.setStyle({
                        weight: 3,
                        opacity: 1,
                        color: 'yellow'
                    });
                    overlays.addLayer(autobahn).addTo(map);
                }
            });
        }
        else if(e.name === "Fernbahnnetz (Stand 2016)"){
            $.ajax({
                url: url,
                type: "GET",
                dataType: 'json',
                data: {
                    'LAYER': 'db_fernverkehr_kleinmassstaeblich'
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    console.log(xhr.status);
                    console.log(thrownError);
                    alertError();
                    removeProgressBar();
                },
                success: function (data) {
                    var data = JSON.parse((data));
                    fernbahnnetz.addData(data);
                    fernbahnnetz.setStyle({
                        weight: 3,
                        opacity: 1,
                        color: 'black'
                    });
                    overlays.addLayer(fernbahnnetz).addTo(map);
                }
            });
        }
        else if(e.name === "Hauptfließgewässer"){
            $.ajax({
                url: url,
                type: "GET",
                dataType: 'json',
                data: {
                    'LAYER': 'hauptgewaesser'
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    console.log(xhr.status);
                    console.log(thrownError);
                    alertError();
                    removeProgressBar();
                },
                success: function (data) {
                    var data = JSON.parse((data));
                    gew_haupt.addData(data);
                    gew_haupt.setStyle({
                        weight: 3,
                        opacity: 1,
                        color: 'blue'
                    });
                    overlays.addLayer(gew_haupt).addTo(map);
                }
            });
        }
        else if(e.name === "Ländergrenzen") {
            $.ajax({
                url: url,
                type: "GET",
                dataType: 'json',
                data: {
                    'LAYER': 'vg250_bld_'+time+'_grob'
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    console.log(xhr.status);
                    console.log(thrownError);
                    alertError();
                    removeProgressBar();
                },
                success: function (data) {
                    var data = JSON.parse((data));
                    grenze_laender.addData(data);
                    grenze_laender.setStyle({
                        weight: 2,
                        opacity: 1,
                        color: 'black',
                        fillOpacity: 0,
                        dashArray: '3'
                    });
                    overlays.addLayer(grenze_laender).addTo(map);
                }
            });
        }
        else if(e.name === "Kreisgrenzen") {
            setProgressBar();
            setProgressHeader("Erstelle Layer");
            $.ajax({
                url: url,
                type: "GET",
                dataType: 'json',
                data: {
                    'LAYER': 'vg250_krs_'+time+'_grob'
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    console.log(xhr.status);
                    console.log(thrownError);
                    alertError();
                    removeProgressBar();
                },
                success: function (data) {
                    var data = JSON.parse((data));
                    grenze_kreise.addData(data);
                    grenze_kreise.setStyle({
                        weight: 1,
                        opacity: 1,
                        color: 'black',
                        dashArray: '3',
                        fillOpacity: 0
                    });
                    overlays.addLayer(grenze_kreise).addTo(map);
                    removeProgressBar();
                }
            });
        }
        else if(e.name === "Gemeindegrenzen") {
            setProgressBar();
            setProgressHeader("Erstelle Layer");
            $.ajax({
                url: url,
                type: "GET",
                dataType: 'json',
                data: {
                    'LAYER': 'vg250_gem_'+time+'_grob'
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    console.log(xhr.status);
                    console.log(thrownError);
                    alertError();
                    removeProgressBar();
                },
                success: function (data) {
                    var data = JSON.parse((data));
                    grenze_gemeinden.addData(data);
                    grenze_gemeinden.setStyle({
                        weight: 1,
                        opacity: 1,
                        color: 'black',
                        dashArray: '3',
                        fillOpacity: 0
                    });
                    overlays.addLayer(grenze_gemeinden).addTo(map);
                    removeProgressBar();
                }
            });
        }
    });

    map.on('baselayerchange', function (e) {
        if (map.hasLayer(raster)) {
            raster.bringToFront();
        }
        if (map.hasLayer(raster_links)) {
            raster_links.bringToFront();
        }
        if (map.hasLayer(raster_rechts)) {
            raster_rechts.bringToFront();
        }
    });

    function highlightFeature(e) {
        var layer = e.target;

        layer.bringToBack();
    }

    function onEachFeature(feature, layer) {
        layer.on({
            mouseover: highlightFeature
        });
    }
});

function bringOverlaysToFront(){
    overlays.eachLayer(function(layer){
        layer.bringToFront();
    });
}
function getOverlayLayer(){
    return overlays;
}
function getBaseMaps(){
    return baseMaps;
}
function getOverlayMaps(){
    return overlayMaps;
}