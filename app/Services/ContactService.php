<?php

namespace App\Services;

use App\Models\Contact;
use App\Models\ContactTag;
use App\Models\Organization;
use Illuminate\Support\Collection;

class ContactService
{
    public function __construct(
        private MessageService $messageService,
    ) {}

    public function normalizePhone(?string $phone): string
    {
        return $this->messageService->normalizeMobile((string) ($phone ?? ''));
    }

    public function isValidPhone(?string $phone): bool
    {
        if ($phone === null || trim($phone) === '') {
            return false;
        }

        $normalized = $this->normalizePhone($phone);

        return strlen($normalized) >= 10 && strlen($normalized) <= 15;
    }

    public function upsertContact(Organization $organization, array $data): Contact
    {
        $phone = $this->normalizePhone($data['phone'] ?? '');

        return Contact::updateOrCreate(
            [
                'organization_id' => $organization->id,
                'phone' => $phone,
            ],
            [
                'name' => $data['name'] ?? null,
                'email' => $data['email'] ?? null,
                'source' => $data['source'] ?? Contact::SOURCE_MANUAL,
                'opt_in_status' => $data['opt_in_status'] ?? Contact::OPT_IN_UNKNOWN,
            ]
        );
    }

    /**
     * @return array{imported: int, skipped: int, errors: array<int, string>}
     */
    public function importFromCsv(Organization $organization, string $csvContent): array
    {
        $lines = preg_split('/\r\n|\r|\n/', trim($csvContent)) ?: [];
        $imported = 0;
        $skipped = 0;
        $errors = [];

        foreach ($lines as $index => $line) {
            $line = trim($line);
            if ($line === '' || ($index === 0 && $this->looksLikeHeader($line))) {
                continue;
            }

            $parts = str_getcsv($line);
            $name = trim($parts[0] ?? '') ?: null;
            $phone = trim($parts[1] ?? $parts[0] ?? '');
            $email = trim($parts[2] ?? '') ?: null;

            if ($phone === '') {
                $skipped++;
                $errors[$index + 1] = 'Missing phone number';

                continue;
            }

            if (! $this->isValidPhone($phone)) {
                $skipped++;
                $errors[$index + 1] = "Invalid phone: {$phone}";

                continue;
            }

            $this->upsertContact($organization, [
                'name' => $name,
                'phone' => $phone,
                'email' => $email,
                'source' => Contact::SOURCE_CSV,
            ]);

            $imported++;
        }

        return compact('imported', 'skipped', 'errors');
    }

    /**
     * @return array{imported: int, skipped: int}
     */
    public function importFromBulkPaste(Organization $organization, string $text): array
    {
        $lines = preg_split('/\r\n|\r|\n/', trim($text)) ?: [];
        $imported = 0;
        $skipped = 0;

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            $parts = preg_split('/[,\t|]/', $line, 2);
            $first = trim($parts[0] ?? '');
            $second = trim($parts[1] ?? '');

            if (preg_match('/^[0-9+\-\s()]+$/', $first)) {
                $phone = $first;
                $name = $second ?: null;
            } else {
                $name = $first;
                $phone = $second;
            }

            if (! $this->isValidPhone($phone)) {
                $skipped++;

                continue;
            }

            $this->upsertContact($organization, [
                'name' => $name,
                'phone' => $phone,
                'source' => Contact::SOURCE_IMPORT,
            ]);

            $imported++;
        }

        return compact('imported', 'skipped');
    }

    public function syncTags(Contact $contact, array $tagNames): void
    {
        $organization = $contact->organization;
        $tagIds = [];

        foreach ($tagNames as $tagName) {
            $tagName = trim($tagName);
            if ($tagName === '') {
                continue;
            }

            $tag = ContactTag::firstOrCreate([
                'organization_id' => $organization->id,
                'name' => $tagName,
            ]);

            $tagIds[] = $tag->id;
        }

        $contact->tags()->sync($tagIds);
    }

    /**
     * @return Collection<int, array{phone: string, name: ?string, contact_id: ?int, lead_id: ?int}>
     */
    public function resolveAudience(Organization $organization, string $audienceType, ?array $config = []): Collection
    {
        $config ??= [];

        $recipients = match ($audienceType) {
            CampaignAudienceResolver::TYPE_ALL_CONTACTS => $this->mapContacts(
                Contact::forOrganization($organization)
                    ->whereNotNull('phone')
                    ->where('phone', '!=', '')
            ),
            CampaignAudienceResolver::TYPE_CONTACT_LIST => $this->fromContactList($organization, $config),
            CampaignAudienceResolver::TYPE_TAGS => $this->fromTags($organization, $config),
            CampaignAudienceResolver::TYPE_LEADS => $this->mapLeads(
                $organization->leads()
                    ->whereNotNull('phone')
                    ->where('phone', '!=', '')
            ),
            CampaignAudienceResolver::TYPE_MANUAL => $this->fromManual($organization, $config),
            CampaignAudienceResolver::TYPE_CSV => $this->fromCsvConfig($organization, $config),
            default => collect(),
        };

        return $recipients
            ->filter(fn ($r) => is_array($r) && $this->isValidPhone($r['phone'] ?? null))
            ->unique('phone')
            ->values();
    }

    private function recipientFromContact(Contact $contact): ?array
    {
        $phone = $contact->normalizedPhone();
        if (! $this->isValidPhone($phone)) {
            return null;
        }

        return [
            'phone' => $phone,
            'name' => $contact->name,
            'contact_id' => $contact->id,
            'lead_id' => null,
        ];
    }

    private function mapContacts($query): Collection
    {
        $recipients = collect();

        $query->select(['id', 'name', 'phone'])
            ->orderBy('id')
            ->chunkById(500, function ($chunk) use (&$recipients) {
                foreach ($chunk as $contact) {
                    $row = $this->recipientFromContact($contact);
                    if ($row) {
                        $recipients->push($row);
                    }
                }
            });

        return $recipients;
    }

    private function mapLeads($query): Collection
    {
        $recipients = collect();

        $query->select(['id', 'name', 'phone'])
            ->orderBy('id')
            ->chunkById(500, function ($chunk) use (&$recipients) {
                foreach ($chunk as $lead) {
                    $phone = $lead->normalizedPhone();
                    if (! $this->isValidPhone($phone)) {
                        continue;
                    }

                    $recipients->push([
                        'phone' => $phone,
                        'name' => $lead->name,
                        'contact_id' => null,
                        'lead_id' => $lead->id,
                    ]);
                }
            });

        return $recipients;
    }

    private function fromContactList(Organization $organization, array $config): Collection
    {
        $listId = $config['contact_list_id'] ?? null;
        if (! $listId) {
            return collect();
        }

        return $this->mapContacts(
            Contact::forOrganization($organization)
                ->whereHas('lists', fn ($q) => $q->where('contact_lists.id', $listId))
                ->whereNotNull('phone')
                ->where('phone', '!=', '')
        );
    }

    private function fromTags(Organization $organization, array $config): Collection
    {
        $tagIds = $config['tag_ids'] ?? [];
        if (empty($tagIds)) {
            return collect();
        }

        return $this->mapContacts(
            Contact::forOrganization($organization)
                ->whereHas('tags', fn ($q) => $q->whereIn('contact_tags.id', $tagIds))
                ->whereNotNull('phone')
                ->where('phone', '!=', '')
        );
    }

    private function fromManual(Organization $organization, array $config): Collection
    {
        $contactIds = $config['contact_ids'] ?? [];
        $leadIds = $config['lead_ids'] ?? [];

        $fromContacts = empty($contactIds)
            ? collect()
            : $this->mapContacts(
                Contact::forOrganization($organization)
                    ->whereIn('id', $contactIds)
                    ->whereNotNull('phone')
                    ->where('phone', '!=', '')
            );

        $fromLeads = empty($leadIds)
            ? collect()
            : $this->mapLeads(
                $organization->leads()
                    ->whereIn('id', $leadIds)
                    ->whereNotNull('phone')
                    ->where('phone', '!=', '')
            );

        return $fromContacts->merge($fromLeads);
    }

    private function fromCsvConfig(Organization $organization, array $config): Collection
    {
        $phones = $config['phones'] ?? [];

        return collect($phones)->map(function ($entry) use ($organization) {
            if (is_array($entry)) {
                return [
                    'phone' => $this->normalizePhone($entry['phone'] ?? ''),
                    'name' => $entry['name'] ?? null,
                    'contact_id' => null,
                    'lead_id' => null,
                ];
            }

            return [
                'phone' => $this->normalizePhone((string) $entry),
                'name' => null,
                'contact_id' => null,
                'lead_id' => null,
            ];
        });
    }

    private function looksLikeHeader(string $line): bool
    {
        $lower = strtolower($line);

        return str_contains($lower, 'phone') || str_contains($lower, 'name') || str_contains($lower, 'email');
    }
}
