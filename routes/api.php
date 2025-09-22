<?php

use App\Http\Controllers\DataLineageController;
use Illuminate\Support\Facades\Route;

// Secure API route with Sanctum
Route::middleware('auth:sanctum')->post('/lineage/lookup', [DataLineageController::class, 'lookup']);

