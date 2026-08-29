<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-950 text-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Live Reveal 12 Formatur Terpilih - {{ $schoolName }}</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    animation: {
                        'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                        'bounce-subtle': 'bounceSubtle 2s infinite',
                        'glow': 'glow 2s ease-in-out infinite alternate',
                    },
                    keyframes: {
                        bounceSubtle: {
                            '0%, 100%': { transform: 'translateY(-2%)' },
                            '50%': { transform: 'translateY(0)' },
                        },
                        glow: {
                            '0%': { boxShadow: '0 0 15px rgba(16, 185, 129, 0.2)' },
                            '100%': { boxShadow: '0 0 35px rgba(16, 185, 129, 0.6)' },
                        }
                    }
                }
            }
        }
    </script>

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        
        .glass-panel {
            background: rgba(15, 23, 42, 0.75);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .glass-card {
            background: rgba(30, 41, 59, 0.6);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .gold-glow {
            box-shadow: 0 0 30px rgba(245, 158, 11, 0.4), inset 0 0 15px rgba(245, 158, 11, 0.2);
            border-color: rgba(245, 158, 11, 0.6) !important;
        }

        .silver-glow {
            box-shadow: 0 0 25px rgba(203, 213, 225, 0.3), inset 0 0 10px rgba(203, 213, 225, 0.15);
            border-color: rgba(203, 213, 225, 0.5) !important;
        }

        .bronze-glow {
            box-shadow: 0 0 25px rgba(217, 119, 6, 0.3), inset 0 0 10px rgba(217, 119, 6, 0.15);
            border-color: rgba(217, 119, 6, 0.5) !important;
        }

        .emerald-glow {
            box-shadow: 0 0 25px rgba(16, 185, 129, 0.25);
            border-color: rgba(16, 185, 129, 0.4) !important;
        }

        /* Perspective Flip animation */
        .reveal-flip {
            animation: flipIn 0.8s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
        }

        @keyframes flipIn {
            0% { opacity: 0; transform: perspective(800px) rotateX(-60deg) scale(0.85); }
            100% { opacity: 1; transform: perspective(800px) rotateX(0deg) scale(1); }
        }
    </style>
</head>
<body class="min-h-screen bg-slate-950 text-slate-100 selection:bg-emerald-500 selection:text-white relative overflow-x-hidden" x-data="revealShowHandler()">

    <!-- Ambient Canvas Background -->
    <canvas id="ambientBg" class="fixed inset-0 pointer-events-none opacity-40 z-0"></canvas>

    <!-- Content Container -->
    <div class="relative z-10 min-h-screen flex flex-col justify-between p-4 md:p-8 max-w-7xl mx-auto space-y-6">

        <!-- Top Header Navigation & Controls -->
        <header class="glass-panel rounded-3xl p-4 md:p-6 shadow-2xl flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 bg-gradient-to-tr from-emerald-600 to-emerald-400 rounded-2xl flex items-center justify-center font-black text-white text-lg shadow-lg shadow-emerald-500/30">
                    IPM
                </div>
                <div>
                    <div class="flex items-center space-x-2">
                        <span class="w-2.5 h-2.5 bg-emerald-400 rounded-full animate-ping"></span>
                        <span class="text-[10px] font-extrabold uppercase tracking-widest text-emerald-400">STAGE REVEAL SHOW</span>
                    </div>
                    <h1 class="text-lg md:text-xl font-black tracking-tight text-white leading-tight">
                        Pengumuman 12 Formatur Terpilih {{ date('Y') }}
                    </h1>
                    <p class="text-xs text-slate-400 font-semibold truncate max-w-md">
                        {{ $schoolName }} | {{ $electionName }}
                    </p>
                </div>
            </div>

            <!-- Action Controls -->
            <div class="flex flex-wrap items-center gap-2.5 justify-center md:justify-end w-full md:w-auto">
                <!-- Sound Toggle -->
                <button @click="soundEnabled = !soundEnabled; if (!soundEnabled && 'speechSynthesis' in window) window.speechSynthesis.cancel()" :class="soundEnabled ? 'bg-emerald-500/20 text-emerald-300 border-emerald-500/40' : 'bg-slate-800/60 text-slate-500 border-slate-700'" class="p-3 rounded-2xl border text-xs font-bold transition flex items-center space-x-1.5" title="Toggle Suara Efek">
                    <template x-if="soundEnabled">
                        <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15.536a5 5 0 001.414 1.414m2.828-9.9a9 9 0 0112.728 0M11 5L6 9H2v6h4l5 4V5z"></path></svg>
                    </template>
                    <template x-if="!soundEnabled">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15.536a5 5 0 001.414 1.414m12.728-12.728L3.343 19.657M11 5L6 9H2v6h4l5 4V5z"></path></svg>
                    </template>
                    <span class="hidden sm:inline" x-text="soundEnabled ? 'Suara ON' : 'Suara MUTE'"></span>
                </button>

                <!-- Fullscreen Toggle -->
                <button @click="toggleFullscreen()" class="p-3 bg-slate-800/60 hover:bg-slate-700/80 text-slate-300 rounded-2xl border border-slate-700 text-xs font-bold transition" title="Layar Penuh">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path></svg>
                </button>

                <!-- Reset Show Button -->
                <button @click="resetShow()" class="px-3.5 py-2.5 bg-slate-800/80 hover:bg-slate-700 text-slate-300 rounded-2xl border border-slate-700 text-xs font-bold transition flex items-center space-x-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    <span>Reset</span>
                </button>

                <!-- Auto Play Toggle -->
                <button @click="toggleAutoPlay()" :class="autoPlay ? 'bg-amber-500 text-slate-950 font-black shadow-lg shadow-amber-500/20' : 'bg-slate-800/80 text-amber-400 border border-amber-500/30 font-bold'" class="px-4 py-2.5 rounded-2xl text-xs transition flex items-center space-x-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span x-text="autoPlay ? 'Auto Play (Aktif)' : 'Auto Play'"></span>
                </button>

                <!-- Next Reveal Button -->
                <button @click="revealNext()" :disabled="revealedCount >= 12" :class="revealedCount >= 12 ? 'opacity-50 cursor-not-allowed bg-slate-800 text-slate-500' : 'bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black shadow-lg shadow-emerald-500/30 hover:scale-105 active:scale-95'" class="px-5 py-2.5 rounded-2xl text-xs transition transform flex items-center space-x-2">
                    <span>Tampilkan Peringkat Ke-<strong x-text="getNextRankToReveal()">--</strong></span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
                </button>
            </div>
        </header>

        <!-- Main Spotlight Banner for Newly Revealed Candidate -->
        <div class="relative overflow-hidden glass-panel rounded-3xl p-6 md:p-8 border border-emerald-500/30 transition-all duration-700 min-h-[220px] flex items-center justify-center">
            
            <template x-if="spotlightCandidate">
                <div class="w-full flex flex-col md:flex-row items-center justify-between gap-6 reveal-flip">
                    
                    <!-- Left Candidate Info -->
                    <div class="flex flex-col md:flex-row items-center md:items-start space-y-4 md:space-y-0 md:space-x-6 text-center md:text-left">
                        <div class="relative flex-shrink-0">
                            <div class="w-24 h-24 md:w-28 md:h-28 rounded-3xl overflow-hidden border-2 border-emerald-400/80 shadow-2xl bg-slate-900 flex items-center justify-center">
                                <template x-if="spotlightCandidate.foto">
                                    <img :src="spotlightCandidate.foto" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!spotlightCandidate.foto">
                                    <span class="text-3xl font-black text-emerald-400" x-text="spotlightCandidate.nomor_urut"></span>
                                </template>
                            </div>
                            <!-- Rank Tag on Avatar -->
                            <div class="absolute -top-3 -right-3 px-3 py-1 rounded-xl text-xs font-black shadow-xl"
                                 :class="{
                                     'bg-amber-400 text-slate-950 ring-4 ring-amber-500/40': spotlightCandidate.rank === 1,
                                     'bg-slate-200 text-slate-950 ring-4 ring-slate-400/40': spotlightCandidate.rank === 2,
                                     'bg-amber-700 text-amber-100 ring-4 ring-amber-800/40': spotlightCandidate.rank === 3,
                                     'bg-emerald-500 text-slate-950': spotlightCandidate.rank > 3
                                 }">
                                Rank #<span x-text="spotlightCandidate.rank"></span>
                            </div>
                        </div>

                        <div class="space-y-1">
                            <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-xl text-xs font-bold uppercase tracking-wider"
                                 :class="{
                                     'bg-amber-500/20 text-amber-300 border border-amber-500/40': spotlightCandidate.rank === 1,
                                     'bg-slate-300/20 text-slate-200 border border-slate-400/40': spotlightCandidate.rank === 2,
                                     'bg-amber-700/20 text-amber-400 border border-amber-600/40': spotlightCandidate.rank === 3,
                                     'bg-emerald-500/20 text-emerald-300 border border-emerald-500/40': spotlightCandidate.rank > 3
                                 }">
                                <template x-if="spotlightCandidate.rank === 1"><span>👑 KETUA FORMATUR / JUARA 1</span></template>
                                <template x-if="spotlightCandidate.rank === 2"><span>🥈 FORMATUR PERINGKAT 2</span></template>
                                <template x-if="spotlightCandidate.rank === 3"><span>🥉 FORMATUR PERINGKAT 3</span></template>
                                <template x-if="spotlightCandidate.rank > 3"><span>✨ ANGGOTA FORMATUR TERPILIH</span></template>
                            </div>
                            
                            <h2 class="text-2xl md:text-3xl font-black text-white leading-tight" x-text="spotlightCandidate.nama"></h2>
                            <p class="text-sm text-emerald-400 font-bold">
                                No. Urut <span class="font-mono" x-text="spotlightCandidate.nomor_urut"></span> | Kelas: <span x-text="spotlightCandidate.kelas"></span>
                            </p>
                        </div>
                    </div>

                    <!-- Right Animated Tally & Percentage -->
                    <div class="flex flex-col items-center md:items-end space-y-2 bg-slate-900/80 p-5 rounded-2xl border border-slate-800/80 min-w-[200px]">
                        <div class="text-xs uppercase font-extrabold text-slate-400 tracking-wider">Perolehan Suara</div>
                        <div class="text-4xl md:text-5xl font-black text-emerald-400 tracking-tight font-mono" x-text="animatedVotes + ' Suara'"></div>
                        
                        <div class="w-full bg-slate-800 h-2.5 rounded-full overflow-hidden mt-1">
                            <div class="bg-gradient-to-r from-emerald-500 to-amber-400 h-full rounded-full transition-all duration-700" :style="'width: ' + animatedPercentage + '%'"></div>
                        </div>
                        <div class="text-xs font-black text-slate-300" x-text="animatedPercentage + '% dari total suara'"></div>
                    </div>

                </div>
            </template>

            <!-- Waiting state before first reveal -->
            <template x-if="!spotlightCandidate">
                <div class="text-center py-6 space-y-3">
                    <div class="inline-flex p-4 rounded-3xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 animate-bounce-subtle">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <h3 class="text-xl font-extrabold text-white">Siap Untuk Pengumuman Formatur Terpilih</h3>
                    <p class="text-xs text-slate-400 max-w-md mx-auto">
                        Klik tombol <strong class="text-emerald-400">"Tampilkan Peringkat Ke-12"</strong> atau aktifkan <strong class="text-amber-400">"Auto Play"</strong> untuk memulai pertunjukan hasil live!
                    </p>
                </div>
            </template>

        </div>

        <!-- 12 Top Formatur Slots Grid -->
        <div>
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-extrabold uppercase tracking-widest text-slate-300 flex items-center">
                    <span class="w-2.5 h-2.5 bg-emerald-400 rounded-full mr-2"></span>
                    DAFTAR 12 FORMATUR TERPILIH (PERINGKAT 1 - 12)
                </h3>
                <div class="text-xs text-slate-400 font-bold">
                    Terbuka: <span class="text-emerald-400 font-mono" x-text="revealedCount">0</span> / 12 Formatur
                </div>
            </div>

            <!-- Grid 12 Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                <template x-for="rankNum in [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12]" :key="rankNum">
                    <div class="glass-card rounded-2xl p-4 relative transition-all duration-500 overflow-hidden cursor-pointer hover:border-slate-600"
                         @click="selectSlot(rankNum)"
                         :class="{
                             'gold-glow': isRevealed(rankNum) && rankNum === 1,
                             'silver-glow': isRevealed(rankNum) && rankNum === 2,
                             'bronze-glow': isRevealed(rankNum) && rankNum === 3,
                             'emerald-glow': isRevealed(rankNum) && rankNum > 3,
                             'opacity-60 border-dashed border-slate-800': !isRevealed(rankNum)
                         }">
                        
                        <!-- Revealed Slot Content -->
                        <template x-if="isRevealed(rankNum)">
                            <div class="space-y-3 reveal-flip">
                                <div class="flex items-start justify-between">
                                    <!-- Rank Badge -->
                                    <div class="px-2.5 py-1 rounded-xl text-[10px] font-black uppercase tracking-wider"
                                         :class="{
                                             'bg-amber-400 text-slate-950': rankNum === 1,
                                             'bg-slate-200 text-slate-950': rankNum === 2,
                                             'bg-amber-700 text-amber-100': rankNum === 3,
                                             'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30': rankNum > 3
                                         }">
                                        <template x-if="rankNum === 1"><span>🥇 Rank #1</span></template>
                                        <template x-if="rankNum === 2"><span>🥈 Rank #2</span></template>
                                        <template x-if="rankNum === 3"><span>🥉 Rank #3</span></template>
                                        <template x-if="rankNum > 3"><span x-text="'Rank #' + rankNum"></span></template>
                                    </div>

                                    <span class="text-[10px] font-mono font-bold text-slate-400" x-text="'No. ' + getCandidateByRank(rankNum).nomor_urut"></span>
                                </div>

                                <!-- Photo & Details -->
                                <div class="flex items-center space-x-3">
                                    <div class="w-12 h-12 rounded-xl bg-slate-900 border border-slate-700 overflow-hidden flex-shrink-0 flex items-center justify-center">
                                        <template x-if="getCandidateByRank(rankNum).foto">
                                            <img :src="getCandidateByRank(rankNum).foto" class="w-full h-full object-cover">
                                        </template>
                                        <template x-if="!getCandidateByRank(rankNum).foto">
                                            <span class="text-sm font-black text-emerald-400" x-text="getCandidateByRank(rankNum).nomor_urut"></span>
                                        </template>
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <h4 class="text-xs font-black text-white truncate leading-tight" x-text="getCandidateByRank(rankNum).nama"></h4>
                                        <p class="text-[10px] text-emerald-400 font-semibold leading-tight truncate mt-0.5" x-text="getCandidateByRank(rankNum).kelas"></p>
                                    </div>
                                </div>

                                <!-- Vote Tally Bar -->
                                <div class="pt-2 border-t border-slate-800/80 flex items-center justify-between text-xs">
                                    <span class="font-black text-white" x-text="getCandidateByRank(rankNum).votes + ' Suara'"></span>
                                    <span class="font-extrabold text-emerald-400 font-mono" x-text="getCandidateByRank(rankNum).percentage + '%'"></span>
                                </div>
                            </div>
                        </template>

                        <!-- Locked / Unrevealed Slot Content -->
                        <template x-if="!isRevealed(rankNum)">
                            <div class="py-4 text-center space-y-2">
                                <div class="w-10 h-10 mx-auto rounded-xl bg-slate-900/80 border border-slate-800 flex items-center justify-center text-slate-500 font-bold text-xs">
                                    🔒
                                </div>
                                <div class="text-xs font-black text-slate-400" x-text="'FORMATUR RANK #' + rankNum"></div>
                                <p class="text-[10px] text-slate-600 font-semibold">Terkunci...</p>
                            </div>
                        </template>

                    </div>
                </template>
            </div>
        </div>

        <!-- Footer Info -->
        <footer class="text-center py-4 text-xs text-slate-500 font-semibold border-t border-slate-900 flex flex-col sm:flex-row items-center justify-between gap-2">
            <div>Panitia Pemilihan Formatur IPM {{ date('Y') }}</div>
            <div class="flex items-center space-x-2">
                <a href="{{ route('admin.results') }}" class="hover:text-emerald-400 transition">&larr; Kembali ke Hasil Realtime</a>
                <span>|</span>
                <a href="{{ route('admin.dashboard') }}" class="hover:text-emerald-400 transition">Dashboard Admin</a>
            </div>
        </footer>

    </div>

    <!-- Script Handler -->
    <script>
        function revealShowHandler() {
            return {
                candidates: [],
                revealedCount: 0,
                spotlightCandidate: null,
                animatedVotes: 0,
                animatedPercentage: 0,
                autoPlay: false,
                autoPlayTimer: null,
                soundEnabled: true,
                audioCtx: null,

                init() {
                    this.fetchData();
                    this.setupKeyboardShortcuts();
                    this.initAmbientBg();
                    this.initVoices();
                },

                indonesianVoice: null,

                initVoices() {
                    if (!('speechSynthesis' in window)) return;
                    const updateVoice = () => {
                        const voices = window.speechSynthesis.getVoices();
                        if (!voices || voices.length === 0) return;

                        // Strictly find Indonesian voice
                        this.indonesianVoice = 
                            voices.find(v => v.lang === 'id-ID' || v.lang === 'id_ID' || v.lang === 'id') ||
                            voices.find(v => v.lang.toLowerCase().startsWith('id')) ||
                            voices.find(v => v.name.toLowerCase().includes('indonesia') || v.name.toLowerCase().includes('indonesian')) ||
                            voices.find(v => v.lang.toLowerCase().includes('id')) ||
                            null;
                    };

                    updateVoice();
                    window.speechSynthesis.onvoiceschanged = updateVoice;
                },

                fetchData() {
                    const self = this;
                    fetch('{{ route("admin.results.api") }}')
                        .then(res => res.json())
                        .then(data => {
                            if (data.success && data.ranking) {
                                // Filter top 12 formatur
                                self.candidates = data.ranking.slice(0, 12);
                            }
                        })
                        .catch(err => console.error("Error fetching candidates for reveal:", err));
                },

                // Reveal order: starts from Rank 12 down to Rank 1 (12 -> 1)
                getNextRankToReveal() {
                    if (this.revealedCount >= 12) return 1;
                    return 12 - this.revealedCount;
                },

                isRevealed(rankNum) {
                    // rankNum 12 is revealed at revealedCount >= 1, rank 11 at >= 2, ..., rank 1 at >= 12
                    const stepNeeded = 13 - rankNum;
                    return this.revealedCount >= stepNeeded;
                },

                getCandidateByRank(rankNum) {
                    return this.candidates.find(c => c.rank === rankNum) || {
                        nama: 'Calon Formatur',
                        nomor_urut: '00',
                        kelas: '-',
                        votes: 0,
                        percentage: 0
                    };
                },

                revealNext() {
                    if (this.revealedCount >= 12 || this.candidates.length === 0) {
                        this.stopAutoPlay();
                        return;
                    }

                    this.revealedCount++;
                    const currentRankRevealed = 13 - this.revealedCount;
                    const candidate = this.getCandidateByRank(currentRankRevealed);

                    this.setSpotlight(candidate);
                    this.playSound(currentRankRevealed);
                    this.speakCandidate(currentRankRevealed, candidate.nama);

                    // Festive Confetti on Ranks 1, 2, 3
                    if (typeof confetti !== 'undefined') {
                        if (currentRankRevealed === 1) {
                            // Grand Winner Celebration Confetti Cannon
                            const end = Date.now() + 2500;
                            const colors = ['#f59e0b', '#10b981', '#3b82f6', '#ec4899', '#ffffff'];

                            (function frame() {
                                confetti({
                                    particleCount: 8,
                                    angle: 60,
                                    spread: 60,
                                    origin: { x: 0 },
                                    colors: colors
                                });
                                confetti({
                                    particleCount: 8,
                                    angle: 120,
                                    spread: 60,
                                    origin: { x: 1 },
                                    colors: colors
                                });

                                if (Date.now() < end) {
                                    requestAnimationFrame(frame);
                                }
                            })();
                        } else if (currentRankRevealed === 2 || currentRankRevealed === 3) {
                            confetti({
                                particleCount: 90,
                                spread: 80,
                                origin: { y: 0.6 },
                                colors: currentRankRevealed === 2 ? ['#cbd5e1', '#94a3b8', '#ffffff'] : ['#d97706', '#f59e0b', '#fef3c7']
                            });
                        }
                    }
                },

                speakCandidate(rank, candidateName) {
                    if (!('speechSynthesis' in window) || !this.soundEnabled) return;

                    window.speechSynthesis.cancel(); // Stop any active speech

                    let text = '';
                    let rate = 0.88;
                    let pitch = 1.05;

                    if (rank === 1) {
                        text = `Dan... inilah saat yang paling ditunggu-tunggu! Peringkat pertama, sekaligus Ketua Formatur terpilih... adalah... ${candidateName}! Selamat ya, semoga amanah dan sukses selalu!`;
                        rate = 0.82;
                        pitch = 1.1;
                    } else if (rank === 2) {
                        text = `Selanjutnya, di posisi peringkat kedua, kita sambut... ${candidateName}! Keren, selamat ya!`;
                        rate = 0.86;
                        pitch = 1.08;
                    } else if (rank === 3) {
                        text = `Di posisi peringkat ketiga, ada... ${candidateName}! Luar biasa, selamat!`;
                        rate = 0.86;
                        pitch = 1.05;
                    } else if (rank <= 6) {
                        text = `Peringkat ke-${rank}... adalah... ${candidateName}. Selamat bergabung sebagai formatur terpilih!`;
                        rate = 0.88;
                        pitch = 1.02;
                    } else {
                        text = `Dan untuk peringkat ke-${rank}... ${candidateName}. Selamat ya!`;
                        rate = 0.90;
                        pitch = 1.0;
                    }

                    const utterance = new SpeechSynthesisUtterance(text);
                    utterance.lang = 'id-ID';
                    utterance.rate = rate;
                    utterance.pitch = pitch;
                    utterance.volume = 1.0;

                    // Ensure voice is strictly Indonesian
                    if (!this.indonesianVoice) {
                        const voices = window.speechSynthesis.getVoices();
                        this.indonesianVoice = 
                            voices.find(v => v.lang === 'id-ID' || v.lang === 'id_ID' || v.lang === 'id') ||
                            voices.find(v => v.lang.toLowerCase().startsWith('id')) ||
                            voices.find(v => v.name.toLowerCase().includes('indonesia') || v.name.toLowerCase().includes('indonesian')) ||
                            null;
                    }

                    if (this.indonesianVoice) {
                        utterance.voice = this.indonesianVoice;
                    }

                    setTimeout(() => {
                        window.speechSynthesis.speak(utterance);
                    }, 350);
                },

                selectSlot(rankNum) {
                    if (this.isRevealed(rankNum)) {
                        const candidate = this.getCandidateByRank(rankNum);
                        this.setSpotlight(candidate);
                        this.speakCandidate(rankNum, candidate.nama);
                    }
                },

                setSpotlight(candidate) {
                    this.spotlightCandidate = candidate;
                    this.animatedVotes = 0;
                    this.animatedPercentage = 0;

                    const targetVotes = candidate.votes;
                    const targetPercentage = candidate.percentage;
                    const duration = 1000; // ms
                    const steps = 30;
                    const stepDuration = duration / steps;
                    let currentStep = 0;

                    const timer = setInterval(() => {
                        currentStep++;
                        const progress = currentStep / steps;
                        this.animatedVotes = Math.round(targetVotes * progress);
                        this.animatedPercentage = (targetPercentage * progress).toFixed(1);

                        if (currentStep >= steps) {
                            this.animatedVotes = targetVotes;
                            this.animatedPercentage = targetPercentage;
                            clearInterval(timer);
                        }
                    }, stepDuration);
                },

                resetShow() {
                    this.stopAutoPlay();
                    if ('speechSynthesis' in window) {
                        window.speechSynthesis.cancel();
                    }
                    this.revealedCount = 0;
                    this.spotlightCandidate = null;
                },

                toggleAutoPlay() {
                    if (this.autoPlay) {
                        this.stopAutoPlay();
                    } else {
                        if (this.revealedCount >= 12) {
                            this.resetShow();
                        }
                        this.autoPlay = true;
                        this.revealNext();
                        this.autoPlayTimer = setInterval(() => {
                            if (this.revealedCount >= 12) {
                                this.stopAutoPlay();
                            } else {
                                this.revealNext();
                            }
                        }, 5500);
                    }
                },

                stopAutoPlay() {
                    this.autoPlay = false;
                    if (this.autoPlayTimer) {
                        clearInterval(this.autoPlayTimer);
                        this.autoPlayTimer = null;
                    }
                },

                toggleFullscreen() {
                    if (!document.fullscreenElement) {
                        document.documentElement.requestFullscreen().catch(err => console.log(err));
                    } else {
                        if (document.exitFullscreen) {
                            document.exitFullscreen();
                        }
                    }
                },

                setupKeyboardShortcuts() {
                    window.addEventListener('keydown', (e) => {
                        if (e.code === 'Space' || e.code === 'Enter') {
                            e.preventDefault();
                            this.revealNext();
                        }
                    });
                },

                playSound(rank) {
                    if (!this.soundEnabled) return;

                    try {
                        if (!this.audioCtx) {
                            this.audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                        }
                        
                        const ctx = this.audioCtx;
                        if (ctx.state === 'suspended') {
                            ctx.resume();
                        }

                        const now = ctx.currentTime;

                        // Tone synthesis helper
                        const playTone = (freq, type, startTime, duration, vol, detune = 0) => {
                            const osc = ctx.createOscillator();
                            const gain = ctx.createGain();
                            osc.type = type;
                            osc.frequency.setValueAtTime(freq, startTime);
                            osc.detune.setValueAtTime(detune, startTime);

                            gain.gain.setValueAtTime(0, startTime);
                            gain.gain.linearRampToValueAtTime(vol, startTime + 0.02);
                            gain.gain.exponentialRampToValueAtTime(0.0001, startTime + duration);

                            osc.connect(gain);
                            gain.connect(ctx.destination);

                            osc.start(startTime);
                            osc.stop(startTime + duration);
                        };

                        // Drum noise burst helper
                        const playNoiseBurst = (startTime, duration, vol) => {
                            const bufferSize = Math.floor(ctx.sampleRate * duration);
                            const buffer = ctx.createBuffer(1, bufferSize, ctx.sampleRate);
                            const data = buffer.getChannelData(0);
                            for (let i = 0; i < bufferSize; i++) {
                                data[i] = Math.random() * 2 - 1;
                            }
                            const noise = ctx.createBufferSource();
                            noise.buffer = buffer;

                            const filter = ctx.createBiquadFilter();
                            filter.type = 'highpass';
                            filter.frequency.setValueAtTime(1000, startTime);

                            const gain = ctx.createGain();
                            gain.gain.setValueAtTime(vol, startTime);
                            gain.gain.exponentialRampToValueAtTime(0.001, startTime + duration);

                            noise.connect(filter);
                            filter.connect(gain);
                            gain.connect(ctx.destination);

                            noise.start(startTime);
                            noise.stop(startTime + duration);
                        };

                        if (rank === 1) {
                            // 🎺 MAJESTIC GRAND VICTORY FANFARE FOR RANK 1 (Ketua Formatur)
                            playNoiseBurst(now, 0.35, 0.4); // Cymbal / Drum Roll

                            const fanfareNotes = [
                                { f: 261.63, t: 0.00, d: 0.25, type: 'triangle' },
                                { f: 329.63, t: 0.12, d: 0.25, type: 'triangle' },
                                { f: 392.00, t: 0.24, d: 0.30, type: 'triangle' },
                                { f: 523.25, t: 0.38, d: 0.40, type: 'sawtooth' },
                                { f: 659.25, t: 0.52, d: 0.50, type: 'sawtooth' },
                                { f: 783.99, t: 0.68, d: 0.60, type: 'sawtooth' },
                                { f: 1046.50, t: 0.85, d: 1.80, type: 'triangle' },
                            ];

                            fanfareNotes.forEach(n => {
                                playTone(n.f, n.type, now + n.t, n.d, 0.35);
                                playTone(n.f * 1.005, 'sine', now + n.t, n.d, 0.2, 5);
                            });

                            // Sparkling victory chimes
                            const chimes = [1046.5, 1318.5, 1567.98, 2093.0];
                            chimes.forEach((f, i) => {
                                playTone(f, 'sine', now + 1.0 + (i * 0.12), 0.6, 0.25);
                            });

                        } else if (rank === 2 || rank === 3) {
                            // 🥈🥉 FESTIVE PODIUM FANFARE (Ranks 2 & 3)
                            playNoiseBurst(now, 0.15, 0.25);

                            const notes = rank === 2 
                                ? [392.00, 523.25, 659.25, 783.99, 1046.50]
                                : [329.63, 392.00, 523.25, 659.25, 783.99];

                            notes.forEach((f, i) => {
                                const startTime = now + (i * 0.08);
                                const dur = (i === notes.length - 1) ? 1.0 : 0.25;
                                playTone(f, 'triangle', startTime, dur, 0.3);
                                playTone(f * 1.5, 'sine', startTime, dur, 0.15);
                            });
                        } else {
                            // ✨ FESTIVE REVEAL ARPEGGIO (Ranks 4 - 12)
                            playNoiseBurst(now, 0.08, 0.15);

                            const baseF = 261.63 * Math.pow(1.04, (12 - rank));
                            const arpeggio = [baseF, baseF * 1.25, baseF * 1.5, baseF * 2.0];

                            arpeggio.forEach((f, i) => {
                                const startTime = now + (i * 0.06);
                                const dur = (i === arpeggio.length - 1) ? 0.6 : 0.2;
                                playTone(f, 'sine', startTime, dur, 0.25);
                                playTone(f * 2, 'triangle', startTime, dur * 0.8, 0.1);
                            });
                        }
                    } catch (e) {
                        console.log("Audio play suppressed:", e);
                    }
                },

                initAmbientBg() {
                    const canvas = document.getElementById('ambientBg');
                    if (!canvas) return;
                    const ctx = canvas.getContext('2d');

                    let width = canvas.width = window.innerWidth;
                    let height = canvas.height = window.innerHeight;

                    window.addEventListener('resize', () => {
                        width = canvas.width = window.innerWidth;
                        height = canvas.height = window.innerHeight;
                    });

                    const particles = [];
                    for (let i = 0; i < 45; i++) {
                        particles.push({
                            x: Math.random() * width,
                            y: Math.random() * height,
                            r: Math.random() * 2.5 + 1,
                            dx: (Math.random() - 0.5) * 0.4,
                            dy: (Math.random() - 0.5) * 0.4,
                            alpha: Math.random() * 0.5 + 0.2
                        });
                    }

                    function draw() {
                        ctx.clearRect(0, 0, width, height);
                        particles.forEach(p => {
                            p.x += p.dx;
                            p.y += p.dy;
                            if (p.x < 0 || p.x > width) p.dx *= -1;
                            if (p.y < 0 || p.y > height) p.dy *= -1;

                            ctx.beginPath();
                            ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
                            ctx.fillStyle = `rgba(16, 185, 129, ${p.alpha})`;
                            ctx.fill();
                        });
                        requestAnimationFrame(draw);
                    }
                    draw();
                }
            };
        }
    </script>
</body>
</html>
