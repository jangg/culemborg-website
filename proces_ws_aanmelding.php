<?php
include_once('config.php');
// include('class/c_werkzoekende.php');
// include('class/c_processtap.php');

// error_log('Waarom zien we niets gebeuren??');

if (isset($_POST['optie1'])) $optie1 = $_POST['optie1']; else $optie1 = '';
if (isset($_POST['privacy']) && $_POST['privacy'] == 'privacy') $privacy = 'check'; else $privacy = '';

$arr = array ();
if (isset($_POST['voornaam']))			$arr['am_voornaam'] = $_POST['voornaam']; else $arr['am_voornaam'] = '';
if (isset($_POST['tussenvoegsels']))	$arr['am_tussenvoegsels'] = $_POST['tussenvoegsels']; else $arr['am_tussenvoegsels'] = '';
if (isset($_POST['achternaam']))			$arr['am_achternaam'] = $_POST['achternaam']; else $arr['am_achternaam'] = '';
if (isset($_POST['emailadres'])) 		$arr['am_emailadres'] = $_POST['emailadres']; else $arr['am_emailadres'] = '';
$arr['optie_wsp']					= $optie1;



if (isset($_POST['sendBut']) && $_POST['sendBut'] == 'sendForm')
{
	if ($privacy != 'check')
	{
		$_SESSION['result'] = json_encode($arr);
		echo '<script>alert("Er is iets misgegaan. Verstuur het formulier nogmaals altublieft."); window.location.href ="aanmelden.php/";</script>';
		header("location: aanmeldenworkshop.php");
		exit();
	} 
	/* Eerst een email naar de aanmelder */
	$message = 'Beste ' . $arr['am_voornaam'] . ' ' . $arr['am_tussenvoegsels'] . ' ' . $arr['am_achternaam'] . "<br/><br/>";
	$message .= 'Je hebt de volgende gegevens naar JobHulp Culemborg verzonden: ' . "<br/><br/>";
	$message .= 'Emailadres : ' . $arr['am_emailadres'] . '<br/>';
	$message .= 'Je hebt je aangemeld voor de volgende optie(s):' . '<br/><br/>';
	if ($arr['optie_wsp']) $message .= 'xxxxxxxxxxxxxxxx' . '<br/>';
	$message .= "<br/><br/>" . 'Met vriendelijke groeten,';
	$message .= "<br/>" . 'JobHulp Culemborg';

	// error_log($message);
	$name = $arr['am_voornaam'] . ' ' . $arr['am_tussenvoegsels'] . ' ' . $arr['am_achternaam'];
	Tools::MailRoom($name, $arr['am_emailadres'], 'Aanmelding Workshop door JobHulp Culemborg', $message);
	
	/* Dan een email naar de coordinator */	
	$message = 'Er is een nieuwe aanmelding voor xxxxxxxxx binnengekomen via de website jobhulpmculemborg.nl<br/><br/><br/>' . $message;	
	Tools::MailRoom('Coordinator', 'aanmelding@jobhulpculemborg.nl', 'Aanmelding xxxxxxxxxxxxxxxxxx', $message);
	
	Tools::closeMailer();
	
	// error_log($message);
	header('location: aanmeldenworkshop.php');
	exit();	

}

?>
