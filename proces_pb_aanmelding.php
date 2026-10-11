<?php
include_once('config.php');

// error_log('Waarom zien we niets gebeuren??');

$arr = array ();
if (isset($_POST['voornaam']))			$arr['am_voornaam'] = $_POST['voornaam']; else $arr['am_voornaam'] = '';
if (isset($_POST['tussenvoegsels']))	$arr['am_tussenvoegsels'] = $_POST['tussenvoegsels']; else $arr['am_tussenvoegsels'] = '';
if (isset($_POST['achternaam']))			$arr['am_achternaam'] = $_POST['achternaam']; else $arr['am_achternaam'] = '';
if (isset($_POST['emailadres'])) 		$arr['am_emailadres'] = $_POST['emailadres']; else $arr['am_emailadres'] = '';
if (isset($_POST['telefoonnr'])) 		$arr['am_telefoonnr'] = $_POST['telefoonnr']; else $arr['am_telefoonnr'] = '';
if (isset($_POST['aantal'])) 			$arr['am_aantal'] = $_POST['aantal']; else $arr['am_aantal'] = '';

if (isset($_POST['sendBut']) && $_POST['sendBut'] == 'sendForm')
{
	/* Eerst een email naar de aanmelder */
	$message = 'Beste ' . $arr['am_voornaam'] . ' ' . $arr['am_tussenvoegsels'] . ' ' . $arr['am_achternaam'] . "<br/><br/>";
	$message .= 'Je hebt je aangemeld voor de partnerbijeenkomst op 15 oktober a.s.' . "<br/><br/>";
	$message .= 'Emailadres      : ' . $arr['am_emailadres'] . '<br/>';
	$message .= 'Aantal personen : ' . $arr['am_aantal'] . '<br/><br/>';
	$message .= "<br/><br/>" . 'Dank voor de aanmelding en tot donderdag 15 oktober 15:00h';
	$message .= "<br/><br/>" . 'Met vriendelijke groeten,';
	$message .= "<br/>" . LOC_NAME;

	// error_log($message);
	$name = $arr['am_voornaam'] . ' ' . $arr['am_tussenvoegsels'] . ' ' . $arr['am_achternaam'];
	Tools::MailRoom($name, $arr['am_emailadres'], 'Aanmelding ' . LOC_NAME, $message);
	
	/* Dan een email naar de coordinator */
	
	$message = 'Er is een nieuwe aanmelding voor de partnerbijeenkomst<br/><br/><br/>' . $message;
	
	Tools::MailRoom('Organisator', 'aanmelding@' . LOC_DOMAIN_PUB, 'Aanmelding Partnerbijeenkomst', $message);
	
	Tools::closeMailer();
	// echo $wkz;
	header('location: pb_aanmelden.php');
	exit();	

}

?>
