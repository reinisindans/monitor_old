<?php
session_start(); // Sitzung starten/ wieder aufnehmen
?>
<!--aktuell genutzte einfache HTML tabelle in neuem Fenster für Quellen der Stadtteile, Dt/Engl versionen-->


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>

<meta charset="UTF-8"/>
	<meta http-equiv="Content-Style-Type" content="text/css"/>
	<meta http-equiv="pragma" content="no-cache"/>
	<meta http-equiv="cache-control" content="no-cache"/>
<title>Quellen Stadtteilgeometrien - IÖR-Monitor</title>
<link href="screen_viewer.css" rel="stylesheet" type="text/css" media="screen" />
<link href="print_viewer.css" rel="stylesheet" type="text/css" media="print" />
<link href="stadtteilquellen.css" rel="stylesheet" type="text/css" media="screen" />

	
<!-- JQUERY lib  -->
<script src="lib/jquery/external/jquery/jquery.js"></script>
<link href="lib/jquery/jquery-ui.min.css" rel="stylesheet"/>
<script src="lib/jquery/jquery-ui.js"></script>
<script src="lib/jquery/jquery-ui.min.js"></script>
<script src="lib/jquery/jquery.ui.touch-punch.min.js"></script>
<link href="lib/jquery/jquery-ui.theme.css" rel="stylesheet">
</head>

<body>
	<?php
	$sprache = $_GET['lan'];

/*if ($_SESSION['Dokument']['Sprache'] == 'DE') { */
	if ($sprache == 'de') {
		 echo'<h2>Quellen der Stadtteilgeometrien</h2>';

		$xmlFile = './XML/Stadtteile.xml';

		if (file_exists($xmlFile)) {
		    $xml = simplexml_load_file($xmlFile);
		 echo '<table>' ; 
		 echo'	<th>Stadt</th> <th>Stadtgliederung</th> <th>Quelle</th>';
		    foreach ( $xml->record as $user )  
		        {  
		   
		     echo '<tr>' ;       
		     echo '<td> ' . $user->Stadt . '</td>';
		           echo '<td> ' . $user->Stadtgliederung . '</td>';  
		           echo '<td> ' . $user->Quellhinweis . '</td>';  
		        echo '</tr>' ;  
		        }  
		        
		         echo '</table>' ; 
		        
				} 
		else {
		    exit("Datei $xmlFile kann nicht geöffnet werden.");
				}
}

 	 	

/*if ($_SESSION['Dokument']['Sprache'] == 'EN') {*/
	if ($sprache == 'en') {
			echo'<h2>Sources of quarter geometries</h2> 	';

		$xmlFile = './XML/Stadtteile.xml';

		if (file_exists($xmlFile)) {
		    $xml = simplexml_load_file($xmlFile);
		 echo '<table>' ; 
		 echo' 	<th>City</th> <th>City subdivision</th> <th>Source</th>';
		    foreach ( $xml->record as $user )  
		        {  
		   
		     echo '<tr>' ;       
		     echo '<td> ' . $user->Stadt . '</td>';
		           echo '<td> ' . $user->Stadtgliederung . '</td>';  
		           echo '<td> ' . $user->Quellhinweis . '</td>';  
		        echo '</tr>' ;  
		        }  
		        
		         echo '</table>' ; 
		        
				} 
		else {
		    exit("File $xmlFile not found.");
				}


}?>

</body>
</html>