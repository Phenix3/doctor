import './globals/theme.js'; /* By Sheaf.dev */ 

import './bootstrap';
import { Livewire, Alpine } from '../../vendor/livewire/livewire/dist/livewire.esm';

// now you can register
// components using Alpine.data(...) and
// plugins using Alpine.plugin(...) 

// Document Generator Alpine.js components
Alpine.data('documentGenerator', () => ({
    init() {
        // Initialize any client-side functionality
        this.$wire.on('document-generated', (data) => {
            // Handle document generation success
            console.log('Document generated successfully', data);
        });
    }
}));

Alpine.data('dynamicForm', () => ({
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
}));

 
Livewire.start()