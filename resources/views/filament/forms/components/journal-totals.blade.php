<div x-data="{
    items: $wire.entangle('{{ $getStatePath() }}').live,
    
    _getProcessedItems() {
        if (Array.isArray(this.items)) {
            return this.items;
        }
        // Handle cases where items might be an object with item data as values
        if (typeof this.items === 'object' && this.items !== null) {
            return Object.values(this.items);
        }
        return []; // Default to an empty array if not an array or suitable object
    },

    get totalDebit() {
        const currentItems = this._getProcessedItems();
        // The console.log you added previously for this.items is helpful.
        // Let's also log what _getProcessedItems returns:
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
        // The console.log you added for debit and credit is helpful.
        return (debit - credit).toFixed(2);
    }
}">
    <div class="space-y-2">
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
             :class="{ 'text-danger-600': difference != 0, 'text-success-600': difference == 0 }">
            <span>{{ __('Difference') }}</span>
            <span x-text="difference"></span>
        </div>
    </div>
</div> 