	 <!-- ======= Nieuwsbrief aanmelden ======= -->
	 <section id="newsletter" class="<?php echo (Tools::sectOne() ? 'section-one' : 'section-two'); ?>">
	 	<div class="container" data-aos="fade-up">
			  <div class="section-title">
				  <h2>Op de hoogte blijven</h2>
				  <h3>Schrijf je in voor de nieuwsbrief</h3>
			  </div>
				 <div class="row justify-content-center">
					 <div class="col-lg-6">
						 <!-- <h4 class="text-dark">Schrijf je in voor onze nieuwsbrief</h4> -->
						 <p>En blijf op de hoogte van trainingsdata en activiteiten.<br />
							 Afmelden kan op elk moment.
						 </p>
						 <form method="POST" action="proces_nbemail.php" id="nbform">
							 <input type="email" id="email" name="email" placeholder="Je emailadres" required>
							 <input type="submit" name="submit" value="Abonneer" id="" style="background-color: #69468b;">
						 </form>
						 <span id="output_message"><?php if (isset($_GET['res']) && $_GET['res'] == 'suc') echo 'De nieuwsbrief is aangevraagd. Dank je wel.'; ?></span>
					 </div>
				 </div>
			 </div>
		 </div>
	 </section><!-- End Nieuwsbrief aanmelden Section -->