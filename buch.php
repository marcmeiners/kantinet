<?php
/* 
* Seitenname: buch.php
* Autor: Marc Meiners
* PHP Version: 7
*/
?>

<?php include'functions.php'; ?>
<?php if(!isset($_SESSION['benutzer_id'])){
	header("Location: index.php");
}
?>
<html>
<head>
	<meta charset="utf-8">
	<link rel="stylesheet" type="text/css" href="style.css" />
	<title>Buch</title>
</head>
<body>

<div class="wrapper">

<?php include'header.php'; ?>

<div class="maincontent">

<div class="inhalt">
<div class="buch-ansicht">
	<?php
	$con = mysql_verbindung_1();
	//Erste definition einer später genutzten Variable (Standartwert)
	$buch_id = 0;
	
	//Falls ein Buch-id Parameter im Link vorhanden ist, wird dieser in einer Variable gespeichert
	if(isset($_GET['id'])){
		$buch_id = $_GET['id'];
	}
	
	//Falls das im Link vermerkte Buch nicht in der Datenbank existiert und bereits verkauft wurde, wird der User zur Bücherbörse weitergeleitet
	if(mysqli_num_rows(mysqli_query($con, "SELECT * FROM buecher WHERE id='$buch_id' AND gekauft_von_id='0' AND benutzer_geloescht='0' AND benutzer_deaktiviert = '0'"))==0){
		header("Location: buecherboerse.php");
	}
	
	
	else{
		//Die Buch-Informationen werden aus der Datenbank abgefragt und in Variablen gespeichert.
		$res = mysqli_query($con, "SELECT * FROM buecher WHERE id='$buch_id'");
		//Die id des angemeldeten Benutzers wird ebenfalls in einer Variablen gespeichert, im Falle eines Kaufes seitens des angemeldeten Benutzers wird diese benötigt.
		$kaeufer_id = $_SESSION['benutzer_id'];
		$row = mysqli_fetch_assoc($res);
		$buch_titel = $row['buch_titel'];
		$autor = $row['autor'];
		$jahr = $row['jahr'];
		$verlag = $row['verlag'];
		//Einige Werte sind in Kurzform in der Datenbank gespeichert -> Der "Anzeigename" dieser Werte ist in der "functions.php" in einem separaten Array an derselben Position im Array gespeichert.
		$zustand = $zustaende_titel[array_search($row['zustand'], $zustaende)];
		$kategorie = $kategorien_titel[array_search($row['kategorie'], $kategorien)];
		$isbn = $row['isbn'];
		$verkaeufer_id = $row['benutzer_id'];

		//Um den Benutzernamen des Verkäufers herauszufinden, ist eine erneute Datenbankabfrage, dieses Mal aus der "benutzer"-Tabelle, von Nöten.
		$row1 = mysqli_fetch_array(mysqli_query($con, "SELECT benutzername FROM benutzer WHERE id='$verkaeufer_id'"));
		$verkaeufer_benutzername = $row1[0];

		$schule_verkaeufer = $schulen_titel[array_search($row['schule'], $schulen)];

		$datum_einstellung = date('d.m.Y', strtotime($row['datum_erstellt']));
		$preis = $row['preis'];
		
		//Der Benutzer kann seine eigenen Bücher natürlich nicht kaufen
		if($kaeufer_id == $verkaeufer_id and @$_GET['kaufen']==1){
			echo("<span class='statusmeldung'>Leider kannst du dein eigenes Buch nicht kaufen :)</span>");
			
		}
		
		//Falls der "Kaufen"-Button betätigt wird, wird der Folgende Code ausgeführt.
		elseif(@$_GET['kaufen']==1){
			//Das aktuelle Datum wird in einer Variablen gespeichert.
			$date_time_aktuell = date("Y-m-d H:i:s");
			//Die Bücher-Tabelle wird entsprechend angepasst -> Käufer und Datum werden vermerkt -> das Buch wird von nun an nicht mehr in der Bücherbörse ersichtlich sein.
			mysqli_query($con, "UPDATE buecher SET gekauft_von_id = '$kaeufer_id', datum_verkauft = '$date_time_aktuell' WHERE id='$buch_id'");
			//Aus der "benutzer"-Tabelle werden alle Informationen von Käufer und Verkäufer abgefragt.
			$row_verkaeufer = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM benutzer WHERE id='$verkaeufer_id'"));
			$row_kaeufer = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM benutzer WHERE id='$kaeufer_id'"));
			
			//Die E-Mail-Adressen werden in Variablen gespeichert.
			$email_verkaeufer = $row_verkaeufer['email'];
			$email_kaeufer = $row_kaeufer['email'];
			
			//Hier werden die E-Mail-Texte definiert, sowohl der Käufer als auch der Verkäufer bekommen eine E-Mail mit einer Bestätigung des Verkaufes und den Kontaktdaten des jeweils anderen.
			$text_email_verkaeufer = "Hallo " . $row_verkaeufer['vorname'] . "\n\n"
			. "Du hast dein Buch " . $buch_titel . " erfolgreich verkauft. Im Folgenden die Kontaktdaten des Kaeufers:\n\n"
			. $row_kaeufer['vorname'] . " " . $row_kaeufer['nachname'] . "\n"
			. $schulen_titel[array_search($row_kaeufer['schule'], $schulen)] . "\n"
			. $email_kaeufer;
			
			$text_email_kaeufer = "Hallo " . $row_kaeufer['vorname'] . "\n\n"
			. "Du hast das Buch " . $buch_titel . " erfolgreich gekauft. Im Folgenden die Kontaktdaten des Verkaeufers:\n\n"
			. $row_verkaeufer['vorname'] . " " . $row_verkaeufer['nachname'] . "\n"
			. $schulen_titel[array_search($row_verkaeufer['schule'], $schulen)] . "\n"
			. $email_verkaeufer;
			//Diese Ausgabe wird dem Nutzer auf der Website nach dem Kauf angezeigt
			echo("<span class='statusmeldung'>Vielen Dank für deinen Kauf! Die Kontaktangaben des Verkäufers wurden dir per E-Mail geschickt.</span>");
			
			//Hier werden die beiden E-Mails versendet.
			mail($email_verkaeufer, "Du hast dein Buch verkauft - kantinet", $text_email_verkaeufer);
			mail($email_kaeufer, "Vielen Dank fuer deinen Kauf - kantinet", $text_email_kaeufer);


		}
		
		//Hier folgt die standardmässige Ausgabe der Buch-Informationen als Tabelle.
		else{
			//Anzeige des Buch Covers
			?>
			<div class="buch-ansicht-cover">
				<img src="<?php echo($row['cover_link']) ?>">
			</div>

			<div class="buch-ansicht-infos">

				<?php
				//Die Details des Buches werden als Tabelle angezeigt
				?>

				<table>

					<tr>
						<th>Titel</th>
						<th class="info-tabelle-inhalte"><?php echo($buch_titel); ?></th>
					</tr>

					<tr>
						<th class="buch-ansicht-infos-titel">Autor</th>
						<th class="buch-ansicht-infos-inhalte"><?php echo($autor); ?></th>
					</tr>
					<tr>
						<th class="buch-ansicht-infos-titel">Jahr</th>
						<th class="buch-ansicht-infos-inhalte"><?php echo($jahr); ?></th>
					</tr>

					<tr>
						<th class="buch-ansicht-infos-titel">Verlag</th>
						<th class="buch-ansicht-infos-inhalte"><?php echo($verlag); ?></th>
					</tr>

					<tr>
						<th class="buch-ansicht-infos-titel">Kategorie</th>
						<th class="buch-ansicht-infos-inhalte"><?php echo($kategorie); ?></th>
					</tr>

					<tr>
						<th class="buch-ansicht-infos-titel" style="padding-bottom: 40px;">ISBN-Nummer</th>
						<th class="buch-ansicht-infos-inhalte" style="padding-bottom: 40px;"><?php echo($isbn); ?></th>
					</tr>

					<tr>
						<th class="buch-ansicht-infos-titel">Zustand</th>
						<th class="buch-ansicht-infos-inhalte"><?php echo($zustand); ?></th>
					</tr>

					<tr>
						<th class="buch-ansicht-infos-titel">Kantonsschule</th>
						<th class="buch-ansicht-infos-inhalte"><?php echo($schule_verkaeufer); ?></th>
					</tr>

					<tr>
						<th class="buch-ansicht-infos-titel">Einstellungsdatum</th>
						<th class="buch-ansicht-infos-inhalte"><?php echo($datum_einstellung); ?></th>
					</tr>

					<tr>
						<th class="buch-ansicht-infos-titel">Verkäufer</th>
						<th class="buch-ansicht-infos-inhalte"><?php echo($verkaeufer_benutzername); ?></th>
					</tr>

					<tr>
						<th class="buch-ansicht-infos-titel">Preis</th>
						<th class="buch-ansicht-infos-inhalte"><b><?php echo($preis); ?> Fr.</b></th>
					</tr>
				</table>
			<?php
			//Das Buch kann auf klick des Buttons gekauft werden. Der Button fungiert als Link.
			?>
			<a href="<?php echo("?id=" . $buch_id . "&kaufen=1"); ?>"><button>Kaufen</button></a>

			</div>
			<?php 
		} 
	}
	
	?>
</div>	
</div>
	

	
</div>

<?php include'footer.php'; ?>
</div>

</body>
</html>
