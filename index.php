<?php
/* 
* Seitenname: index.php
* Autor: Marc Meiners
* PHP Version: 7
*/
?>

<?php include'functions.php'; ?>
<?php
	if (isset($_GET['logout'])){
		session_destroy();
		header("Location: index.php");
	}
?>
<html>
<head>
	<meta charset="utf-8">
	<link rel="stylesheet" type="text/css" href="style.css" />
	<title>kantinet.ch</title>
</head>
<body>

<div class="wrapper">

<?php include'header.php'; ?>

<div class="maincontent">

<div class="inhalt">
	
Herzlich willkommen auf der Website kantinet.ch, der Schulbuch-Börse für Zürcher Kantonsschüler. Hier kannst du nicht mehr benötigte Schulbücher verkaufen und gebrauchte Exemplare erwerben.<br><br>
Die Seite wurde im Rahmen einer Maturitätsarbeit programmiert und ist kostenlos.

	
</div>
</div>
<?php include'footer.php'; ?>
</div>

</body>
</html>
