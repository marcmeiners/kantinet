<?php
/* 
* Seitenname: reaktivierung.php
* Autor: Marc Meiners
* PHP Version: 7
*/
?>

<?php
//Diese Datei wird nur aufgerufen, wenn der Benutzer auf einen via E-Mail erhaltenen Reaktivierungs-Link klickt

	include'functions.php';
	$con = mysql_verbindung_1();

	//Prüfen, ob eine Kennung im Link vorhanden ist.
	//Sonst: Weiterleitung zur Startseite.
	if(isset($_GET['kennung'])){
		//Speichern der Kennung in einer Variablen.
		$kennung = $_GET['kennung'];
		
		//Wenn die Kennung stimmt, wird der folgende Code ausgeführt.
		//Sonst: Weiterleitung zur Startseite.
		if (mysqli_num_rows(mysqli_query($con, "SELECT email_reaktivierung_kennung FROM benutzer WHERE email_reaktivierung_kennung='$kennung'"))==1){
			//Falls das Konto bereits gelöscht wurde, kann es nicht mehr reaktiviert werden
			$res = mysqli_query($con, "SELECT * FROM benutzer WHERE email_reaktivierung_kennung='$kennung'");
			$row = mysqli_fetch_assoc($res);
			$datum_geloescht = $row['datum_geloescht'];
			$benutzer_id = $row['id'];
			
			if($datum_geloescht != "0000-00-00 00:00:00"){
				echo("Das Konto wurde gelöscht und kann nicht mehr aktiviert werden");
			}
			else{
				//Speichern des aktuellen Datums in einer Variablen.
				$date_time_aktuell = date("Y-m-d H:i:s");
				//Entsprechende Anpassung der Datenbank zur Reaktivierung des Benutzerkontos.
				mysqli_query($con, "UPDATE benutzer SET deaktiviert = '0' WHERE id='$benutzer_id'");
				mysqli_query($con, "UPDATE benutzer SET datum_reaktiviert = '$date_time_aktuell' WHERE id='$benutzer_id'");
				mysqli_query($con, "UPDATE benutzer SET email_reaktivierung_kennung = '0' WHERE id='$benutzer_id'");
				
				mysqli_query($con, "UPDATE buecher SET benutzer_deaktiviert = '0' WHERE benutzer_id = '$benutzer_id'");

				echo('Dein Benutzerkonto wurde erfolgreich reaktiviert. Du kannst dich jetzt auf der Website anmelden.');

			}
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