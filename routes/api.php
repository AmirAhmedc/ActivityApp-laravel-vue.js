<?php

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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

// Route::resource('images',App\Http\Controllers\ImageController::class)->only(['uploadImages','store','show','update','destroy']);

// routes/api.php

use App\Http\Controllers\ImageController;


Route::get('/download-image', [ImageController::class, 'downloadImage']);
Route::post('/upload-images', [ImageController::class, 'uploadImages']);