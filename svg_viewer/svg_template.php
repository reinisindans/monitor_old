<?php 
session_start();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Testviewer</title>
<link href="screen.css" rel="stylesheet" type="text/css" />
</head>

<body>
<div id="Kopf_links"><a href="http://www.ioer.de" target="_blank"><img style="border:0px;" src="gfx/kopf_links.png" alt="http://www.ioer.de" /></a></div>
<div id="Kopf_rechts"></div>
<div id="MENUE_E1_Rahmen">
  <div class="MENUE_E1_Trennstrich"></div>
	<div class="MENUE_E1_inaktiv">Startseite</div>
	<div class="MENUE_E1_Trennstrich"></div>
	<div class="MENUE_E1_inaktiv">Kennzahlen</div>
    <div class="MENUE_E1_Trennstrich"></div>
	<div class="MENUE_E1_aktiv">Karten</div>
    <div class="MENUE_E1_Trennstrich"></div>
	<div class="MENUE_E1_inaktiv">Tabellen,<br />Text, Grafik</div>
  	<div class="MENUE_E1_Trennstrich"></div>
	<div class="MENUE_E1_inaktiv">Infos <br />zur Website</div>
  	<div class="MENUE_E1_Trennstrich"></div>
</div>
<div id="MENUE_E2_Rahmen">
  <div class="MENUE_E2_Trennstrich"></div>
	<div class="MENUE_E2_inaktiv">Quick-Karten-Viewer</div>
  <div class="MENUE_E2_Trennstrich"></div>
	<div class="MENUE_E2_inaktiv">Karten-Viewer für Experten</div>
  <div class="MENUE_E2_Trennstrich"></div>
	<div class="MENUE_E2_aktiv">weitere Daten</div>
    <div class="MENUE_E2_Trennstrich"></div>
</div>
<div id="Content_Rahmen">
	<iframe src="svg_html.php" style="width:995px; height:2000px; border:0px; margin-left:2px;" scrolling="no" frameborder="0" ></iframe>
</div>
</body>
</html>
