<?php 
session_start();
include("includes_classes/verbindung_mysqli.php");

//if($_POST['indikator_lokal']) $_SESSION['Dokument']['indikator_lokal'] = '1'; // Datenbasis lokal/global
//if($_POST['Lokale_Werte_gesendet'] and !$_POST['indikator_lokal']) $_SESSION['Dokument']['indikator_lokal'] = '0';

// Tauschen von Min-Max
if($_GET['tausch']=="ja")
{
	$temp_max = $_SESSION['Dokument']['Fuellung']['Farbwert_max'];
	$temp_min = $_SESSION['Dokument']['Fuellung']['Farbwert_min'];
	
	$_SESSION['Dokument']['Fuellung']['Farbwert_min'] = $temp_max;
	$_SESSION['Dokument']['Fuellung']['Farbwert_max'] = $temp_min;
}

// Daten aus Farbfeldern

if($_GET['color_min'])	$_SESSION['Dokument']['Fuellung']['Farbwert_min'] = $_GET['color_min'];
if($_GET['color_max'])	$_SESSION['Dokument']['Fuellung']['Farbwert_max'] = $_GET['color_max'];
if($_GET['leerfarbe'])	$_SESSION['Dokument']['Fuellung']['LeerFarbe'] = $_GET['leerfarbe'];
if($_GET['textfarbe_labels'])	$_SESSION['Dokument']['Textfarbe_Labels'] = $_GET['textfarbe_labels'];
if($_GET['Strichfarbe'])	$_SESSION['Dokument']['Strichfarbe'] = $_GET['Strichfarbe'];
// Zusatzebenen
if($_GET['Strichfarbe_ZE_VG'])	$_SESSION['Dokument']['Strichfarbe_ZE_VG'] = $_GET['Strichfarbe_ZE_VG'];
if($_GET['Strichfarbe_ZE_GEW'])	$_SESSION['Dokument']['Strichfarbe_GEW'] = $_GET['Strichfarbe_ZE_GEW'];
if($_GET['Strichfarbe_ZE_db'])	$_SESSION['Dokument']['Strichfarbe_db'] = $_GET['Strichfarbe_ZE_db'];
if($_GET['Strichfarbe_ZE_BAB'])	$_SESSION['Dokument']['Strichfarbe_BAB'] = $_GET['Strichfarbe_ZE_BAB']; // BAB
	// BAB als korrekte Signatur oder nicht
	if($_GET['Strichfarbe_BAB_Signatur'])
	{
		if($_SESSION['Dokument']['Strichfarbe_BAB_Signatur'] == '1')
		{
			$_SESSION['Dokument']['Strichfarbe_BAB_Signatur'] = '0';
		}
		else
		{
			$_SESSION['Dokument']['Strichfarbe_BAB_Signatur'] = '1';
		}
	}


// Daten aus numerischen Feldern
if($_POST['Aktion']=="ZV_uebernahme")
{
	$_SESSION['Dokument']['Fuellung']['Farbwert_min'] = $_POST['Farbwert_min'];	
	$_SESSION['Dokument']['Fuellung']['Farbwert_max'] = $_POST['Farbwert_max'];
	$_SESSION['Dokument']['Fuellung']['LeerFarbe'] =$_POST['LeerFarbe'];
	$_SESSION['Dokument']['Textfarbe_Labels'] = $_POST['Textfarbe_Labels'];
	$_SESSION['Dokument']['Strichfarbe'] = $_POST['Strichfarbe'];
	$_SESSION['Dokument']['Strichfarbe_ZE_VG'] = $_POST['Strichfarbe_ZE_VG'];
	$_SESSION['Dokument']['Strichfarbe_GEW'] = $_POST['Strichfarbe_ZE_GEW'];
	$_SESSION['Dokument']['Strichfarbe_BAB'] = $_POST['Strichfarbe_ZE_BAB'];
	$_SESSION['Dokument']['Strichfarbe_db'] = $_POST['Strichfarbe_ZE_db'];
}


	// Farbe für BAB-Kontur hier immer ermitteln (es gibt dafür keine Vorgabe in der DB => berechnet wird immer die Hälfte des Farbwertes der BAB)
	// Achtung: Wird in svg_svg.php ebenfall nochmals in dieser Weise errechnet! ... falls hier umgestellt werden soll, auch da umstellen!					 
	if(strlen($BAB_R = dechex(round(hexdec(substr($_SESSION['Dokument']['Strichfarbe_BAB'],0,2))/2))) < 2) $BAB_R = $BAB_R.'0'; 
	if(strlen($BAB_G = dechex(round(hexdec(substr($_SESSION['Dokument']['Strichfarbe_BAB'],2,2))/2))) < 2) $BAB_G = $BAB_G.'0'; 
	if(strlen($BAB_B = dechex(round(hexdec(substr($_SESSION['Dokument']['Strichfarbe_BAB'],4,2))/2))) < 2) $BAB_B = $BAB_B.'0'; 
	// Zweistelligkeit beachten!!!
	$_SESSION['Dokument']['Strichfarbe_BAB_Kontur'] = $BAB_R.$BAB_G.$BAB_B;


// Setzen nicht gefüllter Variablen, die auch an anderer Stelle nicht gefüllt werden
 if(!$_SESSION['Dokument']['Strichfarbe_db']) $_SESSION['Dokument']['Strichfarbe_db'] = "555555"; // Standardwert für Ferrnbahnnetz setzen



// Klassen neu berechnen (ausgelagert)
include('svg_klassenbildung.php');

// Zeichenvorschrift auf Standard zurücksetzen
if($_POST['Zuruecksetzen']=='1')
{
		include("includes_classes/verbindung_mysqli.php");
		
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
				$_SESSION['Dokument']['Fuellung']['Typ'] = mysqli_result($Ergebnis_ZV,0,'TYP_FUELLUNG');
				$_SESSION['Dokument']['Fuellung']['Farbwert_min'] = mysqli_result($Ergebnis_ZV,0,'FARBWERT_MIN');
				$_SESSION['Dokument']['Fuellung']['Farbwert_max'] = mysqli_result($Ergebnis_ZV,0,'FARBWERT_MAX');
				$_SESSION['Dokument']['Fuellung']['LeerFarbe'] = mysqli_result($Ergebnis_ZV,0,'FARBWERT_LEER');
				$_SESSION['Dokument']['Strichfarbe'] = mysqli_result($Ergebnis_ZV,0,'FARBWERT_KONTUR');
				$_SESSION['Dokument']['Strichfarbe_MouseOver'] = mysqli_result($Ergebnis_ZV,0,'FARBWERT_MOUSEOVER');
				$_SESSION['Dokument']['Textfarbe_Labels'] = mysqli_result($Ergebnis_ZV,0,'FARBWERT_TEXT');
				$_SESSION['Dokument']['Klassen']['Aufloesung'] = mysqli_result($Ergebnis_ZV,0,'KLASSEN_AUFLOESUNG');
				
				$_SESSION['Dokument']['Strichfarbe_ZE_VG'] = "FFFFFF";
				$_SESSION['Dokument']['Strichfarbe_GEW'] =  "000099"; 
					$_SESSION['Dokument']['Strichfarbe_db'] =  "555555";
				$_SESSION['Dokument']['Strichfarbe_BAB'] =  "EEEE00";
				
			}
		}
		
		// Standard ZV
		if(!$_SESSION['Dokument']['Fuellung']['Indikator'] or $Indikator_ZV_nicht_definiert)
		{
			// Standard-ZV mit ID=1 verwenden
			$SQL_ZV = "SELECT * FROM m_zeichenvorschrift WHERE ID_ZEICHENVORSCHRIFT='1'";
			$Ergebnis_ZV = mysqli_query($Verbindung, $SQL_ZV);
					
			$_SESSION['Dokument']['Fuellung']['Typ'] = mysqli_result($Ergebnis_ZV,0,'TYP_FUELLUNG');
			$_SESSION['Dokument']['Fuellung']['Farbwert_min'] = mysqli_result($Ergebnis_ZV,0,'FARBWERT_MIN');
			$_SESSION['Dokument']['Fuellung']['Farbwert_max'] = mysqli_result($Ergebnis_ZV,0,'FARBWERT_MAX');
			$_SESSION['Dokument']['Fuellung']['LeerFarbe'] = mysqli_result($Ergebnis_ZV,0,'FARBWERT_LEER');
			$_SESSION['Dokument']['Strichfarbe'] = mysqli_result($Ergebnis_ZV,0,'FARBWERT_KONTUR');
			$_SESSION['Dokument']['Strichfarbe_MouseOver'] = mysqli_result($Ergebnis_ZV,0,'FARBWERT_MOUSEOVER');
			$_SESSION['Dokument']['Textfarbe_Labels'] = mysqli_result($Ergebnis_ZV,0,'FARBWERT_TEXT');
			$_SESSION['Dokument']['Klassen']['Aufloesung'] = mysqli_result($Ergebnis_ZV,0,'KLASSEN_AUFLOESUNG');
			$_SESSION['Dokument']['Strichfarbe_ZE_VG'] = "FFFFFF";
			$_SESSION['Dokument']['Strichfarbe_GEW'] =  "000099"; 
			$_SESSION['Dokument']['Strichfarbe_db'] =  "555555";
			$_SESSION['Dokument']['Strichfarbe_BAB'] =  "EEEE00";
			
		}
		
		// Klassen neu berechnen 
		include('svg_klassenbildung.php');
	

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
<div style="border: #999999 solid 1px; padding:10px; "><strong>Farbwerte für die Kartendarstellung anpassen</strong><br />
  <br />
  
        <table style="width:800px; border:0px; border-collapse:collapse;">
            <form action="svg_zeichenvorschrift_farbe.php" method="post">
                <tr>
                  <td valign="top">&nbsp;</td>
                  <td colspan="2" valign="top">                    Sie können hier jeweils die Farbe für den Minimal- und den Maximalwert w&auml;hlen.<br />
                    Die dazwischen liegenden Farben werden automatisch berechnet.<br />
                    <br /></td>
                </tr>
              <tr>
                <td width="172" valign="top">                Farbe 
                für Maximum:</td>
                <td width="70" rowspan="2" valign="top"><div style="border:solid #666666 1px; width:15px; height:200px; overflow:hidden;">
                  <?php 				
                        for($i=0; $i <= 100 ; $i++)
                        { 
                            
                            
                            
                            // Runden, in Hex-Wert umwandeln und bei einstelligen Werten eine 0 voranstellen und keine Negativen Werte zulassen <= nicht möglich!!!
                            if(strlen($R_hex = dechex(round(abs($Ri=$R_max_dezimal-(($i)*$R)),0))) < 2) $R_hex = '0'.$R_hex;
                            if(strlen($G_hex = dechex(round(abs($Gi=$G_max_dezimal-(($i)*$G)),0))) < 2) $G_hex = '0'.$G_hex;
                            if(strlen($B_hex = dechex(round(abs($Bi=$B_max_dezimal-(($i)*$B)),0))) < 2) $B_hex = '0'.$B_hex;
                            echo '<div style="height:2px; background:#'.$R_hex.$G_hex.$B_hex.';"></div>';
                        }			
                        ?>
                </div></td>
                <td width="542" align="left" valign="top">
          <map name="colmap_max" id="id">
          <area shape="rect" coords="1,1,7,10" href="svg_zeichenvorschrift_farbe.php?color_max=00FF00" target="_self" />
          <area shape="rect" coords="9,1,15,10" href="svg_zeichenvorschrift_farbe.php?color_max=00FF33" target="_self" />
          <area shape="rect" coords="17,1,23,10" href="svg_zeichenvorschrift_farbe.php?color_max=00FF66" target="_self" />
          <area shape="rect" coords="25,1,31,10" href="svg_zeichenvorschrift_farbe.php?color_max=00FF99" target="_self" />
          <area shape="rect" coords="33,1,39,10" href="svg_zeichenvorschrift_farbe.php?color_max=00FFCC" target="_self" />
          <area shape="rect" coords="41,1,47,10" href="svg_zeichenvorschrift_farbe.php?color_max=00FFFF" target="_self" />

          <area shape="rect" coords="49,1,55,10" href="svg_zeichenvorschrift_farbe.php?color_max=33FF00" target="_self" />
          <area shape="rect" coords="57,1,63,10" href="svg_zeichenvorschrift_farbe.php?color_max=33FF33" target="_self" />
          <area shape="rect" coords="65,1,71,10" href="svg_zeichenvorschrift_farbe.php?color_max=33FF66" target="_self" />
          <area shape="rect" coords="73,1,79,10" href="svg_zeichenvorschrift_farbe.php?color_max=33FF99" target="_self" />
          <area shape="rect" coords="81,1,87,10" href="svg_zeichenvorschrift_farbe.php?color_max=33FFCC" target="_self" />
          <area shape="rect" coords="89,1,95,10" href="svg_zeichenvorschrift_farbe.php?color_max=33FFFF" target="_self" />
          <area shape="rect" coords="97,1,103,10" href="svg_zeichenvorschrift_farbe.php?color_max=66FF00" target="_self" />
          <area shape="rect" coords="105,1,111,10" href="svg_zeichenvorschrift_farbe.php?color_max=66FF33" target="_self" />
          <area shape="rect" coords="113,1,119,10" href="svg_zeichenvorschrift_farbe.php?color_max=66FF66" target="_self" />

          <area shape="rect" coords="121,1,127,10" href="svg_zeichenvorschrift_farbe.php?color_max=66FF99" target="_self" />
          <area shape="rect" coords="129,1,135,10" href="svg_zeichenvorschrift_farbe.php?color_max=66FFCC" target="_self" />
          <area shape="rect" coords="137,1,143,10" href="svg_zeichenvorschrift_farbe.php?color_max=66FFFF" target="_self" />
          <area shape="rect" coords="145,1,151,10" href="svg_zeichenvorschrift_farbe.php?color_max=99FF00" target="_self" />
          <area shape="rect" coords="153,1,159,10" href="svg_zeichenvorschrift_farbe.php?color_max=99FF33" target="_self" />
          <area shape="rect" coords="161,1,167,10" href="svg_zeichenvorschrift_farbe.php?color_max=99FF66" target="_self" />
          <area shape="rect" coords="169,1,175,10" href="svg_zeichenvorschrift_farbe.php?color_max=99FF99" target="_self" />
          <area shape="rect" coords="177,1,183,10" href="svg_zeichenvorschrift_farbe.php?color_max=99FFCC" target="_self" />
          <area shape="rect" coords="185,1,191,10" href="svg_zeichenvorschrift_farbe.php?color_max=99FFFF" target="_self" />

          <area shape="rect" coords="193,1,199,10" href="svg_zeichenvorschrift_farbe.php?color_max=CCFF00" target="_self" />
          <area shape="rect" coords="201,1,207,10" href="svg_zeichenvorschrift_farbe.php?color_max=CCFF33" target="_self" />
          <area shape="rect" coords="209,1,215,10" href="svg_zeichenvorschrift_farbe.php?color_max=CCFF66" target="_self" />
          <area shape="rect" coords="217,1,223,10" href="svg_zeichenvorschrift_farbe.php?color_max=CCFF99" target="_self" />
          <area shape="rect" coords="225,1,231,10" href="svg_zeichenvorschrift_farbe.php?color_max=CCFFCC" target="_self" />
          <area shape="rect" coords="233,1,239,10" href="svg_zeichenvorschrift_farbe.php?color_max=CCFFFF" target="_self" />
          <area shape="rect" coords="241,1,247,10" href="svg_zeichenvorschrift_farbe.php?color_max=FFFF00" target="_self" />
          <area shape="rect" coords="249,1,255,10" href="svg_zeichenvorschrift_farbe.php?color_max=FFFF33" target="_self" />
          <area shape="rect" coords="257,1,263,10" href="svg_zeichenvorschrift_farbe.php?color_max=FFFF66" target="_self" />

          <area shape="rect" coords="265,1,271,10" href="svg_zeichenvorschrift_farbe.php?color_max=FFFF99" target="_self" />
          <area shape="rect" coords="273,1,279,10" href="svg_zeichenvorschrift_farbe.php?color_max=FFFFCC" target="_self" />
          <area shape="rect" coords="281,1,287,10" href="svg_zeichenvorschrift_farbe.php?color_max=FFFFFF" target="_self" />
          <area shape="rect" coords="1,12,7,21" href="svg_zeichenvorschrift_farbe.php?color_max=00CC00" target="_self" />
          <area shape="rect" coords="9,12,15,21" href="svg_zeichenvorschrift_farbe.php?color_max=00CC33" target="_self" />
          <area shape="rect" coords="17,12,23,21" href="svg_zeichenvorschrift_farbe.php?color_max=00CC66" target="_self" />
          <area shape="rect" coords="25,12,31,21" href="svg_zeichenvorschrift_farbe.php?color_max=00CC99" target="_self" />
          <area shape="rect" coords="33,12,39,21" href="svg_zeichenvorschrift_farbe.php?color_max=00CCCC" target="_self" />
          <area shape="rect" coords="41,12,47,21" href="svg_zeichenvorschrift_farbe.php?color_max=00CCFF" target="_self" />

          <area shape="rect" coords="49,12,55,21" href="svg_zeichenvorschrift_farbe.php?color_max=33CC00" target="_self" />
          <area shape="rect" coords="57,12,63,21" href="svg_zeichenvorschrift_farbe.php?color_max=33CC33" target="_self" />
          <area shape="rect" coords="65,12,71,21" href="svg_zeichenvorschrift_farbe.php?color_max=33CC66" target="_self" />
          <area shape="rect" coords="73,12,79,21" href="svg_zeichenvorschrift_farbe.php?color_max=33CC99" target="_self" />
          <area shape="rect" coords="81,12,87,21" href="svg_zeichenvorschrift_farbe.php?color_max=33CCCC" target="_self" />
          <area shape="rect" coords="89,12,95,21" href="svg_zeichenvorschrift_farbe.php?color_max=33CCFF" target="_self" />
          <area shape="rect" coords="97,12,103,21" href="svg_zeichenvorschrift_farbe.php?color_max=66CC00" target="_self" />
          <area shape="rect" coords="105,12,111,21" href="svg_zeichenvorschrift_farbe.php?color_max=66CC33" target="_self" />
          <area shape="rect" coords="113,12,119,21" href="svg_zeichenvorschrift_farbe.php?color_max=66CC66" target="_self" />

          <area shape="rect" coords="121,12,127,21" href="svg_zeichenvorschrift_farbe.php?color_max=66CC99" target="_self" />
          <area shape="rect" coords="129,12,135,21" href="svg_zeichenvorschrift_farbe.php?color_max=66CCCC" target="_self" />
          <area shape="rect" coords="137,12,143,21" href="svg_zeichenvorschrift_farbe.php?color_max=66CCFF" target="_self" />
          <area shape="rect" coords="145,12,151,21" href="svg_zeichenvorschrift_farbe.php?color_max=99CC00" target="_self" />
          <area shape="rect" coords="153,12,159,21" href="svg_zeichenvorschrift_farbe.php?color_max=99CC33" target="_self" />
          <area shape="rect" coords="161,12,167,21" href="svg_zeichenvorschrift_farbe.php?color_max=99CC66" target="_self" />
          <area shape="rect" coords="169,12,175,21" href="svg_zeichenvorschrift_farbe.php?color_max=99CC99" target="_self" />
          <area shape="rect" coords="177,12,183,21" href="svg_zeichenvorschrift_farbe.php?color_max=99CCCC" target="_self" />
          <area shape="rect" coords="185,12,191,21" href="svg_zeichenvorschrift_farbe.php?color_max=99CCFF" target="_self" />

          <area shape="rect" coords="193,12,199,21" href="svg_zeichenvorschrift_farbe.php?color_max=CCCC00" target="_self" />
          <area shape="rect" coords="201,12,207,21" href="svg_zeichenvorschrift_farbe.php?color_max=CCCC33" target="_self" />
          <area shape="rect" coords="209,12,215,21" href="svg_zeichenvorschrift_farbe.php?color_max=CCCC66" target="_self" />
          <area shape="rect" coords="217,12,223,21" href="svg_zeichenvorschrift_farbe.php?color_max=CCCC99" target="_self" />
          <area shape="rect" coords="225,12,231,21" href="svg_zeichenvorschrift_farbe.php?color_max=CCCCCC" target="_self" />
          <area shape="rect" coords="233,12,239,21" href="svg_zeichenvorschrift_farbe.php?color_max=CCCCFF" target="_self" />
          <area shape="rect" coords="241,12,247,21" href="svg_zeichenvorschrift_farbe.php?color_max=FFCC00" target="_self" />
          <area shape="rect" coords="249,12,255,21" href="svg_zeichenvorschrift_farbe.php?color_max=FFCC33" target="_self" />
          <area shape="rect" coords="257,12,263,21" href="svg_zeichenvorschrift_farbe.php?color_max=FFCC66" target="_self" />

          <area shape="rect" coords="265,12,271,21" href="svg_zeichenvorschrift_farbe.php?color_max=FFCC99" target="_self" />
          <area shape="rect" coords="273,12,279,21" href="svg_zeichenvorschrift_farbe.php?color_max=FFCCCC" target="_self" />
          <area shape="rect" coords="281,12,287,21" href="svg_zeichenvorschrift_farbe.php?color_max=FFCCFF" target="_self" />
          <area shape="rect" coords="1,23,7,32" href="svg_zeichenvorschrift_farbe.php?color_max=009900" target="_self" />
          <area shape="rect" coords="9,23,15,32" href="svg_zeichenvorschrift_farbe.php?color_max=009933" target="_self" />
          <area shape="rect" coords="17,23,23,32" href="svg_zeichenvorschrift_farbe.php?color_max=009966" target="_self" />
          <area shape="rect" coords="25,23,31,32" href="svg_zeichenvorschrift_farbe.php?color_max=009999" target="_self" />
          <area shape="rect" coords="33,23,39,32" href="svg_zeichenvorschrift_farbe.php?color_max=0099CC" target="_self" />
          <area shape="rect" coords="41,23,47,32" href="svg_zeichenvorschrift_farbe.php?color_max=0099FF" target="_self" />

          <area shape="rect" coords="49,23,55,32" href="svg_zeichenvorschrift_farbe.php?color_max=339900" target="_self" />
          <area shape="rect" coords="57,23,63,32" href="svg_zeichenvorschrift_farbe.php?color_max=339933" target="_self" />
          <area shape="rect" coords="65,23,71,32" href="svg_zeichenvorschrift_farbe.php?color_max=339966" target="_self" />
          <area shape="rect" coords="73,23,79,32" href="svg_zeichenvorschrift_farbe.php?color_max=339999" target="_self" />
          <area shape="rect" coords="81,23,87,32" href="svg_zeichenvorschrift_farbe.php?color_max=3399CC" target="_self" />
          <area shape="rect" coords="89,23,95,32" href="svg_zeichenvorschrift_farbe.php?color_max=3399FF" target="_self" />
          <area shape="rect" coords="97,23,103,32" href="svg_zeichenvorschrift_farbe.php?color_max=669900" target="_self" />
          <area shape="rect" coords="105,23,111,32" href="svg_zeichenvorschrift_farbe.php?color_max=669933" target="_self" />
          <area shape="rect" coords="113,23,119,32" href="svg_zeichenvorschrift_farbe.php?color_max=669966" target="_self" />

          <area shape="rect" coords="121,23,127,32" href="svg_zeichenvorschrift_farbe.php?color_max=669999" target="_self" />
          <area shape="rect" coords="129,23,135,32" href="svg_zeichenvorschrift_farbe.php?color_max=6699CC" target="_self" />
          <area shape="rect" coords="137,23,143,32" href="svg_zeichenvorschrift_farbe.php?color_max=6699FF" target="_self" />
          <area shape="rect" coords="145,23,151,32" href="svg_zeichenvorschrift_farbe.php?color_max=999900" target="_self" />
          <area shape="rect" coords="153,23,159,32" href="svg_zeichenvorschrift_farbe.php?color_max=999933" target="_self" />
          <area shape="rect" coords="161,23,167,32" href="svg_zeichenvorschrift_farbe.php?color_max=999966" target="_self" />
          <area shape="rect" coords="169,23,175,32" href="svg_zeichenvorschrift_farbe.php?color_max=999999" target="_self" />
          <area shape="rect" coords="177,23,183,32" href="svg_zeichenvorschrift_farbe.php?color_max=9999CC" target="_self" />
          <area shape="rect" coords="185,23,191,32" href="svg_zeichenvorschrift_farbe.php?color_max=9999FF" target="_self" />

          <area shape="rect" coords="193,23,199,32" href="svg_zeichenvorschrift_farbe.php?color_max=CC9900" target="_self" />
          <area shape="rect" coords="201,23,207,32" href="svg_zeichenvorschrift_farbe.php?color_max=CC9933" target="_self" />
          <area shape="rect" coords="209,23,215,32" href="svg_zeichenvorschrift_farbe.php?color_max=CC9966" target="_self" />
          <area shape="rect" coords="217,23,223,32" href="svg_zeichenvorschrift_farbe.php?color_max=CC9999" target="_self" />
          <area shape="rect" coords="225,23,231,32" href="svg_zeichenvorschrift_farbe.php?color_max=CC99CC" target="_self" />
          <area shape="rect" coords="233,23,239,32" href="svg_zeichenvorschrift_farbe.php?color_max=CC99FF" target="_self" />
          <area shape="rect" coords="241,23,247,32" href="svg_zeichenvorschrift_farbe.php?color_max=FF9900" target="_self" />
          <area shape="rect" coords="249,23,255,32" href="svg_zeichenvorschrift_farbe.php?color_max=FF9933" target="_self" />
          <area shape="rect" coords="257,23,263,32" href="svg_zeichenvorschrift_farbe.php?color_max=FF9966" target="_self" />

          <area shape="rect" coords="265,23,271,32" href="svg_zeichenvorschrift_farbe.php?color_max=FF9999" target="_self" />
          <area shape="rect" coords="273,23,279,32" href="svg_zeichenvorschrift_farbe.php?color_max=FF99CC" target="_self" />
          <area shape="rect" coords="281,23,287,32" href="svg_zeichenvorschrift_farbe.php?color_max=FF99FF" target="_self" />
          <area shape="rect" coords="1,34,7,43" href="svg_zeichenvorschrift_farbe.php?color_max=006600" target="_self" />
          <area shape="rect" coords="9,34,15,43" href="svg_zeichenvorschrift_farbe.php?color_max=006633" target="_self" />
          <area shape="rect" coords="17,34,23,43" href="svg_zeichenvorschrift_farbe.php?color_max=006666" target="_self" />
          <area shape="rect" coords="25,34,31,43" href="svg_zeichenvorschrift_farbe.php?color_max=006699" target="_self" />
          <area shape="rect" coords="33,34,39,43" href="svg_zeichenvorschrift_farbe.php?color_max=0066CC" target="_self" />
          <area shape="rect" coords="41,34,47,43" href="svg_zeichenvorschrift_farbe.php?color_max=0066FF" target="_self" />

          <area shape="rect" coords="49,34,55,43" href="svg_zeichenvorschrift_farbe.php?color_max=336600" target="_self" />
          <area shape="rect" coords="57,34,63,43" href="svg_zeichenvorschrift_farbe.php?color_max=336633" target="_self" />
          <area shape="rect" coords="65,34,71,43" href="svg_zeichenvorschrift_farbe.php?color_max=336666" target="_self" />
          <area shape="rect" coords="73,34,79,43" href="svg_zeichenvorschrift_farbe.php?color_max=336699" target="_self" />
          <area shape="rect" coords="81,34,87,43" href="svg_zeichenvorschrift_farbe.php?color_max=3366CC" target="_self" />
          <area shape="rect" coords="89,34,95,43" href="svg_zeichenvorschrift_farbe.php?color_max=3366FF" target="_self" />
          <area shape="rect" coords="97,34,103,43" href="svg_zeichenvorschrift_farbe.php?color_max=666600" target="_self" />
          <area shape="rect" coords="105,34,111,43" href="svg_zeichenvorschrift_farbe.php?color_max=666633" target="_self" />
          <area shape="rect" coords="113,34,119,43" href="svg_zeichenvorschrift_farbe.php?color_max=666666" target="_self" />

          <area shape="rect" coords="121,34,127,43" href="svg_zeichenvorschrift_farbe.php?color_max=666699" target="_self" />
          <area shape="rect" coords="129,34,135,43" href="svg_zeichenvorschrift_farbe.php?color_max=6666CC" target="_self" />
          <area shape="rect" coords="137,34,143,43" href="svg_zeichenvorschrift_farbe.php?color_max=6666FF" target="_self" />
          <area shape="rect" coords="145,34,151,43" href="svg_zeichenvorschrift_farbe.php?color_max=996600" target="_self" />
          <area shape="rect" coords="153,34,159,43" href="svg_zeichenvorschrift_farbe.php?color_max=996633" target="_self" />
          <area shape="rect" coords="161,34,167,43" href="svg_zeichenvorschrift_farbe.php?color_max=996666" target="_self" />
          <area shape="rect" coords="169,34,175,43" href="svg_zeichenvorschrift_farbe.php?color_max=996699" target="_self" />
          <area shape="rect" coords="177,34,183,43" href="svg_zeichenvorschrift_farbe.php?color_max=9966CC" target="_self" />
          <area shape="rect" coords="185,34,191,43" href="svg_zeichenvorschrift_farbe.php?color_max=9966FF" target="_self" />

          <area shape="rect" coords="193,34,199,43" href="svg_zeichenvorschrift_farbe.php?color_max=CC6600" target="_self" />
          <area shape="rect" coords="201,34,207,43" href="svg_zeichenvorschrift_farbe.php?color_max=CC6633" target="_self" />
          <area shape="rect" coords="209,34,215,43" href="svg_zeichenvorschrift_farbe.php?color_max=CC6666" target="_self" />
          <area shape="rect" coords="217,34,223,43" href="svg_zeichenvorschrift_farbe.php?color_max=CC6699" target="_self" />
          <area shape="rect" coords="225,34,231,43" href="svg_zeichenvorschrift_farbe.php?color_max=CC66CC" target="_self" />
          <area shape="rect" coords="233,34,239,43" href="svg_zeichenvorschrift_farbe.php?color_max=CC66FF" target="_self" />
          <area shape="rect" coords="241,34,247,43" href="svg_zeichenvorschrift_farbe.php?color_max=FF6600" target="_self" />
          <area shape="rect" coords="249,34,255,43" href="svg_zeichenvorschrift_farbe.php?color_max=FF6633" target="_self" />
          <area shape="rect" coords="257,34,263,43" href="svg_zeichenvorschrift_farbe.php?color_max=FF6666" target="_self" />

          <area shape="rect" coords="265,34,271,43" href="svg_zeichenvorschrift_farbe.php?color_max=FF6699" target="_self" />
          <area shape="rect" coords="273,34,279,43" href="svg_zeichenvorschrift_farbe.php?color_max=FF66CC" target="_self" />
          <area shape="rect" coords="281,34,287,43" href="svg_zeichenvorschrift_farbe.php?color_max=FF66FF" target="_self" />
          <area shape="rect" coords="1,45,7,54" href="svg_zeichenvorschrift_farbe.php?color_max=003300" target="_self" />
          <area shape="rect" coords="9,45,15,54" href="svg_zeichenvorschrift_farbe.php?color_max=003333" target="_self" />
          <area shape="rect" coords="17,45,23,54" href="svg_zeichenvorschrift_farbe.php?color_max=003366" target="_self" />
          <area shape="rect" coords="25,45,31,54" href="svg_zeichenvorschrift_farbe.php?color_max=003399" target="_self" />
          <area shape="rect" coords="33,45,39,54" href="svg_zeichenvorschrift_farbe.php?color_max=0033CC" target="_self" />
          <area shape="rect" coords="41,45,47,54" href="svg_zeichenvorschrift_farbe.php?color_max=0033FF" target="_self" />

          <area shape="rect" coords="49,45,55,54" href="svg_zeichenvorschrift_farbe.php?color_max=333300" target="_self" />
          <area shape="rect" coords="57,45,63,54" href="svg_zeichenvorschrift_farbe.php?color_max=333333" target="_self" />
          <area shape="rect" coords="65,45,71,54" href="svg_zeichenvorschrift_farbe.php?color_max=333366" target="_self" />
          <area shape="rect" coords="73,45,79,54" href="svg_zeichenvorschrift_farbe.php?color_max=333399" target="_self" />
          <area shape="rect" coords="81,45,87,54" href="svg_zeichenvorschrift_farbe.php?color_max=3333CC" target="_self" />
          <area shape="rect" coords="89,45,95,54" href="svg_zeichenvorschrift_farbe.php?color_max=3333FF" target="_self" />
          <area shape="rect" coords="97,45,103,54" href="svg_zeichenvorschrift_farbe.php?color_max=663300" target="_self" />
          <area shape="rect" coords="105,45,111,54" href="svg_zeichenvorschrift_farbe.php?color_max=663333" target="_self" />
          <area shape="rect" coords="113,45,119,54" href="svg_zeichenvorschrift_farbe.php?color_max=663366" target="_self" />

          <area shape="rect" coords="121,45,127,54" href="svg_zeichenvorschrift_farbe.php?color_max=663399" target="_self" />
          <area shape="rect" coords="129,45,135,54" href="svg_zeichenvorschrift_farbe.php?color_max=6633CC" target="_self" />
          <area shape="rect" coords="137,45,143,54" href="svg_zeichenvorschrift_farbe.php?color_max=6633FF" target="_self" />
          <area shape="rect" coords="145,45,151,54" href="svg_zeichenvorschrift_farbe.php?color_max=993300" target="_self" />
          <area shape="rect" coords="153,45,159,54" href="svg_zeichenvorschrift_farbe.php?color_max=993333" target="_self" />
          <area shape="rect" coords="161,45,167,54" href="svg_zeichenvorschrift_farbe.php?color_max=993366" target="_self" />
          <area shape="rect" coords="169,45,175,54" href="svg_zeichenvorschrift_farbe.php?color_max=993399" target="_self" />
          <area shape="rect" coords="177,45,183,54" href="svg_zeichenvorschrift_farbe.php?color_max=9933CC" target="_self" />
          <area shape="rect" coords="185,45,191,54" href="svg_zeichenvorschrift_farbe.php?color_max=9933FF" target="_self" />

          <area shape="rect" coords="193,45,199,54" href="svg_zeichenvorschrift_farbe.php?color_max=CC3300" target="_self" />
          <area shape="rect" coords="201,45,207,54" href="svg_zeichenvorschrift_farbe.php?color_max=CC3333" target="_self" />
          <area shape="rect" coords="209,45,215,54" href="svg_zeichenvorschrift_farbe.php?color_max=CC3366" target="_self" />
          <area shape="rect" coords="217,45,223,54" href="svg_zeichenvorschrift_farbe.php?color_max=CC3399" target="_self" />
          <area shape="rect" coords="225,45,231,54" href="svg_zeichenvorschrift_farbe.php?color_max=CC33CC" target="_self" />
          <area shape="rect" coords="233,45,239,54" href="svg_zeichenvorschrift_farbe.php?color_max=CC33FF" target="_self" />
          <area shape="rect" coords="241,45,247,54" href="svg_zeichenvorschrift_farbe.php?color_max=FF3300" target="_self" />
          <area shape="rect" coords="249,45,255,54" href="svg_zeichenvorschrift_farbe.php?color_max=FF3333" target="_self" />
          <area shape="rect" coords="257,45,263,54" href="svg_zeichenvorschrift_farbe.php?color_max=FF3366" target="_self" />

          <area shape="rect" coords="265,45,271,54" href="svg_zeichenvorschrift_farbe.php?color_max=FF3399" target="_self" />
          <area shape="rect" coords="273,45,279,54" href="svg_zeichenvorschrift_farbe.php?color_max=FF33CC" target="_self" />
          <area shape="rect" coords="281,45,287,54" href="svg_zeichenvorschrift_farbe.php?color_max=FF33FF" target="_self" />
          <area shape="rect" coords="1,56,7,65" href="svg_zeichenvorschrift_farbe.php?color_max=000000" target="_self" />
          <area shape="rect" coords="9,56,15,65" href="svg_zeichenvorschrift_farbe.php?color_max=000033" target="_self" />
          <area shape="rect" coords="17,56,23,65" href="svg_zeichenvorschrift_farbe.php?color_max=000066" target="_self" />
          <area shape="rect" coords="25,56,31,65" href="svg_zeichenvorschrift_farbe.php?color_max=000099" target="_self" />
          <area shape="rect" coords="33,56,39,65" href="svg_zeichenvorschrift_farbe.php?color_max=0000CC" target="_self" />
          <area shape="rect" coords="41,56,47,65" href="svg_zeichenvorschrift_farbe.php?color_max=0000FF" target="_self" />

          <area shape="rect" coords="49,56,55,65" href="svg_zeichenvorschrift_farbe.php?color_max=330000" target="_self" />
          <area shape="rect" coords="57,56,63,65" href="svg_zeichenvorschrift_farbe.php?color_max=330033" target="_self" />
          <area shape="rect" coords="65,56,71,65" href="svg_zeichenvorschrift_farbe.php?color_max=330066" target="_self" />
          <area shape="rect" coords="73,56,79,65" href="svg_zeichenvorschrift_farbe.php?color_max=330099" target="_self" />
          <area shape="rect" coords="81,56,87,65" href="svg_zeichenvorschrift_farbe.php?color_max=3300CC" target="_self" />
          <area shape="rect" coords="89,56,95,65" href="svg_zeichenvorschrift_farbe.php?color_max=3300FF" target="_self" />
          <area shape="rect" coords="97,56,103,65" href="svg_zeichenvorschrift_farbe.php?color_max=660000" target="_self" />
          <area shape="rect" coords="105,56,111,65" href="svg_zeichenvorschrift_farbe.php?color_max=660033" target="_self" />
          <area shape="rect" coords="113,56,119,65" href="svg_zeichenvorschrift_farbe.php?color_max=660066" target="_self" />

          <area shape="rect" coords="121,56,127,65" href="svg_zeichenvorschrift_farbe.php?color_max=660099" target="_self" />
          <area shape="rect" coords="129,56,135,65" href="svg_zeichenvorschrift_farbe.php?color_max=6600CC" target="_self" />
          <area shape="rect" coords="137,56,143,65" href="svg_zeichenvorschrift_farbe.php?color_max=6600FF" target="_self" />
          <area shape="rect" coords="145,56,151,65" href="svg_zeichenvorschrift_farbe.php?color_max=990000" target="_self" />
          <area shape="rect" coords="153,56,159,65" href="svg_zeichenvorschrift_farbe.php?color_max=990033" target="_self" />
          <area shape="rect" coords="161,56,167,65" href="svg_zeichenvorschrift_farbe.php?color_max=990066" target="_self" />
          <area shape="rect" coords="169,56,175,65" href="svg_zeichenvorschrift_farbe.php?color_max=990099" target="_self" />
          <area shape="rect" coords="177,56,183,65" href="svg_zeichenvorschrift_farbe.php?color_max=9900CC" target="_self" />
          <area shape="rect" coords="185,56,191,65" href="svg_zeichenvorschrift_farbe.php?color_max=9900FF" target="_self" />

          <area shape="rect" coords="193,56,199,65" href="svg_zeichenvorschrift_farbe.php?color_max=CC0000" target="_self" />
          <area shape="rect" coords="201,56,207,65" href="svg_zeichenvorschrift_farbe.php?color_max=CC0033" target="_self" />
          <area shape="rect" coords="209,56,215,65" href="svg_zeichenvorschrift_farbe.php?color_max=CC0066" target="_self" />
          <area shape="rect" coords="217,56,223,65" href="svg_zeichenvorschrift_farbe.php?color_max=CC0099" target="_self" />
          <area shape="rect" coords="225,56,231,65" href="svg_zeichenvorschrift_farbe.php?color_max=CC00CC" target="_self" />
          <area shape="rect" coords="233,56,239,65" href="svg_zeichenvorschrift_farbe.php?color_max=CC00FF" target="_self" />
          <area shape="rect" coords="241,56,247,65" href="svg_zeichenvorschrift_farbe.php?color_max=FF0000" target="_self" />
          <area shape="rect" coords="249,56,255,65" href="svg_zeichenvorschrift_farbe.php?color_max=FF0033" target="_self" />
          <area shape="rect" coords="257,56,263,65" href="svg_zeichenvorschrift_farbe.php?color_max=FF0066" target="_self" />

          <area shape="rect" coords="265,56,271,65" href="svg_zeichenvorschrift_farbe.php?color_max=FF0099" target="_self" />
          <area shape="rect" coords="273,56,279,65" href="svg_zeichenvorschrift_farbe.php?color_max=FF00CC" target="_self" />
          <area shape="rect" coords="281,56,287,65" href="svg_zeichenvorschrift_farbe.php?color_max=FF00FF" target="_self" />
          </map>
                <a><img usemap="#colmap_max" src="gfx/colortable.gif" border="0" width="289" height="67" alt="" /></a>
                
                
                <br />
          <map name="colmap_max_grau" id="id">
          <area shape="rect" coords="1,1,7,10" href="svg_zeichenvorschrift_farbe.php?color_max=000000" target="_self" />
          <area shape="rect" coords="9,1,15,10" href="svg_zeichenvorschrift_farbe.php?color_max=111111" target="_self" />
          <area shape="rect" coords="17,1,23,10" href="svg_zeichenvorschrift_farbe.php?color_max=222222" target="_self" />
          <area shape="rect" coords="25,1,31,10" href="svg_zeichenvorschrift_farbe.php?color_max=333333" target="_self" />
          <area shape="rect" coords="33,1,39,10" href="svg_zeichenvorschrift_farbe.php?color_max=444444" target="_self" />
          <area shape="rect" coords="41,1,47,10" href="svg_zeichenvorschrift_farbe.php?color_max=555555" target="_self" />
          <area shape="rect" coords="49,1,55,10" href="svg_zeichenvorschrift_farbe.php?color_max=666666" target="_self" />
          <area shape="rect" coords="57,1,63,10" href="svg_zeichenvorschrift_farbe.php?color_max=777777" target="_self" />
          <area shape="rect" coords="65,1,71,10" href="svg_zeichenvorschrift_farbe.php?color_max=888888" target="_self" />
          <area shape="rect" coords="73,1,79,10" href="svg_zeichenvorschrift_farbe.php?color_max=999999" target="_self" />
          <area shape="rect" coords="81,1,87,10" href="svg_zeichenvorschrift_farbe.php?color_max=aaaaaa" target="_self" />
          <area shape="rect" coords="89,1,95,10" href="svg_zeichenvorschrift_farbe.php?color_max=bbbbbb" target="_self" />
          <area shape="rect" coords="97,1,103,10" href="svg_zeichenvorschrift_farbe.php?color_max=cccccc" target="_self" />
          <area shape="rect" coords="105,1,111,10" href="svg_zeichenvorschrift_farbe.php?color_max=dddddd" target="_self" />
          <area shape="rect" coords="113,1,119,10" href="svg_zeichenvorschrift_farbe.php?color_max=eeeeee" target="_self" />
          <area shape="rect" coords="121,1,127,10" href="svg_zeichenvorschrift_farbe.php?color_max=ffffff" target="_self" />
          </map>
          <a> <img usemap="#colmap_max_grau" src="gfx/colortable_grau.png" border="0" width="129" height="12" alt="" /></a>
           <br />
                
            Selbst definierter Hex-RGB-Wert: #
            <input name="Farbwert_max" type="text" value="<?php echo $_SESSION['Dokument']['Fuellung']['Farbwert_max']; ?>" size="8" maxlength="6" /> 
            <input name="button" type="submit" class="button_blau_abschicken" id="button" style="cursor: pointer;" value="OK" />
            <br />
            <br /></td>
              </tr>
              <tr>
                <td valign="bottom">Farbe
                für Minimum:</td>
                <td align="left" valign="top">
                
                
          <map name="colmap_min" id="id">
          <area shape="rect" coords="1,1,7,10" href="svg_zeichenvorschrift_farbe.php?color_min=00FF00" target="_self" />
          <area shape="rect" coords="9,1,15,10" href="svg_zeichenvorschrift_farbe.php?color_min=00FF33" target="_self" />
          <area shape="rect" coords="17,1,23,10" href="svg_zeichenvorschrift_farbe.php?color_min=00FF66" target="_self" />
          <area shape="rect" coords="25,1,31,10" href="svg_zeichenvorschrift_farbe.php?color_min=00FF99" target="_self" />
          <area shape="rect" coords="33,1,39,10" href="svg_zeichenvorschrift_farbe.php?color_min=00FFCC" target="_self" />
          <area shape="rect" coords="41,1,47,10" href="svg_zeichenvorschrift_farbe.php?color_min=00FFFF" target="_self" />

          <area shape="rect" coords="49,1,55,10" href="svg_zeichenvorschrift_farbe.php?color_min=33FF00" target="_self" />
          <area shape="rect" coords="57,1,63,10" href="svg_zeichenvorschrift_farbe.php?color_min=33FF33" target="_self" />
          <area shape="rect" coords="65,1,71,10" href="svg_zeichenvorschrift_farbe.php?color_min=33FF66" target="_self" />
          <area shape="rect" coords="73,1,79,10" href="svg_zeichenvorschrift_farbe.php?color_min=33FF99" target="_self" />
          <area shape="rect" coords="81,1,87,10" href="svg_zeichenvorschrift_farbe.php?color_min=33FFCC" target="_self" />
          <area shape="rect" coords="89,1,95,10" href="svg_zeichenvorschrift_farbe.php?color_min=33FFFF" target="_self" />
          <area shape="rect" coords="97,1,103,10" href="svg_zeichenvorschrift_farbe.php?color_min=66FF00" target="_self" />
          <area shape="rect" coords="105,1,111,10" href="svg_zeichenvorschrift_farbe.php?color_min=66FF33" target="_self" />
          <area shape="rect" coords="113,1,119,10" href="svg_zeichenvorschrift_farbe.php?color_min=66FF66" target="_self" />

          <area shape="rect" coords="121,1,127,10" href="svg_zeichenvorschrift_farbe.php?color_min=66FF99" target="_self" />
          <area shape="rect" coords="129,1,135,10" href="svg_zeichenvorschrift_farbe.php?color_min=66FFCC" target="_self" />
          <area shape="rect" coords="137,1,143,10" href="svg_zeichenvorschrift_farbe.php?color_min=66FFFF" target="_self" />
          <area shape="rect" coords="145,1,151,10" href="svg_zeichenvorschrift_farbe.php?color_min=99FF00" target="_self" />
          <area shape="rect" coords="153,1,159,10" href="svg_zeichenvorschrift_farbe.php?color_min=99FF33" target="_self" />
          <area shape="rect" coords="161,1,167,10" href="svg_zeichenvorschrift_farbe.php?color_min=99FF66" target="_self" />
          <area shape="rect" coords="169,1,175,10" href="svg_zeichenvorschrift_farbe.php?color_min=99FF99" target="_self" />
          <area shape="rect" coords="177,1,183,10" href="svg_zeichenvorschrift_farbe.php?color_min=99FFCC" target="_self" />
          <area shape="rect" coords="185,1,191,10" href="svg_zeichenvorschrift_farbe.php?color_min=99FFFF" target="_self" />

          <area shape="rect" coords="193,1,199,10" href="svg_zeichenvorschrift_farbe.php?color_min=CCFF00" target="_self" />
          <area shape="rect" coords="201,1,207,10" href="svg_zeichenvorschrift_farbe.php?color_min=CCFF33" target="_self" />
          <area shape="rect" coords="209,1,215,10" href="svg_zeichenvorschrift_farbe.php?color_min=CCFF66" target="_self" />
          <area shape="rect" coords="217,1,223,10" href="svg_zeichenvorschrift_farbe.php?color_min=CCFF99" target="_self" />
          <area shape="rect" coords="225,1,231,10" href="svg_zeichenvorschrift_farbe.php?color_min=CCFFCC" target="_self" />
          <area shape="rect" coords="233,1,239,10" href="svg_zeichenvorschrift_farbe.php?color_min=CCFFFF" target="_self" />
          <area shape="rect" coords="241,1,247,10" href="svg_zeichenvorschrift_farbe.php?color_min=FFFF00" target="_self" />
          <area shape="rect" coords="249,1,255,10" href="svg_zeichenvorschrift_farbe.php?color_min=FFFF33" target="_self" />
          <area shape="rect" coords="257,1,263,10" href="svg_zeichenvorschrift_farbe.php?color_min=FFFF66" target="_self" />

          <area shape="rect" coords="265,1,271,10" href="svg_zeichenvorschrift_farbe.php?color_min=FFFF99" target="_self" />
          <area shape="rect" coords="273,1,279,10" href="svg_zeichenvorschrift_farbe.php?color_min=FFFFCC" target="_self" />
          <area shape="rect" coords="281,1,287,10" href="svg_zeichenvorschrift_farbe.php?color_min=FFFFFF" target="_self" />
          <area shape="rect" coords="1,12,7,21" href="svg_zeichenvorschrift_farbe.php?color_min=00CC00" target="_self" />
          <area shape="rect" coords="9,12,15,21" href="svg_zeichenvorschrift_farbe.php?color_min=00CC33" target="_self" />
          <area shape="rect" coords="17,12,23,21" href="svg_zeichenvorschrift_farbe.php?color_min=00CC66" target="_self" />
          <area shape="rect" coords="25,12,31,21" href="svg_zeichenvorschrift_farbe.php?color_min=00CC99" target="_self" />
          <area shape="rect" coords="33,12,39,21" href="svg_zeichenvorschrift_farbe.php?color_min=00CCCC" target="_self" />
          <area shape="rect" coords="41,12,47,21" href="svg_zeichenvorschrift_farbe.php?color_min=00CCFF" target="_self" />

          <area shape="rect" coords="49,12,55,21" href="svg_zeichenvorschrift_farbe.php?color_min=33CC00" target="_self" />
          <area shape="rect" coords="57,12,63,21" href="svg_zeichenvorschrift_farbe.php?color_min=33CC33" target="_self" />
          <area shape="rect" coords="65,12,71,21" href="svg_zeichenvorschrift_farbe.php?color_min=33CC66" target="_self" />
          <area shape="rect" coords="73,12,79,21" href="svg_zeichenvorschrift_farbe.php?color_min=33CC99" target="_self" />
          <area shape="rect" coords="81,12,87,21" href="svg_zeichenvorschrift_farbe.php?color_min=33CCCC" target="_self" />
          <area shape="rect" coords="89,12,95,21" href="svg_zeichenvorschrift_farbe.php?color_min=33CCFF" target="_self" />
          <area shape="rect" coords="97,12,103,21" href="svg_zeichenvorschrift_farbe.php?color_min=66CC00" target="_self" />
          <area shape="rect" coords="105,12,111,21" href="svg_zeichenvorschrift_farbe.php?color_min=66CC33" target="_self" />
          <area shape="rect" coords="113,12,119,21" href="svg_zeichenvorschrift_farbe.php?color_min=66CC66" target="_self" />

          <area shape="rect" coords="121,12,127,21" href="svg_zeichenvorschrift_farbe.php?color_min=66CC99" target="_self" />
          <area shape="rect" coords="129,12,135,21" href="svg_zeichenvorschrift_farbe.php?color_min=66CCCC" target="_self" />
          <area shape="rect" coords="137,12,143,21" href="svg_zeichenvorschrift_farbe.php?color_min=66CCFF" target="_self" />
          <area shape="rect" coords="145,12,151,21" href="svg_zeichenvorschrift_farbe.php?color_min=99CC00" target="_self" />
          <area shape="rect" coords="153,12,159,21" href="svg_zeichenvorschrift_farbe.php?color_min=99CC33" target="_self" />
          <area shape="rect" coords="161,12,167,21" href="svg_zeichenvorschrift_farbe.php?color_min=99CC66" target="_self" />
          <area shape="rect" coords="169,12,175,21" href="svg_zeichenvorschrift_farbe.php?color_min=99CC99" target="_self" />
          <area shape="rect" coords="177,12,183,21" href="svg_zeichenvorschrift_farbe.php?color_min=99CCCC" target="_self" />
          <area shape="rect" coords="185,12,191,21" href="svg_zeichenvorschrift_farbe.php?color_min=99CCFF" target="_self" />

          <area shape="rect" coords="193,12,199,21" href="svg_zeichenvorschrift_farbe.php?color_min=CCCC00" target="_self" />
          <area shape="rect" coords="201,12,207,21" href="svg_zeichenvorschrift_farbe.php?color_min=CCCC33" target="_self" />
          <area shape="rect" coords="209,12,215,21" href="svg_zeichenvorschrift_farbe.php?color_min=CCCC66" target="_self" />
          <area shape="rect" coords="217,12,223,21" href="svg_zeichenvorschrift_farbe.php?color_min=CCCC99" target="_self" />
          <area shape="rect" coords="225,12,231,21" href="svg_zeichenvorschrift_farbe.php?color_min=CCCCCC" target="_self" />
          <area shape="rect" coords="233,12,239,21" href="svg_zeichenvorschrift_farbe.php?color_min=CCCCFF" target="_self" />
          <area shape="rect" coords="241,12,247,21" href="svg_zeichenvorschrift_farbe.php?color_min=FFCC00" target="_self" />
          <area shape="rect" coords="249,12,255,21" href="svg_zeichenvorschrift_farbe.php?color_min=FFCC33" target="_self" />
          <area shape="rect" coords="257,12,263,21" href="svg_zeichenvorschrift_farbe.php?color_min=FFCC66" target="_self" />

          <area shape="rect" coords="265,12,271,21" href="svg_zeichenvorschrift_farbe.php?color_min=FFCC99" target="_self" />
          <area shape="rect" coords="273,12,279,21" href="svg_zeichenvorschrift_farbe.php?color_min=FFCCCC" target="_self" />
          <area shape="rect" coords="281,12,287,21" href="svg_zeichenvorschrift_farbe.php?color_min=FFCCFF" target="_self" />
          <area shape="rect" coords="1,23,7,32" href="svg_zeichenvorschrift_farbe.php?color_min=009900" target="_self" />
          <area shape="rect" coords="9,23,15,32" href="svg_zeichenvorschrift_farbe.php?color_min=009933" target="_self" />
          <area shape="rect" coords="17,23,23,32" href="svg_zeichenvorschrift_farbe.php?color_min=009966" target="_self" />
          <area shape="rect" coords="25,23,31,32" href="svg_zeichenvorschrift_farbe.php?color_min=009999" target="_self" />
          <area shape="rect" coords="33,23,39,32" href="svg_zeichenvorschrift_farbe.php?color_min=0099CC" target="_self" />
          <area shape="rect" coords="41,23,47,32" href="svg_zeichenvorschrift_farbe.php?color_min=0099FF" target="_self" />

          <area shape="rect" coords="49,23,55,32" href="svg_zeichenvorschrift_farbe.php?color_min=339900" target="_self" />
          <area shape="rect" coords="57,23,63,32" href="svg_zeichenvorschrift_farbe.php?color_min=339933" target="_self" />
          <area shape="rect" coords="65,23,71,32" href="svg_zeichenvorschrift_farbe.php?color_min=339966" target="_self" />
          <area shape="rect" coords="73,23,79,32" href="svg_zeichenvorschrift_farbe.php?color_min=339999" target="_self" />
          <area shape="rect" coords="81,23,87,32" href="svg_zeichenvorschrift_farbe.php?color_min=3399CC" target="_self" />
          <area shape="rect" coords="89,23,95,32" href="svg_zeichenvorschrift_farbe.php?color_min=3399FF" target="_self" />
          <area shape="rect" coords="97,23,103,32" href="svg_zeichenvorschrift_farbe.php?color_min=669900" target="_self" />
          <area shape="rect" coords="105,23,111,32" href="svg_zeichenvorschrift_farbe.php?color_min=669933" target="_self" />
          <area shape="rect" coords="113,23,119,32" href="svg_zeichenvorschrift_farbe.php?color_min=669966" target="_self" />

          <area shape="rect" coords="121,23,127,32" href="svg_zeichenvorschrift_farbe.php?color_min=669999" target="_self" />
          <area shape="rect" coords="129,23,135,32" href="svg_zeichenvorschrift_farbe.php?color_min=6699CC" target="_self" />
          <area shape="rect" coords="137,23,143,32" href="svg_zeichenvorschrift_farbe.php?color_min=6699FF" target="_self" />
          <area shape="rect" coords="145,23,151,32" href="svg_zeichenvorschrift_farbe.php?color_min=999900" target="_self" />
          <area shape="rect" coords="153,23,159,32" href="svg_zeichenvorschrift_farbe.php?color_min=999933" target="_self" />
          <area shape="rect" coords="161,23,167,32" href="svg_zeichenvorschrift_farbe.php?color_min=999966" target="_self" />
          <area shape="rect" coords="169,23,175,32" href="svg_zeichenvorschrift_farbe.php?color_min=999999" target="_self" />
          <area shape="rect" coords="177,23,183,32" href="svg_zeichenvorschrift_farbe.php?color_min=9999CC" target="_self" />
          <area shape="rect" coords="185,23,191,32" href="svg_zeichenvorschrift_farbe.php?color_min=9999FF" target="_self" />

          <area shape="rect" coords="193,23,199,32" href="svg_zeichenvorschrift_farbe.php?color_min=CC9900" target="_self" />
          <area shape="rect" coords="201,23,207,32" href="svg_zeichenvorschrift_farbe.php?color_min=CC9933" target="_self" />
          <area shape="rect" coords="209,23,215,32" href="svg_zeichenvorschrift_farbe.php?color_min=CC9966" target="_self" />
          <area shape="rect" coords="217,23,223,32" href="svg_zeichenvorschrift_farbe.php?color_min=CC9999" target="_self" />
          <area shape="rect" coords="225,23,231,32" href="svg_zeichenvorschrift_farbe.php?color_min=CC99CC" target="_self" />
          <area shape="rect" coords="233,23,239,32" href="svg_zeichenvorschrift_farbe.php?color_min=CC99FF" target="_self" />
          <area shape="rect" coords="241,23,247,32" href="svg_zeichenvorschrift_farbe.php?color_min=FF9900" target="_self" />
          <area shape="rect" coords="249,23,255,32" href="svg_zeichenvorschrift_farbe.php?color_min=FF9933" target="_self" />
          <area shape="rect" coords="257,23,263,32" href="svg_zeichenvorschrift_farbe.php?color_min=FF9966" target="_self" />

          <area shape="rect" coords="265,23,271,32" href="svg_zeichenvorschrift_farbe.php?color_min=FF9999" target="_self" />
          <area shape="rect" coords="273,23,279,32" href="svg_zeichenvorschrift_farbe.php?color_min=FF99CC" target="_self" />
          <area shape="rect" coords="281,23,287,32" href="svg_zeichenvorschrift_farbe.php?color_min=FF99FF" target="_self" />
          <area shape="rect" coords="1,34,7,43" href="svg_zeichenvorschrift_farbe.php?color_min=006600" target="_self" />
          <area shape="rect" coords="9,34,15,43" href="svg_zeichenvorschrift_farbe.php?color_min=006633" target="_self" />
          <area shape="rect" coords="17,34,23,43" href="svg_zeichenvorschrift_farbe.php?color_min=006666" target="_self" />
          <area shape="rect" coords="25,34,31,43" href="svg_zeichenvorschrift_farbe.php?color_min=006699" target="_self" />
          <area shape="rect" coords="33,34,39,43" href="svg_zeichenvorschrift_farbe.php?color_min=0066CC" target="_self" />
          <area shape="rect" coords="41,34,47,43" href="svg_zeichenvorschrift_farbe.php?color_min=0066FF" target="_self" />

          <area shape="rect" coords="49,34,55,43" href="svg_zeichenvorschrift_farbe.php?color_min=336600" target="_self" />
          <area shape="rect" coords="57,34,63,43" href="svg_zeichenvorschrift_farbe.php?color_min=336633" target="_self" />
          <area shape="rect" coords="65,34,71,43" href="svg_zeichenvorschrift_farbe.php?color_min=336666" target="_self" />
          <area shape="rect" coords="73,34,79,43" href="svg_zeichenvorschrift_farbe.php?color_min=336699" target="_self" />
          <area shape="rect" coords="81,34,87,43" href="svg_zeichenvorschrift_farbe.php?color_min=3366CC" target="_self" />
          <area shape="rect" coords="89,34,95,43" href="svg_zeichenvorschrift_farbe.php?color_min=3366FF" target="_self" />
          <area shape="rect" coords="97,34,103,43" href="svg_zeichenvorschrift_farbe.php?color_min=666600" target="_self" />
          <area shape="rect" coords="105,34,111,43" href="svg_zeichenvorschrift_farbe.php?color_min=666633" target="_self" />
          <area shape="rect" coords="113,34,119,43" href="svg_zeichenvorschrift_farbe.php?color_min=666666" target="_self" />

          <area shape="rect" coords="121,34,127,43" href="svg_zeichenvorschrift_farbe.php?color_min=666699" target="_self" />
          <area shape="rect" coords="129,34,135,43" href="svg_zeichenvorschrift_farbe.php?color_min=6666CC" target="_self" />
          <area shape="rect" coords="137,34,143,43" href="svg_zeichenvorschrift_farbe.php?color_min=6666FF" target="_self" />
          <area shape="rect" coords="145,34,151,43" href="svg_zeichenvorschrift_farbe.php?color_min=996600" target="_self" />
          <area shape="rect" coords="153,34,159,43" href="svg_zeichenvorschrift_farbe.php?color_min=996633" target="_self" />
          <area shape="rect" coords="161,34,167,43" href="svg_zeichenvorschrift_farbe.php?color_min=996666" target="_self" />
          <area shape="rect" coords="169,34,175,43" href="svg_zeichenvorschrift_farbe.php?color_min=996699" target="_self" />
          <area shape="rect" coords="177,34,183,43" href="svg_zeichenvorschrift_farbe.php?color_min=9966CC" target="_self" />
          <area shape="rect" coords="185,34,191,43" href="svg_zeichenvorschrift_farbe.php?color_min=9966FF" target="_self" />

          <area shape="rect" coords="193,34,199,43" href="svg_zeichenvorschrift_farbe.php?color_min=CC6600" target="_self" />
          <area shape="rect" coords="201,34,207,43" href="svg_zeichenvorschrift_farbe.php?color_min=CC6633" target="_self" />
          <area shape="rect" coords="209,34,215,43" href="svg_zeichenvorschrift_farbe.php?color_min=CC6666" target="_self" />
          <area shape="rect" coords="217,34,223,43" href="svg_zeichenvorschrift_farbe.php?color_min=CC6699" target="_self" />
          <area shape="rect" coords="225,34,231,43" href="svg_zeichenvorschrift_farbe.php?color_min=CC66CC" target="_self" />
          <area shape="rect" coords="233,34,239,43" href="svg_zeichenvorschrift_farbe.php?color_min=CC66FF" target="_self" />
          <area shape="rect" coords="241,34,247,43" href="svg_zeichenvorschrift_farbe.php?color_min=FF6600" target="_self" />
          <area shape="rect" coords="249,34,255,43" href="svg_zeichenvorschrift_farbe.php?color_min=FF6633" target="_self" />
          <area shape="rect" coords="257,34,263,43" href="svg_zeichenvorschrift_farbe.php?color_min=FF6666" target="_self" />

          <area shape="rect" coords="265,34,271,43" href="svg_zeichenvorschrift_farbe.php?color_min=FF6699" target="_self" />
          <area shape="rect" coords="273,34,279,43" href="svg_zeichenvorschrift_farbe.php?color_min=FF66CC" target="_self" />
          <area shape="rect" coords="281,34,287,43" href="svg_zeichenvorschrift_farbe.php?color_min=FF66FF" target="_self" />
          <area shape="rect" coords="1,45,7,54" href="svg_zeichenvorschrift_farbe.php?color_min=003300" target="_self" />
          <area shape="rect" coords="9,45,15,54" href="svg_zeichenvorschrift_farbe.php?color_min=003333" target="_self" />
          <area shape="rect" coords="17,45,23,54" href="svg_zeichenvorschrift_farbe.php?color_min=003366" target="_self" />
          <area shape="rect" coords="25,45,31,54" href="svg_zeichenvorschrift_farbe.php?color_min=003399" target="_self" />
          <area shape="rect" coords="33,45,39,54" href="svg_zeichenvorschrift_farbe.php?color_min=0033CC" target="_self" />
          <area shape="rect" coords="41,45,47,54" href="svg_zeichenvorschrift_farbe.php?color_min=0033FF" target="_self" />

          <area shape="rect" coords="49,45,55,54" href="svg_zeichenvorschrift_farbe.php?color_min=333300" target="_self" />
          <area shape="rect" coords="57,45,63,54" href="svg_zeichenvorschrift_farbe.php?color_min=333333" target="_self" />
          <area shape="rect" coords="65,45,71,54" href="svg_zeichenvorschrift_farbe.php?color_min=333366" target="_self" />
          <area shape="rect" coords="73,45,79,54" href="svg_zeichenvorschrift_farbe.php?color_min=333399" target="_self" />
          <area shape="rect" coords="81,45,87,54" href="svg_zeichenvorschrift_farbe.php?color_min=3333CC" target="_self" />
          <area shape="rect" coords="89,45,95,54" href="svg_zeichenvorschrift_farbe.php?color_min=3333FF" target="_self" />
          <area shape="rect" coords="97,45,103,54" href="svg_zeichenvorschrift_farbe.php?color_min=663300" target="_self" />
          <area shape="rect" coords="105,45,111,54" href="svg_zeichenvorschrift_farbe.php?color_min=663333" target="_self" />
          <area shape="rect" coords="113,45,119,54" href="svg_zeichenvorschrift_farbe.php?color_min=663366" target="_self" />

          <area shape="rect" coords="121,45,127,54" href="svg_zeichenvorschrift_farbe.php?color_min=663399" target="_self" />
          <area shape="rect" coords="129,45,135,54" href="svg_zeichenvorschrift_farbe.php?color_min=6633CC" target="_self" />
          <area shape="rect" coords="137,45,143,54" href="svg_zeichenvorschrift_farbe.php?color_min=6633FF" target="_self" />
          <area shape="rect" coords="145,45,151,54" href="svg_zeichenvorschrift_farbe.php?color_min=993300" target="_self" />
          <area shape="rect" coords="153,45,159,54" href="svg_zeichenvorschrift_farbe.php?color_min=993333" target="_self" />
          <area shape="rect" coords="161,45,167,54" href="svg_zeichenvorschrift_farbe.php?color_min=993366" target="_self" />
          <area shape="rect" coords="169,45,175,54" href="svg_zeichenvorschrift_farbe.php?color_min=993399" target="_self" />
          <area shape="rect" coords="177,45,183,54" href="svg_zeichenvorschrift_farbe.php?color_min=9933CC" target="_self" />
          <area shape="rect" coords="185,45,191,54" href="svg_zeichenvorschrift_farbe.php?color_min=9933FF" target="_self" />

          <area shape="rect" coords="193,45,199,54" href="svg_zeichenvorschrift_farbe.php?color_min=CC3300" target="_self" />
          <area shape="rect" coords="201,45,207,54" href="svg_zeichenvorschrift_farbe.php?color_min=CC3333" target="_self" />
          <area shape="rect" coords="209,45,215,54" href="svg_zeichenvorschrift_farbe.php?color_min=CC3366" target="_self" />
          <area shape="rect" coords="217,45,223,54" href="svg_zeichenvorschrift_farbe.php?color_min=CC3399" target="_self" />
          <area shape="rect" coords="225,45,231,54" href="svg_zeichenvorschrift_farbe.php?color_min=CC33CC" target="_self" />
          <area shape="rect" coords="233,45,239,54" href="svg_zeichenvorschrift_farbe.php?color_min=CC33FF" target="_self" />
          <area shape="rect" coords="241,45,247,54" href="svg_zeichenvorschrift_farbe.php?color_min=FF3300" target="_self" />
          <area shape="rect" coords="249,45,255,54" href="svg_zeichenvorschrift_farbe.php?color_min=FF3333" target="_self" />
          <area shape="rect" coords="257,45,263,54" href="svg_zeichenvorschrift_farbe.php?color_min=FF3366" target="_self" />

          <area shape="rect" coords="265,45,271,54" href="svg_zeichenvorschrift_farbe.php?color_min=FF3399" target="_self" />
          <area shape="rect" coords="273,45,279,54" href="svg_zeichenvorschrift_farbe.php?color_min=FF33CC" target="_self" />
          <area shape="rect" coords="281,45,287,54" href="svg_zeichenvorschrift_farbe.php?color_min=FF33FF" target="_self" />
          <area shape="rect" coords="1,56,7,65" href="svg_zeichenvorschrift_farbe.php?color_min=000000" target="_self" />
          <area shape="rect" coords="9,56,15,65" href="svg_zeichenvorschrift_farbe.php?color_min=000033" target="_self" />
          <area shape="rect" coords="17,56,23,65" href="svg_zeichenvorschrift_farbe.php?color_min=000066" target="_self" />
          <area shape="rect" coords="25,56,31,65" href="svg_zeichenvorschrift_farbe.php?color_min=000099" target="_self" />
          <area shape="rect" coords="33,56,39,65" href="svg_zeichenvorschrift_farbe.php?color_min=0000CC" target="_self" />
          <area shape="rect" coords="41,56,47,65" href="svg_zeichenvorschrift_farbe.php?color_min=0000FF" target="_self" />

          <area shape="rect" coords="49,56,55,65" href="svg_zeichenvorschrift_farbe.php?color_min=330000" target="_self" />
          <area shape="rect" coords="57,56,63,65" href="svg_zeichenvorschrift_farbe.php?color_min=330033" target="_self" />
          <area shape="rect" coords="65,56,71,65" href="svg_zeichenvorschrift_farbe.php?color_min=330066" target="_self" />
          <area shape="rect" coords="73,56,79,65" href="svg_zeichenvorschrift_farbe.php?color_min=330099" target="_self" />
          <area shape="rect" coords="81,56,87,65" href="svg_zeichenvorschrift_farbe.php?color_min=3300CC" target="_self" />
          <area shape="rect" coords="89,56,95,65" href="svg_zeichenvorschrift_farbe.php?color_min=3300FF" target="_self" />
          <area shape="rect" coords="97,56,103,65" href="svg_zeichenvorschrift_farbe.php?color_min=660000" target="_self" />
          <area shape="rect" coords="105,56,111,65" href="svg_zeichenvorschrift_farbe.php?color_min=660033" target="_self" />
          <area shape="rect" coords="113,56,119,65" href="svg_zeichenvorschrift_farbe.php?color_min=660066" target="_self" />

          <area shape="rect" coords="121,56,127,65" href="svg_zeichenvorschrift_farbe.php?color_min=660099" target="_self" />
          <area shape="rect" coords="129,56,135,65" href="svg_zeichenvorschrift_farbe.php?color_min=6600CC" target="_self" />
          <area shape="rect" coords="137,56,143,65" href="svg_zeichenvorschrift_farbe.php?color_min=6600FF" target="_self" />
          <area shape="rect" coords="145,56,151,65" href="svg_zeichenvorschrift_farbe.php?color_min=990000" target="_self" />
          <area shape="rect" coords="153,56,159,65" href="svg_zeichenvorschrift_farbe.php?color_min=990033" target="_self" />
          <area shape="rect" coords="161,56,167,65" href="svg_zeichenvorschrift_farbe.php?color_min=990066" target="_self" />
          <area shape="rect" coords="169,56,175,65" href="svg_zeichenvorschrift_farbe.php?color_min=990099" target="_self" />
          <area shape="rect" coords="177,56,183,65" href="svg_zeichenvorschrift_farbe.php?color_min=9900CC" target="_self" />
          <area shape="rect" coords="185,56,191,65" href="svg_zeichenvorschrift_farbe.php?color_min=9900FF" target="_self" />

          <area shape="rect" coords="193,56,199,65" href="svg_zeichenvorschrift_farbe.php?color_min=CC0000" target="_self" />
          <area shape="rect" coords="201,56,207,65" href="svg_zeichenvorschrift_farbe.php?color_min=CC0033" target="_self" />
          <area shape="rect" coords="209,56,215,65" href="svg_zeichenvorschrift_farbe.php?color_min=CC0066" target="_self" />
          <area shape="rect" coords="217,56,223,65" href="svg_zeichenvorschrift_farbe.php?color_min=CC0099" target="_self" />
          <area shape="rect" coords="225,56,231,65" href="svg_zeichenvorschrift_farbe.php?color_min=CC00CC" target="_self" />
          <area shape="rect" coords="233,56,239,65" href="svg_zeichenvorschrift_farbe.php?color_min=CC00FF" target="_self" />
          <area shape="rect" coords="241,56,247,65" href="svg_zeichenvorschrift_farbe.php?color_min=FF0000" target="_self" />
          <area shape="rect" coords="249,56,255,65" href="svg_zeichenvorschrift_farbe.php?color_min=FF0033" target="_self" />
          <area shape="rect" coords="257,56,263,65" href="svg_zeichenvorschrift_farbe.php?color_min=FF0066" target="_self" />

          <area shape="rect" coords="265,56,271,65" href="svg_zeichenvorschrift_farbe.php?color_min=FF0099" target="_self" />
          <area shape="rect" coords="273,56,279,65" href="svg_zeichenvorschrift_farbe.php?color_min=FF00CC" target="_self" />
          <area shape="rect" coords="281,56,287,65" href="svg_zeichenvorschrift_farbe.php?color_min=FF00FF" target="_self" />
          </map>
        	<a>
        	<img usemap="#colmap_min" src="gfx/colortable.gif" border="0" width="289" height="67" alt="" /></a>   
                
             <br />
			<map name="colmap_min_grau" id="id">
          <area shape="rect" coords="1,1,7,10" href="svg_zeichenvorschrift_farbe.php?color_min=000000" target="_self" />
          <area shape="rect" coords="9,1,15,10" href="svg_zeichenvorschrift_farbe.php?color_min=111111" target="_self" />
          <area shape="rect" coords="17,1,23,10" href="svg_zeichenvorschrift_farbe.php?color_min=222222" target="_self" />
          <area shape="rect" coords="25,1,31,10" href="svg_zeichenvorschrift_farbe.php?color_min=333333" target="_self" />
          <area shape="rect" coords="33,1,39,10" href="svg_zeichenvorschrift_farbe.php?color_min=444444" target="_self" />
          <area shape="rect" coords="41,1,47,10" href="svg_zeichenvorschrift_farbe.php?color_min=555555" target="_self" />
          <area shape="rect" coords="49,1,55,10" href="svg_zeichenvorschrift_farbe.php?color_min=666666" target="_self" />
          <area shape="rect" coords="57,1,63,10" href="svg_zeichenvorschrift_farbe.php?color_min=777777" target="_self" />
          <area shape="rect" coords="65,1,71,10" href="svg_zeichenvorschrift_farbe.php?color_min=888888" target="_self" />
          <area shape="rect" coords="73,1,79,10" href="svg_zeichenvorschrift_farbe.php?color_min=999999" target="_self" />
          <area shape="rect" coords="81,1,87,10" href="svg_zeichenvorschrift_farbe.php?color_min=aaaaaa" target="_self" />
          <area shape="rect" coords="89,1,95,10" href="svg_zeichenvorschrift_farbe.php?color_min=bbbbbb" target="_self" />
          <area shape="rect" coords="97,1,103,10" href="svg_zeichenvorschrift_farbe.php?color_min=cccccc" target="_self" />
          <area shape="rect" coords="105,1,111,10" href="svg_zeichenvorschrift_farbe.php?color_min=dddddd" target="_self" />
          <area shape="rect" coords="113,1,119,10" href="svg_zeichenvorschrift_farbe.php?color_min=eeeeee" target="_self" />
          <area shape="rect" coords="121,1,127,10" href="svg_zeichenvorschrift_farbe.php?color_min=ffffff" target="_self" />
          </map>
          <a> <img usemap="#colmap_min_grau" src="gfx/colortable_grau.png" border="0" width="129" height="12" alt="" /></a>
                <br />
                
  				Selbst definierter Hex-RGB-Wert: #
                <input name="Farbwert_min" type="text" value="<?php echo $_SESSION['Dokument']['Fuellung']['Farbwert_min']; ?>" size="8" maxlength="6" />
                  <input name="button6" type="submit" class="button_blau_abschicken" id="button6" style="cursor: pointer;" value="OK" />
                  <br /></td>
              </tr>
              <tr>
                <td>
                <br />
				<br />
                <!--<span class="button_standard_abschicken_a" style="background:#BAD380;"><a href="svg_html.php#top" target="_self">&nbsp;&nbsp;&nbsp;&lt;= Zurück zur Karte&nbsp;&nbsp;&nbsp;</a></span> -->
                <div style="float:left; margin-right:10px; padding-top:15px;">
                    	<a href="svg_html.php?kopieren=1" target="_self"><img src="icons_viewer/back.png" alt="Zur&uuml;ck" /><br />
                    	zur&uuml;ck</a>
                </div> 
                </td>
                <td colspan="2"><span class="button_standard_abschicken_a"><a href="svg_zeichenvorschrift_farbe.php?tausch=ja" target="_self">&nbsp;&nbsp;&nbsp;Minimum &amp; Maximum vertauschen&nbsp;&nbsp;&nbsp;</a></span></td>
              </tr>
              <tr>
                <td colspan="3" valign="bottom">
                  <br />
                  <br />
                <div style="height:0px; border-bottom:1px solid #999999; margin-top:0px; margin-bottom:7px;"></div>                </td>
              </tr>
              <tr>
                <td valign="top">Konturfarbe für<br />
                    Haupt-Kartenelemente<br />
                    <br />
                    <span  class="Text_10px">
                    Hinweis: Bei Anzeige von Bundesländern mit der Raumgliederung auf Gemeindebasis und auch bei Rasterdarstellung werden keine Konturen angezeigt.</span>
                </td>
                <td valign="top">
               	  <div style="padding:5px; background-color:#eeeeee; height:70px;">
                  	<div style="border:0px; width:20px; height:2px; overflow:hidden; background-color:#<?php echo $_SESSION['Dokument']['Strichfarbe']; ?>;"></div>                
                  </div>  
                </td>
                <td valign="top">
                
                
                
                
                     
          <map name="colmap_Strichfarbe" id="id">
          <area shape="rect" coords="1,1,7,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=00FF00" target="_self" />
          <area shape="rect" coords="9,1,15,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=00FF33" target="_self" />
          <area shape="rect" coords="17,1,23,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=00FF66" target="_self" />
          <area shape="rect" coords="25,1,31,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=00FF99" target="_self" />
          <area shape="rect" coords="33,1,39,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=00FFCC" target="_self" />
          <area shape="rect" coords="41,1,47,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=00FFFF" target="_self" />

          <area shape="rect" coords="49,1,55,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=33FF00" target="_self" />
          <area shape="rect" coords="57,1,63,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=33FF33" target="_self" />
          <area shape="rect" coords="65,1,71,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=33FF66" target="_self" />
          <area shape="rect" coords="73,1,79,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=33FF99" target="_self" />
          <area shape="rect" coords="81,1,87,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=33FFCC" target="_self" />
          <area shape="rect" coords="89,1,95,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=33FFFF" target="_self" />
          <area shape="rect" coords="97,1,103,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=66FF00" target="_self" />
          <area shape="rect" coords="105,1,111,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=66FF33" target="_self" />
          <area shape="rect" coords="113,1,119,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=66FF66" target="_self" />

          <area shape="rect" coords="121,1,127,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=66FF99" target="_self" />
          <area shape="rect" coords="129,1,135,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=66FFCC" target="_self" />
          <area shape="rect" coords="137,1,143,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=66FFFF" target="_self" />
          <area shape="rect" coords="145,1,151,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=99FF00" target="_self" />
          <area shape="rect" coords="153,1,159,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=99FF33" target="_self" />
          <area shape="rect" coords="161,1,167,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=99FF66" target="_self" />
          <area shape="rect" coords="169,1,175,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=99FF99" target="_self" />
          <area shape="rect" coords="177,1,183,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=99FFCC" target="_self" />
          <area shape="rect" coords="185,1,191,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=99FFFF" target="_self" />

          <area shape="rect" coords="193,1,199,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=CCFF00" target="_self" />
          <area shape="rect" coords="201,1,207,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=CCFF33" target="_self" />
          <area shape="rect" coords="209,1,215,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=CCFF66" target="_self" />
          <area shape="rect" coords="217,1,223,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=CCFF99" target="_self" />
          <area shape="rect" coords="225,1,231,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=CCFFCC" target="_self" />
          <area shape="rect" coords="233,1,239,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=CCFFFF" target="_self" />
          <area shape="rect" coords="241,1,247,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=FFFF00" target="_self" />
          <area shape="rect" coords="249,1,255,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=FFFF33" target="_self" />
          <area shape="rect" coords="257,1,263,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=FFFF66" target="_self" />

          <area shape="rect" coords="265,1,271,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=FFFF99" target="_self" />
          <area shape="rect" coords="273,1,279,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=FFFFCC" target="_self" />
          <area shape="rect" coords="281,1,287,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=FFFFFF" target="_self" />
          <area shape="rect" coords="1,12,7,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=00CC00" target="_self" />
          <area shape="rect" coords="9,12,15,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=00CC33" target="_self" />
          <area shape="rect" coords="17,12,23,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=00CC66" target="_self" />
          <area shape="rect" coords="25,12,31,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=00CC99" target="_self" />
          <area shape="rect" coords="33,12,39,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=00CCCC" target="_self" />
          <area shape="rect" coords="41,12,47,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=00CCFF" target="_self" />

          <area shape="rect" coords="49,12,55,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=33CC00" target="_self" />
          <area shape="rect" coords="57,12,63,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=33CC33" target="_self" />
          <area shape="rect" coords="65,12,71,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=33CC66" target="_self" />
          <area shape="rect" coords="73,12,79,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=33CC99" target="_self" />
          <area shape="rect" coords="81,12,87,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=33CCCC" target="_self" />
          <area shape="rect" coords="89,12,95,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=33CCFF" target="_self" />
          <area shape="rect" coords="97,12,103,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=66CC00" target="_self" />
          <area shape="rect" coords="105,12,111,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=66CC33" target="_self" />
          <area shape="rect" coords="113,12,119,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=66CC66" target="_self" />

          <area shape="rect" coords="121,12,127,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=66CC99" target="_self" />
          <area shape="rect" coords="129,12,135,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=66CCCC" target="_self" />
          <area shape="rect" coords="137,12,143,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=66CCFF" target="_self" />
          <area shape="rect" coords="145,12,151,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=99CC00" target="_self" />
          <area shape="rect" coords="153,12,159,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=99CC33" target="_self" />
          <area shape="rect" coords="161,12,167,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=99CC66" target="_self" />
          <area shape="rect" coords="169,12,175,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=99CC99" target="_self" />
          <area shape="rect" coords="177,12,183,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=99CCCC" target="_self" />
          <area shape="rect" coords="185,12,191,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=99CCFF" target="_self" />

          <area shape="rect" coords="193,12,199,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=CCCC00" target="_self" />
          <area shape="rect" coords="201,12,207,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=CCCC33" target="_self" />
          <area shape="rect" coords="209,12,215,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=CCCC66" target="_self" />
          <area shape="rect" coords="217,12,223,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=CCCC99" target="_self" />
          <area shape="rect" coords="225,12,231,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=CCCCCC" target="_self" />
          <area shape="rect" coords="233,12,239,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=CCCCFF" target="_self" />
          <area shape="rect" coords="241,12,247,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=FFCC00" target="_self" />
          <area shape="rect" coords="249,12,255,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=FFCC33" target="_self" />
          <area shape="rect" coords="257,12,263,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=FFCC66" target="_self" />

          <area shape="rect" coords="265,12,271,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=FFCC99" target="_self" />
          <area shape="rect" coords="273,12,279,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=FFCCCC" target="_self" />
          <area shape="rect" coords="281,12,287,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=FFCCFF" target="_self" />
          <area shape="rect" coords="1,23,7,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=009900" target="_self" />
          <area shape="rect" coords="9,23,15,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=009933" target="_self" />
          <area shape="rect" coords="17,23,23,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=009966" target="_self" />
          <area shape="rect" coords="25,23,31,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=009999" target="_self" />
          <area shape="rect" coords="33,23,39,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=0099CC" target="_self" />
          <area shape="rect" coords="41,23,47,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=0099FF" target="_self" />

          <area shape="rect" coords="49,23,55,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=339900" target="_self" />
          <area shape="rect" coords="57,23,63,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=339933" target="_self" />
          <area shape="rect" coords="65,23,71,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=339966" target="_self" />
          <area shape="rect" coords="73,23,79,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=339999" target="_self" />
          <area shape="rect" coords="81,23,87,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=3399CC" target="_self" />
          <area shape="rect" coords="89,23,95,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=3399FF" target="_self" />
          <area shape="rect" coords="97,23,103,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=669900" target="_self" />
          <area shape="rect" coords="105,23,111,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=669933" target="_self" />
          <area shape="rect" coords="113,23,119,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=669966" target="_self" />

          <area shape="rect" coords="121,23,127,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=669999" target="_self" />
          <area shape="rect" coords="129,23,135,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=6699CC" target="_self" />
          <area shape="rect" coords="137,23,143,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=6699FF" target="_self" />
          <area shape="rect" coords="145,23,151,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=999900" target="_self" />
          <area shape="rect" coords="153,23,159,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=999933" target="_self" />
          <area shape="rect" coords="161,23,167,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=999966" target="_self" />
          <area shape="rect" coords="169,23,175,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=999999" target="_self" />
          <area shape="rect" coords="177,23,183,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=9999CC" target="_self" />
          <area shape="rect" coords="185,23,191,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=9999FF" target="_self" />

          <area shape="rect" coords="193,23,199,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=CC9900" target="_self" />
          <area shape="rect" coords="201,23,207,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=CC9933" target="_self" />
          <area shape="rect" coords="209,23,215,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=CC9966" target="_self" />
          <area shape="rect" coords="217,23,223,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=CC9999" target="_self" />
          <area shape="rect" coords="225,23,231,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=CC99CC" target="_self" />
          <area shape="rect" coords="233,23,239,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=CC99FF" target="_self" />
          <area shape="rect" coords="241,23,247,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=FF9900" target="_self" />
          <area shape="rect" coords="249,23,255,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=FF9933" target="_self" />
          <area shape="rect" coords="257,23,263,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=FF9966" target="_self" />

          <area shape="rect" coords="265,23,271,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=FF9999" target="_self" />
          <area shape="rect" coords="273,23,279,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=FF99CC" target="_self" />
          <area shape="rect" coords="281,23,287,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=FF99FF" target="_self" />
          <area shape="rect" coords="1,34,7,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=006600" target="_self" />
          <area shape="rect" coords="9,34,15,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=006633" target="_self" />
          <area shape="rect" coords="17,34,23,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=006666" target="_self" />
          <area shape="rect" coords="25,34,31,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=006699" target="_self" />
          <area shape="rect" coords="33,34,39,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=0066CC" target="_self" />
          <area shape="rect" coords="41,34,47,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=0066FF" target="_self" />

          <area shape="rect" coords="49,34,55,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=336600" target="_self" />
          <area shape="rect" coords="57,34,63,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=336633" target="_self" />
          <area shape="rect" coords="65,34,71,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=336666" target="_self" />
          <area shape="rect" coords="73,34,79,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=336699" target="_self" />
          <area shape="rect" coords="81,34,87,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=3366CC" target="_self" />
          <area shape="rect" coords="89,34,95,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=3366FF" target="_self" />
          <area shape="rect" coords="97,34,103,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=666600" target="_self" />
          <area shape="rect" coords="105,34,111,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=666633" target="_self" />
          <area shape="rect" coords="113,34,119,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=666666" target="_self" />

          <area shape="rect" coords="121,34,127,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=666699" target="_self" />
          <area shape="rect" coords="129,34,135,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=6666CC" target="_self" />
          <area shape="rect" coords="137,34,143,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=6666FF" target="_self" />
          <area shape="rect" coords="145,34,151,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=996600" target="_self" />
          <area shape="rect" coords="153,34,159,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=996633" target="_self" />
          <area shape="rect" coords="161,34,167,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=996666" target="_self" />
          <area shape="rect" coords="169,34,175,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=996699" target="_self" />
          <area shape="rect" coords="177,34,183,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=9966CC" target="_self" />
          <area shape="rect" coords="185,34,191,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=9966FF" target="_self" />

          <area shape="rect" coords="193,34,199,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=CC6600" target="_self" />
          <area shape="rect" coords="201,34,207,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=CC6633" target="_self" />
          <area shape="rect" coords="209,34,215,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=CC6666" target="_self" />
          <area shape="rect" coords="217,34,223,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=CC6699" target="_self" />
          <area shape="rect" coords="225,34,231,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=CC66CC" target="_self" />
          <area shape="rect" coords="233,34,239,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=CC66FF" target="_self" />
          <area shape="rect" coords="241,34,247,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=FF6600" target="_self" />
          <area shape="rect" coords="249,34,255,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=FF6633" target="_self" />
          <area shape="rect" coords="257,34,263,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=FF6666" target="_self" />

          <area shape="rect" coords="265,34,271,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=FF6699" target="_self" />
          <area shape="rect" coords="273,34,279,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=FF66CC" target="_self" />
          <area shape="rect" coords="281,34,287,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=FF66FF" target="_self" />
          <area shape="rect" coords="1,45,7,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=003300" target="_self" />
          <area shape="rect" coords="9,45,15,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=003333" target="_self" />
          <area shape="rect" coords="17,45,23,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=003366" target="_self" />
          <area shape="rect" coords="25,45,31,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=003399" target="_self" />
          <area shape="rect" coords="33,45,39,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=0033CC" target="_self" />
          <area shape="rect" coords="41,45,47,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=0033FF" target="_self" />

          <area shape="rect" coords="49,45,55,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=333300" target="_self" />
          <area shape="rect" coords="57,45,63,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=333333" target="_self" />
          <area shape="rect" coords="65,45,71,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=333366" target="_self" />
          <area shape="rect" coords="73,45,79,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=333399" target="_self" />
          <area shape="rect" coords="81,45,87,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=3333CC" target="_self" />
          <area shape="rect" coords="89,45,95,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=3333FF" target="_self" />
          <area shape="rect" coords="97,45,103,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=663300" target="_self" />
          <area shape="rect" coords="105,45,111,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=663333" target="_self" />
          <area shape="rect" coords="113,45,119,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=663366" target="_self" />

          <area shape="rect" coords="121,45,127,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=663399" target="_self" />
          <area shape="rect" coords="129,45,135,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=6633CC" target="_self" />
          <area shape="rect" coords="137,45,143,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=6633FF" target="_self" />
          <area shape="rect" coords="145,45,151,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=993300" target="_self" />
          <area shape="rect" coords="153,45,159,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=993333" target="_self" />
          <area shape="rect" coords="161,45,167,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=993366" target="_self" />
          <area shape="rect" coords="169,45,175,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=993399" target="_self" />
          <area shape="rect" coords="177,45,183,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=9933CC" target="_self" />
          <area shape="rect" coords="185,45,191,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=9933FF" target="_self" />

          <area shape="rect" coords="193,45,199,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=CC3300" target="_self" />
          <area shape="rect" coords="201,45,207,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=CC3333" target="_self" />
          <area shape="rect" coords="209,45,215,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=CC3366" target="_self" />
          <area shape="rect" coords="217,45,223,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=CC3399" target="_self" />
          <area shape="rect" coords="225,45,231,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=CC33CC" target="_self" />
          <area shape="rect" coords="233,45,239,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=CC33FF" target="_self" />
          <area shape="rect" coords="241,45,247,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=FF3300" target="_self" />
          <area shape="rect" coords="249,45,255,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=FF3333" target="_self" />
          <area shape="rect" coords="257,45,263,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=FF3366" target="_self" />

          <area shape="rect" coords="265,45,271,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=FF3399" target="_self" />
          <area shape="rect" coords="273,45,279,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=FF33CC" target="_self" />
          <area shape="rect" coords="281,45,287,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=FF33FF" target="_self" />
          <area shape="rect" coords="1,56,7,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=000000" target="_self" />
          <area shape="rect" coords="9,56,15,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=000033" target="_self" />
          <area shape="rect" coords="17,56,23,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=000066" target="_self" />
          <area shape="rect" coords="25,56,31,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=000099" target="_self" />
          <area shape="rect" coords="33,56,39,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=0000CC" target="_self" />
          <area shape="rect" coords="41,56,47,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=0000FF" target="_self" />

          <area shape="rect" coords="49,56,55,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=330000" target="_self" />
          <area shape="rect" coords="57,56,63,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=330033" target="_self" />
          <area shape="rect" coords="65,56,71,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=330066" target="_self" />
          <area shape="rect" coords="73,56,79,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=330099" target="_self" />
          <area shape="rect" coords="81,56,87,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=3300CC" target="_self" />
          <area shape="rect" coords="89,56,95,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=3300FF" target="_self" />
          <area shape="rect" coords="97,56,103,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=660000" target="_self" />
          <area shape="rect" coords="105,56,111,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=660033" target="_self" />
          <area shape="rect" coords="113,56,119,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=660066" target="_self" />

          <area shape="rect" coords="121,56,127,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=660099" target="_self" />
          <area shape="rect" coords="129,56,135,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=6600CC" target="_self" />
          <area shape="rect" coords="137,56,143,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=6600FF" target="_self" />
          <area shape="rect" coords="145,56,151,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=990000" target="_self" />
          <area shape="rect" coords="153,56,159,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=990033" target="_self" />
          <area shape="rect" coords="161,56,167,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=990066" target="_self" />
          <area shape="rect" coords="169,56,175,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=990099" target="_self" />
          <area shape="rect" coords="177,56,183,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=9900CC" target="_self" />
          <area shape="rect" coords="185,56,191,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=9900FF" target="_self" />

          <area shape="rect" coords="193,56,199,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=CC0000" target="_self" />
          <area shape="rect" coords="201,56,207,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=CC0033" target="_self" />
          <area shape="rect" coords="209,56,215,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=CC0066" target="_self" />
          <area shape="rect" coords="217,56,223,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=CC0099" target="_self" />
          <area shape="rect" coords="225,56,231,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=CC00CC" target="_self" />
          <area shape="rect" coords="233,56,239,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=CC00FF" target="_self" />
          <area shape="rect" coords="241,56,247,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=FF0000" target="_self" />
          <area shape="rect" coords="249,56,255,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=FF0033" target="_self" />
          <area shape="rect" coords="257,56,263,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=FF0066" target="_self" />

          <area shape="rect" coords="265,56,271,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=FF0099" target="_self" />
          <area shape="rect" coords="273,56,279,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=FF00CC" target="_self" />
          <area shape="rect" coords="281,56,287,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=FF00FF" target="_self" />
          </map>
          <a> <img usemap="#colmap_Strichfarbe" src="gfx/colortable.gif" border="0" width="289" height="67" alt="" /></a> 
          <br />
          <map name="colmap_Strichfarbe_grau" id="id">
          <area shape="rect" coords="1,1,7,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=000000" target="_self" />
          <area shape="rect" coords="9,1,15,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=111111" target="_self" />
          <area shape="rect" coords="17,1,23,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=222222" target="_self" />
          <area shape="rect" coords="25,1,31,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=333333" target="_self" />
          <area shape="rect" coords="33,1,39,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=444444" target="_self" />
          <area shape="rect" coords="41,1,47,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=555555" target="_self" />
          <area shape="rect" coords="49,1,55,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=666666" target="_self" />
          <area shape="rect" coords="57,1,63,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=777777" target="_self" />
          <area shape="rect" coords="65,1,71,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=888888" target="_self" />
          <area shape="rect" coords="73,1,79,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=999999" target="_self" />
          <area shape="rect" coords="81,1,87,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=aaaaaa" target="_self" />
          <area shape="rect" coords="89,1,95,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=bbbbbb" target="_self" />
          <area shape="rect" coords="97,1,103,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=cccccc" target="_self" />
          <area shape="rect" coords="105,1,111,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=dddddd" target="_self" />
          <area shape="rect" coords="113,1,119,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=eeeeee" target="_self" />
          <area shape="rect" coords="121,1,127,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe=ffffff" target="_self" />
          </map>
          <a> <img usemap="#colmap_Strichfarbe_grau" src="gfx/colortable_grau.png" border="0" width="129" height="12" alt="" /></a>
          
          
          <br />
          Selbst definierter Hex-RGB-Wert: #
				  <input name="Strichfarbe" type="text" id="Strichfarbe" value="<?php echo $_SESSION['Dokument']['Strichfarbe']; ?>" size="8" maxlength="6" />
                    <input name="button7" type="submit" class="button_blau_abschicken" id="button7" style="cursor: pointer;" value="OK" />
                    <br />                </td>
              </tr>
              <tr>
                <td colspan="3" valign="top">
                  <br />
                  <br />
                  <div style="height:0px; border-bottom:1px solid #999999; margin-top:0px; margin-bottom:7px;"></div>                </td>
              </tr>
              <tr>
                <td valign="top">Textfarbe</td>
                <td align="left" valign="top"><div style="padding:5px; background-color:#eeeeee;  height:70px; color:#<?php echo $_SESSION['Dokument']['Textfarbe_Labels']; ?>;">
                Text
                </div></td>
                <td align="left" valign="top">
                
                
                     
          <map name="colmap_textfarbe" id="id">
          <area shape="rect" coords="1,1,7,10" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=00FF00" target="_self" />
          <area shape="rect" coords="9,1,15,10" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=00FF33" target="_self" />
          <area shape="rect" coords="17,1,23,10" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=00FF66" target="_self" />
          <area shape="rect" coords="25,1,31,10" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=00FF99" target="_self" />
          <area shape="rect" coords="33,1,39,10" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=00FFCC" target="_self" />
          <area shape="rect" coords="41,1,47,10" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=00FFFF" target="_self" />

          <area shape="rect" coords="49,1,55,10" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=33FF00" target="_self" />
          <area shape="rect" coords="57,1,63,10" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=33FF33" target="_self" />
          <area shape="rect" coords="65,1,71,10" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=33FF66" target="_self" />
          <area shape="rect" coords="73,1,79,10" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=33FF99" target="_self" />
          <area shape="rect" coords="81,1,87,10" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=33FFCC" target="_self" />
          <area shape="rect" coords="89,1,95,10" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=33FFFF" target="_self" />
          <area shape="rect" coords="97,1,103,10" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=66FF00" target="_self" />
          <area shape="rect" coords="105,1,111,10" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=66FF33" target="_self" />
          <area shape="rect" coords="113,1,119,10" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=66FF66" target="_self" />

          <area shape="rect" coords="121,1,127,10" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=66FF99" target="_self" />
          <area shape="rect" coords="129,1,135,10" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=66FFCC" target="_self" />
          <area shape="rect" coords="137,1,143,10" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=66FFFF" target="_self" />
          <area shape="rect" coords="145,1,151,10" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=99FF00" target="_self" />
          <area shape="rect" coords="153,1,159,10" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=99FF33" target="_self" />
          <area shape="rect" coords="161,1,167,10" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=99FF66" target="_self" />
          <area shape="rect" coords="169,1,175,10" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=99FF99" target="_self" />
          <area shape="rect" coords="177,1,183,10" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=99FFCC" target="_self" />
          <area shape="rect" coords="185,1,191,10" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=99FFFF" target="_self" />

          <area shape="rect" coords="193,1,199,10" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=CCFF00" target="_self" />
          <area shape="rect" coords="201,1,207,10" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=CCFF33" target="_self" />
          <area shape="rect" coords="209,1,215,10" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=CCFF66" target="_self" />
          <area shape="rect" coords="217,1,223,10" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=CCFF99" target="_self" />
          <area shape="rect" coords="225,1,231,10" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=CCFFCC" target="_self" />
          <area shape="rect" coords="233,1,239,10" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=CCFFFF" target="_self" />
          <area shape="rect" coords="241,1,247,10" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=FFFF00" target="_self" />
          <area shape="rect" coords="249,1,255,10" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=FFFF33" target="_self" />
          <area shape="rect" coords="257,1,263,10" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=FFFF66" target="_self" />

          <area shape="rect" coords="265,1,271,10" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=FFFF99" target="_self" />
          <area shape="rect" coords="273,1,279,10" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=FFFFCC" target="_self" />
          <area shape="rect" coords="281,1,287,10" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=FFFFFF" target="_self" />
          <area shape="rect" coords="1,12,7,21" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=00CC00" target="_self" />
          <area shape="rect" coords="9,12,15,21" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=00CC33" target="_self" />
          <area shape="rect" coords="17,12,23,21" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=00CC66" target="_self" />
          <area shape="rect" coords="25,12,31,21" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=00CC99" target="_self" />
          <area shape="rect" coords="33,12,39,21" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=00CCCC" target="_self" />
          <area shape="rect" coords="41,12,47,21" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=00CCFF" target="_self" />

          <area shape="rect" coords="49,12,55,21" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=33CC00" target="_self" />
          <area shape="rect" coords="57,12,63,21" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=33CC33" target="_self" />
          <area shape="rect" coords="65,12,71,21" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=33CC66" target="_self" />
          <area shape="rect" coords="73,12,79,21" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=33CC99" target="_self" />
          <area shape="rect" coords="81,12,87,21" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=33CCCC" target="_self" />
          <area shape="rect" coords="89,12,95,21" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=33CCFF" target="_self" />
          <area shape="rect" coords="97,12,103,21" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=66CC00" target="_self" />
          <area shape="rect" coords="105,12,111,21" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=66CC33" target="_self" />
          <area shape="rect" coords="113,12,119,21" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=66CC66" target="_self" />

          <area shape="rect" coords="121,12,127,21" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=66CC99" target="_self" />
          <area shape="rect" coords="129,12,135,21" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=66CCCC" target="_self" />
          <area shape="rect" coords="137,12,143,21" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=66CCFF" target="_self" />
          <area shape="rect" coords="145,12,151,21" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=99CC00" target="_self" />
          <area shape="rect" coords="153,12,159,21" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=99CC33" target="_self" />
          <area shape="rect" coords="161,12,167,21" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=99CC66" target="_self" />
          <area shape="rect" coords="169,12,175,21" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=99CC99" target="_self" />
          <area shape="rect" coords="177,12,183,21" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=99CCCC" target="_self" />
          <area shape="rect" coords="185,12,191,21" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=99CCFF" target="_self" />

          <area shape="rect" coords="193,12,199,21" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=CCCC00" target="_self" />
          <area shape="rect" coords="201,12,207,21" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=CCCC33" target="_self" />
          <area shape="rect" coords="209,12,215,21" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=CCCC66" target="_self" />
          <area shape="rect" coords="217,12,223,21" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=CCCC99" target="_self" />
          <area shape="rect" coords="225,12,231,21" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=CCCCCC" target="_self" />
          <area shape="rect" coords="233,12,239,21" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=CCCCFF" target="_self" />
          <area shape="rect" coords="241,12,247,21" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=FFCC00" target="_self" />
          <area shape="rect" coords="249,12,255,21" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=FFCC33" target="_self" />
          <area shape="rect" coords="257,12,263,21" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=FFCC66" target="_self" />

          <area shape="rect" coords="265,12,271,21" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=FFCC99" target="_self" />
          <area shape="rect" coords="273,12,279,21" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=FFCCCC" target="_self" />
          <area shape="rect" coords="281,12,287,21" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=FFCCFF" target="_self" />
          <area shape="rect" coords="1,23,7,32" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=009900" target="_self" />
          <area shape="rect" coords="9,23,15,32" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=009933" target="_self" />
          <area shape="rect" coords="17,23,23,32" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=009966" target="_self" />
          <area shape="rect" coords="25,23,31,32" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=009999" target="_self" />
          <area shape="rect" coords="33,23,39,32" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=0099CC" target="_self" />
          <area shape="rect" coords="41,23,47,32" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=0099FF" target="_self" />

          <area shape="rect" coords="49,23,55,32" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=339900" target="_self" />
          <area shape="rect" coords="57,23,63,32" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=339933" target="_self" />
          <area shape="rect" coords="65,23,71,32" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=339966" target="_self" />
          <area shape="rect" coords="73,23,79,32" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=339999" target="_self" />
          <area shape="rect" coords="81,23,87,32" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=3399CC" target="_self" />
          <area shape="rect" coords="89,23,95,32" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=3399FF" target="_self" />
          <area shape="rect" coords="97,23,103,32" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=669900" target="_self" />
          <area shape="rect" coords="105,23,111,32" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=669933" target="_self" />
          <area shape="rect" coords="113,23,119,32" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=669966" target="_self" />

          <area shape="rect" coords="121,23,127,32" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=669999" target="_self" />
          <area shape="rect" coords="129,23,135,32" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=6699CC" target="_self" />
          <area shape="rect" coords="137,23,143,32" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=6699FF" target="_self" />
          <area shape="rect" coords="145,23,151,32" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=999900" target="_self" />
          <area shape="rect" coords="153,23,159,32" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=999933" target="_self" />
          <area shape="rect" coords="161,23,167,32" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=999966" target="_self" />
          <area shape="rect" coords="169,23,175,32" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=999999" target="_self" />
          <area shape="rect" coords="177,23,183,32" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=9999CC" target="_self" />
          <area shape="rect" coords="185,23,191,32" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=9999FF" target="_self" />

          <area shape="rect" coords="193,23,199,32" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=CC9900" target="_self" />
          <area shape="rect" coords="201,23,207,32" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=CC9933" target="_self" />
          <area shape="rect" coords="209,23,215,32" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=CC9966" target="_self" />
          <area shape="rect" coords="217,23,223,32" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=CC9999" target="_self" />
          <area shape="rect" coords="225,23,231,32" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=CC99CC" target="_self" />
          <area shape="rect" coords="233,23,239,32" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=CC99FF" target="_self" />
          <area shape="rect" coords="241,23,247,32" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=FF9900" target="_self" />
          <area shape="rect" coords="249,23,255,32" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=FF9933" target="_self" />
          <area shape="rect" coords="257,23,263,32" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=FF9966" target="_self" />

          <area shape="rect" coords="265,23,271,32" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=FF9999" target="_self" />
          <area shape="rect" coords="273,23,279,32" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=FF99CC" target="_self" />
          <area shape="rect" coords="281,23,287,32" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=FF99FF" target="_self" />
          <area shape="rect" coords="1,34,7,43" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=006600" target="_self" />
          <area shape="rect" coords="9,34,15,43" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=006633" target="_self" />
          <area shape="rect" coords="17,34,23,43" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=006666" target="_self" />
          <area shape="rect" coords="25,34,31,43" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=006699" target="_self" />
          <area shape="rect" coords="33,34,39,43" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=0066CC" target="_self" />
          <area shape="rect" coords="41,34,47,43" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=0066FF" target="_self" />

          <area shape="rect" coords="49,34,55,43" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=336600" target="_self" />
          <area shape="rect" coords="57,34,63,43" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=336633" target="_self" />
          <area shape="rect" coords="65,34,71,43" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=336666" target="_self" />
          <area shape="rect" coords="73,34,79,43" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=336699" target="_self" />
          <area shape="rect" coords="81,34,87,43" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=3366CC" target="_self" />
          <area shape="rect" coords="89,34,95,43" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=3366FF" target="_self" />
          <area shape="rect" coords="97,34,103,43" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=666600" target="_self" />
          <area shape="rect" coords="105,34,111,43" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=666633" target="_self" />
          <area shape="rect" coords="113,34,119,43" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=666666" target="_self" />

          <area shape="rect" coords="121,34,127,43" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=666699" target="_self" />
          <area shape="rect" coords="129,34,135,43" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=6666CC" target="_self" />
          <area shape="rect" coords="137,34,143,43" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=6666FF" target="_self" />
          <area shape="rect" coords="145,34,151,43" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=996600" target="_self" />
          <area shape="rect" coords="153,34,159,43" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=996633" target="_self" />
          <area shape="rect" coords="161,34,167,43" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=996666" target="_self" />
          <area shape="rect" coords="169,34,175,43" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=996699" target="_self" />
          <area shape="rect" coords="177,34,183,43" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=9966CC" target="_self" />
          <area shape="rect" coords="185,34,191,43" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=9966FF" target="_self" />

          <area shape="rect" coords="193,34,199,43" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=CC6600" target="_self" />
          <area shape="rect" coords="201,34,207,43" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=CC6633" target="_self" />
          <area shape="rect" coords="209,34,215,43" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=CC6666" target="_self" />
          <area shape="rect" coords="217,34,223,43" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=CC6699" target="_self" />
          <area shape="rect" coords="225,34,231,43" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=CC66CC" target="_self" />
          <area shape="rect" coords="233,34,239,43" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=CC66FF" target="_self" />
          <area shape="rect" coords="241,34,247,43" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=FF6600" target="_self" />
          <area shape="rect" coords="249,34,255,43" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=FF6633" target="_self" />
          <area shape="rect" coords="257,34,263,43" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=FF6666" target="_self" />

          <area shape="rect" coords="265,34,271,43" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=FF6699" target="_self" />
          <area shape="rect" coords="273,34,279,43" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=FF66CC" target="_self" />
          <area shape="rect" coords="281,34,287,43" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=FF66FF" target="_self" />
          <area shape="rect" coords="1,45,7,54" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=003300" target="_self" />
          <area shape="rect" coords="9,45,15,54" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=003333" target="_self" />
          <area shape="rect" coords="17,45,23,54" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=003366" target="_self" />
          <area shape="rect" coords="25,45,31,54" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=003399" target="_self" />
          <area shape="rect" coords="33,45,39,54" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=0033CC" target="_self" />
          <area shape="rect" coords="41,45,47,54" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=0033FF" target="_self" />

          <area shape="rect" coords="49,45,55,54" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=333300" target="_self" />
          <area shape="rect" coords="57,45,63,54" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=333333" target="_self" />
          <area shape="rect" coords="65,45,71,54" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=333366" target="_self" />
          <area shape="rect" coords="73,45,79,54" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=333399" target="_self" />
          <area shape="rect" coords="81,45,87,54" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=3333CC" target="_self" />
          <area shape="rect" coords="89,45,95,54" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=3333FF" target="_self" />
          <area shape="rect" coords="97,45,103,54" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=663300" target="_self" />
          <area shape="rect" coords="105,45,111,54" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=663333" target="_self" />
          <area shape="rect" coords="113,45,119,54" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=663366" target="_self" />

          <area shape="rect" coords="121,45,127,54" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=663399" target="_self" />
          <area shape="rect" coords="129,45,135,54" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=6633CC" target="_self" />
          <area shape="rect" coords="137,45,143,54" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=6633FF" target="_self" />
          <area shape="rect" coords="145,45,151,54" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=993300" target="_self" />
          <area shape="rect" coords="153,45,159,54" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=993333" target="_self" />
          <area shape="rect" coords="161,45,167,54" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=993366" target="_self" />
          <area shape="rect" coords="169,45,175,54" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=993399" target="_self" />
          <area shape="rect" coords="177,45,183,54" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=9933CC" target="_self" />
          <area shape="rect" coords="185,45,191,54" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=9933FF" target="_self" />

          <area shape="rect" coords="193,45,199,54" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=CC3300" target="_self" />
          <area shape="rect" coords="201,45,207,54" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=CC3333" target="_self" />
          <area shape="rect" coords="209,45,215,54" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=CC3366" target="_self" />
          <area shape="rect" coords="217,45,223,54" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=CC3399" target="_self" />
          <area shape="rect" coords="225,45,231,54" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=CC33CC" target="_self" />
          <area shape="rect" coords="233,45,239,54" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=CC33FF" target="_self" />
          <area shape="rect" coords="241,45,247,54" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=FF3300" target="_self" />
          <area shape="rect" coords="249,45,255,54" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=FF3333" target="_self" />
          <area shape="rect" coords="257,45,263,54" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=FF3366" target="_self" />

          <area shape="rect" coords="265,45,271,54" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=FF3399" target="_self" />
          <area shape="rect" coords="273,45,279,54" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=FF33CC" target="_self" />
          <area shape="rect" coords="281,45,287,54" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=FF33FF" target="_self" />
          <area shape="rect" coords="1,56,7,65" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=000000" target="_self" />
          <area shape="rect" coords="9,56,15,65" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=000033" target="_self" />
          <area shape="rect" coords="17,56,23,65" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=000066" target="_self" />
          <area shape="rect" coords="25,56,31,65" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=000099" target="_self" />
          <area shape="rect" coords="33,56,39,65" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=0000CC" target="_self" />
          <area shape="rect" coords="41,56,47,65" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=0000FF" target="_self" />

          <area shape="rect" coords="49,56,55,65" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=330000" target="_self" />
          <area shape="rect" coords="57,56,63,65" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=330033" target="_self" />
          <area shape="rect" coords="65,56,71,65" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=330066" target="_self" />
          <area shape="rect" coords="73,56,79,65" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=330099" target="_self" />
          <area shape="rect" coords="81,56,87,65" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=3300CC" target="_self" />
          <area shape="rect" coords="89,56,95,65" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=3300FF" target="_self" />
          <area shape="rect" coords="97,56,103,65" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=660000" target="_self" />
          <area shape="rect" coords="105,56,111,65" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=660033" target="_self" />
          <area shape="rect" coords="113,56,119,65" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=660066" target="_self" />

          <area shape="rect" coords="121,56,127,65" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=660099" target="_self" />
          <area shape="rect" coords="129,56,135,65" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=6600CC" target="_self" />
          <area shape="rect" coords="137,56,143,65" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=6600FF" target="_self" />
          <area shape="rect" coords="145,56,151,65" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=990000" target="_self" />
          <area shape="rect" coords="153,56,159,65" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=990033" target="_self" />
          <area shape="rect" coords="161,56,167,65" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=990066" target="_self" />
          <area shape="rect" coords="169,56,175,65" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=990099" target="_self" />
          <area shape="rect" coords="177,56,183,65" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=9900CC" target="_self" />
          <area shape="rect" coords="185,56,191,65" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=9900FF" target="_self" />

          <area shape="rect" coords="193,56,199,65" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=CC0000" target="_self" />
          <area shape="rect" coords="201,56,207,65" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=CC0033" target="_self" />
          <area shape="rect" coords="209,56,215,65" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=CC0066" target="_self" />
          <area shape="rect" coords="217,56,223,65" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=CC0099" target="_self" />
          <area shape="rect" coords="225,56,231,65" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=CC00CC" target="_self" />
          <area shape="rect" coords="233,56,239,65" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=CC00FF" target="_self" />
          <area shape="rect" coords="241,56,247,65" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=FF0000" target="_self" />
          <area shape="rect" coords="249,56,255,65" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=FF0033" target="_self" />
          <area shape="rect" coords="257,56,263,65" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=FF0066" target="_self" />

          <area shape="rect" coords="265,56,271,65" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=FF0099" target="_self" />
          <area shape="rect" coords="273,56,279,65" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=FF00CC" target="_self" />
          <area shape="rect" coords="281,56,287,65" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=FF00FF" target="_self" /></map>
          <a> <img usemap="#colmap_textfarbe" src="gfx/colortable.gif" border="0" width="289" height="67" alt="" /></a> 
          <br />
          <map name="colmap_textfarbe_grau" id="id">
          <area shape="rect" coords="1,1,7,10" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=000000" target="_self" />
          <area shape="rect" coords="9,1,15,10" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=111111" target="_self" />
          <area shape="rect" coords="17,1,23,10" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=222222" target="_self" />
          <area shape="rect" coords="25,1,31,10" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=333333" target="_self" />
          <area shape="rect" coords="33,1,39,10" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=444444" target="_self" />
          <area shape="rect" coords="41,1,47,10" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=555555" target="_self" />
          <area shape="rect" coords="49,1,55,10" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=666666" target="_self" />
          <area shape="rect" coords="57,1,63,10" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=777777" target="_self" />
          <area shape="rect" coords="65,1,71,10" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=888888" target="_self" />
          <area shape="rect" coords="73,1,79,10" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=999999" target="_self" />
          <area shape="rect" coords="81,1,87,10" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=aaaaaa" target="_self" />
          <area shape="rect" coords="89,1,95,10" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=bbbbbb" target="_self" />
          <area shape="rect" coords="97,1,103,10" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=cccccc" target="_self" />
          <area shape="rect" coords="105,1,111,10" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=dddddd" target="_self" />
          <area shape="rect" coords="113,1,119,10" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=eeeeee" target="_self" />
          <area shape="rect" coords="121,1,127,10" href="svg_zeichenvorschrift_farbe.php?textfarbe_labels=ffffff" target="_self" />
          </map>
          <a> <img usemap="#colmap_textfarbe_grau" src="gfx/colortable_grau.png" border="0" width="129" height="12" alt="" /></a>
          
          
          <br />
          Selbst definierter Hex-RGB-Wert: #
				  <input name="Textfarbe_Labels" type="text" id="Textfarbe_Labels" value="<?php echo $_SESSION['Dokument']['Textfarbe_Labels']; ?>" size="8" maxlength="6" />
                    <input name="button7" type="submit" class="button_blau_abschicken" id="button7" style="cursor: pointer;" value="OK" />
                    <br /></td>
              </tr>
              
          
              <tr>
                <td height="26" colspan="3" valign="top"><br />
                  <br />
                <div style="height:0px; border-bottom:1px solid #999999; margin-top:0px; margin-bottom:7px;"></div>                 
                
                </td>
              </tr>
<!--              <tr>
              <td valign="top">Textfarbe</td>
                <td colspan="2" valign="top">Bestimmen Sie hier, welche Farbe die Kartenbeschriftung (Gemeinde-, Kreis,- oder Landesnamen) erhalten soll. <br />
                  Ein angepasster Farbton kann die Leserlichkeit der Karte deutlich erhöhen.<br />
                  <br />                </td>
              </tr>
              <tr>
                <td valign="top">&nbsp;</td>
                <td align="left" valign="top"><div style="border:solid #666666 1px; width:20px; height:20px; overflow:hidden; background-color:#<?php echo $_SESSION['Dokument']['Farbe_Zusatz_VG']; ?>;"></div></td>
                <td align="left" valign="top">
                
                
                     
          <map name="colmap_Farbe_Zusatz_VG" id="id">
          <area shape="rect" coords="1,1,7,10" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=00FF00" target="_self" />
          <area shape="rect" coords="9,1,15,10" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=00FF33" target="_self" />
          <area shape="rect" coords="17,1,23,10" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=00FF66" target="_self" />
          <area shape="rect" coords="25,1,31,10" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=00FF99" target="_self" />
          <area shape="rect" coords="33,1,39,10" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=00FFCC" target="_self" />
          <area shape="rect" coords="41,1,47,10" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=00FFFF" target="_self" />

          <area shape="rect" coords="49,1,55,10" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=33FF00" target="_self" />
          <area shape="rect" coords="57,1,63,10" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=33FF33" target="_self" />
          <area shape="rect" coords="65,1,71,10" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=33FF66" target="_self" />
          <area shape="rect" coords="73,1,79,10" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=33FF99" target="_self" />
          <area shape="rect" coords="81,1,87,10" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=33FFCC" target="_self" />
          <area shape="rect" coords="89,1,95,10" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=33FFFF" target="_self" />
          <area shape="rect" coords="97,1,103,10" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=66FF00" target="_self" />
          <area shape="rect" coords="105,1,111,10" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=66FF33" target="_self" />
          <area shape="rect" coords="113,1,119,10" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=66FF66" target="_self" />

          <area shape="rect" coords="121,1,127,10" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=66FF99" target="_self" />
          <area shape="rect" coords="129,1,135,10" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=66FFCC" target="_self" />
          <area shape="rect" coords="137,1,143,10" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=66FFFF" target="_self" />
          <area shape="rect" coords="145,1,151,10" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=99FF00" target="_self" />
          <area shape="rect" coords="153,1,159,10" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=99FF33" target="_self" />
          <area shape="rect" coords="161,1,167,10" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=99FF66" target="_self" />
          <area shape="rect" coords="169,1,175,10" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=99FF99" target="_self" />
          <area shape="rect" coords="177,1,183,10" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=99FFCC" target="_self" />
          <area shape="rect" coords="185,1,191,10" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=99FFFF" target="_self" />

          <area shape="rect" coords="193,1,199,10" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=CCFF00" target="_self" />
          <area shape="rect" coords="201,1,207,10" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=CCFF33" target="_self" />
          <area shape="rect" coords="209,1,215,10" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=CCFF66" target="_self" />
          <area shape="rect" coords="217,1,223,10" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=CCFF99" target="_self" />
          <area shape="rect" coords="225,1,231,10" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=CCFFCC" target="_self" />
          <area shape="rect" coords="233,1,239,10" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=CCFFFF" target="_self" />
          <area shape="rect" coords="241,1,247,10" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=FFFF00" target="_self" />
          <area shape="rect" coords="249,1,255,10" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=FFFF33" target="_self" />
          <area shape="rect" coords="257,1,263,10" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=FFFF66" target="_self" />

          <area shape="rect" coords="265,1,271,10" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=FFFF99" target="_self" />
          <area shape="rect" coords="273,1,279,10" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=FFFFCC" target="_self" />
          <area shape="rect" coords="281,1,287,10" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=FFFFFF" target="_self" />
          <area shape="rect" coords="1,12,7,21" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=00CC00" target="_self" />
          <area shape="rect" coords="9,12,15,21" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=00CC33" target="_self" />
          <area shape="rect" coords="17,12,23,21" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=00CC66" target="_self" />
          <area shape="rect" coords="25,12,31,21" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=00CC99" target="_self" />
          <area shape="rect" coords="33,12,39,21" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=00CCCC" target="_self" />
          <area shape="rect" coords="41,12,47,21" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=00CCFF" target="_self" />

          <area shape="rect" coords="49,12,55,21" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=33CC00" target="_self" />
          <area shape="rect" coords="57,12,63,21" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=33CC33" target="_self" />
          <area shape="rect" coords="65,12,71,21" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=33CC66" target="_self" />
          <area shape="rect" coords="73,12,79,21" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=33CC99" target="_self" />
          <area shape="rect" coords="81,12,87,21" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=33CCCC" target="_self" />
          <area shape="rect" coords="89,12,95,21" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=33CCFF" target="_self" />
          <area shape="rect" coords="97,12,103,21" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=66CC00" target="_self" />
          <area shape="rect" coords="105,12,111,21" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=66CC33" target="_self" />
          <area shape="rect" coords="113,12,119,21" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=66CC66" target="_self" />

          <area shape="rect" coords="121,12,127,21" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=66CC99" target="_self" />
          <area shape="rect" coords="129,12,135,21" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=66CCCC" target="_self" />
          <area shape="rect" coords="137,12,143,21" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=66CCFF" target="_self" />
          <area shape="rect" coords="145,12,151,21" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=99CC00" target="_self" />
          <area shape="rect" coords="153,12,159,21" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=99CC33" target="_self" />
          <area shape="rect" coords="161,12,167,21" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=99CC66" target="_self" />
          <area shape="rect" coords="169,12,175,21" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=99CC99" target="_self" />
          <area shape="rect" coords="177,12,183,21" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=99CCCC" target="_self" />
          <area shape="rect" coords="185,12,191,21" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=99CCFF" target="_self" />

          <area shape="rect" coords="193,12,199,21" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=CCCC00" target="_self" />
          <area shape="rect" coords="201,12,207,21" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=CCCC33" target="_self" />
          <area shape="rect" coords="209,12,215,21" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=CCCC66" target="_self" />
          <area shape="rect" coords="217,12,223,21" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=CCCC99" target="_self" />
          <area shape="rect" coords="225,12,231,21" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=CCCCCC" target="_self" />
          <area shape="rect" coords="233,12,239,21" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=CCCCFF" target="_self" />
          <area shape="rect" coords="241,12,247,21" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=FFCC00" target="_self" />
          <area shape="rect" coords="249,12,255,21" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=FFCC33" target="_self" />
          <area shape="rect" coords="257,12,263,21" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=FFCC66" target="_self" />

          <area shape="rect" coords="265,12,271,21" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=FFCC99" target="_self" />
          <area shape="rect" coords="273,12,279,21" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=FFCCCC" target="_self" />
          <area shape="rect" coords="281,12,287,21" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=FFCCFF" target="_self" />
          <area shape="rect" coords="1,23,7,32" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=009900" target="_self" />
          <area shape="rect" coords="9,23,15,32" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=009933" target="_self" />
          <area shape="rect" coords="17,23,23,32" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=009966" target="_self" />
          <area shape="rect" coords="25,23,31,32" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=009999" target="_self" />
          <area shape="rect" coords="33,23,39,32" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=0099CC" target="_self" />
          <area shape="rect" coords="41,23,47,32" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=0099FF" target="_self" />

          <area shape="rect" coords="49,23,55,32" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=339900" target="_self" />
          <area shape="rect" coords="57,23,63,32" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=339933" target="_self" />
          <area shape="rect" coords="65,23,71,32" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=339966" target="_self" />
          <area shape="rect" coords="73,23,79,32" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=339999" target="_self" />
          <area shape="rect" coords="81,23,87,32" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=3399CC" target="_self" />
          <area shape="rect" coords="89,23,95,32" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=3399FF" target="_self" />
          <area shape="rect" coords="97,23,103,32" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=669900" target="_self" />
          <area shape="rect" coords="105,23,111,32" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=669933" target="_self" />
          <area shape="rect" coords="113,23,119,32" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=669966" target="_self" />

          <area shape="rect" coords="121,23,127,32" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=669999" target="_self" />
          <area shape="rect" coords="129,23,135,32" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=6699CC" target="_self" />
          <area shape="rect" coords="137,23,143,32" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=6699FF" target="_self" />
          <area shape="rect" coords="145,23,151,32" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=999900" target="_self" />
          <area shape="rect" coords="153,23,159,32" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=999933" target="_self" />
          <area shape="rect" coords="161,23,167,32" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=999966" target="_self" />
          <area shape="rect" coords="169,23,175,32" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=999999" target="_self" />
          <area shape="rect" coords="177,23,183,32" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=9999CC" target="_self" />
          <area shape="rect" coords="185,23,191,32" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=9999FF" target="_self" />

          <area shape="rect" coords="193,23,199,32" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=CC9900" target="_self" />
          <area shape="rect" coords="201,23,207,32" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=CC9933" target="_self" />
          <area shape="rect" coords="209,23,215,32" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=CC9966" target="_self" />
          <area shape="rect" coords="217,23,223,32" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=CC9999" target="_self" />
          <area shape="rect" coords="225,23,231,32" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=CC99CC" target="_self" />
          <area shape="rect" coords="233,23,239,32" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=CC99FF" target="_self" />
          <area shape="rect" coords="241,23,247,32" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=FF9900" target="_self" />
          <area shape="rect" coords="249,23,255,32" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=FF9933" target="_self" />
          <area shape="rect" coords="257,23,263,32" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=FF9966" target="_self" />

          <area shape="rect" coords="265,23,271,32" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=FF9999" target="_self" />
          <area shape="rect" coords="273,23,279,32" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=FF99CC" target="_self" />
          <area shape="rect" coords="281,23,287,32" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=FF99FF" target="_self" />
          <area shape="rect" coords="1,34,7,43" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=006600" target="_self" />
          <area shape="rect" coords="9,34,15,43" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=006633" target="_self" />
          <area shape="rect" coords="17,34,23,43" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=006666" target="_self" />
          <area shape="rect" coords="25,34,31,43" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=006699" target="_self" />
          <area shape="rect" coords="33,34,39,43" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=0066CC" target="_self" />
          <area shape="rect" coords="41,34,47,43" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=0066FF" target="_self" />

          <area shape="rect" coords="49,34,55,43" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=336600" target="_self" />
          <area shape="rect" coords="57,34,63,43" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=336633" target="_self" />
          <area shape="rect" coords="65,34,71,43" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=336666" target="_self" />
          <area shape="rect" coords="73,34,79,43" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=336699" target="_self" />
          <area shape="rect" coords="81,34,87,43" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=3366CC" target="_self" />
          <area shape="rect" coords="89,34,95,43" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=3366FF" target="_self" />
          <area shape="rect" coords="97,34,103,43" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=666600" target="_self" />
          <area shape="rect" coords="105,34,111,43" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=666633" target="_self" />
          <area shape="rect" coords="113,34,119,43" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=666666" target="_self" />

          <area shape="rect" coords="121,34,127,43" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=666699" target="_self" />
          <area shape="rect" coords="129,34,135,43" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=6666CC" target="_self" />
          <area shape="rect" coords="137,34,143,43" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=6666FF" target="_self" />
          <area shape="rect" coords="145,34,151,43" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=996600" target="_self" />
          <area shape="rect" coords="153,34,159,43" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=996633" target="_self" />
          <area shape="rect" coords="161,34,167,43" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=996666" target="_self" />
          <area shape="rect" coords="169,34,175,43" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=996699" target="_self" />
          <area shape="rect" coords="177,34,183,43" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=9966CC" target="_self" />
          <area shape="rect" coords="185,34,191,43" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=9966FF" target="_self" />

          <area shape="rect" coords="193,34,199,43" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=CC6600" target="_self" />
          <area shape="rect" coords="201,34,207,43" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=CC6633" target="_self" />
          <area shape="rect" coords="209,34,215,43" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=CC6666" target="_self" />
          <area shape="rect" coords="217,34,223,43" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=CC6699" target="_self" />
          <area shape="rect" coords="225,34,231,43" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=CC66CC" target="_self" />
          <area shape="rect" coords="233,34,239,43" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=CC66FF" target="_self" />
          <area shape="rect" coords="241,34,247,43" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=FF6600" target="_self" />
          <area shape="rect" coords="249,34,255,43" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=FF6633" target="_self" />
          <area shape="rect" coords="257,34,263,43" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=FF6666" target="_self" />

          <area shape="rect" coords="265,34,271,43" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=FF6699" target="_self" />
          <area shape="rect" coords="273,34,279,43" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=FF66CC" target="_self" />
          <area shape="rect" coords="281,34,287,43" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=FF66FF" target="_self" />
          <area shape="rect" coords="1,45,7,54" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=003300" target="_self" />
          <area shape="rect" coords="9,45,15,54" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=003333" target="_self" />
          <area shape="rect" coords="17,45,23,54" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=003366" target="_self" />
          <area shape="rect" coords="25,45,31,54" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=003399" target="_self" />
          <area shape="rect" coords="33,45,39,54" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=0033CC" target="_self" />
          <area shape="rect" coords="41,45,47,54" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=0033FF" target="_self" />

          <area shape="rect" coords="49,45,55,54" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=333300" target="_self" />
          <area shape="rect" coords="57,45,63,54" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=333333" target="_self" />
          <area shape="rect" coords="65,45,71,54" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=333366" target="_self" />
          <area shape="rect" coords="73,45,79,54" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=333399" target="_self" />
          <area shape="rect" coords="81,45,87,54" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=3333CC" target="_self" />
          <area shape="rect" coords="89,45,95,54" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=3333FF" target="_self" />
          <area shape="rect" coords="97,45,103,54" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=663300" target="_self" />
          <area shape="rect" coords="105,45,111,54" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=663333" target="_self" />
          <area shape="rect" coords="113,45,119,54" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=663366" target="_self" />

          <area shape="rect" coords="121,45,127,54" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=663399" target="_self" />
          <area shape="rect" coords="129,45,135,54" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=6633CC" target="_self" />
          <area shape="rect" coords="137,45,143,54" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=6633FF" target="_self" />
          <area shape="rect" coords="145,45,151,54" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=993300" target="_self" />
          <area shape="rect" coords="153,45,159,54" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=993333" target="_self" />
          <area shape="rect" coords="161,45,167,54" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=993366" target="_self" />
          <area shape="rect" coords="169,45,175,54" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=993399" target="_self" />
          <area shape="rect" coords="177,45,183,54" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=9933CC" target="_self" />
          <area shape="rect" coords="185,45,191,54" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=9933FF" target="_self" />

          <area shape="rect" coords="193,45,199,54" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=CC3300" target="_self" />
          <area shape="rect" coords="201,45,207,54" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=CC3333" target="_self" />
          <area shape="rect" coords="209,45,215,54" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=CC3366" target="_self" />
          <area shape="rect" coords="217,45,223,54" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=CC3399" target="_self" />
          <area shape="rect" coords="225,45,231,54" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=CC33CC" target="_self" />
          <area shape="rect" coords="233,45,239,54" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=CC33FF" target="_self" />
          <area shape="rect" coords="241,45,247,54" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=FF3300" target="_self" />
          <area shape="rect" coords="249,45,255,54" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=FF3333" target="_self" />
          <area shape="rect" coords="257,45,263,54" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=FF3366" target="_self" />

          <area shape="rect" coords="265,45,271,54" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=FF3399" target="_self" />
          <area shape="rect" coords="273,45,279,54" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=FF33CC" target="_self" />
          <area shape="rect" coords="281,45,287,54" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=FF33FF" target="_self" />
          <area shape="rect" coords="1,56,7,65" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=000000" target="_self" />
          <area shape="rect" coords="9,56,15,65" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=000033" target="_self" />
          <area shape="rect" coords="17,56,23,65" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=000066" target="_self" />
          <area shape="rect" coords="25,56,31,65" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=000099" target="_self" />
          <area shape="rect" coords="33,56,39,65" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=0000CC" target="_self" />
          <area shape="rect" coords="41,56,47,65" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=0000FF" target="_self" />

          <area shape="rect" coords="49,56,55,65" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=330000" target="_self" />
          <area shape="rect" coords="57,56,63,65" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=330033" target="_self" />
          <area shape="rect" coords="65,56,71,65" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=330066" target="_self" />
          <area shape="rect" coords="73,56,79,65" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=330099" target="_self" />
          <area shape="rect" coords="81,56,87,65" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=3300CC" target="_self" />
          <area shape="rect" coords="89,56,95,65" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=3300FF" target="_self" />
          <area shape="rect" coords="97,56,103,65" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=660000" target="_self" />
          <area shape="rect" coords="105,56,111,65" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=660033" target="_self" />
          <area shape="rect" coords="113,56,119,65" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=660066" target="_self" />

          <area shape="rect" coords="121,56,127,65" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=660099" target="_self" />
          <area shape="rect" coords="129,56,135,65" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=6600CC" target="_self" />
          <area shape="rect" coords="137,56,143,65" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=6600FF" target="_self" />
          <area shape="rect" coords="145,56,151,65" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=990000" target="_self" />
          <area shape="rect" coords="153,56,159,65" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=990033" target="_self" />
          <area shape="rect" coords="161,56,167,65" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=990066" target="_self" />
          <area shape="rect" coords="169,56,175,65" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=990099" target="_self" />
          <area shape="rect" coords="177,56,183,65" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=9900CC" target="_self" />
          <area shape="rect" coords="185,56,191,65" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=9900FF" target="_self" />

          <area shape="rect" coords="193,56,199,65" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=CC0000" target="_self" />
          <area shape="rect" coords="201,56,207,65" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=CC0033" target="_self" />
          <area shape="rect" coords="209,56,215,65" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=CC0066" target="_self" />
          <area shape="rect" coords="217,56,223,65" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=CC0099" target="_self" />
          <area shape="rect" coords="225,56,231,65" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=CC00CC" target="_self" />
          <area shape="rect" coords="233,56,239,65" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=CC00FF" target="_self" />
          <area shape="rect" coords="241,56,247,65" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=FF0000" target="_self" />
          <area shape="rect" coords="249,56,255,65" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=FF0033" target="_self" />
          <area shape="rect" coords="257,56,263,65" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=FF0066" target="_self" />

          <area shape="rect" coords="265,56,271,65" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=FF0099" target="_self" />
          <area shape="rect" coords="273,56,279,65" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=FF00CC" target="_self" />
          <area shape="rect" coords="281,56,287,65" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=FF00FF" target="_self" /></map>
          <a> <img usemap="#colmap_Farbe_Zusatz_VG" src="gfx/colortable.gif" border="0" width="289" height="67" alt="" /></a> 
          <br />
          <map name="colmap_textfarbe_grau" id="id">
          <area shape="rect" coords="1,1,7,10" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=000000" target="_self" />
          <area shape="rect" coords="9,1,15,10" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=111111" target="_self" />
          <area shape="rect" coords="17,1,23,10" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=222222" target="_self" />
          <area shape="rect" coords="25,1,31,10" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=333333" target="_self" />
          <area shape="rect" coords="33,1,39,10" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=444444" target="_self" />
          <area shape="rect" coords="41,1,47,10" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=555555" target="_self" />
          <area shape="rect" coords="49,1,55,10" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=666666" target="_self" />
          <area shape="rect" coords="57,1,63,10" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=777777" target="_self" />
          <area shape="rect" coords="65,1,71,10" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=888888" target="_self" />
          <area shape="rect" coords="73,1,79,10" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=999999" target="_self" />
          <area shape="rect" coords="81,1,87,10" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=aaaaaa" target="_self" />
          <area shape="rect" coords="89,1,95,10" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=bbbbbb" target="_self" />
          <area shape="rect" coords="97,1,103,10" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=cccccc" target="_self" />
          <area shape="rect" coords="105,1,111,10" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=dddddd" target="_self" />
          <area shape="rect" coords="113,1,119,10" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=eeeeee" target="_self" />
          <area shape="rect" coords="121,1,127,10" href="svg_zeichenvorschrift_farbe.php?Farbe_Zusatz_VG=ffffff" target="_self" />
          </map>
          <a> <img usemap="#colmap_Farbe_Zusatz_VG_grau" src="gfx/colortable_grau.png" border="0" width="129" height="12" alt="" /></a>
          
          
          <br />
          Selbst definierter Hex-RGB-Wert: #
				  <input name="Farbe_Zusatz_VG" type="text" id="Farbe_Zusatz_VG" value="<?php echo $_SESSION['Dokument']['Farbe_Zusatz_VG']; ?>" size="8" maxlength="6" />
                    <input name="button7" type="submit" class="button_blau_abschicken" id="button7" style="cursor: pointer;" value="OK" />
                    <br /></td>
              </tr>
           <input name="Aktion" type="hidden" value="ZV_uebernahme" />
           </form>
              <tr>
                <td height="26" valign="top">&nbsp;</td>
                <td height="26" colspan="2" valign="top"><br />
                    <br />
                    <div style="height:0px; border-bottom:1px solid #999999; margin-top:0px; margin-bottom:7px;"></div>                 </td>
              </tr> -->
              <tr>
                <td valign="top">Konturfarbe für <br />
                Verwaltungsgrenzen <br />
                als Zusatzebene</td>
                <td valign="top">
                    <div style="padding:5px; background-color:#eeeeee; height:70px;">
                      <div style="border:solid #666666 0px; width:20px; height:2px; overflow:hidden; background-color:#<?php echo $_SESSION['Dokument']['Strichfarbe_ZE_VG']; ?>;"></div>
                  </div>                </td>
                <td valign="top">
                
                
                
                
                     
          <map name="colmap_Strichfarbe_ZE_VG" id="id">
          <area shape="rect" coords="1,1,7,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=00FF00" target="_self" />
          <area shape="rect" coords="9,1,15,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=00FF33" target="_self" />
          <area shape="rect" coords="17,1,23,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=00FF66" target="_self" />
          <area shape="rect" coords="25,1,31,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=00FF99" target="_self" />
          <area shape="rect" coords="33,1,39,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=00FFCC" target="_self" />
          <area shape="rect" coords="41,1,47,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=00FFFF" target="_self" />

          <area shape="rect" coords="49,1,55,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=33FF00" target="_self" />
          <area shape="rect" coords="57,1,63,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=33FF33" target="_self" />
          <area shape="rect" coords="65,1,71,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=33FF66" target="_self" />
          <area shape="rect" coords="73,1,79,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=33FF99" target="_self" />
          <area shape="rect" coords="81,1,87,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=33FFCC" target="_self" />
          <area shape="rect" coords="89,1,95,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=33FFFF" target="_self" />
          <area shape="rect" coords="97,1,103,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=66FF00" target="_self" />
          <area shape="rect" coords="105,1,111,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=66FF33" target="_self" />
          <area shape="rect" coords="113,1,119,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=66FF66" target="_self" />

          <area shape="rect" coords="121,1,127,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=66FF99" target="_self" />
          <area shape="rect" coords="129,1,135,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=66FFCC" target="_self" />
          <area shape="rect" coords="137,1,143,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=66FFFF" target="_self" />
          <area shape="rect" coords="145,1,151,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=99FF00" target="_self" />
          <area shape="rect" coords="153,1,159,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=99FF33" target="_self" />
          <area shape="rect" coords="161,1,167,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=99FF66" target="_self" />
          <area shape="rect" coords="169,1,175,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=99FF99" target="_self" />
          <area shape="rect" coords="177,1,183,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=99FFCC" target="_self" />
          <area shape="rect" coords="185,1,191,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=99FFFF" target="_self" />

          <area shape="rect" coords="193,1,199,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=CCFF00" target="_self" />
          <area shape="rect" coords="201,1,207,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=CCFF33" target="_self" />
          <area shape="rect" coords="209,1,215,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=CCFF66" target="_self" />
          <area shape="rect" coords="217,1,223,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=CCFF99" target="_self" />
          <area shape="rect" coords="225,1,231,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=CCFFCC" target="_self" />
          <area shape="rect" coords="233,1,239,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=CCFFFF" target="_self" />
          <area shape="rect" coords="241,1,247,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=FFFF00" target="_self" />
          <area shape="rect" coords="249,1,255,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=FFFF33" target="_self" />
          <area shape="rect" coords="257,1,263,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=FFFF66" target="_self" />

          <area shape="rect" coords="265,1,271,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=FFFF99" target="_self" />
          <area shape="rect" coords="273,1,279,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=FFFFCC" target="_self" />
          <area shape="rect" coords="281,1,287,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=FFFFFF" target="_self" />
          <area shape="rect" coords="1,12,7,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=00CC00" target="_self" />
          <area shape="rect" coords="9,12,15,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=00CC33" target="_self" />
          <area shape="rect" coords="17,12,23,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=00CC66" target="_self" />
          <area shape="rect" coords="25,12,31,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=00CC99" target="_self" />
          <area shape="rect" coords="33,12,39,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=00CCCC" target="_self" />
          <area shape="rect" coords="41,12,47,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=00CCFF" target="_self" />

          <area shape="rect" coords="49,12,55,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=33CC00" target="_self" />
          <area shape="rect" coords="57,12,63,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=33CC33" target="_self" />
          <area shape="rect" coords="65,12,71,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=33CC66" target="_self" />
          <area shape="rect" coords="73,12,79,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=33CC99" target="_self" />
          <area shape="rect" coords="81,12,87,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=33CCCC" target="_self" />
          <area shape="rect" coords="89,12,95,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=33CCFF" target="_self" />
          <area shape="rect" coords="97,12,103,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=66CC00" target="_self" />
          <area shape="rect" coords="105,12,111,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=66CC33" target="_self" />
          <area shape="rect" coords="113,12,119,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=66CC66" target="_self" />

          <area shape="rect" coords="121,12,127,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=66CC99" target="_self" />
          <area shape="rect" coords="129,12,135,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=66CCCC" target="_self" />
          <area shape="rect" coords="137,12,143,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=66CCFF" target="_self" />
          <area shape="rect" coords="145,12,151,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=99CC00" target="_self" />
          <area shape="rect" coords="153,12,159,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=99CC33" target="_self" />
          <area shape="rect" coords="161,12,167,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=99CC66" target="_self" />
          <area shape="rect" coords="169,12,175,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=99CC99" target="_self" />
          <area shape="rect" coords="177,12,183,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=99CCCC" target="_self" />
          <area shape="rect" coords="185,12,191,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=99CCFF" target="_self" />

          <area shape="rect" coords="193,12,199,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=CCCC00" target="_self" />
          <area shape="rect" coords="201,12,207,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=CCCC33" target="_self" />
          <area shape="rect" coords="209,12,215,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=CCCC66" target="_self" />
          <area shape="rect" coords="217,12,223,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=CCCC99" target="_self" />
          <area shape="rect" coords="225,12,231,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=CCCCCC" target="_self" />
          <area shape="rect" coords="233,12,239,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=CCCCFF" target="_self" />
          <area shape="rect" coords="241,12,247,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=FFCC00" target="_self" />
          <area shape="rect" coords="249,12,255,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=FFCC33" target="_self" />
          <area shape="rect" coords="257,12,263,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=FFCC66" target="_self" />

          <area shape="rect" coords="265,12,271,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=FFCC99" target="_self" />
          <area shape="rect" coords="273,12,279,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=FFCCCC" target="_self" />
          <area shape="rect" coords="281,12,287,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=FFCCFF" target="_self" />
          <area shape="rect" coords="1,23,7,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=009900" target="_self" />
          <area shape="rect" coords="9,23,15,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=009933" target="_self" />
          <area shape="rect" coords="17,23,23,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=009966" target="_self" />
          <area shape="rect" coords="25,23,31,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=009999" target="_self" />
          <area shape="rect" coords="33,23,39,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=0099CC" target="_self" />
          <area shape="rect" coords="41,23,47,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=0099FF" target="_self" />

          <area shape="rect" coords="49,23,55,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=339900" target="_self" />
          <area shape="rect" coords="57,23,63,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=339933" target="_self" />
          <area shape="rect" coords="65,23,71,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=339966" target="_self" />
          <area shape="rect" coords="73,23,79,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=339999" target="_self" />
          <area shape="rect" coords="81,23,87,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=3399CC" target="_self" />
          <area shape="rect" coords="89,23,95,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=3399FF" target="_self" />
          <area shape="rect" coords="97,23,103,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=669900" target="_self" />
          <area shape="rect" coords="105,23,111,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=669933" target="_self" />
          <area shape="rect" coords="113,23,119,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=669966" target="_self" />

          <area shape="rect" coords="121,23,127,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=669999" target="_self" />
          <area shape="rect" coords="129,23,135,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=6699CC" target="_self" />
          <area shape="rect" coords="137,23,143,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=6699FF" target="_self" />
          <area shape="rect" coords="145,23,151,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=999900" target="_self" />
          <area shape="rect" coords="153,23,159,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=999933" target="_self" />
          <area shape="rect" coords="161,23,167,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=999966" target="_self" />
          <area shape="rect" coords="169,23,175,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=999999" target="_self" />
          <area shape="rect" coords="177,23,183,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=9999CC" target="_self" />
          <area shape="rect" coords="185,23,191,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=9999FF" target="_self" />

          <area shape="rect" coords="193,23,199,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=CC9900" target="_self" />
          <area shape="rect" coords="201,23,207,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=CC9933" target="_self" />
          <area shape="rect" coords="209,23,215,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=CC9966" target="_self" />
          <area shape="rect" coords="217,23,223,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=CC9999" target="_self" />
          <area shape="rect" coords="225,23,231,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=CC99CC" target="_self" />
          <area shape="rect" coords="233,23,239,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=CC99FF" target="_self" />
          <area shape="rect" coords="241,23,247,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=FF9900" target="_self" />
          <area shape="rect" coords="249,23,255,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=FF9933" target="_self" />
          <area shape="rect" coords="257,23,263,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=FF9966" target="_self" />

          <area shape="rect" coords="265,23,271,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=FF9999" target="_self" />
          <area shape="rect" coords="273,23,279,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=FF99CC" target="_self" />
          <area shape="rect" coords="281,23,287,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=FF99FF" target="_self" />
          <area shape="rect" coords="1,34,7,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=006600" target="_self" />
          <area shape="rect" coords="9,34,15,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=006633" target="_self" />
          <area shape="rect" coords="17,34,23,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=006666" target="_self" />
          <area shape="rect" coords="25,34,31,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=006699" target="_self" />
          <area shape="rect" coords="33,34,39,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=0066CC" target="_self" />
          <area shape="rect" coords="41,34,47,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=0066FF" target="_self" />

          <area shape="rect" coords="49,34,55,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=336600" target="_self" />
          <area shape="rect" coords="57,34,63,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=336633" target="_self" />
          <area shape="rect" coords="65,34,71,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=336666" target="_self" />
          <area shape="rect" coords="73,34,79,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=336699" target="_self" />
          <area shape="rect" coords="81,34,87,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=3366CC" target="_self" />
          <area shape="rect" coords="89,34,95,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=3366FF" target="_self" />
          <area shape="rect" coords="97,34,103,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=666600" target="_self" />
          <area shape="rect" coords="105,34,111,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=666633" target="_self" />
          <area shape="rect" coords="113,34,119,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=666666" target="_self" />

          <area shape="rect" coords="121,34,127,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=666699" target="_self" />
          <area shape="rect" coords="129,34,135,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=6666CC" target="_self" />
          <area shape="rect" coords="137,34,143,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=6666FF" target="_self" />
          <area shape="rect" coords="145,34,151,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=996600" target="_self" />
          <area shape="rect" coords="153,34,159,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=996633" target="_self" />
          <area shape="rect" coords="161,34,167,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=996666" target="_self" />
          <area shape="rect" coords="169,34,175,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=996699" target="_self" />
          <area shape="rect" coords="177,34,183,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=9966CC" target="_self" />
          <area shape="rect" coords="185,34,191,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=9966FF" target="_self" />

          <area shape="rect" coords="193,34,199,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=CC6600" target="_self" />
          <area shape="rect" coords="201,34,207,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=CC6633" target="_self" />
          <area shape="rect" coords="209,34,215,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=CC6666" target="_self" />
          <area shape="rect" coords="217,34,223,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=CC6699" target="_self" />
          <area shape="rect" coords="225,34,231,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=CC66CC" target="_self" />
          <area shape="rect" coords="233,34,239,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=CC66FF" target="_self" />
          <area shape="rect" coords="241,34,247,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=FF6600" target="_self" />
          <area shape="rect" coords="249,34,255,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=FF6633" target="_self" />
          <area shape="rect" coords="257,34,263,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=FF6666" target="_self" />

          <area shape="rect" coords="265,34,271,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=FF6699" target="_self" />
          <area shape="rect" coords="273,34,279,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=FF66CC" target="_self" />
          <area shape="rect" coords="281,34,287,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=FF66FF" target="_self" />
          <area shape="rect" coords="1,45,7,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=003300" target="_self" />
          <area shape="rect" coords="9,45,15,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=003333" target="_self" />
          <area shape="rect" coords="17,45,23,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=003366" target="_self" />
          <area shape="rect" coords="25,45,31,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=003399" target="_self" />
          <area shape="rect" coords="33,45,39,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=0033CC" target="_self" />
          <area shape="rect" coords="41,45,47,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=0033FF" target="_self" />

          <area shape="rect" coords="49,45,55,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=333300" target="_self" />
          <area shape="rect" coords="57,45,63,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=333333" target="_self" />
          <area shape="rect" coords="65,45,71,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=333366" target="_self" />
          <area shape="rect" coords="73,45,79,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=333399" target="_self" />
          <area shape="rect" coords="81,45,87,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=3333CC" target="_self" />
          <area shape="rect" coords="89,45,95,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=3333FF" target="_self" />
          <area shape="rect" coords="97,45,103,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=663300" target="_self" />
          <area shape="rect" coords="105,45,111,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=663333" target="_self" />
          <area shape="rect" coords="113,45,119,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=663366" target="_self" />

          <area shape="rect" coords="121,45,127,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=663399" target="_self" />
          <area shape="rect" coords="129,45,135,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=6633CC" target="_self" />
          <area shape="rect" coords="137,45,143,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=6633FF" target="_self" />
          <area shape="rect" coords="145,45,151,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=993300" target="_self" />
          <area shape="rect" coords="153,45,159,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=993333" target="_self" />
          <area shape="rect" coords="161,45,167,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=993366" target="_self" />
          <area shape="rect" coords="169,45,175,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=993399" target="_self" />
          <area shape="rect" coords="177,45,183,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=9933CC" target="_self" />
          <area shape="rect" coords="185,45,191,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=9933FF" target="_self" />

          <area shape="rect" coords="193,45,199,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=CC3300" target="_self" />
          <area shape="rect" coords="201,45,207,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=CC3333" target="_self" />
          <area shape="rect" coords="209,45,215,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=CC3366" target="_self" />
          <area shape="rect" coords="217,45,223,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=CC3399" target="_self" />
          <area shape="rect" coords="225,45,231,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=CC33CC" target="_self" />
          <area shape="rect" coords="233,45,239,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=CC33FF" target="_self" />
          <area shape="rect" coords="241,45,247,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=FF3300" target="_self" />
          <area shape="rect" coords="249,45,255,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=FF3333" target="_self" />
          <area shape="rect" coords="257,45,263,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=FF3366" target="_self" />

          <area shape="rect" coords="265,45,271,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=FF3399" target="_self" />
          <area shape="rect" coords="273,45,279,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=FF33CC" target="_self" />
          <area shape="rect" coords="281,45,287,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=FF33FF" target="_self" />
          <area shape="rect" coords="1,56,7,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=000000" target="_self" />
          <area shape="rect" coords="9,56,15,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=000033" target="_self" />
          <area shape="rect" coords="17,56,23,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=000066" target="_self" />
          <area shape="rect" coords="25,56,31,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=000099" target="_self" />
          <area shape="rect" coords="33,56,39,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=0000CC" target="_self" />
          <area shape="rect" coords="41,56,47,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=0000FF" target="_self" />

          <area shape="rect" coords="49,56,55,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=330000" target="_self" />
          <area shape="rect" coords="57,56,63,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=330033" target="_self" />
          <area shape="rect" coords="65,56,71,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=330066" target="_self" />
          <area shape="rect" coords="73,56,79,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=330099" target="_self" />
          <area shape="rect" coords="81,56,87,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=3300CC" target="_self" />
          <area shape="rect" coords="89,56,95,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=3300FF" target="_self" />
          <area shape="rect" coords="97,56,103,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=660000" target="_self" />
          <area shape="rect" coords="105,56,111,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=660033" target="_self" />
          <area shape="rect" coords="113,56,119,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=660066" target="_self" />

          <area shape="rect" coords="121,56,127,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=660099" target="_self" />
          <area shape="rect" coords="129,56,135,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=6600CC" target="_self" />
          <area shape="rect" coords="137,56,143,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=6600FF" target="_self" />
          <area shape="rect" coords="145,56,151,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=990000" target="_self" />
          <area shape="rect" coords="153,56,159,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=990033" target="_self" />
          <area shape="rect" coords="161,56,167,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=990066" target="_self" />
          <area shape="rect" coords="169,56,175,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=990099" target="_self" />
          <area shape="rect" coords="177,56,183,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=9900CC" target="_self" />
          <area shape="rect" coords="185,56,191,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=9900FF" target="_self" />

          <area shape="rect" coords="193,56,199,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=CC0000" target="_self" />
          <area shape="rect" coords="201,56,207,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=CC0033" target="_self" />
          <area shape="rect" coords="209,56,215,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=CC0066" target="_self" />
          <area shape="rect" coords="217,56,223,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=CC0099" target="_self" />
          <area shape="rect" coords="225,56,231,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=CC00CC" target="_self" />
          <area shape="rect" coords="233,56,239,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=CC00FF" target="_self" />
          <area shape="rect" coords="241,56,247,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=FF0000" target="_self" />
          <area shape="rect" coords="249,56,255,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=FF0033" target="_self" />
          <area shape="rect" coords="257,56,263,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=FF0066" target="_self" />

          <area shape="rect" coords="265,56,271,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=FF0099" target="_self" />
          <area shape="rect" coords="273,56,279,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=FF00CC" target="_self" />
          <area shape="rect" coords="281,56,287,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=FF00FF" target="_self" />
          </map>
          <a> <img usemap="#colmap_Strichfarbe_ZE_VG" src="gfx/colortable.gif" border="0" width="289" height="67" alt="" /></a> 
          <br />
          <map name="colmap_Strichfarbe_ZE_VG_grau" id="id">
          <area shape="rect" coords="1,1,7,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=000000" target="_self" />
          <area shape="rect" coords="9,1,15,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=111111" target="_self" />
          <area shape="rect" coords="17,1,23,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=222222" target="_self" />
          <area shape="rect" coords="25,1,31,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=333333" target="_self" />
          <area shape="rect" coords="33,1,39,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=444444" target="_self" />
          <area shape="rect" coords="41,1,47,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=555555" target="_self" />
          <area shape="rect" coords="49,1,55,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=666666" target="_self" />
          <area shape="rect" coords="57,1,63,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=777777" target="_self" />
          <area shape="rect" coords="65,1,71,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=888888" target="_self" />
          <area shape="rect" coords="73,1,79,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=999999" target="_self" />
          <area shape="rect" coords="81,1,87,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=aaaaaa" target="_self" />
          <area shape="rect" coords="89,1,95,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=bbbbbb" target="_self" />
          <area shape="rect" coords="97,1,103,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=cccccc" target="_self" />
          <area shape="rect" coords="105,1,111,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=dddddd" target="_self" />
          <area shape="rect" coords="113,1,119,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=eeeeee" target="_self" />
          <area shape="rect" coords="121,1,127,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_VG=ffffff" target="_self" />
          </map>
          <a> <img usemap="#colmap_Strichfarbe_ZE_VG_grau" src="gfx/colortable_grau.png" border="0" width="129" height="12" alt="" /></a>
          
          
          <br />
          Selbst definierter Hex-RGB-Wert: #
				  <input name="Strichfarbe_ZE_VG" type="text" id="Strichfarbe_ZE_VG" value="<?php echo $_SESSION['Dokument']['Strichfarbe_ZE_VG']; ?>" size="8" maxlength="6" />
                    <input name="button7" type="submit" class="button_blau_abschicken" id="button7" style="cursor: pointer;" value="OK" />
                    <br />                
                  </td>
              </tr>
               <tr>
                <td height="26" colspan="3" valign="top"><br />
                <div style="height:0px; border-bottom:1px solid #999999; margin-top:0px; margin-bottom:7px;"></div>                 
                
                </td>
              </tr>
              <tr>
                <td valign="top">Farbe für <br />
Gewässer <br />
als Zusatzebene</td>
                <td valign="top">
        			<div style="padding:5px; background-color:#eeeeee; height:70px;">
                    	<div style="border:solid #666666 0px; width:20px; height:2px; overflow:hidden; background-color:#<?php echo $_SESSION['Dokument']['Strichfarbe_GEW']; ?>;"></div>
                    </div>    
                        </td>
                <td valign="top">
                
                
                
                
                     
          <map name="colmap_Strichfarbe_ZE_GEW" id="id">
          <area shape="rect" coords="1,1,7,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=00FF00" target="_self" />
          <area shape="rect" coords="9,1,15,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=00FF33" target="_self" />
          <area shape="rect" coords="17,1,23,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=00FF66" target="_self" />
          <area shape="rect" coords="25,1,31,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=00FF99" target="_self" />
          <area shape="rect" coords="33,1,39,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=00FFCC" target="_self" />
          <area shape="rect" coords="41,1,47,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=00FFFF" target="_self" />

          <area shape="rect" coords="49,1,55,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=33FF00" target="_self" />
          <area shape="rect" coords="57,1,63,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=33FF33" target="_self" />
          <area shape="rect" coords="65,1,71,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=33FF66" target="_self" />
          <area shape="rect" coords="73,1,79,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=33FF99" target="_self" />
          <area shape="rect" coords="81,1,87,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=33FFCC" target="_self" />
          <area shape="rect" coords="89,1,95,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=33FFFF" target="_self" />
          <area shape="rect" coords="97,1,103,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=66FF00" target="_self" />
          <area shape="rect" coords="105,1,111,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=66FF33" target="_self" />
          <area shape="rect" coords="113,1,119,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=66FF66" target="_self" />

          <area shape="rect" coords="121,1,127,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=66FF99" target="_self" />
          <area shape="rect" coords="129,1,135,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=66FFCC" target="_self" />
          <area shape="rect" coords="137,1,143,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=66FFFF" target="_self" />
          <area shape="rect" coords="145,1,151,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=99FF00" target="_self" />
          <area shape="rect" coords="153,1,159,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=99FF33" target="_self" />
          <area shape="rect" coords="161,1,167,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=99FF66" target="_self" />
          <area shape="rect" coords="169,1,175,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=99FF99" target="_self" />
          <area shape="rect" coords="177,1,183,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=99FFCC" target="_self" />
          <area shape="rect" coords="185,1,191,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=99FFFF" target="_self" />

          <area shape="rect" coords="193,1,199,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=CCFF00" target="_self" />
          <area shape="rect" coords="201,1,207,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=CCFF33" target="_self" />
          <area shape="rect" coords="209,1,215,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=CCFF66" target="_self" />
          <area shape="rect" coords="217,1,223,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=CCFF99" target="_self" />
          <area shape="rect" coords="225,1,231,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=CCFFCC" target="_self" />
          <area shape="rect" coords="233,1,239,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=CCFFFF" target="_self" />
          <area shape="rect" coords="241,1,247,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=FFFF00" target="_self" />
          <area shape="rect" coords="249,1,255,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=FFFF33" target="_self" />
          <area shape="rect" coords="257,1,263,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=FFFF66" target="_self" />

          <area shape="rect" coords="265,1,271,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=FFFF99" target="_self" />
          <area shape="rect" coords="273,1,279,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=FFFFCC" target="_self" />
          <area shape="rect" coords="281,1,287,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=FFFFFF" target="_self" />
          <area shape="rect" coords="1,12,7,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=00CC00" target="_self" />
          <area shape="rect" coords="9,12,15,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=00CC33" target="_self" />
          <area shape="rect" coords="17,12,23,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=00CC66" target="_self" />
          <area shape="rect" coords="25,12,31,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=00CC99" target="_self" />
          <area shape="rect" coords="33,12,39,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=00CCCC" target="_self" />
          <area shape="rect" coords="41,12,47,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=00CCFF" target="_self" />

          <area shape="rect" coords="49,12,55,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=33CC00" target="_self" />
          <area shape="rect" coords="57,12,63,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=33CC33" target="_self" />
          <area shape="rect" coords="65,12,71,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=33CC66" target="_self" />
          <area shape="rect" coords="73,12,79,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=33CC99" target="_self" />
          <area shape="rect" coords="81,12,87,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=33CCCC" target="_self" />
          <area shape="rect" coords="89,12,95,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=33CCFF" target="_self" />
          <area shape="rect" coords="97,12,103,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=66CC00" target="_self" />
          <area shape="rect" coords="105,12,111,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=66CC33" target="_self" />
          <area shape="rect" coords="113,12,119,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=66CC66" target="_self" />

          <area shape="rect" coords="121,12,127,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=66CC99" target="_self" />
          <area shape="rect" coords="129,12,135,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=66CCCC" target="_self" />
          <area shape="rect" coords="137,12,143,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=66CCFF" target="_self" />
          <area shape="rect" coords="145,12,151,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=99CC00" target="_self" />
          <area shape="rect" coords="153,12,159,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=99CC33" target="_self" />
          <area shape="rect" coords="161,12,167,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=99CC66" target="_self" />
          <area shape="rect" coords="169,12,175,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=99CC99" target="_self" />
          <area shape="rect" coords="177,12,183,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=99CCCC" target="_self" />
          <area shape="rect" coords="185,12,191,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=99CCFF" target="_self" />

          <area shape="rect" coords="193,12,199,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=CCCC00" target="_self" />
          <area shape="rect" coords="201,12,207,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=CCCC33" target="_self" />
          <area shape="rect" coords="209,12,215,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=CCCC66" target="_self" />
          <area shape="rect" coords="217,12,223,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=CCCC99" target="_self" />
          <area shape="rect" coords="225,12,231,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=CCCCCC" target="_self" />
          <area shape="rect" coords="233,12,239,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=CCCCFF" target="_self" />
          <area shape="rect" coords="241,12,247,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=FFCC00" target="_self" />
          <area shape="rect" coords="249,12,255,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=FFCC33" target="_self" />
          <area shape="rect" coords="257,12,263,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=FFCC66" target="_self" />

          <area shape="rect" coords="265,12,271,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=FFCC99" target="_self" />
          <area shape="rect" coords="273,12,279,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=FFCCCC" target="_self" />
          <area shape="rect" coords="281,12,287,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=FFCCFF" target="_self" />
          <area shape="rect" coords="1,23,7,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=009900" target="_self" />
          <area shape="rect" coords="9,23,15,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=009933" target="_self" />
          <area shape="rect" coords="17,23,23,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=009966" target="_self" />
          <area shape="rect" coords="25,23,31,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=009999" target="_self" />
          <area shape="rect" coords="33,23,39,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=0099CC" target="_self" />
          <area shape="rect" coords="41,23,47,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=0099FF" target="_self" />

          <area shape="rect" coords="49,23,55,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=339900" target="_self" />
          <area shape="rect" coords="57,23,63,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=339933" target="_self" />
          <area shape="rect" coords="65,23,71,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=339966" target="_self" />
          <area shape="rect" coords="73,23,79,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=339999" target="_self" />
          <area shape="rect" coords="81,23,87,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=3399CC" target="_self" />
          <area shape="rect" coords="89,23,95,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=3399FF" target="_self" />
          <area shape="rect" coords="97,23,103,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=669900" target="_self" />
          <area shape="rect" coords="105,23,111,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=669933" target="_self" />
          <area shape="rect" coords="113,23,119,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=669966" target="_self" />

          <area shape="rect" coords="121,23,127,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=669999" target="_self" />
          <area shape="rect" coords="129,23,135,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=6699CC" target="_self" />
          <area shape="rect" coords="137,23,143,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=6699FF" target="_self" />
          <area shape="rect" coords="145,23,151,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=999900" target="_self" />
          <area shape="rect" coords="153,23,159,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=999933" target="_self" />
          <area shape="rect" coords="161,23,167,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=999966" target="_self" />
          <area shape="rect" coords="169,23,175,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=999999" target="_self" />
          <area shape="rect" coords="177,23,183,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=9999CC" target="_self" />
          <area shape="rect" coords="185,23,191,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=9999FF" target="_self" />

          <area shape="rect" coords="193,23,199,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=CC9900" target="_self" />
          <area shape="rect" coords="201,23,207,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=CC9933" target="_self" />
          <area shape="rect" coords="209,23,215,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=CC9966" target="_self" />
          <area shape="rect" coords="217,23,223,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=CC9999" target="_self" />
          <area shape="rect" coords="225,23,231,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=CC99CC" target="_self" />
          <area shape="rect" coords="233,23,239,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=CC99FF" target="_self" />
          <area shape="rect" coords="241,23,247,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=FF9900" target="_self" />
          <area shape="rect" coords="249,23,255,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=FF9933" target="_self" />
          <area shape="rect" coords="257,23,263,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=FF9966" target="_self" />

          <area shape="rect" coords="265,23,271,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=FF9999" target="_self" />
          <area shape="rect" coords="273,23,279,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=FF99CC" target="_self" />
          <area shape="rect" coords="281,23,287,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=FF99FF" target="_self" />
          <area shape="rect" coords="1,34,7,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=006600" target="_self" />
          <area shape="rect" coords="9,34,15,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=006633" target="_self" />
          <area shape="rect" coords="17,34,23,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=006666" target="_self" />
          <area shape="rect" coords="25,34,31,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=006699" target="_self" />
          <area shape="rect" coords="33,34,39,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=0066CC" target="_self" />
          <area shape="rect" coords="41,34,47,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=0066FF" target="_self" />

          <area shape="rect" coords="49,34,55,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=336600" target="_self" />
          <area shape="rect" coords="57,34,63,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=336633" target="_self" />
          <area shape="rect" coords="65,34,71,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=336666" target="_self" />
          <area shape="rect" coords="73,34,79,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=336699" target="_self" />
          <area shape="rect" coords="81,34,87,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=3366CC" target="_self" />
          <area shape="rect" coords="89,34,95,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=3366FF" target="_self" />
          <area shape="rect" coords="97,34,103,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=666600" target="_self" />
          <area shape="rect" coords="105,34,111,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=666633" target="_self" />
          <area shape="rect" coords="113,34,119,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=666666" target="_self" />

          <area shape="rect" coords="121,34,127,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=666699" target="_self" />
          <area shape="rect" coords="129,34,135,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=6666CC" target="_self" />
          <area shape="rect" coords="137,34,143,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=6666FF" target="_self" />
          <area shape="rect" coords="145,34,151,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=996600" target="_self" />
          <area shape="rect" coords="153,34,159,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=996633" target="_self" />
          <area shape="rect" coords="161,34,167,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=996666" target="_self" />
          <area shape="rect" coords="169,34,175,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=996699" target="_self" />
          <area shape="rect" coords="177,34,183,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=9966CC" target="_self" />
          <area shape="rect" coords="185,34,191,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=9966FF" target="_self" />

          <area shape="rect" coords="193,34,199,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=CC6600" target="_self" />
          <area shape="rect" coords="201,34,207,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=CC6633" target="_self" />
          <area shape="rect" coords="209,34,215,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=CC6666" target="_self" />
          <area shape="rect" coords="217,34,223,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=CC6699" target="_self" />
          <area shape="rect" coords="225,34,231,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=CC66CC" target="_self" />
          <area shape="rect" coords="233,34,239,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=CC66FF" target="_self" />
          <area shape="rect" coords="241,34,247,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=FF6600" target="_self" />
          <area shape="rect" coords="249,34,255,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=FF6633" target="_self" />
          <area shape="rect" coords="257,34,263,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=FF6666" target="_self" />

          <area shape="rect" coords="265,34,271,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=FF6699" target="_self" />
          <area shape="rect" coords="273,34,279,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=FF66CC" target="_self" />
          <area shape="rect" coords="281,34,287,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=FF66FF" target="_self" />
          <area shape="rect" coords="1,45,7,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=003300" target="_self" />
          <area shape="rect" coords="9,45,15,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=003333" target="_self" />
          <area shape="rect" coords="17,45,23,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=003366" target="_self" />
          <area shape="rect" coords="25,45,31,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=003399" target="_self" />
          <area shape="rect" coords="33,45,39,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=0033CC" target="_self" />
          <area shape="rect" coords="41,45,47,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=0033FF" target="_self" />

          <area shape="rect" coords="49,45,55,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=333300" target="_self" />
          <area shape="rect" coords="57,45,63,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=333333" target="_self" />
          <area shape="rect" coords="65,45,71,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=333366" target="_self" />
          <area shape="rect" coords="73,45,79,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=333399" target="_self" />
          <area shape="rect" coords="81,45,87,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=3333CC" target="_self" />
          <area shape="rect" coords="89,45,95,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=3333FF" target="_self" />
          <area shape="rect" coords="97,45,103,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=663300" target="_self" />
          <area shape="rect" coords="105,45,111,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=663333" target="_self" />
          <area shape="rect" coords="113,45,119,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=663366" target="_self" />

          <area shape="rect" coords="121,45,127,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=663399" target="_self" />
          <area shape="rect" coords="129,45,135,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=6633CC" target="_self" />
          <area shape="rect" coords="137,45,143,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=6633FF" target="_self" />
          <area shape="rect" coords="145,45,151,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=993300" target="_self" />
          <area shape="rect" coords="153,45,159,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=993333" target="_self" />
          <area shape="rect" coords="161,45,167,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=993366" target="_self" />
          <area shape="rect" coords="169,45,175,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=993399" target="_self" />
          <area shape="rect" coords="177,45,183,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=9933CC" target="_self" />
          <area shape="rect" coords="185,45,191,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=9933FF" target="_self" />

          <area shape="rect" coords="193,45,199,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=CC3300" target="_self" />
          <area shape="rect" coords="201,45,207,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=CC3333" target="_self" />
          <area shape="rect" coords="209,45,215,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=CC3366" target="_self" />
          <area shape="rect" coords="217,45,223,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=CC3399" target="_self" />
          <area shape="rect" coords="225,45,231,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=CC33CC" target="_self" />
          <area shape="rect" coords="233,45,239,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=CC33FF" target="_self" />
          <area shape="rect" coords="241,45,247,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=FF3300" target="_self" />
          <area shape="rect" coords="249,45,255,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=FF3333" target="_self" />
          <area shape="rect" coords="257,45,263,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=FF3366" target="_self" />

          <area shape="rect" coords="265,45,271,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=FF3399" target="_self" />
          <area shape="rect" coords="273,45,279,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=FF33CC" target="_self" />
          <area shape="rect" coords="281,45,287,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=FF33FF" target="_self" />
          <area shape="rect" coords="1,56,7,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=000000" target="_self" />
          <area shape="rect" coords="9,56,15,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=000033" target="_self" />
          <area shape="rect" coords="17,56,23,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=000066" target="_self" />
          <area shape="rect" coords="25,56,31,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=000099" target="_self" />
          <area shape="rect" coords="33,56,39,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=0000CC" target="_self" />
          <area shape="rect" coords="41,56,47,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=0000FF" target="_self" />

          <area shape="rect" coords="49,56,55,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=330000" target="_self" />
          <area shape="rect" coords="57,56,63,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=330033" target="_self" />
          <area shape="rect" coords="65,56,71,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=330066" target="_self" />
          <area shape="rect" coords="73,56,79,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=330099" target="_self" />
          <area shape="rect" coords="81,56,87,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=3300CC" target="_self" />
          <area shape="rect" coords="89,56,95,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=3300FF" target="_self" />
          <area shape="rect" coords="97,56,103,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=660000" target="_self" />
          <area shape="rect" coords="105,56,111,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=660033" target="_self" />
          <area shape="rect" coords="113,56,119,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=660066" target="_self" />

          <area shape="rect" coords="121,56,127,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=660099" target="_self" />
          <area shape="rect" coords="129,56,135,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=6600CC" target="_self" />
          <area shape="rect" coords="137,56,143,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=6600FF" target="_self" />
          <area shape="rect" coords="145,56,151,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=990000" target="_self" />
          <area shape="rect" coords="153,56,159,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=990033" target="_self" />
          <area shape="rect" coords="161,56,167,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=990066" target="_self" />
          <area shape="rect" coords="169,56,175,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=990099" target="_self" />
          <area shape="rect" coords="177,56,183,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=9900CC" target="_self" />
          <area shape="rect" coords="185,56,191,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=9900FF" target="_self" />

          <area shape="rect" coords="193,56,199,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=CC0000" target="_self" />
          <area shape="rect" coords="201,56,207,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=CC0033" target="_self" />
          <area shape="rect" coords="209,56,215,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=CC0066" target="_self" />
          <area shape="rect" coords="217,56,223,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=CC0099" target="_self" />
          <area shape="rect" coords="225,56,231,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=CC00CC" target="_self" />
          <area shape="rect" coords="233,56,239,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=CC00FF" target="_self" />
          <area shape="rect" coords="241,56,247,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=FF0000" target="_self" />
          <area shape="rect" coords="249,56,255,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=FF0033" target="_self" />
          <area shape="rect" coords="257,56,263,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=FF0066" target="_self" />

          <area shape="rect" coords="265,56,271,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=FF0099" target="_self" />
          <area shape="rect" coords="273,56,279,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=FF00CC" target="_self" />
          <area shape="rect" coords="281,56,287,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=FF00FF" target="_self" />
          </map>
          <a> <img usemap="#colmap_Strichfarbe_ZE_GEW" src="gfx/colortable.gif" border="0" width="289" height="67" alt="" /></a> 
          <br />
          <map name="colmap_Strichfarbe_ZE_GEW_grau" id="id">
          <area shape="rect" coords="1,1,7,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=000000" target="_self" />
          <area shape="rect" coords="9,1,15,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=111111" target="_self" />
          <area shape="rect" coords="17,1,23,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=222222" target="_self" />
          <area shape="rect" coords="25,1,31,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=333333" target="_self" />
          <area shape="rect" coords="33,1,39,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=444444" target="_self" />
          <area shape="rect" coords="41,1,47,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=555555" target="_self" />
          <area shape="rect" coords="49,1,55,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=666666" target="_self" />
          <area shape="rect" coords="57,1,63,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=777777" target="_self" />
          <area shape="rect" coords="65,1,71,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=888888" target="_self" />
          <area shape="rect" coords="73,1,79,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=999999" target="_self" />
          <area shape="rect" coords="81,1,87,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=aaaaaa" target="_self" />
          <area shape="rect" coords="89,1,95,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=bbbbbb" target="_self" />
          <area shape="rect" coords="97,1,103,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=cccccc" target="_self" />
          <area shape="rect" coords="105,1,111,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=dddddd" target="_self" />
          <area shape="rect" coords="113,1,119,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=eeeeee" target="_self" />
          <area shape="rect" coords="121,1,127,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_GEW=ffffff" target="_self" />
          </map>
          <a> <img usemap="#colmap_Strichfarbe_ZE_GEW_grau" src="gfx/colortable_grau.png" border="0" width="129" height="12" alt="" /></a>
          
          
          <br />
          Selbst definierter Hex-RGB-Wert: #
				  <input name="Strichfarbe_ZE_GEW" type="text" id="Strichfarbe_ZE_GEW" value="<?php echo $_SESSION['Dokument']['Strichfarbe_GEW']; ?>" size="8" maxlength="6" />
                    <input name="button7" type="submit" class="button_blau_abschicken" id="button7" style="cursor: pointer;" value="OK" />
                    <br />                
</td>
              </tr>
              
              
              
                <tr>
                <td height="26" colspan="3" valign="top"><br />
                <div style="height:0px; border-bottom:1px solid #999999; margin-top:0px; margin-bottom:7px;"></div>                 
                
                </td>
              </tr>
              
              
              
              
              
              
              
              
              <tr>
                <td valign="top">Farbe für <br /> 
                Fernbahnnetz
</td>
                <td valign="top">
        			<div style="padding:5px; background-color:#eeeeee; height:70px;">
                    	<div style="border:solid #666666 0px; width:20px; height:2px; overflow:hidden; background-color:#<?php echo $_SESSION['Dokument']['Strichfarbe_db']; ?>;"></div>
                    </div>    
                        </td>
                <td valign="top">
                
                
                
                
                     
          <map name="colmap_Strichfarbe_ZE_db" id="id">
          <area shape="rect" coords="1,1,7,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=00FF00" target="_self" />
          <area shape="rect" coords="9,1,15,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=00FF33" target="_self" />
          <area shape="rect" coords="17,1,23,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=00FF66" target="_self" />
          <area shape="rect" coords="25,1,31,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=00FF99" target="_self" />
          <area shape="rect" coords="33,1,39,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=00FFCC" target="_self" />
          <area shape="rect" coords="41,1,47,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=00FFFF" target="_self" />

          <area shape="rect" coords="49,1,55,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=33FF00" target="_self" />
          <area shape="rect" coords="57,1,63,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=33FF33" target="_self" />
          <area shape="rect" coords="65,1,71,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=33FF66" target="_self" />
          <area shape="rect" coords="73,1,79,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=33FF99" target="_self" />
          <area shape="rect" coords="81,1,87,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=33FFCC" target="_self" />
          <area shape="rect" coords="89,1,95,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=33FFFF" target="_self" />
          <area shape="rect" coords="97,1,103,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=66FF00" target="_self" />
          <area shape="rect" coords="105,1,111,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=66FF33" target="_self" />
          <area shape="rect" coords="113,1,119,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=66FF66" target="_self" />

          <area shape="rect" coords="121,1,127,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=66FF99" target="_self" />
          <area shape="rect" coords="129,1,135,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=66FFCC" target="_self" />
          <area shape="rect" coords="137,1,143,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=66FFFF" target="_self" />
          <area shape="rect" coords="145,1,151,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=99FF00" target="_self" />
          <area shape="rect" coords="153,1,159,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=99FF33" target="_self" />
          <area shape="rect" coords="161,1,167,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=99FF66" target="_self" />
          <area shape="rect" coords="169,1,175,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=99FF99" target="_self" />
          <area shape="rect" coords="177,1,183,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=99FFCC" target="_self" />
          <area shape="rect" coords="185,1,191,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=99FFFF" target="_self" />

          <area shape="rect" coords="193,1,199,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=CCFF00" target="_self" />
          <area shape="rect" coords="201,1,207,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=CCFF33" target="_self" />
          <area shape="rect" coords="209,1,215,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=CCFF66" target="_self" />
          <area shape="rect" coords="217,1,223,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=CCFF99" target="_self" />
          <area shape="rect" coords="225,1,231,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=CCFFCC" target="_self" />
          <area shape="rect" coords="233,1,239,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=CCFFFF" target="_self" />
          <area shape="rect" coords="241,1,247,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=FFFF00" target="_self" />
          <area shape="rect" coords="249,1,255,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=FFFF33" target="_self" />
          <area shape="rect" coords="257,1,263,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=FFFF66" target="_self" />

          <area shape="rect" coords="265,1,271,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=FFFF99" target="_self" />
          <area shape="rect" coords="273,1,279,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=FFFFCC" target="_self" />
          <area shape="rect" coords="281,1,287,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=FFFFFF" target="_self" />
          <area shape="rect" coords="1,12,7,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=00CC00" target="_self" />
          <area shape="rect" coords="9,12,15,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=00CC33" target="_self" />
          <area shape="rect" coords="17,12,23,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=00CC66" target="_self" />
          <area shape="rect" coords="25,12,31,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=00CC99" target="_self" />
          <area shape="rect" coords="33,12,39,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=00CCCC" target="_self" />
          <area shape="rect" coords="41,12,47,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=00CCFF" target="_self" />

          <area shape="rect" coords="49,12,55,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=33CC00" target="_self" />
          <area shape="rect" coords="57,12,63,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=33CC33" target="_self" />
          <area shape="rect" coords="65,12,71,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=33CC66" target="_self" />
          <area shape="rect" coords="73,12,79,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=33CC99" target="_self" />
          <area shape="rect" coords="81,12,87,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=33CCCC" target="_self" />
          <area shape="rect" coords="89,12,95,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=33CCFF" target="_self" />
          <area shape="rect" coords="97,12,103,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=66CC00" target="_self" />
          <area shape="rect" coords="105,12,111,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=66CC33" target="_self" />
          <area shape="rect" coords="113,12,119,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=66CC66" target="_self" />

          <area shape="rect" coords="121,12,127,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=66CC99" target="_self" />
          <area shape="rect" coords="129,12,135,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=66CCCC" target="_self" />
          <area shape="rect" coords="137,12,143,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=66CCFF" target="_self" />
          <area shape="rect" coords="145,12,151,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=99CC00" target="_self" />
          <area shape="rect" coords="153,12,159,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=99CC33" target="_self" />

          <area shape="rect" coords="161,12,167,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=99CC66" target="_self" />
          <area shape="rect" coords="169,12,175,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=99CC99" target="_self" />
          <area shape="rect" coords="177,12,183,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=99CCCC" target="_self" />
          <area shape="rect" coords="185,12,191,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=99CCFF" target="_self" />

          <area shape="rect" coords="193,12,199,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=CCCC00" target="_self" />
          <area shape="rect" coords="201,12,207,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=CCCC33" target="_self" />
          <area shape="rect" coords="209,12,215,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=CCCC66" target="_self" />
          <area shape="rect" coords="217,12,223,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=CCCC99" target="_self" />
          <area shape="rect" coords="225,12,231,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=CCCCCC" target="_self" />
          <area shape="rect" coords="233,12,239,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=CCCCFF" target="_self" />
          <area shape="rect" coords="241,12,247,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=FFCC00" target="_self" />
          <area shape="rect" coords="249,12,255,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=FFCC33" target="_self" />
          <area shape="rect" coords="257,12,263,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=FFCC66" target="_self" />

          <area shape="rect" coords="265,12,271,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=FFCC99" target="_self" />
          <area shape="rect" coords="273,12,279,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=FFCCCC" target="_self" />
          <area shape="rect" coords="281,12,287,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=FFCCFF" target="_self" />
          <area shape="rect" coords="1,23,7,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=009900" target="_self" />
          <area shape="rect" coords="9,23,15,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=009933" target="_self" />
          <area shape="rect" coords="17,23,23,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=009966" target="_self" />
          <area shape="rect" coords="25,23,31,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=009999" target="_self" />
          <area shape="rect" coords="33,23,39,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=0099CC" target="_self" />
          <area shape="rect" coords="41,23,47,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=0099FF" target="_self" />

          <area shape="rect" coords="49,23,55,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=339900" target="_self" />
          <area shape="rect" coords="57,23,63,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=339933" target="_self" />
          <area shape="rect" coords="65,23,71,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=339966" target="_self" />
          <area shape="rect" coords="73,23,79,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=339999" target="_self" />
          <area shape="rect" coords="81,23,87,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=3399CC" target="_self" />
          <area shape="rect" coords="89,23,95,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=3399FF" target="_self" />
          <area shape="rect" coords="97,23,103,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=669900" target="_self" />
          <area shape="rect" coords="105,23,111,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=669933" target="_self" />
          <area shape="rect" coords="113,23,119,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=669966" target="_self" />

          <area shape="rect" coords="121,23,127,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=669999" target="_self" />
          <area shape="rect" coords="129,23,135,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=6699CC" target="_self" />
          <area shape="rect" coords="137,23,143,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=6699FF" target="_self" />
          <area shape="rect" coords="145,23,151,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=999900" target="_self" />
          <area shape="rect" coords="153,23,159,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=999933" target="_self" />
          <area shape="rect" coords="161,23,167,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=999966" target="_self" />
          <area shape="rect" coords="169,23,175,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=999999" target="_self" />
          <area shape="rect" coords="177,23,183,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=9999CC" target="_self" />
          <area shape="rect" coords="185,23,191,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=9999FF" target="_self" />

          <area shape="rect" coords="193,23,199,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=CC9900" target="_self" />
          <area shape="rect" coords="201,23,207,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=CC9933" target="_self" />
          <area shape="rect" coords="209,23,215,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=CC9966" target="_self" />
          <area shape="rect" coords="217,23,223,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=CC9999" target="_self" />
          <area shape="rect" coords="225,23,231,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=CC99CC" target="_self" />
          <area shape="rect" coords="233,23,239,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=CC99FF" target="_self" />
          <area shape="rect" coords="241,23,247,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=FF9900" target="_self" />
          <area shape="rect" coords="249,23,255,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=FF9933" target="_self" />
          <area shape="rect" coords="257,23,263,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=FF9966" target="_self" />

          <area shape="rect" coords="265,23,271,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=FF9999" target="_self" />
          <area shape="rect" coords="273,23,279,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=FF99CC" target="_self" />
          <area shape="rect" coords="281,23,287,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=FF99FF" target="_self" />
          <area shape="rect" coords="1,34,7,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=006600" target="_self" />
          <area shape="rect" coords="9,34,15,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=006633" target="_self" />
          <area shape="rect" coords="17,34,23,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=006666" target="_self" />
          <area shape="rect" coords="25,34,31,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=006699" target="_self" />
          <area shape="rect" coords="33,34,39,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=0066CC" target="_self" />
          <area shape="rect" coords="41,34,47,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=0066FF" target="_self" />

          <area shape="rect" coords="49,34,55,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=336600" target="_self" />
          <area shape="rect" coords="57,34,63,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=336633" target="_self" />
          <area shape="rect" coords="65,34,71,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=336666" target="_self" />
          <area shape="rect" coords="73,34,79,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=336699" target="_self" />
          <area shape="rect" coords="81,34,87,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=3366CC" target="_self" />
          <area shape="rect" coords="89,34,95,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=3366FF" target="_self" />
          <area shape="rect" coords="97,34,103,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=666600" target="_self" />
          <area shape="rect" coords="105,34,111,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=666633" target="_self" />
          <area shape="rect" coords="113,34,119,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=666666" target="_self" />

          <area shape="rect" coords="121,34,127,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=666699" target="_self" />
          <area shape="rect" coords="129,34,135,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=6666CC" target="_self" />
          <area shape="rect" coords="137,34,143,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=6666FF" target="_self" />
          <area shape="rect" coords="145,34,151,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=996600" target="_self" />
          <area shape="rect" coords="153,34,159,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=996633" target="_self" />
          <area shape="rect" coords="161,34,167,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=996666" target="_self" />
          <area shape="rect" coords="169,34,175,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=996699" target="_self" />
          <area shape="rect" coords="177,34,183,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=9966CC" target="_self" />
          <area shape="rect" coords="185,34,191,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=9966FF" target="_self" />

          <area shape="rect" coords="193,34,199,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=CC6600" target="_self" />
          <area shape="rect" coords="201,34,207,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=CC6633" target="_self" />
          <area shape="rect" coords="209,34,215,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=CC6666" target="_self" />
          <area shape="rect" coords="217,34,223,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=CC6699" target="_self" />
          <area shape="rect" coords="225,34,231,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=CC66CC" target="_self" />
          <area shape="rect" coords="233,34,239,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=CC66FF" target="_self" />
          <area shape="rect" coords="241,34,247,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=FF6600" target="_self" />
          <area shape="rect" coords="249,34,255,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=FF6633" target="_self" />
          <area shape="rect" coords="257,34,263,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=FF6666" target="_self" />

          <area shape="rect" coords="265,34,271,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=FF6699" target="_self" />
          <area shape="rect" coords="273,34,279,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=FF66CC" target="_self" />
          <area shape="rect" coords="281,34,287,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=FF66FF" target="_self" />
          <area shape="rect" coords="1,45,7,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=003300" target="_self" />
          <area shape="rect" coords="9,45,15,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=003333" target="_self" />
          <area shape="rect" coords="17,45,23,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=003366" target="_self" />
          <area shape="rect" coords="25,45,31,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=003399" target="_self" />
          <area shape="rect" coords="33,45,39,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=0033CC" target="_self" />
          <area shape="rect" coords="41,45,47,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=0033FF" target="_self" />

          <area shape="rect" coords="49,45,55,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=333300" target="_self" />
          <area shape="rect" coords="57,45,63,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=333333" target="_self" />
          <area shape="rect" coords="65,45,71,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=333366" target="_self" />
          <area shape="rect" coords="73,45,79,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=333399" target="_self" />
          <area shape="rect" coords="81,45,87,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=3333CC" target="_self" />
          <area shape="rect" coords="89,45,95,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=3333FF" target="_self" />
          <area shape="rect" coords="97,45,103,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=663300" target="_self" />
          <area shape="rect" coords="105,45,111,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=663333" target="_self" />
          <area shape="rect" coords="113,45,119,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=663366" target="_self" />

          <area shape="rect" coords="121,45,127,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=663399" target="_self" />
          <area shape="rect" coords="129,45,135,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=6633CC" target="_self" />
          <area shape="rect" coords="137,45,143,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=6633FF" target="_self" />
          <area shape="rect" coords="145,45,151,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=993300" target="_self" />
          <area shape="rect" coords="153,45,159,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=993333" target="_self" />
          <area shape="rect" coords="161,45,167,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=993366" target="_self" />
          <area shape="rect" coords="169,45,175,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=993399" target="_self" />
          <area shape="rect" coords="177,45,183,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=9933CC" target="_self" />
          <area shape="rect" coords="185,45,191,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=9933FF" target="_self" />

          <area shape="rect" coords="193,45,199,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=CC3300" target="_self" />
          <area shape="rect" coords="201,45,207,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=CC3333" target="_self" />
          <area shape="rect" coords="209,45,215,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=CC3366" target="_self" />
          <area shape="rect" coords="217,45,223,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=CC3399" target="_self" />
          <area shape="rect" coords="225,45,231,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=CC33CC" target="_self" />
          <area shape="rect" coords="233,45,239,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=CC33FF" target="_self" />
          <area shape="rect" coords="241,45,247,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=FF3300" target="_self" />
          <area shape="rect" coords="249,45,255,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=FF3333" target="_self" />
          <area shape="rect" coords="257,45,263,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=FF3366" target="_self" />

          <area shape="rect" coords="265,45,271,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=FF3399" target="_self" />
          <area shape="rect" coords="273,45,279,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=FF33CC" target="_self" />
          <area shape="rect" coords="281,45,287,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=FF33FF" target="_self" />
          <area shape="rect" coords="1,56,7,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=000000" target="_self" />
          <area shape="rect" coords="9,56,15,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=000033" target="_self" />
          <area shape="rect" coords="17,56,23,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=000066" target="_self" />
          <area shape="rect" coords="25,56,31,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=000099" target="_self" />
          <area shape="rect" coords="33,56,39,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=0000CC" target="_self" />
          <area shape="rect" coords="41,56,47,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=0000FF" target="_self" />

          <area shape="rect" coords="49,56,55,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=330000" target="_self" />
          <area shape="rect" coords="57,56,63,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=330033" target="_self" />
          <area shape="rect" coords="65,56,71,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=330066" target="_self" />
          <area shape="rect" coords="73,56,79,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=330099" target="_self" />
          <area shape="rect" coords="81,56,87,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=3300CC" target="_self" />
          <area shape="rect" coords="89,56,95,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=3300FF" target="_self" />
          <area shape="rect" coords="97,56,103,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=660000" target="_self" />
          <area shape="rect" coords="105,56,111,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=660033" target="_self" />
          <area shape="rect" coords="113,56,119,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=660066" target="_self" />

          <area shape="rect" coords="121,56,127,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=660099" target="_self" />
          <area shape="rect" coords="129,56,135,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=6600CC" target="_self" />
          <area shape="rect" coords="137,56,143,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=6600FF" target="_self" />
          <area shape="rect" coords="145,56,151,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=990000" target="_self" />
          <area shape="rect" coords="153,56,159,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=990033" target="_self" />
          <area shape="rect" coords="161,56,167,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=990066" target="_self" />
          <area shape="rect" coords="169,56,175,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=990099" target="_self" />
          <area shape="rect" coords="177,56,183,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=9900CC" target="_self" />
          <area shape="rect" coords="185,56,191,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=9900FF" target="_self" />

          <area shape="rect" coords="193,56,199,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=CC0000" target="_self" />
          <area shape="rect" coords="201,56,207,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=CC0033" target="_self" />
          <area shape="rect" coords="209,56,215,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=CC0066" target="_self" />
          <area shape="rect" coords="217,56,223,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=CC0099" target="_self" />
          <area shape="rect" coords="225,56,231,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=CC00CC" target="_self" />
          <area shape="rect" coords="233,56,239,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=CC00FF" target="_self" />
          <area shape="rect" coords="241,56,247,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=FF0000" target="_self" />
          <area shape="rect" coords="249,56,255,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=FF0033" target="_self" />
          <area shape="rect" coords="257,56,263,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=FF0066" target="_self" />

          <area shape="rect" coords="265,56,271,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=FF0099" target="_self" />
          <area shape="rect" coords="273,56,279,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=FF00CC" target="_self" />
          <area shape="rect" coords="281,56,287,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=FF00FF" target="_self" />
          </map>
          <a> <img usemap="#colmap_Strichfarbe_ZE_db" src="gfx/colortable.gif" border="0" width="289" height="67" alt="" /></a> 
          <br />
          <map name="colmap_Strichfarbe_ZE_db_grau" id="id">
          <area shape="rect" coords="1,1,7,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=000000" target="_self" />
          <area shape="rect" coords="9,1,15,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=111111" target="_self" />
          <area shape="rect" coords="17,1,23,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=222222" target="_self" />
          <area shape="rect" coords="25,1,31,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=333333" target="_self" />
          <area shape="rect" coords="33,1,39,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=444444" target="_self" />
          <area shape="rect" coords="41,1,47,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=555555" target="_self" />
          <area shape="rect" coords="49,1,55,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=666666" target="_self" />
          <area shape="rect" coords="57,1,63,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=777777" target="_self" />
          <area shape="rect" coords="65,1,71,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=888888" target="_self" />
          <area shape="rect" coords="73,1,79,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=999999" target="_self" />
          <area shape="rect" coords="81,1,87,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=aaaaaa" target="_self" />
          <area shape="rect" coords="89,1,95,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=bbbbbb" target="_self" />
          <area shape="rect" coords="97,1,103,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=cccccc" target="_self" />
          <area shape="rect" coords="105,1,111,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=dddddd" target="_self" />
          <area shape="rect" coords="113,1,119,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=eeeeee" target="_self" />
          <area shape="rect" coords="121,1,127,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_db=ffffff" target="_self" />
          </map>
          <a> <img usemap="#colmap_Strichfarbe_ZE_db_grau" src="gfx/colortable_grau.png" border="0" width="129" height="12" alt="" /></a>
          
          
          <br />
          Selbst definierter Hex-RGB-Wert: #
				  <input name="Strichfarbe_ZE_db" type="text" id="Strichfarbe_ZE_db" value="<?php echo $_SESSION['Dokument']['Strichfarbe_db']; ?>" size="8" maxlength="6" />
                    <input name="button" type="submit" class="button_blau_abschicken" id="button" style="cursor: pointer;" value="OK" />
                    <br />                
</td>
              </tr>
              
                   
              
              
              
              
               <tr>
                <td height="26" colspan="3" valign="top"><br />
                <div style="height:0px; border-bottom:1px solid #999999; margin-top:0px; margin-bottom:7px;"></div>                 
                
                </td>
              </tr>
              <tr>
                <td valign="top">Farbe für <br />
Bundesautobahnen <br />
als Zusatzebene</td>
                <td valign="top">
           	    	<div style="padding:5px; background-color:#eeeeee; height:70px;">
                        <div style="border:solid #666666 0px; width:20px; height:2px; overflow:hidden; background-color:#<?php echo $_SESSION['Dokument']['Strichfarbe_BAB']; ?>;"></div> 
                        <br />
    					<?php 
						// Signatur ausblenden wenn nicht aktiviert
						if($_SESSION['Dokument']['Strichfarbe_BAB_Signatur'])
						{
							?>
							<div style="border-left: 0px; border-right: 0px; border-top: solid #<?php echo $_SESSION['Dokument']['Strichfarbe_BAB_Kontur']; ?> 2px;  border-bottom: solid #<?php echo $_SESSION['Dokument']['Strichfarbe_BAB_Kontur']; ?> 2px; width:20px; height:2px; overflow:hidden; background-color:#<?php echo $_SESSION['Dokument']['Strichfarbe_BAB']; ?>;"></div>
							
							<?php 
						}
						?>
                    </div>    
                </td>
                <td valign="top">
                
                
                
                
                     
          <map name="colmap_Strichfarbe_ZE_BAB" id="id">
          <area shape="rect" coords="1,1,7,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=00FF00" target="_self" />
          <area shape="rect" coords="9,1,15,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=00FF33" target="_self" />
          <area shape="rect" coords="17,1,23,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=00FF66" target="_self" />
          <area shape="rect" coords="25,1,31,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=00FF99" target="_self" />
          <area shape="rect" coords="33,1,39,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=00FFCC" target="_self" />
          <area shape="rect" coords="41,1,47,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=00FFFF" target="_self" />

          <area shape="rect" coords="49,1,55,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=33FF00" target="_self" />
          <area shape="rect" coords="57,1,63,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=33FF33" target="_self" />
          <area shape="rect" coords="65,1,71,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=33FF66" target="_self" />
          <area shape="rect" coords="73,1,79,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=33FF99" target="_self" />
          <area shape="rect" coords="81,1,87,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=33FFCC" target="_self" />
          <area shape="rect" coords="89,1,95,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=33FFFF" target="_self" />
          <area shape="rect" coords="97,1,103,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=66FF00" target="_self" />
          <area shape="rect" coords="105,1,111,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=66FF33" target="_self" />
          <area shape="rect" coords="113,1,119,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=66FF66" target="_self" />

          <area shape="rect" coords="121,1,127,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=66FF99" target="_self" />
          <area shape="rect" coords="129,1,135,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=66FFCC" target="_self" />
          <area shape="rect" coords="137,1,143,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=66FFFF" target="_self" />
          <area shape="rect" coords="145,1,151,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=99FF00" target="_self" />
          <area shape="rect" coords="153,1,159,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=99FF33" target="_self" />
          <area shape="rect" coords="161,1,167,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=99FF66" target="_self" />
          <area shape="rect" coords="169,1,175,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=99FF99" target="_self" />
          <area shape="rect" coords="177,1,183,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=99FFCC" target="_self" />
          <area shape="rect" coords="185,1,191,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=99FFFF" target="_self" />

          <area shape="rect" coords="193,1,199,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=CCFF00" target="_self" />
          <area shape="rect" coords="201,1,207,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=CCFF33" target="_self" />
          <area shape="rect" coords="209,1,215,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=CCFF66" target="_self" />
          <area shape="rect" coords="217,1,223,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=CCFF99" target="_self" />
          <area shape="rect" coords="225,1,231,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=CCFFCC" target="_self" />
          <area shape="rect" coords="233,1,239,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=CCFFFF" target="_self" />
          <area shape="rect" coords="241,1,247,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=FFFF00" target="_self" />
          <area shape="rect" coords="249,1,255,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=FFFF33" target="_self" />
          <area shape="rect" coords="257,1,263,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=FFFF66" target="_self" />

          <area shape="rect" coords="265,1,271,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=FFFF99" target="_self" />
          <area shape="rect" coords="273,1,279,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=FFFFCC" target="_self" />
          <area shape="rect" coords="281,1,287,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=FFFFFF" target="_self" />
          <area shape="rect" coords="1,12,7,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=00CC00" target="_self" />
          <area shape="rect" coords="9,12,15,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=00CC33" target="_self" />
          <area shape="rect" coords="17,12,23,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=00CC66" target="_self" />
          <area shape="rect" coords="25,12,31,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=00CC99" target="_self" />
          <area shape="rect" coords="33,12,39,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=00CCCC" target="_self" />
          <area shape="rect" coords="41,12,47,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=00CCFF" target="_self" />

          <area shape="rect" coords="49,12,55,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=33CC00" target="_self" />
          <area shape="rect" coords="57,12,63,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=33CC33" target="_self" />
          <area shape="rect" coords="65,12,71,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=33CC66" target="_self" />
          <area shape="rect" coords="73,12,79,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=33CC99" target="_self" />
          <area shape="rect" coords="81,12,87,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=33CCCC" target="_self" />
          <area shape="rect" coords="89,12,95,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=33CCFF" target="_self" />
          <area shape="rect" coords="97,12,103,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=66CC00" target="_self" />
          <area shape="rect" coords="105,12,111,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=66CC33" target="_self" />
          <area shape="rect" coords="113,12,119,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=66CC66" target="_self" />

          <area shape="rect" coords="121,12,127,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=66CC99" target="_self" />
          <area shape="rect" coords="129,12,135,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=66CCCC" target="_self" />
          <area shape="rect" coords="137,12,143,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=66CCFF" target="_self" />
          <area shape="rect" coords="145,12,151,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=99CC00" target="_self" />
          <area shape="rect" coords="153,12,159,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=99CC33" target="_self" />
          <area shape="rect" coords="161,12,167,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=99CC66" target="_self" />
          <area shape="rect" coords="169,12,175,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=99CC99" target="_self" />
          <area shape="rect" coords="177,12,183,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=99CCCC" target="_self" />
          <area shape="rect" coords="185,12,191,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=99CCFF" target="_self" />

          <area shape="rect" coords="193,12,199,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=CCCC00" target="_self" />
          <area shape="rect" coords="201,12,207,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=CCCC33" target="_self" />
          <area shape="rect" coords="209,12,215,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=CCCC66" target="_self" />
          <area shape="rect" coords="217,12,223,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=CCCC99" target="_self" />
          <area shape="rect" coords="225,12,231,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=CCCCCC" target="_self" />
          <area shape="rect" coords="233,12,239,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=CCCCFF" target="_self" />
          <area shape="rect" coords="241,12,247,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=FFCC00" target="_self" />
          <area shape="rect" coords="249,12,255,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=FFCC33" target="_self" />
          <area shape="rect" coords="257,12,263,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=FFCC66" target="_self" />

          <area shape="rect" coords="265,12,271,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=FFCC99" target="_self" />
          <area shape="rect" coords="273,12,279,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=FFCCCC" target="_self" />
          <area shape="rect" coords="281,12,287,21" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=FFCCFF" target="_self" />
          <area shape="rect" coords="1,23,7,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=009900" target="_self" />
          <area shape="rect" coords="9,23,15,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=009933" target="_self" />
          <area shape="rect" coords="17,23,23,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=009966" target="_self" />
          <area shape="rect" coords="25,23,31,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=009999" target="_self" />
          <area shape="rect" coords="33,23,39,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=0099CC" target="_self" />
          <area shape="rect" coords="41,23,47,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=0099FF" target="_self" />

          <area shape="rect" coords="49,23,55,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=339900" target="_self" />
          <area shape="rect" coords="57,23,63,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=339933" target="_self" />
          <area shape="rect" coords="65,23,71,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=339966" target="_self" />
          <area shape="rect" coords="73,23,79,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=339999" target="_self" />
          <area shape="rect" coords="81,23,87,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=3399CC" target="_self" />
          <area shape="rect" coords="89,23,95,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=3399FF" target="_self" />
          <area shape="rect" coords="97,23,103,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=669900" target="_self" />
          <area shape="rect" coords="105,23,111,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=669933" target="_self" />
          <area shape="rect" coords="113,23,119,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=669966" target="_self" />

          <area shape="rect" coords="121,23,127,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=669999" target="_self" />
          <area shape="rect" coords="129,23,135,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=6699CC" target="_self" />
          <area shape="rect" coords="137,23,143,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=6699FF" target="_self" />
          <area shape="rect" coords="145,23,151,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=999900" target="_self" />
          <area shape="rect" coords="153,23,159,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=999933" target="_self" />
          <area shape="rect" coords="161,23,167,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=999966" target="_self" />
          <area shape="rect" coords="169,23,175,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=999999" target="_self" />
          <area shape="rect" coords="177,23,183,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=9999CC" target="_self" />
          <area shape="rect" coords="185,23,191,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=9999FF" target="_self" />

          <area shape="rect" coords="193,23,199,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=CC9900" target="_self" />
          <area shape="rect" coords="201,23,207,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=CC9933" target="_self" />
          <area shape="rect" coords="209,23,215,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=CC9966" target="_self" />
          <area shape="rect" coords="217,23,223,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=CC9999" target="_self" />
          <area shape="rect" coords="225,23,231,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=CC99CC" target="_self" />
          <area shape="rect" coords="233,23,239,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=CC99FF" target="_self" />
          <area shape="rect" coords="241,23,247,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=FF9900" target="_self" />
          <area shape="rect" coords="249,23,255,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=FF9933" target="_self" />
          <area shape="rect" coords="257,23,263,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=FF9966" target="_self" />

          <area shape="rect" coords="265,23,271,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=FF9999" target="_self" />
          <area shape="rect" coords="273,23,279,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=FF99CC" target="_self" />
          <area shape="rect" coords="281,23,287,32" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=FF99FF" target="_self" />
          <area shape="rect" coords="1,34,7,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=006600" target="_self" />
          <area shape="rect" coords="9,34,15,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=006633" target="_self" />
          <area shape="rect" coords="17,34,23,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=006666" target="_self" />
          <area shape="rect" coords="25,34,31,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=006699" target="_self" />
          <area shape="rect" coords="33,34,39,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=0066CC" target="_self" />
          <area shape="rect" coords="41,34,47,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=0066FF" target="_self" />

          <area shape="rect" coords="49,34,55,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=336600" target="_self" />
          <area shape="rect" coords="57,34,63,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=336633" target="_self" />
          <area shape="rect" coords="65,34,71,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=336666" target="_self" />
          <area shape="rect" coords="73,34,79,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=336699" target="_self" />
          <area shape="rect" coords="81,34,87,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=3366CC" target="_self" />
          <area shape="rect" coords="89,34,95,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=3366FF" target="_self" />
          <area shape="rect" coords="97,34,103,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=666600" target="_self" />
          <area shape="rect" coords="105,34,111,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=666633" target="_self" />
          <area shape="rect" coords="113,34,119,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=666666" target="_self" />

          <area shape="rect" coords="121,34,127,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=666699" target="_self" />
          <area shape="rect" coords="129,34,135,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=6666CC" target="_self" />
          <area shape="rect" coords="137,34,143,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=6666FF" target="_self" />
          <area shape="rect" coords="145,34,151,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=996600" target="_self" />
          <area shape="rect" coords="153,34,159,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=996633" target="_self" />
          <area shape="rect" coords="161,34,167,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=996666" target="_self" />
          <area shape="rect" coords="169,34,175,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=996699" target="_self" />
          <area shape="rect" coords="177,34,183,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=9966CC" target="_self" />
          <area shape="rect" coords="185,34,191,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=9966FF" target="_self" />

          <area shape="rect" coords="193,34,199,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=CC6600" target="_self" />
          <area shape="rect" coords="201,34,207,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=CC6633" target="_self" />
          <area shape="rect" coords="209,34,215,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=CC6666" target="_self" />
          <area shape="rect" coords="217,34,223,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=CC6699" target="_self" />
          <area shape="rect" coords="225,34,231,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=CC66CC" target="_self" />
          <area shape="rect" coords="233,34,239,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=CC66FF" target="_self" />
          <area shape="rect" coords="241,34,247,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=FF6600" target="_self" />
          <area shape="rect" coords="249,34,255,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=FF6633" target="_self" />
          <area shape="rect" coords="257,34,263,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=FF6666" target="_self" />

          <area shape="rect" coords="265,34,271,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=FF6699" target="_self" />
          <area shape="rect" coords="273,34,279,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=FF66CC" target="_self" />
          <area shape="rect" coords="281,34,287,43" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=FF66FF" target="_self" />
          <area shape="rect" coords="1,45,7,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=003300" target="_self" />
          <area shape="rect" coords="9,45,15,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=003333" target="_self" />
          <area shape="rect" coords="17,45,23,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=003366" target="_self" />
          <area shape="rect" coords="25,45,31,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=003399" target="_self" />
          <area shape="rect" coords="33,45,39,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=0033CC" target="_self" />
          <area shape="rect" coords="41,45,47,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=0033FF" target="_self" />

          <area shape="rect" coords="49,45,55,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=333300" target="_self" />
          <area shape="rect" coords="57,45,63,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=333333" target="_self" />
          <area shape="rect" coords="65,45,71,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=333366" target="_self" />
          <area shape="rect" coords="73,45,79,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=333399" target="_self" />
          <area shape="rect" coords="81,45,87,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=3333CC" target="_self" />
          <area shape="rect" coords="89,45,95,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=3333FF" target="_self" />
          <area shape="rect" coords="97,45,103,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=663300" target="_self" />
          <area shape="rect" coords="105,45,111,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=663333" target="_self" />
          <area shape="rect" coords="113,45,119,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=663366" target="_self" />

          <area shape="rect" coords="121,45,127,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=663399" target="_self" />
          <area shape="rect" coords="129,45,135,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=6633CC" target="_self" />
          <area shape="rect" coords="137,45,143,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=6633FF" target="_self" />
          <area shape="rect" coords="145,45,151,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=993300" target="_self" />
          <area shape="rect" coords="153,45,159,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=993333" target="_self" />
          <area shape="rect" coords="161,45,167,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=993366" target="_self" />
          <area shape="rect" coords="169,45,175,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=993399" target="_self" />
          <area shape="rect" coords="177,45,183,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=9933CC" target="_self" />
          <area shape="rect" coords="185,45,191,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=9933FF" target="_self" />

          <area shape="rect" coords="193,45,199,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=CC3300" target="_self" />
          <area shape="rect" coords="201,45,207,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=CC3333" target="_self" />
          <area shape="rect" coords="209,45,215,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=CC3366" target="_self" />
          <area shape="rect" coords="217,45,223,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=CC3399" target="_self" />
          <area shape="rect" coords="225,45,231,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=CC33CC" target="_self" />
          <area shape="rect" coords="233,45,239,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=CC33FF" target="_self" />
          <area shape="rect" coords="241,45,247,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=FF3300" target="_self" />
          <area shape="rect" coords="249,45,255,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=FF3333" target="_self" />
          <area shape="rect" coords="257,45,263,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=FF3366" target="_self" />

          <area shape="rect" coords="265,45,271,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=FF3399" target="_self" />
          <area shape="rect" coords="273,45,279,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=FF33CC" target="_self" />
          <area shape="rect" coords="281,45,287,54" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=FF33FF" target="_self" />
          <area shape="rect" coords="1,56,7,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=000000" target="_self" />
          <area shape="rect" coords="9,56,15,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=000033" target="_self" />
          <area shape="rect" coords="17,56,23,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=000066" target="_self" />
          <area shape="rect" coords="25,56,31,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=000099" target="_self" />
          <area shape="rect" coords="33,56,39,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=0000CC" target="_self" />
          <area shape="rect" coords="41,56,47,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=0000FF" target="_self" />

          <area shape="rect" coords="49,56,55,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=330000" target="_self" />
          <area shape="rect" coords="57,56,63,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=330033" target="_self" />
          <area shape="rect" coords="65,56,71,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=330066" target="_self" />
          <area shape="rect" coords="73,56,79,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=330099" target="_self" />
          <area shape="rect" coords="81,56,87,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=3300CC" target="_self" />
          <area shape="rect" coords="89,56,95,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=3300FF" target="_self" />
          <area shape="rect" coords="97,56,103,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=660000" target="_self" />
          <area shape="rect" coords="105,56,111,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=660033" target="_self" />
          <area shape="rect" coords="113,56,119,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=660066" target="_self" />

          <area shape="rect" coords="121,56,127,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=660099" target="_self" />
          <area shape="rect" coords="129,56,135,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=6600CC" target="_self" />
          <area shape="rect" coords="137,56,143,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=6600FF" target="_self" />
          <area shape="rect" coords="145,56,151,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=990000" target="_self" />
          <area shape="rect" coords="153,56,159,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=990033" target="_self" />
          <area shape="rect" coords="161,56,167,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=990066" target="_self" />
          <area shape="rect" coords="169,56,175,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=990099" target="_self" />
          <area shape="rect" coords="177,56,183,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=9900CC" target="_self" />
          <area shape="rect" coords="185,56,191,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=9900FF" target="_self" />

          <area shape="rect" coords="193,56,199,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=CC0000" target="_self" />
          <area shape="rect" coords="201,56,207,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=CC0033" target="_self" />
          <area shape="rect" coords="209,56,215,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=CC0066" target="_self" />
          <area shape="rect" coords="217,56,223,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=CC0099" target="_self" />
          <area shape="rect" coords="225,56,231,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=CC00CC" target="_self" />
          <area shape="rect" coords="233,56,239,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=CC00FF" target="_self" />
          <area shape="rect" coords="241,56,247,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=FF0000" target="_self" />
          <area shape="rect" coords="249,56,255,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=FF0033" target="_self" />
          <area shape="rect" coords="257,56,263,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=FF0066" target="_self" />

          <area shape="rect" coords="265,56,271,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=FF0099" target="_self" />
          <area shape="rect" coords="273,56,279,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=FF00CC" target="_self" />
          <area shape="rect" coords="281,56,287,65" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=FF00FF" target="_self" />
          </map>
          <a> <img usemap="#colmap_Strichfarbe_ZE_BAB" src="gfx/colortable.gif" border="0" width="289" height="67" alt="" /></a> 
          <br />
          <map name="colmap_Strichfarbe_ZE_BAB_grau" id="id">
          <area shape="rect" coords="1,1,7,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=000000" target="_self" />
          <area shape="rect" coords="9,1,15,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=111111" target="_self" />
          <area shape="rect" coords="17,1,23,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=222222" target="_self" />
          <area shape="rect" coords="25,1,31,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=333333" target="_self" />
          <area shape="rect" coords="33,1,39,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=444444" target="_self" />
          <area shape="rect" coords="41,1,47,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=555555" target="_self" />
          <area shape="rect" coords="49,1,55,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=666666" target="_self" />
          <area shape="rect" coords="57,1,63,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=777777" target="_self" />
          <area shape="rect" coords="65,1,71,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=888888" target="_self" />
          <area shape="rect" coords="73,1,79,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=999999" target="_self" />
          <area shape="rect" coords="81,1,87,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=aaaaaa" target="_self" />
          <area shape="rect" coords="89,1,95,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=bbbbbb" target="_self" />
          <area shape="rect" coords="97,1,103,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=cccccc" target="_self" />
          <area shape="rect" coords="105,1,111,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=dddddd" target="_self" />
          <area shape="rect" coords="113,1,119,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=eeeeee" target="_self" />
          <area shape="rect" coords="121,1,127,10" href="svg_zeichenvorschrift_farbe.php?Strichfarbe_ZE_BAB=ffffff" target="_self" />
          </map>
          <a> <img usemap="#colmap_Strichfarbe_ZE_BAB_grau" src="gfx/colortable_grau.png" border="0" width="129" height="12" alt="" /></a>
          
          
          <br />
          <br />
          <!--Für größere Maßstabsbereiche eine Autobahnsignatur statt einer einfachen Linie anzeigen -->  
                    <a href="svg_zeichenvorschrift_farbe.php?Strichfarbe_BAB_Signatur=1" title="Anpassung der Farbdefinitionen der Karte" target="_self" >
                       <div class="button_standard_abschicken_a" style=" text-align:center; background-color: #BDDDFD; width:215px; padding-bottom:2px; padding-top:2px;">
                          <?php 
							if($_SESSION['Dokument']['Strichfarbe_BAB_Signatur'])
							{
								?>
                      			Signaturdarstellung für größere Maßstabsbereiche abschalten
  								<?php
							}
							else
							{
								?>
                              	Signaturdarstellung für größere Maßstabsbereiche einschalten 
                            	<?php 
							}
							?> 
                         </div>
                  </a>
                     <br />
                       <?php if($_SESSION['Dokument']['Strichfarbe_BAB_Signatur']) echo 'Farbe für kleine Maßst&auml;be gültig:<br />'; ?>
    			  <span style=" <?php if($_SESSION['Dokument']['Strichfarbe_BAB_Signatur']) echo 'color:#999999;'; ?> ">Selbst definierter Hex-RGB-Wert: #</span>
				  <input name="Strichfarbe_ZE_BAB" type="text" id="Strichfarbe_ZE_BAB" value="<?php echo $_SESSION['Dokument']['Strichfarbe_BAB']; ?>" size="8" maxlength="6" />
                    <input name="button7" type="submit" class="button_blau_abschicken" id="button7" style="cursor: pointer;" value="OK" />
                    <br />
           		  <a href="svg_zeichenvorschrift_farbe.php?Strichfarbe_BAB_Signatur=1" title="Anpassung der Farbdefinitionen der Karte" target="_self" >           		  </a>
				<br />   
				
				 <input name="Aktion" type="hidden" value="ZV_uebernahme" />
           </form>             
           </td>
              </tr>
         
              
              
              <tr>
                <td height="50" colspan="3" valign="top">
				<div style="float:left; margin-right:10px; padding-top:15px;">
                    <a href="svg_html.php?kopieren=1" target="_self"><img src="icons_viewer/back.png" alt="Zur&uuml;ck" />
                    <br />
                    zur&uuml;ck</a><br />
                </div>
                <br /><br />
    <br />
    
    <br />
    <br />
<div style="height:0px; border-bottom:1px solid #999999; margin-top:0px; margin-bottom:7px;"></div></td>
              </tr>
              <tr>
                <td height="50" valign="top">Werte zurücksetzen:</td>
                <td height="50" colspan="2" valign="top">
                	  Falls Sie die Standardfarben für den ausgewählten Indikator laden möchten, benutzen<br />
                      Sie bitte folgenden Schalter:<br />
                      <br />
                      <form action="svg_zeichenvorschrift_farbe.php" target="_self" method="post">
                        <input name="button7" type="submit" class="button_blau_abschicken" id="button7" style="cursor: pointer;" value="Auf Standardeinstellungen zurücksetzen" />
                        <input name="Zuruecksetzen" type="hidden" value="1" />
                      </form>
                      <br />                  </td>
              </tr>
              <tr>
                <td height="67" colspan="3"><br />
                  <br />
                 
                 <!--<span class="button_standard_abschicken_a" style="background:#BAD380;"><a href="svg_html.php#top" target="_self">&nbsp;&nbsp;&nbsp;&lt;= Zurück zur Karte&nbsp;&nbsp;&nbsp;</a></span> --></td>
   			 </tr>
   			      
        </table>
        
    
  <br />
 </div>
<br />
<br />

</body>
</html>
