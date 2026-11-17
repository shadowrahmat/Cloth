<?php

use Illuminate\Support\Facades\Route;

// SPA Fallback Route - Catches all non-API and non-storage routes and serves the Vue application
Route::get('/{any}', function () {
    return view('app');
})->where('any', '^(?!api|storage).*$');

// Root fallback
Route::get('/', function () {
    return view('app');
});
