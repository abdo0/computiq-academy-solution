<div class="rounded-md border border-gray-200 bg-gray-50 p-4">
    <div class="mb-2 text-sm font-medium text-gray-700">
        {{ __('WhatsApp Message Preview:') }}
    </div>
    <div class="rounded bg-white p-3 text-sm text-gray-800 shadow-sm">
        {{ $preview ?? __('No preview available') }}
    </div>
</div>