@props(['disabled' => false])

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'border-gray-300 focus:border-[#1c9262] focus:ring-[#1c9262] rounded-md shadow-sm']) !!}>
