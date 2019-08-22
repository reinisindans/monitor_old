<?php 
session_start();

include("../includes_classes/verbindung_mysqli.php");

// Memory-Limit erweitern
ini_set('memory_limit', '1000M');
// Zeit-Limit erhöhen
ini_set('max_execution_time', '500');

// Einstellung für Browser-Verbindungs-Erkennung (zum beenden des Scripts)
Set_Time_Limit(0);


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



$Startzeit = date('i:s');




// mögliche Fehlercodes in einem Array erfassen
$SQL_FC = "SELECT * FROM m_fehlercodes WHERE FEHLERCODE >= '1' ORDER BY FEHLERCODE";
$Ergebnis_FC = mysqli_query($Verbindung,$SQL_FC,$Verbindung);
$i_FCP=0;
while(@mysqli_result($Ergebnis_FC,$i_FCP,'FEHLERCODE'))
{
	$_SESSION['Dokument']['Fuellung']['Fehlercodes'][@mysqli_result($Ergebnis_FC,$i_FCP,'FEHLERCODE')]['FEHLERCODE'] = @mysqli_result($Ergebnis_FC,$i_FCP,'FEHLERCODE');
	$_SESSION['Dokument']['Fuellung']['Fehlercodes'][@mysqli_result($Ergebnis_FC,$i_FCP,'FEHLERCODE')]['FEHLER_NAME'] = utf8_encode(@mysqli_result($Ergebnis_FC,$i_FCP,'FEHLER_NAME'));
	$_SESSION['Dokument']['Fuellung']['Fehlercodes'][@mysqli_result($Ergebnis_FC,$i_FCP,'FEHLERCODE')]['FEHLER_BESCHREIBUNG'] = utf8_encode(@mysqli_result($Ergebnis_FC,$i_FCP,'FEHLER_BESCHREIBUNG'));
	$_SESSION['Dokument']['Fuellung']['Fehlercodes'][@mysqli_result($Ergebnis_FC,$i_FCP,'FEHLERCODE')]['FEHLER_FARBCODE'] = @mysqli_result($Ergebnis_FC,$i_FCP,'FEHLER_FARBCODE');
	$i_FCP++;
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
		
				if(is_array($DatenSet['Auswahlkriterium_Wert']))
				{
					// Auswahlkriterium_Werte aus Array aufbereiten
					$AWerte_SQL_PG=''; // Variable leeren
					foreach($DatenSet['Auswahlkriterium_Wert'] as $AWert) // Auswahl SQL-Beginn oder Erweiterung
					{
						if($AWerte_SQL_PG)
						{
							$AWerte_SQL_PG = $AWerte_SQL_PG." OR ".$DatenSet['Auswahlkriterium']." ".$DatenSet['Auswahloperator']
							." '".$DatenSet['AuswahloperatorZusatz_1'].$AWert.$DatenSet['AuswahloperatorZusatz_2']."' ";
						}
						else
						{
							$AWerte_SQL_PG = " AND (".$DatenSet['Auswahlkriterium']." ".$DatenSet['Auswahloperator']
							." '".$DatenSet['AuswahloperatorZusatz_1'].$AWert.$DatenSet['AuswahloperatorZusatz_2']."' ";
						}
					}
					$AWerte_SQL_PG = $AWerte_SQL_PG.")"; // SQL-Klammer schließen
				}
								
				// AGS erfassen
				//--------------
				$SQL_PostGIS = "SELECT ags FROM ".$DatenSet['DB_Tabelle']." WHERE ags > '0' ".$AWerte_SQL_PG;
				$ERGEBNIS_PGSQL_AGS =  pg_query($Verbindung_PostgreSQL,$SQL_PostGIS);  // performanterer Weg!!!
				
				
				
				
				
		// -----------------------------------------------------------------------------------------------------------------------------------------------
				// gefundene AGS aus vorhandenen Geometrien speichern
				
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
	
	// ---------- Methode A
	// Abfrage der Grundaktualität
	// $SQL_Aktualitätswerte = "SELECT AGS,AKTUALITAET,JAHR FROM v_akt_gesamt WHERE JAHR = '".$_SESSION['Dokument']['Jahr_Geometrietabelle']."'".$SQL_Eingrenzung_DS; 
	// ---------- Methode B
	
	// UNION der Abfragen für Aktualitätserfassung < hier performanter!?
	/* $SQL_Aktualitätswerte
	for($i_AGS = 0 ; $i_AGS < $AW_Zaehler ; $i_AGS++)
	{
		if($i_AGS != 0) $SQL_Aktualitätswerte = $SQL_Aktualitätswerte." UNION "; 
		$SQL_Aktualitätswerte = $SQL_Aktualitätswerte."SELECT AGS,AKTUALITAET,JAHR FROM v_akt_gesamt WHERE JAHR = '".$_SESSION['Dokument']['Jahr_Geometrietabelle']."' AND AGS = '".$AWerte_AGS[$i_AGS]."'"; 
	} 	 */

	// ---------
	// |
	// |
	// V
	
	/* $Ergebnis_Aktualitätswerte = mysqli_query($Verbindung,$SQL_Aktualitätswerte);

	$i_GAktualitaet=0;
	while(@mysqli_result($Ergebnis_Aktualitätswerte,$i_GAktualitaet,'JAHR'))
	{		
		$AGS_mit_Aktualitaet_Jahr_Mittel[@mysqli_result($Ergebnis_Aktualitätswerte,$i_GAktualitaet,'AGS')] = @mysqli_result($Ergebnis_Aktualitätswerte,$i_GAktualitaet,'AKTUALITAET');
		$i_GAktualitaet++;
	} */
	 
	 
	 
	 
	// ---------- Methode C !!!
	// funktioniert erstaunlicherweise etwas performanter als zusammengesetzte SELECTs !??????
	
	//Erfassen der SOLL-Grundaktualität zum ermitteln der Differenz zu tatsächlichen Grundaktualitäten
	$SQL_Aktualitäts_Verweis = "SELECT AKTUALITAET_VIEWER FROM v_geometrie_jahr_viewer_postgis WHERE Jahr_im_Viewer = '".$_SESSION['Dokument']['Jahr_Anzeige']."'"; 
	$Ergebnis_Aktualitäts_Verweis = mysqli_query($Verbindung,$SQL_Aktualitäts_Verweis);
	$Grundakt_Verweis = @mysqli_result($Ergebnis_Aktualitäts_Verweis,0,'AKTUALITAET_VIEWER');
	
	// Erfassen der AKT. für jede benötigte AGS aus indikatorwert-Tabelle mit der Indikator-ID: Z00AG
	for($i_AGS = 0 ; $i_AGS < $AW_Zaehler ; $i_AGS++)
	{
	
		$SQL_Aktualitätswerte = "SELECT AGS,INDIKATORWERT FROM m_indikatorwerte_".$_SESSION['Dokument']['Jahr_Anzeige']." WHERE ID_INDIKATOR = 'Z00AG' AND AGS = '".$AWerte_AGS[$i_AGS]."';"; 
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
	
	// Abfrage für Min, Max
	$SQL_Indikatorenwerte = "SELECT MAX(INDIKATORWERT) as Maximum,MIN(INDIKATORWERT) as Minimum FROM m_indikatorwerte_".$_SESSION['Dokument']['Jahr_Anzeige']." 
								WHERE FEHLERCODE < '1' 
								AND ID_INDIKATOR = '".$Indikator."' 
								".$SQL_Eingrenzung_DS_MIN_MAX." 
								AND CHAR_LENGTH(AGS) = '".$_SESSION['Dokument']['Raumgliederung_Stellenanzahl']."'"; 
								
	$Ergebnis_Indikatorenwerte = mysqli_query($Verbindung,$SQL_Indikatorenwerte);
	
	// Füllen der Session-Variablen
	$_SESSION['Dokument']['Fuellung']['Indikator_Wert_min'] = @mysqli_result($Ergebnis_Indikatorenwerte,0,'Minimum')+1000000000;
	$_SESSION['Dokument']['Fuellung']['Indikator_Wert_max'] = @mysqli_result($Ergebnis_Indikatorenwerte,0,'Maximum')+1000000000;

	$_SESSION['Dokument']['Fuellung']['Wertebereich'] = $_SESSION['Dokument']['Fuellung']['Indikator_Wert_max'] - $_SESSION['Dokument']['Fuellung']['Indikator_Wert_min'];
	$_SESSION['Dokument']['Fuellung']['Wertebereich_ein_Prozent'] = $_SESSION['Dokument']['Fuellung']['Wertebereich']/100;
	
	// Werte-Min-Max
	$i_min = $_SESSION['Dokument']['Fuellung']['Indikator_Wert_min'];
	$i_max = $_SESSION['Dokument']['Fuellung']['Indikator_Wert_max'];
	
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
	
	for($i_AGS = 0 ; $i_AGS < $AW_Zaehler ; $i_AGS++)
	{
		$SQL_Eingrenzung_DS = " AND AGS = '".$AWerte_AGS[$i_AGS]."' ";
				
		// Abfrage für Min, Max sowie Einzelwerte
		$SQL_Indikatorenwerte = "SELECT AGS,INDIKATORWERT,FEHLERCODE,HINWEIS_EXTERN FROM m_indikatorwerte_".$_SESSION['Dokument']['Jahr_Anzeige']." WHERE ID_INDIKATOR = '".$Indikator
							."' ".$SQL_Eingrenzung_DS." AND CHAR_LENGTH(AGS) = '".$_SESSION['Dokument']['Raumgliederung_Stellenanzahl']."'"; 
		$Ergebnis_Indikatorenwerte = mysqli_query($Verbindung,$SQL_Indikatorenwerte); /* or substr(@mysqli_result($Ergebnis_Indikatorenwerte,$i_i,'AGS'),0,1) == "0" */ // AGS = 00000000 auch einbeziehen
		
			// bei vorhandenem Fehlercode oder generell fehlendem Datensatz:
			if(@mysqli_result($Ergebnis_Indikatorenwerte,0,'FEHLERCODE') > 0 or !@mysqli_result($Ergebnis_Indikatorenwerte,0,'AGS')) 
			{
				// Fehlercode mit AGS in einem Array hinterlegen
				if(!@mysqli_result($Ergebnis_Indikatorenwerte,0,'AGS'))
				{
					// Fehlender Datensatz => Fehler Nummer 9 = keine Daten vorhanden, aus ungeklärtem Grund
					$AGS_mit_Fehlern[$AWerte_AGS[$i_AGS]] = '9';
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
				$AGS_mit_Werten[$AWerte_AGS[$i_AGS]] = @mysqli_result($Ergebnis_Indikatorenwerte,$i_i,'INDIKATORWERT')+1000000000;
				$AGS_mit_Hinweisen[$AWerte_AGS[$i_AGS]] = @mysqli_result($Ergebnis_Indikatorenwerte,$i_i,'HINWEIS_EXTERN');
				// Werteverteilung speichern ( = Anzahl zugehöriger Raumeinheiten pro Prozentpunkt)
				// Prozentwert gerundet auf Ganzzahl-Prozent (nur bei leeren Indikatoren Fehler <= ok)
				$i_Prozentwert = round(($AGS_mit_Werten[$AWerte_AGS[$i_AGS]]-$i_min) / $i_1Prozent_Wertebereich,$_SESSION['Dokument']['Fuellung']['Rundung']); 
				
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












	
// Rahmenbedingungen ermitteln
if(is_array($_SESSION['Datenbestand']))
{
	foreach($_SESSION['Datenbestand'] as $DatenSet)
	{
		//echo $DatenSet['NAME'];
		if($DatenSet['View']!='0')
		{
	
				if(is_array($DatenSet['Auswahlkriterium_Wert']))
				{
					// Auswahlkriterium_Werte aus Array aufbereiten (Auswahlkriterium = AGS bzw. Teil-AGS)
					$AWerte_SQL=''; // Variable leeren
					foreach($DatenSet['Auswahlkriterium_Wert'] as $AWert) // Auswahl SQL-Beginn oder Erweiterung
					{
						if($AWerte_SQL)
						{
							$AWerte_SQL = $AWerte_SQL." OR ".$DatenSet['Auswahlkriterium']." ".$DatenSet['Auswahloperator']
							." '".$DatenSet['AuswahloperatorZusatz_1'].$AWert.$DatenSet['AuswahloperatorZusatz_2']."' ";
						}
						else
						{
							$AWerte_SQL = " AND (".$DatenSet['Auswahlkriterium']." ".$DatenSet['Auswahloperator']
							." '".$DatenSet['AuswahloperatorZusatz_1'].$AWert.$DatenSet['AuswahloperatorZusatz_2']."' ";
						}
					}
					$AWerte_SQL = $AWerte_SQL.")"; // SQL-Klammer schließen
				}
				
				
				// Polygonanzahl ermitteln, um Rahmenbedingungen berechnen zu können (simplify() o.Ä.)
				$SQL_PostGIS_Polygonzahl = "SELECT count(ags) AS polygone FROM ".$DatenSet['DB_Tabelle']." WHERE gid > '0' ".$AWerte_SQL;
				$ERGEBNIS_PGSQL_Polygonzahl =  pg_query($Verbindung_PostgreSQL,$SQL_PostGIS_Polygonzahl);  
				$Polygon_Zeile = @pg_fetch_assoc($ERGEBNIS_PGSQL_Polygonzahl,0);
				$_SESSION['Dokument']['Polygonanzahl'] = $_SESSION['Dokument']['Polygonanzahl']+$Polygon_Zeile['polygone'];
				
	
				
				
				// Min- und Max-Ausdehnung der Ebene ermitteln !!! performanterer Weg!!!
				//-----------------------------------------------------------------------
				$SQL_PostGIS_Rahmen = "SELECT MIN(xmin(box3d(the_geom))) AS x_min,MIN(ymin(box3d(the_geom))) AS y_min, MAX(xmax(box3d(the_geom))) AS x_max, MAX(ymax(box2d(the_geom))) AS y_max 
				FROM ".$DatenSet['DB_Tabelle']." WHERE gid > '0' ".$AWerte_SQL;
				$ERGEBNIS_PGSQL_Rahmen =  @pg_query($Verbindung_PostgreSQL,$SQL_PostGIS_Rahmen);  
							
				$PG_Zeile = pg_fetch_assoc($ERGEBNIS_PGSQL_Rahmen,0);
				
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


// -----> Check
// an dieser beispielhaften Variable, ob tatsächlich Gebiete gefunden wurden .... ansonsten Abbruch und Ausgabe eines SVG mit eiuner Fehlermeldung
if(!$_SESSION['Dokument']['X_min_global']) LEER(); 


// Ausschnittsgröße ... wird aber normalerweise außerhalb definiert!
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





// ------------------- momentan nicht mehr genutzt, da Simplify mit ArcMAP vorher auf Originaldaten angewendet wurde
// Präzision der zu ermittlenden Geodaten durch Berechnung festlegen .... momentan nicht mehr genutzt, da Simplify mit ArcMAP vorher auf Originaldaten angewendet wurde
// $xp = $_SESSION['Dokument']['Polygonanzahl']*0.002;

// Präzision steigt mit Polygonzahl nach folgender Quadratformel an ... momentan nicht mehr genutzt, da Geometrien vorher abgespeckt werden
// $_SESSION['Dokument']['Praezision'] = round($xp*$xp + $xp*6 + 300,0); // 300 als Grundwert ist ein guter Kompromiss zwischen Geschwindigkeit und dem Auftreten von Klaffungen
// -------------------


// Linienstärke aus Skalierungsfaktor ($s) ermitteln
$xstr = 1/$s;
$_SESSION['Dokument']['Strichstaerke'] = round($xstr*0.5 - ($xstr*$xstr)/8000 ,0);  //+1000; // +1000 nur zum testen anhängen

// Gesonderte Strichstärken für Hintergrund und Events festhalten, falls die Haupt-Strichstärke im nächsten Schritt auf 0 gesetzt wird
$_SESSION['Dokument']['Strichstaerke_HG'] = $_SESSION['Dokument']['Strichstaerke'] * 10; 
$_SESSION['Dokument']['Strichstaerke_Event'] = $_SESSION['Dokument']['Strichstaerke'];

// Strichstärke bei folgenden Anzeigesituationen auf "0" setzen
if($_SESSION['Dokument']['Raumebene']['Bundesland']['View'] == "1" and $_SESSION['Dokument']['Raumgliederung'] == "gem") $_SESSION['Dokument']['Strichstaerke'] = 0;



// -------- hier berechnet, da alle Eckdaten ab hier bekannt sind ------------
// Klassen neu berechnen (ausgelagert)
if($_SESSION['Temp']['Klasse'])
{
	include('../svg_klassenbildung.php');
}
// ---------------------------------------------------------------------------










// Hintergrund einbinden (momentan fest die Bundesländer als Länder)
//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------

/* Simplify durch Vorberechnung mittels ArcMap/Simplify() ersetzt
$SQL_PostGIS = "SELECT gen,ags,AsSvg(Simplify(the_geom,'".$_SESSION['Dokument']['Praezision']."'),1,0) AS geometrie FROM vg250_bld_".$_SESSION['Dokument']['Jahr_Geometrietabelle'];
$ERGEBNIS_PGSQL_VEKTOREN =  pg_query($Verbindung_PostgreSQL,$SQL_PostGIS);  // performanterer Weg!!! */

$SQL_PostGIS = "SELECT ags,AsSvg(the_geom,1,0) AS geometrie FROM vg250_bld_".$_SESSION['Dokument']['Jahr_Geometrietabelle'];
$ERGEBNIS_PGSQL_VEKTOREN =  pg_query($Verbindung_PostgreSQL,$SQL_PostGIS);  // performanterer Weg!!!

$i_hg=0;
// gefundene Datensätze abarbeiten
while($PG_Zeile = @pg_fetch_assoc($ERGEBNIS_PGSQL_VEKTOREN,$i_hg))
{				
		
		
		// Definition hier, falls nicht anders übergeben
		if(!$_SESSION['Dokument']['HG_Fuellung']) $_SESSION['Dokument']['HG_Fuellung']='DDDDDD'; 
		if(!$_SESSION['Dokument']['HG_UmrandFarbe']) $_SESSION['Dokument']['HG_UmrandFarbe']='CCCCCC'; 
		
		
		$JS_Events = '';
		$Ausgabe['Hintergrund'][] = '<path pointer-events="none" stroke-width="'.$SStHG = $_SESSION['Dokument']['Strichstaerke_HG']
									.'" stroke="#'.$_SESSION['Dokument']['HG_UmrandFarbe']
									.'" fill="#'.$_SESSION['Dokument']['HG_Fuellung']
									.'" d="'.$PG_Zeile['geometrie'].'"></path>'; 
		$i_hg++;
}





// ----------------------------- 
//Funktionen für Zusatzebenen-Anzeige

// Allgemeiner Check der gewählten möglichen Zusatzebenen und füllen eines Arrays nach Vorhandensein


// Verwaltungsgrenzen
if($_SESSION['Dokument']['zusatz_bundesland']) { $Zusatzebene[1] = 'bld'; }
if($_SESSION['Dokument']['zusatz_kreis']) { $Zusatzebene[2] = 'krs'; }
// Topographisches < durch Verschneidung mit KartenObjekten ermitteln
if($_SESSION['Dokument']['zusatz_gew']) { $Zusatzebene[3] = 'gew'; }
if($_SESSION['Dokument']['zusatz_autobahn']) { $Zusatzebene[4] = 'bab'; }


function zusatzebenen($Ebene,$AWerte_SQL,$zusatz_global)
{
	include("../includes_classes/verbindung_mysqli.php");
	
	// für Verwaltungsgrenzen (einfach)
	if($Ebene == "bld" or $Ebene == "krs")
	{
		
		
		$SQL_PostGIS_z = "SELECT gen,AsSvg(the_geom,1,0) AS geometrie 
							FROM vg250_".$Ebene."_".$_SESSION['Dokument']['Jahr_Geometrietabelle']." 
							WHERE gid > '0' ".$AWerte_SQL." 
							GROUP BY vg250_".$Ebene."_".$_SESSION['Dokument']['Jahr_Geometrietabelle'].".the_geom,gen";				
		$ERGEBNIS_PGSQL_VEKTOREN_z =  pg_query($Verbindung_PostgreSQL,$SQL_PostGIS_z); 
				
		// gefundene Datensätze abarbeiten
		while($PG_Zeile_z = @pg_fetch_assoc($ERGEBNIS_PGSQL_VEKTOREN_z))
		{
			 // Verhindern von Dopplungen
			 /* if(!$GLOBALS['Zusatz_schon_vorhanden'][$Ebene][$PG_Zeile_z['gen']]) 
			 { */
				 $zusatz = $zusatz.'<path id="Zusatz_'.$Ebene.'_'.$PG_Zeile_z['gen'].'" d="'.$PG_Zeile_z['geometrie'].'" ></path>';
			 /* }
			 $GLOBALS['Zusatz_schon_vorhanden'][$Ebene][$PG_Zeile_z['gen']] = 1; */
		}
	}
	// Für Gewässerdarstellg (Verschneidungsoperation in DB nötig)
	if($Ebene == "gew")
	{
		// Anzeige mit oder ohne Intersect (ohne = enorme Zeiteinsparung)
		if(!$_SESSION['Dokument']['zusatz_gewaesser_intersect'])
		{ 
			/* ohne Simplify
			$SQL_PostGIS_z = "SELECT 
			gewaesser.gn As gewaesser_name, 
			AsSvg(gewaesser.the_geom,1,0) AS geometrie, 
			vg250_".$_SESSION['Dokument']['Raumgliederung']."_".$_SESSION['Dokument']['Jahr_Geometrietabelle'].".gen As vg_name 
			FROM gewaesser 
			INNER JOIN vg250_".$_SESSION['Dokument']['Raumgliederung']."_".$_SESSION['Dokument']['Jahr_Geometrietabelle']." 
			ON ST_Intersects(gewaesser.the_geom, vg250_".$_SESSION['Dokument']['Raumgliederung']."_".$_SESSION['Dokument']['Jahr_Geometrietabelle'].".the_geom)
			".$AWerte_SQL; // '9999' <= große Flüsse!? ... WHERE (gewaesser.brg = '9999' or gewaesser.brg = '125') nicht mehr genutzt
			 */
			 
			/*  // Methode mit JOIN
			$SQL_PostGIS_z = "SELECT 
			gewaesser.gn As gewaesser_name, 
			AsSvg(Simplify(gewaesser.the_geom,'300'),1,0) AS geometrie, 
			vg250_".$_SESSION['Dokument']['Raumgliederung']."_".$_SESSION['Dokument']['Jahr_Geometrietabelle'].".gen As vg_name 
			FROM gewaesser 
			INNER JOIN vg250_".$_SESSION['Dokument']['Raumgliederung']."_".$_SESSION['Dokument']['Jahr_Geometrietabelle']." 
			ON ST_Intersects(gewaesser.the_geom, vg250_".$_SESSION['Dokument']['Raumgliederung']."_".$_SESSION['Dokument']['Jahr_Geometrietabelle'].".the_geom)
			".$AWerte_SQL; // '9999' <= große Flüsse!? ... WHERE (gewaesser.brg = '9999' or gewaesser.brg = '125') nicht mehr genutzt */
			
			// Methode mit INTERSECT
			$SQL_PostGIS_z = "SELECT 
									v.gn As gewaesser_name, 
									AsSvg(Simplify(v.the_geom,'300'),1,0) AS geometrie, 
									m.gen As vg_name
								
								FROM 
									gewaesser v,
									vg250_".$_SESSION['Dokument']['Raumgliederung']."_".$_SESSION['Dokument']['Jahr_Geometrietabelle']." m
								WHERE 
									st_intersects(v.the_geom,m.the_geom)
									".$AWerte_SQL;
			
			
			/* $SQL_PostGIS_z = "SELECT Intersection ( g.geom, b.geom )
 						FROM gewaesser AS g, vg250_".$_SESSION['Dokument']['Raumgliederung']."_".$_SESSION['Dokument']['Jahr_Geometrietabelle']." AS b
 						WHERE IsEmpty ( Intersection ( g.geom, b.geom ) ) = FALSE ".$AWerte_SQL; */
			
			
			/* $SQL_PostGIS_z = "SELECT ST_Multi(ST_Intersection(ST_Union(a.the_geom), ST_Union(b.the_geom)))
							  FROM vg250_".$_SESSION['Dokument']['Raumgliederung']."_".$_SESSION['Dokument']['Jahr_Geometrietabelle']." a, gewaesser b "; */
		}
		else
		{ 
			$SQL_PostGIS_z = "SELECT gewaesser.gn As gewaesser_name, AsSvg(gewaesser.the_geom,1,0) AS geometrie FROM gewaesser";
		}

		$ERGEBNIS_PGSQL_VEKTOREN_z =  pg_query($Verbindung_PostgreSQL,$SQL_PostGIS_z); 
		
		// gefundene Datensätze abarbeiten
		while($PG_Zeile_z = @pg_fetch_assoc($ERGEBNIS_PGSQL_VEKTOREN_z))
		{
			 $zusatz = $zusatz.'<path id="Zusatz_'.$Ebene.'_'.utf8_encode($PG_Zeile_z['gen']).'" d="'.$PG_Zeile_z['geometrie'].'" ></path>';
		}

	}
	
	// Für BABs (Verschneidungsoperation in DB nötig)
	if($Ebene == "bab")
	{
		
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




// SVG Ebenen generieren
// ------------------------------------------------------------------------------------------------------------------------
foreach($_SESSION['Datenbestand'] as $DatenSet)
{
	

	if($DatenSet['View']=='1' and $_SESSION['Dokument']['Fuellung']['Indikator'])
	{

		if(is_array($DatenSet['Auswahlkriterium_Wert']))
		{
			// Auswahlkriterium_Werte aus Array aufbereiten
			$AWerte_SQL=''; // Variable leeren
			foreach($DatenSet['Auswahlkriterium_Wert'] as $AWert) // Auswahl SQL-Beginn oder Erweiterung
			{
				if($AWerte_SQL)
				{
					$AWerte_SQL = $AWerte_SQL." OR ".$DatenSet['Auswahlkriterium']." ".$DatenSet['Auswahloperator']
					." '".$DatenSet['AuswahloperatorZusatz_1'].$AWert.$DatenSet['AuswahloperatorZusatz_2']."' ";
				}
				else
				{
					$AWerte_SQL = " AND (".$DatenSet['Auswahlkriterium']." ".$DatenSet['Auswahloperator']
					." '".$DatenSet['AuswahloperatorZusatz_1'].$AWert.$DatenSet['AuswahloperatorZusatz_2']."' ";
				}
			}
			$AWerte_SQL = $AWerte_SQL.")"; // SQL-Klammer schließen
		}
		
		// an der Stelle lassen, da in direktem Zusammenhang mit $AWerte_SQL (übersichtlicher)
		// Zusätzliche Ebenen einblenden $Zusatzebene[$i_ZEb]
		// Annahme von max 10 Zusatzebenen
		for($i_ZEb=0 ; $i_ZEb < 10 ; $i_ZEb++)
		{
			$zusatz[$Zusatzebene[$i_ZEb]] = zusatzebenen($Zusatzebene[$i_ZEb],$AWerte_SQL,$zusatz[$Zusatzebene[$i_ZEb]]);
		}

		
			
		// Geometrien erfassen und Zeichenvorschriften definieren  // ,0,0) für reine Stützpunkte, ,1,0) für Pfaddefinition
		//------------------------------------------------------------------------------------------------------------------
			
		/* Simplify durch Vorberechnung mittels ArcMap/Simplify() ersetzt
		$SQL_PostGIS = "SELECT gen,ags,AsSvg(Simplify(the_geom,'".$_SESSION['Dokument']['Praezision'] ."'),1,0) AS geometrie,AsSvg(centroid(the_geom)) AS centroid,box2d(the_geom) AS bbox FROM "
		.$DatenSet['DB_Tabelle']." WHERE gid > '0' ".$AWerte_SQL; */
		
		$SQL_PostGIS = "SELECT ags,gen,AsSvg(the_geom,1,0) AS geometrie,AsSvg(centroid(the_geom)) AS centroid,box2d(the_geom) AS bbox FROM "
		.$DatenSet['DB_Tabelle']." WHERE gid > '0' ".$AWerte_SQL;
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
			// --> auf Linuxmaschine nicht nötig (liegt als utf8 in DB vor): $Obj_Info_Name = utf8_encode($Obj_Info_Name);
			
			
			// Textobjekt bei Bedarf (zu lang) umbrechen
			// -------------------------------
			if(strlen($Obj_Info_Name) < 24)
			{
				$Obj_Info_Name_z1 = $Obj_Info_Name;
				$Obj_Info_Name_z2 = "";
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
			$X_Text = $X_Centroid - abs(0.4*(abs($X_Centroid) - abs($BBox_X1)));
			$X_Centroid = $X_Text;
			
			$Y_Text = $Y_Centroid - abs(0.2*(abs($Y_Centroid) - abs($BBox_Y1)));
			$Y_Centroid = $Y_Text;
			
			// ------------------------------------------------------------

			
			// <------------ noch erweiterbar!!!
			
			
			
			// Flächenfüllung generieren
			if(is_array($_SESSION['Dokument']['Fuellung']))
			{
				// Füllung nach Min und Max als Farbverteilung
				if($_SESSION['Dokument']['Fuellung']['Typ']=='Farbbereich')
				{
					// Min-Max Berechnung vom Beginn des Programms heranziehen
					// Berechnung für einzelne Farbanteile
					// $i_Betrag = ($AGS_mit_Werten[$ags] - $i_min);
					
					// Division durch Null vermeiden (verhindert zwar Fehlermeldungen, gibt aber sinnlose Ergebnisse aus, zumindest ermöglicht es eine Fehleranalyse unter Verwendung des Viewers)
					if(($Wertebereich = $i_max - $i_min) > 0) { $Wertebereich = $i_max - $i_min; }else{ $Wertebereich = 1; }
					
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
						foreach($_SESSION['Temp']['Klasse'] as $Klassensets)
						{
							// (Einbeziehen des Untersten Wertes in die 1. Klasse) OR Verteilen der Restlichen Werte auf Klassen)
							if( 
							( $Klassensets['Wert_Untergrenze'] == $_SESSION['Dokument']['Fuellung']['Indikator_Wert_min_Dok_rounded'] and $Klassensets['Wert_Obergrenze'] >= $AGS_mit_Werten[$ags]) 
								or  
							($Klassensets['Wert_Untergrenze'] < $AGS_mit_Werten[$ags] and $Klassensets['Wert_Obergrenze'] >= $AGS_mit_Werten[$ags]))
							{
								$Fuellung_Obj_Farbe = "#".$Klassensets['Farbwert'];
								$AGS_mit_Farbcode[$ags] = "k_".$Klassensets['Farbwert']; // für Mouseover-Effekte in Bezug auf Legende
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
			
			
			
			
			
			// Mouseover
			$JS_Events = ' onmouseover="myinit(';
			$JS_Events = $JS_Events."'e_".$ags."','#".$_SESSION['Dokument']['Strichfarbe_MouseOver']."','1','".$Obj_Info_Name_z1."','".$Obj_Info_Name_z2."','"
			.$wert." ".$Einheit_Anz."','"
			.$AGS_mit_Aktualitaet_Jahr_Mittel[$ags]."','"
			.$AGS_mit_Farbcode[$ags]."','#000000','".$AGS_mit_Hinweisen[$ags]."')"; 
			// Mouseout
			$JS_Events = $JS_Events.'" onmouseout="myinit(';
			$JS_Events = $JS_Events."'e_".$ags."','#".$_SESSION['Dokument']['Strichfarbe']."','0','','','','','".$AGS_mit_Farbcode[$ags]."','none','')"; 
											// die Füllung $Fuellung_Obj_Farbe verwenden und JS-Funktion im Head anpassen
											
			// Onclick
			$JS_Events = $JS_Events.'" onclick="einblenden(';
			$JS_Events = $JS_Events."'Label_".$ags."')";
			$JS_Events = $JS_Events.'" ';
			
			// JScript bei leeren Elementen abschalten
			//if($AGS_mit_Werten[$ags] == "keine Informationen") $JS_Events = '';
			
			// Einzelne Arrays jedes mal den gleichen Zähler und Zählen somit gemeinsam hoch
			// Pfad
			/* $Ausgabe[$DatenSet['NAME']][] = '<g id="'.$ags
											.'" '.$JS_Events
											.'stroke-width="'.$_SESSION['Dokument']['Strichstaerke']
											.'" stroke="#'.$_SESSION['Dokument']['Strichfarbe'] 
											.'"><path id="path_'.$ags.'" fill="'.$Fuellung_Obj_Farbe.'" d="'.$PG_Zeile['geometrie'].'" ></path></g>';  */
											
			$Ausgabe[$DatenSet['NAME']][] = '<g id="e_'.$ags
											.'" '.$JS_Events
											.'stroke-width="'.$_SESSION['Dokument']['Strichstaerke']
											.'" stroke="#'.$_SESSION['Dokument']['Strichfarbe'] 
											.'" fill="'.$Fuellung_Obj_Farbe.'" ><path id="path_e_'.$ags.'"  d="'.$PG_Zeile['geometrie'].'" ></path></g>'; 
			
			
			
			
			// Beschriftung: wird weiter unten (in SVG-Stream) zusammengefügt (an transformierte Koordinaten), sonst wird Textlänge mit gekürzt
			$Ausgabe_Beschriftung_X[$DatenSet['NAME']][] = $X_Centroid;
			$Ausgabe_Beschriftung_Y[$DatenSet['NAME']][] = $Y_Centroid;
			$Ausgabe_Beschriftung_Text[$DatenSet['NAME']][] = $Obj_Info_Name;
			$Ausgabe_Beschriftung_AGS[$DatenSet['NAME']][] = $ags;	
			
			
			
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
	}
}







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
echo '<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN"  "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">
<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:ev="http://www.w3.org/2001/xml-events" version="1.1" baseProfile="full" 
viewBox="0 0 '.$Dok_width.' '.$Dok_height.'" width="'.$Dok_width.'px" height="'.$Dok_height.'px">';

// JavaScript Funktionen für Interaktivität
echo "<defs>
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
				}
				else
				{
					document.getElementById(ElementID).setAttributeNS(null,'display','none');
					
				}
			}
			
			
		]]>
	</script>";
	
// Pattern für Schraffur leerer Polygone, mit Anpassung an Karten-Skalierungsfaktor $s	
$pgroesse_Ausgangswert = 5;
$pattern_groesse = $pgroesse_Ausgangswert*(1/$s);
$pattern_Strichstaerke = 100;
echo '	<pattern id="leerschraff" patternUnits="userSpaceOnUse" viewBox="0 0 1000 1000" width="'.$pattern_groesse.'" height="'.$pattern_groesse.'">
      		<desc>'.$s.'</desc>
			<rect x="0" y="0" width="1000" height="1000" fill="white" />
			<line x1="0" y1="0" x2="1000" y2="1000" stroke-width="'.$pattern_Strichstaerke.'" stroke="#'.$_SESSION['Dokument']['Fuellung']['LeerFarbe'].'" />
			<line x1="-1000" y1="0" x2="1000" y2="2000" stroke-width="'.$pattern_Strichstaerke.'" stroke="#'.$_SESSION['Dokument']['Fuellung']['LeerFarbe'].'" />
			<line x1="0" y1="-1000" x2="2000" y2="1000" stroke-width="'.$pattern_Strichstaerke.'" stroke="#'.$_SESSION['Dokument']['Fuellung']['LeerFarbe'].'" />
    	</pattern>'; 

// Pattern für Schraffur leerer Polygone in der Legende (feste Größe)
$pgroesseleg = $pgroesse_Ausgangswert;
$strichstpattleg = $pattern_Strichstaerke/100;
echo '	<pattern id="leerschraff_legende" patternUnits="userSpaceOnUse" viewBox="0 0 10 10" width="'.$pgroesseleg.'" height="'.$pgroesseleg.'">
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
		echo '	<pattern id="errschraff'.$_SESSION['Dokument']['Fuellung']['Fehlercodes'][$FCod]['FEHLERCODE'].'" patternUnits="userSpaceOnUse" viewBox="0 0 1000 1000" width="'.$pattern_groesse.'" height="'.$pattern_groesse.'">
					<desc>'.$s.'</desc>
					<rect x="0" y="0" width="1000" height="1000" fill="white" />
					<line x1="0" y1="0" x2="1000" y2="1000" stroke-width="'.$pattern_Strichstaerke.'" stroke="#'.$_SESSION['Dokument']['Fuellung']['Fehlercodes'][$FCod]['FEHLER_FARBCODE'].'" />
					<line x1="-1000" y1="0" x2="1000" y2="2000" stroke-width="'.$pattern_Strichstaerke.'" stroke="#'.$_SESSION['Dokument']['Fuellung']['Fehlercodes'][$FCod]['FEHLER_FARBCODE'].'" />
					<line x1="0" y1="-1000" x2="2000" y2="1000" stroke-width="'.$pattern_Strichstaerke.'" stroke="#'.$_SESSION['Dokument']['Fuellung']['Fehlercodes'][$FCod]['FEHLER_FARBCODE'].'" />
				</pattern>'; 
		
		// Pattern für Schraffur leerer Polygone in der Legende (feste Größe)
		$pgroesseleg = $pgroesse_Ausgangswert;
		$strichstpattleg = $pattern_Strichstaerke/100;
		echo '	<pattern id="errschraff'.$_SESSION['Dokument']['Fuellung']['Fehlercodes'][$FCod]['FEHLERCODE'].'_legende" patternUnits="userSpaceOnUse" viewBox="0 0 10 10" width="'.$pgroesseleg.'" height="'.$pgroesseleg.'">
					<desc>'.$s.'</desc>
					<rect x="0" y="0" width="10" height="10" fill="white" />
					<line x1="0" y1="0" x2="10" y2="10" stroke-width="'.$strichstpattleg.'" stroke="#'.$_SESSION['Dokument']['Fuellung']['Fehlercodes'][$FCod]['FEHLER_FARBCODE'].'" />
					<line x1="-10" y1="0" x2="10" y2="20" stroke-width="'.$strichstpattleg.'" stroke="#'.$_SESSION['Dokument']['Fuellung']['Fehlercodes'][$FCod]['FEHLER_FARBCODE'].'" />
					<line x1="0" y1="-10" x2="20" y2="10" stroke-width="'.$strichstpattleg.'" stroke="#'.$_SESSION['Dokument']['Fuellung']['Fehlercodes'][$FCod]['FEHLER_FARBCODE'].'" />
				</pattern>'; 
	}	
}
echo "</defs>";


//---------------------------------------------------------------------------------
// Zukünftig je nach Sprachwahl aus DB holen!
// temporär hier definierte Textbausteine <- müssen bei Übersetzung aus DB kommen

// Verbindung zu DB monitor_svg
mysqli_select_db($Verbindung,"monitor_svg");

// Auslesen der Sprachschnipsel
$Kartentitel = utf8_encode("IÖR-Monitor");
$Karte_leer = utf8_encode("Noch keine Daten ausgewählt.");
$Datenset_Trenner = "Gebiet: ";
$Raumgliederung_Trenner = "Gliederung: ";
$Indikator_Trenner = "Indikator: ";

$Grundaktualitaet_Legende = utf8_encode('<a xlink:href="../index.php?id=88" target="_blank">Mittlere Grundaktualität</a>');
$Grundaktualitaet_Legende_Untertitel = 'Abweichung vom Zeitschnitt';
$Grundaktualitaet_AktVorschau = utf8_encode('<a xlink:href="../index.php?id=88" target="_blank">Abweichung der Mittl. Grundaktualität</a>');
$Grundaktualitaet_AktTitel = utf8_encode('Abweichung der Mittleren Grundaktualität vom gewählten Zeitschnitt: '.$_SESSION['Dokument']['Jahr_Anzeige']);

$Datengrundlage = utf8_encode("Datengrundlage:");
$Erleuterungen = utf8_encode("Informationen zur Kennzahl/ Indikator"); 


// Raumgliederung erfassen ...??? Name ist doch bekannt!?
$SQL_Raumgliederung_Anz = "SELECT NAME, Raumgliederung_HTML FROM v_raumgliederung WHERE DB_Kennung = '".$_SESSION['Dokument']['Raumgliederung']."'";
$Ergebnis_Raumgliederung_Anz = mysqli_query($Verbindung,$SQL_Raumgliederung_Anz);

$Raumgliederung_Ausgabe = $Raumgliederung_Trenner.utf8_encode(@mysqli_result($Ergebnis_Raumgliederung_Anz,0,'Raumgliederung_HTML'));
$_SESSION['Dokument']['Raumgliederung_Ausgabe']	= utf8_encode(@mysqli_result($Ergebnis_Raumgliederung_Anz,0,'Raumgliederung_HTML')); // für Tabellenansicht usw. im Session-Array sinnvoll aufgehoben!



// gewählten Indikator erfassen
//$Indikator_Beschreibung = $_SESSION['Dokument']['Fuellung']['Indikator_Beschreibung']; // ist schon UTF(-codiert
//$Indikator_Ausgabe = $Indikator_Trenner.$Indikator_Beschreibung;



// $BKG_Hinweis = utf8_encode('Geometrische Grundlage: VG250 © Bundesamt für Kartographie und Geodäsie (<a xlink:href="http://www.bkg.bund.de" target="_blank">www.bkg.bund.de</a>)');
$Jahr_Ausgabe = 'Jahr: '.$_SESSION['Dokument']['Jahr_Geometrietabelle'];
$Herausgeber = "Herausgeber:";
$Legende = "Legende";
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
$Datengrundl_Inhalt_1 = utf8_encode('ATKIS Basis DLM, Vermessungsverwaltungen der Länder und BKG, ').$Jahr_plus_eins = ($_SESSION['Dokument']['Jahr_Anzeige']+1);
$Datengrundl_Inhalt_2 = utf8_encode('Bundesamt für Kartographie und Geodäsie, VG250, Gebietsstand: '.$_SESSION['Dokument']['Jahr_Geometrietabelle']);
$Datengrundl_Inhalt_3 = utf8_encode('<a xlink:href="http://www.bkg.bund.de" target="_blank">© Vermessungsverwaltungen der Länder und BKG '.$jhr=($_SESSION['Dokument']['Jahr_Geometrietabelle']+1).' (www.bkg.bund.de)</a>');
// $Datengrundl_Inhalt_3 = utf8_encode($Datengrundl_Inhalt_3);


 
// Copyright in Kartenfeld
$Copyright = utf8_encode('© Leibniz-Institut für ökologische Raumentwicklung');

// IOER Logo includieren => in Variable $LOGO verfügbar
include("../includes_classes/ioer_logo_svg.php");

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
$ObjInfofeld_Rand_oben = 410;
$ObjInfofeld_Titel = 'Markiertes Gebiet';
$ObjInfofeld_Titel_1 = 'Name';
$ObjInfofeld_Titel_2 = utf8_encode('Indikator-Wert');
$ObjInfofeld_Y = $YD-110; // wird dann systematisch um 15 erweitert <= Zeilensprung pro TextObjekt
$ObjInfofeld_Bemerkung = utf8_encode("Für Beschriftung bitte anklicken.");
$ObjInfofeld_Grundakt = utf8_encode('Mittlere Grundaktualität:');
	
	

//----------------------------------------------------------------------------------


// SVG mit Daten füllen
// ----------------------------------------------------------


if(!$_SESSION['Dokument']['Raumgliederung'] or !$_SESSION['Dokument']['Fuellung']['Indikator'] or $Region!='1')
{ 
	// Meldung und Deutschlanbild, wenn Karte noch leer => ansonsten Karte füllen
	// -----------------------------------------------------------------------------
	echo '<g id="Leere Karte">';
	echo '
				<image x="-75" y="0" width="'.$X_Leer = ($_SESSION['Dokument']['groesse_X']+150)
				.'px" height="'.$Y_Leer = ($_SESSION['Dokument']['groesse_Y']+150)
				.'px" xlink:href="gfx/deutschland_leer.png" id="Keine Daten" style="opacity:0.4;" ></image>
				<text x="'.$xleer=($XD/2-300).'" y="'.$xleer=($YD/2).'" dx="" dy="" style="font-size:28px; font-family:Arial; font-weight:bold;" fill="#999999" >'.$Karte_leer.'</text>
				<text x="'.$xleer=($XD/2-300).'" y="'.$xleer=(($YD/2)+50).'" dx="" dy="" style="font-size:28px; font-family:Arial; font-weight:bold;" fill="#999999" >'.$_SESSION['ID_Loginfehler'].'</text>';
	echo '</g>';
}
else
{
	
	
	
	
	
	
	// im unlegend-Betrieb nicht ausgeben
	if(!$_SESSION['Dokument']['unlegend'])
	{
		// Hintergrundfarbe zeichnen
		echo '<g><rect x="0" y="0" width="'.$XD.'px" height="'.$YD.'px" fill="#FFFFFF" stroke="none"/></g>'; 
	}
	
	// Hintergrundebene-Deutschland-Bundesländer einfügen
	echo '<g transform="matrix('.$s.' 0 0 '.$s.' '.$X_min.' '.$Y_max.')" id="hg_'.$DatenSet['NAME'].'" >';
	$i_array=0;
	while($Ausgabe['Hintergrund'][$i_array])
	{
		echo $Ausgabe['Hintergrund'][$i_array]; // Wichtig an der Stelle: Schreiben der Pfade (ohne nochmals zusammensetzen zu müssen) direkt aus Array <= superperformant !!!
		$i_array++;
	}			
	echo "</g>";
			
	// berechnete Inhalte der EbenenObjekte in SVG-Datei einfügen
	foreach($_SESSION['Datenbestand'] as $DatenSet)
	{
		if($DatenSet['View']!='0')
		{		
			// Transformation ohne Verwendung von Ebenen auch direkt in Path integrierbar... aber nicht unbedingt nötig/sinnvoll
			echo '<g transform="matrix('.$s.' 0 0 '.$s.' '.$X_min.' '.$Y_max.')" id="ds_'.$DatenSet['NAME'].'">
			<desc>'.$_SESSION['Datenbestand'][$DatenSet['NAME']]['X_min_global'].' '.$_SESSION['Datenbestand'][$DatenSet['NAME']]['Y_max_global'].'</desc>';
			
			$i_array=0;
			while($Ausgabe[$DatenSet['NAME']][$i_array])
			{
				echo $Ausgabe[$DatenSet['NAME']][$i_array]; // Wichtig an der Stelle: Schreiben der Pfade (ohne nochmals zusammensetzen zu müssen) direkt aus Array <= superperformant !!!
				$i_array++;
			}	
			 // Marker für Mouseover		
			echo "</g>"; 
			
		}
			
	}



	//-------------------->
	// Zeichnen von Zusatzebenen (Flüsse, VG, BABs, ...)
	for($i_ZEb=0 ; $i_ZEb < 10 ; $i_ZEb++)
	{
		// Standards
		// Farben 
		$Z_Color = '#FFFFFF';
		// Strich-Breite
		$Z_stroke_width = 1.3/$s;
		
		
		// Abweichende Einstellungen
		if($Zusatzebene[$i_ZEb] == "gew") 
		{
			// Maßstabsabhängig
			$Z_stroke_width_raw = 0.5; // > M 200km
			if($s > 0.0009) {$Z_stroke_width_raw = 1; } // M 100km
			if($s > 0.002) {$Z_stroke_width_raw = 1.3; } // M 50km
			if($s > 0.005) {$Z_stroke_width_raw = 1.3; } // M 10km
			$Z_stroke_width = $Z_stroke_width_raw/$s;
			// Farbe
			$Z_Color = '#0000CC';
		}
		if($Zusatzebene[$i_ZEb] == "BAB") 
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
		echo zusatz_zeichnen($Zusatzebene[$i_ZEb],$zusatz[$Zusatzebene[$i_ZEb]],$s,$X_min,$Y_max,$Str_wdt_z = ($Z_stroke_width),$Z_Color); // $Str_wdt_z = (2/$s) Pixelbreite 3px in jedem Maßstab einhalten
	}
	//<-------------------





	// Hinweis: Beschriftung von hier ans Ende (im SVG-Dokument nach oben) gelegt


	// Selektierte Datensets(Regionen) herausfinden
	$_SESSION['Datenbestand_Ausgabe'] = "";
	if(is_array($_SESSION['Dokument']['Raumebene']))
	{
		foreach($_SESSION['Datenbestand'] as $DatenSet)
		{
			if($DatenSet['View'] == '1')
			{
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
		echo '<g><rect x="0" y="'.$YD.'" width="'.$XD.'px" height="'.$_SESSION['Dokument']['Hoehe_Legende_unten'].'px" fill="#FFFFFF" stroke="none" stroke-width="1" /></g>'; 
			
		// Legendenfeld (rechts) Hintergrund und Randlinie links
		echo '<g>
				<rect x="'.$xl=($XD-190).'" y="42" width="186px" height="'.$hl=($YD-36).'px" fill="#FFFFFF" stroke="#555555" stroke-width="0px" opacity="1" />
				<rect x="'.$xl=($XD-190).'" y="46" width="2px" height="'.$hl=($YD-46).'" fill="#999999" stroke="none" />';
		echo '<text x="'.$xl=($XD-180).'" y="60" dx="" dy="" style="font-size:12px; font-weight:bold; font-family:Arial;" fill="#444444" >'.$Herausgeber.'</text>';
		
		// Logo mit Link und Skalierung des includierten SVG-Logos
		echo '<a id="logolink" 
			onmouseover="document.getElementById(\'logolink\').setAttributeNS(null,\'text-decoration\',\'underline\');"
			onmouseout="document.getElementById(\'logolink\').setAttributeNS(null,\'text-decoration\',\'none\');" 
			xlink:href="http://www.ioer.de" target="_blank"><text x="'.$xl=($XD-180).'" y="133" dx="" dy="" style="font-size:10px; font-family:Arial;" fill="#444444" >'.$IOER_d.'</text></a>';
		
		// Logo mit Link, falls nötig.... neuerdings ohne!
		echo '<a 
			onmouseover="document.getElementById(\'logolink\').setAttributeNS(null,\'text-decoration\',\'underline\');"
			onmouseout="document.getElementById(\'logolink\').setAttributeNS(null,\'text-decoration\',\'none\');" 
			xlink:href="http://www.ioer.de" target="_blank"><g id="Logo" transform="matrix(0.5, 0, 0,0.5, '.$xl=($XD-270).', 10)">'.$LOGO.'</g></a>'; 
		
		// echo '<g id="Logo" transform="matrix(0.5, 0, 0,0.5, '.$xl=($XD-270).', 10)">'.$LOGO.'</g>'; 
			
		// echo '<line x1="'.$xl=($XD-185).'" y1="145" x2="'.$xl=($XD-5).'" y2="145" style="stroke:rgb(99,99,99); stroke-width:1" />';
		echo '<rect x="'.$xl=($XD-185).'" y="145" width="180px" height="2px" fill="#999999" stroke="none" />';
		echo '<text x="'.$xl=($XD-180).'" y="165" dx="" dy="" style="font-size:12px; font-weight:bold; font-family:Arial;" fill="#444444" >'.$Legende.'</text>';
		echo '<text x="'.$xl=($XD-180).'" y="185" dx="" dy="" style="font-size:11px; font-family:Arial;" fill="#222222" >Einheit: '.$_SESSION['Dokument']['Fuellung']['Indikator_Einheit'].'</text>';
		
		// Legende-Indikator
		switch ($_SESSION['Dokument']['Fuellung']['Typ']) {
			case 'Farbbereich':
				// X Verschiebung
				$xv=150;
				// IndikatorenFarbbereich definiert im SVG-Kopf unter <defs>
				echo '<defs>
						<linearGradient id="IndikatorenFarbbereich" x1="0" x2="0" y1="0%" y2="100%">
							<stop offset="0%" stop-color="#'.$_SESSION['Dokument']['Fuellung']['Farbwert_max'].'" />
							<stop offset="100%" stop-color="#'.$_SESSION['Dokument']['Fuellung']['Farbwert_min'].'" />
						</linearGradient>
					 </defs>';
				// echo '<rect x="'.$xl=($XD-180).'" y="'.$Fy = ($Farbverlauf_Y).'" width="20px" height="100px" style="fill: url(#IndikatorenFarbbereich)" stroke="#555555" stroke-width="1" />';
				echo '<rect x="'.$xl=($XD-180).'" y="'.$Fy = ($Farbverlauf_Y).'" width="20px" height="100px" style="fill: url(#IndikatorenFarbbereich)" />'; 
				echo '<text x="'.$xl=($XD-150).'" y="'.$Fy = ($Farbverlauf_Y+5).'" style="font-size:11px; font-family:Arial;">'.
																	number_format($_SESSION['Dokument']['Fuellung']['Indikator_Wert_max']-1000000000,$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.')
																	.'</text>';
				// Hinweis auf deutschlandweites Wertespektrum, wenn gewählt
				if($_SESSION['Dokument']['indikator_lokal'] == '0')
				{
					echo '<text x="'.$xl=($XD-150).'" y="'.$Fy = ($Farbverlauf_Y+25).'" style="font-size:11px; font-family:Arial;">'.utf8_encode('Hinweis:').'</text>';
					echo '<text x="'.$xl=($XD-150).'" y="'.$Fy = ($Farbverlauf_Y+40).'" style="font-size:11px; font-family:Arial;">'.utf8_encode('Das Wertespektrum bezieht').'</text>';
					echo '<text x="'.$xl=($XD-150).'" y="'.$Fy = ($Farbverlauf_Y+55).'" style="font-size:11px; font-family:Arial;">'.utf8_encode('sich auf das gesamte Gebiet').'</text>';
					echo '<text x="'.$xl=($XD-150).'" y="'.$Fy = ($Farbverlauf_Y+70).'" style="font-size:11px; font-family:Arial;">'.utf8_encode('der Bundesrepublik.').'</text>';
				}
				echo '<text x="'.$xl=($XD-150).'" y="'.$Fy = ($Farbverlauf_Y+100).'" style="font-size:11px; font-family:Arial;">'.
																	number_format($_SESSION['Dokument']['Fuellung']['Indikator_Wert_min']-1000000000,$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.')
																	.'</text>';  
				
				// Boxen für Verortungs-Anzeige bei Mouseover über Objekte
				for($i_box = 0; $i_box <= 100 ; $i_box++)
				{
					echo '<rect id="v_'.$i_box.'" x="'.$xl=($XD-181).'" y="'.$Fy = ($Farbverlauf_Y + 100 - $i_box).'" width="22px" height="1px" style="fill:none;" stroke-width="1" stroke="none" />'; 
				}
				
				
				$klasse_Y = $Farbverlauf_Y + 120;
				// Darstellung für leere Objekte, falls vorhanden
				if($Leerwerte_vorhanden == 1)
				{	 
					echo'<rect id="f_leer" x="'.$xl=($XD-180).'" y="'.$klasse_Y.'" width="20px" height="15px" fill="url(#leerschraff_legende)" stroke="none" stroke-width="2" />'; 
					echo '<text x="'.$xl=($XD-150).'" y="'.$ky = ($klasse_Y+11).'" style="font-size:11px; font-family:Arial;">keine Werte vorhanden</text>';
					$klasse_Y = $klasse_Y + 16;
				}
				
				if(is_array($Fehlerwerte_vorhanden_Array))
				{
					foreach($Fehlerwerte_vorhanden_Array as $FCod)
					{
						echo'<rect id="f_'.$_SESSION['Dokument']['Fuellung']['Fehlercodes'][$FCod]['FEHLERCODE']
						.'"  x="'.$xl=($XD-180).'" y="'.$klasse_Y.'" width="20px" height="15px" fill="url(#errschraff'.$_SESSION['Dokument']['Fuellung']['Fehlercodes'][$FCod]['FEHLERCODE']
						.'_legende)" stroke="none" stroke-width="2" />'; 
						echo '<text x="'.$xl=($XD-150).'" y="'.$ky = ($klasse_Y+11).'" style="font-size:11px; font-family:Arial;">'.$_SESSION['Dokument']['Fuellung']['Fehlercodes'][$FCod]['FEHLER_NAME']
						.'</text>';
						$klasse_Y = $klasse_Y + 16;
					}
				}
				
		
				
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
							echo'<rect id="k_'.$Farbe.'" x="'.$xl=($XD-180).'" y="'.$klasse_Y.'" width="15px" height="10px" fill="#'.$Farbe.'" stroke="none" stroke-width="2" />'; 
							echo '<text x="'.$xl=($XD-160).'" y="'.$ky = ($klasse_Y+8).'" style="font-size:9px; font-family:Arial;"> >'
							.number_format($Untergrenze,$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.').
							' bis '.number_format($Obergrenze,$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.').'</text>';
							$klasse_Y = $klasse_Y + 16;
						}
						
						$Untergrenze = round($Klassensets['Wert_Untergrenze']-1000000000,$_SESSION['Dokument']['Fuellung']['Rundung']);
						// Oberste Klassengrenze auf Dokumentwert beziehen (sonst Rundungsfehler denkbar)
						if($KlasseNull != 'nein') $Obergrenze = $_SESSION['Dokument']['Fuellung']['Indikator_Wert_max_Dok_rounded']-1000000000; 
						if($KlasseNull == 'nein') $Obergrenze = round($Klassensets['Wert_Obergrenze']-1000000000,$_SESSION['Dokument']['Fuellung']['Rundung']); 
						$KlasseNull = 'nein';
						$Farbe = $Klassensets['Farbwert'];
					}
					
					// letzte Klasse abbilden (ohne nochmaligen Schleifendurchlauf)
					echo'<rect  id="k_'.$Klassensets['Farbwert'].'" x="'.$xl=($XD-180).'" y="'.$klasse_Y.'" width="15px" height="10px" fill="#'.$Klassensets['Farbwert'].'" stroke="none" stroke-width="2" />'; 
					echo '<text x="'.$xl=($XD-160).'" y="'.$ky = ($klasse_Y+8).'" style="font-size:9px; font-family:Arial;">'.
										$w = number_format($_SESSION['Dokument']['Fuellung']['Indikator_Wert_min_Dok_rounded']-1000000000,$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.').
										' bis '.number_format($Obergrenze,$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.').'</text>';
					
					// Darstellung für leere Objekte, falls vorhanden
					if($Leerwerte_vorhanden == 1)
					{
						$klasse_Y = $klasse_Y + 16;	 
						echo'<rect id="f_leer" x="'.$xl=($XD-180).'" y="'.$klasse_Y.'" width="15px" height="10px" fill="url(#leerschraff_legende)" stroke="none" stroke-width="2" />'; 
						echo '<text x="'.$xl=($XD-160).'" y="'.$ky = ($klasse_Y+8).'" style="font-size:9px; font-family:Arial;">keine Werte vorhanden</text>';
					}
					
					if(is_array($Fehlerwerte_vorhanden_Array))
					{
						foreach($Fehlerwerte_vorhanden_Array as $FCod)
						{
							$klasse_Y = $klasse_Y + 16;
							echo'<rect id="f_'.$_SESSION['Dokument']['Fuellung']['Fehlercodes'][$FCod]['FEHLERCODE']
							.'" x="'.$xl=($XD-180).'" y="'.$klasse_Y.'" width="15px" height="10px" fill="url(#errschraff'.$_SESSION['Dokument']['Fuellung']['Fehlercodes'][$FCod]['FEHLERCODE']
							.'_legende)" stroke="none" stroke-width="2" />'; 
							echo '<text x="'.$xl=($XD-160).'" y="'.$ky = ($klasse_Y+8).'" style="font-size:9px; font-family:Arial;">'.$_SESSION['Dokument']['Fuellung']['Fehlercodes'][$FCod]['FEHLER_NAME']
							.'</text>';
						}
					}
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
							echo'<rect id="k_'.$Farbe.'" x="'.$xl=($XD-180).'" y="'.$klasse_Y.'" width="15px" height="10px" fill="#'.$Farbe.'" stroke="none" stroke-width="2" />'; 
							echo '<text x="'.$xl=($XD-160).'" y="'.$ky = ($klasse_Y+8).'" style="font-size:9px; font-family:Arial;"> >'
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
					echo'<rect id="k_'.$Klassensets['Farbwert'].'" x="'.$xl=($XD-180).'" y="'.$klasse_Y.'" width="15px" height="10px" fill="#'.$Klassensets['Farbwert'].'" stroke="none" stroke-width="2" />'; 
					echo '<text x="'.$xl=($XD-160).'" y="'.$ky = ($klasse_Y+8).'" style="font-size:9px; font-family:Arial;">'
								.number_format($Untergrenze,$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.').
								' bis '.number_format($Obergrenze,$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.').'</text>';
					
					// Darstellung für leere Objekte, falls vorhanden
					if($Leerwerte_vorhanden == 1)
					{
						$klasse_Y = $klasse_Y + 16;	 
						echo'<rect id="f_leer" x="'.$xl=($XD-180).'" y="'.$klasse_Y.'" width="15px" height="10px" fill="url(#leerschraff_legende)" stroke="none" stroke-width="2" />'; 
						echo '<text x="'.$xl=($XD-160).'" y="'.$ky = ($klasse_Y+8).'" style="font-size:9px; font-family:Arial;">keine Werte vorhanden</text>';
					}
					
					if(is_array($Fehlerwerte_vorhanden_Array))
					{
						foreach($Fehlerwerte_vorhanden_Array as $FCod)
						{
							$klasse_Y = $klasse_Y + 16;
							echo'<rect id="f_'.$_SESSION['Dokument']['Fuellung']['Fehlercodes'][$FCod]['FEHLERCODE']
							.'"  x="'.$xl=($XD-180).'" y="'.$klasse_Y.'" width="15px" height="10px" fill="url(#errschraff'.$_SESSION['Dokument']['Fuellung']['Fehlercodes'][$FCod]['FEHLERCODE']
							.'_legende)" stroke="none" stroke-width="2" />'; 
							echo '<text x="'.$xl=($XD-160).'" y="'.$ky = ($klasse_Y+8).'" style="font-size:9px; font-family:Arial;">'.$_SESSION['Dokument']['Fuellung']['Fehlercodes'][$FCod]['FEHLER_NAME']
							.'</text>';
						}
					}
				}
			break;
		}
		
		// Linienelemente und Konturen
		// ---------------------------
		$klasse_Y = $klasse_Y+24; // $klasse_Y wird direkt aus den Füllungsberechnungen übernommen und um einen bestimmten Wert geändert
		// $x1 wird direkt aus den Füllungsberechnungen übernommen
		
		// Objektkontur
		// bei bld & gem zusammen gewählt, nicht anzeigen!  <= da in Karte bei dirser speziellen kombination unterdrückt
		if($_SESSION['Dokument']['Raumebene']['Bundesland']['View'] != '1' or $_SESSION['Dokument']['Raumgliederung'] != 'gem')
		{
			echo'<rect id="legende_kontur"  x="'.$xl=($XD-$xv-20).'" y="'.$y_kont = ($klasse_Y-3).'" width="15px" height="1px" fill="#'
			.$_SESSION['Dokument']['Strichfarbe'].'" stroke="none" stroke-width="2" />'; 
			// Stringverarbeitung für Sondergebiete (Löschen von Zus. Textbausteinen)
			if($_SESSION['Dokument']['Raumgliederung_Ausgabe'][0] == '*') 
			{
				$_Raumgliederung_Legendentext = substr($_SESSION['Dokument']['Raumgliederung_Ausgabe'],6);
			}
			else
			{
				$_Raumgliederung_Legendentext = $_SESSION['Dokument']['Raumgliederung_Ausgabe'];
			}
			echo '<text x="'.$xl=($XD-$xv).'" y="'.$klasse_Y.'" style="font-size:9px; font-family:Arial;">Grenzen '.$_Raumgliederung_Legendentext.'</text>';
		}
		
		// Zusatz Kreisgrenzen
		if($_SESSION['Dokument']['zusatz_kreis'])
		{
			$klasse_Y = $klasse_Y+16;
			echo'<rect id="legende_kontur"  x="'.$xl=($XD-$xv-20).'" y="'.$y_kont = ($klasse_Y-4).'" width="15px" height="4px" fill="#CCCCCC" stroke="none" stroke-width="2" />'; 
			echo'<rect id="legende_kontur"  x="'.$xl=($XD-$xv-20).'" y="'.$y_kont = ($klasse_Y-3).'" width="15px" height="2px" fill="#FFFFFF" stroke="none" stroke-width="2" />'; 
			echo '<text x="'.$xl=($XD-$xv).'" y="'.$klasse_Y.'" style="font-size:9px; font-family:Arial;">'.utf8_encode('Grenzen Kreise').'</text>';
		}
			
		// Zusatz Bundeslandgrenzen
		if($_SESSION['Dokument']['zusatz_bundesland'])
		{
			$klasse_Y = $klasse_Y+16;
			echo'<rect id="legende_kontur"  x="'.$xl=($XD-$xv-20).'" y="'.$y_kont = ($klasse_Y-4).'" width="15px" height="4px" fill="#CCCCCC" stroke="none" stroke-width="2" />'; 
			echo'<rect id="legende_kontur"  x="'.$xl=($XD-$xv-20).'" y="'.$y_kont = ($klasse_Y-3).'" width="15px" height="2px" fill="#FFFFFF" stroke="none" stroke-width="2" />'; 
			echo '<text x="'.$xl=($XD-$xv).'" y="'.$klasse_Y.'" style="font-size:9px; font-family:Arial;">'.utf8_encode('Grenzen Bundesländer').'</text>';
		}
		echo '</g>';
		
		
		
		
		
		
		
		
		
		/* Histogramm anzeigen: */
				
		echo '<g>
				<defs>
						<linearGradient id="IndikatorenFarbbereich_horizontal" x1="100%" x2="0%" y1="0" y2="0">
							<stop offset="0%" stop-color="#'.$_SESSION['Dokument']['Fuellung']['Farbwert_max'].'" />
							<stop offset="100%" stop-color="#'.$_SESSION['Dokument']['Fuellung']['Farbwert_min'].'" />
						</linearGradient>
					 </defs>';
				echo '<text x="'.$xl=($XD-172).'" y="'.$PosUHist =($YD_gesamt - 263).'" style="font-size:9px; font-family:Arial;">'.utf8_encode('Histogramm').'</text>';
				
				
				// Hintergrundbox mit Klassifikation
				$Hist_Box_Hoehe = 40;
				$Hist_Box_Schrittweite = 1.5;
				
				switch ($_SESSION['Dokument']['Fuellung']['Typ']) {
					case 'Farbbereich':
					
						// Box rechts ein Pix. breiter für bessere Darestellung
						echo '<rect x="'.$xl=($XD-171).'" y="'.$PosUHist =($YD_gesamt - 253).'" 
															width="'.$HBBreite = ($Hist_Box_Schrittweite*102.5).'" 
															height="'.$HBH = ($Hist_Box_Hoehe + 4).'px" 
															style="fill: url(#IndikatorenFarbbereich_horizontal)" 
															stroke="none" />'; 
																				
					break;
					case 'Klassifizierte Farbreihe':
					
						// Einzelne Rechtecke zeichnen	
						for($i=0 ; $i <= 100 ; $i++)
						{
							echo '<rect x="'.$xl=($XD-171+($i*$Hist_Box_Schrittweite)).'" y="'.$PosUHist =($YD_gesamt - 252).'" width="'.$HBSw = ($Hist_Box_Schrittweite + 1.5).'px" 
																																height="'.$HBH = ($Hist_Box_Hoehe + 2).'" style="fill:#';
							// Korrekten Farbwert (Klasse) ermitteln																									
							if(is_array($_SESSION['Temp']['Klasse']))
							{
								foreach($_SESSION['Temp']['Klasse'] as $Klassensets)
								{
									if(($Klassensets['Untergrenze']==0 and $i==0) or ($Klassensets['Untergrenze'] < $i and $Klassensets['Obergrenze'] >= $i))
									{
										echo $Klassensets['Farbwert'];
									}
								}
							}
							echo '" stroke="none" />'; 
						}

					break;
					case 'manuell Klassifizierte Farbreihe':
					
						// Einzelne Rechtecke zeichnen	
						for($i=0 ; $i <= 100 ; $i++)
						{
							echo '<rect x="'.$xl=($XD-171+($i*$Hist_Box_Schrittweite)).'" y="'.$PosUHist =($YD_gesamt - 252).'" width="'.$HBSw = ($Hist_Box_Schrittweite + 1.5).'px" 
																																height="'.$HBH = ($Hist_Box_Hoehe + 2).'" style="fill:#';
							// Korrekten Farbwert (Klasse) ermitteln																									
							if(is_array($_SESSION['Temp']['manuelle_Klasse']))
							{
								foreach($_SESSION['Temp']['manuelle_Klasse'] as $Klassensets)
								{
									if(($Klassensets['Untergrenze']==0 and $i==0) or ($Klassensets['Untergrenze'] < $i and $Klassensets['Obergrenze'] >= $i))
									{
										echo $Klassensets['Farbwert'];
									}
								}
							}
							echo '" stroke="none" />'; 
						}
					
					break;
				}
				
				// Rahmen
				echo '<rect x="'.$xl=($XD-175.5).'" y="'.$PosUHist =($YD_gesamt - 257).'" width="'.$HBBreite = ($Hist_Box_Schrittweite*108).'" 
																				height="'.$HBH = ($Hist_Box_Hoehe + 12).'px" style="fill:none;" stroke="#555555" stroke-width="0.5px" />'; 
				// Säulen einzeichnen	
				for($i=0 ; $i <= 100 ; $i++)
				{
					$Saeulen_Hoehe = $Hist_Box_Hoehe*($_SESSION['Temp']['i_Verteilung'][$i]/$_SESSION['Temp']['i_Verteilung']['Max']); // normalisieren der Werte auf x/30
					$SäulenAnfang_oben = $Hist_Box_Hoehe - $Saeulen_Hoehe;					
					echo '<rect x="'.$xl=($XD-169.5 + ($i * $Hist_Box_Schrittweite)).'" y="'.$PosUHist =($YD_gesamt - 250 + $SäulenAnfang_oben).'" width="1px" height="'.$Saeulen_Hoehe.'" 
																																				style="fill:#000000" stroke="none" />'; 
				}
		echo '</g>';		
		
		
		
		
		
		
		
		
		
		// Aktualität anzeigen (standardmäßig versteckt, wird aber per JScript ein-/ausgeblendet)
		echo '<g id="grundaktlegende" display="none">
				<rect x="'.$xl=($XD-187).'" y="148" width="183px" height="'.$hl=($YD-44).'px" fill="#FFFFFF" stroke="#555555" stroke-width="0px" />
				<text x="'.$xl=($XD-180).'" y="165" dx="" dy="" style="font-size:12px; font-weight:bold; font-family:Arial;" fill="#444444" >'.$Grundaktualitaet_Legende.'</text>
				<text x="'.$xl=($XD-180).'" y="185" dx="" dy="" style="font-size:10px; font-weight:bold; font-family:Arial;" fill="#444444" >'.$Grundaktualitaet_Legende_Untertitel.'</text>';
				
				$Akt_klasse_Y = $k_Y; // Y-Position
				
				for( $i_akt = 0 ; $i_akt <= $Akt_max ; $i_akt++ )
				{
					if($Akt_Differenz_in_karte_vorhanden[$i_akt] or $Akt_Differenz_in_karte_vorhanden[$i_akt]=="0") // Check auf wirkliche Verwendung in der Karte (Variable von Datenerfassung verfügbar)
					{
						echo'<rect x="'.$xl=($XD-180).'" y="'.$Akt_klasse_Y.'" width="15px" height="10px" fill="#'.$FCA_Jahr[$i_akt].'" stroke="#555555" stroke-width="0" />'; 
						if($i_akt == 1) { $AktEinheit = "Jahr"; }else{ $AktEinheit = "Jahre"; }
						echo '<text x="'.$xl=($XD-160).'" y="'.$ky = ($Akt_klasse_Y+8).'" style="font-size:9px; font-family:Arial;">'.$i_akt.' '.$AktEinheit.'</text>';
						$Akt_klasse_Y = $Akt_klasse_Y + 16;
					}
				}	
		echo '</g>'; 	
	} // Ende unlegend
	

	// im unlegend-Betrieb nicht ausgeben
	if(!$_SESSION['Dokument']['unlegend'])
	{ 
		// Karten-Titel
		// ----------------------------------
		echo '<g>';
		// HG weiß
		echo '<rect x="5" y="5" width="'.$X_TitelHG=($XD-10).'px" height="42px" fill="#FFFFFF" stroke-width="0" opacity="1" />'; 
		// Linie unter Titel
		//echo '<rect x="5" y="43" width="'.$PosUX=($XD-5).'" height="1px" fill="#999999" stroke="none" />';
		echo '<rect x="5" y="42" width="'.$PosUX=($XD-5).'" height="2px" fill="#999999" stroke="none" />';
		/* echo '<rect x="10" y="10" width="100px" height="15px" fill="#9FA8CC" stroke-width="0" opacity="1" />'; // HG links
		
		echo '<text x="20" y="22" dx="" dy="" style="font-size:12px; font-family:Arial; font-weight:bold;" fill="#FFFFFF" >'.$Kartentitel.'</text>'; */
		// echo '<text x="15" y="36" dx="" dy="" style="font-size:10px; font-family:Arial;" fill="#444444" >'.$Jahr_Ausgabe.'</text>';
		echo '<text x="15" y="36" dx="" dy="" style="font-size:10px; font-family:Arial;" fill="#444444" >'.$Raumgliederung_Ausgabe.'</text>';
		// Kürzen des Datenset auf Viewerbreite und anhängen von "..."
		if(strlen($Datenset) > 125) $Datenset = substr($Datenset,0,125).",...";
		echo '<text x="160" y="36" dx="" dy="" style="font-size:10px; font-family:Arial;" fill="#444444" >'.$Datenset.'</text>';
		$_SESSION['Dokument']['titelsize'] = "14";
	} // Ende unlegend
	else
	{
		echo '<g>';	
	}
		
	echo '<text x="15" y="20" dx="" dy="" style="font-size:'.$_SESSION['Dokument']['titelsize'].'px; font-weight:bold; font-family:Arial;" fill="#444444" >'
		.$_SESSION['Dokument']['Fuellung']['Indikator_Beschreibung']
		.' ('.$_SESSION['Dokument']['Jahr_Anzeige']
		.')</text>';
	echo '</g>';
		// ----------------------------------



// Maßstabsleiste
	// --------------------------------------

	// wenn bestimmte Maßstabsbereiche eintreten, Länge anpassen: 
	
	$m_strecke = 200000; // Standardwert 200km, bei größeren Maßstäben folgendes:
	if($s > 0.0009) {$m_strecke = 100000; $Strich_2_deakt = 1; } // 100km
	if($s > 0.002) {$m_strecke = 50000; $Strich_2_deakt = 1; } // 50km
	if($s > 0.005) {$m_strecke = 10000; $Strich_2_deakt = 1; } //10km
	
	$m_x=($m_strecke*$s);
	
	echo '<g id="massstabsleiste" >';
	// Symbolik
	echo '<rect x="'.$xm=($XD-220-$m_x).'" y="'.$PosUm=($YD_gesamt-205).'" width="'.$m_x.'px" height="1px" stroke="none" fill="#333333" />';
	echo '<rect x="'.$xm=($XD-220-$m_x).'" y="'.$PosUm=($YD_gesamt-207).'" width="1px" height="3px" stroke="none" fill="#333333" />';
	if(!$Strich_2_deakt) echo '<rect x="'.$xm=($XD-220-($m_x/2)).'" y="'.$PosUm=($YD_gesamt-207).'" width="1px" height="3px" stroke="none" fill="#333333" />'; //Mittlerer Strich nur bei 200km-Leiste
	echo '<rect x="'.$xm=($XD-220).'" 	   y="'.$PosUm=($YD_gesamt-207).'" width="1px" height="3px" stroke="none" fill="#333333" />';
	// Beschriftung
	echo '<text x="'.$xm1=($XD-222-$m_x).'" y="'.$PosUm1=($YD_gesamt-209).'" dx="" dy="" style="font-size:10px; font-family:Arial;" >0</text>';
	echo '<text x="'.$xm2=($XD-230).'" y="'.$PosUm1=($YD_gesamt-209).'" dx="" dy="" style="font-size:10px; font-family:Arial;" >'.$m_str_km = ($m_strecke/1000).'km</text>';
	echo '</g>';
	
	// --------------------------------------


// Copyright im Kartenfeld
	echo '<g id="copyright" >';
	echo '<text x="16" y="'.$PosUm1=($YD_gesamt-205).'" dx="" dy="" style="font-size:9px; font-family:Arial;" ><a target="_blank" xlink:href="http://www.ioer.de">'.$Copyright.'</a></text>';
	echo '</g>';



	
	// im unlegend-Betrieb nicht ausgeben
	if(!$_SESSION['Dokument']['unlegend'])
	{
		// Legendenbox am unteren Kartenrand
		// ersetzt durch Datengrundlage: echo '<g><text x="20" y="'.$PosU=($YD_gesamt-10).'" dx="" dy="" style="font-size:9px; font-family:Arial;" opacity="0.5" >'.$BKG_Hinweis.'</text></g>';
		echo '<rect x="5" y="'.$PosU=($YD_gesamt-197).'" width="'.$PosUX=($XD-5).'" height="2px" fill="#999999" stroke="none" />'; // Begrenzungslinie oben
		
		echo '<g fill="#444444" opacity="1" >';
		// echo '<text x="15" y="'.$PosU=($YD_gesamt-180).'" dx="" dy="" style="font-size:14px; font-family:Arial;" >'.$_SESSION['Dokument']['Fuellung']['Indikator_Beschreibung'].'</text>';
		echo '<text x="15" y="'.$PosU=($YD_gesamt-176).'" dx="" dy="" style="font-size:12px; font-family:Arial;" >'.$Erleuterungen.'</text>';
		echo '<text x="15" y="'.$PosU=($YD_gesamt-160).'" dx="" dy="" style="font-size:10px; font-family:Arial;" >'.$GLOBALS['INFO_VIEWER_ZEILE_1'].'</text>';
		echo '<text x="15" y="'.$PosU=($YD_gesamt-148).'" dx="" dy="" style="font-size:10px; font-family:Arial;" >'.$GLOBALS['INFO_VIEWER_ZEILE_2'].'</text>';
		echo '<text x="15" y="'.$PosU=($YD_gesamt-136).'" dx="" dy="" style="font-size:10px; font-family:Arial;" >'.$GLOBALS['INFO_VIEWER_ZEILE_3'].'</text>';
		echo '<text x="15" y="'.$PosU=($YD_gesamt-124).'" dx="" dy="" style="font-size:10px; font-family:Arial;" >'.$GLOBALS['INFO_VIEWER_ZEILE_4'].'</text>';
		echo '<text x="15" y="'.$PosU=($YD_gesamt-112).'" dx="" dy="" style="font-size:10px; font-family:Arial;" >'.$GLOBALS['INFO_VIEWER_ZEILE_5'].'</text>';
		echo '<text x="15" y="'.$PosU=($YD_gesamt-100).'" dx="" dy="" style="font-size:10px; font-family:Arial;" >'.$GLOBALS['INFO_VIEWER_ZEILE_6'].'</text>';
		
		echo '<text x="15" y="'.$PosU=($YD_gesamt-80).'" dx="" dy="" style="font-size:12px; font-family:Arial;" >'.$Datengrundlage.'</text>';
		// Standard Datengrundlage (BKG ATKIS) anzeigen, wenn in DB hinterlegt (meistens der Fall)
		if($GLOBALS['STANDARD-DATENGRUNDLAGE'] == '1')
		{
			echo '<text x="15" y="'.$PosU=($YD_gesamt-68).'" dx="" dy="" style="font-size:10px; font-family:Arial;" >'.$Datengrundl_Inhalt_1.'</text>';
			
			$PosU_Abzug = 0;
		}
		else
		{
			$PosU_Abzug = 10;	
		}
		echo '<text x="15" y="'.$PosU=($YD_gesamt-(56+$PosU_Abzug)).'" dx="" dy="" style="font-size:10px; font-family:Arial;" >'.$GLOBALS['DATENGRUNDLAGE_ZEILE_1'].'</text>';
		// echo '<text x="15" y="'.$PosU=($YD_gesamt-(44+$PosU_Abzug)).'" dx="" dy="" style="font-size:10px; font-family:Arial;" >'.$GLOBALS['DATENGRUNDLAGE_ZEILE_2'].'</text>';
		echo '<text x="15" y="'.$PosU=($YD_gesamt-40).'" dx="" dy="" style="font-size:12px; font-family:Arial;" >Darstellungsgrundlage:</text>';
		echo '<text x="15" y="'.$PosU=($YD_gesamt-28).'" dx="" dy="" style="font-size:10px; font-family:Arial;" >'.$Datengrundl_Inhalt_2.'</text>';
		echo '<text x="15" y="'.$PosU=($YD_gesamt-16).'" dx="" dy="" style="font-size:10px; font-family:Arial;" >'.$Datengrundl_Inhalt_3.'</text>';
		echo '</g>';
		
		// temporäre Anzeige der Rechenzeit
		$Endzeit = date('i:s');
		
		if($_SESSION['Dokument']['ViewBerechtigung'] == "0")
		{
			echo '<g><text x="'.$PosX=($XD-180).'" y="'.$PosU=($YD_gesamt-270).'" dx="" dy="" style="font-size:9px; font-family:Arial;" >'.
				utf8_encode("Speicherverbrauch (Script):").' '.memory_get_usage().'</text>
			</g>';
			echo '<g><text x="'.$PosX=($XD-180).'" y="'.$PosU=($YD_gesamt-255).'" dx="" dy="" style="font-size:9px; font-family:Arial;" >'.
				utf8_encode("Rechenzeit für Karte:").' '.$Startzeit.' - '.$Endzeit.'</text>
			</g>';
			echo '<g><text x="'.$PosX=($XD-180).'" y="'.$PosU=($YD_gesamt-240).'" dx="" dy="" style="font-size:9px; font-family:Arial;" >'.
				utf8_encode("Zwischenzeit_Einzelwerte_1:").' '.$Zwischenzeit_Einzelwerte_1.'</text>
			</g>';
			echo '<g><text x="'.$PosX=($XD-180).'" y="'.$PosU=($YD_gesamt-225).'" dx="" dy="" style="font-size:9px; font-family:Arial;" >'.
				utf8_encode("Zwischenzeit_Einzelwerte_2:").' '.$Zwischenzeit_Einzelwerte_2.'</text>
			</g>';
			echo '<g><text x="'.$PosX=($XD-180).'" y="'.$PosU=($YD_gesamt-210).'" dx="" dy="" style="font-size:9px; font-family:Arial;" >'.
				utf8_encode("Zwischenzeit_SVG_Beginn_3:").' '.$Zwischenzeit_Einzelwerte_3.'</text>
			</g>';
		}
		
	
		
		
		// Rahmen zeichnen
		echo '<g><rect x="0" y="0" width="'.$XD.'px" height="'.$YD_gesamt.'px" fill="none" stroke="#FFFFFF" stroke-width="10" /></g>'; 
		echo '<g><rect x="0" y="0" width="'.$XD.'px" height="'.$YD_gesamt.'px" fill="none" stroke="#333333" stroke-width="1" /></g>';
		
		// Trick: nicht sichtbares Bild um URL für Speicherung der Label-Anzeige in $_SESSION-Array versteckt aufrufen zu können (href wird von JScript in jedem Element angepasst)
		echo '<g><image x="0" y="0" width="0" height="0" xlink:href="" id="verstecktegrafik" ></image></g>';
		
		
		
		
		
		
		// Aktualität
		// --------------
		
		// nur einblenden wenn $_SESSION['Dokument']['Fuellung']['MITTLERE_AKTUALITAET_IGNORE'] nicht gesetzt
		if(!$GLOBALS['MITTLERE_AKTUALITAET_IGNORE'])
		{
			echo '<text id="akttitel" 
			onmouseover="document.getElementById(\'akttitel\').setAttributeNS(null,\'text-decoration\',\'underline\');"
			onmouseout="document.getElementById(\'akttitel\').setAttributeNS(null,\'text-decoration\',\'none\');" 
			fill="#444444" x="'.$RPos_GA=($_SESSION['Dokument']['groesse_X']-21).'" y="'.$PosU=($_SESSION['Dokument']['groesse_Y']+92).'" dx="" dy="" style="font-size:13px; font-family:Arial;" >'
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
			echo '<g id="aktualitaet_legende" transform="matrix('.$s_akt.' 0 0 '.$s_akt.' '.$X_min_akt.' '.$Y_max_akt.')" style="fill-opacity:1;" >
			<desc>Min-Jahr='.$FCA_Jahr_min.'  Max-Jahr='.$FCA_Jahr_max.'</desc>'
			.$Aktualitaetslayer_Legende.'</g>';
			
			
			// -------- Aktualitätslegende unten --------
			$Akt_klasse_Y = $k_Y;
			$Pos_GAkt = ($_SESSION['Dokument']['groesse_X']-20);		
			for( $i_akt = 0 ; $i_akt <= $Akt_max ; $i_akt++ )
			{
				if($Akt_Differenz_in_karte_vorhanden[$i_akt] or $Akt_Differenz_in_karte_vorhanden[$i_akt]=="0") // Check auf wirkliche Verwendung in der Karte (Variable von Datenerfassung verfügbar)
				{
					echo'<rect x="'.$xl=($Pos_GAkt).'" y="'.$PosU=($_SESSION['Dokument']['groesse_Y']+100+$Akt_kl_Y).'" width="13px" height="8px" fill="#'.$FCA_Jahr[$i_akt].'" stroke="#555555" stroke-width="0" />'; 
					if($i_akt == 1) { $AktEinheit = "Jahr"; }else{ $AktEinheit = "Jahre"; }
					echo '<text x="'.$xl=($Pos_GAkt + 16).'" y="'.$ky = ($PosU=($_SESSION['Dokument']['groesse_Y']+108+$Akt_kl_Y)).'" style="font-size:9px; font-family:Arial;">'.$i_akt.' '.$AktEinheit.'</text>';
					$Akt_kl_Y = $Akt_kl_Y + 11;	
				}
			}
			
			
			// Teiltransparenter-Decker
			echo '<rect id="akt_decker" x="'.$RPos_GA=($_SESSION['Dokument']['groesse_X']-20).'" y="'.$PosU=($_SESSION['Dokument']['groesse_Y']+130).'" 
				width="220" height="90" fill="#ffffff" opacity="0.7" stroke="none" 
				display="none" onclick="aktualitaet_einblenden(\'aktualitaet_karte\'); aktualitaet_einblenden(\'grundaktlegende\');"></rect>';
			
			
			// Aktualität versteckt auf Kartenbereich zeichnen und Titel anpassen (Ein-/ Ausblenden Zeile hier darüber onklick)
			echo '<g id="aktualitaet_karte" display="none" pointer-events="none">
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
				echo utf8_encode('<text id="aktklick" fill="#444444" x="'.$RPos_GA=($_SESSION['Dokument']['groesse_X']+30).'" y="'.$PosU=($_SESSION['Dokument']['groesse_Y']+150)
																	.'" dx="" dy="" style="font-size:14px; font-family:Arial; font-weight:bold;" display="none" opacity="0.6" pointer-events="none" >
																	Ein- und ausblenden
																	<tspan dx="-145" dy="20">der Grundaktualität</tspan>
																	<tspan dx="-130" dy="20">im Kartenfenster:</tspan>
																	<tspan dx="-115" dy="20">(Hier klicken!)</tspan>
																	</text>'); 
			/*}
			 else
			{
				// Permanentanzeige 
				echo utf8_encode('<text id="aktklick_permanent" fill="#444444" x="'.$RPos_GA=($_SESSION['Dokument']['groesse_X']+25).'" y="'.$PosU=($_SESSION['Dokument']['groesse_Y']+150)
																		.'" dx="" dy="" style="font-size:14px; font-family:Arial; font-weight:bold;" opacity="0.6" pointer-events="none" >
																	Ein- und ausblenden
																	<tspan dx="-145" dy="20">der Grundaktualität</tspan>
																	<tspan dx="-130" dy="20">im Kartenfenster:</tspan>
																	<tspan dx="-115" dy="20">(Hier klicken!)</tspan>
																	</text>');
			} */

			
			// Mouse-Klick-Rechteck
			echo '<rect x="'.$RPos_GA=($_SESSION['Dokument']['groesse_X']+25).'" y="'.$PosU=($_SESSION['Dokument']['groesse_Y']+100).'" width="175" height="160" fill="#ffffff" opacity="0" stroke="none" 
			onclick="aktualitaet_einblenden(\'aktualitaet_karte\'); aktualitaet_einblenden(\'grundaktlegende\');"
			onmouseover="document.getElementById(\'aktklick\').setAttributeNS(null,\'display\',\'inline\'); document.getElementById(\'akt_decker\').setAttributeNS(null,\'display\',\'inline\');" 
			onmouseout="document.getElementById(\'aktklick\').setAttributeNS(null,\'display\',\'none\'); document.getElementById(\'akt_decker\').setAttributeNS(null,\'display\',\'none\');" 
			></rect>';
			
			// ---------------
		}
	} //Ende unlegend
	
	
	
	// Beschriftungen auf eigener Ebene erstellen
	foreach($_SESSION['Datenbestand'] as $DatenSet)
	{
		if($DatenSet['View']!='0' and $DatenSet['View']!='HG')
		{		
			// Ebene anlegen
			echo '<g id="Beschriftung_'.$DatenSet['NAME'].'">
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
				$XText=($Ausgabe_Beschriftung_X[$DatenSet['NAME']][$i_array]*$s)+$X_min;
				$YText=($Ausgabe_Beschriftung_Y[$DatenSet['NAME']][$i_array]*$s)+$Y_max;
				
	
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
					if(strlen($TeilStr[$i_teilstr]) > $max_teilstrlaenge)  $max_teilstrlaenge = strlen($TeilStr[$i_teilstr]);
					$i_teilstr++;
				}
				
				$Label_Verkleinerung = 0;
				if($max_teilstrlaenge > $Label_Längenbegrenzung)
				{
					$Label_Verkleinerung = 3;
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
				echo '<text id="Label_'
				.$Ausgabe_Beschriftung_AGS[$DatenSet['NAME']][$i_array]
				.'" x="'.$XText
				.'" y="'.$YText
				.'" style="fill:#'.$_SESSION['Dokument']['Textfarbe_Labels'].'; font-weight:bold; font-size:'.$Font_size_Labels.'px; font-family:Arial;" display="'.$LabelDisplay.'" pointer-events="none" >'
				.$Label_Text
				.'</text>'; 
				// in Style integrieren, falls Umrandung gewünscht: stroke:#DDDDDD; stroke-width:0.3px;
				$i_array++;
			}			
			echo "</g>"; 
		}		
	}
	
	// Objekt-Info
	//-------------------------------------------------
	// im unlegend-Betrieb nicht ausgeben
	if(!$_SESSION['Dokument']['unlegend'])
	{
	
		// Info zu Kartenelementen bei Mausinteraktionen (wird über Karte gelegt)
		echo '<g id="ObjInfofeld" pointer-events="none" opacity="0">';
			echo '<rect x="'.$xl=($XD-380).'" y="'.$LRahmen_oben=($ObjInfofeld_Y+5).'" width="178px" height="85px" fill="#FFFFFF" stroke="#555555" stroke-width="1" opacity="0.7" />'; 
			
			$ObjInfofeld_Y_ff=($ObjInfofeld_Y+5);
			
			echo '<text id="ObjInfofeld_Name_z1" x="'.$RPos=($_SESSION['Dokument']['groesse_X']-160).'" y="'.$ObjInfofeld_Y_ff=($ObjInfofeld_Y_ff+15)
			.'" style="font-size:11px; font-family:Arial; font-weight:bold;"> </text>';
				
			echo '<text id="ObjInfofeld_Name_z2" x="'.$RPos=($_SESSION['Dokument']['groesse_X']-160).'" y="'.$ObjInfofeld_Y_ff=($ObjInfofeld_Y_ff+12)
			.'" style="font-size:11px; font-family:Arial; font-weight:bold;"> </text>';
			
			echo '<text id="ObjInfofeld_Wert" x="'.$RPos=($_SESSION['Dokument']['groesse_X']-160).'" y="'.$ObjInfofeld_Y_ff=($ObjInfofeld_Y_ff+18)
			.'" style="font-size:11px; font-family:Arial; font-weight:bold;"> </text>';
			
			// nur einblenden wenn $_SESSION['Dokument']['Fuellung']['MITTLERE_AKTUALITAET_IGNORE'] nicht gesetzt
			if(!$GLOBALS['MITTLERE_AKTUALITAET_IGNORE'])
			{
				echo '<text id="ObjInfofeld_Aktualitaet_Titel" x="'.$RPos=($_SESSION['Dokument']['groesse_X']-160).'" y="'.$ObjInfofeld_Y_ff=($ObjInfofeld_Y_ff+15)
				.'" style="font-size:10px; font-family:Arial; font-weight:bold;">'.utf8_encode("Mittlere Grundaktualität:").'</text>';
				
				echo '<text id="ObjInfofeld_Aktualitaet" x="'.$RPos=($_SESSION['Dokument']['groesse_X']-40).'" y="'.$ObjInfofeld_Y_ff=($ObjInfofeld_Y_ff+0).'" style="font-size:10px; font-family:Arial; font-weight:bold;">'
				.$ObjInfofeld_Grundakt.'</text>';
			}
			
			echo '<text id="ObjInfofeld_Hinweis" x="'.$RPos=($_SESSION['Dokument']['groesse_X']-160).'" y="'.$ObjInfofeld_Y_ff=($ObjInfofeld_Y_ff+15)
			.'" style="font-size:10px; font-family:Arial; font-weight:bold;"> </text>';
			
		echo '</g>';
	 
	 
	 
		// MouseOver Anzeige des Elements !!!!!!!!!!!!!!! wesentlich schneller als Objekt selbst umzudefinieren und immer ganz oben angezeigt !!!!!!!!!!!!!!!!!
		/// ???????????????????????????????? Wo kommt die Füllung her ????????????????????????
		echo '<g transform="matrix('.$s.' 0 0 '.$s.' '.$X_min.' '.$Y_max.')" style="fill-opacity:0;"><use id="marker_geom" xlink:href="" stroke="#DD4444" stroke-width="'
			  .$strwidth = ($_SESSION['Dokument']['Strichstaerke_Event']*2).'" pointer-events="none" ></use></g>';
	}
}

	
	
	
// Ende: keine Anzeige bei fehlenden Auswahl-Daten

// ---------------------------------------------------------
// Fehlerinformation für AGS-Jahr Konflikt
if($_SESSION['Dokument']['AGS_Fehler'] == 1)
{
	echo '<g>';
	echo '<text x="'.$xleer=($XD/2-300).'" y="250" dx="" dy="" style="font-size:28px; font-family:Arial; font-weight:bold;" fill="#000000" >'.utf8_encode("Achtung!").'</text>';
	echo '<text x="'.$xleer=($XD/2-300).'" y="300" dx="" dy="" style="font-size:20px; font-family:Arial; font-weight:bold;" fill="#000000" >'.utf8_encode("Folgende Verwaltungseinheiten existieren").'</text>';
	echo '<text x="'.$xleer=($XD/2-300).'" y="320" dx="" dy="" style="font-size:20px; font-family:Arial; font-weight:bold;" fill="#000000" >'.utf8_encode("im ausgewählten Jahr nicht:").'</text>';
	echo '<text x="'.$xleer=($XD/2-300).'" y="350" dx="" dy="" style="font-size:20px; font-family:Arial; font-weight:bold;" fill="#000000" >'.$_SESSION['Dokument']['AGS_Fehler_Elemente'].'</text>';
	echo '<text x="'.$xleer=($XD/2-300).'" y="380" dx="" dy="" style="font-size:20px; font-family:Arial; font-weight:bold;" fill="#000000" >'.utf8_encode("Bitte korrigieren Sie Ihre Auswahl!").'</text>';
	echo '</g>';
}
// ---------------------------------------------------------

// Array $_SESSION nach gebrauch der API leeren
if($GLOBALS['API'] == 1)
{
	$_SESSION = unserialize($GLOBALS['SESSION_SPEICHER']);
	//echo "<g><desc>".$GLOBALS['SESSION_SPEICHER']."</desc></g>";
}

/* 
// Die für Druck die größere Anzeige wieder zurückstellen
if($_GET['druck'])
{
	$_SESSION['Dokument']['groesse_X'] = $_SESSION['Dokument']['groesse_X'] - 220;
	$_SESSION['Dokument']['groesse_Y'] = $_SESSION['Dokument']['groesse_Y'] - 200;
} */

// Ende der SVG-Datei speichern
echo '</svg>';?>