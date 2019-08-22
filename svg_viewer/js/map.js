var geojson_grund,
    geoJsonGrund,
    jsongroup = new L.FeatureGroup(),
    jsongroup_grund = new L.FeatureGroup(),
    raster_group = new L.layerGroup(),
    //Add the dropdown functionality
    einheit,
    request_histogramm,
    request_classes,
    overlays,
    klassenJsonGrund,
    raster;

/*
INIT MAP Functions--------------------------------------------------------------
 */
var map = L.map('map',{
    zoomControl:false,
    twoFingerZoom:true,
});
//TODO siehe Aufzeichnungen !!
const indikatorJSON = {
    json_layer : '',
    json_file:'',
    setJSONLayer:function(_layer){
        this.json_layer = _layer;
    },
    getJSONLayer:function(){
      return this.json_layer;
    },
    setJSONFile:function(_json){
        this.json_file=_json;
    },
    getJSONFile:function(){
        return this.json_file;
    },
    init:function(raumgl, callback) {
        const object = this;
        let ind = indikatorauswahl.getSelectedIndikator(),
            klassifizierung = getKlassifizierung(),
            raumgliederung = getRaumgliederungID(),
            klassenanzahl = getKlassenanzahl(),
            time = zeitslider.getTimeSet(),
            ags_set = getAgsArray();

        $.when(setMapView())
            .then(progressbar.init())
            .then(cleanRasters())
            .then(indicatorJSONGroup.clean());

        if (raumgl) {
            raumgliederung = raumgl;
        }
        if (gebietsauswahl.countTags() === 0) {
            ags_set = [];
        }

        //info how much geomtries will be created and afterwards stat the creation
        $.when(getSUMGeometriesInfo(raumgliederung, time, ags_set)).done(function (x) {
            setTimeout(function () {
                progressbar.setHeaderText("Lade " + x + " Gebiete");
            }, 100)
        });

        let def = $.Deferred();

        function defCalls() {
            let requests = [
                getGeoJSON(ind, time, raumgliederung, ags_set),
                getGeneratedClasses(ind, time, raumgliederung, klassifizierung, klassenanzahl)
            ];
            $.when.apply($, requests).done(function () {
                def.resolve(arguments);
            });
            return def.promise();
        }

        defCalls().done(function (arr) {
            //now we have access to array of data
            object.json_file = JSON.parse(arr[0][0]);
            if (getArtDarstellung() === "auto") {
                klassengrenzen.setKlassen(arr[1][0]);
            }

            $.when(indikatorJSON.addToMap())
                .then(daten_akt())
                .then(table.create())
                .then(gebietsauswahl.init())
                .then(legende.fillContent())
                .then(farbschema.fill());

            if (callback) callback();
        });
        page_init = false;
    },
    addToMap:function(geoJson_set, klassenJson_set){
        const object = this;
        let geoJson = this.json_file;
        //optional parameter for undependant creation
        if(geoJson_set){
            geoJson = geoJson_set;
        }
        if(klassenJson_set){
            klassengrenzen.setKlassen(klassenJson_set);
        }

        //let einheit = geoJson.feature[0].properties.einheit;
        $.each(geoJson.features, function(key, value) {
            if(key == 0) {
                einheit = String(value.properties.einheit);
            }
        });

        function onEachFeature(feature, layer) {
            try {
                layer.on({
                    mouseover: object.highlightFeatureOnmouseover,
                    mouseout: object.resetHighlight,
                    click: object.setPopUp
                });
            }catch(err){
                console.log('%cError in map.js:46 '+err, 'background: #222; color: #bada55');
            }
        }

        object.json_layer = new L.GeoJSON(geoJson, {
            style: object.setStyle,
            onEachFeature: onEachFeature
        });

        jsongroup.addLayer(object.json_layer).addTo(map);

        if($('.right_content').is(":hidden")){
            progressbar.remove();
        }
    },
    setPopUp:function(e){
        let layer = e.target,
            gen = layer.feature.properties.gen.toString(),
            value_ags = layer.feature.properties.value_comma,
            einheit = layer.feature.properties.einheit,
            ags = layer.feature.properties.ags,
            grundakt = $('#'+ags).find('.td_akt').text(),
            val_d = DotToComma(value_ags),
            //fc = Fehlercode
            fc = layer.feature.properties.fc.toString(),
            div,
            id_popup = ags.toString().replace(".",""),
            gebietsprofil = '<div><img id="pop_up_gebietsprofil_'+id_popup+'" title="Gebietesprofil: Charakteristik dieser Raumeinheit mit Werteübersicht aller Indikatoren" src="images/icon/indikatoren.png"/><b>  Gebietsprofil</b></div>',
            statistik = '<div><img title="Indikatorwert der Gebietseinheit in Bezug auf statistische Kenngrößen der räumlichen Auswahl und des gewählten Indikators" id="pop_up_diagramm_ags_'+id_popup+'" src="images/icon/histogramm.png"/><b>  Statistik</b></div>',
            indikatorwertentwicklung = '<div><img id="pop_up_diagramm_ind_ags_'+id_popup+'" title="Veränderung der Indikatorwerte für die Gebietseinheit" src="images/icon/indikatoren_verlauf.png"/><b>  Indikatorwertentwicklung</b></div>',
            entwicklungsdiagramm = '<div><img id="pop_up_diagramm_entwicklung_ags_'+id_popup+'" title="Veränderung der Indikatorwerte für die Gebietseinheit" src="images/icon/indikatoren_diagr.png"/><b>  Entwicklungsvergleich</b></div>';

        if(viewState.getViewState() ==='responsive' || getMobileState()) {
            entwicklungsdiagramm = '';
            indikatorwertentwicklung = '';
        }

        if(fc !== '0'){
            //get the single values of each fc
            let arr = fc.split("||");
            let text = arr[2];
            let color = arr[1];
            div = $('<div class="PopUp">' +
                '<div>' +
                '<div><b style="color:red">'+text+'</b></div>' +
                '</div>')[0];
        }else if(!grundakt){
            div =  $('<div class="PopUp">' +
                '<div>' +
                '<b>'+gen+': '+'</b>'+val_d+' '+einheit+'' +
                '</div>' +
                '<hr class="hr"/> '+
                '<div id="pop_up_interactions">'+
                gebietsprofil+statistik+indikatorwertentwicklung+entwicklungsdiagramm+
                '</div>'+
                '</div>')[0];
        }
        else{
            div =  $('<div class="PopUp">' +
                '<div>' +
                '<b>'+gen+': '+'</b>'+val_d+' '+einheit+'' +
                '</div>' +
                '<div>Grundaktualität: '+grundakt+'</div>' +
                '<hr class="hr"/> '+
                '<div id="pop_up_interactions">'+
                gebietsprofil+statistik+indikatorwertentwicklung+entwicklungsdiagramm+
                '</div>'+
                '</div>')[0];
        }


        let bounds = layer.getBounds();
        let popup = L.popup()
            .setLatLng(bounds.getCenter())
            .setContent(div)
            .openOn(map);


        $(document).on('click','#pop_up_gebietsprofil_'+id_popup,function(){
            openGebietsprofil(ags,gen,indikatorauswahl.getSelectedIndikator());
        });

        $(document).on('click','#pop_up_diagramm_ags_'+id_popup,function(){
            openStatistik(ags,gen, value_ags,indikatorauswahl.getSelectedIndikator());
        });
        $(document).on('click','#pop_up_diagramm_entwicklung_ags_'+id_popup,function(){
            openEntwicklungsdiagramm(ags,gen,indikatorauswahl.getSelectedIndikator(),true);
        });
        $(document).on('click','#pop_up_diagramm_ind_ags_'+id_popup,function(){
            openEntwicklungsdiagramm(ags,gen,indikatorauswahl.getSelectedIndikator(),false);
        });
    },
    setMarker:function(lat,lng,title){
        if(!title){
            title = "<b>"+lat+" "+lon+"</b>"
        }
        let icon = L.icon({iconUrl:"images/icon/marker-icon.png",shadowUrl:"images/icon/marker-shadow.png"});
        let popup =L.popup().setLatLng([lat,lng]).setContent(title).openOn(map);
        map.setView(new L.LatLng(lat, lng),getZoom());
    },
    setStyle:function(feature) {
        //the error Code
        let fc = feature.properties.fc;
        let des = feature.properties.des;
        //init styling
        if (fc === '0') {
            return styleGeoJSON.getLayerStyle(feature.properties.value);
        } else {
            let arr = fc.split("||");
            let text = arr[2];
            error_code.setErrorCode(text);
            return styleGeoJSON.getErrorStyle();
        }
    },
    highlightFeatureOnmouseover:function(e) {
        const object = this;
        let layer = e.target;
        layer.setStyle(styleGeoJSON.getActive());
        if (!L.Browser.ie && !L.Browser.opera && !L.Browser.edge) {
            layer.bringToFront();
        }
        //highlight element in legend
        try {
            let fillcolor = layer.options.fillColor.replace('#', '');
            $('#legende_' + fillcolor + " i").css({
                "width": "20px",
                "height": "15px",
                "border": "2px solid " + farbschema.getColorActive()
            });
        }catch(err){}
    },
    resetHighlight: function(e) {
        let layer = e.target;
       layer.setStyle(styleGeoJSON.getLayerStyle(layer.feature.properties.value));
        $('#thead').show();
        $('#'+layer.feature.properties.ags).removeClass("hover");

        zusatzlayer.setForward();
        try {
            let fillcolor = layer.options.fillColor.replace('#', '');
            $('#legende_' + fillcolor + " i").css({"width": "15px", "height": "10px", "border": ""});
        }catch(err){
            //console.log(err);
        }
    },
    getStatistikArray:function(){
        let array = [];
        $.each(this.json_file.stat,function(key,value){
            let obj = {};
            obj[key] = value;
            array.push(obj);
        });
        return array;
    }
};

function initRaster(hex_min, hex_max, _seite, _settings,callback) {
    console.log("init Raster");
    // var darstellung_map = "RESAMPLE=NEAREST";
    let darstellung_map = getGlaettungModus();
    map.off('click',OnClickRaster);

    if(getGlaettungModus() == 1){
        darstellung_map = "RESAMPLE=BILINEAR";
        $('#rasterize').css('background-color','#8CB91B');
    }else{
        darstellung_map = "RESAMPLE=NEAREST";
        $('#rasterize').css('background-color','#4E60AA');
    }

    let ind = indikatorauswahl.getSelectedIndikator();
    let time = zeitslider.getTimeSet();
    let klassifizierung = getKlassifizierung();
    let klassenanzahl = getKlassenanzahl();
    let raumgliederung = getRaumgliederungID();
    //settings for split map request
    if(_seite === 'rechts'){
        ind = _settings[0].ind;
        time = _settings[0].time;
        raumgliederung = _settings[0].raumgl;
        klassifizierung = _settings[0].klassifizierung;
        klassenanzahl = _settings[0].klassenanzahl;
    }

    $.when(getRasterMap(time,ind,raumgliederung,klassifizierung,klassenanzahl,darstellung_map,_seite))
        .then(function(data){
            let txt = data;
            let x = txt.split('##');
            let pfad_mapfile = x[0];
            pfad_mapfile = pfad_mapfile.replace(/^( +)/g, '');
            let layername = x[2];
            let einheit = x[10];
            if(einheit==='proz'){einheit='%'}
            //store the gloabal Variables

            //set Raster for map
            let url = 'https://maps.ioer.de/cgi-bin/mapserv_dv?Map=';
            raster = new L.tileLayer.wms(url+pfad_mapfile,
                {
                    layers: layername,
                    cache: Math.random(),
                    version: '1.3.0',
                    format: 'image/png',
                    srs: "EPSG:3035",
                    transparent: true,
                    group:"ioer",
                    pfadmapfile : pfad_mapfile,
                    layername:layername,
                    einheit:einheit
                });

            if(_seite){
                //removeRasterBySide(_seite);
                cleanRasters(_seite);
                raster.setParams({id:_seite},false);
                getSideBySideControl().setRightLayers(raster.addTo(map));
            }else {
                raster.setParams({id: 'links'}, false);
                if (getSideBySideState()) {
                    cleanRasters('links');
                    getSideBySideControl().setLeftLayers(raster.addTo(map));
                } else {
                    cleanRasters();
                    indicatorJSONGroup.clean();
                    raster.addTo(map);
                }
                daten_akt(x[7]);
            }
            $.when(raster_group.addLayer(raster))
                .then(raster.bringToFront())
                .then(raster.setOpacity(getOpacity()))
                .then(map.on('click',OnClickRaster))
                .then(function(){if(!_seite){legende.fillContent()}});
            if(callback)callback();
        });
    //the on Click event in the map, shows a pop up with value and local community   statistic
}
//The on Click handler for the Raster Map
function OnClickRaster (e) {
    if(raeumlicheauswahl.getRaeumlicheGliederung()==="raster") {
        let mapOptions = getMapOptionsRaster();
        let indikator = indikatorauswahl.getSelectedIndikator();
        let X = map.layerPointToContainerPoint(e.layerPoint).x;
        let Y = map.layerPointToContainerPoint(e.layerPoint).y;
        let BBOX = map.getBounds().toBBoxString();
        let SRS = 'EPSG:4326';
        let WIDTH;
        let HEIGHT = map.getSize().y;
        let lat = e.latlng.lat;
        let lng = e.latlng.lng;

        let windowWidth = $(window).width();

        if (windowWidth > 2045) {
            WIDTH = 2045;
        } else {
            WIDTH = map.getSize().x;
        }

        let devider = $(".leaflet-sbs-divider").offset();

        //the requests
        if(devider) {
            if (X > devider.left) {
                console.log("layer right is clicked");
                indikator = $('#indicator_ddm_vergleich').dropdown('get value');
                mapOptions = getMapOptionsRaster('rechts');
            }else{
                mapOptions = getMapOptionsRaster('links');
            }
        }
        console.log(indikator+"||"+mapOptions[0].pfadmapfile+" || "+mapOptions[0].layername);

        let URL = 'https://maps.ioer.de/cgi-bin/mapserv_dv?Map=' +
            mapOptions[0].pfadmapfile + '&SERVICE=WMS&VERSION=1.1.1&REQUEST=GetFeatureInfo&BBOX=' +
            BBOX + '&SRS=' +
            SRS + '&WIDTH=' + WIDTH + '&HEIGHT=' + HEIGHT + '&LAYERS=' + mapOptions[0].layername +
            '&STYLES=&FORMAT=image/png&TRANSPARENT=true&QUERY_LAYERS=' +
            mapOptions[0].layername + '&INFO_FORMAT=html&X=' + X + '&Y=' + Y;

        let URL_WFS = 'https://sg.geodatenzentrum.de/wfs_vg250?SERVICE=WFS&VERSION=1.1.0&REQUEST=GetFeature&TYPENAME=vg250_gem&BBOX=' +
            lng + ',' + lat + ',' + (lng + 0.000000000000100) + ',' + (lat + 0.000000000000100) +
            '&srsName=' + SRS + '&MAXFEATURES=1';

        let getPixelValue = $.ajax({
            url: URL,
            cache: false,
            datatype: "html",
            type: "GET"
        });
        getPixelValue.done(function (data) {
            let html_value = $(data).text();
            let html_float = parseFloat(html_value);
            let pixel_value = null;
            //get the Ags from the BKG WFS
            let getGem = $.ajax({
                url: URL_WFS,
                cache: false,
                dataType: 'xml',
                type: "GET"
            });
            getGem.done(function (xml) {
                let gem = $(xml).find('vg250\\:gen,gen').text();
                let ags = $(xml).find('vg250\\:ags,ags').text();
                //query the gem statistic
                let getGemStat = $.ajax({
                    type: "GET",
                    url: urlparamter.getURL_RASTER() + "php/onClickQuery.php",
                    dataType: 'json',
                    data: {ags: ags, indikator: indikator, jahr: zeitslider.getTimeSet()}
                });
                getGemStat.done(function (json) {
                    let data = JSON.parse(json);
                    let layer = new L.GeoJSON(data)
                        .setStyle({
                            weight: 2,
                            opacity: 1,
                            color: 'black',
                            fillOpacity: 0
                        });
                    let gem_stat = data.features[0].properties.value;

                    if (html_float === -9998) {
                        pixel_value = "nicht Relevant"
                    }
                    else if (html_float < 0) {
                        pixel_value = " keine Daten"
                    }
                    else {
                        pixel_value = (Math.round(html_float * 100) / 100).toFixed(2) + ' ' + mapOptions[0].einheit;
                    }

                    let popup = new L.popup({
                        maxWith: 300
                    });

                    popup.setContent('<b>Pixelwert: </b>' + pixel_value + '</br>' +
                        '<span>Gemeinde: </span>' + gem + '</br>' +
                        '<span>Gemeindewert: </span>' + gem_stat);
                    popup.setLatLng(e.latlng);

                    if (!$('.map').hasClass('devider_move')) {
                        layer.addTo(map).bringToFront();
                        map.openPopup(popup);
                    }

                    map.on('popupclose', function (e) {
                        map.removeLayer(layer);
                    });


                });
            });
        });
    }
}
function daten_akt(){
    disableElement('#datenalter','Nicht verfügbar');
    if(indikatorauswahl.getSelectedIndiktorGrundaktState()) {
        if (raeumlicheauswahl.getRaeumlicheGliederung() === 'gebiete' && getRaumgliederungfeinID() !== 'gem' && getRaumgliederungID()!=='gem' && zeitslider.getTimeSet() > 2000) {
            enableElement('#datenalter', 'Zeige die Karte des Datenalters an.');
            let def = $.Deferred();

            function defCalls() {
                let requests = [
                    getGeoJSON('Z00AG', zeitslider.getTimeSet(), getRaumgliederungID(), getAgsArray()),
                    getGeneratedClasses('Z00AG', zeitslider.getTimeSet(),getRaumgliederungID(),getKlassifizierung(), getKlassenanzahl())
                ];
                $.when.apply($, requests).done(function () {
                    def.resolve(arguments);
                });
                return def.promise();
            }

            defCalls().done(function (arr) {
                let geoJson_grund = JSON.parse(arr[0][0]);
                let klassen_json_grund = arr[1][0];
                //no grunakt avaliable
                if (typeof geoJson_grund["error"] !== 'undefined') {
                    $('#datenalter_container').hide();
                }
                //avaliable -> create the map isnide the legend
                else {
                    $('#datenalter_container').show();
                    klassenJsonGrund = klassen_json_grund;
                    geoJsonGrund = geoJson_grund;
                    createMap_grund(geoJson_grund, klassen_json_grund);
                }
            });
        } else if(raeumlicheauswahl.getRaeumlicheGliederung()==='raster'){
            enableElement('#datenalter', 'Zeige die Karte des Datenalters an.');
            $.ajax({
                type: "GET",
                url: urlparamter.getURL_RASTER() + 'php/datenalter.php',
                data: {
                    Jahr: zeitslider.getTimeSet(),
                    Kategorie: indikatorauswahl.getSelectedIndikatorKategorie(),
                    Indikator: indikatorauswahl.getSelectedIndikator(),
                    Raumgliederung: getRaumgliederungID()
                },
                success: function (data) {
                    let txt_datenakt = data;
                    let x_datenakt = txt_datenakt.split('##');
                    let datenalter_mapfile = x_datenakt[0].replace(/^( +)/g, '');
                    let datenalter_legende = x_datenakt[1];
                    let datenalter_layer = x_datenakt[2];

                    $('#datenalter_container').show();
                    $('#grundaktmap').empty();

                    grundaktlayer = new L.tileLayer.wms('https://maps.ioer.de/cgi-bin/mapserv_dv?Map=' + datenalter_mapfile,
                        {
                            layers: datenalter_layer,
                            cache: Math.random(),
                            version: '1.3.0',
                            format: 'image/png',
                            transparent: true,
                            id: "ioer"
                        });
                    let rect1 = {color: "#8CB91B", weight: 3, fillOpacity: 0};
                    let miniMapDiv = new L.Control.MiniMap(grundaktlayer, {
                        toggleDisplay: true,
                        zoomLevelOffset: -3,
                        aimingRectOptions: rect1
                    }).addTo(map);
                    let grundaktmap = $("#grundaktmap");
                    grundaktmap.append(miniMapDiv.getContainer());
                    grundaktmap.find('.leaflet-control-minimap-toggle-display').remove();
                    $('#grundakt_legende').empty().load(datenalter_legende, function () {
                        let elements = $(this).find('img');
                        elements.each(function (key, value) {
                            let src = $(this).attr('src');
                            $(this).attr('src', "https://maps.ioer.de" + src);
                        });
                    });

                    grundaktmap.hover(function () {
                        $('#hover_info_grundaktmap').show();
                    }, function () {
                        setTimeout(function () {
                            $('#hover_info_grundaktmap').hide();
                        }, 2000);
                    });

                    let click = 0;
                    $('.grundaktmap_click').click(function () {
                        let grundaktlayer_set = new L.tileLayer.wms('https://maps.ioer.de/cgi-bin/mapserv_dv?Map=' + datenalter_mapfile,
                            {
                                layers: datenalter_layer,
                                cache: Math.random(),
                                version: '1.3.0',
                                format: 'image/png',
                                transparent: true,
                                id: "ioer"
                            });
                        if (raeumlicheauswahl.getRaeumlicheGliederung() === 'raster') {
                            if (click == 0) {
                                cleanRasters();
                                grundaktlayer_set.addTo(map);
                                grundaktlayer_set.bringToFront();
                                grundaktlayer_set.setOpacity(getOpacity());
                                click++;
                            } else {
                                cleanRasters();
                                map.removeLayer(grundaktlayer_set);
                                raster.addTo(map);
                                click = 0;
                            }
                        }
                    });
                }
            });
        }
    }
}
function createMap_grund(geoJson,klassenJson){
    let grades = [];
    let einheit;
    let grundakt_map = $('#grundaktmap');
    //let einheit = geoJson.feature[0].properties.einheit;
    $.each(geoJson.features, function(key, value) {
        if(key == 0) {
            einheit = String(value.properties.einheit);
        }
    });

    function getColor(d) {
        for (let i = 0; i < klassenJson.length; i++) {
            let obj = klassenJson[i];
            let obergrenze = obj.Wert_Obergrenze - 1000000000;
            let untergrenze = obj.Wert_Untergrenze - 1000000000;
            if (d.value <= obergrenze && d.value >= untergrenze) {
                return '#' + obj.Farbwert;
            }
        }
    }

    function style(feature) {
        let style_geojson = {
            fillColor: getColor(feature.properties),
            weight: 0.1,
            opacity: 1,
            fillOpacity: 1,
            color: 'black'
        };
        return style_geojson;
    }

    geojson_grund = new L.GeoJSON(geoJson, {
        style: style
    });

    jsongroup_grund.removeFrom(map);
    jsongroup_grund.addLayer(geojson_grund);

    $.each(klassenJson,function (key,value) {
        let minus_max = value.Wert_Obergrenze-1000000000;
        let minus_min = value.Wert_Untergrenze-1000000000;
        let round_max = (Math.round(minus_max * 100) / 100).toFixed(2);
        let round_min = (Math.round(minus_min * 100) / 100).toFixed(2);
        grades.push({
            "max":round_max,
            "min": round_min,
            "farbe": '#'+value.Farbwert
        });
    });

    $('#grundakt_titel').text('Datenalter gegenüber '+zeitslider.getTimeSet()+' (Jahren)');

    grades.reverse();
    let last = grades[grades.length-1];

    $('#grundakt_legende').empty();

    $.each(grades,function (key,value) {
        $('#grundakt_legende').append('<div id="legende_grund_line"><i style="background:'+ value.farbe +'"></i>'+'('+key+') '+parseInt(value.max,10) + '</div>');
    });

    $('#grundakt_leg').attr('src', '');

    //Quelle: https://github.com/Norkart/Leaflet-MiniMap
    //create the Minimap with the grundakt map inside
    let rect1 = {color: "#8CB91B", weight: 3,fillOpacity:0};
    let miniMapDiv = new L.Control.MiniMap(jsongroup_grund, {
        toggleDisplay: true,
        aimingRectOptions: rect1,
        zoomLevelOffset: -3
    }).addTo(map);

    grundakt_map
        .empty()
        .append(miniMapDiv.getContainer())
        .find('.leaflet-control-minimap-toggle-display')
        .remove();

    //the hover function
    grundakt_map.hover(function(){
        $('#hover_info_grundaktmap').show();
    },function(){
        setTimeout(function(){
            $('#hover_info_grundaktmap').hide();
        },2000);
    });

    let click_grundakt_map = 0;
    $('.grundaktmap_click').click(function(){
        if(raeumlicheauswahl.getRaeumlicheGliederung()==='gebiete') {
            let style_mapLayer = jsongroup;
            if (click_grundakt_map== 0) {
                jsongroup.setStyle(styleGeoJSON.disable);
                jsongroup_grund.addTo(map);
                jsongroup_grund.setStyle({fillOpacity:getOpacity()});
                click_grundakt_map++;
            } else {
                jsongroup_grund.removeFrom(map);
                jsongroup.setStyle(styleGeoJSON.getStandard());
                click_grundakt_map = 0;
            }
        }
    });
}
function getMapLayerArray(exluded_areas){
    let ags_array = [];
    jsongroup.eachLayer(function (layer) {
        layer.eachLayer(function (layer) {
            //exclude by des
            let des = layer.feature.properties.spatial_class;
            let ags_feature = layer.feature.properties.ags;
            let name = layer.feature.properties.gen;
            let fc = layer.feature.properties.fc;
            let grundakt = layer.feature.properties.grundakt;
            let value = layer.feature.properties.value;
            let hc = layer.feature.properties.hc;
            let value_comma = layer.feature.properties.value_comma;
            if($.inArray(des,exluded_areas)===-1){
                ags_array.push({
                    ags: ags_feature,
                    gen: name,
                    fc: fc,
                    grundakt: grundakt,
                    value: value,
                    hc: hc,
                    value_comma: value_comma,
                    des: des
                });
            }
        });
    });
    return ags_array;
}
function cleanRasters(id){
    raster_group.eachLayer(function (layer) {
        if(id) {
            if (layer.wmsParams.id === id) {
                map.removeLayer(layer);
            }
        }else{
            map.removeLayer(layer);

        }
    });
}
function clearAGS_Array(){
    if(getAgsArray().length >0) {
        urlparamter.removeUrlParameter('ags_array');
    }
}
function getKlassenJson_grund(){
    let value = klassenJsonGrund;
    return value;
}
function getGeoJsonGrund(){
    let value = geoJsonGrund;
    return value;
}
function getAgsArray(){
    let parameter_ags = urlparamter.getUrlParameter('ags_array');
    let ags_array = '';
    if(typeof parameter_ags !=='undefined' || parameter_ags) {
        ags_array = uniqueArray(parameter_ags.split(','));
    }
    return ags_array;
}
function getRasterLayer(){
    return raster;
}
function getMapOptionsRaster(_seite){
    let options = [];
    let layer_set = {};
    raster_group.eachLayer(function (layer) {
        if (_seite) {
            if (layer.wmsParams.id === _seite) {
                layer_set = layer;
            }
        }
        else{
            layer_set = layer;
        }
    });
    options.push({
        pfadmapfile:layer_set.options.pfadmapfile,
        layername: layer_set.options.layername,
        einheit:layer_set.options.einheit
    });
    return options;
}
function getZoom(){
    return urlparamter.getUrlParameter('zoom');
}
const klassengrenzen = {
    klassen: {},
    setKlassen: function(_klassen){this.klassen=_klassen;},
    getKlassen: function(){return this.klassen},
    getMax:function(){
        return Math.max.apply(Math, this.klassen.map(function (o) {
            return o.Wert_Obergrenze - 1000000000;
        }));
    },
    getMin:function(){
        return Math.min.apply(Math, this.klassen.map(function (o) {
            return o.Wert_Untergrenze - 1000000000;}));
    },
    getColor:function(layer_value){
        let klassenJson = this.getKlassen(),
            obergrenze_max = this.getMax(),
            untergrenze_min = this.getMin();
        for (let i = 0; i < klassenJson.length; i++) {
            let obj = klassenJson[i];
            let max = klassenJson.length-1;
            let obergrenze = obj.Wert_Obergrenze - 1000000000;
            let untergrenze = obj.Wert_Untergrenze - 1000000000;

            let value_ind = (Math.round(layer_value * 100) / 100).toFixed(2);

            if (value_ind <= obergrenze && value_ind >= untergrenze) {
                return '#' + obj.Farbwert;
            }
            else if (value_ind < untergrenze_min > 0) {
                return '#' + obj.Farbwert;
            }
            else if (value_ind === 0) {
                return '#' + obj.Farbwert;
            }
            else if (value_ind > obergrenze_max) {
                return '#' + klassenJson[max].Farbwert;
            }
            else if (value_ind === obergrenze_max) {
                return '#' + obj.Farbwert;
            }
        }
    },
    toString:function(){
        return JSON.stringify(this.klassen);
    }
};
const indicatorJSONGroup = {
    highlight:function(ags, fit_bounds){
        try {
            jsongroup.eachLayer(function (layer) {
                layer.eachLayer(function (layer) {
                    let ags_feature = layer.feature.properties.ags;
                    if (ags === ags_feature) {
                        if (fit_bounds) {
                            let bounds = layer.getBounds();
                            map.fitBounds(bounds);
                        }
                        layer.setStyle(styleGeoJSON.getActive());
                        if (!L.Browser.ie && !L.Browser.opera && !L.Browser.edge) {
                            layer.bringToFront();
                        }
                        return false;
                    } else {
                        return false;
                    }
                });
            });
        }catch(err){}
    },
    resetHightlight:function(){
        try {
            jsongroup.eachLayer(function (layer) {
                layer.eachLayer(function (layer) {
                    layer.setStyle(styleGeoJSON.getLayerStyle(layer.feature.properties.value));
                    return false;
                });
            });
        }catch(err){}
    },
    fitBounds:function(){
        let bounds = jsongroup.getBounds();
        if (raeumlicheauswahl.getRaeumlicheGliederung() === 'raster') {
            map.setView(new L.LatLng(50.9307, 9.7558), 6.8);
        } else {
            try{
                map.fitBounds(bounds);
            }catch(e){console.log(e)}
        }
    },
    clean:function(){
        jsongroup.clearLayers();
        jsongroup_grund.clearLayers();
    }
};
const error_code = {
    error_code: false,
    error_color: '',
    setErrorCode:function(_error_code){this.error_code = _error_code;},
    getErrorCode:function(){return this.error_code;},
    setErrorColor:function(_error_color){this.error_color = _error_color;},
    getErrorColor:function(){return this.error_color;}
};
