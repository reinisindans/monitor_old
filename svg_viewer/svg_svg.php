<?php 
session_start();



/* 
echo  '<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN"  "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">
<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:ev="http://www.w3.org/2001/xml-events" version="1.1" baseProfile="full" 
viewBox="0 0 '.$Dok_width.' '.$Dok_height.'" width="'.$Dok_width.'px" height="'.$Dok_height.'px">';

 */

// Leeren der Errorvariable
$ERROR_DEBUG='';

// Ausgabe in Puffer umleiten
ob_start(); 

include("includes_classes/verbindung_mysqli.php");

// Memory-Limit erweitern
ini_set('memory_limit', '1000M');
// Zeit-Limit erhöhen
ini_set('max_execution_time', '500');

// Einstellung für Browser-Verbindungs-Erkennung (zum beenden des Scripts)
Set_Time_Limit(0);


// Testschalter für Rasterisierung mittels Batik in Datei
// $_SESSION['Dokument']['Dateiausgabe'] = 1;




# svg-header senden:
$content="Content-type: image/svg+xml";
header($content);
# Daten in Speicher lesen:
//$data=ob_get_contents(); 
# Speicher leeren:
//ob_clean();


// Leeren der Anzeigeparameter des Viewers selbst
$_SESSION['Dokument']['X_min_global'] = '';
$_SESSION['Dokument']['Y_min_global']  = '';
$_SESSION['Dokument']['X_max_global'] = '';
$_SESSION['Dokument']['Y_max_global']  = '';
$_SESSION['Dokument']['Width'] = '';
$_SESSION['Dokument']['Height'] = '';
$_SESSION['Dokument']['Polygonanzahl'] = '';
$_SESSION['Dokument']['Fuellung']['Indikator_Wert_min'] = '';
$_SESSION['Dokument']['Fuellung']['Indikator_Wert_max'] = '';

/*
-> nicht sinnvoll für Druck > Schrift wird kleiner (gut für Karte, jedoch schlecht für Legenden usw.)
.. hier wie auch am Ende des Dokuments erstmal auskommentiert...
// Für Druck die größere Anzeige wählen
if($_GET['druck'])
{
	$_SESSION['Dokument']['groesse_X'] = $_SESSION['Dokument']['groesse_X'] + 220;
	$_SESSION['Dokument']['groesse_Y'] = $_SESSION['Dokument']['groesse_Y'] + 200;
} */

	
// Array selbstständig erstellen, falls ID übergeben wurde
API();
// Infos zu Indikator neu und komplett (außerhalb der Session-Arrays => kleinere Speicherstände) erfassen
IND();
// Check aus Berechtigung zur Anzeige der geforderten Daten
SECURITY();


function API()
{
	/* 
	API
			help = Aufrufen der Hilfe (nur GET)			(Int)			
			idk = ID der gespeicherten Karte aus DB 	(Int)
			width = Breite des Kartenfeldes 			(Pixel)
			height = Höhe des Kartenfeldes 				(Pixel)
			unlegend = Legendenfelder ausblenden 		(bool)
			titelsize = Fontsize für Titel				(Pixel)
			links, rechts, oben, unten = Ränder			(Pixel)
			font_size = Schriftgröße für Labels			(Pixel)
	
	*/
	
	// Hilfe unter svg_svg.php?help=1 anbieten:
	if($_GET['help'])
	{
		{
			echo utf8_encode('<?xml version="1.0" encoding="utf-8"?>
			<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN"  "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">
			<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:ev="http://www.w3.org/2001/xml-events" version="1.1" baseProfile="full" 
			viewBox="0 0 300 300" width="300px" height="300px">			
			
			<text x="0" y="0" dx="30" dy="30" style="font-size:20px; font-family:Arial; font-weight:bold;" fill="#999999" >
			Hilfe für Aufruf über API
			</text>
			<text x="0" y="0" dx="30" dy="50" style="font-size:12px; font-family:Arial; font-weight:bold;" fill="#999999" >
			help = Aufrufen der Hilfe (nur GET) (Int)
			</text>
			<text x="0" y="0" dx="30" dy="70" style="font-size:12px; font-family:Arial; font-weight:bold;" fill="#999999" >
			idk = ID der gespeicherten Karte aus DB (Int)
			</text>
			<text x="0" y="0" dx="30" dy="90" style="font-size:12px; font-family:Arial; font-weight:bold;" fill="#999999" >
			width = Breite des Kartenfeldes (Pixel)
			</text>
			<text x="0" y="0" dx="30" dy="110" style="font-size:12px; font-family:Arial; font-weight:bold;" fill="#999999" >
			height = Höhe des Kartenfeldes (Pixel)
			</text>
			<text x="0" y="0" dx="30" dy="130" style="font-size:12px; font-family:Arial; font-weight:bold;" fill="#999999" >
			unlegend = Legendenfelder ausblenden (bool)
			</text>
			<text x="0" y="0" dx="30" dy="150" style="font-size:12px; font-family:Arial; font-weight:bold;" fill="#999999" >
			titelsize = Fontsize für Titel (Pixel)
			</text>
			<text x="0" y="0" dx="30" dy="170" style="font-size:12px; font-family:Arial; font-weight:bold;" fill="#999999" >
			links, rechts, oben, unten = Ränder	(Pixel)
			</text>
			<text x="0" y="0" dx="30" dy="190" style="font-size:12px; font-family:Arial; font-weight:bold;" fill="#999999" >
			font_size = Schriftgröße für Labels (Pixel)
			</text>	
			</svg>');	
		}
		die;
	}
	
	include("includes_classes/verbindung_mysqli.php");
	
	$Viewer_Berechtigung_Speicher = $_SESSION['Dokument']['ViewBerechtigung'];
	$Viewer_Berechtigung_Speicher_IP_Restrikt = $_SESSION['Dokument']['ViewBerechtigung_IP_Restriktion'];
	
	// Alle Einstellungen aus DB übernehmen, falls ID Übergeben wurde
	// $_GET['typo3'] abfrage verhindert nochmaliges laden, falls idk in Adresszeile vorkommt
	if($_POST['idk'] or $_GET['idk']) 
	{
		// API in Benutzung?
		$GLOBALS['API'] = 1;
		
		// speichern des Session-Array für Wiederherstellung
		$Sessiondaten = $_SESSION;
		$GLOBALS['SESSION_SPEICHER'] = serialize($Sessiondaten);
		
		// POST und GET mit Variable "id" zulassen
		if($_POST['idk']) $ID_POST=$_POST['idk'];
		if($_GET['idk']) $ID_POST=$_GET['idk'];
		
		
		$SQL_id = "SELECT * FROM v_user_link_speicher WHERE id='".$ID_POST."'";
		$Ergebnis_id = mysqli_query($Verbindung,$SQL_id);
		
		if(@mysqli_result($Ergebnis_id,0,'array_value'))
		{
			$SV = mysqli_result($Ergebnis_id,0,'array_value');
			$_SESSION = unserialize($SV);
		}
		
		// Setzen der Berechtigung auf vorherige (vor dem laden des Speicherstandes) Wert
		$_SESSION['Dokument']['ViewBerechtigung'] = $Viewer_Berechtigung_Speicher;
		$_SESSION['Dokument']['ViewBerechtigung_IP_Restriktion'] = $Viewer_Berechtigung_Speicher_IP_Restrikt;
		
		// Prüfen der Berechtigung, den geforderten Inhalt anzuzeigen
		$SQL_Indikator_Rechte = "SELECT STATUS_INDIKATOR_FREIGABE FROM m_indikator_freigabe 
				WHERE ID_INDIKATOR = '".$_SESSION['Dokument']['Fuellung']['Indikator']."' AND JAHR = '".$_SESSION['Dokument']['Jahr_Anzeige']."'";
		$Ergebnis_Indikator_Rechte = mysqli_query($Verbindung,$SQL_Indikator_Rechte);
		if(@mysqli_result($Ergebnis_Indikator_Rechte,0,'STATUS_INDIKATOR_FREIGABE') < $_SESSION['Dokument']['ViewBerechtigung'])
		{
			$_SESSION = array();
			$_SESSION['ID_Loginfehler'] = utf8_encode("Sie haben nicht die erforderlichen Rechte für die Darstellung der Karte.");
		}
	
	
		// Höhe und Breite definierbar machen
		if($_POST['width']) $_SESSION['Dokument']['groesse_X'] = $_POST['width'];
		if($_GET['width']) $_SESSION['Dokument']['groesse_X'] = $_GET['width'];

		if($_POST['height']) $_SESSION['Dokument']['groesse_Y'] = $_POST['height'];
		if($_GET['height']) $_SESSION['Dokument']['groesse_Y'] = $_GET['height'];
		
		// Falls nur ein Parameter übergeben wurde, den Fehlenden ergänzen (quadratisch)
		if(!$_SESSION['Dokument']['groesse_Y']) $_SESSION['Dokument']['groesse_Y'] = $_SESSION['Dokument']['groesse_X'];
		if(!$_SESSION['Dokument']['groesse_X']) $_SESSION['Dokument']['groesse_X'] = $_SESSION['Dokument']['groesse_Y'];
		
		// Legende ein-/ausblenden
		if($_GET['unlegend'] or $_POST['unlegend'])
		{
			$_SESSION['Dokument']['unlegend'] = 1;
			$_SESSION['Dokument']['Hoehe_Legende_unten'] = 0;
		}
		else
		{
			$_SESSION['Dokument']['unlegend'] = 0;
		}
		
		// Fontsize für Titel
		if($_GET['titelsize'] or $_POST['titelsize'])
		{
			if($_GET['titelsize']) { $_SESSION['Dokument']['titelsize'] = $_GET['titelsize']; }else{ $_SESSION['Dokument']['titelsize'] = $_POST['titelsize']; }
		}
		else
		{
			$_SESSION['Dokument']['titelsize'] = 14;
		}
		
		// Ränder
		if($_GET['links']) $_SESSION['Dokument']['Rand_L'] = $_GET['links'];
		if($_POST['links']) $_SESSION['Dokument']['Rand_L'] = $_POST['links'];
		
		if($_GET['rechts']) $_SESSION['Dokument']['Rand_R'] = $_GET['rechts'];
		if($_POST['rechts']) $_SESSION['Dokument']['Rand_R'] = $_POST['rechts'];
		
		if($_GET['oben']) $_SESSION['Dokument']['Rand_O'] = $_GET['oben'];
		if($_POST['oben']) $_SESSION['Dokument']['Rand_O'] = $_POST['oben'];
		
		if($_GET['unten']) $_SESSION['Dokument']['Rand_U'] = $_GET['unten'];
		if($_POST['unten']) $_SESSION['Dokument']['Rand_U'] = $_POST['unten'];
		
		// Schriftgröße für Labels
		if($_GET['font_size']) $Font_size_Labels = $_GET['font_size'];
		if($_POST['font_size']) $Font_size_Labels = $_POST['font_size'];
		
	}
}



function IND()
{
		// Indikator-Eigenschaften nochmals erfassen (bügelt evtl. enthaltene alte Fehler aus gespeicherten Karten aus)
		include("includes_classes/verbindung_mysqli.php");
		// nur ausführen, wenn Indikator schon gewählt
		if($_SESSION['Dokument']['Fuellung']['Indikator'])
		{
			$SQL_Indikator_Info = "SELECT * FROM m_indikatoren WHERE ID_INDIKATOR='".$_SESSION['Dokument']['Fuellung']['Indikator']."'";
			$Ergebnis_Indikator_Info = mysqli_query($Verbindung,$SQL_Indikator_Info);
			
			if($_SESSION['Dokument']['Sprache'] == 'DE')
			{
				$_SESSION['Dokument']['Fuellung']['Indikator_Einheit'] = utf8_encode(@mysqli_result($Ergebnis_Indikator_Info,0,'EINHEIT'));
				$_SESSION['Dokument']['Fuellung']['Indikator_Beschreibung'] = utf8_encode(@mysqli_result($Ergebnis_Indikator_Info,0,'INDIKATOR_NAME'));
				$_SESSION['Dokument']['Fuellung']['Rundung'] = @mysqli_result($Ergebnis_Indikator_Info,0,'RUNDUNG_NACHKOMMASTELLEN');
				$GLOBALS['INFO_VIEWER_ZEILE_1'] = utf8_encode(@mysqli_result($Ergebnis_Indikator_Info,0,'INFO_VIEWER_ZEILE_1'));
				$GLOBALS['INFO_VIEWER_ZEILE_2'] = utf8_encode(@mysqli_result($Ergebnis_Indikator_Info,0,'INFO_VIEWER_ZEILE_2'));
				$GLOBALS['INFO_VIEWER_ZEILE_3'] = utf8_encode(@mysqli_result($Ergebnis_Indikator_Info,0,'INFO_VIEWER_ZEILE_3'));
				$GLOBALS['INFO_VIEWER_ZEILE_4'] = utf8_encode(@mysqli_result($Ergebnis_Indikator_Info,0,'INFO_VIEWER_ZEILE_4'));
				$GLOBALS['INFO_VIEWER_ZEILE_5'] = utf8_encode(@mysqli_result($Ergebnis_Indikator_Info,0,'INFO_VIEWER_ZEILE_5'));
				$GLOBALS['INFO_VIEWER_ZEILE_6'] = utf8_encode(@mysqli_result($Ergebnis_Indikator_Info,0,'INFO_VIEWER_ZEILE_6'));
				$GLOBALS['STANDARD-DATENGRUNDLAGE'] = @mysqli_result($Ergebnis_Indikator_Info,0,'DATENGRUNDLAGE_ATKIS'); // Schalter für Anzeige der STandard ATKIS Datengrundlage (BKG)
				$GLOBALS['DATENGRUNDLAGE_ZEILE_1'] = utf8_encode(@mysqli_result($Ergebnis_Indikator_Info,0,'DATENGRUNDLAGE_ZEILE_1'));
				$GLOBALS['DATENGRUNDLAGE_ZEILE_2'] = utf8_encode(@mysqli_result($Ergebnis_Indikator_Info,0,'DATENGRUNDLAGE_ZEILE_2'));
				$GLOBALS['MITTLERE_AKTUALITAET_IGNORE'] = utf8_encode(@mysqli_result($Ergebnis_Indikator_Info,0,'MITTLERE_AKTUALITAET_IGNORE'));
			}
			
			if($_SESSION['Dokument']['Sprache'] == 'EN')
			{
				$_SESSION['Dokument']['Fuellung']['Indikator_Einheit'] = utf8_encode(@mysqli_result($Ergebnis_Indikator_Info,0,'EINHEIT_EN'));
				$_SESSION['Dokument']['Fuellung']['Indikator_Beschreibung'] = utf8_encode(@mysqli_result($Ergebnis_Indikator_Info,0,'INDIKATOR_NAME_EN'));
				$_SESSION['Dokument']['Fuellung']['Rundung'] = @mysqli_result($Ergebnis_Indikator_Info,0,'RUNDUNG_NACHKOMMASTELLEN');
				$GLOBALS['INFO_VIEWER_ZEILE_1'] = utf8_encode(@mysqli_result($Ergebnis_Indikator_Info,0,'INFO_VIEWER_ZEILE_1_EN'));
				$GLOBALS['INFO_VIEWER_ZEILE_2'] = utf8_encode(@mysqli_result($Ergebnis_Indikator_Info,0,'INFO_VIEWER_ZEILE_2_EN'));
				$GLOBALS['INFO_VIEWER_ZEILE_3'] = utf8_encode(@mysqli_result($Ergebnis_Indikator_Info,0,'INFO_VIEWER_ZEILE_3_EN'));
				$GLOBALS['INFO_VIEWER_ZEILE_4'] = utf8_encode(@mysqli_result($Ergebnis_Indikator_Info,0,'INFO_VIEWER_ZEILE_4_EN'));
				$GLOBALS['INFO_VIEWER_ZEILE_5'] = utf8_encode(@mysqli_result($Ergebnis_Indikator_Info,0,'INFO_VIEWER_ZEILE_5_EN'));
				$GLOBALS['INFO_VIEWER_ZEILE_6'] = utf8_encode(@mysqli_result($Ergebnis_Indikator_Info,0,'INFO_VIEWER_ZEILE_6_EN'));
				$GLOBALS['STANDARD-DATENGRUNDLAGE'] = @mysqli_result($Ergebnis_Indikator_Info,0,'DATENGRUNDLAGE_ATKIS'); // Schalter für Anzeige der STandard ATKIS Datengrundlage (BKG)
				$GLOBALS['DATENGRUNDLAGE_ZEILE_1'] = utf8_encode(@mysqli_result($Ergebnis_Indikator_Info,0,'DATENGRUNDLAGE_ZEILE_1_EN'));
				$GLOBALS['DATENGRUNDLAGE_ZEILE_2'] = utf8_encode(@mysqli_result($Ergebnis_Indikator_Info,0,'DATENGRUNDLAGE_ZEILE_2_EN'));
				$GLOBALS['MITTLERE_AKTUALITAET_IGNORE'] = utf8_encode(@mysqli_result($Ergebnis_Indikator_Info,0,'MITTLERE_AKTUALITAET_IGNORE'));
			}
		}
}


function SECURITY()
{
	include("includes_classes/verbindung_mysqli.php");
	
	// Check ob der gewählte Indikator in Kombination mit dem Zeitschnitt unter der aktuellen Viewer-Berechtigung angezeigt werden darf
	$SQL_Indikator_Zeitschnitt_Berechtigung = "SELECT JAHR FROM m_indikator_freigabe
									WHERE STATUS_INDIKATOR_FREIGABE >= '".$_SESSION['Dokument']['ViewBerechtigung']."'
									AND ID_INDIKATOR = '".$_SESSION['Dokument']['Fuellung']['Indikator']."' 
									AND JAHR = '".$_SESSION['Dokument']['Jahr_Anzeige']."'";
	$Ergebnis_Indikator_Zeitschnitt_Berechtigung = mysqli_query($Verbindung,$SQL_Indikator_Zeitschnitt_Berechtigung);
	// Falls nicht erlaubt: Ausgabe eines SVG mit Hinweis:
	if(!$Zeitschnitt = @mysqli_result($Ergebnis_Indikator_Zeitschnitt_Berechtigung,0,'JAHR'))
	{
		// Indikatorinfo ermitteln
		$SQL_Indikator_Name = "SELECT INDIKATOR_NAME FROM m_indikatoren	WHERE ID_INDIKATOR = '".$_SESSION['Dokument']['Fuellung']['Indikator']."'";
		$Ergebnis_Indikator_Name = mysqli_query($Verbindung,$SQL_Indikator_Name);
		
		// SVG ausgeben
		echo utf8_encode('<?xml version="1.0" encoding="utf-8"?>
			<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN"  "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">
			<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:ev="http://www.w3.org/2001/xml-events" version="1.1" baseProfile="full" 
			viewBox="0 0 300 300" width="300px" height="300px">
			<rect x="0" y="0" width="760px" height="500px" fill="#FFFFFF" stroke="none"/>
			<text x="20" y="20" style="font-size:6px; font-family:Arial;" >Der angeforderte Indikator: </text>
			<text x="20" y="30" style="font-size:7px; font-family:Arial;" >'.@mysqli_result($Ergebnis_Indikator_Name,0,'INDIKATOR_NAME').'</text>
			<text x="20" y="40" style="font-size:6px; font-family:Arial;" >ist für diesen Zeitschnitt leider nicht verfügbar.</text>
		</svg>');
		die;
	}							
}

function LEER()
{
	if($_SESSION['Dokument']['ViewBerechtigung'] == "0")
	{
		// Errormeldung für Debugging
		if($ERROR_DEBUG != '') $Errormeldung = "alert('".$ERROR_DEBUG."');";
	}
	// SVG ausgeben und hinweis auf fehlende Objekte in der Karte
	echo utf8_encode('<?xml version="1.0" encoding="utf-8"?>
			<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN"  "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">
			<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:ev="http://www.w3.org/2001/xml-events" version="1.1" baseProfile="full" 
			viewBox="0 0 300 300" width="300px" height="300px">
			<rect x="0" y="0" width="760px" height="500px" fill="#FFFFFF" stroke="none"/>
			<text x="20" y="20" style="font-size:6px; font-family:Arial;" >Hinweis:</text>
			<text x="20" y="30" style="font-size:7px; font-family:Arial;" >Ihre Auswahl ergab keine anzeigbaren Ergebnisse.</text>
			<text x="20" y="40" style="font-size:6px; font-family:Arial;" >Bitte ändern Sie die Raumgliederung oder wählen Sie einen anderen Zeitschnitt aus!</text>
			
			<script type="text/javascript">
			
			'.$Errormeldung.'
			</script>

		</svg>');
	die;							
}
function LEER_GEM()
{

	if($_SESSION['Dokument']['ViewBerechtigung'] == "0")
	{
		// Errormeldung für Debugging
		if($ERROR_DEBUG != '') $Errormeldung = "alert('".$ERROR_DEBUG."');";
	}
	// SVG ausgeben und Hinweis dass Gemeinden bei Raumglieg. gewählt werden müssen
	echo utf8_encode('<?xml version="1.0" encoding="utf-8"?>
			<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN"  "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">
			<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:ev="http://www.w3.org/2001/xml-events" version="1.1" baseProfile="full" 
			viewBox="0 0 300 300" width="300px" height="300px">
			<rect x="0" y="0" width="760px" height="500px" fill="#FFFFFF" stroke="none"/>
			<text x="20" y="30" style="font-size:7px; font-family:Arial;" >Für Gemeindedarstellung bitte "Räuml. Ausdehnung" = "Bundesland" bzw. "Kreis" </text>
			<text x="20" y="40" style="font-size:7px; font-family:Arial;" >und "Raumgliederung" = "Gemeinden" wählen.</text>
			<script type="text/javascript">
			
			'.$Errormeldung.'
			</script>

		</svg>');
	die;
				
}


$Startzeit = date('i:s');

// temporär belegte Wortmarken
$Nichts_ausgewaehlt = utf8_encode('Nichts ausgewählt.');
$Leer_Info = utf8_encode("keine Informationen");



// Feststellen, ob das Script als Vergleichsanzeige für Zeitschnittvergleiche aus dem Tabellentool heraus verarbeitet werden soll
if($_SESSION['Tabelle']['KARTENANZEIGE'])
{
	$Vergleichskarte = '1';
	// Var wieder zurücksetzen
	$_SESSION['Tabelle']['KARTENANZEIGE'] = '0';
}


// Wenn keine Vergleichskarte gewählt ist, dann lösche Vormerkungen für Ausschluss bestimmter Raumeinheiten aus Array
if(!$Vergleichskarte) 
{
	$_SESSION['Tabelle']['AGS_IGNORE'] = array(); 
	$_SESSION['Tabelle']['KARTENANZEIGE_WERT'] = '';
}

// evtl. geforderte Datei-Ausgabeform annehmen
if($_POST['Dateiausgabe'])
{
	$_SESSION['Dokument']['Dateiausgabe']  = 1;
	$_SESSION['Dokument']['Dateiausgabe_typ_datei'] = $_POST['Dateiausgabe_typ_datei'];
	$_SESSION['Dokument']['Dateiausgabe_width'] = $_POST['Dateiausgabe_width'];
}
else
{
	$_SESSION['Dokument']['Dateiausgabe']  = 0;
}


// --- PostGIS-Einstellungen ---
// Simplifizierungsgrad
$_SESSION['Dokument']['PG_Simplify'] = 30;

// Relative (1) oder absolute (0) Pfade ausgeben
$_SESSION['Dokument']['PG_rel_abs'] = 1;

// Koordinatensystem/Projektion 
// Gauß-Krüger
if(!$_SESSION['Dokument']['PG_SRID'])
{
	$_SESSION['Dokument']['PG_SRID'] = '31465';
	// Nachkommastellen für Koordinaten
	$_SESSION['Dokument']['PG_SVG_Genauigkeit'] = 0;
}

// Simplify Genauigkeit für Zusatzebenen
$_SESSION['Dokument']['PG_Simplify_zus'] = 25;


// mögliche Zusatzebenen checken
// Verwaltungsgrenzen 													   			Hirarchische Aktivierung für Beschriftung
if($_SESSION['Dokument']['zusatz_bundesland']) { 	$Zusatzebene[1] = 'bld'; 		/* $GLOBALS['Zusatzebene_aktiv'] = "bld"; */}
if($_SESSION['Dokument']['zusatz_kreis']) { 		$Zusatzebene[2] = 'krs'; 		/* $GLOBALS['Zusatzebene_aktiv'] = "krs"; */}
if($_SESSION['Dokument']['zusatz_gemeinde']) { 		$Zusatzebene[3] = 'gem'; 		/* $GLOBALS['Zusatzebene_aktiv'] = "gem"; */} // (gem für Raster sinnvoll)
if($_SESSION['Dokument']['zusatz_ror']) { 		$Zusatzebene[7] = 'ror'; }

// Topographisches < durch Verschneidung mit KartenObjekten ermitteln
if($_SESSION['Dokument']['zusatz_gew']) { $Zusatzebene[4] = 'gew'; }
// if($_SESSION['Dokument']['zusatz_gew_fein']) { $Zusatzebene[5] = 'gew_fein'; }
if($_SESSION['Dokument']['zusatz_bab']) { $Zusatzebene[6] = 'bab'; }
if($_SESSION['Dokument']['zusatz_db']) { $Zusatzebene[8] = 'db'; }



// mögliche Fehlercodes in einem Array erfassen
$SQL_FC = "SELECT * FROM m_fehlercodes WHERE FEHLERCODE >= '1' ORDER BY FEHLERCODE";
$Ergebnis_FC = mysqli_query($Verbindung,$SQL_FC);
$i_FCP=0;
while(@mysqli_result($Ergebnis_FC,$i_FCP,'FEHLERCODE'))
{
	$_SESSION['Dokument']['Fuellung']['Fehlercodes'][@mysqli_result($Ergebnis_FC,$i_FCP,'FEHLERCODE')]['FEHLERCODE'] = @mysqli_result($Ergebnis_FC,$i_FCP,'FEHLERCODE');
	$_SESSION['Dokument']['Fuellung']['Fehlercodes'][@mysqli_result($Ergebnis_FC,$i_FCP,'FEHLERCODE')]['FEHLER_NAME'] = utf8_encode(@mysqli_result($Ergebnis_FC,$i_FCP,'FEHLER_'.$_SESSION['Dokument']['Sprache']));
	$_SESSION['Dokument']['Fuellung']['Fehlercodes'][@mysqli_result($Ergebnis_FC,$i_FCP,'FEHLERCODE')]['FEHLER_BESCHREIBUNG'] = utf8_encode(@mysqli_result($Ergebnis_FC,$i_FCP,'FEHLER_'.$_SESSION['Dokument']['Sprache']));
	$_SESSION['Dokument']['Fuellung']['Fehlercodes'][@mysqli_result($Ergebnis_FC,$i_FCP,'FEHLERCODE')]['FEHLER_FARBCODE'] = @mysqli_result($Ergebnis_FC,$i_FCP,'FEHLER_FARBCODE');
	$i_FCP++;
}

// Definition für Hinweiscodes erfassen
$SQL_HC = "SELECT * FROM m_hinweiscodes";
$Ergebnis_HC = mysqli_query($Verbindung,$SQL_HC);
$i_h=0;
while($HC_Code = @mysqli_result($Ergebnis_HC,$i_h,'HC'))
{
	$HC_Definition[$HC_Code]['HC_NAME'] = utf8_encode(@mysqli_result($Ergebnis_HC,$i_h,'HC_NAME'));
	$HC_Definition[$HC_Code]['HC_INFO'] = utf8_encode(@mysqli_result($Ergebnis_HC,$i_h,'HC_INFO'));
	$HC_Definition[$HC_Code]['HC_KURZ'] = utf8_encode(@mysqli_result($Ergebnis_HC,$i_h,'HC_KURZ'));
	$i_h++;
}



// mögliche Farbcodes für Aktualität in einem Array erfassen
$SQL_FCA = "SELECT * FROM m_zeichenvorschrift_aktualitaet";
$Ergebnis_FCA = mysqli_query($Verbindung,$SQL_FCA);
$i_FCA=0;
while(@mysqli_result($Ergebnis_FCA,$i_FCA,'FUELLUNG_AKTUALITAET'))
{
	$JahrAkt = @mysqli_result($Ergebnis_FCA,$i_FCA,'JAHR');
	$FCA_Jahr[$JahrAkt] = @mysqli_result($Ergebnis_FCA,$i_FCA,'FUELLUNG_AKTUALITAET');

	$i_FCA++;
}







// Rahmenbedingungen ermitteln
if(is_array($_SESSION['Datenbestand']))
{
	foreach($_SESSION['Datenbestand'] as $DatenSet)
	{
		//echo  $DatenSet['NAME'];
		if($DatenSet['View']=='1')
		{
			// Check ob schon Region zur Anzeige gewählt wurde
			$Region = '1';
		}
	}
}



// Erfassen des Wertebereichs und der Einzelwerte für gewählten Indikator wenn Raumgliederung und Indikator gesetzt
if($Indikator = $_SESSION['Dokument']['Fuellung']['Indikator'] and $_SESSION['Dokument']['Raumgliederung'] and $Region=='1')
{
	

	
	
	
	// Erfassung der Werte + Informationen 

	// $_SESSION['Dokument']['indikator_lokal'] = '1';
	// Globales oder lokales Max-Min per Schalter 'indikator_lokal'

	//if($_SESSION['Dokument']['indikator_lokal'] == '1')
	//{


	// Kartenobjekte aus Postgres-Daten ermitteln (Hier gemacht, da das ermitteln der zutreffenden Objekte aus PostGIS die sicherste Methode ist)
	// ----------------------------------------------------------------------------------------------------------------------------------------------
		
		
	$AW_Zaehler = 0;
	foreach($_SESSION['Datenbestand'] as $DatenSet)
	{
		if($DatenSet['View']=='1' and $_SESSION['Dokument']['Fuellung']['Indikator'])
		{
			// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! RASTER-AUSNAHME !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
			// Ausnahme für Raster bilden: ags_v beinhaltet übergeordnete Ebene und ist hier einzusetzen, wenn Raumgliederung = rst
			if($_SESSION['Dokument']['Raumgliederung'] == "rst" or $_SESSION['Dokument']['Raumgliederung'] == "r05" or $_SESSION['Dokument']['Raumgliederung'] == "r10")
			{
				$PG_Auswahlkriterium = "ags_v";
				$PG_Group_Statement = " GROUP BY ags";
			}
			else
			{
				$PG_Auswahlkriterium = $DatenSet['Auswahlkriterium'];
			}
			   
			if(is_array($DatenSet['Auswahlkriterium_Wert']))
			{
				// Auswahlkriterium_Werte aus Array aufbereiten
				$AWerte_SQL_PG=''; // Variable leeren
				foreach($DatenSet['Auswahlkriterium_Wert'] as $AWert) // Auswahl SQL-Beginn oder Erweiterung
				{
					if($AWerte_SQL_PG)
					{
						$AWerte_SQL_PG = $AWerte_SQL_PG." OR ".$PG_Auswahlkriterium." ".$DatenSet['Auswahloperator']
						." '".$DatenSet['AuswahloperatorZusatz_1'].$AWert.$DatenSet['AuswahloperatorZusatz_2']."' ";
					}
					else
					{
						$AWerte_SQL_PG = " AND (".$PG_Auswahlkriterium." ".$DatenSet['Auswahloperator']
						." '".$DatenSet['AuswahloperatorZusatz_1'].$AWert.$DatenSet['AuswahloperatorZusatz_2']."' ";
					}
				}
				$AWerte_SQL_PG = $AWerte_SQL_PG.")"; // SQL-Klammer schließen
			}
							
			// AGS erfassen
			//--------------
			$ERROR_DEBUG = $SQL_PostGIS = "SELECT ags FROM ".$DatenSet['DB_Tabelle']." WHERE gid >= '0' ".$AWerte_SQL_PG.$PG_Group_Statement;
			$ERGEBNIS_PGSQL_AGS =  @pg_query($Verbindung_PostgreSQL,$SQL_PostGIS);  // performanterer Weg!!! /// Fehler (Syntax-Error) hier möglich... durch fehlenden Tabellennamen!?
			
				
				
				
				
			// -----------------------------------------------------------------------------------------------------------------------------------------------
			// gefundene AGS aus vorhandenen Geometrien speichern (für gezielte MySQL-Abfragen)
			
			while($PG_Zeile = @pg_fetch_assoc($ERGEBNIS_PGSQL_AGS))
			{
				$AWerte_AGS[$AW_Zaehler] = $PG_Zeile['ags'];
				$AW_Zaehler++;
			}
		}
	}
	//}
	
	
// ----	
$Zwischenzeit_Einzelwerte_1 = date('i:s');
// ----

	// Zusammensetzen der AGS Bedingungen für SQL > alle relevanten AGS < für Aktualität und MIN & MAX -Abfragen
	// ... wird im weiteren Verlauf noch genutzt ... nicht mehr jedoch für Aktualität!
	$SQL_Eingrenzung_DS = " AND (";
	for($i_AGS = 0 ; $i_AGS < $AW_Zaehler ; $i_AGS++)
	{
		if($i_AGS != 0) $SQL_Eingrenzung_DS = $SQL_Eingrenzung_DS." OR "; 
		$SQL_Eingrenzung_DS = $SQL_Eingrenzung_DS." AGS = '".$AWerte_AGS[$i_AGS]."' "; 
	}
	$SQL_Eingrenzung_DS = $SQL_Eingrenzung_DS.") "; 
	
	 
	 
	// ---------- Methode C !!!
	// funktioniert erstaunlicherweise etwas performanter als zusammengesetzte SELECTs !??????
	
	//Erfassen der SOLL-Grundaktualität zum ermitteln der Differenz zu tatsächlichen Grundaktualitäten
	$SQL_Aktualitäts_Verweis = "SELECT AKTUALITAET_VIEWER FROM v_geometrie_jahr_viewer_postgis WHERE Jahr_im_Viewer = '".$_SESSION['Dokument']['Jahr_Anzeige']."'"; 
	$Ergebnis_Aktualitäts_Verweis = mysqli_query($Verbindung,$SQL_Aktualitäts_Verweis);
	// Geht bei temp. falscher Geometrie schief: $Grundakt_Verweis = @mysqli_result($Ergebnis_Aktualitäts_Verweis,0,'AKTUALITAET_VIEWER');
	$Grundakt_Verweis = $_SESSION['Dokument']['Jahr_Anzeige'];
	
	// Erfassen der AKT. für jede benötigte AGS aus indikatorwert-Tabelle mit der Indikator-ID: Z00AG
	for($i_AGS = 0 ; $i_AGS < $AW_Zaehler ; $i_AGS++)
	{
	
		$SQL_Aktualitätswerte = "SELECT AGS,INDIKATORWERT FROM m_indikatorwerte_".$_SESSION['Dokument']['Jahr_Anzeige']." WHERE ID_INDIKATOR = 'Z00AG' AND AGS = '".$AWerte_AGS[$i_AGS]."' AND INDIKATORWERT <= ".$_SESSION['Dokument']['Jahr_Anzeige'].";"; 
		$Ergebnis_Aktualitätswerte = mysqli_query($Verbindung,$SQL_Aktualitätswerte);
		if(@mysqli_result($Ergebnis_Aktualitätswerte,0,'AGS'))
		{
			$AGS_mit_Aktualitaet_Jahr_Mittel[$AWerte_AGS[$i_AGS]] = @mysqli_result($Ergebnis_Aktualitätswerte,0,'INDIKATORWERT');
			$Akt_in_karte_vorhanden[@mysqli_result($Ergebnis_Aktualitätswerte,0,'INDIKATORWERT')] = 1; // An sich so überflüssig und durch Differenz ersetzt
			$AGS_mit_Aktualitaet_Differenz[$AWerte_AGS[$i_AGS]] = $Grundakt_Verweis - @mysqli_result($Ergebnis_Aktualitätswerte,0,'INDIKATORWERT'); // Differenz zur Soll Grundaktualität
			$Akt_Differenz_in_karte_vorhanden[$AGS_mit_Aktualitaet_Differenz[$AWerte_AGS[$i_AGS]]] = 1;
			
		}
		else
		{
			$AGS_mit_Aktualitaet_Jahr_Mittel[$AWerte_AGS[$i_AGS]] = "9999"; // Abfangen von fehlenden AGS in der Aktualität
		}
	} 	
	
	/* 
	... Daten nicht mehr aus dieser Tabelle entnehmen!!!
	// Erfassen der AKT. für jede benötigte AGS
	for($i_AGS = 0 ; $i_AGS < $AW_Zaehler ; $i_AGS++)
	{
		$SQL_Aktualitätswerte = "SELECT AGS,AKTUALITAET,JAHR FROM v_akt_gesamt WHERE JAHR = '".$Grundakt_Verweis."' AND AGS = '".$AWerte_AGS[$i_AGS]."'"; 
		$Ergebnis_Aktualitätswerte = mysqli_query($Verbindung,$SQL_Aktualitätswerte);
		if(@mysqli_result($Ergebnis_Aktualitätswerte,0,'AGS'))
		{
			$AGS_mit_Aktualitaet_Jahr_Mittel[$AWerte_AGS[$i_AGS]] = @mysqli_result($Ergebnis_Aktualitätswerte,0,'AKTUALITAET');
			$Akt_in_karte_vorhanden[@mysqli_result($Ergebnis_Aktualitätswerte,0,'AKTUALITAET')] = 1;
		}
		else
		{
			$AGS_mit_Aktualitaet_Jahr_Mittel[$AWerte_AGS[$i_AGS]] = "9999"; // Abfangen von fehlenden AGS in der Aktualität
		}
	}  */
	// ----------
	
	
	
	
	

	// Min und Max unter beachtung der Stellenzahl der gewählten Raumgliederung ermitteln
	// -----------------
	
	
	// bei "global" nicht eingrenzen
	if($_SESSION['Dokument']['indikator_lokal'] == '1')
	{
		$SQL_Eingrenzung_DS_MIN_MAX = $SQL_Eingrenzung_DS; 	
	}
	
	
	if(!$Vergleichskarte) // --- Schalter für Vergleichskartenanzeige ---
	{
		// Abfrage für Min, Max
		$SQL_Indikatorenwerte = "SELECT MAX(INDIKATORWERT) as Maximum,MIN(INDIKATORWERT) as Minimum FROM m_indikatorwerte_".$_SESSION['Dokument']['Jahr_Anzeige']
									." WHERE (FEHLERCODE < '1' OR FEHLERCODE IS NULL) AND ID_INDIKATOR = '".$Indikator
									."' ".$SQL_Eingrenzung_DS_MIN_MAX
									." AND CHAR_LENGTH(AGS) = '".$_SESSION['Dokument']['Raumgliederung_Stellenanzahl']."' AND VGL_AB = '0';"; 
									
		
		
		
		$Ergebnis_Indikatorenwerte = mysqli_query($Verbindung,$SQL_Indikatorenwerte);
		
		// Füllen der Session-Variablen
		/* ungenau... aber den Werten entsprechend, da diese auch nur normal gerundet werden! */
		$_SESSION['Dokument']['Fuellung']['Indikator_Wert_min'] = round(@mysqli_result($Ergebnis_Indikatorenwerte,0,'Minimum'),$_SESSION['Dokument']['Fuellung']['Rundung'])+1000000000;
		$_SESSION['Dokument']['Fuellung']['Indikator_Wert_max'] = round(@mysqli_result($Ergebnis_Indikatorenwerte,0,'Maximum'),$_SESSION['Dokument']['Fuellung']['Rundung'])+1000000000; 
		
		// Min Max korrekt gerundet
		/* $_SESSION['Dokument']['Fuellung']['Indikator_Wert_min_Dok_rounded'] = floor($_SESSION['Dokument']['Fuellung']['Indikator_Wert_min']*$Rundung)/$Rundung;
		$_SESSION['Dokument']['Fuellung']['Indikator_Wert_max_Dok_rounded'] = ceil($_SESSION['Dokument']['Fuellung']['Indikator_Wert_max']*$Rundung)/$Rundung;
		
		$Rundung = $_SESSION['Dokument']['Fuellung']['Rundung'];
		$_SESSION['Dokument']['Fuellung']['Indikator_Wert_min'] = (floor(@mysqli_result($Ergebnis_Indikatorenwerte,0,'Minimum')*$Rundung)/$Rundung) + 1000000000;
		$_SESSION['Dokument']['Fuellung']['Indikator_Wert_max'] = (ceil(@mysqli_result($Ergebnis_Indikatorenwerte,0,'Maximum')*$Rundung)/$Rundung) + 1000000000;  */
	
		$_SESSION['Dokument']['Fuellung']['Wertebereich'] = $_SESSION['Dokument']['Fuellung']['Indikator_Wert_max'] - $_SESSION['Dokument']['Fuellung']['Indikator_Wert_min'];
		$_SESSION['Dokument']['Fuellung']['Wertebereich_ein_Prozent'] = $_SESSION['Dokument']['Fuellung']['Wertebereich']/100;
		
		// Werte-Min-Max
		$i_min = $_SESSION['Dokument']['Fuellung']['Indikator_Wert_min'];
		$i_max = $_SESSION['Dokument']['Fuellung']['Indikator_Wert_max'];
				
		
	
	}
	else
	{
		// Übergabe aus Tabellentool und Anpassung
		$_SESSION['Dokument']['Fuellung']['Indikator_Wert_min'] = round($_SESSION['temp_vergleichswerte']['Rahmenbedingungen']['Min'],$RVergl = ($_SESSION['Dokument']['Fuellung']['Rundung']+1))+1000000000;
		$_SESSION['Dokument']['Fuellung']['Indikator_Wert_max'] = round($_SESSION['temp_vergleichswerte']['Rahmenbedingungen']['Max'],$RVergl)+1000000000; 
		
		$i_min = $_SESSION['Dokument']['Fuellung']['Indikator_Wert_min']; 
		$i_max = $_SESSION['Dokument']['Fuellung']['Indikator_Wert_max'];
		$_SESSION['temp_vergleichswerte']['Rahmenbedingungen']['Normierung'];
								
	}
	
	// Werte für Verteilungsberechnung
	//-----> muss auch negativ sein können <- ok 
	$i_Wertebereich = $i_max - $i_min; // muss an der Stelle auch immer positiv sein (durch +1000000000 sichergestellt), da Werte aus DB kommen und >0 sein sollten
	$i_1Prozent_Wertebereich = $i_Wertebereich/100; 
	if(!$i_1Prozent_Wertebereich)  $i_1Prozent_Wertebereich=1;  // Fehler bei Auswahl von nur einem Polygon verhindern (dumm, aber schöner)
		
		
		
// ----		
$Zwischenzeit_Einzelwerte_2 = date('i:s');		
// ----	



	// Einzelwerte
	// ------------------
	
	$_SESSION['Temp']['i_Verteilung'] = array(); // Variable leeren nicht vergessen, sonst zählt sie immer weiter hoch ;)
	
	if(!$Vergleichskarte) // --- Schalter für Vergleichskartenanzeige ---
	{
		
		// Normale Karte (keine Vergleichskarte)
		for($i_AGS = 0 ; $i_AGS < $AW_Zaehler ; $i_AGS++)
		{
			$SQL_Eingrenzung_DS = " AND AGS = '".$AWerte_AGS[$i_AGS]."' ";
					
			// Abfrage für Min, Max sowie Einzelwerte
			/* $_SESSION['Error'] =  */ $SQL_Indikatorenwerte = "SELECT AGS,INDIKATORWERT,FEHLERCODE FROM m_indikatorwerte_".$_SESSION['Dokument']['Jahr_Anzeige']." WHERE ID_INDIKATOR = '".$Indikator
								."' ".$SQL_Eingrenzung_DS." AND CHAR_LENGTH(AGS) = '".$_SESSION['Dokument']['Raumgliederung_Stellenanzahl']."' AND VGL_AB = '0'"; 
			$Ergebnis_Indikatorenwerte = mysqli_query($Verbindung,$SQL_Indikatorenwerte); /* or substr(@mysqli_result($Ergebnis_Indikatorenwerte,$i_i,'AGS'),0,1) == "0" */ // AGS = 00000000 auch einbeziehen
			
			// bei vorhandenem Fehlercode oder generell fehlendem Datensatz:
			if(@mysqli_result($Ergebnis_Indikatorenwerte,0,'FEHLERCODE') > 0 or !@mysqli_result($Ergebnis_Indikatorenwerte,0,'AGS')) 
			{
				// Fehlercode mit AGS in einem Array hinterlegen
				if(!@mysqli_result($Ergebnis_Indikatorenwerte,0,'AGS'))
				{
					// Fehlender Datensatz => Fehler Nummer 1 = keine Daten vorhanden, aus ungeklärtem Grund
					$AGS_mit_Fehlern[$AWerte_AGS[$i_AGS]] = '1';
				}
				else
				{
					// Datensatz vorhanden, Fehlernummer übergeben
					$AGS_mit_Fehlern[$AWerte_AGS[$i_AGS]] = @mysqli_result($Ergebnis_Indikatorenwerte,0,'FEHLERCODE');
				}
				
			}
			else
			{
				
				// Einzelwerte nach AGS in $AGS_mit_Werten eintragen
				$AGS_mit_Werten[$AWerte_AGS[$i_AGS]] = round(@mysqli_result($Ergebnis_Indikatorenwerte,$i_i,'INDIKATORWERT'),$_SESSION['Dokument']['Fuellung']['Rundung'])+1000000000;
				
				// Hinweise aus Berechnung aus separater Tabelle erfassen
				$SQL_Hinweiscode = "SELECT HINWEISCODE,HINWEIS_EXTERN FROM m_Indikatorwerte_hinweiscodes 
							WHERE 
							ID_INDIKATOR = '".$Indikator."' 
							AND
							AGS = '".$AWerte_AGS[$i_AGS]."' 
							AND
							JAHR = '".$_SESSION['Dokument']['Jahr_Anzeige']."' 
							ORDER BY JAHR DESC
							";
				$Ergebnis_Hinweiscode = mysqli_query($Verbindung,$SQL_Hinweiscode);
				
				$AGS_mit_Hinweisen[$AWerte_AGS[$i_AGS]] = @mysqli_result($Ergebnis_Hinweiscode,0,'HINWEIS_EXTERN');
				
				// Hinweise aus Prüfung (überschreiben Hinweise aus Berechnung)
				if(@mysqli_result($Ergebnis_Hinweiscode,0,'HINWEISCODE'))
				{
					$AGS_mit_Hinweisen[$AWerte_AGS[$i_AGS]] = $HC_Definition[@mysqli_result($Ergebnis_Hinweiscode,0,'HINWEISCODE')]['HC_KURZ'];
				}
				
				// Werteverteilung speichern ( = Anzahl zugehöriger Raumeinheiten pro Prozentpunkt)
				// Prozentwert gerundet auf Ganzzahl-Prozent (nur bei leeren Indikatoren Fehler <= ok)			
				$i_Prozentwert = floor((round($AGS_mit_Werten[$AWerte_AGS[$i_AGS]],$_SESSION['Dokument']['Fuellung']['Rundung']) - $i_min) / $i_1Prozent_Wertebereich); 
				
				$_SESSION['Temp']['i_Verteilung'][$i_Prozentwert]++; // Hochzählen des Prozent-Array-Wertes um 1
			}
		}
	}
	else
	{
	// Vergleichskartenanzeige							
		foreach($_SESSION['temp_vergleichswerte']['Objekte'] as $Objektdaten)
		{	
			// echo "/".$Objektdaten['AGS']."_".$Objektdaten['V_WERT'];
			if($Objektdaten['FEHLERCODE'] or (!$Objektdaten['V_WERT'] and $Objektdaten['V_WERT'] != '0')) 
			{
					// Datensatz vorhanden, Fehlernummer übergeben
					if(!$Objektdaten['V_WERT'] and $Objektdaten['V_WERT'] != '0')
					{
						$AGS_mit_Fehlern[$Objektdaten['AGS']] = '1';
					}
					// Reihenfolge hier wichtig!
					$AGS_mit_Fehlern[$Objektdaten['AGS']] = $Objektdaten['FEHLERCODE'];
			}
			else
			{
				// Einzelwerte nach AGS in $AGS_mit_Werten eintragen
				$AGS_mit_Werten[$Objektdaten['AGS']] = round($Objektdaten['V_WERT'],$RVergl = ($_SESSION['Dokument']['Fuellung']['Rundung']+1))+1000000000;
				// Hinweiscodes noch nicht berücksichtigt!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
				// $AGS_mit_Hinweisen[$Objektdaten['AGS']] = ??? ;
				
				// Werteverteilung speichern ( = Anzahl zugehöriger Raumeinheiten pro Prozentpunkt)
				// Prozentwert gerundet auf Ganzzahl-Prozent (nur bei leeren Indikatoren Fehler <= ok)	
				$i_Prozentwert = floor((round($AGS_mit_Werten[$Objektdaten['AGS']],$RVergl) - $i_min) / $i_1Prozent_Wertebereich); 
				$_SESSION['Temp']['i_Verteilung'][$i_Prozentwert]++; // Hochzählen des Prozent-Array-Wertes um 1
				
			}	
		}	
	}




	
	// Max-der Verteilung ermitteln
	for($i=0 ; $i<=100 ; $i++)
	{
		if($_SESSION['Temp']['i_Verteilung'][$i] > $i_Verteilung_max) 
		{
			$i_Verteilung_max = $_SESSION['Temp']['i_Verteilung'][$i];
			$_SESSION['Temp']['i_Verteilung']['Max_Prozentzahl'] = $i;
		}
	}
	$_SESSION['Temp']['i_Verteilung']['Max'] = $i_Verteilung_max; // Max-Anzahl der Verteilung in Session verfügbar halten
	
	
	// Teiler für Darstellung ermitteln
	$_SESSION['Temp']['i_Verteilung']['NormTeiler'] = $i_Verteilung_max/30; // 30 Stufen als Default-Teiler
	
	
	// Zeichenvorschrift_min_max
	if($_SESSION['Dokument']['Fuellung']['Typ']=='Farbbereich')
	{
		
		
		// Min und Max erfassen (sicher sinnvoll jeweils über gesamt Deutschland => bessere Vergleichbarkeit unterschiedlicher Karten)
		$R_min_dezimal = hexdec(substr($_SESSION['Dokument']['Fuellung']['Farbwert_min'],0,2));
		$G_min_dezimal = hexdec(substr($_SESSION['Dokument']['Fuellung']['Farbwert_min'],2,2));
		$B_min_dezimal = hexdec(substr($_SESSION['Dokument']['Fuellung']['Farbwert_min'],4,2));
		
		// ---------------> prüfen < oder > und bei Bedarf umkehren!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
		
		$R_max_dezimal = hexdec(substr($_SESSION['Dokument']['Fuellung']['Farbwert_max'],0,2));
		$G_max_dezimal = hexdec(substr($_SESSION['Dokument']['Fuellung']['Farbwert_max'],2,2));
		$B_max_dezimal = hexdec(substr($_SESSION['Dokument']['Fuellung']['Farbwert_max'],4,2));
		
		
		
		// Prüfen ob Farbwert auf- oder absteigend ist
		if($R_max_dezimal == $R_min_dezimal)
		{
			$R_Verhaeltniss = "gleich";
		} 
		else 
		{
			if($R_max_dezimal > $R_min_dezimal)
			{
				$R_Differenz = abs($R_max_dezimal - $R_min_dezimal);
				$R_Verhaeltniss = "aufsteigend";
			} 
			else 
			{
				$R_Differenz = abs($R_min_dezimal - $R_max_dezimal);
				$R_Verhaeltniss = "absteigend";
			}
		}
		
		
		if($G_max_dezimal == $G_min_dezimal)
		{
			$G_Verhaeltniss = "gleich";
		} 
		else 
		{
			if($G_max_dezimal > $G_min_dezimal)
			{
				$G_Differenz = abs($G_max_dezimal - $G_min_dezimal);
				$G_Verhaeltniss = "aufsteigend";
			} 
			else 
			{
				$G_Differenz = abs($G_min_dezimal - $G_max_dezimal);
				$G_Verhaeltniss = "absteigend";
			}
		}
		
		
		if($B_max_dezimal == $B_min_dezimal)
		{
			$B_Verhaeltniss = "gleich";
		} 
		else 
		{
			if($B_max_dezimal > $B_min_dezimal)
			{
				$B_Differenz = abs($B_max_dezimal - $B_min_dezimal);
				$B_Verhaeltniss = "aufsteigend";
			} 
			else 
			{
				$B_Differenz = abs($B_min_dezimal - $B_max_dezimal);
				$B_Verhaeltniss = "absteigend";
			}
		}	
	}
}











	
// Rahmenbedingungen ermitteln
if(is_array($_SESSION['Datenbestand']))
{
	foreach($_SESSION['Datenbestand'] as $DatenSet)
	{
		//echo  $DatenSet['NAME'];
		if($DatenSet['View']!='0')
		{
	
				// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! RASTER-AUSNAHME !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
				// Ausnahme für Raster bilden: ags_v beinhaltet übergeordnete Ebene und ist hier einzusetzen, wenn Raumgliederung = rst
				if($_SESSION['Dokument']['Raumgliederung'] == "rst" or $_SESSION['Dokument']['Raumgliederung'] == "r05" or $_SESSION['Dokument']['Raumgliederung'] == "r10")
				{
					$PG_Auswahlkriterium = "ags_v";
					$PG_Group_Statement = " GROUP BY ags";
				}
				else
				{
					$PG_Auswahlkriterium = $DatenSet['Auswahlkriterium'];
				}
				
				if(is_array($DatenSet['Auswahlkriterium_Wert']))
				{
					// Auswahlkriterium_Werte aus Array aufbereiten (Auswahlkriterium = AGS bzw. Teil-AGS)
					$AWerte_SQL=''; // Variable leeren
					foreach($DatenSet['Auswahlkriterium_Wert'] as $AWert) // Auswahl SQL-Beginn oder Erweiterung
					{
						if($AWerte_SQL)
						{
							$AWerte_SQL = $AWerte_SQL." OR ".$PG_Auswahlkriterium." ".$DatenSet['Auswahloperator']
							." '".$DatenSet['AuswahloperatorZusatz_1'].$AWert.$DatenSet['AuswahloperatorZusatz_2']."' ";
						}
						else
						{
							$AWerte_SQL = " AND (".$PG_Auswahlkriterium." ".$DatenSet['Auswahloperator']
							." '".$DatenSet['AuswahloperatorZusatz_1'].$AWert.$DatenSet['AuswahloperatorZusatz_2']."' ";
						}
					}
					$AWerte_SQL = $AWerte_SQL.")"; // SQL-Klammer schließen
				} 
				
				
				// Polygonanzahl ermitteln, um Rahmenbedingungen berechnen zu können (simplify() o.Ä.)---15.08.17 nicht mehr verwendet da Fehlermeldungen und nicht benötigt
				/*$SQL_PostGIS_Polygonzahl = "SELECT count(ags) AS polygone FROM ".$DatenSet['DB_Tabelle']." WHERE gid >= '0' ".$AWerte_SQL;
				$ERGEBNIS_PGSQL_Polygonzahl =  pg_query($Verbindung_PostgreSQL,$SQL_PostGIS_Polygonzahl);  
				$Polygon_Zeile = @pg_fetch_assoc($ERGEBNIS_PGSQL_Polygonzahl,0);
				$_SESSION['Dokument']['Polygonanzahl'] = $_SESSION['Dokument']['Polygonanzahl'] + $Polygon_Zeile['polygone'];
				*/
	
				
				
				// Min- und Max-Ausdehnung der Ebene ermitteln !!! performanterer Weg!!!
				//-----------------------------------------------------------------------
				/* $_SESSION['Error'] =  */ $SQL_PostGIS_Rahmen = "SELECT MIN(xmin(box3d(ST_Transform((the_geom),".$_SESSION['Dokument']['PG_SRID'].")))) AS x_min,MIN(ymin(box3d(ST_Transform((the_geom),".$_SESSION['Dokument']['PG_SRID'].")))) AS y_min, MAX(xmax(box3d(ST_Transform((the_geom),".$_SESSION['Dokument']['PG_SRID'].")))) AS x_max, MAX(ymax(box2d(ST_Transform((the_geom),".$_SESSION['Dokument']['PG_SRID'].")))) AS y_max FROM ".$DatenSet['DB_Tabelle']." WHERE gid >= '0' ".$AWerte_SQL;
				$ERGEBNIS_PGSQL_Rahmen =  @pg_query($Verbindung_PostgreSQL,$SQL_PostGIS_Rahmen);  
							
				$PG_Zeile = @pg_fetch_assoc($ERGEBNIS_PGSQL_Rahmen,0);
				
				$_SESSION['Datenbestand'][$DatenSet['NAME']]['X_min_global'] = $PG_Zeile['x_min']; 
				$_SESSION['Datenbestand'][$DatenSet['NAME']]['Y_min_global'] = $PG_Zeile['y_min'];
				$_SESSION['Datenbestand'][$DatenSet['NAME']]['X_max_global'] = $PG_Zeile['x_max'];
				$_SESSION['Datenbestand'][$DatenSet['NAME']]['Y_max_global'] = $PG_Zeile['y_max'];
				
				// SVG-Layer für Raumbezug schließen ????
				$_SESSION['Datenbestand'][$DatenSet['NAME']]['SVG_Ebenendefinition'] = '<g opacity ="'.$DatenSet['Transparenz'].'" id="'.$DatenSet['NAME'].'" >';
				
				// Check auf einerseits die Anzeige des Datenpaketes und andererseits, ob darin wirklich anzuzeigende Objekte gefunden wurden (bei kfs ind lks nicht immer der Fall)
				if($DatenSet['View']=='1' and $_SESSION['Datenbestand'][$DatenSet['NAME']]['X_min_global'])
				{
					// Zusammenführen bestehender Rahmenwerte
					if($_SESSION['Dokument']['X_min_global'] > $_SESSION['Datenbestand'][$DatenSet['NAME']]['X_min_global'] or !$_SESSION['Dokument']['X_min_global']) 
						{$_SESSION['Dokument']['X_min_global'] = $_SESSION['Datenbestand'][$DatenSet['NAME']]['X_min_global'];}
					if($_SESSION['Dokument']['Y_min_global'] > $_SESSION['Datenbestand'][$DatenSet['NAME']]['Y_min_global'] or !$_SESSION['Dokument']['Y_min_global']) 
						{$_SESSION['Dokument']['Y_min_global'] = $_SESSION['Datenbestand'][$DatenSet['NAME']]['Y_min_global'];}
					if($_SESSION['Dokument']['X_max_global'] < $_SESSION['Datenbestand'][$DatenSet['NAME']]['X_max_global'] or !$_SESSION['Dokument']['X_max_global']) 
						{$_SESSION['Dokument']['X_max_global'] = $_SESSION['Datenbestand'][$DatenSet['NAME']]['X_max_global'];}
					if($_SESSION['Dokument']['Y_max_global'] < $_SESSION['Datenbestand'][$DatenSet['NAME']]['Y_max_global'] or !$_SESSION['Dokument']['Y_max_global']) 
						{$_SESSION['Dokument']['Y_max_global'] = $_SESSION['Datenbestand'][$DatenSet['NAME']]['Y_max_global'];}
					$_SESSION['Dokument']['Width'] = ($_SESSION['Dokument']['X_max_global'] - $_SESSION['Dokument']['X_min_global']);
					$_SESSION['Dokument']['Height'] = ($_SESSION['Dokument']['Y_max_global'] - $_SESSION['Dokument']['Y_min_global']);
				}
		}
	}
}

// -----> Check
// an dieser beispielhaften Variable, ob tatsächlich Gebiete gefunden wurden .... ansonsten Abbruch und Ausgabe eines SVG mit eiuner Fehlermeldung
if((!$_SESSION['Dokument']['X_min_global']) && ($_SESSION['Dokument']['Raumebene_NAME_Auswertung'] == 'Gemeinde')) LEER_GEM(); 
if(!$_SESSION['Dokument']['X_min_global']) LEER(); 




/* if($Vergleichskarte) // --- Einstellungen für Vergleichskartenanzeige ---
{
	$groesse_X = 800; 
	$groesse_Y = 800-$_SESSION['Dokument']['Hoehe_Legende_unten']; 

	
	// Skalierungsfaktor für Linke Obere Ecke auf die Ausschnittsgröße berechnen
	$xs = $groesse_X/$_SESSION['Dokument']['Width'];
	$ys = $groesse_Y/$_SESSION['Dokument']['Height'];
	
	
	// Lage testen
	$X_Glob_Versatz = 0; 
	$Y_Glob_Versatz = 0;
	if($xs>$ys)
	{ 
		// bei Höhe 100%
		$s = $ys; 
		$X_Glob_Versatz = ($_SESSION['Dokument']['Height'] - $_SESSION['Dokument']['Width']) /2 ;
	}
	else
	{ 
		// bei Breite 100%
		$s = $xs; 
		$Y_Glob_Versatz = ($_SESSION['Dokument']['Width'] - $_SESSION['Dokument']['Height']) /2 ;
		
	}
	
	
	// Weite und Höhe berechnen
	$X_min = -(($_SESSION['Dokument']['X_min_global']-$X_Glob_Versatz)*$s);
	$X_max = ($_SESSION['Dokument']['X_max_global']-$X_Glob_Versatz)*$s;
	$Y_min = ($_SESSION['Dokument']['Y_min_global']+$Y_Glob_Versatz)*$s;
	$Y_max = ($_SESSION['Dokument']['Y_max_global']+$Y_Glob_Versatz)*$s;
	$Width = $_SESSION['Dokument']['Width']*$s;
	$Height = $_SESSION['Dokument']['Height']*$s;
		
	
	// Größe mit Rand
	$XD = $groesse_X;		
	$YD = $groesse_Y;	
	
	// Größe mit Rand und Legendenhöhe
	$YD_gesamt = $YD+$_SESSION['Dokument']['Hoehe_Legende_unten'];
	
	// Skalierungsfaktor für andere Scripte bereitstellen
	$_SESSION['Dokument']['Skalierungsfaktor'] = $s;
	
	
	
}
else
{ */
	
	// Ausschnittsgröße festlegen, falls nicht definiert ... wird aber normalerweise außerhalb definiert!
	if($_SESSION['Dokument']['groesse_X']) { $groesse_X = $_SESSION['Dokument']['groesse_X']; }else{ $groesse_X = 500; }
	if($_SESSION['Dokument']['groesse_Y']) { $groesse_Y = $_SESSION['Dokument']['groesse_Y']; }else{ $groesse_Y = 500; }
	if(!$_SESSION['Dokument']['Width']) $_SESSION['Dokument']['Width'] = '641157';
	if(!$_SESSION['Dokument']['Height']) $_SESSION['Dokument']['Height'] = '865838';
				
	// Skalierungsfaktor für Linke Obere Ecke auf die Ausschnittsgröße berechnen
	$xs = $groesse_X/$_SESSION['Dokument']['Width'];
	$ys = $groesse_Y/$_SESSION['Dokument']['Height'];
	
	
	// Lage testen
	$X_Glob_Versatz = 0; 
	$Y_Glob_Versatz = 0;
	if($xs>$ys)
	{ 
		// bei Höhe 100%
		$s = $ys; 
		$X_Glob_Versatz = ($_SESSION['Dokument']['Height'] - $_SESSION['Dokument']['Width']) /2 ;
	}
	else
	{ 
		// bei Breite 100%
		$s = $xs; 
		$Y_Glob_Versatz = ($_SESSION['Dokument']['Width'] - $_SESSION['Dokument']['Height']) /2 ;
		
	}
	
	
	
	// Weite und Höhe berechnen
	$X_min = -(($_SESSION['Dokument']['X_min_global']-$X_Glob_Versatz)*$s-$_SESSION['Dokument']['Rand_L']);
	$X_max = ($_SESSION['Dokument']['X_max_global']-$X_Glob_Versatz)*$s-$_SESSION['Dokument']['Rand_R'];
	$Y_min = ($_SESSION['Dokument']['Y_min_global']+$Y_Glob_Versatz)*$s+$_SESSION['Dokument']['Rand_U'];
	$Y_max = ($_SESSION['Dokument']['Y_max_global']+$Y_Glob_Versatz)*$s+$_SESSION['Dokument']['Rand_O'];
	$Width = $_SESSION['Dokument']['Width']*$s-$_SESSION['Dokument']['Rand_L']-$_SESSION['Dokument']['Rand_R'];
	$Height = $_SESSION['Dokument']['Height']*$s-$_SESSION['Dokument']['Rand_O']-$_SESSION['Dokument']['Rand_U'];
		
	
	// Größe mit Rand
	$XD = $groesse_X+$_SESSION['Dokument']['Rand_L']+$_SESSION['Dokument']['Rand_R'];		
	$YD = $groesse_Y+$_SESSION['Dokument']['Rand_O']+$_SESSION['Dokument']['Rand_U'];	
	
	// Größe mit Rand und Legendenhöhe
	$YD_gesamt = $YD+$_SESSION['Dokument']['Hoehe_Legende_unten'];
	
	// Skalierungsfaktor für andere Scripte bereitstellen
	$_SESSION['Dokument']['Skalierungsfaktor'] = $s;

/* } */
// ------------------- momentan nicht mehr genutzt, da Simplify mit ArcMAP vorher auf Originaldaten angewendet wurde
// Präzision der zu ermittlenden Geodaten durch Berechnung festlegen .... momentan nicht mehr genutzt, da Simplify mit ArcMAP vorher auf Originaldaten angewendet wurde
// $xp = $_SESSION['Dokument']['Polygonanzahl']*0.002;

// Präzision steigt mit Polygonzahl nach folgender Quadratformel an ... momentan nicht mehr genutzt, da Geometrien vorher abgespeckt werden
// $_SESSION['Dokument']['Praezision'] = round($xp*$xp + $xp*6 + 300,0); // 300 als Grundwert ist ein guter Kompromiss zwischen Geschwindigkeit und dem Auftreten von Klaffungen
// -------------------


// Linienstärke aus Skalierungsfaktor ($s) ermitteln
$xstr = 1/$s;
$_SESSION['Dokument']['Strichstaerke'] = round($xstr*0.5 - ($xstr*$xstr)/8000 ,0);  //+1000; // +1000 nur zum testen anhängen
$_SESSION['Dokument']['Strichstaerke_zusatz'] = $_SESSION['Dokument']['Strichstaerke']; // extra festhalten, da bei manchen Darstellungen $_SESSION['Dokument']['Strichstaerke'] = 0 gesetzt wird

// Gesonderte Strichstärken für Hintergrund und Events festhalten, falls die Haupt-Strichstärke im nächsten Schritt auf 0 gesetzt wird
$_SESSION['Dokument']['Strichstaerke_HG'] = $_SESSION['Dokument']['Strichstaerke'] * 10; 
$_SESSION['Dokument']['Strichstaerke_Event'] = $_SESSION['Dokument']['Strichstaerke'];

// Strichstärke bei Gemeinden und Kartendruck auf 0 bei anzeige auf 30 setzen
if(!$_SESSION['Dokument']['Dateiausgabe'])
{
	if($_SESSION['Dokument']['Raumebene']['Bundesland']['View'] == "1" and $_SESSION['Dokument']['Raumgliederung'] == "gem") $_SESSION['Dokument']['Strichstaerke'] = 30;
}
else 
{
	if($_SESSION['Dokument']['Raumebene']['Bundesland']['View'] == "1" and $_SESSION['Dokument']['Raumgliederung'] == "gem") $_SESSION['Dokument']['Strichstaerke'] = 0;
}

// -------- hier berechnet, da alle Eckdaten ab hier bekannt sind ------------
// Klassen neu berechnen (ausgelagert)
if($_SESSION['Temp']['Klasse'])
{
	include('svg_klassenbildung.php');
}
// ---------------------------------------------------------------------------




// INHALT (Geo-Objekte) als SVG-Codes generieren
// ------------------------------------------------------------------------------------------------------------------------
foreach($_SESSION['Datenbestand'] as $DatenSet)
{
	

	if($DatenSet['View']=='1' and $_SESSION['Dokument']['Fuellung']['Indikator'])
	{

		// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! RASTER-AUSNAHME !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
		// Ausnahme für Raster bilden: ags_v beinhaltet übergeordnete Ebene und ist hier einzusetzen, wenn Raumgliederung = rst
		if($_SESSION['Dokument']['Raumgliederung'] == "rst" or $_SESSION['Dokument']['Raumgliederung'] == "r05" or $_SESSION['Dokument']['Raumgliederung'] == "r10")
		{
			$PG_Auswahlkriterium = "ags_v";
		}
		else
		{
			$PG_Auswahlkriterium = $DatenSet['Auswahlkriterium'];
		}
		   
	
		if(is_array($DatenSet['Auswahlkriterium_Wert']))
		{
			// Auswahlkriterium_Werte aus Array aufbereiten
			$AWerte_SQL=''; // Variable leeren
			foreach($DatenSet['Auswahlkriterium_Wert'] as $AWert) // Auswahl SQL-Beginn oder Erweiterung
			{
				if($AWerte_SQL)
				{
					$AWerte_SQL = $AWerte_SQL." OR ".$PG_Auswahlkriterium." ".$DatenSet['Auswahloperator']
					." '".$DatenSet['AuswahloperatorZusatz_1'].$AWert.$DatenSet['AuswahloperatorZusatz_2']."' ";
				}
				else
				{
					$AWerte_SQL = " AND (".$PG_Auswahlkriterium." ".$DatenSet['Auswahloperator']
					." '".$DatenSet['AuswahloperatorZusatz_1'].$AWert.$DatenSet['AuswahloperatorZusatz_2']."' ";
				}
			}
			$AWerte_SQL = $AWerte_SQL.")"; // SQL-Klammer schließen
		}
		

		// ------------------- Zusätzliche Ebenen einblenden $Zusatzebene[$i_ZEb]-----------------------
		
		if(is_array($DatenSet['Auswahlkriterium_Wert']))
		{
			// Auswahlkriterium_Werte aus Array aufbereiten
			$AWerte_SQL_Zusatz = ''; // Variable leeren
			foreach($DatenSet['Auswahlkriterium_Wert'] as $AWert) // Auswahl SQL-Beginn oder Erweiterung
			{
				if($AWerte_SQL_Zusatz)
				{
					$AWerte_SQL_Zusatz = $AWerte_SQL_Zusatz." OR ags ".$DatenSet['Auswahloperator']
					." '".$DatenSet['AuswahloperatorZusatz_1'].$AWert.$DatenSet['AuswahloperatorZusatz_2']."' ";
				}
				else
				{
					$AWerte_SQL_Zusatz = " AND (ags ".$DatenSet['Auswahloperator']
					." '".$DatenSet['AuswahloperatorZusatz_1'].$AWert.$DatenSet['AuswahloperatorZusatz_2']."' ";
				}
			}
			$AWerte_SQL_Zusatz = $AWerte_SQL_Zusatz.")"; // SQL-Klammer schließen
		}
		// Annahme von max 10 Zusatzebenen
		for($i_ZEb=0 ; $i_ZEb < 10 ; $i_ZEb++)
		{
			// !!! Wichtig: hier werden alle Zusatzebenen generiert und zusammengeführt (bei Mehrfachauswahl) 
			$zusatz[$Zusatzebene[$i_ZEb]] = $zusatz[$Zusatzebene[$i_ZEb]].zusatzebenen($Zusatzebene[$i_ZEb],$AWerte_SQL_Zusatz,$zusatz[$Zusatzebene[$i_ZEb]]);
		}

		
			
		// Geometrien erfassen und Zeichenvorschriften definieren  // ,0,0) für reine Stützpunkte, ,1,0) für Pfaddefinition
		//------------------------------------------------------------------------------------------------------------------
			
		/* Simplify durch Vorberechnung mittels ArcMap/Simplify() ersetzt
		$SQL_PostGIS = "SELECT gen,ags,AsSvg(Simplify(ST_Transform((the_geom),".$_SESSION['Dokument']['PG_SRID']."),'".$_SESSION['Dokument']['Praezision'] ."'),1,0) AS geometrie,AsSvg(centroid(ST_Transform((the_geom),".$_SESSION['Dokument']['PG_SRID']."))) AS centroid,box2d(ST_Transform((the_geom),".$_SESSION['Dokument']['PG_SRID'].")) AS bbox FROM "
		.$DatenSet['DB_Tabelle']." WHERE gid >= '0' ".$AWerte_SQL; */
		
		// bei angezeigtem Raster dieses Feld mit erfassen
		if($_SESSION['Dokument']['Raumgliederung'] == "rst" or $_SESSION['Dokument']['Raumgliederung'] == "r05" or $_SESSION['Dokument']['Raumgliederung'] == "r10")
		{
			$SQL_Raster_zusatzerfassung = "gen_gemeinde,";
		}
		
		
		$Debugging = $SQL_PostGIS = "SELECT ags, gen,"
								.$SQL_Raster_zusatzerfassung
								." AsSvg(ST_Transform(simplify(the_geom,".$_SESSION['Dokument']['PG_Simplify']."),".$_SESSION['Dokument']['PG_SRID']."),"
								.$_SESSION['Dokument']['PG_rel_abs'].",".$_SESSION['Dokument']['PG_SVG_Genauigkeit']
								.") AS geometrie, AsSvg(centroid(ST_Transform((the_geom),".$_SESSION['Dokument']['PG_SRID']."))) AS centroid,box2d(ST_Transform((the_geom),".$_SESSION['Dokument']['PG_SRID'].")) AS bbox FROM "
								.$DatenSet['DB_Tabelle']
								." WHERE gid >= '0' "
								.$AWerte_SQL;
		$ERGEBNIS_PGSQL_VEKTOREN =  @pg_query($Verbindung_PostgreSQL,$SQL_PostGIS);  // performanterer Weg!!!
	
		// gefundene Datensätze abarbeiten
		while($PG_Zeile = @pg_fetch_assoc($ERGEBNIS_PGSQL_VEKTOREN))
		{
			
			// Prüfen ob Browser-Stopp betätigt oder Fenster geschlossen wurde und beenden des Scriptes, falls "ja"
			// ----------> Wichtig für Server-Gesamtperformance!!!
			Flush();
			if(Connection_Aborted()) die;
			// ---------->
			
				
		
			$ags = $PG_Zeile['ags']; // AGS als eindeutiges Identifizierungsmerkmal
			$Obj_Info_Name = $PG_Zeile['gen']; // Name des Info-Objekts
			// $Obj_Info_Name = $PG_Zeile['gen'].' (AGS:'.$PG_Zeile['ags'].')'; // Name des Info-Objekts erweitert... leider auch in Karte angezeigt
			// --> auf Linuxmaschine nicht nötig (liegt als utf8 in DB vor): $Obj_Info_Name = utf8_encode($Obj_Info_Name);
			
			// bei angezeigtem Raster dieses Feld mit ablegen
			/* if($_SESSION['Dokument']['Raumgliederung'] == "rst" or $_SESSION['Dokument']['Raumgliederung'] == "r05" or $_SESSION['Dokument']['Raumgliederung'] == "r10")
			{
				$Obj_Raster_Gemeindename = $PG_Zeile['gen_gemeinde'];
			} */
			
			// Textobjekt bei Bedarf (zu lang) umbrechen
			// -------------------------------
			// Bei ROR "AGS" ausblenden
			if($_SESSION['Dokument']['Raumgliederung'] != 'ror') $AGS_anmerk = 'AGS ';
			
			if(strlen($Obj_Info_Name) < 24)
			{
				$Obj_Info_Name_z1 = $Obj_Info_Name;
				/* keine Rasteranzeige mehr!
				 $Obj_Info_Name_z2 = $Obj_Raster_Gemeindename;
				// Verhindern der Anzeige von "0" in dem Feld
				// if($Obj_Info_Name_z2 == "0") $Obj_Info_Name_z2 = ""; */
				
				$Obj_Info_Name_z2 = ' ('.$AGS_anmerk.$PG_Zeile['ags'].')'; // AGS anhängen
			}
			else
			{
				// Trennung nach " " oder "-" unterscheiden
				if(strstr($Obj_Info_Name," ")) $Trennzeichen = " ";
				if(strstr($Obj_Info_Name,"-")) $Trennzeichen = "-";
						  
				// Suchen nach evtl. vorh. Bindestrich für Trennung und Trennen des Strings
				$TeilStr[0] = strtok($Obj_Info_Name,$Trennzeichen);
				$i_teilstr = 1;
				while($TeilStr[$i_teilstr] = strtok($Trennzeichen)) $i_teilstr++;
			
				// Zeile 1 füllen
				$i_teilstr = 0;
				$Obj_Info_Name_z1 = "";
				$Obj_Info_Name_z1 = $TeilStr[$i_teilstr];
				
				// Verbinden der Teilstrings Zeile 2
				$Obj_Info_Name_z2 = "";
				$i_teilstr = 1;
				while($TeilStr[$i_teilstr])
				{
					$Obj_Info_Name_z2 = $Obj_Info_Name_z2.$Trennzeichen.$TeilStr[$i_teilstr];
					$i_teilstr++;
				}
				$Obj_Info_Name_z2 .= ' ('.$AGS_anmerk.$PG_Zeile['ags'].')'; // AGS anhängen
			}
			
			
			
			
			// -------------- Schriftplatzierung ------------------------
			// Punkt für Objekt-Zentrum (zur Schriftplatzierg. usw.)
			$Centroid = $PG_Zeile['centroid']; 
			$unwichtig = strtok($Centroid,'"');
			$X_Centroid = strtok('"'); // SVG-X-Koordinate des Mittelpunktes
			$unwichtig = strtok('"');
			$Y_Centroid = strtok('"');// SVG-Y-Koordinate des Mittelpunktes
			
			// Bounding Box Ausdehnung erfassen
			$BBox_0 = $PG_Zeile['bbox'];
			$BBox_1 = strtok($BBox_0,'(');
			$BBox_1 = strtok('(');
			$BBox_2 = strtok($BBox_1,')');
			$BBox_X1 = strtok($BBox_2,' ');
			$BBox_3 = strtok(' ');
			$BBox_Y1 = strtok($BBox_3,',');
			
			$bbox=$BBox_Y1;
			
			// leichte Verschiebung des Mittelpunktes um 1/x Objekthöhe nach oben
			// ... Verschiebung bei Multipoligonen fehlerhaft
			$X_Text = $X_Centroid - abs(0.4*(abs($X_Centroid) - abs($BBox_X1)));
			//$X_Centroid = $X_Text;
			
			$Y_Text = $Y_Centroid - abs(0.2*(abs($Y_Centroid) - abs($BBox_Y1)));
			//$Y_Centroid = $Y_Text;
			
			// ------------------------------------------------------------

			
			// <------------ noch erweiterbar!!!
			
			
			// Definition
			// Division durch Null vermeiden (verhindert zwar Fehlermeldungen, gibt aber sinnlose Ergebnisse aus, zumindest ermöglicht es eine Fehleranalyse unter Verwendung des Viewers)
			$Wertebereich = $i_max - $i_min;
			if($Wertebereich > 0) { $Wertebereich = $i_max - $i_min; }else{ $Wertebereich = 1; }
			
			// Flächenfüllung generieren
			if(is_array($_SESSION['Dokument']['Fuellung']) and !$Vergleichskarte)
			{
				// Füllung nach Min und Max als Farbverteilung
				if($_SESSION['Dokument']['Fuellung']['Typ']=='Farbbereich')
				{
					// Min-Max Berechnung vom Beginn des Programms heranziehen
					// Berechnung für einzelne Farbanteile
					// $i_Betrag = ($AGS_mit_Werten[$ags] - $i_min);
					
					
					// außerhalb schon berechnet ... if($Wertebereich > 0) { $Wertebereich = $i_max - $i_min; }else{ $Wertebereich = 1; }
					
					switch ($R_Verhaeltniss) {
						case "gleich":
							$R = $R_min_dezimal; // ist ja = $.._max_dezimal
						break;
						case "aufsteigend":
							
							$R = ($R_Differenz * (($AGS_mit_Werten[$ags] - $i_min) / $Wertebereich)) + $R_min_dezimal;
							// Testausgabe: $R = $R_Differenz." * ((".$AGS_mit_Werten[$ags]." - ".$i_min.") / ".$i_max." - ".$i_min.") + ".$R_min_dezimal;
						break;
						case "absteigend":
							$R = $R_min_dezimal - ($R_Differenz * (($AGS_mit_Werten[$ags] - $i_min) / $Wertebereich)); 
						break;
					}
					
					switch ($G_Verhaeltniss) {
						case "gleich":
							$G = $G_min_dezimal; // ist ja = $.._max_dezimal
						break;
						case "aufsteigend":
							$G = ($G_Differenz * (($AGS_mit_Werten[$ags] - $i_min) / $Wertebereich)) + $G_min_dezimal; 
						break;
						case "absteigend":
							$G = $G_min_dezimal - ($G_Differenz * (($AGS_mit_Werten[$ags] - $i_min) / $Wertebereich));
							//$G = $G_min_dezimal." - ".$G_Differenz." * ((".$AGS_mit_Werten[$ags]." - ".$i_min.") / ".$i_max." - ".$i_min.")";
						break;
					}
					
					switch ($B_Verhaeltniss) {
						case "gleich":
							$B = $B_min_dezimal; // ist ja = $.._max_dezimal
						break;
						case "aufsteigend":
							$B = ($B_Differenz * (($AGS_mit_Werten[$ags] - $i_min) / $Wertebereich)) + $B_min_dezimal;
						break;
						case "absteigend":
							$B = $B_min_dezimal - ($B_Differenz * (($AGS_mit_Werten[$ags] - $i_min) / $Wertebereich));
						break;
					}
					
					
					
					// Runden, in Hex-Wert umwandeln und bei einstelligen Werten eine 0 voranstellen und keine Negativen Werte zulassen <= nicht möglich!!!
					if(strlen($R_hex = dechex(round(abs($R),0))) < 2) $R_hex = '0'.$R_hex;
					if(strlen($G_hex = dechex(round(abs($G),0))) < 2) $G_hex = '0'.$G_hex;
					if(strlen($B_hex = dechex(round(abs($B),0))) < 2) $B_hex = '0'.$B_hex;
					$Fuellung_Obj_Farbe = "#".$R_hex.$G_hex.$B_hex;
					
					// für Effekte in Bezug auf Legende (hier Prozentangabe im Wertebereich ohne Kommastellen statt Farbcode)
					$AGS_mit_Farbcode[$ags] = "v_".round(((($AGS_mit_Werten[$ags] - $i_min) / $Wertebereich)*100),0);
					
					// Fehlerhafte Werte
					if($AGS_mit_Fehlern[$ags])
					{
						$AGS_mit_Werten[$ags] = $_SESSION['Dokument']['Fuellung']['Fehlercodes'][$AGS_mit_Fehlern[$ags]]['FEHLER_NAME'];
						// $Fuellung_Obj_Farbe = $_SESSION['Dokument']['Fuellung']['LeerFarbe'];
						$Fuellung_Obj_Farbe = "url(#errschraff".$AGS_mit_Fehlern[$ags].")";
						// für Effekte in Bezug auf Legende
						$AGS_mit_Farbcode[$ags] = "f_".$AGS_mit_Fehlern[$ags]; 
						// Erfassung ob Fehlerwerte vorgekommen sind
						$Fehlerwerte_vorhanden_Array[$AGS_mit_Fehlern[$ags]] = $AGS_mit_Fehlern[$ags]; // Array mit Fehlernummer x setzen = Fehler in Karte vorhanden						
					}
						
					// Leerwerte füllen
					if(!$AGS_mit_Werten[$ags] and $AGS_mit_Werten[$ags]!='0' and !$AGS_mit_Fehlern[$ags])
					{
						$AGS_mit_Werten[$ags] = $Leer_Info;
						//$Fuellung_Obj_Farbe = $_SESSION['Dokument']['Fuellung']['LeerFarbe'];
						$Fuellung_Obj_Farbe = "url(#leerschraff)";
						// für Effekte in Bezug auf Legende
						$AGS_mit_Farbcode[$ags] = 'f_leer'; 
						// Erfassung ob leerwerte vorgekommen sind
						$Leerwerte_vorhanden = 1;
					}
					
				}
				
				// Füllung nach Klassen
				if($_SESSION['Dokument']['Fuellung']['Typ']=='Klassifizierte Farbreihe')
				{
					
		
					// Klassen erfassen
					// Array muss vorhanden sein, dass Klassifizierte Füllung nur über svg_zeichenvorschrift_klass.php gewählt werden kann, wo auch immer dieses Array berechnet wird:
					if(is_array($_SESSION['Temp']['Klasse'])) 
					{
						$Check_erste_Klasse = 0;
						foreach($_SESSION['Temp']['Klasse'] as $Klassensets)
						{
							// (Einbeziehen des Untersten Wertes in die 1. Klasse) OR Verteilen der Restlichen Werte auf Klassen)
							if($Klassensets['Wert_Untergrenze'] < $AGS_mit_Werten[$ags] and $Klassensets['Wert_Obergrenze'] >= $AGS_mit_Werten[$ags])
							{
								// Testausgabe: echo  " / Wert ".$AGS_mit_Werten[$ags]." Untergrenze ".$Klassensets['Wert_Untergrenze']." Obergrenze ".$Klassensets['Wert_Obergrenze'];
								$Fuellung_Obj_Farbe = "#".$Klassensets['Farbwert'];
								$AGS_mit_Farbcode[$ags] = "k_".$Klassensets['Farbwert']; // für Mouseover-Effekte in Bezug auf Legende
							}
							else
							{
								// Treffer auf Dok-Minwert abfangen und zuordnen 
								if(!$Check_erste_Klasse 
								   and $Klassensets['Wert_Untergrenze'] == $_SESSION['Dokument']['Fuellung']['Indikator_Wert_min_Dok_rounded'] 
								   and $Klassensets['Wert_Untergrenze'] == $AGS_mit_Werten[$ags]) 
								{
									// Testausgabe: echo  " / Wert ".$AGS_mit_Werten[$ags]." Untergrenze ".$Klassensets['Wert_Untergrenze']." Obergrenze ".$Klassensets['Wert_Obergrenze'];
									$Fuellung_Obj_Farbe = "#".$Klassensets['Farbwert'];
									$AGS_mit_Farbcode[$ags] = "k_".$Klassensets['Farbwert']; // für Mouseover-Effekte in Bezug auf Legende
								}
							
							}
							$Check_erste_Klasse = 1;
						}
					}

					
					// Fehlerhafte Werte
					if($AGS_mit_Fehlern[$ags])
					{
						$AGS_mit_Werten[$ags] = $_SESSION['Dokument']['Fuellung']['Fehlercodes'][$AGS_mit_Fehlern[$ags]]['FEHLER_NAME'];
						// $Fuellung_Obj_Farbe = $_SESSION['Dokument']['Fuellung']['LeerFarbe'];
						$Fuellung_Obj_Farbe = "url(#errschraff".$AGS_mit_Fehlern[$ags].")";
						// für Effekte in Bezug auf Legende
						$AGS_mit_Farbcode[$ags] = "f_".$AGS_mit_Fehlern[$ags]; 
						// Erfassung ob Fehlerwerte vorgekommen sind
						$Fehlerwerte_vorhanden_Array[$AGS_mit_Fehlern[$ags]] = $AGS_mit_Fehlern[$ags]; // Array mit Fehlernummer x setzen = Fehler in Karte vorhanden						
					}
					
					// Leerwerte füllen
					if(!$AGS_mit_Werten[$ags] and $AGS_mit_Werten[$ags]!='0' and !$AGS_mit_Fehlern[$ags])
					{
						$AGS_mit_Werten[$ags] = $Leer_Info;
						// $Fuellung_Obj_Farbe = $_SESSION['Dokument']['Fuellung']['LeerFarbe'];
						$Fuellung_Obj_Farbe = "url(#leerschraff)";
						// für Effekte in Bezug auf Legende
						$AGS_mit_Farbcode[$ags] = 'f_leer'; 
						// Erfassung ob leerwerte vorgekommen sind
						$Leerwerte_vorhanden = 1;
					}
					
					
					
					
				}
			
				// Füllung nach manuell erstellten Klassen
				if($_SESSION['Dokument']['Fuellung']['Typ']=='manuell Klassifizierte Farbreihe')
				{
					// Klassen erfassen
					// Array muss vorhanden sein, dass Klassifizierte Füllung nur über svg_zeichenvorschrift_klass.php gewählt werden kann, wo auch immer dieses Array berechnet wird:
					if(is_array($_SESSION['Temp']['manuelle_Klasse'])) 
					{
						foreach($_SESSION['Temp']['manuelle_Klasse'] as $Klassensets)
						{
							if(($Klassensets['Wert_Untergrenze']==$_SESSION['Dokument']['Fuellung']['Indikator_Wert_min'] and $Klassensets['Wert_Obergrenze'] >= $AGS_mit_Werten[$ags]) 
							or ($Klassensets['Wert_Untergrenze'] < $AGS_mit_Werten[$ags] and $Klassensets['Wert_Obergrenze'] >= $AGS_mit_Werten[$ags]))
							{
								$Fuellung_Obj_Farbe = "#".$Klassensets['Farbwert'];
								$AGS_mit_Farbcode[$ags] = "k_".$Klassensets['Farbwert']; // für Effekte in Bezug auf Legende
							}
						}
					}
					
					// Fehlerhafte Werte
					if($AGS_mit_Fehlern[$ags])
					{
						$AGS_mit_Werten[$ags] = $_SESSION['Dokument']['Fuellung']['Fehlercodes'][$AGS_mit_Fehlern[$ags]]['FEHLER_NAME'];
						// $Fuellung_Obj_Farbe = $_SESSION['Dokument']['Fuellung']['LeerFarbe'];
						$Fuellung_Obj_Farbe = "url(#errschraff".$AGS_mit_Fehlern[$ags].")";
						// für Effekte in Bezug auf Legende
						$AGS_mit_Farbcode[$ags] = "f_".$AGS_mit_Fehlern[$ags]; 
						// Erfassung ob Fehlerwerte vorgekommen sind
						$Fehlerwerte_vorhanden_Array[$AGS_mit_Fehlern[$ags]] = $AGS_mit_Fehlern[$ags]; // Array mit Fehlernummer x setzen = Fehler in Karte vorhanden
					}
					
					// Leerwerte füllen
					if(!$AGS_mit_Werten[$ags] and $AGS_mit_Werten[$ags]!='0' and !$AGS_mit_Fehlern[$ags])
					{
						$AGS_mit_Werten[$ags] = $Leer_Info;
						// $Fuellung_Obj_Farbe = $_SESSION['Dokument']['Fuellung']['LeerFarbe'];
						$Fuellung_Obj_Farbe = "url(#leerschraff)";
						// für Effekte in Bezug auf Legende
						$AGS_mit_Farbcode[$ags] = 'f_leer'; 
						// Erfassung ob leerwerte vorgekommen sind
						$Leerwerte_vorhanden = 1;
					}
				}
			}
			
			
			
			// Flächenfüllung für Vergleichskarten => NUR Farbskala von Weiß nach Positiv und Weiß nach Negativ
			if($Vergleichskarte) // --- Schalter für Vergleichskartenanzeige ---
			{
			
			
			
				// Min und Max erfassen (jeweils dann auf FFFFFF zulaufend behandeln => immer aufsteigend
				$R_diff_min_dezimal = 255 - hexdec(substr($_SESSION['Dokument']['Fuellung']['Farbwert_Diff_min'],0,2));
				$G_diff_min_dezimal = 255 - hexdec(substr($_SESSION['Dokument']['Fuellung']['Farbwert_Diff_min'],2,2));
				$B_diff_min_dezimal = 255 - hexdec(substr($_SESSION['Dokument']['Fuellung']['Farbwert_Diff_min'],4,2));
						
				$R_diff_max_dezimal = 255 - hexdec(substr($_SESSION['Dokument']['Fuellung']['Farbwert_Diff_max'],0,2));
				$G_diff_max_dezimal = 255 - hexdec(substr($_SESSION['Dokument']['Fuellung']['Farbwert_Diff_max'],2,2));
				$B_diff_max_dezimal = 255 - hexdec(substr($_SESSION['Dokument']['Fuellung']['Farbwert_Diff_max'],4,2));
				
			
				// Zeichenvorschrift für Werte einhalten
				$AGS_WERT_VERGL = ($AGS_mit_Werten[$ags]-1000000000);
				if($AGS_WERT_VERGL < 0)
				{
					// negative Werte
					// echo "/".$AGS_WERT_VERGL;
					$Wert_stelle = abs((($AGS_WERT_VERGL * 100) / $_SESSION['temp_vergleichswerte']['Rahmenbedingungen']['Min'])/ 100);
					// berechnen und Check auf evtl. Fehler
					$R = 255 - ($R_diff_min_dezimal * $Wert_stelle) ;
					if($R < 0) $R = 0;
					if($R > 255) $R = 255;
					
					$G = 255 - ($G_diff_min_dezimal * $Wert_stelle) ;
					if($G < 0) $G = 0;
					if($G > 255) $G = 255;
					
					$B = 255 - ($B_diff_min_dezimal * $Wert_stelle) ;
					if($B < 0) $B = 0;
					if($B > 255) $B = 255;
				}
				else
				{
					// positive Werte
					// echo "/+".$AGS_WERT_VERGL;
					$Wert_stelle = abs((($AGS_WERT_VERGL * 100) / $_SESSION['temp_vergleichswerte']['Rahmenbedingungen']['Max'])/ 100);
					// berechnen und Check auf evtl. Fehler
					$R = 255 - ($R_diff_max_dezimal * $Wert_stelle) ;
					if($R < 0) $R = 0;
					if($R > 255) $R = 255;
					
					$G = 255 - ($G_diff_max_dezimal * $Wert_stelle) ;
					if($G < 0) $G = 0;
					if($G > 255) $G = 255;
					
					$B = 255 - ($B_diff_max_dezimal * $Wert_stelle) ;
					if($B < 0) $B = 0;
					if($B > 255) $B = 255;
				}
			
				// Runden, in Hex-Wert umwandeln und bei einstelligen Werten eine 0 voranstellen und keine Negativen Werte zulassen <= nicht möglich!!!
				if(strlen($R_hex = dechex(round(abs($R),0))) < 2) $R_hex = '0'.$R_hex;
				if(strlen($G_hex = dechex(round(abs($G),0))) < 2) $G_hex = '0'.$G_hex;
				if(strlen($B_hex = dechex(round(abs($B),0))) < 2) $B_hex = '0'.$B_hex;
				$Fuellung_Obj_Farbe = "#".$R_hex.$G_hex.$B_hex;
			
			

					
				// für Effekte in Bezug auf Legende (hier Prozentangabe im Wertebereich ohne Kommastellen statt Farbcode)
				$AGS_mit_Farbcode[$ags] = "v_".round(((($AGS_mit_Werten[$ags] - $i_min) / $Wertebereich)*100),0);
					
				// Fehlerhafte Werte
				if($AGS_mit_Fehlern[$ags])
				{
						$AGS_mit_Werten[$ags] = $_SESSION['Dokument']['Fuellung']['Fehlercodes'][$AGS_mit_Fehlern[$ags]]['FEHLER_NAME'];
						// $Fuellung_Obj_Farbe = $_SESSION['Dokument']['Fuellung']['LeerFarbe'];
						$Fuellung_Obj_Farbe = "url(#errschraff".$AGS_mit_Fehlern[$ags].")";
						// für Effekte in Bezug auf Legende
						$AGS_mit_Farbcode[$ags] = "f_".$AGS_mit_Fehlern[$ags]; 
						// Erfassung ob Fehlerwerte vorgekommen sind
						$Fehlerwerte_vorhanden_Array[$AGS_mit_Fehlern[$ags]] = $AGS_mit_Fehlern[$ags]; // Array mit Fehlernummer x setzen = Fehler in Karte vorhanden						
				}
						
				// Leerwerte füllen
				if(!$AGS_mit_Werten[$ags] and $AGS_mit_Werten[$ags]!='0' and !$AGS_mit_Fehlern[$ags])
				{
						$AGS_mit_Werten[$ags] = $Leer_Info;
						//$Fuellung_Obj_Farbe = $_SESSION['Dokument']['Fuellung']['LeerFarbe'];
						$Fuellung_Obj_Farbe = "url(#leerschraff)";
						// für Effekte in Bezug auf Legende
						$AGS_mit_Farbcode[$ags] = 'f_leer'; 
						// Erfassung ob leerwerte vorgekommen sind
						$Leerwerte_vorhanden = 1;
				}
			}





			// ------------------- SVG-Path-Objekt zusammensetzen ----------------------------			
			$Einheit_Anz = $_SESSION['Dokument']['Fuellung']['Indikator_Einheit'];
			if($AGS_mit_Werten[$ags] == $Leer_Info) $Einheit_Anz = ""; // Keine Einheit bei Leeren Objekten
			if($AGS_mit_Fehlern[$ags]) $Einheit_Anz = ""; // Keine Einheit bei Fehlerhaften Objekten
			
			// Wert für Objektinfo ermitteln und formatieren
			// 1000000000 nur abziehen, wenn wirklich Wert hinterlegt ist
			// => Inhalt checken und evtl. 1000000000 abziehen!
			if(!$AGS_mit_Fehlern[$ags] and $AGS_mit_Werten[$ags] != $Leer_Info)
			{
				$wert = number_format($AGS_mit_Werten[$ags]-1000000000,$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.');
			}
			else
			{
				$wert = $AGS_mit_Werten[$ags];
			}
			
			
			
			// Leere Akt. abfangen => Probleme bei JScript Aufruf (muss Wert enthalten)
			if($AGS_mit_Aktualitaet_Jahr_Mittel[$ags])
			{
				$MittlAkt_myinit = $AGS_mit_Aktualitaet_Jahr_Mittel[$ags];
			}
			else
			{
				$MittlAkt_myinit = $_SESSION['Dokument']['Jahr_Anzeige'];
			}
			
			// Mouseover
			$JS_Events = ' onmouseover="myinit(';
			$JS_Events = $JS_Events."'e_".$ags."','#".$_SESSION['Dokument']['Strichfarbe_MouseOver']."','1','".$Obj_Info_Name_z1."','".$Obj_Info_Name_z2."','"
			.$wert." ".$Einheit_Anz."','"
			.$MittlAkt_myinit."','"
			.$AGS_mit_Farbcode[$ags]."','#000000','".$AGS_mit_Hinweisen[$ags]."')"; 
			// Mouseout
			$JS_Events = $JS_Events.'" onmouseout="myinit(';
			$JS_Events = $JS_Events."'e_".$ags."','#".$_SESSION['Dokument']['Strichfarbe']."','0','','','','','".$AGS_mit_Farbcode[$ags]."','none','')"; 
											// die Füllung $Fuellung_Obj_Farbe verwenden und JS-Funktion im Head anpassen
			$JS_Events = $JS_Events.'" ';
			
			// Onclick (bei Rasterdarstellung hier auf Zusatzebene (Gemeinde) umleiten)
			if($_SESSION['Dokument']['Raumgliederung'] == "rst" or $_SESSION['Dokument']['Raumgliederung'] == "r05" or $_SESSION['Dokument']['Raumgliederung'] == "r10")
			{
				// für Raster: Ziel auf Gemeinde-Zusatzebene
				$JS_Events = $JS_Events.' onclick="einblenden(';
				$JS_Events = $JS_Events."'Label_".$Obj_Raster_Gemeindename."')";
				$JS_Events = $JS_Events.'" ';
			}
			else
			{
				// Normallfall = selbe Ebene
				$JS_Events = $JS_Events.' onclick="einblenden(';
				$JS_Events = $JS_Events."'Label_".$ags."')";
				$JS_Events = $JS_Events.'" ';
			}
			
			// JScript bei leeren Elementen abschalten
			//if($AGS_mit_Werten[$ags] == "keine Informationen") $JS_Events = '';
			
			// Einzelne Arrays jedes mal den gleichen Zähler und Zählen somit gemeinsam hoch
			// Pfad
			/* $Ausgabe[$DatenSet['NAME']][] = '<g id="'.$ags
											.'" '.$JS_Events
											.'stroke-width="'.$_SESSION['Dokument']['Strichstaerke']
											.'" stroke="#'.$_SESSION['Dokument']['Strichfarbe'] 
											.'"><path stroke-linejoin="round"  id="path_'.$ags.'" fill="'.$Fuellung_Obj_Farbe.'" d="'.$PG_Zeile['geometrie'].'" ></path></g>';  */
								
			if($_SESSION['Dokument']['Strichstaerke'])
			{
				$stroke_width = ' stroke-width="'.$_SESSION['Dokument']['Strichstaerke'].'" ';
				$stroke = ' stroke="#'.$_SESSION['Dokument']['Strichfarbe'].'" ';
				$fill = ' fill="'.$Fuellung_Obj_Farbe.'" ';
			}
			else
			{
				$stroke_width = '';
				$stroke = ' stroke="none" ';
				$fill = ' fill="'.$Fuellung_Obj_Farbe.'" ';
			}
			
			
			$Ausgabe[$DatenSet['NAME']][] = '<g id="e_'.$ags
											.'" '.$JS_Events
											.$stroke
											.$stroke_width 
											.$fill.' ><path stroke-linejoin="round"  id="path_e_'.$ags.'"  d="'.$PG_Zeile['geometrie'].'" ></path></g>'; 
			
			
			
			
			
			
			
			// Beschriftung: wird weiter unten (in SVG-Stream) zusammengefügt (an transformierte Koordinaten), sonst wird Textlänge mit gekürzt
			$Ausgabe_Beschriftung_X[$DatenSet['NAME']][] = $X_Centroid;
			$Ausgabe_Beschriftung_Y[$DatenSet['NAME']][] = $Y_Centroid;
			$Ausgabe_Beschriftung_Text[$DatenSet['NAME']][] = $Obj_Info_Name;
			$Ausgabe_Beschriftung_AGS[$DatenSet['NAME']][] = $ags;	
			
			// Bei Vergleichskarten vorerst keine Aktualität ausgeben
			if(!$Vergleichskarte)
			{
				// Aktualitätslayer füllen (Legende)
				$Aktualitaetslayer_Legende = $Aktualitaetslayer_Legende.'<use id="aktualitaet_legende_e_'.$ags.'" xlink:href="#path_e_'
									.$ags.'" fill="#'.$FCA_Jahr[$AGS_mit_Aktualitaet_Differenz[$ags]].'" stroke-width="0" ></use>';
				// Aktualitätslayer füllen (Karte)
				$Aktualitaetslayer_Karte = $Aktualitaetslayer_Karte.'<use id="aktualitaet__karte_e_'.$ags.'" xlink:href="#path_e_'
									.$ags.'" fill="#'.$FCA_Jahr[$AGS_mit_Aktualitaet_Differenz[$ags]].'" stroke-width="0" ></use>';
				// stroke-width="'.$strwidth = ($_SESSION['Dokument']['Strichstaerke_Event']*2).'"
				
				
				
				//Max- Min-Aktualität für Legende feststellen
				if(!$Akt_min or $Akt_min > $AGS_mit_Aktualitaet_Differenz[$ags]) $Akt_min = $AGS_mit_Aktualitaet_Differenz[$ags];
				if(!$Akt_max or $Akt_max < $AGS_mit_Aktualitaet_Differenz[$ags]) $Akt_max = $AGS_mit_Aktualitaet_Differenz[$ags];
			}	
		
			// Layer für Markierungen aus Vergleichstabelle heraus
			if($Vergleichskarte) // --- Schalter für Vergleichskartenanzeige ---
			{
				$Markierungslayer_Vergleichskarte = $Markierungslayer_Vergleichskarte.'<use id="vergleich_markierung_'.$ags.'" xlink:href="#path_e_'
								.$ags.'" fill="none" stroke-width="0" ></use>';
			}
				
		}		
	}
}




// Hintergrund einbinden (momentan fest die Bundesländer als Länder)
//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------

/* Simplify durch Vorberechnung mittels ArcMap/Simplify() ersetzt
$SQL_PostGIS = "SELECT gen,ags,AsSvg(Simplify(ST_Transform((the_geom),".$_SESSION['Dokument']['PG_SRID']."),'".$_SESSION['Dokument']['Praezision']."'),1,0) AS geometrie FROM vg250_bld_".$_SESSION['Dokument']['Jahr_Geometrietabelle'];
$ERGEBNIS_PGSQL_VEKTOREN =  pg_query($Verbindung_PostgreSQL,$SQL_PostGIS);  // performanterer Weg!!! */

$SQL_PostGIS = "SELECT ags,AsSvg(ST_Transform(simplify(the_geom,".$_SESSION['Dokument']['PG_Simplify']."),".$_SESSION['Dokument']['PG_SRID']."),".$_SESSION['Dokument']['PG_rel_abs'].",".$_SESSION['Dokument']['PG_SVG_Genauigkeit'].") AS geometrie FROM vg250_bld_".$_SESSION['Dokument']['Jahr_Geometrietabelle']."_".$_SESSION['Dokument']['Raumgliederung_Zusatzkennung'];
$ERGEBNIS_PGSQL_VEKTOREN =  pg_query($Verbindung_PostgreSQL,$SQL_PostGIS);  // performanterer Weg!!!
// $_SESSION['ERROR'] = $SQL_PostGIS;

$i_hg=0;
// gefundene Datensätze abarbeiten
while($PG_Zeile = @pg_fetch_assoc($ERGEBNIS_PGSQL_VEKTOREN,$i_hg))
{				
		
		
		// Definition hier, falls nicht anders übergeben
		if(!$_SESSION['Dokument']['HG_Fuellung']) $_SESSION['Dokument']['HG_Fuellung']='DDDDDD'; 
		if(!$_SESSION['Dokument']['HG_UmrandFarbe']) $_SESSION['Dokument']['HG_UmrandFarbe']='CCCCCC'; 
		
		
		$JS_Events = '';
		$Ausgabe['Hintergrund'][] = '<path stroke-linejoin="round"  pointer-events="none" stroke-width="'.$SStHG = $_SESSION['Dokument']['Strichstaerke_HG']
									.'" stroke="#'.$_SESSION['Dokument']['HG_UmrandFarbe']
									.'" fill="#'.$_SESSION['Dokument']['HG_Fuellung']
									.'" d="'.$PG_Zeile['geometrie'].'"></path>'; 
		$i_hg++;
}





// ----------------------------- Funktionen für Zusatzebenen-Anzeige -------------------------------------


// Funktion wird an anderer Stelle mehrfach abgearbeitet um Mehrfachauswahlen zu berücksichtigen und mehrere Ebenen zu generieren
function zusatzebenen($Ebene,$AWerte_SQL,$zusatz_global)
{
	include("includes_classes/verbindung_mysqli.php");
	
	// für Verwaltungsgrenzen (einfach)
	if($Ebene == "bld" or $Ebene == "krs" or $Ebene == "gem" or $Ebene == "ror")
	{
		// Zum testen: $_SESSION['test'][] = 
		$SQL_PostGIS_z = "SELECT 
							gen,
							AsSvg(ST_Transform(simplify(the_geom,".$_SESSION['Dokument']['PG_Simplify']."),".$_SESSION['Dokument']['PG_SRID']."),".$_SESSION['Dokument']['PG_rel_abs'].",".$_SESSION['Dokument']['PG_SVG_Genauigkeit'].") AS geometrie, 
							AsSvg(centroid(ST_Transform((the_geom),".$_SESSION['Dokument']['PG_SRID']."))) AS centroid,
							box2d(ST_Transform((the_geom),".$_SESSION['Dokument']['PG_SRID'].")) AS bbox
							FROM vg250_".$Ebene."_".$_SESSION['Dokument']['Jahr_Geometrietabelle']."_".$_SESSION['Dokument']['Raumgliederung_Zusatzkennung']
							." WHERE gid >= '0' ".$AWerte_SQL
							." GROUP BY vg250_".$Ebene."_".$_SESSION['Dokument']['Jahr_Geometrietabelle']."_".$_SESSION['Dokument']['Raumgliederung_Zusatzkennung'].".the_geom,gen";				
		$ERGEBNIS_PGSQL_VEKTOREN_z =  pg_query($Verbindung_PostgreSQL,$SQL_PostGIS_z); 
		
		// Variable leeren
		$zusatz = '';
		
		$i_zeb=0;
		// gefundene Datensätze abarbeiten
		while($PG_Zeile_z = @pg_fetch_assoc($ERGEBNIS_PGSQL_VEKTOREN_z))
		{
			 // Verhindern von Dopplungen
			 /* if(!$GLOBALS['Zusatz_schon_vorhanden'][$Ebene][$PG_Zeile_z['gen']]) 
			 { */
			 
			
			 // Onclick (bei Rasterdarstellung hier nicht nutzen und auf Zusatzebene umleiten)
			// if($_SESSION['Dokument']['Raumgliederung'] == "rst" and $_SESSION['Dokument']['Zusatzebene_aktiv'] == $Ebene)
			/* if($_SESSION['Dokument']['Zusatzebene_aktiv'] == $Ebene)
			{
				$JS_Events_z = ' pointer-events="visible" onclick="einblenden(';
				$JS_Events_z = $JS_Events_z."'Label_".$PG_Zeile_z['gen']."')";
				$JS_Events_z = $JS_Events_z.'" ';
				// $JS_Events_z = "";
			} */
			$JS_Events_z = ' pointer-events="none" ';
																		  
			$zusatz = $zusatz.'<path stroke-linejoin="round"  id="Zusatz_'.$Ebene.'_'.$PG_Zeile_z['gen'].'" '.$JS_Events_z.' fill="none" d="'.$PG_Zeile_z['geometrie'].'" ></path>';
			
			// -------------- Schriftplatzierung ------------------------
			// Punkt für Objekt-Zentrum (zur Schriftplatzierg. usw.)
			$Centroid = $PG_Zeile_z['centroid']; 
			$unwichtig = strtok($Centroid,'"');
			$X_Centroid = strtok('"'); // SVG-X-Koordinate des Mittelpunktes
			$unwichtig = strtok('"');
			$Y_Centroid = strtok('"');// SVG-Y-Koordinate des Mittelpunktes
			
			// Bounding Box Ausdehnung erfassen
			$BBox_0 = $PG_Zeile_z['bbox'];
			$BBox_1 = strtok($BBox_0,'(');
			$BBox_1 = strtok('(');
			$BBox_2 = strtok($BBox_1,')');
			$BBox_X1 = strtok($BBox_2,' ');
			$BBox_3 = strtok(' ');
			$BBox_Y1 = strtok($BBox_3,',');
			
			$bbox=$BBox_Y1;
			
			// leichte Verschiebung des Mittelpunktes um 1/x Objekthöhe nach oben
			$X_Text = $X_Centroid - abs(0.4*(abs($X_Centroid) - abs($BBox_X1)));
			//$X_Centroid = $X_Text;
			
			$Y_Text = $Y_Centroid - abs(0.2*(abs($Y_Centroid) - abs($BBox_Y1)));
			//$Y_Centroid = $Y_Text;
			
			 
			// Beschriftung: wird weiter unten (in SVG-Stream) zusammengefügt (an transformierte Koordinaten), sonst wird Textlänge mit gekürzt
			$GLOBALS['i_AusgBeschr_Count']++; // Nötig, da bei mehreren durchläufen die Werte wieder überschrieben würden, wenn nicht weitergezählt würde
			$GLOBALS['Ausgabe_Beschriftung'][$Ebene][$GLOBALS['i_AusgBeschr_Count']]['X'] = $X_Centroid;
			$GLOBALS['Ausgabe_Beschriftung'][$Ebene][$GLOBALS['i_AusgBeschr_Count']]['Y'] = $Y_Centroid;
			$GLOBALS['Ausgabe_Beschriftung'][$Ebene][$GLOBALS['i_AusgBeschr_Count']]['NAME'] = $PG_Zeile_z['gen'];
			
			 
			 
			
			 /* }
			 $GLOBALS['Zusatz_schon_vorhanden'][$Ebene][$PG_Zeile_z['gen']] = 1; */	
			 $i_zeb++;
		}
	}
	
	
	// Für Gewässerdarstellg 
	if($Ebene == "gew" and !$GLOBALS['zusatz_gew_vorhanden'])
	{
		// nur eine Ausführung notwendig... da Globale 3D-Box abgefragt wird
		$GLOBALS['zusatz_gew_vorhanden'] = 1;
		
		// Variable leeren
		$zusatz = '';
				
		// Konturfarbe vorläufig hier definiert
		if(!$_SESSION['Dokument']['Strichfarbe_GEW']) $_SESSION['Dokument']['Strichfarbe_GEW'] = "000099";
		
		// Für Kleinmaßstäbe	
		if($_SESSION['Dokument']['zusatz_gew_typ'] == "klein")
		{
			// Datenmenge zu gering, als dass sich Intersect lohnen würde => normale Abfrage des gesamten Datenbestands	
			$SQL_PostGIS_z = "SELECT gn As gewaesser_name, AsSvg(ST_Transform(simplify(the_geom,".$_SESSION['Dokument']['PG_Simplify']."),".$_SESSION['Dokument']['PG_SRID']."),"
								.$_SESSION['Dokument']['PG_rel_abs'].",".$_SESSION['Dokument']['PG_SVG_Genauigkeit']
								.") AS geometrie FROM gew_kleinmasstaeblich";
			$ERGEBNIS_PGSQL_VEKTOREN_z =  pg_query($Verbindung_PostgreSQL,$SQL_PostGIS_z); 
			
			// gefundene Datensätze abarbeiten
			$idsz=0;
			while($PG_Zeile_z = @pg_fetch_assoc($ERGEBNIS_PGSQL_VEKTOREN_z))
			{
				 
				 $zusatz = $zusatz.'<path stroke-linejoin="round" stroke="#'.$_SESSION['Dokument']['Strichfarbe_GEW'].'" stroke-width="'.$gew_width_a = ($_SESSION['Dokument']['Strichstaerke_zusatz']*3)
				 .'" id="Zusatz_'.$Ebene.'_'.utf8_encode($PG_Zeile_z['gewaesser_name']).'_'.$idsz.'" d="'.$PG_Zeile_z['geometrie'].'" ></path>';
				 $idsz++;
			}
	
		}
			
		
		// Für Gewässerdarstellg (Verschneidungsoperation in DB nötig)
		if($_SESSION['Dokument']['zusatz_gew_typ'] == "gross")
		{
			
			// Methode mittels Auswahl aus der 3D-Box der Karten-Eckdaten
			$SQL_PostGIS_z = "SELECT gn As gewaesser_name,AsSvg(ST_Transform(simplify(the_geom,".$_SESSION['Dokument']['PG_Simplify']."),".$_SESSION['Dokument']['PG_SRID']."),"
								.$_SESSION['Dokument']['PG_rel_abs'].",".$_SESSION['Dokument']['PG_SVG_Genauigkeit']
								.") AS geometrie FROM gew_grossmasstaeblich WHERE ST_Transform((the_geom),".$_SESSION['Dokument']['PG_SRID'].")
         	&& SetSRID( 'BOX3D( ".$_SESSION['Dokument']['X_min_global']." ".$_SESSION['Dokument']['Y_max_global'].",".$_SESSION['Dokument']['X_max_global']." ".$_SESSION['Dokument']['Y_min_global']." )'::box3d,".$_SESSION['Dokument']['PG_SRID']." )";
								
			$ERGEBNIS_PGSQL_VEKTOREN_z =  pg_query($Verbindung_PostgreSQL,$SQL_PostGIS_z); 
			
			// gefundene Datensätze abarbeiten
			$idsz=0;
			while($PG_Zeile_z = @pg_fetch_assoc($ERGEBNIS_PGSQL_VEKTOREN_z))
			{
				 /* echo  "ok"; */
				 $zusatz = $zusatz.'<path stroke-linejoin="round" stroke="#'.$_SESSION['Dokument']['Strichfarbe_GEW'].'" stroke-width="'.$gew_width_a = ($_SESSION['Dokument']['Strichstaerke_zusatz']*2)
				 .'" id="Zusatz_'.$Ebene.'_'.utf8_encode($PG_Zeile_z['gewaesser_name']).'_'.$idsz.'" d="'.$PG_Zeile_z['geometrie'].'" ></path>';
				 $idsz++;
			}
	
		}
	}
	
	
	// Für BABs (Verschneidungsoperation in DB nötig)
	if($Ebene == "bab" and !$GLOBALS['zusatz_bab_vorhanden'])
	{
		
		// nur eine Ausführung notwendig... da Globale 3D-Box abgefragt wird
		$GLOBALS['zusatz_bab_vorhanden'] = 1;
		
		// Konturfarbe abgreifen aber nich das Server-Array verändern
		if(!$_SESSION['Dokument']['Strichfarbe_BAB'])  $_SESSION['Dokument']['Strichfarbe_BAB'] = "EEEE00";
				 
		// Variable leeren
		$zusatz = '';

		
		// Kleinmaßstäbige Karten
		if($_SESSION['Dokument']['Raumebene_NAME_Auswertung'] == "Deutschland")
		{
			// auf Bundesebene etwas stärker generalisieren und unbedingt ein DISSOLVE durchführen
			// $Simplify_BAB = $_SESSION['Dokument']['PG_Simplify_zus'] * 4;
			// Datenmenge zu gering, als dass sich Intersect lohnen würde => normale Abfrage des gesamten Datenbestands	
			$SQL_PostGIS_z = "SELECT kn As BAB_name, AsSvg(ST_Transform(simplify(the_geom,".$_SESSION['Dokument']['PG_Simplify']."),".$_SESSION['Dokument']['PG_SRID']."),"
								.$_SESSION['Dokument']['PG_rel_abs'].",".$_SESSION['Dokument']['PG_SVG_Genauigkeit']
								.") AS geometrie FROM bab_kleinmasstaeblich";
			$ERGEBNIS_PGSQL_VEKTOREN_z =  pg_query($Verbindung_PostgreSQL,$SQL_PostGIS_z); 
			
			$idsz=0;
			while($PG_Zeile_z = @pg_fetch_assoc($ERGEBNIS_PGSQL_VEKTOREN_z))
			{
				 
				 $zusatz = $zusatz.'<path stroke-linejoin="round"  id="Zusatz_'.$Ebene.'_'.utf8_encode($PG_Zeile_z['BAB_name']).'_'.$idsz.'" d="'.$PG_Zeile_z['geometrie']
				 .'" stroke="#'.$_SESSION['Dokument']['Strichfarbe_BAB'].'" stroke-width="'.$bab_width_a = ($_SESSION['Dokument']['Strichstaerke_zusatz']*4).'" style="stroke-linecap: round;" ></path>'; 
				 $idsz++;
			} 
			
		}
		else	
		// Großmaßstäbige Karten
		{
			
			// Methode mittels Auswahl aus der 3D-Box der Karten-Eckdaten
			$SQL_PostGIS_z = "SELECT kn As BAB_name, AsSvg(ST_Transform(simplify(the_geom,".$_SESSION['Dokument']['PG_Simplify']."),".$_SESSION['Dokument']['PG_SRID']."),"
								.$_SESSION['Dokument']['PG_rel_abs'].",".$_SESSION['Dokument']['PG_SVG_Genauigkeit']
								.") AS geometrie  FROM bab_grossmasstaeblich WHERE ST_Transform((the_geom),".$_SESSION['Dokument']['PG_SRID'].")
         	&& SetSRID( 'BOX3D( ".$_SESSION['Dokument']['X_min_global']." ".$_SESSION['Dokument']['Y_max_global'].",".$_SESSION['Dokument']['X_max_global']." ".$_SESSION['Dokument']['Y_min_global']." )'::box3d,".$_SESSION['Dokument']['PG_SRID']." )";
								
			$ERGEBNIS_PGSQL_VEKTOREN_z =  pg_query($Verbindung_PostgreSQL,$SQL_PostGIS_z); 
			
			// gefundene Datensätze abarbeiten
			$idsz=0;
			while($PG_Zeile_z = @pg_fetch_assoc($ERGEBNIS_PGSQL_VEKTOREN_z))
			{
				 
				 // Konturfarbe prüfen und falls nicht gesetzt, dann richtige BAB-Signatur zeichnen ($_SESSION['Dokument']['Strichfarbe_BAB_Signatur'] vordefiniert auf '1' bei Viewerstart)
				 if($_SESSION['Dokument']['Strichfarbe_BAB_Signatur'])
				 {
					// Anzeige als Signatur
					 // Farbe für Kontur hier immer nochmals ermitteln
					if(strlen($BAB_R = dechex(round(hexdec(substr($_SESSION['Dokument']['Strichfarbe_BAB'],0,2))/2))) < 2) $BAB_R = $BAB_R.'0'; 
					if(strlen($BAB_G = dechex(round(hexdec(substr($_SESSION['Dokument']['Strichfarbe_BAB'],2,2))/2))) < 2) $BAB_G = $BAB_G.'0'; 
					if(strlen($BAB_B = dechex(round(hexdec(substr($_SESSION['Dokument']['Strichfarbe_BAB'],4,2))/2))) < 2) $BAB_B = $BAB_B.'0';  
					 $_SESSION['Dokument']['Strichfarbe_BAB_Kontur'] = $BAB_R.$BAB_G.$BAB_B;
					 
					 $zusatz_bab_grund = $zusatz_bab_grund.'<path stroke-linejoin="round"  id="Zusatz_'.$Ebene.'_'.utf8_encode($PG_Zeile_z['BAB_name']).'_'.$idsz.'_a" d="'.$PG_Zeile_z['geometrie']
					 .'" stroke="#'.$_SESSION['Dokument']['Strichfarbe_BAB_Kontur'].'" stroke-width="'.$bab_width_a = ($_SESSION['Dokument']['Strichstaerke_zusatz']*6).'" style="stroke-linecap: butt;" ></path>';
					 
					 $zusatz_bab_decker = $zusatz_bab_decker.'<path stroke-linejoin="round"  id="Zusatz_'.$Ebene.'_'.utf8_encode($PG_Zeile_z['BAB_name']).'_'.$idsz.'_b" d="'.$PG_Zeile_z['geometrie']
					 .'" stroke="#'.$_SESSION['Dokument']['Strichfarbe_BAB'].'" stroke-width="'.$bab_width_b = ($_SESSION['Dokument']['Strichstaerke_zusatz']*3).'" style="stroke-linecap: round;" ></path>';
					 $idsz++;
				 }
				 else
				 {
					   $zusatz = $zusatz.'<path stroke-linejoin="round"  id="Zusatz_'.$Ebene.'_'.utf8_encode($PG_Zeile_z['BAB_name']).'_'.$idsz.'" d="'.$PG_Zeile_z['geometrie']
					   .'" stroke="#'.$_SESSION['Dokument']['Strichfarbe_BAB'].'" stroke-width="'.$bab_width_a = ($_SESSION['Dokument']['Strichstaerke_zusatz']*5).'" style="stroke-linecap: round;" ></path>'; 

					
				 }
			}
			// Zusammenführen der Ebenen bei echter BAB-Signatur
			if($_SESSION['Dokument']['Strichfarbe_BAB_Signatur']) $zusatz =  $zusatz_bab_grund.$zusatz_bab_decker;
		}
	}
	
	
	
	
	// Für DB-Fernverkehr (Verschneidungsoperation in DB nötig)
	if($Ebene == "db" and !$GLOBALS['zusatz_db_vorhanden'])
	{
		
		// nur eine Ausführung notwendig... da Globale 3D-Box abgefragt wird
		$GLOBALS['zusatz_db_vorhanden'] = 1;
		
		// Konturfarbe abgreifen aber nich das Server-Array verändern
		if(!$_SESSION['Dokument']['Strichfarbe_db'])  $_SESSION['Dokument']['Strichfarbe_db'] = "555555";
				 
		// Variable leeren
		$zusatz = '';

		
		// Kleinmaßstäbige Karten
		if($_SESSION['Dokument']['Raumebene_NAME_Auswertung'] == "Deutschland")
		{
			// auf Bundesebene etwas stärker generalisieren und unbedingt ein DISSOLVE durchführen
			$Simplify_DB = 0;
			// Datenmenge zu gering, als dass sich Intersect lohnen würde => normale Abfrage des gesamten Datenbestands	
			$SQL_PostGIS_z = "SELECT gid As db_id, AsSvg(ST_Transform(simplify(the_geom,".$_SESSION['Dokument']['PG_Simplify']."),".$_SESSION['Dokument']['PG_SRID']."),"
								.$_SESSION['Dokument']['PG_rel_abs'].",".$_SESSION['Dokument']['PG_SVG_Genauigkeit']
								.") AS geometrie FROM db_fernverkehr_kleinmassstaeblich";
			$ERGEBNIS_PGSQL_VEKTOREN_z =  pg_query($Verbindung_PostgreSQL,$SQL_PostGIS_z); 
			
			$idsz=0;
			while($PG_Zeile_z = @pg_fetch_assoc($ERGEBNIS_PGSQL_VEKTOREN_z))
			{
				 
				 $zusatz = $zusatz.'<path stroke-linejoin="round"  id="Zusatz_'.$Ebene.'_'.utf8_encode($PG_Zeile_z['db_id']).'_'.$idsz.'" d="'.$PG_Zeile_z['geometrie']
				 .'" stroke="#'.$_SESSION['Dokument']['Strichfarbe_db'].'" stroke-width="'.$db_width = ($_SESSION['Dokument']['Strichstaerke_zusatz']*4).'" style="stroke-linecap: round;" ></path>'; 
				 $idsz++;
			} 
			
		}
		else	
		// Großmaßstäbige Karten
		{
			// Methode mittels Auswahl aus der 3D-Box der Karten-Eckdaten
			$SQL_PostGIS_z = "SELECT gid As db_id, AsSvg(ST_Transform(simplify(the_geom,".$_SESSION['Dokument']['PG_Simplify']."),".$_SESSION['Dokument']['PG_SRID']."),"
								.$_SESSION['Dokument']['PG_rel_abs'].",".$_SESSION['Dokument']['PG_SVG_Genauigkeit']
								.") AS geometrie  FROM db_fernverkehr_grossmassstaeblich WHERE ST_Transform((the_geom),".$_SESSION['Dokument']['PG_SRID'].")
         	&& SetSRID( 'BOX3D( ".$_SESSION['Dokument']['X_min_global']." ".$_SESSION['Dokument']['Y_max_global'].",".$_SESSION['Dokument']['X_max_global']." ".$_SESSION['Dokument']['Y_min_global']." )'::box3d,".$_SESSION['Dokument']['PG_SRID']." )";
								
			$ERGEBNIS_PGSQL_VEKTOREN_z =  pg_query($Verbindung_PostgreSQL,$SQL_PostGIS_z); 
			
			$idsz=0;
			while($PG_Zeile_z = @pg_fetch_assoc($ERGEBNIS_PGSQL_VEKTOREN_z))
			{
				 
				 $zusatz = $zusatz.'<path stroke-linejoin="round"  id="Zusatz_'.$Ebene.'_'.utf8_encode($PG_Zeile_z['db_id']).'_'.$idsz.'" d="'.$PG_Zeile_z['geometrie']
				 .'" stroke="#'.$_SESSION['Dokument']['Strichfarbe_db'].'" stroke-width="'.$db_width = ($_SESSION['Dokument']['Strichstaerke_zusatz']*4).'" style="stroke-linecap: round;" ></path>'; 
				 $idsz++;
			} 
		}
	}

	return $zusatz; // zusatz_global ???
}



// Zeichnen wird im SVG-Ausgabeteil erst aufgerufen
function zusatz_zeichnen($ZEbene_Bezeichnung,$Ebeneninhalt,$s,$X_min,$Y_max,$Stroke_width,$Color)
{
	return $ZE_Ausg = ' <g transform="matrix('.$s.' 0 0 '.$s.' '.$X_min.' '.$Y_max.')" id="zusatz_'.$ZEbene_Bezeichnung.'" fill="none" pointer-events="none" stroke-width="'.$Stroke_width.'" stroke="'.$Color.'">
			<desc>Zusatz-Inhalt '.utf8_encode($ZEbene_Bezeichnung).'</desc>'.
			$Ebeneninhalt
	.'</g>';
}

// ENDE Funktionen für Zusatzebenen-Anzeige
// ------------------------------ 







$Zwischenzeit_Einzelwerte_3 = date('i:s');

// -----------------------------------------------------------------------------------------------------
//										SVG - STREAM
// -----------------------------------------------------------------------------------------------------


// Schreibens in das Dokument <= optimiert durch Schreibvorgang der Geometrien direkt aus einem Array (ohne Zusammensetzungen) in das Dokument
// > Reduktion der benötigten Zeit auf 1/10  !!!!!!!!!!!!!!!!
//--------------------------------------------------------------------------------------------------------------------------------------------



// im unlegend-Betrieb nicht ausgeben
if(!$_SESSION['Dokument']['unlegend'])
{
	$Dok_width = $XD;
	$Dok_height = $YD_gesamt;
}
else
{
	$Dok_width = $_SESSION['Dokument']['groesse_X']+$_SESSION['Dokument']['Rand_L']+$_SESSION['Dokument']['Rand_R'];
	$Dok_height = $_SESSION['Dokument']['groesse_Y']+$_SESSION['Dokument']['Rand_O']+$_SESSION['Dokument']['Rand_U'];		
}



// Kopf der SVG-Datei speichern
echo  '<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN"  "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">
<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:ev="http://www.w3.org/2001/xml-events" version="1.1" baseProfile="full" 
viewBox="0 0 '.$Dok_width.' '.$Dok_height.'" width="'.$Dok_width.'px" height="'.$Dok_height.'px">';

// JavaScript Funktionen für Interaktivität

echo  "<defs>
	<script type=\"text/javascript\">
		<![CDATA[
			if(!window){ window = this; }
			if(!document){ window.document = evt.target.ownerDocument; }
			var ElementID;
			var ElementID_2;
			var Farbe;
			
			var xlinkNS = 'http://www.w3.org/1999/xlink';
			var uebergabe;
			
			
			
			function myinit(ElementID,Farbe,Opacity,ObjInfofeld_Name_z1,ObjInfofeld_Name_z2,KriteriumWert,Grundaktualitaet,LegFarbcode,BorderFarbcode,Hinweis)
			{
				document.getElementById('marker_geom').setAttributeNS(xlinkNS,'xlink:href','#path_' + ElementID);
				document.getElementById('marker_geom').setAttributeNS(null,'stroke',Farbe);
				document.getElementById('marker_geom').setAttributeNS(null,'opacity',Opacity);
				document.getElementById('ObjInfofeld').setAttributeNS(null,'opacity',Opacity);
				document.getElementById('ObjInfofeld_Name_z1').firstChild.data = ObjInfofeld_Name_z1;
				document.getElementById('ObjInfofeld_Name_z2').firstChild.data = ObjInfofeld_Name_z2;
				document.getElementById('ObjInfofeld_Wert').firstChild.data = KriteriumWert;
				document.getElementById('ObjInfofeld_Aktualitaet').firstChild.data = Grundaktualitaet;
				document.getElementById('ObjInfofeld_Hinweis').firstChild.data = Hinweis;
				document.getElementById(LegFarbcode).setAttributeNS(null,'stroke',BorderFarbcode);
				
			}
			
			function einblenden(ElementID)
			{
				if(document.getElementById(ElementID).getAttributeNS(null,'display') == 'none')
				{
					document.getElementById(ElementID).setAttributeNS(null,'display','inline');
					
					uebergabe = 'svg_agsname_anzeigen.php?visible=1&ags='+ElementID;
					document.getElementById('verstecktegrafik').setAttributeNS(xlinkNS,'xlink:href',uebergabe);

				}
				else
				{
					document.getElementById(ElementID).setAttributeNS(null,'display','none');
					
					uebergabe = 'svg_agsname_anzeigen.php?visible=0&ags='+ElementID;
					document.getElementById('verstecktegrafik').setAttributeNS(xlinkNS,'xlink:href',uebergabe);
					
				}
			}
			
			function aktualitaet_einblenden(ElementID)
			{
				if(document.getElementById(ElementID).getAttributeNS(null,'display') == 'none')
				{
					
					document.getElementById(ElementID).setAttributeNS(null,'display','inline');
					
					document.getElementById('akt_event').setAttribute('onmouseout','none');
					
					document.getElementById('ObjInfofeld_Wert').setAttributeNS(null,'display','none');
				}
				else
				{
					document.getElementById(ElementID).setAttributeNS(null,'display','none');
					
					document.getElementById('akt_event').setAttribute('onmouseout',
							\"document.getElementById(\'aktklick\').setAttributeNS(null,\'display\',\'none\'); document.getElementById(\'akt_decker\').setAttributeNS(null,\'display\',\'none\');\")
					
					document.getElementById('ObjInfofeld_Wert').setAttributeNS(null,'display','inline');
				}
			}
			
			
			
		]]>
	</script>";
	
	
	
	
// ------ Testausgabe --------------			
if($_SESSION['Dokument']['ViewBerechtigung'] == '0')
{
	echo $Debugging;
}
// ------ Testausgabe ende --------------		
	
	
	
	
// Pattern für Schraffur leerer Polygone, mit Anpassung an Karten-Skalierungsfaktor $s	
$pgroesse_Ausgangswert = 5;
$pattern_groesse = $pgroesse_Ausgangswert*(1/$s);
$pattern_Strichstaerke = 100;
echo  '	<pattern id="leerschraff" patternUnits="userSpaceOnUse" viewBox="0 0 1000 1000" width="'.$pattern_groesse.'" height="'.$pattern_groesse.'">
      		<desc>'.$s.'</desc>
			<rect x="0" y="0" width="1000" height="1000" fill="white" />
			<line x1="0" y1="0" x2="1000" y2="1000" stroke-width="'.$pattern_Strichstaerke.'" stroke="#'.$_SESSION['Dokument']['Fuellung']['LeerFarbe'].'" />
			<line x1="-1000" y1="0" x2="1000" y2="2000" stroke-width="'.$pattern_Strichstaerke.'" stroke="#'.$_SESSION['Dokument']['Fuellung']['LeerFarbe'].'" />
			<line x1="0" y1="-1000" x2="2000" y2="1000" stroke-width="'.$pattern_Strichstaerke.'" stroke="#'.$_SESSION['Dokument']['Fuellung']['LeerFarbe'].'" />
    	</pattern>'; 

// Pattern für Schraffur leerer Polygone in der Legende (feste Größe)
$pgroesseleg = $pgroesse_Ausgangswert;
$strichstpattleg = $pattern_Strichstaerke/100;
echo  '	<pattern id="leerschraff_legende" patternUnits="userSpaceOnUse" viewBox="0 0 10 10" width="'.$pgroesseleg.'" height="'.$pgroesseleg.'">
      		<desc>'.$s.'</desc>
			<rect x="0" y="0" width="10" height="10" fill="white" />
			<line x1="0" y1="0" x2="10" y2="10" stroke-width="'.$strichstpattleg.'" stroke="#'.$_SESSION['Dokument']['Fuellung']['LeerFarbe'].'" />
			<line x1="-10" y1="0" x2="10" y2="20" stroke-width="'.$strichstpattleg.'" stroke="#'.$_SESSION['Dokument']['Fuellung']['LeerFarbe'].'" />
			<line x1="0" y1="-10" x2="20" y2="10" stroke-width="'.$strichstpattleg.'" stroke="#'.$_SESSION['Dokument']['Fuellung']['LeerFarbe'].'" />
    	</pattern>'; 



// Schraffur bei Fehlern:
$pgroesse_Ausgangswert = 5;
$pattern_groesse = $pgroesse_Ausgangswert*(1/$s);
$pattern_Strichstaerke = 100;

// Fehlercodes, die in der Karte vorgekommen sind abarbeiten und Patterns erstellen
if(is_array($Fehlerwerte_vorhanden_Array))
{
	foreach($Fehlerwerte_vorhanden_Array as $FCod)
	{
		echo  '	<pattern id="errschraff'.$_SESSION['Dokument']['Fuellung']['Fehlercodes'][$FCod]['FEHLERCODE'].'" patternUnits="userSpaceOnUse" viewBox="0 0 1000 1000" width="'.$pattern_groesse.'" height="'.$pattern_groesse.'">
					<desc>'.$s.'</desc>
					<rect x="0" y="0" width="1000" height="1000" fill="white" />
					<line x1="0" y1="0" x2="1000" y2="1000" stroke-width="'.$pattern_Strichstaerke.'" stroke="#'.$_SESSION['Dokument']['Fuellung']['Fehlercodes'][$FCod]['FEHLER_FARBCODE'].'" />
					<line x1="-1000" y1="0" x2="1000" y2="2000" stroke-width="'.$pattern_Strichstaerke.'" stroke="#'.$_SESSION['Dokument']['Fuellung']['Fehlercodes'][$FCod]['FEHLER_FARBCODE'].'" />
					<line x1="0" y1="-1000" x2="2000" y2="1000" stroke-width="'.$pattern_Strichstaerke.'" stroke="#'.$_SESSION['Dokument']['Fuellung']['Fehlercodes'][$FCod]['FEHLER_FARBCODE'].'" />
				</pattern>'; 
		
		// Pattern für Schraffur leerer Polygone in der Legende (feste Größe)
		$pgroesseleg = $pgroesse_Ausgangswert;
		$strichstpattleg = $pattern_Strichstaerke/100;
		echo  '	<pattern id="errschraff'.$_SESSION['Dokument']['Fuellung']['Fehlercodes'][$FCod]['FEHLERCODE'].'_legende" patternUnits="userSpaceOnUse" viewBox="0 0 10 10" width="'.$pgroesseleg.'" height="'.$pgroesseleg.'">
					<desc>'.$s.'</desc>
					<rect x="0" y="0" width="10" height="10" fill="white" />
					<line x1="0" y1="0" x2="10" y2="10" stroke-width="'.$strichstpattleg.'" stroke="#'.$_SESSION['Dokument']['Fuellung']['Fehlercodes'][$FCod]['FEHLER_FARBCODE'].'" />
					<line x1="-10" y1="0" x2="10" y2="20" stroke-width="'.$strichstpattleg.'" stroke="#'.$_SESSION['Dokument']['Fuellung']['Fehlercodes'][$FCod]['FEHLER_FARBCODE'].'" />
					<line x1="0" y1="-10" x2="20" y2="10" stroke-width="'.$strichstpattleg.'" stroke="#'.$_SESSION['Dokument']['Fuellung']['Fehlercodes'][$FCod]['FEHLER_FARBCODE'].'" />
				</pattern>'; 
	}	
}
echo  "</defs>";





//---------------------------------------------------------------------------------
// Zukünftig je nach Sprachwahl aus DB holen!
// temporär hier definierte Textbausteine <- müssen bei Übersetzung aus DB kommen

// Verbindung zu DB monitor_svg
//mysqli_select_db("monitor_svg");

// Auslesen der Sprachschnipsel
/* $Kartentitel = utf8_encode("IÖR-Monitor");
$Karte_leer = utf8_encode("Noch keine Daten ausgewählt.");
$Datenset_Trenner = "Gebiet: ";
$Raumgliederung_Trenner = "Gliederung: ";
$Indikator_Trenner = "Indikator: ";
$Datengrundlage = utf8_encode("Datengrundlage:");
$Erleuterungen = utf8_encode("Informationen zum Indikator");  
*/

// Sprachvariablen definieren
$Sprache_Ausgabe['DE']['Kartentitel'] = 'IÖR-Monitor';
$Sprache_Ausgabe['EN']['Kartentitel'] = 'IÖR-Monitor';
$Kartentitel = utf8_encode($Sprache_Ausgabe[$_SESSION['Dokument']['Sprache']]['Kartentitel']);

$Sprache_Ausgabe['DE']['Karte_leer'] = 'Noch keine Daten ausgewählt.';
$Sprache_Ausgabe['EN']['Karte_leer'] = 'No data selected.';
$Karte_leer = utf8_encode($Sprache_Ausgabe[$_SESSION['Dokument']['Sprache']]['Karte_leer']);

$Sprache_Ausgabe['DE']['Datenset_Trenner'] = 'Gebiet: ';
$Sprache_Ausgabe['EN']['Datenset_Trenner'] = 'Area: ';
//$Datenset_Trenner = utf8_encode($Sprache_Ausgabe[$_SESSION['Dokument']['Sprache']]['Datenset_Trenner']);

$Sprache_Ausgabe['DE']['Title_mitte'] = ' in ';
$Sprache_Ausgabe['EN']['Title_mitte'] = ' in ';
$Title_mitte = utf8_encode($Sprache_Ausgabe[$_SESSION['Dokument']['Sprache']]['Title_mitte']);

$Sprache_Ausgabe['DE']['Raumgliederung_Trenner'] = 'Gliederung: ';
$Sprache_Ausgabe['EN']['Raumgliederung_Trenner'] = 'Segmentation: ';
$Raumgliederung_Trenner = utf8_encode($Sprache_Ausgabe[$_SESSION['Dokument']['Sprache']]['Raumgliederung_Trenner']);

$Sprache_Ausgabe['DE']['Indikator_Trenner'] = 'Indikator: ';
$Sprache_Ausgabe['EN']['Indikator_Trenner'] = 'Indicator: ';
$Indikator_Trenner = utf8_encode($Sprache_Ausgabe[$_SESSION['Dokument']['Sprache']]['Indikator_Trenner']);

$Sprache_Ausgabe['DE']['Datengrundlage'] = 'Datengrundlage:';
$Sprache_Ausgabe['EN']['Datengrundlage'] = 'Data basis:';
$Datengrundlage = utf8_encode($Sprache_Ausgabe[$_SESSION['Dokument']['Sprache']]['Datengrundlage']);

$Sprache_Ausgabe['DE']['Erleuterungen'] = 'Informationen zum Indikator';
$Sprache_Ausgabe['EN']['Erleuterungen'] = 'Indicator information';
$Erleuterungen = utf8_encode($Sprache_Ausgabe[$_SESSION['Dokument']['Sprache']]['Erleuterungen']);

$Sprache_Ausgabe['DE']['Kartenprojektion'] = 'Kartenprojektion:';
$Sprache_Ausgabe['EN']['Kartenprojektion'] = 'Projection:';
$Kartenprojektion = utf8_encode($Sprache_Ausgabe[$_SESSION['Dokument']['Sprache']]['Kartenprojektion']);

/*Version mit einfachem HTML Fenster mit Tabelle*/
$Sprache_Ausgabe['DE']['Stadtteilquelle'] = '<a xlink:href="Stadtteilquellen.php" target="_blank" fill="blue" >Quellen der Stadtteilgeometrien</a>';
$Sprache_Ausgabe['EN']['Stadtteilquelle'] = '<a xlink:href="Stadtteilquellen.php" target="_blank" fill="blue" >City geometries from several sources</a>';
$Stadtteilquelle = utf8_encode($Sprache_Ausgabe[$_SESSION['Dokument']['Sprache']]['Stadtteilquelle']);


/* 
$Sprache_Ausgabe['DE']['Datengrundlage'] = 'Kennblatt';
$Sprache_Ausgabe['EN']['Datengrundlage'] = 'Datasheet';

$Sprache_Ausgabe['DE']['Datengrundlage'] = 'Kennblatt';
$Sprache_Ausgabe['EN']['Datengrundlage'] = 'Datasheet';

$Sprache_Ausgabe['DE']['Datengrundlage'] = 'Kennblatt';
$Sprache_Ausgabe['EN']['Datengrundlage'] = 'Datasheet';
 */
 //Legende und Titel wenn Grundaktkarte groß
$Sprache_Ausgabe['DE']['Grundaktualitaet_Legende'] = '<a xlink:href="http://new.ioer-monitor.de/index.php?id=88" target="_blank">Datenalter gegenüber '.$_SESSION['Dokument']['Jahr_Anzeige'].'</a>';
$Sprache_Ausgabe['EN']['Grundaktualitaet_Legende'] = '<a xlink:href="http://new.ioer-monitor.de/index.php?id=88" target="_blank">Age of data compared to '.$_SESSION['Dokument']['Jahr_Anzeige'].'</a>';
$Grundaktualitaet_Legende = utf8_encode($Sprache_Ausgabe[$_SESSION['Dokument']['Sprache']]['Grundaktualitaet_Legende']);

$Sprache_Ausgabe['DE']['Grundaktualitaet_Legende_Untertitel'] = 'in Jahren (Datum)';
$Sprache_Ausgabe['EN']['Grundaktualitaet_Legende_Untertitel'] = 'in years';
$Grundaktualitaet_Legende_Untertitel = utf8_encode($Sprache_Ausgabe[$_SESSION['Dokument']['Sprache']]['Grundaktualitaet_Legende_Untertitel']);


$Sprache_Ausgabe['DE']['Grundaktualitaet_Legende_Info'] = '<a xlink:href="http://new.ioer-monitor.de/index.php?id=88" target="_blank">Mehr Informationen</a>';
$Sprache_Ausgabe['EN']['Grundaktualitaet_Legende_Info'] = '<a xlink:href="http://new.ioer-monitor.de/index.php?id=88" target="_blank">More information</a>';
$Grundaktualitaet_Legende_Info = utf8_encode($Sprache_Ausgabe[$_SESSION['Dokument']['Sprache']]['Grundaktualitaet_Legende_Info']);

//Legende und Titel wenn Grundakt klein unten rechts
$Sprache_Ausgabe['DE']['Grundaktualitaet_AktVorschau'] = '<a xlink:href="http://new.ioer-monitor.de/index.php?id=88" target="_blank">Datenalter gegenüber '.$_SESSION['Dokument']['Jahr_Anzeige'].' (in Jahren)</a>';
$Sprache_Ausgabe['EN']['Grundaktualitaet_AktVorschau'] = '<a xlink:href="http://new.ioer-monitor.de/index.php?id=88" target="_blank">Age of data compared to '.$_SESSION['Dokument']['Jahr_Anzeige'].' (in years)</a>';
$Grundaktualitaet_AktVorschau = utf8_encode($Sprache_Ausgabe[$_SESSION['Dokument']['Sprache']]['Grundaktualitaet_AktVorschau']);

$Sprache_Ausgabe['DE']['Grundaktualitaet_AktVorschau_2000'] = '<a xlink:href="http://new.ioer-monitor.de/index.php?id=88" target="_blank">Die genaue Grundaktualität ist </a>';
$Sprache_Ausgabe['EN']['Grundaktualitaet_AktVorschau_2000'] = '<a xlink:href="http://new.ioer-monitor.de/index.php?id=88" target="_blank">Exact age of data unknown. A basal</a>';
$Grundaktualitaet_AktVorschau_2000 = utf8_encode($Sprache_Ausgabe[$_SESSION['Dokument']['Sprache']]['Grundaktualitaet_AktVorschau_2000']);

$Sprache_Ausgabe['DE']['Grundaktualitaet_AktVorschau_2000_b'] = '	<a xlink:href="http://new.ioer-monitor.de/index.php?id=88" target="_blank">unbekannt. Nach Hinweisen der </a>';
$Sprache_Ausgabe['EN']['Grundaktualitaet_AktVorschau_2000_b'] = '<a xlink:href="http://new.ioer-monitor.de/index.php?id=88" target="_blank">topicality as of 1995 is assumed.</a>';
$Grundaktualitaet_AktVorschau_2000_b = utf8_encode($Sprache_Ausgabe[$_SESSION['Dokument']['Sprache']]['Grundaktualitaet_AktVorschau_2000_b']);

$Sprache_Ausgabe['DE']['Grundaktualitaet_AktVorschau_2000_c'] = '	<a xlink:href="http://new.ioer-monitor.de/index.php?id=88" target="_blank">Vermessungsverwaltungen muss </a>';
$Sprache_Ausgabe['EN']['Grundaktualitaet_AktVorschau_2000_c'] = '<a xlink:href="http://new.ioer-monitor.de/index.php?id=88" target="_blank"></a>';
$Grundaktualitaet_AktVorschau_2000_c = utf8_encode($Sprache_Ausgabe[$_SESSION['Dokument']['Sprache']]['Grundaktualitaet_AktVorschau_2000_c']);

$Sprache_Ausgabe['DE']['Grundaktualitaet_AktVorschau_2000_d'] = '	<a xlink:href="http://new.ioer-monitor.de/index.php?id=88" target="_blank">von einem Datenstand um 1995</a>';
$Sprache_Ausgabe['EN']['Grundaktualitaet_AktVorschau_2000_d'] = '<a xlink:href="http://new.ioer-monitor.de/index.php?id=88" target="_blank"></a>';
$Grundaktualitaet_AktVorschau_2000_d = utf8_encode($Sprache_Ausgabe[$_SESSION['Dokument']['Sprache']]['Grundaktualitaet_AktVorschau_2000_d']);

$Sprache_Ausgabe['DE']['Grundaktualitaet_AktVorschau_2000_e'] = '	<a xlink:href="http://new.ioer-monitor.de/index.php?id=88" target="_blank"> ausgegangen werden.</a>';
$Sprache_Ausgabe['EN']['Grundaktualitaet_AktVorschau_2000_e'] = '<a xlink:href="http://new.ioer-monitor.de/index.php?id=88" target="_blank"></a>';
$Grundaktualitaet_AktVorschau_2000_e = utf8_encode($Sprache_Ausgabe[$_SESSION['Dokument']['Sprache']]['Grundaktualitaet_AktVorschau_2000_e']);


$Sprache_Ausgabe['DE']['Grundaktualitaet_AktTitel'] = utf8_encode('Datenalter gegenüber gewähltem Zeitschnitt: '.$_SESSION['Dokument']['Jahr_Anzeige']);
$Sprache_Ausgabe['EN']['Grundaktualitaet_AktTitel'] = utf8_encode('Data age compared to selected time slice: '.$_SESSION['Dokument']['Jahr_Anzeige']);
$Grundaktualitaet_AktTitel = $Sprache_Ausgabe[$_SESSION['Dokument']['Sprache']]['Grundaktualitaet_AktTitel'];



$Sprache_Ausgabe['DE']['Darstellungsgrundlage'] = 'Darstellungsgrundlage';
$Sprache_Ausgabe['EN']['Darstellungsgrundlage'] = 'Map segmentation basis';
$Darstellungsgrundlage = utf8_encode($Sprache_Ausgabe[$_SESSION['Dokument']['Sprache']]['Darstellungsgrundlage']);

$Sprache_Ausgabe['DE']['Einheit_txt'] = 'Einheit';
$Sprache_Ausgabe['EN']['Einheit_txt'] = 'Unit';
$Einheit_txt = utf8_encode($Sprache_Ausgabe[$_SESSION['Dokument']['Sprache']]['Einheit_txt']);

$Sprache_Ausgabe['DE']['Legende_txt'] = 'Legende';
$Sprache_Ausgabe['EN']['Legende_txt'] = 'Map legend';
$Legende_txt = utf8_encode($Sprache_Ausgabe[$_SESSION['Dokument']['Sprache']]['Legende_txt']);



// Raumgliederung erfassen ...??? Name ist doch bekannt!?
$SQL_Raumgliederung_Anz = "SELECT DB_Kennung,ID_RAUMGLIEDERUNG,NAME,Raumgliederung_HTML FROM v_raumgliederung WHERE DB_Kennung = '".$_SESSION['Dokument']['Raumgliederung']."'";
$Ergebnis_Raumgliederung_Anz = mysqli_query($Verbindung,$SQL_Raumgliederung_Anz);

//$Raumgl = utf8_encode(@mysqli_result($Ergebnis_Raumgliederung_Anz,0,'Raumgliederung_HTML'));
//$ID_Raumgl = utf8_encode(@mysqli_result($Ergebnis_Raumgliederung_Anz,0,'ID_RAUMGLIEDERUNG'));
$Key_Raumgl = utf8_encode(@mysqli_result($Ergebnis_Raumgliederung_Anz,0,'DB_Kennung')); 


// Raumgliederungsausgabe zwischen Titel und Karte
// DE
if($_SESSION['Dokument']['Sprache'] == 'DE')
{
	if($Key_Raumgl == 'ror') $Raumgl = utf8_encode('Raumordngsreg.'); // Fehler bei der Darstellung durch zu langen Gliederungsnamen bei ROR verhindern
	if($Key_Raumgl == 'bld') $Raumgl = utf8_encode('Bundesländer'); 
	if($Key_Raumgl == 'krs') $Raumgl = utf8_encode('Kreise'); 
	if($Key_Raumgl == 'lks') $Raumgl = utf8_encode('Landkreise'); 
	if($Key_Raumgl == 'kfs') $Raumgl = utf8_encode('Kreisfreie Städte'); 
	if($Key_Raumgl == 'gem') $Raumgl = utf8_encode('Gemeinden'); 
	if($Key_Raumgl == 'vwg') $Raumgl = utf8_encode('Gemeindeverbände'); 
  if($Key_Raumgl == 'g50') $Raumgl = utf8_encode('Städt. >50.000 Ew.'); 
  if($Key_Raumgl == 'stt') $Raumgl = utf8_encode('Stadtteile'); 
}


// EN
if($_SESSION['Dokument']['Sprache'] == 'EN')
{
	if($Key_Raumgl == 'ror') $Raumgl = utf8_encode('Planning Reg.'); // Fehler bei der Darstellung durch zu langen Gliederungsnamen bei ROR verhindern
	if($Key_Raumgl == 'bld') $Raumgl = utf8_encode('States'); 
	if($Key_Raumgl == 'krs') $Raumgl = utf8_encode('Districts'); 
	if($Key_Raumgl == 'lks') $Raumgl = utf8_encode('Country Districts'); 
	if($Key_Raumgl == 'kfs') $Raumgl = utf8_encode('City Districts'); 
	if($Key_Raumgl == 'gem') $Raumgl = utf8_encode('Municipal level');
	if($Key_Raumgl == 'vwg') $Raumgl = utf8_encode('Municipal association');
	if($Key_Raumgl == 'g50') $Raumgl = utf8_encode('Cities >50k in.');     
	if($Key_Raumgl == 'stt') $Raumgl = utf8_encode('Quarters');   
}



$Raumgliederung_Ausgabe = $Raumgl.$Title_mitte;
// $_SESSION['Dokument']['Raumgliederung_Ausgabe']	= $Sprache_Ausgabe[$_SESSION['Dokument']['Sprache']][utf8_encode(@mysqli_result($Ergebnis_Raumgliederung_Anz,0,'Raumgliederung_HTML'))];
$_SESSION['Dokument']['Raumgliederung_Ausgabe']	= utf8_encode(@mysqli_result($Ergebnis_Raumgliederung_Anz,0,'Raumgliederung_HTML')); // Name der aktuellen Raumglied. für Tabellenansicht usw. im Session-Array sinnvoll aufgehoben!



// gewählten Indikator erfassen
//$Indikator_Beschreibung = $_SESSION['Dokument']['Fuellung']['Indikator_Beschreibung']; // ist schon UTF(-codiert
//$Indikator_Ausgabe = $Indikator_Trenner.$Indikator_Beschreibung;



// $BKG_Hinweis = utf8_encode('Geometrische Grundlage: VG250 © Bundesamt für Kartographie und Geodäsie (<a xlink:href="http://www.bkg.bund.de" target="_blank">www.bkg.bund.de</a>)');
$Jahr_Ausgabe = 'Jahr: '.$_SESSION['Dokument']['Jahr_Geometrietabelle'];
$Herausgeber = ""; //"Herausgeber:";
// $Legende = "Legende";
$IOER_a = utf8_encode("Leibniz Institut für");
$IOER_b = utf8_encode("ökologische");
$IOER_c = utf8_encode("Raumentwicklung e.V.");
$IOER_d = utf8_encode("www.ioer.de");

/* 
Hinweise zu Grundlagen der Kartendarstellung...
Formulierungsforderung des BKG:
Datengrundlage: ATKIS Basis DLM, Vermessungsverwaltungen der Länder und BKG, Jahreszahl
Darstellungsgrundlage: (c)Bundesamt für Kartographie und Geodäsie, VG250 (ohne ATKIS). Jahreszahl
*/

// Datengrundlage auch für Tabelle im Session-Array ablegen
if($GLOBALS['STANDARD-DATENGRUNDLAGE'] == '1') 
{
	//$_SESSION['Dokument']['Datengrundlage_0'] = $Datengrundl_Inhalt_1 = utf8_encode('ATKIS Basis DLM, Vermessungsverwaltungen der Länder und BKG (').$Jahr_plus_eins = ($_SESSION['Dokument']['Jahr_Anzeige']+1).')';
    $_SESSION['Dokument']['Datengrundlage_0'] = $Datengrundl_Inhalt_1 = utf8_encode('Digitales Basis-Landschaftsmodell (Basis-DLM): © GeoBasis-DE / BKG (').$Jahr_plus_eins = ($_SESSION['Dokument']['Jahr_Anzeige']+1).')';
}
else
{
	$_SESSION['Dokument']['Datengrundlage_0'] = '';
}
if($GLOBALS['DATENGRUNDLAGE_ZEILE_1'])
{
	$_SESSION['Dokument']['Datengrundlage_1'] = $Datengrundl_Inhalt_1_1 = $GLOBALS['DATENGRUNDLAGE_ZEILE_1'].' ('.$Jahr_plus_eins = ($_SESSION['Dokument']['Jahr_Anzeige']+1).')';
}
else
{
	$_SESSION['Dokument']['Datengrundlage_1'] = '';
}
//$Datengrundl_Inhalt_2 = utf8_encode('Bundesamt für Kartographie und Geodäsie, DLM250, VG250 Gebietsstand: '.$_SESSION['Dokument']['Jahr_Geometrietabelle']);
$Datengrundl_Inhalt_2 = utf8_encode('Verwaltungsgebiete 1:250.000 (VG250): © GeoBasis-DE / BKG (').$Jahr_plus_eins = ($_SESSION['Dokument']['Jahr_Anzeige']+1).')';
//$Datengrundl_Inhalt_3 = utf8_encode('<a xlink:href="http://www.bkg.bund.de" target="_blank">© Vermessungsverwaltungen der Länder und BKG '.$jhr=($_SESSION['Dokument']['Jahr_Geometrietabelle']+1).' (www.bkg.bund.de)</a>');
//Change Rubel
//$Datengrundl_Inhalt_3 = utf8_encode('<a xlink:href="http://www.bkg.bund.de" target="_blank">© GeoBasis-DE / BKG '.date("Y").' (www.bkg.bund.de)</a>');
$Datengrundl_Inhalt_3 = utf8_encode('<a xlink:href="http://www.bkg.bund.de" target="_blank">www.bkg.bund.de</a>');
// $Datengrundl_Inhalt_3 = utf8_encode($Datengrundl_Inhalt_3);

// Testausgabe
// -----------------------------------
// $Datengrundl_Inhalt_3	= $Test_SQL_Ausgabe;
// -----------------------------------

 
// Copyright in Kartenfeld  + IOER Logo includieren => in Variable $LOGO verfügbar
if($_SESSION['Dokument']['Sprache'] == 'DE')
{
$Copyright = utf8_encode('IÖR-Monitor©Leibniz-Institut für ökologische Raumentwicklung');
include("includes_classes/ioer_logo_svg.php");
}
	if($_SESSION['Dokument']['Sprache'] == 'EN')
{
	$Copyright_a = utf8_encode('Monitor of Settlement and Open Space Development'); 
	$Copyright_b =utf8_encode('© Leibniz Institute of Ecological Urban and Regional Development'); 
		include("includes_classes/ioer_logo_svg_en.php");
	
}



// Schriftgröße für Labels festlegen
if(!$Font_size_Labels) $Font_size_Labels = '12';

// Y-Position für Farbverlauf in Legende
$Farbverlauf_Y = 200;

// Klassenanzeige in Legende
$k_Y = 195; // Y_Anfangswert

// Verteilungskurve in Legende
$i_Verteilg_Y = '150'; // Y_Anfangswert der Kurve
$x_wert = $xl=($XD-170); // X-Anfangswert der Kurve

// Infofeld für Mouse-Events

if($_SESSION['Dokument']['Sprache'] == 'DE')
{
	$ObjInfofeld_Rand_oben = 410;
	$ObjInfofeld_Titel = 'Markiertes Gebiet';
	$ObjInfofeld_Titel_1 = 'Name';
	$ObjInfofeld_Titel_2 = utf8_encode('Indikator-Wert');
	$ObjInfofeld_Y = $YD-110; // wird dann systematisch um 15 erweitert <= Zeilensprung pro TextObjekt
	$ObjInfofeld_Bemerkung = utf8_encode("Für Beschriftung bitte anklicken.");
	$ObjInfofeld_Grundakt = utf8_encode('Mittlere Grundaktualität:');	
	$Histogramm = utf8_encode('Histogramm:');
}
				
if($_SESSION['Dokument']['Sprache'] == 'EN')
{
	$ObjInfofeld_Rand_oben = 410;
	$ObjInfofeld_Titel = 'Selected area';
	$ObjInfofeld_Titel_1 = 'Name';
	$ObjInfofeld_Titel_2 = utf8_encode('Indicator value');
	$ObjInfofeld_Y = $YD-110; // wird dann systematisch um 15 erweitert <= Zeilensprung pro TextObjekt
	$ObjInfofeld_Bemerkung = utf8_encode("For viewing label, please click!");
	$ObjInfofeld_Grundakt = utf8_encode('Mean basal topicality:');
	$Histogramm = utf8_encode('Histogram:');	
}
	
	

//----------------------------------------------------------------------------------


// SVG mit Daten füllen
// ----------------------------------------------------------


if(!$_SESSION['Dokument']['Raumgliederung'] or !$_SESSION['Dokument']['Fuellung']['Indikator'] or $Region!='1')
{ 
	// Meldung und Deutschlandbild, wenn Karte noch leer => ansonsten Karte füllen
	// -----------------------------------------------------------------------------
	echo  '<g id="Leere Karte">';
	// Hintergrundfarbe zeichnen
	echo  '<rect x="0" y="0" width="760px" height="500px" fill="#FFFFFF" stroke="none"/>'; 
	echo  '
				<image x="-75" y="0" width="'.$X_Leer = ($_SESSION['Dokument']['groesse_X']+150)
				.'px" height="'.$Y_Leer = ($_SESSION['Dokument']['groesse_Y']+150)
				.'px" xlink:href="gfx/deutschland_leer.png" id="Keine Daten" style="opacity:0.4;" ></image>
				<text x="'.$xleer=($XD/2-300).'" y="'.$xleer=($YD/2).'" dx="" dy="" style="font-size:28px; font-family:Arial; font-weight:bold;" fill="#999999" >'.$Karte_leer.'</text>
				<text x="'.$xleer=($XD/2-300).'" y="'.$xleer=(($YD/2)+50).'" dx="" dy="" style="font-size:28px; font-family:Arial; font-weight:bold;" fill="#999999" >'.$_SESSION['ID_Loginfehler'].'</text>';
	echo  '</g>';
}
else
{
	
	
	// ------- Counter -------
	// Speichern der Kartendaten für Auswertung
	// Nur für externe User erfassen, nicht für IÖR-Netzwerk (IP muss von Proxy/Internet (=angegebene IP kommen)
	if($_SERVER['REMOTE_ADDR'] == '192.9.200.7')

	{
	
		// Karte schon einmal angezeigt? = Prüfen ob schon Eintrag in MYSQL vorhanden
		$SQL_Auswertg_vorh = "SELECT ID_AUSWERTUNG,ZAEHLER FROM v_auswertung 
								WHERE 
								JAHR = '".$_SESSION['Dokument']['Jahr_Anzeige']."' AND
								RAUMEBENE = '".$_SESSION['Dokument']['Raumebene_NAME_Auswertung']."' AND
								RAUMGLIEDERUNG = '".$_SESSION['Dokument']['Raumgliederung']."' AND
								ID_INDIKATOR = '".$_SESSION['Dokument']['Fuellung']['Indikator']."';";
		$Ergebnis_Auswertg_vorh = mysqli_query($Verbindung,$SQL_Auswertg_vorh);
	
		if(@mysqli_result($Ergebnis_Auswertg_vorh,0,'ID_AUSWERTUNG'))
		{
			// wenn vorhanden: UPDATE des Zählers
			$SQL_Auswertg_UPD = "UPDATE v_auswertung 
									SET ZAEHLER = '".$ZAUPD = (@mysqli_result($Ergebnis_Auswertg_vorh,0,'ZAEHLER') + 1)."' 
									WHERE ID_AUSWERTUNG = '".@mysqli_result($Ergebnis_Auswertg_vorh,0,'ID_AUSWERTUNG')."';";
			$Ergebnis_Auswertg_UPD = mysqli_query($Verbindung,$SQL_Auswertg_UPD);
		
		}
		else
		{
			// wenn nicht vorhanden: INSERT Kartenkombination
			$SQL_Auswertg_INS = "INSERT INTO v_auswertung 
									(JAHR,RAUMEBENE,RAUMGLIEDERUNG,ID_INDIKATOR,ZAEHLER)
									VALUES
									('".$_SESSION['Dokument']['Jahr_Anzeige']."',
									 '".$_SESSION['Dokument']['Raumebene_NAME_Auswertung']."',
									 '".$_SESSION['Dokument']['Raumgliederung']."',
									 '".$_SESSION['Dokument']['Fuellung']['Indikator']."',
									 '1');";
			$Ergebnis_Auswertg_INS = mysqli_query($Verbindung,$SQL_Auswertg_INS);
			
		}
	}
//-----------Ende Counter---------
	
	
	
	// -----> Hintergrundfarbe: im unlegend-Betrieb nicht ausgeben
	if(!$_SESSION['Dokument']['unlegend'])
	{
		// Hintergrundfarbe zeichnen
		echo  '<g><rect x="0" y="0" width="'.$XD.'px" height="'.$YD.'px" fill="#FFFFFF" stroke="none"/></g>'; 
	}
	
	// -----> Hintergrundebene-Deutschland-Bundesländer einfügen
	echo  '<g transform="matrix('.$s.' 0 0 '.$s.' '.$X_min.' '.$Y_max.')" id="hg_'.$DatenSet['NAME'].'" >';
	$i_array=0;
	while($Ausgabe['Hintergrund'][$i_array])
	{
		echo  $Ausgabe['Hintergrund'][$i_array]; // Wichtig an der Stelle: Schreiben der Pfade (ohne nochmals zusammensetzen zu müssen) direkt aus Array <= superperformant !!!
		$i_array++;
	}			
	echo  "</g>";
		
		
		
		
	// --------------------------------------------------------> INHALT: berechnete EbenenObjekte einfügen ------------------------
	foreach($_SESSION['Datenbestand'] as $DatenSet)
	{
		if($DatenSet['View']!='0')
		{		
			// Transformation ohne Verwendung von Ebenen auch direkt in Path integrierbar... aber nicht unbedingt nötig/sinnvoll
			echo  '<g transform="matrix('.$s.' 0 0 '.$s.' '.$X_min.' '.$Y_max.')" id="ds_'.$DatenSet['NAME'].'">
			<desc>'.$_SESSION['Datenbestand'][$DatenSet['NAME']]['X_min_global'].' '.$_SESSION['Datenbestand'][$DatenSet['NAME']]['Y_max_global'].'</desc>';
			
			$i_array=0;
			while($Ausgabe[$DatenSet['NAME']][$i_array])
			{
				echo  $Ausgabe[$DatenSet['NAME']][$i_array]; // Wichtig an der Stelle: Schreiben der Pfade (ohne nochmals zusammensetzen zu müssen) direkt aus Array <= superperformant !!!
				$i_array++;
			}	
			 // Marker für Mouseover		
			echo  "</g>"; 
			
		}
			
	}
	// -------------------------------------------------------- INHALT ENDE -------------------------------------------------------





	// -------------------------------------------------------- Zusatzebenen ------------------------------------------------------
	
/* 	// Aktive Zusatzebene für Beschriftung festlegen (nach Hirarchie)
	// Hirarchische Aktivschaltung einer Zusatzebene als Beschriftungsbasis
	foreach($Zusatzebene as $Ebene_view)
	{
		if(($Ebene_view == "bld" or $Ebene_view == "krs" or $Ebene_view == "gem"))
		{
			if($GLOBALS['Zusatzebene_aktiv'] != "krs" and $GLOBALS['Zusatzebene_aktiv'] != "gem") $GLOBALS['Zusatzebene_aktiv'] = "bld";
			if($Ebene_view == "krs" or $Ebene_view == "gem")
			{
				if($GLOBALS['Zusatzebene_aktiv'] != "gem") $GLOBALS['Zusatzebene_aktiv'] = "krs";
				if($Ebene_view == "gem")
				{
					$GLOBALS['Zusatzebene_aktiv'] = "gem";
				}
			}
		}
	} */
		
	// ----->  Zeichnen von Zusatzebenen (Flüsse, VG, BABs, ...)
	for($i_ZEb=0 ; $i_ZEb < 10 ; $i_ZEb++)
	{
		// --- Standards für Verwaltungsgrenzen ---
			// Farben 
			$Z_Color = "#".$_SESSION['Dokument']['Strichfarbe_ZE_VG'];
			if(!$_SESSION['Dokument']['Strichfarbe_ZE_VG']) $Z_Color = "#".$_SESSION['Dokument']['Strichfarbe_ZE_VG'] = "FFFFFF";
			// Strich-Breite
			$Z_stroke_width = 1.3/$s;
		
		
		// Abweichende Vor-Einstellungen
		if($Zusatzebene[$i_ZEb] == "gew") 
		{
			// Maßstabsabhängige Ausgabe
			$Z_stroke_width_raw = 0.3; // > M 200km
			if($s > 0.0009) {$Z_stroke_width_raw = 0.7; } // M 100km
			if($s > 0.002) {$Z_stroke_width_raw = 1; } // M 50km
			if($s > 0.005) {$Z_stroke_width_raw = 1; } // M 10km
			$Z_stroke_width = $Z_stroke_width_raw/$s;
			// Farbe
			$Z_Color = '#0000CC';
		}
		
		/* 
		// Abweichende Vor-Einstellungen
		if($Zusatzebene[$i_ZEb] == "gew_fein") 
		{
			// Maßstabsabhängig
			$Z_stroke_width_raw = 0.3; // > M 200km
			if($s > 0.0009) {$Z_stroke_width_raw = 0.7; } // M 100km
			if($s > 0.002) {$Z_stroke_width_raw = 1; } // M 50km
			if($s > 0.005) {$Z_stroke_width_raw = 1; } // M 10km
			$Z_stroke_width = $Z_stroke_width_raw/$s;
			// Farbe
			$Z_Color = '#0000CC';
		} 
		*/
		if($Zusatzebene[$i_ZEb] == "bab") 
		{
			// Maßstabsabhängig
			$Z_stroke_width_raw = 0.5; // > M 200km
			if($s > 0.0009) {$Z_stroke_width_raw = 1; } // M 100km
			if($s > 0.002) {$Z_stroke_width_raw = 1.3; } // M 50km
			if($s > 0.005) {$Z_stroke_width_raw = 1.3; } // M 10km
			$Z_stroke_width = $Z_stroke_width_raw/$s;
			// Farbe
			$Z_Color = '#555555';
		}
		
		if($Zusatzebene[$i_ZEb] == "db") 
		{
			// Maßstabsabhängig
			$Z_stroke_width_raw = 0.5; // > M 200km
			if($s > 0.0009) {$Z_stroke_width_raw = 1; } // M 100km
			if($s > 0.002) {$Z_stroke_width_raw = 1.3; } // M 50km
			if($s > 0.005) {$Z_stroke_width_raw = 1.3; } // M 10km
			$Z_stroke_width = $Z_stroke_width_raw/$s;
			// Farbe
			$Z_Color = '#555555';
		}
		
		
		// SVG-Code für Ebene durch Funktionen (oben) generieren lassen, wenn gewählt
		if($Zusatzebene[$i_ZEb]) echo  zusatz_zeichnen($Zusatzebene[$i_ZEb],$zusatz[$Zusatzebene[$i_ZEb]],$s,$X_min,$Y_max,$Str_wdt_z = ($Z_stroke_width),$Z_Color); 
		// $Str_wdt_z = (2/$s) Pixelbreite 3px in jedem Maßstab einhalten							
	}
	// -------------------------------------------------------- ENDE Zusatzebenen ------------------------------------------------------





	// Hinweis: Beschriftung von hier ans Ende (im SVG-Dokument nach oben) gelegt

	// ------> SESSION-Array-Anpassung: Selektierte Datensets(Regionen) herausfinden
	$_SESSION['Datenbestand_Ausgabe'] = "";
	if(is_array($_SESSION['Dokument']['Raumebene']))
	{
		foreach($_SESSION['Datenbestand'] as $DatenSet)
		{
			if($DatenSet['View'] == '1')
			{
				// EN für Deutschland (nur für Deutschland Namenskorrektur machen, alles andere wird nicht geändert)
				if($DatenSet['NAME_UTF8'] == 'Deutschland' and $_SESSION['Dokument']['Sprache'] == 'EN')  $DatenSet['NAME_UTF8'] = 'Germany';
				
				$Datenset = $Datenset.$Datenset_Trenner.$DatenSet['NAME_UTF8'];
				$Datenset_Trenner = utf8_encode(", ");	
				$_SESSION['Datenbestand_Ausgabe'] = $_SESSION['Datenbestand_Ausgabe'].$Datenset_Trenner_Ausg.$DatenSet['NAME_UTF8']; // für Tabellenansicht o.Ä. besser in Session-Array verfügbar zu machen	
				$Datenset_Trenner_Ausg = utf8_encode(", ");
			}
		}
	}
	
	
	
	
	
	
	// Legende und Hintergründe
	// ---------------------------------
	// ---------------------------------
	
	
	
	
	// im unlegend-Betrieb nicht ausgeben
	if(!$_SESSION['Dokument']['unlegend'])
	{
		
		// Legendenfeld (unten) Hintergrund
		echo  '<g><rect x="0" y="'.$YD.'" width="'.$XD.'px" height="'.$_SESSION['Dokument']['Hoehe_Legende_unten'].'px" fill="#FFFFFF" stroke="none" stroke-width="1" /></g>'; 
		
		// Decker unter Maßstabsleiste und Copyright
		echo  '<g><rect x="0" y="'.$YD_MC = ($YD-16).'" width="'.$XD.'px" height="17px" fill="#FFFFFF" stroke="none" stroke-width="1" /></g>';
			
		// Legendenfeld (rechts) Hintergrund und Randlinie links
		echo  '<g>
				<rect x="'.$xl=($XD-190).'" y="42" width="186px" height="'.$hl=($YD-36).'px" fill="#FFFFFF" stroke="none" opacity="1" />
				<rect x="'.$xl=($XD-190).'" y="46" width="2px" height="'.$hl=($YD-46).'" fill="#999999" stroke="none" />';
		echo  '<text x="'.$xl=($XD-180).'" y="60" dx="" dy="" style="font-size:12px; font-weight:bold; font-family:Arial;" fill="#444444" >'.$Herausgeber.'</text>';
		
			// Logo mit Link und Skalierung des includierten SVG-Logos
	if($_SESSION['Dokument']['Sprache'] == 'DE')
			{ 
		 	// Logo DE einbinden
					echo  '<a 
						onmouseover="document.getElementById(\'logolink\').setAttributeNS(null,\'text-decoration\',\'underline\');"
						onmouseout="document.getElementById(\'logolink\').setAttributeNS(null,\'text-decoration\',\'none\');" 
						xlink:href="http://www.ioer.de" target="_blank"><g id="Logo" transform="matrix(0.5, 0, 0,0.5, '.$xl=($XD-270).', 10)">'.$LOGO.'</g></a>'; 
	   }
			if($_SESSION['Dokument']['Sprache'] == 'EN')
			{	
		
				// Logo EN einbinden, etwas skaliert
		echo  '<a 
			onmouseover="document.getElementById(\'logolink\').setAttributeNS(null,\'text-decoration\',\'underline\');"
			onmouseout="document.getElementById(\'logolink\').setAttributeNS(null,\'text-decoration\',\'none\');" 
			xlink:href="http://www.ioer.de" target="_blank"><g id="Logo" transform="matrix(0.3, 0, 0, 0.3, '.$xl=($XD-182).', 65)">'.$LOGO.'</g></a>'; 
		}		
		
		echo  '<rect x="'.$xl=($XD-185).'" y="145" width="180px" height="2px" fill="#999999" stroke="none" />';
		echo  '<text x="'.$xl=($XD-180).'" y="165" dx="" dy="" style="font-size:11px; font-weight:bold; font-family:Arial;" fill="#222222" >'.$Legende_txt.'</text>';
		
	//in Legende kein "Einheit:", wenn keine vorhanden (zB Hemerobie)
		if ($_SESSION['Dokument']['Fuellung']['Indikator_Einheit']!= ''){
		echo  '<text x="'.$xl=($XD-180).'" y="185" dx="" dy="" style="font-size:11px; font-family:Arial;" fill="#222222" >'.$Einheit_txt.': '.$_SESSION['Dokument']['Fuellung']['Indikator_Einheit'].'</text>';
	}
		
		
		if(!$Vergleichskarte) // --- Schalter für Vergleichskartenanzeige ---
		{
			// Legende-Indikator
			switch ($_SESSION['Dokument']['Fuellung']['Typ']) {
				case 'Farbbereich':
					// X Verschiebung
					$xv=150;
					// IndikatorenFarbbereich definiert im SVG-Kopf unter <defs>
					echo  '<defs>
							<linearGradient id="IndikatorenFarbbereich" x1="0" x2="0" y1="0%" y2="100%">
								<stop offset="0%" stop-color="#'.$_SESSION['Dokument']['Fuellung']['Farbwert_max'].'" />
								<stop offset="100%" stop-color="#'.$_SESSION['Dokument']['Fuellung']['Farbwert_min'].'" />
							</linearGradient>
						 </defs>';
					// echo  '<rect x="'.$xl=($XD-180).'" y="'.$Fy = ($Farbverlauf_Y).'" width="20px" height="100px" style="fill: url(#IndikatorenFarbbereich)" stroke="#555555" stroke-width="1" />';
					echo  '<rect x="'.$xl=($XD-180).'" y="'.$Fy = ($Farbverlauf_Y).'" width="20px" height="100px" style="fill: url(#IndikatorenFarbbereich)" />'; 
					echo  '<text x="'.$xl=($XD-150).'" y="'.$Fy = ($Farbverlauf_Y+5).'" style="font-size:11px; font-family:Arial;">'.
																		number_format($_SESSION['Dokument']['Fuellung']['Indikator_Wert_max']-1000000000,$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.')
																		.'</text>';
					// Hinweis auf deutschlandweites Wertespektrum, wenn gewählt
					if($_SESSION['Dokument']['indikator_lokal'] == '0')
					{
						echo  '<text x="'.$xl=($XD-150).'" y="'.$Fy = ($Farbverlauf_Y+25).'" style="font-size:11px; font-family:Arial;">'.utf8_encode('Hinweis:').'</text>';
						echo  '<text x="'.$xl=($XD-150).'" y="'.$Fy = ($Farbverlauf_Y+40).'" style="font-size:11px; font-family:Arial;">'.utf8_encode('Das Wertespektrum bezieht').'</text>';
						echo  '<text x="'.$xl=($XD-150).'" y="'.$Fy = ($Farbverlauf_Y+55).'" style="font-size:11px; font-family:Arial;">'.utf8_encode('sich auf das gesamte Gebiet').'</text>';
						echo  '<text x="'.$xl=($XD-150).'" y="'.$Fy = ($Farbverlauf_Y+70).'" style="font-size:11px; font-family:Arial;">'.utf8_encode('der Bundesrepublik.').'</text>';
					}
					echo  '<text x="'.$xl=($XD-150).'" y="'.$Fy = ($Farbverlauf_Y+100).'" style="font-size:11px; font-family:Arial;">'.
																		number_format($_SESSION['Dokument']['Fuellung']['Indikator_Wert_min']-1000000000,$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.')
																		.'</text>';  
					
					// Boxen für Verortungs-Anzeige bei Mouseover über Objekte
					for($i_box = 0; $i_box <= 100 ; $i_box++)
					{
						echo  '<rect id="v_'.$i_box.'" x="'.$xl=($XD-181).'" y="'.$Fy = ($Farbverlauf_Y + 100 - $i_box).'" width="22px" height="1px" style="fill:none;" stroke-width="1" stroke="none" />'; 
					}
					
					
					$klasse_Y = $Farbverlauf_Y + 120;
					/* // Darstellung für leere Objekte, falls vorhanden
					if($Leerwerte_vorhanden == 1)
					{	 
						echo '<rect id="f_leer" x="'.$xl=($XD-180).'" y="'.$klasse_Y.'" width="20px" height="15px" fill="url(#leerschraff_legende)" stroke="none" stroke-width="2" />'; 
						echo  '<text x="'.$xl=($XD-150).'" y="'.$ky = ($klasse_Y+11).'" style="font-size:11px; font-family:Arial;">keine Werte vorhanden</text>';
						$klasse_Y = $klasse_Y + 16;
					}
					
					if(is_array($Fehlerwerte_vorhanden_Array))
					{
						foreach($Fehlerwerte_vorhanden_Array as $FCod)
						{
							echo '<rect id="f_'.$_SESSION['Dokument']['Fuellung']['Fehlercodes'][$FCod]['FEHLERCODE']
							.'"  x="'.$xl=($XD-180).'" y="'.$klasse_Y.'" width="20px" height="15px" fill="url(#errschraff'.$_SESSION['Dokument']['Fuellung']['Fehlercodes'][$FCod]['FEHLERCODE']
							.'_legende)" stroke="none" stroke-width="2" />'; 
							echo  '<text x="'.$xl=($XD-150).'" y="'.$ky = ($klasse_Y+11).'" style="font-size:11px; font-family:Arial;">'.$_SESSION['Dokument']['Fuellung']['Fehlercodes'][$FCod]['FEHLER_NAME']
							.'</text>';
							$klasse_Y = $klasse_Y + 16;
						}
					} */
					
			
					
				break;
				case 'Klassifizierte Farbreihe':
		
					// Füllung nach Klassen
					
					// Y Positionierung
					$klasse_Y = $k_Y;
					// X Verschiebung
					$xv=160;
					// Array umkehren (sieht besser aus bei Ausgabe)
					$Klassen_umgedreht = array_reverse($_SESSION['Temp']['Klasse']);
					// Klassen erfassen
					// Array muss vorhanden sein, da Klassifizierte Füllung nur über svg_zeichenvorschrift.php gewählt werden kann, wo auch immer dieses Array berechnet wird:
					if(is_array($Klassen_umgedreht)) 
					{
						foreach($Klassen_umgedreht as $Klassensets)
						{
							// vor berechnung angezeigt, um letztes Element gesondert, außerhalb der foreach-Schleife ausgeben zu können
							if($Obergrenze) // Verhindert anzeige leeren Elementes
							{
								echo '<rect id="k_'.$Farbe.'" x="'.$xl=($XD-180).'" y="'.$klasse_Y.'" width="15px" height="10px" fill="#'.$Farbe.'" stroke="none" stroke-width="2" />'; 
								echo  '<text x="'.$xl=($XD-160).'" y="'.$ky = ($klasse_Y+8).'" style="font-size:9px; font-family:Arial;"> >'
									.number_format($Untergrenze,$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.').
									' bis '.number_format($Obergrenze,$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.').'</text>';
								$klasse_Y = $klasse_Y + 16;
							}
							
							$Untergrenze = round($Klassensets['Wert_Untergrenze']-1000000000,$_SESSION['Dokument']['Fuellung']['Rundung']);
							// Oberste Klassengrenze auf Dokumentwert beziehen (sonst Rundungsfehler denkbar)
							if($KlasseNull != 'nein') $Obergrenze = $_SESSION['Dokument']['Fuellung']['Indikator_Wert_max_Dok_rounded']-1000000000; 
							if($KlasseNull == 'nein') $Obergrenze = round($Klassensets['Wert_Obergrenze']-1000000000,$_SESSION['Dokument']['Fuellung']['Rundung']); 
							$KlasseNull = 'nein'; // zur erkennung der Klasse 0
							$Farbe = $Klassensets['Farbwert'];
						}
						
						// letzte Klasse abbilden (ohne nochmaligen Schleifendurchlauf)
						echo '<rect  id="k_'.$Klassensets['Farbwert'].'" x="'.$xl=($XD-180).'" y="'.$klasse_Y.'" width="15px" height="10px" fill="#'.$Klassensets['Farbwert'].'" stroke="none" stroke-width="2" />'; 
						
						// Unterscheidung, falls Karte nur ein Objekt beinhaltet (Berlin zB)
						if(!$_SESSION['Temp']['Nur_ein_Wert_vorhanden'])
						{
							echo  '<text x="'.$xl=($XD-160).'" y="'.$ky = ($klasse_Y+8).'" style="font-size:9px; font-family:Arial;">'.
											$w = number_format($_SESSION['Dokument']['Fuellung']['Indikator_Wert_min_Dok_rounded']-1000000000,$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.').
											' bis '.number_format($Obergrenze,$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.').'</text>';
						}
						else
						{
							echo  '<text x="'.$xl=($XD-160).'" y="'.$ky = ($klasse_Y+8).'" style="font-size:9px; font-family:Arial;">'.
											$w = number_format($_SESSION['Dokument']['Fuellung']['Indikator_Wert_min_Dok_rounded']-1000000000,$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.')
											.'</text>';
						}
											
						
						/* // Darstellung für leere Objekte, falls vorhanden (sollte nicht mehr vorkommen!)
						if($Leerwerte_vorhanden == 1)
						{
							$klasse_Y = $klasse_Y + 16;	 
							echo '<rect id="f_leer" x="'.$xl=($XD-180).'" y="'.$klasse_Y.'" width="15px" height="10px" fill="url(#leerschraff_legende)" stroke="none" stroke-width="2" />'; 
							echo  '<text x="'.$xl=($XD-160).'" y="'.$ky = ($klasse_Y+8).'" style="font-size:9px; font-family:Arial;">keine Werte vorhanden</text>';
						}
						
						if(is_array($Fehlerwerte_vorhanden_Array))
						{
							foreach($Fehlerwerte_vorhanden_Array as $FCod)
							{
								$klasse_Y = $klasse_Y + 16;
								echo '<rect id="f_'.$_SESSION['Dokument']['Fuellung']['Fehlercodes'][$FCod]['FEHLERCODE']
								.'" x="'.$xl=($XD-180).'" y="'.$klasse_Y.'" width="15px" height="10px" fill="url(#errschraff'.$_SESSION['Dokument']['Fuellung']['Fehlercodes'][$FCod]['FEHLERCODE']
								.'_legende)" stroke="none" stroke-width="2" />'; 
								echo  '<text x="'.$xl=($XD-160).'" y="'.$ky = ($klasse_Y+8).'" style="font-size:9px; font-family:Arial;">'.$_SESSION['Dokument']['Fuellung']['Fehlercodes'][$FCod]['FEHLER_NAME']
								.'</text>';
							}
						} */
					}
				break;
				case 'manuell Klassifizierte Farbreihe':
		
					// Füllung nach Klassen
					$klasse_Y = $k_Y;
					// X Verschiebung
					$xv=160;
					// Array umkehren (sieht besser aus bei Ausgabe)
					$Klassen_umgedreht = array_reverse($_SESSION['Temp']['manuelle_Klasse']);
					// Klassen erfassen
					// Array muss vorhanden sein, da Klassifizierte Füllung nur über svg_zeichenvorschrift.php gewählt werden kann, wo auch immer dieses Array berechnet wird:
					if(is_array($Klassen_umgedreht)) 
					{
						foreach($Klassen_umgedreht as $Klassensets)
						{
							// vor berechnung angezeigt, um letztes Element gesondert, außerhalb der foreach-Schleife ausgeben zu können
							if($Obergrenze) // Verhindert anzeige leeren Elementes
							{
								echo '<rect id="k_'.$Farbe.'" x="'.$xl=($XD-180).'" y="'.$klasse_Y.'" width="15px" height="10px" fill="#'.$Farbe.'" stroke="none" stroke-width="2" />'; 
								echo  '<text x="'.$xl=($XD-160).'" y="'.$ky = ($klasse_Y+8).'" style="font-size:9px; font-family:Arial;"> >'
								.number_format($Untergrenze,$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.').
								' bis '.number_format($Obergrenze,$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.').'</text>';
								$klasse_Y = $klasse_Y + 16;
							}
							$Untergrenze = round($Klassensets['Wert_Untergrenze']-1000000000,$_SESSION['Dokument']['Fuellung']['Rundung']);
							// hier nicht Dokumentwert... komische Effekte wenn dieser kleiner als Klassengrenzen (bei geänderter Zeitschnittauswahl)!
							$Obergrenze = round($Klassensets['Wert_Obergrenze']-1000000000,$_SESSION['Dokument']['Fuellung']['Rundung']); 
							$KlasseNull = 'nein';
							$Farbe = $Klassensets['Farbwert'];
						}
						
						// letzte Klasse abbilden (ohne nochmaligen Schleifendurchlauf): hier auch nicht Dokumentwerte sondern echte Klassengrenzen benutzt
						echo '<rect id="k_'.$Klassensets['Farbwert'].'" x="'.$xl=($XD-180).'" y="'.$klasse_Y.'" width="15px" height="10px" fill="#'.$Klassensets['Farbwert'].'" stroke="none" stroke-width="2" />'; 
						echo  '<text x="'.$xl=($XD-160).'" y="'.$ky = ($klasse_Y+8).'" style="font-size:9px; font-family:Arial;">'
									.number_format($Untergrenze,$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.').
									' bis '.number_format($Obergrenze,$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.').'</text>';
						
						/* // Darstellung für leere Objekte, falls vorhanden
						if($Leerwerte_vorhanden == 1)
						{
							$klasse_Y = $klasse_Y + 16;	 
							echo '<rect id="f_leer" x="'.$xl=($XD-180).'" y="'.$klasse_Y.'" width="15px" height="10px" fill="url(#leerschraff_legende)" stroke="none" stroke-width="2" />'; 
							echo  '<text x="'.$xl=($XD-160).'" y="'.$ky = ($klasse_Y+8).'" style="font-size:9px; font-family:Arial;">keine Werte vorhanden</text>';
						}
						
						if(is_array($Fehlerwerte_vorhanden_Array))
						{
							foreach($Fehlerwerte_vorhanden_Array as $FCod)
							{
								$klasse_Y = $klasse_Y + 16;
								echo '<rect id="f_'.$_SESSION['Dokument']['Fuellung']['Fehlercodes'][$FCod]['FEHLERCODE']
								.'"  x="'.$xl=($XD-180).'" y="'.$klasse_Y.'" width="15px" height="10px" fill="url(#errschraff'.$_SESSION['Dokument']['Fuellung']['Fehlercodes'][$FCod]['FEHLERCODE']
								.'_legende)" stroke="none" stroke-width="2" />'; 
								echo  '<text x="'.$xl=($XD-160).'" y="'.$ky = ($klasse_Y+8).'" style="font-size:9px; font-family:Arial;">'.$_SESSION['Dokument']['Fuellung']['Fehlercodes'][$FCod]['FEHLER_NAME']
								.'</text>';
							}
						} */
					}
				break;
			}
		}
		else
		{
			// --- Legende für Vergleichskartenanzeige ---
			
				// X Verschiebung
				$xv=150;
					// IndikatorenFarbbereich definiert im SVG-Kopf unter <defs>
					echo  '<defs>';
					
					/* echo '
							<linearGradient id="IndikatorenFarbbereich" x1="0" x2="0" y1="0%" y2="100%">
								<stop offset="0%" stop-color="#'.$_SESSION['Dokument']['Fuellung']['Farbwert_max'].'" />
								<stop offset="100%" stop-color="#'.$_SESSION['Dokument']['Fuellung']['Farbwert_min'].'" />
							</linearGradient>'; */
							
							
							
					echo  '
						<linearGradient id="IndikatorenFarbbereich_horizontal_vergleich_L" x1="0" x2="0%" y1="0" y2="100%">
							<stop offset="0%" stop-color="#'.$_SESSION['Dokument']['Fuellung']['Farbwert_Diff_max'].'" />
							<stop offset="50%" stop-color="#FFFFFF" />
						</linearGradient>
						<linearGradient id="IndikatorenFarbbereich_horizontal_vergleich_L_2" x1="0" x2="0%" y1="0" y2="100%">
							<stop offset="0%" stop-color="#FFFFFF" />
							<stop offset="100%" stop-color="#'.$_SESSION['Dokument']['Fuellung']['Farbwert_Diff_min'].'" />
						</linearGradient>';
						
					echo '	
						 </defs>';
						 
					// echo  '<rect x="'.$xl=($XD-180).'" y="'.$Fy = ($Farbverlauf_Y).'" width="20px" height="100px" style="fill: url(#IndikatorenFarbbereich)" stroke="#555555" stroke-width="1" />';
					echo  '<rect x="'.$xl=($XD-180).'" y="'.$Fy = ($Farbverlauf_Y).'" width="20px" height="100px" style="fill: url(#IndikatorenFarbbereich_horizontal_vergleich_L)" />'; 
					echo  '<rect x="'.$xl=($XD-180).'" y="'.$Fy = ($Farbverlauf_Y+50).'" width="20px" height="50px" style="fill: url(#IndikatorenFarbbereich_horizontal_vergleich_L_2)" />'; 
					echo  '<text x="'.$xl=($XD-150).'" y="'.$Fy = ($Farbverlauf_Y+5).'" style="font-size:11px; font-family:Arial;">'.
																		number_format($_SESSION['Dokument']['Fuellung']['Indikator_Wert_max']-1000000000,$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.')
																		.'</text>';
																		
					// Hinweis auf deutschlandweites Wertespektrum, wenn gewählt
					/* if($_SESSION['Dokument']['indikator_lokal'] == '0')
					{
						echo  '<text x="'.$xl=($XD-150).'" y="'.$Fy = ($Farbverlauf_Y+25).'" style="font-size:11px; font-family:Arial;">'.utf8_encode('Hinweis:').'</text>';
						echo  '<text x="'.$xl=($XD-150).'" y="'.$Fy = ($Farbverlauf_Y+40).'" style="font-size:11px; font-family:Arial;">'.utf8_encode('Das Wertespektrum bezieht').'</text>';
						echo  '<text x="'.$xl=($XD-150).'" y="'.$Fy = ($Farbverlauf_Y+55).'" style="font-size:11px; font-family:Arial;">'.utf8_encode('sich auf das gesamte Gebiet').'</text>';
						echo  '<text x="'.$xl=($XD-150).'" y="'.$Fy = ($Farbverlauf_Y+70).'" style="font-size:11px; font-family:Arial;">'.utf8_encode('der Bundesrepublik.').'</text>';
					} */
					echo  '<text x="'.$xl=($XD-150).'" y="'.$Fy = ($Farbverlauf_Y+100).'" style="font-size:11px; font-family:Arial;">'.
																		number_format($_SESSION['Dokument']['Fuellung']['Indikator_Wert_min']-1000000000,$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.')
																		.'</text>';  
					
					// Boxen für Verortungs-Anzeige bei Mouseover über Objekte
					for($i_box = 0; $i_box <= 100 ; $i_box++)
					{
						echo  '<rect id="v_'.$i_box.'" x="'.$xl=($XD-181).'" y="'.$Fy = ($Farbverlauf_Y + 100 - $i_box).'" width="22px" height="1px" style="fill:none;" stroke-width="1" stroke="none" />'; 
					}
					
					
					$klasse_Y = $Farbverlauf_Y + 120;
					/* // Darstellung für leere Objekte, falls vorhanden
					if($Leerwerte_vorhanden == 1)
					{	 
						echo '<rect id="f_leer" x="'.$xl=($XD-180).'" y="'.$klasse_Y.'" width="20px" height="15px" fill="url(#leerschraff_legende)" stroke="none" stroke-width="2" />'; 
						echo  '<text x="'.$xl=($XD-150).'" y="'.$ky = ($klasse_Y+11).'" style="font-size:11px; font-family:Arial;">keine Werte vorhanden</text>';
						$klasse_Y = $klasse_Y + 16;
					}
					
					if(is_array($Fehlerwerte_vorhanden_Array))
					{
						foreach($Fehlerwerte_vorhanden_Array as $FCod)
						{
							echo '<rect id="f_'.$_SESSION['Dokument']['Fuellung']['Fehlercodes'][$FCod]['FEHLERCODE']
							.'"  x="'.$xl=($XD-180).'" y="'.$klasse_Y.'" width="20px" height="15px" fill="url(#errschraff'.$_SESSION['Dokument']['Fuellung']['Fehlercodes'][$FCod]['FEHLERCODE']
							.'_legende)" stroke="none" stroke-width="2" />'; 
							echo  '<text x="'.$xl=($XD-150).'" y="'.$ky = ($klasse_Y+11).'" style="font-size:11px; font-family:Arial;">'.$_SESSION['Dokument']['Fuellung']['Fehlercodes'][$FCod]['FEHLER_NAME']
							.'</text>';
							$klasse_Y = $klasse_Y + 16;
						}
					} */
			
			
		}
		
		// Darstellung für leere Objekte, falls vorhanden (sollte nicht mehr vorkommen!)
		if($Leerwerte_vorhanden == 1)
		{
			$klasse_Y = $klasse_Y + 16;	 
			echo '<rect id="f_leer" x="'.$xl=($XD-180).'" y="'.$klasse_Y.'" width="15px" height="10px" fill="url(#leerschraff_legende)" stroke="none" stroke-width="2" />'; 
			echo  '<text x="'.$xF=($XD-160).'" y="'.$ky = ($klasse_Y+8).'" style="font-size:9px; font-family:Arial;">keine Werte vorhanden</text>';
		}
		
		// Vorkommende Fehlerklassen abbilden			
		if(is_array($Fehlerwerte_vorhanden_Array))
		{
			foreach($Fehlerwerte_vorhanden_Array as $FCod)
			{
				$klasse_Y = $klasse_Y + 16;
				echo '<rect id="f_'.$_SESSION['Dokument']['Fuellung']['Fehlercodes'][$FCod]['FEHLERCODE']
				.'" x="'.$xl=($XD-180).'" y="'.$klasse_Y.'" width="15px" height="10px" fill="url(#errschraff'.$_SESSION['Dokument']['Fuellung']['Fehlercodes'][$FCod]['FEHLERCODE']
				.'_legende)" stroke="none" stroke-width="2" />'; 
				echo  '<text x="'.$xF=($XD-160).'" y="'.$ky = ($klasse_Y+8).'" style="font-size:9px; font-family:Arial;">'.$_SESSION['Dokument']['Fuellung']['Fehlercodes'][$FCod]['FEHLER_NAME']
				.'</text>';
			}
		}
		
		
		
		// Linienelemente und Konturen
		// ---------------------------
		$klasse_Y = $klasse_Y+24; // $klasse_Y wird direkt aus den Füllungsberechnungen übernommen und um einen bestimmten Wert geändert
		// $x1 wird direkt aus den Füllungsberechnungen übernommen
		
		// Objektkontur
		// bei bld & gem zusammen gewählt, nicht anzeigen!  <= da in Karte bei dirser speziellen kombination unterdrückt
		if(($_SESSION['Dokument']['Raumebene']['Bundesland']['View'] != '1' or $_SESSION['Dokument']['Raumgliederung'] != 'gem') and $_SESSION['Dokument']['Raumgliederung'] != 'rst' 
		and $_SESSION['Dokument']['Raumgliederung'] != 'r05' and $_SESSION['Dokument']['Raumgliederung'] != 'r10')
		{
			echo '<rect id="legende_kontur"  x="'.$xl=($XD-$xv-20).'" y="'.$y_kont = ($klasse_Y-3).'" width="15px" height="1px" fill="#'
			.$_SESSION['Dokument']['Strichfarbe'].'" stroke="none" stroke-width="2" />'; 
			
			/* ergibt leider nur sinnfreie Ausgaben
			// Stringverarbeitung für Sondergebiete (Löschen von Zus. Textbausteinen)
			if($_SESSION['Dokument']['Raumgliederung_Ausgabe'][0] == '*') 
			{
				$_Raumgliederung_Legendentext = substr($_SESSION['Dokument']['Raumgliederung_Ausgabe'],6);
			}
			else
			{
				$_Raumgliederung_Legendentext = $_SESSION['Dokument']['Raumgliederung_Ausgabe'];
			} 
			echo  '<text x="'.$xl=($XD-$xv).'" y="'.$klasse_Y.'" style="font-size:9px; font-family:Arial;">Grenzen '.$_Raumgliederung_Legendentext.'</text>';
			
			*/
			
			// Bessere Methode, aber weniger dynamisch
			if($_SESSION['Dokument']['Sprache'] == 'DE')
			{
				if($_SESSION['Dokument']['Raumgliederung'] == 'bld') $_Raumgliederung_Legendentext = utf8_encode("Bundeslandgrenzen");
				if($_SESSION['Dokument']['Raumgliederung'] == 'krs') $_Raumgliederung_Legendentext = utf8_encode("Kreisgrenzen");
				if($_SESSION['Dokument']['Raumgliederung'] == 'kfs') $_Raumgliederung_Legendentext = utf8_encode("Grenzen kreisfreier Städte");
				if($_SESSION['Dokument']['Raumgliederung'] == 'lks') $_Raumgliederung_Legendentext = utf8_encode("Landkreisgrenzen");
				if($_SESSION['Dokument']['Raumgliederung'] == 'gem') $_Raumgliederung_Legendentext = utf8_encode("Gemeindegrenzen");
				if($_SESSION['Dokument']['Raumgliederung'] == 'vwg') $_Raumgliederung_Legendentext = utf8_encode("Gemeindeverbandsgrenzen");
				if($_SESSION['Dokument']['Raumgliederung'] == 'ror') $_Raumgliederung_Legendentext = utf8_encode("Raumordnungsregionsgrenzen");
				if($_SESSION['Dokument']['Raumgliederung'] == 'g50') $_Raumgliederung_Legendentext = utf8_encode("Stadtgrenzen");
				if($_SESSION['Dokument']['Raumgliederung'] == 'stt') $_Raumgliederung_Legendentext = utf8_encode("Stadtteilgrenzen");
			}
			if($_SESSION['Dokument']['Sprache'] == 'EN')
			{
				if($_SESSION['Dokument']['Raumgliederung'] == 'bld') $_Raumgliederung_Legendentext = utf8_encode("State boundary");
				if($_SESSION['Dokument']['Raumgliederung'] == 'krs') $_Raumgliederung_Legendentext = utf8_encode("District boundary");
				if($_SESSION['Dokument']['Raumgliederung'] == 'kfs') $_Raumgliederung_Legendentext = utf8_encode("District boundary");
				if($_SESSION['Dokument']['Raumgliederung'] == 'lks') $_Raumgliederung_Legendentext = utf8_encode("District boundary");
				if($_SESSION['Dokument']['Raumgliederung'] == 'gem') $_Raumgliederung_Legendentext = utf8_encode("Municipal boundary");
				if($_SESSION['Dokument']['Raumgliederung'] == 'vwg') $_Raumgliederung_Legendentext = utf8_encode("Boundary of municipal associations");
				if($_SESSION['Dokument']['Raumgliederung'] == 'ror') $_Raumgliederung_Legendentext = utf8_encode("Spatial planning regions boundary");
				if($_SESSION['Dokument']['Raumgliederung'] == 'g50') $_Raumgliederung_Legendentext = utf8_encode("City boundary");
				if($_SESSION['Dokument']['Raumgliederung'] == 'stt') $_Raumgliederung_Legendentext = utf8_encode("Quarter boundary");
			}
			echo  '<text x="'.$xl=($XD-$xv).'" y="'.$klasse_Y.'" style="font-size:9px; font-family:Arial;">'.$_Raumgliederung_Legendentext.'</text>';
		}
		
		//Sprachdefinitionen zur Ausgabe der Zusatzebenen in Legende
		if($_SESSION['Dokument']['Sprache'] == 'DE')
			{
				if($_SESSION['Dokument']['zusatz_gemeinde']) $_Zusatz_gem_Legendentext = utf8_encode("Gemeindegrenzen");
				if($_SESSION['Dokument']['zusatz_kreis']) $_Zusatz_krs_Legendentext = utf8_encode("Kreisgrenzen");
				if($_SESSION['Dokument']['zusatz_ror']) $_Zusatz_ror_Legendentext = utf8_encode("Raumordnungsregionsgrenzen");
				if($_SESSION['Dokument']['zusatz_bundesland']) $_Zusatz_bld_Legendentext = utf8_encode("Bundeslandgrenzen");
				if($_SESSION['Dokument']['zusatz_bab']) $_Zusatz_bab_Legendentext = utf8_encode("Autobahnnetz (Stand 2015)");
				if($_SESSION['Dokument']['zusatz_db']) $_Zusatz_db_Legendentext = utf8_encode("Fernbahnnetz (Stand 2016)");
				if($_SESSION['Dokument']['zusatz_gew']) $_Zusatz_gew_Legendentext = utf8_encode("Gewässer");
			}
			if($_SESSION['Dokument']['Sprache'] == 'EN')
			{
				if($_SESSION['Dokument']['zusatz_gemeinde']) $_Zusatz_gem_Legendentext = utf8_encode("Municipal boundary");
			  if($_SESSION['Dokument']['zusatz_kreis']) $_Zusatz_krs_Legendentext = utf8_encode("District boundary");
			  if($_SESSION['Dokument']['zusatz_ror']) $_Zusatz_ror_Legendentext = utf8_encode("Spatial planning regions boundary");
			  if($_SESSION['Dokument']['zusatz_bundesland']) $_Zusatz_bld_Legendentext = utf8_encode("State boundary");
			  if($_SESSION['Dokument']['zusatz_bab']) $_Zusatz_bab_Legendentext = utf8_encode("Motorway network (as at 2015)");
			  if($_SESSION['Dokument']['zusatz_db']) $_Zusatz_db_Legendentext = utf8_encode("Intercity railway (as at 2016)");
			  if($_SESSION['Dokument']['zusatz_gew']) $_Zusatz_gew_Legendentext = utf8_encode("Running water");
			}
		
		// Zusatz Gemeindegrenzen
		if($_SESSION['Dokument']['zusatz_gemeinde'])
		{
			$klasse_Y = $klasse_Y+16;
			echo '<rect id="legende_kontur"  x="'.$xl=($XD-$xv-20).'" y="'.$y_kont = ($klasse_Y-4).'" width="15px" height="4px" fill="#CCCCCC" stroke="none" stroke-width="2" />'; 
			echo '<rect id="legende_kontur"  x="'.$xl=($XD-$xv-20).'" y="'.$y_kont = ($klasse_Y-3).'" width="15px" height="2px" fill="#'.$_SESSION['Dokument']['Strichfarbe_ZE_VG']
			.'" stroke="none" stroke-width="2" />'; 
			echo  '<text x="'.$xl=($XD-$xv).'" y="'.$klasse_Y.'" style="font-size:9px; font-family:Arial;">'.$_Zusatz_gem_Legendentext.'</text>';
		}
		
		// Zusatz Kreisgrenzen
		if($_SESSION['Dokument']['zusatz_kreis'])
		{
			$klasse_Y = $klasse_Y+16;
			echo '<rect id="legende_kontur"  x="'.$xl=($XD-$xv-20).'" y="'.$y_kont = ($klasse_Y-4).'" width="15px" height="4px" fill="#CCCCCC" stroke="none" stroke-width="2" />'; 
			echo '<rect id="legende_kontur"  x="'.$xl=($XD-$xv-20).'" y="'.$y_kont = ($klasse_Y-3).'" width="15px" height="2px" fill="#'.$_SESSION['Dokument']['Strichfarbe_ZE_VG']
			.'" stroke="none" stroke-width="2" />'; 
			echo  '<text x="'.$xl=($XD-$xv).'" y="'.$klasse_Y.'" style="font-size:9px; font-family:Arial;">'.$_Zusatz_krs_Legendentext.'</text>';
		}
		
		// Zusatz Raumordnungsregionen
		if($_SESSION['Dokument']['zusatz_ror'])
		{
			$klasse_Y = $klasse_Y+16;
			echo '<rect id="legende_kontur"  x="'.$xl=($XD-$xv-20).'" y="'.$y_kont = ($klasse_Y-4).'" width="15px" height="4px" fill="#CCCCCC" stroke="none" stroke-width="2" />'; 
			echo '<rect id="legende_kontur"  x="'.$xl=($XD-$xv-20).'" y="'.$y_kont = ($klasse_Y-3).'" width="15px" height="2px" fill="#'.$_SESSION['Dokument']['Strichfarbe_ZE_VG']
			.'" stroke="none" stroke-width="2" />'; 
			echo  '<text x="'.$xl=($XD-$xv).'" y="'.$klasse_Y.'" style="font-size:9px; font-family:Arial;">'.$_Zusatz_ror_Legendentext.'</text>';
		}
			
		// Zusatz Bundeslandgrenzen
		if($_SESSION['Dokument']['zusatz_bundesland'])
		{
			$klasse_Y = $klasse_Y+16;
			echo '<rect id="legende_kontur"  x="'.$xl=($XD-$xv-20).'" y="'.$y_kont = ($klasse_Y-4).'" width="15px" height="4px" fill="#CCCCCC" stroke="none" stroke-width="2" />'; 
			echo '<rect id="legende_kontur"  x="'.$xl=($XD-$xv-20).'" y="'.$y_kont = ($klasse_Y-3).'" width="15px" height="2px" fill="#'.$_SESSION['Dokument']['Strichfarbe_ZE_VG']
			.'" stroke="none" stroke-width="2" />'; 
			echo  '<text x="'.$xl=($XD-$xv).'" y="'.$klasse_Y.'" style="font-size:9px; font-family:Arial;">'.$_Zusatz_bld_Legendentext.'</text>';
		}
		
		
		
		// Zusatz BAB
		if($_SESSION['Dokument']['zusatz_bab'])
		{
			$klasse_Y = $klasse_Y+16;
			
			// Kleinmaßstäbige Karten
			if($_SESSION['Dokument']['Raumebene_NAME_Auswertung'] == "Deutschland")
			{
				
				echo '<rect id="legende_kontur"  x="'.$xl=($XD-$xv-20).'" y="'.$y_kont = ($klasse_Y-3).'" width="15px" height="1px" fill="#'.$_SESSION['Dokument']['Strichfarbe_BAB']
				.'" stroke="none" stroke-width="1" />'; 
				echo  '<text x="'.$xl=($XD-$xv).'" y="'.$klasse_Y.'" style="font-size:9px; font-family:Arial;">'.$_Zusatz_bab_Legendentext.'</text>';
			}
			else
			{
				// Großmaßstäbliche Anzeige
				echo '<rect id="legende_kontur"  x="'.$xl=($XD-$xv-20).'" y="'.$y_kont = ($klasse_Y-4).'" width="15px" height="3px" fill="#'.$_SESSION['Dokument']['Strichfarbe_BAB_Kontur']
				.'" stroke="none" stroke-width="1" />'; 
				echo '<rect id="legende_kontur"  x="'.$xl=($XD-$xv-20).'" y="'.$y_kont = ($klasse_Y-3).'" width="15px" height="1px" fill="#'.$_SESSION['Dokument']['Strichfarbe_BAB']
				.'" stroke="none" stroke-width="1" />'; 
				echo  '<text x="'.$xl=($XD-$xv).'" y="'.$klasse_Y.'" style="font-size:9px; font-family:Arial;">'.$_Zusatz_bab_Legendentext.'</text>';
	
			}
		}
		
		// Zusatz Fernbahnnetz
		if($_SESSION['Dokument']['zusatz_db'])
		{
			$klasse_Y = $klasse_Y+16;
			echo '<rect id="legende_kontur"  x="'.$xl=($XD-$xv-20).'" y="'.$y_kont = ($klasse_Y-3).'" width="15px" height="2px" fill="#'.$_SESSION['Dokument']['Strichfarbe_db']
			.'" stroke="none" stroke-width="1" />'; 
			echo  '<text x="'.$xl=($XD-$xv).'" y="'.$klasse_Y.'" style="font-size:9px; font-family:Arial;">'.$_Zusatz_db_Legendentext.'</text>';
		}
	
		
		
		/* 
		// Zusatz Hauptgewässer
		if($_SESSION['Dokument']['zusatz_gew'])
		{
			$klasse_Y = $klasse_Y+16;
			echo '<rect id="legende_kontur"  x="'.$xl=($XD-$xv-20).'" y="'.$y_kont = ($klasse_Y-3).'" width="15px" height="1px" fill="#'.$_SESSION['Dokument']['Strichfarbe_GEW']
			.'" stroke="none" stroke-width="1" />'; 
			echo  '<text x="'.$xl=($XD-$xv).'" y="'.$klasse_Y.'" style="font-size:9px; font-family:Arial;">'.utf8_encode('Gewässer').'</text>';
		} 
		*/
		
		// Zusatz Gewässer
		if($_SESSION['Dokument']['zusatz_gew'])
		{
			$klasse_Y = $klasse_Y+16;
			echo '<rect id="legende_kontur"  x="'.$xl=($XD-$xv-20).'" y="'.$y_kont = ($klasse_Y-3).'" width="15px" height="2px" fill="#'.$_SESSION['Dokument']['Strichfarbe_GEW']
			.'" stroke="none" stroke-width="1" />'; 
			echo  '<text x="'.$xl=($XD-$xv).'" y="'.$klasse_Y.'" style="font-size:9px; font-family:Arial;">'.$_Zusatz_gew_Legendentext.'</text>';
		}




		// Abschluss der Legendenebene
		echo  '</g>';
		
		
		
		
		
		
		
		
		/* Histogramm normal und für Vergleichskarten anzeigen: */
				
		echo  '<g>
				<defs>';
				
		if(!$Vergleichskarte) // --- Schalter für Vergleichskartenanzeige ---
		{
			echo '		
						<linearGradient id="IndikatorenFarbbereich_horizontal" x1="100%" x2="0%" y1="0" y2="0">
							<stop offset="0%" stop-color="#'.$_SESSION['Dokument']['Fuellung']['Farbwert_max'].'" />
							<stop offset="100%" stop-color="#'.$_SESSION['Dokument']['Fuellung']['Farbwert_min'].'" />
						</linearGradient>';
		}
		
		if($Vergleichskarte) // --- Schalter für Vergleichskartenanzeige ---
		{
			echo '				
						<linearGradient id="IndikatorenFarbbereich_horizontal_vergleich" x1="100%" x2="0%" y1="0" y2="0">
							<stop offset="0%" stop-color="#'.$_SESSION['Dokument']['Fuellung']['Farbwert_Diff_max'].'" />
							<stop offset="50%" stop-color="#FFFFFF" />
						</linearGradient>
						<linearGradient id="IndikatorenFarbbereich_horizontal_vergleich_2" x1="100%" x2="0%" y1="0" y2="0">
							<stop offset="0%" stop-color="#FFFFFF" />
							<stop offset="100%" stop-color="#'.$_SESSION['Dokument']['Fuellung']['Farbwert_Diff_min'].'" />
						</linearGradient>';
						
		}
		
		echo '
		        </defs>';
		
		
		
		// Variablendeklaration und Gestaltungselemente
		$Hist_Box_Hoehe = 40;
		$Hist_Box_Schrittweite = 1.5;			 
		echo  '<text x="'.$xl=($XD-172).'" y="'.$PosUHist =($YD_gesamt - 263).'" style="font-size:9px; font-family:Arial;">'.utf8_encode($Histogramm).'</text>';			 
					 
		
		
		if(!$Vergleichskarte) // --- Schalter für Vergleichskartenanzeige ---
		{
			
				
				// Info zur Klassifikation in der Legende einblenden
				// Nur zeigen, wenn Y-Position bis Y px nicht von Legendeninhalten überdeckt wird
				
				if($klasse_Y < 500)
				{
					if($_SESSION['Dokument']['Fuellung']['Typ'] == "Klassifizierte Farbreihe") 
					{
						$Klass_Ausgabe_a = 'Automatische Klasseneinteilung';
						if(!$_SESSION['Dokument']['Fuellung']['Untertyp'] or $_SESSION['Dokument']['Fuellung']['Untertyp'] == "haeufigkeit") $Klass_Ausgabe_b = 'gleicher Klassenbesetzung';
						if($_SESSION['Dokument']['Fuellung']['Untertyp'] == "gleich")  $Klass_Ausgabe_b = 'gleicher Klassenbreite';
					}
					if($_SESSION['Dokument']['Fuellung']['Typ'] == "Farbbereich")
					{
						 $Klass_Ausgabe_a = 'Farbskala: Min. bis Max.';
						if($_SESSION['Dokument']['indikator_lokal'] == '1') $Klass_Ausgabe_b = 'Normierung:  Kartenausschnitt';
						if($_SESSION['Dokument']['indikator_lokal'] != '1') $Klass_Ausgabe_b = 'Normierung:  Deutschlandweit';
					}
					if($_SESSION['Dokument']['Fuellung']['Typ'] == "manuell Klassifizierte Farbreihe") $Klass_Ausgabe_a = 'Manuelle Klasseneinteilung';
					if($_SESSION['Dokument']['Fuellung']['Typ'] == "manuell Klassifizierte Farbreihe" and $_SESSION['Dokument']['Fuellung']['ManUntertyp'] == "simpel") 
					{
						$Klass_Ausgabe_a = 'Manell fixierte Klasseneinteilung';
					}
					
					$Inf_Y = $PosUHist =($YD_gesamt - 305);
					if($_SESSION['Dokument']['Sprache'] == 'DE')
					{
						echo  '<text x="'.$xl=($XD-175).'" y="'.$Inf_Y.'" style="font-size:10px; font-weight:bold; font-family:Arial;">Klassifikationsmethode:</text>';
					}
					$Inf_Y = $Inf_Y+12;
					if($_SESSION['Dokument']['Sprache'] == 'DE')
					{
						echo  '<text x="'.$xl=($XD-175).'" y="'.$Inf_Y.'" style="font-size:10px; font-weight:bold; font-family:Arial;">'.$Klass_Ausgabe_a.'</text>';
					}
					if($Klass_Ausgabe_b and $_SESSION['Dokument']['Sprache'] == 'DE')
					{
						$Inf_Y = $Inf_Y+12;
						echo  '<text x="'.$xl=($XD-175).'" y="'.$Inf_Y.'" style="font-size:10px; font-weight:bold;font-family:Arial;">'.$Klass_Ausgabe_b.'</text>';
					}
				}
				
				

				
				
				
				
				
				
				switch ($_SESSION['Dokument']['Fuellung']['Typ']) {
					case 'Farbbereich':
					
						// Box rechts ein Pix. breiter für bessere Darestellung
						echo  '<rect x="'.$xl=($XD-171).'" y="'.$PosUHist =($YD_gesamt - 253).'" 
															width="'.$HBBreite = ($Hist_Box_Schrittweite*102.5).'" 
															height="'.$HBH = ($Hist_Box_Hoehe + 4).'px" 
															style="fill: url(#IndikatorenFarbbereich_horizontal)" 
															stroke="none" />'; 
																				
					break;
					case 'Klassifizierte Farbreihe':
					
						// Einzelne Rechtecke zeichnen	
						for($i=0 ; $i <= 100 ; $i++)
						{
							echo  '<rect x="'.$xl=($XD-171+($i*$Hist_Box_Schrittweite)).'" y="'.$PosUHist =($YD_gesamt - 252).'" width="'.$HBSw = ($Hist_Box_Schrittweite + 1.5).'px" 
																																height="'.$HBH = ($Hist_Box_Hoehe + 2).'" style="fill:#';
							// Korrekten Farbwert (Klasse) ermitteln																									
							if(is_array($_SESSION['Temp']['Klasse']))
							{
								foreach($_SESSION['Temp']['Klasse'] as $Klassensets)
								{
									if(($Klassensets['Untergrenze']==0 and $i==0) or ($Klassensets['Untergrenze'] < $i and $Klassensets['Obergrenze'] >= $i))
									{
										echo  $Klassensets['Farbwert'];
									}
								}
							}
							echo  '" stroke="none" />'; 
						}

					break;
					case 'manuell Klassifizierte Farbreihe':
					
						// Einzelne Rechtecke zeichnen	
						for($i=0 ; $i <= 100 ; $i++)
						{
							echo  '<rect x="'.$xl=($XD-171+($i*$Hist_Box_Schrittweite)).'" y="'.$PosUHist =($YD_gesamt - 252).'" width="'.$HBSw = ($Hist_Box_Schrittweite + 1.5).'px" 
																																height="'.$HBH = ($Hist_Box_Hoehe + 2).'" style="fill:#';
							// Korrekten Farbwert (Klasse) ermitteln																									
							if(is_array($_SESSION['Temp']['manuelle_Klasse']))
							{
								foreach($_SESSION['Temp']['manuelle_Klasse'] as $Klassensets)
								{
									if(($Klassensets['Untergrenze']==0 and $i==0) or ($Klassensets['Untergrenze'] < $i and $Klassensets['Obergrenze'] >= $i))
									{
										echo  $Klassensets['Farbwert'];
									}
								}
							}
							echo  '" stroke="none" />'; 
						}
					
					break;
				}
		}
		else
		{
			
			
			// ----- Vergleichskartenhistogramm ------
			
			// Box rechts ein Pix. breiter für bessere Darestellung
			echo  '<rect x="'.$xl=($XD-171).'" y="'.$PosUHist =($YD_gesamt - 253).'" 
															width="'.$HBBreite = ($Hist_Box_Schrittweite*102.5).'" 
															height="'.$HBH = ($Hist_Box_Hoehe + 4).'px" 
															style="fill: url(#IndikatorenFarbbereich_horizontal_vergleich)" 
															stroke="none" />'; 	
															 										
			// Box rechts ein Pix. breiter für bessere Darestellung
			echo  '<rect x="'.$xl=($XD-171).'" y="'.$PosUHist =($YD_gesamt - 253).'" 
															width="'.$HBBreite = ($Hist_Box_Schrittweite*51).'" 
															height="'.$HBH = ($Hist_Box_Hoehe + 4).'px" 
															style="fill: url(#IndikatorenFarbbereich_horizontal_vergleich_2)" 
															stroke="none" />'; 	
		}
				
				
		// Rahmen
		echo  '<rect x="'.$xl=($XD-175.5).'" y="'.$PosUHist =($YD_gesamt - 257).'" width="'.$HBBreite = ($Hist_Box_Schrittweite*108).'" 
																				height="'.$HBH = ($Hist_Box_Hoehe + 12).'px" style="fill:none;" stroke="#555555" stroke-width="0.5px" />'; 
		// Säulen einzeichnen	
		for($i=0 ; $i <= 100 ; $i++)
		{
			$Saeulen_Hoehe = @($Hist_Box_Hoehe*($_SESSION['Temp']['i_Verteilung'][$i]/$_SESSION['Temp']['i_Verteilung']['Max'])); // normalisieren der Werte auf x/30
			$SäulenAnfang_oben = $Hist_Box_Hoehe - $Saeulen_Hoehe;					
			echo  '<rect x="'.$xl=($XD-169.5 + ($i * $Hist_Box_Schrittweite)).'" y="'.$PosUHist =($YD_gesamt - 250 + $SäulenAnfang_oben).'" width="1px" height="'.$Saeulen_Hoehe.'" 
																																				style="fill:#000000" stroke="none" />'; 
		}
				
				
		echo  '</g>';		
		
		
		
		
		
		
		
		
		// Bei Vergleichskarten vorerst keine Aktualität ausgeben
		if(!$Vergleichskarte)
		{
			// Aktualität anzeigen (standardmäßig versteckt, wird aber per JScript ein-/ausgeblendet)
			echo  '<g id="grundaktlegende" display="none">
					<rect x="'.$xl=($XD-187).'" y="148" width="183px" height="'.$hl=($YD-44).'px" fill="#FFFFFF" stroke="none" />
					<text x="'.$xl=($XD-180).'" y="165" dx="" dy="" style="font-size:12px; font-weight:bold; font-family:Arial;" fill="#444444" >'.$Grundaktualitaet_Legende.'</text>
					<text x="'.$xl=($XD-180).'" y="185" dx="" dy="" style="font-size:10px; font-weight:bold; font-family:Arial;" fill="#444444" >'.$Grundaktualitaet_Legende_Untertitel.'</text>';
					
					$Akt_klasse_Y = $k_Y; // Y-Position
					
					for( $i_akt = 0 ; $i_akt <= $Akt_max ; $i_akt++ )
					{
						if($Akt_Differenz_in_karte_vorhanden[$i_akt] or $Akt_Differenz_in_karte_vorhanden[$i_akt]=="0") // Check auf wirkliche Verwendung in der Karte (Variable von Datenerfassung verfügbar)
						{
							echo '<rect x="'.$xl=($XD-180).'" y="'.$Akt_klasse_Y.'" width="15px" height="10px" fill="#'.$FCA_Jahr[$i_akt].'" stroke="none" />'; 
							//if($i_akt == 1) { $AktEinheit = "Jahr"; }else{ $AktEinheit = "Jahre"; }
							//echo  '<text x="'.$xl=($XD-160).'" y="'.$ky = ($Akt_klasse_Y+8).'" style="font-size:9px; font-family:Arial;">'.$i_akt.' '.$AktEinheit.'</text>';
							echo  '<text x="'.$xl=($XD-160).'" y="'.$ky = ($Akt_klasse_Y+8).'" style="font-size:9px; font-family:Arial;">'.
									$i_akt.' ('.$J_akt=($_SESSION['Dokument']['Jahr_Anzeige']-$i_akt)
								.')</text>';
							$Akt_klasse_Y = $Akt_klasse_Y + 16;
						}
					}
					// Infolink
					echo  '<text x="'.$xl=($XD-180).'" y="'.$ky = ($Akt_klasse_Y+20).'" dx="" dy="" style="font-size:10px; font-weight:bold; font-family:Arial;" fill="#000099" >'.$Grundaktualitaet_Legende_Info.'</text>';
			echo  '</g>'; 
		}
			
	} // Ende unlegend
	

	// im unlegend-Betrieb nicht ausgeben
	if(!$_SESSION['Dokument']['unlegend'])
	{ 
		// Karten-Titel
		// ----------------------------------
		echo  '<g>';
		// HG weiß
		echo  '<rect x="5" y="1" width="'.$X_TitelHG=($XD-10).'px" height="46px" fill="#FFFFFF" stroke="none" opacity="1" />'; 
		// Linie unter Titel
		//echo  '<rect x="5" y="43" width="'.$PosUX=($XD-5).'" height="1px" fill="#999999" stroke="none" />';
		echo  '<rect x="5" y="42" width="'.$PosUX=($XD-5).'" height="2px" fill="#999999" stroke="none" />';
		/* echo  '<rect x="10" y="10" width="100px" height="15px" fill="#9FA8CC" stroke-width="0" opacity="1" />'; // HG links
		
		echo  '<text x="20" y="22" dx="" dy="" style="font-size:12px; font-family:Arial; font-weight:bold;" fill="#FFFFFF" >'.$Kartentitel.'</text>'; */
		// echo  '<text x="15" y="36" dx="" dy="" style="font-size:10px; font-family:Arial;" fill="#444444" >'.$Jahr_Ausgabe.'</text>';
		echo  '<text x="15" y="36" dx="" dy="" style="font-size:10px; font-family:Arial;" fill="#444444" >'.$Raumgliederung_Ausgabe.''.$Datenset.'</text>';
		// Kürzen des Datenset auf Viewerbreite und anhängen von "..."
		if(strlen($Datenset) > 110) $Datenset = substr($Datenset,0,110).",...";
		echo  '<text x="160" y="36" dx="" dy="" style="font-size:10px; font-family:Arial;" fill="#444444" ></text>';
		$_SESSION['Dokument']['titelsize'] = "14";
	} // Ende unlegend
	else
	{
		echo  '<g>';	
	}
	
	
	// Titel anpassen, wenn Vergleichskarte angezeigt
	if(!$Vergleichskarte) // --- Schalter für Vergleichskartenanzeige ---
	{
		echo  '<text id="titel" x="15" y="20" dx="" dy="" style="font-size:'.$_SESSION['Dokument']['titelsize'].'px; font-weight:bold; font-family:Arial;" fill="#444444" >'
		.$_SESSION['Dokument']['Fuellung']['Indikator_Beschreibung']
		.' ('.$_SESSION['Dokument']['Jahr_Anzeige']
		.')</text>';
	}
	else
	{
		echo  '<text id="titel" x="15" y="20" dx="" dy="" style="font-size:'.$_SESSION['Dokument']['titelsize'].'px; font-weight:bold; font-family:Arial;" fill="#444444" >'
		.$_SESSION['Dokument']['Fuellung']['Indikator_Beschreibung']
		.' ('.$_SESSION['temp_vergleichswerte']['Rahmenbedingungen']['KARTENANZEIGE_TYP'].$_SESSION['temp_vergleichswerte']['Rahmenbedingungen']['Jahr_Vergleich'].'-'.$_SESSION['temp_vergleichswerte']['Rahmenbedingungen']['Jahr_Basis'].')</text>';
	}
	


	echo  '</g>';
	// ----------------------------------





// Maßstabsleiste
	// --------------------------------------

	// wenn bestimmte Maßstabsbereiche eintreten, Länge anpassen: 
	
	$m_strecke = 200000; // Standardwert 200km, bei größeren Maßstäben folgendes:
	if($s > 0.0009) {$m_strecke = 100000; $Strich_2_deakt = 1; } // 100km
	if($s > 0.002) {$m_strecke = 50000; $Strich_2_deakt = 1; } // 50km
	if($s > 0.005) {$m_strecke = 10000; $Strich_2_deakt = 1; } // 10km
	if($s > 0.03) {$m_strecke = 1000; $Strich_2_deakt = 1; } //1 km
	
	$m_x=($m_strecke*$s);
	
	echo  '<g id="massstabsleiste" >';
	// Symbolik
	echo  '<rect x="'.$xm=($XD-220-$m_x).'" y="'.$PosUm=($YD_gesamt-202).'" width="'.$m_x.'px" height="1px" stroke="none" fill="#333333" />';
	echo  '<rect x="'.$xm=($XD-220-$m_x).'" y="'.$PosUm=($YD_gesamt-204).'" width="1px" height="3px" stroke="none" fill="#333333" />';
	if(!$Strich_2_deakt) echo  '<rect x="'.$xm=($XD-220-($m_x/2)).'" y="'.$PosUm=($YD_gesamt-204).'" width="1px" height="3px" stroke="none" fill="#333333" />'; //Mittlerer Strich nur bei 200km-Leiste
	echo  '<rect x="'.$xm=($XD-220).'" 	   y="'.$PosUm=($YD_gesamt-204).'" width="1px" height="3px" stroke="none" fill="#333333" />';
	// Beschriftung
	echo  '<text x="'.$xm1=($XD-222-$m_x).'" y="'.$PosUm1=($YD_gesamt-206).'" dx="" dy="" style="font-size:10px; font-family:Arial;" >0</text>';
	echo  '<text x="'.$xm2=($XD-230).'" y="'.$PosUm1=($YD_gesamt-206).'" dx="" dy="" style="font-size:10px; font-family:Arial;" >'.$m_str_km = ($m_strecke/1000).'km</text>';
	echo  '</g>';
	
	// --------------------------------------


// Copyright im Kartenfeld
	echo  '<g id="copyright" >';
	if($_SESSION['Dokument']['Sprache'] == 'DE')
{	
	echo  '<text x="16" y="'.$PosUm1=($YD_gesamt-203).'" dx="" dy="" style="font-size:9px; font-family:Arial;" ><a target="_blank" xlink:href="http://www.ioer.de">'.$Copyright.'</a></text>';
	}
	if($_SESSION['Dokument']['Sprache'] == 'EN')
{
	echo  '<text x="16" y="'.$PosUm1=($YD_gesamt-214).'" dx="" dy="" style="font-size:9px; font-family:Arial;" ><a target="_blank" xlink:href="http://www.ioer.de/1">'.$Copyright_a.'</a></text>';
	echo  '<text x="16" y="'.$PosUm1=($YD_gesamt-203).'" dx="" dy="" style="font-size:9px; font-family:Arial;" ><a target="_blank" xlink:href="http://www.ioer.de/1">'.$Copyright_b.'</a></text>';
}	
	echo  '</g>';


	// im unlegend-Betrieb nicht ausgeben
	if(!$_SESSION['Dokument']['unlegend'])
	{
		// Legendenbox am unteren Kartenrand
			echo  '<rect x="5" y="'.$PosU=($YD_gesamt-198).'" width="'.$PosUX=($XD-5).'" height="2px" fill="#999999" stroke="none" />'; // Begrenzungslinie oben
		
		echo  '<g fill="#444444" opacity="1" >';
	
		echo  '<text x="15" y="'.$PosU=($YD_gesamt-180).'" dx="" dy="" style="font-size:12px; font-family:Arial;" >'.$Erleuterungen.'</text>';
		echo  '<text x="15" y="'.$PosU=($YD_gesamt-167).'" dx="" dy="" style="font-size:10px; font-family:Arial;" >'.$GLOBALS['INFO_VIEWER_ZEILE_1'].'</text>';
		echo  '<text x="15" y="'.$PosU=($YD_gesamt-155).'" dx="" dy="" style="font-size:10px; font-family:Arial;" >'.$GLOBALS['INFO_VIEWER_ZEILE_2'].'</text>';
		echo  '<text x="15" y="'.$PosU=($YD_gesamt-143).'" dx="" dy="" style="font-size:10px; font-family:Arial;" >'.$GLOBALS['INFO_VIEWER_ZEILE_3'].'</text>';
		echo  '<text x="15" y="'.$PosU=($YD_gesamt-131).'" dx="" dy="" style="font-size:10px; font-family:Arial;" >'.$GLOBALS['INFO_VIEWER_ZEILE_4'].'</text>';
		echo  '<text x="15" y="'.$PosU=($YD_gesamt-119).'" dx="" dy="" style="font-size:10px; font-family:Arial;" >'.$GLOBALS['INFO_VIEWER_ZEILE_5'].'</text>';
		echo  '<text x="15" y="'.$PosU=($YD_gesamt-107).'" dx="" dy="" style="font-size:10px; font-family:Arial;" >'.$GLOBALS['INFO_VIEWER_ZEILE_6'].'</text>';
		
		echo  '<text x="15" y="'.$PosU=($YD_gesamt-89).'" dx="" dy="" style="font-size:12px; font-family:Arial;" >'.$Datengrundlage.'</text>';
		// Standard Datengrundlage (BKG ATKIS) anzeigen, wenn in DB hinterlegt (meistens der Fall)
		if($Datengrundl_Inhalt_1)
		{
			echo  '<text x="15" y="'.$PosU=($YD_gesamt-78).'" dx="" dy="" style="font-size:10px; font-family:Arial;" >'.$Datengrundl_Inhalt_1.'</text>';
			
			$PosU_Abzug = 0;
		}
		else
		{
			$PosU_Abzug = 10;	
		}
		echo  '<text x="15" y="'.$PosU=($YD_gesamt-(67+$PosU_Abzug)).'" dx="" dy="" style="font-size:10px; font-family:Arial;" >'.$Datengrundl_Inhalt_1_1.'</text>'; // $Datengrundl_Inhalt_1_1 ... weil um automat. Jahr+1 ergänzt
		echo  '<text x="15" y="'.$PosU=($YD_gesamt-(57+$PosU_Abzug)).'" dx="" dy="" style="font-size:10px; font-family:Arial;" >'.$GLOBALS['DATENGRUNDLAGE_ZEILE_2'].'</text>';
		
		echo  '<text x="15" y="'.$PosU=($YD_gesamt-43).'" dx="" dy="" style="font-size:12px; font-family:Arial;" >'.$Darstellungsgrundlage.':</text>';
		echo  '<text x="15" y="'.$PosU=($YD_gesamt-31).'" dx="" dy="" style="font-size:10px; font-family:Arial;" >'.$Datengrundl_Inhalt_2.'</text>';
		echo  '<text x="15" y="'.$PosU=($YD_gesamt-19).'" dx="" dy="" style="font-size:10px; font-family:Arial;" >'.$Datengrundl_Inhalt_3.'</text>';
	//		echo  '<text x="15" y="'.$PosU=($YD_gesamt-7).'" dx="" dy="" style="font-size:10px; font-family:Arial;" >'.$Kartenprojektion.'</text>';
	
		
			//Quelle mit Link zu detailierten Stadtteilinfos
		if ($_SESSION['Dokument']['Raumgliederung'] == "stt")
		{
			echo  '<text 	id="stadtteil" onmouseover="document.getElementById(\'stadtteil\').setAttributeNS(null,\'text-decoration\',\'underline\');"
			onmouseout="document.getElementById(\'stadtteil\').setAttributeNS(null,\'text-decoration\',\'none\');" 
			fill="blue" x="15" y="'.$PosU=($YD_gesamt-7).'" dx="" dy="" style="font-size:10px; font-family:Arial;" >'.$Stadtteilquelle.'</text>';
		
			
		}	
		
		echo  '</g>';
		
		
		
		echo  '<g><text x="'.$PosX=($XD-175).'" y="'.$PosU_Zeit=($YD_gesamt-330).'" dx="" dy="" style="font-size:9px; font-family:Arial;" >'.				
			$Kartenprojektion.'</text>		
					</g>';
		 echo  '<g><text x="'.$PosX=($XD-175).'" y="'.$PosU_Zeit=($PosU_Zeit+12).'" dx="" dy="" style="font-size:9px; font-family:Arial;" >'.
				utf8_encode("ETRS89 / UTM Zone 32N").'</text>
			   </g>'; 
		
		
		// Rahmen zeichnen
		echo  '<g><rect x="0" y="0" width="'.$XD.'px" height="'.$YD_gesamt.'px" fill="none" stroke="#FFFFFF" stroke-width="10" /></g>'; 
		echo  '<g><rect x="0" y="0" width="'.$XD.'px" height="'.$YD_gesamt.'px" fill="none" stroke="#333333" stroke-width="1" /></g>';
		
		// Trick: nicht sichtbares Bild um URL für Speicherung der Label-Anzeige in $_SESSION-Array versteckt aufrufen zu können (href wird von JScript in jedem Element angepasst)
		// echo  '<g><image x="0" y="0" width="0" height="0" xlink:href="#" id="verstecktegrafik" ></image></g>';
		
		// nur ausgeben, wenn Karte nicht als Datei gespeichert werden soll
		if(!$_SESSION['Dokument']['Dateiausgabe'])
		{
			echo  '<g><image x="0" y="0" width="0" height="0" xlink:href="./icons_viewer/leer_pixel.png" id="verstecktegrafik" ></image></g>';
		}
		
		// Übergabe der Karteninformationen an den Rasterviewer 
		 echo  '<g>
		 <image x="0" y="0" width="0" height="0" xlink:href="https://maps.ioer.de/detailviewer/raster/parameter.php?k='.$_SESSION['Dokument']['Fuellung']['Kategorie'].'" id="verstecktegrafik2" ></image>
		 <image x="0" y="0" width="0" height="0" xlink:href="https://maps.ioer.de/detailviewer/raster/parameter.php?i='.$_SESSION['Dokument']['Fuellung']['Indikator'].'" id="verstecktegrafik3" ></image>
		 <image x="0" y="0" width="0" height="0" xlink:href="https://maps.ioer.de/detailviewer/raster/parameter.php?z='.$_SESSION['Dokument']['Jahr_Anzeige'].'" id="verstecktegrafik4" ></image>
		 <image x="0" y="0" width="0" height="0" xlink:href="https://maps.ioer.de/detailviewer/raster/parameter.php?box='.$_SESSION['Dokument']['X_min_global'].','.$_SESSION['Dokument']['Y_max_global'].','.$_SESSION['Dokument']['X_max_global'].','.$_SESSION['Dokument']['Y_min_global'].'" id="verstecktegrafik5" ></image>
		 
		 </g>'; /**/
		
		 /*echo  '<g><image x="0" y="0" width="0" height="0" xlink:href="https://maps.ioer.de/detailviewer/raster/getparam.php?k='.$_SESSION['Dokument']['Fuellung']['Kategorie'].'&i='.$_SESSION['Dokument']['Fuellung']['Indikator'].'&z='.$_SESSION['Dokument']['Jahr_Anzeige'].'&box='.$_SESSION['Dokument']['X_min_global'].','.$_SESSION['Dokument']['Y_max_global'].','.$_SESSION['Dokument']['X_max_global'].','.$_SESSION['Dokument']['Y_min_global'].'" id="verstecktegrafik2" ></image></g>'; */
		 
		
		// Aktualität
		// --------------
		
		// nur einblenden wenn $_SESSION['Dokument']['Fuellung']['MITTLERE_AKTUALITAET_IGNORE'] nicht gesetzt und keine Vergleichskarte angezeigt werden soll
	if(!$GLOBALS['MITTLERE_AKTUALITAET_IGNORE'] and !$Vergleichskarte and $_SESSION['Dokument']['Jahr_Anzeige']>='2006')
		{
			echo  '<text id="akttitel" 
			onmouseover="document.getElementById(\'akttitel\').setAttributeNS(null,\'text-decoration\',\'underline\');"
			onmouseout="document.getElementById(\'akttitel\').setAttributeNS(null,\'text-decoration\',\'none\');" 
			fill="#444444" x="'.$RPos_GA=($_SESSION['Dokument']['groesse_X']-21).'" y="'.$PosU=($_SESSION['Dokument']['groesse_Y']+92).'" dx="" dy="" style="font-size:12px; font-family:Arial;" >'
			.$Grundaktualitaet_AktVorschau.'</text>';
			
			$FCA_Jahr_min = 3000; // großer Wert benötigt um Min zu ermitteln ... wurde aber an sich weiter oben schon ermittelt !?
			if(is_array($AGS_mit_Aktualitaet_Differenz))
			{
				foreach($AGS_mit_Aktualitaet_Differenz as $minmax_jahr)
				{
					if($minmax_jahr < $FCA_Jahr_min) $FCA_Jahr_min = $minmax_jahr;
					if($minmax_jahr > $FCA_Jahr_max) $FCA_Jahr_max = $minmax_jahr;
				}
			}
			// Skalierungsfaktor für Linke Obere Ecke auf die Ausschnittsgröße berechnen
			$xs_akt = 160/$_SESSION['Dokument']['Width'];
			$ys_akt = 160/$_SESSION['Dokument']['Height'];
			
			if($xs_akt>$ys_akt)
			{ 
				$s_akt = $ys_akt; 
			}
			else
			{ 
				$s_akt = $xs_akt; 
			}
			
			// Weite und Höhe berechnen
			$X_min_akt = -($_SESSION['Dokument']['X_min_global']*$s_akt-$_SESSION['Dokument']['Rand_L']);
			$Y_max_akt = $_SESSION['Dokument']['Y_max_global']*$s_akt+$_SESSION['Dokument']['Rand_O'];
			
			$X_min_akt = $X_min_akt + $XD - 190;
			$Y_max_akt = $Y_max_akt + $YD - 20;
			
			// Aktualität auf neue Ebene im Menübereich (unten rechts) zeichnen
			echo  '<g id="aktualitaet_legende" transform="matrix('.$s_akt.' 0 0 '.$s_akt.' '.$X_min_akt.' '.$Y_max_akt.')" >
			<desc>Min-Jahr='.$FCA_Jahr_min.'  Max-Jahr='.$FCA_Jahr_max.'</desc>'
			.$Aktualitaetslayer_Legende.'</g>';
			
			
			// -------- Aktualitätslegende unten --------
			$Akt_klasse_Y = $k_Y;
			$Pos_GAkt = ($_SESSION['Dokument']['groesse_X']-20);		
			for( $i_akt = 0 ; $i_akt <= $Akt_max ; $i_akt++ )
			{
				if($Akt_Differenz_in_karte_vorhanden[$i_akt] or $Akt_Differenz_in_karte_vorhanden[$i_akt]=="0") // Check auf wirkliche Verwendung in der Karte (Variable von Datenerfassung verfügbar)
				{
					echo '<rect x="'.$xl=($Pos_GAkt).'" y="'.$PosU=($_SESSION['Dokument']['groesse_Y']+100+$Akt_kl_Y).'" width="13px" height="8px" fill="#'.$FCA_Jahr[$i_akt].'" stroke="#555555" stroke-width="0" />'; 
					//if($i_akt == 1) { $AktEinheit = "Jahr"; }else{ $AktEinheit = "Jahre"; }
					//echo  '<text x="'.$xl=($Pos_GAkt + 16).'" y="'.$ky = ($PosU=($_SESSION['Dokument']['groesse_Y']+108+$Akt_kl_Y)).'" style="font-size:9px; font-family:Arial;">'.$i_akt.' '.$AktEinheit.'</text>';
					echo  '<text x="'.$xl=($Pos_GAkt + 16).'" y="'.$ky = ($PosU=($_SESSION['Dokument']['groesse_Y']+108+$Akt_kl_Y)).'" style="font-size:9px; font-family:Arial;">'
							.$i_akt.' ('.$J_akt=($_SESSION['Dokument']['Jahr_Anzeige']-$i_akt)
						.')</text>';
					$Akt_kl_Y = $Akt_kl_Y + 11;	
				}
			}
			
			
			// Teiltransparenter-Decker
			echo  '<rect id="akt_decker" x="'.$RPos_GA=($_SESSION['Dokument']['groesse_X']-20).'" y="'.$PosU=($_SESSION['Dokument']['groesse_Y']+130).'" 
				width="220" height="90" fill="#ffffff" opacity="0.7" stroke="none" 
				display="none" onclick="aktualitaet_einblenden(\'aktualitaet_karte\'); aktualitaet_einblenden(\'grundaktlegende\');"></rect>';
			
			
			// Aktualität versteckt auf Kartenbereich zeichnen und Titel anpassen (Ein-/ Ausblenden Zeile hier darüber onklick)
			echo  '<g id="aktualitaet_karte" display="none" pointer-events="none">
					<g id="akt_karte_karte" transform="matrix('.$s.' 0 0 '.$s.' '.$X_min.' '.$Y_max.')" pointer-events="none">
						'.$Aktualitaetslayer_Karte.'
					</g>
					<g id="akt_karte_titel_decker" pointer-events="none">
						<rect y="5" x="5" width="'.$xT_Aenderg=($XD-10).'" height="20" stroke="none" fill="#ffffff" opacity="1"></rect>
						<text x="15" y="20" dx="" dy="" style="font-size:'.$_SESSION['Dokument']['titelsize'].'px; font-weight:bold; font-family:Arial;" fill="#444444">'.$Grundaktualitaet_AktTitel.'</text>
					</g>
				</g>';
			
			
			
			
			// Info - "Klick"
			/* if($_GET['druck']) ....... war für Permanentanzeige/Klickanzeige ........
			{ */
				/* Version die nur beim Mouseover angezeigt wird:*/
				echo  utf8_encode('<text id="aktklick" fill="#444444" x="'.$RPos_GA=($_SESSION['Dokument']['groesse_X']+30).'" y="'.$PosU=($_SESSION['Dokument']['groesse_Y']+150)
																	.'" dx="" dy="" style="font-size:14px; font-family:Arial; font-weight:bold;" display="none" opacity="0.6" pointer-events="none" >');
																	
				if($_SESSION['Dokument']['Sprache'] == 'DE')
				{
						echo utf8_encode('Ein- und Ausblenden
						<tspan dx="-145" dy="20">der Grundaktualität</tspan>
						<tspan dx="-130" dy="20">im Kartenfenster:</tspan>
						<tspan dx="-115" dy="20">(Hier klicken!)</tspan>
					</text>'); 
				}
				
				if($_SESSION['Dokument']['Sprache'] == 'EN')
				{
						echo utf8_encode('View and hide
						<tspan dx="-145" dy="20">the mean basal topicality</tspan>
						<tspan dx="-130" dy="20">in the map</tspan>
						<tspan dx="-100" dy="20">(Please click here!)</tspan>
					</text>'); 
				}
				
				
			/*}
			 else
			{
				// Permanentanzeige 
				echo  utf8_encode('<text id="aktklick_permanent" fill="#444444" x="'.$RPos_GA=($_SESSION['Dokument']['groesse_X']+25).'" y="'.$PosU=($_SESSION['Dokument']['groesse_Y']+150)
																		.'" dx="" dy="" style="font-size:14px; font-family:Arial; font-weight:bold;" opacity="0.6" pointer-events="none" >
																	Ein- und ausblenden
																	<tspan dx="-145" dy="20">der Grundaktualität</tspan>
																	<tspan dx="-130" dy="20">im Kartenfenster:</tspan>
																	<tspan dx="-115" dy="20">(Hier klicken!)</tspan>
																	</text>');
			} */

			
			// Mouse-Klick-Rechteck
			echo  '<rect x="'.$RPos_GA=($_SESSION['Dokument']['groesse_X']+25).'" y="'.$PosU=($_SESSION['Dokument']['groesse_Y']+100).'" width="175" height="160" fill="#ffffff" opacity="0" stroke="none" 
			id="akt_event" 
			onclick="aktualitaet_einblenden(\'aktualitaet_karte\'); aktualitaet_einblenden(\'grundaktlegende\');"
			onmouseover="document.getElementById(\'aktklick\').setAttributeNS(null,\'display\',\'inline\'); document.getElementById(\'akt_decker\').setAttributeNS(null,\'display\',\'inline\');" 
			onmouseout="document.getElementById(\'aktklick\').setAttributeNS(null,\'display\',\'none\'); document.getElementById(\'akt_decker\').setAttributeNS(null,\'display\',\'none\');" 
			></rect>';
			
			// ---------------
		
			/* ---------------
					onmouseover="document.getElementById(\'akttitel\').setAttributeNS(null,\'text-decoration\',\'underline\');"
			onmouseout="document.getElementById(\'akttitel\').setAttributeNS(null,\'text-decoration\',\'none\');"*/
		}
		elseif (!$GLOBALS['MITTLERE_AKTUALITAET_IGNORE'] and !$Vergleichskarte and $_SESSION['Dokument']['Jahr_Anzeige']<'2006')
		{   //Hinweis statt Grundaktkarte für Zeitschnitte vor 2006
				echo  '<text id="akttitel" 	 
			fill="#444444" x="'.$RPos_GA=($_SESSION['Dokument']['groesse_X']-1).'" y="'.$PosU=($_SESSION['Dokument']['groesse_Y']+92).'" dx="" dy="" style="font-size:12px; font-family:Arial;" >'
			.$Grundaktualitaet_AktVorschau_2000.'</text>
			 <text id="akttitel_b" 			
			fill="#444444" x="'.$RPos_GA=($_SESSION['Dokument']['groesse_X']-1).'" y="'.$PosU=($_SESSION['Dokument']['groesse_Y']+106).'" dx="" dy="" style="font-size:12px; font-family:Arial;" >'
			.$Grundaktualitaet_AktVorschau_2000_b.'</text>
				 <text id="akttitel_c" 	 
			fill="#444444" x="'.$RPos_GA=($_SESSION['Dokument']['groesse_X']-1).'" y="'.$PosU=($_SESSION['Dokument']['groesse_Y']+118).'" dx="" dy="" style="font-size:12px; font-family:Arial;" >'
			.$Grundaktualitaet_AktVorschau_2000_c.'</text>
				 <text id="akttitel_d" 		
			fill="#444444" x="'.$RPos_GA=($_SESSION['Dokument']['groesse_X']-1).'" y="'.$PosU=($_SESSION['Dokument']['groesse_Y']+130).'" dx="" dy="" style="font-size:12px; font-family:Arial;" >'
			.$Grundaktualitaet_AktVorschau_2000_d.'</text>
			 <text id="akttitel_e"  
			fill="#444444" x="'.$RPos_GA=($_SESSION['Dokument']['groesse_X']-1).'" y="'.$PosU=($_SESSION['Dokument']['groesse_Y']+142).'" dx="" dy="" style="font-size:12px; font-family:Arial;" >'
			.$Grundaktualitaet_AktVorschau_2000_e.'</text>';
			
		
		
		
		
		}
		
		
		
	} //Ende unlegend
	
	// Markierungsgeometrien für Vergleichskartenanzeige
	if($Vergleichskarte) // --- Schalter für Vergleichskartenanzeige ---
	{
		echo  '<g id="vergleich_markierung" pointer-events="none">
			<g id="vergleich_markierung_karte" transform="matrix('.$s.' 0 0 '.$s.' '.$X_min.' '.$Y_max.')" pointer-events="none">
				'.$Markierungslayer_Vergleichskarte.'
			</g>
		</g>';
	}


	// Geändert.... nur noch für Raster, sonst zu viele Nebeneffekte: if(!$_SESSION['Dokument']['Zusatzebene_aktiv'])
	// Bei Nutzung von Rasterdaten
	if($_SESSION['Dokument']['Raumgliederung'] != "rst" or $_SESSION['Dokument']['Raumgliederung'] != "r05" or $_SESSION['Dokument']['Raumgliederung'] != "r10") 
	{
		// ---- Anzeige normaler Labels für alles außer Rasterdarstellung ----	
		// Beschriftungen auf eigener Ebene erstellen
		foreach($_SESSION['Datenbestand'] as $DatenSet)
		{
			if($DatenSet['View']!='0' and $DatenSet['View']!='HG')
			{		
				// Ebene anlegen
				echo  '<g id="Beschriftung_'.$DatenSet['NAME'].'">
				<desc>'.$DatenSet['NAME'].' '.$_SESSION['Datenbestand'][$DatenSet['NAME']]['X_min_global'].' '.$_SESSION['Datenbestand'][$DatenSet['NAME']]['Y_max_global'].'</desc>';
				
				$i_array=0;
				while($Ausgabe_Beschriftung_X[$DatenSet['NAME']][$i_array]) // $Ausgabe_Beschriftung_X sollte immer Werte enthalten und ist somit ein sicherer Anhaltspunkt
				{
					// Bestimmen der Anzeige inline/none
					if($_SESSION['Dokument']['LabelAnzeige'][$Ausgabe_Beschriftung_AGS[$DatenSet['NAME']][$i_array]] == '1')
					{
						$LabelDisplay = 'inline';
					}
					else
					{
						$LabelDisplay = 'none';
					}
					
					
					// Transformation allein der Koordinaten (Verschiebung), nicht der Abmessungen
					//$XText=($Ausgabe_Beschriftung_X[$DatenSet['NAME']][$i_array]*$s)+$X_min;
					//$YText=($Ausgabe_Beschriftung_Y[$DatenSet['NAME']][$i_array]*$s)+$Y_max;
					
		
					// Textobjekt bei Bedarf umbrechen und verkleinern
					// --------------------------
					$Label_Längenbegrenzung = 15;
					// --------------------------
					$Label_Text_Original = $Ausgabe_Beschriftung_Text[$DatenSet['NAME']][$i_array];
					
					// Trennung nach " " oder "-" unterscheiden
					if(strstr($Label_Text_Original," ")) $Trennzeichen = " ";
					if(strstr($Label_Text_Original,"-")) $Trennzeichen = "-";
							  
					// Suchen nach evtl. vorh. Bindestrich für Trennung und Trennen des Strings
					$TeilStr[0] = strtok($Label_Text_Original,$Trennzeichen);
					$i_teilstr = 1;
					while($TeilStr[$i_teilstr] = strtok($Trennzeichen)) $i_teilstr++;
					
					// Verbinden der Teilstrings mit Umbruch
					$Label_Text = "";
					$max_teilstrlaenge = 0;
					
					// Längenanalyse (Max) des Einzelstrings für evtl. nachfolgende Fontgrößen-Änderung
					$i_teilstr = 0;
					while($TeilStr[$i_teilstr])
					{
						
						if(strlen($TeilStr[$i_teilstr]) > $max_teilstrlaenge)
						{
							$max_teilstrlaenge = strlen($TeilStr[$i_teilstr]);
							// X-Lagekorrektur für Text vermerken (1 Zeichen = 2 Pixel Verschiebung)
							$X_Lagekorrektur = $max_teilstrlaenge * 2;
							$X_Textlaenge_Check = $max_teilstrlaenge * 5; // vorher 6 ... war zu hoch 
							$X_Textlaenge_Versatz = $max_teilstrlaenge * 6.5; 
							$X_Textlaenge_Versatz_Verkleinerung = $max_teilstrlaenge * 4.8; 
						}
						$i_teilstr++;
					}
					
					// Transformation allein der Koordinaten (Verschiebung), nicht der Abmessungen
					$XText_roh = ($Ausgabe_Beschriftung_X[$DatenSet['NAME']][$i_array]*$s)+$X_min;
					$YText=($Ausgabe_Beschriftung_Y[$DatenSet['NAME']][$i_array]*$s)+$Y_max;
					// Korrektur der X_Lage auf Grund der Textlänge
					$XText = $XText_roh - $X_Lagekorrektur;
					// X-Lagekorrektur für rechten Rand
					if($X_PosLabel = ($XText_roh + $X_Textlaenge_Check) > $_SESSION['Dokument']['groesse_X'])
					{
						$XText = ($_SESSION['Dokument']['groesse_X'] - $X_Textlaenge_Versatz) + 12 ;
					}
					
					// Y-Lagekorrektur bei Überdeckungen
					// Lösung:
					// Evtl. zukünftig durch Hinterlegung (hier in Array) vorheriger Labels prüfen und eine Verschiebung im Rahmen der Möglichkeiten der Box ausführen
					
					
					
					$Label_Verkleinerung = 0;
					if($max_teilstrlaenge > $Label_Längenbegrenzung)
					{
						$Label_Verkleinerung = 3;
						// X-Lagekorrektur für rechten Rand
						if($X_PosLabel = ($XText_roh + $X_Textlaenge_Check) > $_SESSION['Dokument']['groesse_X'])
						{
							$XText = ($_SESSION['Dokument']['groesse_X'] - $X_Textlaenge_Versatz_Verkleinerung) + 15 ;
						}
					}
					

					// Zusammensetzen des neuen Komplett-Labels
					$Trenner_neu = "";
					$X_Umbruch = ' x="'.$XText.'" ';
					$Y_Umbruch = "";
					
					// ... nur noch max 2-zeilig machen!!!
					$i_teilstr = 0;
					$Label_Text = $Label_Text.'<tspan '.$X_Umbruch.' '.$Y_Umbruch.' style="font-size:'.$NF_Size = ($Font_size_Labels-$Label_Verkleinerung).'px;">'
									  .$Trenner_neu.$TeilStr[$i_teilstr].'</tspan>';
					$Trenner_neu = $Trennzeichen;
					$X_Umbruch = ' x="'.$XText.'" ';
					$Y_Umbruch = ' dy="'.$NF_Y = ($Font_size_Labels-$Label_Verkleinerung).'px" ';
					
					$Teilstr_gefuellt = 0;
					$i_teilstr = 1;
					while($TeilStr[$i_teilstr])
					{
						// Anfang 2. Textzeile
						if($i_teilstr == 1) $Label_Text = $Label_Text.'<tspan '.$X_Umbruch.' '.$Y_Umbruch.' style="font-size:'.$NF_Size = ($Font_size_Labels-$Label_Verkleinerung).'px;">';
						// Ihnalt
						$Label_Text = $Label_Text.$Trenner_neu.$TeilStr[$i_teilstr];
						$Trenner_neu = $Trennzeichen;
						$Teilstr_gefuellt = 1;
						$i_teilstr++;
					}
					// Ende z. Textzeile
					if($Teilstr_gefuellt) 	$Label_Text = $Label_Text.'</tspan>';
	
					
					// TextObjekt ausgeben
					echo  '<text id="Label_'
					.$Ausgabe_Beschriftung_AGS[$DatenSet['NAME']][$i_array]
					.'" x="'.$XText
					.'" y="'.$YText
					.'" style="fill:#'.$_SESSION['Dokument']['Textfarbe_Labels'].'; font-weight:bold; font-size:'.$Font_size_Labels.'px; font-family:Arial;" display="'.$LabelDisplay.'" pointer-events="none" >'
					.$Label_Text
					.'</text>'; 
					// in Style integrieren, falls Umrandung gewünscht: stroke:#DDDDDD; stroke-width:0.3px;
					$i_array++;
				}			
				echo  "</g>"; 
			}		
		}
	}
	else
	{
		// ---- Anzeige von Gemeinde-Labels für Rasterdarstellung ----	
		
		// Beschriftungen auf eigener Ebene erstellen ... gibt zZ nur noch eine, also foreach an sich sinnlos, aber noch hier belassen
		foreach($GLOBALS['Ausgabe_Beschriftung']['gem'] as $DatenSet)
		{
	
				// Ebene anlegen
				echo  '<g id="Beschriftung_'.$DatenSet['NAME'].'">
				<desc>'.$DatenSet['NAME'].' '.$_SESSION['Datenbestand'][$DatenSet['NAME']]['X_min_global'].' '.$_SESSION['Datenbestand'][$DatenSet['NAME']]['Y_max_global'].'</desc>';
				
				
					// Bestimmen der Anzeige inline/none
					if($_SESSION['Dokument']['LabelAnzeige'][$DatenSet['NAME']] == '1')
					{
						$LabelDisplay = 'inline';
					}
					else
					{
						$LabelDisplay = 'none';
					}
					
					
					// Transformation allein der Koordinaten (Verschiebung), nicht der Abmessungen
					//$XText=($DatenSet['X']*$s)+$X_min;
					//$YText=($DatenSet['Y']*$s)+$Y_max;
					
		
					// Textobjekt bei Bedarf umbrechen und verkleinern
					// --------------------------
					$Label_Längenbegrenzung = 15;
					// --------------------------
					$Label_Text_Original = $DatenSet['NAME'];
					
					// Trennung nach " " oder "-" unterscheiden
					if(strstr($Label_Text_Original," ")) $Trennzeichen = " ";
					if(strstr($Label_Text_Original,"-")) $Trennzeichen = "-";
							  
					// Suchen nach evtl. vorh. Bindestrich für Trennung und Trennen des Strings
					$TeilStr[0] = strtok($Label_Text_Original,$Trennzeichen);
					$i_teilstr = 1;
					while($TeilStr[$i_teilstr] = strtok($Trennzeichen)) $i_teilstr++;
					
					// Verbinden der Teilstrings mit Umbruch
					$Label_Text = "";
					$max_teilstrlaenge = 0;
					
					// Längenanalyse (Max) des Einzelstrings für evtl. nachfolgende Fontgrößen-Änderung
					$i_teilstr = 0;
					while($TeilStr[$i_teilstr])
					{						
						if(strlen($TeilStr[$i_teilstr]) > $max_teilstrlaenge)
						{
							$max_teilstrlaenge = strlen($TeilStr[$i_teilstr]);
							// X-Lagekorrektur für Text vermerken (1 Zeichen = 2 Pixel Verschiebung)
							$X_Lagekorrektur = $max_teilstrlaenge * 2;
							$X_Textlaenge_Check = $max_teilstrlaenge * 6; 
							$X_Textlaenge_Versatz = $max_teilstrlaenge * 6.5; 
							$X_Textlaenge_Versatz_Verkleinerung = $max_teilstrlaenge * 4.8; 
						}
						$i_teilstr++;
					}
					/* 
					$XText=($DatenSet['X']*$s)+$X_min;
					$YText=($DatenSet['Y']*$s)+$Y_max;
					
					 */
					 
					// Transformation allein der Koordinaten (Verschiebung), nicht der Abmessungen
					$XText_roh = ($DatenSet['X']*$s)+$X_min;
					$YText=($DatenSet['Y']*$s)+$Y_max;
					// Korrektur der X_Lage auf Grund der Textlänge
					$XText = $XText_roh - $X_Lagekorrektur;
					// X-Lagekorrektur für rechten Rand
					if($X_PosLabel = ($XText_roh + $X_Textlaenge_Check) > $_SESSION['Dokument']['groesse_X'])
					{
						$XText = ($_SESSION['Dokument']['groesse_X'] - $X_Textlaenge_Versatz) + 12 ;
					}
					
					// Y-Lagekorrektur bei Überdeckungen
					// Lösung:
					// Evtl. zukünftig durch Hinterlegung (hier in Array) vorheriger Labels prüfen und eine Verschiebung im Rahmen der Möglichkeiten der Box ausführen
					
										
					
					$Label_Verkleinerung = 0;
					if($max_teilstrlaenge > $Label_Längenbegrenzung)
					{
						$Label_Verkleinerung = 3;
						// X-Lagekorrektur für rechten Rand
						if($X_PosLabel = ($XText_roh + $X_Textlaenge_Check) > $_SESSION['Dokument']['groesse_X'])
						{
							$XText = ($_SESSION['Dokument']['groesse_X'] - $X_Textlaenge_Versatz_Verkleinerung) + 15 ;
						}
					}
					
					// Zusammensetzen des neuen Komplett-Labels
					$Trenner_neu = "";
					$X_Umbruch = ' x="'.$XText.'" ';
					$Y_Umbruch = "";
					
					// ... nur noch max 2-zeilig machen!!!
					$i_teilstr = 0;
					$Label_Text = $Label_Text.'<tspan '.$X_Umbruch.' '.$Y_Umbruch.' style="font-size:'.$NF_Size = ($Font_size_Labels-$Label_Verkleinerung).'px;">'
									  .$Trenner_neu.$TeilStr[$i_teilstr].'</tspan>';
					$Trenner_neu = $Trennzeichen;
					$X_Umbruch = ' x="'.$XText.'" ';
					$Y_Umbruch = ' dy="'.$NF_Y = ($Font_size_Labels-$Label_Verkleinerung).'px" ';
					
					$Teilstr_gefuellt = 0;
					$i_teilstr = 1;
					while($TeilStr[$i_teilstr])
					{
						// Anfang 2. Textzeile
						if($i_teilstr == 1) $Label_Text = $Label_Text.'<tspan '.$X_Umbruch.' '.$Y_Umbruch.' style="font-size:'.$NF_Size = ($Font_size_Labels-$Label_Verkleinerung).'px;">';
						// Ihnalt
						$Label_Text = $Label_Text.$Trenner_neu.$TeilStr[$i_teilstr];
						$Trenner_neu = $Trennzeichen;
						$Teilstr_gefuellt = 1;
						$i_teilstr++;
					}
					// Ende z. Textzeile
					if($Teilstr_gefuellt) 	$Label_Text = $Label_Text.'</tspan>';
	
					
					// TextObjekt ausgeben
					echo  '<text id="Label_'
					.$DatenSet['NAME']
					.'" x="'.$XText
					.'" y="'.$YText
					.'" style="fill:#'.$_SESSION['Dokument']['Textfarbe_Labels'].'; font-weight:bold; font-size:'.$Font_size_Labels.'px; font-family:Arial;" display="'.$LabelDisplay.'" pointer-events="none" >'
					.$Label_Text
					.'</text>'; 
					// in Style integrieren, falls Umrandung gewünscht: stroke:#DDDDDD; stroke-width:0.3px;
					$i_array++;
							
				echo  "</g>";
		}
	}
	
	
	
	// Objekt-Info
	//-------------------------------------------------
	// im unlegend-Betrieb nicht ausgeben
	if(!$_SESSION['Dokument']['unlegend'])
	{
	
		// Info zu Kartenelementen bei Mausinteraktionen (wird über Karte gelegt)
		echo  '<g id="ObjInfofeld" pointer-events="none" opacity="0">';
			echo  '<rect x="'.$xl=($XD-380).'" y="'.$LRahmen_oben=($ObjInfofeld_Y+5).'" width="178px" height="85px" fill="#FFFFFF" stroke="#555555" stroke-width="1" opacity="0.7" />'; 
			
			$ObjInfofeld_Y_ff=($ObjInfofeld_Y+5);
			
			echo  '<text id="ObjInfofeld_Name_z1" x="'.$RPos=($_SESSION['Dokument']['groesse_X']-160).'" y="'.$ObjInfofeld_Y_ff=($ObjInfofeld_Y_ff+15)
			.'" style="font-size:11px; font-family:Arial; font-weight:bold;"> </text>';
				
			echo  '<text id="ObjInfofeld_Name_z2" x="'.$RPos=($_SESSION['Dokument']['groesse_X']-160).'" y="'.$ObjInfofeld_Y_ff=($ObjInfofeld_Y_ff+12)
			.'" style="font-size:11px; font-family:Arial; font-weight:bold;"> </text>';
			
			echo  '<text id="ObjInfofeld_Wert" x="'.$RPos=($_SESSION['Dokument']['groesse_X']-160).'" y="'.$ObjInfofeld_Y_ff=($ObjInfofeld_Y_ff+18)
			.'" style="font-size:11px; font-family:Arial; font-weight:bold;"> </text>';
			
			// nur einblenden wenn $_SESSION['Dokument']['Fuellung']['MITTLERE_AKTUALITAET_IGNORE'] nicht gesetzt
			// Bei Vergleichskarten vorerst keine Aktualität ausgeben
			if(!$Vergleichskarte)
			{
				// Änderung: Wird doch überall gezeigt, nur anders benannt
				if(!$GLOBALS['MITTLERE_AKTUALITAET_IGNORE'])
				{
					$Aktualitäts_String = $ObjInfofeld_Grundakt;
					
					echo  '<text id="ObjInfofeld_Aktualitaet_Titel" x="'.$RPos=($_SESSION['Dokument']['groesse_X']-160).'" y="'.$ObjInfofeld_Y_ff=($ObjInfofeld_Y_ff+15)
					.'" style="font-size:10px; font-family:Arial; font-weight:bold;">'.$Aktualitäts_String.'</text>';
					
					echo  '<text id="ObjInfofeld_Aktualitaet" x="'.$RPos=($_SESSION['Dokument']['groesse_X']-40).'" y="'.$ObjInfofeld_Y_ff=($ObjInfofeld_Y_ff+0)
					.'" style="font-size:10px; font-family:Arial; font-weight:bold;">'.$ObjInfofeld_Grundakt.'</text>';
					
				}
				else
				{
					// versteckte einbindung nötig, da sonst Fehler im IE durch fehlendes Objekt auftreten
					$Aktualitäts_String = utf8_encode($ObjInfofeld_Grundakt);
					
					echo  '<text id="ObjInfofeld_Aktualitaet_Titel" x="'.$RPos=($_SESSION['Dokument']['groesse_X']-160).'" y="'.$ObjInfofeld_Y_ff=($ObjInfofeld_Y_ff+15)
					.'" style="display:none;">'.$Aktualitäts_String.'</text>';
					
					echo  '<text id="ObjInfofeld_Aktualitaet" x="'.$RPos=($_SESSION['Dokument']['groesse_X']-40).'" y="'.$ObjInfofeld_Y_ff=($ObjInfofeld_Y_ff+0)
					.'" style="display:none;">'.$ObjInfofeld_Grundakt.'</text>';
				}
			}
			echo  '<text id="ObjInfofeld_Hinweis" x="'.$RPos=($_SESSION['Dokument']['groesse_X']-160).'" y="'.$ObjInfofeld_Y_ff=($ObjInfofeld_Y_ff+15)
			.'" style="font-size:10px; font-family:Arial; font-weight:bold;"> </text>';
			
		echo  '</g>';
	 
	 
	 
		// MouseOver Anzeige des Elements !!!!!!!!!!!!!!! wesentlich schneller als Objekt selbst umzudefinieren und immer ganz oben angezeigt !!!!!!!!!!!!!!!!!
		/// ???????????????????????????????? Wo kommt die Füllung her ????????????????????????
		// Hier erstmal leer definiert/angelegt, die Zuordnung zu einem Objekt erfolgt dann durch JScript am Objekt selbst über die Definition von xlink:href="...."
		echo  '<text id="leeresobjekt"></text>';
		echo  '<g transform="matrix('.$s.' 0 0 '.$s.' '.$X_min.' '.$Y_max.')" style="fill-opacity:0;"><use id="marker_geom" xlink:href="#leeresobjekt" stroke="#DD4444" stroke-width="'
			  .$strwidth = ($_SESSION['Dokument']['Strichstaerke_Event']*2).'" pointer-events="none" ></use></g>';
	}
}

	
	
	
// Ende: keine Anzeige bei fehlenden Auswahl-Daten

// ---------------------------------------------------------
// Fehlerinformation für AGS-Jahr Konflikt
if($_SESSION['Dokument']['AGS_Fehler'] == 1)
{
	if($_SESSION['Dokument']['Sprache'] == 'DE')
	{
		echo  '<g>';
		echo  '<text x="'.$xleer=($XD/2-300).'" y="250" dx="" dy="" style="font-size:28px; font-family:Arial; font-weight:bold;" fill="#000000" >'.utf8_encode("Achtung!").'</text>';
		echo  '<text x="'.$xleer=($XD/2-300).'" y="300" dx="" dy="" style="font-size:20px; font-family:Arial; font-weight:bold;" fill="#000000" >'.utf8_encode("Folgende Verwaltungseinheiten existieren").'</text>';
		echo  '<text x="'.$xleer=($XD/2-300).'" y="320" dx="" dy="" style="font-size:20px; font-family:Arial; font-weight:bold;" fill="#000000" >'.utf8_encode("im ausgewählten Jahr nicht:").'</text>';
		echo  '<text x="'.$xleer=($XD/2-300).'" y="350" dx="" dy="" style="font-size:20px; font-family:Arial; font-weight:bold;" fill="#000000" >'.$_SESSION['Dokument']['AGS_Fehler_Elemente'].'</text>';
		echo  '<text x="'.$xleer=($XD/2-300).'" y="380" dx="" dy="" style="font-size:20px; font-family:Arial; font-weight:bold;" fill="#000000" >'.utf8_encode("Bitte korrigieren Sie Ihre Auswahl!").'</text>';
		echo  '</g>';
	}
	
	if($_SESSION['Dokument']['Sprache'] == 'EN')
	{
		echo  '<g>';
		echo  '<text x="'.$xleer=($XD/2-300).'" y="250" dx="" dy="" style="font-size:28px; font-family:Arial; font-weight:bold;" fill="#000000" >'.utf8_encode("Attention!").'</text>';
		echo  '<text x="'.$xleer=($XD/2-300).'" y="300" dx="" dy="" style="font-size:20px; font-family:Arial; font-weight:bold;" fill="#000000" >'.utf8_encode("The following areas").'</text>';
		echo  '<text x="'.$xleer=($XD/2-300).'" y="320" dx="" dy="" style="font-size:20px; font-family:Arial; font-weight:bold;" fill="#000000" >'.utf8_encode("aren't available in the year:").'</text>';
		echo  '<text x="'.$xleer=($XD/2-300).'" y="350" dx="" dy="" style="font-size:20px; font-family:Arial; font-weight:bold;" fill="#000000" >'.$_SESSION['Dokument']['AGS_Fehler_Elemente'].'</text>';
		echo  '<text x="'.$xleer=($XD/2-300).'" y="380" dx="" dy="" style="font-size:20px; font-family:Arial; font-weight:bold;" fill="#000000" >'.utf8_encode("Please change your selection!").'</text>';
		echo  '</g>';
	}
}
// ---------------------------------------------------------

// Array $_SESSION nach gebrauch der API leeren
if($GLOBALS['API'] == 1)
{
	$_SESSION = unserialize($GLOBALS['SESSION_SPEICHER']);
	//echo  "<g><desc>".$GLOBALS['SESSION_SPEICHER']."</desc></g>";
}

/* 
// Die für Druck die größere Anzeige wieder zurückstellen
if($_GET['druck'])
{
	$_SESSION['Dokument']['groesse_X'] = $_SESSION['Dokument']['groesse_X'] - 220;
	$_SESSION['Dokument']['groesse_Y'] = $_SESSION['Dokument']['groesse_Y'] - 200;
} */




// Ende der SVG-Datei speichern
echo  '</svg>';




// Form der Ausgabe ermitteln und entweder direkt ausgeben oder als Datei speichern
if(!$_SESSION['Dokument']['Dateiausgabe'])
{
		$Testus= fopen('monitor.ioer.de/svg_viewer/ressource.txt','w+');
	// Ausgabe auf Bildschirm
	ob_end_flush();
}
else
{

	// Löschen von Dateien die älter als eine Stunde sind 3600(s) Abweichung von der Systemzeit
	$Pfad = 'temp/';
	$Verzeichnis = opendir($Pfad);
	
	/*if ($Verzeichnis===false ){
		$ausgabi= "geht nicht auf";
		}
		else {$ausgabi="funktioniert";}*/
	
	while($Datei_loesch = readdir($Verzeichnis))
	{
		// Nur echte Dateien löschen
		if (is_file($Pfad.$Datei_loesch) and (filemtime($Pfad.$Datei_loesch) + 3600) < time()) 
		{
			$ausgabi= "gelöscht";
			unlink($Pfad.$Datei_loesch);
			// echo $Pfad.$Datei_loesch.' '.filemtime($Pfad.$Datei_loesch).' ';
		}
		
	}
	closedir($Verzeichnis);
	
	
	
	// Ausgabe in Datei
	$Dateiname = $_SESSION['Dokument']['Fuellung']['Indikator'].'_'.$_SESSION['Dokument']['Jahr_Anzeige'].'_'.$_SESSION['Dokument']['Raumgliederung'].'_'.mt_rand(0,1000);
	// $Dateiname = date('YmdHis').'_'.rand(1,100);
	$Dateiname_svg = $Dateiname.'.svg';
	
	$Datei = fopen('temp/'.$Dateiname_svg,'w+');
	fwrite($Datei,ob_get_contents());
	fclose($Datei);

	// Test für Rasterisierung mittels Batik (Filetypes können sein: image/png, image/jpeg, image/tiff, application/pdf)
	// $_SESSION['Dokument']['Dateiausgabe_width'] = 3000;

	switch($_SESSION['Dokument']['Dateiausgabe_typ_datei'])
	{
		case 'png':
			$_SESSION['Dokument']['Dateiausgabe_typ'] = 'image/png';
			break;
		case 'jpg':
			$_SESSION['Dokument']['Dateiausgabe_typ'] = 'image/jpeg';
			$Qualitaet = ' -q 0.9 ';
			break;
		case 'tif':
			$_SESSION['Dokument']['Dateiausgabe_typ'] = 'image/tiff';
			break;
		case 'pdf':
			$_SESSION['Dokument']['Dateiausgabe_typ'] = 'application/pdf';
			break;
	}
	
	
	
	// Rasterisierung
	
		$Output = shell_exec('java -Xmx1536m -jar batik/batik-rasterizer.jar -d /srv/www/htdocs/monitor/svg_viewer/temp '.$Qualitaet.' -w '.$_SESSION['Dokument']['Dateiausgabe_width'].' -m '.$_SESSION['Dokument']['Dateiausgabe_typ'].' temp/'
																																																.$Dateiname_svg );
	
/*	$Output = shell_exec('java -Xmx1536m -jar batik/batik-rasterizer.jar -d /srv/www/htdocs/monitor/svg_viewer/temp   -m application/pdf temp/test.pdf'
																																																 );

		$Output = shell_exec('java -Xmx1536m -jar batik/batik-rasterizer.jar -d /srv/www/htdocs/monitor/svg_viewer/temp  -w 2000 -m application/pdf temp/'
																																																.$Dateiname_svg );
				$Output = shell_exec('java -Xmx1536m -jar batik/batik-rasterizer.jar -d /srv/www/htdocs/monitor/svg_viewer/temp '.$Qualitaet.' -w '.$_SESSION['Dokument']['Dateiausgabe_width'].' -m '.$_SESSION['Dokument']['Dateiausgabe_typ'].' temp/'
																																																.$Dateiname_svg );	*/																																										
																																																
	ob_end_clean(); // Leeren des Ausgabepuffers
	
	// SVG mit Downloadlink ausgeben
	echo utf8_encode('<?xml version="1.0" encoding="utf-8"?>
			<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN"  "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">
			<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:ev="http://www.w3.org/2001/xml-events" version="1.1" baseProfile="full" 
			viewBox="0 0 500 600" width="500px" height="600px">
			<rect x="0" y="0" width="500px" height="600px" fill="#FFFFFF" stroke="none"/>
			<text x="40" y="40" style="font-size:16px; font-family:Arial;" >Die angeforderte Kartendatei wurde fertiggestellt:</text>
			<a xlink:href="temp/'.$Dateiname.'.'.$_SESSION['Dokument']['Dateiausgabe_typ_datei'].'" target="_self">
				<rect x="115" y="60" width="175px" height="44px" fill="#eeeeee" stroke="#555555"/>
				<text x="130" y="91" style="font-size:30px; font-family:Arial; font-weight:bold;" >Download</text>
			</a>
			<text x="240" y="240">'.	$Datei .'</text >
		</svg>'); 
}




pg_close($Verbindung_PostgreSQL);
mysqli_close($Verbindung);


?>