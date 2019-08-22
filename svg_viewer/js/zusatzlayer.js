var webatlas,
    webatlas_farbe,
    topplus,
    topplus_farbe,
    osm,
    osm_farbe,
    satellite,
    satellite_farbe,
    grenze_laender,
    grenze_kreise,
    grenze_gemeinden,
    layer_grund,
    noBackground,
    autobahn,
    fernbahnnetz,
    gew_haupt,
    baseMaps,
    baseMaps_farbe,
    overlayMaps,
    overlays = new L.FeatureGroup();

$(document).ready(function createLayer(){
    //Date for attribution Webatlas
    var currentdate = new Date();
    var datetime = currentdate.getFullYear();

    //URL BKG
    var url_topplus = 'https://sgx.geodatenzentrum.de/wms_topplus_web_open';
    var url_webatlas = 'https://sg.geodatenzentrum.de/wms_webatlasde.light_grau?';
    var url_webatlas_farbe = 'https://sg.geodatenzentrum.de/wms_webatlasde.light?';

    //Layer BKG
    var topplus_layer = 'web_grau';
    var topplus_layer_farbe = 'web';
    var webatlas_layer_farbe = 'webatlasde.light';
    var webatlas_layer = 'webatlasde.light_grau';

    // WMS Webatlas
    webatlas = new L.tileLayer.wms(url_webatlas, {
        layers: webatlas_layer,
        version: '1.3.0',
        format: 'image/png',
        srs:"EPSG:3035",
        transparent: true,
        name: "webatlas",
        attribution: '<a href="http://www.geodatenzentrum.de/geodaten/gdz_rahmen.gdz_div?gdz_spr=deu&gdz_akt_zeile=4&gdz_anz_zeile=4&gdz_unt_zeile=0&gdz_user_id=0">© GeoBasis- DE / BKG ('+datetime+')</a>',
        id: 'baselayer'
    });

    webatlas_farbe = new L.tileLayer.wms(url_webatlas_farbe, {
        layers: webatlas_layer_farbe,
        version: '1.3.0',
        format: 'image/png',
        srs:"EPSG:3035",
        transparent: true,
        name: "webatlas_farbe",
        attribution: '<a href="http://www.geodatenzentrum.de/geodaten/gdz_rahmen.gdz_div?gdz_spr=deu&gdz_akt_zeile=4&gdz_anz_zeile=4&gdz_unt_zeile=0&gdz_user_id=0">© GeoBasis- DE / BKG ('+datetime+')</a>',
        id: 'baselayer'
    });

    topplus = new L.tileLayer.wms(url_topplus, {
        layers: topplus_layer,
        version: '1.3.0',
        format: 'image/png',
        srs:"EPSG:3035",
        transparent: true,
        name: "topplus",
        attribution: '<a href="http://www.bkg.bund.de">TopPlus © GeoBasis- DE / BKG ('+datetime+')</a>',
        id: 'baselayer'
    });

    topplus_farbe = new L.tileLayer.wms(url_topplus, {
        layers: topplus_layer_farbe,
        version: '1.3.0',
        format: 'image/png',
        srs:"EPSG:3035",
        transparent: true,
        name: "webatlas_farbe",
        attribution: '<a href="http://www.bkg.bund.de">TopPlus © GeoBasis- DE / BKG ('+datetime+')</a>',
        id: 'baselayer'
    });

    flaechenschema = new L.tileLayer.wms("https://maps.ioer.de/cgi-bin/mapserv_dv?map=/mapsrv_daten/detailviewer/wms_mapfiles/flaechenschema.map", {
        layers: "Flaechenschema",
        version: '1.3.0',
        format: 'image/png',
        srs:"EPSG:3035",
        transparent: true,
        name: "flaechenschema",
        attribution: '<a href="http://www.bkg.bund.de">TopPlus © GeoBasis- DE / BKG ('+datetime+')</a>',
        id: 'baselayer'
    });

    noBackground = L.tileLayer('',{name:"noBackground",id: 'baselayer'});

    //Satellite
    satellite = L.tileLayer.grayscale('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
        attribution: '&copy; Esri',name: "satellite",id: 'baselayer'});

    satellite_farbe = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
        attribution: '&copy; Esri',name: "satellite_farbe",id: 'baselayer'});

    //OSM
    osm = L.tileLayer.grayscale('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',name: "osm",id: 'baselayer'});

    osm_farbe = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',name: "osm_farbe",id: 'baselayer'});

    layer_grund = new L.tileLayer.wms('http://maps.ioer.de/cgi-bin/mapserv_dv?Map=' + localStorage.getItem("mapfile_datenalter"),
        {
            layers: localStorage.getItem("layer_datenalter"),
            version: '1.3.0',
            format: 'image/png',
            transparent: true,
            opacity: 0.5,
            name: "layer_grund",
            id: 'baselayer'
        });

    grenze_laender =new L.GeoJSON('',{
        name: 'grenze_laender',
        onEachFeature: onEachFeature
    });

    grenze_kreise = new L.GeoJSON('',{
        name: 'grenze_kreise',
        onEachFeature: onEachFeature
    });

    grenze_gemeinden = new L.GeoJSON('',{
        name: 'grenze_gemeinden',
        onEachFeature: onEachFeature
    });

    autobahn = new L.GeoJSON('',{
        name: 'autobahn',
        onEachFeature: onEachFeature
    });
    fernbahnnetz = new L.GeoJSON('',{
        name: 'fernbahnnetz',
        onEachFeature: onEachFeature
    });
    gew_haupt = new L.GeoJSON('',{
        name: 'gew_haupt',
        onEachFeature: onEachFeature
    });

    if(getTest_State() == 1) {
        baseMaps = {
            "WebAtlas_DE": webatlas,
            "TopPlus-Web-Open": topplus,
            "Satellit": satellite,
            "OSM": osm,
            "Flächenschema": flaechenschema,
            "kein Hintergrund": noBackground
        };
    }else{
        baseMaps = {
            "WebAtlas_DE": webatlas,
            "kein Hintergrund": noBackground,
            "TopPlus-Web-Open": topplus,
            "Satellit": satellite,
            "OSM": osm
        };
    }

    baseMaps_farbe = {
        "WebAtlas_DE": webatlas_farbe,
        "kein Hintergrund": noBackground,
        "TopPlus-Web-Open": topplus_farbe,
        "Satellit":satellite_farbe,
        "OSM": osm_farbe
    };

    overlayMaps = {
        "Ländergrenzen":grenze_laender,
        "Kreisgrenzen":grenze_kreise,
        "Gemeindegrenzen":grenze_gemeinden,
        "Autobahnnetz (Stand 2015)":autobahn,
        "Fernbahnnetz (Stand 2016)":fernbahnnetz,
        "Hauptfließgewässer": gew_haupt
    };

    //Callbacks --------------------------------
    map.on('overlayadd', function(e) {

        let time = zeitslider.getTimeSet();

        if(time >= 2016){
            time = 2015;
        }

        if(e.name === "Autobahnnetz (Stand 2015)") {
            $.when(getZusatzlayer('bab_grossmasstaeblich')).done(function(_data){
                let json = JSON.parse((_data));
                autobahn.addData(json);
                autobahn.setStyle(styleGeoJSON.autobahn);
                zusatzlayer.setStyleSet(styleGeoJSON.autobahn);
                zusatzlayer.setLayerSet(autobahn);
                zusatzlayer.setParam();
            });
        }
        else if(e.name === "Fernbahnnetz (Stand 2016)"){
            $.when(getZusatzlayer('db_fernverkehr_kleinmassstaeblich')).done(function(_data){
                let json = JSON.parse((_data));
                fernbahnnetz.addData(json);
                fernbahnnetz.setStyle(styleGeoJSON.fernbahnnetz);
                zusatzlayer.setStyleSet(styleGeoJSON.fernbahnnetz);
                zusatzlayer.setLayerSet(fernbahnnetz);
                zusatzlayer.setParam();
            });
        }
        else if(e.name === "Hauptfließgewässer"){
            $.when(getZusatzlayer('hauptgewaesser')).done(function(_data){
                let json = JSON.parse(_data);
                gew_haupt.addData(json);
                gew_haupt.setStyle(styleGeoJSON.gewaesser);
                zusatzlayer.setStyleSet(styleGeoJSON.gewaesser);
                zusatzlayer.setLayerSet(gew_haupt);
                zusatzlayer.setParam();
            });
        }
        else if(e.name === "Ländergrenzen") {
            $.when(getZusatzlayer('vg250_bld_'+time+'_grob')).done(function(_data){
                let json = JSON.parse(_data);
                grenze_laender.addData(json);
                grenze_laender.setStyle(styleGeoJSON.laendergrenzen);
                zusatzlayer.setStyleSet(styleGeoJSON.laendergrenzen);
                zusatzlayer.setLayerSet(grenze_laender);
                zusatzlayer.setParam();
            });
        }
        else if(e.name === "Kreisgrenzen") {
            progressbar.init();
            progressbar.setHeaderText("Erstelle Layer");
            $.when(getZusatzlayer('vg250_krs_'+time+'_grob')).done(function(_data){
                let json = JSON.parse(_data);
                grenze_kreise.addData(json);
                grenze_kreise.setStyle(styleGeoJSON.kreigrenzen);
                zusatzlayer.setStyleSet(styleGeoJSON.kreigrenzen);
                zusatzlayer.setLayerSet(grenze_kreise);
                progressbar.remove();
                zusatzlayer.setParam();
                progressbar.remove();
            });
        }
        else if(e.name === "Gemeindegrenzen") {
            progressbar.init();
            progressbar.setHeaderText("Erstelle Layer");
            $.when(getZusatzlayer('vg250_gem_'+time+'_grob')).done(function(_data){
                let json = JSON.parse(_data);
                grenze_gemeinden.addData(json);
                grenze_gemeinden.setStyle(styleGeoJSON.gemeindegrenzen);
                zusatzlayer.setStyleSet(styleGeoJSON.gemeindegrenzen);
                zusatzlayer.setLayerSet(grenze_gemeinden);
                progressbar.remove();
                zusatzlayer.setParam();
            });
        }
    });

    map.on('overlayremove',function (e) {
        overlays.eachLayer(function(layer){
            if(layer.options.name === e.layer.options.name){
                    overlays.removeLayer(layer);
            }
        });
        setTimeout(function(){
            zusatzlayer.setParam();
        },1000)
    });

    map.on('baselayerchange', function (e) {
        $('.leaflet-control-attribution').find('a').each(function(){$(this).attr("target","_blank")});
        //track the choice
        var param_layer = baselayer.getParameter();
        var layer =e.layer.options.name;
        if(!param_layer){
            baselayer.setParamter(layer);
        }else{
            baselayer.updateParamter(layer);
        }
        if(e.name === "Flächenschema") {
            //flaechenschema_grau.setParams({layers:"Flächenschema_"+getTime()});
            //flaechenschema_farbe.setParams({layers:"Flächenschema_"+getTime()});
        }
        if (map.hasLayer(raster)) {
            raster_group.eachLayer(function (layer) {
               layer.bringToFront();
            });
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
const baselayer = {
    paramter:'baselayer',
    getParameter:function(){
      return urlparamter.getUrlParameter(this.paramter);
    },
    setParamter:function(_value){
      urlparamter.setUrlParameter(this.paramter,_value);
    },
    updateParamter:function(_value){
      urlparamter.updateURLParameter(this.paramter,_value);
    },
    init:function(){
        let parameter = this.getParameter();
        $.each(baseMaps,function(key,value){value.removeFrom(map)});
        if(parameter && parameter !=='noBackground'){
            $.each(baseMaps,function(key,value){
                console.log(value.options.name);
                let param = parameter.replace("_farbe","");
                if(param.indexOf(value.options.name)>=0){
                    value.addTo(map);
                }
            });
        }else{
            webatlas.addTo(map);
        }
    },
    set:function(choice){
        $.each(baseMaps,function(key,value){
            if(choice === value.options.name){
                value.addTo(map);
            }else{
                value.removeFrom(map);
            }
        });
    }
};
const zusatzlayer ={
    style:'',
    layer:'',
    parameter:'overlays',
    getParameter:function(){
        return urlparamter.getUrlParameter(this.parameter);
    },
    setParam:function(){
        let array = [],
            object = this;
        overlays.eachLayer(function (layer) {
            array.push(layer.options.name);
        });

        if(array.length ==0){
            object.removeParamter();
        }
        else if (!this.getParameter()) {
            urlparamter.setUrlParameter(this.parameter, array.toString());
        } else {
            urlparamter.updateURLParameter(this.parameter, array.toString());
        }
    },
    removeParamter:function(){
        urlparamter.removeUrlParameter(this.parameter);
    },
    setStyleSet:function(_style){
        this.style = _style;
    },
    getStyleSet:function(){
        return this.style;
    },
    setLayerSet:function(_layer){
        overlays.addLayer(_layer);
        this.layer= _layer;
    },
    getLayerSet:function(){
        return this.layer;
    },
    setForward:function(){
        if(this.layer) {
            try {
                this.layer.bringToFront();
            }catch(err){}
        }
    },
    init:function(){
        if(this.getParameter()){
            var array = this.getParameter().split(',');
            $.each(overlayMaps,function(key,value){
                $.each(array,function(key_a,value_a){
                    if(value_a === value.options.name){
                        map.addLayer(value);
                    }
                });
            });
        }
    }
};
