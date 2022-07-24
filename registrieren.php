<?php
/* 
* Seitenname: registrieren.php
* Autor: Marc Meiners
* PHP Version: 7
*/
?>

<?php include'functions.php'; ?>
<?php if(isset($_SESSION['benutzer_id'])){
	header("Location: index.php");
}
$con = mysql_verbindung_1(); //Herstellen einer MySQL Verbindung -> siehe functions.php

?>
<html>
	<head>
		<meta charset="utf-8">
		<link rel="stylesheet" type="text/css" href="style.css" />
		<title>Registrieren</title>
	</head>
	<body>

		<div class="wrapper">

		<?php include'header.php'; ?>

		<div class="maincontent">

		<div class="inhalt">
			<div class="formular">
				
				<?php
				//Erste Definition von später genutzten Variablen (Standartwerte)
				$vorname = "";
				$nachname = "";
				$benutzername = "";
				$email = "";
				
				//Falls das Registrierungsformular ausgefüllt wurde, werden die Formularwerte in Variablen gespeichert
				if(isset($_GET['login']) and isset($_POST['vorname'])){ 

					$vorname = mysqli_real_escape_string($con, $_POST['vorname']);
					$nachname = mysqli_real_escape_string($con, $_POST['nachname']);
					$benutzername = mysqli_real_escape_string($con, $_POST['benutzername']);
					$email = mysqli_real_escape_string($con, $_POST['email']);
					$stufe = mysqli_real_escape_string($con, $_POST['stufe']);
					
					//Falls die gewählte Stufe unter 3 liegt, wird das Profil automatisch als Untergymnasium definiert - egal was für ein Profil zuvor beim Formular gewählt wurde
					if ($stufe < 3 and $stufe > 1) { 
						$profil="ug";
					}
					else {
						$profil = mysqli_real_escape_string($con, $_POST['profil']); 
					}
					$schule = mysqli_real_escape_string($con, $_POST['schule']);
					$passwort = mysqli_real_escape_string($con, $_POST['passwort']);
					$fehler = false;
									
					//Bedinungen, die die Länge der Formularwerte überprüfen:
					
					if (strlen($vorname)>50 or strlen($vorname)<1 ){
						echo("<span class='statusmeldung'>Der Vorname muss zwischen 1 und 50 Zeichen lang sein</span></br>");
						$fehler=true;
					}
					
					if (strlen($nachname)>50 or strlen($nachname)<1 ){
						echo("<span class='statusmeldung'>Der Nachname muss zwischen 1 und 50 Zeichen lang sein</span></br>");
						$fehler=true;
					}
					
					if (strlen($benutzername)>25 or strlen($benutzername)<5 ){
						echo("<span class='statusmeldung'>Der Benutzername muss zwischen 5 und 25 Zeichen lang sein</span></br>");
						$fehler=true;
					}
									
					//Kontrolle, ob Benutzername bereits in der Datenbank existiert
					if (mysqli_num_rows(mysqli_query($con, "SELECT benutzername FROM benutzer WHERE benutzername='$benutzername'"))==1){
						echo("<span class='statusmeldung'>Der Benutzername existiert bereits. Bitte wähle einen anderen.</span></br>");
						$fehler=true;
					}

					
						
					//Hier wird die Stammdomain der eingegebenen E-Mail Adresse ermittelt -> Kontrolle ob die E-Mail Adresse von einer Kantonsschule ist
						
					//Trennung der E-Mail-Adresse am @ Zeichen und Speicherung der Teile in einem Array.
					$email_array = explode('@', $email);
					//Speicherung der gesamten Domain der E-Mail-Adresse.
					$domainstring = $email_array[count($email_array)-1];
					//Trennung der Domain an den "." Zeichen und Speicherung der Teile in einem Array
					//-> Es könnte sich um eine Subdomain handeln
					$domain_array = explode('.', $domainstring);
					//Speicherung der Stamm-Domain in einer Variable -> Auswahl der letzten beiden Elemente des Domain Arrays mit Punkt dazwischen.
					$stamm_domain = $domain_array[count($domain_array)-2] . "." . $domain_array[count($domain_array)-1];

					//Kontrolle, ob die Domain erlaubt ist
					if (!in_array($stamm_domain, $schulen_domains)){
						echo("<span class='statusmeldung'>Bitte gib deine persönliche Schul-E-Mail-Adresse an. Die E-Mail-Adresse muss zwischen 5 und 100 Zeichen lang sein</span></br>");
						$fehler=true;
					}

					//Bedinung, die die Länge der E-Mail Adresse überprüft

					if (!filter_var($email, FILTER_VALIDATE_EMAIL) or strlen($email)>50 or strlen($email)<5){
						echo("<span class='statusmeldung'>Bitte gib deine persönliche Schul-Mailadresse an. Die E-Mail Adresse muss zwischen 5 und 100 Zeichen lang sein</span></br>");
						$fehler=true;
					}
					
					
					//Kontrolle, ob die E-Mail Adresse bereits in der Datenbank existiert
					
					if (mysqli_num_rows(mysqli_query($con, "SELECT email FROM benutzer WHERE email='$email'"))==1){
						echo("<span class='statusmeldung'>Die E-Mail Adresse ist bereits mit einem Benutzerkonto verbunden.</span></br>");
						$fehler=true;
					}
					
					
					//Bedinungen, die prüfen, ob die Drop-Down Menus ausgewählt wurden
					
					if($stufe=="0"){
						echo("<span class='statusmeldung'>Bitte wähle eine Klassenstufe</span></br>");
						$fehler=true;
					}
					
					if($profil=="0"){
						echo("<span class='statusmeldung'>Bitte wähle ein Profil</span></br>");
						$fehler=true;
					}
					
					if($schule=="0"){
						echo("<span class='statusmeldung'>Bitte wähle eine Kantonsschule</span></br>");
						$fehler=true;
					}
					
					
					//Bedinung, die die Länge des Passworts überprüft

					if (strlen($passwort)>50 or strlen($passwort)<8 ){
						echo("<span class='statusmeldung'>Das Passwort muss zwischen 8 und 50 Zeichen lang sein</span></br>");
						$fehler=true;
					}
					
					//Falls alles korrekt ausgefüllt wurde
					if ($fehler==false){

						//Verschlüsselung des Passwortes
						$passwort_hash = sha1($passwort);
							
						//Aktuelles Datum ermitteln
						$date_time_aktuell = date("Y-m-d H:i:s"); 
						//Hinzufügen des Benutzers in die benutzer Tabelle der Datenbank
						$sql = "INSERT INTO benutzer (datum_erstellt, datum_aktualisiert, datum_reaktiviert, datum_geloescht, deaktiviert, vorname, nachname, email, passwort, benutzername, stufe, profil, schule, email_registrierung_kennung, email_registrierung_ok, email_reaktivierung_kennung) VALUES ('$date_time_aktuell', '$date_time_aktuell', '$date_time_aktuell', '0000-00-00 00.00.00', '0', '$vorname', '$nachname', '$email', '$passwort_hash', '$benutzername', '$stufe', '$profil', '$schule', '$kennung', '0', '0')";
						
						mysqli_query($con, $sql);
						
						//Generieren einer Zufallszahl zur E-Mail-Aktivierung des Benutzerkontos.
						$kennung = rand(1,999999999);
						//Generieren eines Bestätigungslinks mit der Zufallszahl als GET-Parameter im Link
						$bestaetigungslink = $domain_url . "/aktivierung.php?kennung=" . $kennung;
						
						//Senden des Bestätigungslinks.
						mail($email, "Bestaetigung der E-Mail Adresse - kantinet", $bestaetigungslink);
						
						echo("Vielen Dank für die Registrierung! Du hast eine E-Mail mit einem Bestätigungslink erhalten.");
						
					}
					}
				?>

				
				<?php
				//Registrierungsformular
				?>
				<form action="?login=1" method="post">
					<input name="vorname" placeholder="Vorname" value="<?php echo($vorname); ?>"></br>
					<input name="nachname" placeholder="Nachname" value="<?php echo($nachname); ?>"></br>
					<input name="benutzername" placeholder="Benutzername" value="<?php echo($benutzername); ?>"></br>
					<input name="email" placeholder="Schul-E-Mail-Adresse" value="<?php echo($email); ?>"></br>
					<select name="stufe" placeholder="Klassenstufe">
						<option value="0">---Klassenstufe wählen---</option>
						<option value="1">1. Klasse</option>
						<option value="2">2. Klasse</option>
						<option value="3">3. Klasse</option>
						<option value="4">4. Klasse</option>
						<option value="5">5. Klasse</option>
						<option value="6">6. Klasse</option>
					</select></br>
	
				<select name="profil">
					<option value="0">---Profil wählen---</option>
					<?php 

					//Die möglichen Profile werden aus den in der "functions.php" Datei definierten Arrays geholt und mithilfe einer Schleife als Auswahlwerte des Drop-Down Menus gesetzt
					$anzahl_profile = count($profile_titel);

					for($i=0; $i<$anzahl_profile; $i++){
						?>
						<option value="<?php echo($profile[$i]) ?>"><?php echo($profile_titel[$i]) ?></option>
						<?php
					}
					?>
				</select><br>
		
		<select name="schule">
					<option value="0">---Kantonsschule wählen---</option>
					<?php 

					//Die möglichen Kantonsschulen werden aus den in der "functions.php" Datei definierten Arrays geholt und mithilfe einer While-Schleife als Auswahlwerte des Drop-Down Menus gesetzt
					$anzahl_schulen = count($schulen_titel);

					for($i=0; $i<$anzahl_schulen; $i++){
						?>
						<option value="<?php echo($schulen[$i]) ?>"><?php echo($schulen_titel[$i]) ?></option>
						<?php
					}
					?>
				</select><br>
	

					<input name="passwort" type="password" placeholder="Passwort"></br>
					<input type="submit" value="Registrieren">

				</form>
		</div>
		</div>
		</div>
		<?php include'footer.php'; ?>
		<?php mysqli_close($con); ?>

		</div>

	</body>
</html>