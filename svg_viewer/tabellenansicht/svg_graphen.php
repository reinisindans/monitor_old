<?php session_start(); // Sitzung starten/ wieder aufnehmen

header("Content-Type: text/html; charset=utf-8");

// Memory-Limit erweitern
ini_set('memory_limit', '232M');
//ini_set('max_execution_time', '500');

include("../includes_classes/verbindung_mysqli.php");
include("../includes_classes/implode_explode.php");
include("../includes_classes/login.php");

// Userstatus
Login();

if($_GET['Admin_Anzeige']) $Admin_Anzeige = 1;


// Anzeigeart der Stützpunkte (alle oder nur mit bestimmter Entfernung zueinander)
// Bei neuem Diagrammstart
if($_POST['neu'])
{
    if ($_POST['Indikator_Tab'] == "S12EG" or $_POST['Indikator_Tab'] == "S12FG" or $_POST['Indikator_Tab'] == "S11EG" or $_POST['Indikator_Tab'] == "S11FG")
	{
        $_SESSION['Diagramm']['Alle_Stuetzpunkte_zeigen'] = 1;
    }
    else
    {    
        $_SESSION['Diagramm']['Alle_Stuetzpunkte_zeigen'] = 0;
    }
}    
// Schalter auswerten
if($_POST['Stuetzpunktform'])
{
	if($_POST['Alle_Stuetzpunkte_zeigen'] and $_POST['Indikator_Tab'] != "S12EG" and $_POST['Indikator_Tab'] != "S12FG" and $_POST['Indikator_Tab'] != "S11EG" and $_POST['Indikator_Tab'] != "S11FG")
	{
		$_SESSION['Diagramm']['Alle_Stuetzpunkte_zeigen'] = 0;
	}       
	else
	{
		$_SESSION['Diagramm']['Alle_Stuetzpunkte_zeigen'] = 1;
	}
}
// Datenübernahme für Gebietseinheit
// Test AGS
// $_SESSION['Diagramm']['AGS_Dokument'] = '14612000';
if($_GET['ags'])
{
	 $_SESSION['Diagramm']['AGS_Dokument'] = $_GET['ags'];
	 $_SESSION['Diagramm']['name'] = $_GET['name'];
	 $_SESSION['Diagramm']['Raumgliederung'] = $_GET['Raumgliederung'];
}

if($_POST['ags'])  // POST höherwertig als GET !
{
	$_SESSION['Diagramm']['AGS_Dokument'] = $_POST['ags'];
	$_SESSION['Diagramm']['name'] = $_POST['name'];
	switch ($_POST['Raumgliederung']) 
	{
		case 'gem':
			$_SESSION['Diagramm']['Raumgliederung'] = 'Gemeinde';
			break;
		case 'krs':
			$_SESSION['Diagramm']['Raumgliederung'] = 'Kreis';
			break;
		case 'kfs':
			$_SESSION['Diagramm']['Raumgliederung'] = 'Kreisfreie Stadt o.Ä.';
			break;
		case 'lks':
			$_SESSION['Diagramm']['Raumgliederung'] = 'Landkreis';
			break;
		case 'bld':
			$_SESSION['Diagramm']['Raumgliederung'] = 'Bundesland';
			break;
	}
	
	
}




// Dokument Maße
if(!$_SESSION['Diagramm']['width'] and !$_SESSION['Diagramm']['height'])
{
	$_SESSION['Diagramm']['width'] = 700;
	$_SESSION['Diagramm']['height'] = 300;
}
$Grössenaenderungsvariable = 100;
if($_GET['width_plus']) $_SESSION['Diagramm']['width'] = $_SESSION['Diagramm']['width'] + $Grössenaenderungsvariable;
if($_GET['width_minus'] and $_SESSION['Diagramm']['width'] >= '700') $_SESSION['Diagramm']['width'] = $_SESSION['Diagramm']['width'] - $Grössenaenderungsvariable;
if($_GET['height_plus']) $_SESSION['Diagramm']['height'] = $_SESSION['Diagramm']['height'] + $Grössenaenderungsvariable;
if($_GET['height_minus'] and $_SESSION['Diagramm']['height'] >= '300') $_SESSION['Diagramm']['height'] = $_SESSION['Diagramm']['height'] - $Grössenaenderungsvariable;

// Obergrenzen von User übernehmen
// Obergrenze für Hauptdiagramm (nur %)
if($_POST['Obergrenze_Diagr'])
{
	$_SESSION['Diagramm']['Obergrenze_Diagr'] = $_POST['Obergrenze_Diagr'];
	if($_SESSION['Diagramm']['Obergrenze_Diagr'] > 100) $_SESSION['Diagramm']['Obergrenze_Diagr'] = 100; 
}
// Obergrenze löschen
if($_POST['Obergrenze_Diagr_DEL'])
{
	$_SESSION['Diagramm']['Obergrenze_Diagr'] = '';
}

// Indikator aus Tabelle automatisch aktivieren und dabei Diagramm leeren
if($_POST['Indikator_Tab']) 
{
	$_SESSION['Diagramm']['Indikatoren_Graph'] = array(); // leeren
	$_SESSION['Diagramm']['Indikatoren_Graph'][$_POST['Indikator_Tab']] = $_POST['Indikator_Tab'];
	if($_POST['Indikator_Tab2']) $_SESSION['Diagramm']['Indikatoren_Graph'][$_POST['Indikator_Tab2']] = $_POST['Indikator_Tab2'];
}

// Indikator aus Auswahlmenü hinzufügen
if($_POST['Indikator_hinzu']) 
{
	$_SESSION['Diagramm']['Indikatoren_Graph'][$_POST['Indikator_hinzu']] = $_POST['Indikator_hinzu'];
	
}

// Einzelnen Indikator entfernen
if($_POST['IND_DEL']) 
{
	// Zelle leeren
	$_SESSION['Diagramm']['Indikatoren_Graph'][$_POST['IND_DEL']] = '';
	// Zelle entfernen (Array säubern)
	$temp = $_SESSION['Diagramm']['Indikatoren_Graph']; // Daten sichern
	$_SESSION['Diagramm']['Indikatoren_Graph'] = array(); // Array leeren
	// Zurückschreiben der Daten mit Inhalt
	foreach($temp as $ti)
	{
		if($ti) $_SESSION['Diagramm']['Indikatoren_Graph'][$ti] = $ti;
	}
	
}

// Angleichungsfaktor:
if($_POST['IND_FAKTOR_minus']) $_SESSION['Diagramm']['Indikatoren_Graph_Faktor'][$_POST['IND_FAKTOR_minus']] /= 10;
if($_POST['IND_FAKTOR_plus'])$_SESSION['Diagramm']['Indikatoren_Graph_Faktor'][$_POST['IND_FAKTOR_plus']] *= 10;

 
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Entwicklungsdiagramm - IÖR Monitor</title>

<link href="../screen_viewer.css" rel="stylesheet" type="text/css" />
</head>

<body style="padding-left:35px;" class="body_unterseiten">
<a style="border:0px;" href="http://www.ioer-monitor.de" target="_blank">
<img src="../gfx/kopf_v2_unterseiten.png" width="100%" alt="Kopfgrafik" class="nur_im_print"/>
<img src="../gfx/kopf_v2_unterseiten.png" width="999" height="119" alt="Kopfgrafik" class="nicht_im_print"/>
</a>

<!--Diagram option on/off Rubel-->

<script>
    function toggle5(showHideDiv, switchImgTag) {

            var ele = document.getElementById(showHideDiv);

            var imageEle = document.getElementById(switchImgTag);

            if(ele.style.display == "none") {

                    ele.style.display = "block";

    		imageEle.innerHTML = '<img src="minus1.png" title="Tabelle Steuerelemente aus">';

            }

            else {

                    ele.style.display = "none";

                    imageEle.innerHTML = '<img src="plus1.png" title="Tabelle Steuerelemente ein">';

            }

    }
</script>

<h2>Entwicklungsdiagramm</h2>

<div id="headerDivImg">
<a id="imageDivLink" href="javascript:toggle5('contentDivImg', 'imageDivLink');"><img src="plus1.png"></a>
</div>
<div id="contentDivImg" style="display: none;">
Dieses Diagramm stellt die Entwicklung der Indikatoren dar.<br />
<?php 
// Hinweis auf Berechnung der Trendwerte bei gültiger Trend Rgl & IND << im moment nur für Prüfer
if($_SESSION['Dokument']['ViewBerechtigung'] == "0"&& $_SESSION['Tabelle']['Trend_Raumgliederung'] == '1'&& $_SESSION['Tabelle']['Trend_Indikator'] == '1')
{	echo 'Die Berechnung der Trendwerte erfolgt auf Basis aller Indikatorwerte ab 2008.<br />';	}
?>

<br />

<!-- Indikatoren hinzufügen -->

 <?php 
		 

// Prüfer mit ID=0 darf alles sehen
if($_SESSION['Dokument']['ViewBerechtigung'] != "0")
{		
	$SQL_Indikatoren = "SELECT * FROM m_indikatoren,m_indikator_freigabe,m_thematische_kategorien  
						WHERE m_indikatoren.ID_THEMA_KAT = m_thematische_kategorien.ID_THEMA_KAT 
						AND m_indikatoren.ID_INDIKATOR = m_indikator_freigabe.ID_INDIKATOR 
						AND STATUS_INDIKATOR_FREIGABE >= '".$_SESSION['Dokument']['ViewBerechtigung']."'  
						ORDER BY SORTIERUNG_THEMA_KAT, INDIKATOR_NAME";
						// gelöscht: AND JAHR = '".$_SESSION['Dokument']['Jahr_Anzeige']."'
}
else
{
	// enthaltene Kategorien erfassen
	$SQL_Indikatoren = "SELECT * FROM m_indikatoren,m_indikator_freigabe,m_thematische_kategorien  
						WHERE m_indikatoren.ID_THEMA_KAT = m_thematische_kategorien.ID_THEMA_KAT 
						AND m_indikatoren.ID_INDIKATOR = m_indikator_freigabe.ID_INDIKATOR 
						ORDER BY SORTIERUNG_THEMA_KAT, INDIKATOR_NAME";
						// AND JAHR = '".$_SESSION['Dokument']['Jahr_Anzeige']."' 
}
			
$Ergebnis_Indikatoren = mysqli_query($Verbindung,$SQL_Indikatoren);

		 
?>
<div id="menue_ausw" class="nicht_im_print">
<a name="liste" id="liste"></a> 
Liste verfügbarer Indikatoren
<form action="svg_graphen.php#liste" method="post">
	<select  title="Auswahl verf&uuml;gbarer Indikatoren" name="Indikator_hinzu" id="Indikator_hinzu" style=" width:230px; border: solid 1px #666666; background-color:#FFFFFF; font-size:12px;"
                      onchange="submit();" 
                      onfocus="expandSELECT(this);" 
                      onblur="contractSELECT(this);" > 
       	       <option value="leer" >-- Indikator hinzufügen --</option>
 
       	       <?php 
							$i_ind=0;
							while($Ind_ID = @mysqli_result($Ergebnis_Indikatoren,$i_ind,'ID_INDIKATOR'))
							{
								$Ind_NAME = @mysqli_result($Ergebnis_Indikatoren,$i_ind,'INDIKATOR_NAME');
								$KAT_NAME = utf8_encode(@mysqli_result($Ergebnis_Indikatoren,$i_ind,'THEMA_KAT_NAME'));
								
								if($Ind_ID_vorher != $Ind_ID)
								{
									if($KAT_NAME_vorher != $KAT_NAME) { ?><option value="leer" ><?php echo "-------- ".$KAT_NAME." --------"; ?></option><?php }
									?>
      <option <?php 
										// farbliche Hinterlegung für freigegebene IND für Prüfer
										if($_SESSION['Dokument']['ViewBerechtigung'] == "0" and @mysqli_result($Ergebnis_Indikatoren,$i_ind,'STATUS_INDIKATOR_FREIGABE') == '3')
										{
											$Farbe_Liste = 'style="background:#CFC;"';
										}
										// farbliche Hinterlegung wenn IND ausgewählt ist
										if($_SESSION['Diagramm']['Indikatoren_Graph'][$Ind_ID])
										{
											$Farbe_Liste = 'style="background:#9dc8fb;"';
										}
										echo $Farbe_Liste;
										$Farbe_Liste = '';
	
									?> value="<?php echo $Ind_value = utf8_encode(@mysqli_result($Ergebnis_Indikatoren,$i_ind,'ID_INDIKATOR')) ?>" <?php 
									/* if($Ind_value == $_SESSION['Diagramm']['Tabelle']['Indikator_2'])
									{
										$Druck_Indikator_2 = "1"; // Nur Variable für Druck-Layout
										echo 'selected="selected"';
										$_SESSION['Diagramm']['Tabelle']['Indikator_2_Name'] = utf8_encode($Ind_NAME);
										$_SESSION['Diagramm']['Tabelle']['Indikator_2_Einheit'] = utf8_encode(@mysqli_result($Ergebnis_Indikatoren,$i_ind,'EINHEIT'));
									}  */?> > <?php 
									// ID für Prüfer anzeigen
									if($_SESSION['Dokument']['ViewBerechtigung'] == "0") echo "(".$Ind_value.") ";
									echo utf8_encode($Ind_NAME); ?></option>
				   				
									<?php 
								}
								$KAT_NAME_vorher = $KAT_NAME;
								$Ind_ID_vorher = $Ind_ID;
								$i_ind++;
							}
							?>
   	 </select>
 <!--    <input name="sendind" type="submit" value="Aktualisieren" />-->
</form>
<br />

Für die Ansicht aktivierte Indikatoren<br />

<?php 
foreach($_SESSION['Diagramm']['Indikatoren_Graph'] as $ID_IND_akt)
{
	// Indikatorname für Ausgabe erfassen
	$SQL_IName = "SELECT INDIKATOR_NAME,EINHEIT FROM m_indikatoren WHERE ID_INDIKATOR = '".$ID_IND_akt."'";
	$Ergebnis_IName = mysqli_query($Verbindung,$SQL_IName);
	?>
	<form style=" font-size:12px; display:inline;" action="svg_graphen.php#liste" method="post">
	<input class="button_standard_abschicken_a" style="width:15px; height:15px; text-align:center; font-size:8px;" name="DEL" type="submit" value="X" title="Indikator entfernen" />
	<?php echo utf8_encode(@mysqli_result($Ergebnis_IName,0,'INDIKATOR_NAME'))." (".utf8_encode(@mysqli_result($Ergebnis_IName,0,'EINHEIT')).") "; ?>
    <input name="IND_DEL" type="hidden" value="<?php echo $ID_IND_akt; ?>" />
    </form>
	&nbsp;&nbsp;
    <?php 
	if(@mysqli_result($Ergebnis_IName,0,'EINHEIT') != '%')
	{
		$Nicht_Anteils_Ind_vorh = 1;
		?>
        ( Normalisierungsfaktor: 
		<form style=" font-size:12px; display:inline;" action="svg_graphen.php" method="post">
		<input class="button_standard_abschicken_a" style="background:#EEEEEE; width:15px; height:15px; text-align:center; font-size:8px;" name="DEL" type="submit" value="-" title="Indikator entfernen" />
		<input name="IND_FAKTOR_minus" type="hidden" value="<?php echo $ID_IND_akt; ?>" />
		</form>
		<?php 
		// Logik zum vermeinden von '0'
		if($_SESSION['Diagramm']['Indikatoren_Graph_Faktor'][$ID_IND_akt] < 1) $_SESSION['Diagramm']['Indikatoren_Graph_Faktor'][$ID_IND_akt] = 1;
		echo $_SESSION['Diagramm']['Indikatoren_Graph_Faktor'][$ID_IND_akt]; ?>
		<form style=" font-size:12px; display:inline;" action="svg_graphen.php" method="post">
		<input class="button_standard_abschicken_a" style="background:#EEEEEE; width:15px; height:15px; text-align:center; font-size:8px;" name="DEL" type="submit" value="+" title="Indikator entfernen" />
		<input name="IND_FAKTOR_plus" type="hidden" value="<?php echo $ID_IND_akt; ?>" />
		</form> )
		<?php 
	} /* */
	?>
    <br />

	<?php 
}
?>
<!-- Rubel--><br />
<div id="r9" <?php if($ID_IND_akt == "S12EG" or $ID_IND_akt == "S12FG" or $ID_IND_akt == "S11EG" or $ID_IND_akt == "S11FG") echo 'style="display: none;"';?> >
Darstellung anpassen<br />
<form action="svg_graphen.php#liste" method="post">
<!-- <input name="Alle_Stuetzpunkte_zeigen" type="checkbox" value="1" onchange="submit();" <?php if(!$_SESSION['Diagramm']['Alle_Stuetzpunkte_zeigen']){  ?> checked="checked" <?php } ?> /> 
Zweifelhafte Stützpunkte filtern<br /> -->
<input name="Stuetzpunktform" type="hidden" value="1" />
<?php 
if(!$_SESSION['Diagramm']['Alle_Stuetzpunkte_zeigen'] and $_POST['Indikator_Tab'] != "S12EG" and $_POST['Indikator_Tab'] != "S12FG" and $_POST['Indikator_Tab'] != "S11EG" and $_POST['Indikator_Tab'] != "S11FG")
{
	?>
	<input name="Alle_Stuetzpunkte_zeigen" type="hidden" value="0" />
	<input name="sendind" type="submit" value="Alle Stützpunkte anzeigen" />
	<?php 
}
else
{
	?>
	<input name="Alle_Stuetzpunkte_zeigen" type="hidden" value="1" />
	<input name="sendind" type="submit" value="Diagramm glätten" />
	<?php 
}
?>
</form>
</div>
<?php 
// Knopf für Prüfer
if($_SESSION['Dokument']['ViewBerechtigung'] == "0")
{	
	?>
	<a href="svg_graphen_admin.php" target="_top"><input type="button" value="Doppelfenster öffnen" /></input></a>
	<?php 
}
?>
<br />

</div>
<span style="font-size:10px">Hinweise: </span>
<ul style="margin:0px; padding-left:20px;">
<li style="font-size:10px;">
Die y-Achse weist die Entwicklung des Indikators in Prozentpunkten aus.<br />
</li>
<li class="nicht_im_print" style="font-size:10px">Für mehr Informationen können Sie mit dem Mauszeiger über die Stützpunkte im Diagramm fahren und bekommen so die genauen 
Werte und Veränderungen angezeigt.
</li>
<?php 
if(!$_SESSION['Diagramm']['Alle_Stuetzpunkte_zeigen']  and $_POST['Indikator_Tab'] != "S12EG" and $_POST['Indikator_Tab'] != "S12FG" and $_POST['Indikator_Tab'] != "S11EG" and $_POST['Indikator_Tab'] != "S11FG")
{
	?><li class="nicht_im_print" style="font-size:10px">Das Diagramm wird geglättet dargestellt und enthält u.U. nicht alle verfügbaren Stützpunkte.</li><?php 
}
else if ($_POST['Indikator_Tab'] == "S12EG" or $_POST['Indikator_Tab'] == "S12FG" or $_POST['Indikator_Tab'] == "S11EG" or $_POST['Indikator_Tab'] == "S11FG")
{
    $_SESSION['Diagramm']['Alle_Stuetzpunkte_zeigen'] = 1;
}
// Ergänzende Zeile bei $Nicht_Anteils_Ind_vorh
if($Nicht_Anteils_Ind_vorh)
{
	?>
    <li style="font-size:10px">
    Achtung: Das Diagramm ist auf die Anzeige von Indikatoren optimiert, die prozentuale Anteile von Gebietsflächen beinhalten.<br />
	Indikatoren, mit anderen Einheiten als "%", werden auf den Maßstab der Anteilsindikatoren normiert, um sie vergleichbar anzeigen zu können.<br />
	Die Skalenteilung der y-Achse hat jedoch für diese Indikatoren keine Aussagekraft.
    </li>
	<?php 	
}
?>
</ul>
<br />
<br />
</div>

Entwicklungsdiagramm für Gebietseinheit: <?php echo '<strong>'.$_SESSION['Diagramm']['name'].'</strong> ';
if($_SESSION['Diagramm']['AGS_Dokument'] != '99') 
{
	echo '(AGS: '.$_SESSION['Diagramm']['AGS_Dokument'].') ';
	echo '<br />Typ: '.$_SESSION['Diagramm']['Raumgliederung'].'<br />';
}
?>
<br />
<!--
<br />
<form action="svg_graphen.php" method="post">
Obergrenze für das Übersichtsdiagramm:<br />
<input name="Obergrenze_Diagr" type="text" size="4" maxlength="3" value="<?php echo $_SESSION['Diagramm']['Obergrenze_Diagr']; ?>" />
<input name="Obergr_Diag_send" type="submit" value="Festlegen" />
</form>
<form action="" method="post">
	<input name="Obergrenze_Diagr_DEL" type="hidden" value="1"/>
	<input name="Obergr_Diag_DEL_send" type="submit" value="Entfernen" />
</form> -->
<br />
<!-- Keine Daten für Anzeige gewählt -->
<?php 
// Check auf leeres Diagramm, dann hier enden
foreach($_SESSION['Diagramm']['Indikatoren_Graph'] as $data_ok)
{
	$Daten_vorhanden = 1;
	
}

if(!$Daten_vorhanden) 
{
	echo 'Keine Daten gewählt.</body></html>';
	die;
}
?>

<div style="float:left;">
  <?php 
// Daten erfassen
// TEMPORARY
$Ergebnis_drop_table = mysqli_query($Verbindung,"DROP TABLE `t_temp_indikatoren_diagramm`");
$SQL_temp_table = "CREATE TABLE `t_temp_indikatoren_diagramm` (
  `ID`  int(20) NOT NULL AUTO_INCREMENT,
  `ID_INDIKATOR` varchar(10) NOT NULL,
  `IND_NAME` varchar(255) default NULL,
  `KATEGORIE` varchar(100) default NULL,
  `EINHEIT` varchar(100) default NULL,
  `WERT` double default NULL,
  `WERT_VERAENDERUNG` double default NULL,
  `WERT_VERAENDERUNG_REAL` double default NULL,
  `WERT_VERAENDERUNG_RELATIV` double default NULL,
  `FEHLERCODE` int(2)  NULL DEFAULT 0,
  `AKT_IGNORE` int(1) default NULL,
  `AKT_Monat` int(4) default NULL,
  `AKT_Jahr` int(4) default NULL,
  `JAHR` int(4) default NULL,
  `RUNDUNG_NACHKOMMASTELLEN` int(2) default NULL,
  `FARBWERT_MAX` varchar(6) default NULL,
  `SORTIERUNG_THEMA_KAT` int(20) default NULL,
  `SORTIERUNG` int(20) default NULL,
   PRIMARY KEY  (`ID`)
) ENGINE=HEAP DEFAULT CHARSET=utf8;"; 
// ) ENGINE=HEAP DEFAULT CHARSET=utf8;"; 
$Ergebnis_Indikatoren = mysqli_query($Verbindung,$SQL_temp_table);


// Indikatoren für Anzeige ermitteln


// Check auf freigegebene Indikatoren für User
$SQL_Indikatoren = "SELECT * FROM m_indikatoren,m_indikator_freigabe,m_thematische_kategorien 
						WHERE m_indikatoren.ID_INDIKATOR = m_indikator_freigabe.ID_INDIKATOR 
						AND STATUS_INDIKATOR_FREIGABE >= '".$_SESSION['Dokument']['ViewBerechtigung']."' 
						AND m_indikatoren.ID_THEMA_KAT = m_thematische_kategorien.ID_THEMA_KAT 
						ORDER BY SORTIERUNG_THEMA_KAT,SORTIERUNG";
$Ergebnis_Indikatoren = mysqli_query($Verbindung,$SQL_Indikatoren);



// Vorhandene, freigegebene, ausgewählte Indikatoren eintragen
$i_ind=0;
while($Ind_such = @mysqli_result($Ergebnis_Indikatoren,$i_ind,'ID_INDIKATOR'))
{ 		
	// Eingrenzung aus Auswahl-Array (nur einfügen, wenn im Array abgelegt)
	if($_SESSION['Diagramm']['Indikatoren_Graph'][@mysqli_result($Ergebnis_Indikatoren,$i_ind,'ID_INDIKATOR')])
	{

		// Zeichenvorschrift einbeziehen
		$SQL_ZV = "SELECT * FROM m_zeichenvorschrift WHERE ID_INDIKATOR = '".@mysqli_result($Ergebnis_Indikatoren,$i_ind,'ID_INDIKATOR')."'";
		$Ergebnis_ZV = mysqli_query($Verbindung,$SQL_ZV);
		// Wenn kein Farbwert übergeben, dann generieren:
		if(!$t_FW = @mysqli_result($Ergebnis_ZV,0,'FARBWERT_MAX'))
		{
			$t_FW = '888888';
		}
		// Daten in DS schreiben
		$SQL_INS_IND = "INSERT INTO t_temp_indikatoren_diagramm 
						(ID_INDIKATOR,JAHR,AKT_IGNORE,EINHEIT,KATEGORIE,SORTIERUNG_THEMA_KAT,SORTIERUNG,IND_NAME,FARBWERT_MAX,RUNDUNG_NACHKOMMASTELLEN) 
						VALUES 
							('".@mysqli_result($Ergebnis_Indikatoren,$i_ind,'ID_INDIKATOR')
							."','".@mysqli_result($Ergebnis_Indikatoren,$i_ind,'JAHR')
							."','".@mysqli_result($Ergebnis_Indikatoren,$i_ind,'MITTLERE_AKTUALITAET_IGNORE')
							."','".@mysqli_result($Ergebnis_Indikatoren,$i_ind,'EINHEIT')
							."','".@mysqli_result($Ergebnis_Indikatoren,$i_ind,'THEMA_KAT_NAME')
							."','".@mysqli_result($Ergebnis_Indikatoren,$i_ind,'SORTIERUNG_THEMA_KAT')
							."','".@mysqli_result($Ergebnis_Indikatoren,$i_ind,'SORTIERUNG')
							."','".@mysqli_result($Ergebnis_Indikatoren,$i_ind,'INDIKATOR_NAME')
							."','".$t_FW
							."','".@mysqli_result($Ergebnis_Indikatoren,$i_ind,'RUNDUNG_NACHKOMMASTELLEN')."');";
							
		if(!$Ergebnis_INS_IND = mysqli_query($Verbindung,$SQL_INS_IND)) echo "<!-- Fehler! ".$SQL_INS_IND." -->";
		
		// Jahr Min/Max gleich mit abfangen
		if($Jahr_min > @mysqli_result($Ergebnis_Indikatoren,$i_ind,'JAHR') or !$Jahr_min) $Jahr_min = @mysqli_result($Ergebnis_Indikatoren,$i_ind,'JAHR');
		if($Jahr_max < @mysqli_result($Ergebnis_Indikatoren,$i_ind,'JAHR') or !$Jahr_max) $Jahr_max = @mysqli_result($Ergebnis_Indikatoren,$i_ind,'JAHR');
	}

	
	
	$i_ind++;

}

// Füllen der Tabelle mit Werten pro verzeichnetem Datensatz
// -------------------------------------

$SQL_DS_vorh = "SELECT * FROM t_temp_indikatoren_diagramm";
$Ergebnis_DS_vorh = mysqli_query($Verbindung,$SQL_DS_vorh); 

$i_ds = 0;
$werte_min_all = 100;
$werte_max_all = 0;
while($ID = @mysqli_result($Ergebnis_DS_vorh,$i_ds,'ID'))
{
	
	
	// Indikatorwert und Fehlercode aus der Tabelle des entspr. Zusatzzeitschnittes ermitteln und Min/Max des Wertespektrums ermitteln
	$SQL_Indikatorenwerte = "SELECT INDIKATORWERT,FEHLERCODE,HINWEISCODE,AGS 
	FROM m_indikatorwerte_".@mysqli_result($Ergebnis_DS_vorh,$i_ds,'JAHR')." 
	WHERE AGS = '".$_SESSION['Diagramm']['AGS_Dokument']."' 
	AND ID_INDIKATOR = '".@mysqli_result($Ergebnis_DS_vorh,$i_ds,'ID_INDIKATOR')."'
	AND (VGL_AB = '0' OR VGL_AB <= '".$Jahr_max."') ORDER BY VGL_AB DESC;"; // Bester vergleichbarer Datensatz kommt an Stelle "0" und wird als EInziger verwendet
	$Ergebnis_Indikatorenwerte = mysqli_query($Verbindung,$SQL_Indikatorenwerte); 
	
	
	// Werte einfügen und Max Min ermitteln
	if(@mysqli_result($Ergebnis_Indikatorenwerte,0,'AGS'))
	{

		$SQL_UPD = "UPDATE t_temp_indikatoren_diagramm
		SET WERT = '".@mysqli_result($Ergebnis_Indikatorenwerte,0,'INDIKATORWERT')."', 
		FEHLERCODE = '".@mysqli_result($Ergebnis_Indikatorenwerte,0,'FEHLERCODE')."' 
		WHERE ID = '".$ID."';";
		$Ergebnis_UPD = mysqli_query($Verbindung,$SQL_UPD); 
		
		//Min/Max des Wertespektrums ermitteln
		// echo "<br />".$werte_min = @mysqli_result($Ergebnis_Indikatorenwerte,0,'INDIKATORWERT');
		
		// Min derzeit nicht in Verwendung
		if($werte_min_all > @mysqli_result($Ergebnis_Indikatorenwerte,0,'INDIKATORWERT')) $werte_min_all = @mysqli_result($Ergebnis_Indikatorenwerte,0,'INDIKATORWERT');
		
		// Max festsetzen wenn vom User eingegeben
		if($_SESSION['Diagramm']['Obergrenze_Diagr'])
		{
			$werte_max_all = $_SESSION['Diagramm']['Obergrenze_Diagr'];
		}
		else
		{
			if($werte_max_all < @mysqli_result($Ergebnis_Indikatorenwerte,0,'INDIKATORWERT')) $werte_max_all = @mysqli_result($Ergebnis_Indikatorenwerte,0,'INDIKATORWERT');
		}
	}

	
	// Aktualität einfügen, MITTLERE_AKTUALITAET_IGNORE bzw. AKTUALITAET_TOP berücksichtigen
	// -------
	
	// Grundaktualität verwenden ja/nein
	if(@mysqli_result($Ergebnis_DS_vorh,$i_ds,'AKT_IGNORE'))
	{
			
	// Tabellenjahr als Grundakt.
		$SQL_UPD = "UPDATE t_temp_indikatoren_diagramm
			SET AKT_Jahr = '".@mysqli_result($Ergebnis_DS_vorh,$i_ds,'JAHR')."', 
			AKT_Monat = '12' 
			WHERE ID = '".$ID."';";
		$Ergebnis_UPD = mysqli_query($Verbindung,$SQL_UPD); 
		
		// Jahr Min/Max gleich mit abfangen und genauer setzen
		if($Jahr_min > @mysqli_result($Ergebnis_DS_vorh,$i_ds,'JAHR') or !$Jahr_min) $Jahr_min = @mysqli_result($Ergebnis_DS_vorh,$i_ds,'JAHR');
		if($Jahr_max < @mysqli_result($Ergebnis_DS_vorh,$i_ds,'JAHR') or !$Jahr_max) $Jahr_max = @mysqli_result($Ergebnis_DS_vorh,$i_ds,'JAHR');
		
	}
	else
	{
	// Normale Grundakt.

		// Jahr
		$SQL_Indikatorenwerte = "SELECT INDIKATORWERT,FEHLERCODE,HINWEISCODE,AGS 
		FROM m_indikatorwerte_".@mysqli_result($Ergebnis_DS_vorh,$i_ds,'JAHR')." 
		WHERE AGS = '".$_SESSION['Diagramm']['AGS_Dokument']."' 
		AND ID_INDIKATOR = 'Z00AG'
		AND (VGL_AB IS NULL or VGL_AB = '0' OR VGL_AB <= '".$Jahr_max."') ORDER BY VGL_AB DESC;"; // Bester vergleichbarer Datensatz kommt an Stelle "0" und wird als EInziger verwendet
		$Ergebnis_Indikatorenwerte = mysqli_query($Verbindung,$SQL_Indikatorenwerte); 
		
			// Jahr einfügen
	$Trend_abfrage = @mysqli_result($Ergebnis_DS_vorh,$i_ds,'JAHR');
	if($Trend_abfrage >'2024')
    {
      
	$SQL_UPD = "UPDATE t_temp_indikatoren_diagramm
			SET AKT_Jahr = '".@mysqli_result($Ergebnis_DS_vorh,$i_ds,'JAHR')."'  
			WHERE ID = '".$ID."';";
			$Ergebnis_UPD = mysqli_query($Verbindung,$SQL_UPD); 
			
//Variable setzen, dass Punkt ein Trendwert ist (für später eigene Farbe/Darstellung)
	$Trend_Punkt = '1';

		}
	else	
	{
		if(@mysqli_result($Ergebnis_Indikatorenwerte,0,'AGS'))
		{
			
			$SQL_UPD = "UPDATE t_temp_indikatoren_diagramm
			SET AKT_Jahr = '".@mysqli_result($Ergebnis_Indikatorenwerte,0,'INDIKATORWERT')."'  
			WHERE ID = '".$ID."';";
			$Ergebnis_UPD = mysqli_query($Verbindung,$SQL_UPD); 
			
			
			//es ist kein Trendwert
				$Trend_Punkt = '0';
			
			// Jahr Min/Max gleich mit abfangen und genauer setzen
			if($Jahr_min > @mysqli_result($Ergebnis_Indikatorenwerte,0,'INDIKATORWERT') or !$Jahr_min) $Jahr_min = @mysqli_result($Ergebnis_Indikatorenwerte,0,'INDIKATORWERT');
			if($Jahr_max < @mysqli_result($Ergebnis_Indikatorenwerte,0,'INDIKATORWERT') or !$Jahr_max) $Jahr_max = @mysqli_result($Ergebnis_Indikatorenwerte,0,'INDIKATORWERT');
		
		}
	}
		// Monat
		// Indikatorwert und Fehlercode aud der Tabelle des entspr. Zusatzzeitschnittes ermitteln
		$SQL_Indikatorenwerte = "SELECT INDIKATORWERT,FEHLERCODE,HINWEISCODE,AGS 
		FROM m_indikatorwerte_".@mysqli_result($Ergebnis_DS_vorh,$i_ds,'JAHR')." 
		WHERE AGS = '".$_SESSION['Diagramm']['AGS_Dokument']."' 
		AND ID_INDIKATOR = 'Z01AG'
		AND (VGL_AB IS NULL or VGL_AB = '0' OR VGL_AB <= '".$Jahr_max."') ORDER BY VGL_AB DESC;"; // Bester vergleichbarer Datensatz kommt an Stelle "0" und wird als EInziger verwendet
		$Ergebnis_Indikatorenwerte = mysqli_query($Verbindung,$SQL_Indikatorenwerte); 
		
		// Monat einfügen
		
	
	if($Trend_abfrage >'2024')
    {
      
		$SQL_UPD = "UPDATE t_temp_indikatoren_diagramm
			SET AKT_Monat = '12'  
			WHERE ID = '".$ID."';";
			$Ergebnis_UPD = mysqli_query($Verbindung,$SQL_UPD); 
		}
		
		
	else{
		if(@mysqli_result($Ergebnis_Indikatorenwerte,0,'AGS'))
		{
		
		
			$SQL_UPD = "UPDATE t_temp_indikatoren_diagramm
			SET AKT_Monat = '".@mysqli_result($Ergebnis_Indikatorenwerte,0,'INDIKATORWERT')."'  
			WHERE ID = '".$ID."';";
			$Ergebnis_UPD = mysqli_query($Verbindung,$SQL_UPD); 
		}	
	}
	
	}
	
	$i_ds++;
}

// $Jahr_max ein Jahr hoch setzen, für bessere Darstellung im Diagramm
$Jahr_max++;



// Leere Datensätze Wert = NULL entfernen
$SQL_DEL = "DELETE FROM t_temp_indikatoren_diagramm WHERE WERT IS NULL;";
$Ergebnis_DEL = mysqli_query($Verbindung,$SQL_DEL); 


// Spalte für Veränderungswert füllen
$SQL_DS_vorh = "SELECT * FROM t_temp_indikatoren_diagramm ORDER BY SORTIERUNG_THEMA_KAT,SORTIERUNG,ID_INDIKATOR,Jahr;"; 
//$SQL_DS_vorh = "SELECT * FROM t_temp_indikatoren_diagramm WHERE EINHEIT = '%' ".$WHERE." ORDER BY SORTIERUNG_THEMA_KAT,SORTIERUNG,ID_INDIKATOR,Jahr;"; 
$Ergebnis_DS_vorh = mysqli_query($Verbindung,$SQL_DS_vorh); 
$i_ds = 0;
// Ermitteln der Veränderungen pro Indikator für Veränderungsdiagramm-Anzeige
while($ID = @mysqli_result($Ergebnis_DS_vorh,$i_ds,'ID'))
{
	$WERT = @mysqli_result($Ergebnis_DS_vorh,$i_ds,'WERT');	
	$FEHLERCODE = @mysqli_result($Ergebnis_DS_vorh,$i_ds,'FEHLERCODE');
	$ID_INDIKATOR = @mysqli_result($Ergebnis_DS_vorh,$i_ds,'ID_INDIKATOR');
	$EINHEIT =@mysqli_result($Ergebnis_DS_vorh,$i_ds,'EINHEIT');
	
	// Prüfen auf Indikator Wechsel aus der Abfrage, dann Vorwert Gleichsetzen, sodass dies der Nullpunkt ist
	if($ID_INDIKATOR_aktiv != $ID_INDIKATOR)
	{
		
		$Vorwert_0 = $WERT;	
		// Indikatoren mit Einheit != % werden hier auf 50% normalisiert
		if($EINHEIT != '%') 
		{
			$Normalisierungsfaktor = $WERT/$_SESSION['Diagramm']['Indikatoren_Graph_Faktor'][$ID_INDIKATOR];
			$Absolutindikator_vorhanden = 1;
		}
	}

	
	// Veränderungswert in Tabell übertragen
	if(!$FEHLERCODE)
	{
				
		$Veränderungswert_UPD = $WERT - $Vorwert_0;
		$Veränderungswert_UPD_REAL = $WERT - $Vorwert_0;
		// Indikatoren mit Einheit != % werden hier auf 50% normalisiert
		if($EINHEIT != '%') 
		{
			if ($Vorwert_0 >= 0) {$Veränderungswert_UPD = ($WERT - $Vorwert_0)/$Normalisierungsfaktor;}
            else {$Veränderungswert_UPD = -($WERT - $Vorwert_0)/$Normalisierungsfaktor;}
		}
		// Veränd.-Werte Schreiben
		$SQL_DS_Veraend = "UPDATE t_temp_indikatoren_diagramm SET 
		WERT_VERAENDERUNG = ".$Veränderungswert_UPD.",
		WERT_VERAENDERUNG_REAL = ".$Veränderungswert_UPD_REAL." 
		WHERE ID = '".$ID."';";
		$Ergebnis_DS_Veraend = mysqli_query($Verbindung,$SQL_DS_Veraend); 
	}

	
	$ID_INDIKATOR_aktiv = $ID_INDIKATOR;
	$Vorwert = $WERT;
	$i_ds++;
}





//}


// ------ Schleife ------ END





	// Variablen zur Platzierung
	
	
	
	/* // Höhe erweitern, um jeden Indikator der kommt (Zeilen für Legende)*/
	$SQL_DS_vorh = "SELECT COUNT(DISTINCT ID_INDIKATOR) AS Ind_anz FROM t_temp_indikatoren_diagramm;"; 
	$Ergebnis_DS_vorh = mysqli_query($Verbindung,$SQL_DS_vorh); 
	$Ind_anz = @mysqli_result($Ergebnis_DS_vorh,0,'Ind_anz'); 
	
	// Dokument
	$Leg_Kopf = 60;
	$Zeilenhoehe = 19;

	$width = $_SESSION['Diagramm']['width'];
	$height = $_SESSION['Diagramm']['height'] + ($Zeilenhoehe * $Ind_anz);

	// Ränder
	$xrand = 70;
	$yrand = 50 + ($Zeilenhoehe * $Ind_anz);
	$xrabdrechts = 30;
	$yrabdoben = 20;
	
	// Diagramm
	$xDiagramm = $width-$xrand-$xrabdrechts;
	$yDiagramm = $height-$yrand-$yrabdoben;	
	
	// berechnen der Schrittweite für die Achsen
	// für test: $Jahre = 10; 
	$Jahre = $Jahr_max - $Jahr_min;
	$xEinheit = $xDiagramm/$Jahre;
	
	$SQL_DS_M = "SELECT MIN(WERT) as MIN_WERT, RUNDUNG_NACHKOMMASTELLEN, EINHEIT, MAX(WERT_VERAENDERUNG) AS MAX_V, MIN(WERT_VERAENDERUNG) AS MIN_V, COUNT(DISTINCT ID_INDIKATOR) AS Ind_anz FROM t_temp_indikatoren_diagramm;"; 
	$Ergebnis_DS_M = mysqli_query($Verbindung,$SQL_DS_M);
	

	 
	// Echdaten erfassen
	$Einzelindikator_MIN_WERT = @mysqli_result($Ergebnis_DS_M,0,'MIN_WERT'); 
	$Einzelindikator_Rundung = @mysqli_result($Ergebnis_DS_M,0,'RUNDUNG_NACHKOMMASTELLEN');
	$Einzelindikator_Einheit_ind = utf8_encode(@mysqli_result($Ergebnis_DS_M,0,'EINHEIT'));
	// Maximalausprägung der Werte-Veränderung bei %
	$MAX_V = @mysqli_result($Ergebnis_DS_M,0,'MAX_V'); 
	$MIN_V = @mysqli_result($Ergebnis_DS_M,0,'MIN_V'); 
	$Wertespektrum_V = abs($MIN_V);
	if($Wertespektrum_V < abs($MAX_V)) $Wertespektrum_V = abs($MAX_V);
	//echo "<br /> ----------------- M ".$Wertespektrum_V."<br />";

	
	// $Wertespektrum = 20; // erst einmal immer 100% als Annahme
	// $Wertespektrum = (ceil(($werte_max_all)/10))*10;
	// $Wertespektrum = (ceil(($Wertespektrum_V)/10))*10;
	$Wertespektrum = 2*ceil($Wertespektrum_V);
	
	$yEinheit = $yDiagramm/$Wertespektrum;
	// Skalenteilung nach Werteausprägung anpassen (als Vielfaches von)
	$yTeilung = 10;
	if($Wertespektrum <= 50) $yTeilung = 5;
	if($Wertespektrum <= 20) $yTeilung = 1;
	if($Wertespektrum <= 5) $yTeilung = 0.5;
	//if($Wertespektrum <= 1) $yTeilung = 0.2;
	 // Teilung erst einmal bei 10 festgesetzt
	// $yTeilung = $Wertespektrum/10;
	// echo "........<br />.........".$yTeilung."........<br />.........";
	
	
	
	// ------------------------------------------------------------------ SVG ---------------------------------------------------------------
	
	
	// Dokument für Dateiausgabe manipulieren	
  	if($_POST['Dateiausgabe']) { $Datei_height_plus = 50; }else{ $Datei_height_plus = 0; }

  	$SVG = '<svg xmlns="http://www.w3.org/2000/svg"
     xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:ev="http://www.w3.org/2001/xml-events"
     version="1.1" baseProfile="full"
     width="'.$dokx=(2*$xrand)+$width.'px" height="'.$doky=(2*$yrand)+$height+$Datei_height_plus.'px" 
	 id="svg" 
	 >
	 <style type="text/css" >
      <![CDATA[
		
       @media print {
			.nicht_im_print {
			display: none;
			height: 0px;
			width: 0px;
			}
			
		}

      ]]>
    </style>
	<defs>
		<symbol id="schalter">
		  <desc>Schalter für Größenänderung</desc>
		  <rect  x="0" y="0" width="20" height="20" style="fill:#DDD; stroke:none;" />
		  <path d="M 5 7 l 5 5 l 5 -5" stroke="black" fill="none" stroke-width="1" />		  
		</symbol>
	</defs>
	<rect id="Hintergrund_weiss" x="0" y="0" width ="100%" height ="100%" fill="white"  ></rect>
	
	'; 
	
	
	/*  Mauszeiger Erfassung Test für Fenstergröße ?>	
    <script type="text/javascript">
	function Mauskontrolle (Element) {
	  var Pos = window.event.offsetX + "/" + window.event.offsetY;
	  //window.status = Pos;
	  testext.innerHTML = Pos;
	  return true;
	}
	function Mauskontrolle2 (Element) {
	  var Pos = window.event.offsetX + "/" + window.event.offsetY;
	  //window.status = Pos;
	  testext2.innerHTML = Pos;
	  return true;
	}
	function Mauskontrolle3 (Element) {
	  var Pos = window.event.offsetX + "/" + window.event.offsetY;
	  //window.status = Pos;
	  testext3.innerHTML = Pos;
	  return true;
	}
	</script>
	
	<g transform="matrix(1 0 0 -1 0 <?php echo $t=$height; ?>)">
	<rect onmousemove="Mauskontrolle2(this)" onmouseup="Mauskontrolle3(this)" x="0" y="<?php echo -$yrand; ?>" width="<?php echo $x=$dokx-20; ?>" height="<?php echo $y=$doky-20; ?>" fill="white" stroke="black" stroke-width="1"  />
	<rect  onmousedown="Mauskontrolle(this)" x="50" y="<?php echo -$yrand+200; ?>" width="20" height="20" fill="white" stroke="black" stroke-width="1"  />
	<text transform="matrix(1 0 0 -1 0 0)" id="testext" x="0" y="<?php echo -$yrand+100; ?>" >TEXT</text>
	<text transform="matrix(1 0 0 -1 0 0)" id="testext2" x="0" y="<?php echo -$yrand+130; ?>" >TEXT2</text> 
	<text transform="matrix(1 0 0 -1 0 0)" id="testext3" x="0" y="<?php echo -$yrand+160; ?>" >TEXT3</text> 
	</g>
	<?php  */ 
	
	$SVG .= '<g transform="matrix(1 0 0 -1 0 '.$t=$height+$Datei_height_plus.')" style="font-family:Arial, Helvetica, sans-serif;"> 
	<!-- SVG Inhalt -->';
	
	// Titel für Dateiausgabe
	if($_POST['Dateiausgabe']) 
	{
		$SVG .= '<text transform="matrix(1 0 0 -1 0 0)" x="'.$t=($xrand-50).'" y="0" dy="-'.$t=($yDiagramm+$yrand+$Datei_height_plus-5).'" style="font-size: 16px; font-weight: bold;" >'.$Diagrammname_Dateiausgabe1.'</text>';
		$SVG .= '<text transform="matrix(1 0 0 -1 0 0)" x="'.$t=($xrand-50).'" y="0" dy="-'.$t=($yDiagramm+$yrand+$Datei_height_plus-25).'" style="font-size: 16px;" >'.$Diagrammname_Dateiausgabe2.'</text>';
	}
	 
	//... ohne Rahmnenfläche, also auf weiß besser!
	/* <!-- Rahmen -->
	<rect x="5" y="5" width="<?php echo $t=$width-5; ?>" height="<?php echo $t=$height-5; ?>" fill="#efede2" stroke="none" stroke-width="1"/>
	*/
	
		//Horizontale XAchse unten
	$SVG .= '
	<!-- Diagramm Strahlen -->
	<!-- X -->
	<line x1="'.$xrand.'" y1="'.$yrand.'" x2="'.$t=($xDiagramm+$xrand+10).'" y2="'.$yrand.'" stroke="#323429" stroke-width="1"/>
	<!-- X-Teilung -->
	'; 
	$Beschriftung_Jahre = $Jahr_min;
	$xAnz = $Jahr_max - $Jahr_min;
	$t_xd = $xrand-$xEinheit; // Damit am Nullpunkt gestartet wird
	for($i=$xEinheit ; $i<=$xDiagramm ; $i=$i+$xEinheit)
	{
		//Unterteilungen an xAchse und vertikale Linien in Diagrammfeld
		$SVG .= '
		<!-- Linienelemente Achsen X-->
		<line x1="'.$t=$xrand+$i.'" y1="'.$t=($yrand-3).'" x2="'.$t=$xrand+$i.'" y2="'.$t=($yrand+3).'" stroke="#323429" stroke-width="1"/>
		<line x1="'.$t=$xrand+$i.'" y1="'.$t=$yrand.'" x2="'.$t=$xrand+$i.'" y2="'.$t=($yDiagramm+$yrand+10).'" stroke="#9c9c9c" stroke-width="0.5"/>
		<!-- Text an Achsen-->
		';
		// Ab einer bestimmten Anzahl von Jahren nur jedes 2. Jahr beschriften (-1 damit beim 1. Jahr begonnen wird zu zählen... sonst verrutschen die Jahre bei der Anzeige)
		if(($Beschriftung_Jahre % 2 == 0) or ($xAnz < 10))
		{
			$SVG .= '
			<g transform="matrix(1 0 0 -1 '.$xversch=$i + $xrand - ($xEinheit*0.4).' '.$t=($yrand-45).')">
				<text transform="rotate(-90,0,0)" x="0" y="0">'.$Beschriftung_Jahre.'</text>						
			</g>
			'; 
		}
		$Beschriftung_Jahre++;
	}
	
	$SVG .= '
	<!-- Y Achse links-->
	<line x1="'.$xrand.'" y1="'.$yrand.'" x2="'.$xrand.'" y2="'.$t=($yDiagramm+$yrand+10).'" stroke="#323429" stroke-width="1"/>

	<!-- Y-Teilung -->
	';
	$z=0-($Wertespektrum/2);
	$ty=0;
	for($i=0 ; $i<=$yDiagramm ; $i=$i+$yEinheit)
	{
		
	
		// Teilung nur alle $yTeilung anzeigen
		if(fmod($z, $yTeilung) == 0)
		{
			$SVG .= '
			<line x1="'.$t=($xrand-3).'" y1="'.$t=$yrand+$i.'" x2="'.$t=($xrand+3).'" y2="'.$t=$yrand+$i.'" stroke="#323429" stroke-width="1"/>
			<line x1="'.$t=$xrand.'" y1="'.$t=$yrand+$i.'" x2="'.$t=($xDiagramm+$xrand+10).'" y2="'.$t=$yrand+$i.'" stroke="#9c9c9c" stroke-width="0.5"/>
			
			<!-- Text -->
			';
			
			$fontsize_ausg = 'font-size:14px;';
			// Vorzeichen und Sonderfall ergänzen 
			if($z < 0) $z_a = $z.',0';
			if($z == 0) $z_a = 'x';
			if($z == 0 and $Ind_anz == 1) 
			{ 

				$SQL_DS_Startwert = "SELECT WERT FROM t_temp_indikatoren_diagramm ORDER BY AKT_Jahr,AKT_Monat;"; 
				$Ergebnis_DS_Startwert = mysqli_query($Verbindung,$SQL_DS_Startwert); 
				// Anfangswert usw. erfassen für Darstellung an der Y-Achse bei nur einem einzigen gew. Indikator
				$Einzelindikator_Startwert = @mysqli_result($Ergebnis_DS_Startwert,0,'WERT'); 
			
			
				$z_a = number_format($x = ($Einzelindikator_Startwert - 0),$Einzelindikator_Rundung, ',', '.'); 	//Runden+Formatierung
				$Einzelindikator_Einheit_ind_ausg = $Einzelindikator_Einheit_ind;
				
				// wenn Zahlen zu lang, dann zweite Zeile 
				$z_a_Test = $z_a.$Einzelindikator_Einheit_ind_ausg;
				if(strlen($z_a_Test) > 6)
				{
					$z_a_Zeile2 = $Einzelindikator_Einheit_ind_ausg; // Einheit anhängen	
					
				}
				else
				{
					$z_a = $z_a.$Einzelindikator_Einheit_ind_ausg; // Einheit anhängen	
					$z_a_Zeile2 = '';
				}
				
				// verschieben + skalieren
				$Rundungsverschiebung =  -$Einzelindikator_Rundung * 10; 
				$Rundungsverschiebung2 = -strlen($z_a) *10;
				if(abs($Rundungsverschiebung2) > abs($Rundungsverschiebung)) $Rundungsverschiebung = $Rundungsverschiebung2;
				if($Rundungsverschiebung < -1) $Rundungsverschiebung = -1; // Begrenzung der Verschiebung
				$Fontsize_num = round(20/(sqrt(strlen($z_a))/2));
				if($Fontsize_num > 14) $Fontsize_num = 14;
				$fontsize_ausg = 'font-size:'.$Fontsize_num.'px;';
			}
			else
			{ 
		
				$Rundungsverschiebung = 0; 
				$Einzelindikator_Einheit_ind_ausg = '';
				$fontsize_ausg = 'font-size:14px;';
				$z_a_Zeile2 = '';
			}
			if($z > 0) $z_a = '+'.$z.',0';
			
			
			// Testen, ob $z_a == 'x' für darstellung einer Legendenzeile zu x
			if($z_a == 'x') $x_Zeile_ja = 1;
			
			// Entfernen der Skalenwerte bei ausschließlicher Anzeige von NICHT-%-Indikatoren			
			if($z != 0 and $Ind_anz == 1 and $Absolutindikator_vorhanden)
			{
				if($z < 0) $z_a = '-';
				if($z > 0) $z_a = '+';
				$Rundungsverschiebung = '20';
			}
			
			$SVG .= '
			
			<text transform="matrix(1 0 0 -1 0 0)" x="'.$t=$xrand-50+$Rundungsverschiebung.'" y="0" dy="-'.$t = ($i+$yrand-2).'" style="'.$fontsize_ausg.'" >'.$z_a.'</text>
			<text transform="matrix(1 0 0 -1 0 0)" x="'.$t=$xrand-50+$Rundungsverschiebung.'" y="0" dy="-'.$t = ($i+$yrand-20).'" style="'.$fontsize_ausg.'" >'.$z_a_Zeile2.'</text>
			';
			
		
		}
		$z++;
	}
	//Ende YAchse
	
	
	
	
	// Zeichnen der Graphen, in diesem Diagramm jedoch nur Anteile (also Indikatoren mit der Einheit %)
	//$SQL_DS_vorh = "SELECT * FROM t_temp_indikatoren_diagramm WHERE ID_INDIKATOR = 'S02RG' ORDER BY AKT_Jahr;";
	
	$SQL_DS_vorh = "SELECT * FROM t_temp_indikatoren_diagramm ORDER BY SORTIERUNG_THEMA_KAT,ID_INDIKATOR,Jahr;"; 
	//$SQL_DS_vorh = "SELECT * FROM t_temp_indikatoren_diagramm WHERE EINHEIT = '%' ".$WHERE." ORDER BY SORTIERUNG_THEMA_KAT,SORTIERUNG,ID_INDIKATOR,Jahr;"; 
	$Ergebnis_DS_vorh = mysqli_query($Verbindung,$SQL_DS_vorh); 
	
	// Relative Veränderung in Tabelle füllen
	$ID_INDIKATOR_vorher = '';
	$i_ds = 0;
	$ID_INDIKATOR_aktiv = '';
	
	while($ID = @mysqli_result($Ergebnis_DS_vorh,$i_ds,'ID'))  //debug
	{
		// Vorherigen DS merken
		$WERT_vorher = $WERT;
		$ID_INDIKATOR_vorher = $ID_INDIKATOR;
		// Aktuelle Werte
		$ID_INDIKATOR = @mysqli_result($Ergebnis_DS_vorh,$i_ds,'ID_INDIKATOR');
		$WERT = @mysqli_result($Ergebnis_DS_vorh,$i_ds,'WERT');
				
		// Eintragen, wenn kein Indikatorwechsel stattfand
		if($ID_INDIKATOR_vorher == $ID_INDIKATOR)
		{
			// Relative Veränd.-Werte schreiben (wird hier mehrfach ausgeführt, ist aber zeitlich zu vernachlässigen
			$Veränderungswert_relativ_UPD = $WERT - $WERT_vorher;
			$SQL_DS_Veraend_rel = "UPDATE t_temp_indikatoren_diagramm SET 
			WERT_VERAENDERUNG_RELATIV = '".$Veränderungswert_relativ_UPD."' 
			WHERE ID = '".$ID."';";
			$Ergebnis_DS_Veraend_rel = mysqli_query($Verbindung,$SQL_DS_Veraend_rel);

	
			
		}
	
		
		
		$i_ds++;
	}
	
	$SQL_DS_vorh = "SELECT * FROM t_temp_indikatoren_diagramm ORDER BY SORTIERUNG_THEMA_KAT,ID_INDIKATOR,Jahr;"; 
	//$SQL_DS_vorh = "SELECT * FROM t_temp_indikatoren_diagramm WHERE EINHEIT = '%' ".$WHERE." ORDER BY SORTIERUNG_THEMA_KAT,SORTIERUNG,ID_INDIKATOR,Jahr;"; 
	$Ergebnis_DS_vorh = mysqli_query($Verbindung,$SQL_DS_vorh); 
	// Variablen leeren
	$ID_INDIKATOR_vorher = '';
	$i_ds = 0;
	$ID_INDIKATOR_aktiv = '';
	$AKT_Jahr_merk = ''; 
	$AKT_Monat_merk = '';
	
	// Stützpunkte durch gehen
	while($ID = @mysqli_result($Ergebnis_DS_vorh,$i_ds,'ID'))
	{
		$Stützpunkt_Ausreißer_vorher = $Stützpunkt_Ausreißer; // Ausblendung des vorherigen Punktes merken und einbeziehen
		$Stützpunkt_Ausreißer = '';
		$Stuetzpkt_ausblenden = 1; // voreingestellt jeder Punkt kritisch ... nähere Betrachtung folgt und kann dies kippen
				
		
		// Aktueller DS
		$ID_INDIKATOR = @mysqli_result($Ergebnis_DS_vorh,$i_ds,'ID_INDIKATOR');
		$AKT_Jahr_alt_temp = $AKT_Jahr_merk; // Datum des vorherigen Stützpunkt temporär merken
		$AKT_Monat_alt_temp = $AKT_Monat_merk; // Datum des vorherigen Stützpunkt temporär merken
		$AKT_Jahr = @mysqli_result($Ergebnis_DS_vorh,$i_ds,'AKT_Jahr');
		$AKT_Jahr_dez = $AKT_Jahr-$Jahr_min;
		$AKT_Monat = @mysqli_result($Ergebnis_DS_vorh,$i_ds,'AKT_Monat');
		$AKT_Monat_dez = $AKT_Monat/12;
		$WERT_real = @mysqli_result($Ergebnis_DS_vorh,$i_ds,'WERT');
		$WERT = @mysqli_result($Ergebnis_DS_vorh,$i_ds,'WERT_VERAENDERUNG');
		$WERT_VERAENDERUNG_REAL = @mysqli_result($Ergebnis_DS_vorh,$i_ds,'WERT_VERAENDERUNG_REAL'); // für Ausgabe als Text (nicht normiert)

		$WERT_VERAENDERUNG_RELATIV = @mysqli_result($Ergebnis_DS_vorh,$i_ds,'WERT_VERAENDERUNG_RELATIV'); // beim ersten DS des Indikators jeweils leer, da Update erst folgt
		$FEHLERCODE = @mysqli_result($Ergebnis_DS_vorh,$i_ds,'FEHLERCODE');
		$IND_NAME = utf8_encode(@mysqli_result($Ergebnis_DS_vorh,$i_ds,'IND_NAME'));
		$Einheit_ind = utf8_encode(@mysqli_result($Ergebnis_DS_vorh,$i_ds,'EINHEIT'));
		$Farbe_graph = @mysqli_result($Ergebnis_DS_vorh,$i_ds,'FARBWERT_MAX');
		$Rundung = @mysqli_result($Ergebnis_DS_vorh,$i_ds,'RUNDUNG_NACHKOMMASTELLEN');
		$KATEGORIE_aktuell = @mysqli_result($Ergebnis_DS_vorh,$i_ds,'KATEGORIE');
		 
	// Check ob der Abstand der Grundaktualität zum nächsten Stützpunkt ein Mindestmaß $Mindest_Akt_Diff erfüllt
		$AKT_Jahr_chk = @mysqli_result($Ergebnis_DS_vorh,$t=$i_ds+1,'AKT_Jahr');
		$AKT_Monat_chk = @mysqli_result($Ergebnis_DS_vorh,$t=$i_ds+1,'AKT_Monat');
		$Mindest_Akt_Diff = 6; // 6 Monate Mindestabstand
		$Mindest_Akt_aktiv = ($AKT_Jahr*12)+$AKT_Monat+6;
		$Mindest_Akt_naechstes = ($AKT_Jahr_chk*12)+$AKT_Monat_chk;
		$ID_INDIKATOR_naechstes = @mysqli_result($Ergebnis_DS_vorh,$t=$i_ds+1,'ID_INDIKATOR');
		$WERT_naechstes = @mysqli_result($Ergebnis_DS_vorh,$t=$i_ds+1,'WERT_VERAENDERUNG');
		$WERT_VERAENDERUNG_RELATIV_naechstes = @mysqli_result($Ergebnis_DS_vorh,$t=$i_ds+1,'WERT_VERAENDERUNG_RELATIV');
		// Status des Punktes vorher merken 
		$AKT_chk_vorher = $AKT_chk;
		// Var. zurücksetzen
		$AKT_chk = '0';
		// Aktualitätsentfernung
		if($t = ($Mindest_Akt_naechstes - $Mindest_Akt_aktiv) >= $Mindest_Akt_Diff) 
		{
			// Aktualitätsentfernung ok
			$AKT_chk = 1; 
			
			
		}
		
		// Wenn vorheriger Punkt entfernt wurde, nochmals checken, ob die Aktualitätsdifferenz vom vorherigen zum dann nun folgenden Punkt ok wäre und dann Anzeigen des akt. Punktes
		// Dies verhindert zu starkes ausdünnen, aber auch Punktwolken werden eliminiert
		if($AKT_chk_vorher === '0' or $Stützpunkt_Ausreißer_vorher == '1')
		{
			$AKT_Jahr_chk = @mysqli_result($Ergebnis_DS_vorh,$t=$i_ds+1,'AKT_Jahr');
			$AKT_Monat_chk = @mysqli_result($Ergebnis_DS_vorh,$t=$i_ds+1,'AKT_Monat');
			$Mindest_Akt_Diff = 6; // 6 Monate Mindestabstand
			$Mindest_Akt_aktiv = (@mysqli_result($Ergebnis_DS_vorh,$t=$i_ds-1,'AKT_Jahr')*12)+ @mysqli_result($Ergebnis_DS_vorh,$t=$i_ds-1,'AKT_Monat')+6;
			$Mindest_Akt_naechstes = ($AKT_Jahr_chk*12)+$AKT_Monat_chk;
			if($t = ($Mindest_Akt_naechstes - $Mindest_Akt_aktiv) >= $Mindest_Akt_Diff) $AKT_chk = 1; // Aktualitätsentfernung zum vorvorherigen ok
		}
		
		
		
	// TEST => echo "
	// TEST =>  AKT:".$Mindest_Akt_naechstes."-".$Mindest_Akt_aktiv."=>".$AKT_chk." ";
		
		/* */
		
	    // Ausreißer löschen	
		/* ?><script type="text/javascript">alert('Standardabw.:<?php echo abs($Standardabweichung[$ID_INDIKATOR]); ?> Wert: <?php echo abs($WERT); ?>');</script><?php */
		// Prüfen, ob es sich um Anfangs oder Endpunkt handelt, dann nicht einspringen!

	// TEST => echo " P:".$ID_INDIKATOR_vorher.' '.$ID_INDIKATOR.' '.$ID_INDIKATOR_naechstes.' _ ';
	
		// Prüfen, ob Stützpunkt an beiden Flanken die gleiche Richtung hat
		if(($WERT_vorher < $WERT) and ($WERT > $WERT_naechstes) or ($WERT_vorher > $WERT) and ($WERT < $WERT_naechstes))
		{
				/* 
				// Ausreißer mittels Standardabweichung ermitteln und eliminieren
				// Standardabweichung für Indikator ermitteln
				$SQL_DS_Stdev = "SELECT STDDEV(WERT_VERAENDERUNG_RELATIV) FROM t_temp_indikatoren_diagramm WHERE ID_INDIKATOR = '".$ID_INDIKATOR."';"; 
				$Ergebnis_DS_Stdev = mysqli_query($Verbindung,$SQL_DS_Stdev); 
				$Standardabweichung[$ID_INDIKATOR] = @mysqli_result($Ergebnis_DS_Stdev,0,0);
				
				// Markergrenze für Hinweis auf Ausreißer
				$StdAbw_toleranz = abs($Standardabweichung[$ID_INDIKATOR] * 1.02);
				 */
				/* Test-Alert:
				?><script type="text/javascript">alert('Standardabw.:<?php echo abs($Standardabweichung[$ID_INDIKATOR]); ?> Wert: <?php echo abs($WERT_VERAENDERUNG_RELATIV); ?> naechster Wert: <?php echo abs($WERT_VERAENDERUNG_RELATIV_naechstes); ?>');</script><?php */
				/* 
				// bleibt innerhalb der vorgegebenen Parameter (Punktdifferenzen mit der Standardabweichung testen)
				if(abs($WERT_VERAENDERUNG_RELATIV) > $StdAbw_toleranz and abs($WERT_VERAENDERUNG_RELATIV_naechstes) > $StdAbw_toleranz)
				{
					// Punkt als ausreißer markieren
					$Stützpunkt_Ausreißer = 1;
				}	
				 */
				 	
				// ==> jetzt radikaleres vorgehen und immer als Ausreißer definieren, wenn beide Flanken gegenläufig sind 
					// Punkt als ausreißer markieren
					$Stützpunkt_Ausreißer = 1;
	
		}

		
	
	// Stützpunkt bewerten
	// Endpunkt
	if($ID_INDIKATOR != $ID_INDIKATOR_naechstes)
	{
		$Stuetzpkt_ausblenden = 0;
	}
	
	// Anfangspunkt UND kein Aktualitätsproblem auftritt => anzeigen
	// wieso Aktualitätscheck ??? if($ID_INDIKATOR_vorher != $ID_INDIKATOR and $AKT_chk)
	
	// Anfangspunkt  => anzeigen
	if($ID_INDIKATOR_vorher != $ID_INDIKATOR)
	{
		$Stuetzpkt_ausblenden = 0;
		$AKT_chk = '1'; // Setzen der Variable für Aktualitätscheck des nächsten Punktes
	}
	else
	{
		// einzelner Ausreißer UND kein Aktualitätsproblem auftritt => anzeigen
		if(!$Stützpunkt_Ausreißer and $AKT_chk)
		{
			$Stuetzpkt_ausblenden = 0;
		}
	}
	// Menüeinstellung auf "alle anzeigen" gesetzt
	if($_SESSION['Diagramm']['Alle_Stuetzpunkte_zeigen'])
	{
		$Stuetzpkt_ausblenden = 0;
	}
	
	// Vorherigen DS merken
	$WERT_vorher = $WERT;
	$ID_INDIKATOR_vorher = $ID_INDIKATOR; 
		
	// evtl. Ausblendung des Stützpunktes
	if(!$Stuetzpkt_ausblenden or $Admin_Anzeige)
	{
		// Datum dieses Stützpunkts weitergeben
		$AKT_Jahr_merk = $AKT_Jahr; 
		$AKT_Monat_merk = $AKT_Monat; 
		
		// Datum des vorherigen Stützpunkt FEST merken
		$AKT_Jahr_alt = $AKT_Jahr_alt_temp; 
		$AKT_Monat_alt = $AKT_Monat_alt_temp;
		
		
		// Zähler für Indikatoranz in gleicher Kategorie
		if(($KATEGORIE_aktuell != $KATEGORIE_alt))
		{
			$KAT_IND_Anz = 0;
		}
		else
		{
			// Auswerten ob IND noch der Gleiche ist
			if($ID_INDIKATOR != $ID_INDIKATOR_alt)
			{
				$KAT_IND_Anz = $KAT_IND_Anz+1;					  
			}
		}
		$KATEGORIE_alt = $KATEGORIE_aktuell;
		$ID_INDIKATOR_alt = $ID_INDIKATOR;
		
		
		// Prüfen auf Indikator Wechsel aus der Abfrage
		if($ID_INDIKATOR_aktiv != $ID_INDIKATOR or !$ID_INDIKATOR_aktiv)
		{
			
			// Variablen zum zeichnen leeren
			$x1 = '';
			$y1 = '';
			
			// Vergleichswert zurücksetzen
			$WERT_vorher = 0;
			$WERT_VERAENDERUNG_REAL_vorher = 0;
			
			// Zeile für Legende erzeugen 
			// Text
			$Leg_Kopf_y = $yrand - $Leg_Kopf;
			$Leg_text_dy = $Leg_text_dy + $Zeilenhoehe;
			$xrand_LegText = $xrand + 25;
			$Legende_Ind .= '<text transform="matrix(1 0 0 -1 0 0)" x="'.$xrand_LegText.'" y="-'.$Leg_Kopf_y.'" dy="'.$Leg_text_dy.'" >'.$IND_NAME.' ('.$Einheit_ind.')</text>';
			// Symbol
			$Legende_Ind_Symb_yt = $Legende_Ind_Symb_yt + $Zeilenhoehe;
			$Legende_Ind_Symb_y = $Leg_Kopf_y + 5 - $Legende_Ind_Symb_yt;
			$Legende_Ind_Symb_x1 = $xrand - 10;
			$Legende_Ind_Symb_x2 = $xrand + 20;
			// ... verlagert, sollen untersch. sein: $Legende_Ind_Symb .= '<line x1="'.$Legende_Ind_Symb_x1.'" y1="'.$Legende_Ind_Symb_y.'" x2="'.$Legende_Ind_Symb_x2.'" y2="'.$Legende_Ind_Symb_y.'" stroke="#'.$Farbe_graph.'" stroke-width="1" stroke-dasharray="15,2" />';
			//$Legende_Ind_Symb .= '<circle cx="'.$Legende_Ind_Symb_x1.'" cy="'.$Legende_Ind_Symb_y.'" r="5" stroke="#'.$Farbe_graph.'" stroke-width="2" fill="white" ></circle>';
		
		
		
		// Symbole ändern, wenn Kategorie die gleiche bleibt
				if($KAT_IND_Anz=='0' or $KAT_IND_Anz=='4' or $KAT_IND_Anz >= '8')
				{
					// Linie zeichnen
					$Legende_Ind_Symb .= '<line x1="'.$Legende_Ind_Symb_x1.'" y1="'.$Legende_Ind_Symb_y.'" x2="'.$Legende_Ind_Symb_x2.'" y2="'.$Legende_Ind_Symb_y
					.'" stroke="#'.$Farbe_graph.'" stroke-width="1" stroke-dasharray="15,2" />';
					// Punkt zeichnen
					$Legende_Ind_Symb .= '<circle cx="'.$Legende_Ind_Symb_x1.'" cy="'.$Legende_Ind_Symb_y.'" r="5" stroke="#'.$Farbe_graph.'" stroke-width="2" fill="white" ></circle>';
				}
				
				if($KAT_IND_Anz=='1' or $KAT_IND_Anz=='5')
				{
					// Linie zeichnen
					$Legende_Ind_Symb .= '<line x1="'.$Legende_Ind_Symb_x1.'" y1="'.$Legende_Ind_Symb_y.'" x2="'.$Legende_Ind_Symb_x2.'" y2="'.$Legende_Ind_Symb_y
					.'" stroke="#'.$Farbe_graph.'" stroke-width="1" stroke-dasharray="15,2,4,2" />';
					// Rechteck zeichnen
					$Legende_Ind_Symb .= '<rect x="'.$temp=($Legende_Ind_Symb_x1-4).'" y="'.$temp=($Legende_Ind_Symb_y-4).'" width ="8" height ="8" stroke="#'.$Farbe_graph.'" stroke-width="2" fill="white" />';
					
					
				}
				
				if($KAT_IND_Anz=='2' or $KAT_IND_Anz=='6')
				{
					// Linie zeichnen
					$Legende_Ind_Symb .= '<line x1="'.$Legende_Ind_Symb_x1.'" y1="'.$Legende_Ind_Symb_y.'" x2="'.$Legende_Ind_Symb_x2.'" y2="'.$Legende_Ind_Symb_y
					.'" stroke="#'.$Farbe_graph.'" stroke-width="1" stroke-dasharray="10,2,2,2,2,2" />';
					// Dreieck zeichnen
					$Legende_Ind_Symb .= '<path d="M'.$temp=($Legende_Ind_Symb_x1-5).' '.$temp=($Legende_Ind_Symb_y-3).' l10 0 l-5 8 Z" stroke="#'.$Farbe_graph.'" stroke-width="2" fill="white" />';
					 
				}
				
				if($KAT_IND_Anz=='3' or $KAT_IND_Anz=='7')
				{
					// Linie zeichnen
					$Legende_Ind_Symb .= '<line x1="'.$Legende_Ind_Symb_x1.'" y1="'.$Legende_Ind_Symb_y.'" x2="'.$Legende_Ind_Symb_x2.'" y2="'.$Legende_Ind_Symb_y
					.'" stroke="#'.$Farbe_graph.'" stroke-width="1" stroke-dasharray="10,2,6,2,2,2" />';
					// gedrehtes Rechteck zeichnen
					$Legende_Ind_Symb .= '<path d="M'.$temp=($Legende_Ind_Symb_x1-6).' '.$Legende_Ind_Symb_y.' l6 -6 l6 6 l-6 6 Z" stroke="#'.$Farbe_graph.'" stroke-width="2" fill="white"  />';

				}		
		}
		
					
		$ID_INDIKATOR_aktiv = $ID_INDIKATOR;
		
		// Anteils-Diagramm nur für Ind. mit Einheit = %
		// -----------------------------------------------
		//if($Einheit_ind == '%')
		//{
					
			// Werte für Punktkoordinaten erfassen
			if(!$FEHLERCODE)
			{
				$x2 = $xrand + ($xEinheit * ($AKT_Jahr_dez + $AKT_Monat_dez));
				$y2 = $yrand + ($yDiagramm/2) + ($yEinheit * $WERT); // durch $yDiagramm/2 in die Diagrammmitte verschoben

				//$WERT_Ausg = number_format($x = ($WERT - $WERT_vorher),$Rundung, ',', '.');
				$WERT_Ausg = number_format($x_ausg = ($WERT_VERAENDERUNG_REAL - $WERT_VERAENDERUNG_REAL_vorher),$Rundung, ',', '.');
				// Änderung verfolgen und nicht nur vom Nullpunkt aus angeben (unschön sonst)
				$WERT_vorher = $WERT;
				$WERT_VERAENDERUNG_REAL_vorher = $WERT_VERAENDERUNG_REAL;
				$WERT_real = number_format($x = ($WERT_real - 0),$Rundung, ',', '.');
				
				// Einheit bei % auf Prozentpunkte setzen
				$Einheit_ind_Veraenderg = $Einheit_ind;
				if($Einheit_ind == '%') $Einheit_ind_Veraenderg = ' Prozentpunkte';
				
				
				// Richtung der Entwicklung ermitteln
				if($x_ausg > 0.0000) 
				{
					$Richtung = '+';
				}
				else
				{
					$Richtung = '';
				}
				// Symbole ändern, wenn Kategorie die gleiche bleibt
////////////////Punkte						
				
				if($KAT_IND_Anz=='0' or $KAT_IND_Anz=='4' or $KAT_IND_Anz >= '8')
				{
					// Linie zeichen
					if($y1)
					{
						if ($AKT_Jahr >= '2025' && $_SESSION['Tabelle']['Trend_Raumgliederung'] == '1' )
						{
									$Diagramm_Lines .= '
									<line x1="'.$x1.'" y1="'.$y1.'" x2="'.$x2.'" y2="'.$y2.'" stroke="#'.$Farbe_graph.'" stroke-dasharray="4,2" stroke-width="1" opacity ="0.5">
									<title>'.$IND_NAME.'</title>   
									</line>';
						}
						elseif ($AKT_Jahr >= '2025' && $_SESSION['Tabelle']['Trend_Raumgliederung']!= '1' )
						{								//zeichne keine Linie zw Punkten						
						}
						else
						{										
									$Diagramm_Lines .= '
									<line x1="'.$x1.'" y1="'.$y1.'" x2="'.$x2.'" y2="'.$y2.'" stroke="#'.$Farbe_graph.'" stroke-dasharray="15,2" stroke-width="1" >
									<title>'.$IND_NAME.'</title>   
									</line>';									
						}
					}
					// Punkt zeichnen
					
					if ($AKT_Jahr >= '2025'&& $_SESSION['Tabelle']['Trend_Raumgliederung'] == '1'){
					
						$Diagramm_Circles .= '
						<circle cx="'.$x2.'" cy="'.$y2.'" r="3" stroke="#'.$Farbe_graph.'" stroke-width="2" fill="white" opacity ="0.5">
						<title>'.$IND_NAME.' (Indikatorwert: '.$WERT_real.$Einheit_ind;
				 }
					elseif ($AKT_Jahr >= '2025' && $_SESSION['Tabelle']['Trend_Raumgliederung']!= '1'){	$Trendpunkt_pkt='1';
								}
				else
				{
								$Diagramm_Circles .= '
					<circle cx="'.$x2.'" cy="'.$y2.'" r="3" stroke="#'.$Farbe_graph.'" stroke-width="2" fill="white">
					<title>'.$IND_NAME.' (Indikatorwert: '.$WERT_real.$Einheit_ind;
				}
					
					// Entwicklung an Startpunkt nicht anzeigen
					if($AKT_Jahr_alt)
					{
						$Diagramm_Circles .= ' / Entwicklung: '.$Richtung.$WERT_Ausg.$Einheit_ind_Veraenderg.' von '.$AKT_Monat_alt.'/'.$AKT_Jahr_alt.' bis '.$AKT_Monat.'/'.$AKT_Jahr; 
					}
					else
					{
						$Diagramm_Circles .= '; Stand: '.$AKT_Monat.'/'.$AKT_Jahr;
					}
			if ($Trendpunkt_pkt != '1'){
					$Diagramm_Circles .= ')</title>   
				</circle>'; }
				}
////////////////Rechteckig				
				if($KAT_IND_Anz=='1' or $KAT_IND_Anz=='5')
				{
					if($y1)
					{
					// Linie zeichen zw Rechtecken			
						if ($AKT_Jahr >= '2025'&& $_SESSION['Tabelle']['Trend_Raumgliederung'] == '1'){	
					
						$Diagramm_Lines .= '
						<line x1="'.$x1.'" y1="'.$y1.'" x2="'.$x2.'" y2="'.$y2.'" stroke="#'.$Farbe_graph.'" stroke-dasharray="4,2" stroke-width="1" opacity ="0.5" >
						<title>'.$IND_NAME.'</title>   
						</line>';
					}
							elseif ($AKT_Jahr >= '2025' && $_SESSION['Tabelle']['Trend_Raumgliederung']!= '1' )
						{								//zeichne keine Linie	zw Rechtecken		 			
						}	
					else{
						
						
						$Diagramm_Lines .= '
						<line x1="'.$x1.'" y1="'.$y1.'" x2="'.$x2.'" y2="'.$y2.'" stroke="#'.$Farbe_graph.'" stroke-dasharray="15,2,4,2" stroke-width="1" >
						<title>'.$IND_NAME.'</title>   
						</line>';
					}
						
					}
					// Rechteck zeichnen
				if ($AKT_Jahr >= '2025' && $_SESSION['Tabelle']['Trend_Raumgliederung'] == '1'){	
						$Diagramm_Circles .= '<rect x="'.$temp=($x2-3).'" y="'.$temp=($y2-3).'" width ="6" height ="6" stroke="#'.$Farbe_graph.'" stroke-width="1.5" fill="white" opacity ="0.5" >
						<title>'.$IND_NAME.' (Indikatorwert: '.$WERT_real.$Einheit_ind;
				 }					
				elseif ($AKT_Jahr >= '2025' && $_SESSION['Tabelle']['Trend_Raumgliederung']!= '1' )
						{	//zeichne kein Rechteck & kennzeichne Trendpunkt 			
							$Trendpunkt_rechteck='1';			
						}	
				else{
							$Diagramm_Circles .= '<rect x="'.$temp=($x2-3).'" y="'.$temp=($y2-3).'" width ="6" height ="6" stroke="#'.$Farbe_graph.'" stroke-width="1.5" fill="white"  >
					<title>'.$IND_NAME.' (Indikatorwert: '.$WERT_real.$Einheit_ind;
					
					
					}
					
					// Entwicklung an Startpunkt nicht anzeigen
					if($AKT_Jahr_alt)
					{
						$Diagramm_Circles .= ' / Entwicklung: '.$Richtung.$WERT_Ausg.$Einheit_ind_Veraenderg.' von '.$AKT_Monat_alt.'/'.$AKT_Jahr_alt.' bis '.$AKT_Monat.'/'.$AKT_Jahr; 
					}
					else
					{
						$Diagramm_Circles .= '; Stand: '.$AKT_Monat.'/'.$AKT_Jahr;
					}
					
				//schließe Rechtecke, nur wenn kein Trendpunkt
				if ($Trendpunkt_rechteck != '1'){
					$Diagramm_Circles .= ')</title>
					</rect>
				';}
					?><script type="text/javascript">
					alert('<?php echo $Diagramm_Circles; ?>');
					</script>
					<?php 
					
				}
////////////////Dreieckig					
				if($KAT_IND_Anz=='2' or $KAT_IND_Anz=='6')
				{
					if($y1)
					{
						// Linie zeichenn
						
								if ($AKT_Jahr >= '2025' && $_SESSION['Tabelle']['Trend_Raumgliederung'] == '1'){	
									$Diagramm_Lines .= '
									<line x1="'.$x1.'" y1="'.$y1.'" x2="'.$x2.'" y2="'.$y2.'" stroke="#'.$Farbe_graph.'" stroke-dasharray="4,2" stroke-width="1" opacity ="0.5">
									<title>'.$IND_NAME.'</title>   
									</line>';
								}	
										elseif ($AKT_Jahr >= '2025' && $_SESSION['Tabelle']['Trend_Raumgliederung']!= '1' )
						{								//zeichne keine Linie					
						}					
								else{
										$Diagramm_Lines .= '
									<line x1="'.$x1.'" y1="'.$y1.'" x2="'.$x2.'" y2="'.$y2.'" stroke="#'.$Farbe_graph.'" stroke-dasharray="10,2,2,2,2,22" stroke-width="1" >
									<title>'.$IND_NAME.'</title>   
									</line>';									
								}					
						
					}
					// Dreieck zeichnen
					
						if ($AKT_Jahr >= '2025' && $_SESSION['Tabelle']['Trend_Raumgliederung'] == '1'){	
							$Diagramm_Circles .= '
							<path d="M'.$temp=($x2-4).' '.$temp=($y2-2).' l8 0 l-4 6 Z" stroke="#'.$Farbe_graph.'" stroke-width="1.5" fill="white" opacity ="0.5"  >
							<title>'.$IND_NAME.' (Indikatorwert: '.$WERT_real.$Einheit_ind;
						}
							elseif ($AKT_Jahr >= '2025' && $_SESSION['Tabelle']['Trend_Raumgliederung']!= '1' )
						{		//zeichne kein Dreieck aber kennzeichne Trendpunkt
							$Trendpunkt_dreieck='1';
						}	
						else{
							$Diagramm_Circles .= '
							<path d="M'.$temp=($x2-4).' '.$temp=($y2-2).' l8 0 l-4 6 Z" stroke="#'.$Farbe_graph.'" stroke-width="1.5" fill="white"  >
							<title>'.$IND_NAME.' (Indikatorwert: '.$WERT_real.$Einheit_ind;
						}
					
					// Entwicklung an Startpunkt nicht anzeigen
					if($AKT_Jahr_alt)
					{
						$Diagramm_Circles .= '; Entwicklung: '.$Richtung.$WERT_Ausg.$Einheit_ind_Veraenderg.' von '.$AKT_Monat_alt.'/'.$AKT_Jahr_alt.' bis '.$AKT_Monat.'/'.$AKT_Jahr; 
					}
					else
					{
						$Diagramm_Circles .= ' / Stand: '.$AKT_Monat.'/'.$AKT_Jahr;
					}
					if ($Trendpunkt_dreieck != '1'){
					$Diagramm_Circles .= ')</title>   
				</path>';}
					 
				}
		////Rauten		
				if($KAT_IND_Anz=='3' or $KAT_IND_Anz=='7')
				{
					if($y1)
					{
						if ($AKT_Jahr >= '2025' && $_SESSION['Tabelle']['Trend_Raumgliederung'] == '1')
						{	
							// Linie zeichen 
							$Diagramm_Lines .= '
							<line x1="'.$x1.'" y1="'.$y1.'" x2="'.$x2.'" y2="'.$y2.'" stroke="#'.$Farbe_graph.'" stroke-dasharray="4,2" stroke-width="1" opacity ="0.5">
							<title>'.$IND_NAME.'</title>   
							</line>';
					  }
						elseif ($AKT_Jahr >= '2025' && $_SESSION['Tabelle']['Trend_Raumgliederung']!= '1' )
						{								//zeichne keine Linie
							
						}	
						else{
							$Diagramm_Lines .= '
							<line x1="'.$x1.'" y1="'.$y1.'" x2="'.$x2.'" y2="'.$y2.'" stroke="#'.$Farbe_graph.'" stroke-dasharray="10,2,6,2,2,2" stroke-width="1" >
							<title>'.$IND_NAME.'</title>   
							</line>';
							}					
				}
					
										
					// gedrehtes Rechteck zeichnen
					
					if ($AKT_Jahr >= '2025' && $_SESSION['Tabelle']['Trend_Raumgliederung'] == '1')
						{	
							$Diagramm_Circles .= '
							<path d="M'.$temp=($x2-4).' '.$y2.' l4 -4 l4 4 l-4 4 Z" stroke="#'.$Farbe_graph.'" stroke-width="1.5" fill="white" opacity ="0.5" >
							<title>'.$IND_NAME.' (Indikatorwert: '.$WERT_real.$Einheit_ind;
						}
							elseif ($AKT_Jahr >= '2025' && $_SESSION['Tabelle']['Trend_Raumgliederung']!= '1' )
						{								//zeichne keine Raute	
							$Trendpunkt_raute='1';						
						}	
						else{	
							$Diagramm_Circles .= '
							<path d="M'.$temp=($x2-4).' '.$y2.' l4 -4 l4 4 l-4 4 Z" stroke="#'.$Farbe_graph.'" stroke-width="1.5" fill="white"  >
							<title>'.$IND_NAME.' (Indikatorwert: '.$WERT_real.$Einheit_ind;
						}
					
					// Entwicklung an Startpunkt nicht anzeigen
					if($AKT_Jahr_alt)
					{
						$Diagramm_Circles .= ' / Entwicklung: '.$Richtung.$WERT_Ausg.$Einheit_ind_Veraenderg.' von '.$AKT_Monat_alt.'/'.$AKT_Jahr_alt.' bis '.$AKT_Monat.'/'.$AKT_Jahr; 
					}
					else
					{
						$Diagramm_Circles .= '; Stand: '.$AKT_Monat.'/'.$AKT_Jahr;
					}
					
				if ($Trendpunkt_raute != '1'){
					$Diagramm_Circles .= ')</title>   
					</path>';
				}
				}
				
			}
			
		
			/* 
			if(!$FEHLERCODE and $y1)
			{
				$Diagramm_Lines .= '
				<line x1="'.$x1.'" y1="'.$y1.'" x2="'.$x2.'" y2="'.$y2.'" stroke="#'.$Farbe_graph.'" stroke-dasharray="5,10" stroke-width="1" >
				<title>'.$IND_NAME.'</title>   
				</line>';
			} */
			// Werte des Vorpunkts behalten, für Linie
			$x1 = $x2;
			$y1 = $y2;
		//}
		// -----------------------------------------------
		
		
		// Veränderungsdiagramm für (jeden gewählten) Indikator
		// -----------------------------------------------
		
		
		
		
		// -----------------------------------------------
		
		
		} 
		$i_ds++;
		
	}
	
		// Legendenzeile für x einfügen, wenn dargestellt (mehrere Indikatoren gewählt also)
		if($x_Zeile_ja)
		{
			$Legende_Ind .= '<text transform="matrix(1 0 0 -1 0 0)" x="'.$Legende_Ind_Symb_x1.'" y="-'.$Leg_Kopf_y.'" dy="'.$xdy = ($Leg_text_dy + $Zeilenhoehe).'" >x:</text>';
			$Legende_Ind .= '<text transform="matrix(1 0 0 -1 0 0)" x="'.$xrand_LegText.'" y="-'.$Leg_Kopf_y.'" dy="'.$xdy = ($Leg_text_dy + $Zeilenhoehe).'" >Ausgangswert</text>';
		
			
		}	
	
	// Legende
	

			
			
			
	// SVG-Ausgabe der Daten und der Legende
	$SVG .= $Diagramm_Lines.$Diagramm_Circles.$Legende_Ind.$Legende_Ind_Symb;
	
	// Im Exportmodus ausblenden
	if(!$_POST['Dateiausgabe'])
	{
		$SVG .= '
    	<g class="nicht_im_print">
			<a  xlink:href="?height_minus=1"><use xlink:href="#schalter" transform="translate('.$tr=($dokx-20).','.$tr=-($yrand-65).')" /></a>
			<a  xlink:href="?height_plus=1"><use xlink:href="#schalter" transform="translate('.$tr=($dokx-20).','.$tr=-($yrand-40).') rotate(180,10,10) " /></a>
			<a  xlink:href="?width_minus=1"><use xlink:href="#schalter" transform="translate('.$tr=($dokx-75).','.$tr=-($yrand-10).') rotate(90,10,10) " /></a>
			<a  xlink:href="?width_plus=1"><use xlink:href="#schalter" transform="translate('.$tr=($dokx-50).','.$tr=-($yrand-10).') rotate(-90,10,10) " /></a>
		</g>';
	}
	$SVG .= '
	</g>
</svg>';



// ------------------------------------------- EXPORT -----------------------------------------------



if(!$_POST['Dateiausgabe'])
{
	// Ausgabe SVG
	echo $SVG;
}
else
{ 
	// Ausgabe SVG in Datei
	// Löschen von Dateien die älter als eine Stunde sind 3600(s) Abweichung von der Systemzeit
	$Pfad = '../temp/';

	// Verzeichnis leeren... nur ältere Dateien!
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
 	
	
	
	// Ausgabe in Datei
	$Dateiname = 'Entwicklungsdiagramm_'.$_SESSION['Diagramm']['name'].'_'.$_SESSION['Diagramm']['Raumgliederung'].'_'.date('YmdHis');
	// Dateiname Web/Verzeichnissicher machen
	$Ersetzuingen = array(

        ' ' => '_',
        'Ä' => 'Ae',
        'Ö' => 'Oe',
        'Ü' => 'Ue',
        'ä' => 'ae',
        'ö' => 'oe',
        'ü' => 'ue',
        'ß' => 'ss',
		'.' => '_',
		'/' => '_',
		'\\' => '_',
		'!' => '_',
		'(' => '_',
		')' => '_',
		'?' => '_'
	);
	
	$Dateiname = strtr($Dateiname,$Ersetzuingen);
	// $Dateiname = date('YmdHis').'_'.rand(1,100);
	$Dateiname_svg = $Dateiname.'.svg';
	
	$Datei = fopen('../temp/'.$Dateiname_svg,'w+');
	fwrite($Datei,$SVG);
	fclose($Datei);

	// Test für Rasterisierung mittels Batik (Filetypes können sein: image/png, image/jpeg, image/tiff, application/pdf)
	// $_SESSION['Dokument']['Dateiausgabe_width'] = 3000;

	switch($_POST['Dateiausgabe_typ_datei'])
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
	$Output = shell_exec('java -Xmx1536m -jar /srv/www/htdocs/monitor/svg_viewer/batik/batik-rasterizer.jar -d /srv/www/htdocs/monitor/svg_viewer/temp '.$Qualitaet.' -w '.$_POST['Dateiausgabe_width'].' -m '.$_SESSION['Dokument']['Dateiausgabe_typ'].' /srv/www/htdocs/monitor/svg_viewer/temp/'.$Dateiname_svg );

	
	
	
	// SVG mit Downloadlink ausgeben
	echo utf8_encode('<?xml version="1.0" encoding="utf-8"?>
			<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN"  "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">
			<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:ev="http://www.w3.org/2001/xml-events" version="1.1" baseProfile="full" 
			viewBox="0 0 500 600" width="500px" height="600px">
			<rect x="0" y="0" width="500px" height="600px" fill="#FFFFFF" stroke="none"/>
			<text x="40" y="40" style="font-size:16px; font-family:Arial;" >Die angeforderte Grafikdatei wurde fertiggestellt:</text>
			<a xlink:href="../temp/'.$Dateiname.'.'.$_POST['Dateiausgabe_typ_datei'].'" target="_self">
				<rect x="115" y="60" width="175px" height="44px" fill="#eeeeee" stroke="#555555"/>
				<text x="130" y="91" style="font-size:30px; font-family:Arial; font-weight:bold;" >Download</text>
			</a>
		</svg>'); 
}



?>


</div>



























<?php 
// Im Exportmodus ausblenden
/* */
if(!$_POST['Dateiausgabe'])
{
?>
	<br />
	<br />

    <div class="nicht_im_print" id="rasterize" name="rasterize" style="font-size:12px; margin-bottom:8px; clear:both;">
          <div style="font-weight:bold; font-size:16px;"> Diagramm als Datei downloaden:</div>
      <form action="" method="post" target="_blank">
                <br />
				<div style="margin-bottom:3px;">Ausgabeeinstellungen:</div>
				
                <select name="Dateiausgabe_typ_datei">
				  <option value="png" <?php if($_SESSION['Dokument']['Dateiausgabe_typ_datei'] == 'png') { ?> selected="selected"<?php } ?> >PNG</option>
				  <option value="tif" <?php if($_SESSION['Dokument']['Dateiausgabe_typ_datei'] == 'tif') { ?> selected="selected"<?php } ?> >TIFF</option>
				  <option value="jpg" <?php if($_SESSION['Dokument']['Dateiausgabe_typ_datei'] == 'jpg') { ?> selected="selected"<?php } ?> >JPEG</option>
				  <option value="pdf" <?php if($_SESSION['Dokument']['Dateiausgabe_typ_datei'] == 'pdf') { ?> selected="selected"<?php } ?> >PDF</option>
				</select> 
                <select name="Dateiausgabe_width">
				  <option value="3000" <?php if($_SESSION['Dokument']['Dateiausgabe_width'] == '3000') { ?> selected="selected"<?php } ?> >3000 px</option>
				  <option value="2000" <?php if($_SESSION['Dokument']['Dateiausgabe_width'] == '2000' or !$_SESSION['Dokument']['Dateiausgabe_width']) { ?> selected="selected"<?php } ?> >2000 px</option>
				  <option value="1200" <?php if($_SESSION['Dokument']['Dateiausgabe_width'] == '1200') { ?> selected="selected"<?php } ?> >1200 px</option>
				</select> 
				  
                <br />
				<input name="Erzeugen" type="submit" value="Datei erzeugen" class="button_gruen_abschicken" style="margin-top:6px;" />
                <input name="Dateiausgabe" type="hidden" value="1" /><br />
                <br />
				<a href="http://xmlgraphics.apache.org/batik/tools/rasterizer.html" target="_blank">Mit freundlicher Unterstützung des &quot;batik&quot; SVG-Rasterizer</a>.
                
				<br />
				<br />
				<br />
				<br />
<br />	
                </form>
              	
                
 </div>
	<?php 
} 
?>


</div>




</body>
</html>