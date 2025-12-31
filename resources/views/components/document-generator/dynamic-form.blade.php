@props([
    'requiredFields' => [],
    'fieldLabels' => [],
    'fields' => [],
    'validationErrors' => []
])

<div class="space-y-6" x-data="dynamicForm()">
    @foreach($requiredFields as $fieldName)
        @php
            $fieldLabel = $fieldLabels[$fieldName] ?? ucfirst(str_replace('_', ' ', $fieldName));
            $fieldValue = $fields[$fieldName] ?? '';
            $hasError = isset($validationErrors["fields.{$fieldName}"]);
            $errorMessage = $hasError ? $validationErrors["fields.{$fieldName}"][0] : '';
            
            // Determine field type based on field name
            $fieldType = 'text';
            $isTextarea = false;
            $isDate = false;
            
            if (str_contains($fieldName, 'email')) {
                $fieldType = 'email';
            } elseif (str_contains($fieldName, 'phone')) {
                $fieldType = 'tel';
            } elseif (str_contains($fieldName, 'date')) {
                $fieldType = 'date';
                $isDate = true;
            } elseif (in_array($fieldName, [
                'request_details', 'motivation_content', 'qualifications', 'declaration_content',
                'complaint_details', 'justification', 'appeal_grounds', 'message_content'
            ])) {
                $isTextarea = true;
            }
        @endphp

        <x-ui.field>
            <x-ui.label data-slot="label" for="{{ $fieldName }}" :required="true">
                {{ $fieldLabel }}
            </x-ui.label>
            
            @if($isTextarea)
                <textarea
                    wire:model.live.debounce.500ms="fields.{{ $fieldName }}"
                    id="{{ $fieldName }}"
                    name="{{ $fieldName }}"
                    rows="4"
                    @class([
                        'w-full px-3 py-2 text-sm border rounded-box bg-white dark:bg-neutral-900 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 transition-colors duration-200 resize-y',
                        'border-black/10 dark:border-white/15 focus:border-black/15 dark:focus:border-white/20 focus:ring-2 focus:ring-neutral-900/15 dark:focus:ring-neutral-100/15' => !$hasError,
                        'border-red-600/30 border-2 focus:border-red-600/30 focus:ring-red-600/20 dark:border-red-400/30 dark:focus:border-red-400/30 dark:focus:ring-red-400/20' => $hasError,
                    ])
                    placeholder="Saisissez {{ strtolower($fieldLabel) }}..."
                    x-data="{ 
                        count: 0,
                        maxLength: {{ in_array($fieldName, ['motivation_content', 'complaint_details', 'appeal_grounds']) ? 2500 : 2000 }}
                    }"
                    x-init="count = $el.value.length"
                    x-on:input="count = $el.value.length"
                ></textarea>
                <div class="flex justify-between items-center mt-1">
                    @if($hasError)
                        <x-ui.error data-slot="error">
                            {{ $errorMessage }}
                        </x-ui.error>
                    @else
                        <div></div>
                    @endif
                    <div class="text-xs text-gray-500 dark:text-gray-400" x-text="`${count}/${maxLength} caractères`"></div>
                </div>
            @else
                <x-ui.input
                    wire:model.live.debounce.300ms="fields.{{ $fieldName }}"
                    type="{{ $fieldType }}"
                    id="{{ $fieldName }}"
                    name="{{ $fieldName }}"
                    placeholder="Saisissez {{ strtolower($fieldLabel) }}..."
                    :invalid="$hasError"
                    @if($isDate)
                        max="{{ date('Y-m-d') }}"
                    @endif
                />
                @if($hasError)
                    <x-ui.error data-slot="error">
                        {{ $errorMessage }}
                    </x-ui.error>
                @endif
            @endif
            
            {{-- Field-specific help text --}}
            @if($fieldName === 'applicant_phone' || $fieldName === 'phone')
                <x-ui.text class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Format: +XXX XX XX XX XX ou XX XX XX XX XX
                </x-ui.text>
            @elseif($fieldName === 'date')
                <x-ui.text class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Date du document (aujourd'hui par défaut)
                </x-ui.text>
            @elseif(str_contains($fieldName, 'address'))
                <x-ui.text class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Adresse complète avec ville et code postal
                </x-ui.text>
            @elseif(in_array($fieldName, ['request_details', 'motivation_content', 'complaint_details']))
                <x-ui.text class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Décrivez clairement et de manière factuelle. Évitez les opinions personnelles.
                </x-ui.text>
            @endif
        </x-ui.field>
    @endforeach

    {{-- Auto-fill date field with today's date --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const dateField = document.querySelector('input[name="date"]');
            if (dateField && !dateField.value) {
                dateField.value = new Date().toISOString().split('T')[0];
                // Trigger Livewire update
                dateField.dispatchEvent(new Event('input'));
            }
        });
    </script>
</div>

<script>
function dynamicForm() {
    return {
        init() {
            // Auto-focus first field
            this.$nextTick(() => {
                const firstInput = this.$el.querySelector('input, textarea');
                if (firstInput) {
                    firstInput.focus();
                }
            });
        },
        
        validateField(fieldName, value) {
            // Client-side validation helpers
            if (fieldName.includes('email') && value) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return emailRegex.test(value);
            }
            
            if (fieldName.includes('phone') && value) {
                const phoneRegex = /^[\+]?[0-9\s\-\(\)]{8,20}$/;
                return phoneRegex.test(value);
            }
            
            return true;
        }
    }
}
</script>