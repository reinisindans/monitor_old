<!DOCTYPE html>
<html lang="de">
<head>
	<title>URL Check - IÖR-Flächenportal</title>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
	<meta http-equiv="x-ua-compatible" content="ie=edge">
	<meta name="robots" content="index,follow" />
	
	<link href="Css/bootstrap.min.css" rel="stylesheet" type="text/css" />
	<link href="Css/font-awesome.min.css" rel="stylesheet" type="text/css" />
	<link href="Css/animate.min.css" rel="stylesheet" type="text/css" />
	<link href="Css/jquery.mmenu.all.css" rel="stylesheet" type="text/css" />
	<link href="Css/jquery.mmenu.bootstrap.css" rel="stylesheet" type="text/css" />
	<link href="Css/mmenu-extensions/jquery.mmenu.shadows.css" rel="stylesheet" type="text/css" />
	<link href="Css/mmenu-extensions/jquery.mmenu.borderstyle.css" rel="stylesheet" type="text/css" />
	<link href="Css/screen.css" rel="stylesheet" type="text/css" />
	<link href="Css/screen-fp.css" rel="stylesheet" type="text/css" />
	<script language="javascript" src="JavaScript/jquery.min.js" type="text/javascript"></script>
	<!-- <script language="javascript" src="js/modernizr.min.js" type="text/javascript"></script> -->
	<script language="javascript" src="JavaScript/bootstrap.min.js" type="text/javascript"></script>
	<script language="javascript" src="JavaScript/bootstrap-dropdownhover.min.js" type="text/javascript"></script>
	<script language="javascript" src="JavaScript/jquery.mmenu.all.min.js" type="text/javascript"></script>
	<script language="javascript" src="JavaScript/jquery.mmenu.bootstrap.min.js" type="text/javascript"></script>
	<script language="javascript" src="JavaScript/jquery.headroom.js" type="text/javascript"></script>
	<script language="javascript" src="JavaScript/headroom.min.js" type="text/javascript"></script>
	<script language="javascript" src="JavaScript/default.js" type="text/javascript"></script>

	<script language="javascript" src="JavaScript/fp.js" type="text/javascript"></script>
	<script language="javascript" src="JavaScript/admin.js" type="text/javascript"></script>

	

	<?php //$bibkey = $_GET['bibkey'];
	//include 'urlcheck.php';
	$myArray = $_POST['mydataa'];
	$anzahl = count($myArray);
	echo "<p>Es gibt $anzahl Einträge</p>";
	echo "<ul>";

	 
	for ($x = 0; $x < $anzahl; $x++)
	{
		echo "<li>Eintrag von $x ist $myArray[$x] </li>";
	}
	 
	echo "</ul>";
	function js2php_proc() {
		$str = json_decode($_POST['str'], true); 
		echo json_encode($str);
	}

	?>



</head>
	<body>
	
	
<div class="page">
	
<nav class="navbar navbar-default">
	<div class="container">
		<div class="navbar-header">
			<a class="navbar-toggle" href="#mobile-navbar">
				<span>Menu&nbsp;</span>
				<i class="fa fa-navicon"></i>
			</a>
			<a class="navbar-brand" href="http://www.ioer.de/">
				<img alt="Brand" src="Images/logo-ioer.svg">
			</a>
		</div>
		<div id="navbar">
			<ul class="nav navbar-nav navbar-right" data-hover="dropdown" data-animations="fadeIn fadeIn">
				<li class="dropdown ">
					<a href="kat.html" >Kategorien <i class="dropdown-toggle"></i></a>
					<ul class="dropdown-menu">
						<li><a href="ziele.php">Ziele</a></li>
						<li><a href="gesetz.php">Gesetzliche Regelungen</a></li>
						<li><a href="stat.php">Statistische Angebote</a></li>
						<li><a href="port.php">Portale</a></li>	
						<li><a href="anw.php">Anwendungen</a></li>
						<li><a href="lit.php">Fachliteratur</a></li>						
					</ul>
				</li>
				<li><a href="news.php">Neuigkeiten</a></li>
				<li><a href="veranstaltungen.php">Veranstaltungen</a></li>
				<li><a href="glossar.php">Glossar</a></li>
				<li class="active"><a href="suche.php">Suche</a></li>
				<li>
						<form class="navbar-form navbar-search" id="searchForm" action="" method="POST">
							<div class="navbar-form navbar-search form-group">
								<div class="input-icon search">
									<input type="text" class="form-control" id="inputSearch" placeholder="Suchen..." name="search"/>		
								</div>
						</div>
				</form>
				</li>
			</ul>
		</div>
	</div>
</nav>
<header>
	<div class="container">
		<div class="row">
			<div class="col-sm-9">
				<h2><a href="index.html">IÖR-Flächenportal</a></h2>
			</div>
			<div class="col-sm-3"></div>
		</div>
	</div>

</header>





	
		<!--Einbinden der Bibtex Quelldatei-->
<!--<bibtex src="Flaechennutzungsportal-la-kurz.bib"></bibtex>-->
<bibtex src="./data/Flaechenportal.bib"></bibtex>

<div id="bibtex_errors"></div>



<div class="bibtex_structure">
  <div class="sections bibtextypekey">
  


	<div class="section @ziele" id="@ZIELE" title="Ziele">
		 <h2 class="hideIfDivEmpty">Ziele</h2>
      <div class="sort date" extra="DESC string">
        <div class="templates"></div>
      </div>	
      	<a href="#top"><em>(nach oben)</em></a>	
		</div>  	
  	<div class="section @fachliteratur" id="@FACHLITERATUR" title="Fachliteratur">
		 <h2 class="hideIfDivEmpty">Fachliteratur</h2>
      <div class="sort date" extra="DESC string">
        <div class="templates"></div>
      </div>	
      	<a href="#top" ><em>(nach oben)</em></a>	
		</div>  	  
	<div class="section @gesetze" id="@GESETZE" title="Gesetzliche Regelungen">
	 <h2 class="hideIfDivEmpty">Gesetzliche Regelungen</h2>
      <div class="sort date" extra="DESC string">
      		
        <div class="templates"></div>
      </div>	
      	<a href="#top" ><em>(nach oben)</em></a>	
		</div>  
					<div class="section @statistik" id="@STATISTIK" title="Statistische Angebote" >
		 <h2 class="hideIfDivEmpty">Statistische Angebote</h2>
      <div class="sort date" extra="DESC string">
        <div class="templates"></div>
      </div>
      	<a href="#top" ><em>(nach oben)</em></a>		
		</div>  		
		<div class="section @portale" id="@PORTALE" title="Portale">
		 <h2 class="hideIfDivEmpty">Portale</h2>
      <div class="sort date" extra="DESC string">
        <div class="templates"></div>
      </div>	
   	<a href="#top"><em>(nach oben)</em></a>   	
		</div>  
						<div class="section @anwendungen" id="@ANWENDUNGEN" title="Anwendungen">
		 <h2 class="hideIfDivEmpty">Anwendungen</h2>
      <div class="sort date" extra="DESC string">
        <div class="templates"></div>
      </div>	
      	<a href="#top" ><em>(nach oben)</em></a>	
		</div> 
		

		<div class="section @inreference" id="@INREFERENCE" title="Glossarbegriffe">
		 <h2 class="hideIfDivEmpty">Glossarbegriffe</h2>
      <div class="sort date" extra="DESC string">
        <div class="templates"></div>
      </div>
      	<a href="#top" ><em>(nach oben)</em></a>		
		</div> 
		
		
		
			<div class="section @misc" id="@misc" title="Sonstige Ziele (@misc)">
		 <h2 class="hideIfDivEmpty">Sonstige Ziele (@misc)</h2>
      <div class="sort date" extra="DESC string">
        <div class="templates"></div>
      </div>	
      	<a href="#top" ><em>(nach oben)</em></a>	
		</div>  
			<div class="section @unpublished|@article" id="@UNPUBLISHED|@ARTICLE" title="Sonstige Fachliteratur (Vortrag/@unpublished@article)">
		 <h2 class="hideIfDivEmpty">Sonstige Fachliteratur (Vortrag/@unpublished@article)</h2>
      <div class="sort date" extra="DESC string">
        <div class="templates"></div>
      </div>	
      	<a href="#top" ><em>(nach oben)</em></a>	
		</div>  
		<div class="section @legislation" id="@LEGISLATION" title="Sonstige gesetzliche Regelungen (Gesetze/@legislation)">
	 <h2 class="hideIfDivEmpty">Sonstige gesetzliche Regelungen (Gesetze/@legislation)</h2>
      <div class="sort date" extra="DESC string">
      		
        <div class="templates"></div>
      </div>	
      	<a href="#top" ><em>(nach oben)</em></a>	
		</div>
	
 
		<div class="section @report" id="@REPORT" title="Sonstige Berichte (@report)">
		 <h2 class="hideIfDivEmpty">Sonstige Berichte (@report)</h2>
      <div class="sort date" extra="DESC string">
        <div class="templates"></div>
      </div>	
      	<a href="#top" ><em>(nach oben)</em></a>	
		</div>  
		<div class="section @online" id="@ONLINE" title="Sonstige Webseite (@online)">
		 <h2 class="hideIfDivEmpty">Sonstige Webseite (@online)</h2>
      <div class="sort date" extra="DESC string">
        <div class="templates"></div>
      </div>	
      	<a href="#top" ><em>(nach oben)</em></a>	
		</div>  
	<!--	<div class="section @incollection" id="@INCOLLECTION" title="anderes(INCOLLECTION)">
		 <h2 class="hideIfDivEmpty">anderes</h2>
      <div class="sort date" extra="DESC string">
        <div class="templates"></div>
      </div>	
      	<a href="#top" ><em>(nach oben)</em></a>	
		</div>  -->

     
       
      </div>
  </div>

<section>
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<ol class="breadcrumb">
					<li><a href="index.html"><i class="fa fa-home"></i></a></li>
					<li class="active">Suche</li>
				</ol>

			
				<h3><i class="fa fa-search"></i>&nbsp;&nbsp;Check URL</h3><hr> <button id="check-start" type="button" class="btn btn-primary">Check starten</button>
				<div id="loading"></div>
				<?php


		
  //if(!($_POST['mydata'])){echo "ja ist da"; echo($urllist);}else{echo "kein post";}
  


// here i would like use foreach:
	//if(!$_POST){echo "ja ist da<br />";}else{echo "kein post<br />";}


/*	$mygetter = $_POST['mydataa'];
$values = json_decode($mygetter);

$data = json_decode(file_get_contents('php://input'), true);
print_r($data);
echo $data["mydataa"][0];*/
//	$urllist = json_encode($_POST['mydata']);
/*	$urllist = $_POST['mydata'];
	echo $urllist; //=NULL
	echo json_encode($_POST['mydata']);
	
	echo "hello";
	$anzahl = count($urllist);
	echo "<p>Es gibt $anzahl Einträge</p>";
	echo "<ul>";

	 
	for ($x = 0; $x < $anzahl; $x++)
	{
		echo "<li>Eintrag von $x ist $urllist[$x] </li>";
	}
	 
	echo "</ul>";*/
//	print_r($_POST); //-->Array{}
	//var_dump($_POST); 	//-->array(0) { } 
	
//	print file_get_contents('php://input'); //nichts wird gedruckt

//var_dump($data->mydata);  //--> NULL
//var_dump($data->_0ddyryc7z); // -->NULL
//echo '<pre>'; print_r($_POST); echo '</pre>'; //--> Array
												//(

												//) in einem Kasten
	//$entityBody = file_get_contents('php://input'); var_dump($entityBody);	//-->string(0) "" 		 					
	//$data = json_decode(file_get_contents('php://input'), true); var_dump($data); //-->NULL
	//$json = file_get_contents('php://input'); $obj = json_decode($json);
	//var_dump($obj);
	

/*
	$str = $_POST;
	function isValidJSON($str) {
		json_decode($str);
		return json_last_error() == JSON_ERROR_NONE;
	 }
	 
	 $json_params = file_get_contents("php://input");
	 
	 if (strlen($json_params) > 0 && isValidJSON($json_params))
	  { $decoded_params = json_decode($json_params);}
	   else{echo "blala";}*/

  ?>			
					<div id="bibtex_display" >
							<!-- Ausgabebereich der Einträge-->
							<!--Template bestimmt Anordnung der Elemente, stylebiblio.css bestimmt Aussehen
										mit class=if... können Leere Zeilen vermieden werden
									Beginn Template eines Eintrags-->
						<div class="bibtex_template">
							<div class="media">
								<div class="media-body">								
									<div class="row">
										<div class="col-sm-9 col-md-10">
											<a class="bibtexVar bibtexCodeLink noread" href="+URL+" extra="URL"><h4 class="first"> <span class="title"> </span></h4></a>								
											<div class="doc-body">
												<p class="if url">
													<span class="url"></span>


												</p>
												Status:	
											
										
													<?php
											


												//folgender Teil geht mit festem Link, Problem: dynamische Links von js übergeben
													$file = 'https://www.bundesregierung.de/Content/Infomaterial/BPA/Bestellservice/Deutsche_Nachhaltigkeitsstrategie_Neuauflage_2016.pdf?__blob=publicationFile&v=7';
													$file_headers = @get_headers($file);
													
													if(/*!$file_headers || */strpos($file_headers[0], '200')) {
														$exists = "200 -Link funktioniert";
													}	
													elseif(/*!$file_headers || */strpos($file_headers[0], '301')) {
														$exists = "301 -Link wird umgeleitet, aber funktioniert noch. Bitte prüfen!";
													}
													elseif(/*!$file_headers ||*/ strpos($file_headers[0], '404')) {
														$exists = "404 -Link defekt, bitte ersetzen!";
													}
													else {
														$exists =  $file_headers[0] . ", bitte prüfen!";
													}	
													echo $exists;
													?>
													
											</div>	
										</div>
																
									</div>
								</div>
								
							</div>
						</div>
					</div>

			</div>
		</div> 
	</div>
	
</section>

<footer>
	<div class="container">
		<div class="row">
			<div class="col-xs-12 col-sm-4 col-md-3">
				<h4>Inhalt</h4>
				<ul>
					<li><a href="ziele.php">Ziele</a></li>
					<li><a href="gesetz.php">Gesetzliche Regelungen</a></li>			
					<li><a href="stat.php">Statistische Angebote</a></li>
					<li><a href="port.php">Portale</a></li>	
					<li><a href="anw.php">Anwendungen</a></li>
					<li><a href="lit.php">Fachliteratur</a></li>
					<li><a href="news.php">Neuigkeiten</a></li>
					<li><a href="veranstaltungen.php">Veranstaltungen</a></li>
					<li><a href="glossar.php">Glossar</a></li>
				</ul>
			</div>
			<div class="col-xs-12 col-sm-4 col-md-5">
				<h4>Weitere Informationen</h4>
				<ul>
					<li><a href="#">Datenschutz</a></li>
					<li><a href="#">Impressum</a></li>
				</ul>
			</div>
			<div class="col-xs-12 col-sm-4 col-md-4">
				<h4>Kontakt</h4>
				<p>Leibniz-Institut für ökologische Raumentwicklung&nbsp;e.V.</p>
				<p>Weberplatz 1<br>01217 Dresden</p>
				<p>Telefon + 49 (0)351 46 79 0<br>E-Mail <a href="#">monitor[im]ioer.de</a><br>URL <a href="#">https://ioer.de/</a></p>
			</div>
		</div>
	</div>
	<div class="container">
		<div class="row">
			<div class="col-sm-10">
				<p>© IOER 2018</p>
			</div>
			<div class="col-sm-2"></div>
		</div>
	</div>
</footer>
<div id ="right" >

</div>
</div>
<link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:200,200i,300,300i,400,400i,600,600i,700,700i,900,900i" media="all">
<?php

	?>


</body>
	
</html>








