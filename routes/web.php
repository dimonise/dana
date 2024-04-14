<?php

use App\Http\Controllers\ProfileController;
use \App\Http\Controllers\DetailsController;
use \App\Http\Controllers\SelectCarController;
use \App\Http\Controllers\CategoryController;
use \App\Http\Controllers\OrdersController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DetailsController::class, 'index']
)->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/get-details', [DetailsController::class, 'get_details']);
    Route::get('/orders-all', [OrdersController::class, 'index'])->name('orders-all');
    Route::post('/search-detail', [DetailsController::class, 'search_details']);
    Route::post('/search-search-detail', [DetailsController::class, 'search_search_details']);
    Route::get('/new-order', [SelectCarController::class, 'index'])->name('new-order');
    Route::post('/select-auto-mark', [SelectCarController::class, 'getMark']);
    Route::post('/select-auto-model', [SelectCarController::class, 'getModel']);
    Route::post('/select-auto-body', [SelectCarController::class, 'getBody']);
    Route::post('/select-auto-engine', [SelectCarController::class, 'getEngine']);
    Route::post('/select-auto-modification', [SelectCarController::class, 'getModification']);
    Route::post('/select-auto-cats', [SelectCarController::class, 'getCategories']);
    Route::post('/select-auto-subcats', [SelectCarController::class, 'getSubCategories']);
    Route::post('/select-auto-subsubcats', [SelectCarController::class, 'getSubSubCategories']);
    Route::post('/get-cities', [SelectCarController::class, 'getList']);
    Route::post('/get-posts', [SelectCarController::class, 'getPosts']);
    Route::get('/feel-category', [CategoryController::class, 'index']);
    Route::post('/add-to-cart', [OrdersController::class, 'newOrder']);
    Route::post('/add-to-order', [OrdersController::class, 'addOrder']);
});

require __DIR__.'/auth.php';
