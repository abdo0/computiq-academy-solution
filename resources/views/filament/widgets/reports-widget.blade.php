<x-filament-widgets::widget>
    <div class="space-y-6">
        {{-- Header Section --}}
        <div class="flex items-center justify-between mb-6 hidden">
            <div>
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                    {{ __('Reports') }}
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Access comprehensive reports and analytics') }}
                </p>
            </div>
        </div>

            {{-- Last Updated Info --}}
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center space-x-2 text-sm text-gray-500 dark:text-gray-400">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                    </svg>
                    <span id="lastUpdated">{{ __('Last updated: ') }}{{ now()->format('H:i') }}</span>
                </div>
                <button onclick="refreshAllData()" 
                        class="inline-flex items-center px-3 py-1 text-xs font-medium text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 rounded hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"></path>
                    </svg>
                    {{ __('Refresh All') }}
                </button>
            </div>

            {{-- Reports Grid --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6">
            @foreach($this->getReports() as $report)
                <div class="bg-white dark:bg-gray-900 border-2 border-gray-200 dark:border-gray-700 rounded-md p-5 md:p-6 shadow-sm hover:shadow-xl hover:scale-[1.02] transition-all duration-300 group cursor-pointer touch-manipulation relative overflow-hidden"
                     onclick="window.location.href='{{ $report['url'] }}'">
                    
                    {{-- Background gradient on hover --}}
                    <div class="absolute inset-0 bg-gradient-to-br from-{{ $report['color'] }}-50/0 to-{{ $report['color'] }}-50/0 dark:from-{{ $report['color'] }}-900/0 dark:to-{{ $report['color'] }}-900/0 group-hover:from-{{ $report['color'] }}-50/30 group-hover:to-{{ $report['color'] }}-50/10 dark:group-hover:from-{{ $report['color'] }}-900/20 dark:group-hover:to-{{ $report['color'] }}-900/10 transition-all duration-300"></div>
                    
                    <div class="relative z-10">
                    
                    {{-- Icon and Title --}}
                    <div class="flex items-center space-x-3 mb-5">
                        <div class="w-12 h-12 bg-{{ $report['color'] }}-100 dark:bg-{{ $report['color'] }}-900/50 rounded-md flex items-center justify-center group-hover:scale-110 transition-transform duration-200 shadow-sm">
                            <x-filament::icon :icon="$report['icon']" class="h-6 w-6 text-{{ $report['color'] }}-600 dark:text-{{ $report['color'] }}-400" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white group-hover:text-{{ $report['color'] }}-600 dark:group-hover:text-{{ $report['color'] }}-400 transition-colors">
                                {{ $report['title'] }}
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                {{ $report['description'] }}
                            </p>
                        </div>
                    </div>

                            {{-- Stats --}}
                            <div class="space-y-2.5 mb-5">
                                @foreach($report['stats'] as $statLabel => $statValue)
                                    <div class="flex items-center justify-between py-1.5 px-2 rounded-md hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $statLabel }}</span>
                                        <div class="flex items-center space-x-2">
                                            <span class="text-sm font-bold text-gray-900 dark:text-white">{{ number_format($statValue) }}</span>
                                            @if(isset($this->getChartData()[$loop->parent->index]['trends'][$loop->index]))
                                                @php
                                                    $trend = $this->getChartData()[$loop->parent->index]['trends'][$loop->index];
                                                @endphp
                                                @if($trend['direction'] === 'up')
                                                    <span class="text-xs text-green-600 dark:text-green-400 flex items-center">
                                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                                        </svg>
                                                        +{{ $trend['percentage'] }}%
                                                    </span>
                                                @elseif($trend['direction'] === 'down')
                                                    <span class="text-xs text-red-600 dark:text-red-400 flex items-center">
                                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 12.586V5a1 1 0 012 0v7.586l2.293-2.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                        </svg>
                                                        -{{ $trend['percentage'] }}%
                                                    </span>
                                                @else
                                                    <span class="text-xs text-gray-500 dark:text-gray-400 flex items-center">
                                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                                                        </svg>
                                                        0%
                                                    </span>
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                    {{-- Mini Chart --}}
                    <div class="h-56 mb-5 rounded-md overflow-hidden bg-gray-50 dark:bg-gray-800/50 p-2">
                        <canvas id="chart-{{ $loop->index }}" class="w-full h-full"></canvas>
                    </div>

                            {{-- Quick Actions --}}
                            <div class="mt-5 pt-4 border-t-2 border-gray-200 dark:border-gray-700">
                                <div class="flex items-center justify-between">
                                    <div class="flex space-x-2">
                                        <a href="{{ $report['url'] }}" 
                                           class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-{{ $report['color'] }}-600 dark:text-{{ $report['color'] }}-400 bg-{{ $report['color'] }}-50 dark:bg-{{ $report['color'] }}-900/30 rounded-md hover:bg-{{ $report['color'] }}-100 dark:hover:bg-{{ $report['color'] }}-900/50 transition-all duration-200 shadow-sm hover:shadow">
                                            <svg class="w-3.5 h-3.5 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"></path>
                                                <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"></path>
                                            </svg>
                                            {{ __('View') }}
                                        </a>
                                        <button onclick="exportData('{{ $report['title'] }}')" 
                                                class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 transition-all duration-200 shadow-sm hover:shadow">
                                            <svg class="w-3.5 h-3.5 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                            </svg>
                                            {{ __('Export') }}
                                        </button>
                                    </div>
                                    <button onclick="refreshData({{ $loop->index }})" 
                                            class="inline-flex items-center p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Quick Actions --}}
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-800 dark:to-gray-700 border-2 border-blue-200 dark:border-gray-600 rounded-md p-6 mt-8 shadow-sm">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-blue-600 dark:bg-blue-500 rounded-md flex items-center justify-center shadow-sm">
                        <x-heroicon-o-chart-bar class="h-5 w-5 text-white" />
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white">{{ __('Quick Actions') }}</h3>
                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-0.5">{{ __('Export data or generate custom reports') }}</p>
                    </div>
                </div>
                <div class="flex space-x-2">
                    <a href="{{ route('reports.export', ['type' => 'all', 'format' => 'excel']) }}" 
                       class="inline-flex items-center px-4 py-2 text-xs font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200 shadow-sm hover:shadow">
                        <x-heroicon-o-arrow-down-tray class="h-4 w-4 mr-1.5" />
                        {{ __('Export All') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Chart.js Script --}}
    {{-- <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> --}}
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Chart.register(ChartDataLabels);
            createMiniCharts();
        });

        document.addEventListener('livewire:navigated', function() {
            Chart.register(ChartDataLabels);
            createMiniCharts();
        });

        // Listen for dark mode changes
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                    setTimeout(createMiniCharts, 100);
                }
            });
        });
        observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });

            // Quick action functions
            function exportData(reportTitle) {
                // Create a simple CSV export
                const data = @json($this->getChartData());
                const reportKey = reportTitle.toLowerCase().replace(/\s+/g, '_').replace('_reports', '');
                
                if (data[reportKey]) {
                    const csvContent = "data:text/csv;charset=utf-8," 
                        + "Metric,Value\n"
                        + data[reportKey].labels.map((label, index) => `${label},${data[reportKey].data[index]}`).join("\n");
                    
                    const encodedUri = encodeURI(csvContent);
                    const link = document.createElement("A");
                    link.setAttribute("href", encodedUri);
                    link.setAttribute("download", `${reportTitle}_data.csv`);
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                }
            }

            function refreshData(chartIndex) {
                // Recreate the specific chart
                const canvas = document.getElementById(`chart-${chartIndex}`);
                if (canvas) {
                    const ctx = canvas.getContext('2d');
                    if (window[`miniChart${chartIndex}`]) {
                        window[`miniChart${chartIndex}`].destroy();
                    }
                    createMiniCharts();
                }
            }

            function refreshAllData() {
                // Refresh all charts and update timestamp
                createMiniCharts();
                updateLastUpdatedTime();
            }

            function updateLastUpdatedTime() {
                const now = new Date();
                const timeString = now.toLocaleTimeString('en-US', { 
                    hour12: false, 
                    hour: '2-digit', 
                    minute: '2-digit' 
                });
                document.getElementById('lastUpdated').textContent = `{{ __('Last updated: ') }}${timeString}`;
            }

            // Auto-refresh every 5 minutes
            setInterval(function() {
                refreshAllData();
            }, 5 * 60 * 1000);

            function showBarDetails(label, value, reportType) {
                // Create a simple modal or alert with detailed information
                const message = `${label}: ${value}\n\nThis represents data from ${reportType} reports.\nClick "View Report" to see more details.`;
                
                // You could replace this with a proper modal component
                alert(message);
            }

            function createMiniCharts() {
                const chartData = @json($this->getChartData());
                const reportKeys = ['clients', 'complaints', 'announcements', 'marketing_campaigns', 'branches'];
            
            reportKeys.forEach((key, index) => {
                const canvas = document.getElementById(`chart-${index}`);
                if (!canvas) return;

                const ctx = canvas.getContext('2d');
                if (!ctx) return;

                // Destroy existing chart if it exists
                if (window[`miniChart${index}`]) {
                    window[`miniChart${index}`].destroy();
                }

                const data = chartData[key];
                if (!data) return;

                window[`miniChart${index}`] = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: data.labels,
                        datasets: [{
                            data: data.data,
                            backgroundColor: data.colors,
                            borderWidth: 0,
                            borderRadius: 4,
                            borderSkipped: false,
                            datalabels: {
                                display: true,
                                color: '#ffffff',
                                font: {
                                    weight: 'bold',
                                    size: 9
                                },
                                anchor: 'end',
                                align: 'right',
                                offset: 4,
                                padding: 2,
                                formatter: function(value, context) {
                                    return value > 0 ? value : '';
                                }
                            }
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        indexAxis: 'y',
                        layout: {
                            padding: {
                                right: 20 // Add padding for data labels
                            }
                        },
                        onClick: function(event, elements) {
                            if (elements.length > 0) {
                                const element = elements[0];
                                const label = data.labels[element.index];
                                const value = data.data[element.index];
                                
                                // Show detailed info in a tooltip or modal
                                showBarDetails(label, value, reportKeys[index]);
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                enabled: true,
                                backgroundColor: function() {
                                    return document.documentElement.classList.contains('dark') ? 'rgba(31, 41, 55, 0.9)' : 'rgba(0, 0, 0, 0.8)';
                                },
                                titleColor: '#fff',
                                bodyColor: '#fff',
                                borderColor: function() {
                                    return document.documentElement.classList.contains('dark') ? 'rgba(75, 85, 99, 0.3)' : 'rgba(255, 255, 255, 0.1)';
                                },
                                borderWidth: 1,
                                cornerRadius: 6,
                                displayColors: true,
                                callbacks: {
                                    label: function(context) {
                                        const label = context.label || '';
                                        const value = context.parsed.x; // For horizontal bar chart
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                                        return `${label}: ${value} (${percentage}%)`;
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                beginAtZero: true,
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    color: function() {
                                        return document.documentElement.classList.contains('dark') ? '#d1d5db' : '#374151';
                                    },
                                    font: {
                                        size: 10
                                    },
                                    padding: 15 // Add padding for data labels
                                }
                            },
                            y: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    color: function() {
                                        return document.documentElement.classList.contains('dark') ? '#d1d5db' : '#374151';
                                    },
                                    font: {
                                        size: 10
                                    }
                                }
                            }
                        },
                        elements: {
                            bar: {
                                borderWidth: 0
                            }
                        }
                    }
                });
            });
        }
    </script>
</x-filament-widgets::widget>
