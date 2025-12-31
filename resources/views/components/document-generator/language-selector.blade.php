@props([
    'languages' => [],
    'invalid' => false,
    'placeholder' => 'Sélectionnez une langue...'
])

@php
    $selectedLanguage = collect($languages)->firstWhere('key', $attributes->get('wire:model.live') ? $this->language ?? '' : '');
    
    $languageFlags = [
        'fr' => '🇫🇷',
        'en' => '🇬🇧',
    ];
    
    $languageDescriptions = [
        'fr' => 'Terminologie et conventions administratives françaises',
        'en' => 'English administrative terminology and conventions',
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
                @if($selectedLanguage)
                    <div class="flex items-center gap-3">
                        <span class="text-lg">
                            {{ $languageFlags[$selectedLanguage['key']] ?? '🌐' }}
                        </span>
                        <div>
                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                {{ $selectedLanguage['label'] }}
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $languageDescriptions[$selectedLanguage['key']] ?? '' }}
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
            @foreach($languages as $language)
                <x-ui.dropdown.item 
                    wire:click="handleLanguageChange('{{ $language['key'] }}')"
                    class="flex items-start gap-3 px-3 py-3 hover:bg-gray-50 dark:hover:bg-neutral-800 cursor-pointer"
                >
                    <span class="text-lg mt-0.5">
                        {{ $languageFlags[$language['key']] ?? '🌐' }}
                    </span>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ $language['label'] }}
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            {{ $languageDescriptions[$language['key']] ?? '' }}
                        </div>
                    </div>
                    @if($selectedLanguage && $selectedLanguage['key'] === $language['key'])
                        <x-ui.icon name="check" class="w-4 h-4 text-green-600 mt-0.5" />
                    @endif
                </x-ui.dropdown.item>
            @endforeach
        </div>
    </x-slot:menu>
</x-ui.dropdown>