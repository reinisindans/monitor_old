/****Funktion für Kopieren von Code (Bibtexraw) in Zwischenablage */
function CopyToClipboard(containerid) {
	console.log(containerid);
if (document.selection) { 
    var range = document.body.createTextRange();
    range.moveToElementText(document.getElementById(containerid));
    range.select().createTextRange();
    document.execCommand("copy"); 

} else if (window.getSelection) {
    var range = document.createRange();
     range.selectNode(document.getElementById(containerid));
     window.getSelection().addRange(range);
     document.execCommand("copy");
     //alert("Code wurde in Zwischenablage gespeichert.") 
     containerid2= "hinweiscopy" + containerid;
     	console.log(containerid2);
     document.getElementById(containerid2).textContent = "Code erfolgreich in Zwischenablage gespeichert.";
}}
/*****Ende Kopie Zwischenablage */


/****Funktionen für Suche****/
	
//Reset Searchbar
		function reset() {

			$("select").each(function () {
			  localStorage.setItem($(this).attr("id"),"");
			  $(this).val("");
			}); 
			$("#searchbar").val("");
			$("#searchbar").trigger('change');
		}			



/********Suche von anderer Seite aus: Wenn Suchfeld abgeschickt wird, nehme dessen value und gebe es als Input in Such-Formular**********/
$(function() {
			$('#searchForm').submit(function () {
                var input = String(document.getElementById("inputSearch").value);
              
                if (input != ""){
                	 location.href = "./suche.php?q="+input;
			              $(function(input) {
			                	document.getElementById("searchbar").value = input;			                  	
                   	});                   
                    return false;
                }
      });
});
/********Ende Suche von anderer Seite aus*********/



/****Suche: Dropdown-Liste für Keywords, todo: alphabetische sortierung****/
function keywordsList(object) {
  var map = new Object();
  $("span.keywords").each(function(i, obj) {
  
  	arrayString = $(this).text().split(new RegExp(",[\\s]+and[\\s]+|,[\\s]+"));

	  	for (i = 0; i < arrayString.length; i++) {
	  	  if(arrayString[i] in map) {
	  		map[arrayString[i]] += 1;
	  	  } else {
	  		map[arrayString[i]] = 1;
	  	  }
	  	}  	
  }); 	 
      
  var tuples = [];
  for (var key in map) tuples.push([key, key.split(" ").pop().toLowerCase()]);
//Sortieren aller Keywords nach alphabet
  tuples.sort(function(a, b) {
    a = a[1];
     b = b[1];
    return a < b ? -1 : (a > b ? 1 : 0);
  });

//Keywords an Leerzeichen auftrennen und mit Komma umsortieren- nicht nötig
  for (var i = 0; i < tuples.length; i++) {
    var key = tuples[i][0];
    var value = tuples[i][1];    
    var array = key.split(" "); 
    var text = array.join(" "); //alle Elemente des Arrays als sting hintereinander auflisten, mit Leerzeichen dazwischen
   // var text = array.pop()+", "+array.join(" "); //pop() löscht letztes Element des arrays, dieses wird an Anfang gestellt, getrennt mit Komma
	object.append($("<option></option>").attr("value",key).text(text)); //alle umgestellten Keywords als Options in Dropdown einfügen	
  }
}
/****Ende: suche /Liste für Keywords****/

/****Ende Funktionen für Suche****/
