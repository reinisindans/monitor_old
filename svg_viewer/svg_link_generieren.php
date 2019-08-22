<?php 
session_start();
include("includes_classes/verbindung_mysqli.php");
include("includes_classes/implode_explode.php");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta charset="UTF-8" />
<title>Unbenanntes Dokument</title>
<link href="screen_viewer.css" rel="stylesheet" type="text/css" />
</head>
<body style="padding-left:40px;">
<p>
<?php 

// SESSION in normales Array schreiben
$ArraySpeicher = $_SESSION;

// Löschen leerer Raumeinheiten (kleinerer Datensatz)
if(is_array($ArraySpeicher['Datenbestand']))
{
	// Alle Anzeigen auf 0 setzen
	foreach($ArraySpeicher['Datenbestand'] as $DatenSet)
	{
		// HG ausklammern
		if($ArraySpeicher['Datenbestand'][$DatenSet['NAME']]['View'] == '0')
		{
			unset($ArraySpeicher['Datenbestand'][$DatenSet['NAME']]);
		}
	}
}

$ArraySpeicher = serialize($ArraySpeicher); // benötigt Pecl-Erweiterungsfunktionen !!!

//$ArraySpeicher = implodeMDA($ArraySpeicher,'|');

//$ArraySpeicher = utf8_encode($ArraySpeicher);

// neuen User-Speicherstand in DB einfügen
$SQL_id_INSERT = "INSERT INTO v_user_link_speicher (array_value) VALUES ('".$ArraySpeicher."')";
$Ergebnis_id_INSERT = mysqli_query($Verbindung,$SQL_id_INSERT);

// Letzte auto-increment ID ermitteln
$ID = mysqli_insert_id($Verbindung);

 //echo $ArraySpeicher."<br /><br />";
//print_r($_SESSION);
//echo "<br /><br />"; 
// Test: $_SESSION = unserialize($ArraySpeicher); ... ok!

if($_SESSION['Dokument']['Sprache'] == 'DE')
{
?>
  <br />
  
  <br />
  <img src="icons_viewer/save.png" width="69" height="71" alt="Karte gesichert" /><br />
  Ihre Karte wurde gesichert und ist dauerhaft verfügbar.<br />
  <br />
  Sie können die Karte im Viewer jeder Zeit unter Angabe der Kartennummer <strong><?php echo $ID; ?></strong>  oder<br />
  über folgenden Link direkt aufrufen:<br />
  <br />
        <a href="http://www.ioer-monitor.de/index.php?id=8&idk=<?php echo $ID; ?>" target="_top">http://www.ioer-monitor.de/index.php?id=8&idk=<?php echo $ID; ?></a></p>
   
   
    <p>Kartenlink versenden: 
   
   <a 
style="background-color:#BDDDFD; padding-left:12px; padding-right:12px; color:#333; text-decoration:none;" class="button_standard_abschicken_a"
href="mailto:?subject=Karte des Monitor der Siedlungs und Freiraumentwicklung (www.ioer-monitor.de)&body=Sehr geehrte Damen und Herren,%0AIhnen wird in dieser Email ein Link zu einer interaktiven Karte des Monitors der Siedlungs- und Freiraumentwicklung des Leibniz-Instituts für ökologische Raumentwicklung (http://www.ioer.de) gesendet.%0A%0ABitte nutzen Sie auf folgenden Weblink, um die Karte aufzurufen: http://www.ioer-monitor.de/index.php?id=8&idk=<?php echo $ID; ?>%0A%0AMit freundlichen Grüßen%0A%0A">E-Mail</a>
  
</a>   
 
  <br />
  <br />
  <div style="float:left; margin-right:10px; padding-top:15px;"><a href="http://www.ioer-monitor.de/index.php?id=5" target="_top"><img src="icons_viewer/back.png" alt="Zur&uuml;ck" /><br />&nbsp;zur&uuml;ck</a></div>
  <!--<a href="svg_html.php" target="_self">
  <input name="Button-Zeichenvorschrift" class="button_standard_abschicken" type="button" value="zurück zur Karte" />   
  </a> --></p>

<?php 
}


if($_SESSION['Dokument']['Sprache'] == 'EN')
{
?>
  <br />
  
  <br />
  <img src="icons_viewer/save.png" width="69" height="71" alt="map saved" /><br />
 Your map has been successfully stored in our database. <br />
  <br />
  You can load this map with the ID number <strong><?php echo $ID; ?></strong>  or<br />
  use this link to open the viewer directly:<br />
  <br />
    <a href="http://www.ioer-monitor.de/index.php?id=8&L=1&idk=<?php echo $ID; ?>" target="_top">http://www.ioer-monitor.de/index.php?id=8&L=1&idk=<?php echo $ID; ?></a></p>
   <p>Send map link per email: 
    <a 
        style="background-color:#BDDDFD; padding-left:12px; padding-right:12px; color:#333; text-decoration:none;" class="button_standard_abschicken_a"
        href="mailto:?subject=Map from the Monitor of Settlement and Open Space Development (www.ioer-monitor.de/home/?L=1)&body=Dear Sir or Madam,%0AWith this E-Mail you are receiving a link to an interactive map from the Monitor of Settlement and Open Space Development provided by the Leibniz Institute of Ecological Urban and Regional Development (http://www.ioer-monitor.de/home/?L=1).%0A%0APlease use the following hyperlink to open the map: www.ioer-monitor.de/index.php?id=8&L=1&idk=<?php echo $ID; ?>%0A%0ASincerely yours%0A%0A">E-Mail</a>
  <br /> 
  
  
  
  <br />
  <br />

  <div style="float:left; margin-right:10px; padding-top:15px;"><a href="http://www.ioer-monitor.de/index.php?id=5&L=1" target="_top"><img src="icons_viewer/back.png" alt="back" /><br />&nbsp;go back</a></div>
  <!--<a href="svg_html.php" target="_self">
  <input name="Button-Zeichenvorschrift" class="button_standard_abschicken" type="button" value="go back to the map" />
</a> --></p>

<?php 
}
?>
</body>
</html>
