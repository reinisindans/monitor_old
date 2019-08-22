<?php 
// session_start();

// ------------------ evtl. verwendete Zeichenvorschrift für Klassen nach neuen Karteninhalten aktualisieren -------------------

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


					
switch ($R_Verhaeltniss) {
	case "gleich":
		$R = 0;
	break;
	case "aufsteigend":
		
		$R = $R_Differenz/100;
				
	break;
	case "absteigend":
		$R = ($R_Differenz/100)*(-1); 
	break;
}
					
switch ($G_Verhaeltniss) {
	case "gleich":
		$G = 0;
	break;
	case "aufsteigend":
		$G = $G_Differenz/100; 
	break;
	case "absteigend":
		$G = ($G_Differenz/100)*(-1);
	
	break;
}
					
switch ($B_Verhaeltniss) {
	case "gleich":
		$B = 0;
	break;
	case "aufsteigend":
		$B = $B_Differenz/100;
	break;
	case "absteigend":
		$B = ($B_Differenz/100)*(-1);
	break;
}	


$_SESSION['Temp']['Klasse'] = array();
 
 
// Eckdaten sammeln und aufbereiten
// ------------------------------------------

// Rundungs-10er-Potenz verarbeitbar erfassen
$Rundung = 1;
while($_SESSION['Dokument']['Fuellung']['Rundung'] > 0 and $Rdg=($Rundungszaehler+1) <= $_SESSION['Dokument']['Fuellung']['Rundung'])
{
	$Rundungszaehler++;
	if($Rundungszaehler == 1) 
	{
		$Rundung=10;
	}
	else
	{
		$Rundung = $Rundung*10;
	}
	
}




// Min Max korrekt gerundet
//$_SESSION['Dokument']['Fuellung']['Indikator_Wert_min_Dok_rounded'] = floor($_SESSION['Dokument']['Fuellung']['Indikator_Wert_min']*$Rundung)/$Rundung;
//$_SESSION['Dokument']['Fuellung']['Indikator_Wert_max_Dok_rounded'] = ceil($_SESSION['Dokument']['Fuellung']['Indikator_Wert_max']*$Rundung)/$Rundung; //
// evtl. doch besser normal gerundet: 
$_SESSION['Dokument']['Fuellung']['Indikator_Wert_min_Dok_rounded'] = $_SESSION['Dokument']['Fuellung']['Indikator_Wert_min'];
$_SESSION['Dokument']['Fuellung']['Indikator_Wert_max_Dok_rounded'] = $_SESSION['Dokument']['Fuellung']['Indikator_Wert_max'];
 
 
// ------------------------- 
// Manuelle Klasse (Ober-/Untergrenzen) mit betreuen, da bei Kartendarstellung dieses Script immer mir ausgeführt wird
if(isset($_SESSION['Temp']['manuelle_Klasse']) and $_SESSION['Temp']['manuelle_Klasse'] != "leer")
{
	// Untergrenze 1. man. Klasse setzen, wenn zu hoch
	if($_SESSION['Temp']['manuelle_Klasse'][0]['Wert_Untergrenze'] > $_SESSION['Dokument']['Fuellung']['Indikator_Wert_min_Dok_rounded'])
	{
		$_SESSION['Temp']['manuelle_Klasse'][0]['Wert_Untergrenze'] = $_SESSION['Dokument']['Fuellung']['Indikator_Wert_min_Dok_rounded'];
	}
	// Hochzählen der vorhandenen Klassen
	$i_mklass = 0;
	while(isset($_SESSION['Temp']['manuelle_Klasse'][$i_mklass])) { $i_mklass++; }
	// Obergrenze letzte man. Klasse setzen, wenn zu niedrig
	$i_mklass--;
	if($_SESSION['Temp']['manuelle_Klasse'][$i_mklass]['Wert_Obergrenze'] < $_SESSION['Dokument']['Fuellung']['Indikator_Wert_max_Dok_rounded'])
	{
		$_SESSION['Temp']['manuelle_Klasse'][$i_mklass]['Wert_Obergrenze'] = $_SESSION['Dokument']['Fuellung']['Indikator_Wert_max_Dok_rounded'];
	}
}
// -----------------------
 
 
// zur Sicherheit hier nochmals gesetzt, falls noch nicht geschehen
if(!$_SESSION['Dokument']['Klassen']['Aufloesung']) $_SESSION['Dokument']['Klassen']['Aufloesung'] = 7; 
 
 

// Test auf gleiche Ober- bzw. Untergrenzenwerte => entspricht nur einem einzigen ausgewählten Objekt 
if($_SESSION['Dokument']['Fuellung']['Indikator_Wert_min'] != $_SESSION['Dokument']['Fuellung']['Indikator_Wert_max'])
{
 
	// Häufigkeitsverteilte Klassen und Histogramm definieren
	// --------------------------------------------------------------------------------
	
	if(!$_SESSION['Dokument']['Fuellung']['Untertyp'] or $_SESSION['Dokument']['Fuellung']['Untertyp'] == "haeufigkeit")
	{
		
		$HistTeileGefuellt = 0;
		for($i=0 ; $i <= 100 ; $i++)
		{
			// Gesamtanz. der Raumeinheiten für Klassengenerierung nach Anzahl (Auflösung)
			$Einheiten_Anz = $Einheiten_Anz + $_SESSION['Temp']['i_Verteilung'][$i]; 
			// Gesamtzahl gefüllter Histogramm-Teile (nicht die GeoObjekte an sich) ermitteln
			if($_SESSION['Temp']['i_Verteilung'][$i]) $HistTeileGefuellt++;
		}
		// max. Klassen-Auflösung beschränken
		if($HistTeileGefuellt < $_SESSION['Dokument']['Klassen']['Aufloesung']) 
		{
			$KlassAufloesg_korrigiert = $HistTeileGefuellt;
		}
		else
		{
			$KlassAufloesg_korrigiert = $_SESSION['Dokument']['Klassen']['Aufloesung'];
		}
		
		// Auflösung wird übergeben und ist ein Ziel-Richtwert für die Klassenanzahl (wird nicht überschritten aber evtl. leicht unterschritten .... gut?)
		$Klassengroesse = @(1 * ($Einheiten_Anz / $KlassAufloesg_korrigiert)); // weniger als 1 => mehr Klassen!
		
		
		// ------ Korrektur der Klassengröße bei Extremverteilungen (sehr viele Objekte in einer einzigen Verteilungs-Prozent-Klasse) ----
		// Finden des Verteilungs-Maximums
		for($i=0 ; $i <= 100 ; $i++)
		{
			if($Vi_max < $_SESSION['Temp']['i_Verteilung'][$i]) 
			{
				// altes Max vermerken => ergibt am Schluss den nächsthöchsten Wert
				// $Vi_max_2 = $Vi_max;
				
				// neues Max setzen
				$Vi_max = $_SESSION['Temp']['i_Verteilung'][$i];
				// Gesamt-Objektzahl bestimmen
				$Vi_gesamt = $Vi_gesamt + $_SESSION['Temp']['i_Verteilung'][$i];
			}
		}
		// Korrektur durch Vergleich der Extremballung mit der restlichen Verteilungsmenge
		if($Vi_gesamt - $Vi_max <= $Vi_max) 
		{
			$Klassengroesse = @(1 * (($Einheiten_Anz - $Vi_max) / $KlassAufloesg_korrigiert)); // weniger als 1 => mehr Klassen!
		}
		// ------ ------
		
		
		// Ersetzung für feste Klassenzahl
		$BasisKlasse = $Klassengroesse; 
		
		$i_Klassen = 0;  // Laufvariable für Klassen setzen
		for($i=0 ; $i <= 100 ; $i++)
		{
			// alte Berechnung: $BasisKlasse = $_SESSION['Temp']['i_Verteilung']['Max']/$_SESSION['Dokument']['Klassen']['Aufloesung']; // <--------------- Durch bestimmten Wert teilen, um Klassenzahl zu erhöhen
			
			// Klassenfüllung
			$Klassen_Volumen = $Klassen_Volumen + $_SESSION['Temp']['i_Verteilung'][$i];

			// neue Klasse setzen wenn Max-Wert erreicht oder $i bei 100=Ende
			if($Klassen_Volumen >= $BasisKlasse or $i==100) 
			{
				
				// Ober- / Untergrenzen erfassen
				if($i_Klassen > 0)
				{
					// in Prozent
					$_SESSION['Temp']['Klasse'][$i_Klassen]['Untergrenze'] =  $i_untergr;
					$_SESSION['Temp']['Klasse'][$i_Klassen]['Obergrenze'] =  $i;
					
					// Klassengrenzen als Werte setzen
					$_SESSION['Temp']['Klasse'][$i_Klassen]['Wert_Untergrenze'] = (ceil(($_SESSION['Dokument']['Fuellung']['Indikator_Wert_min'] + 
										($_SESSION['Temp']['Klasse'][$i_Klassen]['Untergrenze'] * $_SESSION['Dokument']['Fuellung']['Wertebereich_ein_Prozent']))*$Rundung)/$Rundung);
					$_SESSION['Temp']['Klasse'][$i_Klassen]['Wert_Obergrenze'] = (ceil(($_SESSION['Dokument']['Fuellung']['Indikator_Wert_min'] + 
										($_SESSION['Temp']['Klasse'][$i_Klassen]['Obergrenze'] * $_SESSION['Dokument']['Fuellung']['Wertebereich_ein_Prozent']))*$Rundung)/$Rundung);
					 
					
				}
				else
				{
					// in Prozent
					$_SESSION['Temp']['Klasse'][$i_Klassen]['Untergrenze'] =  '0';
					$_SESSION['Temp']['Klasse'][$i_Klassen]['Obergrenze'] =  $i;
					
					// Klassengrenzen als Werte setzen
					$_SESSION['Temp']['Klasse'][0]['Wert_Untergrenze'] = $_SESSION['Dokument']['Fuellung']['Indikator_Wert_min_Dok_rounded'];
					$_SESSION['Temp']['Klasse'][0]['Wert_Obergrenze'] = (ceil(($_SESSION['Dokument']['Fuellung']['Indikator_Wert_min'] + 
										($_SESSION['Temp']['Klasse'][$i_Klassen]['Obergrenze'] * $_SESSION['Dokument']['Fuellung']['Wertebereich_ein_Prozent']))*$Rundung)/$Rundung);
				}
				
				
				// Korrektur für oberste Klasse ... sonst evtl Rundungsfehler bei Rechenoperation
				if($i==100) 
				{
					$_SESSION['Temp']['Klasse'][$i_Klassen]['Wert_Obergrenze'] = $_SESSION['Dokument']['Fuellung']['Indikator_Wert_max_Dok_rounded'];
					$_SESSION['Temp']['Klasse'][$i_Klassen]['Obergrenze'] = 100;
				}
							
				// Leere Klassen (durch Rundung bei wenigen Gebietseinheiten denkbar) eliminieren
				//if($_SESSION['Temp']['Klasse'][$i_Klassen]['Wert_Obergrenze'] != $_SESSION['Temp']['Klasse'][$i_Klassen]['Wert_Untergrenze'])
				//{
					// Nummer der nächsten Klasse
					$i_Klassen++; 
					// Zurücksetzen der Größen-Ermittlungsvariable
					$Klassen_Volumen = 0; 
					// behalten der Schwelle
					$i_untergr = $i; 
				//}
			} 
		}
	}
	
	
	
	
	// Gleichabständige Klassen (hier dennoch auf die 100er-Teilung bezogen, da dies der Rundung, die oft nicht mehr als 2 Stellig ist, entgegen kommt
	// -----------------------------------------------------------------------------------------------------------------------------------------------
	
	if($_SESSION['Dokument']['Fuellung']['Untertyp'] == "gleich")
	{
		// Max. Gefüllte Prozenteinheiten ermitteln und max Klassenauflösung festsetzen
		for($i=0 ; $i <= 100 ; $i++)
		{
			// Gesamtanz. der Raumeinheiten für Klassengenerierung nach Anzahl (Auflösung)
			$Einheiten_Anz = $Einheiten_Anz + $_SESSION['Temp']['i_Verteilung'][$i]; 
			// Gesamtzahl gefüllter Histogramm-Teile ermitteln
			if($_SESSION['Temp']['i_Verteilung'][$i]) $HistTeileGefuellt++;
		}
		// max. Klassen-Auflösung beschränken
			// max. Klassen-Auflösung beschränken
		if($HistTeileGefuellt < $_SESSION['Dokument']['Klassen']['Aufloesung']) 
		{
			$KlassAufloesg_korrigiert = $HistTeileGefuellt;
		}
		else
		{
			$KlassAufloesg_korrigiert = $_SESSION['Dokument']['Klassen']['Aufloesung'];
		}
		
		
		$Klassengroesse_in_einProzenteinheiten = 100/$KlassAufloesg_korrigiert;
		
		$i_Klassen = 0;  // Laufvariable für Klassen setzen
		while($i_Klassen < $KlassAufloesg_korrigiert)
		{
			$Klassengr_floor = floor($Klassengroesse_in_einProzenteinheiten*$Rundung)/$Rundung;
			$Rest = $Rest + $Klassengroesse_in_einProzenteinheiten - $Klassengr_floor;
			if($Rest >= 1) 
			{	
				$Klassengr_floor++;
				$Rest = 0;
			}
			
			// Ober- / Untergrenzen erfassen
			if($i_Klassen > 0)
			{
				$_SESSION['Temp']['Klasse'][$i_Klassen]['Untergrenze'] = $_SESSION['Temp']['Klasse'][$ivorher=($i_Klassen-1)]['Obergrenze'];
			}
			else
			{
				$_SESSION['Temp']['Klasse'][$i_Klassen]['Untergrenze'] = '0';
			}
			$_SESSION['Temp']['Klasse'][$i_Klassen]['Obergrenze'] = $_SESSION['Temp']['Klasse'][$i_Klassen]['Untergrenze'] + $Klassengr_floor;
			
					
		
			$_SESSION['Temp']['Klasse'][$i_Klassen]['Wert_Untergrenze'] = (ceil(($_SESSION['Dokument']['Fuellung']['Indikator_Wert_min'] + 
										($_SESSION['Temp']['Klasse'][$i_Klassen]['Untergrenze'] * $_SESSION['Dokument']['Fuellung']['Wertebereich_ein_Prozent']))*$Rundung)/$Rundung);
			$_SESSION['Temp']['Klasse'][$i_Klassen]['Wert_Obergrenze'] = (ceil(($_SESSION['Dokument']['Fuellung']['Indikator_Wert_min'] + 
										($_SESSION['Temp']['Klasse'][$i_Klassen]['Obergrenze'] * $_SESSION['Dokument']['Fuellung']['Wertebereich_ein_Prozent']))*$Rundung)/$Rundung);
					
			
			// Korrektur für Untergrenze der Klasse "0" ... sonst evtl Rundungsfehler bei Rechenoperation
			if($i_Klassen == 0) $_SESSION['Temp']['Klasse'][0]['Wert_Untergrenze'] = $_SESSION['Dokument']['Fuellung']['Indikator_Wert_min_Dok_rounded'];
			// Korrektur für oberste Klasse ... sonst evtl Rundungsfehler bei Rechenoperation
			if($i_Klassen == ($KlassAufloesg_korrigiert-1)) 
			{
				$_SESSION['Temp']['Klasse'][$i_Klassen]['Wert_Obergrenze'] = $_SESSION['Dokument']['Fuellung']['Indikator_Wert_max_Dok_rounded'];
				$_SESSION['Temp']['Klasse'][$i_Klassen]['Obergrenze'] = 100;
			}
		
			$i_Klassen++; // Nummer der Klasse
		}
	}
	
	// Vermerk über wirkliche Anzahl berechneter Klassen
	$_SESSION['Temp']['KlassenAnz'] = $i_Klassen;


	// Farbgebung einfließen lassen
	// -------------------------------------------------------------------------------------------------------
	
	// Letzte Hochstufung rückgängig machen (Klassennummer nicht existent, da Schleife beendet)
	$i_Klassen--;
	if(!$i_Klassen) $i_Klassen = 1; // Fehler bei Auswahl von nur einem Polygon verhindern (dumm, aber schöner)
	$Spaltwert = 100/($i_Klassen);
	if(strlen($R_hex = dechex(round(abs($Ri=$R_max_dezimal),0))) < 2) $R_hex = '0'.$R_hex; // jeweils 0 Vor Einstellige Ergebnisse setzen
	if(strlen($G_hex = dechex(round(abs($Gi=$G_max_dezimal),0))) < 2) $G_hex = '0'.$G_hex;
	if(strlen($B_hex = dechex(round(abs($Bi=$B_max_dezimal),0))) < 2) $B_hex = '0'.$B_hex;
	$_SESSION['Temp']['Klasse'][0]['Farbwert'] = $R_hex.$G_hex.$B_hex;
	
	for($Klasse = 0 ; $Klasse <= $i_Klassen ; $Klasse++)
	{
		 // Farbgebung für Klasse 
		if(strlen($R_hex = dechex(round(abs($Ri=$R_max_dezimal-($Spaltwert*$Klasse*$R)),0))) < 2) $R_hex = '0'.$R_hex; // jeweils 0 Vor Einstellige Ergebnisse setzen
		if(strlen($G_hex = dechex(round(abs($Gi=$G_max_dezimal-($Spaltwert*$Klasse*$G)),0))) < 2) $G_hex = '0'.$G_hex;
		if(strlen($B_hex = dechex(round(abs($Bi=$B_max_dezimal-($Spaltwert*$Klasse*$B)),0))) < 2) $B_hex = '0'.$B_hex;
		$_SESSION['Temp']['Klasse'][$KL = $i_Klassen-$Klasse]['Farbwert'] = $R_hex.$G_hex.$B_hex;
		// -> Umkehrung der Farbwerte durch $KL = $i_Klassen-$Klasse ( sonst falsche Richtung)
	}
	
	// Hinweis auf nur ein Objekt in der Karte leeren
	$_SESSION['Temp']['Nur_ein_Wert_vorhanden'] = '0';

}
else
{
	// Nur eine Klasse generieren, falls nur ein einziges Objekt gewählt wurde
	$_SESSION['Temp']['Klasse']['0']['Wert_Untergrenze'] = $_SESSION['Dokument']['Fuellung']['Indikator_Wert_min_Dok_rounded'];
	$_SESSION['Temp']['Klasse']['0']['Wert_Obergrenze'] = $_SESSION['Dokument']['Fuellung']['Indikator_Wert_max_Dok_rounded'];
	$_SESSION['Temp']['Klasse']['0']['Untergrenze'] = '0';
	$_SESSION['Temp']['Klasse']['0']['Obergrenze'] = '100';
	$_SESSION['Temp']['Klasse']['0']['Farbwert'] = $_SESSION['Dokument']['Fuellung']['Farbwert_max'];
	// Hinweis auf nur ein Objekt in der Karte
	$_SESSION['Temp']['Nur_ein_Wert_vorhanden'] = '1';
	
}








// ------------------ Klassen aktualisieren ENDE -------------------

/* 
if(!$_SESSION['Temp']['manuelle_Klasse'])
{
	$_SESSION['Temp']['manuelle_Klasse'] = $_SESSION['Temp']['Klasse'];
}
 */
?>
