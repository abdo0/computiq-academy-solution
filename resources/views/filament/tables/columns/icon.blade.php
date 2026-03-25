@php
    $icon = $getState();
@endphp

@if (filled($icon))
    <span class="flex items-center justify-center w-6 h-6" style="color: #555;">
        <i class="fas fa-{{ $icon }}"></i>
    </span>
@else
    {{-- Optional: display a placeholder or nothing if no icon is set --}}
    <span class="flex items-center justify-center w-6 h-6 text-gray-400">
        -
    </span>
@endif 