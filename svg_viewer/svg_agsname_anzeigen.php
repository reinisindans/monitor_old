<?php 
session_start(); 

// setzt die Anzeigeoption für alle Labels die angeklickt wurden im Array

$AGS = $_GET['ags'];
$Sichtbarkeit = $_GET['visible'];

$temp = strtok($AGS,"_");
$AGS_sauber = strtok("_");

$_SESSION['Dokument']['LabelAnzeige'][$AGS_sauber] = $Sichtbarkeit;
$_SESSION['Tabelle']['Markierung'][$AGS_sauber] = $Sichtbarkeit;

?>