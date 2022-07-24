<?php
/* 
* Seitenname: buecherboerse.php
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
	<title>Bücherbörse</title>
</head>
<body>

<div class="wrapper">

<?php include'header.php'; ?>

<div class="maincontent" style="width:750px;">

<div class="inhalt">

<?php	
	
	//Erste Definition später genutzter Variablen (Startwert)
	$fehler = false;
	$isbn_ok = true;
	
	//Speicherung der Benutzer id in Variable
	$benutzer_id = $_SESSION['benutzer_id'];

	
	//Diese Bedingung stellt sicher, dass die vom Buch-Registrierungsformular übergebenen Werte nicht überschrieben werden -> "Leere" Startwerte werden nur gesetzt, wenn das Formular nicht bereits falsch ausgefüllt wurde
	if(!isset($_GET['buch-registrieren'])){
		$titel="";
		$autor="";
		$verlag="";
		$jahr="";
		$preis="";
		$cover_link = "standard-cover.png";
	}

	//Diese Bedingung wird ausgeführt, wenn das Buch-Registrierungsformular abgesendet wurde
	if(isset($_GET['buch-registrieren']) and isset($_POST['titel'])){
		//Speicherung der Formular-Daten in Variablen
		//Eine SQL-Injection Abwehr (mysqli_real_escape_string) ist beim Jahr und beim Preis nicht nötig, da diese sowieso darauf kontrolliert werden, ob sie numerische Werte sind
		$titel = mysqli_real_escape_string($con, $_POST['titel']);
		$autor = mysqli_real_escape_string($con, $_POST['autor']);
		$jahr = $_POST['jahr'];
		$verlag = mysqli_real_escape_string($con, $_POST['verlag']);
		$kategorie = $_POST['kategorie'];
		$zustand = $_POST['zustand'];
		$preis = $_POST['preis'];
		$cover_link = $_POST['cover_link'];
		$isbn = $_POST['isbn'];

		//Diverse Bedingungen, die sicherstellen, dass das Formular korrekt ausgefüllt wurde
		//Wurde etwas falsch ausgefüllt, erhält die Variable $fehler den Wert true und der User wird aufgefordert, die Daten zu überprüfen. Es erfolgt kein Datenbank-Eintrag
		
		//Der Titel darf nicht zu lang sein, da die Buch-Rahmen der Bücherbörse in ihrer Grösse begrenzt sind
		if(strlen($titel)>40 or strlen($titel)<1 ){
			echo("<span class='statusmeldung'>Der Titel muss zwischen 1 und 40 Zeichen lang sein</span></br>");
			$fehler=true;
		}

		if(strlen($autor)>80 or strlen($autor)<1 ){
			echo("<span class='statusmeldung'>Der Autor muss zwischen 1 und 80 Zeichen lang sein</span></br>");
			$fehler=true;
		}
		
		if(strlen($verlag)>80 or strlen($verlag)<1 ){
			echo("<span class='statusmeldung'>Der Verlag muss zwischen 1 und 80 Zeichen lang sein</span></br>");
			$fehler=true;
		}

		if(strlen($jahr)!= 4 or !is_numeric($jahr)){
			echo("<span class='statusmeldung'>Das Jahr muss eine vierstellige Zahl sein</span></br>");
			$fehler=true;
		}

		if($kategorie=="0"){
			echo("<span class='statusmeldung'>Bitte wähle eine Kategorie</span></br>");
			$fehler=true;
		}

		if($zustand=="0"){
			echo("<span class='statusmeldung'>Bitte wähle einen Zustand</span></br>");
			$fehler=true;
		}


		if(strlen($preis)>15 or strlen($preis)<1){
			echo("<span class='statusmeldung'>Bitte gib einen gültigen Preis in Franken ein</span></br>");
			$fehler=true;
		}
		
		else{
			//Hier wird der vom Benutzer eingegebene Preis zuerst in einen Float umgewandelt und anschliessend auf fünf Rappen genau gerundet.
			$preis_float  = floatval($preis);
			$preis_float_gerundet = number_format(round(($preis_float + 0.000001) * 20) / 20, 2, '.', '');
			//Hier wird ein Maximalpreis gesetzt. Mehr als 100 Fr. sind unrealistisch für ein Buch.			if ($preis_float_gerundet > 100){
				echo("<span class='statusmeldung'>Der Preis darf nicht mehr als 100 Fr. betragen</span></br>");
				$fehler=true;
			}
		}
		
		$anz_am_verkaufen = mysqli_num_rows(mysqli_query($con, "SELECT * FROM buecher WHERE benutzer_id = '$benutzer_id' AND datum_verkauft = '0000-00-00 00:00:00'"));
		
		if($anz_am_verkaufen > 20){
			echo("<span class='statusmeldung'>Du kannst nicht mehr als 20 Bücher gleichzeitug verkaufen.</span></br>");
			$fehler=true;
		}

		//Falls der Benutzer alles richtig eingegeben hat, wird hier ein Datenbankeintrag gemacht und der Benutzer erhält eine Bestätigungsnachricht
		if ($fehler==false){
			echo("<span class='statusmeldung'>Vielen Dank für die Registrierung des Buches!</span>");
			
			

			$date_time_aktuell = date("Y-m-d H:i:s");
			
			$res1 = mysqli_query($con, "SELECT schule FROM benutzer WHERE id='$benutzer_id'");
			$row1 = mysqli_fetch_array($res1);
			$benutzer_schule = $row1[0];
			
			$res = "INSERT INTO buecher (datum_erstellt, datum_aktualisiert, datum_verkauft, datum_verkauf_abgeschlossen, benutzer_id, benutzer_geloescht, benutzer_deaktiviert, gekauft_von_id, schule, isbn, cover_link, buch_titel, autor, jahr, verlag, kategorie, zustand, preis) VALUES ('$date_time_aktuell', '$date_time_aktuell', '0000-00-00 00.00.00', '0000-00-00 00.00.00', '$benutzer_id', '0', '0', '0', '$benutzer_schule', '$isbn', '$cover_link', '$titel', '$autor', '$jahr', '$verlag', '$kategorie', '$zustand', '$preis_float_gerundet')";
			mysqli_query($con, $res);
						
		}
	}
	
	//Wenn das ISBN Nummer Feld augefüllt und das Formular abgeschickt wurde, wird der folgende Block ausgeführt
	if(isset($_GET['isbn-suche']) and isset($_POST['isbn_suche']) and !isset($_GET['isbn-fehlt'])){
		
		//Hier werden allfällige Bindestriche der ISBN Nummmer entfernt
		$isbn = str_replace("-", "", $_POST['isbn_suche']);
		
		//Eine ISBN-Nummer ist immer eine 10- oder 13-stellige numerische Zeichenkette.
		if(!is_numeric($isbn) or (strlen($isbn)!=10 and strlen($isbn)!=13)){
			echo("<span class='statusmeldung'>Die ISBN Nummer ist ungültig. Eine ISBN Nummer ist eine 10- oder 13-stellige Zahl.</span></br>");
			$isbn_ok=false;
		}
		else{
			
			//Die vom Benutzer eingegebene ISBN-Nummer wird nun an die Google Books API gesendet.
			//Das @ Zeichen verhindert eine Fehlermeldung falls die Google Seite nicht existiert.
			@$str = file_get_contents("https://www.googleapis.com/books/v1/volumes?q=isbn:$isbn");
			
			//Die von Google zurückgegebene Seite ist eine JSON-Datei mit den entsprechenden gesuchten Informationen zum Buch.
			//Die JSON-Datei wird mit der folgenden Funktion in einen assoziativen Array umgewandelt, damit mit den Informationen in PHP gearbeitet werden kann.
			$json = json_decode($str, true);

			//Die folgenden Bedingungen kontrollieren, ob von den jeweiligen gesuchten Buch-Eigenschaften Informationen in der Google Books API vorhanden sind.
			//Falls entsprechende Buch-Informationen gefunden werden können, werden diese in den am Anfang dieser Datei als leer definierten Variablen gespeichert.
			
			if(isset($json['items']['0']['volumeInfo']['imageLinks']['thumbnail'])){
				$cover_link = $json['items']['0']['volumeInfo']['imageLinks']['thumbnail'];		
			}

			if(isset($json['items']['0']['volumeInfo']['title'])){
				$titel = $json['items']['0']['volumeInfo']['title'];
			}

			
			if(isset($json['items']['0']['volumeInfo']['authors']['0'])){
				$autor = $json['items']['0']['volumeInfo']['authors']['0'];
			}
			
			if(isset($json['items']['0']['volumeInfo']['publisher'])){
				$verlag = $json['items']['0']['volumeInfo']['publisher'];
			}

			if(isset($json['items']['0']['volumeInfo']['publishedDate'])){				
				//Hier wird mithilfe einer Funktion das Jahr aus dem Veröffentlichungsdatum "herausgeschnitten"
				$jahr = substr($json['items']['0']['volumeInfo']['publishedDate'], 0, 4);
			}
		}
	}
	
	//Diese Bedingung wird ausgeführt, wenn eine ISBN Nummer eingegeben wurde oder auf den "Keine ISBN?" Link geklickt wurde
	//In diesem Block wird das Buch-Registrierungsformular aufgerufen
	if((isset($_GET['isbn-suche']) and isset($_POST['isbn_suche']) and $isbn_ok==true) or (isset($_GET['buch-registrieren']) and $fehler==true) or isset($_GET['isbn-fehlt'])){?>

		<div class="formular">
		<div class="buecherboerse-registration-top">
			
			<?php
			//Falls das Buch-Cover nicht mehr existerien sollte, wird automatisch das Standart-Cover angezeigt
			?>

			<img src="<?php echo($cover_link); ?>" onError="this.src='standard-cover.png';" >			

			<?php
			//Falls Informationen der Google API geholt werden konnten, werden diese mithilfe des "value" Parameters direkt in das Formular aufgenommen
			//Falls das Formular zuvor falsch ausgefüllt wurde, werden die alten Formularwerte automatisch erneut in das Formular aufgenommen
			//Falls keine alten Formulardaten oder Daten der Google API existieren, wird das entsprechende Formularfeld leergelassen und ein entsprechender "placeholder wird angezeigt"
			?>

			<form action="?buch-registrieren=1" method="post">
				<input type="hidden" name="cover_link" value="<?php echo($cover_link); ?>">
				<input type="hidden" name="isbn" value="<?php echo($isbn); ?>">
				<input name="titel" placeholder="Buchtitel" value="<?php echo($titel); ?>"><br>
				<input name="autor" placeholder="Autor" value="<?php echo($autor); ?>"><br>
				<input name="verlag" placeholder="Verlag" value="<?php echo($verlag); ?>"><br>
				<input name="jahr" placeholder="Erscheinungsjahr" value="<?php echo($jahr); ?>"><br>

		</div>

			<select name="kategorie">
				<option value="0">---Kategorie wählen---</option>
				<?php 
		
				//Die möglichen Kategorien werden aus den in der "functions.php" Datei definierten Arrays geholt und mithilfe einer Schleife als Auswahlwerte des Drop-Down Menus gesetzt
				$anzahl_kategorien = count($kategorien_titel);
		
				for($i=0; $i<$anzahl_kategorien; $i++){
					?>
					<option value="<?php echo($kategorien[$i]) ?>"><?php echo($kategorien_titel[$i]) ?></option>
					<?php
				}
				?>
			</select><br>

				<select name="zustand">
				<option value="0">---Zustand des Artikels angeben---</option>
				<?php 
		
				//Die möglichen Zustände werden aus den in der "functions.php" Datei definierten Arrays geholt und mithilfe einer Schleife als Auswahlwerte des Drop-Down Menus gesetzt
				$anzahl_zustaende = count($zustaende_titel);
		
				for($i=0; $i<$anzahl_zustaende; $i++){
					?>
					<option value="<?php echo($zustaende[$i]) ?>"><?php echo($zustaende_titel[$i]) ?></option>
					<?php
				}
				?>
			</select><br>
			
			

			<input name="preis" placeholder="Preis in CHF" value="<?php echo($preis); ?>"><br>
			<input type="submit" value="Buch verkaufen">

		</form>
		</div>

	<?php }
	//Hier werden die Standard-Bedingungen für die spätere Datenbank-Abfrage definiert. Es werden nur Bücher angezeigt, die noch nicht verkauft wurden und nicht von einem deaktivierten oder gelöschten Benutzer stammen.
	$bedingung_standard = "WHERE gekauft_von_id = '0' AND benutzer_geloescht = '0' AND benutzer_deaktiviert = '0'";
	$bedingung_schule = "";
	$bedingung_kategorie = "";
	//Dieser Block stellt die Standartanzeige der Bücherbörse dar. Hier werden die registrierten Bücher und das ISBN-Feld angezeigt
	if((!isset($_POST['isbn_suche']) and !isset($_POST['titel']) and !isset($_GET['isbn-fehlt'])) or ($isbn_ok==false) ){ ?>

		<div class="isbn-feld">
	
			<form class="formular" action="?isbn-suche=1" method="post">
					<input style="width: 70%;" name="isbn_suche" placeholder="ISBN Nummer">
					<input style="width: 30%; float: right;" type="submit" value="Buch verkaufen!">
			</form>

			<?php
			//Falls keine ISBN Nummer vorhanden ist, wird man mit diesem Link direkt zu einem leeren Buch-Registrierungsformular mit Standart-Cover weitergeleitet
			?>

			<a href="?isbn-fehlt=1">Keine ISBN?</a>
			
		</div>
	
		<div class="buecher">
			<?php

			//Falls eine Kategorie und/oder eine Schule in der Sidebar ausgewählt wurde, wird im Folgenden eine entsprechende Bedingung aufgestellt, die anschliessend der Standard-SQL-Anfrage angefügt wird.
			//Falls in der URL Parameter vorhanden sind, wird geprüft, ob die entsprechende Kategorie oder die Schule auch wirklich im in der Datei "functions.php" definierten Array vorhanden sind.


			if(isset($_GET['kategorie'])){
				if(in_array($_GET['kategorie'], $kategorien)==true){
					$bedingung_kategorie = "AND kategorie = '" . $_GET['kategorie'] . "'";
				}
			}

			if(isset($_GET['schule'])){
				if(in_array($_GET['schule'], $schulen)==true){
					$bedingung_schule = "AND schule = '" . $_GET['schule'] . "'";
				}
			}

			//Nun werden die Bedingungen in der SQL-Sprache entsprechend zusammengefügt.
			$bedingung_gesamt = $bedingung_standard . $bedingung_kategorie . $bedingung_schule;


			//Hier wird eine MySQL-Anfrage erstellt, die die oben definierten Bedingungen berücksichtigt und nur dazu dient, die gesamte Anzahl Datensätze zu bestimmen.
			$res_gesamt = mysqli_query($con, "SELECT * FROM buecher $bedingung_gesamt");

			//Für die spätere Blätterfunktion wird die Anzahl ausgewählter Datensätze bestimmt.
			//Daraus wird die Anzahl Seiten bestimmt, welche auch für die Datenbankabfrage benötigt wird.
			$anz_datensaetze = mysqli_num_rows($res_gesamt);
			$anz_datensaetze_pro_seite = 12;
			$anz_seiten = ceil($anz_datensaetze / $anz_datensaetze_pro_seite);

			//Die $p Variable bestimmt die aktuell angezeigte Seite, der Standardwert von $p ist 1
			
			if(isset($_GET['p'])){
				$p=intval($_GET['p']);
				//Sicherstellen, dass die im Link hinterlegte Seite existiert
				if($p <= 0 or $p > $anz_seiten){
				$p=1;
				}
			}

			else{
				$p=1;
			}

			if($anz_datensaetze==0){
				echo("Leider sind in dieser Kategorie keine Bücher vorhanden.");
			}
				//Mit den folgenden Befehlen wird das entsprechende "Intervall" an Datensätzen ausgewählt. Auf der ersten Seite werden beispielsweise die Datensätze 0-11 angezeigt, also die ersten 12 Datensätze.
				//Das Intervall wird im SQL Befehl mit dem Parameter "LIMIT" definiert.
				$limit_start = ($anz_datensaetze_pro_seite*$p) - $anz_datensaetze_pro_seite;

				$res = mysqli_query($con, "SELECT id, cover_link, buch_titel FROM buecher $bedingung_gesamt ORDER BY id DESC LIMIT $limit_start,$anz_datensaetze_pro_seite");


				//Die While-Schleife gibt die Datensätze in Form von Buch-Rahmen mit Titel und Cover aus.
				//Beim Klicken auf einen der Buch-Rahmen wird man zur entsprechenden Buch-Informations-Seite weitergeleitet.
		
				while($row = mysqli_fetch_object($res)){
			?>

				<a href="buch.php?id=<?php echo $row->id ?>">
					<div class="buch-rahmen">
						<div class="buch-rahmen-bild">
							<img src="<?php echo $row->cover_link; ?>" onError="this.src='standard-cover.png';" >
						</div>

						<div class="buch-rahmen-text">
							<?php echo $row->buch_titel; ?>
						</div>

					</div>
				</a>
					<?php
				}			
			?>
		</div>
	
	<?php 
		
		//Im Folgenden wird die Blätterfunktion ausgeführt.
		//Die aktuelle URL (der Stammordnerpfad) wird in den nächsten Schritten benötigt
		$aktuelle_url = $_SERVER['REQUEST_URI'];
		
			//Es könnte sein, dass der "p"-Wert in der URL ($_GET['p']) nicht mit der "$p"-Variable übereinstimmt, da jemand die URL manuell bearbeitet hat.
			//Falls die "$p"-Variable nicht 1 ist, stimmt der Wert in der URL sicher damit überein und es existiert sicher ein "p"-Wert in der URL.
			//Falls eine Kategorie und/oder eine Schule im Link gespeichert sind dürfen diese beim Blättern nicht verloren gehen.
			//Daher wird die aktuelle URL im "p"-Parameter durch ein String-replacement bearbeitet und der neue "p"-Wert wird gesetzt.

			if($p != 1){
				?>
				<span class="blaetter-link"><a href="<?php echo(str_replace("p=".$p, "p=1", $aktuelle_url)); ?>"><< Anfang</a></span>
				<span class="blaetter-link"><a href="<?php echo(str_replace("p=".$p, "p=" . ($p-1), $aktuelle_url)); ?>">< Zurück</a></span>
				<?php
			}
		
		//Auf der ersten Seite können die Links "Zurück" und "Anfang" nicht angeklickt werden.
		else{
				?>
				<span class="blaetter-link"><< Anfang</span>
				<span class="blaetter-link">< Zurück</span>
				<?php
			}

			//Hier könnte $p gleich 1 sein und der "p"-Wert in der URL könnte nicht damit übereinstimmen.
			//Wenn "$p" gleich 1 ist, könnte auch gar kein "p"-Wert in der URL gespeichert sein.
			//$p könnte mit dem "p"-Wert in der URL übereinstimmen.
			if($p<$anz_seiten){
				
				if($p=1){
					
					//Kontrolle ob in der URL ein "p"-Wert gespeichert ist, wenn ja wird er überschrieben
					if(isset($_GET['p'])){
						?>
						<span class="blaetter-link"><a href="<?php echo(str_replace("p=".$_GET['p'], "p=" . ($p+1), $aktuelle_url)); ?>">Weiter ></a></span>	
						<span class="blaetter-link"><a href="<?php echo(str_replace("p=".$_GET['p'], "p=" . $anz_seiten, $aktuelle_url)); ?>">Ende >></a></span>
						<?php
					}
					
					//Falls kein "p"-Wert in der URL gespeichert ist, wird einer angefügt.
					//Hier spielt es zusätzlich eine Rolle, ob bereits andere GET-Parameter in der URL vorhanden sind ("&"- oder "?"-Zeichen).
					else{
						if(isset($_GET['kategorie']) or isset($_GET['schule'])){
							?>
							<span class="blaetter-link"><a href="<?php echo($aktuelle_url . "&p=" . ($p+1)); ?>">Weiter ></a></span>	
							<span class="blaetter-link"><a href="<?php echo($aktuelle_url . "&p=" . $anz_seiten); ?>">Ende >></a></span>
							<?php
						}
						
						else{
							?>
							<span class="blaetter-link"><a href="<?php echo($aktuelle_url . "?p=" . ($p+1)); ?>">Weiter ></a></span>	
							<span class="blaetter-link"><a href="<?php echo($aktuelle_url . "?p=" . $anz_seiten); ?>">Ende >></a></span>
							<?php
						}
					}	
				}
				
				//Falls die "$p"-Variable nicht 1 ist, stimmt der Wert in der URL sicher damit überein und es existiert sicher ein "p"-Wert in der URL. Dieser kann einfach überschrieben werden.
				else{
					?>
					<span class="blaetter-link"><a href="<?php echo(str_replace("p=".$p, "p=" . ($p+1), $aktuelle_url)); ?>">Weiter ></a></span>	
					<span class="blaetter-link"><a href="<?php echo(str_replace("p=".$p, "p=" . $anz_seiten, $aktuelle_url)); ?>">Ende >></a></span>
					<?php
				}
			}
			//Auf der letzten Seite können die Links "Weiter" und "Ende" nicht angeklickt werden.

			else{
				?>
				<span class="blaetter-link">Weiter ></span>
				<span class="blaetter-link">Ende >></span>
				<?php
			}
		
		
			//Hier werden die aktuelle Seite und die gesamte Anzahl Seiten ausgegeben (nur wenn mehr als 0 Datensätze existieren).
			if($anz_datensaetze>0){		
			?>
				<span class="blaetter-link" style="float:right;">Seite <?php echo($p); ?> von <?php echo ($anz_seiten); ?></span>
			<?php 
			}
	} 
	?>
</div>
</div>
<div class="sidebar">
<div class="sidebar-element">
<div class="inhalt">
	
	<?php 
	//Hier wurde die Sidebar Programmiert
	//Es existieren zwei Sidebar-Elemente - "Kategorien" und "Schulen"
	//Die Kategorien und Schulen, die angezeigt werden, werden wiederum aus den in der Datei "functions.php" definierten Arrays geholt, mithilfe einer While-Schleife
	//Wenn eine Kategorie oder eine Schule ausgewählt wurde, wird hinter dem entsprechenden Titel (Kategorien oder Schulen) ein Hacken (&#10004) angezeigt
	
	if($bedingung_kategorie != ""){
		echo("<h1>Kategorien &#10004</h1>");
	} 
	
	else{
		echo("<h1>Kategorien</h1>");
	}


	for ($i=0; $i < count($kategorien); $i++){
		
		//Falls die ausgewählte Kategorie in der Schleife "an die Reihe kommt", wird dafür gesorgt, dass diese eine andere Farbe hat und fett hervorgehoben wird.
		//Bei erneutem Klicken auf die ausgewählte Kategorie wird der Parameter in der URL gleich null gesetzt. Dies ist gleichwertig, wie wenn gar keine Kategorie in der URL vorhanden ist.
		//Das @ verhindert eine Fehlermeldung, falls kein "$_GET['kategorie']"-Wert existiert.
		if(@$_GET['kategorie']==$kategorien[$i]){
			$neue_url_kategorie = str_replace("kategorie=".$kategorien[$i], "kategorie=0", $aktuelle_url);
			
			if(isset($_GET['p'])){
				//Falls ein "p" Wert in der URL vorhanden ist wird dieser auf 1 gesetzt, damit man beim erneuten Klicken auf die ausgewählte Kategorie wieder auf die erste Seite gelangt.
				$neue_url_kategorie = str_replace("p=".$_GET['p'], "p=1", $neue_url_kategorie);
			}
			
			?><a style="color:#000; font-weight: bold;" href="<?php echo($neue_url_kategorie); ?>">&#11206; <?php echo($kategorien_titel[$i]); ?></a><?php
			}
		else { 
			if(!isset($_GET['schule'])){
				?><a href="?kategorie=<?php echo($kategorien[$i]); ?>">&#11208; <?php echo($kategorien_titel[$i]); ?></a><?php 
			}
			else{
				//Hier wird dafür gesorgt, dass ein allfälliger Parameter zur Schule nicht verloren geht und in der neuen URL immer noch vorhanden ist.
				?><a href="?kategorie=<?php echo($kategorien[$i]); ?>&schule=<?php echo($_GET['schule']); ?>">&#11208; <?php echo($kategorien_titel[$i]); ?></a><?php
			}
		}
	}
	?>
</div>
</div>
	
<div class="sidebar-element" style="margin-top: 20px;">
<div class="inhalt">
	
	<?php 
	
	//Die Programmierschritte sind hier analog zu denjenigen beim obigen Kategorien Sidebar-Element
	
	if($bedingung_schule != ""){
		echo("<h1>Schulen &#10004</h1>");
	} 
	
	else{
		echo("<h1>Schulen</h1>");
	}

	$aktuelle_url = $_SERVER['REQUEST_URI'];

	for ($i=0; $i < count($schulen); $i++){
		if(@$_GET['schule']==$schulen[$i]){
			$neue_url_schule = str_replace("schule=".$schulen[$i], "schule=0", $aktuelle_url);

			if(isset($_GET['p'])){
				$neue_url_schule = str_replace("p=".$_GET['p'], "p=1", $neue_url_schule);
			}
			
			?><a style="color:#000; font-weight: bold;" href="<?php echo($neue_url_schule); ?>">&#11206; <?php echo($schulen_titel[$i]); ?></a><?php
		}
		else{
			if(!isset($_GET['kategorie'])){
				?><a href="?schule=<?php echo($schulen[$i]); ?>">&#11208; <?php echo($schulen_titel[$i]); ?></a><?php 
			}
			else{
				?><a href="?kategorie=<?php echo($_GET['kategorie']); ?>&schule=<?php echo($schulen[$i]); ?>">&#11208; <?php echo($schulen_titel[$i]); ?></a><?php
			}
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
