<?php

use App\Http\Controllers\EventController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::get('/info', [EventController::class, 'index']);
Route::get('/event/{id}', [EventController::class, 'show']);
Route::get('/user/{login}', [EventController::class, 'user']);
Route::post('/create', [EventController::class, 'create']);
Route::post('/remove', [EventController::class, 'removeParticipate']);
Route::post('/add', [EventController::class, 'addParticipate']);
