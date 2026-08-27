@extends('layouts.admin', ['headerTitle' => 'Hasil Pemilihan Realtime'])

@section('content')
<div class="space-y-8" x-data="realtimeResultsHandler()">
    
    <!-- Top Summary Banner -->
    <div class="bg-gradient-to-r from-emerald-900 via-emerald-800 to-slate-900 rounded-3xl p-6 text-white shadow-xl flex flex-col md:flex-row items-center justify-between gap-6">
        <div>
            <div class="flex items-center space-x-2">
                <span class="w-3 h-3 bg-emerald-400 rounded-full animate-ping"></span>
                <span class="text-xs font-extrabold uppercase tracking-wider text-emerald-300">LIVE REALTIME MONITORING</span>
            </div>
            <h2 class="text-xl sm:text-2xl font-extrabold tracking-tight mt-1">
                Hasil Perolehan Suara Calon Formatur
            </h2>
            <p class="text-xs text-emerald-200 mt-0.5">
                Data diperbarui secara otomatis secara realtime | Terakhir diperbarui: <span font-mono class="font-bold text-white" x-text="lastUpdated">--:--:--</span>
            </p>
        </div>

        <div class="flex items-center space-x-4">
            <div class="text-right border-r border-emerald-700/50 pr-4">
                <div class="text-2xl font-black text-white" x-text="summary.voted_students + ' / ' + summary.total_students">0 / 0</div>
                <div class="text-[10px] uppercase font-bold text-emerald-300">Siswa Memilih</div>
            </div>

            <div class="text-right">
                <div class="text-2xl font-black text-amber-400" x-text="summary.participation_rate + '%'">0%</div>
                <div class="text-[10px] uppercase font-bold text-emerald-300">Partisipasi</div>
            </div>

            <a href="{{ route('admin.results.reveal') }}" target="_blank" class="px-4 py-2.5 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-slate-950 rounded-2xl font-black text-xs shadow-lg shadow-amber-500/20 transition flex items-center space-x-1.5" title="Buka Mode Panggung Pengumuman 12 Formatur">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span>Live Reveal Show (Top 12)</span>
            </a>

            <button type="button" @click="fetchData()" class="p-3 bg-emerald-800 hover:bg-emerald-700 rounded-2xl transition text-emerald-200 hover:text-white" title="Refresh Sekarang">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
            </button>
        </div>
    </div>

    <!-- Chart Controls & Visualizations -->
    <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm">
        <div class="flex flex-col sm:flex-row items-center justify-between mb-6 pb-4 border-b border-slate-100 gap-4">
            <h3 class="text-sm font-extrabold uppercase tracking-wider text-slate-800 flex items-center">
                <svg class="w-5 h-5 mr-2 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                Grafik Perolehan Suara
            </h3>

            <div class="flex items-center bg-slate-100 p-1 rounded-2xl space-x-1">
                <button type="button" @click="activeChart = 'bar'" :class="activeChart === 'bar' ? 'bg-white shadow text-slate-900 font-extrabold' : 'text-slate-500 hover:text-slate-900'" class="px-3 py-1.5 rounded-xl text-xs transition">
                    Bar Chart
                </button>
                <button type="button" @click="activeChart = 'horizontalBar'" :class="activeChart === 'horizontalBar' ? 'bg-white shadow text-slate-900 font-extrabold' : 'text-slate-500 hover:text-slate-900'" class="px-3 py-1.5 rounded-xl text-xs transition">
                    Horizontal Bar
                </button>
                <button type="button" @click="activeChart = 'doughnut'" :class="activeChart === 'doughnut' ? 'bg-white shadow text-slate-900 font-extrabold' : 'text-slate-500 hover:text-slate-900'" class="px-3 py-1.5 rounded-xl text-xs transition">
                    Donut Chart
                </button>
            </div>
        </div>

        <!-- Canvas Container -->
        <div class="relative min-h-[350px] w-full">
            <canvas id="liveChartCanvas"></canvas>
        </div>
    </div>

    <!-- Leaderboard / Ranking Table -->
    <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm">
        <h3 class="text-sm font-extrabold uppercase tracking-wider text-slate-800 mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path></svg>
            Peringkat Suara Terbanyak Formatur
        </h3>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-400 font-extrabold uppercase tracking-wider">
                    <tr>
                        <th class="py-3.5 px-4 text-center">Rank</th>
                        <th class="py-3.5 px-4">No. Urut</th>
                        <th class="py-3.5 px-4">Nama Calon</th>
                        <th class="py-3.5 px-4">Kelas</th>
                        <th class="py-3.5 px-4">Total Suara</th>
                        <th class="py-3.5 px-4">Persentase Pemilih</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    <template x-for="candidate in ranking" :key="candidate.id">
                        <tr class="hover:bg-slate-50/80 transition" :class="candidate.rank <= 9 ? 'bg-emerald-50/30 font-bold' : ''">
                            <td class="py-3 px-4 text-center font-black">
                                <template x-if="candidate.rank === 1"><span>🥇 1</span></template>
                                <template x-if="candidate.rank === 2"><span>🥈 2</span></template>
                                <template x-if="candidate.rank === 3"><span>🥉 3</span></template>
                                <template x-if="candidate.rank > 3"><span class="text-slate-400" x-text="candidate.rank"></span></template>
                            </td>
                            <td class="py-3 px-4 font-mono font-extrabold text-slate-900" x-text="candidate.nomor_urut"></td>
                            <td class="py-3 px-4 font-extrabold text-slate-900 flex items-center space-x-2">
                                <template x-if="candidate.foto">
                                    <img :src="candidate.foto" class="w-7 h-7 rounded-full object-cover border border-slate-200">
                                </template>
                                <span x-text="candidate.nama"></span>
                            </td>
                            <td class="py-3 px-4 font-semibold text-emerald-700" x-text="candidate.kelas"></td>
                            <td class="py-3 px-4 font-black text-slate-900">
                                <span x-text="candidate.votes + ' Suara'" class="px-2.5 py-1 rounded-xl bg-slate-100"></span>
                            </td>
                            <td class="py-3 px-4">
                                <div class="flex items-center space-x-2">
                                    <div class="w-24 bg-slate-100 h-2 rounded-full overflow-hidden">
                                        <div class="bg-emerald-600 h-full rounded-full" :style="'width: ' + candidate.percentage + '%'"></div>
                                    </div>
                                    <span class="font-extrabold text-slate-900" x-text="candidate.percentage + '%'"></span>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function realtimeResultsHandler() {
    return {
        summary: {
            total_students: 0,
            voted_students: 0,
            participation_rate: 0
        },
        ranking: [],
        lastUpdated: '--:--:--',
        activeChart: 'bar',
        chartInstance: null,
        timer: null,

        init() {
            this.fetchData();
            // Poll every 5 seconds
            this.timer = setInterval(() => {
                this.fetchData();
            }, 5000);

            this.$watch('activeChart', () => {
                this.renderChart();
            });
        },

        fetchData() {
            const self = this;
            fetch('{{ route("admin.results.api") }}')
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        self.summary = data.summary;
                        self.ranking = data.ranking;
                        self.lastUpdated = data.summary.last_updated;
                        self.chartData = data.chart;
                        self.renderChart();
                    }
                })
                .catch(err => console.error("Error loading live results:", err));
        },

        renderChart() {
            if (!this.chartData) return;

            const ctx = document.getElementById('liveChartCanvas').getContext('2d');
            if (this.chartInstance) {
                this.chartInstance.destroy();
            }

            const chartType = this.activeChart === 'horizontalBar' ? 'bar' : this.activeChart;
            const isHorizontal = this.activeChart === 'horizontalBar';

            const bgColors = [
                '#059669', '#10b981', '#3b82f6', '#6366f1', '#8b5cf6',
                '#ec4899', '#f43f5e', '#f59e0b', '#14b8a6', '#06b6d4',
                '#84cc16', '#a855f7'
            ];

            this.chartInstance = new Chart(ctx, {
                type: chartType,
                data: {
                    labels: this.chartData.labels,
                    datasets: [{
                        label: 'Jumlah Suara',
                        data: this.chartData.votes,
                        backgroundColor: chartType === 'doughnut' ? bgColors : '#059669',
                        borderColor: '#047857',
                        borderWidth: 1.5,
                        borderRadius: 8,
                    }]
                },
                options: {
                    indexAxis: isHorizontal ? 'y' : 'x',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: chartType === 'doughnut'
                        }
                    },
                    scales: chartType === 'doughnut' ? {} : {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });
        }
    };
}
</script>
@endpush
