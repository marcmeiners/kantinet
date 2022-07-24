<?php
/* 
* Seitenname: header.php
* Autor: Marc Meiners
* PHP Version: 7
*/
?>

<?php
//Diese Seite stellt den Kopfbereich der Website dar und wird in den anderen Dateien eingebunden.
//Gewisse Header-Menu einträge werden nur angezeigt, wenn ein Benutzer eingeloggt ist, bzw, wenn er nicht eingeloggt ist
?>

<div class="header">
		<div class="headermenu">
			<div class="headermenu-links">
				<a style="color: #0431B4;" href="index.php">kantinet.ch</a>
				<?php
				if (isset($_SESSION['benutzer_id'])){
				?>
				<a href="buecherboerse.php">Bücherbörse</a>
				<?php
				}
				?>
				
			</div>
			<div class="headermenu-rechts">
				<?php
				if (isset($_SESSION['benutzer_id'])){
				?>
				<a href="dashboard.php">Dashboard</a>
				<a href="index.php?logout=1">Logout</a>
				<?php
				}
				?>
				<?php
				if (!isset($_SESSION['benutzer_id'])){
				?>
				<a href="registrieren.php">Regsitrieren</a>
				<a href="login.php">Login</a>
				<?php
				}
				?>

			</div>
		</div>
  </div>
