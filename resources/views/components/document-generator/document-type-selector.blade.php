@props([
    'documentTypes' => [],
    'invalid' => false,
    'placeholder' => 'Sélectionnez un type de document...'
])

@php
    $selectedType = collect($documentTypes)->firstWhere('key', $attributes->get('wire:model.live') ? $this->document_type ?? '' : '');
    
    $typeIcons = [
        'ADMINISTRATIVE_APPLICATION' => 'document-text',
        'MOTIVATION_LETTER' => 'heart',
        'INTERNSHIP_REQUEST' => 'academic-cap',
        'HONOR_ATTESTATION' => 'shield-check',
        'COMPLAINT_LETTER' => 'exclamation-triangle',
        'ADMINISTRATIVE_ACT_REQUEST' => 'clipboard-document-list',
        'APPEAL_OR_POSTPONEMENT_LETTER' => 'arrow-path',
        'GENERIC_ADMINISTRATIVE_CORRESPONDENCE' => 'envelope',
    ];
    
    $typeDescriptions = [
        'ADMINISTRATIVE_APPLICATION' => 'Demandes officielles auprès des administrations',
        'MOTIVATION_LETTER' => 'Lettres de motivation pour emplois ou candidatures',
        'INTERNSHIP_REQUEST' => 'Demandes de stage pour étudiants',
        'HONOR_ATTESTATION' => 'Déclarations sur l\'honneur officielles',
        'COMPLAINT_LETTER' => 'Lettres de plainte formelles',
        'ADMINISTRATIVE_ACT_REQUEST' => 'Demandes d\'actes administratifs',
        'APPEAL_OR_POSTPONEMENT_LETTER' => 'Lettres de recours ou de report',
        'GENERIC_ADMINISTRATIVE_CORRESPONDENCE' => 'Correspondance administrative générale',
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
                @if($selectedType)
                    <div class="flex items-center gap-3">
                        <x-ui.icon 
                            :name="$typeIcons[$selectedType['key']] ?? 'document-text'" 
                            class="w-5 h-5 text-blue-600 dark:text-blue-400" 
                        />
                        <div>
                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                {{ $selectedType['label'] }}
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $typeDescriptions[$selectedType['key']] ?? '' }}
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
        <div class="max-h-80 overflow-y-auto">
            @foreach($documentTypes as $type)
                <x-ui.dropdown.item 
                    wire:click="handleDocumentTypeChange('{{ $type['key'] }}')"
                    class="flex items-start gap-3 px-3 py-3 hover:bg-gray-50 dark:hover:bg-neutral-800 cursor-pointer"
                >
                    <x-ui.icon 
                        :name="$typeIcons[$type['key']] ?? 'document-text'" 
                        class="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5" 
                    />
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ $type['label'] }}
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            {{ $typeDescriptions[$type['key']] ?? '' }}
                        </div>
                    </div>
                    @if($selectedType && $selectedType['key'] === $type['key'])
                        <x-ui.icon name="check" class="w-4 h-4 text-green-600 mt-0.5" />
                    @endif
                </x-ui.dropdown.item>
            @endforeach
        </div>
    </x-slot:menu>
</x-ui.dropdown>