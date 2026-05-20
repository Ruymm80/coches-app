<?php

namespace App\Http\Requests;

use App\Enums\BodyType;
use App\Enums\FuelType;
use App\Enums\ListingStatus;
use App\Enums\Transmission;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateListingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('listing')) ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:160'],
            'brand' => ['required', 'string', 'max:60'],
            'model' => ['required', 'string', 'max:60'],
            'description' => ['required', 'string', 'min:20', 'max:5000'],
            'price' => ['required', 'integer', 'min:1', 'max:1000000'],
            'year' => ['required', 'integer', 'min:1950', 'max:'.(date('Y') + 1)],
            'mileage_km' => ['required', 'integer', 'min:0', 'max:2000000'],
            'fuel_type' => ['required', new Enum(FuelType::class)],
            'transmission' => ['required', new Enum(Transmission::class)],
            'body_type' => ['required', new Enum(BodyType::class)],
            'color' => ['nullable', 'string', 'max:40'],
            'province' => ['required', 'string', 'max:60'],
            'status' => ['required', new Enum(ListingStatus::class)],
            'images' => ['nullable', 'array', 'max:8'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'delete_images' => ['nullable', 'array'],
            'delete_images.*' => ['integer'],
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => 'título',
            'description' => 'descripción',
            'price' => 'precio',
            'year' => 'año',
            'mileage_km' => 'kilómetros',
            'fuel_type' => 'combustible',
            'transmission' => 'cambio',
            'body_type' => 'carrocería',
            'color' => 'color',
            'province' => 'provincia',
            'status' => 'estado',
            'images' => 'imágenes',
            'images.*' => 'imagen',
        ];
    }
}
