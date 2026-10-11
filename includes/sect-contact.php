<!-- ======= Contact Section ======= -->
<?php
// Genereer een nieuwe som als die er nog niet is, of na een fout antwoord
if (!isset($_SESSION['captcha_a']) || (isset($_GET['resc']) && $_GET['resc'] == '3')) {
	$_SESSION['captcha_a'] = rand(1, 9);
	$_SESSION['captcha_b'] = rand(1, 9);
}

// Bepaal welke melding getoond moet worden
$resc = isset($_GET['resc']) ? (int)$_GET['resc'] : -1;

function contactMelding(int $code, int $verwacht, string $klasse, string $tekst): string 
{
	$toon = ($code === $verwacht) ? 'block' : 'none';
	return "<div class=\"{$klasse}\" style=\"display:{$toon}\">{$tekst}</div>";
}
?>
<section id="contact" class="contact <?php echo (Tools::sectOne() ? 'section-one' : 'section-two'); ?>">
	<div class="container" data-aos="fade-up">

		<div class="section-title">
			<h2>Contact</h2>
			<h3>Neem contact op</h3>
		</div>
			<div class="row mb-4 g-5">
				<div class="col-lg">
					<p><?= LOC_NAME ?> is één van de deelnemers aan de inloop van het Hulp & Adviespunt in Culemborg.
						Voor algemene zaken kun je contact opnemen met <?= LOC_NAME ?> via het onderstaande formulier.</p>
				</div>
				<div class="col-lg">
					<p>Wil je je aanmelden voor een JobGroep, voor een individueel traject of als vrijwilliger? <a href="aanmelden.php">Ga dan naar het aanmeldformulier</a>.</p>
				</div>
			</div>

			<div class="row" data-aos="fade-up" data-aos-delay="100">
				<div class="col-md-6 col-lg-3 d-flex align-items-stretch">
					<!-- <div class="col-lg-3 col-md-6"> -->
					<div class="info-box mb-4">
						<i class="bx bx-map"></i>
						<h3>Bezoekadres</h3>
						<p>De Salaamander</p>
						<p>Heimanslaan 2 </p>
						<p>Dinsdag van 9:30 to 11:30 uur</p>
						<p><?= LOC_LOCATION ?></p>
					</div>
				</div>

				<div class="col-md-6 col-lg-3 d-flex align-items-stretch">
					<div class="info-box  mb-4">
						<i class="bx bx-envelope"></i>
						<h3>Email</h3>
						<p><a href="mailto:info@jobhulpculemborg.nl">info@jobhulpculemborg.nl</a></p>
					</div>
				</div>

				<div class="col-md-6 col-lg-3 d-flex align-items-stretch">
					<div class="info-box  mb-4">
						<i class="bx bx-phone-call"></i>
						<h3>Telefoon</h3>
						<p><?= LOC_TELEFOON1 ?></p>
					</div>
				</div>

				<div class="col-md-6 col-lg-3 d-flex align-items-stretch">
					<div class="info-box  mb-4">
						<i class="bx bx-building-house"></i>
						<h3>Postadres</h3>
						<p>Alida de Jongstraat 56</p>
						<p>4105 ED Culemborg</p>
					</div>
				</div>


			</div>
			<!-- <div class="sharethis-inline-share-buttons"></div> -->
			<div class="row" data-aos="fade-up" data-aos-delay="100">

				<div class="col-xl-6 ">
					<!-- hieronder het iframe van google maps -->
					<iframe 
						src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2458.9059732095902!2d5.242154277157967!3d51.95390607191834!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x47c65e49962ddcaf%3A0x31dc783d5f121a95!2sHeimanslaan%202%2C%204102%20JD%20Culemborg!5e0!3m2!1snl!2snl!4v1770808926582!5m2!1snl!2snl" 
						width="480" 
						height="360" 
						style="border:0;" 
						title="Locatie JobHulp Culemborg op Google Maps"
						allowfullscreen 
						loading="lazy" 
						referrerpolicy="no-referrer-when-downgrade">
					</iframe>
				</div>

				<div class="col-xl-6">
					<form action="proces_contact.php" method="POST" class="php-email-form">
						<?php
						// CSRF-token aanmaken en meesturen
						if (empty($_SESSION['csrf_token'])) {
							$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
						}
						?>
						<input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

						<div class="row">
							<div class="col form-group">
								<input type="text" name="name" class="form-control" id="name" placeholder="Je naam" required>
							</div>
							<div class="col form-group">
								<input type="email" class="form-control" name="email" id="email" placeholder="Je emailadres" required>
							</div>
						</div>
						<div class="form-group">
							<input type="text" class="form-control" name="subject" id="subject" placeholder="Onderwerp" required>
						</div>
						<div class="form-group">
							<textarea class="form-control" name="message" rows="5" placeholder="Bericht" required></textarea>
						</div>
						<div class="my-3">
							<?php
							echo contactMelding($resc, 2, 'error-message',  'Het emailadres is niet ingevuld. Probeer opnieuw.');
							echo contactMelding($resc, 1, 'error-message',  'Je bericht is NIET verzonden. Probeer het later nog eens.');
							echo contactMelding($resc, 0, 'sent-message',   'Je bericht is verstuurd. Dankjewel!');
							echo contactMelding($resc, 3, 'error-message',  'Verkeerd antwoord bij de veiligheidsvraag. Probeer opnieuw.');
							?>
						</div>
						<div class="form-group">
							<label for="captcha">
								Veiligheidsvraag: hoeveel is
								<?php echo $_SESSION['captcha_a']; ?> + <?php echo $_SESSION['captcha_b']; ?>?
							</label>
							<input type="number" class="form-control" name="captcha" id="captcha"
								   placeholder="Jouw antwoord" required>
						</div>
						<div class="text-center"><button type="submit" style="background-color: #69468b;">Verstuur bericht</button></div>
					</form>
				</div>

			</div>

		</div>
</section><!-- End Contact Section -->