<?php

use \Illuminate\Support\Facades\Route;
use \Neon\Http\Controllers\LinkController;

/** Default routing for Neon.
 * 
 * Using Laravel's very nice fallback method to get page content from Neon.
 * 
 */
Route::fallback([LinkController::class, 'show'])
    ->middleware('web');
