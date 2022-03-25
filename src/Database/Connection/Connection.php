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
		$driver = isset($config['driver']) ? $config['driver'] : 'mysql';
		$host = isset($config['host']) ? $config['host'] : 'localhost';
		$port = isset($config['port'])
			? $config['port']
			: (strstr($config['host'], ':') ? explode(':', $config['host'])[1] : '');
		$charset = 'utf8';
		$collation = isset($config['collation']) ? $config['collation'] : 'utf8mb4_general_ci';

		$dsn = '';
		if (in_array($driver, ['', 'mysql', 'pgsql'])) {
			$dsn = $driver . ':host=' . str_replace(':' . $port, '', $host) . ';' . ($port !== '' ? 'port=' . $port . ';' : '') . 'dbname=' . $config['database'];
		} elseif ($driver === 'sqlite') {
			$dsn = 'sqlite:' . $config['database'];
		} elseif ($driver === 'oracle') {
			$dsn = 'oci:dbname=' . $host . '/' . $config['database'];
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
