$(function storage_position(){

    map.on('moveend',         function () {

        var centerPoint = map.getSize().divideBy(2),
            targetLatLng = map.containerPointToLatLng(centerPoint);

        urlparamter.updateURLParameter('lat',targetLatLng.lat);
        urlparamter.updateURLParameter('lng',targetLatLng.lng);

    });
    map.on('zoomend',function () {

        var zoom = map.getZoom();
        urlparamter.updateURLParameter('zoom',zoom);

    });
});
function setMapView(lat,lng,zoom){
    var zoom_set,lat_set,lng_set;

    //Zoom
    if(!zoom){
        var zoom_param = urlparamter.getUrlParameter('zoom');
        if(!zoom_param) {
            urlparamter.setUrlParameter('zoom', 8);
            zoom_set = 8;
        }else{
            zoom_set = zoom_param;
        }
    }else{
        urlparamter.updateURLParameter('zoom',zoom);
        zoom_set = zoom;
    }
    //lat
    if(!lat){
        var lat_param = urlparamter.getUrlParameter('lat');
        if(!lat_param) {
            urlparamter.setUrlParameter('lat', 50.9307);
            lat_set = 50.9307;
        }else{
            lat_set = lat_param;
        }
    }else{
        urlparamter.updateURLParameter('lat',lat);
        lat_set = lat;
    }
    //lng
    if(!lng){
        var lng_param = urlparamter.getUrlParameter('lng');
        if(!lng_param){
            urlparamter.setUrlParameter('lng',9.7558);
            lng_set = 9.7558;
        }else{
            lng_set = lng_param;
        }
    }else{
        urlparamter.updateURLParameter('lng',lng);
    }
    map.setView(new L.LatLng(lat_set, lng_set), zoom_set);
    //initializeFirstView the layer if set
    baselayer.init();
    zusatzlayer.init();
}

/**
 * @return {string}
 */
function DotToComma(int){
    return int
            .toString()
            .replace(/\./g, ',');
}

function checkOverlap(ele1,ele2){

    var coverElem = ele2;
    var elemArray = [];
    elemArray.push(ele1,ele2);

    var state;

    for(var i=0; i< elemArray.length; i++)
    {
        var currElemOffset = elemArray[i].offset();
        var currElemWidth = elemArray[i].width();

        var currElemStPoint = currElemOffset.left ;
        var currElemEndPoint = currElemStPoint + currElemWidth;
        state = currElemStPoint <= coverElem.offset().left && coverElem.offset().left <= currElemEndPoint;
    }

    return state;
}
function removefromarray(array,value_remove){
    var array_cleaned = [];
    $.each(array,function(key,value){
        if(value.id != value_remove){
            //must seperate time values
            if(value.id === indikatorauswahl.getSelectedIndikator()){
                if(value.time != value_remove){
                    array_cleaned.push(value);
                }
            }else{
                array_cleaned.push(value);
            }
        }
    });

    return array_cleaned;
}
function uniqueArray(list) {
    var result = [];
    $.each(list, function(i, e) {
        if ($.inArray(e, result) == -1) result.push(e);
    });
    return result;
}
function getMaxArray(array, propName){
    return Math.max.apply(Math, array.map(function(i) {
        return i[propName];
    }));
}
function getMinArray(array, propName){
    return Math.min.apply(Math, array.map(function(i) {
        return i[propName];
    }));
}
function getMobileState(){
    if(mainView.getWidth()<700){
        return true;
    }else{
        return false;
    }
}
function getCurrentYear(){
    return (new Date).getFullYear();
}
function setGlaettungModus(darstellung){
    var darstellung_param = urlparamter.getUrlParameter('glaettung');
    if(!darstellung_param){
        urlparamter.setUrlParameter('glaettung',darstellung);
    }else{
        urlparamter.updateURLParameter('glaettung',darstellung);
    }
}
function getGlaettungModus(){
    if(!urlparamter.getUrlParameter('glaettung')){
        urlparamter.setUrlParameter('glaettung',0);
    }
    return urlparamter.getUrlParameter('glaettung');
}
function enableElement(elem,title){
    $(elem)
        .prop('title',title)
        .prop('disabled',false)
        .css('cursor','pointer');
}
function disableElement(elem,text) {
    $(elem)
        .prop('title', text)
        .prop('disabled', true)
        .css('cursor', 'not-allowed');
}
function highlightElementByID(id,color){
    console.log("highlight element");
    let color_set = farbschema.getColorActive();
    if(color){
        color_set = color;
    }
    $('#'+id).css("border","2px solid "+color_set);
}
function resetHighlightElementByID(id){
    $('#'+id).css("border","");
}
function slideDownElementByID(id){
    $('#'+id).slideDown("slow", function () {});
    return true;
}
function slideUpElementByID(id){
    $('#'+id).slideUp("slow", function () {});
    return true;
}
function get_browser() {
    var ua=navigator.userAgent,tem,M=ua.match(/(opera|chrome|safari|firefox|msie|trident(?=\/))\/?\s*(\d+)/i) || [];
    if(/trident/i.test(M[1])){
        tem=/\brv[ :]+(\d+)/g.exec(ua) || [];
        return {name:'IE',version:(tem[1]||'')};
    }
    if(M[1]==='Chrome'){
        tem=ua.match(/\bOPR|Edge\/(\d+)/)
        if(tem!=null)   {return {name:'Opera', version:tem[1]};}
    }
    M=M[2]? [M[1], M[2]]: [navigator.appName, navigator.appVersion, '-?'];
    if((tem=ua.match(/version\/(\d+)/i))!=null) {M.splice(1,1,tem[1]);}
    return {
        name: M[0],
        version: M[1]
    };
}
