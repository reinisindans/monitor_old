//no indicator chosen
function alertOldBrowser(){
    $('#Modal').hide();
    swal(
        "Alte Version des Internet Explorers",
        "Sie verwenden eine veraltete Version des Internet Explorers, diese wird nicht unterstützt.",
        "error"
    );

}
function alertNoIndicatorChosen(){
    setTimeout(function(){
        swal(
            "Kein Indikator gewählt",
            "Bitte wählen Sie erst einen Indikator aus",
            "info"
        )
    },500);
}
//first init
function alertWebTour(){
    legende.close();
    startMap.set();
    swal({
            title: 'Herzlich willkommen in der neuen Ansicht des IÖR-Monitors',
            text: "Wir möchten Sie zu einer Tour durch die Inhalte und Funktionen der Anwendung einladen.",
            showCancelButton: true,
            imageUrl: 'images/kopf_v2.png',
            imageWidth: 200,
            html: true,
            imageHeight: 200,
            confirmButtonColor: '#006dcc',
            cancelButtonColor: '#ff3300',
            confirmButtonText: 'Starten der Tour',
            cancelButtonText: "Abbrechen"
        },
        function (isConfirm) {
            if (isConfirm) {
                progressbar.remove();
                StartWebTour();
            }
        });
}
//error messages
function alertError(){
    setTimeout(function(){
        swal(
            "Es ist ein Problem aufgetreten",
            "Bitte versuchen Sie es später nochmal oder kontaktieren Sie uns über das Feedback Formular.",
            "error"
        );
        progressbar.remove();
    },500);
}
//info messages
function alertOneTimeShift(){
    $.when(
    setTimeout(function(){
        swal({
            title: "Der Indikator steht nur für den Zeitschnitt " + zeitslider.getTimeSet() + " zur Verfügung.",
            text: "Aus diesem Grund entfällt der Zeitslider.",
            type: "info"
        },
            function (isConfirm) {
                if (isConfirm) {
                    zeitslider.hide();
                }
            }
        );
    },500));
}
function alertRelief(){
    setTimeout(function(){
        swal(
            "Der Indikator ist unabhängig der Zeitschnitte.",
            "Aus diesem Grund entfällt der Zeitslider.",
            "info"
        );
    },500);
}
function alertNotInTimeShift(){
    console.log("alertNotInTimeShift");
    setTimeout(function () {
        swal(
            'Der Indikator ist im gewählten Zeitschnitt nicht vorhanden',
            'Für den Indikator ' + $('#Indikator option:selected').text() + ' wurde das Jahr auf ' + Math.max.apply(Math, indikatorauswahl.getFilteredPossibleYears()) + ' angepasst',
            'success'
        );
    }, 500);
}
function alertNotinSpatialRange(raumglTXT,selection){
    console.log("alert not in spatial range");
    $.when(urlparamter.removeUrlParameter('raumgl_fein'))
        .then(progressbar.remove())
        .then(setTimeout(function () {
                swal({
                    title: 'Der Indikator ist in der gewählten Raumgliederung nicht vorhanden.',
                    text: 'Es wäre möglich den Indikator auf die Raumgliederung ' + raumglTXT + ' anzupassen',
                    type: 'info',
                    cancelButtonText: "Abbrechen",
                    showCancelButton: true,
                },
                    function (isConfirm) {
                        if (isConfirm) {
                            $.when(urlparamter.removeUrlParameter('ags_array'))
                                .then(urlparamter.updateURLParameter('raumgl',selection))
                                .then($('#dropdown_grenzen_container').dropdown('clear'))
                                .then(hideRaumgl_MenuFein())
                                .then(indikatorJSON.init(selection))
                                .then(setHeader());
                        }else{
                            indikatorauswahl.setIndicator(indikatorauswahl.getPreviousIndikator());
                        }
                    }
                );
            }, 500));
}
function alertNotAsRaster(){
    console.log("alert NotasRaster");
    $.when(setTimeout(function(){
                swal({
                    title: "Der Indikator ist nicht in der Räumlichen Gliederung verfügbar",
                    text: "Möchten Sie sich den Indikator trotzdem visualisieren ? ",
                    type: "warning",
                    cancelButtonText: "Abbrechen",
                    showCancelButton: true,
                },
                function(isConfirm){
                    if (isConfirm) {
                        if(raeumlicheauswahl.getRaeumlicheGliederung()==='raster'){
                            $('#spatial_choice_checkbox_container').checkbox('uncheck');
                        }else{
                            $('#spatial_choice_checkbox_container').checkbox('check');
                        }
                    }
                }
                );
            },500));
}
function alertServerlast(choice){
    setTimeout(function(){
        swal({
            title: "Erhöhte Belastung",
            text: "Bei der jetzigen Auswahl wird eine erhöhte Rechenlast an den Browser und unserem Server gestellt, deshalb kann es zu Verzögerungen bei den Interaktionen kommen. " +
            "Sie können durch eine Verfeinerung ihrer Auswahl, wie beispielsweise die Wahl eines Bundeslandes den Prozess beschleunigen.",
            type: "warning",
            cancelButtonText: "Abbrechen",
            showCancelButton: true,
        },
            function (isConfirm) {
                if (isConfirm) {
                    $.when(urlparamter.updateURLParameter('raumgl',choice))
                        .then($('#dropdown_datenalter').hide())
                        .then(setHeader(getRaumgliederung_text()+" in Deutschland"))
                        .then(indikatorJSON.init())
                        .then(rightView.close());
                }else{
                    $('#'+getRaumgliederungID()+"_raumgl").prop("selected",true);
                }
            }

        )
    },500);
}