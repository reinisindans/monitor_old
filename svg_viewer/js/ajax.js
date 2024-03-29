const url_backend = "php";

/*
AJAX GETTER Functions------------------------------------------------------------
 */
function getSUMGeometriesInfo(raumgl,time,ags_array){
    return $.ajax({
        url: url_backend+"/map/count_ags.php",
        type:"GET",
        dataType:"json",
        data:{
            raumgl:raumgl,
            time:time,
            ags:ags_array + ""
        },
        success:function(){
            console.log(this.url);
        }
    })
}
function getHistogramm(){
    request_histogramm = $.ajax({
        url:url_backend+"/html/histogramm.php",
        type:"GET",
        data:{
            indikator:indikatorauswahl.getSelectedIndikator()
        },
        success:function(){
            console.log("Hole Histogramm: "+this.url);
        }
    });
    return request_histogramm;
}
function getIndZusatzinformationen(ind,time){
    let ind_set = indikatorauswahl.getSelectedIndikator();
    let time_set = zeitslider.getTimeSet();
    if(ind){
        ind_set = ind;
    }
    if(time){
        time_set= time;
    }
    let json = JSON.parse('{"ind":{"id":"'+ind_set+'","time":"'+time_set+'"},"format":{"id":"'+raeumlicheauswahl.getRaeumlicheGliederung()+'"},"query":"additional_info"}');
    return $.ajax({
        type: "GET",
        url: "php/getIndicatorValue.php",
        data: {
            values: JSON.stringify(json)
        },
    });
}
function getGeoJSON(ind,time,raumgliederung,ags_array){
    $request_geojson = $.ajax({
        url: url_backend+"/map/create_json.php",
        type: "GET",
        dataType: 'json',
        data: {
            'indikator': ind,
            'year': time,
            'raumgliederung': raumgliederung,
            'ags': ags_array + "",
            'caching':true
        },
        error: function (xhr, ajaxOptions, thrownError) {
            if (thrownError !== 'abort' && ind !=='Z00AG') {
                progressbar.remove();
                console.log("error create map:" + thrownError);
                alertError();
            }
        },success:function(data){
            console.log(this.url);
            progressbar.setHeaderText("vom Server empfangene Geometrien, werden visualisiert");
        }
    });
    return $request_geojson;
}
function getGeneratedClasses(indikator,time,raumgl,klassifizierung,klassenanzahl){
    request_classes = $.ajax({
        url: url_backend+"/map/klassenbildung.php",
        type: "GET",
        dataType: 'json',
        data: {
            'indikator':indikator,
            'KLASSIFIZIERUNG': klassifizierung,
            'KLASSENANZAHL': klassenanzahl,
            'hex_min':farbschema.getHexMin(),
            'hex_max': farbschema.getHexMax(),
            'raumgl':raumgl,
            'time':time
        },
        error: function (xhr, ajaxOptions, thrownError) {
            if (thrownError !== 'abort') {
                progressbar.remove();
                console.log("error create map:" + thrownError);
                alertError();
            }
        },
        success:function(){
            console.log(this.url);
        }
    });
    return request_classes;
}
function getRasterMap(time,ind,raumgliederung,klassifizierung,klassenanzahl,darstellung_map,_seite){
    return $.ajax({
        type: "GET",
        url: urlparamter.getURL_RASTER()+"php/map/create_raster.php",
        cache: false,
        data: {
            "Jahr": time,
            "Indikator": ind,
            "Raumgliederung": raumgliederung,
            "Klassifizierung": klassifizierung,
            "AnzKlassen": klassenanzahl,
            "Darstellung": darstellung_map,
            "hex_min": farbschema.getHexMin(),
            "hex_max": farbschema.getHexMax(),
            "seite": _seite
        }
    });
}
function getColorHTML(array, id) {
    return $.ajax({
        type: "GET",
        url: url_backend+'/colors/create_color_schema.php',
        dataType: "html",
        data: {
            colmax_rgb: array[0],
            colmin_rgb: array[1],
            anz_klassen: getKlassenanzahl(),
            id: id
        }
    });
}
function getIndicatorValueByMapAGS(json,ags_array){
    //json must look like //the input JSON z.B. {id:addedValue,time:time}
    let ags_set = getMapLayerArray(table.excludedAreas);
    //optional ags array must include ags object {ags:01}
    if(ags_array){
        ags_set = ags_array;
    }
    return $.ajax({
        url: url_backend+"/html/indicator_values.php",
        type: "POST",
        data:{
            values:json,
            ags_array_string: JSON.stringify(ags_set),
            grundakt_set: indikatorauswahl.getSelectedIndiktorGrundaktState()
        },
        error:function(data){
            alertError();

        }
    });
}
function getAvabilityIndicator(_ind){
    let ind = indikatorauswahl.getSelectedIndikator();
    if(_ind){ind=_ind;}
    let json = JSON.parse('{"ind":{"id":"'+ind+'"},"format":{"id":"'+raeumlicheauswahl.getRaeumlicheGliederung()+'"},"query":"avability"}');
    return $.ajax({
        type: "GET",
        url: "php/getIndicatorValue.php",
        data: {
            values: JSON.stringify(json)
        },
        success:function(data){
            //console.log(this.url);
        },
        error: function (xhr, ajaxOptions, thrownError) {
            console.log(this.url);
            progressbar.remove();
            console.log("error create create chart:" + thrownError);
            alertError();
        }
    });
}
/*
Getter Menu
 */
function getAllAvaliableIndicators(){
    return $.ajax({
        type: "GET",
        url: "php/html/create_menu.php",
        dataType: "json",
        data: {
            'modus': raeumlicheauswahl.getRaeumlicheGliederung()
        },
        error:function(data){
            console.log(data);
            alertError();
        }
    });
}
/*Get the time shifts for a single indicator with callback to retrieve the spatial possebilities with
final map creation*/
function getJahre(ind){
    let ind_set = indikatorauswahl.getSelectedIndikator();
    if(ind){
        ind_set = ind;
    }
    let json = JSON.parse('{"ind":{"id": "'+ind+'"},"format":{"id":"'+raeumlicheauswahl.getRaeumlicheGliederung()+'"},"query":"years"}');
    return  $.ajax({
        type: "GET",
        url: "php/getIndicatorValue.php",
        data: {
            values:JSON.stringify(json)
        },
        error: function (xhr, ajaxOptions, thrownError) {
            console.log(this.url);
            progressbar.remove();
            console.log("error create create chart:" + thrownError);
            alertError();
        },
        success:function(){
            /*console.log("+++++++++++++++++++++++++++++++++");
            console.log(this.url);*/
        }
    });
}
function getRaumgliederung(ind){
    let ind_set = indikatorauswahl.getSelectedIndikator();
    if(ind){
        ind_set = ind;
    }

    let request_Raumgliederung = $.ajax({
        url: "php/html/raumgliederung.php",
        type: "GET",
        data: {
            'indikator': ind_set,
            //Year must be set, fot final initializing
            'jahr':zeitslider.getTimeSet(),
            'modus':raeumlicheauswahl.getRaeumlicheGliederung()
        },
        error: function (xhr, ajaxOptions, thrownError) {
            progressbar.remove();
            console.log("error create Raumgliederung:"+thrownError);
            alertError();
        }
    });
    return request_Raumgliederung;
}
function getZusatzlayer(layer){
    return $.ajax({
        url: "php/holeZusatzLayer.php",
        type: "GET",
        dataType: 'json',
        data: {
            LAYER: layer
        },
        error: function (xhr, ajaxOptions, thrownError) {
            console.log(xhr.status);
            console.log(thrownError);
            alertError();
            progressbar.remove();
        }
    });
}
//dialog
function getStatistik(ags, name, wert){
    let raumgliederung_txt = getRaumgliederung_text();
    //set the value if Raumgl fein was set
    if(getRaumgliederungfeinID() != null){
        raumgliederung_txt = getRaumgliederungfein_text()
    }
    return $.ajax({
        url: "php/dialog/statistik.php",
        type: "POST",
        data: {
            'ags': ags,
            'map_array':getMapLayerArray(),
            'name': name,
            'wert': wert,
            'einheit':indikatorauswahl.getIndikatorEinheit(),
            'raumgliederung_name': raumgliederung_txt,
            'raeumliche_ausdehnung':getRaeumlicheAusdehnung_text()
        }
    });
}