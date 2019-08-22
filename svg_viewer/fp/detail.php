<!--Javascript nach https://github.com/pcooksey/bibtex-js/wiki-->
<!DOCTYPE html>
<html lang="de">
<head>
	<title>Detailansicht - IÖR-Flächenportal</title>
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
	<script language="javascript" src="JavaScript/bootstrap.min.js" type="text/javascript"></script>
	<script language="javascript" src="JavaScript/bootstrap-dropdownhover.min.js" type="text/javascript"></script>
	<script language="javascript" src="JavaScript/jquery.mmenu.all.min.js" type="text/javascript"></script>
	<script language="javascript" src="JavaScript/jquery.mmenu.bootstrap.min.js" type="text/javascript"></script>
	<script language="javascript" src="JavaScript/jquery.headroom.js" type="text/javascript"></script>
	<script language="javascript" src="JavaScript/headroom.min.js" type="text/javascript"></script>
	<script language="javascript" src="JavaScript/default.js" type="text/javascript"></script>

	<script language="javascript" src="JavaScript/parser.js" type="text/javascript"></script>
	<script language="javascript" src="JavaScript/fp.js" type="text/javascript"></script>
	
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
				<li class="dropdown">
					<a href="kat.html">Kategorien <i class="dropdown-toggle"></i></a>
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
								<input type="text" class="form-control" id="inputSearch" placeholder="Portal durchsuchen..." name="search"/>		
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
<bibtex src="./data/Flaechenportal.bib"></bibtex>
<section>
	<div class="container">	
		<div id="bibtex_display" bibtexkeys="<?php echo($bibkey);?>">		
			<!-- Ausgabebereich der Einträge-->
			<!--Template bestimmt Anordnung der Elemente, stylebiblio.css bestimmt Aussehen
				mit class=if... können Leere Zeilen vermieden werden
			Beginn Template eines Eintrags-->
			<div class="bibtex_template">
				<div class="media">
					<div class="media-body">
						<ol class="breadcrumb">
							<li><a href="index.html"><i class="fa fa-home"></i></a></li>
							<li id="crumb"><a> <span class="bibtextype"></span></a></li>
							<li class="active"><span class="title" id="titel"></span></li>
						</ol>	
						<h3><span class="iconclass"></span>&nbsp;<span class="title"></span></h3>
						<dl class="dl-horizontal">
							<span class="if shorttitle"> <dt>Kurztitel</dt>	<dd><span class="shorttitle"></span>		</dd></span>
							<span class="if author"> <dt>Autor(en)</dt>	<dd><span class="author"></span>		</dd></span>
							<span class="if editor"> <dt>Herausgeber</dt>	<dd><span class="editor"></span>		</dd></span>
							<span class="if editora"> <dt>Unter Mitarbeit von</dt>	<dd><span class="editora"></span>		</dd></span>
							<span class="if institution"> <dt>Institution</dt>	<dd><span class="institution"></span>		</dd></span>
							<span class="if date"> <dt>Veröffentlichungsdatum</dt>	<dd><span class="date"></span>		</dd></span>
							<span class="if journal"> <dt>Veröffentlicht in <span class="if number "> (Ausgabe)</span></dt>
									<dd><span class="journal"></span>	  <span class="if number ">(<span class="number noread"></span>)</span></dd>
							</span>
							<span class="if series"> <dt>Reihe</dt>	<dd><span class="series"></span></dd></span>
							<span class="if issn"> <dt>ISSN</dt>	<dd><span class="issn"></span>		</dd></span>
							<span class="if abstract"> <dt>Zusammenfassung</dt>	<dd><span class="abstract"></span>		</dd></span>
							<span class="if url"> <dt>Verweis</dt>	<dd><a class="bibtexVar" href="+URL+" extra="URL" target="_blank"><i class="fa fa-lg fa-external-link-square"></i>&nbsp;Öffnen	</a>	</dd></span>
							<span class="if annotation0"> <dt>Inhalte</dt>	
								<dd>		
									<span class="if annotation0">  <span class="annotation0"></span><br/></span>
									<span class="if annotation1">  <span class="annotation1"></span><br/></span>
									<span class="if annotation2">  <span class="annotation2"></span><br/></span>
									<span class="if annotation3">  <span class="annotation3"></span><br/></span>
									<span class="if annotation4">  <span class="annotation4"></span><br/></span>
									<span class="if annotation5">  <span class="annotation5"></span><br/></span>
									<span class="if annotation6">  <span class="annotation6"></span><br/></span>
									<span class="if annotation7">  <span class="annotation7"></span><br/></span>
									<span class="if annotation8">  <span class="annotation8"></span><br/></span>
									<span class="if annotation9">  <span class="annotation9"></span><br/></span>
									<span class="if annotation10"> <span class="annotation10"></span><br/></span>
									<span class="if annotation11"> <span class="annotation11"></span><br/></span>
									<span class="if annotation12"> <span class="annotation12"></span><br/></span>
									<span class="if annotation13"> <span class="annotation13"></span><br/></span>
									<span class="if annotation14"> <span class="annotation14"></span><br/></span>
									<span class="if annotation15"> <span class="annotation15"></span><br/></span>
									<span class="if annotation16"> <span class="annotation16"></span><br/></span>
									<span class="if annotation17"> <span class="annotation17"></span><br/></span>
									<span class="if annotation18"> <span class="annotation18"></span><br/></span>
									<span class="if annotation19"> <span class="annotation19"></span><br/></span>
									<span class="if annotation20"> <span class="annotation20"></span><br/></span>	
								</dd>
							</span>							   
							<span class="if keywords"> <dt>Schlagwörter</dt>	<dd ><span class="keywords" id="keywords"></span>		</dd></span>     
								
							<dt>Literaturangabe (American <br>Psychological Association <br>6th edition)</dt>	
								<dd>
									<span class="if author"> <span class="author"></span>.</span>	<span class="if editor"> <span class="editor"></span>	(Hrsg.)</span>	
									<span class="if date">(<span class="date"></span>).	</span><span class="title"> </span>.<span class="if url"> 	Abgerufen von&nbsp;<span class="url"></span></span>
								</dd>	
							<dt>Export</dt>
								<dd>				     	
								<!--Bootstrap collapse wird ausgelöst bei Klick auf diesen Link...-->
									<a class="bibtexVar bibtexCodeLink noread" role="button" data-toggle="collapse" href="#bib+BIBTEXKEY+" 
										aria-expanded="false" aria-controls="bib+BIBTEXKEY+" extra="BIBTEXKEY">
										<i class="fa fa-external-link"></i>&nbsp;&nbsp;Einbinden im BibLaTeX-Format	
									</a>
									<!--...und klappt Feld auf mit .bib Eintrag des Elements. class "noread" bei bibtexraw bewirkt, dass suche nicht darauf ausgeführt wird-->
									<div class="bibtexVar collapse noread" id="bib+BIBTEXKEY+" extra="BIBTEXKEY">
										&nbsp;	<i class="fa fa-question-circle-o" aria-hidden="true"></i>&nbsp;Hilfe: Untenstehenden BibLaTeX Code auswählen, in Zwischenablage kopieren, in Zoteros Hauptmenü 'Datei' > 'Importieren aus Zwischenablage' verwenden
										<br /> <button type="button" class="btn btn-primary" id="button1" onclick="CopyToClipboard('spanraw')">Code kopieren</button>
										<div id="hinweiscopy"></div>
										<div class="well">										
											<span id="spanraw" class="bibtexraw noread"></span>
										</div>
									</div>    
								</dd> 
						</dl>					
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
