
    {{-- Table --}}
    <div class="card">
        <div class="card-header">
            <span class="card-title">Aktivitas Terbaru</span>
            <span class="badge badge-primary">Total: {{ $logs->total() ?? 0 }} Record</span>
        </div>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th class="w-44">Waktu & Tanggal</th>
                        <th class="w-32 text-center">Jenis</th>
                        <th class="w-64">User</th>
                        <th>Keterangan Aktivitas</th>
                        <th class="w-24 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs ?? [] as $log)
                        <tr>
                            <td>
                                <div class="font-medium text-gray-700">{{ $log->created_at->format('d M Y') }}</div>
                                <div class="text-[10px] text-gray-400 font-mono">{{ $log->created_at->format('H:i:s') }} WIB</div>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-info">{{ $log->log_name }}</span>
                            </td>
                            <td>
                                <div class="font-bold text-gray-700">{{ $log->causer->username ?? 'System' }}</div>
                                <div class="text-xs text-gray-500">{{ $log->causer->nama ?? '-' }}</div>
                            </td>
                            <td class="max-w-md">
                                <p class="text-xs text-gray-600 italic truncate">"{{ $log->description }}"</p>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('audit.activity.show', $log->id) }}" class="btn btn-sm btn-outline text-indigo-600 border-indigo-200 hover:bg-indigo-50">
                                    <x-ui.icon name="eye" size="14" />
                                    <span>Lihat</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <x-ui.empty-state 
                                    icon="clock" 
                                    title="Data Log Tidak Ditemukan" 
                                    description="Belum ada log aktivitas yang tercatat di sistem." 
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if(isset($logs) && $logs->hasPages())
        <div class="card-body border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-4">
             <p class="text-sm text-gray-500">Menampilkan {{ $logs->firstItem() }} - {{ $logs->lastItem() }} dari {{ $logs->total() }}</p>
             <div class="pagination">
                {{-- Previous --}}
                @if($logs->onFirstPage())
                    <span class="pagination-btn" disabled>
                        <x-ui.icon name="chevron-left" size="16" />
                    </span>
                @else
                    <a href="{{ $logs->previousPageUrl() }}" class="pagination-btn">
                        <x-ui.icon name="chevron-left" size="16" />
                    </a>
                @endif
                
                {{-- Next --}}
                @if($logs->hasMorePages())
                    <a href="{{ $logs->nextPageUrl() }}" class="pagination-btn">
                        <x-ui.icon name="chevron-right" size="16" />
                    </a>
                @else
                    <span class="pagination-btn" disabled>
                        <x-ui.icon name="chevron-right" size="16" />
                    </span>
                @endif
            </div>
        </div>
        @endif
    </div>
