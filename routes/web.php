<?php

use App\Http\Controllers\Admin\CreditController as AdminCreditController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\MessageLogController as AdminMessageLogController;
use App\Http\Controllers\Admin\OrganizationApiKeyController;
use App\Http\Controllers\Admin\OrganizationApiTestController;
use App\Http\Controllers\Admin\OrganizationController;
use App\Http\Controllers\Admin\OrganizationWhatsAppController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Org\ApiKeyController;
use App\Http\Controllers\Org\DashboardController;
use App\Http\Controllers\Org\MessageLogController;
use App\Http\Controllers\Org\RechargeController;
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

    Route::post('/organizations/{organization}/api-keys/regenerate', [OrganizationApiKeyController::class, 'regenerateBoth'])->name('organizations.api-keys.regenerate');
    Route::post('/organizations/{organization}/api-keys/regenerate-key', [OrganizationApiKeyController::class, 'regenerateKey'])->name('organizations.api-keys.regenerate-key');
    Route::post('/organizations/{organization}/api-keys/regenerate-secret', [OrganizationApiKeyController::class, 'regenerateSecret'])->name('organizations.api-keys.regenerate-secret');

    Route::get('/organizations/{organization}/whatsapp', [OrganizationWhatsAppController::class, 'show'])->name('organizations.whatsapp');
    Route::post('/organizations/{organization}/whatsapp/connect', [OrganizationWhatsAppController::class, 'connect'])->name('organizations.whatsapp.connect');
    Route::get('/organizations/{organization}/whatsapp/qr', [OrganizationWhatsAppController::class, 'qr'])->name('organizations.whatsapp.qr');
    Route::get('/organizations/{organization}/whatsapp/status', [OrganizationWhatsAppController::class, 'status'])->name('organizations.whatsapp.status');
    Route::post('/organizations/{organization}/whatsapp/disconnect', [OrganizationWhatsAppController::class, 'disconnect'])->name('organizations.whatsapp.disconnect');

    Route::get('/organizations/{organization}/api-test', [OrganizationApiTestController::class, 'show'])->name('organizations.api-test');
    Route::post('/organizations/{organization}/api-test', [OrganizationApiTestController::class, 'send'])->name('organizations.api-test.send');

    Route::get('/credits', [AdminCreditController::class, 'index'])->name('credits.index');
    Route::post('/credits/{organization}', [AdminCreditController::class, 'store'])->name('credits.store');
    Route::get('/logs', [AdminMessageLogController::class, 'index'])->name('logs.index');
});

Route::middleware(['auth', EnsureOrganizationAdmin::class])->name('org.')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/whatsapp', [WhatsAppController::class, 'index'])->name('whatsapp.index');
    Route::post('/whatsapp/connect', [WhatsAppController::class, 'connect'])->name('whatsapp.connect');
    Route::get('/whatsapp/qr', [WhatsAppController::class, 'qr'])->name('whatsapp.qr');
    Route::get('/whatsapp/status', [WhatsAppController::class, 'status'])->name('whatsapp.status');
    Route::post('/whatsapp/disconnect', [WhatsAppController::class, 'disconnect'])->name('whatsapp.disconnect');

    Route::get('/recharge', [RechargeController::class, 'index'])->name('recharge.index');

    Route::get('/api-keys', [ApiKeyController::class, 'index'])->name('api-keys.index');
    Route::post('/api-keys/regenerate', [ApiKeyController::class, 'regenerate'])->name('api-keys.regenerate');
    Route::get('/logs', [MessageLogController::class, 'index'])->name('logs.index');
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings/password', [SettingsController::class, 'updatePassword'])->name('settings.password');

    Route::redirect('/dashboard/whatsapp', '/whatsapp');
    Route::redirect('/dashboard/credits', '/recharge');
    Route::redirect('/dashboard/api-keys', '/api-keys');
    Route::redirect('/dashboard/logs', '/logs');
    Route::redirect('/dashboard/settings', '/settings');
});
