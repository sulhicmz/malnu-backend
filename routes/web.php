<?php

declare(strict_types=1);

use App\Http\Controllers\admin\HomeController;
use Hypervel\Support\Facades\Route;

Route::get('/', [HomeController::class, 'indexView']);

// CSP Report endpoint
Route::post('/csp-report', function () {
    // Log the CSP violation for analysis
    $cspReport = request()->getContent();
    
    // In production, you might want to log this to a file or monitoring service
    if (!empty($cspReport)) {
        $reportData = json_decode($cspReport, true);
        if ($reportData && isset($reportData['csp-report'])) {
            // Log the violation details
            \Hypervel\Support\Facades\Log::warning('CSP Violation', $reportData['csp-report']);
        }
    }
    
    return response('CSP Report Received', 204);
})->name('csp.report');