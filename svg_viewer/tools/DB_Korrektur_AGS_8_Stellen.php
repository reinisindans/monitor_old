<?php 

// Zu bearbeitendes Jahr (für DB-Name)
$Jahr = "2006";

$Verbindung = mysqli_connect("localhost","monitor","");
mysqli_select_db($Verbindung,"monitor_".$Jahr);
/* 
// Datensätze mit AGS erfassen
$SQL_DS = "SELECT * FROM verwaltungsebenen";
$Ergebnis_DS = mysqli_query($Verbindung,$SQL_DS);
$i_ds=0;
while($ID = @mysqli_result($Ergebnis_DS,$i_ds,'ID')) // Alle Datensätze erfassen
{
	$AGS = mysqli_result($Ergebnis_DS,$i_ds,'AGS');
	$DS_Laenge = strlen($AGS);
	if($DS_Laenge < 8) // Nur Datensätze mit < 8 Stellen bearbeiten
	{
		while($DS_Laenge < 8) // in Schleife mit Nullen auffüllen bis AGS-Länge = 8
		{
			$AGS = $AGS."0"; // eine 0 anfügen
			$DS_Laenge = strlen($AGS); // erneut Länge Prüfen
		}
		// Zurückschreiben in DB mit 8 Stellen
		echo "<br />";
		echo $SQL_DS_UPDATE = "UPDATE verwaltungsebenen SET AGS='".$AGS."' WHERE ID='".$ID."'";
		if($Ergebnis_DS_UPDATE = mysqli_query($Verbindung,$SQL_DS_UPDATE)) echo " ok";
	}
	$i_ds++;
}
 */
// Falschwerte (-9999,9) mit 0 ersetzen
$SQL_I_UPDATE = "UPDATE calc_indikatoren SET indikator = '0' WHERE indikator < '-9000'";
if($Ergebnis_I_UPDATE = mysqli_query($Verbindung,$SQL_I_UPDATE)) echo " Wertebereinigung < ok";

?>