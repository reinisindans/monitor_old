var apikey = "O7QrwNuRqQJOB0liItE4KXWw";
var user = "4501007";
var groups= "2176615";

//var baseURL = "https://api.zotero.org/users/"+user+"/items/";
var baseURL = "https://api.zotero.org/groups/"+groups+"/items/";
var baseParam = "?key="+apikey;


function get_data_for_tag() {

	var id = $("#citations").text();
	$("#citations").html("");

	var url = baseURL+baseParam+"&tag="+id+"&format=json&include=bib&sort=creator";

	$.get(url, null, function(data, status) {
		if(status == "success") {
			$.each(data, function(index, entry) {
				var citation = entry['bib'];
				var zoterokey = entry['key'];
				$().add(citation).attr("entry", zoterokey).appendTo("#citations");
			});

			$("#citations div").each(function(index, element) {
				var url2 = baseURL+$(element).attr("entry")+"/children"+baseParam+"&format=json&include=data";

				$.get(url2, null, function(data, status) {
					$.each(data, function(index, note) {
						$().add("<p>"+note['data']['note']+"</p>").css("margin-left", "2em").appendTo(element);
					});
				});
			});
		}
	});
}

$(document).ready(get_data_for_tag);