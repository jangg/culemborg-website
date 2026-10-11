<?php
include_once 'config.php';

echo MAIL_LOGDIR . '<br/>';
Tools::MailRoom('Jan GG', 'jangG@mac.com', 'Welkom!', '<b>De melding komt van <a href="https://jobhulpculemborg.nl">JobHulp Culemborg</a>!</b>');
echo "✅ Mail verzonden!";

?>