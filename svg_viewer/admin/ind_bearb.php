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

	// Checkboxen auswerten
	 
	if($_POST['BRD']){ $Schalter_BRD = 1;}else{$Schalter_BRD = 0;}
	if($_POST['BLD']){ $Schalter_BLD = 1; }else{$Schalter_BLD = 0;}
	if($_POST['KRS']) {$Schalter_KRS = 1;}else{$Schalter_KRS = 0;}
	if($_POST['GEM']){ $Schalter_GEM = 1;}else{$Schalter_GEM = 0;}
	if($_POST['PLR']){ $Schalter_PLR = 1; }else{$Schalter_PLR = 0;}// nicht verwendet z.Z.
	if($_POST['ROR']){ $Schalter_ROR = 1;}else{$Schalter_ROR = 0;}
if($_POST['GMK']) {$Schalter_GMK = 1;}else{$Schalter_GMK = 0;}
	if($_POST['R10']) {$Schalter_R10 = 1;}else{$Schalter_R10 = 0;}
	if($_POST['RST']){ $Schalter_RST = 1;}else{$Schalter_RST = 0;}
	if($_POST['G50']) {$Schalter_G50 = 1;}else{$Schalter_G50 = 0;}
	if($_POST['STT']) {$Schalter_STT = 1; }else{$Schalter_STT = 0;}
		if($_POST['VWG']) {$Schalter_VWG = 1; }else{$Schalter_VWG = 0;}

	if($_POST['R05']) {$Schalter_R05 = 1;}else{$Schalter_R05 = 0;}
	if($_POST['R5M']){ $Schalter_R5M = 1;}else{$Schalter_R5M = 0;}
	if($_POST['R2M']) {$Schalter_R2M = 1;}else{$Schalter_R2M = 0;}
	if($_POST['R1M']){ $Schalter_R1M = 1;}  else{$Schalter_R1M = 0;}
	
	if($_POST['WMS']) {$Schalter_WMS = 1;}else{$Schalter_WMS = 0;}
if($_POST['WCS']) {$Schalter_WCS = 1;}else{$Schalter_WCS = 0;}
if($_POST['WFS']) {$Schalter_WFS = 1;}else{$Schalter_WFS = 0;}
	
if($_POST['DATENGRUNDLAGE_ATKIS']) {$Schalter_ATKIS = 1;}else{$Schalter_WFS = 0;}
	if($_POST['MITTLERE_AKTUALITAET_IGNORE']) {$Schalter_Mitt = 1;}else{$Schalter_Mitt = 0;}
	
	/*Vorbereiten aller Var*/
	$u_ID_INDIKATOR = $_POST['ID_IND'];
	$u_EINHEIT = utf8_decode($_POST['Einheit']);
	$u_EINHEIT_EN = utf8_decode($_POST['Einheit_EN']);
	$u_INDIKATOR_NAME = utf8_decode($_POST['NAME']);
	$u_INDIKATOR_NAME_EN = utf8_decode($_POST['NAME_EN']);
	$u_ID_THEMA_KAT = $_POST['ID_THEMA_KAT'];	
	
	$u_RUNDUNG_NACHKOMMASTELLEN = $_POST['RUNDUNG_NACHKOMMASTELLEN'];	
	$u_ZEITSCHNITTE = utf8_decode($_POST['ZEITSCHNITTE']);

	$u_BEDEUTUNG_INTERPRETATION =utf8_decode($_POST['BEDEUTUNG_INTERPRETATION']);
	$u_BEDEUTUNG_INTERPRETATION_EN = utf8_decode($_POST['BEDEUTUNG_INTERPRETATION_EN']);
	$u_INFO_VIEWER_ZEILE_1 = utf8_decode($_POST['INFO_VIEWER_ZEILE_1']);
	$u_INFO_VIEWER_ZEILE_1_EN = utf8_decode($_POST['INFO_VIEWER_ZEILE_1_EN']);
	$u_INFO_VIEWER_ZEILE_2 = utf8_decode($_POST['INFO_VIEWER_ZEILE_2']);
	$u_INFO_VIEWER_ZEILE_2_EN =utf8_decode($_POST['INFO_VIEWER_ZEILE_2_EN']);
	$u_INFO_VIEWER_ZEILE_3 = utf8_decode($_POST['INFO_VIEWER_ZEILE_3']);
	$u_INFO_VIEWER_ZEILE_3_EN = utf8_decode($_POST['INFO_VIEWER_ZEILE_3_EN']);
	$u_INFO_VIEWER_ZEILE_4 = utf8_decode($_POST['INFO_VIEWER_ZEILE_4']);
	$u_INFO_VIEWER_ZEILE_4_EN = utf8_decode($_POST['INFO_VIEWER_ZEILE_4_EN']);
	$u_INFO_VIEWER_ZEILE_5 = utf8_decode($_POST['INFO_VIEWER_ZEILE_5']);
	$u_INFO_VIEWER_ZEILE_5_EN = utf8_decode($_POST['INFO_VIEWER_ZEILE_5_EN']);
	$u_INFO_VIEWER_ZEILE_6 = utf8_decode($_POST['INFO_VIEWER_ZEILE_6']);
	$u_INFO_VIEWER_ZEILE_6_EN =utf8_decode($_POST['INFO_VIEWER_ZEILE_6_EN']);
	
	$u_DATENGRUNDLAGE_ZEILE_1 = utf8_decode($_POST['DATENGRUNDLAGE_ZEILE_1']);
	$u_DATENGRUNDLAGE_ZEILE_1_EN = utf8_decode($_POST['DATENGRUNDLAGE_ZEILE_1_EN']);
	$u_DATENGRUNDLAGE_ZEILE_2 = utf8_decode($_POST['DATENGRUNDLAGE_ZEILE_2']);
	$u_DATENGRUNDLAGE_ZEILE_2_EN = utf8_decode($_POST['DATENGRUNDLAGE_ZEILE_2_EN']);
	$u_METHODIK = utf8_decode($_POST['METHODENBESCHREIBUNG']);
	$u_METHODIK_EN =utf8_decode($_POST['METHODIK_EN']);
	$u_VERWEISE = utf8_decode($_POST['VERWEISE']);
	$u_VERWEISE_EN = utf8_decode($_POST['VERWEISE_EN']);
	$u_LITERATUR = utf8_decode($_POST['LITERATUR']);
	$u_LITERATUR_EN =utf8_decode($_POST['LITERATUR_EN']);
	$u_BERECHNUNG = utf8_decode($_POST['BERECHNUNG']);
	$u_BERECHNUNG_EN = utf8_decode($_POST['BERECHNUNG_EN']);
	$u_BEMERKUNGEN = utf8_decode($_POST['BEMERKUNGEN']);
	$u_BEMERKUNGEN_EN = utf8_decode($_POST['BEMERKUNGEN_EN']);
	
	$u_org_INDIKATOR = $_POST['ID_IND_org'];
	

	
		//Update von m_indikatoren. geteilte Abfrage mit stmt gegen SQL-Injection
	$SQL_UPDATE_2 = "UPDATE m_indikatoren SET 	
		ID_INDIKATOR = ?,
		EINHEIT = ?,
		EINHEIT_EN = ?,
		INDIKATOR_NAME = ?,
		INDIKATOR_NAME_EN = ?,
		ID_THEMA_KAT = ?,	
		RUNDUNG_NACHKOMMASTELLEN =?,	
		ZEITSCHNITTE = ?,
		BEDEUTUNG_INTERPRETATION = ?,
		BEDEUTUNG_INTERPRETATION_EN = ?,
		INFO_VIEWER_ZEILE_1 = ?,
		INFO_VIEWER_ZEILE_1_EN = ?,
		INFO_VIEWER_ZEILE_2 = ?,
		INFO_VIEWER_ZEILE_2_EN = ?,
		INFO_VIEWER_ZEILE_3 = ?,
		INFO_VIEWER_ZEILE_3_EN = ?,
		INFO_VIEWER_ZEILE_4 = ?,
		INFO_VIEWER_ZEILE_4_EN = ?,
		INFO_VIEWER_ZEILE_5 = ?,
		INFO_VIEWER_ZEILE_5_EN = ?,
		INFO_VIEWER_ZEILE_6 = ?,
		INFO_VIEWER_ZEILE_6_EN = ?,	
		DATENGRUNDLAGE_ZEILE_1 = ?,
		DATENGRUNDLAGE_ZEILE_1_EN = ?,
		DATENGRUNDLAGE_ZEILE_2 = ?,
		DATENGRUNDLAGE_ZEILE_2_EN = ?,
		METHODIK = ?,
		METHODIK_EN = ?,
		VERWEISE = ?,
		VERWEISE_EN = ?,
		LITERATUR = ?,
		LITERATUR_EN = ?,
		BERECHNUNG =?,
		BERECHNUNG_EN = ?,
		BEMERKUNGEN = ?,
		BEMERKUNGEN_EN =?, 		
	MITTLERE_AKTUALITAET_IGNORE = ?,
	DATENGRUNDLAGE_ATKIS  = ?,
	RAUMEBENE_BRD = ?,
	RAUMEBENE_BLD = ?,
	RAUMEBENE_KRS =?,
	RAUMEBENE_GEM = ?,
	RAUMEBENE_ROR = ?,
	RAUMEBENE_PLR = ?,
	RAUMEBENE_GMK = ?,
	RAUMEBENE_RST = ?,
	RAUMEBENE_R10 = ?,
	RAUMEBENE_G50 = ?,
  RAUMEBENE_STT = ?,
  RAUMEBENE_VWG = ?,
	RAUMEBENE_R05 = ?,
	RAUMEBENE_R5M = ?,
	RAUMEBENE_R2M = ?,
	RAUMEBENE_R1M = ?,
  WMS = ?,
  WCS = ?,
		WFS =?
 WHERE ID_INDIKATOR = ? "; /* create a prepared statement */


	if ($stmt   = mysqli_prepare ($Verbindung, $SQL_UPDATE_2)){/* create a prepared statement */
	 /* bind parameters for markers */
    mysqli_stmt_bind_param($stmt, "ssssssisssssssssssssssssssssssssssssiiiiiiiiiiiiiiiiiiiiis", 
    	$u_ID_INDIKATOR, 	$u_EINHEIT,
	$u_EINHEIT_EN,	$u_INDIKATOR_NAME, $u_INDIKATOR_NAME_EN, $u_ID_THEMA_KAT, $u_RUNDUNG_NACHKOMMASTELLEN,
	$u_ZEITSCHNITTE,
	$u_BEDEUTUNG_INTERPRETATION,
	$u_BEDEUTUNG_INTERPRETATION_EN,
	$u_INFO_VIEWER_ZEILE_1,
	$u_INFO_VIEWER_ZEILE_1_EN,
	$u_INFO_VIEWER_ZEILE_2,
	$u_INFO_VIEWER_ZEILE_2_EN,
	$u_INFO_VIEWER_ZEILE_3,
	$u_INFO_VIEWER_ZEILE_3_EN,
	$u_INFO_VIEWER_ZEILE_4,
	$u_INFO_VIEWER_ZEILE_4_EN,
	$u_INFO_VIEWER_ZEILE_5,
	$u_INFO_VIEWER_ZEILE_5_EN,
	$u_INFO_VIEWER_ZEILE_6,
	$u_INFO_VIEWER_ZEILE_6_EN,	
	$u_DATENGRUNDLAGE_ZEILE_1,
	$u_DATENGRUNDLAGE_ZEILE_1_EN,
	$u_DATENGRUNDLAGE_ZEILE_2,
	$u_DATENGRUNDLAGE_ZEILE_2_EN,
	$u_METHODIK,
	$u_METHODIK_EN,
	$u_VERWEISE,
	$u_VERWEISE_EN,
	$u_LITERATUR,
	$u_LITERATUR_EN,
	$u_BERECHNUNG, 
	$u_BERECHNUNG_EN, 
	$u_BEMERKUNGEN,
	$u_BEMERKUNGEN_EN, 	
	$Schalter_Mitt,
	$Schalter_ATKIS,
	$Schalter_BRD,
	$Schalter_BLD,
	$Schalter_KRS,
	$Schalter_GEM,
	$Schalter_ROR,
	$Schalter_PLR,
	$Schalter_GMK,
	$Schalter_RST,
	$Schalter_R10,
	$Schalter_G50,
  $Schalter_STT,
  $Schalter_VWG,
	$Schalter_R05,
	$Schalter_R5M,
	$Schalter_R2M,
	$Schalter_R1M,
  $Schalter_WMS,
  $Schalter_WCS,
	$Schalter_WFS,
	$u_org_INDIKATOR);
    
    /* execute query */
    mysqli_stmt_execute($stmt);

    /* bind result variables */
    mysqli_stmt_bind_result($stmt, $ergebn);

    /* fetch value */
    mysqli_stmt_fetch($stmt);

    
    /* close statement */
    mysqli_stmt_close($stmt);

 echo "Update erfolgreich für Bereich Gesamter Indikator";
    echo"<br/>";
    
} else {
    echo "ERROR: Could not execute $SQL_UPDATE_2. " . mysqli_error($Verbindung);
}
	
	

   

	/*geändert am 13.10.2017
	//Update von m_indikatoren. Leere Veriablen mit IF abgefangen und ausgewertet, damit Update erfolgen kann (php7)
	$SQL_UPDATE_2 = "UPDATE m_indikatoren 
SET
	ID_INDIKATOR = '".$_POST['ID_IND']."',
	EINHEIT = IF('".$_POST['Einheit']."'='','','".mysqli_real_escape_string($Verbindung,utf8_decode($_POST['Einheit']))."'),
	EINHEIT_EN = IF('".$_POST['Einheit_EN']."'='','','".mysqli_real_escape_string($Verbindung,utf8_decode($_POST['Einheit_EN']))."'),
	INDIKATOR_NAME = IF('".$_POST['NAME']."'='','','".mysqli_real_escape_string($Verbindung,utf8_decode($_POST['NAME']))."'),
	INDIKATOR_NAME_EN = IF('".$_POST['NAME_EN']."'='','','".mysqli_real_escape_string($Verbindung,utf8_decode($_POST['NAME_EN']))."'),
	ID_THEMA_KAT = IF('".$_POST['ID_THEMA_KAT']."'='','','".$_POST['ID_THEMA_KAT']."'),
	
	RUNDUNG_NACHKOMMASTELLEN = IF('".$_POST['RUNDUNG_NACHKOMMASTELLEN']."'='','','".$_POST['RUNDUNG_NACHKOMMASTELLEN']."'),
	
	ZEITSCHNITTE = IF('".$_POST['ZEITSCHNITTE']."'='','','".mysqli_real_escape_string($Verbindung,utf8_decode($_POST['ZEITSCHNITTE']))."'),

	BEDEUTUNG_INTERPRETATION = IF('".$_POST['BEDEUTUNG_INTERPRETATION']."'='','','".mysqli_real_escape_string($Verbindung,utf8_decode($_POST['BEDEUTUNG_INTERPRETATION']))."'),
	BEDEUTUNG_INTERPRETATION_EN = IF('".$_POST['BEDEUTUNG_INTERPRETATION_EN']."'='','','".mysqli_real_escape_string($Verbindung ,utf8_decode($_POST['BEDEUTUNG_INTERPRETATION_EN']))."'),
	INFO_VIEWER_ZEILE_1 = IF('".$_POST['INFO_VIEWER_ZEILE_1']."'='','','".mysqli_real_escape_string($Verbindung,utf8_decode($_POST['INFO_VIEWER_ZEILE_1']))."'),
	INFO_VIEWER_ZEILE_1_EN = IF('".$_POST['INFO_VIEWER_ZEILE_1_EN']."'='','','".mysqli_real_escape_string($Verbindung,utf8_decode($_POST['INFO_VIEWER_ZEILE_1_EN']))."'),
	INFO_VIEWER_ZEILE_2 = IF('".$_POST['INFO_VIEWER_ZEILE_2']."'='','','".mysqli_real_escape_string($Verbindung,utf8_decode($_POST['INFO_VIEWER_ZEILE_2']))."'),
	INFO_VIEWER_ZEILE_2_EN = IF('".$_POST['INFO_VIEWER_ZEILE_2_EN']."'='','','".mysqli_real_escape_string($Verbindung,utf8_decode($_POST['INFO_VIEWER_ZEILE_2_EN']))."'),
	INFO_VIEWER_ZEILE_3 = IF('".$_POST['INFO_VIEWER_ZEILE_3']."'='','','".mysqli_real_escape_string($Verbindung,utf8_decode($_POST['INFO_VIEWER_ZEILE_3']))."'),
	INFO_VIEWER_ZEILE_3_EN = IF('".$_POST['INFO_VIEWER_ZEILE_3_EN']."'='','','".mysqli_real_escape_string($Verbindung,utf8_decode($_POST['INFO_VIEWER_ZEILE_3_EN']))."'),
	INFO_VIEWER_ZEILE_4 = IF('".$_POST['INFO_VIEWER_ZEILE_4']."'='','','".mysqli_real_escape_string($Verbindung,utf8_decode($_POST['INFO_VIEWER_ZEILE_4']))."'),
	INFO_VIEWER_ZEILE_4_EN = IF('".$_POST['INFO_VIEWER_ZEILE_4_EN']."'='','','".mysqli_real_escape_string($Verbindung,utf8_decode($_POST['INFO_VIEWER_ZEILE_4_EN']))."'),
	INFO_VIEWER_ZEILE_5 = IF('".$_POST['INFO_VIEWER_ZEILE_5']."'='','','".mysqli_real_escape_string($Verbindung,utf8_decode($_POST['INFO_VIEWER_ZEILE_5']))."'),
	INFO_VIEWER_ZEILE_5_EN = IF('".$_POST['INFO_VIEWER_ZEILE_5_EN']."'='','','".mysqli_real_escape_string($Verbindung,utf8_decode($_POST['INFO_VIEWER_ZEILE_5_EN']))."'),
	INFO_VIEWER_ZEILE_6 = IF('".$_POST['INFO_VIEWER_ZEILE_6']."'='','','".mysqli_real_escape_string($Verbindung,utf8_decode($_POST['INFO_VIEWER_ZEILE_6']))."'),
	INFO_VIEWER_ZEILE_6_EN = IF('".$_POST['INFO_VIEWER_ZEILE_6_EN']."'='','','".mysqli_real_escape_string($Verbindung,utf8_decode($_POST['INFO_VIEWER_ZEILE_6_EN']))."'),
	
	DATENGRUNDLAGE_ZEILE_1 = IF('".$_POST['DATENGRUNDLAGE_ZEILE_1']."'='','','".mysqli_real_escape_string($Verbindung,utf8_decode($_POST['DATENGRUNDLAGE_ZEILE_1']))."'),
	DATENGRUNDLAGE_ZEILE_1_EN = IF('".$_POST['DATENGRUNDLAGE_ZEILE_1_EN']."'='','','".mysqli_real_escape_string($Verbindung,utf8_decode($_POST['DATENGRUNDLAGE_ZEILE_1_EN']))."'),
	DATENGRUNDLAGE_ZEILE_2 = IF('".$_POST['DATENGRUNDLAGE_ZEILE_2']."'='','','".mysqli_real_escape_string($Verbindung,utf8_decode($_POST['DATENGRUNDLAGE_ZEILE_2']))."'),
	DATENGRUNDLAGE_ZEILE_2_EN = IF('".$_POST['DATENGRUNDLAGE_ZEILE_2_EN']."'='','','".mysqli_real_escape_string($Verbindung,utf8_decode($_POST['DATENGRUNDLAGE_ZEILE_2_EN']))."'),
	METHODIK = IF('".$_POST['METHODENBESCHREIBUNG']."'='','','".mysqli_real_escape_string($Verbindung,utf8_decode($_POST['METHODENBESCHREIBUNG']))."'),
	METHODIK_EN = IF('".$_POST['METHODIK_EN']."'='','','".mysqli_real_escape_string($Verbindung,utf8_decode($_POST['METHODIK_EN']))."'),
	VERWEISE = IF('".$_POST['VERWEISE']."'='','','".mysqli_real_escape_string($Verbindung,utf8_decode($_POST['VERWEISE']))."'),
	VERWEISE_EN = IF('".$_POST['VERWEISE_EN']."'='','','".mysqli_real_escape_string($Verbindung,utf8_decode($_POST['VERWEISE_EN']))."'),
	LITERATUR = IF('".$_POST['LITERATUR']."'='','','".mysqli_real_escape_string($Verbindung,utf8_decode($_POST['LITERATUR']))."'),
	LITERATUR_EN = IF('".$_POST['LITERATUR_EN']."'='','','".mysqli_real_escape_string($Verbindung,utf8_decode($_POST['LITERATUR_EN']))."'),
	BERECHNUNG = IF('".$_POST['BERECHNUNG']."'='','','".mysqli_real_escape_string($Verbindung,utf8_decode($_POST['BERECHNUNG']))."'),
	BERECHNUNG_EN = IF('".$_POST['BERECHNUNG_EN']."'='','','".mysqli_real_escape_string($Verbindung,utf8_decode($_POST['BERECHNUNG_EN']))."'),
	BEMERKUNGEN = IF('".$_POST['BEMERKUNGEN']."'='','','".mysqli_real_escape_string($Verbindung,utf8_decode($_POST['BEMERKUNGEN']))."'),
	BEMERKUNGEN_EN = IF('".$_POST['BEMERKUNGEN_EN']."'='','','".mysqli_real_escape_string($Verbindung,utf8_decode($_POST['BEMERKUNGEN_EN']))."'),
	
	
MITTLERE_AKTUALITAET_IGNORE = IF('".$_POST['MITTLERE_AKTUALITAET_IGNORE']."'='','0','".$_POST['MITTLERE_AKTUALITAET_IGNORE']."'),
	
	DATENGRUNDLAGE_ATKIS  = IF('".$Schalter_ATKIS."'='',0,1),
	RAUMEBENE_BRD = IF('".$Schalter_BRD."'='',0,1),
	RAUMEBENE_BLD = IF('".$Schalter_BLD."'='',0,1),
	RAUMEBENE_KRS = IF('".$Schalter_KRS."'='',0,1),
	RAUMEBENE_GEM = IF('".$Schalter_GEM."'='',0,1),
	RAUMEBENE_ROR = IF('".$Schalter_ROR."'='',0,1),
	RAUMEBENE_PLR = IF('".$Schalter_PLR."'='',0,1),
	RAUMEBENE_GMK = IF('".$Schalter_GMK."'='',0,1),
	RAUMEBENE_RST = IF('".$Schalter_RST."'='',0,1),
	RAUMEBENE_R10 = IF('".$Schalter_R10."'='',0,1),
	RAUMEBENE_G50 = IF('".$Schalter_G50."'='',0,1),
  RAUMEBENE_STT = IF('".$Schalter_STT."'='',0,1),
  RAUMEBENE_STT = IF('".$Schalter_VWG."'='',0,1),
	RAUMEBENE_R05 = IF('".$Schalter_R05."'='',0,1),
	RAUMEBENE_R5M = IF('".$Schalter_R5M."'='',0,1),
	RAUMEBENE_R2M = IF('".$Schalter_R2M."'='',0,1),
	RAUMEBENE_R1M = IF('".$Schalter_R1M."'='',0,1),
  WMS = IF('".$Schalter_WMS."'='',0,1),
  WCS = IF('".$Schalter_WCS."'='',0,1),
  WFS = IF('".$Schalter_WFS."'='',0,1),

	
	WHERE ID_INDIKATOR = '".$_POST['ID_IND_org']."' 
	";
	*/
	
	/*geändert August 2017
	// Update der Tabelle m_thematische_kategorien (ID_THEMA_KAT wird automatisch kaskadierend in der DB geändert) 
	
	SET ID_INDIKATOR = '".$_POST['ID_IND']."',

	
	INDIKATOR_NAME = '".mysqli_real_escape_string($Verbindung,utf8_decode($_POST['NAME']))."',
	INDIKATOR_NAME_EN = '".mysqli_real_escape_string($Verbindung,utf8_decode($_POST['NAME_EN']))."',
	ID_THEMA_KAT = '".$_POST['ID_THEMA_KAT']."',
	RUNDUNG_NACHKOMMASTELLEN = '".$_POST['RUNDUNG_NACHKOMMASTELLEN']."',
	ZEITSCHNITTE = '".mysqli_real_escape_string($Verbindung,utf8_decode($_POST['ZEITSCHNITTE']))."',
	MITTLERE_AKTUALITAET_IGNORE = '".$_POST['MITTLERE_AKTUALITAET_IGNORE']."',
	BEDEUTUNG_INTERPRETATION = '".mysqli_real_escape_string($Verbindung,utf8_decode($_POST['BEDEUTUNG_INTERPRETATION']))."',
	BEDEUTUNG_INTERPRETATION_EN = '".mysqli_real_escape_string($Verbindung ,utf8_decode($_POST['BEDEUTUNG_INTERPRETATION_EN']))."',
	INFO_VIEWER_ZEILE_1 = '".mysqli_real_escape_string($Verbindung,utf8_decode($_POST['INFO_VIEWER_ZEILE_1']))."',
	INFO_VIEWER_ZEILE_1_EN = '".mysqli_real_escape_string($Verbindung,utf8_decode($_POST['INFO_VIEWER_ZEILE_1_EN']))."',
	INFO_VIEWER_ZEILE_2 = '".mysqli_real_escape_string($Verbindung,utf8_decode($_POST['INFO_VIEWER_ZEILE_2']))."',
	INFO_VIEWER_ZEILE_2_EN = '".mysqli_real_escape_string($Verbindung,utf8_decode($_POST['INFO_VIEWER_ZEILE_2_EN']))."',
	INFO_VIEWER_ZEILE_3 = '".mysqli_real_escape_string($Verbindung,utf8_decode($_POST['INFO_VIEWER_ZEILE_3']))."',
	INFO_VIEWER_ZEILE_3_EN = '".mysqli_real_escape_string($Verbindung,utf8_decode($_POST['INFO_VIEWER_ZEILE_3_EN']))."',
	INFO_VIEWER_ZEILE_4 = '".mysqli_real_escape_string($Verbindung,utf8_decode($_POST['INFO_VIEWER_ZEILE_4']))."',
	INFO_VIEWER_ZEILE_4_EN = '".mysqli_real_escape_string($Verbindung,utf8_decode($_POST['INFO_VIEWER_ZEILE_4_EN']))."',
	INFO_VIEWER_ZEILE_5 = '".mysqli_real_escape_string($Verbindung,utf8_decode($_POST['INFO_VIEWER_ZEILE_5']))."',
	INFO_VIEWER_ZEILE_5_EN = '".mysqli_real_escape_string($Verbindung,utf8_decode($_POST['INFO_VIEWER_ZEILE_5_EN']))."',
	INFO_VIEWER_ZEILE_6 = '".mysqli_real_escape_string($Verbindung,utf8_decode($_POST['INFO_VIEWER_ZEILE_6']))."',
	INFO_VIEWER_ZEILE_6_EN = '".mysqli_real_escape_string($Verbindung,utf8_decode($_POST['INFO_VIEWER_ZEILE_6_EN']))."',
	
	DATENGRUNDLAGE_ZEILE_1 = '".mysqli_real_escape_string($Verbindung,utf8_decode($_POST['DATENGRUNDLAGE_ZEILE_1']))."',
	DATENGRUNDLAGE_ZEILE_1_EN = '".mysqli_real_escape_string($Verbindung,utf8_decode($_POST['DATENGRUNDLAGE_ZEILE_1_EN']))."',
	DATENGRUNDLAGE_ZEILE_2 = '".mysqli_real_escape_string($Verbindung,utf8_decode($_POST['DATENGRUNDLAGE_ZEILE_2']))."',
	DATENGRUNDLAGE_ZEILE_2_EN = '".mysqli_real_escape_string($Verbindung,utf8_decode($_POST['DATENGRUNDLAGE_ZEILE_2_EN']))."',
	METHODIK = '".mysqli_real_escape_string($Verbindung,utf8_decode($_POST['METHODENBESCHREIBUNG']))."',
	METHODIK_EN = '".mysqli_real_escape_string($Verbindung,utf8_decode($_POST['METHODIK_EN']))."',
	VERWEISE = '".mysqli_real_escape_string($Verbindung,utf8_decode($_POST['VERWEISE']))."',
	VERWEISE_EN = '".mysqli_real_escape_string($Verbindun ,utf8_decode($_POST['VERWEISE_EN']))."',
	LITERATUR = '".mysqli_real_escape_string($Verbindung,utf8_decode($_POST['LITERATUR']))."',
	LITERATUR_EN = '".mysqli_real_escape_string($Verbindung,utf8_decode($_POST['LITERATUR_EN']))."',
	BERECHNUNG = '".mysqli_real_escape_string($Verbindung,utf8_decode($_POST['BERECHNUNG']))."',
	BERECHNUNG_EN = '".mysqli_real_escape_string($Verbindung,utf8_decode($_POST['BERECHNUNG_EN']))."',
	BEMERKUNGEN = '".mysqli_real_escape_string($Verbindung,utf8_decode($_POST['BEMERKUNGEN']))."',
	BEMERKUNGEN_EN = '".mysqli_real_escape_string($Verbindun ,utf8_decode($_POST['BEMERKUNGEN_EN']))."',
	//	$Ergebnis_UPD_2 = mysqli_query($Verbindung,$SQL_UPDATE_2);
//	$Ergebnis_UPD_2 = mysqli_query($Verbindung,$SQL_UPDATE_2);


	*//*
	if(mysqli_query($Verbindung,$SQL_UPDATE_2)){

    echo "Update erfolgreich für Bereich Gesamter Indikator";
    echo"<br/>";
    
} else {
    echo "ERROR: Could not execute $SQL_UPDATE_2. " . mysqli_error($Verbindung);
}
	*/

	
	
		// nur Update benötigt da Datensätze automatisch im Haupttool (=Übersicht) angelegt werden
	// Update der Tabelle m_them_kategorie_freigabe
	$SQL_UPDATE = "UPDATE m_indikator_freigabe 
	SET 
	STATUS_INDIKATOR_PRUEFG = '".$_POST['STATUS_PRUEFUNG']."',
	DATUM_PRUEFUNG = '".$_POST['PruefDatum']."',
	PRUEFER = '".utf8_decode($_POST['Pruefer'])."',
	STATUS_INDIKATOR_PRUEFG_BEMERKUNG = '".mysqli_real_escape_string($Verbindung,utf8_decode($_POST['Bemerkg']))."',
	STATUS_INDIKATOR_FREIGABE = '".$_POST['STATUS_FREIGABE']."', 
	RASTER_FREIGABE = IF('".$_POST['RASTER_FREIGABE']."'='',0,1) 
	WHERE ID_INDIKATOR = '".$_POST['ID_IND']."' 
	AND JAHR = '".$_POST['Jahr']."' 
	";
		if(mysqli_query($Verbindung,$SQL_UPDATE)){

    echo "Update erfolgreich für Bereich Konkretes Jahr";
   
} else {
    echo "ABER im unteren Bereich <br/> ERROR: Could not execute $SQL_UPDATE. " . mysqli_error($Verbindung);
}
	
	$Ergebnis_UPD = mysqli_query($Verbindung,$SQL_UPDATE);
}

// Farbwerte/Klassifizierg übernehmen
if($_POST['aktion'] == "farbwerte")
{
	// UPDATE oder INSERT
	$SQL_DSF_vorh = "SELECT * FROM m_zeichenvorschrift WHERE ID_INDIKATOR = '".$_POST['Ind']."'";
	$Ergebnis_DSF_vorh = mysqli_query($Verbindung,$SQL_DSF_vorh);
	
	if(@mysqli_result($Ergebnis_DSF_vorh,0,'ID_INDIKATOR'))
	{
		$Ergebnis_DSF_UPD = 
						mysqli_query($Verbindung,"UPDATE m_zeichenvorschrift 
						SET 
						TYP_FUELLUNG = '".$_SESSION['Dokument']['Fuellung']['Typ']."',
						UNTERTYP_FUELLUNG = '".$_SESSION['Dokument']['Fuellung']['Untertyp']."',
						FARBWERT_MIN = '".$_SESSION['Dokument']['Fuellung']['Farbwert_min']."',
						FARBWERT_MAX = '".$_SESSION['Dokument']['Fuellung']['Farbwert_max']."',
						FARBWERT_LEER = '".$_SESSION['Dokument']['Fuellung']['LeerFarbe']."',
						FARBWERT_KONTUR = '".$_SESSION['Dokument']['Strichfarbe']."',
						FARBWERT_MOUSEOVER = '".$_SESSION['Dokument']['Strichfarbe_MouseOver']."',
						FARBWERT_TEXT = '".$_SESSION['Dokument']['Textfarbe_Labels']."',
						KLASSEN_AUFLOESUNG = '".$_SESSION['Dokument']['Klassen']['Aufloesung']."'
						WHERE  ID_INDIKATOR = '".$_POST['Ind']."'"
						);	
						
					
   

	}
	else
	{
		$SQL_DSF_INS = "INSERT INTO m_zeichenvorschrift 
						(ID_INDIKATOR,TYP_FUELLUNG,UNTERTYP_FUELLUNG,FARBWERT_MIN,FARBWERT_MAX,FARBWERT_LEER,FARBWERT_KONTUR,FARBWERT_MOUSEOVER,FARBWERT_TEXT,KLASSEN_AUFLOESUNG)
						VALUES
						('".$_POST['Ind']."',
						'".$_SESSION['Dokument']['Fuellung']['Typ']."',
						'".$_SESSION['Dokument']['Fuellung']['Untertyp']."',
						'".$_SESSION['Dokument']['Fuellung']['Farbwert_min']."',
						'".$_SESSION['Dokument']['Fuellung']['Farbwert_max']."',
						'".$_SESSION['Dokument']['Fuellung']['LeerFarbe']."',
						'".$_SESSION['Dokument']['Strichfarbe']."',
						'".$_SESSION['Dokument']['Strichfarbe_MouseOver']."',
						'".$_SESSION['Dokument']['Textfarbe_Labels']."',
						'".$_SESSION['Dokument']['Klassen']['Aufloesung']."')";
		$Ergebnis_DSF_INS = mysqli_query($Verbindung,$SQL_DSF_INS);		
	}
}

// Farbwerte für Differenzanzeige updaten
if($_POST['aktion'] == "DIFF_UPDATE")
{
	$Ergebnis_DIFF_UPD = mysqli_query($Verbindung,"UPDATE m_zeichenvorschrift 
										SET 
										FARBWERT_DIFF_MIN = '".$_POST['FARBWERT_DIFF_MIN']."',
										FARBWERT_DIFF_MAX = '".$_POST['FARBWERT_DIFF_MAX']."'
										WHERE  ID_INDIKATOR = '".$_POST['Ind']."'"
										);

}

// Interpretation UPDATE/INSERT

if($_POST['aktion'] == "interpretation")
{
	$SQL_INTP_vorh = "SELECT ID_INTERPRETATION FROM m_interpretation WHERE ID_INDIKATOR = '".$_POST['ID_IND']."' AND JAHR = '".$_POST['Jahr']."' ";
	$Ergebnis_INTP_vorh = mysqli_query($Verbindung,$SQL_INTP_vorh);
			
	if($ID_INTERPRETATION = mysqli_result($Ergebnis_INTP_vorh,0,'ID_INTERPRETATION'))
	{
		$Ergebnis_INTP_UPD = mysqli_query($Verbindung,"UPDATE m_interpretation SET INTERPRETATION = '".$_POST['Interpretation']."' WHERE ID_INTERPRETATION = '".$ID_INTERPRETATION."'");
		if($Ergebnis_INTP_UPD) $INTP_Speicherg = 1;
	}
	else
	{
		$Ergebnis_INTP_INS = mysqli_query($Verbindung,"INSERT INTO m_interpretation (ID_INDIKATOR,JAHR,INTERPRETATION) VALUES ('".$_POST['ID_IND']."','".$_POST['Jahr']."','".$_POST['Interpretation']."')");
		if($Ergebnis_INTP_INS) $INTP_Speicherg = 1;
	}
}


$Aufklapp = $_POST['Aufklapp'];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Indikator bearbeiten</title>
<link href="../screen_viewer.css" rel="stylesheet" type="text/css" />
<style type="text/css">


td {
	padding: 5px;
	border: 1px solid #333;
}
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

</style></head>
<body style="padding-left:40px;">
<a name="oben" id="oben"></a>
<?php 

$Jahr = $_POST['Jahr'];
$ID_IND = $_POST['ID_IND'];


$SQL_IND = "SELECT * FROM m_indikator_freigabe,m_indikatoren 
			WHERE m_indikator_freigabe.ID_INDIKATOR = m_indikatoren.ID_INDIKATOR 
			AND m_indikatoren.ID_INDIKATOR = '".$ID_IND."' 
			AND m_indikator_freigabe.JAHR = '".$Jahr."' ";
$Ergebnis_IND = mysqli_query($Verbindung,$SQL_IND);


//Setzen dieser Variablen wichtig, damit auch die Angaben aus dem Prüfertool in DB geschrieben werden, auch wenn diese leer ist!
$IND = mysqli_result($Ergebnis_IND,0,'ID_INDIKATOR');
$IND_Name = utf8_encode(mysqli_result($Ergebnis_IND,0,'INDIKATOR_NAME'));
$IND_Name_EN = utf8_encode(mysqli_result($Ergebnis_IND,0,'INDIKATOR_NAME_EN'));
$Einheit = utf8_encode(mysqli_result($Ergebnis_IND,0,'EINHEIT'));
$Einheit_EN = utf8_encode(mysqli_result($Ergebnis_IND,0,'EINHEIT_EN'));
$RUNDUNG_NACHKOMMASTELLEN = utf8_encode(mysqli_result($Ergebnis_IND,0,'RUNDUNG_NACHKOMMASTELLEN'));
$ZEITSCHNITTE = utf8_encode(mysqli_result($Ergebnis_IND,0,'ZEITSCHNITTE'));
$MITTLERE_AKTUALITAET_IGNORE = mysqli_result($Ergebnis_IND,0,'MITTLERE_AKTUALITAET_IGNORE');
$BEARBEITUNGSSTAND = utf8_encode(mysqli_result($Ergebnis_IND,0,'BEARBEITUNGSSTAND'));

//Deutsch
$INFO_VIEWER_ZEILE_1 = utf8_encode(mysqli_result($Ergebnis_IND,0,'INFO_VIEWER_ZEILE_1'));
$INFO_VIEWER_ZEILE_2 = utf8_encode(mysqli_result($Ergebnis_IND,0,'INFO_VIEWER_ZEILE_2'));
$INFO_VIEWER_ZEILE_3 = utf8_encode(mysqli_result($Ergebnis_IND,0,'INFO_VIEWER_ZEILE_3'));
$INFO_VIEWER_ZEILE_4 = utf8_encode(mysqli_result($Ergebnis_IND,0,'INFO_VIEWER_ZEILE_4'));
$INFO_VIEWER_ZEILE_5 = utf8_encode(mysqli_result($Ergebnis_IND,0,'INFO_VIEWER_ZEILE_5'));
$INFO_VIEWER_ZEILE_6 = utf8_encode(mysqli_result($Ergebnis_IND,0,'INFO_VIEWER_ZEILE_6'));
$BEDEUTUNG_INTERPRETATION = utf8_encode(mysqli_result($Ergebnis_IND,0,'BEDEUTUNG_INTERPRETATION'));
$DATENGRUNDLAGE_ATKIS = utf8_encode(mysqli_result($Ergebnis_IND,0,'DATENGRUNDLAGE_ATKIS'));
$DATENGRUNDLAGE_ZEILE_1 = utf8_encode(mysqli_result($Ergebnis_IND,0,'DATENGRUNDLAGE_ZEILE_1'));
$DATENGRUNDLAGE_ZEILE_2 = utf8_encode(mysqli_result($Ergebnis_IND,0,'DATENGRUNDLAGE_ZEILE_2'));
$METHODENBESCHREIBUNG = utf8_encode(mysqli_result($Ergebnis_IND,0,'METHODIK'));
$VERWEISE = utf8_encode(mysqli_result($Ergebnis_IND,0,'VERWEISE'));
$BERECHNUNG = utf8_encode(mysqli_result($Ergebnis_IND,0,'BERECHNUNG'));
$BEMERKUNGEN = utf8_encode(mysqli_result($Ergebnis_IND,0,'BEMERKUNGEN'));


//Englisch
$INFO_VIEWER_ZEILE_1_EN = utf8_encode(mysqli_result($Ergebnis_IND,0,'INFO_VIEWER_ZEILE_1_EN'));
$INFO_VIEWER_ZEILE_2_EN = utf8_encode(mysqli_result($Ergebnis_IND,0,'INFO_VIEWER_ZEILE_2_EN'));
$INFO_VIEWER_ZEILE_3_EN = utf8_encode(mysqli_result($Ergebnis_IND,0,'INFO_VIEWER_ZEILE_3_EN'));
$INFO_VIEWER_ZEILE_4_EN = utf8_encode(mysqli_result($Ergebnis_IND,0,'INFO_VIEWER_ZEILE_4_EN'));
$INFO_VIEWER_ZEILE_5_EN = utf8_encode(mysqli_result($Ergebnis_IND,0,'INFO_VIEWER_ZEILE_5_EN'));
$INFO_VIEWER_ZEILE_6_EN = utf8_encode(mysqli_result($Ergebnis_IND,0,'INFO_VIEWER_ZEILE_6_EN'));


$BEDEUTUNG_INTERPRETATION_EN = utf8_encode(mysqli_result($Ergebnis_IND,0,'BEDEUTUNG_INTERPRETATION_EN'));
$DATENGRUNDLAGE_ZEILE_1_EN = utf8_encode(mysqli_result($Ergebnis_IND,0,'DATENGRUNDLAGE_ZEILE_1_EN'));
$DATENGRUNDLAGE_ZEILE_2_EN = utf8_encode(mysqli_result($Ergebnis_IND,0,'DATENGRUNDLAGE_ZEILE_2_EN'));
$METHODENBESCHREIBUNG_EN = utf8_encode(mysqli_result($Ergebnis_IND,0,'METHODIK_EN'));
$VERWEISE_EN = utf8_encode(mysqli_result($Ergebnis_IND,0,'VERWEISE_EN'));
$BERECHNUNG_EN = utf8_encode(mysqli_result($Ergebnis_IND,0,'BERECHNUNG_EN'));
$BEMERKUNGEN_EN = utf8_encode(mysqli_result($Ergebnis_IND,0,'BEMERKUNGEN_EN'));
$BRD = utf8_encode(mysqli_result($Ergebnis_IND,0,'RAUMEBENE_BRD'));
$BLD = utf8_encode(mysqli_result($Ergebnis_IND,0,'RAUMEBENE_BLD'));
$KRS = utf8_encode(mysqli_result($Ergebnis_IND,0,'RAUMEBENE_KRS'));
$GEM = utf8_encode(mysqli_result($Ergebnis_IND,0,'RAUMEBENE_GEM'));
$PLR = utf8_encode(mysqli_result($Ergebnis_IND,0,'RAUMEBENE_PLR'));
$ROR = utf8_encode(mysqli_result($Ergebnis_IND,0,'RAUMEBENE_ROR'));
$GMK = utf8_encode(mysqli_result($Ergebnis_IND,0,'RAUMEBENE_GMK'));
$RST = utf8_encode(mysqli_result($Ergebnis_IND,0,'RAUMEBENE_RST'));
$R10 = utf8_encode(mysqli_result($Ergebnis_IND,0,'RAUMEBENE_R10'));
$G50 = utf8_encode(mysqli_result($Ergebnis_IND,0,'RAUMEBENE_G50'));
$STT = utf8_encode(mysqli_result($Ergebnis_IND,0,'RAUMEBENE_STT'));
$VWG = utf8_encode(mysqli_result($Ergebnis_IND,0,'RAUMEBENE_VWG'));

$PLR = utf8_encode(mysqli_result($Ergebnis_IND,0,'RAUMEBENE_PLR'));
$R05 = utf8_encode(mysqli_result($Ergebnis_IND,0,'RAUMEBENE_R05'));
$R5M = utf8_encode(mysqli_result($Ergebnis_IND,0,'RAUMEBENE_R5M'));
$R2M = utf8_encode(mysqli_result($Ergebnis_IND,0,'RAUMEBENE_R2M'));
$R1M = utf8_encode(mysqli_result($Ergebnis_IND,0,'RAUMEBENE_R1M'));

$WMS = utf8_encode(mysqli_result($Ergebnis_IND,0,'WMS'));
$WCS = utf8_encode(mysqli_result($Ergebnis_IND,0,'WCS'));
$WFS = utf8_encode(mysqli_result($Ergebnis_IND,0,'WFS'));

$RAUMEBENE_ANDERE = utf8_encode(mysqli_result($Ergebnis_IND,0,'RAUMEBENE_ANDERE'));
$LITERATUR = utf8_encode(mysqli_result($Ergebnis_IND,0,'LITERATUR'));
$RASTER_FREIGABE = @mysqli_result($Ergebnis_IND,0,'RASTER_FREIGABE');



?>


<br />
<!--- titel-->
<h2>Freigabe des Indikators <?php echo $ID_IND." = ".$IND_Name; ?> für das Jahr <?php echo $Jahr; ?></h2>    
 <!---Beginn oberer Zurückbutton nach Überschrift---> 
    <form action="pruefung_u_rechte_uebersicht.php#<?php echo $ID_IND; ?>" method="get">
        <input name="Aufklapp" type="hidden" value="<?php echo $Aufklapp; ?>" />
        <input name="Ind" type="hidden" value="<?php echo $ID_IND; ?>" />
        <input name="Jhr" type="hidden" value="<?php echo $Jahr; ?>" />
        <input name="" type="submit" value="< Zur&uuml;ck" class="button_standard_abschicken_a" style="padding-left:5px; padding-right:5px; background: #B7CDA0" />
    </form>
    <br />
         <!---Ende oberer Zurückbutton nach Überschrift--->

<!---Beginn "für den Jahres Datenbestand gültig" -->

<div style="margin-left:0px; padding:10px; border:#666666 solid 1px; background:#FFFFFF; width:800px;">
    <form action="ind_bearb.php" method="post">
    
    
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
                                        if(mysqli_result($Ergebnis_IND,0,'STATUS_INDIKATOR_PRUEFG') == mysqli_result($Ergebnis_Rechte,$i_re,'STATUS_PRUEFUNG')) 
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
      <input type="text" name="PruefDatum" id="PruefDatum" value="<?php echo $Datum_Pruef = mysqli_result($Ergebnis_IND,0,'DATUM_PRUEFUNG'); ?>"  style="width:200px;" />
      (<span class="td_rand_5px">JJJJ-MM-DD</span>)<br />
      <br />
      gepr&uuml;ft durch:<br />
      <input type="text" name="Pruefer" id="Pruefer" value="<?php echo $Pruefer_name = utf8_encode(mysqli_result($Ergebnis_IND,0,'PRUEFER')); ?>"  style="width:200px;" />
      <br />
      <br />
      Bemerkungen zur Pr&uuml;fung (255 Zeichen):<br />
      <textarea name="Bemerkg" cols="70" rows="5" wrap="off" id="Bemerkg"><?php echo utf8_encode(mysqli_result($Ergebnis_IND,0,'STATUS_INDIKATOR_PRUEFG_BEMERKUNG')); ?></textarea>
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
								if(mysqli_result($Ergebnis_IND,0,'STATUS_INDIKATOR_FREIGABE') == mysqli_result($Ergebnis_Rechte,$i_re,'STATUS_FREIGABE')) 
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
      <input name="RASTER_FREIGABE" type="checkbox" id="RASTER_FREIGABE" value="1" <?php if($RASTER_FREIGABE) {echo 'checked="checked"';} ?> />
      Rasterausgabe verfügbar<br />
<br />
      <input name="input" type="submit" value="Speichern" class="button_standard_abschicken_a" />
      <br />
    </div>
    <br />
    <!---Ende "für den Jahres Datenbestand gültig" -->    
    
    
<!---Beginn "für den gesamten Datenbestand gültig" -->
    <div class="graue_Box">	<strong>F&uuml;r Gesamten Datenbestand dieses Indikators gültig:</strong><br />
        <br />
        <table width="100%" border="0" cellspacing="0" cellpadding="0" style="border-collapse:collapse;">
          <tr>
            <td colspan="3" valign="top">&nbsp;</td>
            <td rowspan="2" valign="top">&nbsp;</td>
          </tr>
          <tr>
            <td colspan="3" valign="top">&nbsp;</td>
          </tr>
          <tr>
            <td valign="top"><span class="td_rand_5px">Indikator</span></td>
            <td valign="top"><span class="td_rand_5px">
              <textarea name="NAME" cols="50" rows="2" id="NAME"><?php echo $IND_Name; ?></textarea>
            </span></td>
            <td valign="top"><span class="td_rand_5px">Code</span></td>
            <td valign="top"><span class="td_rand_5px">
            <input name="ID_IND" type="text" id="ID_IND" value="<?php echo $IND; ?>" style="width:100px;" />
            </span></td>
          </tr>
          <tr>
            <td valign="top" style="background: #E7EFFE;" ><span class="td_rand_5px">Indikator</span> EN</td>
            <td valign="top" style="background: #E7EFFE;" ><span class="td_rand_5px">
              <textarea name="NAME_EN" cols="50" rows="2" id="NAME_EN"><?php echo utf8_encode(mysqli_result($Ergebnis_IND,0,'INDIKATOR_NAME_EN')); ?></textarea>
            </span></td>
            <td valign="top">&nbsp;</td>
            <td valign="top">&nbsp;</td>
          </tr>
          <tr>
            <td valign="top"><span class="td_rand_5px">Kategorie</span></td>
            <td valign="top"><span class="td_rand_5px">
              <select name="ID_THEMA_KAT" id="ID_THEMA_KAT" style="width:200px;" >
                <?php 
						 
			$SQL_Kat = "SELECT * FROM m_thematische_kategorien ORDER BY SORTIERUNG_THEMA_KAT";
			$Ergebnis_Kat = mysqli_query($Verbindung,$SQL_Kat);
			$i_kat = 0;
			while(@mysqli_result($Ergebnis_Kat,$i_kat,'ID_THEMA_KAT'))
			{
				?>
                <option value="<?php echo mysqli_result($Ergebnis_Kat,$i_kat,'ID_THEMA_KAT'); ?>" <?php 
					if(mysqli_result($Ergebnis_IND,0,'ID_THEMA_KAT') == mysqli_result($Ergebnis_Kat,$i_kat,'ID_THEMA_KAT')) 
					{
						echo 'selected="selected"'; 
					}
					?> ><?php echo utf8_encode(mysqli_result($Ergebnis_Kat,$i_kat,'THEMA_KAT_NAME')); 
				?></option>
                <?php
				$i_kat++; 
			 }
            ?>
              </select>
            </span></td>
            <td valign="top"><span class="td_rand_5px">Ma&szlig;einheit</span></td>
            <td valign="top"><span class="td_rand_5px">
            <input name="Einheit" type="text" id="Einheit" value="<?php echo $Einheit; ?>" style="width:50px;" />
            </span></td>
          </tr>
          <tr>
            <td valign="top" >&nbsp;</td>
            <td valign="top" >&nbsp;</td>
            <td valign="top" style="background: #E7EFFE;" ><span class="td_rand_5px">Ma&szlig;einheit</span> EN</td>
            <td valign="top" style="background: #E7EFFE;" ><span class="td_rand_5px">
              <input name="Einheit_EN" type="text" id="Einheit_EN" value="<?php echo utf8_encode(mysqli_result($Ergebnis_IND,0,'EINHEIT_EN')); ?>" style="width:50px;" />
            </span></td>
          </tr>
          <tr>
            <td valign="top"><span class="td_rand_5px">Zeitschnitte<br />
              (gepr&uuml;ft)
            </span></td>
            <td valign="top"><span class="td_rand_5px">
              <textarea name="ZEITSCHNITTE" cols="50" rows="2" id="ZEITSCHNITTE" ><?php echo $ZEITSCHNITTE; ?></textarea>
            </span></td>
            <td valign="top"><span class="td_rand_5px">Bearbeitungs-<br />
            Stand</span></td>
            <td valign="top">
              <?php echo substr($BEARBEITUNGSSTAND,0,10); ?>
            </td>
          </tr>
          <tr>
            <td valign="top">&nbsp;</td>
            <td valign="top">&nbsp;</td>
            <td valign="top">&nbsp;</td>
            <td valign="top">&nbsp;</td>
          </tr>
          <tr>
            <td colspan="4" valign="top">Rundung für Kartendarstellung (Nachkommastellen) 
              <input name="RUNDUNG_NACHKOMMASTELLEN" type="text" id="RUNDUNG_NACHKOMMASTELLEN" size="4" value="<?php echo $RUNDUNG_NACHKOMMASTELLEN; ?>" /></td>
          </tr>
          <tr>
            <td colspan="4" valign="top">Mittlere Grundaktualität nicht sinnvoll: 
            <input type="checkbox" name="MITTLERE_AKTUALITAET_IGNORE" id="MITTLERE_AKTUALITAET_IGNORE" value="1" <?php if($MITTLERE_AKTUALITAET_IGNORE) echo 'checked="checked"';?>/></td>
          </tr>
          <tr>
            <td colspan="4" valign="top">Kurzbeschreibung (auch für Kartenlegende)</td>
          </tr>
          <tr>
            <td colspan="4" valign="top">
            	
            	<!--
            	<input name="INFO_VIEWER_ZEILE_1" type="text" id="INFO_VIEWER_ZEILE_1" size="80" maxlength="80" value="<?php echo $INFO_VIEWER_ZEILE_1; ?>" />
              <br />
              <input type="text" name="INFO_VIEWER_ZEILE_2" id="INFO_VIEWER_ZEILE_2" size="80" maxlength="80" value="<?php echo $INFO_VIEWER_ZEILE_2 = utf8_encode(mysqli_result($Ergebnis_IND,0,'INFO_VIEWER_ZEILE_2')); ?>" />
              <br />
              <input type="text" name="INFO_VIEWER_ZEILE_3" id="INFO_VIEWER_ZEILE_3" size="80" maxlength="80" value="<?php echo $INFO_VIEWER_ZEILE_3 = utf8_encode(mysqli_result($Ergebnis_IND,0,'INFO_VIEWER_ZEILE_3')); ?>" />
              <br />
              <input type="text" name="INFO_VIEWER_ZEILE_4" id="INFO_VIEWER_ZEILE_4" size="80" maxlength="80" value="<?php echo $INFO_VIEWER_ZEILE_4 = utf8_encode(mysqli_result($Ergebnis_IND,0,'INFO_VIEWER_ZEILE_4')); ?>" />
              <br />
              <input type="text" name="INFO_VIEWER_ZEILE_5" id="INFO_VIEWER_ZEILE_5" size="80" maxlength="80" value="<?php echo $INFO_VIEWER_ZEILE_5 = utf8_encode(mysqli_result($Ergebnis_IND,0,'INFO_VIEWER_ZEILE_5')); ?>" />
              <br />
            <input type="text" name="INFO_VIEWER_ZEILE_6" id="INFO_VIEWER_ZEILE_6" size="80" maxlength="80" value="<?php echo $INFO_VIEWER_ZEILE_6 = utf8_encode(mysqli_result($Ergebnis_IND,0,'INFO_VIEWER_ZEILE_6')); ?>" />
            -->
            
           <textarea name="INFO_VIEWER_ZEILE_1" rows="0" id="INFO_VIEWER_ZEILE_1"  maxlength="80" style="width:700px; height:20px;"><?php echo $INFO_VIEWER_ZEILE_1; ?></textarea>
           <textarea name="INFO_VIEWER_ZEILE_2" rows="0" id="INFO_VIEWER_ZEILE_2"  maxlength="80" style="width:700px; height:20px;"><?php echo $INFO_VIEWER_ZEILE_2; ?></textarea> 
           <textarea name="INFO_VIEWER_ZEILE_3" rows="0" id="INFO_VIEWER_ZEILE_3"  maxlength="80" style="width:700px; height:20px;"><?php echo $INFO_VIEWER_ZEILE_3; ?></textarea>
           <textarea name="INFO_VIEWER_ZEILE_4" rows="0" id="INFO_VIEWER_ZEILE_4"  maxlength="80" style="width:700px; height:20px;"><?php echo $INFO_VIEWER_ZEILE_4; ?></textarea>
           <textarea name="INFO_VIEWER_ZEILE_5" rows="0" id="INFO_VIEWER_ZEILE_5"  maxlength="80" style="width:700px; height:20px;"><?php echo $INFO_VIEWER_ZEILE_5; ?></textarea>
           <textarea name="INFO_VIEWER_ZEILE_6" rows="0" id="INFO_VIEWER_ZEILE_6"  maxlength="80" style="width:700px; height:20px;"><?php echo $INFO_VIEWER_ZEILE_6; ?></textarea>
            
            </td>
          </tr>
          
                    <tr>
            <td colspan="4" valign="top" style="background: #E7EFFE;" >EN:<br />
            	
            	<!--
              <input name="INFO_VIEWER_ZEILE_1_EN" type="text" id="INFO_VIEWER_ZEILE_1_EN" size="80" maxlength="80" value="<?php echo $INFO_VIEWER_ZEILE_1_EN; ?>" />
              <br />
              <input type="text" name="INFO_VIEWER_ZEILE_2_EN" id="INFO_VIEWER_ZEILE_2_EN" size="80" maxlength="80" value="<?php echo $INFO_VIEWER_ZEILE_2_EN = utf8_encode(mysqli_result($Ergebnis_IND,0,'INFO_VIEWER_ZEILE_2_EN')); ?>" />
              <br />
              <input type="text" name="INFO_VIEWER_ZEILE_3_EN" id="INFO_VIEWER_ZEILE_3_EN" size="80" maxlength="80" value="<?php echo $INFO_VIEWER_ZEILE_3_EN = utf8_encode(mysqli_result($Ergebnis_IND,0,'INFO_VIEWER_ZEILE_3_EN')); ?>" />
              <br />
              <input type="text" name="INFO_VIEWER_ZEILE_4_EN" id="INFO_VIEWER_ZEILE_4_EN" size="80" maxlength="80" value="<?php echo $INFO_VIEWER_ZEILE_4_EN = utf8_encode(mysqli_result($Ergebnis_IND,0,'INFO_VIEWER_ZEILE_4_EN')); ?>" />
              <br />
              <input type="text" name="INFO_VIEWER_ZEILE_5_EN" id="INFO_VIEWER_ZEILE_5_EN" size="80" maxlength="80" value="<?php echo $INFO_VIEWER_ZEILE_5_EN = utf8_encode(mysqli_result($Ergebnis_IND,0,'INFO_VIEWER_ZEILE_5_EN')); ?>" />
              <br />
            <input type="text" name="INFO_VIEWER_ZEILE_6_EN" id="INFO_VIEWER_ZEILE_6_EN" size="80" maxlength="80" value="<?php echo $INFO_VIEWER_ZEILE_6_EN = utf8_encode(mysqli_result($Ergebnis_IND,0,'INFO_VIEWER_ZEILE_6_EN')); ?>" />
            -->
           <textarea name="INFO_VIEWER_ZEILE_1_EN" rows="0" id="INFO_VIEWER_ZEILE_1_EN"  maxlength="80" style="width:700px; height:20px;"><?php echo $INFO_VIEWER_ZEILE_1_EN; ?></textarea>
           <textarea name="INFO_VIEWER_ZEILE_2_EN" rows="0" id="INFO_VIEWER_ZEILE_2_EN"  maxlength="80" style="width:700px; height:20px;"><?php echo $INFO_VIEWER_ZEILE_2_EN; ?></textarea> 
           <textarea name="INFO_VIEWER_ZEILE_3_EN" rows="0" id="INFO_VIEWER_ZEILE_3_EN"  maxlength="80" style="width:700px; height:20px;"><?php echo $INFO_VIEWER_ZEILE_3_EN; ?></textarea>
           <textarea name="INFO_VIEWER_ZEILE_4_EN" rows="0" id="INFO_VIEWER_ZEILE_4_EN"  maxlength="80" style="width:700px; height:20px;"><?php echo $INFO_VIEWER_ZEILE_4_EN; ?></textarea>
           <textarea name="INFO_VIEWER_ZEILE_5_EN" rows="0" id="INFO_VIEWER_ZEILE_5_EN"  maxlength="80" style="width:700px; height:20px;"><?php echo $INFO_VIEWER_ZEILE_5_EN; ?></textarea>
           <textarea name="INFO_VIEWER_ZEILE_6_EN" rows="0" id="INFO_VIEWER_ZEILE_6_EN"  maxlength="80" style="width:700px; height:20px;"><?php echo $INFO_VIEWER_ZEILE_6_EN; ?></textarea>
            </td>
          </tr>
          
          <tr>
            <td colspan="4" valign="top">Bedeutung und Interpretation</td>
          </tr>
          <tr>
            <td colspan="4" valign="top"><textarea name="BEDEUTUNG_INTERPRETATION" rows="5" id="BEDEUTUNG_INTERPRETATION" style="width:700px;"><?php echo $BEDEUTUNG_INTERPRETATION; ?></textarea></td>
          </tr>
          
          <tr>
            <td colspan="4" valign="top" style="background: #E7EFFE;" >EN:<br />
<textarea name="BEDEUTUNG_INTERPRETATION_EN" rows="5" id="BEDEUTUNG_INTERPRETATION_EN" style="width:700px;"><?php echo utf8_encode(mysqli_result($Ergebnis_IND,0,'BEDEUTUNG_INTERPRETATION_EN')); ?></textarea></td>
          </tr>
          
          
          <tr>
            <td colspan="4" valign="top">Datengrundlagen</td>
          </tr>
          <tr>
            <td colspan="4" valign="top"><input type="checkbox" name="DATENGRUNDLAGE_ATKIS" id="DATENGRUNDLAGE_ATKIS" <?php if($DATENGRUNDLAGE_ATKIS) echo "checked"; ?> /> ATKIS Basis-DLM, BKG</td>
          </tr>
          <tr>
            <td colspan="4" valign="top">
            	<!---
            	<input type="text" name="DATENGRUNDLAGE_ZEILE_1" id="DATENGRUNDLAGE_ZEILE_1" size="80" maxlength="80" value="<?php echo $DATENGRUNDLAGE_ZEILE_1 = utf8_encode(mysqli_result($Ergebnis_IND,0,'DATENGRUNDLAGE_ZEILE_1')); ?>" />
              <br />
            <input type="text" name="DATENGRUNDLAGE_ZEILE_2" id="DATENGRUNDLAGE_ZEILE_2" size="80" maxlength="80" value="<?php echo $DATENGRUNDLAGE_ZEILE_2 = utf8_encode(mysqli_result($Ergebnis_IND,0,'DATENGRUNDLAGE_ZEILE_2')); ?>" />
  <span class="Text_10px"><br/>ACHTUNG Schreibweise für Links: <strong>&lt;a target='_blank' href='LINK'&gt;TEXT&lt;/a&gt;  (Bei Datengrundlagen keine doppelten Gänsefüßchen verwenden!).</strong></span>-->
            <textarea name="DATENGRUNDLAGE_ZEILE_1" rows="0" id="DATENGRUNDLAGE_ZEILE_1"  maxlength="80" style="width:700px; height:20px;"><?php echo $DATENGRUNDLAGE_ZEILE_1; ?></textarea>
           <textarea name="DATENGRUNDLAGE_ZEILE_2" rows="0" id="DATENGRUNDLAGE_ZEILE_2"  maxlength="80" style="width:700px; height:20px;"><?php echo $DATENGRUNDLAGE_ZEILE_2; ?></textarea> 
          </td>
         
          </tr>
          
           <tr>
            <td colspan="4" valign="top" style="background: #E7EFFE;" >EN:<br />
<!--<input type="text" name="DATENGRUNDLAGE_ZEILE_1_EN" id="DATENGRUNDLAGE_ZEILE_1_EN" size="80" maxlength="80" value="<?php echo $DATENGRUNDLAGE_ZEILE_1_EN = utf8_encode(mysqli_result($Ergebnis_IND,0,'DATENGRUNDLAGE_ZEILE_1_EN')); ?>" />
              <br />
            <input type="text" name="DATENGRUNDLAGE_ZEILE_2_EN" id="DATENGRUNDLAGE_ZEILE_2_EN" size="80" maxlength="80" value="<?php echo $DATENGRUNDLAGE_ZEILE_2_EN = utf8_encode(mysqli_result($Ergebnis_IND,0,'DATENGRUNDLAGE_ZEILE_2_EN')); ?>" />-->
          <textarea name="DATENGRUNDLAGE_ZEILE_1_EN" rows="0" id="DATENGRUNDLAGE_ZEILE_1_EN"  maxlength="80" style="width:700px; height:20px;"><?php echo $DATENGRUNDLAGE_ZEILE_1_EN; ?></textarea>
           <textarea name="DATENGRUNDLAGE_ZEILE_2_EN" rows="0" id="DATENGRUNDLAGE_ZEILE_2_EN"  maxlength="80" style="width:700px; height:20px;"><?php echo $DATENGRUNDLAGE_ZEILE_2_EN; ?></textarea> 
          </td>
         
          </tr>
          
          
          <tr>
            <td colspan="4" valign="top">Methodik</td>
          </tr>
          <tr>
            <td colspan="4" valign="top"><textarea name="METHODENBESCHREIBUNG" rows="10" id="METHODENBESCHREIBUNG" style="width:700px;"><?php echo $METHODENBESCHREIBUNG; ?></textarea></td>
          </tr>
          
          <tr>
            <td colspan="4" valign="top" style="background: #E7EFFE;" >EN:<br />
			<textarea name="METHODIK_EN" rows="10" id="METHODIK_EN" style="width:700px;"><?php echo utf8_encode(mysqli_result($Ergebnis_IND,0,'METHODIK_EN')); ?></textarea></td>
          </tr>          
          
          <tr>
            <td colspan="4" valign="top">Verweise (optional)</td>
          </tr>
          <tr>
            <td colspan="4" valign="top"><textarea name="VERWEISE" rows="5" id="VERWEISE" style="width:700px;"><?php echo $VERWEISE; ?></textarea>
            <br />
            <span class="Text_10px">Schreibweise für Links: <strong>&lt;a target=&quot;_blank&quot; href=&quot;LINK&quot;&gt;TEXT&lt;/a&gt;</strong></span></td>
          </tr>
          
                    <tr>
            <td colspan="4" valign="top" style="background: #E7EFFE;" >EN:<br />
            <textarea name="VERWEISE_EN" rows="5" id="VERWEISE_EN" style="width:700px;"><?php echo utf8_encode(mysqli_result($Ergebnis_IND,0,'VERWEISE_EN'));
			 ?></textarea>
            <br />
            <span class="Text_10px">Schreibweise für Links: <strong>&lt;a target=&quot;_blank&quot; href=&quot;LINK&quot;&gt;TEXT&lt;/a&gt;</strong></span></td>
          </tr>
          
          <tr>
            <td colspan="4" valign="top">Bemerkungen (optional)</td>
          </tr>
          <tr>
            <td colspan="4" valign="top"><textarea name="BEMERKUNGEN" rows="5" id="BEMERKUNGEN" style="width:700px;"><?php echo $BEMERKUNGEN; ?></textarea></td>
          </tr>
          
          <tr>
            <td colspan="4" valign="top" style="background: #E7EFFE;" >EN:<br />
            <textarea name="BEMERKUNGEN_EN" rows="5" id="BEMERKUNGEN_EN" style="width:700px;"><?php echo utf8_encode(mysqli_result($Ergebnis_IND,0,'BEMERKUNGEN_EN')); ?></textarea></td>
          </tr>
          
          
          <tr>
            <td colspan="4" valign="top">Bezugsebenen</td>
          </tr>
          <tr>
            <td colspan="4" valign="top" >
            <div>
                     <table width="100%" border="0" cellspacing="0" cellpadding="0" style="border-collapse:collapse; border:0px;">
                          <tr>
                            <td width="4%">
                                <input type="checkbox" name="BRD" id="BRD" <?php if($BRD) echo "checked"; ?> />
                            </td>
                            <td width="30%">Bundesrepublik Deutschland</td>
                            <td width="4%">
                               <input type="checkbox" name="G50" id="G50" <?php if($G50) echo "checked"; ?> />
                            </td>
                            <td width="29%">Städte (&gt; 50 000 Ew.) </td>
                             <td width="4%">
                            	<input type="checkbox" name="R10" id="R10" <?php if($R10) echo "checked"; ?> />
                            </td>
                            <td width="29%">Raster 10 km</td>
                          </tr>
                          <tr>
                            <td>
                            	<input type="checkbox" name="BLD" id="BLD" <?php if($BLD) echo "checked"; ?> />
                            </td>
                            <td>Bundesl&auml;nder</td>
                            <td>
                           	<input type="checkbox" name="STT" id="STT" <?php if($STT) echo "checked"; ?> />
                            </td>
                            <td>Stadtteile</td>
                             <td>
                            	<input type="checkbox" name="R05" id="R05" <?php if($R05) echo "checked"; ?> />  
                            </td>	
                            <td>Raster 5 km</td>
                          </tr>
                          <tr>
                            <td>
                            	<input type="checkbox" name="KRS" id="KRS" <?php if($KRS) echo "checked"; ?> />
                            </td>
                            <td>Kreise</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                             <td>
                            	<input type="checkbox" name="RST" id="RST" <?php if($RST) echo "checked"; ?> />
                            </td>
                            <td>Raster 1 km</td>
                          </tr>
                          <tr>
                            <td>
                            	<input type="checkbox" name="GEM" id="GEM" <?php if($GEM) echo "checked"; ?> />
                            </td>
                            <td>Gemeinden</td>
                             <td>
                            	<input type="checkbox" name="VWG" id="VWG" <?php if($VWG) echo "checked"; ?> />
                            </td>
                            <td>Gemeindeverband</td>
                             <td>
                          	<input type="checkbox" name="R5M" id="R5M" <?php if($R5M) echo "checked"; ?> />
                            </td>
                            <td>Raster 500 m</td>
                          </tr>
                          <tr>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>	
                            	<input type="checkbox" name="R2M" id="R2M" <?php if($R2M) echo "checked"; ?> /> 
                            </td>
                           <td>Raster 200 m</td>
                          </tr>
                          <tr>
                             <td>
                             <input type="checkbox" name="ROR" id="ROR" <?php if($ROR) echo "checked"; ?> />
                            </td>
                            <td>Raumordnungsregionen</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                           <td>	
                     <input type="checkbox" name="R1M" id="R1M" <?php if($R1M) echo "checked"; ?> />  
                            </td>
                           <td>Raster 100 m</td>
                          </tr>  
                          <tr>
                            <td>
                          	<input type="checkbox" name="PLR" id="PLR" <?php if($PLR) echo "checked"; ?> />
                            </td>
                            <td>Planungsregionen</td>
                             <td>&nbsp;</td>
                            <td>&nbsp;</td>
                           </tr>
                     </table>
               </div>
            </td>
          </tr>
         
					<tr>
            <td colspan="4" valign="top" >
            <div>
                     <table width="100%" border="0" cellspacing="0" cellpadding="0" style="border-collapse:collapse; border:0px;">
                          <tr>
                            <td width="4%">
                                <input type="checkbox" name="WMS" id="WMS" <?php if($WMS) echo "checked"; ?> />
                            </td>
                            <td width="30%">WMS</td>
                            <td width="4%">
                               <input type="checkbox" name="WCS" id="WCS" <?php if($WCS) echo "checked"; ?> />
                            </td>
                            <td width="29%">WCS </td>
                             <td width="4%">
                            	<input type="checkbox" name="WFS" id="WFS" <?php if($WFS) echo "checked"; ?> />
                            </td>
                            <td width="29%">WFS</td>
                          </tr>
                     
                     </table>
               </div>
            </td>
          </tr>        
        <tr>
            <td colspan="4" valign="top">Quellen/Literatur</td>
          </tr>
          <tr>
            <td colspan="4" valign="top">
           	  <textarea name="LITERATUR" rows="5" id="LITERATUR" style="width:700px;"><?php echo $LITERATUR; ?></textarea><br />
            <span class="Text_10px">Schreibweise für Links: <strong>&lt;a target=&quot;_blank&quot; href=&quot;LINK&quot;&gt;TEXT&lt;/a&gt;</strong></span></td>
          </tr>
          
          <tr>
            <td colspan="4" valign="top" style="background: #E7EFFE;" >EN:<br />
           	  <textarea name="LITERATUR_EN" rows="5" id="LITERATUR_EN" style="width:700px;"><?php echo utf8_encode(mysqli_result($Ergebnis_IND,0,'LITERATUR_EN')); ?></textarea><br />
            <span class="Text_10px">Schreibweise für Links: <strong>&lt;a target=&quot;_blank&quot; href=&quot;LINK&quot;&gt;TEXT&lt;/a&gt;</strong></span></td>
          </tr>
          
        </table>
<br />
<br />
<br />
        <br />

        <input name="input3" type="submit" value="Speichern" class="button_standard_abschicken_a" />
		<br />
        <br />
    </div>
    <input name="Aufklapp" type="hidden" value="<?php echo $Aufklapp; ?>" />
        <input name="aktion" type="hidden" value="update" />
        <input name="Jahr" type="hidden" value="<?php echo $Jahr; ?>" />
        <input name="ID_IND_org" type="hidden" value="<?php echo $ID_IND; ?>" />

  </form>
</div>
<!---Ende "für den gesamten Datenbestand gültig" -->
	<br />
<!---Beginn Zurückbutton zw gesamter Datenbestand und Farbgebung/Klassifikation-->
    <form action="pruefung_u_rechte_uebersicht.php#<?php echo $ID_IND; ?>" method="get">
        <input name="Aufklapp" type="hidden" value="<?php echo $Aufklapp; ?>" />
        <input name="Ind" type="hidden" value="<?php echo $ID_IND; ?>" />
        <input name="Jhr" type="hidden" value="<?php echo $Jahr; ?>" />
        <input name="" type="submit" value="< Zur&uuml;ck" class="button_standard_abschicken_a" style="padding-left:5px; padding-right:5px; background: #B7CDA0" />
    </form>
    <br />
<!---Ende Zurückbutton zw Gesamter Datenbestand und Farbgebung/Klassifikation-->  
    
    
<!---Beginn Farbgebung/Klassifikation--> 
    <a name="DSF" id="DSF"></a>
<div style="margin-left:0px; padding:10px; border:#666666 solid 1px; background:#FFFFFF; width:800px;">
  
    <div class="graue_Box">	<strong>Farbgebung / Klassifizierung </strong>(F&uuml;r Gesamten Datenbestand dieses Indikators g&uuml;ltig)<br />
      <br />
      Derzeitige Definition in der Datenbank:<br />
      <br />
      <?php 
	  if($Ergebnis_DSF_UPD or $Ergebnis_DSF_INS) 
	  {
	  ?>
	  <span style="color:#CC0000; font-weight:bold;">Die Daten wurden erfolgreich in der Datenbank gespeichert.</span><br />
	  <?php 
	  }
	  ?>
      <br />
		
 		<?php 
		// UPDATE oder INSERT
		$SQL_DSF = "SELECT * FROM m_zeichenvorschrift WHERE ID_INDIKATOR = '".$_POST['ID_IND']."'";
		$Ergebnis_DSF = mysqli_query($Verbindung,$SQL_DSF);
		
		if(@mysqli_result($Ergebnis_DSF,0,'ID_INDIKATOR'))
		{
			?>
<table>
                <tr>
               	  <td>TYP_FUELLUNG:</td><td><?php echo @mysqli_result($Ergebnis_DSF,0,'TYP_FUELLUNG'); ?></td><td></td>
                </tr>
                <tr>
               	  <td>UNTERTYP_FUELLUNG:</td><td><?php if(@mysqli_result($Ergebnis_DSF,0,'UNTERTYP_FUELLUNG') != "haeufigkeit") { echo "Gleiche Klassenbreite";}else{ echo "Gleiche Klassenbesetzung"; } ?></td><td></td>
                </tr>
                <tr>
                	<td>KLASSEN_AUFLOESUNG:</td><td><?php echo @mysqli_result($Ergebnis_DSF,0,'KLASSEN_AUFLOESUNG'); ?></td><td></td>
                </tr>
				<tr>
       	 			 <td>FARBWERT_MIN:</td><td><?php echo @mysqli_result($Ergebnis_DSF,0,'FARBWERT_MIN'); ?></td><td>
           				<div style="border:solid #666666 1px; width:15px; height:15px; overflow:hidden; background-color:#<?php echo @mysqli_result($Ergebnis_DSF,0,'FARBWERT_MIN'); ?>;"></div>
                	</td>
                </tr>
                <tr>
               	  <td>FARBWERT_MAX:</td><td><?php echo @mysqli_result($Ergebnis_DSF,0,'FARBWERT_MAX'); ?></td><td>
                	<div style="border:solid #666666 1px; width:15px; height:15px; overflow:hidden; background-color:#<?php echo @mysqli_result($Ergebnis_DSF,0,'FARBWERT_MAX'); ?>;"></div>
                	</td>
                </tr>
                
                <tr>
               	  <td>FARBWERT_LEER:</td><td><?php echo @mysqli_result($Ergebnis_DSF,0,'FARBWERT_LEER'); ?></td><td>
                	<div style="border:solid #666666 1px; width:15px; height:15px; overflow:hidden; background-color:#<?php echo @mysqli_result($Ergebnis_DSF,0,'FARBWERT_LEER'); ?>;"></div>
                	</td>
                </tr>
                <tr>
               	  <td>FARBWERT_KONTUR:</td><td><?php echo @mysqli_result($Ergebnis_DSF,0,'FARBWERT_KONTUR'); ?></td><td>
                	<div style="border:solid #666666 1px; width:15px; height:15px; overflow:hidden; background-color:#<?php echo @mysqli_result($Ergebnis_DSF,0,'FARBWERT_KONTUR'); ?>;"></div>
                	</td>
                </tr>
                <tr>
               	  <td>FARBWERT_MOUSEOVER:</td><td><?php echo @mysqli_result($Ergebnis_DSF,0,'FARBWERT_MOUSEOVER'); ?></td><td>
                	<div style="border:solid #666666 1px; width:15px; height:15px; overflow:hidden; background-color:#<?php echo @mysqli_result($Ergebnis_DSF,0,'FARBWERT_MOUSEOVER'); ?>;"></div>
                	</td>
                </tr>
                <tr>
               	  <td>FARBWERT_TEXT:</td><td><?php echo @mysqli_result($Ergebnis_DSF,0,'FARBWERT_TEXT'); ?></td><td>
                	<div style="border:solid #666666 1px; width:15px; height:15px; overflow:hidden; background-color:#<?php echo @mysqli_result($Ergebnis_DSF,0,'FARBWERT_TEXT'); ?>;"></div>
                	</td>
                </tr>
                <form action="" method="post">
                    <tr>
                      <td colspan="3">&nbsp;</td>
                    </tr>
                    <tr>
                       <td>FARBWERT_DIFF_MIN:</td>
                       <td>
					   <input name="FARBWERT_DIFF_MIN" type="text" value="<?php echo @mysqli_result($Ergebnis_DSF,0,'FARBWERT_DIFF_MIN'); ?>" />
                       </td>
                       <td>
                          <div style="border:solid #666666 1px; width:15px; height:15px; overflow:hidden; background-color:#<?php echo @mysqli_result($Ergebnis_DSF,0,'FARBWERT_DIFF_MIN'); ?>;"></div>
                      </td>
                    </tr>
                    <tr>
                      <td>FARBWERT_DIFF_MAX:</td>
                      <td>
					  <input name="FARBWERT_DIFF_MAX" type="text" value="<?php echo @mysqli_result($Ergebnis_DSF,0,'FARBWERT_DIFF_MAX'); ?>" />
                      </td>
                      <td>
                        <div style="border:solid #666666 1px; width:15px; height:15px; overflow:hidden; background-color:#<?php echo @mysqli_result($Ergebnis_DSF,0,'FARBWERT_DIFF_MAX'); ?>;"></div>
                      </td>
                    </tr>
                    <tr>
                      <td>
                      	<input name="Ind" type="hidden" value="<?php echo $ID_IND; ?>" />
                        <input name="ID_IND" type="hidden" value="<?php echo $ID_IND; ?>" />
                        <input name="Aufklapp" type="hidden" value="<?php echo $Aufklapp; ?>" />
                        <input name="Jahr" type="hidden" value="<?php echo $Jahr; ?>" />
                        <input name="aktion" type="hidden" value="DIFF_UPDATE" />
                      </td>
                      <td colspan="2">
                          <input name="diff" type="submit" value="Differenz-Farbwerte speichern" />
                      </td>
                    </tr>
                </form>
			</table>
			<?php 
		}
		else
		{
		  ?>
			<strong>Keine Definition vorhanden.</strong>
			<?php 
		}
		?>
        <br />
        <br />
        <form action="#DSF" method="post">
            <input name="aktion" type="hidden" value="farbwerte" />
            <input name="Ind" type="hidden" value="<?php echo $ID_IND; ?>" />
            <input name="ID_IND" type="hidden" value="<?php echo $ID_IND; ?>" />
            <input name="Aufklapp" type="hidden" value="<?php echo $Aufklapp; ?>" />
            <input name="Jahr" type="hidden" value="<?php echo $Jahr; ?>" />
            <input name="input4" type="submit" value="Einstellungen aus der Karte heraus &uuml;bernehmen" class="button_standard_abschicken_a" />
       </form> 
       <br />
        <!--
        <br />
        <br />
        <br />
        Für Änderungen: 
        <a href="../svg_html.php#top" target="_self"><span class="button_standard_abschicken_a" style="padding-left:5px; padding-right:5px; background: #B7CDA0">Zur&uuml;ck zur Karte</span></a> -->
        <br /> 
        <br />   
    </div>
    
</div>
<!---Ende Farbgebung/Klassifikation-->    




<!--Beginn letzter Abscnitt: Interpretation--->
<br />
<a name="INTP" id="INTP"></a>
<br />

<div style="margin-left:0px; padding:10px; border:#666666 solid 1px; background:#FFFFFF; width:800px;">
  <form action="#INTP" method="post">
    <div class="graue_Box">	<strong>Interpretation </strong>(Nur f&uuml;r gewählten Zeitschnitt des Indikators gültig)<br />
      <br />
      <?php 
	  if($INTP_Speicherg) { ?>  <span style="color:#CC0000; font-weight:bold;">Die Daten wurden erfolgreich in der Datenbank gespeichert.</span><br /><?php }
	  
	  
	  $SQL_INTP = "SELECT * FROM m_interpretation 
			WHERE ID_INDIKATOR = '".$ID_IND."' 
			AND JAHR = '".$Jahr."' ";
	  $Ergebnis_INTP = mysqli_query($Verbindung,$SQL_INTP);
		
	  $INTP = @mysqli_result($Ergebnis_INTP,0,'INTERPRETATION');
	  ?>
      <textarea name="Interpretation" rows="20" style="width:750px;" id="Interpretation"><?php echo $INTP; ?></textarea>
      <br />
      <br />
        <br />
        <input name="aktion" type="hidden" value="interpretation" />
        <input name="Ind" type="hidden" value="<?php echo $ID_IND; ?>" />
        <input name="ID_IND" type="hidden" value="<?php echo $ID_IND; ?>" />
        <input name="Aufklapp" type="hidden" value="<?php echo $Aufklapp; ?>" />
        <input name="Jahr" type="hidden" value="<?php echo $Jahr; ?>" />
        <input name="input4" type="submit" value="Speichern" class="button_standard_abschicken_a" />
        <br />
        <br /> 
        <br />   
    </div>
  </form>
</div>
<!--Ende letzter Abschnitt: Interpretation--->
<br />
<!--Zurückbutton ganz unten-->
    <form action="pruefung_u_rechte_uebersicht.php#<?php echo $ID_IND; ?>" method="get">
        <input name="Aufklapp" type="hidden" value="<?php echo $Aufklapp; ?>" />
        <input name="Ind" type="hidden" value="<?php echo $ID_IND; ?>" />
        <input name="Jhr" type="hidden" value="<?php echo $Jahr; ?>" />
        <input name="" type="submit" value="< Zur&uuml;ck" class="button_standard_abschicken_a" style="padding-left:5px; padding-right:5px; background: #B7CDA0" />
    </form>



</body>
</html>




