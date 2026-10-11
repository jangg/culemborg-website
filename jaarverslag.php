<!DOCTYPE HTML>
<html lang="nl-NL">
	<head>
		<?php include_once ('includes/head.php'); ?>
  		<link href="/assets/css/style_page.css" rel="stylesheet">
  		
  		<!-- cookiealert styles -->
  		<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/Wruczek/Bootstrap-Cookie-Alert@gh-pages/cookiealert.css">
  		<script>
	 		$(document).ready(function() {
				$('#a_over').addClass('active');
	 		});
  		</script>
		<link href="css/jumbotron.css" rel="stylesheet">
		<link href="css/style.css" rel="stylesheet">
<!-- 		<script type="text/javascript" src="../../extras/jquery.min.1.7.js"></script> -->
		<script type="text/javascript" src="js/turnjs4/extras/modernizr.2.5.3.min.js"></script>
	</head>
 
<body style="background-color: #dddddd; font-size: 16px;">	
	<?php include_once ('includes/header.php'); ?>
	
	<main role="main">
		<!-- ======= Hero Section ======= -->
		 <section id="pagehero" class="d-flex align-items-center">
			<div id="overlay"></div>
			<div class="container" data-aos="zoom-out" data-aos-delay="100" style="z-index: 3;">
			  <h1 class="col-lg-12 d-none d-lg-block text-center" style="font-size: 4em;">Ons <span>Jaarverslag over 2021</span></h1>
			  <h1 class="col-lg-12 d-lg-none" style="font-size: 2em;">Ons <span>Jaarverslag over 2021</span></h1>
			</div>
		 </section><!-- End Hero -->

		<!-- Main jumbotron for a primary marketing message or call to action -->
		<div class="container">
			<div class="row">				
				<div class="col-12">
					<div id="flipbook" class="mt-5">
						<div class="flipbook">
							<div> <img src="img/jaarverslag2021/1.jpg" class='img-fluid'></div>
							<div> <img src="img/jaarverslag2021/2.jpg" class='img-fluid'></div>
							<div> <img src="img/jaarverslag2021/3.jpg" class='img-fluid'></div>
							<div> <img src="img/jaarverslag2021/4.jpg" class='img-fluid'></div>
							<div> <img src="img/jaarverslag2021/5.jpg" class='img-fluid'></div>
							<div> <img src="img/jaarverslag2021/6.jpg" class='img-fluid'></div>
							<div> <img src="img/jaarverslag2021/7.jpg" class='img-fluid'></div>
							<div> <img src="img/jaarverslag2021/8.jpg" class='img-fluid'></div>
							<div> <img src="img/jaarverslag2021/9.jpg" class='img-fluid'></div>
							<div> <img src="img/jaarverslag2021/10.jpg" class='img-fluid'></div>
							<div> <img src="img/jaarverslag2021/11.jpg" class='img-fluid'></div>
							<div> <img src="img/jaarverslag2021/12.jpg" class='img-fluid'></div>
							<div> <img src="img/jaarverslag2021/13.jpg" class='img-fluid'></div>
							<div> <img src="img/jaarverslag2021/14.jpg" class='img-fluid'></div>
							<div> <img src="img/jaarverslag2021/15.jpg" class='img-fluid'></div>
							<div> <img src="img/jaarverslag2021/16.jpg" class='img-fluid'></div>
							
						</div>
					</div>
				</div>
				<div class="col-12">
					<p class="mt-4" style="color: black;">Klik op de hoeken om de bladzijden om te slaan.</p>
				</div>
			</div>
			<!-- <p><a class="btn btn-primary btn-lg" href="#" role="button">Learn more &raquo;</a></p> -->
		</div>								
	</main>
	<?php include('includes/footer.php'); ?>
	<script type="text/javascript">
function loadApp() {

	// Create the flipbook

	
	$('.flipbook').turn({
			// Width
	
			width:922,
			
			// Height
	
			height:650,
	
			// Elevation
	
			elevation: 50,
			
			// Enable gradients
	
			gradients: true,
			
			// Auto center this flipbook
	
			autoCenter: true
	
	});

}

// Load the HTML4 version if there's not CSS transform

yepnope({
	test : Modernizr.csstransforms,
	yep: ['js/turnjs4/lib/turn.js'],
	nope: ['js/turnjs4/lib/turn.html4.min.js'],
	both: ['css/basic.css'],
	complete: loadApp
});
	</script>
</body>
</html>