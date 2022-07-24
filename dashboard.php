<?php
/* 
* Seitenname: dashboard.php
* Autor: Marc Meiners
* PHP Version: 7
*/
?>

<?php include'functions.php'; ?>
<?php if(!isset($_SESSION['benutzer_id'])){
	header("Location: index.php");
}
$con = mysql_verbindung_1();
?>

<html>
<head>
	<meta charset="utf-8">
	<link rel="stylesheet" type="text/css" href="style.css" />
	<title>Dashboard</title>
</head>
<body>

<div class="wrapper">

<?php include'header.php'; ?>

<div class="maincontent" style="width:750px;">

<div class="inhalt">
<?php
	
	//Speichern der Benutzer id und des aktuellen Datums in lokalen Variablen
	$benutzer_id = $_SESSION['benutzer_id'];
	$date_time_aktuell = date("Y-m-d H:i:s");
	
	//Definieren der Menu-Einträge des Dashboards als Arrays -> Sowohl "Anzeige-Format" als auch "Linkformat"
	$dashboard_menu = array("am_verkaufen", "verkauft", "verkauft_archiv", "gekauft", "gekauft_archiv");
	$dashboard_menu_titel = array("Am Verkaufen", "Verkauft", "Verkauft Archiv", "Gekauft", "Gekauft Archiv");
	
	//Definieren der Standardausgabe, die angezeigt wird, wenn keine Bücher im Dashboard-Menu vorhanden sind
	$keine_buecher_meldung = "<a href='dashboard.php'>Zurück</a><hr>In dieser Kategorie sind keine Bücher vorhanden";
	
	//Abfragen von Datenbankeinträgen der "buecher" Tabelle der entsprechenden Bücher, die in den einzelnen Dashboard-Menus aufgelistet werden
	$res_am_verkaufen = mysqli_query($con, "SELECT * FROM buecher WHERE benutzer_id = '$benutzer_id' AND gekauft_von_id = '0' AND datum_verkauf_abgeschlossen = '0000-00-00 00:00:00' ORDER BY id DESC");
	$res_verkauft = mysqli_query($con, "SELECT * FROM buecher WHERE benutzer_id = '$benutzer_id' AND gekauft_von_id != '0' AND datum_verkauf_abgeschlossen = '0000-00-00 00:00:00' ORDER BY id DESC");
	$res_verkauft_archiv = mysqli_query($con, "SELECT * FROM buecher WHERE benutzer_id = '$benutzer_id' AND gekauft_von_id != '0' AND datum_verkauf_abgeschlossen != '0000-00-00 00:00:00' ORDER BY id DESC");
	$res_gekauft = mysqli_query($con, "SELECT * FROM buecher WHERE gekauft_von_id = '$benutzer_id' AND datum_verkauf_abgeschlossen = '0000-00-00 00:00:00' ORDER BY id DESC");
	$res_gekauft_archiv = $res = mysqli_query($con, "SELECT * FROM buecher WHERE gekauft_von_id = '$benutzer_id' AND datum_verkauf_abgeschlossen != '0000-00-00 00:00:00' ORDER BY id DESC");
	
	//Der Verkäufer kann aktuell zum Verkauf stehende Bücher löschen
	//Die Bücher werden auf der Website nicht mehr angezeigt und komplett aus der Datenbank gelöscht
	if(isset($_POST['buch_loeschen'])){
		$buch_loeschen_id = $_POST['buch_loeschen'];
		mysqli_query($con, "DELETE FROM buecher WHERE id = '$buch_loeschen_id'");
		echo("<span class='statusmeldung'>Du hast dein Buch erfolgreich gelöscht. Es wird nun nicht mehr auf der Website gelistet.</span>");
	}
	
	//Ein noch nicht abgeschlossener Verkaufsprozess kann vom Verkäufer abgebrochen werden.
	//Das Buch wird in diesem Fall reaktiviert und ist wieder in der Bücherbörse ersichtlich.
	if(isset($_POST['buch_reaktivieren_id'])){
		$buch_reaktivieren_id = $_POST['buch_reaktivieren_id'];
		mysqli_query($con, "UPDATE buecher SET datum_verkauft = '0000-00-00 00:00:00', gekauft_von_id = '0' WHERE id = '$buch_reaktivieren_id'");
		echo("<span class='statusmeldung'></span>");
	}
	
	//Der abgeschlossene Verkaufsprozess kann nur seitens des Verkäufers bestätigt werden.
	//Das entsprechende Buch erscheint nun bei Käufer und Verkäufer im Archiv.
	if(isset($_POST['buch_archiv_id'])){
		$buch_archiv_id = $_POST['buch_archiv_id'];
		mysqli_query($con, "UPDATE buecher SET datum_verkauf_abgeschlossen = '$date_time_aktuell' WHERE id = '$buch_archiv_id'");
		echo("<span class='statusmeldung'></span>");
	}
	
	//Änderung des Benutzernamens bei entsprechender Anfrage.
	if(isset($_POST['neuer_benutzername'])){
		$neuer_benutzername = mysqli_real_escape_string($con, $_POST['neuer_benutzername']);
		$fehler = false;
		//Kontrolle, ob der Benutzername den Zeichenrichtlinien entspricht.
		if (strlen($neuer_benutzername)>25 or strlen($neuer_benutzername)<5 ){
			echo("<span class='statusmeldung'>Der Benutzername muss zwischen 5 und 25 Zeichen lang sein</span> </br>");
			$fehler=true;
		}
					
		//Kontrolle, ob Benutzername bereits in der Datenbank existiert.
		if (mysqli_num_rows(mysqli_query($con, "SELECT benutzername FROM benutzer WHERE benutzername='$neuer_benutzername'"))==1){
			echo("<span class='statusmeldung'>Der Benutzername existiert bereits. Bitte wähle einen anderen.</span></br>");
			$fehler=true;
		}
		//Datenbank-Aktualisierung.
		if ($fehler == false){
			mysqli_query($con, "UPDATE benutzer SET benutzername = '$neuer_benutzername', datum_aktualisiert = '$date_time_aktuell' WHERE id = '$benutzer_id'");
			echo("<span class='statusmeldung'>Du hast deinen Benutzernamen erfolgreich geändert!</span>");
		}
	}
	
	//Änderung des Passworts bei entsprechender Anfrage.
	if(isset($_POST['neues_passwort'])){
		$neues_passwort = mysqli_real_escape_string($con, $_POST['neues_passwort']);
		$fehler = false;
		//Kontrolle, ob das neue Passwort den Zeichenrichtlinien entspricht.
		if (strlen($neues_passwort)>50 or strlen($neues_passwort)<8 ){
			echo("<span class='statusmeldung'>Das Passwort muss zwischen 8 und 50 Zeichen lang sein</span></br>");
			$fehler=true;
		}
		//Umwandlung des neuen Passwortes in einen Hash.
		if($fehler==false){
			$neues_passwort_hash = sha1($neues_passwort);
			//Datenbank-Aktualisierung.
			mysqli_query($con, "UPDATE benutzer SET passwort = '$neues_passwort_hash', datum_aktualisiert = '$date_time_aktuell' WHERE id = '$benutzer_id'");
			echo("<span class='statusmeldung'>Du hast dein Passwort erfolgreich geändert!</span>");
		}
	}
	
	//Kontolöschung bei entsprechender Anfrage.
	if(isset($_POST['konto_loeschen'])){
		//Damit die Kontolöschung nicht aus Versehen erfolgt, muss der Benutzer im entsprechenden Dashboard-Formular die Löschung durch Eingabe einer Kennung ("1234") bestätigen.
		if($_POST['konto_loeschen'] == "1234"){
			//Das Konto wird als gelöscht definiert, der Datenbankeintrag bleibt vorerst erhalten.
			mysqli_query($con, "UPDATE benutzer SET datum_geloescht = '$date_time_aktuell' WHERE id = '$benutzer_id'");
			//Alle Bücher, die vom Benutzer jemals zum Verkauf eingestellt wurden, werden als gelöscht markiert und nicht mehr in der Bücherbörse angezeigt.
			mysqli_query($con, "UPDATE buecher SET benutzer_geloescht = '1' WHERE benutzer_id = '$benutzer_id'");
			//Löschen der "Benutzer-Session" und Weiterleitung zur Startseite.
			session_destroy();
			header("Location: index.php");
		}
		else{
			echo("Wenn du das Benutzerkonto löschen möchtest, dann gib bitte '1234' in das Formular ein.");
			
		}
	}
	
	//Prüfen, ob im Link kein "menu" Parameter vorhanden ist, der als menu existiert -> Link kann vom User abgeändert werden
	//Die folgenden Zeilen stellen die "Standartansicht" des Dashboards dar
	if(@in_array($_GET['menu'], $dashboard_menu) == false){
		//MySQL Abfrage: Komplette "Zeile" des angemeldeten Nutzers aus der Datenbank abfragen
		$row_benutzer = mysqli_fetch_array(mysqli_query($con, "SELECT * FROM benutzer WHERE id='$benutzer_id'"));
		
		//Ausgabe der persönlichen Daten des angemeldeten Nutzers in der Dashboard Standartansicht
		//Zur Ausgabe wird mit HTML eine Tabelle erzeugt
		?>
		<h1>Deine Daten</h1>
		<table>
			<tr>
				<th>Name</th>
				<th><?php echo($row_benutzer['vorname'] . " " . $row_benutzer['nachname']); ?></th>
			</tr>
			<tr>
				<th>Benutzername</th>
				<th><?php echo($row_benutzer['benutzername']);?></th>
			</tr>
			<tr>
				<th>E-Mail-Adresse</th>
				<th><?php echo($row_benutzer['email']);?></th>
			</tr>
			<tr>
				<th>Kantonsschule</th>
				<th><?php echo($schulen_titel[array_search($row_benutzer['schule'], $schulen)]);?></th>
			</tr>
			
			<tr>
				<th>Profil</th>
				<th><?php echo($profile_titel[array_search($row_benutzer['profil'], $profile)]);?></th>
			</tr>
			
			<tr>
				<th>Kontoeröffnung</th>
				<th><?php echo(date('d.m.Y', strtotime($row_benutzer['datum_erstellt'])));?></th>
			</tr>
			
			<tr>
				<th>Am Verkaufen</th>
					<th><?php echo(mysqli_num_rows($res_am_verkaufen) . " " . "Bücher");?></th>		
			</tr>
			
			<tr>
				<th>Verkauft</th>
					<th><?php echo(mysqli_num_rows($res_verkauft) . " " . "Bücher");?></th>	
			</tr>
			
			<tr>
				<th>Verkauft Archiv</th>
				<th><?php echo(mysqli_num_rows($res_verkauft_archiv) . " " . "Bücher");?></th>			
			</tr>
			
			<tr>
				<th>Gekauft</th>
				<th><?php echo(mysqli_num_rows($res_gekauft) . " " . "Bücher");?></th>			
			</tr>
			
			<tr>
				<th>Gekauft Archiv</th>
				<th><?php echo(mysqli_num_rows($res_gekauft_archiv) . " " . "Bücher");?></th>			
			</tr>
			
		</table>

		<hr>			
		<h1>Ändere deine Daten</h1>
	<br>
	<?php		
	//Die folgenden Zeilen stellen mit HTML erzeugt Formulare zur Änderung gewisser Nutzerdaten dar
	//Die Formulare werden beim absenden an diese Datei (dashboard.php) zurück geschickt und ausgewertet (Siehe Anfang dieser Datei)
	?>
	<form class="formular" action="dashboard.php" method="post">
			<input style="width: 60%;" name="neuer_benutzername" placeholder="Neuer Benutzername">
			<input style="width: 40%; float: right; text-align:left;" type="submit" value="Benutzername ändern">
	</form>
	<br>
	<form class="formular" action="dashboard.php" method="post">
		<input style="width: 60%;" name="neues_passwort" type="password" placeholder="Neues Passwort">
		<input style="width: 40%; float: right; text-align:left;" type="submit" value="Passwort ändern">
	</form>
	<br>
	<hr>
	<h1>Konto löschen</h1>
	<br>
	<form class="formular" action="dashboard.php" method="post">
		<input style="width: 60%; font-size: 16px; float: left;" name="konto_loeschen" placeholder="Zur Bestätigung hier 1234 eingeben">
		<input style="width: 40%; float: left; font-size: 16px; margin-bottom: 30px; text-align:left;" type="submit" value="Benutzerkonto endgültig löschen">
	</form>
		<?php
	}


	//Die Auflistung der Bücher der entsprechenden Dashboard-Menu-Einträge werden jeweils mit der folgenden Funktion ausgeführt
	//Die zum Menu passende MySQL Abfrage ($res) wird der Funktion als erster Parameterg gegeben
	//Die MySQL Abfragen, die hier verwendet werden, sind am anfang dieser Datei definiert
	//Die Parameter "$verkauft" und "gekauft" können den Wert 0 oder 1 haben, je nachdem wird die Funktion angepasst (Siehe weiter unten), so kann die gleiche Funktion für alle Menus verwendet werden
	function dashboard_buecher($res, $verkauft, $gekauft){
		$con = mysql_verbindung_1();
		?>
		<a style="font-weight: bold;" href="dashboard.php">Zurück</a>
		<hr>
		<?php
		//Die While Schleife des Dashboards gibt die Bücher, anders als in der Bücherbörse, nicht als Rahmen sondern als Liste aus
		while($row = mysqli_fetch_object($res)){
				?>
				<?php echo("<b>" . $row->buch_titel . ", " . $row->autor . "</b>"); ?>
				<br>
				<?php echo("Preis: " . $row->preis); ?> Fr.
				<br>
				<?php  
				//Bei den gekauften Büchern wird der Verkäufer und dessen E-Mail Adresse angezeigt
				if($gekauft!=""){
					$verkaeufer_id = $row->benutzer_id;
					$row_verkaeufer = mysqli_fetch_array(mysqli_query($con, "SELECT * FROM benutzer WHERE id='$verkaeufer_id'"));
					echo("Verkäufer: " . $row_verkaeufer['vorname'] . " " . $row_verkaeufer['nachname'] . ", " . $row_verkaeufer['email'] . "<br>"); 
					
				}
				//Bei den verkauften Bücher wird der Käufer und dessen E-Mail Adresse anezeigt
				if($verkauft!=""){
					$kaeufer_id = $row->gekauft_von_id;
					$row_kaeufer = mysqli_fetch_array(mysqli_query($con, "SELECT * FROM benutzer WHERE id='$kaeufer_id'"));
					echo("Käufer: " . $row_kaeufer['vorname'] . " " . $row_kaeufer['nachname'] . ", " . $row_kaeufer['email'] . "<br>"); 
	
				}
				//Wenn ein Verkäufer sein Buch noch nicht verkauft hat, kann er über diesen Link das Angebot einsehen
				if($verkauft == "" and $gekauft==""){
					?>
					<a href="buch.php?id=<?php echo($row->id); ?>">Buch ansehen</a>
				<?php
				}
				//Über den mithilfe dieses Formulares erzeugten Button kann ein zum Verkauf stehendes Buch vom Verkäufer gelöscht werden
				if(@$_GET['menu'] == "am_verkaufen"){
					?>
					<form style="margin-bottom: 5px; margin-top: 5px;" action="dashboard.php" method="post">
					<input type="hidden" name="buch_loeschen" value="<?php echo($row->id); ?>">
					<input type="submit" value="Buch löschen und Verkaufsprozess abbrechen">
					</form>

					<?php
				}	
			
				//Über den mithilfe dieses Formulares erzeugten Button kann ein verkauftes Buch vom Verkäufer ins Archiv verschoben werden, wenn der Verkaufsprozess abgeschlossen ist 
				if(@$_GET['menu'] == "verkauft"){
					?>
					<form style="margin-bottom: 5px; margin-top: 5px;" action="dashboard.php" method="post">
					<input type="hidden" name="buch_reaktivieren_id" value="<?php echo($row->id); ?>">
					<input type="submit" value="Verkaufsprozess abbrechen und Buch wieder online stellen">
					</form>
					<form action="dashboard.php" method="post">
					<input type="hidden" name="buch_archiv_id" value="<?php echo($row->id); ?>">
					<input type="submit" value="Verkaufsprozess abschliessen und Buch ins Archiv verschieben">
					</form>

					<?php
				}
				
				//In den beiden Archiven wird bei den Büchern angegeben, wann der Verkauf stattfand
				
				if(@$_GET['menu'] == "verkauft_archiv"){
					echo("<br>Datum verkauft: " . date('d.m.Y', strtotime($row->datum_verkauft))) . "<br>";
					echo("Datum Verkauf abgeschlossen: " . date('d.m.Y', strtotime($row->datum_verkauf_abgeschlossen)));
				}
			
				if(@$_GET['menu'] == "gekauft_archiv"){
					echo("<br>Datum gekauft: " . date('d.m.Y', strtotime($row->datum_verkauft))) . "<br>";
					echo("Datum Kauf abgeschlossen: " . date('d.m.Y', strtotime($row->datum_verkauf_abgeschlossen)));
				}

				echo("<hr>");
			}
	}
	
//In den Folgenden Code-Blöcken wird die oben definierte Funktion verwendet
//Falls ein Menu angeklickt wurde bzw. falls im Link ein gültiges Menu steht, wird das entsprechende Menu mithilfe der folgenden Bedingungen angezeigt
//Falls in der entsprechenden Kategorie keine Bücher vorhanden sind, wird die am Anfang dieser Datei definierte Fehlermeldung ("$keine_buecher_meldung") angezeigt
	
	if(@$_GET['menu'] == "am_verkaufen"){
		echo("<h1>Am verkaufen</h1><hr>");
		if(mysqli_num_rows($res_am_verkaufen)==0){
			echo($keine_buecher_meldung);
		}
		else{	
			dashboard_buecher($res_am_verkaufen, "", "");
		}
	}
	
	if(@$_GET['menu'] == "verkauft"){
		echo("<h1>Verkauft</h1><hr>");
		if(mysqli_num_rows($res_verkauft)==0){
			echo($keine_buecher_meldung);
		}
		else{
			dashboard_buecher($res_verkauft, "1", "");
		}
	}
	
	if(@$_GET['menu'] == "verkauft_archiv"){
		echo("<h1>Verkauft Archiv</h1><hr>");
		if(mysqli_num_rows($res_verkauft_archiv)==0){
			echo($keine_buecher_meldung);
		}
		else{
			dashboard_buecher($res_verkauft_archiv, "1", "");
			
		}
	}
	
	if(@$_GET['menu'] == "gekauft"){
		echo("<h1>Gekauft</h1><hr>");
		if(mysqli_num_rows($res_gekauft)==0){
			echo($keine_buecher_meldung);
		}
		
		else{
			dashboard_buecher($res_gekauft, "", "1");

		}
	}
	
	if(@$_GET['menu'] == "gekauft_archiv"){
		echo("<h1>Gekauft Archiv</h1><hr>");
		if(mysqli_num_rows($res_gekauft_archiv)==0){
			echo($keine_buecher_meldung);
		}
		else{
			dashboard_buecher($res_gekauft_archiv, "", "1");

		}
	}
	
?>

</div>
</div>
	
<div class="sidebar">
<div class="sidebar-element">
<div class="inhalt">
	
	<?php 
	//Im Folgenden wird die Sidebar des Dashboards analog zu denjenigen Sidebars der Bücherbörse erzeugt
	if(@in_array($_GET['menu'], $dashboard_menu)){
		echo("<h1>Menu &#10004</h1>");
	}
	
	else{
		echo("<h1>Menu</h1>");
	}


	for ($i=0; $i < count($dashboard_menu); $i++){
		if(@$_GET['menu']==$dashboard_menu[$i]){
			?><a style="color:#000; font-weight: bold;" href="?menu=0">&#11206; <?php echo($dashboard_menu_titel[$i]); ?></a><?php
			}
		else { 
			?><a href="?menu=<?php echo($dashboard_menu[$i]); ?>">&#11208; <?php echo($dashboard_menu_titel[$i]); ?></a><?php 
		}
	}
	?>
</div>
</div>
</div>

<?php include'footer.php'; ?>
<?php mysqli_close($con); ?>

</div>

</body>
</html>
