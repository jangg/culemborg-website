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

	public function isBeheerder ()
	{
		/*
		beheerind:
			Welk niveau welk recht geeft, staat in includes/rechten.php
			(gelijk voor beide sites).
			Gebruik in pagina's heeft_recht('naam') / require_recht('naam') uit config.php,
			niet het getal zelf.
			Oude waarde 'j' telt als 9.
		*/
		if ($this->beheerind == 'j' || $this->beheerind == '9')
		return TRUE;
		else
		return FALSE;
	}
	
	public function authLevel ()
	{
		/*
		beheerind:
			Welk niveau welk recht geeft, staat in includes/rechten.php
			(gelijk voor beide sites).
			Gebruik in pagina's heeft_recht('naam') / require_recht('naam') uit config.php,
			niet het getal zelf.
			Oude waarde 'j' telt als 9.
		*/
		if (is_numeric($this->beheerind))
			return $this->beheerind;
		else
		{
			if ($this->beheerind == 'j')
				 return '9';
			else return '0';
		}
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
