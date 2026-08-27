@extends('layouts.admin', ['headerTitle' => 'Audit Log & Catatan Aktivitas System'])

@section('content')
<div class="space-y-6">
    
    <!-- Filter Bar -->
    <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
        <form method="GET" action="{{ route('admin.audit-logs') }}" class="flex flex-wrap items-center gap-3 w-full">
            <div class="relative min-w-[220px] flex-grow sm:flex-grow-0">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari user, aksi, deskripsi..."
                    class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-semibold focus:ring-2 focus:ring-emerald-600">
                <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>

            <select name="action" onchange="this.form.submit()" class="py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-semibold focus:ring-2 focus:ring-emerald-600">
                <option value="">-- Semua Jenis Aksi --</option>
                @foreach($actions as $act)
                    <option value="{{ $act }}" {{ request('action') == $act ? 'selected' : '' }}>{{ $act }}</option>
                @endforeach
            </select>

            @if(request()->anyFilled(['search', 'action']))
                <a href="{{ route('admin.audit-logs') }}" class="py-2.5 px-3 bg-slate-200 text-slate-700 hover:bg-slate-300 rounded-2xl text-xs font-bold transition">
                    Reset
                </a>
            @endif
        </form>
    </div>

    <!-- Logs Table -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-400 font-extrabold uppercase tracking-wider">
                    <tr>
                        <th class="py-4 px-4">Waktu</th>
                        <th class="py-4 px-4">Pengguna (User)</th>
                        <th class="py-4 px-4">Kode Aksi</th>
                        <th class="py-4 px-4">Keterangan / Detail Aktivitas</th>
                        <th class="py-4 px-4">IP Address</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($logs as $log)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3.5 px-4 font-mono text-slate-500 whitespace-nowrap">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                            <td class="py-3.5 px-4 font-extrabold text-slate-900 whitespace-nowrap">{{ $log->user }}</td>
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <span class="px-2.5 py-1 rounded-lg font-mono text-[10px] font-bold bg-slate-100 text-slate-800 border border-slate-200">
                                    {{ $log->action }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-slate-700 font-medium">{{ $log->description }}</td>
                            <td class="py-3.5 px-4 font-mono text-slate-400 text-[11px] whitespace-nowrap">{{ $log->ip ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-6 text-center text-slate-400">Tidak ada log aktivitas yang tercatat.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $logs->links() }}
        </div>
    </div>

</div>
@endsection
