<!-- ======= Footer ======= -->
<footer id="footer">
	<div class="footer-top">
		<div class="container">
			<div class="row">
				<div class="col-lg-3 col-md-6 footer-contact">
					<h3><?= LOC_NAME ?></h3>
					<h5>Postadres:</h5>
					<p>Alida de Jongstraat 56 <br>
						4105 ED Culemborg</p><br>
					<h5>Bezoekadres:</h5>
					<p>De Salaamander<br />
						Heimanslaan 2<br />
						Dinsdag 9:30-11:30 uur</p><br>
						<p>BonVie<br />
						Admiraal Vlinderlaan 2<br />
						Donderdag 9:30-11:30 uur</p><br>

					<h5>Contactinformatie</h5>
					<p>Vul het <a href="">formulier</a> in</p>
				</div>
				
				<div class="col-lg-3 col-md-6 footer-links">
					<h4>De kortste weg naar ...</h4>
					<ul>
						<li><i class="bi bi-caret-right-square"></i> <a href="/index.php">Home</a></li>
						<li><i class="bi bi-caret-right-square"></i> <a href="/aanmelden.php#aanmelden">Aanmelden</a></li>
						<li><i class="bi bi-caret-right-square"></i> <a href="/stichting.php">Over ons</a></li>
						<li><i class="bi bi-caret-right-square"></i> <a href="/organisatie.php#sponsors">Sponsoren</a></li>
						<li><i class="bi bi-caret-right-square"></i> <a href="/index.php#faq">Veel gestelde vragen</a></li>
						<li><i class="bi bi-caret-right-square"></i> <a href="/privacy.php">De privacyregels</a></li>
						<li><i class="bi bi-caret-right-square"></i> <a href="/stichting.php#downloads">Documenten die gedownload kunnen worden</a></li>
					</ul>
				</div>

				<div class="col-lg-3 col-md-6 footer-links">
					<h4>Wat we doen</h4>
					<ul>
						<li><i class="bi bi-caret-right-square"></i> <a href="/index.php#maatje">JobMaatjes begeleiding</a></li>
						<li><i class="bi bi-caret-right-square"></i> <a href="#">JobGroep training</a></li>
						<li><i class="bi bi-caret-right-square"></i> <a href="#">Stap in vrijwilligerswerk</a></li>
					</ul>
				</div>

				<div class="col-lg-3 col-md-6 footer-links">
					<h4>Onze Social Media</h4>
					<p>Volg ons op de belangrijkste social media</p>
					<div class="social-links my-3">
						<a href="https://www.facebook.com/jobhulp" class="facebook"><i class="bx bxl-facebook" style="background-color: white;"></i></a>
						<a href="https://www.linkedin.com/company/110410941" class="linkedin"><i class="bx bxl-linkedin" style="background-color: white;"></i></i></a>
					</div>
					<br /><br />
					<div class="row">
						<a href="stichting.php#stichtinginfo">
							<div class="col-8 p-2 bg-light rounded">
								<p style="font-size: .9em;color: black;"><?= LOC_NAME ?> is een</p>
								<img src="assets/img/ANBI_zk_FC_blauw.jpg" class="img-fluid bg-light" width="100%">
							</div>
						</a>
						<div class="col-4"></div>
					</div>
				</div>

			</div>
		</div>
	</div>

	<div class="container py-4">
		<div class="copyright">
			&copy; <?php echo date("Y");?>&nbsp&nbsp<span><?= LOC_NAME ?></span>.
		</div>
	</div>
</footer><!-- End Footer -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<div id="cookie-banner" class="cookie-banner">
  <p>
	Deze website gebruikt alleen cookies voor analyse en een optimale werking.
	Door op "Akkoord" te klikken geef je toestemming voor het plaatsen van cookies.
	<a href="/privacy.php" target="_blank">Lees meer</a>
  </p>
  <button id="cookie-accept">Akkoord</button>
</div>
<script>
document.addEventListener("DOMContentLoaded", function() {

	function loadGA() {
		var gtagscript = document.createElement('script');
		gtagscript.src = "https://www.googletagmanager.com/gtag/js?id=G-PZCYRS9PGQ";
		gtagscript.async = true;
		document.head.appendChild(gtagscript);
		
		window.dataLayer = window.dataLayer || [];
		window.gtag = function(){ dataLayer.push(arguments); };
		window.gtag('js', new Date());
		window.gtag('config', 'G-PZCYRS9PGQ');
	}

  if (localStorage.getItem("cookieConsent") === "true") {
    loadGA();
  } else {
    document.getElementById("cookie-banner").style.display = "block";
  }

  document.getElementById("cookie-accept").onclick = function() {
    localStorage.setItem("cookieConsent", "true");
    document.getElementById("cookie-banner").style.display = "none";
    loadGA();
  };

});
</script>
<!-- Vendor JS Files -->
<script src="/assets/vendor/aos/aos.js"></script>
<script src="/assets/vendor/glightbox/js/glightbox.min.js"></script>
<script src="/assets/vendor/isotope-layout/isotope.pkgd.min.js"></script>
<!-- <script src="/assets/vendor/php-email-form/validate.js"></script> -->
<script src="/assets/vendor/purecounter/purecounter.js"></script>
<script src="/assets/vendor/swiper/swiper-bundle.min.js"></script>
<script src="/assets/vendor/waypoints/noframework.waypoints.js"></script>

<!-- Template Main JS File -->
<script src="/assets/js/main.js"></script>
<!-- Include cookiealert script -->
<script src="https://cdn.jsdelivr.net/gh/Wruczek/Bootstrap-Cookie-Alert@gh-pages/cookiealert.js"></script>
