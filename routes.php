<?php

use Illuminate\Support\Facades\Route;
use Pterodactyl\BlueprintFramework\Extensions\{identifier}\AddonController;

Route::prefix('/server/{server}')->group(function () {
    Route::get('/', [AddonController::class, 'index']);
    Route::post('/', [AddonController::class, 'store']);
    Route::put('/{uuid}', [AddonController::class, 'update']);
    Route::delete('/{uuid}', [AddonController::class, 'destroy']);
    Route::get('/search', [AddonController::class, 'search']);
});
