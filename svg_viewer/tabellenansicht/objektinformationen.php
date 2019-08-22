<?php 
session_start();
include("../includes_classes/verbindung_mysqli.php");

// Klassen neu berechnen (ausgelagert), da unter Umständen keine Autom. Klassifiz. gewählt sein könnte, dies jedoch für das Histogramm benötigt wird
include('../svg_klassenbildung.php');


$AGS = $_POST['ags'];
$Name = $_POST['name'];
$Wert = $_POST['wert'];

$Wertebereich = $_SESSION['Dokument']['Fuellung']['Wertebereich'];
$prozentwert = $_SESSION['Dokument']['Fuellung']['Wertebereich_ein_Prozent'];
$X_min = $_SESSION['Dokument']['Fuellung']['Indikator_Wert_min']-1000000000;
$W_Min = $_POST['W_Min'];

$Standardabweichung  = $_POST['Standardabweichung'];
$Median_1_Wert = $_POST['Median_1_Wert'];
$Median_1_Name = $_POST['Median_1_Name'];
$Median_1_Titel = 'title="Median: '.$Median_1_Wert.'"';
$Median_2_Wert = $_POST['Median_2_Wert'];
$Median_2_Name = $_POST['Median_2_Name'];
$Median_2_Titel = 'title="Median: '.$Median_2_Wert.'"';
$n_Stichproben = $_POST['n']; 

$AMittel = $_POST['AMittel'];
$AMittel_Titel = 'title="Arithmetisches Mittel: '.number_format($AMittel,$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.').'"';

$Stabw_u = $AMittel - $Standardabweichung;
$Stabw_o = $AMittel + $Standardabweichung;
$Stabw_2u = $AMittel - (2*$Standardabweichung);
$Stabw_2o = $AMittel + (2*$Standardabweichung);


?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Statistik Gebietseinheit - IÖR Monitor</title>
<link href="../screen_viewer.css" rel="stylesheet" type="text/css" />
<style type="text/css">

body {
	font-size:12px;	
}
.td_kopftabelle {
	text-align: left;
	padding-top: 2px;
	padding-right: 15px;
	padding-bottom: 2px;
	padding-left: 15px;
	border: 1px solid #CCC;
	vertical-align: top;
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

</style>
</head>

<body style="padding-left:35px;" class="body_unterseiten">
<a style="border:0px;" href="http://www.ioer-monitor.de" target="_blank">
<img src="../gfx/kopf_v2_unterseiten.png" width="999" height="119" alt="Kopfgrafik" /><br />
</a>
<br />
</div>

<div style="border: #999999 solid 0px; padding:10px; ">
  <h3>Wert für <?php 
							 
							 		echo $Name;
									// Unterscheidung nach Land-/Kreis-/Gemeindeschlüssel
							 		if(strlen($AGS) == 2) echo ' (Landesschl&uuml;ssel: '.$AGS.')'; 
							  		if(strlen($AGS) == 5) echo ' (Kreisschl&uuml;ssel: '.$AGS.')'; 
							  		if(strlen($AGS) == 9) echo ' (AGS: '.$AGS.')'; 
							  
	?>
    in Bezug auf statistische Kenngrößen der räumlichen Auswahl und <br />
    des gewählten Indikators: <?php echo $_SESSION['Dokument']['Fuellung']['Indikator_Beschreibung']; ?> (<?php echo $_SESSION['Dokument']['Jahr_Anzeige']; ?>)</h3>
  <table style="width:800px; border:0px; border-collapse:collapse;">
    <tr>
                  <td valign="top" class="transp_hintergrund" style="width:200px; padding:0px;">
                  		
                        
                        <table style="width:800px; border:0px; border-collapse:collapse;">
                            <tr class="grauer_hintergrund">
                              <td colspan="3" valign="top" class="td_kopftabelle"><strong>Statistische Kenngrößen</strong></td>
                            </tr>
                            <tr>
                              <td colspan="2" valign="top" class="td_kopftabelle">Einheit:</td>
                              <td valign="top" class="td_kopftabelle"><?php echo $_SESSION['Dokument']['Fuellung']['Indikator_Einheit']; ?></td>
                          </tr>
                          <tr>
                              <td colspan="2" valign="top" class="td_kopftabelle">Gebiet:</td>
                              <td valign="top" class="td_kopftabelle"><?php echo $_SESSION['Datenbestand_Ausgabe']; ?></td>
                         </tr>
                         <tr>
                              <td colspan="2" valign="top" class="td_kopftabelle">Raumgliederung:</td>
                              <td valign="top" class="td_kopftabelle"><?php 
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
                            <tr>
                              <td colspan="2" valign="top" class="td_kopftabelle">Anzahl erfasster Gebietseinheiten (n):</td>
                              <td valign="top" class="td_kopftabelle"><?php echo $n_Stichproben; ?>&nbsp;</td>
                            </tr>
                           
                          
                            <tr>
                              <td colspan="3" valign="top" class="td_kopftabelle">&nbsp;</td>
                            </tr>
                            <tr>
                              <td valign="top" class="td_kopftabelle"><div style="background-color:#33AA33;width:10px; height:10px;">&nbsp;</div></td>
                              <td valign="top" class="td_kopftabelle">Arithmetisches Mittel: &micro;</td>
                              <td valign="top" class="td_kopftabelle"><?php echo number_format($AMittel,$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.')." "; ?></td>
                            </tr>
                            <tr>
                              <td valign="top" class="td_kopftabelle"><div style="background-color:#5555DD;width:10px; height:10px;">&nbsp;</div></td>
                              <td valign="top" class="td_kopftabelle">Median:</td>
                              <td valign="top" class="td_kopftabelle"><?php 
							  /* ... zu viele belanglose Infos und Umlautfehler in den Namen
							  
							  if($Median_2_Name)
							  {
								  echo "Oberer Median: ".$Median_1_Wert." (".$Median_1_Name."); Unterer Median: ".$Median_2_Wert." (".$Median_2_Name.")";
							  }
							  else
							  {
								  echo $Median_1_Wert." (".$Median_1_Name.")";
							  } */
 							  
							  if($Median_2_Name)
							  {
								  echo number_format($Median_1_Wert,$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.');
								  if($Median_1_Wert != $Median_2_Wert) echo " (2. Median: ".number_format($Median_2_Wert,$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.').")"; // 2. Median nur anzeigen, wenn verschieden vom 1.
							  }
							  else
							  {
								  echo number_format($Median_1_Wert,$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.');
							  } 
                              ?></td>
                          </tr>
                            <tr>
                              <td valign="top" class="td_kopftabelle"><div style="background-color:#77DD77;width:10px; height:10px;">&nbsp;</div></td>
                              <td valign="top" class="td_kopftabelle">Standardabweichung: &#963; (&micro;-&#963; ... &micro;+&#963;)</td>
                              <td valign="top" class="td_kopftabelle">
                               <?php echo number_format($Standardabweichung,$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.')." "; ?>
                              (<?php 
							   // Unterste Wertegrenze bei Ausgabe beachten:
							   if($Stabw_u < $W_Min)
							   {
								   echo number_format($W_Min,$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.');
							   }
							   else
							   {
							   		echo number_format($Stabw_u,$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.'); 
							   }
							   ?> ... <?php echo number_format($Stabw_o,$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.'); ?>)

                              </td>
                          </tr>
                            <tr>
                              <td valign="top" class="td_kopftabelle"><div style="background-color:#99BB99;width:10px; height:10px;">&nbsp;</div></td>
                              <td valign="top" class="td_kopftabelle">Doppelte Standardabw.: 2&#963; (&micro;-2&#963; ... &micro;+2&#963;)</td>
                              <td valign="top" class="td_kopftabelle"><?php 
							  	echo number_format($Standardabweichung2=(2*$Standardabweichung),$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.')." "; 
								?>
                         	(<?php 
						  	// Unterste Wertegrenze bei Ausgabe beachten:
							   if($Stabw_2u < $W_Min)
							   {
								   echo number_format($W_Min,$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.');
							   }
							   else
							   {
							   		echo number_format($Stabw_2u,$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.'); 
							   }

						
								?> ... <?php echo number_format($Stabw_2o,$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.'); ?>) </td>
                          </tr>
                          <tr valign="top">
                              <td valign="top" class="td_kopftabelle">&nbsp;</td>
                              <td colspan="2" valign="top" class="td_kopftabelle">Falls die oben genannten Grenzen der Standardabweichung über den Wertebereich hinaus reichen, werden diese durch das Minimum oder das Maximum des Wertebereichs ersetzt.<br />                                <br />                                <br /></td>
                          </tr>
                          <tr valign="top" class="grauer_hintergrund">
                              <td colspan="3" valign="top" class="td_kopftabelle"><strong>Lokalisierung des Wertes im Histogramm</strong></td>
                          </tr>
                          <tr>
                              <td width="40" valign="top" style="padding-top:5px;" class="td_kopftabelle"><div style="background-color:#DD5555;width:10px; height:10px;">&nbsp;</div></td>
                              <td width="282" valign="top" class="td_kopftabelle">Wert für  <strong>
							  <?php 
							 
							 		echo $Name;
									// Unterscheidung nach Land-/Kreis-/Gemeindeschlüssel
							 		if(strlen($AGS) == 2) echo ' (Landesschl&uuml;ssel: '.$AGS.')'; 
							  		if(strlen($AGS) == 5) echo ' (Kreisschl&uuml;ssel: '.$AGS.')'; 
							  		if(strlen($AGS) == 9) echo ' (AGS: '.$AGS.')'; 
							  
							  ?>
                              </strong>:</td>
                              <td width="462" valign="top" class="td_kopftabelle">
                               	  <?php echo number_format($Wert,$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.')." "; ?>
                              </td>
                          </tr>

             		 </table>
	  </td>
                </tr>
 
               <tr>
                <td valign="top" class="transp_hintergrund" style=" padding:0px;">
					<br />

			  	  <?php 
					$Tabellenhoehe = "100";
					
					// Finden der Max-Position
					for($i=0 ; $i <= 100 ; $i++)
                   	{
						if($_SESSION['Temp']['i_Verteilung'][$i]/$_SESSION['Temp']['i_Verteilung']['Max'] == 1)
						{
							$MaxHist = '<div style=" text-align:right; font-size:12px; color:#999999;">
                            	Häufigkeitsmaximum mit '.$_SESSION['Temp']['i_Verteilung']['Max'].' Gebietseinheiten im Bereich von &asymp; 
								'.number_format($Untergrenze = ($prozentwert * $i) + $X_min,$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.').
								' - '.number_format($Obergrenze = ($prozentwert * ($i+1)) + $X_min,$_SESSION['Dokument']['Fuellung']['Rundung'], ',', '.').
								'</div>';
							// bei mehreren Maxima nur Wert ausgeben
							if($MaxHist_vorhanden) $MaxHist = '<div style=" text-align:right; font-size:12px; color:#999999;">Häufigkeitsmaxima: '.$_SESSION['Temp']['i_Verteilung']['Max'].' Gebietseinheiten</div>';
							$MaxHist_vorhanden = 1;
							
						}
						
					}
					echo $MaxHist;
					?>
                    
                    
              <table style=" margin-top:2px; width:800px; border-spacing:0px; border-left:0px; border-top:1px dashed #999999; border-bottom:0px; border-right:0px; border-collapse:collapse;">
                    	<tr style="height:<?php echo $Tabellenhoehe; ?>px;">
							<?php 
                            // Anzeige der Verteilung an sich (Säulen)
                              for($i=0 ; $i <= 100 ; $i++)
                              {
                                  // wird auf Teiler 30 gerechnet (hier Erhöhung optisch besser)
                                  $h = ($_SESSION['Temp']['i_Verteilung'][$i]/$_SESSION['Temp']['i_Verteilung']['Max'])*$Tabellenhoehe;  
								  // wenn Wert != 0 aber kleiner als 1 dann auf 1 erhöhen (damit Anzeige möglich ist)
								  if($h > 0 and $h < 1) $h = 1;
								  
								  if($_SESSION['Temp']['i_Verteilung'][$i]/$_SESSION['Temp']['i_Verteilung']['Max'] == 1)
								  {
										  
								  }
                                  // $h_fuell = 120-$h; // Durch Erhöhung auf y+e, statt y, bezogen
                                                           
                                  ?>
                                    <td title="<?php echo round($_SESSION['Temp']['i_Verteilung'][$i],0); ?> Gebietseinheiten" style="padding:0px; vertical-align:bottom; width:4px; background:#FFFFFF<?php 
										// Einfärben der Prozentklasse, in der sich das Objekt befindet
										$Farbwert_HG = "";
										
										/* 										
										$AGS = $_POST['ags'];
										$Name = $_POST['name'];
										$Wert = $_POST['wert'];
										
										$Wertebereich = $_SESSION['Dokument']['Fuellung'][Wertebereich];
										$prozentwert = $_SESSION['Dokument']['Fuellung'][Wertebereich_ein_Prozent];
										
										$Standardabweichung  = $_POST['Standardabweichung'];
										$Median_1_Wert = $_POST['Median_1_Wert'];
										$Median_1_Name = $_POST['Median_1_Name'];
										$Median_2_Wert = $_POST['Median_2_Wert'];
										$Median_2_Name = $_POST['Median_2_Name']; 
										*/
										
										
										/* 
										$Untergrenze = ($prozentwert * $i) + $X_min;
										$Obergrenze = ($prozentwert * ($i+1)) + $X_min;
										
                                         if(($Untergrenze==0 and $Wert==0) or ($Untergrenze < $Wert and $Obergrenze >= $Wert))
                                         {
                                             $Farbwert_HG = "990000";
                                         }
										else
										{
											if(!$Farbwert_HG) $Farbwert_HG = "FFFFFF";
										}
                                         
										echo $Farbwert_HG; */
                                        ?>">
                                        
                                      <div style=" line-height:0px; font-size:0px; background:#<?php 
												// Einfärben der Prozentklasse, in der sich das Objekt befindet
												$Farbwert_HG = "";
												
												/* 										
												$AGS = $_POST['ags'];
												$Name = $_POST['name'];
												$Wert = $_POST['wert'];
												
												$Wertebereich = $_SESSION['Dokument']['Fuellung'][Wertebereich];
												$prozentwert = $_SESSION['Dokument']['Fuellung'][Wertebereich_ein_Prozent];
												
												$Standardabweichung  = $_POST['Standardabweichung'];
												$Median_1_Wert = $_POST['Median_1_Wert'];
												$Median_1_Name = $_POST['Median_1_Name'];
												$Median_2_Wert = $_POST['Median_2_Wert'];
												$Median_2_Name = $_POST['Median_2_Name']; 
												*/
												
												$Untergrenze = ($prozentwert * $i) + $X_min;
												$Obergrenze = ($prozentwert * ($i+1)) + $X_min;
																								
												// .......... letztes Statement nötig um Anfangswert mit einzubeziehen (bleibt ansonsten außen vor) ... Check auf Treffer in leerem vorhergehenden Teil => Anzeige
												if((($Untergrenze==0 and $Wert==0) or ($Untergrenze < $Wert and $Obergrenze >= $Wert) or ($i==0 and $Wert < $Obergrenze)) 
												or ($Treffer and $Leeres_Verteilungselement))
												{
													 $Farbwert_HG = "DD5555";
													 $Treffer = 1; // Auswertung eines Treffers für Check auf falsche Verteilungsklasse durch Rundungsfehler
												}
												else
												{
													/* // Check auf Median verlagert in extra DIV
													if($Untergrenze < $Median_1_Wert and $Obergrenze >= $Median_1_Wert or $Untergrenze < $Median_2_Wert and $Obergrenze >= $Median_2_Wert) $Farbwert_HG = "5555DD"; */
													
													// Normalfärbung
													if(!$Farbwert_HG) $Farbwert_HG = "999999";
													$Treffer = 0;
												}
												 
												echo $Farbwert_HG;
                                        
										?>; vertical-align:bottom; border:0px; margin-left:2px; padding:0px; width:4px; height:<?php echo $h; ?>px;<?php 
										if(!$h) 
										{ 
											echo " visibility:hidden; "; 
											$Leeres_Verteilungselement = 1; // Vermerk für unscharfe Anzeige bei Rundungsfehlern für nächsten Durchlauf
										}
										else
										{
											$Leeres_Verteilungselement = 0;
										}
										?>;">
                                        
										<?php 
                                        echo "<!-- ------------------------------------ ".$Untergrenze." ".$Obergrenze." ".$Wert." -------------------------------------------- -->";
                                        
											/* // Check auf Median und Darstellung (kleiner als Säule => wichtig, falls Wert auch auf dem selben Balken liegt)
											if($Untergrenze < $Median_1_Wert and $Obergrenze >= $Median_1_Wert or $Untergrenze < $Median_2_Wert and $Obergrenze >= $Median_2_Wert)
											{
												?><div style="width:2px; margin-left:0px; height:100%; background-color:#5555DD;"></div><?php 
											} */
                                        ?>
                                        </div>
                                    </td>
                                  <?php 
                              }
                              ?>
                       <tr>
                       <tr style="height:1px; background:#FFF;"></tr>
                       <tr style="height:10px; background:#999999;">
							<?php 
                            // Anzeige der Eckdaten der Verteilung
                              for($i=0 ; $i <= 100 ; $i++)
                              {
                                    // wird auf Teiler 30 gerechnet (hier Erhöhung optisch besser)
                                  $h = ($_SESSION['Temp']['i_Verteilung'][$i]/$_SESSION['Temp']['i_Verteilung']['Max'])*$Tabellenhoehe; 
                                  
                                  // $h_fuell = 120-$h; // Durch Erhöhung auf y+e, statt y, bezogen
                                                            
                                  ?>
                                    <td style="padding:0px; vertical-align:bottom; width:2px; background:#<?php 
												// Einfärben der Prozentklasse, in der sich das Objekt befindet
												$Farbwert_HG = "";
												$Titel_v = "";
												/* 										
												$AGS = $_POST['ags'];
												$Name = $_POST['name'];
												$Wert = $_POST['wert'];
												
												$Wertebereich = $_SESSION['Dokument']['Fuellung'][Wertebereich];
												$prozentwert = $_SESSION['Dokument']['Fuellung'][Wertebereich_ein_Prozent];
												
												$Standardabweichung  = $_POST['Standardabweichung'];
												$Median_1_Wert = $_POST['Median_1_Wert'];
												$Median_1_Name = $_POST['Median_1_Name'];
												$Median_2_Wert = $_POST['Median_2_Wert'];
												$Median_2_Name = $_POST['Median_2_Name']; 
												*/
												
												$Untergrenze = ($prozentwert * $i) + $X_min;
												$Obergrenze = ($prozentwert * ($i+1)) + $X_min;
												
												// Standardabw. +-2-Sigma
												if(($Untergrenze==0 and $Stabw_u==0) or ($Untergrenze < $Stabw_2o and $Obergrenze > $Stabw_2u))
												{
													// +-Sigma
													if(($Untergrenze==0 and $Stabw_u==0) or ($Untergrenze < $Stabw_o and $Obergrenze > $Stabw_u))
													{
														// Ar-Mittel
														if(($Untergrenze==0 and $AMittel==0) or ($Untergrenze < $AMittel and $Obergrenze >= $AMittel))
														{
															$Farbwert_HG = "33AA33"; // Ar. Mittel
															$Titel_v = $AMittel_Titel;
														}													
														else
														{ 
															$Farbwert_HG = "77DD77"; // 1 Sigma
														}
													}
													else
													{
														$Farbwert_HG = "99BB99"; // 2 Sigma
													}
												}
												else
												{
													if(!$Farbwert_HG) $Farbwert_HG = "999999"; // keine Bedeutung
												}
												 
												echo $Farbwert_HG;
                                        
										?>;" 
										<?php echo $Titel_v; ?> >
                                             <?php 
											// Check auf Median und Darstellung (kleiner als Säule => wichtig, falls Mittelwert auch auf dem selben Balken liegt)
											if($Untergrenze < $Median_1_Wert and $Obergrenze >= $Median_1_Wert) // Median 1 /* or $Untergrenze < $Median_2_Wert and $Obergrenze >= $Median_2_Wert) */
											{
												?><div <?php echo $Median_1_Titel; ?> style="width:100%; margin-left:0px; height:100%; background-color:#5555DD; cursor:default;">&nbsp;</div><?php 
											}
											if($Untergrenze < $Median_2_Wert and $Obergrenze >= $Median_2_Wert and $Median_1_Wert != $Median_2_Wert) // Median 2
											{
												?><div <?php echo $Median_2_Titel; ?> style="width:100%; margin-left:0px; height:100%; background-color:#5555DD; cursor:default;">&nbsp;</div><?php 
											}
                                        	?>
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
                 </td>
              </tr>
        </table>
        <br />
        <span class="nicht_im_print">Hinweis zum Druck dieser Seite: <br />
        Bitte stellen Sie sicher, dass Ihr Browser darauf eingestellt ist, auch Hintergrundfarben zu drucken! <br />
  		Zu finden sind diese Optionen meist unter dem Stichwort &quot;Druckeinstellungen&quot; oder &quot;Seite einrichten&quot;.
 		<br />
		<br />
        <a  href="tabelle_zur_karte_v3.php" target="_self" ><img src="../icons_viewer/back.png" alt="Zur&uuml;ck" /><br /> 
        &nbsp;zur&uuml;ck</a>
    	</span>
  		<br />
  		<br />
        <!-- Hinweis bei unstimmigen Druckoptionen -->
        <div style="width:100%; height:45px; position:relative; font-size:16px;">
            <strong>Hinweis:</strong>&nbsp;Sie&nbsp;sollten&nbsp;die&nbsp;Druckeinstellungen&nbsp;Ihres&nbsp;Browsers&nbsp;korrigieren, <br />
			wenn&nbsp;Sie&nbsp;das&nbsp;Histogramm&nbsp;mit&nbsp;ausdrucken&nbsp;möchten!
            <div style="position:absolute; top:0px; left:0px; width:100%; height:45px; background-color:#FFF;">
            </div>
        </div>
 </div>
<br />
<br />

</body>
</html>
