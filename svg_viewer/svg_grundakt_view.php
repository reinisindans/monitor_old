<?php 
session_start(); // Sitzung starten/ wieder aufnehmen


include("includes_classes/verbindung_mysqli.php");
include("includes_classes/implode_explode.php");

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>IÖR Monitor</title>
<link href="screen_viewer.css" rel="stylesheet" type="text/css" media="screen" />
<link href="print_viewer.css" rel="stylesheet" type="text/css" media="print" />
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
body {
	background-color: #FFFFFF;
	padding: 20px;
}
-->
</style>
</head>
<body>
<h2>Übersicht über die Grundaktualität 
der verwendeten Daten <br />
für das Jahr <?php echo $_SESSION['Dokument']['Jahr_Anzeige']; ?></h2>

<img src="gfx/grundaktualitaet/<?php echo $_SESSION['Dokument']['Jahr_Anzeige']; ?>_655px.png" alt="Grundaktualit&uuml;t" />
</body>
</html>
