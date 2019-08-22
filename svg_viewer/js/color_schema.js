//Source Colors: http://colorbrewer2.org/#type=sequential&scheme=PuRd&n=3
var grey = ['f0f0f0','636363'];
var YlOrRd = ['ffeda0','f03b20'];
var YlGnBu = ['edf8b1','2c7fb8'];
var PuRd = ['e7e1ef','dd1c77'];
var map_request_raster;
var request_geojson;

function create_color_schema(){

    $('#color_schema').empty();

    createColorArray(grey,"grey");
    createColorArray(YlOrRd,"YlOrRd");
    createColorArray(YlGnBu,"YlGnBu");
    createColorArray(PuRd,"PuRd");

}

function createColorArray(array,id) {

    var klassenanzahl = $("#Klassenanzahl").val();

    if(map.hasLayer(geojson)) {
        $.ajax({
            type: "GET",
            url: 'php/create_color_schema.php',
            dataType: "html",
            data: {
                colmax_rgb: array[0],
                colmin_rgb: array[1],
                anz_klassen: klassenanzahl,
                id: id
            },
            success: function (data) {
                $('#color_schema').append(data);
            }
        });
    }else{
        $.ajax({
            type: "GET",
            url: 'php/raster/create_color_schema.php',
            dataType: "html",
            data: {
                colmax_rgb: array[0],
                colmin_rgb: array[1],
                anz_klassen: klassenanzahl,
                id: id
            },
            success: function (data) {
                $('#color_schema').append(data);
            }
        });
    }
}

$(function() {

    var click = 0;

    //Listener for the color ranges
    $(document).on('click', "#grey", function () {
        if (click == 0) {
            var content = $(this).html();
            $("#farbwahl_btn").empty().append('<span class="glyphicon glyphicon-remove"></span><div class="color-line">' + content + '</div>');
            //craete the new colored map
            setColorArray(grey);
            click++;
        }else{
            click = 0;
        }
    });

    $(document).on('click', "#YlOrRd", function () {
        if (click == 0) {
            var content = $(this).html();
            $("#farbwahl_btn").empty().append('<span class="glyphicon glyphicon-remove"></span><div class="color-line">' + content + '</div>');
            //craete the new colored map
            setColorArray(YlOrRd);
            click++;
        }else{
            click = 0;
        }
    });

    $(document).on('click', "#YlGnBu", function () {
        if (click == 0) {
            var content = $(this).html();
            $("#farbwahl_btn").empty().append('<span class="glyphicon glyphicon-remove"></span><div class="color-line">' + content + '</div>');
            //craete the new colored map
            setColorArray(YlGnBu);
            click++;
        }else{
            click = 0;
        }
    });

    $(document).on('click', "#PuRd", function () {
        if (click == 0) {
            var content = $(this).html();
            $("#farbwahl_btn").empty().append('<span class="glyphicon glyphicon-remove"></span><div class="color-line">' + content + '</div>');
            //craete the new colored map
            setColorArray(PuRd);
            click++;
        }else{
            click = 0;
        }
    });

    //reset to standard range
    $("#farbwahl_btn").click(function(){
        if(click === 1){
            $("#farbwahl_btn").empty().append('Bitte Wählen..<span class="caret"></span>');
            if(map.hasLayer(raster)){
                holeKarte_links()
            }
            else if(map.hasLayer(raster_rechts)){
                holeKarte_links();
                holeKarte_rechts();
            }else{
                holeKarteGeoJSON();
            }
            click =0;
        }
    });
});

function setColorArray(array){
    if(map.hasLayer(geojson)){
        holeKarteGeoJSON(null,array[0],array[1]);
    }
    else if(map.hasLayer(raster_rechts)){
        holeKarte_links(array[0],array[1]);
        holeKarte_rechts(array[0],array[1]);
    }else{
        holeKarte_links(array[0],array[1]);
    }
}
function getColorActive(){
    return '#8CB91B';
}
function getColorMain(){
    return '#4E60AA';
}