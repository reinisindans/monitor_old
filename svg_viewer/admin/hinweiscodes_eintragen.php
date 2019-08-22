<?php 
// Zeit-Limit erhöhen
ini_set('max_execution_time', '500');

include("../includes_classes/verbindung_mysqli.php");





// Vervielfältigung für Indikatoren ...
$SQL_HW = "SELECT * FROM h_2010";
$Ergebnis_HW = mysqli_query($Verbindung,$SQL_HW);		
$i_hc=0;
while(@mysqli_result($Ergebnis_HW,$i_hc,'AGS'))
{
	
	// Indikatoren ...
	$SQL_I = "SELECT * FROM m_indikatoren";
	$Ergebnis_I = mysqli_query($Verbindung,$SQL_I);		
	$i_i=0;
	while($Indikator = @mysqli_result($Ergebnis_I,$i_i,'ID_INDIKATOR'))
	{
	 	$SQL_INS = "INSERT INTO h_2010 (AGS,Code,Indikator) VALUES ('".@mysqli_result($Ergebnis_HW,$i_hc,'AGS')."','".@mysqli_result($Ergebnis_HW,$i_hc,'Code')."','".$Indikator."');";
		$Ergebnis_INS = mysqli_query($Verbindung,$SQL_INS);
		
		$i_i++;	
	}
	$i_hc++;	
}




?>






