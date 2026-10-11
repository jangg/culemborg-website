<?php
/**
 * sucstor.php — Succesverhalen (statische versie)
 *
 * Deze pagina toont de succesverhalen van de stichting ZONDER database.
 * De verhalen staan hieronder als vaste array ($sucstorRows) en worden via
 * Sucstor::fromRow() omgezet naar dezelfde objecten die de database-versie
 * ook gebruikt. Daardoor blijft de opmaakcode identiek en is later
 * terugschakelen naar de database een kwestie van de array vervangen door
 * een sucstor_coll-query (zie sucstor_db.php.bak voor de oude versie).
 *
 */

require_once('config.php');
include_once('class/c_sucstor.php');

/* Map waarin de foto's van de succesverhalen staan.
   In de database-versie wijst dit naar de intranet-server. */
if (!defined('IMG_BASE_URL')) {
    define('IMG_BASE_URL', '/imgsucstor/');
}

/* Zet op false zodra de echte verhalen erin staan: verbergt de melding
   dat het om voorbeeldteksten gaat. */
define('SUCSTOR_VOORBEELD', false);

/* -------------------------------------------------------------------------
 * De succesverhalen. De sleutels zijn gelijk aan de kolomnamen van de
 * tabel `sucstor`, zodat Sucstor::fromRow() ze rechtstreeks accepteert.
 *
 *   titel               kop van het verhaal
 *   subtitel            korte ondertitel
 *   tekst_samenvatting  het citaat dat boven het verhaal wordt getoond
 *   tekst_kort          de verhaaltekst (HTML met <p>-tags)
 *   tekst_knop          tekst op de knop; leeg = geen knop
 *   link_knop           doel van de knop (externe URL); leeg = geen knop
 *   datetime_pub_extern publicatiedatum (jjjj-mm-dd uu:mm:ss)
 *   picfile1            bestandsnaam van de foto in /img/sucstor/
 * ---------------------------------------------------------------------- */

$sucstorRows = [
    [
        'id'                  => 1,
        'titel'               => 'Hilde - "Samen kom je verder"',
        'subtitel'            => '',
        'tekst_samenvatting'  => 'Het motto “samen kom je verder” heeft mij doen besluiten om met JobGroep aan de slag te gaan.',
        'tekst_kort'          => '<p>Door de JobGroep bijeenkomsten heb ik ook inzicht gekregen in wat ik belangrijk vind in mijn werk en het heeft mij geleerd om hulp te vragen. Het is mooi te ervaren dat mensen je graag willen helpen en wat een “netwerk” voor je kan doen.</p>'
							   . '<p>JobGroep is hierdoor een echte aanrader. Je wordt op een persoonlijke manier begeleid die aansluit bij jou, door mensen die dit doen met veel passie! Dat maakt JobGroep bijzonder en zorgt het voor succes.</p>',
        'tekst_knop'          => '',
        'link_knop'           => '',
        'datetime_pub_extern' => '',
        'picfile1'            => 'sucstor4.jpg',
    ],
    [
        'id'                  => 2,
        'titel'               => 'Rasa - "Een nieuwe start in Nederland"',
        'subtitel'            => '',
        'tekst_samenvatting'  => 'De JobGroep hielp me ontdekken welk werk echt bij me past',
        'tekst_kort'          => '<p>Om een lang verhaal kort te maken: ik schreef me in voor de cursus en begon samen met een leuke groep mensen acht weken lang te ontdekken 
									wat we wilden doen in onze toekomst. Elke keer leerde ik veel over mezelf en anderen, maar ook over verschillende mentaliteiten en culturele aspecten.</p>'
								 . ' <p>Met veel inspanning vond ik in de laatste week van de JobGroep een geweldige baan. De JobGroep heeft me geholpen te ontdekken welk werk echt bij me past en wat ik wil in mijn carrière. En deze baan is perfect, ik ben er ontzettend blij mee.</p>',
        'tekst_knop'          => '',
        'link_knop'           => '',
        'datetime_pub_extern' => '',
        'picfile1'            => 'sucstor5.jpg',
    ],
    [
        'id'                  => 3,
        'titel'               => 'Angelique - "Met een maatje sta je sterker"',
        'subtitel'            => '',
        'tekst_samenvatting'  => 'Zonder mijn maatje had ik de handdoek al lang in de ring geworpen',
        'tekst_kort'          => '<p>In eerste instantie dacht ik dat ik het allemaal wel alleen kon. Totdat mijn zoektocht naar een leuke nieuwe baan langer duurde 
								dan ik zelf had verwacht. Ik zag de advertentie van JobHulp in de krant</p>'
                               . '<p>Mijn JobMaatje heeft mij de handvaten aangereikt die ik nodig had om ervoor te zorgen dat ik bij een sollicitatie eruit zou springen. Dit deed zij door te luisteren en een vertaalslag te maken, mij inzicht te geven en te motiveren als het even tegenzat en vooral door er altijd te zijn en een luisterend oor te bieden. Dankjewel JobHulp voor het fantastisch werk wat jullie doen! Jullie inzet is ongekend</p>',
        'tekst_knop'          => '',
        'link_knop'           => '',
        'datetime_pub_extern' => '',
        'picfile1'            => 'sucstor6.jpg',
    ],
];

/* Omzetten naar Sucstor-objecten, precies zoals de database-versie dat doet. */
$sucstorColl = [];
foreach ($sucstorRows as $row) {
    $sucstorColl[] = Sucstor::fromRow($row);
}

/**
 * Datum in het Nederlands, met IntlDateFormatter wanneer de intl-extensie
 * beschikbaar is en anders via Tools::ConvertTS().
 */
function sucstorDatum(string $datetime): string
{
    if ($datetime === '') {
        return '';
    }
    if (class_exists('IntlDateFormatter')) {
        $formatter = new IntlDateFormatter(
            'nl_NL',
            IntlDateFormatter::FULL,
            IntlDateFormatter::NONE
        );
        $formatter->setPattern('EEEE d MMMM yyyy');
        return $formatter->format(new DateTimeImmutable($datetime));
    }
    return Tools::ConvertTS($datetime);
}

/* -------------------------------------------------------------------------
 * Opbouw van de HTML. Even verhalen: foto links, tekst rechts.
 * Oneven verhalen: foto rechts, tekst links (vanaf lg-breedte).
 * ---------------------------------------------------------------------- */

$sucstorHTML = '';

if (empty($sucstorColl)) {
    $sucstorHTML = '<p class="text-muted">Er zijn momenteel nog geen succesverhalen.</p>';
} else {
    foreach ($sucstorColl as $index => $sucstor) {

        $fotoRechts = ($index % 2 === 1);
        $ordFoto    = $fotoRechts ? ' order-lg-2' : ' order-lg-1';
        $ordTekst   = $fotoRechts ? ' order-lg-1' : ' order-lg-2';

        $id        = (int) $sucstor->id;
        $titel     = htmlspecialchars($sucstor->titel,              ENT_QUOTES, 'UTF-8');
        $subtitel  = htmlspecialchars($sucstor->subtitel,           ENT_QUOTES, 'UTF-8');
        $citaat    = htmlspecialchars($sucstor->tekst_samenvatting, ENT_QUOTES, 'UTF-8');
        $tekstKnop = htmlspecialchars($sucstor->tekst_knop,         ENT_QUOTES, 'UTF-8');
        $picfile1  = htmlspecialchars($sucstor->picfile1,           ENT_QUOTES, 'UTF-8');
        $datumStr  = sucstorDatum($sucstor->datetime_pub_extern);

        /* Externe link valideren, net als in de database-versie. */
        $linkKnop = '';
        if ($sucstor->link_knop !== '') {
            $linkKnop = filter_var($sucstor->link_knop, FILTER_VALIDATE_URL)
                ? $sucstor->link_knop
                : '#';
        }
        $sucstorHTML .= '
  <div class="row align-items-stretch" id="SV' . $id . '">

    <div class="col-lg-3 d-flex align-items-stretch pb-0 mb-4' . $ordFoto . '">
      <div class="card image-box p-1" data-aos="fade-up" data-aos-delay="400" style="width: 100%;">';

        if ($picfile1 !== '') {
            $sucstorHTML .= '<img src="' . IMG_BASE_URL . $picfile1 . '" class="m-0" style="height: 100%; object-fit: cover;" alt="' . $titel . '">';
        }

        $sucstorHTML .= '
      </div>
    </div>

    <div class="col-lg-9 d-flex mb-4' . $ordTekst . '">
      <div class="card text-box" data-aos="zoom-in" data-aos-delay="200" style="width: 100%;">
        <div class="card-header text-dark">
          <p>' . $datumStr . '</p>
          <h2>' . $titel . '</h2>
          <h5>' . $subtitel . '</h5>
        </div>
        <div class="card-body">';

        if ($citaat !== '') {
            $sucstorHTML .= '
          <blockquote class="sucstor-quote">
            <i class="bi bi-quote"></i>
            <p>' . $citaat . '</p>
          </blockquote>';
        }

        $sucstorHTML .= '
          ' . Tools::getShortPost($sucstor->tekst_kort, 1000) . '
        </div>';

        /* Voettekst met knop alleen tonen wanneer er een knop is. */
        if ($tekstKnop !== '' && $linkKnop !== '') {
            $sucstorHTML .= '
        <div class="card-footer">
          <a href="' . htmlspecialchars($linkKnop, ENT_QUOTES, 'UTF-8') . '" target="_blank" rel="noopener noreferrer"><button class="btn btn-primary">' . $tekstKnop . '</button></a>
        </div>';
        }

        $sucstorHTML .= '
      </div>
    </div>
  </div>';
    }

}
?>
<!DOCTYPE html>
<html lang="nl">

<head>
  <?php include_once('includes/head.php'); ?>
  <link href="/assets/css/style_home.css" rel="stylesheet">
  <link href="/assets/css/style_nieuws.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/Wruczek/Bootstrap-Cookie-Alert@gh-pages/cookiealert.css">
  <style>
    /* Citaat boven het verhaal */
    #nieuwsberichten .sucstor-quote {
      border-left: 4px solid #69468b;
      margin: 0 0 1.2em 0;
      padding: 0.2em 0 0.2em 1em;
      color: #3f3f3f;
    }

    #nieuwsberichten .sucstor-quote p {
      margin: 0;
      font-size: 1.15em;
      font-style: italic;
      line-height: 1.5;
    }

    #nieuwsberichten .sucstor-quote .bi-quote {
      font-size: 1.8em;
      line-height: 1;
      color: #69468b;
    }
  </style>
  <script>
    $(document).ready(function () {
      $('#a_home').removeClass('active');
      $('#a_sucstor').addClass('active');
    });
  </script>
</head>

<body>
  <?php include_once('includes/header.php'); ?>

  <!-- ======= Hero Section ======= -->
  <section id="hero" class="d-flex align-items-center" style="height: 30vh;">
    <div id="overlay"></div>
    <div class="container" data-aos="zoom-out" data-aos-delay="100" style="z-index: 3;">
      <h1 class="col-lg-12 d-none d-xl-block my-4" style="font-size: 6em;">Succesverhalen</h1>
      <h1 class="col-lg-12 d-none d-xl-block my-4" style="font-size: 3em;">van <span><?= LOC_NAME ?></span></h1>
      <h1 class="col-lg-12 d-xl-none my-1" style="font-size: 3em;">Succesverhalen</h1>
      <h1 class="col-lg-12 d-xl-none my-1" style="font-size: 1.5em;">van <span><?= LOC_NAME ?></span></h1>
    </div>
  </section><!-- End Hero -->

  <main id="main" data-aos="fade-up">

    <!-- ======= Breadcrumbs ======= -->
    <section class="breadcrumbs">
      <div class="container">
        <div class="d-flex justify-content-between align-items-center">
          <h2 class="text-dark"></h2>
          <ol>
            <li><a href="index.php">Home</a></li>
            <li>Succesverhalen</li>
          </ol>
        </div>
      </div>
    </section><!-- End Breadcrumbs -->

    <!-- ======= Introductie Section ======= -->
    <section id="introductie" style="padding-top: 40px; padding-bottom: 40px; background-color: #B19DC4;">
      <div class="container" data-aos="fade-up">
        <div class="row gy-4">
          <h1 class="text-center text-black">Samen op weg naar passend werk</h1>
    
          <div class="col-lg-12 d-flex">
            <div class="card text-box h-100" data-aos="zoom-in" data-aos-delay="100" style="width: 100%;">
              <!-- <div class="card-header text-dark"> -->
                <!-- <h2>Maatjes relaas</h2>
                <h5>Wat heeft hij te zeggen</h5> -->
              <!-- </div> -->
              <div class="card-body">
                <h2>Onze werkzoekenden en JobMaatjes laten zien wat er mogelijk is met de juiste begeleiding, motivatie en samenwerking.</h2>
                <h2>Laat je inspireren door hun verhalen</h2>
                </h1>
              </div>
            </div>
          </div>
      </div>
    </div>
  </section><!-- EndIntroductie Section -->
    

	<!-- ======= Succesverhalen Section ======= -->
	<section id="nieuwsberichten" style="padding-top: 40px; background-color: #EAE4F3;">
	  <div class="container" data-aos="fade-up">
		<div class="section-title">
		  <?php echo $sucstorHTML; ?>
		</div>
	  </div>
	</section><!-- End Succesverhalen Section -->

    <!-- ======= Maatjesverhalen Section ======= -->
  <section id="maatjesverhalen" style="padding-top: 40px; padding-bottom: 40px; background-color: #B19DC4;">
    <div class="container" data-aos="fade-up">
      <div class="row gy-4">
        <h1 class="text-center text-black">Wat maatjes zeggen</h1>
  
        <div class="col-lg-4 d-flex">
          <div class="card text-box h-100" data-aos="zoom-in" data-aos-delay="100" style="width: 100%;">
            <div class="card-header text-dark">
              <h2>Uitdagen</h2>
              <!-- <h5>Wat heeft hij te zeggen</h5> -->
            </div>
            <div class="card-body">
              <p><b>“Ik vind het waardevol om mensen op weg te helpen. Ik kan als maatje mijn ervaring inzetten om mensen te begeleiden. Tegelijk leer ik hier zelf ook veel van en blijf ik mezelf uitdagen en ontwikkelen, dat vind ik belangrijk.” </b></p>
            </div>
          </div>
        </div>
  
        <div class="col-lg-4 d-flex">
          <div class="card text-box h-100" data-aos="zoom-in" data-aos-delay="200" style="width: 100%;">
            <div class="card-header text-dark">
              <h2>Voldoening</h2>
              <!-- <h5>Wat heeft hij te zeggen</h5> -->
            </div>
            <div class="card-body">
              <p><b>“Anderen helpen een baan te vinden, soms zichzelf te (her)ontdekken, brengt mij veel voldoening en vreugde.”</b></p>
            </div>
          </div>
        </div>
  
        <div class="col-lg-4 d-flex">
          <div class="card text-box h-100" data-aos="zoom-in" data-aos-delay="300" style="width: 100%;">
            <div class="card-header text-dark">
              <h2>Zijwieltjes</h2>
              <!-- <h5>Wat heeft hij te zeggen</h5> -->
            </div>
            <div class="card-body">
              <p><b>“Werken als JobMaatje voelt als een eindje met iemand meerijden, waarbij ik de zijwieltjes ben, die langzaam maar zeker eraf gehaald worden, waarna die persoon zelfstandig wegfietst”</b></p>
            </div>
          </div>
        </div>
  
      </div>
    </div>
  </section><!-- End Maatjesverhalen Section -->
  <?php include 'includes/sect-aanmeldbutton.php'; ?>
  </main><!-- End #main -->

  <?php include_once('includes/footer.php'); ?>

</body>

</html>
