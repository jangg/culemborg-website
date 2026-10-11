<?php
/* ------------------------------------------------------------------
 * VOORBEELD van includes/t-config.php (instellingen van t-aanmelden.php)
 *
 * De echte t-config.php staat NIET in git, omdat hij het SMTP-wachtwoord
 * bevat. Kwijt? Kopieer dit bestand naar t-config.php en vul alles in,
 * of haal hem van de server (jobhulpculemborg.nl/includes/t-config.php).
 * Zie t-LEESMIJ.md voor de uitleg.
 * ------------------------------------------------------------------ */

// --- De bijeenkomst ------------------------------------------------
define('BIJEENKOMST_TITEL',         'Naam van de bijeenkomst');
define('BIJEENKOMST_OMSCHRIJVING',  'Korte omschrijving (komt ook in het agendabestand).');
define('BIJEENKOMST_START',         '2026-11-19 19:30');   // jjjj-mm-dd uu:mm
define('BIJEENKOMST_EIND',          '2026-11-19 21:30');   // leeg = start + 2 uur
define('BIJEENKOMST_AANMELDEN_TOT', '');                   // leeg = tot de start
define('BIJEENKOMST_LOCATIE_NAAM',  'Naam van de locatie');
define('BIJEENKOMST_LOCATIE_ADRES', 'Straat 1, 4100 AA Culemborg');

// --- De site en contact --------------------------------------------
define('SITE_NAAM',        'JobHulp Culemborg');
define('SITE_URL',         'https://jobhulpculemborg.nl');
define('CONTACT_NAAM',     '');                            // leeg = niet tonen
define('CONTACT_EMAIL',    '');                            // reply-to op de bevestiging; leeg = geen
define('CONTACT_TELEFOON', '');                            // leeg = niet tonen
define('WHATSAPP_NUMMER',  '31600000000');                 // internationaal, zonder + en spaties
define('WHATSAPP_TEKST',   'Hallo, ik heb een vraag over de bijeenkomst.');

// --- Mail ----------------------------------------------------------
define('MAIL_NAAR',     'aanmelding@jobhulpculemborg.nl'); // de organisatie
define('MAIL_VAN',      'no-reply@jobhulpculemborg.nl');   // hetzelfde domein als de site (DMARC)
define('MAIL_VAN_NAAM', 'JobHulp Culemborg');

define('SMTP_GEBRUIK',     true);            // false = PHP mail()
define('SMTP_HOST',        'mail.voorbeeld.nl');
define('SMTP_POORT',       465);
define('SMTP_BEVEILIGING', 'ssl');           // 'tls' (587) of 'ssl' (465)
define('SMTP_GEBRUIKER',   'no-reply@jobhulpculemborg.nl');
define('SMTP_WACHTWOORD',  '');              // NOOIT in git zetten

// --- Spam-rem en vangnet --------------------------------------------
define('MAX_PERSONEN',   4);     // maximaal aantal personen per aanmelding
define('MAX_PER_BEZOEK', 5);     // maximaal aantal verzendingen per browsersessie
define('MIN_INVULTIJD',  5);     // seconden; sneller ingevuld = waarschijnlijk een bot
define('LOGBESTAND',     '');    // pad BUITEN public_html voor een CSV-kopie; leeg = niets
