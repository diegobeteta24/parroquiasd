<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

class StoreIntentionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in($this->allowedTypes())],
            'category' => ['nullable', Rule::in(['acciones_de_gracia', 'peticiones', 'difuntos'])],
            'public_text' => ['nullable', 'string', 'max:2000'],
            'donor_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'payment_method' => ['required', Rule::in(['cash', 'transfer', 'card'])],
            'receipt' => ['nullable', 'image', 'max:4096'],
            'dedicatee' => ['nullable', 'string', 'max:255'],
            'is_prepaid' => ['sometimes', 'boolean'],
            'stipend_amount_gtq' => ['nullable', 'numeric', 'min:0'],
            'payment_ref' => ['nullable', 'string', 'max:255'],
            'novena' => ['nullable', 'boolean'],
            'times' => ['nullable', 'integer', 'min:1', 'max:'.$this->maxTimes()],
            'extra_mass_ids' => ['nullable', 'array'],
            'extra_mass_ids.*' => ['integer', 'distinct'],
        ];
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            'is_prepaid' => true,
            'novena' => $this->boolean('novena'),
        ]);

        if ($this->boolean('novena') && !$this->filled('times')) {
            $this->merge(['times' => 9]);
        }

        if (!$this->boolean('novena')) {
            $this->merge([
                'times' => 1,
                'extra_mass_ids' => [],
            ]);
        }
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (!$this->requiresReceipt()) {
                return;
            }

            if (!$this->hasFile('receipt')) {
                $validator->errors()->add('receipt', 'Debes adjuntar la boleta para transferencias o tarjeta (si no está prepagada).');
            }
        });
    }

    public function requiresReceipt(): bool
    {
        return !$this->boolean('is_prepaid') && in_array($this->input('payment_method'), ['transfer', 'card'], true);
    }

    public function times(): int
    {
        $times = (int) ($this->input('times') ?? 1);
        return max(1, min($this->maxTimes(), $times));
    }

    public function extraMassIds(): Collection
    {
        return collect($this->input('extra_mass_ids', []))
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();
    }

    protected function allowedTypes(): array
    {
        $mass = $this->route('mass');

        if ($mass && $mass->is_special && $mass->special_category === 'rosario') {
            return ['rosario'];
        }

        return ['rezada', 'cantada'];
    }

    protected function maxTimes(): int
    {
        return (int) config('portal.intentions_max_repetitions', 1000);
    }
}
