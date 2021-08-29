<?php

$create_password_resets_table = [
	"mode" => "NEW",
	"table"	=> "password_resets",
	"primary_key" => "email",
	"up" => [
		"email" => "VARCHAR(255) NOT NULL",
		"token" => "VARCHAR(255) NOT NULL",
		"created_at" => "TIMESTAMP NULL DEFAULT NULL",
	],
	"down" => [
		"" => ""
	]
];