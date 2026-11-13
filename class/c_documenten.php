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
 * "Ruimtes" (keuze van de organisaties, nov 2026: optie A + C):
 *   'gedeeld'      A: de gemeenschappelijke map van de coördinatoren; eigenaar_id = NULL
 *   'werkzoekende' C: de documenten van één werkzoekende; eigenaar_id = werkzkd.id
 *   'eigen'        B: persoonlijke documenten (niet gekozen; wordt niet meer getoond,
 *                  staat nog in de database voor documenten uit de testperiode)
 *
 * WIE WAT MAG, staat in Document::magPagina(), Document::magRuimte() en
 * Document::magWijzigen() (en de rechten documenten_gedeeld (A),
 * documenten_eigen (B) en documenten_werkzoekenden (C) in includes/rechten.php;
 * per gebruiker in te stellen op het Systeem-scherm, nov 2026).
 * De pagina's vragen het altijd hier na, zodat de regels op één plek staan.
 *
 * Deze classes lezen en schrijven alleen hun eigen tabellen (document,
 * docmap, instelling). Gegevens van werkzoekenden, maatjes en gebruikers
 * komen via de classes Werkzoekende, Werkzoekende_coll, Maatje en User.
 *
 * Alle methodes zijn "static": je roept ze aan als Document::zoek(12),
 * zonder eerst een object te maken. Een document of map is gewoon een
 * array met de kolommen uit de database, bijvoorbeeld $doc['naam'].
 * ------------------------------------------------------------------ */
include_once __DIR__ . '/c_user.php';
include_once __DIR__ . '/c_maatje.php';
include_once __DIR__ . '/c_werkzoekende.php';
include_once __DIR__ . '/c_werkzoekende_coll.php';

class Instelling
{
	/* Standaardwaarden, voor als de tabel (nog) leeg is. */
	const STANDAARD = [
		'doc_extensies'  => 'pdf,doc,docx,xls,xlsx,ppt,pptx,odt,ods,txt,rtf,jpg,jpeg,png',
		'doc_max_mb'     => '10',
		'doc_quotum_mb'  => '100',		// per persoon: wat iemand bij werkzoekenden toevoegt
		'doc_gedeeld_mb' => '4096',		// de gedeelde map in totaal (4 GB)
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

	/* Maximum per persoon: alles wat iemand bij werkzoekenden heeft toegevoegd. */
	public static function quotumBytes (): int
	{
		return (int)(floatval(self::get('doc_quotum_mb')) * 1024 * 1024);
	}

	/* Maximum voor de gedeelde map, alle documenten samen. */
	public static function gedeeldBytes (): int
	{
		return (int)(floatval(self::get('doc_gedeeld_mb')) * 1024 * 1024);
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
		$stmt = $connection->prepare("SELECT * FROM document WHERE ruimte = :r AND eigenaar_id <=> :e AND map_id <=> :m ORDER BY naam");
		$stmt->execute([':r' => $ruimte, ':e' => $eigenaar, ':m' => $map]);
		return self::metNamen($stmt->fetchAll(PDO::FETCH_ASSOC));
	}

	/* Zoeken op naam in een hele ruimte (alle mappen). */
	public static function zoekOpNaam (string $ruimte, ?int $eigenaar, string $tekst): array
	{
		global $connection;
		// % en _ hebben in LIKE een speciale betekenis; met een \ ervoor zoeken we ze letterlijk
		$tekst = '%' . addcslashes($tekst, '%_\\') . '%';
		$stmt = $connection->prepare("SELECT * FROM document WHERE ruimte = :r AND eigenaar_id <=> :e AND naam LIKE :t ORDER BY naam LIMIT 200");
		$stmt->execute([':r' => $ruimte, ':e' => $eigenaar, ':t' => $tekst]);
		return self::metNamen($stmt->fetchAll(PDO::FETCH_ASSOC));
	}

	/* Zet bij elk document de naam van wie het heeft toegevoegd (door_naam).
	   Via de class User, met 'histid' zodat ook iemand die als verwijderd is
	   gemarkeerd nog een naam heeft. Per persoon maar één keer opzoeken. */
	private static function metNamen (array $docs): array
	{
		static $namen = [];
		foreach ($docs as &$d) {
			$u = (int)$d['gemaakt_door'];
			if (!isset($namen[$u])) {
				$user = new User('histid', $u);
				$namen[$u] = $user->id ? self::naamVan($user) : '';
			}
			$d['door_naam'] = $namen[$u];
		}
		unset($d);		// na foreach met & altijd de verwijzing opheffen
		return $docs;
	}

	/* Hoeveel bytes iemand bij werkzoekenden heeft toegevoegd (de gedeelde map telt
	   niet mee; die heeft een eigen maximum). Voor het maximum per persoon. */
	public static function gebruikt (int $userId): int
	{
		global $connection;
		$stmt = $connection->prepare("SELECT COALESCE(SUM(grootte), 0) FROM document WHERE gemaakt_door = :u AND ruimte <> 'gedeeld'");
		$stmt->execute([':u' => $userId]);
		return (int)$stmt->fetchColumn();
	}

	/* Hoeveel bytes de gedeelde map in totaal gebruikt. */
	public static function gebruiktGedeeld (): int
	{
		global $connection;
		return (int)$connection->query("SELECT COALESCE(SUM(grootte), 0) FROM document WHERE ruimte = 'gedeeld'")->fetchColumn();
	}

	/* Hoeveel bytes er per ruimte in gebruik zijn: ['gedeeld' => .., 'werkzoekende' => .., 'eigen' => ..].
	   Een ruimte zonder documenten staat erin met 0. Voor het overzicht in documenten_beheer.php. */
	public static function gebruiktPerRuimte (): array
	{
		return array_map(fn($r) => $r['bytes'], self::opslagPerRuimte());
	}

	/* Per ruimte het aantal documenten en de bytes (nov 2026):
	   ['gedeeld' => ['aantal' => 12, 'bytes' => 3456789], 'werkzoekende' => [...], 'eigen' => [...]].
	   Eén query: COUNT(*) telt de documenten, SUM(grootte) telt de bytes op, per ruimte (GROUP BY). */
	public static function opslagPerRuimte (): array
	{
		global $connection;
		$opslag = [];
		foreach (['gedeeld', 'werkzoekende', 'eigen'] as $ruimte) $opslag[$ruimte] = ['aantal' => 0, 'bytes' => 0];
		$rijen = $connection->query("SELECT ruimte, COUNT(*), COALESCE(SUM(grootte), 0) FROM document GROUP BY ruimte")
			->fetchAll(PDO::FETCH_NUM);
		foreach ($rijen as [$ruimte, $aantal, $bytes]) $opslag[$ruimte] = ['aantal' => (int)$aantal, 'bytes' => (int)$bytes];
		return $opslag;
	}

	/* Per werkzoekende het aantal documenten: [werkzkd.id => aantal]. */
	public static function aantallenPerWerkzoekende (): array
	{
		global $connection;
		$rijen = $connection->query("SELECT eigenaar_id, COUNT(*) FROM document WHERE ruimte = 'werkzoekende' GROUP BY eigenaar_id")
			->fetchAll(PDO::FETCH_NUM);
		$aantallen = [];
		foreach ($rijen as [$id, $n]) $aantallen[(int)$id] = (int)$n;
		return $aantallen;
	}

	/* ----------------------------------------------------------
	 * Werkzoekenden (optie C)
	 * ---------------------------------------------------------- */

	/* Het id van het maatje dat deze gebruiker is, of null als hij geen maatje is.
	   Gebruiker en maatje zijn dezelfde persoon (zelfde id_person). */
	public static function maatjeIdVan (int $userId): ?int
	{
		static $cache = [];
		if (!array_key_exists($userId, $cache)) {
			$user = new User('id', $userId);
			$mtj  = $user->id ? new Maatje('id_person', $user->id_person) : null;
			$cache[$userId] = ($mtj && $mtj->id) ? (int)$mtj->id : null;
		}
		return $cache[$userId];
	}

	/* De werkzoekende met dit werkzkd.id, of null als die niet (meer) bestaat.
	   Werkzoekende('id') vindt alleen werkzoekenden die niet als verwijderd
	   zijn gemarkeerd. */
	public static function werkzoekende (?int $wzkId): ?Werkzoekende
	{
		static $cache = [];
		if (!$wzkId) return null;
		if (!array_key_exists($wzkId, $cache)) {
			$wzk = new Werkzoekende('id', $wzkId);
			$cache[$wzkId] = $wzk->id ? $wzk : null;
		}
		return $cache[$wzkId];
	}

	/* De werkzoekenden die deze gebruiker in Documenten ziet, op achternaam:
	   met recht documenten_werkzoekenden (C) alle, met documenten_eigen (B)
	   alleen de werkzoekenden die hij als maatje begeleidt, anders geen. */
	public static function werkzoekendenVoor (int $userId): array
	{
		$volgorde = [['person.achternaam', 'ASC']];
		if (heeft_recht('documenten_werkzoekenden')) {
			$coll = new Werkzoekende_coll([], $volgorde);
		} else {
			if (!heeft_recht('documenten_eigen')) return [];
			$mtjId = self::maatjeIdVan($userId);
			if ($mtjId === null) return [];
			$coll = new Werkzoekende_coll([['werkzkd.id_maatje', $mtjId]], $volgorde);
		}
		return $coll->werkzoekendeColl;
	}

	/* Is deze werkzoekende actief? Dezelfde regel als in het WAS
	   (beheerwas/overz_werkzoekenden.php): status t/m 599 is actief,
	   600 en hoger (uitgeschreven, uitgestroomd e.d.) is niet-actief.
	   De status is een tekst van drie cijfers ('000'), daarom (int). */
	public static function isActief (Werkzoekende $w): bool
	{
		return (int)$w->status <= 599;
	}

	/* "Voornaam tussenvoegsels Achternaam" van een User, Maatje of Werkzoekende. */
	public static function naamVan (object $p): string
	{
		return trim(preg_replace('/\s+/', ' ', $p->voornaam . ' ' . $p->tussenvoegsels . ' ' . $p->achternaam));
	}

	/* ----------------------------------------------------------
	 * Wie mag wat
	 * ---------------------------------------------------------- */

	/* Mag deze gebruiker de pagina Documenten openen? Ja, als hij minstens
	   één van de drie documentenrechten heeft (A, B of C). */
	public static function magPagina (): bool
	{
		return heeft_recht('documenten_gedeeld') || heeft_recht('documenten_eigen')
			|| heeft_recht('documenten_werkzoekenden');
	}

	/* Mag deze gebruiker in deze ruimte kijken (en dus ook uploaden en mappen maken)?
	   gedeeld:      recht documenten_gedeeld (A)
	   werkzoekende: de werkzoekende moet bestaan; dan wie het recht
	                 documenten_werkzoekenden (C) heeft, en het maatje van die
	                 werkzoekende als hij het recht documenten_eigen (B) heeft
	   eigen:        niet meer in gebruik; alleen de beheerder
	   De beheerder mag overal (heeft_recht() geeft voor hem altijd true). */
	public static function magRuimte (string $ruimte, ?int $eigenaar, int $userId): bool
	{
		if ($ruimte === 'werkzoekende') {
			$wzk = self::werkzoekende($eigenaar);
			if ($wzk === null) return false;
			if (heeft_recht('documenten_werkzoekenden')) return true;
			if (!heeft_recht('documenten_eigen')) return false;
			$mtjId = self::maatjeIdVan($userId);
			return $mtjId !== null && (int)$wzk->id_maatje === $mtjId;
		}
		if (heeft_recht('beheerder')) return true;
		if ($ruimte === 'gedeeld') return heeft_recht('documenten_gedeeld');
		return false;
	}

	/* Mag deze gebruiker dit document (of deze map) verwijderen of hernoemen?
	   Alleen wie het heeft gemaakt (zolang hij er nog bij mag), en de beheerder altijd.
	   Een nieuw maatje kan dus de documenten van zijn voorganger lezen, maar niet weggooien. */
	public static function magWijzigen (array $item, int $userId): bool
	{
		if (heeft_recht('beheerder')) return true;
		$eigenaar = $item['eigenaar_id'] === null ? null : (int)$item['eigenaar_id'];
		return (int)$item['gemaakt_door'] === $userId && self::magRuimte($item['ruimte'], $eigenaar, $userId);
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
		if ($ruimte === 'gedeeld') {
			// De gedeelde map heeft één maximum voor alle documenten samen (ook voor de beheerder)
			$inGebruik = self::gebruiktGedeeld();
			if ($inGebruik + $grootte > Instelling::gedeeldBytes()) {
				return "$toon past er niet meer bij: de gedeelde map is vol (" . self::mb($inGebruik) . ' van de '
					. self::mb(Instelling::gedeeldBytes()) . '). Verwijder eerst iets of vraag de beheerder om meer ruimte.';
			}
		} elseif (!heeft_recht('beheerder') && self::gebruikt($door) + $grootte > Instelling::quotumBytes()) {
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
	 * Bestanden die het intranet zelf maakt (nov 2026)
	 * ---------------------------------------------------------- */

	/* De naam waaronder het intakeformulier van een werkzoekende wordt opgeslagen,
	   met de datum van vandaag: "Intakeformulier Jan de Vries 2026-11-13.pdf".
	   Jaar-maand-dag, zodat de formulieren in de lijst op datum staan.
	   Op één plek, zodat intake.php (de knop) en intake_pdf.php (het opslaan)
	   altijd precies dezelfde naam gebruiken. */
	public static function intakeNaam (Werkzoekende $w): string
	{
		return self::schoneNaam('Intakeformulier ' . self::naamVan($w) . ' ' . date('Y-m-d') . '.pdf') ?? 'Intakeformulier.pdf';
	}

	/* Het document met precies deze naam in deze map ($map = null: het bovenste
	   niveau), of null als het er niet is. De database vergelijkt zonder op
	   hoofdletters te letten, net als Windows en macOS dat doen. */
	public static function zoekNaamInMap (string $ruimte, ?int $eigenaar, ?int $map, string $naam): ?array
	{
		global $connection;
		$stmt = $connection->prepare("SELECT * FROM document WHERE ruimte = :r AND eigenaar_id <=> :e "
			. "AND map_id <=> :m AND naam = :n ORDER BY id LIMIT 1");
		$stmt->execute([':r' => $ruimte, ':e' => $eigenaar, ':m' => $map, ':n' => $naam]);
		return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
	}

	/* Bewaart een bestand dat het intranet zelf heeft gemaakt, zoals de
	   intake-PDF. Anders dan upload() komt de inhoud niet uit een formulier
	   maar uit een tekst ($inhoud), dus geen is_uploaded_file()/move_uploaded_file()
	   maar file_put_contents(). De lijst met toegestane soorten van de beheerder
	   geldt hier niet: wat het intranet zelf maakt, vertrouwen we.
	   $vervang = true: staat er al een document met dezelfde naam op dezelfde
	   plek, dan wordt dat vervangen (zelfde regel in de database, nieuwe inhoud).
	   $vervang = false: dan gebeurt er niets en komt er een melding terug.
	   Geeft null terug als het gelukt is, anders een foutmelding voor de gebruiker. */
	public static function bewaarInhoud (string $inhoud, string $naam, string $ruimte, ?int $eigenaar, ?int $map, int $door, bool $vervang = false): ?string
	{
		global $connection;
		$naam = self::schoneNaam($naam);
		if ($naam === null) return 'Het bestand heeft geen geldige naam.';
		$grootte = strlen($inhoud);			// strlen telt bytes, precies wat we willen
		if ($grootte === 0) return "$naam is leeg en is niet opgeslagen.";

		// 1. Bestaat de naam al?
		$bestaand = self::zoekNaamInMap($ruimte, $eigenaar, $map, $naam);
		if ($bestaand && !$vervang) {
			return "Er staat al een document \"$naam\". Het is niet vervangen.";
		}

		// 2. Het maximum per persoon. Vervangt iemand zijn eigen document,
		//    dan telt het oude document niet meer mee.
		if ($ruimte !== 'gedeeld' && !heeft_recht('beheerder')) {
			$al = self::gebruikt($door);
			if ($bestaand && (int)$bestaand['gemaakt_door'] === $door) $al -= (int)$bestaand['grootte'];
			if ($al + $grootte > Instelling::quotumBytes()) {
				return "$naam past er niet meer bij: je hebt al " . self::mb($al) . ' van de '
					. self::mb(Instelling::quotumBytes()) . ' in gebruik. Verwijder eerst iets wat je niet meer nodig hebt.';
			}
		}

		// 3. Het bestand wegschrijven onder een nieuwe willekeurige naam
		$opslag = self::opslagmap();
		if ($opslag === null) {
			return 'De documentenmap op de server is niet ingesteld. Meld dit aan de beheerder.';
		}
		$opslagnaam = bin2hex(random_bytes(16));
		$doel = $opslag . $opslagnaam;
		if (file_put_contents($doel, $inhoud) !== $grootte) {
			@unlink($doel);
			error_log("Document::bewaarInhoud: kan niet schrijven naar $doel");
			return "$naam kon niet worden opgeslagen. Meld dit aan de beheerder.";
		}
		@chmod($doel, 0640);
		$ext  = strtolower(pathinfo($naam, PATHINFO_EXTENSION));
		$mime = (new finfo(FILEINFO_MIME_TYPE))->buffer($inhoud) ?: 'application/octet-stream';

		// 4. De database. Bij vervangen: dezelfde regel (zelfde id) krijgt het nieuwe
		//    bestand, en pas DAARNA gaat het oude bestand weg. Mislukt de database,
		//    dan blijft het oude document dus gewoon heel.
		try {
			if ($bestaand) {
				$stmt = $connection->prepare("UPDATE document SET opslagnaam = :o, extensie = :x, mime = :t, "
					. "grootte = :g, gemaakt_door = :d, gemaakt_op = NOW() WHERE id = :id");
				$stmt->execute([':o' => $opslagnaam, ':x' => $ext, ':t' => $mime, ':g' => $grootte,
					':d' => $door, ':id' => $bestaand['id']]);
			} else {
				$stmt = $connection->prepare("INSERT INTO document (ruimte, eigenaar_id, map_id, naam, opslagnaam, extensie, "
					. "mime, grootte, gemaakt_door, gemaakt_op) VALUES (:r, :e, :m, :n, :o, :x, :t, :g, :d, NOW())");
				$stmt->execute([':r' => $ruimte, ':e' => $eigenaar, ':m' => $map, ':n' => $naam, ':o' => $opslagnaam,
					':x' => $ext, ':t' => $mime, ':g' => $grootte, ':d' => $door]);
			}
		} catch (PDOException $e) {
			@unlink($doel);
			error_log('Document::bewaarInhoud database: ' . $e->getMessage());
			return "$naam kon niet worden opgeslagen. Meld dit aan de beheerder.";
		}
		if ($bestaand) {
			$oud = self::pad($bestaand);
			if ($oud !== null && is_file($oud)) @unlink($oud);
		}
		return null;
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
		if ($bytes >= 1024 * 1024 * 1024) return number_format($bytes / 1024 / 1024 / 1024, 1, ',', '.') . ' GB';
		if ($bytes <= 0) return '0 kB';		// nov 2026: een lege map toonde "1 kB"
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

	/* Aantal documenten van één eigenaar in een ruimte (bijv. van één werkzoekende). */
	public static function aantal (string $ruimte, ?int $eigenaar): int
	{
		global $connection;
		$stmt = $connection->prepare("SELECT COUNT(*) FROM document WHERE ruimte = :r AND eigenaar_id <=> :e");
		$stmt->execute([':r' => $ruimte, ':e' => $eigenaar]);
		return (int)$stmt->fetchColumn();
	}

	/* Alle documenten (regels én bestanden) en mappen van één eigenaar in een ruimte
	   verwijderen. Voor als een werkzoekende definitief wordt verwijderd
	   (maintenance/cleanDB.php). Geeft het aantal verwijderde documenten terug. */
	public static function verwijderAllesVan (string $ruimte, int $eigenaar): int
	{
		global $connection;
		$stmt = $connection->prepare("SELECT * FROM document WHERE ruimte = :r AND eigenaar_id = :e");
		$stmt->execute([':r' => $ruimte, ':e' => $eigenaar]);
		$docs = $stmt->fetchAll(PDO::FETCH_ASSOC);
		foreach ($docs as $doc) {
			self::verwijder($doc);		// regel en bestand
		}
		$connection->prepare("DELETE FROM docmap WHERE ruimte = :r AND eigenaar_id = :e")
			->execute([':r' => $ruimte, ':e' => $eigenaar]);
		return count($docs);
	}

	/* Werkzoekenden die niet meer bestaan (ook niet als "verwijderd" gemarkeerd),
	   maar nog wel documenten hebben: [werkzkd.id => aantal]. Dat kan alleen als
	   iemand buiten cleanDB.php om is verwijderd, bijvoorbeeld in phpMyAdmin. */
	public static function wezen (): array
	{
		$wezen = [];
		$zoeker = new Werkzoekende();
		foreach (self::aantallenPerWerkzoekende() as $id => $n) {
			// readAllWerkzoekendeWithId: ook werkzoekenden met delind = 'j'
			if (!$zoeker->readAllWerkzoekendeWithId($id)) $wezen[$id] = $n;
		}
		return $wezen;
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
