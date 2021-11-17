<?php

namespace App\Core\Database\Migration;

use App\Core\App;

class Migrator
{
	public function __construct()
	{
		$this->migrationFiles = "database/migrations/";
		$this->schemaFiles = "database/schema/";
		$this->stubsPath = "vendor/sprnva/framework/src/Database/Migration/stubs/";
		$this->schemaName = "mysql-schema.sql";

		$this->table = "migrations";
		$this->schema = new SchemaFactory($this->schemaName, $this->schemaFiles, $this->table);
		$this->repository = new MigrationRepository($this->schemaName, $this->table);
	}

	/**
	 * Scan the local repository and store in array
	 * remove the `.` and `..`
	 */
	public function localRepository()
	{
		$scanned = array_diff(scandir($this->migrationFiles), array('.', '..'));
		return $scanned;
	}

	/**
	 * Run "up" a migration instance.
	 * 
	 */
	public function runUp($file)
	{
		$data = $this->requireMigrationFiles($file);
		$mode = $data['mode'];
		$schemas = "";
		if ($mode != null) {
			$schemas .= $this->repository->schemaScaffold($mode, $data['table'], $data['up'], $data['primary_key'], "UP");
		}

		return $schemas;
	}

	/**
	 * This will include the migration file
	 * contents to the begin the run
	 * 
	 */
	public function requireMigrationFiles($file)
	{
		require_once($this->migrationFiles . $file);
		$line = $this->sanitizedName($this->removeDotPhp($file));
		$data = $$line;
		return $data;
	}

	/**
	 * This is the migration:migrate builder
	 * make sure database repository exist
	 * generate a next migration batch
	 * get pending migrations
	 * run up instance
	 * execute the schema
	 * insert migration name to migration table repository
	 * 
	 */
	public function runPending()
	{
		$response = "";
		$response .= $this->ensureRepositoryExist();
		$migration_batch = $this->repository->getNextBatchNumber();
		$pending_migrations = $this->pendingMigrations();
		if (count($pending_migrations) > 0) {
			foreach ($pending_migrations as $file) {
				$migration_name = $this->removeDotPhp($file);
				$schema = $this->runUp($file);
				if ($schema != "") {
					$query_response = DB()->query($schema);
					if ($query_response) {

						// Once we have run a migrations file, we will log that it was run in this
						// repository so that we don't try to run it next time we do a migration
						// in the application. A migration repository keeps the migrate order.
						$this->repository->log($migration_name, $migration_batch);
						$response .= nl2br("Migrated: $migration_name\n");
					} else {

						// if the schema failed to execute against the database
						$response .= nl2br("Failed: $migration_name\n");
						$response .= nl2br("-- $query_response[error]\n");
					}
				} else {

					// if the migrations file has nothing
					$response .= nl2br("Empty: $migration_name\n");
				}
			}
		} else {

			// if we have no outstanding migrations to migrate
			$response .= nl2br("Nothing to migrate\n");
		}

		return $response;
	}

	/**
	 * This is the outstanding migrations
	 * 
	 */
	public function pendingMigrations()
	{
		$outstanding = [];
		$this->ensureMigrationBinExist();
		foreach ($this->localRepository() as $file) {
			$line = $this->removeDotPhp($file);

			// Once we grab all of the migration files for the path, we will compare them
			// against the migrations that have already been run for this package then
			// run each of the outstanding migrations against a database connection.
			$result = $this->repository->getRan();
			if (!in_array($line, $result)) {
				$outstanding[] = $file;
			}
		}

		return $outstanding;
	}

	public function isUniqueFileName($fileName)
	{
		$allMigrations = $this->getAllMigrations();
		if (!in_array($fileName, $allMigrations)) {
			$res = 1;
		} else {
			$res = 0;
		}

		return $res;
	}

	public function getAllMigrations()
	{
		$dbRepo = $this->repository->getRan();
		foreach ($this->localRepository() as $file) {
			$fileName = $this->removeDotPhp($file);
			array_push($dbRepo, $fileName);
		}

		$cleanName = [];
		foreach ($dbRepo as $files) {
			$cleanName[] = $this->sanitizedName($files);
		}

		return $cleanName;
	}

	/**
	 * Ensure migraton table is present in our database
	 * 
	 */
	public function ensureRepositoryExist()
	{
		$response = "";
		$isExist = $this->schema->tableExist($this->table);
		if ($isExist == 0) {
			$response .= $this->repository->createRepository();
		}

		return $response;
	}

	/**
	 * Remove .php on migration file name
	 * 
	 */
	public function removeDotPhp($name)
	{
		return str_replace('.php', '', basename($name));
	}

	/**
	 * Sanitize the name, removes the numbers in
	 * the head of the migration file
	 * 
	 */
	public function sanitizedName($name)
	{
		$cut_name = explode('_', $name);
		$fileName = array_slice($cut_name, 1);
		return implode("_", $fileName);
	}

	/**
	 * This is the migration file scaffold
	 * the generation of migration file
	 * 
	 */
	public function scaffold($name)
	{
		$response = "";
		$this->ensureMigrationBinExist();
		$response .= $this->ensureRepositoryExist();
		$fileName = $this->buildMigrationName($name);
		$dir = $this->migrationFiles . $fileName;
		$migrationName = $this->removeDotPhp($fileName);
		$varName = $this->sanitizedName($migrationName);
		if ($this->isUniqueFileName($varName)) {
			$content = $this->populateStub($this->stubsPath . "migrations.stubs", $varName);
			if (!file_exists($dir)) {
				$handle = fopen($dir, 'w+');
				fwrite($handle, $content);
				fclose($handle);
				chmod($dir, 0777);

				$response .= nl2br("Created Migration: $migrationName\n-- visit directory database/migrations/\n");
			} else {
				$response .= nl2br("Migration Exist\n");
			}
		} else {
			$response .= nl2br("Migration Name: Already exist.\n");
		}

		return $response;
	}

	/**
	 * Let's get our migration stub file
	 * and populate dynamic variable specified
	 * 
	 */
	public function populateStub($path, $var_name)
	{
		if ($this->isFile($path)) {

			// Let's read the stubs file to string
			$stub = file_get_contents($path);

			// Here we will replace the table place-holders 
			// with the variable name specified.
			return str_replace('{{ varName }}', $var_name, $stub);
		}
	}

	/**
	 * this will create a users migration
	 * 
	 */
	public function userTableMigrate()
	{
		$isUserTableExist = $this->schema->tableExist('users');
		if (!$isUserTableExist) {

			$fileName = "20210408051901_create_users_table.php";
			$dir = $this->migrationFiles . $fileName;
			$content = file_get_contents($this->stubsPath . "user_migration.stubs");

			if (!file_exists($dir)) {
				$handle = fopen($dir, 'w+');
				fwrite($handle, $content);
				fclose($handle);
				chmod($dir, 0777);
			}
		}
	}

	/**
	 * this will create a roles migration
	 * 
	 */
	public function roleTableMigrate()
	{
		$isRoleTableExist = $this->schema->tableExist('role');
		if (!$isRoleTableExist) {

			$fileName = "20210408051901_create_roles_table.php";
			$dir = $this->migrationFiles . $fileName;
			$content = file_get_contents($this->stubsPath . "user_roles.stubs");

			if (!file_exists($dir)) {
				$handle = fopen($dir, 'w+');
				fwrite($handle, $content);
				fclose($handle);
				chmod($dir, 0777);
			}
		}
	}
	
	/**
	 * this will create a permissions migration
	 * 
	 */
	public function permissionTableMigrate()
	{
		$isRoleTableExist = $this->schema->tableExist('permissions');
		if (!$isRoleTableExist) {

			$fileName = "20210408051901_create_permission_table.php";
			$dir = $this->migrationFiles . $fileName;
			$content = file_get_contents($this->stubsPath . "permissions.stubs");

			if (!file_exists($dir)) {
				$handle = fopen($dir, 'w+');
				fwrite($handle, $content);
				fclose($handle);
				chmod($dir, 0777);
			}
		}
	}

	/**
	 * this will create a password_resets migration
	 * 
	 */
	public function passResetTableMigrate()
	{
		$isPassResetTableExist = $this->schema->tableExist('password_resets');
		if (!$isPassResetTableExist) {

			$fileName = "20210408051901_create_password_resets_table.php";
			$dir = $this->migrationFiles . $fileName;
			$content = file_get_contents($this->stubsPath . "password_resets.stubs");

			if (!file_exists($dir)) {
				$handle = fopen($dir, 'w+');
				fwrite($handle, $content);
				fclose($handle);
				chmod($dir, 0777);
			}
		}
	}

	/**
	 * Determine if the given path is a file.
	 * 
	 */
	public function isFile($file)
	{
		return is_file($file);
	}

	/**
	 * Ensure that migration bin is exist before creating scaffold
	 * 
	 */
	public function ensureMigrationBinExist()
	{
		if (!file_exists($this->migrationFiles)) {
			mkdir($this->migrationFiles);
			chmod($this->migrationFiles, 0777);
		}
	}

	/**
	 * Ensure that schema bin is exist
	 * 
	 */
	public function ensureSchemaBinExist()
	{
		if (!file_exists($this->schemaFiles)) {
			mkdir($this->schemaFiles);
			chmod($this->schemaFiles, 0777);
		}
	}

	/**
	 * Get the date prefix for the migration.
	 *
	 */
	protected function getDatePrefix()
	{
		return date('YmdHis');
	}

	/**
	 * make migration name.
	 * 
	 */
	protected function buildMigrationName($name)
	{
		$migrationName = preg_replace('/[^a-zA-Z0-9]+/', '_', $name);
		return $this->getDatePrefix() . "_" . $migrationName . '.php';
	}

	/**
	 * this is the migrate fresh builder
	 * drop all the tables in database
	 * generate the migration table
	 * run the stored database schema if exist
	 * run the migration
	 * add the views
	 * 
	 */
	public function migrateFresh()
	{
		$response = "";
		$response .= $this->schema->dropAllTables();
		$response .= $this->runStoredDbSchema();
		$this->userTableMigrate();
		$this->roleTableMigrate();
		$this->permissionTableMigrate();
		$this->passResetTableMigrate();
		$response .= $this->runPending();

		return $response;
	}

	/**
	 * Let's import the stored database schema in our database
	 * 
	 */
	public function runStoredDbSchema()
	{
		$response = "";
		$this->ensureSchemaBinExist();
		$file = $this->schemaFiles . $this->schemaName;
		if (file_exists($file)) {
			$schemaFile = $this->schemaFiles . $this->schemaName;
			$response .= nl2br("Loading stored database schema: " . $schemaFile . "\n");
			$res = $this->schema->importStoredDatabaseSchema();
			$response .= ($res == 0) ? nl2br("Loaded stored database schema.\n") : nl2br("Failed: stored database schema not loaded\n");
		}

		return $response;
	}

	/**
	 * this dump our database
	 * 
	 */
	public function dumpBuild($option)
	{
		$response = "";
		$this->removeExistingSchema();
		$this->ensureSchemaBinExist();
		$res = $this->schema->schemaDump($option);
		if ($res == 0) {
			if ($option == "prune") {
				$this->prune();
			}
			$response .= nl2br("Database schema dumped successfully.\n");
		} else {
			$response .= nl2br("Failed: database schema failed to dump.\n");
		}

		return $response;
	}

	/**
	 * this will remove existimg schema directory
	 * 
	 */
	public function removeExistingSchema()
	{
		$this->rmdir_recursive($this->schemaFiles);
	}

	/**
	 * this will remove existimg schema directory
	 * 
	 */
	public function prune()
	{
		if (file_exists($this->migrationFiles)) {
			$this->rmdir_recursive($this->migrationFiles);
		}
	}

	/**
	 * this will delete files and directory recursively
	 * 
	 */
	public function rmdir_recursive($dir)
	{
		foreach (scandir($dir) as $file) {
			if ('.' === $file || '..' === $file)
				continue;
			if (is_dir("$dir/$file"))
				$this->rmdir_recursive("$dir/$file");
			else
				unlink("$dir/$file");
		}

		rmdir($dir);
	}

	/**
	 * We want to pull in the last batch of migrations that ran on the previous
	 * migration operation. We'll then reverse those migrations and run each
	 * of them "down" to reverse the last migration "operation" which ran.
	 * 
	 */
	public function runRollback()
	{
		$response = "";
		$response .= $this->ensureRepositoryExist();
		$migration_batch = $this->repository->getLastBatchNumber();
		$repository = $this->rollbackRepository();
		if (count($repository) > 0) {
			foreach ($repository as $file) {
				$migration_name = $this->removeDotPhp($file);
				$schema = $this->runDown($file);
				if ($schema != "") {
					$query_response = DB()->query($schema);
					if ($query_response) {

						// Once we have run a migrations file, we remove migration to repository.
						$this->repository->delete($migration_name, $migration_batch);
						$response .= nl2br("Rolled back: $migration_name\n");
					} else {

						$response .= nl2br("Failed: $migration_name\n");
						$response .= nl2br("-- $query_response[error]\n");
					}
				} else {

					$response .= nl2br("Empty: $migration_name\n");
				}
			}
		} else {

			$response .= nl2br("Nothing to rollback.\n");
		}

		return $response;
	}

	public function rollbackRepository()
	{
		$toRollback = [];
		$results = $this->repository->getRanBatch();
		if (count($results) > 0) {
			foreach ($results as $result) {
				$file = $result . ".php";
				if (file_exists($this->migrationFiles . $file)) {
					$toRollback[] = $file;
				}
			}
		}

		return $toRollback;
	}

	public function runDown($file)
	{
		$data = $this->requireMigrationFiles($file);
		$mode = $data['mode'];
		$schemas = "";
		if ($mode != null) {
			$schemas .= $this->repository->schemaScaffold($mode, $data['table'], $data['down'], $data['primary_key'], "DOWN");
		}

		return $schemas;
	}
}
