<?php

use App\Core\Routing\Route;
// routes for database migration
Route::get('/migration', ['MigrationController@index']);
Route::post('/migrate-run', ['MigrationController@run']);
