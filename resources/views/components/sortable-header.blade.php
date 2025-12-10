@props(['field', 'label', 'currentSort' => null, 'currentDirection' => 'desc'])

@php
    $isSorted = $currentSort === $field;
    $nextDirection = $isSorted && $currentDirection === 'asc' ? 'desc' : 'asc';
    $url = request()->fullUrlWithQuery(['sort' => $field, 'direction' => $nextDirection]);
@endphp

<th {{ $attributes->merge(['class' => 'px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors']) }}>
    <a href="{{ $url }}" class="flex items-center space-x-1 group">
        <span>{{ $label }}</span>
        <span class="flex flex-col">
            @if($isSorted)
                @if($currentDirection === 'asc')
                    <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M5 10l5-5 5 5H5z"/>
                    </svg>
                @else
                    <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M15 10l-5 5-5-5h10z"/>
                    </svg>
                @endif
            @else
                <svg class="w-4 h-4 text-gray-400 dark:text-gray-600 opacity-0 group-hover:opacity-100 transition-opacity" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M5 10l5-5 5 5H5z"/>
                </svg>
            @endif
        </span>
    </a>
</th>
