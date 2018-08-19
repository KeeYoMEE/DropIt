<?php

namespace Dt\Db;

use \Dt\Db\Medoo;

class Database
{
    private $db;

	public function __construct($args)
	{
		$this->db = new Medoo($args);
	}

	public function getInstance()
	{
		return $this->db;
	}
}