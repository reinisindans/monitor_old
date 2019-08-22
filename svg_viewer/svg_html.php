<?php 

session_start(); // Sitzung starten/ wieder aufnehmen



include("includes_classes/verbindung_mysqli.php");
include("includes_classes/implode_explode.php");
include("includes_classes/utf8_check.php");

// Array mit Wortdefinitionen in mehreren Sprachen
$Sprache_Ausgabe['DE']['Kategorie'] = 'Indikator-Kategorie'; 
$Sprache_Ausgabe['EN']['Kategorie'] = 'Category';

$Sprache_Ausgabe['DE']['Auswahl_Kategorie'] = 'Übergeordnete Kategorie zur Auswahl zugehöriger Indikatoren wählen';
$Sprache_Ausgabe['EN']['Auswahl_Kategorie'] = 'Category Seletion';
$Sprache_Ausgabe['DE']['Auswahl_Indikator'] = 'Auswahl des Indikators';
$Sprache_Ausgabe['EN']['Auswahl_Indikator'] = 'Indicator Seletion';
$Sprache_Ausgabe['DE']['Auswahl_Zeitschnitt'] = 'Auswahl der Zeitschnittes';
$Sprache_Ausgabe['EN']['Auswahl_Zeitschnitt'] = 'Year';
$Sprache_Ausgabe['DE']['Auswahl_Raumebene'] = 'Auswahl der Gebietsabdeckung';
$Sprache_Ausgabe['EN']['Auswahl_Raumebene'] = 'Administrative boundaries for area selection';
$Sprache_Ausgabe['DE']['Suche_Gebiete'] = 'Suche nach Gebieten für die Kartendarstellung';
$Sprache_Ausgabe['EN']['Suche_Gebiete'] = 'Search for areas';
$Sprache_Ausgabe['DE']['Auswahl_Gebiete'] = 'Suche nach Gebieten für die Kartendarstellung';
$Sprache_Ausgabe['EN']['Auswahl_Gebiete'] = 'Selection of areas for map extent';
$Sprache_Ausgabe['DE']['Auswahl_Raumgliederung'] = 'Gliederungstiefe der ausgewählten Gebietsabdeckung';
$Sprache_Ausgabe['EN']['Auswahl_Raumgliederung'] = 'Spatial units for map view';
$Sprache_Ausgabe['DE']['Auswahl_Raumgliederung_STT'] = 'Stadtgliederung auf der obersten teilstädtischen Ebene. Geometriegrundlage von 2014.';
$Sprache_Ausgabe['EN']['Auswahl_Raumgliederung_STT'] = 'Name may differ. Geometry as of 2014.';


$Sprache_Ausgabe['DE']['Auswahl_Laden'] = 'Laden einer gespeicherten Karte durch Eingabe einer ID im nebenstehenden Feld';
$Sprache_Ausgabe['EN']['Auswahl_Laden'] = 'Load a stored map per ID number';
$Sprache_Ausgabe['DE']['Kennblatt_Titel'] = 'Erläuterungen zum Indikator und Erhebungsmethodik';
$Sprache_Ausgabe['EN']['Kennblatt_Titel'] = 'More information on the indicator';
$Sprache_Ausgabe['DE']['Kartenlink_Title'] = 'Dauerhaftes Abspeichern der aktuellen Karte auf dem Server des IÖR und Generieren eines Weblinks zum Abruf der gespeicherten Karte';
$Sprache_Ausgabe['EN']['Kartenlink_Title'] = 'Save the map in our database and generate a ID number and a weblink';
$Sprache_Ausgabe['DE']['reset_Title'] = 'Entfernen gesetzter Beschriftungen aus der Kartenansicht';
$Sprache_Ausgabe['EN']['reset_Title'] = 'Remove labels from the map';
$Sprache_Ausgabe['DE']['loeschen_Title'] = 'Verwerfen sämtlicher getätigter Einstellungen des Viewers';
$Sprache_Ausgabe['EN']['loeschen_Title'] = 'Reset the whole mapviewer';

$Sprache_Ausgabe['DE']['Zeitschnitt'] = 'Zeitschnitt'; 
$Sprache_Ausgabe['EN']['Zeitschnitt'] = 'Year';
$Sprache_Ausgabe['DE']['Indikator'] = 'Indikator'; 
$Sprache_Ausgabe['EN']['Indikator'] = 'Indicator';
$Sprache_Ausgabe['DE']['BitteWaehlen'] = 'Bitte w&auml;hlen!'; 
$Sprache_Ausgabe['EN']['BitteWaehlen'] = 'Please select!';
$Sprache_Ausgabe['DE']['Ausdehnung'] = 'R&auml;umliche Ausdehnung'; 
$Sprache_Ausgabe['EN']['Ausdehnung'] = 'Extent';
$Sprache_Ausgabe['DE']['Raumgliederung'] = 'Raumgliederung'; 
$Sprache_Ausgabe['EN']['Raumgliederung'] = 'Spatial units';
$Sprache_Ausgabe['DE']['Angezeigte Gebiete'] = 'Gebietsauswahl'; 
$Sprache_Ausgabe['EN']['Angezeigte Gebiete'] = 'Select area';

$Sprache_Ausgabe['DE']['Mehrfachauswahl ermöglichen'] = 'Mehrfachauswahl ermöglichen';
$Sprache_Ausgabe['EN']['Mehrfachauswahl ermöglichen'] = 'Activate multi selection';
$Sprache_Ausgabe['DE']['Zurueck zur Einfachauswahl'] = 'Zurück zur Einfachauswahl';
$Sprache_Ausgabe['EN']['Zurueck zur Einfachauswahl'] = 'Back to single selection';
$Sprache_Ausgabe['DE']['Mehrfachauswahl Taste'] = '(Mehrfachauswahl mit Strg- oder Shift-Taste)';
$Sprache_Ausgabe['EN']['Mehrfachauswahl Taste'] = '(Multi selection with Ctrl- oder Shift-Key)';
$Sprache_Ausgabe['DE']['zeichnen'] = 'Karte aktualisieren';
$Sprache_Ausgabe['EN']['zeichnen'] = 'Draw map';

$Sprache_Ausgabe['DE']['Tabelle'] = 'Indikatorwerttabelle<br /><span style="font-weight:normal;">Raum-/Zeitvergleiche</span>';
$Sprache_Ausgabe['EN']['Tabelle'] = 'Table - Indicatorvalues<br /><span style="font-weight:normal;">Spacial & time comparison</span>';

$Sprache_Ausgabe['DE']['Interpretation'] = 'Interpretation';
$Sprache_Ausgabe['EN']['Interpretation'] = 'Interpretation';

$Sprache_Ausgabe['DE']['Kennblatt'] = 'Indikatorkennblatt';
$Sprache_Ausgabe['EN']['Kennblatt'] = 'Datasheet';

$Sprache_Ausgabe['DE']['Interpretation_link'] = 'http://new.ioer-monitor.de/karten/karten/interpretation/?L=0';
$Sprache_Ausgabe['EN']['Interpretation_link'] = 'http://new.ioer-monitor.de/en/maps/maps/interpretation/';

$Sprache_Ausgabe['DE']['Kennblatt_link'] = 'http://www.ioer-monitor.de/index.php?id=44';
$Sprache_Ausgabe['EN']['Kennblatt_link'] = 'http://www.ioer-monitor.de/index.php?id=44&L=2';
//---------
$Sprache_Ausgabe['DE']['Karte_einbinden'] = 'Karte einbinden / speichern';
$Sprache_Ausgabe['EN']['Karte_einbinden'] = 'Save / Embed map';

$Sprache_Ausgabe['DE']['Karte_einbinden_titel'] = 'Karte einbinden / speichern';
$Sprache_Ausgabe['EN']['Karte_einbinden_titel'] = 'Save / Embed map';

$Sprache_Ausgabe['DE']['Einbinden_GIS'] = 'Einbinden in eigenes GIS als:';
$Sprache_Ausgabe['EN']['Einbinden_GIS'] = 'Export';

$Sprache_Ausgabe['DE']['WFS_titel'] = 'Export';
$Sprache_Ausgabe['EN']['WFS_titel'] = 'Export';

$Sprache_Ausgabe['DE']['Karte_speichern'] = 'Dauerhaftes Speichern der Karte<br /> auf dem IÖR-Server:';
$Sprache_Ausgabe['EN']['Karte_speichern'] = 'Export';

$Sprache_Ausgabe['DE']['Export_Zeile'] = 'Export der Kartendarstellung als:';
$Sprache_Ausgabe['EN']['Export_Zeile'] = 'Export';

$Sprache_Ausgabe['DE']['WFS_Dialog_1'] = 'Dieser WFS-Dienst steht Ihnen nur für die Verwendung in Ihrem eigenen GIS-System zur Verfügung. Voraussetzung ist die Zustimmung zu geltenden Nutzungsbedingungen.<br/>'; 
$Sprache_Ausgabe['EN']['WFS_Dialog_1'] = 'You can use this Web Feature Service (WFS) only in your own GIS. Therefor accept the valid terms and conditions.';

$Sprache_Ausgabe['DE']['WFS_Dialog_2'] = 'Ich akzeptiere alle geltenden <a href="http://www.ioer-monitor.de/fileadmin/Dokumente/PDFs/Nutzungsbedingungen_IOER-Monitor.pdf" target="_blank" style="color:blue;text-decoration:underline;">Nutzungsbedingungen</a>.';
$Sprache_Ausgabe['EN']['WFS_Dialog_2'] = 'I accept the valid  <a href="http://www.ioer-monitor.de/fileadmin/Dokumente/PDFs/Nutzungsbedingungen_IOER-Monitor.pdf" target="_blank" style="color:blue;text-decoration:underline;">terms and conditions</a> (German only).';


$Sprache_Ausgabe['DE']['WFS_Dialog_3'] = 'Die zu verwendende URL für den WFS-Dienst (Version 2.0.0) lautet:';
$Sprache_Ausgabe['EN']['WFS_Dialog_3'] = 'Please use the following URL: ';

$Sprache_Ausgabe['DE']['WFS_Dialog_4'] = '<u>Hinweis für ArcGIS:</u> Bitte nicht alle Layer auf einmal laden, da sonst ein Fehler verursacht wird. Für QGIS trifft das nicht zu.';
$Sprache_Ausgabe['EN']['WFS_Dialog_4'] = '<u>Advice for ArcGIS:</u> Please do not import all layers at once, this causes an error. For QGIS this problem is not known.';

$Sprache_Ausgabe['DE']['WFS_Dialog_5'] = '<h3>Anleitungen zum Importieren:</h3>
					<a style="text-decoration: none; text-align: center;" target="_blank" href="pdf/anleitung_import_arcgis.pdf"><button style="cursor: pointer;">ArcGIS</button></a>
							<a target="_blank" href="pdf/anleitung_import_qgis.pdf" style="text-decoration: none; "><button style="margin-left:15px; cursor: pointer;">QGIS</button></a>
				
						';
$Sprache_Ausgabe['EN']['WFS_Dialog_5'] = '';




$Sprache_Ausgabe['DE']['Ausgabeeinstellungen'] = 'Ausgabeeinstellungen';
$Sprache_Ausgabe['EN']['Ausgabeeinstellungen'] = 'Output options';

$Sprache_Ausgabe['DE']['Export'] = 'Bilddatei';
$Sprache_Ausgabe['EN']['Export'] = 'Image';

$Sprache_Ausgabe['DE']['Export_alttext'] = 'Export der Karte als PNG, TIFF oder JPEG ';
$Sprache_Ausgabe['EN']['Export_alttext'] = 'Export as PNG, TIFF oder JPEG';

$Sprache_Ausgabe['DE']['Datei erzeugen'] = 'Datei erzeugen';
$Sprache_Ausgabe['EN']['Datei erzeugen'] = 'Generate File';

$Sprache_Ausgabe['DE']['PDFDatei erzeugen'] = 'PDF';
$Sprache_Ausgabe['EN']['PDFDatei erzeugen'] = 'Generate PDF';

$Sprache_Ausgabe['DE']['PDFDatei title'] = 'PDF-Datei erzeugen';
$Sprache_Ausgabe['EN']['PDFDatei title'] = 'Generate PDF';

$Sprache_Ausgabe['DE']['batik'] = 'Mit Unterstützung des';
$Sprache_Ausgabe['EN']['batik'] = 'Supported by';

$Sprache_Ausgabe['DE']['Kartenlink'] = 'Kartenlink erzeugen';
$Sprache_Ausgabe['EN']['Kartenlink'] = 'Generate a map link';

$Sprache_Ausgabe['DE']['Karte'] = 'Karte laden';
$Sprache_Ausgabe['EN']['Karte'] = 'Load map';

$Sprache_Ausgabe['DE']['Nr'] = 'Nr';
$Sprache_Ausgabe['EN']['Nr'] = 'No';

$Sprache_Ausgabe['DE']['SVG'] = 'PDF-Datei';
$Sprache_Ausgabe['EN']['SVG'] = 'Print';

$Sprache_Ausgabe['DE']['SVG_alttext'] = 'Öffnet Druckansicht bzw. Speicherung als SVG-Grafik per Browsermenü möglich (z.B. Datei/Speichern unter)';
$Sprache_Ausgabe['EN']['SVG_alttext'] = 'Generate the map as SVG-Image, so you can save or print it.';

$Sprache_Ausgabe['DE']['reset'] = 'Gebietsnamen löschen';
$Sprache_Ausgabe['EN']['reset'] = 'Remove all labels';

$Sprache_Ausgabe['DE']['loeschen'] = 'Viewer zurücksetzen';
$Sprache_Ausgabe['EN']['loeschen'] = 'Reset viewer';

$Sprache_Ausgabe['DE']['Karte_einbinden'] = 'Karte einbinden / speichern';
$Sprache_Ausgabe['EN']['Karte_einbinden'] = 'Save / Embed map';

$Sprache_Ausgabe['DE']['Karte_einbinden_titel'] = 'Funktionen um Karte in eigenem GIS einzubinden, als Bild oder PDF-Datei ausgeben sowie zur dauerhaften Speicherung auf dieser Webseite';
$Sprache_Ausgabe['EN']['Karte_einbinden_titel'] = 'Options to embed the map in your own GIS, to export it as image or PDF and to store it permanently on this webseite';

$Sprache_Ausgabe['DE']['Einbinden_GIS'] = '<strong>Einbinden in eigenes GIS als:</strong>';
$Sprache_Ausgabe['EN']['Einbinden_GIS'] = '<strong>Embed the map in your own GIS as:</strong>';

$Sprache_Ausgabe['DE']['WFS_titel'] = 'Erzeugt URL um Kartendaten mittels WFS in eigenem GIS verwenden zu können';
$Sprache_Ausgabe['EN']['WFS_titel'] = 'Generate URL to use the map data in your own GIS';

$Sprache_Ausgabe['DE']['Karte_speichern'] = '<strong>Dauerhaftes Speichern der Karte <br> auf dem IÖR-Server:</strong>';
$Sprache_Ausgabe['EN']['Karte_speichern'] = '<strong>Save the map permanently <br> at the IÖR-server:</strong>';

$Sprache_Ausgabe['DE']['Export_Zeile'] = '<strong>Export der Kartendarstellung als:</strong>';
$Sprache_Ausgabe['EN']['Export_Zeile'] = '<strong>Export the map as:</strong>';

$Sprache_Ausgabe['DE']['beta_Title'] = 'Öffnet neuen Kartenviewer in externem Fenster';
$Sprache_Ausgabe['EN']['beta_Title'] = 'Open new Mapviewer in new window';
$Sprache_Ausgabe['DE']['beta_button'] = 'Neuer Kartenviewer (Beta)';
$Sprache_Ausgabe['EN']['beta_button'] = 'New mapviewer (beta)';



// ... für Aufklapp-Menüs

$Sprache_Ausgabe['DE']['Deutschland'] = 'Deutschland'; $Sprache_Ausgabe['EN']['Deutschland'] = 'Germany';
$Sprache_Ausgabe['DE']['Bundesland'] = 'Bundesland'; $Sprache_Ausgabe['EN']['Bundesland'] = 'State';
$Sprache_Ausgabe['DE']['Kreis'] = 'Kreis'; $Sprache_Ausgabe['EN']['Kreis'] = 'District';
$Sprache_Ausgabe['DE']['Gemeinde'] = 'Gemeinde'; $Sprache_Ausgabe['EN']['Gemeinde'] = 'Municipal level';

$Sprache_Ausgabe['DE']['Bundesländer'] = 'Bundesl&auml;nder'; $Sprache_Ausgabe['EN']['Bundesländer'] = 'States';
$Sprache_Ausgabe['DE']['Raumordnungsregionen'] = 'Raumordnungsregionen'; $Sprache_Ausgabe['EN']['Raumordnungsregionen'] = 'Spatial planning regions';
$Sprache_Ausgabe['DE']['Kreise'] = 'Kreise'; $Sprache_Ausgabe['EN']['Kreise'] = 'Districts';
$Sprache_Ausgabe['DE']['* nur kreisfreie Städte'] = '* nur kreisfreie Städte'; $Sprache_Ausgabe['EN']['* nur kreisfreie Städte'] = '* only City Districts';
$Sprache_Ausgabe['DE']['* nur Landkreise'] = '* nur Landkreise'; $Sprache_Ausgabe['EN']['* nur Landkreise'] = '* only Country Districts';
$Sprache_Ausgabe['DE']['Gemeinden'] = 'Gemeinden'; $Sprache_Ausgabe['EN']['Gemeinden'] = 'Municipal level';
$Sprache_Ausgabe['DE']['Gemeindeverbände'] = 'Gemeindeverbände'; $Sprache_Ausgabe['EN']['Gemeindeverbände'] = 'Municipal associations';
$Sprache_Ausgabe['DE']['Städte ab 50 000 Ew.'] = 'Städte (> 50000 Ew.)'; $Sprache_Ausgabe['EN']['Städte ab 50 000 Ew.'] = 'Cities (> 50000 inh.)';
$Sprache_Ausgabe['DE']['* Stadtteile'] = '* Stadtteile'; $Sprache_Ausgabe['EN']['* Stadtteile'] = '* Quarters';


// Allgemeine Übernahmen von Funktionswerten aus Sonderfunktionalitäten
// --------------------------------------------------------------------

// Bestimmte Variablen leeren/setzen


/* locale auf Deutsch setzen */
// $loc_de = setlocale (LC_ALL, 'de_DE@euro', 'de_DE', 'de', 'ge', 'de_DE.UTF8', 'de_DE.UTF-8', 'de_DE.8859-1');

$GLOBALS['ID_Info'] = '';
// Begrenzung der Raumeinheiten zur gleichzeitigen Auswahl
$GLOBALS['RE_Begrenzung'] = 0; // Hat keinen Einfluss auf Begrenzung von Rasterdaten (1= Begrenzung für alle Raumebenen aktiv / 0= Begrenzung nur noch für Raster aktiv)
$GLOBALS['RE_Begrenzung_Anz'] = 5; // Begrenzung auf X Raumeinheiten
// Prüfer mit ID=0 darf mehr anzeigen
if($_SESSION['Dokument']['ViewBerechtigung'] == "0")
{
	$GLOBALS['RE_Begrenzung_Anz'] = 100; // Begrenzung auf X Raumeinheiten
}


//neu März 2017: Auch Typ übernehmen, damit man von Farbverlauf zurück auf automatische Klass kommt, statt nur Untertyp zu ändern (Farbverlauf würde erhalten bleiben)
if($_POST['Typ'])
{
	$_SESSION['Dokument']['Fuellung']['Typ']=$_POST['Typ'];	
}



// Klassifikation auf gleiche Klassenbesetzung oder gleiche Klassenbreite setzen (nur bei $_SESSION['Dokument']['Fuellung']['Typ'] == "Klassifizierte Farbreihe" sinnvoll)
if($_POST['untertyp'])
{
	$_SESSION['Dokument']['Fuellung']['Untertyp'] = $_POST['untertyp'];
		
}

//Kopieren der Farbeinstellungen und Kalsseinstellungen neu 31.3.17 aus svg_zeichenvorschrift_klass

// Manuelle Klasse aus automatischer Kl. neu erstellen (einerseits beim ersten öffnen der Optionen Simpel (???) oder manuell und andererseits auf Userwunsch
if(
($_GET['kopieren'] and ((!$_SESSION['Temp']['manuelle_Klasse'] or $_SESSION['Temp']['manuelle_Klasse'] = "leer") and $_SESSION['Dokument']['Fuellung']['Typ'] == "manuell Klassifizierte Farbreihe"))) 
{
	unset($_SESSION['Temp']['manuelle_Klasse']);
	$_SESSION['Temp']['manuelle_Klasse'] = $_SESSION['Temp']['Klasse'];
}

// Zwischen Einfach- bzw. Mehrfachauswahl umschalten
if($_GET['Mehrfachauswahl'])
{
	$_SESSION['Dokument']['Mehrfachauswahl'] = 1;
}
if($_GET['Einfachauswahl'])
{
	$_SESSION['Dokument']['Mehrfachauswahl'] = '';
}

// Zurücksetzen der Zeichenvorschrift aus svg_zeichenvorschrift_klass.php heraus
if($_POST['standard'] == "standard")
{
	$SQL_ZV = "SELECT * FROM m_zeichenvorschrift WHERE ID_INDIKATOR='".$_SESSION['Dokument']['Fuellung']['Indikator']."'";
	$Ergebnis_ZV = mysqli_query($Verbindung,$SQL_ZV);
	
	// Falls ZV nicht definiert wurde, weiter unten Standard-ZV mit ID=1 verwenden
	if(!@mysqli_result($Ergebnis_ZV,0,'ID_ZEICHENVORSCHRIFT'))
	{
		$Indikator_ZV_nicht_definiert = '1';
	}
	else
	{	
		$_SESSION['Dokument']['Fuellung']['Typ'] = mysqli_result($Ergebnis_ZV,0,'TYP_FUELLUNG');
		$_SESSION['Dokument']['Fuellung']['Farbwert_min'] = mysqli_result($Ergebnis_ZV,0,'FARBWERT_MIN');
		$_SESSION['Dokument']['Fuellung']['Farbwert_max'] = mysqli_result($Ergebnis_ZV,0,'FARBWERT_MAX');
		$_SESSION['Dokument']['Fuellung']['LeerFarbe'] = mysqli_result($Ergebnis_ZV,0,'FARBWERT_LEER');
		$_SESSION['Dokument']['Strichfarbe'] = mysqli_result($Ergebnis_ZV,0,'FARBWERT_KONTUR');
		$_SESSION['Dokument']['Strichfarbe_MouseOver'] = mysqli_result($Ergebnis_ZV,0,'FARBWERT_MOUSEOVER');
		$_SESSION['Dokument']['Textfarbe_Labels'] = mysqli_result($Ergebnis_ZV,0,'FARBWERT_TEXT');
		$_SESSION['Dokument']['Klassen']['Aufloesung'] = mysqli_result($Ergebnis_ZV,0,'KLASSEN_AUFLOESUNG');
		$_SESSION['Dokument']['indikator_lokal'] = '1';
	}
}


// Zusatzebenen
if($_POST['zus_aktualisieren'])
{
	// Check auf Übergaben durch POST .... einfach erweitern!
	$Zusatz_moeglich[1] = 'bundesland'; // Grenzen
	$Zusatz_moeglich[2] = 'kreis'; // Grenzen
	$Zusatz_moeglich[3] = 'gemeinde'; // Grenzen
	$Zusatz_moeglich[4] = 'ror'; // Grenzen
	
	$Zusatz_moeglich[5] = 'gew'; // Gewässer
	
	$Zusatz_moeglich[6] = 'db'; // DB-Fernverkehrsnetz
	$Zusatz_moeglich[7] = 'bab'; // Autobahn

	$Zusatz_anz = 10;
	
	// muss gesetzt sein... ,wenn leer, dann auch so gewollt
	// $_SESSION['Dokument']['Zusatzebene_aktiv'] = $_POST['zusatzebene_aktiv'];
		
	$i = 1;
	for($i=1 ; $i <= $Zusatz_anz ; $i++)
	{
		if($_POST[$Zusatz_moeglich[$i]]) 
		{
			$_SESSION['Dokument']['zusatz_'.$Zusatz_moeglich[$i]] = '1';
			
			// Ausnahme: Gewässer extra abfangen und Typ hinterlegen, statt nur Schalter zu setzen
			if($Zusatz_moeglich[$i] == "gew" and $_SESSION['Dokument']['zusatz_'.$Zusatz_moeglich[$i]] == '1')
			{
				$_SESSION['Dokument']['zusatz_gew_typ'] = $_POST['gew_typ'];
				// Typ bedarf u.U. einer Änderung bei Deutschland als Raumebene (Korrektur erfolgt beim Schalter direkt)
			}
		}
		else
		{
			$_SESSION['Dokument']['zusatz_'.$Zusatz_moeglich[$i]] = '0';
		}
	}
}


// Normierung der Wertebasis
if($_POST['wertebasis'] == "reg") 
{
	$_SESSION['Dokument']['indikator_lokal'] = '1';
}

if($_POST['wertebasis'] == "deu") 
{
	$_SESSION['Dokument']['indikator_lokal'] = '0';
	$_SESSION['Dokument']['Fuellung']['Typ'] = 'Farbbereich'; // autom. Klassifizierung technisch schwierig und nicht sinnvoll
}




// Funktionsaufrufe zum Aufbau der Viewerlogik
// -------------------------------------------
SetReset();
Login();
Jahresrfassung($Verbindung);
Startkarte();
ID();
Projektion();
Raumgliederung();
Raumebene();
MenueEinAus();
SetView();
SetArrayDefaults();
SetIndikator();



// Funktionen
// ----------
function Login()
{
	include("includes_classes/verbindung_mysqli.php");
	
	// evtl. leere Pwd auf "leer" setzen
	if(!$Pwd = $_POST['Passwort']) 
	{
		$Pwd = "leer";
	}
	
	// soll für Intern/extern/Prüfungs-Zugangsberechtigung genutzt werden
	if($_POST['Rechte'] or $_POST['Rechte'] == "0")
	{ 
		$SQL_Login = "SELECT * FROM m_status_freigabe,m_status_freigabe_passwoerter 
						WHERE m_status_freigabe.STATUS_FREIGABE = m_status_freigabe_passwoerter.STATUS_FREIGABE 
						AND m_status_freigabe.STATUS_FREIGABE LIKE '".$_POST['Rechte']."%' 
						AND PASSWORT = '".$Pwd."' AND IP_RESTRIKT LIKE '".substr($_SERVER["REMOTE_ADDR"],0,6)."%'";
			$Ergebnis_Login = mysqli_query($Verbindung,$SQL_Login);
		// erfolgreicher Login
		$_SESSION['Dokument']['ViewBerechtigung'] = mysqli_result($Ergebnis_Login,0,'STATUS_FREIGABE'); 
		$_SESSION['Dokument']['ViewBerechtigung_IP_Restriktion'] = mysqli_result($Ergebnis_Login,0,'IP_RESTRIKT');
		
		// wenn evtl. geforderte IP nicht zutrifft...
		if($IP = @mysqli_result($Ergebnis_Login,0,'IP_RESTRIKT'))
		{
			if($IP != substr($_SERVER["REMOTE_ADDR"],0,6)) $_SESSION['Dokument']['ViewBerechtigung'] = "3";
			
		} 

		// Wenn $_SESSION['Dokument']['ViewBerechtigung'] leer ist, dann auf jeden Fall auf 3 = Gast setzen
		if(!@mysqli_result($Ergebnis_Login,0,'STATUS_NAME')) $_SESSION['Dokument']['ViewBerechtigung'] = "3";
	}
	else
	{
		// Standard-Login auf 3 = Gast setzen, wenn nichts übergeben wurde und kein Login gesetzt ist
		if(!$_SESSION['Dokument']['ViewBerechtigung'] and $_SESSION['Dokument']['ViewBerechtigung'] != "0") 	
		{
			$_SESSION['Dokument']['ViewBerechtigung'] = "3";
		}
	}
	
	// Logout verwalten und karte leeren in Funktion SetReset()

}

function Jahresrfassung($Verbindung)
{
	// Grundlegende Einstellung, muss gesetzt sein! => daraus resultiert der Inhalt der Auswahlrequests
	if($_GET['Jahr'] > '1900') 
	{
		// Jahr von Formular übernehmen
		$_SESSION['Dokument']['Jahr_Anzeige'] = $_GET['Jahr'];
		// Löschen von Tabellentool-Einstellungen (hinfällig mit Basisjahrwechsel)
		$_SESSION['Tabelle'] = array();
		$_SESSION[Tabellen_Sortierung] ='';
    	$_SESSION[Tabellen_Sortierung_asc_desc] ='';
    	$_SESSION[Tabellen_Sortierung_ZZ] ='';
    	$_SESSION[Tabellen_Sortierung_FC] ='';
	}
	
	// Prüfung der Verfügbarkeit der Kategorie im Zeitschnitt, sonst aktuellst verfügbaren Zeitschn. wählen
	// Für Prüfer nicht durchführen
	if($_SESSION['Dokument']['ViewBerechtigung'] != "0" and $_GET['Indikator'])
	{
		$SQL_Indikatoren_check = "SELECT * FROM m_indikatoren,m_indikator_freigabe 
											WHERE m_indikatoren.ID_INDIKATOR='".$_GET['Indikator']."' 
											AND m_indikatoren.ID_INDIKATOR = m_indikator_freigabe.ID_INDIKATOR 
											AND STATUS_INDIKATOR_FREIGABE >= '".$_SESSION['Dokument']['ViewBerechtigung']."' 
											ORDER BY JAHR DESC";
		
		if($Ergebnis_Indikatoren_check = mysqli_query($Verbindung,$SQL_Indikatoren_check)) echo "ok!";;
		$JAHR_aktuellst = mysqli_result($Ergebnis_Indikatoren_check,0,'JAHR');
		
		$i_ck=0;
        while($JAHR_check = @mysqli_result($Ergebnis_Indikatoren_check,$i_ck,'JAHR'))
		{
			$JAHR_check_values[$JAHR_check] = $JAHR_check;
			$i_ck++;
		}
		// Prüfen, ob Jahr freigabe hat, wenn nicht, höchstes verfügbares setzen
		if(!$JAHR_check_values[$_SESSION['Dokument']['Jahr_Anzeige']])
		{
			// aktuellstes mögl. Jahr setzen
			$_SESSION['Dokument']['Jahr_Anzeige'] = $JAHR_aktuellst;
			// Löschen von Tabellentool-Einstellungen (hinfällig mit Basisjahrwechsel)
			$_SESSION['Tabelle'] = array();
			$_SESSION[Tabellen_Sortierung] ='';
			$_SESSION[Tabellen_Sortierung_asc_desc] ='';
			$_SESSION[Tabellen_Sortierung_ZZ] ='';
			$_SESSION[Tabellen_Sortierung_FC] ='';
		}
		
	}
	
	
	// Erfassen der zugehörigen Geometrie zum gewählten Jahr (ermöglicht Mehrfach-Verwendung von Geometrietabellen)
	include("includes_classes/verbindung_mysqli.php");
	$SQL_Jahr_Geometrietabelle = "SELECT PostGIS_Tabelle_Jahr FROM v_geometrie_jahr_viewer_postgis WHERE Jahr_im_Viewer='".$_SESSION['Dokument']['Jahr_Anzeige']."'";
	$Ergebnis_Jahr_Geometrietabelle = mysqli_query($Verbindung,$SQL_Jahr_Geometrietabelle);
	$_SESSION['Dokument']['Jahr_Geometrietabelle'] = mysqli_result($Ergebnis_Jahr_Geometrietabelle,0,'PostGIS_Tabelle_Jahr'); 
	
	 // Jahr in Datensatz-Session-Arrays korrigieren
	if($_SESSION['Datenbestand'])
	{
		foreach($_SESSION['Datenbestand'] as $DatenSet)
		{
			$_SESSION['Datenbestand'][$DatenSet['NAME']]['DB_Tabelle'] 
									= $_SESSION['Datenbestand'][$DatenSet['NAME']]['DB_Tabelle_Teilstring']."_".$_SESSION['Dokument']['Raumgliederung']
																									."_".$_SESSION['Dokument']['Jahr_Geometrietabelle']
																									."_".$_SESSION['Dokument']['Raumgliederung_Zusatzkennung'];
		}
	}
	
	// Checken, ob AGS evtl. in gewähltem Jahr nicht mehr existiert
	// -------------------------------------------- ( weiter unten nochmals gebraucht -> evtl. mal in eine Funktion packen!?
	if(is_array($_SESSION['Datenbestand']))
	{
		// Alle Anzeigen auf 0 setzen
		foreach($_SESSION['Datenbestand'] as $DatenSet)
		{
			// HG ausklammern
			if($_SESSION['Datenbestand'][$DatenSet['NAME']]['View'] == '1')
			{
				// ----- Fehler AGS-Jahr -----
				// Schalter auf "1" setzen, falls AGS im angezeigten Jahr nicht existiert
				if($_SESSION['Dokument']['Jahr_Geometrietabelle'] < $_SESSION['Datenbestand'][$DatenSet['NAME']]['Jahr_min'] 
				or $_SESSION['Dokument']['Jahr_Geometrietabelle'] > $_SESSION['Datenbestand'][$DatenSet['NAME']]['Jahr_max']) 
				{
					//echo "<pre>";
					//print_r($_SESSION['Datenbestand'][$DS_NAME]);
					//echo "</pre>"; 
					
					$AGS_Fehler = 1;
					if($AGS_Fehler_Elemente) $Fehler_Trenner = ", ";
					$AGS_Fehler_Elemente = $AGS_Fehler_Elemente.$Fehler_Trenner.$_SESSION['Datenbestand'][$DatenSet['NAME']]['NAME_UTF8'];
				}
			}
		}

	}
			
	// ----- Fehler AGS-Jahr -----
	// Vermerk in Session Array bei Konflikt zwischen Geometrie und Jahr
	if($AGS_Fehler)
	{
		
		$_SESSION['Dokument']['AGS_Fehler'] = 1;
		$_SESSION['Dokument']['AGS_Fehler_Elemente'] = $AGS_Fehler_Elemente;
	}
	else
	{
		$_SESSION['Dokument']['AGS_Fehler'] = 0;
		$_SESSION['Dokument']['AGS_Fehler_Elemente'] = "";
	}
	
	// ---------------------------------------------------
}


function Startkarte()
{
	// Standardkarte anzeigen, wenn über Typo3 aufgerufen und $_POST['RESET'], Kat, Ind, Jahr leer sind
	if(!$_POST['RESET'] and !$_SESSION['Dokument']['Jahr_Anzeige'] and !$_SESSION['Dokument']['Fuellung']['Kategorie'] and !$_GET['idk'])
	{
		$_SESSION['Dokument']['Standardkarte'] = '2701'; // Standard Karte
	}
	else
	{
		$_SESSION['Dokument']['Standardkarte'] = '';
			
	}
}


function ID()
{
	include("includes_classes/verbindung_mysqli.php");
	$_SESSION['Besuch'] = true;
	$Viewer_Berechtigung_Speicher = $_SESSION['Dokument']['ViewBerechtigung'];
	$Viewer_Berechtigung_Speicher_IP_Restrikt = $_SESSION['Dokument']['ViewBerechtigung_IP_Restriktion'];
	$SESSION_Backup = $_SESSION;
	
	// Alle Einstellungen aus DB übernehmen, falls ID Übergeben wurde
	// $_GET['typo3'] abfrage verhindert nochmaliges laden, falls idk in Adresszeile vorkommt
//	if($_POST['idk'] or ($_GET['idk'] and $_GET['typo3']) or $_SESSION['Dokument']['Standardkarte'])  weggenommen wegen neuem viewer
		if($_POST['idk'] or ($_GET['idk']) or $_SESSION['Dokument']['Standardkarte']) 
	{
	
		
		// POST und GET mit Variable "id" zulassen
		if($_POST['idk']) $ID_POST=$_POST['idk'];
		if($_GET['idk']) $ID_POST=$_GET['idk'];
		
		// Standardkarte anzeigen
		if($_SESSION['Dokument']['Standardkarte']) $ID_POST = $_SESSION['Dokument']['Standardkarte'];
		
		// Erstellen der SESSION aus dem serialisierten Array heraus
		$SQL_id = "SELECT * FROM v_user_link_speicher WHERE id='".$ID_POST."'";
		$Ergebnis_id = mysqli_query($Verbindung,$SQL_id);
		
		if(@mysqli_result($Ergebnis_id,0,'array_value'))
		{
			$SV = @mysqli_result($Ergebnis_id,0,'array_value');
			$_SESSION = unserialize($SV);
			// Info zur Anzeige für User
			if($_SESSION['Dokument']['Sprache'] == 'DE') $GLOBALS['ID_Info'] = '<span>Karte '.$ID_POST.' angezeigt</span>';
			if($_SESSION['Dokument']['Sprache'] == 'EN') $GLOBALS['ID_Info'] = '<span>Map '.$ID_POST.' is loaded</span>';
		}
		else
		{
			// Info zur Anzeige für User
			if($_SESSION['Dokument']['Sprache'] == 'DE') $GLOBALS['ID_Info'] = '<span style="color:#EE5555; font-weight:bold;">Fehler: Karte '.$ID_POST.' nicht vorhanden</span>';
			if($_SESSION['Dokument']['Sprache'] == 'EN') $GLOBALS['ID_Info'] = '<span style="color:#EE5555; font-weight:bold;">Error: Map '.$ID_POST.' not available</span>';
			// $GLOBALS['ID_Info'] = '<span style="color:#EE5555; font-weight:bold;">Fehler: Karte '.$ID_POST.' nicht vorhanden.</span>';
			$GLOBALS['ID_Error'] = 1;
		}
		
		// fehlende (inaktive und deshalb gelöschte) Raumeinheiten wieder hinzufügen
		// ------------
		if(is_array($_SESSION['Dokument']['Raumebene']))
		{
			foreach($_SESSION['Dokument']['Raumebene'] as $REview)
			{
				// Aufzählung des als Hintergrund definierten Layers vermeiden... nur für dieses Menü wichtig => im Viewer extra behandelt 
				if($REview['NAME'] != 'Hintergrund' and $REview['View'] == '1')
				{
					$RE_aus_ID = $REview['NAME']; // Nur eine aktivierte RE möglich
				} 	
			}
		}
		
		// nur nicht vorhandene Raumeinheiten in SESSION-Array füllen
		$SQL_DS = "SELECT * FROM v_geometrie,v_raumebene WHERE v_geometrie.RAUMEBENE = v_raumebene.RAUMEBENE AND (v_raumebene.RAUMEBENE = '".$RE_aus_ID."' ) ORDER BY v_geometrie.NAME";
		$Ergebnis_DS = mysqli_query($Verbindung,$SQL_DS);	
		
		$i_ds = 0;
		while($DS_Name = @mysqli_result($Ergebnis_DS,$i_ds,'ID_GEOMETRIE'))
		{
			if(!$_SESSION['Datenbestand'][$DS_Name]['View'])
			{
				$_SESSION['Datenbestand'][$DS_Name]['View'] = '0';
				$_SESSION['Datenbestand'][$DS_Name]['NAME'] = @mysqli_result($Ergebnis_DS,$i_ds,'ID_GEOMETRIE'); 
				$_SESSION['Datenbestand'][$DS_Name]['NAME_HTML'] = @mysqli_result($Ergebnis_DS,$i_ds,'Name_UTF8'); // Name_HTML wird in DB nicht mehr gefüllt
				$_SESSION['Datenbestand'][$DS_Name]['NAME_UTF8'] = @mysqli_result($Ergebnis_DS,$i_ds,'Name_UTF8'); 
				
				// Check auf vorhandenes UTF8
				if(is_utf8(@mysqli_result($Ergebnis_DS,$i_ds,'Name_UTF8')))
				{
					$_SESSION['Datenbestand'][$DS_Name]['NAME_HTML'] = @mysqli_result($Ergebnis_DS,$i_ds,'Name_UTF8'); // Name_HTML wird in DB nicht mehr gefüllt
					$_SESSION['Datenbestand'][$DS_Name]['NAME_UTF8'] = @mysqli_result($Ergebnis_DS,$i_ds,'Name_UTF8'); 
				}
				else
				{
					$_SESSION['Datenbestand'][$DS_Name]['NAME_HTML'] = utf8_encode(@mysqli_result($Ergebnis_DS,$i_ds,'Name_UTF8')); // Name_HTML wird in DB nicht mehr gefüllt
					$_SESSION['Datenbestand'][$DS_Name]['NAME_UTF8'] = utf8_encode(@mysqli_result($Ergebnis_DS,$i_ds,'Name_UTF8')); 
				}
				
				
				$_SESSION['Datenbestand'][$DS_Name]['Jahr_min'] = @mysqli_result($Ergebnis_DS,$i_ds,'Jahr_min'); 
				$_SESSION['Datenbestand'][$DS_Name]['Jahr_max'] = @mysqli_result($Ergebnis_DS,$i_ds,'Jahr_max'); 
				$_SESSION['Datenbestand'][$DS_Name]['Auswahlkriterium'] = @mysqli_result($Ergebnis_DS,$i_ds,'SQL_Auswahlkriterium');
				$_SESSION['Datenbestand'][$DS_Name]['Auswahloperator'] = @mysqli_result($Ergebnis_DS,$i_ds,'SQL_Auswahloperator');
				$_SESSION['Datenbestand'][$DS_Name]['AuswahloperatorZusatz_1'] = @mysqli_result($Ergebnis_DS,$i_ds,'SQL_Auswahlop_Zusatz_1');
				$_SESSION['Datenbestand'][$DS_Name]['AuswahloperatorZusatz_2'] = @mysqli_result($Ergebnis_DS,$i_ds,'SQL_Auswahlop_Zusatz_2');
				$_SESSION['Datenbestand'][$DS_Name]['Transparenz'] = @mysqli_result($Ergebnis_DS,$i_ds,'Transparenz');
				$_SESSION['Datenbestand'][$DS_Name]['DB_Tabelle_Teilstring'] = @mysqli_result($Ergebnis_DS,$i_ds,'Postgres_Tabelle');
				$_SESSION['Datenbestand'][$DS_Name]['DB_Tabelle'] 
									= $_SESSION['Datenbestand'][$DS_Name]['DB_Tabelle_Teilstring']
									."_".$_SESSION['Dokument']['Raumgliederung']
									."_".$_SESSION['Dokument']['Jahr_Geometrietabelle']
									."_".$_SESSION['Dokument']['Raumgliederung_Zusatzkennung'];
				
		
				// Unterarray für Auswahlkriterien aus DB füllen (wenn z.B. mehrere Bundesländer gewählt werden müssen die 0 oder 1 als AGS haben können o.Ä.
				$SQL_DS_Kriterien = "SELECT * FROM v_geometrie_kriterium_werte WHERE ID_GEOMETRIE = '".$DS_Name."' ";
				$Ergebnis_DS_Kriterien = mysqli_query($Verbindung,$SQL_DS_Kriterien);
				$i_dsk = 0;
				while(@mysqli_result($Ergebnis_DS_Kriterien,$i_dsk,'id_werte'))
				{
					$_SESSION['Datenbestand'][$DS_Name]['Auswahlkriterium_Wert'][$i_dsk] = @mysqli_result($Ergebnis_DS_Kriterien,$i_dsk,'Wert');
					$i_dsk++;
				
				}
			}
			$i_ds++;			
		}	
			
		// -------------
				
		// Setzen der Berechtigung auf vorherige (vor dem laden des Speicherstandes) Wert
		$_SESSION['Dokument']['ViewBerechtigung'] = $Viewer_Berechtigung_Speicher;
		$_SESSION['Dokument']['ViewBerechtigung_IP_Restriktion'] = $Viewer_Berechtigung_Speicher_IP_Restrikt;
		
		// Prüfen der Berechtigung, den geforderten Inhalt anzuzeigen
		$SQL_Indikator_Rechte = "SELECT * FROM m_indikator_freigabe 
				WHERE ID_INDIKATOR = '".$_SESSION['Dokument']['Fuellung']['Indikator']."' AND JAHR = '".$_SESSION['Dokument']['Jahr_Anzeige']."'";
		$Ergebnis_Indikator_Rechte = mysqli_query($Verbindung,$SQL_Indikator_Rechte);
		// Viewer leeren, da ein unbefugter Zugriff oder ein Zeitschnittwechsel ohne entspr Freigabe stattgefunden hat
		if(@mysqli_result($Ergebnis_Indikator_Rechte,0,'STATUS_INDIKATOR_FREIGABE') < $_SESSION['Dokument']['ViewBerechtigung'])
		{
			// ... entschärft ....
			// $_SESSION['Status'] = "Reset";
			// SetReset();
			// ... nun kein Reset mehr sondern laden des vorherigen Zustands
			$_SESSION = $SESSION_Backup;
			// Info zur Anzeige für User
			if($_SESSION['Dokument']['Sprache'] == 'DE') $GLOBALS['ID_Info'] = '<span style="color:#EE5555; font-weight:bold;">Fehler: Karte '.$ID_POST.' nicht vorhanden</span>';
			if($_SESSION['Dokument']['Sprache'] == 'EN') $GLOBALS['ID_Info'] = '<span style="color:#EE5555; font-weight:bold;">Error: Map '.$ID_POST.' is not available</span>';
			$GLOBALS['ID_Error'] = 1;
			
		}
		
	}
	
	// Spracheinstellung wiederherstellen (betrifft alte Speicherstände)
	// $_SESSION['Dokument']['Sprache'] = '';
	if(!$_SESSION['Dokument']['Sprache']) $_SESSION['Dokument']['Sprache'] = 'DE';
	if($_GET['lang']) $_SESSION['Dokument']['Sprache'] = $_GET['lang'];
		
}


function SetReset()
{
	
	// Reset der Beschriftungssetzung
	if($_POST['RESET_LABELS'])
	{
        // Leeren des Array
		unset($_SESSION['Dokument']['LabelAnzeige']);
	}
	// Mitnehmen der Spracheinstellung
	$Language = $_SESSION['Dokument']['Sprache'];
	
	// Reset all
	// alle Einstellungen des Nutzers verwerfen und/oder bei Neustart alte Cache-Daten verwerfen
	if($_POST['RESET'] or $_SESSION['Status'] != 'aktiv' or $_POST['Rechte'] == "LOGOUT" or $_GET['SetReset']) 
	{
		include("includes_classes/verbindung_mysqli.php");
		
		// Nur Bereiche des Array löschen => evtl Farbauswahl soll erhalten bleiben [User]
		$_SESSION['Datenbestand'] = array();
		// Login beibehalten:
		$Login_behalten = $_SESSION['Dokument']['ViewBerechtigung'];
		$_SESSION['Dokument'] = array();
		$_SESSION['Dokument']['ViewBerechtigung'] = $Login_behalten;
		
		$_SESSION['Temp'] = array();
		$_SESSION['Tabelle'] = array();
		$_SESSION['Status'] = '';
		
		// Zeichenvorschrift default laden (evtl. zukünftig aus DB)
		SetZeichenvorschrift();
		
		// Login erfassen
		Login();
		
		// Dokumenten-Rahmenbedingungen setzen (Größe,Rand)
		$_SESSION['Dokument']['groesse_X'] = "550";
		$_SESSION['Dokument']['groesse_Y'] = "550";
		$_SESSION['Dokument']['Rand_L'] = "10";
		$_SESSION['Dokument']['Rand_R'] = "200";
		$_SESSION['Dokument']['Rand_O'] = "50";
		$_SESSION['Dokument']['Rand_U'] = "20";
		$_SESSION['Dokument']['Hoehe_Legende_unten'] = "200";
		
		
		// Klassenauflösung vordefinieren, sonst evtl Fehler
		if(!$_SESSION['Dokument']['Klassen']['Aufloesung']) $_SESSION['Dokument']['Klassen']['Aufloesung'] = 7;
		
		
		// Jahr initialisieren
		$_SESSION['Dokument']['Jahr_Anzeige'] = ''; // Neuerdings ohne Jahresauswahl ok;
		/* 
		$SQL_Jahr = "SELECT JAHR FROM m_them_kategorie_freigabe WHERE STATUS_KATEGORIE_FREIGABE >= '".$_SESSION['Dokument']['ViewBerechtigung']."' GROUP BY JAHR ORDER BY JAHR DESC"; 
		$Ergebnis_Jahr = mysqli_query($Verbindung,$SQL_Jahr);
		$_SESSION['Dokument']['Jahr_Anzeige'] = @mysqli_result($Ergebnis_Jahr,0,'JAHR');
		 */
		// voreingestellte Datenbasis für Berechnung der Zeichenvorschrift (1=lokal, 0=global)
		$_SESSION['Dokument']['indikator_lokal'] = '1';
		
		// Erfassen der zugehörigen Geometrie zum gewählten Jahr (ermöglicht Mehrfach-Verwendung von Geometrietabellen)
		$SQL_Jahr_Geometrietabelle = "SELECT PostGIS_Tabelle_Jahr FROM v_geometrie_jahr_viewer_postgis WHERE Jahr_im_Viewer='".$_SESSION['Dokument']['Jahr_Anzeige']."'";
		$Ergebnis_Jahr_Geometrietabelle = mysqli_query($Verbindung,$SQL_Jahr_Geometrietabelle);
		$_SESSION['Dokument']['Jahr_Geometrietabelle'] = @mysqli_result($Ergebnis_Jahr_Geometrietabelle,0,'PostGIS_Tabelle_Jahr');
		
		// Mitnehmen der Spracheinstellung
		$_SESSION['Dokument']['Sprache'] = $Language;
		 
	}
}


function Projektion()
{
	
	$_SESSION['Dokument']['PG_SRID'] = '3035';
	$_SESSION['Dokument']['PG_SVG_Genauigkeit'] = 0;
	// Temporär ist die Auswahl der Projektion deaktiviert, da in der Einstiegskarte eine falsche Projektion stecken kann
	/* // Koordinatensystem/Projektion 
	// Gauß-Krüger
	if($_GET['projektion'] == 'gk')
	{
		$_SESSION['Dokument']['PG_SRID'] = '31465';
		// Nachkommastellen für Koordinaten
		$_SESSION['Dokument']['PG_SVG_Genauigkeit'] = 0;
	}
	
	// Lambert azimutal (Default)
	if(!$_SESSION['Dokument']['PG_SRID'] or $_GET['projektion'] == 'lambert')
	{
		
		$_SESSION['Dokument']['PG_SRID'] = '3035';
		// Nachkommastellen für Koordinaten
		$_SESSION['Dokument']['PG_SVG_Genauigkeit'] = 0;
	}	 */
}


function Raumgliederung()
{
	// ------------- erfassen einer gültigen Raumgliederungskennung --------------
	
	// Automatische Aktivierung der Gemeindegrenzen-Zusatzebene für Beschriftung auf Rasterbasis bei setzen der Raumgliederung auf "rst"
	// ??? if($_SESSION['Dokument']['Raumgliederung'] != "rst" and ($_POST['Raumgliederung'] or $GLOBALS['RGliederung_automatisch'] or $_GET['Raumgliederung'])) 
	//{
		// Defaulteinstellung nach bestimmten Kriterien setzen, oder eben nicht: ... nur auf Raumebene: Kreis!
		// Für Raster 1km
		if(($_POST['Raumgliederung'] == "rst" or $GLOBALS['RGliederung_automatisch'] == "rst" or $_GET['Raumgliederung'] == "rst") 
		and $_SESSION['Dokument']['Raumebene']['Bundesland']['View'] != '1'
		and $_SESSION['Dokument']['Raumebene']['Deutschland']['View'] != '1') 
		{
			if($_SESSION['Dokument']['Raumebene_NAME_Auswertung'] != $_GET['RaumebeneGewaehlt'] and $_SESSION['Dokument']['Raumebene_NAME_Auswertung'] == 'Kreis' )
			{
				$_SESSION['Dokument']['zusatz_gemeinde'] = "1"; 
			}	
		}
		// Für Raster 5km
		if(($_POST['Raumgliederung'] == "r05" or $GLOBALS['RGliederung_automatisch'] == "r05" or $_GET['Raumgliederung'] == "r05")) 
		{
			if($_SESSION['Dokument']['Raumebene_NAME_Auswertung'] != $_GET['RaumebeneGewaehlt'])
			{
				$_SESSION['Dokument']['zusatz_kreis'] = "1"; 
			}
		}
		// Für Raster 10km
		if(($_POST['Raumgliederung'] == "r10" or $GLOBALS['RGliederung_automatisch'] == "r10" or $_GET['Raumgliederung'] == "r10") ) 
		{
			if($_SESSION['Dokument']['Raumebene_NAME_Auswertung'] != $_GET['RaumebeneGewaehlt'])
			{
				$_SESSION['Dokument']['zusatz_bundesland'] = "1"; 
			}
		}

	//}
	
	// Zusatzebene Gemeindegrenzen generell nur für Raumebene Kreis zulassen
	if($_SESSION['Dokument']['Raumebene']['Kreis']['View'] != '1') 
	{
		$_SESSION['Dokument']['zusatz_gemeinde'] = "0"; 
	}
	
	// Raumgliederung erfassen
	if($_POST['Raumgliederung'] or $GLOBALS['RGliederung_automatisch'] or $_GET['Raumgliederung']) 
	{
	
		$_SESSION['Dokument']['Raumgliederung'] = $_POST['Raumgliederung'];
		// Übernahme von voreingestellten Raumgliederungen (darf nur eine sein)
		if(!$_SESSION['Dokument']['Raumgliederung'] and $GLOBALS['RGliederung_automatisch']) $_SESSION['Dokument']['Raumgliederung'] = $GLOBALS['RGliederung_automatisch'];
		// Übername von Adresszeile (GET) bei Direktaufruf
		if($_GET['Raumgliederung']) $_SESSION['Dokument']['Raumgliederung'] = $_GET['Raumgliederung'];
		
		// Automatische Aktivierung der Gemeindegrenzen-Zusatzebene für Beschriftung auf Rasterbasis bei setzen der Raumgliederung auf "rst"
		// if($_SESSION['Dokument']['Raumgliederung'] != "rst") $_SESSION['Dokument']['zusatz_gemeinde'] = "0"; 
	}
	
	
	
	
	// Prüfen der Berechtigung für RASTER-Anzeige
	include("includes_classes/verbindung_mysqli.php");
	$SQL_Indikator_Rechte = "SELECT * FROM m_indikator_freigabe WHERE ID_INDIKATOR = '".$_SESSION['Dokument']['Fuellung']['Indikator']."' AND JAHR = '".$_SESSION['Dokument']['Jahr_Anzeige']."'";
		$Ergebnis_Indikator_Rechte = mysqli_query($Verbindung,$SQL_Indikator_Rechte);

// echo "<br />RASTER_FREIGABE=".@mysqli_result($Ergebnis_Indikator_Rechte,0,'RASTER_FREIGABE');
// echo "<br />Raumgliederung=".$_SESSION['Dokument']['Raumgliederung'];

	// Raster-Anzeige prüfen... falls nicht verfügbar, auf "gem" umschalten
	if(!@mysqli_result($Ergebnis_Indikator_Rechte,0,'RASTER_FREIGABE'))
	{
// echo "<br />Schleife";
		// Rasteranzeige wenn gewählt umschalten
		if(($_SESSION['Dokument']['Raumgliederung'] == "rst" or $_SESSION['Dokument']['Raumgliederung'] == "r05" or $_SESSION['Dokument']['Raumgliederung'] == "r10") and $_SESSION['Dokument']['ViewBerechtigung'] != "0")
		{
			$_SESSION['Dokument']['Raumgliederung'] = "krs"; 
			$Raumgliederung_Eingriff = 1;
		}
		// Schalter für Anzeige der Rasterdarstellung für Raumgliederungsauswahl hinterlegen
		$_SESSION['Dokument']['Raumgliederung_Raster_ok'] = "0";
	}
	else
	{
		// Schalter für Anzeige der Rasterdarstellung für Raumgliederungsauswahl hinterlegen
		$_SESSION['Dokument']['Raumgliederung_Raster_ok'] = "1";	
	}





// Baustelle
// -------------------------->	
	// Generelle Prüfung auf Veröffentlichung der Raumebene für gewählten Indikator (vorerst nicht Raster, diese wurden schon geprüft und sind in Tabelle noch nicht vollst. implementiert)
    if($_SESSION['Dokument']['Raumgliederung'] == "gem" and $_SESSION['Dokument']['ViewBerechtigung'] != "0")
	{
		// Check der Verfügbarkeit der Raumgliederung
		$SQL_Raumgliederung_Ind = "SELECT * FROM m_indikatoren WHERE ID_INDIKATOR = '".$_SESSION['Dokument']['Fuellung']['Indikator']."' AND RAUMEBENE_".$_SESSION['Dokument']['Raumgliederung']." = '1'";
		$Ergebnis_Raumgliederung_Ind = mysqli_query($Verbindung,$SQL_Raumgliederung_Ind);
		
		if(!@mysqli_result($Ergebnis_Raumgliederung_Ind,0,'ID_INDIKATOR'))
		{
			// Ändern der Raumebene auf Verfügbare Ebene (vorerst nur Kreis, da übergeordnete Ebenen ab Kreis immer da sein sollten)
			$_SESSION['Dokument']['Raumgliederung'] = "krs"; 
			$Raumgliederung_Eingriff = 1;
			
			echo '<script type="text/javascript">
				alert("Die gewünschte Raumgliederung ist leider nicht verfügbar.");
				</script>
				';
		}
	}
	
	// Generelle Prüfung auf Veröffentlichung der Raumebene für gewählten Indikator (vorerst nicht Raster, diese wurden schon geprüft und sind in Tabelle noch nicht vollst. implementiert)
    if($_SESSION['Dokument']['Raumgliederung'] == "bld" and $_SESSION['Dokument']['ViewBerechtigung'] != "0")
	{
		// Check der Verfügbarkeit der Raumgliederung
		$SQL_Raumgliederung_Ind = "SELECT * FROM m_indikatoren WHERE ID_INDIKATOR = '".$_SESSION['Dokument']['Fuellung']['Indikator']."' AND RAUMEBENE_BLD = '1'";
		$Ergebnis_Raumgliederung_Ind = mysqli_query($Verbindung,$SQL_Raumgliederung_Ind);
		
		if(!@mysqli_result($Ergebnis_Raumgliederung_Ind,0,'ID_INDIKATOR'))
		{
			// Ändern der Raumebene auf Verfügbare Ebene (vorerst nur Kreis, da übergeordnete Ebenen ab Kreis immer da sein sollten)
			$_SESSION['Dokument']['Raumgliederung'] = "krs"; 
			$Raumgliederung_Eingriff = 1;
			
			/* echo '<script type="text/javascript">
				alert("Die gewünschte Raumgliederung ist leider nicht verfügbar.");
				</script>
				'; */
		}
	}
	
	// Generelle Prüfung auf Veröffentlichung der Raumebene für gewählten Indikator (vorerst nicht Raster, diese wurden schon geprüft und sind in Tabelle noch nicht vollst. implementiert)
    if($_SESSION['Dokument']['Raumgliederung'] == "ror" and $_SESSION['Dokument']['ViewBerechtigung'] != "0")
	{
		// Check der Verfügbarkeit der Raumgliederung
		$SQL_Raumgliederung_Ind = "SELECT * FROM m_indikatoren WHERE ID_INDIKATOR = '".$_SESSION['Dokument']['Fuellung']['Indikator']."' AND RAUMEBENE_ROR = '1'";
		$Ergebnis_Raumgliederung_Ind = mysqli_query($Verbindung,$SQL_Raumgliederung_Ind);
		
		if(!@mysqli_result($Ergebnis_Raumgliederung_Ind,0,'ID_INDIKATOR'))
		{
			// Ändern der Raumebene auf Verfügbare Ebene (vorerst nur Kreis, da übergeordnete Ebenen ab Kreis immer da sein sollten)
			$_SESSION['Dokument']['Raumgliederung'] = "krs"; 
			$Raumgliederung_Eingriff = 1;
			
			/* echo '<script type="text/javascript">
				alert("Die gewünschte Raumgliederung ist leider nicht verfügbar.");
				</script>
				'; */
		}
	}
	
	
	
// -------------------------->	






	
	// ------------- restliche Umgebungsvariablen setzen -------------
	if($_POST['Raumgliederung'] or $GLOBALS['RGliederung_automatisch'] or $_GET['Raumgliederung'] or $Raumgliederung_Eingriff) 
	{
		include("includes_classes/verbindung_mysqli.php");
		$SQL_Raumgliederung_Stellenanzahl = "SELECT * FROM v_raumgliederung WHERE DB_Kennung = '".$_SESSION['Dokument']['Raumgliederung']."'"; // sollte mit DB_Kennung sicher funktionieren
		$Ergebnis_Raumgliederung_Stellenanzahl = mysqli_query($Verbindung,$SQL_Raumgliederung_Stellenanzahl);
		$_SESSION['Dokument']['Raumgliederung_Stellenanzahl'] = @mysqli_result($Ergebnis_Raumgliederung_Stellenanzahl,0,'DB_AGS_Stellenanzahl');
	
		foreach($_SESSION['Datenbestand'] as $DatenSet)
		{
			$_SESSION['Datenbestand'][$DatenSet['NAME']]['DB_Tabelle'] = $_SESSION['Datenbestand'][$DatenSet['NAME']]['DB_Tabelle_Teilstring']."_"
																							.$_SESSION['Dokument']['Raumgliederung']
																							."_".$_SESSION['Dokument']['Jahr_Geometrietabelle']
																							."_".$_SESSION['Dokument']['Raumgliederung_Zusatzkennung'];
		}
	}
	
	// Korrekte DB_Kennung auch hier ermitteln
	if(is_array($_SESSION['Dokument']['Raumebene']))
	{
		foreach($_SESSION['Dokument']['Raumebene'] as $REview)
		{
			// Aufzählung des als Hintergrund definierten Layers vermeiden... nur für dieses Menü wichtig => im Viewer extra behandelt 
			if($REview['NAME'] != 'Hintergrund')
			{
				if($REview['View']=='1')
				{
					$Raumeinheit = $REview['NAME']; // gewählte Raumeinheit für Hinweis auf richtige geometrie (grob/fein)
					$SQL_Raumgliederung_DBZK = "SELECT DB_Zusatzkennung FROM v_raumgliederung WHERE DB_Kennung = '".$_SESSION['Dokument']['Raumgliederung']."' AND RAUMEBENE = '".$Raumeinheit."'";
						$Ergebnis_Raumgliederung_DBZK = mysqli_query($Verbindung,$SQL_Raumgliederung_DBZK);
				
					// !!!!!!!!!!!!!!!!!!! ACHTUNG !!!!!!!!!!!!!!!!!!!!!
					// Spalte/Attribut wird nur beim wechseln der RE gecheckt und muss deswegen über die gesamte RE immer die gleiche sein!!!!!!!!!!!
					$_SESSION['Dokument']['Raumgliederung_Zusatzkennung'] = @mysqli_result($Ergebnis_Raumgliederung_DBZK,0,'DB_Zusatzkennung');
				}				
			} 	
		}
	}
	// ---- AUSNAHME DURCH RASTER --- Zusatzebene für Gemeindegrenzen-Anzeige ausblenden, wenn Raster nicht gewählt
	// if($_SESSION['Dokument']['Raumgliederung'] != "rst") $_SESSION['Dokument']['zusatz_gemeinde'] = "0"; 	
}

function Raumebene()
{
		
	// Vermerk, ob sich Raumebene tatsächlich geändert hat
	if(!$_SESSION['Dokument']['Raumebene'] or ($_GET['RaumebeneGewaehlt'] and $_SESSION['Dokument']['Raumebene'][$_GET['RaumebeneGewaehlt']]['View'] != '1'))
	{	
		
		// Wenn REbene gewählt, dann übernehmen
		$REbene_aktuell = $_GET['RaumebeneGewaehlt'];
		
		// Setzen der Default-Raumebene (Deutschland), wenn keine REbene gewählt und in Session noch nicht vorhanden
		if(!$_GET['RaumebeneGewaehlt'])
		{
			$REbene_aktuell = "Deutschland";
		}
		
		
		$_SESSION['Dokument']['Viewer_Datenset_ok'] = '0'; // Viewer Anzeige unterdrücken <= Region geleert/ noch nicht gewählt
		$GLOBALS['Raumebene_geaendert'] = "ja"; // Veranlassen, das $SESSION['Datenbestand'] erneuert wird
	
	
		// Raumebenen erfassen
		include("includes_classes/verbindung_mysqli.php");
		$SQL_Raumebene = "SELECT * FROM v_raumebene ORDER BY Sortierung";
		$Ergebnis_Raumebene = mysqli_query($Verbindung,$SQL_Raumebene);
		$i_re = 0;
		while($RE_Name = mysqli_result($Ergebnis_Raumebene,$i_re,'RAUMEBENE'))
		{
			$_SESSION['Dokument']['Raumebene'][$RE_Name]['View'] = '0'; // nur anwenden wenn Änderung oder Reset
			$_SESSION['Dokument']['Raumebene'][$RE_Name]['NAME'] = $RE_Name;
			$_SESSION['Dokument']['Raumebene'][$RE_Name]['NAME_HTML'] = utf8_encode(@mysqli_result($Ergebnis_Raumebene,$i_re,'RAUMEBENE'));
			$i_re++;
		}
		
		// gewählte Raumebene auf View=1 setzen und SQL zusammenstellen für Regionen
		if($REbene_aktuell) $_SESSION['Dokument']['Raumebene'][$REbene_aktuell]['View'] = '1';
		if($REbene_aktuell) $GLOBALS['RE_Select'] = " OR v_raumebene.RAUMEBENE = '".$REbene_aktuell."'";
		
		
		
		// Erste vorgesehene Raumgliederungen erfassen (wird benötigt, wenn es nur eine Region in der Raumebene zur Auswahl gibt und diese in DB auf automatisch aktiviert steht)
		// Beispiel Deutschland > Deutschland
		$SQL_Raumgliederung_menue = "SELECT * FROM v_raumgliederung WHERE RAUMEBENE = '".$REbene_aktuell."' ORDER BY Sortierung";
		$Ergebnis_Raumgliederung_menue = mysqli_query($Verbindung,$SQL_Raumgliederung_menue);
		
		$GLOBALS['RGliederung_automatisch'] = @mysqli_result($Ergebnis_Raumgliederung_menue,0,'DB_Kennung');
		Raumgliederung(); // Einbindung über Funktionsaufruf der Standard-Funktion für Raumgliederungen	
		
		
		//  DB-Zusatzkennung erfassen (für Anzeige des korrekt generalisierten Datensatzes wichtig)
		if(is_array($_SESSION['Dokument']['Raumebene']))
		{
			foreach($_SESSION['Dokument']['Raumebene'] as $REview)
			{
				// Aufzählung des als Hintergrund definierten Layers vermeiden... nur für dieses Menü wichtig => im Viewer extra behandelt 
				if($REview['NAME'] != 'Hintergrund')
				{
					if($REview['View']=='1')
					{
						$Raumeinheit = $REview['NAME']; // gewählte Raumeinheit für Hinweis auf richtige geometrie (grob/fein)
						$SQL_Raumgliederung_DBZK = "SELECT DB_Zusatzkennung FROM v_raumgliederung WHERE DB_Kennung = '".$_SESSION['Dokument']['Raumgliederung']."' AND RAUMEBENE = '".$Raumeinheit."'";
						$Ergebnis_Raumgliederung_DBZK = mysqli_query($Verbindung,$SQL_Raumgliederung_DBZK);
					
						// !!!!!!!!!!!!!!!!!!! ACHTUNG !!!!!!!!!!!!!!!!!!!!!
						// Spalte/Attribut wird nur beim wechseln der RE gecheckt und muss deswegen über die gesamte RE immer die gleiche sein!!!!!!!!!!!
						$_SESSION['Dokument']['Raumgliederung_Zusatzkennung'] = @mysqli_result($Ergebnis_Raumgliederung_DBZK,0,'DB_Zusatzkennung');
					}				
				} 	
			}
		}
	}
}


function MenueEinAus()
{
	// Ebenenliste ein-/ausblenden ..... bei Gelegenheit in POST umwandeln!
	if($_GET['Aktion'] == 'Ebenen_Anzeige_hide' and $_SESSION['Dokument']['Ebenen_Anzeige'] == '')
	{
		$_SESSION['Dokument']['groesse_X'] = $_SESSION['Dokument']['groesse_X']+220;
		$_SESSION['Dokument']['groesse_Y'] = $_SESSION['Dokument']['groesse_Y']+200;
		$_SESSION['Dokument']['Ebenen_Anzeige'] = 'hide';	
	}
	if($_GET['Aktion'] == 'Ebenen_Anzeige_show' and $_SESSION['Dokument']['Ebenen_Anzeige'] == 'hide') 
	{
		$_SESSION['Dokument']['groesse_X'] = $_SESSION['Dokument']['groesse_X']-220;
		$_SESSION['Dokument']['groesse_Y'] = $_SESSION['Dokument']['groesse_Y']-200;
		$_SESSION['Dokument']['Ebenen_Anzeige'] = '';	
	}
}

// gewählten Indikator und Kategorien aktivieren
function SetIndikator()
{
	
	
	// Kategorie erfassen
	if($_GET['Kategorie'])
	{
		$_SESSION['Dokument']['Fuellung']['Kategorie'] = $_GET['Kategorie'];
		$_SESSION['Dokument']['Fuellung']['Indikator'] = ""; // Bei Kategorie-Wechsel leeren <- Indikator nicht mehr zutreffend für Kategorie
		
	}
	
	
	// Indikator erfassen, nur falls Indikator oder Kategorie wirklich geändert
	if($_GET['Indikator'])
	{
		$_SESSION['Dokument']['Fuellung']['Indikator'] = $_GET['Indikator'];
		
		/* in SVG Verschoben, Produziert Fehler im Array... : */
		
		// weitere Indikator-Infos erfassen
		include("includes_classes/verbindung_mysqli.php");
			
		$SQL_Indikator_Info = "SELECT * FROM m_indikatoren WHERE ID_INDIKATOR='".$_SESSION['Dokument']['Fuellung']['Indikator']."'";
		$Ergebnis_Indikator_Info = mysqli_query($Verbindung,$SQL_Indikator_Info);
		$_SESSION['Dokument']['Fuellung']['Indikator_Einheit'] = utf8_encode(@mysqli_result($Ergebnis_Indikator_Info,0,'EINHEIT'));
		$_SESSION['Dokument']['Fuellung']['Indikator_Beschreibung'] = utf8_encode(@mysqli_result($Ergebnis_Indikator_Info,0,'INDIKATOR_NAME'));
		$_SESSION['Dokument']['Fuellung']['Rundung'] = @mysqli_result($Ergebnis_Indikator_Info,0,'RUNDUNG_NACHKOMMASTELLEN');
		// wird in svg_svg.php nochmals geprüft und ergänzt
		
		
		// Raumgliederung nochmals checken, da unter Umständen RASTER nicht angezeigt werden darf (Ausnahmeregelung)
		Raumgliederung();
		// Zeichenvorschrift neu setzen, wenn Indilator neu gewählt
		SetZeichenvorschrift();
		// Check für Zeitschnitt-Anpassung für Indikator (bei Nichtverfügbarkeit im Zeitschnitt)
		// ... klappt hier nicht ... Jahresrfassung($_SESSION['Dokument']['Fuellung']['Indikator']);
	}
}

function SetZeichenvorschrift()
{
	
	include("includes_classes/verbindung_mysqli.php");
	
	// Manuelle Klasse leeren, damit dadurch kein Unfug zustande kommt
	$_SESSION['Temp']['manuelle_Klasse'] = "leer";
	
	// Spezielle ZV für Indikator aus DB laden
	if($_SESSION['Dokument']['Fuellung']['Indikator'])
	{
		$SQL_ZV = "SELECT * FROM m_zeichenvorschrift WHERE ID_INDIKATOR='".$_SESSION['Dokument']['Fuellung']['Indikator']."'";
		$Ergebnis_ZV = mysqli_query($Verbindung,$SQL_ZV);
		
		// Falls ZV nicht definiert wurde, weiter unten Standard-ZV mit ID=1 verwenden
		if(!@mysqli_result($Ergebnis_ZV,0,'ID_ZEICHENVORSCHRIFT'))
		{
			$Indikator_ZV_nicht_definiert = '1';
		}
		else
		{	
			$_SESSION['Dokument']['Fuellung']['Typ'] = @mysqli_result($Ergebnis_ZV,0,'TYP_FUELLUNG');
			$_SESSION['Dokument']['Fuellung']['Untertyp'] = @mysqli_result($Ergebnis_ZV,0,'UNTERTYP_FUELLUNG'); // kann gefüllt sein, muss aber nicht
			$_SESSION['Dokument']['Fuellung']['Farbwert_min'] = @mysqli_result($Ergebnis_ZV,0,'FARBWERT_MIN');
			$_SESSION['Dokument']['Fuellung']['Farbwert_max'] = @mysqli_result($Ergebnis_ZV,0,'FARBWERT_MAX');
			$_SESSION['Dokument']['Fuellung']['LeerFarbe'] = @mysqli_result($Ergebnis_ZV,0,'FARBWERT_LEER');
			$_SESSION['Dokument']['Strichfarbe'] = @mysqli_result($Ergebnis_ZV,0,'FARBWERT_KONTUR');
			$_SESSION['Dokument']['Strichfarbe_MouseOver'] = @mysqli_result($Ergebnis_ZV,0,'FARBWERT_MOUSEOVER');
			$_SESSION['Dokument']['Textfarbe_Labels'] = @mysqli_result($Ergebnis_ZV,0,'FARBWERT_TEXT');
			$_SESSION['Dokument']['Klassen']['Aufloesung'] = @mysqli_result($Ergebnis_ZV,0,'KLASSEN_AUFLOESUNG');
			
			
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
	}
	
	// Standard ZV
	if(!$_SESSION['Dokument']['Fuellung']['Indikator'] or $Indikator_ZV_nicht_definiert)
	{
		// Standard-ZV mit ID=1 verwenden
		$SQL_ZV = "SELECT * FROM m_zeichenvorschrift WHERE ID_ZEICHENVORSCHRIFT='1'";
		$Ergebnis_ZV = mysqli_query($Verbindung,$SQL_ZV);
				
		$_SESSION['Dokument']['Fuellung']['Typ'] = @mysqli_result($Ergebnis_ZV,0,'TYP_FUELLUNG');
		$_SESSION['Dokument']['Fuellung']['Farbwert_min'] = @mysqli_result($Ergebnis_ZV,0,'FARBWERT_MIN');
		$_SESSION['Dokument']['Fuellung']['Farbwert_max'] = @mysqli_result($Ergebnis_ZV,0,'FARBWERT_MAX');
		$_SESSION['Dokument']['Fuellung']['LeerFarbe'] = @mysqli_result($Ergebnis_ZV,0,'FARBWERT_LEER');
		$_SESSION['Dokument']['Strichfarbe'] = @mysqli_result($Ergebnis_ZV,0,'FARBWERT_KONTUR');
		$_SESSION['Dokument']['Strichfarbe_MouseOver'] = @mysqli_result($Ergebnis_ZV,0,'FARBWERT_MOUSEOVER');
		$_SESSION['Dokument']['Textfarbe_Labels'] = @mysqli_result($Ergebnis_ZV,0,'FARBWERT_TEXT');
		$_SESSION['Dokument']['Klassen']['Aufloesung'] = @mysqli_result($Ergebnis_ZV,0,'KLASSEN_AUFLOESUNG');
		
		// BAB-Signatur standardmäßig ein
		$_SESSION['Dokument']['Strichfarbe_BAB_Signatur'] = '1';
		
		// Zusatzebenenfärbung
		$_SESSION['Dokument']['Strichfarbe_BAB'] = "EEEE00";
		$_SESSION['Dokument']['Strichfarbe_GEW'] = "000099";		 
		
		// Für Differenzkarten über Tabellentool wichtig
		$_SESSION['Dokument']['Fuellung']['Farbwert_Diff_min'] = @mysqli_result($Ergebnis_ZV,0,'FARBWERT_DIFF_MIN');
		$_SESSION['Dokument']['Fuellung']['Farbwert_Diff_max'] = @mysqli_result($Ergebnis_ZV,0,'FARBWERT_DIFF_MAX');
	}
	
	// Klassen neu berechnen 
	include('svg_klassenbildung.php');

}

function SetView()
{
	// Änderungen an den Einstellungen übernehmen
	if($_POST['Aktion']=='Datensatz')
	{
		if(is_array($_SESSION['Datenbestand']))
		{
			// Alle Anzeigen auf 0 setzen
			foreach($_SESSION['Datenbestand'] as $DatenSet)
			{
				// HG ausklammern
				if($_SESSION['Datenbestand'][$DatenSet['NAME']]['View'] == '0' or $_SESSION['Datenbestand'][$DatenSet['NAME']]['View'] == '1')
				{
					$_SESSION['Datenbestand'][$DatenSet['NAME']]['View'] = '0';
				}
			}
		}
		// Dokumentsettings löschen
		// $_SESSION['Dokument']=array(); // .... macht Alle Auswahlen kaputt...anscheinend aber auch nicht nötig
		
		// Gewählte Datensätze für Anzeige wieder auf 1 schalten
		$DatensaetzeGewaehlt = $_POST['DatensaetzeGewaehlt'];
		// Ersetzen des Array bei Auswahl von Deutschland als Raumebene => nur noch Deutschland zur Anzeige übrig
		// if($_POST['DatensaetzeGewaehlt_DE']) $DatensaetzeGewaehlt[0] = "Deutschland";
		if(is_array($DatensaetzeGewaehlt))
		{
			foreach($DatensaetzeGewaehlt as $DS_NAME)
			{
				// Eingrenzung auf derzeit nur 3 Raumeinheiten gleichzeitig <---------------------------------------------------- EINGRENZUNG auf 3 RAUMEINHEITEN ------------------------
				// Für Prüfer abschalten und auch nur, wenn Schalter $RE_Begrenzung gesetzt ist oder $_SESSION['Dokument']['Raumgliederung'] == "rst"
				if(($GLOBALS['RE_Begrenzung'] or $_SESSION['Dokument']['Raumgliederung'] == "rst") and $_SESSION['Dokument']['ViewBerechtigung'] != "0")
				{
					if($Anz_WE_view < $GLOBALS['RE_Begrenzung_Anz'])
					{ 
						$_SESSION['Datenbestand'][$DS_NAME]['View'] = '1';
						$_SESSION['Dokument']['Viewer_Datenset_ok'] = '1'; // Viewer-Anzeige aktivieren
					} 
				}
				else
				{
					$_SESSION['Datenbestand'][$DS_NAME]['View'] = '1';
					$_SESSION['Dokument']['Viewer_Datenset_ok'] = '1'; // Viewer-Anzeige aktivieren
				}
				$Anz_WE_view++;
			}
			
		
			
			
			// ----- Fehler AGS-Jahr -----
			// Schalter auf "1" setzen, falls AGS im angezeigten Jahr nicht existiert
			if($_SESSION['Dokument']['Jahr_Geometrietabelle'] < $_SESSION['Datenbestand'][$DS_NAME]['Jahr_min'] 
			or $_SESSION['Dokument']['Jahr_Geometrietabelle'] > $_SESSION['Datenbestand'][$DS_NAME]['Jahr_max']) 
			{
				/* echo "<pre>";
				print_r($_SESSION['Datenbestand'][$DS_NAME]);
				echo "</pre>"; */
				
				$AGS_Fehler = 1;
				if($AGS_Fehler_Elemente) $Fehler_Trenner = ", ";
				$AGS_Fehler_Elemente = $AGS_Fehler_Elemente.$Fehler_Trenner.$_SESSION['Datenbestand'][$DS_NAME]['NAME_UTF8'];
			}
		}
		
			
		
		// ----- Fehler AGS-Jahr -----
		// Vermerk in Session Array bei Konflikt zwischen nicht vorhandener Geometrie (AGS) im gewählten Jahr
		if($AGS_Fehler)
		{
			
			$_SESSION['Dokument']['AGS_Fehler'] = 1;
			$_SESSION['Dokument']['AGS_Fehler_Elemente'] = $AGS_Fehler_Elemente;
		}
		else
		{
			$_SESSION['Dokument']['AGS_Fehler'] = 0;
			$_SESSION['Dokument']['AGS_Fehler_Elemente'] = "";
		}
		
	}
}


function SetArrayDefaults()
{
	// Datenbestand/Definitionen erfassen
	if(!$_SESSION['Datenbestand'] or $_POST['RESET'] or $GLOBALS['Raumebene_geaendert']=="ja" or $_SESSION['Status'] != 'aktiv')
	{
		
		
		include("includes_classes/verbindung_mysqli.php");
		$_SESSION['Status'] = 'aktiv';
		
	
		// Variable für aktualisierung leeren	
		$_SESSION['Datenbestand']=array();
		
		// Datenbestand wird in Array gefüllt
		$SQL_DS = "SELECT * FROM v_geometrie,v_raumebene WHERE v_geometrie.RAUMEBENE = v_raumebene.RAUMEBENE AND (v_raumebene.RAUMEBENE = 'PLATZHALTER' ".$GLOBALS['RE_Select']." ) ORDER BY v_geometrie.NAME";
		$Ergebnis_DS = mysqli_query($Verbindung,$SQL_DS);
		
		// vorher...wie gedacht??? : 
		// $SQL_DS = "SELECT * FROM v_geometrie,v_raumebene WHERE v_geometrie.RAUMEBENE = v_raumebene.RAUMEBENE AND (RAUMEBENE = 'xxxxx_kein_HG_mehr' ".$GLOBALS['RE_Select']." ) ORDER BY Sortierung,ID_GEOMETRIE";
		
		
		// Zufällige Auswahl einer Raumeinheit für die Anzeige
		// Erfassen der Anzahl der Raumeinheiten
		$i_rev=0;
		while(@mysqli_result($Ergebnis_DS,$i_rev,'ID_GEOMETRIE'))
		{
			$vorh_RE = $i_rev;
			$i_rev++;
		}
		// Zufallszahl daraus ermitteln und Zufalls_Raumeinheit erfassen
		mt_srand(time());
		$REzufall = mt_rand(0,$vorh_RE);		
		
		$i_ds = 0;
		while($DS_Name = @mysqli_result($Ergebnis_DS,$i_ds,'ID_GEOMETRIE'))
		{
			// alte, starre Regelung per DB-Eintrag: 
			// $_SESSION['Datenbestand'][$DS_Name]['View'] = @mysqli_result($Ergebnis_DS,$i_ds,'View'); // Anzeige 0/1 für evtl. Voreinstellungen aus DB (aber keine Verwendung in Sicht)
			// Neue Zufallsgesteuerte Variante der Vorselektion und diese nicht auf Kreisebene (fehlerträchtig!? und sinnfrei):
			if($i_ds == $REzufall and ($_SESSION['Dokument']['Raumebene']['Bundesland']['View'] == '1' or $_SESSION['Dokument']['Raumebene']['Deutschland']['View'] == '1'))
			{
				$_SESSION['Datenbestand'][$DS_Name]['View'] = '1';
			}
			else
			{
				$_SESSION['Datenbestand'][$DS_Name]['View'] = '0';
			}
			
			if($_SESSION['Datenbestand'][$DS_Name]['View'] == '1') $_SESSION['Dokument']['Viewer_Datenset_ok'] = '1';
			$_SESSION['Datenbestand'][$DS_Name]['NAME'] = @mysqli_result($Ergebnis_DS,$i_ds,'ID_GEOMETRIE'); 
			// Check auf vorhandenes UTF8
			if(is_utf8(@mysqli_result($Ergebnis_DS,$i_ds,'Name_UTF8')))
			{
				$_SESSION['Datenbestand'][$DS_Name]['NAME_UTF8'] = @mysqli_result($Ergebnis_DS,$i_ds,'Name_UTF8');
				$_SESSION['Datenbestand'][$DS_Name]['NAME_HTML'] = @mysqli_result($Ergebnis_DS,$i_ds,'Name_UTF8'); // Name_HTML wird in DB nicht mehr gefüllt
			}
			else
			{
				$_SESSION['Datenbestand'][$DS_Name]['NAME_UTF8'] = utf8_encode(@mysqli_result($Ergebnis_DS,$i_ds,'Name_UTF8'));
				$_SESSION['Datenbestand'][$DS_Name]['NAME_HTML'] =  utf8_encode(@mysqli_result($Ergebnis_DS,$i_ds,'Name_UTF8')); // Name_HTML wird in DB nicht mehr gefüllt
			}
			$_SESSION['Datenbestand'][$DS_Name]['Jahr_min'] = @mysqli_result($Ergebnis_DS,$i_ds,'Jahr_min'); 
			$_SESSION['Datenbestand'][$DS_Name]['Jahr_max'] = @mysqli_result($Ergebnis_DS,$i_ds,'Jahr_max'); 
			$_SESSION['Datenbestand'][$DS_Name]['Auswahlkriterium'] = @mysqli_result($Ergebnis_DS,$i_ds,'SQL_Auswahlkriterium');
			$_SESSION['Datenbestand'][$DS_Name]['Auswahloperator'] = @mysqli_result($Ergebnis_DS,$i_ds,'SQL_Auswahloperator');
			$_SESSION['Datenbestand'][$DS_Name]['AuswahloperatorZusatz_1'] = @mysqli_result($Ergebnis_DS,$i_ds,'SQL_Auswahlop_Zusatz_1');
			$_SESSION['Datenbestand'][$DS_Name]['AuswahloperatorZusatz_2'] = @mysqli_result($Ergebnis_DS,$i_ds,'SQL_Auswahlop_Zusatz_2');
			$_SESSION['Datenbestand'][$DS_Name]['Transparenz'] = @mysqli_result($Ergebnis_DS,$i_ds,'Transparenz');
			$_SESSION['Datenbestand'][$DS_Name]['DB_Tabelle_Teilstring'] = @mysqli_result($Ergebnis_DS,$i_ds,'Postgres_Tabelle');
			$_SESSION['Datenbestand'][$DS_Name]['DB_Tabelle'] 
								= $_SESSION['Datenbestand'][$DS_Name]['DB_Tabelle_Teilstring']
								."_".$_SESSION['Dokument']['Raumgliederung']
								."_".$_SESSION['Dokument']['Jahr_Geometrietabelle']
								."_".$_SESSION['Dokument']['Raumgliederung_Zusatzkennung'];
			





			// Unterarray für Auswahlkriterien aus DB füllen (wenn z.B. mehrere Bundesländer gewählt werden o.Ä.
			$SQL_DS_Kriterien = "SELECT * FROM v_geometrie_kriterium_werte WHERE ID_GEOMETRIE = '".$DS_Name."' ";
		  $Ergebnis_DS_Kriterien = mysqli_query($Verbindung,$SQL_DS_Kriterien);
			$i_dsk = 0;

//DEBUG $_SESSION['Datenbestand12'] = @mysqli_result($Ergebnis_DS_Kriterien,$i_dsk,'id_werte');

			while(@mysqli_result($Ergebnis_DS_Kriterien,$i_dsk,'id_werte')) //es ist kein array und es ist etwas da zb bei anzeige Thüringen id_werte=473, Wert=16 (16.Bundesland) stimmt
			{
				$_SESSION['Datenbestand'][$DS_Name]['Auswahlkriterium_Wert'][$i_dsk] = @mysqli_result($Ergebnis_DS_Kriterien,$i_dsk,'Wert'); //es ist kein array aber etwas da zb Wert=1
				$i_dsk++;
				

			}
			
			// Variable wieder leeren
			$GLOBALS['Raumebene_geaendert']=="";
				
			$i_ds++;			
		}	
	}	
}
	// Nur für externe User erfassen, nicht für IÖR-Netzwerk (IP muss von Proxy/Internet (=angegebene IP kommen)
	//if($_SERVER['REMOTE_ADDR'] == '192.9.200.7')



//Prüfen der IP um IÖR Nutzer nicht mitzuzählen bei Downloads
/*if (! isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
	$client_ip = $_SERVER['REMOTE_ADDR'];
	
}
else {
	$client_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	
}*/


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
$counterFile = './new_surface.txt' ;

// jQuery ajax request is sent here
if ( isset($_GET['increase']) )  //
{
	
	    if ( ( $counter = @file_get_contents($counterFile) ) === false ) die('Error : file counter does not exist') ;
	           if ( $user_kennung == 'extern'  ){ file_put_contents($counterFile,++$counter) ;}
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

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>IÖR Monitor</title>


<!-- JQUERY lib für JQuery Dialog via WFS-->
<script src="lib/jquery/external/jquery/jquery.js"></script>
<link href="lib/jquery/jquery-ui.min.css" rel="stylesheet"/>
<script src="lib/jquery/jquery-ui.js"></script>
<script src="lib/jquery/jquery-ui.min.js"></script>
<script src="lib/jquery/jquery.ui.touch-punch.min.js"></script>
<link href="lib/jquery/jquery-ui.theme.css" rel="stylesheet">

<link href="screen_viewer.css" rel="stylesheet" type="text/css" media="screen" />
<link href="print_viewer.css" rel="stylesheet" type="text/css" media="print" />


<!--Ladecursor einblenden-->
<script type="text/javascript" src="./javascript/LadeCursor.js"></script>

<!--Suche in Multiple Select Menü (Gebietseinheiten)-->
<script type="text/javascript" src="./javascript/SucheGebiete.js"></script>



<style type="text/css">

a:link {
	text-decoration: none;
	color: #333333;
}
a:visited {
	text-decoration: none;
	color: #333333;
}
a:hover {
	text-decoration: none;
	color: #333333;
}
a:active {
	text-decoration: none;
	color: #333333;
}

</style>

<!-- Funktion aus Dreamweaver: funktioniert ganz gut soweit -->
<script type="text/javascript">

function MM_jumpMenu(targ,selObj,restore){ //v3.0
	var er =selObj.options[selObj.selectedIndex].value;
	console.log(er);
  eval(targ+".location='"+selObj.options[selObj.selectedIndex].value+"'");
  if (restore) selObj.selectedIndex=0;
  console.log("menu geht");
}

// Vergroessern der SELECT Boxen im IE
function expandSELECT(sel) {
var bName;
bName = navigator.appName; 
if (bName == "Microsoft Internet Explorer") sel.style.width = '';
}
function contractSELECT(sel) {
var bName;
bName = navigator.appName; 
if (bName == "Microsoft Internet Explorer") sel.style.width = '200px';
}


</script>

     <script type="text/javascript">
     	//Counter dialog new surface
     	
         jQuery(document).on('click','a#beta-rst',function(){
             jQuery('div#counter-ausgabe').html('Loading...') ;
             var ajax = jQuery.ajax({
                 method : 'get',
                 url : './svg_html.php', // Link to this page
                 data : { 'increase' : '1' }
             }) ;
             ajax.done(function(data){
                 jQuery('div#counter-ausgabe').html(data) ;
                 
             }) ;
             ajax.fail(function(data){
                 //alert('ajax fail : url of ajax request is not reachable') ;
                      jQuery('div#counter-ausgabe').html(data) ;
                
             }) ;
         }) ;
         </script>
         <script type="text/javascript">
              	//Counter button new surface
     	
                 jQuery(document).on('click','a#beta',function(){
             jQuery('div#counter-ausgabe').html('Loading...') ;
             var ajax = jQuery.ajax({
                 method : 'get',
                 url : './svg_html.php', // Link to this page
                 data : { 'increase' : '1' }
             }) ;
             ajax.done(function(data){
                 jQuery('div#counter-ausgabe').html(data) ;
                 
             }) ;
             ajax.fail(function(data){
                 //alert('ajax fail : url of ajax request is not reachable') ;
                      jQuery('div#counter-ausgabe').html(data) ;
                
             }) ;
         }) ;
     </script>

<!-- Kleine Funktion zum Ein- / Ausblenden der Div-Boxen weiter unten im Menue -->
<script type="text/javascript">
                function Change_DIV(id) 
				{
                    if (document.getElementById) 
					{
                        if (document.getElementById(id).style.display == 'none') 
						{
                            document.getElementById(id).style.display = 'block';
                        }
                        else 
						{
                            document.getElementById(id).style.display = 'none';
                        }
                    }
                }
				function Close_DIV(id) 
				{
                    if (document.getElementById) 
					{
                        document.getElementById(id).style.display = 'none';
                    }
                }
                
                 //Dialog zum neuen Kartenviewer bei Erstaufruf   
                  function neu_dialog(){$(function() {
											  	$("#dialog-Beta").dialog({
										                title: 'Wichtige Information!',
												        resizable: false,
												        modal: true,
												        width:'50%',
												        height:'auto',
										              	position: {  my: "top left", at: "center top+100px", of: window },
										                open: function() {
										                    $('#menu').hide();
										                    $('.arrow').hide();
										                    $('.subMenu').hide();
										                    $( ".toggle_arrow").show();
										                    $( ".toggle_arrow" ).html('<i class="fa fa-chevron-down fa-2x"></i>');
										                    $(this).closest(".ui-dialog")
										                        .find(".ui-dialog-titlebar-close")
										                        .html("<span class='ui-button-icon-primary ui-icon ui-icon-closethick' style='margin:-8px;'></span>");
										                  //Dialog schließen, wenn Klick auf graue Fläche drumherum  
										                  jQuery('.ui-widget-overlay').on('click', function() {
																			jQuery('#dialog-Beta').dialog('close')});
										               
										                }
										                
						            			}).css("font-size", "14px");
														});
														
													}
												function close_dialog()
												{				
												    //Close jQuery UI dialog 

												    $(".ui-dialog").hide();
												    $(".ui-widget-overlay").hide();
												}
                
                
</script>

</head>
<script type="text/javascript"> var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www."); document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E")); </script> <script type="text/javascript"> try { var pageTracker = _gat._getTracker("UA-11879208-2"); pageTracker._trackPageview(); } catch(err) {}</script>
<body>


<!---dialog neuer Viewer--->
	<?php
	/*if($_SESSION['Besuch'] === null && $_SESSION['Dokument']['Sprache'] == 'DE'){
		$_SESSION['Besuch'] = true;
		echo '
		<script language="javascript" type="text/javascript">
		neu_dialog();
		
		</script>';

		

	}*/
	
	?>

	<!--Inhalt des kleinen Dialogfensters neue Oberfläche bei Kartenerstaufruf-->
	
			<!--	<?php if ( $user_kennung == 'ioer'  ){?>
			<div id="dialog-Beta" style="font-size:11pt; display:none;" title="Wichtige Information!">
				<br/>
				<strong> 				
					Ab Montag, den 19.03.2018 wird der IÖR-Monitor ausschließlich mit neuem Kartenviewer angeboten.<br/>
					IÖR-Nutzer finden den alten Viewer weiterhin unter http://ioer-monitor.de/karten/karten/ bzw. https://maps.ioer.de/detailviewer/raster/ 
			
				</strong>
	</br>	
		    <button class="button_standard_abschicken_a" title="Verbleibt im alten Kartenviewer" id="cancel-rst" style=" float:right; font-family:Verdana; margin-right:70px; font-size:12px; width:auto; padding:5px ; cursor:pointer; display: inline;" onclick="close_dialog();">
                 Schließen   
            </button>
			      <a class="button_standard_abschicken_a" href="http://monitor.ioer.de/?kat=<?php echo $_SESSION['Dokument']['Fuellung']['Kategorie'];?>&ind=<?php echo $_SESSION['Dokument']['Fuellung']['Indikator'];?>&time=<?php echo $_SESSION['Dokument']['Jahr_Anzeige'];?>"
			      	 target="_blank"  title="Öffnet neuen Kartenviewer in externem Fenster" id="beta-rst" style="text-decoration:none;float:right; margin-right:10px; display: inline;cursor:pointer; font-family:Verdana; width:auto; padding:5px 7px 5px 7px; font-size:12px; ">
                  Zum neuen Viewer  
            </a>
			      
				</div>
			<?php }
			else{?>
					<div id="dialog-Beta" style="font-size:11pt; display:none;" title="Wichtige Information!">
				<br/>
				<strong>				
						Ab Montag, den 19.03.2018 wird der IÖR-Monitor ausschließlich mit neuem Kartenviewer angeboten.<br/>
					
				</strong>
	</br>	
		    <button class="button_standard_abschicken_a" title="Verbleibt im alten Kartenviewer" id="cancel-rst" style=" float:right; font-family:Verdana; margin-right:70px; font-size:12px; width:auto; padding:5px ; cursor:pointer; display: inline;" onclick="close_dialog();">
                 Schließen   
            </button>
			      <a class="button_standard_abschicken_a" href="http://monitor.ioer.de/?kat=<?php echo $_SESSION['Dokument']['Fuellung']['Kategorie'];?>&ind=<?php echo $_SESSION['Dokument']['Fuellung']['Indikator'];?>&time=<?php echo $_SESSION['Dokument']['Jahr_Anzeige'];?>"
			      	 target="_blank"  title="Öffnet neuen Kartenviewer in externem Fenster" id="beta-rst" style="text-decoration:none;float:right; margin-right:10px; display: inline;cursor:pointer; font-family:Verdana; width:auto; padding:5px 7px 5px 7px; font-size:12px; ">
                  Zum neuen Viewer  
            </a>
			      
				</div>
			<?php	}	?>--->

<!---Dialog neuer Viewer Ende-->

	<!--Beginn DEBUG ausgabebereich
	<div style="margin:270px; z-index:-1000000000000000;">Test
	<?php
		echo'tersttt';
		if (is_array($_SESSION['Datenbestand12'])){echo 'es ist ein array';}else {echo 'es ist kein array';}
			echo "<br/>";
		if (!$_SESSION['Datenbestand12']){echo 'nix da';}else {echo 'etwas da';}
		echo "<pre>";
print_r($_SESSION['Datenbestand12']);
echo "</pre><br /><br /><br />";  
	echo'terst';
	
	
?>
</div>
-->
<!--ENDE debugausgabe bereich-->

<a name="top" id="top"></a>
<?php 
// Blendet komplettes Menü aus, wenn dies vom Nutzer aktiviert ist => größere Karte
if($_SESSION['Dokument']['Ebenen_Anzeige'] != 'hide')
{
	
?>




<div id="KMenue">
	
  <div style="height:7px; border-top:1px solid #999999;"></div>
	  <?php 
			// Ausgeben des Loginstatus bei ViewBerechtigung < 3
			if($_SESSION['Dokument']['ViewBerechtigung'] < 3) echo '<span style="color:#990000;">'.utf8_encode($_SESSION['Dokument']['ViewBerechtigung_Name']).'</span>';
	  ?>
      <div class="button_schliessen">
			
            <a href="?Aktion=Ebenen_Anzeige_hide"><img src="gfx/button_schliessen_link.png"  title="Steuerungselemente ausblenden und Kartenansicht vergr&ouml;&szlig;ern" /></a>        
      </div>
			
      <div style="text-align:left; margin-left:5px;">      
         <br />
		 <?php 
		 
		 /* 
		

?>
<strong>Zeitschnitt</strong> <br />
            <select  title="Auswahl des Zeitschnittes oder Zeitvergleiches" name="Jahr" id="Jahr" style="width:200px; border: solid 1px #666666; background-color:#FFFFFF;" onchange="MM_jumpMenu('self',this,0)" >
                <!--<option value="">Bitte w&auml;hlen!</option> -->
					<?php 
					
					// Sortierung nach v_geometrie_jahr_viewer_postgis.SORTIERUNG_VIEWER und Ausgabe nach Verfügbarkeit von Kategorien im Zeitraum
					$SQL_Jahre = "SELECT JAHR FROM m_them_kategorie_freigabe,v_geometrie_jahr_viewer_postgis 
									WHERE m_them_kategorie_freigabe.STATUS_KATEGORIE_FREIGABE >= '".$_SESSION['Dokument']['ViewBerechtigung']."' 
									AND m_them_kategorie_freigabe.JAHR = v_geometrie_jahr_viewer_postgis.Jahr_im_Viewer 
									GROUP BY JAHR 
									ORDER BY SORTIERUNG_VIEWER DESC"; 
					$Ergebnis_Jahre = mysql_query($SQL_Jahre,$Verbindung);

					$i_jhr = 0;
					while($Jahr = @mysql_result($Ergebnis_Jahre,$i_jhr,'JAHR'))
					{
						
						?>
                       	<option <?php 
						
							// Check ob der gewählte Indikator in Kombination mit dem Zeitschnitt unter der aktuellen Viewer-Berechtigung angezeigt werden darf
							// und einfärbung des Zeitschnittes, falls nicht ok
							$SQL_Indikator_Zeitschnitt_Berechtigung = "SELECT JAHR FROM m_indikator_freigabe
															WHERE STATUS_INDIKATOR_FREIGABE >= '".$_SESSION['Dokument']['ViewBerechtigung']."'
															AND ID_INDIKATOR = '".$_SESSION['Dokument']['Fuellung']['Indikator']."' 
															AND JAHR = '".$Jahr."'";
							$Ergebnis_Indikator_Zeitschnitt_Berechtigung = mysql_query($SQL_Indikator_Zeitschnitt_Berechtigung,$Verbindung);
							// Falls nicht erlaubt: Ausgabe eines SVG mit Hinweis:
							if(!$Zeitschnitt_vorh = @mysql_result($Ergebnis_Indikator_Zeitschnitt_Berechtigung,0,'JAHR')) echo ' style="color:#AAAAAA;" ';
							
						?> value="svg_html.php?Jahr=<?php echo @mysql_result($Ergebnis_Jahre,$i_jhr,'JAHR'); ?>" <?php 
						if($Jahr == $_SESSION['Dokument']['Jahr_Anzeige']){echo 'selected="selected"';} ?> > <?php echo @mysql_result($Ergebnis_Jahre,$i_jhr,'JAHR'); 
						?>
                       	</option>
						<?php
                        $i_jhr++;
					}
					?>
  			</select>
        <div style="height:5px;"></div>
        
 <?php   */
		 
		 
		// Änderung der Projektion, nur für Prüfer zur Testung
		if($_SESSION['Dokument']['ViewBerechtigung'] == "0")
		{
			?>
            
			Test: <a href="?projektion=gk" target="_self">GK</a> / <a href="?projektion=lambert" target="_self">Lambert</a><br />
        	<?php 
		}
		
   ?>   
        
      <strong>
		 <?php if(!$_SESSION['Dokument']['Fuellung']['Kategorie'])
		  { 
			?>
			<span style="color:#990000;"><?php echo $Sprache_Ausgabe[$_SESSION['Dokument']['Sprache']]['Kategorie'].":"; ?></span>
			<?php 
		  }
		  else
		  { 
			echo $Sprache_Ausgabe[$_SESSION['Dokument']['Sprache']]['Kategorie'].":"; 
		  }
		  ?></strong><?php    
        
        
        
        // Prüfer mit ID=0 darf alles sehen
			if($_SESSION['Dokument']['ViewBerechtigung'] != "0")
			{
				// enthaltene Kategorien im Zeitschnitt erfassen
				$SQL_Kategorien = "SELECT * FROM m_thematische_kategorien,m_them_kategorie_freigabe 
								WHERE m_thematische_kategorien.ID_THEMA_KAT = m_them_kategorie_freigabe.ID_THEMA_KAT 
								AND STATUS_KATEGORIE_FREIGABE >= '".$_SESSION['Dokument']['ViewBerechtigung']."' 
								AND JAHR = '".$_SESSION['Dokument']['Jahr_Anzeige']."' 
								ORDER BY SORTIERUNG_THEMA_KAT"; 
				
				// Kategorien außerhalb des gewählten Jahres erfassen
				$SQL_Kategorien_NJ = "SELECT * FROM m_thematische_kategorien,m_them_kategorie_freigabe 
								WHERE m_thematische_kategorien.ID_THEMA_KAT = m_them_kategorie_freigabe.ID_THEMA_KAT 
								AND STATUS_KATEGORIE_FREIGABE >= '".$_SESSION['Dokument']['ViewBerechtigung']."' 
								AND JAHR <> '".$_SESSION['Dokument']['Jahr_Anzeige']."' 
								ORDER BY SORTIERUNG_THEMA_KAT";				
								
			}
			else
			{
				// enthaltene Kategorien erfassen
				$SQL_Kategorien = "SELECT * FROM m_thematische_kategorien ORDER BY SORTIERUNG_THEMA_KAT"; 
			}
			
			
			?>
         <select title="<?php echo $Sprache_Ausgabe[$_SESSION['Dokument']['Sprache']]['Auswahl_Kategorie']; ?>" name="Kategorie" id="Kategorie" style="width:200px; border: solid 1px #666666; background-color:#FFFFCC;"
             onchange="MM_jumpMenu('self',this,0)" >
                <option value=""><?php echo $Sprache_Ausgabe[$_SESSION['Dokument']['Sprache']]['BitteWaehlen']; ?></option>
				<?php 
				
				// Kategorien des gewählten Zeitschnitts anzeigen
          $Ergebnis_Kategorien = mysqli_query($Verbindung,$SQL_Kategorien);
			    $i_k=0;
         while($Kat_NAME = @mysqli_result($Ergebnis_Kategorien,$i_k,'THEMA_KAT_NAME'))
         {$K_Markiert = mysqli_result($Ergebnis_Kategorien,$i_k,'K_MARKIERUNG');
                	// Lokalisierung
					if($_SESSION['Dokument']['Sprache'] and $_SESSION['Dokument']['Sprache'] != 'DE') 
					{
						$Kat_NAME_Ausgabe = @mysqli_result($Ergebnis_Kategorien,$i_k,'THEMA_KAT_NAME_'.$_SESSION['Dokument']['Sprache']);
					}
					else
					{
						$Kat_NAME_Ausgabe = $Kat_NAME;
					}
					?><option value="svg_html.php?Kategorie=<?php echo $KAT_value = utf8_encode(@mysqli_result($Ergebnis_Kategorien,$i_k,'ID_THEMA_KAT')) ?>" 
                   				style="<?php 
											// Kategorie markieren
									if($K_Markiert == '1') { echo ' font-weight:bold; '; } ?>" 
									<?php 
                   		if($KAT_value == $_SESSION['Dokument']['Fuellung']['Kategorie']){echo 'selected="selected"';$KAT_ist_selektiert = $_SESSION['Dokument']['Fuellung']['Kategorie'];} ?> > <?php echo utf8_encode($Kat_NAME_Ausgabe); 
					?></option><?php
					
					// Array zur Dopplungsprüfung mit Anzeige der rest Kateg.
					$Kat_NAME_vorh[$Kat_NAME] = $Kat_NAME;
					$i_k++;
				}

				
				// Nicht zum Jahr gehörende Kategorien auflisten
				$Ergebnis_Kategorien_NJ = mysqli_query($Verbindung,$SQL_Kategorien_NJ);
                $i_k=0;
         while($Kat_NAME = @mysqli_result($Ergebnis_Kategorien_NJ,$i_k,'THEMA_KAT_NAME'))
         {
         	      	$K_Markiert = mysqli_result($Ergebnis_Kategorien_NJ,$i_k,'K_MARKIERUNG');
                	if($Kat_NAME != $Kat_NAME_alt and !$Kat_NAME_vorh[$Kat_NAME])
					{
						
						// Lokalisierung
						if($_SESSION['Dokument']['Sprache'] and $_SESSION['Dokument']['Sprache'] != 'DE') 
						{
							$Kat_NAME_Ausgabe = @mysqli_result($Ergebnis_Kategorien_NJ,$i_k,'THEMA_KAT_NAME_'.$_SESSION['Dokument']['Sprache']);
						}
						else
						{
							$Kat_NAME_Ausgabe = $Kat_NAME;
						}
						?><option  style="<?php 
											// Indikator markieren
									if($K_Markiert == '1') { echo ' font-weight:bold; '; } ?> " 
                        <?php if($_SESSION['Dokument']['Jahr_Anzeige']) { ?> title="Kategorie für den vorgewählten Zeitschnitt nicht vorhanden, bei Indikatorauswahl wird automatisch auf den jüngsten vorhandenen umgeschaltet" <?php } ?>
                       
                       value="svg_html.php?Kategorie=<?php echo $KAT_value = utf8_encode(@mysqli_result($Ergebnis_Kategorien_NJ,$i_k,'ID_THEMA_KAT')); 
						?>"
						 <?php 
							if($KAT_value == $_SESSION['Dokument']['Fuellung']['Kategorie']){echo 'selected="selected"';} ?> > <?php 
							/* if($_SESSION['Dokument']['Jahr_Anzeige']) { ?>*<?php }  */ echo utf8_encode($Kat_NAME_Ausgabe); 
						?></option><?php 
					}
					$Kat_NAME_alt = $Kat_NAME;
					$i_k++;
				}
				?>
                
                
  			</select>
        
         <?php 
			if(!$_SESSION['Dokument']['Fuellung']['Kategorie'])
			 { 
				?>
	  			<script type="text/javascript">document.getElementById('Kategorie').style.backgroundColor="#ffdddd";</script>
		   		<?php 
			 }
             ?>
    </div>
    
          
    
	<div style="height:5px;"></div>
			<?php
			if($_SESSION['Dokument']['Fuellung']['Kategorie'])
			{ 
				  if(!$_SESSION['Dokument']['Fuellung']['Indikator']) 
				  { 
					?>
					<span style="color:#990000; margin-left:15px; font-weight:bold;"><?php echo $Sprache_Ausgabe[$_SESSION['Dokument']['Sprache']]['Indikator'].":";?></span>
					<?php 
				  }
				  else
				  { 
					echo '<span style="margin-left:15px; font-weight:bold;">'.$Sprache_Ausgabe[$_SESSION['Dokument']['Sprache']]['Indikator'].':</span>'; 
				  }
	
					// Nutzer sortiert nach Markierung und Kurzname
					if($_SESSION['Dokument']['ViewBerechtigung'] != "0")
					{		
						// Indikatoren nur für gew. Zeitschnitt
						$SQL_Indikatoren = "SELECT * FROM m_indikatoren,m_indikator_freigabe 
											WHERE ID_THEMA_KAT='".$_SESSION['Dokument']['Fuellung']['Kategorie']."' 
											AND m_indikatoren.ID_INDIKATOR = m_indikator_freigabe.ID_INDIKATOR 
											AND STATUS_INDIKATOR_FREIGABE >= '".$_SESSION['Dokument']['ViewBerechtigung']."' 
											AND JAHR = '".$_SESSION['Dokument']['Jahr_Anzeige']."' 
											ORDER BY SORTIERUNG ASC";
											
						// Indikatoren außerhalb des gew. Zeitschnitt INDIKATOR_NAME_KURZ 
						$SQL_Indikatoren_NJ = "SELECT * FROM m_indikatoren,m_indikator_freigabe 
											WHERE ID_THEMA_KAT='".$_SESSION['Dokument']['Fuellung']['Kategorie']."' 
											AND m_indikatoren.ID_INDIKATOR = m_indikator_freigabe.ID_INDIKATOR 
											AND STATUS_INDIKATOR_FREIGABE >= '".$_SESSION['Dokument']['ViewBerechtigung']."' 
											AND JAHR <> '".$_SESSION['Dokument']['Jahr_Anzeige']."' 
											ORDER BY SORTIERUNG ASC";
											
					 						
					}
					else// Prüfer sortiert nach ID
					{
						// Indikatoren nur für gew. Zeitschnitt
						$SQL_Indikatoren = "SELECT * FROM m_indikatoren,m_indikator_freigabe 
											WHERE ID_THEMA_KAT='".$_SESSION['Dokument']['Fuellung']['Kategorie']."' 
											AND m_indikatoren.ID_INDIKATOR = m_indikator_freigabe.ID_INDIKATOR 
											AND STATUS_INDIKATOR_FREIGABE >= '".$_SESSION['Dokument']['ViewBerechtigung']."' 
											AND JAHR = '".$_SESSION['Dokument']['Jahr_Anzeige']."' 
											ORDER BY m_indikatoren.SORTIERUNG ASC";
											
						// Indikatoren außerhalb des gew. Zeitschnitt
						$SQL_Indikatoren_NJ = "SELECT * FROM m_indikatoren,m_indikator_freigabe 
											WHERE ID_THEMA_KAT='".$_SESSION['Dokument']['Fuellung']['Kategorie']."' 
											AND m_indikatoren.ID_INDIKATOR = m_indikator_freigabe.ID_INDIKATOR 
											AND STATUS_INDIKATOR_FREIGABE >= '".$_SESSION['Dokument']['ViewBerechtigung']."' 
											AND JAHR <> '".$_SESSION['Dokument']['Jahr_Anzeige']."' 
											ORDER BY m_indikatoren.SORTIERUNG ASC";
					} 
					
					
					
					?>
					<!-- Indikatorauswahl -->
					<div style="text-align:left; margin-left:15px;">
                    <select title="<?php echo $Sprache_Ausgabe[$_SESSION['Dokument']['Sprache']]['Auswahl_Indikator']; ?>" name="Indikator" id="Indikator" style="width:200px; background-color:<?php 
					 if(!$_SESSION['Dokument']['Fuellung']['Indikator']) { echo "#ffdddd"; }else{ echo "#ffffcc"; }
					
					?>;  border:solid 1px #666666;" onchange="MM_jumpMenu('self',this,0)" 
                      onfocus="expandSELECT(this);" 
                      onblur="contractSELECT(this);" > 
                        <option value=""><?php echo $Sprache_Ausgabe[$_SESSION['Dokument']['Sprache']]['BitteWaehlen']; ?></option>
                        <?php 
							
							// Ind im gew. Zeitschnitt
							$Ergebnis_Indikatoren = mysqli_query($Verbindung,$SQL_Indikatoren);
							
							$i_ind=0;
							while($ID_IND_Liste = @mysqli_result($Ergebnis_Indikatoren,$i_ind,'ID_INDIKATOR'))
							{
							
								$Ind_NAME = @mysqli_result($Ergebnis_Indikatoren,$i_ind,'INDIKATOR_NAME_KURZ');
								$Markiert = @mysqli_result($Ergebnis_Indikatoren,$i_ind,'MARKIERUNG');

								?>
								<option 
                                style=" <?php 
									// farbliche grüne Hinterlegung für freigegebene IND für Prüfer, rot wenn nicht freigegeben
					 				if($_SESSION['Dokument']['ViewBerechtigung'] == "0" and @mysqli_result($Ergebnis_Indikatoren,$i_ind,'STATUS_INDIKATOR_FREIGABE') == '3')
									{
										echo 'background:#CCFFCC;';
									}
									elseif ($_SESSION['Dokument']['ViewBerechtigung'] == "0") 
									{
										echo 'background:#FFDDDD;';
									}
									// Indikator markieren
									if($Markiert == '1') { echo 'font-weight:bold;'; }
									
									
								?>" value="svg_html.php?Indikator=<?php echo $Ind_value = utf8_encode(@mysqli_result($Ergebnis_Indikatoren,$i_ind,'ID_INDIKATOR')) ?>"
								 <?php 
								if($Ind_value == $_SESSION['Dokument']['Fuellung']['Indikator'])
                                    {
                                        $IND_ist_selektiert = $_SESSION['Dokument']['Fuellung']['Indikator'];
                                        $_SESSION["RAUMGLIEDERUNG_CACHE"] = $_SESSION['Dokument']['Raumgliederung'];
                                        $_SESSION["RAUMGLIEDERUNG_CACHE_input"] = $_SESSION['Dokument']['Raumgliederung'];
                                        
                                        // vorgesehene Raumgliederungen erfassen
                                        $SQL_Raumgliederung_menue2 = "SELECT * FROM v_raumgliederung WHERE Raumebene = '".$_SESSION['Dokument']['Raumebene_NAME_Auswertung']."' ORDER BY Sortierung";
                                        $Ergebnis_Raumgliederung_menue2 = mysqli_query($Verbindung,$SQL_Raumgliederung_menue2);
                                        $i_rg2 = 0;
                                        $isset = '0';
                                        $RG_check = '0';
                                        $count_test = 0;
                                        
                                        while(@mysqli_result($Ergebnis_Raumgliederung_menue2,$i_rg2,'ID_RAUMGLIEDERUNG'))
                                        {
                                            // Raster nur anbieten, wenn Umgebungsvariable $_SESSION['Dokument']['Raumgliederung_Raster_ok'] = 1 (in Adminoberfläche Raster angehakt) oder ein Prüfer eingelogt ist
                                            if((@mysqli_result($Ergebnis_Raumgliederung_menue2,$i_rg2,'DB_Kennung') != "rst" and @mysqli_result($Ergebnis_Raumgliederung_menue2,$i_rg2,'DB_Kennung') != "r05" and @mysqli_result($Ergebnis_Raumgliederung_menue2,$i_rg2,'DB_Kennung') != "r10") or $_SESSION['Dokument']['Raumgliederung_Raster_ok'] or $_SESSION['Dokument']['ViewBerechtigung'] == "0")
                                            {
                                                // Prüfung des Kennblattes, ob gewählte Raumgliederung für Indikator erlaubt ist
    
                                                $SQL_Raumgliederung_Visible2 = "SELECT * FROM m_indikatoren WHERE ID_INDIKATOR = '".$IND_ist_selektiert."'";
                                                $Ergebnis_Raumgliederung_Visible2 = mysqli_query($Verbindung,$SQL_Raumgliederung_Visible2);
                                                            
                                                $Kennung_Spalte_RG2 = 'RAUMEBENE_'.@mysqli_result($Ergebnis_Raumgliederung_menue2,$i_rg2,'KENNUNG_IND');
                                                if(@mysqli_result($Ergebnis_Raumgliederung_Visible2,0,$Kennung_Spalte_RG2) == '1' )//and $IND_ist_selektiert)
                                                    {
                                                    $first_RG = @mysqli_result($Ergebnis_Raumgliederung_menue2,$i_rg2,'DB_Kennung');
                                                    }
                                                
                                                if($_SESSION['Dokument']['Raumgliederung'] == @mysqli_result($Ergebnis_Raumgliederung_menue2,$i_rg2,'DB_Kennung') and @mysqli_result($Ergebnis_Raumgliederung_Visible2,0,$Kennung_Spalte_RG2) == '1') 
                                                { 
                                                $isset = '1';
                                                }
												
                                            $count_test++;     
                                            }
                                            $i_rg2++;
                                            
                                        }                                       
                                        if($isset == '0') {$_SESSION["RAUMGLIEDERUNG_CACHE"] = $first_RG;} 
										
                                    
				              
                                echo 'selected="selected"';     
                                    } 
             // Mouseover der einzelnen Indikatoren:  alle Zeilen der Kurzbeschreibung
									if($_SESSION['Dokument']['Sprache'] == 'DE') 
								{?> 
										title="<?php echo  utf8_encode(@mysqli_result($Ergebnis_Indikatoren,$i_ind,'INFO_VIEWER_ZEILE_1')),
										   utf8_encode(@mysqli_result($Ergebnis_Indikatoren,$i_ind,'INFO_VIEWER_ZEILE_2')),
									   utf8_encode(@mysqli_result($Ergebnis_Indikatoren,$i_ind,'INFO_VIEWER_ZEILE_3')),
										  utf8_encode(@mysqli_result($Ergebnis_Indikatoren,$i_ind,'INFO_VIEWER_ZEILE_4')),
									 utf8_encode(@mysqli_result($Ergebnis_Indikatoren,$i_ind,'INFO_VIEWER_ZEILE_5')),
									  utf8_encode(@mysqli_result($Ergebnis_Indikatoren,$i_ind,'INFO_VIEWER_ZEILE_6'));?>
									  "
									 <?php }
                    	if($_SESSION['Dokument']['Sprache'] == 'EN') 
								{?> 
										title="<?php echo  utf8_encode(@mysqli_result($Ergebnis_Indikatoren,$i_ind,'INFO_VIEWER_ZEILE_1_EN')),
										   utf8_encode(@mysqli_result($Ergebnis_Indikatoren,$i_ind,'INFO_VIEWER_ZEILE_2_EN')),
									   utf8_encode(@mysqli_result($Ergebnis_Indikatoren,$i_ind,'INFO_VIEWER_ZEILE_3_EN')),
										  utf8_encode(@mysqli_result($Ergebnis_Indikatoren,$i_ind,'INFO_VIEWER_ZEILE_4_EN')),
									 utf8_encode(@mysqli_result($Ergebnis_Indikatoren,$i_ind,'INFO_VIEWER_ZEILE_5_EN')),
									  utf8_encode(@mysqli_result($Ergebnis_Indikatoren,$i_ind,'INFO_VIEWER_ZEILE_6_EN'));?>
									  "	
									   <?php } ?>            
                                 
                 >                    
                                            
               <?php                        
								// ID für Prüfer anzeigen
								if($_SESSION['Dokument']['ViewBerechtigung'] == "0") {echo $Ind_value." ";}    //  echo "(".$Ind_value.") ";
								
								// Lokalisierung
								if($_SESSION['Dokument']['Sprache'] and $_SESSION['Dokument']['Sprache'] != 'DE') 
								{
									$Ind_NAME_Ausgabe = @mysqli_result($Ergebnis_Indikatoren,$i_ind,'INDIKATOR_NAME_'.$_SESSION['Dokument']['Sprache']);
								}
								else
								{
										//Prüfer sieht Langnamen, Nutzer sieht Kurznamen
										if($_SESSION['Dokument']['ViewBerechtigung'] != "0")
										{
												$Ind_NAME_Ausgabe = $Ind_NAME;
										}
										else
										{
											$Ind_NAME_Ausgabe = @mysqli_result($Ergebnis_Indikatoren,$i_ind,'INDIKATOR_NAME');
										}	
								
								
								}
								
								echo utf8_encode($Ind_NAME_Ausgabe); ?></option>
								<?php 
								// Array zur Dopplungsprüfung mit Anzeige der rest Kateg.
								$ID_IND_vorh[$ID_IND_Liste] = $ID_IND_Liste;
								
								$i_ind++;
							}
							
							
							
							// Ind außerhalb gew. Zeitschnitt 
							$Ergebnis_Indikatoren_NJ = mysqli_query($Verbindung,$SQL_Indikatoren_NJ);
							$i_ind=0;
							while($ID_IND_Liste = @mysqli_result($Ergebnis_Indikatoren_NJ,$i_ind,'ID_INDIKATOR'))
							{
								$Ind_NAME = @mysqli_result($Ergebnis_Indikatoren_NJ,$i_ind,'INDIKATOR_NAME_KURZ');
								$Markiert = @mysqli_result($Ergebnis_Indikatoren_NJ,$i_ind,'MARKIERUNG');
								if($ID_IND_Liste_alt != $ID_IND_Liste and !$ID_IND_vorh[$ID_IND_Liste])
								{
									?>
									<option 
                                    <?php if($_SESSION['Dokument']['Jahr_Anzeige']) { ?> title="Indikator für den vorgewählten Zeitschnitt nicht vorhanden, es wird automatisch auf den jüngsten vorhandenen umgeschaltet"<?php } ?> 
                                    style=" <?php 
									  // nur einfärben, wenn Jahr gewählt
									  if($_SESSION['Dokument']['Jahr_Anzeige']) 
									  {
										 // farbliche Hinterlegung für freigegebene IND für Prüfer
										 if($_SESSION['Dokument']['ViewBerechtigung'] == "0" and @mysqli_result($Ergebnis_Indikatoren_NJ,$i_ind,'STATUS_INDIKATOR_FREIGABE') == '3')
										 {
											echo 'background:#CCFFCC;';
										 }
										 else
										 {
											// echo 'color:#999;';	
										 }
										 // Indikator markieren
										 if($Markiert == '1') { echo 'font-weight:bold;'; }
									  }
										
									?>" value="svg_html.php?Indikator=<?php echo $Ind_value = utf8_encode(@mysqli_result($Ergebnis_Indikatoren_NJ,$i_ind,'ID_INDIKATOR')) ?>" <?php 
									if($Ind_value == $_SESSION['Dokument']['Fuellung']['Indikator']){echo 'selected="selected"';} ?> > <?php 
									/* if($_SESSION['Dokument']['Jahr_Anzeige']) { ?>*<?php }   */
									// ID für Prüfer anzeigen
									if($_SESSION['Dokument']['ViewBerechtigung'] == "0") echo $Ind_value." ";
									// echo utf8_encode($Ind_NAME); 
									
									// Lokalisierung
									if($_SESSION['Dokument']['Sprache'] and $_SESSION['Dokument']['Sprache'] != 'DE') 
									{
										$Ind_NAME_Ausgabe = @mysqli_result($Ergebnis_Indikatoren_NJ,$i_ind,'INDIKATOR_NAME_'.$_SESSION['Dokument']['Sprache']);
									}
									else
									{
										//Prüfer sieht Langnamen, Nutzer sieht Kurznamen
										if($_SESSION['Dokument']['ViewBerechtigung'] != "0")
										{
												$Ind_NAME_Ausgabe = $Ind_NAME;
										}
										else
										{
											$Ind_NAME_Ausgabe = @mysqli_result($Ergebnis_Indikatoren,$i_ind,'INDIKATOR_NAME');
										}	
									}
									
									echo utf8_encode($Ind_NAME_Ausgabe);
									?></option>
									<?php 
								}
								$ID_IND_Liste_alt = $ID_IND_Liste;
								$i_ind++;
							}
						
						?>
					</select>
                    
<script> 
//prüft in js-umgebung, ob sich die gliederung geändert hat. falls ja (='1'), wird später erneutes submit rausgefeuert!
var gliederung_fired = <?php if($_SESSION["RAUMGLIEDERUNG_CACHE"] != $_SESSION["RAUMGLIEDERUNG_CACHE_input"]){echo '1';} else{ echo '0';}?>;
</script> 
			<?php 
            
                
    /* 
                    if(!$_SESSION['Dokument']['Fuellung']['Indikator'])
                    { 
                        ?>
                          <script type="text/javascript">document.getElementById('Indikator').style.backgroundColor="#ffdddd";</script>
                        <?php 
                     }
                     else
                     {
                        ?>
                            <a href="index.php?id=44" target="_blank">
                                <div class="button_standard_abschicken_a" style="width:160px; text-align:center; background-color:#BDDDFD;">Indikatorkennblatt</div>
                            </a>
                        <?php 
                     } */
                    ?>
      </div> 
                <div style="height:5px;"></div>                
                               
                <?php 
			} 
			
        	?>	

  <div style=" padding-left:5px; padding-bottom:5px; padding-top:5px ;border-top:1px solid #999999;">            
   <strong><?php echo $Sprache_Ausgabe[$_SESSION['Dokument']['Sprache']]['Zeitschnitt'].":";?></strong> <br />
            <select  title="<?php echo $Sprache_Ausgabe[$_SESSION['Dokument']['Sprache']]['Auswahl_Zeitschnitt']; ?>" name="Jahr" id="Jahr" style="width:200px; border: solid 1px #666666; background-color:#FFFFFF;" onchange="MM_jumpMenu('self',this,0)" >
                <option value=""><?php echo $Sprache_Ausgabe[$_SESSION['Dokument']['Sprache']]['BitteWaehlen']; ?></option>
					<?php 
					
					// Sortierung nach v_geometrie_jahr_viewer_postgis.SORTIERUNG_VIEWER und Ausgabe nach Verfügbarkeit von Kategorien im Zeitraum
					$SQL_Jahre = "SELECT JAHR FROM m_them_kategorie_freigabe,v_geometrie_jahr_viewer_postgis 
									WHERE m_them_kategorie_freigabe.STATUS_KATEGORIE_FREIGABE >= '".$_SESSION['Dokument']['ViewBerechtigung']."' 
									AND m_them_kategorie_freigabe.JAHR = v_geometrie_jahr_viewer_postgis.Jahr_im_Viewer 
									AND v_geometrie_jahr_viewer_postgis.Jahr_im_Viewer < '2025'
									GROUP BY JAHR 
									ORDER BY SORTIERUNG_VIEWER DESC"; 
					$Ergebnis_Jahre = mysqli_query($Verbindung,$SQL_Jahre);

					$i_jhr = 0;
					while($Jahr = @mysqli_result($Ergebnis_Jahre,$i_jhr,'JAHR'))
					{
						
						?>
                       	<option <?php 
						
							// Check ob der gewählte Indikator in Kombination mit dem Zeitschnitt unter der aktuellen Viewer-Berechtigung angezeigt werden darf
							// und einfärbung des Zeitschnittes, falls nicht ok
							$SQL_Indikator_Zeitschnitt_Berechtigung = "SELECT JAHR FROM m_indikator_freigabe
															WHERE STATUS_INDIKATOR_FREIGABE >= '".$_SESSION['Dokument']['ViewBerechtigung']."'
															AND ID_INDIKATOR = '".$_SESSION['Dokument']['Fuellung']['Indikator']."' 
															AND JAHR = '".$Jahr."'";
							$Ergebnis_Indikator_Zeitschnitt_Berechtigung = mysqli_query($Verbindung,$SQL_Indikator_Zeitschnitt_Berechtigung);
							// Falls nicht erlaubt: Ausgabe eines SVG mit Hinweis:
							if(!$Zeitschnitt_vorh = @mysqli_result($Ergebnis_Indikator_Zeitschnitt_Berechtigung,0,'JAHR')) echo ' style="color:#AAAAAA;" title="für diesen Indikator nicht vorhanden" disabled';
							
						?> value="svg_html.php?Jahr=<?php echo @mysqli_result($Ergebnis_Jahre,$i_jhr,'JAHR'); ?>" <?php 
						if($Jahr == $_SESSION['Dokument']['Jahr_Anzeige']){echo 'selected="selected"';} ?> > <?php echo @mysqli_result($Ergebnis_Jahre,$i_jhr,'JAHR'); 
						?>
                       	</option>
						<?php
                        $i_jhr++;
					}
					?>
  			</select>
        <!--<div style="height:5px;"></div> -->
</div>   
    
    
    <div style="height:1px; border-bottom:1px solid #999999; margin-top:7px;"></div>
            <div style="background-color:#dddddd; padding-left:5px; padding-bottom:5px; padding-top:5px;">
              <div id="Raumebene_Text"><strong><?php echo $Sprache_Ausgabe[$_SESSION['Dokument']['Sprache']]['Ausdehnung'].":";?></strong></div>
            
              <select title="<?php echo $Sprache_Ausgabe[$_SESSION['Dokument']['Sprache']]['Auswahl_Raumebene']; ?>" id="RaumebeneGewaehlt" name="RaumebeneGewaehlt" style="width:200px; background-color:#FFFFFF; border:solid 1px #666666;"  onchange="MM_jumpMenu('self',this,0)" >
    			<option value=""><?php echo $Sprache_Ausgabe[$_SESSION['Dokument']['Sprache']]['BitteWaehlen']; ?></option>
               <?php 
				if(is_array($_SESSION['Dokument']['Raumebene']))
				{
					foreach($_SESSION['Dokument']['Raumebene'] as $REview)
					{
						// Aufzählung des als Hintergrund definierten Layers vermeiden... nur für dieses Menü wichtig => im Viewer extra behandelt 
						if($REview['NAME'] != 'Hintergrund')
						{
							?>
							<option value="svg_html.php?RaumebeneGewaehlt=<?php echo $REview['NAME']; ?>" <?php 
							if($REview['View']=='1')
							{
								?>selected="selected"<?php 
								$RE_ist_selektiert = $REview['NAME']; // Vermerken der Raumebene für Anzeige weiterer Menüpunkte
								$_SESSION['Dokument']['Raumebene_NAME_Auswertung'] = $RE_ist_selektiert;
							}
							?>><?php 
														
							// Lokalisierte Darstellung
							echo $Sprache_Ausgabe[$_SESSION['Dokument']['Sprache']][$REview['NAME_HTML']];
							
							
							?></option>
							<?php
						} 	
					}
				}
               ?>
            </select>
            <?php 
			if(!$RE_ist_selektiert)
   			{
				?>
				<script type="text/javascript">
                    document.getElementById('RaumebeneGewaehlt').style.backgroundColor="#ffdddd";
                    document.getElementById('Raumebene_Text').innerHTML='<span style="color:#990000;">Bitte Fl&auml;chenbezug w&auml;hlen!<span>';</script>
                </script>
                <?php 
			}
			?>           
	</div>
    
    
    
             <?php 
   // ---------------------------------------------------------------- 
   // ausgeblendet wenn noch keine Raumebene gewählt ( erhöht Übersichtlichkeit beim Start des Viewers ) , auch für räum. Ausdehn.=Gemeinde ausgeblendet
   if($RE_ist_selektiert && $_SESSION['Dokument']['Raumebene_NAME_Auswertung'] != 'Gemeinde')
   {
	   ?>
	   
	   <form action="svg_html.php" method="post" name="form" target="_self" id="form" style="background-color:#dddddd; margin:0px;" >
	   <?php 
	   	   
	    // select menü der Raumeinheiten nur anzeigen wenn nicht ganz Deutschland gewählt
	    	      
	 	   ?> 
	   
	   
	   
        <div style=" <?php if($RE_ist_selektiert == "Deutschland") echo 'display:none;'; ?> border-bottom:solid 1px #666666; padding-bottom:5px;" >  
			<div style="height:5px;"></div>
				<div id="Regionen_Text"  style="margin-left:15px;"><?php echo $Sprache_Ausgabe[$_SESSION['Dokument']['Sprache']]['Angezeigte Gebiete'].":";?></div>
			  
			 <!--Suchfeld zum durchforsten des folgenden multiple select. Löst durch Tastatureingabe Funktionen aus "SucheGebiete.js" aus und zeigt Filterergebnisse dynamisch an -->		
			 <input type="text" title="<?php echo $Sprache_Ausgabe[$_SESSION['Dokument']['Sprache']]['Suche_Gebiete']; ?>" name="SearchBox" id="SearchBox" style=" border:solid 1px #666666; width:183px;" placeholder="Suchen" />
			
			
		 <!--multiple select für Raumeinheiten (Bdl und Kreise)-->  
					<select 
            	title="<?php echo $Sprache_Ausgabe[$_SESSION['Dokument']['Sprache']]['Auswahl_Gebiete']; ?>" 
            	id="DatensaetzeGewaehlt" 
            	name="DatensaetzeGewaehlt[]" 
            	required="required"
            	size="6" 
			  	style="width:200px; background-color:#FFFFFF; border:solid 1px #666666;  margin-left:15px; margin-top:4px;" 
              	multiple 
                <?php if(!$_SESSION['Dokument']['Mehrfachauswahl']) echo ' onclick="submit()" '; ?>
				  <?php 
					// Bestimmen der Anzahl gleichzeitig ausgewählter Gebietseinheiten
					// Für Prüfer abschalten und auch nur, wenn Schalter $RE_Begrenzung gesetzt ist oder $_SESSION['Dokument']['Raumgliederung'] == "rst"
					if(($GLOBALS['RE_Begrenzung'] or $_SESSION['Dokument']['Raumgliederung'] == "rst") and $_SESSION['Dokument']['ViewBerechtigung'] != "0" and $_SESSION['Dokument']['Mehrfachauswahl'])
					{
						// Anzeige für Einfachauswahl
					?>
						onchange="return Auswahl_Anzahl()"
						<?php 
					 }
					 else
					 {
						// Anzeige bei Mehrfachauswahl
						/*  ????? Sinn? */
						// echo ' onchange="submit();"';
						 
						  
					 }
				 ?>
				>
					<?php
					/* // Testen ob überhaupt ein Element gewählt ist, ansonsten müsste eines automatisch ausgewählt werden (verringert Verwirrung des Nutzers)
					foreach($_SESSION['Datenbestand'] as $DatenSet)
					{
							if($DatenSet['NAME'] and ($DatenSet['View']=='1' or $DatenSet['View']=='0'))
							{ 
								if($DatenSet['View']=='1'){$Elem_Selektiert = 1; }
							} 
					} */
					// Anzeige der Elemente
					if(is_array($_SESSION['Datenbestand']))
					{
						foreach($_SESSION['Datenbestand'] as $DatenSet)
						{
							// Nutzung von $_SESSION['Dokument']['Jahr_Geometrietabelle'] da diese Variable den Bezug zur wirklichen Geometrie-Jahr-Beziehung herstellt
							if($DatenSet['NAME'] 
								and $DatenSet['Jahr_min'] <= $_SESSION['Dokument']['Jahr_Geometrietabelle'] 
								and $DatenSet['Jahr_max'] >= $_SESSION['Dokument']['Jahr_Geometrietabelle'] 
								and ($DatenSet['View']=='1' or $DatenSet['View']=='0')) // Hintergrund-Layer ($DatenSet['View']=='HG')ausblenden, wird sowieso nicht markiert
							{ 
								$Namen_Array[$DatenSet['NAME_UTF8'].'_'.$DatenSet['NAME']]['NAME'] = $DatenSet['NAME'];
								$Namen_Array[$DatenSet['NAME_UTF8'].'_'.$DatenSet['NAME']]['NAME_UTF8'] = $DatenSet['NAME_UTF8'];
								$Namen_Array[$DatenSet['NAME_UTF8'].'_'.$DatenSet['NAME']]['View'] = $DatenSet['View'];
								
								$sortierung[] = $DatenSet['NAME_UTF8'];
							} 
							
						}
						if(is_array($Namen_Array))
						{
							ksort($Namen_Array,SORT_LOCALE_STRING);
						}
					}
			
					
					foreach($Namen_Array as $DatenSet)
					{
						?>
							<option value="<?php echo $DatenSet['NAME']; ?>" <?php
							 if($DatenSet['View']=='1'){ ?> selected="selected" <?php } 
							 /* // bei NULL selektierten Elementen das Erste automatisch selektieren
							 if($Elem_Selektiert != 1) { ?>selected="selected"<?php $Elem_Selektiert = 1; } // Schalter wieder zurücksetzen für folgende Elemente */
							 ?> >
								<?php echo $DatenSet['NAME_UTF8']; ?>
							</option>
						<?php
						 
					}
					?>
				</select>
				<br />
					<span style="font-size:9px; margin-left:15px;"><?php echo $Sprache_Ausgabe[$_SESSION['Dokument']['Sprache']]['Mehrfachauswahl Taste'];?><br /></span>
			
			
		<script type="text/javascript">
								function Auswahl_Anzahl(form)
				{
					var ctr = 0;
					var mctr = <?php echo $GLOBALS['RE_Begrenzung_Anz']; ?>; /* this is the maximum number of options you want selected */
					var selObj = document.getElementById('DatensaetzeGewaehlt');
					
					for (var i=0; i< selObj.options.length; i++)
					{
						if (selObj.options[i].selected==true)
						ctr++;
					}
					
					if (ctr>mctr)
					{
						alert("Sie haben mehr als 3 Raumeinheiten ausgewählt. \nZur Zeit ist die Auswahl noch auf max. 3 Raumeinheiten begrenzt.");
						return false;
					}
					
					return true;
				}
				</script>	
				<?php 
				if($_SESSION['Dokument']['Viewer_Datenset_ok'] != '1')
				{
					?>
					<script type="text/javascript">
							document.getElementById('DatensaetzeGewaehlt').style.backgroundColor="#ffdddd";
							document.getElementById('Regionen_Text').innerHTML='<span style="color:#990000;">Bitte Region(en) w&auml;hlen!<span>';</script>
					</script>
					<?php
				}			
					
		?>
	    	</div>
            
			  <div style="height:3px;"></div>
            <input name="Aktion" type="hidden" value="Datensatz" />

            <span style="margin-left:15px;"><strong><?php echo $Sprache_Ausgabe[$_SESSION['Dokument']['Sprache']]['Raumgliederung'].":"; ?></strong></span><br />
    
    	<?php  
			/* Variante für Freischaltung nach Raumteilung/Indikator
			?>
    
    
                <select onchange="submit();" title="R&auml;umliche Gliederung des gew&auml;hlten Gebietes f&uuml;r die Kartendarstellung festlegen" name="Raumgliederung" id="Raumgliederung" 
            style="margin-left:15px; width:200px; border:solid 1px #666666;">
               <option value="" style="border-bottom:#AAA solid 1px; background:#CCC;">Bitte w&auml;hlen!</option>
			   <?php  
			   // vorgesehene Raumgliederungen erfassen
				$SQL_Raumgliederung_menue = "SELECT * FROM v_raumgliederung WHERE Raumebene = '".$RE_ist_selektiert."' ORDER BY Sortierung";
				$Ergebnis_Raumgliederung_menue = mysql_query($SQL_Raumgliederung_menue,$Verbindung);
				$i_rg = 0;
				while(@mysql_result($Ergebnis_Raumgliederung_menue,$i_rg,'ID_Raumgliederung'))
				{
                	// Raster nur anbieten, wenn Umgebungsvariable $_SESSION['Dokument']['Raumgliederung_Raster_ok'] = 1 oder ein Prüfer eingelogt ist
					if(@mysql_result($Ergebnis_Raumgliederung_menue,$i_rg,'DB_Kennung') != "rst" or $_SESSION['Dokument']['Raumgliederung_Raster_ok'] or $_SESSION['Dokument']['ViewBerechtigung'] == "0")
					{

	
						?>
                        <option value="<?php 
						echo @mysql_result($Ergebnis_Raumgliederung_menue,$i_rg,'DB_Kennung'); 
						?>" 
						<?php
						
						
						// Unterdrücken der Anzeige, wenn Raumgliederung im Kennblatt nicht aktiviert wurde
						$SQL_IND_Akt = "SELECT * FROM m_indikatoren WHERE ID_INDIKATOR = '".$_SESSION['Dokument']['Fuellung']['Indikator']."'";
						$Ergebnis_IND_Akt = mysql_query($SQL_IND_Akt,$Verbindung);
						
						$Kennung_Spalte_RG = 'RAUMEBENE_'.@mysql_result($Ergebnis_Raumgliederung_menue,$i_rg,'KENNUNG_KENNBLATT');
						if(!@mysql_result($Ergebnis_IND_Akt,0,$Kennung_Spalte_RG) and $_SESSION['Dokument']['Fuellung']['Indikator'])
						{
							// Ausgrauen der Anzeige
							echo ' disabled="disabled" ';
							$RG_F = ' (nicht vorgesehen)';
							
							// Rot einfärben, als Hinweis, dass andere RG gewählt werden muss
							if($_SESSION['Dokument']['Raumgliederung'] == @mysql_result($Ergebnis_Raumgliederung_menue,$i_rg,'DB_Kennung')) 
							{
								$RG_ROT = "<script type=\"text/javascript\">document.getElementById('Raumgliederung').style.backgroundColor='#ffdddd';</script>";
								// RG leeren, damit keine falsche Anzeige erzeugt wird
								$_SESSION['Dokument']['Raumgliederung'] = '';
							}
						}
						
						if($_SESSION['Dokument']['Raumgliederung'] == @mysql_result($Ergebnis_Raumgliederung_menue,$i_rg,'DB_Kennung')) 
						{ 
							echo 'selected="selected"'; 
						}
						// Einfärbung für Prüfer falls nicht freigegeben (nur bei Unterpunkt: Raster)
						if($_SESSION['Dokument']['ViewBerechtigung'] == "0" and !$_SESSION['Dokument']['Raumgliederung_Raster_ok'] and @mysql_result($Ergebnis_Raumgliederung_menue,$i_rg,'DB_Kennung') == "rst")
						{
							echo ' style="background-color:#ffaaaa;" ';
						}
						
						?> 
												><?php echo utf8_encode(@mysql_result($Ergebnis_Raumgliederung_menue,$i_rg,'Raumgliederung_HTML')).$RG_F; ?></option><?php
						$RG_F = ''; // Var leeren
					}
					$i_rg++;
				}
				
				 // Einblendung von weiteren Möglichkeiten unter anderen Raumebenenauswahlen
				if($RE_ist_selektiert == "Deutschland")
				{
					?><option disabled="disabled">Gemeinden (ab Ausdehnung: Bundesland)</option><?php 
				}
				
				if($RE_ist_selektiert != "Kreis")
				{
					?><option disabled="disabled">Raster 1km (ab Ausdehnung: Kreis)</option><?php 
				}
				?>
            </select>
			
			<?php
			// Rot einfärben des Schalters für Raumgliederung, wenn die Angewählte für den Indikator nicht verfügbar ist
			echo $RG_ROT;
			
			 */ 
			?>
     <select onchange="submit();" title="<?php echo $Sprache_Ausgabe[$_SESSION['Dokument']['Sprache']]['Auswahl_Raumgliederung']; ?>" name="Raumgliederung" id="Raumgliederung" 
            style="margin-left:15px; width:200px; border:solid 1px #666666;">
               <?php  
			   // vorgesehene Raumgliederungen erfassen
				$SQL_Raumgliederung_menue = "SELECT * FROM v_raumgliederung WHERE Raumebene = '".$RE_ist_selektiert."' ORDER BY Sortierung";
				$Ergebnis_Raumgliederung_menue = mysqli_query($Verbindung,$SQL_Raumgliederung_menue);
				$i_rg = 0;
				
				while(@mysqli_result($Ergebnis_Raumgliederung_menue,$i_rg,'ID_RAUMGLIEDERUNG'))
				{
                	// Raster nur anbieten, wenn Umgebungsvariable $_SESSION['Dokument']['Raumgliederung_Raster_ok'] = 1 (in Adminoberfläche Raster angehakt) oder ein Prüfer eingelogt ist
					if((@mysqli_result($Ergebnis_Raumgliederung_menue,$i_rg,'DB_Kennung') != "rst" and @mysqli_result($Ergebnis_Raumgliederung_menue,$i_rg,'DB_Kennung') != "r05" and @mysqli_result($Ergebnis_Raumgliederung_menue,$i_rg,'DB_Kennung') != "r10") or $_SESSION['Dokument']['Raumgliederung_Raster_ok'] or $_SESSION['Dokument']['ViewBerechtigung'] == "0")
					{
						// Prüfung des Kennblattes, ob gewählte Raumgliederung für Indikator erlaubt ist
										

						?><option value="<?php 
						echo @mysqli_result($Ergebnis_Raumgliederung_menue,$i_rg,'DB_Kennung'); 
						?>" <?php
						
						$SQL_Raumgliederung_Visible = "SELECT * FROM m_indikatoren WHERE ID_INDIKATOR = '".$IND_ist_selektiert."'";
						$Ergebnis_Raumgliederung_Visible = mysqli_query($Verbindung,$SQL_Raumgliederung_Visible);
									
						$Kennung_Spalte_RG = 'RAUMEBENE_'.@mysqli_result($Ergebnis_Raumgliederung_menue,$i_rg,'KENNUNG_IND');
						if(!@mysqli_result($Ergebnis_Raumgliederung_Visible,0,$Kennung_Spalte_RG) and $IND_ist_selektiert)
						{
						// Ausgrauen der Anzeige
						echo ' disabled="disabled" ';
						$RG_F = ' (nicht vorgesehen)';
									
						// Rot einfärben, als Hinweis, dass andere RG gewählt werden muss
						if($_SESSION['Dokument']['Raumgliederung'] == @mysqli_result($Ergebnis_Raumgliederung_menue,$i_rg,'DB_Kennung')) 
									{
									$RG_ROT = "<script type=\"text/javascript\">document.getElementById('Raumgliederung').style.backgroundColor='#ffdddd';</script>";
									// RG leeren, damit keine falsche Anzeige erzeugt wird
									$_SESSION['Dokument']['Raumgliederung'] = '';
										} 
						}
						
						if($_SESSION['Dokument']['Raumgliederung'] == @mysqli_result($Ergebnis_Raumgliederung_menue,$i_rg,'DB_Kennung')) 
						{ 
							echo 'selected="selected"'; 
						}
						
						/* Auskommentiert 20.02.2017
						// Einfärbung für Prüfer falls nicht freigegeben (nur bei Unterpunkt: Raster)
						if($_SESSION['Dokument']['ViewBerechtigung'] == "0" and !$_SESSION['Dokument']['Raumgliederung_Raster_ok'] and @mysqli_result($Ergebnis_Raumgliederung_menue,$i_rg,'DB_Kennung') == "rst")
						{
							echo ' style="background-color:#ffaaaa;" ';
						}
						*/
						
						
							/*Mouseover für Stadtteile neu 15.03.17 */
									if($_SESSION['Dokument']['Sprache'] == 'DE' && utf8_encode(@mysqli_result($Ergebnis_Raumgliederung_menue,$i_rg,'DB_Kennung'))=='stt') 
								{?> 
										title="Stadtgliederung auf der obersten teilstädtischen Ebene. Geometriegrundlage von 2014."
									 <?php }
									 
                    	if($_SESSION['Dokument']['Sprache'] == 'EN' && utf8_encode(@mysqli_result($Ergebnis_Raumgliederung_menue,$i_rg,'DB_Kennung'))=='stt')  
								{?> 
										title="Name may differ. Geometry as of 2014."
									 <?php }
						
						
						
						
						?> ><?php 
					
						
						// Lokalisierte Anzeige
						echo $Sprache_Ausgabe[$_SESSION['Dokument']['Sprache']][utf8_encode(@mysqli_result($Ergebnis_Raumgliederung_menue,$i_rg,'Raumgliederung_HTML'))];
					
						?></option><?php
						
					}
					$i_rg++;
					
				}
				
				 // Einblendung von weiteren Möglichkeiten unter anderen Raumebenenauswahlen
				/* if($RE_ist_selektiert != "Deutschland")
				{
					?><option disabled="disabled">Raumordnungsregionen (nur in Ausdehnung: Deutschland)</option><?php 
				} */
				
				if($RE_ist_selektiert == "Deutschland")
				{
					
					if($_SESSION['Dokument']['Sprache'] == 'DE') { ?><option disabled="disabled">Gemeinden (ab Ausdehnung: Bundesland)</option><?php }
					if($_SESSION['Dokument']['Sprache'] == 'EN') { /* ?><option disabled="disabled">Gemeinden (ab Ausdehnung: Bundesland)</option><?php  */}
									 
				}
				
				/* if($RE_ist_selektiert != "Kreis")
				{
					?><option disabled="disabled">Raster 1km (ab Ausdehnung: Kreis)</option><?php 
				}*/
				?> 
            </select>
			
			
<?php 
			// Wechsel der DB zurück auf monitor_svg
			mysqli_select_db($Verbindung, "monitor_svg"); 
			?>
            <br />
            <br />
   </form>
            
     <script>      
//if (gliederung_fired == '1') { document.Raumgliederung.submit('<?php  echo $_SESSION["RAUMGLIEDERUNG_CACHE"]; ?>');}
//if (gliederung_fired == '1') { document.Raumgliederung.submit('<?php  echo $_SESSION["RAUMGLIEDERUNG_CACHE"]; ?>',0);}
//if (gliederung_fired == '1') { MM_jumpMenu(document.Raumgliederung,'<?php  echo $_SESSION["RAUMGLIEDERUNG_CACHE"]; ?>',0);}
if (gliederung_fired == '1') {this.form.submit();}

</script>       
            
                <?php 
	  
	 // Elemente erst einblenden, wenn Indikator und Raumgliederung gewählt, da erst dann sinvoll
	 if($_SESSION['Dokument']['Raumgliederung']  and $_SESSION['Dokument']['Fuellung']['Indikator'])
	 {
	  ?>
      <div style="height:2px; border-top:1px solid #999999; margin-top:0px; "></div>
  	  <div style="text-align:left; margin-top:0px; margin-left:15px; padding-right:15px;">

            <div style="height:1px; clear:both;"></div>
            
          
                       
                <?php 
				// TabellenMenü nur für Deutsche Anzeige anzeigen
				if($_SESSION['Dokument']['Sprache'] == 'DE')
				{
				?>
				 <!------Button Tabelle -Indikatorwerte----->
						<a  href="tabellenansicht/tabelle_zur_karte_v3.php?neu=1" title="Indikatorwerte aller gezeigten Gebietseinheiten mit Sortier- und Exportoptionen" target="_blank"  >
							<div class="button_standard_abschicken_a"
								style="margin-left:0px; background-color: #ffffcc; border:#000 2px solid; text-align:center; 
								background-color: #ffffcc; width:187px; padding-bottom:3px; padding-top:3px; font-weight:bold;">
							  <?php echo $Sprache_Ausgabe[$_SESSION['Dokument']['Sprache']]['Tabelle']; ?>
		
						</div>
						</a> 
	<?php }
		   ?>
		   		<!---Button Indikatorkennblatt--->
            <a href="<?php echo $Sprache_Ausgabe[$_SESSION['Dokument']['Sprache']]['Kennblatt_link']; ?>" title="<?php echo $Sprache_Ausgabe[$_SESSION['Dokument']['Sprache']]['Kennblatt_Titel']; ?>" target="_blank" style="">
                <div class="button_standard_abschicken_a" style=" text-align:center; background-color: #ffffcc; width:187px; padding-bottom:2px; padding-top:2px;">
                	<?php echo $Sprache_Ausgabe[$_SESSION['Dokument']['Sprache']]['Kennblatt']; ?>
                </div>
            </a>
            
   		
        
        
        <div style="height:1px; clear:both;"></div>          
     
        </div>
        
        
        
       
     <!------------------Beginn blauer Buttonbereich-------------------------------->
        <?php 
			// Menü Kartengestaltung nur für Deutsche Anzeige anzeigen
			if($_SESSION['Dokument']['Sprache'] == 'DE')
			{
			?>
                
        <div style="height:2px; clear:both; border-top:1px solid #999999; margin-top:7px; margin-bottom:0px;"></div>
        <div style="text-align:left; margin-top:0px; margin-left:15px; padding-right:15px;">  
           <div style="height:1px; clear:both;"></div> 
          
            
 						<!---Beginn Button Kartengestaltung----> 
 						<a href="#" onclick="Change_DIV('einklapp'); Close_DIV('einklappgrau');" title="Auswahlmöglichkeiten für zusätzliche Kartenelemente, Farben und Werteklassifizierung" target="_self" >
                <div class="button_standard_abschicken_a" style="margin-top:5px; text-align:center; background-color: #BDDDFD; width:187px;  padding-bottom:2px; padding-top:2px;">
                	Kartengestaltung
                </div>
            </a>  
						<!---Ende Button Kartengestaltung----> 

       <!----Beginn Zuklappbereich--->     
      		<div id="einklapp" name="einklapp" style="font-size:12px; display:none; margin-bottom:8px; clear:both;">
       
              	 <!--------Beginn Bereich Klassifizierung---->  
	<?php  
					//für automatische Farbreihe  
		 if($_SESSION['Dokument']['Fuellung']['Typ'] == "Klassifizierte Farbreihe")
							{ ?><br />
	                <strong>Klassifizierung der Werte:</strong><br />
	                  	 
	                 <form action="svg_html.php" method="post">
	                        <input name="untertyp" type="radio" value="haeufigkeit" checked onclick="submit();" <?php 
	                         if((!$_SESSION['Dokument']['Fuellung']['Untertyp'] or $_SESSION['Dokument']['Fuellung']['Untertyp'] == "haeufigkeit") && $_SESSION['Dokument']['Fuellung']['Typ'] != "manuell Klassifizierte Farbreihe" && $_SESSION['Dokument']['Fuellung']['Typ'] != "Farbbereich") echo "checked"; ?> /> 
	            								Gleiche Klassenbesetzung<br />
	                        <input name="untertyp" type="radio" value="gleich"<?php 
	                         if($_SESSION['Dokument']['Fuellung']['Untertyp'] == "gleich") echo "checked"; ?> onclick="submit();"/> 
	                        		Gleiche Klassenbreite
		            <!-- <input name="senden" type="hidden" value="Anwenden" class="button_gruen_abschicken" style="cursor: pointer;" />
					          <input name="klass_aktualisieren" type="hidden" value="1" />  -->
	            			</form>
	            			  <div style="margin-top:3px;">
	            </div>
	            		    
	            	    <form action="svg_zeichenvorschrift_klass.php?kopieren=1" method="post" target="_self">
	         <input name="untertyp" type="radio" value="haeufigkeit" <?php if($_SESSION['Dokument']['Fuellung']['Typ'] == "manuell Klassifizierte Farbreihe" or $_SESSION['Dokument']['Fuellung']['Typ'] == "Farbbereich" and !$_SESSION['Dokument']['Fuellung']['ManUntertyp']) echo "checked"; ?>  onclick="submit();"/> 
	            								Erweiterte Klassifizierung</form>
	            					
	

	            								
	           								
	            <?php 
							}
							else
							{     //für manuelle Farbreihe	nun auch mit automatisch, aber Umweg über zeichenvorschrift_klass
	               ?>  <br />
	           			 <strong>Klassifizierung der Werte:</strong><br />
	           			 
	           			 
	           			 
	           			 
	                <!--  <form action="svg_zeichenvorschrift_klass.php" method="post">-->
	                <form action="svg_html.php" method="post">
	                        <input name="untertyp" type="radio" value="haeufigkeit"  onclick="submit();" <?php 
	                         if((!$_SESSION['Dokument']['Fuellung']['Untertyp'] or $_SESSION['Dokument']['Fuellung']['Untertyp'] == "haeufigkeit") && $_SESSION['Dokument']['Fuellung']['Typ'] != "manuell Klassifizierte Farbreihe" && $_SESSION['Dokument']['Fuellung']['Typ'] != "Farbbereich") echo "checked"; ?> /> 
	            								Gleiche Klassenbesetzung<br />
	                      <input name="untertyp" type="radio" value="gleich"<?php 
	                         if($_SESSION['Dokument']['Fuellung']['Untertyp'] == "gleich" && $_SESSION['Dokument']['Fuellung']['Typ'] != "Farbbereich") echo "checked"; ?> onclick="submit();"/> 
	                        		Gleiche Klassenbreite
		            <input name="senden" type="hidden" value="Anwenden" class="button_gruen_abschicken" style="cursor: pointer;" />
					          <input name="Typ" type="hidden" value="Klassifizierte Farbreihe" />
	            			</form>
	            			
	            			
	            		    <div style="margin-top:3px;">
	            </div>
	            	    <form action="svg_zeichenvorschrift_klass.php?kopieren=1" method="post" target="_self">
	                        <input name="untertyp" type="radio" value="haeufigkeit" <?php if($_SESSION['Dokument']['Fuellung']['Typ'] == "manuell Klassifizierte Farbreihe"  or $_SESSION['Dokument']['Fuellung']['Typ'] == "Farbbereich" and !$_SESSION['Dokument']['Fuellung']['ManUntertyp']) echo "checked"; ?>  onclick="submit();"/> 
	            								Erweiterte Klassifizierung</form>
	            		            	   										
	            								
	               <?php 
							}	?>
							
							
					
							
                            
   					<div style="clear:both; height:0px; margin-bottom:3px;"></div>
				<!--------Ende Bereich Klassifizierung---->
       
     
          
          
            
              	<hr style="border: none; border-top: 1px solid #CCCCCC; color: #CCCCCC; background-color: #CCCCCC; height: 1px;"/>         	
            		
          <!---Beginn Bereich Zusatz---->  		
          
      		<strong>Zusätzliche Kartenelemente anzeigen:</strong><br />
                
          <form action="svg_html.php" id="ZE" method="post">
                	<input name="bundesland" type="checkbox" value="1" title="Ein und Ausblenden der Bundeslandgrenzen" 
										<?php  if($_SESSION['Dokument']['zusatz_bundesland']) echo 'checked="checked"'; ?> /> Bundeslandgrenzen
                    <br />                                                         
                             
                    <input name="kreis" type="checkbox" value="1" title="Ein und Ausblenden der Kreisgrenzen"  
										<?php  if($_SESSION['Dokument']['zusatz_kreis']) echo 'checked="checked"'; ?> /> Kreisgrenzen
                    <br />
            
										<?php 
										// Einblendung der Gemeindegrenzen + Namen bei Rasterdarstellung
		                    if($_SESSION['Dokument']['Raumebene_NAME_Auswertung'] == "Kreis")
		                    {
					                		?>
															 <input name="gemeinde" type="checkbox" <?php  if($_SESSION['Dokument']['zusatz_gemeinde']) echo 'checked="checked"'; ?> /> Gemeindegrenzen & Namen
															 <br />
															 <?php 
												} ?>
                           
                     <input name="bab" type="checkbox" value="1" <?php  if($_SESSION['Dokument']['zusatz_bab']) echo 'checked="checked"'; ?> /> Autobahnnetz (Stand 2015)<br />
                     <input name="db" type="checkbox" value="1" <?php  if($_SESSION['Dokument']['zusatz_db']) echo 'checked="checked"'; ?> /> Fernbahnnetz (Stand 2016)<br />
                     
                    <input name="gew" id="gew" type="checkbox" value="1" <?php  
											if($_SESSION['Dokument']['zusatz_gew']) 
											{
												echo 'checked="checked"'; 
												// Korrektur der Gewässerzusatzebene: ändern der Gewässerauswahl auf Hauptfließgewässer, wenn Deutschland als Raumebene gewählt ist
												if($_SESSION['Dokument']['Raumebene_NAME_Auswertung'] == "Deutschland") $_SESSION['Dokument']['zusatz_gew_typ'] = 'klein';
											}
											?> 
		                    title="Ein und Ausblenden von Gew&auml;ssern"/> Gew&auml;sser<br /> 
		                    
		                    &nbsp;&nbsp;<input name="gew_typ" type="radio" value="klein" <?php if($_SESSION['Dokument']['zusatz_gew_typ'] == "klein" or !$_SESSION['Dokument']['zusatz_gew_typ']) echo 'checked="checked"'; ?> 
		                    title="Ein und Ausblenden von Haupt-Flie&szlig;gew&auml;ssern für &Uuml;bersichtsma&szlig;st&auml;be"/> Hauptfließgew&auml;sser<br />    
		                        
							
		                    &nbsp;&nbsp;<input name="gew_typ" type="radio" value="gross" <?php  
											if($_SESSION['Dokument']['zusatz_gew_typ'] == "gross") echo ' checked="checked" '; 
											// ausgrauen wenn Deutschland als Raumebene gewählt ist
											if($_SESSION['Dokument']['Raumebene_NAME_Auswertung'] == "Deutschland") echo " disabled ";
										?> 
					              title="Ein und Ausblenden von Flie&szlig;gew&auml;ssern für gr&ouml;&szlig;ere Ma&szlig;st&auml;be" /> Flie&szlig;gew&auml;sser<br />  
					              
					              
					          <input name="senden" type="submit" value="Anwenden" class="button_gruen_abschicken" style="cursor: pointer;" />
            <input name="zus_aktualisieren" type="hidden" value="1" />    					    
					     </form>         
					          
      
             <!---Ende Bereich Zusatz---->   
		              
		              		            
		              <hr style="border: none; border-top: 1px solid #CCCCCC; color: #CCCCCC; background-color: #CCCCCC; height: 1px;"/>
		              
		            <!------Entfernt 26.1.17, da Fehlerhaft -Beginn Bereich Normierung
		              
		           	<strong>Normierung auf Wertebasis:</strong><br />
				        <form action="svg_html.php" method="post">
				       
				        
				                 <input name="wertebasis" type="radio" value="reg" <?php 
				                 if($_SESSION['Dokument']['indikator_lokal'] == '1') echo "checked";   ?> /> Regionales Wertespektrum<br />
				                <input name="wertebasis" type="radio" value="deu" <?php if($_SESSION['Dokument']['indikator_lokal'] == '0') echo "checked";			                
				                ?> /> Deutschlandweites Wertesp.<br />
				                
				                
				             
							    <input name="senden" type="submit" value="Anwenden" class="button_gruen_abschicken" style="cursor: pointer;" />
			            <input name="norm_aktualisieren" type="hidden" value="1" />  
																	
								</form>
		            
		          Ende Bereich Normierung
		    
		         	<hr style="border: none; border-top: 1px solid #CCCCCC; color: #CCCCCC; background-color: #CCCCCC; height: 1px;"/> 	--->  
            	
            <!---Button Farbe-->
            
            
            	<a href="svg_zeichenvorschrift_farbe.php" title="Anpassung der Farbdefinitionen der Karte" target="_self" >
                <div class="button_standard_abschicken_a" style=" text-align:center; font-size:11px; background-color: #BDDDFD; width:160px; padding-bottom:2px; padding-top:2px;">
                	Farbanpassung
                </div>
            </a>
            <!---Ende Button Farbe-->	     
		           
		       
        	</div>
        <!----Ende Zuklappbereich--->
               
   			</div> 
      <?php 
	// Ende Ausblendung bei EN	
	}
	?>
	 <!------------------Ende blaue Buttons-------------------------------->   

	
	
    <div style="height:1px; clear:both;"></div>   
    <div style="height:6px; clear:both; border-bottom:1px solid #999999; margin-top:0px; margin-bottom:7px;"></div>
        <?php
  }
}

			
			?>
      
            

				<!---Beginn graue Buttons---->
			 <div style="margin-left:15px;">
					<?php  
		      // Erst einblenden, wenn Raumebene gewählt
		       if($Indikator = $_SESSION['Dokument']['Fuellung']['Indikator'] and $_SESSION['Dokument']['Raumgliederung'] and $_SESSION['Dokument']['Raumebene_NAME_Auswertung'] != 'Gemeinde')
		       {
		              ?>
		           <!---Beginn Button Karte einbinden / speichern----> 
 						<a href="#" onclick="Change_DIV('einklappgrau'); Close_DIV('einklapp'); " title="  <?php echo $Sprache_Ausgabe[$_SESSION['Dokument']['Sprache']]['Karte_einbinden_titel']; ?>" target="_self" >
                <div class="button_standard_abschicken_a" style="margin-top:0px; text-align:center; width:187px; padding-bottom:3px; padding-top:3px;">
                  <?php echo $Sprache_Ausgabe[$_SESSION['Dokument']['Sprache']]['Karte_einbinden']; ?>
                </div>
            </a>  
						<!---Ende Button Karte einbinden / speichern---->       
  
 				<!----Beginn Zuklappbereich--->     
 				 <div id="einklappgrau" name="einklappgrau" style="font-size:12px; display:none; margin-bottom:6px; margin-top:6px;clear:both;">
        <div style= margin-bottom:6px;"><?php echo $Sprache_Ausgabe[$_SESSION['Dokument']['Sprache']]['Einbinden_GIS']; ?> </div>
            
				              	
				  <!-- Beginn WFS PART -->
					<script>
						function wfs_dialog() {
					  	$("#dialog_wfs").dialog({
				              title: 'WFS',
							        resizable: false,
							        modal: true,
							        width:'75%',
					            dialogClass: 'ui-dialog-osx',
					            dialogClass: 'no-close success-dialog',
					            dialogClass: "#wfs_dialog",
					            position: 'top',
					            //Wenn Dialogfenster geöffnet, zeige seine Elemente               
					            open: function() {
					           
											     
					                 $( ".toggle_arrow").show();
					                 $( ".toggle_arrow" ).html('<i class="fa fa-chevron-down fa-2x"></i>');
					                 $(this).closest(".ui-dialog")
					                        .find(".ui-dialog-titlebar-close")
					                        .html("<span class='ui-button-icon ui-icon ui-icon-closethick'style='margin:-8px;'></span>");
					                  //zeige Link noch nicht an, wenn Checkbox nicht checked                                         
					        					if (!$("#nutzcheck_wfs").is(":checked")) {$("#zustimmung_wfs").hide();}	
					        					           //Dialog schließen, wenn Klick auf graue Fläche drumherum  
										                  jQuery('.ui-widget-overlay').on('click', function() {
																			jQuery('#dialog_wfs').dialog('close')});
					            },   
						  }).css("font-size", "12px");
							        
							//ein- und ausblenden der Nutzungsbedingungen bei Klick in Checkbox
							 $('#nutzcheck_wfs').click(function() {
										 $("#zustimmung_wfs").toggle(this.checked);
							 });
				   	}		
					</script>
										
					<input type="button" class="button_standard_abschicken_a" id="btn_wfs" name="btn_wfs" title="<?php echo $Sprache_Ausgabe[$_SESSION['Dokument']['Sprache']]['WFS_titel']; ?>" value="WFS" style="margin-top:4px; text-align:center;  width:187px; padding-bottom:2px; margin-bottom:5px; padding-top:2px;"  onclick='wfs_dialog()'/>

						        <div id="dialog_wfs" style="display:none;">
											<div id="AUSGABETEXT" name="AUSGABETEXT">	
												<div style="" onclick="wfs_dialog();"></div>
												<p><?php echo $Sprache_Ausgabe[$_SESSION['Dokument']['Sprache']]['WFS_Dialog_1']; ?> </p>
												<p id="nutzungsbed_wfs"><input type="checkbox" id="nutzcheck_wfs"><?php echo $Sprache_Ausgabe[$_SESSION['Dokument']['Sprache']]['WFS_Dialog_2']; ?> </p>
												<div id="zustimmung_wfs">
													<p><?php echo $Sprache_Ausgabe[$_SESSION['Dokument']['Sprache']]['WFS_Dialog_3']; ?> </p>									
													<p ><h3 id="wfs_link">https://maps.ioer.de/cgi-bin/wfs?MAP=<?php echo $IND_ist_selektiert ?></h3></p
													<p><?php echo $Sprache_Ausgabe[$_SESSION['Dokument']['Sprache']]['WFS_Dialog_4']; ?></p>							
													<p><?php echo $Sprache_Ausgabe[$_SESSION['Dokument']['Sprache']]['WFS_Dialog_5']; ?></p>
												</div>
											</div>	
										</div>
					<?php}?>	        
				<!---ENDE WFS PART-->
			
						<div style="height:2px; clear:both; border-top:1px solid #CCCCCC; margin-top:7px; margin-left:18px; margin-right:27px;"></div>   
						
			<div style="margin-bottom:0px;">  <?php echo $Sprache_Ausgabe[$_SESSION['Dokument']['Sprache']]['Export_Zeile']; ?></div>    
						
						  <!----Beginn Button Druck pdf--->
			         	<div>				          	     			          
	              	<form action="svg_svg.php" method="post" target="_blank">
	              
						         <select  style="display: none" name="Dateiausgabe_typ_datei">
												<option value="pdf" selected="selected">PDF</option>
										</select> 
						        <select style="display: none" name="Dateiausgabe_width">
										<option value="2000"  selected="selected">2000 px</option>
										 </select> 
										 
										 
								  <input name="erzeugen" type="submit" onclick="" value="<?php echo $Sprache_Ausgabe[$_SESSION['Dokument']['Sprache']]['PDFDatei erzeugen']; ?>" 
			            title="<?php echo $Sprache_Ausgabe[$_SESSION['Dokument']['Sprache']]['PDFDatei title']; ?>" class="button_standard_abschicken_a" 
			            style=" text-align:center; width:95px; float:left; padding-bottom:3px; padding-top:3px; cursor: pointer;" />
							 
							    <input name="Dateiausgabe" type="hidden" value="1" />
								      </form>
	            
	              </div> 
			          
			         <!----Ende Button Druck pdf---> 										
		  
			              
	         <!----Beginn Button Bilddatei--->
						  	<a href="#" onclick="Change_DIV('rasterize')" title="<?php echo $Sprache_Ausgabe[$_SESSION['Dokument']['Sprache']]['Export_alttext']; ?>" target="_self"  >
				          <div class="button_standard_abschicken_a" style="margin-right:16px; text-align:center; float:right; width:85px; padding-bottom:3px; padding-top:3px;">
				                		<?php echo $Sprache_Ausgabe[$_SESSION['Dokument']['Sprache']]['Export']; ?>
				          </div>
			          </a>
			                
			       <div id="rasterize" name="rasterize" style="font-size:12px; display:none; margin-bottom:8px; clear:both;">
			              	<form action="svg_svg.php" method="post" target="_blank">
				                <br />
								<div style="margin-bottom:3px;"><?php echo $Sprache_Ausgabe[$_SESSION['Dokument']['Sprache']]['Ausgabeeinstellungen']; ?>:</div>
								
				                <select name="Dateiausgabe_typ_datei">
								  <option value="png" <?php if($_SESSION['Dokument']['Dateiausgabe_typ_datei'] == 'png') { ?> selected="selected"<?php } ?> >PNG</option>
								  <option value="tif" <?php if($_SESSION['Dokument']['Dateiausgabe_typ_datei'] == 'tif') { ?> selected="selected"<?php } ?> >TIFF</option>
								  <option value="jpg" <?php if($_SESSION['Dokument']['Dateiausgabe_typ_datei'] == 'jpg') { ?> selected="selected"<?php } ?> >JPEG</option>
								  <!--<option value="pdf" <?php if($_SESSION['Dokument']['Dateiausgabe_typ_datei'] == 'pdf') { ?> selected="selected"<?php } ?> >PDF</option>-->
								</select> 
				                <select name="Dateiausgabe_width">
								  <option value="3000" <?php if($_SESSION['Dokument']['Dateiausgabe_width'] == '3000') { ?> selected="selected"<?php } ?> >3000 px</option>
								  <option value="2000" <?php if($_SESSION['Dokument']['Dateiausgabe_width'] == '2000' or !$_SESSION['Dokument']['Dateiausgabe_width']) { ?> selected="selected"<?php } ?> >2000 px</option>
								  <option value="1200" <?php if($_SESSION['Dokument']['Dateiausgabe_width'] == '1200') { ?> selected="selected"<?php } ?> >1200 px</option>
								</select> 
								  
				                <br />
								<input name="Erzeugen" type="submit" value="<?php echo $Sprache_Ausgabe[$_SESSION['Dokument']['Sprache']]['Datei erzeugen']; ?>" class="button_gruen_abschicken" style="margin-top:6px; cursor: pointer;" />
				                <input name="Dateiausgabe" type="hidden" value="1" /><br />
				                <br />
								<a href="http://xmlgraphics.apache.org/batik/tools/rasterizer.html" target="_blank"><?php echo $Sprache_Ausgabe[$_SESSION['Dokument']['Sprache']]['batik']; ?><br />batik SVG-Rasterizer</a>
				                
								<br />	
			                </form>
			       </div>        
			      <!----Ende Button Bilddatei--->   
			      
			      <div style="height:1px; clear:both;"></div> 
			 <div style="height:2px; clear:both; border-top:1px solid #CCCCCC; margin-top:8px;  margin-right:27px;"></div>        
			 <div style=" margin-top:3px; ">  <?php echo $Sprache_Ausgabe[$_SESSION['Dokument']['Sprache']]['Karte_speichern']; ?></div>     		
			<!----Beginn Button Kartenlink--->
						 <div style="height:2px; clear:both;"></div> 
			       <a href="svg_link_generieren.php" title="<?php echo $Sprache_Ausgabe[$_SESSION['Dokument']['Sprache']]['Kartenlink_Title']; ?>" target="_self">
			        	<div class="button_standard_abschicken_a" style=" margin-left:0px; width:187px; padding-top:2px; padding-bottom:2px;text-align:center;">	
			          <?php echo $Sprache_Ausgabe[$_SESSION['Dokument']['Sprache']]['Kartenlink']; ?>
			       		</div>
			       </a>
			   <!----Ende Button Kartenlink---> 
			 </div>
     <!----Ende Zuklappbereich---> 
     
				</div>
<!-- Ende Zusätzliche Funktionen graue Buttons oben -->
 <div style="height:2px; clear:both; border-top:1px solid #999999; margin-top:7px; margin-bottom:0px;"></div>        
              
            <?php 
            }
			else
			{
				?>
				<br />
				<br />
				<?php 
			}
			?>
			
			<!----Beginn Bereich Karte laden--->
      <form action="svg_html.php" method="post" style="padding:0px; margin:0px; margin-top:5px; margin-left:15px;">
				<?php 
				// Fehlermeldungen / Hinweise
                if($GLOBALS['ID_Info'])
                {
                    echo '<div style="height:1px; clear:both;"></div>'.$GLOBALS['ID_Info'].'<br />';	
                }
                ?>
               <input title="<?php echo $Sprache_Ausgabe[$_SESSION['Dokument']['Sprache']]['Auswahl_Laden']; ?>" name="SendenLaden" type="submit" class="button_standard_abschicken_a" 
               value="<?php echo $Sprache_Ausgabe[$_SESSION['Dokument']['Sprache']]['Karte']; ?>" style=" text-align:center; width:95px; padding-top:2px; padding-bottom:2px; cursor: pointer; 
               <?php 
			  				 if($GLOBALS['ID_Error']) { echo 'background:#EE3333;'; }
			  				?>" /> 
               
               <?php echo $Sprache_Ausgabe[$_SESSION['Dokument']['Sprache']]['Nr']; ?>.: <input name="idk" type="text" value="<?php echo $_POST['id']; ?>" style=" width:55px;">
            </form>
         <!----Ende Bereich Karte laden--->    
     
       <!---Ende untere Buttons---->
	
	
	<div style="height:0px; border-bottom:1px solid #999999; margin-top:5px; margin-bottom:7px;clear:both;"></div>
      	<br />
    	
      
    
		 


      
   
    <!---Beginn rote Buttons---->  
        <?php 
		/* ... leider Ausblenden nich sinnvoll, da nicht live wieder eingeblendet, wenn beschriftung gesetzt und Karte nicht neu geladen
		if(isset($_SESSION['Dokument']['LabelAnzeige']))
		{ */
			?>
			<form action="svg_html.php" method="post" target="_self">
				<input name="RESET_LABELS" type="hidden" value="RESET_LABELS">
				<div style="text-align:center; margin-top:5px;">
						  <input title="<?php echo $Sprache_Ausgabe[$_SESSION['Dokument']['Sprache']]['reset_Title']; ?>" name="Senden" type="submit" class="button_rot_abschicken" value="<?php echo $Sprache_Ausgabe[$_SESSION['Dokument']['Sprache']]['reset']; ?>" style=" width:187px; cursor: pointer;">
				</div>
			</form>
			
        <form action="svg_html.php" method="post" target="_self">
			<input name="RESET" type="hidden" value="RESET">
            <div style="text-align:center; margin-top:5px;">
                      <input title="<?php echo $Sprache_Ausgabe[$_SESSION['Dokument']['Sprache']]['loeschen_Title']; ?>" name="Senden" type="submit" class="button_rot_abschicken" value="<?php echo $Sprache_Ausgabe[$_SESSION['Dokument']['Sprache']]['loeschen']; ?>" style=" width:187px; cursor: pointer;">
        	</div>
        </form>
       	<!---Ende rote Buttons---->     
      
       <!---Beginn Prüferbereich---->       
  		<br />
		
        
      
        <?php 
		// Einblenden des Links für Prüfertools
		if($_SESSION['Dokument']['ViewBerechtigung'] == "0")
		{
			?>
			F&uuml;r Prüfberechtigte:<br />
			<br />
			<a href="admin/pruefung_u_rechte_uebersicht.php?Aufklapp=<?php echo $_SESSION['Dokument']['Fuellung']['Kategorie']; 
																		?>&Ind=<?php echo $_SESSION['Dokument']['Fuellung']['Indikator'];
																		?>&Jhr=<?php echo $_SESSION['Dokument']['Jahr_Anzeige']; 
																		?>#<?php echo $_SESSION['Dokument']['Fuellung']['Kategorie']; ?>" target="_blank">
			<span class="button_standard_abschicken_a" style=" padding-left:5px; padding-right:5px;" >Pr&uuml;fung und Freigabe</span>			</a>
            <br />
			<br />
			Hinweis:<br />
			Die hier evtl. angepasste Farbgebung und Klassifizierung kann ebenfalls bei der Prüfung/Freigabe mit gespeichert werden.<br />
			<br />
			<form action="svg_html.php" method="post" target="_self">
            	<input name="Rechte" type="hidden" value="LOGOUT" />
              	<input name="Passwort" type="hidden" svalue="" />
              	<input name="Senden" type="submit" class="button_blau_abschicken" value="Ausloggen">
            </form>
				
			<?php
		}
		else{
		?>
		
				<div style="height:0px; border-bottom:1px solid #999999; margin-top:10px; margin-bottom:5px;clear:both;"></div>
         			<a target="_blank" href="http://www.ioer-monitor.de/login">
			<span class="button_standard_abschicken_a" style=" padding-left:5px; padding-right:5px; margin-left:17px;" >Prüfer Login</span>			</a><br/>
				<br/>
         
          <br />
    <?php } ?>     
		<br /><br />
    
        <a class="button_gruen_abschicken" href="https://maps.ioer.de/detailviewer/raster/" 
        	  title="Alter Rasterviewer"  
        	id="old" target="_blank" style="font-weight: bold; margin:1px 14px 1px 14px; padding:14px 13px; float:center;">
                  Alter Rasterviewer
            </a>
    
             <br />
                  <br />
        	  <!-- 	<div style=" <?php if($_SESSION['Dokument']['ViewBerechtigung'] != "0")
												{	echo 'display:none;';   }?>" id="counter-ausgabe" >Besucher: <?php echo $counter; ?>
					</div>-->
        
<!---Ende Prüferbereich---->
	</div>
</div>
 <?php
     
      
      
      
          
   
  }  //Ende Menü, wenn es eingeblendet ist
            
            
            
   else
{//wenn Menü ausgeblendet ist:
	?>
    <div class="button_show">
			<a href="?Aktion=Ebenen_Anzeige_show"><img src="gfx/button_schliessen_link.png" style="width:16px; height:17px;" title="Steuerungselemente einblenden" /></a>    
    </div>
<?php 
}
?>         
            
     <?php 

// Karte nur bei Verfügbaren Daten als SVG ausgeben / ansonsten Grafik anzeigen (angenehmerer Bildaufbau und stimmiger als SVG (im IE))
if(!$_SESSION['Dokument']['Raumgliederung'] or !$_SESSION['Dokument']['Fuellung']['Indikator'])
{ 
	?>
    <div id="Karte_leer">
    	<?php if($_SESSION['Dokument']['Sprache'] == 'DE') { echo '<img src="gfx/karte_leer.png" style="margin-right:200px;" width="539" height="696" alt="Keine Daten ausgewählt" />'; } ?>
        <?php if($_SESSION['Dokument']['Sprache'] == 'EN') { echo '<img src="gfx/karte_leer_en.png" style="margin-right:200px;" width="539" height="696" alt="No data selected" />'; } ?>
    </div>
	<?php 
}
else
{
 SVG_Objekt();
}


function SVG_Objekt()
{
	// Preloader-GIF
	?>
    <div id="Preloader">
    	
        <?php if($_SESSION['Dokument']['Sprache'] == 'DE') { echo '<img src="icons_viewer/preloader/preloader.gif" width="760" height="500" alt="Karte wird erstellt" />'; } ?>
        <?php if($_SESSION['Dokument']['Sprache'] == 'EN') { echo '<img src="icons_viewer/preloader/preloader_en.gif" width="760" height="500" alt="Map is rendering" />'; } ?>
    </div>
    
    <div id="Karte">
        <object type="image/svg+xml"
                data="svg_svg.php"
                width="<?php echo $x = $_SESSION["Dokument"]["groesse_X"]+$_SESSION["Dokument"]["Rand_L"]+$_SESSION["Dokument"]["Rand_R"]+2; ?>"
                height="<?php echo $_SESSION["Dokument"]["groesse_Y"]+$_SESSION["Dokument"]["Rand_O"]+$_SESSION["Dokument"]["Rand_U"]+$_SESSION["Dokument"]["Hoehe_Legende_unten"]; ?>">
                  	<param name="src" value="svg_svg.php" />
                    <div style=" padding-top:50px; padding-right:200px; padding-left:350px; background-color:#FFFFFF; height:500px;">
                      <strong>Leider ist eine Kartenanzeige nicht möglich, <br />
                              da Ihr Browser aktuelle Web-Standards (SVG) nicht unterstützt!</strong>
                      <ul>
                        <li style="padding-top:5px;">Wenden Sie sich für die Fehlerbehebung bitte an Ihren Systemadministrator, dieser sollte Ihnen bei<br />
                              der Installation eines aktuellen Browsers (z.B. Firefox, Opera, InternetExplorer ab Version 9, Safari) behilflich sein.
                                <br />
                        </li>
                        <li style="padding-top:5px;">Steht Ihnen kein Administrator zur Verfügung, können Sie das Problem wie folgt lösen:<br />
                                <div style="padding-top:5px; font-weight:bold;">Ohne Administratorrechte:</div>
                              - Laden sie sich unter dem folgenden Link den Opera@usb-Browser herunter: <strong>
                              <a style="color:#00F;" target="_blank" href="http://www.opera-usb.com/operausb.htm">Opera@usb</a></strong><br />
                              - Entpacken Sie ihn (startet automatisch, wenn Sie beim Download auf &quot;Ausführen&quot; klicken)
                              in ein Verzeichnis Ihrer Wahl<br />
                              - Starten Sie den Browser direkt aus dem, von Ihnen ausgewählten, Verzeichnis heraus und rufen Sie den IÖR-Monitor darüber auf

                                <div style="padding-top:5px; font-weight:bold;">Mit Administratorrechten:</div>
                                Installieren Sie einen aktuellen Browser Ihrer Wahl  (z.B. <a style="color:#00F;" href="http://www.mozilla.org/de/firefox/new/" target="_blank">Firefox</a>, 
                                <a style="color:#00F;" href="http://de.opera.com/download/" target="_blank">Opera</a>, 
                                <a style="color:#00F;" href="http://windows.microsoft.com/de-DE/internet-explorer/downloads/ie" target="_blank">InternetExplorer ab Version 9</a>, 
                                <a style="color:#00F;" href="http://www.apple.com/de/safari/download/" target="_blank">Safari</a>) auf Ihrem Rechner<br />
                                <br />
                        </li>
                     </ul>
                  </div>
        </object>
    </div>
	
		<?php 
} 
?>





    
            
            
            
            
          
	
	


</body>
</html>

