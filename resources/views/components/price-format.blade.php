@props(['value'])

<span {{ $attributes }}>{{ number_format((int) $value, 0, ',', '.') }} €</span>
