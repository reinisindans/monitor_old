var splitter,
    page_init = true,
    splitter_width = null,
    changed = false,
    raeumliche_gliederung = 'gebiete',
    step;

$(function menu_interaction() {
    //The Map Reset
    $('#btn_reset').click(function () {
        let url = window.location.href.replace(window.location.search,'');
        window.open(url,"_self");
        return false;
    });

    //the color schema
    let click_farb = 0;
    $("#farbwahl_btn").click(function () {
        if(click_farb ==0) {
            $("#color_schema").show();
            click_farb++;
        }else{
            $('#color_schema').hide();
            click_farb = 0;
        }

    });

    //open the "kennblatt"
    $( ".kennblatt" ).click(function() {
        openKennblatt();
    });

    //open and close the dropdown's
    $(".hh_sf").click(function(event) {
        let ddm = $(this).find('i').data('ddm');
        let ddm_container = $('#'+ddm);

        if(ddm_container.hasClass('pinned')===false && !ddm_container.is(':visible')){
            ddm_container.slideDown();
        }else if(ddm_container.is(':visible')===true &&ddm_container.hasClass('pinned')===false){
            ddm_container.slideUp();
        }
        $('.dropdown_menu').each(function(){
            if($(this).is('#'+ddm)===false && $(this).hasClass('pinned')===false){
                $(this).slideUp();
            }
        });viewState.getViewState()
        //set the height og the overflow content inside the menu bar
        if(mainView.getHeight() <= 1000 && viewState.getViewState() ==='mw') {
            let height = $('.toolbar').height() - $('#no_overflow').height() - 60;
            $('#overflow_content').css("height",height+50);
        }
    });
    //pin the element in the menu and unpin
    $('.pin').click(function(event){
        let drop_menu = $(this).find('i').data('ddm');
        let icon =  $(this).find('i');
        if(icon.hasClass('arrow_pinned')){
            icon.removeClass('arrow_pinned');
            $('#'+drop_menu).removeClass('pinned');
        }else {
            icon.addClass('arrow_pinned');
            $('#' + drop_menu).addClass('pinned');
        }
    });

    // the mapnavbar
    $('.toolbar .menu_m').on('click', function() {
        toggleToolbar();
    });

    //klassifizierungsmenu
    $('#menu_klassifizierung').find('input').change(function () {
        let value = $(this).val();
        $.when(resetArtDarstellung())
            .then(urlparamter.updateURLParameter('klassifizierung',value))
            .then(function() {
                if(raeumlicheauswahl.getRaeumlicheGliederung()==='gebiete'){
                    if(typeof getRaumgliederungfeinID() !=='undefined'){
                        indikatorJSON.init(getRaumgliederungfeinID());
                    }else{
                        indikatorJSON.init();
                    }
                }else{
                    initRaster();
                }
            });
    });

    //change the number of classes
    $('#Klassenanzahl').change(function(){
        resetArtDarstellung();
        let value =$(this).val();
        let param = urlparamter.getUrlParameter('klassenanzahl');
        if(!param){
            urlparamter.setUrlParameter('klassenanzahl',value);
        }else{
            urlparamter.updateURLParameter('klassenanzahl',value);
        }
        if(raeumlicheauswahl.getRaeumlicheGliederung()==='gebiete'){
            if(typeof getRaumgliederungfeinID() !=='undefined'){
                indikatorJSON.init(getRaumgliederungfeinID());
            }else{
                indikatorJSON.init();
            }
        }else{
            initRaster();
        }
    });

    //art of the vizualization
    $('#menu_darstellung').find('input').change(function () {
        let value = $(this).val();
        urlparamter.updateURLParameter('darstellung',value);
        if(value =="auto"){
            if(raeumlicheauswahl.getRaeumlicheGliederung()==='gebiete'){
                indikatorJSON.init()
            }else{
                initRaster();
            }
        }
    });

    $('#Raumgliederung').change(function(){
        changed = true;
        let choice = $(this).val();
        //start the pipeline
        $.when($('#dropdown_grenzen_container').dropdown('clear'))
            .then(urlparamter.removeUrlParameter('raumgl_fein'))
            .then(urlparamter.removeUrlParameter('ags_array'))
                .then( function setMap(){
                //save the user setted spatial extent
                if(choice==="gem"){
                    alertServerlast(choice);
                }else {
                    $.when(urlparamter.updateURLParameter('raumgl',choice))
                        .then(setHeader(getRaumgliederung_text()+" in Deutschland"))
                        .then(indikatorJSON.init());
                }
            })
            .then(table_expand_panel.close())
            .then(clearAGS_Array())
            .then(setMenuFein())
            .then(gebietsauswahl.clear())
            //remove the fine choice for spatial extent
            .then(hideRaumgl_MenuFein());
        changed = false;
        //info if needed
    });

    $('#Raumgliederung_Fein').change(function() {
        let valueSelected = this.value;
        let url_parameter = urlparamter.getUrlParameter('raumgl_fein');
        console.log(valueSelected);
        if(valueSelected === 'null'){
            $.when(urlparamter.removeUrlParameter('raumgl_fein'))
                .then(indikatorJSON.init());
        }else {
            if(!url_parameter){
                urlparamter.setUrlParameter('raumgl_fein',valueSelected);
            }else {
                urlparamter.updateURLParameter('raumgl_fein', valueSelected);
            }
            indikatorJSON.init(valueSelected);
        }
        setHeader(getDropdownRaumgglGrobSelectionAsString()+" als "+$(this).find("option:selected").text());
    });

});
//Gebietskulisse------------------------------------------------------------------------
function initRaumgliederung(json_raumgl) {
    if(raeumlicheauswahl.getRaeumlicheGliederung()==='gebiete') {
        let raumgl_content = $('#Raumgliederung');
        let spatialRange = [];
        let raumgl_parameter = urlparamter.getUrlParameter('raumgl');
        if (!raumgl_parameter) {
            urlparamter.setUrlParameter('raumgl', 'bld');
        }
        raumgl_content.empty();
        $.each(json_raumgl,function(key,value){
            if(value.state==="enabled"){
                spatialRange.push(value.id);
            }
            let html = '<option data-state="'+value.state+'" id="'+value.id+'_raumgl" name="'+value.name+'" value="'+value.id+'" '+value.state+'>'+value.name+'</option>';
            $('#Raumgliederung').append(html);
        });

        //set the disable title
        raumgl_content.find('option').each(function(){if($(this).is(':disabled')){$(this).attr("title","Für den Indikator nicht verfügbar")}});

        //set the selected Option
        let inArray = $.inArray(getRaumgliederungID(), spatialRange);
        let selected = getRaumgliederungID();
        //check if it's possible to set the stored parameter
        if (inArray != -1) {
            selected = getRaumgliederungID();
        }
        //if not check if krs is possible as the main option
        else {
            selected = spatialRange[0];
            indicatorJSONGroup.clean();
            alertNotinSpatialRange($('#Raumgliederung option:selected').text(), selected);
            return false;
        }

        urlparamter.updateURLParameter('raumgl', selected);
        $('#' + selected + "_raumgl").prop("selected", true);

        //check if raumgl fein is set or not -> standrad map create
        let parameter_ags = urlparamter.getUrlParameter('ags_array');
        let selection_fein = getRaumgliederungfeinID();
        if (!getRaumgliederungfeinID() && !parameter_ags) {
            $.when(indikatorJSON.init())
                .then(setHeader(getRaumgliederung_text() + " in Deutschland"));
        } else {
            //check if parameter are set
            if (parameter_ags) {
                //set the ddm grob map
                indikatorJSON.init(getRaumgliederungID(),
                    function () {
                        //reacreate the ddm grob
                        let ags_set = parameter_ags.split(',');
                        page_init = true;
                        $('#dropdown_grenzen_container').dropdown('refresh').dropdown('set selected', ags_set);
                        if (!selection_fein) {
                            setHeader(getRaumgliederung_text() + " in Deutschland")
                        }
                        //selection fine is set -> create the map fine
                        else {
                               //check if the parameter is possible for the given indicator
                                if ($.inArray(selection_fein, spatialRange) !== -1) {
                                    $('#raumgl_fein' + selection_fein).prop("selected", true);
                                    setHeader(getDropdownRaumgglGrobSelectionAsString()+" als "+getRaumgliederungfein_text());
                                    indikatorJSON.init(selection_fein);
                                } else {
                                    indicatorJSONGroup.clean();
                                    alertNotinSpatialRange($('#Raumgliederung option:selected').text(), $('#Raumgliederung option:selected').val());
                                    if (spatialRange.length > 1) {
                                        showRaumgl_MenuFein();
                                    } else {
                                        hideRaumgl_MenuFein();
                                    }
                                }
                            }
                        }
                );
            }
        }
    }
    else{
        let steps_set = [];
        let slider = $("#raum_slider");
        $.each(json_raumgl, function (i,val) {
            steps_set.push(val);
        });
        steps = steps_set;
        //initializeFirstView the spatial extend slider
        $.when(initSpatialSlider(steps_set,slider))
            .then(initRaster(null,null,null,null,setHeader(getRaumgliederung_text())));
    }
}
function hideRaumgl_MenuFein(){
    $('#user_choice').hide();
}
function showRaumgl_MenuFein(){
    if(getRaumgliederungID()!=='ror' && getRaumgliederungID() !=='gem') {
        $.when(setMenuFein())
            .then($('#user_choice').show());
    }
}
//set Menu Fein
function setMenuFein(){
    if(raeumlicheauswahl.getRaeumlicheGliederung()==="gebiete") {
        console.log("setMenuFein()");
        $('#Raumgliederung_Fein').empty();

        let length_raumgl = $('#Raumgliederung option:not(:disabled)').length;
        if (getRaumgliederungID() !== 'ror' && getRaumgliederungID() !=='gem' && length_raumgl > 1) {
            let values_menu = [];
            let values = [];

            $('#Raumgliederung').find('option').each(function () {
                    values_menu.push({id: $(this).val(), name: $(this).text(),state:$(this).data('state')});
                    values.push($(this).val());
            });

            let value_set = getRaumgliederungID();
            let position = $.inArray(value_set, values);
            //show the menu only if the user has mor than 2 possebilities
            if (position != (values.length - 1) || values.length > 1) {
                $('#Raumgliederung_Fein').append('<option data-val="preset" style="color: lightgrey;" selected="true" value="empty" disabled="disabled">Bitte wählen!</option><option data-val="preset" value="null">Auswahl zurücksetzen</option>');
                $.each(values_menu, function (key, value) {
                    if (key > position) {
                        $('#Raumgliederung_Fein').append('<option data-val="preset" id="raumgl_fein' + value.id + '" name="' + value.name + '" value="' + value.id + '" '+value.state+'>' + value.name + '</option>');
                    }
                });
                //set the disable title
                $('#Raumgliederung_Fein').find('option').each(function(){if($(this).is(':disabled')){$(this).attr("title","Für den Indikator nicht verfügbar")}});
                if(typeof getRaumgliederungfeinID() !=='undefined'){
                    $('#raumgl_fein'+getRaumgliederungfeinID()).prop("selected",true);
                }
            } else {
                $('#Raumgliederung_Fein').append('<option data-val="preset" style="color: lightgrey;" selected="true" value="empty" disabled="disabled">keine Feingliederung verfügbar</option>');
            }
        } else {
            $('#Raumgliederung_Fein').append('<option data-val="preset" style="color: lightgrey;" selected="true" value="empty" disabled="disabled">keine Feingliederung verfügbar</option>');
        }
    }
}
function toggleToolbar(){
    $('.toolbar').toggleClass("toolbar_close",500);
    slideHeader();

}
function closeToolbar(){
    $('.toolbar').addClass("toolbar_close");
    slideHeader();
}
function cloneIndicatorMenu(appendToId,newClassId,orientation,exclude_kat,possible_indicators){

    $('.'+newClassId).remove();

    let target_ddm = $('.link_kat');

    if(target_ddm.length===0){
        target_ddm = $('.link_sub');
    }

    target_ddm.each(function(){
        $(this)
            .clone()
            .appendTo('#'+appendToId)
            .removeClass('link_kat')
            .addClass(newClassId);
    });
    $('.'+newClassId).
    each(function() {
        let element = $(this);
        let kat = $(this).attr("value");
        let time = zeitslider.getTimeSet();
        //add  the needed classes and change the id
        element
            .find('i')
            .addClass(orientation);
        element
            .find('.submenu')
            .addClass(orientation)
            .addClass('transition')
            .removeAttr("id")
            .attr('id', 'submenu'+kat+newClassId)
            .find('.item').each(function(){
                //if true clone only indicators which times are possible with the indicator set times
                if(possible_indicators){
                    let times_values = $(this).data("times").toString().split(',');
                    let kat_name = $(this).data("kat");
                    let time = zeitslider.getTimeSet().toString();
                    if($.inArray(time,times_values)===-1){
                        $(this).remove();
                    }
                }
            })
    });

    //remove empty kats
    $(' .'+newClassId).each(function(){
       if($(this).find('.item').length ==0){
           $(this).remove();
       }
    });

    //set the align css for the menu
    let text_align = 'left';
    if(orientation==='left'){
        text_align = 'right';
    }
    $('#'+appendToId+' >.item').css('text-align',text_align);

    //remove a excluded Kat
    if(exclude_kat){
        if(exclude_kat instanceof Array){
            $.each(exclude_kat,function(key,value){
                $('.' + newClassId + "[value=" + value + "]").remove();
            });
        }else {
            $('.' + newClassId + "[value=" + exclude_kat + "]").remove();
        }
    }
}
function slideHeader(){
    console.log("set header position");
    if(viewState.getViewState()==='mw') {
        if (!$('#toolbar').hasClass('toolbar_close')) {
            $('#indikator_header').animate({"left": "20%"}, 500);
            $('#slider_zeit_container').animate({"left": "20%"}, 500);
        } else {
            $('#indikator_header').animate({"left": "500px"}, 500);
            $('#slider_zeit_container').animate({"left": "500px"}, 500);
        }
    }
}
function setHeader(val){
    setTimeout(function(){
        showHeader();
        let kat = indikatorauswahl.getSelectedIndikatorKategorie(),
            indikatorname_lang = indikatorauswahl.getSelectedIndikatorText_Lang(),
            raumgl_text = val;

        if(!val){
            raumgl_text = getRaumgliederung_text()+" in Deutschland";
        }
        //fill the menu
        if(kat === 'X'){
            $("#header").text(indikatorname_lang);
            $("#header_raumgl").text(raumgl_text);
        }else {
            $("#header").text(indikatorname_lang +' (' + zeitslider.getTimeSet() + ")");
            $("#header_raumgl").text(raumgl_text);
        }
    },1000);
}
function hideHeader(){
    $('#indikator_header').hide();
}
function showHeader(){
    $('#indikator_header').show();
}
function setRaumgliederung(value){
    $('#Raumgliederung').val(value);
}
function setRaumgliederungFein(value){
    $('#raumgl_fein'+value).attr("selected",true);
}
function getRaumgliederungID(){
    if(raeumlicheauswahl.getRaeumlicheGliederung()==='gebiete') {
        return urlparamter.getUrlParameter('raumgl');
    }else{
        return steps[urlparamter.getUrlParameter('rasterweite')];
    }
}
function getRaumgliederung_text(){
    if(raeumlicheauswahl.getRaeumlicheGliederung()==='gebiete') {
        return $('#Raumgliederung').find("option:selected").text().replace("- nur", "").replace("-", "");
    }else{
        return getRaumgliederungID();
    }
}
function getRaumgliederungfeinID(){
    return urlparamter.getUrlParameter('raumgl_fein');
}
function getRaumgliederungfein_text(){
    return $('#Raumgliederung_Fein').find("option:selected").text().replace("- nur","").replace("-","");
}
function getArtDarstellung(){
    let parameter = urlparamter.getUrlParameter('darstellung');
    if(!parameter){
        urlparamter.setUrlParameter('darstellung','auto');
    }
    return urlparamter.getUrlParameter('darstellung');
}
//modus is optional
function getKlassifizierung(modus){
    let parameter = urlparamter.getUrlParameter('klassifizierung');
    if(raeumlicheauswahl.getRaeumlicheGliederung()==='gebiete' || modus==="gebiete") {
        if (!parameter) {
            urlparamter.setUrlParameter('klassifizierung', 'haeufigkeit');
        }
        return urlparamter.getUrlParameter('klassifizierung');
    }else{
        let values = ['gleicheAnzahl','gleicheBreite'];
        if(parameter === 'haeufigkeit'){
            return values[0];
        }else{
            return values[1];
        }
    }
}
function getKlassenanzahl(){
    let parameter = urlparamter.getUrlParameter('klassenanzahl');
    if(!parameter){
        urlparamter.setUrlParameter('klassenanzahl',7);
    }else{
        $('#klassi_'+parameter).prop("selected",true);
    }
    return parseInt(urlparamter.getUrlParameter('klassenanzahl'));
}
function getRaeumlicheAusdehnung_text(){
    let tags =  $("#dropdown_grenzen_container").find('a');
    if(tags.length !== 0){
        let elements = [];
        tags.each(function () {
            elements.push($(this).text());
        });
        return elements.join(",");
    }else{
        return 'Deutschland';
    }
}
function resetArtDarstellung(){
    urlparamter.updateURLParameter('darstellung','auto');
    $('#farbreihe_auto').prop('checked', true);
}
function getDropdownRaumgglGrobSelectionAsString(){
    let string = '';
    $('#dropdown_grenzen_container').find('a').each(function(key,value){
        string +=$(this).text()+",";
    });
    return string.slice(0, -1);
}

//Models--------------------------------------------------------------------
const indikatorauswahl ={
    possebilities:'',
    all_possible_years:'',
    filtered_years:'',
    paramter:'ind',
    previous_indikator:'',
    getPreviousIndikator:function(){
      return this.previous_indikator;
    },
    getSelectedIndikator:function(){
        return urlparamter.getUrlParameter(this.paramter);
    },
    getSelectedIndikatorKategorie:function(){
        return $('#'+this.getSelectedIndikator()+"_item").attr("data-kat");
    },
    setIndikatorParameter:function(_value){
        urlparamter.setUrlParameter(this.paramter, _value);
    },
    getIndikatorEinheit:function(){
        let value =this.getIndikatorInfo(this.getSelectedIndikator(),"unit");
        if(typeof value ==='undefined' || value===''){
            value = '';
        }
        return value;
    },
    getSelectedIndiktorGrundaktState:function(){
        let value = $('#'+this.getSelectedIndikator()+'_item').data('actuality');
        return value === 'verfügbar';
    },
    updateIndikatorParamter:function(_value){
        urlparamter.updateURLParameter(this.paramter, _value);
    },
    getAllPossibleYears:function(){
      return this.all_possible_years;
    },
    getFilteredPossibleYears:function(){
      return this.filtered_years;
    },
    getPossebilities:function(){
      return this.possebilities;
    },
    getDOMObject:function(){
        $elem = $('#indicator_ddm');
        return $elem;
    },
    init:function(){
        const menu = this;
        menu.fill();
        menu.getDOMObject()
            .dropdown('refresh')
            .dropdown({
                onChange: function (value, text, $choice) {
                    //clean the search field
                    $('#search_input_indikatoren').val('');
                    //save the prev selected indicator as paramter
                    menu.previous_indikator=value;
                    menu.setIndicator(value);
                    legende.init();
                    if (raeumlicheauswahl.getRaeumlicheGliederung() === 'gebiete') {
                        resetArtDarstellung();
                        clearChartArray();
                        table_expand_panel.close();
                    }
                },
                onHide: function () {
                    resetHighlightElementByID('indicator_ddm');
                }
            });
    },
    fill:function(){
        const menu = this;
        //get all possebilities via ajax
        $.when(getAllAvaliableIndicators()).done(function(data){
            menu.possebilities = data;
            let container = $('#kat_auswahl');
            let html = "";
            //fill the Options
            $.each(data,function(cat_key,cat_value){
                let cat_id = cat_key,
                    cat_name = cat_value.cat_name;
                if(mainView.getWidth()>=500) {
                    html += '<div id="kat_item_'+cat_id+'" class="ui left pointing dropdown link item link_kat" value="' + cat_id + '"><i class="dropdown icon"></i>' + cat_name + '<div id="submenu' + cat_id + '" class="menu submenu upward">';
                }
                $.each(cat_value.indicators, function (key, value) {
                    let ind_id = key;
                    let ind_name = value.ind_name;
                    let markierung = value.significant;
                    let grundakt_state = value.basic_actuality_state;
                    let einheit = value.unit;
                    let times = value.times;
                    if (markierung === 'true') {
                        html += '<div class="indicator_ddm_item_bold item link_sub" id="' + ind_id + '_item' + '" data-times="'+times+'" data-einheit="'+einheit+'" data-value="' + ind_id + '" value="' + ind_id + '" data-kat="' + cat_id + '" data-name="' + ind_name + '" data-sort="1" data-actuality="'+grundakt_state+'">';
                    } else {
                        html += '<div class="item link_sub" id="' + ind_id + '_item' + '" data-times="'+times+'" data-einheit="'+einheit+'" data-value="' + ind_id + '" value="' + ind_id + '" data-kat="' + cat_id + '" data-name="' + ind_name + '" data-sort="0" data-actuality="'+grundakt_state+'">';
                    }
                    html += ind_name + "</div>";
                });
                html +='</div></div>';
            });
            container.empty().append(html);
            //sort by attribute 'markierung'
            $(container).find('div').sort(function(a,b){
                let contentA =parseInt( $(a).attr('data-sort'));
                let contentB =parseInt( $(b).attr('data-sort'));
                return (contentA < contentB) ? -1 : (contentA > contentB) ? 1 : 0;
            });
        })
        //append 'Siedlungsdicht for Herr Dr. Meinel'
            .then(function() {
                    $('#B02DT_item').clone().appendTo('#submenuN');
                }
            );
    },
    checkAvability:function(_ind,draw){
        let ind = this.getSelectedIndikator();
        const menu = this;
        if(_ind){ind = _ind;}
        $.when(getAvabilityIndicator(ind)).done(function(data){
            $.each(data,function(key,value) {
                if(value.ind === ind) {
                    if(value.avability==false){
                        alertNotAsRaster();
                        $('.raster_export').hide();
                        return false;
                    }else{
                        if(!ind){
                            menu.setIndikatorParameter(ind);
                        }else{
                            menu.updateIndikatorParamter(ind);
                        }
                        if(draw){
                            indikatorauswahl.setIndicator(ind);
                        }
                        $('.raster_export').show();
                        return true;
                    }
                }
            });
        });

    },
    setIndicator:function(indicator_id){
        const menu = this;

        let ind_param = menu.getSelectedIndikator();
        if (!ind_param) {
            menu.setIndikatorParameter(indicator_id);
        } else {
            menu.updateIndikatorParamter(indicator_id);
        }
        $('#ind_choice_info').css({"color": "black", "font-weight": "bold"});
        $('.kennblatt').show();
        //reset the first init layer
        startMap.remove();
        farbschema.reset();
        $.when(getJahre(indicator_id)).done(function(data_time){
            menu.all_possible_years = data_time;
            let years_selected = [];
            $.each(data_time,function(key,value){
               if(value<getCurrentYear()){
                   years_selected.push(value);
               }
            });
            menu.filtered_years = years_selected;
            zeitslider.init(years_selected);
            $.when(getRaumgliederung(indicator_id)).done(function(data_raum){
                initRaumgliederung(data_raum);
            });
        });
        //hightlight the kat and item inside the ddm
        $.when($('.item').each(function () {
            $(this).css({"color": "rgba(0,0,0,.87)", "font-weight": ""})
        }))
            .then($choice.css("color", farbschema.getColorMain()))
            .then($('#kat_item_' + menu.getSelectedIndikatorKategorie()).css({"color": farbschema.getColorMain(), "font-weight": "bold"}));
    },
    getIndikatorInfo:function(indicator_id,key_name){
        let val_found = null;
        $.each(this.getPossebilities(),function(cat_key,cat_value){
            $.each(cat_value.indicators, function (key, value) {
                if(key===indicator_id){
                    val_found = value[key_name];
                }
            });
        });
        return val_found;
    },
    getSelectedIndikatorText:function(){
        const menu = this;
        let name = this.getDOMObject().dropdown('get text');
        if(name.toLowerCase().indexOf("bitte")===0 || menu.getSelectedIndikator() !== menu.previous_indikator){
            setTimeout(function(){
                name = $('#'+menu.getSelectedIndikator()+"_item").text();
                menu.setSelectedIndikatorText(name);
            },1000);
        }
        return name;
    },
    setSelectedIndikatorText:function(value){
        this.getDOMObject().dropdown('set text',value);
    },
    getSelectedIndikatorText_Lang:function(){
        //just as control mechanism
        this.getSelectedIndikatorText();
        return $('#'+this.getSelectedIndikator()+"_item").attr("data-name");
    }
};
const gebietsauswahl = {
    mapLayer:[],
    mapLayerGrund:[],
    addedAGS:[],
    paramter:'ags_array',
    getParamter:function(){
        return urlparamter.getUrlParameter(this.paramter);
    },
    setParamter:function(_value){
        urlparamter.setUrlParameter(this.paramter,_value);
    },
    updateParamter:function(_value){
        urlparamter.updateURLParameter(this.paramter,_value);
    },
    removeParamter:function(){
      urlparamter.removeUrlParameter(this.paramter);
    },
    getMapLayer:function(){return this.mapLayer;},
    setMapLayer:function(array){this.mapLayer=array;},
    getMapLayerGrund:function(){return this.mapLayerGrund;},
    setMapLayerGrund:function(array){this.mapLayerGrund=array;},
    getAddedAGS:function(){return this.addedAGS;},
    setAddedAGS:function(array){this.addedAGS=array;},
    getDOMObject:function(){
        $menu = $('#dropdown_grenzen_container');
        return $menu;
    },
    init:function(){
        /*
        Extend the existing semntic ui Object with the needed functions
         */
        let parameter_ags_set = this.getParamter(),
            mapLayer = [],
            mapLayer_grund = [],
            ags_array = [],
            menu =  this.getDOMObject(),
            object = this,
            geoJson = indikatorJSON.getJSONFile();
        if (this.countTags()=== 0) {
            this.fill(indikatorJSON.getJSONFile());
            if(typeof parameter_ags_set ==='undefined'){
                object.setParamter('');
            }
            //create parameter AGS_ARRAY if not set
            let raumgliederung = getRaumgliederung_text();
            $('#grenzen_choice').text('Gebietsauswahl: ' + raumgliederung.replace("- nur", ""));
            menu
                .dropdown({
                    onAdd: function (addedValue, addedText, $addedChoice) {
                        //close after each choice
                        menu.dropdown('hide');
                        indicatorJSONGroup.clean();
                        //update the paramter
                        ags_array.push(addedValue);
                        if(getMapLayerArray().length!==ags_array.length) {
                            object.updateParamter(ags_array.toString());
                        }
                        $.each(geoJson.features, function (key, value) {
                            $.each(value, function (_key, _value) {
                                if (_value.ags === addedValue) {
                                    mapLayer.push(value);
                                }
                            });
                        });
                        //only if possible
                        try {
                            $.each(getGeoJsonGrund().features, function (key, value) {
                                $.each(value, function (_key, _value) {
                                    if (_value.ags === addedValue) {
                                        mapLayer_grund.push(value);
                                    }
                                });
                            });
                        } catch (err) {

                        }
                        object.setMapLayer(mapLayer);
                        object.setMapLayerGrund(mapLayer_grund);
                        object.setAddedAGS(ags_array);
                        if(getRaumgliederungfeinID() && !page_init){
                            indikatorJSON.init(getRaumgliederungfeinID());
                        }else {
                            object.addSelectedLayersToMap();
                            table.create();
                            showRaumgl_MenuFein();
                        }

                        if(typeof getRaumgliederungfeinID() !== 'undefined'){
                            setHeader(getDropdownRaumgglGrobSelectionAsString()+" als "+$("#Raumgliederung_Fein").find("option:selected").text());
                        }else{
                            setHeader(getDropdownRaumgglGrobSelectionAsString());
                        }
                    },
                    onRemove: function (removedValue, removedText, $removedChoice) {
                        //changed: prevend Trigger
                        let value = removedValue;
                        if (object.countTags() > 1 && changed == false) {
                            object.removeSelectedLayersFromMap(value);
                            if(typeof getRaumgliederungfeinID() !== 'undefined'){
                                setHeader(getDropdownRaumgglGrobSelectionAsString()+" als "+$("#Raumgliederung_Fein").find("option:selected").text());
                            }else{
                                setHeader(getDropdownRaumgglGrobSelectionAsString());
                            }
                        }else{
                            if(changed === false) {
                                $.when(urlparamter.removeUrlParameter('raumgl_fein'))
                                    .then(object.removeParamter())
                                    .then(clearAGS_Array())
                                    .then(indikatorJSON.init())
                                    .then(hideRaumgl_MenuFein())
                                    .then(setMenuFein())
                                    .then(setHeader());
                            }
                        }
                        $("tr[id^='"+value+"']").remove();
                    }
                });
        }else{
            showRaumgl_MenuFein();
        }
    },
    addSelectedLayersToMap:function(){
        indicatorJSONGroup.clean();
        $.each(this.getMapLayer(), function (key, value) {
            indikatorJSON.addToMap(value, klassengrenzen.getKlassen());
        });
        $.each(this.getMapLayerGrund(), function (key, value) {
            createMap_grund(value, getKlassenJson_grund());
        });
        indicatorJSONGroup.fitBounds();
    },
    removeSelectedLayersFromMap:function(value){
        console.log(value);
        let mapLayer = this.getMapLayer(),
            mapLayer_grund = this.getMapLayerGrund(),
            ags_array= this.getAddedAGS();
        for (let i = 0; i < mapLayer.length; i++) {
            if (mapLayer[i].properties.ags == value) {
                mapLayer.splice(i, 1);
                break;
            }
        }
        for (let i = 0; i < mapLayer_grund.length; i++) {
            if (mapLayer_grund[i].properties.ags == value) {
                mapLayer_grund.splice(i, 1);
                break;
            }
        }
        for (let i = 0; i < ags_array.length; i++) {
            if (ags_array[i] == value) {
                ags_array.splice(i, 1);
                break;
            }
        }
        this.updateParamter(ags_array.toString());
        this.setMapLayer(mapLayer);
        this.setMapLayerGrund(mapLayer_grund);
        this.setAddedAGS(ags_array);
        if(getRaumgliederungfeinID()) {
            indikatorJSON.init(getRaumgliederungfeinID());

        }else{
            this.addSelectedLayersToMap();
        }
        indicatorJSONGroup.fitBounds();
    },
    fill:function(json){
        let items = {values:[]},
            selection = this.getDOMObject().dropdown('get value').split(','),
            json_set = json;
        for (let i = 0; i < json_set.features.length; i++) {
            let gen = json_set.features[i].properties.gen,
                ags = json_set.features[i].properties.ags,
                fc = json_set.features[i].properties.fc;
            //items.push('<div class="item item_ddm_grob" data-value="'+ags+'" data-sort="'+gen+'">'+gen+'</div>');
            if(fc===0 || typeof fc !=='undefined' && $.inArray(ags,selection)===-1) {
                items['values'].push({
                    text: gen,
                    value: ags,
                    name: gen
                });
            }
        }
        items['values'].sort(function (a, b) {
            return a.name.localeCompare(b.name);
        });

        this.getDOMObject().dropdown('setup menu',items);
    },
    clear:function(){this.getDOMObject().dropdown('clear')},
    countTags:function(){
        let tags =  this.getDOMObject().find('a');
        return tags.length;
    }
};
const raeumlicheauswahl = {
    param:'raeumliche_gliederung',
    setParam:function(_value){
      urlparamter.setUrlParameter(this.param,_value);
    },
    getParam:function(){
      return urlparamter.getUrlParameter(this.param);
    },
    upateParam:function(_value){
      urlparamter.updateURLParameter(this.param,_value);
    },
    getDOMObject:function(){
        $elem = $('#spatial_choice_checkbox_container');
        return $elem;
    },
    init:function(){
        indikatorauswahl.init();
        legende.init();
        const checkbox = this;
        checkbox.getDOMObject()
            .checkbox('enable')
            .checkbox({
                onChecked: function () {
                  checkbox.setRaster();
                },
                onUnchecked: function() {
                    checkbox.setGebiete();
                }
            });
        if(raeumlicheauswahl.getRaeumlicheGliederung()==='raster'){
            checkbox.setChecked();
        }
    },
    setRaster:function(){
        const object = this;
        $.when(rightView.close())
            .then(object.upateParam('raster'))
            .then(indicatorJSONGroup.clean())
            .then(rasterize.addTo(map))
            .then(indikatorauswahl.fill())
            .then(indikatorauswahl.checkAvability(false,true))
            .then(vergleichcontrol.addTo(map))
            .then(legende.init())
            .then(function(){$('#spatial_range_raster').show()})
            .then(function(){$('#spatial_range_gebiete').hide()})
            .then($('#gebiete_label').css("color","black"))
            .then($('#raster_label').css("color",farbschema.getColorMain()))
            .then(panner.hide());
    },
    setGebiete:function(){
        const object = this;
        $.when(object.upateParam('gebiete'))
            .then(mainView.restoreView())
            .then(cleanRasters())
            .then(indikatorauswahl.fill())
            .then(indikatorauswahl.checkAvability(false,true))
            .then(function(){$('#panRight').show()})
            .then(map.removeControl(rasterize))
            .then(map.removeControl(vergleichcontrol))
            .then(function(){$('#spatial_range_raster').hide()})
            .then(function(){$('#spatial_range_gebiete').show()})
            .then($('#gebiete_label').css("color",farbschema.getColorMain()))
            .then($('#raster_label').css("color","black"))
            .then(removeSideBySideControl())
            .then(panner.init());
    },
    setChecked:function(){
        this.getDOMObject()
            .checkbox('check');
    },
    setUnchecked:function(){
        this.getDOMObject()
            .checkbox('set unchecked');
    },
    getRaeumlicheGliederung:function(){
        let parameter =  this.getParam();
        if(!parameter){
            this.setParam('gebiete');
        }
        return this.getParam();
    }
};
const farbschema = {
    farben: {
        grey: ['f0f0f0', '636363'],
        YlOrRd: ['ffeda0', 'f03b20'],
        YlGnBu: ['edf8b1', '2c7fb8'],
        PuRd: ['e7e1ef', 'dd1c77']
    },
    paramter: 'farbschema',
    getDOMObject: function () {
        $elem = $('#color_schema');
        return $elem;
    },
    getFarbwahlButtonDomObject: function () {
        $elem = $("#farbwahl_btn");
        return $elem;
    },
    getParamter: function () {
        return urlparamter.getUrlParameter(this.paramter);
    },
    setParamter: function (_value) {
        urlparamter.setUrlParameter(this.paramter, _value)
    },
    updateParamter: function (_value) {
        urlparamter.updateURLParameter(this.paramter, _value);
    },
    removeParamter: function () {
        urlparamter.removeUrlParameter(this.paramter);
    },
    fill: function () {
        let color_container = $('#color_schema'),
            def = $.Deferred();

        const object = this;

        function defCalls() {
            let requests = [];
            $.each(object.farben, function (key, value) {
                requests.push(getColorHTML(value, key.toString()));
            });
            $.when.apply($, requests).done(function () {
                def.resolve(arguments);
            });
            return def.promise();
        }

        defCalls().done(function (arr) {
            color_container.empty();
            $.each(arr, function (key, value) {
                color_container.append(value[0]);
            })
        });
    },
    init: function () {
        const object = this;
        this.fill();
        $(document).on('click','.color-line',function(){
            let content = $(this).html(),
                id = $(this).attr("id");
            object.getFarbwahlButtonDomObject()
                .empty()
                .append('<span id="color_remove" class="glyphicon glyphicon-remove"></span><div class="color-line">' + content + '</div>');
            //craete the new colored map
            console.log(object.getParamter());
            let paramter = object.getParamter();
            if (typeof paramter !== 'undefined') {
                object.updateParamter(object.farben[id].toString());
            } else {
                object.setParamter(object.farben[id].toString());
            }
            object.setColorChoice();
        });
        $(document).on('click','#color_remove',function(){
            object.removeParamter();
            object.getFarbwahlButtonDomObject()
                .empty()
                .append('Bitte Wählen..<span class="caret"></span>');
            object.getDOMObject().show();
            if (raeumlicheauswahl.getRaeumlicheGliederung() === 'raster') {
                initRaster();
            }
            else {
                if (typeof getRaumgliederungfeinID() === 'undefined') {
                    indikatorJSON.init();
                } else {
                    indikatorJSON.init(getRaumgliederungfeinID());
                }
            }
        });
    },
    reset:function(){
        this.removeParamter();
        this.getFarbwahlButtonDomObject()
            .empty()
            .append('Bitte Wählen..<span class="caret"></span>');
    },
    setColorChoice: function () {
        if (raeumlicheauswahl.getRaeumlicheGliederung()==="gebiete") {
            if (typeof getRaumgliederungfeinID() === 'undefined') {
                indikatorJSON.init();
            } else {
                indikatorJSON.init(getRaumgliederungfeinID());
            }
        }
        else {
            console.log("++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++");
            initRaster();
        }
    },
    getColorActive: function () {
        return "#8CB91B";
    },
    getColorMain: function () {
        return '#4E60AA';
    },
    getHexMin: function () {
        let paramter = this.getParamter(),
            return_value = '';
        if (typeof paramter !== 'undefined') {
            let value = paramter.split(',');
            return_value = value[0];
        }
        return return_value;
    },
    getHexMax: function () {
        let paramter = this.getParamter(),
            return_value = '';
        if (typeof paramter !== 'undefined') {
            let value = paramter.split(',');
            return_value = value[1];
        }
        console.log(return_value);
        return return_value;
    }
};
