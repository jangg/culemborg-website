<?php
require_once('config.php');

  if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
      die('Ongeldige aanvraag.');
  }
  $captcha_a = $_SESSION['captcha_a'] ?? 0;
  $captcha_b = $_SESSION['captcha_b'] ?? 0;
  $captcha_antwoord = (int) ($_POST['captcha'] ?? -1);
  
  if ($captcha_antwoord !== ($captcha_a + $captcha_b)) {
      // Fout antwoord — stuur terug
      header('Location: index.php?resc=3#contact');
      exit;
  }

  /*****
  ******  Hier start het prog
  *****/
  if (!isset($_POST['email']) || $_POST['email'] == '')
  {
    header('location: index.php?resc=2#contact');
    exit(); 
  }
  $nameTo = $_POST['name'];
  $emailTo = $_POST['email'];
  $subject = $_POST['subject'];
  $message = 'Beste ' . $nameTo . "<br/><br/>";
  $message .= 'Je hebt het volgende bericht naar ' . LOC_NAME . ' verzonden:<br/><br/>Onderwerp:<br/><br/>';
  $message .= $_POST['subject'];
  $message .= '<br/><br/>Tekst:<br/><br/>';
  $message .= $_POST['message'];
  $message .= "<br/><br/>" . 'Wij zullen zo spoedig mogelijk antwoorden op uw vraag of opmerking.';
  $message .= "<br/><br/>" . 'Met vriendelijke groeten,';
  $message .= "<br/>" . LOC_NAME;
  
  Tools::MailRoom ($nameTo, $emailTo, $subject, $message);  
  
  $receiving_email_address = 'info@' . LOC_DOMAIN_PUB;
  $message = $nameTo . ' (' . $emailTo . ') heeft het volgende bericht verzonden:' . "<br/><br/>";
  $message .= $_POST['message'];
  Tools::MailRoom ($receiving_email_address, $receiving_email_address , $subject, $message);
  header('location: index.php?resc=0#contact');
  exit(); 
  
?>
