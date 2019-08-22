<?php
set_time_limit(0);

$Verbindung = mysql_connect("localhost","monitor","");
mysql_select_db("monitor_svg", $Verbindung);

$Jahr = '2006';



// Tabelle erstellen
$SQL_Indikatoren = "SELECT ID_INDIKATOR FROM m_indikatorwerte GROUP BY ID_INDIKATOR ORDER BY ID_INDIKATOR"; 
$Ergebnis_Indikatoren = mysql_query($SQL_Indikatoren,$Verbindung);
	

$Tabelle = "CREATE TABLE indikatoren_fuer_ArcGIS_".$Jahr." (AGS CHAR (8)";

$i = 0;
while($ID_INDIKATOR = @mysql_result($Ergebnis_Indikatoren,$i,'ID_INDIKATOR')) 
{
	$Tabelle = $Tabelle.",".$ID_INDIKATOR." DOUBLE";
	$i++;
}

$Tabelle = $Tabelle.")";

mysql_select_db("test", $Verbindung);
$Ergebnis_Tabelle = mysql_query($Tabelle,$Verbindung);
mysql_select_db("monitor_svg", $Verbindung);


// Tabelle füllen (nur mit aktuellsten Zahlen, ältere werden ignoriert)

$SQL_Indikatorenwerte = "SELECT * FROM m_indikatorwerte WHERE JAHR = '".$Jahr."' ORDER BY AGS, ID_INDIKATOR, JAHR_LIEFERUNG DESC, MONAT_LIEFERUNG DESC"; 
$Ergebnis_Indikatorenwerte = mysql_query($SQL_Indikatorenwerte,$Verbindung);

mysql_select_db("test", $Verbindung);

$i_mm = 0;
while($ID_INDIKATOR = @mysql_result($Ergebnis_Indikatorenwerte,$i_mm,'ID_INDIKATOR'))
{
	$AGS = @mysql_result($Ergebnis_Indikatorenwerte,$i_mm,'AGS');
	if($ID_INDIKATOR != $ID_INDIKATOR_alt or $AGS != $AGS_alt) // Sondert ältere Datensätze des Jahres aus
	{
		if($AGS == $AGS_alt)
		{
			if(!@mysql_result($Ergebnis_Indikatorenwerte,$i_mm,'FEHLERCODE'))
			{
				$SQL_INS = "UPDATE indikatoren_fuer_ArcGIS_".$Jahr." SET ".$ID_INDIKATOR." = '".@mysql_result($Ergebnis_Indikatorenwerte,$i_mm,'INDIKATORWERT')."' WHERE AGS = '".$AGS."'";
			}
			else
			{
				$SQL_INS = "UPDATE indikatoren_fuer_ArcGIS_".$Jahr." SET ".$ID_INDIKATOR." = '99999.9' WHERE AGS = '".$AGS."'";
			}
		}
		else
		{
			if(!@mysql_result($Ergebnis_Indikatorenwerte,$i_mm,'FEHLERCODE'))
			{
				$SQL_INS = "INSERT INTO indikatoren_fuer_ArcGIS_".$Jahr." (AGS,".$ID_INDIKATOR.") VALUES ('".$AGS."','".@mysql_result($Ergebnis_Indikatorenwerte,$i_mm,'INDIKATORWERT')."')";
			}
			else
			{
				$SQL_INS = "INSERT INTO indikatoren_fuer_ArcGIS (AGS,".$ID_INDIKATOR.") VALUES ('".$AGS."','99999,9')";
			}
			$AGS_Anzahl++;
		}
		$ERG_INS = mysql_query($SQL_INS,$Verbindung);
	}
	$AGS_alt = $AGS;
	$ID_INDIKATOR_alt = $ID_INDIKATOR;
	$i_mm++;
}

echo "Fertig!<br />".$AGS_Anzahl." AGS übernommen.";























?>

