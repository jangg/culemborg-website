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
             $('#sendBut').click(function (){
                // Vanaf hier wordt het formulier gecontroleerd 
                var error = 0;
                var voornaam = $('#voornaam').val();
                var achternaam = $('#achternaam').val();
                var emailadres = $('#emailadres').val();
                if (voornaam == '') {
                    error = error + 1;
                }
                if (achternaam == '') {
                    error = error + 1;
                }
                if (emailadres == '') {
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
                        <li>Aanmelden workshop</li>
                    </ol>
                </div>

            </div>
        </section><!-- End Breadcrumbs -->

        <section id="aanmeld_jg" class="aanmeldform pt-0 section-one">
            

            <form id="aanmeldform" action="proces_ws_aanmelding.php" method="POST" novalidate>
            <div class="titelbalk">
                <div id="overlay"></div>
                <div class="container-fluid titelbalk py-5">
                    <div class="row justify-content-center">
                        <div class="col-xl-6" style="z-index: 3;">
                            <h4 class="text-center text-light display-2" style="text-shadow: 3px 3px 8px #1d423e;">Aanmelden workshop 10 oktober a.s.</h4>
                        </div>
                    </div>
                </div>
            </div>

            <div class="container pt-3 pb-2">
                <div id="aanmelden" class="row g-3 mb-4">
                    <div class="col-xl"></div>
                    <div class="col-xl-8">
                        <h2 class="text-dark my-3">Welkom!</h2>
                        <p>Leuk dat je komt. We wensen je veel plezier bij het volgen van de workshop.</p>
                    </div>
                    <div class="col-xl"></div>
                </div>
                <div class="contact row g-3">
                    <div class="col-xl"></div>
                    <!-- <div class="col-xl-6 d-xl-none"> -->
                        <div class="col-xl-6">
                        <legend class="border pt-1 pb-4 px-3">Persoonlijke gegevens
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
                                <label for="emailadres" class="col-form-label p-0 m-0">Emailadres*</label><span>(Verplicht)</span>
                                <input id="emailadres" type="email" id="emailadres1" name="emailadres" class="form-control m-0" aria-label="emailadres" aria-describedby="emailadres" value="<?php if (isset($arr->am_emailadres)) echo $arr->am_emailadres; ?>">
                            </div>
                            <span2 style="font-size: .65em; color: #777;">Velden met een * zijn verplicht</span2>
                        </legend>
                        
                        <legend class="border pt-1 pb-4 px-3 mt-3">Wat zou je graag willen doen?*
                            <div>
                                <input type="checkbox" id="optie-mtj" name="optie1" value="mtj" <?php if (isset($arr->optie_mtj) && $arr->optie_mtj == 'mtj') echo 'checked';?>>
                                <label for="optie-maatje">Workshop 'Gebruik Social Media' door Hilde van Leenen</label><br />
                            </div>
                        </legend>
                   </div>
                    <div class="col-xl"></div>
                </div>
                <div class="contact row">
                    <div class="col-xl"></div>
                    <div class="col-xl-6">
                        <legend class="border pt-1 pb-4 px-3 my-3">Privacyverklaring<span>(Verplicht)</span>
                        <div style="background-color: #e0fdfa; padding: 3px 10px; font-size: .7em;">
                            <input type="checkbox" id="optie_prv" name="privacy" value="privacy">
                            <p>Ik geef JobHulpMaatje Zoetermeer toestemming voor de verwerking van mijn persoonsgegevens.</p>
                        </div>
                        <p style="font-size: .7em; padding-top: 5px;">Jouw persoonsgegevens worden uitsluitend binnen JobHulpMaatje Zoetermeer verwerkt. Je leest in <a href="privacy.php">onze privacyverklaring</a> hoe wij zorgvuldig omgaan met je persoonsgegevens.
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