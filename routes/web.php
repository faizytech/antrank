<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectSheetController;
use Illuminate\Support\Facades\Mail;
use App\Mail\DailyReports;

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
    return view('welcome');
});
Route::get('/projects', [ProjectController::class, 'index']);
Route::get('/projects/{projectId}/keyphrases', [ProjectController::class, 'getKeyPh']);
Route::get('/projects/{projectId}/rankings', [ProjectController::class, 'getRanking']);
Route::get('/test', function(){
    Mail::to('faizytech.php@gmail.com')->send(new DailyReports("https://www.google.com"));
});