<?php 
session_start();
include("includes_classes/verbindung_mysqli.php");


if($_POST['Typ'])
{
	$_SESSION['Dokument']['Fuellung']['Typ']=$_POST['Typ'];	
}

if($_POST['untertyp'])
{
	$_SESSION['Dokument']['Fuellung']['Untertyp'] = $_POST['untertyp'];
}

// Untertyp für Man Klasse festsetzen (simpel oder wirklich manuell (=leer))
if($_POST['Typ'] == "manuell Klassifizierte Farbreihe")
{
	$_SESSION['Dokument']['Fuellung']['ManUntertyp'] = $_POST['ManUntertyp'];
}


// Standardzeichenvorschrift für Indikator wieder aktivieren ... in svg_html.php überführt... da sonst teilweise fehlerhaft
/* 
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
 */

// Manuelle Klasse aus automatischer Kl. neu erstellen (einerseits beim ersten öffnen der Optionen Simpel (???) oder manuell und andererseits auf Userwunsch
if($_POST['ManUntertyp'] == "simpel" 
or ($_GET['kopieren'] and ((!$_SESSION['Temp']['manuelle_Klasse'] or $_SESSION['Temp']['manuelle_Klasse'] = "leer") and $_SESSION['Dokument']['Fuellung']['Typ'] == "manuell Klassifizierte Farbreihe"))) 
{
	unset($_SESSION['Temp']['manuelle_Klasse']);
	$_SESSION['Temp']['manuelle_Klasse'] = $_SESSION['Temp']['Klasse'];
}


// Manuelle Klasse löschen
if($_GET['loesch']) unset($_SESSION['Temp']['manuelle_Klasse'][$_GET['loesch']]);

// Manuelle Klassen anpassen
if($_POST['Aktion'] == "manKlass")
{
	$_SESSION['Dokument']['Fuellung']['ManUntertyp'] = "";
	$k=0;
	for($k = 0; $k < sizeof($_POST['Obergrenze']); $k++)
	{
		if($_POST['Obergrenze'][$k])
		{
			
			$_SESSION['Temp']['manuelle_Klasse'][$k]['Wert_Obergrenze'] = round(strtr($_POST['Obergrenze'][$k],',','.'),$_SESSION['Dokument']['Fuellung']['Rundung'])+1000000000;
			$_SESSION['Temp']['manuelle_Klasse'][$k]['Obergrenze'] = (($_SESSION['Temp']['manuelle_Klasse'][$k]['Wert_Obergrenze'] - $_SESSION['Dokument']['Fuellung']['Indikator_Wert_min']) * 100) 
																																					/ $_SESSION['Dokument']['Fuellung']['Wertebereich'];
			// nur ändern, wenn nächste Klasse vorhanden
			if($_SESSION['Temp']['manuelle_Klasse'][$k+1]['Wert_Untergrenze'] or $_SESSION['Temp']['manuelle_Klasse'][$k+1]['Wert_Obergrenze'] or $_POST['Obergrenze'][$k+1]) 
			{
				$_SESSION['Temp']['manuelle_Klasse'][$k+1]['Wert_Untergrenze'] = round(strtr($_POST['Obergrenze'][$k],',','.'),$_SESSION['Dokument']['Fuellung']['Rundung'])+1000000000;
				$_SESSION['Temp']['manuelle_Klasse'][$k+1]['Untergrenze'] = (($_SESSION['Temp']['manuelle_Klasse'][$k+1]['Wert_Untergrenze'] - $_SESSION['Dokument']['Fuellung']['Indikator_Wert_min']) * 100) 
																																					/ $_SESSION['Dokument']['Fuellung']['Wertebereich'];
			}
		}
		if($_POST['Farbwert'][$k])
		{
			$_SESSION['Temp']['manuelle_Klasse'][$k]['Farbwert']=$_POST['Farbwert'][$k];
		}
	}
	// Korrektur der Unter-/Obergrenzen aus den Doc-Grenzen heraus Dokumentes
	include('svg_klassenbildung.php'); 
}


// Auflösung setzen
// if($_GET['Klassen_Aufloesung']) {	$_SESSION['Dokument']['Klassen']['Aufloesung'] = $_GET['Klassen_Aufloesung']; }







// autom. häufigkeitsverteilte Klassen neu berechnen (ausgelagert)
if($_SESSION['Dokument']['Fuellung']['Typ'] == "Klassifizierte Farbreihe") 
{
	
	// Klassenzahl ändern
	if($_POST['klass_mw'])
	{
		$KlassenAnz_alt = $_SESSION['Temp']['KlassenAnz'];
		
		// Erhöhung
		if($_POST['klass_mw'] == "mehr")
		{
			// Bei Einstieg hier schon setzen, da Schleife sonst nicht aktiviert wird
			$_SESSION['Dokument']['Klassen']['Aufloesung']++;
			// mit Sicherung gegen Endlosschleife
			/* while(!$SchleifenFortsetzung or ($KlassenAnz_alt >= $_SESSION['Temp']['KlassenAnz'] and $_SESSION['Dokument']['Klassen']['Aufloesung'] > 2 and $_SESSION['Dokument']['Klassen']['Aufloesung'] < 19))
			{
				if($SchleifenFortsetzung)
				{
					$_SESSION['Dokument']['Klassen']['Aufloesung']++;
				}
				$SchleifenFortsetzung = 1; */
				// max. min. Grenzen für Klassenauflösung/Zahl setzen
				if($_SESSION['Dokument']['Klassen']['Aufloesung'] < 2) 	{	$_SESSION['Dokument']['Klassen']['Aufloesung'] = 2; 	}
				if($_SESSION['Dokument']['Klassen']['Aufloesung'] > 19) {  	$_SESSION['Dokument']['Klassen']['Aufloesung'] = 19; 	}
				// Klassen erzeugen
				include('svg_klassenbildung.php'); 
			/* } */
		}
		// Verringerung
		if($_POST['klass_mw'] == "weniger")
		{
			// Bei Einstieg hier schon setzen, da Schleife sonst nicht aktiviert wird
			$_SESSION['Dokument']['Klassen']['Aufloesung']--;
			// mit Sicherung gegen Endlosschleife
			/* while(!$SchleifenFortsetzung or ($KlassenAnz_alt <= $_SESSION['Temp']['KlassenAnz'] and $_SESSION['Dokument']['Klassen']['Aufloesung'] > 2 and $_SESSION['Dokument']['Klassen']['Aufloesung'] < 19))
			{	
				if($SchleifenFortsetzung)
				{
				   	$_SESSION['Dokument']['Klassen']['Aufloesung']--;
				}
				$SchleifenFortsetzung = 1; */
				// max. min. Grenzen für Klassenauflösung/Zahl setzen
				if($_SESSION['Dokument']['Klassen']['Aufloesung'] < 2) 	{	$_SESSION['Dokument']['Klassen']['Aufloesung'] = 2; 	}
				if($_SESSION['Dokument']['Klassen']['Aufloesung'] > 19) {  	$_SESSION['Dokument']['Klassen']['Aufloesung'] = 19; 	}
				// Klassen erzeugen
				include('svg_klassenbildung.php'); 
			/* } */
		}
	}	
	else
	{
		// max. min. Grenzen für Klassenauflösung/Zahl setzen
		if($_SESSION['Dokument']['Klassen']['Aufloesung'] < 2) 	{	$_SESSION['Dokument']['Klassen']['Aufloesung'] = 2; 	}
		if($_SESSION['Dokument']['Klassen']['Aufloesung'] > 19) {  	$_SESSION['Dokument']['Klassen']['Aufloesung'] = 19; 	}
		
		// Standardmäßig immer erstmal durchlaufen (damit auch alles aktuell ist)
		include('svg_klassenbildung.php');
	}
}



?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Unbenanntes Dokument</title>
<link href="screen_viewer.css" rel="stylesheet" type="text/css" />
</head>

<body style="padding-left:40px;">
<br />
</div>
<br />
<br />
<div style="border: #999999 solid 1px; padding:10px; margin-right:10px;">
  <table style="width:810px; border:0px; border-collapse:collapse;">
            
                <tr>
                  <td colspan="2" valign="top" class="grauer_hintergrund" ><strong>Darstellungsmöglichkeiten für die Kennzahl- / Indikatorwerte:
                  </strong></td>
          </tr>
                <tr>
                  <td valign="top" class="transp_hintergrund" style="width:200px;">&nbsp;</td>
                  <td valign="top" class="transp_hintergrund" >&nbsp;</td>
                </tr>
                
                
                
                
                
                <tr>
                  <td valign="top" class="transp_hintergrund">Art der Darstellung</td>
                  <td rowspan="2" valign="top" class="transp_hintergrund" >
                  
                  
                  <?php 
				  if($_SESSION['Dokument']['indikator_lokal'] == '1')
				  {				  
					  ?>
					   <form action="svg_zeichenvorschrift_klass.php" method="post">
					   <?php if($_SESSION['Dokument']['Fuellung']['Typ'] == "Klassifizierte Farbreihe")
					   {
							?><img src="gfx/punkt_aktiv.png" alt="aktiv" style="margin-top:2px;" /><?php
					   }
					   else
					   {
							?><img src="gfx/punkt_inaktiv.png" alt="inaktiv" style="margin-top:2px;" /><?php
					   }
						?>
						<input name="input" type="submit" value="Farbreihe (automatisch)" class="button_blau_abschicken" style="margin-left:3px;" />
						
						<input name="Typ" type="hidden" value="Klassifizierte Farbreihe" />
					  </form>
                  <?php 
				  }
                  else
                  {
                  	?><img src="gfx/punkt_inaktiv.png" alt="inaktiv" style="margin-top:2px;" /> Automatische Klassifizierung nur bei regionaler <a href="svg_zeichenvorschrift_lok_glob.php">Normierung</a> wählbar.
                  <?php
                  }
	
	// ------------Button Einstellungen beibehalten------------			  
				  // Nur Anzeigen, wenn Klassifizierung wirklich vorhanden und nicht nur Verlauf gewählt ist
				  if($_SESSION['Dokument']['Fuellung']['Typ'] == "Klassifizierte Farbreihe" 
					or ($_SESSION['Dokument']['Fuellung']['Typ'] == "manuell Klassifizierte Farbreihe" and $_SESSION['Dokument']['Fuellung']['ManUntertyp'] == "simpel"))
				  {
					  ?>
					   
					   <form action="svg_zeichenvorschrift_klass.php" method="post">
						   <?php if($_SESSION['Dokument']['Fuellung']['Typ'] == "manuell Klassifizierte Farbreihe" and $_SESSION['Dokument']['Fuellung']['ManUntertyp'] == "simpel")
						   {
								?><img src="gfx/punkt_aktiv.png" alt="aktiv" style="margin-top:2px;" /><?php
						   }
						   else
						   {
								?><img src="gfx/punkt_inaktiv.png" alt="inaktiv" style="margin-top:2px;" /><?php
						   }
						   ?>
							<input name="input" type="submit" value="Einstellung beibehalten" class="button_blau_abschicken" style="margin-left:3px;" />
							<input name="ManUntertyp" type="hidden" value="simpel" />
							<input name="Typ" type="hidden" value="manuell Klassifizierte Farbreihe" />
					   (sinnvoll für Zeitschnittvergleiche & manuelle Farbreihe)
					   </form> 
					   <?php 
				   }
				   ?>				   
 <!------Ende Button Einstellungen beibehalten---------------------->		   
				   
 <!------Beginn Button Farbreihe (manuell)---------------------->		
                   <form action="svg_zeichenvorschrift_klass.php?kopieren=1" method="post">
					   <?php if($_SESSION['Dokument']['Fuellung']['Typ'] == "manuell Klassifizierte Farbreihe" and !$_SESSION['Dokument']['Fuellung']['ManUntertyp'])
                       {
                            ?><img src="gfx/punkt_aktiv.png" alt="aktiv" style="margin-top:2px;" /><?php
                       }
                       else
                       {
                            ?><img src="gfx/punkt_inaktiv.png" alt="inaktiv" style="margin-top:2px;" /><?php
                       }
                       ?>
                        <input name="input" type="submit" value="Farbreihe (manuell)" class="button_blau_abschicken" style="margin-left:3px;" />
                        <input name="Typ" type="hidden" value="manuell Klassifizierte Farbreihe" />
                      (Expertenfunktion)
                   </form>
                   
       <!------Ende Button Farbreihe manuell---------------------->		            
                   
    <!----Button Farbverlauf---->                   
                   <form act ion="svg_zeichenvorschrift_klass.php" method="post">
                  <?php if($_SESSION['Dokument']['Fuellung']['Typ'] == "Farbbereich")
				   {
				   		 ?><img src="gfx/punkt_aktiv.png" alt="aktiv" style="margin-top:2px;" /><?php
				   }
				   else
				   {
				   		?><img src="gfx/punkt_inaktiv.png" alt="inaktiv" style="margin-top:2px;" /><?php 
				   		/*diese Zeile auskommentieren  */
				   }
				   ?>
                 	<input name="input" type="submit" value="Farbverlauf" class="button_blau_abschicken" style="margin-left:3px;" />   <!--diese Zeile auskommentieren-->
                    
                    <input name="Typ" type="hidden" value="Farbbereich" />
                  </form>     
                   <br />
                   
 <!----Ende Button Farbverlauf---->
                   
                   
 <!---- Button Standard wiederherstellen---->   
                   
                   <form id="form1" name="form1" method="post" action="svg_html.php">
                  <input name="input" type="submit" value="Standard wiederherstellen" class="button_blau_abschicken" style="margin-left:3px;background:#9FA8CC;" />
                        <input name="standard" type="hidden" value="standard" />
                   </form>
                   <br />
                   
    <!----Ende Button Standard wiederherstellen---->                       
                  </td>
               </tr>
               
       
  <!-------------------------Ende Buttonbereich-------------------------------------------------->             
               
               <!--Zurück button--->        
               
                <tr>
                  <td valign="bottom" class="transp_hintergrund"><div style="float:left; margin-right:10px; padding-top:15px;"> <a href="svg_html.php#top" target="_self"><img src="icons_viewer/back.png" alt="Zurück" /><br />
                  zur&uuml;ck</a><a name="histogramm_ak" id="histogramm_ak"></a> </div></td>
                </tr>
                <!--Ende Zurück button--->
                
 <!------------------------	Beginn Textbereich für Verlauf (Farbbereich)------------------------------------->

       <?php if($_SESSION['Dokument']['Fuellung']['Typ'] == "Farbbereich")
		{
			?>
                <tr>
                  <td colspan="2" valign="bottom" class="transp_hintergrund">&nbsp;</td>
                </tr>
                
                <tr>
                  <td valign="bottom" class="transp_hintergrund">
                  
                  <br />
                  <br /></td>
                  <td valign="top" class="transp_hintergrund" >
                    <strong>Kontinuierlicher Farbverlauf von Min. - Max.</strong>                    <br />
                    <br />
                    Benutzen Sie einen kontinuierlichen Farbverlauf, wenn die Werteverteilung des gewählten Indikators gleichmäßig ist oder die Farbintensität direkt an die Werteunterschiede angelehnt sein soll.<br />
                    <br />
                  <br />
                  
                  
                  
                  Histogramm:<br />
                  <br />

                  
                  
                  <?php 
					
					// Farbraum erfassen
					// --------------------------
					
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
					
					
					
					$Tabellenhoehe = "100";
					
					
					?>
                    
                  <table style="width:400px; border-spacing:0px; border:1px solid #666666; padding:2px; border-collapse:collapse;">
                    	<tr style="height:<?php echo $Tabellenhoehe; ?>px;">
							<?php 
							
							
	
							
							
							
							
                            // Anzeige der Verteilung an sich (Säulen)
                              for($i=0 ; $i <= 100 ; $i++)
                              {
                                  // wird auf Teiler 30 gerechnet (hier Erhöhung optisch besser)
                                  $h = ($_SESSION['Temp']['i_Verteilung'][$i]/$_SESSION['Temp']['i_Verteilung']['Max'])*$Tabellenhoehe;                               
                                  // $h_fuell = 120-$h; // Durch Erhöhung auf y+e, statt y, bezogen
                                                            
                                  ?>
                                    <td title="<?php echo round($_SESSION['Temp']['i_Verteilung'][$i],0); ?> Gebietseinheiten" style="padding:0px; vertical-align:bottom; width:2px; background:#<?php 
                                       		// Runden, in Hex-Wert umwandeln und bei einstelligen Werten eine 0 voranstellen und keine Negativen Werte zulassen <= nicht möglich!!!
											$ip = 100 - $i;
											if(strlen($R_hex = dechex(round(abs($Ri=$R_max_dezimal-(($ip)*$R)),0))) < 2) $R_hex = '0'.$R_hex;
											if(strlen($G_hex = dechex(round(abs($Gi=$G_max_dezimal-(($ip)*$G)),0))) < 2) $G_hex = '0'.$G_hex;
											if(strlen($B_hex = dechex(round(abs($Bi=$B_max_dezimal-(($ip)*$B)),0))) < 2) $B_hex = '0'.$B_hex;
											echo $R_hex.$G_hex.$B_hex;
                                        ?>">
                                        
                                        <div style=" line-height:0px; font-size:0px; background:#000000; border:0px; margin-left:1px; padding:0px; width:2px; height:<?php echo $h; ?>px;<?php 
										if(!$h) { echo " visibility:hidden; "; }
										?>"></div>
                                    </td>
                                  <?php 
                              }
                              ?>
                       <tr>
                       <tr style="height:1px; background:#FFF;"></tr>
                       <tr style="height:10px;">
							<?php 
                            // Anzeige der Verteilung an sich (Säulen)
                              for($i=0 ; $i <= 100 ; $i++)
                              {
                                    // wird auf Teiler 30 gerechnet (hier Erhöhung optisch besser)
                                  $h = ($_SESSION['Temp']['i_Verteilung'][$i]/$_SESSION['Temp']['i_Verteilung']['Max'])*$Tabellenhoehe; 
                                  
                                  // $h_fuell = 120-$h; // Durch Erhöhung auf y+e, statt y, bezogen
                                                            
                                  ?>
                                    <td style="padding:0px; vertical-align:bottom; width:2px; background:#<?php 
                                        // Runden, in Hex-Wert umwandeln und bei einstelligen Werten eine 0 voranstellen und keine Negativen Werte zulassen <= nicht möglich!!!
											$ip = 100 - $i;
											if(strlen($R_hex = dechex(round(abs($Ri=$R_max_dezimal-(($ip)*$R)),0))) < 2) $R_hex = '0'.$R_hex;
											if(strlen($G_hex = dechex(round(abs($Gi=$G_max_dezimal-(($ip)*$G)),0))) < 2) $G_hex = '0'.$G_hex;
											if(strlen($B_hex = dechex(round(abs($Bi=$B_max_dezimal-(($ip)*$B)),0))) < 2) $B_hex = '0'.$B_hex;
											echo $R_hex.$G_hex.$B_hex;
                                        ?>">
                                        
                                    </td>
                                  <?php 
                              }
                              ?>
                       <tr>
                       </tr>
                       	 <td colspan="101">
                           <div style="position: relative; clear:both;">
                                  <div style=" position:absolute; top:0px; left:0px;"><?php 
                                                            echo number_format($_SESSION['Dokument']['Fuellung']['Indikator_Wert_min']-1000000000,$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.')
                                                                    ." ".$_SESSION['Dokument']['Fuellung']['Indikator_Einheit']; ?></div>
                                  <div style=" position:absolute; top:0px; right:0px;"><?php 
                                                            echo number_format($_SESSION['Dokument']['Fuellung']['Indikator_Wert_max']-1000000000,$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.')
                                                                    ." ".$_SESSION['Dokument']['Fuellung']['Indikator_Einheit']; ?></div> 
                            </div> 
                         </td>
                       </tr>
                    </table>
                  	<br />
                  	<br />
				<!--	<span class="button_standard_abschicken_a" style="background:#9FA8CC;"><a href="svg_zeichenvorschrift_farbe.php" target="_self">&nbsp;&nbsp;Farben anpassen&nbsp;&nbsp;&nbsp;</a></span>-->
                  
                  
                  
                  
                  
                  
                  
                  
                  </td>
              </tr>
            <?php 
		}
//------------------------	Ende Textbereich für Verlauf (Farbbereich)-------------------------------------
		
		
		
//------------------------	Beginn Textbereich für Automatische Klassi -------------------------------------	

	
		
		if($_SESSION['Dokument']['Fuellung']['Typ'] == "Klassifizierte Farbreihe")
		{
			?>
			  
              <tr>
                <td height="31" valign="top" class="transp_hintergrund">&nbsp;</td>
                <td valign="top" class="transp_hintergrund"><strong>Automatische Klassifizierung<br />
                  <br />
                </strong></td>
              </tr>
           
              <tr>
                <td height="69" valign="top" class="transp_hintergrund">Typ</td>
                <td valign="top" class="transp_hintergrund">
                  <div style="padding-left:2px;">
                  	<form action="#histogramm_ak" method="post">
                        <input name="untertyp" type="radio" value="haeufigkeit" onclick="submit();" <?php 
                                            if(!$_SESSION['Dokument']['Fuellung']['Untertyp'] or $_SESSION['Dokument']['Fuellung']['Untertyp'] == "haeufigkeit") echo "checked"; ?> /> 
                        Gleiche Klassenbesetzung (Quantile)<br />
                        <input name="untertyp" type="radio" value="gleich" onclick="submit();" <?php 
                                            if($_SESSION['Dokument']['Fuellung']['Untertyp'] == "gleich") echo "checked"; ?> /> 
                        Gleiche Klassenbreite
					</form>
                  </div>
                  <?php 
				  	// Testausgabe:
				  	/* echo "<br />KlasseAuflösg:".$_SESSION['Dokument']['Klassen']['Aufloesung'];
					echo "<br />Klassenanz_neu:".$_SESSION['Temp']['KlassenAnz']; */
					?>
                </td>
    		</tr>
             

              <tr>
                <td rowspan="2" valign="top" class="transp_hintergrund">                Anzahl der Klassen<span class="Text_10px"></span><br /></td>
                <td valign="top" class="transp_hintergrund"> 
                  
			    </td>
              </tr>
              <tr>
                <td valign="top" class="transp_hintergrund">
               	  <table width="100%" style="border:0px; margin-bottom:5px;">
                      <tr>
                        <td width="6%" valign="top">
                        
                        
                        	 <form id="mw" name="mw" method="post" action="#histogramm_ak">
               	      			<input type="submit" name="mehr" id="mehr" value="+"  style="width:20px;"  class="button_blau_abschicken"/>
                             	<input name="klass_mw" type="hidden" value="mehr" />     
                             </form> 
                             <form id="mw1" name="mw1" method="post" action="#histogramm_ak">
                                <input type="submit" name="weniger" id="weniger" value="-"  style="width:20px;" class="button_blau_abschicken"/>
                                <input name="klass_mw" type="hidden" value="weniger" />
                             </form> 
                             
                                                     
                        </td>
                        <td width="94%" valign="top">
                        	<!--<div class="reglerRahmen" style=" height:72px;">
                                    <?php 
                                       /*  for($i=20 ; $i>=3 ; $i--)
                                        {
                                            ?>
                                            <div <?php if($i == $_SESSION['Dokument']['Klassen']['Aufloesung']) { echo 'class="reglerTeil_aktiv"'; }else{ echo 'class="reglerTeil"'; } ?> style="height:4px;">
                                                <a href="svg_zeichenvorschrift_klass.php?Klassen_Aufloesung=<?php echo $i; ?>#histogramm_ak" style="display:block;">
                                                  	<div style="margin-left:3px; margin-right:3px; background:#666666;">&nbsp;</div>
                                                </a>                                            </div>
                                            <?php 
                                        } */
                                     
									 
									 
									 ?>
                          		</div>    -->                       
                          		
                                
                              <div style="clear:both;">
                              <?php 
                              foreach($_SESSION['Temp']['Klasse'] as $Klassenset)
                              {
                                    ?><div title="bis <?php echo $OG_Wert = ($Klassenset['Wert_Obergrenze']-1000000000).$_SESSION['Dokument']['Indikator_Einheit']; 
                                                ?>" style="float:left; width:20px; height:20px; margin:3px; background-color:#<?php echo $Klassenset['Farbwert']; ?>">
                                      </div>
                                    <?php 
                                    $KZahl++;
                              }
                              ?>
                              </div> 
                              <div  style="clear:both;">
                            <strong><?php echo $KZahl; ?> Klassen</strong> <?php 
							if(!$_SESSION['Dokument']['Fuellung']['Untertyp'] or $_SESSION['Dokument']['Fuellung']['Untertyp'] == "haeufigkeit")
							{
								// Ausgabe nur bei Häufigkeitsverteilter Klassifizierung
								?>
                             aus Daten ableitbar (angestrebt waren <?php echo $_SESSION['Dokument']['Klassen']['Aufloesung']; ?> Klassen)<?php 
							}
							?>                         
                             </div>
                        
                        </td>
                      </tr>
                  </table></td>
          </tr>    
               <tr>
                <td valign="top" class="transp_hintergrund"><br />
                  Werteverteilung und<br />
                  Vorschau der autom.<br />
                  Klassifizierung<br />
                  (nur zur Information)<br />
                 <br />                                </td>
                <td valign="top" class="transp_hintergrund"> 
                  	
                    <br />
					H&auml;ufigkeits- und Klassenverteilung:
                  <br />
                    <br />
					<!--<span style="font-size:12px;">Maximum der Verteilung = <?php echo $_SESSION['Temp']['i_Verteilung']['Max']; ?> Gebietseinheiten</span> -->
                    
                    
                    
                                  
                    
                    
                    <?php 
					$Tabellenhoehe = "100";
					?>
                    
                    <table style="width:400px; border-spacing:0px; border:1px solid #666666; padding:2px; border-collapse:collapse;">
                    	<tr style="height:<?php echo $Tabellenhoehe; ?>px;">
							<?php 
                            // Anzeige der Verteilung an sich (Säulen)
                              for($i=0 ; $i <= 100 ; $i++)
                              {
                                  // wird auf Teiler 30 gerechnet (hier Erhöhung optisch besser)
                                  $h = ($_SESSION['Temp']['i_Verteilung'][$i]/$_SESSION['Temp']['i_Verteilung']['Max'])*$Tabellenhoehe;                               
                                  // $h_fuell = 120-$h; // Durch Erhöhung auf y+e, statt y, bezogen
                                                            
                                  ?>
                                    <td title="<?php echo round($_SESSION['Temp']['i_Verteilung'][$i],0); ?> Gebietseinheiten" style="padding:0px; vertical-align:bottom; width:2px; background:#<?php 
                                        if(is_array($_SESSION['Temp']['Klasse']))
                                        {
                                            $DurchlaufCheck_a = 0; 
											foreach($_SESSION['Temp']['Klasse'] as $Klassensets)
                                            {
                                                if((!$DurchlaufCheck_a and $Klassensets['Untergrenze']==0 and $i==0) or ($Klassensets['Untergrenze'] < $i and $Klassensets['Obergrenze'] >= $i))
                                                {
                                                    echo $Klassensets['Farbwert'];
													// für Doppelte Klassengrenzen bei $Klassensets['Untergrenze']==0
													$DurchlaufCheck_a = 1; 
                                                }
                                            }
                                        }
                                        ?>">
                                        
                                        <div style=" line-height:0px; font-size:0px; background:#000000; border:0px; margin-left:1px; padding:0px; width:2px; height:<?php echo $h; ?>px;<?php 
										if(!$h) { echo " visibility:hidden; "; }
										?>"></div>
                                    </td>
                                  <?php 
                              }
                              ?>
                       <tr>
                       <tr style="height:1px; background:#FFF;"></tr>
                       <tr style="height:10px;">
							<?php 
                            // Anzeige der Verteilung an sich (Säulen)
                              for($i=0 ; $i <= 100 ; $i++)
                              {
                                  // wird auf Teiler 30 gerechnet (hier Erhöhung optisch besser)
                                  //$h = ($_SESSION['Temp']['i_Verteilung'][$i]/$_SESSION['Temp']['i_Verteilung']['Max'])*$Tabellenhoehe; 
                                  
                                  // $h_fuell = 120-$h; // Durch Erhöhung auf y+e, statt y, bezogen
                                                            
                                  ?>
                                    <td korks="<?php echo $i; ?>" style="padding:0px; vertical-align:bottom; width:2px; background:#<?php 
                                        if(is_array($_SESSION['Temp']['Klasse']))
                                        {
                                            $DurchlaufCheck = 0; 
											foreach($_SESSION['Temp']['Klasse'] as $Klassensets)
                                            {
                                                if((!$DurchlaufCheck and $Klassensets['Untergrenze']==0 and $i==0) or ($Klassensets['Untergrenze'] < $i and $Klassensets['Obergrenze'] >= $i))
                                                {
                                                    echo $Klassensets['Farbwert'];
													// für Doppelte Klassengrenzen bei $Klassensets['Untergrenze']==0
													$DurchlaufCheck = 1; 
                                                }
                                            }
                                        }
                                        ?>">
                                        
                                    </td>
                                  <?php 
                              }
                              ?>
                       <tr>
                       </tr>
                       	 <td colspan="101">
                           <div style="position: relative; clear:both;">
                                  <div style=" position:absolute; top:0px; left:0px;"><?php 
                                                            echo number_format($_SESSION['Dokument']['Fuellung']['Indikator_Wert_min']-1000000000,$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.')
                                                                    ." ".$_SESSION['Dokument']['Fuellung']['Indikator_Einheit']; ?></div>
                                  <div style=" position:absolute; top:0px; right:0px;"><?php 
                                                            echo number_format($_SESSION['Dokument']['Fuellung']['Indikator_Wert_max']-1000000000,$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.')
                                                                    ." ".$_SESSION['Dokument']['Fuellung']['Indikator_Einheit']; ?></div> 
                            </div> 
                         </td>
                       </tr>
                    </table>

                    
                    <br />
                    <br />
         <!--           <span class="button_standard_abschicken_a" style="background:#9FA8CC;"><a href="svg_zeichenvorschrift_farbe.php" target="_self">&nbsp;&nbsp;Farben anpassen&nbsp;&nbsp;&nbsp;</a></span>-->
                    <br />
                 </td>
              </tr> 
           	<?php 
		}
//------------------------	Ende Textbereich für Automatische Klassi -------------------------------------			
		
		
//------------------------	Beginn Textbereich für Einstellungen Beibehalten, wenn Farbreihe (automatisch) gewählt -------------------------------------			
					
		// Für Man. Klass aber in Abgespeckter Form, nur zum festsetzen für Vergleiche
		// ------------------------------------------------------------------------------
		
		
		
		if($_SESSION['Dokument']['Fuellung']['Typ'] == "manuell Klassifizierte Farbreihe" and $_SESSION['Dokument']['Fuellung']['ManUntertyp'] == "simpel")
		{
			?>        
          <tr>
            <td height="69" class="transp_hintergrund">&nbsp;</td>
              <td class="transp_hintergrund"><br />

                <strong>Fest eingestellte Klassifizierung</strong>
                <p>
					Solange der gewählte Indikator nicht gewechselt wird, werden bei Aufruf anderer Zeitschnitte nur jeweils die Wert-Ober und -Untergrenze erweitert.
                    <br />
					<br />
				  Histogramm mit festgelegten Klassengrenzen:<br />
 
                    <?php 
					$Tabellenhoehe = "100";
					?>
                  <a name="histogramm" id="histogramm"></a>
					<table style="width:400px; border-spacing:0px; padding:2px; border:1px solid #666666; border-collapse:collapse;">
                   	  <tr style="height:<?php echo $Tabellenhoehe; ?>px;">
							<?php 
                            // Anzeige der Verteilung an sich (Säulen)
                              for($i=0 ; $i <= 100 ; $i++)
                              {
                                  // wird auf Teiler 30 gerechnet (hier Erhöhung optisch besser)
                                  $h = ($_SESSION['Temp']['i_Verteilung'][$i]/$_SESSION['Temp']['i_Verteilung']['Max'])*$Tabellenhoehe;                               
                                  // $h_fuell = 120-$h; // Durch Erhöhung auf y+e, statt y, bezogen
                                                            
                                  ?>
									<td title="<?php echo round($_SESSION['Temp']['i_Verteilung'][$i],0); ?> Gebietseinheiten" style="padding:0px; vertical-align:bottom; width:2px; background:#<?php 
                                        if(is_array($_SESSION['Temp']['manuelle_Klasse']))
                                        {
                                            $DurchlaufCheck_a = 0; 
											foreach($_SESSION['Temp']['manuelle_Klasse'] as $Klassensets)
                                            {
                                                if((!$DurchlaufCheck_a and $Klassensets['Untergrenze']==0 and $i==0) or ($Klassensets['Untergrenze'] < $i and $Klassensets['Obergrenze'] >= $i))
                                                {
                                                    echo $Klassensets['Farbwert'];
													$DurchlaufCheck_a = 1; 
                                                }
                                            }
                                        }
                                        ?>">
                                        
                                        <div style=" line-height:0px; font-size:0px; background:#000000; border:0px; margin-left:1px; padding:0px; width:2px; height:<?php echo $h; ?>px;<?php 
										if(!$h) { echo " visibility:hidden; "; }
										?>"></div>
                        </td>
                                  <?php 
                              }
                              ?>
                  <tr>
                  <tr style="height:1px; background:#FFF;"></tr>
                       <tr style="height:10px;">
							<?php 
                            // Anzeige der Verteilung an sich (Säulen)
                              for($i=0 ; $i <= 100 ; $i++)
                              {
                                    // wird auf Teiler 30 gerechnet (hier Erhöhung optisch besser)
                                  $h = ($_SESSION['Temp']['i_Verteilung'][$i]/$_SESSION['Temp']['i_Verteilung']['Max'])*$Tabellenhoehe; 
                                  
                                  // $h_fuell = 120-$h; // Durch Erhöhung auf y+e, statt y, bezogen
                                                            
                                  ?>
<td style="padding:0px; vertical-align:bottom; width:2px; background:#<?php 
                                        if(is_array($_SESSION['Temp']['manuelle_Klasse']))
                                        {
                                            foreach($_SESSION['Temp']['manuelle_Klasse'] as $Klassensets)
                                            {
                                                $DurchlaufCheck = 0; 
												if((!$DurchlaufCheck and $Klassensets['Untergrenze']==0 and $i==0) or ($Klassensets['Untergrenze'] < $i and $Klassensets['Obergrenze'] >= $i))
                                                {
                                                    echo $Klassensets['Farbwert'];
													$DurchlaufCheck = 1; 
                                                }
                                            }
                                        }
                                        ?>">
                                        
                         </td>
                                  <?php 
                              }
                              ?>
                       <tr>
                       </tr>
                       	 <td colspan="101">
                           <div style="position: relative; clear:both;">
                                  <div style=" position:absolute; top:0px; left:0px;"><?php 
                                                            echo number_format($_SESSION['Dokument']['Fuellung']['Indikator_Wert_min']-1000000000,$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.')
                                                                    ." ".$_SESSION['Dokument']['Fuellung']['Indikator_Einheit']; ?></div>
                                  <div style=" position:absolute; top:0px; right:0px;"><?php 
                                                            echo number_format($_SESSION['Dokument']['Fuellung']['Indikator_Wert_max']-1000000000,$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.')
                                                                    ." ".$_SESSION['Dokument']['Fuellung']['Indikator_Einheit']; ?></div> 
                           </div> 
                         </td>
                       </tr>
                </table>

                    
                    <br />
                <br />
                </p>
            <br /></td>
    </tr>
         <?php 
	}	
//------------------------	Ende Textbereich für Einstellungen beibehalten -------------------------------------			
		
		
	
		
		
		
//------------------------	Beginn Textbereich für Manuelle Klassi (Experten) -------------------------------------			
		
		// Für echte Manuelle Klassifizierung
		//-----------------------------------------------
		if($_SESSION['Dokument']['Fuellung']['Typ'] == "manuell Klassifizierte Farbreihe" and !$_SESSION['Dokument']['Fuellung']['ManUntertyp'])
		{
			?>        
          <tr>
            <td height="69" class="transp_hintergrund">&nbsp;</td>
              <td class="transp_hintergrund">
              <br />

                <strong>Manuelle Klassifizierung (für Experten)</strong>
                       <p>                Benutzen Sie diese Variante, wenn Sie die Anzahl, die Grenzen und die Farben der Klassen selbst definieren möchten.<br /> </p>
                	
                	<p style="font-size:10px;"> Diese Klassifizierung kann dann auch für andere Zeitschnitte verwendet werden. Bei einer Änderung des angezeigten Indikators, werden die hier eingestellten Werte allerdings verworfen.</p>
                <br /> 
               <a href="svg_zeichenvorschrift_klass.php?kopieren=1" target="_self" class="button_gruen_abschicken" style="  padding:3px; font-size:13px; background:#9FA8CC; "> &nbsp;Standardklassendefinition laden&nbsp;</a>
                  <br />
                  <br />
                
                
                
                
                <!----alteVersion
                <p>                Benutzen Sie diese Variante, wenn Sie die Anzahl, die Grenzen und die Farben der Klassen selbst definieren möchten. Diese Klassifizierung kann dann auch für andere Zeitschnitte verwendet werden. Bei einer Änderung des angezeigten Indikators, werden die hier eingestellten Werte allerdings verworfen.
                <br />
                Klicken Sie bitte <a href="svg_zeichenvorschrift_klass.php?kopieren=1" target="_self" class="button_gruen_abschicken" style="text-decoration:none; color:#333;"> &nbsp;hier&nbsp;</a>, wenn sie die Klassen auf die Einstellungen der automatisierten Klassifizierung
                  zurücksetzen möchten!<br />
                  <br />

--->



					Histogramm mit Klassengrenzen:<br />
 
                    <?php 
					$Tabellenhoehe = "100";
					?>
                                      
                  <a name="histogramm" id="histogramm"></a>
                <table style="width:400px; border-spacing:0px;  border:1px solid #666666; border-collapse:collapse;">
                   	  <tr style="height:<?php echo $Tabellenhoehe; ?>px;">
							<?php 
                            // Anzeige der Verteilung an sich (Säulen)
                              for($i=0 ; $i <= 100 ; $i++)
                              {
                                  // wird auf Teiler 30 gerechnet (hier Erhöhung optisch besser)
                                  $h = ($_SESSION['Temp']['i_Verteilung'][$i]/$_SESSION['Temp']['i_Verteilung']['Max'])*$Tabellenhoehe;                               
                                  // $h_fuell = 120-$h; // Durch Erhöhung auf y+e, statt y, bezogen
                                                            
                                  ?>
									<td title="<?php echo round($_SESSION['Temp']['i_Verteilung'][$i],0); ?> Gebietseinheiten" style="padding:0px; vertical-align:bottom; width:2px; background:#<?php 
                                        if(is_array($_SESSION['Temp']['manuelle_Klasse']))
                                        {
                                            $DurchlaufCheck_a = 0;
											foreach($_SESSION['Temp']['manuelle_Klasse'] as $Klassensets)
                                            {
                                                if(!$DurchlaufCheck_a and (($Klassensets['Untergrenze']==0 and $i==0) or ($Klassensets['Untergrenze'] < $i and $Klassensets['Obergrenze'] >= $i)))
                                                {
                                                    echo $Klassensets['Farbwert'];
													$DurchlaufCheck_a = 1; 
                                                }
                                            }
                                        }
                                        ?>">
                                        
                                        <div style=" line-height:0px; font-size:0px; background:#000000; border:0px; margin-left:1px; padding:0px; width:2px; height:<?php echo $h; ?>px;<?php 
										if(!$h) { echo " visibility:hidden; "; }
										?>"></div>
                                    </td>
                                  <?php 
                              }
                              ?>
                       <tr>
                       <tr style="height:1px; background:#FFF;"></tr>
                       <tr style="height:10px;">
							<?php 
                            // Anzeige der Verteilung an sich (Säulen)
                              for($i=0 ; $i <= 100 ; $i++)
                              {
                                    // wird auf Teiler 30 gerechnet (hier Erhöhung optisch besser)
                                  $h = ($_SESSION['Temp']['i_Verteilung'][$i]/$_SESSION['Temp']['i_Verteilung']['Max'])*$Tabellenhoehe; 
                                  
                                  // $h_fuell = 120-$h; // Durch Erhöhung auf y+e, statt y, bezogen
                                                            
                                  ?>
<td style="padding:0px; vertical-align:bottom; width:2px; background:#<?php 
                                        if(is_array($_SESSION['Temp']['manuelle_Klasse']))
                                        {
                                            $DurchlaufCheck = 0;
											foreach($_SESSION['Temp']['manuelle_Klasse'] as $Klassensets)
                                            {
                                                if((!$DurchlaufCheck and $Klassensets['Untergrenze']==0 and $i==0) or ($Klassensets['Untergrenze'] < $i and $Klassensets['Obergrenze'] >= $i))
                                                {
                                                    echo $Klassensets['Farbwert'];
													$DurchlaufCheck = 1;
                                                }
                                            }
                                        }
                                        ?>">
                                        
                                    </td>
                                  <?php 
                              }
                              ?>
                       <tr>
                       </tr>
                       	 <td colspan="101">
                           <div style="position: relative; clear:both;">
                                  <div style=" position:absolute; top:0px; left:0px;"><?php 
                                                            echo number_format($_SESSION['Dokument']['Fuellung']['Indikator_Wert_min']-1000000000,$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.')
                                                                    ." ".$_SESSION['Dokument']['Fuellung']['Indikator_Einheit']; ?></div>
                                  <div style=" position:absolute; top:0px; right:0px;"><?php 
                                                            echo number_format($_SESSION['Dokument']['Fuellung']['Indikator_Wert_max']-1000000000,$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.')
                                                                    ." ".$_SESSION['Dokument']['Fuellung']['Indikator_Einheit']; ?></div> 
                           </div> 
                         </td>
                       </tr>
                    </table>
                    
                    
                    
                    
                    
                    
                    
                    
                    
                    
                    <br />
                <br />
                </p>
              <form action="svg_zeichenvorschrift_klass.php#histogramm" method="post" enctype="multipart/form-data">
                    <table style="border-collapse:collapse;">
   					<tr style="font-size:12px;">
                         <td width="63" class="transp_hintergrund" style=" border:solid 1px #333333">Nummer</td>
                        <td width="130" class="transp_hintergrund" style=" border:solid 1px #333333">Untergrenze (&lt;x)</td>
                        <td width="130" class="transp_hintergrund" style=" border:solid 1px #333333">Obergrenze (&ge;x)</td>
                        <td class="transp_hintergrund" style=" border:solid 1px #333333">Hex-Farbcode (RRGGBB)</td>
               	        <td class="transp_hintergrund" style=" border:solid 1px #333333">&nbsp;</td>
   					</tr>
                      
                        <?php 
                        for($i_klassNr = 0 ; $i_klassNr <= 20 ; $i_klassNr++ )
                        {
                            
							if($_SESSION['Temp']['manuelle_Klasse'][$i_klassNr-1]['Wert_Untergrenze'] or $_SESSION['Temp']['manuelle_Klasse'][$i_klassNr]['Wert_Obergrenze'])
							{
								?>
							<tr>
							  <td height="50" valign="top" class="transp_hintergrund" style=" border-bottom:solid 1px #333333"><?php echo $i_klassNr; ?>                           	  </td>
							  <td valign="top" class="transp_hintergrund" style=" border-bottom:solid 1px #333333">
									<?php 
									if($_SESSION['Temp']['manuelle_Klasse'][$i_klassNr]['Wert_Untergrenze'])
									{
										echo number_format($_SESSION['Temp']['manuelle_Klasse'][$i_klassNr]['Wert_Untergrenze']-1000000000,$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.'); 
									}
									?>                                </td>
                              <td valign="top" class="transp_hintergrund" style=" border-bottom:solid 1px #333333">
                              <input name="Obergrenze[]" type="text" value="<?php 
							  if($_SESSION['Temp']['manuelle_Klasse'][$i_klassNr]['Wert_Obergrenze'])
							  {
							  	echo number_format($_SESSION['Temp']['manuelle_Klasse'][$i_klassNr]['Wert_Obergrenze']-1000000000,$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.'); 
							  }
							  ?>" size="5" /> 
                              <?php 
							  if($_SESSION['Temp']['manuelle_Klasse'][$i_klassNr]['Wert_Obergrenze'] < $_SESSION['Temp']['manuelle_Klasse'][$i_klassNr]['Wert_Untergrenze'])
							  {
									?>
								  <span class="Text_10px" style="color:#CC0000;"> <br />
								  Wert nicht schlüssig!</span>                              
								  <?php 
							  }
		
							  if($_SESSION['Temp']['manuelle_Klasse'][$i_klassNr]['Wert_Obergrenze'] == $_SESSION['Temp']['manuelle_Klasse'][$i_klassNr]['Wert_Untergrenze']
							  and $_SESSION['Temp']['manuelle_Klasse'][$i_klassNr]['Wert_Untergrenze'])
							  {
									?>
								  <span class="Text_10px" style="color:#CC0000;"> <br />
						      Klasse beinhaltet keinen Wertebereich!</span>                              
								  <?php 
							  }
		
							  ?> 
                              </td>
							  <td width="172" valign="top" class="transp_hintergrund" style=" border-bottom:solid 1px #333333">
							  	<div style="float:left; width:20px; height:15px; border:1px solid #666666; background-color:#<?php echo $_SESSION['Temp']['manuelle_Klasse'][$i_klassNr]['Farbwert']; ?>;"></div>
                           	  &nbsp;&nbsp;#<input name="Farbwert[]" type="text" value="<?php echo $_SESSION['Temp']['manuelle_Klasse'][$i_klassNr]['Farbwert']; ?>" size="6" />                                
                              <div style="clear:both;"></div>
                              </td>
									<td width="73" valign="top" class="transp_hintergrund" style=" border-bottom:solid 1px #333333">
									<?php 
									if(!$_SESSION['Temp']['manuelle_Klasse'][$i_klassNr+1]['Wert_Untergrenze'] 
									and !$_SESSION['Temp']['manuelle_Klasse'][$i_klassNr+1]['Wert_Obergrenze'] 
									and !$_SESSION['Temp']['manuelle_Klasse'][$i_klassNr+1]['Farbwert'] 
									and !$printloesch)
									{
										?>
										<span class="button_standard_abschicken_a" style="background:#CC5555; padding-left:5px;">
											<a href="svg_zeichenvorschrift_klass.php?loesch=<?php echo $i_klassNr; ?>" target="_self"> L&ouml;schen&nbsp;</a>										</span>
										<?php 
										$printloesch=1;
									}
									?>								</td>
							</tr><?php
							}
                        }
                        ?>
                  </table>
                    <br />
					<input type="submit" value="Einstellungen &uuml;bernehmen" class="button_standard_abschicken_a" style="background:#CCCCCC;"/>
                    <input name="Aktion" type="hidden" value="manKlass" />
                </form>
                
                <br /></td>
           </tr>
         <?php 
	}
	//------------------------	Ende Textbereich für Manuelle Klassi (Experten) -------------------------------------				
		
	?>
      			
      			
      				<!--Zurück Button--->	
            <tr>
                <td height="67" valign="top" class="transp_hintergrund">
                <!--<span class="button_standard_abschicken_a" style="background:#BAD380;">
                <a href="svg_html.php#top" target="_self">&nbsp;&nbsp;&nbsp;&lt;= Zurück zur Karte&nbsp;&nbsp;&nbsp;</a></span> -->
               <div style="float:left; margin-right:10px; padding-top:15px;">
                    	<a href="svg_html.php#top" target="_self"><img src="icons_viewer/back.png" alt="Zur&uuml;ck" /><br />
                    	zur&uuml;ck</a>
                </div>
               </td>
							<td valign="top" class="transp_hintergrund"> 
                </td>
            </tr>
               <!--Zurück Button-->   
            
        </table>
        
    
  <br />
</div>
<br />
<br />

</body>
</html>
