<?php 
session_start(); // Sitzung starten/ wieder aufnehmen
include("../includes_classes/verbindung_mysqli.php");

Login();

function Login() //Übernommen von SVG_Viewer
{
	include("../includes_classes/verbindung_mysqli.php");	
	// evtl. leere Pwd auf "leer" setzen
	if(!$Pwd = $_POST['Passwort']) 
	{
		$Pwd = "leer";
	}
	
	// soll für Intern/extern/Prüfungs-Zugangsberechtigung genutzt werden
	if($_POST['Rechte'] or $_POST['Rechte'] === "0" and $_POST['Rechte_anpassen'])
	{ 
		$SQL_Login = "SELECT * FROM m_status_freigabe,m_status_freigabe_passwoerter 
						WHERE m_status_freigabe.STATUS_FREIGABE = m_status_freigabe_passwoerter.STATUS_FREIGABE 
						AND m_status_freigabe.STATUS_FREIGABE LIKE '".$_POST['Rechte']."%' 
						AND PASSWORT = '".$Pwd."'";
				$Ergebnis_Login = mysqli_query($Verbindung,$SQL_Login);
		
		// erfolgreicher Login
		$_SESSION['Dokument']['ViewBerechtigung'] = @mysqli_result($Ergebnis_Login,0,'STATUS_FREIGABE'); 
		$_SESSION['Dokument']['ViewBerechtigung_IP_Restriktion'] = @mysqli_result($Ergebnis_Login,0,'IP_RESTRIKT'); // ist auf jeden Fall gesetzt, wenn STATUS das in min. einem Fall verlangt > sonst sinnlos
		
		// Wenn $_SESSION['Dokument']['ViewBerechtigung'] leer ist, dann auf jeden Fall auf 3 = Gast setzen
		if(!@mysqli_result($Ergebnis_Login,0,'STATUS_NAME')) $_SESSION['Dokument']['ViewBerechtigung'] = "3";
	}
	else
	{
		// Standard-Login auf 3 = Gast setzen, wenn nichts übergeben wurde und kein Login gesetzt ist
		$_SESSION['Dokument']['ViewBerechtigung'] = "3";
	}	
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Login - IÖR-Flächenportal</title>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
	<meta http-equiv="x-ua-compatible" content="ie=edge">
	
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
	<script language="javascript" src="JavaScript/fp.js" type="text/javascript"></script>
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


<section>
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<ol class="breadcrumb">
					<li><a href="index.html"><i class="fa fa-home"></i></a></li>
					<li class="active">Upload Bibtex</li>
				</ol>
			
				<h3><i class="fa fa-search"></i>&nbsp;&nbsp;Login</h3><hr> 

					<?php
					if($_SESSION['Dokument']['ViewBerechtigung'] < 3) // bei Gast ausblenden
					{
							// Status namentlich erfassen
							$SQL_Rechte = "SELECT * FROM m_status_freigabe ORDER BY STATUS_FREIGABE DESC";
								$Ergebnis_Rechte = mysqli_query($Verbindung,$SQL_Rechte);
							$i_re = 0;
							while(@mysqli_result($Ergebnis_Rechte,$i_re,'STATUS_NAME'))
							{
								if($_SESSION['Dokument']['ViewBerechtigung'] == @mysqli_result($Ergebnis_Rechte,$i_re,'STATUS_FREIGABE')) 
								{
									$Aktiver_Status = utf8_encode(@mysqli_result($Ergebnis_Rechte,$i_re,'STATUS_NAME')); 
								} 
								$i_re++; 
							}	
						?>
						Login-Status: <span style="color:#C00;"><?php echo $Aktiver_Status;  ?></span> 
						<br />
						<br />
						<br />
						Status der Datenbank: 						
						<?php						
						$result = mysqli_query($Verbindung,"SHOW TABLE STATUS LIKE 'm_indikatoren';");
						if(@mysqli_result($result,0,'Engine')== 'InnoDB') { echo "OK!"; }else{ echo "Achtung: Fehlerhaftes Datenbankformat! Bitte den Administrator SOFORT informieren!"; }
						
					}
					else
					{
						?>
						<form action="" method="post" target="_self">
							Bitte einloggen um BibTeX-Datei zu aktualisieren.<br />
						<select name="Rechte">
							<?php 
								$SQL_Rechte = "SELECT * FROM m_status_freigabe ORDER BY STATUS_FREIGABE DESC";
								$Ergebnis_Rechte = mysqli_query($Verbindung,$SQL_Rechte);
								$i_re = 0;
								while(@mysqli_result($Ergebnis_Rechte,$i_re,'STATUS_NAME'))
								{
									?>
									<option value="<?php echo mysqli_result($Ergebnis_Rechte,$i_re,'STATUS_FREIGABE'); ?>" <?php 
															if($_SESSION['Dokument']['ViewBerechtigung'] == @mysqli_result($Ergebnis_Rechte,$i_re,'STATUS_FREIGABE')) 
															{
																echo 'selected="selected"';
																$Aktiver_Status = utf8_encode(@mysqli_result($Ergebnis_Rechte,$i_re,'STATUS_NAME')); 
															} 
													?>><?php echo utf8_encode(@mysqli_result($Ergebnis_Rechte,$i_re,'STATUS_NAME')); ?></option> 
															 
									<?php
									$i_re++; 
								}
								?>
						</select>
						<br />
						<input name="Passwort" type="password" size="15" />
						<input name="Senden" type="submit"  value="ok">
						<input type="hidden" name="Rechte_anpassen" id="Rechte_anpassen" value="1"/>
						</form>
			  <?php }
						?>
				<br />
				<br />
				<br />
				<?php 
				if($_SESSION['Dokument']['ViewBerechtigung'] < 3) // bei Gast ausblenden
				{
					?>
					<form action="" method="post" target="_self">
									<input name="Rechte" type="hidden" value="LOGOUT" />
									<input name="Passwort" type="hidden" svalue="" />
									<input name="Senden" type="submit"  value="Ausloggen">
					</form>
					<br />
					<a name="Karten2" href="https://monitor.ioer.de/svg_viewer/fp/upload.php" class="button_grau_abschicken" >Zum BibTex-Upload</a>				
					
					<?php 
				}
				?>
								
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



</body>
</html>