<?php 
session_start(); // Sitzung starten/ wieder aufnehmen

// Memory-Limit erweitern
ini_set('memory_limit', '232M');
//ini_set('max_execution_time', '500');

include("../includes_classes/verbindung_mysqli.php");
include("../includes_classes/implode_explode.php");




// Einstellg bezügl MITTLERE_AKTUALITAET_IGNORE erfassen
$SQL_Indikator_Info = "SELECT * FROM m_indikatoren WHERE ID_INDIKATOR='".$_SESSION['Dokument']['Fuellung']['Indikator']."'";
$Ergebnis_Indikator_Info = mysqli_query($Verbindung,$SQL_Indikator_Info);
$MITTLERE_AKTUALITAET_IGNORE = utf8_encode(@mysqli_result($Ergebnis_Indikator_Info,0,'MITTLERE_AKTUALITAET_IGNORE'));


// Voreinstellungen
$akt=1; // Aktualität anzeigen <= muss später aus DB kommen

// Kartenanzeige
if($_POST['karteanzeige'])
{
	if($_POST['karte'])
	{
		$_SESSION['Tabelle']['Karte'] = '1';
	}
	else
	{
		$_SESSION['Tabelle']['Karte'] = '0';
	}
}




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
		$_SESSION['Tabellen_Sortierung'] = "NAME"; 
		$_SESSION['Tabellen_Sortierung_asc_desc'] = "ASC";
	}
}






// Ein-Ausblenden der Übergeordneten Raumeinheiten

// Kreise
if($_POST['UERE_KRS']) 
{
	$_SESSION['Tabelle']['UERE_KRS'] = '1';
}
else
{
	// setze nur NUll, wenn die Anweisung wirklich aus dem Formular kommt (d.h. wenn $_POST['Zeitschnitt_UERE_Formular'] übergeben wurde)
	if($_POST['Zeitschnitt_UERE_Formular']) $_SESSION['Tabelle']['UERE_KRS'] = '0';
}

// Bundesländer
if($_POST['UERE_BLD']) 
{
	$_SESSION['Tabelle']['UERE_BLD'] = '1';
}
else
{
	// setze nur NUll, wenn die Anweisung wirklich aus dem Formular kommt (d.h. wenn $_POST['Zeitschnitt_UERE_Formular'] übergeben wurde)
	if($_POST['Zeitschnitt_UERE_Formular']) $_SESSION['Tabelle']['UERE_BLD'] = '0';
}

// Bund
if($_POST['UERE_BND']) 
{
	$_SESSION['Tabelle']['UERE_BND'] = '1';
}
else
{
	// setze nur NUll, wenn die Anweisung wirklich aus dem Formular kommt (d.h. wenn $_POST['Zeitschnitt_UERE_Formular'] übergeben wurde)
	if($_POST['Zeitschnitt_UERE_Formular']) $_SESSION['Tabelle']['UERE_BND'] = '0';
}




// Alle ÜE ein- oder ausschalten, in Abhängigkeit von Unterauswahlen und vorherigen Zustand
if($_POST['Zeitschnitt_UERE_Formular'])
{
	if($_POST['UERE']) 
	{
		if(!$_SESSION['Tabelle']['UERE'])
		{
			$_SESSION['Tabelle']['UERE'] = '1';
			$_SESSION['Tabelle']['UERE_KRS'] = '1';
			$_SESSION['Tabelle']['UERE_BLD'] = '1';
			$_SESSION['Tabelle']['UERE_BND'] = '1';
		}
	}
	else
	{
		if($_SESSION['Tabelle']['UERE'])
		{
			$_SESSION['Tabelle']['UERE'] = '0';
			$_SESSION['Tabelle']['UERE_KRS'] = '0';
			$_SESSION['Tabelle']['UERE_BLD'] = '0';
			$_SESSION['Tabelle']['UERE_BND'] = '0';
		}
	}
}
// schalten und ausgrauen wenn nur Teilauswahl getätigt
if($_SESSION['Tabelle']['UERE_KRS'] or $_SESSION['Tabelle']['UERE_BLD'] or $_SESSION['Tabelle']['UERE_BND']) 
{ 
	$_SESSION['Tabelle']['UERE'] = '1'; 
}
else
{
	$_SESSION['Tabelle']['UERE'] = '0'; 
}





// Vergleichswerte für Zeitschnitte rechnen
if($_POST['VERGLEICH']) 
{
	$_SESSION['Tabelle']['VERGLEICH'] = '1';
}
else
{
	// setze nur NUll, wenn die Anweisung wirklich aus dem Formular kommt (d.h. wenn $_POST['Zeitschnitt_UERE_Formular'] übergeben wurde)
	if($_POST['Zeitschnitt_UERE_Formular']) $_SESSION['Tabelle']['VERGLEICH'] = '0';
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

/* 
$Ergebnis_drop_table = mysqli_query($Verbindung,"DROP TABLE `t_temp_tabellentool`");
$SQL_temp_table = "CREATE TABLE `t_temp_tabellentool` (
  `AGS` varchar(20) NOT NULL,
  `NAME` varchar(100) default NULL,
  `WERT` double default NULL,
  `FEHLERCODE` int(2)  NULL DEFAULT 0 ,
  `AKT` int(4) default NULL,
  `NAME_KRS` varchar(100) default NULL,
  `WERT_KRS` double default NULL,
  `WERT_KRS_DIFF` double default NULL,
  `FEHLERCODE_KRS` int(2) default NULL,
  `NAME_BLD` varchar(100) default NULL,
  `WERT_BLD` double default NULL,
  `WERT_BLD_DIFF` double default NULL,
  `FEHLERCODE_BLD` int(2) default NULL,
   PRIMARY KEY  (`AGS`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";   */

//-------------> für den echten Betrieb Temp-Table nutzen
/* */
$SQL_temp_table = "CREATE TEMPORARY TABLE `t_temp_tabellentool` (
  `AGS` varchar(20) NOT NULL,
  `NAME` varchar(100) default NULL,
  `SVG_GEOMETRIE` varchar(255) default NULL,
  `WERT` double default NULL,
  `FEHLERCODE` int(2)  NULL DEFAULT 0,
  `AKT` int(4) default NULL,
  `NAME_KRS` varchar(100) default NULL,
  `WERT_KRS` double default NULL,
  `WERT_KRS_DIFF` double default NULL,
  `AKT_KRS` int(4) default NULL,
  `FEHLERCODE_KRS` int(2) default NULL,
  `NAME_BLD` varchar(100) default NULL,
  `WERT_BLD` double default NULL,
  `WERT_BLD_DIFF` double default NULL,
  `AKT_BLD` int(4) default NULL,
  `FEHLERCODE_BLD` int(2) default NULL, 
  `WERT_BND` double default NULL,
  `WERT_BND_DIFF` double default NULL,
  `AKT_BND` int(4) default NULL,
  `FEHLERCODE_BND` int(2) default NULL, 
   PRIMARY KEY  (`AGS`)
) ENGINE=HEAP DEFAULT CHARSET=utf8;";  

				
$Ergebnis_temp_table = mysqli_query($Verbindung,$SQL_temp_table); 



// Gewählte Raumebenen erfassen und SQL vorbereiten
if(is_array($_SESSION['Datenbestand']))
{
	foreach($_SESSION['Datenbestand'] as $DatenSet)
	{
		// Check auf [View]==1 < wenn =1 dann ausgewählt
		if($DatenSet['View']=='1')
		{
			$Region = '1'; // Schalter, ob min 1 Region ausgewählt ist
			foreach($DatenSet['Auswahlkriterium_Wert'] as $UnterDatenSet)
			{
				$Auswahl[] = $UnterDatenSet;
			}
		}
	}
}


// -------------------- PG -> MySQL ------------------------
// Erfassen der gewählten Objekte und Grundfüllung der DB mit (AGS+Namen-) Datensätzen als Basis für die weitere Verarbeitung
if($Indikator = $_SESSION['Dokument']['Fuellung']['Indikator'] and $_SESSION['Dokument']['Raumgliederung'] and $Region=='1')
{ 
	// Erfassen der gewählten Raumeinheiten
	foreach($Auswahl as $Teil_AGS)
	{
		
		// Betroffene Einheiten mit Namen aus PG selektieren
		$SQL_PG_Name = "SELECT 
		ags,
		gen,
		xmin(box3d(the_geom)) AS x_min,
		ymin(box3d(the_geom)) AS y_min, 
		xmax(box3d(the_geom)) AS x_max, 
		ymax(box2d(the_geom)) AS y_max,
		AsSvg(the_geom,1,0) AS geometrie
		FROM vg250_".$_SESSION['Dokument']['Raumgliederung']."_".$_SESSION['Dokument']['Jahr_Geometrietabelle']." WHERE AGS LIKE '".$Teil_AGS."%'";
		$ERGEBNIS_PG_Name = pg_query($Verbindung_PostgreSQL,$SQL_PG_Name);  				
		while($PG_Zeile = @pg_fetch_assoc($ERGEBNIS_PG_Name))
		{
			
			
			// Kreise als übergeordnete Raumeinheit mit anzeigen wenn Gemeindeebene ausgewählt
			if($_SESSION['Dokument']['Raumgliederung'] == "gem")
			{
				// Kreis-Namen aus PG selektieren
				$SQL_PG_Name_KRS = "SELECT gen FROM vg250_krs_".$_SESSION['Dokument']['Jahr_Geometrietabelle']." WHERE AGS = '".substr($PG_Zeile['ags'],0,5)."'";
				$ERGEBNIS_PG_Name_KRS = pg_query($Verbindung_PostgreSQL,$SQL_PG_Name_KRS);  				
				$PG_Zeile_KRS = @pg_fetch_assoc($ERGEBNIS_PG_Name_KRS);
				$Name_KRS = $PG_Zeile_KRS['gen'];
			}
			else
			{
				// u.U. auswertbar für Tabellenanzeige
				$Name_KRS = "-";
			}
			
			// Bundesländer als übergeordnete Raumeinheit mit anzeigen wenn folgendes ausgewählt
			if($_SESSION['Dokument']['Raumgliederung'] == "gem" 
			or $_SESSION['Dokument']['Raumgliederung'] == "krs" 
			or $_SESSION['Dokument']['Raumgliederung'] == "kfs" 
			or $_SESSION['Dokument']['Raumgliederung'] == "lks")
			{
				// Kreis-Namen aus PG selektieren
				$SQL_PG_Name_BLD = "SELECT gen FROM vg250_bld_".$_SESSION['Dokument']['Jahr_Geometrietabelle']." WHERE AGS = '".substr($PG_Zeile['ags'],0,2)."'";
				$ERGEBNIS_PG_Name_BLD = pg_query($Verbindung_PostgreSQL,$SQL_PG_Name_BLD);  				
				$PG_Zeile_BLD = @pg_fetch_assoc($ERGEBNIS_PG_Name_BLD);
				$Name_BLD = $PG_Zeile_BLD['gen'];
			}
			else
			{
				// u.U. auswertbar für Tabellenanzeige
				$Name_BLD = "-";
			}
			
			
			$SQL_PG_AG = "INSERT INTO t_temp_tabellentool 
							(AGS,NAME,NAME_KRS,NAME_BLD) 
						VALUES 
							('".$PG_Zeile['ags']."','".$PG_Zeile['gen']."','".$Name_KRS."','".$Name_BLD."');";
			$Ergebnis_PG_AGS = mysqli_query($Verbindung,$SQL_PG_AG);
		}
		
		
		
		
		
		
	}	
}








// -------------------- MySQL -> ------------------------
// Füllen der DB mit Werten
// ------------------------

// Wert für Deutschland für Berechnungen ermitteln (AGS = 99)
$SQL_Indikatorenwert_D = "SELECT INDIKATORWERT,FEHLERCODE FROM m_indikatorwerte_".$_SESSION['Dokument']['Jahr_Anzeige']." WHERE AGS = '99' AND ID_INDIKATOR = '".$_SESSION['Dokument']['Fuellung']['Indikator']."';"; 
$Ergebnis_Indikatorenwerte_D = mysqli_query($Verbindung,$SQL_Indikatorenwert_D); 
$Wert_D = @mysqli_result($Ergebnis_Indikatorenwerte_D,0,'INDIKATORWERT');
$Fehlercode_D = @mysqli_result($Ergebnis_Indikatorenwerte_D,0,'FEHLERCODE');

// Mittl. Grundaktualität für Deutschland für Berechnungen ermitteln (AGS = 99)
$SQL_Akt_D = "SELECT INDIKATORWERT,FEHLERCODE FROM m_indikatorwerte_".$_SESSION['Dokument']['Jahr_Anzeige']." WHERE AGS = '99' AND ID_INDIKATOR = 'Z00AG';"; 
$Ergebnis_Akt_D = mysqli_query($Verbindung,$SQL_Akt_D); 
$Akt_D = @mysqli_result($Ergebnis_Akt_D,0,'INDIKATORWERT');

// ------------------------
// DB-Tabelle füllen
$SQL_DS_vorh = "SELECT * FROM t_temp_tabellentool";
$Ergebnis_DS_vorh = mysqli_query($Verbindung,$SQL_DS_vorh); 
$i_ds = 0;
while($ags = @mysqli_result($Ergebnis_DS_vorh,$i_ds,'AGS'))
{
	// Indikatorwert und Fehlercode
	$SQL_Indikatorenwerte = "SELECT INDIKATORWERT,FEHLERCODE 
	FROM m_indikatorwerte_".$_SESSION['Dokument']['Jahr_Anzeige']." 
	WHERE AGS = '".$ags."' 
	AND ID_INDIKATOR = '".$_SESSION['Dokument']['Fuellung']['Indikator']."';"; 
	$Ergebnis_Indikatorenwerte = mysqli_query($Verbindung,$SQL_Indikatorenwerte); 
			
	$UPD_Wert = @mysqli_result($Ergebnis_Indikatorenwerte,0,'INDIKATORWERT');
	$UPD_Fehlercode = @mysqli_result($Ergebnis_Indikatorenwerte,0,'FEHLERCODE');
	
	// Nur erfassen, wenn Akt. angezeigt werden soll
	if($_SESSION['Tabelle']['AKTUALITAET'])
	{
		//Erfassen der vorgesehenen Grundaktualitäts-Angabe
		/* $SQL_Aktualitäts_Verweis = "SELECT AKTUALITAET_VIEWER FROM v_geometrie_jahr_viewer_postgis WHERE Jahr_im_Viewer = '".$_SESSION['Dokument']['Jahr_Anzeige']."'"; 
		$Ergebnis_Aktualitäts_Verweis = mysqli_query($Verbindung,$SQL_Aktualitäts_Verweis);
		$Grundakt_Verweis = @mysqli_result($Ergebnis_Aktualitäts_Verweis,0,'AKTUALITAET_VIEWER'); */
		
		// Grundaktualität
		$SQL_Grundktualitaet = "SELECT AGS,INDIKATORWERT FROM m_indikatorwerte_".$_SESSION['Dokument']['Jahr_Anzeige']." WHERE ID_INDIKATOR = 'Z00AG' AND AGS = '".$ags."';"; 
		// $SQL_Grundktualitaet = "SELECT AKTUALITAET FROM v_akt_gesamt WHERE JAHR = '".$Grundakt_Verweis."' AND AGS = '".$ags."'"; 
		$Ergebnis_Grundktualitaet = mysqli_query($Verbindung,$SQL_Grundktualitaet);
		
		$Grundakt = @mysqli_result($Ergebnis_Grundktualitaet,0,'INDIKATORWERT');
		
		// Grundaktualität KRS
		if($_SESSION['Dokument']['Raumgliederung'] == "gem")
		{
			$SQL_Grundktualitaet = "SELECT AGS,INDIKATORWERT FROM m_indikatorwerte_".$_SESSION['Dokument']['Jahr_Anzeige']." WHERE ID_INDIKATOR = 'Z00AG' AND AGS = '".substr($ags,0,5)."';"; 
			// $SQL_Grundktualitaet = "SELECT AKTUALITAET FROM v_akt_gesamt WHERE JAHR = '".$Grundakt_Verweis."' AND AGS = '".substr($ags,0,5)."'"; 
			$Ergebnis_Grundktualitaet = mysqli_query($Verbindung,$SQL_Grundktualitaet);
			
			$Grundakt_KRS = @mysqli_result($Ergebnis_Grundktualitaet,0,'INDIKATORWERT');
		}
		
		// Grundaktualität BLD
		if($_SESSION['Dokument']['Raumgliederung'] == "gem" 
		or $_SESSION['Dokument']['Raumgliederung'] == "krs" 
		or $_SESSION['Dokument']['Raumgliederung'] == "kfs" 
		or $_SESSION['Dokument']['Raumgliederung'] == "lks")
		{
			$SQL_Grundktualitaet = "SELECT AGS,INDIKATORWERT FROM m_indikatorwerte_".$_SESSION['Dokument']['Jahr_Anzeige']." WHERE ID_INDIKATOR = 'Z00AG' AND AGS = '".substr($ags,0,2)."';"; 
			// $SQL_Grundktualitaet = "SELECT AKTUALITAET FROM v_akt_gesamt WHERE JAHR = '".$Grundakt_Verweis."' AND AGS = '".substr($ags,0,2)."'"; 
			$Ergebnis_Grundktualitaet = mysqli_query($Verbindung,$SQL_Grundktualitaet);
			
			$Grundakt_BLD = @mysqli_result($Ergebnis_Grundktualitaet,0,'INDIKATORWERT');
		}
	}
	
	
	// Nur ausführen, wenn übergeordnete Raumeinheiten wirklich angezeigt werden sollen
	if($_SESSION['Tabelle']['UERE_KRS'] or $_SESSION['Tabelle']['UERE_BLD'] or $_SESSION['Tabelle']['UERE_BND'] )
	{
		// Werte für Kreise als übergeordnete Raumeinheit mit erfassen wenn Gemeindeebene ausgewählt
		if($_SESSION['Dokument']['Raumgliederung'] == "gem")
		{
			// Indikatorwert und Fehlercode
			$SQL_Indikatorenwerte_KRS = "SELECT INDIKATORWERT,FEHLERCODE 
			FROM m_indikatorwerte_".$_SESSION['Dokument']['Jahr_Anzeige']." 
			WHERE AGS = '".substr($ags,0,5)."' 
			AND ID_INDIKATOR = '".$_SESSION['Dokument']['Fuellung']['Indikator']."';"; 
			$Ergebnis_Indikatorenwerte_KRS = mysqli_query($Verbindung,$SQL_Indikatorenwerte_KRS); 
					
			$UPD_Wert_KRS = @mysqli_result($Ergebnis_Indikatorenwerte_KRS,0,'INDIKATORWERT');
			$UPD_Fehlercode_KRS = @mysqli_result($Ergebnis_Indikatorenwerte_KRS,0,'FEHLERCODE');
		}
			
		// Werete für Bundesländer als übergeordnete Raumeinheit mit anzeigen wenn folgendes ausgewählt
		if($_SESSION['Dokument']['Raumgliederung'] == "gem" 
		or $_SESSION['Dokument']['Raumgliederung'] == "krs" 
		or $_SESSION['Dokument']['Raumgliederung'] == "kfs" 
		or $_SESSION['Dokument']['Raumgliederung'] == "lks")
		{
			// Indikatorwert und Fehlercode
			$SQL_Indikatorenwerte_BLD = "SELECT INDIKATORWERT,FEHLERCODE 
			FROM m_indikatorwerte_".$_SESSION['Dokument']['Jahr_Anzeige']." 
			WHERE AGS = '".substr($ags,0,2)."' 
			AND ID_INDIKATOR = '".$_SESSION['Dokument']['Fuellung']['Indikator']."';"; 
			$Ergebnis_Indikatorenwerte_BLD = mysqli_query($Verbindung,$SQL_Indikatorenwerte_BLD); 
					
			$UPD_Wert_BLD = @mysqli_result($Ergebnis_Indikatorenwerte_BLD,0,'INDIKATORWERT');
			$UPD_Fehlercode_BLD = @mysqli_result($Ergebnis_Indikatorenwerte_BLD,0,'FEHLERCODE');
		}
	}
	
	// Update der bestehenden Datensätze
	$SQL_DS_UPD = "UPDATE t_temp_tabellentool 
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
	WHERE AGS = '".$ags."'";
	$Ergebnis_DS_UPD = mysqli_query($Verbindung,$SQL_DS_UPD); 
	
	$i_ds++;
	
} 

// ---- Differenzen bilden und einsortieren
// Für übergeordnete Ebenen der konkreten Werte
$SQL_DS_UE_DIFF = "UPDATE t_temp_tabellentool 
					SET 
					WERT_KRS_DIFF = WERT - WERT_KRS,
					WERT_BLD_DIFF = WERT - WERT_BLD,
					WERT_BND_DIFF = WERT - WERT_BND
					;";				
$Ergebnis_DS_UE_DIFF = mysqli_query($Verbindung,$SQL_DS_UE_DIFF);


// ---------------------------
// Erweitern der Tabelle

// temp-Füllung
// $ZSZusatzTest = "2006";


// Zusätzliche Zeitschnitte einfügen

// Sortierung Abfrge der möglichen Zeitschnitte für den (evtl. auch eingeloggten) Nutzer
$SQL_Jahre_uebernahme = "SELECT JAHR FROM m_indikator_freigabe,v_geometrie_jahr_viewer_postgis 
                                                WHERE m_indikator_freigabe.STATUS_INDIKATOR_FREIGABE >= '".$_SESSION['Dokument']['ViewBerechtigung']."' 
                                                AND m_indikator_freigabe.JAHR = v_geometrie_jahr_viewer_postgis.Jahr_im_Viewer
												AND m_indikator_freigabe.ID_INDIKATOR = '".$_SESSION['Dokument']['Fuellung']['Indikator']."' 
                                                GROUP BY JAHR 
                                                ORDER BY SORTIERUNG_VIEWER DESC"; 
$Ergebnis_Jahre_uebernahme = mysqli_query($Verbindung,$SQL_Jahre_uebernahme);
     
// leeren des betreffenden SESSION-Array Abschnitts, wenn das vorgesehene Formular abgesendet wurde
if($_POST['Zeitschnitt_UERE_Formular']) $_SESSION['Tabelle']['Zeitschnitt_Zusatz'] = array(); 
     
// wieder füllen des SESSION-Array geordnet nach Ausgabe der Abfrage aus v_geometrie_jahr_viewer_postgis
$i_jhru = 0;
while($ZusatzZS = @mysqli_result($Ergebnis_Jahre_uebernahme,$i_jhru,'JAHR'))
{
	
// Ablegen der Ergebnisse in separatem Array für die spätere Anzeige der Formularelemente
// ---------
	$Zeitschnitte_vorh[] = $ZusatzZS;
// ---------
	// Anfügen der Klausel "ZS_", da Namen mit Zahlen am Beginn bei HTML-Formularelementen nicht erlaubt
	$ZSP = 'ZS_'.mysqli_result($Ergebnis_Jahre_uebernahme,$i_jhru,'JAHR');
	
	// wenn dieser Zeitschnitt im Formular angehakt war (per Post mit Name übergeben), Ablegen als Wert  <= Anzeige / ansonsten keine Hinterlegung im Array <= keine Anzeige
	// Ist kein Post erfolgt dann wird bestehendes Array geprüft
	if($_POST[$ZSP] or $_SESSION['Tabelle']['Zeitschnitt_Zusatz'][$ZusatzZS] == 1)
	{
		// temp Wertsetzung!!!!!!!!!!!!!!!!!!!!!
		if($ZSZusatzTest)
		{
			$ZusatzZS = $ZSZusatzTest;
		}
		// Wert der Reihe nach in Array schreiben
		$_SESSION['Tabelle']['Zeitschnitt_Zusatz'][$ZusatzZS] = 1;
		
		// Erweitern der Tabelle um benötigte Spalten
		$SQL_DS_ALT = "ALTER TABLE t_temp_tabellentool 
		  ADD `WERT_".$ZusatzZS."` double default NULL,
		  ADD `WERT_DIFF_".$ZusatzZS."` double default NULL,
		  ADD `WERT_RELATIV_".$ZusatzZS."` double default NULL,
		  ADD `FEHLERCODE_".$ZusatzZS."` int(2) default NULL,
		  ADD `AKT_".$ZusatzZS."` int(4) default NULL,
		  ADD `WERT_KRS_".$ZusatzZS."` double default NULL,
		  ADD `WERT_KRS_DIFF_".$ZusatzZS."` double default NULL,
		  ADD `WERT_KRS_RELATIV_".$ZusatzZS."` double default NULL,
		  ADD `FEHLERCODE_KRS_".$ZusatzZS."` int(2) default NULL,
		  ADD `WERT_BLD_".$ZusatzZS."` double default NULL,
		  ADD `WERT_BLD_DIFF_".$ZusatzZS."` double default NULL,
		  ADD `WERT_BLD_RELATIV_".$ZusatzZS."` double default NULL,
		  ADD `FEHLERCODE_BLD_".$ZusatzZS."` int(2) default NULL,
		  ADD `WERT_BND_".$ZusatzZS."` double default NULL,
		  ADD `WERT_BND_DIFF_".$ZusatzZS."` double default NULL,
		  ADD `WERT_BND_RELATIV_".$ZusatzZS."` double default NULL,
		  ADD `AKT_BND_".$ZusatzZS."` double default NULL,
		  ADD `FEHLERCODE_BND_".$ZusatzZS."` int(2) default NULL;";
		$Ergebnis_DS_ALT = mysqli_query($Verbindung,$SQL_DS_ALT); 
		
		
		// Wert für Deutschland für Berechnungen ermitteln (AGS = 99)
		$SQL_Indikatorenwert_D_Z = "SELECT INDIKATORWERT,FEHLERCODE,NAME FROM m_indikatorwerte_".$ZusatzZS." WHERE AGS = '99' AND ID_INDIKATOR = '".$_SESSION['Dokument']['Fuellung']['Indikator']."';"; 
		$Ergebnis_Indikatorenwerte_D_Z = mysqli_query($Verbindung,$SQL_Indikatorenwert_D_Z); 
		$Wert_D_Z = @mysqli_result($Ergebnis_Indikatorenwerte_D_Z,0,'INDIKATORWERT');
		$Fehlercode_D_Z = @mysqli_result($Ergebnis_Indikatorenwerte_D_Z,0,'FEHLERCODE');
		
		// Mittl. Grundaktualität für Deutschland für Berechnungen ermitteln (AGS = 99)
		$SQL_Akt_D_Z = "SELECT INDIKATORWERT,FEHLERCODE FROM m_indikatorwerte_".$ZusatzZS." WHERE AGS = '99' AND ID_INDIKATOR = 'Z00AG';"; 
		$Ergebnis_Akt_D_Z = mysqli_query($Verbindung,$SQL_Akt_D_Z); 
		$Akt_D_Z = @mysqli_result($Ergebnis_Akt_D_Z,0,'INDIKATORWERT');
	
	
		// Füllen der ZusatzspaltenDB mit Werten pro verzeichnetem Datensatz
		// -------------------------------------
		$SQL_DS_vorh = "SELECT * FROM t_temp_tabellentool";
		$Ergebnis_DS_vorh = mysqli_query($Verbindung,$SQL_DS_vorh); 
		$i_ds = 0;
		while($ags = @mysqli_result($Ergebnis_DS_vorh,$i_ds,'AGS'))
		{
			// Indikatorwert und Fehlercode
			$SQL_Indikatorenwerte = "SELECT INDIKATORWERT,FEHLERCODE,AGS 
			FROM m_indikatorwerte_".$ZusatzZS." 
			WHERE AGS = '".$ags."' 
			AND ID_INDIKATOR = '".$_SESSION['Dokument']['Fuellung']['Indikator']."';"; 
			$Ergebnis_Indikatorenwerte = mysqli_query($Verbindung,$SQL_Indikatorenwerte); 
			
			// fehlende Raumeinheiten erkennen
			if(@mysqli_result($Ergebnis_Indikatorenwerte,0,'AGS'))
			{
				$UPD_Wert = @mysqli_result($Ergebnis_Indikatorenwerte,0,'INDIKATORWERT');
				$UPD_Fehlercode = @mysqli_result($Ergebnis_Indikatorenwerte,0,'FEHLERCODE');
			}
			else
			{
				// Markierung für fehlende Raumeinheit bei Vergleichen
				$UPD_Fehlercode = '8';
			}
			
			// Nur erfassen, wenn Akt. angezeigt werden soll
			if($_SESSION['Tabelle']['AKTUALITAET'] and !$UPD_Fehlercode)
			{
				/* //Erfassen der vorgesehenen Grundaktualitäts-Angabe
				$SQL_Aktualitäts_Verweis = "SELECT AKTUALITAET_VIEWER FROM v_geometrie_jahr_viewer_postgis WHERE Jahr_im_Viewer = '".$ZusatzZS."'"; 
				$Ergebnis_Aktualitäts_Verweis = mysqli_query($Verbindung,$SQL_Aktualitäts_Verweis);
				$Grundakt_Verweis = @mysqli_result($Ergebnis_Aktualitäts_Verweis,0,'AKTUALITAET_VIEWER'); */
				
				// Grundaktualität
				$SQL_Grundktualitaet = "SELECT AGS,INDIKATORWERT FROM m_indikatorwerte_".$ZusatzZS." WHERE ID_INDIKATOR = 'Z00AG' AND AGS = '".$ags."';"; 
				// $SQL_Grundktualitaet = "SELECT AKTUALITAET FROM v_akt_gesamt WHERE JAHR = '".$Grundakt_Verweis."' AND AGS = '".$ags."'"; 
				$Ergebnis_Grundktualitaet = mysqli_query($Verbindung,$SQL_Grundktualitaet);
				
				$Grundakt = @mysqli_result($Ergebnis_Grundktualitaet,0,'INDIKATORWERT');
			}
			
			
			// Nur ausführen, wenn übergeordnete Raumeinheiten wirklich angezeigt werden sollen
			if($_SESSION['Tabelle']['UERE'] and !$UPD_Fehlercode)
			{
				// Werte für Kreise als übergeordnete Raumeinheit mit erfassen wenn Gemeindeebene ausgewählt
				if($_SESSION['Dokument']['Raumgliederung'] == "gem")
				{
					// Indikatorwert und Fehlercode
					$SQL_Indikatorenwerte_KRS = "SELECT INDIKATORWERT,FEHLERCODE,AGS 
					FROM m_indikatorwerte_".$ZusatzZS." 
					WHERE AGS = '".substr($ags,0,5)."' 
					AND ID_INDIKATOR = '".$_SESSION['Dokument']['Fuellung']['Indikator']."';"; 
					$Ergebnis_Indikatorenwerte_KRS = mysqli_query($Verbindung,$SQL_Indikatorenwerte_KRS); 
							
					$UPD_Wert_KRS = @mysqli_result($Ergebnis_Indikatorenwerte_KRS,0,'INDIKATORWERT');
					$UPD_Fehlercode_KRS = @mysqli_result($Ergebnis_Indikatorenwerte_KRS,0,'FEHLERCODE');
				}
					
				// Werete für Bundesländer als übergeordnete Raumeinheit mit anzeigen wenn folgendes ausgewählt
				if($_SESSION['Dokument']['Raumgliederung'] == "gem" 
				or $_SESSION['Dokument']['Raumgliederung'] == "krs" 
				or $_SESSION['Dokument']['Raumgliederung'] == "kfs" 
				or $_SESSION['Dokument']['Raumgliederung'] == "lks")
				{
					// Indikatorwert und Fehlercode
					$SQL_Indikatorenwerte_BLD = "SELECT INDIKATORWERT,FEHLERCODE,AGS  
					FROM m_indikatorwerte_".$ZusatzZS." 
					WHERE AGS = '".substr($ags,0,2)."' 
					AND ID_INDIKATOR = '".$_SESSION['Dokument']['Fuellung']['Indikator']."';"; 
					$Ergebnis_Indikatorenwerte_BLD = mysqli_query($Verbindung,$SQL_Indikatorenwerte_BLD); 
							
					$UPD_Wert_BLD = @mysqli_result($Ergebnis_Indikatorenwerte_BLD,0,'INDIKATORWERT');
					$UPD_Fehlercode_BLD = @mysqli_result($Ergebnis_Indikatorenwerte_BLD,0,'FEHLERCODE');
				}
			}
			else
			{
				// bei fehlender Grund-Raumeinheit die Übergeordneten gleichfalls ausblenden
				if($UPD_Fehlercode)
				{
					$UPD_Fehlercode_BLD = '8';
					$UPD_Fehlercode_KRS = '8';
				}
			}
			
			// Update der bestehenden Datensätze
			$SQL_DS_UPD = "UPDATE t_temp_tabellentool 
			SET 
			WERT_".$ZusatzZS." = '".$UPD_Wert."',
			FEHLERCODE_".$ZusatzZS." = '".$UPD_Fehlercode."',
			AKT_".$ZusatzZS." = '".$Grundakt."',
			WERT_KRS_".$ZusatzZS." = '".$UPD_Wert_KRS."',
			FEHLERCODE_KRS_".$ZusatzZS." = '".$UPD_Fehlercode_KRS."',
			WERT_BLD_".$ZusatzZS." = '".$UPD_Wert_BLD."',
			FEHLERCODE_BLD_".$ZusatzZS." = '".$UPD_Fehlercode_BLD."',
			WERT_BND_".$ZusatzZS." = '".$Wert_D_Z."',
			AKT_BND_".$ZusatzZS." = '".$Akt_D_Z."',
			FEHLERCODE_BND_".$ZusatzZS." = '".$Fehlercode_D_Z."'
			WHERE AGS = '".$ags."'";
			$Ergebnis_DS_UPD = mysqli_query($Verbindung,$SQL_DS_UPD); 
			

			$i_ds++;			
		} 
	}
	
	// ---- Differenzen bilden und einsortieren
	// Für Zusatzzeitschnitte, wenn vorgesehen 
	if($_SESSION['Tabelle']['VERGLEICH'] == '1')
	{
		/* $SQL_DS_W_DIFF = "UPDATE t_temp_tabellentool 
						SET 
						WERT_DIFF_".$ZusatzZS." = WERT_".$ZusatzZS." - WERT, 
						WERT_KRS_DIFF_".$ZusatzZS." = WERT_KRS_".$ZusatzZS." - WERT_KRS,
						WERT_BLD_DIFF_".$ZusatzZS." = WERT_BLD_".$ZusatzZS." - WERT_BLD, 
						WERT_RELATIV_".$ZusatzZS." = 1-(WERT / WERT_".$ZusatzZS."), 
						WERT_KRS_RELATIV_".$ZusatzZS." = 1-(WERT_KRS / WERT_KRS_".$ZusatzZS."),
						WERT_BLD_RELATIV_".$ZusatzZS." = 1-(WERT_BLD / WERT_BLD_".$ZusatzZS.") 
						;"; */
		// nur absolute Änderungen
		$SQL_DS_W_DIFF = "UPDATE t_temp_tabellentool 
						SET 
						WERT_DIFF_".$ZusatzZS." = WERT_".$ZusatzZS." - WERT, 
						WERT_KRS_DIFF_".$ZusatzZS." = WERT_KRS_".$ZusatzZS." - WERT_KRS,
						WERT_BLD_DIFF_".$ZusatzZS." = WERT_BLD_".$ZusatzZS." - WERT_BLD,
						WERT_BND_DIFF_".$ZusatzZS." = WERT_BND_".$ZusatzZS." - WERT_BND 
						;";				
		$Ergebnis_DS_W_DIFF = mysqli_query($Verbindung,$SQL_DS_W_DIFF); 
	}
	$i_jhru++;
}







// Statistische Kenngrößen ermitteln:
// ----------------------------------

// alle fehlerfreien Daten erfassen
// With sample standard deviation: STDDEV_SAMP()
$SQL_Stat = "SELECT 
						AVG(WERT) AS DURCHSCHNITT,  
						SUM(WERT) AS SUMME, 
						STDDEV_SAMP(WERT) AS STANDARDABWEICHUNG, 
						MAX(WERT) AS MAXIMUM, 
						MIN(WERT) AS MINIMUM, 
						COUNT(WERT) AS COUNT
						FROM t_temp_tabellentool 
						WHERE FEHLERCODE = '0'"; 
				
$Ergebnis_Stat = mysqli_query($Verbindung,$SQL_Stat); 

// ---------
$Ar_Mittel = @mysqli_result($Ergebnis_Stat,0,'DURCHSCHNITT');
$Summe = @mysqli_result($Ergebnis_Stat,0,'SUMME');
$Standardabweichung = @mysqli_result($Ergebnis_Stat,0,'STANDARDABWEICHUNG');
$Maximum = @mysqli_result($Ergebnis_Stat,0,'MAXIMUM'); // besser aus Werten ermitteln, wegen 1 oder mehrerer Treffer
$Minimum = @mysqli_result($Ergebnis_Stat,0,'MINIMUM'); // besser aus Werten ermitteln, wegen 1 oder mehrerer Treffer
$Medianstelle = @floor((@mysqli_result($Ergebnis_Stat,0,'COUNT')+1)/2);
$n_Stichproben = @mysqli_result($Ergebnis_Stat,0,'COUNT');
// ---------

// Namen bzw. mehrere Min oder Max ermitteln
// Min
$SQL_Min = "SELECT WERT,NAME FROM t_temp_tabellentool WHERE FEHLERCODE = '0' ORDER BY WERT ASC"; 
$Ergebnis_Min = mysqli_query($Verbindung,$SQL_Min); 

if(@mysqli_result($Ergebnis_Min,0,'WERT') != @mysqli_result($Ergebnis_Min,1,'WERT'))
{
	$Minimum_Name = @mysqli_result($Ergebnis_Min,0,'NAME');
}
else
{
	$Minimum_Name = "Mehrere Minima";
}
// Max
$SQL_Max = "SELECT WERT,NAME FROM t_temp_tabellentool WHERE FEHLERCODE = '0' ORDER BY WERT DESC"; 
$Ergebnis_Max = mysqli_query($Verbindung,$SQL_Max); 

if(@mysqli_result($Ergebnis_Max,0,'WERT') != @mysqli_result($Ergebnis_Max,1,'WERT'))
{
	$Maximum_Name = @mysqli_result($Ergebnis_Max,0,'NAME');
}
else
{
	$Maximum_Name = "Mehrere Maxima";
}



// Median ermitteln
$SQL_WERTE = "SELECT * FROM t_temp_tabellentool WHERE FEHLERCODE = '0' ORDER BY WERT"; 
$Ergebnis_WERTE = mysqli_query($Verbindung,$SQL_WERTE); 

// ---------
$Median_Wert = @mysqli_result($Ergebnis_WERTE,$Medianstelle,'WERT');
$Median_Name = @mysqli_result($Ergebnis_WERTE,$Medianstelle,'NAME');
// ---------

// Weitere Kenngrößen ableiten:
/* 
// Minimum
$i_stw = 0;
while(@mysqli_result($Ergebnis_WERTE,$i_stw,'NAME'))
{
	$Checkwert = @mysqli_result($Ergebnis_WERTE,$i_stw,'WERT');
	// 1 oder mehrere Minima abtesten
	if($Min_Ausgabe_akt and $Checkwert_alt == $Checkwert) 	$Min_Ausgabe = $Checkwert." (mehrere Minima enthalten)";
	if(!$Min_Ausgabe_akt)
	{
		$Min_Ausgabe = number_format($Checkwert,$Rundg1mehr = ($_SESSION['Dokument']['Fuellung']['Rundung']+1))." (".@mysqli_result($Ergebnis_WERTE,$i_stw,'NAME').")"; // bei Rundung eine Stelle mehr als vorgesehen
		$Min_Ausgabe_akt = 1;
		$Checkwert_alt = $Checkwert;
	}	
	$i_stw++;
}
$Checkwert_alt = "";

// Maximum
$i_stw = $i_stw-1; // wiederverwenden des Zählers
while(@mysqli_result($Ergebnis_WERTE,$i_stw,'NAME'))
{
	$Checkwert = @mysqli_result($Ergebnis_WERTE,$i_stw,'WERT');
	// 1 oder mehrere Maxima abtesten
	if($Max_Ausgabe_akt and $Max_Ausgabe and $Checkwert_alt == $Checkwert) 	$Max_Ausgabe = $Checkwert." (mehrere Maxima enthalten)";	
	if(!$Max_Ausgabe_akt) 
	{
		$Max_Ausgabe = number_format($Checkwert,$Rundg1mehr = ($_SESSION['Dokument']['Fuellung']['Rundung']+1))." (".@mysqli_result($Ergebnis_WERTE,$i_stw,'NAME').")"; // bei Rundung eine Stelle mehr als vorgesehen
		$Max_Ausgabe_akt = 1;
		$Checkwert_alt = $Checkwert;
	}
	
	$i_stw--;
}

 */













// Begin des HTML Dokuments

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>IÖR Monitor</title>
<link href="../screen_viewer.css" rel="stylesheet" type="text/css" media="screen" />
<link href="../print_viewer.css" rel="stylesheet" type="text/css" media="print" />
<style type="text/css">
<!--
body {
	font-size:12px;	
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
	color: #444;
}
a {
	cursor: default;
}
a:visited {
	text-decoration: none;
	color: #444;
}
a:hover {
	text-decoration: none;
	color: #444;
}
a:active {
	text-decoration: none;
	color: #444;
}
@media print {
	.nicht_im_print {display:none;}
}
-->
</style>
</head>
<body style="padding-left:35px;" class="body_unterseiten">
<img src="../gfx/kopf_v2_unterseiten.png" width="999" height="119" alt="Kopfgrafik" /><br />



<strong>IÖR-Monitor©Leibniz-Institut für ökologische Raumentwicklung</strong>
    <table style="border-collapse:collapse; border:none;">
      <!--<tr>
      <td colspan="2" style="border:none; padding:0px;">&nbsp;</td>
      <td style="border-right:none; border-top:none; border-bottom:none; border-left:1px solid #999999;" >
      <div class="nicht_im_print" style="padding-top:5px">
        Kostenlose Bestellung der Daten im CSV-Format <br />
        per <a 
        style="background-color:#DDDDDD; padding-left:12px; padding-right:12px; padding-top:0px; padding-bottom:0px;" class="button_standard_abschicken_a" 
        href="mailto:monitor@ioer.de?subject=Monitor_Tabelle&body=Bestellung der folgenden Tabelle des Monitors der Siedlungs- und Freiraumentwicklung:%0A%0AIndikator: <?php 
		echo utf8_encode($_SESSION['Dokument']['Fuellung']['Indikator']."%0AInhalt: ".$_SESSION['Datenbestand_Ausgabe']
		."%0ARaumgliederung: ".$_SESSION['Dokument']['Raumgliederung_Ausgabe']
		."%0A%0AVerwendungszweck: %0AZus%E4tzliche Bemerkungen: %0A%0AName: %0AEinrichtung: %0AAdresse: %0ATelefon: "); ?>">Email</a>
		</div>
      </td>
      </tr> -->
      <tr>
      <td colspan="3" style="border:none; padding:0px;">
      			<h2><?php echo $_SESSION['Dokument']['Fuellung']['Indikator_Beschreibung']; ?> (<?php echo $_SESSION['Dokument']['Jahr_Anzeige']; ?>)</h2>
      			<div style="padding-bottom:10px;"  class="nicht_im_print">
                	<a style=" color:#000; background-color:#BDDDFD; padding-left:12px; padding-right:12px; padding-top:2px; padding-bottom:2px;" class="button_standard_abschicken_a" 
            		target="_blank" href="http://www.ioer-monitor.de/index.php?id=44">Indikatorkennblatt</a>
                </div>
                
      </td>
      </tr>
      <tr>
        <td width="183" valign="top" style="border:none; padding:0px;">Einheit:</td>
        <td width="228" valign="top" style="border:none;"><?php echo $_SESSION['Dokument']['Fuellung']['Indikator_Einheit']; ?></td>
        <td class="nicht_im_print"  width="365" rowspan="9" align="left" valign="top" style="border-right:none; border-top:none; border-bottom:none; border-left:1px solid #999999;">
        <div class="nicht_im_print">
          <form action="tabelle_zur_karte_v3.php" method="post">
             <input style=" <?php if($_SESSION['Tabelle']['UERE_GRAU']) echo 'color:#999999;' ?> " class="nicht_im_print" onclick="submit();" type="checkbox" name="UERE" id="UERE" <?php 
			 if($_SESSION['Tabelle']['UERE']) echo "checked";
			 if($_SESSION['Tabelle']['UERE_GRAU']) echo "checked";
			 ?> />
             &Uuml;bergeordnete Raumeinheiten<br />
           	<?php   
		   	// nur sinnvolles Einblenden von Schaltern:
			if($_SESSION['Dokument']['Raumgliederung'] == "gem")
			{
				?>
				<input style="margin-left:20px;" class="nicht_im_print" onclick="submit();" type="checkbox" name="UERE_KRS" id="UERE_KRS" <?php if($_SESSION['Tabelle']['UERE_KRS']) echo "checked";?> /> Kreise<br />
                <?php 
			}
			if($_SESSION['Dokument']['Raumgliederung'] == "gem" 
			or $_SESSION['Dokument']['Raumgliederung'] == "krs" 
			or $_SESSION['Dokument']['Raumgliederung'] == "kfs" 
			or $_SESSION['Dokument']['Raumgliederung'] == "lks")
			{
			 ?>
			 <input style="margin-left:20px;" class="nicht_im_print" onclick="submit();" type="checkbox" name="UERE_BLD" id="UERE_BLD" <?php if($_SESSION['Tabelle']['UERE_BLD']) echo "checked";?> /> Bundesl&auml;nder<br />
             <?php 
			}
			?>
			<input style="margin-left:20px;" class="nicht_im_print" onclick="submit();" type="checkbox" name="UERE_BND" id="UERE_BND" <?php if($_SESSION['Tabelle']['UERE_BND']) echo "checked";?> /> Bundesrepublik<br />
           
			<?php 
			if(!$MITTLERE_AKTUALITAET_IGNORE)
			{
				?>
            	<input class="nicht_im_print" onclick="submit();" type="checkbox" name="AKTUALITAET" id="AKTUALITAET" <?php if($_SESSION['Tabelle']['AKTUALITAET']) { echo "checked"; } ?> /> 
   	      		Mittlere Grundaktualit&auml;t <a href="../../index.php?id=88" target="_blank"><img src="../gfx/icons/klein/document_search.png" width="14" height="14" alt="Glossar" /></a><br />
				<?php 
			}

			// Check auf aktivierte Zusatzzeitschnitte
			if(is_array($Zeitschnitte_vorh))
			{								 
				foreach($Zeitschnitte_vorh as $Zeitschnitt_Zusatz)
				{
					// gewählten Dokumentenzeitschnitt nicht mit aufführen
					if($Zeitschnitt_Zusatz != $_SESSION['Dokument']['Jahr_Anzeige'])
					{
						?>
			<input onclick="submit();" 
									name="ZS_<?php echo $Zeitschnitt_Zusatz; ?>" 
									type="checkbox" 
									value="<?php echo $Zeitschnitt_Zusatz; ?>"
									<?php if($_SESSION['Tabelle']['Zeitschnitt_Zusatz'][$Zeitschnitt_Zusatz]) 
									{
										echo "checked";
										// Variable setzen, dass zus. Zeitschnitt vorhanden ist
										$Zus_ZS_vorh = 1;
									}
									?>
						/>		 
						Zeitschnitt <?php echo $Zeitschnitt_Zusatz; ?> anfügen <br />
						<?php 
					}
									
				}
			}

			// Nur anzeigen, wenn zus. Zeitschnitt wirklich verfügbar ist
			if($Zus_ZS_vorh)
			{
				?>
				<input class="nicht_im_print" onclick="submit();" type="checkbox" name="VERGLEICH" id="VERGLEICH" <?php if($_SESSION['Tabelle']['VERGLEICH']) echo "checked";?> /> 
		Differenzen zusätzlicher Zeitschnitte<br />
				<?php 
			}
			?>
            <br />
            <input name="Zeitschnitt_UERE_Formular" type="hidden" value="1" />
            <!--
            <input style="background-color:#BDDDFD; padding-left:12px; padding-right:12px; padding-top:0px; padding-bottom:0px;" class="button_standard_abschicken_a nicht_im_print" name="" type="submit" value="OK" />
            -->
          </form>
        <br />
           <!-- 
           <div style="height:23px;">
           		 <a style="background-color:#BDDDFD; padding-left:12px; padding-right:12px; padding-top:2px; padding-bottom:2px;" class="button_standard_abschicken_a" 
            		target="_blank" href="index.php?id=44">Indikatorkennblatt</a>
           	</div>
           	<div style="height:23px;">
           		<a style="background-color:#BDDDFD; padding-left:12px; padding-right:12px; padding-top:2px; padding-bottom:2px;" class="button_standard_abschicken_a" 
            		onClick="javascript:window.print()">Tabelle drucken</a>
            </div> 
            -->
		 	
          </div>
        </td>
        <!--<td width="203" rowspan="7" align="left" valign="top" style="border-right:none; border-top:none; border-bottom:none; border-left:1px solid #999999;">
        <strong>IÖR-Monitor©Leibniz-Institut für ökologische Raumentwicklung</strong><br />
        <br />
        <div class="nicht_im_print">
        Kostenlose Bestellung der Daten im csv- oder xls-Format über 
        <a 
        style="background-color:#BDDDFD; padding-left:12px; padding-right:12px; padding-top:0px; padding-bottom:0px;" class="button_standard_abschicken_a" 
        href="mailto:monitor@ioer.de?subject=Monitor_Tabelle&body=Bestellung einer Tabelle%0A%0AIndikator: <?php 
		echo $_SESSION['Dokument']['Fuellung']['Indikator']."%0AInhalt: ".$_SESSION['Datenbestand_Ausgabe']
		."%0ARaumgliederung: ".$_SESSION['Dokument']['Raumgliederung_Ausgabe']
		."%0A%0AFormat: %0A%0AName: %0AAdresse: "; ?>">Email</a>
        </div>
        </td> -->
      </tr>
      <tr valign="top">
        <td style="border:none; padding:0px;">Zeitschnitt:</td>
        <td style="border:none;"><?php echo $_SESSION['Dokument']['Jahr_Anzeige']; ?></td>
      </tr>
      <tr valign="top">
        <td style="border:none; padding:0px;">Einbezogenes Gebiet:</td>
        <td style="border:none; white-space:normal;"><?php echo $_SESSION['Datenbestand_Ausgabe']; ?></td>
      </tr>
      <tr valign="top">
        <td style="border:none; padding:0px;">Raumgliederung:</td>
        <td style="border:none;"><?php 
			// Stringverarbeitung für Sondergebiete (Löschen von Zus. Textbausteinen)
			if($_SESSION['Dokument']['Raumgliederung_Ausgabe'][0] == '*') 
			{
				$_Raumgliederung_Legendentext = substr($_SESSION['Dokument']['Raumgliederung_Ausgabe'],6);
			}
			else
			{
				$_Raumgliederung_Legendentext = $_SESSION['Dokument']['Raumgliederung_Ausgabe'];
			}
			echo $_Raumgliederung_Legendentext; ?>
         </td>
      </tr>
      <tr valign="top">
        <td style="border:none; padding:0px;">Anzahl der Gebietseinheiten (n):</td>
        <td style="border:none;"><?php echo $n_Stichproben; ?></td>
      </tr>
      <tr valign="top">
        <td style="border:none; padding:0px;">Minimum:</td>
        <td style="border:none;"><?php if($_SESSION['Dokument']['ViewBerechtigung'] == "0"){ echo $Minimum; }else{ echo number_format($Minimum,$Rndgkpf = ($_SESSION['Dokument']['Fuellung']['Rundung']+1), ',', '.');} ?> (<?php echo $Minimum_Name; ?>)</td>
      </tr>
      <tr valign="top">
        <td style="border:none; padding:0px;">Maximum:</td>
        <td style="border:none;"><?php if($_SESSION['Dokument']['ViewBerechtigung'] == "0"){ echo $Maximum; }else{ echo  number_format($Maximum,$Rndgkpf = ($_SESSION['Dokument']['Fuellung']['Rundung']+1), ',', '.'); }?> (<?php echo $Maximum_Name; ?>)</td>
      </tr>
      <tr valign="top">
        <td style="border:none; padding:0px;">Arithmetisches Mittel:</td>
        <td style="border:none;"><?php  echo number_format($Ar_Mittel,$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.');   ?></td>
      </tr>
       <tr valign="top">
        <td style="border:none; padding:0px;">Bundesrepublik:</td>
        <td style="border:none;"><?php  
			if(!$Fehlercode_D) echo number_format($Wert_D,$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.');   
			?></td>
      </tr>
</table>




<br />
<form action="tabelleninformationen.php" method="post" class="nicht_im_print">
			  <!--<input onfocus="this.blur();" style="background:none; border:none;  padding-right:10px; font-size:12px;" type="submit" value="<?php echo $Werte[$i_ausg]['AGS']; ?>" /> -->
			  <div style="height:35px;">
              <input style="background-color:#BDDDFD; padding-left:12px; padding-right:12px; padding-top:1px; padding-bottom:1px;" class="button_standard_abschicken_a" 
              												name="schalter" type="submit" value="Statistische Kenngrößen und Histogramm" />
			  <input name="W_Min" type="hidden" value="<?php echo $Min_Ausgabe; ?>" />
			  <input name="Standardabweichung" type="hidden" value="<?php echo round($Standardabweichung,$_SESSION['Dokument']['Fuellung']['Rundung']); ?>" />
              <input name="Median_1_Wert" type="hidden" value="<?php echo round($Median_Wert,$_SESSION['Dokument']['Fuellung']['Rundung']); ?>" />
              <input name="Median_2_Wert" type="hidden" value="" />
              <input name="Median_1_Name" type="hidden" value="<?php echo round($Median_Name,$_SESSION['Dokument']['Fuellung']['Rundung']); ?>" />
              <input name="Median_2_Name" type="hidden" value="" />
              <input name="n" type="hidden" value="<?php echo $n_Stichproben; ?>" />
              <input name="AMittel" type="hidden" value="<?php echo round($Ar_Mittel,$_SESSION['Dokument']['Fuellung']['Rundung']); ?>" /> 
              </div>
              <div style="height:25px;">
                   	<a style="background-color:#BDDDFD; padding-left:12px; padding-right:12px; padding-top:2px; padding-bottom:2px;" class="button_standard_abschicken_a" 
            		onClick="javascript:window.print()">Tabelle drucken</a>
              </div>
</form>
<br />
<br />




<!--
<form action="" method="post">
	Test: Vergleichskarte <br />
    <?php 
	if(!$_SESSION['Tabelle']['Karte'])
	{
		?>
		<input name="k_in" type="submit" value="Karte einblenden" />
		<input name="karte" type="hidden" value="1" />
		<?php 
	}
	else
	{
		?>
		<input name="k_out" type="submit" value="Karte ausblenden" />
		<input name="karte" type="hidden" value="0" />
		<?php 
	}
	?>
    <input name="karteanzeige" type="hidden" value="1" />
</form>
 -->
<br />
<br />












<?php 
// ------------------------ Datentabelle ---------------------------
// Nur anzeigen, wenn Karte nicht für Anzeige ausgewählt:


	?>
	hier:
    <?php 
	echo "<pre>";
	print_r($_POST);
	echo "</pre>";
	
	?>
    <br />
<br />
<br />
<br />

	<table style="border-collapse:collapse;">
    
    
    
    
	<?php 
	
	
	
	
	// --------------- Daten für übergabe als POST abarbeiten ------------------------------------------
	
	// Erstellen der Sortieranweisungen für SQL
	if($_SESSION['Tabellen_Sortierung'])
	{
		if($_SESSION['Tabellen_Sortierung']=="NAME") $SQL_COLLATE = "COLLATE utf8_general_ci"; // anwenden, wenn nach Name sortiert wird (DIN 5007)
		$SQL_Order = ", ".$_SESSION['Tabellen_Sortierung']." ".$SQL_COLLATE." ".$_SESSION['Tabellen_Sortierung_asc_desc'].";";
	}
	
	$SQL_ANZEIGE = "SELECT * FROM t_temp_tabellentool ORDER BY FEHLERCODE ASC ".$SQL_Order;
	// Check, ob Abfrage erfolgreich war, ansnsten Ausführung mit Standard-Sortierung und setzen dieser in SESSION-Array (da evtl. fehlerhaft durch Ausblendung eines Zusatzzeitschnittes)
	if(!$Ergebnis_ANZEIGE = mysqli_query($Verbindung,$SQL_ANZEIGE))
	{
		$_SESSION['Tabellen_Sortierung'] = "NAME"; 
		$_SESSION['Tabellen_Sortierung_asc_desc'] = "ASC";
		$SQL_Order = ", ".$_SESSION['Tabellen_Sortierung']." ".$SQL_COLLATE." ".$_SESSION['Tabellen_Sortierung_asc_desc'].";";
		$SQL_ANZEIGE = "SELECT * FROM t_temp_tabellentool ORDER BY FEHLERCODE ASC ".$SQL_Order;
		$Ergebnis_ANZEIGE = mysqli_query($Verbindung,$SQL_ANZEIGE);
	}
	
	
	$i_anz = 0;
	while($ags = @mysqli_result($Ergebnis_ANZEIGE,$i_anz,'AGS'))
	{
		
		// Test auf Fehlercode
		if($FC = @mysqli_result($Ergebnis_ANZEIGE,$i_anz,'FEHLERCODE'))
		{
			$SQL_FC = "SELECT * FROM m_fehlercodes WHERE FEHLERCODE = '".$FC."'";
			$Ergebnis_FC = mysqli_query($Verbindung,$SQL_FC);
					
			$Fehlername = utf8_encode(mysqli_result($Ergebnis_FC,0,'FEHLER_NAME'));
			$Fehlerbschreibung = utf8_encode(mysqli_result($Ergebnis_FC,0,'FEHLER_BESCHREIBUNG'));
			$Fehlerfarbcode = mysqli_result($Ergebnis_FC,0,'FEHLER_FARBCODE');
		}
		else
		{
			$Fehlername = '';
			$Fehlerbschreibung = '';
			$Fehlerfarbcode = '';
		}
		
		?>
		<tr style="background:<?php if($i_anz % 5 == 0) { echo '#EEEEEE'; }else{ echo '#FFFFFF'; }?>;">
			<!-- Link zum Histogramm -->
			<td>
				<?php 
				if(!$FC) // Check auf Fehlercode
				{
					/* 
					?>
					<form action="objektinformationen.php" method="post">
					  <input type="image" src="../gfx/icons/klein/document_search.png" alt="Objektinfo"> 
					  <input name="ags" type="hidden" value="<?php echo $ags; ?>" />
					  <input name="name" type="hidden" value="<?php echo @mysqli_result($Ergebnis_ANZEIGE,$i_anz,'NAME'); ?>" />
					  <input name="wert" type="hidden" value="<?php echo  round(@mysqli_result($Ergebnis_ANZEIGE,$i_anz,'WERT'),$_SESSION['Dokument']['Fuellung']['Rundung']); ?>" />
					  <input name="W_Min" type="hidden" value="<?php echo $Min_Ausgabe; ?>" />
					  <input name="Standardabweichung" type="hidden" value="<?php echo round($Standardabweichung,$_SESSION['Dokument']['Fuellung']['Rundung']); ?>" />
					  <input name="Median_1_Wert" type="hidden" value="<?php echo  round($Median_Wert,$_SESSION['Dokument']['Fuellung']['Rundung']); ?>" />
					  <input name="Median_2_Wert" type="hidden" value="<?php  ?>" />
					  <input name="Median_1_Name" type="hidden" value="<?php echo  round($Median_Name,$_SESSION['Dokument']['Fuellung']['Rundung']); ?>" />
					  <input name="Median_2_Name" type="hidden" value="<?php  ?>" />
                      <input name="n" type="hidden" value="<?php echo $n_Stichproben; ?>" />
					  <input name="AMittel" type="hidden" value="<?php echo  round($Ar_Mittel,$_SESSION['Dokument']['Fuellung']['Rundung']); ?>" />  
					</form>
				<?php
				 */
				}
			?>
			</td>
			<!-- AGS -->
			<td>
				<?php echo @mysqli_result($Ergebnis_ANZEIGE,$i_anz,'AGS');  ?>
			</td>
			<!-- Name -->
			<td>
				<?php echo @mysqli_result($Ergebnis_ANZEIGE,$i_anz,'NAME');  ?>
			</td>
			<!-- Wert -->
			<td style=" text-align:right;">
				<?php 	if(!$FC) 
						{  
							if($_SESSION['Dokument']['ViewBerechtigung'] == "0")
							{ 
								echo number_format($x = (@mysqli_result($Ergebnis_ANZEIGE,$i_anz,'WERT') - 0),4, ',', '.'); 
							}
							else
							{ 
								echo number_format($x = (@mysqli_result($Ergebnis_ANZEIGE,$i_anz,'WERT') - 0),$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.'); 
							}
						}
						else
						{ 
							?><span style="color:#<?php echo $Fehlerfarbcode; ?>;"><?php echo $Fehlername; ?></span><?php 
						} 
						?>
			</td>
			<!-- Grundaktualitaet -->
			<?php
			if($_SESSION['Tabelle']['AKTUALITAET'])
			{
				?>
				<td style="color:#777; text-align:center;">
					<?php 
						if(!$FC) { echo @mysqli_result($Ergebnis_ANZEIGE,$i_anz,'AKT'); } 
					?>
				</td>
				<?php 
			}
			?>
	
			<!-- Uebergeordnete RE -->
			<?php  
			// Kreise
			if($_SESSION['Tabelle']['UERE_KRS'])
			{
	
				
				// sinnvolle Anzeige der Zeile nur bei Gemeinden
				if($_SESSION['Dokument']['Raumgliederung'] == "gem" )
				{
					?>
                    <!-- Trenner -->
                     <td style="background:#CCC; width:3px; padding:0px;" >&nbsp;</td>
                    <!-- Kreis -->
					<td>
					<?php 
						// Breite der Box (Festlegung)
						$Boxbreite = "100";
						?>
						<div style="width:<?php echo $Boxbreite; ?>px; height:15px; padding:0px; position:relative; border:solid 1px #999;">
						  <?php 
							if(!$FC) 
							{ 
								// Breite der Box in bezug zum Max-Wertn (auf 50% der Boxbreite wegen +-
								/* if(@mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_ZZS='WERT_DIFF_'.$ZZeitschnitt) > 0 or @mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_ZZS='WERT_DIFF_'.$ZZeitschnitt) < 0)
								{ */
									$Box_width = abs(50 * (@mysqli_result($Ergebnis_ANZEIGE,$i_anz,'WERT_KRS_DIFF')/$Max_diff_krs));  
								/* }
								else
								{ 	$Box_width=0; } */
								
								// Positionierung der Box von der Mitte aus
								if(@mysqli_result($Ergebnis_ANZEIGE,$i_anz,'WERT_KRS_DIFF') < 0) 
								{
									// Dynamisch den Platz vor der Box berechnen und als margin belegen
									$Box_Margin_left = 50 - $Box_width; 
									if(!$Box_width) $Box_Margin_left = "0"; // Falls Variable leer.... vermeidet XHTML Fehler
									$Box_BackColor = "#8888CC";
									$Textpos_align = "left";
									$Border_seite = "right";
								}
								else
								{
									$Box_Margin_left = 50; // bei >0 ist 50% immer korrekt
									$Box_BackColor = "#CC8888";
									$Textpos_align = "right";
									$Border_seite = "left";
								}
								?>
								<div style="height:100%;width:<?php echo $Box_width; ?>%; margin-left:<?php echo round($Box_Margin_left,0); ?>%; background-color:<?php echo $Box_BackColor; ?>; 
													position:absolute; z-index:5; border-<?php echo $Border_seite; ?>:#666 solid 1px;">
										
								</div>
								<div style="width:<?php echo $BB = ($Boxbreite-10); ?>px; padding-right:5px; padding-left:5px; position:absolute; z-index:10; text-align:<?php echo $Textpos_align; ?>;">
										<?php 
									
									if($_SESSION['Dokument']['ViewBerechtigung'] == "0")
									{ 
										echo number_format(@mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_ZZS='WERT_KRS_DIFF'),$rndg_p=($_SESSION['Dokument']['Fuellung']['Rundung']+1), ',', '.');
									}
									else
									{ 
										echo number_format(@mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_ZZS='WERT_KRS_DIFF'),$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.');
									}
									?>
								</div>
								<?php 
							}
							?>
						</div>
					  </td>
					  <td>
						<?php 
						if(!$FC) 
						{ 
							echo number_format(@mysqli_result($Ergebnis_ANZEIGE,$i_anz,'WERT_KRS'),$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.')." (".@mysqli_result($Ergebnis_ANZEIGE,$i_anz,'NAME_KRS').")"; 
						}
						?>
						</td>
						<!-- Grundaktualitaet KRS -->
						<?php
						if($_SESSION['Tabelle']['AKTUALITAET'])
						{
							?>
							<td style="color:#777; text-align:center;">
								<?php 
									if(!$FC) { echo @mysqli_result($Ergebnis_ANZEIGE,$i_anz,'AKT_KRS'); } 
								?>
							</td>
							<?php 
						}
					}
				}
				
			// Bundesländer
			if($_SESSION['Tabelle']['UERE_BLD'])
			{
				// sinnvolle Anzeige der kompletten Spalte nur bei Gemeinden oder Kreisen
				if($_SESSION['Dokument']['Raumgliederung'] == "gem" 
				or $_SESSION['Dokument']['Raumgliederung'] == "krs" 
				or $_SESSION['Dokument']['Raumgliederung'] == "kfs" 
				or $_SESSION['Dokument']['Raumgliederung'] == "lks")
				{
					?>
					<!-- Trenner -->
					<td style="background:#CCC; width:3px; padding:0px;" >&nbsp;</td>
					<!-- Bundesland -->
					<td>
						<?php 
						// Breite der Box (Festlegung)
						$Boxbreite = "100";
						?>
						<div style="width:<?php echo $Boxbreite; ?>px; height:15px; padding:0px; position:relative; border:solid 1px #999;">
						  <?php 
							if(!$FC) 
							{ 
								// Breite der Box in bezug zum Max-Wertn (auf 50% der Boxbreite wegen +-
								/* if(@mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_ZZS='WERT_DIFF_'.$ZZeitschnitt) > 0 or @mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_ZZS='WERT_DIFF_'.$ZZeitschnitt) < 0)
								{ */
									$Box_width = abs(50 * (@mysqli_result($Ergebnis_ANZEIGE,$i_anz,'WERT_BLD_DIFF')/$Max_diff_bld));  
								/* }
								else
								{ 	$Box_width=0; } */
								
								// Positionierung der Box von der Mitte aus
								if(@mysqli_result($Ergebnis_ANZEIGE,$i_anz,'WERT_BLD_DIFF') < 0) 
								{
									// Dynamisch den Platz vor der Box berechnen und als margin belegen
									$Box_Margin_left = 50 - $Box_width; 
									if(!$Box_width) $Box_Margin_left = "0"; // Falls Variable leer.... vermeidet XHTML Fehler
									$Box_BackColor = "#8888CC";
									$Textpos_align = "left";
									$Border_seite = "right";
								}
								else
								{
									$Box_Margin_left = 50; // bei >0 ist 50% immer korrekt
									$Box_BackColor = "#CC8888";
									$Textpos_align = "right";
									$Border_seite = "left";
								}
								?>
								<div style="height:100%;width:<?php echo $Box_width; ?>%; margin-left:<?php echo round($Box_Margin_left,0); ?>%; background-color:<?php echo $Box_BackColor; ?>; 
													position:absolute; z-index:5; border-<?php echo $Border_seite; ?>:#666 solid 1px;">
										
								</div>
								<div style="width:<?php echo $BB = ($Boxbreite-10); ?>px; padding-right:5px; padding-left:5px; position:absolute; z-index:10; text-align:<?php echo $Textpos_align; ?>;">
										<?php 
									
									if($_SESSION['Dokument']['ViewBerechtigung'] == "0")
									{ 
										echo number_format(@mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_ZZS='WERT_BLD_DIFF'),$rndg_p=($_SESSION['Dokument']['Fuellung']['Rundung']+1), ',', '.');
									}
									else
									{ 
										echo number_format(@mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_ZZS='WERT_BLD_DIFF'),$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.');
									}
									?>
								</div>
								<?php 
							}
							?>
						</div>
					</td>
					<td>
						<?php 
						if(!$FC) 
						{ 
							echo number_format(@mysqli_result($Ergebnis_ANZEIGE,$i_anz,'WERT_BLD'),$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.')." (".@mysqli_result($Ergebnis_ANZEIGE,$i_anz,'NAME_BLD').")"; 
						}
						?>
					</td>
					<!-- Grundaktualitaet BLD -->
					<?php
					if($_SESSION['Tabelle']['AKTUALITAET'])
					{
						?>
						<td style="color:#777; text-align:center;">
							<?php 
								if(!$FC) { echo @mysqli_result($Ergebnis_ANZEIGE,$i_anz,'AKT_BLD'); } 
							?>
						</td>
						<?php 
					}
				}
			}
			
			// Bund
			if($_SESSION['Tabelle']['UERE_BND'])
			{
					?>
					<!-- Trenner -->
					<td style="background:#CCC; width:3px; padding:0px;" >&nbsp;</td>
					<!-- Bund -->
					<td>
						<?php 
						// Breite der Box (Festlegung)
						$Boxbreite = "100";
						?>
						<div style="width:<?php echo $Boxbreite; ?>px; height:15px; padding:0px; position:relative; border:solid 1px #999;">
						  <?php 
							if(!$FC) 
							{ 
								// Breite der Box in bezug zum Max-Wertn (auf 50% der Boxbreite wegen +-
								/* if(@mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_ZZS='WERT_DIFF_'.$ZZeitschnitt) > 0 or @mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_ZZS='WERT_DIFF_'.$ZZeitschnitt) < 0)
								{ */
									$Box_width = abs(50 * (@mysqli_result($Ergebnis_ANZEIGE,$i_anz,'WERT_BND_DIFF')/$Max_diff_bnd));  
								/* }
								else
								{ 	$Box_width=0; } */
								
								// Positionierung der Box von der Mitte aus
								if(@mysqli_result($Ergebnis_ANZEIGE,$i_anz,'WERT_BND_DIFF') < 0) 
								{
									// Dynamisch den Platz vor der Box berechnen und als margin belegen
									$Box_Margin_left = 50 - $Box_width; 
									if(!$Box_width) $Box_Margin_left = "0"; // Falls Variable leer.... vermeidet XHTML Fehler
									$Box_BackColor = "#8888CC";
									$Textpos_align = "left";
									$Border_seite = "right";
								}
								else
								{
									$Box_Margin_left = 50; // bei >0 ist 50% immer korrekt
									$Box_BackColor = "#CC8888";
									$Textpos_align = "right";
									$Border_seite = "left";
								}
								?>
								<div style="height:100%;width:<?php echo $Box_width; ?>%; margin-left:<?php echo round($Box_Margin_left,0); ?>%; background-color:<?php echo $Box_BackColor; ?>; 
													position:absolute; z-index:5; border-<?php echo $Border_seite; ?>:#666 solid 1px;">
										
								</div>
								<div style="width:<?php echo $BB = ($Boxbreite-10); ?>px; padding-right:5px; padding-left:5px; position:absolute; z-index:10; text-align:<?php echo $Textpos_align; ?>;">
										<?php 
									
									if($_SESSION['Dokument']['ViewBerechtigung'] == "0")
									{ 
										echo number_format(@mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_ZZS='WERT_BND_DIFF'),$rndg_p=($_SESSION['Dokument']['Fuellung']['Rundung']+1), ',', '.');
									}
									else
									{ 
										echo number_format(@mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_ZZS='WERT_BND_DIFF'),$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.');
									}
									?>
								</div>
								<?php 
							}
							?>
						</div>
					</td>
					<td>
						<?php 
						if(!$FC) 
						{ 
							echo number_format(@mysqli_result($Ergebnis_ANZEIGE,$i_anz,'WERT_BND'),$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.'); 
						}
						?>
					</td>
					<!-- Grundaktualitaet BND -->
					<?php
					if($_SESSION['Tabelle']['AKTUALITAET'])
					{
						?>
						<td style="color:#777; text-align:center;">
							<?php 
								if(!$FC) { echo @mysqli_result($Ergebnis_ANZEIGE,$i_anz,'AKT_BND'); } 
							?>
						</td>
						<?php 
					}
			}
			
			// ----------------------- Zusätzliche Zeitschnitte ausgeben -----------------------------
			
			if(is_array($Zeitschnitte_vorh))
			{
				foreach($Zeitschnitte_vorh as $ZZeitschnitt)
				{
					if($ZZeitschnitt != $_SESSION['Dokument']['Jahr_Anzeige'] and $_SESSION['Tabelle']['Zeitschnitt_Zusatz'][$ZZeitschnitt] == 1) // Aktuellen Zeitschnitt nicht berücksichtigen!
					{
						
						// Test auf Fehlercode im Zusatzzeitschnitt
						if($FC = @mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_FC = 'FEHLERCODE_'.$ZZeitschnitt))
						{
							$SQL_FC = "SELECT * FROM m_fehlercodes WHERE FEHLERCODE = '".$FC."'";
							$Ergebnis_FC = mysqli_query($Verbindung,$SQL_FC,$Verbindung);
									
							$Fehlername = utf8_encode(mysqli_result($Ergebnis_FC,0,'FEHLER_NAME'));
							$Fehlerbschreibung = utf8_encode(mysqli_result($Ergebnis_FC,0,'FEHLER_BESCHREIBUNG'));
							$Fehlerfarbcode = mysqli_result($Ergebnis_FC,0,'FEHLER_FARBCODE');
						}
						else
						{
							$Fehlername = '';
							$Fehlerbschreibung = '';
							$Fehlerfarbcode = '';
						}
						
						?>
						<!-- Trenner -->
						<td style="background:#CCC;" >&nbsp;</td>
						
						 <!-- Absolute Änderung -->
						 <?php 
						if($_SESSION['Tabelle']['VERGLEICH'] == '1')
						{
							?>
							<td>
								<?php 
								// Breite der Box (Festlegung)
								$Boxbreite = "100";
								?>
								<div style="width:<?php echo $Boxbreite; ?>px; height:15px; padding:0px; position:relative; border:solid 1px #999;">
								  <?php 
									if(!$FC) 
									{ 
										// Breite der Box in bezug zum Max-Wertn (auf 50% der Boxbreite wegen +-
										/* if(@mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_ZZS='WERT_DIFF_'.$ZZeitschnitt) > 0 or @mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_ZZS='WERT_DIFF_'.$ZZeitschnitt) < 0)
										{ */
											$Box_width = @abs(50 * (@mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_ZZS='WERT_DIFF_'.$ZZeitschnitt)/$Max_abs[$ZZeitschnitt]));  
										/* }
										else
										{ 	$Box_width=0; } */
										
										// Positionierung der Box von der Mitte aus
										if(@mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_ZZS='WERT_DIFF_'.$ZZeitschnitt) < 0) 
										{
											// Dynamisch den Platz vor der Box berechnen und als margin belegen
											$Box_Margin_left = 50 - $Box_width; 
											if(!$Box_width) $Box_Margin_left = "0"; // Falls Variable leer.... vermeidet XHTML Fehler
											$Box_BackColor = "#8888CC";
											$Textpos_align = "left";
											$Border_seite = "right";
										}
										else
										{
											$Box_Margin_left = 50; // bei >0 ist 50% immer korrekt
											$Box_BackColor = "#CC8888";
											$Textpos_align = "right";
											$Border_seite = "left";
										}
										?>
										<div style="height:100%;width:<?php echo $Box_width; ?>%; margin-left:<?php echo round($Box_Margin_left,0); ?>%; background-color:<?php echo $Box_BackColor; ?>; 
													position:absolute; z-index:5; border-<?php echo $Border_seite; ?>:#666 solid 1px;">
										
										</div>
										<div style="width:<?php echo $BB = ($Boxbreite-10); ?>px; padding-right:5px; padding-left:5px; position:absolute; z-index:10; text-align:<?php echo $Textpos_align; ?>;">
											<?php 
											
											if($_SESSION['Dokument']['ViewBerechtigung'] == "0")
											{ 
												echo number_format(@mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_ZZS='WERT_DIFF_'.$ZZeitschnitt),$rndg_p=($_SESSION['Dokument']['Fuellung']['Rundung']+1), ',', '.');
											}
											else
											{ 
												echo number_format(@mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_ZZS='WERT_DIFF_'.$ZZeitschnitt),$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.');
											}
											?>
										</div>
										<?php 
									}
		
								  ?>
								</div>
                            <form action="" method="post">
                                	<input name="vergleich[WERT_DIFF][<?php echo $ZZeitschnitt; ?>][<?php echo @mysqli_result($Ergebnis_ANZEIGE,$i_anz,'AGS');  ?>]" type="hidden" value="<?php 
										echo number_format(@mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_ZZS='WERT_DIFF_'.$ZZeitschnitt),$rndg_p=($_SESSION['Dokument']['Fuellung']['Rundung']+1), ',', '.'); 
										?>" />
                                <input type="submit" value="Wertdifferenz <?php echo $ZZeitschnitt; ?> zu <?php echo $_SESSION['Dokument']['Jahr_Anzeige']; ?>" />
                              </form>
							</td>
							<?php 
						}
						?>
						<!-- Wert -->
						<td style="text-align:right;">
							
								<?php 
								if(!$FC) 
								{ 
									if($_SESSION['Dokument']['ViewBerechtigung'] == "0")
									{ 
										echo number_format($x = (@mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_ZZS='WERT_'.$ZZeitschnitt) - 0),4, ',', '.');
									}
									else
									{ 
										echo number_format($x = (@mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_ZZS='WERT_'.$ZZeitschnitt) - 0),$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.');
									}
								}
								else
								{ 
									?><span style="color:#<?php echo $Fehlerfarbcode; ?>;"><?php echo $Fehlername; ?></span><?php 
								} 
								?>
						</td>
						<!-- Relative Änderung -->
						<?php /* ?>
						<td>
							<?php 
							// Breite der Box (Festlegung)
							$Boxbreite = "80";
							?>
							<div style="width:<?php echo $Boxbreite; ?>px; height:15px; padding:0px;">
							  <?php 
								if(!$FC) 
								{ 
									// Breite der Box in bezug zum Max-Wertn (auf 50% der Boxbreite wegen +-
									// if(@mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_ZZS='WERT_DIFF_'.$ZZeitschnitt) > 0 or @mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_ZZS='WERT_DIFF_'.$ZZeitschnitt) < 0)
									//{ 
										$Box_width = abs(50 * (@mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_ZZS='WERT_RELATIV_'.$ZZeitschnitt)/$Max_rel[$ZZeitschnitt]));  
									// }
									//else
									//{ 	$Box_width=0; } 
									
									// Positionierung der Box von der Mitte aus
									if(@mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_ZZS='WERT_RELATIV_'.$ZZeitschnitt) < 0) 
									{
										$Box_Margin_left = 50 - $Box_width; // Dynamisch den Platz vor der Box berechnen und als margin belegen
										$Box_BackColor = "#8888CC";
									}
									else
									{
										$Box_Margin_left = 50; // bei >0 ist 50% immer korrekt
										$Box_BackColor = "#CC8888";
									}
									?>
									<div style="width:<?php echo $Box_width; ?>%; margin-left:<?php echo $Box_Margin_left; ?>%; background-color:<?php echo $Box_BackColor; ?>;">
										<?php 
										
										if($_SESSION['Dokument']['ViewBerechtigung'] == "0")
										{ 
											echo @mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_ZZS='WERT_RELATIV_'.$ZZeitschnitt); 
										}
										else
										{ 
											echo number_format(@mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_ZZS='WERT_RELATIV_'.$ZZeitschnitt),$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.');
										}
										?>
									</div>
									<?php 
								}
	
							  ?>
							</div>
						</td>
					   <?php */ ?>
					   
						<!-- Grundaktualitaet -->
						<?php
						if($_SESSION['Tabelle']['AKTUALITAET'])
						{
							?>
							<td  style="color:#777; text-align:center;">
								<?php 
									if(!$FC) echo @mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_ZZS='AKT_'.$ZZeitschnitt); 
								?>
							</td>
							<?php 
						}
						?>
				
						<!-- Uebergeordnete RE -->
						<?php 
						if($_SESSION['Tabelle']['UERE_KRS'])
						{	
								?>
								<!-- Kreis -->
								<?php 
								// sinnvolle Anzeige der Zeile nur bei Gemeinden
								if($_SESSION['Dokument']['Raumgliederung'] == "gem" )
								{
									?>
									<td>
									<?php        
									if(!$FC) echo number_format(@mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_ZZS='WERT_KRS_'.$ZZeitschnitt),$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.')
											." (".@mysqli_result($Ergebnis_ANZEIGE,$i_anz,'NAME_KRS').")"; 
									?>
									</td>
									<?php
								}
							}
						if($_SESSION['Tabelle']['UERE_BLD'])
						{
							// sinnvolle Anzeige der kompletten Spalte nur bei Gemeinden oder Kreisen
							if($_SESSION['Dokument']['Raumgliederung'] == "gem" 
							or $_SESSION['Dokument']['Raumgliederung'] == "krs" 
							or $_SESSION['Dokument']['Raumgliederung'] == "kfs" 
							or $_SESSION['Dokument']['Raumgliederung'] == "lks")
							{
								?>
								<!-- Bundesland -->
								<td>
									<?php 
									if(!$FC) echo number_format(@mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_ZZS='WERT_BLD_'.$ZZeitschnitt),$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.')
											." (".@mysqli_result($Ergebnis_ANZEIGE,$i_anz,'NAME_BLD').")"; 
									?>
								</td>
								<?php 
							}
						}
						if($_SESSION['Tabelle']['UERE_BND'])
						{
								?>
								<!-- Bundesland -->
								<td>
									
									<?php 
									
									if(!$FC) echo number_format(@mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_ZZS='WERT_BND_'.$ZZeitschnitt),$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.'); 
									?>
								</td>
								<?php 						
						}
					}
				}
			}
			
			
			
			
			
			?>
			
		</tr>
		<?php 
		
		$i_anz++;
	}
	
	
	?>
	</table>
	<br />
<br />
<br />


	<?php 
	// ENDE der Datentabelle






// ---------------------------------------------------------
// Ausgabe als Tabelle
// ---------------------------------------------------------
// evtl. Schalter für Tabellenausgabe
if(!$jdöwjepofwj)
{

	?>
	<div class="Text_10px nicht_im_print" style="padding-bottom:2px;">Sortierung über Klick in jeweiligen Spaltennamen möglich</div>
	<table style="border-collapse:collapse;">
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    		<?php 
		// ------------------------ Über-Kopfzeile ---------------------------
		?>
	<tr>
		<!-- Überkopf über direkten Werten -->
		<?php
		if($_SESSION['Tabelle']['AKTUALITAET'])
		{
			$clospan_a = "5";
		}
		else
		{
			$clospan_a = "4";
		}
		?>
        <th style="padding:10px; color:#444444;" colspan="<?php echo $clospan_a; ?>" >
		<?php 
			switch ($_SESSION['Dokument']['Raumgliederung'])
				{
				case 'gem':
				echo 'Gemeinde';
				break;
				case 'krs':
				echo 'Kreis';
				break;
				case 'bld':
				echo 'Bundesland';
				break;
				default:
				echo 'Indikatorwert';
				break;
			}

			echo " (".$_SESSION['Dokument']['Jahr_Anzeige'].")"; 
			?>
         </th>
         <!-- Überkopf über Uebergeordnete RE -->
		<?php
			// Kreise
			if($_SESSION['Tabelle']['UERE_KRS'])
			{
				$clospan_b = 2;
				
				
				// sinnvolle Anzeige der Zeile nur bei Gemeinden
				if($_SESSION['Dokument']['Raumgliederung'] == "gem" )
				{
					?>
					<!-- Trenner -->
					<th style="background:#CCC; width:1px; padding:0px;" >&nbsp;</th>
					 <?php
					if($_SESSION['Tabelle']['AKTUALITAET'])
					{
						$clospan_b = $clospan_b+1;
					}
					?>
					<th style="padding:10px; color:#444444;" colspan="<?php echo $clospan_b; ?>">Übergeordneter Kreis <?php echo " (".$_SESSION['Dokument']['Jahr_Anzeige'].")"; ?></th>
					<?php
				}
			}
			
			// Bundesländer
			if($_SESSION['Tabelle']['UERE_BLD'])
			{	
				// sinnvolle Anzeige der kompletten Spalte nur bei Gemeinden oder Kreisen
				if($_SESSION['Dokument']['Raumgliederung'] == "gem" 
				or $_SESSION['Dokument']['Raumgliederung'] == "krs" 
				or $_SESSION['Dokument']['Raumgliederung'] == "kfs" 
				or $_SESSION['Dokument']['Raumgliederung'] == "lks")
				{
					
					$clospan_b = 2;
				
					?>
					<!-- Trenner -->
					<th style="background:#CCC; width:1px; padding:0px;" >&nbsp;</th>
					 <?php
					if($_SESSION['Tabelle']['AKTUALITAET'])
					{
						$clospan_b = $clospan_b+1;
					}
					?>
					<th style="padding:10px; color:#444444;" colspan="<?php echo $clospan_b; ?>">Übergeordnetes Bundesland <?php echo " (".$_SESSION['Dokument']['Jahr_Anzeige'].")"; ?></th>
					<?php
				}
			}
			
			// Bund
			if($_SESSION['Tabelle']['UERE_BND'])
			{	
				$clospan_c = 2;

				?>
				<!-- Trenner -->
				<th style="background:#CCC; width:1px; padding:0px;" >&nbsp;</th>
				 <?php
				if($_SESSION['Tabelle']['AKTUALITAET'])
				{
					$clospan_c = $clospan_c+1;
				}
				?>
				<th style="padding:10px; color:#444444;" colspan="<?php echo $clospan_c; ?>">Gesamte Bundesrepublik <?php echo " (".$_SESSION['Dokument']['Jahr_Anzeige'].")"; ?></th>
                <?php
			}
			
			
			// ----------------------- Zusätzliche Zeitschnitte ausgeben -----------------------------
			
			if(is_array($Zeitschnitte_vorh))
			{
				foreach($Zeitschnitte_vorh as $ZZeitschnitt)
				{
					if($ZZeitschnitt != $_SESSION['Dokument']['Jahr_Anzeige'] and $_SESSION['Tabelle']['Zeitschnitt_Zusatz'][$ZZeitschnitt] == 1) // Aktuellen Zeitschnitt nicht berücksichtigen!
					{
						$clospan_z = 1;
						?>
						<!-- Trenner -->
						<th style="background:#CCC; width:3px;" >&nbsp;</th>
						
						<!-- absolute Veränderung zum gewählten Basiszeitschnitt -->
						<?php 
						if($_SESSION['Tabelle']['VERGLEICH'] == '1')
						{	
							$clospan_z = $clospan_z+1;
						}
						?>
						
						<!-- Grundaktualitaet -->
						<?php
						if($_SESSION['Tabelle']['AKTUALITAET'])
						{
							$clospan_z = $clospan_z+1;
						}
						?>
				
						<!-- Uebergeordnete RE -->
						<?php 
						if($_SESSION['Tabelle']['UERE_KRS'])
						{
							?>
								<!-- Kreis -->
								<?php 
								// sinnvolle Anzeige der Zeile nur bei Gemeinden
								if($_SESSION['Dokument']['Raumgliederung'] == "gem" )
								{
									$clospan_z = $clospan_z+1;
								}
						}
						if($_SESSION['Tabelle']['UERE_BLD'])
						{
							// sinnvolle Anzeige der kompletten Spalte nur bei Gemeinden oder Kreisen
							if($_SESSION['Dokument']['Raumgliederung'] == "gem" 
							or $_SESSION['Dokument']['Raumgliederung'] == "krs" 
							or $_SESSION['Dokument']['Raumgliederung'] == "kfs" 
							or $_SESSION['Dokument']['Raumgliederung'] == "lks")
							{	
								$clospan_z = $clospan_z+1;
							}
						}
						if($_SESSION['Tabelle']['UERE_BND'])
						{	
							$clospan_z = $clospan_z+1;
						}
						?>
						<th style="padding:10px; color:#444444;" colspan="<?php echo $clospan_z; ?>"><?php echo $ZZeitschnitt; ?></th>
                    	<?php
					}
				}
			}
	
			?>
	  </tr>
	
	<?php 
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    

		// ------------------------ Kopfzeile ---------------------------
		?>
	<tr>
			<!-- Link zum Histogramm -->
			<th>&nbsp;
				
			</th>
			<!-- AGS -->
			<th style=" <?php if($_SESSION['Tabellen_Sortierung'] == 'AGS'){ echo 'background-color:#e0ebc5;'; } ?> ">
				<a href="?sort=AGS">
				  <div style="
					display:block;
					padding-top:2px; 
					padding-bottom:2px; 
					padding-left:10px; 
					padding-right:15px; 
					margin-right:3px;
					<?php 
					  if($_SESSION['Tabellen_Sortierung'] == 'AGS')
					  {
							if($_SESSION['Tabellen_Sortierung_asc_desc'] == "ASC")
							{ ?>  
										background-image:url(../gfx/sort_asc.png); 
										background-repeat:no-repeat; 
										background-position: right 4px;
										<?php 
							} 
							if($_SESSION['Tabellen_Sortierung_asc_desc'] == "DESC") 
							{ ?> 
										background-image:url(../gfx/sort_desc.png); 
										background-repeat:no-repeat; 
										background-position: right 4px;
										<?php 
							} 
						}
				?>">AGS</div></a>
				
				
			</th>
			<!-- Name -->
			<th style=" <?php if($_SESSION['Tabellen_Sortierung'] == 'NAME'){ echo 'background-color:#e0ebc5;'; } ?> ">
				<a href="?sort=NAME">
					<div style="
						height:100%;
						display:block;
						padding-top:2px; 
						padding-bottom:2px; 
						padding-left:10px; 
						padding-right:15px; 
						margin-right:3px;
						<?php 
					  if($_SESSION['Tabellen_Sortierung'] == 'NAME')
					  {
								if($_SESSION['Tabellen_Sortierung_asc_desc'] == "ASC")
								{ ?>  
										background-image:url(../gfx/sort_asc.png); 
										background-repeat:no-repeat; 
										background-position: right 4px;
										<?php 
								} 
								if($_SESSION['Tabellen_Sortierung_asc_desc'] == "DESC") 
								{ ?> 
										background-image:url(../gfx/sort_desc.png); 
										background-repeat:no-repeat; 
										background-position: right 4px;
										<?php 
								} 
						}
				?>">Name</div></a>
			</th>
			<!-- Wert -->
			<th style=" <?php if($_SESSION['Tabellen_Sortierung'] == 'WERT'){ echo 'background-color:#e0ebc5;'; } ?> ">
				<a href="?sort=WERT">
					<div style="
						display:block;
						padding-top:2px; 
						padding-bottom:2px; 
						padding-left:10px; 
						padding-right:15px; 
						margin-right:3px;
						<?php 
					  if($_SESSION['Tabellen_Sortierung'] == 'WERT')
					  {
							if($_SESSION['Tabellen_Sortierung_asc_desc'] == "ASC")
							{ ?>  
										background-image:url(../gfx/sort_asc.png); 
										background-repeat:no-repeat; 
										background-position: right 4px;
										<?php 
							} 
							if($_SESSION['Tabellen_Sortierung_asc_desc'] == "DESC") 
							{ ?> 
										background-image:url(../gfx/sort_desc.png); 
										background-repeat:no-repeat; 
										background-position: right 4px;
										<?php 
							} 
					  }
				?>">
					<?php 
					switch ($_SESSION['Dokument']['Raumgliederung'])
					{
						case 'gem':
						echo 'Gemeindewert';
						break;
						case 'krs':
						echo 'Kreiswert';
						break;
						case 'bld':
						echo 'Bundeslandwert';
						break;
						default:
						echo 'Wert';
						break;
					}
					?>
				</div></a>
			</th>
			<!-- Grundaktualitaet -->
			<?php
			if($_SESSION['Tabelle']['AKTUALITAET'])
			{
				?>
				<th style=" <?php if($_SESSION['Tabellen_Sortierung'] == 'AKT'){ echo 'background-color:#e0ebc5;'; } ?> ">
					<a href="?sort=AKT">
						<div style="padding-top:2px; padding-bottom:2px; padding-left:10px; padding-right:10px; margin-right:3px;<?php 
						  if($_SESSION['Tabellen_Sortierung'] == 'AKT')
						  {
								if($_SESSION['Tabellen_Sortierung_asc_desc'] == "ASC")
								{ ?>  
											background-image:url(../gfx/sort_asc.png); 
											background-repeat:no-repeat; 
											background-position: right 4px;
											<?php 
								} 
								if($_SESSION['Tabellen_Sortierung_asc_desc'] == "DESC") 
								{ ?> 
											background-image:url(../gfx/sort_desc.png); 
											background-repeat:no-repeat; 
											background-position: right 4px;
											<?php 
								} 
						  }
						?>">Mittlere<br />
						  Grund-<br />
						  aktualit&auml;t&nbsp;<!--<a class="nicht_im_print" href="../../index.php?id=88" target="_blank"><img src="../gfx/icons/klein/document_search.png" width="14" height="14" alt="Glossar" /></a> -->
						</div>
					 </a>
				</th>
				<?php 
			}
			?>
			<!-- Uebergeordnete RE -->
			<?php
			// Kreise
			if($_SESSION['Tabelle']['UERE_KRS'])
			{
	
				
				// sinnvolle Anzeige der Zeile nur bei Gemeinden
				if($_SESSION['Dokument']['Raumgliederung'] == "gem" )
				{
					?>
					<!-- Trenner -->
					<th style="background:#CCC; width:1px; padding:0px;" >&nbsp;</th>
					<!-- Kreis -->
					<th style=" <?php if($_SESSION['Tabellen_Sortierung'] == 'WERT_KRS_DIFF'){ echo 'background-color:#e0ebc5;'; } ?> ">
					   <a href="?sort=WERT_KRS_DIFF">
						   <div style="padding-top:2px; padding-bottom:2px; padding-left:10px; padding-right:10px; margin-right:3px;<?php 
						  if($_SESSION['Tabellen_Sortierung'] == 'WERT_KRS_DIFF')
						  {
								if($_SESSION['Tabellen_Sortierung_asc_desc'] == "ASC")
								{ ?>  
											background-image:url(../gfx/sort_asc.png); 
											background-repeat:no-repeat; 
											background-position: right 4px;
											<?php 
								} 
								if($_SESSION['Tabellen_Sortierung_asc_desc'] == "DESC") 
								{ ?> 
											background-image:url(../gfx/sort_desc.png); 
											background-repeat:no-repeat; 
											background-position: right 4px;
											<?php 
								} 
						  }
						?>">
							   Differenz <br />
								zum Kreis
						  <?php 
														   
							   // Max für Box dieser Spaltenwerte ermitteln
							   $SQL_Max = "SELECT MAX(WERT_KRS_DIFF) AS MAX, MIN(WERT_KRS_DIFF) AS MIN FROM t_temp_tabellentool WHERE FEHLERCODE = '0'"; 
							   $Ergebnis_Max = mysqli_query($Verbindung,$SQL_Max); 
							   $Max_diff_krs = @mysqli_result($Ergebnis_Max,0,'MAX');
							   $Min = abs(@mysqli_result($Ergebnis_Max,0,'MIN'));
							   if($Min > $Max_diff_krs) $Max_diff_krs = $Min;
							   ?>
						   </div>
						</a>
					</th>
					<th style=" <?php if($_SESSION['Tabellen_Sortierung'] == 'WERT_KRS'){ echo 'background-color:#e0ebc5;'; } ?> ">
					   <a href="?sort=WERT_KRS">
						<div style="padding-top:2px; padding-bottom:2px; padding-left:10px; padding-right:10px; margin-right:3px;<?php 
						  if($_SESSION['Tabellen_Sortierung'] == 'WERT_KRS')
						  {
								if($_SESSION['Tabellen_Sortierung_asc_desc'] == "ASC")
								{ ?> 
											background-image:url(../gfx/sort_asc.png); 
											background-repeat:no-repeat; 
											background-position: right 4px;
											<?php 
								} 
								if($_SESSION['Tabellen_Sortierung_asc_desc'] == "DESC") 
								{ ?> 
											background-image:url(../gfx/sort_desc.png); 
											background-repeat:no-repeat; 
											background-position: right 4px;
											<?php 
								} 
						  }
						?>">Kreiswert<br />
							(Name)</div>
					   </a>
					</th>
					 <?php
					if($_SESSION['Tabelle']['AKTUALITAET'])
					{
						?>
						<th style=" <?php if($_SESSION['Tabellen_Sortierung'] == 'AKT_KRS'){ echo 'background-color:#e0ebc5;'; } ?> ">
						   <a href="?sort=AKT_KRS">
							   <div style="padding-top:2px; padding-bottom:2px; padding-left:10px; padding-right:10px; margin-right:3px;<?php 
							  if($_SESSION['Tabellen_Sortierung'] == 'AKT_KRS')
							  {
									if($_SESSION['Tabellen_Sortierung_asc_desc'] == "ASC")
									{ ?>  
												background-image:url(../gfx/sort_asc.png); 
												background-repeat:no-repeat; 
												background-position: right 4px;
												<?php 
									} 
									if($_SESSION['Tabellen_Sortierung_asc_desc'] == "DESC") 
									{ ?> 
												background-image:url(../gfx/sort_desc.png); 
												background-repeat:no-repeat; 
												background-position: right 4px;
												<?php 
									} 
							  }
							?>">
								   Mittlere<br />
								   Grundakt.<br />
								   Kreis
							   </div>
							</a>   
						</th> 
						<?php 
					}
				}
			}
			// Bundesländer
			if($_SESSION['Tabelle']['UERE_BLD'])
			{	
				// sinnvolle Anzeige der kompletten Spalte nur bei Gemeinden oder Kreisen
				if($_SESSION['Dokument']['Raumgliederung'] == "gem" 
				or $_SESSION['Dokument']['Raumgliederung'] == "krs" 
				or $_SESSION['Dokument']['Raumgliederung'] == "kfs" 
				or $_SESSION['Dokument']['Raumgliederung'] == "lks")
				{
					?>
					<!-- Trenner -->
					<th style="background:#CCC; width:1px; padding:0px;" >&nbsp;</th>
					<!-- Bundesland -->
					<th style=" <?php if($_SESSION['Tabellen_Sortierung'] == 'WERT_BLD_DIFF'){ echo 'background-color:#e0ebc5;'; } ?> ">
					   <a href="?sort=WERT_BLD_DIFF">
						   <div style="padding-top:2px; padding-bottom:2px; padding-left:10px; padding-right:10px; margin-right:3px;<?php 
						  if($_SESSION['Tabellen_Sortierung'] == 'WERT_BLD_DIFF')
						  {
								if($_SESSION['Tabellen_Sortierung_asc_desc'] == "ASC")
								{ ?>  
											background-image:url(../gfx/sort_asc.png); 
											background-repeat:no-repeat; 
											background-position: right 4px;
											<?php 
								} 
								if($_SESSION['Tabellen_Sortierung_asc_desc'] == "DESC") 
								{ ?> 
											background-image:url(../gfx/sort_desc.png); 
											background-repeat:no-repeat; 
											background-position: right 4px;
											<?php 
								} 
						  }
							?>">
							Differenz <br />
						  zum Bundesland
							<?php 
														
							// Max für Box dieser Spaltenwerte ermitteln
							$SQL_Max = "SELECT MAX(WERT_BLD_DIFF) AS MAX, MIN(WERT_BLD_DIFF) AS MIN FROM t_temp_tabellentool WHERE FEHLERCODE = '0'"; 
							$Ergebnis_Max = mysqli_query($Verbindung,$SQL_Max); 
							$Max_diff_bld = @mysqli_result($Ergebnis_Max,0,'MAX');
							$Min = abs(@mysqli_result($Ergebnis_Max,0,'MIN'));
							if($Min > $Max_diff_bld) $Max_diff_bld = $Min;
							?>
						</div>
					   </a>
					</th>
					<th style=" <?php if($_SESSION['Tabellen_Sortierung'] == 'WERT_BLD'){ echo 'background-color:#e0ebc5;'; } ?> ">
					   <a href="?sort=WERT_BLD">
						   <div style="padding-top:2px; padding-bottom:2px; padding-left:10px; padding-right:10px; margin-right:3px;<?php 
						  if($_SESSION['Tabellen_Sortierung'] == 'WERT_BLD')
						  {
								if($_SESSION['Tabellen_Sortierung_asc_desc'] == "ASC")
								{ ?>  
											background-image:url(../gfx/sort_asc.png); 
											background-repeat:no-repeat; 
											background-position: right 4px;
											<?php 
								} 
								if($_SESSION['Tabellen_Sortierung_asc_desc'] == "DESC") 
								{ ?> 
											background-image:url(../gfx/sort_desc.png); 
											background-repeat:no-repeat; 
											background-position: right 4px;
											<?php 
								} 
						  }
						?>">Bundeslandwert<br />
							(Name)
						</div>
					   </a>
					</th>
					 <?php
					if($_SESSION['Tabelle']['AKTUALITAET'])
					{
						?>
						<th style=" <?php if($_SESSION['Tabellen_Sortierung'] == 'AKT_BLD'){ echo 'background-color:#e0ebc5;'; } ?> ">
						   <a href="?sort=AKT_BLD">
							   <div style="padding-top:2px; padding-bottom:2px; padding-left:10px; padding-right:10px; margin-right:3px;<?php 
							  if($_SESSION['Tabellen_Sortierung'] == 'AKT_BLD')
							  {
									if($_SESSION['Tabellen_Sortierung_asc_desc'] == "ASC")
									{ ?>  
												background-image:url(../gfx/sort_asc.png); 
												background-repeat:no-repeat; 
												background-position: right 4px;
												<?php 
									} 
									if($_SESSION['Tabellen_Sortierung_asc_desc'] == "DESC") 
									{ ?> 
												background-image:url(../gfx/sort_desc.png); 
												background-repeat:no-repeat; 
												background-position: right 4px;
												<?php 
									} 
							  }
							?>">
								Mittlere<br />
								Grundakt.<br />
								Bundesland
							</div>
						  </a>
						</th>
						<?php
					}
				}
			}
			
			// Bund
			if($_SESSION['Tabelle']['UERE_BND'])
			{	
					?>
					<!-- Trenner -->
					<th style="background:#CCC; width:1px; padding:0px;" >&nbsp;</th>
					<!-- Bundesland -->
					<th style=" <?php if($_SESSION['Tabellen_Sortierung'] == 'WERT_BND_DIFF'){ echo 'background-color:#e0ebc5;'; } ?> ">
					   <a href="?sort=WERT_BND_DIFF">
						   <div style="padding-top:2px; padding-bottom:2px; padding-left:10px; padding-right:10px; margin-right:3px;<?php 
						  if($_SESSION['Tabellen_Sortierung'] == 'WERT_BND_DIFF')
						  {
								if($_SESSION['Tabellen_Sortierung_asc_desc'] == "ASC")
								{ ?>  
											background-image:url(../gfx/sort_asc.png); 
											background-repeat:no-repeat; 
											background-position: right 4px;
											<?php 
								} 
								if($_SESSION['Tabellen_Sortierung_asc_desc'] == "DESC") 
								{ ?> 
											background-image:url(../gfx/sort_desc.png); 
											background-repeat:no-repeat; 
											background-position: right 4px;
											<?php 
								} 
						  }
							?>">
							Differenz <br />
							zur Bundesrepublik
							<?php 
														
							// Max für Box dieser Spaltenwerte ermitteln
							$SQL_Max = "SELECT MAX(WERT_BND_DIFF) AS MAX, MIN(WERT_BND_DIFF) AS MIN FROM t_temp_tabellentool WHERE FEHLERCODE = '0'"; 
							$Ergebnis_Max = mysqli_query($Verbindung,$SQL_Max); 
							$Max_diff_bnd = @mysqli_result($Ergebnis_Max,0,'MAX');
							$Min = abs(@mysqli_result($Ergebnis_Max,0,'MIN'));
							if($Min > $Max_diff_bnd) $Max_diff_bnd = $Min;
							?>
						</div>
					  </a>
					</th>
					<th>
						<div style="padding-top:2px; padding-bottom:2px; padding-left:10px; padding-right:10px; margin-right:3px; color:#444444;">Wert für<br />Bundesrepublik
						</div>
					</th>
					 <?php
					if($_SESSION['Tabelle']['AKTUALITAET'])
					{
						?>
						<th>
							<div style="padding-top:2px; padding-bottom:2px; padding-left:10px; padding-right:10px; margin-right:3px; color:#444444;">
								Mittlere<br />
								Grundakt.<br />
								Bundesrepublik
							</div>
						</th>
						<?php
					}
			}
			
			
			// ----------------------- Zusätzliche Zeitschnitte ausgeben -----------------------------
			
			if(is_array($Zeitschnitte_vorh))
			{
				foreach($Zeitschnitte_vorh as $ZZeitschnitt)
				{
					if($ZZeitschnitt != $_SESSION['Dokument']['Jahr_Anzeige'] and $_SESSION['Tabelle']['Zeitschnitt_Zusatz'][$ZZeitschnitt] == 1) // Aktuellen Zeitschnitt nicht berücksichtigen!
					{
						?>
						<!-- Trenner -->
						<th style="background:#CCC; width:3px;" >&nbsp;</th>
						
						<!-- absolute Veränderung zum gewählten Basiszeitschnitt -->
						<?php 
						if($_SESSION['Tabelle']['VERGLEICH'] == '1')
						{
							?>
							<th style=" <?php if($_SESSION['Tabellen_Sortierung'] == 'WERT_DIFF_'.$ZZeitschnitt){ echo 'background-color:#e0ebc5;'; } ?> ">
					   <a href="?sort=<?php echo 'WERT_DIFF_'.$ZZeitschnitt ?>">
						   <div style="padding-top:2px; padding-bottom:2px; padding-left:10px; padding-right:10px; margin-right:3px;<?php 
						  if($_SESSION['Tabellen_Sortierung'] == 'WERT_DIFF_'.$ZZeitschnitt)
						  {
								if($_SESSION['Tabellen_Sortierung_asc_desc'] == "ASC")
								{ ?>  
											background-image:url(../gfx/sort_asc.png); 
											background-repeat:no-repeat; 
											background-position: right 4px;
											<?php 
								} 
								if($_SESSION['Tabellen_Sortierung_asc_desc'] == "DESC") 
								{ ?> 
											background-image:url(../gfx/sort_desc.png); 
											background-repeat:no-repeat; 
											background-position: right 4px;
											<?php 
								} 
						  }
							?>">
							  Differenz <br />
							  <?php echo $ZZeitschnitt." zu ".$_SESSION['Dokument']['Jahr_Anzeige']; ?> 
									<?php 
																
									// Max für Box dieser Spaltenwerte ermitteln
									$SQL_Max = "SELECT MAX(WERT_DIFF_".$ZZeitschnitt.") AS MAX, MIN(WERT_DIFF_".$ZZeitschnitt.") AS MIN FROM t_temp_tabellentool WHERE FEHLERCODE = '0'"; 
									$Ergebnis_Max = mysqli_query($Verbindung,$SQL_Max); 
									$Max_abs[$ZZeitschnitt] = @mysqli_result($Ergebnis_Max,0,'MAX');
									$Min = abs(@mysqli_result($Ergebnis_Max,0,'MIN'));
									if($Min > $Max_abs[$ZZeitschnitt]) $Max_abs[$ZZeitschnitt] = $Min;
									?>
								</div>
							  </a>
							</th>
							<?php 
						}
						?>
						<!-- Wert -->
						<th style=" <?php if($_SESSION['Tabellen_Sortierung'] == 'WERT_'.$ZZeitschnitt){ echo 'background-color:#e0ebc5;'; } ?> ">
						 <a href="?sort=<?php echo 'WERT_'.$ZZeitschnitt ?>">
						   <div style="padding-top:2px; padding-bottom:2px; padding-left:10px; padding-right:10px; margin-right:3px;<?php 
						  if($_SESSION['Tabellen_Sortierung'] == 'WERT_'.$ZZeitschnitt)
						  {
								if($_SESSION['Tabellen_Sortierung_asc_desc'] == "ASC")
								{ ?>  
											background-image:url(../gfx/sort_asc.png); 
											background-repeat:no-repeat; 
											background-position: right 4px;
											<?php 
								} 
								if($_SESSION['Tabellen_Sortierung_asc_desc'] == "DESC") 
								{ ?> 
											background-image:url(../gfx/sort_desc.png); 
											background-repeat:no-repeat; 
											background-position: right 4px;
											<?php 
								} 
						  }
							?>">
								<?php
								echo 'Wert ('.$ZZeitschnitt.')'; 
								?>
							</div>
						  </a>
						</th>
						<!-- relative Veränderung zum gewählten Basiszeitschnitt -->
						<?php /* ?>
						<th>
							<div style="padding-top:2px; padding-bottom:2px; padding-left:10px; padding-right:5px; margin-right:3px;">
								Änderung (relativ)
								<?php 
															
								// Max für Box dieser Spaltenwerte ermitteln
								$SQL_Max = "SELECT MAX(WERT_RELATIV_".$ZZeitschnitt.") AS MAX, MIN(WERT_RELATIV_".$ZZeitschnitt.") AS MIN FROM t_temp_tabellentool WHERE FEHLERCODE = '0'"; 
								$Ergebnis_Max = mysqli_query($Verbindung,$SQL_Max); 
								$Max_rel[$ZZeitschnitt] = @mysqli_result($Ergebnis_Max,0,'MAX');
								$Min = abs(@mysqli_result($Ergebnis_Max,0,'MIN'));
								if($Min > $Max_rel[$ZZeitschnitt]) $Max_rel[$ZZeitschnitt] = $Min;
								?>
							</div>
						</th>
						<?php  */ ?>
						<!-- Grundaktualitaet -->
						<?php
						if($_SESSION['Tabelle']['AKTUALITAET'])
						{
							?>
							<th style=" <?php if($_SESSION['Tabellen_Sortierung'] == 'AKT_'.$ZZeitschnitt){ echo 'background-color:#e0ebc5;'; } ?> ">
						 <a href="?sort=<?php echo 'AKT_'.$ZZeitschnitt ?>">
						   <div style="padding-top:2px; padding-bottom:2px; padding-left:10px; padding-right:10px; margin-right:3px;<?php 
						  if($_SESSION['Tabellen_Sortierung'] == 'AKT_'.$ZZeitschnitt)
						  {
								if($_SESSION['Tabellen_Sortierung_asc_desc'] == "ASC")
								{ ?>  
											background-image:url(../gfx/sort_asc.png); 
											background-repeat:no-repeat; 
											background-position: right 4px;
											<?php 
								} 
								if($_SESSION['Tabellen_Sortierung_asc_desc'] == "DESC") 
								{ ?> 
											background-image:url(../gfx/sort_desc.png); 
											background-repeat:no-repeat; 
											background-position: right 4px;
											<?php 
								} 
						  }
							?>">
									<?php 
									echo 'Mittlere<br />Grundaktualit&auml;t<br />('.$ZZeitschnitt.')'; 
									?>
								</div>
							  </a>
							</th>
							<?php 
						}
						?>
				
						<!-- Uebergeordnete RE -->
						<?php 
						if($_SESSION['Tabelle']['UERE_KRS'])
						{
							?>
								<!-- Kreis -->
								<?php 
								// sinnvolle Anzeige der Zeile nur bei Gemeinden
								if($_SESSION['Dokument']['Raumgliederung'] == "gem" )
								{
									?>
									<th style=" <?php if($_SESSION['Tabellen_Sortierung'] == 'WERT_KRS_'.$ZZeitschnitt){ echo 'background-color:#e0ebc5;'; } ?> ">
										 <a href="?sort=<?php echo 'WERT_KRS_'.$ZZeitschnitt ?>">
										   <div style="padding-top:2px; padding-bottom:2px; padding-left:10px; padding-right:10px; margin-right:3px;<?php 
										  if($_SESSION['Tabellen_Sortierung'] == 'WERT_KRS_'.$ZZeitschnitt)
										  {
												if($_SESSION['Tabellen_Sortierung_asc_desc'] == "ASC")
												{ ?>  
															background-image:url(../gfx/sort_asc.png); 
															background-repeat:no-repeat; 
															background-position: right 4px;
															<?php 
												} 
												if($_SESSION['Tabellen_Sortierung_asc_desc'] == "DESC") 
												{ ?> 
															background-image:url(../gfx/sort_desc.png); 
															background-repeat:no-repeat; 
															background-position: right 4px;
															<?php 
												} 
										  }
											?>">
											<?php        
											echo 'Kreis ('.$ZZeitschnitt.')';
											?>
										</div>
									   </a>
									</th>
									<?php
								}
						}
						if($_SESSION['Tabelle']['UERE_BLD'])
						{
							// sinnvolle Anzeige der kompletten Spalte nur bei Gemeinden oder Kreisen
							if($_SESSION['Dokument']['Raumgliederung'] == "gem" 
							or $_SESSION['Dokument']['Raumgliederung'] == "krs" 
							or $_SESSION['Dokument']['Raumgliederung'] == "kfs" 
							or $_SESSION['Dokument']['Raumgliederung'] == "lks")
							{
								?>
								<!-- Bundesland -->
								<th style=" <?php if($_SESSION['Tabellen_Sortierung'] == 'WERT_BLD_'.$ZZeitschnitt){ echo 'background-color:#e0ebc5;'; } ?> ">
										 <a href="?sort=<?php echo 'WERT_BLD_'.$ZZeitschnitt ?>">
										   <div style="padding-top:2px; padding-bottom:2px; padding-left:10px; padding-right:10px; margin-right:3px;<?php 
										  if($_SESSION['Tabellen_Sortierung'] == 'WERT_BLD_'.$ZZeitschnitt)
										  {
												if($_SESSION['Tabellen_Sortierung_asc_desc'] == "ASC")
												{ ?>  
															background-image:url(../gfx/sort_asc.png); 
															background-repeat:no-repeat; 
															background-position: right 4px;
															<?php 
												} 
												if($_SESSION['Tabellen_Sortierung_asc_desc'] == "DESC") 
												{ ?> 
															background-image:url(../gfx/sort_desc.png); 
															background-repeat:no-repeat; 
															background-position: right 4px;
															<?php 
												} 
										  }
											?>">
										<?php 
										echo 'Bundesland ('.$ZZeitschnitt.')';
										?>
									</div>
								   </a>
								</th>
							<?php
							}
						}
						if($_SESSION['Tabelle']['UERE_BND'])
						{
							
							?>
								<!-- Bundesland -->
								<th style=" color:#444444;">
									 <div style="padding-top:2px; padding-bottom:2px; padding-left:10px; padding-right:10px; margin-right:3px;">
										<?php 
										echo 'Bundesrepublik ('.$ZZeitschnitt.')';
										?>
									</div>
								</th>
							<?php
						}
					}
				}
			}
	
			?>
		</tr>
	
	<?php 
	
	
	
	
	
	
	
	
	
	// --------------- Daten-Zeilen ------------------------------------------
	
	// Erstellen der Sortieranweisungen für SQL
	if($_SESSION['Tabellen_Sortierung'])
	{
		if($_SESSION['Tabellen_Sortierung']=="NAME") $SQL_COLLATE = "COLLATE utf8_general_ci"; // anwenden, wenn nach Name sortiert wird (DIN 5007)
		$SQL_Order = ", ".$_SESSION['Tabellen_Sortierung']." ".$SQL_COLLATE." ".$_SESSION['Tabellen_Sortierung_asc_desc'].";";
	}
	
	$SQL_ANZEIGE = "SELECT * FROM t_temp_tabellentool ORDER BY FEHLERCODE ASC ".$SQL_Order;
	// Check, ob Abfrage erfolgreich war, ansnsten Ausführung mit Standard-Sortierung und setzen dieser in SESSION-Array (da evtl. fehlerhaft durch Ausblendung eines Zusatzzeitschnittes)
	if(!$Ergebnis_ANZEIGE = mysqli_query($Verbindung,$SQL_ANZEIGE))
	{
		$_SESSION['Tabellen_Sortierung'] = "NAME"; 
		$_SESSION['Tabellen_Sortierung_asc_desc'] = "ASC";
		$SQL_Order = ", ".$_SESSION['Tabellen_Sortierung']." ".$SQL_COLLATE." ".$_SESSION['Tabellen_Sortierung_asc_desc'].";";
		$SQL_ANZEIGE = "SELECT * FROM t_temp_tabellentool ORDER BY FEHLERCODE ASC ".$SQL_Order;
		$Ergebnis_ANZEIGE = mysqli_query($Verbindung,$SQL_ANZEIGE);
	}
	
	
	$i_anz = 0;
	while($ags = @mysqli_result($Ergebnis_ANZEIGE,$i_anz,'AGS'))
	{
		
		// Test auf Fehlercode
		if($FC = @mysqli_result($Ergebnis_ANZEIGE,$i_anz,'FEHLERCODE'))
		{
			$SQL_FC = "SELECT * FROM m_fehlercodes WHERE FEHLERCODE = '".$FC."'";
			$Ergebnis_FC = mysqli_query($Verbindung,$SQL_FC);
					
			$Fehlername = utf8_encode(mysqli_result($Ergebnis_FC,0,'FEHLER_NAME'));
			$Fehlerbschreibung = utf8_encode(mysqli_result($Ergebnis_FC,0,'FEHLER_BESCHREIBUNG'));
			$Fehlerfarbcode = mysqli_result($Ergebnis_FC,0,'FEHLER_FARBCODE');
		}
		else
		{
			$Fehlername = '';
			$Fehlerbschreibung = '';
			$Fehlerfarbcode = '';
		}
		
		?>
		<tr style="background:<?php if($i_anz % 5 == 0) { echo '#EEEEEE'; }else{ echo '#FFFFFF'; }?>;">
			<!-- Link zum Histogramm -->
			<td>
				<?php 
				if(!$FC) // Check auf Fehlercode
				{
					?>
					<form action="objektinformationen.php" method="post">
					  <input type="image" src="../gfx/icons/klein/document_search.png" alt="Objektinfo"> <?php /* echo $ags; */ ?> 
					  <input name="ags" type="hidden" value="<?php echo $ags; ?>" />
					  <input name="name" type="hidden" value="<?php echo @mysqli_result($Ergebnis_ANZEIGE,$i_anz,'NAME'); ?>" />
					  <input name="wert" type="hidden" value="<?php echo  round(@mysqli_result($Ergebnis_ANZEIGE,$i_anz,'WERT'),$_SESSION['Dokument']['Fuellung']['Rundung']); ?>" />
					  <input name="W_Min" type="hidden" value="<?php echo $Min_Ausgabe; ?>" />
					  <input name="Standardabweichung" type="hidden" value="<?php echo round($Standardabweichung,$_SESSION['Dokument']['Fuellung']['Rundung']); ?>" />
					  <input name="Median_1_Wert" type="hidden" value="<?php echo  round($Median_Wert,$_SESSION['Dokument']['Fuellung']['Rundung']); ?>" />
					  <input name="Median_2_Wert" type="hidden" value="<?php  ?>" />
					  <input name="Median_1_Name" type="hidden" value="<?php echo  round($Median_Name,$_SESSION['Dokument']['Fuellung']['Rundung']); ?>" />
					  <input name="Median_2_Name" type="hidden" value="<?php  ?>" />
                      <input name="n" type="hidden" value="<?php echo $n_Stichproben; ?>" />
					  <input name="AMittel" type="hidden" value="<?php echo  round($Ar_Mittel,$_SESSION['Dokument']['Fuellung']['Rundung']); ?>" />  
					</form>
				<?php
				}
			?>
			</td>
			<!-- AGS -->
			<td>
				<?php echo @mysqli_result($Ergebnis_ANZEIGE,$i_anz,'AGS');  ?>
			</td>
			<!-- Name -->
			<td>
				<?php echo @mysqli_result($Ergebnis_ANZEIGE,$i_anz,'NAME');  ?>
			</td>
			<!-- Wert -->
			<td style=" text-align:right;">
				<?php 	if(!$FC) 
						{  
							if($_SESSION['Dokument']['ViewBerechtigung'] == "0")
							{ 
								echo number_format($x = (@mysqli_result($Ergebnis_ANZEIGE,$i_anz,'WERT') - 0),4, ',', '.'); 
							}
							else
							{ 
								echo number_format($x = (@mysqli_result($Ergebnis_ANZEIGE,$i_anz,'WERT') - 0),$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.'); 
							}
						}
						else
						{ 
							?><span style="color:#<?php echo $Fehlerfarbcode; ?>;"><?php echo $Fehlername; ?></span><?php 
						} 
						?>
			</td>
			<!-- Grundaktualitaet -->
			<?php
			if($_SESSION['Tabelle']['AKTUALITAET'])
			{
				?>
				<td style="color:#777; text-align:center;">
					<?php 
						if(!$FC) { echo @mysqli_result($Ergebnis_ANZEIGE,$i_anz,'AKT'); } 
					?>
				</td>
				<?php 
			}
			?>
	
			<!-- Uebergeordnete RE -->
			<?php  
			// Kreise
			if($_SESSION['Tabelle']['UERE_KRS'])
			{
	
				
				// sinnvolle Anzeige der Zeile nur bei Gemeinden
				if($_SESSION['Dokument']['Raumgliederung'] == "gem" )
				{
					?>
                    <!-- Trenner -->
                     <td style="background:#CCC; width:3px; padding:0px;" >&nbsp;</td>
                    <!-- Kreis -->
					<td>
					<?php 
						// Breite der Box (Festlegung)
						$Boxbreite = "100";
						?>
						<div style="width:<?php echo $Boxbreite; ?>px; height:15px; padding:0px; position:relative; border:solid 1px #999;">
						  <?php 
							if(!$FC) 
							{ 
								// Breite der Box in bezug zum Max-Wertn (auf 50% der Boxbreite wegen +-
								/* if(@mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_ZZS='WERT_DIFF_'.$ZZeitschnitt) > 0 or @mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_ZZS='WERT_DIFF_'.$ZZeitschnitt) < 0)
								{ */
									$Box_width = abs(50 * (@mysqli_result($Ergebnis_ANZEIGE,$i_anz,'WERT_KRS_DIFF')/$Max_diff_krs));  
								/* }
								else
								{ 	$Box_width=0; } */
								
								// Positionierung der Box von der Mitte aus
								if(@mysqli_result($Ergebnis_ANZEIGE,$i_anz,'WERT_KRS_DIFF') < 0) 
								{
									// Dynamisch den Platz vor der Box berechnen und als margin belegen
									$Box_Margin_left = 50 - $Box_width; 
									if(!$Box_width) $Box_Margin_left = "0"; // Falls Variable leer.... vermeidet XHTML Fehler
									$Box_BackColor = "#8888CC";
									$Textpos_align = "left";
									$Border_seite = "right";
								}
								else
								{
									$Box_Margin_left = 50; // bei >0 ist 50% immer korrekt
									$Box_BackColor = "#CC8888";
									$Textpos_align = "right";
									$Border_seite = "left";
								}
								?>
								<div style="height:100%;width:<?php echo $Box_width; ?>%; margin-left:<?php echo round($Box_Margin_left,0); ?>%; background-color:<?php echo $Box_BackColor; ?>; 
													position:absolute; z-index:5; border-<?php echo $Border_seite; ?>:#666 solid 1px;">
										
								</div>
								<div style="width:<?php echo $BB = ($Boxbreite-10); ?>px; padding-right:5px; padding-left:5px; position:absolute; z-index:10; text-align:<?php echo $Textpos_align; ?>;">
										<?php 
									
									if($_SESSION['Dokument']['ViewBerechtigung'] == "0")
									{ 
										echo number_format(@mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_ZZS='WERT_KRS_DIFF'),$rndg_p=($_SESSION['Dokument']['Fuellung']['Rundung']+1), ',', '.');
									}
									else
									{ 
										echo number_format(@mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_ZZS='WERT_KRS_DIFF'),$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.');
									}
									?>
								</div>
								<?php 
							}
							?>
						</div>
					  </td>
					  <td>
						<?php 
						if(!$FC) 
						{ 
							echo number_format(@mysqli_result($Ergebnis_ANZEIGE,$i_anz,'WERT_KRS'),$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.')." (".@mysqli_result($Ergebnis_ANZEIGE,$i_anz,'NAME_KRS').")"; 
						}
						?>
						</td>
						<!-- Grundaktualitaet KRS -->
						<?php
						if($_SESSION['Tabelle']['AKTUALITAET'])
						{
							?>
							<td style="color:#777; text-align:center;">
								<?php 
									if(!$FC) { echo @mysqli_result($Ergebnis_ANZEIGE,$i_anz,'AKT_KRS'); } 
								?>
							</td>
							<?php 
						}
					}
				}
				
			// Bundesländer
			if($_SESSION['Tabelle']['UERE_BLD'])
			{
				// sinnvolle Anzeige der kompletten Spalte nur bei Gemeinden oder Kreisen
				if($_SESSION['Dokument']['Raumgliederung'] == "gem" 
				or $_SESSION['Dokument']['Raumgliederung'] == "krs" 
				or $_SESSION['Dokument']['Raumgliederung'] == "kfs" 
				or $_SESSION['Dokument']['Raumgliederung'] == "lks")
				{
					?>
					<!-- Trenner -->
					<td style="background:#CCC; width:3px; padding:0px;" >&nbsp;</td>
					<!-- Bundesland -->
					<td>
						<?php 
						// Breite der Box (Festlegung)
						$Boxbreite = "100";
						?>
						<div style="width:<?php echo $Boxbreite; ?>px; height:15px; padding:0px; position:relative; border:solid 1px #999;">
						  <?php 
							if(!$FC) 
							{ 
								// Breite der Box in bezug zum Max-Wertn (auf 50% der Boxbreite wegen +-
								/* if(@mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_ZZS='WERT_DIFF_'.$ZZeitschnitt) > 0 or @mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_ZZS='WERT_DIFF_'.$ZZeitschnitt) < 0)
								{ */
									$Box_width = abs(50 * (@mysqli_result($Ergebnis_ANZEIGE,$i_anz,'WERT_BLD_DIFF')/$Max_diff_bld));  
								/* }
								else
								{ 	$Box_width=0; } */
								
								// Positionierung der Box von der Mitte aus
								if(@mysqli_result($Ergebnis_ANZEIGE,$i_anz,'WERT_BLD_DIFF') < 0) 
								{
									// Dynamisch den Platz vor der Box berechnen und als margin belegen
									$Box_Margin_left = 50 - $Box_width; 
									if(!$Box_width) $Box_Margin_left = "0"; // Falls Variable leer.... vermeidet XHTML Fehler
									$Box_BackColor = "#8888CC";
									$Textpos_align = "left";
									$Border_seite = "right";
								}
								else
								{
									$Box_Margin_left = 50; // bei >0 ist 50% immer korrekt
									$Box_BackColor = "#CC8888";
									$Textpos_align = "right";
									$Border_seite = "left";
								}
								?>
								<div style="height:100%;width:<?php echo $Box_width; ?>%; margin-left:<?php echo round($Box_Margin_left,0); ?>%; background-color:<?php echo $Box_BackColor; ?>; 
													position:absolute; z-index:5; border-<?php echo $Border_seite; ?>:#666 solid 1px;">
										
								</div>
								<div style="width:<?php echo $BB = ($Boxbreite-10); ?>px; padding-right:5px; padding-left:5px; position:absolute; z-index:10; text-align:<?php echo $Textpos_align; ?>;">
										<?php 
									
									if($_SESSION['Dokument']['ViewBerechtigung'] == "0")
									{ 
										echo number_format(@mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_ZZS='WERT_BLD_DIFF'),$rndg_p=($_SESSION['Dokument']['Fuellung']['Rundung']+1), ',', '.');
									}
									else
									{ 
										echo number_format(@mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_ZZS='WERT_BLD_DIFF'),$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.');
									}
									?>
								</div>
								<?php 
							}
							?>
						</div>
					</td>
					<td>
						<?php 
						if(!$FC) 
						{ 
							echo number_format(@mysqli_result($Ergebnis_ANZEIGE,$i_anz,'WERT_BLD'),$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.')." (".@mysqli_result($Ergebnis_ANZEIGE,$i_anz,'NAME_BLD').")"; 
						}
						?>
					</td>
					<!-- Grundaktualitaet BLD -->
					<?php
					if($_SESSION['Tabelle']['AKTUALITAET'])
					{
						?>
						<td style="color:#777; text-align:center;">
							<?php 
								if(!$FC) { echo @mysqli_result($Ergebnis_ANZEIGE,$i_anz,'AKT_BLD'); } 
							?>
						</td>
						<?php 
					}
				}
			}
			
			// Bund
			if($_SESSION['Tabelle']['UERE_BND'])
			{
					?>
					<!-- Trenner -->
					<td style="background:#CCC; width:3px; padding:0px;" >&nbsp;</td>
					<!-- Bund -->
					<td>
						<?php 
						// Breite der Box (Festlegung)
						$Boxbreite = "100";
						?>
						<div style="width:<?php echo $Boxbreite; ?>px; height:15px; padding:0px; position:relative; border:solid 1px #999;">
						  <?php 
							if(!$FC) 
							{ 
								// Breite der Box in bezug zum Max-Wertn (auf 50% der Boxbreite wegen +-
								/* if(@mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_ZZS='WERT_DIFF_'.$ZZeitschnitt) > 0 or @mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_ZZS='WERT_DIFF_'.$ZZeitschnitt) < 0)
								{ */
									$Box_width = abs(50 * (@mysqli_result($Ergebnis_ANZEIGE,$i_anz,'WERT_BND_DIFF')/$Max_diff_bnd));  
								/* }
								else
								{ 	$Box_width=0; } */
								
								// Positionierung der Box von der Mitte aus
								if(@mysqli_result($Ergebnis_ANZEIGE,$i_anz,'WERT_BND_DIFF') < 0) 
								{
									// Dynamisch den Platz vor der Box berechnen und als margin belegen
									$Box_Margin_left = 50 - $Box_width; 
									if(!$Box_width) $Box_Margin_left = "0"; // Falls Variable leer.... vermeidet XHTML Fehler
									$Box_BackColor = "#8888CC";
									$Textpos_align = "left";
									$Border_seite = "right";
								}
								else
								{
									$Box_Margin_left = 50; // bei >0 ist 50% immer korrekt
									$Box_BackColor = "#CC8888";
									$Textpos_align = "right";
									$Border_seite = "left";
								}
								?>
								<div style="height:100%;width:<?php echo $Box_width; ?>%; margin-left:<?php echo round($Box_Margin_left,0); ?>%; background-color:<?php echo $Box_BackColor; ?>; 
													position:absolute; z-index:5; border-<?php echo $Border_seite; ?>:#666 solid 1px;">
										
								</div>
								<div style="width:<?php echo $BB = ($Boxbreite-10); ?>px; padding-right:5px; padding-left:5px; position:absolute; z-index:10; text-align:<?php echo $Textpos_align; ?>;">
										<?php 
									
									if($_SESSION['Dokument']['ViewBerechtigung'] == "0")
									{ 
										echo number_format(@mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_ZZS='WERT_BND_DIFF'),$rndg_p=($_SESSION['Dokument']['Fuellung']['Rundung']+1), ',', '.');
									}
									else
									{ 
										echo number_format(@mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_ZZS='WERT_BND_DIFF'),$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.');
									}
									?>
								</div>
								<?php 
							}
							?>
						</div>
					</td>
					<td>
						<?php 
						if(!$FC) 
						{ 
							echo number_format(@mysqli_result($Ergebnis_ANZEIGE,$i_anz,'WERT_BND'),$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.'); 
						}
						?>
					</td>
					<!-- Grundaktualitaet BND -->
					<?php
					if($_SESSION['Tabelle']['AKTUALITAET'])
					{
						?>
						<td style="color:#777; text-align:center;">
							<?php 
								if(!$FC) { echo @mysqli_result($Ergebnis_ANZEIGE,$i_anz,'AKT_BND'); } 
							?>
						</td>
						<?php 
					}
			}
			
			// ----------------------- Zusätzliche Zeitschnitte ausgeben -----------------------------
			
			if(is_array($Zeitschnitte_vorh))
			{
				foreach($Zeitschnitte_vorh as $ZZeitschnitt)
				{
					if($ZZeitschnitt != $_SESSION['Dokument']['Jahr_Anzeige'] and $_SESSION['Tabelle']['Zeitschnitt_Zusatz'][$ZZeitschnitt] == 1) // Aktuellen Zeitschnitt nicht berücksichtigen!
					{
						
						// Test auf Fehlercode im Zusatzzeitschnitt
						if($FC = @mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_FC = 'FEHLERCODE_'.$ZZeitschnitt))
						{
							$SQL_FC = "SELECT * FROM m_fehlercodes WHERE FEHLERCODE = '".$FC."'";
							$Ergebnis_FC = mysqli_query($Verbindung,$SQL_FC);
									
							$Fehlername = utf8_encode(mysqli_result($Ergebnis_FC,0,'FEHLER_NAME'));
							$Fehlerbschreibung = utf8_encode(mysqli_result($Ergebnis_FC,0,'FEHLER_BESCHREIBUNG'));
							$Fehlerfarbcode = mysqli_result($Ergebnis_FC,0,'FEHLER_FARBCODE');
						}
						else
						{
							$Fehlername = '';
							$Fehlerbschreibung = '';
							$Fehlerfarbcode = '';
						}
						
						?>
						<!-- Trenner -->
						<td style="background:#CCC;" >&nbsp;</td>
						
						 <!-- Absolute Änderung -->
						 <?php 
						if($_SESSION['Tabelle']['VERGLEICH'] == '1')
						{
							?>
							<td>
								<?php 
								// Breite der Box (Festlegung)
								$Boxbreite = "100";
								?>
								<div style="width:<?php echo $Boxbreite; ?>px; height:15px; padding:0px; position:relative; border:solid 1px #999;">
								  <?php 
									if(!$FC) 
									{ 
										// Breite der Box in bezug zum Max-Wertn (auf 50% der Boxbreite wegen +-
										/* if(@mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_ZZS='WERT_DIFF_'.$ZZeitschnitt) > 0 or @mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_ZZS='WERT_DIFF_'.$ZZeitschnitt) < 0)
										{ */
											$Box_width = @abs(50 * (@mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_ZZS='WERT_DIFF_'.$ZZeitschnitt)/$Max_abs[$ZZeitschnitt]));  
										/* }
										else
										{ 	$Box_width=0; } */
										
										// Positionierung der Box von der Mitte aus
										if(@mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_ZZS='WERT_DIFF_'.$ZZeitschnitt) < 0) 
										{
											// Dynamisch den Platz vor der Box berechnen und als margin belegen
											$Box_Margin_left = 50 - $Box_width; 
											if(!$Box_width) $Box_Margin_left = "0"; // Falls Variable leer.... vermeidet XHTML Fehler
											$Box_BackColor = "#8888CC";
											$Textpos_align = "left";
											$Border_seite = "right";
										}
										else
										{
											$Box_Margin_left = 50; // bei >0 ist 50% immer korrekt
											$Box_BackColor = "#CC8888";
											$Textpos_align = "right";
											$Border_seite = "left";
										}
										?>
										<div style="height:100%;width:<?php echo $Box_width; ?>%; margin-left:<?php echo round($Box_Margin_left,0); ?>%; background-color:<?php echo $Box_BackColor; ?>; 
													position:absolute; z-index:5; border-<?php echo $Border_seite; ?>:#666 solid 1px;">
										
										</div>
										<div style="width:<?php echo $BB = ($Boxbreite-10); ?>px; padding-right:5px; padding-left:5px; position:absolute; z-index:10; text-align:<?php echo $Textpos_align; ?>;">
											<?php 
											
											if($_SESSION['Dokument']['ViewBerechtigung'] == "0")
											{ 
												echo number_format(@mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_ZZS='WERT_DIFF_'.$ZZeitschnitt),$rndg_p=($_SESSION['Dokument']['Fuellung']['Rundung']+1), ',', '.');
											}
											else
											{ 
												echo number_format(@mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_ZZS='WERT_DIFF_'.$ZZeitschnitt),$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.');
											}
											?>
										</div>
										<?php 
									}
		
								  ?>
								</div>
							</td>
							<?php 
						}
						?>
						<!-- Wert -->
						<td style="text-align:right;">
							
								<?php 
								if(!$FC) 
								{ 
									if($_SESSION['Dokument']['ViewBerechtigung'] == "0")
									{ 
										echo number_format($x = (@mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_ZZS='WERT_'.$ZZeitschnitt) - 0),4, ',', '.');
									}
									else
									{ 
										echo number_format($x = (@mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_ZZS='WERT_'.$ZZeitschnitt) - 0),$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.');
									}
								}
								else
								{ 
									?><span style="color:#<?php echo $Fehlerfarbcode; ?>;"><?php echo $Fehlername; ?></span><?php 
								} 
								?>
						</td>
						<!-- Relative Änderung -->
						<?php /* ?>
						<td>
							<?php 
							// Breite der Box (Festlegung)
							$Boxbreite = "80";
							?>
							<div style="width:<?php echo $Boxbreite; ?>px; height:15px; padding:0px;">
							  <?php 
								if(!$FC) 
								{ 
									// Breite der Box in bezug zum Max-Wertn (auf 50% der Boxbreite wegen +-
									// if(@mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_ZZS='WERT_DIFF_'.$ZZeitschnitt) > 0 or @mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_ZZS='WERT_DIFF_'.$ZZeitschnitt) < 0)
									//{ 
										$Box_width = abs(50 * (@mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_ZZS='WERT_RELATIV_'.$ZZeitschnitt)/$Max_rel[$ZZeitschnitt]));  
									// }
									//else
									//{ 	$Box_width=0; } 
									
									// Positionierung der Box von der Mitte aus
									if(@mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_ZZS='WERT_RELATIV_'.$ZZeitschnitt) < 0) 
									{
										$Box_Margin_left = 50 - $Box_width; // Dynamisch den Platz vor der Box berechnen und als margin belegen
										$Box_BackColor = "#8888CC";
									}
									else
									{
										$Box_Margin_left = 50; // bei >0 ist 50% immer korrekt
										$Box_BackColor = "#CC8888";
									}
									?>
									<div style="width:<?php echo $Box_width; ?>%; margin-left:<?php echo $Box_Margin_left; ?>%; background-color:<?php echo $Box_BackColor; ?>;">
										<?php 
										
										if($_SESSION['Dokument']['ViewBerechtigung'] == "0")
										{ 
											echo @mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_ZZS='WERT_RELATIV_'.$ZZeitschnitt); 
										}
										else
										{ 
											echo number_format(@mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_ZZS='WERT_RELATIV_'.$ZZeitschnitt),$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.');
										}
										?>
									</div>
									<?php 
								}
	
							  ?>
							</div>
						</td>
					   <?php */ ?>
					   
						<!-- Grundaktualitaet -->
						<?php
						if($_SESSION['Tabelle']['AKTUALITAET'])
						{
							?>
							<td  style="color:#777; text-align:center;">
								<?php 
									if(!$FC) echo @mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_ZZS='AKT_'.$ZZeitschnitt); 
								?>
							</td>
							<?php 
						}
						?>
				
						<!-- Uebergeordnete RE -->
						<?php 
						if($_SESSION['Tabelle']['UERE_KRS'])
						{	
								?>
								<!-- Kreis -->
								<?php 
								// sinnvolle Anzeige der Zeile nur bei Gemeinden
								if($_SESSION['Dokument']['Raumgliederung'] == "gem" )
								{
									?>
									<td>
									<?php        
									if(!$FC) echo number_format(@mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_ZZS='WERT_KRS_'.$ZZeitschnitt),$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.')
											." (".@mysqli_result($Ergebnis_ANZEIGE,$i_anz,'NAME_KRS').")"; 
									?>
									</td>
									<?php
								}
							}
						if($_SESSION['Tabelle']['UERE_BLD'])
						{
							// sinnvolle Anzeige der kompletten Spalte nur bei Gemeinden oder Kreisen
							if($_SESSION['Dokument']['Raumgliederung'] == "gem" 
							or $_SESSION['Dokument']['Raumgliederung'] == "krs" 
							or $_SESSION['Dokument']['Raumgliederung'] == "kfs" 
							or $_SESSION['Dokument']['Raumgliederung'] == "lks")
							{
								?>
								<!-- Bundesland -->
								<td>
									<?php 
									if(!$FC) echo number_format(@mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_ZZS='WERT_BLD_'.$ZZeitschnitt),$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.')
											." (".@mysqli_result($Ergebnis_ANZEIGE,$i_anz,'NAME_BLD').")"; 
									?>
								</td>
								<?php 
							}
						}
						if($_SESSION['Tabelle']['UERE_BND'])
						{
								?>
								<!-- Bundesland -->
								<td>
									<?php 
									if(!$FC) echo number_format(@mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_ZZS='WERT_BND_'.$ZZeitschnitt),$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.'); 
									?>
								</td>
								<?php 						
						}
					}
				}
			}
			
			
			
			
			
			?>
			
		</tr>
		<?php 
		
		$i_anz++;
	}
	
	
	?>
	</table>
	
    
    
    <br />
	<div class="nicht_im_print" style="padding-top:5px">
		   Bestellung der Daten im CSV-Format per 
			<!-- #BDDDFD --><a 
			style="background-color:#DDDDDD; padding-left:12px; padding-right:12px; padding-top:0px; padding-bottom:0px;" class="button_standard_abschicken_a" 
			href="mailto:monitor@ioer.de?subject=Monitor_Tabelle&body=Bestellung der folgenden Tabelle des Monitors der Siedlungs- und Freiraumentwicklung:%0A%0AIndikator: <?php 
			echo utf8_encode($_SESSION['Dokument']['Fuellung']['Indikator']."%0AInhalt: ".$_SESSION['Datenbestand_Ausgabe']
			."%0ARaumgliederung: ".$_SESSION['Dokument']['Raumgliederung_Ausgabe']
			."%0A%0AVerwendungszweck: %0AZus%E4tzliche Bemerkungen: %0A%0AName: %0AEinrichtung: %0AAdresse: %0ATelefon: "); ?>">Email</a>
			</div>
	<br />
	<?php 
	// ENDE der Datentabelle
}

?>
</body>
</html>