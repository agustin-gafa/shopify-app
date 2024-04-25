<?php

use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

// Auth::routes();

// Route::get('/', [App\Http\Controllers\HomeController::class, 'index']);
// Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Auth::routes(['register' => false]);

Route::get('/', [App\Http\Controllers\HomeController::class, 'cuadro']);
Route::get('/home', [App\Http\Controllers\HomeController::class, 'cuadro']);

Route::get('/productos', [App\Http\Controllers\ShopifyController::class, 'getProducts']);

Route::get('/almacen', [App\Http\Controllers\HomeController::class, 'almacen']);

Route::get('/rkn-config', [App\Http\Controllers\HomeController::class, 'configStore']);


Route::post('/crear-producto', [App\Http\Controllers\CuadroController::class, 'crearProductoFront']);
Route::post('/stock', [App\Http\Controllers\CuadroController::class, 'stockFront']);


// WEBHOOKS
Route::post('/shopify/webhook', [App\Http\Controllers\WebHooksController::class, 'orders']);