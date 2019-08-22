<?php 
set_time_limit(0); // Laufzeit aus unendlich setzen (Programm kann u.U. 10h dauern)
include("../includes_classes/verbindung_mysqli.php");

$Jahr = $_POST['Jahr'];
$Raumebenenname = $_POST['Raumebenenname'];

if($Raumebenenname == "Bundesland")  $Raumebene = "bld";
if($Raumebenenname == "Kreis")  $Raumebene = "krs";








?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>IÖR Monitor</title>
<link href="screen_viewer.css" rel="stylesheet" type="text/css" media="screen" />
<link href="print_viewer.css" rel="stylesheet" type="text/css" media="print" />

<link href="../screen_v2.css" rel="stylesheet" type="text/css" />
<style type="text/css">
body,td,th {
	color: #CCC;
	font-size: 16px;
}
a:link {
	color: #99CCFF;
}
a:visited {
	color: #99CCFF;
}
a:hover {
	color: #99CCFF;
}
a:active {
	color: #99CCFF;
}
</style>
</head>
<body>
<div style="width:900px; margin:auto;">
<form action="" method="post">
  <h2>Fortführung der Geometrien aus neuen Tabellen der PostGIS-DB</h2>
  <p>Jahr oder Zeitraumkürzel<br />
    <input name="Jahr" type="text" value="<?php echo $Jahr; ?>" />
    <br />
    <br />
    Raumebene<br />
    <select name="Raumebenenname">
      <option value="Bundesland" <?php if($Raumebenenname == "Bundesland") echo "selected"; ?>>Bundesland</option>
      <option value="Kreis" <?php if($Raumebenenname == "Kreis") echo "selected"; ?>>Kreis</option>
    </select>
    <br />
    <br />
    <input name="Senden" type="submit" value="Ausführen" />
  </p>
</form>

<?php
// Abbruch bei leeren Eingabefeldern
if(!$Jahr)
{
	echo "</body></html>";
	die;
}


// AGS importieren aus PostGIS
$SQL_PostGIS = "SELECT ags,gen FROM vg250_".$Raumebene."_".$Jahr."_fein";
$ERGEBNIS_PGSQL =  pg_query($Verbindung_PostgreSQL,$SQL_PostGIS);  
							
$PG_Zeile = pg_fetch_assoc($ERGEBNIS_PGSQL,0);

// Check auf Vorhandensein des gefundenen AGS-Datensatzes
$i_v=0;
while($PG_Zeile = @pg_fetch_assoc($ERGEBNIS_PGSQL,$i_v))
{
	$AGS = $PG_Zeile['ags'];
	$NAME = $PG_Zeile['gen'];
	
	// Erfassen der ID
	$SQL_AGS_vorhanden = "SELECT Wert,v_geometrie.ID_GEOMETRIE,Jahr_min,Jahr_max FROM v_geometrie,v_geometrie_kriterium_werte 
						WHERE 
						v_geometrie.ID_GEOMETRIE = v_geometrie_kriterium_werte.ID_GEOMETRIE
						AND Wert = '".$AGS."' AND RAUMEBENE = '".$Raumebenenname."' ";
	$Ergebnis_AGS_vorhanden = mysqli_query($Verbindung,$SQL_AGS_vorhanden);

	if($ID_vorh = mysqli_result($Ergebnis_AGS_vorhanden,0,'ID_GEOMETRIE'))
	{
		// UPDATE
		// wenn Jahr_max in DB kleiner sein sollte
		if(mysqli_result($Ergebnis_AGS_vorhanden,0,'Jahr_max') < $Jahr)
		{
			$SQL_INS = "UPDATE v_geometrie SET Jahr_max = '".$Jahr."' WHERE ID_GEOMETRIE = '".$ID_vorh."'";
			if($Erg_INS = mysqli_query($Verbindung,$SQL_INS)) $UPD_Anz++;
		}
		
		if(mysqli_result($Ergebnis_AGS_vorhanden,0,'Jahr_min') > $Jahr)
		{
			$SQL_INS = "UPDATE v_geometrie SET Jahr_max = '".$Jahr."' WHERE ID_GEOMETRIE = '".$ID_vorh."'";
			if($Erg_INS = mysqli_query($Verbindung,$SQL_INS)) $UPD_Anz++;
		}
		
		// Nur Name Updaten (nur für Korrekturen)
		$SQL_INS = "UPDATE v_geometrie SET Name='".$NAME."',Name_HTML='".$NAME."',Name_UTF8='".$NAME."' WHERE ID_GEOMETRIE = '".$ID_vorh."'";
		if($Erg_INS = mysqli_query($Verbindung,$SQL_INS)) $UPD_Anz_Name++;

	}
	else
	{
		// INSERT
		$SQL_INS = "INSERT INTO v_geometrie (Name,Name_UTF8,Jahr_min,Jahr_max,SQL_Auswahlkriterium,SQL_Auswahloperator,SQL_Auswahlop_Zusatz_2,Transparenz,Postgres_Tabelle,RAUMEBENE,View) 
		VALUES ('".$NAME."','".$NAME."','".$Jahr."','".$Jahr."','AGS','LIKE','%','1','vg250','".$Raumebenenname."','0')";
		$Erg_INS = mysqli_query($Verbindung,$SQL_INS);
		
		$id = mysqli_insert_id(); // letzte eingefügte ID erfassen und für kommenden INSERT nutzen ////php update: evtl $Verbindung in klammern??
		
		$SQL_INS2 = "INSERT INTO v_geometrie_kriterium_werte (ID_GEOMETRIE,Wert) 
		VALUES ('".$id."','".$AGS."')";
		if($Erg_INS2 = mysqli_query($Verbindung,$SQL_INS2)) $INS_Anz++;
		
	}
	
	$i_v++;
}

echo "<br /><br />Erfolgreich ausgeführte Aktionen:<br /><br />";
echo "INSERTs: ".$INS_Anz;
echo "<br />UPDATEs: ".$UPD_Anz;
echo "<br />Name-UPDATEs: ".$UPD_Anz_Name;

// alte AGS checken und Endjahr setzen
// => wenn der Reihe nach und nicht von Hand eingespeist wird, nicht nötig




?>
</div>
</body>
</html>
