<?php
/* een sessie blijft hoogstens 4 uur in de lucht. Daarna moet er opnieuw worden ingelogd */
ini_set("session.cookie_lifetime","7200");
session_start();
require 'includes/configDB.php';
require_once 'class/c_tools.php';

error_reporting(E_ALL);
setlocale(LC_ALL, 'nl_NL');

function openDB ()
{
	global $connection;
	if (!is_a($connection, 'PDO'))
	{
		$connection = new PDO( DB_DSN, DB_USERNAME, DB_PASSWORD, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'utf8mb4\'') );
		$connection->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
		// error_log('Het openen van de database lukt wel!');	
	}
}
openDB();

// h() = veilig tonen: maakt van tekst gewone tekst (geen HTML).
// Gelijk aan h() in config.php van het intranet; de gedeelde classes gebruiken hem.
if (!function_exists('h')) {
	function h($w): string
	{
		return htmlspecialchars((string)$w, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false);
	}
}
?>