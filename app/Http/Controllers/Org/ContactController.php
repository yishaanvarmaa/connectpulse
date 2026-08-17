<?php

namespace App\Http\Controllers\Org;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\ContactList;
use App\Models\ContactTag;
use App\Services\ContactService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function __construct(
        private ContactService $contactService,
    ) {}

    public function index(Request $request): View
    {
        $organization = $request->user()->organization;

        $query = Contact::forOrganization($organization)->with('tags');

        if ($search = $request->string('search')->trim()->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($tagId = $request->integer('tag')) {
            $query->whereHas('tags', fn ($q) => $q->where('contact_tags.id', $tagId));
        }

        $contacts = $query->orderByDesc('created_at')->paginate(30);
        $tags = ContactTag::forOrganization($organization)->orderBy('name')->get();

        return view('org.contacts.index', compact('contacts', 'tags'));
    }

    public function store(Request $request): RedirectResponse
    {
        $organization = $request->user()->organization;

        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'tags' => ['nullable', 'string'],
        ]);

        if (! $this->contactService->isValidPhone($validated['phone'])) {
            return back()->withInput()->with('error', 'Invalid phone number.');
        }

        $contact = $this->contactService->upsertContact($organization, [
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'],
        ]);

        if (! empty($validated['tags'])) {
            $tagNames = array_filter(array_map('trim', explode(',', $validated['tags'])));
            $this->contactService->syncTags($contact, $tagNames);
        }

        return back()->with('success', 'Contact saved.');
    }

    public function import(Request $request): RedirectResponse
    {
        $organization = $request->user()->organization;

        $validated = $request->validate([
            'import_type' => ['required', 'in:csv,paste'],
            'csv_file' => ['nullable', 'file', 'mimes:csv,txt', 'max:2048'],
            'bulk_phones' => ['nullable', 'string'],
        ]);

        if ($validated['import_type'] === 'csv' && $request->hasFile('csv_file')) {
            $content = file_get_contents($request->file('csv_file')->getRealPath());
            $result = $this->contactService->importFromCsv($organization, $content ?: '');

            return back()->with('success', "Imported {$result['imported']} contact(s), skipped {$result['skipped']}.");
        }

        if ($validated['import_type'] === 'paste' && ! empty($validated['bulk_phones'])) {
            $result = $this->contactService->importFromBulkPaste($organization, $validated['bulk_phones']);

            return back()->with('success', "Imported {$result['imported']} contact(s), skipped {$result['skipped']}.");
        }

        return back()->with('error', 'No import data provided.');
    }

    public function storeList(Request $request): RedirectResponse
    {
        $organization = $request->user()->organization;

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'contact_ids' => ['nullable', 'array'],
            'contact_ids.*' => ['integer'],
        ]);

        $list = ContactList::create([
            'organization_id' => $organization->id,
            'name' => $validated['name'],
            'description' => $validated['description'],
        ]);

        if (! empty($validated['contact_ids'])) {
            $validIds = Contact::forOrganization($organization)
                ->whereIn('id', $validated['contact_ids'])
                ->pluck('id');
            $list->contacts()->sync($validIds);
        }

        return back()->with('success', 'Contact list created.');
    }
}
