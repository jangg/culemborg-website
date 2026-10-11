<?php
require_once ('config.php');
?>
<!DOCTYPE html>
<html lang="nl">

<head>
  <?php include_once ('includes/head.php'); ?>
  <link href="/assets/css/style_stichting.css" rel="stylesheet">
  
  <!-- cookiealert styles -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/Wruczek/Bootstrap-Cookie-Alert@gh-pages/cookiealert.css">
  <script>
    $(document).ready(function() {
      $('#a_over').addClass('active');
    });
  </script>

</head>

<body>
  <?php include_once ('includes/header.php'); ?>


  <main id="main">
    <!-- <main id="main"> -->

    <!-- ======= Hero Section ======= -->
    <section id="pagehero" class="d-flex align-items-center" style='background: url("assets/img/hero3-bg.jpg"); background-size: cover; background-position: center;'>
      <div id="overlay"></div>
      <div class="container" data-aos="zoom-out" data-aos-delay="100" style="z-index: 3;">
        <h1 class="col-lg-12 d-none d-lg-block text-center" style="font-size: 4em;">Onze <span>organisatie</span></h1>
        <h1 class="col-lg-12 d-lg-none" style="font-size: 2em;">Onze <span>organisatie</span></h1>
        <!-- <h1 class="col-lg-12 d-lg-none" style="font-size: 2em;">Welkom bij <span>JobHulpMaatje Zoetermeer</span></h1>
      <h2 class="my-5">Als je op zoek bent naar werk en je kunt wat hulp gebruiken</h2>
      <div class="d-flex">
        <a href="#about" class="btn-get-started scrollto mx-2" style="width: 150px;  border-radius: 5px;">Begin hier</a>
        <a href="#news" class="btn-read-news border border-white scrollto text-white" style="width: 150px;  border-radius: 5px;"><i class="bi bi-newspaper"></i></i><span>  Nieuws</span></a>
      </div> -->
      </div>
    </section><!-- End Hero -->
    <!-- ======= Page Section ======= -->
    <section id="page" class="section-two">
      <div class="container" data-aos="fade-up">

        <div class="section-title">
          <h2>Wie doen het</h2>
          <h3>Alle betrokken mensen</h3>
          <div class="row mb-4 g-5">
            <div class="col-lg">
              <p>JobHulp Culemborg is een Goede Doelen Organisatie. Wij zijn ook een ANBI erkende stichting (RSIN: 855158049). Dat betekent dat de overheid formeel erkent dat JobHulp Culemborg geen belasting hoeft te betalen over giften en subsidies die zij ontvangt van sponsoren. En dat voor gevers giften aan JobHulp Culemborg aftrekbaar zijn voor belastingen. Voor meer informatie over de status van ANBI, zie <a href="https://www.belastingdienst.nl/wps/wcm/connect/bldcontentnl/belastingdienst/zakelijk/bijzondere_regelingen/goede_doelen/algemeen_nut_beogende_instellingen/wat_is_een_anbi" target="_blank">hier</a></p>
              <p>De stichting heeft een bestuur dat uit 4 mensen bestaat en sinds januari 2026 zijn drie nieuwe leden toegetreden tot het bestuur. Bestuursleden worden voor onbepaalde tijd benoemd.</p>
              <p>De operationele leiding ligt bij de Coördinator. Deze wordt bijgestaan door één of meerdere JobMaatjes die samen het Intaketeam vormen. De coördinator is een ZZP’er die tegen vergoeding invulling geeft aan de rol van Coördinator en JobGroep-trainer. Voor de uitvoering van deze taken is een vastgesteld aantal uren beschikbaar.</p>
              <p>De overige vrijwilligers zijn JobMaatjes of aspirant-JobMaatjes of vrijwilligers met een rol als redactielid, fondsenwerver of webmaster.</p>
              <p>Een heel belangrijke rol is weggelegd voor de organisaties (kerken, bedrijven, overheid en fondsen) die door giften of subsidies het werk van JobHulp Culemborg mogelijk maken. Zonder hen kan JobHulp Culemborg niet bestaan. Dankzij het feit dat zij de maatschappelijke waarde van JobHulp Culemborg al meer dan 10 jaar erkennen, kunnen wij mensen helpen bij het vinden van passend werk. Wij zijn hen, mede namens de vele werkzoekenden die over de afgelopen jaren door JobHulp Culemborg zijn begeleid, hier zeer erkentelijk voor.</p>
            </div>
        </div>
      </div>
    </section>
    
    <!-- ======= sponsors Section ======= -->
    <?php 
    /* forceer achtergrondkleur naar groen */
    global $sectionNumber;
    $sectionNumber = 'two';
    include ('includes/sect-sponsors.php'); 
    ?>
  
    <!-- ======= Frequently Asked Questions Section ======= -->
    <section id="faq" class="faq section-two">
      <div class="container" data-aos="fade-up">

        <div class="section-title">
          <h2>F.A.Q.</h2>
          <h3>De meest gestelde <span>vragen</span> over de organisatie</h3>
            <p>Hier vind je de vragen die het meest zijn gesteld over onze zienswijze op de arbeidsmarkt en onze rol daarin</p>
        </div>

        <div class="row justify-content-center">
          <div class="col-xl-10">
            <ul class="faq-list">

              <li>
                <div data-bs-toggle="collapse" class="collapsed question" href="#faq1">Wat doet <?= LOC_NAME ?> wel en wat doet ze niet<i class="bi bi-caret-down-square icon-show"></i><i class="bi bi-caret-up-square icon-close"></i></div>
                <div id="faq1" class="collapse" data-bs-parent=".faq-list">
                  <p>JobHulp Culemborg helpt mensen bij het zoeken naar werk. Wij bieden daarbij ondersteuning bij het vergroten van (zelf)kennis en vaardigheden, zoals bijvoorbeeld het geven van advies voor het opstellen van een CV, hoe te netwerken en jezelf te presenteren op sociale media en hoe je je kunt voorbereiden op een sollicitatiegesprek.</p>
                  <p>Kortom, wij "trainen" mensen om het beste uit zichzelf te halen als het aankomt op het zoeken naar werk. </p>
                  <p>Dat trainen doen we met behulp van groepsworkshops en individueel, en met behulp van een JobMaatje.</p>
                  <p>Waar mogelijk proberen wij ook, via onze netwerken, de werkzoekende in contact te brengen met mensen die hen misschien een stapje verder kunnen brengen.</p>
                  
                  <p>Wat JobHulp Culemborg niet doet, is bemiddelen tussen de werkzoekende en mogelijke werkgevers of meegaan op sollicitatiebezoek. En ook schrijft JobHulp Culemborg geen brieven of e-mails. Uiteindelijk moet de werkzoekende zelf aan de slag om een baan te vinden. De grondregel is dat de werkzoekende zelf verantwoordelijk is en blijft voor het slagen (of niet slagen) van de zoektocht.</p>
                  <p>De grondregel is dat de werkzoekendete allen tijde zelf verantwoordelijk blijft voor het slagen (of niet slagen) van de zoektocht.
                </div>
              </li>

              <!-- <li>
                <div data-bs-toggle="collapse" href="#faq2" class="collapsed question">? <i class="bi bi-caret-down-square icon-show"></i><i class="bi bi-caret-up-square icon-close"></i></div>
                <div id="faq2" class="collapse" data-bs-parent=".faq-list">
                  <p>Om maatje te worden moet je aan een aantal voorwaarden voldoen:</p>
                  <ol>
                    <li>Ouder zijn dan 18 jaar</li>
                    <li>Goed Nederlands kunnen spreken en schrijven</li>
                    <li>Minimaal 2 uur per week beschikbaar hebben</li>
                    <li>Een recente VOG (Verklaring Omtrent Gedrag) kunnen overleggen</li>
                    <li>De training maatje bij JobHulpMaatje Nederland met succes hebben afgelegd</li>
                  </ol>

                </div>
              </li> -->

            </ul>
          </div>
        </div>

      </div>
    </section><!-- End Frequently Asked Questions Section -->



  </main><!-- End #main -->
  <?php include_once ('includes/footer.php'); ?>

</body>

</html>