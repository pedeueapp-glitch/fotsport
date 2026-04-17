<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:50', 'alpha_dash', Rule::unique(User::class)->ignore($this->user()->id)],
            'email' => ['email', 'max:255', Rule::unique(User::class)->ignore($this->user()->id)],
            'avatar' => ['nullable', 'image', 'max:2048'],
            'cover_photo' => ['nullable', 'image', 'max:4096'],
            'whatsapp' => ['nullable', 'string', 'max:20', 'regex:/^\(?\d{2}\)?[\s-]?\d{4,5}-?\d{4}$/'],
            'instagram' => ['nullable', 'string', 'max:50', 'regex:/^([A-Za-z0-9_](?:(?:[A-Za-z0-9_]|(?:\.(?!\.))){0,28}(?:[A-Za-z0-9_]))?)$/'], // Basic Instagram username validation
            'facebook' => ['nullable', 'string', 'max:255', 'url'], // Basic Facebook URL validation
            'bio' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
