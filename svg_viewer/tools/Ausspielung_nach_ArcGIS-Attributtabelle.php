<?php
set_time_limit(0); // Laufzeit aus unendlich setzen (Programm kann u.U. 10h dauern)

$Verbindung = mysqli_connect("localhost","monitor","");
mysqli_select_db($Verbindung,"monitor_svg");

// Voreinstellungen
// ------------------------------
$Jahr = '2006';
$Ziel_DB = "test";
// ------------------------------


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

// Umschalten auf Ziel-DB
mysqli_select_db($Verbindung,$Ziel_DB);
$Ergebnis_Tabelle = mysqli_query($Verbindung,$Tabelle);

// Umschalten auf Quell-DB
mysqli_select_db($Verbindung"monitor_svg");


// Tabelle füllen (nur mit aktuellsten Zahlen, ältere werden ignoriert)

$SQL_Indikatorenwerte = "SELECT * FROM m_indikatorwerte WHERE JAHR = '".$Jahr."' ORDER BY AGS, ID_INDIKATOR, JAHR_LIEFERUNG DESC, MONAT_LIEFERUNG DESC"; 
$Ergebnis_Indikatorenwerte = mysqli_query($Verbindung,$SQL_Indikatorenwerte);

// Umschalten auf Ziel-DB
mysqli_select_db($Verbindung,$Ziel_DB);

$i_mm = 0;
while($ID_INDIKATOR = @mysqli_result($Ergebnis_Indikatorenwerte,$i_mm,'ID_INDIKATOR'))
{
	$AGS = @mysqli_result($Ergebnis_Indikatorenwerte,$i_mm,'AGS');
	if($ID_INDIKATOR != $ID_INDIKATOR_alt or $AGS != $AGS_alt) // Sondert ältere Datensätze des Jahres aus
	{
		if($AGS == $AGS_alt)
		{
			if(!@mysqli_result($Ergebnis_Indikatorenwerte,$i_mm,'FEHLERCODE'))
			{
				$SQL_INS = "UPDATE indikatoren_fuer_ArcGIS_".$Jahr." SET ".$ID_INDIKATOR." = '".@mysqli_result($Ergebnis_Indikatorenwerte,$i_mm,'INDIKATORWERT')."' WHERE AGS = '".$AGS."'";
			}
			else
			{
				$SQL_INS = "UPDATE indikatoren_fuer_ArcGIS_".$Jahr." SET ".$ID_INDIKATOR." = '99999.9' WHERE AGS = '".$AGS."'";
			}
		}
		else
		{
			if(!@mysqli_result($Ergebnis_Indikatorenwerte,$i_mm,'FEHLERCODE'))
			{
				$SQL_INS = "INSERT INTO indikatoren_fuer_ArcGIS_".$Jahr." (AGS,".$ID_INDIKATOR.") VALUES ('".$AGS."','".@mysqli_result($Ergebnis_Indikatorenwerte,$i_mm,'INDIKATORWERT')."')";
			}
			else
			{
				$SQL_INS = "INSERT INTO indikatoren_fuer_ArcGIS (AGS,".$ID_INDIKATOR.") VALUES ('".$AGS."','99999,9')";
			}
			$AGS_Anzahl++;
		}
		$ERG_INS = mysqli_query($Verbindung,$SQL_INS);
	}
	$AGS_alt = $AGS;
	$ID_INDIKATOR_alt = $ID_INDIKATOR;
	$i_mm++;
}

echo "Fertig!<br />".$AGS_Anzahl." AGS übernommen.";























?>

