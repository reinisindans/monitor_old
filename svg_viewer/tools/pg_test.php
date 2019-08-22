<?php 
ini_set('max_execution_time', '500');

// include("../includes_classes/verbindung_mysqli.php");

$Verbindung_PostgreSQL = pg_connect("host=localhost port=5432 dbname=monitor_geodat user=monitor_svg password=monitor_svguser");

include('../includes_classes/progressbar/progressbar.class.php');


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Unbenanntes Dokument</title>
</head>

<body>

    <br />
	<br />
	<br />
	Fortschritt<br />
	<?php 

    
	// Erfassen aller Tabelleneinträge
	$SQL_PostGIS = "SELECT AsSvg(the_geom,1) AS geometrie,box2d(the_geom) AS bbox,gen,ags FROM vg250_bld_2008";
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
		
		echo "<br />".$gen = $PG_Zeile['gen']." ".substr($PG_Zeile['geometrie'],0,50)."..., BBox2D: ".$PG_Zeile['bbox'];
		//$PSQL = "UPDATE vg250_".$_POST['raumebene']."_".$_POST['zeitschnitt']." SET gen='".$gen."' WHERE ags ='".$PG_Zeile['ags']."'";
		//$ERGEBNIS_PGSQL_UPD = @pg_query($Verbindung_PostgreSQL,$PSQL); 
		// es Können Fehler im Format auftreten, die aber nur zum Nicht-Update des Datensatzes führen
		// Umlautfehler werden jedoch gut korrigiert :)
		$i++;
	}	
	
$SQL_PostGIS = "SELECT MIN(xmin(box3d(the_geom))) AS x_min,MIN(ymin(box3d(the_geom))) AS y_min, MAX(xmax(box3d(the_geom))) AS x_max, MAX(ymax(box2d(the_geom))) AS y_max FROM vg250_krs_2008 WHERE gid >= '0'  AND (AGS LIKE '07131%' )";
$ERGEBNIS_PGSQL_AGS =  pg_query($Verbindung_PostgreSQL,$SQL_PostGIS);
	
	
$PG_Zeile = @pg_fetch_assoc($ERGEBNIS_PGSQL_AGS,0);
echo "<br />".$PG_Zeile['x_min']." ".$PG_Zeile['y_min']." ".$PG_Zeile['x_max']." ".$PG_Zeile['y_max']." ";			
	
	

	?>
    <br />
	<br />


</body>
</html>
