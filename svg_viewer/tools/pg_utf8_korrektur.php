<?php 
ini_set('max_execution_time', '500');

include("../includes_classes/verbindung_mysqli.php");
include('../includes_classes/progressbar/progressbar.class.php');


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Unbenanntes Dokument</title>
</head>

<body>

<?php
if($_POST['aktion'])
{
	?>
    <br />
	<br />
	<br />
	Fortschritt der Korrektur<br />
	<?php 

    
	// Erfassen aller Tabelleneinträge
	$SQL_PostGIS = "SELECT gen,ags FROM vg250_".$_POST['raumebene']."_".$_POST['zeitschnitt'];
	$ERGEBNIS_PGSQL_AGS =  pg_query($Verbindung_PostgreSQL,$SQL_PostGIS);
	
	$AnzDatensätze = pg_num_rows($ERGEBNIS_PGSQL_AGS);
	// Fortschrittsbalken als Objekt erstellen
	$myprogressbar = new progressbar(0, $AnzDatensätze, 200, 20);
	
	// Fortschrittsbalken Anzeige
	$myprogressbar->print_code();
	
	// gefundene Datensätze abarbeiten
	$i=0;
	while($PG_Zeile = @pg_fetch_assoc($ERGEBNIS_PGSQL_AGS,$i))
	{				
		// Fortschrittsbalken ein Step weiter
		$myprogressbar->step();
		
		$gen = utf8_decode(utf8_decode(utf8_encode($PG_Zeile['gen'])));
		$PSQL = "UPDATE vg250_".$_POST['raumebene']."_".$_POST['zeitschnitt']." SET gen='".$gen."' WHERE ags ='".$PG_Zeile['ags']."'";
		$ERGEBNIS_PGSQL_UPD = @pg_query($Verbindung_PostgreSQL,$PSQL); 
		// es Können Fehler im Format auftreten, die aber nur zum Nicht-Update des Datensatzes führen
		// Umlautfehler werden jedoch gut korrigiert :)
		$i++;
	}	
	?>
    <br />
	<br />
	Weitere Korrekturen durchführen?<br />
	<br />
	<br />
	<?php 
}

?>	
	<br />
	<br />

	Bitte die Informationen für die UTF8-Korrektur ergänzen:<br />
	<br />

	<form action="" method="post">
	Raumebene<br />
	<input name="raumebene" type="text" />
	<br />
	<br />
	
	Zeitschnitt<br />
	<input name="zeitschnitt" type="text" />
	<br />
	<br />
    <input name="aktion" type="hidden" value="1" />
	<input name="" type="submit" value="Senden" />
	</form>

</body>
</html>
