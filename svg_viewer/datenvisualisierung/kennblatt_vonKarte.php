<?php 
session_start();
include("../includes_classes/verbindung_mysqli.php");


// Array mit Wortdefinitionen in mehreren Sprachen
$Sprache_Ausgabe['DE']['Kategorie'] = 'Kategorie'; 
$Sprache_Ausgabe['EN']['Kategorie'] = 'Category';
$Sprache_Ausgabe['DE']['Einheit'] = 'Maßeinheit'; 
$Sprache_Ausgabe['EN']['Einheit'] = 'Unit';
$Sprache_Ausgabe['DE']['Kurzbeschreibung'] = 'Kurzbeschreibung'; 
$Sprache_Ausgabe['EN']['Kurzbeschreibung'] = 'Short description';
$Sprache_Ausgabe['DE']['Bedeutung'] = 'Bedeutung und Interpretation'; 
$Sprache_Ausgabe['EN']['Bedeutung'] = 'Importance and interpretation';
$Sprache_Ausgabe['DE']['Datengrundlagen'] = 'Datengrundlagen'; 
$Sprache_Ausgabe['EN']['Datengrundlagen'] = 'Data basis';
$Sprache_Ausgabe['DE']['Methodik'] = 'Methodik'; 
$Sprache_Ausgabe['EN']['Methodik'] = 'Methodology';
$Sprache_Ausgabe['DE']['Verweise'] = 'Verweise'; 
$Sprache_Ausgabe['EN']['Verweise'] = 'Eprimands';
$Sprache_Ausgabe['DE']['Bemerkungen'] = 'Bemerkungen'; 
$Sprache_Ausgabe['EN']['Bemerkungen'] = 'Remarks';
$Sprache_Ausgabe['DE']['Bezugsebenen'] = 'Bezugsebenen'; 
$Sprache_Ausgabe['EN']['Bezugsebenen'] = 'Available levels';
$Sprache_Ausgabe['DE']['Dienste'] = 'Verfügbare Geodienste mit Links'; 
$Sprache_Ausgabe['EN']['Dienste'] = 'Links to available geoservices';
$Sprache_Ausgabe['DE']['Quellen'] = 'Quellen/Literatur'; 
$Sprache_Ausgabe['EN']['Quellen'] = 'Sources/References';



?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Indikatorkennblatt</title>
<link href="../screen_viewer.css" rel="stylesheet" type="text/css" />
<style type="text/css">

body {
	background-color:#FFF;
		font-size:12px;
}
td {
	border: 1px solid #333;
}
a:link {
	text-decoration: none;
	color: #039;
}
a:visited {
	text-decoration: none;
	color: #039;
}
a:hover {
	text-decoration: none;
	color: #666;
}
a:active {
	text-decoration: none;
	color: #039;
}
@media print {
	.nicht_im_print {display:none;}
}


</style>
</head>

<body>

<?php 


// Funktion um Links einzuschließen



if($_POST['ID_IND'] or $_GET['ID_IND'])
{
	if($_POST['ID_IND']) $INDIKAT0R = $_POST['ID_IND'];
	if($_GET['ID_IND']) $INDIKAT0R = $_GET['ID_IND'];
}
else
{
	$INDIKAT0R = $_SESSION['Dokument']['Fuellung']['Indikator'];
}

$SQL_IND = "SELECT * FROM m_indikatoren WHERE ID_INDIKATOR = '".$INDIKAT0R."'";
$Ergebnis_IND = mysqli_query($Verbindung,$SQL_IND);

// Lokalisierung
if($_SESSION['Dokument']['Sprache'] == 'DE')
{
$IND = mysqli_result($Ergebnis_IND,0,'ID_INDIKATOR');
$IND_Name = utf8_encode(mysqli_result($Ergebnis_IND,0,'INDIKATOR_NAME'));
$Einheit = utf8_encode(mysqli_result($Ergebnis_IND,0,'EINHEIT'));
$RUNDUNG_NACHKOMMASTELLEN = utf8_encode(mysqli_result($Ergebnis_IND,0,'RUNDUNG_NACHKOMMASTELLEN'));
$ZEITSCHNITTE = utf8_encode(mysqli_result($Ergebnis_IND,0,'ZEITSCHNITTE'));
$INFO_VIEWER_ZEILE_1 = utf8_encode(mysqli_result($Ergebnis_IND,0,'INFO_VIEWER_ZEILE_1'));
$INFO_VIEWER_ZEILE_2 = utf8_encode(mysqli_result($Ergebnis_IND,0,'INFO_VIEWER_ZEILE_2'));
$INFO_VIEWER_ZEILE_3 = utf8_encode(mysqli_result($Ergebnis_IND,0,'INFO_VIEWER_ZEILE_3'));
$INFO_VIEWER_ZEILE_4 = utf8_encode(mysqli_result($Ergebnis_IND,0,'INFO_VIEWER_ZEILE_4'));
$INFO_VIEWER_ZEILE_5 = utf8_encode(mysqli_result($Ergebnis_IND,0,'INFO_VIEWER_ZEILE_5'));
$INFO_VIEWER_ZEILE_6 = utf8_encode(mysqli_result($Ergebnis_IND,0,'INFO_VIEWER_ZEILE_6'));
$BEDEUTUNG_INTERPRETATION = nl2br(utf8_encode(mysqli_result($Ergebnis_IND,0,'BEDEUTUNG_INTERPRETATION')));
$DATENGRUNDLAGE_ATKIS = utf8_encode(mysqli_result($Ergebnis_IND,0,'DATENGRUNDLAGE_ATKIS'));
$DATENGRUNDLAGE_ZEILE_1 = utf8_encode(mysqli_result($Ergebnis_IND,0,'DATENGRUNDLAGE_ZEILE_1'));
$DATENGRUNDLAGE_ZEILE_2 = utf8_encode(mysqli_result($Ergebnis_IND,0,'DATENGRUNDLAGE_ZEILE_2'));
$METHODENBESCHREIBUNG = nl2br(utf8_encode(mysqli_result($Ergebnis_IND,0,'METHODIK')));
$VERWEISE = nl2br(utf8_encode(mysqli_result($Ergebnis_IND,0,'VERWEISE')));
$BERECHNUNG = nl2br(utf8_encode(mysqli_result($Ergebnis_IND,0,'BERECHNUNG')));
$BEMERKUNGEN = nl2br(utf8_encode(mysqli_result($Ergebnis_IND,0,'BEMERKUNGEN')));
$LITERATUR = nl2br(utf8_encode(mysqli_result($Ergebnis_IND,0,'LITERATUR')));
$BRD = utf8_encode(mysqli_result($Ergebnis_IND,0,'RAUMEBENE_BRD'));
$BLD = utf8_encode(mysqli_result($Ergebnis_IND,0,'RAUMEBENE_BLD'));
$KRS = utf8_encode(mysqli_result($Ergebnis_IND,0,'RAUMEBENE_KRS'));
$GEM = utf8_encode(mysqli_result($Ergebnis_IND,0,'RAUMEBENE_GEM'));
$ROR = utf8_encode(mysqli_result($Ergebnis_IND,0,'RAUMEBENE_ROR'));
$G50 = utf8_encode(mysqli_result($Ergebnis_IND,0,'RAUMEBENE_G50'));
$STT = utf8_encode(mysqli_result($Ergebnis_IND,0,'RAUMEBENE_STT'));
$PLR = utf8_encode(mysqli_result($Ergebnis_IND,0,'RAUMEBENE_PLR'));
$VWG = utf8_encode(mysqli_result($Ergebnis_IND,0,'RAUMEBENE_VWG'));
$RST = utf8_encode(mysqli_result($Ergebnis_IND,0,'RAUMEBENE_RST'));
$R10 = utf8_encode(mysqli_result($Ergebnis_IND,0,'RAUMEBENE_R10'));
$R05 = utf8_encode(mysqli_result($Ergebnis_IND,0,'RAUMEBENE_R05'));
$R5M = utf8_encode(mysqli_result($Ergebnis_IND,0,'RAUMEBENE_R5M'));
$R2M = utf8_encode(mysqli_result($Ergebnis_IND,0,'RAUMEBENE_R2M'));
$R1M = utf8_encode(mysqli_result($Ergebnis_IND,0,'RAUMEBENE_R1M'));
$WMS = utf8_encode(mysqli_result($Ergebnis_IND,0,'WMS'));
$WCS = utf8_encode(mysqli_result($Ergebnis_IND,0,'WCS'));
$WFS = utf8_encode(mysqli_result($Ergebnis_IND,0,'WFS'));
$WMS_link = "http://maps.ioer.de/cgi-bin/wms?map=" . $IND . "_100&";
$WCS_link = "http://maps.ioer.de/cgi-bin/wcs?map=" . $IND . "_wcs";
$WFS_link = "http://maps.ioer.de/cgi-bin/wfs?map=" . $IND;
}

if($_SESSION['Dokument']['Sprache'] == 'EN')
{
$IND = mysqli_result($Ergebnis_IND,0,'ID_INDIKATOR');
$IND_Name = utf8_encode(mysqli_result($Ergebnis_IND,0,'INDIKATOR_NAME_EN'));
$Einheit = utf8_encode(mysqli_result($Ergebnis_IND,0,'EINHEIT_EN'));
$RUNDUNG_NACHKOMMASTELLEN = utf8_encode(mysqli_result($Ergebnis_IND,0,'RUNDUNG_NACHKOMMASTELLEN'));
$ZEITSCHNITTE = utf8_encode(mysqli_result($Ergebnis_IND,0,'ZEITSCHNITTE'));
$INFO_VIEWER_ZEILE_1 = utf8_encode(mysqli_result($Ergebnis_IND,0,'INFO_VIEWER_ZEILE_1_EN'));
$INFO_VIEWER_ZEILE_2 = utf8_encode(mysqli_result($Ergebnis_IND,0,'INFO_VIEWER_ZEILE_2_EN'));
$INFO_VIEWER_ZEILE_3 = utf8_encode(mysqli_result($Ergebnis_IND,0,'INFO_VIEWER_ZEILE_3_EN'));
$INFO_VIEWER_ZEILE_4 = utf8_encode(mysqli_result($Ergebnis_IND,0,'INFO_VIEWER_ZEILE_4_EN'));
$INFO_VIEWER_ZEILE_5 = utf8_encode(mysqli_result($Ergebnis_IND,0,'INFO_VIEWER_ZEILE_5_EN'));
$INFO_VIEWER_ZEILE_6 = utf8_encode(mysqli_result($Ergebnis_IND,0,'INFO_VIEWER_ZEILE_6_EN'));
$BEDEUTUNG_INTERPRETATION =nl2br(utf8_encode(mysqli_result($Ergebnis_IND,0,'BEDEUTUNG_INTERPRETATION_EN')));
$DATENGRUNDLAGE_ATKIS = utf8_encode(mysqli_result($Ergebnis_IND,0,'DATENGRUNDLAGE_ATKIS'));
$DATENGRUNDLAGE_ZEILE_1 = utf8_encode(mysqli_result($Ergebnis_IND,0,'DATENGRUNDLAGE_ZEILE_1_EN'));
$DATENGRUNDLAGE_ZEILE_2 = utf8_encode(mysqli_result($Ergebnis_IND,0,'DATENGRUNDLAGE_ZEILE_2_EN'));
$METHODENBESCHREIBUNG = nl2br(utf8_encode(mysqli_result($Ergebnis_IND,0,'METHODIK_EN')));
$VERWEISE = nl2br(utf8_encode(mysqli_result($Ergebnis_IND,0,'VERWEISE_EN')));
$BERECHNUNG = nl2br(utf8_encode(mysqli_result($Ergebnis_IND,0,'BERECHNUNG_EN')));
$BEMERKUNGEN = nl2br(utf8_encode(mysqli_result($Ergebnis_IND,0,'BEMERKUNGEN_EN')));
$LITERATUR = nl2br(utf8_encode(mysqli_result($Ergebnis_IND,0,'LITERATUR_EN')));
$BRD = utf8_encode(mysqli_result($Ergebnis_IND,0,'RAUMEBENE_BRD'));
$BLD = utf8_encode(mysqli_result($Ergebnis_IND,0,'RAUMEBENE_BLD'));
$KRS = utf8_encode(mysqli_result($Ergebnis_IND,0,'RAUMEBENE_KRS'));
$GEM = utf8_encode(mysqli_result($Ergebnis_IND,0,'RAUMEBENE_GEM'));
$ROR = utf8_encode(mysqli_result($Ergebnis_IND,0,'RAUMEBENE_ROR'));
$G50 = utf8_encode(mysqli_result($Ergebnis_IND,0,'RAUMEBENE_G50'));
$STT = utf8_encode(mysqli_result($Ergebnis_IND,0,'RAUMEBENE_STT'));
$PLR = utf8_encode(mysqli_result($Ergebnis_IND,0,'RAUMEBENE_PLR'));
$VWG = utf8_encode(mysqli_result($Ergebnis_IND,0,'RAUMEBENE_VWG'));
$RST = utf8_encode(mysqli_result($Ergebnis_IND,0,'RAUMEBENE_RST'));
$R10 = utf8_encode(mysqli_result($Ergebnis_IND,0,'RAUMEBENE_R10'));
$R05 = utf8_encode(mysqli_result($Ergebnis_IND,0,'RAUMEBENE_R05'));
$R5M = utf8_encode(mysqli_result($Ergebnis_IND,0,'RAUMEBENE_R5M'));
$R2M = utf8_encode(mysqli_result($Ergebnis_IND,0,'RAUMEBENE_R2M'));
$R1M = utf8_encode(mysqli_result($Ergebnis_IND,0,'RAUMEBENE_R1M'));
$WMS = utf8_encode(mysqli_result($Ergebnis_IND,0,'WMS'));
$WCS = utf8_encode(mysqli_result($Ergebnis_IND,0,'WCS'));
$WFS = utf8_encode(mysqli_result($Ergebnis_IND,0,'WFS'));
$WMS_link = "http://maps.ioer.de/cgi-bin/wms?map=" . $IND . "_100&";
$WCS_link = "http://maps.ioer.de/cgi-bin/wcs?map=" . $IND . "_wcs";
$WFS_link = "http://maps.ioer.de/cgi-bin/wfs?map=" . $IND;
}

// Beenden, falls kein Indikator gewählt
if(!$IND)
{
	echo '<br /><br /><br /><strong>Es wurde kein Indikator zur Anzeige ausgewählt.</strong></body>';
	die;	
}



?>

<!--
<form action="http://db.intranet-ioer.de/monitor/index.php?id=8" method="post" target="_top">


<div class="nicht_im_print">
<input style=" width:180px;" name="s1" type="submit" class="button_gruen_abschicken " id="s1" value="< Zur&uuml;ck zur Karte" />
</div> -->

<img src="../gfx/kopf_v2_unterseiten.png" width="100%" alt="Kopfgrafik" class="nur_im_print" />                    
<div style="text-align:right; margin-right:30px;"><a onClick="javascript:window.print()" class="nicht_im_print" ><img src="../icons_viewer/printer.png" alt="Drucken" style="cursor: pointer;" />
    <br /><?php 
  // Lokalisierung
	if($_SESSION['Dokument']['Sprache'] == 'DE')
	{
	?>Drucken&nbsp;<?php 
	}
	if($_SESSION['Dokument']['Sprache'] == 'EN')
	{
	?>Print out<?php 
	}
  ?></a></div>
<div style="margin-left:0px; padding:10px; border:0px; background:#FFFFFF; width:95%;">
	


<!--Titelbereich-->


<div style="text-align:center;">
<?php 
			// Lokalisierung
			if($_SESSION['Dokument']['Sprache'] == 'DE')
			{
				?><strong>Indikatorkennblatt</strong><br />
				<?php 				
			}
			if($_SESSION['Dokument']['Sprache'] == 'EN')
			{
				?><strong>Indicator data sheet</strong><br />
				<?php 	
			}
			?>

  <br />
  <strong style="font-size:18px"><?php echo $IND_Name; ?></strong><br />
</div>
<!-- Ende Titelbereich-->


<br />
<br />


<strong><?php echo $Sprache_Ausgabe[$_SESSION['Dokument']['Sprache']]['Kategorie']; ?>:</strong><br />
<?php 
				 
$SQL_Kat = "SELECT * FROM m_thematische_kategorien ORDER BY SORTIERUNG_THEMA_KAT";
$Ergebnis_Kat = mysqli_query($Verbindung,$SQL_Kat);
$i_kat = 0;
while(@mysqli_result($Ergebnis_Kat,$i_kat,'ID_THEMA_KAT'))
{
	if(@mysqli_result($Ergebnis_IND,0,'ID_THEMA_KAT') == @mysqli_result($Ergebnis_Kat,$i_kat,'ID_THEMA_KAT')) 
	{
		if($_SESSION['Dokument']['Sprache'] == 'DE') echo utf8_encode(@mysqli_result($Ergebnis_Kat,$i_kat,'THEMA_KAT_NAME')); 
		if($_SESSION['Dokument']['Sprache'] == 'EN') echo utf8_encode(@mysqli_result($Ergebnis_Kat,$i_kat,'THEMA_KAT_NAME_EN')); 
	}
	$i_kat++; 
}
?>
<br />
<br />
<strong><?php echo $Sprache_Ausgabe[$_SESSION['Dokument']['Sprache']]['Einheit']; ?>:</strong><br />
<?php echo $Einheit; ?>
<br />
<br />
<strong><?php echo $Sprache_Ausgabe[$_SESSION['Dokument']['Sprache']]['Kurzbeschreibung']; ?>:</strong><br />
<?php echo $INFO_VIEWER_ZEILE_1." <br />". $INFO_VIEWER_ZEILE_2." <br />". $INFO_VIEWER_ZEILE_3." <br />". $INFO_VIEWER_ZEILE_4." <br />". $INFO_VIEWER_ZEILE_5." <br />". $INFO_VIEWER_ZEILE_6;  ?>
<br />
<br />
<strong><?php echo $Sprache_Ausgabe[$_SESSION['Dokument']['Sprache']]['Bedeutung']; ?>:</strong><br />
<?php echo $BEDEUTUNG_INTERPRETATION; ?><br />
<br />
<strong><?php echo $Sprache_Ausgabe[$_SESSION['Dokument']['Sprache']]['Datengrundlagen']; ?>:</strong><br />
<?php 
if($DATENGRUNDLAGE_ATKIS) echo "ATKIS Basis-DLM, BKG <br />";
if($DATENGRUNDLAGE_ZEILE_1) echo $DATENGRUNDLAGE_ZEILE_1."<br />";   
if($DATENGRUNDLAGE_ZEILE_2) echo $DATENGRUNDLAGE_ZEILE_2."<br />";   
?>
<br />
<strong><?php echo $Sprache_Ausgabe[$_SESSION['Dokument']['Sprache']]['Methodik']; ?>:</strong><br />
<?php echo $METHODENBESCHREIBUNG; ?><br />
<br />
<strong><?php echo $Sprache_Ausgabe[$_SESSION['Dokument']['Sprache']]['Verweise']; ?>:</strong><br />
<?php echo $VERWEISE; ?><br />
<br />
<strong><?php echo $Sprache_Ausgabe[$_SESSION['Dokument']['Sprache']]['Bemerkungen']; ?>:</strong><br />
<?php echo $BEMERKUNGEN; ?><br />
<br />
<strong><?php echo $Sprache_Ausgabe[$_SESSION['Dokument']['Sprache']]['Bezugsebenen']; ?>:</strong><br />
<?php 
if($_SESSION['Dokument']['Sprache'] == 'DE')
{
	?>
	<table width="100%" border="0" cellspacing="5" cellpadding="0" style="border:0px;">
                           <tr>
                            <td width="15" style="padding-left:3px;">
                                <?php if($BRD) echo "<strong>X</strong>"; ?>
                            </td>
                            <td  style="border:0px; padding-left:5px;">Bundesrepublik Deutschland</td>
                            <td width="15" style="padding-left:3px;">
                            	<?php if($G50) echo "<strong>X</strong>"; ?>
                            </td>
                            <td  style="border:0px; padding-left:5px;">Städte (&gt; 50 000 Ew.)</td>
                            <td width="15" style="padding-left:3px;">
                                <?php if($R10) echo "<strong>X</strong>"; ?>
                            </td>
                            <td  style="border:0px; padding-left:5px;"> Raster 10 km</td>
      </tr>
                          <tr>
                            <td width="15" style="padding-left:3px;">
                            	<?php if($BLD) echo "<strong>X</strong>"; ?>
                            </td>
                            <td style="border:0px; padding-left:5px;">Bundesl&auml;nder</td>
                            <td width="15" style="padding-left:3px;">
                            	<?php if($STT) echo "<strong>X</strong>"; ?>
                            </td>
                            <td style="border:0px; padding-left:5px;">Stadtteile</td>
                            <td width="15" style="padding-left:3px;">
                                <?php if($R05) echo "<strong>X</strong>"; ?>
                            </td>
                            <td  style="border:0px; padding-left:5px;">Raster 5 km</td>
      </tr>
                          <tr>
                            <td width="15" style="padding-left:3px;">
                            	<?php if($KRS) echo "<strong>X</strong>"; ?>
                            </td>
                            <td style="border:0px; padding-left:5px;">Kreise</td>
                          <td width="15" style="border:0px"></td>
                            <td style="border:0px; padding-left:5px;"> 
                           </td>
                            <td width="15" style="padding-left:3px;">
                                <?php if($RST) echo "<strong>X</strong>"; ?>
                            </td>
                            <td  style="border:0px; padding-left:5px;">Raster 1 km</td>
      </tr>
                          <tr>
                            <td width="15" style="padding-left:3px;">
                            	<?php if($GEM) echo "<strong>X</strong>"; ?>
                            </td>
                            <td style="border:0px; padding-left:5px;">Gemeinden</td>
                            
                                 <td width="15" style="padding-left:3px;">
                            	<?php if($VWG) echo "<strong>X</strong>"; ?>
                            </td>
                            <td style="border:0px; padding-left:5px;">Gemeindeverband</td>
                          
                            <td width="15" style="padding-left:3px;">
                                <?php if($R5M) echo "<strong>X</strong>"; ?>
                            </td>
                            <td  style="border:0px; padding-left:5px;">Raster 500 m</td>
      </tr>
            <tr>
                             <td width="15" style="border:0px"></td>
                            <td style="border:0px; padding-left:5px;"></td>
                            <td width="15" style="border:0px"></td>
                            <td style="border:0px; padding-left:5px;"></td>
                             <td width="15" style="padding-left:3px;">
                                <?php if($R2M) echo "<strong>X</strong>"; ?>
                            </td>                              
                            <td  style="border:0px; padding-left:5px;">Raster 200 m</td>
      </tr>
            <tr>
                            <td width="15" style="padding-left:3px;">
                                <?php if($ROR) echo "<strong>X</strong>"; ?>
                            </td>
                            <td  style="border:0px; padding-left:5px;">Raumordnungsregionen</td>
                            <td width="15" style="border:0px"></td>
                            <td style="border:0px; padding-left:5px;"></td>
                            
                            <td width="15" style="padding-left:3px;">
                                <?php if($R1M) echo "<strong>X</strong>"; ?>
                            </td>
                            <td  style="border:0px; padding-left:5px;">Raster 100 m</td>
      </tr>
            <tr>
                            <td width="15" style="padding-left:3px;">
                                <?php if($PLR) echo "<strong>X</strong>"; ?>
                            </td>
                            <td  style="border:0px; padding-left:5px;">Planungsregionen</td>
                            
                           
                            <td width="15" style="border:0px"></td>
                            <td style="border:0px; padding-left:5px;"></td>
                            <td width="15" style="border:0px"></td>
                            <td style="border:0px; padding-left:5px;"></td>
                            
                           
      </tr>
    </table>
	<?php    
} 
?>

<?php 
if($_SESSION['Dokument']['Sprache'] == 'EN')
{
	?>
	<table width="100%" border="0" cellspacing="5" cellpadding="0" style="border:0px;">
                           <tr>
                            <td width="15" style="padding-left:3px;">
                                <?php if($BRD) echo "<strong>X</strong>"; ?>
                            </td>
                            <td  style="border:0px; padding-left:5px;">Whole Germany</td>
                            <td width="15" style="padding-left:3px;">
                            	<?php if($G50) echo "<strong>X</strong>"; ?>
                            </td>
                            <td  style="border:0px; padding-left:5px;">Cities (&gt; 50 000 inh.)</td>
                            <td width="15" style="padding-left:3px;">
                                <?php if($R10) echo "<strong>X</strong>"; ?>
                            </td>
                            <td  style="border:0px; padding-left:5px;"> Grid 10 km</td>
      </tr>
                          <tr>
                            <td width="15" style="padding-left:3px;">
                            	<?php if($BLD) echo "<strong>X</strong>"; ?>
                            </td>
                            <td style="border:0px; padding-left:5px;">States</td>
                            <td width="15" style="padding-left:3px;">
                            	<?php if($STT) echo "<strong>X</strong>"; ?>
                            </td>
                            <td style="border:0px; padding-left:5px;">Quarters</td>
                            <td width="15" style="padding-left:3px;">
                                <?php if($R05) echo "<strong>X</strong>"; ?>
                            </td>
                            <td  style="border:0px; padding-left:5px;">Grid 5 km</td>
      </tr>
                          <tr>
                            <td width="15" style="padding-left:3px;">
                            	<?php if($KRS) echo "<strong>X</strong>"; ?>
                            </td>
                            <td style="border:0px; padding-left:5px;">Districts</td>
                          <td width="15" style="border:0px"></td>
                            <td style="border:0px; padding-left:5px;"> 
                           </td>
                            <td width="15" style="padding-left:3px;">
                                <?php if($RST) echo "<strong>X</strong>"; ?>
                            </td>
                            <td  style="border:0px; padding-left:5px;">Grid 1 km</td>
      </tr>
                          <tr>
                            <td width="15" style="padding-left:3px;">
                            	<?php if($GEM) echo "<strong>X</strong>"; ?>
                            </td>
                            <td style="border:0px; padding-left:5px;">Municipal level</td>
                           <td width="15" style="border:0px"></td>
                            <td style="border:0px; padding-left:5px;"></td>
                            <td width="15" style="padding-left:3px;">
                                <?php if($R5M) echo "<strong>X</strong>"; ?>
                            </td>
                            <td  style="border:0px; padding-left:5px;">Grid 500 m</td>
      </tr>
            <tr>
                             <td width="15" style="border:0px"></td>
                            <td style="border:0px; padding-left:5px;"></td>
                            <td width="15" style="border:0px"></td>
                            <td style="border:0px; padding-left:5px;"></td>
                             <td width="15" style="padding-left:3px;">
                                <?php if($R2M) echo "<strong>X</strong>"; ?>
                            </td>                              
                            <td  style="border:0px; padding-left:5px;">Grid 200 m</td>
      </tr>
            <tr>
                            <td width="15" style="padding-left:3px;">
                                <?php if($ROR) echo "<strong>X</strong>"; ?>
                            </td>
                            <td  style="border:0px; padding-left:5px;">Spatial planning regions</td>
                            <td width="15" style="border:0px"></td>
                            <td style="border:0px; padding-left:5px;"></td>
                            
                            <td width="15" style="padding-left:3px;">
                                <?php if($R1M) echo "<strong>X</strong>"; ?>
                            </td>
                            <td  style="border:0px; padding-left:5px;">Grid 100 m</td>
      </tr>
         <!--  wenn en bekannt: <tr>
            							  <td width="15" style="padding-left:3px;">
                                <?php if($PLR) echo "<strong>X</strong>"; ?>
                            </td>
                            <td  style="border:0px; padding-left:5px;">hier engl Name PLR eintragen</td>hier engl Name PLR eintragen
                            
                           
                            <td width="15" style="border:0px"></td>
                            <td style="border:0px; padding-left:5px;"></td>
                            <td width="15" style="border:0px"></td>
                            <td style="border:0px; padding-left:5px;"></td>       
      </tr>-->
    </table>
	<?php    
} 
?>


<br />
<strong><?php echo $Sprache_Ausgabe[$_SESSION['Dokument']['Sprache']]['Dienste']; ?>:</strong><br />


<table width="100%" border="0" cellspacing="7" cellpadding="0" style="border:0px;">
                           <tr>
                            <td width="15" style="padding-left:3px;">
                                <?php if($WMS) echo "<strong>X</strong>"; ?>
                            </td>
                            <td  style="border:0px; padding-left:5px;">
                             WMS: <?php if($WMS) echo $WMS_link;?>
                           </td>
                         
      </tr>
                          <tr>
                            <td width="15" style="padding-left:3px;">
                            	<?php if($WCS) echo "<strong>X</strong>"; ?>
                            </td>
                            <td style="border:0px; padding-left:5px;">
                        		  WCS:  <?php if($WCS) echo $WCS_link;?>
                            </td>
                         
      </tr>
                          <tr>
                            <td width="15" style="padding-left:3px;">
                            	<?php if($WFS) echo "<strong>X</strong>"; ?>
                            </td>
                            <td style="border:0px; padding-left:5px;">
                           WFS: <?php if($WFS) echo $WFS_link;?>
                            
                            </td>
                          
      </tr>
</table>

		

    <br />
    <strong><?php echo $Sprache_Ausgabe[$_SESSION['Dokument']['Sprache']]['Quellen']; ?>:</strong><br />
<?php 





echo $LITERATUR; 



?><br />
<br />
<br />
</div>
<br />
<!--<div class="nicht_im_print">
<input style=" width:180px;" name="s1" type="submit" class="nicht_im_print button_gruen_abschicken " id="s1" value="< Zur&uuml;ck zur Karte" />
</div> -->
<br />
<br />
<!--</form>-->
</body>
</html>
