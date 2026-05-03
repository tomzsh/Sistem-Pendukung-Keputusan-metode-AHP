<div class="fixed top-5 right-5 z-[100] w-full max-w-sm space-y-3 pointer-events-none">
    @foreach (['success', 'error', 'warning', 'info'] as $type)
        @if (session()->has($type))
            @php
                $config = match($type) {
                    'success' => ['icon' => 'check-circle', 'color' => 'bg-white dark:bg-zinc-800 border-zinc-200 dark:border-zinc-700', 'iconColor' => 'text-emerald-500'],
                    'error'   => ['icon' => 'x-circle', 'color' => 'bg-white dark:bg-zinc-800 border-zinc-200 dark:border-zinc-700', 'iconColor' => 'text-rose-500'],
                    'warning' => ['icon' => 'exclamation-triangle', 'color' => 'bg-white dark:bg-zinc-800 border-zinc-200 dark:border-zinc-700', 'iconColor' => 'text-amber-500'],
                    'info'    => ['icon' => 'information-circle', 'color' => 'bg-white dark:bg-zinc-800 border-zinc-200 dark:border-zinc-700', 'iconColor' => 'text-sky-500'],
                };
            @endphp

            <div x-data="{ show: true }"
                 x-show="show"
                 x-init="setTimeout(() => show = false, 5000)"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-2 sm:translate-y-0 sm:translate-x-8"
                 x-transition:enter-end="opacity-100 translate-x-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="pointer-events-auto relative flex items-center gap-3 p-4 rounded-xl border shadow-lg {{ $config['color'] }}">
                
                {{-- Icon --}}
                <div class="{{ $config['iconColor'] }}">
                    <flux:icon name="{{ $config['icon'] }}" variant="mini" />
                </div>

                {{-- Message --}}
                <div class="flex-1 text-sm font-medium text-zinc-800 dark:text-zinc-200">
                    {{ session($type) }}
                </div>

                {{-- Close Button --}}
                <button @click="show = false" class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300 transition-colors">
                    <flux:icon name="x-mark" variant="micro" />
                </button>
            </div>
        @endif
    @endforeach
</div>