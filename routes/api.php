<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DifferenceController;


Route::post('/compare/save', [DifferenceController::class, 'saveFile']);

Route::get('/compare/git', [DifferenceController::class, 'compareFilesGit']);

Route::get('/compare/simple', [DifferenceController::class, 'compareFilesSimple']);

Route::get('/compare/lcs', [DifferenceController::class, 'compareFileLcs']);
