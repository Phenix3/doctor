@props([
    'countries' => [],
    'invalid' => false,
    'placeholder' => 'Sélectionnez un pays...'
])

@php
    $selectedCountry = collect($countries)->firstWhere('key', $attributes->get('wire:model.live') ? $this->country ?? '' : '');
@endphp

<x-ui.dropdown position="bottom-start">
    <x-slot:button>
        <div @class([
            'w-full flex items-center justify-between px-3 py-2 text-left bg-white dark:bg-neutral-900 border rounded-box transition-colors duration-200',
            'border-black/10 dark:border-white/15 hover:border-black/15 dark:hover:border-white/20' => !$invalid,
            'border-red-600/30 border-2 dark:border-red-400/30' => $invalid,
        ])>
            <div class="flex items-center gap-3">
                @if($selectedCountry)
                    <div class="flex items-center gap-2">
                        <div class="w-5 h-5 rounded-sm bg-gradient-to-br from-green-400 to-blue-500 flex items-center justify-center text-white text-xs font-bold">
                            {{ strtoupper(substr($selectedCountry['label'], 0, 2)) }}
                        </div>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ $selectedCountry['label'] }}
                        </span>
                    </div>
                @else
                    <span class="text-sm text-gray-500 dark:text-gray-400">
                        {{ $placeholder }}
                    </span>
                @endif
            </div>
            <x-ui.icon name="chevron-down" class="w-4 h-4 text-gray-400 transition-transform duration-200" />
        </div>
    </x-slot:button>

    <x-slot:menu>
        <div class="max-h-64 overflow-y-auto">
            @foreach($countries as $country)
                <x-ui.dropdown.item 
                    wire:click="handleCountryChange('{{ $country['key'] }}')"
                    class="flex items-center gap-3 px-3 py-2 hover:bg-gray-50 dark:hover:bg-neutral-800 cursor-pointer"
                >
                    <div class="w-5 h-5 rounded-sm bg-gradient-to-br from-green-400 to-blue-500 flex items-center justify-center text-white text-xs font-bold">
                        {{ strtoupper(substr($country['label'], 0, 2)) }}
                    </div>
                    <span class="text-sm text-gray-900 dark:text-white">
                        {{ $country['label'] }}
                    </span>
                    @if($selectedCountry && $selectedCountry['key'] === $country['key'])
                        <x-ui.icon name="check" class="w-4 h-4 text-green-600 ml-auto" />
                    @endif
                </x-ui.dropdown.item>
            @endforeach
        </div>
    </x-slot:menu>
</x-ui.dropdown>