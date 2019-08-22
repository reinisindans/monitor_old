//var indikatorname_lang;
var osm;
var map;
var steps = [];
var jahre = [];
var mapLayer = [];
var mapLayer_grund = [];
var splitter;
var dd_fein_set;
var first_init = false;
var localStorageItem_raumgl = 'raumgl',localStorage_item_ind = 'indicator';

$(function() {
      //divider
    //QUELLLE: https://github.com/jcubic/jquery.splitter
    if(getWidth() <= 1280) {
        showResponsiveTable(width,height);
    }
    else if(getWidth() <= 1400){
        showTable();
        setTimeout(function() {
            toolbar_toggle();
            header_slide();
            closeTable();
            setlegendeheight();
        },1000);
    }
    else{
        showTable(getWidth(),getHeight());
    }

    //the menu
    $(".hh_sf").on('click', function (event) {
        event.preventDefault();
        var currentClass = $(this).next().prop('class');
        if (currentClass == "dropdown_menu expanded") {
            $(this).next().removeClass("expanded");
            $(this).next().addClass("collapsed");
            $(this).next().slideUp();
        } else {
            $(".expanded").slideUp().addClass("collapsed").removeClass("expanded");
            $(this).next().removeClass("collapsed");
            $(this).next().addClass("expanded");
            $(this).next().slideDown();
        }
    });

    $(".hh_sf_r").on('click', function (event) {
        event.preventDefault();
        var currentClass = $(this).next().prop('class');
        if (currentClass == "dropdown_menu expanded_r") {
            $(this).next().removeClass("expanded_r");
            $(this).next().addClass("collapsed_r");
            $(this).next().slideUp();

        } else {
            $(".expanded_r").slideUp().addClass("collapsed_r").removeClass("expanded_r");
            $(this).next().removeClass("collapsed_r");
            $(this).next().addClass("expanded_r");
            $(this).next().slideDown();

        }
    });

    $(".hh_dd").on('click', function (event) {
        event.preventDefault();

        var currentClass = $(this).next().prop('class');
        if (currentClass == "dropdown_menu expanded_d") {
            $(this).next().removeClass("expanded_d");
            $(this).next().addClass("collapsed_d");
            $(this).next().slideUp();

        } else {
            $(".expanded_d").slideUp().addClass("collapsed_d").removeClass("expanded_d");
            $(this).next().removeClass("collapsed_d");
            $(this).next().addClass("expanded_d");
            $(this).next().slideDown();

        }
    });

    $(".hh_dd_l").on('click', function (event) {
        event.preventDefault();

        var currentClass = $(this).next().prop('class');
        if (currentClass == "dropdown_menu expanded_l") {
            $(this).next().removeClass("expanded_l");
            $(this).next().addClass("collapsed_l");
            $(this).next().slideUp();

        } else {
            $(".expanded_l").slideUp().addClass("collapsed_l").removeClass("expanded_l");
            $(this).next().removeClass("collapsed_l");
            $(this).next().addClass("expanded_l");
            $(this).next().slideDown();

        }
    });

    //The dropdown_menu arrow's
    $('.hh_sf').click( function(){
        $(this).find('i').toggleClass('glyphicon-chevron-down').toggleClass('glyphicon-chevron-up');
    });

    $('.hh_sf_r').click( function(){
        $(this).find('i').toggleClass('glyphicon-chevron-down').toggleClass('glyphicon-chevron-up');
    });

    $('.hh_dd').click( function(){
        $(this).find('i').toggleClass('glyphicon-chevron-down').toggleClass('glyphicon-chevron-up');
    });

    $('.hh_dd_l').click( function(){
        $(this).find('i').toggleClass('glyphicon-chevron-down').toggleClass('glyphicon-chevron-up');
    });

    // the mapnavbar
    $('.toolbar .menu_m').on('click', function() {
        toolbar_toggle();
    });

});
/*
START THE CALLBACK HELL
 */
//Load the Map by an specific Indicator----------------------------------------------------
function  LoadMenuByIndicator(_ind_bez) {
    setFirst_INIT_State(true);
    $('#kat_auswahl').empty();
    $.ajax({
        type: "GET",
        url: "php/kategorie_query.php",
        dataType: "html",
        success: function (data) {
            //add the categories
            $('#kat_auswahl').append(data);
            //fill the categories with the given indicators
            $('.link_kat').each(function (key, val) {
                var kat = $(this).attr("value");
                $.ajax({
                    type: "GET",
                    url: "php/indikatoren_query.php",
                    dataType: "html",
                    data: {
                        'kategorie': kat
                    },
                    success: function (data) {
                        $('#submenu' + kat)
                            .empty()
                            .append(data);
                            //set the given indicator as selected
                        setUrlParameter('ind',_ind_bez);
                        $('#indicator_ddm').dropdown('refresh').dropdown('set selected', _ind_bez);
                    }
                });
            });
        }
    });
}
//the indicator dropdown menu-------------------------------------------------
$(function(){

    $('#menu_klassifizierung input').change(function () {
        var value = $(this).val();
        updateURLParameter('klassifizierung_grenzen',value);
        holeKarteGeoJSON();
    });

    $('#Klassenanzahl').change(function(){
        var value =$(this).val();
        var param = getUrlParameter('klassenanzahl');
        if(!param){
            setUrlParameter('klassenanzahl',value);
        }else{
            updateURLParameter('klassenanzahl',value);
        }
        $("#farbwahl_btn").empty().append('Bitte Wählen..<span class="caret"></span>');
        holeKarteGeoJSON();
    });

    $('#indicator_ddm').dropdown({
        onChange: function (value, text, $choice) {
            setJahre(value);
            $('#search_input_indikatoren').val('');
            clearChartArray();
            closeTabelleErweiternPanel();
            clearPanel();
            updateURLParameter('ind',value);
        }
    });

});
/*Get the time shifts for a single indicator with callback to retrieve the spatial possebilities with
final map creation*/
function setJahre(ind){
    var time_request =  $.ajax({
        type: "GET",
        url: "php/year_query.php",
        dataType: "html",
        data: {
            'indikator_rechts': ind
        },
        error: function (xhr, ajaxOptions, thrownError) {
            removeProgressBar();
            console.log("error create create chart:" + thrownError);
            alertError();
        }
    });
    time_request.done(function (data) {
        jahre = [];
        var txt = data;
        var x = txt.split('||');

        //remove empty strings
        var z = $.map( x, function(v){
            return v === "" ? null : v;
        });
        $.each(z, function (i,val) {
            var numb = parseInt(val);
            if(val <= 2016) {
                jahre.push(numb);
            }
        });

        function sortNumber(a,b) {
            return a - b;
        }

        jahre.sort(sortNumber);
        zeit_slider(jahre);
        Raumgliederung(ind);
    });
}
//Gebietskulisse------------------------------------------------------------------------
function Raumgliederung(ind) {
    var request_Raumgliederung = $.ajax({
        url: "php/raumgliederung.php",
        type: "GET",
        dataType: "html",
        data: {
            'indikator': ind,
            //Year must be set, fot final initializing
            'jahr':getTime()
        },
        error: function (xhr, ajaxOptions, thrownError) {
            removeProgressBar();
            console.log("error create Raumgliederung:"+thrownError);
            alertError();
        }
    });
    request_Raumgliederung.done(function(data){
        var raumgl = localStorage.getItem(localStorageItem_raumgl);
        $('#Raumgliederung').
        empty().
        append(data).
        find('option').
        each(function(key, value){
            var option_val = $(this).val();
            if(raumgl === option_val && $(this).is(":not(:disabled)")){
                $(this).prop("selected", true);
                return false;
            }
            else if(option_val === 'krs'&& $(this).is(":not(:disabled)")){
                 $(this).prop("selected", true);
                return false;
            }
        });

        $('#gem_raumgl').hide();

        if(dd_fein_set === false) {
            setMenuFein();
            holeKarteGeoJSON();
        }else{
            holeKarteGeoJSON(getRaumgliederungfein_val());
        }
        setHeader(getRaumgliederung_grenzen_text()+" in Deutschland");
    });

}
var changed = false;
$(function menu_change_RaeumlicheAusdehnung(){
    $('#Raumgliederung').change(function(){
        changed = true;
        closeTabelleErweiternPanel();
        clearAGS_Array();
        holeKarteGeoJSON();
        setHeader(getRaumgliederung_grenzen_text()+" in Deutschland");
        //remove the fine choice for spatial extent
        hideRaumgl_MenuFein();
        //save the user setted spatial extent
        localStorage.setItem(localStorageItem_raumgl,getRaumgliederung_grenzen_val());
        changed = false;
    });
});
//Gebietsauswahl-----------------------------------------------------------------------------------------------------
function dropdow_grob() {
    var geoJson = getGeoJson();
    //initialize only if nothing is set
    if (countTagsDDM($('#dropdown_grenzen_container')) == 0) {
        var raumgliederung = getRaumgliederung_grenzen_text();
        $('#grenzen_choice').text('Gebietsauswahl: ' + raumgliederung.replace("- nur", ""));

        var menu = $('#menu_grenzen_grob');
        menu.empty();
        var items = [];

        for (var i = 0; i < geoJson.features.length; i++) {
            var gen = geoJson.features[i].properties.gen;
            var ags = geoJson.features[i].properties.ags;
            //items.push('<div class="item item_ddm_grob" data-value="'+ags+'" data-sort="'+gen+'">'+gen+'</div>');
            items.push({
                gen: gen,
                ags: ags
            })
        }
        items.sort(function (a, b) {
            return a.gen.localeCompare(b.gen);
        });

        for (var j = 0; j < items.length; j++) {
            menu.append('<div class="item" data-value="' + items[j].ags + '" >' + items[j].gen + '</div>');
        }

        var value_names = [];

        $('#dropdown_grenzen_container')
            .dropdown({
                onAdd: function (addedValue, addedText, $addedChoice) {
                    //close after each choice
                    $('#dropdown_grenzen_container').dropdown('hide');
                    cleanGeoJson();
                    ags_array.push(addedValue);
                    mapLayer = [];
                    mapLayer_grund = [];
                    value_names.push(addedValue);
                    $.each(geoJson.features, function (key, value) {
                        var index = $.inArray(value.properties.ags, ags_array);
                        if (index != -1) {
                            mapLayer.push(value);
                        }
                    });
                    $.each(getGeoJsonGrund().features, function (key, value) {
                        var index = $.inArray(value.properties.ags, ags_array);
                        if (index != -1) {
                            mapLayer_grund.push(value);
                        }
                    });

                    if (getRaumgliederungfein_val() != null) {
                        holeKarteGeoJSON(getRaumgliederungfein_val());
                    } else {
                        createAGSJSON(mapLayer, mapLayer_grund);
                        create_table();
                        setMenuFein();
                    }
                    setHeader("Gebietsauswahl als " + getRaumgliederung_grenzen_text());
                },
                onLabelRemove: function (value) {
                    if (countTagsDDM('#dropdown_grenzen_container') > 1) {
                        removeAGSJSON(value);
                    }else{
                        console.log(changed);
                        if(changed == false) {
                            ags_array = [];
                            holeKarteGeoJSON();
                            hideRaumgl_MenuFein();
                        }
                    }
                    $("tr[id^='"+value+"']").remove();
                },
                onLabelSelect: function($selectedLabels){
                    var ags =$($selectedLabels).data("value");
                    getJsonGruop().eachLayer(function (layer) {
                        layer.eachLayer(function (layer) {
                            if(layer.feature.properties.ags == ags){
                                var bounds = layer.getBounds();
                                map.fitBounds(bounds);
                            }
                            return false;
                        });
                    });
                }
            });

        //function to visualize a single JSON
        function createAGSJSON(array, array_grund) {
            cleanJsonGroups()
            $.each(array, function (key, value) {
                createMap(value, getKlassenJson());
                var bounds = getJsonGruop().getBounds();
                map.fitBounds(bounds);
            });
            $.each(array_grund, function (key, value) {
                createMap_grund(value, getKlassenJson_grund());
            });
        }
    //function to remove a specific JSON by a given AGS
        function removeAGSJSON(value) {
            for (var i = 0; i < mapLayer.length; i++) {
                if (mapLayer[i].properties.ags == value) {
                    mapLayer.splice(i, 1);
                    break;
                }
            }
            for (var i = 0; i < mapLayer_grund.length; i++) {
                if (mapLayer_grund[i].properties.ags == value) {
                    mapLayer_grund.splice(i, 1);
                    break;
                }
            }
            for (var i = 0; i < ags_array.length; i++) {
                if (ags_array[i] == value) {
                    ags_array.splice(i, 1);
                    break;
                }
            }
            if(getRaumgliederungfein_val() != null) {
                holeKarteGeoJSON(getRaumgliederungfein_val());

            }else{
                createAGSJSON(mapLayer, mapLayer_grund);
            }
        }

        return false;
    }
}
//dropdown Menu Fein----------------------------------------------------------------------------------------------
$(function menu_change_Fein(){
    $('#Raumgliederung_Fein').change(function() {
        var valueSelected = this.value;
        if(valueSelected === 'null'){
            holeKarteGeoJSON();
            setMenuFein();
        }else {
            holeKarteGeoJSON(getRaumgliederungfein_val());
            dd_fein_set = true;
        }
    });
});
function hideRaumgl_MenuFein(){
    $('#user_choice').hide();
}
function showRaumgl_MenuFein(){
    $('#user_choice').show();
}
//set Menu Fein
function setMenuFein(){
    var length_raumgl = $('#Raumgliederung option:not(:disabled)').length;
    if(getRaumgliederung_grenzen_val() != 'ror' && length_raumgl >1) {
        showRaumgl_MenuFein();
        var removeItem_val = getRaumgliederung_grenzen_val();

        var values = [];

        $('#Raumgliederung').find('option').each(function (key, value) {
            if ($(this).is(":not(:disabled)")) {
                values.push({id: $(this).val(), name: $(this).text()});
            }
        });
        var result = [];

        function getKeyToRemove(r) {
            var key_s = null;
            $.each(values, function (key, value) {
                if (value.id === r) {
                    key_s = key;
                }
            });
            return key_s;
        }

        for (var i = getKeyToRemove(removeItem_val) + 1; i < values.length; i++) {
            result.push(values[i]);
        }
        $('#Raumgliederung_Fein').empty().append('<option style="color: lightgrey;" selected="true" value="empty" disabled="disabled">Bitte wählen!</option><option value="null">Auswahl zurücksetzen</option>');
        $.each(result, function (key, value) {
            $('#Raumgliederung_Fein').append('<option name="' + value.name + '" value="' + value.id + '">' + value.name + '</option>');
        });
    }
}

function toolbar_toggle(){
    $('.toolbar').toggleClass("toolbar_close",500);
    header_slide();

}

function closeToolbar(){
    $('.toolbar').addClass('toolbar_close',500);

}

function showTable(width,height){
    console.log("Show Table");
    toolbar_toggle();
    $('.panner').hide();
    //set the slider width
    $('.slider_zeit_container_grenzen').css("min-width",width*1/10);
    splitter = $('#mapwrap').height('100%').split({
        orientation: 'vertical',
        limit: 10,
        onDrag: function(event) {
            $('#legende').hide().css({"right":$('#rightPane').width()+10});
            $('#legende_button').css("right",$('#rightPane').width()).show();
            $('#legende_close').css("right",$('#rightPane').width()+30);
            destroyStickyTableHeader();
        },
        onDragEnd: function (event) {
            map._onResize();
            $(window).trigger('resize.stickyTableHeaders');
            if(checkOverlap($('.leaflet-control'),$('.indikator_header'))===true){
                console.log("Overlapping");
            }
            setStickyTableHeader();
            if($('#leftPane').width() <= 300){
                $('#indikator_header').hide();
                $('#slider_zeit_container').hide();
            }else{
                $('#indikator_header').show();
                $('#slider_zeit_container').show();
            }
        }
    });

    //set the splitter position
    if(width <= 1024){
        splitter.position(width/2);
        $('.indikator_header').css("right","20%");
        $('#legende').css({"right":$('#rightPane').width()+10,'display':''}).hide();
        $('#legende_close').css("right",$('#rightPane').width()+30);
        $('#legende_button').css("right",$('#rightPane').width()).show();
    }
    else{
        splitter.position(45+width/100+"%");
        $('#legende').css({"right":$('#rightPane').width()+10,'display':''});
        $('#legende_close').css("right",$('#rightPane').width()+30);
    }

    //disable divider
    $('#table_close').click(function(){
        closeTable();
    });

    //enable divider by buttom click
    $('#panRight').click(function () {
        openTable();
    });
}
function showResponsiveTable(width,height){
    toolbar_toggle();
    $('#table_ags').removeClass("collapsing");

    setPanner();
    $('.right_content').css("display","none");
    $('#panRight').click(function(){
        if($('#tBody_value_table').is(':hidden')) {
            $('.left_content').hide();
            $('.right_content').show();
            $('#table_close').hide();
            $('.scrollable-area').css("top","100px");
            removeLegende();
            click++;
        }else{
            showLegende();
            $('.left_content').show();
            $('.right_content').hide();
            setPanner();
            click=0;
        }
    });
    var scrollTimeout = null;
    var scrollendDelay = 500; // ms
    $(".scrollable-area").scroll(function(){
        if ( scrollTimeout === null ) {
            scrollbeginHandler();
        } else {
            clearTimeout( scrollTimeout );
        }
        scrollTimeout = setTimeout( scrollendHandler, scrollendDelay );
    });
    function scrollbeginHandler() {
        // this code executes on "scrollbegin"
        $('.panner').hide();
    }

    function scrollendHandler() {
        // this code executes on "scrollend"
        $('.panner').show();
        scrollTimeout = null;
    }
    $('#legende').css("right","0px");
    $('#legende_close').css("right","20px");

    $('#legende_close').click(function(){
        $('#legende').css({"display":"none"});
        $('#legende_button').css("right","0px").show();
    });

    $('#legende_button').click(function(){
        $('#legende').show();
        $('#legende_button').hide();
        $('#legende_close').css("right","10px");
    });
}
function expandTablePage(width){
    //only if the splitter is active
    if($('.right_content').is(':visible')) {
        console.log("expand Table page with: " + width + " px");
        splitter.position(width);
        $('.indikator_header').css("right", "20%");
        $('#legende').css({"right": $('#rightPane').width() + 10, 'display': null});
        $('#legende_close').css("right", $('#rightPane').width() + 30);
        $('#legende_button').css("right", $('#rightPane').width()).show();
        if ($('.toolbar').is(":visible")) {
            if($('#map').width()<=350) {
                closeToolbar();
            }
        }
        //only if possible
        if( $('#slider_zeit_container').is(":visible")) {
            if ($('#leftPane').width() <= 300) {
                $('#indikator_header').hide();
                $('#slider_zeit_container').hide();
            } else {
                $('#indikator_header').show();
                $('#slider_zeit_container').show();
            }
        }
        map._onResize();
        if(getExpandArray().length>=2){
            closeLegende();
        }
    }
}
function cloneIndicatorMenu(appendToId,newClassId,orientation){

    $('.'+newClassId).remove();

    $('.link_kat').each(function(){
        $(this)
            .clone()
            .appendTo('#'+appendToId)
            .removeClass('link_kat')
            .addClass(newClassId);
    });
    $('.'+newClassId).each(function() {
        var element = $(this);
        var kat = $(this).attr("value");
        var time = getTime();
        //add  the needed classes and change the id
        element
            .find('i')
            .addClass(orientation);
        element
            .find('.submenu')
            .addClass(orientation)
            .addClass('transition')
            .removeAttr("id")
            .attr('id', 'submenu'+kat+newClassId);
            $.ajax({
                type: "GET",
                url: "php/tabelle/indikatoren_expand_query.php",
                data: {
                    kategorie: kat,
                    year: time
                },
                success: function (data) {
                    if($.trim(data)) {
                        $('#submenu' + kat + newClassId).empty().append(data);
                    }else{
                        element.remove();
                    }
                    $('.'+getIndikatorSelectVal()+'_item_table').remove();
                }
            });
        });
}
function header_slide(){
    console.log("set header position");
    if(map.hasLayer(raster_rechts)){
        if (!$('.toolbar').hasClass('toolbar_close')) {
            $('#indikator_header_links').animate({"left": "20%"}, 500);
            $('#slider_zeit_container_links').animate({"left": "20%"}, 500);
        } else {
            $('#indikator_header_links').animate({"left": "500px"}, 500);
            $('#slider_zeit_container_links').animate({"left": "500px"}, 500);
        }
    }else{
        if (!$('.toolbar').hasClass('toolbar_close')) {
            $('#indikator_header').animate({"left": "20%"}, 500);
            $('#slider_zeit_container').animate({"left": "20%"}, 500);
        } else {
            $('#indikator_header').animate({"left": "500px"}, 500);
            $('#slider_zeit_container').animate({"left": "500px"}, 500);
        }
    }
}
function setKlassenanzahl(anzahl){
    $("#Klassenanzahl").val(anzahl);
}
function setHeader(val){
    var kat = getKategorie_val();
    var indikatorname_lang = getIndikatorname_Lang();
    //fill the menu
    if(kat == 'X'){
        $("#header").text(indikatorname_lang);
        $("#header_raumgl").text(val);
    }else {
        $("#header").text(indikatorname_lang +" "+'(' + getTime() + ")");
        $("#header_raumgl").text(val);
    }
}
function getJahreArray(){
    return jahre;
}
function setRaumgliederung(value){
    $('#Raumgliederung').val(value);
}
function setRaumgliederungFein(value){
    $('#Raumgliederung_Fein').val(value);
}
function getWidth(){
    return $(window).width();
}
function getHeight(){
    return $(window).height();
}
function getRaumgliederung_grenzen_val(){
    var raumgl = $('#Raumgliederung').val();
    return raumgl;
}
function getRaumgliederung_grenzen_text(){
    var raumgl = $('#Raumgliederung').find("option:selected").text().replace("- nur","").replace("-","");
    return raumgl;
}
function getRaumgliederungfein_val(){
    var raumgl = $('#Raumgliederung_Fein').val();
    return raumgl;
}
function getRaumgliederungfein_text(){
    var raumgl = $('#Raumgliederung_Fein').find("option:selected").text().replace("- nur","").replace("-","");
    return raumgl;
}
function getDD_Fein_state(){
    return dd_fein_set;
}
function setDD_Fein_state(state){
    dd_fein_set = state;
}
function setIndikator(ind){
    $('#indicator_ddm').dropdown('set selected',ind);
}
function getIndikatorSelectVal(){
    var indikator = $('#indicator_ddm').dropdown('get value');
    return indikator;
}
function getIndikatorname(){
    var name = $('#indicator_ddm').dropdown('get text');
    return name;
}
function getIndikatorname_Lang(){
    var value = $('#'+getIndikatorSelectVal()+"_item").attr("data-name");
    return value;
}
function getKLassifizierung(){
    var parameter = getUrlParameter('klassifizierung_grenzen');
    if(!parameter){
        setUrlParameter('klassifizierung_grenzen','haeufigkeit');
    }
    return getUrlParameter('klassifizierung_grenzen');
}
function getKlassenanzahl(){
    var parameter = getUrlParameter('klassenanzahl');
    if(!parameter){
        setUrlParameter('klassenanzahl',7);
    }
    return parseInt(getUrlParameter('klassenanzahl'));
}
function getKategorie_val(){
    var val = $('#'+getIndikatorSelectVal()+"_item").attr("data-kat");
    return val;
}
function setKlassifizierung(klassifizierung){

    var input = $("#menu_klassifizierung");

    input.find("input").val(klassifizierung);
}
function getFirst_INIT_State(){
    return first_init;
}
function  setFirst_INIT_State(state){
    first_init = state;
}
function getRaeumlicheAusdehnung_text(){
    var tags =  $("#dropdown_grenzen_container").find('a');
    if(tags.length !== 0){
        var elements = [];
        tags.each(function () {
            elements.push($(this).text());
        });
        return elements.join(",");
    }else{
        return 'Deutschland';
    }
}
function getTime(){
    return getUrlParameter('time');
}

function countTagsDDM(ele){
    ele = $(ele);
    var tags =  ele.find('a');
    return tags.length;
}
function getURL_SVG(){
    if(getTest_State()==1){
        return "https://monitor.ioer.de/TESTBED/";
    }else{
        return "https://monitor.ioer.de/";
    }
}
function getURL_RASTER(){
    if(getTest_State()==1){
        return "https://maps.ioer.de/monitor_raster/TESTBED/";
    }else{
        return "https://maps.ioer.de/monitor_raster/";
    }
}
function updateURLParameter(param, paramVal){

    var url = window.location.href;
    var string_setted = param+"="+getUrlParameter(param);
    var new_set = param+"="+paramVal;
    var new_url = url.toString().replace(string_setted,new_set);

    window.history.pushState(param, paramVal, new_url);

}
function getUrlParameter(key) {
    var sPageURL = window.location.search.substring(1);
    var sURLVariables = sPageURL.split('&');

    for (var i = 0; i < sURLVariables.length; i++)
    {
        var sParameterName = sURLVariables[i].split('=');
        if (sParameterName[0] == key)
        {
            return sParameterName[1];
        }
    }

}
function setUrlParameter(key, value)
{
    var url = window.location.href;

    //detect if not set
    if(getUrlParameter(key)){
        updateURLParameter(key,value);
    }else {
        //add the ? if not allready set
        if (url.toString().indexOf("?") <= 0) {
            url += "?";
        }
        window.history.pushState(key, value, url + key + "=" + value + "&");
    }
}
function getAllParameters(){
    var url = window.location.href;
    var url_string = url.toString();
    var param_arr = url_string.split('?');
    return param_arr[1];
}
function removeUrlParameter(key_rm){
    var url = window.location.href;
    var url_string = url.toString();
    var param_arr = url_string.split('?');
    var param_split = param_arr[1].split('&');
    var return_arary = [];
    $.each(param_split,function(key,value){
        if(value.indexOf(key_rm)<0){
            return_arary.push(value);
        }
    });
    window.location = getURL_SVG()+"index.html?"+return_arary.join("&");
}