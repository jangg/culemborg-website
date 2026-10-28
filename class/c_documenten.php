<?php
/* ------------------------------------------------------------------
 * Documentenfunctie (okt 2026)
 *
 * Drie kleine classes in één bestand, omdat ze altijd samen worden gebruikt:
 *   Instelling  instellingen die de beheerder op het intranet wijzigt
 *               (tabel instelling: toegestane soorten, max. grootte, maximum per persoon)
 *   DocMap      mappen (tabel docmap), met submappen via parent_id
 *   Document    documenten (tabel document)
 *
 * Opzet: de GEGEVENS staan in de database, de BESTANDEN in DOC_DIR
 * (een map buiten public_html, ingesteld in includes/configDB.php).
 * Een bestand krijgt in DOC_DIR een willekeurige naam van 32 tekens
 * (kolom opslagnaam); de echte naam staat alleen in de database.
 * Zo kan niemand een bestand via een webadres opvragen of raden, en
 * zitten twee bestanden met dezelfde naam elkaar nooit in de weg.
 *
 * Twee "ruimtes":
 *   'eigen'   persoonlijke documenten; eigenaar_id = user.id van de eigenaar
 *   'gedeeld' de gemeenschappelijke map; eigenaar_id = NULL
 *
 * WIE WAT MAG, staat in Document::magZien() en Document::magVerwijderen()
 * (en de rechten documenten_eigen / documenten_gedeeld in includes/rechten.php).
 * De pagina's vragen het altijd hier na, zodat de regels op één plek staan.
 *
 * Alle methodes zijn "static": je roept ze aan als Document::zoek(12),
 * zonder eerst een object te maken. Een document of map is gewoon een
 * array met de kolommen uit de database, bijvoorbeeld $doc['naam'].
 * ------------------------------------------------------------------ */

class Instelling
{
	/* Standaardwaarden, voor als de tabel (nog) leeg is. */
	const STANDAARD = [
		'doc_extensies' => 'pdf,doc,docx,xls,xlsx,ppt,pptx,odt,ods,txt,rtf,jpg,jpeg,png',
		'doc_max_mb'    => '10',
		'doc_quotum_mb' => '200',
	];

	/* Deze soorten komen er NOOIT in, ook niet als de beheerder ze invult:
	   bestanden die een webserver of computer zou kunnen uitvoeren. */
	const VERBODEN = ['php','php3','php4','php5','php7','php8','phtml','phar','pht','phps',
		'cgi','pl','py','sh','bash','exe','com','bat','cmd','msi','js','mjs','vbs','jar',
		'htm','html','xhtml','shtml','svg','htaccess','htpasswd','ini','scr','app','dmg'];

	public static function get (string $sleutel): string
	{
		global $connection;
		// static: binnen één verzoek maar één keer uit de database lezen
		static $alles = null;
		if ($alles === null) {
			$alles = self::STANDAARD;
			try {
				foreach ($connection->query("SELECT sleutel, waarde FROM instelling") as $rij) {
					$alles[$rij['sleutel']] = $rij['waarde'];
				}
			} catch (PDOException $e) {
				error_log('Instelling::get: ' . $e->getMessage());
			}
		}
		return (string)($alles[$sleutel] ?? '');
	}

	public static function set (string $sleutel, string $waarde): void
	{
		global $connection;
		// REPLACE INTO: bestaat de sleutel al, dan wordt de regel vervangen
		$stmt = $connection->prepare("REPLACE INTO instelling (sleutel, waarde) VALUES (:s, :w)");
		$stmt->execute([':s' => $sleutel, ':w' => $waarde]);
	}

	/* De toegestane extensies als array, bijvoorbeeld ['pdf', 'docx']. */
	public static function extensies (): array
	{
		$lijst = array_map('trim', explode(',', strtolower(self::get('doc_extensies'))));
		return array_values(array_filter($lijst, fn($e) => $e !== '' && !in_array($e, self::VERBODEN, true)));
	}

	/* Maximale grootte per bestand in bytes: de instelling van de beheerder,
	   maar nooit meer dan de server (PHP) toelaat. */
	public static function maxBytes (): int
	{
		$beheerder = (int)(floatval(self::get('doc_max_mb')) * 1024 * 1024);
		return min($beheerder, self::phpMaxBytes());
	}

	public static function quotumBytes (): int
	{
		return (int)(floatval(self::get('doc_quotum_mb')) * 1024 * 1024);
	}

	/* Wat PHP op deze server maximaal accepteert (upload_max_filesize en post_max_size). */
	public static function phpMaxBytes (): int
	{
		return min(self::iniBytes(ini_get('upload_max_filesize')), self::iniBytes(ini_get('post_max_size')));
	}

	/* "8M" -> 8388608 */
	private static function iniBytes (string|false $waarde): int
	{
		$waarde = trim((string)$waarde);
		if ($waarde === '') return PHP_INT_MAX;
		$getal = (float)$waarde;
		switch (strtolower(substr($waarde, -1))) {
			case 'g': $getal *= 1024;
			case 'm': $getal *= 1024;	// geen break: g valt door naar m en k
			case 'k': $getal *= 1024;
		}
		return $getal > 0 ? (int)$getal : PHP_INT_MAX;
	}
}

class DocMap
{
	public static function zoek (int $id): ?array
	{
		global $connection;
		$stmt = $connection->prepare("SELECT * FROM docmap WHERE id = :id");
		$stmt->execute([':id' => $id]);
		return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
	}

	/* De submappen van een map ($parent = null: het bovenste niveau). */
	public static function lijst (string $ruimte, ?int $eigenaar, ?int $parent): array
	{
		global $connection;
		// "<=>" is in MySQL "is gelijk aan, ook als beide NULL zijn"
		$stmt = $connection->prepare("SELECT * FROM docmap WHERE ruimte = :r AND eigenaar_id <=> :e "
			. "AND parent_id <=> :p ORDER BY naam");
		$stmt->execute([':r' => $ruimte, ':e' => $eigenaar, ':p' => $parent]);
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	/* Het pad van de bovenste map tot en met deze map, voor de "kruimels"
	   bovenaan de pagina: Mijn documenten › Cursus › 2026 */
	public static function pad (?int $id): array
	{
		$pad = [];
		$teller = 0;
		while ($id !== null && $teller++ < 50) {	// 50: noodrem tegen een kring van mappen
			$map = self::zoek($id);
			if (!$map) break;
			array_unshift($pad, $map);
			$id = $map['parent_id'] === null ? null : (int)$map['parent_id'];
		}
		return $pad;
	}

	public static function maak (string $ruimte, ?int $eigenaar, ?int $parent, string $naam, int $door): int
	{
		global $connection;
		$stmt = $connection->prepare("INSERT INTO docmap (ruimte, eigenaar_id, parent_id, naam, gemaakt_door, gemaakt_op) "
			. "VALUES (:r, :e, :p, :n, :d, NOW())");
		$stmt->execute([':r' => $ruimte, ':e' => $eigenaar, ':p' => $parent, ':n' => $naam, ':d' => $door]);
		return (int)$connection->lastInsertId();
	}

	/* Een submap met precies deze naam onder $parent, of null als die er niet is. */
	public static function zoekKind (string $ruimte, ?int $eigenaar, ?int $parent, string $naam): ?int
	{
		global $connection;
		$stmt = $connection->prepare("SELECT id FROM docmap WHERE ruimte = :r AND eigenaar_id <=> :e "
			. "AND parent_id <=> :p AND naam = :n ORDER BY id LIMIT 1");
		$stmt->execute([':r' => $ruimte, ':e' => $eigenaar, ':p' => $parent, ':n' => $naam]);
		$id = $stmt->fetchColumn();
		return $id === false ? null : (int)$id;
	}

	/* Voor het uploaden van een hele map. $pad is de plek van het bestand binnen
	   die map, bijvoorbeeld "Cursus/2026/brief.pdf". Zorgt dat de mappen "Cursus"
	   en "Cursus/2026" bestaan onder $parent (een map die al bestaat wordt
	   hergebruikt, er komt geen tweede) en geeft het id van de map terug waar het
	   bestand in hoort. Zonder mappen in het pad: gewoon $parent.
	   $cache onthoudt binnen één verzoek welke mappen al zijn opgezocht. */
	public static function maakPad (string $ruimte, ?int $eigenaar, ?int $parent, string $pad, int $door, array &$cache): ?int
	{
		$delen = explode('/', str_replace('\\', '/', $pad));
		array_pop($delen);					// het laatste deel is de bestandsnaam
		$map = $parent;
		foreach (array_slice($delen, 0, 20) as $deel) {		// 20: noodrem tegen absurd diepe paden
			$naam = self::schoneNaam($deel);
			if ($naam === null || $naam === '.' || $naam === '..') continue;
			$sleutel = ($map ?? 0) . '/' . mb_strtolower($naam);
			if (!isset($cache[$sleutel])) {
				$cache[$sleutel] = self::zoekKind($ruimte, $eigenaar, $map, $naam)
					?? self::maak($ruimte, $eigenaar, $map, $naam, $door);
			}
			$map = $cache[$sleutel];
		}
		return $map;
	}

	public static function hernoem (int $id, string $naam): void
	{
		global $connection;
		$connection->prepare("UPDATE docmap SET naam = :n WHERE id = :id")->execute([':n' => $naam, ':id' => $id]);
	}

	public static function isLeeg (int $id): bool
	{
		global $connection;
		$stmt = $connection->prepare("SELECT (SELECT COUNT(*) FROM docmap WHERE parent_id = :a) "
			. "+ (SELECT COUNT(*) FROM document WHERE map_id = :b)");
		$stmt->execute([':a' => $id, ':b' => $id]);
		return (int)$stmt->fetchColumn() === 0;
	}

	public static function verwijder (int $id): void
	{
		global $connection;
		$connection->prepare("DELETE FROM docmap WHERE id = :id")->execute([':id' => $id]);
	}

	/* Een geldige mapnaam, of null. Geen / \ en geen onzichtbare tekens. */
	public static function schoneNaam (string $naam): ?string
	{
		$naam = trim(preg_replace('/[\x00-\x1F\x7F\/\\\\]+/u', ' ', $naam) ?? '');
		$naam = mb_substr(preg_replace('/\s+/u', ' ', $naam), 0, 100);
		return $naam === '' ? null : $naam;
	}
}

class Document
{
	public static function zoek (int $id): ?array
	{
		global $connection;
		$stmt = $connection->prepare("SELECT * FROM document WHERE id = :id");
		$stmt->execute([':id' => $id]);
		return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
	}

	/* De documenten in een map ($map = null: het bovenste niveau). */
	public static function lijst (string $ruimte, ?int $eigenaar, ?int $map): array
	{
		global $connection;
		$stmt = $connection->prepare("SELECT d.*, TRIM(CONCAT_WS(' ', p.voornaam, p.tussenvoegsels, p.achternaam)) AS door_naam "
			. "FROM document d LEFT JOIN user u ON u.id = d.gemaakt_door LEFT JOIN person p ON p.person_id = u.id_person "
			. "WHERE d.ruimte = :r AND d.eigenaar_id <=> :e AND d.map_id <=> :m ORDER BY d.naam");
		$stmt->execute([':r' => $ruimte, ':e' => $eigenaar, ':m' => $map]);
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	/* Zoeken op naam in een hele ruimte (alle mappen). */
	public static function zoekOpNaam (string $ruimte, ?int $eigenaar, string $tekst): array
	{
		global $connection;
		// % en _ hebben in LIKE een speciale betekenis; met een \ ervoor zoeken we ze letterlijk
		$tekst = '%' . addcslashes($tekst, '%_\\') . '%';
		$stmt = $connection->prepare("SELECT d.*, TRIM(CONCAT_WS(' ', p.voornaam, p.tussenvoegsels, p.achternaam)) AS door_naam "
			. "FROM document d LEFT JOIN user u ON u.id = d.gemaakt_door LEFT JOIN person p ON p.person_id = u.id_person "
			. "WHERE d.ruimte = :r AND d.eigenaar_id <=> :e AND d.naam LIKE :t ORDER BY d.naam LIMIT 200");
		$stmt->execute([':r' => $ruimte, ':e' => $eigenaar, ':t' => $tekst]);
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	/* Hoeveel bytes iemand in totaal heeft geüpload (eigen + gedeeld). */
	public static function gebruikt (int $userId): int
	{
		global $connection;
		$stmt = $connection->prepare("SELECT COALESCE(SUM(grootte), 0) FROM document WHERE gemaakt_door = :u");
		$stmt->execute([':u' => $userId]);
		return (int)$stmt->fetchColumn();
	}

	/* ----------------------------------------------------------
	 * Wie mag wat
	 * ---------------------------------------------------------- */

	/* Mag deze gebruiker in deze ruimte kijken (en dus ook uploaden en mappen maken)? */
	public static function magRuimte (string $ruimte, ?int $eigenaar, int $userId): bool
	{
		if (heeft_recht('beheerder')) return true;
		if ($ruimte === 'eigen')   return heeft_recht('documenten_eigen') && $eigenaar === $userId;
		if ($ruimte === 'gedeeld') return heeft_recht('documenten_gedeeld');
		return false;
	}

	/* Mag deze gebruiker dit document (of deze map) verwijderen of hernoemen?
	   In de eigen ruimte: de eigenaar. In de gedeelde map: alleen wie het heeft
	   gemaakt. De beheerder altijd. */
	public static function magWijzigen (array $item, int $userId): bool
	{
		if (heeft_recht('beheerder')) return true;
		if ($item['ruimte'] === 'eigen') {
			return heeft_recht('documenten_eigen') && (int)$item['eigenaar_id'] === $userId;
		}
		return heeft_recht('documenten_gedeeld') && (int)$item['gemaakt_door'] === $userId;
	}

	/* ----------------------------------------------------------
	 * Uploaden
	 * ---------------------------------------------------------- */

	/* Controleert en bewaart één geüpload bestand.
	   $bestand is één element uit $_FILES (zie normaliseerFiles()).
	   Geeft null terug als het gelukt is, anders een foutmelding voor de gebruiker. */
	public static function upload (array $bestand, string $ruimte, ?int $eigenaar, ?int $map, int $door): ?string
	{
		global $connection;
		$naam = self::schoneNaam((string)$bestand['name']);
		$toon = $naam ?? 'Het bestand';

		// 1. Is het uploaden zelf gelukt?
		switch ($bestand['error']) {
			case UPLOAD_ERR_OK: break;
			case UPLOAD_ERR_INI_SIZE:
			case UPLOAD_ERR_FORM_SIZE:
				return "$toon is te groot. Het maximum is " . self::mb(Instelling::maxBytes()) . '.';
			case UPLOAD_ERR_NO_FILE:
				return 'Er is geen bestand gekozen.';
			default:
				error_log('Document::upload: foutcode ' . $bestand['error']);
				return "$toon kon niet worden ontvangen. Probeer het nog eens.";
		}
		// is_uploaded_file: het bestand komt echt uit dit formulier (en niet bijv. /etc/passwd)
		if ($naam === null || !is_uploaded_file($bestand['tmp_name'])) {
			return 'Het bestand kon niet worden ontvangen. Probeer het nog eens.';
		}

		// 2. Soort bestand: de extensie moet op de lijst van de beheerder staan
		$ext = strtolower(pathinfo($naam, PATHINFO_EXTENSION));
		if ($ext === '' || !in_array($ext, Instelling::extensies(), true)) {
			return "$toon is niet toegestaan. Toegestaan zijn: " . implode(', ', Instelling::extensies()) . '.';
		}

		// 3. Grootte, per bestand en het totaal per persoon
		$grootte = (int)filesize($bestand['tmp_name']);
		if ($grootte <= 0) {
			return "$toon is leeg.";
		}
		if ($grootte > Instelling::maxBytes()) {
			return "$toon is te groot (" . self::mb($grootte) . '). Het maximum is ' . self::mb(Instelling::maxBytes()) . '.';
		}
		if (!heeft_recht('beheerder') && self::gebruikt($door) + $grootte > Instelling::quotumBytes()) {
			return "$toon past er niet meer bij: je hebt al " . self::mb(self::gebruikt($door)) . ' van de '
				. self::mb(Instelling::quotumBytes()) . ' in gebruik. Verwijder eerst iets wat je niet meer nodig hebt.';
		}

		// 4. De inhoud: finfo kijkt naar de eerste bytes van het bestand, niet naar de naam.
		//    Een webpagina of programma met de naam "brief.pdf" komt er zo niet in.
		$mime = (new finfo(FILEINFO_MIME_TYPE))->file($bestand['tmp_name']) ?: 'application/octet-stream';
		if (preg_match('~(php|html|javascript|x-sh|x-shellscript|executable|x-dosexec|x-msdownload|svg)~i', $mime)) {
			error_log("Document::upload geweigerd: $naam is $mime");
			return "$toon is niet toegestaan.";
		}

		// 5. Bewaren onder een willekeurige naam
		$map_ok = self::opslagmap();
		if ($map_ok === null) {
			return 'De documentenmap op de server is niet ingesteld. Meld dit aan de beheerder.';
		}
		$opslagnaam = bin2hex(random_bytes(16));		// 32 tekens, onmogelijk te raden
		$doel = $map_ok . $opslagnaam;
		if (!move_uploaded_file($bestand['tmp_name'], $doel)) {
			error_log("Document::upload: kan niet schrijven naar $doel");
			return "$toon kon niet worden opgeslagen. Meld dit aan de beheerder.";
		}
		@chmod($doel, 0640);

		// 6. Pas als het bestand er staat, de regel in de database. Mislukt dat,
		//    dan het bestand weer weg, zodat er geen "wees" achterblijft.
		try {
			$stmt = $connection->prepare("INSERT INTO document (ruimte, eigenaar_id, map_id, naam, opslagnaam, extensie, "
				. "mime, grootte, gemaakt_door, gemaakt_op) VALUES (:r, :e, :m, :n, :o, :x, :t, :g, :d, NOW())");
			$stmt->execute([':r' => $ruimte, ':e' => $eigenaar, ':m' => $map, ':n' => $naam, ':o' => $opslagnaam,
				':x' => $ext, ':t' => $mime, ':g' => $grootte, ':d' => $door]);
		} catch (PDOException $e) {
			@unlink($doel);
			error_log('Document::upload database: ' . $e->getMessage());
			return "$toon kon niet worden opgeslagen. Meld dit aan de beheerder.";
		}
		return null;
	}

	/* $_FILES['bestanden'] met meerdere bestanden is een vreemde opbouw:
	   ['name' => [a, b], 'size' => [..]]. Dit maakt er een gewone lijst van:
	   [['name' => a, 'size' => ..], ['name' => b, ...]]. */
	public static function normaliseerFiles (array $files): array
	{
		$lijst = [];
		if (!is_array($files['name'] ?? null)) {
			return isset($files['name']) ? [$files] : [];
		}
		foreach ($files['name'] as $i => $naam) {
			$lijst[] = ['name' => $naam, 'tmp_name' => $files['tmp_name'][$i],
				'error' => $files['error'][$i], 'size' => $files['size'][$i]];
		}
		return $lijst;
	}

	public static function hernoem (int $id, string $naam): void
	{
		global $connection;
		$connection->prepare("UPDATE document SET naam = :n WHERE id = :id")->execute([':n' => $naam, ':id' => $id]);
	}

	public static function verwijder (array $doc): void
	{
		global $connection;
		$connection->prepare("DELETE FROM document WHERE id = :id")->execute([':id' => $doc['id']]);
		$pad = self::pad($doc);
		if ($pad !== null && is_file($pad)) {
			@unlink($pad);
		}
	}

	/* ----------------------------------------------------------
	 * Hulpjes
	 * ---------------------------------------------------------- */

	/* DOC_DIR met een / aan het eind, of null als die niet bestaat of niet beschrijfbaar is. */
	public static function opslagmap (): ?string
	{
		if (!defined('DOC_DIR')) return null;
		$map = rtrim(DOC_DIR, '/\\') . '/';
		if (!is_dir($map)) {
			@mkdir($map, 0750, true);
		}
		return (is_dir($map) && is_writable($map)) ? $map : null;
	}

	/* Het volledige pad van het bestand op de server. */
	public static function pad (array $doc): ?string
	{
		$map = self::opslagmap();
		// opslagnaam is altijd 32 hex-tekens; controleer het toch, dan kan er nooit ../ in zitten
		if ($map === null || !preg_match('/^[0-9a-f]{32}$/', (string)$doc['opslagnaam'])) return null;
		return $map . $doc['opslagnaam'];
	}

	/* Een pad zoals de browser het meestuurt bij het uploaden van een map
	   ("Cursus/2026/brief.pdf"), opgeschoond: elk deel een gewone naam,
	   zonder lege delen, . of .. en hooguit 300 tekens. */
	public static function schoonPad (string $pad): string
	{
		$delen = [];
		foreach (explode('/', str_replace('\\', '/', $pad)) as $deel) {
			$deel = DocMap::schoneNaam($deel);
			if ($deel !== null && $deel !== '.' && $deel !== '..') $delen[] = $deel;
		}
		return mb_substr(implode('/', $delen), 0, 300);
	}

	/* Een veilige bestandsnaam: zonder map ervoor, zonder onzichtbare tekens of / \ : * ? " < > | */
	public static function schoneNaam (string $naam): ?string
	{
		$naam = basename(str_replace('\\', '/', $naam));		// "C:\Users\x\cv.pdf" -> "cv.pdf"
		$naam = preg_replace('/[\x00-\x1F\x7F\/\\\\:*?"<>|]+/u', '_', $naam) ?? '';
		$naam = trim(preg_replace('/\s+/u', ' ', $naam), " .");
		if ($naam === '') return null;
		if (mb_strlen($naam) > 200) {		// lange naam inkorten, extensie bewaren
			$ext  = pathinfo($naam, PATHINFO_EXTENSION);
			$naam = mb_substr($naam, 0, 190) . ($ext !== '' ? '.' . $ext : '');
		}
		return $naam;
	}

	/* 1536000 -> "1,5 MB" */
	public static function mb (int $bytes): string
	{
		if ($bytes >= 1024 * 1024 * 1024 * 1024) return 'onbeperkt';
		if ($bytes < 1024 * 1024) return max(1, (int)round($bytes / 1024)) . ' kB';
		return number_format($bytes / 1024 / 1024, 1, ',', '.') . ' MB';
	}

	/* Een Font Awesome-icoon dat bij de soort past. */
	public static function icoon (string $ext): string
	{
		$soorten = [
			'pdf' => 'fa-file-pdf text-danger',
			'doc' => 'fa-file-word text-primary', 'docx' => 'fa-file-word text-primary', 'odt' => 'fa-file-word text-primary', 'rtf' => 'fa-file-word text-primary',
			'xls' => 'fa-file-excel text-success', 'xlsx' => 'fa-file-excel text-success', 'ods' => 'fa-file-excel text-success', 'csv' => 'fa-file-excel text-success',
			'ppt' => 'fa-file-powerpoint text-warning', 'pptx' => 'fa-file-powerpoint text-warning', 'odp' => 'fa-file-powerpoint text-warning',
			'jpg' => 'fa-file-image text-info', 'jpeg' => 'fa-file-image text-info', 'png' => 'fa-file-image text-info', 'gif' => 'fa-file-image text-info',
			'txt' => 'fa-file-lines text-secondary', 'zip' => 'fa-file-zipper text-secondary',
		];
		return 'fa-regular ' . ($soorten[strtolower($ext)] ?? 'fa-file text-secondary');
	}

	/* ----------------------------------------------------------
	 * Voor de beheerder
	 * ---------------------------------------------------------- */

	/* Per gebruiker: aantal documenten en totale grootte (ook gebruikers die niet meer bestaan). */
	public static function overzichtPerGebruiker (): array
	{
		global $connection;
		$sql = "SELECT d.eigenaar_id AS id, u.username, TRIM(CONCAT_WS(' ', p.voornaam, p.tussenvoegsels, p.achternaam)) AS naam, "
			. "COUNT(*) AS aantal, SUM(d.grootte) AS grootte, (u.id IS NULL) AS weg "
			. "FROM document d LEFT JOIN user u ON u.id = d.eigenaar_id LEFT JOIN person p ON p.person_id = u.id_person "
			. "WHERE d.ruimte = 'eigen' GROUP BY d.eigenaar_id, u.username, naam, weg ORDER BY weg DESC, naam";
		return $connection->query($sql)->fetchAll(PDO::FETCH_ASSOC);
	}

	/* Alle eigen mappen en documenten van $vanId verhuizen naar de eigen ruimte van $naarId,
	   in een nieuwe map "Van <naam>". Bedoeld voor een maatje dat stopt. */
	public static function verhuisNaar (int $vanId, int $naarId, string $mapnaam): void
	{
		global $connection;
		$connection->beginTransaction();	// alles of niets
		try {
			$nieuw = DocMap::maak('eigen', $naarId, null, $mapnaam, $naarId);
			// mappen en documenten op het bovenste niveau komen in de nieuwe map
			$connection->prepare("UPDATE docmap SET parent_id = :n WHERE ruimte = 'eigen' AND eigenaar_id = :v AND parent_id IS NULL")
				->execute([':n' => $nieuw, ':v' => $vanId]);
			$connection->prepare("UPDATE document SET map_id = :n WHERE ruimte = 'eigen' AND eigenaar_id = :v AND map_id IS NULL")
				->execute([':n' => $nieuw, ':v' => $vanId]);
			// en alles krijgt de nieuwe eigenaar
			$connection->prepare("UPDATE docmap SET eigenaar_id = :n WHERE ruimte = 'eigen' AND eigenaar_id = :v")
				->execute([':n' => $naarId, ':v' => $vanId]);
			$connection->prepare("UPDATE document SET eigenaar_id = :n WHERE ruimte = 'eigen' AND eigenaar_id = :v")
				->execute([':n' => $naarId, ':v' => $vanId]);
			$connection->commit();
		} catch (PDOException $e) {
			$connection->rollBack();
			throw $e;
		}
	}

	/* Controle: bestanden zonder regel in de database, en regels zonder bestand. */
	public static function controleer (): array
	{
		global $connection;
		$map = self::opslagmap();
		if ($map === null) return ['zonder_regel' => [], 'zonder_bestand' => []];
		$inDb = $connection->query("SELECT opslagnaam FROM document")->fetchAll(PDO::FETCH_COLUMN);
		$opSchijf = array_values(array_filter(scandir($map), fn($f) => preg_match('/^[0-9a-f]{32}$/', $f)));
		return [
			'zonder_regel'   => array_values(array_diff($opSchijf, $inDb)),
			'zonder_bestand' => array_values(array_diff($inDb, $opSchijf)),
		];
	}
}
