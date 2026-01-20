@props(['selectionMode' => 'selectionMode', 'selectAll' => 'selectAll', 'toggleSelectAll' => 'toggleSelectAll'])

<th class="w-20 text-center cursor-pointer select-none hover:bg-gray-100 transition-colors group" @click="$dispatch('toggle-selection-mode')" title="Klik untuk memilih data">
    <div class="flex items-center justify-center">
        <template x-if="!{{ $selectionMode }}">
            <div class="flex items-center justify-center gap-2 text-gray-400 group-hover:text-indigo-600 transition-colors p-1">
                <span class="text-[10px] font-bold uppercase tracking-wider">Pilih</span>
                <x-ui.icon name="check-square" size="16" />
            </div>
        </template>
        <template x-if="{{ $selectionMode }}">
            <div class="flex items-center justify-center gap-1">
                <input type="checkbox" x-model="{{ $selectAll }}" @click.stop class="w-5 h-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 cursor-pointer" title="Pilih Semua">
                <button type="button" @click.stop="$dispatch('toggle-selection-mode', false)" class="p-1 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded transition-colors" title="Batalkan Pilih">
                    <x-ui.icon name="x" size="14" />
                </button>
            </div>
        </template>
    </div>
</th>
