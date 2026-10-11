<?php
/**
 * functies.php - opmaak, validatie en verzending
 *
 * PHPMailer laden: via Composer (vendor/autoload.php) of handmatig in
 * includes/PHPMailer/. Beide worden hieronder geprobeerd.
 */

use PHPMailer\PHPMailer\PHPMailer;

if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
} else {
    require_once __DIR__ . '/../PHPMailer/src/Exception.php';
    require_once __DIR__ . '/../PHPMailer/src/PHPMailer.php';
    require_once __DIR__ . '/../PHPMailer/src/SMTP.php';
}

// ---------------------------------------------------------------------------
// Opmaak
// ---------------------------------------------------------------------------

/** Veilig weergeven in HTML.
 *  config.php van de site definieert h() ook (okt 2026); dan wordt die gebruikt.
 *  Zonder deze controle stopt PHP met "Cannot redeclare h()". */
if (!function_exists('h')) {
    function h($tekst)
    {
        return htmlspecialchars((string) $tekst, ENT_QUOTES, 'UTF-8');
    }
}

/** Bijvoorbeeld: donderdag 24 september 2026, 19.30 - 21.30 uur */
function datumTekst()
{
    $dagen = array('zondag', 'maandag', 'dinsdag', 'woensdag', 'donderdag', 'vrijdag', 'zaterdag');
    $maanden = array('', 'januari', 'februari', 'maart', 'april', 'mei', 'juni',
                     'juli', 'augustus', 'september', 'oktober', 'november', 'december');

    $start = strtotime(BIJEENKOMST_START);
    if ($start === false) {
        return '';
    }

    $tekst = $dagen[(int) date('w', $start)] . ' ' . date('j', $start) . ' '
           . $maanden[(int) date('n', $start)] . ' ' . date('Y', $start)
           . ', ' . date('G.i', $start);

    if (BIJEENKOMST_EIND !== '') {
        $eind = strtotime(BIJEENKOMST_EIND);
        if ($eind !== false) {
            $tekst .= ' - ' . date('G.i', $eind);
        }
    }

    return $tekst . ' uur';
}

function locatieTekst()
{
    return implode(', ', array_filter(array(BIJEENKOMST_LOCATIE_NAAM, BIJEENKOMST_LOCATIE_ADRES)));
}

/** Kan er nu nog aangemeld worden? */
function aanmeldenKan()
{
    $sluit = (BIJEENKOMST_AANMELDEN_TOT !== '') ? BIJEENKOMST_AANMELDEN_TOT : BIJEENKOMST_START;
    $tijd  = strtotime($sluit);
    return ($tijd !== false && $tijd >= time());
}

// ---------------------------------------------------------------------------
// Validatie
// ---------------------------------------------------------------------------

/**
 * Haalt de velden uit $_POST en controleert ze.
 * Retourneert array('gegevens' => array(...), 'fouten' => array(veld => tekst)).
 */
function leesFormulier($post)
{
    $gegevens = array(
        'naam'      => '',
        'email'     => '',
        'telefoon'  => '',
        'aantal'    => 1,
        'opmerking' => ''
    );

    // Regeleindes uit de losse velden halen (voorkomt headerinjectie).
    foreach (array('naam', 'email', 'telefoon') as $veld) {
        if (isset($post[$veld]) && is_string($post[$veld])) {
            $gegevens[$veld] = trim(str_replace(array("\r", "\n"), ' ', $post[$veld]));
        }
    }
    if (isset($post['opmerking']) && is_string($post['opmerking'])) {
        $gegevens['opmerking'] = trim($post['opmerking']);
    }
    $gegevens['aantal'] = isset($post['aantal']) ? (int) $post['aantal'] : 1;

    $fouten = array();

    if (mb_strlen($gegevens['naam']) < 2) {
        $fouten['naam'] = 'Vul je naam in.';
    } elseif (mb_strlen($gegevens['naam']) > 100) {
        $fouten['naam'] = 'Deze naam is te lang.';
    }

    if ($gegevens['email'] === '') {
        $fouten['email'] = 'Vul je e-mailadres in, dan sturen we een bevestiging.';
    } elseif (!filter_var($gegevens['email'], FILTER_VALIDATE_EMAIL)) {
        $fouten['email'] = 'Dit e-mailadres klopt niet. Controleer of er een @ en een punt in staat.';
    } elseif (mb_strlen($gegevens['email']) > 150) {
        $fouten['email'] = 'Dit e-mailadres is te lang.';
    }

    if ($gegevens['telefoon'] !== '') {
        $cijfers = preg_replace('/[^0-9]/', '', $gegevens['telefoon']);
        if (strlen($cijfers) < 9 || strlen($cijfers) > 15) {
            $fouten['telefoon'] = 'Dit telefoonnummer klopt niet.';
        }
    }

    if ($gegevens['aantal'] < 1 || $gegevens['aantal'] > MAX_PERSONEN) {
        $fouten['aantal'] = 'Kies een aantal van 1 tot en met ' . MAX_PERSONEN . '.';
    }

    if (mb_strlen($gegevens['opmerking']) > 500) {
        $fouten['opmerking'] = 'Houd de opmerking iets korter (maximaal 500 tekens).';
    }

    return array('gegevens' => $gegevens, 'fouten' => $fouten);
}

// ---------------------------------------------------------------------------
// Verzending
// ---------------------------------------------------------------------------

function maakMailer()
{
    $mail = new PHPMailer(true);
    $mail->CharSet  = 'UTF-8';
    $mail->Encoding = 'base64';

    if (SMTP_GEBRUIK) {
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->Port       = SMTP_POORT;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_GEBRUIKER;
        $mail->Password   = SMTP_WACHTWOORD;
        $mail->SMTPSecure = SMTP_BEVEILIGING;
    }

    $mail->setFrom(MAIL_VAN, MAIL_VAN_NAAM);
    return $mail;
}

/** Agendabestand zodat de deelnemer de datum meteen kan vastleggen. */
function maakAgendaBestand()
{
    $start = strtotime(BIJEENKOMST_START);
    $eind  = (BIJEENKOMST_EIND !== '') ? strtotime(BIJEENKOMST_EIND) : $start + 7200;

    $vouw = function ($tekst) {
        return str_replace(array("\\", "\n", ",", ";"), array("\\\\", "\\n", "\\,", "\\;"), $tekst);
    };

    $regels = array(
        'BEGIN:VCALENDAR',
        'VERSION:2.0',
        'PRODID:-//' . SITE_NAAM . '//Aanmelding//NL',
        'CALSCALE:GREGORIAN',
        'METHOD:PUBLISH',
        'BEGIN:VEVENT',
        'UID:' . md5(BIJEENKOMST_TITEL . BIJEENKOMST_START) . '@' . parse_url(SITE_URL, PHP_URL_HOST),
        'DTSTAMP:' . gmdate('Ymd\THis\Z'),
        'DTSTART:' . gmdate('Ymd\THis\Z', $start),
        'DTEND:'   . gmdate('Ymd\THis\Z', $eind),
        'SUMMARY:'  . $vouw(BIJEENKOMST_TITEL),
        'LOCATION:' . $vouw(locatieTekst()),
        'DESCRIPTION:' . $vouw(BIJEENKOMST_OMSCHRIJVING),
        'BEGIN:VALARM',
        'TRIGGER:-PT2H',
        'ACTION:DISPLAY',
        'DESCRIPTION:' . $vouw(BIJEENKOMST_TITEL),
        'END:VALARM',
        'END:VEVENT',
        'END:VCALENDAR'
    );

    return implode("\r\n", $regels);
}

/**
 * De belangrijkste mail: de aanmelding naar de organisatie.
 * Lukt deze niet, dan is de aanmelding weg - vandaar dat aanmelden.php de
 * retourwaarde controleert en anders het telefoonnummer toont.
 */
function stuurAanmeldingNaarOrganisatie($g)
{
    $regels = array(
        'Naam: '            . $g['naam'],
        'E-mail: '          . $g['email'],
        'Telefoon: '        . ($g['telefoon']  !== '' ? $g['telefoon']  : '-'),
        'Aantal personen: ' . (int) $g['aantal'],
        'Opmerking: '       . ($g['opmerking'] !== '' ? $g['opmerking'] : '-'),
        '',
        'Bijeenkomst: ' . BIJEENKOMST_TITEL,
        'Aangemeld op: ' . date('d-m-Y H:i')
    );

    try {
        $mail = maakMailer();
        $mail->addAddress(MAIL_NAAR);
        $mail->addReplyTo($g['email'], $g['naam']);   // zo kun je meteen terugmailen
        $mail->Subject = 'Aanmelding: ' . $g['naam'];
        $mail->isHTML(false);
        $mail->Body    = implode("\n", $regels);
        $mail->send();
    } catch (Exception $e) {
        error_log('stuurAanmeldingNaarOrganisatie() ' . $e->getMessage());
        return false;
    }

    return true;
}

/** De bevestiging naar de deelnemer, met agendabestand. */
function stuurBevestiging($g)
{
    $naam     = h($g['naam']);
    $personen = (int) $g['aantal'];
    $metWie   = ($personen > 1) ? '<p>Je komt met ' . $personen . ' personen.</p>' : '';

    $html = '<!DOCTYPE html><html lang="nl"><head><meta charset="utf-8"></head>'
          . '<body style="font-family:Arial,Helvetica,sans-serif;font-size:16px;color:#212529;line-height:1.5;">'
          . '<p>Hallo ' . $naam . ',</p>'
          . '<p>Je aanmelding voor <strong>' . h(BIJEENKOMST_TITEL) . '</strong> is binnen. Leuk dat je komt.</p>'
          . '<table cellpadding="6" style="font-size:16px;">'
          . '<tr><td><strong>Wanneer</strong></td><td>' . h(datumTekst()) . '</td></tr>'
          . '<tr><td><strong>Waar</strong></td><td>' . h(locatieTekst()) . '</td></tr>'
          . '</table>'
          . $metWie
          . '<p>In de bijlage zit een agendabestand. Open je dat op je telefoon of computer, '
          . 'dan staat de bijeenkomst meteen in je agenda.</p>'
          . '<p>Kun je toch niet? Stuur dan even een antwoord op deze mail'
          . (CONTACT_TELEFOON !== '' ? ' of bel ' . h(CONTACT_TELEFOON) : '')
          . ', dan komt je plek vrij voor iemand anders.</p>'
          . '<p>Tot ziens,<br>' . (CONTACT_NAAM !== '' ? h(CONTACT_NAAM) . '<br>' : '') . h(SITE_NAAM)
          . (CONTACT_TELEFOON !== '' ? '<br>' . h(CONTACT_TELEFOON) : '') . '</p>'
          . '</body></html>';

    $tekst = 'Hallo ' . $g['naam'] . ",\n\n"
           . 'Je aanmelding voor ' . BIJEENKOMST_TITEL . " is binnen. Leuk dat je komt.\n\n"
           . 'Wanneer: ' . datumTekst() . "\n"
           . 'Waar: ' . locatieTekst() . "\n"
           . ($personen > 1 ? "Je komt met $personen personen.\n" : '')
           . "\nKun je toch niet? Stuur even een antwoord op deze mail"
           . (CONTACT_TELEFOON !== '' ? ' of bel ' . CONTACT_TELEFOON : '') . ".\n\n"
           . "Tot ziens,\n" . (CONTACT_NAAM !== '' ? CONTACT_NAAM . "\n" : '') . SITE_NAAM
           . (CONTACT_TELEFOON !== '' ? "\n" . CONTACT_TELEFOON : '');

    try {
        $mail = maakMailer();
        $mail->addAddress($g['email'], $g['naam']);
        if (CONTACT_EMAIL !== '') {
            $mail->addReplyTo(CONTACT_EMAIL, CONTACT_NAAM);
        }
        $mail->Subject = 'Je aanmelding voor ' . BIJEENKOMST_TITEL;
        $mail->isHTML(true);
        $mail->Body    = $html;
        $mail->AltBody = $tekst;
        $mail->addStringAttachment(
            maakAgendaBestand(),
            'bijeenkomst.ics',
            'base64',
            'text/calendar; charset=utf-8; method=PUBLISH'
        );
        $mail->send();
    } catch (Exception $e) {
        error_log('stuurBevestiging() ' . $e->getMessage());
        return false;
    }

    return true;
}

/** Optioneel vangnet: regel wegschrijven naar een CSV-bestand buiten de webmap. */
function schrijfNaarLogbestand($g)
{
    if (LOGBESTAND === '') {
        return;
    }

    $regel = array(
        date('d-m-Y H:i'),
        $g['naam'],
        $g['email'],
        $g['telefoon'],
        (int) $g['aantal'],
        $g['opmerking']
    );

    $bestand = @fopen(LOGBESTAND, 'a');
    if ($bestand === false) {
        error_log('schrijfNaarLogbestand(): kan ' . LOGBESTAND . ' niet openen');
        return;
    }

    if (flock($bestand, LOCK_EX)) {
        fputcsv($bestand, $regel, ';');
        flock($bestand, LOCK_UN);
    }
    fclose($bestand);
}
