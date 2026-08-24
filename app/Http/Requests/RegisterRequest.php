<?php

namespace App\Http\Requests;

use App\Models\Organization;
use App\Models\User;
use App\Rules\NotDisposableEmail;
use App\Support\EmailAddress;
use App\Support\PhoneNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('email')) {
            $this->merge([
                'email' => strtolower(trim((string) $this->input('email'))),
            ]);
        }

        if ($this->has('mobile')) {
            $this->merge([
                'mobile' => PhoneNumber::national((string) $this->input('mobile')),
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'company_name' => ['required', 'string', 'max:255'],
            'contact_person' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email:rfc', 'max:255', new NotDisposableEmail],
            'mobile' => ['required', 'string', 'regex:/^[6-9]\d{9}$/'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'website' => ['nullable', 'string', 'max:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'mobile.regex' => 'Enter a valid 10-digit Indian mobile number.',
            'website.max' => 'Unable to complete registration.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $email = (string) $this->input('email', '');
            $mobile = (string) $this->input('mobile', '');

            if ($email !== '') {
                $normalized = EmailAddress::normalize($email);

                $emailTaken = Organization::query()
                    ->where('email', $email)
                    ->orWhere('normalized_email', $normalized)
                    ->exists()
                    || User::query()
                        ->where('email', $email)
                        ->orWhere('email', $normalized)
                        ->exists();

                if ($emailTaken) {
                    $validator->errors()->add('email', 'This email is already registered.');
                }
            }

            if ($mobile !== '' && Organization::query()->where('mobile', $mobile)->exists()) {
                $validator->errors()->add('mobile', 'This mobile number is already registered.');
            }
        });
    }
}
