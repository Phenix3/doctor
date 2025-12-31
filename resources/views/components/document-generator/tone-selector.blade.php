@props([
    'toneLevels' => [],
    'invalid' => false,
    'placeholder' => 'Sélectionnez un niveau de formalité...'
])

@php
    $selectedTone = collect($toneLevels)->firstWhere('key', $attributes->get('wire:model.live') ? $this->tone_level ?? '' : '');
    
    $toneIcons = [
        'SIMPLE' => 'chat-bubble-left',
        'OFFICIAL' => 'building-office',
        'VERY_OFFICIAL' => 'building-library',
    ];
    
    $toneDescriptions = [
        'SIMPLE' => 'Langage administratif accessible et direct',
        'OFFICIAL' => 'Formalité administrative standard',
        'VERY_OFFICIAL' => 'Niveau institutionnel et ministériel',
    ];
    
    $toneColors = [
        'SIMPLE' => 'text-green-600 dark:text-green-400',
        'OFFICIAL' => 'text-blue-600 dark:text-blue-400',
        'VERY_OFFICIAL' => 'text-purple-600 dark:text-purple-400',
    ];
@endphp

<x-ui.dropdown position="bottom-start">
    <x-slot:button>
        <div @class([
            'w-full flex items-center justify-between px-3 py-2 text-left bg-white dark:bg-neutral-900 border rounded-box transition-colors duration-200',
            'border-black/10 dark:border-white/15 hover:border-black/15 dark:hover:border-white/20' => !$invalid,
            'border-red-600/30 border-2 dark:border-red-400/30' => $invalid,
        ])>
            <div class="flex items-center gap-3">
                @if($selectedTone)
                    <div class="flex items-center gap-3">
                        <x-ui.icon 
                            :name="$toneIcons[$selectedTone['key']] ?? 'chat-bubble-left'" 
                            :class="'w-5 h-5 ' . ($toneColors[$selectedTone['key']] ?? 'text-gray-600 dark:text-gray-400')" 
                        />
                        <div>
                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                {{ $selectedTone['label'] }}
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $toneDescriptions[$selectedTone['key']] ?? '' }}
                            </div>
                        </div>
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
        <div class="space-y-1">
            @foreach($toneLevels as $tone)
                <x-ui.dropdown.item 
                    wire:click="handleToneChange('{{ $tone['key'] }}')"
                    class="flex items-start gap-3 px-3 py-3 hover:bg-gray-50 dark:hover:bg-neutral-800 cursor-pointer"
                >
                    <x-ui.icon 
                        :name="$toneIcons[$tone['key']] ?? 'chat-bubble-left'" 
                        :class="'w-5 h-5 mt-0.5 ' . ($toneColors[$tone['key']] ?? 'text-gray-600 dark:text-gray-400')" 
                    />
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ $tone['label'] }}
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            {{ $toneDescriptions[$tone['key']] ?? '' }}
                        </div>
                    </div>
                    @if($selectedTone && $selectedTone['key'] === $tone['key'])
                        <x-ui.icon name="check" class="w-4 h-4 text-green-600 mt-0.5" />
                    @endif
                </x-ui.dropdown.item>
            @endforeach
        </div>
    </x-slot:menu>
</x-ui.dropdown>