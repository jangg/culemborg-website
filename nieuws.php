<?php

require_once ('config.php');
include_once ('class/c_newsitem_coll.php');
/****
** haal de 10 meest recente nieuwsberichten op
*****/

$arr1 = array (array (0 => 'pubind_extern', 1 => 'j'));
$arr2 = array (array (0 => 'datetime_pub_extern', 1 => 'DESC'));

$newsitemColl = new Newsitem_coll($arr1, $arr2, 10);

$newsitemHTML = '';
foreach ($newsitemColl->newsitemColl as $newsitem) 
{
  $datum = new DateTimeImmutable($newsitem->datetime_pub_extern);
  
  
  $newsitemHTML .= '
  <div class="row" id="NI' . $newsitem->id . '">
    <div class="col-lg-5 d-flex align-items-stretch pb-0 mb-4">
      <div class="card image-box p-1" data-aos="fade-up" data-aos-delay="400" style="width: 100%;">';

      /* eerst foto altijd  */
      if ($newsitem->picfile1 != '')
        $newsitemHTML .= '<img src="https://' . LOC_DOMAIN_INTRA . '/img/' . $newsitem->picfile1 . '" class="m-0" style="height: 100%; object-fit: cover;"/>';
      if (str_word_count($newsitem->tekst_kort) > 100)  
      { /* tweede foto als de tekst langer is 100 woorden */
        if ($newsitem->picfile2 != '')
          $newsitemHTML .= '<br/><img src="https://' . LOC_DOMAIN_INTRA . '/img/' . $newsitem->picfile2 . '" class="m-0" style="height: 100%; object-fit: cover;"/>';
      }
      if (str_word_count($newsitem->tekst_kort) > 200)  
      { /* derde foto als de tekst langer is 200 woorden*/
        if ($newsitem->picfile3 != '')
          $newsitemHTML .= '<br/><img src="https://' . LOC_DOMAIN_INTRA . '/img/' . $newsitem->picfile3 . '" class="m-0" style="height: 100%; object-fit: cover;"/>';
      }
    $newsitemHTML .= '
            </div>
          </div>';
  /* einde opmaak foto's */

  $newsitemHTML .= '
    <div class="col-lg-7 d-flex mb-4">
      <div class="card text-box" data-aos="zoom-in" data-aos-delay="200" style="width: 100%;">
        <div class="card-header text-dark">
          <p>' . strftime('%A %e %B %Y', $datum->getTimestamp()) . '</p>
          <h2>' . $newsitem->titel  . '</h2>
          <h5>' . $newsitem->subtitel  . '</h5>
        </div>
        <div class="card-body">
        ' . Tools::getShortPost($newsitem->tekst_kort, 1000) .
        '</div>
        <div class="card-footer">';
  if ($newsitem->tekst_knop != '')
  {
    if ($newsitem->link_knop == '')
    {
      $newsitemHTML .= '<a href="nieuwsbericht.php?nid=' . $newsitem->id . '"><button class="btn btn-primary">' . $newsitem->tekst_knop . '</button></a>';
    } else
    {
      $newsitemHTML .= '<a href="' . $newsitem->link_knop . '"><button class="btn btn-primary">' . $newsitem->tekst_knop . '</button></a>';
    }
    
  }
  // $newsitemHTML .= '
  // <div class="fb-share-button" data-href="https://jobhulpmaatjezoetermeer.nl/nieuws.php" data-layout="" data-size=""><a target="_blank" href="https://www.facebook.com/sharer/sharer.php?u=https%3A%2F%2Fjobhulpmaatjezoetermeer.nl%2Fnieuws.php&amp;src=sdkpreparse" class="fb-xfbml-parse-ignore">Delen</a></div>
  // ';
        
  $newsitemHTML .= '     
        </div> 
      </div>
    </div>
  </div>';
}
$title=urlencode('Content title');
$url= urlencode('url of page');
$summary=urlencode("Content you can dynamically display like:");
$image=urlencode('image url path to display image');

// ' . Tools::getShortPost($newsitem->tekst_kort, 50) . 

?>
<!DOCTYPE html>
<html lang="nl">

<head>
  <?php include_once ('includes/head.php'); ?>
  <link href="/assets/css/style_nieuws.css" rel="stylesheet">
  
  <!-- cookiealert styles -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/Wruczek/Bootstrap-Cookie-Alert@gh-pages/cookiealert.css">
  <script src="http://connect.facebook.net/en_US/all.js" async=""></script>
  <script>
    $(document).ready(function(){
      $('#a_home').removeClass('active');
      $('#a_nieuws').addClass('active');
    });
  </script>
</head>
<body>
  <?php include_once ('includes/header.php'); ?>
  <!-- ======= Hero Section ======= -->
  <section id="hero" class="d-flex align-items-center" style="height: 30vh;">
    <div id="overlay"></div>
    <div class="container" data-aos="zoom-out" data-aos-delay="100" style="z-index: 3;">
      <h1 class="col-lg-12 d-none d-xl-block my-4" style="font-size: 6em;">Nieuws</span></h1>
      <h1 class="col-lg-12 d-none d-xl-block my-4" style="font-size: 3em;">Welkom bij <span><?= LOC_NAME ?></span></h1>
      <h1 class="col-lg-12 d-xl-none my-1" style="font-size: 3em;">Nieuws</span></h1>
      <h1 class="col-lg-12 d-xl-none my-1" style="font-size: 1.5em;">Welkom bij <span><?= LOC_NAME ?></span></h1>
      <!-- <h2 class="my-5">Als je op zoek bent naar werk en je kunt wat hulp gebruiken</h2> -->
    </div>
   </section><!-- End Hero -->

  <main id="main" data-aos="fade-up">
    <!-- <main id="main"> -->
  <!-- ======= Breadcrumbs ======= -->
    <section class="breadcrumbs">
      <div class="container">
  
        <div class="d-flex justify-content-between align-items-center">
          <h2 class="text-dark"></h2>
          <ol>
            <li><a href="index.php">Home</a></li>
            <li>Nieuws</li>
          </ol>
        </div>
  
      </div>
    </section><!-- End Breadcrumbs -->

    <!-- ======= Featured news Section ======= -->
    <section id="nieuwsberichten" style="padding-top: 40px;">
      <div class="container" data-aos="fade-up">
        <div class="section-title">
          <!-- <a href="https://www.facebook.com/sharer/sharer.php?u=jobhulpmaatjezoetermeer.nl/nieuws.php&" target="_blank">
            Share on Facebook
          </a> -->
          <?php if ($newsitemHTML != "") {
            echo $newsitemHTML;
          } else {
            echo "<h3>Helaas, er zijn geen nieuwsberichten op dit moment.</h3>";
          } ?>
        </div>
      </div>
    </section><!-- End Featured news Section -->

  </main><!-- End #main -->
  <?php include_once ('includes/footer.php'); ?>
  <div id="fb-root"></div>
  <!-- <script async defer crossorigin="anonymous" src="https://connect.facebook.net/nl_NL/sdk.js#xfbml=1&version=v19.0&appId=986027332142084" nonce="YzMk9q1E"></script> -->
</body>
</html>