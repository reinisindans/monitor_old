<?php 
session_start();
include("../includes_classes/verbindung_mysqli.php");




// Array mit Wortdefinitionen in mehreren Sprachen

$Sprache_Ausgabe['DE']['WMS_BOX'] = 'URL für den WMS-Service:'; 
$Sprache_Ausgabe['EN']['WMS_BOX'] = 'Link to WMS:';
$Sprache_Ausgabe['DE']['WCS_BOX'] = 'URL für den WCS-Service:'; 
$Sprache_Ausgabe['EN']['WCS_BOX'] = 'Link to WCS:';
$Sprache_Ausgabe['DE']['WFS_BOX'] = 'URL für den WFS-Service:'; 
$Sprache_Ausgabe['EN']['WFS_BOX'] = 'Link to WFS:';

//$Kat = $_GET['KAT'];
//$Indi = $_GET['IND'];

if($_SESSION['Dokument']['Sprache'] == 'DE')
{
$langwms ="";
}
if($_SESSION['Dokument']['Sprache'] == 'EN')
{
$langwms ="LANGUAGE=eng";
}



?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Indikatoren</title>
<!-- JQUERY lib für JQuery Dialog via WFS-->
<script src="../lib/jquery/external/jquery/jquery.js"></script>
<link href="../lib/jquery/jquery-ui.min.css" rel="stylesheet"/>
<script src="../lib/jquery/jquery-ui.js"></script>
<script src="../lib/jquery/jquery-ui.min.js"></script>
<script src="../lib/jquery/jquery.ui.touch-punch.min.js"></script>
<link href="../lib/jquery/jquery-ui.theme.css" rel="stylesheet">

<!--Bootstrap-->
<link href="../lib/bootstrap/css/bootstrap.min.css" rel="stylesheet">
<script src="../lib/bootstrap/js/bootstrap.min.js"></script>

<!--fontawesome für Symbole Buttons-->

<link rel="stylesheet" href="../lib/font-awesome/css/font-awesome.min.css">

<link href="../screen_viewer.css" rel="stylesheet" type="text/css" />

<style type="text/css">

html, body {
	background-color:rgb(242, 242, 242);
	
	}



</style>
<script type="text/javascript">
//Links zu OGC-Diensten generieren
	function url_anzeigen(id,langwms)
	{
	
		var trimlang = langwms.trim();		 //Sprache berücksichtigen
		document.getElementById('url_'+ id).innerHTML='https://monitor.ioer.de/cgi-bin/wms?MAP=' + id +'_wms&'+trimlang;
		document.getElementById('urlwcs_' + id).innerHTML='https://monitor.ioer.de/cgi-bin/wcs?MAP=' + id + '_wcs';
		document.getElementById('urlwfs_'+ id).innerHTML='https://monitor.ioer.de/cgi-bin/wfs?MAP=' + id  + '_wfs';

	}
	</script>

</head>
<body style="color:#555555; background-color:rgb(242, 242, 242);">
	
	

 

<?php		
// Indikatorenauflistung
// -----------------------------------------------------------------------------------------------------
// Anzeigen, wenn gewählt

foreach($_GET['IND'] as $Indi){
		$SQL_IND = "SELECT m_indikatoren.ID_INDIKATOR,m_indikatoren.INDIKATOR_NAME, m_indikatoren.INDIKATOR_NAME_EN, INFO_VIEWER_ZEILE_1, INFO_VIEWER_ZEILE_2, INFO_VIEWER_ZEILE_3, INFO_VIEWER_ZEILE_4,
		INFO_VIEWER_ZEILE_5, INFO_VIEWER_ZEILE_6, INFO_VIEWER_ZEILE_1_EN, INFO_VIEWER_ZEILE_2_EN, INFO_VIEWER_ZEILE_3_EN, INFO_VIEWER_ZEILE_4_EN,	INFO_VIEWER_ZEILE_5_EN, INFO_VIEWER_ZEILE_6_EN FROM m_indikatoren
											WHERE ID_INDIKATOR = '".$Indi."' 
											
											 
											GROUP BY ID_INDIKATOR
											ORDER BY SORTIERUNG";
		$Ergebnis_IND = mysqli_query($Verbindung,$SQL_IND);
		
		$i_ind=0;

$Ind = @mysqli_result($Ergebnis_IND,$i_ind,'ID_INDIKATOR')
		
						?>
			    <a name="<?php echo $Ind; ?>" id="<?php echo $Ind; ?>"></a><?php 
					if($_SESSION['Dokument']['Sprache'] == 'DE')
					{
						$Ind_Name = utf8_encode(mysqli_result($Ergebnis_IND,$i_ind,'INDIKATOR_NAME'));
					}
						if($_SESSION['Dokument']['Sprache'] == 'EN')
					{
						$Ind_Name = utf8_encode(mysqli_result($Ergebnis_IND,$i_ind,'INDIKATOR_NAME_EN'));
					}
						?>
						
<!--Zeilen--->	
		<div>
			<div class="ind-names">
			<!--Bevölkerungshinweis-->
			    		<?php 
			    		$vgl = strpos($Ind_Name,"Einwohner");
			    			$vgl1 = strpos($Ind_Name,"Siedlungsdichte");
			    				$vgl2 = strpos($Ind_Name,"Person");
			    					$vgl3 = strpos($Ind_Name,"Verkehrsflächennutzungsdichte");
			    		
			    		
			    		if($vgl !== false || $vgl1 !== false || $vgl2 !== false || $vgl3 !== false) {?> 
			   		    	<i class='fa fa-male' aria-hidden='true' <?php if($_SESSION['Dokument']['Sprache'] == 'DE') 
											{?> 
													title='Bevölkerungsbezogener Indikator'
												 <?php }
			                    	if($_SESSION['Dokument']['Sprache'] == 'EN') 
											{?> 
													title='Population-based indicator'
												   <?php } ?> ></i> 
												   <?php }?>
			    	<!--Name-->
			    	<?php echo $Ind_Name;?> 
			    </div>
			    	<div class="symbols">
			    	<!--info-Zeichen-->
			       <a class="button-text" href="http://www.ioer-monitor.de/?id=44&ID_IND=<?php 
			                                echo $Ind; if($_SESSION['Dokument']['Sprache'] == 'EN'){echo '&L=2';}?>" 
			                                target="_blank" <?php if($_SESSION['Dokument']['Sprache'] == 'DE') 
											{?> 
													title="<?php echo  utf8_encode(@mysqli_result($Ergebnis_IND,$i_ind,'INFO_VIEWER_ZEILE_1')),
													   utf8_encode(@mysqli_result($Ergebnis_IND,$i_ind,'INFO_VIEWER_ZEILE_2')),
												   utf8_encode(@mysqli_result($Ergebnis_IND,$i_ind,'INFO_VIEWER_ZEILE_3')),
													  utf8_encode(@mysqli_result($Ergebnis_IND,$i_ind,'INFO_VIEWER_ZEILE_4')),
												 utf8_encode(@mysqli_result($Ergebnis_IND,$i_ind,'INFO_VIEWER_ZEILE_5')),
												  utf8_encode(@mysqli_result($Ergebnis_IND,$i_ind,'INFO_VIEWER_ZEILE_6'));?>
												  "
												 <?php }
			                    	if($_SESSION['Dokument']['Sprache'] == 'EN') 
											{?> 
													title="<?php echo  utf8_encode(@mysqli_result($Ergebnis_IND,$i_ind,'INFO_VIEWER_ZEILE_1_EN')),
													   utf8_encode(@mysqli_result($Ergebnis_IND,$i_ind,'INFO_VIEWER_ZEILE_2_EN')),
												   utf8_encode(@mysqli_result($Ergebnis_IND,$i_ind,'INFO_VIEWER_ZEILE_3_EN')),
													  utf8_encode(@mysqli_result($Ergebnis_IND,$i_ind,'INFO_VIEWER_ZEILE_4_EN')),
												 utf8_encode(@mysqli_result($Ergebnis_IND,$i_ind,'INFO_VIEWER_ZEILE_5_EN')),
												  utf8_encode(@mysqli_result($Ergebnis_IND,$i_ind,'INFO_VIEWER_ZEILE_6_EN'));?>
												  "	
												   <?php } ?> ><i class="fa fa-info-circle" aria-hidden="true"></i>&nbsp;Info&nbsp;
							</a>
							
							<!--Karte-->
			       <a class="button-text" href="https://monitor.ioer.de/?ind=<?php echo $Ind ?>&time=2017<?php if($Ind==O10RT || $Ind==O11RT || $Ind==U51RG|| $Ind==R06RT|| $Ind==B31RT){echo '&raeumliche_gliederung=raster';}?>"  target="_blank" 
			              <?php if($_SESSION['Dokument']['Sprache'] == 'DE') {?> title="Karte anzeigen" <?php }
			                    	if($_SESSION['Dokument']['Sprache'] == 'EN'){?> title="Show map" <?php } ?> ><i class="fa fa-globe" aria-hidden="true"></i><?php if($_SESSION['Dokument']['Sprache'] == 'DE') {?>&nbsp;Karte&nbsp;                  		
			                    		<?php }
			                    	if($_SESSION['Dokument']['Sprache'] == 'EN'){?>&nbsp;Map&nbsp;<?php } ?> 
			                    		
							</a>
							
										<!--Dienste-->
			       <a class="button-text" onclick="url_anzeigen('<?php echo $Ind; ?>',' <?php echo $langwms; ?>')" data-toggle="collapse" role="button" href="#AUSGABETEXT_<?php echo $Ind?>"  aria-expanded="false" aria-controls="AUSGABETEXT_<?php echo $Ind?>" 
			          <?php if($_SESSION['Dokument']['Sprache'] == 'DE') 
											{?> 
													title="Links der Geodienste (WMS/WCS/WFS)"
												  
												 <?php }
			                    	if($_SESSION['Dokument']['Sprache'] == 'EN') 
											{?> 
													title="Links for geoservices (WMS/WCS/WFS)"
												  	
												   <?php } ?> ><i class="fa fa-external-link-square" aria-hidden="true"></i>&nbsp;Export&nbsp;
							</a>
							
							
				  <!--WMS/WCS/WFS-Link erfassen und ausgeben
						 ----------------------------------------------------------------------------------------------------->
					
							<div class="collapse dienste_ausgabe"  id="AUSGABETEXT_<?php echo $Ind?>" name="AUSGABETEXT">				    
						     
						    <?php echo $Sprache_Ausgabe[$_SESSION['Dokument']['Sprache']]['WMS_BOX']; ?><br />
						      
								<div  id="url_<?php echo $Ind?>"  name="url"></div>
								 
						     <?php echo $Sprache_Ausgabe[$_SESSION['Dokument']['Sprache']]['WCS_BOX']; ?><br />
						     
								<div  id="urlwcs_<?php echo $Ind?>" name="urlwcs"></div>  
						        <?php echo $Sprache_Ausgabe[$_SESSION['Dokument']['Sprache']]['WFS_BOX']; ?><br />
						       
								<div  id="urlwfs_<?php echo $Ind?>" name="urlwfs"></div>	
							
						  </div>			
    		</div>						
								 
</div>   
<?php
}
?>
</body>
</html>
