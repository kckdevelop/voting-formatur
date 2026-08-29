@php
    $perPage = $perPage ?? 15;

    if ($perPage == 10) {
        $orientation = 'portrait';
        $cols = 2;
        $rows = 5;
        $qrSize = 92;
        $marginPrint = '6mm';

        $logoSize = 'w-6.5 h-6.5 text-[9px]';
        $titleSize = 'text-[11px]';
        $subTitleSize = 'text-[8px]';
        $labelSize = 'text-[7.5px]';
        $nameSize = 'text-xs sm:text-[13px]';
        $infoTextSize = 'text-[11px]';
        $tokenSize = 'text-xs px-2 py-0.5';
        $qrBoxSize = 'w-[98px] h-[98px]';
        $footerSize = 'text-[8px]';
        $cardPadding = 'p-2.5 sm:p-3';
        $bodyGap = 'my-1 gap-2';
    } elseif ($perPage == 20) {
        $orientation = 'landscape';
        $cols = 5;
        $rows = 4;
        $qrSize = 48;
        $marginPrint = '5mm';

        $logoSize = 'w-5 h-5 text-[8px]';
        $titleSize = 'text-[8.5px]';
        $subTitleSize = 'text-[6.5px]';
        $labelSize = 'text-[6.5px]';
        $nameSize = 'text-[9.5px]';
        $infoTextSize = 'text-[8.5px]';
        $tokenSize = 'text-[8.5px] px-1 py-0.2';
        $qrBoxSize = 'w-[56px] h-[56px]';
        $footerSize = 'text-[6.5px]';
        $cardPadding = 'p-1.5';
        $bodyGap = 'my-0.5 gap-1';
    } else { // 15 default
        $orientation = 'landscape';
        $cols = 5;
        $rows = 3;
        $qrSize = 64;
        $marginPrint = '5mm';

        $logoSize = 'w-5.5 h-5.5 text-[8.5px]';
        $titleSize = 'text-[9.5px]';
        $subTitleSize = 'text-[7px]';
        $labelSize = 'text-[7px]';
        $nameSize = 'text-[11px]';
        $infoTextSize = 'text-[9.5px]';
        $tokenSize = 'text-[9.5px] px-1 py-0.5';
        $qrBoxSize = 'w-[72px] h-[72px]';
        $footerSize = 'text-[7px]';
        $cardPadding = 'p-2';
        $bodyGap = 'my-1 gap-1';
    }
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Kartu QR Code Pemilih ({{ $perPage }} Kartu/Lembar - {{ ucfirst($orientation) }}) - {{ $schoolName }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

    <style>
        @page {
            size: A4 {{ $orientation }};
            margin: {{ $marginPrint }};
        }

        @media print {
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
            }

            html, body {
                width: 100% !important;
                height: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                background: white !important;
            }

            .no-print {
                display: none !important;
            }

            .print-container {
                max-width: 100% !important;
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .print-container > * {
                margin-top: 0 !important;
                margin-bottom: 0 !important;
            }

            .a4-page {
                width: 100% !important;
                height: 100% !important;
                max-height: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                display: grid !important;
                grid-template-columns: repeat({{ $cols }}, 1fr) !important;
                grid-template-rows: repeat({{ $rows }}, 1fr) !important;
                gap: 2mm !important;
                box-sizing: border-box !important;
                page-break-after: always !important;
                break-after: page !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
                background: transparent !important;
                border: none !important;
                box-shadow: none !important;
                border-radius: 0 !important;
            }

            .a4-page:last-child {
                page-break-after: auto !important;
                break-after: auto !important;
            }

            .voter-card {
                height: 100% !important;
                max-height: 100% !important;
                box-sizing: border-box !important;
                border: 1.5px solid #047857 !important;
                overflow: hidden !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
        }

        /* Prevent QRCodeJS duplicate canvas display */
        [id^="qrcode-"] canvas {
            display: none !important;
        }
        [id^="qrcode-"] img {
            display: block !important;
            margin: 0 auto !important;
            max-width: 100% !important;
            height: auto !important;
        }

        /* Screen Preview Styles */
        .a4-page {
            display: grid;
            grid-template-columns: repeat(1, 1fr);
            gap: 1rem;
        }
        @media (min-width: 640px) {
            .a4-page {
                grid-template-columns: repeat({{ min($cols, 2) }}, 1fr);
            }
        }
        @media (min-width: 1024px) {
            .a4-page {
                grid-template-columns: repeat({{ min($cols, 3) }}, 1fr);
                gap: 0.75rem;
            }
        }
        @media (min-width: 1280px) {
            .a4-page {
                grid-template-columns: repeat({{ $cols }}, 1fr);
                gap: 0.75rem;
            }
        }
    </style>
</head>
<body class="bg-slate-100 p-4 md:p-6 min-h-screen font-sans text-slate-800">

    <!-- Top Action & Filter Bar (hidden in print) -->
    <div class="no-print max-w-7xl mx-auto mb-6 bg-white p-5 rounded-3xl shadow-md border border-slate-200 flex flex-col md:flex-row items-center justify-between gap-4">
        <div>
            <h1 class="text-lg font-black text-slate-900 leading-tight">Cetak Kartu Pemilih QR Code (A4 {{ ucfirst($orientation) }} - {{ $perPage }} Kartu / Lembar)</h1>
            <p class="text-xs text-slate-500 font-semibold mt-0.5">
                Total terdaftar: <strong class="text-emerald-700">{{ count($students) }} siswa</strong> 
                @if(request('kelas')) | Kelas: <span class="bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded-md font-bold">{{ request('kelas') }}</span> @endif
            </p>
        </div>

        <!-- Filter by Class & Actions -->
        <div class="flex flex-wrap items-center gap-3 w-full md:w-auto justify-end">
            <!-- Filter Form -->
            <form method="GET" action="{{ route('admin.students.qr-cards') }}" class="flex flex-wrap items-center gap-3">
                
                <!-- Layout Dropdown -->
                <div class="flex items-center space-x-1.5">
                    <span class="text-xs font-bold text-slate-500">Tampilan:</span>
                    <select name="per_page" onchange="this.form.submit()" class="py-2 px-3 bg-emerald-50 border border-emerald-300 text-emerald-900 rounded-xl text-xs font-extrabold focus:ring-2 focus:ring-emerald-600 focus:outline-none shadow-sm cursor-pointer">
                        <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10 Kartu / Lembar (Portrait 2x5)</option>
                        <option value="15" {{ $perPage == 15 ? 'selected' : '' }}>15 Kartu / Lembar (Landscape 5x3)</option>
                        <option value="20" {{ $perPage == 20 ? 'selected' : '' }}>20 Kartu / Lembar (Landscape 5x4)</option>
                    </select>
                </div>

                <!-- Kelas Dropdown -->
                <div class="flex items-center space-x-1.5">
                    <span class="text-xs font-bold text-slate-500">Kelas:</span>
                    <select name="kelas" onchange="this.form.submit()" class="py-2 px-3 bg-slate-50 border border-slate-300 rounded-xl text-xs font-bold focus:ring-2 focus:ring-emerald-600 focus:outline-none cursor-pointer">
                        <option value="">-- Semua Kelas --</option>
                        @foreach($kelases as $k)
                            <option value="{{ $k }}" {{ request('kelas') == $k ? 'selected' : '' }}>Kelas {{ $k }}</option>
                        @endforeach
                    </select>
                </div>

                @if(request()->filled('kelas') || request()->filled('per_page'))
                    <a href="{{ route('admin.students.qr-cards') }}" class="py-2 px-3 bg-slate-200 text-slate-700 hover:bg-slate-300 rounded-xl text-xs font-bold transition">
                        Reset
                    </a>
                @endif
            </form>

            <a href="{{ route('admin.students.index') }}" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-xl text-xs font-bold transition">
                &larr; Kembali
            </a>

            <!-- Download as PNG Button -->
            <button id="btn-download-img" onclick="downloadAllAsImages()" class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-extrabold shadow-lg shadow-indigo-600/20 transition flex items-center">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                Unduh Gambar (PNG)
            </button>

            <!-- Direct Print Button -->
            <button onclick="window.print()" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-extrabold shadow-lg shadow-emerald-600/20 transition flex items-center">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Cetak / Print Kartu (A4)
            </button>
        </div>
    </div>

    <!-- Cards Sheet (Chunked dynamically per A4 page) -->
    <div class="print-container max-w-7xl mx-auto space-y-8 print:space-y-0">
        @php $totalChunkPages = count($students->chunk($perPage)); @endphp
        @forelse($students->chunk($perPage) as $pageIndex => $studentChunk)
            
            <div class="space-y-2">
                <!-- Helper header bar on screen preview -->
                <div class="no-print flex items-center justify-between px-2 text-xs font-extrabold text-slate-500">
                    <span>Lembar {{ $pageIndex + 1 }} dari {{ $totalChunkPages }} ({{ count($studentChunk) }} Kartu)</span>
                    <button onclick="downloadSinglePage('page-sheet-{{ $pageIndex }}', {{ $pageIndex + 1 }})" class="px-3 py-1.5 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 rounded-xl text-[11px] font-bold shadow-xs transition flex items-center">
                        <svg class="w-3.5 h-3.5 mr-1 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        Unduh Gambar Lembar Ini
                    </button>
                </div>

                <div id="page-sheet-{{ $pageIndex }}" class="a4-page bg-white p-3 md:p-5 rounded-3xl shadow-lg border border-slate-200">
                    @foreach($studentChunk as $student)
                        <div class="voter-card bg-white border-2 border-emerald-700/90 rounded-xl {{ $cardPadding }} flex flex-col justify-between relative overflow-hidden bg-gradient-to-b from-white to-slate-50/50 shadow-sm">
                            
                            <!-- Header Card -->
                            <div class="flex items-center justify-between pb-0.5 border-b border-slate-200">
                                <div class="flex items-center space-x-1.5 min-w-0">
                                    <div class="{{ $logoSize }} bg-emerald-800 rounded-md flex items-center justify-center text-white font-black flex-shrink-0 shadow-sm">
                                        IPM
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <h2 class="{{ $titleSize }} font-black text-emerald-900 leading-none truncate">
                                            {{ $schoolName }}
                                        </h2>
                                        <p class="{{ $subTitleSize }} font-extrabold text-slate-500 uppercase tracking-tight mt-0.5">
                                            KARTU PEMILIH E-VOTING IPM
                                        </p>
                                    </div>
                                </div>
                                <span class="text-[7.5px] font-extrabold bg-emerald-50 text-emerald-800 border border-emerald-200/80 px-1.5 py-0.5 rounded-full flex-shrink-0 ml-1">
                                    {{ date('Y') }}
                                </span>
                            </div>

                            <!-- Card Body: Details & QR -->
                            <div class="{{ $bodyGap }} flex items-center justify-between min-w-0">
                                <!-- Student Info -->
                                <div class="min-w-0 flex-1 space-y-0.5 pr-1">
                                    <div>
                                        <span class="{{ $labelSize }} uppercase font-bold text-slate-400 block leading-none">Nama Siswa</span>
                                        <p class="{{ $nameSize }} font-black text-slate-900 leading-tight truncate mt-0.5" title="{{ $student->nama }}">{{ $student->nama }}</p>
                                    </div>

                                    <div class="flex items-center space-x-2 pt-0.5">
                                        <div>
                                            <span class="{{ $labelSize }} uppercase font-bold text-slate-400 block leading-none">NIS</span>
                                            <p class="{{ $infoTextSize }} font-mono font-black text-emerald-700 leading-tight mt-0.5">{{ $student->nis }}</p>
                                        </div>
                                        <div>
                                            <span class="{{ $labelSize }} uppercase font-bold text-slate-400 block leading-none">Kelas</span>
                                            <p class="{{ $infoTextSize }} font-extrabold text-slate-800 leading-tight truncate max-w-[65px] bg-slate-100 px-1 py-0.2 rounded border border-slate-200 mt-0.5">{{ $student->kelas }}</p>
                                        </div>
                                    </div>

                                    <div class="pt-0.5">
                                        <span class="{{ $labelSize }} uppercase font-bold text-slate-400 block leading-none">Token Login</span>
                                        <p class="{{ $tokenSize }} font-mono font-black bg-emerald-50 text-emerald-950 rounded border border-emerald-300 inline-block leading-none mt-0.5 tracking-wider shadow-xs">
                                            {{ $student->plain_token ?? '******' }}
                                        </p>
                                    </div>
                                </div>

                                <!-- QR Code Container -->
                                <div class="{{ $qrBoxSize }} bg-white p-1 rounded-xl border border-emerald-300/80 shadow-xs flex flex-col items-center justify-center flex-shrink-0">
                                    <div id="qrcode-{{ $student->id }}" class="flex items-center justify-center w-full h-full"></div>
                                </div>
                            </div>

                            <!-- Card Footer -->
                            <div class="pt-0.5 border-t border-slate-100 flex items-center justify-between {{ $footerSize }} text-slate-400 font-semibold leading-none">
                                <span>Formatur {{ date('Y') }}</span>
                                <span class="text-emerald-700 font-bold">Login QR</span>
                            </div>

                        </div>
                    @endforeach
                </div>
            </div>

        @empty
            <div class="bg-white rounded-3xl p-12 text-center text-slate-400 border border-slate-200">
                Tidak ada data siswa untuk kelas yang dipilih.
            </div>
        @endforelse
    </div>

    <!-- Script to Generate QR Codes & Export Images -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @foreach($students as $student)
                (function() {
                    const payload = JSON.stringify({
                        nis: "{{ $student->nis }}",
                        token: "{{ $student->plain_token ?? '' }}"
                    });

                    const container = document.getElementById("qrcode-{{ $student->id }}");
                    if (container && typeof QRCode !== 'undefined') {
                        container.innerHTML = '';
                        new QRCode(container, {
                            text: payload,
                            width: {{ $qrSize }},
                            height: {{ $qrSize }},
                            colorDark : "#064e3b",
                            colorLight : "#ffffff",
                            correctLevel : QRCode.CorrectLevel.M
                        });
                    }
                })();
            @endforeach
        });

        async function downloadSinglePage(pageId, pageNum) {
            const page = document.getElementById(pageId);
            if (!page || typeof html2canvas === 'undefined') {
                alert('Library pemroses gambar belum siap. Silakan coba lagi.');
                return;
            }

            try {
                const canvas = await html2canvas(page, {
                    scale: 2.5,
                    useCORS: true,
                    backgroundColor: '#ffffff',
                    logging: false
                });

                const link = document.createElement('a');
                link.download = `Kartu_Pemilih_IPM_Lembar_${pageNum}.png`;
                link.href = canvas.toDataURL('image/png');
                link.click();
            } catch (err) {
                console.error(err);
                alert('Gagal mengunduh gambar kartu: ' + err.message);
            }
        }

        async function downloadAllAsImages() {
            if (typeof html2canvas === 'undefined') {
                alert('Library pemroses gambar belum siap. Silakan coba lagi.');
                return;
            }

            const btn = document.getElementById('btn-download-img');
            const originalHTML = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = `
                <svg class="animate-spin w-4 h-4 mr-1.5 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Memproses Gambar...
            `;

            const pages = document.querySelectorAll('.a4-page');
            try {
                for (let i = 0; i < pages.length; i++) {
                    const canvas = await html2canvas(pages[i], {
                        scale: 2.5,
                        useCORS: true,
                        backgroundColor: '#ffffff',
                        logging: false
                    });

                    const link = document.createElement('a');
                    link.download = `Kartu_Pemilih_IPM_Lembar_${i + 1}.png`;
                    link.href = canvas.toDataURL('image/png');
                    link.click();

                    if (pages.length > 1) {
                        await new Promise(resolve => setTimeout(resolve, 500));
                    }
                }
            } catch (err) {
                console.error(err);
                alert('Terjadi kesalahan saat memproses gambar: ' + err.message);
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalHTML;
            }
        }
    </script>
</body>
</html>
