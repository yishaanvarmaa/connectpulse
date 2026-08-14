<?php

namespace App\View\Composers;

use App\Services\CreditService;
use App\Services\MessageService;
use Illuminate\View\View;

class OrgShellComposer
{
    public function __construct(
        private CreditService $creditService,
        private MessageService $messageService,
    ) {}

    public function compose(View $view): void
    {
        $user = auth()->user();

        if (! $user?->isOrganizationAdmin() || ! $user->organization) {
            return;
        }

        $organization = $user->organization->loadMissing('whatsappConnection', 'creditWallet');
        $stats = $this->messageService->getDashboardStats($organization);

        $view->with([
            'shellOrganization' => $organization,
            'shellBalance' => $this->creditService->getBalance($organization),
            'shellWhatsAppConnected' => ($stats['connection_status'] ?? '') === 'Connected',
            'shellWhatsAppPhone' => $stats['connected_number'] ?? null,
        ]);
    }
}
