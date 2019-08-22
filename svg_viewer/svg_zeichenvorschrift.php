<?php 
session_start();
include("includes_classes/verbindung_mysqli.php");

if($_POST['indikator_lokal']) $_SESSION['Dokument']['indikator_lokal'] = '1'; // Datenbasis lokal/global
if($_POST['Lokale_Werte_gesendet'] and !$_POST['indikator_lokal']) $_SESSION['Dokument']['indikator_lokal'] = '0';

// Tauschen von Min-Max
if($_GET['tausch']=="ja")
{
	$temp_max = $_SESSION['Dokument']['Fuellung']['Farbwert_max'];
	$temp_min = $_SESSION['Dokument']['Fuellung']['Farbwert_min'];
	
	$_SESSION['Dokument']['Fuellung']['Farbwert_min'] = $temp_max;
	$_SESSION['Dokument']['Fuellung']['Farbwert_max'] = $temp_min;
}



if($_POST['Aktion']=="ZV_uebernahme")
{
	$_SESSION['Dokument']['Fuellung']['Typ']=$_POST['Typ'];
	
	// Übernahme, hexformatierung und zusammenführung aus getrennten Textboxen und Prüfung auf < 255
	if(strlen($R_hex_post = dechex($_POST['Farbwert_min_R'])) < 2) $R_hex_post = '0'.$R_hex_post;
	if($_POST['Farbwert_min_R'] > 255) $R_hex_post = 'FF';
	if(strlen($G_hex_post = dechex($_POST['Farbwert_min_G'])) < 2) $G_hex_post = '0'.$G_hex_post;
	if($_POST['Farbwert_min_G'] > 255) $G_hex_post = 'FF';
	if(strlen($B_hex_post = dechex($_POST['Farbwert_min_B'])) < 2) $B_hex_post = '0'.$B_hex_post;
	if($_POST['Farbwert_min_B'] > 255) $B_hex_post = 'FF';
	$_SESSION['Dokument']['Fuellung']['Farbwert_min'] = $R_hex_post.$G_hex_post.$B_hex_post;
	
	if(strlen($R_hex_post = dechex($_POST['Farbwert_max_R'])) < 2) $R_hex_post = '0'.$R_hex_post;
	if($_POST['Farbwert_max_R'] > 255) $R_hex_post = 'FF';
	if(strlen($G_hex_post = dechex($_POST['Farbwert_max_G'])) < 2) $G_hex_post = '0'.$G_hex_post;
	if($_POST['Farbwert_max_G'] > 255) $G_hex_post = 'FF';
	if(strlen($B_hex_post = dechex($_POST['Farbwert_max_B'])) < 2) $B_hex_post = '0'.$B_hex_post;
	if($_POST['Farbwert_max_B'] > 255) $B_hex_post = 'FF';
	$_SESSION['Dokument']['Fuellung']['Farbwert_max'] = $R_hex_post.$G_hex_post.$B_hex_post;
	
	if(strlen($R_hex_post = dechex($_POST['LeerFarbe_R'])) < 2) $R_hex_post = '0'.$R_hex_post;
	if($_POST['LeerFarbe_R'] > 255) $R_hex_post = 'FF';
	if(strlen($G_hex_post = dechex($_POST['LeerFarbe_G'])) < 2) $G_hex_post = '0'.$G_hex_post;
	if($_POST['LeerFarbe_G'] > 255) $G_hex_post = 'FF';
	if(strlen($B_hex_post = dechex($_POST['LeerFarbe_B'])) < 2) $B_hex_post = '0'.$B_hex_post;
	if($_POST['LeerFarbe_B'] > 255) $B_hex_post = 'FF';
	$_SESSION['Dokument']['Fuellung']['LeerFarbe'] = $R_hex_post.$G_hex_post.$B_hex_post;
	
}

// ------------------------------- Für Get Übergaben den Farben aus Slider -----------------------------
if($_GET['Farbwert_max_R']!="")
{
	// Übernahme, hexformatierung und zusammenführung aus getrennten Textboxen und Prüfung auf < 255
	if(strlen($R_hex_GET = dechex($_GET['Farbwert_max_R'])) < 2) $R_hex_GET = '0'.$R_hex_GET;
	if($_GET['Farbwert_max_R'] > 255) $R_hex_GET = 'FF';
	$temp = $R_hex_GET.$_SESSION['Dokument']['Fuellung']['Farbwert_max'][2].$_SESSION['Dokument']['Fuellung']['Farbwert_max'][3]
											.$_SESSION['Dokument']['Fuellung']['Farbwert_max'][4].$_SESSION['Dokument']['Fuellung']['Farbwert_max'][5];
	$_SESSION['Dokument']['Fuellung']['Farbwert_max'] = $temp;	
}
if($_GET['Farbwert_max_G']!="")
{
	// Übernahme, hexformatierung und zusammenführung aus getrennten Textboxen und Prüfung auf < 255
	if(strlen($G_hex_GET = dechex($_GET['Farbwert_max_G'])) < 2) $G_hex_GET = '0'.$G_hex_GET;
	if($_GET['Farbwert_max_G'] > 255) $G_hex_GET = 'FF';
	$temp = $_SESSION['Dokument']['Fuellung']['Farbwert_max'][0].$_SESSION['Dokument']['Fuellung']['Farbwert_max'][1]
											.$G_hex_GET.$_SESSION['Dokument']['Fuellung']['Farbwert_max'][4].$_SESSION['Dokument']['Fuellung']['Farbwert_max'][5];
	$_SESSION['Dokument']['Fuellung']['Farbwert_max'] = $temp;	
}
if($_GET['Farbwert_max_B']!="")
{
	// Übernahme, hexformatierung und zusammenführung aus getrennten Textboxen und Prüfung auf < 255
	if(strlen($B_hex_GET = dechex($_GET['Farbwert_max_B'])) < 2) $B_hex_GET = '0'.$B_hex_GET;
	if($_GET['Farbwert_max_B'] > 255) $B_hex_GET = 'FF';
	$temp = $_SESSION['Dokument']['Fuellung']['Farbwert_max'][0].$_SESSION['Dokument']['Fuellung']['Farbwert_max'][1]
											.$_SESSION['Dokument']['Fuellung']['Farbwert_max'][2].$_SESSION['Dokument']['Fuellung']['Farbwert_max'][3].$B_hex_GET;
	$_SESSION['Dokument']['Fuellung']['Farbwert_max'] = $temp;	
}

if($_GET['Farbwert_min_R']!="")
{
	// Übernahme, hexformatierung und zusammenführung aus getrennten Textboxen und Prüfung auf < 255
	if(strlen($R_hex_GET = dechex($_GET['Farbwert_min_R'])) < 2) $R_hex_GET = '0'.$R_hex_GET;
	if($_GET['Farbwert_min_R'] > 255) $R_hex_GET = 'FF';
	$temp = $R_hex_GET.$_SESSION['Dokument']['Fuellung']['Farbwert_min'][2].$_SESSION['Dokument']['Fuellung']['Farbwert_min'][3]
											.$_SESSION['Dokument']['Fuellung']['Farbwert_min'][4].$_SESSION['Dokument']['Fuellung']['Farbwert_min'][5];
	$_SESSION['Dokument']['Fuellung']['Farbwert_min'] = $temp;	
}
if($_GET['Farbwert_min_G']!="")
{
	// Übernahme, hexformatierung und zusammenführung aus getrennten Textboxen und Prüfung auf < 255
	if(strlen($G_hex_GET = dechex($_GET['Farbwert_min_G'])) < 2) $G_hex_GET = '0'.$G_hex_GET;
	if($_GET['Farbwert_min_G'] > 255) $G_hex_GET = 'FF';
	$temp = $_SESSION['Dokument']['Fuellung']['Farbwert_min'][0].$_SESSION['Dokument']['Fuellung']['Farbwert_min'][1]
											.$G_hex_GET.$_SESSION['Dokument']['Fuellung']['Farbwert_min'][4].$_SESSION['Dokument']['Fuellung']['Farbwert_min'][5];
	$_SESSION['Dokument']['Fuellung']['Farbwert_min'] = $temp;	
}
if($_GET['Farbwert_min_B']!="")
{
	// Übernahme, hexformatierung und zusammenführung aus getrennten Textboxen und Prüfung auf < 255
	if(strlen($B_hex_GET = dechex($_GET['Farbwert_min_B'])) < 2) $B_hex_GET = '0'.$B_hex_GET;
	if($_GET['Farbwert_min_B'] > 255) $B_hex_GET = 'FF';
	$temp = $_SESSION['Dokument']['Fuellung']['Farbwert_min'][0].$_SESSION['Dokument']['Fuellung']['Farbwert_min'][1]
											.$_SESSION['Dokument']['Fuellung']['Farbwert_min'][2].$_SESSION['Dokument']['Fuellung']['Farbwert_min'][3].$B_hex_GET;
	$_SESSION['Dokument']['Fuellung']['Farbwert_min'] = $temp;	
}




if($_GET['LeerFarbe_R']!="")
{
	// Übernahme, hexformatierung und zusammenführung aus getrennten Textboxen und Prüfung auf < 255
	if(strlen($R_hex_GET = dechex($_GET['LeerFarbe_R'])) < 2) $R_hex_GET = '0'.$R_hex_GET;
	if($_GET['LeerFarbe_R'] > 255) $R_hex_GET = 'FF';
	$temp = $R_hex_GET.$_SESSION['Dokument']['Fuellung']['LeerFarbe'][2].$_SESSION['Dokument']['Fuellung']['LeerFarbe'][3]
											.$_SESSION['Dokument']['Fuellung']['LeerFarbe'][4].$_SESSION['Dokument']['Fuellung']['LeerFarbe'][5];
	$_SESSION['Dokument']['Fuellung']['LeerFarbe'] = $temp;	
}
if($_GET['LeerFarbe_G']!="")
{
	// Übernahme, hexformatierung und zusammenführung aus getrennten Textboxen und Prüfung auf < 255
	if(strlen($G_hex_GET = dechex($_GET['LeerFarbe_G'])) < 2) $G_hex_GET = '0'.$G_hex_GET;
	if($_GET['LeerFarbe_G'] > 255) $G_hex_GET = 'FF';
	$temp = $_SESSION['Dokument']['Fuellung']['LeerFarbe'][0].$_SESSION['Dokument']['Fuellung']['LeerFarbe'][1]
											.$G_hex_GET.$_SESSION['Dokument']['Fuellung']['LeerFarbe'][4].$_SESSION['Dokument']['Fuellung']['LeerFarbe'][5];
	$_SESSION['Dokument']['Fuellung']['LeerFarbe'] = $temp;	
}
if($_GET['LeerFarbe_B']!="")
{
	// Übernahme, hexformatierung und zusammenführung aus getrennten Textboxen und Prüfung auf < 255
	if(strlen($B_hex_GET = dechex($_GET['LeerFarbe_B'])) < 2) $B_hex_GET = '0'.$B_hex_GET;
	if($_GET['LeerFarbe_B'] > 255) $B_hex_GET = 'FF';
	$temp = $_SESSION['Dokument']['Fuellung']['LeerFarbe'][0].$_SESSION['Dokument']['Fuellung']['LeerFarbe'][1]
											.$_SESSION['Dokument']['Fuellung']['LeerFarbe'][2].$_SESSION['Dokument']['Fuellung']['LeerFarbe'][3].$B_hex_GET;
	$_SESSION['Dokument']['Fuellung']['LeerFarbe'] = $temp;	
}






// Auflösung setzen
if($_GET['Klassen_Aufloesung']) {	$_SESSION['Dokument']['Klassen']['Aufloesung'] = $_GET['Klassen_Aufloesung']/10; }
// ... noch POST ... if($_POST['Klassen_Aufloesung']) {	$_SESSION['Dokument']['Klassen']['Aufloesung'] = strtr($_POST['Klassen_Aufloesung'],",","."); }
if(!$_SESSION['Dokument']['Klassen']['Aufloesung']) {  $_SESSION['Dokument']['Klassen']['Aufloesung'] = 1; } // Standard-Wert
if($_SESSION['Dokument']['Klassen']['Aufloesung'] < 0.2) {	$_SESSION['Dokument']['Klassen']['Aufloesung'] = 0.2; }
if($_SESSION['Dokument']['Klassen']['Aufloesung'] > 3) {  $_SESSION['Dokument']['Klassen']['Aufloesung'] = 3; }


// Klassen neu berechnen (ausgelagert)
include('svg_klassenbildung.php');




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
<div style="border: #999999 solid 1px; padding:10px; ">
  
        <table style="width:800px; border:0px; border-collapse:collapse;">
            <form action="svg_zeichenvorschrift.php" method="post">
                <tr>
                  <td valign="top" class="">Lokales oder <br />
                    deutschlandweites Wertespektrum<br />
                  <br /></td>
                  <td colspan="2" valign="top" class=""><input type="checkbox" name="indikator_lokal" id="indikator_lokal" <?php if($_SESSION['Dokument']['indikator_lokal']=='1') echo " checked "; ?> />
                    Lokale Werte verwenden 
                      <br />
                      <span class="Text_10px">(wird erst bei erneuter Anzeige der Karte angepasst)</span>
                      <input type="hidden" name="Lokale_Werte_gesendet" id="Lokale_Werte_gesendet"  value="gesendet" /></td>
                </tr>
                <tr>
                  <td width="186" valign="top" class="grauer_hintergrund">Farbdarstellung (Typ)<br />
                  <br />                  <br /></td>
                  <td colspan="2" valign="top" class="grauer_hintergrund">
                  	<input name="Typ" type="radio" value="Klassifizierte Farbreihe" <?php if($_SESSION['Dokument']['Fuellung']['Typ'] == "Klassifizierte Farbreihe") echo "checked"; ?> /> 
                  	Klassifizierte Farbreihe
                  (automatische Häufigkeitsverteilung)<br />
                  <br />
                  <input name="Typ" type="radio" value="Farbbereich" <?php if($_SESSION['Dokument']['Fuellung']['Typ'] == "Farbbereich") echo "checked"; ?> />

                  Kontinuierlicher Farbbereich von Min. - Max.<br /></td>
                </tr>                
                                
              <tr>
                  <td height="69" class="grauer_hintergrund">
                    <br />
                    <br />

                    <span class="button_standard_abschicken_a" style="background:#BAD380;"><a href="svg_html.php" target="_self">&nbsp;&nbsp;&nbsp;&lt;= Zurück zur Karte&nbsp;&nbsp;&nbsp;</a></span>				</td>
                <td colspan="2" class="grauer_hintergrund"><input name="input" type="submit" value="gew&auml;hlte Methode f&uuml;r Farbdarstellung verwenden!" class="button_blau_abschicken" />
                  <br />
                  <br /></td>
                </tr>
          
                <tr>
                  <td valign="top"><br />
                  Werteverteilung <br />
                  (nur zur Information)<br /></td>
                  <td colspan="2" valign="top"> 
                  	<br />
					<span style="font-size:12px;">Maximum der Verteilung = <?php echo $_SESSION['Temp']['i_Verteilung']['Max']; ?> Gebietseinheiten im Bereich von <?php echo $_SESSION['Temp']['i_Verteilung']['Max_Prozentzahl']; ?>% des Wertebereiches</span>
                    <div style="clear:both">
						<?php 
                        // Anzeige der Verteilung an sich (Säulen)
                          for($i=0 ; $i <= 100 ; $i++)
                          {
                              $h = ($_SESSION['Temp']['i_Verteilung'][$i]/$_SESSION['Temp']['i_Verteilung']['NormTeiler'])*4; // im Viewer wird auf Teiler 30 gerechnet (hier Erhöhung optisch besser)
                              $h_fuell = 120-$h; // Durch Erhöhung auf y+e, statt y, bezogen
                                                        
                              ?>
                        		<div title="<?php echo round($_SESSION['Temp']['i_Verteilung'][$i],0); ?> Objekte bei <?php echo round($i,0); ?>% des Wertebereiches" style="width:4px; float:left; background:#<?php 
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
                                ?>">
                                <div style=" background:#000000; width:4px; height:<?php echo $h_fuell; ?>px; opacity:0;"></div>
                                <div style=" background:#FFFFFF; border-top: #333333 solid 1px; width:4px; height:<?php echo $h; ?>px; opacity:0.5;"></div>
                        </div>
                              <?php 
                          }
                          ?>
                          <br />
                        <div style="position: relative; clear:both;">
                              <div style=" position:absolute; top:0px; left:0px;"><?php 
							  							echo round($_SESSION['Dokument']['Fuellung']['Indikator_Wert_min'],2)." ".utf8_encode($_SESSION['Dokument']['Fuellung']['Indikator_Einheit']); ?></div>
                              <div style=" position:absolute; top:0px; right:100px;"><?php 
							  							echo round($_SESSION['Dokument']['Fuellung']['Indikator_Wert_max'],2)." ".utf8_encode($_SESSION['Dokument']['Fuellung']['Indikator_Einheit']); ?></div> 
                        </div>
                    </div>
                    <br />                    </td>
                </tr>
      			<tr>
                  <td valign="top"><br />
                  Anzahl der Klassen<span class="Text_10px"></span></td>
                  <td colspan="2" valign="top">                    <br />
                      <table width="100%" style="border:0px;">
                        <tr>
                          <td width="33" valign="top">Max.</td>
                          <td width="27" rowspan="2" valign="top">
                                  <div class="reglerRahmen" style=" height:60px;">
                                    <?php 
                                        for($i=30 ; $i>=1 ; $i--)
                                        {
                                            ?>
                                            <div <?php 
                                            if($i == ($_SESSION['Dokument']['Klassen']['Aufloesung']*10)) { echo 'class="reglerTeil_aktiv"'; }else{ echo 'class="reglerTeil"'; } 
                                            ?> ><a href="svg_zeichenvorschrift.php?Klassen_Aufloesung=<?php echo $i; ?>" style="display:block;">
                                              <div style="margin-left:3px; margin-right:3px; background:#666666;">&nbsp;</div>
                                            </a></div>
                                            <?php 
                                        }
                                        ?>
                                    </div>                          </td>
                          <td width="394" rowspan="2" valign="baseline">
                          
                          <div style="clear:both;">
						  <?php 
						  foreach($_SESSION['Temp']['Klasse'] as $Klassenset)
						  {
						  		?><div title="bis <?php echo $Klassenset['Wert_Obergrenze'].$_SESSION['Dokument']['Indikator_Einheit']; 
											?>" style="float:left; width:20px; height:20px; margin:3px; background-color:#<?php echo $Klassenset['Farbwert']; ?>">
                                  </div>
								<?php 
								$KZahl++;
						  }
						  ?>
          					</div> 
                          <div  style="clear:both;">
							<?php echo $KZahl; ?> Klassen abgeleitet                            </div>                          </td>
                        </tr>
                        <tr>
                          <td valign="bottom">Min.</td>
                        </tr>
                      </table>                    </td>
                </tr>
                <tr>
                  <td style="height:5px;" valign="top">&nbsp;</td>
                  <td style="height:5px;" colspan="2">&nbsp;</td>
                </tr>

                <tr>
                  <td valign="top"><div style="height:0px; border-bottom:1px solid #999999; margin-top:7px; margin-bottom:7px;"></div></td>
                  <td width="17" valign="top"><div style="height:0px; border-bottom:1px solid #999999; margin-top:7px; margin-bottom:7px;"></div></td>
                  <td valign="top">&nbsp;</td>
                </tr>
              <tr>
                <td valign="top"><br />
                Farbwert für Werte-Maximum (0-255):</td>
                <td width="17" rowspan="2" valign="top"><div style="border:solid #666666 1px; width:15px; height:200px; overflow:hidden;">
                  <?php 				
                        for($i=0; $i <= 100 ; $i++)
                        { 
                            
                            
                            
                            // Runden, in Hex-Wert umwandeln und bei einstelligen Werten eine 0 voranstellen und keine Negativen Werte zulassen <= nicht möglich!!!
                            if(strlen($R_hex = dechex(round(abs($Ri=$R_max_dezimal-($i*$R)),0))) < 2) $R_hex = '0'.$R_hex;
                            if(strlen($G_hex = dechex(round(abs($Gi=$G_max_dezimal-($i*$G)),0))) < 2) $G_hex = '0'.$G_hex;
                            if(strlen($B_hex = dechex(round(abs($Bi=$B_max_dezimal-($i*$B)),0))) < 2) $B_hex = '0'.$B_hex;
                            echo '<div style="height:2px; background:#'.$R_hex.$G_hex.$B_hex.';"></div>';
                        }			
                        ?>
                </div></td>
                <td width="267" valign="top">
                            <table style="border:0px; width:400px;">
<tr>
                                    <td width="67" valign="top">Rot:
                                      <input name="Farbwert_max_R" type="text" maxlength="3" style="width:30px;" value="<?php echo hexdec(substr($_SESSION['Dokument']['Fuellung']['Farbwert_max'],0,2)); ?>" />                                    </td>
<td width="47" valign="top">
<div class="reglerRahmen" style=" height:53px;">
                                            <?php 
                                            for($i=260 ; $i>=0 ; $i = $i-10)
                                            {
                                                ?>
                                                <div 
                                                    <?php 
                                                    if($i == hexdec($t=$_SESSION['Dokument']['Fuellung']['Farbwert_max'][0].$_SESSION['Dokument']['Fuellung']['Farbwert_max'][1])) 
                                                    { echo 'class="reglerTeil_aktiv"'; }else{ echo 'class="reglerTeil"'; } 
                                                    ?> >
                                                        <a href="svg_zeichenvorschrift.php?Farbwert_max_R=<?php echo $i; ?>" style="display:block;">
                                                        	<div style="margin-left:3px; margin-right:3px; background:#<?php echo $xf = round((89-((1/$i)*800))+10,0); ?>0000;">&nbsp;</div>
                                                        </a>                                                </div>
                                                <?php 
                                            }
                                            ?>
                                        </div>                                     </td>
              <td width="77" valign="top">Grün:
              <input name="Farbwert_max_G" type="text" maxlength="3" style="width:30px;" value="<?php echo hexdec(substr($_SESSION['Dokument']['Fuellung']['Farbwert_max'],2,2)); ?>" /></td>
<td width="47" valign="top">                                 
<div class="reglerRahmen" style=" height:53px;">
                                            <?php 
                                            for($i=260 ; $i>=0 ; $i = $i-10)
                                            {
                                                ?>
                                                <div 
                                                    <?php 
                                                    if($i == hexdec($t=$_SESSION['Dokument']['Fuellung']['Farbwert_max'][2].$_SESSION['Dokument']['Fuellung']['Farbwert_max'][3])) 
                                                    { echo 'class="reglerTeil_aktiv"'; }else{ echo 'class="reglerTeil"'; } 
                                                    ?> >
                                                        <a href="svg_zeichenvorschrift.php?Farbwert_max_G=<?php echo $i; ?>" style="display:block;">
                                                        	<div style="margin-left:3px; margin-right:3px; background:#00<?php echo $xf = round((89-((1/$i)*800))+10,0); ?>00;">&nbsp;</div>
                                                        </a>                                                </div>
                                                <?php 
                                            }
                                            ?>
                                        </div>                                    </td>
              <td width="74" valign="top">Blau:
              <input name="Farbwert_max_B" type="text" maxlength="3" style="width:30px;" value="<?php echo hexdec(substr($_SESSION['Dokument']['Fuellung']['Farbwert_max'],4,2)); ?>" /></td>
<td width="60" valign="top">                                 
<div class="reglerRahmen" style=" height:53px;">
                                            <?php 
                                            for($i=260 ; $i>=0 ; $i = $i-10)
                                            {
                                                ?>
                                                <div 
                                                    <?php 
                                                    if($i == hexdec($t=$_SESSION['Dokument']['Fuellung']['Farbwert_max'][4].$_SESSION['Dokument']['Fuellung']['Farbwert_max'][5])) 
                                                    { echo 'class="reglerTeil_aktiv"'; }else{ echo 'class="reglerTeil"'; } 
                                                    ?> >
                                                        <a href="svg_zeichenvorschrift.php?Farbwert_max_B=<?php echo $i; ?>" style="display:block;">
                                                        	<div style="margin-left:3px; margin-right:3px; background:#0000<?php echo $xf = round((89-((1/$i)*800))+10,0); ?>;">&nbsp;</div>
                                                        </a>                                                </div>
                                                <?php 
                                            }
                                            ?>
                                        </div>                                      </td>
                              </tr>
                                </table>                </td>
              </tr>
              <tr>
                <td valign="bottom">Farbwert für Werte-Minimum (0-255):</td>
                <td valign="bottom"><br />                  <table style="border:0px; width:400px;">
                    <tr>
                      <td width="67" valign="bottom">Rot:
                        <input name="Farbwert_min_R" type="text" maxlength="3" style="width:30px;" value="<?php echo hexdec(substr($_SESSION['Dokument']['Fuellung']['Farbwert_min'],0,2)); ?>" />                      </td>
                      <td width="47" valign="bottom"><div class="reglerRahmen" style=" height:53px;">
                          <?php 
                                            for($i=260 ; $i>=0 ; $i = $i-10)
                                            {
                                                ?>
                        <div 
                                                    <?php 
                                                    if($i == hexdec($t=$_SESSION['Dokument']['Fuellung']['Farbwert_min'][0].$_SESSION['Dokument']['Fuellung']['Farbwert_min'][1])) 
                                                    { echo 'class="reglerTeil_aktiv"'; }else{ echo 'class="reglerTeil"'; } 
                                                    ?> > <a href="svg_zeichenvorschrift.php?Farbwert_min_R=<?php echo $i; ?>" style="display:block;">
                            <div style="margin-left:3px; margin-right:3px; background:#<?php echo $xf = round((89-((1/$i)*800))+10,0); ?>0000;">&nbsp;</div>
                        </a> </div>
                        <?php 
                                            }
                                            ?>
                      </div></td>
                      <td width="77" valign="bottom">Grün:
                        <input name="Farbwert_min_G" type="text" maxlength="3" style="width:30px;" value="<?php echo hexdec(substr($_SESSION['Dokument']['Fuellung']['Farbwert_min'],2,2)); ?>" /></td>
                      <td width="47" valign="bottom"><div class="reglerRahmen" style=" height:53px;">
                          <?php 
                                            for($i=260 ; $i>=0 ; $i = $i-10)
                                            {
                                                ?>
                        <div 
                                                    <?php 
                                                    if($i == hexdec($t=$_SESSION['Dokument']['Fuellung']['Farbwert_min'][2].$_SESSION['Dokument']['Fuellung']['Farbwert_min'][3])) 
                                                    { echo 'class="reglerTeil_aktiv"'; }else{ echo 'class="reglerTeil"'; } 
                                                    ?> > <a href="svg_zeichenvorschrift.php?Farbwert_min_G=<?php echo $i; ?>" style="display:block;">
                            <div style="margin-left:3px; margin-right:3px; background:#00<?php echo $xf = round((89-((1/$i)*800))+10,0); ?>00;">&nbsp;</div>
                        </a> </div>
                        <?php 
                                            }
                                            ?>
                      </div></td>
                      <td width="74" valign="bottom">Blau:
                        <input name="Farbwert_min_B" type="text" maxlength="3" style="width:30px;" value="<?php echo hexdec(substr($_SESSION['Dokument']['Fuellung']['Farbwert_min'],4,2)); ?>" /></td>
                      <td width="60" valign="bottom"><div class="reglerRahmen" style=" height:53px;">
                          <?php 
                                            for($i=260 ; $i>=0 ; $i = $i-10)
                                            {
                                                ?>
                        <div 
                                                    <?php 
                                                    if($i == hexdec($t=$_SESSION['Dokument']['Fuellung']['Farbwert_min'][4].$_SESSION['Dokument']['Fuellung']['Farbwert_min'][5])) 
                                                    { echo 'class="reglerTeil_aktiv"'; }else{ echo 'class="reglerTeil"'; } 
                                                    ?> > <a href="svg_zeichenvorschrift.php?Farbwert_min_B=<?php echo $i; ?>" style="display:block;">
                            <div style="margin-left:3px; margin-right:3px; background:#0000<?php echo $xf = round((89-((1/$i)*800))+10,0); ?>;">&nbsp;</div>
                        </a> </div>
                        <?php 
                                            }
                                            ?>
                      </div></td>
                    </tr>
                  </table>                </td>
              </tr>
              <tr>
                <td>&nbsp;</td>
                <td colspan="2"><br />
                <span class="button_standard_abschicken_a"><a href="svg_zeichenvorschrift.php?tausch=ja" target="_self">&nbsp;&nbsp;&nbsp;Minimum &amp; Maximum vertauschen&nbsp;&nbsp;&nbsp;</a></span></td>
              </tr>
              <tr>
                <td><br />
                  <br />
                Farbwert für fehlende Daten (0-255):</td>
                <td><br />
                <br />
                <div style="border:solid #666666 1px; width:15px; height:15px; overflow:hidden; background-color:#<?php echo $_SESSION['Dokument']['Fuellung']['LeerFarbe']; ?>;"></div>        </td>
                <td>
                    <br />
                    <table style="border:0px; width:400px;">
                      <tr>
                        <td width="67" valign="bottom">Rot:
                          <input name="LeerFarbe_R" type="text" id="LeerFarbe_R" style="width:30px;" value="<?php echo hexdec(substr($_SESSION['Dokument']['Fuellung']['LeerFarbe'],0,2)); ?>" maxlength="3" />                        </td>
                        <td width="47" valign="bottom"><div class="reglerRahmen" style=" height:53px;">
                            <?php 
                                            for($i=260 ; $i>=0 ; $i = $i-10)
                                            {
                                                ?>
                          <div 
                                                    <?php 
                                                    if($i == hexdec($t=$_SESSION['Dokument']['Fuellung']['LeerFarbe'][0].$_SESSION['Dokument']['Fuellung']['LeerFarbe'][1])) 
                                                    { echo 'class="reglerTeil_aktiv"'; }else{ echo 'class="reglerTeil"'; } 
                                                    ?> > <a href="svg_zeichenvorschrift.php?LeerFarbe_R=<?php echo $i; ?>" style="display:block;">
                              <div style="margin-left:3px; margin-right:3px; background:#<?php echo $xf = round((89-((1/$i)*800))+10,0); ?>0000;">&nbsp;</div>
                          </a> </div>
                          <?php 
                                            }
                                            ?>
                        </div></td>
                        <td width="77" valign="bottom">Grün:
                          <input name="LeerFarbe_G" type="text" id="LeerFarbe_G" style="width:30px;" value="<?php echo hexdec(substr($_SESSION['Dokument']['Fuellung']['LeerFarbe'],2,2)); ?>" maxlength="3" /></td>
                        <td width="47" valign="bottom"><div class="reglerRahmen" style=" height:53px;">
                            <?php 
                                            for($i=260 ; $i>=0 ; $i = $i-10)
                                            {
                                                ?>
                          <div 
                                                    <?php 
                                                    if($i == hexdec($t=$_SESSION['Dokument']['Fuellung']['LeerFarbe'][2].$_SESSION['Dokument']['Fuellung']['LeerFarbe'][3])) 
                                                    { echo 'class="reglerTeil_aktiv"'; }else{ echo 'class="reglerTeil"'; } 
                                                    ?> > <a href="svg_zeichenvorschrift.php?LeerFarbe_G=<?php echo $i; ?>" style="display:block;">
                              <div style="margin-left:3px; margin-right:3px; background:#00<?php echo $xf = round((89-((1/$i)*800))+10,0); ?>00;">&nbsp;</div>
                          </a> </div>
                          <?php 
                                            }
                                            ?>
                        </div></td>
                        <td width="74" valign="bottom">Blau:
                          <input name="LeerFarbe_B" type="text" id="LeerFarbe_B" style="width:30px;" value="<?php echo hexdec(substr($_SESSION['Dokument']['Fuellung']['LeerFarbe'],4,2)); ?>" maxlength="3" /></td>
                        <td width="60" valign="bottom"><div class="reglerRahmen" style=" height:53px;">
                            <?php 
                                            for($i=260 ; $i>=0 ; $i = $i-10)
                                            {
                                                ?>
                          <div 
                                                    <?php 
                                                    if($i == hexdec($t=$_SESSION['Dokument']['Fuellung']['LeerFarbe'][4].$_SESSION['Dokument']['Fuellung']['LeerFarbe'][5])) 
                                                    { echo 'class="reglerTeil_aktiv"'; }else{ echo 'class="reglerTeil"'; } 
                                                    ?> > <a href="svg_zeichenvorschrift.php?LeerFarbe_B=<?php echo $i; ?>" style="display:block;">
                              <div style="margin-left:3px; margin-right:3px; background:#0000<?php echo $xf = round((89-((1/$i)*800))+10,0); ?>;">&nbsp;</div>
                          </a> </div>
                          <?php 
                                            }
                                            ?>
                        </div></td>
                      </tr>
                    </table>                </td>
              </tr>
              <tr>
                <td height="67"><br />
                  <br />
                 
                    <!--<span class="button_standard_abschicken_a" style="background:#BAD380;"><a href="svg_html.php" target="_self">&nbsp;&nbsp;&nbsp;&lt;= Zurück zur Karte&nbsp;&nbsp;&nbsp;</a></span> --> 
                    <div style="float:left; margin-right:10px; padding-top:15px;">
                    	<a href="svg_html.php" target="_self"><img src="icons_viewer/back.png" width="52" height="44" alt="Zur&uuml;ck" /><br />&nbsp;zur&uuml;ck</a>
                    </div>               
             </td>
			<td colspan="2"><input name="input" type="submit" value="numerische Eingaben best&auml;tigen!" class="button_blau_abschicken" />
                <input name="Aktion" type="hidden" value="ZV_uebernahme" /></td>
              </tr>
          </form>
        </table>
        
    
        <br />
 </div>
<br />
<br />

</body>
</html>
