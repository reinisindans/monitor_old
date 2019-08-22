<?php 
session_start(); // Sitzung starten/ wieder aufnehmen

// Spracheinstellung
// $_SESSION['Dokument']['Sprache'] = '';
if(!$_SESSION['Dokument']['Sprache']) $_SESSION['Dokument']['Sprache'] = 'DE';
if($_GET['lang']) $_SESSION['Dokument']['Sprache'] = $_GET['lang'];

?>