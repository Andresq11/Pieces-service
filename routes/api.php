<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\BlockController;
use App\Http\Controllers\PieceController;
use App\Http\Controllers\ReportController;

Route::middleware('validate.token')->group(function () {

    #Piezas
    Route::get('/v1/pieces', [PieceController::class, 'all']);
    Route::get('/v1/blocks/{block_id}/pieces', [PieceController::class, 'index']);
    Route::post('/v1/blocks/{block_id}/pieces', [PieceController::class, 'store']);
    Route::get('/v1/blocks/{block_id}/pieces/{id}', [PieceController::class, 'show']);
    Route::put('/v1/blocks/{block_id}/pieces/{id}', [PieceController::class, 'update']);
    Route::delete('/v1/blocks/{block_id}/pieces/{id}', [PieceController::class, 'destroy']);

    #Reportes
    Route::get('/v1/reports/pending-by-project', [ReportController::class, 'pendientesPorProyecto']);
    Route::get('/v1/reports/totals-by-status', [ReportController::class, 'totalesPorEstado']);

    #Proyectos
    Route::get('/v1/projects', [ProjectController::class, 'index']);
    Route::post('/v1/projects', [ProjectController::class, 'store']);
    Route::get('/v1/projects/{id}', [ProjectController::class, 'show']);
    Route::put('/v1/projects/{id}', [ProjectController::class, 'update']);
    Route::delete('/v1/projects/{id}', [ProjectController::class, 'destroy']);

    #Bloques
    Route::get('/v1/projects/{project_id}/blocks', [BlockController::class, 'index']);
    Route::post('/v1/projects/{project_id}/blocks', [BlockController::class, 'store']);
    Route::get('/v1/projects/{project_id}/blocks/{id}', [BlockController::class, 'show']);
    Route::put('/v1/projects/{project_id}/blocks/{id}', [BlockController::class, 'update']);
    Route::delete('/v1/projects/{project_id}/blocks/{id}', [BlockController::class, 'destroy']);

    #Piezas
    Route::get('/v1/blocks/{block_id}/pieces', [PieceController::class, 'index']);
    Route::post('/v1/blocks/{block_id}/pieces', [PieceController::class, 'store']);
    Route::get('/v1/blocks/{block_id}/pieces/{id}', [PieceController::class, 'show']);
    Route::put('/v1/blocks/{block_id}/pieces/{id}', [PieceController::class, 'update']);
    Route::delete('/v1/blocks/{block_id}/pieces/{id}', [PieceController::class, 'destroy']);

});