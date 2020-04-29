<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/', function () {
    // \Illuminate\Support\Facades\Cache::store('redis')->put('Laradock', 'is awesome', 10);
    return view('welcome');
});

Route::get('/incoming-updates', 'TelegramBotController@getIncomingUpdates');
