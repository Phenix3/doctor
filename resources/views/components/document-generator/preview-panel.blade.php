@props([
    'content' => ''
])

<div class="bg-gray-50 dark:bg-neutral-800 rounded-lg border border-gray-200 dark:border-neutral-700 overflow-hidden">
    {{-- Preview Header --}}
    <div class="bg-white dark:bg-neutral-900 px-4 py-3 border-b border-gray-200 dark:border-neutral-700">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <x-ui.icon name="eye" class="w-4 h-4 text-blue-600 dark:text-blue-400" />
                <span class="text-sm font-medium text-gray-900 dark:text-white">
                    Aperçu du Document
                </span>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs text-gray-500 dark:text-gray-400">
                    Prévisualisation
                </span>
                <div class="w-2 h-2 bg-orange-400 rounded-full animate-pulse"></div>
            </div>
        </div>
    </div>

    {{-- Preview Content --}}
    <div class="p-6">
        <div class="bg-white dark:bg-neutral-900 rounded-lg shadow-sm border border-gray-200 dark:border-neutral-700 min-h-[400px]">
            {{-- Document Paper Simulation --}}
            <div class="p-8 max-w-none">
                @if($content)
                    <div class="prose prose-sm max-w-none dark:prose-invert">
                        {!! nl2br(e($content)) !!}
                    </div>
                @else
                    <div class="flex items-center justify-center h-64 text-gray-500 dark:text-gray-400">
                        <div class="text-center">
                            <x-ui.icon name="document-text" class="w-12 h-12 mx-auto mb-4 text-gray-300 dark:text-gray-600" />
                            <p class="text-sm">L'aperçu du document apparaîtra ici</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Preview Footer --}}
    @if($content)
        <div class="bg-white dark:bg-neutral-900 px-4 py-3 border-t border-gray-200 dark:border-neutral-700">
            <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                <div class="flex items-center gap-4">
                    <span>Caractères: {{ strlen($content) }}</span>
                    <span>Mots: {{ str_word_count(strip_tags($content)) }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <x-ui.icon name="information-circle" class="w-4 h-4" />
                    <span>Ceci est un aperçu. Le document final peut différer légèrement.</span>
                </div>
            </div>
        </div>
    @endif
</div>