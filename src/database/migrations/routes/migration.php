<?php

// routes for database migration
$router->get('/migration', ['MigrationController@index']);
$router->post('/migrate-run', ['MigrationController@run']);
