<?php

namespace App\Core\Database\Migration;

use App\Core\App;

class SchemaFactory
{
	public function __construct($schemaName = '', $schemaPath = '', $migration_table = '')
	{
		$this->schemaFilePath = $schemaPath . $schemaName;
		$this->mysqlPath = App::get('config')['app']['mysql_path'];

		// database engine
		$this->engine = "InnoDB";

		$this->database = App::get('config')['database']['name'];

		$this->migration_table = $migration_table;
	}

	/**
	 * Create a scaffold of our create table
	 * 
	 */
	public function createSchema($table, $cols, $primary)
	{
		$create_structure = "";
		$engine = $this->engine;
		$create_structure .= "CREATE TABLE `$table` (";
		foreach ($cols as $col => $datatype) {
			$create_structure .= "`$col` " . $datatype . ",";
		}
		$create_structure .= "PRIMARY KEY (`$primary`) USING BTREE
		)
		COLLATE='latin1_swedish_ci'
		ENGINE=$engine
		;";

		return $create_structure;
	}

	/**
	 * Create a scaffold of our alter table
	 * 
	 */
	public function alterSchema($table, $cols)
	{
		$countCols = 1;
		$alter_structure = "";
		$alter_structure .= "ALTER TABLE `$table` ";
		foreach ($cols as $datatype) {
			$putComma = ($countCols < count($cols)) ? ", " : " ";
			$alter_structure .= $datatype . $putComma;
			$countCols++;
		}
		$alter_structure .= ";";

		return $alter_structure;
	}

	public function renameTableSchema($cols)
	{
		$scaffold = "";
		foreach ($cols as $from => $to) {
			$scaffold .= "ALTER TABLE `$from` ";
			$scaffold .= "RENAME TO `$to`;";
		}
		return $scaffold;
	}

	/**
	 * Create a scaffold of our drop table
	 * 
	 */
	public function dropSchema($table)
	{
		return "DROP TABLE IF EXISTS `$table`;";
	}

	/**
	 * check if migarations table exist in our database
	 * 
	 */
	public function tableExist($table)
	{
		$database = $this->database;
		$fetch = DB()->select("COUNT(TABLE_NAME) AS counted_rows", "INFORMATION_SCHEMA.TABLES", "TABLE_SCHEMA = '$database' AND TABLE_NAME = '$table'")->get();
		return ($fetch['counted_rows'] > 0) ? 1 : 0;
	}

	/**
	 * this will drop a single table
	 * set FOREIGN_KEY_CHECKS = 0
	 * 
	 */
	public function dropTable($table)
	{
		$this->foreignKeyChecks();
		DB()->query("DROP TABLE `$table`;");
	}

	/**
	 * this will drop a single table
	 * if exist set FOREIGN_KEY_CHECKS = 0
	 * 
	 */
	public function dropIfExists($table)
	{
		$this->foreignKeyChecks();
		DB()->query("DROP TABLE IF EXISTS `$table`;");
	}

	/**
	 * set FOREIGN_KEY_CHECKS = 0
	 * 
	 */
	public function foreignKeyChecks()
	{
		DB()->query("SET FOREIGN_KEY_CHECKS=0;");
	}

	/**
	 * this will drop all the tables in the database
	 * also drops the views
	 * 
	 */
	public function dropAllTables()
	{
		$tables = $this->allTables();
		if (count($tables) > 0) {
			foreach ($tables as $table) {
				$this->dropIfExists($table);
			}
		}

		return nl2br("Dropped all tables successfully.\n");
	}

	/**
	 * store all tables of a database to an array
	 * 
	 */
	public function allTables()
	{
		$tables_arr = [];
		$database = $this->database;
		$loop_all_table = DB()->selectLoop("TABLE_NAME", "INFORMATION_SCHEMA.TABLES", "TABLE_SCHEMA = '$database' AND TABLE_TYPE = 'BASE TABLE' ORDER BY TABLE_NAME ASC")->get();
		if (count($loop_all_table) > 0) {
			foreach ($loop_all_table as $tbl) {
				$tables_arr[] = $tbl['TABLE_NAME'];
			}
		}

		return $tables_arr;
	}

	/**
	 * this will execute the stored database schema
	 * 
	 */
	public function importStoredDatabaseSchema()
	{
		$output = null;
		$retval = null;
		$command = $this->importSql();
		exec($command, $output, $retval);

		return $retval;
	}

	/**
	 * Initial dump structure
	 * 
	 */
	public function schemaDump($option)
	{
		$output = null;
		$retval = null;
		$buildDump = $this->sqlDumpNodata();
		exec($buildDump, $output, $retval);

		if ($option != "prune") {
			// we include the table that has presets data
			$migrationsData = $this->sqlDump();
			exec($migrationsData);
		}

		return $retval;
	}

	/**
	 * This is the options we used in dump
	 * 
	 */
	public function optionDump()
	{
		return "--single-transaction --skip-add-drop-table --skip-add-locks --skip-comments --skip-set-charset --tz-utc";
	}

	/**
	 * This is the option where the dump should go
	 * 
	 */
	public function resultFile()
	{
		return '--result-file="' . $this->schemaFilePath . '"';
	}

	/**
	 * the connection we used to connect to mysql
	 * 
	 */
	public function conn()
	{
		return '--host="' . App::get('config')['database']['connection'] . '" --user="' . App::get('config')['database']['username'] . '" --password="' . App::get('config')['database']['password'] . '"';
	}

	/**
	 * this will manufacture our import structure
	 * 
	 */
	public function importSql()
	{
		return $this->mysqlPath . 'mysql ' . $this->conn() . ' ' . $this->database . ' < ' . $this->schemaFilePath;
	}

	/**
	 * this will manufacture our dump structure
	 * this one has no data it only dump
	 * table schema
	 * 
	 */
	public function sqlDumpNodata()
	{
		return $this->mysqlPath . "mysqldump " . $this->optionDump() . " " . $this->conn() . " " . $this->database . " --routines " . $this->resultFile() . " --no-data";
	}

	/**
	 * this will manufacture our dump structure
	 * this one has data that we specify in
	 * tableWithPresets method
	 * 
	 */
	public function sqlDump()
	{
		return $this->mysqlPath . "mysqldump " . $this->optionDump() . " " . $this->conn() . " --no-create-info --skip-triggers " . $this->database . " " . $this->migration_table . " >> " . $this->schemaFilePath;
	}
}
