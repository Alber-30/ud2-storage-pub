<?php

use App\Http\Controllers\JsonController;

Route::get('json', [JsonController::class, 'index']);
Route::post('json', [JsonController::class, 'store']);
Route::get('json/{filename}', [JsonController::class, 'show']);
Route::put('json/{filename}', [JsonController::class, 'update']);
Route::delete('json/{filename}', [JsonController::class, 'destroy']);

