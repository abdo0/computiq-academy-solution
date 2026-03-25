<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() == 'ar' || app()->getLocale() == 'ku' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', config('app.name')) - {{ settings('company_name', config('app.name')) }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @if(app()->getLocale() == 'ar' || app()->getLocale() == 'ku')
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Arabic:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @endif
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['{{ app()->getLocale() == "ar" || app()->getLocale() == "ku" ? "Noto Sans Arabic" : "Inter" }}', 'sans-serif'],
                        arabic: ['Noto Sans Arabic', 'Tajawal', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50: '#eff6ff',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                        }
                    }
                }
            }
        }
    </script>
    
    <!-- Additional Styles -->
    <style>
        body {
            font-family: '{{ app()->getLocale() == "ar" || app()->getLocale() == "ku" ? "Noto Sans Arabic" : "Inter" }}', 'Tajawal', sans-serif;
        }
        
        @if(app()->getLocale() == 'ar' || app()->getLocale() == 'ku')
        /* Enhanced Arabic/Kurdish typography */
        body, html {
            font-family: 'Noto Sans Arabic', 'Tajawal', sans-serif !important;
            font-feature-settings: "liga" 1, "kern" 1, "mark" 1, "mkmk" 1;
            text-rendering: optimizeLegibility;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Noto Sans Arabic', 'Tajawal', sans-serif !important;
            font-weight: 600;
            line-height: 1.4;
        }
        
        p, span, div, label, input, textarea, select {
            font-family: 'Noto Sans Arabic', 'Tajawal', sans-serif !important;
        }
        
        /* RTL improvements */
        [dir="rtl"] {
            text-align: right;
        }
        
        /* RTL Rating Stars */
        .rating-stars {
            flex-direction: row-reverse;
            justify-content: flex-end;
        }
        @else
        /* LTR Rating Stars */
        .rating-stars {
            flex-direction: row;
            justify-content: flex-start;
        }
        @endif
        
        /* Common Rating Stars Styles */
        .rating-stars {
            display: flex;
            gap: 0.25rem;
        }
        
        .rating-stars input[type="radio"] {
            display: none;
        }
        
        .rating-stars label {
            font-size: 2rem;
            color: #d1d5db;
            cursor: pointer;
            transition: color 0.2s;
        }
        
        .rating-stars label:hover,
        .rating-stars label:hover ~ label,
        .rating-stars input[type="radio"]:checked ~ label {
            color: #fbbf24;
        }
        
        @media (max-width: 640px) {
            .rating-stars label {
                font-size: 1.5rem;
            }
        }
    </style>
    
    @stack('styles')
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center">
                    @if(settings('logo'))
                        <img src="{{ settings('logo') }}" 
                             alt="{{ settings('app_name', config('app.name')) }}" 
                             class="h-10 w-auto object-contain me-1.5">
                    @endif
                    <h1 class="text-xl font-semibold text-gray-900">
                        {{ settings('app_name', config('app.name')) }}
                    </h1>
                </div>
                
                <!-- Language Switcher -->
                <div class="flex items-center space-x-4">
                    <div class="relative">
                        <select onchange="changeLanguage(this.value)" class="appearance-none bg-gray-100 border border-gray-300 rounded-md px-3 py-1 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-primary-500">
                            <option value="en" {{ app()->getLocale() == 'en' ? 'selected' : '' }}>English</option>
                            <option value="ar" {{ app()->getLocale() == 'ar' ? 'selected' : '' }}>العربية</option>
                            <option value="ku" {{ app()->getLocale() == 'ku' ? 'selected' : '' }}>کوردی</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 mt-16">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="text-center text-sm text-gray-600">
                <p>&copy; {{ date('Y') }} {{ settings('company_name', config('app.name')) }}. {{ __('All rights reserved.') }}</p>
                @if(settings('copyright_text'))
                    <p class="mt-2">{{ settings('copyright_text') }}</p>
                @endif
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script>
        function changeLanguage(locale) {
            const url = new URL(window.location);
            url.searchParams.set('Locale', locale);
            window.location.href = url.toString();
        }
        
        // Rating functionality
        document.addEventListener('DOMContentLoaded', function() {
            const ratingContainers = document.querySelectorAll('.rating-stars');
            
            ratingContainers.forEach(container => {
                const inputs = container.querySelectorAll('input[type="radio"]');
                const labels = container.querySelectorAll('label');
                
                labels.forEach((label, index) => {
                    label.addEventListener('mouseenter', () => {
                        labels.forEach((l, i) => {
                            if (i <= index) {
                                l.style.color = '#fbbf24';
                            } else {
                                l.style.color = '#d1d5db';
                            }
                        });
                    });
                    
                    label.addEventListener('click', () => {
                        inputs[index].checked = true;
                        labels.forEach((l, i) => {
                            if (i <= index) {
                                l.style.color = '#fbbf24';
                            } else {
                                l.style.color = '#d1d5db';
                            }
                        });
                    });
                });
                
                container.addEventListener('mouseleave', () => {
                    const checkedInput = container.querySelector('input[type="radio"]:checked');
                    if (checkedInput) {
                        const checkedIndex = Array.from(inputs).indexOf(checkedInput);
                        labels.forEach((l, i) => {
                            if (i <= checkedIndex) {
                                l.style.color = '#fbbf24';
                            } else {
                                l.style.color = '#d1d5db';
                            }
                        });
                    } else {
                        labels.forEach(l => l.style.color = '#d1d5db');
                    }
                });
            });
        });
    </script>
    
    @stack('scripts')
</body>
</html>