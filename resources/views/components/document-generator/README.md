# Document Generator UI Components

This directory contains specialized SheafUI components for the African Administrative Document Generator.

## Components Overview

### Main Components

1. **country-selector.blade.php** - Dropdown selector for African countries with flag icons
2. **document-type-selector.blade.php** - Dropdown selector for document types with descriptions and icons
3. **tone-selector.blade.php** - Dropdown selector for formality levels (Simple, Official, Very Official)
4. **language-selector.blade.php** - Language selector (French/English) with flag icons
5. **dynamic-form.blade.php** - Dynamic form that shows required fields based on document type
6. **preview-panel.blade.php** - Document preview component with paper simulation
7. **document-viewer.blade.php** - Final document viewer with metadata and export options

## Usage

### In Livewire Components

```php
// Main document generator view
<x-document-generator.country-selector 
    wire:model.live="country"
    :countries="$this->countries"
    :invalid="isset($validationErrors['country'])"
/>

<x-document-generator.document-type-selector 
    wire:model.live="document_type"
    :document-types="$this->documentTypes"
    :invalid="isset($validationErrors['document_type'])"
/>

<x-document-generator.dynamic-form 
    :required-fields="$requiredFields"
    :field-labels="$this->fieldLabels"
    :fields="$fields"
    :validation-errors="$validationErrors"
    wire:model="fields"
/>
```

## Features

### Real-time Validation
- Client-side validation for email and phone formats
- Real-time character counting for text areas
- Visual error states with red borders

### Accessibility
- Proper ARIA labels and roles
- Keyboard navigation support
- Screen reader friendly

### Responsive Design
- Mobile-first approach
- Adaptive layouts for different screen sizes
- Touch-friendly interactions

### Alpine.js Integration
- Smooth animations and transitions
- Client-side interactivity
- Form state management

## Styling

All components use SheafUI design system with:
- Consistent spacing and typography
- Dark mode support
- Tailwind CSS utility classes
- Custom CSS variables for theming

## Dependencies

- SheafUI components (ui.*)
- Alpine.js for interactivity
- Heroicons for icons
- Tailwind CSS for styling
- Livewire for server-side reactivity