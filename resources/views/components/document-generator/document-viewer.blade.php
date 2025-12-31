@props([
    'content' => '',
    'metadata' => []
])

<div class="bg-gray-50 dark:bg-neutral-800 rounded-lg border border-gray-200 dark:border-neutral-700 overflow-hidden">
    {{-- Document Header --}}
    <div class="bg-white dark:bg-neutral-900 px-4 py-3 border-b border-gray-200 dark:border-neutral-700">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <x-ui.icon name="document-check" class="w-4 h-4 text-green-600 dark:text-green-400" />
                <span class="text-sm font-medium text-gray-900 dark:text-white">
                    Document Généré
                </span>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs text-gray-500 dark:text-gray-400">
                    Prêt pour téléchargement
                </span>
                <div class="w-2 h-2 bg-green-400 rounded-full"></div>
            </div>
        </div>
    </div>

    {{-- Document Content --}}
    <div class="p-6">
        <div class="bg-white dark:bg-neutral-900 rounded-lg shadow-sm border border-gray-200 dark:border-neutral-700 min-h-[500px]">
            {{-- Document Paper Simulation --}}
            <div class="p-8 max-w-none">
                @if($content)
                    <div class="prose prose-sm max-w-none dark:prose-invert font-serif leading-relaxed">
                        {!! nl2br(e($content)) !!}
                    </div>
                @else
                    <div class="flex items-center justify-center h-64 text-gray-500 dark:text-gray-400">
                        <div class="text-center">
                            <x-ui.icon name="document-text" class="w-12 h-12 mx-auto mb-4 text-gray-300 dark:text-gray-600" />
                            <p class="text-sm">Aucun document généré</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Document Footer with Metadata --}}
    @if($content)
        <div class="bg-white dark:bg-neutral-900 px-4 py-3 border-t border-gray-200 dark:border-neutral-700">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs text-gray-500 dark:text-gray-400">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <x-ui.icon name="document-text" class="w-3 h-3" />
                        <span>Caractères: {{ strlen($content) }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <x-ui.icon name="chat-bubble-left" class="w-3 h-3" />
                        <span>Mots: {{ str_word_count(strip_tags($content)) }}</span>
                    </div>
                </div>
                
                @if(!empty($metadata))
                    <div class="space-y-1">
                        @if(isset($metadata['country']))
                            <div class="flex items-center gap-2">
                                <x-ui.icon name="globe-alt" class="w-3 h-3" />
                                <span>Pays: {{ $metadata['country'] }}</span>
                            </div>
                        @endif
                        @if(isset($metadata['document_type']))
                            <div class="flex items-center gap-2">
                                <x-ui.icon name="clipboard-document-list" class="w-3 h-3" />
                                <span>Type: {{ $metadata['document_type'] }}</span>
                            </div>
                        @endif
                        @if(isset($metadata['tone_level']))
                            <div class="flex items-center gap-2">
                                <x-ui.icon name="adjustments-horizontal" class="w-3 h-3" />
                                <span>Ton: {{ $metadata['tone_level'] }}</span>
                            </div>
                        @endif
                        @if(isset($metadata['generated_at']))
                            <div class="flex items-center gap-2">
                                <x-ui.icon name="clock" class="w-3 h-3" />
                                <span>Généré: {{ $metadata['generated_at'] }}</span>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
            
            {{-- Action Buttons --}}
            <div class="flex items-center justify-between mt-4 pt-3 border-t border-gray-200 dark:border-neutral-700">
                <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                    <x-ui.icon name="shield-check" class="w-4 h-4 text-green-600 dark:text-green-400" />
                    <span>Document vérifié et prêt pour usage officiel</span>
                </div>
                
                <div class="flex items-center gap-2">
                    <x-ui.button 
                        onclick="window.print()"
                        variant="ghost"
                        size="sm"
                    >
                        <x-ui.icon name="printer" />
                        Imprimer
                    </x-ui.button>
                    
                    <x-ui.button 
                        onclick="copyToClipboard()"
                        variant="ghost"
                        size="sm"
                        x-data="{ copied: false }"
                        x-on:click="
                            navigator.clipboard.writeText('{{ addslashes($content) }}');
                            copied = true;
                            setTimeout(() => copied = false, 2000);
                        "
                    >
                        <x-ui.icon name="clipboard" x-show="!copied" />
                        <x-ui.icon name="check" x-show="copied" class="text-green-600" />
                        <span x-text="copied ? 'Copié!' : 'Copier'"></span>
                    </x-ui.button>
                </div>
            </div>
        </div>
    @endif
</div>

<style>
@media print {
    .bg-gray-50,
    .bg-neutral-800,
    .border,
    .shadow-sm {
        background: white !important;
        border: none !important;
        box-shadow: none !important;
    }
    
    .dark\:bg-neutral-900,
    .dark\:bg-neutral-800 {
        background: white !important;
    }
    
    .text-gray-500,
    .text-gray-400,
    .dark\:text-gray-400,
    .dark\:text-gray-300 {
        color: black !important;
    }
    
    button {
        display: none !important;
    }
}
</style>