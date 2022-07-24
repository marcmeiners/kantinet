<?php
/* 
* Seitenname: login.php
* Autor: Marc Meiners
* PHP Version: 7
*/
?>

<?php include'functions.php'; ?>
<?php if(isset($_SESSION['benutzer_id'])){
	header("Location: index.php");
}
$con = mysql_verbindung_1();
?>

<html>
	<head>
		<meta charset="utf-8">
		<link rel="stylesheet" type="text/css" href="style.css" />
		<title>Login</title>
	</head>
	<body>

		<div class="wrapper">

		<?php include'header.php'; ?>

		<div class="maincontent">

		<div class="inhalt">
			<div class="formular">
				
				<?php
					//Prüfen, ob das Login Formular ausgefüllt wurde
					if(isset($_POST['benutzer']))
					{
						//Definieren von später genutzten Variablen
						$benutzer = mysqli_real_escape_string($con, $_POST['benutzer']);
						$passwort = mysqli_real_escape_string($con, $_POST['passwort']);
						$fehler = false;
						$email_existiert=false;
						$benutzername_existiert=false;

						//Kontrolle, ob Zeichenketten zu lange sind
						if (strlen($benutzer) > 100 or strlen($passwort) > 50){
							echo("<span class='statusmeldung'>Die Anfrage war ungültig. Bitte überprüfe den Benutzernamen und das Passwort</span>");
							$fehler = true;
						}
						
						//Kontrolle, ob eingegebende E-Mail oder eingegebener Benutzername existiert
						if (mysqli_num_rows(mysqli_query($con, "SELECT benutzername FROM benutzer WHERE benutzername='$benutzer'"))==1){
							$benutzername_existiert=true;
						}
						if (mysqli_num_rows(mysqli_query($con, "SELECT email FROM benutzer WHERE email='$benutzer'"))==1){
							$email_existiert=true;
						}
						
						if ($email_existiert==false and $benutzername_existiert==false){
							echo("<span class='statusmeldung'>Die Anfrage war ungültig. Bitte überprüfe den Benutzernamen und das Passwort</span>");
							$fehler = true;
						}
						
						if ($fehler==false){
							//Abfragen der Benutzer id
							//Der Benutzername könnte mit der E-Mail Adresse identisch sein
							if ($email_existiert==true and $benutzername_existiert==true){
								$res = mysqli_query($con, "SELECT id FROM benutzer WHERE benutzername='$benutzer'");
								$row = mysqli_fetch_array($res);
								$benutzer_id = $row[0];
							}
							
							elseif ($email_existiert==true and $benutzername_existiert==false){
								$res = mysqli_query($con, "SELECT id FROM benutzer WHERE email='$benutzer'");
								$row = mysqli_fetch_array($res);
								$benutzer_id = $row[0];
							}
							
							elseif ($email_existiert==false and $benutzername_existiert==true){
								$res = mysqli_query($con, "SELECT id FROM benutzer WHERE benutzername='$benutzer'");
								$row = mysqli_fetch_array($res);
								$benutzer_id = $row[0];
							}
							//Abfragen des ganzen Datensatzes des entsprechenden Benutzers
							$res = mysqli_query($con, "SELECT * FROM benutzer WHERE id='$benutzer_id'");
							$row = mysqli_fetch_assoc($res);
							//Speichern des Passwort-Hashes in einer lokalen Variable
							$passwort_db = $row['passwort'];
							
							//Umwandlung des eingegebenen Passwortes in einen Hash
							//Kontrolle, ob die Passwort-Hashes übereinstimmen
							if ($passwort_db==sha1($passwort)){
								//Kontrolle, ob das Konto nach der Registrierung noch nicht aktiviert wurde
								if($row['email_registrierung_ok']!=1){
									echo("<span class='statusmeldung'>Bitte bestätige deine E-Mail Adresse. Dir wurde zu diesem Zweck eine E-Mail geschickt.</span>");
									$fehler=true;
								}
								//Kontrolle, ob das Benutzerkonto gelöscht wurde
								elseif($row['datum_geloescht'] != "0000-00-00 00:00:00"){
									echo("<span class='statusmeldung'>Das Benutzerkonto wurde gelöscht.</span>");
									$fehler=true;
								}
								
								else{
									//Speichern des Datums der letzten Reaktivierung und des aktuellen Datums in Variablen
									$datum_reaktiviert = new DateTime(date('Y-m-d', strtotime($row['datum_reaktiviert'])));
									$datum_aktuell = new DateTime(date('Y-m-d'));
									
									//Zeit seit der letzten Reaktivierung (oder Kontoerstellung) in Tagen
									$differenz = $datum_reaktiviert->diff($datum_aktuell)->days;
									//Speichern der E-Mail adresse in einer lokalen Variable
									$email = $row['email'];
									
									//Der Benutzer kann sich anmelden, erhält aber eine Reaktivierungs-Mail
									if($differenz > 365 and $differenz <= 1095 and $row['email_reaktivierung_kennung']==0){
										//Generieren einer Zufallszahl, die in der Datenbank zwischengespeichert wird
										$kennung = rand(1,999999999);
										mysqli_query($con, "UPDATE benutzer SET email_reaktivierung_kennung = '$kennung' WHERE id='$benutzer_id'");
										//Generieren des Bestätigungslinks
										$bestaetigungslink = $domain_url . "/reaktivierung.php?kennung=" . $kennung;
										//Versenden der Reaktivierungs-Mail
										mail($email, "Reaktivierung des Benutzerkontos - kantinet", $bestaetigungslink);
									}
									
									//Wenn die letzte Reaktivierung mehr als drei Jahre zurückliegt und der Benutzer noch keine Reaktivierungs-Mail bekommen hat, bekommt er die E-Mail nun, kann sich aber nicht mehr anmelden bis zur Reaktivierung
									elseif($differenz > 1095  and $row['email_reaktivierung_kennung']==0){
										$fehler=true;
										$kennung = rand(1,999999999);
										mysqli_query($con, "UPDATE benutzer SET email_reaktivierung_kennung = '$kennung' WHERE id='$benutzer_id'");
										$bestaetigungslink = $domain_url . "/reaktivierung.php?kennung=" . $kennung;
										mail($email, "Reaktivierung des Benutzerkontos - kantinet", $bestaetigungslink);
										//Ausgabe zur Information
										echo("<span class='statusmeldung'>Das Benutzerkonto ist deaktiviert. Zur Reaktivierung wurde dir eine E-Mail gesendet.</span>");
									}
									//Falls die Reaktivierung mehr als drei Jahre zurückliegt und bereits eine Reaktivierungs-Mail versendet wurde, wird keine weitere Nachricht versendet und der Benutzer kann sich bis zur Reaktivierung nicht mehr einloggen
									elseif($differenz > 1095  and $row['email_reaktivierung_kennung']!=0){
										$fehler=true;
										echo("<span class='statusmeldung'>Das Benutzerkonto wurde deaktiviert. Zur Reaktivierung wurde bereits eine E-Mail versendet.</span>");
									}
									
									if($differenz > 1095){
										mysqli_query($con, "UPDATE buecher SET benutzer_deaktiviert = '1' WHERE benutzer_id='$benutzer_id'");
										mysqli_query($con, "UPDATE benutzer SET deaktiviert = '1' WHERE id='$benutzer_id'");
									}
																		
									//Login erfolgreich: Speicherung der Benuter-id in einer Session
									//Weiterleitung zur Bücherbörse
									if($fehler==false){
										$_SESSION['benutzer_id']=$benutzer_id;
										header("Location: buecherboerse.php");
									}	
								}	
							}
							else{
								//Information über Fehleingabe
								echo("<span class='statusmeldung'>Die Anfrage war ungültig. Bitte überprüfe den Benutzernamen und das Passwort</span>");
							}

						} 

					}
				?>

				<form action="" method="post">
					<input name="benutzer" placeholder="Benutzername oder E-Mail-Adresse"><br>
					<input name="passwort" type="password" placeholder="Passwort"><br>
					<input type="submit" value="Login">
				</form>
		</div>
		</div>
		</div>
		<?php include'footer.php'; ?>
		<?php mysqli_close($con); ?>
		</div>

	</body>
</html>
