<?php
require_once 'config.php';
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
    <section id="pagehero" class="d-flex align-items-center">
      <div id="overlay"></div>
      <div class="container" data-aos="zoom-out" data-aos-delay="100" style="z-index: 3;">
        <h1 class="col-lg-12 d-none d-lg-block text-center" style="font-size: 4em;">Onze <span>Missie</span></h1>
        <h1 class="col-lg-12 d-lg-none" style="font-size: 2em;">Onze <span>Missie</span></h1>
      </div>
    </section><!-- End Hero -->
    <!-- ======= Page Section ======= -->
    <section id="page" class="section-one">
      <div class="container" data-aos="fade-up">

        <div class="section-title">
          <h2>Wat is onze missie?</h2>
          <h3>En wat zijn onze drijfveren?</h3>
          <p>Het verliezen van een baan is voor veel mensen een ingrijpende ervaring. Opeens heb je geen collega’s meer, geen dagritme en vervalt een deel van je inkomen. Daarnaast ervaren mensen ook verlies van doel en richting in het leven. Het is vervolgens niet altijd gemakkelijk om op eigen kracht een nieuwe baan te vinden.</p> 
          <p>Tegen deze achtergrond is JobHulp Culemborg in 2015 opgericht voor en door werkzoekenden. Onze missie is mensen concreet verder te helpen op weg naar vrijwilligerswerk of betaald werk, waardoor ze weer kunnen deelnemen aan de maatschappij en arbeidsmarkt. Wij komen op voor mensen die een steuntje in de rug kunnen gebruiken. Via onze JobGroepen en JobMaatjes bieden wij intensieve persoonlijke aandacht en ondersteuning op maat, en wij hebben zorg voor ieders’ individuele talenten, kwaliteiten en behoeften.</p>
          <p>JobHulp Culemborg is een vrijwilligersorganisatie en onze getrainde en gecertificeerde vrijwilligers gaan kosteloos als JobMaatje aan de slag middels 1-op-1 begeleiding. Het doel is om mensen weer in hun kracht te zetten.</p>
        </div>
      </div>
    </section>
    
    <!-- ======= Frequently Asked Questions Section ======= -->
    <!-- <section id="faq" class="section-two faq">
      <div class="container" data-aos="fade-up">

        <div class="section-title">
          <h2>F.A.Q.</h2>
          <h3>De meest gestelde vragen over onze missie</span></h3>
          <p>Hier vind je de vragen die het meest zijn gesteld door mensen die willen weten waarom wij dit doen</p>
        </div>

        <div class="row justify-content-center">
          <div class="col-xl-10">
            <ul class="faq-list">

              <li>
                <div data-bs-toggle="collapse" class="collapsed question" href="#faq1">Wat doet <?= LOC_NAME ?>?<i class="bi bi-caret-down-square icon-show"></i><i class="bi bi-caret-up-square icon-close"></i></div>
                <div id="faq1" class="collapse" data-bs-parent=".faq-list">
                  <p>
                    JobHulp Culemborg is een organisatie die bestaat uit mensen die op vrijwillige basis andere mensen (werkzoekenden) bijstaan bij het zoeken naar een baan of vervangend zinvolle arbeid (zoals vrijwilligerswerk of een studie).</p>
                </div>
              </li>

              <li>
                <div data-bs-toggle="collapse" href="#faq2" class="collapsed question">Kan iedereen een JobMaatje worden? <i class="bi bi-caret-down-square icon-show"></i><i class="bi bi-caret-up-square icon-close"></i></div>
                <div id="faq2" class="collapse" data-bs-parent=".faq-list">
                  <p>Om JobMaatje te worden moet je aan een aantal voorwaarden voldoen:</p>
                  <ol>
                    <li>Ouder zijn dan 18 jaar</li>
                    <li>Goed Nederlands kunnen spreken en schrijven</li>
                    <li>Minimaal 2 uur per week beschikbaar hebben</li>
                    <li>Een recente VOG (Verklaring Omtrent Gedrag) kunnen overleggen</li>
                    <li>De training JobMaatje bij JobHulp Culemborg met succes hebben afgelegd</li>
                  </ol>

                </div>
              </li>

            </ul>
          </div>
        </div>

      </div>
    </section><!-- End Frequently Asked Questions Section -->



  </main><!-- End #main -->
  <?php include_once ('includes/footer.php'); ?>

</body>

</html>