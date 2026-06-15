@props(['status'])

@php
    $flashMap = [
        'profile-updated'        => 'Perfil actualizado correctamente.',
        'password-updated'       => 'Contraseña actualizada correctamente.',
        'verification-link-sent' => 'Te hemos enviado un nuevo enlace de verificación al correo.',
    ];
    $flashText = $status ? ($flashMap[$status] ?? $status) : null;
@endphp

@if ($flashText)
    <div {{ $attributes->merge(['class' => 'font-medium text-sm text-green-600']) }}>
        {{ $flashText }}
    </div>
@endif
