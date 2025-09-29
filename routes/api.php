<?php

use App\Http\Controllers\Api\Reports\JobBookingsController;
use App\Http\Controllers\Api\Reports\ConversionFunnelController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'throttle:60,1'])->prefix('reports')->group(function () {
    // Job Bookings Report
    Route::get('/job-bookings', [JobBookingsController::class, 'index'])->name('api.reports.job-bookings');
    Route::get('/job-bookings/export', [JobBookingsController::class, 'export'])->name('api.reports.job-bookings.export');
    
    // Conversion Funnel Report
    Route::get('/conversion-funnel', [ConversionFunnelController::class, 'index'])->name('api.reports.conversion-funnel');
    Route::get('/conversion-funnel/export', [ConversionFunnelController::class, 'export'])->name('api.reports.conversion-funnel.export');
});