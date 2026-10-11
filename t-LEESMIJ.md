# Aanmeldformulier zonder database

Drie bestanden plus PHPMailer. De aanmelding gaat per mail naar de organisatie,
de deelnemer krijgt een bevestiging met agendabestand. Er wordt niets opgeslagen.

```
aanmelden.php             het formulier en de verwerking
includes/config.php       alle instellingen, inclusief de bijeenkomst zelf
includes/functies.php     validatie, opmaak, de twee mails en het .ics-bestand
```

## Installeren

1. Zet de bestanden op de site.
2. **PHPMailer**: via Composer (`composer require phpmailer/phpmailer`) of
   handmatig `Exception.php`, `PHPMailer.php` en `SMTP.php` in
   `includes/PHPMailer/`. Beide manieren worden herkend.
3. Vul `includes/config.php` in: bijeenkomst, contactgegevens, SMTP en het
   WhatsApp-nummer.
4. Test met je eigen adres. Controleer of beide mails aankomen en of de
   `.ics`-bijlage netjes opent.

Volgende bijeenkomst? Alleen `config.php` aanpassen.

## Wat er anders is dan de versie met database

**Vervallen:** deelnemerslijst in de browser, CSV-export, maximumaantal en
"vol"-melding, dubbele aanmeldingen herkennen, en de afmeldlink met unieke
code. Afmelden gaat nu via "antwoord op deze mail of bel ons" — voor een
bijeenkomst van deze omvang werkt dat prima.

**Je deelnemerslijst** is voortaan je mailbox. De onderwerpregel is
`Aanmelding: <naam>`, dus een filterregel die deze mails in een aparte map zet,
geeft je in één oogopslag het overzicht. De reply-to staat op de deelnemer, dus
je kunt direct terugmailen.

**Herinnering sturen** doe je met de hand: map openen, adressen overnemen, en
versturen via **BCC** — nooit via CC. Deelname aan een JobHulp-bijeenkomst zegt
iets over iemands situatie.

## Aandachtspunten

**Als de mail niet aankomt, is de aanmelding weg.** Dat is de prijs van geen
database. Het formulier vangt dit op: mislukt de verzending naar de organisatie,
dan krijgt de bezoeker een duidelijke melding met je telefoonnummer, niet de
mededeling dat het gelukt is. De bevestiging naar de deelnemer is minder
kritiek — mislukt die, dan is de aanmelding zelf toch binnen.

**Vangnet.** Wil je toch iets van zekerheid: zet in `config.php` een pad bij
`LOGBESTAND`, dan komt elke aanmelding er ook als CSV-regel in te staan. Zet dat
bestand **buiten** de public_html, anders is het via de browser op te vragen.
Laat je het leeg, dan wordt er niets weggeschreven.

**E-mail.** Zet `MAIL_VAN` op een adres van hetzelfde domein als waar de site
draait, anders sneuvelt de bezorging op DMARC. In `functies.php` staan bewust
geen DKIM-instellingen: DirectAdmin en Exim ondertekenen al op serverniveau.

**Spam.** Geen captcha — die kost aanmeldingen. In plaats daarvan een verborgen
honeypotveld, een minimale invultijd, een CSRF-token en een rem van vijf
verzendingen per browsersessie. Zonder database kan die rem niet op IP-adres,
dus hij is wat zwakker dan in de vorige versie. Bij een formulier van deze
omvang is dat geen bezwaar; loopt het toch uit de hand, dan is een IP-blokkade
in `.htaccess` de snelste oplossing.

**Bootstrap** komt van de CDN. Draait de rest van de site op een lokale kopie,
verwijs daar dan naar zodat de opmaak gelijk blijft.
