<?php 

error_reporting(E_ERROR | E_WARNING | E_PARSE);

/* PostgreSQL_ODBC_Verbindung */ 
//$PostGIS_ODBC = odbc_connect("PostgreSQL35W","postgres","jojojo");

// Spracheinstellung
// $_SESSION['Dokument']['Sprache'] = '';
if(!$_SESSION['Dokument']['Sprache']) $_SESSION['Dokument']['Sprache'] = 'DE';
if($_GET['lang']) $_SESSION['Dokument']['Sprache'] = $_GET['lang'];

/* MySQL Verbindung */
//$Verbindung = mysql_connect("localhost","root","");
//$Verbindung = mysql_pconnect("localhost","monitor_svg","monitor_svguser"); // peresistente Verbindung wird nicht nach jedem Script getrennt!
//mysql_select_db("monitor_svg", $Verbindung);
//mysql_set_charset('utf8', $Verbindung);

// Neuere Verbindungsmethode für neuere PHP Versionen



   $user = 'monitor_svg';
   $pass = 'monitor_svguser';
   $dbName = 'monitor_svg';
   $host = '127.0.0.1 ';  //127.0.0.1   192.9.200.43  localhost

   $Verbindung = mysqli_init();
   // mysqli_options($Verbindung,MYSQLI_OPT_LOCAL_INFILE);
   mysqli_real_connect($Verbindung,$host, $user, $pass,$dbName) 
                  or die ('<P>Unable to connect</P>');
	// Auf Latin1 setzen, um EInstellung der alten Serverconfig zu imitieren
   $Verbindung->set_charset("latin1");
   
// Funktion nicht mehrfach in einem Script starten
if(!function_exists('mysqli_result'))
{
	function mysqli_result($res, $row, $field=0) 
	{ 
		if($res)
		{
			$res->data_seek($row); 
			$datarow = $res->fetch_array(); 
			return $datarow[$field]; 
		}
		else
		{
			return $t='';	
		}
		
	} 

}

	/* change character set to utf8
if (!$Verbindung->set_charset("latin1")) {
    printf("Error loading character set utf8: %s\n", $Verbindung->error);
} else {
    printf("Current character set: %s\n", $Verbindung->character_set_name());
}
	
	




// Test
$result = $Verbindung->query("SELECT * FROM m_thematische_kategorien ORDER BY SORTIERUNG_THEMA_KAT");

echo "Hier: ";
echo mysqli_result($result,0,'THEMA_KAT_NAME');

$row = $result->fetch_assoc();
echo " ... ".$row['THEMA_KAT_NAME'];

echo "<br />Zeichensatz:".mysqli_client_encoding($Verbindung)."<br />";
echo " Ende"; 

*/

$Verbindung_PostgreSQL = pg_connect("host=localhost port=5432 dbname=monitor_geodat user=monitor_svg password=monitor_svguser options='--client_encoding=UTF8'") or die('Could not connect: ' . pg_last_error()) ;




?>
