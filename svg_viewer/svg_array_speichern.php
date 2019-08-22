<?php 
session_start();
include("includes_classes/verbindung_mysqli.php");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Unbenanntes Dokument</title>
<link href="screen_viewer.css" rel="stylesheet" type="text/css" />

</head>

<body style="padding-left:40px;">
<?php 

$ArraySpeicher = serialize($_SESSION); // benötigt Pecl-Erweiterungsfunktionen !!!
echo $ArraySpeicher;
?>
<br />
<br />
<?php 
$Testarray = unserialize($ArraySpeicher);
print_r($Testarray);


?>
</body>
</html>
