<?php

namespace App\AdminModule\Model;

use \Nette,
	\Nette\Security\Passwords,
	\Nette\Utils\Paginator;


/**
 * Users management.
 */
class UserManager extends Nette\Object implements Nette\Security\IAuthenticator
{

	const
			TABLE_NAME = 'user',
			COLUMN_ID = 'id',
			COLUMN_NAME = 'name',
			COLUMN_PASSWORD_HASH = 'password',
			COLUMN_ROLE = 'roles';

	/** @var Nette\Database\Context */
	private $database;

	public function __construct(Nette\Database\Context $database)
	{
		$this->database = $database;
	}

	/**
	 * Performs an authentication.
	 * @return Nette\Security\Identity
	 * @throws Nette\Security\AuthenticationException
	 */
	public function authenticate(array $credentials)
	{
		list($username, $password) = $credentials;

		$row = $this->database->table(self::TABLE_NAME)->where(self::COLUMN_NAME,
						$username)->fetch();

		if (!$row) {
			throw new Nette\Security\AuthenticationException('The username is incorrect.',
			self::IDENTITY_NOT_FOUND);
		} elseif (!Passwords::verify($password, $row[self::COLUMN_PASSWORD_HASH])) {
			throw new Nette\Security\AuthenticationException('The password is incorrect.',
			self::INVALID_CREDENTIAL);
		} elseif (Passwords::needsRehash($row[self::COLUMN_PASSWORD_HASH])) {
			$row->update(array(
					self::COLUMN_PASSWORD_HASH => Passwords::hash($password),
			));
		}

		$arr = $row->toArray();
		unset($arr[self::COLUMN_PASSWORD_HASH]);
		return new Nette\Security\Identity($row[self::COLUMN_ID],
				$row[self::COLUMN_ROLE], $arr);
	}

	/**
	 * Adds new user.
	 * @param  string
	 * @param  string
	 * @return void
	 */
	public function pridat($jmeno, $heslo, $role)
	{
		$this->database->table(self::TABLE_NAME)->insert(array(
				self::COLUMN_NAME => $jmeno,
				self::COLUMN_PASSWORD_HASH => Passwords::hash($heslo),
				self::COLUMN_ROLE => $role,
		));
	}

	/**
	 * Načte podle ID
	 * @param int
	 * @return \Nette\Database\Table\Selection
	 */
	public function nacti($id)
	{
		return $this->database->table(self::TABLE_NAME)->where(self::COLUMN_ID,
						(int) $id)->fetch();
	}

	/**
	 * Upraví
	 * @param int
	 * @param string
	 * @param string
	 * @param string
	 * @return int
	 */
	public function uprav($id, $jmeno, $heslo, $role)
	{
		$u = array(
				self::COLUMN_NAME => $jmeno,
				self::COLUMN_ROLE => $role,
		);
		if ($heslo) {
			$u[self::COLUMN_PASSWORD_HASH] = Passwords::hash($heslo);
		}
		return $this->database->table(self::TABLE_NAME)
						->where(self::COLUMN_ID, (int) $id)
						->update($u);
	}

	/**
	 * Vytváří základ pro výpis,
	 * výsledek nijak neseřazuje ani nespouští načítání
	 * @return Nette\Database\Table\Selection
	 */
	private function zakladVypisu()
	{
		return $this->database->table(self::TABLE_NAME);
	}

	/**
	 * Počet oddělení
	 * @param string hledana hodnota
	 * @return int
	 */
	public function pocet()
	{
		return $this->zakladVypisu()->count(self::COLUMN_ID);
	}

	/**
	 * Výpis oddělení
	 * @param \Nette\Utils\Paginator
	 * @return Nette\Database\Table\Selection
	 */
	public function vypis(Paginator $paginator)
	{
		return $this->zakladVypisu()
						->order(self::COLUMN_NAME)
						->limit($paginator->getLength(), $paginator->getOffset());
	}
	
	/**
	 * Odstraní polozku
	 * @param int $id
	 * @return int
	 */
	public function odstran($id)
	{
		return $this->database->table(self::TABLE_NAME)
						->where(self::COLUMN_ID, $id)->delete();
	}

}
