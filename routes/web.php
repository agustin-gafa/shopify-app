<?php

use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

Auth::routes();

Route::get('/', [App\Http\Controllers\HomeController::class, 'index']);
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::get('/rkn', [App\Http\Controllers\HomeController::class, 'cuadro']);

Route::get('/productos', [App\Http\Controllers\ShopifyController::class, 'getProducts']);

Route::get('/almacen', [App\Http\Controllers\HomeController::class, 'almacen']);



Route::post('/crear-producto', [App\Http\Controllers\CuadroController::class, 'crearProductoFront']);