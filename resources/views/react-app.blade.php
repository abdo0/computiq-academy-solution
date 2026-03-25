<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ in_array(app()->getLocale(), ['ar', 'ku']) ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    @php
        $seo = $seo ?? [];
        $locale = app()->getLocale();
        
        // Get site name - handle JSON string or array
        $siteNameRaw = settings('company_name');
        
        // Check if it's a JSON string and decode it
        if (is_string($siteNameRaw) && !empty($siteNameRaw)) {
            $decoded = json_decode($siteNameRaw, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $siteNameRaw = $decoded;
            }
        }
        
        // Extract site name based on locale
        if (is_array($siteNameRaw) && !empty($siteNameRaw)) {
            $siteName = $siteNameRaw[$locale] ?? $siteNameRaw['ar'] ?? 'مؤسسة نخوة';
        } elseif (is_string($siteNameRaw) && !empty($siteNameRaw)) {
            $siteName = $siteNameRaw;
        } else {
            $siteName = 'مؤسسة نخوة';
        }
        
        // Final check: ensure siteName is always a string
        $siteName = (string) $siteName;
        
        $title = $seo['title'] ?? settings('seo_title_'.$locale) ?? settings('seo_title_ar') ?? __('Home');
        $description = $seo['description'] ?? settings('seo_description_'.$locale) ?? settings('seo_description_ar') ?? __('A trusted charitable donation platform');
        $keywords = $seo['keywords'] ?? settings('seo_keywords_'.$locale) ?? settings('seo_keywords_ar') ?? __('education,learning,computiq');
        $ogImage = $seo['og_image'] ?? settings('seo_og_image') ?? settings('company_logo');
        if ($ogImage && !filter_var($ogImage, FILTER_VALIDATE_URL)) {
            $ogImage = asset('storage/'.$ogImage);
        }
        
        // Ensure all values are strings (not arrays or objects)
        $title = is_string($title) ? $title : (string) $title;
        $description = is_string($description) ? $description : (string) $description;
        $keywords = is_string($keywords) ? $keywords : (string) $keywords;
        $ogImage = is_string($ogImage) ? $ogImage : null;
        
    @endphp
    
    <title>{{ $title }}</title>
    <meta name="description" content="{{ $description }}">
    <meta name="keywords" content="{{ $keywords }}">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $title }}">
    <meta property="og:description" content="{{ $description }}">
    @if($ogImage)
    <meta property="og:image" content="{{ $ogImage }}">
    @endif
    <meta property="og:url" content="{{ url()->current() }}">
    
    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $title }}">
    <meta name="twitter:description" content="{{ $description }}">
    @if($ogImage)
    <meta name="twitter:image" content="{{ $ogImage }}">
    @endif
    
    @viteReactRefresh
    @vite(['resources/js/react/index.tsx'])
    
    <script>
        window.__locale = '{{ $locale }}';
        window.__translations = @json($translations ?? []);
    </script>
</head>
<body>
    @php
        $allSettings = settings();
        
        // Get logo URL - settings helper already converts storage paths to asset URLs
        $logoUrl = settings('company_logo') ?: settings('logo_url') ?: settings('logo') ?: null;
        
        // Format settings to match AppSettings structure
        // Get currency info
        $currencyCode = 'USD';
        $currencySymbol = '$';
        try {
            if (Schema::hasTable('currencies')) {
                $defaultCurrency = \App\Models\Currency::getDefault();
                if ($defaultCurrency) {
                    $currencyCode = $defaultCurrency->code;
                    $currencySymbol = $defaultCurrency->symbol;
                }
            }
        } catch (\Exception $e) {
            // Fallback to settings
            $currencyCode = settings('currency', 'USD');
        }
        
        // Preload footer pages (for React footer)
        $footerPages = [];
        $otherPages = [];
        try {
            if (Schema::hasTable('pages')) {
                $footerPages = \App\Models\Page::query()
                    ->where('is_published', true)
                    ->where('show_in_footer', true)
                    ->orderBy('sort_order')
                    ->get(['slug'])
                    ->pluck('slug')
                    ->values()
                    ->all();

                $otherPages = \App\Models\Page::query()
                    ->where('is_published', true)
                    ->where('show_in_header', false)
                    ->where('show_in_footer', false)
                    ->orderBy('sort_order')
                    ->get(['slug', 'title'])
                    ->map(function ($page) {
                        return [
                            'slug' => $page->slug,
                            'title' => $page->title,
                        ];
                    })
                    ->values()
                    ->all();
            }
        } catch (\Exception $e) {
            $footerPages = [];
            $otherPages = [];
        }
        
        $formattedSettings = [
            'logoUrl'      => $logoUrl,
            'siteName'     => $allSettings['site_name_'.$locale] ?? $allSettings['site_name_ar'] ?? null,
            'currency' => [
                'code'   => $currencyCode,
                'symbol' => $currencySymbol,
            ],
            'contactEmail'  => $allSettings['contact_email'] ?? null,
            'contactPhone'  => array_filter([
                $allSettings['contact_phone_1'] ?? $allSettings['contact_phone'] ?? null,
                $allSettings['contact_phone_2'] ?? $allSettings['contact_phone_secondary'] ?? null,
            ]),
            'address'       => $allSettings['address_'.$locale] ?? $allSettings['address_ar'] ?? null,
            'socialLinks' => [
                'twitter'   => $allSettings['social_twitter']   ?? null,
                'facebook'  => $allSettings['social_facebook']  ?? null,
                'instagram' => $allSettings['social_instagram'] ?? null,
                'youtube'   => $allSettings['social_youtube']   ?? null,
            ],
            'footerDesc'    => $allSettings['footer_desc_'.$locale] ?? $allSettings['footer_desc_ar'] ?? null,
            'defaultSeo' => [
                'title'       => $allSettings['seo_title_'.$locale] ?? $allSettings['seo_title_ar'] ?? null,
                'description' => $allSettings['seo_description_'.$locale] ?? $allSettings['seo_description_ar'] ?? null,
                'keywords'    => $allSettings['seo_keywords_'.$locale] ?? $allSettings['seo_keywords_ar'] ?? null,
                'ogImage'     => $allSettings['seo_og_image'] ?? null,
            ],
            'footerPages' => $footerPages,
            'otherPages'  => $otherPages,
            'heroContent' => Cache::remember('hero_content_'.$locale, 3600, fn() => [
                'title'    => $allSettings['hero_title_'.$locale]    ?? $allSettings['hero_title_ar'] ?? null,
                'subtitle' => $allSettings['hero_subtitle_'.$locale] ?? $allSettings['hero_subtitle_ar'] ?? null,
                'perks'    => $allSettings['hero_perks_'.$locale]    ?? $allSettings['hero_perks_ar'] ?? null,
                'cta_text' => $allSettings['hero_cta_text_'.$locale] ?? $allSettings['hero_cta_text_ar'] ?? null,
                'background_image' => $allSettings['hero_background_image'] ?? null,
            ]),
        ];
        
        // Load organization with verification relationship if authenticated
        $organization = null;
        if (Auth::guard('organization')->check()) {
            $org = Auth::guard('organization')->user();
            $org->loadMissing('verification');
            $organization = (new \App\Http\Resources\OrganizationResource($org))->toArray(request());
        }
        
        // homeData is no longer pre-fetched to allow skeletons to show
        $homeData = null;

        $initialData = [
            'donor' => Auth::guard('donor')->check() 
                ? (new \App\Http\Resources\DonorResource(Auth::guard('donor')->user()))->toArray(request()) 
                : null,
            'organization' => $organization,
            'settings' => $formattedSettings,
            // 'homeData' is purposely omitted or left null to force API fetch
        ];
    @endphp
    <div 
        id="root"
        data-initial='{!! json_encode($initialData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}'
    ></div>
</body>
</html>
