<?php
/* 
* Seitenname: aktivierung.php
* Autor: Marc Meiners
* PHP Version: 7
*/
?>

<?php
//Diese Datei wird nur aufgerufen, wenn der Benutzer nach der Registrierung des Benutzerkontos auf den E-Mail Bestätigungslink klickt.
	include'functions.php';	
	$con = mysql_verbindung_1();


	//Prüfen, ob die Bestätigungs-Kennung als Parameter im Link vorhanden ist.
	//Sonst: Weiterleitung zur Startseite.
	if(isset($_GET['kennung'])){
		$kennung = $_GET['kennung'];	
		
		//Wenn die Kennung im Link mit derjenigen in der Datenbank übereinstimmt, wird der Benutzer aktiviert -> Der entsprechende Parameter wird in der Datenbank abgeändert.
		//Sonst wird der Benutzer zur Startseite weitergeleitet.
		if (mysqli_num_rows(mysqli_query($con, "SELECT email_registrierung_kennung FROM benutzer WHERE email_registrierung_kennung='$kennung'"))==1){
			mysqli_query($con, "UPDATE benutzer SET email_registrierung_ok = '1' WHERE email_registrierung_kennung='$kennung'");
			//Der Benutzer bekommt eine Bestätigung angezeigt und kann sich einloggen.
			echo('Dein Benutzerkonto wurde erfolgreich bestätigt. Du kannst dich jetzt auf der Website anmelden.');
			?>
			<a href="login.php">Zum Login-Bereich</a>
			<?php

		}
		else{
			header("Location: index.php");
		}
	}
	else{
		header("Location: index.php");
		}
		mysqli_close($con);

?>