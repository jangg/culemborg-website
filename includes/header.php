<!-- ======= Top Bar ======= -->
  <section id="topbar" class="d-flex align-items-center">
	<div class="container d-flex justify-content-center justify-content-md-between">
	  <div class="contact-info d-flex align-items-center">

		<i class="bi bi-envelope d-flex align-items-center"><a href="mailto:<?php echo LOC_INFO_EMAIL; ?>"><?php echo LOC_INFO_EMAIL; ?></a></i>
		<i class="bi bi-phone d-flex align-items-center ms-4"><?php echo LOC_TELEFOON1; ?></span></i>
	  </div>
	  <div class="social-links d-none d-md-flex align-items-center">
		<a href="https://www.facebook.com/jobhulp/" class="facebook"><i class="bi bi-facebook"></i></a>
		<a href="https://www.linkedin.com/company/110410941" class="linkedin"><i class="bi bi-linkedin"></i></a>
	  </div>
	</div>
  </section>
  <!-- ==== end Top Bar ======-->


  <!-- ======= Header ======= -->
  <header id="header" class="d-flex align-items-center">
	<div class="container d-flex align-items-center justify-content-between">

	  <!-- Uncomment below if you prefer to use an image logo -->
	  <a href="/index.php" class="logo"><img src="logos/<?= LOC_LOGO ?>" alt=""></a>

	  <nav id="navbar" class="navbar">
		<ul>
			<li><a id="a_home" class="nav-link scrollto" href="/index.php">Home</a></li>
			<li><a id="a_agenda" class="nav-link scrollto" href="/agenda.php">Agenda</a></li>
			
			<li class="dropdown"><a id="a_hulp" class="navlink scrollto" href="#"><span>Helpen</span> <i class="bi bi-caret-down-square"></i></a>
			<ul>
				<li><a href="/werkzoekende/">Voor werkzoekenden</a></li>
				<li><a href="/vrijwilliger/">Voor vrijwilligers</a></li>
				<li><a href="/sponsor/">Voor sponsoren</a></li>
				<li><a href="/werkgever/">Voor werkgevers</a></li>
				<li style="background-color: #b19ec4;"><a href="/aanmelden.php">Aanmelden</a></li>
			</ul>
			</li>
			<li><a id="a_sucstor" class="nav-link scrollto" href="/sucstor.php">Succesverhalen</a></li>
			<li><a id="a_nieuws" class="nav-link scrollto" href="/nieuws.php">Nieuws</a></li>
			<li><a class="nav-link scrollto" href="/index.php#contact">Contact</a></li>
			<li class="dropdown"><a id="a_over" class="navlink scrollto" href="#"><span>Over <?php echo LOC_NAME; ?></span> <i class="bi bi-caret-down-square"></i></a>
			<ul>
				<li><a href="/missie.php">Missie</a></li>
				<li><a href="/visie.php">Visie</a></li>
				<li><a href="/organisatie.php">Organisatie</a></li>
				<li><a href="/stichting.php">Stichting</a></li>
				<li><a href="<?= LOC_WEBSITE_INTRA ?>">Intranet</a></li>
			</ul>
		</ul>
		<i class="bi bi-list mobile-nav-toggle"></i>		
	  </nav><!-- .navbar -->
	</div>
  </header>
  <!-- End Header -->
