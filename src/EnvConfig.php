<?php

return [
	'database' => [
		'name' => $config["name"],
		'username' => $config["username"],
		'password' => $config["password"],
		'connection' => $config["connection"],
		'options' => [
			PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING
		]
	],

	'app' => [
		'base_url' => $config["base_url"],
		'name' => $config["app_name"],

		// choices: windows, macOS, linux
		'os' => $config["os"],

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
