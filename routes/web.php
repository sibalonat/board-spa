<?php

use App\Http\Controllers\CardsController;
use App\Http\Controllers\ColumnController;
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
    return view('welcome');
});

// API CALLS
// here i left some of the parent properties just as demostration of relations between models. In most cases, in other projects i would avoid using unused properties in routes

Route::get('/column', [ColumnController::class, 'index'])->name('getcolumnwithcards');
Route::post('/column', [ColumnController::class, 'store'])->name('storecolumnname');
Route::delete('/column/{column}', [ColumnController::class, 'destroy'])->name('deletecolumn');
Route::post('/column/{column}/cards', [CardsController::class, 'store'])->name('storecard');
Route::get('/column/{column}/cards/{card}', [CardsController::class, 'show'])->name('getcard');
Route::put('/column/{column}/cards/{card}', [CardsController::class, 'update'])->name('updatecard');
Route::put('/cards/{card}', [CardsController::class, 'changeColumnParentId'])->name('updatecardparentid');

Route::get('/export', function () {
    Spatie\DbDumper\Databases\MySql::create()
        ->setDbName('board_spa')
        ->setUserName('root')
        ->setPassword('')
        ->dumpToFile('dump.sql');
});
