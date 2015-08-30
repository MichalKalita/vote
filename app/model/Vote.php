<?php

namespace App\Model;

use Nette;
use Nette\Caching\Cache;


class Vote extends Nette\Object
{

	/** @var Nette\Database\Context */
	private $database;

	/** @var Cache */
	private $cache;

	public function __construct(Nette\Database\Context $database, Cache $cache)
	{
		$this->database = $database;
		$this->cache = $cache;
	}

}
