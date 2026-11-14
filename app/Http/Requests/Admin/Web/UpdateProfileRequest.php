<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Web;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $merged = [];

        if ($this->has('email')) {
            $merged['email'] = strtolower(trim((string) $this->input('email')));
        }

        if ($this->has('interests_input')) {
            $raw = (string) $this->input('interests_input', '');
            $merged['interests'] = $raw === ''
                ? []
                : array_values(array_filter(array_map('trim', explode(',', $raw))));
        }

        if ($merged !== []) {
            $this->merge($merged);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $userId = (string) $this->user()?->_id;

        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class, 'email')->ignore($userId, '_id'),
            ],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'interests' => ['sometimes', 'array', 'max:20'],
            'interests.*' => ['string', 'max:50'],
        ];
    }
}
