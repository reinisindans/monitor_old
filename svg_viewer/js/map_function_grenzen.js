var map;
var osm;
var geojson,geojson_grund;
var geoJsonGrund;
var steps;
var jahre;
var jsongroup = new L.FeatureGroup();
var jsongroup_grund = new L.FeatureGroup();
//Add the dropdown functionality
var ags_array = [];
var legende;
var einheit;
var request_geojson;
var request_datenakt_geojson;
var style_geojson;
var overlays;
var time;
var klassenJsonGrund;
var klassen_Json;
var geo_Json;
var errorcode,error_color;

function holeKarteGeoJSON(raumgl, hex_min, hex_max) {

    setMapView();

    setProgressBar();
    setProgressHeader("erstelle Karte");

    //set the slider value
    $('#opacity_slider').slider("value",80);

    cleanJsonGroups();

    map._onResize();

    var ind = getIndikatorSelectVal();

    var klassifizierung = getKLassifizierung();
    var raumgliederung;

    var klassenanzahl = getKlassenanzahl();

    if(raumgl == null) {
        raumgliederung = getRaumgliederung_grenzen_val();
    }else{
        raumgliederung = raumgl;
    }

    time = getTime();

    console.log(time+"||"+ind+"||"+raumgliederung+"||"+klassifizierung+"||"+klassenanzahl+"||"+hex_min+"||"+"||"+hex_max);

    request_geojson = $.ajax({
        url: "php/create_json.php",
        type: "GET",
        data: {
            'indikator': ind,
            'year': time,
            'raumgliederung': raumgliederung,
            'ags': ags_array + ""
        },
        error: function (xhr, ajaxOptions, thrownError) {
            removeProgressBar();
            console.log("error create map:"+thrownError);
            alertError();
            clearAGS_Array();
        }
    });

   request_geojson.done(function (geo_json) {
       var geoJson = JSON.parse(geo_json);
        geo_Json = geoJson;
        $.ajax({
            url: "php/klassenbildung.php",
            type: "GET",
            data: {
                'KLASSIFIZIERUNG': klassifizierung,
                'KLASSENANZAHL': klassenanzahl,
                'hex_min':hex_min,
                'hex_max':hex_max
            },
            error: function (xhr, ajaxOptions, thrownError) {
                removeProgressBar();
                console.log("error create map:"+thrownError);
                alertError();
            },
            success: function (klassen_json) {
                var klassenJson = JSON.parse(klassen_json);
                klassen_Json = klassenJson;

                createMap(geoJson, klassenJson);

                //create the table
                create_table();
                dropdow_grob();
                //create Zusatz
                daten_akt(getRaumgliederung_grenzen_val());

                //Create the color schema
                create_color_schema();
                return false;
            }
        });
    });
}

function cleanJsonGroups(){
    jsongroup.clearLayers();
    jsongroup_grund.clearLayers();
}

function cleanGeoJson() {
    map.eachLayer(function(layer){
       if(layer instanceof L.LayerGroup){
           map.removeLayer(layer);
       }
    });
}

function createMap(geoJson,klassenJson) {

    errorcode = false;

    //var einheit = geoJson.feature[0].properties.einheit;
    $.each(geoJson.features, function(key, value) {
        if(key == 0) {
           einheit = String(value.properties.einheit);
        }
    });
    var obergrenze_max = Math.max.apply(Math, klassenJson.map(function (o) {
        return o.Wert_Obergrenze - 1000000000;
    }));
    var untergrenze_min = Math.min.apply(Math, klassenJson.map(function (o) {
        return o.Wert_Untergrenze - 1000000000;
    }));

    function getColor(d) {
        for (var i = 0; i < klassenJson.length; i++) {
            var obj = klassenJson[i];
            var max = klassenJson.length-1;
            var obergrenze = obj.Wert_Obergrenze - 1000000000;
            var untergrenze = obj.Wert_Untergrenze - 1000000000;
            if (d.value < obergrenze && d.value > untergrenze) {
                return '#' + obj.Farbwert;
            }
            //Behandlung von Sonderfällen
            else if (d.value > obergrenze_max && d.value < obj.Obergrenze) {
                return '#' + obj.Farbwert;
            }
            else if (d.value < untergrenze_min > 0) {
                return '#' + obj.Farbwert;
            }
            else if (d.value == 0) {
                return '#' + obj.Farbwert;
            }
            else if (d.value > obergrenze_max) {
                return '#' + klassenJson[max].Farbwert;
            }
            else if (d.value == obergrenze_max) {
                return '#' + obj.Farbwert;
            }
        }
    }

    function style(feature) {
        var fc = feature.properties.fc;
        if (fc === '0') {
            style_geojson = {
                fillColor: getColor(feature.properties),
                weight: 0.25,
                opacity: 1,
                color: 'black',
                fillOpacity: $('#opacity_slider').slider("option", "value") / 100
            };
            return style_geojson;
        } else {
            // Default Stripes
            var arr = fc.split("||");
            var text = arr[2];
            errorcode = text;
            var color = arr[1];
            error_color = color;
            var bigStripes = new L.StripePattern({
                patternContentUnits: 'objectBoundingBox',
                patternUnits: 'objectBoundingBox',
                weight: 0.1,
                spaceWeight: 0.1,
                height: 0.2,
                angle: 45,
                color: '#'+color
            });
            bigStripes.addTo(map);

            style_geojson = {
                fillPattern: bigStripes,
                weight: 0.25,
                opacity: 1,
                color: 'black',
                fillOpacity: $('#opacity_slider').slider("option", "value") / 100
            };
            return style_geojson;
        }
    }

    function highlightFeature_mouseover(e) {
        var layer = e.target;

        layer.setStyle({
            weight: 5,
            color: '#8CB91B',
            dashArray: ''
        });

        setPopUp(layer);

        if (!L.Browser.ie && !L.Browser.opera && !L.Browser.edge) {
            layer.bringToFront();
        }

    }

    function highlightFeature_click(e){
        var layer = e.target;
        var ags = layer.feature.properties.ags;

        if (!L.Browser.ie && !L.Browser.opera && !L.Browser.edge) {
            layer.bringToFront();
        }

        //zoom into table
        var ypos = '#' + ags;

        $(ypos).scrollintoview();
        $('#thead').hide();
        $('#tBody_value_table').find(ypos).addClass("hover");

    }

    function setPopUp(layer){
        var gen = layer.feature.properties.gen.toString();
        var value_ags = (Math.round(layer.feature.properties.value * 100) / 100).toFixed(2);
        var einheit = layer.feature.properties.einheit;
        var ags = layer.feature.properties.ags;
        var grundakt = $('#'+ags).find('.td_akt').text();
        var val_d = DotToComma(value_ags);
        //fc = Fehlercode
        var fc = layer.feature.properties.fc.toString();
        var div;
        var id_popup = ags.toString().replace(".","");
        if(fc !== '0'){
             //get the single values of each fc
            var arr = fc.split("||");
            var text = arr[2];
            var color = arr[1];
             div = $('<div class="PopUp">' +
                 '<div>' +
                 '<div><b style="color:#'+color+'">'+text+'</b></div>' +
                 '</div>')[0];
        }else if(!grundakt){
            var gebietsprofil = '<div><img id="pop_up_gebietsprofil_'+id_popup+'" title="Gebietesprofil: Charakteristik dieser Raumeinheit mit Werteübersicht aller Indikatoren" src="images/icon/indikatoren.png"/><b>  Gebietsprofil</b></div>';
            var statistik = '<div><img title="Indikatorwert der Gebietseinheit in Bezug auf statistische Kenngrößen der räumlichen Auswahl und des gewählten Indikators" id="pop_up_diagramm_ags_'+id_popup+'" src="images/icon/histogramm.png"/><b>  Statistik</b></div>';
            var entwicklungsdiagramm = '<div><img id="pop_up_diagramm_entwicklung_ags_'+id_popup+'" title="Veränderung der Indikatorwerte für die Gebietseinheit" src="images/icon/indikatoren_diagr.png"/><b>  Entwicklungsdiagramm</b></div>';
            if(getRaumgliederungfein_val() === 'gem'){
                entwicklungsdiagramm = '';
            }
            div =  $('<div class="PopUp">' +
                '<div>' +
                '<b>'+gen+': '+'</b>'+val_d+' '+einheit+'' +
                '</div>' +
                '<hr class="hr"/> '+
                '<div id="pop_up_interactions">'+
                gebietsprofil+statistik+entwicklungsdiagramm+
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
                    '<div><img id="pop_up_gebietsprofil_'+id_popup+'" title="Gebietesprofil: Charakteristik dieser Raumeinheit mit Werteübersicht aller Indikatoren" src="images/icon/indikatoren.png"/><b>  Gebietsprofil</b></div>'+
                    '<div><img title="Indikatorwert der Gebietseinheit in Bezug auf statistische Kenngrößen der räumlichen Auswahl und des gewählten Indikators" id="pop_up_diagramm_ags_'+id_popup+'" src="images/icon/histogramm.png"/><b>  Statistik</b></div>'+
                    '<div><img id="pop_up_diagramm_entwicklung_ags_'+id_popup+'" title="Veränderung der Indikatorwerte für die Gebietseinheit" src="images/icon/indikatoren_diagr.png"/><b>  Entwicklungsdiagramm</b></div>'+
                '</div>'+
                 '</div>')[0];
         }

        var popup = L.popup().setContent(div);
        layer.bindPopup(popup);

        $(document).on('click','#pop_up_gebietsprofil_'+id_popup,function(){
            openGebietsprofil(ags,gen,getIndikatorSelectVal());
        });

        $(document).on('click','#pop_up_diagramm_ags_'+id_popup,function(){
            openHistogramm(ags,gen, value_ags,getIndikatorSelectVal());
        });
        $(document).on('click','#pop_up_diagramm_entwicklung_ags_'+id_popup,function(){
            openEntwicklungsdiagramm(ags,gen,getIndikatorSelectVal());
        });
    }

    function resetHighlight(e) {
        geojson.resetStyle(e.target);
        var layer = e.target;
        $('#thead').show();
        $('#tBody_value_table').find('#'+layer.feature.properties.ags).removeClass("hover");

        bringOverlaysToFront();
    }

    function onEachFeature(feature, layer) {
        layer.on({
            mouseover: highlightFeature_mouseover,
            mouseout: resetHighlight,
            click: highlightFeature_click
        });
    }

    geojson = new L.GeoJSON(geoJson, {
        style: style,
        onEachFeature: onEachFeature
    });
    jsongroup.addLayer(geojson).addTo(map);

    createLegende();

    //TODO:set_Fail_color();
    return false;
}
function createLegende(){

    var grades = [];

    $.each(getKlassenJson(),function (key,value) {
        var minus_max = value.Wert_Obergrenze-1000000000;
        var minus_min = value.Wert_Untergrenze-1000000000;
        var round_max = (Math.round(minus_max * 100) / 100).toFixed(2);
        var round_min = (Math.round(minus_min * 100) / 100).toFixed(2);
        grades.push({
            "max":round_max,
            "min": round_min,
            "farbe": '#'+value.Farbwert
        });
    });

    grades.reverse();

    $('#legende_i').empty();

    $.each(grades,function (key,value) {
        $('#legende_i').append('<div id="legende_'+value.farbe+'" class="legende_line"><i style="background:' + value.farbe + '"></i> ' + DotToComma(value.min) + ' - ' + DotToComma(value.max) + '</div>');
    });
    if(errorcode != false){
        $('#legende_i').append('<div id="legende_error" class="legende_line"><i style="background: repeating-linear-gradient(45deg,#'+error_color+',white 5px, white 1px, white 1px);"></i>'+errorcode+'</div>');
    }
}
function daten_akt(){

    var ind = getIndikatorSelectVal();

    request_datenakt_geojson = $.ajax({
        type: "POST",
        url: 'php/datenalter/datenalter.php'
    });

    request_datenakt_geojson.done(function (data) {
        $.ajax({
            url: "php/datenalter/klassenbildung.php",
            type: "GET",
            success: function (klassen) {
                console.log(data.length);
                //not avaliable
                if(data.length ==3){
                    $('#datenalter_container').hide();
                }
                //avaliable -> create the map isnide the legend
                else {
                    $('#datenalter_container').show();
                    var geoJson_grund = JSON.parse(data);
                    var klassen_json_grund = JSON.parse(klassen);
                    klassenJsonGrund = klassen_json_grund;
                    geoJsonGrund = geoJson_grund;
                    createMap_grund(geoJson_grund, klassen_json_grund);
                }
            }
        });
    });


    /*------hole Zusatzinfos------------------------------------------------------------------------*/
     $.ajax({
            url: "php/zusatzinformationen.php",
            type: "GET",
            data: {
                'indikator': ind,
                'year': getTime()
            },
            error: function (xhr, ajaxOptions, thrownError) {
                console.log(xhr.status);
                console.log(thrownError);
            },
            success: function (data) {
                var txt = data;
                var x = txt.split('||');
                var zusatzinfo = x[0];
                var datengrundlage = x[1];
                var atkis = x[2];
                $('#indikator_info_text').text(zusatzinfo);
                $('#datengrundlage_content').text(datengrundlage+atkis);
                if(getEinheit().length >=1){
                    $('.legende_einheit').show();
                    $('#legende_einheit').text(' '+getEinheit());
                }else{
                    $('.legende_einheit').hide();
                }
            }
        });

    /*------Histogramm------------------------------------------------------------------------*/
    $.get("php/histogramm.php",function(data){
        var einteilung = $("#menu_klassifizierung").find("input[name=Klassifikationsmethode]:checked").val();
        if(einteilung == 'haeufigkeit'){
            $('#histogramm_klasseneinteilung').html('Gleiche Klassenbesetzung');
        }
        else{
            $('#histogramm_klasseneinteilung').html('Gleiche Klassenbreite');
        }
        $('#histogramm_pic').empty().append(data);
    });
}
function createMap_grund(geoJson,klassenJson){

    var grades = [];
    var einheit;
    //var einheit = geoJson.feature[0].properties.einheit;
    $.each(geoJson.features, function(key, value) {
        if(key == 0) {
            einheit = String(value.properties.einheit);
        }
    });

    function getColor(d) {
        for (var i = 0; i < klassenJson.length; i++) {
            var obj = klassenJson[i];
            var obergrenze = obj.Wert_Obergrenze - 1000000000;
            var untergrenze = obj.Wert_Untergrenze - 1000000000;
            if (d.value <= obergrenze && d.value >= untergrenze) {
                return '#' + obj.Farbwert;
            }
        }
    }

    function style(feature) {
        style_geojson = {
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

    jsongroup_grund.addLayer(geojson_grund);

    $.each(klassenJson,function (key,value) {
        var minus_max = value.Wert_Obergrenze-1000000000;
        var minus_min = value.Wert_Untergrenze-1000000000;
        var round_max = (Math.round(minus_max * 100) / 100).toFixed(2);
        var round_min = (Math.round(minus_min * 100) / 100).toFixed(2);
        grades.push({
            "max":round_max,
            "min": round_min,
            "farbe": '#'+value.Farbwert
        });
    });

    $('#grundakt_titel').text('Datenalter gegenüber '+time+' (Jahren)');

    grades.reverse();
    var last = grades[grades.length-1];

    $('#grundakt_legende').empty();

    $.each(grades,function (key,value) {
        $('#grundakt_legende').append('<div id="legende_grund_line"><i style="background:'+ value.farbe +'"></i>'+'('+key+') '+parseInt(value.max,10) + '</div>');
    });

    $('#grundaktmap').empty();

    $('#grundakt_leg').attr('src', '');

    //Quelle: https://github.com/Norkart/Leaflet-MiniMap
    var rect1 = {color: "#8CB91B", weight: 3,fillOpacity:0};
    var miniMapDiv = new L.Control.MiniMap(jsongroup_grund, {
        toggleDisplay: true,
        aimingRectOptions: rect1,
        zoomLevelOffset: -3
    }).addTo(map);

    $("#grundaktmap")
        .append(miniMapDiv.getContainer())
        .find('.leaflet-control-minimap-toggle-display').remove();
    $('#grundaktmap').hover(function(){
       $('#hover_info_grundaktmap').show();
    },function(){
        setTimeout(function(){
            $('#hover_info_grundaktmap').hide();
        },2000);
    });

    var click = 0;
    $('.grundaktmap_click').click(function(){
        if(click == 0){
            cleanGeoJson();
            jsongroup_grund.addTo(map);
            click++;
        }else{
            cleanGeoJson();
            jsongroup.addTo(map);
            click = 0;
        }
    });
}
function getMapLayerArray(){
    var ags_array = [];
    jsongroup.eachLayer(function (layer) {
        layer.eachLayer(function (layer) {
            var ags_feature = layer.feature.properties.ags;
            var name = layer.feature.properties.gen;
            var fc = layer.feature.properties.fc;
            ags_array.push({ags: ags_feature, gen: name, fc:fc});
        });
    });
    return ags_array;
}
function clearAGS_Array(){
    $('#dropdown_grenzen_container').dropdown('clear');
    ags_array = [];
}
function getKlassenJson_grund(){
    var value = klassenJsonGrund;
    return value;
}
function getGeoJsonGrund(){
    var value = geoJsonGrund;
    return value;
}
function getAgsArray(){
    var value = ags_array;
    return value;
}
function getKlassenJson(){
    var value = klassen_Json;
    return value;
}
function getGeoJson(){
    var value = geo_Json;
    return value;
}
function getJsonGruop(){
    return jsongroup;
}
function getJsonGroup_Grund(){
    return jsongroup_grund;
}
function getEinheit(){
    var value = einheit;
    return value;
}
function fitBounds(){
    var bounds = jsongroup.getBounds();
    if(getFirst_INIT_State()== true) {
        try {
            map.fitBounds(bounds);
            //reset the state
            setFirst_INIT_State(false);
        }catch(err){
            alertError();
        }
    }else if(getDD_Fein_state() == true){
        try {
            map.fitBounds(bounds);
            setDD_Fein_state(false);
        }catch(err){
            alertError();
        }
    }
}

