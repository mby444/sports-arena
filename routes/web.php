<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/cek-driver', function () {
    return response()->json([
        'php_ini' => php_ini_loaded_file(),
        'pdo_drivers' => PDO::getAvailableDrivers(),
    ]);
});