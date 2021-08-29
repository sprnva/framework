<?php

namespace App\Core\Database\Migration;

class Migration
{
	public function __construct()
	{
		$this->migrator = new Migrator;
	}

	/**
	 * This will migrate the content of the all the 
	 * new migration files in the migration bin
	 */
	public function migrate()
	{
		return $this->migrator->runPending();
	}

	/**
	 * This will drop all the tables and
	 * load stored database schema if exist
	 * migrate the content of the all the migration files
	 * in the migration bin
	 */
	public function fresh()
	{
		return $this->migrator->migrateFresh();
	}

	/**
	 * This will rollback/undo migration 1 step down
	 *
	 */
	public function rollback()
	{
		return $this->migrator->runRollback();
	}

	/**
	 * This will create a migration file
	 * @param string $name
	 * 
	 */
	public function make($name)
	{
		return $this->migrator->scaffold($name);
	}

	public function dump($prune = NULL)
	{
		return $this->migrator->dumpBuild($prune);
	}
}
