<?php

$file= $_GET['dateiname'];
header("Content-Type: application/csv");
header("Content-Length: ".filesize($file));
header("Content-Transfer-Encoding: utf-8"); 
header("Accept-Ranges: bytes");
header("Pragma: no-cache");
header("Expires: 0");  
header("Content-Disposition: attachment; filename=IOER-Monitor-Tabelle.csv");
readfile($_GET['dateiname']);
?>