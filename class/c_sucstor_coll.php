<?php
include_once('c_sucstor.php');

/**
 * Class Sucstor_coll
 *
 * Verbeteringen t.o.v. origineel:
 * - SQL-injectie gedicht: kolomnamen gewhitelist, waarden via prepared statement parameters
 * - Constructor overloading anti-pattern vervangen door één constructor met optionele parameters
 * - global $connection vervangen door dependency injection
 * - echo in execQuery vervangen door error_log (fouten horen niet zichtbaar in de output)
 * - Sucstor::fromRow() gebruikt i.p.v. new sucstor($row) (aansluitend op verbeterde c_sucstor.php)
 * - __get vereenvoudigd tot een expliciete getter
 * - ORDER BY richting gewhitelist (alleen ASC/DESC toegestaan)
 */
class Sucstor_coll
{
	protected array  $sucstorColl = [];
	protected string $query       = '';

	// Whitelist van kolomnamen die gebruikt mogen worden in WHERE en ORDER BY.
	// Voeg hier kolomnamen aan toe als de tabel uitgebreid wordt.
	private const ALLOWED_COLUMNS = [
		'id', 'delind', 'id_user_created', 'datetime_created', 'datetime_modified',
		'titel', 'subtitel', 'tekst', 'tekst_kort', 'tekst_samenvatting',
		'tekst_knop', 'link_knop',
		'pubind_intern', 'datetime_pub_intern',
		'pubind_extern', 'datetime_pub_extern',
		'picfile1', 'picfile2', 'picfile3', 'picfile4',
	];

	private const ALLOWED_DIRECTIONS = ['ASC', 'DESC'];

	// -------------------------------------------------------------------------
	// Constructor
	// -------------------------------------------------------------------------

	/**
	 * @param PDO        $connection  Database-verbinding
	 * @param array      $selectArr   Selectiecriteria: [[kolomnaam, waarde], ...]
	 *                                Lege array = geen extra filter
	 * @param array      $orderArr    Sorteervolgorde: [[kolomnaam, 'ASC'|'DESC'], ...]
	 * @param int|null   $limit       Maximaal aantal rijen, of null voor geen limiet
	 */
	public function __construct(
		PDO $connection,
		array  $selectArr = [],
		array  $orderArr  = [],
		?int   $limit     = null
	) {
		$this->execQuery($connection, $selectArr, $orderArr, $limit);
	}

	// -------------------------------------------------------------------------
	// Public interface
	// -------------------------------------------------------------------------

	public function getSucstorColl(): array
	{
		return $this->sucstorColl;
	}

	public function getQuery(): string
	{
		return $this->query ?: 'Er is geen query aangemaakt.';
	}

	public function count(): int
	{
		return count($this->sucstorColl);
	}

	// -------------------------------------------------------------------------
	// Private helpers
	// -------------------------------------------------------------------------

	private function execQuery(PDO $connection, array $selectArr, array $orderArr, ?int $limit): void
	{
		$params = [];

		// Bouw de WHERE-clausule op met geparametriseerde placeholders
		$sql = 'SELECT sucstor.* FROM sucstor WHERE sucstor.delind = "n"';

		foreach ($selectArr as $i => $selection) {
			$kolom  = $selection[0];
			$waarde = $selection[1];

			if (!in_array($kolom, self::ALLOWED_COLUMNS, true)) {
				error_log("Sucstor_coll: ongeldige kolomnaam in selectArr: '$kolom'");
				continue;
			}

			$placeholder     = ':where_' . $kolom . '_' . $i;
			$sql            .= ' AND ' . $kolom . ' = ' . $placeholder;
			$params[$placeholder] = $waarde;
		}

		// ORDER BY
		if (!empty($orderArr)) {
			$orderParts = [];
			foreach ($orderArr as $sort) {
				$kolom     = $sort[0];
				$richting  = strtoupper($sort[1]);

				if (!in_array($kolom, self::ALLOWED_COLUMNS, true)) {
					error_log("Sucstor_coll: ongeldige kolomnaam in orderArr: '$kolom'");
					continue;
				}
				if (!in_array($richting, self::ALLOWED_DIRECTIONS, true)) {
					error_log("Sucstor_coll: ongeldige sorteervolgorde: '$richting'");
					continue;
				}

				$orderParts[] = $kolom . ' ' . $richting;
			}
			if (!empty($orderParts)) {
				$sql .= ' ORDER BY ' . implode(', ', $orderParts);
			}
		}

		// LIMIT — integer, dus direct inlineren is veilig
		if ($limit !== null) {
			$sql .= ' LIMIT ' . $limit;
		}

		$this->query = $sql;

		try {
			$stmt = $connection->prepare($sql);
			foreach ($params as $placeholder => $waarde) {
				$stmt->bindValue($placeholder, $waarde, PDO::PARAM_STR);
			}
			$stmt->execute();
			$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

			foreach ($rows as $row) {
				$this->sucstorColl[] = Sucstor::fromRow($row);
			}
		} catch (PDOException $e) {
			error_log('Sucstor_coll::execQuery mislukt: ' . $e->getMessage());
		}
	}
}
