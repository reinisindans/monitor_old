<?php 
session_start(); // Sitzung starten/ wieder aufnehmen
include("includes_classes/verbindung_mysqli.php");

Login();

function Login()
{
	include("includes_classes/verbindung_mysqli.php");
	
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
		
		// bei Forderung einer IP Restriktion
		/* 
		if($_SESSION['Dokument']['ViewBerechtigung_IP_Restriktion'])
		{
			// geforderte IP-Zugangs-Kombi abfragen
			$SQL_Login_IP = "SELECT * FROM m_status_freigabe,m_status_freigabe_passwoerter 
							WHERE m_status_freigabe.STATUS_FREIGABE = m_status_freigabe_passwoerter.STATUS_FREIGABE 
							AND m_status_freigabe.STATUS_FREIGABE = '".$_POST['Rechte']."' 
							AND PASSWORT = '".$Pwd."' AND IP_RESTRIKT LIKE '".$_SERVER["REMOTE_ADDR"]."%'";
			$Ergebnis_Login_IP = mysqli_query($Verbindung,$SQL_Login_IP);
			// ausloggen wenn IP in DB nicht getroffen
			if(!@mysqli_result($Ergebnis_Login_IP,0,'IP_RESTRIKT'))
			{
				$_SESSION['Dokument']['ViewBerechtigung'] = "3";
			} 
			else
			{
				// Status Name ablegen
				$_SESSION['Dokument']['ViewBerechtigung_Name'] = @mysqli_result($Ergebnis_Login_IP,0,'STATUS_NAME');
			}
		} 
		*/
		
		
		// Wenn $_SESSION['Dokument']['ViewBerechtigung'] leer ist, dann auf jeden Fall auf 3 = Gast setzen
		if(!@mysqli_result($Ergebnis_Login,0,'STATUS_NAME')) $_SESSION['Dokument']['ViewBerechtigung'] = "3";
	}
	else
	{
		// Standard-Login auf 3 = Gast setzen, wenn nichts übergeben wurde und kein Login gesetzt ist
		//if(!$_SESSION['Dokument']['ViewBerechtigung'] and $_SESSION['Dokument']['ViewBerechtigung'] != "0") 	
		//{
			$_SESSION['Dokument']['ViewBerechtigung'] = "3";
		//}
	}
	
	// Logout verwalten und karte leeren in Funktion SetReset()

}


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link href="./screen_viewer.css" rel="stylesheet" type="text/css" />
<title>Login</title>
</head>

<body>


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
						$Aktiver_Status = utf8_encode(@mysqli_result($Ergebnis_Rechte,$i_re,'STATUS_NAME'));  //evtl ohne unicode, da das von testserver kommt
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
			Login<br />
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
												$Aktiver_Status = utf8_encode(@mysqli_result($Ergebnis_Rechte,$i_re,'STATUS_NAME'));  //evtl ohne unicode, da das von testserver kommt
											} 
									?>><?php echo utf8_encode(@mysqli_result($Ergebnis_Rechte,$i_re,'STATUS_NAME')); ?></option>  <!--evtl ohne unicode, da das von testserver kommt-->
											} 
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
		<?php 
	}

// echo $_SERVER["REMOTE_ADDR"];

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
	<a name="Karten2" href="https://monitor.ioer.de/svg_viewer/svg_html.php" class="button_grau_abschicken" >Alter SVG-Viewer</a>
	
	
	<?php 
}
else{
?>
	<br/>
	<a name="Karten" href="https://monitor.ioer.de/svg_viewer/svg_html.php" class="button_grau_abschicken" >Alter SVG-Viewer</a>
<?php } ?>
</body>
</html>