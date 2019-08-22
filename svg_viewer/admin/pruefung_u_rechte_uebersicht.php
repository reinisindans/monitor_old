<?php 
session_start();

// Aufruf nur für Prüfer
if($_SESSION['Dokument']['ViewBerechtigung'] != "0")
{
 die;
 echo 'Zugriff verweigert';
}

include("../includes_classes/verbindung_mysqli.php");

//DB bereinigen und Einträge mit ID_Indikator= "NEU" löschen

if($_POST['neu_loeschen'])
{
	$SQL_neu_loeschen = "DELETE FROM m_indikatoren WHERE ID_INDIKATOR = 'NEU'";
	$Ergebnis_neu_loeschen = mysqli_query($Verbindung,$SQL_neu_loeschen);
	$SQL_neu_loeschen2 = "DELETE FROM m_indikator_freigabe WHERE ID_INDIKATOR = 'NEU'";
	$Ergebnis_neu_loeschen2 = mysqli_query($Verbindung,$SQL_neu_loeschen2);
		if(mysqli_query($Verbindung,$SQL_neu_loeschen))
		{
	    echo "DB-Inhalte mit ID=NEU gelöscht.";
	    echo"<br/>";    
		} 
		else {
    echo "ERROR: Could not execute $SQL_neu_loeschen. " . mysqli_error($Verbindung);
		}
}


// Neuem Indikator hinzufügen
if($_POST['kat_neu'])
{
	// Höchste Sortierungsnummer herausfinden 
	echo $SQL_hSn = "SELECT MAX(SORTIERUNG) AS MaxSort FROM m_indikatoren WHERE ID_THEMA_KAT = '".$_POST['kat_neu']."'";
	$Ergebnis_hSn = mysqli_query($Verbindung,$SQL_hSn);
	$hSortnr = mysqli_result($Ergebnis_hSn,0,'MaxSort');
	
	// Indikatordatensatz neu
	$Ergebnis_Neu_DS = mysqli_query($Verbindung,"INSERT INTO m_indikatoren (ID_INDIKATOR,ID_THEMA_KAT,SORTIERUNG,INDIKATOR_NAME) VALUES ('NEU','".$_POST['kat_neu']."','".$Srt = ($hSortnr+10)."','Unbenannt')");
	
	// Jahresdatensätze neu
	for($i_jhr = 2006 ; $i_jhr <= date(Y) ; $i_jhr++)
	{
		$Ergebnis_Auffuellung_x = mysqli_query($Verbindung,"INSERT INTO m_indikator_freigabe (ID_INDIKATOR,JAHR) VALUES ('NEU','".$i_jhr."')");
	}

}


// Kategorien-Reihenfolge ändern
if($_POST['kat_up'] or $_POST['kat_down'])
{
	$kat_verschieb = $_POST['kat_up'];
	
	// für Down statt Up anpassen
	if($_POST['kat_down']) 
	{
		$SQL_Reihenfolge = "DESC"; 
		$kat_verschieb = $_POST['kat_down'];
	}
	
	$Ergebnis_up = mysqli_query($Verbindung,"SELECT * FROM m_thematische_kategorien ORDER BY SORTIERUNG_THEMA_KAT ".$SQL_Reihenfolge);
	$i_s=0;
	while(@mysqli_result($Ergebnis_up,$i_s,'ID_THEMA_KAT'))
	{
		if($ID_vorher and @mysqli_result($Ergebnis_up,$i_s,'ID_THEMA_KAT') and $kat_verschieb == @mysqli_result($Ergebnis_up,$i_s,'ID_THEMA_KAT'))
		{
			$ID_this = @mysqli_result($Ergebnis_up,$i_s,'ID_THEMA_KAT');
			$Sort_this = @mysqli_result($Ergebnis_up,$i_s,'SORTIERUNG_THEMA_KAT');
			
			$Ergebnis_up_1 = mysqli_query($Verbindung,"UPDATE m_thematische_kategorien SET SORTIERUNG_THEMA_KAT='".$Sort_vorher."' WHERE ID_THEMA_KAT = '".$ID_this."'");
			$Ergebnis_up_2 = mysqli_query($Verbindung,"UPDATE m_thematische_kategorien SET SORTIERUNG_THEMA_KAT='".$Sort_this."' WHERE ID_THEMA_KAT = '".$ID_vorher."'");
			
			$i_s=1111111111111; // Schleife beenden
		}
		
		$ID_vorher = @mysqli_result($Ergebnis_up,$i_s,'ID_THEMA_KAT');
		$Sort_vorher =@mysqli_result($Ergebnis_up,$i_s,'SORTIERUNG_THEMA_KAT');
		$i_s++; 
	}
} 

// Indikatoren-Reihenfolge ändern
if($_POST['ind_up'] or $_POST['ind_down'])
{
	$ind_verschieb = $_POST['ind_up'];
	
	// für Down statt Up anpassen
	if($_POST['ind_down']) 
	{
		$SQL_Reihenfolge = "DESC"; 
		$ind_verschieb = $_POST['ind_down'];
	}
	
	$Ergebnis_up = mysqli_query($Verbindung,"SELECT * FROM m_indikatoren WHERE ID_THEMA_KAT = '".$_POST['kat_ind']."' ORDER BY SORTIERUNG ".$SQL_Reihenfolge);
	$i_s=0;
	while(@mysqli_result($Ergebnis_up,$i_s,'ID_INDIKATOR'))
	{
		if($ID_vorher and @mysqli_result($Ergebnis_up,$i_s,'ID_INDIKATOR') and $ind_verschieb == @mysqli_result($Ergebnis_up,$i_s,'ID_INDIKATOR'))
		{
			$ID_this = @mysqli_result($Ergebnis_up,$i_s,'ID_INDIKATOR');
			$Sort_this = @mysqli_result($Ergebnis_up,$i_s,'SORTIERUNG');
			
			$Ergebnis_up_1 = mysqli_query($Verbindung,"UPDATE m_indikatoren SET SORTIERUNG='".$Sort_vorher."' WHERE ID_INDIKATOR = '".$ID_this."'");
			$Ergebnis_up_2 = mysqli_query($Verbindung,"UPDATE m_indikatoren SET SORTIERUNG='".$Sort_this."' WHERE ID_INDIKATOR = '".$ID_vorher."'");
			
			$i_s=1111111111111; // Schleife beenden
		}
		
		$ID_vorher = @mysqli_result($Ergebnis_up,$i_s,'ID_INDIKATOR');
		$Sort_vorher =@mysqli_result($Ergebnis_up,$i_s,'SORTIERUNG');
		$i_s++; 
	}
} 

// Auffüllen der DB mit Statusdatensätzen nach vorhandensein von Datensätzen in der Tabelle: v_geometrie_jahr_viewer_postgis
$Ergebnis_Auffuellung_exist_Jahrestest = mysqli_query($Verbindung,"SELECT * FROM v_geometrie_jahr_viewer_postgis");
$i_jv = 0;
while($Jahr_im_Viewer = @mysqli_result($Ergebnis_Auffuellung_exist_Jahrestest,$i_jv,'Jahr_im_Viewer'))
{

	// für Indikatoren
	$SQL_Auffuellung = "SELECT * FROM m_indikatoren";
	$Ergebnis_Auffuellung = mysqli_query($Verbindung,$SQL_Auffuellung);
	
	$i_id = 0;
	while($ID_I = @mysqli_result($Ergebnis_Auffuellung,$i_id,'ID_INDIKATOR'))
	{
		$SQL_Auffuellung_2 = "SELECT * FROM m_indikator_freigabe WHERE ID_INDIKATOR = '".$ID_I."' AND JAHR = '".$Jahr_im_Viewer."'";
		$Ergebnis_Auffuellung_2 = mysqli_query($Verbindung,$SQL_Auffuellung_2);
		
		if(!@mysqli_result($Ergebnis_Auffuellung_2,0,'ID_INDIKATOR'))
		{
			$Ergebnis_Auffuellung_x = mysqli_query($Verbindung,"INSERT INTO m_indikator_freigabe (ID_INDIKATOR,JAHR) VALUES ('".$ID_I."','".$Jahr_im_Viewer."')");
		}
		
		$i_id++;
	}
		
	// für Kategorien
	$SQL_Auffuellung = "SELECT * FROM m_thematische_kategorien";
	$Ergebnis_Auffuellung = mysqli_query($Verbindung,$SQL_Auffuellung);
	
	$i_id = 0;
	while($ID_K = @mysqli_result($Ergebnis_Auffuellung,$i_id,'ID_THEMA_KAT'))
	{
		$SQL_Auffuellung_2 = "SELECT * FROM m_them_kategorie_freigabe WHERE ID_THEMA_KAT = '".$ID_K."' AND JAHR = '".$Jahr_im_Viewer."'";
		$Ergebnis_Auffuellung_2 = mysqli_query($Verbindung,$SQL_Auffuellung_2);
		
		if(!@mysqli_result($Ergebnis_Auffuellung_2,0,'ID_THEMA_KAT'))
		{
			$Ergebnis_Auffuellung_x = mysqli_query($Verbindung,"INSERT INTO m_them_kategorie_freigabe (ID_THEMA_KAT,JAHR) VALUES ('".$ID_K."','".$Jahr_im_Viewer."')");
		}
		
		$i_id++;
	}
	$i_jv++;
}

// Auf und Zuklappen der Kategorien ermöglichen
if($_GET['Aufklapp'] == $_GET['Aufklapp_alt'])
{
	$Aufklapp = "";
}
else
{
	$Aufklapp = $_GET['Aufklapp'];
}


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Prüferübersicht</title>
<link href="../screen_viewer.css" rel="stylesheet" type="text/css" />
<style type="text/css">

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
<a name="oben" id="oben"></a><br />
<h2>Freigaben der Kategorien und deren Indikatoren</h2>
<!--<a href="../svg_html.php" target="_self"><span class="button_standard_abschicken_a" style="padding-left:5px; padding-right:5px; background: #B7CDA0">Zur&uuml;ck zur Karte</span></a> -->
&nbsp;Hinweis: <br />
&nbsp;Die Karte ist für die weitere Arbeit im alten Fenster noch offen, da diese Anwendung in einem neuen Fenster geöffnet wurde.
<br />
<br />
<a href="admin_info.php" target="_top"><span class="button_blau_abschicken">&nbsp;Mehr Informationen/Links für die Administration&nbsp;</span></a><br />
<br />
<br />
Sollten noch alte Einträge in m_indikatoren und m_indikator_freigabe mit ID "NEU" vorkommen, so können diese hier aus der Datenbank entfernt werden.<br/>Empfohlen ehe ein neuer Indikator angelegt wird.
		<form action="" method="post">
			<input name="neu_loeschen" type="hidden" value="neu_loeschen" />
			<input type="submit" class="button_standard_abschicken_a" style="padding-left:5px; padding-right:5px;" value="Indikator NEU loeschen"/>
            <input name="Aufklapp" type="hidden" value="<?php echo $Aufklapp; ?>" />
		</form>

<br />


<?php 

// Kategorien erfassen und anzeigen
// -----------------------------------------------------------------------------------------------------
$SQL_KAT = "SELECT * FROM m_thematische_kategorien ORDER BY SORTIERUNG_THEMA_KAT";
$Ergebnis_KAT = mysqli_query($Verbindung,$SQL_KAT);

$i_kat=0;
while($Kat = @mysqli_result($Ergebnis_KAT,$i_kat,'ID_THEMA_KAT'))
{
	$Kat_Name = utf8_encode(mysqli_result($Ergebnis_KAT,$i_kat,'THEMA_KAT_NAME'));
	// echo "<h2>".$Kat.": ".$Kat_Name."</h2><br />";
	?>
    <a name="<?php echo $Kat; ?>" id="<?php echo $Kat; ?>"></a> <!-- Anker für Positionierung aus Viewer heraus --> 
	<div style=" padding:10px; border:#666666 solid 1px; margin:5px; background:#FFFFFF; width:800px;">
    
    <a href="?Aufklapp=<?php echo $Kat; ?>&Aufklapp_alt=<?php echo $Aufklapp; ?>" target="_self" class="button_standard_abschicken_a" style="margin-left:4px; padding-left:5px; padding-right:5px; font-weight:bold; 
    																																													background:#EFEFEF;"><?php 
	
	// Symbol Auf-, Zuklapp anzeigen
	if($Aufklapp == $Kat)
	{
		?><img src="../gfx/symbol_aufgeklappt.png" alt="offen" /> <?php 			
	}
	else
	{
		?><img src="../gfx/symbol_zugeklappt.png" alt="geschlossen" /> <?php			
	}
	echo $Kat.": ".$Kat_Name; 
	?></a>
        
    <div style="width:100%; text-align:right;">
    <form action="" method="post" style="margin:1px; padding:0px;">
      <input name="" type="image" src="../gfx/button_up.png"/>
      <input name="kat_up" type="hidden" value="<?php echo $Kat; ?>" />
      <input name="Aufklapp" type="hidden" value="<?php echo $Aufklapp; ?>" />
    </form>
    <form action="" method="post" style="margin:1px; padding:0px;">
    	<input name="" type="image" src="../gfx/button_down.png"/>
        <input name="kat_down" type="hidden" value="<?php echo $Kat; ?>" /> 
        <input name="Aufklapp" type="hidden" value="<?php echo $Aufklapp; ?>" />       
    </form>
    </div>
  <table>
		<tr>
			<?php 
			// Freigabe für Kategorie pro Jahr anzeigen
			$SQL_KAT_Freigabe = "SELECT * FROM m_them_kategorie_freigabe,m_status_freigabe 
			WHERE m_them_kategorie_freigabe.STATUS_KATEGORIE_FREIGABE = m_status_freigabe.STATUS_FREIGABE 
			AND ID_THEMA_KAT = '".$Kat."' ORDER BY JAHR";
			$Ergebnis_KAT_Freigabe = mysqli_query($Verbindung,$SQL_KAT_Freigabe);
			$i_katf = 0;
			while($Jahr = @mysqli_result($Ergebnis_KAT_Freigabe,$i_katf,'JAHR'))
			{

				$Farbe_Button = mysqli_result($Ergebnis_KAT_Freigabe,$i_katf,'STATUS_FARBCODE')
				?>
				<td style=" width:60px;">
					<form action="kat_bearb.php#oben" method="post" target="_self">
			      <input type="submit" class="button_standard_abschicken_a" style=" padding-left:5px; padding-right:5px; background-color:#<?php echo $Farbe_Button; ?>;" 
                        							value="<?php echo mysqli_result($Ergebnis_KAT_Freigabe,$i_katf,'JAHR'); ?>"/>
						<input name="ID_KAT" type="hidden" value="<?php echo $Kat; ?>" />
						<input name="Jahr" type="hidden" value="<?php echo mysqli_result($Ergebnis_KAT_Freigabe,$i_katf,'JAHR'); ?>" />
                        <input name="Aufklapp" type="hidden" value="<?php echo $Aufklapp; ?>" />
					</form>
		  		</td>
				<?php 
				$i_katf++;
			}	
			?>
           <!--
           echo mysqli_result($Ergebnis_KAT_Freigabe,$i_katf,'Jahr');
           
           
            <td style=" width:120px;" >
               	<form action="#<?php echo $Kat; ?>" method="post">
                   	<input name="aktion" type="hidden" value="neu" />
                   	<input name="Kat" type="hidden" value="<?php echo $Kat; ?>" />
					<input name="Jahr" type="text" value="" size="4" maxlength="4" />
                    <input name="Aufklapp" type="hidden" value="<?php echo $Aufklapp; ?>" />
                    <input name="Senden" type="submit" class="button_standard_abschicken" value="Neu!" />
				</form>
            </td> -->
	  </tr>
  </table>
    
	<?php 
	
	// Indikatoren
	// -----------------------------------------------------------------------------------------------------
	// Anzeigen, wenn gewählt
	if($Aufklapp == $Kat)
	{
		$SQL_IND = "SELECT * FROM m_indikatoren WHERE ID_THEMA_KAT = '".$Kat."' ORDER BY SORTIERUNG";
		$Ergebnis_IND = mysqli_query($Verbindung,$SQL_IND);
		
		$i_ind=0;
		while($Ind = @mysqli_result($Ergebnis_IND,$i_ind,'ID_INDIKATOR'))
		{
			?>
  			<div style="border-top:#666666 solid 1px;"></div>
            <a name="<?php echo $Ind; ?>" id="<?php echo $Ind; ?>"></a><?php 
			$Ind_Name = utf8_encode(mysqli_result($Ergebnis_IND,$i_ind,'INDIKATOR_NAME'));
			?>
            <table style="background:#EEEEEE; width:90%;">
				<tr>
                	<td valign="top" style="width:50px;">
                    <form action="" method="post" style="margin:1px; padding:0px;">
                          <input name="" type="image" src="../gfx/button_up.png"/>
                          <input name="ind_up" type="hidden" value="<?php echo $Ind; ?>" />
                          <input name="kat_ind" type="hidden" value="<?php echo $Kat; ?>" />
                          <input name="Aufklapp" type="hidden" value="<?php echo $Aufklapp; ?>" />
                      </form>
                        <form action="" method="post" style="margin:1px; padding:0px;">
                            <input name="" type="image" src="../gfx/button_down.png"/>
                            <input name="ind_down" type="hidden" value="<?php echo $Ind; ?>" /> 
                            <input name="kat_ind" type="hidden" value="<?php echo $Kat; ?>" /> 
                            <input name="Aufklapp" type="hidden" value="<?php echo $Aufklapp; ?>" />      
                      </form>                   
                    </td>
                	<td valign="top" >
					<?php echo $Ind." ".$Ind_Name;?>
                    </td>
                </tr>
            </table>
            
			
			
			<table>
				<tr>
                  <td valign="top" style="width:50px;">
                  </td>
		    		<?php 
					// Freigabe für Kategorie pro Jahr anzeigen
					$SQL_IND_Freigabe = "SELECT * FROM m_indikator_freigabe,m_status_freigabe,m_status_pruefung 
					WHERE m_indikator_freigabe.STATUS_INDIKATOR_FREIGABE = m_status_freigabe.STATUS_FREIGABE 
					AND m_indikator_freigabe.STATUS_INDIKATOR_PRUEFG = m_status_pruefung.STATUS_PRUEFUNG 
					AND ID_INDIKATOR = '".$Ind."' ORDER BY JAHR";
					$Ergebnis_IND_Freigabe = mysqli_query($Verbindung,$SQL_IND_Freigabe);
					$i_indf = 0;
					while($Jahr = @mysqli_result($Ergebnis_IND_Freigabe,$i_indf,'JAHR'))
					{
		
						$Farbe_Button = mysqli_result($Ergebnis_IND_Freigabe,$i_indf,'STATUS_FARBCODE');
						$Farbe_Button_Text = mysqli_result($Ergebnis_IND_Freigabe,$i_indf,'STATUS_PRUEFG_FARBCODE');
						?>
						
				  <td style=" <?php if(($_GET['Ind'] == $Ind or $_POST['Ind'] == $Ind) and($_POST['Jhr'] == $Jahr or $_GET['Jhr'] == $Jahr)) 
									{
										echo 'border:solid 3px #990000; padding-left:5px; width:55px; '; 
									}
									else
									{
										echo "width:60px; ";
									}
									?> 
                        ">	
               		  <form action="ind_bearb.php#oben" method="post" target="_self">
               		    <input type="submit" class="button_standard_abschicken_a" style=" color:#<?php echo $Farbe_Button_Text; 
								?>; padding-left:5px; padding-right:5px; background-color:#<?php echo $Farbe_Button; 
								?>;" value="<?php echo mysqli_result($Ergebnis_IND_Freigabe,$i_indf,'JAHR'); ?>"/>
               		    <input name="ID_IND" type="hidden" value="<?php echo $Ind; ?>" />
						<input name="Jahr" type="hidden" value="<?php echo mysqli_result($Ergebnis_IND_Freigabe,$i_indf,'JAHR'); ?>" />
                        <input name="Aufklapp" type="hidden" value="<?php echo $Aufklapp; ?>" />
					  </form>						</td>
				  <?php 
						$i_indf++;
					}	
					?>
                    <!--<td style=" width:120px;" >
                    	<form action="#<?php echo $Kat; ?>" method="post">
                        	<input name="aktion" type="hidden" value="neu" />
                        	<input name="Ind" type="hidden" value="<?php echo $Ind; ?>" />
							<input name="Jahr" type="text" value="" size="4" maxlength="4" />
                            <input name="Aufklapp" type="hidden" value="<?php echo $Aufklapp; ?>" />
                            <input name="Senden" type="submit" class="button_standard_abschicken" value="Neu!" />
						</form>
                    </td> -->
		      </tr>
			</table>
			<?php 

			$i_ind++;
		}
	}
	
	// Neuen Indikator erstellen
	if($Aufklapp == $Kat)
	{
		?>
		<br />
		<form action="" method="post">
			<input name="kat_neu" type="hidden" value="<?php echo $Kat; ?>" />
			<input type="submit" class="button_standard_abschicken_a" style="padding-left:5px; padding-right:5px;" value="Neuen Indikator erstellen"/>
            <input name="Aufklapp" type="hidden" value="<?php echo $Aufklapp; ?>" />
		</form>
		<?php 
	}
	?>
</div>
	<?php
	
	$i_kat++;
}

?>

</body>
</html>
