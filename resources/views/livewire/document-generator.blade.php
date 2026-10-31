<div class="min-h-screen bg-gradient-to-b from-muted/30 to-background pb-12" x-data="documentGenerator()">
    {{-- Header --}}
    <div class="bg-gradient-to-r from-primary/5 via-accent/5 to-primary/5 border-b border-border/50">
        <div class="max-w-7xl mx-auto px-6 py-12 text-center space-y-4">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 bg-primary/10 dark:bg-primary/20 rounded-full mb-2">
                <x-ui.icon name="document-text" class="w-4 h-4 text-primary" />
                <span class="text-xs font-medium text-primary">Générateur Intelligent</span>
            </div>
            <x-ui.heading level="h1" size="2xl" class="text-foreground font-bold tracking-tight">
                Générateur de Documents Administratifs Africains
            </x-ui.heading>
            <x-ui.text class="text-lg text-muted-foreground max-w-3xl mx-auto leading-relaxed">
                Créez des documents administratifs professionnels adaptés aux conventions locales de votre pays.
            </x-ui.text>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-6 mt-8">

    {{-- Main Form --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Configuration Panel --}}
        <div class="lg:col-span-1 space-y-5">
            <x-ui.card class="p-6 shadow-md border border-border/50 bg-card">
                <div class="flex items-center gap-3 mb-6 pb-4 border-b border-border/50">
                    <div class="p-2 bg-primary/10 rounded-lg">
                        <x-ui.icon name="cog-6-tooth" class="w-5 h-5 text-primary" />
                    </div>
                    <x-ui.heading level="h2" size="lg" class="text-card-foreground">
                        Configuration du Document
                    </x-ui.heading>
                </div>

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
                <x-ui.card class="p-6 shadow-md border border-border/50 bg-card sticky top-6">
                    <div class="space-y-3">
                        <x-ui.button
                            wire:click="generatePreview"
                            wire:loading.attr="disabled"
                            wire:target="generatePreview"
                            variant="outline"
                            size="lg"
                            class="w-full justify-center font-medium"
                            :disabled="$isGenerating"
                        >
                            <x-ui.icon name="eye" wire:loading.remove wire:target="generatePreview" />
                            <x-ui.icon.loading wire:loading wire:target="generatePreview" />
                            Aperçu du Document
                        </x-ui.button>

                        <x-ui.button
                            wire:click="generateDocument"
                            wire:loading.attr="disabled"
                            wire:target="generateDocument"
                            variant="solid"
                            size="lg"
                            class="w-full justify-center font-medium bg-primary hover:bg-primary/90 text-primary-foreground"
                            :disabled="$isGenerating"
                        >
                            <x-ui.icon name="document-text" wire:loading.remove wire:target="generateDocument" />
                            <x-ui.icon.loading wire:loading wire:target="generateDocument" />
                            Générer le Document
                        </x-ui.button>

                        @if($documentResult)
                            <div class="pt-2 border-t border-border/50">
                                <x-ui.button
                                    wire:click="downloadDocument"
                                    variant="solid"
                                    size="lg"
                                    class="w-full justify-center font-medium bg-green-600 hover:bg-green-700 text-white"
                                >
                                    <x-ui.icon name="arrow-down-tray" />
                                    Télécharger PDF
                                </x-ui.button>
                            </div>
                        @endif

                        <div class="pt-2">
                            <x-ui.button
                                wire:click="resetForm"
                                variant="ghost"
                                size="md"
                                class="w-full justify-center text-muted-foreground hover:text-foreground"
                            >
                                <x-ui.icon name="arrow-path" />
                                Nouveau Document
                            </x-ui.button>
                        </div>
                    </div>
                </x-ui.card>
            @endif
        </div>

        {{-- Content Panel --}}
        <div class="lg:col-span-2 space-y-5">
            {{-- Dynamic Fields Form --}}
            @if($showFieldsForm && !empty($requiredFields))
                <x-ui.card class="p-6 shadow-md border border-border/50 bg-card">
                    <div class="flex items-center gap-3 mb-6 pb-4 border-b border-border/50">
                        <div class="p-2 bg-accent/10 rounded-lg">
                            <x-ui.icon name="document" class="w-5 h-5 text-accent-foreground" />
                        </div>
                        <x-ui.heading level="h2" size="lg" class="text-card-foreground">
                            Informations du Document
                        </x-ui.heading>
                    </div>

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
                <x-ui.card class="p-6 shadow-md border border-border/50 bg-card">
                    <div class="flex items-center justify-between mb-6 pb-4 border-b border-border/50">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-blue-500/10 rounded-lg">
                                <x-ui.icon name="eye" class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                            </div>
                            <x-ui.heading level="h2" size="lg" class="text-card-foreground">
                                Aperçu du Document
                            </x-ui.heading>
                        </div>
                        <x-ui.button
                            @click="$wire.showPreview = false"
                            variant="ghost"
                            size="sm"
                            class="text-muted-foreground hover:text-foreground"
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
                <x-ui.card class="p-6 shadow-md border border-green-500/20 bg-card">
                    <div class="flex items-center justify-between mb-6 pb-4 border-b border-border/50">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-green-500/10 rounded-lg">
                                <x-ui.icon name="check-circle" class="w-5 h-5 text-green-600 dark:text-green-400" />
                            </div>
                            <x-ui.heading level="h2" size="lg" class="text-card-foreground">
                                Document Généré
                            </x-ui.heading>
                        </div>
                        <div class="flex gap-2">
                            <x-ui.button
                                wire:click="downloadDocument"
                                variant="solid"
                                size="sm"
                                class="bg-green-600 hover:bg-green-700 text-white"
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
                <x-ui.card class="p-8 shadow-md border border-border/50 bg-gradient-to-br from-card to-muted/20">
                    <div class="flex items-center gap-3 mb-6 pb-4 border-b border-border/50">
                        <div class="p-2 bg-primary/10 rounded-lg">
                            <x-ui.icon name="light-bulb" class="w-5 h-5 text-primary" />
                        </div>
                        <x-ui.heading level="h2" size="lg" class="text-card-foreground">
                            Comment utiliser le générateur
                        </x-ui.heading>
                    </div>
                    <div class="space-y-5">
                        <div class="flex items-start gap-4 p-4 rounded-lg bg-primary/5 border border-primary/10 transition-all hover:shadow-sm">
                            <div class="flex-shrink-0 w-8 h-8 bg-primary text-primary-foreground rounded-full flex items-center justify-center text-sm font-bold shadow-sm">
                                1
                            </div>
                            <div class="flex-1">
                                <x-ui.text class="text-card-foreground font-medium">
                                    Sélectionnez votre pays pour appliquer les conventions administratives locales
                                </x-ui.text>
                            </div>
                        </div>
                        <div class="flex items-start gap-4 p-4 rounded-lg bg-primary/5 border border-primary/10 transition-all hover:shadow-sm">
                            <div class="flex-shrink-0 w-8 h-8 bg-primary text-primary-foreground rounded-full flex items-center justify-center text-sm font-bold shadow-sm">
                                2
                            </div>
                            <div class="flex-1">
                                <x-ui.text class="text-card-foreground font-medium">
                                    Choisissez le type de document administratif que vous souhaitez créer
                                </x-ui.text>
                            </div>
                        </div>
                        <div class="flex items-start gap-4 p-4 rounded-lg bg-primary/5 border border-primary/10 transition-all hover:shadow-sm">
                            <div class="flex-shrink-0 w-8 h-8 bg-primary text-primary-foreground rounded-full flex items-center justify-center text-sm font-bold shadow-sm">
                                3
                            </div>
                            <div class="flex-1">
                                <x-ui.text class="text-card-foreground font-medium">
                                    Définissez le niveau de formalité approprié à votre contexte
                                </x-ui.text>
                            </div>
                        </div>
                        <div class="flex items-start gap-4 p-4 rounded-lg bg-primary/5 border border-primary/10 transition-all hover:shadow-sm">
                            <div class="flex-shrink-0 w-8 h-8 bg-primary text-primary-foreground rounded-full flex items-center justify-center text-sm font-bold shadow-sm">
                                4
                            </div>
                            <div class="flex-1">
                                <x-ui.text class="text-card-foreground font-medium">
                                    Remplissez les informations requises et générez votre document
                                </x-ui.text>
                            </div>
                        </div>
                    </div>
                </x-ui.card>
            @endif
        </div>
    </div>
    </div>

    {{-- Error Messages --}}
    @if($errors->any())
        <div class="fixed bottom-4 right-4 z-50 max-w-md">
            @foreach($errors->all() as $error)
                <x-ui.toast.item
                    type="error"
                    :message="$error"
                    x-data="{ show: true }"
                    x-show="show"
                    x-init="setTimeout(() => show = false, 5000)"
                    class="shadow-lg"
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