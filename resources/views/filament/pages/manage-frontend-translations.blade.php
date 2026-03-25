<x-filament-panels::page>
    <div x-data="{
            search: @entangle('search').live(debounce: '500ms'),
            translations: @entangle('translations'),
            isEditing: null,
            editValue: '',
            
            startEdit(key, value) {
                this.isEditing = key;
                this.editValue = value;
                $nextTick(() => {
                    $refs['input_' + key].focus();
                });
            },
            
            saveEdit(key) {
                @this.call('updateTranslation', key, this.editValue);
                this.isEditing = null;
            },
            
            cancelEdit() {
                this.isEditing = null;
                this.editValue = '';
            }
        }"
        class="space-y-6"
    >
        <!-- Language Tabs -->
        <div class="flex space-x-2 rtl:space-x-reverse border-b border-gray-200 dark:border-white/10">
            @foreach($this->languages as $code => $name)
                <button
                    wire:click="$set('selectedLanguage', '{{ $code }}')"
                    @class([
                        'px-4 py-2 text-sm font-medium border-b-2 focus:outline-none transition-colors duration-200',
                        'border-primary-600 text-primary-600 dark:border-primary-500 dark:text-primary-500' => $this->selectedLanguage === $code,
                        'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' => $this->selectedLanguage !== $code,
                    ])
                >
                    {{ $name }}
                </button>
            @endforeach
        </div>

        <!-- Search and Stats Bar -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-4 sm:space-y-0">
            <div class="w-full sm:w-1/3">
                <x-filament::input.wrapper icon="heroicon-m-magnifying-glass">
                    <x-filament::input
                        type="search"
                        wire:model.live.debounce.500ms="search"
                        placeholder="{{ __('Search keys or values...') }}"
                    />
                </x-filament::input.wrapper>
            </div>
            
            <div class="text-sm text-gray-500 dark:text-gray-400">
                {{ __('Showing :count keys for :language', [
                    'count' => $this->getTranslationsCount(),
                    'language' => $this->languages[$this->selectedLanguage]
                ]) }}
            </div>
        </div>

        @php
            $paginatedData = $this->getPaginatedTranslations();
            $translationsList = $paginatedData['data'];
        @endphp

        <!-- Translation Table -->
        <div class="bg-white dark:bg-gray-900 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 rounded-md overflow-hidden">
            <table class="w-full text-left rtl:text-right divide-y divide-gray-200 dark:divide-white/5">
                <thead class="bg-gray-50 dark:bg-white/5">
                    <tr>
                        <th class="px-4 py-3 text-sm font-medium text-gray-500 dark:text-gray-400 w-1/3">{{ __('Key') }}</th>
                        <th class="px-4 py-3 text-sm font-medium text-gray-500 dark:text-gray-400 w-1/2">{{ __('Value') }}</th>
                        <th class="px-4 py-3 text-sm font-medium text-gray-500 dark:text-gray-400 w-auto text-right rtl:text-left">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-white/5 whitespace-nowrap">
                    @forelse($translationsList as $translation)
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition duration-75">
                            <!-- Key Column -->
                            <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white truncate max-w-xs" title="{{ $translation->key }}">
                                <span class="bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-300 py-1 px-2 rounded font-mono text-xs">
                                    {{ $translation->key }}
                                </span>
                            </td>
                            
                            <!-- Value Column -->
                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 whitespace-normal">
                                <div x-show="isEditing !== '{{ md5($translation->key) }}'" 
                                     @dblclick="startEdit('{{ md5($translation->key) }}', '{{ addslashes($translation->value) }}')"
                                     class="cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800 p-1 -m-1 rounded">
                                    @if($translation->key === $translation->value)
                                        <span class="text-amber-600 dark:text-amber-500 italic">{{ __('Untranslated') }} ({{ $translation->value }})</span>
                                    @else
                                        {{ $translation->value }}
                                    @endif
                                </div>
                                
                                <div x-show="isEditing === '{{ md5($translation->key) }}'" x-cloak class="flex space-x-2 rtl:space-x-reverse items-center">
                                    <textarea 
                                        x-ref="input_{{ md5($translation->key) }}"
                                        x-model="editValue"
                                        @keydown.enter.prevent="saveEdit('{{ $translation->key }}')"
                                        @keydown.escape.prevent="cancelEdit()"
                                        class="block w-full rounded-md border-gray-300 dark:border-white/10 dark:bg-gray-900 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 transition duration-75 sm:text-sm"
                                        rows="2"
                                    ></textarea>
                                </div>
                            </td>
                            
                            <!-- Actions Column -->
                            <td class="px-4 py-3 text-sm text-right rtl:text-left">
                                <div class="flex items-center justify-end space-x-2 rtl:space-x-reverse">
                                    <div x-show="isEditing !== '{{ md5($translation->key) }}'">
                                        <x-filament::icon-button
                                            icon="heroicon-m-pencil-square"
                                            color="gray"
                                            tooltip="{{ __('Edit') }}"
                                            @click="startEdit('{{ md5($translation->key) }}', '{{ addslashes($translation->value) }}')"
                                        />
                                    </div>
                                    
                                    <div x-show="isEditing === '{{ md5($translation->key) }}'" x-cloak class="flex space-x-1 rtl:space-x-reverse">
                                        <x-filament::icon-button
                                            icon="heroicon-m-check"
                                            color="success"
                                            tooltip="{{ __('Save') }}"
                                            @click="saveEdit('{{ $translation->key }}')"
                                        />
                                        <x-filament::icon-button
                                            icon="heroicon-m-x-mark"
                                            color="danger"
                                            tooltip="{{ __('Cancel') }}"
                                            @click="cancelEdit()"
                                        />
                                    </div>

                                    <div x-show="isEditing !== '{{ md5($translation->key) }}'">
                                        <x-filament::icon-button
                                            icon="heroicon-m-trash"
                                            color="danger"
                                            tooltip="{{ __('Delete') }}"
                                            wire:click="deleteTranslation('{{ $translation->key }}')"
                                            wire:confirm="{{ __('Are you sure you want to delete this translation?') }}"
                                        />
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                <div class="flex flex-col items-center justify-center space-y-3">
                                    <x-filament::icon
                                        icon="heroicon-o-language"
                                        class="w-8 h-8 text-gray-400 dark:text-gray-500"
                                    />
                                    <p>{{ __('No translations found.') }}</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- Pagination Controls -->
            @if($paginatedData['totalPages'] > 1)
                <div class="px-4 py-3 border-t border-gray-200 dark:border-white/10 flex items-center justify-between">
                    <div class="flex-1 flex justify-between sm:hidden">
                        <x-filament::button
                            color="gray"
                            wire:click="previousPage"
                            wire:loading.attr="disabled"
                            :disabled="$paginatedData['currentPage'] <= 1"
                        >
                            {{ __('Previous') }}
                        </x-filament::button>
                        <x-filament::button
                            color="gray"
                            wire:click="nextPage"
                            wire:loading.attr="disabled"
                            :disabled="$paginatedData['currentPage'] >= $paginatedData['totalPages']"
                        >
                            {{ __('Next') }}
                        </x-filament::button>
                    </div>
                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-gray-700 dark:text-gray-300">
                                {{ __('Showing') }}
                                <span class="font-medium">{{ ($paginatedData['currentPage'] - 1) * $this->perPage + 1 }}</span>
                                {{ __('To') }}
                                <span class="font-medium">{{ min($paginatedData['currentPage'] * $this->perPage, $paginatedData['total']) }}</span>
                                {{ __('Of') }}
                                <span class="font-medium">{{ $paginatedData['total'] }}</span>
                                {{ __('Results') }}
                            </p>
                        </div>
                        <div>
                            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                                <button
                                    wire:click="previousPage"
                                    wire:loading.attr="disabled"
                                    {{ $paginatedData['currentPage'] <= 1 ? 'disabled' : '' }}
                                    class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 dark:border-white/10 bg-white dark:bg-gray-800 text-sm font-medium text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50"
                                >
                                    <span class="sr-only">{{ __('Previous') }}</span>
                                    <x-filament::icon icon="heroicon-m-chevron-left" class="h-5 w-5 rtl:rotate-180" />
                                </button>
                                
                                @php
                                    $startPage = max(1, $paginatedData['currentPage'] - 2);
                                    $endPage = min($paginatedData['totalPages'], $paginatedData['currentPage'] + 2);
                                @endphp
                                
                                @for ($i = $startPage; $i <= $endPage; $i++)
                                    <button
                                        wire:click="gotoPage({{ $i }})"
                                        class="relative inline-flex items-center px-4 py-2 border border-gray-300 dark:border-white/10 bg-white dark:bg-gray-800 text-sm font-medium {{ $paginatedData['currentPage'] === $i ? 'text-primary-600 dark:text-primary-400 z-10 bg-primary-50 dark:bg-primary-900/50' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }}"
                                    >
                                        {{ $i }}
                                    </button>
                                @endfor
                                
                                <button
                                    wire:click="nextPage"
                                    wire:loading.attr="disabled"
                                    {{ $paginatedData['currentPage'] >= $paginatedData['totalPages'] ? 'disabled' : '' }}
                                    class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 dark:border-white/10 bg-white dark:bg-gray-800 text-sm font-medium text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50"
                                >
                                    <span class="sr-only">{{ __('Next') }}</span>
                                    <x-filament::icon icon="heroicon-m-chevron-right" class="h-5 w-5 rtl:rotate-180" />
                                </button>
                            </nav>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-filament-panels::page>
