<!DOCTYPE html>
<html lang="nl">

<head>
  <?php include_once ('includes/head.php'); ?>
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
        <h1 class="col-lg-12 d-none d-lg-block text-center" style="font-size: 4em;">Helpen als <span>JobHulpMaatje</span></h1>
        <h1 class="col-lg-12 d-lg-none" style="font-size: 2em;">Helpen als <span>JobHulpMaatje</span></h1>
        <!-- <h1 class="col-lg-12 d-lg-none" style="font-size: 2em;">Welkom bij <span>JobHulpMaatje Zoetermeer</span></h1>
      <h2 class="my-5">Als je op zoek bent naar werk en je kunt wat hulp gebruiken</h2>
      <div class="d-flex">
        <a href="#about" class="btn-get-started scrollto mx-2" style="width: 150px;  border-radius: 5px;">Begin hier</a>
        <a href="#news" class="btn-read-news border border-white scrollto text-white" style="width: 150px;  border-radius: 5px;"><i class="bi bi-newspaper"></i></i><span>  Nieuws</span></a>
      </div> -->
      </div>
    </section><!-- End Hero -->
    <!-- ======= Page Section ======= -->
    <section id="page" class="page faq-bg">
      <div class="container" data-aos="fade-up">

        <div class="section-title">
          <h2>Maatje zijn</h2>
          <h3>Wat betekent het om <span>maatje</span> te zijn?</h3>
          <p>Zet je talenten in voor <?= LOC_NAME ?>. De trainingsdagen voor maatjes worden, zodra ze bekend zijn, gemeld in de agenda. Doe je mee?</p>
        </div>
      </div>
    </section>
    <section id="page" class="page">
      <div class="container" data-aos="fade-up">
        <div class="row">
          <!-- <div class="col-lg-12" data-aos="fade-right" data-aos-delay="100">
            <img src="assets/img/imgwp/home_bericht-aandeslagmetgroep2.jpg" class="img-fluid rounded my-3" alt="">
            <img src="assets/img/imgwp/home_slideshow-3.jpg" class="img-fluid rounded my-3" alt="">
          </div> -->
          <div class="col-lg-12 pt-4 pt-lg-0 content d-flex flex-column justify-content-center" data-aos="fade-up" data-aos-delay="100">
            <ul>
              <li>
                <div class="row">
                  <div class="column1"><i class="fas fa-handshake"></i></div>
                  <div class="column2">
                    <h3>Wat is een maatje?</h3>
                    <p class="fst-italic">Korte uitleg</p>
                    <h4>Ondersteuning geven</h4>
                    <p>Zoeken naar werk kan een lastige taak zijn. Door allerlei omstandigheden kan iemand in een ongewenste situatie terecht komen en daarbij zijn of haar werk verliezen.
                      Vaak is het vinden van nieuw werk dan weliswaar niet altijd leuk maar het lukt wel. Maar in veel gevallen blijkt het toch veel lastiger te zijn om een nieuwe baan te vinden</p>
                    <p>Als je als werkzoekende er alleen voor staat, kan een maatje die je helpt en steunt een uitkomst zijn. Je maatje kijkt met je mee, geeft je tips en praat je moed in. Precies wat je nodig hebt
                      bij je zoektocht naar nieuw werk.
                    </p>
                    <p><?= LOC_NAME ?> beschikt over veel vrijwilligers die werkzoekenden kunnen bij staan in hun zoektocht.</p>
                  </div>
                </div>
              </li>
              <li>
                <div  class="row">
                  <div class="column1"><i class="fas fa-list-alt"></i></div>
                  <div class="column2">
                    <h3>Hoe word je maatje bij <?= LOC_NAME ?>?</h3>
                    <p class="fst-italic">Korte uitleg</p>
                    <h4>Voldoe aan bepaalde criteria</h4>
                    <p>Om een maatje te zijn moet iemand aan bepaalde voorwaarden voldoen. Sommige zijn goed te bepalen, andere wat lastiger omdat ze moeilijk meetbaar zijn.</p>
                    <p>Zo moet je woonachtig zijn in Culemborg of in de omgeving van Culemborg. De werkzoekenden komen immers ook uit deze omgeving.</p>
                    <p>Je moet 18 jaar of ouder zijn.</p>
                    <p>Je moet goed Nederlands spreken en schrijven.</p>
                    <p>Je moet beschikken over de gewenste sociale vaardigheden, zoals empathie, inlevingsvermogen en geduld.</p>
                  <div>
                </div>
              </li>
              <li>
                <div  class="row">
                  <div class="column1"><i class="fas fa-comments"></i></div>
                  <div class="column2">
                    <h3>Wil je maatje worden?</h3>
                    <p class="fst-italic">Meld je aan</p>
                    <h4>Het begint met een gesprek</h4>
                    <p>Als je je aanmeld m.b.v. onderstaand formulier en je hebt het verstuurd, dan nemen we daarna snel contact met je op.</p>
                    <p>Eén van onze coördinatoren maakt een afspraak met je om kennis te maken en bespreekt de mogelijkheden.</p>
                    <p>Als dat goed gaat en we zijn het eens, dan starten we de volgde fase zoals het inschrijven voor de training JobHulpMaatje.</p>
                   <div>
                </div>
              </li>
            </ul>
          </div>
        </div>

      </div>
    </section><!-- End About Section -->


    <!-- ======= Frequently Asked Questions Section ======= -->
    <section id="faq" class="faq faq-bg">
      <div class="container" data-aos="fade-up">

        <div class="section-title">
          <h2>F.A.Q.</h2>
          <h3>Frequently Asked <span>Questions</span></h3>
          <h4 class="text-dark">(Veel gestelde <span>vragen over maatje zijn</span>)</h4>
          <p>Hier vind je de vragen die het meest zijn gesteld door mensen die geïnteresseerd zijn om maatje bij <?= LOC_NAME ?> te worden</p>
        </div>

        <div class="row justify-content-center">
          <div class="col-xl-10">
            <ul class="faq-list">

              <li>
                <div data-bs-toggle="collapse" class="collapsed question" href="#faq1">Wat doet <?= LOC_NAME ?>?<i class="bi bi-caret-down-square icon-show"></i><i class="bi bi-caret-up-square icon-close"></i></div>
                <div id="faq1" class="collapse" data-bs-parent=".faq-list">
                  <p>
                    <?= LOC_NAME ?> is een organisatie die bestaat uit mensen die op vrijwillige basis andere mensen (werkzoekenden) bijstaan bij het zoeken naar een baan of vervangend zinvolle arbeid (zoals vrijwilligerswerk of een studie).</p>
                </div>
              </li>

              <li>
                <div data-bs-toggle="collapse" href="#faq2" class="collapsed question">Kan iedereen een maatje worden? <i class="bi bi-caret-down-square icon-show"></i><i class="bi bi-caret-up-square icon-close"></i></div>
                <div id="faq2" class="collapse" data-bs-parent=".faq-list">
                  <p>Om maatje te worden moet je aan een aantal voorwaarden voldoen:</p>
                  <ol>
                    <li>Ouder zijn dan 18 jaar</li>
                    <li>Goed Nederlands kunnen spreken en schrijven</li>
                    <li>Minimaal 2 uur per week beschikbaar hebben</li>
                    <li>Een recente VOG (Verklaring Omtrent Gedrag) kunnen overleggen</li>
                    <li>De training maatje bij <?= LOC_NAME ?> met succes hebben afgelegd</li>
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