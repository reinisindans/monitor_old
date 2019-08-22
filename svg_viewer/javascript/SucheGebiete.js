/*nach http://stackoverflow.com/questions/22542696/search-only-display-matching-options-in-a-select-multi-list*/


//dynamisches Ein- und Ausblenden von Optionen des mit "selectionE1" übergebenen <select>menüs (siehe unten)
var showOnlyOptionsSimilarToText = function (selectionEl, str, isCaseSensitive) {
    if (typeof isCaseSensitive == 'undefined')
        isCaseSensitive = true;
    if (isCaseSensitive)
        str = str.toLowerCase();

    var $el = $(selectionEl);

    $el.children("option:selected").removeAttr('selected');
    $el.val('');
    $el.children("option").hide();

    $el.children("option").filter(function () {
        var text = $(this).text();
        if (isCaseSensitive)
            text = text.toLowerCase();

        if (text.indexOf(str) > -1)
            return true;

        return false;
    }).show();

};

//Bei Eingabe in <input> mit id "Searchbox" suche passende Optionen aus <select> mit id "DatensaetzeGewählt"
$(document).ready(function () {
	var timeout;
	$("#SearchBox").on("keyup", function () {
		var userInput = $("#SearchBox").val();
		window.clearTimeout(timeout);
		timeout = window.setTimeout(function() {
			showOnlyOptionsSimilarToText($("#DatensaetzeGewaehlt"), userInput, true);
		}, 500);

	});
});