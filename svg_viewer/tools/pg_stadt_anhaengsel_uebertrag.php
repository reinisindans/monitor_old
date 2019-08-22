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
	Fortschritt der Übertrag<br />
	<?php 

    
	// Erfassen aller Tabelleneinträge
	$SQL_PostGIS = "SELECT gen,ags FROM vg250_krs_".$_POST['zeitschnitt']."_old WHERE gen LIKE '%(Stadt)';";
	$ERGEBNIS_PGSQL_AGS =  pg_query($Verbindung_PostgreSQL,$SQL_PostGIS);
	
	// gefundene Datensätze abarbeiten
	$i=0;
	while($PG_Zeile = @pg_fetch_assoc($ERGEBNIS_PGSQL_AGS,$i))
	{					
		$gen = $PG_Zeile['gen'];
		$PSQL = "UPDATE vg250_krs_".$_POST['zeitschnitt']." SET gen='".$gen."' WHERE ags ='".$PG_Zeile['ags']."'";
		if($ERGEBNIS_PGSQL_UPD = pg_query($Verbindung_PostgreSQL,$PSQL)) echo "<br />".$gen."ok";
		
		$i++;
	}	
}

?>	
	<form action="" method="post">
    <br />
	<br />
	Übertrag von (Stadt) in neue vg250_krs_<input name="zeitschnitt" type="text" /> aus Tabelle ..._old
	<br />
    
    <input name="aktion" type="hidden" value="1" />
	<input name="" type="submit" value="Senden" />
	</form>

</body>
</html>
