<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Database\Migration\Migration;
use App\Core\App;

class MigrationController
{
	protected $pageTitle;

	public function index()
	{
		if (App::get('config')['app']['environment'] != "development") {
			redirect('/home');
		}

		$pageTitle = "Migration";

		return packageView('framework/src/database/migrations/views/index', compact('pageTitle'));
	}

	public function run()
	{
		if (App::get('config')['app']['environment'] != "development") {
			redirect('/home');
		}

		$request = Request::validate('/migration');

		$migration = new Migration();
		$commandType = $request["command"];
		$migrationName = $request["migrationName"];

		switch ($commandType) {
			case 'migration:make':
				echo $migration->make($migrationName);
				break;

			case 'migration:migrate':
				echo $migration->migrate();
				break;

			case 'migration:fresh':
				echo $migration->fresh();
				break;

			case 'schema:dump':
				echo $migration->dump();
				break;

			case 'schema:dump-prune':
				echo $migration->dump('prune');
				break;

			case 'migration:rollback':
				echo $migration->rollback();
				break;

			default:
				break;
		}
	}
}
