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
		// utf8mb4 via de DSN (charset=...) in plaats van PDO::MYSQL_ATTR_INIT_COMMAND:
		// die constante is verouderd in PHP 8.5. Dit werkt in elke PHP-versie hetzelfde.
		// Een eventuele andere charset in DB_DSN (bijv. utf8) wordt vervangen door utf8mb4.
		$dsn = preg_replace('/;?charset=[^;]*/i', '', DB_DSN) . ';charset=utf8mb4';
		$connection = new PDO( $dsn, DB_USERNAME, DB_PASSWORD );
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