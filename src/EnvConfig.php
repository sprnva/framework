<?php

return [
	'database' => [

		# Database Driver Type (optional)
		# default value: mysql
		# values: mysql, pgsql, sqlite, oracle
		'driver' => isset($config['driver'])
			? $config['driver']
			: 'mysql',

		# Database Name (required)
		'name' => $config["database"],

		# Database User Name (required)
		'username' => $config["username"],

		# Database User Password (required)
		'password' => $config["password"],

		# Host name or IP Address (optional)
		# hostname:port (for Port Usage. Example: 127.0.0.1:1010)
		# default value: localhost
		'connection' => isset($config['host'])
			? $config['host']
			: 'localhost',

		# Connection options (Things like SSL certificates, etc)
		'options' => [
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
		],

		# IP Address for Database Host (optional)
		# default value: null
		'port' => isset($config['port'])
			? $config['port']
			: (strstr($config['host'], ':')
				? explode(':', $config['host'])[1]
				: ''),

		# Database Charset (optional)
		# default value: utf8
		'charset' => isset($config['charset'])
			? $config['charset']
			: 'utf8mb4',

		# Database Charset Collation (optional)
		# default value: utf8_general_ci
		'collation' => isset($config['collation'])
			? $config['collation']
			: 'utf8mb4_general_ci',

		# Database Prefix (optional)
		# default value: null
		'prefix' => isset($config['prefix'])
			? $config['prefix']
			: '',

		# Cache Directory of the Sql Result (optional)
		# default value: __DIR__ . '/cache/'
		'cachedir'	=> isset($config['cachedir'])
			? $config['cachedir']
			: __DIR__ . '/cache/',
	],

	'app' => [
		'base_url' => $config["base_url"],
		'name' => $config["app_name"],

		// for more flexible database migration please indicate 
		// the path of mysql in your machine including the trailing slashes.
		'mysql_path' => $config["mysql_path"],

		// choices: development, production
		'environment' => $config["environment"],

		// EMAIL
		'smtp_host' => $config["smtp_host"],
		'smtp_username' => $config["smtp_username"],
		'smtp_password' => $config["smtp_password"],
		'smtp_auth' => $config["smtp_auth"],
		'smtp_auto_tls' => $config["smtp_auto_tls"],
		'smtp_port' => $config["smtp_port"]
	]
];
