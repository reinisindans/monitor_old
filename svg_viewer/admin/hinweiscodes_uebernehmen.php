<?php 
// Zeit-Limit erhöhen
ini_set('max_execution_time', '500');

include("../includes_classes/verbindung_mysqli.php");

if($Zeitschnitt = $_POST['Zeitschnitt'])
{
	// Löschen alter Hinweiscodes
	$SQL_HW_DEL = "UPDATE m_indikatorwerte_".$Zeitschnitt." SET HINWEISCODE = ''";
	$Ergebnis_HW_DEL = mysqli_query($Verbindung,$SQL_HW_DEL);		
	
	// Hinweiscodes nach Priorität erfassen
	$SQL_HW_PRIORITAET = "SELECT * FROM m_hinweiscodes ORDER BY HC_PRIORITAET DESC";
	$Ergebnis_HW_PRIORITAET = mysqli_query($Verbindung,$SQL_HW_PRIORITAET);
	
	$i_hc = 0;
	while($HC = mysqli_result($Ergebnis_HW_PRIORITAET,$i_hc,'HC'))
	{
		// Hinweiscodes aus Tabelle h_XXXX erfassen
		$SQL_HW_SELECT = "SELECT * FROM h_".$Zeitschnitt." WHERE Code = '".$HC."' GROUP BY AGS,Indikator";
		$Ergebnis_HW_SELECT = mysqli_query($Verbindung,$SQL_HW_SELECT);
		
		$i = 0;
		while($ags = @mysqli_result($Ergebnis_HW_SELECT,$i,'AGS'))
		{
			// Hinweiscodes einfügen
			$SQL_HW_UPD = "UPDATE m_indikatorwerte_".$Zeitschnitt." SET HINWEISCODE = '".$HC."' WHERE AGS = '".$ags."' AND ID_INDIKATOR = '".@mysqli_result($Ergebnis_HW_SELECT,$i,'Indikator')."'";
			$Ergebnis_HW_UPD = mysqli_query($Verbindung,$SQL_HW_UPD);		
			
			// Hinweiscodes in Kreisebene setzen
			if(substr($ags,5,3)=='000')
			{
				echo "<br />".$SQL_HW_UPD = "UPDATE m_indikatorwerte_".$Zeitschnitt." SET HINWEISCODE = '".$HC."' WHERE AGS = '".substr($ags,0,5)."' AND ID_INDIKATOR = '".@mysqli_result($Ergebnis_HW_SELECT,$i,'Indikator')."'";
				$Ergebnis_HW_UPD = mysqli_query($Verbindung,$SQL_HW_UPD);
			}
			
			// Hinweiscodes in Bundeslandebene setzen
			if(substr($ags,2,7)=='000000')
			{
				echo $SQL_HW_UPD = "UPDATE m_indikatorwerte_".$Zeitschnitt." SET HINWEISCODE = '".$HC."' WHERE AGS = '".substr($ags,0,2)."' AND ID_INDIKATOR = '".@mysqli_result($Ergebnis_HW_SELECT,$i,'Indikator')."'";
				$Ergebnis_HW_UPD = mysqli_query($Verbindung,$SQL_HW_UPD);
			}
			
			$i++;		
		}
		$i_hc++;
	} 
}



?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Unbenanntes Dokument</title>
</head>

<body>

<?php if($SQL_HW_UPD) echo "<br /><br />Alte Hinweiscodes wurden entfernt.<br />
Updates wurden durchgeführt.<br /><br />"; ?>

<form action="" method="post">
Zeitschnitt für Hinweiscode-Update angeben: <input name="Zeitschnitt" type="text" value="<?php echo $Zeitschnitt; ?>" /><br />
<br />
<input name="" type="submit" value="UPDATE" />
</form>

<br />
Bitte stellen Sie sicher, dass die Tabelle h_XXXX existiert und aktuell ist!
</body>
</html>








