<?php
include_once('c_person.php');
class User extends Person
{
	protected $id;
	protected $id_person;
	protected $username;
	protected $password;
	protected $password_mod;
	protected $password_hash;	// hash van het wachtwoord (okt 2026)
	protected $reset_hash;		// sha256 van de token uit de "wachtwoord vergeten"-link
	protected $reset_expires;	// tot wanneer die link geldig is
	protected $activity;
	protected $beheerind;
	protected $berichtInd;
	protected $berichtSentInd;
	protected $forumNoteInd;
	protected $id_team;
	protected $rechten;			// nov 2026: lijst van rechten uit user_recht; NULL = nog niet gelezen (zie rechten())
	
	public function __construct () 
	{
		$this->id             = NULL;
		$this->id_person      = NULL;
		$this->username       = '';
		$this->password 	  = '';
		$this->password_mod	  = '';
		$this->password_hash  = '';
		$this->reset_hash     = NULL;
		$this->reset_expires  = NULL;
		$this->activity       = '';
		$this->beheerind      = '';
		$this->berichtInd       = '';
		$this->berichtSentInd       = '';
		$this->forumNoteInd	   ='';
		$this->id_team = NULL;
		$this->rechten = NULL;
		parent::__construct ();	
	
		$a = func_get_args(); 
		$i = func_num_args();
		if (method_exists($this,$f='__construct'.$i)) {
			call_user_func_array(array($this,$f),$a);
		}
	}

	public function __construct1 ($userrow) 
	{

		if ($userrow)
		{
			$this->id             = $userrow['id'];
			$this->id_person      = $userrow['id_person'];
			$this->username       = $userrow['username'];
			$this->password 	  = $userrow['password'];
			$this->password_mod	  = $userrow['password_mod'];
			// ?? geeft de waarde rechts als de kolom (nog) niet bestaat of NULL is
			$this->password_hash  = $userrow['password_hash'] ?? '';
			$this->reset_hash     = $userrow['reset_hash'] ?? NULL;
			$this->reset_expires  = $userrow['reset_expires'] ?? NULL;
			$this->activity       = $userrow['activity'];
			$this->beheerind      = $userrow['beheerind'];
			$this->berichtInd       = $userrow['berichtInd'];
			$this->berichtSentInd       = $userrow['berichtSentInd'];
			$this->forumNoteInd	   = $userrow['forumNoteInd'];
			$this->id_team	   = $userrow['id_team'];
			parent::__construct1 ($userrow);		
		}
		else
			$this->id = NULL;

	}
	
	public function __construct2 ($attr, $value)
	{
		/* id, gebruikersnaam of emailadres is bekend, haal op uit DB 
			als HistId wordt gebruikt dan wordt ook een user gegeven die delind = n heeft. De user bestaat dus niet op het moment van ophalen maar staat wel in de database
		*/
		switch ($attr)
		{
			case 'id':
				$this->__construct1($this->readUserWithId($value, NULL));
				break;				
			case 'username':
				$this->__construct1($this->readUserWithUsername($value));
				break;
			case 'id_person':
				$this->__construct1($this->readUserWithId_person($value));
				break;
			case 'emailadres':
				$this->__construct1($this->readUserWithEmailadres($value));
				break;
			case 'resettoken':
				$this->__construct1($this->readUserWithResetToken($value));
				break;
			case 'histid':
				$this->__construct1($this->readUserWithId($value, 'h'));
				break;
			default:
				return FALSE;
		}
	}
		
	public function readUserWithId ($attr, $h)
	{
		/* Haal de gegevens uit de database
			$userid kan 2 soorten waarde hebben:
			NULL of 0 => het object bestaat niet in de database => zo laten
			integer => het object kan uit de DB gelezen worden => ophalen en attrs vullen
		*/
		global $connection;
		try
		{
			openDB();
			$sql = "SELECT user.*, person.* FROM user JOIN person ON user.id_person = person.person_id WHERE user.id = :id ";
			if ($h != 'h')
				$sql .= "AND person.delind = 'n' ";
			$sql .= "LIMIT 1;";
			
			$stmt = $connection->prepare( $sql );
			$stmt->bindValue( ":id", $attr, PDO::PARAM_STR);
	//			echo $sql . '<br/>';
			$stmt->execute();
			$userrow = $stmt->fetch(PDO::FETCH_ASSOC);
		}
		catch (PDOException $e) 
		{
			  error_log('Connectie (user 2) met de database mislukt: ' . $e->getMessage());
			  return FALSE;
		}
		return $userrow;	
	}

	public function readUserWithUsername ($attr)
	{
		/* Haal de gegevens uit de database
			$userid kan 2 soorten waarde hebben:
			NULL of 0 => het object bestaat niet in de database => zo laten
			integer => het object kan uit de DB gelezen worden => ophalen en attrs vullen
		*/
		global $connection;
		try
		{
			openDB();
			$sql = "SELECT user.*, person.* FROM user JOIN person ON user.id_person = person.person_id WHERE user.username = :username AND person.delind = 'n' LIMIT 1;";
			
			$stmt = $connection->prepare( $sql );
			$stmt->bindValue( ":username", $attr, PDO::PARAM_STR);
	//			echo $sql . '<br/>';
			$stmt->execute();
			$userrow = $stmt->fetch(PDO::FETCH_ASSOC);
		}
		catch (PDOException $e) 
		{
			  error_log('Connectie (user 3) met de database mislukt: ' . $e->getMessage());
			  return FALSE;
		}
	    return $userrow;	
	}
	
	public function readUserWithId_person ($attr)
	{
		global $connection;
		try
		{
			openDB();
			$sql = "SELECT user.*, person.* FROM user JOIN person ON user.id_person = person.person_id WHERE user.id_person = :id_person AND person.delind = 'n' LIMIT 1;";
			
			$stmt = $connection->prepare( $sql );
			$stmt->bindValue( ":id_person", $attr, PDO::PARAM_STR);
	//			echo $sql . '<br/>';
			$stmt->execute();
			$userrow = $stmt->fetch(PDO::FETCH_ASSOC);
		}
		catch (PDOException $e) 
		{
			  error_log('Connectie (user 5) met de database mislukt: ' . $e->getMessage());
			  return FALSE;
		}
		return $userrow;	
	}

	public function readUserWithEmailadres ($attr)
	{
		global $connection;
		try
		{
			openDB();
			$sql = "SELECT user.*, person.* FROM user INNER JOIN person ON user.id_person = person.person_id WHERE person.emailadres = :emailadres AND person.delind = 'n' LIMIT 1;";
			
			$stmt = $connection->prepare( $sql );
			$stmt->bindValue( ":emailadres", $attr, PDO::PARAM_STR);
	//			echo $sql . '<br/>';
			$stmt->execute();
			$userrow = $stmt->fetch(PDO::FETCH_ASSOC);
		}
		catch (PDOException $e) 
		{
			  error_log('Connectie (user 4) met de database mislukt: ' . $e->getMessage());
			  return FALSE;
		}
		return $userrow;	
	}

	public function readUserWithResetToken ($token)
	{
		/* Zoek de gebruiker bij een token uit de "wachtwoord vergeten"-link.
			In de database staat alleen de sha256-hash van de token. Komt de
			database ooit op straat te liggen, dan kan niemand met die hashes
			een wachtwoord resetten.
			De tijd wordt in PHP bepaald (niet met NOW() in MySQL), zodat een
			verschil in tijdzone tussen PHP en MySQL geen rol speelt.
		*/
		global $connection;
		try
		{
			openDB();
			$sql = "SELECT user.*, person.* FROM user JOIN person ON user.id_person = person.person_id WHERE user.reset_hash = :reset_hash AND user.reset_expires > :nu AND person.delind = 'n' LIMIT 1;";
			$stmt = $connection->prepare( $sql );
			$stmt->bindValue( ":reset_hash", hash('sha256', (string)$token), PDO::PARAM_STR);
			$stmt->bindValue( ":nu", date('Y-m-d H:i:s'), PDO::PARAM_STR);
			$stmt->execute();
			$userrow = $stmt->fetch(PDO::FETCH_ASSOC);
		}
		catch (PDOException $e)
		{
			  error_log('Connectie (user 6) met de database mislukt: ' . $e->getMessage());
			  return FALSE;
		}
		return $userrow;
	}

	/* ------------------------------------------------------------------
	 * Wachtwoorden (okt 2026)
	 *
	 * password_hash: de hash van het wachtwoord, gemaakt met password_hash().
	 *   Een hash kun je niet terugrekenen naar het wachtwoord. Bij inloggen
	 *   rekent password_verify() het ingetypte wachtwoord op dezelfde manier
	 *   om en vergelijkt het resultaat.
	 * password: de oude kolom met een leesbaar wachtwoord. Alleen nog nodig
	 *   voor nieuwe gebruikers die in phpMyAdmin worden aangemaakt. Bij de
	 *   eerste keer inloggen wordt dat omgezet naar een hash en wordt deze
	 *   kolom leeggemaakt.
	 * ------------------------------------------------------------------ */
	public function verifyPassword (string $wachtwoord): bool
	{
		if ($this->password_hash != '')
		{
			if (!password_verify($wachtwoord, $this->password_hash))
				return FALSE;
			// Kiest PHP ooit een sterkere standaardmethode, dan wordt de hash
			// bij de volgende keer inloggen vanzelf vernieuwd.
			if (password_needs_rehash($this->password_hash, PASSWORD_DEFAULT))
				$this->setPassword($wachtwoord);
			return TRUE;
		}
		// Nog geen hash: leesbaar wachtwoord (nieuwe gebruiker uit phpMyAdmin).
		// hash_equals() doet er altijd even lang over, zodat de reactietijd
		// niet verraadt hoeveel tekens er goed waren.
		if ($this->password != '' && hash_equals((string)$this->password, $wachtwoord))
		{
			$this->setPassword($wachtwoord);	// de aanroeper slaat op met updateToDB()
			return TRUE;
		}
		return FALSE;
	}

	public function setPassword (string $wachtwoord): void
	{
		// PASSWORD_DEFAULT is nu bcrypt. Het zout (salt) zit in de hash zelf,
		// dus twee gelijke wachtwoorden geven toch een verschillende hash.
		$this->password_hash = password_hash($wachtwoord, PASSWORD_DEFAULT);
		$this->password      = '';	// de leesbare versie verdwijnt
	}

	public static function wachtwoordFout (string $wachtwoord): ?string
	{
		// Geeft een foutmelding, of NULL als het wachtwoord goed is.
		if (mb_strlen($wachtwoord) < 8)
			return 'Het nieuwe wachtwoord moet minimaal 8 tekens lang zijn.';
		if (strlen($wachtwoord) > 72)	// bcrypt kijkt niet verder dan 72 bytes
			return 'Het nieuwe wachtwoord mag maximaal 72 tekens lang zijn.';
		return NULL;
	}

	public function maakResetToken (): string
	{
		// random_bytes() is cryptografisch veilig (rand() is dat niet).
		// 32 bytes = 64 hexadecimale tekens: onmogelijk te raden.
		$token = bin2hex(random_bytes(32));
		$this->reset_hash    = hash('sha256', $token);
		$this->reset_expires = date('Y-m-d H:i:s', time() + 3600);	// 1 uur geldig
		return $token;	// alleen deze versie gaat in de mail; opslaan met updateToDB()
	}

	public function resetRecentAangevraagd (): bool
	{
		// TRUE als er minder dan 5 minuten geleden al een link is gestuurd.
		// Zo kan niemand iemands mailbox volspammen via "wachtwoord vergeten".
		if (empty($this->reset_expires))
			return FALSE;
		return strtotime($this->reset_expires) > time() + 3600 - 300;
	}

	public function wisResetToken (): void
	{
		$this->reset_hash    = NULL;
		$this->reset_expires = NULL;
	}

	/* ------------------------------------------------------------------
	 * Rechten (nov 2026)
	 *
	 * Elke gebruiker heeft een lijst van rechten in de tabel user_recht
	 * (één regel per recht). Welke rechten er bestaan, staat in
	 * includes/rechten.php. Pagina's vragen het na met heeft_recht('naam')
	 * uit config.php; die gebruikt rechten() hieronder.
	 * ------------------------------------------------------------------ */

	/* Zolang de migratie (tabel user_recht) nog niet is gedraaid, gelden de
	   oude niveaus uit user.beheerind. Dit is precies de omzetting uit
	   migraties/2026-11-06_rechten-per-gebruiker_jhmz.sql.
	   Kan weg zodra de migratie overal is gedraaid en beheerind is verwijderd. */
	const NIVEAU_RECHTEN = [
		'0' => [],
		'1' => ['forum_lezen'],
		'2' => ['forum_lezen', 'forum_schrijven', 'documenten_eigen'],
		'3' => ['forum_lezen', 'forum_schrijven', 'documenten_eigen', 'redactie'],
		'4' => ['forum_lezen', 'forum_schrijven', 'documenten_eigen', 'was_lezen'],
		'5' => ['forum_lezen', 'forum_schrijven', 'documenten_eigen', 'was_lezen', 'was_wijzigen', 'documenten_gedeeld', 'documenten_werkzoekenden'],
		'6' => ['forum_lezen', 'forum_schrijven', 'documenten_eigen', 'was_lezen', 'was_wijzigen', 'documenten_gedeeld', 'documenten_werkzoekenden', 'maatjes_wijzigen', 'presentie_anderen'],
		'7' => ['forum_lezen', 'forum_schrijven', 'documenten_eigen', 'redactie', 'was_lezen'],
		'8' => ['forum_lezen', 'forum_schrijven', 'documenten_eigen', 'redactie', 'was_lezen', 'was_wijzigen', 'documenten_gedeeld', 'documenten_werkzoekenden'],
		'9' => ['beheerder'],
		'j' => ['beheerder'],
	];

	public function rechten (): array
	{
		// De rechten van deze gebruiker, bijvoorbeeld ['forum_lezen', 'was_lezen'].
		// Wordt één keer gelezen en daarna onthouden in $this->rechten.
		if ($this->rechten === NULL)
		{
			$this->rechten = [];
			if ($this->id)
			{
				global $connection;
				try
				{
					openDB();
					$stmt = $connection->prepare("SELECT recht FROM user_recht WHERE user_id = :id ORDER BY recht");
					$stmt->execute([':id' => $this->id]);
					// FETCH_COLUMN: alleen de eerste kolom, als gewone lijst
					$this->rechten = $stmt->fetchAll(PDO::FETCH_COLUMN);
				}
				catch (PDOException $e)
				{
					// Meestal: de tabel user_recht bestaat (nog) niet. Dan de oude niveaus.
					error_log('Rechten (user 7): user_recht niet leesbaar, beheerind gebruikt: ' . $e->getMessage());
					$this->rechten = self::NIVEAU_RECHTEN[(string)$this->beheerind] ?? [];
				}
			}
		}
		return $this->rechten;
	}

	public function isBeheerder (): bool
	{
		return in_array('beheerder', $this->rechten(), true);
	}

	public function slaRechtenOp (array $rechten): bool
	{
		/* Vervangt alle rechten van deze gebruiker door $rechten.
		   Een transactie zorgt dat het in één keer gebeurt: lukt het
		   toevoegen niet, dan wordt ook het verwijderen teruggedraaid
		   (rollBack) en houdt de gebruiker zijn oude rechten.
		   Controle op wat wel en niet mag, gebeurt in systeem.php. */
		global $connection;
		$rechten = array_values(array_unique($rechten));
		try
		{
			openDB();
			$connection->beginTransaction();
			$connection->prepare("DELETE FROM user_recht WHERE user_id = :id")->execute([':id' => $this->id]);
			$stmt = $connection->prepare("INSERT INTO user_recht (user_id, recht) VALUES (:id, :recht)");
			foreach ($rechten as $recht)
				$stmt->execute([':id' => $this->id, ':recht' => $recht]);
			$connection->commit();
		}
		catch (PDOException $e)
		{
			if ($connection->inTransaction()) $connection->rollBack();
			error_log('Rechten opslaan (user 8) mislukt: ' . $e->getMessage());
			return FALSE;
		}
		sort($rechten);
		$this->rechten = $rechten;
		return TRUE;
	}

	/* ------------------------------------------------------------------
	 * Voor het Systeem-scherm (systeem.php, nov 2026)
	 * ------------------------------------------------------------------ */

	public static function usernameBestaat (string $username): bool
	{
		// Ook geblokkeerde gebruikers tellen mee: een inlognaam is uniek in de tabel.
		global $connection;
		openDB();
		$stmt = $connection->prepare("SELECT COUNT(*) FROM user WHERE username = :u");
		$stmt->execute([':u' => $username]);
		return (int)$stmt->fetchColumn() > 0;
	}

	public static function maak (int $idPerson, string $username, string $wachtwoord): ?int
	{
		/* Nieuwe gebruiker voor een bestaand maatje (person_id).
		   Het wachtwoord wordt meteen als hash opgeslagen; password_mod = 'j'
		   zorgt dat hij het bij de eerste keer inloggen zelf moet wijzigen.
		   activity is verplicht in de tabel; we vullen het moment van aanmaken in.
		   Geeft het nieuwe user.id terug, of NULL als het niet lukte. */
		global $connection;
		try
		{
			openDB();
			$sql = "INSERT INTO user (id_person, username, password, password_hash, password_mod, activity,
							beheerind, berichtInd, berichtSentInd, forumNoteInd, id_team)
					VALUES (:id_person, :username, '', :hash, 'j', :nu, '0', 'n', 'n', 'n', 0)";
			$stmt = $connection->prepare($sql);
			$stmt->execute([
				':id_person' => $idPerson,
				':username'  => $username,
				':hash'      => password_hash($wachtwoord, PASSWORD_DEFAULT),
				':nu'        => date('Y-m-d H:i:s'),
			]);
			return (int)$connection->lastInsertId();
		}
		catch (PDOException $e)
		{
			error_log('Gebruiker aanmaken (user 9) mislukt: ' . $e->getMessage());
			return NULL;
		}
	}

	public static function lijstSysteem (): array
	{
		/* Alle gebruikers, ook de geblokkeerde (person.delind = 'j'), op achternaam.
		   Per gebruiker een array met de kolommen uit user en person, plus
		   'rechten' (een lijst). GROUP_CONCAT plakt de rechten in één query
		   aan elkaar ("forum_lezen,was_lezen"); explode() maakt er weer een lijst van. */
		global $connection;
		openDB();
		try
		{
			$sql = "SELECT user.id, user.username, user.activity, user.password_mod, user.berichtInd, user.forumNoteInd,
						user.beheerind, person.person_id, person.voornaam, person.tussenvoegsels, person.achternaam,
						person.emailadres, person.delind,
						(SELECT GROUP_CONCAT(r.recht ORDER BY r.recht) FROM user_recht r WHERE r.user_id = user.id) AS rechten
					FROM user JOIN person ON user.id_person = person.person_id
					ORDER BY (person.delind = 'n') DESC, person.achternaam, person.voornaam";
			$rows = $connection->query($sql)->fetchAll(PDO::FETCH_ASSOC);
			foreach ($rows as &$row)
				$row['rechten'] = $row['rechten'] === NULL ? [] : explode(',', $row['rechten']);
			unset($row);	// de verwijzing (&) opheffen, anders kan een volgende foreach $rows beschadigen
		}
		catch (PDOException $e)
		{
			error_log('Gebruikerslijst (user 10) mislukt: ' . $e->getMessage());
			return [];
		}
		return $rows;
	}

	public function mustChangePassword()
	{
		if ($this->password_mod == 'j')
		return TRUE;
		else 
		return FALSE;
	}
	
	public function updateToDB () 
	{
		global $connection;
		try
		{
			// openDB();
			$sql = "UPDATE user SET
								id_person =		:id_person   , 
								username =		:username    , 
								password = 		:password 	 , 
								password_mod = 		:password_mod 	 , 
								password_hash =	:password_hash ,
								reset_hash =	:reset_hash ,
								reset_expires =	:reset_expires ,
								activity =		:activity    , 
								beheerind =		:beheerind   , 
								berichtInd =	:berichtInd    , 
								berichtSentInd = :berichtSentInd ,
								forumNoteInd = :forumNoteInd ,
								id_team = :id_team 
							WHERE id = :id";
			
			
			$stmt = $connection->prepare( $sql );
			$stmt->bindValue( ":id"          , $this->id, PDO::PARAM_STR);
			$stmt->bindValue( ":id_person"   , $this->id_person, PDO::PARAM_STR);
			$stmt->bindValue( ":username"    , $this->username, PDO::PARAM_STR);
			$stmt->bindValue( ":password" 	 , $this->password, PDO::PARAM_STR);
			$stmt->bindValue( ":password_mod" 	 , $this->password_mod, PDO::PARAM_STR);
			$stmt->bindValue( ":password_hash"	 , $this->password_hash, PDO::PARAM_STR);
			// PARAM_NULL zorgt dat een lege reset-link echt NULL wordt in de database
			$stmt->bindValue( ":reset_hash"		 , $this->reset_hash, $this->reset_hash === NULL ? PDO::PARAM_NULL : PDO::PARAM_STR);
			$stmt->bindValue( ":reset_expires"	 , $this->reset_expires, $this->reset_expires === NULL ? PDO::PARAM_NULL : PDO::PARAM_STR);
			$stmt->bindValue( ":activity"    , $this->activity, PDO::PARAM_STR);
			$stmt->bindValue( ":beheerind"   , $this->beheerind, PDO::PARAM_STR);
			$stmt->bindValue( ":berichtInd"    , $this->berichtInd, PDO::PARAM_STR);
			$stmt->bindValue( ":berichtSentInd"	, $this->berichtSentInd, PDO::PARAM_STR);
			$stmt->bindValue( ":forumNoteInd"	, $this->forumNoteInd, PDO::PARAM_STR);
			$stmt->bindValue( ":id_team"	, $this->id_team, PDO::PARAM_STR);
			$stmt->execute();
		}
		catch (PDOException $e) 
		{
			  error_log('Connectie (user 5) met de database mislukt: ' . $e->getMessage());
			  return FALSE;
		}
		parent::updateToDB ();
		return TRUE;	
	}

}
