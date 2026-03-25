<div class="space-y-4">
    @foreach ($transactions as $transaction)
        <div class="bg-white dark:bg-gray-800 rounded-md border border-gray-200 dark:border-gray-700 p-4 hover:shadow-md transition-shadow">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Transaction Number & Amount -->
                <div>
                    <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">{{ __('Transaction #') }}</div>
                    <div class="font-semibold text-gray-900 dark:text-white">{{ $transaction->transaction_number }}</div>
                    <div class="text-2xl font-bold text-green-600 dark:text-green-400 mt-2">
                        {{ \Filament\Support\format_money($transaction->amount, 'USD') }}
                    </div>
                </div>

                <!-- Date & Status -->
                <div>
                    <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">{{ __('Date') }}</div>
                    <div class="font-medium text-gray-900 dark:text-white">
                        {{ $transaction->transaction_date->format('M d, Y') }}
                    </div>
                    <div class="mt-2">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-xl text-xs font-medium
                            @if($transaction->status->value === 'completed') bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400
                            @elseif($transaction->status->value === 'pending') bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400
                            @elseif($transaction->status->value === 'failed') bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400
                            @else bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400
                            @endif">
                            {{ $transaction->status->getLabel() }}
                        </span>
                    </div>
                </div>

                <!-- Payment Method & Reference -->
                <div>
                    <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">{{ __('Payment Method') }}</div>
                    <div class="font-medium text-gray-900 dark:text-white">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-xl text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">
                            {{ $transaction->payment_method->getLabel() }}
                        </span>
                    </div>
                    @if($transaction->reference_number)
                        <div class="mt-2">
                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('Reference') }}</div>
                            <div class="text-sm font-mono text-gray-700 dark:text-gray-300">{{ $transaction->reference_number }}</div>
                        </div>
                    @endif
                </div>
            </div>

            @if($transaction->notes)
                <div class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-700">
                    <div class="text-sm text-gray-600 dark:text-gray-400">{{ $transaction->notes }}</div>
                </div>
            @endif
        </div>
    @endforeach
</div>

