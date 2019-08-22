<?php
set_time_limit(0);

$Verbindung = mysqli_connect("localhost","monitor","");
mysqli_select_db($Verbindung,"monitor_svg");

$Jahr = '2006';



// Tabelle erstellen
$SQL_Indikatoren = "SELECT ID_INDIKATOR FROM m_indikatorwerte GROUP BY ID_INDIKATOR ORDER BY ID_INDIKATOR"; 
$Ergebnis_Indikatoren = mysqli_query($Verbindung,$SQL_Indikatoren);
	

$Tabelle = "CREATE TABLE indikatoren_fuer_ArcGIS_".$Jahr." (AGS CHAR (8)";

$i = 0;
while($ID_INDIKATOR = @mysqli_result($Ergebnis_Indikatoren,$i,'ID_INDIKATOR')) 
{
	$Tabelle = $Tabelle.",".$ID_INDIKATOR." DOUBLE";
	$i++;
}

$Tabelle = $Tabelle.")";

mysqli_select_db($Verbindung,"test");
$Ergebnis_Tabelle = mysqli_query($Verbindung,$Tabelle);



// Tabelle füllen (nur mit aktuellsten Zahlen, ältere werden ignoriert)
// -----------------------------------------------------------------------------------------------------------------------------------------

mysqli_select_db($Verbindung,"monitor_svg");
$SQL_AGS = "SELECT AGS FROM m_indikatorwerte GROUP BY AGS ORDER BY AGS"; 
$Ergebnis_AGS = mysqli_query($Verbindung,$SQL_AGS);


$i_ags = 0;
while($AGS = @mysqli_result($Ergebnis_AGS,$i_ags,'AGS'))
{
	
	if($i_ags % 100 == 0) // Info Ausgabe alle 100 AGS
	{
		echo $i_ags." geschafft (".date("H:i:s").")...<br />";
	}
	
	mysqli_select_db($Verbindung,"test");
	$SQL_INS = "INSERT INTO indikatoren_fuer_ArcGIS_".$Jahr." (AGS) VALUES ('".$AGS."')";
	$ERG_INS = mysqli_query($Verbindung,$SQL_INS);
	
	mysqli_select_db($Verbindung,"monitor_svg");
	$SQL_Indikatorenwerte = "SELECT * FROM m_indikatorwerte WHERE AGS = '".$AGS."' AND JAHR = '".$Jahr."' ORDER BY ID_INDIKATOR, JAHR_LIEFERUNG DESC, MONAT_LIEFERUNG DESC"; 
	$Ergebnis_Indikatorenwerte = mysqli_query($Verbindung,$SQL_Indikatorenwerte);
	
	mysqli_select_db( $Verbindung,"test");
	$i_mm = 0;
	while($ID_INDIKATOR = @mysqli_result($Ergebnis_Indikatorenwerte,$i_mm,'ID_INDIKATOR'))
	{
		if($ID_INDIKATOR != $ID_INDIKATOR_alt) // Sondert ältere Datensätze des Jahres aus
		{
			if(!@mysqli_result($Ergebnis_Indikatorenwerte,$i_mm,'FEHLERCODE'))
			{
				$SQL_UPD = "UPDATE indikatoren_fuer_ArcGIS_".$Jahr." SET ".$ID_INDIKATOR." = '".@mysqli_result($Ergebnis_Indikatorenwerte,$i_mm,'INDIKATORWERT')."' WHERE AGS = '".$AGS."'";
			}
			else
			{
				$SQL_UPD = "UPDATE indikatoren_fuer_ArcGIS_".$Jahr." SET ".$ID_INDIKATOR." = '-9999.9' WHERE AGS = '".$AGS."'";
			}
			
			$ERG_UPD = mysqli_query($Verbindung,$SQL_UPD);
		}
		
		$ID_INDIKATOR_alt = $ID_INDIKATOR;
		$i_mm++;
	}
	
	$i_ags++;
}








echo "Fertig!<br />".$AGS_Anzahl." AGS &uuml;bernommen.";























?>

