<?php
include_once('config.php');
if (isset($_SESSION['result']))
{
    $arr = json_decode ($_SESSION['result']);
    unset($_SESSION['result']);
}
?>
<!DOCTYPE html>
<html lang="nl">

<head>
    <?php include_once ('includes/head.php'); ?>
    <link href="/assets/css/style_home.css" rel="stylesheet">
    <style>
    #aanmeld_jg .titelbalk {
      width: 100%;
      height: 40vh;
      min-height: 200px;
      background: url("assets/img/winkelstraat.jpg") top left;
      background-size: cover;
      position: relative;
    }
    </style>

    <script>
        // function isEmail(email) {
        //     var regex = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        //     return regex.test(email);
        //    }
        $(document).ready(function() {
            function isEmail(email) {                
                var mailformat = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/;
                if(email.value.match(mailformat))
                {
                    return true;
                }
                else
                {
                    return false;
                }
               }
            $('#a_wzaanmeld').addClass('active');
            $("#geboortedatum").datepicker({
                dateFormat: "yy-mm-dd",
                minDate: "1950-01-01",
                maxDate: "2001-12-31",
                defaultDate: "1970-03-01",
                yearRange: '1950:2002',
                changeMonth: true,
                changeYear: true,
            });
            $('div.ui-datepicker').css({
                fontSize: '0.9em'
            });
            $('#voornaam').blur(function (){
                if (!$(this).val()) {
                    $(this).siblings('span').show();
                }
                else {
                    $(this).siblings('span').hide();
                }
            });
            $('#achternaam').blur(function (){
                if (!$(this).val()) {
                    $(this).siblings('span').show();
                }
                else {
                    $(this).siblings('span').hide();
                }
            });
            $('#emailadres').blur(function (){
                if (!$(this).val()) {
                    $(this).siblings('span').show();
                }
                else {
                    $(this).siblings('span').hide();
                }
            });
            $('#telefoonnr').blur(function (){
                if (!$(this).val()) {
                    $(this).siblings('span').show();
                }
                else {
                    $(this).siblings('span').hide();
                }
            });
            $('#sendBut').click(function (){
                // Vanaf hier wordt het formulier gecontroleerd 
                var error = 0;
                var voornaam = $('#voornaam').val();
                var achternaam = $('#achternaam').val();
                var emailadres = $('#emailadres').val();
                var straat = $('#straat').val();
                var huisnummer = $('#huisnummer').val();
                var postcode = $('#postcode').val();
                var woonplaats = $('#woonplaats').val();
                var telefoonnr = $('#telefoonnr').val();
                if (voornaam == '') {
                    error = error + 1;
                }
                if (achternaam == '') {
                    error = error + 1;
                }
                if (emailadres == '') {
                    error = error + 1;
                }
                if (telefoonnr == '') {
                    error = error + 1;
                }
                if (!isEmail(emailadres)) {
                    error = error + 1;
                     alert('Het emailadres bevat fouten. Graag corrigeren ajb.');
                }
                    
                if (error === 0)
                {
                    if($('#optie_prv').prop("checked") == false) {
                        $(this).parents('span').show();
                        event.preventDefault();
                        alert('U dient ons toestemming te geven voor het opslaan van uw gegevens');
                    }
                    else {
                        $(this).parents('span').hide();
                        // $('#aanmeldform').submit(function (event) {
                        //     alert ('De appfile is aangeroepen');
                        // });
                         $('#aanmeldform').submit();
                         // alert('WEL check');
                    }
                } else {
                    event.preventDefault();
                    alert('Het formulier bevat fouten. Graag corrigeren ajb.');
                }
             });



         });
    </script>

</head>

<body>
    <?php include_once ('includes/header.php'); ?>
    <main id="main" data-aos="fade-up">
        <!-- ======= Breadcrumbs ======= -->
        <section class="breadcrumbs">
            <div class="container">

                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="text-dark"></h2>
                    <ol>
                        <li><a href="index.php">Home</a></li>
                        <li>Aanmelden partnerbijeenkomst</li>
                    </ol>
                </div>

            </div>
        </section><!-- End Breadcrumbs -->

        <section id="aanmeld_jg" class="aanmeldform pt-0 section-one">
            

            <form id="aanmeldform" action="proces_pb_aanmelding.php" method="POST" novalidate>
            <div class="titelbalk">
                <div id="overlay"></div>
                <div class="container-fluid titelbalk py-5">
                    <div class="row justify-content-center">
                        <div class="col-xl-6" style="z-index: 3;">
                            <h4 class="text-center text-light display-2" style="text-shadow: 3px 3px 8px #1d423e;">Ik kom<br/>naar de partnerbijeenkomst<br/>op 16 juni</h4>
                        </div>
                    </div>
                </div>
            </div>

            <div class="container pt-3 pb-2">
                <!-- <div class="row g-3">
                    <div class="col-xl"></div>
                    <div class="col-xl-8">
                        <h2 class="text-dark my-3">Je wilt hulp. Wat nu?</h2>
                        <p>Gefeliciteerd! Je hebt de eerste stap gezet. Je gaat je aanmelden bij JobHulpMaatje Zoetermeer. Dit is misschien wel de moeilijkste stap van allemaal.</p>
                        <h2 class="text-dark my-3">Wat kun je dan kiezen?</h2>
                        <p>Als je op zoek bent naar werk, willen wij je graag helpen hierbij. Er is echter geen standaard wijze waarop wij deze hulp jou het beste kunnen geven. Daarvoor moeten we weten wie je bent, wat je wil en wat je kan.</p>
                        <p>Als je je aanmeldt bij JobHulpMaatje Zoetermeer is de eerste stap dat we een kennismakingsgesprek met je hebben.</p>
                        <p>In zo'n 'intake'-gesprek die wordt verzorgd door een JobHulpMaatje coördinator, praten we over je situatie, je eisen en wensen en de mogelijkheden.
                            Wij leggen ook uit wat JobHulpMaatje kan doen voor je en ook, heel belangrijk, wat JobHulpMaatje van jou verwacht als we je helpen.</p>
                        <p>Met de informatie die we dan hebben over jou en je wensen, kijken we intern wat volgens ons de beste manier is om je te helpen. En dat kan op verschillende manieren. Als we dat hebben besloten, nemen we weer contact met je op
                            en bespreken deze met je. Gezamenlijk nemen we dan een beslissing.</p>
                        </p>
                        <p>Deze mogelijkheden zijn er:</p>
                        <ul>
                            <li>Je neemt deel aan een Jobgroup</li>
                            <li>Je wordt toegewezen aan een Maatje</li>
                            <li>Je neemt deel aan de reguliere Workshops</li>
                        </ul>
                        <p>

                    </div>
                    <div class="col-xl"></div>
                </div> -->
                <div id="aanmelden" class="row g-3 mb-4">
                    <div class="col-xl"></div>
                    <div class="col-xl-8">
                        <h2 class="text-dark text-center my-3">Laat hier je gegevens achter</h2>
                    </div>
                    <div class="col-xl"></div>
                </div>
                <div class="contact row g-3">
                    <div class="col-xl"></div>
                    <!-- <div class="col-xl-6 d-xl-none"> -->
                        <div class="col-xl-6">
                        <legend class="border pt-1 pb-4 px-3">
                            <div>
                                <label for="voornaam" class="col-form-label p-0 m-0">Voornaam*</label><span>(Verplicht)</span>
                                <input id="voornaam" type="text" name="voornaam" class="form-control m-0" placeholder="" aria-label="voornaam" aria-describedby="voornaam" required value="<?php if (isset($arr->am_voornaam)) echo $arr->am_voornaam; ?>">
                            </div>
                            <div>
                                <label for="tussenvoegsels" class="col-form-label p-0 m-0">Tussenvoegsels</label>
                                <input type="text" name="tussenvoegsels" class="form-control m-0" placeholder="" aria-label="tussenvoegsels" aria-describedby="tussenvoegsels" value="<?php if (isset($arr->am_tussenvoegsels)) echo $arr->am_tussenvoegsels; ?>">
                            </div>
                            <div>
                                <label for="achternaam" class="col-form-label p-0 m-0">Achternaam*</label><span>(Verplicht)</span>
                                <input id="achternaam" type="text" name="achternaam" class="form-control m-0" placeholder="" aria-label="achternaam" aria-describedby="achternaam" required value="<?php if (isset($arr->am_achternaam)) echo $arr->am_achternaam; ?>">
                            </div>
                            <div>
                                <label for="organisatie" class="col-form-label p-0 m-0">Organisatie</label>
                                <input id="organisatie" type="text" id="organisatie" name="organisatie" class="form-control m-0" aria-label="organisatie" aria-describedby="organisatie" value="<?php if (isset($arr->am_organisatie)) echo $arr->am_organisatie; ?>">
                            </div>
                            <div>
                                <label for="aantal" class="col-form-label p-0 m-0">Aantal personen&nbsp&nbsp</label>
                                
                                <select name="aantal" id="aantal" class="form-control m-0">
                                  <option value="1">1</option>
                                  <option value="2">2</option>
                                  <option value="3">3</option>
                                  <option value="4">4</option>
                                </select>
                                <!-- <input id="aantalpers" list="aantal" id="aantalpers" name="aantalpers" class="form-control m-0" aria-label="aantalpers" aria-describedby="aantalpers" value="<?php if (isset($arr->am_aantalpers)) echo $arr->am_aantalpers; else echo '1';?>">
                                <datalist id="aantal">
                                  <option value="alleen">
                                  <option value="met 2 personen">
                                  <option value="met 3 personen">
                                  <option value="met 4 personen">
                                </datalist> -->
                            </div>
                            <div>
                                <label for="emailadres" class="col-form-label p-0 m-0">Emailadres*</label><span>(Verplicht)</span>
                                <input id="emailadres" type="email" id="emailadres1" name="emailadres" class="form-control m-0" aria-label="emailadres" aria-describedby="emailadres" value="<?php if (isset($arr->am_emailadres)) echo $arr->am_emailadres; ?>">
                            </div>
                            <div>
                                <label for="telefoonnr" class="col-form-label p-0 m-0">Telefoonnummer</label>
                                <input id="telefoonnr" type="tel" id="telefoonnr1" name="telefoonnr" class="form-control m-0" aria-label="telefoonnr" aria-describedby="telefoonnr" value="<?php if (isset($arr->am_telefoonnr)) echo $arr->am_telefoonnr; ?>">
                            </div>
                            <span2 style="font-size: .65em; color: #777;">Velden met een * zijn verplicht</span2>
                        </legend>                       
                    </div>
                    <div class="col-xl"></div>
                </div>
                 <div class="contact row">
                    <div class="col-xl"></div>
                    <div class="col-xl-6">
                        <button id="sendBut" type="submit" name="sendBut" value="sendForm" class="btn btn-primary">Verstuur aanmelding</button>
                    </div>
                    <div class="col-xl"></div>
                </div>
            </div>
            <div class="my-3">
                <!-- <div class="loading">laden...</div>
                <div class="error-message"></div>
                <div class="sent-message">Je bericht is verstuurd. Dankjewel!</div> -->
            </div>

            </form>
        </section>

    </main><!-- End #main -->
    <?php include_once ('includes/footer.php'); ?>

</body>

</html>