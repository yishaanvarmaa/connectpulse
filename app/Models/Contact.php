<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Contact extends Model
{
    public const SOURCE_MANUAL = 'manual';

    public const SOURCE_CSV = 'csv';

    public const SOURCE_IMPORT = 'import';

    public const SOURCE_LEAD = 'lead';

    public const OPT_IN_UNKNOWN = 'unknown';

    public const OPT_IN_YES = 'yes';

    public const OPT_IN_NO = 'no';

    protected $fillable = [
        'organization_id',
        'name',
        'phone',
        'email',
        'source',
        'opt_in_status',
        'last_contacted_at',
    ];

    protected function casts(): array
    {
        return [
            'last_contacted_at' => 'datetime',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(ContactTag::class, 'contact_contact_tag');
    }

    public function lists(): BelongsToMany
    {
        return $this->belongsToMany(ContactList::class, 'contact_list_contact');
    }

    public function scopeForOrganization(Builder $query, Organization $organization): Builder
    {
        return $query->where('organization_id', $organization->id);
    }

    public function normalizedPhone(): string
    {
        return preg_replace('/[^0-9]/', '', (string) ($this->phone ?? '')) ?: '';
    }
}
