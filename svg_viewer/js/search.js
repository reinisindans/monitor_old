var map;
var url;
var geojson;
var options;
var steps;

$(function(){
    //css settings for result
    $('.results').css({'max-height':$('#toolbar').height()-50,'overflow-y':'auto', 'overflow-x':'hidden'});

    $('.ui.search')
        .search({
            type          : 'category',
            minCharacters : 2,
            error: {
                noResults   : 'Kein Ergebnis für die Suchanfrage',
                serverError : 'Es gab ein Problem mit dem Server'
            },
            cache: false,
            apiSettings   : {
                onResponse: function(Response) {
                    console.log(Response);
                       var
                        response = {
                            results : {}
                        }
                    ;
                    // translate GitHub API response to work with search
                    $.each(Response.results, function(index, item) {
                        var
                            language   = item.category || 'Unknown',
                            maxResults = 15
                        ;
                        if(index >= maxResults) {
                            return false;
                        }
                        // create new category
                        if(response.results[language] === undefined) {
                            response.results[language] = {
                                name    : language,
                                results : []
                            };
                        }
                        // add result to category
                        response.results[language].results.push({
                            title       : item.titel,
                            description : item.description,
                            value         : item.value,
                            category: item.category
                        });
                    });
                    return response;
                },
                url: 'php/search.php',
                method: 'GET',
                dataType: 'json',
                data:{
                    option:'all',
                    q: function(){
                        //create the expression
                        var value = $('#search_input_field').val().toLocaleLowerCase();
                        return value.replace('-',' ');
                    }
                },
                cache:false
            },
            onSelect: function(result,response){
                var cat = result.category;
                if(cat === 'Indikatoren'){
                    indikatorauswahl.checkAvability(result.value,true);
                }else if(cat ==='Orte'){
                    var lat = result.value[0];
                    var lon = result.value[1];
                    var title = "<b>"+result.title+"</b></br>"+result.description;
                    indikatorJSON.setMarker(lon,lat,title);
                }
                setTimeout(function(){
                    $('#search_input_field').val('');
                    $('.ui.search').search('hide results');
                },500);
            }
        });
});