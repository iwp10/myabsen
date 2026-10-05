<?php

namespace App\Http\Requests\Admin;

use App\Services\ArsipService;
use Illuminate\Foundation\Http\FormRequest;

class ArsipRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'jenis' => ['nullable', 'string', 'in:'.implode(',', ArsipService::WHITELIST)],
            'search' => ['nullable', 'string', 'max:255'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('jenis') && ! in_array($this->query('jenis'), ArsipService::WHITELIST, true)) {
            abort(404);
        }
    }
}
