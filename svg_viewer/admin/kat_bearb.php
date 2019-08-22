<?php 
session_start();

// Aufruf nur für Prüfer
if($_SESSION['Dokument']['ViewBerechtigung'] != "0")
{
 die;
}

include("../includes_classes/verbindung_mysqli.php");

// Datensatz bei Bedarf updaten
if($_POST['aktion'] == "update")
{
	// Update der Tabelle m_thematische_kategorien (ID_THEMA_KAT wird automatisch kaskadierend in der DB geändert) 
	$SQL_UPDATE_2 = "UPDATE m_thematische_kategorien 
	SET ID_THEMA_KAT = '".$_POST['ID_KAT']."',
	THEMA_KAT_NAME = '".utf8_decode($_POST['NAME'])."',
	THEMA_KAT_NAME_EN = '".utf8_decode($_POST['NAME_EN'])."'
	WHERE ID_THEMA_KAT = '".$_POST['ID_KAT_org']."' 
	";
	$Ergebnis_UPD_2 = mysqli_query($Verbindung,$SQL_UPDATE_2);
	
	// Update der Tabelle m_them_kategorie_freigabe
	$SQL_UPDATE = "UPDATE m_them_kategorie_freigabe 
	SET 
	STATUS_KATEGORIE_PRUEFG = '".$_POST['STATUS_PRUEFUNG']."',
	DATUM_PRUEFUNG = '".$_POST['PruefDatum']."',
	PRUEFER = '".utf8_decode($_POST['Pruefer'])."',
	STATUS_KATEGORIE_PRUEFG_BEMERKUNG = '".utf8_decode($_POST['Bemerkg'])."',
	STATUS_KATEGORIE_FREIGABE = '".$_POST['STATUS_FREIGABE']."' 
	WHERE ID_THEMA_KAT = '".$_POST['ID_KAT']."' 
	AND JAHR = '".$_POST['Jahr']."' 
	";
		$Ergebnis_UPD = mysqli_query($Verbindung,$SQL_UPDATE);
	

	
}




$Aufklapp = $_POST['Aufklapp'];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Kategorie bearbeiten</title>
<link href="../screen_viewer.css" rel="stylesheet" type="text/css" />
<style type="text/css">
<!--
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
-->
</style></head>
<body style="padding-left:40px;">
<a name="oben" id="oben"></a>
<?php 

$Jahr = $_POST['Jahr'];
$ID_KAT = $_POST['ID_KAT'];


$SQL_KAT = "SELECT * FROM m_them_kategorie_freigabe,m_thematische_kategorien 
			WHERE m_them_kategorie_freigabe.ID_THEMA_KAT = m_thematische_kategorien.ID_THEMA_KAT 
			AND m_thematische_kategorien.ID_THEMA_KAT = '".$ID_KAT."' 
			AND m_them_kategorie_freigabe.JAHR = '".$Jahr."' ";
$Ergebnis_KAT = mysqli_query($Verbindung,$SQL_KAT);


$Kat = mysqli_result($Ergebnis_KAT,0,'ID_THEMA_KAT');
$Kat_Name = utf8_encode(mysqli_result($Ergebnis_KAT,0,'THEMA_KAT_NAME'));
$Kat_Name_EN = utf8_encode(mysqli_result($Ergebnis_KAT,0,'THEMA_KAT_NAME_EN'));

?>
<br />
<h2>Freigabe der Kategorie <?php echo $ID_KAT." = ".$Kat_Name; ?> für das Jahr <?php echo $Jahr; ?></h2>    
    <form action="pruefung_u_rechte_uebersicht.php" method="get">
        <input name="Aufklapp" type="hidden" value="<?php echo $Aufklapp; ?>" />
        <input type="submit" value="&lt; Zurück" class="button_standard_abschicken_a" style="padding-left:5px; padding-right:5px; background: #B7CDA0" />
        <br />
        <br />
</form>
    <div style="margin-left:0px; padding:10px; border:#666666 solid 1px; background:#FFFFFF; width:800px;">
    <form action="" method="post">
    <div class="graue_Box">	<strong>F&uuml;r Gesamten Datenbestand gültig:</strong><br />
        <br />
        ID (für alle Jahre gültig)<br />
        <input name="ID_KAT" type="text" id="ID_KAT" value="<?php echo $Kat; ?>" style="width:200px;" />
        <br />
        <br />
        Name (für alle Jahre gültig)<br />
        <input name="NAME" type="text" id="NAME" value="<?php echo $Kat_Name; ?>" size="90" maxlength="254" />
        <br />
        <br />
        Name EN<br />
        <input name="NAME_EN" type="text" id="NAME_EN" value="<?php echo $Kat_Name_EN; ?>" size="90" maxlength="254" />
        <br />
        <br />
        <input name="input3" type="submit" value="Speichern" class="button_standard_abschicken_a" />
    <br />
        <br />
    </div>
    <br />
    <div class="graue_Box"><strong>Für den Jahres-Datenbestand <?php echo $Jahr; ?> g&uuml;ltig:</strong><br />
      <br />
      Pr&uuml;fstatus:<br />
      
      
      <select name="STATUS_PRUEFUNG" style="width:200px;">
        <?php 
            $SQL_Rechte = "SELECT * FROM m_status_pruefung ORDER BY STATUS_PRUEFUNG DESC";
            $Ergebnis_Rechte = mysqli_query($Verbindung,$SQL_Rechte);
            $i_re = 0;
            while(@mysqli_result($Ergebnis_Rechte,$i_re,'STATUS_PRUEFUNG_NAME'))
            {
                ?>
        <option value="<?php echo mysqli_result($Ergebnis_Rechte,$i_re,'STATUS_PRUEFUNG'); ?>" <?php 
                                        if(mysqli_result($Ergebnis_KAT,0,'STATUS_KATEGORIE_PRUEFG') == mysqli_result($Ergebnis_Rechte,$i_re,'STATUS_PRUEFUNG')) 
                                        {
                                            echo 'selected="selected"'; 
                                            $Akt_Pruefung_Beschreibg = utf8_encode(mysqli_result($Ergebnis_Rechte,$i_re,'STATUS_PRUEFUNG_BESCHREIBUNG'));
											$Akt_Pruefung = mysqli_result($Ergebnis_Rechte,$i_re,'STATUS_PRUEFUNG');
                                        }
                                ?>><?php echo utf8_encode(mysqli_result($Ergebnis_Rechte,$i_re,'STATUS_PRUEFUNG_NAME')); ?></option>
        <?php
                $i_re++; 
             }
             ?>
      </select>
      gesetzt: <span style="padding-left:5px; padding-right:5px; border:solid 1px #999999;"><?php echo $Akt_Pruefung_Beschreibg; ?></span>
      <br />
      <br />
      Datum der Pr&uuml;fung:<br />
      <input type="text" name="PruefDatum" id="PruefDatum" value="<?php echo $Datum_Pruef = mysqli_result($Ergebnis_KAT,0,'DATUM_PRUEFUNG'); ?>"  style="width:200px;" />
      <br />
      <br />
      gepr&uuml;ft durch:<br />
      <input type="text" name="Pruefer" id="Pruefer" value="<?php echo $Pruefer_name = utf8_encode(mysqli_result($Ergebnis_KAT,0,'PRUEFER')); ?>"  style="width:200px;" />
      <br />
      <br />
      Bemerkungen zur Pr&uuml;fung (255 Zeichen):<br />
      <textarea name="Bemerkg" cols="70" rows="5" wrap="off" id="Bemerkg"><?php echo utf8_encode(mysqli_result($Ergebnis_KAT,0,'STATUS_KATEGORIE_PRUEFG_BEMERKUNG')); ?></textarea>
      <br />
      <br />
      <input name="input2" type="submit" value="Speichern" class="button_standard_abschicken_a" />
      <br />
      <br />
      Freigabe:<br />
      <select name="STATUS_FREIGABE" id="STATUS_FREIGABE" style="width:200px;" >
        <?php 
			// nur Freigeben wenn geprüft:
			if($Akt_Pruefung == "3" and $Datum_Pruef and $Pruefer_name)
			{
							 
				$SQL_Rechte = "SELECT * FROM m_status_freigabe ORDER BY STATUS_FREIGABE DESC";
				$Ergebnis_Rechte = mysqli_query($Verbindung,$SQL_Rechte);
				$i_re = 0;
				while(@mysqli_result($Ergebnis_Rechte,$i_re,'STATUS_NAME'))
				{
					?>
							<option value="<?php echo mysqli_result($Ergebnis_Rechte,$i_re,'STATUS_FREIGABE'); ?>" <?php 
								if(mysqli_result($Ergebnis_KAT,0,'STATUS_KATEGORIE_FREIGABE') == mysqli_result($Ergebnis_Rechte,$i_re,'STATUS_FREIGABE')) 
								{
									echo 'selected="selected"'; 
									$Akt_Freigabe_Beschreibg = utf8_encode(mysqli_result($Ergebnis_Rechte,$i_re,'STATUS_FREIGABE_BESCHREIBUNG'));
									$FC_selected = mysqli_result($Ergebnis_Rechte,$i_re,'STATUS_FARBCODE');
								}
								?> style=" background-color:#<?php echo $FC = mysqli_result($Ergebnis_Rechte,$i_re,'STATUS_FARBCODE'); ?>"><?php echo utf8_encode(mysqli_result($Ergebnis_Rechte,$i_re,'STATUS_NAME')); ?>
	    </option>
					<?php
					$i_re++; 
				 }
			}
			else
			{
				?><option value="0" style=" background-color:#990000">Datenpr&uuml;fung ung&uuml;ltig!</option>
				<?php 
			}
            ?>
      </select> 
      <!--<script type="text/javascript">
            document.getElementById("STATUS_FREIGABE").style.background='#<?php echo $FC_selected; ?>';
        </script> -->
      
      gesetzt: <span style="background:#<?php echo $FC_selected; ?>; padding-left:5px; padding-right:5px; border:solid 1px #999999;"><?php echo $Akt_Freigabe_Beschreibg; ?></span><br />
      <br />
      <input name="input" type="submit" value="Speichern" class="button_standard_abschicken_a" />
      <br />
    </div>
    <br />
    
        <input name="Aufklapp" type="hidden" value="<?php echo $Aufklapp; ?>" />
        <input name="aktion" type="hidden" value="update" />
        <input name="Jahr" type="hidden" value="<?php echo $Jahr; ?>" />
        <input name="ID_KAT_org" type="hidden" value="<?php echo $ID_KAT; ?>" />
  </form>
</div>

<form action="pruefung_u_rechte_uebersicht.php" method="get">
    <input name="Aufklapp" type="hidden" value="<?php echo $Aufklapp; ?>" />
    <input name="" type="submit" value="&lt; Zurück" class="button_standard_abschicken_a" style="padding-left:5px; padding-right:5px; background: #B7CDA0" />
</form>
</body>
</html>
