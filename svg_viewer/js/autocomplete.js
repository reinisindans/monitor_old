var map;
var url;
var geojson;
var options;
var geo_marker;
var steps;

//the highlighting part
//Quelle: http://stackoverflow.com/questions/2435964/jqueryui-how-can-i-custom-format-the-autocomplete-plug-in-results
function monkeyPatchAutocomplete() {
    $.ui.autocomplete.prototype._renderItem = function( ul, item) {
        var re = new RegExp("^" + this.term) ;
        var t = item.label.replace(re,"<span style='font-weight:bold;color:#4E60AA;'>" +
            this.term +
            "</span>");
        return $( "<li></li>" )
            .data( "item.autocomplete", item )
            .append( "<a>" + t + "</a>" )
            .appendTo( ul );
    };
}

$(document).ready(function(){

    monkeyPatchAutocomplete();

    $('#search_input_orte').autocomplete({
        source: function( request, response ) {
           $.ajax({
                type: "GET",
                url: "php/geonames.php",
                dataType: "json",
                data: {
                    'q': $('#search_input_orte').val(),
                    'name_startsWith': request.term
                },
                success: function( data ) {
                    console.log(data);
                    response( $.map( _.uniq(data.geonames, false, function(o){return o.name}), function( item ) {
                        return {
                            label: item.name,
                            coordinates: item.lat + ", "+item.lng
                        }
                    }));
                }
            });
        },
        minLength: 1,
        select: function( event, ui ) {
            var split = ui.item.coordinates.split(",");
            var lat =parseFloat(split[0]);
            var lng = parseFloat(split[1]);
            setMarker(lat,lng);
            toolbar_toggle();
            header_slide();
        },
        open: function() {
            $( this ).removeClass( "ui-corner-all" ).addClass( "ui-corner-top" );
        },
        close: function() {
            $( this ).removeClass( "ui-corner-top" ).addClass( "ui-corner-all" );
        }
    });

    function setMarker(lat,lng){
        if(map.hasLayer(geo_marker)) {
            map.removeLayer(geo_marker);
        }
        geo_marker = L.marker([lat, lng]).addTo(map);
        map.setView(new L.LatLng(lat, lng),15);
    }


    //Qelle: http://jqueryui.com/autocomplete/#categories
    $.widget( "custom.catcomplete", $.ui.autocomplete, {

        _create: function() {
            this._super();
            this.widget().menu( "option", "items", "> :not(.ui-autocomplete-category)" );
        },
        _renderMenu: function( ul, items ) {
            var self = this,
                currentCategory = "";
            $.each( items, function( index, item ) {
                var li;
                if ( item.category != currentCategory ) {
                    ul.append( "<li class='ui-autocomplete-category'><b>" + item.category + "</b></li>" );
                    currentCategory = item.category;
                }
                li = self._renderItemData( ul, item );
                if ( item.category ) {
                    li.attr( "aria-label", item.category + " : " + item.label );
                }
            });
        },
        _renderItem: function( ul, item) {
            var re = new RegExp("^" + this.term) ;
            var t = item.label.replace(re,"<span style='font-weight:bold;color:Blue;'>" +
                this.term +
                "</span>");
            return $( "<li></li>" )
                .data( "item.autocomplete", item )
                .append( "<a>" + t + "</a>" )
                .appendTo( ul );
        }
    });

    $('#search_input_indikatoren').catcomplete({
        source: function( request, response ) {
            $.ajax({
                type: "GET",
                url: "php/autocomplete_indikatoren_grenzen.php",
                dataType: "json",
                data: {
                    's_string': $('#search_input_indikatoren').val()
                },
                success: function( data ) {
                    response( $.map(data, function( item ) {
                        return {
                            label: item.INDIKATOR_NAME_KURZ,
                            id_indikator: item.ID_INDIKATOR,
                            id_kat: item.ID_THEMA_KAT,
                            category: item.THEMA_KAT_NAME
                        }
                    }));
                }
            });
        },
        minLength: 1,
        select: function( event, ui ) {
            $("#search_input_indikatoren").val(' ');
            closeTabelleErweiternPanel();
            var ind_bez = ui.item.id_indikator;
            //LoadMenuByIndicator(ind_bez);
            $('#indicator_ddm').dropdown('set selected',ind_bez);
            toolbar_toggle();
            header_slide();
        },
        open: function() {
            $( this ).removeClass( "ui-corner-all" ).addClass( "ui-corner-top" );
        },
        close: function() {
            $( this ).removeClass( "ui-corner-top" ).addClass( "ui-corner-all" );
        }
    });
});