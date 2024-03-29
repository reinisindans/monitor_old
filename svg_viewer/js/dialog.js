//This file stores all the jquery Dialogs inside the Page
$(function() {
    //Feedback
    $('#feedback_a').click(function () {

        //workaround for php 7 -> run in PHP 5
        let url = 'https://maps.ioer.de/monitor_raster/php/mail/mailer.php';

        $("#feedback_div").dialog({
            title: 'Feedback',
            hide: 'blind',
            show: 'blind',
            resizable: true,
            modal: true,
            width: "70%",
            position: {
                my: "center",
                at: "center",
                of: window
            },
            open: function (ev, ui) {
                //Quelle: http://twitterbootstrap.org/bootstrap-form-validation/
                $('#reg_form').bootstrapValidator({
                    feedbackIcons: {
                        valid: 'glyphicon glyphicon-ok',
                        invalid: 'glyphicon glyphicon-remove',
                        validating: 'glyphicon glyphicon-refresh'
                    },
                    fields: {
                        name: {
                            validators: {
                                stringLength: {
                                    min: 2,
                                    message: 'Bitte nennen Sie uns ihren Namen'

                                },
                                notEmpty: {
                                    message: 'Dies ist kein gültiger Name'
                                }
                            }
                        },
                        message: {
                            validators: {
                                stringLength: {
                                    min: 10,
                                    max: 1000,
                                    message: 'Bitte hinterlassen Sie uns eine Nachricht, mit mindestens 10 und maximal 1000 Zeichen'
                                },
                                notEmpty: {
                                    message: 'Bitte hinterlassen Sie uns eine Nachricht, mit mindestens 10 und maximal 1000 Zeichen'
                                }
                            }
                        },
                        email: {
                            validators: {
                                notEmpty: {
                                    message: 'Bitten nennen Sie uns ihre Email Adresse'
                                },
                                emailAddress: {
                                    message: 'Dies ist keine gültige Email Adresse'
                                }
                            }
                        }
                    }
                });
                let send = 'true';
                $('#send_btn').click(function () {
                    $('#success_message').slideDown({opacity: "show"}, "slow");
                    $('#reg_form').data('bootstrapValidator').resetForm();

                    // Use Ajax to submit form data
                    let name = $("#name").val();
                    let email = $("#email").val();
                    let message = $("#message").val();
                    $.ajax({
                        type: 'POST',
                        url: url,
                        data: "name=" + name + "&email=" + email + "&message=" + message,
                        error: function (request, status, error) {
                            console.log(request.responseText);
                        },
                        success: function (data) {

                            if (data.indexOf("Error") >= 0) {
                                send = 'false';
                                swal({
                                    title: 'Fehler!',
                                    text: 'Ihre Nachricht konnte nicht zugestellt werden. Bitte kontaktieren Sie uns unter: <a id="mail_to">Email</a>',
                                    type: 'error',
                                    html: true
                                })
                            }
                            $('#mail_to').click(function () {
                                window.location.href = "mailto:l.mucha@ioer.de";
                            });
                        }
                    });
                    $('#feedback_div').dialog('close');
                    if (send === 'true') {
                        swal(
                            'Vielen Dank!',
                            'Ihre Nachricht wurde zugestellt.',
                            'success'
                        )
                    }

                    return false;
                });
                $('#cancel').click(function () {
                    $('#feedback_div').dialog('close');
                    swal(
                        'Abgebrochen',
                        'Falls Sie sich es anders überlegen, würde wir uns sehr freuen !',
                        'error'
                    )
                    return false;
                });
            }
        });
    });

//OGC Export
    $("#wms").click(function () {
        let indikator = indikatorauswahl.getSelectedIndikator();

        let wms_link = 'http://maps.ioer.de/cgi-bin/wms?MAP=' + indikator + '_100';

        if(typeof indikatorauswahl.getSelectedIndikator() !=='undefined') {
            $('#checkbox_wms').prop('checked', false);
            $('#wms_allow').hide();

            $('#checkbox_wms').change(function () {
                if ($(this).is(":checked")) {
                    $('#wms_allow').show();
                    $('#wms_link').text(wms_link);
                } else {
                    $('#wms_allow').hide();
                }
            });
            $("#wms_text").dialog({
                title: 'WMS Dienst',
                hide: 'blind',
                show: 'blind',
                width: 500,
                resizable: false
            });
        }else{
            alertNoIndicatorChosen();
        }
        return false;
    });
    $("#wcs").click(function () {
        let indikator = indikatorauswahl.getSelectedIndikator();
        let wcs_link = 'http://maps.ioer.de/cgi-bin/wcs?MAP=' + indikator + '_wcs';
        if(typeof indikatorauswahl.getSelectedIndikator() !=='undefined') {
            $('#checkbox_wcs').prop('checked', false);
            $('#wcs_allow').hide();
            $('#checkbox_wcs').change(function () {
                if ($(this).is(":checked")) {
                    $('#wcs_allow').show();
                    $('#wcs_link').text(wcs_link);
                } else {
                    $('#wcs_allow').hide();
                }
            });

            $("#wcs_text").dialog({
                title: 'WCS Dienst',
                hide: 'blind',
                show: 'blind',
                width: 500,
                resizable: false
            });
        }else{
            alertNoIndicatorChosen();
        }

        return false;
    });
    $("#wfs").click(function () {
        let indikator = indikatorauswahl.getSelectedIndikator();
        let wfs_link = 'http://maps.ioer.de/cgi-bin/wfs?MAP=' + indikator;
        if(typeof indikatorauswahl.getSelectedIndikator() !=='undefined') {
            $('#checkbox_wfs').prop('checked', false);
            $('#wfs_allow').hide();

            $('#checkbox_wfs').change(function () {
                if ($(this).is(":checked")) {
                    $('#wfs_allow').show();
                    $('#wfs_link').text(wfs_link);
                } else {
                    $('#wfs_allow').hide();
                }
            });

            $("#wfs_text").dialog({
                title: 'WFS Dienst',
                hide: 'blind',
                show: 'blind',
                width: 500,
                resizable: false
            });
        }else{
            alertNoIndicatorChosen();
        }
        return false;
    });
});
//Feedback
function openKennblatt(ind){
    console.log("openKennblatt()");
    let ind_set = indikatorauswahl.getSelectedIndikator();
    if(ind){
        ind_set = ind;
    }
    $("#kennblatt_text").dialog({
        title: 'Indikatorkennblatt',
        hide: 'blind',
        show: 'blind',
        width: calculateWidth(),
        height: calculateHeight(),
        open: function (ev, ui) {
            $.ajax({
                url: "php/dialog/kennblatt.php",
                type: "POST",
                data: {
                    'ID_IND': ind_set
                },
                dataType: "html",
                error: function (xhr, ajaxOptions, thrownError) {
                    console.log(xhr.status);
                    console.log(thrownError);
                },
                success: function (data) {
                    $('#kennblatt_text')
                        .html(data);
                    setTimeout(function() {
                        $('#kennblatt_text').scrollTop(0);
                    },500);
                }
            });
        }
    });
}
//indikatorenvergleich
function openGebietsprofil(ags,name,ind){
    console.log("öffne Gebietsprofil für: "+ags+"||"+name+"||"+ind);
    let $dialogContainer = $('#gebietsprofil_content');
    let $detachedChildren = $dialogContainer.children().detach();

    $( "#gebietsprofil_content").dialog({
        title: 'Gebietscharakteristik',
        width: "80%",
        height: calculateHeight(),
        open: function(ev, ui){
            $('.ui-dialog-titlebar-close').attr('id','close_dialog');
            $detachedChildren.appendTo($dialogContainer);
            $('.ui-widget-overlay').addClass('custom-overlay');
            $.ajax({
                url: "php/dialog/gebietsprofil.php",
                type: "POST",
                dataType: "html",
                data: {
                    'ags': ags,
                    'name': name
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    console.log(xhr.status);
                    console.log(thrownError);
                },
                success: function (data) {
                    $('#gebietsprofil_content').html(data);
                }
            });
        },
        close: function(){
            $('.ui-dialog-titlebar-close').removeAttr('id');
        }
    });
}

function openStatistik(ags, name, wert){

    console.log("openStatistik für :"+ags+"||"+name+"||"+wert);

    let dialogContainer = $('#objektinformationen_content');
    dialogContainer.dialog({
        title: 'Statistik Gebietseinheit',
        width: calculateWidth(),
        height: calculateHeight(),
        open: function(ev, ui){
            $('.ui-widget-overlay').addClass('custom-overlay');
            $.when(getStatistik(ags, name, wert)).done(function(data){dialogContainer.html(data);})
        }
    });
}
//development chart----------------------------------------------------------------------
function openEntwicklungsdiagramm(ags,name,ind,ind_vergleich) {
    let ind_array_chart = [];
    let state_stueztpnt = true;
    let state_prognose = false;
    let resizeId;
    $('#default_diagramm_choice').text(indikatorauswahl.getSelectedIndikatorText());
    //call back for resize finish
    function resize(){
        InitChart(ind_array_chart);
    }
    let title = 'Entwicklungsvergleich';
    if(!ind_vergleich){
        title = 'Indikatorwertentwicklung';
    }
    if($('#kat_auswahl_diagramm').length >=1){

        cloneIndicatorMenu('kat_auswahl_diagramm', 'link_kat_diagramm', 'right',['X'],false);
        //remove items which have not the simular unit
        $('#indicator_ddm_diagramm .submenu .item').each(function(){
           if(indikatorauswahl.getIndikatorEinheit() !== $(this).data('einheit')){
               $(this).remove();
           }
        });
        //clear empty categories
        $('.link_kat_diagramm').each(function(){
            if($(this).find('.submenu').children().length===0){
                $(this).remove();
            }
        });
        $('#kat_auswahl_diagramm').find('.item').each(function(){$(this).css("color","rgba(0,0,0,.87)")});
        //remove selected Indicatopr from the list
        $("#kat_auswahl_diagramm").find("#"+indikatorauswahl.getSelectedIndikator()+"_item").remove();
    }
    $("#entwicklungsdiagramm_content").dialog({
        title: title,
        width: calculateWidth(),
        height: calculateHeight()+50,
        resize: function (event, ui) {
            clearTimeout(resizeId);
            resizeId = setTimeout(resize,100);
        },
        open: function (ev, ui) {
            clearChart();
            //set the info text
            $("#diagramm_gebietsname").text(name);
            $('#diagramm_ags').text(" (" + ags + ")");
            $('#diagrmm_gebiets_typ').text(" "+indikatorauswahl.getIndikatorEinheit());
            if(ind_vergleich) {
                $('#indikator_choice_container_diagramm').show();
                //initializeFirstView the dropdown menu to expand the chart with other inbdicators
                if (ind_array_chart.length == 0) {
                    ind_array_chart.push({"id": ind});
                }
                InitChart(ind_array_chart);
                //set the selcted value
                $('#indicator_ddm_diagramm')
                    .dropdown({
                        'maxSelections': 2,
                        onAdd: function (addedValue, addedText, $addedChoice) {
                            $.when(ind_array_chart.push({"id": addedValue}))
                                .then(InitChart(ind_array_chart))
                                .then($(this).blur());
                        },
                        onLabelRemove: function (value) {
                            $.when(ind_array_chart = removefromarray(ind_array_chart, value))
                                .then(InitChart(ind_array_chart));
                        }
                    });
            }else{
                $('#indikator_choice_container_diagramm').hide();
                $.when(clearChartArray())
                    .then(ind_array_chart.push({"id": ind}))
                    .then(InitChart(ind_array_chart));
            }
            //options-------------------------
            //1. alle Stützpkt
            $('#alle_stpkt')
                .prop('checked', false)
                .change(function(){
                    if (this.checked) {
                        state_stueztpnt = false;
                        InitChart(ind_array_chart);
                    }else{
                        state_stueztpnt = true;
                        InitChart(ind_array_chart);
                    }
                });
            if($.inArray(2025,indikatorauswahl.getAllPossibleYears())!==-1){
                $('#prognose_container').show();
            }else{
                $('#prognose_container').hide();
            }
            //2. Prognose
            $('#prognose')
                .prop('checked', false)
                .change(function(){
                    if (this.checked) {
                        state_prognose = true;
                        InitChart(ind_array_chart);
                    }else{
                        state_prognose = false;
                        InitChart(ind_array_chart);
                    }
                });
        },
        close:function(){
            $('#indicator_ddm_diagramm')
                .dropdown('clear');
        }
    });
    //create the chart
    function InitChart(array) {
        console.log('Init Chart');
        //show loading info
        $('#diagramm_loading_info').show();

        if (array.length == 0) {
            $('#visualisation').hide();
            $('#Hinweis_diagramm_empty').show();
        } else {
            //clean the chart
            $('#visualisation').show().empty();
            //remove the tip if shown
            $('#Hinweis_diagramm_empty').hide();
        }

        //set dynamic the chart dimensions
        $('#diagramm').css("height", $('#entwicklungsdiagramm_content').height() - $('#diagramm_options').height() - 70 + (array.length * 30));
        //clean the legend
        $('.legende_single_part_container').remove();

        let merge_data = [];

        let svg = d3.select("#visualisation"),
            margin = {top: 20, right: 60, bottom: 30, left: 60};

        let chart_width = $('#diagramm').width() - margin.left - margin.right;
        let chart_height = 430 - (array.length * 30);
        //let chart_height = $('.ui-dialog').height()*(1.5/3);
        let x = d3.scaleTime().range([0, chart_width]);
        let y = d3.scaleLinear().range([chart_height, 0]);

        let g = svg.append("g")
            .attr("transform", "translate(" + margin.left + "," + margin.top + ")");

        let line = d3.line()
            .x(function (d) {
                return x(d.date);
            })
            .y(function (d) {
                return y(d.value);
            });

        let legend = svg.append("g")
            .attr("class", "legend");

        //callback for returning the Data
        function getDataArray(ind) {
            return $.ajax({
                type: "GET",
                url: "php/dialog/entwicklungs_diagramm.php",
                data: {
                    ags: ags,
                    indikator: ind,
                    state_stueztpnt: state_stueztpnt,
                    state_ind_vergleich: ind_vergleich,
                    prognose: state_prognose
                }
            });
        }

        let def = $.Deferred();

        function defCalls() {
            let requests = [];
            $.each(array, function (key, value) {
                requests.push(getDataArray(value.id));
            });
            $.when.apply($, requests).done(function () {
                def.resolve(arguments);
            });
            return def.promise();
        }

        defCalls().done(function (arr) {
            let i = 0;
            $.each(array, function (key, val) {
                let obj = {"id": val.id, "values": arr[i][0]};
                if (array.length == 1) {
                    obj = {"id": val.id, "values": arr[0]};
                }
                merge_data.push(obj);
                i++;
            });
            $.when($('#diagramm_loading_info').hide())
                .then(scaleChart())
                .then(createPath());
        });

        function scaleChart() {
            let data = [];
            $.each(merge_data, function (key, value) {
                $.each(value.values, function (x, y) {
                    data.push({"year": y.year, "value": y.value, "real_value": y.real_value});
                })
            });
            let minYear = getMinArray(data, "year");
            let maxYear = getMaxArray(data, "year");
            let maxValue = parseInt(Math.round(getMaxArray(data, "value")) + 1);
            let minValue = parseInt(Math.round(getMinArray(data, "value")) - 1);
            let min_date = new Date(minYear - 2, 0, 1);
            let max_date = new Date(maxYear + 5, 0, 1);
            let current_year = getCurrentYear();
            //reset max year if prognose is unset
            if (state_prognose == false) {
                max_date = new Date(current_year + 2, 0, 1);
            }
            if (minYear == maxYear) {
                x.domain(d3.extent([new Date(maxYear - 5, 0, 1), max_date]));
            } else {
                x.domain(d3.extent([min_date, max_date]));
            }

            y.domain(d3.extent([minValue, maxValue]));


            g.append("g")
                .attr("class", "axis axis--x")
                .attr("transform", "translate(0," + chart_height + ")")
                .call(d3.axisBottom(x).scale(x).ticks(data.length +1).tickFormat(function(d){
                    if(state_prognose == true){
                        if(d.getFullYear() <= getCurrentYear()){
                            return d.getFullYear();
                        }
                    }else{
                        return d.getFullYear();
                    }
                }));

            g.append("g")
                .attr("class", "axis axis--y")
                .call(d3.axisLeft(y).ticks(6).tickFormat(function (d) {
                    if (ind_vergleich) {
                        if (d == 0) {
                            if (array.length == 1) {
                                return data[0].real_value;
                            } else {
                                return 'x';
                            }
                        }
                        else if (d != minValue || d != maxValue) {
                            return d;
                        }
                    } else {
                        return d;
                    }
                }))
                .append("text")
                .attr("class", "axis-title")
                .attr("transform", "rotate(-90)")
                .attr("y", 6)
                .attr("dy", ".71em")
                .style("text-anchor", "end")
                .attr("fill", "#4E60AA");
        }

        //fill the path values
        function createPath() {
            $.each(merge_data, function (key, value) {
                let data = value.values;
                parseTime(data);
                appendData(data, data[0].color.toString());
                createCircle(data, data[0].color.toString());
                setLegende(data, data[0].color.toString());
            });
        }

        //add the data
        function appendData(data, color) {
            let color_set = color;
            g.append("path")
                .data(data)
                .attr("class", "path_line")
                .attr("id", data[0].id + "_path")
                .attr('stroke', color_set)
                .attr("stroke-dasharray", ("7, 3"))
                .attr("fill", "none")
                .attr("d", line(data));
        }

        let margin_top = 0;

        function setLegende(data, color) {
            legend.append('g')
                .append("rect")
                .attr("x", margin.left)
                .attr("y", chart_height + 50 + margin_top)
                .attr("width", 10)
                .attr("height", 10)
                .style("fill", color);

            legend.append("text")
                .attr("x", margin.left + 30)
                .attr("y", chart_height + 60 + margin_top)
                .attr("height", 30)
                .attr("width", chart_width)
                .style("fill", color)
                .text(data[0].name + ' in ' + data[0].einheit);

            margin_top += 20;
        }

        function createCircle(data, color) {
            let color_set = color;
            let format_month = d3.timeFormat("%m");
            let format_year = d3.timeFormat("%Y");
            for (i = 0; i < data.length; i++) {

                let circle = g.append("g");
                circle.append("circle")
                    .attr("class", data[0].id + "_circle circle")
                    .attr("r", 5.5)
                    .attr("data-value", data[i].value)
                    .attr('fill', function () {
                        if (data[i].year > (new Date).getFullYear()) {
                            return 'white';
                        } else {
                            return color_set;
                        }
                    })
                    .attr('stroke', color_set)
                    .attr("data-realvalue", data[i].real_value)
                    .attr("data-date", format_month(data[i].date) + "_" + format_year(data[i].date))
                    .attr("data-date_d3", data[i].date)
                    .attr("data-name", data[i].name)
                    .attr("data-ind", data[i].id)
                    .attr("data-year", data[i].year)
                    .attr("data-month", data[i].month)
                    .attr("data-einheit", data[i].einheit)
                    .attr("data-color", color_set)
                    .attr("transform", "translate(" + x(data[i].date) + "," + y(data[i].value) + ")")
                    .on("mouseover", handleMouseOver).on("mouseout", handleMouseOut);
            }
        }

        // Create Event Handlers for mouse
        function handleMouseOver(d) {
            $(this).attr("r", 7.5);
            let ind = $(this).data('ind');
            let year = $(this).data('year');
            let month = $(this).data('month');
            let value = $(this).data('value');
            let value_txt = value.toString().replace(".", "");
            let real_value = $(this).data('realvalue');
            let date = $(this).data('date');
            let date_d3 = $(this).data('date_d3');
            let color = $(this).data('color');
            let einheit = $(this).data('einheit');
            let x = d3.event.pageX - document.getElementById('visualisation').getBoundingClientRect().x + 10;
            let y = d3.event.pageY - document.getElementById('visualisation').getBoundingClientRect().y + 120;
            let html = '';
            let text_value = "Wert: " + real_value + einheit;
            //the tooltip for ind vergleich
            if (ind_vergleich) {
                let data = [];
                $.each(merge_data,function(x,y){
                    if (y.id === ind) {
                        data.push(y.values);
                    }
                });
                let ind_before = getIndexBefore(data[0],ind, year);

                //check if the oldest year is hover
                if (ind_before == -1) {
                    html = text_value + "<br/>" + "Stand: " + month + "/" + year;
                } else {
                    //the text part
                    let date_before = "von " + data[0][ind_before].month + "/" +data[0][ind_before].year + " bis " + month + "/" + year;
                    let text_value_dev = "Entwicklung: " + (value - data[0][ind_before].value).toFixed(2) + einheit;
                    html = text_value + "<br/>" + text_value_dev + "<br/>" + date_before;
                }
            } else {
                html = text_value + "<br/> Stand: " + month + "/" + year;
            }
            $('#tooltip').show()
                .css({"left": x, "top": y, "color": color, "border": "1px solid" + color})
                .html(html);
        }

        function handleMouseOut(d, i) {
            $(this).attr("r", 5.5);
            $('#tooltip').hide();
        }

        function parseTime(data) {

            let parseTime = d3.timeParse("%m/%Y");
            // format the data
            data.forEach(function (d) {
                d.date = parseTime(d.date);
                d.value = +d.value;
            });
            return data;
        }

        function getIndexBefore(merge_data,ind, year) {
            let array = [];
            for (let i = 0; i < merge_data.length; i++) {
                if (merge_data[i].id === ind) {
                    array.push(merge_data[i])
                }
            }
            for (let i = 0; i < array.length; i++) {
                if (array[i].id === ind) {
                    if (array[i].year == year) {
                        return i - 1;
                    }
                }
            }
        }
    }
    //export the diagramm as image
    $('#diagramm_download_format_choice').dropdown({
        onChange: function (value, text, $choice) {
            if (value === 'png') {
                let width = $('#visualisation').width();
                let height = $('#visualisation').height();
                //workaround for firefox Bug
                $('#visualisation').attr("height",height).attr("width",width);
                svgString2Image(width, height, '.container_diagramm #diagramm svg', saveIMAGE);
            } else if (value === 'pdf') {
                let width = $('#visualisation').width();
                let height = $('#visualisation').height();
                //workaround for firefox Bug
                $('#visualisation').attr("height",height).attr("width",width);
                svgString2DataURL(width, height, '.container_diagramm #diagramm svg', savePDF);
            }
        }
    });

}
//clear the diagramm
function clearChart(){
    $('#visualisation').empty();
    $("#diagramm_gebietsname").empty();
    $('#diagramm_ags').empty();
    $('#diagrmm_gebiets_typ').empty();
}
//clear the array
function clearChartArray(){
    ind_array_chart = [];
}
function openVergleichskarteDialog(){

    let raumgl;
    let jahre;
    let dialog_container = $('#vergleich_dialog');
    let button_map = $('#vergleich_btn');
    let dropdown_ind =  $('#indicator_ddm_vergleich');

    //open the dialog
    dialog_container.slideDown();
    button_map.css("background-color",farbschema.getColorActive());

    if ($('#kat_auswahl_vergleich').length === 1) {
        cloneIndicatorMenu('kat_auswahl_vergleich', 'ink_kat_vergleich', 'right',["X","G"],false);
        if(viewState.getViewState()==="responsive") {
            dropdown_ind.addClass('fluid search').dropdown('refresh');
        }else{
            dropdown_ind
                .removeClass('fluid')
                .removeClass('search')
                .dropdown('refresh');
        }
    }

    dropdown_ind.dropdown({
        onChange: function (value, text, $choice) {
            initVergleich(value);
            $('.ind_content').slideDown();
        }
    });

    //pre select the set indicator
    if(!dropdown_ind.dropdown('get value')||typeof dropdown_ind.dropdown('get value') ==='undefined') {
        dropdown_ind.dropdown('set selected',indikatorauswahl.getSelectedIndikator());
    }

    function initVergleich(value){
        let def = $.Deferred();
        function defCalls(){
            let requests = [
                getJahre(value),
                getRaumgliederung(value)
            ]
            $.when.apply($,requests).done(function(){
                def.resolve(arguments);
            });
            return def.promise();
        }
        defCalls().done(function(arr) {
            //now we have access to array of data
            jahre = arr[0][0];
            raumgl = arr[1][0];
            $.when(setTimeSliderVergleich(jahre)).then(setSpatialRangeSlider(raumgl)).then(setLegende());

        });

        function setTimeSliderVergleich(arr){
            let slider = $( "#zeit_slider_vergleich" );
            slider
                .slider({
                    orientation: "horizontal",
                    min: 0,
                    max: arr.length - 1,
                    step: 1,
                    value: 0,
                    stop: function (event, ui) {
                        setLegende();
                    }
                });

          pips.set(slider,arr);
        }
        function setSpatialRangeSlider(arr){
            let slider = $("#raum_slider_vergleich");
            slider.slider({
                orientation: "horizontal",
                min: 0,
                max: arr.length - 1,
                value: 0,
                step: 1,
                stop: function (event, ui) {
                    setLegende();
                }
            });

            let labels = [];
            $.each(arr,function(key,value){labels.push(value.replace('Raster','').replace('m',''));});
           pips.set(slider,labels);
        }
    }

    function setLegende(){
        let settings = getSettings();
        let ind = settings[0].ind;
        let time = settings[0].time;
        let kat = settings[0].kat;
        let raumgl_set = settings[0].raumgl;
        let klassifizierung = settings[0].klassifizierung;
        let klassenanzahl = settings[0].klassenanzahl;

        $.when(getRasterMap(time, ind, raumgl_set, klassifizierung, klassenanzahl, getArtDarstellung()))
            .done(function (data) {
                let txt = data;
                let x = txt.split('##');

                let legende = x[1];
                let legende_schraffur ="https://maps.ioer.de/cgi-bin/mapserv_dv?map=/mapsrv_daten/detailviewer/mapfiles/mapserv_raster.map&MODE=legend&layer=schraffur&IMGSIZE=150+30";

                let einheit = x[10];
                if(einheit==='proz'){einheit='%'}

                $('#legende_vergleich_i').empty().load(legende, function () {
                    let elements = $(this).find('img');
                    elements.each(function (key, value) {
                        let src = $(this).attr('src');
                        $(this).attr('src', "https://maps.ioer.de" + src);
                    });
                });

                $('.iconlegende_schraffur').load(legende_schraffur, function () {
                    let elements = $(this).find('img');
                    elements.each(function (key, value) {
                        let src = $(this).attr('src');
                        $(this).attr('src', "https://maps.ioer.de" + src);
                    });
                });

                if (einheit.length >= 1) {
                    $('#legende_vergleich_einheit').show().text(' ' + einheit);
                } else {
                    $('#legende_vergleich_einheit').hide();
                }

                $.ajax({
                    type:"GET",
                    url :urlparamter.getURL_RASTER() + "php/histogramm.php?Jahr=" + time + "&Kategorie=" + kat + "&Indikator=" + ind + "&Raumgliederung=" + raumgl_set + "&Klassifizierung=" + klassifizierung + "&AnzKlassen=" + klassenanzahl,
                    success:function(data){
                        $('#histogramm_pic_vergleich').empty().append('<img style="width:100%;" src="'+data+'"/>');
                    }
                });

                $.when(getIndZusatzinformationen(ind,time)).done(function(data){
                    let txt = data;
                    let x = txt.split('||');
                    let zusatzinfo = x[0];
                    let datengrundlage = x[1];
                    if (datengrundlage.length >= 3) {
                        datengrundlage = datengrundlage + "</br>";
                    }
                    let atkis = x[2];
                    $('#indikator_info_text_vergleich').text(zusatzinfo);
                    $('#datengrundlage_content_vergleich').html(datengrundlage + atkis);
                });
            });
    }
    function getSettings(){
        let ind = $('#indicator_ddm_vergleich').dropdown('get value');
        let time = jahre[$('#zeit_slider_vergleich').slider("option", 'value')];
        let raumgl_set = raumgl[$('#raum_slider_vergleich').slider("option", "value")];
        let kat = $('#kat_auswahl_vergleich #'+indikatorauswahl.getSelectedIndikator()+"_item").attr("data-kat");
        let klassifizierung = $('#menu_klassifizierung_vergleich').find('input:checked').val();
        let klassenanzahl = $('#menu_klassenanzahl_vergleich').find('select').val();
        let settings = [];
        settings.push({"kat":kat,"ind":ind,"time":time,"raumgl":raumgl_set,"klassifizierung":klassifizierung,"klassenanzahl":klassenanzahl});
        return settings;
    }
    //events
    $(function(){

        //klassifizierungsmenu
        $('#menu_klassifizierung_vergleich').find('input').change(function () {
           setLegende();
        });

        //change the number of classes
        $('#Klassenanzahl_vergleich').change(function(){
           setLegende();
        });

        //destroy the function
        $('#close_vergleich').find('.destroy').click(function(){
            removeSideBySideControl();
            dialog_container.slideUp();
            button_map.css("background-color",farbschema.getColorMain());
        });

        //close the dialog
        $('#close_vergleich').find('.close').click(function () {
            dialog_container.slideUp();
        });

        $('#create_vergleichskarte_button').click(function(){
            initRaster(null,null,"rechts",getSettings());
            $('#vergleich_dialog').slideUp();
        });

        $("#kennblatt_vergleich").click(function(){
            openKennblatt($('#indicator_ddm_vergleich').dropdown('get value'));
        });
    });
}
//calculate Width and Height of the setted dialog's
function calculateWidth(){
    const width = mainView.getWidth();
    if($('.right_content').is(':visible') || width >=1280 && width<2000){
        return width*0.5;
    }
    else if(width>2000){
        return 1200;
    }
    else{
        return width-50;
    }
}
function calculateHeight(){
    const height = mainView.getHeight();
    if($('.right_content').is(':visible') || height >= 800){
        return height-210;
    }else{
        return height-100;
    }
}

