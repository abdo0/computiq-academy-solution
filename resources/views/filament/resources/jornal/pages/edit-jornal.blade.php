<x-filament-panels::page>
    <x-filament-schemas::form wire:submit="save">
        {{ $this->form }}

        {{-- Added Journal Totals --}}
        <div x-data="{
            items: $wire.entangle('data.items').live, // Adjusted to use 'data.items'
            
            _getProcessedItems() {
                if (Array.isArray(this.items)) {
                    return this.items;
                }
                if (typeof this.items === 'object' && this.items !== null) {
                    return Object.values(this.items);
                }
                return [];
            },

            get totalDebit() {
                const currentItems = this._getProcessedItems();
                console.log('For Debit - Original this.items:', this.items, 'Processed items:', currentItems);
                return currentItems.reduce((sum, item) => {
                    const debitValue = (typeof item === 'object' && item !== null) ? parseFloat(item.debit) : 0;
                    return sum + (isNaN(debitValue) ? 0 : debitValue);
                }, 0).toFixed(2);
            },

            get totalCredit() {
                const currentItems = this._getProcessedItems();
                console.log('For Credit - Original this.items:', this.items, 'Processed items:', currentItems);
                return currentItems.reduce((sum, item) => {
                    const creditValue = (typeof item === 'object' && item !== null) ? parseFloat(item.credit) : 0;
                    return sum + (isNaN(creditValue) ? 0 : creditValue);
                }, 0).toFixed(2);
            },

            get difference() {
                const debit = parseFloat(this.totalDebit) || 0;
                const credit = parseFloat(this.totalCredit) || 0;
                return (debit - credit).toFixed(2);
            }
        }">
            <div class="space-y-2 filament-forms-card-component p-6 bg-white shadow rounded-md dark:bg-gray-800">
                <div class="flex justify-between">
                    <span>{{ __('Total Debit') }}</span>
                    <span x-text="totalDebit"></span>
                </div>
                <div class="flex justify-between">
                    <span>{{ __('Total Credit') }}</span>
                    <span x-text="totalCredit"></span>
                </div>
                <hr>
                <div class="flex justify-between font-bold"
                     :class="{ 'text-danger-600 dark:text-danger-400': difference != 0, 'text-success-600 dark:text-success-400': difference == 0 }">
                    <span>{{ __('Difference') }}</span>
                    <span x-text="difference"></span>
                </div>
            </div>
        </div>
        {{-- End Journal Totals --}}

        <x-filament-schemas::actions
            :actions="$this->getCachedFormActions()"
            :full-width="$this->hasFullWidthFormActions()"
        />
    </x-filament-schemas::form>

</x-filament-panels::page>