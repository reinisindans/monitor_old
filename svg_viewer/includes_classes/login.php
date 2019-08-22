<?php 
function Login()
{
	include("./verbindung_mysqli.php");
	
	// evtl. leere Pwd auf "leer" setzen
	if(!$Pwd = $_POST['Passwort']) 
	{
		$Pwd = "leer";
	}
	
	// soll für Intern/extern/Prüfungs-Zugangsberechtigung genutzt werden
	if($_POST['Rechte'] or $_POST['Rechte'] == "0")
	{ 
		$SQL_Login = "SELECT * FROM m_status_freigabe,m_status_freigabe_passwoerter 
						WHERE m_status_freigabe.STATUS_FREIGABE = m_status_freigabe_passwoerter.STATUS_FREIGABE 
						AND m_status_freigabe.STATUS_FREIGABE LIKE '".$_POST['Rechte']."%' 
						AND PASSWORT = '".$Pwd."' AND IP_RESTRIKT LIKE '".substr($_SERVER["REMOTE_ADDR"],0,6)."%'";
		$Ergebnis_Login = mysqli_query($Verbindung,$SQL_Login);
		// erfolgreicher Login
		$_SESSION['Dokument']['ViewBerechtigung'] = @mysqli_result($Ergebnis_Login,0,'STATUS_FREIGABE'); 
		$_SESSION['Dokument']['ViewBerechtigung_IP_Restriktion'] = @mysqli_result($Ergebnis_Login,0,'IP_RESTRIKT');
		
		// wenn evtl. geforderte IP nicht zutrifft...
		if($IP = @mysqli_result($Ergebnis_Login,0,'IP_RESTRIKT'))
		{
			if($IP != substr($_SERVER["REMOTE_ADDR"],0,6)) $_SESSION['Dokument']['ViewBerechtigung'] = "3";
			
		} 

		// Wenn $_SESSION['Dokument']['ViewBerechtigung'] leer ist, dann auf jeden Fall auf 3 = Gast setzen
		if(!@mysqli_result($Ergebnis_Login,0,'STATUS_NAME')) $_SESSION['Dokument']['ViewBerechtigung'] = "3";
	}
	else
	{
		// Standard-Login auf 3 = Gast setzen, wenn nichts übergeben wurde und kein Login gesetzt ist
		if(!$_SESSION['Dokument']['ViewBerechtigung'] and $_SESSION['Dokument']['ViewBerechtigung'] != "0") 	
		{
			$_SESSION['Dokument']['ViewBerechtigung'] = "3";
		}
	}
	
	// Logout verwalten und karte leeren in Funktion SetReset()

}
?>