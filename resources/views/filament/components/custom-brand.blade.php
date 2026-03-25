@php
    $displayMode = settings('logo_display_mode', 'logo_with_text');

    $logo = settings('company_logo');
    $collapsedLogo = settings('company_favicon') ?: settings('favicon');
    $appName = settings('app_name', config('app.name'));

    // Fallback: if logo_only is selected but no logo exists, show text
    $showLogo = ($displayMode === 'logo_only' || $displayMode === 'logo_with_text') && $logo;
    $showText = $displayMode === 'text_only' || $displayMode === 'logo_with_text' || ($displayMode === 'logo_only' && !$logo);
@endphp

<div class="flex items-center w-full">
    @if ($showLogo)
        <img src="{{ $logo }}" alt="{{ $appName }}" class="w-full h-8 me-3 hidden lg:!block">
    @endif
    
    @if ($showText)
        <span class="text-xl font-bold leading-5 tracking-tight filament-brand">
            {{ $appName }}
        </span>
    @endif
</div>