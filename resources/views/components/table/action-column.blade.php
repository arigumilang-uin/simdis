@props([
    'id', 
    'selectionMode' => 'selectionMode', 
    'selected' => 'selected',
    'allowSelection' => true
])

<td class="text-center relative">
    @if($allowSelection)
    {{-- Selection Mode: Checkbox --}}
    <template x-if="{{ $selectionMode }}">
        <div class="flex justify-center">
            <input type="checkbox" value="{{ $id }}" x-model="{{ $selected }}" class="w-5 h-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 cursor-pointer">
        </div>
    </template>
    @endif

    {{-- Normal Mode: Kebab Dropdown --}}
    <template x-if="{{ $allowSelection ? '!' . $selectionMode : 'true' }}">
        <div 
             x-data="{ 
                 open: false,
                 timer: null,
                 isLongPress: false,
                 
                 startPress() {
                     if (!{{ $allowSelection ? 'true' : 'false' }}) return;
                     this.isLongPress = false;
                     this.timer = setTimeout(() => {
                         this.isLongPress = true;
                         $dispatch('enter-selection', { id: '{{ $id }}' });
                     }, 500);
                 },
                 
                 endPress() {
                     if (this.timer) clearTimeout(this.timer);
                 },

                 toggle() {
                     if (this.isLongPress) return;
                     if (this.open) { this.open = false; return; }
                     this.open = true;
                     this.$nextTick(() => {
                         const trigger = this.$refs.trigger.getBoundingClientRect();
                         const menu = this.$refs.menu;
                         
                         let left = trigger.right - menu.offsetWidth;
                         let top = trigger.bottom + 2; 
                         
                         if (window.innerHeight - trigger.bottom < menu.offsetHeight + 20) {
                             top = trigger.top - menu.offsetHeight - 2;
                         }
                         
                         menu.style.top = `${top}px`;
                         menu.style.left = `${left}px`;
                         menu.style.zIndex = '99999'; // Ensure it's on top
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
                @touchstart.passive="startPress()"
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
                     class="w-auto min-w-[150px] max-w-[200px] origin-top-right rounded-xl bg-white shadow-xl ring-1 ring-black ring-opacity-5 focus:outline-none border border-gray-100"
                >
                    <div class="py-1">
                        {{ $slot }}
                    </div>
                </div>
            </template>
        </div>
    </template>
</td>
