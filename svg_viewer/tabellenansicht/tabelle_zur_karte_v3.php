<?php 
header( 'content-type: text/html; charset=utf-8' );
session_start(); // Sitzung starten/ wieder aufnehmen

// Erstaufruf für div. Voreinstellungen checken
if(strstr($_SERVER['HTTP_REFERER'],'svg_html.php')) 
{ 
	$Erstaufruf = '1'; 
}


// Testvariable für Laufzeit
$Laufzeit = 'Start: '.date('H:i:s');
//aktuelles Jahr für Einwohnerzahlen
$cur_year = date("Y");
$last_year = $cur_year - 1;

// Memory-Limit erweitern ->nicht nötig da Serverseitig 512M zur Verfügung
//ini_set('memory_limit', '512M');
//ini_set('max_execution_time', '500');

// Schwelle für Grundaktualitätsdifferenz bei x/Jahr
$Akt_Schwelle = 1;

include("../includes_classes/verbindung_mysqli.php");
include("../includes_classes/implode_explode.php");



//Prüfen der IP um IÖR Nutzer nicht mitzuzählen bei Downloads
if (! isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
	$client_ip = $_SERVER['REMOTE_ADDR'];
}
else {
	$client_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
}


if (strpos($client_ip, '192.9.200.') !== false) {
    $user_kennung='ioer';
}
else {$user_kennung = 'extern';}




//Zählen von Downloads (im Moment bei XLS Button, zugehöriges js in head)
$counterFile = 'counter.txt' ;

// jQuery ajax request is sent here
if ( isset($_GET['increase']) )  //
{
	
	    if ( ( $counter = @file_get_contents($counterFile) ) === false ) die('Error : file counter does not exist') ;
	           if ( $user_kennung != 'ioer'){ file_put_contents($counterFile,++$counter) ;}
	            echo $counter ;
	            return false ; 
 }
         if ( ! $counter = @file_get_contents($counterFile) )
        {
            if ( ! $myfile = fopen($counterFile,'w') )
                die('Unable to create counter file !!') ;
            chmod($counterFile,0644);
            file_put_contents($counterFile,0) ;
        }


///Berechtigungen für Trendfortschreibung ermitteln und zusammenfassen

//alle leeren damit jeder veränderte Indikator/Zeitschnitt/Raumgliederung neu geprüft wird
$_SESSION['Tabelle']['Trend_Indikator'] = '';
$_SESSION['Tabelle']['Trend_Raumgliederung'] = '';
$_SESSION['Tabelle']['Trend_Jahr'] = '';
$_SESSION['Tabelle']['Trend_Berechtigung'] = '';


//1) nur für spezielle Indikatoren

if($_SESSION['Dokument']['Fuellung']['Indikator'] == 'S11RG'
or $_SESSION['Dokument']['Fuellung']['Indikator'] == 'S12RG'
 or $_SESSION['Dokument']['Fuellung']['Indikator'] == 'F02RG'
  or $_SESSION['Dokument']['Fuellung']['Indikator'] == 'F07RG'
   or $_SESSION['Dokument']['Fuellung']['Indikator'] == 'S02RG'
   )
  
  {$_SESSION['Tabelle']['Trend_Indikator'] = '1';} //=Indikator ist berechtigt
else
		{$_SESSION['Tabelle']['Trend_Indikator'] = '0';}
		
		
//2) nur für Raumgliederungen größer als Gemeinden
if($_SESSION['Dokument']['Raumgliederung'] != "gem"
 and $_SESSION['Dokument']['Raumgliederung'] != "g50" 
 and $_SESSION['Dokument']['Raumgliederung'] != "stt")
   {$_SESSION['Tabelle']['Trend_Raumgliederung'] = '1';} //=Raumgl. ist berechtigt

else
		{$_SESSION['Tabelle']['Trend_Raumgliederung'] = '0';}
		
//3) nur wenn aktuellster Zeitschnitt in Monitor gewählt 
if($_SESSION['Dokument']['Jahr_Anzeige'] == $last_year)

   {$_SESSION['Tabelle']['Trend_Jahr'] = '1';} ///=Zeitschnitt ist berechtigt

else
		{$_SESSION['Tabelle']['Trend_Jahr'] = '0';}		
		
		
//4) Gesamtberechtigung, wenn 1-3) zutreffend
 if ($_SESSION['Tabelle']['Trend_Raumgliederung'] == '1' 
 and $_SESSION['Tabelle']['Trend_Indikator'] == '1' 
 and $_SESSION['Tabelle']['Trend_Jahr'] == '1')
  {$_SESSION['Tabelle']['Trend_Berechtigung'] = '1';} ///=alle Berechtigunggen sind erteilt
		
else
		{$_SESSION['Tabelle']['Trend_Berechtigung'] = '0';}	
		

//Ende Trendfortschreibung


// Erkennen ob ein "ha-Ziel"-Indikator gewählt wurde, um Anzeige der Veränderung in ha/Tage ausgeben zu können
if(($_SESSION['Dokument']['Fuellung']['Indikator'] == 'S11RG'
or $_SESSION['Dokument']['Fuellung']['Indikator'] == 'S12RG'
 or $_SESSION['Dokument']['Fuellung']['Indikator'] == 'S15RG'
  or $_SESSION['Dokument']['Fuellung']['Indikator'] == 'V01RG'
   or $_SESSION['Dokument']['Fuellung']['Indikator'] == 'S08RG'
    or $_SESSION['Dokument']['Fuellung']['Indikator'] == 'F02RG'
	 or $_SESSION['Dokument']['Fuellung']['Indikator'] == 'F07RG')
and $_SESSION['Dokument']['Raumgliederung'] == "bld")
{
	$_SESSION['Tabelle']['WERT_ABS_DIFF'] = '1';
}
else
{
	$_SESSION['Tabelle']['WERT_ABS_DIFF'] = '';
}


// Dateiname + Pfad für evtl. CSV-Export bestimmen
if($_POST['csv']) 
{
	// $Dateiname = mt_rand().".csv";
	
	$Dateiname = $_SESSION['Dokument']['Fuellung']['Indikator'].'_'.$_SESSION['Dokument']['Jahr_Anzeige'].'_'.$_SESSION['Dokument']['Raumgliederung'].'_'.mt_rand(0,1000).'.csv';
	$tmpfname = "../temp/".$Dateiname;
}

// Einstellg bezügl MITTLERE_AKTUALITAET_IGNORE erfassen
$SQL_Indikator_Info = "SELECT * FROM m_indikatoren WHERE ID_INDIKATOR='".$_SESSION['Dokument']['Fuellung']['Indikator']."'";
$Ergebnis_Indikator_Info = mysqli_query($Verbindung,$SQL_Indikator_Info);
$MITTLERE_AKTUALITAET_IGNORE = utf8_encode(@mysqli_result($Ergebnis_Indikator_Info,0,'MITTLERE_AKTUALITAET_IGNORE'));



// Spezielle ZV für Vergleiche für Indikator aus DB laden
if($_SESSION['Dokument']['Fuellung']['Indikator'])
{
	$SQL_ZV = "SELECT * FROM m_zeichenvorschrift WHERE ID_INDIKATOR='".$_SESSION['Dokument']['Fuellung']['Indikator']."'";
	$Ergebnis_ZV = mysqli_query($Verbindung,$SQL_ZV);
			
	// Für Differenzkarten über Tabellentool wichtig
	$_SESSION['Dokument']['Fuellung']['Farbwert_Diff_min'] = @mysqli_result($Ergebnis_ZV,0,'FARBWERT_DIFF_MIN');
	$_SESSION['Dokument']['Fuellung']['Farbwert_Diff_max'] = @mysqli_result($Ergebnis_ZV,0,'FARBWERT_DIFF_MAX');
	// Standardwerte setzen, falls nicht vorhanden
	if(!$_SESSION['Dokument']['Fuellung']['Farbwert_Diff_min'] or !$_SESSION['Dokument']['Fuellung']['Farbwert_Diff_max'])
	{
		$_SESSION['Dokument']['Fuellung']['Farbwert_Diff_min'] = "6490C2";
		$_SESSION['Dokument']['Fuellung']['Farbwert_Diff_max'] = "DD9988";
	}
}




// Voreinstellungen
// $akt=1; // Aktualität anzeigen <= muss später aus DB kommen

// Schalter zum Anspringen des Ankers #tabellenkopf
if($_GET['sort'] or $_POST['Zeitschnitt_UERE_Formular'] or $_GET['Indikator_2']) $Sortierung_gewaehlt = 1; 


// Karte
// -----------------------------------------------
// Kartenanzeige ein und ausschalten
if($_POST['KARTENANZEIGE_WERT'])
{
	if($_POST['KARTENANZEIGE_WERT'] != "ausblenden")
	{
		$_SESSION['Tabelle']['KARTENANZEIGE_TYP'] = $_POST['KARTENANZEIGE_TYP'];
		$_SESSION['Tabelle']['KARTENANZEIGE_WERT'] = $_POST['KARTENANZEIGE_WERT'];
		$_SESSION['Tabelle']['KARTENANZEIGE_FEHLERCODE'] = $_POST['KARTENANZEIGE_FEHLERCODE'];
		$_SESSION['Tabelle']['KARTENANZEIGE_FEHLERCODE_AKT'] = $_POST['KARTENANZEIGE_FEHLERCODE_AKT'];
		$_SESSION['Tabelle']['KARTENANZEIGE_ZZEITSCHNITT'] = $_POST['KARTENANZEIGE_ZZEITSCHNITT']; 
	}
	else
	{
		$_SESSION['Tabelle']['KARTENANZEIGE_TYP'] = '';
		$_SESSION['Tabelle']['KARTENANZEIGE_WERT'] = '';
		$_SESSION['Tabelle']['KARTENANZEIGE_FEHLERCODE'] = '';
		$_SESSION['Tabelle']['KARTENANZEIGE_FEHLERCODE_AKT'] = '';
		$_SESSION['Tabelle']['KARTENANZEIGE_ZZEITSCHNITT'] = '';
	}
}
// Tabelle wieder anzeigen und Karte ausblenden ... Leeren der Anzeigeinformationen zur Vermeidung von Fehlern
if($_POST['TABELLENANZEIGE']) $_SESSION['Tabelle']['KARTENANZEIGE_WERT'] = '';
if($_POST['TABELLENANZEIGE']) $_SESSION['Tabelle']['KARTENANZEIGE_FEHLERCODE'] = '';
if($_POST['TABELLENANZEIGE']) $_SESSION['Tabelle']['KARTENANZEIGE_ZZEITSCHNITT'] = '';

// ------------------------------------------------

// 2. Indikator für "Dummanzeige" erfassen
if($_GET['Indikator_2'])
{
	$_SESSION['Tabelle']['Indikator_2'] = $_GET['Indikator_2'];
}
if($_GET['Indikator_2'] == "leer")
{
	$_SESSION['Tabelle']['Indikator_2'] = "";
}


// Absolutwert-Indikator für Anzeige erfassen
if($_POST['WERT_ABS'] or $_POST['WERT_ABS_DIFF'] and ((substr($_SESSION['Dokument']['Fuellung']['Indikator'],3,2) == 'RG'  && $_SESSION['Dokument']['Fuellung']['Indikator']!=='O01RG' && $_SESSION['Dokument']['Fuellung']['Indikator']!=='O03RG' && $_SESSION['Dokument']['Fuellung']['Indikator']!=='O04RG') or substr($_SESSION['Dokument']['Fuellung']['Indikator'],3,2) == 'RT'))
{
	$_SESSION['Tabelle']['WERT_ABS'] = "1";
}
else
{
	if($_POST['Zeitschnitt_UERE_Formular']) $_SESSION['Tabelle']['WERT_ABS'] = '';
	if((substr($_SESSION['Dokument']['Fuellung']['Indikator'],3,2) == 'RG' && $_SESSION['Dokument']['Fuellung']['Indikator']!=='O01RG' && $_SESSION['Dokument']['Fuellung']['Indikator']!=='O03RG' && $_SESSION['Dokument']['Fuellung']['Indikator']!=='O04RG')
					 or substr($_SESSION['Dokument']['Fuellung']['Indikator'],3,2) == 'RT')
	{
		// Keine Aktion
	}
	else
	{
		$_SESSION['Tabelle']['WERT_ABS'] = '';
	}
}






// Ein-Ausblenden der Flächengröße
if($_POST['FLAECHE']) 
{
	$_SESSION['Tabelle']['FLAECHE'] = '1'; 
}
else
{
	if($_POST['Zeitschnitt_UERE_Formular']) $_SESSION['Tabelle']['FLAECHE'] = '0';
}

// Ein-Ausblenden der Einwohnerzahl
if($_POST['EWZ'] ) 
{
	$_SESSION['Tabelle']['EWZ'] = '1'; 
}
else
{
	if($_POST['Zeitschnitt_UERE_Formular']) $_SESSION['Tabelle']['EWZ'] = '0';
}



// Raumgliederung neu setzen, falls bei KRS/LKS/KFS geändert
if($_POST['KRS_LKS_KFS']) 
{
	$_SESSION['Dokument']['Raumgliederung'] = $_POST['KRS_LKS_KFS'];
	// Name der Raumgliederung anhand der DB-Kennung ($_SESSION['Dokument']['Raumgliederung']) ermitteln
	$SQL_Raumgliederung_Stellenanzahl = "SELECT * FROM v_raumgliederung WHERE DB_Kennung = '".$_SESSION['Dokument']['Raumgliederung']."'"; 
	$Ergebnis_Raumgliederung_Stellenanzahl = mysqli_query($Verbindung,$SQL_Raumgliederung_Stellenanzahl);
	$_SESSION['Dokument']['Raumgliederung_Stellenanzahl'] = mysqli_result($Ergebnis_Raumgliederung_Stellenanzahl,0,'DB_AGS_Stellenanzahl');
	
	// Setzen der korrekten Geometrietabellen für die einzelnen Datensets
	
	foreach($_SESSION['Datenbestand'] as $DatenSet)
	{
			$_SESSION['Datenbestand'][$DatenSet['NAME']]['DB_Tabelle'] = $_SESSION['Datenbestand'][$DatenSet['NAME']]['DB_Tabelle_Teilstring']."_"
																						.$_SESSION['Dokument']['Raumgliederung']."_".$_SESSION['Dokument']['Jahr_Geometrietabelle'];
	}
}

// Sortierung (Nur wenn wirklich abgesendet)
if($_GET['sort'] and !$_POST['Zeitschnitt_UERE_Formular'])
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
	$_SESSION['Tabellen_Sortierung_FC'] = $_GET['SortFC'];
}
// else
// {
	// Standardsortierung
	if(!$_SESSION['Tabellen_Sortierung'])
	{
		$_SESSION['Tabellen_Sortierung'] = "NAME"; 
		$_SESSION['Tabellen_Sortierung_asc_desc'] = "ASC";
	}
// }




// Zusatz zur Sortierung (wichtig für Fehlerbehandlung bei zus. Zeitschnitten) (Nur wenn wirklich abgesendet)
if($_GET['Zusatzzeitschnitt'] and !$_POST['Zeitschnitt_UERE_Formular'])
{
	if($_GET['Zusatzzeitschnitt'] != 'nein')
	{
		$_SESSION['Tabellen_Sortierung_ZZ'] = $_GET['Zusatzzeitschnitt'];
	}
	else
	{
		$_SESSION['Tabellen_Sortierung_ZZ'] = '';
	}
}
else
{
	$_SESSION['Tabellen_Sortierung_ZZ'] = '';
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







// Auschluss/Einbeziehen gemeindefreier Gebiete
if($_POST['Zeitschnitt_UERE_Formular'] or $Erstaufruf) 
{
	// Gemeindefreie Gebiete zur Anzeige bringen
	if($_POST['GEMEINDEFREI'] == '1')
	{
		$_SESSION['Tabelle']['GEMEINDEFREI'] = '1';
		$_SQL_GEMEINDEFREI = "";
	}
	// Gemeindefreie Gebiete ausklammern
	if($_POST['GEMEINDEFREI'] != '1' or $Erstaufruf)
	{
		$_SESSION['Tabelle']['GEMEINDEFREI'] = '0';
		$_SQL_GEMEINDEFREI = " AND des <> 'gemeindefreies Gebiet' AND des <> 'Gemeindefreies Gebiet' ";
	}

}
else
{
	// Gemeindefreie Gebiete als Standard ausklammern
	if(!$_SESSION['Tabelle']['GEMEINDEFREI']) 
	{
		$_SESSION['Tabelle']['GEMEINDEFREI'] = '0';
		$_SQL_GEMEINDEFREI = " AND des <> 'gemeindefreies Gebiet'";
	}
	else
	{ 
		$_SQL_GEMEINDEFREI = ""; 
	}
}


// Definition für Hinweiscodes erfassen

$SQL_HC = "SELECT * FROM m_hinweiscodes";
$Ergebnis_HC = mysqli_query($Verbindung,$SQL_HC);
$i_h=0;
while($HC_Code = @mysqli_result($Ergebnis_HC,$i_h,'HC'))
{
	$HC_Definition[$HC_Code]['HC_NAME'] = utf8_encode(@mysqli_result($Ergebnis_HC,$i_h,'HC_NAME'));
	$HC_Definition[$HC_Code]['HC_INFO'] = utf8_encode(@mysqli_result($Ergebnis_HC,$i_h,'HC_INFO'));
	
	$i_h++;
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




// Alles abschalten wenn 2. Indikator gewählt wurde
if($_SESSION['Tabelle']['Indikator_2'])
{
	$_SESSION['Tabelle']['UERE'] = '0';
	$_SESSION['Tabelle']['UERE_KRS'] = '0';
	$_SESSION['Tabelle']['UERE_BLD'] = '0';
	$_SESSION['Tabelle']['UERE_BND'] = '0';
	$_SESSION['Tabelle']['VERGLEICH'] = '0';
	$_SESSION['Tabelle']['AKTUALITAET'] = '0';

}



// Testvariable für Laufzeit
$Laufzeit = $Laufzeit.'
Tabelle erzeugen: '.date('H:i:s');



// -----------------------------------------------
// Temp-Table erstellen

// Wichtig: Tabelle vorher löschen, da sonst Inhalte behalten wersen!?
$SQL_temp_table = "DROP TABLE `t_temp_tabellentool`";



//-------------> nur zum testen:

/* TEMPORARY */
$Ergebnis_drop_table = mysqli_query($Verbindung,"DROP TABLE `t_temp_tabellentool`");
$SQL_temp_table = "CREATE TEMPORARY TABLE `t_temp_tabellentool` (
  `AGS` varchar(20) NOT NULL,
  `NAME` varchar(100) default NULL,
  `SVG_GEOMETRIE` varchar(255) default NULL,
  `FLAECHE` double default 0,
  `EWZ` double default 0,
  `WERT` double default 0,
  `WERT_ABS` double default 0,
  `FEHLERCODE` varchar(2)  NULL DEFAULT 0,
  `HINWEISCODE` varchar(100)  NULL DEFAULT 0,
  `WERT_2` double default 0,
  `FEHLERCODE_2` varchar(2)  NULL DEFAULT 0,
  `HINWEISCODE_2` varchar(100)  NULL DEFAULT 0,
  `AKT` double default 0,
  `AKT_AUSGABE` varchar(100) default 0,
  `NAME_KRS` varchar(100) default 0,
  `WERT_KRS` double default 0,
  `WERT_KRS_DIFF` double default 0,
  `AKT_KRS` double default 0,
  `AKT_KRS_AUSGABE` varchar(100) default 0,
  `FEHLERCODE_KRS` varchar(2) default 0,
  `NAME_BLD` varchar(100) default 0,
  `WERT_BLD` double default 0,
  `WERT_BLD_DIFF` double default 0,
  `AKT_BLD` double default 0,
  `AKT_BLD_AUSGABE` varchar(100) default 0,
  `FEHLERCODE_BLD` varchar(2) default 0, 
  `WERT_BND` double default 0,
  `WERT_BND_DIFF` double default 0,
  `AKT_BND` double default 0,
  `AKT_BND_AUSGABE` varchar(100) default 0,
  `FEHLERCODE_BND` varchar(2) default 0, 
   PRIMARY KEY  (`AGS`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8;";  // ENGINE=MEMORY


		
$Ergebnis_temp_table = mysqli_query($Verbindung,$SQL_temp_table); 



// Gewählte Raumebenen erfassen und SQL vorbereiten
if(is_array($_SESSION['Datenbestand']) || is_object($_SESSION['Datenbestand']))  //in session array steht exakt das gleiche wie in www server
{
 
	foreach($_SESSION['Datenbestand'] as $DatenSet)
	{
		// Check auf [View]==1 < wenn =1 dann ausgewählt
		if($DatenSet['View']=='1')
		{
			$Region = '1'; // Schalter, ob min 1 Region ausgewählt ist				// ja 1 wird gesetzt
		
			foreach($DatenSet['Auswahlkriterium_Wert'] as $UnterDatenSet)   	//	echo $UnterDatenSet;  //10 14 15 01 16
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
		
		/* Für Prüfer auch Rasterdaten anzeigen
		if($_SESSION['Dokument']['Raumgliederung'] == "rst")
		{
			$Rasteranzeige_fuer_pruefer = " OR AGS_ZELLE LIKE '".$Teil_AGS."%' ";
			$_SQL_GEMEINDEFREI = "";
		}
	
		*/
		
		// Betroffene Einheiten mit Namen aus PG selektieren
		$SQL_PG_Name = "SELECT ags,gen 
						FROM vg250_".$_SESSION['Dokument']['Raumgliederung']."_".$_SESSION['Dokument']['Jahr_Geometrietabelle']."_".$_SESSION['Dokument']['Raumgliederung_Zusatzkennung']." 
						WHERE AGS LIKE '".$Teil_AGS."%' ".$Rasteranzeige_fuer_pruefer." ".$_SQL_GEMEINDEFREI."";
		$ERGEBNIS_PG_Name = pg_query($Verbindung_PostgreSQL,$SQL_PG_Name);  				
		while($PG_Zeile = @pg_fetch_assoc($ERGEBNIS_PG_Name))
		{
			
			$testzaehler++;
			
			// Kreise als übergeordnete Raumeinheit mit anzeigen wenn Gemeindeebene ausgewählt
			if($_SESSION['Dokument']['Raumgliederung'] == "gem")
			{
				// Kreis-Namen aus PG selektieren
				$SQL_PG_Name_KRS = "SELECT gen FROM vg250_krs_".$_SESSION['Dokument']['Jahr_Geometrietabelle']."_".$_SESSION['Dokument']['Raumgliederung_Zusatzkennung']." 
									WHERE AGS = '".substr($PG_Zeile['ags'],0,5)."'";
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
			or $_SESSION['Dokument']['Raumgliederung'] == "lks"
			or $_SESSION['Dokument']['Raumgliederung'] == "ror"
			)
			{
				// Kreis-Namen aus PG selektieren
				$SQL_PG_Name_BLD = "SELECT gen FROM vg250_bld_".$_SESSION['Dokument']['Jahr_Geometrietabelle']."_".$_SESSION['Dokument']['Raumgliederung_Zusatzkennung']." 
									WHERE AGS = '".substr($PG_Zeile['ags'],0,2)."'";
				$ERGEBNIS_PG_Name_BLD = pg_query($Verbindung_PostgreSQL,$SQL_PG_Name_BLD);  				
				$PG_Zeile_BLD = @pg_fetch_assoc($ERGEBNIS_PG_Name_BLD);
				$Name_BLD = $PG_Zeile_BLD['gen'];
			}
			else
			{
				// u.U. auswertbar für Tabellenanzeige
				$Name_BLD = "-";
			}
			
			
			$SQL_PG_AG = 'INSERT INTO t_temp_tabellentool 
							(AGS,NAME,NAME_KRS,NAME_BLD,SVG_GEOMETRIE) 
						VALUES 
							("'.$PG_Zeile['ags'].'","'.$PG_Zeile['gen'].'","'.$Name_KRS.'","'.$Name_BLD.'","leer");';
			if(!$Ergebnis_PG_AGS = mysqli_query($Verbindung,$SQL_PG_AG)) echo "<!-- Fehler! ".$SQL_PG_AG." -->";
		}
		
		// Deutschlandwerte als zus. Datensatz anfügen
		$SQL_Deutschland = 'INSERT INTO t_temp_tabellentool 
							(AGS,NAME,NAME_KRS,NAME_BLD,SVG_GEOMETRIE) 
						VALUES 
							("99","Deutschland","","","leer");';
		if(!$Ergebnis_Deutschland = mysqli_query($Verbindung,$SQL_Deutschland)) echo "<!-- Fehler! ".$SQL_PG_AG." -->";
	}	
}



// Testvariable für Laufzeit
$Laufzeit = $Laufzeit.'
Tabelle erzeugt/Geodaten eingefügt: '.date('H:i:s');




// -------------------- MySQL -> ------------------------
// Füllen der DB mit Werten
// ------------------------

// Wert für Deutschland für Berechnungen ermitteln (AGS = 99)
$SQL_Indikatorenwert_D = "SELECT INDIKATORWERT,FEHLERCODE FROM m_indikatorwerte_".$_SESSION['Dokument']['Jahr_Anzeige']." WHERE AGS = '99' AND ID_INDIKATOR = '".$_SESSION['Dokument']['Fuellung']['Indikator']."' AND VGL_AB = '0';"; 
$Ergebnis_Indikatorenwerte_D = mysqli_query($Verbindung,$SQL_Indikatorenwert_D); 
$Wert_D = @mysqli_result($Ergebnis_Indikatorenwerte_D,0,'INDIKATORWERT');

$Wert_DE = @mysqli_result($Ergebnis_Indikatorenwerte_D,0,'INDIKATORWERT');
if(empty($Wert_DE)||!isset($Wert_DE))
{$Wert_DE = '0';}
$Fehlercode_D = @mysqli_result($Ergebnis_Indikatorenwerte_D,0,'FEHLERCODE');
if(empty($Fehlercode_D)||!isset($Fehlercode_D))
{$Fehlercode_D='0';}

// Mittl. Grundaktualität für Deutschland für Berechnungen ermitteln (AGS = 99)
$SQL_Akt_D = "SELECT INDIKATORWERT,FEHLERCODE FROM m_indikatorwerte_".$_SESSION['Dokument']['Jahr_Anzeige']." WHERE AGS = '99' AND ID_INDIKATOR = 'Z03AG' AND VGL_AB = '0';"; 
$Ergebnis_Akt_D = mysqli_query($Verbindung,$SQL_Akt_D); 
$Akt_D = @mysqli_result($Ergebnis_Akt_D,0,'INDIKATORWERT');
// Akt Ausgabe
// Jahr
$SQL_Akt_D = "SELECT INDIKATORWERT,FEHLERCODE FROM m_indikatorwerte_".$_SESSION['Dokument']['Jahr_Anzeige']." WHERE AGS = '99' AND ID_INDIKATOR = 'Z00AG' AND VGL_AB = '0';"; 
$Ergebnis_Akt_D = mysqli_query($Verbindung,$SQL_Akt_D); 
$Akt_D_J = @mysqli_result($Ergebnis_Akt_D,0,'INDIKATORWERT');
// Monat
$SQL_Akt_D = "SELECT INDIKATORWERT,FEHLERCODE FROM m_indikatorwerte_".$_SESSION['Dokument']['Jahr_Anzeige']." WHERE AGS = '99' AND ID_INDIKATOR = 'Z01AG' AND VGL_AB = '0';"; 
$Ergebnis_Akt_D = mysqli_query($Verbindung,$SQL_Akt_D); 
$Akt_D_m = @mysqli_result($Ergebnis_Akt_D,0,'INDIKATORWERT');

if($Akt_D_m < 10) $Akt_D_m = "0".$Akt_D_m;
$Akt_D_Ausgabe = $Akt_D_m." / ".$Akt_D_J;


// ------------------------
// DB-Tabelle füllen
$SQL_DS_vorh = "SELECT * FROM t_temp_tabellentool";
$Ergebnis_DS_vorh = mysqli_query($Verbindung,$SQL_DS_vorh); 
$i_ds = 0;

// Testvariable für Laufzeit
$Laufzeit = $Laufzeit.'
Tabellen Werte abgefragt: '.date('H:i:s');

while($ags = @mysqli_result($Ergebnis_DS_vorh,$i_ds,'AGS'))
{

	// Indikatorwert, Hinweiscode(s) und Fehlercode
	$SQL_Indikatorenwerte = "SELECT AGS,INDIKATORWERT,FEHLERCODE 
	FROM m_indikatorwerte_".$_SESSION['Dokument']['Jahr_Anzeige']." 
	WHERE AGS = '".$ags."' 
	AND ID_INDIKATOR = '".$_SESSION['Dokument']['Fuellung']['Indikator']."' AND VGL_AB = '0';"; 
	$Ergebnis_Indikatorenwerte = mysqli_query($Verbindung,$SQL_Indikatorenwerte); 

	

	// Check auf 0 oder NULL
	if(@mysqli_result($Ergebnis_Indikatorenwerte,0,'AGS'))  
	{
	
		$UPD_Wert = @mysqli_result($Ergebnis_Indikatorenwerte,0,'INDIKATORWERT');   
		
		$UPD_Fehlercode = @mysqli_result($Ergebnis_Indikatorenwerte,0,'FEHLERCODE');
	
	
		// Hinweiscodes	
		$SQL_Hinweiscode = "SELECT Code FROM h_".$_SESSION['Dokument']['Jahr_Anzeige']."  
							WHERE 
							INDIKATOR = '".$_SESSION['Dokument']['Fuellung']['Indikator']."' 
							AND
							AGS = '".$ags."'
							GROUP BY Code 
							";
		$Ergebnis_Hinweiscode = mysqli_query($Verbindung,$SQL_Hinweiscode); 	
		
		// Auf mehrere Hinweiscodes prüfen
		$UPD_Hinweiscode = ''; // Leeren, für den nächsten AGS für den Fall, dass kein HC übergeben wurde	
		$i_HC=0;
		while($Hinweiscode_Teil = @mysqli_result($Ergebnis_Hinweiscode,$i_HC,'Code'))
		{
			if($i_HC==0)
			{
				//$Hinweiscodes_Dopplung = array(); // Array leeren
				//$Hinweiscodes_Dopplung[$Hinweiscode_Teil] = $Hinweiscode_Teil;
				$UPD_Hinweiscode = $Hinweiscode_Teil;
			}
			else
			{
				//if($Hinweiscodes_Dopplung[$Hinweiscode_Teil] != $Hinweiscode_Teil)
				//{
					$UPD_Hinweiscode = $UPD_Hinweiscode.','.$Hinweiscode_Teil;
				//}
			}
			// alt aber in verwendung
			$Hinweiscodes_vorhanden[$Hinweiscode_Teil] = 1;
			//
			$Hinweiscodes_Dokument[$Hinweiscode_Teil] = $Hinweiscode_Teil;
			$i_HC++;
		}
	}
	else
	{
		$UPD_Wert = NULL; 
		$UPD_Fehlercode = '1';	

	}
	// print_r($Hinweiscodes_Dokument);
		

	// Schalter zur Prüfung, ob Hinweiscodes übergeben wurden
	//if($UPD_Hinweiscode) $Hinweiscodes_vorhanden[$UPD_Hinweiscode] = 1;
	
	
	
	// Indikatorwert als Absolutwert auslesen
	if($_SESSION['Tabelle']['WERT_ABS'] or $_SESSION['Tabelle']['WERT_ABS_DIFF'])
	{
		$IND_ABS = substr($_SESSION['Dokument']['Fuellung']['Indikator'],0,3).'AG'; // Absolutwert-Indikator-ID zusammenstellen
		$SQL_Indikatorenwerte_AG = "SELECT INDIKATORWERT,FEHLERCODE 
		FROM m_indikatorwerte_".$_SESSION['Dokument']['Jahr_Anzeige']." 
		WHERE AGS = '".$ags."' 
		AND ID_INDIKATOR = '".$IND_ABS."' AND VGL_AB = '0';"; 
		$Ergebnis_Indikatorenwerte_AG = mysqli_query($Verbindung,$SQL_Indikatorenwerte_AG); 
						
		// $UPD_Wert_ABS = (@mysqli_result($Ergebnis_Indikatorenwerte_AG,0,'INDIKATORWERT')*100); // inkl Umrechnung von km² in ha
		
	 $UPD_Wert_ABS = @mysqli_result($Ergebnis_Indikatorenwerte_AG,0,'INDIKATORWERT'); // in km² 
	
	
	}
	else
	{
			$UPD_Wert_ABS ='0';
		}

	// Indikatorwert_2 mit Fehlercodes
	if($_SESSION['Tabelle']['Indikator_2'])
	{
		$SQL_Indikatorenwerte_2 = "SELECT INDIKATORWERT,FEHLERCODE  
		FROM m_indikatorwerte_".$_SESSION['Dokument']['Jahr_Anzeige']." 
		WHERE AGS = '".$ags."' 
		AND ID_INDIKATOR = '".$_SESSION['Tabelle']['Indikator_2']."' AND VGL_AB = '0';"; 
		$Ergebnis_Indikatorenwerte_2 = mysqli_query($Verbindung,$SQL_Indikatorenwerte_2); 
		
		$UPD_Wert_2 = @mysqli_result($Ergebnis_Indikatorenwerte_2,0,'INDIKATORWERT');
		$UPD_Fehlercode_2 = @mysqli_result($Ergebnis_Indikatorenwerte_2,0,'FEHLERCODE');
		
		// Hinweiscodes
		/* 
		$SQL_Hinweiscode_2 = "SELECT Code FROM h_".$_SESSION['Dokument']['Jahr_Anzeige']."  
							WHERE 
							INDIKATOR = '".$_SESSION['Tabelle']['Indikator_2']."' 
							AND
							AGS = '".$ags."'
							";
		$Ergebnis_Hinweiscode_2 = mysqli_query($Verbindung,$SQL_Hinweiscode_2); 	 
		$UPD_Hinweiscode_2 = @mysqli_result($Ergebnis_Hinweiscode_2,0,'Code');
		
		// Schalter zur Prüfung, ob Hinweiscodes übergeben wurden
		if($UPD_Hinweiscode) $Hinweiscodes_vorhanden[$UPD_Hinweiscode_2] = 1;	 
		
			// Hinweiscodes	
		$SQL_Hinweiscode = "SELECT Code FROM h_".$_SESSION['Dokument']['Jahr_Anzeige']."  
							WHERE 
							INDIKATOR = '".$_SESSION['Tabelle']['Indikator_2']."' 
							AND
							AGS = '".$ags."' 
							";
		$Ergebnis_Hinweiscode = mysqli_query($Verbindung,$SQL_Hinweiscode); 	
		*/
		// Auf mehrere Hinweiscodes prüfen
		/* $UPD_Hinweiscode = ''; // Leeren, füe den nächsten AGS für den Fall, dass kein HC übergeben wurde	
		$i_HC=0;
		while($Hinweiscode_Teil = @mysqli_result($Ergebnis_Hinweiscode,$i_HC,'Code'))
		{
			if($i_HC==0)
			{
				$Hinweiscodes_Dopplung = array(); // Array leeren
				$Hinweiscodes_Dopplung[$Hinweiscode_Teil] = $Hinweiscode_Teil;
				$UPD_Hinweiscode_2 = $Hinweiscode_Teil;
			}
			else
			{
				if($Hinweiscodes_Dopplung[$Hinweiscode_Teil] != $Hinweiscode_Teil)
				{
					$UPD_Hinweiscode_2 = $UPD_Hinweiscode.','.$Hinweiscode_Teil;
				}
			}
			// alt aber in verwendung
			$Hinweiscodes_vorhanden[$Hinweiscode_Teil] = 1;
			//
			$Hinweiscodes_Dokument[$Hinweiscode_Teil] = $Hinweiscode_Teil;
			
			$i_HC++;
		} */
	}
	else
	{
		$UPD_Wert_2 = '0';
		$UPD_Fehlercode_2 = '0';
	}	
	
	// Gebietsfläche als Metainformation für Tabelle
	if($_SESSION['Tabelle']['FLAECHE']) 
	{
		$SQL_FLAECHE = "SELECT INDIKATORWERT   
		FROM m_indikatorwerte_".$_SESSION['Dokument']['Jahr_Anzeige']." 
		WHERE AGS = '".$ags."' 
		AND ID_INDIKATOR = 'S00AG' AND VGL_AB = '0';"; 
		$Ergebnis_FLAECHE = mysqli_query($Verbindung,$SQL_FLAECHE); 
		
		$UPD_FLAECHE = @mysqli_result($Ergebnis_FLAECHE,0,'INDIKATORWERT');
	}
	else
	{
			$UPD_FLAECHE ='0';
	}
	
	// Einwohnerzahl als Metainformation für Tabelle
	//wenn noch keine EWZ für $last_year vorhanden, so nehme Werte von vorhergehendem Jahr und generiere hinweise
	//für Stadtteile wäre keine EWZ da, deshalb obere fälle nciht beachten und else-zweig wählen, damit keine update/NULL probleme
	if($_SESSION['Tabelle']['EWZ']&& $_SESSION['Dokument']['Jahr_Anzeige']!= $last_year && $_SESSION['Dokument']['Raumgliederung']!=='stt') 
	{ 
		$SQL_EWZ = "SELECT INDIKATORWERT   
		FROM m_indikatorwerte_".$_SESSION['Dokument']['Jahr_Anzeige']." 
		WHERE AGS = '".$ags."' 
		AND ID_INDIKATOR = 'B00AG' AND VGL_AB = '0';"; 
		$Ergebnis_EWZ = mysqli_query($Verbindung,$SQL_EWZ); 
		$UPD_EWZ = @mysqli_result($Ergebnis_EWZ,0,'INDIKATORWERT');
	}
		elseif ($_SESSION['Tabelle']['EWZ']&& $_SESSION['Dokument']['Jahr_Anzeige']== $last_year && $_SESSION['Dokument']['Raumgliederung']!=='stt')
	{ 
		//gerade gewähltes Jahr bestimmen
		$Jahr_Anzeige= $_SESSION['Dokument']['Jahr_Anzeige'];
		//vorgehendes Jahr bestimmen
		$vorh_Jahr = $Jahr_Anzeige-1;
		//EWZ des vohergehenden Jahres
		$SQL_EWZ = "SELECT INDIKATORWERT   
		FROM m_indikatorwerte_".$vorh_Jahr." 
		WHERE AGS = '".$ags."' 
		AND ID_INDIKATOR = 'B00AG' AND VGL_AB = '0';"; 
		$Ergebnis_EWZ = mysqli_query($Verbindung,$SQL_EWZ); 		
		$UPD_EWZ = @mysqli_result($Ergebnis_EWZ,0,'INDIKATORWERT');
		//Hinweise generieren, die später nur aufgerufen werden, wenn sie existieren (Unter Tabmenü und in Tabkopf EWZ)
		$EW_Hinweis="<br/>(*) Für " + $cur_year + ": Einwohnerzahl noch nicht verfügbar";
		$EW_Hinweis_Titel="<br/><span style='font-size: 0.8em;'>(*) Werte " + $last_year + "</span>";
	}
	//damit UPDATE bei php 7 funktioniert, darf nicht NULL übergeben werden, sonst keine Tabellenanzeige
	else 
	{
		$UPD_EWZ = '0';
	}
	
	

		
		// Grundaktualität
		$SQL_Grundktualitaet = "SELECT AGS,INDIKATORWERT,ID_INDIKATOR FROM m_indikatorwerte_".$_SESSION['Dokument']['Jahr_Anzeige']." WHERE (ID_INDIKATOR = 'Z00AG' or ID_INDIKATOR = 'Z01AG' or ID_INDIKATOR = 'Z03AG') AND AGS = '".$ags."' ORDER BY ID_INDIKATOR DESC;"; 
		// $SQL_Grundktualitaet = "SELECT AKTUALITAET FROM v_akt_gesamt WHERE JAHR = '".$Grundakt_Verweis."' AND AGS = '".$ags."'"; 
		$Ergebnis_Grundktualitaet = mysqli_query($Verbindung,$SQL_Grundktualitaet);
		
		// Korrektes einsortieren der Werte und zusammenführen für Ausgabe
		$i_akt_zus = 0;
		while($ID_IND_AKT_ZUS = @mysqli_result($Ergebnis_Grundktualitaet,$i_akt_zus,'ID_INDIKATOR'))
		{
			if($ID_IND_AKT_ZUS == 'Z03AG') $Grundakt = @mysqli_result($Ergebnis_Grundktualitaet,$i_akt_zus,'INDIKATORWERT');
			if($ID_IND_AKT_ZUS == 'Z01AG') $Grundakt_AUSGABE_m = @mysqli_result($Ergebnis_Grundktualitaet,$i_akt_zus,'INDIKATORWERT');
			if($ID_IND_AKT_ZUS == 'Z00AG') $Grundakt_AUSGABE_J = @mysqli_result($Ergebnis_Grundktualitaet,$i_akt_zus,'INDIKATORWERT');
			$i_akt_zus++;
		}
		if($Grundakt_AUSGABE_m < 10) $Grundakt_AUSGABE_m = "0".$Grundakt_AUSGABE_m;
		$Grundakt_AUSGABE = $Grundakt_AUSGABE_m." / ".$Grundakt_AUSGABE_J;
		

		
		// Grundaktualität KRS
		if($_SESSION['Dokument']['Raumgliederung'] == "gem")
		{
			$SQL_Grundktualitaet = "SELECT AGS,INDIKATORWERT,ID_INDIKATOR FROM m_indikatorwerte_".$_SESSION['Dokument']['Jahr_Anzeige']." WHERE (ID_INDIKATOR = 'Z00AG' or ID_INDIKATOR = 'Z01AG' or ID_INDIKATOR = 'Z03AG') AND AGS = '".substr($ags,0,5)."' ORDER BY ID_INDIKATOR DESC;"; 
			// $SQL_Grundktualitaet = "SELECT AKTUALITAET FROM v_akt_gesamt WHERE JAHR = '".$Grundakt_Verweis."' AND AGS = '".substr($ags,0,5)."'"; 
			$Ergebnis_Grundktualitaet = mysqli_query($Verbindung,$SQL_Grundktualitaet);
			
			// Korrektes einsortieren der Werte und zusammenführen für Ausgabe
			$i_akt_zus = 0;
			while($ID_IND_AKT_ZUS = @mysqli_result($Ergebnis_Grundktualitaet,$i_akt_zus,'ID_INDIKATOR'))
			{
				if($ID_IND_AKT_ZUS == 'Z03AG') $Grundakt_KRS = @mysqli_result($Ergebnis_Grundktualitaet,$i_akt_zus,'INDIKATORWERT');
				if($ID_IND_AKT_ZUS == 'Z01AG') $Grundakt_KRS_AUSGABE_m = @mysqli_result($Ergebnis_Grundktualitaet,$i_akt_zus,'INDIKATORWERT');
				if($ID_IND_AKT_ZUS == 'Z00AG') $Grundakt_KRS_AUSGABE_J = @mysqli_result($Ergebnis_Grundktualitaet,$i_akt_zus,'INDIKATORWERT');
				$i_akt_zus++;
			}
			if($Grundakt_KRS_AUSGABE_m < 10) $Grundakt_KRS_AUSGABE_m = "0".$Grundakt_KRS_AUSGABE_m;
			$Grundakt_KRS_AUSGABE = $Grundakt_KRS_AUSGABE_m." / ".$Grundakt_KRS_AUSGABE_J;
			
		}
		else
		{
			$Grundakt_KRS = '0';
			$Grundakt_KRS_AUSGABE ='0';
		}
		
		// Grundaktualität BLD
		if($_SESSION['Dokument']['Raumgliederung'] == "gem" 
		or $_SESSION['Dokument']['Raumgliederung'] == "krs" 
		or $_SESSION['Dokument']['Raumgliederung'] == "kfs" 
		or $_SESSION['Dokument']['Raumgliederung'] == "lks"
		or $_SESSION['Dokument']['Raumgliederung'] == "ror")
		{
			$Grundakt_KRS ='0';
			$SQL_Grundktualitaet = "SELECT AGS,INDIKATORWERT,ID_INDIKATOR FROM m_indikatorwerte_".$_SESSION['Dokument']['Jahr_Anzeige']." WHERE (ID_INDIKATOR = 'Z00AG' or ID_INDIKATOR = 'Z01AG' or ID_INDIKATOR = 'Z03AG') AND AGS = '".substr($ags,0,2)."' ORDER BY ID_INDIKATOR DESC;"; 
			// $SQL_Grundktualitaet = "SELECT AKTUALITAET FROM v_akt_gesamt WHERE JAHR = '".$Grundakt_Verweis."' AND AGS = '".substr($ags,0,2)."'"; 
			$Ergebnis_Grundktualitaet = mysqli_query($Verbindung,$SQL_Grundktualitaet);
			
			// Korrektes einsortieren der Werte und zusammenführen für Ausgabe
			$i_akt_zus = 0;
			while($ID_IND_AKT_ZUS = @mysqli_result($Ergebnis_Grundktualitaet,$i_akt_zus,'ID_INDIKATOR'))
			{
				if($ID_IND_AKT_ZUS == 'Z03AG') $Grundakt_BLD = @mysqli_result($Ergebnis_Grundktualitaet,$i_akt_zus,'INDIKATORWERT');
				if($ID_IND_AKT_ZUS == 'Z01AG') $Grundakt_BLD_AUSGABE_m = @mysqli_result($Ergebnis_Grundktualitaet,$i_akt_zus,'INDIKATORWERT');
				if($ID_IND_AKT_ZUS == 'Z00AG') $Grundakt_BLD_AUSGABE_J = @mysqli_result($Ergebnis_Grundktualitaet,$i_akt_zus,'INDIKATORWERT');
				$i_akt_zus++;
			}
			if($Grundakt_BLD_AUSGABE_m < 10) $Grundakt_BLD_AUSGABE_m = "0".$Grundakt_BLD_AUSGABE_m;
			$Grundakt_BLD_AUSGABE = $Grundakt_BLD_AUSGABE_m." / ".$Grundakt_BLD_AUSGABE_J;
			
		}

				else
		{
				
			$Grundakt_BLD = '0';
			$Grundakt_BLD_AUSGABE ='0';
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
			AND ID_INDIKATOR = '".$_SESSION['Dokument']['Fuellung']['Indikator']."' AND VGL_AB = '0';"; 
			$Ergebnis_Indikatorenwerte_KRS = mysqli_query($Verbindung,$SQL_Indikatorenwerte_KRS); 
					
			$UPD_Wert_KRS = @mysqli_result($Ergebnis_Indikatorenwerte_KRS,0,'INDIKATORWERT');
			$UPD_Fehlercode_KRS = @mysqli_result($Ergebnis_Indikatorenwerte_KRS,0,'FEHLERCODE');
		}
		else
		{
			$UPD_Wert_KRS = '0';
			$UPD_Fehlercode_KRS = '0';
			}
	
		// Werte für Bundesländer als übergeordnete Raumeinheit mit anzeigen wenn folgendes ausgewählt
		if($_SESSION['Dokument']['Raumgliederung'] == "gem" 
		or $_SESSION['Dokument']['Raumgliederung'] == "krs" 
		or $_SESSION['Dokument']['Raumgliederung'] == "kfs" 
		or $_SESSION['Dokument']['Raumgliederung'] == "lks"
		or $_SESSION['Dokument']['Raumgliederung'] == "ror")
		{
			// Indikatorwert und Fehlercode
			$SQL_Indikatorenwerte_BLD = "SELECT INDIKATORWERT,FEHLERCODE 
			FROM m_indikatorwerte_".$_SESSION['Dokument']['Jahr_Anzeige']." 
			WHERE AGS = '".substr($ags,0,2)."' 
			AND ID_INDIKATOR = '".$_SESSION['Dokument']['Fuellung']['Indikator']."' AND VGL_AB = '0';"; 
			$Ergebnis_Indikatorenwerte_BLD = mysqli_query($Verbindung,$SQL_Indikatorenwerte_BLD); 
					
			$UPD_Wert_BLD = @mysqli_result($Ergebnis_Indikatorenwerte_BLD,0,'INDIKATORWERT');
			$UPD_Fehlercode_BLD = @mysqli_result($Ergebnis_Indikatorenwerte_BLD,0,'FEHLERCODE');
		}
			else
		{
			$UPD_Wert_BLD = '0';
			$UPD_Fehlercode_BLD = '0';
		
		}	
	}
	else
		{
			$UPD_Wert_KRS = '0';
			$UPD_Fehlercode_KRS = '0';
			$UPD_Wert_BLD = '0';
			$UPD_Fehlercode_BLD = '0';
		
			
		}	
//; //wofür ist das gut??

	// Update der bestehenden Datensätze    
//leere Werte für Update belegen, haben sonst keinen Einfluss)


if(empty($Grundakt_KRS)||!isset($Grundakt_KRS)) {$Grundakt_KRS='0';}
if(empty($Grundakt_BLD)||!isset($Grundakt_BLD)) {$Grundakt_BLD='0';}
if(empty($Grundakt_BLD_AUSGABE)||!isset($Grundakt_BLD_AUSGABE)) {$Grundakt_BLD_AUSGABE='0';}
if(empty($Grundakt_KRS_AUSGABE)||!isset($Grundakt_KRS_AUSGABE)) {$Grundakt_KRS_AUSGABE='0';}

if(empty($Akt_D)||!isset($Akt_D)) {$Akt_D='0';}
if(empty($Akt_D_Ausgabe)||!isset($Akt_D_Ausgabe)) {$Akt_D_Ausgabe=NULL;}

	$SQL_DS_UPD = "UPDATE t_temp_tabellentool 
	SET
	WERT = ".$UPD_Wert.",
	WERT_ABS = ".$UPD_Wert_ABS.",
	FEHLERCODE = '".$UPD_Fehlercode."',
		EWZ = '".$UPD_EWZ."',
		FLAECHE = '".$UPD_FLAECHE."',
			WERT_2 = '".$UPD_Wert_2."',
	FEHLERCODE_2 = '".$UPD_Fehlercode_2."',
		AKT = '".$Grundakt."',
	AKT_AUSGABE = '".$Grundakt_AUSGABE."',
	
	
	AKT_KRS = '".$Grundakt_KRS."',
	WERT_KRS = '".$UPD_Wert_KRS."',
	AKT_KRS_AUSGABE = '".$Grundakt_KRS_AUSGABE."',
	FEHLERCODE_KRS = '".$UPD_Fehlercode_KRS."',
	
	WERT_BLD = '".$UPD_Wert_BLD."',
 FEHLERCODE_BLD = '".$UPD_Fehlercode_BLD."',
  	AKT_BLD = '".$Grundakt_BLD."',
	AKT_BLD_AUSGABE = '".$Grundakt_BLD_AUSGABE."',
	

	
		AKT_BND = '".$Akt_D."',
	AKT_BND_AUSGABE = '".$Akt_D_Ausgabe."',


	WERT_BND = '".$Wert_DE."',	
	FEHLERCODE_BND = '".$Fehlercode_D."',

	
	HINWEISCODE = '".$UPD_Hinweiscode."',
	HINWEISCODE_2 = '".$UPD_Hinweiscode_2."'

	WHERE AGS = '".$ags."'";
	
	
	$Ergebnis_DS_UPD = mysqli_query($Verbindung,$SQL_DS_UPD); 
	
	$i_ds++;
	
} 
// Testvariable für Laufzeit
$Laufzeit = $Laufzeit.'
while beendet: '.date('H:i:s');

// ---- Fehlercode bei NULL Werten vergeben -----------
$SQL_DS_FC = "UPDATE t_temp_tabellentool 
	SET FEHLERCODE = '1' 
	WHERE WERT IS NULL;";
$Ergebnis_DS_FC = mysqli_query($Verbindung,$SQL_DS_FC); 

// Testvariable für Laufzeit
$Laufzeit = $Laufzeit.'
Tabelle Werte Basiszeitschnitt eingefügt: '.date('H:i:s');



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



// Zusätzliche Zeitschnitte einfügen

// Sortierung AbfaRge der möglichen Zeitschnitte für den (evtl. auch eingeloggten) Nutzer
$SQL_Jahre_uebernahme = "SELECT JAHR FROM m_indikator_freigabe,v_geometrie_jahr_viewer_postgis 
                                                WHERE m_indikator_freigabe.STATUS_INDIKATOR_FREIGABE >= '".$_SESSION['Dokument']['ViewBerechtigung']."' 
                                                AND m_indikator_freigabe.JAHR = v_geometrie_jahr_viewer_postgis.Jahr_im_Viewer
												AND m_indikator_freigabe.ID_INDIKATOR = '".$_SESSION['Dokument']['Fuellung']['Indikator']."' 
                                                GROUP BY JAHR 
                                                ORDER BY SORTIERUNG_VIEWER DESC"; 
$Ergebnis_Jahre_uebernahme = mysqli_query($Verbindung,$SQL_Jahre_uebernahme);
     
// Leeren der betreffenden SESSION-Array Abschnitte, wenn das vorgesehene Formular abgesendet wurde
// -------> Wichtig <------
if($_POST['Zeitschnitt_UERE_Formular'])
{
	// Zeitschnitte leeren, um sie dann dem Formular entsprechend korrekt zu füllen
	$_SESSION['Tabelle']['Zeitschnitt_Zusatz'] = array(); 
    
	// Bei fehlendem Zeitschnitt, entsprechende Sortierung entfernen
	$i_jhru1 = 0;
	while($ZusatzZS = @mysqli_result($Ergebnis_Jahre_uebernahme,$i_jhru1,'JAHR') and !$_SESSION['Tabelle']['Indikator_2'])
	{
		if($_POST['ZS_'.$ZusatzZS])
		{
			$temp_Zeitschnitte[$ZusatzZS] = '1';
		}
		$i_jhru1++;
	}
	


$JahrSort = substr($_SESSION['Tabellen_Sortierung'],$xstrl=strlen($_SESSION['Tabellen_Sortierung'])-4,4);  

	if($temp_Zeitschnitte[$JahrSort] != '1' and !ctype_alpha($JahrSort))
	{
			$_SESSION['Tabellen_Sortierung'] =NULL;
			$_SESSION['Tabellen_Sortierung_FC'] =NULL;
	}
}

// Testvariable für Laufzeit
$Laufzeit = $Laufzeit.'
Tabelle übergeordn. Regionen-Werte Basiszeitschnitt eingefügt: '.date('H:i:s');





// wieder füllen des SESSION-Array geordnet nach Ausgabe der Abfrage aus v_geometrie_jahr_viewer_postgis
$i_jhru = 0;
while($ZusatzZS = @mysqli_result($Ergebnis_Jahre_uebernahme,$i_jhru,'JAHR') and !$_SESSION['Tabelle']['Indikator_2'])
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
		  ADD `WERT_ABS_".$ZusatzZS."` double default NULL,
		  ADD `WERT_DIFF_".$ZusatzZS."` double default NULL,
		  ADD `WERT_ABS_DIFF_".$ZusatzZS."` double default NULL,
		  ADD `WERT_ABS_TAG_DIFF_".$ZusatzZS."` double default NULL,
		  ADD `WERT_DIFF_AKT_JAHRE_".$ZusatzZS."` double default NULL,
		  ADD `WERT_DIFF_AKT_JAHRE_FEHLERCODE_".$ZusatzZS."` varchar(50) default NULL,
		  ADD `WERT_RELATIV_".$ZusatzZS."` double default NULL,
		  ADD `FEHLERCODE_".$ZusatzZS."` varchar(2) default NULL,
		  ADD `HINWEISCODE_".$ZusatzZS."` varchar(100) default NULL,
		  ADD `AKT_".$ZusatzZS."` double default NULL,
		  ADD `AKT_AUSGABE_".$ZusatzZS."` varchar(100) default NULL,
		  ADD `AKT_DIFF_".$ZusatzZS."` double default NULL,
		  ADD `WERT_KRS_".$ZusatzZS."` double default NULL,
		  ADD `WERT_KRS_DIFF_".$ZusatzZS."` double default NULL,
		  ADD `WERT_KRS_RELATIV_".$ZusatzZS."` double default NULL,
		  ADD `FEHLERCODE_KRS_".$ZusatzZS."` varchar(2) default NULL,
		  ADD `WERT_BLD_".$ZusatzZS."` double default NULL,
		  ADD `WERT_BLD_DIFF_".$ZusatzZS."` double default NULL,
		  ADD `WERT_BLD_RELATIV_".$ZusatzZS."` double default NULL,
		  ADD `FEHLERCODE_BLD_".$ZusatzZS."` varchar(2) default NULL,
		  ADD `WERT_BND_".$ZusatzZS."` double default NULL,
		  ADD `WERT_BND_DIFF_".$ZusatzZS."` double default NULL,
		  ADD `WERT_BND_RELATIV_".$ZusatzZS."` double default NULL,
		  ADD `AKT_BND_".$ZusatzZS."` double default NULL,
		  ADD `AKT_BND_AUSGABE_".$ZusatzZS."` varchar(100) default NULL,
		  ADD `FEHLERCODE_BND_".$ZusatzZS."` varchar(2) default NULL;";
		$Ergebnis_DS_ALT = mysqli_query($Verbindung,$SQL_DS_ALT); 
		


		// Wert für Deutschland für Berechnungen ermitteln (AGS = 99)
		$SQL_Indikatorenwert_D_Z = "SELECT INDIKATORWERT,FEHLERCODE FROM m_indikatorwerte_".$ZusatzZS." WHERE AGS = '99' AND ID_INDIKATOR = '".$_SESSION['Dokument']['Fuellung']['Indikator']."' AND (VGL_AB = '0' OR VGL_AB <= '".$_SESSION['Dokument']['Jahr_Anzeige']."') ORDER BY VGL_AB DESC;"; 
		
		$Ergebnis_Indikatorenwerte_D_Z = mysqli_query($Verbindung,$SQL_Indikatorenwert_D_Z); 
		$Wert_D_Z = @mysqli_result($Ergebnis_Indikatorenwerte_D_Z,0,'INDIKATORWERT');
		$Fehlercode_D_Z = @mysqli_result($Ergebnis_Indikatorenwerte_D_Z,0,'FEHLERCODE');
		
		// Mittl. Grundaktualität für Deutschland für Berechnungen ermitteln (AGS = 99)
		$SQL_Akt_D_Z = "SELECT INDIKATORWERT,FEHLERCODE FROM m_indikatorwerte_".$ZusatzZS." WHERE AGS = '99' AND ID_INDIKATOR = 'Z03AG' AND (VGL_AB = '0' OR VGL_AB <= '".$_SESSION['Dokument']['Jahr_Anzeige']."') ORDER BY VGL_AB DESC;"; 
		$Ergebnis_Akt_D_Z = mysqli_query($Verbindung,$SQL_Akt_D_Z); 
		$Akt_D_Z = @mysqli_result($Ergebnis_Akt_D_Z,0,'INDIKATORWERT');
		
		
		// Akt Ausgabe
		// Jahr
		$SQL_Akt_D_Z = "SELECT INDIKATORWERT,FEHLERCODE FROM m_indikatorwerte_".$ZusatzZS." WHERE AGS = '99' AND ID_INDIKATOR = 'Z00AG' AND (VGL_AB = '0' OR VGL_AB <= '".$_SESSION['Dokument']['Jahr_Anzeige']."') ORDER BY VGL_AB DESC;"; 
		$Ergebnis_Akt_D = mysqli_query($Verbindung,$SQL_Akt_D); 
		$Akt_D_J_Z = @mysqli_result($Ergebnis_Akt_D,0,'INDIKATORWERT');
		// Monat
		$SQL_Akt_D_Z = "SELECT INDIKATORWERT,FEHLERCODE FROM m_indikatorwerte_".$ZusatzZS." WHERE AGS = '99' AND ID_INDIKATOR = 'Z01AG' AND (VGL_AB = '0' OR VGL_AB <= '".$_SESSION['Dokument']['Jahr_Anzeige']."') ORDER BY VGL_AB DESC;";  
		$Ergebnis_Akt_D = mysqli_query($Verbindung,$SQL_Akt_D); 
		$Akt_D_m_Z = @mysqli_result($Ergebnis_Akt_D,0,'INDIKATORWERT');
		
		if($Akt_D_m_Z < 10) $Akt_D_m_Z = "0".$Akt_D_m_Z;
		$Akt_D_Ausgabe_Z = $Akt_D_m_Z." / ".$Akt_D_J_Z;
	
	
		// Füllen der ZusatzspaltenDB mit Werten pro verzeichnetem Datensatz
		// -------------------------------------
		$SQL_DS_vorh = "SELECT * FROM t_temp_tabellentool";
		$Ergebnis_DS_vorh = mysqli_query($Verbindung,$SQL_DS_vorh); 
		$i_ds = 0;
		while($ags = @mysqli_result($Ergebnis_DS_vorh,$i_ds,'AGS'))
		{
			
			// Indikatorwert und Fehlercode aus der Tabelle des entspr. Zusatzzeitschnittes ermitteln
			$SQL_Indikatorenwerte = "SELECT INDIKATORWERT,FEHLERCODE,HINWEISCODE,AGS 
			FROM m_indikatorwerte_".$ZusatzZS." 
			WHERE AGS = '".$ags."' 
			AND ID_INDIKATOR = '".$_SESSION['Dokument']['Fuellung']['Indikator']."'
			AND (VGL_AB = '0' OR VGL_AB <= '".$_SESSION['Dokument']['Jahr_Anzeige']."') ORDER BY VGL_AB DESC;"; // Bester vergleichbarer Datensatz kommt an Stelle "0" und wird als EInziger verwendet
			$Ergebnis_Indikatorenwerte = mysqli_query($Verbindung,$SQL_Indikatorenwerte); 
			
			// fehlende Raumeinheiten erkennen
			if(@mysqli_result($Ergebnis_Indikatorenwerte,0,'AGS'))
			{
				$UPD_Wert = @mysqli_result($Ergebnis_Indikatorenwerte,0,'INDIKATORWERT');
				$UPD_Fehlercode = @mysqli_result($Ergebnis_Indikatorenwerte,0,'FEHLERCODE');

				// Schalter zur Prüfung, ob Hinweiscodes übergeben wurden
				
				
				// ----------------------------------------
				
				// Hinweiscodes	für Vergleichsjahr direkt ermitteln
				
				$SQL_Hinweiscode = "SELECT Code FROM h_".$ZusatzZS."  
									WHERE 
									INDIKATOR = '".$_SESSION['Dokument']['Fuellung']['Indikator']."' 
									AND
									AGS = '".$ags."' 
									GROUP BY Code
									";
				$Ergebnis_Hinweiscode = mysqli_query($Verbindung,$SQL_Hinweiscode); 	
				
				// Auf mehrere Hinweiscodes prüfen
				$UPD_Hinweiscode = ''; // Leeren, für den nächsten AGS für den Fall, dass kein HC übergeben wurde	
				$i_HC=0;
				while($Hinweiscode_Teil = @mysqli_result($Ergebnis_Hinweiscode,$i_HC,'Code'))
				{
					if($i_HC==0)
					{
						//$Hinweiscodes_Dopplung = array(); // Array leeren
						//$Hinweiscodes_Dopplung[$Hinweiscode_Teil] = $Hinweiscode_Teil;
						$UPD_Hinweiscode = $Hinweiscode_Teil;
					}
					else
					{
						//if($Hinweiscodes_Dopplung[$Hinweiscode_Teil] != $Hinweiscode_Teil)
						//{
							$UPD_Hinweiscode = $UPD_Hinweiscode.','.$Hinweiscode_Teil;
						//}
					}

					$Hinweiscodes_Dokument[$Hinweiscode_Teil] = $Hinweiscode_Teil;
					// alt aber in verwendung
					$Hinweiscodes_vorhanden[$Hinweiscode_Teil] = 1;
					//
					$i_HC++;
				}				
					
				
			}
			else
			{
				// Markierung für fehlende Raumeinheit bei Vergleichen
				$UPD_Fehlercode = '7';
	$UPD_Wert ='NULL';
$UPD_Hinweiscode ='NULL';

			}
			
			// ----------------------------------
				// Hinweiscodes	für Basisjahr nachtragen => Erfassung auch der Jahresscheiben dazwischen !!!
				
				// Tabellen ermitteln, die betroffen sind
				$Union = '';
				$SQL_Hinweiscode_Vergleich = "SELECT Code FROM ( ";
				$VerglZeitschnitte = '';
				for($jv = ($ZusatzZS) ; $jv <= $_SESSION['Dokument']['Jahr_Anzeige'] ; $jv++ )
				{
					// Test ob Tabelle vorhanden ist
					$Ergebnis_HinweiscodeTab_vorh = mysqli_query($Verbindung,"SHOW TABLE STATUS LIKE 'h_".$jv."'"); 
					if(@mysqli_result($Ergebnis_HinweiscodeTab_vorh,0,'Name'))
					{
						$SQL_Hinweiscode_Vergleich .= $Union;
						$SQL_Hinweiscode_Vergleich .= "SELECT Code FROM h_".$jv."  
									WHERE 
									INDIKATOR = '".$_SESSION['Dokument']['Fuellung']['Indikator']."' 
									AND
									AGS = '".$ags."'
									";
									
						$Union = " UNION ALL ";			
					}
				}
				$SQL_Hinweiscode_Vergleich .= " ) AS UnionTable GROUP BY Code";
				$Ergebnis_Hinweiscode_Vergleich = mysqli_query($Verbindung,$SQL_Hinweiscode_Vergleich); 	
				
				// Mehrere Hinweiscodes sind abgefangen
				$UPD_Hinweiscode_Vergleich = NULL; // Leeren, für den nächsten AGS für den Fall, dass kein HC übergeben wurde	
				$i_HC=0;
				while($Hinweiscode_Teil_Vergleich = @mysqli_result($Ergebnis_Hinweiscode_Vergleich,$i_HC,'Code'))
				{
					if($i_HC==0)
					{
						$UPD_Hinweiscode_Vergleich = $Hinweiscode_Teil_Vergleich;
					}
					else
					{
						$UPD_Hinweiscode_Vergleich .= ",".$Hinweiscode_Teil_Vergleich;
					}
					$i_HC++;
				}

				// Update des Hinweiscode-Feldes des Basiszeitschnitts
				$SQL_DS_UPD_Vergleich = "UPDATE t_temp_tabellentool 
				SET 
				HINWEISCODE = 	IF('".$UPD_Hinweiscode_Vergleich."'='',NULL,'".$UPD_Hinweiscode_Vergleich."'),
				WHERE AGS = '".$ags."'";
				$Ergebnis_DS_UPD_Vergleich = mysqli_query($Verbindung,$SQL_DS_UPD_Vergleich); 
			
			
			
			
			
			
			
			// Indikatorwert als Absolutwert auslesen
			if($_SESSION['Tabelle']['WERT_ABS'] or $_SESSION['Tabelle']['WERT_ABS_DIFF'])
			{
				$IND_ABS = substr($_SESSION['Dokument']['Fuellung']['Indikator'],0,3).'AG'; // Absolutwert-Indikator-ID zusammenstellen
				$SQL_Indikatorenwerte_AG = "SELECT INDIKATORWERT,FEHLERCODE,HINWEISCODE,AGS 
				FROM m_indikatorwerte_".$ZusatzZS." 
				WHERE AGS = '".$ags."' 
				AND ID_INDIKATOR = '".$IND_ABS."'
				AND (VGL_AB = '0' OR VGL_AB <= '".$_SESSION['Dokument']['Jahr_Anzeige']."') ORDER BY VGL_AB DESC;"; // Bester vergleichbarer Datensatz kommt an Stelle "0" und wird als EInziger verwendet
				$Ergebnis_Indikatorenwerte_AG = mysqli_query($Verbindung,$SQL_Indikatorenwerte_AG); 
				
				// fehlende Raumeinheiten erkennen
				if(@mysqli_result($Ergebnis_Indikatorenwerte,0,'AGS'))
				{
					// $UPD_Wert_ABS = (@mysqli_result($Ergebnis_Indikatorenwerte_AG,0,'INDIKATORWERT')*100); // inkl Umrechnung von km² in ha
					$UPD_Wert_ABS = @mysqli_result($Ergebnis_Indikatorenwerte_AG,0,'INDIKATORWERT'); // in km² 
					
				}
			}
			else
			{
				$UPD_Wert_ABS = '0';
				}
			
			
			
			
			// Nur ausführen, wenn keine Fehlercodes übergeben wurden!
			if(!$UPD_Fehlercode)
			{
				
	
					// Grundaktualität
					$SQL_Grundktualitaet = "SELECT AGS,INDIKATORWERT,ID_INDIKATOR FROM m_indikatorwerte_".$ZusatzZS." WHERE (ID_INDIKATOR = 'Z00AG' or ID_INDIKATOR = 'Z01AG' or ID_INDIKATOR = 'Z03AG') AND AGS = '".$ags."' AND (VGL_AB = '0' OR VGL_AB <= '".$_SESSION['Dokument']['Jahr_Anzeige']."') ORDER BY VGL_AB DESC,ID_INDIKATOR DESC;"; 
					// $SQL_Grundktualitaet = "SELECT AKTUALITAET FROM v_akt_gesamt WHERE JAHR = '".$Grundakt_Verweis."' AND AGS = '".$ags."'"; 
					$Ergebnis_Grundktualitaet = mysqli_query($Verbindung,$SQL_Grundktualitaet);
					
					// Korrektes einsortieren der Werte und zusammenführen für Ausgabe
					$i_akt_zus = 0;
					while($ID_IND_AKT_ZUS = @mysqli_result($Ergebnis_Grundktualitaet,$i_akt_zus,'ID_INDIKATOR'))
					{
						if($ID_IND_AKT_ZUS == 'Z03AG') $Grundakt_Z = @mysqli_result($Ergebnis_Grundktualitaet,$i_akt_zus,'INDIKATORWERT');
						if($ID_IND_AKT_ZUS == 'Z01AG') $Grundakt_AUSGABE_Z_m = @mysqli_result($Ergebnis_Grundktualitaet,$i_akt_zus,'INDIKATORWERT');
						if($ID_IND_AKT_ZUS == 'Z00AG') $Grundakt_AUSGABE_Z_J = @mysqli_result($Ergebnis_Grundktualitaet,$i_akt_zus,'INDIKATORWERT');
						$i_akt_zus++;
					}
					if($Grundakt_AUSGABE_Z_m < 10) $Grundakt_AUSGABE_Z_m = "0".$Grundakt_AUSGABE_Z_m;
					$Grundakt_AUSGABE_Z = $Grundakt_AUSGABE_Z_m." / ".$Grundakt_AUSGABE_Z_J; // Differenz der Grundakt.
					
					// Negative Werte für Prüfer ausgeben (zur Fehlerfindung), an sonsten "0" setzen
					$Grundakt_Diff = @mysqli_result($Ergebnis_DS_vorh,$i_ds,'AKT') - $Grundakt_Z;
					if($_SESSION['Dokument']['ViewBerechtigung'] != "0")
					{
						 if($Grundakt_Diff <= 0)
						{
							$Grundakt_Diff = '0';
						} 						
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
						AND ID_INDIKATOR = '".$_SESSION['Dokument']['Fuellung']['Indikator']."'
						AND (VGL_AB = '0' OR VGL_AB <= '".$_SESSION['Dokument']['Jahr_Anzeige']."') ORDER BY VGL_AB DESC;"; // Bester vergleichbarer Datensatz kommt an Stelle "0" und wird als EInziger verwendet 
						$Ergebnis_Indikatorenwerte_KRS = mysqli_query($Verbindung,$SQL_Indikatorenwerte_KRS); 
								
						$UPD_Wert_KRS = @mysqli_result($Ergebnis_Indikatorenwerte_KRS,0,'INDIKATORWERT');
						$UPD_Fehlercode_KRS = @mysqli_result($Ergebnis_Indikatorenwerte_KRS,0,'FEHLERCODE');
					}
									else
					{
						$UPD_Wert_KRS = NULL;
						$UPD_Fehlercode_KRS = NULL;
						}
				
					// Werte für Bundesländer als übergeordnete Raumeinheit mit anzeigen wenn folgendes ausgewählt
					if($_SESSION['Dokument']['Raumgliederung'] == "gem" 
					or $_SESSION['Dokument']['Raumgliederung'] == "krs" 
					or $_SESSION['Dokument']['Raumgliederung'] == "kfs" 
					or $_SESSION['Dokument']['Raumgliederung'] == "lks"
					or $_SESSION['Dokument']['Raumgliederung'] == "ror")
					{
						// Indikatorwert und Fehlercode
						$SQL_Indikatorenwerte_BLD = "SELECT INDIKATORWERT,FEHLERCODE,AGS  
						FROM m_indikatorwerte_".$ZusatzZS." 
						WHERE AGS = '".substr($ags,0,2)."' 
						AND ID_INDIKATOR = '".$_SESSION['Dokument']['Fuellung']['Indikator']."'
						AND (VGL_AB = '0' OR VGL_AB <= '".$_SESSION['Dokument']['Jahr_Anzeige']."') ORDER BY VGL_AB DESC;"; // Bester vergleichbarer Datensatz kommt an Stelle "0" und wird als EInziger verwendet
						$Ergebnis_Indikatorenwerte_BLD = mysqli_query($Verbindung,$SQL_Indikatorenwerte_BLD); 
								
						$UPD_Wert_BLD = @mysqli_result($Ergebnis_Indikatorenwerte_BLD,0,'INDIKATORWERT');
						$UPD_Fehlercode_BLD = @mysqli_result($Ergebnis_Indikatorenwerte_BLD,0,'FEHLERCODE');
					}
					else
					{
						$UPD_Wert_BLD = '0';
						$UPD_Fehlercode_BLD = '0';
						}
				}
				else
				{
					// bei fehlender Grund-Raumeinheit die Übergeordneten gleichfalls ausblenden
					if($UPD_Fehlercode)
					{
							$UPD_Wert_KRS = NULL;
						$UPD_Wert_BLD = NULL;
						$UPD_Fehlercode_BLD = '8';
						$UPD_Fehlercode_KRS = '8';
					}
						else
					{
						$UPD_Wert_KRS = '0';
						$UPD_Fehlercode_KRS = '0';
						$UPD_Wert_BLD = '0';
						$UPD_Fehlercode_BLD = '0';
						
					}
					
				}
				
				
			
				
				// Update der bestehenden Datensätze, nur wenn nicht leer, Prüfung für php7
			if ($ZusatzZS =='2025' || $ZusatzZS=='2030'){
			
				$SQL_DS_UPD = "UPDATE t_temp_tabellentool 
				SET 
			WERT_".$ZusatzZS." = 	IF( '".$UPD_Wert."'='',NULL,'".$UPD_Wert."')
		
				WHERE AGS = '".$ags."'";
				
			$Ergebnis_DS_UPD = mysqli_query($Verbindung,$SQL_DS_UPD); }
			else
			{
				
					$SQL_DS_UPD = "UPDATE t_temp_tabellentool 
				SET 
						WERT_".$ZusatzZS." = IF( '".$UPD_Wert."'='',NULL,'".$UPD_Wert."'),
					WERT_ABS_".$ZusatzZS." =	IF( '".$UPD_Wert_ABS."'='',NULL,'".$UPD_Wert_ABS."'),
					FEHLERCODE_".$ZusatzZS." =	IF( '".$UPD_Fehlercode."'='',NULL,'".$UPD_Fehlercode."'),
							HINWEISCODE_".$ZusatzZS." = 	IF('".$UPD_Hinweiscode."'='',NULL,'".$UPD_Hinweiscode."'),
				WERT_ABS_".$ZusatzZS." =	IF( '".$UPD_Wert_ABS."'='',NULL,'".$UPD_Wert_ABS."'),	
				FEHLERCODE_".$ZusatzZS." =	IF( '".$UPD_Fehlercode."'='',NULL,'".$UPD_Fehlercode."'),
				HINWEISCODE_".$ZusatzZS." = 	IF('".$UPD_Hinweiscode."'='',NULL,'".$UPD_Hinweiscode."'),
				AKT_".$ZusatzZS." = 	IF('".$Grundakt."'='',NULL,'".$Grundakt."'),
				AKT_AUSGABE_".$ZusatzZS." = 	IF('".$Grundakt_AUSGABE_Z."'='',NULL,'".$Grundakt_AUSGABE_Z."'),
				AKT_DIFF_".$ZusatzZS." = 	IF('".$Grundakt_Diff."'='',NULL,'".$Grundakt_Diff."'),
				WERT_KRS_".$ZusatzZS." = 	IF('".$UPD_Wert_KRS."'='',NULL,'".$UPD_Wert_KRS."'),
				FEHLERCODE_KRS_".$ZusatzZS." = 	IF('".$UPD_Fehlercode_KRS."'='',NULL,'".$UPD_Fehlercode_KRS."'),
				WERT_BLD_".$ZusatzZS." = 	IF('".$UPD_Wert_BLD."'='',NULL,'".$UPD_Wert_BLD."'),
				FEHLERCODE_BLD_".$ZusatzZS." = 	IF('".$UPD_Fehlercode_BLD."'='',NULL,'".$UPD_Fehlercode_BLD."'),
				WERT_BND_".$ZusatzZS." = 	IF('".$Wert_D_Z."'='',NULL,'".$Wert_D_Z."'),
				AKT_BND_".$ZusatzZS." = 	IF('".$Akt_D_Z."'='',NULL,'".$Akt_D_Z."'),
				AKT_BND_AUSGABE_".$ZusatzZS." = 	IF('".$Akt_D_Ausgabe_Z."'='',NULL,'".$Akt_D_Ausgabe_Z."'),
				FEHLERCODE_BND_".$ZusatzZS." = 	IF('".$Fehlercode_D_Z."'='',NULL,'".$Fehlercode_D_Z."')
		
				WHERE AGS = '".$ags."'";
				
				$Ergebnis_DS_UPD = mysqli_query($Verbindung,$SQL_DS_UPD); 	
				
			}	
			
			

			} 
			else
			{
			
				// Update der bestehenden Datensätze bei Fehlercode (außer Fehlercodes nur NULL einfügen!)
				$SQL_DS_UPD = "UPDATE t_temp_tabellentool 
				SET 

				WERT_".$ZusatzZS." = NULL,
				WERT_ABS_".$ZusatzZS." = NULL,
					FEHLERCODE_".$ZusatzZS." =	IF( '".$UPD_Fehlercode."'='',NULL,'".$UPD_Fehlercode."'),
				HINWEISCODE_".$ZusatzZS." = NULL,
				AKT_".$ZusatzZS." = NULL,
				AKT_AUSGABE_".$ZusatzZS." = NULL,
				AKT_DIFF_".$ZusatzZS." = NULL,
				WERT_KRS_".$ZusatzZS." = NULL,
					FEHLERCODE_KRS_".$ZusatzZS." = 	IF('".$UPD_Fehlercode_KRS."'='',NULL,'".$UPD_Fehlercode_KRS."'),
				WERT_BLD_".$ZusatzZS." = NULL,
						FEHLERCODE_BLD_".$ZusatzZS." = 	IF('".$UPD_Fehlercode_BLD."'='',NULL,'".$UPD_Fehlercode_BLD."'),
				WERT_BND_".$ZusatzZS." = NULL,
				AKT_BND_".$ZusatzZS." = NULL,
				AKT_BND_AUSGABE_".$ZusatzZS." = NULL,
				FEHLERCODE_BND_".$ZusatzZS." = 	IF('".$Fehlercode_D_Z."'='',NULL,'".$Fehlercode_D_Z."')

				WHERE AGS = '".$ags."'";
				$Ergebnis_DS_UPD = mysqli_query($Verbindung,$SQL_DS_UPD); 
				
			}
			
			$i_ds++;
		}

		
		
		// ---- Fehlercode bei NULL Werten vergeben -----------
		$SQL_DS_FC = "UPDATE t_temp_tabellentool 
			SET FEHLERCODE_".$ZusatzZS." = '1' 
			WHERE WERT_".$ZusatzZS." IS NULL AND FEHLERCODE_".$ZusatzZS." IS NULL ;";
		$Ergebnis_DS_FC = mysqli_query($Verbindung,$SQL_DS_FC); 

		// ---- Differenzen bilden und einsortieren
		// Für Zusatzzeitschnitte, wenn vorgesehen 
		if($_SESSION['Tabelle']['VERGLEICH'] == '1')
		{

			// Absolute Änderungen berechnen und eintragen (Fehlerbehandlung folgt bei der Ausgabe)
			$SQL_DS_W_DIFF = "UPDATE t_temp_tabellentool 
							SET 
							WERT_DIFF_".$ZusatzZS." = WERT - WERT_".$ZusatzZS.", 
							WERT_ABS_DIFF_".$ZusatzZS." = WERT_ABS - WERT_ABS_".$ZusatzZS.",
							WERT_KRS_DIFF_".$ZusatzZS." = WERT_KRS - WERT_KRS_".$ZusatzZS.",
							WERT_BLD_DIFF_".$ZusatzZS." = WERT_BLD - WERT_BLD_".$ZusatzZS.",
							WERT_BND_DIFF_".$ZusatzZS." = WERT_BND - WERT_BND_".$ZusatzZS." 
							;";	
			$Ergebnis_DS_W_DIFF = mysqli_query($Verbindung,$SQL_DS_W_DIFF); 
			
			// Prüfung der Aktualitäts-Differenzen, da hier auch Fehler entstehen können (gleiche Jahre... Division durch 0 )
			$SQL_DS_W_DIFF_Pruefung = "SELECT * FROM t_temp_tabellentool";
			$Ergebnis_DS_W_DIFF_Pruefung = mysqli_query($Verbindung,$SQL_DS_W_DIFF_Pruefung); 
			$i_ds_diff_pruef = 0;
			while($ags_ZZP = @mysqli_result($Ergebnis_DS_W_DIFF_Pruefung,$i_ds_diff_pruef,'AGS'))
			{
				// Test ob Grundaktualität tatsächlich unterschiedlich ist und die Differenz >= 0,5 Jahre beträgt
					// Check auf AKT_DIFF_".$ZusatzZS < 1 (immer mit min. 1 rechnen! ... sonst skaliert man die Werte willkürlich
					$Akt_diff_check = @mysqli_result($Ergebnis_DS_W_DIFF_Pruefung,$i_ds_diff_pruef,'AKT_DIFF_'.$ZusatzZS);
					
					if($Akt_diff_check >= $Akt_Schwelle) // bei < $Akt_Schwelle wird auf 1 Jahr gerechnet
					{
						$SQL_DS_W_DIFF = "UPDATE t_temp_tabellentool 
										SET 
										WERT_DIFF_AKT_JAHRE_FEHLERCODE_".$ZusatzZS." = '0',
										WERT_DIFF_AKT_JAHRE_".$ZusatzZS." = (WERT - WERT_".$ZusatzZS.")/ABS(AKT_DIFF_".$ZusatzZS."), 
										WERT_ABS_TAG_DIFF_".$ZusatzZS." = ((WERT_ABS - WERT_ABS_".$ZusatzZS.")/ABS((AKT_DIFF_".$ZusatzZS."*365)))*100 
										WHERE ags = '".$ags_ZZP."';";	
									
					}
					 else
					{
						if($_SESSION['Dokument']['ViewBerechtigung'] == "0") // Für Prüfer mit 1 Jahr rechnen
						{
							$SQL_DS_W_DIFF = "UPDATE t_temp_tabellentool 
										SET 
										WERT_DIFF_AKT_JAHRE_FEHLERCODE_".$ZusatzZS." = '0',
										WERT_DIFF_AKT_JAHRE_".$ZusatzZS." = (WERT - WERT_".$ZusatzZS.")/ABS(AKT_DIFF_".$ZusatzZS."), 
										WERT_ABS_TAG_DIFF_".$ZusatzZS." = ((WERT_ABS - WERT_ABS_".$ZusatzZS.")/ABS((AKT_DIFF_".$ZusatzZS."*365)))*100 
										WHERE ags = '".$ags_ZZP."';";	
						}
						else
						{
						
							// Version für Leerstelle bei < 0.8 Jahr (Vergabe des Fehlers v4)
							$SQL_DS_W_DIFF = "UPDATE t_temp_tabellentool 
											SET 
											WERT_DIFF_AKT_JAHRE_FEHLERCODE_".$ZusatzZS." = 'v4',
											WERT_DIFF_AKT_JAHRE_".$ZusatzZS." = NULL, 
											WERT_ABS_TAG_DIFF_".$ZusatzZS." = NULL 
										WHERE ags = '".$ags_ZZP."';"; 
						}
						
					} 
					
	
					
				
				$Ergebnis_DS_W_DIFF_Pruefung_UPD = mysqli_query($Verbindung,$SQL_DS_W_DIFF); 
				$i_ds_diff_pruef++;
			}
		}
	}
			else{
			
						// Update des Wertes für Zusatzzeitschnitts, damit nicht NULL und keine Anzeige
				$SQL_DS_UPD = "UPDATE t_temp_tabellentool 
				SET 
				WERT_".$ZusatzZS." = '".$UPD_Wert."',
		
				WHERE AGS = '".$ags."'";
				
				$Ergebnis_DS_UPD = mysqli_query($Verbindung,$SQL_DS_UPD); 
				
			
			
			}


	// Testvariable für Laufzeit
	$Laufzeit = $Laufzeit.'
	Tabelle Werte Zusatzzeitschnitt '.$ZusatzZS.' eingefügt: '.date('H:i:s');
	
	$i_jhru++;
}



// Tabelle wieder um Deutschlandwerte bereinigen (sonst evtl. Fehlerträchtig)
// --------------------------
// Zeile auslesen
$SQL_99 = "SELECT * FROM t_temp_tabellentool WHERE AGS = '99'"; 			
$Ergebnis_99 = mysqli_query($Verbindung,$SQL_99); 

// Spalten ermitteln und Werte in Array  $Deutschlandwerte[] ablegen
$SQL_Spalten = mysqli_query($Verbindung,"SHOW COLUMNS FROM t_temp_tabellentool");
while($row = mysqli_fetch_assoc($SQL_Spalten)){
    $Deutschlandwerte[$row['Field']] = @mysqli_result($Ergebnis_99,0,$row['Field']);
} 
// Zeile löschen
$SQL_99_DEL = "DELETE FROM t_temp_tabellentool WHERE AGS = '99'"; 			
$Ergebnis_99_DEL = mysqli_query($Verbindung,$SQL_99_DEL); 



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
						COUNT(AGS) AS COUNT
						FROM t_temp_tabellentool 
						WHERE FEHLERCODE < '1'"; 
				
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

// Test für Stichprobenzahl
$SQL_test = "SELECT * FROM t_temp_tabellentool WHERE FEHLERCODE < '1'"; 	
$Ergebnis_test = mysqli_query($Verbindung,$SQL_test); 
$stichprobentestanz=0;
while(@mysqli_result($Ergebnis_test,$stichprobentestanz,'AGS'))
{
	$stichprobentestanz++;
}


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




// Löschung per Hand (User) von bestimmten AGS aus der Tabelle (Außreißer eliminieren)
	
if($_GET['deaktivieren']) $_SESSION['Tabelle']['AGS_IGNORE'][$_GET['deaktivieren']] = $_GET['deaktivieren'];
if($_GET['aktivieren']) $_SESSION['Tabelle']['AGS_IGNORE'][$_GET['aktivieren']] = '';

if(is_array($_SESSION['Tabelle']['AGS_IGNORE']) || is_object($_SESSION['Tabelle']['AGS_IGNORE'])) {
foreach($_SESSION['Tabelle']['AGS_IGNORE'] as $AGS_Ignore)
{
	if($AGS_Ignore)
	{
		// Betroffene Einheiten aus Tebelle entfernen
		$SQL_AGS_Info = "SELECT AGS, NAME FROM t_temp_tabellentool WHERE AGS = '".$AGS_Ignore."'";
		$Ergebnis_AGS_Info = mysqli_query($Verbindung,$SQL_AGS_Info);
		
		$SQL_AGS_DEL = "DELETE FROM t_temp_tabellentool WHERE AGS = '".$AGS_Ignore."'";
		$Ergebnis_AGS_DEL = mysqli_query($Verbindung,$SQL_AGS_DEL);
		
		$SQL_AGS_INS = "INSERT INTO t_temp_tabellentool (AGS, NAME, FEHLERCODE) VALUES ('".$AGS_Ignore."','".@mysqli_result($Ergebnis_AGS_Info,0,'NAME')."','v2')";
		$Ergebnis_AGS_INS = mysqli_query($Verbindung,$SQL_AGS_INS);
	}
}


}



// Testvariable für Laufzeit
$Laufzeit = $Laufzeit.'
Tabelle fehlerbereinigt: '.date('H:i:s');

// Begin des HTML Dokuments

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta charset="UTF-8"/>
<meta http-equiv="expires" content="0">
<meta http-equiv="cache-control" content="no-cache">



<title>Tabelle zur Karte - IÖR Monitor</title>

<!-- JQUERY lib für JQuery Dialog via WFS-->
<script src="../lib/jquery/external/jquery/jquery.js"></script>
<link href="../lib/jquery/jquery-ui.min.css" rel="stylesheet"/>
<script src="../lib/jquery/jquery-ui.js"></script>
<script src="../lib/jquery/jquery-ui.min.js"></script>
<script src="../lib/jquery/jquery.ui.touch-punch.min.js"></script>
<link href="../lib/jquery/jquery-ui.theme.css" rel="stylesheet">

<!--Funktionen für Tabellenansicht-->
<script type="text/javascript" src="../javascript/tabelle.js"></script>

<!--fontawesome für Symbole Buttons-->
<!--<script src="https://use.fontawesome.com/b813c6ed44.js"></script>--->
<link rel="stylesheet" href="../lib/font-awesome/css/font-awesome.min.css">


<!--Bootstrap-->
<link href="../lib/bootstrap/css/bootstrap.min.css" rel="stylesheet">
<script src="../lib/bootstrap/js/bootstrap.min.js"></script>



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
	font-size: 12px;
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
	color: #000;
}
a {
	cursor: default;
	color:#000;
}
a:visited {
	text-decoration: none;
	color: #000;
}
a:hover {
	text-decoration: none;
	color: #444;
}
a:active {
	text-decoration: none;
	color: #444;
}



  /* Icon when the collapsible content is shown */
.btn:after {
   font-family: FontAwesome;
    content: "\f106";
    font-size: 22px;
    margin-left:10px;
  
    
}

/* Icon when the collapsible content is hidden */
.btn.collapsed:after {
 font-family: FontAwesome;
    content: "\f107";
    font-size: 22px;
    margin-left:10px;
}


@media print {
	.nicht_im_print {
	display:none;
	height: 0px;
	width: 0px;
	}
}
@media screen {
	.nur_im_print {	
	display:none;
	height: 0px;
	width: 0px;
	}
}

</style>

     <script type="text/javascript">
     	//Counter XLS Download
     	
         jQuery(document).on('click','button#download',function(){
             jQuery('div#counter').html('Loading...') ;
             var ajax = jQuery.ajax({
                 method : 'get',
                 url : './tabelle_zur_karte_v3.php', // Link to this page
                 data : { 'increase' : '1' }
             }) ;
             ajax.done(function(data){
                 jQuery('div#counter').html(data) ;
                 jQuery('div#counter2').html(data) ;
             }) ;
             ajax.fail(function(data){
                 //alert('ajax fail : url of ajax request is not reachable') ;
                      jQuery('div#counter').html(data) ;
                 jQuery('div#counter2').html(data) ;
             }) ;
         }) ;
     </script>

</head>


<body style="padding-left:35px;" class="body_unterseiten">
<a style="border:0px;" href="http://www.ioer-monitor.de" target="_blank">
<img src="../gfx/kopf_v2_unterseiten.png" width="100%" alt="Kopfgrafik" title="http://www.ioer-monitor.de" class="nur_im_print"/>
<img src="../gfx/kopf_v2_unterseiten.png" width="999" height="119" alt="Kopfgrafik" title="http://www.ioer-monitor.de" class="nicht_im_print"/>
</a>

<div id="headerDivImg">

	<a class="button_tabellentitel nicht_im_print"
 id="b_tab" href="javascript:toggle5('contentDivImg');" style="cursor: pointer;" title="Erweitern der Tabelle um zeitlichen und thematischen Vergleich und Exportoption
"> <i class="fa fa-angle-down" style="font-weight:bold;"></i>    Optionen für Zeit- und Indikatorvergleich wählen </a>

<br />
<br />
<br />


</div>

<!---Beginn Ein- und Ausklappbarer Eingabebereich/Menü-->
<div id="contentDivImg" style="display: none;">
  <!--------------Titel Indikator und Impressum ------------------> 
<div class="nicht_im_print">
	 <!-----Beginn Titel Impressum--->
    <table style="border:0px; margin-bottom: 5px;">
		  <tr>
	      
	      	<h5 style="margin-bottom:3px;"><a name="tabellenkopf" id="tabellenkopf"></a><span class="body_unterseiten"></span><?php echo $_SESSION['Dokument']['Fuellung']['Indikator_Beschreibung']; ?> (<?php echo $_SESSION['Dokument']['Jahr_Anzeige']; ?>)
	        <?php 
				// Für Prüfer noch die ID
	        if($_SESSION['Dokument']['ViewBerechtigung'] == "0") { echo " (".$_SESSION['Dokument']['Fuellung']['Indikator'].")"; }
			
			?>
	        </h5>
	      	<p style=" font-weight:bold; font-size:12px;">IÖR-Monitor©Leibniz-Institut für ökologische Raumentwicklung  <br /> </p> 
	    </tr>
	   </table>
	   <!-----Ende Titel Impressum--->   
</div>
 <!--------------Ende Titel Indikator und Impressum Nicht im Print----------------->


    <table style="border-collapse:collapse; border:none; border:#000000 1px solid;">
    	   <tr>
<!---------------- Beginn Spalte links (zeitschnitte)----------->
        
  <td class="Tabelle_Zeitschnittmenue  nicht_im_print" align="left" valign="top" style="padding-left:20px; padding-right:30px; padding-bottom:10px; border:none; background-color:#EFEFEF; ">
       
    <div class="nicht_im_print">
          	<?php 
		// Hinweis wegen der Deaktivierung bei Auswahl eines 2. Ind. und Ausblendung inaktiver Optionen
			if($_SESSION['Tabelle']['Indikator_2']) 
			{
				?>Bei Auswahl eines 2. Indikators<br />
                sind keine zusätzlichen Zeitschnitte<br />auswählbar.
                <br />
            <!--- Ende Spalte links (Zeitschnitte) bei 2. IND--->         
           <form action="" method="post">
                            
                            
                            
             <div style="padding-left:5px; margin-top:10px; border-left:#8CB91B 3px solid; border-right:#8CB91B 3px solid; padding-right:5px;">   
                                 
    <!--- Beginn Spalte rechts (Zusatz) bei 2. Zeitschnitt --->    
             <td class="Tabelle_Zeitschnittmenue  nicht_im_print" align="left"	
             	valign="top" style="padding-left:10px; padding-right:30px; margin-top:15px; border:none; background-color:#EFEFEF;">
        	 		<div style="margin-top:15px;">  <strong>Weitere Kenngrößen zum Vergleich:</strong> <br/> </div>
                    <?php 
						// Nur anzeigen, wenn Ind. = ...RG
							 if(substr($_SESSION['Dokument']['Fuellung']['Indikator'],3,2)=='RG' && $_SESSION['Dokument']['Fuellung']['Indikator']!=='O01RG' && $_SESSION['Dokument']['Fuellung']['Indikator']!=='O03RG' && $_SESSION['Dokument']['Fuellung']['Indikator']!=='O04RG')
									{
										?>   
	                       
	                     <input style="" class="nicht_im_print" type="checkbox" name="WERT_ABS" id="WERT_ABS" <?php 
	                        if($_SESSION['Tabelle']['WERT_ABS']) echo "checked";
	                  ?> />
	                                Absolute Indikatorwerte<br />
	                   		<?php 
									}
									?>
                
            			  <input style="" class="nicht_im_print" type="checkbox" name="FLAECHE" id="FLAECHE" <?php 
                     if($_SESSION['Tabelle']['FLAECHE']) echo "checked";
                     ?> />
                      Gebietsfl&auml;che<br />
                     
                     
                      
                    	<!-- Nur anzeigen, wenn Ind. nicht stadtteile-->
                    	
                    	  <?php 
                    	  if($_SESSION['Dokument']['Raumgliederung']!=='stt'){?>
                    		   <input style="" class="nicht_im_print" type="checkbox" name="EWZ" id="EWZ" <?php 
                     if($_SESSION['Tabelle']['EWZ'] && $_SESSION['Dokument']['Raumgliederung']!=='stt') echo "checked";
                     ?> />
                      Einwohnerzahl<br />
					  <?php }?>
					  
									  <!--für ÖSL div hide - Rubel --> 
                     <input id="r1" <?php if(empty($Wert_D)||!isset($Wert_D)) echo 'style="display: none;"'; ?> style=" <?php if($_SESSION['Tabelle']['UERE_GRAU']) echo 'color:#999999;' ?> " class="nicht_im_print" type="checkbox" name="UERE" id="UERE" <?php 
                     if($_SESSION['Tabelle']['UERE']) echo "checked";
                     if($_SESSION['Tabelle']['UERE_GRAU']) echo "checked";
                     if($_SESSION['Tabelle']['Indikator_2']) echo "disabled"; 
                     ?> />
                      <a id="r1" <?php if(empty($Wert_D)||!isset($Wert_D)) echo 'style="display: none;"'; ?> >
										 	 &Uuml;bergeordnete Raumeinheiten <br />
										  </a>
                      <?php   
                    // nur sinnvolles Einblenden von Schaltern:
                    if($_SESSION['Dokument']['Raumgliederung'] == "gem")
                    {
                        ?>
                  <input id="r2" <?php if(empty($Wert_D)||!isset($Wert_D)) echo 'style="display: none;"'; ?> style="margin-left:20px;" class="nicht_im_print" type="checkbox" name="UERE_KRS" id="UERE_KRS" <?php 
                        if($_SESSION['Tabelle']['UERE_KRS']) echo "checked";
                        if($_SESSION['Tabelle']['Indikator_2']) echo "disabled"; 
                        ?> /> 
										<a id="r2" <?php if(empty($Wert_D)||!isset($Wert_D)) echo 'style="display: none;"'; ?> >
											Kreise<br />
										</a>
                      <?php 
                    }
                    if($_SESSION['Dokument']['Raumgliederung'] == "gem" 
                    or $_SESSION['Dokument']['Raumgliederung'] == "krs" 
                    or $_SESSION['Dokument']['Raumgliederung'] == "kfs" 
                    or $_SESSION['Dokument']['Raumgliederung'] == "lks")
                    {
                     ?>
                  <input id="r3" <?php if(empty($Wert_D)||!isset($Wert_D)) echo 'style="display: none;"'; ?> style="margin-left:20px;" class="nicht_im_print" type="checkbox" name="UERE_BLD" id="UERE_BLD" <?php 
                     if($_SESSION['Tabelle']['UERE_BLD']) echo "checked";
                     if($_SESSION['Tabelle']['Indikator_2']) echo "disabled"; 
                     ?> /> 
										 <a id="r3" <?php if(empty($Wert_D)||!isset($Wert_D)) echo 'style="display: none;"'; ?> >
											 Bundesl&auml;nder<br />
										 </a>
                      <?php 
                    }
                    ?>
                  <input id="r4" <?php if(empty($Wert_D)||!isset($Wert_D)) echo 'style="display: none;"'; ?> style="margin-left:20px;" class="nicht_im_print" type="checkbox" name="UERE_BND" id="UERE_BND" <?php 
                    if($_SESSION['Tabelle']['UERE_BND']) echo "checked";
                    if($_SESSION['Tabelle']['Indikator_2']) echo "disabled"; 
                    ?> /> 
										<a id="r4" <?php if(empty($Wert_D)||!isset($Wert_D)) echo 'style="display: none;"'; ?> >
											Bundesrepublik<br />
										</a>
                      <input class="nicht_im_print" type="checkbox" name="AKTUALITAET" id="AKTUALITAET" <?php 
                    if($_SESSION['Tabelle']['AKTUALITAET']) echo "checked";  
                  
                    if($_SESSION['Tabelle']['Indikator_2']) echo "disabled"; 
                    ?>  
                        <?php if($MITTLERE_AKTUALITAET_IGNORE) { echo "disabled"; } ?> /> 
                      Mittlere Grundaktualit&auml;t <a href="http://www.ioer-monitor.de/index.php?id=88" target="_blank"><img src="../gfx/icons/klein/document_search.png" width="14" height="14" alt="Glossar" title="Zur Begriffserklärung im Glossar"style="cursor: pointer;" /></a><br />
                  
                  


             <input name="Zeitschnitt_UERE_Formular" type="hidden" value="1" />
       
              <input  id="submit" name="submit" type="submit" class= "button_gruen_abschicken_gross" style="cursor: pointer; margin-top:20px;  " title="Gewählte Tabellenspalten anzeigen" value= "Tabelle aktualisieren" onclick="submit();" />
     
    </td>
  </div>
        </form>
          
                <?php 
			}///Ende Menü links / Ausblendung wenn 2. Indi gewählt
			else
			{//wenn kein 2. IND gewählt
				?>
                
                    <form action="" method="post">
                            
                            
                            
                    <div id="zusatz_ZS_links" style="padding-left:5px; margin-top:10px; border-left:#8CB91B 3px solid; border-right:#8CB91B 3px solid; padding-right:5px;">   
                    <?php   
                    // Check auf aktivierte Zusatzzeitschnitte in Menü
                    if(is_array($Zeitschnitte_vorh))
                    {		
                    	 echo "<span style='font-weight:bold; margin-top:8px;'>Zeitschnitte anfügen: <br/>	</span>";	
                    							 
                        foreach($Zeitschnitte_vorh as $Zeitschnitt_Zusatz)
                        {
                            // gewählten Dokumentenzeitschnitt nicht mit aufführen und nur zurückliegende Zeitschnitte für Vergleich zulassen
                            if($Zeitschnitt_Zusatz != $_SESSION['Dokument']['Jahr_Anzeige'] and $_SESSION['Dokument']['Jahr_Anzeige'] > $Zeitschnitt_Zusatz)
                            {
                                ?>
                                    <input  onclick="submit();" 
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
                                <span style="font-weight:bold;"><?php echo $Zeitschnitt_Zusatz; ?></span> <br />
                                
                                <?php 
                            }
                            // Vermerken, wenn ein neuerer Zeitschnitt nicht zum Vergleich angeboten wurde um einen Hinweis auszugeben
                            if($_SESSION['Dokument']['Jahr_Anzeige'] < $Zeitschnitt_Zusatz)
                            {
                                $ZS_Hinweis = '<span style="color:#990000;">Hinweis: </span>Für Vergleiche können nur <br />frühere Zeitschnitte angezeigt werden.<br /><br />';
                            }				
                            
                        }
                        echo $ZS_Hinweis;
                    }
                    
         //--------------------------------------------------------------------------------------------------------------------------      	          
                    // Checkbox "Differenzen anzeigen" nur anzeigen, wenn zus. Zeitschnitt wirklich verfügbar ist
                    if($Zus_ZS_vorh)
                    {?>              
                    	 <input class="nicht_im_print" onclick="submit();" type="checkbox" name="VERGLEICH" id="VERGLEICH" <?php if($_SESSION['Tabelle']['VERGLEICH'] and $_SESSION['Tabelle']['VERGLEICH']!='0') echo "checked"; ?>  /> 
			                 <span style="font-weight:bold;">Differenzen anzeigen</span><br />
                   	 <?php 
                    }
                   
          
                    // nur sinnvolles Einblenden von Schaltern:
                    if($_SESSION['Dokument']['Raumgliederung'] == "gem")
                    {
                        ?>
                      <input  value="1" class="nicht_im_print" name="GEMEINDEFREI" type="checkbox" <?php if($_SESSION['Tabelle']['GEMEINDEFREI']) echo "checked"; ?>/>
                      mit gemeindefreien  Gebieten<br />
                      <?php 
                    }
                    ?>
                    </div>
                    
        <!--Absatz mit Hinweis; eingeblendet, wenn Trendberechtigung vorliegt und ein Trendwert gewählt wurde-->
          <div id="zusatz_ZS_links_Hinweis" style="display:none; margin-top:19px; padding-left:5px; border-left:#8CB91B 3px solid; border-right:#8CB91B 3px solid; padding-right:5px;">   
		        <span style="color:#990000;">Hinweis: </span>Anzeige von zusätzlichen <br />Zeitschnitten und weiteren <br /> Kenngrößen nur bei Deaktivierung<br /> der Trendfortschreibung.
<br />
		         
		      </div>
                 
    </td>      
                       
                       
    <td class="Tabelle_Zeitschnittmenue  nicht_im_print" align="left"
            valign="top" style="padding-left:10px; padding-right:30px; margin-top:15px; border:none; background-color:#EFEFEF;">
            <div id="weitere_kenngr">  
         <div style="margin-top:15px;">  <strong>Weitere Kenngrößen zum Vergleich:</strong> <br/> </div>
                    <?php 
				// Nur anzeigen, wenn Ind. = ...RG
					if(substr($_SESSION['Dokument']['Fuellung']['Indikator'],3,2)=='RG' && $_SESSION['Dokument']['Fuellung']['Indikator']!=='O01RG' && $_SESSION['Dokument']['Fuellung']['Indikator']!=='O03RG' && $_SESSION['Dokument']['Fuellung']['Indikator']!=='O04RG')
					{
						?>     
                       
                       <input style="" class="nicht_im_print" type="checkbox" name="WERT_ABS" id="WERT_ABS" <?php 
                              if($_SESSION['Tabelle']['WERT_ABS']) echo "checked";
                             ?> />
                                Absolute Indikatorwerte<br />
                   		<?php 
					}
					?>
                
              <input style="" class="nicht_im_print" type="checkbox" name="FLAECHE" id="FLAECHE" <?php 
                     if($_SESSION['Tabelle']['FLAECHE']) echo "checked";
                     ?> />
                      Gebietsfl&auml;che<br />
                        <?php	  if($_SESSION['Dokument']['Raumgliederung']!=='stt'){?>
                       <input style="" class="nicht_im_print" type="checkbox" name="EWZ" id="EWZ" <?php 
                     if($_SESSION['Tabelle']['EWZ'] && $_SESSION['Dokument']['Raumgliederung']!=='stt') echo "checked";
                     ?> />
                      Einwohnerzahl<br />
				<?php } ?>
					  
					  <!--für ÖSL div hide - Rubel --> 
                     <input id="r1" <?php if(empty($Wert_D)||!isset($Wert_D)) echo 'style="display: none;"'; ?> style=" <?php if($_SESSION['Tabelle']['UERE_GRAU']) echo 'color:#999999;' ?> " class="nicht_im_print" type="checkbox" name="UERE" id="UERE" <?php 
                     if($_SESSION['Tabelle']['UERE']) echo "checked";
                     if($_SESSION['Tabelle']['UERE_GRAU']) echo "checked";
                     if($_SESSION['Tabelle']['Indikator_2']) echo "disabled"; 
                     ?> />
                      <a id="r1" <?php if(empty($Wert_D)||!isset($Wert_D)) echo 'style="display: none;"'; ?> >
					  &Uuml;bergeordnete Raumeinheiten <br />
					  </a>
                      <?php   
                    // nur sinnvolles Einblenden von Schaltern:
                    if($_SESSION['Dokument']['Raumgliederung'] == "gem")
                    {
                        ?>
                  <input id="r2" <?php if(empty($Wert_D)||!isset($Wert_D)) echo 'style="display: none;"'; ?> style="margin-left:20px;" class="nicht_im_print" type="checkbox" name="UERE_KRS" id="UERE_KRS" <?php 
                        if($_SESSION['Tabelle']['UERE_KRS']) echo "checked";
                        if($_SESSION['Tabelle']['Indikator_2']) echo "disabled"; 
                        ?> /> 
						<a id="r2" <?php if(empty($Wert_D)||!isset($Wert_D)) echo 'style="display: none;"'; ?> >
						Kreise<br />
						</a>
                      <?php 
                    }
                    if($_SESSION['Dokument']['Raumgliederung'] == "gem" 
                    or $_SESSION['Dokument']['Raumgliederung'] == "krs" 
                    or $_SESSION['Dokument']['Raumgliederung'] == "kfs" 
                    or $_SESSION['Dokument']['Raumgliederung'] == "lks")
                    {
                     ?>
                  <input id="r3" <?php if(empty($Wert_D)||!isset($Wert_D)) echo 'style="display: none;"'; ?> style="margin-left:20px;" class="nicht_im_print" type="checkbox" name="UERE_BLD" id="UERE_BLD" <?php 
                     if($_SESSION['Tabelle']['UERE_BLD']) echo "checked";
                     if($_SESSION['Tabelle']['Indikator_2']) echo "disabled"; 
                     ?> /> 
					 <a id="r3" <?php if(empty($Wert_D)||!isset($Wert_D)) echo 'style="display: none;"'; ?> >
					 Bundesl&auml;nder<br />
					 </a>
                      <?php 
                    }
                    ?>
                  <input id="r4" <?php if(empty($Wert_D)||!isset($Wert_D)) echo 'style="display: none;"'; ?> style="margin-left:20px;" class="nicht_im_print" type="checkbox" name="UERE_BND" id="UERE_BND" <?php 
                    if($_SESSION['Tabelle']['UERE_BND']) echo "checked";
                    if($_SESSION['Tabelle']['Indikator_2']) echo "disabled"; 
                    ?> /> 
					<a id="r4" <?php if(empty($Wert_D)||!isset($Wert_D)) echo 'style="display: none;"'; ?> >
					Bundesrepublik<br />
					</a>
                      <input class="nicht_im_print" type="checkbox" name="AKTUALITAET" id="AKTUALITAET" <?php 
                    if($_SESSION['Tabelle']['AKTUALITAET']) echo "checked";  
                    if($_SESSION['Tabelle']['Indikator_2']) echo "disabled"; 
                     
                    ?>  
                        <?php if($MITTLERE_AKTUALITAET_IGNORE) { echo "disabled"; } ?> /> 
                      Mittlere Grundaktualit&auml;t <a href="http://www.ioer-monitor.de/index.php?id=88" target="_blank"><img src="../gfx/icons/klein/document_search.png" width="14" height="14" alt="Glossar" title="Zur Begriffserklärung im Glossar"style="cursor: pointer;" /></a><br />
                  
         <br/>         
         </div> <!---ende weitere Kenngrößen, wenn kein 2. IND-->              
           
          
                               
                               
  <!-------------------------------------Beginn Trendzeitschnitte in Menü ----------------------------->     
                 
	      <form action="" method="post">                        
                                                
         <div style="padding-left:5px;  padding-right:5px;">  
			 <?php                
         //Umkehren der Reihenfolge der Zeitschnitte für Anzeige
	        $Zeitschnitt_Zusatz_Trend_array = $Zeitschnitte_vorh;  
	        
	        if(is_array($Zeitschnitt_Zusatz_Trend_array) || is_object($Zeitschnitt_Zusatz_Trend_array)) {       	
	        $Zeitschnitt_Zusatz_Trend_array_reverse = array_reverse($Zeitschnitt_Zusatz_Trend_array); 
	      }
	              
	       //Hinweistexte sammeln
	        $Trend_ZS_Hinweis = '<span style="color:#990000;">Hinweis: </span>Anzeige von Trendwerten nur bei <br /> Deaktivierung zusätzlicher Zeitschnitte.<br />';
	        $Trend_Zeit_Hinweis = '<span style="color:#990000;">Hinweis: </span>Anzeige von Trendwerten nur bei <br /> Auswahl des aktuellsten Zeitschnittes.<br />';
	        $Trend_Ind_Hinweis = '<span style="color:#990000;">Hinweis: </span>Anzeige von Trendwerten nur für <br /> ausgewählte Indikatoren. <br />'; 
          $Trend_RGl_Hinweis = '<span style="color:#990000;">Hinweis: </span>Anzeige von Trendwerten nur für <br /> Raumgliederungen größer als Gemeinden.<br />';                       
          //Überschriften Trendmenü
          $Trend_Titel = '<span style="font-weight:bold; margin-top:8px;">Lineare Trendfortschreibung<a href="http://www.ioer-monitor.de/glossar/t/trendfortschreibung" target="_blank"><img src="../gfx/icons/klein/document_search.png" width="14" height="14" title="Zur Erklärung im Glossar" alt="Glossar" style="cursor: pointer;"/></a> für: <br/>	</span>';          
          $Trend_Titel_grau = '<span style="font-weight:bold; margin-top:8px; color:#777777;">Lineare Trendfortschreibung<a href="http://www.ioer-monitor.de/glossar/t/trendfortschreibung" target="_blank"><img src="../gfx/icons/klein/document_search.png" width="14" height="14" title="Zur Erklärung im Glossar" alt="Glossar" style="cursor: pointer;"/></a> für: <br/>	</span>';
                 
         //Check ob Indikator zur Anzeige von Trends berechtigt
	      if ($_SESSION['Tabelle']['Trend_Indikator']== '1')
	      {                    
            //Trendfortschreibung nur für aktuellsten Zeitschnit anbieten     
            if ($_SESSION['Tabelle']['Trend_Jahr']== '1')
            {   
                // Check auf angegebene Zusatzzeitschnitte in Menü; darin müssen auch 2025 oder 2030 vorkommen; prüfen ob raumglied größer gemeinde
	              if(is_array($Zeitschnitte_vorh) && (in_array("2025", $Zeitschnitte_vorh) or in_array("2030", $Zeitschnitte_vorh)))
	              {		                  	                 
	               if($Zus_ZS_vorh == 1 ||  $_SESSION['Tabelle']['Trend_Raumgliederung']!= '1' ){  echo $Trend_Titel_grau;	}
	               else{  echo $Trend_Titel;	}	             	
	             	
                 foreach($Zeitschnitt_Zusatz_Trend_array_reverse as $Zeitschnitt_Zusatz_Trend) 
                   {
                       // gewählten Dokumentenzeitschnitt nicht mit aufführen und nur zurückliegende Zeitschnitte für Vergleich zulassen
                       if($Zeitschnitt_Zusatz_Trend  > '2024')
                          {
                           ?>                           
                             <input onclick="submit();" 
                                name="ZS_<?php echo $Zeitschnitt_Zusatz_Trend; ?>" 
                                type="checkbox" 
                                id="trend_Zusatz_ZS_<?php echo $Zeitschnitt_Zusatz_Trend; ?>" 
                                value="<?php echo $Zeitschnitt_Zusatz_Trend; ?>" 
                                <?php                                 
                                 if($Zus_ZS_vorh == 1 ||  $_SESSION['Tabelle']['Trend_Raumgliederung']!= '1'   ){echo 'disabled';}
                                
                                if($_SESSION['Tabelle']['Zeitschnitt_Zusatz'][$Zeitschnitt_Zusatz_Trend]&& $_SESSION['Tabelle']['Trend_Berechtigung'] == '1') 
                                 {
                                    echo "checked";
                                     $Trend_ZS_vorh = '1';  // Variable setzen, dass zus. Zeitschnitt vorhanden ist                                     
                     						 }  ?>
                             />		 
                             <span style="font-weight:bold; <?php  if($Zus_ZS_vorh == 1 || $_SESSION['Tabelle']['Trend_Berechtigung'] != '1' ){echo 'color:#777777;';}?>  "><?php echo $Zeitschnitt_Zusatz_Trend; ?></span> <br />
                        		<?php                                                   
                          }   	
                   }       
                   if($Zus_ZS_vorh == 1){echo $Trend_ZS_Hinweis;} 
                   if ($_SESSION['Tabelle']['Trend_Raumgliederung'] != '1') {echo $Trend_RGl_Hinweis;}                              
                }
            } 
				      //Wenn nicht aktuellester Zeitschnitt gewählt, dann Hinweis und Deaktivierung der Option  
				    else
				    {     
                 // Check auf aufgelistete Zusatzzeitschnitte in Menü; darin müssen auch 2025 oder 2030 vorkommen; nur wenn raumglied größer gemeinde&&  ( $_SESSION['Tabelle']['Trend_Raumgliederung']== '1'  ) 
	              if(is_array($Zeitschnitte_vorh) && (in_array("2025", $Zeitschnitte_vorh) or in_array("2030", $Zeitschnitte_vorh)))
	              {		
	                 echo $Trend_Titel_grau;		          	             	
	              	foreach($Zeitschnitt_Zusatz_Trend_array_reverse as $Zeitschnitt_Zusatz_Trend)  //$Zeitschnitte_vorh as 
                   {
                       // Trendwert Checkboxes zeichnen aber ausgrauen und disabled, weil Zeitschnitt nicht aktuellster
                       if($Zeitschnitt_Zusatz_Trend  > '2024')
                          {  ?>                           
                             <input  
                                name="ZS_<?php echo $Zeitschnitt_Zusatz_Trend; ?>" 
                                type="checkbox"
                                id="trend_Zusatz_ZS_<?php echo $Zeitschnitt_Zusatz_Trend; ?>" 
                                value=""
                                disabled
                               />		 
                               <span style="font-weight:bold; color:#777777;"><?php echo $Zeitschnitt_Zusatz_Trend; ?></span> <br />
                           <?php 
                          }   	
                   }                      
                      echo $Trend_Zeit_Hinweis;                   
                }
            }  
        }   
        else //wenn Indikator nicht für Trend freigegeben, ausgrauen und Hinweis
        {
        
        	 echo $Trend_Titel_grau;		            	             	
	             	foreach($Zeitschnitt_Zusatz_Trend_array_reverse as $Zeitschnitt_Zusatz_Trend)  //$Zeitschnitte_vorh as 
                   {
                       // Trendwert Checkboxes zeichnen aber ausgrauen und disabled, weil Indikator für Trend nicht nicht aktuellster
                       if($Zeitschnitt_Zusatz_Trend  > '2024')
                          {  ?>                           
                             <input  
                                name="ZS_<?php echo $Zeitschnitt_Zusatz_Trend; ?>" 
                                type="checkbox"
                                id="trend_Zusatz_ZS_<?php echo $Zeitschnitt_Zusatz_Trend; ?>" 
                                value=""
                                disabled
                               />		 
                               <span style="font-weight:bold; color:#777777;"><?php echo $Zeitschnitt_Zusatz_Trend; ?></span> <br />
                           <?php 
                          }   	
                   }  
                   //Hinweis mit Link zum Glossareintrag Trend     
                    echo $Trend_Ind_Hinweis;  
                        
        } 
             ?>    
        </div>   	
     <!-------------------------------------Ende Trendzeitschnitte in Menü ----------------------------->
 
    <input name="Zeitschnitt_UERE_Formular" type="hidden" value="1" />
    <input  id="submit" name="submit" type="submit" class= "button_gruen_abschicken_gross" style="cursor: pointer; margin-top:20px;" title="Gewählte Tabellenspalten anzeigen" value= "Tabelle aktualisieren" onclick="submit();" />   
  </form>
    </td>        
       <?php 
 
 }			//ende else wenn kein 2. Indi gewählt
		  ?>     
            
          
          
          </div>
       </div> 
<!--- Ende Spalte links--->
    	
    	
 	<!--- Beginn Spalte rechts--->   	
  <td valign="top" style="padding-top:20px; padding-right:20px; width:380px; border: #000000 1px solid;">
  	
  	
    	  <!--------------Definition der Buttons in einer Tabellenzeile------------------>
	  <table class="nicht_im_print" style="border:0px;  margin-bottom: 0px; margin-left: 65px;margin-top:1px;" >
	     <tr> 
	     	
	     	
	         <!--Button1 Indikatorkennblatt-->   
	                <td style="border:none; border-right: 1px solid #aaaaaa; padding-top: 5px;">
	                    <a href="http://www.ioer-monitor.de/index.php?id=44"class="nicht_im_print"  title="Erläuterungen zum Indikator und Erhebungsmethodik" target="_blank"><img style="margin-bottom:1px; cursor: pointer;" src="../icons_viewer/indikatorblat.png" alt="Indikatorkennblatt" /></a>
	                </td> 
	         
	          				
					<!--Button2 Statistische Kenngrößen und Histogramm-->   
					<td style="border:none; border-right: 1px solid #aaaaaa; padding-top: 5px;">
					 <form action="tabelleninformationen.php" method="post" class="nicht_im_print" name="histform" target="_blank">															
						     <a onclick="histform.submit();" href="#" title="Statistische Kenngrößen und Histogramm des Indikators" ><img style="margin-bottom:1px; cursor: pointer;"  src="../icons_viewer/stat.png" alt="Statistische Kenngrößen und Histogramm" /></a>
	                  <input name="W_Min" type="hidden" value="<?php echo $Min_Ausgabe; ?>" />
	                                  <input name="Standardabweichung" type="hidden" value="<?php echo round($Standardabweichung,$_SESSION['Dokument']['Fuellung']['Rundung']); ?>" />
	                                  <input name="Median_1_Wert" type="hidden" value="<?php echo round($Median_Wert,$_SESSION['Dokument']['Fuellung']['Rundung']); ?>" />
	                                  <input name="Median_2_Wert" type="hidden" value="" />
	                                  <input name="Median_1_Name" type="hidden" value="<?php echo round($Median_Name,$_SESSION['Dokument']['Fuellung']['Rundung']); ?>" />
	                                  <input name="Median_2_Name" type="hidden" value="" />
	                                  <input name="n" type="hidden" value="<?php echo $n_Stichproben; ?>" />
	                                  <input name="AMittel" type="hidden" value="<?php echo round($Ar_Mittel,$_SESSION['Dokument']['Fuellung']['Rundung']); ?>" /> 
						
	          </form>
	        </td>       
	                 
	           	                        	
	                        	
	            	<!--Button3 Druck-->	
	              <td style="border:none; border-right: 1px solid #aaaaaa; padding-top: 5px;">
	                   <a onClick="javascript:window.print()" class="nicht_im_print" title="Tabelle drucken"><img style="margin-bottom:1px; cursor: pointer;" src="../icons_viewer/print_dark.png" alt="Drucken" /></a>
	              </td>
	                        
	        
	        
	        
			
	             <!--Button4 Tabellendatei für Download--> 
							 <td style="border:none;  padding-top: 0px;">
							 		<!--Popup nach erfolgter csv Berechnung Für Chrome extra Anweisung Datei nicht in Browser zu öffnen -nur in Kombi mit a href möglich-->
									<div  class="nicht_im_print"id="export" style="display:none; text-align:center;">
										 <?php 										
											if(strpos($_SERVER['HTTP_USER_AGENT'],"Chrome")==true)
											{?>								
												<p>Die Tabelle wurde erfolgreich exportiert. </br></br> <a class="button_grau_abschicken" style="padding:5px; background-color:#EEEEEE;" href="<?php echo $tmpfname;?>" download>Download</a> </p>
												<?php												
											}								
											else
											{		?>
												<p>Die Tabelle wurde erfolgreich exportiert. </br></br> 
													<button id="download"  onclick="location.href='<?php echo $tmpfname; ?>'"	<?php		if ( $user_kennung != 'ioer'){echo 'onclick="window.open(this.href);return false";';}?>;">Download</button>
												</p>
												<div style=" <?php if($_SESSION['Dokument']['ViewBerechtigung'] != "0")
												{	echo 'display:none;';   }?>" id="counter" >Downloads: </br><?php echo $counter; ?>
										  	</div>										
													<?php
												}?>													
									</div>
									<!--Ende Download Popup-->
									
									
									<!--Buttons Download in Menü-->
									 <?php 
									//Button nach Download (roter Pfeilbutton) 
										if($_POST['csv'])
										{													// Download mit ausweichen auf download_csv.php, um den Header so zu setzen, dass die Datei nicht im Browser geöffnet wird; Chrome benötigt extra Aufruf um Dten nicht in Browserfenster auszugeben
												?>
												<a href="<?php echo $tmpfname;?>" 
													<?php if(strpos($_SERVER['HTTP_USER_AGENT'],"Chrome")==true){echo "download";}?>><img style="margin-bottom:1px;" src="../icons_viewer/down_xls.png" title="Die .csv Datei wurde erfolgreich erstellt. Für Download hier klicken." alt="download" onload="export_dialog()" /></a>
												<?php												
										}
									//Button vor Download (Standard) 
										else
										{	?>									
								
											<form class="nicht_im_print" method="post" action="">										
														
											<input name="csv_button"  title="Tabellendatei für Download erstellen (.csv)" type="image" src="../gfx/csv-down.png" alt="Submit" /> <!--nicht angeklickt-->
											<input name="csv" type="hidden" value="erstellen" />
											
											</form>	
									<?php 
										}
									?>	
	             </td> 
	             
	             <!-- Ausgabe Download Anzahl für Prüfer-->
	             <td style="border:none; padding-top: 5px;">
								<div style=" <?php if($_SESSION['Dokument']['ViewBerechtigung'] != "0")
										{	echo 'display:none;';   }?>" id="counter2" >Downloads:</br>(seit 15.08.17) </br><?php echo $counter; ?>
								</div>
							 </td>           							
	            	           							
				</tr>
   </table>
       <!--------------Ende Definition der Buttons in Tabellenzeile------------------>
       
       
        <hr style="border: none; color: #000000; background-color: #000000; height: 1px; width:300px;"/>  
						  
							          
					<!-----Beginn rechte Spalte mittleres Feld--->
 
        <div style="display:block; width:150px; margin-left:30px; float:left;">Einbezogenes Gebiet:</div>
		    <div style="display:block; white-space: normal; padding-left:150px;"><?php echo $_SESSION['Datenbestand_Ausgabe']; ?></div>
        <span style="margin-left:30px; clear:both;">Raumgliederung:</span>
        <div style="margin-left:180px; margin-top:-15px; clear:both;">
          <?php 
			// Stringverarbeitung für Sondergebiete (Löschen von Zus. Textbausteinen)
			if($_SESSION['Dokument']['Raumgliederung_Ausgabe'][0] == '*') 
			{
				$_Raumgliederung_Legendentext = substr($_SESSION['Dokument']['Raumgliederung_Ausgabe'],2);
			}
			else
			{
				$_Raumgliederung_Legendentext = $_SESSION['Dokument']['Raumgliederung_Ausgabe'];
			}
		
			// Auswahlfeld nur bei Kreis/Landkreis/Kreisfreie Stadt anbieten, sonst folgt ein Benennungschaos
			if($_SESSION['Dokument']['Raumgliederung'] == "krs" or $_SESSION['Dokument']['Raumgliederung'] == "lks" or $_SESSION['Dokument']['Raumgliederung'] == "kfs")
			{
				?>
				<form name="formKRS" id="formKRS" method="post" action="">
									<input style="margin-left:0px;" class="nicht_im_print" onclick="submit();" type="radio" name="KRS_LKS_KFS" id="KRS_LKS_KFS"  value="krs"
								<?php if($_SESSION['Dokument']['Raumgliederung'] == "krs") echo "checked";?> />
             			<span class="<?php if($_SESSION['Dokument']['Raumgliederung'] != "krs") echo "nicht_im_print";?>">Kreise<br /></span>
               		
               		<input style="margin-left:0px;" class="nicht_im_print" onclick="submit();" type="radio" name="KRS_LKS_KFS" id="KRS_LKS_KFS"  value="lks"
								<?php if($_SESSION['Dokument']['Raumgliederung'] == "lks") echo "checked";?> />
             			<span class="nicht_im_print">nur </span><span class="<?php if($_SESSION['Dokument']['Raumgliederung'] != "lks") echo "nicht_im_print";?>">Landkreise<br /></span>
                	
                	<input style="margin-left:0px;" class="nicht_im_print" onclick="submit();" type="radio" name="KRS_LKS_KFS" id="KRS_LKS_KFS"  value="kfs"
								<?php if($_SESSION['Dokument']['Raumgliederung'] == "kfs") echo "checked";?> />
             			<span class="nicht_im_print">nur </span><span class="<?php if($_SESSION['Dokument']['Raumgliederung'] != "kfs") echo "nicht_im_print";?>">kreisfreie Städte<br /></span>
	  			</form>
				<?php 
			}
			else
			{
				// einfache Ausgabe bei anderen Raumgliederungen
				echo $_Raumgliederung_Legendentext; 
			}
		
	
			?>
            
            </div>        
<!--------------------Ende rechte Spalte mittleres Feld------------------------------------> 
							   
							     
							   <hr style="border: none; color: #000000; background-color: #000000; height: 1px; width:300px;"/>    
					
							     
							           
							 <!-----Beginn Auswahl Vglindi, Hinweis und nicht-Anzeige bei gewähltem Zusatzzeitschnitt links---> 
							 
								<?php if ( $_SESSION['Tabelle']['Zeitschnitt_Zusatz']) { ?>
												<div class="nicht_im_print" style="margin-top:5px; margin-bottom:15px; margin-left:30px; font-weight:bold;">Hinweis: Bei Auswahl eines zusätzlichen Zeitschnittes <br/>kann kein weiterer Indikator angezeigt werden. </div>
							<?php	}
							else
							{?>	 
							 
          
        	<div class="nicht_im_print" style="margin-top:1px; margin-left:50px; ">Indikator zum Vergleich anfügen:</div>
           	<div style="clear:both; height:1px;"></div>
           
            <?php 
		 
		    // --------------- 2. Indikator ( für "Dummanzeige" )auswählen ---------------
            // Prüfer mit ID=0 darf alles sehen
			if($_SESSION['Dokument']['ViewBerechtigung'] != "0")
			{		
				$SQL_Indikatoren = "SELECT * FROM m_indikatoren,m_indikator_freigabe,m_thematische_kategorien  
									WHERE m_indikatoren.ID_THEMA_KAT = m_thematische_kategorien.ID_THEMA_KAT 
									AND m_indikatoren.ID_INDIKATOR = m_indikator_freigabe.ID_INDIKATOR 
									AND STATUS_INDIKATOR_FREIGABE >= '".$_SESSION['Dokument']['ViewBerechtigung']."' 
									AND JAHR = '".$_SESSION['Dokument']['Jahr_Anzeige']."' 
									ORDER BY SORTIERUNG_THEMA_KAT, SORTIERUNG";
			}
			else
			{
				// enthaltene Kategorien erfassen
				$SQL_Indikatoren = "SELECT * FROM m_indikatoren,m_indikator_freigabe,m_thematische_kategorien  
									WHERE m_indikatoren.ID_THEMA_KAT = m_thematische_kategorien.ID_THEMA_KAT 
									AND m_indikatoren.ID_INDIKATOR = m_indikator_freigabe.ID_INDIKATOR 
									AND JAHR = '".$_SESSION['Dokument']['Jahr_Anzeige']."' 
									ORDER BY SORTIERUNG_THEMA_KAT, SORTIERUNG";
			}
			
			
			$Ergebnis_Indikatoren = mysqli_query($Verbindung,$SQL_Indikatoren);

		 
		 ?>
    
           <div class="nicht_im_print" style="margin-bottom:10px; margin-left:50px;">
       	     <select  title="Auswahl verf&uuml;gbarer Indikatoren" name="Indikator_2" id="Indikator_2" style=" width:230px; border: solid 1px #666666; background-color:#FFFFFF; font-size:12px;"
                      onchange="MM_jumpMenu('self',this,0)" 
                      onfocus="expandSELECT(this);" 
                      onblur="contractSELECT(this);" > 
       	       <option value="?Indikator_2=leer" >-- Kein Indikator gewählt --</option>
       	       <?php 
							$i_ind=0;
							while(@mysqli_result($Ergebnis_Indikatoren,$i_ind,'ID_INDIKATOR'))
							{
								$Ind_NAME = @mysqli_result($Ergebnis_Indikatoren,$i_ind,'INDIKATOR_NAME');
								$KAT_NAME = utf8_encode(@mysqli_result($Ergebnis_Indikatoren,$i_ind,'THEMA_KAT_NAME'));
								if($KAT_NAME_vorher != $KAT_NAME) { ?><option value="?Indikator_2=leer" ><?php echo "-------- ".$KAT_NAME." --------"; ?></option><?php }
								?>
       	       <option <?php 
									// farbliche Hinterlegung für freigegebene IND für Prüfer
					 				if($_SESSION['Dokument']['ViewBerechtigung'] == "0" and @mysqli_result($Ergebnis_Indikatoren,$i_ind,'STATUS_INDIKATOR_FREIGABE') == '3')
									{
										echo 'style="background:#CFC;"';
									}
								?> value="?Indikator_2=<?php echo $Ind_value = utf8_encode(@mysqli_result($Ergebnis_Indikatoren,$i_ind,'ID_INDIKATOR')) ?>" <?php 
								if($Ind_value == $_SESSION['Tabelle']['Indikator_2'])
								{
									$Druck_Indikator_2 = "1"; // Nur Variable für Druck-Layout
									echo 'selected="selected"';
									$_SESSION['Tabelle']['Indikator_2_Name'] = utf8_encode($Ind_NAME);
									$_SESSION['Tabelle']['Indikator_2_Einheit'] = utf8_encode(@mysqli_result($Ergebnis_Indikatoren,$i_ind,'EINHEIT'));
								} ?> > <?php 
								// ID für Prüfer anzeigen
								if($_SESSION['Dokument']['ViewBerechtigung'] == "0") echo "(".$Ind_value.") ";
								echo utf8_encode($Ind_NAME); ?></option>
       	       <?php 
								$KAT_NAME_vorher = $KAT_NAME;
								$i_ind++;
							}
							?>
   	         </select>
		   <?php 
			if($_SESSION['Tabelle']['Indikator_2_Einheit'])
			{
				?> (in <?php echo $_SESSION['Tabelle']['Indikator_2_Einheit']; ?>)<?php 
			}
			?>  
   	      </div>
             	
      
        <?php

	
	
		// Normales Kopfmenü anzeigen (nicht die Karte)
		
		?>
         
              <div style="clear:both; height:1px;"></div>
              
		<?php 
		    }//Ende Einblendung, wenn kein zusätzlicher Zeitschnitt aktiviert    
							    //----Ende Auswahl Vglindi--
		
		
		
		// Ausgabe 2. Indikator für Druck
		if($Druck_Indikator_2)
		{
			 ?>
			   <div class="nur_im_print">
				   Vergleichsindikator: 
				   <?php 
					echo $_SESSION['Tabelle']['Indikator_2_Name']; 
						if($_SESSION['Tabelle']['Indikator_2_Einheit'])
						{
							?> (in <?php echo $_SESSION['Tabelle']['Indikator_2_Einheit']; ?>)<?php 
						}
				   ?>
			  </div>
			  <?php 
		}    ?>   
		
        </td>
<!-------------------------------- Ende Spalte rechts------------------------------->
		
		      </tr>
</table>
  <!-------------------------------- Ende Optionen ------------------------------->		
   </div>
	 <!---Ende Ein- und Ausklappbarer Eingabebereich/Menü--> 

        

   <!-------------------------------- Hinweise zwischen Menü und Tabelle ------------------------------->           
    <table> 
      <tr>
        <td colspan="3" valign="top" style="border:none; padding:0px; padding-top:10px;">
        <div style="padding-bottom:2px; font-size:10px;"><strong>Zur Information: </strong>
Zeitschnittvergleiche erfolgen durchgehend auf dem Gebietsstand des gewählten Basisjahres.
	<?php //Hinweis für noch unbekannte Einwohnerzahlen
						if ($EW_Hinweis) {echo $EW_Hinweis;}?></div>
        </td>
      </tr>
</table>

<div style="height:8px;"></div>
<!--------------------------------Ende Hinweise zwischen Menü und Tabelle ------------------------------->     

<?php 


// ------------------------ Karte oder Menü anzeigen ---------------------------
if($_SESSION['Tabelle']['KARTENANZEIGE_WERT'])
{
			// Karte zeigen
			$_SESSION['Tabelle']['KARTENANZEIGE'] = '1'; // für svg_svg.php wichtig für die Anzeigeerstellung
			
			// Temporäres spreichern der anzuzeigenden Werte in SESSION-Array
			$SQL_Kartendaten = "SELECT * FROM t_temp_tabellentool";
			$Ergebnis_Kartendaten = mysqli_query($Verbindung,$SQL_Kartendaten);
			
			$Normierung = 0;
			
			// alte Daten löschen
			$_SESSION['temp_vergleichswerte'] = array();
			
			
		
			// ---------------------- Speichern jedes Geoobjektes im SESSION-Array -----------------------------
			$i_temp = '0';
			while($AGS = @mysqli_result($Ergebnis_Kartendaten,$i_temp,'AGS'))
			{
				$_SESSION['temp_vergleichswerte']['Objekte'][$i_temp]['AGS'] = $AGS;
				
				// Fehlerbehandlung
				$FC = @mysqli_result($Ergebnis_Kartendaten,$i_temp,$_SESSION['Tabelle']['KARTENANZEIGE_FEHLERCODE']);
				$FC_AKT = @mysqli_result($Ergebnis_Kartendaten,$i_temp,$_SESSION['Tabelle']['KARTENANZEIGE_FEHLERCODE_AKT']);
				
				// Fehler durch ausgeblendete Raumeinheit abfangen
				if(@mysqli_result($Ergebnis_Kartendaten,$i_temp,'FEHLERCODE')) $FC = @mysqli_result($Ergebnis_Kartendaten,$i_temp,'FEHLERCODE');
				
				
				if(!$FC and !$FC_AKT)
				{ 
					// Wert übernehmen, der als Spalte angeklickt wurde, wenn kein Fehler vermerkt ist ... sonst Fehler vermerken
					$Wert_V = $_SESSION['temp_vergleichswerte']['Objekte'][$i_temp]['V_WERT'] = @mysqli_result($Ergebnis_Kartendaten,$i_temp,$_SESSION['Tabelle']['KARTENANZEIGE_WERT']);
					// Normierung (Min und Max auch aneinander anpassen und nicht separat betrachten)
					// Normierung übernehmen wenn kein Fehler vermerkt
					if(abs($Wert_V) > $Normierung) 
					{
						$Normierung = abs($Wert_V); 
					}
				}
				else
				{
					// Reihenfolge bestimmt die Fehlerausgabe in der Karte
					if($FC_AKT) $_SESSION['temp_vergleichswerte']['Objekte'][$i_temp]['FEHLERCODE'] = $FC_AKT;
					if($FC) $_SESSION['temp_vergleichswerte']['Objekte'][$i_temp]['FEHLERCODE'] = $FC;
				}
				

				$i_temp++;
			}
			
			
			// Normierung speichern (hier besser erfassbar als im SVG-Script
			$_SESSION['temp_vergleichswerte']['Rahmenbedingungen']['Min'] = -$Normierung; 
			$_SESSION['temp_vergleichswerte']['Rahmenbedingungen']['Max'] = $Normierung;
			$_SESSION['temp_vergleichswerte']['Rahmenbedingungen']['Normierung'] = abs($Normierung);
			$_SESSION['temp_vergleichswerte']['Rahmenbedingungen']['Jahr_Basis'] = $_SESSION['Dokument']['Jahr_Anzeige'];
			$_SESSION['temp_vergleichswerte']['Rahmenbedingungen']['Jahr_Vergleich'] = $_SESSION['Tabelle']['KARTENANZEIGE_ZZEITSCHNITT'] ;
			$_SESSION['temp_vergleichswerte']['Rahmenbedingungen']['KARTENANZEIGE_TYP'] = $_SESSION['Tabelle']['KARTENANZEIGE_TYP'];
			
			
			
			?>
			<br />
			<div>
			  <object type="image/svg+xml"
				data="../svg_svg.php"
				width="<?php echo $x_karte = 800; ?>"
				height="<?php echo $y_karte = 800; ?>"
                id="svgDOM" 
                onload="SVG_geladen();" >
					<param name="src" value="../svg_svg.php?ktyp=vergleich" />
					<!--<img src="" width="425" height="533" alt="" /> -->
					<div style="text-align:center; padding-top:100px; padding-right:200px;">
					Leider unterst&uuml;tzt Ihr Browser keine SVG-Darstellung.<br /><br />
					Bitte installieren Sie das <strong><a target="_blank" href="http://www.adobe.com/svg/viewer/install/">AdobeSVG-Plugin</a></strong><br />
					oder verwenden Sie einen anderen Browser (Empfehlung: <a href="http://www.opera.com/" target="_blank"><strong>Opera</strong>)</a>!<br />
					<br />
					<br />
					Weitere technische Hinweise: <a target="_top" href="http://www.ioer-monitor.de/index.php?id=85"><strong>hier</strong></a> 
					</div>
			  </object> 
			</div>
        
	<?php 
	// Testvariable für Laufzeit
	$Laufzeit = $Laufzeit.'
	Vergleichskarte generiert: '.date('H:i:s');
}
?>



<!-- Versteckte Grafik für Serverkommunikation vorherpfad: ./icons_viewer/leer_pixel.png-->
<img style=" width:0px; height:0px;" src="../icons_viewer/leer_pixel.png" id="verstecktegrafik" ></img>
 
 
    
    
    
<?php 

// Schalter zum Ausblenden der Kartenanzeige
if($_SESSION['Tabelle']['KARTENANZEIGE_WERT'])
{
	?>
<br />
	<form action="#tabellenkopf" method="post" class="nicht_im_print">
       		<input name="karteaus_button" type="submit" class="button_standard_abschicken_a" 
            style=" text-align:center; width:200px; font-size:12px; height:20px; padding:0px; padding-bottom:2px; padding-left:7px;" 
            	value="Karte ausblenden" />
        	<input name="TABELLENANZEIGE" type="hidden" value="TABELLENANZEIGE" />
   	</form>
    

<br />
    <br />
	<?php 
}
?>

<?php


	
	// Legende für Hinweiscodes nur anzeigen, wenn Hinweiscodes vorhanden (Schalter $Hinweiscodes_vorhanden gesetzt)
	if($Hinweiscodes_vorhanden)
	{
		?>
<a href="http://www.ioer-monitor.de/index.php?id=98#c201" target="_blank" title="Mehr Informationen finden Sie in den Bedienungshinweisen, über Klick auf diese Überschrift.">Übersicht zu Hinweisen in der Tabelle:</a><br />
		<?php 
		for( $HC_Code = 1 ; $HC_Code <= 30 ; $HC_Code++ )
		{
			if($Hinweiscodes_vorhanden[$HC_Code])
			{
				?>
				<img src="../icons_viewer/hinweis_<?php echo $HC_Code; ?>.png" width="16" height="14" alt="Hinweis-Icon" />&nbsp;&nbsp;<?php echo $HC_Definition[$HC_Code]['HC_NAME'].' ('.$HC_Definition[$HC_Code]['HC_INFO'].')'; ?><br />
				<?php
			}
		}
		?>
        <div style="height:8px;"></div>
		<?php
	}
	

	?>
<!----------Ende Bereich zwischen Menü und Tabelle-------------------------------------->



<!------------------------ Datentabelle ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------>


<table style="border-collapse:collapse;">

  
		<!------------------------ obere Kopfzeile der Tabelle unten------------------------------------------------------------------------------------------------------------------------------------------------------------->
	<tr>
	  
      <?php 
	  // vorerst nur für Admins sichtbar
	  if($_SESSION['Dokument']['ViewBerechtigung'] == "0")
	  { 
		  ?>
		  <th rowspan="2" style="padding:10px;" ><img src="../icons_viewer/txt_aktivinaktiv.png" width="19" height="105" alt="aktiv/inaktiv" /></th>
		 
		  <?php 
	  }
	  ?>
      <!-- Markierung -->
       <th rowspan="2" style="padding:10px;" ><img title="Markierte Gebiete werden in der Karte automatisch beschriftet (Karte neu laden erforderlich)." src="../icons_viewer/txt_markieren.png" width="23" height="91" alt="markieren" /></th>
	 
		<!-- Überkopf über direkten Werten -->
		<?php
		// Spalte erweitern wenn Akt angezeigt
		if($_SESSION['Tabelle']['AKTUALITAET'])
		{
			if ( $Trend_ZS_vorh == '1')	{	$clospan_a = "4";}
			else {	$clospan_a = "5";}
		}
		else
		{
			$clospan_a = "4";
		}
		// Spalte erweitern wenn 2. Ind angezeigt
		if($_SESSION['Tabelle']['Indikator_2']) 
		{
			$clospan_a++;
		}
		// Spalte erweitern wenn Absolut-Ind angezeigt und IND = ...RG
			if($_SESSION['Tabelle']['WERT_ABS'] and substr($_SESSION['Dokument']['Fuellung']['Indikator'],3,2)=='RG' && $Trend_ZS_vorh != '1')
		{
			$clospan_a++;
		}
		/* nein, wird hier ja nicht angezeigt
		 // Spalte erweitern wenn Absolutwertveränderung angezeigt
		if($_SESSION['Tabelle']['WERT_ABS_DIFF']) 
		{
			$clospan_a++;
		} */
		// Spalte erweitern wenn Fläche angezeigt
	  if($_SESSION['Tabelle']['FLAECHE'] && $Trend_ZS_vorh != '1') 
		{
			$clospan_a++;
		}
		// Spalte erweitern wenn Einwohnerzahl angezeigt
		if($_SESSION['Tabelle']['EWZ'] && $Trend_ZS_vorh != '1' && $_SESSION['Dokument']['Raumgliederung']!=='stt' )  
		{
			$clospan_a++;
		}
		?>
		
    <th style="padding:10px; color:#000000; font-size:18pt;" colspan="<?php echo $clospan_a; ?>" >
		<?php echo $_SESSION['Dokument']['Fuellung']['Indikator_Beschreibung']; ?> 
		
		(<?php 
			

			echo $_SESSION['Dokument']['Jahr_Anzeige']; 
			?>)
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
					<th class="changerow" style="background:#CCC; width:1px; padding:0px;" >&nbsp;</th>
					 <?php
					if($_SESSION['Tabelle']['AKTUALITAET'])
					{
						$clospan_b ++;
					}
					?>
					<th class="changerow" style="padding:10px; color:#000000;" colspan="<?php echo $clospan_b; ?>">Übergeordneter Kreis <?php echo " (".$_SESSION['Dokument']['Jahr_Anzeige'].")"; ?></th>
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
					<th class="changerow" style="background:#CCC; width:1px; padding:0px;" >&nbsp;</th>
					 <?php
					if($_SESSION['Tabelle']['AKTUALITAET'])
					{
						$clospan_b ++;
					}
					?>
					<th class="changerow" style="padding:10px; color:#000000;" colspan="<?php echo $clospan_b; ?>">Übergeordnetes Bundesland <?php echo " (".$_SESSION['Dokument']['Jahr_Anzeige'].")"; ?></th>
					<?php
				}
			}
			
			// Bund
			if($_SESSION['Tabelle']['UERE_BND'])
			{	
				$clospan_c = 2;

				?>
				<!-- Trenner -->
				<th class="changerow" style="background:#CCC; width:1px; padding:0px;" >&nbsp;</th>
				 <?php
				if($_SESSION['Tabelle']['AKTUALITAET'])
				{
					$clospan_c ++;
				}
				?>
				<th class="changerow" style="padding:10px; color:#000000;" colspan="<?php echo $clospan_c; ?>">Gesamte Bundesrepublik <?php echo " (".$_SESSION['Dokument']['Jahr_Anzeige'].")"; ?></th>
                <?php
			}
			
			
				// ----------------------- Zusätzliche Zeitschnitte ausgeben obere Kopfzeile -----------------------------
 //Spalten 2025 und 2030 tauschen, da diese aufsteigend angezeigt werden sollen (ZusatzZeitschnitte sonst absteigend)
 if(is_array($Zeitschnitte_vorh) && (in_array("2025", $Zeitschnitte_vorh) or in_array("2030", $Zeitschnitte_vorh))&& ( $_SESSION['Tabelle']['Trend_Raumgliederung']== '1'  ))
  {
 	$Tausch2030 = $Zeitschnitte_vorh[0];
 	$Zeitschnitte_vorh[0]= $Zeitschnitte_vorh[1];
 	$Zeitschnitte_vorh[1] = $Tausch2030;
 	}
				
		
			if(is_array($Zeitschnitte_vorh))
			{
				foreach($Zeitschnitte_vorh as $ZZeitschnitt)
				{
					if(($ZZeitschnitt != $_SESSION['Dokument']['Jahr_Anzeige'] and $_SESSION['Tabelle']['Zeitschnitt_Zusatz'][$ZZeitschnitt] == 1) and ($ZZeitschnitt <'2024' or ($ZZeitschnitt >= '2025' and   $_SESSION['Tabelle']['Trend_Berechtigung'] == '1' && $Zus_ZS_vorh != '1') ) ) // Aktuellen Zeitschnitt nicht berücksichtigen & nur ausgeben wenn auch Werte vorhanden!
					{
							
						$clospan_z = 1;
						?>
						<!-- Trenner -->
						<th style="background:#CCC; width:3px;" >&nbsp;</th>
						
						<?php
					//Viele Spalten bei normalen Zeitschnitten anhängen
								if($ZZeitschnitt != '2025' and 	$ZZeitschnitt != '2030')
						{
							?>
						
						
						<!-- absolute Veränderung zum gewählten Basiszeitschnitt -->
						<?php 
						if($_SESSION['Tabelle']['VERGLEICH'] == '1')
						{	
							$clospan_z++;
						}
						// Diff pro Jahr
						if($_SESSION['Tabelle']['VERGLEICH'] == '1' and $_SESSION['Tabelle']['AKTUALITAET'])
						{	
							$clospan_z++;
						}
						?>
						
						<!-- Grundaktualitaet -->
						<?php
						if($_SESSION['Tabelle']['AKTUALITAET'])
						{
							$clospan_z++; // Grundakt generell
							
							$clospan_z++; // Grundakt Diff
						}

                        // Spalte erweitern wenn Absolutwertveränderung angezeigt
						if($_SESSION['Tabelle']['WERT_ABS_DIFF'] and $_SESSION['Tabelle']['WERT_ABS'] and $_SESSION['Tabelle']['VERGLEICH'] and substr($_SESSION['Dokument']['Fuellung']['Indikator'],3,2)=='RG') 
                        {
                            $clospan_z++;
                        }
						// Spalte erweitern wenn Absolut-Ind angezeigt und IND = ...RG
                        if($_SESSION['Tabelle']['WERT_ABS'] and substr($_SESSION['Dokument']['Fuellung']['Indikator'],3,2)=='RG') 
                        {
                            $clospan_z++;
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
									$clospan_z++;
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
								$clospan_z++;
							}
						}
						if($_SESSION['Tabelle']['UERE_BND'])
						{	
							$clospan_z++;
						}
						?>
						<th style="padding:10px; color:#000000;" colspan="<?php echo $clospan_z; ?>">Zum Vergleich (<?php echo $ZZeitschnitt; ?>)</th>
                    	<?php
                    	
                    	
               		}
					
					//für Trendfortschreibung nur Wert zulassen
						if(($ZZeitschnitt == '2025' or 	$ZZeitschnitt == '2030')and ($_SESSION['Tabelle']['Trend_Berechtigung'] == '1')&& $Zus_ZS_vorh != '1' )
						{	?>													
							<th style="padding:10px; color:#000000;" colspan="<?php echo $clospan_z; ?>">Trendfortschreibung (<?php echo $ZZeitschnitt; ?>)</th>
	                    	<?php
							// Über-Kopf in separates Array
							$ik++;
							$CSV_Kopf[$ik]['Text'] = 'Trendforschreibung ('.$ZZeitschnitt.')';
							$CSV_Kopf[$ik]['Colspan'] = $clospan_z;
						}    	
             	
					}
				}
			}
	
			?>
	  </tr>

	<?php     
  
    
		// ------------------------ untere Kopfzeile ---------------------------------------------------------------------
		?>
	<tr style="border:0px; border-bottom: 2px solid #666;">
	        <?php 
	  // vorerst nur für Admins sichtbar
	  if($_SESSION['Dokument']['ViewBerechtigung'] == "0")
	  { 
		?>
        <?php 
	  }
	  ?>  
          <th style="padding-left:5px; padding-right:5px; text-align:center;">
              lfd.<br />
              Nr.
          <?php  $CSV[0][] = 'lfd. Nr.'; ?></th>
		<!-- AGS untere Kopfzeile-->
		  <th style=" <?php if($_SESSION['Tabellen_Sortierung'] == 'AGS'){ echo 'background-color:#e0ebc5;'; } ?> ">
				<a href="?sort=AGS&Zusatzzeitschnitt=nein">
				  <div  title="Sortieren nach dem amtlichen Gemeindeschlüssel"style="
					cursor:s-resize;
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
				?>">
                <?php 
				// AGS nur wenn sinnvoll, ansonsten ID
				if($_SESSION['Dokument']['Raumgliederung'] == "gem" or $_SESSION['Dokument']['Raumgliederung'] == "krs" or $_SESSION['Dokument']['Raumgliederung'] == "bld"
				 or $_SESSION['Dokument']['Raumgliederung'] == "kfs"  or $_SESSION['Dokument']['Raumgliederung'] == "lks")
				{
					echo "AGS";
				}
				else
				{
					echo "ID";
				}
				?>
        </div>
       </a>
			<?php  $CSV[0][] = 'ID(AGS)'; ?>
				
	  </th>
	  
			<!-- Name untere Kopfzeile-->
			<th style=" <?php if($_SESSION['Tabellen_Sortierung'] == 'NAME'){ echo 'background-color:#e0ebc5;'; } ?> ">
				<a href="?sort=NAME&Zusatzzeitschnitt=nein">
					<div title="Sortieren nach..." style="
						cursor:s-resize;
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
				?>">Gebietsname</div></a>
                <?php  $CSV[0][] = 'Gebietsname'; ?>
			</th>
			
			<!-- Wert untere Kopfzeile-->
			<th style=" <?php if($_SESSION['Tabellen_Sortierung'] == 'WERT'){ echo 'background-color:#e0ebc5;'; } ?> ">
				<a href="?sort=WERT&Zusatzzeitschnitt=nein">
					<div title="Sortieren nach..." style="
						cursor:s-resize;
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
						echo '<br />(';
						 echo $_SESSION['Dokument']['Fuellung']['Indikator_Einheit']; 
 						echo ")";

						break;
						case 'krs':
							echo 'Kreiswert';
						echo '<br />(';
						 echo $_SESSION['Dokument']['Fuellung']['Indikator_Einheit'] ; 
 							echo ")";

						break;
						case 'bld':
						echo 'Bundeslandwert';
						echo '<br />(';
						 echo $_SESSION['Dokument']['Fuellung']['Indikator_Einheit'] ; 
 						echo ")";

						break;
						default:
						echo 'Indikatorwert';
						echo '<br />(';
						 echo $_SESSION['Dokument']['Fuellung']['Indikator_Einheit'] ; 
						 echo ")";
						break;
					}	?>
				</div></a>
                <?php  $CSV[0][] = 'Indikatorwert'; ?>
			</th>
            			
            
            
             <!-- Wert Absolut-Indikator untere Kopfzeile-->
            <?php 
			// Spalte erweitern wenn Absolut-Ind angezeigt und IND = ...RG
			if($_SESSION['Tabelle']['WERT_ABS'] and substr($_SESSION['Dokument']['Fuellung']['Indikator'],3,2)=='RG') 
			{
				?>
                <th class="changerow" style=" <?php if($_SESSION['Tabellen_Sortierung'] == 'WERT_ABS'){ echo 'background-color:#e0ebc5;'; } ?> text-align: center; ">
                    <a href="?sort=WERT_ABS" title="Absolutwerte zum Indikator">
                        <div title="Sortieren nach..." style="
                        	cursor:s-resize;
                            display:block;
                            padding-top:2px; 
                            padding-bottom:2px; 
                            padding-left:10px; 
                            padding-right:15px; 
                            margin-right:3px;
                             text-align:left;
                            <?php 
                          if($_SESSION['Tabellen_Sortierung'] == 'WERT_ABS')
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
                    
                    
                    Absoluter<br /> 
                    Indikatorwert<br />  
                    (km²)
					<?php if ($Trend_ZS_vorh != '1'){ $CSV[0][] = 'Absouter Indikatorwert';} ?>

                  </div></a>
                </th>
            	<?php 
			}
			?>
            
            
            
            <!-- Wert 2. Indikator untere Kopfzeile-->
            <?php 
			// Spalte erweitern wenn 2. Ind angezeigt
			if($_SESSION['Tabelle']['Indikator_2']) 
			{
				?>
                <th class="changerow" style=" <?php if($_SESSION['Tabellen_Sortierung'] == 'WERT_2'){ echo 'background-color:#e0ebc5;'; } ?> text-align:right; ">
                    <a href="?sort=WERT_2&Zusatzzeitschnitt=nein" title="<?php echo $_SESSION['Tabelle']['Indikator_2_Name']; ?>">
                        <div title="Sortieren nach..." style="
                        	cursor:s-resize;
                            display:block;
                            padding-top:2px; 
                            padding-bottom:2px; 
                            padding-left:10px; 
                            padding-right:15px; 
                            margin-right:3px;
                             text-align:right;
                            <?php 
                          if($_SESSION['Tabellen_Sortierung'] == 'WERT_2')
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
                    
                    
                    <?php echo $_SESSION['Tabelle']['Indikator_2_Name']; ?>
					<?php if ($Trend_ZS_vorh != '1'){ $CSV[0][] = '2. Indikator: '.$_SESSION['Tabelle']['Indikator_2_Name'];} ?>
				                    
                    </div></a>
                </th>
            	<?php 
			}
			?>
            
            
            <!-- Gebietsfläche untere Kopfzeile-->
			<?php
			if($_SESSION['Tabelle']['FLAECHE'])
			{
				?>
				<th class="changerow" style=" <?php if($_SESSION['Tabellen_Sortierung'] == 'FLAECHE'){ echo 'background-color:#e0ebc5;'; } ?> ">
					<a href="?sort=FLAECHE&Zusatzzeitschnitt=nein">
						<div title="Sortieren nach..." style=" cursor:s-resize; padding-top:2px; padding-bottom:2px; padding-left:10px; padding-right:10px; margin-right:3px;text-align:right;<?php 
						  if($_SESSION['Tabellen_Sortierung'] == 'FLAECHE')
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
						?>">Gebiets-<br />fläche (km²)</div>
				  </a>
                     <?php if ($Trend_ZS_vorh != '1'){ $CSV[0][] = 'Gebietsfläche'; }?>
				</th>
				<?php 
			}
			?> 
            
            <!-- Einwohnerzahl untere Kopfzeile-->
			<?php
			if($_SESSION['Tabelle']['EWZ'] && $_SESSION['Dokument']['Raumgliederung']!=='stt')
			{
				?>
				<th class="changerow" style=" <?php if($_SESSION['Tabellen_Sortierung'] == 'EWZ'){ echo 'background-color:#e0ebc5;'; } ?> ">
					<a href="?sort=EWZ&Zusatzzeitschnitt=nein">
						<div title="Sortieren nach..." style=" cursor:s-resize; padding-top:2px; padding-bottom:2px; padding-left:10px; padding-right:10px; margin-right:3px;text-align:right;<?php 
						  if($_SESSION['Tabellen_Sortierung'] == 'EWZ')
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
						?>">Einwohner-<br />zahl
							<?php	if ($EW_Hinweis_Titel) {echo $EW_Hinweis_Titel;}?>
						</div>
				  </a>
                     <?php if ($Trend_ZS_vorh != '1'){ $CSV[0][] = 'Einwohnerzahl';} ?>
				</th>
				<?php 
			}
			?>
            
            
			<!-- Grundaktualitaet untere Kopfzeile-->
			<?php
			if($_SESSION['Tabelle']['AKTUALITAET'])
			{
				?>
				<th  class="changerow" style=" <?php if($_SESSION['Tabellen_Sortierung'] == 'AKT'){ echo 'background-color:#e0ebc5;'; } ?> ">
					<a href="?sort=AKT&Zusatzzeitschnitt=nein">
						<div title="Sortieren nach..." style=" cursor:s-resize; padding-top:2px; padding-bottom:2px; padding-left:10px; padding-right:10px; margin-right:3px;<?php 
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
						  aktualit&auml;t&nbsp;
						</div>
				  </a>
                     <?php  if ($Trend_ZS_vorh != '1'){$CSV[0][] = 'Mittlere Grundaktualität';} ?>
				</th>
				<?php 
			}
			?>
			<!-- Uebergeordnete RE untere Kopfzeile-->
			<?php
			// Kreise
			if($_SESSION['Tabelle']['UERE_KRS'])
			{
	
				
				// sinnvolle Anzeige der Zeile nur bei Gemeinden
				if($_SESSION['Dokument']['Raumgliederung'] == "gem" )
				{
					?>
					<!-- Trenner untere Kopfzeile-->
					<th  class="changerow" style="background:#CCC; width:1px; padding:0px;" >&nbsp;</th>
					<!-- Kreis untere Kopfzeile-->
					<th  class="changerow" style=" <?php if($_SESSION['Tabellen_Sortierung'] == 'WERT_KRS_DIFF'){ echo 'background-color:#e0ebc5;'; } ?> ">
					   <a href="?sort=WERT_KRS_DIFF&Zusatzzeitschnitt=nein">
						   <div title="Sortieren nach..." style=" cursor:s-resize; padding-top:2px; padding-bottom:2px; padding-left:10px; padding-right:10px; margin-right:3px;<?php 
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
							   $SQL_Max = "SELECT MAX(WERT_KRS_DIFF) AS MAX, MIN(WERT_KRS_DIFF) AS MIN FROM t_temp_tabellentool WHERE FEHLERCODE = '0' or FEHLERCODE = ''"; 
							   $Ergebnis_Max = mysqli_query($Verbindung,$SQL_Max); 
							   $Max_diff_krs = @mysqli_result($Ergebnis_Max,0,'MAX');
							   $Min = abs(@mysqli_result($Ergebnis_Max,0,'MIN'));
							   if($Min > $Max_diff_krs) $Max_diff_krs = $Min;
							   ?>
						   </div>
                           
					  </a>
                        <?php if ($Trend_ZS_vorh != '1'){ $CSV[0][] = 'Differenz zum Kreis'; }?>
       
					</th>
					<th  class="changerow" style=" <?php if($_SESSION['Tabellen_Sortierung'] == 'WERT_KRS'){ echo 'background-color:#e0ebc5;'; } ?> ">
					   <a href="?sort=WERT_KRS&Zusatzzeitschnitt=nein">
						<div title="Sortieren nach..." style=" cursor:s-resize; padding-top:2px; padding-bottom:2px; padding-left:10px; padding-right:10px; margin-right:3px;<?php 
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
							(Name)
							<br />
							(<?php echo $_SESSION['Dokument']['Fuellung']['Indikator_Einheit']; ?>)
							
							</div>
					   </a>
                       <?php if ($Trend_ZS_vorh != '1'){ $CSV[0][] = 'Kreiswert (Name)'; }?>
					</th>
		
					 <?php
					if($_SESSION['Tabelle']['AKTUALITAET'])
					{
						?>
						<th  class="changerow" style=" <?php if($_SESSION['Tabellen_Sortierung'] == 'AKT_KRS'){ echo 'background-color:#e0ebc5;'; } ?> ">
						   <a href="?sort=AKT_KRS&Zusatzzeitschnitt=nein">
							   <div title="Sortieren nach..." style=" cursor:s-resize; padding-top:2px; padding-bottom:2px; padding-left:10px; padding-right:10px; margin-right:3px;<?php 
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
                            <?php if ($Trend_ZS_vorh != '1'){ $CSV[0][] = 'Mittlere Grundakt. Kreis';} ?>   
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
					<!-- Trenner untere Kopfzeile-->
					<th class="changerow" style="background:#CCC; width:1px; padding:0px;" >&nbsp;</th>
					<!-- Bundesland untere Kopfzeile-->
					<th class="changerow" style=" <?php if($_SESSION['Tabellen_Sortierung'] == 'WERT_BLD_DIFF'){ echo 'background-color:#e0ebc5;'; } ?> ">
					   <a href="?sort=WERT_BLD_DIFF&Zusatzzeitschnitt=nein">
						   <div title="Sortieren nach..." style=" cursor:s-resize; padding-top:2px; padding-bottom:2px; padding-left:10px; padding-right:10px; margin-right:3px;<?php 
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
						  <?php if ($Trend_ZS_vorh != '1'){ $CSV[0][] = 'Differenz zum Bundesland';} ?>
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
					<th class="changerow" style=" <?php if($_SESSION['Tabellen_Sortierung'] == 'WERT_BLD'){ echo 'background-color:#e0ebc5;'; } ?> ">
					   <a href="?sort=WERT_BLD&Zusatzzeitschnitt=nein">
						   <div title="Sortieren nach..." style=" cursor:s-resize; padding-top:2px; padding-bottom:2px; padding-left:10px; padding-right:10px; margin-right:3px;<?php 
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
							(Name)<br />
							(<?php echo $_SESSION['Dokument']['Fuellung']['Indikator_Einheit']; ?>)

                            <?php  if ($Trend_ZS_vorh != '1'){$CSV[0][] = 'Bundeslandwert (Name)'; }?>
						</div>
					   </a>
					</th>
					 <?php
					if($_SESSION['Tabelle']['AKTUALITAET'])
					{
						?>
						<th class="changerow" style=" <?php if($_SESSION['Tabellen_Sortierung'] == 'AKT_BLD'){ echo 'background-color:#e0ebc5;'; } ?> ">
						   <a href="?sort=AKT_BLD&Zusatzzeitschnitt=nein">
							   <div title="Sortieren nach..." style=" cursor:s-resize; padding-top:2px; padding-bottom:2px; padding-left:10px; padding-right:10px; margin-right:3px;<?php 
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
                                <?php if ($Trend_ZS_vorh != '1'){ $CSV[0][] = 'Mittlere Grundakt. Bundesland';} ?>
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
					<!-- Trenner untere Kopfzeile-->
					<th class="changerow" style="background:#CCC; width:1px; padding:0px;" >&nbsp;</th>
					<!-- Bundesland untere Kopfzeile-->
					<th class="changerow" style=" <?php if($_SESSION['Tabellen_Sortierung'] == 'WERT_BND_DIFF'){ echo 'background-color:#e0ebc5;'; } ?> ">
					   <a href="?sort=WERT_BND_DIFF&Zusatzzeitschnitt=nein">
						   <div title="Sortieren nach..." style=" cursor:s-resize; padding-top:2px; padding-bottom:2px; padding-left:10px; padding-right:10px; margin-right:3px;<?php 
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
                            <?php if ($Trend_ZS_vorh != '1'){ $CSV[0][] = 'Differenz zur gesamten Bundesrepublik';} ?>
							<?php 
														
							// Max für Box dieser Spaltenwerte ermitteln
							$SQL_Max = "SELECT MAX(WERT_BND_DIFF) AS MAX, MIN(WERT_BND_DIFF) AS MIN FROM t_temp_tabellentool WHERE FEHLERCODE = '0' or FEHLERCODE = ''"; 
							$Ergebnis_Max = mysqli_query($Verbindung,$SQL_Max); 
							$Max_diff_bnd = @mysqli_result($Ergebnis_Max,0,'MAX');
							$Min = abs(@mysqli_result($Ergebnis_Max,0,'MIN'));
							if($Min > $Max_diff_bnd) $Max_diff_bnd = $Min;
							?>
						</div>
					  </a>                   
					</th>
					<th class="changerow">
						<div style="padding-top:2px; padding-bottom:2px; padding-left:10px; padding-right:10px; margin-right:3px; color:#444444;">Wert für<br />Bundesrepublik
                        <?php if ($Trend_ZS_vorh != '1'){ $CSV[0][] = 'Wert für Bundesrepublik'; }?>
						</div>
					</th>
					 <?php
					if($_SESSION['Tabelle']['AKTUALITAET'])
					{
						?>
						<th class="changerow">
							<div style="padding-top:2px; padding-bottom:2px; padding-left:10px; padding-right:10px; margin-right:3px; color:#444444;">
								Mittlere<br />
								Grundakt.<br />
								Bundesrepublik
                                <?php  if ($Trend_ZS_vorh != '1'){ $CSV[0][] = 'Mittlere Grundakt. Bundesrepublik'; }?>
							</div>
						</th>
						<?php
					}
			}
			
			
			// ----------------------- Zusätzliche Zeitschnitte ausgeben untere Kopfzeile------------------------------
			
			if(is_array($Zeitschnitte_vorh))
			{
				foreach($Zeitschnitte_vorh as $ZZeitschnitt)
				{
					if(($ZZeitschnitt != $_SESSION['Dokument']['Jahr_Anzeige'] and $_SESSION['Tabelle']['Zeitschnitt_Zusatz'][$ZZeitschnitt] == 1)and ($ZZeitschnitt <'2024' or ($ZZeitschnitt >= '2025' and   $_SESSION['Tabelle']['Trend_Berechtigung'] == '1' and $Zus_ZS_vorh != '1') ) ) // Aktuellen Zeitschnitt nicht berücksichtigen!
					{
						?>
						<!-- Trenner -->
						<th style="background:#CCC; width:3px;" >&nbsp;</th>
						
                        <!-- Wert ZZS untere Kopfzeile-->
						<th style=" <?php if($_SESSION['Tabellen_Sortierung'] == 'WERT_'.$ZZeitschnitt){ echo 'background-color:#e0ebc5;'; } ?> ">
						 <a href="?sort=<?php echo 'WERT_'.$ZZeitschnitt ?>&Zusatzzeitschnitt=<?php echo $ZZeitschnitt; ?>">
						   <div title="Sortieren nach..." style=" cursor:s-resize; padding-top:2px; padding-bottom:2px; padding-left:10px; padding-right:10px; margin-right:3px;<?php 
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
								 
								switch ($_SESSION['Dokument']['Raumgliederung'])
								{
									case 'gem':
									echo 'Gemeindewert';
									echo '<br />(';
						 echo $_SESSION['Dokument']['Fuellung']['Indikator_Einheit']; 
 						echo ")";
									break;
									case 'krs':
									echo 'Kreiswert';
									echo '<br />(';
						 echo $_SESSION['Dokument']['Fuellung']['Indikator_Einheit']; 
 						echo ")";
									break;
									case 'bld':
									echo 'Bundeslandwert';
									echo '<br />(';
						 echo $_SESSION['Dokument']['Fuellung']['Indikator_Einheit']; 
 						echo ")";
									break;
									default:
									echo 'Wert';
									echo '<br />(';
						 echo $_SESSION['Dokument']['Fuellung']['Indikator_Einheit']; 
 						echo ")";
									break;
								}
								// echo ' ('.$ZZeitschnitt.')'; 
								?>
						 </div>
						  </a>
                          <?php  $CSV[0][] = 'Wert '.$ZZeitschnitt; ?>
						</th>
                	<?php 
									//Viele Spalten bei normalen Zeitschnitten anhängen
					if($ZZeitschnitt != '2025' and 	$ZZeitschnitt != '2030')
						{?>         
                        
                    	<!-- Wert Absolut-Indikator ZZS untere Kopfzeile-->
						<?php 
                        // Spalte erweitern wenn Absolut-Ind angezeigt und IND = ...RG
						if($_SESSION['Tabelle']['WERT_ABS'] and substr($_SESSION['Dokument']['Fuellung']['Indikator'],3,2)=='RG') 
                        {
                            ?>
                            <th style=" <?php if($_SESSION['Tabellen_Sortierung'] == 'WERT_ABS_'.$ZZeitschnitt){ echo 'background-color:#e0ebc5;'; } ?> text-align:center; ">
                                <a href="?sort=WERT_ABS_<?php echo $ZZeitschnitt; ?>" title="Absolutwerte zum Indikator">
                                    <div title="Sortieren nach..." style="
                                    	cursor:s-resize;
                                        display:block;
                                        padding-top:2px; 
                                        padding-bottom:2px; 
                                        padding-left:10px; 
                                        padding-right:15px; 
                                        margin-right:3px;
                                         text-align:left;
                                        <?php 
                                      if($_SESSION['Tabellen_Sortierung'] == 'WERT_ABS_'.$ZZeitschnitt)
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
                                
                                Absoluter<br /> 
                    Indikatorwert<br />  
                    (km²)
                                <?php  $CSV[0][] = 'Absolutwert'; ?>
            
                              </div></a>
                            </th>
                            <?php 
                        }
                        ?>                       
                        
      <!-- Wert Absolutwert-Veränderung (ha/d) nur für Flächeninanspruchnahme SxxAG ZZS untere Kopfzeile-->
						<?php 
                        // Spalte erweitern wenn Absolut-Ind angezeigt
                        if($_SESSION['Tabelle']['WERT_ABS_DIFF'] and $_SESSION['Tabelle']['WERT_ABS'] and $_SESSION['Tabelle']['VERGLEICH']) 
                        {
                            ?>
                            <th style=" <?php if($_SESSION['Tabellen_Sortierung'] == 'WERT_ABS_TAG_DIFF_'.$ZZeitschnitt){ echo 'background-color:#e0ebc5;'; } ?> text-align:right; ">
                                <a href="?sort=WERT_ABS_TAG_DIFF_<?php echo $ZZeitschnitt; ?>" title="Absolutwerte zum Indikator">
                                    <div title="Sortieren nach..." style="
                                    	cursor:s-resize;
                                        display:block;
                                        padding-top:2px; 
                                        padding-bottom:2px; 
                                        padding-left:10px; 
                                        padding-right:15px; 
                                        margin-right:3px;
                                         text-align:left;
                                        <?php 
                                      if($_SESSION['Tabellen_Sortierung'] == 'WERT_ABS')
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
                                
                                
                                      	Tägliche Flächen-<br />
										inanspruchnahme<br />
                                        von <?php echo $ZZeitschnitt; ?> bis <?php echo $_SESSION['Dokument']['Jahr_Anzeige']; ?><br />
										(ha/d)
									  <?php  $CSV[0][] = 'Fläche (km²)'; 
																
										// Max-Min für Box dieser Spaltenwerte ermitteln
										$SQL_Max = "SELECT 
															MAX(WERT_ABS_TAG_DIFF_".$ZZeitschnitt.") AS MAX, 
															MIN(WERT_ABS_TAG_DIFF_".$ZZeitschnitt.") AS MIN
															FROM t_temp_tabellentool WHERE FEHLERCODE = '0' or FEHLERCODE = ''"; 
										$Ergebnis_Max = mysqli_query($Verbindung,$SQL_Max); 
										$Max_abs_tag_diff[$ZZeitschnitt] = @mysqli_result($Ergebnis_Max,0,'MAX');
										$Min = abs(@mysqli_result($Ergebnis_Max,0,'MIN'));
										if($Min > $Max_abs_tag_diff[$ZZeitschnitt]) $Max_abs_tag_diff[$ZZeitschnitt] = $Min;
												
									?>            
                              </div></a>
                            </th>
                            <?php 
                        }
                        ?>
 
                        
                        <!-- Grundaktualitaet ZZS untere Kopfzeile-->
						<?php
						if($_SESSION['Tabelle']['AKTUALITAET'])
						{
							?>
							<th style=" <?php if($_SESSION['Tabellen_Sortierung'] == 'AKT_'.$ZZeitschnitt){ echo 'background-color:#e0ebc5;'; } ?> ">
						 <a href="?sort=<?php echo 'AKT_'.$ZZeitschnitt ?>&Zusatzzeitschnitt=<?php echo $ZZeitschnitt; ?>">
						   <div title="Sortieren nach..." style=" cursor:s-resize; padding-top:2px; padding-bottom:2px; padding-left:10px; padding-right:10px; margin-right:3px;<?php 
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
									echo 'Mittlere<br />Grund-<br />aktualit&auml;t'; 
									?>
						 </div>
							  </a>
                              <?php  $CSV[0][] = 'Mittlere Grundaktualität '.$ZZeitschnitt; ?>
							</th>
							<?php 
						}
						?>

				
						<!-- absolute Veränderung zum gewählten Basiszeitschnitt ZZS untere Kopfzeile-->
						<?php 
						if($_SESSION['Tabelle']['VERGLEICH'] == '1')
						{
							?>
							<th style=" <?php if($_SESSION['Tabellen_Sortierung'] == 'WERT_DIFF_'.$ZZeitschnitt){ echo 'background-color:#e0ebc5;'; } ?> ">
					   <a href="?sort=<?php echo 'WERT_DIFF_'.$ZZeitschnitt ?>&Zusatzzeitschnitt=<?php echo $ZZeitschnitt; ?>">
						   <div title="Sortieren nach..." style=" cursor:s-resize; padding-top:2px; padding-bottom:2px; padding-left:10px; padding-right:10px; margin-right:3px;<?php 
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
							  Veränderung <br />
						     gesamt <br />
						      (<?php echo $ZZeitschnitt; ?> bis <?php echo $_SESSION['Dokument']['Jahr_Anzeige']; ?>)
                             <?php  $CSV[0][] = 'Differenz '.$ZZeitschnitt; ?>
    				  <?php /* echo $ZZeitschnitt." zu ".$_SESSION['Dokument']['Jahr_Anzeige']; */ ?> 
									<?php 
																
									// Max für Box dieser Spaltenwerte ermitteln
									$SQL_Max = "SELECT 
														MAX(WERT_DIFF_".$ZZeitschnitt.") AS MAX, 
														MIN(WERT_DIFF_".$ZZeitschnitt.") AS MIN
														FROM t_temp_tabellentool WHERE FEHLERCODE = '0' or FEHLERCODE = ''"; 
									$Ergebnis_Max = mysqli_query($Verbindung,$SQL_Max); 
									$Max_abs[$ZZeitschnitt] = @mysqli_result($Ergebnis_Max,0,'MAX');
									$Min = abs(@mysqli_result($Ergebnis_Max,0,'MIN'));
									if($Min > $Max_abs[$ZZeitschnitt]) $Max_abs[$ZZeitschnitt] = $Min;

									?>
					   </div>
							  </a>
                               <?php 
								
								if($_SESSION['Dokument']['ViewBerechtigung'] == "0" or getenv("REMOTE_ADDR")=='127.0.0.1')
								{
									?>
									  <div style="padding-top:2px; padding-bottom:2px; padding-left:10px; padding-right:10px; margin-right:3px;" class="nicht_im_print" >
										  <form action="" method="post">
												
												<input name="KARTENANZEIGE_TYP" type="hidden" value="Entwicklung" />
                                                <input name="KARTENANZEIGE_WERT" type="hidden" value="<?php echo "WERT_DIFF_".$ZZeitschnitt; ?>" />
												<input name="KARTENANZEIGE_FEHLERCODE" type="hidden" value="<?php echo "FEHLERCODE_".$ZZeitschnitt; ?>" />
                                                <input name="KARTENANZEIGE_FEHLERCODE_AKT" type="hidden" value="" />
                                                <input name="KARTENANZEIGE_ZZEITSCHNITT" type="hidden" value="<?php echo $ZZeitschnitt; ?>" />
												<input name="send" type="submit" value="Karte" /> <span style="font-size:12px; font-weight:normal;">(nur für Prüfung!)</span>
												
										  </form>
									  </div>
                                      <?php 
								}
								
								?>
							</th>
							<?php 
						}
						?>
 
	<!-- Grundaktualitaet-Differenz ZZS untere Kopfzeile-->
						<?php
						if($_SESSION['Tabelle']['AKTUALITAET'] and !$MITTLERE_AKTUALITAET_IGNORE)
						{
							?>
							<th style=" <?php if($_SESSION['Tabellen_Sortierung'] == 'AKT_DIFF_'.$ZZeitschnitt){ echo 'background-color:#e0ebc5;'; } ?> ">
						 <a href="?sort=<?php echo 'AKT_DIFF_'.$ZZeitschnitt ?>&Zusatzzeitschnitt=<?php echo $ZZeitschnitt; ?>">
						   <div title="Sortieren nach..." style=" cursor:s-resize; padding-top:2px; padding-bottom:2px; padding-left:10px; padding-right:10px; margin-right:3px;<?php 
						  if($_SESSION['Tabellen_Sortierung'] == 'AKT_DIFF_'.$ZZeitschnitt)
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
									echo 'Aktualit&auml;ts-<br />differenz<br />(Jahre, dezimal)'; 
									?>
                                    <?php  $CSV[0][] = 'Aktualitätsdifferenz '.$ZZeitschnitt; ?>
						 </div>
							  </a>
							</th>
							<?php 
						}
						?>

    <!-- absolute Veränderung pro Jahr (nach Mittl. Grundakt) ZZS untere Kopfzeile-->
						<?php 
						if($_SESSION['Tabelle']['VERGLEICH'] == '1' and $_SESSION['Tabelle']['AKTUALITAET'])
						{
							?>
							<th style=" <?php if($_SESSION['Tabellen_Sortierung'] == 'WERT_DIFF_AKT_JAHRE_'.$ZZeitschnitt){ echo 'background-color:#e0ebc5;'; } ?> ">
					   		<a href="?sort=<?php echo 'WERT_DIFF_AKT_JAHRE_'.$ZZeitschnitt ?>&Zusatzzeitschnitt=<?php echo $ZZeitschnitt; ?>&SortFC=<?php 
																																echo 'WERT_DIFF_AKT_JAHRE_FEHLERCODE_'.$ZZeitschnitt ?>">
						   <div title="Sortieren nach..." style=" cursor:s-resize; padding-top:2px; padding-bottom:2px; padding-left:10px; padding-right:10px; margin-right:3px;<?php 
						  if($_SESSION['Tabellen_Sortierung'] == 'WERT_DIFF_AKT_JAHRE_'.$ZZeitschnitt)
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
							  	Durchschn. jährl. <br />
							  	Veränderung <br />
(<?php echo $ZZeitschnitt; ?> bis <?php echo $_SESSION['Dokument']['Jahr_Anzeige']; ?>) <br />

								
                           <?php  $CSV[0][] = 'Differenz pro Jahr'; ?>
							  <?php /* echo $ZZeitschnitt." zu ".$_SESSION['Dokument']['Jahr_Anzeige']; */ ?> 
									<?php 
																
									// Max für Box dieser Spaltenwerte ermitteln
									$SQL_Max = "SELECT 
														MAX(WERT_DIFF_AKT_JAHRE_".$ZZeitschnitt.") AS MAX_DIFF_JAHRE, 
														MIN(WERT_DIFF_AKT_JAHRE_".$ZZeitschnitt.") AS MIN_DIFF_JAHRE  
														FROM t_temp_tabellentool WHERE (FEHLERCODE = '0' OR FEHLERCODE = '') AND (WERT_DIFF_AKT_JAHRE_FEHLERCODE_".$ZZeitschnitt." = '0' OR WERT_DIFF_AKT_JAHRE_FEHLERCODE_".$ZZeitschnitt." = ''); "; 
									$Ergebnis_Max = mysqli_query($Verbindung,$SQL_Max); 
									$Max_abs_Diff_Jahre[$ZZeitschnitt] = @mysqli_result($Ergebnis_Max,0,'MAX_DIFF_JAHRE');
									$Min_abs_Diff_Jahre = abs(@mysqli_result($Ergebnis_Max,0,'MIN_DIFF_JAHRE'));
									if($Min_abs_Diff_Jahre > $Max_abs_Diff_Jahre[$ZZeitschnitt]) $Max_abs_Diff_Jahre[$ZZeitschnitt] = $Min_abs_Diff_Jahre;
									?>
							</div>
							  </a>
                               <?php 
								if($_SESSION['Dokument']['ViewBerechtigung'] == "0" or getenv("REMOTE_ADDR")=='127.0.0.1')
								{
									?>
									<div style="padding-top:2px; padding-bottom:2px; padding-left:10px; padding-right:10px; margin-right:3px;" class="nicht_im_print" >
										  <form action="#" method="post">
												<input name="KARTENANZEIGE_TYP" type="hidden" value="Entwicklung pro Jahr " />
												<input name="KARTENANZEIGE_WERT" type="hidden" value="<?php echo "WERT_DIFF_AKT_JAHRE_".$ZZeitschnitt; ?>" />
												<input name="KARTENANZEIGE_FEHLERCODE" type="hidden" value="<?php echo "FEHLERCODE_".$ZZeitschnitt; ?>" />
                                                <input name="KARTENANZEIGE_FEHLERCODE_AKT" type="hidden" value="<?php echo "WERT_DIFF_AKT_JAHRE_FEHLERCODE_".$ZZeitschnitt; ?>" />
                                                <input name="KARTENANZEIGE_ZZEITSCHNITT" type="hidden" value="<?php echo $ZZeitschnitt; ?>" />
												<input name="send" type="submit" value="Karte" />
												
										  </form>
									</div>
                                 	<?php 
								}
								?>
							</th>
							<?php 
						}
						?>
                        
    
						<!-- Uebergeordnete RE ZZS untere Kopfzeile-->
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
										 <a href="?sort=<?php echo 'WERT_KRS_'.$ZZeitschnitt ?>&Zusatzzeitschnitt=<?php echo $ZZeitschnitt; ?>">
										   <div title="Sortieren nach..." style=" cursor:s-resize; padding-top:2px; padding-bottom:2px; padding-left:10px; padding-right:10px; margin-right:3px;<?php 
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
											echo 'Kreis ('.$ZZeitschnitt.')';?>
										<br />
							(<?php echo $_SESSION['Dokument']['Fuellung']['Indikator_Einheit']; ?>)

											
										
                        <?php  $CSV[0][] = 'Kreis '.$ZZeitschnitt; ?>
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
										 <a href="?sort=<?php echo 'WERT_BLD_'.$ZZeitschnitt ?>&Zusatzzeitschnitt=<?php echo $ZZeitschnitt; ?>">
										   <div title="Sortieren nach..." style=" cursor:s-resize; padding-top:2px; padding-bottom:2px; padding-left:10px; padding-right:10px; margin-right:3px;<?php 
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
										echo 'Bundesland';
										?><br />
							(<?php echo $_SESSION['Dokument']['Fuellung']['Indikator_Einheit']; ?>)

                                        <?php  $CSV[0][] = 'Bundesland '.$ZZeitschnitt; ?>
									</div>
								   </a>
								</th>
							<?php
							}
						}
						if($_SESSION['Tabelle']['UERE_BND'])
						{
							
							?>
								<!-- Bundesland ZZS untere Kopfzeile-->
								<th style=" color:#444444;">
									 <div style="padding-top:2px; padding-bottom:2px; padding-left:10px; padding-right:10px; margin-right:3px;">
										<?php 
										echo 'Bundesrepublik';
										?><br />
							(<?php echo $_SESSION['Dokument']['Fuellung']['Indikator_Einheit']; ?>)

                                        <?php  $CSV[0][] = 'Bundesrepublik '.$ZZeitschnitt; ?>
									</div>
								</th>
							<?php
						}
					}
				}
			}
			} //Ende zusätzliche Zeitschnitte untere Kopfzeile--------------------------------------------------------------------------------------------
	
			?>
	  </tr>
	
	<?php 
	//Ende Tabellenkopf--------------------------------------------------------------------------------------------
	
	

	
	// --------------------------------------- DatenZeilen ------------------------------------------------------------------------------------------------------------------------------
	
	// Erstellen der Sortieranweisungen für SQL
	if($_SESSION['Tabellen_Sortierung'])
	{
		// Collation anwenden, wenn nach Name sortiert wird (DIN 5007) ... sonst leer lassen
		if($_SESSION['Tabellen_Sortierung']=="NAME") $SQL_COLLATE = "COLLATE utf8_general_ci"; 
		
		// Prüfen, ob nach Inhalten eines Zusatzzeitschnitts sortiert wird ... dann Zeitschnitt-Fehlercode mit auswerten!
		if($_SESSION['Tabellen_Sortierung_ZZ']) $ZZ_Fehlercode_Sort = ", FEHLERCODE_".$_SESSION['Tabellen_Sortierung_ZZ'];
		// Sortieroption ausformulieren
		$SQL_Order = $ZZ_Fehlercode_Sort.", ".$_SESSION['Tabellen_Sortierung']." ".$SQL_COLLATE." ".$_SESSION['Tabellen_Sortierung_asc_desc'].";";
	}
	if($_SESSION['Tabellen_Sortierung_FC']) $SQL_Order_FC = ",".$_SESSION['Tabellen_Sortierung_FC']. " ASC ";
	
	$SQL_ANZEIGE = "SELECT * FROM t_temp_tabellentool ORDER BY FEHLERCODE ASC ".$SQL_Order_FC." ".$SQL_Order;
	// Check, ob Abfrage erfolgreich war, ansnsten Ausführung mit Standard-Sortierung und setzen dieser in SESSION-Array (da evtl. fehlerhaft durch Ausblendung eines Zusatzzeitschnittes)
	if(!$Ergebnis_ANZEIGE = mysqli_query($Verbindung,$SQL_ANZEIGE))
	{
		$_SESSION['Tabellen_Sortierung'] = "NAME"; 
		// if(!$_SESSION['Tabellen_Sortierung_asc_desc']) $_SESSION['Tabellen_Sortierung_asc_desc'] = "ASC";
		if(!$_SESSION['Tabellen_Sortierung_asc_desc']) $SQL_Order = ", ".$_SESSION['Tabellen_Sortierung']." ".$SQL_COLLATE." ".$_SESSION['Tabellen_Sortierung_asc_desc'].";";
		$SQL_ANZEIGE = "SELECT * FROM t_temp_tabellentool ORDER BY FEHLERCODE ASC ".$SQL_Order_FC." ".$SQL_Order;
		$Ergebnis_ANZEIGE = mysqli_query($Verbindung,$SQL_ANZEIGE);
	}
	


	
	$i_anz = 0;
	$i_zaehl = 1;
	while($ags = @mysqli_result($Ergebnis_ANZEIGE,$i_anz,'AGS'))
	{
		
		// Test auf Fehlercode
		if($FC = @mysqli_result($Ergebnis_ANZEIGE,$i_anz,'FEHLERCODE'))
		{
			$SQL_FC = "SELECT * FROM m_fehlercodes WHERE FEHLERCODE = '".$FC."'";
			$Ergebnis_FC = mysqli_query($Verbindung,$SQL_FC);
					
			$Fehlername = utf8_encode(mysqli_result($Ergebnis_FC,0,'FEHLER_BESCHREIBUNG'));
			$Fehlerbschreibung = utf8_encode(mysqli_result($Ergebnis_FC,0,'FEHLER_BESCHREIBUNG'));
			$Fehlerfarbcode = mysqli_result($Ergebnis_FC,0,'FEHLER_FARBCODE');
			$FC_OgrWert = $FC;
		}
		else
		{
			$Fehlername = NULL;
			$Fehlerbschreibung = NULL;
			$Fehlerfarbcode = NULL;
			$FC_OgrWert = NULL;
		}
		
		?>
		<tr id="Zeile_<?php echo $ags; ?>" style=" <?php 
			if($_SESSION['Tabelle']['Markierung'][$ags]) 
			{ 
				?> border:2px solid #933; 
				<?php
			}
			else
			{
				?> border: 0px; <?php
            }
			?>    
           background:<?php if($i_zaehl % 5 == 0) { echo '#EEEEEE'; }else{ echo '#FFFFFF'; }?>;" >
           
           
		  <?php 
		  // vorerst nur für Admins sichtbar
		  if($_SESSION['Dokument']['ViewBerechtigung'] == "0")
		  { 
			  ?>
              <td style="text-align:right;">
			  <?php 
                if($_SESSION['Tabelle']['AGS_IGNORE'][$ags])
                {
                    ?><a href="?aktivieren=<?php echo $ags; ?>"><input name="inakt" type="checkbox" value="" />
                    <!--<img src="../gfx/button_haken_inaktiv.png" alt="inaktiv" width="16" height="15" title="Raumeinheit ist ausgeblendet." /> --></a>
                    <?php 
                }
                else
                {
                    ?><a href="?deaktivieren=<?php echo $ags; ?>"><input name="akt" type="checkbox" value="" checked />
                    <!--<img src="../gfx/button_haken_aktiv.png" alt="aktiv" width="16" height="15" title="Raumeinheit wird angezeigt." /> --></a>
                    <?php 
                }
              ?>
          </td>
           
          <?php 
		}
		?>
          
         <!-- Markierung --> 
		<td style="text-align:right;">
         <?php 
			// Schalter auch unter Berücksichtigung früherer Auswahlen korrekt anzeigen
			?>
            <!-- Markierung nicht gesetzt -->
            <a id="Marker_<?php echo $ags; ?>" onclick="markieren('<?php echo $ags; ?>','#aa00aa');" style="display:<?php 
				if($_SESSION['Tabelle']['Markierung'][$ags]) 
				{
					?>none<?php
				}
				else
				{ 
					?>inline<?php
				 
				}
			?>;"><div><img src="../icons_viewer/markierung_inaktiv.png" alt="aktiv" width="16" height="15" title="markieren" /></div></a>
            
            <?php
			// Markierung auch in SVG übernehmen wenn gesetzt ... wird sonst übergangen!?
            /* if($_SESSION['Tabelle']['Markierung'][$ags]) 
			{
				?>
                <script type="text/javascript">
					markieren('<?php echo $ags; ?>','#aa00aa');
				</script> 
            	<?php 
			} */
			?>
            
            <!-- Markierung gesetzt -->    
          	<a id="DeMarker_<?php echo $ags; ?>" onclick="demarkieren('<?php echo $ags; ?>');" style="display:<?php 
				if($_SESSION['Tabelle']['Markierung'][$ags]) 
				{
					?>inline<?php
					 
				}
				else
				{ 
					?>none<?php 
					
				}
			?>;"><div><img src="../icons_viewer/markierung_aktiv.png" alt="aktiv" width="16" height="15" title="markiert" /></div></a>
                
				<?php
				// Markierung in der ! Karte ! anbringen
                if($_SESSION['Tabelle']['Markierung'][$ags]) 
				{
					?>
					<script type="text/javascript">			
						/* Verzögerter Funktionsaufruf, da evtl. das SVG noch nicht geladen/fertiggestellt ist
						 ... + mehrfacher Aufruf, um sehr lange Ladezeiten zu berücksichtigen, aber schnell anzeigen zu können 	
						setTimeout("markieren_bei_reload('<?php echo $ags; ?>','#aa00aa')", 3000);	
						setTimeout("markieren_bei_reload('<?php echo $ags; ?>','#aa00aa')", 3000); */
						 markieren_bei_reload('<?php echo $ags; ?>','#aa00aa'); 
					</script>
                    
                	<?php
				}
 	
          ?>
          </td>
          
          
		  <td style="text-align:right;"><?php 
		  	echo $i_zaehl; 
		  	$CSV[$i_zaehl][] = $i_zaehl;
		  	?>
          </td>
			<!-- AGS -->
			<td>
				<?php 
				echo $ags;  
				$CSV[$i_zaehl][] = $ags;
				?>
      </td>
			<!-- Name -->
			<td>
                
                <form style="display:inline;" target="_blank" action="tabelle_indikatorenvergleich.php" method="post" name="form_ags_<?php echo $ags; ?>">       
                   	  <input type="image" src="../icons_viewer/indikatoren.png" title="Gebietsprofil: Charakteristik dieser Raumeinheit mit Werteübersicht aller Indikatoren" alt="Mehr Indikatoren"> 
                      <input name="ags" type="hidden" value="<?php echo $ags; ?>" />
                      <input name="name" type="hidden" value="<?php echo @mysqli_result($Ergebnis_ANZEIGE,$i_anz,'NAME'); ?>" />
                      <input name="name_KRS" type="hidden" value="<?php echo @mysqli_result($Ergebnis_ANZEIGE,$i_anz,'NAME_KRS'); ?>" />
                      <input name="name_BLD" type="hidden" value="<?php echo @mysqli_result($Ergebnis_ANZEIGE,$i_anz,'NAME_BLD'); ?>" />
                      <a href="#" onclick="document.form_ags_<?php echo $ags; ?>.submit();" style="cursor: pointer;" title="Gebietsprofil: Charakteristik dieser Raumeinheit mit Werteübersicht aller Indikatoren" >&nbsp;&nbsp;<?php 
					  																		echo $CSV[$i_zaehl][] = @mysqli_result($Ergebnis_ANZEIGE,$i_anz,'NAME');  
					  																		?></a>
                </form>
                
                 
			</td>
            
            
			<!-- Wert -->
			<td style=" text-align:right;">
            	 <form style="display:inline;"  target="_blank" action="objektinformationen.php" method="post" name="form_wert_<?php echo $ags; ?>">
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
                        
						<?php   
						if(!$FC) 
						{  
                            // Test: echo @mysqli_result($Ergebnis_ANZEIGE,$i_anz,'HINWEISCODE');
                            // Hinweis anzeigen
							if($HC_gesamt = @mysqli_result($Ergebnis_ANZEIGE,$i_anz,'HINWEISCODE'))
							{
								// Mehrere Hinweiscodes berücksichtigen
								if(strlen($HC_gesamt) > 2)
								{
									$HC = strtok($HC_gesamt, ",");
									while ($HC !== false) {
										?>
                                        <img src="../icons_viewer/hinweis_<?php echo $HC; ?>.png" width="16" height="14" alt="Hinweis" title="<?php echo $HC_Definition[$HC]['HC_NAME']; ?>" />
                                        <?php
										$HC = strtok(",");	
									}
								}
								else
								{
									?>
									<img src="../icons_viewer/hinweis_<?php echo $HC_gesamt; ?>.png" width="16" height="14" alt="Hinweis" title="<?php echo $HC_Definition[$HC_gesamt]['HC_NAME']; ?>" />
									<?php
								}
							}
                            
							// Wert anzeigen
							// ... kein Inputfeld mehr, dadurch wird die Darstellung anders... echo '<input name="" style="border:0px; background:none; font-weight:bold;" type="submit" value="';
							echo '<span style="font-weight: bold;">';
							if($_SESSION['Dokument']['ViewBerechtigung'] == "0")
							{ 
								echo $CSV[$i_zaehl][] = number_format($x = (@mysqli_result($Ergebnis_ANZEIGE,$i_anz,'WERT') - 0),4, ',', '.'); 
							}
							else
							{ 
								echo $CSV[$i_zaehl][] = number_format($x = (@mysqli_result($Ergebnis_ANZEIGE,$i_anz,'WERT') - 0),$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.'); 
							}
							echo '</span>';
							// echo '" />';
                            ?>
                            &nbsp;&nbsp;
				   			<input type="image" src="../icons_viewer/histogramm.png" alt="Info" title="Indikatorwert der Gebietseinheit in Bezug auf statistische Kenngrößen der räumlichen Auswahl und des gewählten Indikators">
							<?php 
						}
						else
						{ 
							?><span style="color:#<?php echo $Fehlerfarbcode; ?>;"><?php echo $CSV[$i_zaehl][] = $Fehlername; ?></span><?php 
						} 
						?>  
              </form>
              
              <?php 		 
			// Für Gemeinden derzeit ausgeblendet, da die Daten zu unzuverlässig sind (für Prüfer immer an)
			if($_SESSION['Dokument']['Raumgliederung'] != "gem" or $_SESSION['Dokument']['ViewBerechtigung'] == "0")
			{	  
				?>
                <form style="display:inline;" target="_blank" action="svg_graphen.php" method="post" name="form_ags_<?php echo $ags; ?>_diagr"> 
                	  <input name="neu" type="hidden" value="1" />      
                   	  <input type="image" src="../icons_viewer/indikatoren_diagr.png" title="Veränderung der Indikatorwerte für die Gebietseinheit" alt="Diagramm"> 
                      <input name="ags" type="hidden" value="<?php echo $ags; ?>" />
                      <input name="name" type="hidden" value="<?php echo @mysqli_result($Ergebnis_ANZEIGE,$i_anz,'NAME'); ?>" />
                      <input name="Raumgliederung" type="hidden" value="<?php echo $_SESSION['Dokument']['Raumgliederung']; ?>" />
                      <input name="Indikator_Tab" type="hidden" value="<?php echo $_SESSION['Dokument']['Fuellung']['Indikator']; ?>" />
                      <input name="Indikator_Tab2" type="hidden" value="<?php echo $_SESSION['Tabelle']['Indikator_2']; ?>" />
                </form>
               <?php 
			}
			  ?>
			</td>                       
           
            <!-- Absolutwert-Indikator -->
            <?php 
			// Spalte erweitern wenn Absolut-Ind angezeigt und IND = ...RG
			if($_SESSION['Tabelle']['WERT_ABS'] and substr($_SESSION['Dokument']['Fuellung']['Indikator'],3,2)=='RG')
			{
				?>
                <td class="changerow" style=" text-align:right;">
                    <?php   
											
						if(!$FC) 
						{  

							// ABS-Wert anzeigen
							echo '<span style="border:0px; background:none; font-weight:bold; ';
							// Verwirrt eher: if($HC = @mysqli_result($Ergebnis_ANZEIGE,$i_anz,'HINWEISCODE')) { echo ' color:#995500; '; }
							echo '">';
							if($_SESSION['Dokument']['ViewBerechtigung'] == "0")
							{ 
								if ($Trend_ZS_vorh != '1'){echo $CSV[$i_zaehl][] = number_format($x = (@mysqli_result($Ergebnis_ANZEIGE,$i_anz,'WERT_ABS') - 0),4, ',', '.'); }
							}
							else
							{ 
								// Rundung um 1 erhöhen bei Flächenausgabe:
							if ($Trend_ZS_vorh != '1'){	echo $CSV[$i_zaehl][] = number_format($x = (@mysqli_result($Ergebnis_ANZEIGE,$i_anz,'WERT_ABS') - 0),$RundFG = ($_SESSION['Dokument']['Fuellung']['Rundung']+1), ',', '.'); }
							}
							echo '</span>';
                           
						}
						else
						{ 
							?><span style="color:#<?php echo $Fehlerfarbcode; ?>;"><?php if ($Trend_ZS_vorh != '1'){ echo $CSV[$i_zaehl][] = $Fehlername; }?></span><?php  
						} 
					?>
                </td>
                
                <?php 
			}
			?>           
            
            
            
            <!-- Wert für 2. Indikator -->
            <?php 
			// Spalte erweitern wenn 2. Ind angezeigt
			if($_SESSION['Tabelle']['Indikator_2']) 
			{
				?>
                <td style=" text-align:right;">
                    <?php   
						
						
						
						
						// Test auf Fehlercode_2
						if($FC_2 = @mysqli_result($Ergebnis_ANZEIGE,$i_anz,'FEHLERCODE_2'))
						{
							$SQL_FC_2 = "SELECT * FROM m_fehlercodes WHERE FEHLERCODE = '".$FC_2."'";
							$Ergebnis_FC_2 = mysqli_query($Verbindung,$SQL_FC_2);
									
							$Fehlername_2 = utf8_encode(mysqli_result($Ergebnis_FC_2,0,'FEHLER_BESCHREIBUNG'));
							$Fehlerbschreibung_2 = utf8_encode(mysqli_result($Ergebnis_FC_2,0,'FEHLER_BESCHREIBUNG'));
							$Fehlerfarbcode_2 = mysqli_result($Ergebnis_FC_2,0,'FEHLER_FARBCODE');
							$FC_OgrWert_2 = $FC_2;
						}
						else
						{
							$Fehlername_2 = NULL;
							$Fehlerbschreibung_2 = NULL;
							$Fehlerfarbcode_2 = NULL;
							$FC_OgrWert_2 = NULL;
						}
											
						
						if(!$FC_2) 
						{  
                            /* ... fehlerhaft, da Hinweiscodes hier nicht die gleichen, wie beim Hauptindikator sind
                            // Hinweis anzeigen
							if($HC_2 = @mysqli_result($Ergebnis_ANZEIGE,$i_anz,'HINWEISCODE_2'))
							{
								
								?>
								<img src="../icons_viewer/hinweis_<?php echo $HC; ?>.png" width="16" height="14" alt="Hinweis" title="<?php echo $HC_Definition[$HC]['HC_NAME']; ?>" />
                                
								<?php 
								
							}
                            */
							
							// Wert anzeigen
							echo '<span style="border:0px; background:none; font-weight:bold; ';
							if($HC_2 = @mysqli_result($Ergebnis_ANZEIGE,$i_anz,'HINWEISCODE_2')) { echo ' color:#995500; '; }
							echo '">';
							if($_SESSION['Dokument']['ViewBerechtigung'] == "0")
							{ 
								if ($Trend_ZS_vorh != '1'){echo $CSV[$i_zaehl][] = number_format($x = (@mysqli_result($Ergebnis_ANZEIGE,$i_anz,'WERT_2') - 0),4, ',', '.'); }
							}
							else
							{ 
							if ($Trend_ZS_vorh != '1'){	echo $CSV[$i_zaehl][] = number_format($x = (@mysqli_result($Ergebnis_ANZEIGE,$i_anz,'WERT_2') - 0),$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.'); }
							}
							echo '</span>';
                           
						}
						else
						{ 
							?><span style="color:#<?php echo $Fehlerfarbcode_2; ?>;"><?php echo $CSV[$i_zaehl][] = $Fehlername_2; ?></span><?php 
						} 
					?>
                </td>
                
                <?php 
			}
			?>                     
                        
            <!-- Gebietsfläche Datenzeilen-->
            <?php 
			// Spalte erweitern wenn 2. Ind angezeigt
			if($_SESSION['Tabelle']['FLAECHE']) 
			{
				?>
                <td class="changerow" style=" text-align:right;">
                    <?php   
						   	// Wert anzeigen
							echo '<span style="border:0px; background:none;">';
							if($_SESSION['Dokument']['ViewBerechtigung'] == "0")
							{ 
							if ($Trend_ZS_vorh != '1'){echo $CSV[$i_zaehl][] = number_format($x = (@mysqli_result($Ergebnis_ANZEIGE,$i_anz,'FLAECHE') - 0),4, ',', '.'); }
							}
							else
							{ 
							if ($Trend_ZS_vorh != '1'){echo $CSV[$i_zaehl][] = number_format($x = (@mysqli_result($Ergebnis_ANZEIGE,$i_anz,'FLAECHE') - 0),$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.'); }
							}
							echo '</span>';                       
					
					?>
                </td>
      <?php 
			}	?>
            
                        
             <!-- Einwohnerzahl Datenzeilen-->
            <?php 
			// Spalte erweitern wenn 2. Ind angezeigt
			if($_SESSION['Tabelle']['EWZ'] && $_SESSION['Dokument']['Raumgliederung']!=='stt') 
			{
				?>
         <td class="changerow" style=" text-align:right;">
            <?php   
						// Wert anzeigen
							echo '<span style="border:0px; background:none;">';
							if ($Trend_ZS_vorh != '1'){	$CSV[$i_zaehl][] = $EWZ_ausg = number_format($x = (@mysqli_result($Ergebnis_ANZEIGE,$i_anz,'EWZ') - 0),0, ',', '.'); }
							// alt/nicht aktiv:"-" bei fehlender EWZ ausgeben
					  	if ($EWZ_ausg == "-1")
							{
								echo "-";
							}
							else
							{
								echo $EWZ_ausg;
							}
							echo '</span>';                        
						
					?>
                </td>                
<?php 	}
			?>
            
            
            
            
			<!-- Grundaktualitaet -->
			<?php
			if($_SESSION['Tabelle']['AKTUALITAET'])
			{
				?>
				<td class="changerow" style="color:#777; text-align:center;">
					<?php 
					if ($Trend_ZS_vorh != '1'){	if(!$FC) { echo $CSV[$i_zaehl][] = @mysqli_result($Ergebnis_ANZEIGE,$i_anz,'AKT_AUSGABE'); }  else { $CSV[$i_zaehl][] = 'Fehler';  }}
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
                     <td class="changerow" style="background:#CCC; width:3px; padding:0px;" >&nbsp;</td>
                    <!-- Kreis -->
					<td class="changerow" >
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
									$Box_width = abs(49.5 * (@mysqli_result($Ergebnis_ANZEIGE,$i_anz,'WERT_KRS_DIFF')/$Max_diff_krs));  
								/* }
								else
								{ 	$Box_width=0; } */
								
								// Positionierung der Box von der Mitte aus
								if(@mysqli_result($Ergebnis_ANZEIGE,$i_anz,'WERT_KRS_DIFF') < 0) 
								{
									// Dynamisch den Platz vor der Box berechnen und als margin belegen
									$Box_Margin_left = 50 - $Box_width; 
									if(!$Box_width) $Box_Margin_left = "0"; // Falls Variable leer.... vermeidet XHTML Fehler
									$Box_BackColor = "#".$_SESSION['Dokument']['Fuellung']['Farbwert_Diff_min'];
									$Textpos_align = "left";
									$Border_seite = "right";
								}
								else
								{
									$Box_Margin_left = 50; // bei >0 ist 50% immer korrekt
									$Box_BackColor = "#".$_SESSION['Dokument']['Fuellung']['Farbwert_Diff_max'];
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
									if ($Trend_ZS_vorh != '1'){	echo $CSV[$i_zaehl][] = number_format(@mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_ZZS='WERT_KRS_DIFF'),$rndg_p=($_SESSION['Dokument']['Fuellung']['Rundung']+1), ',', '.');}
									}
									else
									{ 
								if ($Trend_ZS_vorh != '1'){		echo $CSV[$i_zaehl][] =  number_format(@mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_ZZS='WERT_KRS_DIFF'),$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.');}
									}
									?>
								</div>
								<?php 
							} 
							else 
							{ 
							if ($Trend_ZS_vorh != '1'){	$CSV[$i_zaehl][] = 'Fehler'; }
							}
							
							?>
						</div>
					  </td>
					  <td class="changerow" >
						<?php 
						if(!$FC) 
						{ 
							if ($Trend_ZS_vorh != '1'){echo $CSV[$i_zaehl][] = number_format(@mysqli_result($Ergebnis_ANZEIGE,$i_anz,'WERT_KRS'),$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.')." (".@mysqli_result($Ergebnis_ANZEIGE,$i_anz,'NAME_KRS').")"; }
						}
						?>
						</td>
						<!-- Grundaktualitaet KRS -->
						<?php
						if($_SESSION['Tabelle']['AKTUALITAET'])
						{
							?>
							<td class="changerow" style="color:#777; text-align:center;">
								<?php 
								if ($Trend_ZS_vorh != '1'){	if(!$FC) { echo $CSV[$i_zaehl][] = @mysqli_result($Ergebnis_ANZEIGE,$i_anz,'AKT_KRS_AUSGABE'); } else { $CSV[$i_zaehl][] = 'Fehler'; }}
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
					<td class="changerow"  style="background:#CCC; width:3px; padding:0px;" >&nbsp;</td>
					<!-- Bundesland -->
					<td class="changerow" >
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
									$Box_width = abs(49.5 * (@mysqli_result($Ergebnis_ANZEIGE,$i_anz,'WERT_BLD_DIFF')/$Max_diff_bld));  
								/* }
								else
								{ 	$Box_width=0; } */
								
								// Positionierung der Box von der Mitte aus
								if(@mysqli_result($Ergebnis_ANZEIGE,$i_anz,'WERT_BLD_DIFF') < 0) 
								{
									// Dynamisch den Platz vor der Box berechnen und als margin belegen
									$Box_Margin_left = 50 - $Box_width; 
									if(!$Box_width) $Box_Margin_left = "0"; // Falls Variable leer.... vermeidet XHTML Fehler
									$Box_BackColor = "#".$_SESSION['Dokument']['Fuellung']['Farbwert_Diff_min'];
									$Textpos_align = "left";
									$Border_seite = "right";
								}
								else
								{
									$Box_Margin_left = 50; // bei >0 ist 50% immer korrekt
									$Box_BackColor = "#".$_SESSION['Dokument']['Fuellung']['Farbwert_Diff_max'];
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
									if ($Trend_ZS_vorh != '1'){	echo $CSV[$i_zaehl][] = number_format(@mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_ZZS='WERT_BLD_DIFF'),$rndg_p=($_SESSION['Dokument']['Fuellung']['Rundung']+1), ',', '.');}
									}
									else
									{ 
										if ($Trend_ZS_vorh != '1'){echo $CSV[$i_zaehl][] = number_format(@mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_ZZS='WERT_BLD_DIFF'),$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.');}
									}
									?>
								</div>
								<?php 
							}
							else
							{
								if ($Trend_ZS_vorh != '1'){$CSV[$i_zaehl][] = 'Fehler';	}
							}
							?>
						</div>
					</td>
					<td class="changerow">
						<?php 
						if(!$FC) 
						{ 
							if ($Trend_ZS_vorh != '1'){echo $CSV[$i_zaehl][] = number_format(@mysqli_result($Ergebnis_ANZEIGE,$i_anz,'WERT_BLD'),$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.')." (".@mysqli_result($Ergebnis_ANZEIGE,$i_anz,'NAME_BLD').")"; }
						}
						else
						{
							if ($Trend_ZS_vorh != '1'){$CSV[$i_zaehl][] = 'Fehler';}
						}
						?>
					</td>
					<!-- Grundaktualitaet BLD -->
					<?php
					if($_SESSION['Tabelle']['AKTUALITAET'])
					{
						?>
						<td class="changerow" style="color:#777; text-align:center;">
							<?php 
								if(!$FC) { echo $CSV[$i_zaehl][] = @mysqli_result($Ergebnis_ANZEIGE,$i_anz,'AKT_BLD_AUSGABE'); } else { $CSV[$i_zaehl][] = 'Fehler'; }
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
					<td class="changerow" style="background:#CCC; width:3px; padding:0px;" >&nbsp;</td>
					<!-- Bund -->
					<td class="changerow" >
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
									$Box_width = abs(49.5 * (@mysqli_result($Ergebnis_ANZEIGE,$i_anz,'WERT_BND_DIFF')/$Max_diff_bnd));  
								/* }
								else
								{ 	$Box_width=0; } */
								
								// Positionierung der Box von der Mitte aus
								if(@mysqli_result($Ergebnis_ANZEIGE,$i_anz,'WERT_BND_DIFF') < 0) 
								{
									// Dynamisch den Platz vor der Box berechnen und als margin belegen
									$Box_Margin_left = 50 - $Box_width; 
									if(!$Box_width) $Box_Margin_left = "0"; // Falls Variable leer.... vermeidet XHTML Fehler
									$Box_BackColor = "#".$_SESSION['Dokument']['Fuellung']['Farbwert_Diff_min'];
									$Textpos_align = "left";
									$Border_seite = "right";
								}
								else
								{
									$Box_Margin_left = 50; // bei >0 ist 50% immer korrekt
									$Box_BackColor = "#".$_SESSION['Dokument']['Fuellung']['Farbwert_Diff_max'];
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
									if ($Trend_ZS_vorh != '1'){	echo $CSV[$i_zaehl][] = number_format(@mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_ZZS='WERT_BND_DIFF'),$rndg_p=($_SESSION['Dokument']['Fuellung']['Rundung']+1), ',', '.');}
									}
									else
									{ 
									if ($Trend_ZS_vorh != '1'){	echo $CSV[$i_zaehl][] = number_format(@mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_ZZS='WERT_BND_DIFF'),$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.');}
									}
									?>
								</div>
								<?php 
							}
							else
							{
								if ($Trend_ZS_vorh != '1'){$CSV[$i_zaehl][] = 'Fehler';}
							}
							?>
						</div>
					</td>
					<td class="changerow" >
						<?php 
						if(!$FC) 
						{ 
						if ($Trend_ZS_vorh != '1'){	echo $CSV[$i_zaehl][] = number_format(@mysqli_result($Ergebnis_ANZEIGE,$i_anz,'WERT_BND'),$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.'); }
						}
						else
						{
							if ($Trend_ZS_vorh != '1'){$CSV[$i_zaehl][] = 'Fehler';}
						}
						?>
					</td>
					<!-- Grundaktualitaet BND -->
					<?php
					if($_SESSION['Tabelle']['AKTUALITAET'])
					{
						?>
						<td class="changerow" style="color:#777; text-align:center;">
							<?php 
								if(!$FC) { if ($Trend_ZS_vorh != '1'){echo $CSV[$i_zaehl][] = @mysqli_result($Ergebnis_ANZEIGE,$i_anz,'AKT_BND_AUSGABE'); }}
								else
								{
									if ($Trend_ZS_vorh != '1'){$CSV[$i_zaehl][] = 'Fehler';}
								} 
							?>
						</td>
						<?php 
					}
			}

			
			
			// ------------------------------------ Zusätzliche Zeitschnitte ausgeben Datenzeilen--------------------------------------------
			
			if(is_array($Zeitschnitte_vorh))
			{
				foreach($Zeitschnitte_vorh as $ZZeitschnitt)
				{
						if(($ZZeitschnitt != $_SESSION['Dokument']['Jahr_Anzeige'] and $_SESSION['Tabelle']['Zeitschnitt_Zusatz'][$ZZeitschnitt] == 1)and ($ZZeitschnitt <'2024' or ($ZZeitschnitt >= '2025' and   $_SESSION['Tabelle']['Trend_Berechtigung'] == '1' && $Zus_ZS_vorh != '1') ) ) // Aktuellen Zeitschnitt nicht berücksichtigen!
					{
						
						// Test auf Fehlercode im Zusatzzeitschnitt
						if($FC = @mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_FC = 'FEHLERCODE_'.$ZZeitschnitt))
						{
							$SQL_FC = "SELECT * FROM m_fehlercodes WHERE FEHLERCODE = '".$FC."'";
							$Ergebnis_FC = @mysqli_query($Verbindung,$SQL_FC);
									
							$Fehlername = utf8_encode(@mysqli_result($Ergebnis_FC,0,'FEHLER_BESCHREIBUNG'));
							$Fehlerbschreibung = utf8_encode(@mysqli_result($Ergebnis_FC,0,'FEHLER_BESCHREIBUNG'));
							$Fehlerfarbcode = @mysqli_result($Ergebnis_FC,0,'FEHLER_FARBCODE');
							
							
							if(!$Fehlername)
							{
								$Fehlername = utf8_encode('Unbekannter Fehler');
								$Fehlerbschreibung = utf8_encode('Unbekannter Fehler');
								$Fehlerfarbcode = utf8_encode('990000');
							}
						}
						else
						{
							$Fehlername = NULL;
							$Fehlerbschreibung = NULL;
							$Fehlerfarbcode = NULL;
						}
						
						?>
						<!-- Trenner -->
						<td style="background:#CCC;" >&nbsp;</td>
						 
                        
						<!-- Wert -->
						<td style="text-align:right; font-weight:bold;">
							
								<?php 
								if(!$FC) 
								{  
									
									// Hinweis anzeigen
									/* if($HC = @mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_HC_ZZS='HINWEISCODE_'.$ZZeitschnitt))
									{
										?>
										<img src="../icons_viewer/hinweis_<?php echo $HC; ?>.png" width="16" height="14" alt="Hinweis" title="<?php echo $HC_Definition[$HC]['HC_NAME']; ?>" />&nbsp;&nbsp;
										
						  <?php
									} */
									 // Hinweis anzeigen
									if($HC_gesamt =  @mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_HC_ZZS='HINWEISCODE_'.$ZZeitschnitt))
									{
										// Mehrere Hinweiscodes berücksichtigen
										if(strlen($HC_gesamt) > 2)
										{
											$HC = strtok($HC_gesamt, ",");
											while ($HC !== false) {
												?>
												<img src="../icons_viewer/hinweis_<?php echo $HC; ?>.png" width="16" height="14" alt="Hinweis" title="<?php echo $HC_Definition[$HC]['HC_NAME']; ?>" />
												<?php
												$HC = strtok(",");	
											}
										}
										else
										{
											?>
											<img src="../icons_viewer/hinweis_<?php echo $HC_gesamt; ?>.png" width="16" height="14" alt="Hinweis" title="<?php echo $HC_Definition[$HC_gesamt]['HC_NAME']; ?>" />
											<?php
										}
									}
	
									
									if($_SESSION['Dokument']['ViewBerechtigung'] == "0")
									{ 
										echo $CSV[$i_zaehl][] = number_format($x = (@mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_ZZS='WERT_'.$ZZeitschnitt) - 0),4, ',', '.');
									}
									else
									{ 
										echo $CSV[$i_zaehl][] = number_format($x = (@mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_ZZS='WERT_'.$ZZeitschnitt) - 0),$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.');
									}
								}
								else
								{ 
									?><span style="color:#<?php echo $Fehlerfarbcode; ?>;"><?php echo $CSV[$i_zaehl][] = $Fehlername; ?></span><?php 
								}  
								?>
						</td>  
							<?php                       
     		//Viele Spalten bei normalen Zeitschnitten anhängen
					if($ZZeitschnitt != '2025' and 	$ZZeitschnitt != '2030')
						{?>           
                                   
                      

					 <!-- Wert Absolut-Indikator -->
						 <?php 
                        // Spalte erweitern wenn Absolutwert-Ind angezeigt und Ind = ...RG
                        if($_SESSION['Tabelle']['WERT_ABS'] and substr($_SESSION['Dokument']['Fuellung']['Indikator'],3,2)=='RG') 
                        {
                            ?>
                            <td style=" text-align:right;">
                                <?php   
                                                        
                                    if(!$FC) 
                                    {  
            
                                        // ABS-Wert anzeigen
                                        echo '<span style="border:0px; background:none; font-weight:bold; ';
                                        // if($HC = @mysqli_result($Ergebnis_ANZEIGE,$i_anz,'HINWEISCODE')) { echo ' color:#995500; '; }
                                        echo '">';
                                        if($_SESSION['Dokument']['ViewBerechtigung'] == "0")
                                        { 
                                            echo $CSV[$i_zaehl][] = number_format($x = (@mysqli_result($Ergebnis_ANZEIGE,$i_anz,'WERT_ABS_'.$ZZeitschnitt) - 0),4, ',', '.'); 
                                        }
                                        else
                                        { 
                                            // Rundung um 1 erhöhen bei Flächenausgabe:
                                            echo $CSV[$i_zaehl][] = number_format($x = (@mysqli_result($Ergebnis_ANZEIGE,$i_anz,'WERT_ABS_'.$ZZeitschnitt) - 0),$RundFG = ($_SESSION['Dokument']['Fuellung']['Rundung']+1), ',', '.'); 
                                        }
                                        echo '</span>';
                                       
                                    }
                                    else
                                    { 
                                        ?><span style="color:#<?php echo $Fehlerfarbcode; ?>;"><?php echo $CSV[$i_zaehl][] = $Fehlername; ?></span><?php  
                                    } 
                                ?>
                            </td>
                            
                            <?php 
                        }
                        ?>

                        
                      <!-- Wert Absolutwert-Veränderung (ha/d) nur für Flächeninanspruchnahme SxxAG -->
                        <?php 
                        // Spalte erweitern wenn Absolut-Ind angezeigt
                        if($_SESSION['Tabelle']['WERT_ABS_DIFF'] and $_SESSION['Tabelle']['WERT_ABS'] and $_SESSION['Tabelle']['VERGLEICH']) 
                        {
							?>
							<td style=" text-align:right;">
								<?php   
						
								// Breite der Box (Festlegung)
								$Boxbreite = "100";
								
								if(!$FC and !$FC_OgrWert) 
								{ 
										?>
										<div style="width:<?php echo $Boxbreite; ?>px; height:15px; padding:0px; position:relative; border:solid 1px #999;">
								  		<?php 
										// Breite der Box in bezug zum Max-Wertn (auf 50% der Boxbreite wegen +-
										/* if(@mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_ZZS='WERT_DIFF_'.$ZZeitschnitt) > 0 or @mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_ZZS='WERT_DIFF_'.$ZZeitschnitt) < 0)
										{ */
											$Box_width = @abs(50 * (@mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_ZZS='WERT_ABS_TAG_DIFF_'.$ZZeitschnitt)/$Max_abs_tag_diff[$ZZeitschnitt])); 
											// echo  @mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_ZZS='WERT_DIFF_'.$ZZeitschnitt);
										/* }
										else
										{ 	$Box_width=0; } */
										
										// Positionierung der Box von der Mitte aus
										if(@mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_ZZS='WERT_ABS_TAG_DIFF_'.$ZZeitschnitt) < 0) 
										{
											// Dynamisch den Platz vor der Box berechnen und als margin belegen
											$Box_Margin_left = 50 - $Box_width; 
											if(!$Box_width) $Box_Margin_left = "0"; // Falls Variable leer.... vermeidet XHTML Fehler
											$Box_BackColor = "#".$_SESSION['Dokument']['Fuellung']['Farbwert_Diff_min'];
											$Textpos_align = "left";
											$Border_seite = "right";
										}
										else
										{
											$Box_Margin_left = 50; // bei >0 ist 50% immer korrekt
											$Box_BackColor = "#".$_SESSION['Dokument']['Fuellung']['Farbwert_Diff_max'];
											$Textpos_align = "right";
											$Border_seite = "left";
										}
										?>
										<div style="height:100%;width:<?php echo $Box_width; ?>%; margin-left:<?php echo round($Box_Margin_left,0); ?>%; background-color:<?php echo $Box_BackColor; ?>; 
													position:absolute; z-index:5; border-<?php echo $Border_seite; ?>:#666 solid 1px;">
										
										</div>
										<div style="width:<?php echo $BB = ($Boxbreite-10); ?>px; padding-right:5px; padding-left:5px; position:absolute; z-index:10; text-align:<?php echo $Textpos_align; ?>;">
											<?php 
											
											/* if($_SESSION['Dokument']['ViewBerechtigung'] == "0")
											{ 
												echo number_format(@mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_ZZS='WERT_DIFF_'.$ZZeitschnitt),$rndg_p=($_SESSION['Dokument']['Fuellung']['Rundung']+1), ',', '.');
											}
											else
											{  */
												echo $CSV[$i_zaehl][] = number_format(@mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_ZZS='WERT_ABS_TAG_DIFF_'.$ZZeitschnitt),$_SESSION['Dokument']['Fuellung']['Rundung']+1, ',', '.');
											/* } */
											?>
										</div>
                                      </div>
									<?php 
									
							}
							else
							{ 
								$CSV[$i_zaehl][] = '-'; 
							}
												?>
							</td>
							
							<?php 
						}
						?>
                        
                        
                        
                        
						<!-- Grundaktualitaet -->
						<?php
						if($_SESSION['Tabelle']['AKTUALITAET'])
						{
							?>
							<td  style="color:#777; text-align:center;">
								<?php 
									$Akt_ZZ_Ausg =  @mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_ZZS='AKT_AUSGABE_'.$ZZeitschnitt);
									if(!$FC and $Akt_ZZ_Ausg) 
									{
										echo $CSV[$i_zaehl][] = $Akt_ZZ_Ausg; 
									}
									else
									{ 
										echo $CSV[$i_zaehl][] = '-'; 
									}
								?>
							</td>
							<?php 
						}
						?>                       
                     
                        
                        
                        
						 <!-- Absolute Änderung -->
						 <?php 
						if($_SESSION['Tabelle']['VERGLEICH'] == '1')
						{
							?>
							<td>
								<?php 
								// Breite der Box (Festlegung)
								$Boxbreite = "100";
								
								if(!$FC and !$FC_OgrWert) 
								{ 
										?>
										<div style="width:<?php echo $Boxbreite; ?>px; height:15px; padding:0px; position:relative; border:solid 1px #999;">
								  		<?php 
										// Breite der Box in bezug zum Max-Wertn (auf 50% der Boxbreite wegen +-
										/* if(@mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_ZZS='WERT_DIFF_'.$ZZeitschnitt) > 0 or @mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_ZZS='WERT_DIFF_'.$ZZeitschnitt) < 0)
										{ */
											$Box_width = @abs(50 * (@mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_ZZS='WERT_DIFF_'.$ZZeitschnitt)/$Max_abs[$ZZeitschnitt])); 
											// echo  @mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_ZZS='WERT_DIFF_'.$ZZeitschnitt);
										/* }
										else
										{ 	$Box_width=0; } */
										
										// Positionierung der Box von der Mitte aus
										if(@mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_ZZS='WERT_DIFF_'.$ZZeitschnitt) < 0) 
										{
											// Dynamisch den Platz vor der Box berechnen und als margin belegen
											$Box_Margin_left = 50 - $Box_width; 
											if(!$Box_width) $Box_Margin_left = "0"; // Falls Variable leer.... vermeidet XHTML Fehler
											$Box_BackColor = "#".$_SESSION['Dokument']['Fuellung']['Farbwert_Diff_min'];
											$Textpos_align = "left";
											$Border_seite = "right";
										}
										else
										{
											$Box_Margin_left = 50; // bei >0 ist 50% immer korrekt
											$Box_BackColor = "#".$_SESSION['Dokument']['Fuellung']['Farbwert_Diff_max'];
											$Textpos_align = "right";
											$Border_seite = "left";
										}
										?>
										<div style="height:100%;width:<?php echo $Box_width; ?>%; margin-left:<?php echo round($Box_Margin_left,0); ?>%; background-color:<?php echo $Box_BackColor; ?>; 
													position:absolute; z-index:5; border-<?php echo $Border_seite; ?>:#666 solid 1px;">
										
										</div>
										<div style="width:<?php echo $BB = ($Boxbreite-10); ?>px; padding-right:5px; padding-left:5px; position:absolute; z-index:10; text-align:<?php echo $Textpos_align; ?>;">
											<?php 
											
											/* if($_SESSION['Dokument']['ViewBerechtigung'] == "0")
											{ 
												echo number_format(@mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_ZZS='WERT_DIFF_'.$ZZeitschnitt),$rndg_p=($_SESSION['Dokument']['Fuellung']['Rundung']+1), ',', '.');
											}
											else
											{  */
												echo $CSV[$i_zaehl][] = number_format(@mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_ZZS='WERT_DIFF_'.$ZZeitschnitt),$_SESSION['Dokument']['Fuellung']['Rundung']+1, ',', '.');
											/* } */
											?>
										</div>
                                      </div>
									<?php 
									
							}
							else
							{ 
								$CSV[$i_zaehl][] = '-'; 
							}
		
							?>
								
							</td>
							<?php 
						}
						?>
                                               
                        
                        <!-- Grundaktualitaet-Differenz -->
						<?php
						if($_SESSION['Tabelle']['AKTUALITAET'] and !$MITTLERE_AKTUALITAET_IGNORE)
						{
							?>
							<td  style="color:#777; text-align:center;">
								<?php 
									$Akt_ZZ_Diff_Ausg =  @mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_ZZS='AKT_DIFF_'.$ZZeitschnitt);
									if(!$FC) 
									{ 
										// für Prüfer bei negativen Werten (=Datenfehler) rot färben
										if($_SESSION['Dokument']['ViewBerechtigung'] == "0")
										{
											if($Akt_ZZ_Diff_Ausg < 0) echo '<span style="color:#990000;">';
											echo number_format($CSV[$i_zaehl][] = $Akt_ZZ_Diff_Ausg,1, ',', '.'); 
											if($Akt_ZZ_Diff_Ausg < 0) echo '</span>';
										}
										else
										{
											// Rundung auf 1 bei z.B. 0,999 verhindern
											// ---> Immer abrunden, besser!
											//if($Akt_ZZ_Diff_Ausg < 1 and $Akt_ZZ_Diff_Ausg > 0.94)
											//{
												$Akt_ZZ_Diff_Ausg = (floor($x = $Akt_ZZ_Diff_Ausg*10))/10;
											//}
											echo $CSV[$i_zaehl][] = number_format($Akt_ZZ_Diff_Ausg,1, ',', '.');
											// Vermerk wenn AktDiff zu klein
											if($Akt_ZZ_Diff_Ausg < 1)
											{
												$Marker_AktDiff_zu_klein = 1;
												// echo "zu klein!";
											}
											else
											{
												$Marker_AktDiff_zu_klein = 0;
											}
											
										}
									}
									else
									{ 
										echo $CSV[$i_zaehl][] = '-'; 
									}
									
																	?>
							</td>
							<?php 
						}
						?>
                        
                      
                        
						 <!-- Absolute Änderung pro Jahr (nach Mittl. Grundakt) -->
						 <?php 
						if($_SESSION['Tabelle']['VERGLEICH'] == '1' and $_SESSION['Tabelle']['AKTUALITAET'])
						{
							?>
							<td>
								<?php 
								$FC_v = @mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_ZZS='WERT_DIFF_AKT_JAHRE_FEHLERCODE_'.$ZZeitschnitt);
								// Ausblenden der Box bei fehlenden Werten
								if($FC_v == 'v1' or $FC_v == 'v2' or $FC_v == 'v3' or $FC_v == 'v4' and (!$FC and !$FC_OgrWert))
								{
									// Fehlerbeschreibung ausgeben
									$SQL_FC_v = "SELECT * FROM m_fehlercodes WHERE FEHLERCODE = '".$FC_v."'";
									$Ergebnis_FC_v = mysqli_query($Verbindung,$SQL_FC_v);
									echo $CSV[$i_zaehl][] = @utf8_encode(mysqli_result($Ergebnis_FC_v,0,'FEHLER_BESCHREIBUNG'));
									
									// $CSV[$i_zaehl][] = @mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_ZZS='WERT_DIFF_AKT_JAHRE_FEHLERCODE_'.$ZZeitschnitt);
										
									
								}
								else
								{
									// Breite der Box (Festlegung)
									$Boxbreite = "100";
									
									if(!$FC and !$FC_OgrWert and ($Akt_ZZ_Diff_Ausg >= 1 or $_SESSION['Dokument']['ViewBerechtigung'] == "0"))  // für Test als Prüfer auf "0" gesetzt
									{ 
											?>
											<div style="width:<?php echo $Boxbreite; ?>px; height:15px; padding:0px; position:relative; border:solid 1px #999;">
									 		 <?php 
											// Breite der Box in bezug zum Max-Wertn (auf 50% der Boxbreite wegen +-
											/* if(@mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_ZZS='WERT_DIFF_'.$ZZeitschnitt) > 0 
																or @mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_ZZS='WERT_DIFF_'.$ZZeitschnitt) < 0)
											{ */
												$Box_width = @abs(50 * (@mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_ZZS='WERT_DIFF_AKT_JAHRE_'.$ZZeitschnitt)/$Max_abs_Diff_Jahre[$ZZeitschnitt]));  
											/* }
											else
											{ 	$Box_width=0; } */
											
											// Positionierung der Box von der Mitte aus
											if(@mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_ZZS='WERT_DIFF_AKT_JAHRE_'.$ZZeitschnitt) < 0) 
											{
												// Dynamisch den Platz vor der Box berechnen und als margin belegen
												$Box_Margin_left = 50 - $Box_width; 
												if(!$Box_width) $Box_Margin_left = "0"; // Falls Variable leer.... vermeidet XHTML Fehler
												$Box_BackColor = "#".$_SESSION['Dokument']['Fuellung']['Farbwert_Diff_min'];
												$Textpos_align = "left";
												$Border_seite = "right";
											}
											else
											{
												$Box_Margin_left = 50; // bei >0 ist 50% immer korrekt
												$Box_BackColor = "#".$_SESSION['Dokument']['Fuellung']['Farbwert_Diff_max'];
												$Textpos_align = "right";
												$Border_seite = "left";
											}
											?>
											<div style="height:100%;width:<?php echo $Box_width; ?>%; margin-left:<?php echo round($Box_Margin_left,0); ?>%; background-color:<?php echo $Box_BackColor; ?>; 
														position:absolute; z-index:5; border-<?php echo $Border_seite; ?>:#666 solid 1px;">
											
											</div>
											<div style="width:<?php echo $BB = ($Boxbreite-10); ?>px; padding-right:5px; padding-left:5px; position:absolute; z-index:10; text-align:<?php echo $Textpos_align; ?>;">
												<?php 
												
												/* if($_SESSION['Dokument']['ViewBerechtigung'] == "0")
												{ 
													echo number_format(@mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_ZZS='WERT_DIFF_AKT_JAHRE_'.$ZZeitschnitt),$rndg_p=($_SESSION['Dokument']['Fuellung']['Rundung']+1), ',', '.');
												}
												else
												{  */
												
												
													echo $CSV[$i_zaehl][] = number_format(@mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_ZZS='WERT_DIFF_AKT_JAHRE_'.$ZZeitschnitt),$_SESSION['Dokument']['Fuellung']['Rundung']+1, ',', '.');
												
												
												
												/* } */
												?>
											</div>
											
										</div>
                                        
											<?php 
                                            // Anzeige von ha/d für Prüfer
											if($_SESSION['Dokument']['ViewBerechtigung'] == "0" and $_SESSION['Dokument']['Fuellung']['Indikator_Einheit'] == 'km²')
											{
                                            	?><div style="font-weight:bold;"><?php 
												// Wert wird zZ hier berechnet, ist aber auch in Tabelle vorhanden
												echo $ha_pro_d = number_format(((@mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_ZZS='WERT_DIFF_AKT_JAHRE_'.$ZZeitschnitt) * 100) / 365),$_SESSION['Dokument']['Fuellung']['Rundung']+1, ',', '.');
												?> ha/d 
												</div><?php 
											}
                                            ?>
                                        
										<?php 
											
									}
									
								}
								// Ersatzausgabe wenn AktDiff zu klein => kein Wert
								if($Marker_AktDiff_zu_klein)
								{
									//echo '<span style="font-size:10px; color:#888888;">Aktualitätsdifferenz zu gering</span>';
								}
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
									if(!$FC)
									{
										 echo $CSV[$i_zaehl][] = number_format(@mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_ZZS='WERT_KRS_'.$ZZeitschnitt),$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.')
											." (".@mysqli_result($Ergebnis_ANZEIGE,$i_anz,'NAME_KRS').")"; 
									
                  }
                  else
                  {
                     $CSV[$i_zaehl][] = '-';
                  }
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
									if(!$FC) 
									{
										echo $CSV[$i_zaehl][] = number_format(@mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_ZZS='WERT_BLD_'.$ZZeitschnitt),$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.')
											." (".@mysqli_result($Ergebnis_ANZEIGE,$i_anz,'NAME_BLD').")"; 
									}
									else
									{
										$CSV[$i_zaehl][] = '-';	
									}
									
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
									if(!$FC) 
									{
										echo $CSV[$i_zaehl][] = number_format(@mysqli_result($Ergebnis_ANZEIGE,$i_anz,$Stelle_ZZS='WERT_BND_'.$ZZeitschnitt),$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.'); 
									}
									else
									{
										$CSV[$i_zaehl][] = '-';	
									}?>
								</td>
								<?php 						
						}
					}
				}
				}
			}
			//---------------Ende Datenzeilen Zusätzliche Zeitschnitte-------------------------
			?>
			
		</tr>
		<?php 
		
		$i_anz++;
		$i_zaehl++;
	}
	
	?>    
  
    
<!-------------------------------------------Beginn unterste Abschließende Zeile für Bundesrepublik Deutschland-------------------------------------------------------->    
    
<!--für ÖSL div hide - Rubel -->   
<tr id="Zeile_99" <?php if(empty($Wert_D)||!isset($Wert_D)) echo 'style="display: none;"'; ?> style=" border: 0px; border-top: 2px solid #666; background:#DDDDDD;" >
           
           
		  <?php 
		  // leeres Kästchen bei inaktiv/aktiv, vorerst nur für Admins sichtbar
		  if($_SESSION['Dokument']['ViewBerechtigung'] == "0")
		  { 
			  ?>
              <td style="text-align:right;">

          	</td>
          
          
		  
          <?php 
		}
		?>
        <!-- Markierung -->
          <td style="text-align:right;">
 
          </td>
          <?php 
		  // $i_zaehl++;
		  if ($Wert_D != '0'){ 
		$CSV[$i_zaehl][] = $i_zaehl;}
		  ?>
    <td style="text-align:right;">
    	
    <?php    if ($Wert_D != '0'){ $CSV[$i_zaehl][] = 'BRD'; }?></td>
			<!-- AGS -->
            
			
			<td>&nbsp;</td>
			<!-- Name Bundesrepublik -->
			<td style="font-weight:bold;">
         

                <form style="display:inline;" target="_blank" action="tabelle_indikatorenvergleich.php" method="post" name="form_ags_99">       
                   	  <input type="image" src="../icons_viewer/indikatoren.png" title="Gebietsprofil: Charakteristik dieser Raumeinheit mit Werteübersicht aller Indikatoren" alt="Mehr Indikatoren"> 
                      <input name="ags" type="hidden" value="99" />
                      <input name="name" type="hidden" value="Bundesrepublik" />
                      <input name="name_KRS" type="hidden" value="" />
                      <input name="name_BLD" type="hidden" value="" />
                      <a href="#" onclick="document.form_ags_99.submit();" title="Gebietsprofil: Charakteristik dieser Raumeinheit mit Werteübersicht aller Indikatoren" ></a> &nbsp;&nbsp;Bundesrepublik
                </form>
                <?php  if ($Wert_D != '0'){ $CSV[$i_zaehl][] = 'Bundesrepublik';} ?>
               
</td>            

            
			<!-- Wert Bundesrepublik -->
			<td style=" text-align:right;">
            	       <?php 
							echo '<span style="font-weight: bold;">';
							if($_SESSION['Dokument']['ViewBerechtigung'] == "0" && $Wert_D != '0')
							{ 
								echo  $CSV[$i_zaehl][] = number_format($x = ($Deutschlandwerte['WERT'] - 0),4, ',', '.'); 
							}
							elseif  ($Wert_D != '0')
							{ 
								echo  $CSV[$i_zaehl][] = number_format($x = ($Deutschlandwerte['WERT'] - 0),$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.'); 
							}
							echo '</span>';
							
							//Entwicklungsdiagramm
                            ?>    
                  &nbsp;&nbsp;
           		<form style="display:inline;" target="_blank" action="svg_graphen.php" method="post" name="form_ags_<?php echo $ags; ?>_diagr">       
                   	  <input style="margin-left:31px;" type="image" src="../icons_viewer/indikatoren_diagr.png" title="Veränderung des Indikatorwerte für die Gebietseinheit" alt="Diagramm"> 
                      <input name="ags" type="hidden" value="99" />
                      <input name="name" type="hidden" value="Deutschland" />
                      <input name="Raumgliederung" type="hidden" value="Gesamte Bundesrepublik" />
                      <input name="Indikator_Tab" type="hidden" value="<?php echo $_SESSION['Dokument']['Fuellung']['Indikator']; ?>" />
                      <input name="Indikator_Tab2" type="hidden" value="<?php echo $_SESSION['Tabelle']['Indikator_2']; ?>" />
                </form>
                <?php 
					  ?>

			</td>
           
            <!-- Absolutwert-Indikator Bundesrepublik-->
            <?php 
			// Spalte erweitern wenn Absolut-Ind angezeigt und IND = ...RG
			if($_SESSION['Tabelle']['WERT_ABS'] and substr($_SESSION['Dokument']['Fuellung']['Indikator'],3,2)=='RG') 
			{
				?>
                <td class="changerow"  style=" text-align:right;">
                    <?php   
							// ABS-Wert anzeigen
							echo '<span style="border:0px; background:none; font-weight:bold; ';
							echo '">';
							if($_SESSION['Dokument']['ViewBerechtigung'] == "0" && $Wert_D != '0')
							{ 
							if ($Trend_ZS_vorh != '1'){	echo  $CSV[$i_zaehl][] = number_format($x = ($Deutschlandwerte['WERT_ABS'] - 0),4, ',', '.'); }
							}
							elseif ($Wert_D != '0')
							{ 
								// Rundung um 1 erhöhen bei Flächenausgabe:
							if ($Trend_ZS_vorh != '1'){	echo  $CSV[$i_zaehl][] = number_format($x = ($Deutschlandwerte['WERT_ABS'] - 0),$RundFG = ($_SESSION['Dokument']['Fuellung']['Rundung']+1), ',', '.'); }
							}
							echo '</span>';
					?>
                </td>
                
                <?php 
			}
			?>
 
            
            <!-- Wert für 2. Indikator Bundesrepublik-->
            <?php 
			// Spalte erweitern wenn 2. Ind angezeigt
			if($_SESSION['Tabelle']['Indikator_2']) 
			{
				?>
                <td style=" text-align:right;">
                    <?php   
						
					
							// Wert anzeigen
							echo '<span style="border:0px; background:none; font-weight:bold; ';
							if($HC_2 = @mysqli_result($Ergebnis_ANZEIGE,$i_anz,'HINWEISCODE_2')) { echo ' color:#995500; '; }
							echo '">';
							if($_SESSION['Dokument']['ViewBerechtigung'] == "0" &&  $Wert_D != '0')
							{ 
								echo  $CSV[$i_zaehl][] = number_format($x = ($Deutschlandwerte['WERT_2'] - 0),4, ',', '.'); 
							}
							elseif  ($Wert_D != '0')
							{ 
								echo  $CSV[$i_zaehl][] = number_format($x = ($Deutschlandwerte['WERT_2'] - 0),$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.'); 
							}
							echo '</span>';
                           
			
					?>
                </td>
                
                <?php 
			}
			?>           
                        
            <!-- Gebietsfläche Bundesrepublik-->
            <?php 
			// Spalte erweitern wenn 2. Ind angezeigt
			if($_SESSION['Tabelle']['FLAECHE']) 
			{
				?>
                <td class="changerow"  style=" text-align:right;">
                    <?php   
						   	// Wert anzeigen
							echo '<span style="border:0px; background:none;">';
							if($_SESSION['Dokument']['ViewBerechtigung'] == "0" && $Wert_D != '0')
							{ 
								if ($Trend_ZS_vorh != '1'){echo  $CSV[$i_zaehl][] = number_format($x = ($Deutschlandwerte['FLAECHE'] - 0),4, ',', '.'); }
							}
							elseif ($Wert_D != '0')
							{ 
								if ($Trend_ZS_vorh != '1'){echo  $CSV[$i_zaehl][] = number_format($x = ($Deutschlandwerte['FLAECHE'] - 0),$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.'); }
							}
							echo '</span>';
                           
						

					?>
                </td>
                
                <?php 
			}
			?>           
            
             <!-- Einwohnerzahl Bundesrepublik-->
            <?php 
			// Spalte erweitern wenn 2. Ind angezeigt
			if($_SESSION['Tabelle']['EWZ'] && $_SESSION['Dokument']['Raumgliederung']!=='stt') 
			{
				?>
                <td class="changerow" style=" text-align:right;">
                    <?php   
						
                          	// Wert anzeigen
							echo '<span style="border:0px; background:none;">';
						if ($Trend_ZS_vorh != '1' && $Wert_D != '0'){ echo  $CSV[$i_zaehl][] = number_format($x = ($Deutschlandwerte['EWZ'] - 0),0, ',', '.'); }
							echo '</span>';
                           
						

					?>
                </td>
                
                <?php 
			}
			?>          
          
          
           
			<!-- Grundaktualitaet Bundesrepublik-->
			<?php
			if($_SESSION['Tabelle']['AKTUALITAET'])
			{
				?>
				<td class="changerow" style="color:#777; text-align:center;">
					<?php 
						 if ($Wert_D != '0' && $Trend_ZS_vorh != '1'){  echo  $CSV[$i_zaehl][] = $Deutschlandwerte['AKT_BND_AUSGABE'];}
					?>
				</td>
				<?php 
			}
			?>
	
			<!-- Uebergeordnete RE Bundesrepublik-->
			<?php  
			// Kreise
			if($_SESSION['Tabelle']['UERE_KRS'])
			{
	
				
				// sinnvolle Anzeige der Zeile nur bei Gemeinden
				if($_SESSION['Dokument']['Raumgliederung'] == "gem" )
				{
					?>
          <!-- Trenner -->
          <td class="changerow" style="background:#CCC; width:3px; padding:0px;" >&nbsp;</td>
         <!-- Kreis -->
                    
				 <td class="changerow" >
					  <?php  if ($Wert_D != '0'&& $Trend_ZS_vorh != '1'){  $CSV[$i_zaehl][] = ' '; }?>
         </td>
				 <td class="changerow" >
						<?php  if ($Wert_D != '0'&& $Trend_ZS_vorh != '1'){  $CSV[$i_zaehl][] = ' ';} ?>
				 </td>
						<!-- Grundaktualitaet KRS -->
						<?php
						if($_SESSION['Tabelle']['AKTUALITAET'])
						{
							?>
							<td class="changerow" style="color:#777; text-align:center;">
								<?php if ($Wert_D != '0'&& $Trend_ZS_vorh != '1'){   $CSV[$i_zaehl][] = ' ';} ?>
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
					<td class="changerow" style="background:#CCC; width:3px; padding:0px;" >&nbsp;</td>
					<!-- Bundesland -->
					<td class="changerow" >
					<?php  if ($Wert_D != '0'&& $Trend_ZS_vorh != '1'){   $CSV[$i_zaehl][] = ' '; }?>
	</td>
					<td class="changerow" >
					<?php  if ($Wert_D != '0'&& $Trend_ZS_vorh != '1'){  $CSV[$i_zaehl][] = ' ';} ?>	
	</td>
					<!-- Grundaktualitaet BLD -->
					<?php
					if($_SESSION['Tabelle']['AKTUALITAET'])
					{
						?>
						<td class="changerow" style="color:#777; text-align:center;">
							<?php  if ($Wert_D != '0'){  $CSV[$i_zaehl][] = ' ';} ?>
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
					<td class="changerow"  style="background:#CCC; width:3px; padding:0px;" >&nbsp;</td>
					<!-- Bund -->
					<td class="changerow" >
						<?php  if ($Wert_D != '0'&& $Trend_ZS_vorh != '1'){  $CSV[$i_zaehl][] = ' '; }?>
	</td>
					<td class="changerow" >
						<?php  if ($Wert_D != '0'&& $Trend_ZS_vorh != '1'){  $CSV[$i_zaehl][] = ' '; }?>
	</td>
					<!-- Grundaktualitaet BND -->
					<?php
					if($_SESSION['Tabelle']['AKTUALITAET'])
					{
						?>
						<td class="changerow" style="color:#777; text-align:center;">
							<?php   if ($Wert_D != '0'&& $Trend_ZS_vorh != '1'){ $CSV[$i_zaehl][] = ' ';} ?>
	</td>
						<?php 
					}
			}
			

			// ----------------------- Zusätzliche Zeitschnitte ausgeben Bundesrepublik-----------------------------
			
			if(is_array($Zeitschnitte_vorh))
			{
				foreach($Zeitschnitte_vorh as $ZZeitschnitt)
				{
						if(($ZZeitschnitt != $_SESSION['Dokument']['Jahr_Anzeige'] and $_SESSION['Tabelle']['Zeitschnitt_Zusatz'][$ZZeitschnitt] == 1)and ($ZZeitschnitt <'2024' or ($ZZeitschnitt >= '2025' and   $_SESSION['Tabelle']['Trend_Berechtigung'] == '1'&& $Zus_ZS_vorh != '1') ) ) // Aktuellen Zeitschnitt nicht berücksichtigen!
					{
						
				
						?>
						<!-- Trenner -->
						<td style="background:#CCC;" >&nbsp;</td>
						 
                        
						<!-- Wert -->
						<td style="text-align:right; font-weight:bold;">
							
								<?php 

									if($_SESSION['Dokument']['ViewBerechtigung'] == "0" &&  $Wert_D != '0')
									{ 
										echo  $CSV[$i_zaehl][] = number_format($x = ($Deutschlandwerte['WERT_'.$ZZeitschnitt] - 0),4, ',', '.');
									}
									elseif ($Wert_D != '0')
									{ 
										echo  $CSV[$i_zaehl][] = number_format($x = ($Deutschlandwerte['WERT_'.$ZZeitschnitt] - 0),$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.');
									}
			 
								?>
						</td>                      
              	<?php 
									//Viele Spalten bei normalen Zeitschnitten anhängen
					if($ZZeitschnitt != '2025' and 	$ZZeitschnitt != '2030')
						{?>             
                           
                  
					 <!-- Wert Absolut-Indikator Bundesrepublik-->
						 <?php 
                        // Spalte erweitern wenn Absolutwert-Ind angezeigt und Ind. = ...RG
                        if($_SESSION['Tabelle']['WERT_ABS'] and substr($_SESSION['Dokument']['Fuellung']['Indikator'],3,2)=='RG') 
                        {
                            ?>
                            <td style=" text-align:right;">
                                <?php                                                          
                                  
                                        // ABS-Wert anzeigen
                                        echo '<span style="border:0px; background:none; font-weight:bold; ';
                                        // if($HC = @mysqli_result($Ergebnis_ANZEIGE,$i_anz,'HINWEISCODE')) { echo ' color:#995500; '; }
                                        echo '">';
                                        if($_SESSION['Dokument']['ViewBerechtigung'] == "0" && $Wert_D != '0')
                                        { 
                                            echo  $CSV[$i_zaehl][] = number_format($x = ($x = $Deutschlandwerte['WERT_ABS_'.$ZZeitschnitt] - 0),4, ',', '.'); 
                                        }
                                        elseif ($Wert_D != '0')
                                        { 
                                            // Rundung um 1 erhöhen bei Flächenausgabe:
                                            echo  $CSV[$i_zaehl][] = number_format($x = ($Deutschlandwerte['WERT_ABS_'.$ZZeitschnitt] - 0),$RundFG = ($_SESSION['Dokument']['Fuellung']['Rundung']+1), ',', '.'); 
                                        }
                                        echo '</span>';
                                   ?>
                            </td>
                            
                            <?php 
                        }
                        ?>                      
                                                                        
                      <!-- Wert Absolutwert-Veränderung (ha/d) nur für Flächeninanspruchnahme SxxAG -->
                        <?php 
                        // Spalte erweitern wenn Absolut-Ind angezeigt
                        if($_SESSION['Tabelle']['WERT_ABS_DIFF'] and $_SESSION['Tabelle']['WERT_ABS'] and $_SESSION['Tabelle']['VERGLEICH']) 
                        {
							?>
							<td style=" text-align:left; padding-left:80px;">
								<?php   
								
			
										// ABS-Wert-Veränderung anzeigen
										echo '<span style="border:0px; background:none; font-weight:bold; ';
										// if($HC = @mysqli_result($Ergebnis_ANZEIGE,$i_anz,'HINWEISCODE')) { echo ' color:#995500; '; }
										echo '">';
										if($_SESSION['Dokument']['ViewBerechtigung'] == "0" &&  $Wert_D != '0')
										{ 
											echo  $CSV[$i_zaehl][] = number_format($x = ($Deutschlandwerte['WERT_ABS_TAG_DIFF_'.$ZZeitschnitt] - 0),4, ',', '.'); 
										}
										elseif ($Wert_D != '0')
										{ 
											// Rundung um 1 erhöhen bei Flächenausgabe:
											echo  $CSV[$i_zaehl][] = number_format($x = ($Deutschlandwerte['WERT_ABS_TAG_DIFF_'.$ZZeitschnitt] - 0),$RundFG = ($_SESSION['Dokument']['Fuellung']['Rundung']+1), ',', '.'); 
										}
										echo '</span>';
								
								?>
							</td>
							
							<?php 
						}
						?>          
                       
                        
						<!-- Grundaktualitaet Bundesrepublik-->
						<?php
						if($_SESSION['Tabelle']['AKTUALITAET'])
						{
							?>
							<td  style="color:#777; text-align:center;">
								<?php 
								 if ($Wert_D != '0'){ 	echo  $CSV[$i_zaehl][] = $Deutschlandwerte['AKT_AUSGABE_'.$ZZeitschnitt];}
									
								?>
							</td>
							<?php 
						}
						?>      
                        
						 <!-- Absolute Änderung -->
						 <?php 
						if($_SESSION['Tabelle']['VERGLEICH'] == '1')
						{
							?>
							<td style="text-align:right;">
								<?php 	
								 if ($Wert_D != '0'){ 	echo  $CSV[$i_zaehl][] = number_format($Deutschlandwerte['WERT_DIFF_'.$ZZeitschnitt],$_SESSION['Dokument']['Fuellung']['Rundung']+1, ',', '.');}
							?>
								
							</td>
							<?php 
						}
						?>                                               
                        
                        <!-- Grundaktualitaet-Differenz Bundesrepublik-->
						<?php
						if($_SESSION['Tabelle']['AKTUALITAET'] and !$MITTLERE_AKTUALITAET_IGNORE)
						{
							?>
							<td  style="color:#777; text-align:center;">
								<?php 
									//echo $Deutschlandwerte['AKT_DIFF_'.$ZZeitschnitt];
								 if ($Wert_D != '0'){ 	echo  $CSV[$i_zaehl][] = number_format($Deutschlandwerte['AKT_DIFF_'.$ZZeitschnitt],1, ',', '.'); }
								?>
							</td>
							<?php 
						}
						?>                    
                        
                        
						 <!-- Absolute Änderung pro Tag (nach Mittl. Grundakt) -->
						 <?php 
						if($_SESSION['Tabelle']['VERGLEICH'] == '1' and !$MITTLERE_AKTUALITAET_IGNORE and $_SESSION['Tabelle']['AKTUALITAET'])
						{
							?>
							<td style="text-align:right;">
                            <?php 
								 if ($Wert_D != '0'){  echo  $CSV[$i_zaehl][] = number_format($Deutschlandwerte['WERT_DIFF_AKT_JAHRE_'.$ZZeitschnitt],$_SESSION['Dokument']['Fuellung']['Rundung']+1, ',', '.'); }
							
                              // Anzeige von ha/d für Prüfer
								if($_SESSION['Dokument']['ViewBerechtigung'] == "0" and $_SESSION['Dokument']['Fuellung']['Indikator_Einheit'] == 'km²')
								{
                                  	?><div style="font-weight:bold;"><?php 
									echo $ha_pro_d = number_format((($Deutschlandwerte['WERT_DIFF_AKT_JAHRE_'.$ZZeitschnitt] * 100) / 365),$_SESSION['Dokument']['Fuellung']['Rundung']+1, ',', '.');
									?> ha/d 
									</div><?php 
								}
                                ?>
                                
							</td>
							<?php 
						}
					
						?>
  
						<!-- Uebergeordnete RE Bundesrepublik-->
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
									<td><?php  if ($Wert_D != '0'){  $CSV[$i_zaehl][] = ' ';} ?>
									
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
									<?php  if ($Wert_D != '0'){  $CSV[$i_zaehl][] = ' '; }?>
	</td>
								<?php 
							}
						}
						if($_SESSION['Tabelle']['UERE_BND'])
						{
								?>
								<!-- Bundesland -->
								<td>
									<?php  if ($Wert_D != '0'){  $CSV[$i_zaehl][] = ' '; }?>
	</td>
								<?php 						
						}
					}
				}
			}	
		}		
			?>
					</tr>
<!---------------------------------ENDE Zeile Bundesrepublik------------------------------------>
		
</table>

<table>
<tr>
        <td colspan="3" valign="top" style="border:none; padding:0px; padding-top:10px;">
		
	
<br />
       	 	Datengrundlage: 
			<?php echo $_SESSION['Dokument']['Datengrundlage_0']; 
            if($_SESSION['Dokument']['Datengrundlage_0'] and $_SESSION['Dokument']['Datengrundlage_1']) { echo '; '; }else{ echo ' '; }
			echo $_SESSION['Dokument']['Datengrundlage_1']; ?>
        </td>
      </tr>
</table>


<?php 

	// Testvariable für Laufzeit
	$Laufzeit = $Laufzeit.'
	Tabelle ausgegeben: '.date('H:i:s');
?>

	
<br />

	<?php 
	//-------------------------------------------- ENDE der Datentabelle----------------------------------------------------
	


// CSV-Datei speichern
if($_POST['csv'])
{

	// Array $CSV Reihenfolge bumkehren
	// Zeilenanz. erfassen
	$i_csv_zeilenanz = 0;
	foreach($CSV as $CSV_Zeile)
	{
		$i_csv_zeilenanz++;
	}
	// Zeilen neu einsortieren
	$i_csv_zeilenanz_inv = $i_csv_zeilenanz;
	foreach($CSV as $CSV_Zeile)
	{
		// Zeile [0] an Pos [0] belassen
		if($i_csv_zeilenanz_inv == $i_csv_zeilenanz)
		{
			$CSV_inv[0] = $CSV_Zeile;
		}
		else
		{
			$CSV_inv[$i_csv_zeilenanz_inv] = $CSV_Zeile;
		}
		$i_csv_zeilenanz_inv--;
	}
	// Wieder in Ursprungsarray schreiben
	$CSV = $CSV_inv;
	
	
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
	
	$CSV_Daten = new Format;
	$CSV_Inhalt = utf8_decode($CSV_Daten -> arr_to_csv($CSV));
	
	// ... wird am Programm-Anfang generiert um Downloadfeld bereitstellen zu können:    $Dateiname = mt_rand().".csv";	// besser mit Indikator-Jahr usw. im Namen benennen	?
	// ... $tmpfname = "../temp/".$Dateiname;
			
	$fp = fopen($tmpfname, "w+");
	fwrite($fp,$CSV_Inhalt);
	fclose($fp);



// Testvariable für Laufzeit
$Laufzeit = $Laufzeit.'
CSV geschrieben: '.date('H:i:s');	

}

?>


<script>
	
/*Menü: Prüft ob rechts trendzeitschnitt angewählt, wenn ja dann zeige links zusatzzeitschnitte nicht sondern nur einen Hinweis (anhand der IDs) 		
Tabellenihalte: Prüft ob in Menü Trendzeitschnitt gewählt, wenn ja blende Inhalte (passend zu Menü) in Tabelle aus, die bestimmte Klasse (changerow) haben
	 */
		
if(document.getElementById('trend_Zusatz_ZS_2025')&&((document.getElementById('trend_Zusatz_ZS_2025').checked)||(document.getElementById('trend_Zusatz_ZS_2030').checked))){  
	//Menü
    $("#zusatz_ZS_links").hide();
    $("#weitere_kenngr").hide();
    $("#zusatz_ZS_links_Hinweis").show();
   
  //Tabelleninhalte
	  $('th.changerow').hide(); 
	  $('td.changerow').hide();  
} else {
	//Menü
    $("#zusatz_ZS_links").show();
    $("#weitere_kenngr").show();
    $("#zusatz_ZS_links_Hinweis").hide();
  //Tabelleninhalte
		$('th.changerow').show();
		$('td.changerow').show();
}	
		
</script>


</body>
</html>