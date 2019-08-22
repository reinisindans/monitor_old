		<?php  
//API abfragen nach ersten 100 Einträgen, zwischenspeichern auf Server
$curl = curl_init();
curl_setopt_array($curl, array(
//  CURLOPT_URL => "https://api.zotero.org/groups/2176615/items/top?v=3&key=O7QrwNuRqQJOB0liItE4KXWw&format=biblatex&limit=100&start=0",
CURLOPT_URL => "https://api.zotero.org/groups/2176615/items/?key=O7QrwNuRqQJOB0liItE4KXWw&format=json&include=biblatex,data&limit=100&start=0",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "GET",
  CURLOPT_HTTPHEADER => array(
    "cache-control: no-cache"
  ),
));
$response = curl_exec($curl);
$err = curl_error($curl);
curl_close($curl);
$replacement = ",";
$response = substr($response, 0, -1).$replacement;//remove last character(]), replace with ,

$file = "./data/documents.json";
file_put_contents($file, $response); //überschriebt alten Stand


//API abfragen nach Einträgen 101-200, zwischenspeichern auf Server
$curl = curl_init();
curl_setopt_array($curl, array(
 // CURLOPT_URL => "https://api.zotero.org/groups/2176615/items/top?v=3&key=O7QrwNuRqQJOB0liItE4KXWw&format=biblatex&limit=100&start=100",
 CURLOPT_URL => "https://api.zotero.org/groups/2176615/items/?key=O7QrwNuRqQJOB0liItE4KXWw&format=json&include=biblatex,data&limit=100&start=100",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "GET",
  CURLOPT_HTTPHEADER => array(
    "cache-control: no-cache"
  ),
));
$response = curl_exec($curl);
$err = curl_error($curl);
curl_close($curl);
$response = substr($response,1); //lösche erstes Zeichen [
//$response = substr($response, 0, -1).$replacement;//remove last character(]), replace with ,
$file = "./data/documents.json";
file_put_contents($file, $response, FILE_APPEND); //anhängen an erste Einträge


/*
//API abfragen nach Einträgen 201-300, zwischenspeichern auf Server
$curl = curl_init();
curl_setopt_array($curl, array(
 // CURLOPT_URL => "https://api.zotero.org/groups/2176615/items/top?v=3&key=O7QrwNuRqQJOB0liItE4KXWw&format=biblatex&limit=100&start=200",
 CURLOPT_URL => "https://api.zotero.org/groups/2176615/items/?key=O7QrwNuRqQJOB0liItE4KXWw&format=json&include=biblatex,data&limit=100&start=200",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "GET",
  CURLOPT_HTTPHEADER => array(
    "cache-control: no-cache"
  ),
));
$response = curl_exec($curl);
$err = curl_error($curl);
curl_close($curl);
$response = substr($response,1); //lösche erstes Zeichen [
$response = substr($response, 0, -1).$replacement;//remove last character(]), replace with ,
$file = "./data/documents.json";
file_put_contents($file, $response, FILE_APPEND);

//API abfragen nach Einträgen 301-400, zwischenspeichern auf Server
$curl = curl_init();
curl_setopt_array($curl, array(
//  CURLOPT_URL => "https://api.zotero.org/groups/2176615/items/top?v=3&key=O7QrwNuRqQJOB0liItE4KXWw&format=biblatex&limit=100&start=300",
CURLOPT_URL => "https://api.zotero.org/groups/2176615/items/?key=O7QrwNuRqQJOB0liItE4KXWw&format=json&include=biblatex,data&limit=100&start=300",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "GET",
  CURLOPT_HTTPHEADER => array(
    "cache-control: no-cache"
  ),
));
$response = curl_exec($curl);
$err = curl_error($curl);
curl_close($curl);
$response = substr($response,1); //lösche erstes Zeichen [
$file = "./data/documents.json";
file_put_contents($file, $response, FILE_APPEND);
*/

//So sind im Moment 400 Einträge im IÖR-Flächenportal möglich. Bei Bedarf nach weiteren, einfach die Funktionen erneut ausführen

	?>
