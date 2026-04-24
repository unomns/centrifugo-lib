<?php

use Illuminate\Support\Facades\Route;
use Unomns\Centrifugo\Http\Controllers\CentrifugoProxyController;

/*
 * Centrifugo proxy routes.
 *
 * Requests are validated via HMAC signature by default (X-Centrifugo-Hmac-Sha256).
 * Set CENTRIFUGO_VERIFY_PROXY_SIGNATURE=false in local dev to skip validation.
 *
 * Publish this file to customise prefix, middleware, or domain:
 *   php artisan vendor:publish --tag=centrifugo-routes
 *
 * Once published you own this file — the package's auto-registration is
 * skipped automatically.
 */

Route::prefix('centrifugo')->middleware('centrifugo.signature')->group(function (): void {
    Route::post('/connect',     [CentrifugoProxyController::class, 'connect']);
    Route::post('/subscribe',   [CentrifugoProxyController::class, 'subscribe']);
    Route::post('/publish',     [CentrifugoProxyController::class, 'publish']);
    Route::post('/rpc',         [CentrifugoProxyController::class, 'rpc']);
    Route::post('/refresh',     [CentrifugoProxyController::class, 'refresh']);
    Route::post('/sub-refresh', [CentrifugoProxyController::class, 'subRefresh']);
});
