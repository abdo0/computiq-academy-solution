<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Database and Table Selection -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ __('Select Database') }}
                </label>
                <select wire:model.live="selectedDatabase" wire:change="selectDatabase" 
                        class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                    <option value="">{{ __('Choose a database...') }}</option>
                    @foreach($this->databases as $database)
                        <option value="{{ $database }}">{{ $database }}</option>
                    @endforeach
                </select>
            </div>
            
            @if($selectedDatabase)
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('Select Table') }}
                    </label>
                    <select wire:model.live="selectedTable" wire:change="selectTable"
                            class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                        <option value="">{{ __('Choose a table...') }}</option>
                        @foreach($this->tables as $table)
                            <option value="{{ $table }}">{{ $table }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
        </div>

        <!-- Database Info -->
        @if($selectedDatabase)
            <div class="bg-white dark:bg-gray-800 shadow rounded-md p-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                    {{ __('Database Information') }}
                </h3>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="font-medium text-gray-700 dark:text-gray-300">{{ __('Database:') }}</span>
                        <span class="text-gray-900 dark:text-white">{{ $selectedDatabase }}</span>
                    </div>
                    <div>
                        <span class="font-medium text-gray-700 dark:text-gray-300">{{ __('Tables:') }}</span>
                        <span class="text-gray-900 dark:text-white">{{ $this->tables->count() }}</span>
                    </div>
                </div>
            </div>
        @endif

        <!-- Table Structure -->
        @if($selectedTable && count($tableStructure) > 0)
            <div class="bg-white dark:bg-gray-800 shadow rounded-md">
                <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                        {{ __('Table Structure') }}: {{ $selectedTable }}
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    {{ __('Column') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    {{ __('Type') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    {{ __('Nullable') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    {{ __('Default') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    {{ __('Length') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($tableStructure as $column)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $column['column_name'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                        {{ $column['data_type'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                        {{ $column['is_nullable'] === 'YES' ? __('Yes') : __('No') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                        {{ $column['column_default'] ?? __('None') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                        {{ $column['character_maximum_length'] ?? '-' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- Table Data -->
        @if($selectedTable && count($tableData) > 0)
            <div class="bg-white dark:bg-gray-800 shadow rounded-md">
                <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                        {{ __('Table Data') }}: {{ $selectedTable }}
                        <span class="text-sm text-gray-500 dark:text-gray-400">
                            ({{ __(':count total rows', ['count' => $this->getTableRowCount()]) }})
                        </span>
                    </h3>
                    <div class="flex items-center space-x-2">
                        <select wire:model.live="perPage" wire:change="updatedPerPage"
                                class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('per page') }}</span>
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                @if(count($tableData) > 0)
                                    @foreach(array_keys($tableData[0]) as $column)
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            {{ $column }}
                                        </th>
                                    @endforeach
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        {{ __('Actions') }}
                                    </th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($tableData as $index => $row)
                                <tr>
                                    @foreach($row as $column => $value)
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                            @if($editingRowId === $index && $column !== $this->getPrimaryKey())
                                                <input type="text" wire:model="editingRowData.{{ $column }}"
                                                       class="w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                                            @else
                                                <span class="max-w-xs truncate block">
                                                    {{ is_string($value) ? Str::limit($value, 50) : $value }}
                                                </span>
                                            @endif
                                        </td>
                                    @endforeach
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @if($editingRowId === $index)
                                            <div class="flex space-x-2">
                                                <button wire:click="saveRow" 
                                                        class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-200">
                                                    {{ __('Save') }}
                                                </button>
                                                <button wire:click="cancelEdit" 
                                                        class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200">
                                                    {{ __('Cancel') }}
                                                </button>
                                            </div>
                                        @else
                                            <div class="flex space-x-2">
                                                <button wire:click="startEditRow({{ $index }})" 
                                                        class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-200">
                                                    {{ __('Edit') }}
                                                </button>
                                                <button wire:click="deleteRow({{ $index }})" 
                                                        onclick="return confirm('{{ __('Are you sure?') }}')"
                                                        class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-200">
                                                    {{ __('Delete') }}
                                                </button>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($this->getTableRowCount() > $perPage)
                    <div class="px-6 py-3 border-t border-gray-200 dark:border-gray-700">
                        <div class="flex justify-between items-center">
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                {{ __('Showing :from to :to of :total results', [
                                    'from' => (($this->getPage() - 1) * $perPage) + 1,
                                    'to' => min($this->getPage() * $perPage, $this->getTableRowCount()),
                                    'total' => $this->getTableRowCount()
                                ]) }}
                            </div>
                            <div class="flex space-x-2">
                                @if($this->getPage() > 1)
                                    <button wire:click="previousPage" 
                                            class="px-3 py-1 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded hover:bg-gray-300 dark:hover:bg-gray-500">
                                        {{ __('Previous') }}
                                    </button>
                                @endif
                                
                                @for($i = max(1, $this->getPage() - 2); $i <= min($this->getTotalPages(), $this->getPage() + 2); $i++)
                                    <button wire:click="gotoPage({{ $i }})"
                                            class="px-3 py-1 rounded {{ $i === $this->getPage() ? 'bg-primary-500 text-white' : 'bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-500' }}">
                                        {{ $i }}
                                    </button>
                                @endfor
                                
                                @if($this->getPage() < $this->getTotalPages())
                                    <button wire:click="nextPage" 
                                            class="px-3 py-1 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded hover:bg-gray-300 dark:hover:bg-gray-500">
                                        {{ __('Next') }}
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @endif

        <!-- SQL Query Executor -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-md">
            <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                    {{ __('SQL Query Executor') }}
                </h3>
            </div>
            <div class="p-4 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('SQL Query') }}
                    </label>
                    <textarea wire:model="sqlQuery" 
                              rows="4" 
                              placeholder="{{ __('Enter your SQL query here...') }}"
                              class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 font-mono text-sm"></textarea>
                </div>
                
                <div class="flex justify-between items-center">
                    <button wire:click="executeQuery" 
                            class="px-4 py-2 bg-primary-500 text-white rounded hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
                        {{ __('Execute Query') }}
                    </button>
                    
                    <div class="flex space-x-2">
                        <button wire:click="$set('sqlQuery', 'SELECT * FROM ' . ($selectedTable ?? 'table_name') . ' LIMIT 10;')"
                                class="px-3 py-1 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded text-sm hover:bg-gray-300 dark:hover:bg-gray-500">
                            {{ __('SELECT') }}
                        </button>
                        <button wire:click="$set('sqlQuery', 'SHOW TABLES;')"
                                class="px-3 py-1 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded text-sm hover:bg-gray-300 dark:hover:bg-gray-500">
                            {{ __('SHOW TABLES') }}
                        </button>
                        @if($selectedTable)
                            <button wire:click="$set('sqlQuery', 'DESCRIBE ' . $selectedTable . ';')"
                                    class="px-3 py-1 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded text-sm hover:bg-gray-300 dark:hover:bg-gray-500">
                                {{ __('DESCRIBE') }}
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Query Results -->
        @if($showQueryResults && count($queryResults) > 0)
            <div class="bg-white dark:bg-gray-800 shadow rounded-md">
                <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                        {{ __('Query Results') }}
                        <span class="text-sm text-gray-500 dark:text-gray-400">
                            ({{ count($queryResults) }} {{ __('Rows') }})
                        </span>
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                @if(count($queryResults) > 0)
                                    @foreach(array_keys($queryResults[0]) as $column)
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            {{ $column }}
                                        </th>
                                    @endforeach
                                @endif
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($queryResults as $row)
                                <tr>
                                    @foreach($row as $value)
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                            <span class="max-w-xs truncate block">
                                                {{ is_string($value) ? Str::limit($value, 100) : $value }}
                                            </span>
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>