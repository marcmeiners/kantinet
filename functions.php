<?php
/* 
* Seitenname: functions.php
* Autor: Marc Meiners
* PHP Version: 7
*/
?>

<?php
if(!isset($_SESSION)) 
{ 
	session_start(); 
} 

$domain_url="kantinet.ch";

//Mithilfe der folgenden Arrays werden die Kategorien und Schulen definiert. Diese können im nachhinein ergänzt werden.
$kategorien_titel = array("Mathematik", "Deutsch", "Französisch", "Italienisch", "Englisch","Spanisch", "Russisch", "Chinesisch", "Latein", "Physik", "Chemie", "Biologie", "Geschichte", "Geographie", "BG", "Musik", "Wirtschaft und Recht", "Informatik", "Griechisch", "Religion", "Sonstige");
$kategorien = array("m", "d", "f", "i", "e", "sp", "ru", "chi", "l", "ph", "ch", "b", "g", "gg", "bg", "mu", "wr", "inf", "gr", "r", "so");

$schulen_titel = array("Kantonale Maturitätsschule für Erwachsene", "Kantonsschule Büelrain", "Kantonsschule Enge", "Kantonsschule Freudenberg", "Liceo Artistico", "Kantonsschule Hohe Promenade", "Kantonsschule Hottingen", "Kantonsschule Im Lee", "Kantonsschule Küsnacht", "Kantonsschule Limmattal", "K+S Rämibühl", "LG Rämibühl", "MNG Rämibühl", "RG Rämibühl", "Kantonsschule Rychenberg", "Kantonsschule Stadelhofen", "Kantonsschule Uetikon am See", "Kantonsschule Uster", "Kantonsschule Wiedikon", "Kantonsschule Zimmerberg", "Kantonsschule Zürcher Oberland", "Kantonsschule Zürcher Unterland", "Kantonsschule Zürich Nord");
$schulen = array("kme", "kbw", "ken", "kfr", "liceo", "kshp", "ksh", "klw", "ksk", "ksl", "ks", "lg", "mng", "rg", "krw", "kst", "kue", "kus", "kwi", "kszi", "kzo", "kzu", "kzn"); 
$schulen_domains = array("kme.ch", "kbw.ch", "ken.ch", "kfr.ch", "liceo.ch", "kshp.ch", "ksh.ch", "ksimlee.ch", "kkn.ch", "kslzh.ch", "ksgymnasium.ch", "lgr.ch", "mng.ch", "rgzh.ch", "krw.ch", "ksstadelhofen.ch", "kuezh.ch" , "ksuster.ch", "kwi.ch", "kszi.ch", "kzo.ch", "kzu.ch", "kzn.ch");

$profile_titel = array("Untergymnasium", "Physik und Anwendungen der Mathematik", "Biologie und Chemie", "Wirtschaft und Recht", "Neusprachlich", "Altsprachlich", "Kunst", "Immersion");
$profile = array("ug", "pam", "bch", "wr", "n", "a", "k", "i");

$zustaende = array("neu", "wie_neu", "gebraucht", "stark_gebraucht");
$zustaende_titel = array("Neu", "Wie neu", "Gebraucht", "Stark gebraucht");

//Diese Funktion stellt eine Verbindung zum MySQL-Server her. Die Datenbank-Login-Details können im Nachhinein abgeändert werden.
function mysql_verbindung_1(){
	$con = mysqli_connect("localhost", "root", "", "kantinet");
    //Die Zeichencodierung wird auf UTF 8 gestellt.
	mysqli_query($con, "SET NAMES utf8");			
	return $con;
}


?>