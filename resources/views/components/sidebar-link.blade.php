@props(['active' => false])

@php
$classes = ($active ?? false)
            ? 'flex items-center p-2 text-base text-gray-900 rounded-lg bg-gray-100 dark:bg-gray-700 group dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700'
            : 'flex items-center p-2 text-base text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
