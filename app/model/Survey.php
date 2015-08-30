<?php

namespace App\Model;

use Nette;
use Nette\Caching\Cache;


class Survey extends Nette\Object
{

	public static $TABLE_NAME = 'survey';
	public static $COLUMN_PROJECT = 'project';
	public static $COLUMN_NAME = 'name';

	/** @var Nette\Database\Context */
	private $database;

	/** @var Cache */
	private $cache;

	public function __construct(Nette\Database\Context $database, Cache $cache)
	{
		$this->database = $database;
		$this->cache = $cache;
	}

	public function table()
	{
		return $this->database->table(self::$TABLE_NAME);
	}

	public function add($data)
	{
		return $this->table()->insert($data);
	}

	public function edit($id, $data)
	{
		return $this->table()->wherePrimary($id)->update($data);
	}

	public function delete($id)
	{
		return $this->table()->wherePrimary($id)->delete();
	}

	public function getFromProject($projectId)
	{
		return $this->table()->where(self::$COLUMN_PROJECT, $projectId)->order(self::$COLUMN_NAME);
	}

	public function get($id)
	{
		return $this->table()->wherePrimary($id)->fetch();
	}
}
