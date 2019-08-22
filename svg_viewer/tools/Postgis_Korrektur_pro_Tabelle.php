<?php 

include("includes_classes/verbindung_mysqli.php");

$Jahr="2006";
$Raumebene="krs";



// $AGS_Lnge="8"; // bei krs = 5 ; bld = 2 ; bei gem = 8

switch ($Raumebene) {
	case "bld":
		$AGS_Lnge="2";
	break;
	case "krs":
		$AGS_Lnge="5";
	break;
	case "gem":
		$AGS_Lnge="8";
	break;
	case "":
		die;
	break;
}

$SQL_PostGIS = "SELECT * FROM vg250_".$Raumebene."_".$Jahr;
$ERGEBNIS_PGSQL_AGS =  pg_query($Verbindung_PostgreSQL,$SQL_PostGIS);  
$i_hg=0;
// gefundene Datensätze abarbeiten
while($PG_Zeile = @pg_fetch_assoc($ERGEBNIS_PGSQL_AGS,$i_hg))
{				
	
	$ags_kurz = substr($PG_Zeile['ags'],0,$AGS_Lnge);
	$PSQL = "UPDATE vg250_".$Raumebene."_".$Jahr." SET ags='".$ags_kurz."' WHERE gid='".$PG_Zeile['gid']."'";
	$ERGEBNIS_PGSQL_UPD = pg_query($Verbindung_PostgreSQL,$PSQL); 
	$i_hg++;
}

echo "gelöscht:<br />";

// löschen von Seegebieten o.Ä.
$i_hg=0;
while($PG_Zeile = @pg_fetch_assoc($ERGEBNIS_PGSQL_AGS,$i_hg))
{	
	// ein EWZ = 0
	if($PG_Zeile['ewz'] == 0)
	{
		// nach Dopplungen suchen
		$SQL_PostGIS_dopplung = "SELECT * FROM vg250_".$Raumebene."_".$Jahr." WHERE ags ='".$PG_Zeile['ags']."' AND ewz > 0";
		$ERGEBNIS_PGSQL_AGS_dopplung =  pg_query($Verbindung_PostgreSQL,$SQL_PostGIS_dopplung); 
		$PG_Zeile = @pg_fetch_assoc($ERGEBNIS_PGSQL_AGS_dopplung,0);
		// wenn Dollpung vorhanden, DS mit ewz=0 löschen
		if($PG_Zeile['ags']) 
		{
			$SQL_PostGIS_del = "DELETE FROM vg250_".$Raumebene."_".$Jahr." WHERE ags ='".$PG_Zeile['ags']."' AND ewz = 0";
			$ERGEBNIS_PGSQL_AGS_del =  pg_query($Verbindung_PostgreSQL,$SQL_PostGIS_del); 
			echo "<br />".$PG_Zeile['gen'];
			
		}
	}
	$i_hg++;
}


?>