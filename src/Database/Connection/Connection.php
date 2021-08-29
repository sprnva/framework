<?php

namespace App\Core\Database\Connection;

use PDO;
use PDOException;
use App\Core\Database\Connection\Exception\ConnectionException;

class Connection implements ConnectionInterface
{
	/**
	 * Create a new PDO connection.
	 *
	 * @param array $config
	 */
	public static function make($config)
	{
		try {
			return new PDO(
				'mysql:host=' . $config['connection'] . ';dbname=' . $config['name'],
				$config['username'],
				$config['password'],
				$config['options']
			);
		} catch (PDOException $expection) {
			throw new ConnectionException($expection->getMessage(), $expection);
		}
	}
}
