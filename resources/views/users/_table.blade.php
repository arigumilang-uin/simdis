{{-- Dispatch total count to header --}}
<div x-data x-init="$dispatch('update-total-data', { total: {{ $users->total() }} })"></div>

    {{-- Bulk Action Toolbar --}}
    <div x-show="selected.length > 0" x-transition x-cloak class="bg-indigo-50 p-3 flex flex-col sm:flex-row justify-between items-center gap-3 mb-4 rounded-xl border border-indigo-100 shadow-sm">
        <div class="flex items-center gap-2">
            <span class="flex items-center justify-center w-6 h-6 rounded-full bg-indigo-600 text-white text-xs font-bold" x-text="selected.length"></span>
            <span class="text-sm font-medium text-indigo-900">User Terpilih</span>
        </div>
        <div class="flex flex-wrap gap-2">
            <button type="button" @click="submitBulkAction('{{ route('users.bulk-activate') }}', 'Aktifkan user terpilih?')" class="btn btn-sm btn-white text-emerald-600 border-emerald-200 hover:bg-emerald-50">
                <x-ui.icon name="user-check" size="14" />
                Aktifkan
            </button>
            <button type="button" @click="submitBulkAction('{{ route('users.bulk-deactivate') }}', 'Nonaktifkan user terpilih?')" class="btn btn-sm btn-white text-orange-600 border-orange-200 hover:bg-orange-50">
                <x-ui.icon name="user-x" size="14" />
                Suspend
            </button>
            <button type="button" @click="submitBulkAction('{{ route('users.bulk-delete') }}', 'Hapus user terpilih? Tindakan ini tidak dapat dibatalkan!')" class="btn btn-sm btn-white text-red-600 border-red-200 hover:bg-red-50">
                <x-ui.icon name="trash" size="14" />
                Hapus
            </button>
        </div>
    </div>

    {{-- Hidden Bulk Action Form --}}
    <form id="bulk-action-form" method="POST" style="display: none;">
        @csrf
        <input type="hidden" name="ids" :value="selected.join(',')">
    </form>

    {{-- Table --}}
    <div class="table-container min-h-[300px]"> {{-- min-h added to prevent dropdown cut-off at bottom --}}
        <table class="table">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Keterangan</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th class="w-20 text-center cursor-pointer select-none hover:bg-gray-100 transition-colors group" @click="toggleSelectionMode()" title="Klik untuk memilih data">
                        <div class="flex items-center justify-center">
                            <template x-if="!selectionMode">
                                <div class="flex items-center justify-center gap-2 text-gray-400 group-hover:text-indigo-600 transition-colors p-1">
                                    <span class="text-[10px] font-bold uppercase tracking-wider">Pilih</span>
                                    <x-ui.icon name="check-square" size="16" />
                                </div>
                            </template>
                            <template x-if="selectionMode">
                                <div class="flex items-center justify-center gap-1">
                                    <input type="checkbox" x-model="selectAll" @change="toggleSelectAll()" @click.stop class="w-5 h-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 cursor-pointer" title="Pilih Semua">
                                    <button type="button" @click.stop="selectionMode = false" class="p-1 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded transition-colors" title="Batalkan Pilih">
                                        <x-ui.icon name="x" size="14" />
                                    </button>
                                </div>
                            </template>
                        </div>
                    </th>
                </tr>
            </thead>
            <tbody>
                @forelse($users ?? [] as $user)
                    <tr :class="{ 'bg-indigo-50/40': selected.includes('{{ $user->id }}') }">
                        <td class="font-medium text-gray-800">{{ $user->username }}</td>
                        <td class="text-gray-600 text-sm">{{ $user->nama ?? '-' }}</td>
                        <td class="text-gray-500">{{ $user->email ?? '-' }}</td>
                        <td>
                            <span class="badge badge-primary">{{ $user->role->nama_role ?? '-' }}</span>
                        </td>
                        <td class="text-center relative">
                            {{-- Normal Mode: Kebab Dropdown --}}
                            <div x-show="!selectionMode" 
                                 x-data="{
                                     open: false,
                                     timer: null,
                                     isLongPress: false,
                                     
                                     startPress() {
                                         this.isLongPress = false;
                                         this.timer = setTimeout(() => {
                                             this.isLongPress = true;
                                             // Trigger Selection Mode
                                             this.toggleSelectionMode();
                                             // Add this row to selected
                                             if (!this.selected.includes('{{ $user->id }}')) {
                                                 this.selected.push('{{ $user->id }}');
                                             }
                                             // Haptic feedback if available (for mobile)
                                             if (navigator.vibrate) navigator.vibrate(50);
                                         }, 500);
                                     },
                                     
                                     endPress() {
                                         clearTimeout(this.timer);
                                     },
                                     
                                     toggle() {
                                         if (this.isLongPress) return;
                                         if (this.open) { this.open = false; return; }
                                         this.open = true;
                                         this.$nextTick(() => {
                                             const trigger = this.$refs.trigger.getBoundingClientRect();
                                             const menu = this.$refs.menu;
                                             
                                             // Calculate Right Alignment relative to viewport
                                             let left = trigger.right - menu.offsetWidth;
                                             let top = trigger.bottom + 2; // + padding
                                             
                                             // Check bottom overflow
                                             if (window.innerHeight - trigger.bottom < menu.offsetHeight + 20) {
                                                 top = trigger.top - menu.offsetHeight - 2;
                                             }
                                             
                                             menu.style.top = `${top}px`;
                                             menu.style.left = `${left}px`;
                                         });
                                     }
                                 }" 
                                 @scroll.window="open = false"
                                 @resize.window="open = false"
                                 class="relative inline-block text-left"
                            >
                                <button 
                                    x-ref="trigger" 
                                    @click="toggle()" 
                                    @mousedown="startPress()"
                                    @touchstart="startPress()"
                                    @mouseup="endPress()"
                                    @mouseleave="endPress()"
                                    @touchend="endPress()"
                                    type="button" 
                                    class="p-1.5 text-gray-400 rounded-lg hover:bg-gray-100 hover:text-gray-600 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 select-none"
                                >
                                    <x-ui.icon name="more-horizontal" size="18" />
                                </button>
                                
                                <template x-teleport="body">
                                    <div x-show="open" 
                                         x-ref="menu"
                                         @click.outside="open = false"
                                         style="position: fixed; z-index: 9999; display: none;"
                                         x-transition:enter="transition ease-out duration-100"
                                         x-transition:enter-start="transform opacity-0 scale-95"
                                         x-transition:enter-end="transform opacity-100 scale-100"
                                         x-transition:leave="transition ease-in duration-75"
                                         x-transition:leave-start="transform opacity-100 scale-100"
                                         x-transition:leave-end="transform opacity-0 scale-95"
                                         class="w-36 origin-top-right rounded-xl bg-white shadow-xl ring-1 ring-black ring-opacity-5 focus:outline-none border border-gray-100"
                                    >
                                        <div class="py-1">
                                            <a href="{{ route('users.edit', $user->id) }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-indigo-600 transition-colors">
                                                <x-ui.icon name="edit" size="14" />
                                                Edit
                                            </a>
                                            <div class="border-t border-gray-100 my-1"></div>
                                            <form action="{{ route('users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Hapus user {{ $user->username }}?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="flex w-full items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50 hover:text-red-700 transition-colors">
                                                    <x-ui.icon name="trash" size="14" />
                                                    Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            {{-- Selection Mode: Checkbox --}}
                            <div x-show="selectionMode" style="display: none;">
                                <input type="checkbox" value="{{ $user->id }}" x-model="selected" class="w-5 h-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 cursor-pointer">
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            <x-ui.empty-state 
                                icon="users" 
                                title="Tidak Ada User" 
                                message="Belum ada user yang terdaftar." 
                            />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    
    @if(method_exists($users, 'hasPages') && $users->hasPages())
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mt-4">
            <p class="text-sm text-gray-500">Menampilkan {{ $users->firstItem() }} - {{ $users->lastItem() }} dari {{ $users->total() }}</p>
            <div class="pagination">
                {{-- Previous --}}
                @if($users->onFirstPage())
                    <span class="pagination-btn" disabled>
                        <x-ui.icon name="chevron-left" size="16" />
                    </span>
                @else
                    <a href="{{ $users->previousPageUrl() }}" class="pagination-btn">
                        <x-ui.icon name="chevron-left" size="16" />
                    </a>
                @endif
                
                {{-- Next --}}
                @if($users->hasMorePages())
                    <a href="{{ $users->nextPageUrl() }}" class="pagination-btn">
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
    
    <script>
        function submitBulkAction(url, message) {
            if (confirm(message)) {
                const form = document.getElementById('bulk-action-form');
                form.action = url;
                form.submit();
            }
        }
    </script>
