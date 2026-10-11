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
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/ui/1.14.1/jquery-ui.min.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.14.1/themes/base/jquery-ui.css">

    <script>
        $(document).ready(function() {
            function isEmail(email) {
              return /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(email);
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
                        <li>Aanmelden</li>
                    </ol>
                </div>

            </div>
        </section><!-- End Breadcrumbs -->

        <section id="aanmeld_jg" class="aanmeldform pt-0 section-one">
            

            <form id="aanmeldform" action="proces_aanmelding.php" method="POST" novalidate>
            <div class="titelbalk">
                <div id="overlay"></div>
                <div class="container-fluid titelbalk py-5">
                    <div class="row justify-content-center">
                        <div class="col-xl-6" style="z-index: 3;">
                            <h4 class="text-center text-light display-2" style="text-shadow: 3px 3px 8px #1d423e;">Aanmelden</h4>
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
                        <h2 class="text-dark my-3">Het aanmeldformulier</h2>
                        <p>Als je het formulier hieronder invult en verstuurt dan nemen we daarna zo snel mogelijk contact me je op. We maken dan een vervolgafspraak.
                    </div>
                    <div class="col-xl"></div>
                </div>
                <div class="contact row g-3">
                    <div class="col-xl"></div>
                    <!-- <div class="col-xl-6 d-xl-none"> -->
                        <div class="col-xl-6">
                        <fieldset>
                        <legend class="border pt-1 pb-4 px-3">Persoonlijke gegevens
                            <div>
                                <label for="voornaam" class="col-form-label p-0 m-0">Voornaam*</label><span>(Verplicht)</span>
                                <input id="voornaam" type="text" name="voornaam" class="form-control m-0" placeholder="" aria-label="voornaam" aria-describedby="voornaam" required value="<?php if (isset($arr->am_voornaam)) echo $arr->am_voornaam; ?>">
                            </div>
                            <div>
                                <label for="tussenvoegsels" class="col-form-label p-0 m-0">Tussenvoegsels</label>
                                <input id="tussenvoegsels" type="text" name="tussenvoegsels" class="form-control m-0" placeholder="" aria-label="tussenvoegsels" aria-describedby="tussenvoegsels" value="<?php if (isset($arr->am_tussenvoegsels)) echo $arr->am_tussenvoegsels; ?>">
                            </div>
                            <div>
                                <label for="achternaam" class="col-form-label p-0 m-0">Achternaam*</label><span>(Verplicht)</span>
                                <input id="achternaam" type="text" name="achternaam" class="form-control m-0" placeholder="" aria-label="achternaam" aria-describedby="achternaam" required value="<?php if (isset($arr->am_achternaam)) echo $arr->am_achternaam; ?>">
                            </div>
                            <div>
                                <label for="straat" class="col-form-label p-0 m-0">Straat</label>
                                <input id="straat" type="text" name="straat" class="form-control m-0" aria-label="straat" aria-describedby="straat" value="<?php if (isset($arr->am_straat)) echo $arr->am_straat; ?>">
                            </div>
                            <div>
                                <label for="huisnummer" class="col-form-label p-0 m-0">Huisnummer</label>
                                <input id="huisnummer" type="text" name="huisnummer" class="form-control m-0" aria-label="huisnummer" aria-describedby="huisnummer" value="<?php if (isset($arr->am_huisnummer)) echo $arr->am_huisnummer; ?>">
                            </div>
                            <div>
                                <label for="postcode" class="col-form-label p-0 m-0">Postcode</label>
                                <input id="postcode" type="text" name="postcode" class="form-control m-0" aria-label="postcode" aria-describedby="postcode" value="<?php if (isset($arr->am_postcode)) echo $arr->am_postcode; ?>">
                            </div>
                            <div>
                                <label for="woonplaats" class="col-form-label p-0 m-0">Woonplaats</label>
                                <input id="woonplaats" type="text" name="woonplaats" class="form-control m-0" aria-label="woonplaats" aria-describedby="woonplaats" value="<?php if (isset($arr->am_woonplaats)) echo $arr->am_woonplaats; ?>">
                            </div>
                            <div>
                                <label for="emailadres" class="col-form-label p-0 m-0">Emailadres*</label><span>(Verplicht)</span>
                                <input id="emailadres" type="email" name="emailadres" class="form-control m-0" aria-label="emailadres" aria-describedby="emailadres" value="<?php if (isset($arr->am_emailadres)) echo $arr->am_emailadres; ?>" required>
                            </div>
                            <div>
                                <label for="telefoonnr" class="col-form-label p-0 m-0">Telefoonnummer*</label><span>(Verplicht)</span>
                                <input id="telefoonnr" type="tel" name="telefoonnr" class="form-control m-0" aria-label="telefoonnr" aria-describedby="telefoonnr" value="<?php if (isset($arr->am_telefoonnr)) echo $arr->am_telefoonnr; ?>" required>
                            </div>
                            <span style="font-size: .65em; color: #777;">Velden met een * zijn verplicht</span>
                        </legend>
                        </fieldset>
                        <fieldset>
                        <legend class="border pt-1 pb-4 px-3 mt-3">Wat zou je graag willen doen?*
                            <div>
                                <input type="checkbox" id="optie-mtj" name="optie1" value="mtj" <?php if (isset($arr->optie_mtj) && $arr->optie_mtj == 'mtj') echo 'checked';?>>
                                <label for="optie-mtj">Individueel traject met een JobMaatje</label><br />
                                <input type="checkbox" id="optie-jg1" name="optie2" value="jg1" <?php if (isset($arr->optie_jg1) && $arr->optie_jg1 == 'jg1') echo 'checked';?>>
                                <label for="optie-jg1">Deelnemen aan een JobGroep</label><br />
                                <input type="checkbox" id="optie-jg2" name="optie3" value="jg2" <?php if (isset($arr->optie_jg2) && $arr->optie_jg2 == 'jg2') echo 'checked';?>>
                                <label for="optie-jg2">Deelnemen aan "Stap in vrijwilligerswerk"</label><br />
                                <input type="checkbox" id="optie-jg3" name="optie4" value="jg3" <?php if (isset($arr->optie_jg3) && $arr->optie_jg3 == 'jg3') echo 'checked';?>>
                                <label for="optie-jg3">Helpen als vrijwilliger</label><br />
                                <input type="checkbox" id="optie-anders" name="optie7" value="and" <?php if (isset($arr->optie_and) && $arr->optie_and == 'and') echo 'checked';?>>
                                <label for="optie-anders">Iets anders</label><br />
                            </div>
                        </legend>
                        </fieldset>
                        <fieldset>
                        <legend class="border pt-1 pb-4 px-3 mt-3">Vertel iets over jezelf
                            <div>
                                <label for="situatie" class="col-form-label p-0 m-0">Wat is je situatie?</label>
                                <textarea name="situatie" id="situatie" class="form-control" aria-describedby="situatieHelpInline" rows="5"><?php if (isset($arr->am_situatie)) echo $arr->am_situatie; ?></textarea>
                            </div>
                            <div>
                                <label for="reden" class="col-form-label p-0 m-0">Hoe ben je bij ons terecht gekomen?</label>
                                <textarea  name="reden" id="reden" class="form-control" aria-describedby="bronHelpInline" rows="5"><?php if (isset($arr->am_reden)) echo $arr->am_reden; ?></textarea>
                            </div>
                            <div>
                                <label for="opmerkingen" class="col-form-label p-0 m-0">Vragen en opmerkingen</label>
                                <textarea name="opmerkingen" id="opmerkingen" class="form-control" aria-describedby="opmerkingenHelpInline" rows="5"><?php if (isset($arr->am_opmerkingen)) echo $arr->am_opmerkingen; ?></textarea>
                            </div>
                        </legend>
                        </fieldset>
                   </div>
                    <div class="col-xl"></div>
                </div>
                <div class="contact row">
                    <div class="col-xl"></div>
                    <div class="col-xl-6">
                        <legend class="border pt-1 pb-4 px-3 my-3">Privacyverklaring<span>(Verplicht)</span>
                        <div style="background-color: #e0fdfa; padding: 3px 10px; font-size: .7em;">
                            <input type="checkbox" id="optie_prv" name="privacy" value="privacy">
                            <p>Ik geef JobHulp Culemborg toestemming voor de verwerking van mijn persoonsgegevens.</p>
                        </div>
                        <p style="font-size: .7em; padding-top: 5px;">Jouw persoonsgegevens worden uitsluitend binnen JobHulp Culemborg verwerkt. Je leest in <a href="privacy.php">onze privacyverklaring</a> hoe wij zorgvuldig omgaan met je persoonsgegevens.
                        </p>
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