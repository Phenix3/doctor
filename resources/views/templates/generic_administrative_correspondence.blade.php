{{-- Template pour Courrier Administratif Générique --}}
@php
    $currentDate = isset($date) ? \Carbon\Carbon::parse($date)->format('d/m/Y') : now()->format('d/m/Y');
    $country = $country ?? 'SN';
    $tone_level = $tone_level ?? 'OFFICIAL';
    
    // Country-specific adaptations
    $dateFormat = match($country) {
        'CI' => 'd/m/Y',
        'SN' => 'd/m/Y',
        'ML' => 'd/m/Y',
        'BF' => 'd/m/Y',
        default => 'd/m/Y'
    };
    
    // Tone-specific formulas
    $openingFormula = match($tone_level) {
        'SIMPLE' => 'Monsieur/Madame,',
        'OFFICIAL' => 'Monsieur/Madame,',
        'VERY_OFFICIAL' => 'Excellence, Monsieur/Madame le/la ' . ($recipient_title ?? 'Ministre'),
        default => 'Monsieur/Madame,'
    };
    
    $closingFormula = match($tone_level) {
        'SIMPLE' => 'Cordialement,',
        'OFFICIAL' => 'Je vous prie d\'agréer, Monsieur/Madame, l\'expression de mes salutations distinguées.',
        'VERY_OFFICIAL' => 'Je vous prie d\'agréer, Excellence, l\'expression de ma très haute considération.',
        default => 'Je vous prie d\'agréer, Monsieur/Madame, l\'expression de mes salutations distinguées.'
    };
@endphp

{{ $sender_name ?? '' }}
{{ $sender_address ?? '' }}
{{ $sender_city ?? '' }}
@if(!empty($sender_phone))
Téléphone : {{ $sender_phone }}
@endif
@if(!empty($sender_email))
Email : {{ $sender_email }}
@endif

@if(!empty($sender_function))
Fonction : {{ $sender_function }}
@endif

@if(!empty($sender_organization))
Organisation : {{ $sender_organization }}
@endif


{{ $recipient_title ?? 'Monsieur/Madame le/la' }} {{ $recipient_name ?? '' }}
{{ $recipient_organization ?? '' }}
@if(!empty($recipient_address))
{{ $recipient_address }}
@endif
@if(!empty($recipient_city))
{{ $recipient_city }}
@endif


{{ $sender_city ?? '' }}, le {{ \Carbon\Carbon::parse($date ?? now())->format($dateFormat) }}


Objet : {{ $subject ?? 'Correspondance administrative' }}

@if(!empty($reference_number))
Référence : {{ $reference_number }}
@endif

@if(!empty($your_reference))
Votre référence : {{ $your_reference }}
@endif


{{ $politeness_formula_opening ?? $openingFormula }}

@if(!empty($context))
CONTEXTE :
{{ $context }}
@endif

MESSAGE PRINCIPAL :
{{ $message_content ?? '' }}

@if(!empty($supporting_details))
DÉTAILS COMPLÉMENTAIRES :
{{ $supporting_details }}
@endif

@if(!empty($background_information))
INFORMATIONS DE CONTEXTE :
{{ $background_information }}
@endif

@if(!empty($requested_action))
ACTION DEMANDÉE :
{{ $requested_action }}
@endif

@if(!empty($deadline))
DÉLAI :
{{ $deadline }}
@endif

@if(!empty($follow_up_information))
INFORMATIONS DE SUIVI :
{{ $follow_up_information }}
@endif

@if(!empty($contact_information))
CONTACT :
{{ $contact_information }}
@endif

@if(!empty($next_steps))
PROCHAINES ÉTAPES :
{{ $next_steps }}
@endif

@if(!empty($additional_notes))
NOTES ADDITIONNELLES :
{{ $additional_notes }}
@endif

{{ $politeness_formula_closing ?? $closingFormula }}


{{ $sender_name ?? '' }}
@if(!empty($sender_signature))
{{ $sender_signature }}
@endif

@if(!empty($sender_function))
{{ $sender_function }}
@endif


Pièces jointes :
{{ $attachments ?? 'Selon objet' }}

@if(!empty($copy_to))
Copie à :
{{ $copy_to }}
@endif