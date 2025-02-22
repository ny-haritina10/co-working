<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\EspaceController;
use App\Http\Controllers\OptionController;
use App\Http\Controllers\PaiementController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\DatabaseResetController;
use App\Http\Controllers\StatistiqueController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\ClientAuthController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// auth
Route::post('/admin/login', [AdminAuthController::class, 'login']);
Route::post('/client/auth', [ClientAuthController::class, 'loginOrSignUp']);

// client 
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/client/logout', [ClientAuthController::class, 'logout']);
    Route::get('/client/me', [ClientAuthController::class, 'getAuthenticatedClient']);
});

// guarded back-office routes
Route::middleware(['auth:sanctum'])->prefix('back-office')->group(function () {
    Route::post('/espaces/import', [EspaceController::class, 'importCsv']);
    Route::post('/options/import', [OptionController::class, 'importCsv']);
    Route::post('/reservations/import', [ReservationController::class, 'importCsv']);
    
    Route::post('/paiements/import', [PaiementController::class, 'importCsv']);
    Route::put('/paiements/{id}/validate', [PaiementController::class, 'validatePaiement']);

    Route::delete('/reset-database', [DatabaseResetController::class, 'reset']);

    Route::get('/statistics/periodic-revenue', [StatistiqueController::class, 'getRevenueByPeriod']);
    Route::get('/statistics/total-revenue', [StatistiqueController::class, 'getTotalRevenue']);
    Route::get('/statistics/top-time-slots', [StatistiqueController::class, 'getTopTimeSlots']);

    Route::post('/admin/logout', [AdminAuthController::class, 'logout']);
});