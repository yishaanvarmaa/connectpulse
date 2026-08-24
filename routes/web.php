<?php

use App\Http\Controllers\Web\MarketingController;
use App\Http\Controllers\Admin\CreditController as AdminCreditController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\MessageLogController as AdminMessageLogController;
use App\Http\Controllers\Admin\OrganizationApiKeyController;
use App\Http\Controllers\Admin\OrganizationApiTestController;
use App\Http\Controllers\Admin\OrganizationController;
use App\Http\Controllers\Admin\OrganizationWhatsAppController;
use App\Http\Controllers\Api\RazorpayPaymentController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Org\ApiKeyController;
use App\Http\Controllers\Org\Crm\DashboardController as CrmDashboardController;
use App\Http\Controllers\Org\Crm\FollowUpController as CrmFollowUpController;
use App\Http\Controllers\Org\Crm\LeadController as CrmLeadController;
use App\Http\Controllers\Org\Crm\PipelineController as CrmPipelineController;
use App\Http\Controllers\Org\Crm\ReportController as CrmReportController;
use App\Http\Controllers\Org\CampaignController;
use App\Http\Controllers\Org\ContactController;
use App\Http\Controllers\Org\DashboardController;
use App\Http\Controllers\Org\InboxController;
use App\Http\Controllers\Org\MessageLogController;
use App\Http\Controllers\Org\SearchController;
use App\Http\Controllers\Org\RechargeController;
use App\Http\Controllers\Org\SettingsController;
use App\Http\Controllers\Webhooks\RazorpayWebhookController;
use App\Http\Controllers\Org\WhatsAppController;
use App\Http\Middleware\EnsureOrganizationAdmin;
use App\Http\Middleware\EnsureSuperAdmin;
use Illuminate\Support\Facades\Route;

Route::get('/', [MarketingController::class, 'home'])->name('home');
Route::get('/pricing', [MarketingController::class, 'pricing'])->name('pricing');
Route::get('/contact', [MarketingController::class, 'contact'])->name('contact');
Route::get('/privacy-policy', [MarketingController::class, 'privacy'])->name('privacy');
Route::get('/terms', [MarketingController::class, 'terms'])->name('terms');
Route::get('/refund-policy', [MarketingController::class, 'refund'])->name('refund');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', [RegisterController::class, 'show'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->middleware('throttle:registration');
});

Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::post('/webhooks/razorpay', RazorpayWebhookController::class)->name('webhooks.razorpay');

Route::prefix('admin')->middleware(['auth', EnsureSuperAdmin::class])->name('admin.')->group(function () {
    Route::get('/dashboard', AdminDashboardController::class)->name('dashboard');
    Route::resource('organizations', OrganizationController::class)->except(['edit']);
    Route::post('/organizations/{organization}/suspend', [OrganizationController::class, 'suspend'])->name('organizations.suspend');
    Route::post('/organizations/{organization}/activate', [OrganizationController::class, 'activate'])->name('organizations.activate');
    Route::post('/credits/{organization}/set', [AdminCreditController::class, 'setBalance'])->name('credits.set');

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
    Route::get('/search', SearchController::class)->name('search');
    Route::view('/more', 'org.more.index')->name('more');

    Route::get('/inbox', [InboxController::class, 'index'])->name('inbox.index');
    Route::redirect('/logs', '/inbox')->name('logs.index');

    Route::get('/whatsapp', [WhatsAppController::class, 'index'])->name('whatsapp.index');
    Route::post('/whatsapp/connect', [WhatsAppController::class, 'connect'])->name('whatsapp.connect');
    Route::get('/whatsapp/qr', [WhatsAppController::class, 'qr'])->name('whatsapp.qr');
    Route::get('/whatsapp/status', [WhatsAppController::class, 'status'])->name('whatsapp.status');
    Route::post('/whatsapp/disconnect', [WhatsAppController::class, 'disconnect'])->name('whatsapp.disconnect');

    Route::get('/recharge', [RechargeController::class, 'index'])->name('recharge.index');
    Route::post('/api/create-order', [RazorpayPaymentController::class, 'createOrder'])->name('api.create-order');
    Route::post('/api/verify-payment', [RazorpayPaymentController::class, 'verifyPayment'])->name('api.verify-payment');

    Route::get('/api-keys', [ApiKeyController::class, 'index'])->name('api-keys.index');
    Route::post('/api-keys/regenerate', [ApiKeyController::class, 'regenerate'])->name('api-keys.regenerate');

    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings/password', [SettingsController::class, 'updatePassword'])->name('settings.password');

    Route::prefix('campaigns')->name('campaigns.')->group(function () {
        Route::get('/', [CampaignController::class, 'index'])->name('index');
        Route::get('/create', [CampaignController::class, 'create'])->name('create');
        Route::post('/', [CampaignController::class, 'store'])->name('store');

        Route::prefix('{campaign}')->whereNumber('campaign')->group(function () {
            Route::get('/', [CampaignController::class, 'show'])->name('show');
            Route::get('/status', [CampaignController::class, 'status'])->name('status');
            Route::post('/pause', [CampaignController::class, 'pause'])->name('pause');
            Route::post('/resume', [CampaignController::class, 'resume'])->name('resume');
            Route::post('/cancel', [CampaignController::class, 'cancel'])->name('cancel');
            Route::delete('/', [CampaignController::class, 'destroy'])->name('destroy');
            Route::post('/retry', [CampaignController::class, 'retry'])->name('retry');
            Route::post('/test', [CampaignController::class, 'test'])->name('test');
            Route::post('/confirm-test', [CampaignController::class, 'confirmTest'])->name('confirm-test');
            Route::post('/launch', [CampaignController::class, 'launch'])->name('launch');
            Route::post('/kick', [CampaignController::class, 'kick'])->name('kick');
        });
    });

    Route::prefix('contacts')->name('contacts.')->group(function () {
        Route::get('/', [ContactController::class, 'index'])->name('index');
        Route::post('/', [ContactController::class, 'store'])->name('store');
        Route::post('/import', [ContactController::class, 'import'])->name('import');
        Route::post('/lists', [ContactController::class, 'storeList'])->name('lists.store');
    });

    Route::redirect('/dashboard/whatsapp', '/whatsapp');
    Route::redirect('/dashboard/credits', '/recharge');
    Route::redirect('/dashboard/api-keys', '/api-keys');
    Route::redirect('/dashboard/logs', '/logs');
    Route::redirect('/dashboard/settings', '/settings');

    Route::prefix('crm')->name('crm.')->group(function () {
        Route::redirect('/', '/dashboard')->name('dashboard');
        Route::get('/leads', [CrmLeadController::class, 'index'])->name('leads.index');
        Route::get('/leads/create', [CrmLeadController::class, 'create'])->name('leads.create');
        Route::post('/leads', [CrmLeadController::class, 'store'])->name('leads.store');
        Route::get('/leads/{lead}', [CrmLeadController::class, 'show'])->name('leads.show');
        Route::get('/leads/{lead}/edit', [CrmLeadController::class, 'edit'])->name('leads.edit');
        Route::put('/leads/{lead}', [CrmLeadController::class, 'update'])->name('leads.update');
        Route::post('/leads/{lead}/status', [CrmLeadController::class, 'updateStatus'])->name('leads.status');
        Route::post('/leads/{lead}/notes', [CrmLeadController::class, 'addNote'])->name('leads.notes');
        Route::post('/leads/{lead}/whatsapp', [CrmLeadController::class, 'sendWhatsApp'])->name('leads.whatsapp');
        Route::post('/leads/{lead}/follow-ups', [CrmLeadController::class, 'storeFollowUp'])->name('leads.follow-ups.store');
        Route::post('/leads/{lead}/log-interaction', [CrmLeadController::class, 'logInteraction'])->name('leads.log-interaction');
        Route::post('/leads/{lead}/temperature', [CrmLeadController::class, 'updateTemperature'])->name('leads.temperature');
        Route::post('/leads/bulk-status', [CrmLeadController::class, 'bulkStatus'])->name('leads.bulk-status');

        Route::get('/pipeline', [CrmPipelineController::class, 'index'])->name('pipeline.index');
        Route::post('/pipeline/{lead}/status', [CrmPipelineController::class, 'updateStatus'])->name('pipeline.status');

        Route::get('/follow-ups', [CrmFollowUpController::class, 'index'])->name('follow-ups.index');
        Route::post('/follow-ups/{followUp}/complete', [CrmFollowUpController::class, 'complete'])->name('follow-ups.complete');
        Route::post('/follow-ups/{followUp}/reschedule', [CrmFollowUpController::class, 'reschedule'])->name('follow-ups.reschedule');
        Route::post('/follow-ups/{followUp}/cancel', [CrmFollowUpController::class, 'cancel'])->name('follow-ups.cancel');
        Route::post('/follow-ups/{followUp}/whatsapp', [CrmFollowUpController::class, 'sendWhatsApp'])->name('follow-ups.whatsapp');

        Route::get('/reports', [CrmReportController::class, 'index'])->name('reports.index');
    });
});
