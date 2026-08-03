<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\EstimateController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DealController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\PettyCashController;

Route::get('login', [AuthController::class, 'showLogin'])->name('login');
Route::post('login', [AuthController::class, 'login'])->name('login.post');
Route::post('logout', [AuthController::class, 'logout'])->name('logout');

Route::get('maintenance', function () {
    if (\App\Models\Setting::get('maintenance_mode') != 1) {
        return redirect()->route('login');
    }
    return view('errors.maintenance');
})->name('maintenance');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/notifications/mark-as-read', function () {
        auth()->user()->unreadNotifications->markAsRead();
        return response()->json(['success' => true]);
    })->name('notifications.markAsRead');
    Route::get('/', function () {
        return redirect()->route('dashboard');
    });

    // Petty Cash Routes (Accessible to Staff, HOD, Management, Super Admin)
    Route::get('/petty-cash', [PettyCashController::class, 'index'])->name('petty-cash.index');
    Route::post('/petty-cash', [PettyCashController::class, 'store'])->name('petty-cash.store');
    Route::get('/petty-cash/{pettyCash}', [PettyCashController::class, 'show'])->name('petty-cash.show');
    Route::post('/petty-cash/{pettyCash}/hod-approve', [PettyCashController::class, 'hodApprove'])->name('petty-cash.hodApprove');
    Route::post('/petty-cash/{pettyCash}/hod-reject', [PettyCashController::class, 'hodReject'])->name('petty-cash.hodReject');
    Route::post('/petty-cash/{pettyCash}/admin-approve', [PettyCashController::class, 'adminApprove'])->name('petty-cash.adminApprove');
    Route::post('/petty-cash/{pettyCash}/admin-reject', [PettyCashController::class, 'adminReject'])->name('petty-cash.adminReject');
    Route::post('/petty-cash/{pettyCash}/settle', [PettyCashController::class, 'settleIOU'])->name('petty-cash.settle');
    Route::post('/petty-cash/{pettyCash}/reappeal', [PettyCashController::class, 'reappeal'])->name('petty-cash.reappeal');

    Route::middleware(['prevent.staff'])->group(function () {
        Route::get('/dashboard/export', [DashboardController::class, 'exportCsv'])->name('dashboard.export');
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export', [ReportController::class, 'exportCsv'])->name('reports.export');

        Route::resource('customers', CustomerController::class);

        // Deals
        Route::get('/deals', [DealController::class, 'index'])->name('deals.index');
        Route::post('/deals', [DealController::class, 'store'])->name('deals.store');
        Route::put('/deals/{deal}', [DealController::class, 'update'])->name('deals.update');
        Route::get('/deals/{deal}/delete', [DealController::class, 'destroy'])->name('deals.destroy.get');
        Route::delete('/deals/{deal}', [DealController::class, 'destroy'])->name('deals.destroy');
        Route::post('/deals/{deal}/stage', [DealController::class, 'updateStage'])->name('deals.updateStage');
        Route::post('/deals/{deal}/create-estimate', [DealController::class, 'createEstimate'])->name('deals.createEstimate');
        Route::post('/deals/{deal}/create-invoice', [DealController::class, 'createInvoice'])->name('deals.createInvoice');

        // Jobs
        Route::get('/jobs', [JobController::class, 'index'])->name('jobs.index');

        Route::post('estimates/{estimate}/accept', [EstimateController::class, 'markAsAccepted'])->name('estimates.accept');
        Route::post('estimates/{estimate}/reject', [EstimateController::class, 'markAsRejected'])->name('estimates.reject');
        Route::post('estimates/{estimate}/convert', [
            EstimateController::class,
            'convertToInvoice'
        ])->name('estimates.convert');
        Route::post('estimates/{estimate}/status', [EstimateController::class, 'updateStatus'])->name('estimates.updateStatus');
        Route::resource('estimates', EstimateController::class);

        Route::get('invoices/ready', [InvoiceController::class, 'ready'])->name('invoices.ready');
        Route::get('invoices/invoiced', [InvoiceController::class, 'invoiced'])->name('invoices.invoiced');
        Route::get('invoices/rejected', [InvoiceController::class, 'rejected'])->name('invoices.rejected');
        Route::get('invoices/proforma', [InvoiceController::class, 'proforma'])->name('invoices.proforma');
        Route::post('invoices/{invoice}/status', [InvoiceController::class, 'updateStatus'])->name('invoices.updateStatus');
        Route::post('invoices/{invoice}/duplicate', [InvoiceController::class, 'duplicate'])->name('invoices.duplicate');
        Route::resource('invoices', InvoiceController::class);
        Route::resource('temp-invoices', \App\Http\Controllers\TempInvoiceController::class)->only(['edit', 'update']);
        Route::post('temp-invoices/{tempInvoice}/revert', [InvoiceController::class, 'revertToPending'])->name('temp-invoices.revert');


        // Super Admin Only Routes
        Route::middleware(['role:Super Admin'])->group(function () {
            Route::get('activities', [ActivityController::class, 'index'])->name('activities.index');
            Route::get('users/download-sample', [UserController::class, 'downloadSample'])->name('users.download-sample');
            Route::post('users/import', [UserController::class, 'import'])->name('users.import');
            Route::resource('users', UserController::class);

            // Customer Requests
            Route::get('customers/requests/{request}', [CustomerController::class, 'reviewRequest'])->name('customers.requests.review');
            Route::post('customers/requests/{request}/approve', [CustomerController::class, 'approveRequest'])->name('customers.requests.approve');
            Route::post('customers/requests/{request}/reject', [CustomerController::class, 'rejectRequest'])->name('customers.requests.reject');

            // Settings
            Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
            Route::post('settings/general', [SettingController::class, 'updateGeneral'])->name('settings.updateGeneral');
            Route::post('settings/tax', [SettingController::class, 'updateTax'])->name('settings.updateTax');
            Route::post('settings/department-targets', [SettingController::class, 'updateDepartmentTargets'])->name('settings.updateDepartmentTargets');
            Route::post('settings/user-targets', [SettingController::class, 'updateUserTargets'])->name('settings.updateUserTargets');
            Route::post('settings/maintenance', [SettingController::class, 'updateMaintenance'])->name('settings.updateMaintenance');
            
            Route::post('settings/managers', [SettingController::class, 'storeManager'])->name('settings.storeManager');
            Route::get('settings/managers/{manager}/delete', [SettingController::class, 'destroyManager'])->name('settings.destroyManager.get');
            Route::delete('settings/managers/{manager}', [SettingController::class, 'destroyManager'])->name('settings.destroyManager');
            Route::put('settings/managers/{manager}', [SettingController::class, 'updateManager'])->name('settings.updateManager');
            Route::post('settings/terms', [SettingController::class, 'storeTerm'])->name('settings.storeTerm');
            Route::get('settings/terms/{term}/delete', [SettingController::class, 'destroyTerm'])->name('settings.destroyTerm.get');
            Route::delete('settings/terms/{term}', [SettingController::class, 'destroyTerm'])->name('settings.destroyTerm');
            Route::put('settings/terms/{term}', [SettingController::class, 'updateTerm'])->name('settings.updateTerm');
            Route::post('settings/currencies', [SettingController::class, 'storeCurrency'])->name('settings.storeCurrency');
            Route::get('settings/currencies/{currency}/delete', [SettingController::class, 'destroyCurrency'])->name('settings.destroyCurrency.get');
            Route::delete('settings/currencies/{currency}', [SettingController::class, 'destroyCurrency'])->name('settings.destroyCurrency');
            Route::put('settings/currencies/{currency}', [SettingController::class, 'updateCurrency'])->name('settings.updateCurrency');

            Route::post('settings/expense-categories', [SettingController::class, 'storeExpenseCategory'])->name('settings.storeExpenseCategory');
            Route::get('settings/expense-categories/{expenseCategory}/delete', [SettingController::class, 'destroyExpenseCategory'])->name('settings.destroyExpenseCategory.get');
            Route::delete('settings/expense-categories/{expenseCategory}', [SettingController::class, 'destroyExpenseCategory'])->name('settings.destroyExpenseCategory');
            Route::put('settings/expense-categories/{expenseCategory}', [SettingController::class, 'updateExpenseCategory'])->name('settings.updateExpenseCategory');
        });
    });
});