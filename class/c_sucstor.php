<?php

/**
 * Class Sucstor
 *
 * Verbeteringen t.o.v. origineel:
 * - Static factory methods vervangen de __construct1/__construct2 anti-pattern
 * - Typed properties (PHP 7.4+)
 * - Dependency injection voor $connection in plaats van global
 * - INSERT INTO (was INSERT zonder INTO)
 * - PDO::PARAM_INT voor id-velden
 * - Auto-timestamps bij save en update
 * - __toString gecorrigeerd (dubbele datetime_created, ontbrekende picfile4)
 * - Lege __destruct verwijderd
 * - __get/__set beperkt tot read-only voor id, schrijftoegang via expliciete setters
 */
class Sucstor
{
	protected ?int    $id;
	protected string  $delind;
	protected string  $id_user_created;
	protected string  $datetime_created;
	protected string  $datetime_modified;
	protected string  $titel;
	protected string  $subtitel;
	protected string  $tekst;
	protected string  $tekst_kort;
	protected string  $tekst_samenvatting;
	protected string  $tekst_knop;
	protected string  $link_knop;
	protected string  $pubind_intern;
	protected string  $datetime_pub_intern;
	protected string  $pubind_extern;
	protected string  $datetime_pub_extern;
	protected string  $picfile1;
	protected string  $picfile2;
	protected string  $picfile3;
	protected string  $picfile4;

	// Lijst van velden die via __get gelezen mogen worden
	private const READABLE = [
		'id', 'delind', 'id_user_created', 'datetime_created', 'datetime_modified',
		'titel', 'subtitel', 'tekst', 'tekst_kort', 'tekst_samenvatting',
		'tekst_knop', 'link_knop', 'pubind_intern', 'datetime_pub_intern',
		'pubind_extern', 'datetime_pub_extern',
		'picfile1', 'picfile2', 'picfile3', 'picfile4',
	];

	// Lijst van velden die via __set geschreven mogen worden (id is read-only na opslaan)
	private const WRITABLE = [
		'delind', 'id_user_created', 'datetime_created', 'datetime_modified',
		'titel', 'subtitel', 'tekst', 'tekst_kort', 'tekst_samenvatting',
		'tekst_knop', 'link_knop', 'pubind_intern', 'datetime_pub_intern',
		'pubind_extern', 'datetime_pub_extern',
		'picfile1', 'picfile2', 'picfile3', 'picfile4',
	];

	// -------------------------------------------------------------------------
	// Constructor: altijd leeg object — gebruik static factory methods
	// -------------------------------------------------------------------------

	public function __construct()
	{
		$this->id                  = null;
		$this->delind              = 'n';
		$this->id_user_created     = '';
		$this->datetime_created    = '';
		$this->datetime_modified   = '';
		$this->titel               = '';
		$this->subtitel            = '';
		$this->tekst               = '';
		$this->tekst_kort          = '';
		$this->tekst_samenvatting  = '';
		$this->tekst_knop          = '';
		$this->link_knop           = '';
		$this->pubind_intern       = 'n';
		$this->datetime_pub_intern = '';
		$this->pubind_extern       = 'n';
		$this->datetime_pub_extern = '';
		$this->picfile1            = '';
		$this->picfile2            = '';
		$this->picfile3            = '';
		$this->picfile4            = '';
	}

	// -------------------------------------------------------------------------
	// Static factory methods
	// -------------------------------------------------------------------------

	/**
	 * Maak een Sucstor-object op basis van een database-id.
	 * Geeft null terug als het record niet bestaat.
	 */
	public static function fromId(int $id, PDO $connection): ?self
	{
		$obj = new self();
		$row = $obj->readFromDb($id, $connection);
		if (!$row) {
			return null;
		}
		$obj->fillFromRow($row);
		return $obj;
	}

	/**
	 * Maak een Sucstor-object op basis van een ruwe database-rij (array).
	 * Handig bij collectie-queries waarbij je al alle rijen hebt opgehaald.
	 */
	public static function fromRow(array $row): self
	{
		$obj = new self();
		$obj->fillFromRow($row);
		return $obj;
	}

	// -------------------------------------------------------------------------
	// Private helpers
	// -------------------------------------------------------------------------

	private function fillFromRow(array $row): void
	{
		$this->id                  = isset($row['id']) ? (int) $row['id'] : null;
		$this->delind              = $row['delind']              ?? 'n';
		$this->id_user_created     = $row['id_user_created']     ?? '';
		$this->datetime_created    = $row['datetime_created']    ?? '';
		$this->datetime_modified   = $row['datetime_modified']   ?? '';
		$this->titel               = $row['titel']               ?? '';
		$this->subtitel            = $row['subtitel']            ?? '';
		$this->tekst               = $row['tekst']               ?? '';
		$this->tekst_kort          = $row['tekst_kort']          ?? '';
		$this->tekst_samenvatting  = $row['tekst_samenvatting']  ?? '';
		$this->tekst_knop          = $row['tekst_knop']          ?? '';
		$this->link_knop           = $row['link_knop']           ?? '';
		$this->pubind_intern       = $row['pubind_intern']       ?? 'n';
		$this->datetime_pub_intern = $row['datetime_pub_intern'] ?? '';
		$this->pubind_extern       = $row['pubind_extern']       ?? 'n';
		$this->datetime_pub_extern = $row['datetime_pub_extern'] ?? '';
		$this->picfile1            = $row['picfile1']            ?? '';
		$this->picfile2            = $row['picfile2']            ?? '';
		$this->picfile3            = $row['picfile3']            ?? '';
		$this->picfile4            = $row['picfile4']            ?? '';
	}

	private function readFromDb(int $id, PDO $connection): ?array
	{
		try {
			$sql  = "SELECT * FROM sucstor WHERE id = :id AND delind = 'n' LIMIT 1";
			$stmt = $connection->prepare($sql);
			$stmt->bindValue(':id', $id, PDO::PARAM_INT);
			$stmt->execute();
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			return $row ?: null;
		} catch (PDOException $e) {
			error_log('Sucstor::readFromDb mislukt: ' . $e->getMessage());
			return null;
		}
	}

	// -------------------------------------------------------------------------
	// Database-operaties
	// -------------------------------------------------------------------------

	/**
	 * Sla een nieuw record op. Vult $this->id met het nieuwe auto-increment id.
	 * Stelt datetime_created en datetime_modified automatisch in.
	 */
	public function saveToDB(PDO $connection): bool
	{
		$now = (new DateTimeImmutable())->format('Y-m-d H:i:s');
		$this->datetime_created   = $now;
		$this->datetime_modified  = $now;

		try {
			$sql = "INSERT INTO sucstor (
						delind, id_user_created,
						datetime_created, datetime_modified,
						titel, subtitel, tekst, tekst_kort, tekst_samenvatting,
						tekst_knop, link_knop,
						pubind_intern, datetime_pub_intern,
						pubind_extern, datetime_pub_extern,
						picfile1, picfile2, picfile3, picfile4
					) VALUES (
						:delind, :id_user_created,
						:datetime_created, :datetime_modified,
						:titel, :subtitel, :tekst, :tekst_kort, :tekst_samenvatting,
						:tekst_knop, :link_knop,
						:pubind_intern, :datetime_pub_intern,
						:pubind_extern, :datetime_pub_extern,
						:picfile1, :picfile2, :picfile3, :picfile4
					)";

			$stmt = $connection->prepare($sql);
			$this->bindCommonValues($stmt);
			$stmt->execute();

			$this->id = (int) $connection->lastInsertId();
			return true;

		} catch (PDOException $e) {
			error_log('Sucstor::saveToDB mislukt: ' . $e->getMessage());
			return false;
		}
	}

	/**
	 * Werk een bestaand record bij. Stelt datetime_modified automatisch in.
	 */
	public function updateToDB(PDO $connection): bool
	{
		if ($this->id === null) {
			error_log('Sucstor::updateToDB aangeroepen zonder id');
			return false;
		}

		$this->datetime_modified = (new DateTimeImmutable())->format('Y-m-d H:i:s');

		try {
			$sql = "UPDATE sucstor SET
						delind              = :delind,
						id_user_created     = :id_user_created,
						datetime_created    = :datetime_created,
						datetime_modified   = :datetime_modified,
						titel               = :titel,
						subtitel            = :subtitel,
						tekst               = :tekst,
						tekst_kort          = :tekst_kort,
						tekst_samenvatting  = :tekst_samenvatting,
						tekst_knop          = :tekst_knop,
						link_knop           = :link_knop,
						pubind_intern       = :pubind_intern,
						datetime_pub_intern = :datetime_pub_intern,
						pubind_extern       = :pubind_extern,
						datetime_pub_extern = :datetime_pub_extern,
						picfile1            = :picfile1,
						picfile2            = :picfile2,
						picfile3            = :picfile3,
						picfile4            = :picfile4
					WHERE id = :id";

			$stmt = $connection->prepare($sql);
			$stmt->bindValue(':id', $this->id, PDO::PARAM_INT);
			$this->bindCommonValues($stmt);
			$stmt->execute();
			return true;

		} catch (PDOException $e) {
			error_log('Sucstor::updateToDB mislukt: ' . $e->getMessage());
			return false;
		}
	}

	/**
	 * Zachte verwijdering: zet delind op 'j' in plaats van echt verwijderen.
	 */
	public function deleteFromDB(PDO $connection): bool
	{
		if ($this->id === null) {
			return false;
		}
		try {
			$sql  = "UPDATE sucstor SET delind = 'j', datetime_modified = :now WHERE id = :id";
			$stmt = $connection->prepare($sql);
			$stmt->bindValue(':now', (new DateTimeImmutable())->format('Y-m-d H:i:s'), PDO::PARAM_STR);
			$stmt->bindValue(':id',  $this->id, PDO::PARAM_INT);
			$stmt->execute();
			$this->delind = 'j';
			return true;
		} catch (PDOException $e) {
			error_log('Sucstor::deleteFromDB mislukt: ' . $e->getMessage());
			return false;
		}
	}

	// -------------------------------------------------------------------------
	// Magic methods
	// -------------------------------------------------------------------------

	public function __get(string $attr)
	{
		if (in_array($attr, self::READABLE, true)) {
			return $this->$attr;
		}
		throw new \InvalidArgumentException("Sucstor: onbekend attribuut '$attr'");
	}

	public function __set(string $attr, $value): void
	{
		if (in_array($attr, self::WRITABLE, true)) {
			$this->$attr = $value;
			return;
		}
		throw new \InvalidArgumentException("Sucstor: attribuut '$attr' is niet schrijfbaar");
	}

	public function __toString(): string
	{
		return implode(PHP_EOL, [
			'id                  = ' . ($this->id ?? 'NULL'),
			'delind              = ' . $this->delind,
			'id_user_created     = ' . $this->id_user_created,
			'datetime_created    = ' . $this->datetime_created,
			'datetime_modified   = ' . $this->datetime_modified,
			'titel               = ' . $this->titel,
			'subtitel            = ' . $this->subtitel,
			'tekst_kort          = ' . $this->tekst_kort,
			'tekst_samenvatting  = ' . $this->tekst_samenvatting,
			'tekst_knop          = ' . $this->tekst_knop,
			'link_knop           = ' . $this->link_knop,
			'pubind_intern       = ' . $this->pubind_intern,
			'datetime_pub_intern = ' . $this->datetime_pub_intern,
			'pubind_extern       = ' . $this->pubind_extern,
			'datetime_pub_extern = ' . $this->datetime_pub_extern,
			'picfile1            = ' . $this->picfile1,
			'picfile2            = ' . $this->picfile2,
			'picfile3            = ' . $this->picfile3,
			'picfile4            = ' . $this->picfile4,
		]);
	}

	// -------------------------------------------------------------------------
	// Private helpers
	// -------------------------------------------------------------------------

	/**
	 * Bind alle velden die zowel bij INSERT als UPDATE gebruikt worden.
	 */
	private function bindCommonValues(\PDOStatement $stmt): void
	{
		$stmt->bindValue(':delind',              $this->delind,              PDO::PARAM_STR);
		$stmt->bindValue(':id_user_created',     $this->id_user_created,     PDO::PARAM_STR);
		$stmt->bindValue(':datetime_created',    $this->datetime_created,    PDO::PARAM_STR);
		$stmt->bindValue(':datetime_modified',   $this->datetime_modified,   PDO::PARAM_STR);
		$stmt->bindValue(':titel',               $this->titel,               PDO::PARAM_STR);
		$stmt->bindValue(':subtitel',            $this->subtitel,            PDO::PARAM_STR);
		$stmt->bindValue(':tekst',               $this->tekst,               PDO::PARAM_STR);
		$stmt->bindValue(':tekst_kort',          $this->tekst_kort,          PDO::PARAM_STR);
		$stmt->bindValue(':tekst_samenvatting',  $this->tekst_samenvatting,  PDO::PARAM_STR);
		$stmt->bindValue(':tekst_knop',          $this->tekst_knop,          PDO::PARAM_STR);
		$stmt->bindValue(':link_knop',           $this->link_knop,           PDO::PARAM_STR);
		$stmt->bindValue(':pubind_intern',       $this->pubind_intern,       PDO::PARAM_STR);
		$stmt->bindValue(':datetime_pub_intern', $this->datetime_pub_intern, PDO::PARAM_STR);
		$stmt->bindValue(':pubind_extern',       $this->pubind_extern,       PDO::PARAM_STR);
		$stmt->bindValue(':datetime_pub_extern', $this->datetime_pub_extern, PDO::PARAM_STR);
		$stmt->bindValue(':picfile1',            $this->picfile1,            PDO::PARAM_STR);
		$stmt->bindValue(':picfile2',            $this->picfile2,            PDO::PARAM_STR);
		$stmt->bindValue(':picfile3',            $this->picfile3,            PDO::PARAM_STR);
		$stmt->bindValue(':picfile4',            $this->picfile4,            PDO::PARAM_STR);
	}
}