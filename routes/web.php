<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::middleware(['verify.shopify'])->group(function() {

    Route::get('/settings', [\App\Http\Controllers\HomeController::class, 'settings']);
    Route::get('/', [\App\Http\Controllers\HomeController::class, 'keys'])->name('home');

    Route::post('/save-keys', [\App\Http\Controllers\HomeController::class, 'saveKeys'])->name('save-keys');
    Route::post('/save-settings', [\App\Http\Controllers\HomeController::class, 'saveSettings'])->name('save-settings');
    Route::post('/refresh', [\App\Http\Controllers\HomeController::class, 'refreshFiles']);

});

Route::get('/send_product', [\App\Http\Controllers\EventsController::class, 'sendProduct'])->name('send_product');
Route::get('/save_order', [\App\Http\Controllers\EventsController::class, 'saveOrder'])->name('save_order');
