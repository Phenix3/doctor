<div class="max-w-7xl mx-auto p-6 space-y-8" x-data="documentGenerator()">
    {{-- Header --}}
    <div class="text-center space-y-4">
        <x-ui.heading level="h1" size="2xl" class="text-gray-900 dark:text-white">
            Générateur de Documents Administratifs Africains
        </x-ui.heading>
        <x-ui.text class="text-lg text-gray-600 dark:text-gray-300 max-w-3xl mx-auto">
            Créez des documents administratifs professionnels adaptés aux conventions locales de votre pays.
        </x-ui.text>
    </div>

    {{-- Main Form --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Configuration Panel --}}
        <div class="lg:col-span-1 space-y-6">
            <x-ui.card class="p-6">
                <x-ui.heading level="h2" size="lg" class="mb-6">
                    Configuration du Document
                </x-ui.heading>

                <div class="space-y-6">
                    {{-- Country Selection --}}
                    <x-ui.field>
                        <x-ui.label data-slot="label" for="country" :required="true">
                            Pays
                        </x-ui.label>
                        <x-document-generator.country-selector 
                            wire:model.live="country"
                            :countries="$this->countries"
                            :invalid="isset($validationErrors['country'])"
                        />
                        @if(isset($validationErrors['country']))
                            <x-ui.error data-slot="error">
                                {{ $validationErrors['country'][0] }}
                            </x-ui.error>
                        @endif
                    </x-ui.field>

                    {{-- Document Type Selection --}}
                    <x-ui.field>
                        <x-ui.label data-slot="label" for="document_type" :required="true">
                            Type de Document
                        </x-ui.label>
                        <x-document-generator.document-type-selector 
                            wire:model.live="document_type"
                            :document-types="$this->documentTypes"
                            :invalid="isset($validationErrors['document_type'])"
                        />
                        @if(isset($validationErrors['document_type']))
                            <x-ui.error data-slot="error">
                                {{ $validationErrors['document_type'][0] }}
                            </x-ui.error>
                        @endif
                    </x-ui.field>

                    {{-- Tone Level Selection --}}
                    <x-ui.field>
                        <x-ui.label data-slot="label" for="tone_level" :required="true">
                            Niveau de Formalité
                        </x-ui.label>
                        <x-document-generator.tone-selector 
                            wire:model.live="tone_level"
                            :tone-levels="$this->toneLevels"
                            :invalid="isset($validationErrors['tone_level'])"
                        />
                        @if(isset($validationErrors['tone_level']))
                            <x-ui.error data-slot="error">
                                {{ $validationErrors['tone_level'][0] }}
                            </x-ui.error>
                        @endif
                    </x-ui.field>

                    {{-- Language Selection --}}
                    <x-ui.field>
                        <x-ui.label data-slot="label" for="language" :required="true">
                            Langue
                        </x-ui.label>
                        <x-document-generator.language-selector 
                            wire:model.live="language"
                            :languages="$this->languages"
                            :invalid="isset($validationErrors['language'])"
                        />
                        @if(isset($validationErrors['language']))
                            <x-ui.error data-slot="error">
                                {{ $validationErrors['language'][0] }}
                            </x-ui.error>
                        @endif
                    </x-ui.field>
                </div>
            </x-ui.card>

            {{-- Action Buttons --}}
            @if($showFieldsForm && !empty($requiredFields))
                <x-ui.card class="p-6">
                    <div class="space-y-4">
                        <x-ui.button 
                            wire:click="generatePreview"
                            wire:loading.attr="disabled"
                            wire:target="generatePreview"
                            variant="outline"
                            size="lg"
                            class="w-full"
                            :disabled="$isGenerating"
                        >
                            <x-ui.icon name="eye" wire:loading.remove wire:target="generatePreview" />
                            <x-ui.icon.loading wire:loading wire:target="generatePreview" />
                            Aperçu
                        </x-ui.button>

                        <x-ui.button 
                            wire:click="generateDocument"
                            wire:loading.attr="disabled"
                            wire:target="generateDocument"
                            variant="primary"
                            size="lg"
                            class="w-full"
                            :disabled="$isGenerating"
                        >
                            <x-ui.icon name="document-text" wire:loading.remove wire:target="generateDocument" />
                            <x-ui.icon.loading wire:loading wire:target="generateDocument" />
                            Générer le Document
                        </x-ui.button>

                        @if($documentResult)
                            <x-ui.button 
                                wire:click="downloadDocument"
                                variant="solid"
                                size="lg"
                                class="w-full"
                                color="green"
                            >
                                <x-ui.icon name="arrow-down-tray" />
                                Télécharger PDF
                            </x-ui.button>
                        @endif

                        <x-ui.button 
                            wire:click="resetForm"
                            variant="ghost"
                            size="md"
                            class="w-full"
                        >
                            <x-ui.icon name="arrow-path" />
                            Nouveau Document
                        </x-ui.button>
                    </div>
                </x-ui.card>
            @endif
        </div>

        {{-- Content Panel --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Dynamic Fields Form --}}
            @if($showFieldsForm && !empty($requiredFields))
                <x-ui.card class="p-6">
                    <x-ui.heading level="h2" size="lg" class="mb-6">
                        Informations du Document
                    </x-ui.heading>

                    <x-document-generator.dynamic-form 
                        :required-fields="$requiredFields"
                        :field-labels="$this->fieldLabels"
                        :fields="$fields"
                        :validation-errors="$validationErrors"
                        wire:model="fields"
                    />
                </x-ui.card>
            @endif

            {{-- Preview/Generated Content --}}
            @if($showPreview && $previewContent)
                <x-ui.card class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <x-ui.heading level="h2" size="lg">
                            Aperçu du Document
                        </x-ui.heading>
                        <x-ui.button 
                            @click="$wire.showPreview = false"
                            variant="ghost"
                            size="sm"
                        >
                            <x-ui.icon name="x-mark" />
                        </x-ui.button>
                    </div>
                    
                    <x-document-generator.preview-panel 
                        :content="$previewContent"
                    />
                </x-ui.card>
            @endif

            @if($generatedContent)
                <x-ui.card class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <x-ui.heading level="h2" size="lg">
                            Document Généré
                        </x-ui.heading>
                        <div class="flex gap-2">
                            <x-ui.button 
                                wire:click="downloadDocument"
                                variant="primary"
                                size="sm"
                            >
                                <x-ui.icon name="arrow-down-tray" />
                                Télécharger
                            </x-ui.button>
                        </div>
                    </div>
                    
                    <x-document-generator.document-viewer 
                        :content="$generatedContent"
                        :metadata="$documentResult?->metadata ?? []"
                    />
                </x-ui.card>
            @endif

            {{-- Instructions Panel --}}
            @if(!$showFieldsForm)
                <x-ui.card class="p-6">
                    <x-ui.heading level="h2" size="lg" class="mb-4">
                        Instructions
                    </x-ui.heading>
                    <div class="space-y-4 text-gray-600 dark:text-gray-300">
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0 w-6 h-6 bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-300 rounded-full flex items-center justify-center text-sm font-medium">
                                1
                            </div>
                            <x-ui.text>
                                Sélectionnez votre pays pour appliquer les conventions administratives locales
                            </x-ui.text>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0 w-6 h-6 bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-300 rounded-full flex items-center justify-center text-sm font-medium">
                                2
                            </div>
                            <x-ui.text>
                                Choisissez le type de document administratif que vous souhaitez créer
                            </x-ui.text>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0 w-6 h-6 bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-300 rounded-full flex items-center justify-center text-sm font-medium">
                                3
                            </div>
                            <x-ui.text>
                                Définissez le niveau de formalité approprié à votre contexte
                            </x-ui.text>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0 w-6 h-6 bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-300 rounded-full flex items-center justify-center text-sm font-medium">
                                4
                            </div>
                            <x-ui.text>
                                Remplissez les informations requises et générez votre document
                            </x-ui.text>
                        </div>
                    </div>
                </x-ui.card>
            @endif
        </div>
    </div>

    {{-- Error Messages --}}
    @if($errors->any())
        <div class="fixed bottom-4 right-4 z-50">
            @foreach($errors->all() as $error)
                <x-ui.toast.item 
                    type="error"
                    :message="$error"
                    x-data="{ show: true }"
                    x-show="show"
                    x-init="setTimeout(() => show = false, 5000)"
                />
            @endforeach
        </div>
    @endif
</div>

<script>
function documentGenerator() {
    return {
        init() {
            // Initialize any client-side functionality
            this.$wire.on('document-generated', (data) => {
                // Handle document generation success
                console.log('Document generated successfully', data);
            });
        }
    }
}
</script>