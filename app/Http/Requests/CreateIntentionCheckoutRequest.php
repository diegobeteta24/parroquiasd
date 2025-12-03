<?php

namespace App\Http\Requests;

use App\Models\MassInstance;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class CreateIntentionCheckoutRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Endpoint público
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Datos de la intención
            'mass_instance_id' => ['required', 'integer', 'exists:mass_instances,id'],
            'intention_type' => ['required', 'string', 'in:normal,rezada,cantada,rosario'],
            'category' => ['nullable', Rule::in(['acciones_de_gracia', 'peticiones', 'difuntos'])],
            'public_text' => ['required', 'string', 'max:500', 'min:3'],
            'dedicatee_name' => ['nullable', 'string', 'max:255'],
            
            // Datos del donante
            'donor_name' => ['required', 'string', 'max:255', 'min:3'],
            'donor_email' => ['required', 'email', 'max:255'],
            'donor_phone' => ['nullable', 'string', 'max:20'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'mass_instance_id.required' => 'Por favor selecciona la misa donde se leerá la intención.',
            'mass_instance_id.exists' => 'La misa seleccionada no existe.',
            'intention_type.required' => 'El tipo de intención es requerido.',
            'intention_type.in' => 'El tipo de intención no es válido.',
            'public_text.required' => 'El texto de la intención es requerido.',
            'public_text.min' => 'El texto de la intención debe tener al menos 3 caracteres.',
            'public_text.max' => 'El texto de la intención no puede exceder 500 caracteres.',
            'dedicatee_name.max' => 'El nombre del dedicatario no puede exceder 255 caracteres.',
            'donor_name.required' => 'El nombre del donante es requerido.',
            'donor_name.min' => 'El nombre del donante debe tener al menos 3 caracteres.',
            'donor_email.required' => 'El email del donante es requerido.',
            'donor_email.email' => 'El email del donante debe ser válido.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'mass_instance_id' => 'misa',
            'intention_type' => 'tipo de intención',
            'public_text' => 'intención',
            'dedicatee_name' => 'dedicatario',
            'donor_name' => 'nombre',
            'donor_email' => 'correo electrónico',
            'donor_phone' => 'teléfono',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $massId = (int) $this->input('mass_instance_id');
            $today = now()->timezone(config('app.timezone'));

            if (!$massId) {
                return;
            }

            $mass = MassInstance::withCount('intentions')->find($massId);

            if (!$mass) {
                return;
            }

            if ($mass->status !== 'scheduled' || $mass->starts_at->isPast()) {
                $validator->errors()->add('mass_instance_id', 'La misa seleccionada ya no está disponible.');
                return;
            }

            if ($mass->starts_at->isSameDay($today)) {
                $validator->errors()->add('mass_instance_id', 'No es posible solicitar intenciones para misas del mismo día.');
                return;
            }

            $intentionType = (string) $this->input('intention_type');
            $normalizedType = match ($intentionType) {
                'rosario' => 'rosario',
                'cantada' => 'cantada',
                'rezada', 'normal' => 'normal',
                default => null,
            };

            if ($normalizedType === null) {
                $validator->errors()->add('intention_type', 'El tipo de intención seleccionado no es válido.');
                return;
            }

            $isRosaryMass = $mass->is_special && $mass->special_category === 'rosario';

            if ($normalizedType === 'rosario' && !$isRosaryMass) {
                $validator->errors()->add('mass_instance_id', 'Para intenciones de Rosario debes seleccionar una misa del Rosario.');
            }

            if ($normalizedType !== 'rosario' && $isRosaryMass) {
                $validator->errors()->add('mass_instance_id', 'Las misas de Rosario solo aceptan intenciones de Rosario.');
            }

            if (!$mass->is_special && $mass->capacity !== null && $mass->intentions_count >= $mass->capacity) {
                $validator->errors()->add('mass_instance_id', 'Esta misa ya no tiene cupo disponible.');
            }
        });
    }
}
