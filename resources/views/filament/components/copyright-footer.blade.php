{{-- Copyright Footer Component --}}
<div class="bg-gray-50 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700 px-6 py-4 text-center">
    <div class="flex flex-col sm:!flex-row justify-between items-center text-sm text-gray-600 dark:text-gray-400">
        <div class="mb-2 sm:mb-0">
            © {{ date('Y') }} 
            @if(settings('global_legal_entity_name'))
                {{ settings('global_legal_entity_name') }}
            @else
                {{ settings('app_name', config('app.name')) }}
            @endif
            . {{ __('All rights reserved.') }}
            @if(settings('global_registration_number'))
                <span class="hidden sm:inline">• {{ __('Reg. No') }}: {{ settings('global_registration_number') }}</span>
            @endif
        </div>
        <div class="text-xs">
            @if(settings('global_copyright_text'))
                {{ settings('global_copyright_text') }}
            @else
                {{ __('Business Management System') }}
            @endif
            @if(settings('global_system_version'))
                v{{ settings('global_system_version') }}
            @endif
        </div>
    </div>
    
    @if(settings('global_license_information'))
        <div class="mt-2 text-xs text-gray-500 dark:text-gray-500">
            {{ settings('global_license_information') }}
        </div>
    @endif
</div>