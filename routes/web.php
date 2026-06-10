<?php

use App\Http\Controllers\Admin\CreditController as AdminCreditController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\MessageLogController as AdminMessageLogController;
use App\Http\Controllers\Admin\OrganizationController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Org\ApiKeyController;
use App\Http\Controllers\Org\CreditController;
use App\Http\Controllers\Org\DashboardController;
use App\Http\Controllers\Org\MessageLogController;
use App\Http\Controllers\Org\SettingsController;
use App\Http\Controllers\Org\WhatsAppController;
use App\Http\Middleware\EnsureOrganizationAdmin;
use App\Http\Middleware\EnsureSuperAdmin;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('login'));

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::prefix('admin')->middleware(['auth', EnsureSuperAdmin::class])->name('admin.')->group(function () {
    Route::get('/dashboard', AdminDashboardController::class)->name('dashboard');
    Route::resource('organizations', OrganizationController::class)->except(['edit', 'update']);
    Route::post('/organizations/{organization}/suspend', [OrganizationController::class, 'suspend'])->name('organizations.suspend');
    Route::post('/organizations/{organization}/activate', [OrganizationController::class, 'activate'])->name('organizations.activate');
    Route::get('/credits', [AdminCreditController::class, 'index'])->name('credits.index');
    Route::post('/credits/{organization}', [AdminCreditController::class, 'store'])->name('credits.store');
    Route::get('/logs', [AdminMessageLogController::class, 'index'])->name('logs.index');
});

Route::prefix('dashboard')->middleware(['auth', EnsureOrganizationAdmin::class])->name('org.')->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::get('/whatsapp', [WhatsAppController::class, 'index'])->name('whatsapp.index');
    Route::post('/whatsapp/connect', [WhatsAppController::class, 'connect'])->name('whatsapp.connect');
    Route::get('/whatsapp/qr', [WhatsAppController::class, 'qr'])->name('whatsapp.qr');
    Route::get('/whatsapp/status', [WhatsAppController::class, 'status'])->name('whatsapp.status');
    Route::post('/whatsapp/disconnect', [WhatsAppController::class, 'disconnect'])->name('whatsapp.disconnect');
    Route::get('/api-keys', [ApiKeyController::class, 'index'])->name('api-keys.index');
    Route::post('/api-keys/regenerate', [ApiKeyController::class, 'regenerate'])->name('api-keys.regenerate');
    Route::get('/credits', [CreditController::class, 'index'])->name('credits.index');
    Route::get('/logs', [MessageLogController::class, 'index'])->name('logs.index');
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings/password', [SettingsController::class, 'updatePassword'])->name('settings.password');
});
