<?php 
set_time_limit(0); // Laufzeit aus unendlich setzen (Programm kann u.U. 10h dauern)
include("../includes_classes/verbindung_mysqli.php");

$Jahr = $_POST['Jahr'];
$Raumebenenname_a = $_POST['Raumebenenname_a'];
$Raumebenenname = $_POST['Raumebenenname'];
// $Tabellentyp = $_POST['Tabellentyp'];










?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>IÖR Monitor</title>
<link href="screen_viewer.css" rel="stylesheet" type="text/css" media="screen" />
<link href="print_viewer.css" rel="stylesheet" type="text/css" media="print" />

<link href="../screen_v2.css" rel="stylesheet" type="text/css" />
<style type="text/css">
body,td,th {
	color: #CCC;
	font-size: 16px;
}
a:link {
	color: #99CCFF;
}
a:visited {
	color: #99CCFF;
}
a:hover {
	color: #99CCFF;
}
a:active {
	color: #99CCFF;
}
</style>
</head>
<body>
<div style="width:900px; margin:auto;">
<form action="" method="post">
  <h2>Übertragung geänderter Gebietsnamen von einer korrigierten Tabelle in andere Tabellen (z.Z. Ergänzung Stadt/Landkreis)</h2>
  <p>Zeitschnitt (Jahr)<br />
    <input name="Jahr" type="text" value="<?php echo $Jahr; ?>" />
    <br />
    <br />
    <strong>Raumebene bearbeiten:</strong><br />
    <input type="checkbox" name="Raumebenenname_a" value="krs"  checked="checked" >
    Kreisebene</input><br />
    <input name="Raumebenenname" type="checkbox" value="gem" checked="checked" >Gemeindeebene</input> (Kreisebene muss aktiviert oder schon bearbeitet sein!)
    
    <br />
    <br />
   <!-- <select name="Tabellentyp">
      <option value="fein" <?php if($Raumebenenname == "fein") echo "selected"; ?>>fein</option>
      <option value="grob" <?php if($Raumebenenname == "grob") echo "selected"; ?>>grob</option>
    </select> -->
    
    
    <br />
    <input name="Senden" type="submit" value="Ausführen" />
  </p>
</form>

<?php
// Abbruch bei leeren Eingabefeldern
if(!$Jahr)
{
	echo "</body></html>";
	die;
}

?>
<strong style="color:#00FF00;">Prozessierung von: <?php echo $Raumebenenname_a." ".$Raumebenenname." ".$Jahr ; ?></strong>


<br />
<br />
<?php 


if($Raumebenenname_a == 'krs')
{
	// AGS importieren aus PostGIS
	$SQL_PostGIS = "SELECT ags,gen,des FROM vg250_krs_".$Jahr."_fein ORDER BY gen";
	$ERGEBNIS_PGSQL =  pg_query($Verbindung_PostgreSQL,$SQL_PostGIS);  
								
	
	// Duchlauf der gefundenen Datensätze
	$i_v=0;
	while($PG_Zeile = @pg_fetch_assoc($ERGEBNIS_PGSQL,$i_v))
	{
		// merken des vorherigen Datensatzes
		$ags_alt = $ags;
		$gen_alt = $gen;
		$des_alt = $des;
		
		// Daten des nächsten Datensatzes
		$ags = $PG_Zeile['ags'];
		$gen = $PG_Zeile['gen'];
		$des = $PG_Zeile['des'];
		
		// Wenn Namensgleichheit, dann beide Datensätze anpassen
		if($gen == $gen_alt)
		{
			// Kontrollvariablen setzen
			$L_OK = 0;
			$S_OK = 0;
			
			// Check auf Typ Datensatz "alt" => Umbenennung
			// Landkreis
			if($des_alt == "Kreis" or $des_alt == "Landkreis")
			{
				$gen_alt = $gen_alt." (Landkreis)";
				$L_OK = 1;
			}
			// Stadtkreis
			if($des_alt == "Stadtkreis" or $des_alt == "Kreisfreie Stadt")
			{
				$gen_alt = $gen_alt." (Stadt)";
				$S_OK = 1;
			}
			
			// Check auf Typ des aktiven Datensatzes => Umbenennung
			// Landkreis
			if($des == "Kreis" or $des == "Landkreis")
			{
				$gen = $gen." (Landkreis)";
				$L_OK = 1;
			}
			// Stadtkreis
			if($des == "Stadtkreis" or $des == "Kreisfreie Stadt")
			{
				$gen = $gen." (Stadt)";
				$S_OK = 1;
			}
			
			// Ausgabe, wenn nicht zuordenbar, ansonsten in DB ändern
			if(!$S_OK or !$L_OK)
			{
				echo '<br />
				<span style="font-weight:bold; color:#AA0000;">Fehler: Keine Zuordnung möglich:</span><br />
				'.$gen_alt.' '.$des_alt.'<br />
				'.$gen.' '.$des.'<br />';	
			}
			else
			{
				
				
				// Update der Datensätze krs
				$SQL_PostGIS = "UPDATE vg250_krs_".$Jahr."_fein SET gen = '".$gen_alt."' WHERE ags = '".$ags_alt."';";
				$SQL_PostGIS .= "UPDATE vg250_krs_".$Jahr."_fein SET gen = '".$gen."' WHERE ags = '".$ags."';";
				$SQL_PostGIS .= "UPDATE vg250_krs_".$Jahr."_grob SET gen = '".$gen_alt."' WHERE ags = '".$ags_alt."';"; 
				$SQL_PostGIS .= "UPDATE vg250_krs_".$Jahr."_grob SET gen = '".$gen."' WHERE ags = '".$ags."';";
				
				// Update der Datensätze lks
				$SQL_PostGIS .= "UPDATE vg250_lks_".$Jahr."_fein SET gen = '".$gen_alt."' WHERE ags = '".$ags_alt."';"; 
				$SQL_PostGIS .= "UPDATE vg250_lks_".$Jahr."_fein SET gen = '".$gen."' WHERE ags = '".$ags."';";
				$SQL_PostGIS .= "UPDATE vg250_lks_".$Jahr."_grob SET gen = '".$gen_alt."' WHERE ags = '".$ags_alt."';"; 
				$SQL_PostGIS .= "UPDATE vg250_lks_".$Jahr."_grob SET gen = '".$gen."' WHERE ags = '".$ags."';";
				
				// Update der Datensätze kfs
				$SQL_PostGIS .= "UPDATE vg250_kfs_".$Jahr."_fein SET gen = '".$gen_alt."' WHERE ags = '".$ags_alt."';";
				$SQL_PostGIS .= "UPDATE vg250_kfs_".$Jahr."_fein SET gen = '".$gen."' WHERE ags = '".$ags."';";
				$SQL_PostGIS .= "UPDATE vg250_kfs_".$Jahr."_grob SET gen = '".$gen_alt."' WHERE ags = '".$ags_alt."';";
				$SQL_PostGIS .= "UPDATE vg250_kfs_".$Jahr."_grob SET gen = '".$gen."' WHERE ags = '".$ags."';";
				$ERGEBNIS_PGSQL_UPD =  pg_query($Verbindung_PostgreSQL,$SQL_PostGIS); 
				
				if($ERGEBNIS_PGSQL_UPD) 
				{ 
					echo '<br />
					<span style="font-weight:bold; color:#00AA00;">Geänderter Name:</span><br />
					'.$gen_alt.' '.$des_alt.'<br />
					'.$gen.' '.$des.'<br />';
					$UPD_Anz++; 
				}
				else
				{ 
					echo '<br />
					<span style="font-weight:bold; color:#AA0000;">Fehler bei:</span><br />
					'.$gen_alt.' '.$des_alt.'<br />
					'.$gen.' '.$des.'<br />';
					$UPD_Anz_Name++; 
				}
			}
			
		}
		$i_v++;
	}
	
	echo "<br /><br />Ausgeführte Aktionen:<br /><br />";
	echo "<br />UPDATEs: ".$UPD_Anz;
	echo "<br />Fehler: ".$UPD_Anz_Name;
}
// alte AGS checken und Endjahr setzen
// => wenn der Reihe nach und nicht von Hand eingespeist wird, nicht nötig


if($Raumebenenname == 'gem')
{
	// AGS importieren aus PostGIS
	$SQL_PostGIS = "SELECT ags,gen,des FROM vg250_gem_".$Jahr."_fein ORDER BY gen";
	$ERGEBNIS_PGSQL =  pg_query($Verbindung_PostgreSQL,$SQL_PostGIS);  
								
	
	// Duchlauf der gefundenen Datensätze
	$i_v=0;
	while($PG_Zeile = @pg_fetch_assoc($ERGEBNIS_PGSQL,$i_v))
	{
		// merken des vorherigen Datensatzes
		$ags_alt = $ags;
		$gen_alt = $gen;
		$des_alt = $des;
		
		// Daten des nächsten Datensatzes
		$ags = $PG_Zeile['ags'];
		$gen = $PG_Zeile['gen'];
		$des = $PG_Zeile['des'];
		
		// Namen mit Klammeranhang nicht verändern! => verhindert z.B. doppelte Bearbeitung
		// Test: if(strpos($gen,'(')) echo "<br />Klammer:".$gen;
			
		
		// Wenn Namensgleichheit, dann beide Datensätze anpassen
		if($gen == $gen_alt and !$Klammer_vorhanden = strpos($gen,'('))
		{
			// Kontrollvariablen setzen
			$L_OK = 0;
			$S_OK = 0;
			
			// Ermitteln des übergeordneten Kreisnamens
			// Erster DS
				$SQL_PostGIS = "SELECT gen FROM vg250_krs_".$Jahr."_fein WHERE AGS = '".substr($ags_alt,0,5)."'";
				$ERGEBNIS_PGSQL_K =  pg_query($Verbindung_PostgreSQL,$SQL_PostGIS);  
				$PG_ZeileK = @pg_fetch_assoc($ERGEBNIS_PGSQL_K,0);
				$gen_alt_UPD = $gen_alt." (".$PG_ZeileK['gen'].")";
			
			// Zweiter DS
				$SQL_PostGIS = "SELECT gen FROM vg250_krs_".$Jahr."_fein WHERE AGS = '".substr($ags,0,5)."'";
				$ERGEBNIS_PGSQL_K =  pg_query($Verbindung_PostgreSQL,$SQL_PostGIS);  
				$PG_ZeileK2 = @pg_fetch_assoc($ERGEBNIS_PGSQL_K,0);
				$gen_UPD = $gen." (".$PG_ZeileK2['gen'].")";

			

			
			// Ausgabe, wenn nicht zuordenbar, ansonsten in DB ändern (Abgleich der Kreise zum Ausschluss von Exklaven)
			if(!$PG_ZeileK['gen'] or !$PG_ZeileK2['gen'] or substr($ags_alt,0,5) == substr($ags,0,5))
			{
				echo '<br />
				<span style="font-weight:bold; color:#AA0000;">Fehler: Keine Zuordnung möglich oder Exklave oder Beschreibung in Klammern schon vorhanden:</span><br />
				'.$gen_alt.' '.$PG_ZeileK['gen'].'<br />
				'.$gen.' '.$PG_ZeileK2['gen'].'<br />';	
			}
			else
			{

				// Update der Datensätze krs
				$SQL_PostGIS = "UPDATE vg250_gem_".$Jahr."_fein SET gen = '".$gen_alt_UPD."' WHERE ags = '".$ags_alt."';";
				$SQL_PostGIS .= "UPDATE vg250_gem_".$Jahr."_fein SET gen = '".$gen_UPD."' WHERE ags = '".$ags."';";
				$SQL_PostGIS .= "UPDATE vg250_gem_".$Jahr."_grob SET gen = '".$gen_alt_UPD."' WHERE ags = '".$ags_alt."';"; 
				$SQL_PostGIS .= "UPDATE vg250_gem_".$Jahr."_grob SET gen = '".$gen_UPD."' WHERE ags = '".$ags."';";
				$ERGEBNIS_PGSQL_UPD =  pg_query($Verbindung_PostgreSQL,$SQL_PostGIS); 
				
				if($ERGEBNIS_PGSQL_UPD) 
				{ 
					echo '<br />
					<span style="font-weight:bold; color:#00AA00;">Geänderter Name:</span><br />
					'.$gen_alt_UPD.' <br />
					'.$gen_UPD.' <br />';
					$UPD_Anz++; 
				}
				else
				{ 
					echo '<br />
					<span style="font-weight:bold; color:#AA0000;">Fehler bei:</span><br />
					'.$gen_alt_UPD.' <br />
					'.$gen_UPD.' <br />';
					$UPD_Anz_Name++; 
				}

			}
			
		}
		$i_v++;
	}
	
	
	
	
	echo "<br /><br />Ausgeführte Aktionen:<br /><br />";
	echo "<br />UPDATEs: ".$UPD_Anz;
	echo "<br />Fehler: ".$UPD_Anz_Name."<br /><br />";
	
	
	/*  
	// ====> Korrekturdurchlauf bei Fehlerhaften Eintragungen ( Hier Test auf "))" )
	// Entfernen doppelter Klammern
	// AGS importieren aus PostGIS
	$SQL_PostGIS = "SELECT ags,gen,des FROM vg250_gem_".$Jahr."_fein WHERE gen LIKE '%))'";
	$ERGEBNIS_PGSQL =  pg_query($Verbindung_PostgreSQL,$SQL_PostGIS);  
								
	?><br /><br /><strong>Korrektur von Doppelklammern in Ortsnamen mit Kreisanhang</strong><br /><br /><?php 
	// Duchlauf der gefundenen Datensätze
	$i_v=0;
	while($PG_Zeile = @pg_fetch_assoc($ERGEBNIS_PGSQL,$i_v))
	{
		// merken des vorherigen Datensatzes
		$ags = $PG_Zeile['ags'];
		echo "<br />".$gen = $PG_Zeile['gen'];
		$Klammer_1 = strpos($gen,'(') ;
		$gen_Teil = substr($gen,$t = $Klammer_1 + 1);
		$gen_Teil_Korrektur_1 = str_replace('(','[',$gen_Teil);
		$gen_Teil_Korrektur_2 = str_replace('))','])',$gen_Teil_Korrektur_1);
		echo " > ".$gen_ok = substr($gen,0,$t = $Klammer_1 + 1).$gen_Teil_Korrektur_2;
		$des = $PG_Zeile['des'];
		
		$SQL_PostGISg1 = "UPDATE vg250_gem_".$Jahr."_fein SET gen='".$gen_ok."' WHERE ags='".$ags."';";
		$ERGEBNIS_PGSQLgen1 =  pg_query($Verbindung_PostgreSQL,$SQL_PostGISg1); 
		$SQL_PostGISg2 = "UPDATE vg250_gem_".$Jahr."_grob SET gen='".$gen_ok."' WHERE ags='".$ags."';";
		$ERGEBNIS_PGSQLgen2 =  pg_query($Verbindung_PostgreSQL,$SQL_PostGISg2); 
		if($ERGEBNIS_PGSQLgen1 and $ERGEBNIS_PGSQLgen2) echo ' > <span style="color:#00AA00;"> ok</span>';
		$i_v++;
	}
	*/
}





?>
</div>
</body>
</html>
