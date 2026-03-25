<x-filament-widgets::widget>
    <div class="space-y-6">
        {{-- Quick Stats Section --}}
        <div class="widget-group" data-group="quick-stats">
            <div class="group-container bg-white dark:bg-gray-800 border-2 border-solid border-gray-200 dark:border-gray-700 rounded-md p-4 transition-all">
                <div class="flex items-center gap-2 mb-4">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">{{ __('Quick Stats') }}</h3>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    @foreach($this->getQuickStats() as $index => $stat)
                        <a href="{{ $stat['url'] }}" class="block">
                            <div class="quick-stat-card bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-md p-5 pe-8 hover:shadow-lg transition-all duration-200 relative h-full flex flex-col">
                                {{-- Link Arrow --}}
                                <div class="absolute top-1 right-1 rtl:right-auto rtl:left-1 p-1.5 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors group">
                                    <svg class="w-4 h-4 text-gray-400 dark:text-gray-500 group-hover:text-{{ $stat['color'] }}-600 dark:group-hover:text-{{ $stat['color'] }}-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                    </svg>
                                </div>
                                
                                {{-- Content --}}
                                <div class="quick-stat-content my-auto">
                                    <div class="flex items-center justify-between mb-3">
                                        <div>
                                            <x-filament::icon :icon="$stat['icon']" class="h-8 w-8 text-{{ $stat['color'] }}-600 dark:text-{{ $stat['color'] }}-400" />
                                        </div>
                                        <div class="text-right rtl:text-left">
                                            <div class="text-3xl font-bold text-gray-900 dark:text-white">{{ $stat['value'] }}</div>
                                        </div>
                                    </div>
                                    <div class="text-sm font-medium text-gray-900 dark:text-white mb-1">{{ $stat['title'] }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $stat['description'] }}</div>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Alerts Section --}}
        <div class="widget-group" data-group="alerts">
            <div class="group-container bg-white dark:bg-gray-800 border-2 border-solid border-gray-200 dark:border-gray-700 rounded-md p-4 transition-all">
                <div class="flex items-center gap-2 mb-4">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">{{ __('Alerts & Notifications') }}</h3>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                    @foreach($this->getAlerts() as $alert)
                        <a href="{{ $alert['url'] }}" class="block">
                            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-md p-4 hover:shadow-md transition-all duration-200 group cursor-pointer
                                 @if($alert['count'] > 0) border-l-4 border-l-{{ $alert['color'] }}-500 @endif">
                                
                                {{-- Icon and Count --}}
                                <div class="flex items-center justify-between mb-2">
                                    <div class="w-8 h-8 bg-{{ $alert['color'] }}-100 dark:bg-{{ $alert['color'] }}-900 rounded-md flex items-center justify-center">
                                        <x-filament::icon :icon="$alert['icon']" class="h-4 w-4 text-{{ $alert['color'] }}-600 dark:text-{{ $alert['color'] }}-400" />
                                    </div>
                                    <div class="text-right">
                                        <div class="text-2xl font-bold text-gray-900 dark:text-white
                                            @if($alert['count'] > 0) text-{{ $alert['color'] }}-600 dark:text-{{ $alert['color'] }}-400 @endif">
                                            {{ number_format($alert['count']) }}
                                        </div>
                                    </div>
                                </div>

                                {{-- Title --}}
                                <div class="text-sm font-medium text-gray-900 dark:text-white group-hover:text-{{ $alert['color'] }}-600 dark:group-hover:text-{{ $alert['color'] }}-400 transition-colors">
                                    {{ $alert['title'] }}
                                </div>

                                {{-- Status --}}
                                <div class="mt-2">
                                    @if($alert['count'] > 0)
                                        <span class="inline-flex items-center px-2 py-1 rounded-xl text-xs font-medium bg-{{ $alert['color'] }}-100 text-{{ $alert['color'] }}-800 dark:bg-{{ $alert['color'] }}-900 dark:text-{{ $alert['color'] }}-200">
                                            {{ __('Action Required') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 rounded-xl text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                                            {{ __('All Clear') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Reports Section --}}
        <div class="widget-group" data-group="reports">
            <div class="group-container bg-white dark:bg-gray-800 border-2 border-solid border-gray-200 dark:border-gray-700 rounded-md p-4 transition-all">
                <div class="flex items-center gap-2 mb-4">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">{{ __('Reports') }}</h3>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6">
                    @foreach($this->getReports() as $index => $report)
                        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-md p-4 md:p-6 shadow-sm hover:shadow-lg transition-all duration-300 group cursor-pointer touch-manipulation relative overflow-hidden"
                             onclick="window.location.href='{{ $report['url'] }}'">
                            
                            {{-- Icon and Title --}}
                            <div class="flex items-center space-x-3 mb-4">
                                <div class="w-10 h-10 bg-{{ $report['color'] }}-100 dark:bg-{{ $report['color'] }}-900 rounded-md flex items-center justify-center transition-transform duration-200">
                                    <x-filament::icon :icon="$report['icon']" class="h-5 w-5 text-{{ $report['color'] }}-600 dark:text-{{ $report['color'] }}-400" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white group-hover:text-{{ $report['color'] }}-600 dark:group-hover:text-{{ $report['color'] }}-400 transition-colors">
                                        {{ $report['title'] }}
                                    </h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        {{ $report['description'] }}
                                    </p>
                                </div>
                            </div>

                            {{-- Stats --}}
                            <div class="space-y-2 mb-4">
                                @foreach($report['stats'] as $statLabel => $statValue)
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-gray-600 dark:text-gray-400">{{ $statLabel }}</span>
                                        <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ number_format($statValue) }}</span>
                                    </div>
                                @endforeach
                            </div>

                            {{-- Mini Chart --}}
                            <div class="h-56 mb-4">
                                <canvas id="chart-{{ $index }}" class="w-full h-full"></canvas>
                            </div>

                            {{-- Quick Actions --}}
                            <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                                <div class="flex items-center justify-between">
                                    <div class="flex space-x-2">
                                        <a href="{{ $report['url'] }}" 
                                           class="inline-flex items-center px-2 py-1 text-xs font-medium text-{{ $report['color'] }}-600 dark:text-{{ $report['color'] }}-400 bg-{{ $report['color'] }}-50 dark:bg-{{ $report['color'] }}-900/20 rounded hover:bg-{{ $report['color'] }}-100 dark:hover:bg-{{ $report['color'] }}-900/40 transition-colors">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"></path>
                                                <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"></path>
                                            </svg>
                                            {{ __('View') }}
                                        </a>
                                        <button onclick="exportData('{{ $report['title'] }}', {{ $index }})" 
                                                class="inline-flex items-center px-2 py-1 text-xs font-medium text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-700 rounded hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                            </svg>
                                            {{ __('Export') }}
                                        </button>
                                    </div>
                                    <button onclick="refreshData({{ $index }})" 
                                            class="inline-flex items-center p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Chart.js Script --}}
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof Chart !== 'undefined') {
                Chart.register(ChartDataLabels);
                createMiniCharts();
            }
        });

        document.addEventListener('livewire:navigated', function() {
            if (typeof Chart !== 'undefined') {
                Chart.register(ChartDataLabels);
                createMiniCharts();
            }
        });

        function exportData(reportTitle, index) {
            const data = @json($this->getChartData());
            const reportKeys = ['donors', 'campaigns', 'donations', 'transactions', 'organizations', 'payouts'];
            const key = reportKeys[index];
            
            if (data[key]) {
                const csvContent = "data:text/csv;charset=utf-8," 
                    + "Metric,Value\n"
                    + data[key].labels.map((label, idx) => `${label},${data[key].data[idx]}`).join("\n");
                
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
            createMiniCharts();
        }

        function createMiniCharts() {
            if (typeof Chart === 'undefined') return;
            
            const chartData = @json($this->getChartData());
            const reportKeys = ['donors', 'campaigns', 'donations', 'transactions', 'organizations', 'payouts'];
            
            reportKeys.forEach((key, index) => {
                const canvas = document.getElementById(`chart-${index}`);
                if (!canvas) return;

                const ctx = canvas.getContext('2d');
                if (!ctx) return;

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
                                formatter: function(value) {
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
                                right: 20
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
                                displayColors: true
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
                                    padding: 15
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
