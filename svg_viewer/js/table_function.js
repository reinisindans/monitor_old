var map;
var jsongroup;
//
var steps;
//styles for JSON Table interaction
var highlight = {};
var reset = {};
var standard = {};
var request_tabel;
var expand_table_array = [];
var grundakt_state = true;

//table buttons
$(function(){
    //the rang
    $('#table_ags').bind("sortEnd",function () {
        setRang();
    });
    $(document).on('click','#thead',function () {
        setTimeout(function () {
            setRang();
        }, 1000);
    });

    //dropndown menu
    $("#btn_table").on('click', function (event) {
        //set the Panel Data
        fillPanel();
        $('#tabelle_erweitern').show("slow",function() {
            //close the panel
            $('#panel_close').click(function () {
                closeTabelleErweiternPanel();
            });
            var a = 0;
            $('#indicator_ddm_table').dropdown({
                onAdd: function (addedValue, addedText, $addedChoice) {
                        a = a+1;
                        expand_table_array.push({id:addedValue,text:addedText,time:getTime(),einheit:false, count: 50+a});
                        //disable other choice possibilities
                        $('#hinweis_time').hide();
                        $('#lineare_trend_expand_container').hide();
                        $('#expand_kenngroessen').hide();
                        //clear ddm kenngroessen bld ord brd
                        $('#kenngroessen_ddm_table').find('.label').each(function () {
                           if($(this).data('value')=== 'bld' || $(this).data('value')=== 'brd'){
                               $(this).remove();
                           }
                        });
                    $(this).blur();
                    },
                onLabelRemove: function (value) {
                    expand_table_array = removefromarray(expand_table_array,value);
                }
            });
            //the times to expand the table
            var i = 1;
            $('#zeitschnitt_ddm_table').dropdown({
                onAdd: function (addedValue, addedText, $addedChoice) {
                    i = i+1;
                    expand_table_array.push({id:getIndikatorSelectVal(),text:addedText,time:addedValue,einheit:getEinheit(),count: i+10});
                    $('#lineare_trend_expand_container').hide();
                    $(this).blur();
                },
                onLabelRemove: function (value) {
                    expand_table_array = removefromarray(expand_table_array,value);
                    if(countTagsDDM('#zeitschnitt_ddm_table')<= 1){
                        console.log(countTagsDDM('#zeitschnitt_ddm_table'));
                        $('#lineare_trend_expand_container').show();
                    }
                }
            });
            //kenngrößen-------------------------------------------------------
            //show and hide mittlere Grundakt
            if($('#datenalter_container').is(':visible')) {
                if (grundakt_state == true) {
                    $('#kenngroessen_ddm_table').dropdown('set selected', 'Z00AG');
                }
                $('#item_Z00AG').show();
            }else{
                $('#kenngroessen_ddm_table').dropdown('restore defaults');
                $('#item_Z00AG').hide();
            }
            var x = 0;
            $('#kenngroessen_ddm_table').dropdown({
                onAdd: function (addedValue, addedText, $addedChoice) {
                    x =x+1;
                    if(addedValue === 'brd'){
                        expand_table_array.push({id:addedValue,text:'Gesamte Bundesrepublik ('+getTime()+')',time:getTime(),einheit:false, count: x+2});
                    }
                    else if(addedValue === 'bld'){
                        expand_table_array.push({id:addedValue,text:'Übergeordnetes Bundesland ('+getTime()+')',time:getTime(),einheit:false,count: x+1});
                    }
                    else if(addedValue === 'Z00AG'){
                        grundakt_state = true;
                    }
                    else{
                        expand_table_array.push({id:addedValue,text:addedText,time:getTime(),einheit:false});
                    }
                    $(this).blur();
                },
                onLabelRemove: function (value) {
                    if(value === 'Z00AG'){
                        grundakt_state = false;
                    }else{
                        expand_table_array = removefromarray(expand_table_array,value);
                    }
                }
            });
            //trendfortschreitung
            var y = 0;
            $('#trend_ddm_table').dropdown({
                onAdd: function (addedValue, addedText, $addedChoice) {
                    y =y+1;
                    expand_table_array = [];
                    expand_table_array.push({id:getIndikatorSelectVal(),text:'Trendfortschreibung ('+addedValue+')',time:addedValue,einheit:getEinheit(),count:y+20});
                    $('.ddm_expand').hide();
                    $('#hinweis_time_expand_linear').show();
                    $(this).blur();
                },
                onLabelRemove: function (value) {
                    expand_table_array = removefromarray(expand_table_array,value);
                    if(countTagsDDM('#trend_ddm_table')<= 1){
                        $('.ddm_expand').show();
                        $('#hinweis_time_expand_linear').hide();
                    }
                }
            })
        });
    });
    //tabelle aktualisieren
    $('#btn_table_load_expand').click(function () {
        setProgressBar();
        closeTabelleErweiternPanel();
        create_table();
    });
    //clear all dropdowns - reset btn
    $('#btn_table_clear_expand').click(function(){
       expand_table_array = [];
        clearPanel();
        setProgressBar();
        create_table();
    });
    //export as csv
    $('#csv_export').click(function(e){
        setDownloadState(true);
        e.preventDefault();
        console.log("download Table as CSV");
        destroyStickyTableHeader();
        var table_header = [];
        //push all table header in array
        $('.th_head').each(function (key,values) {
            table_header.push($(this).text());
        });

          // Quelle:https://github.com/zachwick/TableCSVExport
        $('#table_ags').TableCSVExport({
            header: table_header,
            delivery: 'download',
            separator: ';',
            filename:getIndikatorSelectVal()+"_"+getRaumgliederung_grenzen_val()+"_"+getTime()+".csv"
        });
        setStickyTableHeader();
    });
    //print table as pdf
    $('#print_table').click(function(){
        setDownloadState(true);
        $('#table_ags').printThis({
            header: "<img src='"+getURL_SVG()+"images/kopf_v2_unterseiten.png'><br/><h4>Einbezogenes Gebiet: "+$('#header_raumgl').text()+"</h4><h4>Indikator: "+getIndikatorname_Lang()+" ("+getTime()+")</h4>",
            importStyle: true,
            printContainer: true,
            loadCSS: [getURL_SVG()+"css/print.css",getURL_SVG()+"css/style.css",getURL_SVG()+"lib/semanticUi/semantic.css"]
        });
    });
});

function create_table() {

    $('#table_ags').remove();

    if($('.right_content').is(":visible")){
        setProgressHeader("erstelle Tabelle");
    }else{
        removeProgressBar();
    }

    //generate the table body
    request_tabel = $.ajax({
        type: "POST",
        dataType: 'html',
        url: "php/tabelle/create_table.php",
        data: {
            AGS_ARRAY: JSON.stringify(getMapLayerArray()),
            EXPAND_ARRAY: JSON.stringify(getExpandArray()),
            IND_TEXT: getIndikatorname_Lang(),
            grundakt_set: grundakt_state,
            differencen: getDifferencenState()
        },
        error: function (xhr, ajaxOptions, thrownError) {
            removeProgressBar();
            console.log("error create table:"+thrownError);
            alertError();
        }
    });
    request_tabel.done(function (data) {
        try {
            $('#scrollable-area').append(data);
            $('#table_ags')
                .show()
                .tablesorter()
                .trigger("update");
            //Check if the table filling process is finished
            summarize();
            setStickyTableHeader();
            removeProgressBar();
            //set the multi view
            setTableMW();
        }catch (err){
            removeProgressBar();
            console.log("error create table:"+err);
            alertError();
        }
    });
}

function table_interaktion(){
    //indikatorenvergleich as table
    $(document).on('click','.indikatoren_gebietsprofil',function(){
        var ags = $(this).data('ags');
        var name = $(this).data('name');
        var ind = $(this).data('ind');
        openGebietsprofil(ags,name,ind);
    });
    //indikatorwert stat
    $(document).on('click','.indikatoren_diagramm_ags',function() {
        var ags = $(this).data('ags');
        var name = $(this).data('name');
        var ind = $(this).data('ind');
        var wert = $(this).data('wert');
        openHistogramm(ags,name,wert,ind);
    });
    //development chart
    $(document).on('click','.indikatoren_diagramm_ags',function() {
        var ags = $(this).data('ags');
        var name = $(this).data('name');
        var ind = $(this).data('ind');
        var wert = $(this).data('wert');
        openHistogramm(ags,name,wert,ind);
    });
    $(document).on('click','.ind_entwicklungsdiagr',function () {
        var ags = $(this).data('ags');
        var name = $(this).data('name');
        var ind = $(this).data('ind');
        openEntwicklungsdiagramm(ags,name,ind);
    });
    //Live Search in Table
    $('#search_input_table').on('keyup', function() {
        var value = $(this).val();

        var patt = new RegExp(value, "i");

        $('#table_ags').find('tr').each(function () {
            if (!($(this).find('td').text().search(patt) >= 0)) {
                $(this).hide();
                $('#thead tr').show();

            }
            if (($(this).find('td').text().search(patt) >= 0)) {
                $(this).show();
            }
        });
    });

    //Hover
    $(document).on("mouseenter", "#tBody_value_table", function() {
        $(this).delegate('tr', 'mouseover mouseleave', function (e) {
            if (e.type == 'mouseover') {
                $(this).addClass("hover");
                var ags = $(this).find('.td_ags').text();
                ags.trim();
                highlight_layer_hover(ags);
            }
            else {
                $(this).removeClass("hover");
                reset_highlight();
            }
        });
        $(this).delegate('tr', 'click', function (e) {
            if (e.type === 'click') {
                $(this).addClass("hover");
                var ags = $(this).find('.td_ags').text();
                ags.trim();
                highlight_layer_click(ags);
            }
            else {
                $(this).removeClass("hover");
                reset_highlight();
            }
        });
    });
}

$(document).ready(function () {
    table_interaktion();
});

function setstyle(){
    highlight = {
        weight: 5,
        color: '#8CB91B',
        dashArray: ''
    };
    standard = {
        weight: 0.25,
        color: 'black'
    };
}

//The Map hover part
function highlight_layer_hover(ags) {
    setstyle();
    if(map.hasLayer(getGeoJson())){
        getJsonGruop().eachLayer(function (layer) {
            layer.eachLayer(function (layer) {
                var ags_feature = layer.feature.properties.ags;
                if(ags === ags_feature){
                    layer.setStyle(highlight);
                    if (!L.Browser.ie && !L.Browser.opera && !L.Browser.edge) {
                        layer.bringToFront();
                    }
                    return false;
                }else{
                    return false;
                }
            });
        });
    }
}

function highlight_layer_click(ags) {
    setstyle();
    getJsonGruop().eachLayer(function (layer) {
        layer.eachLayer(function (layer) {
            var ags_feature = layer.feature.properties.ags;
            if(ags === ags_feature){
                var bounds = layer.getBounds();
                map.fitBounds(bounds);
                layer.setStyle(highlight);
                if (!L.Browser.ie && !L.Browser.opera && !L.Browser.edge) {
                    layer.bringToFront();
                }
                return false;
            }else{
                return false;
            }
        });
    });
}

function reset_highlight() {
    setstyle();
    getJsonGruop().eachLayer(function (layer) {
        layer.eachLayer(function (layer) {
            layer.setStyle(standard);
            return false;
        });
    });
}

function summarize() {
    setRang();
    $('#table_ags').bind("sortEnd",function () {
        setRang();
    });
    $('.sorter-false').removeClass("header");
}
function setRang(){
    console.log("set Rang");
    $('.count_ags_table').each(function(index){
        $(this).empty();
        $(this).html(index+1);
    });

    //trigger Update tablesorter
    $('.th_head').click(function () {
        if($(this).hasClass('gebietsname')|| $(this).hasClass('ags')){
            $('#tr_rang').text('lfd. Nr.');
        }else {
            $('#tr_rang').text('Rang');
        }
        $('#table_ags').trigger('sortReset');
    });
}

function closeTable(){
    $('.right_content').hide();
    $('#mapwrap').removeClass('splitter_panel');
    map._onResize();
    setPanner();
    setlegendeheight();

    if($('#legende').is(':hidden')){
        $('#legende_button').css("right","0px");
        $('#legende').css("display","none");
        $('#legende_close').css("right","30px");
    }else{
        $('#legende_button').css("right","250px");
        $('#legende').css({"right":"0px",'display':''});
        $('#legende_close').css("right","30px");
    }
}

function openTable(){
    $('#mapwrap').addClass('splitter_panel');
    $('.right_content').show();
    map._onResize();
    $('#panRight').hide();

    if($('#legende').is(':hidden')){
        $('#legende_button').css("right",$('#rightPane').width());
        $('#legende').css("display","none");
        $('#legende_close').css("right",$('#rightPane').width()+30);
    }else{
        $('#legende_button').css("right",$('#rightPane').width()+250);
        $('#legende').css({"right":$('#rightPane').width()+10,'display':''});
        $('#legende_close').css("right",$('#rightPane').width()+30);
    }
}
function setPanner(){
        $('.panner').show();
}

function fillPanel(){
    //DDM Indikatoren
    console.log("fill panel expand");
    cloneIndicatorMenu('kat_auswahl_table','link_kat_table','left');
    //if table was expand
    //DDM Time
    var jahreArray = getJahreArray();
    //check if only one time possibility
    if(jahreArray.length > 1) {
        $('#hinweis_time_expand_null').hide();
        $('#hinweis_time').show();
        $('.time_expand_time_table').remove();
        var min_time = Math.min.apply(Math, jahreArray);
        if (min_time == getTime()) {
            $('#zeitschnitt_ddm_table').hide();
        } else {
            $('#zeitschnitt_ddm_table').show();
            for (var i = 0; i < jahreArray.length; i++) {
                if (getTime() > jahreArray[i]) {
                    var div = '<div class="item time_expand_time_table" data-value="' + jahreArray[i] + '">' + jahreArray[i] + '</div>';
                    $('#zeit_auswahl_table').append(div);
                }
            }
        }
        //if not set the note
    }else{
        $('#zeitschnitt_ddm_table').hide();
        $('#hinweis_time').hide();
        $('#hinweis_time_expand_null').show();
    }
    //Kenngroesen
    var value = getRaumgliederung_grenzen_val();
    var div;
    if(value === 'bld'){
        div = ' <div class="item" data-value="brd" value="brd">Bundesrepublik</div>'
    }
    if(value === 'ror'){
        div = 'Bundesrepublik';
    }
    //check if the string contains a k == something with kreis
    if(value.indexOf("k") >= 0){
        div = '<div class="item" data-value="bld" value="bld">Bundesländer</div>'+
            '</br>'+
            '<div class="item" data-value="brd" value="brd">Bundesrepublik</div>';
    }
    else{
        div = '<div class="item" data-value="brd" value="brd">Bundesrepublik</div>';
    }
    $('#ue_raum_sum_content').empty().append(div);

    //intercept the special cases
    //AG
    if(getIndikatorSelectVal().indexOf("RG") >= 0){
        if(getKategorie_val() !== 'O') {
            $('#expand_abs').show();
        }
    }else{
        $('#expand_abs').hide();
    }
    //Relief
    if(getKategorie_val() === 'X'){
        $('#expand_kenngroessen').hide();
    }else{
        $('#expand_kenngroessen').show();
    }
    //EW not for sst
    if(value === 'stt'){
        $('#expand_b00ag').hide();
    }else{
        $('#expand_b00ag').show();
    }
    //linear trend
    $.get( "php/tabelle/check_lineare_trenfortschreibung.php", function( data ) {
        if(data === 'false'){
            $('#trend_ddm_table').hide();
            $('#trend_hinweis_expand').show();
        }else {
            $('#trend_ddm_table').show();
            $('#trend_hinweis_expand').hide();
        }
    });
}
function clearPanel(){
    $('.ddm_table').dropdown('clear');
    fillPanel();
    expand_table_array = [];
    $('#kenngroessen_ddm_table').dropdown('set selected','Z00AG');
    grundakt_state == true;
}
function closeTabelleErweiternPanel(){
    $('#tabelle_erweitern').hide("slow",function() {
        //hide finished
    });
}
function setStickyTableHeader(){
    $('#table_ags').stickyTableHeaders({
        fixedOffset: $('#thead'),
        scrollableArea: $('.scrollable-area')
    });
}
function destroyStickyTableHeader(){
    $('#table_ags').stickyTableHeaders('destroy');
}
function getExpandArray(){
    return expand_table_array;
}
function setTableMW(){
    var tableWidth = $(window).width()-540;
    var expandWidth = 0;
    if(getExpandArray().length>=1){
        var expandWidth = tableWidth-(getExpandArray().length*220);
        expandTablePage(expandWidth);
    }else{
        expandTablePage(tableWidth);
    }
}
function getDifferencenState(){
    return $('#differences').is(':checked');
}
