<?php
include_once('config.php');
include('class/c_werkzoekende.php');
include('class/c_processtap.php');

// error_log('Waarom zien we niets gebeuren??');

if (isset($_POST['optie1'])) $optie1 = $_POST['optie1']; else $optie1 = '';
if (isset($_POST['optie2'])) $optie2 = $_POST['optie2']; else $optie2 = '';
if (isset($_POST['optie3'])) $optie3 = $_POST['optie3']; else $optie3 = '';
if (isset($_POST['optie4'])) $optie4 = $_POST['optie4']; else $optie4 = '';
if (isset($_POST['optie5'])) $optie5 = $_POST['optie5']; else $optie5 = '';
if (isset($_POST['optie6'])) $optie6 = $_POST['optie6']; else $optie6 = '';
if (isset($_POST['optie7'])) $optie7 = $_POST['optie7']; else $optie7 = '';
if (isset($_POST['privacy']) && $_POST['privacy'] == 'privacy') $privacy = 'check'; else $privacy = '';

$arr = array ();
if (isset($_POST['voornaam']))			$arr['am_voornaam'] = $_POST['voornaam']; else $arr['am_voornaam'] = '';
if (isset($_POST['tussenvoegsels']))	$arr['am_tussenvoegsels'] = $_POST['tussenvoegsels']; else $arr['am_tussenvoegsels'] = '';
if (isset($_POST['achternaam']))		$arr['am_achternaam'] = $_POST['achternaam']; else $arr['am_achternaam'] = '';
if (isset($_POST['straat']))			$arr['am_straat'] = $_POST['straat']; else $arr['am_straat'] = '';
if (isset($_POST['huisnummer']))		$arr['am_huisnummer'] = $_POST['huisnummer']; else $arr['am_huisnummer'] = '';
if (isset($_POST['postcode']))			$arr['am_postcode'] = $_POST['postcode']; else $arr['am_postcode'] = '';
if (isset($_POST['woonplaats']))		$arr['am_woonplaats'] = $_POST['woonplaats']; else $arr['am_woonplaats'] = '';
if (isset($_POST['emailadres'])) 		$arr['am_emailadres'] = $_POST['emailadres']; else $arr['am_emailadres'] = '';
if (isset($_POST['telefoonnr'])) 		$arr['am_telefoonnr'] = $_POST['telefoonnr']; else $arr['am_telefoonnr'] = '';
if (isset($_POST['situatie'])) 			$arr['am_situatie'] = $_POST['situatie']; else $arr['am_situatie'] = '';
if (isset($_POST['reden'])) 			$arr['am_reden'] = $_POST['reden']; else $arr['am_reden'] = '';
if (isset($_POST['opmerkingen'])) 		$arr['am_opmerkingen'] = $_POST['opmerkingen']; else $arr['am_opmerkingen'] = '';
$arr['optie_mtj']					= $optie1;
$arr['optie_jg1']					= $optie2;
$arr['optie_jg2']					= $optie3;
$arr['optie_jg3']					= $optie4;
$arr['optie_wks']					= $optie5;
$arr['optie_vrw']					= $optie6;
$arr['optie_and']					= $optie7;
$arr['optie_prv']					= $privacy;



if (isset($_POST['sendBut']) && $_POST['sendBut'] == 'sendForm')
{
	if ($privacy != 'check')
	{
		$_SESSION['result'] = json_encode($arr);
		echo '<script>alert("Er is iets misgegaan. Verstuur het formulier nogmaals altublieft."); window.location.href ="aanmelden.php/";</script>';
		header("location: aanmelden.php");
		exit();
	} 
	/* Eerst een email naar de aanmelder */
	$message = 'Beste ' . $arr['am_voornaam'] . ' ' . $arr['am_tussenvoegsels'] . ' ' . $arr['am_achternaam'] . "<br/><br/>";
	$message .= 'Je hebt de volgende gegevens naar JobHulp Culemborg verzonden: ' . "<br/><br/>";
	$message .= 'Straat : ' . $arr['am_straat'] . '<br/>';
	$message .= 'Huisnummer : ' . $arr['am_huisnummer'] . '<br/>';
	$message .= 'Postcode : ' . $arr['am_postcode'] . '<br/>';
	$message .= 'Woonplaats : ' . $arr['am_woonplaats'] . '<br/>';
	$message .= 'Emailadres : ' . $arr['am_emailadres'] . '<br/>';
	$message .= 'Telefoonr  : ' . $arr['am_telefoonnr'] . '<br/><br/>';
	$message .= 'Je situatie:<br/>' . $arr['am_situatie'] . '<br/><br/>';
	$message .= 'Reden      :<br/>' . $arr['am_reden'] . '<br/><br/>';
	$message .= 'Opmerkingen:<br/>' . $arr['am_opmerkingen'] . '<br/><br/>';
	$message .= 'Je hebt je aangemeld voor de volgende optie(s):' . '<br/><br/>';
	if ($arr['optie_mtj']) $message .= 'Persoonlijk begeleiding' . '<br/>';
	if ($arr['optie_jg1']) $message .= 'Jobgroep' . '<br/>';
	if ($arr['optie_jg2']) $message .= 'Jobgroep Ik Werk In Nederland' . '<br/>';
	if ($arr['optie_jg3']) $message .= 'Jobgroep ZZP' . '<br/>';
	if ($arr['optie_wks']) $message .= 'Workshop' . '<br/>';
	if ($arr['optie_vrw']) $message .= 'Vrijwilliger bij JobHulp' . '<br/>';
	if ($arr['optie_and']) $message .= 'Je weet het nog niet' . '<br/>';
	$message .= "<br/><br/>" . 'Wij zullen je aanmelding zo spoedig mogelijk verwerken en vervolgens contact met je opnemen.';
	$message .= "<br/><br/>" . 'Met vriendelijke groeten,';
	$message .= "<br/>" . 'JobHulp Culemborg';
    // error_log($message);
	$name = $arr['am_voornaam'] . ' ' . $arr['am_tussenvoegsels'] . ' ' . $arr['am_achternaam'];
	
	if (!Tools::MailRoom($name, $arr['am_emailadres'], 'Aanmelding JobHulp Culemborg', $message))
		error_log('Mail naar sender mislukt!');
	
	/* Dan een email naar de coordinator */
	
	$message = 'Er is een nieuwe aanmelding binnengekomen via de website jobhulpculemborg.nl<br/><br/><br/>' . $message;
	
	if (!Tools::MailRoom('Coordinator', 'aanmelding@jobhulpculemborg.nl', 'Aanmelding JobHulp Culemborg', $message))
		error_log('Mail naar JHC mislukt!');
	Tools::closeMailer();	
	
	// error_log($message);
	/*    
	JobHulpMaatje       = ja ==> $opties +1
	Jobgroup            = ja ==> $opties +2
	JobGroupiWIN        = ja ==> $opties +4
	JobGroupZZP         = ja ==> $opties +8
	anders              = ja ==> $opties +16 
	vrijwilliger  		  = ja ==> $opties +32
	*/  
	$opties = 0;
	if ($optie1 != '') $opties = $opties + 1;
	if ($optie2 != '') $opties = $opties + 2;
	if ($optie3 != '') $opties = $opties + 4;
	if ($optie4 != '') $opties = $opties + 8;
	if ($optie5 != '') $opties = $opties + 16;
	if ($optie6 != '') $opties = $opties + 32;
	if ($optie7 != '') $optie7 = $opties + 64;
		
	/*********
	 /****  vanaf hier moet de aanmelding in de database worden gezet
	 /*********/     
	$wkz = new Werkzoekende();
	$wkz->voornaam = $arr['am_voornaam'];
	$wkz->tussenvoegsels = $arr['am_tussenvoegsels'];
	$wkz->achternaam = $arr['am_achternaam'];
	$wkz->emailadres = $arr['am_emailadres'];
	$wkz->straat = $arr['am_straat'];
	$wkz->huisnummer = $arr['am_huisnummer'];
	$wkz->postcode = $arr['am_postcode'];
	$wkz->woonplaats = $arr['am_woonplaats'];
	$wkz->telefoonnr = $arr['am_telefoonnr'];
	$wkz->situatie = $arr['am_situatie'];
	$wkz->opmerkingen = $arr['am_opmerkingen'];
	$wkz->opties = $opties;
	$wkz->status = '000';
	$wkz->saveToDB();
	$ps = new Processtap();
	$ps->delind = 'n';
	$ps->id_werkzkd = $wkz->id;
	$ps->id_user = 0;
	$ps->dt_stap = new DateTime();
	$ps->wzstatus = '000';
	$ps->drstrnaar = '';
	$ps->toelichting = 'Nieuw';
	$ps->saveToDB();
	// echo $wkz;
	$_SESSION['flash'] = 'aanmelding_ok';
	header("location: index.php");
	exit();
}

?>
