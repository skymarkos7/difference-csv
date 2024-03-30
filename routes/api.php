<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DifferenceController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::post('/compare/save', [DifferenceController::class, 'saveFile']);

Route::get('/compare/git', [DifferenceController::class, 'compareFilesGit']);

Route::post('/compare', [DifferenceController::class, 'compareFiles']);

Route::post('/compare/sof', [DifferenceController::class, 'compareFilesSof']);

