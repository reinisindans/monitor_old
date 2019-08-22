
<html lang="de">
<head>
	<title>Notes - IÖR-Flächenportal</title>
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

	<!--<script language="javascript" src="JavaScript/notes.js" type="text/javascript"></script>-->




<?php $bibkey = $_GET['bibkey'];?>
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
				<li class="dropdown active">
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
				<li><a href="suche.php">Suche</a></li>
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

<section>
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<ol class="breadcrumb">
					<li><a href="index.html"><i class="fa fa-home"></i></a></li>
					<li class="active">Veranstaltungen</li>
				</ol>
				<h3><i class="fa fa-line-chart"></i>&nbsp;&nbsp;Veranstaltungen</h3>
				<p>Workshops, Tagungen und Events in der Übersicht.</p>

				<div class="media">
					<div class="media-body">		
					<div id="citations"></div>
						<div ><span id="prev"></span><span id="next"> </span></div>
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
</div>
<link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:200,200i,300,300i,400,400i,600,600i,700,700i,900,900i" media="all">
	

</body>
</html>