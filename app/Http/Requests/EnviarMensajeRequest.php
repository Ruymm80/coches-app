<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** Reglas mínimas para enviar un mensaje dentro de una conversación. */
class EnviarMensajeRequest extends FormRequest
{
    /** Solo los usuarios autenticados pueden enviar mensajes. */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'min:1', 'max:2000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'body' => 'mensaje',
        ];
    }
}
