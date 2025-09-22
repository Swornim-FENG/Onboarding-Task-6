<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DataLineageController;


Route::get('/', function () {
    return view('welcome');
});

Route::get('/lineage-viewer', [DataLineageController::class, 'index'])->name('lineage.viewer');

Route::get('/encryption/public-key', function () {
    return response()->file(storage_path('app/public/public.pem'));
});

