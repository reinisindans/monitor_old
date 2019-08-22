<?php 
session_start();
include("includes_classes/verbindung_mysqli.php");

// Setzen des Wertebasis-Parameters
if($_POST['wertebasis'] == "reg") 
{
	$_SESSION['Dokument']['indikator_lokal'] = '1';
}

if($_POST['wertebasis'] == "deu") 
{
	$_SESSION['Dokument']['indikator_lokal'] = '0';
	$_SESSION['Dokument']['Fuellung']['Typ'] = 'Farbbereich'; // autom. Klassifizierung technisch schwierig und nicht sinnvoll
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
<div style="border: #999999 solid 1px; padding:10px; ">  <strong>Einstellungen bezüglich der Werteverteilung</strong><br />
  <br />
        <table style="width:800px; border:0px; border-collapse:collapse;">
            
                <tr>
                  <td width="186" rowspan="2" valign="top" class="">Regionales oder <br />
                    deutschlandweites Wertespektrum<br />
                    <br /></td>
                  <td valign="top" class=""><p>Bestimmen Sie hier, auf welcher Werte-Basis die Farbgebung Ihrer 
                    Karte basieren soll.<br />
                    <br />
                    Möchten Sie, dass nur die Werte Ihres gewählten Kartenausschnitts 
                    berücksichtigt werden, dann aktivieren Sie bitte folgenden Schalter:<br />
                    <form action="svg_zeichenvorschrift_lok_glob.php" method="post">
                      <input name="input2" type="submit" value="Regionales Wertespektrum" class="button_standard_abschicken" <?php 
                                                           if($_SESSION['Dokument']['indikator_lokal'] == '1') echo 'style="background:#BAD380;"';?> />
                        <input type="hidden" name="wertebasis" id="Lokale_Werte_gesendet"  value="reg" />
                    </form>
                    <br />
                    Möchten Sie jedoch eine Vergleichbarkeit von Karten verschiedener
                    Regionen sicherstellen, dann aktivieren Sie bitte folgenden Schalter:<br />
                    <br />
                     <form action="svg_zeichenvorschrift_lok_glob.php" method="post">
                         <input name="input2" type="submit" value="Deutschlandweites Wertespektrum" class="button_standard_abschicken" <?php 
                                                           if($_SESSION['Dokument']['indikator_lokal'] == '0') echo 'style="background:#BAD380;"';?> />
                         <input type="hidden" name="wertebasis" id="Lokale_Werte_gesendet"  value="deu" />
                     </form>
                  <br />
                  </p></td>
                </tr>
                <tr>
                  <td width="267" valign="top" class="">
                      <br />
                      <br />
                      <br />
                  </td>
                </tr>
              <tr>
                <td height="67">
                <!--<span class="button_standard_abschicken_a" style="background:#BAD380;"><a href="svg_html.php#top" target="_self">&nbsp;&nbsp;&nbsp;&lt;= Zurück zur Karte&nbsp;&nbsp;&nbsp;</a></span> -->
                <div style="float:left; margin-right:10px; padding-top:15px;">
                    	<a href="svg_html.php#top" target="_self"><img src="icons_viewer/back.png" alt="Zur&uuml;ck" /><br />
                    	zur&uuml;ck</a>
                </div>
                </td>
				<td>&nbsp;</td>
              </tr>
          
        </table>
        
    
  <br />
 </div>
<br />
<br />

</body>
</html>
