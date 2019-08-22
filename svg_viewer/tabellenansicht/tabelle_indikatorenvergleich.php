<?php 
session_start(); // Sitzung starten/ wieder aufnehmen

// Memory-Limit erweitern
ini_set('memory_limit', '232M');
//ini_set('max_execution_time', '500');

include("../includes_classes/verbindung_mysqli.php");
include("../includes_classes/implode_explode.php");


// Dateiname
if($_POST['csv']) 
{
	
	
	// Erste Zeile fest setzen:
	$CSV[0]['Kategorie'] = 'Kategorie';
	$CSV[0]['Indikator'] = 'Indikator';
	if($_SESSION['Dokument']['ViewBerechtigung'] == "0") { $CSV[0]['Indikator_ID'] = 'Indikator_ID'; }
	$CSV[0]['Wert'] = 'Wert';
	$CSV[0]['Einheit'] = 'Einheit';
	/* 
	Werden in Tabelle gesetzt, da diese auch fehlen können
	$CSV[0]['Wert_bund'] = 'Wert_bund';
	$CSV[0]['Wert_Land'] = 'Wert_Land';
	$CSV[0]['Wert_Kreis'] = 'Wert_Kreis'; */
	// als letztes anhängen: $CSV[0]['Aktualitaet'] = 'Grundaktualitaet';
	
}



// Einstellg bezügl MITTLERE_AKTUALITAET_IGNORE erfassen
$SQL_Indikator_Info = "SELECT * FROM m_indikatoren WHERE ID_INDIKATOR='".$_SESSION['Dokument']['Fuellung']['Indikator']."'";
$Ergebnis_Indikator_Info = mysqli_query($Verbindung,$SQL_Indikator_Info);
$MITTLERE_AKTUALITAET_IGNORE = utf8_encode(@mysqli_result($Ergebnis_Indikator_Info,0,'MITTLERE_AKTUALITAET_IGNORE'));

// Eckdaten übernehmen
if($ags_use = $_POST['ags'])
{
	
	if($ags_use == '99')
	{
		// Deutschlandwert
		$_SESSION['INDIKATOR_TABELLE']['ags'] = $ags = '99';
		$_SESSION['INDIKATOR_TABELLE']['name'] = $name = 'Bundesrepublik';
		$_SESSION['INDIKATOR_TABELLE']['name_KRS'] = $name_KRS = '';	
		$_SESSION['INDIKATOR_TABELLE']['name_BLD'] = $name_BLD = '';		
	}
	else
	{
		// normale Raumeinheiten
		$_SESSION['INDIKATOR_TABELLE']['ags'] = $ags = $_POST['ags'];
		$_SESSION['INDIKATOR_TABELLE']['name'] = $name = $_POST['name'];
		$_SESSION['INDIKATOR_TABELLE']['name_KRS'] = $name_KRS = $_POST['name_KRS'];	
		$_SESSION['INDIKATOR_TABELLE']['name_BLD'] = $name_BLD = $_POST['name_BLD'];
	}
	
	
	
}
else
{
	$ags = $_SESSION['INDIKATOR_TABELLE']['ags'];
	$name = $_SESSION['INDIKATOR_TABELLE']['name'];
	$name_KRS = $_SESSION['INDIKATOR_TABELLE']['name_KRS'];
	$name_BLD = $_SESSION['INDIKATOR_TABELLE']['name_BLD'];	
}




// hier mal so angenommen und zu ändern, wenn dann mal Auswahlfeld vorhanden
$Jahr_Auswahl = $_SESSION['Dokument']['Jahr_Anzeige'];

// Sortierung
if($_GET['sort'])
{
	// 2x wiederholte auswahl = umkehrung der sortierreihenfolge
	if($_SESSION['Tabellen_Sortierung'] == $_GET['sort'])
	{
		if($_SESSION['Tabellen_Sortierung_asc_desc'] == "ASC") 
		{
			$_SESSION['Tabellen_Sortierung_asc_desc'] = "DESC";
		}
		else
		{
			$_SESSION['Tabellen_Sortierung_asc_desc'] = "ASC";
		}
	}
	else
	{
		$_SESSION['Tabellen_Sortierung_asc_desc'] = "ASC";
	}
	$_SESSION['Tabellen_Sortierung'] = $_GET['sort'];
}
else
{
	// Standardsortierung
	if(!$_SESSION['Tabellen_Sortierung'])
	{
		$_SESSION['Tabellen_Sortierung'] = ""; 
		$_SESSION['Tabellen_Sortierung_asc_desc'] = "ASC";
	}
}







// Ein-Ausblenden der Grundaktualität
if($_POST['AKTUALITAET'] or $_SESSION['Tabelle']['AKTUALITAET'] == "") // <= Standardmäßig eingeschaltet
{
	$_SESSION['Tabelle']['AKTUALITAET'] = '1'; 
}
else
{
	if($_POST['Zeitschnitt_UERE_Formular']) $_SESSION['Tabelle']['AKTUALITAET'] = '0';
}
// Aktualität auf jeden Fall ausblenden, wenn in DB so verzeichnet
if($MITTLERE_AKTUALITAET_IGNORE)
{
	$_SESSION['Tabelle']['AKTUALITAET'] = '0';
}












// -----------------------------------------------
// Temp-Table erstellen

//-------------> nur zum testen:

/* TEMPORARY */
$Ergebnis_drop_table = mysqli_query($Verbindung,"DROP TABLE `t_temp_indikatoren_tabelle`");
 $SQL_temp_table = "CREATE TEMPORARY TABLE `t_temp_indikatoren_tabelle` (
  `ID_INDIKATOR` varchar(10) NOT NULL,
  `IND_NAME` varchar(255) default NULL,
  `KATEGORIE` varchar(100) default NULL,
  `EINHEIT` varchar(100) default NULL,
  `WERT` double default NULL,
  `FEHLERCODE` int(2)  NULL DEFAULT 0,
  `AKT_IGNORE` int(1) default NULL,
  `AKT` varchar(100) default NULL,
  `WERT_KRS` double default NULL,
  `WERT_KRS_DIFF` double default NULL,
  `AKT_KRS` varchar(100) default NULL,
  `FEHLERCODE_KRS` int(2) default NULL,
  `WERT_BLD` double default NULL,
  `WERT_BLD_DIFF` double default NULL,
  `AKT_BLD` varchar(100) default NULL,
  `FEHLERCODE_BLD` int(2) default NULL, 
  `WERT_BND` double default NULL,
  `WERT_BND_DIFF` double default NULL,
  `AKT_BND` varchar(100) default NULL,
  `FEHLERCODE_BND` int(2) default NULL, 
   PRIMARY KEY  (`ID_INDIKATOR`)
) ENGINE=HEAP DEFAULT CHARSET=utf8;";  
/*
 */
//-------------> für den echten Betrieb Temp-Table nutzen .... funktioniert aber leider mit lokalem MySQL am Arbeitsrechner nicht!????

//$Ergebnis_drop_table = mysqli_query($Verbindung,"DROP TABLE `t_temp_indikatoren_tabelle`");
/* $SQL_temp_table = "CREATE TEMPORARY TABLE `t_temp_indikatoren_tabelle` (
  `ID_INDIKATOR` varchar(10) NOT NULL,
  `IND_NAME` varchar(100) default NULL,
  `EINHEIT` varchar(100) default NULL,
  `WERT` double default NULL,
  `FEHLERCODE` int(2)  NULL DEFAULT 0,
  `AKT_IGNORE` int(1) default NULL,
  `AKT` int(4) default NULL,
  `WERT_KRS` double default NULL,
  `WERT_KRS_DIFF` double default NULL,
  `AKT_KRS` int(4) default NULL,
  `FEHLERCODE_KRS` int(2) default NULL,
  `WERT_BLD` double default NULL,
  `WERT_BLD_DIFF` double default NULL,
  `AKT_BLD` int(4) default NULL,
  `FEHLERCODE_BLD` int(2) default NULL, 
  `WERT_BND` double default NULL,
  `WERT_BND_DIFF` double default NULL,
  `AKT_BND` int(4) default NULL,
  `FEHLERCODE_BND` int(2) default NULL, 
   PRIMARY KEY  (`ID_INDIKATOR`)
) ENGINE=HEAP DEFAULT CHARSET=utf8;";   */

 
$Ergebnis_temp_table = mysqli_query($Verbindung,$SQL_temp_table); 





// Check auf freigegebene Indikatoren für User
$SQL_Indikatoren = "SELECT * FROM m_indikatoren,m_indikator_freigabe,m_thematische_kategorien 
						WHERE m_indikatoren.ID_INDIKATOR = m_indikator_freigabe.ID_INDIKATOR 
						AND STATUS_INDIKATOR_FREIGABE >= '".$_SESSION['Dokument']['ViewBerechtigung']."' 
						AND m_indikatoren.ID_THEMA_KAT = m_thematische_kategorien.ID_THEMA_KAT
						AND JAHR = '".$Jahr_Auswahl."' 
						ORDER BY SORTIERUNG_THEMA_KAT,SORTIERUNG";
$Ergebnis_Indikatoren = mysqli_query($Verbindung,$SQL_Indikatoren);


// Vorhandene, freigegebene Indikatoren eintragen
$i_ind=0;
while(@mysqli_result($Ergebnis_Indikatoren,$i_ind,'ID_INDIKATOR'))
{ 		
	$SQL_INS_IND = "INSERT INTO t_temp_indikatoren_tabelle 
					(ID_INDIKATOR,AKT_IGNORE,EINHEIT,KATEGORIE,IND_NAME) 
					VALUES 
					('".@mysqli_result($Ergebnis_Indikatoren,$i_ind,'ID_INDIKATOR')
					."','".@mysqli_result($Ergebnis_Indikatoren,$i_ind,'MITTLERE_AKTUALITAET_IGNORE')
					."','".@mysqli_result($Ergebnis_Indikatoren,$i_ind,'EINHEIT')
					."','".@mysqli_result($Ergebnis_Indikatoren,$i_ind,'THEMA_KAT_NAME')
					."','".@mysqli_result($Ergebnis_Indikatoren,$i_ind,'INDIKATOR_NAME')."');";
					
	if(!$Ergebnis_INS_IND = mysqli_query($Verbindung,$SQL_INS_IND)) echo "<!-- Fehler! ".$SQL_INS_IND." -->";
	
	$i_ind++;
}




// ------------------------
// DB-Tabelle füllen

$SQL_DS_vorh = "SELECT * FROM t_temp_indikatoren_tabelle";
$Ergebnis_DS_vorh = mysqli_query($Verbindung,$SQL_DS_vorh); 

$i_ds = 0;
$i_ds_color = 0;
while($ID_INDIKATOR = @mysqli_result($Ergebnis_DS_vorh,$i_ds,'ID_INDIKATOR'))
{

		
	// Indikatorwert und Fehlercode
	$SQL_Indikatorenwerte = "SELECT INDIKATORWERT,FEHLERCODE 
	FROM m_indikatorwerte_".$Jahr_Auswahl." 
	WHERE AGS = '".$ags."' 
	AND ID_INDIKATOR = '".$ID_INDIKATOR."';"; 
	$Ergebnis_Indikatorenwerte = mysqli_query($Verbindung,$SQL_Indikatorenwerte); 
			
	$UPD_Wert = @mysqli_result($Ergebnis_Indikatorenwerte,0,'INDIKATORWERT');
	$UPD_Fehlercode = @mysqli_result($Ergebnis_Indikatorenwerte,0,'FEHLERCODE');
	

	// Grundaktualität
	/* $SQL_Grundktualitaet = "SELECT AGS,INDIKATORWERT FROM m_indikatorwerte_".$Jahr_Auswahl." WHERE ID_INDIKATOR = 'Z00AG' AND AGS = '".$ags."';"; 
	// $SQL_Grundktualitaet = "SELECT AKTUALITAET FROM v_akt_gesamt WHERE JAHR = '".$Grundakt_Verweis."' AND AGS = '".$ags."'"; 
	$Ergebnis_Grundktualitaet = mysqli_query($Verbindung,$SQL_Grundktualitaet);
	
	$Grundakt = @mysqli_result($Ergebnis_Grundktualitaet,0,'INDIKATORWERT'); */
	
	// Umstellung auf Jahr.Monat Format
	// Jahr
		$SQL_Akt_D_Z = "SELECT AGS,INDIKATORWERT,VGL_AB FROM m_indikatorwerte_".$Jahr_Auswahl." WHERE AGS = '".$ags."' AND ID_INDIKATOR = 'Z00AG' AND (VGL_AB = '0' OR VGL_AB <= '".$Jahr_Auswahl."') ORDER BY VGL_AB DESC;"; 
		$Ergebnis_Akt_D = mysqli_query($Verbindung,$SQL_Akt_D_Z);
		$Akt_D_J_Z = @mysqli_result($Ergebnis_Akt_D,0,'INDIKATORWERT');
	// Monat
		$SQL_Akt_D_Z = "SELECT AGS,INDIKATORWERT,VGL_AB FROM m_indikatorwerte_".$Jahr_Auswahl." WHERE AGS = '".$ags."' AND ID_INDIKATOR = 'Z01AG' AND (VGL_AB = '0' OR VGL_AB <= '".$Jahr_Auswahl."') ORDER BY VGL_AB DESC;";  
		$Ergebnis_Akt_D = mysqli_query($Verbindung,$SQL_Akt_D_Z); 
		$Akt_D_m_Z = @mysqli_result($Ergebnis_Akt_D,0,'INDIKATORWERT');
		
		if($Akt_D_m_Z < 10) $Akt_D_m_Z = "0".$Akt_D_m_Z;
		$Grundakt = $Akt_D_m_Z." / ".$Akt_D_J_Z;
	
	
	// Grundaktualität KRS
	if($_SESSION['Dokument']['Raumgliederung'] == "gem")
	{
		/* $SQL_Grundktualitaet = "SELECT AGS,INDIKATORWERT FROM m_indikatorwerte_".$Jahr_Auswahl." WHERE ID_INDIKATOR = 'Z00AG' AND AGS = '".substr($ags,0,5)."';"; 
		// $SQL_Grundktualitaet = "SELECT AKTUALITAET FROM v_akt_gesamt WHERE JAHR = '".$Grundakt_Verweis."' AND AGS = '".substr($ags,0,5)."'"; 
		$Ergebnis_Grundktualitaet = mysqli_query($Verbindung,$SQL_Grundktualitaet);
		
		$Grundakt_KRS = @mysqli_result($Ergebnis_Grundktualitaet,0,'INDIKATORWERT');
		 */
		// Umstellung auf Jahr.Monat Format
		// Jahr
			$SQL_Akt_D_Z = "SELECT AGS,INDIKATORWERT,VGL_AB FROM m_indikatorwerte_".$Jahr_Auswahl." WHERE AGS = '".substr($ags,0,5)."' AND ID_INDIKATOR = 'Z00AG' AND (VGL_AB = '0' OR VGL_AB <= '".$Jahr_Auswahl."') ORDER BY VGL_AB DESC;"; 
			$Ergebnis_Akt_D = mysqli_query($Verbindung,$SQL_Akt_D_Z);
			$Akt_D_J_Z = @mysqli_result($Ergebnis_Akt_D,0,'INDIKATORWERT');
		// Monat
			$SQL_Akt_D_Z = "SELECT AGS,INDIKATORWERT,VGL_AB FROM m_indikatorwerte_".$Jahr_Auswahl." WHERE AGS = '".substr($ags,0,5)."' AND ID_INDIKATOR = 'Z01AG' AND (VGL_AB = '0' OR VGL_AB <= '".$Jahr_Auswahl."') ORDER BY VGL_AB DESC;";  
			$Ergebnis_Akt_D = mysqli_query($Verbindung,$SQL_Akt_D_Z); 
			$Akt_D_m_Z = @mysqli_result($Ergebnis_Akt_D,0,'INDIKATORWERT');
			
			if($Akt_D_m_Z < 10) $Akt_D_m_Z = "0".$Akt_D_m_Z;
			$Grundakt_KRS = $Akt_D_m_Z." / ".$Akt_D_J_Z;
			
		
	}
	else{
		
		
		
		}
		
	// Grundaktualität BLD
	if($_SESSION['Dokument']['Raumgliederung'] == "gem" 
	or $_SESSION['Dokument']['Raumgliederung'] == "krs" 
	or $_SESSION['Dokument']['Raumgliederung'] == "kfs" 
	or $_SESSION['Dokument']['Raumgliederung'] == "lks")
	{
		/* $SQL_Grundktualitaet = "SELECT AGS,INDIKATORWERT FROM m_indikatorwerte_".$Jahr_Auswahl." WHERE ID_INDIKATOR = 'Z00AG' AND AGS = '".substr($ags,0,2)."';"; 
		// $SQL_Grundktualitaet = "SELECT AKTUALITAET FROM v_akt_gesamt WHERE JAHR = '".$Grundakt_Verweis."' AND AGS = '".substr($ags,0,2)."'"; 
		$Ergebnis_Grundktualitaet = mysqli_query($Verbindung,$SQL_Grundktualitaet);
		
		$Grundakt_BLD = @mysqli_result($Ergebnis_Grundktualitaet,0,'INDIKATORWERT'); */
		
		// Umstellung auf Jahr.Monat Format
		// Jahr
			$SQL_Akt_D_Z = "SELECT AGS,INDIKATORWERT,VGL_AB FROM m_indikatorwerte_".$Jahr_Auswahl." WHERE AGS = '".substr($ags,0,2)."' AND ID_INDIKATOR = 'Z00AG' AND (VGL_AB = '0' OR VGL_AB <= '".$Jahr_Auswahl."') ORDER BY VGL_AB DESC;"; 
			$Ergebnis_Akt_D = mysqli_query($Verbindung,$SQL_Akt_D_Z);
			$Akt_D_J_Z = @mysqli_result($Ergebnis_Akt_D,0,'INDIKATORWERT');
		// Monat
			$SQL_Akt_D_Z = "SELECT AGS,INDIKATORWERT,VGL_AB FROM m_indikatorwerte_".$Jahr_Auswahl." WHERE AGS = '".substr($ags,0,2)."' AND ID_INDIKATOR = 'Z01AG' AND (VGL_AB = '0' OR VGL_AB <= '".$Jahr_Auswahl."') ORDER BY VGL_AB DESC;";  
			$Ergebnis_Akt_D = mysqli_query($Verbindung,$SQL_Akt_D_Z); 
			$Akt_D_m_Z = @mysqli_result($Ergebnis_Akt_D,0,'INDIKATORWERT');
			
			if($Akt_D_m_Z < 10) $Akt_D_m_Z = "0".$Akt_D_m_Z;
			$Grundakt_BLD = $Akt_D_m_Z." / ".$Akt_D_J_Z;
		
		
	}

	
	
	// Werte für Kreise als übergeordnete Raumeinheit mit erfassen wenn Gemeindeebene ausgewählt
	if($_SESSION['Dokument']['Raumgliederung'] == "gem")
	{
		// Indikatorwert und Fehlercode
		$SQL_Indikatorenwerte_KRS = "SELECT INDIKATORWERT,FEHLERCODE 
		FROM m_indikatorwerte_".$Jahr_Auswahl." 
		WHERE AGS = '".substr($ags,0,5)."' 
		AND ID_INDIKATOR = '".$ID_INDIKATOR."';"; 
		$Ergebnis_Indikatorenwerte_KRS = mysqli_query($Verbindung,$SQL_Indikatorenwerte_KRS); 
				
		$UPD_Wert_KRS = @mysqli_result($Ergebnis_Indikatorenwerte_KRS,0,'INDIKATORWERT');
		$UPD_Fehlercode_KRS = @mysqli_result($Ergebnis_Indikatorenwerte_KRS,0,'FEHLERCODE');
	}
	else
	{
		//damit nicht NULL und Ausgabe in Gebietsprofil erfolgt
				$UPD_Wert_KRS = '0';
			$UPD_Fehlercode_KRS = '0';
		}
		
	// Werete für Bundesländer als übergeordnete Raumeinheit mit anzeigen wenn folgendes ausgewählt
	if($_SESSION['Dokument']['Raumgliederung'] == "gem" 
	or $_SESSION['Dokument']['Raumgliederung'] == "krs" 
	or $_SESSION['Dokument']['Raumgliederung'] == "kfs" 
	or $_SESSION['Dokument']['Raumgliederung'] == "lks")
	{
		// Indikatorwert und Fehlercode
		$SQL_Indikatorenwerte_BLD = "SELECT INDIKATORWERT,FEHLERCODE 
		FROM m_indikatorwerte_".$Jahr_Auswahl." 
		WHERE AGS = '".substr($ags,0,2)."' 
		AND ID_INDIKATOR = '".$ID_INDIKATOR."';"; 
		$Ergebnis_Indikatorenwerte_BLD = mysqli_query($Verbindung,$SQL_Indikatorenwerte_BLD); 
				
		$UPD_Wert_BLD = @mysqli_result($Ergebnis_Indikatorenwerte_BLD,0,'INDIKATORWERT');
		$UPD_Fehlercode_BLD = @mysqli_result($Ergebnis_Indikatorenwerte_BLD,0,'FEHLERCODE');
	}
	else
	{
		//damit nicht NULL und Ausgabe in Gebietsprofil erfolgt für Städte und Stadtteile
				$UPD_Wert_BLD = '0';
			$UPD_Fehlercode_BLD = '0';
		}
	
	// Wert für Deutschland für Berechnungen ermitteln (AGS = 99)
	$SQL_Indikatorenwert_D = "SELECT INDIKATORWERT,FEHLERCODE FROM m_indikatorwerte_".$Jahr_Auswahl." WHERE AGS = '99' AND ID_INDIKATOR = '".$ID_INDIKATOR."';"; 
	$Ergebnis_Indikatorenwerte_D = mysqli_query($Verbindung,$SQL_Indikatorenwert_D); 
	$Wert_D = @mysqli_result($Ergebnis_Indikatorenwerte_D,0,'INDIKATORWERT');
	$Fehlercode_D = @mysqli_result($Ergebnis_Indikatorenwerte_D,0,'FEHLERCODE');
	
	// Mittl. Grundaktualität für Deutschland für Berechnungen ermitteln (AGS = 99)
	/* $SQL_Akt_D = "SELECT INDIKATORWERT,FEHLERCODE FROM m_indikatorwerte_".$Jahr_Auswahl." WHERE AGS = '99' AND ID_INDIKATOR = 'Z00AG';"; 
	$Ergebnis_Akt_D = mysqli_query($Verbindung,$SQL_Akt_D); 
	$Akt_D = @mysqli_result($Ergebnis_Akt_D,0,'INDIKATORWERT'); */
	
	// Umstellung auf Jahr.Monat Format
		// Jahr
			$SQL_Akt_D_Z = "SELECT AGS,INDIKATORWERT,VGL_AB FROM m_indikatorwerte_".$Jahr_Auswahl." WHERE AGS = '99' AND ID_INDIKATOR = 'Z00AG' AND (VGL_AB = '0' OR VGL_AB <= '".$Jahr_Auswahl."') ORDER BY VGL_AB DESC;"; 
			$Ergebnis_Akt_D = mysqli_query($Verbindung,$SQL_Akt_D_Z);
			$Akt_D_J_Z = @mysqli_result($Ergebnis_Akt_D,0,'INDIKATORWERT');
		// Monat
			$SQL_Akt_D_Z = "SELECT AGS,INDIKATORWERT,VGL_AB FROM m_indikatorwerte_".$Jahr_Auswahl." WHERE AGS = '99' AND ID_INDIKATOR = 'Z01AG' AND (VGL_AB = '0' OR VGL_AB <= '".$Jahr_Auswahl."') ORDER BY VGL_AB DESC;";  
			$Ergebnis_Akt_D = mysqli_query($Verbindung,$SQL_Akt_D_Z); 
			$Akt_D_m_Z = @mysqli_result($Ergebnis_Akt_D,0,'INDIKATORWERT');
			
			if($Akt_D_m_Z < 10) $Akt_D_m_Z = "0".$Akt_D_m_Z;
			$Akt_D = $Akt_D_m_Z." / ".$Akt_D_J_Z;
	
	//leere Werte für Update belegen, haben sonst keinen Einfluss)


//if(empty($Grundakt)||!isset($Grundakt)) {$Grundakt='0';}

//if(empty($Grundakt_KRS)||!isset($Grundakt_KRS)) {$Grundakt_KRS='0';}
//if(empty($UPD_Wert_BLD)||!isset($UPD_Wert_BLD)) {$UPD_Wert_BLD='0';}
//if(empty($Akt_D)||!isset($Akt_D)) {$Akt_D='0';}

	
	// Update der bestehenden Datensätze
	
	$SQL_DS_UPD = "UPDATE t_temp_indikatoren_tabelle 
	SET 
WERT = '".$UPD_Wert."',
	FEHLERCODE = '".$UPD_Fehlercode."',
	AKT = '".$Grundakt."',
	WERT_KRS = '".$UPD_Wert_KRS."',
	AKT_KRS = '".$Grundakt_KRS."',
	FEHLERCODE_KRS = '".$UPD_Fehlercode_KRS."',
		WERT_BLD = '".$UPD_Wert_BLD."',
				AKT_BLD = '".$Grundakt_BLD."',
	FEHLERCODE_BLD = '".$UPD_Fehlercode_BLD."',	
	
	WERT_BND = '".$Wert_D."',
	AKT_BND = '".$Akt_D."',
	FEHLERCODE_BND = '".$Fehlercode_D."'
	
	
	WHERE ID_INDIKATOR = '".$ID_INDIKATOR."'
	"	;
	$Ergebnis_DS_UPD = mysqli_query($Verbindung,$SQL_DS_UPD); 
	
	/*
		$SQL_DS_UPD = "UPDATE t_temp_indikatoren_tabelle 
	SET 
	



	



	
	
	WHERE ID_INDIKATOR = '".$ID_INDIKATOR."'";
	*/
	
	
	
	
	
	
	
	
	
	
	$i_ds++;
	$i_ds_color++;






}

// ---- Differenzen bilden und einsortieren
// Für übergeordnete Ebenen der konkreten Werte
$SQL_DS_UE_DIFF = "UPDATE t_temp_indikatoren_tabelle 
					SET 
					WERT_KRS_DIFF = WERT - WERT_KRS,
					WERT_BLD_DIFF = WERT - WERT_BLD,
					WERT_BND_DIFF = WERT - WERT_BND
					;";				
$Ergebnis_DS_UE_DIFF = mysqli_query($Verbindung,$SQL_DS_UE_DIFF);















// Begin des HTML Dokuments

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Gebietscharakteristik - IÖR Monitor</title>
<link href="../screen_viewer.css" rel="stylesheet" type="text/css" media="screen" />
<style type="text/css">

body {
	font-size:12px;
	font-family: Arial, Helvetica, sans-serif;
}
td {
	text-align: left;
	padding-top: 2px;
	padding-right: 10px;
	padding-bottom: 2px;
	padding-left: 10px;
	border: 1px solid #CCC;
	vertical-align: top;
	white-space: nowrap;
}

th {
	text-align: left;
	font-weight: bold;
	border: 1px solid #CCC;
	font-size: 14px;
	padding: 0px;
	vertical-align: top;
	background-color: #DFDFDF;
}
a:link {
	text-decoration: none;
	color: #333;
}
a {
	cursor: default;
	color:#333;
}
a:visited {
	text-decoration: none;
	color: #333;
}
a:hover {
	text-decoration: none;
	color: #333;
}
a:active {
	text-decoration: none;
	color: #333;
}
@media print {
	.nicht_im_print {display:none;}
}
@media screen {
	.nur_im_print {display:none;}
}

</style>
<script type="text/javascript">

function MM_jumpMenu(targ,selObj,restore){ //v3.0
  eval(targ+".location='"+selObj.options[selObj.selectedIndex].value+"'");
  if (restore) selObj.selectedIndex=0;
}

</script>
</head>
<body style="padding-left:35px;" class="body_unterseiten">
<a style="border:0px;" href="http://www.ioer-monitor.de" target="_blank">
<img src="../gfx/kopf_v2_unterseiten.png" width="100%" alt="Kopfgrafik" class="nur_im_print"/>
<img src="../gfx/kopf_v2_unterseiten.png" width="999" height="119" alt="Kopfgrafik" class="nicht_im_print"/>
</a>
<br />

<?php 
//echo $stichprobentestanz; 

?>

<strong>IÖR-Monitor©Leibniz-Institut für ökologische Raumentwicklung</strong>
<br />
<!--<div style="margin-top:5px;" class="nicht_im_print">
  <a href="tabelle_zur_karte_v3.php" target="_self" ><img src="../icons_viewer/back.png" alt="Zur&uuml;ck" /><br /> 
  &nbsp;&nbsp;zur&uuml;ck</a><br />
</div> -->
<br />

<table style="border-collapse:collapse; border:1px; width:950px;">
   <tr>
      <td colspan="2" style="border:none; padding:0px;">
      			<h2 style="margin-bottom:0px;">Übersicht aller Indikatorwerte für <?php 
					// Nennung des Typs der Raumeinheit im Titel
					if($_SESSION['Dokument']['Raumgliederung'] == "gem" and $ags != '99') echo $csv_RG = "Gemeinde";
					if(($_SESSION['Dokument']['Raumgliederung'] == "krs" or $_SESSION['Dokument']['Raumgliederung'] == "kfs" or $_SESSION['Dokument']['Raumgliederung'] == "lks")  and $ags != '99') echo $csv_RG = "Kreis";
					if($_SESSION['Dokument']['Raumgliederung'] == "bld" and $ags != '99') echo $csv_RG = "Bundesland";
				
				?>: <?php echo $csv_Name = $name; ?> <br />
      			  Zeitschnitt:
   			    <?php echo $Jahr_Auswahl; ?></h2> 
                AGS: <?php echo $ags; ?>           
   </td>
   </tr>
   <tr>
      <td style="border:none; padding:0px; border-collapse:collapse; vertical-align:bottom;" class="nicht_im_print">
      <br />
<br />

       <?php 
		if($_POST['csv'])
		{

			// $Dateiname = mt_rand().".csv";
			$Dateiname = $csv_Name.'_'.$csv_RG.'_'.$_SESSION['Dokument']['Jahr_Anzeige'].'_'.$_SESSION['Dokument']['Raumgliederung'].'_'.mt_rand(0,1000).'.csv';
			$tmpfname = "../temp/".$Dateiname;
			
			?>
          <span style="color:#060;">Die Datei wurde erstellt.</span><br />
          <a href="<?php echo $tmpfname; ?>" target="_blank" ><div class="button_standard_abschicken_a" style="background: #800000; color:#FFF; font-size:12px; margin-left:0px; width:260px; padding-top:2px; padding-bottom:2px; padding-left:10px; text-align:left;">Tabelle im CSV-Format hier downloaden</div></a><?php
		}
		else
		{
			?>
          <form action="#tabellenkopf" method="post">
            <input name="csv_button" type="submit" class="button_standard_abschicken_a" style="background-color:#BAD380; text-align:left; width:285px; font-size:12px; height:20px; padding:0px; padding-bottom:2px; padding-left:7px;" value="CSV-Tabellendatei für Download erstellen" />
            <input name="csv" type="hidden" value="erstellen" />
          </form>
          <?php 
		}
		
		?>
					 
      </td>
   </tr>
</table>
<br />

<table style="border-collapse:collapse;">
	<tr>
	  <th style="padding:10px; color:#444444;">Kategorie</th>
		<th style="padding:10px; color:#444444; width:250px;">
    		Indikator
    	</th>
        <?php 
		// nur Prüfern anzeigen
		if($_SESSION['Dokument']['ViewBerechtigung'] == "0")
		{ 
			?>
			<th style="padding:10px; color:#444444;">
				Indikator-ID
			</th>
			<?php 
		}
		?>
        <th style="padding:10px; color:#444444;text-align:right;">
    		Wert
    	</th>
        <th style="padding:10px; color:#444444;">
    		Einheit
    	</th>
        
    
		
		<?php 
		if($ags != '99')
		{
		?>
		
        <th style="padding:10px; color:#444444;text-align:left;">
	  		Werte übergeordneter <br />
		  	Raumeinheiten
					<table style="background:#FFFFFF; margin-top:5px;">
						
                        <tr>
				          <td style="background:#d4d4d4;"></td>
				          
                          <td style="font-style:normal; font-size:12px;">
                              Bundesrepublik
                          </td>
                          
				          
			            </tr>
                        
				        <?php 
                            // Werte für Bundesländer als übergeordnete Raumeinheit mit anzeigen wenn folgendes ausgewählt
                            if($_SESSION['Dokument']['Raumgliederung'] == "gem" 
                            or $_SESSION['Dokument']['Raumgliederung'] == "krs" 
                            or $_SESSION['Dokument']['Raumgliederung'] == "kfs" 
                            or $_SESSION['Dokument']['Raumgliederung'] == "lks")
                            {
                                ?>
                                <tr>
                                  <td style="background:#c1c1c1;"></td>
                                  <td style="font-style:normal; font-size:12px;">
								  		<?php echo $name_BLD; ?>
                                  </td>
                                </tr>
                                <?php 
                            }
                            // Werte für Kreise & Bundesländer als übergeordnete Raumeinheit mit anzeigen wenn folgendes ausgewählt
                            if($_SESSION['Dokument']['Raumgliederung'] == "gem")
                            {
                                ?>
                                <tr>
                                  <td style="background:#9c9c9c;"></td>
                                  <td style="font-style:normal; font-size:12px;">
                                     <?php echo $name_KRS; ?>
                                  </td>
                                </tr>
                                <?php 
                            }
                            ?>
				        <tr>
				          <td height="21" style="background:#DD9988;"></td>
				          <td style="font-style:normal; font-size:12px;">
                               <?php echo $name; ?>
                           </td>
			            </tr>
				   </table>			        
              
		</th>



        <th style="padding:10px; color:#444444;text-align:left;">
	  		Differenz zu
            übergeordneten 
              <br />
          Raumeinheiten
<table style="background:#FFFFFF; margin-top:5px;">
			        <tr>
			          <td style="background:#96b3d4;"></td>
			          <td style="background:#FFEEAA;"></td>
			          <td style="font-style:normal; font-size:12px;">
                                    Bundesrepublik
                      </td>
			          
		            </tr>
			        	<?php 
                            // Werete für Bundesländer als übergeordnete Raumeinheit mit anzeigen wenn folgendes ausgewählt
                            if($_SESSION['Dokument']['Raumgliederung'] == "gem" 
                            or $_SESSION['Dokument']['Raumgliederung'] == "krs" 
                            or $_SESSION['Dokument']['Raumgliederung'] == "kfs" 
                            or $_SESSION['Dokument']['Raumgliederung'] == "lks")
                            {
                                ?>
                                <tr>
                                  <td style="background:#77a0cd;"></td>
                                  <td style="background:#FFDD99;"></td>
                                  <td style="font-style:normal; font-size:12px;">
								  	<?php echo $name_BLD; ?>
								  </td>
                                  </tr>
                                <?php 
                            }
                            // Werete für Kreise & Bundesländer als übergeordnete Raumeinheit mit anzeigen wenn folgendes ausgewählt
                            if($_SESSION['Dokument']['Raumgliederung'] == "gem")
                            {
                                ?>
                                <tr>
                                  <td style="background:#6490c2;"></td>
                                  <td style="background:#FFCC88;"></td>
                                  <td style="font-style:normal; font-size:12px;">
								  	<?php echo $name_KRS; ?>
								  </td>
                                  </tr>
                                <?php 
                            }
                            ?>
			      </table>			    
              
		</th>
        
        <?php
		}
		
		
        /* 
		 
		// Werte für Kreise & Bundesländer als übergeordnete Raumeinheit mit anzeigen wenn folgendes ausgewählt
		if($_SESSION['Dokument']['Raumgliederung'] == "gem")
		{
		    ?>
			<th style="padding:10px; color:#444444;text-align:right;">
    			Wert f&uuml;r Kreis
			</th>
			<th style="padding:10px; color:#444444;text-align:right;">
    			Differenz zum Kreis </th>
	  <?php 
		}

        // Werte für Bundesländer als übergeordnete Raumeinheit mit anzeigen wenn folgendes ausgewählt
		if($_SESSION['Dokument']['Raumgliederung'] == "gem" 
		or $_SESSION['Dokument']['Raumgliederung'] == "krs" 
		or $_SESSION['Dokument']['Raumgliederung'] == "kfs" 
		or $_SESSION['Dokument']['Raumgliederung'] == "lks")
		{
			?>
			<th style="padding:10px; color:#444444;text-align:right;">
    			Wert f&uuml;r Bundesland
			</th>
			<th style="padding:10px; color:#444444;text-align:right;">
				Differenz zum Bundesland
			</th>
			<?php 
		}
		?>
        <th style="padding:10px; color:#444444;text-align:right;">
    		Wert f&uuml;r Bundesrepublik
    	</th>
        <th style="padding:10px; color:#444444;text-align:right;">
    		Differenz zur Bundesrepublik
    	</th> 
        <?php 
        */
        ?>
        
        
        
        <th style="padding:10px; color:#444444;text-align:left; width:200px;">
    		Mittl. Grund-<br />
	    Aktualität 
        </th>
        
  </tr>
    

    
   <?php
   
   // Daten
   // --------------------------------------------------------------------------
   
   
   // Sortierung anpassen ???
   if($_SESSION['Tabellen_Sortierung'])
   {
		$SQL_ORDER_BY = " ORDER BY ".$_SESSION['Tabellen_Sortierung']."_S_DIFF ".$_SESSION['Tabellen_Sortierung_asc_desc'];   
   }
   
   	$SQL_DS = "SELECT * FROM t_temp_indikatoren_tabelle";
   	//$SQL_DS = "SELECT *, (".$_SESSION['Tabellen_Sortierung']."/".$_SESSION['Tabellen_Sortierung']."_DIFF) AS ".$_SESSION['Tabellen_Sortierung']."_S_DIFF FROM t_temp_indikatoren_tabelle ".$SQL_ORDER_BY;
	$Ergebnis_DS = mysqli_query($Verbindung,$SQL_DS); 
	
	
	
	$i_ds = 0;
	$i_ds_color = 0;
	while($ID_INDIKATOR = @mysqli_result($Ergebnis_DS,$i_ds,'ID_INDIKATOR'))
	{

		// Kategorie jeweils nur einmal beim jeweils 1. Datensatz anzeigen
		$KAT = "";
		$Border_KAT = "";
		if($KAT_alt != utf8_encode(@mysqli_result($Ergebnis_DS,$i_ds,'KATEGORIE')))
		{
				$i_ds_color = 0;
				$KAT = utf8_encode(@mysqli_result($Ergebnis_DS,$i_ds,'KATEGORIE'));
				// Tabellenzeile einfügen
				?>
				<tr style="background:#CCCCCC; height:5px;">
                  <td></td>
                  <td></td>
                   <?php 
					// nur Prüfern anzeigen
					if($_SESSION['Dokument']['ViewBerechtigung'] == "0")
					{ 
						?>
						<td></td>
						<?php 
					}
					?>
                  <td></td>
                  <td></td>
                  <?php 
					if($ags != '99')
					{
						?>
                      <td></td>
                      <td></td>
                  		<?php 
					}
				  ?>
                  <td></td>
                </tr>
				<?php 
				
		}
		$KAT_alt = utf8_encode(@mysqli_result($Ergebnis_DS,$i_ds,'KATEGORIE'));
		?>
        <tr style="background:<?php if($i_ds_color % 5 == 0) { echo '#EEEEEE'; }else{ echo '#FFFFFF'; }?>;">
          <td style="width:50px; white-space:normal; <?php echo $Border_KAT; ?>">
			<?php 
			echo $KAT;
			//echo $i_ds_color; 
			$CSV[$i_csv = ($i_ds+1)]['Kategorie'] = $KAT_alt; 
			
			?>
            
          </td>
          <td>
                <div style="width:250px; white-space:normal;">
					<?php echo $CSV[$i_csv = ($i_ds+1)]['Indikator'] = utf8_encode(@mysqli_result($Ergebnis_DS,$i_ds,'IND_NAME')); 
					
					?>
                </div>
          </td>
            <?php 
			// nur Prüfern anzeigen
			if($_SESSION['Dokument']['ViewBerechtigung'] == "0")
			{ 
				?>
				<td>
					<?php echo $CSV[$i_csv = ($i_ds+1)]['Indikator_ID'] = @mysqli_result($Ergebnis_DS,$i_ds,'ID_INDIKATOR'); ?>
				</td>
             	<?php 
			}
			?>
            <td style="text-align:right;">
                <?php echo $CSV[$i_csv = ($i_ds+1)]['Wert'] = number_format(@mysqli_result($Ergebnis_DS,$i_ds,'WERT'),$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.'); ?>
            </td>
            <td>
                <?php echo $CSV[$i_csv = ($i_ds+1)]['Einheit'] = utf8_encode(@mysqli_result($Ergebnis_DS,$i_ds,'EINHEIT')); ?>
            </td>
           
		<?php 
		if($ags != '99')
		{
		?>
		  <td style="text-align:right; padding-right:50px;">
				<?php   
					// Differenzbetrag: @mysqli_result($Ergebnis_DS,$i_ds,'WERT_KRS_DIFF')
					
					
					
					// Feststellen wo der Maximalwert liegt aller Raumeinheiten
					$Max = abs($Wert = @mysqli_result($Ergebnis_DS,$i_ds,'WERT'));
					if($_SESSION['Dokument']['Raumgliederung'] == "gem") 
					{
						if($Max < abs($Wert_KRS = @mysqli_result($Ergebnis_DS,$i_ds,'WERT_KRS'))) $Max = abs($Wert_KRS); 
					}
					if($_SESSION['Dokument']['Raumgliederung'] == "gem" 
					or $_SESSION['Dokument']['Raumgliederung'] == "krs" 
					or $_SESSION['Dokument']['Raumgliederung'] == "kfs" 
					or $_SESSION['Dokument']['Raumgliederung'] == "lks")
					{
						if($Max < abs($Wert_BLD = @mysqli_result($Ergebnis_DS,$i_ds,'WERT_BLD'))) $Max = abs($Wert_BLD);
					}
					if($Max < abs($Wert_BND = @mysqli_result($Ergebnis_DS,$i_ds,'WERT_BND'))) $Max = abs($Wert_BND);
					$Max = abs($Max); // positiver Wert
					
					
					
					
					// Breite der Box (Festlegung)
					$Boxbreite = "100";
					
					// !!! Hinweis: Negative Werte werden direkt am rechten Rand gefangen, da dieser Fall nur extrem selten eintreten wird und eine Zweiteilung der Box nur irritiert
					
					if(!$FC) 
					{ 
					
					
						// Box für Deutschland
						// ----------------------------------------------
	
						// Breite der Box in bezug zum Max-Wertn (auf 49.5% der Boxbreite wegen +- )
						$Box_width_BND = abs(99.5 * $Wert_BND / $Max); 
						
						?>
						<div style="width:<?php echo $Boxbreite; ?>px; height:15px; padding:0px; position:relative; border:solid 1px #999; margin-bottom:1px;">
							
							<?php	

								// Positionierung der Box von der Mitte aus
								if($Box_width_BND < 0) 
								{
									// Dynamisch den Platz vor der Box berechnen und als margin belegen
									$Box_Margin_left = 100 + $Box_width_BND; 
									if(!$Box_width_BND) $Box_Margin_left = "0"; // Falls Variable leer.... vermeidet XHTML Fehler
									$Box_BackColor = "#d4d4d4";
									$Textpos_align = "left";
									$Border_seite = "right";
								}
								else
								{
									$Box_Margin_left = 0; // bei >0 ist 50% immer korrekt
									$Box_BackColor = "#d4d4d4";
									$Textpos_align = "right";
									$Border_seite = "left";
								}
								?>
								<div style="height:100%;width:<?php echo abs($Box_width_BND); ?>%; margin-left:<?php echo $Box_Margin_left; ?>%; background-color:<?php echo $Box_BackColor; ?>; 
													position:absolute; z-index:5; border-<?php echo $Border_seite; ?>:#666 solid 0px;">
										
								</div>
								<div style="width:<?php echo $BB = ($Boxbreite-10); ?>px; padding-right:5px; padding-left:5px; position:absolute; z-index:10; text-align:<?php echo $Textpos_align; ?>;">
										<?php 
									
									if($_SESSION['Dokument']['ViewBerechtigung'] == "0")
									{ 
										echo $CSV[$i_csv = ($i_ds+1)]['Wert_Bund'] = number_format($Wert_BND,$rndg_p=($_SESSION['Dokument']['Fuellung']['Rundung']+1), ',', '.');
										$CSV[0]['Wert_bund'] = 'Wert_Bund';
									}
									else
									{ 
										echo $CSV[$i_csv = ($i_ds+1)]['Wert_Bund'] = number_format($Wert_BND,$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.');
										$CSV[0]['Wert_bund'] = 'Wert_Bund';
									}
									?>
	 							</div>
                        </div>
						<?php 
    
                        
                        
                        // Box für Bundesland
                        // ----------------------------------------------
                        if($_SESSION['Dokument']['Raumgliederung'] == "gem" 
                        or $_SESSION['Dokument']['Raumgliederung'] == "krs" 
                        or $_SESSION['Dokument']['Raumgliederung'] == "kfs" 
                        or $_SESSION['Dokument']['Raumgliederung'] == "lks")
                        {
                                // Breite der Box in bezug zum Max-Wertn (auf 49.5% der Boxbreite wegen +- )
                                $Box_width_BLD = abs(99.5 * $Wert_BLD / $Max); 
                                
                                // Box für Übergeordnete RE
                                ?>
                                <div style="width:<?php echo $Boxbreite; ?>px; height:15px; padding:0px; position:relative; border:solid 1px #999; margin-bottom:1px;">
                                
                                <?php	
    
                                    // Positionierung der Box von der Mitte aus
                                    if($Box_width_BLD < 0) 
                                    {
                                        // Dynamisch den Platz vor der Box berechnen und als margin belegen
                                        $Box_Margin_left = 100 + $Box_width_BLD; 
                                        if(!$Box_width_BLD) $Box_Margin_left = "0"; // Falls Variable leer.... vermeidet XHTML Fehler
                                        $Box_BackColor = "#c1c1c1";
                                        $Textpos_align = "left";
                                        $Border_seite = "right";
                                    }
                                    else
                                    {
                                        $Box_Margin_left = 0; // bei >0 ist 50% immer korrekt
                                        $Box_BackColor = "#c1c1c1";
                                        $Textpos_align = "right";
                                        $Border_seite = "left";
                                    }
                                    ?>
                                    <div style="height:100%;width:<?php echo abs($Box_width_BLD); ?>%; margin-left:<?php echo $Box_Margin_left; ?>%; background-color:<?php echo $Box_BackColor; ?>; 
                                                        position:absolute; z-index:5; border-<?php echo $Border_seite; ?>:#666 solid 0px;">
                                            
                                    </div>
                                    <div style="width:<?php echo $BB = ($Boxbreite-10); ?>px; padding-right:5px; padding-left:5px; position:absolute; z-index:10; text-align:<?php echo $Textpos_align; ?>;">
                                            <?php 
                                        
                                        if($_SESSION['Dokument']['ViewBerechtigung'] == "0")
                                        { 
                                            echo $CSV[$i_csv = ($i_ds+1)]['Wert_Land'] = number_format($Wert_BLD,$rndg_p=($_SESSION['Dokument']['Fuellung']['Rundung']+1), ',', '.');
											$CSV[0]['Wert_land'] = 'Wert_Land';
                                        }
                                        else
                                        { 
                                            echo $CSV[$i_csv = ($i_ds+1)]['Wert_Land'] = number_format($Wert_BLD,$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.');
											$CSV[0]['Wert_land'] = 'Wert_Land';
                                        }
                                        ?>
                                    </div>
                                </div>
                                
                            <?php 
                        }								
                            
                            
                            
                        // Box für Kreis
                        // ----------------------------------------------
                        if($_SESSION['Dokument']['Raumgliederung'] == "gem") 
                        {
                                // Breite der Box in bezug zum Max-Wertn (auf 49.5% der Boxbreite wegen +- )
                                $Box_width_KRS = abs(99.5 * $Wert_KRS / $Max); 
                                
                                // Box für Übergeordnete RE
                                ?>
                                <div style="width:<?php echo $Boxbreite; ?>px; height:15px; padding:0px; position:relative; border:solid 0px #999; margin-bottom:1px;">
                                
                                <?php	
    
                                    // Positionierung der Box von der Mitte aus
                                    if($Box_width_KRS < 0) 
                                    {
                                        // Dynamisch den Platz vor der Box berechnen und als margin belegen
                                        $Box_Margin_left = 100 + $Box_width_KRS; 
                                        if(!$Box_width_KRS) $Box_Margin_left = "0"; // Falls Variable leer.... vermeidet XHTML Fehler
                                        $Box_BackColor = "#9c9c9c";
                                        $Textpos_align = "left";
                                        $Border_seite = "right";
                                    }
                                    else
                                    {
                                        $Box_Margin_left = 0; // bei >0 ist 50% immer korrekt
                                        $Box_BackColor = "#9c9c9c";
                                        $Textpos_align = "right";
                                        $Border_seite = "left";
                                    }
                                    ?>
                                    <div style="height:100%;width:<?php echo abs($Box_width_KRS); ?>%; margin-left:<?php echo $Box_Margin_left; ?>%; background-color:<?php echo $Box_BackColor; ?>; 
                                                        position:absolute; z-index:5; border-<?php echo $Border_seite; ?>:#666 solid 0px;">
                                            
                                    </div>
                                    <div style="width:<?php echo $BB = ($Boxbreite-10); ?>px; padding-right:5px; padding-left:5px; position:absolute; z-index:10; text-align:<?php echo $Textpos_align; ?>;">
                                            <?php 
                                        
                                        if($_SESSION['Dokument']['ViewBerechtigung'] == "0")
                                        { 
                                            echo $CSV[$i_csv = ($i_ds+1)]['Wert_Kreis'] = number_format($Wert_KRS,$rndg_p=($_SESSION['Dokument']['Fuellung']['Rundung']+1), ',', '.');
											$CSV[0]['Wert_kreis'] = 'Wert_Kreis';
                                        }
                                        else
                                        { 
                                            echo $CSV[$i_csv = ($i_ds+1)]['Wert_Kreis'] = number_format($Wert_KRS,$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.');
											$CSV[0]['Wert_kreis'] = 'Wert_Kreis';
                                        }
                                        ?>
                                    </div>
                                </div>
                                
                <?php 
                        }
                                
        
                                
                        // Box für direkt gewählte Raumeinheit
                        // ----------------------------------------------
                        // Breite der Box in bezug zum Max-Wertn (auf 49.5% der Boxbreite wegen +- )
                        $Box_width_W = abs(99.5 * $Wert / $Max); 
                        
                        // Box für Übergeordnete RE
                        ?>
                        <div style="width:<?php echo $Boxbreite; ?>px; height:15px; padding:0px; position:relative; border:solid 1px #999; margin-bottom:1px;">
                        <?php	
    
                            // Positionierung der Box von der Mitte aus
                            if($Box_width_W < 0) 
                            {
                                // Dynamisch den Platz vor der Box berechnen und als margin belegen
                                $Box_Margin_left = 100 + $Box_width_W; 
                                if(!$Box_width_W) $Box_Margin_left = "0"; // Falls Variable leer.... vermeidet XHTML Fehler
                                $Box_BackColor = "#DD9988";
                                $Textpos_align = "left";
                                $Border_seite = "right";
                            }
                            else
                            {
                                $Box_Margin_left = 0; // bei >0 ist 50% immer korrekt
                                $Box_BackColor = "#DD9988";
                                $Textpos_align = "right";
                                $Border_seite = "left";
                            }
                            ?>
                            <div style="height:100%;width:<?php echo abs($Box_width_W); ?>%; margin-left:<?php echo $Box_Margin_left; ?>%; background-color:<?php echo $Box_BackColor; ?>; 
                                                position:absolute; z-index:5; border-<?php echo $Border_seite; ?>:#666 solid 0px;">
                                    
                            </div>
                            <div style="width:<?php echo $BB = ($Boxbreite-10); ?>px; padding-right:5px; padding-left:5px; position:absolute; z-index:10; text-align:<?php echo $Textpos_align; ?>;">
                                    <?php 
                                
                                if($_SESSION['Dokument']['ViewBerechtigung'] == "0")
                                { 
                                    echo number_format($Wert,$rndg_p=($_SESSION['Dokument']['Fuellung']['Rundung']+1), ',', '.');
                                }
                                else
                                { 
                                    echo number_format($Wert,$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.');
                                }
                                ?>
                            </div>
                        </div>
                        <?php 
					}
					?>
						
                        
			</td>
            
            
            
            
            
            
            
            
            
            
            
            
            
<td style="text-align:right; padding-right:50px;">
				<?php   
					// Differenzbetrag: @mysqli_result($Ergebnis_DS,$i_ds,'WERT_KRS_DIFF')
					
					
					
					// Feststellen wo des Differenz-Maximalwerts zu allen übergeordneten Raumeinheiten
					$Diff_Max = abs($Wert_BND = @mysqli_result($Ergebnis_DS,$i_ds,'WERT_BND_DIFF'));
					if($_SESSION['Dokument']['Raumgliederung'] == "gem") 
					{
						if($Diff_Max < abs($Wert_KRS = @mysqli_result($Ergebnis_DS,$i_ds,'WERT_KRS_DIFF'))) $Diff_Max = abs($Wert_KRS); 
					}
					if($_SESSION['Dokument']['Raumgliederung'] == "gem" 
					or $_SESSION['Dokument']['Raumgliederung'] == "krs" 
					or $_SESSION['Dokument']['Raumgliederung'] == "kfs" 
					or $_SESSION['Dokument']['Raumgliederung'] == "lks")
					{
						if($Diff_Max < abs($Wert_BLD = @mysqli_result($Ergebnis_DS,$i_ds,'WERT_BLD_DIFF'))) $Diff_Max = abs($Wert_BLD);
					}
					
					$Diff_Max = abs($Diff_Max); // positiver Wert
					 
					
					// Nutzung des Max aus den Absolutwerten => bessere Anschaulichkeit der Werteunterschied-Ausprägung
					$Diff_Max = $Max;
					
					
					// Breite der Box (Festlegung)
					$Boxbreite = "200";
					
					if(!$FC) 
					{ 
					
					
						// Box für Deutschland
						// ----------------------------------------------
	
						// Breite der Box in bezug zum Max-Wertn (auf 49.5% der Boxbreite wegen +- )
						$Box_width_BND = 49.5 * $Wert_BND / $Diff_Max; 
						
						?>
						<div style="width:<?php echo $Boxbreite; ?>px; height:15px; padding:0px; position:relative; border:solid 1px #999; margin-bottom:1px;">
							
							<?php	

								// Positionierung der Box von der Mitte aus
								if($Box_width_BND < 0) 
								{
									// Dynamisch den Platz vor der Box berechnen und als margin belegen
									$Box_Margin_left = 50 + $Box_width_BND; 
									if(!$Box_width_BND) $Box_Margin_left = "0"; // Falls Variable leer.... vermeidet XHTML Fehler
									$Box_BackColor = "#96b3d4";
									$Textpos_align = "left";
									$Border_seite = "right";
								}
								else
								{
									$Box_Margin_left = 50; // bei >0 ist 50% immer korrekt
									$Box_BackColor = "#FFEEAA";
									$Textpos_align = "right";
									$Border_seite = "left";
								}
								?>
								<div style="height:100%;width:<?php echo abs($Box_width_BND); ?>%; margin-left:<?php echo $Box_Margin_left; ?>%; background-color:<?php echo $Box_BackColor; ?>; 
													position:absolute; z-index:5; border-<?php echo $Border_seite; ?>:#666 solid 1px;">
										
								</div>
								<div style="width:<?php echo $BB = ($Boxbreite-10); ?>px; padding-right:5px; padding-left:5px; position:absolute; z-index:10; text-align:<?php echo $Textpos_align; ?>;">
								  <?php 
									
									if($_SESSION['Dokument']['ViewBerechtigung'] == "0")
									{ 
										echo number_format($Wert_BND,$rndg_p=($_SESSION['Dokument']['Fuellung']['Rundung']+1), ',', '.');
									}
									else
									{ 
										echo number_format($Wert_BND,$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.');
									}
									?>
								</div>
                        </div>
						<?php 
    
                        
                        
                        // Box für Bundesland
                        // ----------------------------------------------
                        if($_SESSION['Dokument']['Raumgliederung'] == "gem" 
                        or $_SESSION['Dokument']['Raumgliederung'] == "krs" 
                        or $_SESSION['Dokument']['Raumgliederung'] == "kfs" 
                        or $_SESSION['Dokument']['Raumgliederung'] == "lks")
                        {
                                // Breite der Box in bezug zum Max-Wertn (auf 49.5% der Boxbreite wegen +- )
                                $Box_width_BLD = 49.5 * $Wert_BLD / $Diff_Max; 
                                
                                // Box für Übergeordnete RE
                                ?>
                                <div style="width:<?php echo $Boxbreite; ?>px; height:15px; padding:0px; position:relative; border:solid 1px #999; margin-bottom:1px;">
                                
                                <?php	
    
                                    // Positionierung der Box von der Mitte aus
                                    if($Box_width_BLD < 0) 
                                    {
                                        // Dynamisch den Platz vor der Box berechnen und als margin belegen
                                        $Box_Margin_left = 50 + $Box_width_BLD; 
                                        if(!$Box_width_BLD) $Box_Margin_left = "0"; // Falls Variable leer.... vermeidet XHTML Fehler
                                        $Box_BackColor = "#77a0cd";
                                        $Textpos_align = "left";
                                        $Border_seite = "right";
                                    }
                                    else
                                    {
                                        $Box_Margin_left = 50; // bei >0 ist 50% immer korrekt
                                        $Box_BackColor = "#FFDD99";
                                        $Textpos_align = "right";
                                        $Border_seite = "left";
                                    }
                                    ?>
                                    <div style="height:100%;width:<?php echo abs($Box_width_BLD); ?>%; margin-left:<?php echo $Box_Margin_left; ?>%; background-color:<?php echo $Box_BackColor; ?>; 
                                                        position:absolute; z-index:5; border-<?php echo $Border_seite; ?>:#666 solid 1px;">
                                            
                                    </div>
                                    <div style="width:<?php echo $BB = ($Boxbreite-10); ?>px; padding-right:5px; padding-left:5px; position:absolute; z-index:10; text-align:<?php echo $Textpos_align; ?>;">
                                            <?php 
                                        
                                        if($_SESSION['Dokument']['ViewBerechtigung'] == "0")
                                        { 
                                            echo number_format($Wert_BLD,$rndg_p=($_SESSION['Dokument']['Fuellung']['Rundung']+1), ',', '.');
                                        }
                                        else
                                        { 
                                            echo number_format($Wert_BLD,$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.');
                                        }
                                        ?>
                                    </div>
                                </div>
                                
                            <?php 
                        }								
                            
                            
                            
                        // Box für Kreis
                        // ----------------------------------------------
                        if($_SESSION['Dokument']['Raumgliederung'] == "gem") 
                        {
                                // Breite der Box in bezug zum Max-Wertn (auf 49.5% der Boxbreite wegen +- )
                                $Box_width_KRS = 49.5 * $Wert_KRS / $Diff_Max; 
                                
                                // Box für Übergeordnete RE
                                ?>
                                <div style="width:<?php echo $Boxbreite; ?>px; height:15px; padding:0px; position:relative; border:solid 1px #999; margin-bottom:1px;">
                                
                                <?php	
    
                                    // Positionierung der Box von der Mitte aus
                                    if($Box_width_KRS < 0) 
                                    {
                                        // Dynamisch den Platz vor der Box berechnen und als margin belegen
                                        $Box_Margin_left = 50 + $Box_width_KRS; 
                                        if(!$Box_width_KRS) $Box_Margin_left = "0"; // Falls Variable leer.... vermeidet XHTML Fehler
                                        $Box_BackColor = "#6490c2";
                                        $Textpos_align = "left";
                                        $Border_seite = "right";
                                    }
                                    else
                                    {
                                        $Box_Margin_left = 50; // bei >0 ist 50% immer korrekt
                                        $Box_BackColor = "#FFCC88";
                                        $Textpos_align = "right";
                                        $Border_seite = "left";
                                    }
                                    ?>
                                    <div style="height:100%;width:<?php echo abs($Box_width_KRS); ?>%; margin-left:<?php echo $Box_Margin_left; ?>%; background-color:<?php echo $Box_BackColor; ?>; 
                                                        position:absolute; z-index:5; border-<?php echo $Border_seite; ?>:#666 solid 1px;">
                                            
                                    </div>
                                    <div style="width:<?php echo $BB = ($Boxbreite-10); ?>px; padding-right:5px; padding-left:5px; position:absolute; z-index:10; text-align:<?php echo $Textpos_align; ?>;">
                                            <?php 
                                        
                                        if($_SESSION['Dokument']['ViewBerechtigung'] == "0")
                                        { 
                                            echo number_format($Wert_KRS,$rndg_p=($_SESSION['Dokument']['Fuellung']['Rundung']+1), ',', '.');
                                        }
                                        else
                                        { 
                                            echo number_format($Wert_KRS,$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.');
                                        }
                                        ?>
                                    </div>
                                </div>
                                
                            <?php 
                        }
					}
					?>
						
                        
			</td>
            

            
            
            <?php 
			
		} // Ende BRD Ausblendung ($ags == '99')
			
			
			
			
			
			
            /* 
            
			if($_SESSION['Dokument']['Raumgliederung'] == "gem" )
			{
				?>
                <td style="text-align:right;">
							<?php echo  number_format(@mysqli_result($Ergebnis_DS,$i_ds,'WERT_KRS'),$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.'); ?>
				</td>			
				<td style="text-align:right;">
							<?php echo  number_format(@mysqli_result($Ergebnis_DS,$i_ds,'WERT_KRS_DIFF'),$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.'); ?>
				</td>
				<?php 
			}
			
			// Werte für Bundesländer als übergeordnete Raumeinheit mit anzeigen wenn folgendes ausgewählt
			if($_SESSION['Dokument']['Raumgliederung'] == "gem" 
			or $_SESSION['Dokument']['Raumgliederung'] == "krs" 
			or $_SESSION['Dokument']['Raumgliederung'] == "kfs" 
			or $_SESSION['Dokument']['Raumgliederung'] == "lks")
			{
				?>
				<td style="text-align:right;">
						<?php echo  number_format(@mysqli_result($Ergebnis_DS,$i_ds,'WERT_BLD'),$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.'); ?>
				</td>
				<td style="text-align:right;">
						<?php echo  number_format(@mysqli_result($Ergebnis_DS,$i_ds,'WERT_BLD_DIFF'),$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.'); ?>
				</td>
				<?php 
			}
			?>
            <td style="text-align:right;">
                    <?php echo  number_format(@mysqli_result($Ergebnis_DS,$i_ds,'WERT_BND'),$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.'); ?>
            </td>
            <td style="text-align:right;">
                    <?php echo  number_format(@mysqli_result($Ergebnis_DS,$i_ds,'WERT_BND_DIFF'),$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.'); ?>
            </td> 
			<?php 
			
            */
			?>
            
            <td style="text-align:left;">
				
                
				<?php 
				// Wenn Aktualität vorgedsehen, dann hier für alle Raumebenen ausgeben
				if(!@mysqli_result($Ergebnis_DS,$i_ds,'AKT_IGNORE'))
				{
					
					if($ags != '99')
					{
					
						// Akt. für BND
						echo "Bundesrepublik: ".@mysqli_result($Ergebnis_DS,$i_ds,'AKT_BND')."<br />"; 
						
						// Akt. für BLD
						if($_SESSION['Dokument']['Raumgliederung'] == "gem")
						{
							echo $name_KRS.": ".@mysqli_result($Ergebnis_DS,$i_ds,'AKT_KRS')."<br />"; 
						}
						
						// Akt. für Kreis
						if($_SESSION['Dokument']['Raumgliederung'] == "gem" 
						or $_SESSION['Dokument']['Raumgliederung'] == "krs" 
						or $_SESSION['Dokument']['Raumgliederung'] == "kfs" 
						or $_SESSION['Dokument']['Raumgliederung'] == "lks")
						{
							echo $name_BLD.": ".@mysqli_result($Ergebnis_DS,$i_ds,'AKT_BLD')."<br />"; 
						}
						// Akt. für Wert
						echo $name.": <strong>".@mysqli_result($Ergebnis_DS,$i_ds,'AKT')."<br /></strong>"; 
						$CSV[$i_csv = ($i_ds+1)]['Aktualitaet'] = @mysqli_result($Ergebnis_DS,$i_ds,'AKT');
					}
					else
					{
						// Akt. 
						echo @mysqli_result($Ergebnis_DS,$i_ds,'AKT'); 
						$CSV[$i_csv = ($i_ds+1)]['Aktualitaet'] = @mysqli_result($Ergebnis_DS,$i_ds,'AKT');
					}
				}
				else
				{
					echo "k.A.";
					$CSV[$i_csv = ($i_ds+1)]['Aktualitaet'] = "k.A.";
				}
				
				?>
                
                
                
            </td>
        
        </tr>
		
        
		<?php 
		
   		$i_ds++;
		$i_ds_color++; // Zählung für Farbhinterlegung
	}
   ?>  
   
            
</table> 
<br />
<!--<div style="margin-top:5px;" class="nicht_im_print">
	<a href="tabelle_zur_karte_v3.php" target="_self" ><img src="../icons_viewer/back.png" alt="Zur&uuml;ck" /><br /> 
	&nbsp;&nbsp;zur&uuml;ck</a><br />
</div> -->
<br />
<br />
<?php 



// CSV-Datei speichern
if($_POST['csv'])
{
	
	
	// csv-Array komplettieren
	$CSV[0]['Aktualitaet'] = 'Grundaktualitaet';

	
	// $Dateiname = mt_rand().".csv";
	//$Dateiname = $csv_Name.'_'.$csv_RG.'_'.$_SESSION['Dokument']['Jahr_Anzeige'].'_'.$_SESSION['Dokument']['Raumgliederung'].'_'.mt_rand(0,1000).'.csv';
	//$tmpfname = "../temp/".$Dateiname;
	
	// Löschen von Dateien die älter als eine Stunde sind 3600(s) Abweichung von der Systemzeit
	$Pfad = '../temp/';
	$Verzeichnis = opendir($Pfad );
	while($Datei_loesch = readdir($Verzeichnis))
	{
		// Nur echte Dateien löschen
		if (is_file($Pfad.$Datei_loesch) and (filemtime($Pfad.$Datei_loesch) + 3600) < time()) 
		{
			unlink($Pfad.$Datei_loesch);
			// echo $Pfad.$Datei_loesch.' '.filemtime($Pfad.$Datei_loesch).' ';
		}
		
	}
	closedir($Verzeichnis);
	
		
	// Klasse zur Umwandlung eines Array in eine CSV-Datei
	class Format 
	{
	   static public function arr_to_csv_line($arr) {
		  $line = array();
		  foreach ($arr as $v) {
			$line[] = is_array($v) ? self::arr_to_csv_line($v) : '"' . str_replace('"', '""', $v) . '"';
		  }
		  return implode(";", $line);
	   }
	
	   static public function arr_to_csv($arr) {
		  $lines = array();
		  foreach ($arr as $v) {
			$lines[] = self::arr_to_csv_line($v);
		  }
		  return implode("\n", $lines);
	   }       
	}
/* 	
	// umsortieren des CSV-Array durch kopieren in neues Array
	foreach($CSV as $CSV_Zeile) 
	{
		$CSV_Inhalt_sort[] = $CSV_Zeile;
	}
	?>
	<pre>
    <?php print_r($CSV); ?>
    <br />
<br />
<br />
<?php print_r($CSV_Inhalt_sort); ?>
    </pre>
	<?php  */
	
	$CSV_Daten = new Format;
	$CSV_Inhalt = utf8_decode($CSV_Daten -> arr_to_csv($CSV));
	
	// ... wird am Programm-Anfang generiert um Downloadfeld bereitstellen zu können:    $Dateiname = mt_rand().".csv";	// besser mit Indikator-Jahr usw. im Namen benennen	?
	// ... $tmpfname = "../temp/".$Dateiname;
	
	
	// Schreiben in Datei		
	$fp = fopen($tmpfname, "w+");
	fwrite($fp,$CSV_Inhalt);
	fclose($fp);
}
?>

</body>
</html>