<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'E-Voting IPM - SMK Muhammadiyah 1 Bantul' }}</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS CDN for instant styling fallback -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        muh: {
                            50: '#ecfdf5',
                            100: '#d1fae5',
                            500: '#10b981',
                            600: '#059669',
                            700: '#047857',
                            800: '#065f46',
                            900: '#064e3b',
                        }
                    },
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- QR scanner scripts are loaded only on student-login page via @push('scripts') -->
    
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="h-full flex flex-col antialiased text-slate-800 bg-slate-50">

    <!-- Header Navigation -->
    <header class="bg-gradient-to-r from-emerald-800 via-emerald-700 to-emerald-900 text-white shadow-lg sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <!-- Logo & School Name -->
                <div class="flex items-center space-x-3">
                    <div class="w-12 h-12 bg-white rounded-xl p-1.5 shadow-md flex items-center justify-center">
                        @if(isset($logoPath) && $logoPath)
                            <img src="{{ asset('storage/' . $logoPath) }}" alt="Logo" class="max-h-full max-w-full object-contain">
                        @else
                            <!-- Muhammadiyah Emblem Icon SVG -->
                            <svg class="w-9 h-9 text-emerald-700" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2L15 8L21 9L17 14L18 20L12 17L6 20L7 14L3 9L9 8L12 2Z" />
                            </svg>
                        @endif
                    </div>
                    <div>
                        <h1 class="text-lg font-bold tracking-tight text-white leading-tight">
                            {{ $schoolName ?? 'SMK Muhammadiyah 1 Bantul' }}
                        </h1>
                        <p class="text-xs text-emerald-200 font-medium">
                            {{ $electionName ?? 'Pemilihan Ketua & Formatur IPM' }}
                        </p>
                    </div>
                </div>

                <!-- User Profile / Logout -->
                @auth('student')
                    <div class="flex items-center space-x-4">
                        <div class="hidden sm:block text-right">
                            <p class="text-sm font-bold text-white leading-tight">
                                {{ Auth::guard('student')->user()->nama }}
                            </p>
                            <p class="text-xs text-emerald-200 font-medium">
                                Kelas: {{ Auth::guard('student')->user()->kelas }} | NIS: {{ Auth::guard('student')->user()->nis }}
                            </p>
                        </div>
                        <form action="{{ route('student.logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="inline-flex items-center px-3.5 py-2 border border-emerald-500/40 rounded-xl text-xs font-semibold text-white bg-emerald-800/80 hover:bg-emerald-900 focus:outline-none transition shadow-sm">
                                <svg class="w-4 h-4 mr-1.5 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                </svg>
                                Keluar
                            </button>
                        </form>
                    </div>
                @endauth
            </div>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="flex-grow">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 py-6 mt-12">
        <div class="max-w-7xl mx-auto px-4 text-center">
            <p class="text-xs text-slate-500">
                &copy; {{ date('Y') }} Panitia Pemilihan Ketua IPM — {{ $schoolName ?? 'SMK Muhammadiyah 1 Bantul' }}.
            </p>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
