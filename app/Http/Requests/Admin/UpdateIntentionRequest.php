<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

class UpdateIntentionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $intention = $this->route('intention');
        $mass = $intention?->mass;
        $allowedTypes = ($mass && $mass->is_special && $mass->special_category === 'rosario')
            ? ['rosario']
            : ['rezada', 'cantada'];

        return [
            'type' => ['required', Rule::in($allowedTypes)],
            'category' => ['nullable', Rule::in(['acciones_de_gracia', 'peticiones', 'difuntos'])],
            'public_text' => ['nullable', 'string', 'max:2000'],
            'donor_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'payment_method' => ['required', Rule::in(['cash', 'transfer', 'card'])],
            'dedicatee' => ['nullable', 'string', 'max:255'],
            'justification' => ['required', 'string', 'min:6'],
            'is_prepaid' => ['sometimes', 'boolean'],
            'stipend_amount_gtq' => ['nullable', 'numeric', 'min:0'],
            'payment_ref' => ['nullable', 'string', 'max:255'],
            'novena' => ['nullable', 'boolean'],
            'times' => ['nullable', 'integer', 'min:1', 'max:'.$this->maxTimes()],
            'extra_mass_ids' => ['nullable', 'array'],
            'extra_mass_ids.*' => ['integer', 'distinct'],
            'group_editor' => ['nullable', 'boolean'],
            'group_mass_ids' => ['nullable', 'array'],
            'group_mass_ids.*' => ['integer', 'distinct'],
            'move_to_mass_id' => ['nullable', 'integer'],
            'receipt' => ['nullable', 'image', 'max:4096'],
        ];
    }

    public function prepareForValidation(): void
    {
        $intention = $this->route('intention');
        $forcePrepaid = $intention?->channel !== 'online';

        $this->merge([
            'is_prepaid' => $forcePrepaid ? true : $this->boolean('is_prepaid'),
            'novena' => $this->boolean('novena'),
            'group_editor' => $this->boolean('group_editor'),
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
        if ($this->boolean('is_prepaid')) {
            return false;
        }

        if (!in_array($this->input('payment_method'), ['transfer', 'card'], true)) {
            return false;
        }

        $intention = $this->route('intention');

        return $intention && $intention->receipt ? false : true;
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

    public function groupMassIds(): Collection
    {
        return collect($this->input('group_mass_ids', []))
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();
    }

    protected function maxTimes(): int
    {
        return (int) config('portal.intentions_max_repetitions', 1000);
    }
}
