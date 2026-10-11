<?php
include_once 'config.php';

if (isset($_POST['email'])) 
{
	 $message =  $_POST['email'] . ' wil graag de JobHulp nieuwsbrief ontvangen.';

	// Set your email address where you want to receive emails. 
	// $to = 'jang@jobhulpmaatjezoetermeer.nl';
	$subject = 'Aanvraag JobHulp Culemborg nieuwsbrief';

	Tools::MailRoom ($_POST['email'], 'info@jobhulpculemborg.nl, 'Aanvraag JobHulp nieuwsbrief', $message);
	
	$message = 'U heeft de JobHulp Nieuwsbrief aangevraagd. Deze zal de eerst volgende keer dat hij verschijnt, aan u worden toegezonden. Indien u de nieuwsbrief niet meer wilt ontvangen, stuur dan een email naar info@jobhulpculemborg.nl met uw emailadres en de tekst "geen nieuwsbrief meer graag".';
	Tools::MailRoom ($_POST['email'], $_POST['email'], 'Aanvraag JobHulp Culemborg nieuwsbrief', $message);
	Tools::closeMailer();
}
header('location: index.php?res=suc#footer');
exit();
?>