<?php

use \Illuminate\Support\Facades\Route;
use \Neon\Http\Controllers\LinkController;

Route::fallback([LinkController::class, 'link']);