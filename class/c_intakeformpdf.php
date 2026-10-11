<?php
// Eerst onze eigen TCPDF-instellingen, dan de bibliotheek zelf (zie includes/pdf_instellingen.php).
// __DIR__ = de map van dit bestand, zodat het pad altijd klopt, waar het ook vandaan geladen wordt.
require_once __DIR__ . '/../includes/pdf_instellingen.php';
require_once __DIR__ . '/../tcpdf/tcpdf.php';
class IntakeformPDF extends TCPDF
{
	//Page header
	public function Header() {
		// Logo. Het pad gaat via het bestandssysteem (__DIR__), niet via een URL.
		// Het type werd vast op 'JPG' gezet, maar het logo is in Culemborg een PNG en
		// in Zoetermeer een SVG; dan verscheen er geen logo. Nu: SVG via ImageSVG(),
		// andere soorten via Image() met type '' (TCPDF bepaalt het type zelf).
		// LOC_LOGO_PDF (optioneel, in configDB.php): een apart logo voor de PDF,
		// bijvoorbeeld een JPG als het gewone logo een SVG is die TCPDF niet goed toont.
		$logo = defined('LOC_LOGO_PDF') ? LOC_LOGO_PDF : LOC_LOGO;
		$image_file = __DIR__ . '/../logos/' . $logo;
		if (is_file($image_file)) {
			if (strtolower(pathinfo($image_file, PATHINFO_EXTENSION)) === 'svg') {
				$this->ImageSVG($image_file, 23, 20, 35, '', '', 'T');
			} else {
				$this->Image($image_file, 23, 20, 35, '', '', '', 'T', false, 300);
			}
		}
		// Set font
		$this->SetFont('helvetica', 'b', 20);
		// Title: rechts van het logo (23 mm + 35 mm logo + 5 mm ruimte), gecentreerd in de rest van de breedte
		$this->SetX(63);
		$this->Cell(0, 15, LOC_NAME . ' intakeformulier', 0, false, 'C', 0, '', 0, false, 'M', 'M');
	}

	// Page footer
	public function Footer() {
		// Position at 15 mm from bottom
		$this->SetY(-15);
		// Set font
		$this->SetFont('helvetica', 'I', 8);
		// Page number
		$this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
	}
}
?>
