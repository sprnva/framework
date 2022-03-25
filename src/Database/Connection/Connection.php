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
		$driver = $config['driver'];
		$host = $config['connection'];
		$port = $config['port'];
		$charset = $config['charset'];
		$collation = $config['collation'];
		$database = $config['name'];

		$dsn = '';
		if (in_array($driver, ['', 'mysql', 'pgsql'])) {
			$dsn = $driver . ':host=' . str_replace(':' . $port, '', $host) . ';' . ($port !== '' ? 'port=' . $port . ';' : '') . 'dbname=' . $database;
		} elseif ($driver === 'sqlite') {
			$dsn = 'sqlite:' . $database;
		} elseif ($driver === 'oracle') {
			$dsn = 'oci:dbname=' . $host . '/' . $database;
		}

		try {
			$conn = new PDO($dsn, $config['username'], $config['password'], isset($config['options']) ? $config['options'] : null);
			$conn->exec("SET NAMES '" . $charset . "' COLLATE '" . $collation . "'");
			$conn->exec("SET CHARACTER SET '" . $charset . "'");
			// $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
			return $conn;
		} catch (PDOException $expection) {
			throw new ConnectionException($expection->getMessage(), $expection);
		}
	}
}
