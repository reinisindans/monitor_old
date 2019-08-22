<?php 

include("../includes_classes/verbindung_mysqli.php");

// Zu bearbeitendes Jahr
$Jahr = $_POST['jahr'];


?>
<form action="" method="post">
  <input name="jahr" type="text" value="<?php echo $Jahr; ?>" />
  <input type="submit" value="AGS k&uuml;rzen!" />
</form>
<?php

// Nur weitermachen, wenn Jahr übergeben!
if(!$Jahr) die;


// bei krs = 5 Stellen und bld = 2 Stellen


// $AGS_Lnge="8" (ist Originallänge); // bei krs = 5 ; bld = 2 ; bei gem = 8
$Raumebene="krs";
$AGS_Lnge="5";

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

$Raumebene="kfs";
$AGS_Lnge="5";

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


$Raumebene="lks";
$AGS_Lnge="5";

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



$Raumebene="bld";
$AGS_Lnge="2";

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

echo "Überzählige Stellen bei krs_".$Jahr.",  kfs_".$Jahr.",  lks_".$Jahr." und bld_".$Jahr." gelöscht:<br />";

// ---------------------------------------- aktuell nicht mehr benötigt ---------------------------------
/* // löschen von Seegebieten o.Ä.
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
 */

?>