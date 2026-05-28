<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TermooController;

Route::post('/novo-jogo', [TermooController::class, 'novoJogo']);

Route::post('/testar-palavra', [TermooController::class, 'testarPalavra']);